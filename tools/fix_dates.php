<?php
/**
 * Extrae fecha real de TODOS los PDFs en tbl_reporte via pdftotext
 * y actualiza created_at + updated_at.
 *
 * Uso (EN EL SERVIDOR):
 *   DB_PROD_PASS=xxx php tools/fix_dates.php --dry-run   (preview)
 *   DB_PROD_PASS=xxx php tools/fix_dates.php              (ejecutar)
 */

$dryRun = in_array('--dry-run', $argv ?? []);
echo $dryRun ? "=== MODO DRY-RUN (no se modifica nada) ===\n\n" : "=== MODO EJECUCIÓN ===\n\n";

// --- Conexión BD ---
$db = new mysqli();
$isServer = file_exists('/www/ca/ca-certificate_cycloid.crt');
if ($isServer) {
    $db->ssl_set(null, null, '/www/ca/ca-certificate_cycloid.crt', null, null);
} else {
    $db->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
}
$db->real_connect(
    'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
    'cycloid_userdb',
    getenv('DB_PROD_PASS'),
    'propiedad_horizontal',
    25060,
    null,
    MYSQLI_CLIENT_SSL
);
if ($db->connect_error) {
    die("Error conexión: " . $db->connect_error . "\n");
}

// --- Ruta base de archivos ---
$basePath = '/www/wwwroot/phorizontal/enterprisesstph/writable/soportes-clientes';
if (!is_dir($basePath)) {
    die("Error: directorio $basePath no existe. ¿Estás en el servidor?\n");
}

// --- Tablas de meses ---
$mesesEs = [
    'enero'=>1,'febrero'=>2,'marzo'=>3,'abril'=>4,'mayo'=>5,'junio'=>6,
    'julio'=>7,'agosto'=>8,'septiembre'=>9,'octubre'=>10,'noviembre'=>11,'diciembre'=>12
];
$mesesAbr = [
    'ene'=>1,'feb'=>2,'mar'=>3,'abr'=>4,'may'=>5,'jun'=>6,
    'jul'=>7,'ago'=>8,'sep'=>9,'oct'=>10,'nov'=>11,'dic'=>12
];

/**
 * Parsear un string de fecha corto (valor de campo "Fecha: xxx")
 */
function parsearFecha($str, $mesesEs, $mesesAbr) {
    // DD/MM/YYYY o DD-MM-YYYY
    if (preg_match('/(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{4})/', $str, $m)) {
        $a = (int)$m[1]; $b = (int)$m[2]; $y = (int)$m[3];
        if ($y >= 2020 && $y <= 2026) {
            if ($b > 12 && $a <= 12) return sprintf('%04d-%02d-%02d', $y, $a, $b);
            if ($b <= 12 && $a <= 31) return sprintf('%04d-%02d-%02d', $y, $b, $a);
        }
    }
    // DD de MMMM de YYYY
    if (preg_match('/(\d{1,2})\s+de\s+(\w+)\s+(?:de|del)\s+(\d{4})/iu', $str, $m)) {
        $mes = $mesesEs[mb_strtolower($m[2], 'UTF-8')] ?? null;
        if ($mes) {
            $d = (int)$m[1]; $y = (int)$m[3];
            if ($y >= 2020 && $y <= 2026 && $d >= 1 && $d <= 31) {
                return sprintf('%04d-%02d-%02d', $y, $mes, $d);
            }
        }
    }
    // YYYY-MM-DD
    if (preg_match('/(\d{4})-(\d{2})-(\d{2})/', $str, $m)) {
        $y = (int)$m[1];
        if ($y >= 2020 && $y <= 2026) return sprintf('%s-%s-%s', $m[1], $m[2], $m[3]);
    }
    return null;
}

/**
 * Extraer fecha de texto PDF. Retorna YYYY-MM-DD o null.
 * Estrategia: recoger TODAS las fechas de TODO el texto del PDF,
 * y devolver la más reciente que no sea futura.
 * La fecha del PDF es la única fuente de verdad.
 */
function extraerFechaDeTexto($texto, $mesesEs, $mesesAbr) {
    if (empty(trim($texto))) return null;

    $texto = str_replace(["\r\n", "\r"], "\n", $texto);
    $hoy = time();
    $fechas = [];

    // Helper para agregar fecha validada
    $agregarFecha = function($fechaStr) use (&$fechas, $hoy) {
        $ts = strtotime($fechaStr);
        if ($ts && $ts >= strtotime('2020-01-01') && $ts <= $hoy + 86400) {
            $fechas[] = $fechaStr;
        }
    };

    // 1. Campos explícitos "Fecha:", "FECHA:", "Date:"
    $patronesCampo = [
        '/(?:FECHA|Fecha)\s*(?:de\s*visita|del\s*documento|de\s*inspección|de\s*inspeccion|de\s*elaboración|de\s*elaboracion)?\s*:\s*(.{3,40})/iu',
        '/Date\s*:\s*(.{5,40})/i',
    ];
    foreach ($patronesCampo as $patron) {
        if (preg_match_all($patron, $texto, $matches)) {
            foreach ($matches[1] as $val) {
                $fecha = parsearFecha(trim($val), $mesesEs, $mesesAbr);
                if ($fecha) $agregarFecha($fecha);
            }
        }
    }

    // 2. DD de MMMM de YYYY (español)
    if (preg_match_all('/(\d{1,2})\s+de\s+(enero|febrero|marzo|abril|mayo|junio|julio|agosto|septiembre|octubre|noviembre|diciembre)\s+(?:de|del)\s+(\d{4})/iu', $texto, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $m) {
            $mes = $mesesEs[mb_strtolower($m[2], 'UTF-8')] ?? null;
            if ($mes) {
                $d = (int)$m[1]; $y = (int)$m[3];
                if ($d >= 1 && $d <= 31) {
                    $agregarFecha(sprintf('%04d-%02d-%02d', $y, $mes, $d));
                }
            }
        }
    }

    // 3. MMMM DD, YYYY o MMMM DD de YYYY
    if (preg_match_all('/(enero|febrero|marzo|abril|mayo|junio|julio|agosto|septiembre|octubre|noviembre|diciembre)\s+(\d{1,2})(?:\s*,\s*|\s+de\s+)(\d{4})/iu', $texto, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $m) {
            $mes = $mesesEs[mb_strtolower($m[1], 'UTF-8')] ?? null;
            if ($mes) {
                $d = (int)$m[2]; $y = (int)$m[3];
                if ($d >= 1 && $d <= 31) {
                    $agregarFecha(sprintf('%04d-%02d-%02d', $y, $mes, $d));
                }
            }
        }
    }

    // 4. MMMM YYYY o MMMM-YYYY (solo mes y año → día 15)
    if (preg_match_all('/(enero|febrero|marzo|abril|mayo|junio|julio|agosto|septiembre|octubre|noviembre|diciembre)[\s\-\/]+(\d{4})/iu', $texto, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $m) {
            $mes = $mesesEs[mb_strtolower($m[1], 'UTF-8')] ?? null;
            if ($mes) {
                $agregarFecha(sprintf('%04d-%02d-15', (int)$m[2], $mes));
            }
        }
    }

    // 5. MES abreviado: ENE 2024, DIC-2025
    if (preg_match_all('/\b(ene|feb|mar|abr|may|jun|jul|ago|sep|oct|nov|dic)[\s\-\/]+(\d{4})/iu', $texto, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $m) {
            $mes = $mesesAbr[mb_strtolower($m[1], 'UTF-8')] ?? null;
            if ($mes) {
                $agregarFecha(sprintf('%04d-%02d-15', (int)$m[2], $mes));
            }
        }
    }

    // 6. DD/MM/YYYY o DD-MM-YYYY o DD.MM.YYYY
    if (preg_match_all('/(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{4})/', $texto, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $m) {
            $a = (int)$m[1]; $b = (int)$m[2]; $y = (int)$m[3];
            if ($b > 12 && $a <= 12) {
                $agregarFecha(sprintf('%04d-%02d-%02d', $y, $a, $b));
            } elseif ($b <= 12 && $a <= 31) {
                $agregarFecha(sprintf('%04d-%02d-%02d', $y, $b, $a));
            }
        }
    }

    // 7. YYYY-MM-DD (ISO)
    if (preg_match_all('/(\d{4})-(\d{2})-(\d{2})/', $texto, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $m) {
            $agregarFecha(sprintf('%s-%s-%s', $m[1], $m[2], $m[3]));
        }
    }

    // 8. DD_MM_YYYY
    if (preg_match_all('/(\d{1,2})_(\d{2})_(\d{4})/', $texto, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $m) {
            $agregarFecha(sprintf('%s-%s-%02d', $m[3], $m[2], (int)$m[1]));
        }
    }

    if (empty($fechas)) return null;

    // Devolver la más reciente
    usort($fechas, function($a, $b) {
        return strtotime($b) - strtotime($a);
    });
    return $fechas[0];
}

// =============================================================
// EJECUCIÓN PRINCIPAL
// =============================================================

$result = $db->query("SELECT id_reporte, titulo_reporte, enlace, report_url, created_at FROM tbl_reporte ORDER BY id_reporte");
$totalRegistros = $result->num_rows;
echo "Total registros en tbl_reporte: $totalRegistros\n\n";

$stmt = $db->prepare("UPDATE tbl_reporte SET created_at=?, updated_at=? WHERE id_reporte=?");

$corregidos = 0;
$sinFecha = 0;
$sinArchivo = 0;
$pdfVacio = 0;
$errores = 0;
$noPdf = 0;
$sinFechaLista = [];

$i = 0;
while ($row = $result->fetch_assoc()) {
    $i++;
    $id = $row['id_reporte'];
    $titulo = substr($row['titulo_reporte'] ?? '', 0, 55);
    $enlace = $row['enlace'] ?: $row['report_url'];

    if (!$enlace) {
        echo "[$i/$totalRegistros] ID $id $titulo → SIN ENLACE\n";
        $sinArchivo++;
        continue;
    }

    // Resolver ruta: quitar prefijo serve-file/ si existe
    $rutaRelativa = preg_replace('#^.*?serve-file/#', '', $enlace);
    $rutaCompleta = $basePath . '/' . $rutaRelativa;

    if (!file_exists($rutaCompleta)) {
        echo "[$i/$totalRegistros] ID $id $titulo → ARCHIVO NO EXISTE: $rutaRelativa\n";
        $sinArchivo++;
        continue;
    }

    // Solo PDFs
    $ext = strtolower(pathinfo($rutaCompleta, PATHINFO_EXTENSION));
    if ($ext !== 'pdf') {
        $noPdf++;
        continue;
    }

    // Extraer texto con pdftotext
    $cmd = 'pdftotext ' . escapeshellarg($rutaCompleta) . ' - 2>/dev/null';
    $textoPdf = shell_exec($cmd);

    if (empty(trim($textoPdf ?? ''))) {
        echo "[$i/$totalRegistros] ID $id $titulo → PDF VACÍO (sin texto)\n";
        $pdfVacio++;
        $sinFechaLista[] = $row;
        continue;
    }

    // Extraer fecha del contenido del PDF
    $fecha = extraerFechaDeTexto($textoPdf, $mesesEs, $mesesAbr);

    if (!$fecha) {
        echo "[$i/$totalRegistros] ID $id $titulo → SIN FECHA en PDF\n";
        $sinFecha++;
        $sinFechaLista[] = $row;
        continue;
    }

    // Validar rango
    $ts = strtotime($fecha);
    if (!$ts || $ts < strtotime('2020-01-01') || $ts > strtotime('2027-01-01')) {
        echo "[$i/$totalRegistros] ID $id $titulo → FECHA FUERA DE RANGO: $fecha\n";
        $sinFecha++;
        $sinFechaLista[] = $row;
        continue;
    }

    $dt = date('Y-m-d H:i:s', strtotime($fecha . ' 12:00:00'));

    echo "[$i/$totalRegistros] ID $id $titulo → $dt\n";

    if (!$dryRun) {
        $stmt->bind_param('ssi', $dt, $dt, $id);
        if (!$stmt->execute()) {
            echo "  ERROR SQL: " . $stmt->error . "\n";
            $errores++;
            continue;
        }
    }
    $corregidos++;
}

$stmt->close();

echo "\n=== RESUMEN ===\n";
echo "Total registros:           $totalRegistros\n";
echo "Actualizados:              $corregidos" . ($dryRun ? " (dry-run)" : "") . "\n";
echo "No PDF (html/png/otro):    $noPdf\n";
echo "Sin archivo / no existe:   $sinArchivo\n";
echo "PDF vacío (sin texto):     $pdfVacio\n";
echo "Sin fecha encontrada:      $sinFecha\n";
echo "Errores SQL:               $errores\n";

if (!empty($sinFechaLista) && count($sinFechaLista) <= 50) {
    echo "\n--- Registros sin fecha (revisión manual) ---\n";
    foreach ($sinFechaLista as $r) {
        echo "  ID {$r['id_reporte']}: {$r['titulo_reporte']}\n";
    }
}

$db->close();
echo "\nCompletado.\n";
