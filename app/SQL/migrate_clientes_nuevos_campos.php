<?php
/**
 * Migración: Agregar campos nuevos a tbl_clientes
 * Reemplaza AppSheet — campos de vendedor, contactos, documentos
 *
 * Uso: php app/SQL/migrate_clientes_nuevos_campos.php [local|production]
 * Producción: DB_PROD_PASS=xxx php app/SQL/migrate_clientes_nuevos_campos.php production
 */

$env = $argv[1] ?? 'local';

$configs = [
    'local' => [
        'host' => 'localhost',
        'user' => 'root',
        'pass' => '',
        'db'   => 'propiedad_horizontal',
        'ssl'  => false,
    ],
    'production' => [
        'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port' => 25060,
        'user' => 'cycloid_userdb',
        'pass' => getenv('DB_PROD_PASS') ?: '',
        'db'   => 'propiedad_horizontal',
        'ssl'  => true,
    ],
];

if (!isset($configs[$env])) {
    echo "Uso: php app/SQL/migrate_clientes_nuevos_campos.php [local|production]\n";
    exit(1);
}

$cfg = $configs[$env];
echo "=== Migración tbl_clientes (campos nuevos) - Entorno: {$env} ===\n\n";

if ($env === 'production' && empty($cfg['pass'])) {
    echo "ERROR: Variable DB_PROD_PASS no definida.\n";
    echo "Uso: DB_PROD_PASS=xxx php app/SQL/migrate_clientes_nuevos_campos.php production\n";
    exit(1);
}

$conn = new mysqli();

if ($cfg['ssl'] ?? false) {
    $conn->ssl_set(null, null, null, null, null);
    $conn->real_connect($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['db'], $cfg['port'] ?? 3306, null, MYSQLI_CLIENT_SSL);
} else {
    $conn->real_connect($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['db'], $cfg['port'] ?? 3306);
}

if ($conn->connect_error) {
    echo "ERROR de conexión: " . $conn->connect_error . "\n";
    exit(1);
}

echo "Conectado a {$cfg['db']}@{$cfg['host']}\n\n";

$ok = 0;
$errors = 0;

// Columnas a agregar con su definición y posición
$columns = [
    'correo_consejo_admon'        => "VARCHAR(255) NULL AFTER correo_cliente",
    'persona_contacto_operaciones' => "VARCHAR(255) NULL AFTER persona_contacto_compras",
    'persona_contacto_pagos'       => "VARCHAR(255) NULL AFTER persona_contacto_operaciones",
    'horarios_y_dias'              => "TEXT NULL AFTER persona_contacto_pagos",
    'vendedor'                     => "VARCHAR(255) NULL AFTER id_consultor",
    'plazo_cartera'                => "VARCHAR(100) NULL AFTER vendedor",
    'fecha_cierre_facturacion'     => "INT NULL AFTER plazo_cartera",
    'fecha_asignacion_cronograma'  => "DATE NULL AFTER fecha_cierre_facturacion",
    'rut'                          => "VARCHAR(255) NULL AFTER logo",
    'camara_comercio'              => "VARCHAR(255) NULL AFTER rut",
    'cedula_rep_legal_doc'         => "VARCHAR(255) NULL AFTER camara_comercio",
    'oferta_comercial'             => "VARCHAR(255) NULL AFTER cedula_rep_legal_doc",
];

foreach ($columns as $colName => $colDef) {
    // Verificar si la columna ya existe
    $check = $conn->query("SHOW COLUMNS FROM tbl_clientes LIKE '{$colName}'");
    if ($check && $check->num_rows > 0) {
        echo "[SKIP] Columna '{$colName}' ya existe\n";
        $ok++;
        continue;
    }

    $sql = "ALTER TABLE tbl_clientes ADD COLUMN {$colName} {$colDef}";
    if ($conn->query($sql)) {
        echo "[OK] Columna '{$colName}' agregada\n";
        $ok++;
    } else {
        echo "[ERROR] No se pudo agregar '{$colName}': " . $conn->error . "\n";
        $errors++;
    }
}

// Verificar estructura final
echo "\n--- Columnas actuales de tbl_clientes ---\n";
$result = $conn->query("SHOW COLUMNS FROM tbl_clientes");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "  {$row['Field']} ({$row['Type']})\n";
    }
}

echo "\n=== Resultado: {$ok} OK, {$errors} errores ===\n";
$conn->close();

exit($errors > 0 ? 1 : 0);
