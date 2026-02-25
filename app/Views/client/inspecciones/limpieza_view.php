<div class="page-header">
    <h1><i class="fas fa-pump-soap me-2"></i> Programa de Limpieza y Desinfección</h1>
    <a href="<?= base_url('client/inspecciones/limpieza-desinfeccion') ?>" class="btn-back">
        <i class="fas fa-arrow-left me-1"></i> Volver
    </a>
</div>

<!-- Datos generales -->
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">DATOS GENERALES</h6>
        <table class="table table-sm mb-0" style="font-size:14px;">
            <tr><td class="text-muted" style="width:40%;">Cliente</td><td><strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong></td></tr>
            <tr><td class="text-muted">Fecha del programa</td><td><?= date('d/m/Y', strtotime($inspeccion['fecha_programa'])) ?></td></tr>
            <tr><td class="text-muted">Responsable</td><td><?= esc($inspeccion['nombre_responsable'] ?? 'No especificado') ?></td></tr>
            <tr><td class="text-muted">Consultor</td><td><?= esc($consultor['nombre_consultor'] ?? '') ?></td></tr>
            <tr><td class="text-muted">Documento</td><td><span class="badge bg-dark">FT-SST-225</span></td></tr>
        </table>
    </div>
</div>

<!-- Acciones -->
<div class="mb-4">
    <?php if (!empty($inspeccion['ruta_pdf'])): ?>
    <a href="/inspecciones/limpieza-desinfeccion/pdf/<?= $inspeccion['id'] ?>" class="btn btn-primary" target="_blank" style="background:#bd9751; border-color:#bd9751;">
        <i class="fas fa-file-pdf"></i> Ver PDF
    </a>
    <?php endif; ?>
</div>
