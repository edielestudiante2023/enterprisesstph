<?php
/**
 * Limpia títulos inconsistentes en tbl_reporte
 * Aplica a TODOS los registros
 */

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

function limpiarTitulo($titulo) {
    // 1. Quitar _Per User Settings[]
    $titulo = str_replace('_Per User Settings[]', '', $titulo);

    // 2. Quitar Audit Log - al inicio
    if (strpos($titulo, 'Audit Log - ') === 0) {
        $titulo = substr($titulo, strlen('Audit Log - '));
    }

    // 3. ___ y todo lo que siga → eliminar (fecha duplicada de AppSheets)
    $titulo = preg_replace('/___.*$/', '', $titulo);

    // 4. __ → ' - ' (separador AppSheets)
    $titulo = str_replace('__', ' - ', $titulo);

    // 5. // → -
    $titulo = str_replace('//', '-', $titulo);

    // 6. _ como separador de palabras (NO en fechas tipo dd_mm_yyyy)
    // Reemplazar _ que NO esté entre dígitos (preservar 30_07_2024)
    $titulo = preg_replace('/(?<!\d)_(?!\d)/', ' ', $titulo);

    // 7. Múltiples espacios → un espacio
    $titulo = preg_replace('/\s{2,}/', ' ', $titulo);

    // 8. Trim
    $titulo = trim($titulo);

    return $titulo;
}

$result = $db->query("SELECT id_reporte, titulo_reporte FROM tbl_reporte");
echo "Total registros: " . $result->num_rows . "\n\n";

$updated = 0;
$unchanged = 0;
$examples = [];

$stmt = $db->prepare("UPDATE tbl_reporte SET titulo_reporte=? WHERE id_reporte=?");

while ($row = $result->fetch_assoc()) {
    $original = $row['titulo_reporte'];
    $limpio = limpiarTitulo($original);

    if ($limpio !== $original) {
        $stmt->bind_param('si', $limpio, $row['id_reporte']);
        $stmt->execute();
        $updated++;

        if (count($examples) < 10) {
            $examples[] = [
                'id' => $row['id_reporte'],
                'antes' => substr($original, 0, 70),
                'despues' => substr($limpio, 0, 70),
            ];
        }
    } else {
        $unchanged++;
    }
}
$stmt->close();

echo "=== RESULTADO ===\n";
echo "Títulos actualizados: $updated\n";
echo "Sin cambios: $unchanged\n";

if (!empty($examples)) {
    echo "\nEjemplos:\n";
    foreach ($examples as $e) {
        echo "  ID {$e['id']}:\n";
        echo "    ANTES:   {$e['antes']}\n";
        echo "    DESPUÉS: {$e['despues']}\n";
    }
}

$db->close();
echo "\nCompletado.\n";
