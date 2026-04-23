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
.escenario { margin: 6px 0 10px 0; }
.escenario h3 { margin: 0 0 2px 0; font-size: 11px; color: #c0392b; border-bottom: 0.5px solid #c0392b; padding-bottom: 1px; }
.escenario p { margin: 1px 0 3px 0; font-size: 9.5px; line-height: 1.35; text-align: justify; }
.escenario .label-hacer    { color: #1b7e3f; font-weight: 700; }
.escenario .label-no-hacer { color: #c0392b; font-weight: 700; }
.escenario .label-quien    { color: #3b5bbd; font-weight: 700; }
.escenario .label-recursos { color: #8a6d30; font-weight: 700; }
.escenario .label-cuando   { color: #555;    font-weight: 700; }
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
    <h3>2.<?= $i + 1 ?>. <?= esc($esc['escenario_nombre']) ?></h3>

    <?php if (!empty($esc['cuando'])): ?>
    <p><span class="label-cuando">CUANDO:</span> <?= nl2br(esc($esc['cuando'])) ?></p>
    <?php endif; ?>

    <?php if (!empty($esc['que_hacer'])): ?>
    <p><span class="label-hacer">QUE HACER:</span> <?= nl2br(esc($esc['que_hacer'])) ?></p>
    <?php endif; ?>

    <?php if (!empty($esc['que_no_hacer'])): ?>
    <p><span class="label-no-hacer">QUE NO HACER:</span> <?= nl2br(esc($esc['que_no_hacer'])) ?></p>
    <?php endif; ?>

    <?php if (!empty($esc['quien'])): ?>
    <p><span class="label-quien">QUIEN RESPONDE:</span> <?= nl2br(esc($esc['quien'])) ?></p>
    <?php endif; ?>

    <?php if (!empty($esc['recursos'])): ?>
    <p><span class="label-recursos">RECURSOS:</span> <?= nl2br(esc($esc['recursos'])) ?></p>
    <?php endif; ?>

    <?php if (!empty($esc['observaciones'])): ?>
    <p style="color:#555;"><em><strong>Observaciones:</strong> <?= nl2br(esc($esc['observaciones'])) ?></em></p>
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
