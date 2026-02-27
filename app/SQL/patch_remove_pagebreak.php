<?php
/**
 * Patcher: Eliminar TODOS los page-break de plantillas PDF
 * Uso: php app/SQL/patch_remove_pagebreak.php
 *
 * Elimina:
 * 1. Líneas CSS con .page-break { page-break-before: always; } (inline y multi-line)
 * 2. Divs <div class="page-break"></div>
 */
if (php_sapi_name() !== 'cli') die('Solo CLI.');

$base = dirname(__DIR__, 2);
$viewsDir = $base . '/app/Views/inspecciones';

// Find all pdf.php files
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($viewsDir, RecursiveDirectoryIterator::SKIP_DOTS)
);

$stats = ['files' => 0, 'css_removed' => 0, 'divs_removed' => 0, 'skipped' => 0];

echo "=== PATCH: Eliminar page-break de PDFs ===\n\n";

foreach ($iterator as $file) {
    if ($file->getFilename() !== 'pdf.php') continue;

    $path = $file->getPathname();
    $relative = str_replace($viewsDir . DIRECTORY_SEPARATOR, '', $path);
    $content = file_get_contents($path);
    $original = $content;

    // 1. Remove inline CSS: .page-break { page-break-before: always; }
    //    Pattern: "        .page-break { page-break-before: always; }\n"
    $content = preg_replace('/\s*\.page-break\s*\{\s*page-break-before:\s*always;\s*\}\n?/', '', $content);

    // 2. Remove multi-line CSS block:
    //    .page-break {
    //        page-break-before: always;
    //    }
    $content = preg_replace('/\s*\.page-break\s*\{\s*\n\s*page-break-before:\s*always;\s*\n\s*\}\n?/', '', $content);

    // 3. Remove div usage: <div class="page-break"></div>
    //    With optional leading whitespace and trailing newline
    $content = preg_replace('/\s*<div class="page-break"><\/div>\s*\n?/', "\n", $content);

    // 4. Clean up any double blank lines created by removal
    $content = preg_replace('/\n{3,}/', "\n\n", $content);

    if ($content === $original) {
        echo "[SKIP] {$relative} — no page-break found\n";
        $stats['skipped']++;
        continue;
    }

    // Count removals
    $cssCount = preg_match_all('/page-break-before/', $original) - preg_match_all('/page-break-before/', $content);
    $divCount = preg_match_all('/class="page-break"/', $original) - preg_match_all('/class="page-break"/', $content);

    file_put_contents($path, $content);
    echo "[OK]   {$relative} — CSS: -{$cssCount}, DIVs: -{$divCount}\n";
    $stats['files']++;
    $stats['css_removed'] += $cssCount;
    $stats['divs_removed'] += $divCount;
}

echo "\n=== RESUMEN ===\n";
echo "Archivos modificados: {$stats['files']}\n";
echo "CSS .page-break eliminados: {$stats['css_removed']}\n";
echo "DIV page-break eliminados: {$stats['divs_removed']}\n";
echo "Archivos sin cambio: {$stats['skipped']}\n";
echo "COMPLETADO.\n";
