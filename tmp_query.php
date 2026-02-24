<?php
$pdo = new PDO(
    "mysql:host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com;port=25060;dbname=propiedad_horizontal;charset=utf8mb4",
    'cycloid_userdb', getenv('DB_PROD_PASS'),
    [PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false, PDO::MYSQL_ATTR_SSL_CA => '', PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

echo "=== PENDIENTES CON id_acta_visita = 2 ===\n";
$sql = "SELECT * FROM tbl_pendientes WHERE id_acta_visita = 2";
$rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
echo "Total: " . count($rows) . "\n";
foreach ($rows as $r) {
    echo "  #{$r['id_pendientes']} | {$r['tarea_actividad']} | Resp: {$r['responsable']} | Estado: {$r['estado']}\n";
}

echo "\n=== PENDIENTES CON id_acta_visita NOT NULL ===\n";
$sql = "SELECT p.id_pendientes, p.tarea_actividad, p.responsable, p.estado, p.id_acta_visita, c.nombre_cliente
        FROM tbl_pendientes p
        JOIN tbl_clientes c ON c.id_cliente = p.id_cliente
        WHERE p.id_acta_visita IS NOT NULL";
foreach ($pdo->query($sql) as $r) {
    echo "  #{$r['id_pendientes']} | Acta:{$r['id_acta_visita']} | {$r['tarea_actividad']} | {$r['nombre_cliente']}\n";
}
