<?php
/**
 * Migración: Agregar id_cronograma_capacitacion a tbl_reporte_capacitacion
 * + agregar id_reporte_capacitacion a tbl_cronog_capacitacion
 * Para vincular reportes ejecutados con el cronograma de capacitación.
 *
 * Uso LOCAL:    php migrate_cronograma_link.php
 * Uso PROD:     DB_PROD_PASS=xxx php migrate_cronograma_link.php production
 */

$isProduction = ($argv[1] ?? '') === 'production';

if ($isProduction) {
    $password = getenv('DB_PROD_PASS');
    if (!$password) {
        echo "ERROR: Variable DB_PROD_PASS no definida.\n";
        echo "Uso: DB_PROD_PASS=xxx php migrate_cronograma_link.php production\n";
        exit(1);
    }
    $config = [
        'host'     => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port'     => 25060,
        'dbname'   => 'propiedad_horizontal',
        'user'     => 'cycloid_userdb',
        'password' => $password,
        'ssl'      => true,
    ];
    echo "=== PRODUCCIÓN ===\n";
} else {
    $config = [
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'dbname'   => 'propiedad_horizontal',
        'user'     => 'root',
        'password' => '',
        'ssl'      => false,
    ];
    echo "=== LOCAL ===\n";
}

$dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset=utf8mb4";
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
if ($config['ssl']) {
    $options[PDO::MYSQL_ATTR_SSL_CA] = true;
    $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
}

try {
    $pdo = new PDO($dsn, $config['user'], $config['password'], $options);
    echo "Conectado a {$config['host']}:{$config['port']}/{$config['dbname']}\n";
} catch (PDOException $e) {
    echo "ERROR conexión: " . $e->getMessage() . "\n";
    exit(1);
}

// 1. Agregar id_cronograma_capacitacion a tbl_reporte_capacitacion
$colExists = $pdo->query("SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = '{$config['dbname']}'
    AND TABLE_NAME = 'tbl_reporte_capacitacion'
    AND COLUMN_NAME = 'id_cronograma_capacitacion'")->fetchColumn();

if (!$colExists) {
    $pdo->exec("ALTER TABLE tbl_reporte_capacitacion
        ADD COLUMN id_cronograma_capacitacion INT NULL DEFAULT NULL AFTER id_consultor,
        ADD INDEX idx_rep_cap_cronograma (id_cronograma_capacitacion)");
    echo "OK — Columna id_cronograma_capacitacion agregada a tbl_reporte_capacitacion\n";
} else {
    echo "SKIP — id_cronograma_capacitacion ya existe en tbl_reporte_capacitacion\n";
}

// 2. Agregar id_reporte_capacitacion a tbl_cronog_capacitacion (link inverso)
$colExists2 = $pdo->query("SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = '{$config['dbname']}'
    AND TABLE_NAME = 'tbl_cronog_capacitacion'
    AND COLUMN_NAME = 'id_reporte_capacitacion'")->fetchColumn();

if (!$colExists2) {
    $pdo->exec("ALTER TABLE tbl_cronog_capacitacion
        ADD COLUMN id_reporte_capacitacion INT NULL DEFAULT NULL,
        ADD INDEX idx_cronog_reporte (id_reporte_capacitacion)");
    echo "OK — Columna id_reporte_capacitacion agregada a tbl_cronog_capacitacion\n";
} else {
    echo "SKIP — id_reporte_capacitacion ya existe en tbl_cronog_capacitacion\n";
}

echo "\n¡Migración completada!\n";
