<?php
/**
 * Hace nullable la columna id_consultor en tbl_programa_plagas
 * para permitir que usuarios no-consultores creen registros.
 *
 * Uso: php migrate_plagas_nullable_consultor.php [local|production]
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
    die("Uso: php migrate_plagas_nullable_consultor.php [local|production]\n");
}

echo "=== Migración: tbl_programa_plagas id_consultor nullable ===\n";
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
    'DROP FK constraint' => "ALTER TABLE `tbl_programa_plagas` DROP FOREIGN KEY `fk_prog_plag_consultor`",
    'Make id_consultor nullable' => "ALTER TABLE `tbl_programa_plagas` MODIFY COLUMN `id_consultor` INT NULL DEFAULT NULL",
    'Recreate FK with SET NULL' => "ALTER TABLE `tbl_programa_plagas` ADD CONSTRAINT `fk_prog_plag_consultor` FOREIGN KEY (`id_consultor`) REFERENCES `tbl_consultor`(`id_consultor`) ON DELETE SET NULL ON UPDATE CASCADE",
];

foreach ($queries as $label => $sql) {
    echo "  {$label}... ";
    if ($conn->query($sql)) {
        echo "OK\n";
    } else {
        echo "ERROR: " . $conn->error . "\n";
    }
}

echo "\n=== Migración completada ===\n";
$conn->close();
