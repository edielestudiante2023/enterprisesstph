<?php
/**
 * sync_estandares_from_contratos.php
 *
 * Script one-shot para sincronizar tbl_clientes.estandares
 * derivándolo de tbl_contratos.frecuencia_visitas (contrato activo).
 *
 * Uso:
 *   LOCAL:      php app/SQL/sync_estandares_from_contratos.php
 *   PRODUCCIÓN: DB_PROD_PASS=xxx php app/SQL/sync_estandares_from_contratos.php production
 */

$env = $argv[1] ?? 'local';

if ($env === 'production') {
    $host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port = 25060;
    $user = 'cycloid_userdb';
    $pass = getenv('DB_PROD_PASS');
    $db   = 'propiedad_horizontal';
    if (!$pass) {
        die("ERROR: Variable DB_PROD_PASS no definida.\n");
    }
} else {
    $host = '127.0.0.1';
    $port = 3306;
    $user = 'root';
    $pass = '';
    $db   = 'empresas_sst';
}

echo "Conectando a [$env] {$host}:{$port}/{$db}...\n";

$mysqli = new mysqli($host, $user, $pass, $db, $port);
if ($mysqli->connect_errno) {
    die("ERROR conexión: " . $mysqli->connect_error . "\n");
}
$mysqli->set_charset('utf8mb4');

// 1. Preview: mostrar clientes que van a cambiar
$previewSql = "
SELECT
    c.id_cliente,
    c.nombre_cliente,
    c.estandares AS estandares_actual,
    ct.frecuencia_visitas,
    CASE ct.frecuencia_visitas
        WHEN 'MENSUAL'    THEN 'Mensual'
        WHEN 'BIMENSUAL'  THEN 'Bimensual'
        WHEN 'TRIMESTRAL' THEN 'Trimestral'
        WHEN 'PROYECTO'   THEN 'Proyecto'
        WHEN 'SEMESTRAL'  THEN 'Proyecto'
        WHEN 'ANUAL'      THEN 'Proyecto'
        ELSE c.estandares
    END AS estandares_nuevo
FROM tbl_clientes c
JOIN tbl_contratos ct ON ct.id_cliente = c.id_cliente AND ct.estado = 'activo'
WHERE ct.frecuencia_visitas IS NOT NULL
HAVING estandares_actual != estandares_nuevo
ORDER BY c.nombre_cliente
";

$result = $mysqli->query($previewSql);
if (!$result) {
    die("ERROR en preview: " . $mysqli->error . "\n");
}

$rows = $result->fetch_all(MYSQLI_ASSOC);
$count = count($rows);

if ($count === 0) {
    echo "OK: Todos los clientes ya tienen estandares sincronizado. No hay cambios necesarios.\n";
    exit(0);
}

echo "\nClientes con discrepancia ({$count}):\n";
echo str_pad('id', 6) . str_pad('nombre', 50) . str_pad('actual', 15) . str_pad('contrato', 15) . "→ nuevo\n";
echo str_repeat('-', 100) . "\n";
foreach ($rows as $row) {
    echo str_pad($row['id_cliente'], 6)
       . str_pad(substr($row['nombre_cliente'], 0, 48), 50)
       . str_pad($row['estandares_actual'] ?? '(null)', 15)
       . str_pad($row['frecuencia_visitas'], 15)
       . $row['estandares_nuevo'] . "\n";
}

echo "\n¿Aplicar UPDATE? [s/N]: ";
$handle = fopen('php://stdin', 'r');
$confirm = strtolower(trim(fgets($handle)));
fclose($handle);

if ($confirm !== 's') {
    echo "Cancelado. No se realizaron cambios.\n";
    exit(0);
}

// 2. Aplicar el UPDATE
$updateSql = "
UPDATE tbl_clientes c
JOIN tbl_contratos ct ON ct.id_cliente = c.id_cliente AND ct.estado = 'activo'
SET c.estandares = CASE ct.frecuencia_visitas
    WHEN 'MENSUAL'    THEN 'Mensual'
    WHEN 'BIMENSUAL'  THEN 'Bimensual'
    WHEN 'TRIMESTRAL' THEN 'Trimestral'
    WHEN 'PROYECTO'   THEN 'Proyecto'
    WHEN 'SEMESTRAL'  THEN 'Proyecto'
    WHEN 'ANUAL'      THEN 'Proyecto'
    ELSE c.estandares END
WHERE ct.frecuencia_visitas IS NOT NULL
";

if (!$mysqli->query($updateSql)) {
    die("ERROR en UPDATE: " . $mysqli->error . "\n");
}

$affected = $mysqli->affected_rows;
echo "OK: {$affected} registro(s) actualizado(s) en [{$env}].\n";
