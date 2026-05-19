<?php
/**
 * Diagnóstico — clasificación de documentos del cliente mirto2
 * SOLO LECTURA. No modifica nada.
 */

$db = new mysqli();
$db->ssl_set(null, null, '/www/ca/ca-certificate_cycloid.crt', null, null);
$db->real_connect(
    'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
    'cycloid_userdb',
    getenv('DB_PROD_PASS'),
    'propiedad_horizontal',
    25060,
    null,
    MYSQLI_CLIENT_SSL
);

if ($db->connect_error) {
    die("Error conexión: " . $db->connect_error . "\n");
}
$db->set_charset('utf8mb4');

echo "==========================================================\n";
echo "DIAGNÓSTICO clasificación documentos cliente mirto2\n";
echo "==========================================================\n\n";

// --- 1. Localizar al cliente mirto2 ---
$r = $db->query("SELECT id_cliente, nombre_cliente, nit_cliente FROM tbl_clientes WHERE LOWER(nombre_cliente) LIKE '%mirto%' OR LOWER(nit_cliente) LIKE '%mirto%' ORDER BY id_cliente");
echo "--- 1. Clientes que coinciden con 'mirto' ---\n";
$clienteId = null;
while ($row = $r->fetch_assoc()) {
    echo "  ID={$row['id_cliente']} | nombre={$row['nombre_cliente']} | nit={$row['nit_cliente']}\n";
    if (stripos($row['nombre_cliente'], 'mirto2') !== false || stripos($row['nit_cliente'], 'mirto2') !== false) {
        $clienteId = (int)$row['id_cliente'];
    }
}
if (!$clienteId) {
    echo "\nNo se identificó exactamente 'mirto2'. Probando con cualquier mirto.\n";
    $r = $db->query("SELECT id_cliente FROM tbl_clientes WHERE LOWER(nombre_cliente) LIKE '%mirto%' LIMIT 1");
    if ($row = $r->fetch_assoc()) $clienteId = (int)$row['id_cliente'];
}
echo "\n>> Usando id_cliente = $clienteId\n\n";

// --- 2. Catálogo detail_report ---
echo "--- 2. CATÁLOGO detail_report (lo que se muestra en reportList como 'Tipo de Documento') ---\n";
$r = $db->query("SELECT id_detailreport, detail_report FROM detail_report ORDER BY id_detailreport");
$catalogo = [];
while ($row = $r->fetch_assoc()) {
    $catalogo[(int)$row['id_detailreport']] = $row['detail_report'];
    echo "  [{$row['id_detailreport']}] {$row['detail_report']}\n";
}

echo "\n--- 3. CATÁLOGO report_type_table (campo 'Tipo de Reporte') ---\n";
$r = $db->query("SELECT id_report_type, report_type FROM report_type_table ORDER BY id_report_type");
$tipos = [];
while ($row = $r->fetch_assoc()) {
    $tipos[(int)$row['id_report_type']] = $row['report_type'];
    echo "  [{$row['id_report_type']}] {$row['report_type']}\n";
}

// --- 4. ÚLTIMOS DOCS del cliente mirto2 ---
echo "\n--- 4. ÚLTIMOS 40 DOCUMENTOS del cliente $clienteId (orden desc por created_at) ---\n";
$stmt = $db->prepare("SELECT r.id_reporte, r.created_at, r.titulo_reporte, r.id_detailreport, r.id_report_type, r.observaciones, r.enlace
                      FROM tbl_reporte r
                      WHERE r.id_cliente = ?
                      ORDER BY r.created_at DESC, r.id_reporte DESC
                      LIMIT 40");
$stmt->bind_param('i', $clienteId);
$stmt->execute();
$result = $stmt->get_result();

printf("%-6s | %-19s | %-3s | %-35s | %-3s | %-25s | %s\n",
    'ID', 'fecha', 'dr', 'detail_report (catálogo)', 'rt', 'report_type', 'titulo / obs'
);
echo str_repeat('-', 200) . "\n";
$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
    $dr  = (int)$row['id_detailreport'];
    $rt  = (int)$row['id_report_type'];
    $drN = $catalogo[$dr] ?? '???';
    $rtN = $tipos[$rt] ?? '???';
    $titulo = substr($row['titulo_reporte'], 0, 70);
    $obs = substr($row['observaciones'] ?? '', 0, 60);
    printf("%-6d | %-19s | %-3d | %-35s | %-3d | %-25s | %s | %s\n",
        $row['id_reporte'], $row['created_at'], $dr, substr($drN,0,35), $rt, substr($rtN,0,25), $titulo, $obs
    );
}

// --- 5. Diagnóstico cruzado: ¿qué id_detailreport están siendo usados con qué títulos? ---
echo "\n--- 5. AGRUPACIÓN: tipo de doc (detail_report) → cantidad y muestras del título ---\n";
$stmt = $db->prepare("SELECT r.id_detailreport,
                            COUNT(*) total,
                            GROUP_CONCAT(DISTINCT SUBSTRING_INDEX(r.titulo_reporte,' - ',2) ORDER BY r.created_at DESC SEPARATOR ' || ') muestras
                     FROM tbl_reporte r
                     WHERE r.id_cliente = ?
                     GROUP BY r.id_detailreport
                     ORDER BY total DESC");
$stmt->bind_param('i', $clienteId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $dr  = (int)$row['id_detailreport'];
    $drN = $catalogo[$dr] ?? '???';
    echo "\n  [{$dr}] {$drN}  → {$row['total']} docs\n";
    foreach (explode(' || ', $row['muestras']) as $m) {
        echo "      · " . substr(trim($m), 0, 110) . "\n";
    }
}

$db->close();
echo "\n==========================================================\n";
echo "Diagnóstico completado.\n";
