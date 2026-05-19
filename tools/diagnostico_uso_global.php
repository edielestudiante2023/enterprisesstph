<?php
/**
 * Uso real del catálogo detail_report — solo lectura.
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
if ($db->connect_error) die("Error: " . $db->connect_error);
$db->set_charset('utf8mb4');

// Catálogo
$cat = [];
$r = $db->query("SELECT id_detailreport, detail_report FROM detail_report ORDER BY id_detailreport");
while ($row = $r->fetch_assoc()) $cat[(int)$row['id_detailreport']] = $row['detail_report'];

// Conteo + muestras de títulos por grupo
echo "id_dr | nombre catálogo                              | total | muestras de títulos (3 más recientes)\n";
echo str_repeat('-', 220) . "\n";

$r = $db->query("
    SELECT id_detailreport, COUNT(*) tot
    FROM tbl_reporte
    WHERE id_detailreport IS NOT NULL
    GROUP BY id_detailreport
    ORDER BY id_detailreport
");
$ordered = [];
while ($row = $r->fetch_assoc()) $ordered[] = $row;

foreach ($ordered as $g) {
    $dr = (int)$g['id_detailreport'];
    $nombre = $cat[$dr] ?? '(NO EXISTE en catálogo)';
    // muestras de los 3 más recientes
    $stmt = $db->prepare("SELECT titulo_reporte FROM tbl_reporte WHERE id_detailreport = ? ORDER BY created_at DESC LIMIT 3");
    $stmt->bind_param('i', $dr);
    $stmt->execute();
    $rr = $stmt->get_result();
    $muestras = [];
    while ($m = $rr->fetch_assoc()) {
        $t = preg_replace('/\s+/', ' ', substr($m['titulo_reporte'], 0, 80));
        $muestras[] = $t;
    }
    printf("%5d | %-45s | %5d | %s\n", $dr, substr($nombre,0,45), $g['tot'], implode(' || ', $muestras));
}

// IDs del catálogo que JAMÁS se han usado
echo "\n--- IDs del catálogo SIN documentos en tbl_reporte ---\n";
$r = $db->query("
    SELECT id_detailreport, detail_report FROM detail_report
    WHERE id_detailreport NOT IN (SELECT DISTINCT id_detailreport FROM tbl_reporte WHERE id_detailreport IS NOT NULL)
    ORDER BY id_detailreport
");
while ($row = $r->fetch_assoc()) {
    echo "  [{$row['id_detailreport']}] {$row['detail_report']}\n";
}

// Próximo ID disponible
$r = $db->query("SELECT MAX(id_detailreport) m FROM detail_report");
$max = (int)$r->fetch_assoc()['m'];
echo "\nMÁXIMO id_detailreport actual en catálogo: $max — el próximo disponible sería: " . ($max + 1) . "\n";

$db->close();
