<?php
/**
 * Reparar nombres de archivos v2
 *
 * Genera un script bash en el servidor con todos los mv,
 * lo ejecuta allá, y luego actualiza la BD.
 */

$logFile    = 'D:/DESARROLLO/carga_produccion_log.csv';
$serverPath = '/www/wwwroot/phorizontal/enterprisesstph/writable/soportes-clientes';
$serverHost = 'root@66.29.154.174';
$baseUrl    = 'https://phorizontal.cycloidtalent.com/serve-file';

$patronPermitido = '/^[a-z0-9~%.:_\-]+$/iu';

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

function limpiarNombre(string $nombre): string
{
    $ext = pathinfo($nombre, PATHINFO_EXTENSION);
    $base = pathinfo($nombre, PATHINFO_FILENAME);
    $base = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $base);
    $base = preg_replace('/[^a-zA-Z0-9._\-]/', '_', $base);
    $base = preg_replace('/_+/', '_', $base);
    $base = trim($base, '_');
    if (empty($base)) {
        $base = 'doc_' . substr(md5($nombre), 0, 8);
    }
    return $base . '.' . strtolower($ext);
}

// ================================================================
// Leer log y encontrar problemáticos
// ================================================================
$fh = fopen($logFile, 'r');
fgetcsv($fh);
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
echo "Archivos a reparar: " . count($problemas) . "\n";

// ================================================================
// Generar mapeo nombre_viejo → nombre_nuevo (evitando colisiones)
// ================================================================
$mapeo = []; // id_reporte => [nit, viejo, nuevo]
$usados = []; // nit/nuevo => true

foreach ($problemas as $p) {
    $nuevo = limpiarNombre($p['archivo']);
    $key = $p['nit'] . '/' . $nuevo;

    // Evitar colisiones
    if (isset($usados[$key])) {
        $ext = pathinfo($nuevo, PATHINFO_EXTENSION);
        $base = pathinfo($nuevo, PATHINFO_FILENAME);
        $nuevo = $base . '_' . substr(md5($p['archivo']), 0, 6) . '.' . $ext;
        $key = $p['nit'] . '/' . $nuevo;
    }
    $usados[$key] = true;

    $mapeo[$p['id_reporte']] = [
        'nit'   => $p['nit'],
        'viejo' => $p['archivo'],
        'nuevo' => $nuevo,
    ];
}

// ================================================================
// Paso 1: Generar script bash de renames en el servidor
// ================================================================
echo "Generando script de renames...\n";

// Agrupar por NIT
$porNit = [];
foreach ($mapeo as $id => $m) {
    $porNit[$m['nit']][] = $m;
}

// Generar script bash
$bashScript = "#!/bin/bash\n";
$bashScript .= "# Rename script generado automáticamente\n";
$bashScript .= "cd $serverPath\n";
$bashScript .= "OK=0\nERR=0\n\n";

foreach ($porNit as $nit => $archivos) {
    $bashScript .= "# NIT: $nit\n";
    foreach ($archivos as $a) {
        $old = $nit . '/' . $a['viejo'];
        $new = $nit . '/' . $a['nuevo'];
        // Usar printf %q para escapar correctamente
        $bashScript .= 'if [ -f ' . bashEscape($old) . ' ]; then mv ' . bashEscape($old) . ' ' . bashEscape($new) . ' && OK=$((OK+1)) || ERR=$((ERR+1)); else ERR=$((ERR+1)); fi' . "\n";
    }
}

$bashScript .= "\necho \"Renames OK: \$OK\"\n";
$bashScript .= "echo \"Renames ERROR: \$ERR\"\n";

function bashEscape(string $s): string {
    // Usar comillas dobles con escape interno
    return '"' . str_replace(['\\', '"', '$', '`'], ['\\\\', '\\"', '\\$', '\\`'], $s) . '"';
}

// Guardar localmente
$localScript = 'D:/DESARROLLO/rename_server.sh';
file_put_contents($localScript, $bashScript);
echo "Script generado: " . strlen($bashScript) . " bytes, " . count($mapeo) . " renames\n";

// Subir al servidor
$remotScript = '/tmp/rename_pdfs.sh';
$scpCmd = 'scp ' . escapeshellarg($localScript) . ' ' . $serverHost . ':' . $remotScript . ' 2>&1';
echo shell_exec($scpCmd);

// Ejecutar en servidor
echo "Ejecutando renames en servidor...\n";
$out = shell_exec('ssh ' . $serverHost . ' "chmod +x ' . $remotScript . ' && bash ' . $remotScript . '" 2>&1');
echo $out . "\n";

// Corregir ownership
echo "Corrigiendo permisos...\n";
shell_exec('ssh ' . $serverHost . ' "find ' . $serverPath . ' -user root -exec chown www:www {} +" 2>&1');

// ================================================================
// Paso 2: Actualizar enlaces en BD
// ================================================================
echo "Actualizando BD...\n";
$db = conectarBD();
$stmt = $db->prepare("UPDATE tbl_reporte SET enlace = ? WHERE id_reporte = ?");

$okDb = 0;
$errDb = 0;
$total = count($mapeo);

foreach ($mapeo as $id => $m) {
    $nuevoEnlace = $baseUrl . '/' . $m['nit'] . '/' . rawurlencode($m['nuevo']);

    try {
        $stmt->bind_param('si', $nuevoEnlace, $id);
        $stmt->execute();
        $okDb++;
    } catch (\Exception $e) {
        echo "  Reconectando BD...\n";
        $db = conectarBD();
        $stmt = $db->prepare("UPDATE tbl_reporte SET enlace = ? WHERE id_reporte = ?");
        try {
            $stmt->bind_param('si', $nuevoEnlace, $id);
            $stmt->execute();
            $okDb++;
        } catch (\Exception $e2) {
            $errDb++;
            echo "  ERROR DB #$id: " . $e2->getMessage() . "\n";
        }
    }

    if ($okDb % 500 === 0) {
        echo "  BD: $okDb/$total actualizados\n";
    }
}

$stmt->close();
$db->close();

echo "\n========================================\n";
echo "  REPARACIÓN V2 COMPLETADA\n";
echo "========================================\n";
echo "Renames servidor: ver output arriba\n";
echo "BD actualizada: $okDb OK, $errDb errores\n";
