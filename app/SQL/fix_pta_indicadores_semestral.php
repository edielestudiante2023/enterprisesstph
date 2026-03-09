<?php
/**
 * Fix: Dejar solo 2 registros por cliente por programa de indicadores (semestral)
 * - Mantiene los 2 primeros (por id_ptacliente ASC)
 * - Les asigna "(Periodo 1)" y "(Periodo 2)"
 * - Elimina los sobrantes
 *
 * Uso: DB_PROD_PASS=xxx php app/SQL/fix_pta_indicadores_semestral.php [production]
 */

$env = $argv[1] ?? 'local';

if ($env === 'production') {
    $host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port = 25060;
    $db   = 'propiedad_horizontal';
    $user = 'cycloid_userdb';
    $pass = getenv('DB_PROD_PASS');
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
$opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
if ($ssl) {
    $opts[PDO::MYSQL_ATTR_SSL_CA] = true;
    $opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
}

try {
    $pdo = new PDO($dsn, $user, $pass, $opts);
    echo "Conectado a {$db} ({$env})\n\n";
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage() . "\n");
}

$programas = [
    'limpieza' => [
        'like' => '%indicadores del Programa de limpieza%',
        'texto_p1' => 'Realizar seguimiento periódico a los indicadores del Programa de limpieza y desinfección. (Periodo 1)',
        'texto_p2' => 'Realizar seguimiento periódico a los indicadores del Programa de limpieza y desinfección. (Periodo 2)',
    ],
    'residuos' => [
        'like' => '%indicadores del Programa de manejo integral%',
        'texto_p1' => 'Realizar seguimiento periódico a los indicadores del Programa de manejo integral de residuos sólidos. (Periodo 1)',
        'texto_p2' => 'Realizar seguimiento periódico a los indicadores del Programa de manejo integral de residuos sólidos. (Periodo 2)',
    ],
    'plagas' => [
        'like' => '%indicadores del Programa de control integrado%',
        'texto_p1' => 'Realizar seguimiento periódico a los indicadores del Programa de control integrado de plagas. (Periodo 1)',
        'texto_p2' => 'Realizar seguimiento periódico a los indicadores del Programa de control integrado de plagas. (Periodo 2)',
    ],
    'agua' => [
        'like' => '%indicadores del Programa de abastecimiento%',
        'texto_p1' => 'Realizar seguimiento periódico a los indicadores del Programa de abastecimiento y control de agua potable. (Periodo 1)',
        'texto_p2' => 'Realizar seguimiento periódico a los indicadores del Programa de abastecimiento y control de agua potable. (Periodo 2)',
    ],
];

$totalKept = 0;
$totalDeleted = 0;

foreach ($programas as $nombre => $config) {
    echo "--- {$nombre} ---\n";

    // Obtener clientes que tienen este tipo de registro
    $stmtClientes = $pdo->prepare("SELECT DISTINCT id_cliente FROM tbl_pta_cliente WHERE actividad_plandetrabajo LIKE ? ORDER BY id_cliente");
    $stmtClientes->execute([$config['like']]);
    $clientes = $stmtClientes->fetchAll(PDO::FETCH_COLUMN);

    $kept = 0;
    $deleted = 0;

    foreach ($clientes as $idCliente) {
        // Obtener todos los registros de este cliente+programa, ordenados por ID
        $stmtRows = $pdo->prepare("SELECT id_ptacliente FROM tbl_pta_cliente WHERE id_cliente = ? AND actividad_plandetrabajo LIKE ? ORDER BY id_ptacliente ASC");
        $stmtRows->execute([$idCliente, $config['like']]);
        $ids = $stmtRows->fetchAll(PDO::FETCH_COLUMN);

        if (count($ids) <= 2) {
            // Ya tiene 2 o menos, solo renombrar
            if (isset($ids[0])) {
                $pdo->prepare("UPDATE tbl_pta_cliente SET actividad_plandetrabajo = ? WHERE id_ptacliente = ?")->execute([$config['texto_p1'], $ids[0]]);
            }
            if (isset($ids[1])) {
                $pdo->prepare("UPDATE tbl_pta_cliente SET actividad_plandetrabajo = ? WHERE id_ptacliente = ?")->execute([$config['texto_p2'], $ids[1]]);
            }
            $kept += count($ids);
            continue;
        }

        // Mantener los 2 primeros
        $keepId1 = $ids[0];
        $keepId2 = $ids[1];

        // Renombrar los que se quedan
        $pdo->prepare("UPDATE tbl_pta_cliente SET actividad_plandetrabajo = ? WHERE id_ptacliente = ?")->execute([$config['texto_p1'], $keepId1]);
        $pdo->prepare("UPDATE tbl_pta_cliente SET actividad_plandetrabajo = ? WHERE id_ptacliente = ?")->execute([$config['texto_p2'], $keepId2]);

        // Eliminar los sobrantes
        $deleteIds = array_slice($ids, 2);
        $placeholders = implode(',', array_fill(0, count($deleteIds), '?'));
        $pdo->prepare("DELETE FROM tbl_pta_cliente WHERE id_ptacliente IN ({$placeholders})")->execute($deleteIds);

        $kept += 2;
        $deleted += count($deleteIds);
    }

    echo "  Clientes: " . count($clientes) . " | Mantenidos: {$kept} | Eliminados: {$deleted}\n";
    $totalKept += $kept;
    $totalDeleted += $deleted;
}

echo "\n=== RESUMEN ===\n";
echo "Total mantenidos: {$totalKept}\n";
echo "Total eliminados: {$totalDeleted}\n";
echo "Esperado mantenidos: ~" . ($totalKept) . " (2 por cliente por programa)\n";
