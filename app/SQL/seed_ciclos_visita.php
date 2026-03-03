<?php
/**
 * Seed: Cargar datos iniciales en tbl_ciclos_visita
 * Datos de mes_prox_visita proporcionados por el usuario.
 *
 * Uso:
 *   Local:      php app/SQL/seed_ciclos_visita.php
 *   Producción: php app/SQL/seed_ciclos_visita.php production
 */

$isProduction = ($argv[1] ?? '') === 'production';

if ($isProduction) {
    $pass = getenv('DB_PROD_PASS');
    $db = mysqli_init();
    mysqli_ssl_set($db, NULL, NULL, NULL, NULL, NULL);
    $db->real_connect(
        'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'cycloid_userdb', $pass, 'propiedad_horizontal', 25060,
        NULL, MYSQLI_CLIENT_SSL
    );
    echo "=== Conectado a PRODUCCIÓN ===\n";
} else {
    $db = new mysqli('localhost', 'root', '', 'propiedad_horizontal');
    echo "=== Conectado a LOCAL ===\n";
}

if ($db->connect_error) {
    echo "ERROR: " . $db->connect_error . "\n";
    exit(1);
}

$db->set_charset('utf8mb4');

// Verificar tabla existe
$check = $db->query("SHOW TABLES LIKE 'tbl_ciclos_visita'");
if ($check->num_rows === 0) {
    echo "ERROR: tbl_ciclos_visita no existe. Ejecutar migrate_ciclos_visita.php primero.\n";
    exit(1);
}

// Verificar si ya hay datos
$existing = $db->query("SELECT COUNT(*) as cnt FROM tbl_ciclos_visita")->fetch_assoc()['cnt'];
if ($existing > 0) {
    echo "Ya hay {$existing} registros. Saltando seed para evitar duplicados.\n";
    echo "Para re-ejecutar, primero vaciar: TRUNCATE tbl_ciclos_visita;\n";
    $db->close();
    exit(0);
}

// Datos del usuario: id_cliente => mes_prox_visita
$data = [
    71 => 3, 37 => 3, 22 => 3, 36 => 3, 65 => 3, 70 => 3, 61 => 3,
    54 => 3, 73 => 3, 55 => 3, 18 => 3, 35 => 3, 49 => 3, 63 => 3,
    31 => 3, 44 => 3, 67 => 3, 17 => 3, 20 => 3, 53 => 3, 39 => 3,
    24 => 3, 64 => 3, 50 => 3, 23 => 3, 30 => 3, 34 => 3, 25 => 3,
    59 => 4, 60 => 4, 32 => 4, 21 => 4, 46 => 4, 42 => 4, 29 => 4,
    62 => 4, 33 => 5, 56 => 5, 58 => 5, 57 => 5,
];

$anio = (int)date('Y');
$inserted = 0;
$errors = 0;
$notFound = 0;

foreach ($data as $idCliente => $mes) {
    // Obtener consultor y estandar del cliente
    $cl = $db->query("
        SELECT id_consultor, estandares
        FROM tbl_clientes
        WHERE id_cliente = {$idCliente}
    ")->fetch_assoc();

    if (!$cl) {
        echo "  AVISO: id_cliente={$idCliente} no encontrado en tbl_clientes.\n";
        $notFound++;
        continue;
    }

    $idConsultor = (int)$cl['id_consultor'];
    $estandar    = $db->real_escape_string($cl['estandares'] ?? '');

    // Buscar agendamiento existente para este cliente en ese mes/año
    $ag = $db->query("
        SELECT id, fecha_visita
        FROM tbl_agendamientos
        WHERE id_cliente = {$idCliente}
          AND MONTH(fecha_visita) = {$mes}
          AND YEAR(fecha_visita) = {$anio}
          AND estado != 'cancelado'
        ORDER BY fecha_visita ASC
        LIMIT 1
    ")->fetch_assoc();

    $fechaAgendada  = $ag ? "'" . $db->real_escape_string($ag['fecha_visita']) . "'" : "NULL";
    $idAgendamiento = $ag ? (int)$ag['id'] : "NULL";

    // Buscar acta de visita completa
    $acta = $db->query("
        SELECT id, fecha_visita
        FROM tbl_acta_visita
        WHERE id_cliente = {$idCliente}
          AND MONTH(fecha_visita) = {$mes}
          AND YEAR(fecha_visita) = {$anio}
          AND estado = 'completo'
        ORDER BY fecha_visita ASC
        LIMIT 1
    ")->fetch_assoc();

    $fechaActa = $acta ? "'" . $db->real_escape_string($acta['fecha_visita']) . "'" : "NULL";
    $idActa    = $acta ? (int)$acta['id'] : "NULL";

    // Calcular estatus
    $estatusAgenda = "'pendiente'";
    $estatusMes    = "'pendiente'";

    if ($acta && $ag) {
        $estatusAgenda = ($acta['fecha_visita'] === $ag['fecha_visita']) ? "'cumple'" : "'incumple'";
        $estatusMes = "'cumple'";
    } elseif ($acta && !$ag) {
        $estatusMes = "'cumple'";
    } elseif (!$acta && $ag && strtotime($ag['fecha_visita']) < strtotime('today')) {
        $estatusAgenda = "'incumple'";
    }

    $sql = "INSERT INTO tbl_ciclos_visita
        (id_cliente, id_consultor, anio, mes_esperado, estandar,
         fecha_agendada, id_agendamiento, fecha_acta, id_acta,
         estatus_agenda, estatus_mes)
        VALUES
        ({$idCliente}, {$idConsultor}, {$anio}, {$mes}, '{$estandar}',
         {$fechaAgendada}, {$idAgendamiento}, {$fechaActa}, {$idActa},
         {$estatusAgenda}, {$estatusMes})";

    if ($db->query($sql)) {
        $inserted++;
    } else {
        echo "  ERROR en id_cliente={$idCliente}: " . $db->error . "\n";
        $errors++;
    }
}

echo "\n=== Resumen ===\n";
echo "Insertados: {$inserted}\n";
echo "Errores: {$errors}\n";
echo "No encontrados: {$notFound}\n";

// Mostrar estadísticas
$total = $db->query("SELECT COUNT(*) as cnt FROM tbl_ciclos_visita")->fetch_assoc()['cnt'];
$cumpleA = $db->query("SELECT COUNT(*) as cnt FROM tbl_ciclos_visita WHERE estatus_agenda = 'cumple'")->fetch_assoc()['cnt'];
$incumpleA = $db->query("SELECT COUNT(*) as cnt FROM tbl_ciclos_visita WHERE estatus_agenda = 'incumple'")->fetch_assoc()['cnt'];
$pendienteA = $db->query("SELECT COUNT(*) as cnt FROM tbl_ciclos_visita WHERE estatus_agenda = 'pendiente'")->fetch_assoc()['cnt'];
$cumpleM = $db->query("SELECT COUNT(*) as cnt FROM tbl_ciclos_visita WHERE estatus_mes = 'cumple'")->fetch_assoc()['cnt'];

echo "\nEstadísticas en tbl_ciclos_visita:\n";
echo "  Total: {$total}\n";
echo "  Estatus agenda: cumple={$cumpleA}, incumple={$incumpleA}, pendiente={$pendienteA}\n";
echo "  Estatus mes: cumple={$cumpleM}\n";

$db->close();
echo "\n=== Listo ===\n";
