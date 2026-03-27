<?php
/**
 * Corrige created_at y updated_at de tbl_reporte
 * 2 fuentes de fecha en orden de prioridad:
 *   1. Fecha extraída del titulo_reporte
 *   2. Header Date del email en mbox (match por Subject vs titulo_reporte)
 * Solo afecta registros con DATE(created_at) >= '2026-03-26'
 *
 * Uso local:  DB_PROD_PASS=xxx php -d memory_limit=2G tools/fix_fechas_reportlist.php
 */

$db = new mysqli();
$db->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
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

// --- Tablas de meses ---
$meses = [
    'ENERO'=>1,'FEBRERO'=>2,'MARZO'=>3,'ABRIL'=>4,'MAYO'=>5,'JUNIO'=>6,
    'JULIO'=>7,'AGOSTO'=>8,'SEPTIEMBRE'=>9,'OCTUBRE'=>10,'NOVIEMBRE'=>11,'DICIEMBRE'=>12
];
$mesesAbr = [
    'ENE'=>1,'FEB'=>2,'MAR'=>3,'ABR'=>4,'MAY'=>5,'JUN'=>6,
    'JUL'=>7,'AGO'=>8,'SEP'=>9,'OCT'=>10,'NOV'=>11,'DIC'=>12
];
$mesesEn = [
    'jan'=>1,'feb'=>2,'mar'=>3,'apr'=>4,'may'=>5,'jun'=>6,
    'jul'=>7,'aug'=>8,'sep'=>9,'oct'=>10,'nov'=>11,'dec'=>12
];

// =============================================================
// FUENTE 1: Extraer fecha del título
// =============================================================
function extraerFechaDelTitulo($titulo, $meses, $mesesAbr, $mesesEn) {
    // 1. ___dd/mm/yyyy o ___mm/dd/yyyy (AppSheets)
    if (preg_match('/___\s*(\d{1,2})\/(\d{1,2})\/(\d{2,4})/', $titulo, $m)) {
        $a = (int)$m[1]; $b = (int)$m[2]; $y = $m[3];
        if (strlen($y) === 2) $y = '20' . $y;
        $y = (int)$y;
        if ($b > 12 && $a <= 12) {
            return sprintf('%04d-%02d-%02d', $y, $a, $b);
        }
        return sprintf('%04d-%02d-%02d', $y, $b, $a);
    }

    // 2. // MES YYYY abreviado (// DIC 2023)
    if (preg_match('/\/\/\s*(ENE|FEB|MAR|ABR|MAY|JUN|JUL|AGO|SEP|OCT|NOV|DIC)\s+(\d{4})/i', $titulo, $m)) {
        $mes = $mesesAbr[strtoupper($m[1])] ?? null;
        if ($mes) return sprintf('%s-%02d-15', $m[2], $mes);
    }
    // // MES YYYY completo (// MARZO 2025)
    if (preg_match('/\/\/\s*(ENERO|FEBRERO|MARZO|ABRIL|MAYO|JUNIO|JULIO|AGOSTO|SEPTIEMBRE|OCTUBRE|NOVIEMBRE|DICIEMBRE)\s+(\d{4})/i', $titulo, $m)) {
        $mes = $meses[strtoupper($m[1])] ?? null;
        if ($mes) return sprintf('%s-%02d-15', $m[2], $mes);
    }

    // 3. // YYYYMMDD o // YYYYDDMM (// 20241601)
    if (preg_match('/\/\/\s*(\d{4})(\d{2})(\d{2})/', $titulo, $m)) {
        $y = (int)$m[1]; $a = (int)$m[2]; $b = (int)$m[3];
        if ($y >= 2020 && $y <= 2026) {
            if ($a > 12 && $b <= 12) {
                return sprintf('%04d-%02d-%02d', $y, $b, $a);
            }
            if ($a <= 12 && $b <= 31) {
                return sprintf('%04d-%02d-%02d', $y, $a, $b);
            }
        }
    }

    // 4. dd/mm/yyyy suelto
    if (preg_match('/(\d{1,2})\/(\d{1,2})\/(\d{4})/', $titulo, $m)) {
        $a = (int)$m[1]; $b = (int)$m[2]; $y = (int)$m[3];
        if ($b > 12 && $a <= 12) {
            return sprintf('%04d-%02d-%02d', $y, $a, $b);
        }
        return sprintf('%04d-%02d-%02d', $y, $b, $a);
    }

    // 5. dd_mm_yyyy
    if (preg_match('/(\d{1,2})_(\d{2})_(\d{4})/', $titulo, $m)) {
        return sprintf('%s-%s-%02d', $m[3], $m[2], $m[1]);
    }

    // 6. YYYY-MM-DD (ISO)
    if (preg_match('/(\d{4})-(\d{2})-(\d{2})/', $titulo, $m)) {
        $y = (int)$m[1];
        if ($y >= 2020 && $y <= 2026) {
            return sprintf('%s-%s-%s', $m[1], $m[2], $m[3]);
        }
    }

    // 7. MES AÑO completo (NOVIEMBRE-2025, JUNIO 2024)
    if (preg_match('/(ENERO|FEBRERO|MARZO|ABRIL|MAYO|JUNIO|JULIO|AGOSTO|SEPTIEMBRE|OCTUBRE|NOVIEMBRE|DICIEMBRE)[- ](\d{4})/i', $titulo, $m)) {
        $mes = $meses[strtoupper($m[1])] ?? null;
        if ($mes) return sprintf('%s-%02d-15', $m[2], $mes);
    }

    // 8. MES abreviado español suelto (DIC 2023)
    if (preg_match('/(ENE|FEB|MAR|ABR|MAY|JUN|JUL|AGO|SEP|OCT|NOV|DIC)[- ](\d{4})/i', $titulo, $m)) {
        $mes = $mesesAbr[strtoupper($m[1])] ?? null;
        if ($mes) return sprintf('%s-%02d-15', $m[2], $mes);
    }

    // 9. Jan-2026, Feb-2026 (inglés abreviado)
    if (preg_match('/(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)-(\d{4})/i', $titulo, $m)) {
        $mes = $mesesEn[strtolower($m[1])] ?? null;
        if ($mes) return sprintf('%s-%02d-15', $m[2], $mes);
    }

    // 10. Año suelto al final
    if (preg_match('/ (202[0-6])(?:\.pdf)?$/i', $titulo, $m)) {
        return $m[1] . '-06-15';
    }

    return null;
}

// =============================================================
// FUENTE 2: Índice Subject→Date desde archivos mbox
// =============================================================
function normalizarParaMatch($str) {
    $str = mb_strtolower($str, 'UTF-8');
    // Quitar prefijos de reenvío
    $str = preg_replace('/^(fwd?|re):\s*/i', '', $str);
    // Quitar separadores AppSheets y limpiar
    $str = preg_replace('/___.*$/', '', $str);
    $str = str_replace(['__', '//', '_'], [' ', ' ', ' '], $str);
    $str = preg_replace('/\s+/', ' ', $str);
    return trim($str);
}

function construirIndiceMbox($mboxDir) {
    $indice = []; // subject_normalizado => fecha
    $archivos = glob($mboxDir . '/*.mbox');
    $totalMbox = count($archivos);
    echo "Construyendo índice Subject→Date desde $totalMbox archivos mbox...\n";

    $procesados = 0;
    foreach ($archivos as $mboxFile) {
        $procesados++;
        $fh = fopen($mboxFile, 'r');
        if (!$fh) continue;

        $currentDate = null;
        $currentSubject = null;
        $enHeaders = false;
        $continuacionSubject = false;

        while (($linea = fgets($fh)) !== false) {
            // Inicio de nuevo email
            if (strpos($linea, 'From ') === 0) {
                // Guardar email anterior si tiene subject y date
                if ($currentSubject && $currentDate) {
                    $key = normalizarParaMatch($currentSubject);
                    if ($key !== '') {
                        $indice[$key] = $currentDate;
                    }
                }
                $enHeaders = true;
                $currentDate = null;
                $currentSubject = null;
                $continuacionSubject = false;
                continue;
            }

            if (!$enHeaders) continue;

            // Línea vacía = fin de headers
            if (trim($linea) === '') {
                // Guardar último email del bloque
                if ($currentSubject && $currentDate) {
                    $key = normalizarParaMatch($currentSubject);
                    if ($key !== '') {
                        $indice[$key] = $currentDate;
                    }
                }
                $enHeaders = false;
                $continuacionSubject = false;
                continue;
            }

            // Continuación de header (línea que empieza con espacio/tab)
            if ($continuacionSubject && preg_match('/^[\s\t]+(.+)/', $linea, $cm)) {
                $currentSubject .= ' ' . trim($cm[1]);
                continue;
            }
            $continuacionSubject = false;

            // Date header
            if (preg_match('/^Date:\s*(.+)/i', $linea, $dm)) {
                $parsed = strtotime(trim($dm[1]));
                if ($parsed) {
                    $currentDate = date('Y-m-d H:i:s', $parsed);
                }
                continue;
            }

            // Subject header
            if (preg_match('/^Subject:\s*(.*)/i', $linea, $sm)) {
                $raw = trim($sm[1]);
                // Decodificar MIME encoded words
                if (preg_match('/=\?/', $raw)) {
                    $decoded = mb_decode_mimeheader($raw);
                    if ($decoded) $raw = $decoded;
                }
                $currentSubject = $raw;
                $continuacionSubject = true;
                continue;
            }
        }
        // Último email del archivo
        if ($currentSubject && $currentDate) {
            $key = normalizarParaMatch($currentSubject);
            if ($key !== '') {
                $indice[$key] = $currentDate;
            }
        }

        fclose($fh);

        if ($procesados % 10 === 0) {
            echo "  Procesados $procesados/$totalMbox mbox (" . count($indice) . " subjects indexados)\n";
        }
    }
    echo "Índice completado: " . count($indice) . " subjects mapeados\n\n";
    return $indice;
}

// =============================================================
// EJECUCIÓN PRINCIPAL
// =============================================================
$result = $db->query("SELECT id_reporte, titulo_reporte, enlace, report_url, created_at FROM tbl_reporte WHERE DATE(created_at) >= '2026-03-26'");
$totalRegistros = $result->num_rows;
echo "Registros a procesar: $totalRegistros\n\n";

$stmt = $db->prepare("UPDATE tbl_reporte SET created_at=?, updated_at=? WHERE id_reporte=?");

$fixedTitulo = 0;
$fixedMbox = 0;
$nodate = 0;

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

// --- Fuente 1: título ---
echo "=== FUENTE 1: Fecha del título ===\n";
$pendientes = [];
$examplesT = [];

foreach ($rows as $row) {
    $fecha = extraerFechaDelTitulo($row['titulo_reporte'], $meses, $mesesAbr, $mesesEn);

    if ($fecha && strtotime($fecha) && strtotime($fecha) > strtotime('2020-01-01') && strtotime($fecha) < strtotime('2027-01-01')) {
        $dt = date('Y-m-d H:i:s', strtotime($fecha . ' 12:00:00'));
        $stmt->bind_param('ssi', $dt, $dt, $row['id_reporte']);
        $stmt->execute();
        $fixedTitulo++;
        if (count($examplesT) < 5) {
            $examplesT[] = "  ID {$row['id_reporte']}: {$row['created_at']} → $dt | " . substr($row['titulo_reporte'], 0, 60);
        }
    } else {
        $pendientes[] = $row;
    }
}
echo "Corregidos por título: $fixedTitulo\n";
if (!empty($examplesT)) {
    echo "Ejemplos:\n";
    foreach ($examplesT as $e) echo "$e\n";
}
echo "Pendientes para mbox: " . count($pendientes) . "\n";

// --- Fuente 2: mbox por Subject ---
$mboxDir = '';
$posiblesDirs = [
    '/c/tmp/takeout/Takeout/Correo electrónico',
    'c:/tmp/takeout/Takeout/Correo electrónico',
    '/tmp/takeout/Takeout/Correo electrónico',
];
foreach ($posiblesDirs as $dir) {
    if (is_dir($dir)) {
        $mboxDir = $dir;
        break;
    }
}

echo "\n=== FUENTE 2: Date header de mbox (match por Subject) ===\n";
if (!empty($pendientes) && $mboxDir) {
    $indice = construirIndiceMbox($mboxDir);
    $examplesM = [];
    $noMatchExamples = [];

    foreach ($pendientes as $row) {
        $tituloNorm = normalizarParaMatch($row['titulo_reporte']);

        $fechaEncontrada = null;

        // Match exacto normalizado
        if (isset($indice[$tituloNorm])) {
            $fechaEncontrada = $indice[$tituloNorm];
        }

        // Match parcial: el subject del mbox contiene el título o viceversa
        if (!$fechaEncontrada && strlen($tituloNorm) >= 10) {
            foreach ($indice as $subjectNorm => $fecha) {
                if (strpos($subjectNorm, $tituloNorm) !== false || strpos($tituloNorm, $subjectNorm) !== false) {
                    $fechaEncontrada = $fecha;
                    break;
                }
            }
        }

        if ($fechaEncontrada) {
            // Validar rango
            $ts = strtotime($fechaEncontrada);
            if ($ts && $ts > strtotime('2020-01-01') && $ts < strtotime('2027-01-01')) {
                $stmt->bind_param('ssi', $fechaEncontrada, $fechaEncontrada, $row['id_reporte']);
                $stmt->execute();
                $fixedMbox++;
                if (count($examplesM) < 10) {
                    $examplesM[] = "  ID {$row['id_reporte']}: {$row['created_at']} → $fechaEncontrada | " . substr($row['titulo_reporte'], 0, 55);
                }
            } else {
                $nodate++;
            }
        } else {
            $nodate++;
            if (count($noMatchExamples) < 10) {
                $noMatchExamples[] = "  ID {$row['id_reporte']}: " . substr($row['titulo_reporte'], 0, 80);
            }
        }
    }
    echo "Corregidos por mbox: $fixedMbox\n";
    if (!empty($examplesM)) {
        echo "Ejemplos:\n";
        foreach ($examplesM as $e) echo "$e\n";
    }
    if (!empty($noMatchExamples)) {
        echo "\nSin match en mbox:\n";
        foreach ($noMatchExamples as $e) echo "$e\n";
    }
} elseif (empty($pendientes)) {
    echo "No quedan pendientes.\n";
} else {
    echo "Directorio mbox no encontrado. Pendientes: " . count($pendientes) . "\n";
    $nodate = count($pendientes);
}

$stmt->close();

echo "\n=== RESUMEN FINAL ===\n";
echo "Total procesados:        $totalRegistros\n";
echo "Corregidos por título:   $fixedTitulo\n";
echo "Corregidos por mbox:     $fixedMbox\n";
echo "Total corregidos:        " . ($fixedTitulo + $fixedMbox) . "\n";
echo "Sin fecha (sin cambios): $nodate\n";

$db->close();
echo "\nCompletado.\n";
