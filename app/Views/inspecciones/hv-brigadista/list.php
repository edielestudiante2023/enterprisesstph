<div class="container-fluid px-3">

    <?php if (session()->getFlashdata('msg')): ?>
    <div class="alert alert-success mt-2" style="font-size:14px;"><?= session()->getFlashdata('msg') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger mt-2" style="font-size:14px;"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <h5 class="mt-2 mb-3"><i class="fas fa-id-card-alt"></i> <?= esc($title) ?></h5>

    <!-- Filtro cliente -->
    <div class="mb-3">
        <select id="filtroCliente" class="form-select" style="font-size:14px;">
            <option value="">Todos los clientes</option>
        </select>
    </div>

    <!-- Listado -->
    <div id="listaRegistros">
    <?php if (empty($registros)): ?>
        <div class="text-center text-muted py-4">
            <i class="fas fa-id-card-alt" style="font-size:40px; opacity:0.3;"></i>
            <p class="mt-2">No hay hojas de vida registradas</p>
        </div>
    <?php else: ?>
        <?php foreach ($registros as $hv): ?>
        <div class="card card-inspeccion mb-2 hv-card" data-cliente="<?= esc($hv['nombre_cliente'] ?? '') ?>">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div style="flex:1;">
                        <strong style="font-size:13px;"><?= esc($hv['nombre_completo'] ?? 'Sin nombre') ?></strong>
                        <div class="text-muted" style="font-size:12px;">
                            CC <?= esc($hv['documento_identidad'] ?? '') ?>
                            &middot; <?= esc($hv['nombre_cliente'] ?? 'Sin cliente') ?>
                        </div>
                        <div class="text-muted" style="font-size:11px;">
                            <?= date('d/m/Y', strtotime($hv['created_at'])) ?>
                            <?php if (!empty($hv['email'])): ?>
                                &middot; <?= esc($hv['email']) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <span class="badge badge-<?= esc($hv['estado']) ?>" style="font-size:11px;">
                        <?= $hv['estado'] === 'completo' ? 'Completo' : 'Borrador' ?>
                    </span>
                </div>
                <div class="mt-2 d-flex gap-1 flex-wrap">
                    <a href="/inspecciones/hv-brigadista/view/<?= $hv['id'] ?>" class="btn btn-sm btn-outline-dark">
                        <i class="fas fa-eye"></i> Ver
                    </a>
                    <?php if ($hv['estado'] === 'completo'): ?>
                        <a href="/inspecciones/hv-brigadista/pdf/<?= $hv['id'] ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                            <i class="fas fa-file-pdf"></i> PDF
                        </a>
                    <?php endif; ?>
                    <?php if ($hv['estado'] === 'borrador'): ?>
                        <form action="/inspecciones/hv-brigadista/finalizar/<?= $hv['id'] ?>" method="post" class="d-inline">
                            <button type="submit" class="btn btn-sm btn-outline-success">
                                <i class="fas fa-check"></i> Finalizar
                            </button>
                        </form>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete-hv" data-id="<?= $hv['id'] ?>" data-nombre="<?= esc($hv['nombre_completo'] ?? '') ?>">
                            <i class="fas fa-trash"></i>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select2 filtro
    $.ajax({
        url: '/inspecciones/api/clientes',
        dataType: 'json',
        success: function(data) {
            const select = document.getElementById('filtroCliente');
            data.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.nombre_cliente;
                opt.textContent = c.nombre_cliente;
                select.appendChild(opt);
            });
            $('#filtroCliente').select2({ placeholder: 'Todos los clientes', allowClear: true, width: '100%' });
        }
    });

    // Filtrar
    $('#filtroCliente').on('change', function() {
        const val = this.value.toLowerCase();
        document.querySelectorAll('.hv-card').forEach(card => {
            const cliente = card.dataset.cliente.toLowerCase();
            card.style.display = (!val || cliente.includes(val)) ? '' : 'none';
        });
    });

    // Eliminar con SweetAlert
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-delete-hv');
        if (!btn) return;
        Swal.fire({
            title: 'Eliminar HV?',
            html: 'Se eliminara la hoja de vida de <strong>' + btn.dataset.nombre + '</strong>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Si, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545',
        }).then(result => {
            if (result.isConfirmed) {
                window.location.href = '/inspecciones/hv-brigadista/delete/' + btn.dataset.id;
            }
        });
    });
});
</script>
