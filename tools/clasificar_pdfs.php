<?php
/**
 * Clasificar PDFs extraídos de Google Takeout usando Claude Haiku API
 *
 * Uso: DB_PROD_PASS=xxx php tools/clasificar_pdfs.php
 *
 * Recorre D:\DESARROLLO\pdfs_extraidos\, extrae texto con pdftotext,
 * clasifica con Claude Haiku, y genera clasificacion_pdfs.csv
 */

$pdfDir    = 'D:/DESARROLLO/pdfs_extraidos';
$outputCsv = 'D:/DESARROLLO/clasificacion_pdfs.csv';
$apiKey    = getenv('ANTHROPIC_API_KEY');
$pdftotext = 'pdftotext';
$modelo    = 'claude-haiku-4-5-20251001';

// ================================================================
// MAPEO tipo_documento → [id_report_type, id_detailreport]
// ================================================================
$MAPEO = [
    'INSPECCION LOCATIVA' => [1, 16],
    'ACTA DE VISITA' => [6, 9],
    'MATRIZ VULNERABILIDAD' => [11, 11],
    'CERTIFICADO DE FUMIGACION' => [13, 16],
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
    'PLAN DE EMERGENCIAS FAMILIAR' => [11, 10],
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
    'OTRO' => [22, 20],
    'ILEGIBLE' => [22, 20],
    'PARA REVISION MANUAL' => [22, 20],
];

// ================================================================
// PROMPT para Claude Haiku
// ================================================================
$SYSTEM_PROMPT = 'Eres un clasificador de documentos SST (Seguridad y Salud en el Trabajo) para copropiedades en Colombia.

Dado el siguiente texto extraído de un PDF, responde SOLO en formato JSON con estos campos:
- tipo_documento: el nombre del tipo de documento de esta lista EXACTA (o "OTRO" si no coincide con ninguno)
- fecha_documento: la fecha más reciente del documento en formato YYYY-MM-DD (la fecha real de cuando se hizo el documento, NO la fecha del template)
- confianza: "alta", "media" o "baja"

LISTA DE TIPOS DE DOCUMENTO:
INSPECCION LOCATIVA
ACTA DE VISITA
MATRIZ VULNERABILIDAD
CERTIFICADO DE FUMIGACION
PLAN DE EMERGENCIAS
CERTIFICADO LAVADO DE TANQUES
DOTACION ASEADORAS
DOTACION TODERO
DOTACION VIGILANTES
EVALUACION DE CONTRATISTA
RESULTADOS CALIFICACION DE ESTÁNDARES MINIMOS
INFORME A LA ALTA DIRECCION
INFORME DE CIERRE DE MES
PLAN DE SANEAMIENTO BASICO
MANEJO DE RESIDUOS Y PLAGAS
CERTIFICADO 50 HORAS
ACTA CAPACITACION
REPORTE DE CAPACITACION
RESPONSABILIDADES SST
CONTRATO SG-SST
ACUERDO DE CONFIDENCIALIDAD
INSPECCION DE BOTIQUIN
INSPECCION ZONA DE RESIDUOS
INSPECCION EXTINTORES
INSPECCION GABINETES CONTRA INCENDIO
RECORRIDO DE INSPECCION
INSPECCION RECURSOS PARA LA SEGURIDAD
OCURRENCIA DE PELIGROS
INSPECCION EQUIPOS DE COMUNICACIONES
SEGURIDAD SOCIAL
SOPORTE LAVADO DE TANQUES
SOPORTE MANEJO DE PLAGAS
SOPORTE DESRATIZACION
PUBLICACION POLITICA Y OBJETIVOS
APROBACION EVALUACION INICIAL REP LEGAL
APROBACION PLAN DE TRABAJO REP LEGAL
HOJA DE VIDA BRIGADISTA
DOCUMENTOS DEL RESPONSABLE SST
PLAN DE EMERGENCIAS FAMILIAR
EVALUACION SIMULACRO
PREPARACION GUION SIMULACRO
INSPECCION SENALIZACION
CONSTANCIA DE PARTICIPACION SIMULACRO
AUDITORIA PROVEEDOR DE ASEO
AUDITORIA PROVEEDOR DE VIGILANCIA
AUDITORIA OTROS PROVEEDORES
APROBACION PLAN DE CAPACITACION REP LEGAL
PROGRAMA DE LIMPIEZA Y DESINFECCION
PROGRAMA DE MANEJO INTEGRAL DE RESIDUOS SOLIDOS
PROGRAMA DE CONTROL INTEGRADO DE PLAGAS
PROGRAMA DE ABASTECIMIENTO Y CONTROL DE AGUA POTABLE
KPI PROGRAMA DE LIMPIEZA Y DESINFECCION
KPI PROGRAMA DE MANEJO INTEGRAL DE RESIDUOS SOLIDOS
KPI PROGRAMA DE CONTROL INTEGRADO DE PLAGAS
KPI PROGRAMA DE ABASTECIMIENTO Y CONTROL DE AGUA POTABLE

IMPORTANTE:
- La fecha del documento es la fecha MÁS RECIENTE que aparezca en el texto (no la del encabezado/plantilla que suele ser antigua)
- Si el texto está vacío o ilegible, responde con tipo_documento:"ILEGIBLE" y fecha_documento:null
- Responde SOLO el JSON, sin explicaciones';

// ================================================================
// Cargar inventario (para fecha_email)
// ================================================================
$inventario = [];
$inventarioCsv = $pdfDir . '/inventario_takeout.csv';
if (file_exists($inventarioCsv)) {
    $fh = fopen($inventarioCsv, 'r');
    $header = fgetcsv($fh);
    while ($row = fgetcsv($fh)) {
        $key = $row[0] . '/' . $row[1];
        $inventario[$key] = [
            'fecha_email' => $row[2] ?? '',
            'subject'     => $row[3] ?? '',
        ];
    }
    fclose($fh);
    echo "Inventario cargado: " . count($inventario) . " entradas\n";
}

// ================================================================
// Cargar clientes desde BD (reutilizando lógica de recarga_desde_takeout.php)
// ================================================================
$clientes = cargarClientes();
echo "Clientes cargados: " . count($clientes) . "\n";

// ================================================================
// Retomar ejecución: leer CSV existente
// ================================================================
$yaClasificados = [];
if (file_exists($outputCsv)) {
    $fh = fopen($outputCsv, 'r');
    fgetcsv($fh); // header
    while ($row = fgetcsv($fh)) {
        $yaClasificados[$row[0] . '/' . $row[1]] = true;
    }
    fclose($fh);
    echo "Retomando: " . count($yaClasificados) . " ya clasificados\n";
}

// ================================================================
// Recopilar todos los PDFs
// ================================================================
$todosLosPdfs = [];
$carpetas = glob($pdfDir . '/*', GLOB_ONLYDIR);
foreach ($carpetas as $carpeta) {
    $nombreCarpeta = basename($carpeta);
    $pdfs = glob($carpeta . '/*.pdf');
    foreach ($pdfs as $pdf) {
        $nombreArchivo = basename($pdf);
        $todosLosPdfs[] = [
            'carpeta'  => $nombreCarpeta,
            'archivo'  => $nombreArchivo,
            'ruta'     => $pdf,
        ];
    }
}

$total = count($todosLosPdfs);
echo "Total PDFs encontrados: $total\n";
echo "Pendientes: " . ($total - count($yaClasificados)) . "\n\n";

// ================================================================
// Abrir CSV para escritura (append si retomando)
// ================================================================
$escribirHeader = !file_exists($outputCsv) || count($yaClasificados) === 0;
$csvHandle = fopen($outputCsv, count($yaClasificados) > 0 ? 'a' : 'w');
if ($escribirHeader) {
    fputcsv($csvHandle, [
        'carpeta', 'archivo', 'nit_cliente', 'id_cliente',
        'tipo_documento', 'id_report_type', 'id_detailreport',
        'fecha_documento', 'confianza', 'fecha_email'
    ]);
}

// ================================================================
// PROCESAR
// ================================================================
$procesados = count($yaClasificados);
$errores = 0;

foreach ($todosLosPdfs as $i => $pdf) {
    $key = $pdf['carpeta'] . '/' . $pdf['archivo'];

    // Saltar ya clasificados
    if (isset($yaClasificados[$key])) continue;

    $procesados++;
    $carpetaLimpia = limpiarNombreLabel($pdf['carpeta']);
    $clienteInfo = buscarClienteCompleto($carpetaLimpia, $clientes);
    $idCliente = $clienteInfo['id_cliente'] ?? 0;
    $nitCliente = $clienteInfo['nit_cliente'] ?? '';

    // 1. Extraer texto con pdftotext
    $texto = extraerTexto($pdf['ruta'], $pdftotext);

    // 2. Si texto vacío → ILEGIBLE
    if (strlen(trim($texto)) < 20) {
        $tipo = 'ILEGIBLE';
        $fecha = null;
        $confianza = 'alta';
    } else {
        // 3. Llamar Claude Haiku
        $resultado = clasificarConClaude($texto, $apiKey, $modelo, $SYSTEM_PROMPT);
        $tipo = $resultado['tipo_documento'] ?? 'OTRO';
        $fecha = $resultado['fecha_documento'] ?? null;
        $confianza = $resultado['confianza'] ?? 'baja';
    }

    // 4. Normalizar tipo y mapear → ids
    $tipo = normalizarTipo($tipo, $MAPEO);
    $ids = $MAPEO[$tipo] ?? $MAPEO['OTRO'];

    // 5. Obtener fecha_email del inventario
    $fechaEmail = '';
    // Buscar en inventario por carpeta_mbox (sin prefijo CONJUNTOS-)
    foreach ($inventario as $invKey => $invData) {
        if (strpos($invKey, $pdf['archivo']) !== false) {
            $fechaEmail = $invData['fecha_email'];
            break;
        }
    }

    // 6. Escribir fila CSV
    fputcsv($csvHandle, [
        $pdf['carpeta'],
        $pdf['archivo'],
        $nitCliente,
        $idCliente,
        $tipo,
        $ids[0],
        $ids[1],
        $fecha,
        $confianza,
        $fechaEmail,
    ]);

    // Progreso
    echo "[$procesados/$total] $tipo | $fecha | $confianza | $carpetaLimpia\n";

    // Flush cada 100
    if ($procesados % 100 === 0) {
        fflush($csvHandle);
        echo "--- Progreso guardado: $procesados/$total ---\n";
    }
}

fclose($csvHandle);

echo "\n========================================\n";
echo "  CLASIFICACIÓN COMPLETADA\n";
echo "========================================\n";
echo "Total procesados: $procesados\n";
echo "Archivo: $outputCsv\n";

// ================================================================
// FUNCIONES
// ================================================================

function extraerTexto(string $pdfPath, string $pdftotext): string
{
    $cmd = escapeshellarg($pdftotext) . ' ' . escapeshellarg($pdfPath) . ' -';
    $output = shell_exec($cmd . ' 2>NUL');
    if ($output === null) return '';
    // Limpiar caracteres no-UTF8 que pdftotext genera
    $output = mb_convert_encoding($output, 'UTF-8', 'UTF-8');
    $output = preg_replace('/[^\x20-\x7E\xC0-\xFF\n\r\t]/u', '', $output);
    // Tomar primeros 3000 caracteres
    return mb_substr($output, 0, 3000);
}

function clasificarConClaude(string $texto, string $apiKey, string $modelo, string $systemPrompt): array
{
    $maxRetries = 3;

    for ($retry = 0; $retry < $maxRetries; $retry++) {
        $payload = json_encode([
            'model'      => $modelo,
            'max_tokens' => 200,
            'system'     => $systemPrompt,
            'messages'   => [
                ['role' => 'user', 'content' => "TEXTO DEL PDF:\n" . $texto],
            ],
        ]);

        $ch = curl_init('https://api.anthropic.com/v1/messages');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'x-api-key: ' . $apiKey,
                'anthropic-version: 2023-06-01',
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            echo "  cURL error: $curlError (retry $retry)\n";
            sleep(5);
            continue;
        }

        if ($httpCode === 429) {
            echo "  Rate limit (429), esperando 5s...\n";
            sleep(5);
            continue;
        }

        if ($httpCode >= 500) {
            echo "  Server error ($httpCode), esperando 10s...\n";
            sleep(10);
            continue;
        }

        if ($httpCode !== 200) {
            echo "  API error HTTP $httpCode: " . substr($response, 0, 200) . "\n";
            return ['tipo_documento' => 'PARA REVISION MANUAL', 'fecha_documento' => null, 'confianza' => 'baja'];
        }

        // Parsear respuesta
        $data = json_decode($response, true);
        $content = $data['content'][0]['text'] ?? '';

        // Extraer JSON de la respuesta (puede venir envuelto en ```json ... ```)
        if (preg_match('/\{[^{}]*"tipo_documento"[^{}]*\}/s', $content, $m)) {
            $json = json_decode($m[0], true);
            if ($json && isset($json['tipo_documento'])) {
                return $json;
            }
        }

        // Intento directo
        $json = json_decode($content, true);
        if ($json && isset($json['tipo_documento'])) {
            return $json;
        }

        echo "  Respuesta no parseable: " . substr($content, 0, 100) . "\n";
        return ['tipo_documento' => 'PARA REVISION MANUAL', 'fecha_documento' => null, 'confianza' => 'baja'];
    }

    echo "  Máximo de reintentos alcanzado\n";
    return ['tipo_documento' => 'PARA REVISION MANUAL', 'fecha_documento' => null, 'confianza' => 'baja'];
}

function cargarClientes(): array
{
    $jsonFile = __DIR__ . '/mapeo_clientes_completo.json';
    if (file_exists($jsonFile)) {
        $data = json_decode(file_get_contents($jsonFile), true);
        if ($data) return $data;
    }

    $mysqli = new mysqli();
    $mysqli->ssl_set(null, null, null, null, null);
    $mysqli->real_connect(
        'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'cycloid_userdb',
        'AVNS_MR2SLvzRh3i_7o9fEHN',
        'propiedad_horizontal',
        25060,
        null,
        MYSQLI_CLIENT_SSL
    );

    if ($mysqli->connect_error) {
        die("ERROR: No se pudo conectar a BD producción: " . $mysqli->connect_error . "\n");
    }
    echo "Conectado a BD PRODUCCIÓN\n";

    $result = $mysqli->query("SELECT id_cliente, nit_cliente, nombre_cliente FROM tbl_clientes");
    $mapeo = [];
    while ($row = $result->fetch_assoc()) {
        $mapeo[] = [
            'id_cliente'     => (int) $row['id_cliente'],
            'nit_cliente'    => $row['nit_cliente'],
            'nombre_cliente' => $row['nombre_cliente'],
        ];
    }
    $mysqli->close();

    file_put_contents($jsonFile, json_encode($mapeo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    return $mapeo;
}

function limpiarNombreLabel(string $label): string
{
    $label = preg_replace('/^CONJUNTOS-/', '', $label);
    return trim($label);
}

function buscarClienteCompleto(string $nombreLabel, array $clientes): ?array
{
    $normalizar = function($s) {
        $s = str_replace(['–', '—'], '-', $s);
        $s = preg_replace('/\s+/', ' ', $s);
        return trim(strtoupper($s));
    };

    $labelNorm = $normalizar($nombreLabel);

    // Match exacto
    foreach ($clientes as $c) {
        if ($normalizar($c['nombre_cliente']) === $labelNorm) {
            return $c;
        }
    }

    // Label es prefijo del nombre en BD
    foreach ($clientes as $c) {
        $nombreNorm = $normalizar($c['nombre_cliente']);
        if (str_starts_with($nombreNorm, $labelNorm)) {
            return $c;
        }
    }

    // Nombre BD es prefijo del label
    foreach ($clientes as $c) {
        $nombreNorm = $normalizar($c['nombre_cliente']);
        if (str_starts_with($labelNorm, $nombreNorm)) {
            return $c;
        }
    }

    // Match por palabras clave (mínimo 3 significativas en común)
    $labelWords = array_filter(preg_split('/\s+/', $labelNorm), fn($w) => strlen($w) > 2);
    foreach ($clientes as $c) {
        $nombreWords = array_filter(preg_split('/\s+/', $normalizar($c['nombre_cliente'])), fn($w) => strlen($w) > 2);
        $common = array_intersect($labelWords, $nombreWords);
        if (count($common) >= 3) {
            return $c;
        }
    }

    // Quitar sufijos comunes y reintentar
    $sinSufijo = preg_replace('/\s*[-–—]\s*(PROPIEDAD HORIZONTAL|PH|P\.?H\.?)\s*$/i', '', $labelNorm);
    if ($sinSufijo !== $labelNorm) {
        foreach ($clientes as $c) {
            $nombreNorm = $normalizar($c['nombre_cliente']);
            $nombreSinSufijo = preg_replace('/\s*[-–—]\s*(PROPIEDAD HORIZONTAL|PH|P\.?H\.?)\s*$/i', '', $nombreNorm);
            if ($sinSufijo === $nombreSinSufijo || str_starts_with($nombreSinSufijo, $sinSufijo) || str_starts_with($sinSufijo, $nombreSinSufijo)) {
                return $c;
            }
        }
    }

    return null;
}

function normalizarTipo(string $tipo, array $mapeo): string
{
    // Si ya es un tipo válido, devolver tal cual
    if (isset($mapeo[$tipo])) return $tipo;

    // Normalizar: guiones bajos → espacios, trim, uppercase
    $norm = strtoupper(trim(str_replace('_', ' ', $tipo)));

    // Match exacto tras normalizar
    if (isset($mapeo[$norm])) return $norm;

    // Correcciones conocidas de Haiku
    $aliases = [
        'INSPECCION BOTIQUIN'              => 'INSPECCION DE BOTIQUIN',
        'INSPECCION ZONA RESIDUOS'         => 'INSPECCION ZONA DE RESIDUOS',
        'DOTACION ASEADORA'                => 'DOTACION ASEADORAS',
        'DOTACION VIGILANTE'               => 'DOTACION VIGILANTES',
        'DOTACION DE VIGILANTES'           => 'DOTACION VIGILANTES',
        'DOTACION DE ASEADORAS'            => 'DOTACION ASEADORAS',
        'DOTACION DE TODERO'               => 'DOTACION TODERO',
        'ACTA DE CAPACITACION'             => 'ACTA CAPACITACION',
        'CERTIFICADO FUMIGACION'           => 'CERTIFICADO DE FUMIGACION',
        'CERTIFICADO LAVADO TANQUES'       => 'CERTIFICADO LAVADO DE TANQUES',
        'SOPORTE LAVADO TANQUES'           => 'SOPORTE LAVADO DE TANQUES',
        'SOPORTE DE MANEJO DE PLAGAS'      => 'SOPORTE MANEJO DE PLAGAS',
        'SOPORTE DE DESRATIZACION'         => 'SOPORTE DESRATIZACION',
        'INSPECCION DE SENALIZACION'       => 'INSPECCION SENALIZACION',
        'INSPECCION DE EXTINTORES'         => 'INSPECCION EXTINTORES',
        'INSPECCION DE GABINETES CONTRA INCENDIO' => 'INSPECCION GABINETES CONTRA INCENDIO',
        'INSPECCION DE EQUIPOS DE COMUNICACIONES'  => 'INSPECCION EQUIPOS DE COMUNICACIONES',
        'INSPECCION DE RECURSOS PARA LA SEGURIDAD' => 'INSPECCION RECURSOS PARA LA SEGURIDAD',
        'RESULTADOS CALIFICACION DE ESTANDARES MINIMOS' => 'RESULTADOS CALIFICACION DE ESTÁNDARES MINIMOS',
        'LISTADO DE ASISTENCIA'            => 'ACTA CAPACITACION',
        'LISTA DE ASISTENCIA'              => 'ACTA CAPACITACION',
        'PLANILLA SEGURIDAD SOCIAL'        => 'SEGURIDAD SOCIAL',
        'PLANILLA DE SEGURIDAD SOCIAL'     => 'SEGURIDAD SOCIAL',
    ];

    if (isset($aliases[$norm])) return $aliases[$norm];

    // Fuzzy: buscar el tipo del mapeo que mejor contenga o sea contenido
    foreach ($mapeo as $tipoValido => $ids) {
        if ($tipoValido === 'OTRO' || $tipoValido === 'ILEGIBLE' || $tipoValido === 'PARA REVISION MANUAL') continue;
        // Si la respuesta de Haiku contiene el tipo válido completo
        if (strpos($norm, $tipoValido) !== false) return $tipoValido;
        // Si el tipo válido contiene la respuesta de Haiku (para respuestas truncadas)
        if (strlen($norm) > 10 && strpos($tipoValido, $norm) !== false) return $tipoValido;
    }

    // No se pudo normalizar → PARA REVISION MANUAL
    if ($tipo !== 'OTRO' && $tipo !== 'ILEGIBLE') {
        echo "  [NORM] No se pudo normalizar: '$tipo' → PARA REVISION MANUAL\n";
        return 'PARA REVISION MANUAL';
    }

    return $tipo;
}
