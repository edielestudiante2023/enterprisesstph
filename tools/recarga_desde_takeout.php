<?php
/**
 * Recarga masiva de documentos desde Google Takeout (.mbox)
 *
 * Procesa cada .mbox, extrae adjuntos PDF/JPG/PNG, y los sube
 * via POST /api/bulk-report-upload con la fecha original del email.
 *
 * USO:
 *   php tools/recarga_desde_takeout.php <directorio_mbox> <url_base> <token>
 *
 * EJEMPLO:
 *   php tools/recarga_desde_takeout.php "c:/tmp/takeout/Takeout/Correo electrónico" http://localhost/enterprisesstph/public MITOKEN123
 *   php tools/recarga_desde_takeout.php "/tmp/takeout/Takeout/Correo electrónico" https://phorizontal.cycloidtalent.com MITOKEN123
 */

if ($argc < 4) {
    echo "Uso: php recarga_desde_takeout.php <directorio_mbox> <url_base> <token>\n";
    exit(1);
}

$mboxDir = $argv[1];
$baseUrl = rtrim($argv[2], '/');
$token   = $argv[3];

if (!is_dir($mboxDir)) {
    die("ERROR: Directorio no existe: $mboxDir\n");
}

// ================================================================
// MAPEO DE CONVENCIONES
// ================================================================
$convenciones = [
    'INSPECCION LOCATIVA' => [1, 16],
    'ACTA DE VISITA' => [6, 9],
    'MATRIZ VULNERABILIDAD' => [11, 11],
    'CERTIFICADO DE FUMIGACION' => [13, 16],
    'PLAN DE EMERGENCIAS FAMILIAR' => [11, 10],
    'PLAN DE EMERGENCIAS' => [11, 10],
    'CERTIFICADO LAVADO DE TANQUES' => [14, 14],
    'DOTACION ASEADORAS' => [3, 7],
    'DOTACION TODERO' => [3, 6],
    'DOTACION VIGILANTES' => [4, 8],
    'EVALUACION DE CONTRATISTA' => [4, 9],
    'RESULTADOS CALIFICACION DE ESTÁNDARES MINIMOS' => [9, 1],
    'INFORME A LA ALTA DIRECCION' => [2, 1],
    'INFORME DE CIERRE DE MES' => [10, 1],
    'PLAN DE SANEAMIENTO BASICO' => [13, 20],
    'MANEJO DE RESIDUOS Y PLAGAS' => [13, 20],
    'CERTIFICADO 50 HORAS' => [8, 23],
    'ACTA CAPACITACION' => [7, 1],
    'REPORTE DE CAPACITACION' => [7, 1],
    'RESPONSABILIDADES SST' => [7, 1],
    'CONTRATO SG-SST' => [19, 20],
    'ACUERDO DE CONFIDENCIALIDAD' => [19, 20],
    'INSPECCION DE BOTIQUIN' => [1, 3],
    'INSPECCION ZONA DE RESIDUOS' => [1, 15],
    'INSPECCION EXTINTORES' => [1, 2],
    'INSPECCION GABINETES CONTRA INCENDIO' => [1, 4],
    'RECORRIDO DE INSPECCION' => [1, 19],
    'INSPECCION RECURSOS PARA LA SEGURIDAD' => [11, 5],
    'OCURRENCIA DE PELIGROS' => [11, 12],
    'INSPECCION EQUIPOS DE COMUNICACIONES' => [1, 21],
    'SEGURIDAD SOCIAL' => [6, 9],
    'SOPORTE LAVADO DE TANQUES' => [13, 20],
    'SOPORTE MANEJO DE PLAGAS' => [13, 20],
    'SOPORTE DESRATIZACION' => [13, 20],
    'PUBLICACION POLITICA Y OBJETIVOS' => [17, 23],
    'APROBACION EVALUACION INICIAL REP LEGAL' => [17, 23],
    'APROBACION PLAN DE TRABAJO REP LEGAL' => [17, 23],
    'HOJA DE VIDA BRIGADISTA' => [11, 10],
    'DOCUMENTOS DEL RESPONSABLE SST' => [21, 20],
    'EVALUACION SIMULACRO' => [11, 10],
    'PREPARACION GUION SIMULACRO' => [11, 10],
    'INSPECCION SENALIZACION' => [1, 21],
    'CONSTANCIA DE PARTICIPACION SIMULACRO' => [11, 10],
    'AUDITORIA PROVEEDOR DE ASEO' => [3, 9],
    'AUDITORIA PROVEEDOR DE VIGILANCIA' => [4, 9],
    'AUDITORIA OTROS PROVEEDORES' => [12, 9],
    'APROBACION PLAN DE CAPACITACION REP LEGAL' => [17, 23],
    'PROGRAMA DE LIMPIEZA Y DESINFECCION' => [13, 20],
    'PROGRAMA DE MANEJO INTEGRAL DE RESIDUOS SOLIDOS' => [13, 20],
    'PROGRAMA DE CONTROL INTEGRADO DE PLAGAS' => [13, 20],
    'PROGRAMA DE ABASTECIMIENTO Y CONTROL DE AGUA POTABLE' => [13, 20],
    'KPI PROGRAMA DE LIMPIEZA Y DESINFECCION' => [13, 20],
    'KPI PROGRAMA DE MANEJO INTEGRAL DE RESIDUOS SOLIDOS' => [13, 20],
    'KPI PROGRAMA DE CONTROL INTEGRADO DE PLAGAS' => [13, 20],
    'KPI PROGRAMA DE ABASTECIMIENTO Y CONTROL DE AGUA POTABLE' => [13, 20],
];

// ================================================================
// MAPEO DE CLIENTES (nombre → id_cliente)
// Se intenta cargar desde BD local, si no, se lee de JSON
// ================================================================
$clientes = cargarClientes();

// ================================================================
// PROCESAR MBOX FILES
// ================================================================
$mboxFiles = glob($mboxDir . '/*.mbox');
if (empty($mboxFiles)) {
    die("ERROR: No se encontraron archivos .mbox en $mboxDir\n");
}

// Excluir el mbox gigante "Abierto.mbox" y otros no relevantes
$mboxFiles = array_filter($mboxFiles, function($f) {
    $name = basename($f);
    return stripos($name, 'Abierto') === false
        && stripos($name, 'AFIANCOL') === false
        && stripos($name, 'ARDURRA') === false
        && stripos($name, 'BIOTOSCANA') === false;
});

echo "=== RECARGA MASIVA DESDE TAKEOUT ===\n";
echo "Servidor: $baseUrl\n";
echo "Archivos mbox: " . count($mboxFiles) . "\n\n";

$stats = ['ok' => 0, 'error' => 0, 'skip' => 0, 'total_emails' => 0];

foreach ($mboxFiles as $mboxFile) {
    $label = basename($mboxFile, '.mbox');
    $clienteNombre = limpiarNombreLabel($label);
    $idCliente = buscarCliente($clienteNombre, $clientes);

    if (!$idCliente) {
        echo "SKIP mbox [$label] — cliente no encontrado en BD\n";
        $stats['skip']++;
        continue;
    }

    echo "\n=== [$label] → cliente ID $idCliente ===\n";
    procesarMbox($mboxFile, $idCliente, $baseUrl, $token, $convenciones, $stats);
}

echo "\n========================================\n";
echo "  RESUMEN FINAL\n";
echo "========================================\n";
echo "Emails procesados: {$stats['total_emails']}\n";
echo "Archivos subidos OK: {$stats['ok']}\n";
echo "Errores: {$stats['error']}\n";
echo "Mbox sin cliente: {$stats['skip']}\n";

// ================================================================
// FUNCIONES
// ================================================================

function procesarMbox(string $mboxFile, int $idCliente, string $baseUrl, string $token, array $convenciones, array &$stats): void
{
    $handle = fopen($mboxFile, 'r');
    if (!$handle) return;

    $currentEmail = '';
    $inEmail = false;

    while (($line = fgets($handle)) !== false) {
        if (preg_match('/^From \S+@\S+/', $line) || (str_starts_with($line, 'From ') && strpos($line, ' 20') !== false)) {
            if ($inEmail && $currentEmail) {
                procesarEmail($currentEmail, $idCliente, $baseUrl, $token, $convenciones, $stats);
            }
            $currentEmail = '';
            $inEmail = true;
            continue;
        }
        if ($inEmail) {
            $currentEmail .= $line;
        }
    }

    if ($inEmail && $currentEmail) {
        procesarEmail($currentEmail, $idCliente, $baseUrl, $token, $convenciones, $stats);
    }

    fclose($handle);
}

function procesarEmail(string $rawEmail, int $idCliente, string $baseUrl, string $token, array $convenciones, array &$stats): void
{
    $stats['total_emails']++;

    // Extraer headers
    $headerEnd = strpos($rawEmail, "\r\n\r\n");
    if ($headerEnd === false) $headerEnd = strpos($rawEmail, "\n\n");
    if ($headerEnd === false) return;

    $headers = substr($rawEmail, 0, $headerEnd);

    // Extraer fecha del email
    $fechaEmail = null;
    if (preg_match('/^Date:\s*(.+)$/mi', $headers, $dm)) {
        $fechaEmail = trim($dm[1]);
    }

    // Extraer subject
    $subject = '';
    if (preg_match('/^Subject:\s*(.+)$/mi', $headers, $sm)) {
        $subject = trim($sm[1]);
        // Decodificar si está encoded
        if (strpos($subject, '=?') !== false) {
            $subject = mb_decode_mimeheader($subject);
        }
    }

    // Verificar si es multipart
    if (!preg_match('/boundary="?([^"\s;]+)"?/i', $rawEmail, $bm)) {
        return;
    }
    $boundary = $bm[1];

    // Extraer adjuntos
    $parts = explode('--' . $boundary, $rawEmail);
    $adjuntosEncontrados = 0;

    foreach ($parts as $part) {
        if (trim($part) === '' || trim($part) === '--') continue;

        // Buscar attachment o inline con filename
        if (!preg_match('/filename[*]?=/i', $part)) continue;

        // Extraer nombre
        $fileName = null;
        if (preg_match('/filename\*=(?:UTF-8\'\'|utf-8\'\')(.+?)(?:\r?\n|\s|;|$)/i', $part, $fnm)) {
            $fileName = urldecode(trim($fnm[1], '"'));
        } elseif (preg_match('/filename="?([^"\r\n;]+)"?/i', $part, $fnm)) {
            $fileName = trim($fnm[1], '"');
        }
        if (!$fileName) continue;

        // Solo PDFs, imágenes y documentos
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (!in_array($ext, ['pdf', 'jpg', 'jpeg', 'png', 'xlsx', 'xls', 'docx'])) continue;

        // Extraer contenido
        $partHeaderEnd = strpos($part, "\r\n\r\n");
        if ($partHeaderEnd === false) $partHeaderEnd = strpos($part, "\n\n");
        if ($partHeaderEnd === false) continue;

        $partHeaders = substr($part, 0, $partHeaderEnd);
        $partBody = substr($part, $partHeaderEnd + 4);

        if (preg_match('/Content-Transfer-Encoding:\s*base64/i', $partHeaders)) {
            $decoded = base64_decode(preg_replace('/\s+/', '', $partBody));
        } elseif (preg_match('/Content-Transfer-Encoding:\s*quoted-printable/i', $partHeaders)) {
            $decoded = quoted_printable_decode($partBody);
        } else {
            $decoded = $partBody;
        }

        if (empty($decoded) || strlen($decoded) < 100) continue;

        // Determinar tipo de documento desde subject
        $docInfo = determinarTipoDocumento($subject, $convenciones);

        // Guardar adjunto temporal
        $tmpFile = sys_get_temp_dir() . '/' . uniqid('takeout_') . '.' . $ext;
        file_put_contents($tmpFile, $decoded);

        // Subir via API
        $titulo = $subject ?: $fileName;
        $result = subirArchivo($baseUrl, $token, $tmpFile, $fileName, $idCliente, $docInfo, $titulo, $fechaEmail);

        unlink($tmpFile);

        if ($result['success']) {
            $stats['ok']++;
            $adjuntosEncontrados++;
            echo "  OK: $fileName (" . round(strlen($decoded)/1024) . " KB)\n";
        } else {
            $stats['error']++;
            echo "  ERROR: $fileName — {$result['error']}\n";
        }
    }
}

function determinarTipoDocumento(string $subject, array $convenciones): array
{
    // Intentar parsear formato AppSheets: NOMBRE_DOC__CLIENTE___FECHA
    if (strpos($subject, '__') !== false) {
        $parts = explode('___', $subject);
        $left = $parts[0] ?? '';
        $docParts = explode('__', $left);
        $nombreDoc = trim($docParts[0] ?? '');

        if ($nombreDoc && isset($convenciones[$nombreDoc])) {
            return [
                'id_report_type'  => $convenciones[$nombreDoc][0],
                'id_detailreport' => $convenciones[$nombreDoc][1],
            ];
        }
    }

    // Match parcial contra convenciones
    $subjectUpper = strtoupper($subject);
    foreach ($convenciones as $nombre => $ids) {
        if (strpos($subjectUpper, strtoupper($nombre)) !== false) {
            return ['id_report_type' => $ids[0], 'id_detailreport' => $ids[1]];
        }
    }

    // Genérico
    return ['id_report_type' => 22, 'id_detailreport' => 20];
}

function subirArchivo(string $baseUrl, string $token, string $filePath, string $fileName, int $idCliente, array $docInfo, string $titulo, ?string $fechaEmail): array
{
    $ch = curl_init();

    $postFields = [
        'id_cliente'      => $idCliente,
        'id_report_type'  => $docInfo['id_report_type'],
        'id_detailreport' => $docInfo['id_detailreport'],
        'titulo_reporte'  => $titulo,
        'estado'          => 'CERRADO',
        'observaciones'   => 'Recargado desde Takeout ' . date('Y-m-d'),
        'archivo'         => new CURLFile($filePath, mime_content_type($filePath), $fileName),
    ];

    if ($fechaEmail) {
        $postFields['fecha_original'] = $fechaEmail;
    }

    curl_setopt_array($ch, [
        CURLOPT_URL            => $baseUrl . '/bulk-report-upload',
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $postFields,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['X-Bulk-Token: ' . $token],
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        return ['success' => false, 'error' => "cURL: $curlError"];
    }

    $json = json_decode($response, true);
    if ($httpCode !== 200 || !($json['success'] ?? false)) {
        return ['success' => false, 'error' => $json['error'] ?? "HTTP $httpCode: $response"];
    }

    return ['success' => true];
}

function cargarClientes(): array
{
    $jsonFile = __DIR__ . '/mapeo_clientes_nit.json';
    if (file_exists($jsonFile)) {
        $data = json_decode(file_get_contents($jsonFile), true);
        if ($data) return $data;
    }

    // Conectar a BD producción (datos reales)
    $mysqli = new mysqli();
    $mysqli->ssl_set(null, null, null, null, null);
    @$mysqli->real_connect(
        'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'cycloid_userdb',
        getenv('DB_PROD_PASS'),
        'propiedad_horizontal',
        25060,
        null,
        MYSQLI_CLIENT_SSL
    );

    if ($mysqli->connect_error) {
        // Fallback a local
        $mysqli = @new mysqli('localhost', 'root', '', 'propiedad_horizontal');
        if ($mysqli->connect_error) {
            die("ERROR: No se pudo conectar a ninguna BD\n");
        }
        echo "Conectado a BD LOCAL\n";
    } else {
        echo "Conectado a BD PRODUCCIÓN\n";
    }

    $result = $mysqli->query("SELECT id_cliente, nit_cliente, nombre_cliente FROM tbl_clientes");
    $mapeo = [];
    while ($row = $result->fetch_assoc()) {
        $mapeo[$row['nombre_cliente']] = (int) $row['id_cliente'];
        // Versiones sin " - PROPIEDAD HORIZONTAL", " - PH", "– PROPIEDAD..."
        $corto = preg_replace('/\s*[-–—]\s*(PROPIEDAD HORIZONTAL|PH)\s*$/i', '', $row['nombre_cliente']);
        if ($corto !== $row['nombre_cliente']) {
            $mapeo[$corto] = (int) $row['id_cliente'];
        }
    }
    $mysqli->close();

    file_put_contents($jsonFile, json_encode($mapeo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    return $mapeo;
}

function limpiarNombreLabel(string $label): string
{
    // Quitar prefijo "CONJUNTOS-" que Gmail pone
    $label = preg_replace('/^CONJUNTOS-/', '', $label);
    // Quitar truncamiento (nombres cortados por Gmail en el .mbox)
    return trim($label);
}

function buscarCliente(string $nombreLabel, array $clientes): ?int
{
    // Normalizar: quitar guiones especiales, dobles espacios
    $normalizar = function($s) {
        $s = str_replace(['–', '—'], '-', $s); // en-dash, em-dash → guion normal
        $s = preg_replace('/\s+/', ' ', $s);
        return trim(strtoupper($s));
    };

    $labelNorm = $normalizar($nombreLabel);

    // Match exacto
    foreach ($clientes as $nombre => $id) {
        if ($normalizar($nombre) === $labelNorm) {
            return $id;
        }
    }

    // Match parcial — el mbox puede estar truncado (Gmail corta a ~50 chars)
    // Usamos las primeras 20 chars del label para buscar en los nombres completos
    $labelPrefix = substr($labelNorm, 0, 20);
    foreach ($clientes as $nombre => $id) {
        $nombreNorm = $normalizar($nombre);
        if (str_starts_with($nombreNorm, $labelPrefix)) {
            return $id;
        }
    }

    // Match inverso — el nombre de BD puede empezar con el label
    foreach ($clientes as $nombre => $id) {
        $nombreNorm = $normalizar($nombre);
        $nombrePrefix = substr($nombreNorm, 0, 20);
        if (str_starts_with($labelNorm, $nombrePrefix)) {
            return $id;
        }
    }

    // Match por palabras clave (mínimo 3 palabras significativas en común)
    $labelWords = array_filter(preg_split('/\s+/', $labelNorm), fn($w) => strlen($w) > 2);
    foreach ($clientes as $nombre => $id) {
        $nombreWords = array_filter(preg_split('/\s+/', $normalizar($nombre)), fn($w) => strlen($w) > 2);
        $common = array_intersect($labelWords, $nombreWords);
        if (count($common) >= 3) {
            return $id;
        }
    }

    return null;
}
