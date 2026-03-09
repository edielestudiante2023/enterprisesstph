<?php
/**
 * Drop FK constraint de id_consultor en tbl_informe_avances
 * y hacer la columna nullable.
 *
 * Uso: php migrate_drop_fk_informe_avances.php [local|production]
 */

if (php_sapi_name() !== 'cli') {
    die('Este script solo puede ejecutarse desde la línea de comandos.');
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
    die("Uso: php migrate_drop_fk_informe_avances.php [local|production]\n");
}

echo "=== Drop FK id_consultor en tbl_informe_avances ===\n";
echo "Entorno: {$env}\n\n";

$flags = $config['ssl'] ? MYSQLI_CLIENT_SSL : 0;
$conn = mysqli_init();
if ($config['ssl']) {
    $conn->ssl_set(null, null, null, null, null);
}
if (!$conn->real_connect($config['host'], $config['user'], $config['password'], $config['database'], $config['port'], null, $flags)) {
    die("Error de conexión: " . $conn->connect_error . "\n");
}
$conn->set_charset('utf8mb4');

$queries = [
    'DROP FK' => "ALTER TABLE `tbl_informe_avances` DROP FOREIGN KEY `fk_infavance_consultor`",
    'NULLABLE' => "ALTER TABLE `tbl_informe_avances` MODIFY COLUMN `id_consultor` INT NULL DEFAULT NULL",
];

foreach ($queries as $label => $sql) {
    echo "  {$label}: ";
    if ($conn->query($sql)) {
        echo "OK\n";
    } else {
        echo "SKIP ({$conn->error})\n";
    }
}

echo "\n=== Done ===\n";
$conn->close();
