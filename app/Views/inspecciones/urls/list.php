<?php if (session()->getFlashdata('msg')): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= session()->getFlashdata('msg') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= session()->getFlashdata('error') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0"><i class="fas fa-link me-2"></i>Accesos Rápidos</h5>
</div>

<a href="/inspecciones/urls/create" class="btn btn-pwa btn-pwa-primary mb-3">
    <i class="fas fa-plus me-2"></i>Nuevo Acceso
</a>

<?php if (empty($grouped)): ?>
    <div class="text-center text-muted py-4">
        <i class="fas fa-inbox fa-3x mb-2"></i>
        <p>No hay accesos rápidos registrados.</p>
    </div>
<?php else: ?>
    <?php
    $colores = [
        'AGENDA CONSULTOR' => '#1565c0',
        'BRIGADISTA'       => '#c62828',
        'INDUCCION'        => '#6a1b9a',
        'KPI'              => '#00695c',
        'PROCEDIMIENTOS'   => '#ef6c00',
        'SIMULACRO'        => '#37474f',
    ];
    ?>
    <?php foreach ($grouped as $tipo => $urls): ?>
    <div class="card mb-3">
        <div class="card-header py-2 px-3 text-white" style="background: <?= $colores[$tipo] ?? '#1c2437' ?>; font-size: 13px;">
            <i class="fas fa-folder-open me-1"></i> <?= esc($tipo) ?>
            <span class="badge bg-light text-dark ms-1" style="font-size: 10px;"><?= count($urls) ?></span>
        </div>
        <div class="card-body p-0">
            <?php foreach ($urls as $u): ?>
            <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom" style="font-size: 13px;">
                <div class="flex-grow-1 me-2" style="min-width: 0;">
                    <a href="<?= esc($u['url']) ?>" target="_blank" class="text-decoration-none fw-bold" style="color: <?= $colores[$tipo] ?? '#1c2437' ?>;">
                        <i class="fas fa-external-link-alt me-1" style="font-size: 10px;"></i><?= esc($u['nombre']) ?>
                    </a>
                </div>
                <div class="d-flex gap-1 flex-shrink-0">
                    <a href="/inspecciones/urls/edit/<?= $u['id'] ?>" class="btn btn-sm btn-outline-dark" style="font-size: 11px; padding: 1px 6px;">
                        <i class="fas fa-edit"></i>
                    </a>
                    <a href="#" class="btn btn-sm btn-outline-danger btn-delete" data-id="<?= $u['id'] ?>" data-nombre="<?= esc($u['nombre']) ?>" style="font-size: 11px; padding: 1px 6px;">
                        <i class="fas fa-trash"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<script>
document.addEventListener('click', function(e) {
    var btn = e.target.closest('.btn-delete');
    if (!btn) return;
    e.preventDefault();
    var id = btn.dataset.id;
    var nombre = btn.dataset.nombre;
    Swal.fire({
        title: 'Eliminar acceso?',
        html: '<strong>' + nombre + '</strong><br>Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then(result => {
        if (result.isConfirmed) {
            window.location.href = '/inspecciones/urls/delete/' + id;
        }
    });
});
</script>
