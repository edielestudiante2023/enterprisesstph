<?php
/**
 * Inserta los detail_report para los 3 planes de contingencia
 * Uso: php add_detail_report_contingencias.php [local|production]
 */
$env = $argv[1] ?? 'local';

if ($env === 'production') {
    $host   = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port   = 25060;
    $dbname = 'propiedad_horizontal';
    $user   = 'cycloid_userdb';
    $pass   = getenv('DB_PROD_PASS');
    $ssl    = true;
} else {
    $host   = '127.0.0.1';
    $port   = 3306;
    $dbname = 'propiedad_horizontal';
    $user   = 'root';
    $pass   = '';
    $ssl    = false;
}

$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
if ($ssl) {
    $options[PDO::MYSQL_ATTR_SSL_CA]                 = true;
    $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
}

$pdo = new PDO($dsn, $user, $pass, $options);
echo "[{$env}] Conectado a {$dbname}\n";

$registros = [
    [36, 'Plan Contingencia Plagas'],
    [38, 'Plan Contingencia Sin Agua'],
    [39, 'Plan Contingencia Basura'],
];

$stmt = $pdo->prepare("INSERT IGNORE INTO detail_report (id_detailreport, detail_report) VALUES (?, ?)");
foreach ($registros as $r) {
    $stmt->execute([$r[0], $r[1]]);
    $affected = $stmt->rowCount();
    if ($affected > 0) {
        echo "[{$env}] OK — insertado id={$r[0]} '{$r[1]}'\n";
    } else {
        echo "[{$env}] Ya existe id={$r[0]} '{$r[1]}'. Saltando.\n";
    }
}

echo "[{$env}] Completado.\n";
