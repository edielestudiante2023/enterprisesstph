<?php
/**
 * Fix historial records for Jan/Feb 2026:
 * - Set porcentaje_cumplimiento = 39.75 (baseline) for all clients
 * - Set plan_trabajo metrics to 0
 *
 * Usage: DB_PROD_PASS=xxx php fix_historial_2026.php production
 *   or:  php fix_historial_2026.php local
 */

$env = $argv[1] ?? 'local';

if ($env === 'production') {
    $host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port = 25060;
    $user = 'cycloid_userdb';
    $pass = getenv('DB_PROD_PASS');
    $db   = 'propiedad_horizontal';
    if (!$pass) {
        die("ERROR: DB_PROD_PASS env var not set\n");
    }
} else {
    $host = 'localhost';
    $port = 3306;
    $user = 'root';
    $pass = '';
    $db   = 'propiedad_horizontal';
}

echo "Connecting to {$env} ({$host}:{$port})...\n";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn = new mysqli($host, $user, $pass, $db, $port);

if ($env === 'production') {
    $conn->ssl_set(null, null, null, null, null);
}

// 1. Check current records
echo "\n=== Current Jan/Feb 2026 ESTANDARES records ===\n";
$res = $conn->query("SELECT id_cliente, nombre_cliente, porcentaje_cumplimiento, fecha_extraccion
    FROM historial_resumen_estandares
    WHERE YEAR(fecha_extraccion) = 2026 AND MONTH(fecha_extraccion) IN (1,2)
    ORDER BY fecha_extraccion, id_cliente");
$count = 0;
while ($row = $res->fetch_assoc()) {
    echo "  Cliente {$row['id_cliente']} ({$row['nombre_cliente']}): {$row['porcentaje_cumplimiento']}% @ {$row['fecha_extraccion']}\n";
    $count++;
}
echo "Total: {$count} records\n";

echo "\n=== Current Jan/Feb 2026 PLAN TRABAJO records ===\n";
$res2 = $conn->query("SELECT id_cliente, nombre_cliente, porcentaje_abiertas, fecha_extraccion
    FROM historial_resumen_plan_trabajo
    WHERE YEAR(fecha_extraccion) = 2026 AND MONTH(fecha_extraccion) IN (1,2)
    ORDER BY fecha_extraccion, id_cliente");
$count2 = 0;
while ($row = $res2->fetch_assoc()) {
    echo "  Cliente {$row['id_cliente']} ({$row['nombre_cliente']}): {$row['porcentaje_abiertas']}% @ {$row['fecha_extraccion']}\n";
    $count2++;
}
echo "Total: {$count2} records\n";

// 2. Update estandares to baseline 39.75
echo "\n=== Updating ESTANDARES Jan/Feb 2026 → 39.75% baseline ===\n";
$stmt = $conn->prepare("UPDATE historial_resumen_estandares
    SET total_valor = 100, total_puntaje = 39.75, porcentaje_cumplimiento = 39.75
    WHERE YEAR(fecha_extraccion) = 2026 AND MONTH(fecha_extraccion) IN (1,2)");
$stmt->execute();
echo "Affected rows: {$stmt->affected_rows}\n";

// 3. Update plan trabajo to 0
echo "\n=== Updating PLAN TRABAJO Jan/Feb 2026 → 0% baseline ===\n";
$stmt2 = $conn->prepare("UPDATE historial_resumen_plan_trabajo
    SET total_actividades = 0, actividades_abiertas = 0, porcentaje_abiertas = 0
    WHERE YEAR(fecha_extraccion) = 2026 AND MONTH(fecha_extraccion) IN (1,2)");
$stmt2->execute();
echo "Affected rows: {$stmt2->affected_rows}\n";

// 4. Verify
echo "\n=== Verification ESTANDARES ===\n";
$res3 = $conn->query("SELECT id_cliente, nombre_cliente, porcentaje_cumplimiento, fecha_extraccion
    FROM historial_resumen_estandares
    WHERE YEAR(fecha_extraccion) = 2026 AND MONTH(fecha_extraccion) IN (1,2)
    ORDER BY fecha_extraccion, id_cliente");
while ($row = $res3->fetch_assoc()) {
    echo "  Cliente {$row['id_cliente']}: {$row['porcentaje_cumplimiento']}% @ {$row['fecha_extraccion']}\n";
}

echo "\nDone!\n";
$conn->close();
