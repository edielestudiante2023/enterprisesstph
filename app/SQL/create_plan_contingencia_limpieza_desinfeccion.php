<?php
/**
 * Migración: crea la tabla tbl_plan_contingencia_limpieza_desinfeccion.
 *
 * Estructura idéntica a tbl_plan_contingencia_plagas (mismo patrón de módulo)
 * para que el controlador clonado funcione 1:1.
 *
 * USO:
 *   Local:        php app/SQL/create_plan_contingencia_limpieza_desinfeccion.php
 *   Producción:   DB_PROD_PASS=xxxx php app/SQL/create_plan_contingencia_limpieza_desinfeccion.php production
 *
 * Idempotente: si la tabla ya existe, no la recrea.
 */

require __DIR__ . '/../../vendor/autoload.php';

$entorno = $argv[1] ?? 'local';

if ($entorno === 'production') {
    $host     = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port     = 25060;
    $database = 'propiedad_horizontal';
    $user     = 'cycloid_userdb';
    $pass     = getenv('DB_PROD_PASS');
    if (!$pass) {
        echo "ERROR: define DB_PROD_PASS en el entorno antes de ejecutar.\n";
        exit(1);
    }
    $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
    $opts = [
        PDO::MYSQL_ATTR_SSL_CA               => null,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        PDO::ATTR_ERRMODE                    => PDO::ERRMODE_EXCEPTION,
    ];
} else {
    $host     = '127.0.0.1';
    $port     = 3306;
    $database = 'propiedad_horizontal';
    $user     = 'root';
    $pass     = '';
    $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
    $opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
}

try {
    $pdo = new PDO($dsn, $user, $pass, $opts);
    echo "Conectado a {$entorno} ({$database}@{$host})\n";

    // Idempotencia: verificar si la tabla ya existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = :db AND table_name = 'tbl_plan_contingencia_limpieza_desinfeccion'");
    $stmt->execute([':db' => $database]);
    $exists = (int) $stmt->fetchColumn() > 0;

    if ($exists) {
        echo "OK: tabla tbl_plan_contingencia_limpieza_desinfeccion ya existe. Nada que hacer.\n";
        exit(0);
    }

    $sql = "
        CREATE TABLE tbl_plan_contingencia_limpieza_desinfeccion (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            id_cliente INT UNSIGNED NOT NULL,
            id_consultor INT UNSIGNED NOT NULL,
            fecha_programa DATE NOT NULL,
            nombre_responsable VARCHAR(200) NULL,
            empresa_limpieza TEXT NULL COMMENT 'Nombre y contacto de la empresa prestadora del servicio de aseo',
            estado ENUM('borrador','completo') NOT NULL DEFAULT 'borrador',
            ruta_pdf VARCHAR(500) NULL,
            created_at DATETIME NULL,
            updated_at DATETIME NULL,
            INDEX idx_cliente (id_cliente),
            INDEX idx_fecha (fecha_programa),
            INDEX idx_estado (estado)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";

    $pdo->exec($sql);
    echo "OK: tabla tbl_plan_contingencia_limpieza_desinfeccion creada.\n";

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
