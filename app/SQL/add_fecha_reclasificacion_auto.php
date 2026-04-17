<?php
/**
 * Migración: agregar fecha_reclasificacion_auto a tbl_pendientes.
 * Marca cuándo el cron reclasifica un pendiente a SIN RESPUESTA DEL CLIENTE por >90 días sin gestión.
 * Uso: php add_fecha_reclasificacion_auto.php [local|production]
 * Idempotente: chequea columna antes de crear.
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
    die("Uso: php add_fecha_reclasificacion_auto.php [local|production]\n");
}

echo "=== Migración fecha_reclasificacion_auto en tbl_pendientes ===\n";
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

    $check = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME='tbl_pendientes' AND COLUMN_NAME='fecha_reclasificacion_auto'");
    $check->execute([$config['database']]);
    if ($check->fetchColumn() > 0) {
        echo "Columna fecha_reclasificacion_auto ya existe (SKIP).\n";
    } else {
        $pdo->exec("ALTER TABLE tbl_pendientes ADD COLUMN fecha_reclasificacion_auto DATE NULL AFTER fecha_cierre_real");
        echo "ADD COLUMN fecha_reclasificacion_auto: OK\n";
    }

    $q = $pdo->query("SELECT estado, COUNT(*) total, SUM(fecha_reclasificacion_auto IS NOT NULL) auto FROM tbl_pendientes GROUP BY estado");
    printf("\n%-30s %-8s %-10s\n", 'ESTADO', 'TOTAL', 'AUTO_CLSF');
    foreach ($q as $r) {
        printf("%-30s %-8s %-10s\n", $r['estado'], $r['total'], $r['auto']);
    }

    echo "\nMIGRACIÓN OK.\n";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
