<?php
/**
 * Diagnostic + cleanup: check/delete orphan tbl_reporte records for KPI modules
 *
 * Usage: DB_PROD_PASS=xxx php app/SQL/cleanup_kpi_reportes.php production
 */

$env = $argv[1] ?? 'local';

if ($env === 'production') {
    $host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port = 25060;
    $db   = 'propiedad_horizontal';
    $user = 'cycloid_userdb';
    $pass = getenv('DB_PROD_PASS');
    if (!$pass) { die("ERROR: Set DB_PROD_PASS environment variable\n"); }
    $ssl  = true;
} else {
    $host = '127.0.0.1';
    $port = 3306;
    $db   = 'propiedad_horizontal';
    $user = 'root';
    $pass = '';
    $ssl  = false;
}

$dsn = "mysql:host={$host};port={$port};dbname={$db}";
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
if ($ssl) {
    $options[PDO::MYSQL_ATTR_SSL_CA] = true;
    $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
}

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "Connected to {$env} database.\n\n";
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage() . "\n");
}

// 1. Show table structure
echo "=== tbl_reporte columns ===\n";
$stmt = $pdo->query("DESCRIBE tbl_reporte");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo "  {$row['Field']} ({$row['Type']}) {$row['Null']} {$row['Key']}\n";
}

// 2. Show ALL KPI-related records (id_detailreport 33-36)
echo "\n=== KPI records in tbl_reporte (id_detailreport 33-36) ===\n";
$stmt = $pdo->query("SELECT id_reporte, titulo_reporte, id_detailreport, id_report_type, id_cliente, estado, enlace, tag, report_url, observaciones, created_at FROM tbl_reporte WHERE id_detailreport IN (33,34,35,36) ORDER BY id_reporte");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (empty($rows)) {
    echo "  (no records found)\n";
} else {
    foreach ($rows as $r) {
        echo "  ID: {$r['id_reporte']}\n";
        echo "    titulo_reporte: " . ($r['titulo_reporte'] ?: '(NULL)') . "\n";
        echo "    id_detailreport: {$r['id_detailreport']}\n";
        echo "    id_cliente: {$r['id_cliente']}\n";
        echo "    estado: " . ($r['estado'] ?: '(NULL)') . "\n";
        echo "    enlace: " . ($r['enlace'] ?: '(NULL)') . "\n";
        echo "    tag: " . ($r['tag'] ?: '(NULL)') . "\n";
        echo "    report_url: " . ($r['report_url'] ?: '(NULL)') . "\n";
        echo "    observaciones: " . ($r['observaciones'] ?: '(NULL)') . "\n";
        echo "    created_at: " . ($r['created_at'] ?: '(NULL)') . "\n";
        echo "  ---\n";
    }
}

// 3. Ask to delete
if (!empty($rows)) {
    echo "\nDelete all " . count($rows) . " KPI records from tbl_reporte? (y/n): ";
    $answer = trim(fgets(STDIN));
    if (strtolower($answer) === 'y') {
        $pdo->exec("DELETE FROM tbl_reporte WHERE id_detailreport IN (33,34,35,36)");
        echo "Deleted.\n";
    } else {
        echo "Skipped.\n";
    }
}

echo "\nDone.\n";
