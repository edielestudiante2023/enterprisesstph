<?php
/**
 * Migración: crea las 3 tablas de planes de contingencia
 * Uso: php create_planes_contingencia.php [local|production]
 *      DB_PROD_PASS=xxx php create_planes_contingencia.php production
 */

$env = $argv[1] ?? 'local';

if ($env === 'production') {
    $host   = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port   = 25060;
    $dbname = 'propiedad_horizontal';
    $user   = 'cycloid_userdb';
    $pass   = getenv('DB_PROD_PASS');
    $ssl    = true;
} else {
    $host   = '127.0.0.1';
    $port   = 3306;
    $dbname = 'propiedad_horizontal';
    $user   = 'root';
    $pass   = '';
    $ssl    = false;
}

$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
if ($ssl) {
    $options[PDO::MYSQL_ATTR_SSL_CA]                 = true;
    $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
}

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "[{$env}] Conectado a {$dbname}\n";
} catch (PDOException $e) {
    echo "ERROR conexión: " . $e->getMessage() . "\n";
    exit(1);
}

$tablas = [
    'tbl_plan_contingencia_plagas' => "
        CREATE TABLE tbl_plan_contingencia_plagas (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            id_cliente INT UNSIGNED NOT NULL,
            id_consultor INT UNSIGNED NOT NULL,
            fecha_programa DATE NOT NULL,
            nombre_responsable VARCHAR(200) NULL,
            empresa_fumigadora TEXT NULL COMMENT 'Nombre y contacto de la empresa de control de plagas',
            estado ENUM('borrador','completo') NOT NULL DEFAULT 'borrador',
            ruta_pdf VARCHAR(500) NULL,
            created_at DATETIME NULL,
            updated_at DATETIME NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",
    'tbl_plan_contingencia_agua' => "
        CREATE TABLE tbl_plan_contingencia_agua (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            id_cliente INT UNSIGNED NOT NULL,
            id_consultor INT UNSIGNED NOT NULL,
            fecha_programa DATE NOT NULL,
            nombre_responsable VARCHAR(200) NULL,
            empresa_carrotanque TEXT NULL COMMENT 'Proveedor de agua alternativa (carrotanque)',
            capacidad_reserva VARCHAR(100) NULL COMMENT 'Capacidad de reserva en litros',
            estado ENUM('borrador','completo') NOT NULL DEFAULT 'borrador',
            ruta_pdf VARCHAR(500) NULL,
            created_at DATETIME NULL,
            updated_at DATETIME NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",
    'tbl_plan_contingencia_basura' => "
        CREATE TABLE tbl_plan_contingencia_basura (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            id_cliente INT UNSIGNED NOT NULL,
            id_consultor INT UNSIGNED NOT NULL,
            fecha_programa DATE NOT NULL,
            nombre_responsable VARCHAR(200) NULL,
            empresa_aseo VARCHAR(200) NULL COMMENT 'Nombre del prestador del servicio de aseo',
            horario_recoleccion_actual VARCHAR(200) NULL COMMENT 'Días y horario normal de recolección',
            estado ENUM('borrador','completo') NOT NULL DEFAULT 'borrador',
            ruta_pdf VARCHAR(500) NULL,
            created_at DATETIME NULL,
            updated_at DATETIME NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",
];

foreach ($tablas as $tabla => $sql) {
    $existe = $pdo->query("SHOW TABLES LIKE '{$tabla}'")->fetchAll();
    if (!empty($existe)) {
        echo "[{$env}] La tabla {$tabla} ya existe. Saltando.\n";
        continue;
    }
    $pdo->exec($sql);
    echo "[{$env}] OK — tabla {$tabla} creada.\n";
}

echo "[{$env}] Migración completada.\n";
