<?php
/**
 * Inspeccion SOLO LECTURA previa a insertar actividad de "Reseñas Google Maps"
 * para todos los clientes activos en tbl_pta_cliente - mes de mayo 2026.
 *
 * Uso: DB_PROD_PASS=xxx php inspect_pta_mayo.php production
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
    die("Uso: php inspect_pta_mayo.php [local|production]\n");
}

echo "=== INSPECCION PTA - Actividad Resenas Google Maps MAYO 2026 ===\n";
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

// 1) Estructura de tbl_pta_cliente
echo "--- 1) Columnas de tbl_pta_cliente ---\n";
$res = $mysqli->query("SHOW COLUMNS FROM tbl_pta_cliente");
while ($row = $res->fetch_assoc()) {
    echo sprintf("  %-45s %-25s %s %s %s\n",
        $row['Field'], $row['Type'],
        $row['Null'],
        $row['Default'] !== null ? "DEFAULT '{$row['Default']}'" : '',
        $row['Extra']
    );
}
echo "\n";

// 2) Estructura de tbl_clientes (campos relevantes para "activo")
echo "--- 2) Columnas relevantes de tbl_clientes (activo/estado) ---\n";
$res = $mysqli->query("SHOW COLUMNS FROM tbl_clientes");
while ($row = $res->fetch_assoc()) {
    $f = strtolower($row['Field']);
    if (strpos($f,'activ')!==false || strpos($f,'estado')!==false
        || strpos($f,'id_cliente')!==false || strpos($f,'nombre')!==false
        || strpos($f,'razon')!==false) {
        echo sprintf("  %-40s %-25s\n", $row['Field'], $row['Type']);
    }
}
echo "\n";

// 3) Distinct values de campos "estado" / "activo" en tbl_clientes
echo "--- 3) Valores distintos de campos de estado en tbl_clientes ---\n";
$res = $mysqli->query("SHOW COLUMNS FROM tbl_clientes");
$candidatos = [];
while ($row = $res->fetch_assoc()) {
    $f = strtolower($row['Field']);
    if (strpos($f,'activ')!==false || strpos($f,'estado')!==false) {
        $candidatos[] = $row['Field'];
    }
}
foreach ($candidatos as $col) {
    $q = $mysqli->query("SELECT `$col`, COUNT(*) c FROM tbl_clientes GROUP BY `$col`");
    echo "  Campo: $col\n";
    while ($r = $q->fetch_assoc()) {
        echo "    " . ($r[$col] === null ? 'NULL' : "'{$r[$col]}'") . " => {$r['c']} filas\n";
    }
}
echo "\n";

// 4) Clientes distintos presentes en tbl_pta_cliente (los que ya tienen plan activo)
echo "--- 4) Clientes distintos en tbl_pta_cliente ---\n";
$res = $mysqli->query("SELECT COUNT(DISTINCT id_cliente) c FROM tbl_pta_cliente");
$r = $res->fetch_assoc();
echo "  Total distintos en PTA: {$r['c']}\n";

$res = $mysqli->query("
    SELECT p.id_cliente, COUNT(*) actividades, c.nombre_cliente, c.estado
    FROM tbl_pta_cliente p
    LEFT JOIN tbl_clientes c ON c.id_cliente = p.id_cliente
    GROUP BY p.id_cliente, c.nombre_cliente, c.estado
    ORDER BY p.id_cliente
");
echo "  Lista (primeros 80):\n";
$n = 0;
while (($row = $res->fetch_assoc()) && $n < 80) {
    echo sprintf("    id=%-6s act=%-5s estado=%-10s  %s\n",
        $row['id_cliente'], $row['actividades'],
        $row['estado'] ?? 'NULL',
        $row['nombre_cliente'] ?? '(sin cliente)');
    $n++;
}
echo "\n";

// 5) Cruzar con tbl_clientes para ver clientes activos con PTA
echo "--- 5) Cruce tbl_pta_cliente x tbl_clientes.estado ---\n";
$res = $mysqli->query("
    SELECT c.estado, COUNT(DISTINCT p.id_cliente) n
    FROM tbl_pta_cliente p
    LEFT JOIN tbl_clientes c ON c.id_cliente = p.id_cliente
    GROUP BY c.estado
");
while ($row = $res->fetch_assoc()) {
    echo "  estado=" . ($row['estado'] === null ? 'NULL' : "'{$row['estado']}'") . " => {$row['n']} clientes distintos\n";
}
echo "\n";

// 5b) TARGET: clientes activos que estan en tbl_pta_cliente
echo "--- 5b) TARGET: clientes con estado='activo' que tienen PTA ---\n";
$res = $mysqli->query("
    SELECT COUNT(DISTINCT p.id_cliente) n
    FROM tbl_pta_cliente p
    INNER JOIN tbl_clientes c ON c.id_cliente = p.id_cliente
    WHERE c.estado = 'activo'
");
$r = $res->fetch_assoc();
echo "  => {$r['n']} clientes activos con PTA (seran los insertados)\n\n";

// 7) Muestra de una actividad reciente (para ver convenciones)
echo "--- 7) 3 ultimas actividades insertadas en tbl_pta_cliente ---\n";
$res = $mysqli->query("SELECT * FROM tbl_pta_cliente ORDER BY created_at DESC LIMIT 3");
while ($row = $res->fetch_assoc()) {
    echo "  ---\n";
    foreach ($row as $k => $v) {
        echo "    $k: " . ($v === null ? 'NULL' : $v) . "\n";
    }
}
echo "\n";

// 8) Verificar si existe alguna actividad previa de "resenas google" para no duplicar
echo "--- 8) Actividades existentes con 'google' o 'resena' en actividad_plandetrabajo ---\n";
$res = $mysqli->query("
    SELECT id_ptacliente, id_cliente, phva_plandetrabajo, numeral_plandetrabajo,
           LEFT(actividad_plandetrabajo, 90) AS actividad, fecha_propuesta, estado_actividad
    FROM tbl_pta_cliente
    WHERE actividad_plandetrabajo LIKE '%oogle%'
       OR actividad_plandetrabajo LIKE '%ese%a%'
       OR actividad_plandetrabajo LIKE '%opinion%'
");
$n = 0;
while ($row = $res->fetch_assoc()) {
    echo sprintf("  id_pta=%s cli=%s phva=%s num=%s fecha=%s estado=%s\n    act=%s\n",
        $row['id_ptacliente'], $row['id_cliente'],
        $row['phva_plandetrabajo'], $row['numeral_plandetrabajo'],
        $row['fecha_propuesta'], $row['estado_actividad'],
        $row['actividad']);
    $n++;
}
echo "  Total coincidencias: $n\n\n";

echo "=== FIN INSPECCION (no se modifico nada) ===\n";
$mysqli->close();
