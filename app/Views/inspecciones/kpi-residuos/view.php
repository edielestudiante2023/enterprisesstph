<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>KPI Residuos Sólidos</h5>
    <a href="/inspecciones/kpi-residuos" class="btn btn-sm btn-outline-secondary">
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
            <tr><th>Consultor</th><td><?= esc($consultor['nombre'] ?? 'N/A') ?></td></tr>
            <tr><th>Indicador</th><td><?= esc($inspeccion['indicador']) ?></td></tr>
            <tr><th>Cumplimiento</th><td><strong><?= number_format($inspeccion['cumplimiento'], 1) ?>%</strong></td></tr>
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
<a href="/inspecciones/kpi-residuos/pdf/<?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-primary mb-4" target="_blank">
    <i class="fas fa-file-pdf me-2"></i>Ver PDF
</a>
<?php endif; ?>
    <?php if ($inspeccion['estado'] === 'completo'): ?>
    <a href="/inspecciones/kpi-residuos/regenerar/<?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline" onclick="return confirm('¿Regenerar el PDF con la plantilla actual?')">
        <i class="fas fa-sync-alt me-2"></i>Regenerar PDF
    </a>
    <?php endif; ?>

<script>
function openPhoto(src) {
    Swal.fire({ imageUrl: src, imageAlt: 'Foto', showConfirmButton: false, showCloseButton: true, width: 'auto' });
}
</script>
