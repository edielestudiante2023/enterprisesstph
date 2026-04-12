<?php
/**
 * Script CLI — Módulo Inspección del Piscinero / Salvavidas
 * Uso: php migrate_inspeccion_piscinero.php [local|production]
 *
 * Crea:
 *   - tbl_inspeccion_piscinero (patrón PLANO — un registro por inspección del perfil)
 *
 * Marco normativo: Ley 1209 de 2008 Art. 14 (salvavidas con RCP vigente por piscina)
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
    die("Uso: php migrate_inspeccion_piscinero.php [local|production]\n");
}

echo "=== Migración SQL - Módulo Inspección Piscinero ===\n";
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
        'desc' => 'CREATE TABLE tbl_inspeccion_piscinero',
        'sql'  => "CREATE TABLE IF NOT EXISTS `tbl_inspeccion_piscinero` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `id_cliente` INT NOT NULL,
            `id_consultor` INT NOT NULL,
            `fecha_inspeccion` DATE NOT NULL,

            -- Datos personales
            `nombre_piscinero` VARCHAR(255) NOT NULL,
            `cedula` VARCHAR(30) NULL,
            `telefono` VARCHAR(30) NULL,
            `vinculacion` ENUM('DIRECTO_COPROPIEDAD','EMPRESA_ASEO','EMPRESA_ESPECIALIZADA','OTRA') NOT NULL DEFAULT 'EMPRESA_ESPECIALIZADA',
            `empresa_contratista` VARCHAR(255) NULL,
            `nit_empresa_contratista` VARCHAR(30) NULL,

            -- Certificaciones (Art. 14 Ley 1209)
            `certificacion_rcp_vigente` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `fecha_vencimiento_rcp` DATE NULL,
            `foto_certificado_rcp` VARCHAR(255) NULL,

            `curso_salvamento_acuatico` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `fecha_vencimiento_salvamento` DATE NULL,
            `foto_certificado_salvamento` VARCHAR(255) NULL,

            -- Afiliaciones SST
            `afiliacion_arl_vigente` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `afiliacion_eps_vigente` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `examenes_medicos_ocupacionales` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `fecha_ultimo_examen_medico` DATE NULL,

            -- Dotación EPP
            `dotacion_epp_entregada` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `gafas_proteccion_quimica` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `guantes_nitrilo` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `careta_proteccion` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `delantal_impermeable` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',

            -- Capacitación
            `capacitacion_manejo_quimicos` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `conocimiento_hojas_seguridad` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `conocimiento_plan_emergencia` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',

            -- Operación
            `horario_cubre_operacion_piscina` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
            `horario_inicio` TIME NULL,
            `horario_fin` TIME NULL,

            -- Evidencias
            `foto_piscinero` VARCHAR(255) NULL,
            `observaciones` TEXT NULL,
            `marco_normativo` TEXT NULL COMMENT 'Referencia normativa congelada (Ley 1209/2008 Art. 14)',
            `ruta_pdf` VARCHAR(255) NULL,
            `estado` ENUM('borrador','completo') NOT NULL DEFAULT 'borrador',
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            CONSTRAINT `fk_insp_piscinero_cliente`
                FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes`(`id_cliente`)
                ON DELETE RESTRICT ON UPDATE CASCADE,
            CONSTRAINT `fk_insp_piscinero_consultor`
                FOREIGN KEY (`id_consultor`) REFERENCES `tbl_consultor`(`id_consultor`)
                ON DELETE RESTRICT ON UPDATE CASCADE,

            INDEX `idx_insp_piscinero_cliente` (`id_cliente`),
            INDEX `idx_insp_piscinero_consultor` (`id_consultor`),
            INDEX `idx_insp_piscinero_estado` (`estado`)
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
