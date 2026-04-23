<?php
function irapiColorView($c) {
    return match ($c) { 'SIN_RIESGO' => '#1b7e3f', 'BAJO' => '#d4a70e', 'MEDIO' => '#e67815', 'ALTO' => '#c0392b', default => '#888' };
}
function irapiLabelView($c) {
    return match ($c) { 'SIN_RIESGO' => 'Sin riesgo', 'BAJO' => 'Bajo', 'MEDIO' => 'Medio', 'ALTO' => 'ALTO', default => '—' };
}
?>
<div class="container-fluid px-3">
    <div class="mt-2 mb-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Inspeccion de Piscinas — <?= esc($cliente['nombre_cliente'] ?? '') ?></h6>
        <span class="badge <?= $inspeccion['estado'] === 'completo' ? 'badge-completo' : 'badge-borrador' ?>">
            <?= $inspeccion['estado'] === 'completo' ? 'Completo' : 'Borrador' ?>
        </span>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">DATOS GENERALES</h6>
            <table class="table table-sm mb-0" style="font-size:13px;">
                <tr><td class="text-muted" style="width:40%;">Cliente</td><td><strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong></td></tr>
                <tr><td class="text-muted">Fecha inspeccion</td><td><?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td></tr>
                <tr><td class="text-muted">Consultor</td><td><?= esc($consultor['nombre_consultor'] ?? '') ?></td></tr>
                <tr><td class="text-muted">Superficie establecimiento (m²)</td><td><?= esc($inspeccion['superficie_total_establecimiento_m2'] ?? '—') ?></td></tr>
                <tr><td class="text-muted">Total piscinas</td><td><strong><?= $inspeccion['total_piscinas'] ?? 0 ?></strong></td></tr>
                <tr><td class="text-muted">Empresa mantenimiento</td><td><?= esc($inspeccion['empresa_mantenimiento'] ?? '-') ?></td></tr>
                <tr><td class="text-muted">Concepto sanitario Sec. Salud</td><td><?= esc(ucfirst($inspeccion['concepto_sanitario'] ?? 'no_emitido')) ?></td></tr>
                <tr><td class="text-muted">DEA presente</td><td><?= esc($inspeccion['dea_presente'] ?? 'NA') ?> — Personal cap.: <?= (int)($inspeccion['dea_personal_capacitado_cantidad'] ?? 0) ?></td></tr>
                <tr><td class="text-muted">Operador certificado</td><td><?= esc($inspeccion['operador_certificado_nombre'] ?? 'No registrado') ?></td></tr>
            </table>
        </div>
    </div>

    <?php if (!empty($evidenciasMap)): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">EVIDENCIAS FOTOGRAFICAS — DOCUMENTACION Y GESTION SANITARIA</h6>
            <?php foreach ($camposEvidenciaMaestro as $codigo => $label): ?>
                <?php $rows = $evidenciasMap[$codigo] ?? []; if (empty($rows)) continue; ?>
                <div class="mb-3">
                    <div class="small-label" style="font-size:12px; font-weight:700; color:#1c2437;"><?= esc($label) ?> — <?= count($rows) ?> foto(s)</div>
                    <div class="d-flex flex-wrap gap-2 mt-1">
                        <?php foreach ($rows as $r): ?>
                        <img src="<?= base_url('/' . $r['foto_path']) ?>" onclick="openPhotoView(this.src)"
                             style="width:120px;height:120px;object-fit:cover;border:1px solid #ccc;border-radius:4px;cursor:pointer;"
                             title="<?= esc($r['descripcion'] ?? '') ?>">
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="modal fade" id="photoModalView" tabindex="-1"><div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content bg-dark"><div class="modal-body p-1 text-center"><img id="photoModalViewImg" src="" class="img-fluid" style="max-height:80vh;"></div></div></div></div>
    <script>
    function openPhotoView(src) {
        const m = document.getElementById('photoModalView');
        if (!m) return;
        document.getElementById('photoModalViewImg').src = src;
        new bootstrap.Modal(m).show();
    }
    </script>
    <?php endif; ?>

    <?php foreach ($piscinas as $i => $p): ?>
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Piscina #<?= $i+1 ?> — <?= esc($p['identificador']) ?></h6>
                <div>
                    <span class="badge" style="background: <?= irapiColorView($p['irapi_clasificacion'] ?? 'SIN_RIESGO') ?>; color:#fff;">
                        IRAPI <?= number_format((float)($p['irapi_valor'] ?? 0), 2) ?> — <?= irapiLabelView($p['irapi_clasificacion'] ?? 'SIN_RIESGO') ?>
                    </span>
                    <?php if (!empty($p['isl_valor'])): ?>
                    <span class="badge" style="background: #555; color:#fff;">ISL <?= number_format((float)$p['isl_valor'], 2) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <table class="table table-sm mb-2" style="font-size:12px;">
                <tr><td>Tipo</td><td><?= esc($p['tipo']) ?></td><td>Uso</td><td><?= esc($p['uso']) ?></td></tr>
                <tr><td>Climatizada</td><td><?= esc($p['climatizada']) ?></td><td>Botiquin</td><td><?= esc($p['botiquin_tipo']) ?></td></tr>
                <tr><td>Prof. max (m)</td><td><?= esc($p['profundidad_max_m'] ?? '—') ?></td><td>Prof. min (m)</td><td><?= esc($p['profundidad_min_m'] ?? '—') ?></td></tr>
            </table>

            <?php $params = $parametrosMap[$p['id']] ?? []; if (!empty($params)): ?>
            <h6 class="mt-2" style="font-size:12px;">Parametros in situ</h6>
            <table class="table table-sm mb-2" style="font-size:11px;">
                <tr><th>Parametro</th><th>Valor</th><th>Unidad</th><th>Rango</th><th>Conforme</th></tr>
                <?php foreach ($params as $prm):
                    $lbl = $parametrosCfg[$prm['parametro']]['label'] ?? $prm['parametro'];
                    $cc = $prm['conforme'] === 'SI' ? 'text-success' : ($prm['conforme'] === 'NO' ? 'text-danger' : 'text-muted');
                ?>
                <tr><td><?= esc($lbl) ?></td><td><?= esc($prm['valor'] ?? '—') ?></td><td><?= esc($prm['unidad'] ?? '') ?></td><td style="font-size:10px;"><?= esc($prm['rango_referencia'] ?? '') ?></td><td class="<?= $cc ?>"><strong><?= esc($prm['conforme']) ?></strong></td></tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>

            <?php $ensayos = $ensayosMap[$p['id']] ?? []; if (!empty($ensayos)): ?>
            <h6 class="mt-2" style="font-size:12px;">Ensayos de laboratorio</h6>
            <ul style="font-size:11px;">
                <?php foreach ($ensayos as $e): ?>
                <li><?= esc($e['tipo']) ?> — <?= !empty($e['fecha_toma']) ? date('d/m/Y', strtotime($e['fecha_toma'])) : '—' ?> — <?= esc($e['laboratorio']) ?> (<?= esc($e['conforme_global']) ?>)</li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>

            <?php if (!empty($p['foto'])): ?>
            <img src="<?= base_url('/' . $p['foto']) ?>" style="max-width:220px;max-height:160px;border:1px solid #ddd;">
            <?php endif; ?>

            <?php $evidsDet = $evidenciasDetMap[$p['id']] ?? []; if (!empty($evidsDet)): ?>
            <h6 class="mt-2" style="font-size:12px;">Evidencias adicionales (<?= count($evidsDet) ?> foto<?= count($evidsDet) > 1 ? 's' : '' ?>)</h6>
            <div class="d-flex flex-wrap gap-2 mt-1">
                <?php foreach ($evidsDet as $ev): ?>
                <div style="position:relative;">
                    <img src="<?= base_url('/' . $ev['foto_path']) ?>"
                         style="width:100px;height:100px;object-fit:cover;border:1px solid #ccc;border-radius:4px;cursor:pointer;"
                         onclick="openPhotoView(this.src)"
                         title="<?= esc($ev['categoria'] ?? '') ?>">
                    <div style="position:absolute;bottom:0;left:0;right:0;background:rgba(0,0,0,0.55);color:#fff;font-size:9px;text-align:center;padding:1px 2px;"><?= esc($ev['categoria'] ?? 'OTRA') ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($p['observaciones'])): ?>
            <p style="font-size:11px; margin-top:4px;"><strong>Obs.:</strong> <?= nl2br(esc($p['observaciones'])) ?></p>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (!empty($inspeccion['recomendaciones_generales'])): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">RECOMENDACIONES</h6>
            <p style="font-size:12px;"><?= nl2br(esc($inspeccion['recomendaciones_generales'])) ?></p>
        </div>
    </div>
    <?php endif; ?>

    <div class="d-grid gap-2 mb-4">
        <?php if ($inspeccion['estado'] === 'completo'): ?>
        <a href="<?= base_url('/inspecciones/piscinas/pdf/' . $inspeccion['id']) ?>" target="_blank" class="btn btn-pwa-primary py-2"><i class="fas fa-file-pdf"></i> Ver PDF</a>
        <a href="<?= base_url('/inspecciones/piscinas/regenerar-pdf/' . $inspeccion['id']) ?>" class="btn btn-outline-secondary py-2"><i class="fas fa-sync"></i> Regenerar PDF</a>
        <a href="<?= base_url('/inspecciones/piscinas/enviar-email/' . $inspeccion['id']) ?>" class="btn btn-outline-primary py-2"><i class="fas fa-envelope"></i> Reenviar email</a>
        <?php else: ?>
        <a href="<?= base_url('/inspecciones/piscinas/edit/' . $inspeccion['id']) ?>" class="btn btn-pwa-primary py-2"><i class="fas fa-edit"></i> Editar</a>
        <?php endif; ?>
        <a href="<?= base_url('/inspecciones/piscinas') ?>" class="btn btn-outline-dark py-2"><i class="fas fa-arrow-left"></i> Volver al listado</a>
    </div>
</div>
