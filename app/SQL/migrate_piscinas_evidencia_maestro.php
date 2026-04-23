<?php
/**
 * Script CLI — Rediseño multi-foto para evidencia de bloque maestro
 * Uso: php migrate_piscinas_evidencia_maestro.php [local|production]
 *
 * Reemplaza las 9 columnas foto_* de tbl_inspeccion_piscinas por una
 * tabla hija que permite N fotos por campo sin límite.
 *
 * Cambios:
 *   - DROP COLUMN de 9 columnas foto_* (si existen)
 *   - CREATE TABLE tbl_piscina_evidencia_maestro
 *
 * Seguro en PROD: DROP COLUMN de foto_* que solo tuvieron datos horas
 * (antes del rediseño). tbl_piscina_evidencia_maestro es nueva, sin conflicto.
 */

if (php_sapi_name() !== 'cli') die('Solo CLI.');

$env = $argv[1] ?? 'local';
if ($env === 'local') {
    $cfg = ['host'=>'127.0.0.1','port'=>3306,'user'=>'root','password'=>'','database'=>'propiedad_horizontal','ssl'=>false];
} elseif ($env === 'production') {
    $cfg = ['host'=>'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com','port'=>25060,'user'=>'cycloid_userdb','password'=>getenv('DB_PROD_PASS') ?: '','database'=>'propiedad_horizontal','ssl'=>true];
} else { die("Uso: php migrate_piscinas_evidencia_maestro.php [local|production]\n"); }

echo "=== Rediseño evidencia maestro multi-foto ===\n";
echo "Entorno: " . strtoupper($env) . "\n\n";

$m = mysqli_init();
if ($cfg['ssl']) { $m->ssl_set(null,null,null,null,null); $m->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false); }
$conn = @$m->real_connect($cfg['host'],$cfg['user'],$cfg['password'],$cfg['database'],$cfg['port'],null,$cfg['ssl']?MYSQLI_CLIENT_SSL:0);
if (!$conn) die("ERR: " . $m->connect_error . "\n");
echo "Conectado.\n";

// --- 1. DROP 9 columnas foto_* si existen ---
$cols = ['foto_concepto_sanitario','foto_dea','foto_operador_cert','foto_doc_art15',
         'foto_plan_saneamiento','foto_manejo_quimicos','foto_area_residuos',
         'foto_contenedores_color','foto_tablero_publico'];
foreach ($cols as $col) {
    $r = $m->query("SHOW COLUMNS FROM tbl_inspeccion_piscinas LIKE '$col'");
    if ($r && $r->num_rows > 0) {
        if ($m->query("ALTER TABLE tbl_inspeccion_piscinas DROP COLUMN `$col`")) {
            echo "  [DROP] $col\n";
        } else {
            echo "  [ERR DROP] $col: " . $m->error . "\n";
        }
    } else {
        echo "  [skip DROP] $col (no existe)\n";
    }
}

// --- 2. CREATE tbl_piscina_evidencia_maestro ---
echo "\n";
$sql = "CREATE TABLE IF NOT EXISTS `tbl_piscina_evidencia_maestro` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_inspeccion` INT NOT NULL,
    `campo` ENUM(
        'concepto_sanitario','dea','operador_cert','doc_art15',
        'plan_saneamiento','manejo_quimicos','area_residuos',
        'contenedores_color','tablero_publico'
    ) NOT NULL,
    `orden` SMALLINT NOT NULL DEFAULT 0,
    `foto_path` VARCHAR(255) NOT NULL,
    `descripcion` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT `fk_pis_evid_maestro_insp`
        FOREIGN KEY (`id_inspeccion`) REFERENCES `tbl_inspeccion_piscinas`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,

    INDEX `idx_pis_evid_maestro_insp` (`id_inspeccion`),
    INDEX `idx_pis_evid_maestro_campo` (`campo`),
    INDEX `idx_pis_evid_maestro_insp_campo` (`id_inspeccion`,`campo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($m->query($sql)) {
    echo "[OK] tbl_piscina_evidencia_maestro creada (o ya existía)\n";
} else {
    echo "[ERR CREATE]: " . $m->error . "\n";
}

$m->close();
echo "\nDONE.\n";
