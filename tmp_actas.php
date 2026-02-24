<?php
$pdo = new PDO(
    "mysql:host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com;port=25060;dbname=propiedad_horizontal;charset=utf8mb4",
    'cycloid_userdb', getenv('DB_PROD_PASS'),
    [PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false, PDO::MYSQL_ATTR_SSL_CA => '', PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

echo "=== ACTAS DE VISITA ===\n";
$sql = "SELECT id, id_cliente, fecha_visita, estado FROM tbl_acta_visita ORDER BY id";
$rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
echo "Total: " . count($rows) . "\n";
foreach ($rows as $r) {
    echo "  Acta #{$r['id']} | Cliente: {$r['id_cliente']} | Fecha: {$r['fecha_visita']} | Estado: {$r['estado']}\n";
}

echo "\n=== PENDIENTES CON id_acta_visita NOT NULL ===\n";
$sql = "SELECT id_pendientes, id_acta_visita, tarea_actividad, id_cliente FROM tbl_pendientes WHERE id_acta_visita IS NOT NULL";
$rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
echo "Total: " . count($rows) . "\n";
foreach ($rows as $r) {
    echo "  Pendiente #{$r['id_pendientes']} | Acta: {$r['id_acta_visita']} | Cliente: {$r['id_cliente']} | Tarea: {$r['tarea_actividad']}\n";
}
