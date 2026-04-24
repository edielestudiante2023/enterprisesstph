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
.just { text-align: justify; line-height: 1.4; }
</style>
</head>
<body>

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
        <td class="header-code">Codigo: FT-SST-251<br>Version: 001</td>
    </tr>
    <tr>
        <td class="header-sist" style="font-size:10px;">INSPECCION ZONA BBQ</td>
        <td class="header-code">Fecha: <?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td>
    </tr>
</table>

<div class="main-title">INFORME DE INSPECCION ZONA BBQ<br><?= esc($cliente['nombre_cliente'] ?? '') ?></div>

<h2>1. Introduccion</h2>
<div class="just" style="font-size: 9.5px;">
    <p style="margin: 0;">
        El presente informe corresponde a la inspeccion de la zona BBQ (zona social
        para asados) del cliente <strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong>, realizada
        el <strong><?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></strong> por el
        consultor SST <strong><?= esc($consultor['nombre_consultor'] ?? '—') ?></strong>. La zona BBQ
        es un bien comun de uso colectivo de la copropiedad (Ley 675 de 2001) donde se manipulan
        fuentes de fuego abierto, gas combustible y superficies a alta temperatura, lo que genera
        riesgos de incendio, quemaduras, intoxicacion por monoxido de carbono y accidentes con
        menores. Por ello requiere verificacion periodica de controles de seguridad.
    </p>
</div>

<h2>2. Alcance</h2>
<div class="just" style="font-size: 9.5px;">
    <p style="margin: 0 0 3px 0;">
        Esta inspeccion comprende la verificacion de condiciones de seguridad en la zona BBQ:
    </p>
    <ul style="margin: 0 0 3px 18px; padding: 0;">
        <li>Reglamento de uso visible, horario, sistema de reserva y supervision de menores.</li>
        <li>Extintor cercano vigente, alarma de humo en zona cubierta adyacente.</li>
        <li>Distancias seguras a vegetacion y fachadas de vivienda.</li>
        <li>Integridad de la instalacion de gas: prueba de fugas, valvula de corte, cilindro ventilado.</li>
        <li>Ventilacion adecuada (no confinada), superficie no combustible bajo el asador.</li>
        <li>Punto de agua, punto electrico con GFCI, recipiente metalico para cenizas.</li>
        <li>Senalizacion de riesgos: quemadura, prohibido menores sin adulto.</li>
        <li>Estado de cada asador (parrilla y conexion de gas) con fecha de ultima prueba de fuga.</li>
        <li>Evidencia fotografica y plan de emergencia documentado.</li>
    </ul>
    <p style="margin: 0;">
        <strong>Lo que NO evalua este informe:</strong> mantenimiento mecanico profundo de equipos,
        dotacion EPP de quien opera, ni inspeccion de la red de gas aguas arriba del regulador (eso
        corresponde a la empresa distribuidora de gas autorizada).
    </p>
</div>

<h2>3. Justificacion</h2>
<div class="just" style="font-size: 9.5px;">
    <p style="margin: 0;">
        La Constitucion Politica reconoce el derecho a un ambiente sano (art. 79) y a la salud
        (art. 49). La Ley 675 de 2001 regula los bienes comunes de las copropiedades y su uso. La
        Ley 1523 de 2012 establece el Sistema Nacional de Gestion del Riesgo de Desastres. El
        Decreto 1072 de 2015 obliga al SG-SST del personal que opere la zona. La NTC 2505 regula
        las instalaciones para suministro de gas domiciliario y la NFPA 58 el codigo de Gas Licuado
        de Petroleo (GLP). NFPA 72 regula las alarmas contra incendios. El Reglamento Tecnico del
        Sector GLP del Ministerio de Minas y Energia y el RETIE complementan las exigencias
        electricas en zonas humedas. Ante la ausencia de una norma colombiana especifica para
        zonas BBQ de copropiedad, el consultor SST aplica por analogia y criterio profesional
        estos referentes.
    </p>
    <p style="margin: 3px 0 0 0;">
        <strong>Disclaimer:</strong> este informe identifica hallazgos SST. No reemplaza el concepto
        tecnico de la empresa distribuidora de gas, ni el de la autoridad sanitaria. La administracion
        es responsable de implementar las recomendaciones en los plazos razonables.
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
        <td class="k">Combustible</td><td><?= esc($combustibles[$inspeccion['tipo_combustible']] ?? '') ?></td>
        <td class="k">N° asadores</td><td><?= (int) ($inspeccion['numero_asadores'] ?? 0) ?></td>
    </tr>
    <tr>
        <td class="k">Aforo maximo</td><td><?= esc($inspeccion['aforo_maximo'] ?? '—') ?></td>
        <td class="k">Horario operacion</td><td><?= esc($inspeccion['horario_operacion'] ?? '—') ?></td>
    </tr>
    <tr>
        <td class="k">Distancia vegetacion</td><td><?= esc($inspeccion['distancia_vegetacion_m'] ?? '—') ?> m</td>
        <td class="k">Distancia vivienda</td><td><?= esc($inspeccion['distancia_vivienda_m'] ?? '—') ?> m</td>
    </tr>
    <tr>
        <td class="k">Tipo extintor</td><td colspan="3"><?= esc($inspeccion['tipo_extintor'] ?? '—') ?></td>
    </tr>
</table>

<h2>5. Checklist de riesgos</h2>
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

<?php if (!empty($asadores)): ?>
<h2>6. Asadores individuales</h2>
<table class="data">
    <tr><th style="width:50px;">N°</th><th>Parrilla</th><th>Conexion gas</th><th>Ult. prueba fuga</th><th>Observaciones</th></tr>
    <?php foreach ($asadores as $a): ?>
    <tr>
        <td><strong><?= esc($a['numero']) ?></strong></td>
        <td><?= esc($a['estado_parrilla']) ?></td>
        <td><?= esc(str_replace('_',' ', $a['estado_conexion_gas'])) ?></td>
        <td><?= !empty($a['fecha_ultima_prueba_fuga']) ? date('d/m/Y', strtotime($a['fecha_ultima_prueba_fuga'])) : '—' ?></td>
        <td><?= esc($a['observaciones'] ?? '—') ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<?php if (!empty($evidenciasBase64)): ?>
<h2><?= !empty($asadores) ? '7' : '6' ?>. Evidencias fotograficas</h2>
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
<h2>Observaciones generales</h2>
<div class="just" style="font-size: 9.5px;"><?= nl2br(esc($inspeccion['observaciones_generales'])) ?></div>
<?php endif; ?>

<?php if (!empty($inspeccion['recomendaciones_generales'])): ?>
<h2>Recomendaciones</h2>
<div class="just" style="font-size: 9.5px;"><?= nl2br(esc($inspeccion['recomendaciones_generales'])) ?></div>
<?php endif; ?>

<h2>Marco normativo</h2>
<table class="data">
    <tr><th style="width:30%;">Norma</th><th>Aplicacion</th></tr>
    <tr><td>Ley 675 de 2001</td><td>Propiedad horizontal, bienes comunes, reglamento interno.</td></tr>
    <tr><td>Ley 1523 de 2012</td><td>Sistema Nacional de Gestion del Riesgo.</td></tr>
    <tr><td>Decreto 1072 de 2015</td><td>SG-SST del personal operario.</td></tr>
    <tr><td>Res 2400 de 1979</td><td>Higiene y seguridad (ventilacion, pisos).</td></tr>
    <tr><td>NTC 2505</td><td>Instalaciones para suministro de gas domiciliario.</td></tr>
    <tr><td>NFPA 58</td><td>Liquefied Petroleum Gas Code.</td></tr>
    <tr><td>NFPA 72</td><td>Alarmas contra incendios en zonas adyacentes.</td></tr>
    <tr><td>Reg. Tec. Sector GLP MinEnergia</td><td>Requisitos tecnicos para GLP.</td></tr>
    <tr><td>RETIE / NFPA 70</td><td>Proteccion electrica (GFCI) en zonas humedas.</td></tr>
    <tr><td>Criterio SST</td><td>Distancias seguras, recipiente cenizas, señalizacion.</td></tr>
</table>

<h3>Articulos especificos evaluados</h3>
<table class="data">
    <tr><th style="width:60px;">Codigo</th><th>Item</th><th>Fundamento</th></tr>
    <?php foreach ($checks as $col => $info): ?>
    <tr>
        <td><strong><?= $info['codigo'] ?></strong></td>
        <td><?= esc($info['label']) ?></td>
        <td style="font-size:8.5px;"><?= esc($info['fundamento']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<div class="footer">
    Informe generado el <?= date('d/m/Y H:i') ?> — Consultor: <?= esc($consultor['nombre_consultor'] ?? '—') ?> — FT-SST-251 v001
</div>

</body>
</html>
