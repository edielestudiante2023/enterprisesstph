<h5 class="mb-3">
    <i class="fas fa-bug me-2"></i>Programa de Control Integrado de Plagas
</h5>

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
                <td class="fw-bold bg-light">Fecha del Programa</td>
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
            <tr>
                <td class="fw-bold bg-light">Documento</td>
                <td><strong>FT-SST-227</strong> — Programa de Control Integrado de Plagas (Versión 001)</td>
            </tr>
        </table>
    </div>
</div>

<div class="d-grid gap-2 mb-4">
    <?php if (!empty($inspeccion['ruta_pdf'])): ?>
    <a href="/inspecciones/control-plagas/pdf/<?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-primary" target="_blank">
        <i class="fas fa-file-pdf me-2"></i>Ver PDF
    </a>
    <?php endif; ?>
    <a href="/client/inspecciones/control-plagas" class="btn btn-pwa btn-pwa-outline">
        <i class="fas fa-arrow-left me-2"></i>Volver al listado
    </a>
</div>
