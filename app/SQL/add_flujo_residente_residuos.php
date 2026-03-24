<?php
/**
 * Migración: agrega columna flujo_residente a tbl_programa_residuos
 * Uso: php add_flujo_residente_residuos.php [local|production]
 *      DB_PROD_PASS=xxx php add_flujo_residente_residuos.php production
 */

$env = $argv[1] ?? 'local';

if ($env === 'production') {
    $host   = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port   = 25060;
    $dbname = 'propiedad_horizontal';
    $user   = 'cycloid_userdb';
    $pass   = getenv('DB_PROD_PASS');
    $ssl    = true;
} else {
    $host   = '127.0.0.1';
    $port   = 3306;
    $dbname = 'propiedad_horizontal';
    $user   = 'root';
    $pass   = '';
    $ssl    = false;
}

$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

if ($ssl) {
    $options[PDO::MYSQL_ATTR_SSL_CA]          = true;
    $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
}

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "[{$env}] Conectado a {$dbname}\n";
} catch (PDOException $e) {
    echo "ERROR conexión: " . $e->getMessage() . "\n";
    exit(1);
}

// Verificar si la columna ya existe
$check = $pdo->query("SHOW COLUMNS FROM tbl_programa_residuos LIKE 'flujo_residente'")->fetchAll();
if (!empty($check)) {
    echo "[{$env}] La columna flujo_residente ya existe. Nada que hacer.\n";
    exit(0);
}

$sql = "ALTER TABLE tbl_programa_residuos ADD COLUMN flujo_residente TEXT NULL COMMENT 'Flujo del residente para disposición de residuos' AFTER nombre_responsable";

$pdo->exec($sql);
echo "[{$env}] OK — columna flujo_residente agregada a tbl_programa_residuos.\n";
