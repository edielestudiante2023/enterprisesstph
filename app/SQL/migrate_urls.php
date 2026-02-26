<?php
/**
 * Migración: Crear tabla tbl_urls y poblar con datos iniciales
 * Módulo de accesos rápidos para consultores (no genera PDF)
 *
 * Uso: DB_PROD_PASS=xxx php app/SQL/migrate_urls.php [production]
 */

$isProduction = ($argv[1] ?? '') === 'production';

if ($isProduction) {
    $host     = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port     = 25060;
    $dbname   = 'propiedad_horizontal';
    $user     = 'cycloid_userdb';
    $password = getenv('DB_PROD_PASS');
    if (!$password) {
        echo "ERROR: DB_PROD_PASS env var required for production.\n";
        exit(1);
    }
    $ssl = true;
    echo "=== PRODUCCIÓN ===\n";
} else {
    $host     = '127.0.0.1';
    $port     = 3306;
    $dbname   = 'propiedad_horizontal';
    $user     = 'root';
    $password = '';
    $ssl      = false;
    echo "=== LOCAL ===\n";
}

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
if ($ssl) {
    $options[PDO::MYSQL_ATTR_SSL_CA] = true;
    $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
}

try {
    $pdo = new PDO($dsn, $user, $password, $options);
    echo "Conectado a $dbname en $host:$port\n\n";
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage() . "\n";
    exit(1);
}

// 1. Crear tabla
$stmt = $pdo->query("SHOW TABLES LIKE 'tbl_urls'");
if ($stmt->rowCount() > 0) {
    echo "Tabla tbl_urls ya existe.\n";
} else {
    $sql = "CREATE TABLE `tbl_urls` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `tipo` VARCHAR(100) NOT NULL,
        `nombre` VARCHAR(255) NOT NULL,
        `url` VARCHAR(1000) NOT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $pdo->exec($sql);
    echo "Tabla tbl_urls creada OK.\n";
}

// 2. Insertar datos iniciales (solo si tabla está vacía)
$count = $pdo->query("SELECT COUNT(*) FROM tbl_urls")->fetchColumn();
if ($count > 0) {
    echo "Tabla ya tiene $count registros, saltando inserts.\n";
} else {
    $datos = [
        ['AGENDA CONSULTOR', 'CALENDARIO CONSULTOR', 'https://info.cycloidtalent.com/agenda.html'],
        ['BRIGADISTA', 'QR HV BRIGADISTA', 'https://info.cycloidtalent.com/qrbrigadista.html'],
        ['BRIGADISTA', 'ENLACE DIRECTO HV BRIGADISTA', 'https://script.google.com/macros/s/AKfycbzsOvx7UHRpgRSU8zyxBhJFu_r4kk4-PADEu3b2sgaDAz7AuNI36UUHUN2Q_NqHpUbi5w/exec'],
        ['INDUCCION', 'QR INDUCCION SST - PH', 'https://info.cycloidtalent.com/qrInduccionSST.html'],
        ['INDUCCION', 'DIAPOSITIVAS PDF INDUCCION SST', 'https://drive.google.com/file/d/11GFBgXJqFEMDUKbuywoUcgzqZPlN9nGz/view?usp=sharing'],
        ['KPI', 'INDICADORES MACRO PH', 'https://lookerstudio.google.com/u/0/reporting/fc8339ec-5c08-4102-87c5-af75efb942ad/page/YwFxE'],
        ['PROCEDIMIENTOS', 'VISITA 1', 'https://info.cycloidtalent.com/visita1.html'],
        ['SIMULACRO', 'PREPARACION SIMULACRO - COMO USAR', 'https://youtu.be/1EgJMNiOOrA'],
        ['SIMULACRO', 'QR EVALUACION SIMULACRO', 'https://info.cycloidtalent.com/qrEvSimulacro.html'],
        ['SIMULACRO', 'ENLACE DIRECTO EVALUACION SIMULACRO', 'https://script.google.com/macros/s/AKfycbyApwLPCf9CfvLLxn4u2vUxh_xQI1-pzOmjcMZdwQYHZBZXBiR1i2h45spvzxWaEf5lOQ/exec'],
        ['SIMULACRO', 'PDF EVALUACIÓN SIMULACRO EVACUACION', 'https://drive.google.com/file/d/1f6ZB5lU_GBj7CdDrLoi1_wwJqMAMxg3Y/view?usp=sharing'],
    ];

    $stmt = $pdo->prepare("INSERT INTO tbl_urls (tipo, nombre, url) VALUES (?, ?, ?)");
    foreach ($datos as $row) {
        $stmt->execute($row);
    }
    echo "Insertados " . count($datos) . " registros.\n";
}

echo "\n=== Migración URLs completada ===\n";
