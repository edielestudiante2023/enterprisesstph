<?php
/**
 * Script CLI para crear tabla del módulo Inspección Equipos de Comunicación
 * Uso: php migrate_inspeccion_comunicaciones.php [local|production]
 *
 * Crea:
 *   - tbl_inspeccion_comunicaciones (tabla única plana, 8 equipos x 2 campos)
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
    die("Uso: php migrate_inspeccion_comunicaciones.php [local|production]\n");
}

echo "=== Migración SQL - Módulo Inspección Equipos de Comunicación ===\n";
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
        'desc' => 'CREATE TABLE tbl_inspeccion_comunicaciones',
        'sql'  => "CREATE TABLE IF NOT EXISTS `tbl_inspeccion_comunicaciones` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `id_cliente` INT NOT NULL,
            `id_consultor` INT NOT NULL,
            `fecha_inspeccion` DATE NOT NULL,
            -- Telefono Fijo
            `cant_telefono_fijo` INT NOT NULL DEFAULT 0,
            `obs_telefono_fijo` TEXT NULL,
            -- Telefonia Celular
            `cant_telefonia_celular` INT NOT NULL DEFAULT 0,
            `obs_telefonia_celular` TEXT NULL,
            -- Radio de Onda Corta
            `cant_radio_onda_corta` INT NOT NULL DEFAULT 0,
            `obs_radio_onda_corta` TEXT NULL,
            -- Software Citofonia
            `cant_software_citofonia` INT NOT NULL DEFAULT 0,
            `obs_software_citofonia` TEXT NULL,
            -- Sistemas de Megafonia
            `cant_megafonia` INT NOT NULL DEFAULT 0,
            `obs_megafonia` TEXT NULL,
            -- Sistemas de CCTV con Audio
            `cant_cctv_audio` INT NOT NULL DEFAULT 0,
            `obs_cctv_audio` TEXT NULL,
            -- Sistemas de Alarma con Comunicacion Incorporada
            `cant_alarma_comunicacion` INT NOT NULL DEFAULT 0,
            `obs_alarma_comunicacion` TEXT NULL,
            -- Sistemas VOIP
            `cant_voip` INT NOT NULL DEFAULT 0,
            `obs_voip` TEXT NULL,
            -- Fotos evidencia
            `foto_1` VARCHAR(255) NULL,
            `foto_2` VARCHAR(255) NULL,
            -- Resultado
            `observaciones_finales` TEXT NULL,
            `ruta_pdf` VARCHAR(255) NULL,
            `estado` ENUM('borrador', 'completo') NOT NULL DEFAULT 'borrador',
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            CONSTRAINT `fk_insp_com_cliente`
                FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes`(`id_cliente`)
                ON DELETE RESTRICT ON UPDATE CASCADE,
            CONSTRAINT `fk_insp_com_consultor`
                FOREIGN KEY (`id_consultor`) REFERENCES `tbl_consultor`(`id_consultor`)
                ON DELETE RESTRICT ON UPDATE CASCADE,

            INDEX `idx_insp_com_cliente` (`id_cliente`),
            INDEX `idx_insp_com_consultor` (`id_consultor`),
            INDEX `idx_insp_com_estado` (`estado`)
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
