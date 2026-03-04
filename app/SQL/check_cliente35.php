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

// 1. Check client 35
echo "=== Cliente id_cliente=35 ===\n";
$stmt = $pdo->prepare("SELECT * FROM tbl_clientes WHERE id_cliente = 35");
$stmt->execute();
$r = $stmt->fetch(PDO::FETCH_ASSOC);
if ($r) {
    foreach ($r as $k => $v) {
        $val = ($v !== null && $v !== '') ? substr($v, 0, 100) : '(vacío)';
        echo "  {$k}: {$val}\n";
    }
} else {
    echo "  (NO EXISTE id_cliente=35)\n";
}

// 2. Check what the select2 query returns - clients near id 35
echo "\n=== Clientes id 30-40 ===\n";
$stmt = $pdo->query("SELECT id_cliente, nombre_cliente, estado_cliente FROM tbl_clientes WHERE id_cliente BETWEEN 30 AND 40 ORDER BY id_cliente");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
    echo "  #{$r['id_cliente']} | {$r['nombre_cliente']} | estado={$r['estado_cliente']}\n";
}

// 3. Check total clients and if there's a filter on estado
echo "\n=== Total clientes ===\n";
$stmt = $pdo->query("SELECT estado_cliente, COUNT(*) as cnt FROM tbl_clientes GROUP BY estado_cliente");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
    echo "  estado={$r['estado_cliente']}: {$r['cnt']}\n";
}
