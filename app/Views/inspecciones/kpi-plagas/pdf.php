<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    @page { margin: 80px 50px 60px 60px; }
    body { font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif; font-size: 10px; color: #333; line-height: 1.5; padding: 0; margin: 0; }
    .header-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
    .header-table td { border: 2px solid #333; padding: 6px 8px; vertical-align: middle; }
    .header-table .logo-cell { width: 100px; text-align: center; }
    .header-table .logo-cell img { max-width: 90px; max-height: 55px; }
    .header-table .title-cell { text-align: center; font-weight: bold; font-size: 10px; }
    .header-table .code-cell { width: 120px; font-size: 9px; }
    .main-title { text-align: center; font-weight: bold; font-size: 13px; color: #1c2437; margin: 20px 0 15px; }
    p { margin: 4px 0 8px; text-align: justify; }
    .data-table { width: 100%; border-collapse: collapse; margin: 12px 0; font-size: 10px; }
    .data-table th { background: #1c2437; color: white; padding: 6px 8px; text-align: left; font-weight: bold; border: 1px solid #333; }
    .data-table td { padding: 5px 8px; border: 1px solid #ccc; vertical-align: top; }
    .evidence-title { font-weight: bold; font-size: 11px; color: #1c2437; margin-top: 20px; margin-bottom: 8px; text-align: center; }
    .evidence-table { width: 100%; border-collapse: collapse; margin-top: 5px; }
    .evidence-table td { text-align: center; padding: 5px; vertical-align: top; }
    .evidence-table img { max-width: 220px; max-height: 200px; }
</style>
</head>
<body>
<?php
$nombreCliente = $cliente['nombre_cliente'] ?? 'CLIENTE';
$fechaDoc = !empty($inspeccion['fecha_inspeccion']) ? date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) : date('d/m/Y');
$fechaSgsst = !empty($cliente['fecha_sgsst']) ? date('d/m/Y', strtotime($cliente['fecha_sgsst'])) : $fechaDoc;
?>

<table class="header-table">
    <tr>
        <td class="logo-cell">
            <?php if (!empty($logoBase64)): ?>
                <img src="<?= $logoBase64 ?>" alt="Logo">
            <?php else: ?>
                <strong>LOGO</strong>
            <?php endif; ?>
        </td>
        <td class="title-cell">
            SISTEMA DE GESTIÓN DE SEGURIDAD Y SALUD EN EL TRABAJO<br>
            <?= $pdfTitle ?>
        </td>
        <td class="code-cell">
            <strong>Código:</strong> <?= $pdfCode ?><br>
            <strong>Versión:</strong> 001<br>
            <strong>Fecha:</strong> <?= $fechaSgsst ?>
        </td>
    </tr>
</table>

<div class="main-title">INDICADORES DEL PROGRAMA</div>

<p><?= $pdfIntro ?> <strong><?= esc($nombreCliente) ?></strong>, se evalúa el siguiente indicador de gestión:</p>

<table class="data-table">
    <tr><th style="width:35%;">FECHA DE LA REVISIÓN</th><td><?= $fechaDoc ?></td></tr>
    <tr><th>CLIENTE</th><td><?= esc($nombreCliente) ?></td></tr>
    <tr><th>INDICADOR</th><td><?= esc($inspeccion['indicador']) ?></td></tr>
    <tr><th>CUMPLIMIENTO</th><td><?= number_format($inspeccion['cumplimiento'], 1) ?>%</td></tr>
</table>

<div class="evidence-title">EVIDENCIAS</div>
<?php
$evidencias = [];
for ($i = 1; $i <= 4; $i++) {
    $campo = "registro_formato_$i";
    if (!empty($fotosBase64[$campo])) {
        $evidencias[] = $fotosBase64[$campo];
    }
}
?>
<?php if (!empty($evidencias)): ?>
<table class="evidence-table">
    <?php $chunks = array_chunk($evidencias, 2); ?>
    <?php foreach ($chunks as $row): ?>
    <tr>
        <?php foreach ($row as $foto): ?>
        <td><img src="<?= $foto ?>" alt="Evidencia"></td>
        <?php endforeach; ?>
        <?php if (count($row) < 2): ?><td></td><?php endif; ?>
    </tr>
    <?php endforeach; ?>
</table>
<?php else: ?>
<p style="text-align:center; color:#999;">Sin evidencias adjuntas.</p>
<?php endif; ?>

</body>
</html>
