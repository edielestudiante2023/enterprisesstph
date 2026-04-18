<?php
/**
 * Migración: convertir tbl_evaluaciones.id_cliente a NULLABLE.
 * Razón: las evaluaciones son transversales a todos los clientes.
 * El id_cliente del form admin era una etiqueta decorativa; la segmentación
 * real se hace con tbl_evaluacion_respuestas.id_cliente_conjunto.
 *
 * Idempotente: detecta estado actual y ajusta solo si es necesario.
 *
 * Uso:
 *   LOCAL      : php app/SQL/alter_evaluaciones_id_cliente_nullable.php
 *   PRODUCCIÓN : DB_PROD_PASS=xxx php app/SQL/alter_evaluaciones_id_cliente_nullable.php production
 */
$env = $argv[1] ?? 'local';

if ($env === 'production') {
    $host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port = 25060;
    $db   = 'propiedad_horizontal';
    $user = 'cycloid_userdb';
    $pass = getenv('DB_PROD_PASS');
    $ssl  = true;
    if (!$pass) { fwrite(STDERR, "ERROR: DB_PROD_PASS no definida.\n"); exit(1); }
} else {
    $host = '127.0.0.1'; $port = 3306;
    $db   = 'propiedad_horizontal'; $user = 'root'; $pass = ''; $ssl = false;
}

$dsn  = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
$opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
if ($ssl) { $opts[PDO::MYSQL_ATTR_SSL_CA] = true; $opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false; }

$pdo = new PDO($dsn, $user, $pass, $opts);
echo "Conectado [{$env}]\n";

// ── Verificar estado actual de la columna ──────────────────────────────────
$col = $pdo->query("
    SELECT COLUMN_NAME, IS_NULLABLE, COLUMN_TYPE, COLUMN_DEFAULT
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tbl_evaluaciones'
      AND COLUMN_NAME = 'id_cliente'
")->fetch(PDO::FETCH_ASSOC);

if (!$col) {
    fwrite(STDERR, "ERROR: columna tbl_evaluaciones.id_cliente no existe.\n");
    exit(1);
}

echo "Estado actual: {$col['COLUMN_TYPE']} NULL={$col['IS_NULLABLE']} DEFAULT=" . ($col['COLUMN_DEFAULT'] ?? 'NULL') . "\n";

if ($col['IS_NULLABLE'] === 'YES') {
    echo "INFO: id_cliente ya es NULLABLE. Nada que hacer.\n";
    exit(0);
}

// ── ALTER ───────────────────────────────────────────────────────────────────
$pdo->exec("ALTER TABLE tbl_evaluaciones MODIFY COLUMN id_cliente INT UNSIGNED NULL DEFAULT NULL");
echo "OK: tbl_evaluaciones.id_cliente ahora es NULLABLE.\n";

// ── Verificación post-ALTER ────────────────────────────────────────────────
$col2 = $pdo->query("
    SELECT IS_NULLABLE, COLUMN_TYPE
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tbl_evaluaciones'
      AND COLUMN_NAME = 'id_cliente'
")->fetch(PDO::FETCH_ASSOC);
echo "Estado nuevo: {$col2['COLUMN_TYPE']} NULL={$col2['IS_NULLABLE']}\n";

echo "\nMigración completada.\n";
