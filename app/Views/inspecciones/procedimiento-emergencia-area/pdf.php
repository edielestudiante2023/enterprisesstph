<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
@page { margin: 28px 28px 36px 28px; }
body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #222; }
h1 { font-size: 16px; margin: 0 0 4px 0; color: #1c2437; }
h2 { font-size: 12px; margin: 6px 0 3px 0; color: #1c2437; border-bottom: 1px solid #c0392b; padding-bottom: 2px; }
h3 { font-size: 10.5px; margin: 4px 0 2px 0; color: #1c2437; }
.header-corp { width: 100%; border-collapse: collapse; border: 1.5px solid #333; margin-bottom: 8px; }
.header-corp td { border: 1px solid #333; padding: 4px 6px; vertical-align: middle; }
.header-logo { width: 100px; text-align: center; font-size: 8px; }
.header-logo img { max-width: 85px; max-height: 50px; }
.header-sist { text-align: center; font-weight: bold; font-size: 9px; }
.header-code { width: 130px; font-size: 8px; }
.main-title { text-align: center; font-size: 12px; font-weight: bold; margin: 4px 0 6px; color: #c0392b; }
table { width: 100%; border-collapse: collapse; margin: 4px 0; }
.kv { width: 100%; }
.kv td { padding: 2px 4px; font-size: 9.5px; vertical-align: top; }
.kv td.k { font-weight: 600; width: 28%; color: #555; background: #f6f6f6; }
.escenario { border: 1.2px solid #c0392b; padding: 6px 8px; margin: 6px 0; border-radius: 4px; }
.escenario h2 { margin: 0 0 4px 0; font-size: 12px; border-bottom-color: #c0392b; padding-bottom: 2px; }
.escenario table.dualcol { width: 100%; margin: 0; border-collapse: collapse; }
.escenario table.dualcol td { width: 50%; vertical-align: top; padding: 2px; border: none; }
.block { margin: 2px 0; padding: 4px 6px; border-left: 3px solid #c0392b; background: #fffbfb; }
.block-title { font-size: 9.5px; font-weight: 700; color: #c0392b; text-transform: uppercase; margin-bottom: 1px; }
.block-content { font-size: 9.5px; line-height: 1.3; }
.footer { font-size: 8px; color: #666; margin-top: 10px; text-align: center; }
.warning-box { background: #fff8e1; border: 1px solid #d4a70e; padding: 4px 8px; border-radius: 4px; margin: 4px 0; font-size: 9px; }
</style>
</head>
<body>

<!-- HEADER CORPORATIVO (FT-SST-248) -->
<table class="header-corp">
    <tr>
        <td class="header-logo" rowspan="2">
            <?php if (!empty($logoBase64)): ?>
                <img src="<?= $logoBase64 ?>">
            <?php else: ?>
                <strong style="font-size:7px;"><?= esc($cliente['nombre_cliente'] ?? '') ?></strong>
            <?php endif; ?>
        </td>
        <td class="header-sist">SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO</td>
        <td class="header-code">Codigo: FT-SST-248<br>Version: 001</td>
    </tr>
    <tr>
        <td class="header-sist" style="font-size:10px;">PROCEDIMIENTO DE REACCION EN EMERGENCIA POR AREA</td>
        <td class="header-code">Fecha: <?= date('d/m/Y', strtotime($procedimiento['fecha_elaboracion'])) ?></td>
    </tr>
</table>

<div class="main-title">
    PROCEDIMIENTO DE REACCION EN EMERGENCIA<br>
    <?= esc($areasLabels[$procedimiento['area']] ?? '') ?><br>
    <span style="font-size:11px;font-weight:normal;color:#444;"><?= esc($cliente['nombre_cliente'] ?? '') ?></span>
</div>

<div class="warning-box">
    <strong>Alcance:</strong> este documento es un procedimiento operativo de reaccion en emergencia especifico para el area indicada. Complementa — y no reemplaza — el Plan de Emergencia de la copropiedad.
</div>

<h2>1. Datos generales del area</h2>
<table class="kv">
    <tr><td class="k">Cliente</td><td><?= esc($cliente['nombre_cliente'] ?? '') ?></td></tr>
    <tr><td class="k">Direccion</td><td><?= esc($cliente['direccion'] ?? '') ?></td></tr>
    <tr><td class="k">Area</td><td><?= esc($areasLabels[$procedimiento['area']] ?? '') ?> — <?= esc($procedimiento['nombre_area_descriptivo'] ?? '') ?></td></tr>
    <tr><td class="k">Responsable del area</td><td><?= esc($procedimiento['responsable_area_nombre'] ?? '') ?> — <?= esc($procedimiento['responsable_area_cargo'] ?? '') ?> — <?= esc($procedimiento['responsable_area_contacto'] ?? '') ?></td></tr>
    <tr><td class="k">Horario de operacion</td><td><?= esc($procedimiento['horario_operacion'] ?? '') ?></td></tr>
    <tr><td class="k">Aforo maximo</td><td><?= esc($procedimiento['aforo_maximo'] ?? '') ?></td></tr>
    <tr><td class="k">Telefonos de emergencia</td><td><?= nl2br(esc($procedimiento['telefonos_emergencia'] ?? '')) ?></td></tr>
    <tr><td class="k">Recursos disponibles</td><td><?= nl2br(esc($procedimiento['recursos_disponibles'] ?? '')) ?></td></tr>
    <?php if (!empty($procedimiento['observaciones_contexto'])): ?>
    <tr><td class="k">Observaciones</td><td><?= nl2br(esc($procedimiento['observaciones_contexto'])) ?></td></tr>
    <?php endif; ?>
    <tr><td class="k">Consultor SST</td><td><?= esc($consultor['nombre_consultor'] ?? '') ?></td></tr>
</table>

<h2>2. Escenarios de emergencia</h2>

<?php foreach ($escenarios as $i => $esc):
    if ((int)$esc['aprobado_por_consultor'] !== 1) continue;
    if (empty($esc['que_hacer']) && empty($esc['que_no_hacer'])) continue;
?>
<div class="escenario">
    <h2>2.<?= $i + 1 ?>. <?= esc($esc['escenario_nombre']) ?></h2>

    <?php if (!empty($esc['cuando'])): ?>
    <div class="block"><div class="block-title">CUANDO</div><div class="block-content"><?= nl2br(esc($esc['cuando'])) ?></div></div>
    <?php endif; ?>

    <!-- QUE HACER + QUE NO HACER en 2 columnas para compactar -->
    <?php if (!empty($esc['que_hacer']) || !empty($esc['que_no_hacer'])): ?>
    <table class="dualcol"><tr>
        <td>
            <?php if (!empty($esc['que_hacer'])): ?>
            <div class="block" style="border-left-color:#1b7e3f; background:#f1faf4;"><div class="block-title" style="color:#1b7e3f;">QUE HACER</div><div class="block-content"><?= nl2br(esc($esc['que_hacer'])) ?></div></div>
            <?php endif; ?>
        </td>
        <td>
            <?php if (!empty($esc['que_no_hacer'])): ?>
            <div class="block"><div class="block-title">QUE NO HACER</div><div class="block-content"><?= nl2br(esc($esc['que_no_hacer'])) ?></div></div>
            <?php endif; ?>
        </td>
    </tr></table>
    <?php endif; ?>

    <!-- QUIEN + RECURSOS en 2 columnas -->
    <?php if (!empty($esc['quien']) || !empty($esc['recursos'])): ?>
    <table class="dualcol"><tr>
        <td>
            <?php if (!empty($esc['quien'])): ?>
            <div class="block" style="border-left-color:#3b5bbd; background:#f5f7ff;"><div class="block-title" style="color:#3b5bbd;">QUIEN responde</div><div class="block-content"><?= nl2br(esc($esc['quien'])) ?></div></div>
            <?php endif; ?>
        </td>
        <td>
            <?php if (!empty($esc['recursos'])): ?>
            <div class="block" style="border-left-color:#bd9751; background:#fffaf0;"><div class="block-title" style="color:#8a6d30;">RECURSOS</div><div class="block-content"><?= nl2br(esc($esc['recursos'])) ?></div></div>
            <?php endif; ?>
        </td>
    </tr></table>
    <?php endif; ?>

    <?php if (!empty($esc['observaciones'])): ?>
    <div style="font-size:9px; color:#555; margin-top:3px;"><em><strong>Observaciones:</strong> <?= nl2br(esc($esc['observaciones'])) ?></em></div>
    <?php endif; ?>
</div>
<?php endforeach; ?>

<h2>3. Marco normativo</h2>
<div style="font-size: 9.5px; color: #555; text-align: justify;"><?= esc($marcoNormativo) ?></div>

<div class="footer">
    Generado automaticamente por el modulo de procedimientos de emergencia por area — SST — <?= date('d/m/Y H:i') ?>.
</div>

</body>
</html>
