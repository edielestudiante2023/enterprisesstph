<?php
/**
 * Script CLI para crear tablas del módulo Inspección de Gabinetes Contra Incendio
 * Uso: php migrate_inspeccion_gabinetes.php [local|production]
 *
 * Crea:
 *   - tbl_inspeccion_gabinetes (tabla principal con datos generales + detectores)
 *   - tbl_gabinete_detalle (N gabinetes individuales inspeccionados)
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
    die("Uso: php migrate_inspeccion_gabinetes.php [local|production]\n");
}

echo "=== Migración SQL - Módulo Inspección Gabinetes ===\n";
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
        'desc' => 'CREATE TABLE tbl_inspeccion_gabinetes',
        'sql'  => "CREATE TABLE IF NOT EXISTS `tbl_inspeccion_gabinetes` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `id_cliente` INT NOT NULL,
            `id_consultor` INT NOT NULL,
            `fecha_inspeccion` DATE NOT NULL,
            -- Gabinetes contra incendio (general)
            `tiene_gabinetes` ENUM('SI','NO') NOT NULL DEFAULT 'SI',
            `entregados_constructora` ENUM('SI','NO') NOT NULL DEFAULT 'SI',
            `cantidad_gabinetes` INT NOT NULL DEFAULT 0,
            `elementos_gabinete` TEXT NULL COMMENT 'Elementos que contiene cada gabinete',
            `ubicacion_gabinetes` TEXT NULL COMMENT 'Ubicación detallada de los gabinetes',
            `estado_senalizacion_gab` TEXT NULL COMMENT 'Estado de la señalización',
            `foto_gab_1` VARCHAR(255) NULL,
            `foto_gab_2` VARCHAR(255) NULL,
            `observaciones_gabinetes` TEXT NULL,
            -- Detectores de humo (sección plana)
            `tiene_detectores` ENUM('SI','NO') NOT NULL DEFAULT 'SI',
            `detectores_entregados` ENUM('SI','NO') NOT NULL DEFAULT 'SI',
            `cantidad_detectores` INT NOT NULL DEFAULT 0,
            `ubicacion_detectores` TEXT NULL,
            `foto_det_1` VARCHAR(255) NULL,
            `foto_det_2` VARCHAR(255) NULL,
            `observaciones_detectores` TEXT NULL,
            -- Resultado
            `ruta_pdf` VARCHAR(255) NULL,
            `estado` ENUM('borrador', 'completo') NOT NULL DEFAULT 'borrador',
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            CONSTRAINT `fk_insp_gab_cliente`
                FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes`(`id_cliente`)
                ON DELETE RESTRICT ON UPDATE CASCADE,
            CONSTRAINT `fk_insp_gab_consultor`
                FOREIGN KEY (`id_consultor`) REFERENCES `tbl_consultor`(`id_consultor`)
                ON DELETE RESTRICT ON UPDATE CASCADE,

            INDEX `idx_insp_gab_cliente` (`id_cliente`),
            INDEX `idx_insp_gab_consultor` (`id_consultor`),
            INDEX `idx_insp_gab_estado` (`estado`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ],
    [
        'desc' => 'CREATE TABLE tbl_gabinete_detalle',
        'sql'  => "CREATE TABLE IF NOT EXISTS `tbl_gabinete_detalle` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `id_inspeccion` INT NOT NULL,
            `numero` INT NOT NULL DEFAULT 1,
            `ubicacion` VARCHAR(255) NULL,
            `tiene_manguera` ENUM('SI','NO') NOT NULL DEFAULT 'SI',
            `tiene_hacha` ENUM('SI','NO') NOT NULL DEFAULT 'SI',
            `tiene_extintor` ENUM('SI','NO') NOT NULL DEFAULT 'NO',
            `tiene_valvula` ENUM('SI','NO') NOT NULL DEFAULT 'SI',
            `tiene_boquilla` ENUM('SI','NO') NOT NULL DEFAULT 'SI',
            `tiene_llave_spanner` ENUM('SI','NO') NOT NULL DEFAULT 'NO',
            `estado` VARCHAR(50) NOT NULL DEFAULT 'BUENO',
            `senalizacion` VARCHAR(50) NOT NULL DEFAULT 'BUENO',
            `foto` VARCHAR(255) NULL COMMENT 'Ruta foto evidencia',
            `observaciones` TEXT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

            CONSTRAINT `fk_gab_det_inspeccion`
                FOREIGN KEY (`id_inspeccion`) REFERENCES `tbl_inspeccion_gabinetes`(`id`)
                ON DELETE CASCADE ON UPDATE CASCADE,

            INDEX `idx_gab_det_inspeccion` (`id_inspeccion`)
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
