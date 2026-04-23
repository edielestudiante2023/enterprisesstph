<?php
/**
 * Script CLI — Refactor de piscinas para alinearlo al alcance real SST.
 * Uso: php migrate_piscinas_refactor_alcance_sst.php [local|production]
 *
 * El consultor SST NO mide parametros in situ ni calcula IRAPI; solo verifica
 * que existan los documentos del operador (planilla diaria, ensayo micro)
 * y observa el botiquin. Este script limpia la BD de lo que no se usa.
 *
 * Cambios:
 *   1. DROP TABLE tbl_piscina_parametro_agua      (12 params in situ)
 *   2. DROP TABLE tbl_piscina_botiquin_item       (checklist detallado)
 *   3. DROP TABLE tbl_piscina_ensayo_laboratorio  (schema obsoleto)
 *      CREATE TABLE tbl_piscina_ensayo_laboratorio NUEVA:
 *        FK id_inspeccion (no id_piscina_detalle), sin UFC, sin norma_citada
 *   4. ALTER tbl_piscina_detalle:
 *        DROP irapi_valor, irapi_clasificacion, isl_valor, isl_interpretacion
 *        ADD foto_botiquin VARCHAR(255)
 *        ADD botiquin_observaciones_faltantes TEXT
 *   5. ALTER tbl_piscina_evidencia_maestro ENUM campo:
 *        + 'planilla_diaria', 'ensayo_microbiologico'
 *
 * Safety: los registros existentes se perderan. El consultor ya sabe que
 * los datos de PRATUM eran de prueba.
 */

if (php_sapi_name() !== 'cli') die('Solo CLI.');
$env = $argv[1] ?? 'local';
if ($env === 'local')          $cfg = ['host'=>'127.0.0.1','port'=>3306,'user'=>'root','password'=>'','database'=>'propiedad_horizontal','ssl'=>false];
elseif ($env === 'production') $cfg = ['host'=>'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com','port'=>25060,'user'=>'cycloid_userdb','password'=>getenv('DB_PROD_PASS') ?: '','database'=>'propiedad_horizontal','ssl'=>true];
else die("Uso: [local|production]\n");

echo "=== Refactor piscinas: alinear al alcance real SST ===\n";
echo "Entorno: " . strtoupper($env) . "\n\n";

$m = mysqli_init();
if ($cfg['ssl']) { $m->ssl_set(null,null,null,null,null); $m->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false); }
if (!@$m->real_connect($cfg['host'],$cfg['user'],$cfg['password'],$cfg['database'],$cfg['port'],null,$cfg['ssl']?MYSQLI_CLIENT_SSL:0)) die("ERR: " . $m->connect_error . "\n");
$m->set_charset('utf8mb4');
echo "Conectado.\n\n";

$okCount = 0; $errCount = 0;
function runSql(mysqli $m, string $desc, string $sql, bool $critical = true): bool {
    global $okCount, $errCount;
    echo '  ' . $desc . '... ';
    if ($m->query($sql)) { echo "OK\n"; $okCount++; return true; }
    echo "ERR: " . $m->error . "\n";
    $errCount++;
    return !$critical;
}

// 1. DROP tbl_piscina_parametro_agua
echo "[1] DROP tablas obsoletas\n";
runSql($m, 'DROP tbl_piscina_parametro_agua',   "DROP TABLE IF EXISTS `tbl_piscina_parametro_agua`");
runSql($m, 'DROP tbl_piscina_botiquin_item',    "DROP TABLE IF EXISTS `tbl_piscina_botiquin_item`");
runSql($m, 'DROP tbl_piscina_ensayo_laboratorio (esquema obsoleto)', "DROP TABLE IF EXISTS `tbl_piscina_ensayo_laboratorio`");

// 2. CREATE nueva tbl_piscina_ensayo_laboratorio (FK al inspeccion, no al detalle)
echo "\n[2] CREATE tbl_piscina_ensayo_laboratorio (esquema nuevo, FK a inspeccion)\n";
$sqlEnsayo = "CREATE TABLE IF NOT EXISTS `tbl_piscina_ensayo_laboratorio` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_inspeccion` INT NOT NULL,
    `tipo` ENUM('MICROBIOLOGICO','FISICOQUIMICO') NOT NULL DEFAULT 'MICROBIOLOGICO',
    `fecha_toma` DATE NULL,
    `laboratorio` VARCHAR(200) NULL,
    `laboratorio_acreditado` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
    `reporta_cumplimiento` ENUM('SI','NO','NA') NOT NULL DEFAULT 'NA',
    `conforme_global` ENUM('SI','NO','PARCIAL','NA') NOT NULL DEFAULT 'NA',
    `archivo_adjunto` VARCHAR(255) NULL,
    `observaciones` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT `fk_pis_ensayo_v3_inspeccion`
        FOREIGN KEY (`id_inspeccion`) REFERENCES `tbl_inspeccion_piscinas`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,

    INDEX `idx_pis_ensayo_v3_inspeccion` (`id_inspeccion`),
    INDEX `idx_pis_ensayo_v3_tipo` (`tipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
runSql($m, 'CREATE tbl_piscina_ensayo_laboratorio', $sqlEnsayo);

// 3. ALTER tbl_piscina_detalle
echo "\n[3] ALTER tbl_piscina_detalle\n";
$cols = ['irapi_valor', 'irapi_clasificacion', 'isl_valor', 'isl_interpretacion'];
foreach ($cols as $col) {
    $r = $m->query("SHOW COLUMNS FROM tbl_piscina_detalle LIKE '$col'");
    if ($r && $r->num_rows > 0) {
        runSql($m, "DROP COLUMN $col", "ALTER TABLE tbl_piscina_detalle DROP COLUMN `$col`");
    } else {
        echo "  [skip] DROP $col (no existe)\n";
    }
}
foreach ([
    'foto_botiquin'                    => "VARCHAR(255) NULL",
    'botiquin_observaciones_faltantes' => "TEXT NULL",
] as $col => $ddl) {
    $r = $m->query("SHOW COLUMNS FROM tbl_piscina_detalle LIKE '$col'");
    if ($r && $r->num_rows > 0) {
        echo "  [skip] ADD $col (ya existe)\n";
    } else {
        runSql($m, "ADD COLUMN $col", "ALTER TABLE tbl_piscina_detalle ADD COLUMN `$col` $ddl");
    }
}

// 4. ALTER tbl_piscina_evidencia_maestro: ampliar ENUM campo
echo "\n[4] Ampliar ENUM campo en tbl_piscina_evidencia_maestro\n";
$sqlAmpliarEnum = "ALTER TABLE tbl_piscina_evidencia_maestro MODIFY COLUMN `campo` ENUM(
    'empresa_mantenimiento',
    'concepto_sanitario',
    'dea',
    'operador_cert',
    'doc_art15',
    'plan_saneamiento',
    'manejo_quimicos',
    'area_residuos',
    'contenedores_color',
    'tablero_publico',
    'planilla_diaria',
    'ensayo_microbiologico'
) NOT NULL";
runSql($m, 'ALTER ENUM campo', $sqlAmpliarEnum);

echo "\n=== RESULTADO ===\n";
echo "OK: $okCount | ERR: $errCount\n";
echo $errCount === 0 ? "MIGRACION COMPLETA.\n" : "HAY ERRORES - REVISAR.\n";

$m->close();
