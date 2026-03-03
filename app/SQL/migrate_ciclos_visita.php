<?php
/**
 * Migración: Crear tbl_ciclos_visita + seed desde tbl_clientes.mes_prox_visita
 *             + DROP COLUMN mes_prox_visita de tbl_clientes
 *
 * Uso:
 *   Local:      php app/SQL/migrate_ciclos_visita.php
 *   Producción: php app/SQL/migrate_ciclos_visita.php production
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
    echo "ERROR de conexión: " . $db->connect_error . "\n";
    exit(1);
}

$db->set_charset('utf8mb4');

// ─── PASO 1: Crear tabla tbl_ciclos_visita ───
echo "\n[1/5] Creando tabla tbl_ciclos_visita...\n";

$result = $db->query("SHOW TABLES LIKE 'tbl_ciclos_visita'");
if ($result->num_rows > 0) {
    echo "  Tabla ya existe. Saltando creación.\n";
} else {
    $sql = "CREATE TABLE tbl_ciclos_visita (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_cliente INT NOT NULL,
        id_consultor INT NOT NULL,
        anio INT NOT NULL,
        mes_esperado INT NOT NULL,
        estandar VARCHAR(50) NULL,
        fecha_agendada DATE NULL,
        id_agendamiento INT NULL,
        fecha_acta DATE NULL,
        id_acta INT NULL,
        estatus_agenda ENUM('pendiente','cumple','incumple') DEFAULT 'pendiente',
        estatus_mes ENUM('pendiente','cumple','incumple') DEFAULT 'pendiente',
        alerta_enviada TINYINT(1) DEFAULT 0,
        confirmacion_enviada TINYINT(1) DEFAULT 0,
        observaciones TEXT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_cliente (id_cliente),
        INDEX idx_consultor (id_consultor),
        INDEX idx_mes_anio (mes_esperado, anio),
        INDEX idx_estatus_agenda (estatus_agenda),
        INDEX idx_estatus_mes (estatus_mes)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    if ($db->query($sql)) {
        echo "  Tabla creada exitosamente.\n";
    } else {
        echo "  ERROR al crear tabla: " . $db->error . "\n";
        exit(1);
    }
}

// ─── PASO 2: Verificar si hay columna mes_prox_visita en tbl_clientes ───
echo "\n[2/5] Verificando columna mes_prox_visita en tbl_clientes...\n";

$colExists = $db->query("SHOW COLUMNS FROM tbl_clientes LIKE 'mes_prox_visita'");
if ($colExists->num_rows === 0) {
    echo "  Columna mes_prox_visita NO existe en tbl_clientes. Nada que migrar.\n";
    $hayDatosSemilla = false;
} else {
    $hayDatosSemilla = true;
    echo "  Columna encontrada.\n";
}

// ─── PASO 3: Migrar datos semilla ───
echo "\n[3/5] Migrando datos semilla...\n";

// Verificar si ya hay registros (evitar duplicados en re-ejecución)
$existing = $db->query("SELECT COUNT(*) as cnt FROM tbl_ciclos_visita");
$existingCount = $existing->fetch_assoc()['cnt'];

if ($existingCount > 0) {
    echo "  Ya hay {$existingCount} registros en tbl_ciclos_visita. Saltando seed.\n";
} elseif ($hayDatosSemilla) {
    // Obtener clientes con mes_prox_visita
    $clientes = $db->query("
        SELECT id_cliente, id_consultor, mes_prox_visita, estandares
        FROM tbl_clientes
        WHERE mes_prox_visita IS NOT NULL AND estado = 'activo'
    ");

    $inserted = 0;
    $anioActual = (int)date('Y');

    while ($row = $clientes->fetch_assoc()) {
        $idCliente   = (int)$row['id_cliente'];
        $idConsultor = (int)$row['id_consultor'];
        $mes         = (int)$row['mes_prox_visita'];
        $estandar    = $db->real_escape_string($row['estandares'] ?? '');

        // Buscar agendamiento existente para este cliente en ese mes/año
        $agStmt = $db->query("
            SELECT id, fecha_visita
            FROM tbl_agendamientos
            WHERE id_cliente = {$idCliente}
              AND MONTH(fecha_visita) = {$mes}
              AND YEAR(fecha_visita) = {$anioActual}
              AND estado != 'cancelado'
            ORDER BY fecha_visita ASC
            LIMIT 1
        ");
        $ag = $agStmt->fetch_assoc();
        $fechaAgendada  = $ag ? "'" . $db->real_escape_string($ag['fecha_visita']) . "'" : "NULL";
        $idAgendamiento = $ag ? (int)$ag['id'] : "NULL";

        // Buscar acta de visita completa para ese cliente en ese mes/año
        $actaStmt = $db->query("
            SELECT id, fecha_visita
            FROM tbl_acta_visita
            WHERE id_cliente = {$idCliente}
              AND MONTH(fecha_visita) = {$mes}
              AND YEAR(fecha_visita) = {$anioActual}
              AND estado = 'completo'
            ORDER BY fecha_visita ASC
            LIMIT 1
        ");
        $acta = $actaStmt->fetch_assoc();
        $fechaActa = $acta ? "'" . $db->real_escape_string($acta['fecha_visita']) . "'" : "NULL";
        $idActa    = $acta ? (int)$acta['id'] : "NULL";

        // Calcular estatus
        $estatusAgenda = "'pendiente'";
        $estatusMes    = "'pendiente'";

        if ($acta && $ag) {
            // Hay acta y agendamiento: comparar fechas para estatus_agenda
            $estatusAgenda = ($acta['fecha_visita'] === $ag['fecha_visita']) ? "'cumple'" : "'incumple'";
            $estatusMes = "'cumple'"; // Hay acta en el mes → mes cumple
        } elseif ($acta && !$ag) {
            // Hay acta pero no agendamiento
            $estatusAgenda = "'pendiente'"; // No había agenda, no aplica
            $estatusMes = "'cumple'";
        } elseif (!$acta && $ag) {
            // Hay agendamiento pero no acta
            $fechaAg = $ag['fecha_visita'];
            if (strtotime($fechaAg) < strtotime('today')) {
                $estatusAgenda = "'incumple'"; // Ya pasó y no fue
            }
            // estatus_mes se determina al cierre del mes
        }

        $sql = "INSERT INTO tbl_ciclos_visita
            (id_cliente, id_consultor, anio, mes_esperado, estandar,
             fecha_agendada, id_agendamiento, fecha_acta, id_acta,
             estatus_agenda, estatus_mes)
            VALUES
            ({$idCliente}, {$idConsultor}, {$anioActual}, {$mes}, '{$estandar}',
             {$fechaAgendada}, {$idAgendamiento}, {$fechaActa}, {$idActa},
             {$estatusAgenda}, {$estatusMes})";

        if ($db->query($sql)) {
            $inserted++;
        } else {
            echo "  ERROR insertando cliente {$idCliente}: " . $db->error . "\n";
        }
    }

    echo "  {$inserted} registros insertados.\n";
} else {
    echo "  Sin datos semilla disponibles.\n";
}

// ─── PASO 4: DROP COLUMN mes_prox_visita de tbl_clientes ───
echo "\n[4/5] Eliminando columna mes_prox_visita de tbl_clientes...\n";

$colCheck = $db->query("SHOW COLUMNS FROM tbl_clientes LIKE 'mes_prox_visita'");
if ($colCheck->num_rows === 0) {
    echo "  Columna ya no existe. OK.\n";
} else {
    if ($db->query("ALTER TABLE tbl_clientes DROP COLUMN mes_prox_visita")) {
        echo "  Columna eliminada exitosamente.\n";
    } else {
        echo "  ERROR al eliminar columna: " . $db->error . "\n";
    }
}

// ─── PASO 5: Resumen ───
echo "\n[5/5] Resumen final...\n";
$total = $db->query("SELECT COUNT(*) as cnt FROM tbl_ciclos_visita")->fetch_assoc()['cnt'];
$cumpleA = $db->query("SELECT COUNT(*) as cnt FROM tbl_ciclos_visita WHERE estatus_agenda = 'cumple'")->fetch_assoc()['cnt'];
$incumpleA = $db->query("SELECT COUNT(*) as cnt FROM tbl_ciclos_visita WHERE estatus_agenda = 'incumple'")->fetch_assoc()['cnt'];
$pendienteA = $db->query("SELECT COUNT(*) as cnt FROM tbl_ciclos_visita WHERE estatus_agenda = 'pendiente'")->fetch_assoc()['cnt'];
$cumpleM = $db->query("SELECT COUNT(*) as cnt FROM tbl_ciclos_visita WHERE estatus_mes = 'cumple'")->fetch_assoc()['cnt'];

echo "  Total registros: {$total}\n";
echo "  Estatus agenda: cumple={$cumpleA}, incumple={$incumpleA}, pendiente={$pendienteA}\n";
echo "  Estatus mes: cumple={$cumpleM}\n";

$colFinal = $db->query("SHOW COLUMNS FROM tbl_clientes LIKE 'mes_prox_visita'");
echo "  Columna mes_prox_visita en tbl_clientes: " . ($colFinal->num_rows > 0 ? "AÚN EXISTE" : "ELIMINADA") . "\n";

$db->close();
echo "\n=== Listo ===\n";
