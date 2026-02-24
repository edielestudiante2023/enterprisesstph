<?php
/**
 * Migración: Crear tabla tbl_auditoria_zona_residuos
 * Auditoría zona residuos — ~33 columnas (11 ENUMs estado + 1 texto libre + 12 fotos)
 *
 * Uso LOCAL:    php migrate_auditoria_zona_residuos.php
 * Uso PROD:     DB_PROD_PASS=xxx php migrate_auditoria_zona_residuos.php production
 */

$isProduction = ($argv[1] ?? '') === 'production';

if ($isProduction) {
    $password = getenv('DB_PROD_PASS');
    if (!$password) {
        echo "ERROR: Variable DB_PROD_PASS no definida.\n";
        echo "Uso: DB_PROD_PASS=xxx php migrate_auditoria_zona_residuos.php production\n";
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
CREATE TABLE IF NOT EXISTS tbl_auditoria_zona_residuos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_consultor INT NOT NULL,
    fecha_inspeccion DATE NOT NULL,

    -- Acceso
    estado_acceso ENUM('bueno','regular','malo','deficiente','no_tiene','no_aplica') NULL,
    foto_acceso VARCHAR(255) NULL,

    -- Techo Pared Pisos
    estado_techo_pared_pisos ENUM('bueno','regular','malo','deficiente','no_tiene','no_aplica') NULL,
    foto_techo_pared_pisos VARCHAR(255) NULL,

    -- Ventilacion
    estado_ventilacion ENUM('bueno','regular','malo','deficiente','no_tiene','no_aplica') NULL,
    foto_ventilacion VARCHAR(255) NULL,

    -- Prevencion Control Incendios
    estado_prevencion_incendios ENUM('bueno','regular','malo','deficiente','no_tiene','no_aplica') NULL,
    foto_prevencion_incendios VARCHAR(255) NULL,

    -- Drenajes
    estado_drenajes ENUM('bueno','regular','malo','deficiente','no_tiene','no_aplica') NULL,
    foto_drenajes VARCHAR(255) NULL,

    -- Proliferacion Plagas (texto libre)
    proliferacion_plagas VARCHAR(255) NULL,
    foto_proliferacion_plagas VARCHAR(255) NULL,

    -- Recipientes
    estado_recipientes ENUM('bueno','regular','malo','deficiente','no_tiene','no_aplica') NULL,
    foto_recipientes VARCHAR(255) NULL,

    -- Reciclaje
    estado_reciclaje ENUM('bueno','regular','malo','deficiente','no_tiene','no_aplica') NULL,
    foto_reciclaje VARCHAR(255) NULL,

    -- Iluminarias
    estado_iluminarias ENUM('bueno','regular','malo','deficiente','no_tiene','no_aplica') NULL,
    foto_iluminarias VARCHAR(255) NULL,

    -- Senalizacion
    estado_senalizacion ENUM('bueno','regular','malo','deficiente','no_tiene','no_aplica') NULL,
    foto_senalizacion VARCHAR(255) NULL,

    -- Limpieza y Desinfeccion
    estado_limpieza_desinfeccion ENUM('bueno','regular','malo','deficiente','no_tiene','no_aplica') NULL,
    foto_limpieza_desinfeccion VARCHAR(255) NULL,

    -- Poseta
    estado_poseta ENUM('bueno','regular','malo','deficiente','no_tiene','no_aplica') NULL,
    foto_poseta VARCHAR(255) NULL,

    -- General
    observaciones TEXT NULL,
    ruta_pdf VARCHAR(255) NULL,
    estado ENUM('borrador','completo') NOT NULL DEFAULT 'borrador',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_aud_res_cliente FOREIGN KEY (id_cliente)
        REFERENCES tbl_clientes(id_cliente) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_aud_res_consultor FOREIGN KEY (id_consultor)
        REFERENCES tbl_consultor(id_consultor) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_aud_res_cliente (id_cliente),
    INDEX idx_aud_res_consultor (id_consultor),
    INDEX idx_aud_res_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

$pdo->exec($sql);
echo "OK — Tabla tbl_auditoria_zona_residuos creada (o ya existía).\n";

$cols = $pdo->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$config['dbname']}' AND TABLE_NAME = 'tbl_auditoria_zona_residuos'")->fetchColumn();
echo "Total columnas en tbl_auditoria_zona_residuos: {$cols}\n";
echo "\n¡Migración completada!\n";
