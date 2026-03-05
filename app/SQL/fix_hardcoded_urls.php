<?php
/**
 * Fix: Replace all hardcoded /inspecciones/ URLs with base_url() in Views
 * Usage: php app/SQL/fix_hardcoded_urls.php [--dry-run]
 */

if (php_sapi_name() !== 'cli') die("Solo CLI.\n");

$dryRun = in_array('--dry-run', $argv);
$base = dirname(__DIR__, 2);
$viewsDir = $base . '/app/Views';

$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($viewsDir, RecursiveDirectoryIterator::SKIP_DOTS)
);

$totalFiles = 0;
$totalReplacements = 0;

// Build PHP echo tag safely to avoid parser issues
$po = '<' . '?= ';  // php open echo
$pc = ' ?' . '>';    // php close

foreach ($files as $file) {
    if ($file->getExtension() !== 'php') continue;

    $path = $file->getPathname();
    $content = file_get_contents($path);
    $original = $content;
    $fileReplacements = 0;

    $phpOpenTag = '<' . '?';

    // Pattern 1: href="/inspecciones/..."
    $content = preg_replace_callback(
        '/href="(\/inspecciones\/[^"]*)"/',
        function($m) use (&$fileReplacements, $po, $pc, $phpOpenTag) {
            if (strpos($m[0], 'base_url') !== false) return $m[0];
            $fileReplacements++;
            $url = $m[1];
            if (strpos($url, $phpOpenTag) !== false) {
                $pos = strpos($url, $phpOpenTag);
                $static = substr($url, 0, $pos);
                $dynamic = substr($url, $pos);
                return 'href="' . $po . 'base_url(\'' . $static . '\')' . $pc . $dynamic . '"';
            }
            return 'href="' . $po . 'base_url(\'' . $url . '\')' . $pc . '"';
        },
        $content
    );

    // Pattern 2: action="/inspecciones/..."
    $content = preg_replace_callback(
        '/action="(\/inspecciones\/[^"]*)"/',
        function($m) use (&$fileReplacements, $po, $pc, $phpOpenTag) {
            if (strpos($m[0], 'base_url') !== false) return $m[0];
            $fileReplacements++;
            $url = $m[1];
            if (strpos($url, $phpOpenTag) !== false) {
                $pos = strpos($url, $phpOpenTag);
                $static = substr($url, 0, $pos);
                $dynamic = substr($url, $pos);
                return 'action="' . $po . 'base_url(\'' . $static . '\')' . $pc . $dynamic . '"';
            }
            return 'action="' . $po . 'base_url(\'' . $url . '\')' . $pc . '"';
        },
        $content
    );

    // Pattern 3: url: '/inspecciones/...' (JS ajax)
    $content = preg_replace_callback(
        "/url: '(\/inspecciones\/[^']*)'/",
        function($m) use (&$fileReplacements, $po, $pc) {
            if (strpos($m[0], 'base_url') !== false) return $m[0];
            $fileReplacements++;
            return "url: '" . $po . "base_url('" . $m[1] . "')" . $pc . "'";
        },
        $content
    );

    // Pattern 4: window.location.href = '/inspecciones/...'
    $content = preg_replace_callback(
        "/window\.location\.href\s*=\s*'(\/inspecciones\/[^']*)'/",
        function($m) use (&$fileReplacements, $po, $pc) {
            if (strpos($m[0], 'base_url') !== false) return $m[0];
            $fileReplacements++;
            return "window.location.href = '" . $po . "base_url('" . $m[1] . "')" . $pc . "'";
        },
        $content
    );

    // Pattern 5: PHP assignments = '/inspecciones/...' or ? '/insp...' : '/insp...'
    $content = preg_replace_callback(
        "/([\=\?\:]\s*)'(\/inspecciones\/[^']*)'/",
        function($m) use (&$fileReplacements) {
            if (strpos($m[0], 'base_url') !== false) return $m[0];
            $fileReplacements++;
            return $m[1] . "base_url('" . $m[2] . "')";
        },
        $content
    );

    // Pattern 6: href="/manifest_inspecciones..."
    $content = preg_replace_callback(
        '/href="(\/manifest_inspecciones[^"]*)"/',
        function($m) use (&$fileReplacements, $po, $pc) {
            if (strpos($m[0], 'base_url') !== false) return $m[0];
            $fileReplacements++;
            return 'href="' . $po . 'base_url(\'' . $m[1] . '\')' . $pc . '"';
        },
        $content
    );

    // Pattern 7: href="/icons/..."
    $content = preg_replace_callback(
        '/href="(\/icons\/[^"]*)"/',
        function($m) use (&$fileReplacements, $po, $pc) {
            if (strpos($m[0], 'base_url') !== false) return $m[0];
            $fileReplacements++;
            return 'href="' . $po . 'base_url(\'' . $m[1] . '\')' . $pc . '"';
        },
        $content
    );

    if ($content !== $original) {
        $totalFiles++;
        $totalReplacements += $fileReplacements;
        $relPath = str_replace($base . '/', '', str_replace('\\', '/', $path));
        echo "[" . ($dryRun ? 'DRY' : 'FIX') . "] {$relPath} ({$fileReplacements} reemplazos)\n";
        if (!$dryRun) {
            file_put_contents($path, $content);
        }
    }
}

echo "\n=== RESUMEN ===\n";
echo "Archivos modificados: {$totalFiles}\n";
echo "Reemplazos totales: {$totalReplacements}\n";
echo $dryRun ? "(DRY RUN — ningun archivo fue modificado)\n" : "COMPLETADO.\n";
