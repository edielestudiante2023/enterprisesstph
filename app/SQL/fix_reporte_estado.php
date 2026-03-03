<?php
/**
 * Fix: Update tbl_reporte records that have empty estado to 'ABIERTO'
 * These were caused by inserting 'Activo' into ENUM('ABIERTO','GESTIONANDO','CERRADO')
 *
 * Usage: DB_PROD_PASS=xxx php app/SQL/fix_reporte_estado.php production
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

// Check how many records have empty estado
$stmt = $pdo->query("SELECT COUNT(*) as cnt FROM tbl_reporte WHERE estado = ''");
$count = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
echo "Records with empty estado: {$count}\n";

if ($count > 0) {
    echo "Updating to 'ABIERTO'...\n";
    $affected = $pdo->exec("UPDATE tbl_reporte SET estado = 'ABIERTO' WHERE estado = ''");
    echo "Updated {$affected} records.\n";
} else {
    echo "Nothing to fix.\n";
}

echo "Done.\n";
