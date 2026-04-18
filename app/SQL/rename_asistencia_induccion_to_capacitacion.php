<?php
/**
 * Paso D: Renombrar tablas y columna de "induccion" a "capacitacion".
 *
 * Cambios:
 *   1. tbl_asistencia_induccion           → tbl_asistencia_capacitacion
 *   2. tbl_asistencia_induccion_asistente → tbl_asistencia_capacitacion_asistente
 *   3. tbl_reporte_capacitacion.mostrar_evaluacion_induccion → mostrar_evaluacion_capacitacion
 *      (si existe con nombre viejo; si no existe, se crea con nombre nuevo)
 *
 * Preserva:
 *   - Vistas legacy v_tbl_asistencia_induccion, v_tbl_asistencia_induccion_asistente,
 *     v_tbl_reporte_capacitacion se recrean APUNTANDO a las nuevas tablas,
 *     manteniendo su nombre antiguo para compatibilidad con cycloid_readonly (chat Otto).
 *
 * Idempotente: detecta estado actual y aplica solo lo necesario.
 *
 * Uso:
 *   LOCAL      : php app/SQL/rename_asistencia_induccion_to_capacitacion.php
 *   PRODUCCIÓN : DB_PROD_PASS=xxx php app/SQL/rename_asistencia_induccion_to_capacitacion.php production
 */
$env = $argv[1] ?? 'local';

if ($env === 'production') {
    $host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port = 25060;
    $db   = 'propiedad_horizontal';
    $user = 'cycloid_userdb';
    $pass = getenv('DB_PROD_PASS');
    $ssl  = true;
    if (!$pass) { fwrite(STDERR, "ERROR: DB_PROD_PASS no definida.\n"); exit(1); }
} else {
    $host = '127.0.0.1'; $port = 3306;
    $db   = 'propiedad_horizontal'; $user = 'root'; $pass = ''; $ssl = false;
}

$dsn  = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
$opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
if ($ssl) { $opts[PDO::MYSQL_ATTR_SSL_CA] = true; $opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false; }

$pdo = new PDO($dsn, $user, $pass, $opts);
echo "Conectado [{$env}]\n\n";

// ── Helpers ─────────────────────────────────────────────────────────────────
function tableExists(PDO $pdo, string $name): bool {
    $s = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND TABLE_TYPE = 'BASE TABLE'");
    $s->execute([$name]);
    return (int) $s->fetchColumn() > 0;
}
function viewExists(PDO $pdo, string $name): bool {
    $s = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.VIEWS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?");
    $s->execute([$name]);
    return (int) $s->fetchColumn() > 0;
}
function columnExists(PDO $pdo, string $table, string $col): bool {
    $s = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
    $s->execute([$table, $col]);
    return (int) $s->fetchColumn() > 0;
}

// ═══════════════════════════════════════════════════════════════════════════
// FASE 1: DROP VIEWS dependientes (necesario antes del RENAME)
// ═══════════════════════════════════════════════════════════════════════════
foreach (['v_tbl_reporte_capacitacion', 'v_tbl_asistencia_induccion_asistente', 'v_tbl_asistencia_induccion'] as $v) {
    if (viewExists($pdo, $v)) {
        $pdo->exec("DROP VIEW `{$v}`");
        echo "DROP VIEW {$v}\n";
    } else {
        echo "INFO: vista {$v} no existía.\n";
    }
}
echo "\n";

// ═══════════════════════════════════════════════════════════════════════════
// FASE 2: RENAME tablas (si el nombre viejo existe)
// ═══════════════════════════════════════════════════════════════════════════
if (tableExists($pdo, 'tbl_asistencia_induccion') && !tableExists($pdo, 'tbl_asistencia_capacitacion')) {
    $pdo->exec("RENAME TABLE `tbl_asistencia_induccion` TO `tbl_asistencia_capacitacion`");
    echo "RENAME TABLE tbl_asistencia_induccion → tbl_asistencia_capacitacion\n";
} else {
    echo "INFO: tbl_asistencia_induccion ya está renombrada o no existe.\n";
}

if (tableExists($pdo, 'tbl_asistencia_induccion_asistente') && !tableExists($pdo, 'tbl_asistencia_capacitacion_asistente')) {
    $pdo->exec("RENAME TABLE `tbl_asistencia_induccion_asistente` TO `tbl_asistencia_capacitacion_asistente`");
    echo "RENAME TABLE tbl_asistencia_induccion_asistente → tbl_asistencia_capacitacion_asistente\n";
} else {
    echo "INFO: tbl_asistencia_induccion_asistente ya está renombrada o no existe.\n";
}
echo "\n";

// ═══════════════════════════════════════════════════════════════════════════
// FASE 3: Columna mostrar_evaluacion_induccion → mostrar_evaluacion_capacitacion
// ═══════════════════════════════════════════════════════════════════════════
$hasOld = columnExists($pdo, 'tbl_reporte_capacitacion', 'mostrar_evaluacion_induccion');
$hasNew = columnExists($pdo, 'tbl_reporte_capacitacion', 'mostrar_evaluacion_capacitacion');

if ($hasOld && !$hasNew) {
    $pdo->exec("ALTER TABLE `tbl_reporte_capacitacion` CHANGE COLUMN `mostrar_evaluacion_induccion` `mostrar_evaluacion_capacitacion` TINYINT(1) NOT NULL DEFAULT 0");
    echo "RENAME COLUMN mostrar_evaluacion_induccion → mostrar_evaluacion_capacitacion\n";
} elseif (!$hasOld && !$hasNew) {
    $pdo->exec("ALTER TABLE `tbl_reporte_capacitacion` ADD COLUMN `mostrar_evaluacion_capacitacion` TINYINT(1) NOT NULL DEFAULT 0");
    echo "ADD COLUMN mostrar_evaluacion_capacitacion (no existía)\n";
} elseif ($hasNew) {
    echo "INFO: columna mostrar_evaluacion_capacitacion ya existe.\n";
}
echo "\n";

// ═══════════════════════════════════════════════════════════════════════════
// FASE 4: RECREAR VIEWS con nombres legacy apuntando a tablas nuevas
// (mantener nombres v_tbl_asistencia_induccion* para compat con cycloid_readonly)
// ═══════════════════════════════════════════════════════════════════════════

// Construir lista de columnas de tbl_asistencia_capacitacion dinámicamente
$asistCols = $pdo->query("
    SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tbl_asistencia_capacitacion'
    ORDER BY ORDINAL_POSITION
")->fetchAll(PDO::FETCH_COLUMN);
$asistSelect = implode(', ', array_map(fn($c) => "`a`.`{$c}`", $asistCols));

// v_tbl_asistencia_induccion → apunta a tbl_asistencia_capacitacion
$pdo->exec("
    CREATE VIEW `v_tbl_asistencia_induccion` AS
    SELECT {$asistSelect},
           `c`.`nombre_cliente`,
           `con`.`nombre_consultor`
    FROM `tbl_asistencia_capacitacion` `a`
    JOIN `tbl_clientes` `c` ON `a`.`id_cliente` = `c`.`id_cliente`
    LEFT JOIN `tbl_consultor` `con` ON `a`.`id_consultor` = `con`.`id_consultor`
");
echo "CREATE VIEW v_tbl_asistencia_induccion\n";

// v_tbl_asistencia_induccion_asistente → apunta a tbl_asistencia_capacitacion_asistente
$pdo->exec("
    CREATE VIEW `v_tbl_asistencia_induccion_asistente` AS
    SELECT `a`.`id`, `a`.`id_asistencia`, `a`.`nombre`, `a`.`cedula`, `a`.`cargo`, `a`.`firma`,
           `a`.`created_at`, `a`.`updated_at`,
           `asi`.`fecha_sesion`, `asi`.`tema`,
           `c`.`nombre_cliente`
    FROM `tbl_asistencia_capacitacion_asistente` `a`
    JOIN `tbl_asistencia_capacitacion` `asi` ON `a`.`id_asistencia` = `asi`.`id`
    JOIN `tbl_clientes` `c` ON `asi`.`id_cliente` = `c`.`id_cliente`
");
echo "CREATE VIEW v_tbl_asistencia_induccion_asistente\n";

// v_tbl_reporte_capacitacion → referencia la nueva columna mostrar_evaluacion_capacitacion
$pdo->exec("
    CREATE VIEW `v_tbl_reporte_capacitacion` AS
    SELECT `rc`.`id`, `rc`.`id_cliente`, `rc`.`id_consultor`, `rc`.`id_cronograma_capacitacion`,
           `rc`.`fecha_capacitacion`, `rc`.`nombre_capacitacion`, `rc`.`objetivo_capacitacion`,
           `rc`.`perfil_asistentes`, `rc`.`nombre_capacitador`, `rc`.`horas_duracion`,
           `rc`.`numero_asistentes`, `rc`.`numero_programados`, `rc`.`numero_evaluados`,
           `rc`.`promedio_calificaciones`, `rc`.`foto_listado_asistencia`, `rc`.`foto_capacitacion`,
           `rc`.`foto_evaluacion`, `rc`.`foto_otros_1`, `rc`.`foto_otros_2`, `rc`.`observaciones`,
           `rc`.`ruta_pdf`, `rc`.`estado`, `rc`.`created_at`, `rc`.`updated_at`,
           `rc`.`mostrar_evaluacion_capacitacion`,
           `c`.`nombre_cliente`,
           `con`.`nombre_consultor`,
           `cc`.`estado` AS `estado_cronograma`
    FROM `tbl_reporte_capacitacion` `rc`
    JOIN `tbl_clientes` `c` ON `rc`.`id_cliente` = `c`.`id_cliente`
    LEFT JOIN `tbl_consultor` `con` ON `rc`.`id_consultor` = `con`.`id_consultor`
    LEFT JOIN `tbl_cronog_capacitacion` `cc` ON `rc`.`id_cronograma_capacitacion` = `cc`.`id_cronograma_capacitacion`
");
echo "CREATE VIEW v_tbl_reporte_capacitacion\n";

echo "\nMigración completada.\n";
