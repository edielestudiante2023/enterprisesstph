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
    .section-title { background: #1c2437; color: white; padding: 4px 8px; font-weight: bold; font-size: 10px; margin: 15px 0 8px; }
    p { margin: 4px 0 8px; text-align: justify; }
    .data-table { width: 100%; border-collapse: collapse; margin: 8px 0; font-size: 10px; }
    .data-table th { background: #1c2437; color: white; padding: 6px 8px; text-align: left; font-weight: bold; border: 1px solid #333; }
    .data-table td { padding: 5px 8px; border: 1px solid #ccc; vertical-align: top; }
    .evidence-table { width: 100%; border-collapse: collapse; margin-top: 5px; }
    .evidence-table td { text-align: center; padding: 5px; vertical-align: top; }
    .evidence-table img { max-width: 220px; max-height: 200px; }
    .badge-cumple { color: #198754; font-weight: bold; }
    .badge-nocumple { color: #dc3545; font-weight: bold; }
    .resumen-table { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 10px; }
    .resumen-table th { background: #e8e8e8; padding: 5px 8px; border: 1px solid #ccc; text-align: center; font-size: 9px; }
    .resumen-table td { padding: 5px 8px; border: 1px solid #ccc; text-align: center; }
</style>
</head>
<body>
<?php
$nombreCliente = $cliente['nombre_cliente'] ?? 'CLIENTE';
$fechaDoc = !empty($inspeccion['fecha_inspeccion']) ? date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) : date('d/m/Y');
$fechaSgsst = !empty($cliente['fecha_sgsst']) ? date('d/m/Y', strtotime($cliente['fecha_sgsst'])) : $fechaDoc;
$totalIndicadores = count($indicadoresData ?? []);
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
            SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO<br>
            <?= $pdfTitle ?>
        </td>
        <td class="code-cell">
            <strong>Codigo:</strong> <?= $pdfCode ?><br>
            <strong>Version:</strong> 001<br>
            <strong>Fecha:</strong> <?= $fechaSgsst ?>
        </td>
    </tr>
</table>

<div class="main-title">INDICADORES DEL PROGRAMA</div>

<p><?= $pdfIntro ?> <strong><?= esc($nombreCliente) ?></strong>, se evaluan los siguientes indicadores de gestion:</p>

<!-- Datos generales -->
<table class="data-table">
    <tr><th style="width:35%;">FECHA DE LA REVISION</th><td><?= $fechaDoc ?></td></tr>
    <tr><th>CLIENTE</th><td><?= esc($nombreCliente) ?></td></tr>
    <tr><th>INDICADORES EVALUADOS</th><td><?= $totalIndicadores ?></td></tr>
</table>

<!-- Cada indicador -->
<?php foreach ($indicadoresData as $idx => $indData):
    $rec = $indData['registro'];
    $fotos = $indData['fotosBase64'];
    $cfg = $indicadorConfig[$rec['indicador']] ?? null;
?>
<div class="section-title">INDICADOR <?= $idx + 1 ?>: <?= esc($rec['indicador']) ?></div>
<table class="data-table">
    <?php if ($cfg): ?>
    <tr><th style="width:35%;">FORMULA</th><td><?= esc($cfg['formula']) ?></td></tr>
    <tr><th>META</th><td><?= esc($cfg['meta_texto']) ?></td></tr>
    <?php endif; ?>
    <?php if ($rec['valor_numerador'] !== null && $rec['valor_denominador'] !== null): ?>
    <tr><th><?= esc($cfg['label_numerador'] ?? 'NUMERADOR') ?></th><td><?= esc($rec['valor_numerador']) ?></td></tr>
    <tr><th><?= esc($cfg['label_denominador'] ?? 'DENOMINADOR') ?></th><td><?= esc($rec['valor_denominador']) ?></td></tr>
    <?php endif; ?>
    <tr><th>CUMPLIMIENTO</th><td><strong><?= number_format($rec['cumplimiento'], 1) ?>%</strong></td></tr>
    <?php if (!empty($rec['calificacion_cualitativa'])): ?>
    <tr><th>CALIFICACION</th><td class="<?= $rec['calificacion_cualitativa'] === 'CUMPLE' ? 'badge-cumple' : 'badge-nocumple' ?>"><?= esc($rec['calificacion_cualitativa']) ?></td></tr>
    <?php endif; ?>
    <?php if (!empty($rec['observaciones'])): ?>
    <tr><th>OBSERVACIONES</th><td><?= esc($rec['observaciones']) ?></td></tr>
    <?php endif; ?>
</table>

<?php
$evidencias = [];
for ($i = 1; $i <= 4; $i++) {
    $campo = "registro_formato_$i";
    if (!empty($fotos[$campo])) {
        $evidencias[] = $fotos[$campo];
    }
}
?>
<?php if (!empty($evidencias)): ?>
<div style="font-weight:bold; font-size:10px; margin-top:8px; margin-bottom:4px;">Evidencias:</div>
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
<?php endif; ?>

<?php endforeach; ?>

<!-- Resumen consolidado -->
<?php if ($totalIndicadores > 1): ?>
<div class="section-title">RESUMEN CONSOLIDADO DE INDICADORES</div>
<table class="resumen-table">
    <tr>
        <th>Indicador</th>
        <th>Meta</th>
        <th>Resultado</th>
        <th>Calificacion</th>
    </tr>
    <?php foreach ($indicadoresData as $indData):
        $rec = $indData['registro'];
        $cfg = $indicadorConfig[$rec['indicador']] ?? null;
    ?>
    <tr>
        <td style="text-align:left;"><?= esc($rec['indicador']) ?></td>
        <td><?= esc($cfg['meta_texto'] ?? '') ?></td>
        <td><strong><?= number_format($rec['cumplimiento'], 1) ?>%</strong></td>
        <td class="<?= ($rec['calificacion_cualitativa'] ?? '') === 'CUMPLE' ? 'badge-cumple' : 'badge-nocumple' ?>">
            <?= esc($rec['calificacion_cualitativa'] ?? '-') ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

</body>
</html>
