<?php
/**
 * Migración de URLs de uploads en base de datos
 *
 * Cambia las rutas de /uploads/ a /serve-file/ en todas las tablas relevantes.
 *
 * USO:
 *   LOCAL:      php app/SQL/migrate_uploads_urls.php local
 *   PRODUCCIÓN: DB_PROD_PASS=xxx php app/SQL/migrate_uploads_urls.php production
 *
 * SIEMPRE probar en LOCAL primero.
 */

$env = $argv[1] ?? 'local';

if ($env === 'production') {
    $pass = getenv('DB_PROD_PASS');
    if (!$pass) {
        die("ERROR: Variable DB_PROD_PASS no definida.\nUso: DB_PROD_PASS=xxx php " . $argv[0] . " production\n");
    }
    $host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port = 25060;
    $user = 'cycloid_userdb';
    $db   = 'propiedad_horizontal';

    $mysqli = new mysqli();
    $mysqli->ssl_set(null, null, null, null, null);
    $mysqli->real_connect($host, $user, $pass, $db, $port, null, MYSQLI_CLIENT_SSL);
} else {
    $host = 'localhost';
    $port = 3306;
    $user = 'root';
    $pass = '';
    $db   = 'propiedad_horizontal';

    $mysqli = new mysqli($host, $user, $pass, $db, $port);
}

if ($mysqli->connect_error) {
    die("Error de conexión: " . $mysqli->connect_error . "\n");
}

$mysqli->set_charset('utf8mb4');

echo "=== Migración de URLs de uploads ===\n";
echo "Entorno: $env\n";
echo "Base de datos: $db\n\n";

// Definir todas las migraciones
$migrations = [
    // tbl_reporte.enlace — URLs completas con dominio
    [
        'table' => 'tbl_reporte',
        'field' => 'enlace',
        'old'   => '/uploads/',
        'new'   => '/serve-file/',
        'where' => "enlace LIKE '%/uploads/%' AND enlace NOT LIKE '%/uploads/logo%'",
    ],
    // tbl_contratos.ruta_pdf_contrato — rutas relativas
    [
        'table' => 'tbl_contratos',
        'field' => 'ruta_pdf_contrato',
        'old'   => 'uploads/contratos/',
        'new'   => 'serve-file/contratos/',
        'where' => "ruta_pdf_contrato LIKE 'uploads/contratos/%'",
    ],
    // tbl_contratos.firma_cliente_imagen — rutas relativas
    [
        'table' => 'tbl_contratos',
        'field' => 'firma_cliente_imagen',
        'old'   => 'uploads/firmas/',
        'new'   => 'serve-file/firmas/',
        'where' => "firma_cliente_imagen LIKE 'uploads/firmas/%'",
    ],
    // tbl_matrices.enlace — rutas relativas
    [
        'table' => 'tbl_matrices',
        'field' => 'enlace',
        'old'   => 'uploads/matrices/',
        'new'   => 'serve-file/matrices/',
        'where' => "enlace LIKE '%uploads/matrices/%'",
    ],
];

// Modo dry-run primero
echo "--- DRY RUN (sin cambios) ---\n\n";

foreach ($migrations as $m) {
    $countQuery = "SELECT COUNT(*) as total FROM {$m['table']} WHERE {$m['where']}";
    $result = $mysqli->query($countQuery);

    if (!$result) {
        echo "SKIP: {$m['table']}.{$m['field']} — Tabla o campo no existe ({$mysqli->error})\n";
        continue;
    }

    $row = $result->fetch_assoc();
    echo "{$m['table']}.{$m['field']}: {$row['total']} registros a migrar\n";

    // Mostrar ejemplos
    $exQuery = "SELECT {$m['field']} FROM {$m['table']} WHERE {$m['where']} LIMIT 3";
    $exResult = $mysqli->query($exQuery);
    if ($exResult) {
        while ($ex = $exResult->fetch_assoc()) {
            $old = $ex[$m['field']];
            $new = str_replace($m['old'], $m['new'], $old);
            echo "  ANTES: $old\n";
            echo "  DESP:  $new\n";
        }
    }
    echo "\n";
}

// Pedir confirmación
echo "¿Ejecutar la migración? (escribe 'SI' para confirmar): ";
$confirmation = trim(fgets(STDIN));

if ($confirmation !== 'SI') {
    echo "Migración cancelada.\n";
    exit(0);
}

// Ejecutar migraciones
echo "\n--- EJECUTANDO MIGRACIONES ---\n\n";

$totalMigrated = 0;

foreach ($migrations as $m) {
    $updateQuery = "UPDATE {$m['table']} SET {$m['field']} = REPLACE({$m['field']}, '{$m['old']}', '{$m['new']}') WHERE {$m['where']}";

    $result = $mysqli->query($updateQuery);

    if (!$result) {
        echo "ERROR en {$m['table']}.{$m['field']}: {$mysqli->error}\n";
        continue;
    }

    $affected = $mysqli->affected_rows;
    $totalMigrated += $affected;
    echo "{$m['table']}.{$m['field']}: $affected registros actualizados\n";
}

echo "\n=== MIGRACIÓN COMPLETADA ===\n";
echo "Total de registros actualizados: $totalMigrated\n";

$mysqli->close();
