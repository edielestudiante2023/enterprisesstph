<?php
/**
 * Script CLI para crear tablas del modulo Inspeccion Productos Quimicos (Fase 11).
 * Uso: php migrate_inspeccion_productos_quimicos.php [local|production]
 *
 * Patron HIBRIDO (doc: docs/14_PATRON_INSPECCION_HIBRIDO.md)
 *
 * Crea:
 *   - tbl_inspeccion_productos_quimicos (master con 17 cal_item_NN ENUM)
 *   - tbl_inspeccion_productos_quimicos_foto (detalle de N fotos con observacion)
 */

if (php_sapi_name() !== 'cli') {
    die('Este script solo puede ejecutarse desde la linea de comandos.');
}

$env = $argv[1] ?? 'local';

if ($env === 'local') {
    $config = [
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'user'     => 'root',
        'password' => '',
        'database' => 'propiedad_horizontal',
        'ssl'      => false,
    ];
} elseif ($env === 'production') {
    $config = [
        'host'     => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port'     => 25060,
        'user'     => 'cycloid_userdb',
        'password' => getenv('DB_PROD_PASS') ?: '',
        'database' => 'propiedad_horizontal',
        'ssl'      => true,
    ];
} else {
    die("Uso: php migrate_inspeccion_productos_quimicos.php [local|production]\n");
}

echo "=== Migracion SQL - Modulo Inspeccion Productos Quimicos ===\n";
echo "Entorno: " . strtoupper($env) . "\n";
echo "Host: {$config['host']}:{$config['port']}\n";
echo "Database: {$config['database']}\n";
echo "---\n";

$mysqli = mysqli_init();

if ($config['ssl']) {
    $mysqli->ssl_set(null, null, null, null, null);
    $mysqli->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
}

$connected = @$mysqli->real_connect(
    $config['host'],
    $config['user'],
    $config['password'],
    $config['database'],
    $config['port'],
    null,
    $config['ssl'] ? MYSQLI_CLIENT_SSL : 0
);

if (!$connected) {
    die("ERROR de conexion: " . $mysqli->connect_error . "\n");
}

echo "Conexion exitosa.\n\n";

$success = 0;
$errors = 0;
$total = 0;

// Construir las 17 columnas cal_item_NN dinamicamente
$calCols = '';
for ($i = 1; $i <= 17; $i++) {
    $padded = str_pad($i, 2, '0', STR_PAD_LEFT);
    $calCols .= "            `cal_item_{$padded}` ENUM('C','CP','NC','NA') NULL,\n";
}

$createStatements = [
    [
        'desc' => 'CREATE TABLE tbl_inspeccion_productos_quimicos',
        'sql'  => "CREATE TABLE IF NOT EXISTS `tbl_inspeccion_productos_quimicos` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `id_cliente` INT NOT NULL,
            `id_consultor` INT NOT NULL,
            `fecha_inspeccion` DATE NOT NULL,
            `ubicacion` VARCHAR(255) NULL,
            `tiene_guadaniadora` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Toggle: si hay guadaniadora/combustible activa items 16-17',
{$calCols}            `porcentaje_cumplimiento` DECIMAL(5,2) NULL COMMENT 'Calculado al finalizar',
            `nivel_riesgo` ENUM('alto','medio','bajo') NULL COMMENT 'alto=90-100, medio=70-89, bajo=<70',
            `observaciones_finales` TEXT NULL,
            `ruta_pdf` VARCHAR(255) NULL,
            `estado` ENUM('borrador','completo') NOT NULL DEFAULT 'borrador',
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            CONSTRAINT `fk_insp_pq_cliente`
                FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes`(`id_cliente`)
                ON DELETE RESTRICT ON UPDATE CASCADE,
            CONSTRAINT `fk_insp_pq_consultor`
                FOREIGN KEY (`id_consultor`) REFERENCES `tbl_consultor`(`id_consultor`)
                ON DELETE RESTRICT ON UPDATE CASCADE,

            INDEX `idx_insp_pq_cliente` (`id_cliente`),
            INDEX `idx_insp_pq_consultor` (`id_consultor`),
            INDEX `idx_insp_pq_estado` (`estado`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ],
    [
        'desc' => 'CREATE TABLE tbl_inspeccion_productos_quimicos_foto',
        'sql'  => "CREATE TABLE IF NOT EXISTS `tbl_inspeccion_productos_quimicos_foto` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `id_inspeccion` INT NOT NULL,
            `orden` INT NOT NULL DEFAULT 0,
            `foto` VARCHAR(255) NULL,
            `observacion` TEXT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

            CONSTRAINT `fk_insp_pq_foto_insp`
                FOREIGN KEY (`id_inspeccion`) REFERENCES `tbl_inspeccion_productos_quimicos`(`id`)
                ON DELETE CASCADE ON UPDATE CASCADE,

            INDEX `idx_insp_pq_foto_insp` (`id_inspeccion`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ],
];

foreach ($createStatements as $stmt) {
    $total++;
    echo "[{$total}] {$stmt['desc']}... ";
    if ($mysqli->query($stmt['sql'])) {
        echo "OK\n";
        $success++;
    } else {
        echo "ERROR: " . $mysqli->error . "\n";
        $errors++;
    }
}

echo "\n=== RESULTADO ===\n";
echo "Exitosas: {$success}\n";
echo "Errores: {$errors}\n";
echo "Total: {$total}\n";

if ($errors === 0) {
    echo "MIGRACION COMPLETADA SIN ERRORES.\n";
} else {
    echo "HAY ERRORES - REVISAR ANTES DE CONTINUAR.\n";
}

$mysqli->close();
