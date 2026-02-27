<?php
/**
 * Script CLI para poblar la tabla detail_report con los 33 tipos de inspección.
 * Uso: php migrate_detail_report_seed.php [local|production]
 * - Verifica cada registro antes de insertar (idempotente).
 * - Si ya existe, lo omite; si no, lo inserta.
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
    die("Uso: php migrate_detail_report_seed.php [local|production]\n");
}

echo "=== Migración: Poblar detail_report (33 tipos de inspección) ===\n";
echo "Entorno: " . strtoupper($env) . "\n";
echo "Host: {$config['host']}:{$config['port']}\n";
echo "Database: {$config['database']}\n";
echo "---\n";

// Conectar
$mysqli = mysqli_init();

if ($config['ssl']) {
    $mysqli->ssl_set(null, null, null, null, null);
    $mysqli->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
}

$connected = @$mysqli->real_connect(
    $config['host'],
    $config['user'],
    $config['password'],
    $config['database'],
    $config['port'],
    null,
    $config['ssl'] ? MYSQLI_CLIENT_SSL : 0
);

if (!$connected) {
    die("ERROR de conexión: " . $mysqli->connect_error . "\n");
}

echo "Conexión exitosa.\n\n";

// ─── Datos a poblar ───
$records = [
    [ 1, 'Actas de Visita'],
    [ 2, 'Señalización'],
    [ 3, 'Locativas'],
    [ 4, 'Extintores'],
    [ 5, 'Botiquín'],
    [ 6, 'Gabinetes'],
    [ 7, 'Comunicaciones'],
    [ 8, 'Rec. Seguridad'],
    [ 9, 'Prob. Peligros'],
    [10, 'Matriz Vuln.'],
    [11, 'Plan Emergencia'],
    [12, 'Ev. Simulacro'],
    [13, 'HV Brigadista'],
    [14, 'Dot. Vigilante'],
    [15, 'Dot. Aseadora'],
    [16, 'Dot. Todero'],
    [17, 'Zona Residuos'],
    [18, 'Capacitaciones'],
    [19, 'Prep. Simulacro'],
    [20, 'Asistencia'],
    [21, 'Limpieza y Des.'],
    [22, 'Residuos Sólidos'],
    [23, 'Control Plagas'],
    [24, 'Agua Potable'],
    [25, 'Plan Saneamiento'],
    [26, 'KPI Limpieza'],
    [27, 'KPI Residuos'],
    [28, 'KPI Plagas'],
    [29, 'KPI Agua Potable'],
    [30, 'Carta Vigía'],
    [31, 'Mantenimientos'],
    [32, 'Pendientes'],
    [33, 'Accesos Rápidos'],
];

$inserted = 0;
$skipped  = 0;
$errors   = 0;

foreach ($records as [$id, $name]) {
    // Verificar si ya existe
    $check = $mysqli->query("SELECT id_detailreport FROM detail_report WHERE id_detailreport = {$id}");
    if ($check && $check->num_rows > 0) {
        echo "[SKIP] id={$id} '{$name}' ya existe\n";
        $skipped++;
        continue;
    }

    // Insertar
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
echo "Omitidos (ya existían): {$skipped}\n";
echo "Errores: {$errors}\n";
echo "Total procesados: " . count($records) . "\n";

if ($errors === 0) {
    echo "MIGRACIÓN COMPLETADA SIN ERRORES.\n";
} else {
    echo "HAY ERRORES - REVISAR ANTES DE CONTINUAR.\n";
}

// Mostrar estado final
echo "\n--- Estado final de detail_report ---\n";
$result = $mysqli->query("SELECT id_detailreport, detail_report FROM detail_report ORDER BY id_detailreport");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "  [{$row['id_detailreport']}] {$row['detail_report']}\n";
    }
}

$mysqli->close();
