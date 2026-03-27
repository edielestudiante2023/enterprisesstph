<?php
/**
 * Migración: Insertar detail_report para Protocolo de Alturas
 * id_detailreport = 44
 *
 * Ejecutar:
 *   LOCAL:  php app/SQL/migrate_detail_report_alturas.php
 *   PROD:   DB_PROD_PASS=xxx php app/SQL/migrate_detail_report_alturas.php production
 */

$isProduction = ($argv[1] ?? '') === 'production';

if ($isProduction) {
    $host   = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $user   = 'cycloid_userdb';
    $pass   = getenv('DB_PROD_PASS');
    $dbname = 'propiedad_horizontal';
    $port   = 25060;
    echo "=== MODO PRODUCCIÓN ===\n";

    $db = new mysqli();
    $db->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
    $db->real_connect($host, $user, $pass, $dbname, $port, null, MYSQLI_CLIENT_SSL);
} else {
    $host   = 'localhost';
    $user   = getenv('database.default.username') ?: 'root';
    $pass   = getenv('database.default.password') ?: '';
    $dbname = getenv('database.default.database') ?: 'propiedad_horizontal';
    $port   = (int)(getenv('database.default.port') ?: 3306);
    echo "=== MODO LOCAL ===\n";

    $db = new mysqli($host, $user, $pass, $dbname, $port);
}

if ($db->connect_error) {
    die("ERROR conexión: " . $db->connect_error . "\n");
}

echo "Conectado a {$host}:{$port}/{$dbname}\n\n";

$id = 44;
$nombre = 'Protocolo Alturas';

$check = $db->query("SELECT id_detailreport FROM detail_report WHERE id_detailreport = {$id}");
if ($check && $check->num_rows > 0) {
    echo "[SKIP] id={$id} '{$nombre}' ya existe.\n";
} else {
    $stmt = $db->prepare("INSERT INTO detail_report (id_detailreport, detail_report) VALUES (?, ?)");
    $stmt->bind_param('is', $id, $nombre);
    if ($stmt->execute()) {
        echo "[OK] id={$id} '{$nombre}' insertado.\n";
    } else {
        echo "[ERROR] " . $stmt->error . "\n";
    }
    $stmt->close();
}

// Verificación
echo "\n=== Verificación ===\n";
$r = $db->query("SELECT id_detailreport, detail_report FROM detail_report WHERE id_detailreport = {$id}");
if ($row = $r->fetch_assoc()) {
    echo "  {$row['id_detailreport']} => {$row['detail_report']}\n";
} else {
    echo "  NO encontrado.\n";
}

$db->close();
echo "\nListo.\n";
