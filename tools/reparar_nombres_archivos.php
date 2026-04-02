<?php
/**
 * Reparar nombres de archivos con caracteres no permitidos por CI4
 *
 * 1. Lee carga_produccion_log.csv
 * 2. Para cada archivo con caracteres problemáticos:
 *    a. Genera nombre limpio (solo a-z0-9._-)
 *    b. Renombra en servidor via SSH
 *    c. Actualiza enlace en tbl_reporte
 */

$logFile    = 'D:/DESARROLLO/carga_produccion_log.csv';
$serverPath = '/www/wwwroot/phorizontal/enterprisesstph/writable/soportes-clientes';
$serverHost = 'root@66.29.154.174';
$baseUrl    = 'https://phorizontal.cycloidtalent.com/serve-file';

// Patrón de caracteres permitidos por CI4
$patronPermitido = '/^[a-z0-9~%.:_\-]+$/iu';

// ================================================================
// Conectar BD
// ================================================================
function conectarBD(): mysqli {
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

$db = conectarBD();
echo "Conectado a BD\n";

// ================================================================
// Leer log y encontrar problemáticos
// ================================================================
$fh = fopen($logFile, 'r');
fgetcsv($fh); // header
$problemas = [];
while ($row = fgetcsv($fh)) {
    if (($row[4] ?? '') !== 'OK') continue;
    $archivo = $row[1];
    if (!preg_match($patronPermitido, $archivo)) {
        $problemas[] = [
            'nit'        => $row[2],
            'archivo'    => $archivo,
            'id_reporte' => (int)$row[3],
        ];
    }
}
fclose($fh);
echo "Archivos a reparar: " . count($problemas) . "\n\n";

// ================================================================
// Log de reparación (para retomar)
// ================================================================
$repairLog = 'D:/DESARROLLO/reparacion_nombres_log.csv';
$yaReparados = [];
if (file_exists($repairLog)) {
    $rh = fopen($repairLog, 'r');
    fgetcsv($rh);
    while ($row = fgetcsv($rh)) {
        $yaReparados[(int)$row[0]] = true;
    }
    fclose($rh);
    echo "Retomando: " . count($yaReparados) . " ya reparados\n";
}

$escribirHeader = !file_exists($repairLog) || count($yaReparados) === 0;
$rlh = fopen($repairLog, count($yaReparados) > 0 ? 'a' : 'w');
if ($escribirHeader) {
    fputcsv($rlh, ['id_reporte', 'nit', 'nombre_original', 'nombre_nuevo', 'resultado']);
}

// ================================================================
// Función para limpiar nombre
// ================================================================
function limpiarNombre(string $nombre): string
{
    // Separar nombre y extensión
    $ext = pathinfo($nombre, PATHINFO_EXTENSION);
    $base = pathinfo($nombre, PATHINFO_FILENAME);

    // Transliterar tildes y caracteres especiales
    $base = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $base);

    // Reemplazar cualquier carácter no permitido por guion bajo
    $base = preg_replace('/[^a-zA-Z0-9._\-]/', '_', $base);

    // Colapsar guiones bajos múltiples
    $base = preg_replace('/_+/', '_', $base);

    // Quitar guiones bajos al inicio y final
    $base = trim($base, '_');

    // Si quedó vacío, usar hash
    if (empty($base)) {
        $base = 'doc_' . substr(md5($nombre), 0, 8);
    }

    return $base . '.' . strtolower($ext);
}

// ================================================================
// Procesar en batches de SSH (más eficiente que uno por uno)
// ================================================================
$total = count($problemas);
$okCount = 0;
$errCount = 0;
$batchSize = 10;
$stmtUpdate = $db->prepare("UPDATE tbl_reporte SET enlace = ? WHERE id_reporte = ?");

// Agrupar por NIT para reducir conexiones SSH
$porNit = [];
foreach ($problemas as $p) {
    if (isset($yaReparados[$p['id_reporte']])) continue;
    $porNit[$p['nit']][] = $p;
}

$procesados = count($yaReparados);
foreach ($porNit as $nit => $archivos) {
    // Batch rename commands para este NIT
    $renameCmds = [];
    $updateData = [];

    foreach ($archivos as $a) {
        $nombreNuevo = limpiarNombre($a['archivo']);

        // Evitar colisiones: si ya existe, agregar hash
        $existentes = array_column($updateData, 'nuevo');
        if (in_array($nombreNuevo, $existentes)) {
            $ext = pathinfo($nombreNuevo, PATHINFO_EXTENSION);
            $base = pathinfo($nombreNuevo, PATHINFO_FILENAME);
            $nombreNuevo = $base . '_' . substr(md5($a['archivo']), 0, 6) . '.' . $ext;
        }

        $oldPath = $serverPath . '/' . $nit . '/' . $a['archivo'];
        $newPath = $serverPath . '/' . $nit . '/' . $nombreNuevo;

        $renameCmds[] = [
            'cmd' => 'mv ' . escapeshellarg($oldPath) . ' ' . escapeshellarg($newPath),
            'id'  => $a['id_reporte'],
            'old' => $a['archivo'],
            'new' => $nombreNuevo,
            'nit' => $nit,
        ];

        $updateData[] = [
            'id'    => $a['id_reporte'],
            'nuevo' => $nombreNuevo,
            'nit'   => $nit,
            'old'   => $a['archivo'],
        ];
    }

    // Ejecutar renames en batches
    foreach (array_chunk($renameCmds, $batchSize) as $batch) {
        $cmds = array_map(fn($b) => $b['cmd'], $batch);
        $sshCmd = 'ssh ' . $serverHost . ' ' . escapeshellarg(implode(' ; ', $cmds)) . ' 2>&1';
        $out = shell_exec($sshCmd);

        // Actualizar BD para cada archivo del batch
        foreach ($batch as $b) {
            $procesados++;
            $nuevoEnlace = $baseUrl . '/' . $b['nit'] . '/' . rawurlencode($b['new']);

            try {
                $stmtUpdate->bind_param('si', $nuevoEnlace, $b['id']);
                $stmtUpdate->execute();
                $okCount++;
                fputcsv($rlh, [$b['id'], $b['nit'], $b['old'], $b['new'], 'OK']);
                echo "[$procesados/$total] OK #" . $b['id'] . " | " . $b['new'] . "\n";
            } catch (\Exception $e) {
                echo "  BD reconectando...\n";
                $db = conectarBD();
                $stmtUpdate = $db->prepare("UPDATE tbl_reporte SET enlace = ? WHERE id_reporte = ?");
                try {
                    $stmtUpdate->bind_param('si', $nuevoEnlace, $b['id']);
                    $stmtUpdate->execute();
                    $okCount++;
                    fputcsv($rlh, [$b['id'], $b['nit'], $b['old'], $b['new'], 'OK']);
                    echo "[$procesados/$total] OK #" . $b['id'] . " | " . $b['new'] . "\n";
                } catch (\Exception $e2) {
                    $errCount++;
                    fputcsv($rlh, [$b['id'], $b['nit'], $b['old'], $b['new'], 'DB_ERROR']);
                    echo "[$procesados/$total] ERROR DB #" . $b['id'] . "\n";
                }
            }
        }

        if ($procesados % 100 === 0) {
            fflush($rlh);
            echo "--- Progreso: $okCount OK, $errCount errores ---\n";
        }
    }
}

$stmtUpdate->close();
$db->close();
fclose($rlh);

echo "\n========================================\n";
echo "  REPARACIÓN COMPLETADA\n";
echo "========================================\n";
echo "Reparados OK: $okCount\n";
echo "Errores: $errCount\n";
echo "Log: $repairLog\n";
