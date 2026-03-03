<?php
/**
 * Migration: Add id_consultor, report_url, tag columns to tbl_reporte
 *
 * Usage: DB_PROD_PASS=xxx php app/SQL/migrate_reporte_tag.php production
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
    echo "Connected to {$env} database.\n";
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage() . "\n");
}

// Check which columns already exist
$existing = [];
$stmt = $pdo->query("DESCRIBE tbl_reporte");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $existing[] = $row['Field'];
}

$alterations = [];

if (!in_array('id_consultor', $existing)) {
    $alterations[] = "ADD COLUMN id_consultor INT NULL AFTER id_cliente";
    echo "  Will add: id_consultor\n";
}
if (!in_array('report_url', $existing)) {
    $alterations[] = "ADD COLUMN report_url VARCHAR(500) NULL AFTER id_report_type";
    echo "  Will add: report_url\n";
}
if (!in_array('tag', $existing)) {
    $alterations[] = "ADD COLUMN tag VARCHAR(255) NULL AFTER report_url";
    $alterations[] = "ADD UNIQUE INDEX idx_tag (tag)";
    echo "  Will add: tag (with unique index)\n";
}

if (empty($alterations)) {
    echo "All columns already exist. Nothing to do.\n";
    exit(0);
}

$sql = "ALTER TABLE tbl_reporte " . implode(", ", $alterations);
echo "Executing: {$sql}\n";
$pdo->exec($sql);
echo "Migration completed successfully.\n";
