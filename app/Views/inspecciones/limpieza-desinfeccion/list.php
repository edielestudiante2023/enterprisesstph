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
    <h5 class="mb-0"><i class="fas fa-broom me-2"></i>Programa Limpieza y Desinfección</h5>
</div>

<!-- Filtro por cliente -->
<div class="mb-3">
    <select id="filtroCliente" class="form-select">
        <option value="">Todos los clientes</option>
    </select>
</div>

<a href="/inspecciones/limpieza-desinfeccion/create" class="btn btn-pwa btn-pwa-primary mb-3">
    <i class="fas fa-plus me-2"></i>Nuevo Programa
</a>

<!-- Cards de inspecciones -->
<div id="listaInspecciones">
<?php if (empty($inspecciones)): ?>
    <div class="text-center text-muted py-4">
        <i class="fas fa-inbox fa-3x mb-2"></i>
        <p>No hay programas registrados.</p>
    </div>
<?php else: ?>
    <?php foreach ($inspecciones as $insp): ?>
    <div class="card card-inspeccion mb-2 card-filtrable" data-cliente="<?= esc($insp['id_cliente']) ?>">
        <div class="card-body py-3 px-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong>
                        <?php if ($insp['estado'] === 'completo'): ?>
                            <i class="fas fa-check-circle text-success"></i>
                        <?php else: ?>
                            <i class="fas fa-edit text-warning"></i>
                        <?php endif; ?>
                        <?= esc($insp['nombre_cliente'] ?? 'Sin cliente') ?>
                    </strong>
                    <div class="text-muted" style="font-size: 13px;">
                        <?= date('d/m/Y', strtotime($insp['fecha_programa'])) ?>
                        <?php if (!empty($insp['nombre_responsable'])): ?>
                            &middot; <?= esc($insp['nombre_responsable']) ?>
                        <?php endif; ?>
                        &middot;
                        <span class="badge bg-<?= $insp['estado'] === 'completo' ? 'success' : 'warning text-dark' ?>" style="font-size: 11px;">
                            <?= $insp['estado'] === 'completo' ? 'Completo' : 'Borrador' ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="mt-2 d-flex gap-2">
                <?php if ($insp['estado'] === 'borrador'): ?>
                    <a href="/inspecciones/limpieza-desinfeccion/edit/<?= $insp['id'] ?>" class="btn btn-sm btn-outline-dark">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <a href="#" class="btn btn-sm btn-outline-danger btn-delete" data-id="<?= $insp['id'] ?>">
                        <i class="fas fa-trash"></i>
                    </a>
                <?php else: ?>
                    <a href="/inspecciones/limpieza-desinfeccion/view/<?= $insp['id'] ?>" class="btn btn-sm btn-outline-dark">
                        <i class="fas fa-eye"></i> Ver
                    </a>
                    <a href="/inspecciones/limpieza-desinfeccion/pdf/<?= $insp['id'] ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                        <i class="fas fa-file-pdf"></i> PDF
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>
</div>

<script>
// Cargar clientes para filtro
fetch('/inspecciones/api/clientes')
    .then(r => r.json())
    .then(clientes => {
        var sel = document.getElementById('filtroCliente');
        clientes.forEach(c => {
            var opt = document.createElement('option');
            opt.value = c.id_cliente;
            opt.textContent = c.nombre_cliente;
            sel.appendChild(opt);
        });
    });

document.getElementById('filtroCliente').addEventListener('change', function() {
    var val = this.value;
    document.querySelectorAll('.card-filtrable').forEach(card => {
        card.style.display = (!val || card.dataset.cliente === val) ? '' : 'none';
    });
});

// Eliminar con SweetAlert
document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        var id = this.dataset.id;
        Swal.fire({
            title: 'Eliminar programa?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (result.isConfirmed) {
                window.location.href = '/inspecciones/limpieza-desinfeccion/delete/' + id;
            }
        });
    });
});
</script>
