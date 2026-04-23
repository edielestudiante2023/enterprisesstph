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

<!-- Hallazgo principal: peor IRAPI -->
<div class="hallazgo-critico">
    <h2>Clasificacion IRAPI del establecimiento (peor caso)</h2>
    <div style="font-size: 22px; font-weight: 700;"><?= number_format((float)$peorVal, 2) ?> — <?= irapiLabel($peorClas) ?></div>
    <div style="font-size: 9px; margin-top: 4px;">Indice de Riesgo del Agua segun Anexo II de la Resolucion 234/2026 Minsalud.</div>
</div>

<!-- INTRODUCCION / ALCANCE -->
<h2>1. Introduccion, alcance y justificacion</h2>
<div style="font-size: 10px; text-align: justify; line-height: 1.4;">
    <p>El presente informe consolida los hallazgos de la inspeccion realizada por el consultor de Seguridad y Salud en el Trabajo (SST) a las piscinas y estanques de uso restringido / colectivo del cliente <strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong>, el dia <strong><?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></strong>.</p>

    <p><strong>Alcance.</strong> Verifica el cumplimiento normativo en 4 frentes: (a) <em>infraestructura y seguridad</em> de la piscina (Ley 1209/2008), (b) <em>calidad del agua</em> con parametros fisicos, quimicos y microbiologicos (Resolucion 234 de 2026 del Ministerio de Salud y Proteccion Social, Anexo I), (c) <em>buenas practicas sanitarias</em>: manejo de quimicos, residuos, documentacion Art. 15, plan de saneamiento Art. 17 (Res 234/2026 Capitulo III), y (d) <em>dotacion de emergencia</em>: DEA, botiquines Tipo A/B/C segun superficie, flotadores, bastones (Art. 18 Res 234/2026).</p>

    <p><strong>Metodo.</strong> Cada parametro in situ capturado se compara contra los valores aceptables del Anexo I y se marca Conforme (SI) / No conforme (NO) / No aplica (NA). Se calcula automaticamente el <strong>IRAPI</strong> (Indice de Riesgo del Agua, Anexo II) a partir de las mediciones, ponderando 45% microbiologicos, 20% residual de desinfectante, 30% asociados a cloracion (pH, ORP, cianurico) y 5% turbiedad. El IRAPI clasifica la piscina en Sin Riesgo (0-10), Bajo (10.1-35), Medio (35.1-75) o Alto (75.1-100). Tambien se calcula el Indice de Saturacion de Langelier (ISL) para determinar si el agua tiende a ser corrosiva, balanceada o incrustante.</p>

    <p><strong>Disclaimer.</strong> Este informe identifica hallazgos de riesgo SST para la copropiedad y sirve de insumo para el plan de mejoramiento. <u>No reemplaza</u> el concepto sanitario de la Secretaria de Salud competente (Art. 10 Res 234), ni la certificacion del operador de piscinas por entidad acreditada (Art. 11 num 7), ni los ensayos de laboratorio trimestrales (Art. 6) que deben ser realizados por laboratorio privado acreditado y entregados por el responsable del establecimiento.</p>
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

<div class="pagebreak"></div>

<h2>4. Piscinas inspeccionadas</h2>

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

    <h3>4.<?= $i+1 ?>.1 Parametros in situ (Anexo I)</h3>
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

    <h3>4.<?= $i+1 ?>.2 Ensayos de laboratorio</h3>
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

    <h3>4.<?= $i+1 ?>.3 Infraestructura, emergencia y avisos</h3>
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
    <h3>4.<?= $i+1 ?>.4 Checklist botiquin tipo <?= esc($p['botiquin_tipo']) ?> (Anexo III)</h3>
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
    <h3>4.<?= $i+1 ?>.5 Observaciones y evidencia</h3>
    <?php if (!empty($p['observaciones'])): ?>
    <div style="margin: 4px 0; font-size: 9.5px;"><?= nl2br(esc($p['observaciones'])) ?></div>
    <?php endif; ?>
    <?php if (!empty($p['foto_base64'])): ?>
    <img class="foto" src="<?= $p['foto_base64'] ?>">
    <?php endif; ?>
    <?php endif; ?>

    <?php $detEvids = $evidenciasDetB64[$idDet] ?? []; if (!empty($detEvids)): ?>
    <h3>4.<?= $i+1 ?>.6 Evidencias adicionales (<?= count($detEvids) ?>)</h3>
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
<h2>5. Recomendaciones generales</h2>
<div style="font-size: 10px;"><?= nl2br(esc($inspeccion['recomendaciones_generales'])) ?></div>
<?php endif; ?>

<div class="pagebreak"></div>

<h2>6. Marco normativo aplicable</h2>

<div style="font-size: 9.5px; text-align: justify; margin-bottom: 6px;">
    La inspeccion se sustenta en el siguiente marco juridico vigente en Colombia. Cada hallazgo de este informe remite al articulado especifico que lo exige.
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

<h3 style="font-size: 11px;">6.2 Articulos especificos de la Resolucion 234 de 2026 evaluados</h3>
<table class="data" style="font-size: 9px;">
    <tr><th style="width: 14%;">Articulo</th><th style="width: 28%;">Tema</th><th>Como se evalua en este informe</th></tr>
    <tr><td><strong>Art. 4</strong></td><td>Fuente de abastecimiento</td><td>Se verifica si el agua proviene de acueducto o fuente natural con analisis fisico-quimico y microbiologico de referencia.</td></tr>
    <tr><td><strong>Art. 5</strong></td><td>Dosificacion segura</td><td>Tres chequeos: dosificacion independiente por quimico, sistema de seguridad por retorno de flujo, prohibicion de dosificacion manual con publico.</td></tr>
    <tr><td><strong>Art. 6</strong></td><td>Muestras de control</td><td>Parametros fisicos y quimicos: ejecucion diaria (pH, cloros, temperatura, color, olor, transparencia) + trimestral con laboratorio (turbidez, ORP, TDS, microbiologicos).</td></tr>
    <tr><td><strong>Art. 7</strong></td><td>Equipos in situ</td><td>Equipos calibrados para analisis rutinario: evidenciado por la existencia del libro de registro diario.</td></tr>
    <tr><td><strong>Art. 9 + Anexo II</strong></td><td>IRAPI</td><td>Se calcula automaticamente desde los parametros capturados: VCM(45%) + VCR(20%) + VAC(30%) + VCT(5%). Clasificacion: 0-10 Sin riesgo, 10.1-35 Bajo, 35.1-75 Medio, 75.1-100 Alto.</td></tr>
    <tr><td><strong>Art. 10</strong></td><td>Concepto sanitario</td><td>Documento emitido por Secretaria de Salud. Se registra estado (favorable/desfavorable/no emitido), fecha y foto.</td></tr>
    <tr><td><strong>Art. 11 num 7</strong></td><td>Operador certificado</td><td>Se documenta nombre, entidad certificadora (SENA, IDEAM, universidad, autoridad municipal) y vigencia.</td></tr>
    <tr><td><strong>Art. 13</strong></td><td>Manejo quimicos</td><td>Fichas tecnicas, Hojas de Seguridad (SDS), EPP, etiquetado GHS.</td></tr>
    <tr><td><strong>Art. 14</strong></td><td>Area residuos</td><td>Area senalizada, separada por tipo, con iluminacion, ventilacion, pisos con drenajes.</td></tr>
    <tr><td><strong>Art. 15</strong></td><td>8 procedimientos obligatorios</td><td>(1) operacion del agua, (2) limpieza del sistema, (3) cierre temporal, (4) muestras, (5) resultados fuera de rango + liberacion fecal, (6) microorganismos no listados, (7) libro de control, (8) plan saneamiento.</td></tr>
    <tr><td><strong>Art. 16</strong></td><td>Libro de registro</td><td>Registro sistematizado con pH, cloros, transparencia, ISL, productos, caudal, retrolavado, bañistas, horas de operacion, volumen de reposicion. Par. 2: publicacion mensual visible al publico.</td></tr>
    <tr><td><strong>Art. 17</strong></td><td>Plan saneamiento</td><td>5 programas: (1) limpieza y desinfeccion, (2) residuos solidos, (3) residuos liquidos, (4) control integrado plagas, (5) abastecimiento agua potable.</td></tr>
    <tr><td><strong>Art. 18 + Anexo III</strong></td><td>Dotacion de emergencia</td><td>DEA con personal capacitado, botiquin por m² (Tipo A &lt;500 / Tipo B 500-2000 / Tipo C &gt;2000) con items listados en Anexo III, 2 flotadores circulares, baston con gancho.</td></tr>
</table>

<h3 style="font-size: 11px; margin-top: 8px;">6.3 Resumen congelado al finalizar</h3>
<div style="font-size: 9px; color: #555; text-align: justify;">
    <?= esc($marcoNormativo) ?>
</div>

<div class="footer">
    Generado automaticamente por el modulo de inspecciones SST — Fecha emision PDF: <?= date('d/m/Y H:i') ?>.
</div>

</body>
</html>
