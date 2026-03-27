<?php
// Archivos fisicos
$fisicos = file("/tmp/archivos_fisicos.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$fisicosBase = [];
$basePath = "/www/wwwroot/phorizontal/enterprisesstph/writable/soportes-clientes/";
foreach ($fisicos as $f) {
    $rel = str_replace($basePath, "", $f);
    $fisicosBase[$rel] = $f;
}

// Enlaces en BD
$enlaces = file("/tmp/enlaces_bd.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$enBD = [];
foreach ($enlaces as $e) {
    if (preg_match('/serve-file\/(.+)$/', $e, $m)) {
        $enBD[$m[1]] = true;
    }
}

// Huerfanos
$huerfanos = [];
foreach ($fisicosBase as $rel => $full) {
    if (!isset($enBD[$rel])) {
        $huerfanos[] = $rel;
    }
}

echo "Archivos fisicos: " . count($fisicosBase) . "\n";
echo "Enlaces en BD: " . count($enBD) . "\n";
echo "Huerfanos: " . count($huerfanos) . "\n\n";

$pdfs = 0; $imgs = 0; $otros = 0;
foreach ($huerfanos as $h) {
    $ext = strtolower(pathinfo($h, PATHINFO_EXTENSION));
    if ($ext === "pdf") $pdfs++;
    elseif (in_array($ext, ["png","jpg","jpeg"])) $imgs++;
    else $otros++;
}
echo "Huerfanos PDF: $pdfs\n";
echo "Huerfanos img: $imgs\n";
echo "Huerfanos otros: $otros\n\n";

echo "Primeros 50 huerfanos:\n";
foreach (array_slice($huerfanos, 0, 50) as $h) echo "  $h\n";
