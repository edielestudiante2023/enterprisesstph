<?php
/**
 * Migracion: Agregar 5 columnas JSON en tbl_plan_emergencia para Fase 2 IA.
 *
 * Columnas:
 *   - matriz_responsables_ia_json  -- JSON con matriz de responsables generada por IA
 *   - diagrama_ia_json             -- JSON con arbol de decision del diagrama de actuacion
 *   - brigada_ia_texto             -- texto enriquecido por IA para brigada
 *   - simulacros_ia_texto          -- texto enriquecido por IA para simulacros
 *   - pons_ia_json                 -- JSON con adendo personalizado por cada PON canonico
 *   - ia_generado_at               -- timestamp de la ultima generacion IA
 *
 * Uso:
 *   php app/SQL/migrate_plan_emergencia_ia_columns.php local
 *   DB_PROD_PASS=xxx php app/SQL/migrate_plan_emergencia_ia_columns.php production
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
    echo "Uso: php migrate_plan_emergencia_ia_columns.php [local|production]\n";
    exit(1);
}

$cfg = $configs[$env];
echo "=== Migracion plan_emergencia IA columns - Entorno: {$env} ===\n\n";

if ($env === 'production' && empty($cfg['pass'])) {
    echo "ERROR: DB_PROD_PASS no esta definida en variables de entorno\n";
    exit(1);
}

$conn = new mysqli($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['db'], $cfg['port'] ?? 3306);

if ($cfg['ssl'] ?? false) {
    $conn->ssl_set(null, null, null, null, null);
    $conn->real_connect($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['db'], $cfg['port'] ?? 3306, null, MYSQLI_CLIENT_SSL);
}

if ($conn->connect_error) {
    echo "ERROR de conexion: " . $conn->connect_error . "\n";
    exit(1);
}

echo "Conectado a {$cfg['db']}@{$cfg['host']}\n\n";

// Verificar columnas existentes
$result = $conn->query("DESCRIBE tbl_plan_emergencia");
if (!$result) {
    echo "ERROR: tabla tbl_plan_emergencia no existe en {$cfg['db']}\n";
    exit(1);
}
$columnas = [];
while ($row = $result->fetch_assoc()) {
    $columnas[] = $row['Field'];
}

$nuevasColumnas = [
    'matriz_responsables_ia_json' => "ALTER TABLE tbl_plan_emergencia ADD COLUMN matriz_responsables_ia_json LONGTEXT NULL",
    'diagrama_ia_json'            => "ALTER TABLE tbl_plan_emergencia ADD COLUMN diagrama_ia_json LONGTEXT NULL",
    'brigada_ia_texto'            => "ALTER TABLE tbl_plan_emergencia ADD COLUMN brigada_ia_texto LONGTEXT NULL",
    'simulacros_ia_texto'         => "ALTER TABLE tbl_plan_emergencia ADD COLUMN simulacros_ia_texto LONGTEXT NULL",
    'pons_ia_json'                => "ALTER TABLE tbl_plan_emergencia ADD COLUMN pons_ia_json LONGTEXT NULL",
    'ia_generado_at'              => "ALTER TABLE tbl_plan_emergencia ADD COLUMN ia_generado_at DATETIME NULL",
];

$ok = 0;
$errors = 0;
$skip = 0;

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
