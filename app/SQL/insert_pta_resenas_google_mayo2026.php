<?php
/**
 * Inserta la actividad "Solicitar reseñas y opiniones positivas en Google Maps
 * al cliente" para todos los clientes activos (tbl_clientes.estado='activo')
 * que ya están en tbl_pta_cliente. Fecha propuesta: 2026-05-31.
 *
 * Uso: DB_PROD_PASS=xxx php insert_pta_resenas_google_mayo2026.php production
 */

if (php_sapi_name() !== 'cli') { die("Solo CLI.\n"); }

$env = $argv[1] ?? 'local';

if ($env === 'local') {
    $config = [
        'host' => '127.0.0.1', 'port' => 3306, 'user' => 'root',
        'password' => '', 'database' => 'propiedad_horizontal', 'ssl' => false,
    ];
} elseif ($env === 'production') {
    $config = [
        'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port' => 25060, 'user' => 'cycloid_userdb',
        'password' => getenv('DB_PROD_PASS') ?: '',
        'database' => 'propiedad_horizontal', 'ssl' => true,
    ];
} else {
    die("Uso: php insert_pta_resenas_google_mayo2026.php [local|production]\n");
}

echo "=== INSERT actividad Resenas Google Maps - Mayo 2026 ===\n";
echo "Entorno: " . strtoupper($env) . "\n";
echo "Host: {$config['host']}:{$config['port']}\n\n";

$mysqli = mysqli_init();
if ($config['ssl']) {
    $mysqli->ssl_set(null, null, null, null, null);
    $mysqli->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
}
if (!@$mysqli->real_connect($config['host'], $config['user'], $config['password'],
        $config['database'], $config['port'], null,
        $config['ssl'] ? MYSQLI_CLIENT_SSL : 0)) {
    die("ERROR conexion: " . $mysqli->connect_error . "\n");
}
echo "Conexion OK.\n\n";

// 1) Conteo previo
$pre = $mysqli->query("SELECT COUNT(*) c FROM tbl_pta_cliente")->fetch_assoc()['c'];
echo "Filas en tbl_pta_cliente ANTES: $pre\n";

$target = $mysqli->query("
    SELECT COUNT(*) c
    FROM tbl_clientes c
    WHERE c.estado = 'activo'
      AND EXISTS (SELECT 1 FROM tbl_pta_cliente p WHERE p.id_cliente = c.id_cliente)
")->fetch_assoc()['c'];
echo "Clientes objetivo (activos con PTA): $target\n\n";

// 2) Transaccion
$mysqli->begin_transaction();

$sql = "
INSERT INTO tbl_pta_cliente
  (id_cliente, phva_plandetrabajo, numeral_plandetrabajo, actividad_plandetrabajo,
   responsable_sugerido_plandetrabajo, fecha_propuesta, fecha_cierre,
   responsable_definido_paralaactividad, estado_actividad, porcentaje_avance,
   observaciones, created_at, updated_at)
SELECT
  c.id_cliente,
  'HACER',
  '2.6.1',
  'Solicitar reseñas y opiniones positivas en Google Maps al cliente',
  'CONSULTOR CYCLOID',
  '2026-05-31',
  NULL,
  '-',
  'ABIERTA',
  0.00,
  NULL,
  NOW(),
  NOW()
FROM tbl_clientes c
WHERE c.estado = 'activo'
  AND EXISTS (SELECT 1 FROM tbl_pta_cliente p WHERE p.id_cliente = c.id_cliente)
";

if (!$mysqli->query($sql)) {
    echo "ERROR INSERT: " . $mysqli->error . "\n";
    $mysqli->rollback();
    $mysqli->close();
    exit(1);
}

$inserted = $mysqli->affected_rows;
echo "Filas insertadas: $inserted\n";

if ((int)$inserted !== (int)$target) {
    echo "ADVERTENCIA: insertadas ($inserted) != objetivo ($target). Haciendo ROLLBACK.\n";
    $mysqli->rollback();
    $mysqli->close();
    exit(1);
}

$mysqli->commit();
echo "COMMIT OK.\n\n";

// 3) Verificacion
$post = $mysqli->query("SELECT COUNT(*) c FROM tbl_pta_cliente")->fetch_assoc()['c'];
echo "Filas en tbl_pta_cliente DESPUES: $post (delta: " . ($post - $pre) . ")\n\n";

echo "--- Muestra de 5 filas insertadas ---\n";
$res = $mysqli->query("
    SELECT p.id_ptacliente, p.id_cliente, c.nombre_cliente, p.fecha_propuesta,
           p.phva_plandetrabajo, p.numeral_plandetrabajo,
           LEFT(p.actividad_plandetrabajo, 70) act
    FROM tbl_pta_cliente p
    LEFT JOIN tbl_clientes c ON c.id_cliente = p.id_cliente
    WHERE p.actividad_plandetrabajo = 'Solicitar reseñas y opiniones positivas en Google Maps al cliente'
      AND p.fecha_propuesta = '2026-05-31'
    ORDER BY p.id_ptacliente DESC
    LIMIT 5
");
while ($r = $res->fetch_assoc()) {
    echo sprintf("  pta=%s cli=%s fecha=%s %s/%s  %s\n    %s\n",
        $r['id_ptacliente'], $r['id_cliente'], $r['fecha_propuesta'],
        $r['phva_plandetrabajo'], $r['numeral_plandetrabajo'],
        $r['nombre_cliente'], $r['act']);
}

echo "\n=== FIN ===\n";
$mysqli->close();
