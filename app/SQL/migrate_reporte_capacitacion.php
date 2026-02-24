<?php
/**
 * Migración: Crear tabla tbl_reporte_capacitacion
 * Reporte de capacitación SST — ~24 columnas (5 fotos, 1 multi-select TEXT, 2 DECIMAL)
 *
 * Uso LOCAL:    php migrate_reporte_capacitacion.php
 * Uso PROD:     DB_PROD_PASS=xxx php migrate_reporte_capacitacion.php production
 */

$isProduction = ($argv[1] ?? '') === 'production';

if ($isProduction) {
    $password = getenv('DB_PROD_PASS');
    if (!$password) {
        echo "ERROR: Variable DB_PROD_PASS no definida.\n";
        echo "Uso: DB_PROD_PASS=xxx php migrate_reporte_capacitacion.php production\n";
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
CREATE TABLE IF NOT EXISTS tbl_reporte_capacitacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_consultor INT NOT NULL,
    fecha_capacitacion DATE NOT NULL,

    -- Datos de la capacitacion
    nombre_capacitacion TEXT NULL,
    objetivo_capacitacion TEXT NULL,
    perfil_asistentes TEXT NULL,
    nombre_capacitador VARCHAR(255) NULL,
    horas_duracion DECIMAL(4,1) NULL,

    -- Asistencia y evaluacion
    numero_asistentes SMALLINT NULL DEFAULT 0,
    numero_programados SMALLINT NULL DEFAULT 0,
    numero_evaluados SMALLINT NULL DEFAULT 0,
    promedio_calificaciones DECIMAL(5,2) NULL,

    -- Fotos
    foto_listado_asistencia VARCHAR(255) NULL,
    foto_capacitacion VARCHAR(255) NULL,
    foto_evaluacion VARCHAR(255) NULL,
    foto_otros_1 VARCHAR(255) NULL,
    foto_otros_2 VARCHAR(255) NULL,

    -- General
    observaciones TEXT NULL,
    ruta_pdf VARCHAR(255) NULL,
    estado ENUM('borrador','completo') NOT NULL DEFAULT 'borrador',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_rep_cap_cliente FOREIGN KEY (id_cliente)
        REFERENCES tbl_clientes(id_cliente) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_rep_cap_consultor FOREIGN KEY (id_consultor)
        REFERENCES tbl_consultor(id_consultor) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_rep_cap_cliente (id_cliente),
    INDEX idx_rep_cap_consultor (id_consultor),
    INDEX idx_rep_cap_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

$pdo->exec($sql);
echo "OK — Tabla tbl_reporte_capacitacion creada (o ya existía).\n";

$cols = $pdo->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$config['dbname']}' AND TABLE_NAME = 'tbl_reporte_capacitacion'")->fetchColumn();
echo "Total columnas en tbl_reporte_capacitacion: {$cols}\n";
echo "\n¡Migración completada!\n";
