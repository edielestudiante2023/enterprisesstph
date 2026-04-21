<?php
/**
 * Aplica la migracion de tbl_pta_no_aplica.
 * Uso: php apply_migration_pta_no_aplica.php [local|production]
 */

if (php_sapi_name() !== 'cli') die("Solo CLI.\n");

$env = $argv[1] ?? 'local';

if ($env === 'local') {
    $cfg = ['host' => '127.0.0.1', 'port' => 3306, 'user' => 'root', 'password' => '', 'database' => 'propiedad_horizontal', 'ssl' => false];
} elseif ($env === 'production') {
    $cfg = [
        'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port' => 25060, 'user' => 'cycloid_userdb',
        'password' => getenv('DB_PROD_PASS') ?: '',
        'database' => 'propiedad_horizontal', 'ssl' => true,
    ];
} else {
    die("Uso: php apply_migration_pta_no_aplica.php [local|production]\n");
}

echo "=== Migracion tbl_pta_no_aplica ===\n";
echo "Entorno: " . strtoupper($env) . "\n---\n";

$m = mysqli_init();
if ($cfg['ssl']) {
    $m->ssl_set(null, null, null, null, null);
    $m->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
}
if (!@$m->real_connect($cfg['host'], $cfg['user'], $cfg['password'], $cfg['database'], $cfg['port'], null, $cfg['ssl'] ? MYSQLI_CLIENT_SSL : 0)) {
    die("ERROR conexion: " . $m->connect_error . "\n");
}

echo "Conexion OK.\n";

$sql = file_get_contents(__DIR__ . '/create_tbl_pta_no_aplica.sql');
if ($m->query($sql)) {
    echo "Tabla creada (o ya existia).\n";
    $r = $m->query("SHOW TABLES LIKE 'tbl_pta_no_aplica'");
    echo $r && $r->num_rows > 0 ? "Verificado: existe.\n" : "ADVERTENCIA.\n";
} else {
    echo "ERROR: " . $m->error . "\n";
}

$m->close();
