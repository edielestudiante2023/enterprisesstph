<?php
/**
 * Migración: Agregar columnas de operacionalización a tablas KPI
 * - valor_numerador, valor_denominador, calificacion_cualitativa, observaciones
 *
 * Uso: DB_PROD_PASS=xxx php app/SQL/migrate_kpi_operacionalizacion.php [production]
 */

$isProduction = ($argv[1] ?? '') === 'production';

if ($isProduction) {
    $host     = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port     = 25060;
    $dbname   = 'propiedad_horizontal';
    $user     = 'cycloid_userdb';
    $password = getenv('DB_PROD_PASS');
    if (!$password) {
        echo "ERROR: DB_PROD_PASS env var required for production.\n";
        exit(1);
    }
    $ssl = true;
    echo "=== PRODUCCIÓN ===\n";
} else {
    $host     = '127.0.0.1';
    $port     = 3306;
    $dbname   = 'propiedad_horizontal';
    $user     = 'root';
    $password = '';
    $ssl      = false;
    echo "=== LOCAL ===\n";
}

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
if ($ssl) {
    $options[PDO::MYSQL_ATTR_SSL_CA] = true;
    $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
}

try {
    $pdo = new PDO($dsn, $user, $password, $options);
    echo "Conectado a $dbname en $host:$port\n\n";
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage() . "\n";
    exit(1);
}

$tables = [
    'tbl_kpi_limpieza',
    'tbl_kpi_residuos',
    'tbl_kpi_plagas',
    'tbl_kpi_agua_potable',
];

$columns = [
    'valor_numerador'          => 'INT DEFAULT NULL AFTER `cumplimiento`',
    'valor_denominador'        => 'INT DEFAULT NULL AFTER `valor_numerador`',
    'calificacion_cualitativa' => "VARCHAR(20) DEFAULT NULL AFTER `valor_denominador`",
    'observaciones'            => 'TEXT DEFAULT NULL AFTER `calificacion_cualitativa`',
];

foreach ($tables as $table) {
    echo "--- $table ---\n";

    // Verificar que la tabla existe
    $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
    if ($stmt->rowCount() === 0) {
        echo "  ADVERTENCIA: tabla $table no existe, saltando.\n\n";
        continue;
    }

    foreach ($columns as $colName => $colDef) {
        $check = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
        $check->execute([$colName]);
        if ($check->rowCount() > 0) {
            echo "  Columna '$colName' ya existe, saltando.\n";
            continue;
        }

        $sql = "ALTER TABLE `$table` ADD COLUMN `$colName` $colDef";
        $pdo->exec($sql);
        echo "  Columna '$colName' agregada OK.\n";
    }

    echo "\n";
}

echo "=== Migración operacionalización KPIs completada ===\n";
