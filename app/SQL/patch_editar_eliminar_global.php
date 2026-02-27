<?php
/**
 * Patcher: Habilitar editar y eliminar para TODOS los registros
 * Uso: php app/SQL/patch_editar_eliminar_global.php
 */
if (php_sapi_name() !== 'cli') die('Solo CLI.');

$base = dirname(__DIR__, 2);
$ctrlDir = $base . '/app/Controllers/Inspecciones/';
$viewsDir = $base . '/app/Views/inspecciones/';

// Safe PHP tags for string building
$PO = '<' . '?php';
$PE = '?' . '>';
$EC = '<' . '?=';

$modules = [
    ['acta-visita',            'acta_visita',            'ActaVisitaController.php'],
    ['senalizacion',           'senalizacion',           'InspeccionSenalizacionController.php'],
    ['inspeccion-locativa',    'inspeccion_locativa',     'InspeccionLocativaController.php'],
    ['extintores',             'extintores',             'InspeccionExtintoresController.php'],
    ['botiquin',               'botiquin',               'InspeccionBotiquinController.php'],
    ['gabinetes',              'gabinetes',              'InspeccionGabineteController.php'],
    ['comunicaciones',         'comunicaciones',         'InspeccionComunicacionController.php'],
    ['recursos-seguridad',     'recursos-seguridad',     'InspeccionRecursosSeguridadController.php'],
    ['probabilidad-peligros',  'probabilidad-peligros',  'ProbabilidadPeligrosController.php'],
    ['matriz-vulnerabilidad',  'matriz-vulnerabilidad',  'MatrizVulnerabilidadController.php'],
    ['plan-emergencia',        'plan-emergencia',        'PlanEmergenciaController.php'],
    ['dotacion-vigilante',     'dotacion-vigilante',     'DotacionVigilanteController.php'],
    ['dotacion-aseadora',      'dotacion-aseadora',      'DotacionAseadoraController.php'],
    ['dotacion-todero',        'dotacion-todero',        'DotacionToderoController.php'],
    ['auditoria-zona-residuos','auditoria-zona-residuos','AuditoriaZonaResiduosController.php'],
    ['reporte-capacitacion',   'reporte-capacitacion',   'ReporteCapacitacionController.php'],
    ['preparacion-simulacro',  'preparacion-simulacro',  'PreparacionSimulacroController.php'],
    ['asistencia-induccion',   'asistencia-induccion',   'AsistenciaInduccionController.php'],
    ['limpieza-desinfeccion',  'limpieza-desinfeccion',  'ProgramaLimpiezaController.php'],
    ['residuos-solidos',       'residuos-solidos',       'ProgramaResiduosController.php'],
    ['control-plagas',         'control-plagas',         'ProgramaPlagasController.php'],
    ['agua-potable',           'agua-potable',           'ProgramaAguaPotableController.php'],
    ['plan-saneamiento',       'plan-saneamiento',       'PlanSaneamientoController.php'],
    ['kpi-limpieza',           'kpi-limpieza',           'KpiLimpiezaController.php'],
    ['kpi-residuos',           'kpi-residuos',           'KpiResiduosController.php'],
    ['kpi-plagas',             'kpi-plagas',             'KpiPlagasController.php'],
    ['kpi-agua-potable',       'kpi-agua-potable',       'KpiAguaPotableController.php'],
];

$stats = ['ctrl_ok' => 0, 'ctrl_skip' => 0, 'view_ok' => 0, 'view_skip' => 0, 'err' => 0];

echo "=== PATCH: Editar/Eliminar Global ===\n\n";

// ──────────────────────────────────────────
// PART 1: CONTROLLERS
// ──────────────────────────────────────────
echo "-- CONTROLADORES --\n";
foreach ($modules as $m) {
    $ctrlFile = $m[2];
    $f = $ctrlDir . $ctrlFile;
    if (!file_exists($f)) { echo "[ERR] {$ctrlFile}\n"; $stats['err']++; continue; }

    $c = file_get_contents($f);
    $original = $c;
    $changes = 0;

    // 1. edit(): Remove redirect to view when estado=completo
    $c = preg_replace(
        '/\n\s*if\s*\(\$\w+\[\'estado\'\]\s*===?\s*\'completo\'\)\s*\{\s*\n\s*return\s+redirect\(\)->to\([^)]*view[^)]*\);\s*\n\s*\}\s*\n/',
        "\n",
        $c, -1, $count
    );
    $changes += $count;

    // 2. update(): Remove estado=completo from compound condition
    $c = preg_replace(
        '/if\s*\(\s*!\$(\w+)\s*\|\|\s*\$\1\[\'estado\'\]\s*===?\s*\'completo\'\s*\)/',
        'if (!$${1})',
        $c, -1, $count
    );
    $changes += $count;

    // 3. delete(): Remove estado=completo guard block
    $c = preg_replace(
        '/\n\s*if\s*\(\$\w+\[\'estado\'\]\s*===?\s*\'completo\'\)\s*\{\s*\n\s*return\s+redirect\(\)->to\([^)]*\)->with\([^)]*\);\s*\n\s*\}\s*\n/',
        "\n",
        $c, -1, $count
    );
    $changes += $count;

    if ($c === $original) {
        echo "[SKIP] {$ctrlFile}\n";
        $stats['ctrl_skip']++;
    } else {
        file_put_contents($f, $c);
        echo "[OK]   {$ctrlFile} ({$changes} cambios)\n";
        $stats['ctrl_ok']++;
    }
}

// ──────────────────────────────────────────
// PART 2: LIST VIEWS (27 standard modules)
// ──────────────────────────────────────────
echo "\n-- VISTAS LIST.PHP --\n";
foreach ($modules as $m) {
    $viewDir = $m[1];
    $f = $viewsDir . $viewDir . '/list.php';
    if (!file_exists($f)) { echo "[ERR] {$viewDir}/list.php\n"; $stats['err']++; continue; }

    $c = file_get_contents($f);
    $original = $c;

    // Step 1: Remove if-borrador guard line
    $c = preg_replace(
        '/[ \t]*<\?php\s+if\s*\(\$\w+\[\'estado\'\]\s*===?\s*\'borrador\'\)\s*:\s*\?>\s*\n/',
        '',
        $c, -1, $count1
    );

    // Step 2: Replace else with if-completo (only first occurrence)
    if ($count1 > 0) {
        $replacement = '                ' . $PO . ' if ($insp[\'estado\'] === \'completo\'): ' . $PE . "\n";
        $c = preg_replace(
            '/[ \t]*<\?php\s+else\s*:\s*\?>\s*\n/',
            $replacement,
            $c, 1
        );
    }

    if ($c === $original) {
        echo "[SKIP] {$viewDir}/list.php\n";
        $stats['view_skip']++;
    } else {
        file_put_contents($f, $c);
        echo "[OK]   {$viewDir}/list.php\n";
        $stats['view_ok']++;
    }
}

// ──────────────────────────────────────────
// PART 3: SPECIAL MODULES
// ──────────────────────────────────────────
echo "\n-- MODULOS ESPECIALES --\n";

// 3a. Simulacro list.php
$simList = $viewsDir . 'simulacro/list.php';
if (file_exists($simList)) {
    $c = file_get_contents($simList);
    $orig = $c;

    // Remove borrador guard
    $c = preg_replace(
        '/[ \t]*<\?php\s+if\s*\(\$ev\[\'estado\'\]\s*===?\s*\'borrador\'\)\s*:\s*\?>\s*\n/',
        '',
        $c
    );

    // Add edit button before Ver button
    $editBtn = '<a href="/inspecciones/simulacro/edit/' . $EC . ' $ev[\'id\'] ' . $PE . '" class="btn btn-sm btn-outline-dark">' . "\n"
             . '                        <i class="fas fa-edit"></i> Editar' . "\n"
             . '                    </a>' . "\n"
             . '                    ';

    if (strpos($c, 'Editar') === false) {
        $c = str_replace(
            '<a href="/inspecciones/simulacro/view/',
            $editBtn . '<a href="/inspecciones/simulacro/view/',
            $c
        );
    }

    if ($c !== $orig) {
        file_put_contents($simList, $c);
        echo "[OK]   simulacro/list.php\n";
    } else {
        echo "[SKIP] simulacro/list.php\n";
    }
}

// 3b. HV Brigadista list.php
$hvList = $viewsDir . 'hv-brigadista/list.php';
if (file_exists($hvList)) {
    $c = file_get_contents($hvList);
    $orig = $c;

    // Remove borrador guard
    $c = preg_replace(
        '/[ \t]*<\?php\s+if\s*\(\$\w+\[\'estado\'\]\s*===?\s*\'borrador\'\)\s*:\s*\?>\s*\n/',
        '',
        $c
    );

    // Add edit button before Ver button
    if (strpos($c, 'Editar') === false && strpos($c, 'hv-brigadista/view/') !== false) {
        $editBtn = '<a href="/inspecciones/hv-brigadista/edit/' . $EC . ' $hv[\'id\'] ' . $PE . '" class="btn btn-sm btn-outline-dark">' . "\n"
                 . '                        <i class="fas fa-edit"></i> Editar' . "\n"
                 . '                    </a>' . "\n"
                 . '                    ';
        $c = str_replace(
            '<a href="/inspecciones/hv-brigadista/view/',
            $editBtn . '<a href="/inspecciones/hv-brigadista/view/',
            $c
        );
    }

    if ($c !== $orig) {
        file_put_contents($hvList, $c);
        echo "[OK]   hv-brigadista/list.php\n";
    } else {
        echo "[SKIP] hv-brigadista/list.php\n";
    }
}

// 3c. Carta Vigia list.php - add edit+delete for all records
$cvList = $viewsDir . 'carta_vigia/list.php';
if (file_exists($cvList)) {
    $c = file_get_contents($cvList);
    $orig = $c;

    // Move delete button outside the if/else block and add edit button
    // Find the closing endif of the firmado check and add edit+delete after it
    $searchEndif = $PO . ' endif; ' . $PE;

    // Check if edit already added
    if (strpos($c, 'carta-vigia/edit/') === false) {
        // Find the endif inside the buttons section (after btn-reenviar)
        $reenviarPos = strpos($c, 'btn-reenviar');
        if ($reenviarPos !== false) {
            // Find the endif after reenviar section
            $endifPos = strpos($c, 'endif;', $reenviarPos);
            if ($endifPos !== false) {
                // Find the closing line of that endif
                $lineEnd = strpos($c, "\n", $endifPos);
                if ($lineEnd !== false) {
                    // Remove the delete button from inside the else block
                    $c = preg_replace(
                        '/\s*<button type="button" class="btn btn-sm btn-outline-danger btn-action btn-eliminar"[^<]*<\/button>\s*\n(\s*' . preg_quote($PO, '/') . ' endif;)/',
                        "\n$1",
                        $c
                    );

                    // Now add edit + delete after the endif
                    $endifPos2 = strpos($c, 'endif;', strpos($c, 'btn-reenviar'));
                    if ($endifPos2 !== false) {
                        $lineEnd2 = strpos($c, "\n", $endifPos2);
                        $afterEndif = "\n"
                            . '                        <a href="/inspecciones/carta-vigia/edit/' . $EC . ' $c[\'id\'] ' . $PE . '" class="btn btn-sm btn-outline-dark btn-action">' . "\n"
                            . '                            <i class="fas fa-edit"></i> Editar' . "\n"
                            . '                        </a>' . "\n"
                            . '                        <button type="button" class="btn btn-sm btn-outline-danger btn-action btn-eliminar" data-id="' . $EC . ' $c[\'id\'] ' . $PE . '" data-nombre="' . $EC . ' esc($c[\'nombre_vigia\']) ' . $PE . '">' . "\n"
                            . '                            <i class="fas fa-trash"></i>' . "\n"
                            . '                        </button>';
                        $c = substr($c, 0, $lineEnd2) . $afterEndif . substr($c, $lineEnd2);
                    }
                }
            }
        }
    }

    if ($c !== $orig) {
        file_put_contents($cvList, $c);
        echo "[OK]   carta_vigia/list.php\n";
    } else {
        echo "[SKIP] carta_vigia/list.php\n";
    }
}

echo "\n=== RESUMEN ===\n";
echo "Controladores: {$stats['ctrl_ok']} OK, {$stats['ctrl_skip']} skip\n";
echo "Vistas list.php: {$stats['view_ok']} OK, {$stats['view_skip']} skip\n";
echo "Errores: {$stats['err']}\n";
echo "COMPLETADO.\n";
