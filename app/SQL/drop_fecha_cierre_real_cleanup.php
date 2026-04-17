<?php
/**
 * Migración: limpieza final del refactor de fechas en tbl_pendientes.
 * - fecha_plazo = deadline
 * - fecha_cierre = fecha real de cierre (NULL hasta que se cierra)
 * - fecha_cierre_real: DROP (era redundante)
 *
 * Backfills:
 *  1. CERRADAs: fecha_plazo = fecha_cierre (si no tenía plazo backfilleado)
 *  2. ABIERTAS/SIN RESPUESTA: fecha_cierre = NULL (no deben tener fecha de cierre)
 *
 * Uso: php drop_fecha_cierre_real_cleanup.php [local|production]
 * Idempotente.
 */

if (php_sapi_name() !== 'cli') die("Solo CLI.\n");

$env = $argv[1] ?? 'local';

if ($env === 'local') {
    $config = ['host' => '127.0.0.1', 'port' => 3306, 'user' => 'root', 'password' => '', 'database' => 'propiedad_horizontal', 'ssl' => false];
} elseif ($env === 'production') {
    $config = ['host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com', 'port' => 25060, 'user' => 'cycloid_userdb', 'password' => getenv('DB_PROD_PASS') ?: '', 'database' => 'propiedad_horizontal', 'ssl' => true];
} else {
    die("Uso: php drop_fecha_cierre_real_cleanup.php [local|production]\n");
}

echo "=== Cleanup refactor fechas tbl_pendientes ===\n";
echo "Entorno: " . strtoupper($env) . " | DB: {$config['database']}\n---\n";

$dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
$opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
if ($config['ssl']) {
    $opts[PDO::MYSQL_ATTR_SSL_CA] = true;
    $opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
}

try {
    $pdo = new PDO($dsn, $config['user'], $config['password'], $opts);
    echo "Conectado.\n";

    $pdo->exec("SET SESSION sql_mode = 'ALLOW_INVALID_DATES'");

    $n1 = $pdo->exec("UPDATE tbl_pendientes SET fecha_plazo = fecha_cierre WHERE estado IN ('CERRADA','CERRADA POR FIN CONTRATO') AND fecha_plazo IS NULL AND fecha_cierre IS NOT NULL AND CAST(fecha_cierre AS CHAR) <> '0000-00-00'");
    echo "Backfill 1 (CERRADAs -> fecha_plazo): {$n1} filas\n";

    $n2 = $pdo->exec("UPDATE tbl_pendientes SET fecha_cierre = NULL WHERE estado IN ('ABIERTA','SIN RESPUESTA DEL CLIENTE')");
    echo "Cleanup 2 (ABIERTA/SIN RESPUESTA -> fecha_cierre=NULL): {$n2} filas\n";

    $check = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME='tbl_pendientes' AND COLUMN_NAME='fecha_cierre_real'");
    $check->execute([$config['database']]);
    if ($check->fetchColumn() > 0) {
        $pdo->exec("ALTER TABLE tbl_pendientes DROP COLUMN fecha_cierre_real");
        echo "DROP COLUMN fecha_cierre_real: OK\n";
    } else {
        echo "Columna fecha_cierre_real ya no existe (SKIP).\n";
    }

    echo "\n--- Validación ---\n";
    $q = $pdo->query("SELECT estado, COUNT(*) total, SUM(fecha_plazo IS NOT NULL) con_plazo, SUM(fecha_cierre IS NOT NULL) con_cierre FROM tbl_pendientes GROUP BY estado");
    printf("%-30s %-8s %-12s %-12s\n", 'ESTADO', 'TOTAL', 'CON_PLAZO', 'CON_CIERRE');
    foreach ($q as $r) {
        printf("%-30s %-8s %-12s %-12s\n", $r['estado'], $r['total'], $r['con_plazo'], $r['con_cierre']);
    }

    echo "\nCLEANUP OK.\n";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
