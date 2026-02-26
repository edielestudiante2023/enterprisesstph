<div class="card mb-3">
    <div class="card-header bg-dark text-white py-2 px-3" style="font-size:13px;">
        <i class="fas fa-chart-line me-1"></i> Datos del KPI - Limpieza y Desinfección
    </div>
    <div class="card-body p-3">
        <table class="table table-sm table-bordered mb-0" style="font-size:13px;">
            <tr><th style="width:35%;">Fecha</th><td><?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td></tr>
            <tr><th>Responsable</th><td><?= esc($inspeccion['nombre_responsable'] ?? 'N/A') ?></td></tr>
            <tr><th>Indicador</th><td><?= esc($inspeccion['indicador']) ?></td></tr>
            <tr><th>Cumplimiento</th><td><strong><?= number_format($inspeccion['cumplimiento'], 1) ?>%</strong></td></tr>
        </table>
    </div>
</div>

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
                         style="max-height:150px; object-fit:cover;">
                    <div style="font-size:11px;" class="text-muted">Evidencia <?= $i ?></div>
                </div>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($inspeccion['ruta_pdf'])): ?>
<a href="/inspecciones/kpi-limpieza/pdf/<?= $inspeccion['id'] ?>" class="btn btn-primary btn-sm mb-3" target="_blank">
    <i class="fas fa-file-pdf me-1"></i> Ver PDF
</a>
<?php endif; ?>
