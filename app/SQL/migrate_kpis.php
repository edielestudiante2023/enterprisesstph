<?php
/**
 * Migración: Crear tablas KPI para los 4 programas
 * - tbl_kpi_limpieza
 * - tbl_kpi_residuos
 * - tbl_kpi_plagas
 * - tbl_kpi_agua_potable
 *
 * Uso: DB_PROD_PASS=xxx php app/SQL/migrate_kpis.php [production]
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
    'tbl_kpi_limpieza' => [
        'fk_cliente'   => 'fk_kpi_limp_cliente',
        'fk_consultor' => 'fk_kpi_limp_consultor',
    ],
    'tbl_kpi_residuos' => [
        'fk_cliente'   => 'fk_kpi_res_cliente',
        'fk_consultor' => 'fk_kpi_res_consultor',
    ],
    'tbl_kpi_plagas' => [
        'fk_cliente'   => 'fk_kpi_plag_cliente',
        'fk_consultor' => 'fk_kpi_plag_consultor',
    ],
    'tbl_kpi_agua_potable' => [
        'fk_cliente'   => 'fk_kpi_agua_cliente',
        'fk_consultor' => 'fk_kpi_agua_consultor',
    ],
];

foreach ($tables as $tableName => $fks) {
    echo "--- $tableName ---\n";

    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE '$tableName'");
    if ($stmt->rowCount() > 0) {
        echo "  Ya existe, saltando.\n\n";
        continue;
    }

    $sql = "CREATE TABLE `$tableName` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `id_cliente` INT NOT NULL,
        `id_consultor` INT NOT NULL,
        `fecha_inspeccion` DATE NOT NULL,
        `nombre_responsable` VARCHAR(255) DEFAULT NULL,
        `indicador` VARCHAR(500) NOT NULL,
        `cumplimiento` DECIMAL(5,2) NOT NULL DEFAULT 0,
        `registro_formato_1` VARCHAR(500) DEFAULT NULL,
        `registro_formato_2` VARCHAR(500) DEFAULT NULL,
        `registro_formato_3` VARCHAR(500) DEFAULT NULL,
        `registro_formato_4` VARCHAR(500) DEFAULT NULL,
        `ruta_pdf` VARCHAR(500) DEFAULT NULL,
        `estado` ENUM('borrador','completo') NOT NULL DEFAULT 'borrador',
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT `{$fks['fk_cliente']}` FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes`(`id_cliente`) ON DELETE CASCADE,
        CONSTRAINT `{$fks['fk_consultor']}` FOREIGN KEY (`id_consultor`) REFERENCES `tbl_consultor`(`id_consultor`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $pdo->exec($sql);
    echo "  Tabla creada OK.\n\n";
}

echo "=== Migración KPIs completada ===\n";
