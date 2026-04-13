<?php
/**
 * Migracion: crear tabla tbl_inspeccion_brigada_simulacros (patron PLANA).
 *
 * Modulo de inspeccion que alimenta la seccion Brigada y Simulacros del Plan
 * de Emergencia. La IA (Claude) usa estos datos para generar el texto
 * personalizado de las secciones brigada_ia_texto y simulacros_ia_texto en
 * tbl_plan_emergencia.
 *
 * Uso:
 *   php app/SQL/migrate_inspeccion_brigada_simulacros.php local
 *   DB_PROD_PASS=xxx php app/SQL/migrate_inspeccion_brigada_simulacros.php production
 */

$env = $argv[1] ?? 'local';

$configs = [
    'local' => [
        'host' => 'localhost',
        'user' => 'root',
        'pass' => '',
        'db'   => 'propiedad_horizontal',
        'ssl'  => false,
    ],
    'production' => [
        'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port' => 25060,
        'user' => 'cycloid_userdb',
        'pass' => getenv('DB_PROD_PASS') ?: '',
        'db'   => 'propiedad_horizontal',
        'ssl'  => true,
    ],
];

if (!isset($configs[$env])) {
    echo "Uso: php migrate_inspeccion_brigada_simulacros.php [local|production]\n";
    exit(1);
}

$cfg = $configs[$env];
echo "=== Migracion tbl_inspeccion_brigada_simulacros - Entorno: {$env} ===\n\n";

if ($env === 'production' && empty($cfg['pass'])) {
    echo "ERROR: DB_PROD_PASS no esta definida en variables de entorno\n";
    exit(1);
}

if ($cfg['ssl'] ?? false) {
    $conn = mysqli_init();
    $conn->ssl_set(null, null, null, null, null);
    $conn->real_connect($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['db'], $cfg['port'] ?? 3306, null, MYSQLI_CLIENT_SSL);
} else {
    $conn = new mysqli($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['db'], $cfg['port'] ?? 3306);
}

if ($conn->connect_error) {
    echo "ERROR de conexion: " . $conn->connect_error . "\n";
    exit(1);
}

echo "Conectado a {$cfg['db']}@{$cfg['host']}\n\n";

// Verificar si la tabla ya existe
$check = $conn->query("SHOW TABLES LIKE 'tbl_inspeccion_brigada_simulacros'");
if ($check && $check->num_rows > 0) {
    echo "[SKIP] La tabla tbl_inspeccion_brigada_simulacros ya existe\n";
    echo "\n=== Migracion idempotente: nada que hacer ===\n";
    $conn->close();
    exit(0);
}

$sql = "CREATE TABLE tbl_inspeccion_brigada_simulacros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_consultor INT NULL,
    fecha_inspeccion DATE NOT NULL,

    -- Estado actual de la brigada
    existe_brigada ENUM('si','no','parcial') NOT NULL DEFAULT 'no',
    fecha_conformacion DATE NULL,
    numero_brigadistas SMALLINT NOT NULL DEFAULT 0,
    nombre_jefe_brigada VARCHAR(255) NULL,
    brigada_capacitada ENUM('si','no','parcial') NOT NULL DEFAULT 'no',
    cuenta_dotacion ENUM('si','no','parcial') NOT NULL DEFAULT 'no',
    detalle_dotacion TEXT NULL,

    -- Capacitaciones realizadas
    capacitacion_primeros_auxilios ENUM('si','no') NOT NULL DEFAULT 'no',
    capacitacion_extintores ENUM('si','no') NOT NULL DEFAULT 'no',
    capacitacion_evacuacion ENUM('si','no') NOT NULL DEFAULT 'no',
    capacitacion_busqueda_rescate ENUM('si','no') NOT NULL DEFAULT 'no',
    capacitacion_comunicaciones ENUM('si','no') NOT NULL DEFAULT 'no',
    fecha_ultima_capacitacion DATE NULL,
    capacitaciones_12m TEXT NULL,

    -- Simulacros
    fecha_ultimo_simulacro DATE NULL,
    tipo_simulacro ENUM('escritorio','parcial','general','no_realizado') NOT NULL DEFAULT 'no_realizado',
    participo_simulacro_nacional ENUM('si','no') NOT NULL DEFAULT 'no',
    cantidad_simulacros_12m SMALLINT NOT NULL DEFAULT 0,

    -- Hallazgos del consultor
    fortalezas TEXT NULL,
    debilidades TEXT NULL,
    recomendaciones TEXT NULL,
    observaciones TEXT NULL,

    -- Evidencias fotograficas (rutas relativas dentro de uploads/)
    foto_brigada_1 VARCHAR(255) NULL,
    foto_brigada_2 VARCHAR(255) NULL,
    foto_dotacion VARCHAR(255) NULL,
    foto_acta_simulacro VARCHAR(255) NULL,

    -- Control de estado y auditoria
    estado ENUM('borrador','completo') NOT NULL DEFAULT 'borrador',
    ruta_pdf VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_cliente (id_cliente),
    INDEX idx_estado (estado),
    INDEX idx_fecha (fecha_inspeccion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql)) {
    echo "[OK] Tabla tbl_inspeccion_brigada_simulacros creada exitosamente\n";

    // Verificacion
    $verify = $conn->query("DESCRIBE tbl_inspeccion_brigada_simulacros");
    $cols = 0;
    while ($verify->fetch_assoc()) $cols++;
    echo "[OK] Verificado: {$cols} columnas creadas\n";
    $conn->close();
    exit(0);
} else {
    echo "[ERROR] " . $conn->error . "\n";
    $conn->close();
    exit(1);
}
