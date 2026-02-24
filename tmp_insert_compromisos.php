<?php
/**
 * Insert test compromisos (pendientes linked to actas de visita)
 * Run: DB_PROD_PASS=xxx php tmp_insert_compromisos.php
 */
$pdo = new PDO(
    "mysql:host=db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com;port=25060;dbname=propiedad_horizontal;charset=utf8mb4",
    'cycloid_userdb', getenv('DB_PROD_PASS'),
    [PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false, PDO::MYSQL_ATTR_SSL_CA => '', PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$compromisos = [
    // Acta 1 - 3 compromisos
    [
        'id_cliente' => 43,
        'id_acta_visita' => 1,
        'responsable' => 'Administrador',
        'tarea_actividad' => 'Actualizar matriz de peligros y valoracion de riesgos',
        'fecha_asignacion' => '2026-02-22',
        'fecha_cierre' => '2026-03-15',
        'estado' => 'ABIERTA',
        'estado_avance' => 'Sin iniciar',
    ],
    [
        'id_cliente' => 43,
        'id_acta_visita' => 1,
        'responsable' => 'Vigia SST',
        'tarea_actividad' => 'Realizar inspeccion mensual de extintores y botiquines',
        'fecha_asignacion' => '2026-02-22',
        'fecha_cierre' => '2026-03-01',
        'estado' => 'ABIERTA',
        'estado_avance' => 'Sin iniciar',
    ],
    [
        'id_cliente' => 43,
        'id_acta_visita' => 1,
        'responsable' => 'Administrador',
        'tarea_actividad' => 'Programar capacitacion de trabajo en alturas para personal de mantenimiento',
        'fecha_asignacion' => '2026-02-22',
        'fecha_cierre' => '2026-03-22',
        'estado' => 'ABIERTA',
        'estado_avance' => 'Sin iniciar',
    ],

    // Acta 2 - 3 compromisos
    [
        'id_cliente' => 43,
        'id_acta_visita' => 2,
        'responsable' => 'Administrador',
        'tarea_actividad' => 'Gestionar mantenimiento preventivo del sistema de bombeo',
        'fecha_asignacion' => '2026-02-22',
        'fecha_cierre' => '2026-03-10',
        'estado' => 'ABIERTA',
        'estado_avance' => 'Sin iniciar',
    ],
    [
        'id_cliente' => 43,
        'id_acta_visita' => 2,
        'responsable' => 'Vigia SST',
        'tarea_actividad' => 'Verificar senalizacion de rutas de evacuacion en pisos 3 y 4',
        'fecha_asignacion' => '2026-02-22',
        'fecha_cierre' => '2026-03-05',
        'estado' => 'ABIERTA',
        'estado_avance' => 'Sin iniciar',
    ],
    [
        'id_cliente' => 43,
        'id_acta_visita' => 2,
        'responsable' => 'Consultor SST',
        'tarea_actividad' => 'Enviar informe de condiciones locativas con recomendaciones',
        'fecha_asignacion' => '2026-02-22',
        'fecha_cierre' => '2026-03-01',
        'estado' => 'ABIERTA',
        'estado_avance' => 'Sin iniciar',
    ],
];

$sql = "INSERT INTO tbl_pendientes (id_cliente, id_acta, id_acta_visita, responsable, tarea_actividad, fecha_asignacion, fecha_cierre, estado, estado_avance, conteo_dias, created_at, updated_at)
        VALUES (:id_cliente, :id_acta, :id_acta_visita, :responsable, :tarea_actividad, :fecha_asignacion, :fecha_cierre, :estado, :estado_avance, :conteo_dias, NOW(), NOW())";

$stmt = $pdo->prepare($sql);
$inserted = 0;

foreach ($compromisos as $c) {
    // Calculate conteo_dias
    $asig = new DateTime($c['fecha_asignacion']);
    $now = new DateTime();
    $conteo = $asig->diff($now)->days;

    $stmt->execute([
        ':id_cliente' => $c['id_cliente'],
        ':id_acta' => 'AV-' . $c['id_acta_visita'],
        ':id_acta_visita' => $c['id_acta_visita'],
        ':responsable' => $c['responsable'],
        ':tarea_actividad' => $c['tarea_actividad'],
        ':fecha_asignacion' => $c['fecha_asignacion'],
        ':fecha_cierre' => $c['fecha_cierre'],
        ':estado' => $c['estado'],
        ':estado_avance' => $c['estado_avance'],
        ':conteo_dias' => $conteo,
    ]);
    $inserted++;
    echo "Inserted: [{$c['id_acta_visita']}] {$c['tarea_actividad']}\n";
}

echo "\nTotal inserted: $inserted\n";

// Verify
echo "\n=== VERIFICACION ===\n";
$rows = $pdo->query("SELECT id_pendientes, id_acta_visita, tarea_actividad, responsable FROM tbl_pendientes WHERE id_acta_visita IS NOT NULL ORDER BY id_acta_visita, id_pendientes")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "  Acta #{$r['id_acta_visita']} | #{$r['id_pendientes']} | {$r['responsable']} | {$r['tarea_actividad']}\n";
}
