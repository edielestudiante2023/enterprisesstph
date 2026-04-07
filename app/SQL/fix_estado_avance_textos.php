<?php
/**
 * Fix: Corregir textos de estado_avance en tbl_informe_avances
 *
 * Uso:
 *   LOCAL:       php app/SQL/fix_estado_avance_textos.php
 *   PRODUCCIÓN:  DB_PROD_PASS=xxx php app/SQL/fix_estado_avance_textos.php production
 */

$env = $argv[1] ?? 'local';

if ($env === 'production') {
    $pass = getenv('DB_PROD_PASS');
    if (!$pass) {
        echo "ERROR: Variable DB_PROD_PASS no definida.\n";
        exit(1);
    }
    $host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port = 25060;
    $db   = 'propiedad_horizontal';
    $user = 'cycloid_userdb';
    $ssl  = true;
    echo "=== PRODUCCIÓN ===\n";
} else {
    $host = '127.0.0.1';
    $port = 3306;
    $db   = 'propiedad_horizontal';
    $user = 'root';
    $pass = '';
    $ssl  = false;
    echo "=== LOCAL ===\n";
}

$dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
$opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
if ($ssl) {
    $opts[PDO::MYSQL_ATTR_SSL_CA] = true;
    $opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
}

try {
    $pdo = new PDO($dsn, $user, $pass, $opts);
    echo "Conectado a {$db}@{$host}:{$port}\n\n";
} catch (PDOException $e) {
    echo "ERROR conexión: " . $e->getMessage() . "\n";
    exit(1);
}

$updates = [
    ['old' => 'AVANCE SIGNIFICATIVO',                  'new' => 'Avance significativo'],
    ['old' => 'AVANCE MODERADO',                       'new' => 'Avance moderado'],
    ['old' => 'ESTABLE',                               'new' => 'Estable'],
    ['old' => 'REINICIO DE CICLO PHVA - BAJA PUNTAJE', 'new' => 'Requiere atención - puntaje disminuyó'],
];

// Primero: detectar los que tenían diferencia > 0 pero < 1 y quedaron con el texto viejo de "REINICIO"
// Esos deben ser "Avance leve" en vez de "Requiere atención"
$stmt = $pdo->prepare(
    "SELECT id, diferencia_neta, estado_avance FROM tbl_informe_avances WHERE estado_avance = ?"
);
$stmt->execute(['REINICIO DE CICLO PHVA - BAJA PUNTAJE']);
$reinicios = $stmt->fetchAll(PDO::FETCH_ASSOC);

$fixLeve = 0;
$fixReq = 0;
foreach ($reinicios as $row) {
    $dif = floatval($row['diferencia_neta']);
    if ($dif > 0) {
        // Era el bug: diferencia positiva pero < 1
        $pdo->prepare("UPDATE tbl_informe_avances SET estado_avance = ? WHERE id = ?")
            ->execute(['Avance leve', $row['id']]);
        $fixLeve++;
        echo "  ID {$row['id']}: diferencia={$dif} → 'Avance leve' (era bug)\n";
    } else {
        // Realmente bajó el puntaje
        $pdo->prepare("UPDATE tbl_informe_avances SET estado_avance = ? WHERE id = ?")
            ->execute(['Requiere atención - puntaje disminuyó', $row['id']]);
        $fixReq++;
        echo "  ID {$row['id']}: diferencia={$dif} → 'Requiere atención - puntaje disminuyó'\n";
    }
}

// Ahora los demás textos (MAYÚSCULAS → formato legible)
foreach ($updates as $u) {
    // Saltar REINICIO ya procesado arriba
    if ($u['old'] === 'REINICIO DE CICLO PHVA - BAJA PUNTAJE') continue;

    $stmt = $pdo->prepare("UPDATE tbl_informe_avances SET estado_avance = ? WHERE estado_avance = ?");
    $stmt->execute([$u['new'], $u['old']]);
    $count = $stmt->rowCount();
    echo "  '{$u['old']}' → '{$u['new']}': {$count} registros\n";
}

echo "\nResumen REINICIO: {$fixLeve} corregidos a 'Avance leve', {$fixReq} a 'Requiere atención'\n";
echo "Migración completada.\n";
