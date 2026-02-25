<?php
/**
 * Script CLI para crear tabla del módulo Evaluación Simulacro de Evacuación
 * Uso: php migrate_evaluacion_simulacro.php [local|production]
 *
 * Crea:
 *   - tbl_evaluacion_simulacro (tabla única, patrón PLANO, acceso público)
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
    die("Uso: php migrate_evaluacion_simulacro.php [local|production]\n");
}

echo "=== Migración SQL - Módulo Evaluación Simulacro de Evacuación ===\n";
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
        'desc' => 'CREATE TABLE tbl_evaluacion_simulacro',
        'sql'  => "CREATE TABLE IF NOT EXISTS `tbl_evaluacion_simulacro` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `id_cliente` INT NOT NULL,

            -- Info general
            `fecha` DATE NOT NULL,
            `direccion` VARCHAR(500) NULL,
            `evento_simulado` ENUM('Sismo','Incendio','Evacuación') NULL,
            `alcance_simulacro` ENUM('Total','Parcial') NULL,
            `tipo_evacuacion` VARCHAR(100) NULL,
            `personal_no_evacua` VARCHAR(255) NULL,
            `tipo_alarma` VARCHAR(500) NULL COMMENT 'Valores separados por coma',
            `distintivos_brigadistas` VARCHAR(500) NULL COMMENT 'Valores separados por coma',
            `puntos_encuentro` VARCHAR(500) NULL,
            `recurso_humano` VARCHAR(500) NULL,
            `equipos_emergencia` TEXT NULL COMMENT 'Valores separados por coma',

            -- Brigadista líder
            `nombre_brigadista_lider` VARCHAR(255) NULL,
            `email_brigadista_lider` VARCHAR(255) NULL,
            `whatsapp_brigadista_lider` VARCHAR(20) NULL,

            -- Fotos evidencia
            `imagen_1` VARCHAR(500) NULL,
            `imagen_2` VARCHAR(500) NULL,

            -- Cronómetro (9 timestamps)
            `hora_inicio` DATETIME NULL,
            `alistamiento_recursos` DATETIME NULL,
            `asumir_roles` DATETIME NULL,
            `suena_alarma` DATETIME NULL,
            `distribucion_roles` DATETIME NULL,
            `llegada_punto_encuentro` DATETIME NULL,
            `agrupacion_por_afinidad` DATETIME NULL,
            `conteo_personal` DATETIME NULL,
            `agradecimiento_y_cierre` DATETIME NULL,
            `tiempo_total` VARCHAR(20) NULL COMMENT 'HH:MM:SS calculado',

            -- Evaluación cuantitativa (5 criterios 1-10)
            `alarma_efectiva` TINYINT NULL,
            `orden_evacuacion` TINYINT NULL,
            `liderazgo_brigadistas` TINYINT NULL,
            `organizacion_punto_encuentro` TINYINT NULL,
            `participacion_general` TINYINT NULL,
            `evaluacion_cuantitativa` VARCHAR(10) NULL COMMENT 'Ej: 8.2/10',
            `evaluacion_cualitativa` TEXT NULL,

            -- Observaciones
            `observaciones` TEXT NULL,

            -- Conteo de evacuados
            `hombre` INT NOT NULL DEFAULT 0,
            `mujer` INT NOT NULL DEFAULT 0,
            `ninos` INT NOT NULL DEFAULT 0,
            `adultos_mayores` INT NOT NULL DEFAULT 0,
            `discapacidad` INT NOT NULL DEFAULT 0,
            `mascotas` INT NOT NULL DEFAULT 0,
            `total` INT NOT NULL DEFAULT 0,

            -- Estado y PDF
            `estado` ENUM('borrador', 'completo') NOT NULL DEFAULT 'borrador',
            `ruta_pdf` VARCHAR(255) NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            CONSTRAINT `fk_eval_sim_cliente`
                FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes`(`id_cliente`)
                ON DELETE RESTRICT ON UPDATE CASCADE,

            INDEX `idx_eval_sim_cliente` (`id_cliente`),
            INDEX `idx_eval_sim_estado` (`estado`),
            INDEX `idx_eval_sim_fecha` (`fecha`)
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
