<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    @page {
        margin: 100px 60px 70px 70px;
    }
    body {
        font-family: Helvetica, Arial, sans-serif;
        font-size: 10px;
        color: #333;
        line-height: 1.4;
    }
    table {
        border-collapse: collapse;
        width: 100%;
    }
    .header-table {
        width: 100%;
        border: 1px solid #333;
        margin-bottom: 15px;
    }
    .header-table td {
        border: 1px solid #333;
        padding: 5px 8px;
        vertical-align: middle;
    }
    .header-logo img {
        max-width: 90px;
        max-height: 55px;
    }
    .header-title {
        text-align: center;
        font-weight: bold;
        font-size: 9px;
        line-height: 1.3;
    }
    .header-code {
        text-align: center;
        font-size: 8px;
        width: 120px;
    }
    .titulo-informe {
        text-align: center;
        font-size: 14px;
        font-weight: bold;
        color: #1c2437;
        margin: 15px 0 5px;
    }
    .subtitulo {
        text-align: center;
        font-size: 11px;
        font-weight: bold;
        color: #bd9751;
        margin-bottom: 3px;
    }
    .periodo-text {
        text-align: center;
        font-size: 10px;
        color: #555;
        margin-bottom: 15px;
    }
    .section-title {
        background: #1c2437;
        color: #fff;
        font-weight: bold;
        font-size: 10px;
        padding: 6px 10px;
        margin-top: 15px;
        margin-bottom: 8px;
    }
    .info-table {
        width: 100%;
        margin-bottom: 12px;
    }
    .info-table td {
        padding: 5px 8px;
        border: 1px solid #ddd;
        font-size: 9px;
    }
    .info-table .label-cell {
        background: #f0f0f0;
        font-weight: bold;
        width: 30%;
        color: #1c2437;
    }
    .metricas-table {
        width: 100%;
        margin-bottom: 12px;
    }
    .metricas-table td {
        padding: 8px;
        border: 1px solid #ddd;
        text-align: center;
        font-size: 10px;
    }
    .metricas-table .metric-value {
        font-size: 18px;
        font-weight: bold;
        color: #1c2437;
    }
    .metricas-table .metric-label {
        font-size: 8px;
        color: #777;
        text-transform: uppercase;
    }
    /* Progress bar via table */
    .progress-bar-table {
        width: 100%;
        height: 18px;
        border: 1px solid #ccc;
        border-radius: 3px;
        overflow: hidden;
    }
    .progress-bar-table td {
        padding: 0;
        height: 18px;
        border: none;
    }
    .progress-fill {
        background: #17a2b8;
        color: #fff;
        font-size: 8px;
        font-weight: bold;
        text-align: center;
        line-height: 18px;
    }
    .progress-fill-green {
        background: #28a745;
    }
    .progress-empty {
        background: #e9ecef;
    }
    .estado-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 4px;
        font-weight: bold;
        font-size: 10px;
        color: #fff;
    }
    .estado-significativo { background: #28a745; }
    .estado-moderado { background: #17a2b8; }
    .estado-estable { background: #ffc107; color: #333; }
    .estado-reinicio { background: #dc3545; }
    .content-text {
        font-size: 9px;
        line-height: 1.5;
        padding: 8px;
        border: 1px solid #eee;
        background: #fafafa;
        margin-bottom: 10px;
    }
    .actividades-table {
        width: 100%;
        margin-bottom: 10px;
    }
    .actividades-table th {
        background: #1c2437;
        color: #fff;
        font-size: 8px;
        padding: 4px 6px;
        text-align: left;
    }
    .actividades-table td {
        font-size: 8px;
        padding: 3px 6px;
        border: 1px solid #ddd;
    }
    .soporte-img {
        max-width: 280px;
        max-height: 200px;
    }
    .page-break {
        page-break-before: always;
    }
    .text-gold { color: #bd9751; }
    .text-green { color: #28a745; }
    .text-red { color: #dc3545; }
    .text-center { text-align: center; }
    .small { font-size: 8px; }
    .footer-text {
        font-size: 7px;
        color: #999;
        text-align: center;
        margin-top: 20px;
    }
</style>
</head>
<body>

<!-- HEADER CORPORATIVO -->
<table class="header-table">
    <tr>
        <td class="header-logo" rowspan="2" style="width: 100px; text-align: center;">
            <?php if (!empty($logoBase64)): ?>
                <img src="<?= $logoBase64 ?>">
            <?php else: ?>
                <span style="font-size:8px; color:#999;">Sin logo</span>
            <?php endif; ?>
        </td>
        <td class="header-title">
            SISTEMA DE GESTION DE SEGURIDAD<br>Y SALUD EN EL TRABAJO SG-SST
        </td>
        <td class="header-code">
            Codigo: FT-SST-205<br>
            Version: 001
        </td>
    </tr>
    <tr>
        <td class="header-title" style="font-size: 11px; color: #1c2437;">
            INFORME DE AVANCES
        </td>
        <td class="header-code">
            Pagina: 1 de 1
        </td>
    </tr>
</table>

<!-- TITULO -->
<div class="titulo-informe">INFORME DE AVANCES</div>
<div class="subtitulo"><?= esc($cliente['nombre_cliente'] ?? '') ?></div>
<div class="periodo-text">
    Periodo: <?= date('d/m/Y', strtotime($informe['fecha_desde'])) ?> - <?= date('d/m/Y', strtotime($informe['fecha_hasta'])) ?>
    &nbsp;|&nbsp; Anio: <?= esc($informe['anio']) ?>
</div>

<!-- INTRO -->
<?php if (!empty($informe['enlace_dashboard'])): ?>
<div style="font-size: 9px; margin-bottom: 12px;">
    Dashboard de seguimiento disponible en: <span class="text-gold"><?= esc($informe['enlace_dashboard']) ?></span>
</div>
<?php endif; ?>

<!-- RESUMEN DE AVANCE -->
<?php if (!empty($informe['resumen_avance'])): ?>
<div class="section-title">RESUMEN DE AVANCE DEL PERIODO</div>
<div class="content-text"><?= nl2br(esc($informe['resumen_avance'])) ?></div>
<?php endif; ?>

<!-- METRICAS PRINCIPALES -->
<div class="section-title">INDICADORES DE CUMPLIMIENTO</div>

<table class="metricas-table">
    <tr>
        <td style="width:25%;">
            <div class="metric-label">PUNTAJE ANTERIOR</div>
            <div class="metric-value"><?= number_format($informe['puntaje_anterior'] ?? 0, 1) ?>%</div>
        </td>
        <td style="width:25%;">
            <div class="metric-label">PUNTAJE ACTUAL</div>
            <div class="metric-value text-gold"><?= number_format($informe['puntaje_actual'] ?? 0, 1) ?>%</div>
        </td>
        <td style="width:25%;">
            <div class="metric-label">DIFERENCIA NETA</div>
            <?php $dif = floatval($informe['diferencia_neta']); ?>
            <div class="metric-value <?= $dif > 0 ? 'text-green' : ($dif < 0 ? 'text-red' : '') ?>">
                <?= $dif > 0 ? '+' : '' ?><?= number_format($dif, 1) ?>
            </div>
        </td>
        <td style="width:25%;">
            <div class="metric-label">ESTADO DE AVANCE</div>
            <?php
                $ea = $informe['estado_avance'];
                $eaClass = match(true) {
                    str_contains($ea, 'SIGNIFICATIVO') => 'estado-significativo',
                    str_contains($ea, 'MODERADO')      => 'estado-moderado',
                    str_contains($ea, 'ESTABLE')       => 'estado-estable',
                    default                            => 'estado-reinicio',
                };
            ?>
            <span class="estado-badge <?= $eaClass ?>"><?= esc($ea) ?></span>
        </td>
    </tr>
</table>

<!-- BARRAS DE INDICADORES -->
<table class="info-table">
    <tr>
        <td class="label-cell" style="width:35%;">Indicador Plan de Trabajo Anual</td>
        <td style="width:65%;">
            <?php $pt = floatval($informe['indicador_plan_trabajo'] ?? 0); ?>
            <table class="progress-bar-table">
                <tr>
                    <td class="progress-fill" style="width: <?= max($pt, 1) ?>%;"><?= number_format($pt, 1) ?>%</td>
                    <?php if ($pt < 100): ?>
                    <td class="progress-empty" style="width: <?= 100 - $pt ?>%;"></td>
                    <?php endif; ?>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td class="label-cell">Indicador Programa de Capacitacion</td>
        <td>
            <?php $cap = floatval($informe['indicador_capacitacion'] ?? 0); ?>
            <table class="progress-bar-table">
                <tr>
                    <td class="progress-fill progress-fill-green" style="width: <?= max($cap, 1) ?>%;"><?= number_format($cap, 1) ?>%</td>
                    <?php if ($cap < 100): ?>
                    <td class="progress-empty" style="width: <?= 100 - $cap ?>%;"></td>
                    <?php endif; ?>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td class="label-cell">Cumplimiento Estandares Minimos</td>
        <td>
            <?php $cumpl = floatval($informe['puntaje_actual'] ?? 0); ?>
            <table class="progress-bar-table">
                <tr>
                    <?php
                        $cumplColor = $cumpl >= 85 ? '#28a745' : ($cumpl >= 60 ? '#ffc107' : '#dc3545');
                    ?>
                    <td class="progress-fill" style="width: <?= max($cumpl, 1) ?>%; background: <?= $cumplColor ?>;"><?= number_format($cumpl, 1) ?>%</td>
                    <?php if ($cumpl < 100): ?>
                    <td class="progress-empty" style="width: <?= 100 - $cumpl ?>%;"></td>
                    <?php endif; ?>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- ACTIVIDADES CERRADAS EN EL PERIODO -->
<?php if (!empty($informe['actividades_cerradas_periodo'])): ?>
<div class="section-title">ACTIVIDADES PTA CERRADAS EN EL PERIODO</div>
<div class="content-text"><?= nl2br(esc($informe['actividades_cerradas_periodo'])) ?></div>
<?php endif; ?>

<!-- ACTIVIDADES ABIERTAS -->
<?php if (!empty($informe['actividades_abiertas'])): ?>
<div class="section-title">ACTIVIDADES Y COMPROMISOS ABIERTOS</div>
<div class="content-text"><?= nl2br(esc($informe['actividades_abiertas'])) ?></div>
<?php endif; ?>

<!-- OBSERVACIONES -->
<?php if (!empty($informe['observaciones'])): ?>
<div class="section-title">OBSERVACIONES</div>
<div class="content-text"><?= nl2br(esc($informe['observaciones'])) ?></div>
<?php endif; ?>

<!-- SOPORTES -->
<?php
    $haySoportes = false;
    for ($i = 1; $i <= 4; $i++) {
        if (!empty($informe["soporte_{$i}_texto"]) || !empty($soportesBase64[$i])) { $haySoportes = true; break; }
    }
?>
<?php if ($haySoportes): ?>
<div class="page-break"></div>
<div class="section-title">SOPORTES</div>

<table class="info-table">
    <?php for ($i = 1; $i <= 4; $i++): ?>
        <?php if (!empty($informe["soporte_{$i}_texto"]) || !empty($soportesBase64[$i])): ?>
        <tr>
            <td class="label-cell">Soporte <?= $i ?></td>
            <td>
                <?php if (!empty($informe["soporte_{$i}_texto"])): ?>
                    <strong><?= esc($informe["soporte_{$i}_texto"]) ?></strong><br>
                <?php endif; ?>
                <?php if (!empty($soportesBase64[$i])): ?>
                    <img src="<?= $soportesBase64[$i] ?>" class="soporte-img">
                <?php endif; ?>
            </td>
        </tr>
        <?php endif; ?>
    <?php endfor; ?>
</table>
<?php endif; ?>

<!-- FOOTER -->
<div class="footer-text">
    Documento generado automaticamente por el SG-SST | <?= date('d/m/Y H:i') ?>
    <?php if (!empty($consultor['nombre_consultor'])): ?>
    | Consultor: <?= esc($consultor['nombre_consultor']) ?>
    <?php endif; ?>
</div>

</body>
</html>
