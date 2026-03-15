<?php
/**
 * Script CLI para crear/actualizar las 53 vistas SQL de Otto
 * Uso: php apply_views_otto.php [local|production]
 * Las vistas usan CREATE OR REPLACE VIEW — son idempotentes y seguras de re-ejecutar.
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
    die("Uso: php apply_views_otto.php [local|production]\n");
}

echo "=== Vistas SQL Otto - Cycloid Enterprises SST ===\n";
echo "Entorno: " . strtoupper($env) . "\n";
echo "Host: {$config['host']}:{$config['port']}\n";
echo "Database: {$config['database']}\n";
echo "---\n";

// Conectar
$mysqli = mysqli_init();

if ($config['ssl']) {
    $mysqli->ssl_set(null, null, null, null, null);
    $mysqli->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
}

$connected = @$mysqli->real_connect(
    $config['host'],
    $config['user'],
    $config['password'],
    $config['database'],
    $config['port'],
    null,
    $config['ssl'] ? MYSQLI_CLIENT_SSL : 0
);

if (!$connected) {
    die("ERROR de conexión: " . $mysqli->connect_error . "\n");
}

echo "Conexión exitosa.\n\n";

// Leer el archivo SQL
$sqlFile = __DIR__ . '/create_views_otto.sql';
if (!file_exists($sqlFile)) {
    die("ERROR: No se encontró el archivo {$sqlFile}\n");
}

$sqlContent = file_get_contents($sqlFile);

// Extraer cada CREATE OR REPLACE VIEW como sentencia independiente
// Dividir por punto y coma, filtrar líneas de comentario y vacías
$statements = [];
$current = '';

foreach (explode("\n", $sqlContent) as $line) {
    $trimmed = trim($line);

    // Saltar comentarios puros y líneas vacías cuando no hay sentencia en curso
    if ($trimmed === '' && $current === '') continue;
    if (str_starts_with($trimmed, '--') && $current === '') continue;

    $current .= $line . "\n";

    // Detectar fin de sentencia (punto y coma al final de la línea, sin contar comentarios)
    $withoutComment = preg_replace('/--.*$/', '', $trimmed);
    if (str_ends_with(rtrim($withoutComment), ';')) {
        $stmt = trim($current);
        if ($stmt !== '') {
            $statements[] = $stmt;
        }
        $current = '';
    }
}

// Filtrar: solo ejecutar sentencias CREATE OR REPLACE VIEW
$views = array_filter($statements, function($s) {
    return stripos($s, 'CREATE OR REPLACE VIEW') !== false;
});

echo "Vistas a crear/actualizar: " . count($views) . "\n\n";

$success = 0;
$errors  = 0;
$idx     = 0;

foreach ($views as $sql) {
    $idx++;
    // Extraer nombre de la vista para mostrar en log
    preg_match('/CREATE OR REPLACE VIEW\s+(\w+)/i', $sql, $m);
    $viewName = $m[1] ?? "vista_{$idx}";

    echo "[{$idx}] {$viewName}... ";

    try {
        if ($mysqli->query($sql)) {
            echo "OK\n";
            $success++;
        } else {
            echo "ERROR: " . $mysqli->error . "\n";
            $errors++;
        }
    } catch (\mysqli_sql_exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
        $errors++;
    }
}

echo "\n=== RESULTADO ===\n";
echo "Creadas/actualizadas: {$success}\n";
echo "Errores:              {$errors}\n";
echo "Total procesadas:     {$idx}\n";

if ($errors === 0) {
    echo "\nTODAS LAS VISTAS CREADAS SIN ERRORES.\n";
} else {
    echo "\nHAY ERRORES - REVISAR ANTES DE EJECUTAR EN PRODUCCIÓN.\n";
    exit(1);
}

$mysqli->close();
