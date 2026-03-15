<?php
/**
 * Script CLI — Crea usuario MySQL readonly para el portal cliente
 * Uso: DB_PROD_PASS=xxx DB_READONLY_PASS=yyy php create_readonly_user.php [local|production]
 *
 * El usuario cycloid_readonly solo tiene SELECT sobre las vistas v_* y
 * sobre las tablas maestras de catálogo necesarias para búsquedas.
 * Físicamente no puede INSERT, UPDATE, DELETE, ni DROP, ni acceder a tbl_*.
 */

if (php_sapi_name() !== 'cli') {
    die('Solo CLI.');
}

$env = $argv[1] ?? 'local';

$readonlyPass = getenv('DB_READONLY_PASS') ?: '';
if (!$readonlyPass) {
    die("ERROR: Define DB_READONLY_PASS=<password> antes de ejecutar.\n");
}

if ($env === 'local') {
    $config = [
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'user'     => 'root',
        'password' => '',
        'database' => 'propiedad_horizontal',
        'ssl'      => false,
        // MySQL: '%' no hace match con 'localhost' (socket). Crear usuario con ambos.
        'db_host_for_grant' => ['localhost', '127.0.0.1'],
    ];
} elseif ($env === 'production') {
    $config = [
        'host'     => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port'     => 25060,
        'user'     => 'cycloid_userdb',
        'password' => getenv('DB_PROD_PASS') ?: '',
        'database' => 'propiedad_horizontal',
        'ssl'      => true,
        'db_host_for_grant' => '%',
    ];
} else {
    die("Uso: php create_readonly_user.php [local|production]\n");
}

echo "=== Crear usuario readonly — Portal Cliente ===\n";
echo "Entorno: " . strtoupper($env) . "\n\n";

$mysqli = mysqli_init();
if ($config['ssl']) {
    $mysqli->ssl_set(null, null, null, null, null);
    $mysqli->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
}
$connected = @$mysqli->real_connect(
    $config['host'], $config['user'], $config['password'],
    $config['database'], $config['port'], null,
    $config['ssl'] ? MYSQLI_CLIENT_SSL : 0
);
if (!$connected) die("ERROR conexión: " . $mysqli->connect_error . "\n");
echo "Conexión exitosa.\n\n";

$db    = $config['database'];
$hosts = (array) $config['db_host_for_grant']; // soporta string o array
$pass  = $mysqli->real_escape_string($readonlyPass);

$success = 0;
$errors  = 0;

function run($mysqli, $sql, $label, &$success, &$errors) {
    echo "[?] {$label}... ";
    try {
        if ($mysqli->query($sql)) {
            echo "OK\n";
            $success++;
        } else {
            echo "ERROR: " . $mysqli->error . "\n";
            $errors++;
        }
    } catch (\mysqli_sql_exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
        $errors++;
    }
}

foreach ($hosts as $host) {
    echo "\n--- Host: {$host} ---\n";

    // ── 1. Crear usuario ──────────────────────────────────────────────────
    run($mysqli,
        "CREATE USER IF NOT EXISTS 'cycloid_readonly'@'{$host}' IDENTIFIED BY '{$pass}'",
        "CREATE USER cycloid_readonly@{$host}",
        $success, $errors
    );

    // ── 2. Revocar permisos previos (limpieza idempotente) ────────────────
    echo "[?] REVOKE ALL ON {$db}.* FROM @{$host}... ";
    try {
        $mysqli->query("REVOKE ALL PRIVILEGES ON `{$db}`.* FROM 'cycloid_readonly'@'{$host}'");
        echo "OK\n"; $success++;
    } catch (\mysqli_sql_exception $e) {
        if (str_contains($e->getMessage(), 'no such grant')) {
            echo "SKIP (usuario nuevo)\n"; $success++;
        } else {
            echo "ERROR: " . $e->getMessage() . "\n"; $errors++;
        }
    }
}

// ── 3 + 4. GRANTs — se aplican a cada host ─────────────────────────────────
$views = [
    'v_evaluacion_inicial_sst',
    'v_historial_resumen_estandares',
    'v_historial_resumen_plan_trabajo',
    'v_tbl_acta_visita',
    'v_tbl_acta_visita_fotos',
    'v_tbl_acta_visita_integrantes',
    'v_tbl_acta_visita_pta',
    'v_tbl_acta_visita_temas',
    'v_tbl_agendamientos',
    'v_tbl_asistencia_induccion',
    'v_tbl_asistencia_induccion_asistente',
    'v_tbl_auditoria_zona_residuos',
    'v_tbl_carta_vigia',
    'v_tbl_ciclos_visita',
    'v_tbl_contratos',
    'v_tbl_cronog_capacitacion',
    'v_tbl_dotacion_aseadora',
    'v_tbl_dotacion_todero',
    'v_tbl_dotacion_vigilante',
    'v_tbl_elemento_botiquin',
    'v_tbl_evaluacion_induccion',
    'v_tbl_evaluacion_induccion_respuesta',
    'v_tbl_evaluacion_simulacro',
    'v_tbl_extintor_detalle',
    'v_tbl_gabinete_detalle',
    'v_tbl_hallazgo_locativo',
    'v_tbl_hv_brigadista',
    'v_tbl_informe_avances',
    'v_tbl_inspeccion_botiquin',
    'v_tbl_inspeccion_comunicaciones',
    'v_tbl_inspeccion_extintores',
    'v_tbl_inspeccion_gabinetes',
    'v_tbl_inspeccion_locativa',
    'v_tbl_inspeccion_recursos_seguridad',
    'v_tbl_inspeccion_senalizacion',
    'v_tbl_item_senalizacion',
    'v_tbl_kpi_agua_potable',
    'v_tbl_kpi_limpieza',
    'v_tbl_kpi_plagas',
    'v_tbl_kpi_residuos',
    'v_tbl_lookerstudio',
    'v_tbl_matrices',
    'v_tbl_matriz_vulnerabilidad',
    'v_tbl_pendientes',
    'v_tbl_plan_emergencia',
    'v_tbl_plan_saneamiento',
    'v_tbl_preparacion_simulacro',
    'v_tbl_presupuesto_detalle',
    'v_tbl_presupuesto_items',
    'v_tbl_presupuesto_sst',
    'v_tbl_probabilidad_peligros',
    'v_tbl_programa_agua_potable',
    'v_tbl_programa_limpieza',
    'v_tbl_programa_plagas',
    'v_tbl_programa_residuos',
    'v_tbl_pta_cliente',
    'v_tbl_pta_transiciones',
    'v_tbl_reporte',
    'v_tbl_reporte_capacitacion',
    'v_tbl_vencimientos_mantenimientos',
    'v_tbl_vigias',
];

$catalogs = [
    'tbl_clientes',
    'tbl_mantenimientos',
    'capacitaciones_sst',
    'tbl_presupuesto_categorias',
    'estandares',
    'tbl_listado_maestro_documentos',
];

foreach ($hosts as $host) {
    echo "\n-- GRANTs para host: {$host} --\n";

    foreach ($views as $obj) {
        run($mysqli,
            "GRANT SELECT ON `{$db}`.`{$obj}` TO 'cycloid_readonly'@'{$host}'",
            "GRANT SELECT v_{$obj}@{$host}",
            $success, $errors
        );
    }
    foreach ($catalogs as $obj) {
        run($mysqli,
            "GRANT SELECT ON `{$db}`.`{$obj}` TO 'cycloid_readonly'@'{$host}'",
            "GRANT SELECT {$obj}@{$host}",
            $success, $errors
        );
    }
}

// ── 5. Aplicar permisos ──────────────────────────────────────────────────────
run($mysqli, "FLUSH PRIVILEGES", "FLUSH PRIVILEGES", $success, $errors);

echo "\n=== RESULTADO ===\n";
echo "OK:     {$success}\n";
echo "Errors: {$errors}\n";

if ($errors === 0) {
    echo "\nUsuario 'cycloid_readonly' creado y permisos aplicados.\n";
    echo "Guarda la contraseña en .env como READONLY_DB_PASS\n";
} else {
    echo "\nRevisar errores arriba antes de continuar.\n";
    exit(1);
}

$mysqli->close();
