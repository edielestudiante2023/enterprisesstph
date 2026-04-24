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
</style>
</head>
<body>

<!-- HEADER CORPORATIVO (FT-SST-250) -->
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
        <td class="header-code">Codigo: FT-SST-250<br>Version: 001</td>
    </tr>
    <tr>
        <td class="header-sist" style="font-size:10px;">INSPECCION DE GIMNASIO (Riesgos locativos)</td>
        <td class="header-code">Fecha: <?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td>
    </tr>
</table>

<div class="main-title">INFORME DE INSPECCION DE GIMNASIO<br><?= esc($cliente['nombre_cliente'] ?? '') ?></div>

<h2>1. Introduccion</h2>
<div class="just" style="font-size: 9.5px;">
    <p style="margin: 0;">
        El presente informe corresponde a la inspeccion de riesgos locativos del gimnasio
        del cliente <strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong>, realizada el
        <strong><?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></strong> por el consultor SST
        <strong><?= esc($consultor['nombre_consultor'] ?? '—') ?></strong>. El gimnasio es un bien comun de uso
        colectivo de la copropiedad (Ley 675 de 2001), por lo cual requiere la verificacion periodica de
        condiciones de seguridad y salubridad que permitan a los residentes hacer uso de la instalacion
        sin exposicion a riesgos prevenibles (caidas, atrapamientos, golpes, quemaduras electricas,
        desorientacion en evacuacion, deshidratacion, entre otros).
    </p>
</div>

<h2>2. Alcance</h2>
<div class="just" style="font-size: 9.5px;">
    <p style="margin: 0 0 3px 0;">
        Esta inspeccion comprende la verificacion de <strong>condiciones locativas y de infraestructura</strong>
        del recinto del gimnasio, con enfoque en:
    </p>
    <ul style="margin: 0 0 3px 18px; padding: 0;">
        <li>Senalizacion de aforo maximo y reglamento de uso visible.</li>
        <li>Piso antideslizante / amortiguado y ausencia de obstaculos.</li>
        <li>Ventilacion e iluminacion adecuadas al tipo de actividad.</li>
        <li>Dotacion de emergencia: extintor ABC vigente, botiquin de primeros auxilios, plano de evacuacion, salida libre, pulsador/intercom.</li>
        <li>Instalacion segura de espejos (anclajes, bordes).</li>
        <li>Punto de hidratacion y orden en vestier/casilleros.</li>
        <li>Evidencia fotografica de hallazgos y condiciones generales.</li>
    </ul>
    <p style="margin: 0;">
        <strong>Lo que NO evalua este informe:</strong> estado mecanico ni mantenimiento de los equipos de
        ejercicio (cardiovasculares, peso libre, maquinas guiadas), evaluacion biomecanica del espacio,
        ni la dotacion de EPP ni el SG-SST del instructor (cuando exista). Esos aspectos se gestionan en
        informes independientes.
    </p>
</div>

<h2>3. Justificacion</h2>
<div class="just" style="font-size: 9.5px;">
    <p style="margin: 0;">
        El articulo 25 de la Constitucion Politica reconoce el derecho al trabajo en condiciones dignas
        y justas, y el articulo 49 el derecho a la salud. La Ley 675 de 2001 define los bienes comunes
        de las copropiedades y la obligacion del reglamento interno de regular su uso. La Resolucion 2400
        de 1979 establece disposiciones sobre higiene y seguridad en establecimientos donde se desarrolla
        actividad fisica (iluminacion art. 79, ventilacion art. 63, pisos art. 205, agua potable art. 44).
        El Decreto 1072 de 2015 obliga al SG-SST para el personal del servicio cuando exista. La NTC 1700
        y el Life Safety Code NFPA 101 regulan los medios de evacuacion. Ante la ausencia de una norma
        colombiana especifica para gimnasios de copropiedad, el consultor SST aplica por analogia y
        criterio profesional estos referentes, identificando oportunidades de mejora y hallazgos que
        puedan afectar la seguridad de los usuarios.
    </p>
    <p style="margin: 3px 0 0 0;">
        <strong>Disclaimer:</strong> este informe identifica hallazgos SST de caracter locativo. No reemplaza
        concepto tecnico de mantenimiento de equipos, evaluacion biomecanica del espacio, ni concepto
        de autoridad sanitaria. La administracion de la copropiedad es responsable de implementar las
        recomendaciones en los plazos razonables que determine el consultor SST.
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
        <td class="k">Aforo maximo</td><td><?= esc($inspeccion['aforo_maximo'] ?? '—') ?></td>
        <td class="k">Horario operacion</td><td><?= esc($inspeccion['horario_operacion'] ?? '—') ?></td>
    </tr>
</table>

<h2>5. Checklist de riesgos locativos</h2>
<table class="data">
    <tr><th style="width:60px;">Codigo</th><th>Descripcion</th><th style="width:60px;">Estado</th><th>Fundamento</th></tr>
    <?php foreach ($checks as $col => $info):
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

<?php if (!empty($evidenciasBase64)): ?>
<h2>6. Evidencias fotograficas</h2>
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
<?php endif; ?>

<?php if (!empty($inspeccion['observaciones_generales'])): ?>
<h2>7. Observaciones generales</h2>
<div class="just" style="font-size: 9.5px;"><?= nl2br(esc($inspeccion['observaciones_generales'])) ?></div>
<?php endif; ?>

<?php if (!empty($inspeccion['recomendaciones_generales'])): ?>
<h2>8. Recomendaciones</h2>
<div class="just" style="font-size: 9.5px;"><?= nl2br(esc($inspeccion['recomendaciones_generales'])) ?></div>
<?php endif; ?>

<h2>9. Marco normativo</h2>
<h3>9.1 Jerarquia normativa</h3>
<table class="data">
    <tr><th style="width:25%;">Nivel</th><th>Norma</th><th>Aplicacion al gimnasio</th></tr>
    <tr><td>Constitucional</td><td>Art. 25, 49, 79 CN 1991</td><td>Trabajo en condiciones dignas, derecho a la salud, ambiente sano.</td></tr>
    <tr><td>Ley</td><td>Ley 9 de 1979 (Codigo Sanitario)</td><td>Saneamiento basico en edificaciones de uso colectivo.</td></tr>
    <tr><td>Ley</td><td>Ley 675 de 2001 (Propiedad Horizontal)</td><td>Bienes comunes, reglamento de uso del gimnasio.</td></tr>
    <tr><td>Decreto</td><td>Decreto 1072 de 2015 (SG-SST)</td><td>Si hay instructor/operario, aplica el sistema de gestion.</td></tr>
    <tr><td>Resolucion</td><td>Resolucion 2400 de 1979</td><td>Arts. 44 (agua potable), 63 (ventilacion), 79 (iluminacion), 205 (pisos).</td></tr>
    <tr><td>NTC</td><td>NTC 1700</td><td>Medios de evacuacion en edificaciones.</td></tr>
    <tr><td>NFPA</td><td>NFPA 101 Life Safety Code</td><td>Salidas de emergencia libres, rutas de evacuacion.</td></tr>
    <tr><td>Criterio SST</td><td>Buenas practicas del consultor</td><td>Espejos anclados, pulsador de emergencia, punto de hidratacion.</td></tr>
</table>

<h3>9.2 Articulos especificos evaluados</h3>
<table class="data">
    <tr><th style="width:60px;">Codigo</th><th>Item</th><th>Fundamento normativo</th></tr>
    <?php foreach ($checks as $col => $info): ?>
    <tr>
        <td><strong><?= $info['codigo'] ?></strong></td>
        <td><?= esc($info['label']) ?></td>
        <td style="font-size:8.5px;"><?= esc($info['fundamento']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<div class="footer">
    Informe generado el <?= date('d/m/Y H:i') ?> — Consultor: <?= esc($consultor['nombre_consultor'] ?? '—') ?> — FT-SST-250 v001
</div>

</body>
</html>
