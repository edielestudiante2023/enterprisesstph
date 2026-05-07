<?php
/**
 * Migración: Extender tbl_acta_capacitacion con campos para
 *   - vínculo a cronograma de capacitación
 *   - tipo_charla (para detectar inducción y disparar FT-SST-003)
 *   - 3 fotos
 *   - PDF de responsabilidades
 *
 * Uso:
 *   php app/SQL/migrate_acta_capacitacion_extender.php                    # LOCAL
 *   DB_PROD_PASS=xxx php app/SQL/migrate_acta_capacitacion_extender.php production
 */

$env = $argv[1] ?? 'local';

if ($env === 'production') {
    $host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port = 25060;
    $db   = 'propiedad_horizontal';
    $user = 'cycloid_userdb';
    $pass = getenv('DB_PROD_PASS');
    $ssl  = true;
    if (!$pass) {
        echo "ERROR: variable de entorno DB_PROD_PASS no está definida.\n";
        exit(1);
    }
} else {
    $host = '127.0.0.1';
    $port = 3306;
    $db   = 'propiedad_horizontal';
    $user = 'root';
    $pass = '';
    $ssl  = false;
}

$dsn  = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
$opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
if ($ssl) {
    $opts[PDO::MYSQL_ATTR_SSL_CA] = true;
    $opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
}

try {
    $pdo = new PDO($dsn, $user, $pass, $opts);
    echo "Conectado a [{$env}] {$db}\n\n";

    $sqls = [
        "ALTER TABLE tbl_acta_capacitacion ADD COLUMN IF NOT EXISTS tipo_charla ENUM('induccion_reinduccion','reunion','charla','capacitacion','otros_temas') NOT NULL DEFAULT 'capacitacion' AFTER modalidad",
        "ALTER TABLE tbl_acta_capacitacion ADD COLUMN IF NOT EXISTS id_cronograma_capacitacion INT NULL DEFAULT NULL AFTER tipo_charla",
        "ALTER TABLE tbl_acta_capacitacion ADD COLUMN IF NOT EXISTS foto_capacitacion VARCHAR(255) NULL AFTER ruta_pdf",
        "ALTER TABLE tbl_acta_capacitacion ADD COLUMN IF NOT EXISTS foto_otros_1 VARCHAR(255) NULL AFTER foto_capacitacion",
        "ALTER TABLE tbl_acta_capacitacion ADD COLUMN IF NOT EXISTS foto_otros_2 VARCHAR(255) NULL AFTER foto_otros_1",
        "ALTER TABLE tbl_acta_capacitacion ADD COLUMN IF NOT EXISTS ruta_pdf_responsabilidades VARCHAR(255) NULL AFTER foto_otros_2",
    ];

    foreach ($sqls as $sql) {
        $pdo->exec($sql);
        echo "OK: " . substr($sql, 0, 90) . "...\n";
    }

    // Índice — MySQL no soporta IF NOT EXISTS para INDEX en todas las versiones
    try {
        $pdo->exec("ALTER TABLE tbl_acta_capacitacion ADD INDEX idx_acta_cap_cronograma (id_cronograma_capacitacion)");
        echo "OK: ADD INDEX idx_acta_cap_cronograma\n";
    } catch (\PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false || strpos($e->getMessage(), '1061') !== false) {
            echo "SKIP: índice idx_acta_cap_cronograma ya existe\n";
        } else {
            throw $e;
        }
    }

    echo "\n=== Verificación columnas nuevas ===\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM `tbl_acta_capacitacion`");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $c) {
        $nuevas = ['tipo_charla','id_cronograma_capacitacion','foto_capacitacion','foto_otros_1','foto_otros_2','ruta_pdf_responsabilidades'];
        if (in_array($c['Field'], $nuevas, true)) {
            echo "  ✓ {$c['Field']} ({$c['Type']})" . ($c['Null'] === 'NO' ? ' NOT NULL' : '') . "\n";
        }
    }

    echo "\nMigración completada.\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
