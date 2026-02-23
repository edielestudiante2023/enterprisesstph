<?php
/**
 * Generate PWA icons from Enterprise SST logo
 * Run: php generate_icons.php
 */

$sourcePath = __DIR__ . '/public/uploads/logoenterprisesstdorado.jpg';
$iconDir = __DIR__ . '/public/icons/';

if (!file_exists($sourcePath)) {
    echo "Source logo not found at: $sourcePath\n";
    exit(1);
}

if (!is_dir($iconDir)) {
    mkdir($iconDir, 0755, true);
}

$source = imagecreatefromjpeg($sourcePath);
if (!$source) {
    echo "Failed to load source image\n";
    exit(1);
}

$srcW = imagesx($source);
$srcH = imagesy($source);
echo "Source: {$srcW}x{$srcH}\n";

$sizes = [192, 512];

foreach ($sizes as $size) {
    $icon = imagecreatetruecolor($size, $size);

    // Resize source to fill entire icon (no white background, no padding)
    imagecopyresampled($icon, $source, 0, 0, 0, 0, $size, $size, $srcW, $srcH);

    $outPath = $iconDir . "icon-{$size}.png";
    imagepng($icon, $outPath);
    imagedestroy($icon);

    echo "Created: $outPath ({$size}x{$size})\n";
}

imagedestroy($source);
echo "Done!\n";
