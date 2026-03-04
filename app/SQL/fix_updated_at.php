<?php
/**
 * Fix: Add missing updated_at/created_at columns to tables that have useTimestamps=true
 *
 * Usage: DB_PROD_PASS=xxx php app/SQL/fix_updated_at.php production
 */
$env = $argv[1] ?? 'local';

if ($env === 'production') {
    $host = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port = 25060;
    $db   = 'propiedad_horizontal';
    $user = 'cycloid_userdb';
    $pass = getenv('DB_PROD_PASS');
    if (!$pass) { die("ERROR: Set DB_PROD_PASS\n"); }
    $ssl  = true;
} else {
    $host = '127.0.0.1';
    $port = 3306;
    $db   = 'propiedad_horizontal';
    $user = 'root';
    $pass = '';
    $ssl  = false;
}

$dsn = "mysql:host={$host};port={$port};dbname={$db}";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_TIMEOUT => 30,
];
if ($ssl) {
    $options[PDO::MYSQL_ATTR_SSL_CA] = true;
    $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
}

$pdo = new PDO($dsn, $user, $pass, $options);
echo "Connected to {$env}\n\n";

// All tables whose models have useTimestamps=true (extracted from grep of all models)
$tables = [
    // Inspecciones
    'tbl_acta_visita',
    'tbl_asistencia_induccion',
    'tbl_asistencia_induccion_asistente',   // NOT _asistentes
    'tbl_auditoria_zona_residuos',
    'tbl_carta_vigia',
    'tbl_dotacion_aseadora',
    'tbl_dotacion_todero',
    'tbl_dotacion_vigilante',
    'tbl_evaluacion_simulacro',
    'tbl_hv_brigadista',
    'tbl_inspeccion_botiquin',
    'tbl_inspeccion_comunicaciones',         // NOT _comunicacion
    'tbl_inspeccion_extintores',
    'tbl_inspeccion_gabinetes',              // NOT _gabinete
    'tbl_inspeccion_locativa',
    'tbl_inspeccion_recursos_seguridad',
    'tbl_inspeccion_senalizacion',
    'tbl_kpi_agua_potable',
    'tbl_kpi_limpieza',
    'tbl_kpi_plagas',
    'tbl_kpi_residuos',
    'tbl_matriz_vulnerabilidad',
    'tbl_plan_emergencia',
    'tbl_plan_saneamiento',
    'tbl_preparacion_simulacro',
    'tbl_probabilidad_peligros',
    'tbl_programa_agua_potable',
    'tbl_programa_limpieza',
    'tbl_programa_plagas',
    'tbl_programa_residuos',
    'tbl_reporte_capacitacion',
    // Core
    'tbl_agendamientos',                     // NOT _agendamiento
    'tbl_ciclos_visita',
    'tbl_contratos',
    'tbl_doc_firma_solicitudes',
    'tbl_informe_avances',
    'tbl_lookerstudio',
    'tbl_pendientes',
    'tbl_pta_cliente',
    'tbl_reporte',
    'tbl_roles',
    'tbl_urls',
    'tbl_usuarios',
    'tbl_vigias',
    // KPI/Policy system
    'tbl_client_kpi',
    'tbl_data_owner',
    'tbl_kpi_definition',
    'tbl_kpi_policy',
    'tbl_kpi_type',
    'tbl_kpis',
    'tbl_matrices',
    'tbl_matricescycloid',
    'tbl_measurement_period',
    'tbl_objectives_policy',
    'tbl_variable_denominator',
    'tbl_variable_numerator',
    'client_policies',
    'document_versions',
    'evaluacion_inicial_sst',
    'policy_types',
];

$fixed = 0;
foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("DESCRIBE {$table}");
        $cols = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'Field');

        $alterations = [];
        if (!in_array('created_at', $cols)) {
            $alterations[] = "ADD COLUMN created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP";
        }
        if (!in_array('updated_at', $cols)) {
            $alterations[] = "ADD COLUMN updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
        }

        if (empty($alterations)) {
            echo "  {$table}: OK\n";
        } else {
            $sql = "ALTER TABLE {$table} " . implode(", ", $alterations);
            echo "  {$table}: FIXING — {$sql}\n";
            $pdo->exec($sql);
            $fixed++;
        }
    } catch (Exception $e) {
        echo "  {$table}: SKIP (table not found or error: " . $e->getMessage() . ")\n";
    }
}

echo "\nFixed {$fixed} tables. Done.\n";
