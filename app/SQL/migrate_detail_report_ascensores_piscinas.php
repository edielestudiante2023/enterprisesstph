<?php
/**
 * Script CLI — Seed detail_report con 3 nuevos tipos de inspección:
 *   - 45: Inspección Ascensores
 *   - 46: Inspección Piscinas
 *   - 47: Inspección Piscinero
 *
 * Uso: php migrate_detail_report_ascensores_piscinas.php [local|production]
 * Idempotente: verifica existencia antes de insertar.
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
    die("Uso: php migrate_detail_report_ascensores_piscinas.php [local|production]\n");
}

echo "=== Seed detail_report — Ascensores / Piscinas / Piscinero ===\n";
echo "Entorno: " . strtoupper($env) . "\n";
echo "Host: {$config['host']}:{$config['port']}\n";
echo "---\n";

$mysqli = mysqli_init();
if ($config['ssl']) {
    $mysqli->ssl_set(null, null, null, null, null);
    $mysqli->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
}

$connected = @$mysqli->real_connect(
    $config['host'], $config['user'], $config['password'],
    $config['database'], $config['port'], null,
    $config['ssl'] ? MYSQLI_CLIENT_SSL : 0
);

if (!$connected) {
    die("ERROR de conexión: " . $mysqli->connect_error . "\n");
}

echo "Conexión exitosa.\n\n";

$records = [
    [45, 'Inspección Ascensores'],
    [46, 'Inspección Piscinas'],
    [47, 'Inspección Piscinero'],
];

$inserted = 0;
$skipped = 0;
$errors = 0;

foreach ($records as [$id, $name]) {
    $check = $mysqli->query("SELECT id_detailreport FROM detail_report WHERE id_detailreport = {$id}");
    if ($check && $check->num_rows > 0) {
        echo "[SKIP] id={$id} '{$name}' ya existe\n";
        $skipped++;
        continue;
    }

    $stmt = $mysqli->prepare("INSERT INTO detail_report (id_detailreport, detail_report) VALUES (?, ?)");
    $stmt->bind_param('is', $id, $name);
    if ($stmt->execute()) {
        echo "[OK]   id={$id} '{$name}' insertado\n";
        $inserted++;
    } else {
        echo "[ERROR] id={$id} '{$name}': " . $stmt->error . "\n";
        $errors++;
    }
    $stmt->close();
}

echo "\n=== RESULTADO ===\n";
echo "Insertados: {$inserted}\n";
echo "Omitidos: {$skipped}\n";
echo "Errores: {$errors}\n";

if ($errors === 0) {
    echo "MIGRACIÓN COMPLETADA SIN ERRORES.\n";
} else {
    echo "HAY ERRORES - REVISAR ANTES DE CONTINUAR.\n";
}

$mysqli->close();
