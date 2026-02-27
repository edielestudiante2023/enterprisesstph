<div class="container-fluid px-3">

    <?php if (session()->getFlashdata('msg')): ?>
    <div class="alert alert-success mt-2" style="font-size:14px;"><?= session()->getFlashdata('msg') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger mt-2" style="font-size:14px;"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <h5 class="mt-2 mb-3"><i class="fas fa-running"></i> <?= esc($title) ?></h5>

    <!-- Filtro cliente -->
    <div class="mb-3">
        <select id="filtroCliente" class="form-select" style="font-size:14px;">
            <option value="">Todos los clientes</option>
        </select>
    </div>

    <!-- Listado -->
    <div id="listaEvaluaciones">
    <?php if (empty($evaluaciones)): ?>
        <div class="text-center text-muted py-4">
            <i class="fas fa-running" style="font-size:40px; opacity:0.3;"></i>
            <p class="mt-2">No hay evaluaciones de simulacro</p>
        </div>
    <?php else: ?>
        <?php foreach ($evaluaciones as $ev): ?>
        <div class="card card-inspeccion mb-2 ev-card" data-cliente="<?= esc($ev['nombre_cliente'] ?? '') ?>">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div style="flex:1;">
                        <strong style="font-size:13px;"><?= esc($ev['nombre_cliente'] ?? 'Sin cliente') ?></strong>
                        <div class="text-muted" style="font-size:12px;">
                            <?= date('d/m/Y', strtotime($ev['fecha'])) ?>
                            <?php if (!empty($ev['evento_simulado'])): ?>
                                &middot; <?= esc($ev['evento_simulado']) ?>
                            <?php endif; ?>
                            <?php if (!empty($ev['nombre_brigadista_lider'])): ?>
                                &middot; <?= esc($ev['nombre_brigadista_lider']) ?>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($ev['evaluacion_cuantitativa'])): ?>
                        <div style="font-size:12px;">
                            <span class="badge bg-info"><?= esc($ev['evaluacion_cuantitativa']) ?></span>
                            <?php if (!empty($ev['tiempo_total'])): ?>
                                <span class="text-muted ms-1"><i class="fas fa-stopwatch"></i> <?= esc($ev['tiempo_total']) ?></span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <span class="badge badge-<?= esc($ev['estado']) ?>" style="font-size:11px;">
                        <?= $ev['estado'] === 'completo' ? 'Completo' : 'Borrador' ?>
                    </span>
                </div>
                <div class="mt-2 d-flex gap-1 flex-wrap">
                    <a href="/inspecciones/simulacro/view/<?= $ev['id'] ?>" class="btn btn-sm btn-outline-dark">
                        <i class="fas fa-eye"></i> Ver
                    </a>
                    <?php if ($ev['estado'] === 'completo'): ?>
                        <a href="/inspecciones/simulacro/pdf/<?= $ev['id'] ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                            <i class="fas fa-file-pdf"></i> PDF
                        </a>
                    <?php endif; ?>
                    <?php if ($ev['estado'] === 'borrador'): ?>
                        <form action="/inspecciones/simulacro/finalizar/<?= $ev['id'] ?>" method="post" class="d-inline">
                            <button type="submit" class="btn btn-sm btn-outline-success">
                                <i class="fas fa-check"></i> Finalizar
                            </button>
                        </form>
                    <?php endif; ?>

                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete-ev" data-id="<?= $ev['id'] ?>" data-nombre="<?= esc($ev['nombre_cliente'] ?? '') ?>">
                            <i class="fas fa-trash"></i>
                        </button>
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
        document.querySelectorAll('.ev-card').forEach(card => {
            const cliente = card.dataset.cliente.toLowerCase();
            card.style.display = (!val || cliente.includes(val)) ? '' : 'none';
        });
    });

    // Eliminar con SweetAlert
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-delete-ev');
        if (!btn) return;
        Swal.fire({
            title: 'Eliminar evaluacion?',
            html: 'Se eliminara la evaluacion de <strong>' + btn.dataset.nombre + '</strong>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Si, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545',
        }).then(result => {
            if (result.isConfirmed) {
                window.location.href = '/inspecciones/simulacro/delete/' + btn.dataset.id;
            }
        });
    });
});
</script>
