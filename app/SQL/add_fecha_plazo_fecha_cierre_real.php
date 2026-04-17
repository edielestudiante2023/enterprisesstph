<?php
/**
 * Migración: agregar fecha_plazo y fecha_cierre_real a tbl_pendientes.
 * Separa la semántica dual de fecha_cierre (plazo vs. fecha real de cierre).
 * Uso: php add_fecha_plazo_fecha_cierre_real.php [local|production]
 * Idempotente: chequea columnas/backup antes de crear.
 */

if (php_sapi_name() !== 'cli') {
    die("Solo CLI.\n");
}

$env = $argv[1] ?? 'local';

if ($env === 'local') {
    $config = [
        'host' => '127.0.0.1', 'port' => 3306,
        'user' => 'root', 'password' => '',
        'database' => 'propiedad_horizontal', 'ssl' => false,
    ];
} elseif ($env === 'production') {
    $config = [
        'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port' => 25060, 'user' => 'cycloid_userdb',
        'password' => getenv('DB_PROD_PASS') ?: '',
        'database' => 'propiedad_horizontal', 'ssl' => true,
    ];
} else {
    die("Uso: php add_fecha_plazo_fecha_cierre_real.php [local|production]\n");
}

echo "=== Migración fecha_plazo + fecha_cierre_real en tbl_pendientes ===\n";
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

    try { $pdo->exec("SET SESSION sql_require_primary_key = 0"); } catch (PDOException $e) { /* MySQL sin esta var (p.ej. local) */ }
    $pdo->exec("SET SESSION sql_mode = 'ALLOW_INVALID_DATES'");

    $backupTable = 'tbl_pendientes_bk_20260417';
    $exists = $pdo->query("SHOW TABLES LIKE '{$backupTable}'")->fetchColumn();
    if ($exists) {
        echo "Backup {$backupTable} ya existe (SKIP).\n";
    } else {
        $pdo->exec("CREATE TABLE {$backupTable} LIKE tbl_pendientes");
        $pdo->exec("INSERT INTO {$backupTable} SELECT * FROM tbl_pendientes");
        $n = $pdo->query("SELECT COUNT(*) FROM {$backupTable}")->fetchColumn();
        echo "Backup creado: {$backupTable} ({$n} filas).\n";
    }

    foreach (['fecha_plazo', 'fecha_cierre_real'] as $col) {
        $c = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME='tbl_pendientes' AND COLUMN_NAME=?");
        $c->execute([$config['database'], $col]);
        if ($c->fetchColumn() > 0) {
            echo "Columna {$col} ya existe (SKIP).\n";
            continue;
        }
        $after = $col === 'fecha_plazo' ? 'fecha_cierre' : 'fecha_plazo';
        $pdo->exec("ALTER TABLE tbl_pendientes ADD COLUMN {$col} DATE NULL AFTER {$after}");
        echo "ADD COLUMN {$col}: OK\n";
    }

    $n1 = $pdo->exec("UPDATE tbl_pendientes SET fecha_plazo = fecha_cierre WHERE estado = 'ABIERTA' AND fecha_plazo IS NULL AND fecha_cierre IS NOT NULL AND CAST(fecha_cierre AS CHAR) <> '0000-00-00' AND fecha_cierre >= '2000-01-01'");
    echo "Backfill 1 (ABIERTAS válidas → fecha_plazo): {$n1} filas\n";

    $n2 = $pdo->exec("UPDATE tbl_pendientes SET fecha_plazo = '2026-05-30' WHERE estado = 'ABIERTA' AND fecha_plazo IS NULL AND (fecha_cierre IS NULL OR CAST(fecha_cierre AS CHAR) = '0000-00-00')");
    echo "Backfill 2 (ABIERTAS basura → 2026-05-30): {$n2} filas\n";

    $n3 = $pdo->exec("UPDATE tbl_pendientes SET fecha_cierre_real = fecha_cierre WHERE estado IN ('CERRADA','CERRADA POR FIN CONTRATO') AND fecha_cierre_real IS NULL AND fecha_cierre IS NOT NULL AND CAST(fecha_cierre AS CHAR) <> '0000-00-00'");
    echo "Backfill 3 (CERRADAs → fecha_cierre_real): {$n3} filas\n";

    echo "\n--- Validación post-migración ---\n";
    $q = $pdo->query("SELECT estado, COUNT(*) total, SUM(fecha_plazo IS NOT NULL) con_plazo, SUM(fecha_cierre_real IS NOT NULL) con_cierre_real FROM tbl_pendientes GROUP BY estado");
    printf("%-30s %-8s %-12s %-18s\n", 'ESTADO', 'TOTAL', 'CON_PLAZO', 'CON_CIERRE_REAL');
    foreach ($q as $r) {
        printf("%-30s %-8s %-12s %-18s\n", $r['estado'], $r['total'], $r['con_plazo'], $r['con_cierre_real']);
    }

    echo "\nMIGRACIÓN OK.\n";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
