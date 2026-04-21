<?php
/**
 * Aplica la migracion SQL de tbl_inspeccion_no_aplica.
 * Uso: php apply_migration_inspeccion_no_aplica.php [local|production]
 */

if (php_sapi_name() !== 'cli') {
    die("Solo CLI.\n");
}

$env = $argv[1] ?? 'local';

if ($env === 'local') {
    $config = [
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'user'     => 'root',
        'password' => '',
        'database' => 'propiedad_horizontal',
        'ssl'      => false,
    ];
} elseif ($env === 'production') {
    $config = [
        'host'     => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port'     => 25060,
        'user'     => 'cycloid_userdb',
        'password' => getenv('DB_PROD_PASS') ?: '',
        'database' => 'propiedad_horizontal',
        'ssl'      => true,
    ];
} else {
    die("Uso: php apply_migration_inspeccion_no_aplica.php [local|production]\n");
}

echo "=== Migracion tbl_inspeccion_no_aplica ===\n";
echo "Entorno: " . strtoupper($env) . "\n";
echo "Host: {$config['host']}:{$config['port']}\n";
echo "Database: {$config['database']}\n---\n";

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

if (!$connected) {
    die("ERROR de conexion: " . $mysqli->connect_error . "\n");
}

echo "Conexion OK.\n";

$sql = file_get_contents(__DIR__ . '/create_tbl_inspeccion_no_aplica.sql');

if ($mysqli->query($sql)) {
    echo "Tabla creada (o ya existia).\n";
    $res = $mysqli->query("SHOW TABLES LIKE 'tbl_inspeccion_no_aplica'");
    echo $res && $res->num_rows > 0 ? "Verificado: la tabla existe.\n" : "ADVERTENCIA: no se encuentra la tabla.\n";
} else {
    echo "ERROR: " . $mysqli->error . "\n";
}

$mysqli->close();
