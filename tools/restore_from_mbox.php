<?php
/**
 * Restaurar archivos adjuntos desde Google Takeout (.mbox)
 *
 * Este script:
 * 1. Lee archivos .mbox exportados de Gmail
 * 2. Extrae todos los adjuntos (PDF, JPG, PNG, XLSX, DOCX)
 * 3. Los organiza por NIT de cliente basándose en las etiquetas del email
 * 4. Los coloca en la carpeta de destino
 *
 * USO:
 *   php tools/restore_from_mbox.php /ruta/al/takeout/Mail/ /ruta/destino/soportes-clientes/
 *
 * REQUISITOS:
 *   - PHP 8.0+
 *   - Extensión mbstring
 */

if ($argc < 3) {
    echo "Uso: php restore_from_mbox.php <directorio_mbox> <directorio_destino>\n";
    echo "Ejemplo: php restore_from_mbox.php /tmp/takeout/Mail/ /www/soportes-clientes/\n";
    exit(1);
}

$mboxDir = rtrim($argv[1], '/\\');
$destDir = rtrim($argv[2], '/\\');

if (!is_dir($mboxDir)) {
    die("ERROR: Directorio de mbox no existe: $mboxDir\n");
}

if (!is_dir($destDir)) {
    mkdir($destDir, 0755, true);
    echo "Creado directorio destino: $destDir\n";
}

// Cargar mapeo de nombre de cliente → NIT
// Esto se puede generar desde la BD con:
// SELECT nombre_cliente, nit_cliente FROM tbl_clientes;
$mapeoFile = __DIR__ . '/mapeo_clientes_nit.json';

if (file_exists($mapeoFile)) {
    $mapeo = json_decode(file_get_contents($mapeoFile), true);
    echo "Cargado mapeo de " . count($mapeo) . " clientes\n";
} else {
    echo "ADVERTENCIA: No se encontró $mapeoFile\n";
    echo "Generando mapeo desde la base de datos...\n";
    $mapeo = generarMapeoDesdeDB();
    file_put_contents($mapeoFile, json_encode($mapeo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "Mapeo guardado en $mapeoFile (" . count($mapeo) . " clientes)\n";
}

// Estadísticas
$stats = [
    'emails_procesados' => 0,
    'adjuntos_extraidos' => 0,
    'adjuntos_sin_mapeo' => 0,
    'errores' => 0,
    'por_nit' => [],
];

// Buscar archivos .mbox
$mboxFiles = glob($mboxDir . '/*.mbox');
if (empty($mboxFiles)) {
    // Google Takeout puede tener subcarpetas
    $mboxFiles = glob($mboxDir . '/**/*.mbox');
}

if (empty($mboxFiles)) {
    die("ERROR: No se encontraron archivos .mbox en $mboxDir\n");
}

echo "\nEncontrados " . count($mboxFiles) . " archivos .mbox\n\n";

foreach ($mboxFiles as $mboxFile) {
    $label = basename($mboxFile, '.mbox');
    echo "=== Procesando: $label ===\n";

    procesarMbox($mboxFile, $label, $destDir, $mapeo, $stats);
}

// Reporte final
echo "\n========================================\n";
echo "  REPORTE DE RESTAURACIÓN\n";
echo "========================================\n";
echo "Emails procesados: {$stats['emails_procesados']}\n";
echo "Adjuntos extraídos: {$stats['adjuntos_extraidos']}\n";
echo "Adjuntos sin mapeo (guardados en 'sin-clasificar/'): {$stats['adjuntos_sin_mapeo']}\n";
echo "Errores: {$stats['errores']}\n";
echo "\nPor NIT:\n";

arsort($stats['por_nit']);
foreach ($stats['por_nit'] as $nit => $count) {
    echo "  $nit: $count archivos\n";
}

// Guardar reporte
$reportPath = $destDir . '/reporte_restauracion_' . date('Ymd_His') . '.json';
file_put_contents($reportPath, json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "\nReporte guardado en: $reportPath\n";

// ================================================================
// FUNCIONES
// ================================================================

function procesarMbox(string $mboxFile, string $label, string $destDir, array $mapeo, array &$stats): void
{
    $handle = fopen($mboxFile, 'r');
    if (!$handle) {
        echo "ERROR: No se puede abrir $mboxFile\n";
        $stats['errores']++;
        return;
    }

    $currentEmail = '';
    $inEmail = false;

    while (($line = fgets($handle)) !== false) {
        // Nuevo email comienza con "From " al inicio de línea
        if (str_starts_with($line, 'From ') && preg_match('/^From \S+/', $line)) {
            if ($inEmail && $currentEmail) {
                procesarEmail($currentEmail, $label, $destDir, $mapeo, $stats);
            }
            $currentEmail = '';
            $inEmail = true;
            continue;
        }

        if ($inEmail) {
            $currentEmail .= $line;
        }
    }

    // Procesar último email
    if ($inEmail && $currentEmail) {
        procesarEmail($currentEmail, $label, $destDir, $mapeo, $stats);
    }

    fclose($handle);
}

function procesarEmail(string $rawEmail, string $label, string $destDir, array $mapeo, array &$stats): void
{
    $stats['emails_procesados']++;

    // Extraer headers
    $headerEnd = strpos($rawEmail, "\r\n\r\n");
    if ($headerEnd === false) {
        $headerEnd = strpos($rawEmail, "\n\n");
    }
    if ($headerEnd === false) return;

    $headers = substr($rawEmail, 0, $headerEnd);
    $body = substr($rawEmail, $headerEnd);

    // Extraer etiquetas de Gmail (X-Gmail-Labels)
    $labels = [];
    if (preg_match('/^X-Gmail-Labels:\s*(.+)$/mi', $headers, $m)) {
        $labels = array_map('trim', explode(',', $m[1]));
    }

    // Determinar NIT del cliente
    $nit = determinarNit($label, $labels, $mapeo);

    // Verificar si es multipart con adjuntos
    if (!preg_match('/Content-Type:\s*multipart\//i', $headers)) {
        return; // No tiene adjuntos
    }

    // Extraer boundary
    if (!preg_match('/boundary="?([^"\s;]+)"?/i', $rawEmail, $bm)) {
        return;
    }
    $boundary = $bm[1];

    // Dividir en partes
    $parts = explode('--' . $boundary, $body);

    foreach ($parts as $part) {
        if (trim($part) === '' || trim($part) === '--') continue;

        // Buscar Content-Disposition: attachment
        if (!preg_match('/Content-Disposition:\s*attachment/i', $part)) {
            // También buscar inline con filename
            if (!preg_match('/filename[*]?=/i', $part)) {
                continue;
            }
        }

        // Extraer nombre del archivo
        $fileName = null;
        if (preg_match('/filename\*=(?:UTF-8\'\'|utf-8\'\')(.+?)(?:\r?\n|\s|;|$)/i', $part, $fnm)) {
            $fileName = urldecode(trim($fnm[1], '"'));
        } elseif (preg_match('/filename="?([^"\r\n;]+)"?/i', $part, $fnm)) {
            $fileName = trim($fnm[1], '"');
        }

        if (!$fileName) continue;

        // Filtrar solo tipos relevantes
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExts = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'xlsx', 'xls', 'docx', 'doc'];
        if (!in_array($ext, $allowedExts)) continue;

        // Extraer contenido codificado
        $partHeaderEnd = strpos($part, "\r\n\r\n");
        if ($partHeaderEnd === false) {
            $partHeaderEnd = strpos($part, "\n\n");
        }
        if ($partHeaderEnd === false) continue;

        $partHeaders = substr($part, 0, $partHeaderEnd);
        $partBody = substr($part, $partHeaderEnd + 4);

        // Decodificar
        if (preg_match('/Content-Transfer-Encoding:\s*base64/i', $partHeaders)) {
            $decoded = base64_decode(preg_replace('/\s+/', '', $partBody));
        } elseif (preg_match('/Content-Transfer-Encoding:\s*quoted-printable/i', $partHeaders)) {
            $decoded = quoted_printable_decode($partBody);
        } else {
            $decoded = $partBody;
        }

        if (empty($decoded)) continue;

        // Guardar archivo
        $targetDir = $nit ? "$destDir/$nit" : "$destDir/sin-clasificar";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        // Evitar colisiones de nombre
        $targetPath = "$targetDir/$fileName";
        if (file_exists($targetPath)) {
            $baseName = pathinfo($fileName, PATHINFO_FILENAME);
            $targetPath = "$targetDir/{$baseName}_" . substr(md5(microtime()), 0, 6) . ".$ext";
        }

        file_put_contents($targetPath, $decoded);
        $stats['adjuntos_extraidos']++;

        if ($nit) {
            $stats['por_nit'][$nit] = ($stats['por_nit'][$nit] ?? 0) + 1;
        } else {
            $stats['adjuntos_sin_mapeo']++;
        }

        $size = strlen($decoded);
        $sizeKB = round($size / 1024, 1);
        echo "  [$nit] $fileName ({$sizeKB} KB)\n";
    }
}

function determinarNit(string $mboxLabel, array $gmailLabels, array $mapeo): ?string
{
    // Intentar con la etiqueta del archivo mbox
    foreach ($mapeo as $nombre => $nit) {
        if (stripos($mboxLabel, $nombre) !== false) {
            return $nit;
        }
    }

    // Intentar con las etiquetas de Gmail del email
    foreach ($gmailLabels as $label) {
        foreach ($mapeo as $nombre => $nit) {
            if (stripos($label, $nombre) !== false) {
                return $nit;
            }
        }
    }

    // Intentar match parcial (primeras 3+ palabras)
    $mboxWords = preg_split('/\s+/', strtoupper($mboxLabel));
    foreach ($mapeo as $nombre => $nit) {
        $nombreWords = preg_split('/\s+/', strtoupper($nombre));
        $matchCount = 0;
        foreach ($mboxWords as $word) {
            if (strlen($word) > 2 && in_array($word, $nombreWords)) {
                $matchCount++;
            }
        }
        if ($matchCount >= 3) {
            return $nit;
        }
    }

    return null;
}

function generarMapeoDesdeDB(): array
{
    // Intentar conexión local
    $mysqli = @new mysqli('localhost', 'root', '', 'propiedad_horizontal');
    if ($mysqli->connect_error) {
        echo "No se pudo conectar a la BD local. Creando mapeo vacío.\n";
        echo "Edita manualmente tools/mapeo_clientes_nit.json con el formato:\n";
        echo '{"NOMBRE CLIENTE": "NIT", ...}' . "\n";
        return [];
    }

    $result = $mysqli->query("SELECT nombre_cliente, nit_cliente FROM tbl_clientes ORDER BY nombre_cliente");
    $mapeo = [];

    while ($row = $result->fetch_assoc()) {
        // Mapear nombre completo
        $mapeo[$row['nombre_cliente']] = $row['nit_cliente'];

        // También mapear versiones cortas comunes
        $nombre = $row['nombre_cliente'];
        // Quitar " - PROPIEDAD HORIZONTAL", " - PH", " PH"
        $corto = preg_replace('/\s*[-–]\s*(PROPIEDAD HORIZONTAL|PH)\s*$/i', '', $nombre);
        if ($corto !== $nombre) {
            $mapeo[$corto] = $row['nit_cliente'];
        }
    }

    $mysqli->close();
    return $mapeo;
}
