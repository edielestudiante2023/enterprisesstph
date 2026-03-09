<?php
/**
 * Hace nullable la columna id_consultor en TODAS las tablas de inspecciones
 * para permitir que usuarios tipo cliente creen registros sin FK error.
 *
 * Uso: php migrate_nullable_consultor_global.php [local|production]
 */

if (php_sapi_name() !== 'cli') {
    die('Este script solo puede ejecutarse desde la línea de comandos.');
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
    die("Uso: php migrate_nullable_consultor_global.php [local|production]\n");
}

// Tablas y sus FK constraints (tbl_programa_plagas ya fue migrada aparte)
$tables = [
    'tbl_inspeccion_extintores'        => 'fk_insp_ext_consultor',
    'tbl_inspeccion_gabinetes'         => 'fk_insp_gab_consultor',
    'tbl_inspeccion_locativa'          => 'fk_insp_locativa_consultor',
    'tbl_inspeccion_senalizacion'      => 'fk_insp_senal_consultor',
    'tbl_inspeccion_botiquin'          => 'fk_insp_bot_consultor',
    'tbl_inspeccion_comunicaciones'    => 'fk_insp_com_consultor',
    'tbl_inspeccion_recursos_seguridad'=> 'fk_insp_rec_consultor',
    'tbl_matriz_vulnerabilidad'        => 'fk_mat_vul_consultor',
    'tbl_plan_emergencia'              => 'fk_plan_emg_consultor',
    'tbl_probabilidad_peligros'        => 'fk_prob_pel_consultor',
    'tbl_programa_limpieza'            => 'fk_prog_limp_consultor',
    'tbl_programa_residuos'            => 'fk_prog_res_consultor',
    'tbl_programa_agua_potable'        => 'fk_prog_agua_consultor',
    'tbl_plan_saneamiento'             => 'fk_plan_san_consultor',
    'tbl_asistencia_induccion'         => 'fk_asist_ind_consultor',
    'tbl_reporte_capacitacion'         => 'fk_rep_cap_consultor',
    'tbl_preparacion_simulacro'        => 'fk_prep_sim_consultor',
    'tbl_dotacion_aseadora'            => 'fk_dot_ase_consultor',
    'tbl_dotacion_todero'              => 'fk_dot_tod_consultor',
    'tbl_dotacion_vigilante'           => 'fk_dot_vig_consultor',
    'tbl_auditoria_zona_residuos'      => 'fk_aud_res_consultor',
    'tbl_kpi_limpieza'                 => 'fk_kpi_limp_consultor',
    'tbl_kpi_agua_potable'             => 'fk_kpi_agua_consultor',
    'tbl_kpi_plagas'                   => 'fk_kpi_plag_consultor',
    'tbl_kpi_residuos'                 => 'fk_kpi_res_consultor',
    'tbl_carta_vigia'                  => 'fk_carta_vigia_consultor',
    'tbl_mantenimientos'               => 'fk_mant_consultor',
    'tbl_agendamiento'                 => 'fk_agend_consultor',
];

echo "=== Migración global: id_consultor nullable en " . count($tables) . " tablas ===\n";
echo "Entorno: {$env}\n\n";

$flags = $config['ssl'] ? MYSQLI_CLIENT_SSL : 0;
$conn = mysqli_init();
if ($config['ssl']) {
    $conn->ssl_set(null, null, null, null, null);
}
if (!$conn->real_connect($config['host'], $config['user'], $config['password'], $config['database'], $config['port'], null, $flags)) {
    die("Error de conexión: " . $conn->connect_error . "\n");
}
$conn->set_charset('utf8mb4');

$ok = 0;
$skip = 0;
$fail = 0;

foreach ($tables as $table => $fkName) {
    echo "── {$table} ──\n";

    // Verificar que la tabla existe
    $check = $conn->query("SHOW TABLES LIKE '{$table}'");
    if ($check->num_rows === 0) {
        echo "  SKIP: tabla no existe\n\n";
        $skip++;
        continue;
    }

    // Verificar que la columna id_consultor existe
    $colCheck = $conn->query("SHOW COLUMNS FROM `{$table}` LIKE 'id_consultor'");
    if ($colCheck->num_rows === 0) {
        echo "  SKIP: columna id_consultor no existe\n\n";
        $skip++;
        continue;
    }

    // 1. Drop FK si existe
    $dropFk = "ALTER TABLE `{$table}` DROP FOREIGN KEY `{$fkName}`";
    if ($conn->query($dropFk)) {
        echo "  DROP FK: OK\n";
    } else {
        // FK puede no existir con ese nombre, intentar sin ella
        echo "  DROP FK: SKIP ({$conn->error})\n";
    }

    // 2. Make nullable
    $modify = "ALTER TABLE `{$table}` MODIFY COLUMN `id_consultor` INT NULL DEFAULT NULL";
    if ($conn->query($modify)) {
        echo "  NULLABLE: OK\n";
    } else {
        echo "  NULLABLE: ERROR ({$conn->error})\n";
        $fail++;
        echo "\n";
        continue;
    }

    // 3. Recreate FK with SET NULL
    $addFk = "ALTER TABLE `{$table}` ADD CONSTRAINT `{$fkName}` FOREIGN KEY (`id_consultor`) REFERENCES `tbl_consultor`(`id_consultor`) ON DELETE SET NULL ON UPDATE CASCADE";
    if ($conn->query($addFk)) {
        echo "  ADD FK: OK\n";
    } else {
        echo "  ADD FK: ERROR ({$conn->error})\n";
        $fail++;
        echo "\n";
        continue;
    }

    $ok++;
    echo "\n";
}

echo "=== Resultado: {$ok} OK, {$skip} SKIP, {$fail} FAIL ===\n";
$conn->close();
