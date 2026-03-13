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
    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i><?= esc($title) ?></h5>
</div>

<div class="mb-3">
    <select id="filtroCliente" class="form-select">
        <option value="">Todos los clientes</option>
    </select>
</div>

<a href="<?= base_url('/inspecciones/' . $slug . '/create') ?>" class="btn btn-pwa btn-pwa-primary mb-3">
    <i class="fas fa-plus me-2"></i>Nuevo KPI
</a>

<div id="listaInspecciones">
<?php if (empty($grupos)): ?>
    <div class="text-center text-muted py-4">
        <i class="fas fa-inbox fa-3x mb-2"></i>
        <p>No hay KPIs registrados.</p>
    </div>
<?php else: ?>
    <?php foreach ($grupos as $grupo): ?>
    <div class="card card-inspeccion mb-2 card-filtrable" data-cliente="<?= esc($grupo['nombre_cliente'] ?? '') ?>">
        <div class="card-body py-3 px-3">
            <div class="d-flex justify-content-between align-items-start">
                <div style="flex:1;">
                    <strong>
                        <?php if ($grupo['estado'] === 'completo'): ?>
                            <i class="fas fa-check-circle text-success"></i>
                        <?php else: ?>
                            <i class="fas fa-edit text-warning"></i>
                        <?php endif; ?>
                        <?= esc($grupo['nombre_cliente'] ?? 'Sin cliente') ?>
                    </strong>
                    <div class="text-muted" style="font-size: 13px;">
                        <?= date('d/m/Y', strtotime($grupo['fecha_inspeccion'])) ?>
                        &middot;
                        <span class="badge bg-<?= $grupo['estado'] === 'completo' ? 'success' : 'warning text-dark' ?>" style="font-size: 11px;">
                            <?= $grupo['estado'] === 'completo' ? 'Completo' : 'Borrador' ?>
                        </span>
                        &middot;
                        <?= count($grupo['indicadores']) ?>/<?= $totalIndicadores ?> indicador(es)
                    </div>
                    <?php foreach ($grupo['indicadores'] as $ind): ?>
                    <div style="font-size: 12px; margin-top: 3px; padding-left: 8px; border-left: 3px solid <?= ($ind['calificacion_cualitativa'] ?? '') === 'CUMPLE' ? '#28a745' : '#dc3545' ?>;">
                        <strong><?= esc(mb_substr($ind['indicador'], 0, 50)) ?></strong>
                        <?php if (!empty($ind['cumplimiento'])): ?>
                            — <?= number_format($ind['cumplimiento'], 1) ?>%
                            <span class="badge bg-<?= ($ind['calificacion_cualitativa'] ?? '') === 'CUMPLE' ? 'success' : 'danger' ?>" style="font-size: 10px;">
                                <?= esc($ind['calificacion_cualitativa'] ?? '') ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="mt-2 d-flex gap-2 flex-wrap">
                <?php foreach ($grupo['indicadores'] as $ind): ?>
                    <a href="<?= base_url('/inspecciones/' . $slug . '/edit/' . $ind['id']) ?>" class="btn btn-sm btn-outline-dark" title="Editar: <?= esc(mb_substr($ind['indicador'], 0, 30)) ?>">
                        <i class="fas fa-edit"></i> <?= esc(mb_substr($ind['indicador'], 0, 20)) ?>...
                    </a>
                <?php endforeach; ?>
                <?php if (count($grupo['indicadores']) < $totalIndicadores && $grupo['estado'] !== 'completo'): ?>
                    <a href="<?= base_url('/inspecciones/' . $slug . '/create/' . $grupo['id_cliente']) ?>" class="btn btn-sm btn-outline-success" title="Agregar indicador faltante">
                        <i class="fas fa-plus"></i> Agregar indicador
                    </a>
                <?php endif; ?>
                <a href="#" class="btn btn-sm btn-outline-danger btn-delete" data-id="<?= $grupo['first_id'] ?>">
                    <i class="fas fa-trash"></i>
                </a>
                <?php if ($grupo['estado'] === 'completo'): ?>
                    <a href="<?= base_url('/inspecciones/' . $slug . '/view/' . $grupo['first_id']) ?>" class="btn btn-sm btn-outline-dark">
                        <i class="fas fa-eye"></i> Ver
                    </a>
                    <a href="<?= base_url('/inspecciones/' . $slug . '/pdf/' . $grupo['first_id']) ?>" class="btn btn-sm btn-outline-primary" target="_blank">
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
document.addEventListener('DOMContentLoaded', function() {
    $.ajax({
        url: '<?= base_url('/inspecciones/api/clientes') ?>',
        dataType: 'json',
        success: function(data) {
            var sel = document.getElementById('filtroCliente');
            data.forEach(function(c) {
                var opt = document.createElement('option');
                opt.value = c.nombre_cliente;
                opt.textContent = c.nombre_cliente;
                sel.appendChild(opt);
            });
            $('#filtroCliente').select2({ placeholder: 'Todos los clientes', allowClear: true, width: '100%' });
        }
    });

    $('#filtroCliente').on('change', function() {
        var val = this.value.toLowerCase();
        document.querySelectorAll('.card-filtrable').forEach(function(card) {
            var cliente = card.dataset.cliente.toLowerCase();
            card.style.display = (!val || cliente.includes(val)) ? '' : 'none';
        });
    });

    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.btn-delete');
        if (!btn) return;
        e.preventDefault();
        Swal.fire({
            title: 'Eliminar KPI?',
            text: 'Esta accion no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Si, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (result.isConfirmed) {
                window.location.href = '<?= base_url('/inspecciones/' . $slug . '/delete/') ?>' + btn.dataset.id;
            }
        });
    });
});
</script>
