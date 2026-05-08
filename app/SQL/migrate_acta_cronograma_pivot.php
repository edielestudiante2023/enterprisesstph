<?php
/**
 * Migración: Tabla puente N:M entre actas de capacitación y cronogramas.
 *
 * Razón: una sola acta (1 jornada del consultor) puede dictar N capacitaciones
 * del cronograma. Antes era 1:1 y se generaban N actas hermanas (saturaba el listado).
 * Ahora es 1:N → 1 acta = 1 borrador, al finalizar genera N PDFs.
 *
 * Schema:
 *   tbl_acta_cronograma:
 *     - id_acta_cronograma  PK
 *     - id_acta             FK → tbl_acta_capacitacion (CASCADE)
 *     - id_cronograma       (apunta a tbl_cronog_capacitacion.id_cronograma_capacitacion)
 *     - objetivo_ia         TEXT  (generado al finalizar)
 *     - ruta_pdf            VARCHAR (PDF específico de este cronograma+acta)
 *     - promedio_calificaciones DECIMAL(5,2) (puntaje IA específico)
 *     - numero_evaluados    INT (count match IA específico)
 *     - UNIQUE (id_acta, id_cronograma)
 *
 * Idempotente.
 *
 * Uso:
 *   php app/SQL/migrate_acta_cronograma_pivot.php                    # LOCAL
 *   DB_PROD_PASS=xxx php app/SQL/migrate_acta_cronograma_pivot.php production
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

    // ¿Existe la tabla?
    $existe = (bool) $pdo->query("SELECT 1 FROM INFORMATION_SCHEMA.TABLES
        WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = 'tbl_acta_cronograma'")->fetchColumn();

    if ($existe) {
        echo "SKIP: tabla tbl_acta_cronograma ya existe\n";
    } else {
        $pdo->exec("
            CREATE TABLE `tbl_acta_cronograma` (
                `id_acta_cronograma` INT AUTO_INCREMENT PRIMARY KEY,
                `id_acta` INT NOT NULL,
                `id_cronograma` INT NOT NULL,
                `objetivo_ia` TEXT NULL,
                `ruta_pdf` VARCHAR(255) NULL,
                `promedio_calificaciones` DECIMAL(5,2) NULL DEFAULT NULL,
                `numero_evaluados` INT NULL DEFAULT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY `uk_acta_cronograma` (`id_acta`, `id_cronograma`),
                INDEX `idx_acta` (`id_acta`),
                INDEX `idx_cronograma` (`id_cronograma`),
                CONSTRAINT `fk_actacron_acta` FOREIGN KEY (`id_acta`)
                    REFERENCES `tbl_acta_capacitacion`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "OK: tabla tbl_acta_cronograma creada\n";
    }

    // Verificación
    echo "\n=== Verificación ===\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM `tbl_acta_cronograma`");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $c) {
        echo "  ✓ {$c['Field']} ({$c['Type']})" . ($c['Null'] === 'NO' ? ' NOT NULL' : '') . "\n";
    }

    echo "\nMigración completada.\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
