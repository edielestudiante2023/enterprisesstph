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

// 1. KPI records
echo "=== KPI records (id_detailreport 33-36) ===\n";
$stmt = $pdo->query("SELECT * FROM tbl_reporte WHERE id_detailreport IN (33,34,35,36) ORDER BY id_reporte DESC");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (empty($rows)) {
    echo "(NINGÚN registro encontrado)\n";
} else {
    foreach ($rows as $r) {
        foreach ($r as $k => $v) {
            echo "  {$k}: " . ($v !== null && $v !== '' ? $v : '(vacío)') . "\n";
        }
        echo "  ---\n";
    }
}

// 2. Last 5 records
echo "\n=== Últimos 5 registros ===\n";
$stmt = $pdo->query("SELECT id_reporte, titulo_reporte, estado, tag, enlace, created_at FROM tbl_reporte ORDER BY id_reporte DESC LIMIT 5");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
    echo "  #{$r['id_reporte']} | " . substr($r['titulo_reporte'], 0, 50) . " | estado={$r['estado']} | tag=" . ($r['tag'] ?: 'NULL') . " | {$r['created_at']}\n";
}

// 3. Check if KPI Limpieza inspection record exists
echo "\n=== tbl_kpi_limpieza records ===\n";
$stmt = $pdo->query("SELECT id, id_cliente, id_consultor, estado, ruta_pdf, fecha_inspeccion FROM tbl_kpi_limpieza ORDER BY id DESC LIMIT 5");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (empty($rows)) {
    echo "(no records)\n";
} else {
    foreach ($rows as $r) {
        foreach ($r as $k => $v) {
            echo "  {$k}: " . ($v !== null && $v !== '' ? $v : '(vacío)') . "\n";
        }
        echo "  ---\n";
    }
}
