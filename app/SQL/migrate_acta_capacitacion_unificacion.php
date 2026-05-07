<?php
/**
 * Migración: Unificación de actas de capacitación con cronograma + asistencia + evaluación.
 *
 * Cambios:
 *   1. Agrega columnas a tbl_acta_capacitacion:
 *      - numero_programados        INT NULL          (asistentes esperados, digitado)
 *      - numero_evaluados          INT NULL          (cuántos respondieron evaluación)
 *      - promedio_calificaciones   DECIMAL(5,2) NULL (puntaje IA o manual)
 *   2. Convierte dictada_por de ENUM a VARCHAR(100) para permitir texto libre.
 *
 * Idempotente: detecta columnas existentes vía INFORMATION_SCHEMA.
 *
 * Uso:
 *   php app/SQL/migrate_acta_capacitacion_unificacion.php                    # LOCAL
 *   DB_PROD_PASS=xxx php app/SQL/migrate_acta_capacitacion_unificacion.php production
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

    $tabla = 'tbl_acta_capacitacion';

    $colExists = function (string $col) use ($pdo, $db, $tabla): bool {
        $stmt = $pdo->prepare("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :tabla AND COLUMN_NAME = :col");
        $stmt->execute([':db' => $db, ':tabla' => $tabla, ':col' => $col]);
        return (bool) $stmt->fetchColumn();
    };

    $colType = function (string $col) use ($pdo, $db, $tabla): ?string {
        $stmt = $pdo->prepare("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :tabla AND COLUMN_NAME = :col");
        $stmt->execute([':db' => $db, ':tabla' => $tabla, ':col' => $col]);
        $r = $stmt->fetchColumn();
        return $r !== false ? (string) $r : null;
    };

    // 1. Agregar 3 columnas nuevas
    $columnas = [
        'numero_programados'      => "INT NULL DEFAULT NULL AFTER observaciones",
        'numero_evaluados'        => "INT NULL DEFAULT NULL AFTER numero_programados",
        'promedio_calificaciones' => "DECIMAL(5,2) NULL DEFAULT NULL AFTER numero_evaluados",
    ];

    foreach ($columnas as $col => $def) {
        if ($colExists($col)) {
            echo "SKIP: columna {$col} ya existe\n";
            continue;
        }
        $pdo->exec("ALTER TABLE `{$tabla}` ADD COLUMN `{$col}` {$def}");
        echo "OK: ADD COLUMN {$col}\n";
    }

    // 2. Cambiar dictada_por de ENUM a VARCHAR(100)
    $tipoActual = $colType('dictada_por');
    if ($tipoActual && stripos($tipoActual, 'enum') === 0) {
        echo "Detectado dictada_por como ENUM ({$tipoActual}). Convirtiendo a VARCHAR(100)...\n";
        $pdo->exec("ALTER TABLE `{$tabla}` MODIFY COLUMN `dictada_por` VARCHAR(100) NOT NULL DEFAULT 'ARL'");
        echo "OK: dictada_por convertido a VARCHAR(100)\n";
    } else {
        echo "SKIP: dictada_por ya es {$tipoActual} (no es ENUM)\n";
    }

    // Verificación
    echo "\n=== Verificación ===\n";
    foreach (['numero_programados','numero_evaluados','promedio_calificaciones','dictada_por'] as $col) {
        $tipo = $colType($col);
        echo "  ✓ {$col}: {$tipo}\n";
    }

    echo "\nMigración completada.\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
