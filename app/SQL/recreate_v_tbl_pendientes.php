<?php
/**
 * Recrea la view v_tbl_pendientes agregando fecha_plazo y fecha_reclasificacion_auto
 * despues del refactor de fechas en tbl_pendientes.
 * Uso: php recreate_v_tbl_pendientes.php [local|production]
 * Idempotente (CREATE OR REPLACE VIEW).
 */

if (php_sapi_name() !== 'cli') die("Solo CLI.\n");

$env = $argv[1] ?? 'local';

if ($env === 'local') {
    $config = ['host' => '127.0.0.1', 'port' => 3306, 'user' => 'root', 'password' => '', 'database' => 'propiedad_horizontal', 'ssl' => false];
} elseif ($env === 'production') {
    $config = ['host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com', 'port' => 25060, 'user' => 'cycloid_userdb', 'password' => getenv('DB_PROD_PASS') ?: '', 'database' => 'propiedad_horizontal', 'ssl' => true];
} else {
    die("Uso: php recreate_v_tbl_pendientes.php [local|production]\n");
}

echo "=== Recrear view v_tbl_pendientes ===\n";
echo "Entorno: " . strtoupper($env) . " | DB: {$config['database']}\n---\n";

$dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
$opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
if ($config['ssl']) {
    $opts[PDO::MYSQL_ATTR_SSL_CA] = true;
    $opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
}

$sql = <<<SQL
CREATE OR REPLACE VIEW v_tbl_pendientes AS
SELECT
  p.id_pendientes, p.id_cliente, p.id_acta, p.responsable, p.tarea_actividad,
  p.fecha_asignacion, p.fecha_plazo, p.fecha_cierre, p.fecha_reclasificacion_auto,
  p.estado, p.estado_avance, p.evidencia_para_cerrarla, p.conteo_dias,
  p.created_at, p.updated_at, p.id_acta_visita,
  c.nombre_cliente,
  av.fecha_visita
FROM tbl_pendientes p
JOIN tbl_clientes c ON p.id_cliente = c.id_cliente
LEFT JOIN tbl_acta_visita av ON p.id_acta_visita = av.id
SQL;

try {
    $pdo = new PDO($dsn, $config['user'], $config['password'], $opts);
    echo "Conectado.\n";
    $pdo->exec($sql);
    echo "View recreada OK.\n";

    $q = $pdo->query("SHOW COLUMNS FROM v_tbl_pendientes");
    echo "\nColumnas actuales:\n";
    foreach ($q as $r) echo "  - {$r['Field']}\n";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
