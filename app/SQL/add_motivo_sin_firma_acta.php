<?php
/**
 * Migración: agregar columna motivo_sin_firma a tbl_acta_visita
 * Uso: php add_motivo_sin_firma_acta.php [production]
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
    $host = 'localhost';
    $port = 3306;
    $db   = 'empresas_sst';
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
    echo "Conectado a [{$env}] {$db}\n";

    // Verificar si ya existe
    $check = $pdo->query("SHOW COLUMNS FROM tbl_acta_visita LIKE 'motivo_sin_firma'");
    if ($check->rowCount() > 0) {
        echo "Columna motivo_sin_firma ya existe. Nada que hacer.\n";
        exit(0);
    }

    $pdo->exec("ALTER TABLE tbl_acta_visita ADD COLUMN motivo_sin_firma VARCHAR(255) NULL DEFAULT NULL AFTER firma_consultor");
    echo "OK: columna motivo_sin_firma agregada a tbl_acta_visita\n";

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
