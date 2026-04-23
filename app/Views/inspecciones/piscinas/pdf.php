<?php
// Clasificación IRAPI con color
function irapiColor($c) {
    return match ($c) {
        'SIN_RIESGO' => '#1b7e3f',
        'BAJO'       => '#d4a70e',
        'MEDIO'      => '#e67815',
        'ALTO'       => '#c0392b',
        default      => '#888',
    };
}
function irapiLabel($c) {
    return match ($c) {
        'SIN_RIESGO' => 'Sin riesgo / Optimo',
        'BAJO'       => 'Bajo',
        'MEDIO'      => 'Medio',
        'ALTO'       => 'ALTO',
        default      => '—',
    };
}

// Peor IRAPI como hallazgo de cabecera
$peorClas = 'SIN_RIESGO';
$peorVal = 0;
$ord = ['SIN_RIESGO'=>0, 'BAJO'=>1, 'MEDIO'=>2, 'ALTO'=>3];
foreach ($piscinas as $p) {
    $c = $p['irapi_clasificacion'] ?? 'SIN_RIESGO';
    if (($ord[$c] ?? 0) > ($ord[$peorClas] ?? 0)) {
        $peorClas = $c;
        $peorVal = $p['irapi_valor'] ?? 0;
    } elseif ($c === $peorClas && ($p['irapi_valor'] ?? 0) > $peorVal) {
        $peorVal = $p['irapi_valor'];
    }
}
$peorColor = irapiColor($peorClas);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
@page { margin: 30px 30px 40px 30px; }
body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #222; }
h1 { font-size: 18px; margin: 0 0 6px 0; color: #1c2437; }
h2 { font-size: 14px; margin: 12px 0 6px 0; color: #1c2437; border-bottom: 2px solid #bd9751; padding-bottom: 3px; }
h3 { font-size: 12px; margin: 8px 0 4px 0; color: #1c2437; }
.header { border-bottom: 3px solid #bd9751; padding-bottom: 8px; margin-bottom: 12px; }
.header-table { width: 100%; }
.header-table td { vertical-align: middle; }
.logo { max-height: 60px; max-width: 160px; }
.title-box { text-align: right; }
table { width: 100%; border-collapse: collapse; margin: 4px 0; }
table.data th, table.data td { border: 1px solid #aaa; padding: 3px 5px; font-size: 9.5px; text-align: left; }
table.data th { background: #eee; font-weight: 700; }
.badge { display: inline-block; padding: 3px 10px; border-radius: 10px; color: #fff; font-weight: 700; font-size: 11px; }
.kv { width: 100%; margin: 4px 0; }
.kv td { padding: 2px 4px; font-size: 10px; vertical-align: top; border: none; }
.kv td.k { font-weight: 600; width: 32%; color: #555; }
.hallazgo-critico { background: <?= $peorColor ?>; color: #fff; padding: 14px; border-radius: 6px; margin: 12px 0; }
.hallazgo-critico h2 { color: #fff; border: none; margin: 0 0 4px 0; }
.piscina { border: 1.5px solid #bd9751; padding: 8px; margin: 10px 0; border-radius: 4px; page-break-inside: avoid; }
.piscina h2 { margin-top: 0; }
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

<div class="header">
    <table class="header-table">
        <tr>
            <td style="width: 180px;"><?php if (!empty($logoBase64)): ?><img class="logo" src="<?= $logoBase64 ?>"><?php endif; ?></td>
            <td class="title-box">
                <h1>INSPECCION DE PISCINAS</h1>
                <div style="font-size: 10px;"><?= esc($cliente['nombre_cliente'] ?? '') ?></div>
                <div style="font-size: 9px; color: #777;">Fecha: <?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></div>
            </td>
        </tr>
    </table>
</div>

<!-- Hallazgo principal: peor IRAPI -->
<div class="hallazgo-critico">
    <h2>Clasificacion IRAPI del establecimiento (peor caso)</h2>
    <div style="font-size: 22px; font-weight: 700;"><?= number_format((float)$peorVal, 2) ?> — <?= irapiLabel($peorClas) ?></div>
    <div style="font-size: 9px; margin-top: 4px;">Indice de Riesgo del Agua segun Anexo II de la Resolucion 234/2026 Minsalud.</div>
</div>

<h2>1. Datos generales del establecimiento</h2>
<table class="kv">
    <tr><td class="k">Cliente</td><td><?= esc($cliente['nombre_cliente'] ?? '') ?></td><td class="k">Consultor</td><td><?= esc($consultor['nombre_consultor'] ?? '') ?></td></tr>
    <tr><td class="k">Direccion</td><td colspan="3"><?= esc($cliente['direccion'] ?? '') ?></td></tr>
    <tr><td class="k">Superficie total (m²)</td><td><?= esc($inspeccion['superficie_total_establecimiento_m2'] ?? '—') ?></td><td class="k">Total piscinas</td><td><?= (int)$inspeccion['total_piscinas'] ?></td></tr>
    <tr><td class="k">Empresa mantenimiento</td><td><?= esc($inspeccion['empresa_mantenimiento'] ?? '') ?></td><td class="k">NIT</td><td><?= esc($inspeccion['nit_empresa_mantenimiento'] ?? '') ?></td></tr>
</table>

<h2>2. Documentacion y gestion sanitaria</h2>
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

<?php if (!empty($evidenciasMaestroB64)): ?>
<h2>2.1 Evidencias fotograficas — documentacion y gestion sanitaria</h2>
<?php foreach ($camposEvidenciaMaestro as $codigo => $label): ?>
    <?php $fotos = $evidenciasMaestroB64[$codigo] ?? []; if (empty($fotos)) continue; ?>
    <h3><?= esc($label) ?> <span style="font-size:9px;color:#777;font-weight:normal;">(<?= count($fotos) ?> foto<?= count($fotos) > 1 ? 's' : '' ?>)</span></h3>
    <table style="width:100%;margin-bottom:6px;"><tr>
    <?php $col = 0; foreach ($fotos as $f):
        if (empty($f['foto_b64'])) continue;
        if ($col === 3) { echo '</tr><tr>'; $col = 0; }
    ?>
        <td style="width:33%;padding:3px;border:none;vertical-align:top;text-align:center;">
            <img src="<?= $f['foto_b64'] ?>" style="max-width:170px;max-height:120px;border:1px solid #bbb;">
            <?php if (!empty($f['descripcion'])): ?>
            <div style="font-size:8px;color:#666;margin-top:2px;"><?= esc($f['descripcion']) ?></div>
            <?php endif; ?>
        </td>
    <?php $col++; endforeach;
    while ($col > 0 && $col < 3) { echo '<td></td>'; $col++; }
    ?>
    </tr></table>
<?php endforeach; ?>
<?php endif; ?>

<div class="pagebreak"></div>

<h2>3. Piscinas inspeccionadas</h2>

<?php foreach ($piscinas as $i => $p): $idDet = $p['id']; ?>
<div class="piscina">
    <h2>Piscina #<?= $i + 1 ?> — <?= esc($p['identificador']) ?></h2>
    <div style="margin-bottom: 6px;">
        <span class="badge" style="background: <?= irapiColor($p['irapi_clasificacion'] ?? 'SIN_RIESGO') ?>;">
            IRAPI: <?= number_format((float)($p['irapi_valor'] ?? 0), 2) ?> — <?= irapiLabel($p['irapi_clasificacion'] ?? 'SIN_RIESGO') ?>
        </span>
        <?php if (!empty($p['isl_valor'])): ?>
        <span class="badge" style="background: #555; margin-left: 4px;">
            ISL Langelier: <?= number_format((float)$p['isl_valor'], 2) ?> (<?= esc($p['isl_interpretacion']) ?>)
        </span>
        <?php endif; ?>
    </div>

    <table class="kv">
        <tr><td class="k">Tipo</td><td><?= esc($p['tipo']) ?></td><td class="k">Uso</td><td><?= esc($p['uso']) ?></td></tr>
        <tr><td class="k">Climatizada</td><td><?= esc($p['climatizada']) ?></td><td class="k">Superficie (m²)</td><td><?= esc($p['superficie_piscina_m2'] ?? '—') ?></td></tr>
        <tr><td class="k">Profundidad max (m)</td><td><?= esc($p['profundidad_max_m'] ?? '—') ?></td><td class="k">Profundidad min (m)</td><td><?= esc($p['profundidad_min_m'] ?? '—') ?></td></tr>
        <tr><td class="k">Aforo piscina</td><td><?= esc($p['aforo_piscina_max'] ?? '—') ?></td><td class="k">Aforo deck</td><td><?= esc($p['aforo_deck_max'] ?? '—') ?></td></tr>
    </table>

    <h3>3.<?= $i+1 ?>.1 Parametros in situ (Anexo I)</h3>
    <?php $params = $parametrosMap[$idDet] ?? []; if (empty($params)): ?>
    <div style="font-size: 9px; color: #888;">Sin mediciones registradas.</div>
    <?php else: ?>
    <table class="data param-tbl">
        <tr><th>Parametro</th><th>Valor</th><th>Unidad</th><th>Rango</th><th>Conforme</th><th>Obs.</th></tr>
        <?php foreach ($params as $prm):
            $label = $parametrosCfg[$prm['parametro']]['label'] ?? $prm['parametro'];
            $class = ['SI'=>'param-ok','NO'=>'param-no','NA'=>'param-na'][$prm['conforme']] ?? 'param-na';
        ?>
        <tr>
            <td><?= esc($label) ?></td>
            <td><?= esc($prm['valor'] ?? '') ?></td>
            <td><?= esc($prm['unidad'] ?? '') ?></td>
            <td style="font-size:8px;"><?= esc($prm['rango_referencia'] ?? '') ?></td>
            <td class="<?= $class ?>"><?= esc($prm['conforme']) ?></td>
            <td style="font-size:8px;"><?= esc($prm['observaciones'] ?? '') ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>

    <h3>3.<?= $i+1 ?>.2 Ensayos de laboratorio</h3>
    <?php $ensayos = $ensayosMap[$idDet] ?? []; if (empty($ensayos)): ?>
    <div style="font-size: 9px; color: #888;">Sin ensayos registrados.</div>
    <?php else: ?>
    <table class="data param-tbl">
        <tr><th>Tipo</th><th>Fecha toma</th><th>Laboratorio</th><th>N° informe</th><th>Norma</th><th>Conforme</th></tr>
        <?php foreach ($ensayos as $e): ?>
        <tr>
            <td><?= esc($e['tipo']) ?></td>
            <td><?= !empty($e['fecha_toma']) ? date('d/m/Y', strtotime($e['fecha_toma'])) : '—' ?></td>
            <td><?= esc($e['laboratorio'] ?? '') ?></td>
            <td><?= esc($e['numero_informe'] ?? '') ?></td>
            <td style="font-size:8px;"><?= esc($e['norma_citada'] ?? '') ?></td>
            <td><?= esc($e['conforme_global']) ?></td>
        </tr>
        <?php if ($e['tipo'] === 'MICROBIOLOGICO'): ?>
        <tr><td colspan="6" style="font-size:8px;">Heterotrofos: <?= esc($e['heterotrofos_ufc'] ?? '—') ?> · Coliformes: <?= esc($e['coliformes_termotolerantes_ufc'] ?? '—') ?> · E.coli: <?= esc($e['ecoli_ufc'] ?? '—') ?> · Pseudomonas: <?= esc($e['pseudomonas_ufc'] ?? '—') ?> · Legionella: <?= esc($e['legionella_ufc'] ?? '—') ?></td></tr>
        <?php endif; ?>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>

    <h3>3.<?= $i+1 ?>.3 Infraestructura, emergencia y avisos</h3>
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

    <?php $botItems = $botiquinMap[$idDet] ?? []; if (!empty($botItems)): ?>
    <h3>3.<?= $i+1 ?>.4 Checklist botiquin tipo <?= esc($p['botiquin_tipo']) ?> (Anexo III)</h3>
    <table class="data param-tbl">
        <tr><th>Item</th><th>Exigida</th><th>Observada</th><th>Estado</th></tr>
        <?php foreach ($botItems as $itm):
            $cls = ['SI'=>'param-ok','NO'=>'param-no','PARCIAL'=>'param-no','NA'=>'param-na'][$itm['presente']] ?? 'param-na';
        ?>
        <tr>
            <td><?= esc($itm['item_nombre']) ?> <span style="font-size:8px;color:#888;">(<?= esc($itm['unidad_medida']) ?>)</span></td>
            <td><?= (int)$itm['cantidad_exigida'] ?></td>
            <td><?= esc($itm['cantidad_observada'] ?? '—') ?></td>
            <td class="<?= $cls ?>"><?= esc($itm['presente']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>

    <?php if (!empty($p['observaciones']) || !empty($p['foto_base64'])): ?>
    <h3>3.<?= $i+1 ?>.5 Observaciones y evidencia</h3>
    <?php if (!empty($p['observaciones'])): ?>
    <div style="margin: 4px 0; font-size: 9.5px;"><?= nl2br(esc($p['observaciones'])) ?></div>
    <?php endif; ?>
    <?php if (!empty($p['foto_base64'])): ?>
    <img class="foto" src="<?= $p['foto_base64'] ?>">
    <?php endif; ?>
    <?php endif; ?>

    <?php $detEvids = $evidenciasDetB64[$idDet] ?? []; if (!empty($detEvids)): ?>
    <h3>3.<?= $i+1 ?>.6 Evidencias adicionales (<?= count($detEvids) ?>)</h3>
    <table style="width:100%;margin-bottom:6px;"><tr>
    <?php $col = 0; foreach ($detEvids as $ev):
        if (empty($ev['foto_b64'])) continue;
        if ($col === 3) { echo '</tr><tr>'; $col = 0; }
    ?>
        <td style="width:33%;padding:3px;border:none;vertical-align:top;text-align:center;">
            <img src="<?= $ev['foto_b64'] ?>" style="max-width:150px;max-height:110px;border:1px solid #bbb;">
            <div style="font-size:8px;color:#444;margin-top:2px;"><strong><?= esc($ev['categoria'] ?? 'OTRA') ?></strong><?= !empty($ev['descripcion']) ? ' — ' . esc($ev['descripcion']) : '' ?></div>
        </td>
    <?php $col++; endforeach;
    while ($col > 0 && $col < 3) { echo '<td></td>'; $col++; }
    ?>
    </tr></table>
    <?php endif; ?>
</div>
<?php endforeach; ?>

<?php if (!empty($inspeccion['recomendaciones_generales'])): ?>
<h2>4. Recomendaciones generales</h2>
<div style="font-size: 10px;"><?= nl2br(esc($inspeccion['recomendaciones_generales'])) ?></div>
<?php endif; ?>

<h2>5. Marco normativo</h2>
<div style="font-size: 9px; color: #555; text-align: justify;">
    <?= esc($marcoNormativo) ?>
</div>

<div class="footer">
    Generado automaticamente por el modulo de inspecciones SST — Fecha emision PDF: <?= date('d/m/Y H:i') ?>.
</div>

</body>
</html>
