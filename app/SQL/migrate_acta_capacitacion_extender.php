<?php
/**
 * Migración: Extender tbl_acta_capacitacion con campos para
 *   - vínculo a cronograma de capacitación
 *   - tipo_charla (para detectar inducción y disparar FT-SST-003)
 *   - 3 fotos
 *   - PDF de responsabilidades
 *
 * Idempotente: detecta columnas existentes vía INFORMATION_SCHEMA y
 * solo agrega las que falten. Compatible con MySQL 5.7+ (sin IF NOT EXISTS).
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

    // Helper: ¿existe la columna?
    $colExists = function (string $tabla, string $col) use ($pdo, $db): bool {
        $stmt = $pdo->prepare("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :tabla AND COLUMN_NAME = :col");
        $stmt->execute([':db' => $db, ':tabla' => $tabla, ':col' => $col]);
        return (bool) $stmt->fetchColumn();
    };

    // Helper: ¿existe el índice?
    $idxExists = function (string $tabla, string $idx) use ($pdo, $db): bool {
        $stmt = $pdo->prepare("SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
            WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :tabla AND INDEX_NAME = :idx LIMIT 1");
        $stmt->execute([':db' => $db, ':tabla' => $tabla, ':idx' => $idx]);
        return (bool) $stmt->fetchColumn();
    };

    $tabla = 'tbl_acta_capacitacion';

    $columnas = [
        'tipo_charla'                => "ENUM('induccion_reinduccion','reunion','charla','capacitacion','otros_temas') NOT NULL DEFAULT 'capacitacion' AFTER modalidad",
        'id_cronograma_capacitacion' => "INT NULL DEFAULT NULL AFTER tipo_charla",
        'foto_capacitacion'          => "VARCHAR(255) NULL AFTER ruta_pdf",
        'foto_otros_1'               => "VARCHAR(255) NULL AFTER foto_capacitacion",
        'foto_otros_2'               => "VARCHAR(255) NULL AFTER foto_otros_1",
        'ruta_pdf_responsabilidades' => "VARCHAR(255) NULL AFTER foto_otros_2",
    ];

    foreach ($columnas as $col => $def) {
        if ($colExists($tabla, $col)) {
            echo "SKIP: columna {$col} ya existe\n";
            continue;
        }
        $sql = "ALTER TABLE `{$tabla}` ADD COLUMN `{$col}` {$def}";
        $pdo->exec($sql);
        echo "OK: ADD COLUMN {$col}\n";
    }

    // Índice
    $idx = 'idx_acta_cap_cronograma';
    if ($idxExists($tabla, $idx)) {
        echo "SKIP: índice {$idx} ya existe\n";
    } else {
        $pdo->exec("ALTER TABLE `{$tabla}` ADD INDEX `{$idx}` (id_cronograma_capacitacion)");
        echo "OK: ADD INDEX {$idx}\n";
    }

    echo "\n=== Verificación columnas nuevas ===\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM `{$tabla}`");
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
