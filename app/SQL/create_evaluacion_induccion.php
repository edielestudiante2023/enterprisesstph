<?php
/**
 * Migración: Módulo Evaluación de Inducción SST
 * Crear tablas tbl_evaluacion_induccion + tbl_evaluacion_induccion_respuesta
 * Agregar columnas a tbl_asistencia_induccion y tbl_reporte_capacitacion
 *
 * Uso: DB_PROD_PASS=xxx php create_evaluacion_induccion.php [production]
 */

$env = $argv[1] ?? 'local';

if ($env === 'production') {
    $host   = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port   = 25060;
    $user   = 'cycloid_userdb';
    $pass   = getenv('DB_PROD_PASS');
    $dbname = 'propiedad_horizontal';
} else {
    $host   = '127.0.0.1';
    $port   = 3306;
    $user   = 'root';
    $pass   = '';
    $dbname = 'propiedad_horizontal';
}

$conn = new mysqli($host, $user, $pass, $dbname, $port);
if ($conn->connect_error) {
    die('Conexión fallida: ' . $conn->connect_error . PHP_EOL);
}
$conn->set_charset('utf8mb4');
echo "Conectado a [$env] $dbname" . PHP_EOL;

$sqls = [];

// ── 1. tbl_evaluacion_induccion ──────────────────────────────────────────────
$sqls[] = "
CREATE TABLE IF NOT EXISTS `tbl_evaluacion_induccion` (
  `id`                       INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_asistencia_induccion`  INT UNSIGNED NULL,
  `id_cliente`               INT UNSIGNED NOT NULL,
  `titulo`                   VARCHAR(255) NOT NULL DEFAULT 'Evaluación Inducción SST',
  `token`                    VARCHAR(64)  NOT NULL,
  `estado`                   ENUM('activo','cerrado') NOT NULL DEFAULT 'activo',
  `created_at`               DATETIME NULL,
  `updated_at`               DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

// ── 2. tbl_evaluacion_induccion_respuesta ────────────────────────────────────
$sqls[] = "
CREATE TABLE IF NOT EXISTS `tbl_evaluacion_induccion_respuesta` (
  `id`                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_evaluacion`       INT UNSIGNED NOT NULL,
  `nombre`              VARCHAR(255) NOT NULL,
  `cedula`              VARCHAR(30)  NOT NULL,
  `whatsapp`            VARCHAR(30)  NOT NULL DEFAULT '',
  `empresa_contratante` VARCHAR(255) NOT NULL DEFAULT '',
  `cargo`               VARCHAR(100) NOT NULL DEFAULT '',
  `id_cliente_conjunto` INT UNSIGNED NULL,
  `acepta_tratamiento`  TINYINT(1)   NOT NULL DEFAULT 0,
  `respuestas`          JSON         NULL,
  `calificacion`        DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `created_at`          DATETIME NULL,
  `updated_at`          DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY `idx_id_evaluacion` (`id_evaluacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

// ── 3. Columnas en tbl_asistencia_induccion ──────────────────────────────────
$sqls[] = "ALTER TABLE `tbl_asistencia_induccion`
  ADD COLUMN IF NOT EXISTS `evaluacion_habilitada` TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `evaluacion_token`      VARCHAR(64) NULL;
";

// ── 4. Columna en tbl_reporte_capacitacion ───────────────────────────────────
$sqls[] = "ALTER TABLE `tbl_reporte_capacitacion`
  ADD COLUMN IF NOT EXISTS `mostrar_evaluacion_induccion` TINYINT(1) NOT NULL DEFAULT 0;
";

foreach ($sqls as $i => $sql) {
    if ($conn->query($sql)) {
        echo "OK [" . ($i + 1) . "]" . PHP_EOL;
    } else {
        echo "ERROR [" . ($i + 1) . "]: " . $conn->error . PHP_EOL;
        echo "SQL: " . trim(preg_replace('/\s+/', ' ', $sql)) . PHP_EOL;
    }
}

$conn->close();
echo "Migración completada." . PHP_EOL;
