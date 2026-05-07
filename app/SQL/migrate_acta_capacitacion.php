<?php
/**
 * Migración: Crear tablas para módulo Acta de Capacitación
 *
 * Crea:
 *   - tbl_acta_capacitacion              (incluye columna token_inscripcion para QR)
 *   - tbl_acta_capacitacion_asistente    (FK ON DELETE CASCADE a tbl_acta_capacitacion)
 *
 * Uso:
 *   php app/SQL/migrate_acta_capacitacion.php                    # LOCAL
 *   DB_PROD_PASS=xxx php app/SQL/migrate_acta_capacitacion.php production
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
        'tbl_acta_capacitacion' => "
            CREATE TABLE IF NOT EXISTS `tbl_acta_capacitacion` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `id_cliente` INT NOT NULL,
                `id_comite` INT NULL DEFAULT NULL COMMENT 'Opcional: NULL si consultor crea sin comité',
                `creado_por_tipo` ENUM('miembro','consultor') NOT NULL,
                `id_miembro` INT NULL DEFAULT NULL,
                `id_consultor` INT NULL DEFAULT NULL,

                `tema` VARCHAR(255) NOT NULL,
                `fecha_capacitacion` DATE NOT NULL,
                `hora_inicio` TIME NULL,
                `hora_fin` TIME NULL,
                `dictada_por` ENUM('ARL','Consultor','Empresa','Otro') NOT NULL DEFAULT 'ARL',
                `nombre_capacitador` VARCHAR(200) NULL,
                `entidad_capacitadora` VARCHAR(200) NULL,
                `modalidad` ENUM('virtual','presencial','mixta') NOT NULL DEFAULT 'virtual',
                `enlace_grabacion` VARCHAR(500) NULL,
                `objetivos` TEXT NULL,
                `contenido` TEXT NULL,
                `observaciones` TEXT NULL,

                `ruta_pdf` VARCHAR(255) NULL,

                `token_inscripcion` VARCHAR(64) NULL DEFAULT NULL,

                `estado` ENUM('borrador','esperando_firmas','completo') NOT NULL DEFAULT 'borrador',
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

                INDEX `idx_acta_cap_cliente` (`id_cliente`),
                INDEX `idx_acta_cap_comite` (`id_comite`),
                INDEX `idx_acta_cap_fecha` (`fecha_capacitacion`),
                INDEX `idx_acta_cap_estado` (`estado`),
                INDEX `idx_acta_cap_creador` (`creado_por_tipo`, `id_miembro`, `id_consultor`),
                INDEX `idx_token_inscripcion` (`token_inscripcion`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        'tbl_acta_capacitacion_asistente' => "
            CREATE TABLE IF NOT EXISTS `tbl_acta_capacitacion_asistente` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `id_acta_capacitacion` INT NOT NULL,

                `nombre_completo` VARCHAR(200) NOT NULL,
                `tipo_documento` ENUM('CC','CE','PA','TI','NIT') DEFAULT 'CC',
                `numero_documento` VARCHAR(20) NULL,
                `cargo` VARCHAR(150) NULL,
                `area_dependencia` VARCHAR(150) NULL,
                `email` VARCHAR(150) NULL,
                `celular` VARCHAR(30) NULL,

                `token_firma` VARCHAR(64) NULL DEFAULT NULL,
                `token_expiracion` DATETIME NULL DEFAULT NULL,
                `firma_path` VARCHAR(255) NULL DEFAULT NULL,
                `firmado_at` DATETIME NULL DEFAULT NULL,

                `orden` INT NOT NULL DEFAULT 1,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

                CONSTRAINT `fk_asistente_acta_cap`
                    FOREIGN KEY (`id_acta_capacitacion`) REFERENCES `tbl_acta_capacitacion`(`id`)
                    ON DELETE CASCADE ON UPDATE CASCADE,

                INDEX `idx_asistente_acta` (`id_acta_capacitacion`),
                UNIQUE KEY `uniq_token_firma` (`token_firma`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
    ];

    foreach ($sqls as $tableName => $sql) {
        $pdo->exec($sql);
        echo "OK: CREATE TABLE IF NOT EXISTS {$tableName}\n";
    }

    echo "\n=== Verificación ===\n";
    foreach (array_keys($sqls) as $tableName) {
        $stmt = $pdo->query("SHOW COLUMNS FROM `{$tableName}`");
        $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "\n{$tableName} (" . count($cols) . " columnas):\n";
        foreach ($cols as $c) {
            echo "  - {$c['Field']} ({$c['Type']})" . ($c['Null'] === 'NO' ? ' NOT NULL' : '') . "\n";
        }
    }

    echo "\nMigración completada.\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
