<?php
/**
 * V2: Empareja huérfanos con emails del mbox por NIT.
 *
 * Lógica: Para cada NIT con huérfanos, busca en el mbox de ese cliente
 * los emails con adjuntos que NO tienen registro en tbl_reporte.
 * Los empareja y registra con la fecha y subject del email.
 */

$mboxDir = $argv[1] ?? '';
$baseUrl = $argv[2] ?? '';

if (!$mboxDir || !$baseUrl) {
    die("Uso: php registrar_huerfanos_v2.php <mbox_dir> <base_url>\n");
}

// Tabla de convenciones
$CONVENCIONES = [
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
    'RESULTADOS CALIFICACION' => [9, 1],
    'INFORME A LA ALTA DIRECCION' => [2, 1],
    'INFORME DE CIERRE DE MES' => [10, 1],
    'PLAN DE SANEAMIENTO' => [13, 20],
    'MANEJO DE RESIDUOS' => [13, 20],
    'CERTIFICADO 50 HORAS' => [8, 23],
    'ACTA CAPACITACION' => [7, 1],
    'REPORTE DE CAPACITACION' => [7, 1],
    'RESPONSABILIDADES SST' => [7, 1],
    'CONTRATO SG-SST' => [19, 20],
    'ACUERDO DE CONFIDENCIALIDAD' => [19, 20],
    'INSPECCION DE BOTIQUIN' => [1, 3],
    'INSPECCION ZONA DE RESIDUOS' => [1, 15],
    'INSPECCION EXTINTORES' => [1, 2],
    'INSPECCION GABINETES' => [1, 4],
    'RECORRIDO DE INSPECCION' => [1, 19],
    'INSPECCION RECURSOS PARA LA SEGURIDAD' => [11, 5],
    'OCURRENCIA DE PELIGROS' => [11, 12],
    'INSPECCION EQUIPOS DE COMUNICACIONES' => [1, 21],
    'SEGURIDAD SOCIAL' => [6, 9],
    'SOPORTE LAVADO DE TANQUES' => [13, 20],
    'SOPORTE MANEJO DE PLAGAS' => [13, 20],
    'SOPORTE DESRATIZACION' => [13, 20],
    'PUBLICACION POLITICA' => [17, 23],
    'APROBACION EVALUACION INICIAL' => [17, 23],
    'APROBACION PLAN DE TRABAJO' => [17, 23],
    'HOJA DE VIDA BRIGADISTA' => [11, 10],
    'DOCUMENTOS DEL RESPONSABLE' => [21, 20],
    'PLAN DE EMERGENCIAS FAMILIAR' => [11, 10],
    'EVALUACION SIMULACRO' => [11, 10],
    'PREPARACION GUION SIMULACRO' => [11, 10],
    'INSPECCION SENALIZACION' => [1, 21],
    'CONSTANCIA DE PARTICIPACION' => [11, 10],
    'AUDITORIA PROVEEDOR DE ASEO' => [3, 9],
    'AUDITORIA PROVEEDOR DE VIGILANCIA' => [4, 9],
    'AUDITORIA OTROS PROVEEDORES' => [12, 9],
    'APROBACION PLAN DE CAPACITACION' => [17, 23],
    'PROGRAMA DE LIMPIEZA' => [13, 20],
    'PROGRAMA DE MANEJO INTEGRAL' => [13, 20],
    'PROGRAMA DE CONTROL INTEGRADO' => [13, 20],
    'PROGRAMA DE ABASTECIMIENTO' => [13, 20],
    'KPI PROGRAMA' => [13, 20],
    'PLANILLA DE SEGURIDAD SOCIAL' => [6, 9],
];

// Conectar BD
echo "Conectando a BD...\n";
$db = new mysqli();
$db->ssl_set(null, null, null, null, null);
$db->real_connect(
    'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
    'cycloid_userdb',
    getenv('DB_PROD_PASS'),
    'propiedad_horizontal', 25060, null, MYSQLI_CLIENT_SSL
);
if ($db->connect_error) die("Error BD: " . $db->connect_error . "\n");

// Mapeo nit → id_cliente y nombre
$clientesPorNit = [];
$clientesPorNombre = [];
$r = $db->query("SELECT id_cliente, nit_cliente, nombre_cliente FROM tbl_clientes");
while ($row = $r->fetch_assoc()) {
    $clientesPorNit[$row['nit_cliente']] = $row;
    $clientesPorNombre[strtoupper(trim($row['nombre_cliente']))] = $row;
}

// Títulos ya existentes en BD por cliente (para evitar duplicados)
$titulosExistentes = [];
$r = $db->query("SELECT id_cliente, titulo_reporte FROM tbl_reporte");
while ($row = $r->fetch_assoc()) {
    $key = $row['id_cliente'] . '|' . strtoupper(trim($row['titulo_reporte']));
    $titulosExistentes[$key] = true;
}
echo "Títulos existentes en BD: " . count($titulosExistentes) . "\n";

// Cargar huérfanos agrupados por NIT
$huerfanosPorNit = [];
$fisicosPath = PHP_OS_FAMILY === 'Windows' ? 'C:/tmp/archivos_fisicos.txt' : '/tmp/archivos_fisicos.txt';
$fisicos = file($fisicosPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if (!$fisicos) die("ERROR: No se pudo leer $fisicosPath\n");
$basePath = "/www/wwwroot/phorizontal/enterprisesstph/writable/soportes-clientes/";

// Cargar enlaces existentes
$enlacesExistentes = [];
$r = $db->query("SELECT enlace FROM tbl_reporte WHERE enlace IS NOT NULL");
while ($row = $r->fetch_assoc()) {
    if (preg_match('/serve-file\/(.+)$/', $row['enlace'], $m)) {
        $enlacesExistentes[$m[1]] = true;
    }
}

foreach ($fisicos as $f) {
    $rel = str_replace($basePath, "", $f);
    if (isset($enlacesExistentes[$rel])) continue; // no es huérfano
    $parts = explode('/', $rel);
    $nit = $parts[0] ?? '';
    if ($nit && isset($clientesPorNit[$nit])) {
        $huerfanosPorNit[$nit][] = $rel;
    }
}

$totalHuerfanos = array_sum(array_map('count', $huerfanosPorNit));
echo "Huérfanos por NIT: " . count($huerfanosPorNit) . " NITs, $totalHuerfanos archivos\n\n";

// Para cada NIT con huérfanos, buscar el mbox correspondiente
$mboxFiles = glob($mboxDir . '/*.mbox');
$stats = ['registrados' => 0, 'duplicado' => 0, 'sin_huerfano' => 0, 'error' => 0, 'residuales' => 0];

foreach ($huerfanosPorNit as $nit => $huerfanos) {
    $cliente = $clientesPorNit[$nit];
    $idCliente = $cliente['id_cliente'];
    $nombreCliente = $cliente['nombre_cliente'];

    // Buscar mbox que corresponda a este cliente
    $mboxFile = encontrarMbox($mboxFiles, $nombreCliente);
    if (!$mboxFile) {
        echo "=== NIT $nit ($nombreCliente): NO ENCONTRÉ MBOX ===\n";
        $stats['residuales'] += count($huerfanos);
        continue;
    }

    echo "=== NIT $nit ($nombreCliente) — " . count($huerfanos) . " huérfanos ===\n";
    echo "    Mbox: " . basename($mboxFile) . "\n";

    // Extraer emails con adjuntos del mbox
    $emailsConAdjuntos = extraerEmailsConAdjuntos($mboxFile);
    echo "    Emails con adjuntos: " . count($emailsConAdjuntos) . "\n";

    // Filtrar emails que ya tienen registro en BD
    $emailsNuevos = [];
    foreach ($emailsConAdjuntos as $email) {
        $tituloLimpio = limpiarTitulo($email['subject']);
        $key = $idCliente . '|' . strtoupper(trim($tituloLimpio));
        if (!isset($titulosExistentes[$key])) {
            $emailsNuevos[] = $email;
        }
    }
    echo "    Emails sin registro en BD: " . count($emailsNuevos) . "\n";

    // Emparejar: asignar cada email nuevo a un huérfano disponible
    $huerfanosDisp = $huerfanos; // copia
    $registrados = 0;

    foreach ($emailsNuevos as $email) {
        if (empty($huerfanosDisp)) break;

        // Tomar el siguiente huérfano disponible
        $rutaRelativa = array_shift($huerfanosDisp);

        $tituloLimpio = limpiarTitulo($email['subject']);
        $docInfo = determinarTipoDoc($email['subject'], $email['filename'] ?? '');
        $createdAt = $email['date'] ? date('Y-m-d H:i:s', strtotime($email['date'])) : date('Y-m-d H:i:s');

        if (!strtotime($createdAt) || strtotime($createdAt) < strtotime('2020-01-01')) {
            $createdAt = date('Y-m-d H:i:s');
        }

        $enlace = $baseUrl . '/serve-file/' . $rutaRelativa;

        $stmt = $db->prepare("INSERT INTO tbl_reporte (id_cliente, id_report_type, id_detailreport, titulo_reporte, enlace, estado, observaciones, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 'CERRADO', 'Registrado v2 huerfano+email', ?, ?)");
        $stmt->bind_param('iiissss', $idCliente, $docInfo[0], $docInfo[1], $tituloLimpio, $enlace, $createdAt, $createdAt);

        try {
            if ($stmt->execute()) {
                $registrados++;
                $stats['registrados']++;
                $titulosExistentes[$idCliente . '|' . strtoupper(trim($tituloLimpio))] = true;
                echo "    OK: $tituloLimpio ($createdAt) → " . basename($rutaRelativa) . "\n";
            } else {
                $stats['error']++;
                echo "    ERROR: " . $db->error . "\n";
                array_unshift($huerfanosDisp, $rutaRelativa); // devolver huérfano
            }
        } catch (Exception $e) {
            $stats['error']++;
            echo "    ERROR: " . $e->getMessage() . "\n";
            array_unshift($huerfanosDisp, $rutaRelativa); // devolver huérfano
        }
        $stmt->close();
    }

    $restantes = count($huerfanosDisp);
    if ($restantes > 0) {
        echo "    Huérfanos sin emparejar: $restantes\n";
        $stats['residuales'] += $restantes;
    }
    echo "    Registrados: $registrados\n\n";
}

echo "========================================\n";
echo "Total registrados: {$stats['registrados']}\n";
echo "Errores: {$stats['error']}\n";
echo "Residuales (sin match): {$stats['residuales']}\n";

$db->close();

// ============================================================
// FUNCIONES
// ============================================================

function encontrarMbox(array $mboxFiles, string $nombreCliente): ?string {
    $nombreUpper = strtoupper($nombreCliente);
    // Buscar coincidencia parcial del nombre del cliente en el nombre del mbox
    foreach ($mboxFiles as $f) {
        $base = strtoupper(basename($f, '.mbox'));
        // Quitar prefijo CONJUNTOS-
        $base = str_replace('CONJUNTOS-', '', $base);

        // Match parcial: primeras 30 letras
        $partial = substr($nombreUpper, 0, 30);
        if (strpos($base, $partial) !== false) return $f;

        // Match inverso
        $basePartial = substr($base, 0, 30);
        if (strpos($nombreUpper, $basePartial) !== false) return $f;
    }
    return null;
}

function extraerEmailsConAdjuntos(string $mboxFile): array {
    $fh = fopen($mboxFile, 'r');
    if (!$fh) return [];

    $emails = [];
    $rawEmail = '';

    while (($line = fgets($fh)) !== false) {
        if (preg_match('/^From \S+ .+$/', $line) && strlen($rawEmail) > 100) {
            $info = parsearEmail($rawEmail);
            if ($info) $emails[] = $info;
            $rawEmail = '';
        } else {
            $rawEmail .= $line;
        }
    }
    if (strlen($rawEmail) > 100) {
        $info = parsearEmail($rawEmail);
        if ($info) $emails[] = $info;
    }
    fclose($fh);
    return $emails;
}

function parsearEmail(string $rawEmail): ?array {
    $headerEnd = strpos($rawEmail, "\r\n\r\n");
    if ($headerEnd === false) $headerEnd = strpos($rawEmail, "\n\n");
    if ($headerEnd === false) return null;

    $headers = substr($rawEmail, 0, $headerEnd);

    // Fecha
    $date = null;
    if (preg_match('/^Date:\s*(.+)$/mi', $headers, $m)) {
        $date = trim($m[1]);
    }

    // Subject
    $subject = '';
    if (preg_match('/^Subject:\s*(.+)$/mi', $headers, $m)) {
        $subject = trim($m[1]);
        if (strpos($subject, '=?') !== false) {
            $subject = mb_decode_mimeheader($subject);
        }
    }

    // Tiene adjunto?
    if (!preg_match('/filename[*]?=/i', $rawEmail)) return null;

    // Extraer primer filename
    $filename = null;
    if (preg_match('/filename\*=(?:UTF-8\'\'|utf-8\'\')(.+?)(?:\r?\n|\s|;|$)/i', $rawEmail, $m)) {
        $filename = urldecode(trim($m[1], '"'));
    } elseif (preg_match('/filename="?([^"\r\n;]+)"?/i', $rawEmail, $m)) {
        $filename = trim($m[1], '"');
    }

    return [
        'subject' => $subject,
        'date' => $date,
        'filename' => $filename,
    ];
}

function limpiarTitulo(string $subject): string {
    // NOMBRE_DOC__CLIENTE___FECHA → NOMBRE_DOC - CLIENTE
    $titulo = preg_replace('/___.*$/', '', $subject);
    $titulo = preg_replace('/__/', ' - ', $titulo);
    // Quitar prefijos de reenvío
    $titulo = preg_replace('/^(Re|Fwd|Rv):\s*/i', '', $titulo);
    return trim($titulo);
}

function determinarTipoDoc(string $subject, string $filename): array {
    global $CONVENCIONES;
    $texto = strtoupper($subject . ' ' . $filename);

    foreach ($CONVENCIONES as $nombre => $ids) {
        if (stripos($texto, $nombre) !== false) {
            return $ids;
        }
    }
    return [2, 1];
}
