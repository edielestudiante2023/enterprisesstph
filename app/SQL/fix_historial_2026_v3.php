<?php
/**
 * Fix historial v3:
 * For Jan/Feb 2026 plan trabajo records, set actividades_abiertas = total count from tbl_pta_cliente
 * (baseline: all activities open at start of cycle)
 *
 * Usage: DB_PROD_PASS=xxx php fix_historial_2026_v3.php production
 */

$env = $argv[1] ?? 'local';

if ($env === 'production') {
    $host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port = 25060;
    $user = 'cycloid_userdb';
    $pass = getenv('DB_PROD_PASS');
    $db   = 'propiedad_horizontal';
    if (!$pass) die("ERROR: DB_PROD_PASS env var not set\n");
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

// 1. Get actual activity counts per client from tbl_pta_cliente (2026 cycle)
echo "\n=== Getting actual PTA counts per client (2026 cycle) ===\n";
$res = $conn->query("SELECT id_cliente, COUNT(*) as total FROM tbl_pta_cliente WHERE created_at >= '2026-01-01' AND created_at <= '2026-12-31' GROUP BY id_cliente");
$counts = [];
while ($row = $res->fetch_assoc()) {
    $counts[$row['id_cliente']] = intval($row['total']);
    echo "  Cliente {$row['id_cliente']}: {$row['total']} actividades\n";
}
echo "Total clients with activities: " . count($counts) . "\n";

// 2. Update Jan/Feb historial records with actual counts (all open at baseline)
echo "\n=== Updating Jan/Feb 2026 PLAN TRABAJO with actual counts ===\n";
$updated = 0;
foreach ($counts as $idCliente => $total) {
    $stmt = $conn->prepare("UPDATE historial_resumen_plan_trabajo SET total_actividades = ?, actividades_abiertas = ?, porcentaje_abiertas = 100.00 WHERE id_cliente = ? AND YEAR(fecha_extraccion) = 2026 AND MONTH(fecha_extraccion) IN (1, 2)");
    $stmt->bind_param('iii', $total, $total, $idCliente);
    $stmt->execute();
    $updated += $stmt->affected_rows;
    if ($stmt->affected_rows > 0) {
        echo "  Cliente {$idCliente}: set total={$total}, abiertas={$total}\n";
    }
}
echo "Total rows updated: {$updated}\n";

// 3. Verify
echo "\n=== Verification: 2026 PLAN TRABAJO (first 20) ===\n";
$res2 = $conn->query("SELECT id_cliente, total_actividades, actividades_abiertas, porcentaje_abiertas, fecha_extraccion FROM historial_resumen_plan_trabajo WHERE YEAR(fecha_extraccion) = 2026 ORDER BY fecha_extraccion, id_cliente LIMIT 20");
while ($row = $res2->fetch_assoc()) {
    echo "  Cliente {$row['id_cliente']}: total={$row['total_actividades']}, abiertas={$row['actividades_abiertas']}, %={$row['porcentaje_abiertas']}% @ {$row['fecha_extraccion']}\n";
}

echo "\nDone!\n";
$conn->close();
