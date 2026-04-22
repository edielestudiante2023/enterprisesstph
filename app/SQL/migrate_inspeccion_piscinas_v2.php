<?php
/**
 * Script CLI — Módulo Inspección de Piscinas v2 (Res 234/2026 Minsalud)
 * Uso: php migrate_inspeccion_piscinas_v2.php [local|production]
 *
 * Reemplaza el esquema v1 (Ley 1209 + Dec 554/2015) por uno alineado a:
 *   - Ley 1209 de 2008 (seguridad/cerramientos/drenajes)
 *   - Decreto 554 de 2015 (reglamentario)
 *   - Resolución 234 de 2026 Minsalud (calidad agua, IRAPI, BPS, botiquines)
 *
 * Crea 5 tablas:
 *   1. tbl_inspeccion_piscinas   — master de la visita
 *   2. tbl_piscina_detalle       — N piscinas/vasos por visita
 *   3. tbl_piscina_parametro_agua — mediciones in situ (pH, cloros, etc.)
 *   4. tbl_piscina_ensayo_laboratorio — ensayos trimestrales (micro/físicoquímico)
 *   5. tbl_piscina_botiquin_item  — checklist Anexo III por piscina
 *
 * IMPORTANTE: este script DROP-CREATE las tablas anteriores.
 * Verificar previamente que no haya datos a conservar.
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
    die("Uso: php migrate_inspeccion_piscinas_v2.php [local|production]\n");
}

echo "=== Migración SQL — Módulo Inspección Piscinas v2 (Res 234/2026) ===\n";
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

// --- Safety check: abortar si hay registros en producción ---
if ($env === 'production') {
    foreach (['tbl_inspeccion_piscinas', 'tbl_piscina_detalle'] as $t) {
        $r = @$mysqli->query("SELECT COUNT(*) AS c FROM `$t`");
        if ($r) {
            $n = (int)($r->fetch_assoc()['c'] ?? 0);
            if ($n > 0) {
                die("ABORTADO: $t tiene $n registros. Confirmar con usuario antes del drop.\n");
            }
        }
    }
    echo "Safety check OK (tablas vacías).\n\n";
}

$success = 0; $errors = 0; $total = 0;

$statements = [
    // --- DROP en orden inverso de dependencias ---
    ['desc' => 'DROP tbl_piscina_botiquin_item (si existe)',
     'sql' => 'DROP TABLE IF EXISTS `tbl_piscina_botiquin_item`'],
    ['desc' => 'DROP tbl_piscina_ensayo_laboratorio (si existe)',
     'sql' => 'DROP TABLE IF EXISTS `tbl_piscina_ensayo_laboratorio`'],
    ['desc' => 'DROP tbl_piscina_parametro_agua (si existe)',
     'sql' => 'DROP TABLE IF EXISTS `tbl_piscina_parametro_agua`'],
    ['desc' => 'DROP tbl_piscina_detalle (si existe)',
     'sql' => 'DROP TABLE IF EXISTS `tbl_piscina_detalle`'],
    ['desc' => 'DROP tbl_inspeccion_piscinas (si existe)',
     'sql' => 'DROP TABLE IF EXISTS `tbl_inspeccion_piscinas`'],

    // --- CREATE 1: master ---
    ['desc' => 'CREATE tbl_inspeccion_piscinas',
     'sql' => "CREATE TABLE `tbl_inspeccion_piscinas` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `id_cliente` INT NOT NULL,
        `id_consultor` INT NOT NULL,
        `fecha_inspeccion` DATE NOT NULL,

        -- Empresa mantenimiento
        `empresa_mantenimiento` VARCHAR(255) NULL,
        `nit_empresa_mantenimiento` VARCHAR(30) NULL,
        `contacto_empresa_mantenimiento` VARCHAR(150) NULL,

        -- Superficie total (determina tipo de botiquín A/B/C)
        `superficie_total_establecimiento_m2` DECIMAL(8,2) NULL COMMENT 'Anexo III Res 234: <500 Tipo A; 500-2000 Tipo B; >2000 Tipo C',

        -- Concepto sanitario Sec. Salud (reemplaza 'certificado municipal')
        `concepto_sanitario` ENUM('favorable','desfavorable','no_emitido') NOT NULL DEFAULT 'no_emitido',
        `concepto_sanitario_fecha` DATE NULL,
        `concepto_sanitario_observaciones` TEXT NULL,

        -- DEA (Art. 18 Res 234)
        `dea_presente` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
        `dea_ubicacion_senalizada` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
        `dea_personal_capacitado_cantidad` INT NOT NULL DEFAULT 0,

        -- Operador certificado (factor riesgo Art. 11 num 7)
        `operador_certificado_nombre` VARCHAR(200) NULL,
        `operador_certificado_entidad` VARCHAR(200) NULL,
        `operador_certificado_vigencia` DATE NULL,

        -- Documentación (Art. 15 — 8 procedimientos)
        `documentacion_art15_completa` ENUM('SI','NO','PARCIAL','NA') NOT NULL DEFAULT 'NA',
        `documentacion_art15_observaciones` TEXT NULL,

        -- Plan de Saneamiento Básico (Art. 17 — 5 programas)
        `plan_saneamiento_completo` ENUM('SI','NO','PARCIAL','NA') NOT NULL DEFAULT 'NA',
        `plan_saneamiento_observaciones` TEXT NULL,

        -- Manejo químicos (Art. 13 — fichas, SDS, EPP, GHS)
        `manejo_quimicos_conforme` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',

        -- Residuos (Art. 14 + 17 num 2)
        `area_residuos_conforme` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
        `contenedores_codificados_color` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',

        -- Tablero público (Art. 16 par 2)
        `tablero_publico_resultados` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',

        `total_piscinas` INT NOT NULL DEFAULT 0,
        `recomendaciones_generales` TEXT NULL,
        `marco_normativo` TEXT NULL COMMENT 'Congelado al finalizar: Ley 1209/2008 + Dec 554/2015 + Res 234/2026',
        `ruta_pdf` VARCHAR(255) NULL,
        `estado` ENUM('borrador','completo') NOT NULL DEFAULT 'borrador',
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        CONSTRAINT `fk_insp_pis_v2_cliente`
            FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes`(`id_cliente`)
            ON DELETE RESTRICT ON UPDATE CASCADE,
        CONSTRAINT `fk_insp_pis_v2_consultor`
            FOREIGN KEY (`id_consultor`) REFERENCES `tbl_consultor`(`id_consultor`)
            ON DELETE RESTRICT ON UPDATE CASCADE,

        INDEX `idx_insp_pis_v2_cliente` (`id_cliente`),
        INDEX `idx_insp_pis_v2_consultor` (`id_consultor`),
        INDEX `idx_insp_pis_v2_estado` (`estado`)
     ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"],

    // --- CREATE 2: detalle piscina ---
    ['desc' => 'CREATE tbl_piscina_detalle',
     'sql' => "CREATE TABLE `tbl_piscina_detalle` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `id_inspeccion` INT NOT NULL,
        `orden` TINYINT NOT NULL DEFAULT 0,
        `identificador` VARCHAR(100) NOT NULL,
        `tipo` ENUM('ADULTOS','NINOS','JACUZZI','CHAPOTEADERO','OTRA') NOT NULL DEFAULT 'ADULTOS',
        `uso` ENUM('COLECTIVO_PUBLICO','RESTRINGIDO') NOT NULL DEFAULT 'RESTRINGIDO',
        `climatizada` ENUM('SI','NO') NOT NULL DEFAULT 'NO',
        `superficie_piscina_m2` DECIMAL(8,2) NULL,
        `volumen_agua_m3` DECIMAL(8,2) NULL,

        -- Profundidad (UX Gabriela: única señalizada + min opcional si variable)
        `perfil_profundidad` ENUM('UNIFORME','VARIABLE') NOT NULL DEFAULT 'UNIFORME',
        `profundidad_max_m` DECIMAL(4,2) NULL,
        `profundidad_min_m` DECIMAL(4,2) NULL,

        -- Aforo
        `aforo_piscina_max` INT NULL,
        `aforo_deck_max` INT NULL,

        -- Infraestructura (Ley 1209 + hallazgos campo)
        `cerramiento_perimetral` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
        `puerta_control_acceso` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
        `alarma_inmersion_80db` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'Consolida alarma_inmersion + alarma_80db (Art. 6 Ley 1209)',
        `boton_parada_emergencia` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'Corte del sistema de recirculación',
        `drenaje_antiatrapamiento` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
        `minimo_dos_drenajes` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
        `sistema_liberacion_vacio` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
        `senalizacion_profundidad` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
        `baldosas_cambio_profundidad` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
        `escaleras_acceso_antideslizantes` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
        `baranda_escaleras` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
        `iluminacion_adecuada` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
        `ventilacion_adecuada` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA' COMMENT 'Obligatoria si climatizada',

        -- Avisos
        `aviso_menores_12` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
        `aviso_reglamento` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
        `aviso_horario` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
        `aviso_ducharse_antes` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
        `aviso_prohibido_zapatos` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
        `aviso_telefonos_emergencia` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
        `aviso_aforo_visible` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',

        -- Emergencia
        `botiquin_tipo` ENUM('A','B','C','NINGUNO') NOT NULL DEFAULT 'NINGUNO' COMMENT 'Según m² del establecimiento (Anexo III Res 234)',
        `camilla_rescate` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
        `flotadores_circulares_min_2` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
        `baston_con_gancho` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
        `citofono_24h` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',

        -- Higiene
        `duchas_previas_obligatorias` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
        `cubiculos_duchas_mujeres` INT NOT NULL DEFAULT 0,
        `cubiculos_duchas_hombres` INT NOT NULL DEFAULT 0,
        `baranda_apoyo_duchas` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
        `lavapies_funcional` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',

        -- Dosificación (Art. 5 Res 234)
        `dosificacion_independiente` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
        `sistema_seguridad_flujo` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
        `no_dosificacion_manual_con_publico` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
        `equipo_bombeo_operativo` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
        `filtros_operativos` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',

        -- Libro registro (Art. 16)
        `libro_registro_existe` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
        `libro_ultima_semana_fecha` DATE NULL,
        `libro_observaciones` TEXT NULL,

        -- Resultado calculado
        `irapi_valor` DECIMAL(5,2) NULL COMMENT 'Calculado con IrapiCalculator (0-100)',
        `irapi_clasificacion` ENUM('SIN_RIESGO','BAJO','MEDIO','ALTO') NULL,
        `isl_valor` DECIMAL(5,2) NULL COMMENT 'Índice Saturación Langelier',
        `isl_interpretacion` ENUM('BALANCEADA','CORROSIVA','INCRUSTANTE') NULL,

        `estado_general` ENUM('BUENO','REGULAR','MALO','CRITICO') NULL,
        `foto` VARCHAR(255) NULL,
        `observaciones` TEXT NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

        CONSTRAINT `fk_pis_det_v2_inspeccion`
            FOREIGN KEY (`id_inspeccion`) REFERENCES `tbl_inspeccion_piscinas`(`id`)
            ON DELETE CASCADE ON UPDATE CASCADE,

        INDEX `idx_pis_det_v2_inspeccion` (`id_inspeccion`)
     ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"],

    // --- CREATE 3: mediciones in situ ---
    ['desc' => 'CREATE tbl_piscina_parametro_agua',
     'sql' => "CREATE TABLE `tbl_piscina_parametro_agua` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `id_piscina_detalle` INT NOT NULL,
        `parametro` ENUM(
            'pH','cloro_libre','cloro_combinado','temperatura',
            'turbidez','orp','tds','conductividad','acido_cianurico',
            'dureza_calcica','alcalinidad_total','bromo_total',
            'color','olor','transparencia','material_flotante'
        ) NOT NULL,
        `valor` DECIMAL(8,2) NULL COMMENT 'Null si parámetro cualitativo',
        `valor_cualitativo` VARCHAR(50) NULL COMMENT 'ACEPTABLE/AUSENTE/FONDO_VISIBLE/etc',
        `unidad` VARCHAR(20) NULL,
        `conforme` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
        `rango_referencia` VARCHAR(50) NULL COMMENT 'ej. 6.8-7.3, <1, <200',
        `observaciones` TEXT NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

        CONSTRAINT `fk_pis_param_v2_detalle`
            FOREIGN KEY (`id_piscina_detalle`) REFERENCES `tbl_piscina_detalle`(`id`)
            ON DELETE CASCADE ON UPDATE CASCADE,

        INDEX `idx_pis_param_v2_detalle` (`id_piscina_detalle`),
        INDEX `idx_pis_param_v2_parametro` (`parametro`)
     ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"],

    // --- CREATE 4: ensayos de laboratorio ---
    ['desc' => 'CREATE tbl_piscina_ensayo_laboratorio',
     'sql' => "CREATE TABLE `tbl_piscina_ensayo_laboratorio` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `id_piscina_detalle` INT NOT NULL,
        `tipo` ENUM('MICROBIOLOGICO','FISICOQUIMICO') NOT NULL,
        `fecha_toma` DATE NULL,
        `fecha_emision_resultados` DATE NULL,
        `laboratorio` VARCHAR(200) NULL,
        `laboratorio_nit` VARCHAR(30) NULL,
        `numero_informe` VARCHAR(80) NULL,
        `norma_citada` VARCHAR(200) NULL COMMENT 'Alerta si cita resolución derogada',

        -- Resultados microbiológicos (UFC/100mL; null si no aplica tipo)
        `heterotrofos_ufc` DECIMAL(10,2) NULL,
        `coliformes_termotolerantes_ufc` DECIMAL(10,2) NULL,
        `ecoli_ufc` DECIMAL(10,2) NULL,
        `pseudomonas_ufc` DECIMAL(10,2) NULL,
        `legionella_ufc` DECIMAL(10,2) NULL,

        `conforme_global` ENUM('SI','NO','PARCIAL','NA') NOT NULL DEFAULT 'NA',
        `archivo_adjunto` VARCHAR(255) NULL,
        `observaciones` TEXT NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

        CONSTRAINT `fk_pis_ensayo_v2_detalle`
            FOREIGN KEY (`id_piscina_detalle`) REFERENCES `tbl_piscina_detalle`(`id`)
            ON DELETE CASCADE ON UPDATE CASCADE,

        INDEX `idx_pis_ensayo_v2_detalle` (`id_piscina_detalle`),
        INDEX `idx_pis_ensayo_v2_tipo` (`tipo`)
     ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"],

    // --- CREATE 5: checklist botiquín ---
    ['desc' => 'CREATE tbl_piscina_botiquin_item',
     'sql' => "CREATE TABLE `tbl_piscina_botiquin_item` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `id_piscina_detalle` INT NOT NULL,
        `tipo_botiquin` ENUM('A','B','C') NOT NULL,
        `item_codigo` VARCHAR(50) NOT NULL COMMENT 'Ej: tijeras_corta_todo',
        `item_nombre` VARCHAR(200) NOT NULL,
        `unidad_medida` VARCHAR(50) NULL,
        `cantidad_exigida` INT NOT NULL DEFAULT 1,
        `cantidad_observada` INT NULL,
        `presente` ENUM('SI','NO','PARCIAL','NA') NOT NULL DEFAULT 'NA',
        `observaciones` TEXT NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

        CONSTRAINT `fk_pis_botiquin_v2_detalle`
            FOREIGN KEY (`id_piscina_detalle`) REFERENCES `tbl_piscina_detalle`(`id`)
            ON DELETE CASCADE ON UPDATE CASCADE,

        INDEX `idx_pis_botiquin_v2_detalle` (`id_piscina_detalle`),
        INDEX `idx_pis_botiquin_v2_tipo` (`tipo_botiquin`)
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
    echo "MIGRACIÓN v2 COMPLETADA SIN ERRORES.\n";
} else {
    echo "HAY ERRORES — revisar antes de continuar.\n";
}

$mysqli->close();
