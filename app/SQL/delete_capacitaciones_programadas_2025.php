<?php
/**
 * One-shot: DELETE capacitaciones estado=PROGRAMADA con fecha_programada en 2025.
 * Uso: php delete_capacitaciones_programadas_2025.php [local|production]
 * Idempotente (si no hay filas, afectadas=0).
 */

if (php_sapi_name() !== 'cli') die("Solo CLI.\n");

$env = $argv[1] ?? 'local';

if ($env === 'local') {
    $config = ['host' => '127.0.0.1', 'port' => 3306, 'user' => 'root', 'password' => '', 'database' => 'propiedad_horizontal', 'ssl' => false];
} elseif ($env === 'production') {
    $config = ['host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com', 'port' => 25060, 'user' => 'cycloid_userdb', 'password' => getenv('DB_PROD_PASS') ?: '', 'database' => 'propiedad_horizontal', 'ssl' => true];
} else {
    die("Uso: php delete_capacitaciones_programadas_2025.php [local|production]\n");
}

echo "=== DELETE capacitaciones PROGRAMADA de 2025 ===\n";
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

    $antes = $pdo->query("SELECT COUNT(*) FROM tbl_cronog_capacitacion WHERE estado='PROGRAMADA' AND YEAR(fecha_programada)=2025")->fetchColumn();
    echo "Candidatas a borrar: {$antes}\n";

    if ($antes > 0) {
        echo "Listado previo:\n";
        $q = $pdo->query("SELECT id_cronograma_capacitacion id, id_cliente, fecha_programada, LEFT(nombre_capacitacion,60) nombre FROM tbl_cronog_capacitacion WHERE estado='PROGRAMADA' AND YEAR(fecha_programada)=2025 ORDER BY fecha_programada");
        foreach ($q as $r) echo "  id={$r['id']} | cli={$r['id_cliente']} | {$r['fecha_programada']} | {$r['nombre']}\n";
    }

    $n = $pdo->exec("DELETE FROM tbl_cronog_capacitacion WHERE estado='PROGRAMADA' AND YEAR(fecha_programada)=2025");
    echo "Filas eliminadas: {$n}\n";

    $despues = $pdo->query("SELECT COUNT(*) FROM tbl_cronog_capacitacion WHERE estado='PROGRAMADA' AND YEAR(fecha_programada)=2025")->fetchColumn();
    echo "Verificacion post-delete (deberia ser 0): {$despues}\n";

    echo "DELETE OK.\n";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
