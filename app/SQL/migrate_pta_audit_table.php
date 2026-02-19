<?php
/**
 * Migración: Crear tabla tbl_pta_cliente_audit para auditoría del PTA
 * Uso: php app/SQL/migrate_pta_audit_table.php [local|production]
 * Producción: DB_PROD_PASS=xxx php app/SQL/migrate_pta_audit_table.php production
 */

$env = $argv[1] ?? 'local';

$configs = [
    'local' => [
        'host' => 'localhost',
        'user' => 'root',
        'pass' => '',
        'db'   => 'propiedad_horizontal',
        'ssl'  => false,
    ],
    'production' => [
        'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port' => 25060,
        'user' => 'cycloid_userdb',
        'pass' => getenv('DB_PROD_PASS') ?: '',
        'db'   => 'propiedad_horizontal',
        'ssl'  => true,
    ],
];

if (!isset($configs[$env])) {
    echo "Uso: php migrate_pta_audit_table.php [local|production]\n";
    exit(1);
}

$cfg = $configs[$env];
echo "=== Migración tbl_pta_cliente_audit - Entorno: {$env} ===\n\n";

if ($env === 'production' && empty($cfg['pass'])) {
    echo "ERROR: Variable DB_PROD_PASS no definida.\n";
    echo "Uso: DB_PROD_PASS=xxx php app/SQL/migrate_pta_audit_table.php production\n";
    exit(1);
}

$conn = new mysqli();

if ($cfg['ssl'] ?? false) {
    $conn->ssl_set(null, null, null, null, null);
    $conn->real_connect($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['db'], $cfg['port'] ?? 3306, null, MYSQLI_CLIENT_SSL);
} else {
    $conn->real_connect($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['db'], $cfg['port'] ?? 3306);
}

if ($conn->connect_error) {
    echo "ERROR de conexión: " . $conn->connect_error . "\n";
    exit(1);
}

echo "Conectado a {$cfg['db']}@{$cfg['host']}\n\n";

$ok = 0;
$errors = 0;

// Verificar si la tabla ya existe
$result = $conn->query("SHOW TABLES LIKE 'tbl_pta_cliente_audit'");
if ($result && $result->num_rows > 0) {
    echo "[SKIP] La tabla tbl_pta_cliente_audit ya existe\n";
    $ok++;
} else {
    $sql = "CREATE TABLE tbl_pta_cliente_audit (
        id_audit INT AUTO_INCREMENT PRIMARY KEY,
        id_ptacliente INT NOT NULL,
        id_cliente INT NULL,
        accion VARCHAR(50) NOT NULL,
        campo_modificado VARCHAR(100) NULL,
        valor_anterior TEXT NULL,
        valor_nuevo TEXT NULL,
        id_usuario INT NOT NULL,
        nombre_usuario VARCHAR(255) NULL,
        email_usuario VARCHAR(255) NULL,
        rol_usuario VARCHAR(100) NULL,
        ip_address VARCHAR(45) NULL,
        user_agent TEXT NULL,
        metodo VARCHAR(255) NULL,
        descripcion TEXT NULL,
        fecha_accion DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_id_ptacliente (id_ptacliente),
        INDEX idx_id_cliente (id_cliente),
        INDEX idx_accion (accion),
        INDEX idx_fecha_accion (fecha_accion),
        INDEX idx_id_usuario (id_usuario)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    if ($conn->query($sql)) {
        echo "[OK] Tabla tbl_pta_cliente_audit creada exitosamente\n";
        $ok++;
    } else {
        echo "[ERROR] No se pudo crear la tabla: " . $conn->error . "\n";
        $errors++;
    }
}

// Verificar estructura de la tabla
$result = $conn->query("DESCRIBE tbl_pta_cliente_audit");
if ($result) {
    echo "\n--- Estructura de la tabla ---\n";
    while ($row = $result->fetch_assoc()) {
        echo "  {$row['Field']} ({$row['Type']})\n";
    }
} else {
    echo "[ERROR] No se pudo verificar la estructura: " . $conn->error . "\n";
    $errors++;
}

echo "\n=== Resultado: {$ok} OK, {$errors} errores ===\n";
$conn->close();

exit($errors > 0 ? 1 : 0);
