<?php
/**
 * Migra tbl_evaluacion_respuestas para impedir duplicados:
 *   - Agrega columna generada `fecha_dia DATE GENERATED ALWAYS AS (DATE(created_at)) STORED`
 *   - Agrega UNIQUE KEY uk_eval_doc_dia (id_evaluacion, cedula, fecha_dia)
 *
 * Uso:
 *   DRY-RUN:   DB_PROD_PASS=xxx php app/SQL/migrate_uk_evaluacion_respuestas.php production
 *   EJECUTAR:  DB_PROD_PASS=xxx php app/SQL/migrate_uk_evaluacion_respuestas.php production --apply
 *
 * Local:
 *   DRY-RUN:   php app/SQL/migrate_uk_evaluacion_respuestas.php
 *   EJECUTAR:  php app/SQL/migrate_uk_evaluacion_respuestas.php local --apply
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
    echo "Conectado a BD {$env}" . ($apply ? " -- MODO APPLY" : " -- MODO DRY-RUN") . "\n\n";
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage() . "\n");
}

// 1. Verificar estado actual de la tabla
echo "=== Estructura actual tbl_evaluacion_respuestas ===\n";
$cols = $pdo->query("SHOW COLUMNS FROM tbl_evaluacion_respuestas")->fetchAll(PDO::FETCH_ASSOC);
$tieneFechaDia = false;
foreach ($cols as $c) {
    echo "  {$c['Field']}  {$c['Type']}  {$c['Null']}  {$c['Extra']}\n";
    if ($c['Field'] === 'fecha_dia') $tieneFechaDia = true;
}

echo "\n=== Indices actuales ===\n";
$idx = $pdo->query("SHOW INDEX FROM tbl_evaluacion_respuestas")->fetchAll(PDO::FETCH_ASSOC);
$tieneUK = false;
foreach ($idx as $i) {
    echo "  {$i['Key_name']}  ({$i['Column_name']})  unique=" . (!$i['Non_unique'] ? 'YES' : 'no') . "\n";
    if ($i['Key_name'] === 'uk_eval_doc_dia') $tieneUK = true;
}

// 2. Verificar si hay filas que violarian la UK (usando SELECT con DATE(created_at))
echo "\n=== Verificando posibles violaciones de UK (antes del ALTER) ===\n";
$violSql = "
    SELECT id_evaluacion, cedula, DATE(created_at) AS dia, COUNT(*) AS cnt
    FROM tbl_evaluacion_respuestas
    WHERE cedula IS NOT NULL AND cedula <> ''
    GROUP BY id_evaluacion, cedula, DATE(created_at)
    HAVING cnt > 1
    ORDER BY id_evaluacion, cedula, dia
";
$viol = $pdo->query($violSql)->fetchAll(PDO::FETCH_ASSOC);
if (empty($viol)) {
    echo "  OK: no hay duplicados por (id_evaluacion, cedula, DATE(created_at)).\n";
} else {
    echo "  ADVERTENCIA: " . count($viol) . " grupos violarian la UK. Correr cleanup_duplicados_evaluacion_respuestas.php --apply antes.\n";
    foreach ($viol as $v) {
        echo "    eval={$v['id_evaluacion']}  ced={$v['cedula']}  dia={$v['dia']}  cnt={$v['cnt']}\n";
    }
}

// 3. Plan de cambios
echo "\n=== Plan ===\n";
$pasos = [];
if (!$tieneFechaDia) {
    $pasos[] = "ADD COLUMN fecha_dia DATE GENERATED ALWAYS AS (DATE(created_at)) STORED";
} else {
    echo "  [skip] columna fecha_dia ya existe\n";
}
if (!$tieneUK) {
    $pasos[] = "ADD UNIQUE KEY uk_eval_doc_dia (id_evaluacion, cedula, fecha_dia)";
} else {
    echo "  [skip] indice uk_eval_doc_dia ya existe\n";
}

if (empty($pasos)) {
    echo "  Nada que aplicar. Migracion ya esta completada.\n";
    exit(0);
}

foreach ($pasos as $p) echo "  $p\n";

if (!empty($viol)) {
    echo "\nABORTADO: hay duplicados vigentes que impediran crear la UK. Corre el cleanup primero.\n";
    exit(1);
}

if (!$apply) {
    echo "\nDRY-RUN: no se ejecuto nada. Para aplicar, agregar flag --apply\n";
    exit(0);
}

// 4. APLICAR
echo "\n=== APLICANDO ===\n";
foreach ($pasos as $p) {
    $sql = "ALTER TABLE tbl_evaluacion_respuestas $p";
    echo "EXEC: $sql\n";
    try {
        $pdo->exec($sql);
        echo "  OK\n";
    } catch (PDOException $e) {
        echo "  ERROR: " . $e->getMessage() . "\n";
        exit(2);
    }
}

echo "\nMigracion completada.\n";

// 5. Verificacion final
echo "\n=== Verificacion post-migracion ===\n";
$idxFinal = $pdo->query("SHOW INDEX FROM tbl_evaluacion_respuestas WHERE Key_name='uk_eval_doc_dia'")->fetchAll(PDO::FETCH_ASSOC);
if (count($idxFinal) >= 3) {
    echo "  UK uk_eval_doc_dia presente con " . count($idxFinal) . " columnas. OK\n";
} else {
    echo "  ADVERTENCIA: UK no quedo bien configurada.\n";
}
