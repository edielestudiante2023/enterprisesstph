<?php
/**
 * Script CLI — ALTER ENUM `area` de tbl_procedimiento_emergencia_area para
 * agregar el valor 'JACUZZI' (Fase 3 Zonas Complementarias, modulo Turco+Sauna+Jacuzzi).
 *
 * Uso: php migrate_enum_area_add_jacuzzi.php [local|production]
 *
 * ENUM antes: ('PISCINA','BANO_TURCO','SAUNA','GYM','ZONA_BBQ')
 * ENUM despues: ('PISCINA','BANO_TURCO','SAUNA','GYM','ZONA_BBQ','JACUZZI')
 *
 * Idempotente: verifica el ENUM actual antes de correr el ALTER. Si JACUZZI ya
 * esta presente, no hace nada.
 */

if (php_sapi_name() !== 'cli') {
    die('Este script solo puede ejecutarse desde la linea de comandos.');
}

mysqli_report(MYSQLI_REPORT_OFF);

$env = $argv[1] ?? 'local';

if ($env === 'local') {
    $config = [
        'host' => '127.0.0.1', 'port' => 3306,
        'user' => 'root', 'password' => '',
        'database' => 'propiedad_horizontal', 'ssl' => false,
    ];
} elseif ($env === 'production') {
    $config = [
        'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com', 'port' => 25060,
        'user' => 'cycloid_userdb', 'password' => getenv('DB_PROD_PASS') ?: '',
        'database' => 'propiedad_horizontal', 'ssl' => true,
    ];
    if (empty($config['password'])) die("ERROR: DB_PROD_PASS no definida.\n");
} else {
    die("Uso: php migrate_enum_area_add_jacuzzi.php [local|production]\n");
}

echo "=== ALTER ENUM area + JACUZZI (FT-SST-249) ===\n";
echo "Entorno: " . strtoupper($env) . "\n---\n";

$mysqli = mysqli_init();
if ($config['ssl']) {
    $mysqli->ssl_set(null, null, null, null, null);
    $mysqli->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
}

$connected = @$mysqli->real_connect(
    $config['host'], $config['user'], $config['password'],
    $config['database'], $config['port'], null,
    $config['ssl'] ? MYSQLI_CLIENT_SSL : 0
);

if (!$connected) die("ERROR de conexion: " . $mysqli->connect_error . "\n");
$mysqli->set_charset('utf8mb4');

// Leer definicion actual del ENUM
$res = $mysqli->query("SELECT COLUMN_TYPE FROM information_schema.COLUMNS
                       WHERE TABLE_SCHEMA = DATABASE()
                         AND TABLE_NAME = 'tbl_procedimiento_emergencia_area'
                         AND COLUMN_NAME = 'area'");
if (!$res || $res->num_rows === 0) {
    die("ERROR: no se encontro la columna area en tbl_procedimiento_emergencia_area.\n");
}
$tipoActual = $res->fetch_assoc()['COLUMN_TYPE'];
echo "ENUM actual: $tipoActual\n";

if (stripos($tipoActual, "'JACUZZI'") !== false) {
    echo "JACUZZI ya esta presente. Nada que hacer.\n";
    $mysqli->close();
    exit(0);
}

// Ejecutar ALTER
$sql = "ALTER TABLE tbl_procedimiento_emergencia_area
        MODIFY COLUMN area ENUM('PISCINA','BANO_TURCO','SAUNA','GYM','ZONA_BBQ','JACUZZI') NOT NULL";
echo "Ejecutando: ALTER ...\n";
if (!$mysqli->query($sql)) {
    die("ERROR ALTER: " . $mysqli->error . "\n");
}
echo "ALTER OK.\n";

// Verificar
$res2 = $mysqli->query("SELECT COLUMN_TYPE FROM information_schema.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE()
                          AND TABLE_NAME = 'tbl_procedimiento_emergencia_area'
                          AND COLUMN_NAME = 'area'");
$tipoFinal = $res2->fetch_assoc()['COLUMN_TYPE'];
echo "ENUM despues: $tipoFinal\n";

echo "\nMIGRACION COMPLETADA SIN ERRORES.\n";
$mysqli->close();
