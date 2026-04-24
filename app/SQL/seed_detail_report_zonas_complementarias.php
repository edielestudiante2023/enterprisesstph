<?php
/**
 * Script CLI — Seed detail_report rows 49, 50, 51 para Fase 3 Zonas Complementarias.
 * Uso: php seed_detail_report_zonas_complementarias.php [local|production]
 *
 * Siembra de una vez:
 *   - 49 = Inspeccion Gimnasio (FT-SST-250)
 *   - 50 = Inspeccion Turco+Sauna+Jacuzzi (FT-SST-249)
 *   - 51 = Inspeccion Zona BBQ (FT-SST-251)
 *
 * Idempotente: INSERT ... ON DUPLICATE KEY UPDATE. Seguro de correr N veces.
 *
 * Contexto: los controllers InspeccionGimnasioController, InspeccionTurcoSaunaController
 * (futuro) y InspeccionZonaBbqController (futuro) insertan en tbl_reporte con esos
 * id_detailreport, lo que requiere que existan las filas por la FK fk_detailreport.
 */

if (php_sapi_name() !== 'cli') {
    die('Este script solo puede ejecutarse desde la linea de comandos.');
}

mysqli_report(MYSQLI_REPORT_OFF);

$env = $argv[1] ?? 'local';

if ($env === 'local') {
    $config = [
        'host' => '127.0.0.1', 'port' => 3306,
        'user' => 'root', 'password' => '',
        'database' => 'propiedad_horizontal', 'ssl' => false,
    ];
} elseif ($env === 'production') {
    $config = [
        'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com', 'port' => 25060,
        'user' => 'cycloid_userdb', 'password' => getenv('DB_PROD_PASS') ?: '',
        'database' => 'propiedad_horizontal', 'ssl' => true,
    ];
    if (empty($config['password'])) die("ERROR: DB_PROD_PASS no definida.\n");
} else {
    die("Uso: php seed_detail_report_zonas_complementarias.php [local|production]\n");
}

echo "=== SEED detail_report (Fase 3: Zonas Complementarias) ===\n";
echo "Entorno: " . strtoupper($env) . "\n---\n";

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

if (!$connected) die("ERROR de conexion: " . $mysqli->connect_error . "\n");
$mysqli->set_charset('utf8mb4');

$rows = [
    49 => 'Inspección Gimnasio',
    50 => 'Inspección Turco/Sauna/Jacuzzi',
    51 => 'Inspección Zona BBQ',
];

$errors = 0;
foreach ($rows as $id => $nombre) {
    $nombreEsc = $mysqli->real_escape_string($nombre);
    $before = $mysqli->query("SELECT detail_report FROM detail_report WHERE id_detailreport = $id")->fetch_assoc();
    $estado = $before ? "ya existe ('{$before['detail_report']}')" : "no existe";
    echo "[$id] $nombre — $estado ... ";

    $sql = "INSERT INTO detail_report (id_detailreport, detail_report) VALUES ($id, '$nombreEsc')
            ON DUPLICATE KEY UPDATE detail_report = VALUES(detail_report)";
    if (!$mysqli->query($sql)) {
        echo "ERROR: " . $mysqli->error . "\n";
        $errors++;
    } else {
        echo "OK\n";
    }
}

echo "\n=== RESULTADO ===\n";
if ($errors === 0) {
    echo "SEED COMPLETADO SIN ERRORES.\n";
    // Verificacion final
    $res = $mysqli->query("SELECT id_detailreport, detail_report FROM detail_report WHERE id_detailreport IN (49,50,51) ORDER BY id_detailreport");
    echo "\nEstado final:\n";
    while ($row = $res->fetch_assoc()) {
        echo "  {$row['id_detailreport']} => {$row['detail_report']}\n";
    }
} else {
    echo "HAY $errors ERRORES — revisar antes de continuar.\n";
    exit(1);
}

$mysqli->close();
