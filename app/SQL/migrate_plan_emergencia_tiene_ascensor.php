<?php
/**
 * Migracion: agregar columna tiene_ascensor en tbl_plan_emergencia.
 *
 * Permite al consultor indicar si la copropiedad tiene ascensor.
 * Si no tiene, el PDF del Plan de Emergencia oculta el PON Codigo 07
 * (Persona(s) atrapada(s) en ascensor).
 *
 * Idempotente.
 *
 * Uso:
 *   php app/SQL/migrate_plan_emergencia_tiene_ascensor.php local
 *   DB_PROD_PASS=xxx php app/SQL/migrate_plan_emergencia_tiene_ascensor.php production
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
    echo "Uso: php migrate_plan_emergencia_tiene_ascensor.php [local|production]\n";
    exit(1);
}

$cfg = $configs[$env];
echo "=== Migracion tiene_ascensor en tbl_plan_emergencia - Entorno: {$env} ===\n\n";

if ($env === 'production' && empty($cfg['pass'])) {
    echo "ERROR: DB_PROD_PASS no esta definida en variables de entorno\n";
    exit(1);
}

if ($cfg['ssl'] ?? false) {
    $conn = mysqli_init();
    $conn->ssl_set(null, null, null, null, null);
    $conn->real_connect($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['db'], $cfg['port'] ?? 3306, null, MYSQLI_CLIENT_SSL);
} else {
    $conn = new mysqli($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['db'], $cfg['port'] ?? 3306);
}

if ($conn->connect_error) {
    echo "ERROR de conexion: " . $conn->connect_error . "\n";
    exit(1);
}

echo "Conectado a {$cfg['db']}@{$cfg['host']}\n\n";

$result = $conn->query("DESCRIBE tbl_plan_emergencia");
$columnas = [];
while ($row = $result->fetch_assoc()) {
    $columnas[] = $row['Field'];
}

if (in_array('tiene_ascensor', $columnas)) {
    echo "[SKIP] Columna tiene_ascensor ya existe\n";
    $conn->close();
    exit(0);
}

$sql = "ALTER TABLE tbl_plan_emergencia ADD COLUMN tiene_ascensor ENUM('si','no') NULL DEFAULT NULL AFTER tiene_gabinetes_hidraulico";
if ($conn->query($sql)) {
    echo "[OK] Columna tiene_ascensor agregada\n";
    $conn->close();
    exit(0);
} else {
    echo "[ERROR] " . $conn->error . "\n";
    $conn->close();
    exit(1);
}
