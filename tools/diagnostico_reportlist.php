<?php
/**
 * Diagnóstico de inconsistencias en tbl_reporte
 * Solo lectura — no modifica nada
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

echo "=== DIAGNÓSTICO tbl_reporte ===\n\n";

// --- 1. Fechas incorrectas ---
echo "--- 1. FECHAS INCORRECTAS (created_at >= 2026-03-26) ---\n";
$r = $db->query("SELECT COUNT(*) as total FROM tbl_reporte WHERE DATE(created_at) >= '2026-03-26'");
$total_fechas = $r->fetch_assoc()['total'];
echo "Total registros con fecha >= 2026-03-26: $total_fechas\n";

$r = $db->query("SELECT id_reporte, titulo_reporte, created_at FROM tbl_reporte WHERE DATE(created_at) >= '2026-03-26' LIMIT 10");
echo "\nEjemplos:\n";
while ($row = $r->fetch_assoc()) {
    echo "  ID {$row['id_reporte']}: [{$row['created_at']}] " . substr($row['titulo_reporte'], 0, 80) . "\n";
}

// Contar cuántos tienen fecha extraíble del título
$meses = [
    'ENERO'=>1,'FEBRERO'=>2,'MARZO'=>3,'ABRIL'=>4,'MAYO'=>5,'JUNIO'=>6,
    'JULIO'=>7,'AGOSTO'=>8,'SEPTIEMBRE'=>9,'OCTUBRE'=>10,'NOVIEMBRE'=>11,'DICIEMBRE'=>12
];
$mesesAbr = [
    'ENE'=>1,'FEB'=>2,'MAR'=>3,'ABR'=>4,'MAY'=>5,'JUN'=>6,
    'JUL'=>7,'AGO'=>8,'SEP'=>9,'OCT'=>10,'NOV'=>11,'DIC'=>12
];
$mesesEn = ['jan'=>1,'feb'=>2,'mar'=>3,'apr'=>4,'may'=>5,'jun'=>6,'jul'=>7,'aug'=>8,'sep'=>9,'oct'=>10,'nov'=>11,'dec'=>12];

$all = $db->query("SELECT id_reporte, titulo_reporte FROM tbl_reporte WHERE DATE(created_at) >= '2026-03-26'");
$con_fecha = 0;
$sin_fecha = 0;
$sin_fecha_ejemplos = [];

while ($row = $all->fetch_assoc()) {
    $t = $row['titulo_reporte'];
    $found = false;

    // Todos los formatos de fecha
    if (preg_match('/___\s*(\d{1,2})\/(\d{1,2})\/(\d{4})/', $t, $m)) $found = true;
    elseif (preg_match('/(\d{1,2})\/(\d{1,2})\/(\d{4})/', $t, $m)) $found = true;
    elseif (preg_match('/(\d{1,2})_(\d{2})_(\d{4})/', $t, $m)) $found = true;
    elseif (preg_match('/(ENERO|FEBRERO|MARZO|ABRIL|MAYO|JUNIO|JULIO|AGOSTO|SEPTIEMBRE|OCTUBRE|NOVIEMBRE|DICIEMBRE)[- ](\d{4})/i', $t)) $found = true;
    elseif (preg_match('/(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)-(\d{4})/i', $t)) $found = true;
    elseif (preg_match('/\/\/\s*(ENE|FEB|MAR|ABR|MAY|JUN|JUL|AGO|SEP|OCT|NOV|DIC)\s+(\d{4})/i', $t)) $found = true;
    elseif (preg_match('/(\d{4})-(\d{2})-(\d{2})/', $t)) $found = true;
    elseif (preg_match('/ (202[0-6])(?:\.pdf)?$/i', $t)) $found = true;

    if ($found) {
        $con_fecha++;
    } else {
        $sin_fecha++;
        if (count($sin_fecha_ejemplos) < 10) {
            $sin_fecha_ejemplos[] = "ID {$row['id_reporte']}: " . substr($t, 0, 80);
        }
    }
}

echo "\nCon fecha extraíble: $con_fecha\n";
echo "Sin fecha extraíble: $sin_fecha\n";
if (!empty($sin_fecha_ejemplos)) {
    echo "Ejemplos sin fecha:\n";
    foreach ($sin_fecha_ejemplos as $e) echo "  $e\n";
}

// --- 2. Archivos no-PDF ---
echo "\n--- 2. ARCHIVOS NO-PDF ---\n";

$extensiones = ['.html', '.png', '.jpg', '.jpeg', '.docx'];
foreach ($extensiones as $ext) {
    $r = $db->query("SELECT COUNT(*) as total FROM tbl_reporte WHERE enlace LIKE '%$ext' OR report_url LIKE '%$ext'");
    $total = $r->fetch_assoc()['total'];
    echo "  $ext: $total registros\n";
}

echo "\nEjemplos .html:\n";
$r = $db->query("SELECT id_reporte, titulo_reporte, enlace FROM tbl_reporte WHERE enlace LIKE '%.html' OR report_url LIKE '%.html' LIMIT 10");
while ($row = $r->fetch_assoc()) {
    echo "  ID {$row['id_reporte']}: " . substr($row['titulo_reporte'], 0, 60) . " → " . substr($row['enlace'], -40) . "\n";
}

echo "\nEjemplos .png:\n";
$r = $db->query("SELECT id_reporte, titulo_reporte, enlace FROM tbl_reporte WHERE enlace LIKE '%.png' OR report_url LIKE '%.png' LIMIT 10");
while ($row = $r->fetch_assoc()) {
    echo "  ID {$row['id_reporte']}: " . substr($row['titulo_reporte'], 0, 60) . " → " . substr($row['enlace'], -40) . "\n";
}

// --- 3. Tipos de reporte inválidos ---
echo "\n--- 3. TIPOS DE REPORTE ---\n";
$tipos_validos = [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,19,20,21];
$lista = implode(',', $tipos_validos);
$r = $db->query("SELECT id_report_type, COUNT(*) as total FROM tbl_reporte WHERE id_report_type NOT IN ($lista) GROUP BY id_report_type");
if ($r->num_rows > 0) {
    echo "Tipos INVÁLIDOS encontrados:\n";
    while ($row = $r->fetch_assoc()) {
        echo "  Tipo {$row['id_report_type']}: {$row['total']} registros\n";
    }
} else {
    echo "Todos los tipos son válidos ✓\n";
}

// Distribución por tipo
echo "\nDistribución por tipo:\n";
$r = $db->query("SELECT r.id_report_type, COALESCE(rt.report_type, 'SIN TIPO') as nombre, COUNT(*) as total FROM tbl_reporte r LEFT JOIN report_type_table rt ON r.id_report_type = rt.id_report_type GROUP BY r.id_report_type ORDER BY r.id_report_type");
while ($row = $r->fetch_assoc()) {
    echo "  [{$row['id_report_type']}] {$row['nombre']}: {$row['total']}\n";
}

// --- 4. Títulos con patrones sucios ---
echo "\n--- 4. TÍTULOS CON PATRONES SUCIOS ---\n";
$patrones = [
    '___' => "LIKE '%\\_\\_\\_%'",
    '__' => "LIKE '%\\_\\_%' AND titulo_reporte NOT LIKE '%\\_\\_\\_%'",
    '//' => "LIKE '%//%'",
    '_Per User Settings[]' => "LIKE '%_Per User Settings[]%'",
    'Audit Log - ' => "LIKE 'Audit Log - %'",
];
foreach ($patrones as $nombre => $where) {
    $r = $db->query("SELECT COUNT(*) as total FROM tbl_reporte WHERE titulo_reporte $where");
    $total = $r->fetch_assoc()['total'];
    echo "  '$nombre': $total registros\n";
}

// --- Resumen ---
echo "\n=== RESUMEN ===\n";
$r = $db->query("SELECT COUNT(*) as total FROM tbl_reporte");
echo "Total registros en tbl_reporte: " . $r->fetch_assoc()['total'] . "\n";
echo "Registros con fecha incorrecta: $total_fechas (de los cuales $con_fecha tienen fecha extraíble)\n";

$db->close();
echo "\nDiagnóstico completado.\n";
