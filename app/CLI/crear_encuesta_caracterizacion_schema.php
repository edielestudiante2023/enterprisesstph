<?php
/**
 * Crea las tablas del modulo Encuesta de Caracterizacion.
 *
 * Uso:
 *   php app/CLI/crear_encuesta_caracterizacion_schema.php
 *   DB_PROD_PASS=xxx php app/CLI/crear_encuesta_caracterizacion_schema.php production
 */

$env = $argv[1] ?? 'local';

if ($env === 'production') {
    $host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port = 25060;
    $db   = 'propiedad_horizontal';
    $user = 'cycloid_userdb';
    $pass = getenv('DB_PROD_PASS');
    $ssl  = true;
    if (!$pass) {
        echo "ERROR: DB_PROD_PASS no esta definida.\n";
        exit(1);
    }
} else {
    $host = '127.0.0.1';
    $port = 3306;
    $db   = 'propiedad_horizontal';
    $user = 'root';
    $pass = '';
    $ssl  = false;
}

$dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
$opts = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
if ($ssl) {
    $opts[PDO::MYSQL_ATTR_SSL_CA] = true;
    $opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
}

try {
    $pdo = new PDO($dsn, $user, $pass, $opts);
    echo "Conectado a [{$env}] {$db}\n";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `tbl_encuesta_caracterizacion` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `id_cliente` INT UNSIGNED NOT NULL,
            `titulo` VARCHAR(200) NOT NULL DEFAULT 'Items Nucleares SG-SST',
            `token` VARCHAR(64) NOT NULL,
            `estado` VARCHAR(20) NOT NULL DEFAULT 'activa',
            `created_at` DATETIME NULL,
            `updated_at` DATETIME NULL,
            UNIQUE KEY `uk_encuesta_caracterizacion_token` (`token`),
            KEY `idx_encuesta_caracterizacion_cliente` (`id_cliente`),
            KEY `idx_encuesta_caracterizacion_estado` (`estado`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "OK: tbl_encuesta_caracterizacion\n";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `tbl_encuesta_caracterizacion_respuestas` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `id_encuesta` INT UNSIGNED NOT NULL,
            `nombre_administrador` VARCHAR(255) NULL,
            `horarios_administracion` VARCHAR(255) NULL,
            `anio_construccion` VARCHAR(255) NULL,
            `estructura_sismo_resistente` VARCHAR(255) NULL,
            `total_unidades_habitacionales` VARCHAR(255) NULL,
            `numero_torres_casas` VARCHAR(255) NULL,
            `cantidad_locales_comerciales` VARCHAR(255) NULL,
            `tiene_oficina_administracion` VARCHAR(255) NULL,
            `cantidad_salones_comunales` VARCHAR(255) NULL,
            `parqueaderos_carros_residentes` VARCHAR(255) NULL,
            `parqueaderos_carros_visitantes` VARCHAR(255) NULL,
            `parqueaderos_motos_residentes` VARCHAR(255) NULL,
            `parqueaderos_motos_visitantes` VARCHAR(255) NULL,
            `propietarios_parqueadero_privado` VARCHAR(255) NULL,
            `proveedor_vigilancia` VARCHAR(255) NULL,
            `cantidad_personal_vigilancia` VARCHAR(255) NULL,
            `proveedor_aseo` VARCHAR(255) NULL,
            `cantidad_personal_aseo` VARCHAR(255) NULL,
            `otros_proveedores` VARCHAR(255) NULL,
            `empresa_control_roedores` VARCHAR(255) NULL,
            `registro_visitantes_descripcion` VARCHAR(255) NULL,
            `registro_visitantes_emergencia` VARCHAR(255) NULL,
            `cuenta_planta_electrica` VARCHAR(255) NULL,
            `cantidad_tanques` VARCHAR(255) NULL,
            `capacidad_individual_tanque` VARCHAR(255) NULL,
            `capacidad_total_almacenamiento` VARCHAR(255) NULL,
            `cuarto_basuras_abierto` VARCHAR(255) NULL,
            `cuenta_megafono` VARCHAR(255) NULL,
            `equipos_telefono_fijo` VARCHAR(255) NULL,
            `equipos_telefonia_celular` VARCHAR(255) NULL,
            `equipos_radio_onda_corta` VARCHAR(255) NULL,
            `equipos_software_citofonia` VARCHAR(255) NULL,
            `equipos_sistemas_megafonia` VARCHAR(255) NULL,
            `equipos_cctv_audio` VARCHAR(255) NULL,
            `equipos_alarma_comunicacion` VARCHAR(255) NULL,
            `equipos_voip` VARCHAR(255) NULL,
            `ip_registro` VARCHAR(45) NULL,
            `user_agent` VARCHAR(255) NULL,
            `created_at` DATETIME NULL,
            `updated_at` DATETIME NULL,
            KEY `idx_encuesta_respuestas_encuesta` (`id_encuesta`),
            KEY `idx_encuesta_respuestas_created` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "OK: tbl_encuesta_caracterizacion_respuestas\n";

    echo "\nVerificacion:\n";
    foreach (['tbl_encuesta_caracterizacion', 'tbl_encuesta_caracterizacion_respuestas'] as $table) {
        $count = $pdo->query("SHOW COLUMNS FROM `{$table}`")->rowCount();
        echo "  {$table}: {$count} columnas\n";
    }

    echo "\nSchema listo.\n";
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
