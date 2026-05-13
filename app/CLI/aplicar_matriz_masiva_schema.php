<?php

/**
 * Aplica/verifica soporte de BD para acciones masivas de Matriz de Inspecciones.
 *
 * Uso:
 *   php app/CLI/aplicar_matriz_masiva_schema.php --all
 *   php app/CLI/aplicar_matriz_masiva_schema.php --local
 *   php app/CLI/aplicar_matriz_masiva_schema.php --production
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Este script solo se ejecuta por CLI.\n");
    exit(1);
}

$root = dirname(__DIR__, 2);

function readEnvValue(string $root, string $key, ?string $default = null): ?string
{
    $envPath = $root . DIRECTORY_SEPARATOR . '.env';
    if (!is_file($envPath)) return $default;

    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (!str_starts_with($line, $key . ' =')) continue;

        return trim(substr($line, strlen($key . ' =')));
    }

    return $default;
}

function readSqlInstructionCredentials(): array
{
    $path = 'D:\\DESARROLLO\\KEYS\\sql.txt';
    if (!is_file($path)) {
        throw new RuntimeException('No se encontro D:\\DESARROLLO\\KEYS\\sql.txt para credenciales de produccion.');
    }

    $content = file_get_contents($path);
    $keys = ['username', 'password', 'host', 'port', 'database'];
    $out = [];
    foreach ($keys as $key) {
        if (!preg_match('/^' . preg_quote($key, '/') . '\s*=\s*(.+)$/mi', $content, $m)) {
            throw new RuntimeException("Falta {$key} en sql.txt.");
        }
        $out[$key] = trim($m[1]);
    }

    return $out;
}

function connectDb(array $cfg, bool $ssl): mysqli
{
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $mysqli = mysqli_init();
    if ($ssl) {
        $mysqli->ssl_set(null, null, null, null, null);
    }
    $flags = $ssl ? MYSQLI_CLIENT_SSL : 0;
    $mysqli->real_connect(
        $cfg['host'],
        $cfg['username'],
        $cfg['password'],
        $cfg['database'],
        (int) $cfg['port'],
        null,
        $flags
    );
    $mysqli->set_charset('utf8mb4');

    return $mysqli;
}

function indexExists(mysqli $db, string $table, string $index): bool
{
    $tableEsc = $db->real_escape_string($table);
    $indexEsc = $db->real_escape_string($index);
    $res = $db->query("SHOW INDEX FROM `{$tableEsc}` WHERE Key_name = '{$indexEsc}'");

    return $res->num_rows > 0;
}

function applySchema(mysqli $db, string $label): void
{
    echo "[{$label}] Verificando tablas...\n";

    $db->query("
        CREATE TABLE IF NOT EXISTS `tbl_inspeccion_frecuencia_cliente` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_cliente` INT NOT NULL,
            `slug_inspeccion` VARCHAR(120) NOT NULL,
            `veces_anio` INT NOT NULL DEFAULT 0,
            `created_at` DATETIME NULL,
            `updated_at` DATETIME NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");

    $db->query("
        CREATE TABLE IF NOT EXISTS `tbl_inspeccion_no_aplica` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_cliente` INT NOT NULL,
            `tipo_inspeccion` VARCHAR(120) NOT NULL,
            `motivo` TEXT NULL,
            `marcado_por` INT NULL,
            `fecha_marcado` DATETIME NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");

    if (!indexExists($db, 'tbl_inspeccion_frecuencia_cliente', 'idx_ifc_cliente_slug')) {
        echo "[{$label}] Creando indice idx_ifc_cliente_slug...\n";
        $db->query("ALTER TABLE `tbl_inspeccion_frecuencia_cliente` ADD INDEX `idx_ifc_cliente_slug` (`id_cliente`, `slug_inspeccion`)");
    }

    if (!indexExists($db, 'tbl_inspeccion_no_aplica', 'idx_ina_cliente_tipo')) {
        echo "[{$label}] Creando indice idx_ina_cliente_tipo...\n";
        $db->query("ALTER TABLE `tbl_inspeccion_no_aplica` ADD INDEX `idx_ina_cliente_tipo` (`id_cliente`, `tipo_inspeccion`)");
    }

    echo "[{$label}] OK\n";
}

$args = $argv;
$runLocal = in_array('--local', $args, true) || in_array('--all', $args, true) || count($args) === 1;
$runProd  = in_array('--production', $args, true) || in_array('--all', $args, true);

try {
    if ($runLocal) {
        $local = [
            'host'     => readEnvValue($root, 'database.default.hostname', 'localhost'),
            'database' => readEnvValue($root, 'database.default.database', 'propiedad_horizontal'),
            'username' => readEnvValue($root, 'database.default.username', 'root'),
            'password' => readEnvValue($root, 'database.default.password', ''),
            'port'     => readEnvValue($root, 'database.default.port', '3306'),
        ];
        $db = connectDb($local, false);
        applySchema($db, 'LOCAL');
        $db->close();
    }

    if ($runProd) {
        $prodRaw = readSqlInstructionCredentials();
        $prod = [
            'host'     => $prodRaw['host'],
            'database' => $prodRaw['database'],
            'username' => $prodRaw['username'],
            'password' => $prodRaw['password'],
            'port'     => $prodRaw['port'],
        ];
        $db = connectDb($prod, true);
        applySchema($db, 'PRODUCCION');
        $db->close();
    }
} catch (Throwable $e) {
    fwrite(STDERR, '[ERROR] ' . $e->getMessage() . "\n");
    exit(1);
}
