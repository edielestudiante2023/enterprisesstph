<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
@page { margin: 30px 30px 40px 30px; }
body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #222; }
h1 { font-size: 20px; margin: 0 0 6px 0; color: #1c2437; }
h2 { font-size: 14px; margin: 12px 0 6px 0; color: #1c2437; border-bottom: 2px solid #c0392b; padding-bottom: 3px; }
h3 { font-size: 12px; margin: 8px 0 4px 0; color: #1c2437; }
.header { border-bottom: 3px solid #c0392b; padding-bottom: 8px; margin-bottom: 12px; }
.header-table { width: 100%; }
.header-table td { vertical-align: middle; }
.logo { max-height: 60px; max-width: 160px; }
.title-box { text-align: right; }
table { width: 100%; border-collapse: collapse; margin: 4px 0; }
.kv { width: 100%; }
.kv td { padding: 3px 5px; font-size: 11px; vertical-align: top; }
.kv td.k { font-weight: 600; width: 32%; color: #555; background: #f6f6f6; }
.escenario { border: 1.5px solid #c0392b; padding: 10px; margin: 10px 0; border-radius: 4px; page-break-inside: avoid; }
.escenario h2 { margin-top: 0; border-bottom-color: #c0392b; }
.block { margin: 6px 0; padding: 6px; border-left: 3px solid #c0392b; background: #fffbfb; }
.block-title { font-size: 11px; font-weight: 700; color: #c0392b; text-transform: uppercase; margin-bottom: 2px; }
.block-content { font-size: 11px; line-height: 1.4; }
.footer { font-size: 8px; color: #666; margin-top: 10px; text-align: center; }
.warning-box { background: #fff8e1; border: 1px solid #d4a70e; padding: 8px; border-radius: 4px; margin: 10px 0; font-size: 10px; }
</style>
</head>
<body>

<div class="header">
    <table class="header-table">
        <tr>
            <td style="width: 180px;"><?php if (!empty($logoBase64)): ?><img class="logo" src="<?= $logoBase64 ?>"><?php endif; ?></td>
            <td class="title-box">
                <h1>PROCEDIMIENTO DE REACCION EN EMERGENCIA</h1>
                <div style="font-size: 13px; font-weight: 600;"><?= esc($areasLabels[$procedimiento['area']] ?? '') ?></div>
                <div style="font-size: 11px; color: #444;"><?= esc($cliente['nombre_cliente'] ?? '') ?></div>
                <div style="font-size: 9px; color: #777;">Fecha: <?= date('d/m/Y', strtotime($procedimiento['fecha_elaboracion'])) ?></div>
            </td>
        </tr>
    </table>
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
    <div class="block"><div class="block-title">CUANDO aplicar este procedimiento</div><div class="block-content"><?= nl2br(esc($esc['cuando'])) ?></div></div>
    <?php endif; ?>

    <?php if (!empty($esc['que_hacer'])): ?>
    <div class="block" style="border-left-color:#1b7e3f; background:#f1faf4;"><div class="block-title" style="color:#1b7e3f;">QUE HACER</div><div class="block-content"><?= nl2br(esc($esc['que_hacer'])) ?></div></div>
    <?php endif; ?>

    <?php if (!empty($esc['que_no_hacer'])): ?>
    <div class="block"><div class="block-title">QUE NO HACER</div><div class="block-content"><?= nl2br(esc($esc['que_no_hacer'])) ?></div></div>
    <?php endif; ?>

    <?php if (!empty($esc['quien'])): ?>
    <div class="block" style="border-left-color:#3b5bbd; background:#f5f7ff;"><div class="block-title" style="color:#3b5bbd;">QUIEN responde</div><div class="block-content"><?= nl2br(esc($esc['quien'])) ?></div></div>
    <?php endif; ?>

    <?php if (!empty($esc['recursos'])): ?>
    <div class="block" style="border-left-color:#bd9751; background:#fffaf0;"><div class="block-title" style="color:#8a6d30;">RECURSOS requeridos</div><div class="block-content"><?= nl2br(esc($esc['recursos'])) ?></div></div>
    <?php endif; ?>

    <?php if (!empty($esc['observaciones'])): ?>
    <div style="font-size:10px; color:#555; margin-top:4px;"><em><strong>Observaciones:</strong> <?= nl2br(esc($esc['observaciones'])) ?></em></div>
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
