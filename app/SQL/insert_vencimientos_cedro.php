<?php
$db = new mysqli('localhost', 'root', '', 'propiedad_horizontal');
if ($db->connect_error) { die("Error: " . $db->connect_error); }

// Mismos datos de Torres de las Americas (id_cliente=43) pero para Cedro Suba III (id_cliente=54)
$inserts = [
    [2, 54, 17, '2025-04-30', 'sin ejecutar', NULL, ''],
    [4, 54, 17, '2025-09-30', 'sin ejecutar', NULL, ''],
    [1, 54, 17, '2025-12-31', 'sin ejecutar', NULL, ''],
    [15, 54, 17, '2026-01-31', 'sin ejecutar', NULL, 'Terraza torre 2 presenta filtración'],
    [5, 54, 17, '2026-02-15', 'sin ejecutar', NULL, 'Bomba principal requiere revisión urgente'],
    [13, 54, 17, '2026-03-10', 'sin ejecutar', NULL, 'Mantenimiento semestral programado'],
];

foreach ($inserts as $i) {
    $stmt = $db->prepare('INSERT INTO tbl_vencimientos_mantenimientos (id_mantenimiento, id_cliente, id_consultor, fecha_vencimiento, estado_actividad, fecha_realizacion, observaciones) VALUES (?,?,?,?,?,?,?)');
    $stmt->bind_param('iiissss', $i[0], $i[1], $i[2], $i[3], $i[4], $i[5], $i[6]);
    $stmt->execute();
    echo "Inserted id=" . $db->insert_id . ": " . $i[3] . "\n";
}

echo "\nTotal insertados: " . count($inserts) . " para Cedro Suba III (id_cliente=54)\n";
$db->close();
