<?php
/**
 * Reset registros de prueba de certificados de servicio (cliente 63).
 * - Borra certificados id 3 y 4
 * - Resetea vencimientos 157 y 158 a 'sin ejecutar'
 *
 * Uso LOCAL:   php app/SQL/reset_certificados_prueba.php
 * Uso PROD:    DB_PROD_PASS=xxx php app/SQL/reset_certificados_prueba.php production
 */

$env = ($argv[1] ?? '') === 'production' ? 'production' : 'local';

if ($env === 'production') {
    $host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port = 25060;
    $user = 'cycloid_userdb';
    $pass = getenv('DB_PROD_PASS');
    $db   = 'propiedad_horizontal';
    $ssl  = true;
} else {
    $host = '127.0.0.1';
    $port = 3306;
    $user = 'root';
    $pass = '';
    $db   = 'propiedad_horizontal';
    $ssl  = false;
}

echo "=== Reset certificados prueba (cliente 63) ===\n";
echo "Entorno: {$env}\n\n";

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
    $opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
    if ($ssl) {
        $opts[PDO::MYSQL_ATTR_SSL_CA] = true;
        $opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }
    $pdo = new PDO($dsn, $user, $pass, $opts);
    echo "Conectado OK\n\n";

    // 1. Borrar certificados id 3 y 4
    $stmt = $pdo->prepare("DELETE FROM tbl_certificado_servicio WHERE id IN (3, 4)");
    $stmt->execute();
    echo "1) Certificados eliminados: " . $stmt->rowCount() . " filas\n";

    // 2. Resetear vencimientos 157 y 158 a 'sin ejecutar'
    $stmt = $pdo->prepare("UPDATE tbl_vencimientos_mantenimientos SET estado_actividad = 'sin ejecutar', fecha_realizacion = NULL WHERE id_vencimientos_mmttos IN (157, 158)");
    $stmt->execute();
    echo "2) Vencimientos reseteados: " . $stmt->rowCount() . " filas\n";

    // 3. Verificar estado final
    echo "\n--- Verificacion ---\n";
    $stmt = $pdo->query("SELECT COUNT(*) FROM tbl_certificado_servicio WHERE id IN (3, 4)");
    echo "Certificados 3,4 restantes: " . $stmt->fetchColumn() . "\n";

    $stmt = $pdo->query("SELECT id_vencimientos_mmttos, id_mantenimiento, estado_actividad, fecha_realizacion FROM tbl_vencimientos_mantenimientos WHERE id_vencimientos_mmttos IN (157, 158)");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "Vencimiento {$row['id_vencimientos_mmttos']}: estado={$row['estado_actividad']}, realizacion={$row['fecha_realizacion']}\n";
    }

    echo "\nOK\n";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
