<?php
/**
 * Migración: tabla tbl_inspeccion_frecuencia_cliente
 * Guarda la frecuencia de cada tipo de inspección por cliente (configurada por el consultor).
 *
 * USO:
 *   Local:        php app/SQL/create_inspeccion_frecuencia_cliente.php
 *   Producción:   php app/SQL/create_inspeccion_frecuencia_cliente.php production
 *                 (toma DB_PROD_PASS del entorno o lee del .env)
 *
 * Idempotente.
 */

require __DIR__ . '/../../vendor/autoload.php';

$entorno = $argv[1] ?? 'local';

if ($entorno === 'production') {
    $host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port = 25060;
    $database = 'propiedad_horizontal';
    $user = 'cycloid_userdb';
    $pass = getenv('DB_PROD_PASS');
    if (!$pass) {
        echo "ERROR: define DB_PROD_PASS en el entorno antes de ejecutar.\n";
        exit(1);
    }
    $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
    $opts = [
        PDO::MYSQL_ATTR_SSL_CA => null,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ];
} else {
    $host = '127.0.0.1';
    $port = 3306;
    $database = 'propiedad_horizontal';
    $user = 'root';
    $pass = '';
    $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
    $opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
}

try {
    $pdo = new PDO($dsn, $user, $pass, $opts);
    echo "Conectado a {$entorno} ({$database}@{$host})\n";

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = :db AND table_name = 'tbl_inspeccion_frecuencia_cliente'");
    $stmt->execute([':db' => $database]);
    if ((int) $stmt->fetchColumn() > 0) {
        echo "OK: tabla tbl_inspeccion_frecuencia_cliente ya existe.\n";
        exit(0);
    }

    $sql = "
        CREATE TABLE tbl_inspeccion_frecuencia_cliente (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            id_cliente INT UNSIGNED NOT NULL,
            slug_inspeccion VARCHAR(80) NOT NULL,
            frecuencia ENUM('mensual','bimensual','trimestral','semestral','anual','puntual') NOT NULL,
            created_at DATETIME NULL,
            updated_at DATETIME NULL,
            UNIQUE KEY uniq_cliente_slug (id_cliente, slug_inspeccion),
            INDEX idx_cliente (id_cliente)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    $pdo->exec($sql);
    echo "OK: tabla tbl_inspeccion_frecuencia_cliente creada.\n";

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
