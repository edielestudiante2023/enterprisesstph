<?php
/**
 * Migración: agrega 'prospecto' al ENUM estado de tbl_clientes
 * Ejecutar: DB_PROD_PASS=xxx php add_estado_prospecto.php [production]
 */

$isProd = in_array('production', $argv ?? []);

if ($isProd) {
    $host   = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port   = 25060;
    $dbname = 'propiedad_horizontal';
    $user   = 'cycloid_userdb';
    $pass   = getenv('DB_PROD_PASS');
} else {
    $host   = '127.0.0.1';
    $port   = 3306;
    $dbname = 'propiedad_horizontal';
    $user   = 'root';
    $pass   = '';
}

$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (Exception $e) {
    die("Conexión fallida: " . $e->getMessage() . "\n");
}

$sql = "ALTER TABLE tbl_clientes
        MODIFY COLUMN estado ENUM('activo','inactivo','pendiente','prospecto') NOT NULL DEFAULT 'activo'";

try {
    $pdo->exec($sql);
    echo "OK: columna estado de tbl_clientes actualizada con valor 'prospecto'\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
