<?php
/**
 * Script CLI: Operaciones masivas sobre clientes inactivos
 *
 * PASO 1 — Activar cliente específico (id_cliente = 43)
 * PASO 2 — Cerrar TODAS las actividades de los clientes con estado='inactivo'
 *           en las 4 tablas: tbl_pta_cliente, tbl_cronog_capacitacion,
 *           tbl_pendientes, tbl_vencimientos_mantenimientos
 *
 * Uso:
 *   php app/CLI/cerrar_actividades_inactivos.php local
 *   php app/CLI/cerrar_actividades_inactivos.php prod TU_PASSWORD
 */

$entorno = $argv[1] ?? null;

if (!in_array($entorno, ['local', 'prod'])) {
    echo "\n[ERROR] Debes especificar el entorno: local o prod\n";
    echo "Ejemplo: php app/CLI/cerrar_actividades_inactivos.php local\n";
    echo "         php app/CLI/cerrar_actividades_inactivos.php prod TU_PASSWORD\n\n";
    exit(1);
}

// ─── Credenciales ─────────────────────────────────────────────────────────────
if ($entorno === 'local') {
    $host   = '127.0.0.1';
    $port   = '3306';
    $dbname = 'propiedad_horizontal';
    $user   = 'root';
    $pass   = '';
    $ssl    = false;
} else {
    $host   = 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com';
    $port   = '25060';
    $dbname = 'propiedad_horizontal';
    $user   = 'cycloid_userdb';
    $pass   = $argv[2] ?? getenv('PROD_DB_PASS') ?: '';
    $ssl    = true;
    if (empty($pass)) {
        echo "\n[ERROR] Para prod debes pasar la contraseña como 2do argumento:\n";
        echo "  php app/CLI/cerrar_actividades_inactivos.php prod TU_PASSWORD\n\n";
        exit(1);
    }
}

// ─── Conexión PDO ─────────────────────────────────────────────────────────────
$dsn     = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
$opciones = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
if ($ssl) {
    $opciones[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    $opciones[PDO::MYSQL_ATTR_SSL_CA]                 = true;
}

echo "\n======================================================\n";
echo "  OPERACIONES MASIVAS — CLIENTES INACTIVOS\n";
echo "  Entorno: " . strtoupper($entorno) . "\n";
echo "  Host:    {$host}:{$port}\n";
echo "  BD:      {$dbname}\n";
echo "======================================================\n\n";

try {
    $pdo = new PDO($dsn, $user, $pass, $opciones);
    echo "[OK] Conexión establecida con {$host}\n\n";
} catch (PDOException $e) {
    echo "[ERROR] No se pudo conectar: " . $e->getMessage() . "\n";
    exit(1);
}

// ─── Verificar si tbl_vencimientos_mantenimientos existe ──────────────────────
$vencimientosExiste = $pdo->query("SHOW TABLES LIKE 'tbl_vencimientos_mantenimientos'")->rowCount() > 0;

// ─── PASO 1: Activar cliente 43 ───────────────────────────────────────────────
echo "══════════════════════════════════════════════════════\n";
echo "  PASO 1 — Activar cliente ID 43\n";
echo "══════════════════════════════════════════════════════\n\n";

$cliente43 = $pdo->query("SELECT id_cliente, nombre_cliente, nit_cliente, estado FROM tbl_clientes WHERE id_cliente = 43")->fetch();

if (!$cliente43) {
    echo "  [AVISO] Cliente 43 no existe en este entorno — se omite.\n\n";
} else {
    echo "  Cliente encontrado:\n";
    echo "    ID:     " . $cliente43['id_cliente'] . "\n";
    echo "    Nombre: " . $cliente43['nombre_cliente'] . "\n";
    echo "    NIT:    " . $cliente43['nit_cliente'] . "\n";
    echo "    Estado: " . $cliente43['estado'] . "\n\n";

    if ($cliente43['estado'] === 'activo') {
        echo "  [INFO] Ya está en estado 'activo'. No se requiere cambio.\n\n";
    } else {
        $stmt = $pdo->prepare("UPDATE tbl_clientes SET estado = 'activo' WHERE id_cliente = 43");
        $stmt->execute();
        echo "  [OK] Cliente 43 actualizado a estado = 'activo'. (" . $stmt->rowCount() . " fila(s) afectada(s))\n\n";
    }
}

// ─── PASO 2: Cerrar actividades de todos los inactivos ───────────────────────
echo "══════════════════════════════════════════════════════\n";
echo "  PASO 2 — Cerrar actividades de clientes inactivos\n";
echo "══════════════════════════════════════════════════════\n\n";

// Listar quiénes son los inactivos AHORA (tras activar el 43)
$inactivos = $pdo->query("SELECT id_cliente, nombre_cliente, nit_cliente FROM tbl_clientes WHERE estado = 'inactivo' ORDER BY nombre_cliente")->fetchAll();

if (empty($inactivos)) {
    echo "  [INFO] No hay clientes con estado 'inactivo'. Nada que cerrar.\n\n";
    echo "======================================================\n";
    echo "  RESULTADO: ÉXITO — Nada que cerrar en PASO 2.\n";
    echo "======================================================\n\n";
    exit(0);
}

echo "  Clientes inactivos a procesar (" . count($inactivos) . "):\n";
foreach ($inactivos as $c) {
    echo "    · [" . $c['id_cliente'] . "] " . $c['nombre_cliente'] . " (NIT " . $c['nit_cliente'] . ")\n";
}
echo "\n";

$errores = 0;

// tbl_pta_cliente
echo "→ Actualizando tbl_pta_cliente...\n";
try {
    $stmt = $pdo->prepare("
        UPDATE tbl_pta_cliente
        SET estado_actividad = 'CERRADA POR FIN CONTRATO'
        WHERE id_cliente IN (SELECT id_cliente FROM tbl_clientes WHERE estado = 'inactivo')
    ");
    $stmt->execute();
    echo "  [OK] " . $stmt->rowCount() . " fila(s) actualizadas.\n\n";
} catch (PDOException $e) {
    echo "  [ERROR] " . $e->getMessage() . "\n\n";
    $errores++;
}

// tbl_cronog_capacitacion
echo "→ Actualizando tbl_cronog_capacitacion...\n";
try {
    $stmt = $pdo->prepare("
        UPDATE tbl_cronog_capacitacion
        SET estado = 'CERRADA POR FIN CONTRATO'
        WHERE id_cliente IN (SELECT id_cliente FROM tbl_clientes WHERE estado = 'inactivo')
    ");
    $stmt->execute();
    echo "  [OK] " . $stmt->rowCount() . " fila(s) actualizadas.\n\n";
} catch (PDOException $e) {
    echo "  [ERROR] " . $e->getMessage() . "\n\n";
    $errores++;
}

// tbl_pendientes
echo "→ Actualizando tbl_pendientes...\n";
try {
    $stmt = $pdo->prepare("
        UPDATE tbl_pendientes
        SET estado = 'CERRADA POR FIN CONTRATO'
        WHERE id_cliente IN (SELECT id_cliente FROM tbl_clientes WHERE estado = 'inactivo')
    ");
    $stmt->execute();
    echo "  [OK] " . $stmt->rowCount() . " fila(s) actualizadas.\n\n";
} catch (PDOException $e) {
    echo "  [ERROR] " . $e->getMessage() . "\n\n";
    $errores++;
}

// tbl_vencimientos_mantenimientos (puede no existir en todos los entornos)
if ($vencimientosExiste) {
    echo "→ Actualizando tbl_vencimientos_mantenimientos...\n";
    try {
        $stmt = $pdo->prepare("
            UPDATE tbl_vencimientos_mantenimientos
            SET estado_actividad = 'CERRADA POR FIN CONTRATO'
            WHERE id_cliente IN (SELECT id_cliente FROM tbl_clientes WHERE estado = 'inactivo')
        ");
        $stmt->execute();
        echo "  [OK] " . $stmt->rowCount() . " fila(s) actualizadas.\n\n";
    } catch (PDOException $e) {
        echo "  [ERROR] " . $e->getMessage() . "\n\n";
        $errores++;
    }
} else {
    echo "→ tbl_vencimientos_mantenimientos no existe en este entorno — se omite.\n\n";
}

// ─── Resultado ────────────────────────────────────────────────────────────────
echo "======================================================\n";
if ($errores === 0) {
    echo "  RESULTADO: ÉXITO TOTAL\n";
    echo "  · Cliente 43 activado (si correspondía)\n";
    echo "  · " . count($inactivos) . " cliente(s) inactivo(s) procesados\n";
    echo "  · Actividades marcadas como CERRADA POR FIN CONTRATO\n";
} else {
    echo "  RESULTADO: {$errores} ERROR(ES) — revisa los mensajes anteriores.\n";
}
echo "======================================================\n\n";

exit($errores > 0 ? 1 : 0);
