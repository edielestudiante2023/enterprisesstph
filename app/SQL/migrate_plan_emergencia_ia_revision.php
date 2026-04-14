<?php
/**
 * Migracion: agregar columnas ia_contexto_json y ia_aprobado_json en
 * tbl_plan_emergencia para soportar el flujo de revision IA manual
 * (Fase 2b — vista de revision intermedia).
 *
 *  - ia_contexto_json  LONGTEXT NULL  { "pons":"...", "diagrama":"...", "matriz":"...", "brigada":"..." }
 *  - ia_aprobado_json  LONGTEXT NULL  { "pons":true,  "diagrama":false, ... }
 *
 * Idempotente.
 *
 * Uso:
 *   php app/SQL/migrate_plan_emergencia_ia_revision.php local
 *   DB_PROD_PASS=xxx php app/SQL/migrate_plan_emergencia_ia_revision.php production
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
    echo "Uso: php migrate_plan_emergencia_ia_revision.php [local|production]\n";
    exit(1);
}

$cfg = $configs[$env];
echo "=== Migracion ia_contexto_json + ia_aprobado_json - Entorno: {$env} ===\n\n";

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

$nuevasColumnas = [
    'ia_contexto_json' => "ALTER TABLE tbl_plan_emergencia ADD COLUMN ia_contexto_json LONGTEXT NULL",
    'ia_aprobado_json' => "ALTER TABLE tbl_plan_emergencia ADD COLUMN ia_aprobado_json LONGTEXT NULL",
];

$ok = 0;
$skip = 0;
$errors = 0;

foreach ($nuevasColumnas as $col => $sql) {
    if (in_array($col, $columnas)) {
        echo "[SKIP] Columna {$col} ya existe\n";
        $skip++;
        continue;
    }
    if ($conn->query($sql)) {
        echo "[OK] Columna {$col} agregada\n";
        $ok++;
    } else {
        echo "[ERROR] {$col}: " . $conn->error . "\n";
        $errors++;
    }
}

echo "\n=== Resultado: {$ok} OK | {$skip} SKIP | {$errors} ERRORES ===\n";
$conn->close();
exit($errors > 0 ? 1 : 0);
