<?php
/**
 * Migración: Crear tabla tbl_plan_emergencia
 * Documento maestro consolidado - ~82 columnas (8 ENUMs, 19 fotos, ~30 TEXT, ~12 SMALLINT)
 *
 * Uso LOCAL:    php migrate_plan_emergencia.php
 * Uso PROD:     DB_PROD_PASS=xxx php migrate_plan_emergencia.php production
 */

$isProduction = ($argv[1] ?? '') === 'production';

if ($isProduction) {
    $password = getenv('DB_PROD_PASS');
    if (!$password) {
        echo "ERROR: Variable DB_PROD_PASS no definida.\n";
        echo "Uso: DB_PROD_PASS=xxx php migrate_plan_emergencia.php production\n";
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
CREATE TABLE IF NOT EXISTS tbl_plan_emergencia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_consultor INT NOT NULL,
    fecha_visita DATE NOT NULL,

    -- FOTOS FACHADA Y PANORAMA
    foto_fachada VARCHAR(255) NULL,
    foto_panorama VARCHAR(255) NULL,

    -- DESCRIPCION DEL INMUEBLE
    casas_o_apartamentos ENUM('casas','apartamentos') NULL,
    sismo_resistente VARCHAR(255) NULL,
    anio_construccion SMALLINT NULL,
    numero_torres SMALLINT NULL,
    numero_unidades_habitacionales SMALLINT NULL,
    casas_pisos VARCHAR(255) NULL,
    foto_torres_1 VARCHAR(255) NULL,
    foto_torres_2 VARCHAR(255) NULL,

    -- PARQUEADEROS
    parqueaderos_carros_residentes SMALLINT NULL DEFAULT 0,
    parqueaderos_carros_visitantes SMALLINT NULL DEFAULT 0,
    parqueaderos_motos_residentes SMALLINT NULL DEFAULT 0,
    parqueaderos_motos_visitantes SMALLINT NULL DEFAULT 0,
    hay_parqueadero_privado ENUM('si','no') NULL,
    foto_parqueaderos_carros VARCHAR(255) NULL,
    foto_parqueaderos_motos VARCHAR(255) NULL,

    -- AREAS COMUNES
    cantidad_salones_comunales SMALLINT NULL DEFAULT 0,
    cantidad_locales_comerciales SMALLINT NULL DEFAULT 0,
    tiene_oficina_admin ENUM('si','no') NULL,
    foto_oficina_admin VARCHAR(255) NULL,

    -- SERVICIOS
    tanque_agua TEXT NULL,
    planta_electrica TEXT NULL,

    -- CIRCULACION VEHICULAR
    circulacion_vehicular TEXT NULL,
    foto_circulacion_vehicular VARCHAR(255) NULL,

    -- CIRCULACION PEATONAL
    circulacion_peatonal TEXT NULL,
    foto_circulacion_peatonal_1 VARCHAR(255) NULL,
    foto_circulacion_peatonal_2 VARCHAR(255) NULL,

    -- SALIDAS DE EMERGENCIA
    salidas_emergencia TEXT NULL,
    foto_salida_emergencia_1 VARCHAR(255) NULL,
    foto_salida_emergencia_2 VARCHAR(255) NULL,

    -- INGRESOS PEATONALES
    ingresos_peatonales TEXT NULL,
    foto_ingresos_peatonales VARCHAR(255) NULL,

    -- ACCESOS VEHICULARES
    accesos_vehiculares TEXT NULL,
    foto_acceso_vehicular_1 VARCHAR(255) NULL,
    foto_acceso_vehicular_2 VARCHAR(255) NULL,

    -- CONCEPTOS DEL CONSULTOR
    concepto_entradas_salidas TEXT NULL,
    hidrantes TEXT NULL,

    -- ENTIDADES CERCANAS
    cai_cercano VARCHAR(255) NULL,
    bomberos_cercanos VARCHAR(255) NULL,

    -- PROVEEDORES
    proveedor_vigilancia VARCHAR(255) NULL,
    proveedor_aseo VARCHAR(255) NULL,
    otros_proveedores TEXT NULL,

    -- CONTROL DE VISITANTES
    registro_visitantes_forma TEXT NULL,
    registro_visitantes_emergencia ENUM('si','no') NULL,

    -- COMUNICACIONES Y SEGURIDAD
    cuenta_megafono ENUM('si','no') NULL,
    ruta_evacuacion TEXT NULL,
    mapa_evacuacion TEXT NULL,
    foto_ruta_evacuacion_1 VARCHAR(255) NULL,
    foto_ruta_evacuacion_2 VARCHAR(255) NULL,
    puntos_encuentro TEXT NULL,
    foto_punto_encuentro_1 VARCHAR(255) NULL,
    foto_punto_encuentro_2 VARCHAR(255) NULL,
    sistema_alarma TEXT NULL,
    codigos_alerta TEXT NULL,
    energia_emergencia TEXT NULL,
    deteccion_fuego TEXT NULL,
    vias_transito TEXT NULL,

    -- ADMINISTRACION
    nombre_administrador VARCHAR(255) NULL,
    horarios_administracion VARCHAR(255) NULL,
    personal_aseo TEXT NULL,
    personal_vigilancia TEXT NULL,

    -- TELEFONOS DE EMERGENCIA
    ciudad ENUM('bogota','soacha') NULL,
    cuadrante VARCHAR(50) NULL,
    tiene_gabinetes_hidraulico ENUM('si','no') NULL,

    -- SERVICIOS GENERALES
    ruta_residuos_solidos TEXT NULL,
    empresa_aseo ENUM('urbaser_soacha','bogota_limpia','promoambiental','ciudad_limpia','area_limpia','lime') NULL,
    servicios_sanitarios TEXT NULL,
    frecuencia_basura VARCHAR(255) NULL,
    detalle_mascotas TEXT NULL,
    detalle_dependencias TEXT NULL,

    -- GENERAL
    observaciones TEXT NULL,
    ruta_pdf VARCHAR(255) NULL,
    estado ENUM('borrador','completo') NOT NULL DEFAULT 'borrador',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_plan_emg_cliente FOREIGN KEY (id_cliente)
        REFERENCES tbl_clientes(id_cliente) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_plan_emg_consultor FOREIGN KEY (id_consultor)
        REFERENCES tbl_consultor(id_consultor) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_plan_emg_cliente (id_cliente),
    INDEX idx_plan_emg_consultor (id_consultor),
    INDEX idx_plan_emg_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

$pdo->exec($sql);
echo "OK — Tabla tbl_plan_emergencia creada (o ya existía).\n";

// Verificar columnas
$cols = $pdo->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$config['dbname']}' AND TABLE_NAME = 'tbl_plan_emergencia'")->fetchColumn();
echo "Total columnas en tbl_plan_emergencia: {$cols}\n";
echo "\n¡Migración completada!\n";
