<?php
/**
 * Test: Renderizar plantilla PDF agua-potable y mostrar texto resultante
 * Uso: php app/SQL/test_pdf_text.php
 */
if (php_sapi_name() !== 'cli') die('Solo CLI.');

$nombreCliente = 'CONJUNTO RESIDENCIAL SANDALO';
$cantTanques = '2';
$capIndividual = 'dims 7x5x2.50m = 75m3';
$capTotal = '150 Metros cubicos';

$html = file_get_contents(dirname(__DIR__) . '/Views/inspecciones/agua-potable/pdf.php');

// Replace PHP echos with values
$html = str_replace('<?= esc($nombreCliente) ?>', $nombreCliente, $html);
$html = str_replace('<?= esc($cantTanques) ?>', $cantTanques, $html);
$html = str_replace('<?= esc($capIndividual) ?>', $capIndividual, $html);
$html = str_replace('<?= esc($capTotal) ?>', $capTotal, $html);

// Remove remaining PHP blocks
$html = preg_replace('/<\?php.*?\?>/s', '', $html);
$html = preg_replace('/<\?=.*?\?>/s', '', $html);

// Strip HTML tags
$text = strip_tags($html);
// Clean up whitespace
$lines = explode("\n", $text);
foreach ($lines as $line) {
    $line = trim($line);
    if ($line !== '') echo $line . "\n";
}
