<?php
/**
 * One-shot: UPDATE fecha_programada -> 2026-05-31
 * en capacitaciones estado=PROGRAMADA con fecha_programada <= 2026-04-30.
 * Uso: php update_capacitaciones_programadas_a_mayo.php [local|production]
 * Idempotente (si no hay filas candidatas, afectadas=0).
 */

if (php_sapi_name() !== 'cli') die("Solo CLI.\n");

$env = $argv[1] ?? 'local';

if ($env === 'local') {
    $config = ['host' => '127.0.0.1', 'port' => 3306, 'user' => 'root', 'password' => '', 'database' => 'propiedad_horizontal', 'ssl' => false];
} elseif ($env === 'production') {
    $config = ['host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com', 'port' => 25060, 'user' => 'cycloid_userdb', 'password' => getenv('DB_PROD_PASS') ?: '', 'database' => 'propiedad_horizontal', 'ssl' => true];
} else {
    die("Uso: php update_capacitaciones_programadas_a_mayo.php [local|production]\n");
}

$fechaNueva = '2026-05-31';
$limite     = '2026-04-30';

echo "=== UPDATE capacitaciones PROGRAMADA <= {$limite} -> {$fechaNueva} ===\n";
echo "Entorno: " . strtoupper($env) . " | DB: {$config['database']}\n---\n";

$dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
$opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
if ($config['ssl']) {
    $opts[PDO::MYSQL_ATTR_SSL_CA] = true;
    $opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
}

try {
    $pdo = new PDO($dsn, $config['user'], $config['password'], $opts);
    echo "Conectado.\n";

    $antes = $pdo->prepare("SELECT COUNT(*) FROM tbl_cronog_capacitacion WHERE estado='PROGRAMADA' AND fecha_programada <= ?");
    $antes->execute([$limite]);
    $n = $antes->fetchColumn();
    echo "Candidatas: {$n}\n";

    $stmt = $pdo->prepare("UPDATE tbl_cronog_capacitacion SET fecha_programada = ? WHERE estado='PROGRAMADA' AND fecha_programada <= ?");
    $stmt->execute([$fechaNueva, $limite]);
    $afectadas = $stmt->rowCount();
    echo "Filas actualizadas: {$afectadas}\n";

    $verif = $pdo->prepare("SELECT COUNT(*) FROM tbl_cronog_capacitacion WHERE estado='PROGRAMADA' AND fecha_programada <= ?");
    $verif->execute([$limite]);
    echo "Verificacion post-update (deberia ser 0): " . $verif->fetchColumn() . "\n";

    $hoy = $pdo->prepare("SELECT COUNT(*) FROM tbl_cronog_capacitacion WHERE estado='PROGRAMADA' AND fecha_programada = ?");
    $hoy->execute([$fechaNueva]);
    echo "Total PROGRAMADAs en {$fechaNueva}: " . $hoy->fetchColumn() . "\n";

    echo "UPDATE OK.\n";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
