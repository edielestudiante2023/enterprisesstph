<?php
/**
 * Migración: Agregar categorías y campos al dashboard_items
 * - Agrega columnas: categoria, icono, color_gradiente, target_blank, activo
 * - Asigna categorías a registros existentes
 * - Marca KPIs como inactivos
 * - Inserta accesos que estaban hardcodeados en la vista
 *
 * Uso:
 *   LOCAL:      php migrate_dashboard_categorias.php local
 *   PRODUCCIÓN: DB_PROD_PASS=xxx php migrate_dashboard_categorias.php production
 */

$env = $argv[1] ?? 'local';

if ($env === 'production') {
    $pass = getenv('DB_PROD_PASS');
    if (!$pass) {
        echo "ERROR: DB_PROD_PASS no definida.\n";
        exit(1);
    }
    $pdo = new PDO(
        'mysql:host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com;port=25060;dbname=propiedad_horizontal;charset=utf8mb4',
        'cycloid_userdb',
        $pass,
        [PDO::MYSQL_ATTR_SSL_CA => true, PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false]
    );
    echo "=== CONECTADO A PRODUCCIÓN ===\n";
} else {
    $pdo = new PDO('mysql:host=localhost;dbname=propiedad_horizontal;charset=utf8mb4', 'root', '');
    echo "=== CONECTADO A LOCAL ===\n";
}

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// ─── PASO 1: Agregar columnas si no existen ───
echo "\n[PASO 1] Agregando columnas nuevas...\n";

$columnas = [
    'categoria'       => "VARCHAR(100) DEFAULT NULL AFTER `orden`",
    'icono'           => "VARCHAR(100) DEFAULT NULL AFTER `categoria`",
    'color_gradiente'  => "VARCHAR(100) DEFAULT NULL AFTER `icono`",
    'target_blank'    => "TINYINT(1) DEFAULT 1 AFTER `color_gradiente`",
    'activo'          => "TINYINT(1) DEFAULT 1 AFTER `target_blank`",
];

$existentes = $pdo->query("DESCRIBE dashboard_items")->fetchAll(PDO::FETCH_COLUMN);

foreach ($columnas as $col => $def) {
    if (in_array($col, $existentes)) {
        echo "  ✓ Columna '$col' ya existe, se omite.\n";
    } else {
        $pdo->exec("ALTER TABLE dashboard_items ADD COLUMN `$col` $def");
        echo "  + Columna '$col' agregada.\n";
    }
}

// ─── PASO 2: Asignar categorías a registros existentes ───
echo "\n[PASO 2] Asignando categorías...\n";

$categorias = [
    // 1. Operación Diaria del Cliente
    24 => ['Operación Diaria',    'fas fa-calendar-alt',     '#0d6efd,#0dcaf0'],
    25 => ['Operación Diaria',    'fas fa-tasks',            '#e74c3c,#c0392b'],
    5  => ['Operación Diaria',    'fas fa-graduation-cap',   '#20c997,#13b397'],
    6  => ['Operación Diaria',    'fas fa-clipboard-check',  '#667eea,#764ba2'],
    40 => ['Operación Diaria',    'fas fa-tools',            '#f39c12,#e67e22'],
    23 => ['Operación Diaria',    'fas fa-hard-hat',         '#6f42c1,#9b59b6'],

    // 2. Gestión Documental
    1  => ['Gestión Documental',  'fas fa-file-upload',      '#2d6a4f,#40916c'],
    26 => ['Gestión Documental',  'fas fa-folder-open',      '#e67e22,#f39c12'],
    9  => ['Gestión Documental',  'fas fa-users-cog',        '#4facfe,#00f2fe'],
    2  => ['Gestión Documental',  'fas fa-sitemap',          '#6c757d,#495057'],
    3  => ['Gestión Documental',  'fas fa-tags',             '#6c757d,#495057'],
    11 => ['Gestión Documental',  'fas fa-code-branch',      '#11998e,#38ef7d'],
    12 => ['Gestión Documental',  'fas fa-gavel',            '#dc3545,#c82333'],
    13 => ['Gestión Documental',  'fas fa-file-alt',         '#fd7e14,#e8590c'],
    22 => ['Gestión Documental',  'fas fa-shield-alt',       '#dc3545,#c82333'],

    // 3. Carga Masiva CSV
    29 => ['Carga Masiva CSV',    'fas fa-file-csv',         '#20c997,#13b397'],
    28 => ['Carga Masiva CSV',    'fas fa-file-csv',         '#0d6efd,#0b5ed7'],
    30 => ['Carga Masiva CSV',    'fas fa-file-csv',         '#e74c3c,#c0392b'],
    31 => ['Carga Masiva CSV',    'fas fa-file-csv',         '#667eea,#764ba2'],
    32 => ['Carga Masiva CSV',    'fas fa-file-csv',         '#6f42c1,#9b59b6'],
    33 => ['Carga Masiva CSV',    'fas fa-file-csv',         '#f39c12,#e67e22'],
    34 => ['Carga Masiva CSV',    'fas fa-file-csv',         '#11998e,#38ef7d'],
    42 => ['Carga Masiva CSV',    'fas fa-file-csv',         '#4facfe,#00f2fe'],

    // 4. Gestión de Usuarios y Accesos
    7  => ['Usuarios y Accesos',  'fas fa-building',         '#2d6a4f,#40916c'],
    8  => ['Usuarios y Accesos',  'fas fa-user-tie',         '#0d6efd,#0dcaf0'],
    36 => ['Usuarios y Accesos',  'fas fa-key',              '#e67e22,#f39c12'],
    37 => ['Usuarios y Accesos',  'fas fa-list-check',       '#6f42c1,#9b59b6'],
    38 => ['Usuarios y Accesos',  'fas fa-eye',              '#667eea,#764ba2'],

    // 5. Configuración de Plataforma
    4  => ['Configuración',       'fas fa-cog',              '#6c757d,#495057'],
    35 => ['Configuración',       'fas fa-th-list',          '#6c757d,#495057'],
    39 => ['Configuración',       'fas fa-atom',             '#6c757d,#495057'],
];

$stmtCat = $pdo->prepare("UPDATE dashboard_items SET categoria = ?, icono = ?, color_gradiente = ? WHERE id = ?");
$updated = 0;
foreach ($categorias as $id => [$cat, $ico, $color]) {
    $stmtCat->execute([$cat, $ico, $color, $id]);
    $updated += $stmtCat->rowCount();
}
echo "  $updated registros actualizados con categoría.\n";

// ─── PASO 3: Marcar KPIs como inactivos ───
echo "\n[PASO 3] Marcando KPIs como inactivos...\n";

$kpiIds = [10, 14, 15, 16, 17, 18, 19, 20, 21, 27];
$placeholders = implode(',', array_fill(0, count($kpiIds), '?'));
$stmtKpi = $pdo->prepare("UPDATE dashboard_items SET activo = 0, categoria = 'KPIs (inactivo)' WHERE id IN ($placeholders)");
$stmtKpi->execute($kpiIds);
echo "  " . $stmtKpi->rowCount() . " registros KPI marcados como inactivos.\n";

// ─── PASO 4: Insertar accesos hardcodeados faltantes ───
echo "\n[PASO 4] Insertando accesos hardcodeados...\n";

$nuevos = [
    // IA y Asistencia
    ['Consultor', 'IA y Asistencia', 'Otto - Asistente IA', 'Chat con el asistente inteligente Otto', '/consultant/chat', 1, 'IA y Asistencia', 'fas fa-robot', '#4facfe,#00f2fe', 0, 1],
    ['Consultor', 'IA y Asistencia', 'Monitor Otto', 'Monitoreo de conversaciones y logs de Otto', '/otto-logs', 2, 'IA y Asistencia', 'fas fa-desktop', '#1c2437,#2d3a52', 1, 1],

    // Gestión Clientes (hardcodeados)
    ['Consultor', 'Gestión Clientes', 'Nuevo Cliente', 'Registrar un nuevo cliente en la plataforma', '/clients/nuevo', 1, 'Gestión Clientes', 'fas fa-user-plus', '#2d6a4f,#40916c', 1, 1],
    ['Consultor', 'Gestión Clientes', 'Ver Vista de Cliente', 'Previsualizar el portal como lo ve el cliente', '/vista-cliente', 2, 'Gestión Clientes', 'fas fa-eye', '#6366f1,#8b5cf6', 1, 1],
    ['Consultor', 'Gestión Clientes', 'Planillas Seg. Social', 'Gestión de planillas de seguridad social', 'planillas-seguridad-social', 3, 'Gestión Clientes', 'fas fa-file-invoice-dollar', '#6f42c1,#9b59b6', 1, 1],

    // Inspecciones y Auditoría
    ['Consultor', 'Inspecciones', 'Inspecciones SST', 'Módulo de inspecciones de seguridad y salud', '/inspecciones', 1, 'Inspecciones y Auditoría', 'fas fa-clipboard-check', '#0d6efd,#0dcaf0', 1, 1],
    ['Consultor', 'Inspecciones', 'Auditoría de Visitas', 'Control y auditoría de visitas realizadas', 'consultant/auditoria-visitas', 2, 'Inspecciones y Auditoría', 'fas fa-search', '#f39c12,#e67e22', 1, 1],

    // Cumplimiento y Control
    ['Consultor', 'Cumplimiento', 'Auditoría PTA', 'Auditoría del Plan de Trabajo Anual', '/audit-pta', 1, 'Cumplimiento y Control', 'fas fa-history', '#e74c3c,#c0392b', 1, 1],
    ['Consultor', 'Cumplimiento', 'Transiciones PTA', 'Historial de transiciones del PTA', '/pta-transiciones', 2, 'Cumplimiento y Control', 'fas fa-exchange-alt', '#0d6efd,#0b5ed7', 1, 1],
    ['Consultor', 'Cumplimiento', 'Listado Maestro', 'Documentos maestros Decreto 1072', 'listado-maestro', 3, 'Cumplimiento y Control', 'fas fa-list-alt', '#e67e22,#f39c12', 1, 1],

    // Planeación SST
    ['Consultor', 'Planeación', 'Presupuesto SST', 'Gestión del presupuesto de SST', 'presupuesto', 1, 'Planeación SST', 'fas fa-calculator', '#11998e,#38ef7d', 1, 1],
    ['Consultor', 'Planeación', 'Seguimiento Agenda', 'Seguimiento de agenda y actividades', 'seguimiento-agenda', 2, 'Planeación SST', 'fas fa-calendar-check', '#e74c3c,#c0392b', 1, 1],
    ['Consultor', 'Planeación', 'Acceso Rápido', 'Atajos a funciones frecuentes', '/quick-access', 3, 'Planeación SST', 'fas fa-bolt', '#bd9751,#d4af37', 1, 1],

    // Dashboards Analíticos
    ['Consultor', 'Dashboards', 'Dashboard Estándares Mínimos', 'Tablero analítico de estándares mínimos', 'consultant/dashboard-estandares', 1, 'Dashboards Analíticos', 'fas fa-chart-pie', '#667eea,#764ba2', 1, 1],
    ['Consultor', 'Dashboards', 'Dashboard Capacitaciones', 'Tablero analítico de capacitaciones', 'consultant/dashboard-capacitaciones', 2, 'Dashboards Analíticos', 'fas fa-graduation-cap', '#f093fb,#f5576c', 1, 1],
    ['Consultor', 'Dashboards', 'Dashboard Plan de Trabajo', 'Tablero analítico del plan de trabajo', 'consultant/dashboard-plan-trabajo', 3, 'Dashboards Analíticos', 'fas fa-tasks', '#4facfe,#00f2fe', 1, 1],
    ['Consultor', 'Dashboards', 'Dashboard Pendientes', 'Tablero analítico de pendientes', 'consultant/dashboard-pendientes', 4, 'Dashboards Analíticos', 'fas fa-clipboard-list', '#fa709a,#fee140', 1, 1],
    ['Consultor', 'Dashboards', 'Informe de Avances', 'Informe consolidado de avances por cliente', 'informe-avances', 5, 'Dashboards Analíticos', 'fas fa-chart-line', '#11998e,#38ef7d', 1, 1],

    // Administración
    ['Consultor', 'Administración', 'Consumo de Plataforma', 'Métricas de uso de la plataforma', '/admin/usage', 1, 'Administración', 'fas fa-chart-line', '#11998e,#38ef7d', 1, 1],
    ['Consultor', 'Administración', 'Panel de Agendamientos', 'Gestión de agendamientos de visitas', '/admin/agendamientos', 2, 'Administración', 'fas fa-calendar-check', '#20c997,#13b397', 1, 1],
    ['Consultor', 'Administración', 'Resetear Ciclo PHVA', 'Resetea evaluaciones de estándares mínimos anuales', '#resetPHVAModal', 3, 'Administración', 'fas fa-redo-alt', '#dc3545,#c82333', 0, 1],
];

// Verificar que no existan ya por accion_url
$stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM dashboard_items WHERE accion_url = ?");
$stmtInsert = $pdo->prepare("INSERT INTO dashboard_items (rol, tipo_proceso, detalle, descripcion, accion_url, orden, categoria, icono, color_gradiente, target_blank, activo) VALUES (?,?,?,?,?,?,?,?,?,?,?)");

$inserted = 0;
$skipped = 0;
foreach ($nuevos as $row) {
    $stmtCheck->execute([$row[4]]); // accion_url
    if ($stmtCheck->fetchColumn() > 0) {
        echo "  ~ Ya existe: {$row[2]} ({$row[4]}), se omite.\n";
        $skipped++;
        continue;
    }
    $stmtInsert->execute($row);
    echo "  + Insertado: {$row[2]}\n";
    $inserted++;
}
echo "  $inserted insertados, $skipped omitidos.\n";

// ─── RESUMEN ───
echo "\n=== RESUMEN ===\n";
$total = $pdo->query("SELECT COUNT(*) FROM dashboard_items")->fetchColumn();
$activos = $pdo->query("SELECT COUNT(*) FROM dashboard_items WHERE activo = 1")->fetchColumn();
$inactivos = $pdo->query("SELECT COUNT(*) FROM dashboard_items WHERE activo = 0")->fetchColumn();
$cats = $pdo->query("SELECT categoria, COUNT(*) c FROM dashboard_items WHERE activo = 1 GROUP BY categoria ORDER BY categoria")->fetchAll(PDO::FETCH_ASSOC);

echo "Total registros: $total\n";
echo "Activos: $activos | Inactivos: $inactivos\n";
echo "\nCategorías activas:\n";
foreach ($cats as $c) {
    echo "  {$c['categoria']}: {$c['c']} items\n";
}
echo "\n✓ Migración completada.\n";
