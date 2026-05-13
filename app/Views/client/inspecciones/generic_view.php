<div class="page-header">
    <h1><i class="fas <?= esc($icono ?? 'fa-clipboard-list') ?> me-2"></i> <?= esc($titulo) ?></h1>
    <a href="<?= base_url($back_url) ?>" class="btn-back">
        <i class="fas fa-arrow-left me-1"></i> Volver
    </a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <h6 class="card-title">DATOS GENERALES</h6>
        <table class="table table-sm mb-0" style="font-size:14px;">
            <tr><td class="text-muted" style="width:40%;">Cliente</td><td><strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong></td></tr>
            <?php if (!empty($registro['nombre_consultor'])): ?>
            <tr><td class="text-muted">Consultor</td><td><?= esc($registro['nombre_consultor']) ?></td></tr>
            <?php endif; ?>
            <?php if (!empty($fecha_campo) && !empty($registro[$fecha_campo])): ?>
            <tr><td class="text-muted">Fecha</td><td><?= date('d/m/Y', strtotime($registro[$fecha_campo])) ?></td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <h6 class="card-title">DETALLE</h6>
        <table class="table table-sm table-bordered mb-0" style="font-size:13px;">
            <tbody>
            <?php foreach ($registro as $campo => $valor): ?>
                <?php
                    if (in_array($campo, ['id','id_cliente','id_consultor','ruta_pdf','archivo','created_at','updated_at','token'], true)) continue;
                    if ($valor === null || $valor === '') continue;
                    $label = ucfirst(str_replace('_', ' ', $campo));
                ?>
                <tr>
                    <th style="width:40%; background:#f8f9fa;"><?= esc($label) ?></th>
                    <td><?= nl2br(esc((string) $valor)) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="d-grid gap-2 mb-4">
    <?php if (!empty($pdf_url)): ?>
    <a href="<?= base_url($pdf_url) ?>" class="btn btn-pdf" target="_blank">
        <i class="fas fa-file-pdf me-2"></i> Ver PDF
    </a>
    <?php elseif (!empty($registro['ruta_pdf'])): ?>
    <a href="<?= base_url($registro['ruta_pdf']) ?>" class="btn btn-pdf" target="_blank">
        <i class="fas fa-file-pdf me-2"></i> Ver PDF
    </a>
    <?php endif; ?>

    <?php if (!empty($registro['archivo'])): ?>
    <a href="<?= base_url($registro['archivo']) ?>" class="btn btn-outline-secondary" target="_blank">
        <i class="fas fa-paperclip me-2"></i> Ver archivo adjunto
    </a>
    <?php endif; ?>
</div>
