<?php
/**
 * Migración: Crear tabla tbl_matriz_vulnerabilidad
 * 25 criterios de evaluación ENUM('a','b','c') + observaciones + meta
 *
 * Uso LOCAL:    php migrate_matriz_vulnerabilidad.php
 * Uso PROD:     DB_PROD_PASS=xxx php migrate_matriz_vulnerabilidad.php production
 */

$isProduction = ($argv[1] ?? '') === 'production';

if ($isProduction) {
    $password = getenv('DB_PROD_PASS');
    if (!$password) {
        echo "ERROR: Variable DB_PROD_PASS no definida.\n";
        echo "Uso: DB_PROD_PASS=xxx php migrate_matriz_vulnerabilidad.php production\n";
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

// Crear tabla
$sql = "
CREATE TABLE IF NOT EXISTS tbl_matriz_vulnerabilidad (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_consultor INT NOT NULL,
    fecha_inspeccion DATE NOT NULL,
    -- 25 criterios de evaluación (a=1.0, b=0.5, c=0.0)
    c1_plan_evacuacion ENUM('a','b','c') NULL,
    c2_alarma_evacuacion ENUM('a','b','c') NULL,
    c3_ruta_evacuacion ENUM('a','b','c') NULL,
    c4_visitantes_rutas ENUM('a','b','c') NULL,
    c5_puntos_reunion ENUM('a','b','c') NULL,
    c6_puntos_reunion_2 ENUM('a','b','c') NULL,
    c7_senalizacion_evacuacion ENUM('a','b','c') NULL,
    c8_rutas_evacuacion ENUM('a','b','c') NULL,
    c9_ruta_principal ENUM('a','b','c') NULL,
    c10_senal_alarma ENUM('a','b','c') NULL,
    c11_sistema_deteccion ENUM('a','b','c') NULL,
    c12_iluminacion ENUM('a','b','c') NULL,
    c13_iluminacion_emergencia ENUM('a','b','c') NULL,
    c14_sistema_contra_incendio ENUM('a','b','c') NULL,
    c15_extintores ENUM('a','b','c') NULL,
    c16_divulgacion_plan ENUM('a','b','c') NULL,
    c17_coordinador_plan ENUM('a','b','c') NULL,
    c18_brigada_emergencia ENUM('a','b','c') NULL,
    c19_simulacros ENUM('a','b','c') NULL,
    c20_entidades_socorro ENUM('a','b','c') NULL,
    c21_ocupantes ENUM('a','b','c') NULL,
    c22_plano_evacuacion ENUM('a','b','c') NULL,
    c23_rutas_circulacion ENUM('a','b','c') NULL,
    c24_puertas_salida ENUM('a','b','c') NULL,
    c25_estructura_construccion ENUM('a','b','c') NULL,
    -- General
    observaciones TEXT NULL,
    ruta_pdf VARCHAR(255) NULL,
    estado ENUM('borrador','completo') NOT NULL DEFAULT 'borrador',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    -- FKs e índices
    CONSTRAINT fk_mat_vul_cliente FOREIGN KEY (id_cliente)
        REFERENCES tbl_clientes(id_cliente) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_mat_vul_consultor FOREIGN KEY (id_consultor)
        REFERENCES tbl_consultor(id_consultor) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_mat_vul_cliente (id_cliente),
    INDEX idx_mat_vul_consultor (id_consultor),
    INDEX idx_mat_vul_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

try {
    $pdo->exec($sql);
    echo "OK: Tabla tbl_matriz_vulnerabilidad creada/verificada.\n";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nMigración completada.\n";
