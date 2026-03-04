<?php
/**
 * Check ALL tables in the database for missing updated_at/created_at
 * compared to what their models expect (useTimestamps=true)
 */
$env = $argv[1] ?? 'local';

if ($env === 'production') {
    $host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port = 25060;
    $db   = 'propiedad_horizontal';
    $user = 'cycloid_userdb';
    $pass = getenv('DB_PROD_PASS');
    if (!$pass) { die("ERROR: Set DB_PROD_PASS\n"); }
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
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 30];
if ($ssl) {
    $options[PDO::MYSQL_ATTR_SSL_CA] = true;
    $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
}

$pdo = new PDO($dsn, $user, $pass, $options);
echo "Connected to {$env}\n\n";

// Get ALL tables
$stmt = $pdo->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo "=== Tables MISSING updated_at or created_at ===\n";
$missing = [];
foreach ($tables as $table) {
    $stmt = $pdo->query("DESCRIBE {$table}");
    $cols = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'Field');

    $hasCreated = in_array('created_at', $cols);
    $hasUpdated = in_array('updated_at', $cols);

    if (!$hasCreated || !$hasUpdated) {
        $m = [];
        if (!$hasCreated) $m[] = 'created_at';
        if (!$hasUpdated) $m[] = 'updated_at';
        echo "  {$table}: MISSING " . implode(', ', $m) . "\n";
        $missing[$table] = $m;
    }
}

if (empty($missing)) {
    echo "  All tables have both columns.\n";
}

echo "\nTotal tables: " . count($tables) . "\n";
echo "Tables with missing columns: " . count($missing) . "\n";
