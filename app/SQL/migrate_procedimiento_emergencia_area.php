<?php
/**
 * Script CLI — Módulo Procedimiento de Emergencia por Área
 * Uso: php migrate_procedimiento_emergencia_area.php [local|production]
 *
 * Crea 2 tablas para el entregable de procedimientos de reacción en
 * emergencia específicos por área (piscina/zona húmeda, baño turco,
 * sauna, gym, zona BBQ).
 *
 * Patrón master-detalle:
 *   tbl_procedimiento_emergencia_area       — cabecera
 *   tbl_procedimiento_emergencia_escenario  — N escenarios por área
 *
 * Marco normativo de referencia:
 *   - Decreto 1072/2015 (SG-SST)
 *   - Ley 1209/2008 (seguridad piscinas)
 *   - Resolución 234/2026 (sanitaria piscinas)
 *   - NFPA/NTC aplicables según área
 */

if (php_sapi_name() !== 'cli') {
    die('Este script solo puede ejecutarse desde la línea de comandos.');
}

$env = $argv[1] ?? 'local';

if ($env === 'local') {
    $config = [
        'host' => '127.0.0.1', 'port' => 3306,
        'user' => 'root', 'password' => '',
        'database' => 'propiedad_horizontal', 'ssl' => false,
    ];
} elseif ($env === 'production') {
    $config = [
        'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com', 'port' => 25060,
        'user' => 'cycloid_userdb', 'password' => getenv('DB_PROD_PASS') ?: '',
        'database' => 'propiedad_horizontal', 'ssl' => true,
    ];
} else {
    die("Uso: php migrate_procedimiento_emergencia_area.php [local|production]\n");
}

echo "=== Migración SQL — Procedimiento Emergencia por Área ===\n";
echo "Entorno: " . strtoupper($env) . "\n";
echo "Host: {$config['host']}:{$config['port']}\n---\n";

$mysqli = mysqli_init();
if ($config['ssl']) {
    $mysqli->ssl_set(null, null, null, null, null);
    $mysqli->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
}

$connected = @$mysqli->real_connect(
    $config['host'], $config['user'], $config['password'],
    $config['database'], $config['port'], null,
    $config['ssl'] ? MYSQLI_CLIENT_SSL : 0
);

if (!$connected) {
    die("ERROR de conexión: " . $mysqli->connect_error . "\n");
}

echo "Conexión exitosa.\n\n";

$success = 0; $errors = 0; $total = 0;

$statements = [
    ['desc' => 'CREATE tbl_procedimiento_emergencia_area',
     'sql' => "CREATE TABLE IF NOT EXISTS `tbl_procedimiento_emergencia_area` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `id_cliente` INT NOT NULL,
        `id_consultor` INT NOT NULL,
        `fecha_elaboracion` DATE NOT NULL,
        `area` ENUM('PISCINA','BANO_TURCO','SAUNA','GYM','ZONA_BBQ') NOT NULL,
        `nombre_area_descriptivo` VARCHAR(150) NULL COMMENT 'Ej: Piscina adultos Club House',

        `responsable_area_nombre` VARCHAR(200) NULL,
        `responsable_area_cargo` VARCHAR(150) NULL,
        `responsable_area_contacto` VARCHAR(150) NULL,

        `horario_operacion` VARCHAR(200) NULL,
        `aforo_maximo` INT NULL,

        `telefonos_emergencia` TEXT NULL COMMENT 'Bomberos, Cruz Roja, ambulancia, clínica',
        `recursos_disponibles` TEXT NULL COMMENT 'DEA, botiquín, camilla, radio, salvavidas, etc.',
        `observaciones_contexto` TEXT NULL,

        `marco_normativo` TEXT NULL COMMENT 'Congelado al finalizar',
        `ruta_pdf` VARCHAR(255) NULL,
        `estado` ENUM('borrador','completo') NOT NULL DEFAULT 'borrador',
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        CONSTRAINT `fk_proc_em_area_cliente`
            FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes`(`id_cliente`)
            ON DELETE RESTRICT ON UPDATE CASCADE,
        CONSTRAINT `fk_proc_em_area_consultor`
            FOREIGN KEY (`id_consultor`) REFERENCES `tbl_consultor`(`id_consultor`)
            ON DELETE RESTRICT ON UPDATE CASCADE,

        INDEX `idx_proc_em_area_cliente` (`id_cliente`),
        INDEX `idx_proc_em_area_consultor` (`id_consultor`),
        INDEX `idx_proc_em_area_area` (`area`),
        INDEX `idx_proc_em_area_estado` (`estado`)
     ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"],

    ['desc' => 'CREATE tbl_procedimiento_emergencia_escenario',
     'sql' => "CREATE TABLE IF NOT EXISTS `tbl_procedimiento_emergencia_escenario` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `id_procedimiento` INT NOT NULL,
        `orden` TINYINT NOT NULL DEFAULT 0,
        `escenario_codigo` VARCHAR(60) NOT NULL COMMENT 'Ej: sismo_banistas_en_agua',
        `escenario_nombre` VARCHAR(200) NOT NULL,

        `que_hacer` TEXT NULL,
        `que_no_hacer` TEXT NULL,
        `cuando` TEXT NULL,
        `quien` TEXT NULL,
        `recursos` TEXT NULL,

        `generado_con_ia` TINYINT(1) NOT NULL DEFAULT 0,
        `modelo_ia` VARCHAR(60) NULL COMMENT 'Ej: claude-haiku-4-5-20251001',
        `aprobado_por_consultor` TINYINT(1) NOT NULL DEFAULT 0,
        `aprobado_at` DATETIME NULL,

        `observaciones` TEXT NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        CONSTRAINT `fk_proc_em_esc_procedimiento`
            FOREIGN KEY (`id_procedimiento`) REFERENCES `tbl_procedimiento_emergencia_area`(`id`)
            ON DELETE CASCADE ON UPDATE CASCADE,

        INDEX `idx_proc_em_esc_procedimiento` (`id_procedimiento`)
     ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"],
];

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
echo "Exitosas: {$success}\nErrores: {$errors}\nTotal: {$total}\n";

if ($errors === 0) {
    echo "MIGRACIÓN COMPLETADA SIN ERRORES.\n";
} else {
    echo "HAY ERRORES — revisar antes de continuar.\n";
}

$mysqli->close();
