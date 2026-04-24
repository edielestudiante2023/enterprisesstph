<?php
/**
 * Script CLI — Modulo Inspeccion Turco + Sauna + Jacuzzi (FT-SST-249).
 * Uso: php migrate_inspeccion_turco_sauna.php [local|production]
 *
 * Fundamento normativo:
 *   - Ley 675 de 2001 (propiedad horizontal)
 *   - Ley 9 de 1979 (Codigo Sanitario)
 *   - Resolucion 2400 de 1979 (higiene y seguridad)
 *   - Decreto 1072 de 2015 (SG-SST personal)
 *   - NFPA 72 (alarma humo) / NFPA 101 (Life Safety)
 *   - RETIE (seguridad electrica en zonas humedas)
 *   - Criterio profesional del consultor SST (no hay norma colombiana
 *     especifica para baño turco, sauna ni jacuzzi de uso residencial).
 *
 * Crea 4 tablas:
 *   1. tbl_inspeccion_turco_sauna           — master, 3 flags aplica_*
 *   2. tbl_turco_sauna_detalle              — N filas por recinto (TURCO/SAUNA/JACUZZI)
 *   3. tbl_turco_sauna_evidencia_maestro    — catalogo categorias
 *   4. tbl_turco_sauna_detalle_evidencia    — 6 slots fijos por inspeccion
 */

if (php_sapi_name() !== 'cli') {
    die('Este script solo puede ejecutarse desde la linea de comandos.');
}

mysqli_report(MYSQLI_REPORT_OFF);

$env   = $argv[1] ?? 'local';
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
    if (empty($config['password'])) die("ERROR: DB_PROD_PASS no definida.\n");
} else {
    die("Uso: php migrate_inspeccion_turco_sauna.php [local|production] [--force]\n");
}

echo "=== Migracion SQL — Modulo Turco+Sauna+Jacuzzi (FT-SST-249) ===\n";
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
if (!$connected) die("ERROR de conexion: " . $mysqli->connect_error . "\n");
$mysqli->set_charset('utf8mb4');
echo "Conexion exitosa.\n\n";

// Safety check
foreach (['tbl_inspeccion_turco_sauna', 'tbl_turco_sauna_detalle'] as $t) {
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
    // DROP en orden inverso
    ['desc' => 'DROP tbl_turco_sauna_detalle_evidencia (si existe)',
     'sql' => 'DROP TABLE IF EXISTS `tbl_turco_sauna_detalle_evidencia`'],
    ['desc' => 'DROP tbl_turco_sauna_evidencia_maestro (si existe)',
     'sql' => 'DROP TABLE IF EXISTS `tbl_turco_sauna_evidencia_maestro`'],
    ['desc' => 'DROP tbl_turco_sauna_detalle (si existe)',
     'sql' => 'DROP TABLE IF EXISTS `tbl_turco_sauna_detalle`'],
    ['desc' => 'DROP tbl_inspeccion_turco_sauna (si existe)',
     'sql' => 'DROP TABLE IF EXISTS `tbl_inspeccion_turco_sauna`'],

    // CREATE 1: master
    ['desc' => 'CREATE tbl_inspeccion_turco_sauna',
     'sql' => "CREATE TABLE `tbl_inspeccion_turco_sauna` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `id_cliente` INT NOT NULL,
        `id_consultor` INT NOT NULL,
        `fecha_inspeccion` DATE NOT NULL,

        -- Flags de recintos presentes
        `aplica_turco` TINYINT(1) NOT NULL DEFAULT 0,
        `aplica_sauna` TINYINT(1) NOT NULL DEFAULT 0,
        `aplica_jacuzzi` TINYINT(1) NOT NULL DEFAULT 0,

        -- Datos generales
        `aforo_maximo_turco` INT NULL,
        `aforo_maximo_sauna` INT NULL,
        `aforo_maximo_jacuzzi` INT NULL,
        `horario_operacion` VARCHAR(100) NULL,

        -- Checklist comun (TS-01..TS-05 + TS-07,08,15,16)
        `reglamento_visible` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'TS-01',
        `reglamento_prohibe_menores_solos` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'TS-01b',
        `aforo_senalizado` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'TS-02',
        `timbre_emergencia_funcional` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'TS-03',
        `punto_hidratacion` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'TS-04',
        `control_temp_protegido` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'TS-05',
        `piso_antideslizante_acceso` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'TS-07',
        `iluminacion_protegida_humedad` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'TS-08',
        `alarma_humo_zona_adyacente` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'TS-15',
        `cronometro_visible` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'TS-16',

        -- Observaciones y meta
        `observaciones_generales` TEXT NULL,
        `recomendaciones_generales` TEXT NULL,
        `marco_normativo` TEXT NULL COMMENT 'Congelado al finalizar',
        `ruta_pdf` VARCHAR(500) NULL,
        `estado` ENUM('borrador','completo') NOT NULL DEFAULT 'borrador',
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        CONSTRAINT `chk_tsj_aplica_al_menos_uno` CHECK (
            `aplica_turco` = 1 OR `aplica_sauna` = 1 OR `aplica_jacuzzi` = 1
        ),
        CONSTRAINT `fk_insp_tsj_cliente`
            FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes`(`id_cliente`)
            ON DELETE RESTRICT ON UPDATE CASCADE,
        CONSTRAINT `fk_insp_tsj_consultor`
            FOREIGN KEY (`id_consultor`) REFERENCES `tbl_consultor`(`id_consultor`)
            ON DELETE RESTRICT ON UPDATE CASCADE,

        INDEX `idx_insp_tsj_cliente` (`id_cliente`),
        INDEX `idx_insp_tsj_consultor` (`id_consultor`),
        INDEX `idx_insp_tsj_estado` (`estado`)
     ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"],

    // CREATE 2: detalle por recinto
    ['desc' => 'CREATE tbl_turco_sauna_detalle',
     'sql' => "CREATE TABLE `tbl_turco_sauna_detalle` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `id_inspeccion` INT NOT NULL,
        `recinto` ENUM('TURCO','SAUNA','JACUZZI') NOT NULL,

        -- Comunes a los tres recintos
        `material_interno` VARCHAR(100) NULL COMMENT 'ceramica, madera, acrilico, etc.',
        `fuente_calor` VARCHAR(100) NULL COMMENT 'generador vapor, hornillo, calefaccion electrica',
        `temperatura_operacion` VARCHAR(50) NULL COMMENT 'rango en °C',
        `sistema_ventilacion` VARCHAR(100) NULL,
        `piso_antideslizante_interior` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
        `iluminacion_adecuada` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
        `aislamiento_electrico_ok` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',

        -- Solo Turco/Sauna (TS-06, TS-09)
        `puerta_abre_hacia_fuera` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'TS-06',
        `puerta_polarizada_visible_exterior` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'TS-06b',
        `ventilacion_rendijas` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'TS-09',

        -- Solo Turco (TS-10, TS-11)
        `desague_piso_funcional` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'TS-10',
        `generador_vapor_mant_vigente` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'TS-11',

        -- Solo Sauna (TS-12,13,14)
        `hornillo_aislado_asiento` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'TS-12',
        `madera_sin_danos_tornillos` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'TS-13',
        `aviso_prohibido_aceites` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'TS-14',

        -- Solo Jacuzzi (TS-17..TS-21 + TS-10 compartido con turco)
        `tiene_agarraderas_pasamanos` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'TS-17',
        `gfci_rcd_circuito` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'TS-18 RETIE',
        `profundidad_senalizada` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'TS-19',
        `cobertura_tapa_fuera_uso` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'TS-20',
        `cartel_prohibiciones_visibles` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'TS-21 menores/alcohol',
        `profundidad_m` DECIMAL(3,2) NULL,
        `temperatura_agua_c` DECIMAL(4,1) NULL,

        `observaciones` TEXT NULL,
        `orden` TINYINT NOT NULL DEFAULT 0,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

        CONSTRAINT `fk_tsj_det_inspeccion`
            FOREIGN KEY (`id_inspeccion`) REFERENCES `tbl_inspeccion_turco_sauna`(`id`)
            ON DELETE CASCADE ON UPDATE CASCADE,

        INDEX `idx_tsj_det_inspeccion` (`id_inspeccion`),
        INDEX `idx_tsj_det_recinto` (`recinto`),
        UNIQUE KEY `uk_tsj_det_unico_recinto` (`id_inspeccion`, `recinto`)
     ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"],

    // CREATE 3: catalogo categorias
    ['desc' => 'CREATE tbl_turco_sauna_evidencia_maestro',
     'sql' => "CREATE TABLE `tbl_turco_sauna_evidencia_maestro` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `codigo` VARCHAR(50) NOT NULL UNIQUE,
        `nombre` VARCHAR(150) NOT NULL,
        `orden` TINYINT NOT NULL DEFAULT 0,
        `activo` TINYINT(1) NOT NULL DEFAULT 1,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_tsj_ev_maestro_orden` (`orden`)
     ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"],

    // CREATE 4: evidencias subidas (6 slots)
    ['desc' => 'CREATE tbl_turco_sauna_detalle_evidencia',
     'sql' => "CREATE TABLE `tbl_turco_sauna_detalle_evidencia` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `id_inspeccion` INT NOT NULL,
        `slot` TINYINT NOT NULL COMMENT 'Slot 1..6',
        `categoria` VARCHAR(50) NULL,
        `descripcion` TEXT NULL,
        `ruta_foto` VARCHAR(500) NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        CONSTRAINT `fk_tsj_det_ev_inspeccion`
            FOREIGN KEY (`id_inspeccion`) REFERENCES `tbl_inspeccion_turco_sauna`(`id`)
            ON DELETE CASCADE ON UPDATE CASCADE,

        INDEX `idx_tsj_det_ev_inspeccion` (`id_inspeccion`),
        UNIQUE KEY `uk_tsj_det_ev_slot` (`id_inspeccion`, `slot`)
     ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"],

    // SEED: categorias estandar
    ['desc' => 'SEED tbl_turco_sauna_evidencia_maestro (16 categorias)',
     'sql' => "INSERT INTO `tbl_turco_sauna_evidencia_maestro` (`codigo`, `nombre`, `orden`) VALUES
        ('turco_interior',      'Turco — vista interior',           1),
        ('turco_desague',       'Turco — desague',                  2),
        ('turco_generador',     'Turco — generador de vapor',       3),
        ('sauna_interior',      'Sauna — vista interior',           4),
        ('sauna_hornillo',      'Sauna — hornillo/piedras',         5),
        ('sauna_puerta',        'Sauna — puerta',                   6),
        ('jacuzzi_interior',    'Jacuzzi — vista interior',         7),
        ('jacuzzi_agarradera',  'Jacuzzi — agarraderas',            8),
        ('jacuzzi_gfci',        'Jacuzzi — GFCI / tablero',         9),
        ('jacuzzi_cobertura',   'Jacuzzi — cobertura fuera de uso', 10),
        ('reglamento',          'Reglamento de uso',                11),
        ('aforo',               'Senal de aforo',                   12),
        ('control_temp',        'Control de temperatura',           13),
        ('punto_hidratacion',   'Punto de hidratacion',             14),
        ('hallazgo',            'Hallazgo / deficiencia',           15),
        ('general',             'Vista general del area',           16)"],
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
