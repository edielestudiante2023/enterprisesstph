<?php
/**
 * Migración: agregar columna actividades_sin_respuesta a tbl_informe_avances.
 * Paralelo a actividades_abiertas — stored como text auto-poblado.
 * Uso: php add_actividades_sin_respuesta_informe.php [local|production]
 * Idempotente.
 */

if (php_sapi_name() !== 'cli') die("Solo CLI.\n");

$env = $argv[1] ?? 'local';

if ($env === 'local') {
    $config = ['host' => '127.0.0.1', 'port' => 3306, 'user' => 'root', 'password' => '', 'database' => 'propiedad_horizontal', 'ssl' => false];
} elseif ($env === 'production') {
    $config = ['host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com', 'port' => 25060, 'user' => 'cycloid_userdb', 'password' => getenv('DB_PROD_PASS') ?: '', 'database' => 'propiedad_horizontal', 'ssl' => true];
} else {
    die("Uso: php add_actividades_sin_respuesta_informe.php [local|production]\n");
}

echo "=== Migración actividades_sin_respuesta en tbl_informe_avances ===\n";
echo "Entorno: " . strtoupper($env) . " | DB: {$config['database']}\n---\n";

$dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
$opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
if ($config['ssl']) {
    $opts[PDO::MYSQL_ATTR_SSL_CA] = true;
    $opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
}

try {
    $pdo = new PDO($dsn, $config['user'], $config['password'], $opts);
    echo "Conectado.\n";

    $check = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME='tbl_informe_avances' AND COLUMN_NAME='actividades_sin_respuesta'");
    $check->execute([$config['database']]);
    if ($check->fetchColumn() > 0) {
        echo "Columna actividades_sin_respuesta ya existe (SKIP).\n";
    } else {
        $pdo->exec("ALTER TABLE tbl_informe_avances ADD COLUMN actividades_sin_respuesta TEXT NULL AFTER actividades_abiertas");
        echo "ADD COLUMN actividades_sin_respuesta: OK\n";
    }

    $total = $pdo->query("SELECT COUNT(*) FROM tbl_informe_avances")->fetchColumn();
    echo "Informes en la tabla: {$total}\n";
    echo "MIGRACIÓN OK.\n";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
