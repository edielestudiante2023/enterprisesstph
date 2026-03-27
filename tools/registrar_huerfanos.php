<?php
/**
 * Recorre los mbox del Takeout, busca adjuntos que existen como huérfanos
 * en writable/soportes-clientes/ pero no tienen registro en tbl_reporte,
 * y los registra con la fecha del email original.
 */

$mboxDir = $argv[1] ?? '';
$baseUrl = $argv[2] ?? '';
$token   = $argv[3] ?? '';

if (!$mboxDir || !$baseUrl || !$token) {
    die("Uso: php registrar_huerfanos.php <mbox_dir> <base_url> <token>\n");
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
];

// Cargar clientes desde BD
echo "Conectando a BD...\n";
$db = new mysqli();
$db->ssl_set(null, null, null, null, null);
$db->real_connect(
    'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
    'cycloid_userdb',
    getenv('DB_PROD_PASS'),
    'propiedad_horizontal',
    25060,
    null,
    MYSQLI_CLIENT_SSL
);
if ($db->connect_error) die("Error BD: " . $db->connect_error . "\n");

// Mapeo nit → id_cliente
$clientes = [];
$clientesPorNombre = [];
$r = $db->query("SELECT id_cliente, nit_cliente, nombre_cliente FROM tbl_clientes");
while ($row = $r->fetch_assoc()) {
    $clientes[$row['nit_cliente']] = $row['id_cliente'];
    $clientesPorNombre[strtoupper(trim($row['nombre_cliente']))] = $row['id_cliente'];
}

// Cargar enlaces existentes en BD
$enlacesExistentes = [];
$r = $db->query("SELECT enlace FROM tbl_reporte WHERE enlace IS NOT NULL");
while ($row = $r->fetch_assoc()) {
    if (preg_match('/serve-file\/(.+)$/', $row['enlace'], $m)) {
        $enlacesExistentes[$m[1]] = true;
    }
}
echo "Enlaces existentes en BD: " . count($enlacesExistentes) . "\n";

// Cargar huérfanos (archivos en disco sin registro en BD)
$huerfanos = [];
$soportesDir = '/www/wwwroot/phorizontal/enterprisesstph/writable/soportes-clientes/';

// Si corremos en local, usar ruta local
if (!is_dir($soportesDir)) {
    // Cargamos desde el listado que ya tenemos
    $fisicos = file("/tmp/archivos_fisicos.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    foreach ($fisicos as $f) {
        $rel = str_replace($soportesDir, "", $f);
        if (!isset($enlacesExistentes[$rel])) {
            $huerfanos[basename($f)] = $rel; // nombre => ruta relativa
        }
    }
} else {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($soportesDir));
    foreach ($it as $file) {
        if ($file->isFile()) {
            $rel = str_replace($soportesDir, "", $file->getPathname());
            if (!isset($enlacesExistentes[$rel])) {
                $huerfanos[basename($file->getPathname())] = $rel;
            }
        }
    }
}
echo "Huérfanos en disco: " . count($huerfanos) . "\n\n";

// Procesar mbox
$mboxFiles = glob($mboxDir . '/*.mbox');
$mboxFiles = array_filter($mboxFiles, function($f) {
    $name = basename($f);
    return stripos($name, 'Abierto') === false;
});
sort($mboxFiles);

$stats = ['registrados' => 0, 'ya_existia' => 0, 'no_huerfano' => 0, 'error' => 0];

foreach ($mboxFiles as $mboxFile) {
    $label = basename($mboxFile, '.mbox');

    // Leer mbox línea por línea para no agotar memoria
    $fh = fopen($mboxFile, 'r');
    if (!$fh) continue;

    $rawEmail = '';
    $emailCount = 0;
    $emails = [];

    while (($line = fgets($fh)) !== false) {
        if (preg_match('/^From \S+ .+$/', $line) && strlen($rawEmail) > 100) {
            $emails[] = $rawEmail;
            $rawEmail = '';
        } else {
            $rawEmail .= $line;
        }
    }
    if (strlen($rawEmail) > 100) $emails[] = $rawEmail;
    fclose($fh);

    // Procesar de a bloques para no quedarse sin memoria
    $emailChunks = array_chunk($emails, 50);
    unset($emails);

    foreach ($emailChunks as $emailBatch) {
    foreach ($emailBatch as $rawEmail) {
        if (strlen($rawEmail) < 100) continue;

        // Extraer headers
        $headerEnd = strpos($rawEmail, "\r\n\r\n");
        if ($headerEnd === false) $headerEnd = strpos($rawEmail, "\n\n");
        if ($headerEnd === false) continue;
        $headers = substr($rawEmail, 0, $headerEnd);

        // Extraer fecha
        $fechaEmail = null;
        if (preg_match('/^Date:\s*(.+)$/mi', $headers, $dm)) {
            $fechaEmail = trim($dm[1]);
        }

        // Extraer subject
        $subject = '';
        if (preg_match('/^Subject:\s*(.+)$/mi', $headers, $sm)) {
            $subject = trim($sm[1]);
            if (strpos($subject, '=?') !== false) {
                $subject = mb_decode_mimeheader($subject);
            }
        }

        // Buscar boundary
        if (!preg_match('/boundary="?([^"\s;]+)"?/i', $rawEmail, $bm)) continue;
        $boundary = $bm[1];

        // Extraer adjuntos
        $parts = explode('--' . $boundary, $rawEmail);

        foreach ($parts as $part) {
            if (!preg_match('/filename[*]?=/i', $part)) continue;

            $fileName = null;
            if (preg_match('/filename\*=(?:UTF-8\'\'|utf-8\'\')(.+?)(?:\r?\n|\s|;|$)/i', $part, $fnm)) {
                $fileName = urldecode(trim($fnm[1], '"'));
            } elseif (preg_match('/filename="?([^"\r\n;]+)"?/i', $part, $fnm)) {
                $fileName = trim($fnm[1], '"');
            }
            if (!$fileName) continue;

            // ¿Es un huérfano?
            if (!isset($huerfanos[$fileName])) continue;

            $rutaRelativa = $huerfanos[$fileName];
            $nit = dirname($rutaRelativa);
            $idCliente = $clientes[$nit] ?? null;

            if (!$idCliente) continue;

            // Determinar tipo de documento
            $docInfo = determinarTipoDoc($fileName, $subject);

            // Determinar fecha
            $createdAt = date('Y-m-d H:i:s');
            if ($fechaEmail && strtotime($fechaEmail)) {
                $createdAt = date('Y-m-d H:i:s', strtotime($fechaEmail));
            }

            // Título
            $titulo = pathinfo($fileName, PATHINFO_FILENAME);
            if ($subject && strlen($subject) > 5) {
                // Limpiar subject
                $titulo = preg_replace('/___.*$/', '', $subject);
                $titulo = preg_replace('/__/', ' - ', $titulo);
            }

            // Enlace
            $enlace = $baseUrl . '/serve-file/' . $rutaRelativa;

            // Insertar en BD
            $stmt = $db->prepare("INSERT INTO tbl_reporte (id_cliente, id_report_type, id_detailreport, titulo_reporte, enlace, estado, observaciones, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 'CERRADO', 'Registrado desde huerfano+mbox', ?, ?)");
            $stmt->bind_param('iiissss', $idCliente, $docInfo[0], $docInfo[1], $titulo, $enlace, $createdAt, $createdAt);

            if ($stmt->execute()) {
                $stats['registrados']++;
                // Remover del mapa para no registrar dos veces
                unset($huerfanos[$fileName]);
                echo "  OK: $fileName → $nit ($createdAt)\n";
            } else {
                $stats['error']++;
                echo "  ERROR: $fileName — " . $db->error . "\n";
            }
            $stmt->close();
        } // end foreach parts
    } // end foreach emailBatch (single email)
    } // end foreach emailChunks
    unset($emailChunks);
} // end foreach mboxFiles

echo "\n========================================\n";
echo "Huérfanos registrados: {$stats['registrados']}\n";
echo "Errores: {$stats['error']}\n";
echo "Huérfanos que quedaron sin match en mbox: " . count($huerfanos) . "\n";

$db->close();

function determinarTipoDoc(string $fileName, string $subject): array {
    global $CONVENCIONES;
    $texto = strtoupper($subject ?: $fileName);

    foreach ($CONVENCIONES as $nombre => $ids) {
        if (stripos($texto, $nombre) !== false) {
            return $ids;
        }
    }
    return [22, 20]; // genérico
}
