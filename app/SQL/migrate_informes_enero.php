<?php
/**
 * Migración: Insertar informes de avances de ENERO 2026 en producción
 * Estos sirven como "puntaje anterior" para los informes de febrero.
 *
 * Uso: DB_PROD_PASS=xxx php app/SQL/migrate_informes_enero.php production
 *      php app/SQL/migrate_informes_enero.php local
 */

$env = $argv[1] ?? 'local';

if ($env === 'production') {
    $pass = getenv('DB_PROD_PASS');
    if (!$pass) { echo "ERROR: Set DB_PROD_PASS env var\n"; exit(1); }
    $db = mysqli_init();
    $db->ssl_set(NULL, NULL, NULL, NULL, NULL);
    $db->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
    $db->real_connect(
        'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'cycloid_userdb', $pass, 'propiedad_horizontal', 25060, NULL, MYSQLI_CLIENT_SSL
    );
} else {
    $db = new mysqli('localhost', 'root', '', 'propiedad_horizontal');
}

if ($db->connect_error) { echo "ERROR conexión: {$db->connect_error}\n"; exit(1); }
$db->set_charset('utf8mb4');
echo "Conectado a: $env\n\n";

// Datos de informes enero 2026 (extraídos del CSV del sistema anterior)
$informes = [
    [
        'cliente' => 'CONJUNTO RESIDENCIAL GRANADO',
        'puntaje_anterior' => 39.75,
        'puntaje_actual' => 53.75,
        'diferencia' => 14.0,
        'estado_avance' => 'AVANCE SIGNIFICATIVO',
        'resumen' => 'Durante el periodo octubre-diciembre 2025 se realizaron visitas de acompañamiento. Se avanzó en la implementación del SG-SST. Se ejecutaron capacitaciones programadas y se socializaron documentos estratégicos del SG-SST para la vigencia 2026.',
        'actividades_abiertas' => 'Sin pendientes',
        'observaciones' => '',
    ],
    [
        'cliente' => 'CONJUNTO RESIDENCIAL EL MOLINO DE LA ABADIA II',
        'puntaje_anterior' => 39.75,
        'puntaje_actual' => 54.25,
        'diferencia' => 14.5,
        'estado_avance' => 'AVANCE SIGNIFICATIVO',
        'resumen' => 'Se realizaron inspecciones locativas y de extintores. Se socializó el Plan de Trabajo Anual SG-SST 2026, Cronograma de Capacitaciones y Evaluación de Estándares Mínimos. Se elaboró informe a la alta dirección.',
        'actividades_abiertas' => 'Remitir el soporte de fumigación y lavado de tanques, correspondiente a las actividades de saneamiento y control sanitario de la copropiedad.',
        'observaciones' => '',
    ],
    [
        'cliente' => 'AGRUPACION DE VIVIENDA JACARANDA',
        'puntaje_anterior' => 39.75,
        'puntaje_actual' => 48.25,
        'diferencia' => 8.5,
        'estado_avance' => 'AVANCE SIGNIFICATIVO',
        'resumen' => 'Se realizó visita técnica el 22 de enero. Se socializaron y validaron documentos estratégicos del SG-SST 2026. Se elaboraron informes a la alta dirección.',
        'actividades_abiertas' => 'Concertar con la empresa de vigilancia el supervisor y control del cumplimiento de las obligaciones en materia de seguridad y salud en el trabajo.',
        'observaciones' => '',
    ],
    [
        'cliente' => 'CONJUNTO RESIDENCIAL ALTOS DE HATO CHICO',
        'puntaje_anterior' => 39.75,
        'puntaje_actual' => 54.25,
        'diferencia' => 14.5,
        'estado_avance' => 'AVANCE SIGNIFICATIVO',
        'resumen' => 'Se realizó visita técnica el 26 de enero. Se elaboró informe a la alta dirección. Se gestionó seguimiento a proveedores con recepción de informe de control de plagas.',
        'actividades_abiertas' => "Señalizar cuarto de residuos de acuerdo a los lineamientos establecidos.\nInstalar botiquín entregado en oficina de administración.",
        'observaciones' => '',
    ],
    [
        'cliente' => 'CONJUNTO PLAZUELAS DE SAN MARTIN III',
        'puntaje_anterior' => 39.75,
        'puntaje_actual' => 54.25,
        'diferencia' => 14.5,
        'estado_avance' => 'AVANCE SIGNIFICATIVO',
        'resumen' => 'Se realizó visita técnica el 26 de enero. Se socializaron y aprobaron documentos estratégicos del SG-SST 2026. Se recibieron soportes de desratización. Se elaboró informe a la alta dirección.',
        'actividades_abiertas' => 'Instalación de señalización botiquín (recepción).',
        'observaciones' => '',
    ],
    [
        'cliente' => 'CONJUNTO RESIDENCIAL LOS TUCANES',
        'puntaje_anterior' => 39.75,
        'puntaje_actual' => 54.25,
        'diferencia' => 14.5,
        'estado_avance' => 'AVANCE SIGNIFICATIVO',
        'resumen' => 'Se realizó visita técnica el 27 de enero. Se socializaron y aprobaron documentos estratégicos del SG-SST 2026. Se elaboró informe a la alta dirección.',
        'actividades_abiertas' => 'Sin pendientes',
        'observaciones' => '',
    ],
    [
        'cliente' => 'CONJUNTO RESIDENCIAL EL ZORZAL',
        'puntaje_anterior' => 39.75,
        'puntaje_actual' => 54.25,
        'diferencia' => 14.5,
        'estado_avance' => 'AVANCE SIGNIFICATIVO',
        'resumen' => 'Se realizó visita técnica el 27 de enero. Se socializaron y aprobaron Evaluación Inicial y Plan de Capacitación 2026. Se elaboró informe a la alta dirección.',
        'actividades_abiertas' => 'Sin pendientes',
        'observaciones' => '',
    ],
    [
        'cliente' => 'CONJUNTO RESIDENCIAL HELICONIA',
        'puntaje_anterior' => 39.75,
        'puntaje_actual' => 55.75,
        'diferencia' => 16.0,
        'estado_avance' => 'AVANCE SIGNIFICATIVO',
        'resumen' => 'Se realizaron visitas técnicas el 28 de enero. Se socializaron y aprobaron documentos estratégicos del SG-SST 2026. Se recibió certificado de fumigación. Se elaboró informe a la alta dirección.',
        'actividades_abiertas' => 'Sin pendientes',
        'observaciones' => '',
    ],
    [
        'cliente' => 'CONJUNTO RESIDENCIAL VIOLETA',
        'puntaje_anterior' => 39.75,
        'puntaje_actual' => 54.25,
        'diferencia' => 14.5,
        'estado_avance' => 'AVANCE SIGNIFICATIVO',
        'resumen' => 'Se realizó visita técnica el 29 de enero. Se socializaron y aprobaron documentos estratégicos del SG-SST 2026. Se envió informe anual 2025.',
        'actividades_abiertas' => 'Enviar soporte de lavado de tanques y fumigación.',
        'observaciones' => '',
    ],
    [
        'cliente' => 'LA ALEGRIA IV CONJUNTO RESIDENCIAL',
        'puntaje_anterior' => 39.75,
        'puntaje_actual' => 55.75,
        'diferencia' => 16.0,
        'estado_avance' => 'AVANCE SIGNIFICATIVO',
        'resumen' => 'Se realizó visita técnica el 30 de enero. Se socializaron y aprobaron documentos estratégicos del SG-SST 2026. Se envió informe anual 2025.',
        'actividades_abiertas' => 'Sin pendientes',
        'observaciones' => '',
    ],
    [
        'cliente' => 'CONJUNTO RESIDENCIAL LUCERNA FASE A ETAPA 1',
        'puntaje_anterior' => 39.75,
        'puntaje_actual' => 50.75,
        'diferencia' => 11.0,
        'estado_avance' => 'AVANCE SIGNIFICATIVO',
        'resumen' => 'Se realizó visita técnica el 19 de enero. Se socializaron y aprobaron documentos estratégicos del SG-SST 2026. Se formalizó continuidad contractual 2026.',
        'actividades_abiertas' => 'Sin pendientes',
        'observaciones' => '',
    ],
    [
        'cliente' => 'CONJUNTO RESIDENCIAL ALAMO',
        'puntaje_anterior' => 39.75,
        'puntaje_actual' => 54.25,
        'diferencia' => 14.5,
        'estado_avance' => 'AVANCE SIGNIFICATIVO',
        'resumen' => 'Se realizó visita técnica el 19 de enero. Se socializaron y aprobaron documentos estratégicos del SG-SST 2026 incluyendo Plan de Trabajo Anual.',
        'actividades_abiertas' => 'Sin pendientes',
        'observaciones' => '',
    ],
    [
        'cliente' => 'CAOBO CONJUNTO RESIDENCIAL CAOBO',
        'puntaje_anterior' => 39.75,
        'puntaje_actual' => 46.25,
        'diferencia' => 6.5,
        'estado_avance' => 'AVANCE MODERADO',
        'resumen' => 'Se realizó visita técnica el 20 de enero. Se socializó y aprobó Plan de Trabajo Anual SG-SST 2026. Se envió informe anual 2025.',
        'actividades_abiertas' => 'Sin pendientes',
        'observaciones' => '',
    ],
    [
        'cliente' => 'CONJUNTO RESIDENCIAL LA ARBOLEDA DE SANTA ANA',
        'puntaje_anterior' => 39.75,
        'puntaje_actual' => 52.25,
        'diferencia' => 12.5,
        'estado_avance' => 'AVANCE SIGNIFICATIVO',
        'resumen' => 'Se realizó visita técnica el 23 de enero. Se socializaron y aprobaron documentos estratégicos del SG-SST 2026. Se realizó auditoría al proveedor de aseo. Se formalizó continuidad contractual 2026.',
        'actividades_abiertas' => 'Sin pendientes',
        'observaciones' => '',
    ],
    [
        'cliente' => 'CONJUNTO RESIDENCIAL ALHELI',
        'puntaje_anterior' => 39.75,
        'puntaje_actual' => 48.25,
        'diferencia' => 8.5,
        'estado_avance' => 'AVANCE MODERADO',
        'resumen' => 'Se realizó visita técnica el 23 de enero. Se realizó inspección del botiquín. Se envió informe anual 2025.',
        'actividades_abiertas' => "Comprar e instalación de requerimientos de secretaria de salud.\nRemplazar implementos vencidos del botiquín.\nConseguir implementos faltantes del botiquín.",
        'observaciones' => '',
    ],
    [
        'cliente' => 'AGRUPACION DE VIVIENDA ACACIA',
        'puntaje_anterior' => 39.75,
        'puntaje_actual' => 44.25,
        'diferencia' => 4.5,
        'estado_avance' => 'AVANCE MODERADO',
        'resumen' => 'Se realizó visita técnica el 23 de enero. Se envió informe anual 2025.',
        'actividades_abiertas' => 'Sin pendientes',
        'observaciones' => '',
    ],
    [
        'cliente' => 'CONJUNTO RESIDENCIAL SAN SEBASTIAN PRIMERA ETAPA',
        'puntaje_anterior' => 39.75,
        'puntaje_actual' => 50.25,
        'diferencia' => 10.5,
        'estado_avance' => 'AVANCE SIGNIFICATIVO',
        'resumen' => 'Se realizó visita técnica el 29 de enero. Se envió informe anual 2025.',
        'actividades_abiertas' => 'Sin pendientes',
        'observaciones' => '',
    ],
    [
        'cliente' => 'CONJUNTO RESIDENCIAL JARDINES DE LA COLINA PRIMERA ETAPA',
        'puntaje_anterior' => 39.75,
        'puntaje_actual' => 39.75,
        'diferencia' => 0.0,
        'estado_avance' => 'ESTABLE',
        'resumen' => 'Se realizó visita técnica el 29 de enero. Se envió informe anual 2025.',
        'actividades_abiertas' => "Instalar señalización en los extintores.\nGestionar las actividades pendientes de enero 2026, compartidas a través de correo electrónico.",
        'observaciones' => '',
    ],
    [
        'cliente' => 'CONJUNTO RESIDENCIAL ALCAPARRO',
        'puntaje_anterior' => 39.75,
        'puntaje_actual' => 48.25,
        'diferencia' => 8.5,
        'estado_avance' => 'AVANCE MODERADO',
        'resumen' => 'Se realizó visita técnica el 30 de enero. Se envió informe anual 2025.',
        'actividades_abiertas' => 'Sin pendientes',
        'observaciones' => '',
    ],
    [
        'cliente' => 'CONJUNTO RESIDENCIAL LA ESPERANZA III',
        'puntaje_anterior' => 39.75,
        'puntaje_actual' => 46.25,
        'diferencia' => 6.5,
        'estado_avance' => 'AVANCE MODERADO',
        'resumen' => 'Se realizó visita técnica el 26 de enero. Se realizó auditoría al proveedor de aseo. Se envió informe anual 2025.',
        'actividades_abiertas' => 'Sin pendientes',
        'observaciones' => '',
    ],
    [
        'cliente' => 'EDIFICIO FINLANDIA',
        'puntaje_anterior' => 39.75,
        'puntaje_actual' => 39.75,
        'diferencia' => 0.0,
        'estado_avance' => 'ESTABLE',
        'resumen' => 'Se realizó visita técnica el 29 de enero.',
        'actividades_abiertas' => 'Gestionar las actividades pendientes de enero 2026, compartidas a través de correo electrónico.',
        'observaciones' => '',
    ],
];

// Verificar tabla existe
$check = $db->query("SHOW TABLES LIKE 'tbl_informe_avances'");
if ($check->num_rows === 0) {
    echo "ERROR: tabla tbl_informe_avances no existe en $env\n";
    exit(1);
}

$insertados = 0;
$errores = 0;
$omitidos = 0;

foreach ($informes as $inf) {
    $nombre = $db->real_escape_string($inf['cliente']);

    // Buscar cliente por nombre parcial
    $r = $db->query("SELECT id_cliente, nombre_cliente, id_consultor FROM tbl_clientes WHERE nombre_cliente LIKE '%$nombre%' LIMIT 1");
    if (!$r || $r->num_rows === 0) {
        echo "  NOT FOUND: {$inf['cliente']}\n";
        $errores++;
        continue;
    }
    $cliente = $r->fetch_assoc();
    $idCliente = $cliente['id_cliente'];
    $idConsultor = $cliente['id_consultor'];

    // Verificar si ya existe informe enero 2026 para este cliente
    $exists = $db->query("SELECT id FROM tbl_informe_avances WHERE id_cliente = $idCliente AND fecha_desde = '2026-01-01' AND fecha_hasta = '2026-01-31'");
    if ($exists && $exists->num_rows > 0) {
        echo "  SKIP (ya existe): {$cliente['nombre_cliente']}\n";
        $omitidos++;
        continue;
    }

    $resumen = $db->real_escape_string($inf['resumen']);
    $actividades = $db->real_escape_string($inf['actividades_abiertas']);
    $observaciones = $db->real_escape_string($inf['observaciones']);

    $sql = "INSERT INTO tbl_informe_avances (
        id_cliente, id_consultor, fecha_desde, fecha_hasta, anio,
        puntaje_anterior, puntaje_actual, diferencia_neta, estado_avance,
        resumen_avance, actividades_abiertas, observaciones,
        actividades_cerradas_periodo,
        estado, created_at, updated_at
    ) VALUES (
        $idCliente, $idConsultor, '2026-01-01', '2026-01-31', 2026,
        {$inf['puntaje_anterior']}, {$inf['puntaje_actual']}, {$inf['diferencia']}, '{$inf['estado_avance']}',
        '$resumen', '$actividades', '$observaciones',
        'Datos importados del sistema anterior.',
        'completo', NOW(), NOW()
    )";

    if ($db->query($sql)) {
        echo "  OK: {$cliente['nombre_cliente']} (id={$idCliente}) → puntaje={$inf['puntaje_actual']}\n";
        $insertados++;
    } else {
        echo "  ERROR: {$cliente['nombre_cliente']} → {$db->error}\n";
        $errores++;
    }
}

echo "\n=== RESUMEN ===\n";
echo "Insertados: $insertados\n";
echo "Omitidos (ya existían): $omitidos\n";
echo "Errores: $errores\n";
echo "Total procesados: " . count($informes) . "\n";

$db->close();
