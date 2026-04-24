<?php
/**
 * Script CLI — Modulo Inspeccion de Gimnasio (FT-SST-250)
 * Uso: php migrate_inspeccion_gimnasio.php [local|production]
 *
 * Alcance: riesgos locativos puros del gimnasio de copropiedad (no captura
 * dotacion EPP del instructor ni mantenimiento de equipos).
 *
 * Fundamento normativo:
 *   - Ley 675 de 2001 (regimen propiedad horizontal)
 *   - Resolucion 2400 de 1979 (higiene y seguridad en establecimientos)
 *   - Decreto 1072 de 2015 (SG-SST)
 *   - NTC 1700 (medios de evacuacion)
 *   - NFPA 101 (Life Safety Code)
 *
 * Crea 3 tablas:
 *   1. tbl_inspeccion_gimnasio              — master de la visita
 *   2. tbl_gimnasio_evidencia_maestro       — catalogo categorias evidencia
 *   3. tbl_gimnasio_detalle_evidencia       — fotos subidas (6 slots por inspeccion)
 *
 * IMPORTANTE: este script es idempotente. Aborta si encuentra datos en
 * produccion sin la bandera --force.
 */

if (php_sapi_name() !== 'cli') {
    die('Este script solo puede ejecutarse desde la linea de comandos.');
}

// PHP 8.1+ activa MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT por defecto, lo que
// convierte errores mysqli en excepciones. Para el safety-check que chequea
// tablas que pueden no existir aun, preferimos falsy returns del legacy mode.
mysqli_report(MYSQLI_REPORT_OFF);

$env = $argv[1] ?? 'local';
$force = in_array('--force', $argv, true);

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
    if (empty($config['password'])) {
        die("ERROR: variable de entorno DB_PROD_PASS no definida.\n");
    }
} else {
    die("Uso: php migrate_inspeccion_gimnasio.php [local|production] [--force]\n");
}

echo "=== Migracion SQL — Modulo Inspeccion Gimnasio (FT-SST-250) ===\n";
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
    die("ERROR de conexion: " . $mysqli->connect_error . "\n");
}

$mysqli->set_charset('utf8mb4');
echo "Conexion exitosa.\n\n";

// --- Safety check: abortar si hay registros (ambos entornos) ---
foreach (['tbl_inspeccion_gimnasio', 'tbl_gimnasio_detalle_evidencia'] as $t) {
    $r = @$mysqli->query("SELECT COUNT(*) AS c FROM `$t`");
    if ($r) {
        $n = (int)($r->fetch_assoc()['c'] ?? 0);
        if ($n > 0 && !$force) {
            die("ABORTADO: $t tiene $n registros. Usar --force para drop-create.\n");
        }
    }
}
echo "Safety check OK.\n\n";

$success = 0; $errors = 0; $total = 0;

$statements = [
    // --- DROP en orden inverso de dependencias ---
    ['desc' => 'DROP tbl_gimnasio_detalle_evidencia (si existe)',
     'sql' => 'DROP TABLE IF EXISTS `tbl_gimnasio_detalle_evidencia`'],
    ['desc' => 'DROP tbl_gimnasio_evidencia_maestro (si existe)',
     'sql' => 'DROP TABLE IF EXISTS `tbl_gimnasio_evidencia_maestro`'],
    ['desc' => 'DROP tbl_inspeccion_gimnasio (si existe)',
     'sql' => 'DROP TABLE IF EXISTS `tbl_inspeccion_gimnasio`'],

    // --- CREATE 1: master ---
    ['desc' => 'CREATE tbl_inspeccion_gimnasio',
     'sql' => "CREATE TABLE `tbl_inspeccion_gimnasio` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `id_cliente` INT NOT NULL,
        `id_consultor` INT NOT NULL,
        `fecha_inspeccion` DATE NOT NULL,

        -- Datos generales de operacion
        `aforo_maximo` INT NULL COMMENT 'Capacidad maxima segun reglamento interno',
        `horario_operacion` VARCHAR(100) NULL,
        `area_aproximada_m2` DECIMAL(7,2) NULL,

        -- Checklist de riesgos locativos (GYM-01 a GYM-13)
        `aforo_senalizado` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'GYM-01',
        `reglamento_visible` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'GYM-02',
        `piso_antideslizante` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'GYM-03',
        `ventilacion_adecuada` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'GYM-04',
        `iluminacion_adecuada` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'GYM-05 (>=300 lux)',
        `extintor_vigente_senalizado` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'GYM-06',
        `botiquin_visible_dotado` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'GYM-07',
        `plano_evacuacion_visible` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'GYM-08',
        `espejos_seguros` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'GYM-09 anclados, sin bordes vivos',
        `punto_hidratacion` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'GYM-10',
        `vestier_ordenado` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'GYM-11',
        `salida_emergencia_libre` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'GYM-12',
        `pulsador_emergencia_funcional` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'GYM-13',

        -- Observaciones y recomendaciones
        `introduccion` TEXT NULL,
        `alcance` TEXT NULL,
        `justificacion` TEXT NULL,
        `observaciones_generales` TEXT NULL,
        `recomendaciones_generales` TEXT NULL,
        `marco_normativo` TEXT NULL COMMENT 'Congelado al finalizar',
        `ruta_pdf` VARCHAR(500) NULL,
        `estado` ENUM('borrador','completo') NOT NULL DEFAULT 'borrador',
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        CONSTRAINT `fk_insp_gym_cliente`
            FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes`(`id_cliente`)
            ON DELETE RESTRICT ON UPDATE CASCADE,
        CONSTRAINT `fk_insp_gym_consultor`
            FOREIGN KEY (`id_consultor`) REFERENCES `tbl_consultor`(`id_consultor`)
            ON DELETE RESTRICT ON UPDATE CASCADE,

        INDEX `idx_insp_gym_cliente` (`id_cliente`),
        INDEX `idx_insp_gym_consultor` (`id_consultor`),
        INDEX `idx_insp_gym_estado` (`estado`)
     ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"],

    // --- CREATE 2: catalogo categorias (6 slots) ---
    ['desc' => 'CREATE tbl_gimnasio_evidencia_maestro',
     'sql' => "CREATE TABLE `tbl_gimnasio_evidencia_maestro` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `codigo` VARCHAR(50) NOT NULL UNIQUE,
        `nombre` VARCHAR(150) NOT NULL,
        `orden` TINYINT NOT NULL DEFAULT 0,
        `activo` TINYINT(1) NOT NULL DEFAULT 1,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_gym_ev_maestro_orden` (`orden`)
     ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"],

    // --- CREATE 3: evidencias subidas por inspeccion ---
    ['desc' => 'CREATE tbl_gimnasio_detalle_evidencia',
     'sql' => "CREATE TABLE `tbl_gimnasio_detalle_evidencia` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `id_inspeccion` INT NOT NULL,
        `slot` TINYINT NOT NULL COMMENT 'Slot 1..6',
        `categoria` VARCHAR(50) NULL COMMENT 'aforo, reglamento, extintor_botiquin, hallazgo, plano_evacuacion, ventilacion, vestier, salida_emergencia, general',
        `descripcion` TEXT NULL,
        `ruta_foto` VARCHAR(500) NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        CONSTRAINT `fk_gym_det_ev_inspeccion`
            FOREIGN KEY (`id_inspeccion`) REFERENCES `tbl_inspeccion_gimnasio`(`id`)
            ON DELETE CASCADE ON UPDATE CASCADE,

        INDEX `idx_gym_det_ev_inspeccion` (`id_inspeccion`),
        UNIQUE KEY `uk_gym_det_ev_slot` (`id_inspeccion`, `slot`)
     ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"],

    // --- SEED: 9 categorias estandar ---
    ['desc' => 'SEED tbl_gimnasio_evidencia_maestro (9 categorias)',
     'sql' => "INSERT INTO `tbl_gimnasio_evidencia_maestro` (`codigo`, `nombre`, `orden`) VALUES
        ('aforo',             'Senalizacion de aforo',      1),
        ('reglamento',        'Reglamento de uso visible',  2),
        ('extintor_botiquin', 'Extintor y botiquin',        3),
        ('plano_evacuacion',  'Plano de evacuacion',        4),
        ('ventilacion',       'Sistema de ventilacion',     5),
        ('vestier',           'Vestier y casilleros',       6),
        ('salida_emergencia', 'Salida de emergencia',       7),
        ('hallazgo',          'Hallazgo / deficiencia',     8),
        ('general',           'Vista general del gimnasio', 9)"],
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
    echo "MIGRACION COMPLETADA SIN ERRORES.\n";
} else {
    echo "HAY ERRORES — revisar antes de continuar.\n";
    exit(1);
}

$mysqli->close();
