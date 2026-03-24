<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0"><i class="fas fa-chart-area me-2"></i>KPI Agua Potable</h5>
    <a href="<?= base_url('/inspecciones/kpi-agua-potable') ?>" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
</div>

<?php if (session()->getFlashdata('msg')): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= session()->getFlashdata('msg') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card mb-3">
    <div class="card-header bg-dark text-white py-2 px-3" style="font-size:13px;">
        <i class="fas fa-info-circle me-1"></i> Datos del KPI
    </div>
    <div class="card-body p-3">
        <table class="table table-sm table-bordered mb-0" style="font-size:13px;">
            <tr><th style="width:35%;">Cliente</th><td><?= esc($cliente['nombre_cliente'] ?? 'N/A') ?></td></tr>
            <tr><th>Fecha</th><td><?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td></tr>
            <tr><th>Responsable</th><td><?= esc($inspeccion['nombre_responsable'] ?? 'N/A') ?></td></tr>
            <tr><th>Indicador</th><td><?= esc($inspeccion['indicador']) ?></td></tr>
            <?php $cfg = ($indicadorConfig[$inspeccion['indicador']] ?? null); ?>
            <?php if ($cfg): ?>
            <tr><th>Fórmula</th><td style="font-size:12px;"><?= esc($cfg['formula']) ?></td></tr>
            <tr><th>Meta</th><td><?= esc($cfg['meta_texto']) ?></td></tr>
            <?php endif; ?>
            <?php if ($inspeccion['valor_numerador'] !== null && $inspeccion['valor_denominador'] !== null): ?>
            <tr><th><?= esc($cfg['label_numerador'] ?? 'Numerador') ?></th><td><?= esc($inspeccion['valor_numerador']) ?></td></tr>
            <tr><th><?= esc($cfg['label_denominador'] ?? 'Denominador') ?></th><td><?= esc($inspeccion['valor_denominador']) ?></td></tr>
            <?php endif; ?>
            <tr><th>Cumplimiento</th><td><strong><?= number_format($inspeccion['cumplimiento'], 1) ?>%</strong></td></tr>
            <?php if (!empty($inspeccion['calificacion_cualitativa'])): ?>
            <tr><th>Calificación</th><td>
                <span class="badge <?= $inspeccion['calificacion_cualitativa'] === 'CUMPLE' ? 'bg-success' : 'bg-danger' ?> fs-6">
                    <?= esc($inspeccion['calificacion_cualitativa']) ?>
                </span>
            </td></tr>
            <?php endif; ?>
            <?php if (!empty($inspeccion['observaciones'])): ?>
            <tr><th>Observaciones</th><td><?= esc($inspeccion['observaciones']) ?></td></tr>
            <?php endif; ?>
            <tr><th>Estado</th><td><span class="badge bg-success">Completo</span></td></tr>
        </table>
    </div>
</div>

<!-- Evidencias fotográficas -->
<?php
$tieneEvidencias = false;
for ($i = 1; $i <= 4; $i++) {
    if (!empty($inspeccion["registro_formato_$i"])) { $tieneEvidencias = true; break; }
}
?>
<?php if ($tieneEvidencias): ?>
<div class="card mb-3">
    <div class="card-header bg-dark text-white py-2 px-3" style="font-size:13px;">
        <i class="fas fa-camera me-1"></i> Evidencias
    </div>
    <div class="card-body p-3">
        <div class="row g-2">
            <?php for ($i = 1; $i <= 4; $i++): ?>
                <?php $campo = "registro_formato_$i"; ?>
                <?php if (!empty($inspeccion[$campo])): ?>
                <div class="col-6 text-center">
                    <img src="/<?= esc($inspeccion[$campo]) ?>" class="img-fluid rounded mb-1"
                         style="max-height:150px; object-fit:cover; cursor:pointer;"
                         onclick="openPhoto(this.src)">
                    <div style="font-size:11px;" class="text-muted">Evidencia <?= $i ?></div>
                </div>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- PDF -->
<?php if (!empty($inspeccion['ruta_pdf'])): ?>
<a href="<?= base_url('/inspecciones/kpi-agua-potable/pdf/') ?><?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-primary mb-4" target="_blank">
    <i class="fas fa-file-pdf me-2"></i>Ver PDF
</a>
<?php endif; ?>
    <?php if ($inspeccion['estado'] === 'completo'): ?>
    <a href="<?= base_url('/inspecciones/kpi-agua-potable/regenerar/') ?><?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline" onclick="return confirm('¿Regenerar el PDF con la plantilla actual?')">
        <i class="fas fa-sync-alt me-2"></i>Regenerar PDF
    </a>
    <a href="<?= base_url('/inspecciones/kpi-agua-potable/enviar-email/') ?><?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline" onclick="return confirm('¿Enviar el PDF por email al cliente, consultor y consultor externo?')">
        <i class="fas fa-envelope me-2"></i>Enviar por Email
    </a>
    <?php endif; ?>

<script>
function openPhoto(src) {
    Swal.fire({ imageUrl: src, imageAlt: 'Foto', showConfirmButton: false, showCloseButton: true, width: 'auto' });
}
</script>
