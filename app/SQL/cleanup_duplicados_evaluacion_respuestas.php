<?php
/**
 * Cleanup duplicados en tbl_evaluacion_respuestas.
 * Criterio: por (id_evaluacion, cedula) deja la fila con calificacion MÁS ALTA.
 * Empate: deja la más reciente (id DESC).
 *
 * Uso:
 *   DRY-RUN:   DB_PROD_PASS=xxx php app/SQL/cleanup_duplicados_evaluacion_respuestas.php production
 *   EJECUTAR:  DB_PROD_PASS=xxx php app/SQL/cleanup_duplicados_evaluacion_respuestas.php production --apply
 */

$env   = $argv[1] ?? 'local';
$apply = in_array('--apply', $argv, true);

if ($env === 'production') {
    $host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port = 25060;
    $db   = 'propiedad_horizontal';
    $user = 'cycloid_userdb';
    $pass = getenv('DB_PROD_PASS');
    if (!$pass) { die("ERROR: Set DB_PROD_PASS environment variable\n"); }
    $ssl  = true;
} else {
    $host = '127.0.0.1';
    $port = 3306;
    $db   = 'propiedad_horizontal';
    $user = 'root';
    $pass = '';
    $ssl  = false;
}

$dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
if ($ssl) {
    $options[PDO::MYSQL_ATTR_SSL_CA] = true;
    $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
}

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "Conectado a BD {$env}" . ($apply ? " — MODO APPLY (borrara filas)" : " — MODO DRY-RUN") . "\n\n";
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage() . "\n");
}

// 1. Total registros actuales
$total = (int)$pdo->query("SELECT COUNT(*) FROM tbl_evaluacion_respuestas")->fetchColumn();
echo "Total filas en tbl_evaluacion_respuestas: $total\n\n";

// 2. Encontrar grupos duplicados (id_evaluacion, cedula) con >1 filas
$sqlGroups = "
    SELECT id_evaluacion, cedula, COUNT(*) AS cnt
    FROM tbl_evaluacion_respuestas
    WHERE cedula IS NOT NULL AND cedula <> ''
    GROUP BY id_evaluacion, cedula
    HAVING cnt > 1
    ORDER BY id_evaluacion, cedula
";
$groups = $pdo->query($sqlGroups)->fetchAll(PDO::FETCH_ASSOC);

if (empty($groups)) {
    echo "No hay duplicados. Nada que hacer.\n";
    exit(0);
}

echo "Grupos duplicados encontrados: " . count($groups) . "\n";
echo str_repeat("=", 100) . "\n";

$totalAEliminar = 0;
$idsAEliminar = [];

$sqlRows = $pdo->prepare("
    SELECT id, id_evaluacion, nombre, cedula, calificacion, created_at
    FROM tbl_evaluacion_respuestas
    WHERE id_evaluacion = :eid AND cedula = :ced
    ORDER BY calificacion DESC, id DESC
");

foreach ($groups as $g) {
    $sqlRows->execute([':eid' => $g['id_evaluacion'], ':ced' => $g['cedula']]);
    $rows = $sqlRows->fetchAll(PDO::FETCH_ASSOC);

    $keep = $rows[0];
    $delete = array_slice($rows, 1);

    echo sprintf(
        "\nEval #%d  Cedula %s  -> %d filas (keep id=%d calif=%.1f, delete %d)\n",
        $g['id_evaluacion'], $g['cedula'], $g['cnt'],
        $keep['id'], (float)$keep['calificacion'], count($delete)
    );
    echo sprintf("  KEEP  : id=%d  nombre=%s  calif=%.1f  created=%s\n",
        $keep['id'], $keep['nombre'], (float)$keep['calificacion'], $keep['created_at']);
    foreach ($delete as $d) {
        echo sprintf("  DELETE: id=%d  nombre=%s  calif=%.1f  created=%s\n",
            $d['id'], $d['nombre'], (float)$d['calificacion'], $d['created_at']);
        $idsAEliminar[] = (int)$d['id'];
    }
    $totalAEliminar += count($delete);
}

echo "\n" . str_repeat("=", 100) . "\n";
echo "RESUMEN:\n";
echo "  Total filas actuales        : $total\n";
echo "  Grupos con duplicados       : " . count($groups) . "\n";
echo "  Filas que se eliminarian    : $totalAEliminar\n";
echo "  Filas resultantes esperadas : " . ($total - $totalAEliminar) . "\n\n";

if (!$apply) {
    echo "DRY-RUN: no se elimino nada. Para aplicar, agregar flag --apply\n";
    exit(0);
}

// 3. APPLY: borrar en transaccion
if (empty($idsAEliminar)) {
    echo "Nada que borrar.\n";
    exit(0);
}

echo "APLICANDO BORRADO...\n";
$pdo->beginTransaction();
try {
    $placeholders = implode(',', array_fill(0, count($idsAEliminar), '?'));
    $del = $pdo->prepare("DELETE FROM tbl_evaluacion_respuestas WHERE id IN ($placeholders)");
    $del->execute($idsAEliminar);
    $n = $del->rowCount();
    $pdo->commit();
    echo "OK: $n filas eliminadas.\n";

    $totalFinal = (int)$pdo->query("SELECT COUNT(*) FROM tbl_evaluacion_respuestas")->fetchColumn();
    echo "Total final en tabla: $totalFinal\n";
} catch (Exception $e) {
    $pdo->rollBack();
    die("ERROR durante el borrado, rollback aplicado: " . $e->getMessage() . "\n");
}
