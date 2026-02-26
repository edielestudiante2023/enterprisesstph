<h5 class="mb-3">
    <i class="fas fa-shield-alt me-2"></i>Plan de Saneamiento Básico
    <span class="badge bg-<?= $inspeccion['estado'] === 'completo' ? 'success' : 'warning text-dark' ?>" style="font-size: 12px;">
        <?= $inspeccion['estado'] === 'completo' ? 'Completo' : 'Borrador' ?>
    </span>
</h5>

<?php if (session()->getFlashdata('msg')): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= session()->getFlashdata('msg') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card mb-3">
    <div class="card-header" style="background: #1c2437; color: white;">
        <i class="fas fa-info-circle me-1"></i> Datos Generales
    </div>
    <div class="card-body p-0">
        <table class="table table-bordered mb-0" style="font-size: 14px;">
            <tr><td class="fw-bold bg-light" style="width: 40%;">Cliente</td><td><?= esc($cliente['nombre_cliente'] ?? '—') ?></td></tr>
            <tr><td class="fw-bold bg-light">Fecha del Plan</td><td><?= !empty($inspeccion['fecha_programa']) ? date('d/m/Y', strtotime($inspeccion['fecha_programa'])) : '—' ?></td></tr>
            <tr><td class="fw-bold bg-light">Responsable</td><td><?= esc($inspeccion['nombre_responsable'] ?? '—') ?></td></tr>
            <tr><td class="fw-bold bg-light">Consultor</td><td><?= esc($consultor['nombre_consultor'] ?? '—') ?></td></tr>
            <tr><td class="fw-bold bg-light">Documento</td><td><strong>FT-SST-219</strong> — Plan de Saneamiento Básico (Versión 001)</td></tr>
        </table>
    </div>
</div>

<div class="d-grid gap-2 mb-4">
    <?php if (!empty($inspeccion['ruta_pdf'])): ?>
    <a href="/inspecciones/plan-saneamiento/pdf/<?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-primary" target="_blank">
        <i class="fas fa-file-pdf me-2"></i>Ver PDF
    </a>
    <?php endif; ?>
    <?php if ($inspeccion['estado'] === 'borrador'): ?>
    <a href="/inspecciones/plan-saneamiento/edit/<?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline">
        <i class="fas fa-edit me-2"></i>Editar
    </a>
    <?php endif; ?>
    <a href="/inspecciones/plan-saneamiento" class="btn btn-pwa btn-pwa-outline">
        <i class="fas fa-arrow-left me-2"></i>Volver al listado
    </a>
</div>
