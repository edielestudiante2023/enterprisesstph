<?php
/**
 * Script CLI para crear tabla del módulo Hoja de Vida Brigadista
 * Uso: php migrate_hv_brigadista.php [local|production]
 *
 * Crea:
 *   - tbl_hv_brigadista (tabla única, patrón PLANO, acceso público)
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
    die("Uso: php migrate_hv_brigadista.php [local|production]\n");
}

echo "=== Migración SQL - Módulo Hoja de Vida Brigadista ===\n";
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
        'desc' => 'CREATE TABLE tbl_hv_brigadista',
        'sql'  => "CREATE TABLE IF NOT EXISTS `tbl_hv_brigadista` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `id_cliente` INT NOT NULL,

            -- Registro
            `fecha_registro` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `fecha_inscripcion` DATE NULL,

            -- Datos personales
            `foto_brigadista` VARCHAR(500) NULL COMMENT 'Path a foto del brigadista',
            `nombre_completo` VARCHAR(255) NOT NULL,
            `documento_identidad` VARCHAR(20) NOT NULL,
            `f_nacimiento` DATE NULL,
            `email` VARCHAR(255) NULL,
            `telefono` VARCHAR(20) NULL,
            `direccion_residencia` VARCHAR(500) NULL,
            `edad` INT NULL,
            `eps` VARCHAR(255) NULL,
            `peso` DECIMAL(5,1) NULL,
            `estatura` DECIMAL(5,1) NULL,
            `rh` VARCHAR(5) NULL COMMENT 'O+, O-, A+, A-, B+, B-, AB+, AB-',

            -- Estudios (flat, max 3)
            `estudios_1` VARCHAR(255) NULL,
            `lugar_estudio_1` VARCHAR(255) NULL,
            `anio_estudio_1` INT NULL,
            `estudios_2` VARCHAR(255) NULL,
            `lugar_estudio_2` VARCHAR(255) NULL,
            `anio_estudio_2` INT NULL,
            `estudios_3` VARCHAR(255) NULL,
            `lugar_estudio_3` VARCHAR(255) NULL,
            `anio_estudio_3` INT NULL,

            -- Salud
            `enfermedades_importantes` TEXT NULL,
            `medicamentos` TEXT NULL,

            -- Cuestionario médico (14 SI/NO)
            `cardiaca` ENUM('SI','NO') NULL,
            `pechoactividad` ENUM('SI','NO') NULL,
            `dolorpecho` ENUM('SI','NO') NULL,
            `conciencia` ENUM('SI','NO') NULL,
            `huesos` ENUM('SI','NO') NULL,
            `medicamentos_bool` ENUM('SI','NO') NULL,
            `actividadfisica` ENUM('SI','NO') NULL,
            `convulsiones` ENUM('SI','NO') NULL,
            `vertigo` ENUM('SI','NO') NULL,
            `oidos` ENUM('SI','NO') NULL,
            `lugarescerrados` ENUM('SI','NO') NULL,
            `miedoalturas` ENUM('SI','NO') NULL,
            `haceejercicio` ENUM('SI','NO') NULL,
            `miedo_ver_sangre` ENUM('SI','NO') NULL,

            -- Extras salud
            `restricciones_medicas` TEXT NULL,
            `deporte_semana` TEXT NULL,

            -- Firma
            `firma` VARCHAR(500) NULL COMMENT 'Path a imagen de firma',

            -- Estado y PDF
            `estado` ENUM('borrador', 'completo') NOT NULL DEFAULT 'borrador',
            `ruta_pdf` VARCHAR(500) NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            CONSTRAINT `fk_hv_brig_cliente`
                FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes`(`id_cliente`)
                ON DELETE RESTRICT ON UPDATE CASCADE,

            INDEX `idx_hv_brig_cliente` (`id_cliente`),
            INDEX `idx_hv_brig_estado` (`estado`),
            INDEX `idx_hv_brig_doc` (`documento_identidad`)
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
