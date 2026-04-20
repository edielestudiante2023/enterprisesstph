<?php /** @var array $registros */ ?>
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
                <i class="fas fa-file-invoice"></i> Planilla Seg. Social
            </h6>
            <small class="text-muted"><?= count($registros) ?> registros</small>
        </div>
        <a href="<?= base_url('/inspecciones/planilla-seg-social/create') ?>" class="btn btn-pwa btn-pwa-primary" style="font-size:14px; padding:8px 16px;">
            <i class="fas fa-plus"></i> Registrar
        </a>
    </div>

    <?php if (empty($registros)): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas fa-file-invoice" style="font-size:48px; opacity:.3;"></i>
        <p class="mt-3">No hay planillas registradas.</p>
        <a href="<?= base_url('/inspecciones/planilla-seg-social/create') ?>" class="btn btn-pwa btn-pwa-primary mt-2">
            <i class="fas fa-plus"></i> Registrar planilla
        </a>
    </div>
    <?php else: ?>

    <?php foreach ($registros as $reg): ?>
    <div class="card mb-2" style="border-left: 4px solid #6c757d;">
        <div class="card-body py-2 px-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong style="font-size:14px;"><?= esc($reg['nombre_cliente'] ?? 'Sin cliente') ?></strong>
                    <div class="text-muted" style="font-size:12px;">
                        Período: <?= esc($reg['periodo']) ?>
                        <?php if (!empty($reg['observaciones'])): ?>
                            &middot; <?= esc(mb_strimwidth($reg['observaciones'], 0, 40, '…')) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="d-flex gap-1 align-items-center">
                    <?php if (!empty($reg['archivo'])): ?>
                    <a href="<?= base_url($reg['archivo']) ?>" target="_blank" class="btn btn-sm btn-outline-dark" title="Ver planilla">
                        <i class="fas fa-file-alt"></i>
                    </a>
                    <?php endif; ?>
                    <a href="<?= base_url('/inspecciones/planilla-seg-social/edit/' . $reg['id']) ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                        <i class="fas fa-pencil-alt"></i>
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-danger" title="Eliminar"
                        onclick="confirmarEliminar('<?= base_url('/inspecciones/planilla-seg-social/delete/' . $reg['id']) ?>')">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <?php endif; ?>
</div>

<script>
function confirmarEliminar(url) {
    Swal.fire({
        title: '¿Eliminar planilla?',
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
