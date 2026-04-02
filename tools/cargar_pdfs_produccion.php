<?php
/**
 * Cargar PDFs clasificados al servidor de producción
 *
 * Uso: php tools/cargar_pdfs_produccion.php
 *
 * 1. Lee clasificacion_pdfs.csv (solo filas clasificadas correctamente)
 * 2. Sube cada PDF via SCP a writable/soportes-clientes/{NIT}/
 * 3. Inserta registro en tbl_reporte
 */

$csvFile    = 'D:/DESARROLLO/clasificacion_pdfs.csv';
$pdfDir     = 'D:/DESARROLLO/pdfs_extraidos';
$serverPath = '/www/wwwroot/phorizontal/enterprisesstph/writable/soportes-clientes';
$serverHost = 'root@66.29.154.174';
$baseUrl    = 'https://phorizontal.cycloidtalent.com/serve-file';

// Tipos a excluir
$EXCLUIR = ['OTRO', 'ILEGIBLE', 'PARA REVISION MANUAL'];

// ================================================================
// Conectar a BD producción
// ================================================================
$db = new mysqli();
$db->ssl_set(null, null, null, null, null);
$db->real_connect(
    'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
    'cycloid_userdb',
    'AVNS_MR2SLvzRh3i_7o9fEHN',
    'propiedad_horizontal',
    25060,
    null,
    MYSQLI_CLIENT_SSL
);
if ($db->connect_error) {
    die("ERROR BD: " . $db->connect_error . "\n");
}
$db->set_charset('utf8mb4');
echo "Conectado a BD PRODUCCIÓN\n";

// Cargar nombres de clientes para el titulo_reporte
$clienteNombres = [];
$r = $db->query("SELECT id_cliente, nombre_cliente FROM tbl_clientes");
while ($row = $r->fetch_assoc()) {
    $clienteNombres[(int)$row['id_cliente']] = $row['nombre_cliente'];
}

// ================================================================
// Leer CSV
// ================================================================
$filas = [];
$fh = fopen($csvFile, 'r');
$header = fgetcsv($fh);
// carpeta,archivo,nit_cliente,id_cliente,tipo_documento,id_report_type,id_detailreport,fecha_documento,confianza,fecha_email
while ($row = fgetcsv($fh)) {
    $tipo = $row[4];
    if (in_array($tipo, $EXCLUIR)) continue;
    // Verificar que tiene id_cliente y nit válidos
    if (empty($row[3]) || $row[3] == '0' || empty($row[2])) continue;
    $filas[] = [
        'carpeta'          => $row[0],
        'archivo'          => $row[1],
        'nit_cliente'      => $row[2],
        'id_cliente'       => (int)$row[3],
        'tipo_documento'   => $row[4],
        'id_report_type'   => (int)$row[5],
        'id_detailreport'  => (int)$row[6],
        'fecha_documento'  => $row[7] ?? '',
        'confianza'        => $row[8] ?? '',
        'fecha_email'      => $row[9] ?? '',
    ];
}
fclose($fh);
echo "Filas a cargar: " . count($filas) . "\n";

// ================================================================
// Soporte retomar: leer log de ya cargados
// ================================================================
$logFile = 'D:/DESARROLLO/carga_produccion_log.csv';
$yaCargados = [];
if (file_exists($logFile)) {
    $lh = fopen($logFile, 'r');
    fgetcsv($lh); // header
    while ($row = fgetcsv($lh)) {
        if (isset($row[0], $row[1])) {
            $yaCargados[$row[0] . '/' . $row[1]] = true;
        }
    }
    fclose($lh);
    echo "Retomando: " . count($yaCargados) . " ya cargados\n";
}

$escribirLogHeader = !file_exists($logFile) || count($yaCargados) === 0;
$logHandle = fopen($logFile, count($yaCargados) > 0 ? 'a' : 'w');
if ($escribirLogHeader) {
    fputcsv($logHandle, ['carpeta', 'archivo', 'nit_cliente', 'id_reporte', 'resultado']);
}

// ================================================================
// Crear carpetas NIT en servidor (batch)
// ================================================================
$nitsUnicos = array_unique(array_column($filas, 'nit_cliente'));
echo "Creando " . count($nitsUnicos) . " carpetas NIT en servidor...\n";
$mkdirCmds = [];
foreach ($nitsUnicos as $nit) {
    $mkdirCmds[] = "mkdir -p $serverPath/$nit && chown www:www $serverPath/$nit";
}
// Ejecutar en batches de 20
foreach (array_chunk($mkdirCmds, 20) as $batch) {
    $cmd = 'ssh ' . $serverHost . ' "' . implode(' && ', $batch) . '" 2>&1';
    shell_exec($cmd);
}
echo "Carpetas creadas.\n\n";

// ================================================================
// Funciones de conexión BD
// ================================================================
$SQL_INSERT = "INSERT INTO tbl_reporte (titulo_reporte, id_detailreport, enlace, estado, observaciones, id_cliente, created_at, updated_at, id_report_type) VALUES (?, ?, ?, 'CERRADO', 'Recuperado desde Takeout + clasificado por IA', ?, ?, ?, ?)";

function reconectarBD(): mysqli {
    $db = new mysqli();
    $db->ssl_set(null, null, null, null, null);
    $db->real_connect(
        'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'cycloid_userdb',
        'AVNS_MR2SLvzRh3i_7o9fEHN',
        'propiedad_horizontal',
        25060,
        null,
        MYSQLI_CLIENT_SSL
    );
    $db->set_charset('utf8mb4');
    return $db;
}

// Reconectar BD después de crear carpetas (la conexión puede haber muerto)
$db = reconectarBD();
echo "BD reconectada.\n";
$stmt = $db->prepare($SQL_INSERT);

// ================================================================
// PROCESAR
// ================================================================
$total = count($filas);
$ok = 0;
$errores = 0;
$skipped = count($yaCargados);

foreach ($filas as $i => $fila) {
    $key = $fila['carpeta'] . '/' . $fila['archivo'];
    if (isset($yaCargados[$key])) continue;

    $num = $skipped + $ok + $errores + 1;

    // Ruta local del PDF
    $localPath = $pdfDir . '/' . $fila['carpeta'] . '/' . $fila['archivo'];
    if (!file_exists($localPath)) {
        echo "[$num/$total] ERROR archivo no existe: {$fila['archivo']}\n";
        fputcsv($logHandle, [$fila['carpeta'], $fila['archivo'], $fila['nit_cliente'], 0, 'ARCHIVO_NO_EXISTE']);
        $errores++;
        continue;
    }

    // Fecha: fecha_documento si existe, sino fecha_email
    $fecha = $fila['fecha_documento'];
    if (empty($fecha) || strlen($fecha) < 8) {
        $fecha = $fila['fecha_email'];
    }
    if (empty($fecha)) {
        $fecha = date('Y-m-d H:i:s');
    }
    // Normalizar fecha a datetime
    if (strlen($fecha) === 10) {
        $fecha = $fecha . ' 00:00:00';
    }

    // Titulo
    $nombreCliente = $clienteNombres[$fila['id_cliente']] ?? 'CLIENTE ' . $fila['id_cliente'];
    $titulo = $fila['tipo_documento'] . ' - ' . $nombreCliente;

    // Enlace
    $enlace = $baseUrl . '/' . $fila['nit_cliente'] . '/' . rawurlencode($fila['archivo']);

    // 1. SCP: subir PDF al servidor
    $remotePath = $serverPath . '/' . $fila['nit_cliente'] . '/' . $fila['archivo'];
    $scpCmd = 'scp ' . escapeshellarg($localPath) . ' ' . escapeshellarg($serverHost . ':' . $remotePath) . ' 2>&1';
    $scpOut = shell_exec($scpCmd);

    if ($scpOut !== null && $scpOut !== '' && stripos($scpOut, 'error') !== false) {
        echo "[$num/$total] ERROR SCP: $scpOut\n";
        fputcsv($logHandle, [$fila['carpeta'], $fila['archivo'], $fila['nit_cliente'], 0, 'SCP_ERROR']);
        $errores++;
        continue;
    }

    // Corregir owner del archivo subido
    $chownCmd = 'ssh ' . $serverHost . ' "chown www:www ' . escapeshellarg($remotePath) . '" 2>&1';
    shell_exec($chownCmd);

    // 2. INSERT en tbl_reporte (con retry por reconexión)
    $inserted = false;
    for ($retry = 0; $retry < 3; $retry++) {
        try {
            $stmt->bind_param('sissssi',
                $titulo,
                $fila['id_detailreport'],
                $enlace,
                $fila['id_cliente'],
                $fecha,
                $fecha,
                $fila['id_report_type']
            );
            if ($stmt->execute()) {
                $idReporte = $stmt->insert_id;
                $ok++;
                fputcsv($logHandle, [$fila['carpeta'], $fila['archivo'], $fila['nit_cliente'], $idReporte, 'OK']);
                echo "[$num/$total] OK #$idReporte | {$fila['tipo_documento']} | {$fila['nit_cliente']} | $fecha\n";
                $inserted = true;
                break;
            }
        } catch (\Exception $e) {
            echo "  BD desconectada, reconectando (retry $retry)...\n";
            sleep(3);
            $db = reconectarBD();
            $stmt = $db->prepare($SQL_INSERT);
        }
    }
    if (!$inserted) {
        $errores++;
        fputcsv($logHandle, [$fila['carpeta'], $fila['archivo'], $fila['nit_cliente'], 0, 'DB_ERROR_RECONNECT_FAILED']);
        echo "[$num/$total] ERROR DB: reconexión fallida\n";
    }

    // Flush log cada 50
    if (($ok + $errores) % 50 === 0) {
        fflush($logHandle);
        echo "--- Progreso: $ok OK, $errores errores de $total ---\n";
    }
}

$stmt->close();
$db->close();
fclose($logHandle);

echo "\n========================================\n";
echo "  CARGA COMPLETADA\n";
echo "========================================\n";
echo "Total filas: $total\n";
echo "Subidos OK: $ok\n";
echo "Errores: $errores\n";
echo "Ya cargados (skip): $skipped\n";
echo "Log: $logFile\n";
