<?php
$recintoLabels = ['TURCO' => 'Baño turco', 'SAUNA' => 'Sauna', 'JACUZZI' => 'Jacuzzi'];
$recintoFlags  = ['TURCO' => 'aplica_turco', 'SAUNA' => 'aplica_sauna', 'JACUZZI' => 'aplica_jacuzzi'];
$recintosActivos = [];
foreach (['TURCO','SAUNA','JACUZZI'] as $rc) {
    if (!empty($inspeccion[$recintoFlags[$rc]])) $recintosActivos[] = $rc;
}
$listaRecintos = implode(' + ', array_map(fn($r) => $recintoLabels[$r], $recintosActivos));
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
@page { margin: 28px 28px 36px 28px; }
body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #222; }
h1 { font-size: 16px; margin: 0 0 4px 0; color: #1c2437; }
h2 { font-size: 12px; margin: 8px 0 4px 0; color: #1c2437; border-bottom: 1px solid #bd9751; padding-bottom: 2px; }
h3 { font-size: 10.5px; margin: 4px 0 2px 0; color: #1c2437; }
.header-corp { width: 100%; border-collapse: collapse; border: 1.5px solid #333; margin-bottom: 8px; }
.header-corp td { border: 1px solid #333; padding: 4px 6px; vertical-align: middle; }
.header-logo { width: 100px; text-align: center; font-size: 8px; }
.header-logo img { max-width: 85px; max-height: 50px; }
.header-sist { text-align: center; font-weight: bold; font-size: 9px; }
.header-code { width: 130px; font-size: 8px; }
.main-title { text-align: center; font-size: 13px; font-weight: bold; margin: 6px 0 10px; color: #1c2437; }
table { width: 100%; border-collapse: collapse; margin: 4px 0; }
table.data th, table.data td { border: 1px solid #aaa; padding: 3px 5px; font-size: 9.5px; text-align: left; }
table.data th { background: #eee; font-weight: 700; }
.kv td { padding: 2px 5px; font-size: 9.5px; border: 1px solid #ccc; vertical-align: top; }
.kv td.k { font-weight: 600; background: #f6f6f6; width: 30%; }
.badge-si { background: #1b7e3f; color: #fff; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: 700; }
.badge-no { background: #c0392b; color: #fff; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: 700; }
.badge-na { background: #888; color: #fff; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: 700; }
.footer { font-size: 8px; color: #666; margin-top: 10px; text-align: center; }
.pagebreak { page-break-after: always; }
.just { text-align: justify; line-height: 1.4; }
.recinto-card { page-break-inside: avoid; margin-top: 6px; }
</style>
</head>
<body>

<!-- HEADER CORPORATIVO (FT-SST-249) -->
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
        <td class="header-code">Codigo: FT-SST-249<br>Version: 001</td>
    </tr>
    <tr>
        <td class="header-sist" style="font-size:10px;">INSPECCION TURCO / SAUNA / JACUZZI</td>
        <td class="header-code">Fecha: <?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td>
    </tr>
</table>

<div class="main-title">
    INFORME DE INSPECCION DE <?= esc(strtoupper($listaRecintos)) ?><br>
    <?= esc($cliente['nombre_cliente'] ?? '') ?>
</div>

<h2>1. Introduccion</h2>
<div class="just" style="font-size: 9.5px;">
    <p style="margin: 0;">
        El presente informe corresponde a la inspeccion de riesgos en las areas
        de <strong><?= esc($listaRecintos) ?></strong> del cliente
        <strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong>, realizada el
        <strong><?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></strong> por el consultor SST
        <strong><?= esc($consultor['nombre_consultor'] ?? '—') ?></strong>. Estas areas son bienes comunes de uso
        colectivo de la copropiedad (Ley 675 de 2001) donde los residentes se exponen a condiciones
        termicas extremas, humedad, inmersion y riesgos electricos, por lo cual requieren verificacion
        periodica de controles de seguridad (aforo, temperatura, ventilacion, evacuacion, emergencia).
    </p>
</div>

<h2>2. Alcance</h2>
<div class="just" style="font-size: 9.5px;">
    <p style="margin: 0 0 3px 0;">
        Esta inspeccion comprende la verificacion de <strong>condiciones locativas y de control
        operativo</strong> en los recintos seleccionados:
    </p>
    <ul style="margin: 0 0 3px 18px; padding: 0;">
        <li>Reglamento de uso visible, aforo maximo señalizado y cronometro de exposicion.</li>
        <li>Timbre/pulsador de emergencia, punto de hidratacion, control de temperatura protegido.</li>
        <li>Piso antideslizante, iluminacion protegida para humedad, ventilacion.</li>
        <li>Puertas con apertura hacia afuera y visual exterior (turco/sauna).</li>
        <li>Desagüe funcional, generador de vapor o hornillo aislado segun el recinto.</li>
        <li>Aislamiento electrico, GFCI/RCD (jacuzzi), agarraderas, cobertura fuera de uso.</li>
        <li>Evidencia fotografica y observaciones por recinto.</li>
    </ul>
    <p style="margin: 0;">
        <strong>Lo que NO evalua este informe:</strong> calidad microbiologica del agua (aplicaria a
        piscinas bajo Res 234/2026 Minsalud — ver modulo piscinas si se requiere), mantenimiento
        mecanico de equipos, ni capacitacion del operador.
    </p>
</div>

<h2>3. Justificacion</h2>
<div class="just" style="font-size: 9.5px;">
    <p style="margin: 0;">
        El articulo 49 de la Constitucion Politica reconoce el derecho a la salud y el articulo 79 el
        derecho a un ambiente sano. La Ley 675 de 2001 regula los bienes comunes de las copropiedades
        y el reglamento interno de convivencia. La Resolucion 2400 de 1979 establece disposiciones
        sobre higiene y seguridad (iluminacion art. 79, ventilacion art. 63, pisos art. 205). El
        Decreto 1072 de 2015 obliga al SG-SST del personal que opere el area. El RETIE (Reglamento
        Tecnico de Instalaciones Electricas) y NFPA 70 exigen protecciones diferenciales en zonas
        humedas (GFCI/RCD). NFPA 72 regula sistemas de alarma contra incendios. Ante la ausencia
        de una norma colombiana especifica para baño turco, sauna o jacuzzi de uso residencial, el
        consultor SST aplica por analogia y criterio profesional estos referentes y las guias tecnicas
        de los fabricantes.
    </p>
    <p style="margin: 3px 0 0 0;">
        <strong>Disclaimer:</strong> este informe identifica hallazgos SST. No reemplaza el concepto
        de la autoridad sanitaria ni la certificacion tecnica del operador/empresa de mantenimiento.
        La administracion de la copropiedad es responsable de implementar las recomendaciones.
    </p>
</div>

<h2>4. Datos generales</h2>
<table class="kv">
    <tr>
        <td class="k">Cliente</td><td><?= esc($cliente['nombre_cliente'] ?? '') ?></td>
        <td class="k">Consultor</td><td><?= esc($consultor['nombre_consultor'] ?? '') ?></td>
    </tr>
    <tr>
        <td class="k">Direccion</td><td colspan="3"><?= esc($cliente['direccion'] ?? '') ?></td>
    </tr>
    <tr>
        <td class="k">Recintos presentes</td><td colspan="3"><?= esc($listaRecintos) ?></td>
    </tr>
    <tr>
        <td class="k">Horario operacion</td><td colspan="3"><?= esc($inspeccion['horario_operacion'] ?? '—') ?></td>
    </tr>
</table>

<h2>5. Checklist comun a los recintos</h2>
<table class="data">
    <tr><th style="width:60px;">Codigo</th><th>Descripcion</th><th style="width:60px;">Estado</th><th>Fundamento</th></tr>
    <?php foreach ($checksMaestro as $col => $info):
        $val = $inspeccion[$col] ?? 'NA';
        $cls = $val === 'SI' ? 'badge-si' : ($val === 'NO' ? 'badge-no' : 'badge-na');
    ?>
    <tr>
        <td><strong><?= $info['codigo'] ?></strong></td>
        <td><?= esc($info['label']) ?></td>
        <td style="text-align:center;"><span class="<?= $cls ?>"><?= $val ?></span></td>
        <td style="font-size:8.5px;"><?= esc($info['fundamento']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<!-- Secciones por recinto -->
<?php $secNum = 6; foreach ($recintosActivos as $rc):
    $det = $detalleMapa[$rc] ?? null;
    $aforoField = 'aforo_maximo_' . strtolower($rc);
?>
<div class="recinto-card">
    <h2><?= $secNum ?>. <?= esc($recintoLabels[$rc]) ?></h2>
    <table class="kv">
        <tr>
            <td class="k">Aforo maximo</td><td><?= esc($inspeccion[$aforoField] ?? '—') ?></td>
            <td class="k">Material interno</td><td><?= esc($det['material_interno'] ?? '—') ?></td>
        </tr>
        <tr>
            <td class="k">Fuente de calor</td><td><?= esc($det['fuente_calor'] ?? '—') ?></td>
            <td class="k">Temperatura operacion</td><td><?= esc($det['temperatura_operacion'] ?? '—') ?></td>
        </tr>
        <?php if ($rc === 'JACUZZI'): ?>
        <tr>
            <td class="k">Profundidad (m)</td><td><?= esc($det['profundidad_m'] ?? '—') ?></td>
            <td class="k">Temp. agua (°C)</td><td><?= esc($det['temperatura_agua_c'] ?? '—') ?></td>
        </tr>
        <?php endif; ?>
    </table>

    <?php if ($det): ?>
    <table class="data" style="margin-top:4px;">
        <tr><th style="width:60px;">Codigo</th><th>Descripcion</th><th style="width:60px;">Estado</th></tr>
        <?php foreach ($checksDetalle as $col => $info):
            if (!in_array($rc, $info['aplica'], true)) continue;
            $val = $det[$col] ?? 'NA';
            $cls = $val === 'SI' ? 'badge-si' : ($val === 'NO' ? 'badge-no' : 'badge-na');
        ?>
        <tr>
            <td><strong><?= $info['codigo'] ?></strong></td>
            <td><?= esc($info['label']) ?></td>
            <td style="text-align:center;"><span class="<?= $cls ?>"><?= $val ?></span></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php if (!empty($det['observaciones'])): ?>
    <p style="margin-top:3px; font-size:9px;"><strong>Observaciones:</strong> <?= nl2br(esc($det['observaciones'])) ?></p>
    <?php endif; ?>
    <?php endif; ?>
</div>
<?php $secNum++; endforeach; ?>

<?php if (!empty($evidenciasBase64)): ?>
<h2><?= $secNum ?>. Evidencias fotograficas</h2>
<table style="width:100%; margin-top:4px;"><tr>
<?php $col = 0; foreach ($evidenciasBase64 as $slot => $ev):
    if ($col === 3) { echo '</tr><tr>'; $col = 0; }
?>
    <td style="width:33.33%; padding:4px; border:none; vertical-align:top; text-align:center;">
        <div style="font-size:8.5px; font-weight:bold; color:#1c2437; margin-bottom:2px;">
            Slot <?= $slot ?><?= !empty($ev['categoria']) ? ' — ' . esc($ev['categoria']) : '' ?>
        </div>
        <img src="<?= $ev['base64'] ?>" style="max-width:155px; max-height:115px; border:1px solid #bbb;">
        <?php if (!empty($ev['descripcion'])): ?>
        <div style="font-size:8px; color:#666; margin-top:2px;"><?= esc($ev['descripcion']) ?></div>
        <?php endif; ?>
    </td>
<?php $col++; endforeach;
while ($col > 0 && $col < 3) { echo '<td></td>'; $col++; }
?>
</tr></table>
<?php $secNum++; endif; ?>

<?php if (!empty($inspeccion['observaciones_generales'])): ?>
<h2><?= $secNum ?>. Observaciones generales</h2>
<div class="just" style="font-size: 9.5px;"><?= nl2br(esc($inspeccion['observaciones_generales'])) ?></div>
<?php $secNum++; endif; ?>

<?php if (!empty($inspeccion['recomendaciones_generales'])): ?>
<h2><?= $secNum ?>. Recomendaciones</h2>
<div class="just" style="font-size: 9.5px;"><?= nl2br(esc($inspeccion['recomendaciones_generales'])) ?></div>
<?php $secNum++; endif; ?>

<h2><?= $secNum ?>. Marco normativo</h2>
<h3><?= $secNum ?>.1 Jerarquia normativa</h3>
<table class="data">
    <tr><th style="width:25%;">Nivel</th><th>Norma</th><th>Aplicacion</th></tr>
    <tr><td>Constitucional</td><td>Arts. 49, 79 CN 1991</td><td>Derecho a la salud y ambiente sano.</td></tr>
    <tr><td>Ley</td><td>Ley 9 de 1979 (Codigo Sanitario)</td><td>Saneamiento en edificaciones de uso colectivo.</td></tr>
    <tr><td>Ley</td><td>Ley 675 de 2001 (Propiedad Horizontal)</td><td>Bienes comunes, reglamento interno.</td></tr>
    <tr><td>Ley</td><td>Ley 1523 de 2012</td><td>Sistema Nacional de Gestion del Riesgo.</td></tr>
    <tr><td>Decreto</td><td>Decreto 1072 de 2015 (SG-SST)</td><td>Sistema de Gestion SST del personal del area.</td></tr>
    <tr><td>Resolucion</td><td>Resolucion 2400 de 1979</td><td>Arts. 63 (ventilacion), 79 (iluminacion), 205 (pisos).</td></tr>
    <tr><td>NTC</td><td>NTC 2505</td><td>Instalaciones para suministro de gas (generador vapor turco).</td></tr>
    <tr><td>NFPA</td><td>NFPA 70, 72, 101</td><td>Instalaciones electricas, alarmas, medios de evacuacion.</td></tr>
    <tr><td>RETIE</td><td>Reglamento Tecnico Instalaciones Electricas</td><td>Proteccion electrica en zonas humedas (GFCI/RCD).</td></tr>
    <tr><td>Criterio SST</td><td>Buenas practicas del consultor</td><td>Cronometro, control temperatura, aforo, cobertura jacuzzi.</td></tr>
</table>

<h3><?= $secNum ?>.2 Articulos especificos evaluados (checklist comun)</h3>
<table class="data">
    <tr><th style="width:60px;">Codigo</th><th>Item</th><th>Fundamento</th></tr>
    <?php foreach ($checksMaestro as $col => $info): ?>
    <tr>
        <td><strong><?= $info['codigo'] ?></strong></td>
        <td><?= esc($info['label']) ?></td>
        <td style="font-size:8.5px;"><?= esc($info['fundamento']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<h3><?= $secNum ?>.3 Checklist por recinto</h3>
<table class="data">
    <tr><th style="width:60px;">Codigo</th><th>Item</th><th>Aplica a</th><th>Fundamento</th></tr>
    <?php foreach ($checksDetalle as $col => $info): ?>
    <tr>
        <td><strong><?= $info['codigo'] ?></strong></td>
        <td><?= esc($info['label']) ?></td>
        <td style="font-size:8.5px;"><?= esc(implode(' / ', $info['aplica'])) ?></td>
        <td style="font-size:8.5px;"><?= esc($info['fundamento']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<div class="footer">
    Informe generado el <?= date('d/m/Y H:i') ?> — Consultor: <?= esc($consultor['nombre_consultor'] ?? '—') ?> — FT-SST-249 v001
</div>

</body>
</html>
