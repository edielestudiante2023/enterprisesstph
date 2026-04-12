<?php
/**
 * Script CLI — Módulo Inspección de Piscinas
 * Uso: php migrate_inspeccion_piscinas.php [local|production]
 *
 * Crea:
 *   - tbl_inspeccion_piscinas (master)
 *   - tbl_piscina_detalle (N piscinas/vasos por inspección, patrón N-ITEMS)
 *
 * Marco normativo: Ley 1209 de 2008 + Decreto Reglamentario 554 de 2015
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
    die("Uso: php migrate_inspeccion_piscinas.php [local|production]\n");
}

echo "=== Migración SQL - Módulo Inspección Piscinas ===\n";
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
        'desc' => 'CREATE TABLE tbl_inspeccion_piscinas',
        'sql'  => "CREATE TABLE IF NOT EXISTS `tbl_inspeccion_piscinas` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `id_cliente` INT NOT NULL,
            `id_consultor` INT NOT NULL,
            `fecha_inspeccion` DATE NOT NULL,
            `empresa_mantenimiento` VARCHAR(255) NULL,
            `nit_empresa_mantenimiento` VARCHAR(30) NULL,
            `contacto_empresa_mantenimiento` VARCHAR(150) NULL,
            `certificado_municipal_vigente` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'Certificado de seguridad expedido por municipio (Ley 1209)',
            `fecha_vencimiento_certificado_mpio` DATE NULL,
            `total_piscinas` INT NOT NULL DEFAULT 0,
            `recomendaciones_generales` TEXT NULL,
            `marco_normativo` TEXT NULL COMMENT 'Referencia normativa congelada (Ley 1209/2008 + Decreto 554/2015)',
            `ruta_pdf` VARCHAR(255) NULL,
            `estado` ENUM('borrador','completo') NOT NULL DEFAULT 'borrador',
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            CONSTRAINT `fk_insp_pis_cliente`
                FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes`(`id_cliente`)
                ON DELETE RESTRICT ON UPDATE CASCADE,
            CONSTRAINT `fk_insp_pis_consultor`
                FOREIGN KEY (`id_consultor`) REFERENCES `tbl_consultor`(`id_consultor`)
                ON DELETE RESTRICT ON UPDATE CASCADE,

            INDEX `idx_insp_pis_cliente` (`id_cliente`),
            INDEX `idx_insp_pis_consultor` (`id_consultor`),
            INDEX `idx_insp_pis_estado` (`estado`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ],
    [
        'desc' => 'CREATE TABLE tbl_piscina_detalle',
        'sql'  => "CREATE TABLE IF NOT EXISTS `tbl_piscina_detalle` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `id_inspeccion` INT NOT NULL,
            `orden` TINYINT NOT NULL DEFAULT 0,
            `identificador` VARCHAR(100) NOT NULL COMMENT 'Ej: Piscina principal, Piscina niños, Jacuzzi',
            `tipo` ENUM('ADULTOS','NINOS','JACUZZI','CHAPOTEADERO','OTRA') NOT NULL DEFAULT 'ADULTOS',
            `profundidad_minima_m` DECIMAL(4,2) NULL,
            `profundidad_maxima_m` DECIMAL(4,2) NULL,

            -- Art. 5, 14: Cerramientos
            `cerramiento_perimetral` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `puerta_control_acceso` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',

            -- Art. 6, 11g, 14: Alarmas
            `alarma_inmersion` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `alarma_80db_funcional` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',

            -- Art. 12: Drenajes antiatrapamiento
            `drenaje_antiatrapamiento` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `minimo_dos_drenajes` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `sistema_liberacion_vacio` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',

            -- Art. 13: Señalización profundidad
            `senalizacion_profundidad` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `baldosas_cambio_profundidad` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',

            -- Art. 11c, 11d, 11f: Equipamiento de emergencia
            `botiquin_primeros_auxilios` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `flotadores_circulares_min_2` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `baston_con_gancho` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `citofono_24h` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',

            -- Art. 14: Avisos
            `aviso_menores_12_anos` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `aviso_reglamento_visible` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',

            -- Art. 11b: Calidad de agua
            `agua_limpia_visualmente` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `registro_cloro_diario` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `registro_ph_diario` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `desinfeccion_quimica_vigente` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',

            -- Equipos
            `equipo_bombeo_operativo` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `filtros_operativos` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `dosificador_quimicos` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',

            -- Higiene
            `duchas_previas_obligatorias` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',

            -- Resultado
            `estado_general` ENUM('BUENO','REGULAR','MALO','CRITICO') NOT NULL DEFAULT 'BUENO',
            `foto` VARCHAR(255) NULL,
            `observaciones` TEXT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

            CONSTRAINT `fk_pis_det_inspeccion`
                FOREIGN KEY (`id_inspeccion`) REFERENCES `tbl_inspeccion_piscinas`(`id`)
                ON DELETE CASCADE ON UPDATE CASCADE,

            INDEX `idx_pis_det_inspeccion` (`id_inspeccion`)
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
