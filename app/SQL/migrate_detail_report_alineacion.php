<?php
/**
 * Migración: alineación catálogo detail_report con módulo Inspecciones.
 *
 * Inserta los 11 grupos faltantes (IDs 52–62) para cubrir inspecciones
 * que generaban PDF pero no tenían grupo propio en el catálogo, y por eso
 * terminaban clasificadas en grupos ajenos en /reportList.
 *
 * USO:
 *   DB_PROD_PASS=xxx php app/SQL/migrate_detail_report_alineacion.php [production|local]
 *
 * Idempotente: chequea cada id antes de insertar.
 * NO modifica documentos existentes en tbl_reporte (eso es fase 2).
 */

$entorno = $argv[1] ?? 'local';

if ($entorno === 'production') {
    $host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $user = 'cycloid_userdb';
    $pass = getenv('DB_PROD_PASS');
    $db   = 'propiedad_horizontal';
    $port = 25060;
    $ssl  = true;
} else {
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $db   = 'propiedad_horizontal';
    $port = 3306;
    $ssl  = false;
}

if (!$pass && $entorno === 'production') {
    die("ERROR: falta DB_PROD_PASS\n");
}

$mysqli = new mysqli();
if ($ssl) {
    $mysqli->ssl_set(null, null, '/www/ca/ca-certificate_cycloid.crt', null, null);
    $mysqli->real_connect($host, $user, $pass, $db, $port, null, MYSQLI_CLIENT_SSL);
} else {
    $mysqli->real_connect($host, $user, $pass, $db, $port);
}

if ($mysqli->connect_error) {
    die("ERROR conexión: " . $mysqli->connect_error . "\n");
}
$mysqli->set_charset('utf8mb4');

echo "=== Migración detail_report — alineación con módulo Inspecciones ===\n";
echo "Entorno: $entorno\n\n";

/**
 * Grupos nuevos a insertar.
 * Si el id ya existe en el catálogo (con cualquier nombre), se omite el insert
 * para preservar lo que ya hay.
 */
$nuevos = [
    52 => 'Acta de Visita',
    53 => 'Inspección Señalización',
    54 => 'Inspección Comunicaciones',
    55 => 'Inspección Productos Químicos',
    56 => 'Programa Limpieza y Desinfección',
    57 => 'Programa Control Integrado de Plagas',
    58 => 'Programa Manejo Integral de Residuos',
    59 => 'Plan Contingencia Limpieza y Desinfección',
    60 => 'Procedimiento Emergencia por Área',
    61 => 'Preparación de Simulacro',
    62 => 'Auditoría Zona Residuos',
];

$insertados = 0;
$omitidos = 0;

foreach ($nuevos as $id => $nombre) {
    $stmt = $mysqli->prepare("SELECT id_detailreport, detail_report FROM detail_report WHERE id_detailreport = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        echo "  [$id] YA EXISTE → '{$row['detail_report']}' — se omite.\n";
        $omitidos++;
        continue;
    }

    $stmtIns = $mysqli->prepare("INSERT INTO detail_report (id_detailreport, detail_report) VALUES (?, ?)");
    $stmtIns->bind_param('is', $id, $nombre);
    if ($stmtIns->execute()) {
        echo "  [$id] INSERTADO → '$nombre'\n";
        $insertados++;
    } else {
        echo "  [$id] ERROR insertando '$nombre': " . $stmtIns->error . "\n";
    }
}

echo "\n--- Resumen ---\n";
echo "Insertados: $insertados\n";
echo "Omitidos (ya existían): $omitidos\n";

echo "\n--- Verificación final ---\n";
$r = $mysqli->query("SELECT id_detailreport, detail_report FROM detail_report WHERE id_detailreport BETWEEN 52 AND 62 ORDER BY id_detailreport");
while ($row = $r->fetch_assoc()) {
    echo "  [{$row['id_detailreport']}] {$row['detail_report']}\n";
}

$mysqli->close();
echo "\nMigración completada.\n";
