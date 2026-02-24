<?php
/**
 * Migración: Crear tabla tbl_preparacion_simulacro
 * Preparación y guión del simulacro de evacuación — ~35 columnas (4 ENUMs, 9 TIME, 3 EnumList TEXT, 2 fotos)
 *
 * Uso LOCAL:    php migrate_preparacion_simulacro.php
 * Uso PROD:     DB_PROD_PASS=xxx php migrate_preparacion_simulacro.php production
 */

$isProduction = ($argv[1] ?? '') === 'production';

if ($isProduction) {
    $password = getenv('DB_PROD_PASS');
    if (!$password) {
        echo "ERROR: Variable DB_PROD_PASS no definida.\n";
        echo "Uso: DB_PROD_PASS=xxx php migrate_preparacion_simulacro.php production\n";
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
CREATE TABLE IF NOT EXISTS tbl_preparacion_simulacro (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_consultor INT NOT NULL,
    fecha_simulacro DATE NOT NULL,

    -- Ubicacion
    ubicacion VARCHAR(100) NULL,
    direccion VARCHAR(255) NULL,

    -- Configuracion del simulacro
    evento_simulado ENUM('sismo') NULL,
    alcance_simulacro ENUM('total','parcial') NULL,
    tipo_evacuacion ENUM('horizontal','vertical','mixta') NULL,
    personal_no_evacua TEXT NULL,

    -- Alarma y distintivos (EnumList comma-separated)
    tipo_alarma TEXT NULL,
    distintivos_brigadistas TEXT NULL,

    -- Logistica
    puntos_encuentro TEXT NULL,
    recurso_humano TEXT NULL,
    equipos_emergencia TEXT NULL,

    -- Brigadista lider
    nombre_brigadista_lider VARCHAR(255) NULL,
    email_brigadista_lider VARCHAR(255) NULL,
    whatsapp_brigadista_lider VARCHAR(20) NULL,

    -- Evaluacion
    entrega_formato_evaluacion ENUM('si','no') NULL,

    -- Fotos
    imagen_1 VARCHAR(255) NULL,
    imagen_2 VARCHAR(255) NULL,

    -- Cronograma (9 TIME)
    hora_inicio TIME NULL,
    alistamiento_recursos TIME NULL,
    asumir_roles TIME NULL,
    suena_alarma TIME NULL,
    distribucion_roles TIME NULL,
    llegada_punto_encuentro TIME NULL,
    agrupacion_por_afinidad TIME NULL,
    conteo_personal TIME NULL,
    agradecimiento_cierre TIME NULL,

    -- General
    observaciones TEXT NULL,
    ruta_pdf VARCHAR(255) NULL,
    estado ENUM('borrador','completo') NOT NULL DEFAULT 'borrador',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_prep_sim_cliente FOREIGN KEY (id_cliente)
        REFERENCES tbl_clientes(id_cliente) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_prep_sim_consultor FOREIGN KEY (id_consultor)
        REFERENCES tbl_consultor(id_consultor) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_prep_sim_cliente (id_cliente),
    INDEX idx_prep_sim_consultor (id_consultor),
    INDEX idx_prep_sim_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

$pdo->exec($sql);
echo "OK — Tabla tbl_preparacion_simulacro creada (o ya existía).\n";

$cols = $pdo->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$config['dbname']}' AND TABLE_NAME = 'tbl_preparacion_simulacro'")->fetchColumn();
echo "Total columnas en tbl_preparacion_simulacro: {$cols}\n";
echo "\n¡Migración completada!\n";
