<h5 class="mb-3">
    <i class="fas fa-tint-slash me-2"></i>Plan de Contingencia — Sin Suministro de Agua
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
            <tr>
                <td class="fw-bold bg-light" style="width: 40%;">Cliente</td>
                <td><?= esc($cliente['nombre_cliente'] ?? '—') ?></td>
            </tr>
            <tr>
                <td class="fw-bold bg-light">Fecha del Plan</td>
                <td><?= !empty($inspeccion['fecha_programa']) ? date('d/m/Y', strtotime($inspeccion['fecha_programa'])) : '—' ?></td>
            </tr>
            <tr>
                <td class="fw-bold bg-light">Responsable</td>
                <td><?= esc($inspeccion['nombre_responsable'] ?? '—') ?></td>
            </tr>
            <tr>
                <td class="fw-bold bg-light">Consultor</td>
                <td><?= esc($consultor['nombre_consultor'] ?? '—') ?></td>
            </tr>
            <?php if (!empty($inspeccion['empresa_carrotanque'])): ?>
            <tr>
                <td class="fw-bold bg-light">Proveedor Carrotanque</td>
                <td><?= esc($inspeccion['empresa_carrotanque']) ?></td>
            </tr>
            <?php endif; ?>
            <?php if (!empty($inspeccion['capacidad_reserva'])): ?>
            <tr>
                <td class="fw-bold bg-light">Capacidad de Reserva</td>
                <td><?= esc($inspeccion['capacidad_reserva']) ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td class="fw-bold bg-light">Documento</td>
                <td><strong>FT-SST-234</strong> — Plan de Contingencia Sin Suministro de Agua (Versión 001)</td>
            </tr>
        </table>
    </div>
</div>

<div class="d-grid gap-2 mb-4">
    <?php if (!empty($inspeccion['ruta_pdf'])): ?>
    <a href="<?= base_url('/inspecciones/contingencia-agua/pdf/') ?><?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-primary" target="_blank">
        <i class="fas fa-file-pdf me-2"></i>Ver PDF
    </a>
    <?php endif; ?>
    <?php if ($inspeccion['estado'] === 'completo'): ?>
    <a href="<?= base_url('/inspecciones/contingencia-agua/regenerar/') ?><?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline" onclick="return confirm('¿Regenerar el PDF con la plantilla actual?')">
        <i class="fas fa-sync-alt me-2"></i>Regenerar PDF
    </a>
    <a href="<?= base_url('/inspecciones/contingencia-agua/enviar-email/') ?><?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline" onclick="return confirm('¿Enviar el PDF por email al cliente, consultor y consultor externo?')">
        <i class="fas fa-envelope me-2"></i>Enviar por Email
    </a>
    <?php endif; ?>
    <?php if ($inspeccion['estado'] === 'borrador'): ?>
    <a href="<?= base_url('/inspecciones/contingencia-agua/edit/') ?><?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline">
        <i class="fas fa-edit me-2"></i>Editar
    </a>
    <?php endif; ?>
    <a href="<?= base_url('/inspecciones/contingencia-agua') ?>" class="btn btn-pwa btn-pwa-outline">
        <i class="fas fa-arrow-left me-2"></i>Volver al listado
    </a>
</div>
