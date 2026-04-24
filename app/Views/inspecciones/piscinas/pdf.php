<?php
/**
 * Calcula dias entre fecha_toma del ensayo y hoy. Retorna null si fecha vacia.
 */
function diasDesdeEnsayo(?string $fecha): ?int {
    if (empty($fecha)) return null;
    try {
        $f = new DateTime($fecha);
        $h = new DateTime('today');
        return (int)$h->diff($f)->days * ($f > $h ? -1 : 1);
    } catch (\Throwable $e) { return null; }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
@page { margin: 28px 28px 36px 28px; }
body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #222; }
h1 { font-size: 16px; margin: 0 0 4px 0; color: #1c2437; }
h2 { font-size: 12px; margin: 6px 0 3px 0; color: #1c2437; border-bottom: 1px solid #bd9751; padding-bottom: 2px; page-break-after: avoid; }
h3 { font-size: 10.5px; margin: 4px 0 2px 0; color: #1c2437; page-break-after: avoid; }
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
.badge { display: inline-block; padding: 3px 10px; border-radius: 10px; color: #fff; font-weight: 700; font-size: 11px; }
.kv { width: 100%; margin: 2px 0; page-break-inside: avoid; }
.kv td { padding: 1px 3px; font-size: 9.5px; vertical-align: top; border: none; }
.kv td.k { font-weight: 600; width: 32%; color: #555; }
.piscina { margin: 6px 0 10px 0; }
.botiquin-box { page-break-inside: avoid; }
.piscina h2 { margin-top: 0; border-bottom: 1.5px solid #bd9751; padding-bottom: 2px; }
.badge-vigente { background:#1b7e3f; color:#fff; padding:2px 8px; border-radius:10px; font-size:9px; font-weight:700; }
.badge-vencido { background:#c0392b; color:#fff; padding:2px 8px; border-radius:10px; font-size:9px; font-weight:700; }
.badge-na { background:#888; color:#fff; padding:2px 8px; border-radius:10px; font-size:9px; font-weight:700; }
.param-tbl td { font-size: 9px; }
.param-ok { color: #1b7e3f; font-weight: 700; }
.param-no { color: #c0392b; font-weight: 700; }
.param-na { color: #888; }
.footer { font-size: 8px; color: #666; margin-top: 10px; text-align: center; }
.pagebreak { page-break-after: always; }
.foto { max-width: 180px; max-height: 140px; border: 1px solid #ccc; }
</style>
</head>
<body>

<!-- HEADER CORPORATIVO (FT-SST-246) -->
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
        <td class="header-code">Codigo: FT-SST-246<br>Version: 001</td>
    </tr>
    <tr>
        <td class="header-sist" style="font-size:10px;">FORMATO DE INSPECCION DE PISCINAS (Res 234/2026 Minsalud)</td>
        <td class="header-code">Fecha: <?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td>
    </tr>
</table>

<div class="main-title">INFORME DE INSPECCION DE PISCINAS<br><?= esc($cliente['nombre_cliente'] ?? '') ?></div>

<!-- INTRODUCCION / ALCANCE (compacto, alcance SST real) -->
<h2>1. Introduccion, alcance y justificacion</h2>
<div style="font-size: 9.5px; text-align: justify; line-height: 1.35;">
    <p style="margin: 0 0 3px 0;">Informe de inspeccion SST a las piscinas del cliente <strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong> realizada el <strong><?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></strong>.</p>
    <p style="margin: 0 0 3px 0;"><strong>Alcance:</strong> el consultor SST verifica condiciones de infraestructura y seguridad (Ley 1209/2008), avisos reglamentarios, dotacion de emergencia (DEA, botiquines A/B/C por m², flotadores, baston), estado de higiene y la <strong>existencia de los documentos que exige el operador</strong>: libro/planilla diaria de operaciones (Art. 16 Res 234) y ensayo microbiologico trimestral vigente (Art. 6 Res 234).</p>
    <p style="margin: 0 0 3px 0;"><strong>Lo que NO evalua este informe:</strong> interpretacion de parametros fisicoquimicos in situ, calculo de IRAPI, interpretacion de UFC microbiologicos. Estos son <strong>responsabilidad del laboratorio acreditado</strong> y se evidencian en el certificado trimestral. El consultor solo verifica que exista vigente.</p>
    <p style="margin: 0;"><strong>Disclaimer:</strong> este informe identifica hallazgos SST; <u>no reemplaza</u> el concepto sanitario de la Secretaria de Salud (Art. 10), la certificacion del operador por entidad acreditada (Art. 11 num 7) ni los ensayos de laboratorio trimestrales (Art. 6).</p>
</div>

<h2>2. Datos generales del establecimiento</h2>
<table class="kv">
    <tr><td class="k">Cliente</td><td><?= esc($cliente['nombre_cliente'] ?? '') ?></td><td class="k">Consultor</td><td><?= esc($consultor['nombre_consultor'] ?? '') ?></td></tr>
    <tr><td class="k">Direccion</td><td colspan="3"><?= esc($cliente['direccion'] ?? '') ?></td></tr>
    <tr><td class="k">Superficie total (m²)</td><td><?= esc($inspeccion['superficie_total_establecimiento_m2'] ?? '—') ?></td><td class="k">Total piscinas</td><td><?= (int)$inspeccion['total_piscinas'] ?></td></tr>
    <tr><td class="k">Empresa mantenimiento</td><td><?= esc($inspeccion['empresa_mantenimiento'] ?? '') ?></td><td class="k">NIT</td><td><?= esc($inspeccion['nit_empresa_mantenimiento'] ?? '') ?></td></tr>
</table>

<h2>3. Documentacion y gestion sanitaria</h2>
<table class="data">
    <tr><th>Item</th><th>Estado</th><th>Observaciones</th></tr>
    <tr><td>Concepto sanitario Secretaria de Salud</td><td><?= esc(ucfirst($inspeccion['concepto_sanitario'] ?? 'no_emitido')) ?> <?= !empty($inspeccion['concepto_sanitario_fecha']) ? '(' . date('d/m/Y', strtotime($inspeccion['concepto_sanitario_fecha'])) . ')' : '' ?></td><td><?= esc($inspeccion['concepto_sanitario_observaciones'] ?? '') ?></td></tr>
    <tr><td>DEA (Desfibrilador) presente</td><td><?= esc($inspeccion['dea_presente'] ?? 'NA') ?></td><td>Personal capacitado: <?= (int)($inspeccion['dea_personal_capacitado_cantidad'] ?? 0) ?></td></tr>
    <tr><td>Operador de piscinas certificado</td><td><?= !empty($inspeccion['operador_certificado_nombre']) ? esc($inspeccion['operador_certificado_nombre']) : 'No registrado' ?></td><td><?= esc($inspeccion['operador_certificado_entidad'] ?? '') ?> <?= !empty($inspeccion['operador_certificado_vigencia']) ? '(vence ' . date('d/m/Y', strtotime($inspeccion['operador_certificado_vigencia'])) . ')' : '' ?></td></tr>
    <tr><td>Documentacion Art. 15 (8 procedimientos)</td><td><?= esc($inspeccion['documentacion_art15_completa'] ?? 'NA') ?></td><td><?= esc($inspeccion['documentacion_art15_observaciones'] ?? '') ?></td></tr>
    <tr><td>Plan de Saneamiento Basico Art. 17 (5 programas)</td><td><?= esc($inspeccion['plan_saneamiento_completo'] ?? 'NA') ?></td><td><?= esc($inspeccion['plan_saneamiento_observaciones'] ?? '') ?></td></tr>
    <tr><td>Manejo seguro de quimicos (fichas, SDS, EPP, GHS)</td><td><?= esc($inspeccion['manejo_quimicos_conforme'] ?? 'NA') ?></td><td>Decreto 1072/2015 + Decreto 1496/2018</td></tr>
    <tr><td>Area de almacenamiento de residuos</td><td><?= esc($inspeccion['area_residuos_conforme'] ?? 'NA') ?></td><td>Art. 14 Res 234/2026</td></tr>
    <tr><td>Contenedores codificados por color</td><td><?= esc($inspeccion['contenedores_codificados_color'] ?? 'NA') ?></td><td>Biologico rojo, ordinario verde, reciclable blanco</td></tr>
    <tr><td>Tablero publico con resultados mensuales</td><td><?= esc($inspeccion['tablero_publico_resultados'] ?? 'NA') ?></td><td>Art. 16 par. 2 Res 234/2026</td></tr>
</table>

<?php
// Aplanar todas las fotos del maestro en una sola lista con su label
$flatEvidencias = [];
foreach ($camposEvidenciaMaestro as $codigo => $label) {
    $fotos = $evidenciasMaestroB64[$codigo] ?? [];
    foreach ($fotos as $f) {
        if (empty($f['foto_b64'])) continue;
        $flatEvidencias[] = array_merge($f, ['label' => $label]);
    }
}
if (!empty($flatEvidencias)): ?>
<h2>3.1 Evidencias fotograficas — documentacion y gestion sanitaria</h2>
<table style="width:100%;margin-top:4px;"><tr>
<?php $col = 0; foreach ($flatEvidencias as $f):
    if ($col === 3) { echo '</tr><tr>'; $col = 0; }
?>
    <td style="width:33.33%;padding:4px;border:none;vertical-align:top;text-align:center;">
        <div style="font-size:8.5px;font-weight:bold;color:#1c2437;margin-bottom:2px;line-height:1.1;"><?= esc($f['label']) ?></div>
        <img src="<?= $f['foto_b64'] ?>" style="max-width:155px;max-height:115px;border:1px solid #bbb;">
        <?php if (!empty($f['descripcion'])): ?>
        <div style="font-size:8px;color:#666;margin-top:2px;"><?= esc($f['descripcion']) ?></div>
        <?php endif; ?>
    </td>
<?php $col++; endforeach;
while ($col > 0 && $col < 3) { echo '<td></td>'; $col++; }
?>
</tr></table>
<?php endif; ?>

<!-- Seccion 4: Documentos del operador (nivel maestro) -->
<h2>4. Documentos del operador (Art. 6 + Art. 16 Res 234/2026)</h2>
<?php
$ensayoMicro = null;
foreach (($ensayos ?? []) as $ens) {
    if (($ens['tipo'] ?? '') === 'MICROBIOLOGICO') { $ensayoMicro = $ens; break; }
}
$diasMicro = $ensayoMicro ? diasDesdeEnsayo($ensayoMicro['fecha_toma'] ?? '') : null;
$estadoEnsayo = '';
$badgeClass = 'badge-na';
if ($diasMicro !== null && $diasMicro >= 0) {
    if ($diasMicro <= 90) { $estadoEnsayo = 'Vigente (' . $diasMicro . ' dias)'; $badgeClass = 'badge-vigente'; }
    else                   { $estadoEnsayo = 'VENCIDO (' . $diasMicro . ' dias > 90)'; $badgeClass = 'badge-vencido'; }
}
?>
<table class="data">
    <tr><th>Documento</th><th>Estado</th><th>Observaciones</th></tr>
    <tr>
        <td><strong>Planilla diaria de operaciones</strong><br><span style="font-size:8px;color:#666;">Art. 16 Res 234/2026</span></td>
        <td>Ver evidencias fotograficas (categoria "planilla_diaria")</td>
        <td style="font-size:8.5px;">El operador lleva registro diario con pH, cloros, transparencia, Langelier. El consultor SST solo verifica existencia y actualizacion.</td>
    </tr>
    <tr>
        <td><strong>Ensayo microbiologico trimestral</strong><br><span style="font-size:8px;color:#666;">Art. 6 Res 234/2026</span></td>
        <td>
            <?php if ($ensayoMicro): ?>
                Fecha: <?= !empty($ensayoMicro['fecha_toma']) ? date('d/m/Y', strtotime($ensayoMicro['fecha_toma'])) : '—' ?><br>
                Laboratorio: <?= esc($ensayoMicro['laboratorio'] ?? '—') ?><br>
                Laboratorio acreditado: <?= esc($ensayoMicro['laboratorio_acreditado'] ?? 'NA') ?><br>
                Informe reporta cumplimiento: <?= esc($ensayoMicro['reporta_cumplimiento'] ?? 'NA') ?><br>
                <span class="<?= $badgeClass ?>"><?= $estadoEnsayo ?: 'Sin fecha' ?></span>
            <?php else: ?>
                <span class="badge-na">No registra</span>
            <?php endif; ?>
        </td>
        <td style="font-size:8.5px;"><?= esc($ensayoMicro['observaciones'] ?? '') ?></td>
    </tr>
</table>

<?php foreach ($piscinas as $i => $p): $idDet = $p['id']; ?>
<div class="piscina">
    <h2>Piscina #<?= $i + 1 ?> — <?= esc($p['identificador']) ?></h2>

    <table class="kv">
        <tr><td class="k">Tipo</td><td><?= esc($p['tipo']) ?></td><td class="k">Uso</td><td><?= esc($p['uso']) ?></td></tr>
        <tr><td class="k">Climatizada</td><td><?= esc($p['climatizada']) ?></td><td class="k">Superficie (m²)</td><td><?= esc($p['superficie_piscina_m2'] ?? '—') ?></td></tr>
        <tr><td class="k">Profundidad max (m)</td><td><?= esc($p['profundidad_max_m'] ?? '—') ?></td><td class="k">Profundidad min (m)</td><td><?= esc($p['profundidad_min_m'] ?? '—') ?></td></tr>
        <tr><td class="k">Aforo piscina</td><td><?= esc($p['aforo_piscina_max'] ?? '—') ?></td><td class="k">Aforo deck</td><td><?= esc($p['aforo_deck_max'] ?? '—') ?></td></tr>
    </table>

    <h3>Infraestructura, emergencia y avisos</h3>
    <table class="data param-tbl">
        <tr><th>Elemento</th><th>Estado</th><th>Elemento</th><th>Estado</th></tr>
        <?php
        $items = [
            'cerramiento_perimetral' => 'Cerramiento perimetral',
            'puerta_control_acceso'  => 'Puerta control acceso',
            'alarma_inmersion_80db'  => 'Alarma inmersion 80dB',
            'boton_parada_emergencia'=> 'Boton parada emergencia',
            'drenaje_antiatrapamiento' => 'Drenaje antiatrapamiento',
            'minimo_dos_drenajes'    => 'Minimo dos drenajes',
            'sistema_liberacion_vacio' => 'Sistema liberacion vacio',
            'senalizacion_profundidad' => 'Senalizacion profundidad',
            'baldosas_cambio_profundidad' => 'Baldosas cambio prof.',
            'escaleras_acceso_antideslizantes' => 'Escaleras antideslizantes',
            'baranda_escaleras' => 'Baranda escaleras',
            'iluminacion_adecuada' => 'Iluminacion adecuada',
            'ventilacion_adecuada' => 'Ventilacion adecuada',
            'aviso_menores_12' => 'Aviso menores 12',
            'aviso_reglamento' => 'Aviso reglamento',
            'aviso_horario' => 'Aviso horario',
            'aviso_ducharse_antes' => 'Aviso ducharse antes',
            'aviso_prohibido_zapatos' => 'Aviso prohibido zapatos',
            'aviso_telefonos_emergencia' => 'Aviso telefonos emergencia',
            'aviso_aforo_visible' => 'Aviso aforo',
            'camilla_rescate' => 'Camilla rescate',
            'flotadores_circulares_min_2' => 'Flotadores circulares',
            'baston_con_gancho' => 'Baston con gancho',
            'citofono_24h' => 'Citofono 24h',
            'duchas_previas_obligatorias' => 'Duchas previas',
            'baranda_apoyo_duchas' => 'Baranda apoyo duchas',
            'lavapies_funcional' => 'Lavapies funcional',
            'dosificacion_independiente' => 'Dosif. independiente',
            'sistema_seguridad_flujo' => 'Seguridad de flujo',
            'no_dosificacion_manual_con_publico' => 'No dosif. manual c/publico',
            'equipo_bombeo_operativo' => 'Equipo bombeo',
            'filtros_operativos' => 'Filtros operativos',
            'libro_registro_existe' => 'Libro registro',
        ];
        $keys = array_keys($items);
        for ($k = 0; $k < count($keys); $k += 2):
            $k1 = $keys[$k]; $k2 = $keys[$k+1] ?? null;
            $c1 = $p[$k1] ?? 'NA';
            $c1cls = ['SI'=>'param-ok','NO'=>'param-no','NA'=>'param-na'][$c1] ?? 'param-na';
        ?>
        <tr>
            <td><?= $items[$k1] ?></td><td class="<?= $c1cls ?>"><?= esc($c1) ?></td>
            <?php if ($k2): $c2 = $p[$k2] ?? 'NA'; $c2cls = ['SI'=>'param-ok','NO'=>'param-no','NA'=>'param-na'][$c2] ?? 'param-na'; ?>
            <td><?= $items[$k2] ?></td><td class="<?= $c2cls ?>"><?= esc($c2) ?></td>
            <?php else: ?><td></td><td></td><?php endif; ?>
        </tr>
        <?php endfor; ?>
        <tr>
            <td>Botiquin tipo</td><td><?= esc($p['botiquin_tipo'] ?? 'NINGUNO') ?></td>
            <td>Cubiculos M / H</td><td><?= (int)$p['cubiculos_duchas_mujeres'] ?> / <?= (int)$p['cubiculos_duchas_hombres'] ?></td>
        </tr>
    </table>

    <div class="botiquin-box">
    <h3>Botiquin (Anexo III — Tipo <?= esc($p['botiquin_tipo'] ?? 'NINGUNO') ?>)</h3>
    <table style="width:100%;margin:4px 0;">
        <tr>
            <td style="width:40%;vertical-align:top;text-align:center;border:none;padding:4px;">
                <?php if (!empty($p['foto_botiquin_base64'])): ?>
                <img src="<?= $p['foto_botiquin_base64'] ?>" style="max-width:180px;max-height:140px;border:1px solid #bbb;">
                <div style="font-size:8px;color:#666;margin-top:2px;">Foto del botiquin</div>
                <?php else: ?>
                <div style="font-size:9px;color:#888;border:1px dashed #ccc;padding:14px;">Sin foto del botiquin</div>
                <?php endif; ?>
            </td>
            <td style="vertical-align:top;border:none;padding:4px;">
                <div style="font-size:9.5px;"><strong>Observaciones / items faltantes:</strong></div>
                <?php if (!empty($p['botiquin_observaciones_faltantes'])): ?>
                <div style="font-size:9px;margin-top:3px;"><?= nl2br(esc($p['botiquin_observaciones_faltantes'])) ?></div>
                <?php else: ?>
                <div style="font-size:9px;color:#888;margin-top:3px;">Sin observaciones registradas.</div>
                <?php endif; ?>
            </td>
        </tr>
    </table>
    </div>

    <?php if (!empty($p['observaciones']) || !empty($p['foto_base64'])): ?>
    <h3>Observaciones y evidencia</h3>
    <?php if (!empty($p['observaciones'])): ?>
    <div style="margin: 4px 0; font-size: 9.5px;"><?= nl2br(esc($p['observaciones'])) ?></div>
    <?php endif; ?>
    <?php if (!empty($p['foto_base64'])): ?>
    <img class="foto" src="<?= $p['foto_base64'] ?>">
    <?php endif; ?>
    <?php endif; ?>

    <?php $detEvids = $evidenciasDetB64[$idDet] ?? []; if (!empty($detEvids)): ?>
    <h3>Evidencias adicionales (<?= count($detEvids) ?>)</h3>
    <table style="width:100%;margin-bottom:6px;"><tr>
    <?php $col = 0; foreach ($detEvids as $ev):
        if (empty($ev['foto_b64'])) continue;
        if ($col === 3) { echo '</tr><tr>'; $col = 0; }
    ?>
        <?php
            $cat  = !empty($ev['categoria'])   ? $ev['categoria']   : 'Evidencia';
            $desc = $ev['descripcion'] ?? '';
        ?>
        <td style="width:33%;padding:3px;border:none;vertical-align:top;text-align:center;">
            <img src="<?= $ev['foto_b64'] ?>" style="max-width:150px;max-height:110px;border:1px solid #bbb;">
            <div style="font-size:8px;color:#444;margin-top:2px;"><strong><?= esc($cat) ?></strong><?= $desc !== '' ? ' — ' . esc($desc) : '' ?></div>
        </td>
    <?php $col++; endforeach;
    while ($col > 0 && $col < 3) { echo '<td></td>'; $col++; }
    ?>
    </tr></table>
    <?php endif; ?>
</div>
<?php endforeach; ?>

<?php if (!empty($inspeccion['recomendaciones_generales'])): ?>
<h2>5. Recomendaciones generales</h2>
<div style="font-size: 10px;"><?= nl2br(esc($inspeccion['recomendaciones_generales'])) ?></div>
<?php endif; ?>

<h2>6. Marco normativo aplicable</h2>

<div style="font-size: 9.5px; text-align: justify; margin-bottom: 6px;">
    La inspeccion se sustenta en el siguiente marco juridico vigente en Colombia.
</div>

<h3 style="font-size: 11px;">6.1 Jerarquia normativa</h3>
<table class="data" style="font-size: 9px;">
    <tr><th style="width: 22%;">Norma</th><th style="width: 30%;">Entidad emisora</th><th>Alcance</th></tr>
    <tr><td><strong>Ley 9 de 1979</strong></td><td>Congreso de la Republica</td><td>Codigo Sanitario Nacional. Arts. 222, 227 y 229 regulan agua de piscinas, personal capacitado en primeros auxilios y equipos de control del agua.</td></tr>
    <tr><td><strong>Ley 1209 de 2008</strong></td><td>Congreso de la Republica</td><td>Normas de seguridad en piscinas de uso colectivo: cerramientos, alarmas de inmersion, drenajes antiatrapamiento, senalizacion, dotacion de emergencia, salvavidas con RCP.</td></tr>
    <tr><td><strong>Decreto 554 de 2015</strong></td><td>Ministerio de Salud</td><td>Reglamenta la Ley 1209 (Art. 5 al 14): estandares tecnicos de elementos de seguridad obligatorios.</td></tr>
    <tr><td><strong>Decreto 780 de 2016</strong></td><td>Ministerio de Salud</td><td>Unico reglamentario del sector salud. Titulo 8 (Arts. 2.8.7.x) regula piscinas y estructuras similares, competencias de autoridades sanitarias, concepto sanitario.</td></tr>
    <tr><td><strong>Resolucion 234 de 2026</strong></td><td>Ministerio de Salud y Proteccion Social</td><td><strong>Norma central de este informe.</strong> Criterios de calidad del agua (Anexo I), IRAPI (Anexo II), contenido del botiquin (Anexo III), buenas practicas sanitarias.</td></tr>
    <tr><td><strong>Decreto 1072 de 2015</strong></td><td>Ministerio del Trabajo</td><td>SG-SST. Aplica al personal operario de la piscina (dosificacion de quimicos, EPP, examenes medicos).</td></tr>
    <tr><td><strong>Decreto 1496 de 2018</strong></td><td>Ministerio del Trabajo</td><td>Sistema Globalmente Armonizado (SGA/GHS) para el etiquetado de productos quimicos utilizados en el tratamiento del agua.</td></tr>
</table>

<h3 style="font-size: 11px; margin-top: 8px;">6.2 Resumen congelado al finalizar</h3>
<div style="font-size: 9px; color: #555; text-align: justify;">
    <?= esc($marcoNormativo) ?>
</div>

<div class="footer">
    Generado automaticamente por el modulo de inspecciones SST — Fecha emision PDF: <?= date('d/m/Y H:i') ?>.
</div>

</body>
</html>
