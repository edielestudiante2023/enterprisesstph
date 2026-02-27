<?php
$db = new mysqli('localhost', 'root', '', 'propiedad_horizontal');
if ($db->connect_error) { die("Error: " . $db->connect_error); }

$inserts = [
    [5, 43, 17, '2026-02-15', 'sin ejecutar', NULL, 'Bomba principal requiere revisión urgente'],
    [13, 43, 17, '2026-03-10', 'sin ejecutar', NULL, 'Mantenimiento semestral programado'],
    [15, 43, 17, '2026-01-31', 'sin ejecutar', NULL, 'Terraza torre 2 presenta filtración'],
];

foreach ($inserts as $i) {
    $stmt = $db->prepare('INSERT INTO tbl_vencimientos_mantenimientos (id_mantenimiento, id_cliente, id_consultor, fecha_vencimiento, estado_actividad, fecha_realizacion, observaciones) VALUES (?,?,?,?,?,?,?)');
    $stmt->bind_param('iiissss', $i[0], $i[1], $i[2], $i[3], $i[4], $i[5], $i[6]);
    $stmt->execute();
    echo "Inserted id=" . $db->insert_id . ": " . $i[3] . " - mantenimiento " . $i[0] . "\n";
}

echo "\n=== Total sin ejecutar para cliente 43 ===\n";
$r = $db->query("SELECT v.*, m.detalle_mantenimiento FROM tbl_vencimientos_mantenimientos v LEFT JOIN tbl_mantenimientos m ON m.id_mantenimiento = v.id_mantenimiento WHERE v.id_cliente = 43 AND v.estado_actividad = 'sin ejecutar' ORDER BY v.fecha_vencimiento ASC");
echo $r->num_rows . " registros\n";
while ($row = $r->fetch_assoc()) {
    echo $row['fecha_vencimiento'] . ' | ' . $row['detalle_mantenimiento'] . ' | ' . ($row['observaciones'] ?: '-') . "\n";
}
$db->close();
