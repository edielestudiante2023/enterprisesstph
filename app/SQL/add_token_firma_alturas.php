<?php
/**
 * Migración: agregar columnas de token para firma protocolo alturas en tbl_clientes
 *
 * Uso:
 *   LOCAL:      php app/SQL/add_token_firma_alturas.php
 *   PRODUCCIÓN: DB_PROD_PASS=xxx php app/SQL/add_token_firma_alturas.php production
 */

$isProduction = ($argv[1] ?? '') === 'production';

if ($isProduction) {
    $db = new mysqli();
    $db->ssl_set(null, null, null, null, null);
    @$db->real_connect(
        'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'cycloid_userdb',
        getenv('DB_PROD_PASS'),
        'propiedad_horizontal',
        25060,
        null,
        MYSQLI_CLIENT_SSL
    );
    echo "=== PRODUCCIÓN ===\n";
} else {
    $db = new mysqli('localhost', 'root', '', 'propiedad_horizontal');
    echo "=== LOCAL ===\n";
}

if ($db->connect_error) {
    die("Error conexión: " . $db->connect_error . "\n");
}

$db->set_charset('utf8mb4');

$columnas = [
    "token_firma_alturas VARCHAR(128) NULL DEFAULT NULL",
    "token_firma_alturas_exp DATETIME NULL DEFAULT NULL",
    "firma_alturas_fecha DATETIME NULL DEFAULT NULL",
    "firma_alturas_ip VARCHAR(45) NULL DEFAULT NULL",
    "protocolo_alturas_firmado TINYINT(1) NOT NULL DEFAULT 0",
];

foreach ($columnas as $col) {
    $nombre = explode(' ', trim($col))[0];
    $check = $db->query("SHOW COLUMNS FROM tbl_clientes LIKE '$nombre'");
    if ($check->num_rows > 0) {
        echo "  $nombre ya existe, skip\n";
        continue;
    }
    $sql = "ALTER TABLE tbl_clientes ADD COLUMN $col";
    if ($db->query($sql)) {
        echo "  OK: $nombre agregada\n";
    } else {
        echo "  ERROR: $nombre - " . $db->error . "\n";
    }
}

echo "Migración completada.\n";
