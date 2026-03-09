<?php
$pdo = new PDO(
    'mysql:host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com;port=25060;dbname=propiedad_horizontal;charset=utf8mb4',
    'cycloid_userdb',
    'AVNS_iDypWizlpMRwHIORJGG',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::MYSQL_ATTR_SSL_CA => true, PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false]
);

// Primero ver columnas
$cols = $pdo->query("SHOW COLUMNS FROM tbl_pta_cliente")->fetchAll(PDO::FETCH_ASSOC);
echo "Columnas:\n";
foreach ($cols as $c) echo "  {$c['Field']} ({$c['Type']})\n";
echo "\n";

// Agrupar por programa base (sin periodo)
$programas = [
    'limpieza' => '%indicadores del Programa de limpieza%',
    'residuos' => '%indicadores del Programa de manejo integral%',
    'plagas'   => '%indicadores del Programa de control integrado%',
    'agua'     => '%indicadores del Programa de abastecimiento%',
];

foreach ($programas as $nombre => $like) {
    echo "=== $nombre ===\n";
    $stmt = $pdo->prepare("SELECT id_cliente, COUNT(*) as cnt FROM tbl_pta_cliente WHERE actividad_plandetrabajo LIKE ? GROUP BY id_cliente ORDER BY cnt DESC LIMIT 10");
    $stmt->execute([$like]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        echo "  Cliente {$r['id_cliente']}: {$r['cnt']} registros\n";
    }
    // Total clientes
    $stmt2 = $pdo->prepare("SELECT COUNT(DISTINCT id_cliente) as clientes, COUNT(*) as total FROM tbl_pta_cliente WHERE actividad_plandetrabajo LIKE ?");
    $stmt2->execute([$like]);
    $t = $stmt2->fetch(PDO::FETCH_ASSOC);
    echo "  TOTAL: {$t['total']} registros en {$t['clientes']} clientes\n\n";
}
