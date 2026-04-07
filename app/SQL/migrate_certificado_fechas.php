<?php
/**
 * Migración: Agregar fecha_inspeccion y nueva_fecha_vencimiento a tbl_certificado_servicio
 *
 * Uso:
 *   LOCAL:       php app/SQL/migrate_certificado_fechas.php
 *   PRODUCCIÓN:  DB_PROD_PASS=xxx php app/SQL/migrate_certificado_fechas.php production
 */

$env = $argv[1] ?? 'local';

if ($env === 'production') {
    $pass = getenv('DB_PROD_PASS');
    if (!$pass) {
        echo "ERROR: Variable DB_PROD_PASS no definida.\n";
        exit(1);
    }
    $host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port = 25060;
    $db   = 'propiedad_horizontal';
    $user = 'cycloid_userdb';
    $ssl  = true;
    echo "=== PRODUCCIÓN ===\n";
} else {
    $host = '127.0.0.1';
    $port = 3306;
    $db   = 'propiedad_horizontal';
    $user = 'root';
    $pass = '';
    $ssl  = false;
    echo "=== LOCAL ===\n";
}

$dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
$opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
if ($ssl) {
    $opts[PDO::MYSQL_ATTR_SSL_CA] = true;
    $opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
}

try {
    $pdo = new PDO($dsn, $user, $pass, $opts);
    echo "Conectado a {$db}@{$host}:{$port}\n";
} catch (PDOException $e) {
    echo "ERROR conexión: " . $e->getMessage() . "\n";
    exit(1);
}

// ---------- Crear tabla si no existe ----------

$checkTable = $pdo->prepare(
    "SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
     WHERE TABLE_SCHEMA = :db AND TABLE_NAME = 'tbl_certificado_servicio'"
);
$checkTable->execute(['db' => $db]);

if ($checkTable->fetchColumn() == 0) {
    $pdo->exec("
        CREATE TABLE tbl_certificado_servicio (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_cliente INT NOT NULL,
            id_mantenimiento INT NOT NULL,
            fecha_servicio DATE NOT NULL,
            fecha_inspeccion DATE NULL,
            nueva_fecha_vencimiento DATE NULL,
            archivo VARCHAR(500) NULL,
            observaciones TEXT NULL,
            id_consultor INT NULL,
            id_vencimiento INT NULL,
            created_at DATETIME NULL,
            INDEX idx_cliente (id_cliente),
            INDEX idx_mantenimiento (id_mantenimiento),
            INDEX idx_vencimiento (id_vencimiento)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  Tabla 'tbl_certificado_servicio' creada OK.\n";
} else {
    echo "  Tabla 'tbl_certificado_servicio' ya existe.\n";

    // ---------- Verificar y agregar columnas nuevas ----------
    $columnas = [
        'fecha_inspeccion'        => "ALTER TABLE tbl_certificado_servicio ADD COLUMN fecha_inspeccion DATE NULL AFTER fecha_servicio",
        'nueva_fecha_vencimiento' => "ALTER TABLE tbl_certificado_servicio ADD COLUMN nueva_fecha_vencimiento DATE NULL AFTER fecha_inspeccion",
    ];

    foreach ($columnas as $col => $sql) {
        $check = $pdo->prepare(
            "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = :db AND TABLE_NAME = 'tbl_certificado_servicio' AND COLUMN_NAME = :col"
        );
        $check->execute(['db' => $db, 'col' => $col]);

        if ($check->fetchColumn() > 0) {
            echo "  Columna '{$col}' ya existe — omitida.\n";
        } else {
            $pdo->exec($sql);
            echo "  Columna '{$col}' agregada OK.\n";
        }
    }
}

echo "\nMigración completada.\n";
