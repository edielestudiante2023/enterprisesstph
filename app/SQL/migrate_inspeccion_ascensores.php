<?php
/**
 * Script CLI — Módulo Inspección de Ascensores
 * Uso: php migrate_inspeccion_ascensores.php [local|production]
 *
 * Crea:
 *   - tbl_inspeccion_ascensores (master — datos generales de la inspección)
 *   - tbl_ascensor_detalle (N ascensores por inspección, patrón N-ITEMS dinámicos)
 *
 * Marco normativo: NTC 5926-1:2012 (Criterios para inspecciones de ascensores)
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
    die("Uso: php migrate_inspeccion_ascensores.php [local|production]\n");
}

echo "=== Migración SQL - Módulo Inspección Ascensores ===\n";
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

$createStatements = [
    [
        'desc' => 'CREATE TABLE tbl_inspeccion_ascensores',
        'sql'  => "CREATE TABLE IF NOT EXISTS `tbl_inspeccion_ascensores` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `id_cliente` INT NOT NULL,
            `id_consultor` INT NOT NULL,
            `fecha_inspeccion` DATE NOT NULL,
            `empresa_mantenimiento` VARCHAR(255) NULL COMMENT 'Empresa que hace mantenimiento preventivo mensual',
            `nit_empresa_mantenimiento` VARCHAR(30) NULL,
            `contacto_empresa_mantenimiento` VARCHAR(150) NULL,
            `organismo_certificador_onac` VARCHAR(255) NULL COMMENT 'Entidad acreditada ONAC que emitió certificado',
            `fecha_ultimo_certificado_onac` DATE NULL,
            `fecha_vencimiento_certificado_onac` DATE NULL,
            `certificado_visible_al_publico` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `cronograma_mantenimiento_anual` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `reportes_tecnicos_disponibles` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `total_ascensores` INT NOT NULL DEFAULT 0,
            `recomendaciones_generales` TEXT NULL,
            `marco_normativo` TEXT NULL COMMENT 'Referencia normativa congelada al momento de la inspección (NTC 5926-1)',
            `ruta_pdf` VARCHAR(255) NULL,
            `estado` ENUM('borrador','completo') NOT NULL DEFAULT 'borrador',
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            CONSTRAINT `fk_insp_asc_cliente`
                FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes`(`id_cliente`)
                ON DELETE RESTRICT ON UPDATE CASCADE,
            CONSTRAINT `fk_insp_asc_consultor`
                FOREIGN KEY (`id_consultor`) REFERENCES `tbl_consultor`(`id_consultor`)
                ON DELETE RESTRICT ON UPDATE CASCADE,

            INDEX `idx_insp_asc_cliente` (`id_cliente`),
            INDEX `idx_insp_asc_consultor` (`id_consultor`),
            INDEX `idx_insp_asc_estado` (`estado`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ],
    [
        'desc' => 'CREATE TABLE tbl_ascensor_detalle',
        'sql'  => "CREATE TABLE IF NOT EXISTS `tbl_ascensor_detalle` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `id_inspeccion` INT NOT NULL,
            `orden` TINYINT NOT NULL DEFAULT 0,
            `identificador` VARCHAR(100) NOT NULL COMMENT 'Ej: Torre A - Ascensor 1',
            `capacidad_kg` INT NULL,
            `capacidad_personas` INT NULL,
            `pisos_servidos` VARCHAR(50) NULL COMMENT 'Ej: Sótano-15',
            `tipo` ENUM('ELECTRICO','HIDRAULICO','NA') NOT NULL DEFAULT 'ELECTRICO',

            -- Zona: Cabina
            `cab_piso_antideslizante` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `cab_iluminacion_normal` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `cab_iluminacion_emergencia` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `cab_ventilacion` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `cab_pasamanos` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `cab_botonera_operativa` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `cab_display_piso` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `cab_sensor_sobrecarga` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `cab_placa_capacidad_visible` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `cab_intercomunicador_funcional` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',

            -- Zona: Puertas
            `pue_alineacion` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `pue_fotocelula_cortina` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `pue_mecanismo_cierre` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `pue_enclavamientos` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `pue_nivelacion_piso` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',

            -- Zona: Cuarto de máquinas
            `cm_maquina_tractora` ENUM('BUENO','REGULAR','MALO','NA') NOT NULL DEFAULT 'NA',
            `cm_poleas_cables` ENUM('BUENO','REGULAR','MALO','NA') NOT NULL DEFAULT 'NA',
            `cm_sistema_freno` ENUM('BUENO','REGULAR','MALO','NA') NOT NULL DEFAULT 'NA',
            `cm_tablero_control` ENUM('BUENO','REGULAR','MALO','NA') NOT NULL DEFAULT 'NA',
            `cm_iluminacion_ventilacion` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `cm_orden_aseo` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `cm_extintor_vigente` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `cm_acceso_restringido` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',

            -- Zona: Foso (Pit)
            `foso_amortiguadores` ENUM('BUENO','REGULAR','MALO','NA') NOT NULL DEFAULT 'NA',
            `foso_limpieza` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `foso_sin_agua_residuos` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `foso_interruptor_parada` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `foso_escalera_acceso` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `foso_iluminacion` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',

            -- Zona: Shaft/hueco
            `shaft_integridad_estructural` ENUM('BUENO','REGULAR','MALO','NA') NOT NULL DEFAULT 'NA',
            `shaft_estado_guias` ENUM('BUENO','REGULAR','MALO','NA') NOT NULL DEFAULT 'NA',
            `shaft_sin_cableado_ajeno` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',

            -- Zona: Circuitos eléctricos de seguridad
            `elec_puesta_tierra` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `elec_limitador_velocidad` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `elec_paracaidas` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `elec_final_carrera` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `elec_protecciones_termomagneticas` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',

            -- Zona: Contrapeso
            `cp_guias_estado` ENUM('BUENO','REGULAR','MALO','NA') NOT NULL DEFAULT 'NA',
            `cp_sin_obstaculos` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',

            -- Zona: Señalización
            `sen_placa_capacidad` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `sen_instrucciones_emergencia` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `sen_numero_emergencia` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `sen_certificado_visible` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',

            -- Resultado
            `estado_general` ENUM('BUENO','REGULAR','MALO','CRITICO') NOT NULL DEFAULT 'BUENO',
            `certificado_onac_vigente` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `foto` VARCHAR(255) NULL,
            `observaciones` TEXT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

            CONSTRAINT `fk_asc_det_inspeccion`
                FOREIGN KEY (`id_inspeccion`) REFERENCES `tbl_inspeccion_ascensores`(`id`)
                ON DELETE CASCADE ON UPDATE CASCADE,

            INDEX `idx_asc_det_inspeccion` (`id_inspeccion`)
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
echo "Exitosas: {$success}\nErrores: {$errors}\nTotal: {$total}\n";

if ($errors === 0) {
    echo "MIGRACIÓN COMPLETADA SIN ERRORES.\n";
} else {
    echo "HAY ERRORES - REVISAR ANTES DE CONTINUAR.\n";
}

$mysqli->close();
