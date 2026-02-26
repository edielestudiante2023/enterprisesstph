<?php
/**
 * Script CLI para crear tabla del módulo Plan de Saneamiento Básico
 * Uso: php migrate_plan_saneamiento.php [local|production]
 */

if (php_sapi_name() !== 'cli') {
    die('Este script solo puede ejecutarse desde la línea de comandos.');
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
    die("Uso: php migrate_plan_saneamiento.php [local|production]\n");
}

echo "=== Migración SQL - Módulo Plan de Saneamiento Básico ===\n";
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
    die("ERROR de conexión: " . $mysqli->connect_error . "\n");
}

echo "Conexión exitosa.\n\n";

$success = 0;
$errors = 0;
$total = 0;

$createStatements = [
    [
        'desc' => 'CREATE TABLE tbl_plan_saneamiento',
        'sql'  => "CREATE TABLE IF NOT EXISTS `tbl_plan_saneamiento` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `id_cliente` INT NOT NULL,
            `id_consultor` INT NOT NULL,
            `fecha_programa` DATE NOT NULL,
            `nombre_responsable` VARCHAR(255) NULL,
            `ruta_pdf` VARCHAR(255) NULL,
            `estado` ENUM('borrador', 'completo') NOT NULL DEFAULT 'borrador',
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            CONSTRAINT `fk_plan_san_cliente`
                FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes`(`id_cliente`)
                ON DELETE RESTRICT ON UPDATE CASCADE,
            CONSTRAINT `fk_plan_san_consultor`
                FOREIGN KEY (`id_consultor`) REFERENCES `tbl_consultor`(`id_consultor`)
                ON DELETE RESTRICT ON UPDATE CASCADE,

            INDEX `idx_plan_san_cliente` (`id_cliente`),
            INDEX `idx_plan_san_consultor` (`id_consultor`),
            INDEX `idx_plan_san_estado` (`estado`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ],
];

foreach ($createStatements as $stmt) {
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

if ($errors === 0) {
    echo "MIGRACIÓN COMPLETADA SIN ERRORES.\n";
} else {
    echo "HAY ERRORES - REVISAR ANTES DE CONTINUAR.\n";
}

$mysqli->close();
