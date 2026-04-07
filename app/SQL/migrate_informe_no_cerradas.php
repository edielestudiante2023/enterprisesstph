<?php
/**
 * Migración: Agregar actividades_no_cerradas_pta a tbl_informe_avances
 *
 * Uso:
 *   LOCAL:       php app/SQL/migrate_informe_no_cerradas.php
 *   PRODUCCIÓN:  DB_PROD_PASS=xxx php app/SQL/migrate_informe_no_cerradas.php production
 */

$env = $argv[1] ?? 'local';

if ($env === 'production') {
    $pass = getenv('DB_PROD_PASS');
    if (!$pass) {
        echo "ERROR: Variable DB_PROD_PASS no definida.\n";
        exit(1);
    }
    $host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port = 25060;
    $db   = 'propiedad_horizontal';
    $user = 'cycloid_userdb';
    $ssl  = true;
    echo "=== PRODUCCIÓN ===\n";
} else {
    $host = '127.0.0.1';
    $port = 3306;
    $db   = 'propiedad_horizontal';
    $user = 'root';
    $pass = '';
    $ssl  = false;
    echo "=== LOCAL ===\n";
}

$dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
$opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
if ($ssl) {
    $opts[PDO::MYSQL_ATTR_SSL_CA] = true;
    $opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
}

try {
    $pdo = new PDO($dsn, $user, $pass, $opts);
    echo "Conectado a {$db}@{$host}:{$port}\n";
} catch (PDOException $e) {
    echo "ERROR conexión: " . $e->getMessage() . "\n";
    exit(1);
}

$col = 'actividades_no_cerradas_pta';
$check = $pdo->prepare(
    "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = :db AND TABLE_NAME = 'tbl_informe_avances' AND COLUMN_NAME = :col"
);
$check->execute(['db' => $db, 'col' => $col]);

if ($check->fetchColumn() > 0) {
    echo "  Columna '{$col}' ya existe — omitida.\n";
} else {
    $pdo->exec("ALTER TABLE tbl_informe_avances ADD COLUMN {$col} TEXT NULL AFTER actividades_cerradas_periodo");
    echo "  Columna '{$col}' agregada OK.\n";
}

echo "\nMigración completada.\n";
