<?php
/**
 * Script CLI — Modulo Inspeccion Zona BBQ (FT-SST-251).
 * Uso: php migrate_inspeccion_zona_bbq.php [local|production]
 *
 * Fundamento normativo:
 *   - Ley 675 de 2001 (propiedad horizontal, reglamento interno)
 *   - Decreto 1072 de 2015 (SG-SST personal)
 *   - NTC 2505 (instalaciones gas domiciliario)
 *   - NFPA 58 (Liquefied Petroleum Gas Code)
 *   - NFPA 72 (alarmas humo)
 *   - RETIE (proteccion electrica en zonas humedas)
 *   - Reglamento Tecnico Sector GLP (MinEnergia)
 *   - Criterio profesional del consultor SST.
 *
 * Crea 4 tablas:
 *   1. tbl_inspeccion_zona_bbq              — master
 *   2. tbl_zona_bbq_asador                  — N asadores por zona
 *   3. tbl_zona_bbq_evidencia_maestro       — catalogo categorias
 *   4. tbl_zona_bbq_detalle_evidencia       — 6 slots fijos
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
    die("Uso: php migrate_inspeccion_zona_bbq.php [local|production] [--force]\n");
}

echo "=== Migracion SQL — Modulo Zona BBQ (FT-SST-251) ===\n";
echo "Entorno: " . strtoupper($env) . "\n---\n";

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

foreach (['tbl_inspeccion_zona_bbq', 'tbl_zona_bbq_asador'] as $t) {
    $r = @$mysqli->query("SELECT COUNT(*) AS c FROM `$t`");
    if ($r) {
        $n = (int)($r->fetch_assoc()['c'] ?? 0);
        if ($n > 0 && !$force) die("ABORTADO: $t tiene $n registros. Usar --force.\n");
    }
}
echo "Safety check OK.\n\n";

$success = 0; $errors = 0; $total = 0;

$statements = [
    ['desc' => 'DROP tbl_zona_bbq_detalle_evidencia (si existe)',
     'sql' => 'DROP TABLE IF EXISTS `tbl_zona_bbq_detalle_evidencia`'],
    ['desc' => 'DROP tbl_zona_bbq_evidencia_maestro (si existe)',
     'sql' => 'DROP TABLE IF EXISTS `tbl_zona_bbq_evidencia_maestro`'],
    ['desc' => 'DROP tbl_zona_bbq_asador (si existe)',
     'sql' => 'DROP TABLE IF EXISTS `tbl_zona_bbq_asador`'],
    ['desc' => 'DROP tbl_inspeccion_zona_bbq (si existe)',
     'sql' => 'DROP TABLE IF EXISTS `tbl_inspeccion_zona_bbq`'],

    ['desc' => 'CREATE tbl_inspeccion_zona_bbq',
     'sql' => "CREATE TABLE `tbl_inspeccion_zona_bbq` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `id_cliente` INT NOT NULL,
        `id_consultor` INT NOT NULL,
        `fecha_inspeccion` DATE NOT NULL,

        -- Datos generales
        `numero_asadores` INT NOT NULL DEFAULT 1,
        `tipo_combustible` ENUM('GAS_LP','GAS_NATURAL','LENA','CARBON','ELECTRICO','MIXTO') NOT NULL DEFAULT 'GAS_LP',
        `aforo_maximo` INT NULL,
        `horario_operacion` VARCHAR(100) NULL,
        `tiene_sistema_reserva` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',

        -- Checklist BBQ-01..BBQ-17
        `reglamento_visible` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'BBQ-01',
        `extintor_cercano_vigente` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'BBQ-02',
        `tipo_extintor` VARCHAR(50) NULL COMMENT 'ABC 10lb, CO2, etc.',
        `distancia_vegetacion_ok` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'BBQ-03 >=1.5m',
        `distancia_vivienda_ok` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'BBQ-04 >=3m',
        `prueba_fugas_gas_vigente` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'BBQ-05 <=12 meses',
        `valvula_corte_accesible` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'BBQ-06',
        `cilindro_glp_exterior_ventilado` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'BBQ-07',
        `ventilacion_adecuada` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'BBQ-08',
        `punto_agua_accesible` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'BBQ-09',
        `punto_electrico_gfci` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'BBQ-10',
        `superficie_no_combustible` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'BBQ-11',
        `senal_prohibido_menores_solos` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'BBQ-12',
        `senal_riesgo_quemadura` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'BBQ-13',
        `mecheros_fuera_alcance` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'BBQ-14',
        `recipiente_cenizas_metalico` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'BBQ-15',
        `alarma_humo_adyacente` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'BBQ-16',
        `plan_emergencia_documentado` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'BBQ-17',

        -- Medidas cuantitativas
        `distancia_vegetacion_m` DECIMAL(4,1) NULL,
        `distancia_vivienda_m` DECIMAL(4,1) NULL,

        -- Meta
        `observaciones_generales` TEXT NULL,
        `recomendaciones_generales` TEXT NULL,
        `marco_normativo` TEXT NULL COMMENT 'Congelado al finalizar',
        `ruta_pdf` VARCHAR(500) NULL,
        `estado` ENUM('borrador','completo') NOT NULL DEFAULT 'borrador',
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        CONSTRAINT `fk_insp_bbq_cliente`
            FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes`(`id_cliente`)
            ON DELETE RESTRICT ON UPDATE CASCADE,
        CONSTRAINT `fk_insp_bbq_consultor`
            FOREIGN KEY (`id_consultor`) REFERENCES `tbl_consultor`(`id_consultor`)
            ON DELETE RESTRICT ON UPDATE CASCADE,

        INDEX `idx_insp_bbq_cliente` (`id_cliente`),
        INDEX `idx_insp_bbq_consultor` (`id_consultor`),
        INDEX `idx_insp_bbq_estado` (`estado`)
     ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"],

    ['desc' => 'CREATE tbl_zona_bbq_asador',
     'sql' => "CREATE TABLE `tbl_zona_bbq_asador` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `id_inspeccion` INT NOT NULL,
        `numero` VARCHAR(10) NOT NULL COMMENT 'Identificador: 1, 2, A, B',
        `estado_parrilla` ENUM('operativo','danado','requiere_mant') NOT NULL DEFAULT 'operativo',
        `estado_conexion_gas` ENUM('operativo','fuga_detectada','sin_conexion','no_aplica') NOT NULL DEFAULT 'no_aplica',
        `fecha_ultima_prueba_fuga` DATE NULL,
        `observaciones` TEXT NULL,
        `orden` TINYINT NOT NULL DEFAULT 0,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

        CONSTRAINT `fk_bbq_asador_inspeccion`
            FOREIGN KEY (`id_inspeccion`) REFERENCES `tbl_inspeccion_zona_bbq`(`id`)
            ON DELETE CASCADE ON UPDATE CASCADE,
        INDEX `idx_bbq_asador_inspeccion` (`id_inspeccion`)
     ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"],

    ['desc' => 'CREATE tbl_zona_bbq_evidencia_maestro',
     'sql' => "CREATE TABLE `tbl_zona_bbq_evidencia_maestro` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `codigo` VARCHAR(50) NOT NULL UNIQUE,
        `nombre` VARCHAR(150) NOT NULL,
        `orden` TINYINT NOT NULL DEFAULT 0,
        `activo` TINYINT(1) NOT NULL DEFAULT 1,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_bbq_ev_maestro_orden` (`orden`)
     ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"],

    ['desc' => 'CREATE tbl_zona_bbq_detalle_evidencia',
     'sql' => "CREATE TABLE `tbl_zona_bbq_detalle_evidencia` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `id_inspeccion` INT NOT NULL,
        `slot` TINYINT NOT NULL COMMENT 'Slot 1..6',
        `categoria` VARCHAR(50) NULL,
        `descripcion` TEXT NULL,
        `ruta_foto` VARCHAR(500) NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        CONSTRAINT `fk_bbq_det_ev_inspeccion`
            FOREIGN KEY (`id_inspeccion`) REFERENCES `tbl_inspeccion_zona_bbq`(`id`)
            ON DELETE CASCADE ON UPDATE CASCADE,

        INDEX `idx_bbq_det_ev_inspeccion` (`id_inspeccion`),
        UNIQUE KEY `uk_bbq_det_ev_slot` (`id_inspeccion`, `slot`)
     ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"],

    ['desc' => 'SEED tbl_zona_bbq_evidencia_maestro (12 categorias)',
     'sql' => "INSERT INTO `tbl_zona_bbq_evidencia_maestro` (`codigo`, `nombre`, `orden`) VALUES
        ('asador',            'Asador / parrilla',                1),
        ('extintor',          'Extintor cercano',                 2),
        ('cilindro_gas',      'Cilindro GLP',                     3),
        ('valvula_corte',     'Valvula de corte de gas',          4),
        ('conexion_gas',      'Conexion / tuberia de gas',        5),
        ('punto_agua',        'Punto de agua',                    6),
        ('punto_electrico',   'Punto electrico / GFCI',           7),
        ('senalizacion',      'Senalizacion de riesgo',           8),
        ('vegetacion_cercana','Vegetacion / material combustible',9),
        ('reglamento',        'Reglamento de uso',                10),
        ('hallazgo',          'Hallazgo / deficiencia',           11),
        ('general',           'Vista general de la zona',         12)"],
];

foreach ($statements as $stmt) {
    $total++;
    echo "[{$total}] {$stmt['desc']}... ";
    if ($mysqli->query($stmt['sql'])) { echo "OK\n"; $success++; }
    else { echo "ERROR: " . $mysqli->error . "\n"; $errors++; }
}

echo "\n=== RESULTADO ===\nExitosas: $success\nErrores: $errors\nTotal: $total\n";
if ($errors === 0) echo "MIGRACION COMPLETADA SIN ERRORES.\n";
else { echo "HAY ERRORES.\n"; exit(1); }
$mysqli->close();
