<?php
/**
 * Migración: Módulo Seguimiento Agenda
 * Crea tbl_seguimiento_clientes y tbl_seguimiento_historial
 *
 * Uso:
 *   LOCAL:      php app/SQL/create_seguimiento_agenda.php
 *   PRODUCCIÓN: php app/SQL/create_seguimiento_agenda.php production
 */

$env = $argv[1] ?? 'local';

if ($env === 'production') {
    $host     = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port     = 25060;
    $dbname   = 'propiedad_horizontal';
    $user     = 'cycloid_userdb';
    $password = getenv('DB_PROD_PASS');
    $ssl      = true;
} else {
    $host     = '127.0.0.1';
    $port     = 3306;
    $dbname   = 'propiedad_horizontal';
    $user     = 'root';
    $password = '';
    $ssl      = false;
}

echo "=== Migración: Seguimiento Agenda ===\n";
echo "Entorno: " . strtoupper($env) . "\n\n";

$mysqli = new mysqli($host, $user, $password, $dbname, $port);
if ($ssl) {
    $mysqli->ssl_set(NULL, NULL, NULL, NULL, NULL);
}
if ($mysqli->connect_error) {
    die("Error de conexión: " . $mysqli->connect_error . "\n");
}
$mysqli->set_charset('utf8mb4');

$sqls = [];

// ─── Tabla principal ───────────────────────────────────────────────────────
$sqls[] = [
    'desc' => 'Crear tbl_seguimiento_clientes',
    'sql'  => "
        CREATE TABLE IF NOT EXISTS tbl_seguimiento_clientes (
            id              INT AUTO_INCREMENT PRIMARY KEY,
            id_cliente      INT NOT NULL,
            asunto          VARCHAR(500) NOT NULL,
            mensaje         TEXT NOT NULL,
            opciones_fechas TEXT NULL COMMENT 'JSON array de opciones de fecha',
            consultor       VARCHAR(255) NOT NULL DEFAULT 'Edison Cuervo',
            cargo_consultor VARCHAR(255) NOT NULL DEFAULT 'Consultor SST',
            activo          TINYINT(1) NOT NULL DEFAULT 1,
            detenido        TINYINT(1) NOT NULL DEFAULT 0,
            motivo_detencion VARCHAR(255) NULL,
            created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_activo (activo, detenido),
            INDEX idx_cliente (id_cliente)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    "
];

// ─── Tabla historial ───────────────────────────────────────────────────────
$sqls[] = [
    'desc' => 'Crear tbl_seguimiento_historial',
    'sql'  => "
        CREATE TABLE IF NOT EXISTS tbl_seguimiento_historial (
            id                  INT AUTO_INCREMENT PRIMARY KEY,
            id_seguimiento      INT NOT NULL,
            id_cliente          INT NOT NULL,
            fecha_envio         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            estado              ENUM('ENVIADO','ERROR','DETENIDO') NOT NULL DEFAULT 'ENVIADO',
            detalle             VARCHAR(500) NULL,
            INDEX idx_seguimiento (id_seguimiento),
            INDEX idx_cliente (id_cliente),
            INDEX idx_fecha (fecha_envio)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    "
];

// ─── Ejecutar ──────────────────────────────────────────────────────────────
$ok = 0;
$err = 0;
foreach ($sqls as $item) {
    echo "→ {$item['desc']}... ";
    if ($mysqli->query($item['sql'])) {
        echo "OK\n";
        $ok++;
    } else {
        echo "ERROR: " . $mysqli->error . "\n";
        $err++;
    }
}

echo "\n=== Resultado: {$ok} OK | {$err} errores ===\n";
$mysqli->close();
exit($err > 0 ? 1 : 0);
