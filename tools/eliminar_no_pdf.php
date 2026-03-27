<?php
/**
 * Elimina registros de tbl_reporte cuyo enlace apunta a .html o .png
 * Dry-run por defecto — pasar --execute para eliminar
 */

$execute = in_array('--execute', $argv ?? []);

$db = new mysqli();
$isServer = file_exists('/www/ca/ca-certificate_cycloid.crt');
if ($isServer) {
    $db->ssl_set(null, null, '/www/ca/ca-certificate_cycloid.crt', null, null);
} else {
    $db->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
}
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

echo $execute ? "=== MODO EJECUCIÓN ===\n\n" : "=== MODO DRY-RUN (pasar --execute para eliminar) ===\n\n";

// Buscar registros .html y .png (en enlace o report_url)
$query = "SELECT id_reporte, titulo_reporte, enlace, report_url
          FROM tbl_reporte
          WHERE enlace LIKE '%.html'
             OR enlace LIKE '%.png'
             OR report_url LIKE '%.html'
             OR report_url LIKE '%.png'";

$result = $db->query($query);
$total = $result->num_rows;

echo "Registros a eliminar: $total\n\n";

$por_extension = ['html' => 0, 'png' => 0];
$ids_to_delete = [];

while ($row = $result->fetch_assoc()) {
    $archivo = $row['enlace'] ?: $row['report_url'];
    $ext = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));

    if (in_array($ext, ['html', 'png'])) {
        $por_extension[$ext]++;
        $ids_to_delete[] = $row['id_reporte'];

        if (count($ids_to_delete) <= 10) {
            echo "  [{$ext}] ID {$row['id_reporte']}: " . substr($row['titulo_reporte'], 0, 60) . "\n";
            echo "         → " . substr($archivo, -50) . "\n";
        }
    }
}

if (count($ids_to_delete) > 10) {
    echo "  ... y " . (count($ids_to_delete) - 10) . " más\n";
}

echo "\nDesglose:\n";
echo "  .html: {$por_extension['html']}\n";
echo "  .png:  {$por_extension['png']}\n";

if ($execute && !empty($ids_to_delete)) {
    $chunks = array_chunk($ids_to_delete, 100);
    $deleted = 0;
    foreach ($chunks as $chunk) {
        $placeholders = implode(',', $chunk);
        $db->query("DELETE FROM tbl_reporte WHERE id_reporte IN ($placeholders)");
        $deleted += $db->affected_rows;
    }
    echo "\n✓ Eliminados: $deleted registros\n";
} elseif (!$execute && !empty($ids_to_delete)) {
    echo "\nPara eliminar, ejecutar con --execute\n";
} else {
    echo "\nNo hay registros para eliminar.\n";
}

$db->close();
echo "Completado.\n";
