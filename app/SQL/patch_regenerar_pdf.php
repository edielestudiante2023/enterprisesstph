<?php
/**
 * Patcher: Regenerar PDF en todos los módulos
 * Uso: php patch_regenerar_pdf.php
 */
if (php_sapi_name() !== 'cli') die('Solo CLI.');

$base = dirname(__DIR__, 2);

$modules = [
    ['acta-visita',            'acta_visita',            'ActaVisitaController.php',                 'acta'],
    ['senalizacion',           'senalizacion',           'InspeccionSenalizacionController.php',      'inspeccion'],
    ['inspeccion-locativa',    'inspeccion_locativa',     'InspeccionLocativaController.php',          'inspeccion'],
    ['extintores',             'extintores',             'InspeccionExtintoresController.php',        'inspeccion'],
    ['botiquin',               'botiquin',               'InspeccionBotiquinController.php',          'inspeccion'],
    ['gabinetes',              'gabinetes',              'InspeccionGabineteController.php',          'inspeccion'],
    ['comunicaciones',         'comunicaciones',         'InspeccionComunicacionController.php',      'inspeccion'],
    ['recursos-seguridad',     'recursos-seguridad',     'InspeccionRecursosSeguridadController.php', 'inspeccion'],
    ['probabilidad-peligros',  'probabilidad-peligros',  'ProbabilidadPeligrosController.php',       'inspeccion'],
    ['matriz-vulnerabilidad',  'matriz-vulnerabilidad',  'MatrizVulnerabilidadController.php',       'inspeccion'],
    ['plan-emergencia',        'plan-emergencia',        'PlanEmergenciaController.php',             'inspeccion'],
    ['simulacro',              'simulacro',              'EvaluacionSimulacroController.php',        'eval'],
    ['hv-brigadista',          'hv-brigadista',          'HvBrigadistaController.php',               'hv'],
    ['dotacion-vigilante',     'dotacion-vigilante',     'DotacionVigilanteController.php',          'inspeccion'],
    ['dotacion-aseadora',      'dotacion-aseadora',      'DotacionAseadoraController.php',           'inspeccion'],
    ['dotacion-todero',        'dotacion-todero',        'DotacionToderoController.php',             'inspeccion'],
    ['auditoria-zona-residuos','auditoria-zona-residuos','AuditoriaZonaResiduosController.php',     'inspeccion'],
    ['reporte-capacitacion',   'reporte-capacitacion',   'ReporteCapacitacionController.php',        'inspeccion'],
    ['preparacion-simulacro',  'preparacion-simulacro',  'PreparacionSimulacroController.php',       'inspeccion'],
    ['asistencia-induccion',   'asistencia-induccion',   'AsistenciaInduccionController.php',        'inspeccion'],
    ['limpieza-desinfeccion',  'limpieza-desinfeccion',  'ProgramaLimpiezaController.php',           'inspeccion'],
    ['residuos-solidos',       'residuos-solidos',       'ProgramaResiduosController.php',           'inspeccion'],
    ['control-plagas',         'control-plagas',         'ProgramaPlagasController.php',             'inspeccion'],
    ['plan-saneamiento',       'plan-saneamiento',       'PlanSaneamientoController.php',            'inspeccion'],
    ['kpi-limpieza',           'kpi-limpieza',           'KpiLimpiezaController.php',                'inspeccion'],
    ['kpi-residuos',           'kpi-residuos',           'KpiResiduosController.php',                'inspeccion'],
    ['kpi-plagas',             'kpi-plagas',             'KpiPlagasController.php',                  'inspeccion'],
    ['kpi-agua-potable',       'kpi-agua-potable',       'KpiAguaPotableController.php',             'inspeccion'],
];

$stats = ['vOk' => 0, 'vSkip' => 0, 'cOk' => 0, 'cSkip' => 0, 'rOk' => 0, 'err' => 0];

echo "=== PATCH: Regenerar PDF ===\n\n";

// ── VIEWS ──
echo "-- VISTAS --\n";
foreach ($modules as $m) {
    $route = $m[0];
    $viewDir = $m[1];
    $varName = $m[3];
    $f = $base . '/app/Views/inspecciones/' . $viewDir . '/view.php';
    if (!file_exists($f)) { echo "[ERR] {$f}\n"; $stats['err']++; continue; }
    $c = file_get_contents($f);
    if (strpos($c, 'regenerar') !== false) { echo "[SKIP] {$viewDir}\n"; $stats['vSkip']++; continue; }

    // Build regenerar button HTML as plain text
    $btn = buildViewButton($route, $varName);

    // Find insertion point: after the "Ver PDF" endif block, or after </a> for KPIs
    $pdfPos = strpos($c, '/pdf/');
    if ($pdfPos === false) { echo "[ERR] {$viewDir} - no /pdf/\n"; $stats['err']++; continue; }

    $endifTag = '<' . '?php endif; ?' . '>';
    $endifPos = strpos($c, $endifTag, $pdfPos);
    if ($endifPos !== false) {
        $insertAt = $endifPos + strlen($endifTag);
        $nl = strpos($c, "\n", $insertAt);
        if ($nl !== false) $insertAt = $nl + 1;
    } else {
        $aClose = strpos($c, '</a>', $pdfPos);
        if ($aClose === false) { echo "[ERR] {$viewDir} - no </a>\n"; $stats['err']++; continue; }
        $nl = strpos($c, "\n", $aClose);
        $insertAt = ($nl !== false) ? $nl + 1 : $aClose + 4;
    }

    $c = substr($c, 0, $insertAt) . $btn . "\n" . substr($c, $insertAt);
    file_put_contents($f, $c);
    echo "[OK] {$viewDir}\n";
    $stats['vOk']++;
}

// ── CONTROLLERS ──
echo "\n-- CONTROLADORES --\n";
foreach ($modules as $m) {
    $route = $m[0];
    $ctrlFile = $m[2];
    $f = $base . '/app/Controllers/Inspecciones/' . $ctrlFile;
    if (!file_exists($f)) { echo "[ERR] {$f}\n"; $stats['err']++; continue; }
    $c = file_get_contents($f);
    if (strpos($c, 'regenerarPdf') !== false) { echo "[SKIP] {$ctrlFile}\n"; $stats['cSkip']++; continue; }

    // Detect model variable
    $modelVar = '$this->inspeccionModel';
    if (preg_match('/protected\s+\w+\s+(\$model)\s*[;=]/', $c)) $modelVar = '$this->model';
    elseif (preg_match('/protected\s+\w+\s+(\$\w+)\s*;/', $c, $mm)) $modelVar = '$this->' . ltrim($mm[1], '$');

    // Detect PDF method
    $pdfMethod = 'generarPdfInterno';
    if (preg_match('/private\s+function\s+(generarPdf\w*)\s*\(/', $c, $mm)) $pdfMethod = $mm[1];

    $hasUpload = strpos($c, 'uploadToReportes') !== false;
    $method = buildControllerMethod($route, $modelVar, $pdfMethod, $hasUpload);

    // Find insertion point
    $marker = strpos($c, 'private function');
    if ($marker === false) $marker = strrpos($c, '}');
    if ($marker === false) { echo "[ERR] {$ctrlFile} - no insert point\n"; $stats['err']++; continue; }

    $c = substr($c, 0, $marker) . $method . substr($c, $marker);
    file_put_contents($f, $c);
    echo "[OK] {$ctrlFile}\n";
    $stats['cOk']++;
}

// ── ROUTES ──
echo "\n-- RUTAS --\n";
$rc = file_get_contents($base . '/app/Config/Routes.php');
foreach ($modules as $m) {
    $route = $m[0];
    $ctrlFile = $m[2];
    if (strpos($rc, $route . '/regenerar') !== false) { echo "[SKIP] {$route}\n"; continue; }

    $pdfRouteStr = "'{$route}/pdf/(:num)'";
    $pos = strpos($rc, $pdfRouteStr);
    if ($pos === false) { echo "[ERR] {$route} - no pdf route\n"; $stats['err']++; continue; }

    $lineEnd = strpos($rc, "\n", $pos);
    if ($lineEnd === false) $lineEnd = strlen($rc);

    // Get controller name from the pdf route line
    $pdfLine = substr($rc, $pos, $lineEnd - $pos);
    $ctrlName = str_replace('.php', '', $ctrlFile);
    if (preg_match('/(\w+Controller)::\w+/', $pdfLine, $mm)) $ctrlName = $mm[1];

    $newLine = "\n    \$routes->get('" . $route . "/regenerar/(:num)', '" . $ctrlName . "::regenerarPdf/\$1');";
    $rc = substr($rc, 0, $lineEnd) . $newLine . substr($rc, $lineEnd);
    echo "[OK] {$route}\n";
    $stats['rOk']++;
}
file_put_contents($base . '/app/Config/Routes.php', $rc);

echo "\n=== RESUMEN ===\n";
echo "Vistas: {$stats['vOk']} OK, {$stats['vSkip']} skip\n";
echo "Controladores: {$stats['cOk']} OK, {$stats['cSkip']} skip\n";
echo "Rutas: {$stats['rOk']} OK\n";
echo "Errores: {$stats['err']}\n";
echo ($stats['err'] === 0) ? "COMPLETADO SIN ERRORES.\n" : "HAY ERRORES.\n";

// ═══════════════════════════════════════
// Helper functions
// ═══════════════════════════════════════

function buildViewButton(string $route, string $varName): string
{
    $phpOpen = '<' . '?php';
    $phpEnd = '?' . '>';
    $phpEcho = '<' . '?=';
    return
        '    ' . $phpOpen . ' if ($' . $varName . '[\'estado\'] === \'completo\'): ' . $phpEnd . "\n" .
        '    <a href="/inspecciones/' . $route . '/regenerar/' . $phpEcho . ' $' . $varName . '[\'id\'] ' . $phpEnd . '" class="btn btn-pwa btn-pwa-outline" onclick="return confirm(\'¿Regenerar el PDF con la plantilla actual?\')">' . "\n" .
        '        <i class="fas fa-sync-alt me-2"></i>Regenerar PDF' . "\n" .
        '    </a>' . "\n" .
        '    ' . $phpOpen . ' endif; ' . $phpEnd;
}

function buildControllerMethod(string $route, string $modelVar, string $pdfMethod, bool $hasUpload): string
{
    $lines = [];
    $lines[] = '    public function regenerarPdf($id)';
    $lines[] = '    {';
    $lines[] = '        $inspeccion = ' . $modelVar . '->find($id);';
    $lines[] = '        if (!$inspeccion || ($inspeccion[\'estado\'] ?? \'\') !== \'completo\') {';
    $lines[] = '            return redirect()->to(\'/inspecciones/' . $route . '\')->with(\'error\', \'Solo se puede regenerar un registro finalizado.\');';
    $lines[] = '        }';
    $lines[] = '';
    $lines[] = '        $pdfPath = $this->' . $pdfMethod . '($id);';
    $lines[] = '';
    $lines[] = '        ' . $modelVar . '->update($id, [';
    $lines[] = '            \'ruta_pdf\' => $pdfPath,';
    $lines[] = '        ]);';
    if ($hasUpload) {
        $lines[] = '';
        $lines[] = '        $inspeccion = ' . $modelVar . '->find($id);';
        $lines[] = '        $this->uploadToReportes($inspeccion, $pdfPath);';
    }
    $lines[] = '';
    $lines[] = '        return redirect()->to("/inspecciones/' . $route . '/view/{$id}")->with(\'msg\', \'PDF regenerado exitosamente.\');';
    $lines[] = '    }';
    $lines[] = '';

    return implode("\n", $lines) . "\n    ";
}
