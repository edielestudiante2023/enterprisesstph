<?php
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
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
if ($ssl) {
    $options[PDO::MYSQL_ATTR_SSL_CA] = true;
    $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
}

$pdo = new PDO($dsn, $user, $pass, $options);
echo "Connected to {$env}\n\n";

// Tables used by inspecciones that have useTimestamps=true in their models
$tables = [
    'tbl_acta_visita',
    'tbl_clientes',
    'tbl_reporte',
    'tbl_pendientes',
];

foreach ($tables as $table) {
    echo "=== {$table} ===\n";
    try {
        $stmt = $pdo->query("DESCRIBE {$table}");
        $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $hasCreated = false;
        $hasUpdated = false;
        foreach ($cols as $c) {
            if ($c['Field'] === 'created_at') $hasCreated = true;
            if ($c['Field'] === 'updated_at') $hasUpdated = true;
        }
        echo "  created_at: " . ($hasCreated ? 'YES' : 'MISSING') . "\n";
        echo "  updated_at: " . ($hasUpdated ? 'YES' : 'MISSING') . "\n";
    } catch (Exception $e) {
        echo "  ERROR: " . $e->getMessage() . "\n";
    }
}
