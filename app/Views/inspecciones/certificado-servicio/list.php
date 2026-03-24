<?php
/**
 * @var array $cfg   ['nombre', 'slug', 'icon', 'detailreport']
 * @var int   $tipo
 * @var array $registros
 */
?>
<div class="container-fluid px-3">

    <?php if ($flash = session()->getFlashdata('msg')): ?>
    <div class="alert alert-success mt-2" style="font-size:14px;"><?= esc($flash) ?></div>
    <?php endif; ?>
    <?php if ($flash = session()->getFlashdata('error')): ?>
    <div class="alert alert-danger mt-2" style="font-size:14px;"><?= esc($flash) ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
        <div>
            <h6 class="mb-0" style="color:#1c2437;font-weight:700;">
                <i class="fas <?= esc($cfg['icon']) ?>"></i> <?= esc($cfg['nombre']) ?>
            </h6>
            <small class="text-muted"><?= count($registros) ?> registros</small>
        </div>
        <a href="<?= base_url('/inspecciones/' . $cfg['slug'] . '/create') ?>" class="btn btn-pwa btn-pwa-primary" style="font-size:14px; padding:8px 16px;">
            <i class="fas fa-plus"></i> Registrar
        </a>
    </div>

    <?php if (empty($registros)): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas <?= esc($cfg['icon']) ?>" style="font-size:48px; opacity:.3;"></i>
        <p class="mt-3">No hay registros aún.</p>
        <a href="<?= base_url('/inspecciones/' . $cfg['slug'] . '/create') ?>" class="btn btn-pwa btn-pwa-primary mt-2">
            <i class="fas fa-plus"></i> Registrar servicio
        </a>
    </div>
    <?php else: ?>

    <?php foreach ($registros as $reg): ?>
    <div class="card mb-2" style="border-left: 4px solid #bd9751;">
        <div class="card-body py-2 px-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong style="font-size:14px;"><?= esc($reg['nombre_cliente'] ?? 'Sin cliente') ?></strong>
                    <div class="text-muted" style="font-size:12px;">
                        <?= date('d/m/Y', strtotime($reg['fecha_servicio'])) ?>
                        <?php if (!empty($reg['observaciones'])): ?>
                            &middot; <?= esc(mb_strimwidth($reg['observaciones'], 0, 50, '…')) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="d-flex gap-1 align-items-center">
                    <?php if (!empty($reg['archivo'])): ?>
                    <a href="<?= base_url($reg['archivo']) ?>" target="_blank" class="btn btn-sm btn-outline-dark" title="Ver certificado">
                        <i class="fas fa-file-alt"></i>
                    </a>
                    <?php endif; ?>
                    <button type="button" class="btn btn-sm btn-outline-danger"
                        onclick="confirmarEliminar('<?= base_url('/inspecciones/' . $cfg['slug'] . '/delete/' . $reg['id']) ?>')">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <?php if (!empty($reg['id_vencimiento'])): ?>
            <div style="font-size:11px; color:#28a745; margin-top:3px;">
                <i class="fas fa-check-circle"></i> Vencimiento cerrado
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <?php endif; ?>
</div>

<script>
function confirmarEliminar(url) {
    Swal.fire({
        title: '¿Eliminar registro?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Eliminar',
        cancelButtonText: 'Cancelar',
    }).then(function(result) {
        if (result.isConfirmed) {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = url;
            var csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '<?= csrf_token() ?>';
            csrf.value = '<?= csrf_hash() ?>';
            form.appendChild(csrf);
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
