<?php
/**
 * Extrae TODOS los adjuntos PDF de los mbox del Takeout
 * y los guarda en carpetas por cliente con su nombre original.
 * También genera un CSV de inventario con: cliente, archivo, fecha_email, subject
 *
 * NO toca la BD. NO sube nada al servidor. Solo extrae a disco local.
 */

$mboxDir = $argv[1] ?? '';
$outputDir = $argv[2] ?? '';

if (!$mboxDir || !$outputDir) {
    die("Uso: php extraer_pdfs_takeout.php <mbox_dir> <output_dir>\n");
}

if (!is_dir($outputDir)) {
    mkdir($outputDir, 0777, true);
}

$mboxFiles = glob($mboxDir . '/*.mbox');
$mboxFiles = array_filter($mboxFiles, function($f) {
    $name = basename($f);
    return stripos($name, 'Abierto') === false;
});
sort($mboxFiles);

echo "=== EXTRACCIÓN DE PDFs DEL TAKEOUT ===\n";
echo "Mbox: " . count($mboxFiles) . "\n";
echo "Output: $outputDir\n\n";

// CSV de inventario
$csvFile = $outputDir . '/inventario_takeout.csv';
$csv = fopen($csvFile, 'w');
fputcsv($csv, ['carpeta_mbox', 'archivo', 'fecha_email', 'subject', 'tamaño_bytes']);

$stats = ['pdfs' => 0, 'otros' => 0, 'emails' => 0, 'duplicados' => 0];

foreach ($mboxFiles as $mboxFile) {
    $label = basename($mboxFile, '.mbox');

    // Crear carpeta para este cliente
    $clientDir = $outputDir . '/' . preg_replace('/[<>:"\/\\|?*]/', '_', $label);
    if (!is_dir($clientDir)) {
        mkdir($clientDir, 0777, true);
    }

    echo "=== [$label] ===\n";

    // Leer mbox línea por línea
    $fh = fopen($mboxFile, 'r');
    if (!$fh) continue;

    $rawEmail = '';
    $emailsEnArchivo = 0;
    $pdfsEnArchivo = 0;

    while (true) {
        $line = fgets($fh);
        $isEnd = ($line === false);
        $isNewEmail = (!$isEnd && preg_match('/^From \S+ .+$/', $line));

        if (($isNewEmail || $isEnd) && strlen($rawEmail) > 100) {
            // Procesar email
            $stats['emails']++;
            $emailsEnArchivo++;

            $result = procesarEmail($rawEmail, $clientDir, $label, $csv, $stats);
            $pdfsEnArchivo += $result;

            $rawEmail = '';
        }

        if ($isEnd) break;

        if (!$isNewEmail) {
            $rawEmail .= $line;
        }
    }
    fclose($fh);

    echo "  Emails: $emailsEnArchivo, PDFs: $pdfsEnArchivo\n";
}

fclose($csv);

echo "\n========================================\n";
echo "Total emails procesados: {$stats['emails']}\n";
echo "Total PDFs extraídos: {$stats['pdfs']}\n";
echo "Otros archivos (png/jpg/docx): {$stats['otros']}\n";
echo "Duplicados saltados: {$stats['duplicados']}\n";
echo "Inventario: $csvFile\n";

function procesarEmail(string $rawEmail, string $clientDir, string $label, $csv, array &$stats): int {
    // Headers
    $headerEnd = strpos($rawEmail, "\r\n\r\n");
    if ($headerEnd === false) $headerEnd = strpos($rawEmail, "\n\n");
    if ($headerEnd === false) return 0;
    $headers = substr($rawEmail, 0, $headerEnd);

    // Fecha
    $fechaEmail = '';
    if (preg_match('/^Date:\s*(.+)$/mi', $headers, $m)) {
        $fechaEmail = trim($m[1]);
        $ts = strtotime($fechaEmail);
        if ($ts) $fechaEmail = date('Y-m-d H:i:s', $ts);
    }

    // Subject
    $subject = '';
    if (preg_match('/^Subject:\s*(.+)$/mi', $headers, $m)) {
        $subject = trim($m[1]);
        if (strpos($subject, '=?') !== false) {
            $decoded = mb_decode_mimeheader($subject);
            if ($decoded) $subject = $decoded;
        }
        // Subject puede ser multilinea
        $subject = preg_replace('/\s+/', ' ', $subject);
    }

    // Buscar boundary
    if (!preg_match('/boundary="?([^"\s;]+)"?/i', $rawEmail, $bm)) return 0;
    $boundary = $bm[1];

    // Extraer partes
    $parts = explode('--' . $boundary, $rawEmail);
    $pdfsExtraidos = 0;

    foreach ($parts as $part) {
        if (!preg_match('/filename[*]?=/i', $part)) continue;

        // Nombre del archivo
        $fileName = null;
        if (preg_match('/filename\*=(?:UTF-8\'\'|utf-8\'\')(.+?)(?:\r?\n|\s|;|$)/i', $part, $fnm)) {
            $fileName = urldecode(trim($fnm[1], '"'));
        } elseif (preg_match('/filename="?([^"\r\n;]+)"?/i', $part, $fnm)) {
            $fileName = trim($fnm[1], '"');
        }
        if (!$fileName) continue;

        // Decodificar nombres MIME encoded (=?UTF-8?B?...?=)
        if (strpos($fileName, '=?') !== false) {
            $decoded = mb_decode_mimeheader($fileName);
            if ($decoded && $decoded !== $fileName) $fileName = $decoded;
        }

        // Limpiar caracteres problemáticos del nombre
        $fileName = preg_replace('/[<>:"\/\\|?*]/', '_', $fileName);
        $fileName = trim($fileName);

        // Asegurar extensión .pdf
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (!$ext) $fileName .= '.pdf';

        // Extraer body del part
        $partHeaderEnd = strpos($part, "\r\n\r\n");
        if ($partHeaderEnd === false) $partHeaderEnd = strpos($part, "\n\n");
        if ($partHeaderEnd === false) continue;
        $partBody = substr($part, $partHeaderEnd + 4);

        // Decodificar
        if (preg_match('/Content-Transfer-Encoding:\s*base64/i', $part)) {
            $decoded = base64_decode(preg_replace('/\s+/', '', $partBody));
        } elseif (preg_match('/Content-Transfer-Encoding:\s*quoted-printable/i', $part)) {
            $decoded = quoted_printable_decode($partBody);
        } else {
            $decoded = $partBody;
        }

        if (!$decoded || strlen($decoded) < 100) continue;

        // SOLO guardar si es PDF real (magic bytes %PDF)
        $isPdf = (substr($decoded, 0, 4) === '%PDF');

        if ($isPdf) {
            // Forzar extensión .pdf si no la tiene
            if (strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) !== 'pdf') {
                $fileName = pathinfo($fileName, PATHINFO_FILENAME) . '.pdf';
            }
            // Evitar duplicados
            $destPath = $clientDir . '/' . $fileName;
            if (file_exists($destPath)) {
                // Agregar sufijo
                $base = pathinfo($fileName, PATHINFO_FILENAME);
                $destPath = $clientDir . '/' . $base . '_' . substr(md5($decoded), 0, 6) . '.pdf';
                if (file_exists($destPath)) {
                    $stats['duplicados']++;
                    continue;
                }
            }

            file_put_contents($destPath, $decoded);
            $stats['pdfs']++;
            $pdfsExtraidos++;

            // Registrar en CSV
            fputcsv($csv, [$label, $fileName, $fechaEmail, $subject, strlen($decoded)]);
        } else {
            $stats['otros']++;
        }
    }

    return $pdfsExtraidos;
}
