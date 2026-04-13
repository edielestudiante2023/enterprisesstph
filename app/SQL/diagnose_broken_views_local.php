<?php
/**
 * Diagnóstico de vistas rotas en LOCAL (XAMPP).
 * Uso: php app/SQL/diagnose_broken_views_local.php
 * SOLO LECTURA.
 */

$host = '127.0.0.1';
$port = 3306;
$db   = 'propiedad_horizontal';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$db",
        $user, $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 10]
    );
    echo "Conectado OK a LOCAL.\n\n";
} catch (Exception $e) {
    fwrite(STDERR, "FAILED: " . $e->getMessage() . "\n");
    exit(2);
}

$views = $pdo->prepare("SELECT TABLE_NAME FROM information_schema.VIEWS WHERE TABLE_SCHEMA = :db ORDER BY TABLE_NAME");
$views->execute([':db' => $db]);
$views = $views->fetchAll(PDO::FETCH_COLUMN);

$total = count($views);
echo "Vistas encontradas: $total\n" . str_repeat('-', 70) . "\n";

$broken = [];
foreach ($views as $i => $v) {
    $n = $i + 1;
    try {
        $pdo->query("SHOW FIELDS FROM `$v`");
        echo sprintf("[%3d/%d] OK     %s\n", $n, $total, $v);
    } catch (Exception $e) {
        $broken[$v] = $e->getMessage();
        echo sprintf("[%3d/%d] BROKEN %s\n         -> %s\n", $n, $total, $v, $e->getMessage());
    }
}

echo "\n" . str_repeat('=', 70) . "\n";
echo "RESUMEN LOCAL\n";
echo str_repeat('=', 70) . "\n";
echo "Vistas OK:    " . ($total - count($broken)) . "\n";
echo "Vistas ROTAS: " . count($broken) . "\n";

if ($broken) {
    echo "\n--- Detalle ---\n";
    foreach ($broken as $v => $m) echo "* $v\n  $m\n\n";
}

// Estado de las tablas relevantes
echo str_repeat('=', 70) . "\n";
echo "ESTADO TABLAS EVALUACION\n";
echo str_repeat('=', 70) . "\n";
$stmt = $pdo->prepare(
    "SELECT TABLE_NAME FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = :db AND TABLE_TYPE='BASE TABLE'
       AND TABLE_NAME LIKE 'tbl_evaluacion%' ORDER BY TABLE_NAME"
);
$stmt->execute([':db' => $db]);
foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $t) {
    echo " - $t\n";
}
