<?php
/**
 * Regenerar PDFs masivamente desde la base de datos
 *
 * Este script llama a los endpoints de generación de PDF para cada
 * registro que tiene un enlace roto en tbl_reporte.
 *
 * USO:
 *   LOCAL:      php tools/regenerate_pdfs.php [--dry-run] [--tipo=acta_visita]
 *   PRODUCCIÓN: Ejecutar via HTTP en el servidor (ver instrucciones abajo)
 *
 * Para producción, es más seguro ejecutar via cURL a los endpoints:
 *   curl -b cookies.txt https://phorizontal.cycloidtalent.com/inspecciones/extintores/generatePdf/3
 *
 * Este script genera un archivo .sh con todos los cURL necesarios.
 */

// Configuración
$baseUrl = 'https://phorizontal.cycloidtalent.com';
$dryRun = in_array('--dry-run', $argv);
$tipoFiltro = null;

foreach ($argv as $arg) {
    if (str_starts_with($arg, '--tipo=')) {
        $tipoFiltro = substr($arg, 7);
    }
}

// Mapeo de patrones de enlace → endpoint de regeneración
$regeneradores = [
    'acta_visita' => [
        'pattern' => '%acta_visita_%',
        'endpoint' => '/inspecciones/acta-visita/uploadToReportes/',
        'table' => 'tbl_acta_visita',
        'id_field' => 'id',
    ],
    'extintores' => [
        'pattern' => '%extintores_%',
        'endpoint' => '/inspecciones/extintores/uploadToReportes/',
        'table' => 'tbl_inspeccion_extintores',
        'id_field' => 'id',
    ],
    'locativa' => [
        'pattern' => '%inspeccion_locativa_%',
        'endpoint' => '/inspecciones/locativa/uploadToReportes/',
        'table' => 'tbl_inspeccion_locativa',
        'id_field' => 'id',
    ],
    'senalizacion' => [
        'pattern' => '%senalizacion_%',
        'endpoint' => '/inspecciones/senalizacion/uploadToReportes/',
        'table' => 'tbl_inspeccion_senalizacion',
        'id_field' => 'id',
    ],
    'botiquin' => [
        'pattern' => '%botiquin_%',
        'endpoint' => '/inspecciones/botiquin/uploadToReportes/',
        'table' => 'tbl_inspeccion_botiquin',
        'id_field' => 'id',
    ],
    'gabinete' => [
        'pattern' => '%gabinete_%',
        'endpoint' => '/inspecciones/gabinetes/uploadToReportes/',
        'table' => 'tbl_inspeccion_gabinete',
        'id_field' => 'id',
    ],
    'comunicacion' => [
        'pattern' => '%comunicacion_%',
        'endpoint' => '/inspecciones/comunicaciones/uploadToReportes/',
        'table' => 'tbl_inspeccion_comunicacion',
        'id_field' => 'id',
    ],
    'reporte_capacitacion' => [
        'pattern' => '%reporte_capacitacion_%',
        'endpoint' => '/inspecciones/reporte-capacitacion/uploadToReportes/',
        'table' => 'tbl_reporte_capacitacion',
        'id_field' => 'id',
    ],
    'asistencia_induccion' => [
        'pattern' => '%asistencia_induccion_%',
        'endpoint' => '/inspecciones/asistencia-induccion/uploadToReportes/',
        'table' => 'tbl_asistencia_induccion',
        'id_field' => 'id',
    ],
    'dotacion_vigilante' => [
        'pattern' => '%dotacion_vigilante_%',
        'endpoint' => '/inspecciones/dotacion-vigilante/uploadToReportes/',
        'table' => 'tbl_dotacion_vigilante',
        'id_field' => 'id',
    ],
    'kpi_residuos' => [
        'pattern' => '%kpi-residuos_%',
        'endpoint' => '/inspecciones/kpi-residuos/uploadToReportes/',
        'table' => 'tbl_kpi_residuos',
        'id_field' => 'id',
    ],
    'kpi_limpieza' => [
        'pattern' => '%kpi-limpieza_%',
        'endpoint' => '/inspecciones/kpi-limpieza/uploadToReportes/',
        'table' => 'tbl_kpi_limpieza',
        'id_field' => 'id',
    ],
    'kpi_plagas' => [
        'pattern' => '%kpi-plagas_%',
        'endpoint' => '/inspecciones/kpi-plagas/uploadToReportes/',
        'table' => 'tbl_kpi_plagas',
        'id_field' => 'id',
    ],
    'kpi_agua' => [
        'pattern' => '%kpi-agua-potable_%',
        'endpoint' => '/inspecciones/kpi-agua-potable/uploadToReportes/',
        'table' => 'tbl_kpi_agua_potable',
        'id_field' => 'id',
    ],
    'informe_avances' => [
        'pattern' => '%informe_avances_%',
        'endpoint' => '/informe-avances/uploadToReportes/',
        'table' => 'tbl_informe_avances',
        'id_field' => 'id',
    ],
];

echo "=== Generador de script de regeneración de PDFs ===\n";
echo $dryRun ? "(DRY RUN - solo cuenta, no genera script)\n" : "(Generando script de regeneración)\n";
echo "\n";

// Conectar a BD local para contar registros
$mysqli = @new mysqli('localhost', 'root', '', 'propiedad_horizontal');
if ($mysqli->connect_error) {
    echo "ADVERTENCIA: No se pudo conectar a BD local. Generando script genérico.\n\n";
    $mysqli = null;
}

$scriptLines = [];
$scriptLines[] = '#!/bin/bash';
$scriptLines[] = '# Script de regeneración masiva de PDFs';
$scriptLines[] = '# Generado el ' . date('Y-m-d H:i:s');
$scriptLines[] = '# Ejecutar en el servidor con una sesión de consultor activa';
$scriptLines[] = '';
$scriptLines[] = 'BASE_URL="' . $baseUrl . '"';
$scriptLines[] = 'COOKIE_FILE="/tmp/regenerate_cookies.txt"';
$scriptLines[] = '';
$scriptLines[] = '# IMPORTANTE: Primero loguearse para obtener cookie de sesión';
$scriptLines[] = '# curl -c $COOKIE_FILE -d "email=tu@email.com&password=tupass" $BASE_URL/loginPost';
$scriptLines[] = '';
$scriptLines[] = 'echo "=== Regeneración masiva de PDFs ==="';
$scriptLines[] = 'echo "Inicio: $(date)"';
$scriptLines[] = '';

$totalRegistros = 0;

foreach ($regeneradores as $tipo => $config) {
    if ($tipoFiltro && $tipo !== $tipoFiltro) continue;

    $count = '?';
    if ($mysqli) {
        $result = $mysqli->query("SELECT COUNT(*) as total FROM tbl_reporte WHERE enlace LIKE '{$config['pattern']}'");
        if ($result) {
            $row = $result->fetch_assoc();
            $count = $row['total'];
        }

        // Obtener IDs de la tabla fuente
        $tableCheck = $mysqli->query("SHOW TABLES LIKE '{$config['table']}'");
        if ($tableCheck && $tableCheck->num_rows > 0) {
            $idsResult = $mysqli->query("SELECT {$config['id_field']} FROM {$config['table']} ORDER BY {$config['id_field']}");
            if ($idsResult && $idsResult->num_rows > 0) {
                $scriptLines[] = "# === $tipo ($count reportes en BD, {$idsResult->num_rows} registros fuente) ===";

                while ($idRow = $idsResult->fetch_assoc()) {
                    $id = $idRow[$config['id_field']];
                    $scriptLines[] = "curl -s -b \$COOKIE_FILE \"\$BASE_URL{$config['endpoint']}$id\" && echo \" [$tipo #$id OK]\" || echo \" [$tipo #$id FAIL]\"";
                    $totalRegistros++;
                }
                $scriptLines[] = '';
            }
        }
    }

    echo "$tipo: $count reportes en BD\n";
}

echo "\nTotal de registros a regenerar: $totalRegistros\n";

if (!$dryRun) {
    $scriptPath = __DIR__ . '/regenerate_all_pdfs.sh';
    $scriptLines[] = 'echo ""';
    $scriptLines[] = 'echo "=== Regeneración completada ==="';
    $scriptLines[] = 'echo "Fin: $(date)"';

    file_put_contents($scriptPath, implode("\n", $scriptLines) . "\n");
    echo "\nScript generado en: $scriptPath\n";
    echo "Cópialo al servidor y ejecútalo después de loguearte.\n";
} else {
    echo "\n(Dry run - no se generó script)\n";
}

if ($mysqli) $mysqli->close();
