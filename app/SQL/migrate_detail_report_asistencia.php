<?php
/**
 * Migración: Agregar detail_report para Asistencia Inducción (34) y Responsabilidades SST (35)
 *
 * Usage LOCAL:      php app/SQL/migrate_detail_report_asistencia.php
 * Usage PRODUCCION: php app/SQL/migrate_detail_report_asistencia.php production
 */

define('ROOTPATH', dirname(__DIR__, 2) . '/');
require_once ROOTPATH . 'vendor/autoload.php';
$dotenv = new CodeIgniter\Config\DotEnv(ROOTPATH);
$dotenv->load();

$isProduction = ($argv[1] ?? '') === 'production';

if ($isProduction) {
    $host   = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $user   = 'cycloid_userdb';
    $pass   = getenv('DB_PROD_PASS');
    $dbname = 'propiedad_horizontal';
    $port   = 25060;

    if (empty($pass)) {
        die("ERROR: Variable DB_PROD_PASS no definida. Ejecute: DB_PROD_PASS=xxx php app/SQL/migrate_detail_report_asistencia.php production\n");
    }
    echo "=== MODO PRODUCCIÓN ===\n";
} else {
    $host   = getenv('database.default.hostname') ?: 'localhost';
    $user   = getenv('database.default.username') ?: 'root';
    $pass   = getenv('database.default.password') ?: '';
    $dbname = getenv('database.default.database') ?: 'propiedad_horizontal';
    $port   = (int)(getenv('database.default.port') ?: 3306);
    echo "=== MODO LOCAL ===\n";
}

echo "Conectando a {$host}:{$port}/{$dbname}...\n";

$db = new mysqli($host, $user, $pass, $dbname, $port);
if ($db->connect_error) {
    die("ERROR conexión: " . $db->connect_error . "\n");
}

$inserts = [
    34 => 'Asistencia Inducción',
    35 => 'Responsabilidades SST',
];

foreach ($inserts as $id => $nombre) {
    // Check if already exists
    $check = $db->query("SELECT id_detailreport FROM detail_report WHERE id_detailreport = {$id}");
    if ($check && $check->num_rows > 0) {
        echo "  ID {$id} ({$nombre}): YA EXISTE, skip.\n";
        continue;
    }

    $stmt = $db->prepare("INSERT INTO detail_report (id_detailreport, detail_report) VALUES (?, ?)");
    $stmt->bind_param('is', $id, $nombre);

    if ($stmt->execute()) {
        echo "  ID {$id} ({$nombre}): INSERTADO OK.\n";
    } else {
        echo "  ID {$id} ({$nombre}): ERROR - " . $stmt->error . "\n";
    }
    $stmt->close();
}

// Verificación
echo "\n=== Verificación ===\n";
$r = $db->query("SELECT id_detailreport, detail_report FROM detail_report WHERE id_detailreport IN (34, 35) ORDER BY id_detailreport");
while ($row = $r->fetch_assoc()) {
    echo "  {$row['id_detailreport']} => {$row['detail_report']}\n";
}

$db->close();
echo "\nMigración completada.\n";
