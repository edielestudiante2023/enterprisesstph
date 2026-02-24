<?php
$pdo = new PDO(
    "mysql:host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com;port=25060;dbname=propiedad_horizontal;charset=utf8mb4",
    'cycloid_userdb', getenv('DB_PROD_PASS'),
    [PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false, PDO::MYSQL_ATTR_SSL_CA => '', PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$rows = $pdo->query('DESCRIBE tbl_pendientes')->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo str_pad($r['Field'], 30) . " | " . str_pad($r['Type'], 20) . " | Null: " . $r['Null'] . " | Default: " . ($r['Default'] ?? 'NULL') . "\n";
}
