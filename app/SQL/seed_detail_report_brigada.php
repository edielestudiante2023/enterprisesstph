<?php
/**
 * Inserta el registro de detail_report id=48 "Brigada y Simulacros" que el
 * modulo InspeccionBrigadaSimulacrosController usa en uploadToReportes() para
 * publicar los PDF de cada inspeccion en el listado central de reportes del
 * cliente (tbl_reporte).
 *
 * Idempotente (INSERT IGNORE).
 *
 * Uso:
 *   php app/SQL/seed_detail_report_brigada.php local
 *   DB_PROD_PASS=xxx php app/SQL/seed_detail_report_brigada.php production
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
    echo "Uso: php seed_detail_report_brigada.php [local|production]\n";
    exit(1);
}

$cfg = $configs[$env];
echo "=== Seed detail_report id=48 Brigada y Simulacros - Entorno: {$env} ===\n\n";

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

$sql = "INSERT IGNORE INTO detail_report (id_detailreport, detail_report) VALUES (48, 'Brigada y Simulacros')";
if (!$conn->query($sql)) {
    echo "[ERROR] " . $conn->error . "\n";
    $conn->close();
    exit(1);
}

$affected = $conn->affected_rows;
$verify = $conn->query("SELECT id_detailreport, detail_report FROM detail_report WHERE id_detailreport = 48");
$row = $verify->fetch_assoc();

if ($affected > 0) {
    echo "[OK] Registro creado: {$row['id_detailreport']} | {$row['detail_report']}\n";
} else {
    echo "[SKIP] Registro ya existia: {$row['id_detailreport']} | {$row['detail_report']}\n";
}

$conn->close();
exit(0);
