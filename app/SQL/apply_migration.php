<?php
/**
 * Script CLI para aplicar migraciones SQL de firma electrónica + clausula_primera_objeto
 * Uso: php apply_migration.php [local|production]
 * Compatible con MySQL 8.x (sin ADD COLUMN IF NOT EXISTS)
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
    die("Uso: php apply_migration.php [local|production]\n");
}

echo "=== Migración SQL - Firma Electrónica + Cláusula Primera IA ===\n";
echo "Entorno: " . strtoupper($env) . "\n";
echo "Host: {$config['host']}:{$config['port']}\n";
echo "Database: {$config['database']}\n";
echo "---\n";

// Conectar
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

/**
 * Verifica si una columna existe en una tabla
 */
function columnExists($mysqli, $database, $table, $column) {
    $stmt = $mysqli->prepare(
        "SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?"
    );
    $stmt->bind_param('sss', $database, $table, $column);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result['cnt'] > 0;
}

$success = 0;
$skipped = 0;
$errors = 0;
$total = 0;

// ========== PARTE 1: CREATE TABLES (IF NOT EXISTS funciona en MySQL) ==========

$createStatements = [
    [
        'desc' => 'CREATE TABLE tbl_doc_firma_solicitudes',
        'sql'  => "CREATE TABLE IF NOT EXISTS `tbl_doc_firma_solicitudes` (
            `id_solicitud` INT NOT NULL AUTO_INCREMENT,
            `id_documento` INT NOT NULL COMMENT 'FK a tbl_documentos_sst.id_documento',
            `id_version` INT NULL COMMENT 'FK a tbl_doc_versiones_sst.id_version',
            `token` VARCHAR(64) NOT NULL COMMENT 'Token único para link de firma',
            `estado` ENUM('pendiente', 'esperando', 'firmado', 'expirado', 'rechazado', 'cancelado') NOT NULL DEFAULT 'pendiente',
            `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `fecha_expiracion` DATETIME NOT NULL,
            `fecha_firma` DATETIME NULL,
            `firmante_tipo` ENUM('elaboro', 'reviso', 'delegado_sst', 'representante_legal') NOT NULL,
            `orden_firma` TINYINT NOT NULL DEFAULT 1,
            `firmante_interno_id` INT NULL COMMENT 'ID usuario del sistema si es firma interna',
            `firmante_email` VARCHAR(255) NULL,
            `firmante_nombre` VARCHAR(255) NOT NULL,
            `firmante_cargo` VARCHAR(100) NULL,
            `firmante_documento` VARCHAR(20) NULL COMMENT 'Cédula o NIT del firmante',
            `recordatorios_enviados` INT NOT NULL DEFAULT 0,
            `ultimo_recordatorio` DATETIME NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id_solicitud`),
            UNIQUE KEY `uk_token` (`token`),
            KEY `idx_documento` (`id_documento`),
            KEY `idx_estado` (`estado`),
            KEY `idx_email` (`firmante_email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ],
    [
        'desc' => 'CREATE TABLE tbl_doc_firma_evidencias',
        'sql'  => "CREATE TABLE IF NOT EXISTS `tbl_doc_firma_evidencias` (
            `id_evidencia` INT NOT NULL AUTO_INCREMENT,
            `id_solicitud` INT NOT NULL,
            `ip_address` VARCHAR(45) NOT NULL,
            `user_agent` TEXT NULL,
            `fecha_hora_utc` DATETIME NOT NULL,
            `geolocalizacion` VARCHAR(255) NULL COMMENT 'Lat,Lng si está disponible',
            `tipo_firma` ENUM('draw', 'upload', 'internal') NOT NULL COMMENT 'Dibujada, subida como imagen, o interna del sistema',
            `firma_imagen` LONGTEXT NULL COMMENT 'Base64 de la imagen de firma',
            `hash_documento` VARCHAR(64) NOT NULL COMMENT 'SHA-256 del contenido del documento al momento de firmar',
            `aceptacion_terminos` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id_evidencia`),
            KEY `idx_solicitud` (`id_solicitud`),
            CONSTRAINT `fk_evidencia_solicitud` FOREIGN KEY (`id_solicitud`)
                REFERENCES `tbl_doc_firma_solicitudes` (`id_solicitud`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ],
    [
        'desc' => 'CREATE TABLE tbl_doc_firma_audit_log',
        'sql'  => "CREATE TABLE IF NOT EXISTS `tbl_doc_firma_audit_log` (
            `id_log` INT NOT NULL AUTO_INCREMENT,
            `id_solicitud` INT NOT NULL,
            `evento` VARCHAR(50) NOT NULL COMMENT 'solicitud_creada, email_enviado, link_abierto, firma_completada, token_reenviado, solicitud_cancelada',
            `fecha_hora` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `ip_address` VARCHAR(45) NULL,
            `detalles` JSON NULL COMMENT 'Información adicional del evento',
            PRIMARY KEY (`id_log`),
            KEY `idx_solicitud` (`id_solicitud`),
            KEY `idx_evento` (`evento`),
            CONSTRAINT `fk_audit_solicitud` FOREIGN KEY (`id_solicitud`)
                REFERENCES `tbl_doc_firma_solicitudes` (`id_solicitud`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ],
];

foreach ($createStatements as $i => $stmt) {
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

// ========== PARTE 2: ALTER TABLE con verificación por INFORMATION_SCHEMA ==========

$alterColumns = [
    ['column' => 'token_firma',              'sql' => "ALTER TABLE `tbl_contratos` ADD COLUMN `token_firma` VARCHAR(64) NULL"],
    ['column' => 'token_firma_expiracion',   'sql' => "ALTER TABLE `tbl_contratos` ADD COLUMN `token_firma_expiracion` DATETIME NULL"],
    ['column' => 'estado_firma',             'sql' => "ALTER TABLE `tbl_contratos` ADD COLUMN `estado_firma` ENUM('sin_enviar','pendiente_firma','firmado') DEFAULT 'sin_enviar'"],
    ['column' => 'firma_cliente_nombre',     'sql' => "ALTER TABLE `tbl_contratos` ADD COLUMN `firma_cliente_nombre` VARCHAR(255) NULL"],
    ['column' => 'firma_cliente_cedula',     'sql' => "ALTER TABLE `tbl_contratos` ADD COLUMN `firma_cliente_cedula` VARCHAR(20) NULL"],
    ['column' => 'firma_cliente_imagen',     'sql' => "ALTER TABLE `tbl_contratos` ADD COLUMN `firma_cliente_imagen` VARCHAR(500) NULL COMMENT 'Ruta al PNG de la firma'"],
    ['column' => 'firma_cliente_ip',         'sql' => "ALTER TABLE `tbl_contratos` ADD COLUMN `firma_cliente_ip` VARCHAR(45) NULL"],
    ['column' => 'firma_cliente_fecha',      'sql' => "ALTER TABLE `tbl_contratos` ADD COLUMN `firma_cliente_fecha` DATETIME NULL"],
    ['column' => 'clausula_primera_objeto',  'sql' => "ALTER TABLE `tbl_contratos` ADD COLUMN `clausula_primera_objeto` TEXT NULL COMMENT 'Texto personalizado de la Cláusula Primera (Objeto)'"],
];

foreach ($alterColumns as $col) {
    $total++;
    echo "[{$total}] ADD COLUMN {$col['column']}... ";

    if (columnExists($mysqli, $config['database'], 'tbl_contratos', $col['column'])) {
        echo "SKIP (ya existe)\n";
        $skipped++;
    } else {
        if ($mysqli->query($col['sql'])) {
            echo "OK (creada)\n";
            $success++;
        } else {
            echo "ERROR: " . $mysqli->error . "\n";
            $errors++;
        }
    }
}

echo "\n=== RESULTADO ===\n";
echo "Exitosas: {$success}\n";
echo "Omitidas (ya existían): {$skipped}\n";
echo "Errores: {$errors}\n";
echo "Total: {$total}\n";

if ($errors === 0) {
    echo "MIGRACIÓN COMPLETADA SIN ERRORES.\n";
} else {
    echo "HAY ERRORES - REVISAR ANTES DE CONTINUAR.\n";
}

$mysqli->close();
