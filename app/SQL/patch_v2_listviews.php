<?php
/**
 * Patcher v2: Fix list.php views - remove borrador guard, show edit+delete always
 * Works line-by-line to find the CORRECT else/endif matching the borrador guard
 * Uso: php app/SQL/patch_v2_listviews.php
 */
if (php_sapi_name() !== 'cli') die('Solo CLI.');

$base = dirname(__DIR__, 2);
$viewsDir = $base . '/app/Views/inspecciones/';

// Safe PHP tags for string building
$PO = '<' . '?php';
$PE = '?' . '>';
$EC = '<' . '?=';

$modules = [
    'acta_visita', 'senalizacion', 'inspeccion_locativa', 'extintores',
    'botiquin', 'gabinetes', 'comunicaciones', 'recursos-seguridad',
    'probabilidad-peligros', 'matriz-vulnerabilidad', 'plan-emergencia',
    'dotacion-vigilante', 'dotacion-aseadora', 'dotacion-todero',
    'auditoria-zona-residuos', 'reporte-capacitacion', 'preparacion-simulacro',
    'asistencia-induccion', 'limpieza-desinfeccion', 'residuos-solidos',
    'control-plagas', 'agua-potable', 'plan-saneamiento',
    'kpi-limpieza', 'kpi-residuos', 'kpi-plagas', 'kpi-agua-potable',
];

$stats = ['ok' => 0, 'skip' => 0, 'err' => 0];

echo "=== PATCH v2: Fix list.php borrador guards ===\n\n";

echo "-- 27 MODULOS ESTANDAR --\n";
foreach ($modules as $mod) {
    $f = $viewsDir . $mod . '/list.php';
    if (!file_exists($f)) {
        echo "[ERR]  {$mod}/list.php not found\n";
        $stats['err']++;
        continue;
    }

    $lines = file($f);
    $content = implode('', $lines);

    // Find the borrador guard and capture variable name
    if (!preg_match('/<\?php\s+if\s*\(\$(\w+)\[\'estado\'\]\s*===?\s*\'borrador\'\)\s*:\s*\?>/', $content, $m)) {
        echo "[SKIP] {$mod}/list.php - no borrador guard\n";
        $stats['skip']++;
        continue;
    }

    $vn = $m[1]; // variable name (e.g., insp, acta)

    // Find the guard line index
    $guardIdx = null;
    foreach ($lines as $i => $line) {
        if (preg_match('/<\?php\s+if\s*\(\$' . $vn . '\[\'estado\'\]\s*===?\s*\'borrador\'\)\s*:\s*\?>/', $line)) {
            $guardIdx = $i;
            break;
        }
    }

    if ($guardIdx === null) {
        echo "[ERR]  {$mod}/list.php - guard line not found\n";
        $stats['err']++;
        continue;
    }

    // Get the indentation of the guard line
    preg_match('/^([ \t]*)/', $lines[$guardIdx], $indentMatch);
    $guardIndent = strlen($indentMatch[1]);

    // Find matching else/elseif/endif at the SAME indentation level
    $elseifIdx = null;
    $elseIdx = null;
    $endifIdx = null;

    for ($i = $guardIdx + 1; $i < count($lines); $i++) {
        $line = $lines[$i];
        $trimmed = trim($line);

        // Skip empty/non-PHP lines
        if (!preg_match('/<\?php\s+(else|elseif|endif)/', $trimmed)) continue;

        // Check indentation matches
        preg_match('/^([ \t]*)/', $line, $im);
        $lineIndent = strlen($im[1]);

        if ($lineIndent !== $guardIndent) continue;

        if (preg_match('/<\?php\s+elseif\s/', $trimmed) && $elseifIdx === null) {
            $elseifIdx = $i;
        } elseif (preg_match('/<\?php\s+else\s*:\s*\?>/', $trimmed) && $elseIdx === null) {
            $elseIdx = $i;
        } elseif (preg_match('/<\?php\s+endif;\s*\?>/', $trimmed)) {
            $endifIdx = $i;
            break;
        }
    }

    if ($endifIdx === null || $elseIdx === null) {
        echo "[ERR]  {$mod}/list.php - no matching else/endif (else={$elseIdx}, endif={$endifIdx})\n";
        $stats['err']++;
        continue;
    }

    // Rebuild the file
    $newLines = [];
    $indent = $indentMatch[1]; // preserve original indent string

    // Everything before the guard line
    for ($i = 0; $i < $guardIdx; $i++) {
        $newLines[] = $lines[$i];
    }

    // Skip the guard line (remove if-borrador)

    // Content between guard and first else/elseif (edit+delete buttons) - keep as-is
    $firstBranch = $elseifIdx ?? $elseIdx;
    for ($i = $guardIdx + 1; $i < $firstBranch; $i++) {
        $newLines[] = $lines[$i];
    }

    if ($elseifIdx !== null) {
        // 3-STATE: borrador / elseif X / else / endif
        // Change elseif to if
        $elseifLine = $lines[$elseifIdx];
        $elseifLine = preg_replace('/<\?php\s+elseif/', $PO . ' if', $elseifLine);
        $newLines[] = $elseifLine;

        // Content between elseif and else
        for ($i = $elseifIdx + 1; $i < $elseIdx; $i++) {
            $newLines[] = $lines[$i];
        }

        // Change else to elseif ($var['estado'] === 'completo'):
        $newLines[] = $indent . $PO . ' elseif ($' . $vn . '[\'estado\'] === \'completo\'): ' . $PE . "\n";

        // Content between else and endif
        for ($i = $elseIdx + 1; $i < $endifIdx; $i++) {
            $newLines[] = $lines[$i];
        }

        // Keep endif
        $newLines[] = $lines[$endifIdx];
        $type = '3-state';
    } else {
        // 2-STATE: borrador / else / endif
        // Replace else with if ($var['estado'] === 'completo'):
        $newLines[] = $indent . $PO . ' if ($' . $vn . '[\'estado\'] === \'completo\'): ' . $PE . "\n";

        // Content between else and endif (view+pdf buttons) - keep as-is
        for ($i = $elseIdx + 1; $i < $endifIdx; $i++) {
            $newLines[] = $lines[$i];
        }

        // Keep endif
        $newLines[] = $lines[$endifIdx];
        $type = '2-state';
    }

    // Everything after endif
    for ($i = $endifIdx + 1; $i < count($lines); $i++) {
        $newLines[] = $lines[$i];
    }

    $newContent = implode('', $newLines);
    file_put_contents($f, $newContent);
    echo "[OK]   {$mod}/list.php ({$type}, \${$vn})\n";
    $stats['ok']++;
}

// ──────────────────────────────────────────
// SPECIAL: simulacro/list.php
// ──────────────────────────────────────────
echo "\n-- MODULOS ESPECIALES --\n";

$simFile = $viewsDir . 'simulacro/list.php';
if (file_exists($simFile)) {
    $c = file_get_contents($simFile);
    $orig = $c;

    // Pattern: if ($ev['estado'] === 'borrador'): ... <form>Finalizar</form> <button>Delete</button> ... endif
    // Fix: keep Finalizar inside borrador guard, move delete button outside
    $pattern = '/(<\?php if \(\$ev\[\'estado\'\] === \'borrador\'\): \?>.*?)(\s*<button type="button" class="btn btn-sm btn-outline-danger btn-delete-ev"[^>]*>.*?<\/button>)\s*\n(\s*<\?php endif; \?>)/s';

    if (preg_match($pattern, $c, $sm)) {
        // Move the delete button after the endif
        $c = preg_replace($pattern, '$1' . "\n" . '$3' . "\n" . '$2', $c);
    }

    if ($c !== $orig) {
        file_put_contents($simFile, $c);
        echo "[OK]   simulacro/list.php\n";
    } else {
        echo "[SKIP] simulacro/list.php\n";
    }
}

// ──────────────────────────────────────────
// SPECIAL: hv-brigadista/list.php
// ──────────────────────────────────────────
$hvFile = $viewsDir . 'hv-brigadista/list.php';
if (file_exists($hvFile)) {
    $c = file_get_contents($hvFile);
    $orig = $c;

    // Same pattern as simulacro but with $hv and btn-delete-hv
    $pattern = '/(<\?php if \(\$hv\[\'estado\'\] === \'borrador\'\): \?>.*?)(\s*<button type="button" class="btn btn-sm btn-outline-danger btn-delete-hv"[^>]*>.*?<\/button>)\s*\n(\s*<\?php endif; \?>)/s';

    if (preg_match($pattern, $c, $sm)) {
        $c = preg_replace($pattern, '$1' . "\n" . '$3' . "\n" . '$2', $c);
    }

    if ($c !== $orig) {
        file_put_contents($hvFile, $c);
        echo "[OK]   hv-brigadista/list.php\n";
    } else {
        echo "[SKIP] hv-brigadista/list.php\n";
    }
}

// ──────────────────────────────────────────
// SPECIAL: carta_vigia/list.php
// ──────────────────────────────────────────
$cvFile = $viewsDir . 'carta_vigia/list.php';
if (file_exists($cvFile)) {
    $c = file_get_contents($cvFile);
    $orig = $c;

    // Current structure inside the firmado if/else:
    //   if (firmado): Ver PDF
    //   else: Reenviar + Eliminar
    //   endif
    // Target:
    //   if (firmado): Ver PDF
    //   else: Reenviar
    //   endif
    //   Editar + Eliminar (always)

    // Step 1: Remove the delete button from inside the else block
    // Match the delete button before the endif
    $deleteBtn = '                        <button type="button" class="btn btn-sm btn-outline-danger btn-action btn-eliminar" data-id="' . $EC . ' $c[\'id\'] ' . $PE . '" data-nombre="' . $EC . ' esc($c[\'nombre_vigia\']) ' . $PE . '">' . "\n"
               . '                            <i class="fas fa-trash"></i>' . "\n"
               . '                        </button>';

    // Find and remove the delete button that's inside the else block (before endif)
    $c = preg_replace(
        '/\s*<button type="button" class="btn btn-sm btn-outline-danger btn-action btn-eliminar"[^>]*>\s*\n\s*<i class="fas fa-trash"><\/i>\s*\n\s*<\/button>\s*\n(\s*<\?php endif;)/',
        "\n$1",
        $c
    );

    // Step 2: Add edit + delete after endif (if not already present)
    if (strpos($c, 'carta-vigia/edit/') === false) {
        $endifLine = $PO . ' endif; ' . $PE;
        $editDeleteBlock = "\n"
            . '                    <a href="/inspecciones/carta-vigia/edit/' . $EC . ' $c[\'id\'] ' . $PE . '" class="btn btn-sm btn-outline-dark btn-action">' . "\n"
            . '                        <i class="fas fa-edit"></i> Editar' . "\n"
            . '                    </a>' . "\n"
            . '                    <button type="button" class="btn btn-sm btn-outline-danger btn-action btn-eliminar" data-id="' . $EC . ' $c[\'id\'] ' . $PE . '" data-nombre="' . $EC . ' esc($c[\'nombre_vigia\']) ' . $PE . '">' . "\n"
            . '                        <i class="fas fa-trash"></i>' . "\n"
            . '                    </button>';

        // Find the endif inside the buttons section (the one after btn-reenviar)
        $reenviarPos = strpos($c, 'btn-reenviar');
        if ($reenviarPos !== false) {
            $endifPos = strpos($c, 'endif;', $reenviarPos);
            if ($endifPos !== false) {
                $lineEnd = strpos($c, "\n", $endifPos);
                if ($lineEnd !== false) {
                    $c = substr($c, 0, $lineEnd) . $editDeleteBlock . substr($c, $lineEnd);
                }
            }
        }
    }

    if ($c !== $orig) {
        file_put_contents($cvFile, $c);
        echo "[OK]   carta_vigia/list.php\n";
    } else {
        echo "[SKIP] carta_vigia/list.php\n";
    }
}

echo "\n=== RESUMEN ===\n";
echo "OK: {$stats['ok']}, Skip: {$stats['skip']}, Errores: {$stats['err']}\n";
echo "COMPLETADO.\n";
