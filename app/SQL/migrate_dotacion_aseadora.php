<?php
/**
 * Migración: Crear tabla tbl_dotacion_aseadora
 * Inspección dotación/EPP del personal de aseo — ~25 columnas (8 ENUMs EPP, 2 fotos)
 *
 * Uso LOCAL:    php migrate_dotacion_aseadora.php
 * Uso PROD:     DB_PROD_PASS=xxx php migrate_dotacion_aseadora.php production
 */

$isProduction = ($argv[1] ?? '') === 'production';

if ($isProduction) {
    $password = getenv('DB_PROD_PASS');
    if (!$password) {
        echo "ERROR: Variable DB_PROD_PASS no definida.\n";
        echo "Uso: DB_PROD_PASS=xxx php migrate_dotacion_aseadora.php production\n";
        exit(1);
    }
    $config = [
        'host'     => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port'     => 25060,
        'dbname'   => 'propiedad_horizontal',
        'user'     => 'cycloid_userdb',
        'password' => $password,
        'ssl'      => true,
    ];
    echo "=== PRODUCCIÓN ===\n";
} else {
    $config = [
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'dbname'   => 'propiedad_horizontal',
        'user'     => 'root',
        'password' => '',
        'ssl'      => false,
    ];
    echo "=== LOCAL ===\n";
}

$dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset=utf8mb4";
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
if ($config['ssl']) {
    $options[PDO::MYSQL_ATTR_SSL_CA] = true;
    $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
}

try {
    $pdo = new PDO($dsn, $config['user'], $config['password'], $options);
    echo "Conectado a {$config['host']}:{$config['port']}/{$config['dbname']}\n";
} catch (PDOException $e) {
    echo "ERROR conexión: " . $e->getMessage() . "\n";
    exit(1);
}

$sql = "
CREATE TABLE IF NOT EXISTS tbl_dotacion_aseadora (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_consultor INT NOT NULL,
    fecha_inspeccion DATE NOT NULL,

    -- Datos del contratista/aseadora
    contratista VARCHAR(255) NULL,
    servicio VARCHAR(255) NULL,
    nombre_cargo VARCHAR(255) NULL,
    actividades_frecuentes TEXT NULL,

    -- Fotos
    foto_cuerpo_completo VARCHAR(255) NULL,
    foto_cuarto_almacenamiento VARCHAR(255) NULL,

    -- Items EPP (8 items x ENUM)
    estado_tapabocas ENUM('bueno','regular','deficiente','no_tiene','no_aplica') NULL,
    estado_guantes_nitrilo ENUM('bueno','regular','deficiente','no_tiene','no_aplica') NULL,
    estado_guantes_caucho ENUM('bueno','regular','deficiente','no_tiene','no_aplica') NULL,
    estado_gafas ENUM('bueno','regular','deficiente','no_tiene','no_aplica') NULL,
    estado_uniforme ENUM('bueno','regular','deficiente','no_tiene','no_aplica') NULL,
    estado_sombrero ENUM('bueno','regular','deficiente','no_tiene','no_aplica') NULL,
    estado_zapato ENUM('bueno','regular','deficiente','no_tiene','no_aplica') NULL,
    estado_botas_caucho ENUM('bueno','regular','deficiente','no_tiene','no_aplica') NULL,

    -- Concepto
    concepto_final TEXT NULL,

    -- General
    observaciones TEXT NULL,
    ruta_pdf VARCHAR(255) NULL,
    estado ENUM('borrador','completo') NOT NULL DEFAULT 'borrador',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_dot_ase_cliente FOREIGN KEY (id_cliente)
        REFERENCES tbl_clientes(id_cliente) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_dot_ase_consultor FOREIGN KEY (id_consultor)
        REFERENCES tbl_consultor(id_consultor) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_dot_ase_cliente (id_cliente),
    INDEX idx_dot_ase_consultor (id_consultor),
    INDEX idx_dot_ase_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

$pdo->exec($sql);
echo "OK — Tabla tbl_dotacion_aseadora creada (o ya existía).\n";

$cols = $pdo->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$config['dbname']}' AND TABLE_NAME = 'tbl_dotacion_aseadora'")->fetchColumn();
echo "Total columnas en tbl_dotacion_aseadora: {$cols}\n";
echo "\n¡Migración completada!\n";
