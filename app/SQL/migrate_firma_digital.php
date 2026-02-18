#!/usr/bin/env php
<?php
/**
 * Migración: Firma Digital / Firma Electrónica
 *
 * Uso:
 *   php migrate_firma_digital.php local
 *   php migrate_firma_digital.php production
 *
 * Crea 3 tablas nuevas y agrega 8 columnas a tbl_contratos.
 * Todas las operaciones usan IF NOT EXISTS, seguro para re-ejecución.
 */

if (php_sapi_name() !== 'cli') {
    die("Este script solo se puede ejecutar desde la línea de comandos.\n");
}

$env = $argv[1] ?? null;
if (!in_array($env, ['local', 'production'])) {
    echo "Uso: php migrate_firma_digital.php [local|production]\n";
    exit(1);
}

// ── Credenciales ──────────────────────────────────────────────
$configs = [
    'local' => [
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'user'     => 'root',
        'password' => '',
        'database' => 'propiedad_horizontal',
        'ssl'      => false,
    ],
    'production' => [
        'host'     => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port'     => 25060,
        'user'     => 'cycloid_userdb',
        'password' => getenv('DB_PROD_PASS') ?: '',
        'database' => 'propiedad_horizontal',
        'ssl'      => true,
    ],
];

$cfg = $configs[$env];

echo "═══════════════════════════════════════════════════════════\n";
echo "  MIGRACIÓN FIRMA DIGITAL - " . strtoupper($env) . "\n";
echo "  Host: {$cfg['host']}:{$cfg['port']}\n";
echo "  DB:   {$cfg['database']}\n";
echo "═══════════════════════════════════════════════════════════\n\n";

// ── Conexión ──────────────────────────────────────────────────
$mysqli = mysqli_init();

if ($cfg['ssl']) {
    $mysqli->ssl_set(null, null, null, null, null);
    $mysqli->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
}

$connected = @$mysqli->real_connect(
    $cfg['host'],
    $cfg['user'],
    $cfg['password'],
    $cfg['database'],
    $cfg['port'],
    null,
    $cfg['ssl'] ? MYSQLI_CLIENT_SSL : 0
);

if (!$connected) {
    echo "[ERROR] No se pudo conectar: {$mysqli->connect_error}\n";
    exit(1);
}

$mysqli->set_charset('utf8mb4');
echo "[OK] Conexión establecida a {$cfg['database']}\n\n";

// ── Verificación previa ───────────────────────────────────────
echo "── PRE-CHECK ──────────────────────────────────────────────\n";

// Verificar que tbl_contratos existe
$result = $mysqli->query("SHOW TABLES LIKE 'tbl_contratos'");
if ($result->num_rows === 0) {
    echo "[ERROR] La tabla tbl_contratos NO existe en {$cfg['database']}.\n";
    echo "        Esta migración requiere que tbl_contratos exista.\n";
    $mysqli->close();
    exit(1);
}
echo "[OK] tbl_contratos existe\n";

// Verificar tablas firma existentes
foreach (['tbl_doc_firma_solicitudes', 'tbl_doc_firma_evidencias', 'tbl_doc_firma_audit_log'] as $tabla) {
    $r = $mysqli->query("SHOW TABLES LIKE '{$tabla}'");
    echo ($r->num_rows > 0) ? "[INFO] {$tabla} ya existe (se omitirá CREATE)\n" : "[INFO] {$tabla} será creada\n";
}

// Verificar columnas firma en tbl_contratos
$columnasFirma = [
    'token_firma', 'token_firma_expiracion', 'estado_firma',
    'firma_cliente_nombre', 'firma_cliente_cedula', 'firma_cliente_imagen',
    'firma_cliente_ip', 'firma_cliente_fecha'
];
$existentes = [];
$r = $mysqli->query("DESCRIBE tbl_contratos");
while ($row = $r->fetch_assoc()) {
    if (in_array($row['Field'], $columnasFirma)) {
        $existentes[] = $row['Field'];
    }
}
$faltantes = array_diff($columnasFirma, $existentes);
if (count($existentes) > 0) {
    echo "[INFO] Columnas firma ya existentes: " . implode(', ', $existentes) . "\n";
}
if (count($faltantes) > 0) {
    echo "[INFO] Columnas firma por agregar: " . implode(', ', $faltantes) . "\n";
} else {
    echo "[INFO] Todas las columnas firma ya existen en tbl_contratos\n";
}

echo "\n── EJECUTANDO MIGRACIÓN ────────────────────────────────────\n";

// ── Sentencias SQL ────────────────────────────────────────────
$statements = [
    'Crear tabla tbl_doc_firma_solicitudes' => "
        CREATE TABLE IF NOT EXISTS `tbl_doc_firma_solicitudes` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    'Crear tabla tbl_doc_firma_evidencias' => "
        CREATE TABLE IF NOT EXISTS `tbl_doc_firma_evidencias` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    'Crear tabla tbl_doc_firma_audit_log' => "
        CREATE TABLE IF NOT EXISTS `tbl_doc_firma_audit_log` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

];

// Columnas a agregar en tbl_contratos (compatible MySQL y MariaDB)
$alterColumns = [
    'token_firma'              => "VARCHAR(64) NULL",
    'token_firma_expiracion'   => "DATETIME NULL",
    'estado_firma'             => "ENUM('sin_enviar','pendiente_firma','firmado') DEFAULT 'sin_enviar'",
    'firma_cliente_nombre'     => "VARCHAR(255) NULL",
    'firma_cliente_cedula'     => "VARCHAR(20) NULL",
    'firma_cliente_imagen'     => "VARCHAR(500) NULL COMMENT 'Ruta al PNG de la firma'",
    'firma_cliente_ip'         => "VARCHAR(45) NULL",
    'firma_cliente_fecha'      => "DATETIME NULL",
];

// ── Ejecución ─────────────────────────────────────────────────
$ok = 0;
$errors = 0;

// 1. Crear tablas
foreach ($statements as $label => $sql) {
    $result = $mysqli->query($sql);
    if ($result) {
        $info = $mysqli->info ?: 'sin cambios (ya existía)';
        echo "[OK] {$label} → {$info}\n";
        $ok++;
    } else {
        echo "[ERROR] {$label} → {$mysqli->error}\n";
        $errors++;
    }
}

// 2. ALTER TABLE - verificar columna antes de agregarla (compatible MySQL puro)
$r = $mysqli->query("DESCRIBE tbl_contratos");
$existingCols = [];
while ($row = $r->fetch_assoc()) {
    $existingCols[] = $row['Field'];
}

foreach ($alterColumns as $colName => $colDef) {
    if (in_array($colName, $existingCols)) {
        echo "[OK] ALTER: {$colName} → ya existe (omitido)\n";
        $ok++;
    } else {
        $sql = "ALTER TABLE tbl_contratos ADD COLUMN {$colName} {$colDef}";
        $result = $mysqli->query($sql);
        if ($result) {
            echo "[OK] ALTER: {$colName} → columna agregada\n";
            $ok++;
        } else {
            echo "[ERROR] ALTER: {$colName} → {$mysqli->error}\n";
            $errors++;
        }
    }
}

// ── Verificación post-migración ───────────────────────────────
echo "\n── VERIFICACIÓN POST-MIGRACIÓN ────────────────────────────\n";

// Verificar tablas
foreach (['tbl_doc_firma_solicitudes', 'tbl_doc_firma_evidencias', 'tbl_doc_firma_audit_log'] as $tabla) {
    $r = $mysqli->query("SHOW TABLES LIKE '{$tabla}'");
    $status = ($r->num_rows > 0) ? 'OK' : 'FALTA';
    echo "[{$status}] {$tabla}\n";
}

// Verificar columnas
$r = $mysqli->query("DESCRIBE tbl_contratos");
$cols = [];
while ($row = $r->fetch_assoc()) {
    $cols[] = $row['Field'];
}
foreach ($columnasFirma as $col) {
    $status = in_array($col, $cols) ? 'OK' : 'FALTA';
    echo "[{$status}] tbl_contratos.{$col}\n";
}

$mysqli->close();

echo "\n═══════════════════════════════════════════════════════════\n";
echo "  RESULTADO: {$ok} exitosas, {$errors} errores\n";
echo "═══════════════════════════════════════════════════════════\n";

exit($errors > 0 ? 1 : 0);
