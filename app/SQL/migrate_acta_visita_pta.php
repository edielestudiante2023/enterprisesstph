<?php
/**
 * Migración: Crear tabla tbl_acta_visita_pta
 * Vincula actividades del PTA con el acta de visita (checkboxes)
 *
 * Uso: php app/SQL/migrate_acta_visita_pta.php [production]
 */

$isProduction = isset($argv[1]) && $argv[1] === 'production';

if ($isProduction) {
    $host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port = 25060;
    $db   = 'propiedad_horizontal';
    $user = 'cycloid_userdb';
    $pass = getenv('DB_PROD_PASS');
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

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
    $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
    if ($ssl) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = true;
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "Conectado a {$db}\n";

    // Crear tabla
    $sql = "CREATE TABLE IF NOT EXISTS tbl_acta_visita_pta (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_acta_visita INT NOT NULL,
        id_ptacliente INT NOT NULL,
        cerrada TINYINT(1) NOT NULL DEFAULT 0,
        justificacion_no_cierre TEXT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_acta_visita) REFERENCES tbl_acta_visita(id) ON DELETE CASCADE,
        FOREIGN KEY (id_ptacliente) REFERENCES tbl_pta_cliente(id_ptacliente) ON DELETE CASCADE,
        UNIQUE KEY uk_acta_pta (id_acta_visita, id_ptacliente)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $pdo->exec($sql);
    echo "✓ Tabla tbl_acta_visita_pta creada (o ya existía)\n";

    // Verificar
    $stmt = $pdo->query("DESCRIBE tbl_acta_visita_pta");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Columnas: " . implode(', ', $cols) . "\n";

    echo "\n¡Migración completada!\n";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
