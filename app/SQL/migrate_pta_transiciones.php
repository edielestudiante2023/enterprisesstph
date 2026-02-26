<?php
/**
 * Migración: Crear tabla tbl_pta_transiciones
 * Registra únicamente cuando una actividad cambia de estado ABIERTA a otro estado.
 *
 * Uso: php app/SQL/migrate_pta_transiciones.php [local|production]
 * Producción: DB_PROD_PASS=xxx php app/SQL/migrate_pta_transiciones.php production
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
    echo "Uso: php migrate_pta_transiciones.php [local|production]\n";
    exit(1);
}

$cfg = $configs[$env];
echo "=== Migración tbl_pta_transiciones - Entorno: {$env} ===\n\n";

if ($env === 'production' && empty($cfg['pass'])) {
    echo "ERROR: Variable DB_PROD_PASS no definida.\n";
    echo "Uso: DB_PROD_PASS=xxx php app/SQL/migrate_pta_transiciones.php production\n";
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
$result = $conn->query("SHOW TABLES LIKE 'tbl_pta_transiciones'");
if ($result && $result->num_rows > 0) {
    echo "[SKIP] La tabla tbl_pta_transiciones ya existe\n";
    $ok++;
} else {
    $sql = "CREATE TABLE tbl_pta_transiciones (
        id_transicion INT AUTO_INCREMENT PRIMARY KEY,
        id_ptacliente INT NOT NULL COMMENT 'FK a tbl_pta_cliente.id_ptacliente',
        id_cliente INT NOT NULL COMMENT 'FK al cliente',
        estado_anterior VARCHAR(50) NOT NULL DEFAULT 'ABIERTA' COMMENT 'Siempre ABIERTA en este contexto',
        estado_nuevo VARCHAR(50) NOT NULL COMMENT 'Estado al que pasó (GESTIONANDO, CERRADA, etc.)',
        id_usuario INT NOT NULL COMMENT 'Quién hizo el cambio',
        nombre_usuario VARCHAR(255) NULL COMMENT 'Nombre del usuario al momento del cambio',
        fecha_transicion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Cuándo ocurrió',
        INDEX idx_id_ptacliente (id_ptacliente),
        INDEX idx_id_cliente (id_cliente),
        INDEX idx_estado_nuevo (estado_nuevo),
        INDEX idx_fecha_transicion (fecha_transicion),
        INDEX idx_id_usuario (id_usuario)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    COMMENT='Registra transiciones de actividades que salen de estado ABIERTA'";

    if ($conn->query($sql)) {
        echo "[OK] Tabla tbl_pta_transiciones creada exitosamente\n";
        $ok++;
    } else {
        echo "[ERROR] No se pudo crear la tabla: " . $conn->error . "\n";
        $errors++;
    }
}

// Verificar estructura de la tabla
$result = $conn->query("DESCRIBE tbl_pta_transiciones");
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
