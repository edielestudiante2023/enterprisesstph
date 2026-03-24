<?php
/**
 * Migración: renombrar tablas de evaluación para arquitectura correcta.
 *
 * tbl_evaluacion_induccion          → tbl_evaluaciones
 * tbl_evaluacion_induccion_respuesta → tbl_evaluacion_respuestas
 * tbl_evaluacion_sesion              → tbl_evaluacion_sesiones
 * + ALTER tbl_evaluaciones.id_cliente → NULL (evaluación es del tema, no del cliente)
 *
 * Uso:
 *   php app/SQL/rename_evaluacion_tables.php          (local)
 *   DB_PROD_PASS=xxx php app/SQL/rename_evaluacion_tables.php production
 */

$env = $argv[1] ?? 'local';

if ($env === 'production') {
    $host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port = 25060;
    $db   = 'propiedad_horizontal';
    $user = 'cycloid_userdb';
    $pass = getenv('DB_PROD_PASS');
    $ssl  = true;
} else {
    $host = 'localhost'; $port = 3306;
    $db   = 'propiedad_horizontal'; $user = 'root'; $pass = ''; $ssl = false;
}

$dsn  = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
$opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
if ($ssl) {
    $opts[PDO::MYSQL_ATTR_SSL_CA] = true;
    $opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
}

$pdo = new PDO($dsn, $user, $pass, $opts);
echo "Conectado [{$env}]\n\n";

// Helper: verificar si tabla existe
function tableExists(PDO $pdo, string $db, string $table): bool {
    $r = $pdo->query("SELECT COUNT(*) FROM information_schema.tables
        WHERE table_schema='{$db}' AND table_name='{$table}'")->fetchColumn();
    return (int)$r > 0;
}

// ── 1. tbl_evaluacion_induccion → tbl_evaluaciones ─────────────────────────
if (tableExists($pdo, $db, 'tbl_evaluacion_induccion') && !tableExists($pdo, $db, 'tbl_evaluaciones')) {
    $pdo->exec("RENAME TABLE tbl_evaluacion_induccion TO tbl_evaluaciones");
    echo "OK: tbl_evaluacion_induccion → tbl_evaluaciones\n";
} elseif (tableExists($pdo, $db, 'tbl_evaluaciones')) {
    echo "INFO: tbl_evaluaciones ya existe, se omite\n";
} else {
    echo "WARN: tbl_evaluacion_induccion no encontrada\n";
}

// ── 2. tbl_evaluacion_induccion_respuesta → tbl_evaluacion_respuestas ───────
if (tableExists($pdo, $db, 'tbl_evaluacion_induccion_respuesta') && !tableExists($pdo, $db, 'tbl_evaluacion_respuestas')) {
    $pdo->exec("RENAME TABLE tbl_evaluacion_induccion_respuesta TO tbl_evaluacion_respuestas");
    echo "OK: tbl_evaluacion_induccion_respuesta → tbl_evaluacion_respuestas\n";
} elseif (tableExists($pdo, $db, 'tbl_evaluacion_respuestas')) {
    echo "INFO: tbl_evaluacion_respuestas ya existe, se omite\n";
} else {
    echo "WARN: tbl_evaluacion_induccion_respuesta no encontrada\n";
}

// ── 3. tbl_evaluacion_sesion → tbl_evaluacion_sesiones ──────────────────────
if (tableExists($pdo, $db, 'tbl_evaluacion_sesion') && !tableExists($pdo, $db, 'tbl_evaluacion_sesiones')) {
    $pdo->exec("RENAME TABLE tbl_evaluacion_sesion TO tbl_evaluacion_sesiones");
    echo "OK: tbl_evaluacion_sesion → tbl_evaluacion_sesiones\n";
} elseif (tableExists($pdo, $db, 'tbl_evaluacion_sesiones')) {
    echo "INFO: tbl_evaluacion_sesiones ya existe, se omite\n";
} else {
    echo "WARN: tbl_evaluacion_sesion no encontrada\n";
}

// ── 4. id_cliente nullable en tbl_evaluaciones ──────────────────────────────
$col = $pdo->query("SELECT IS_NULLABLE FROM information_schema.columns
    WHERE table_schema='{$db}' AND table_name='tbl_evaluaciones' AND column_name='id_cliente'")->fetch();
if ($col && $col['IS_NULLABLE'] === 'NO') {
    $pdo->exec("ALTER TABLE tbl_evaluaciones MODIFY id_cliente INT UNSIGNED NULL DEFAULT NULL");
    echo "OK: id_cliente → NULL en tbl_evaluaciones\n";
} else {
    echo "INFO: id_cliente ya es nullable\n";
}

// ── 5. Verificación final ────────────────────────────────────────────────────
echo "\n--- Verificación ---\n";
foreach (['tbl_evaluaciones', 'tbl_evaluacion_respuestas', 'tbl_evaluacion_sesiones'] as $t) {
    $exists = tableExists($pdo, $db, $t) ? 'OK' : 'FALTA';
    $count  = $exists === 'OK' ? $pdo->query("SELECT COUNT(*) FROM {$t}")->fetchColumn() : '-';
    echo "  {$t}: {$exists} ({$count} filas)\n";
}

echo "\nMigración completada.\n";
