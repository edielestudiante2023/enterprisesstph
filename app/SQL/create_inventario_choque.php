<?php
/**
 * Script CLI para crear tablas del modulo "Inventario de Fotos de Choque"
 * Uso:
 *   php app/SQL/create_inventario_choque.php local
 *   DB_PROD_PASS=xxx php app/SQL/create_inventario_choque.php production
 */

if (php_sapi_name() !== 'cli') {
    die('Este script solo puede ejecutarse desde la linea de comandos.');
}

$env = $argv[1] ?? 'local';

if ($env === 'local') {
    $config = [
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'user'     => 'root',
        'password' => '',
        'database' => 'propiedad_horizontal',
        'ssl'      => false,
    ];
} elseif ($env === 'production') {
    $config = [
        'host'     => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port'     => 25060,
        'user'     => 'cycloid_userdb',
        'password' => getenv('DB_PROD_PASS') ?: '',
        'database' => 'propiedad_horizontal',
        'ssl'      => true,
    ];
} else {
    die("Uso: php create_inventario_choque.php [local|production]\n");
}

echo "=== Migracion SQL - Inventario de Fotos de Choque ===\n";
echo "Entorno: " . strtoupper($env) . "\n";
echo "Host: {$config['host']}:{$config['port']}\n";
echo "Database: {$config['database']}\n";
echo "---\n";

$mysqli = mysqli_init();
if ($config['ssl']) {
    $mysqli->ssl_set(null, null, null, null, null);
    $mysqli->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
}
$connected = @$mysqli->real_connect(
    $config['host'],
    $config['user'],
    $config['password'],
    $config['database'],
    $config['port'],
    null,
    $config['ssl'] ? MYSQLI_CLIENT_SSL : 0
);
if (!$connected) {
    die("ERROR de conexion: " . $mysqli->connect_error . "\n");
}
echo "Conexion exitosa.\n\n";

$statements = [
    [
        'desc' => 'CREATE TABLE tbl_inventario_choque',
        'sql'  => "CREATE TABLE IF NOT EXISTS `tbl_inventario_choque` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `id_cliente` INT NOT NULL,
            `id_consultor` INT NULL,
            `fecha_captura` DATE NOT NULL,
            `observaciones` TEXT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_cliente` (`id_cliente`),
            KEY `idx_consultor` (`id_consultor`),
            KEY `idx_fecha` (`fecha_captura`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    ],
    [
        'desc' => 'CREATE TABLE tbl_inventario_choque_items',
        'sql'  => "CREATE TABLE IF NOT EXISTS `tbl_inventario_choque_items` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `id_inventario` INT NOT NULL,
            `categoria` VARCHAR(80) NOT NULL,
            `item` VARCHAR(150) NOT NULL,
            `orden` INT NOT NULL DEFAULT 0,
            `marcado` TINYINT(1) NOT NULL DEFAULT 0,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_inventario` (`id_inventario`),
            CONSTRAINT `fk_inv_choque_items` FOREIGN KEY (`id_inventario`)
                REFERENCES `tbl_inventario_choque` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    ],
];

$success = 0; $errors = 0; $total = 0;
foreach ($statements as $stmt) {
    $total++;
    echo "[{$total}] {$stmt['desc']}... ";
    if ($mysqli->query($stmt['sql'])) {
        echo "OK\n";
        $success++;
    } else {
        echo "ERROR: " . $mysqli->error . "\n";
        $errors++;
    }
}

echo "\n=== RESULTADO ===\n";
echo "Exitosas: {$success}\n";
echo "Errores: {$errors}\n";
echo "Total: {$total}\n";
echo $errors === 0 ? "MIGRACION COMPLETADA SIN ERRORES.\n" : "HAY ERRORES - REVISAR.\n";

$mysqli->close();
