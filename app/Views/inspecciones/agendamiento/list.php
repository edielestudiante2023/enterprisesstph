<div class="container-fluid px-3">
    <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
        <h6 class="mb-0">Agendamientos</h6>
        <a href="/inspecciones/agendamiento/create" class="btn btn-sm btn-pwa-primary" style="width:auto; padding: 8px 16px;">
            <i class="fas fa-plus"></i> Nuevo
        </a>
    </div>

    <!-- Alertas -->
    <?php if (session()->getFlashdata('msg')): ?>
        <div class="alert alert-success alert-dismissible fade show py-2" style="font-size:13px;">
            <?= session()->getFlashdata('msg') ?>
            <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show py-2" style="font-size:13px;">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Filtro por cliente -->
    <div class="mb-2">
        <select id="filterCliente" class="form-select form-select-sm">
            <option value="">Todos los clientes</option>
            <?php foreach ($clientes as $cli): ?>
                <option value="<?= strtolower(esc($cli['nombre_cliente'])) ?>"><?= esc($cli['nombre_cliente']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Filtro por estado -->
    <div class="mb-3">
        <div class="btn-group btn-group-sm w-100" role="group">
            <button type="button" class="btn btn-outline-dark filter-estado active" data-estado="">Todos</button>
            <button type="button" class="btn btn-outline-warning filter-estado" data-estado="pendiente">Pendiente</button>
            <button type="button" class="btn btn-outline-success filter-estado" data-estado="confirmado">Confirmado</button>
            <button type="button" class="btn btn-outline-secondary filter-estado" data-estado="cancelado">Cancelado</button>
        </div>
    </div>

    <!-- Lista -->
    <?php if (empty($agendamientos)): ?>
        <div class="text-center text-muted py-5">
            <i class="fas fa-calendar-alt fa-3x mb-3" style="opacity:0.3;"></i>
            <p>No hay agendamientos</p>
            <a href="/inspecciones/agendamiento/create" class="btn btn-pwa-primary" style="width:auto; padding: 8px 24px;">
                Crear primer agendamiento
            </a>
        </div>
    <?php else: ?>
        <div id="agendamientosList">
        <?php foreach ($agendamientos as $ag): ?>
            <div class="card card-inspeccion <?= esc($ag['estado']) ?> ag-item mb-2"
                 data-cliente="<?= strtolower(esc($ag['nombre_cliente'] ?? '')) ?>"
                 data-estado="<?= esc($ag['estado']) ?>">
                <div class="card-body py-3 px-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div style="flex:1;">
                            <strong><?= esc($ag['nombre_cliente'] ?? 'Sin cliente') ?></strong>
                            <div class="text-muted" style="font-size: 13px;">
                                <i class="fas fa-calendar me-1"></i>
                                <?= date('d/m/Y', strtotime($ag['fecha_visita'])) ?>
                                - <?= date('g:i A', strtotime($ag['hora_visita'])) ?>
                            </div>
                            <div style="font-size: 12px; color: #888; margin-top: 2px;">
                                <i class="fas fa-sync-alt me-1"></i>
                                <?= ucfirst(esc($ag['frecuencia'])) ?>
                                <?php if ($ag['ultima_visita']): ?>
                                    &middot; Última: <?= date('d/m/Y', strtotime($ag['ultima_visita'])) ?>
                                <?php else: ?>
                                    &middot; Sin visitas previas
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-<?php
                                switch ($ag['estado']) {
                                    case 'pendiente': echo 'warning text-dark'; break;
                                    case 'confirmado': echo 'success'; break;
                                    case 'completado': echo 'primary'; break;
                                    case 'cancelado': echo 'secondary'; break;
                                }
                            ?>">
                                <?= ucfirst($ag['estado']) ?>
                            </span>
                            <?php if ($ag['email_enviado']): ?>
                                <div style="font-size:11px;" class="text-success mt-1"><i class="fas fa-envelope-circle-check"></i> Enviado</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!empty($ag['preparacion_cliente'])): ?>
                        <div style="font-size: 12px; color: #555; margin-top: 4px; background: #f0f0f0; padding: 4px 8px; border-radius: 4px;">
                            <i class="fas fa-clipboard-check me-1"></i> <?= esc($ag['preparacion_cliente']) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($ag['estado'] !== 'cancelado' && $ag['estado'] !== 'completado'): ?>
                    <div class="mt-2 d-flex gap-2 flex-wrap">
                        <a href="/inspecciones/agendamiento/edit/<?= $ag['id'] ?>" class="btn btn-sm btn-outline-dark">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <button class="btn btn-sm btn-outline-success btn-send-invite" data-id="<?= $ag['id'] ?>">
                            <i class="fas fa-paper-plane"></i> <?= $ag['email_enviado'] ? 'Reenviar' : 'Enviar' ?>
                        </button>
                        <button class="btn btn-sm btn-outline-danger btn-cancel-ag" data-id="<?= $ag['id'] ?>">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select2 para filtro de cliente
    $('#filterCliente').select2({ placeholder: 'Todos los clientes', allowClear: true, width: '100%' });

    // Filtro por cliente
    $('#filterCliente').on('change', function() {
        filterItems();
    });

    // Filtro por estado
    document.querySelectorAll('.filter-estado').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-estado').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            filterItems();
        });
    });
});

function filterItems() {
    const clienteFilter = document.getElementById('filterCliente').value.toLowerCase();
    const estadoFilter = document.querySelector('.filter-estado.active')?.dataset.estado || '';

    document.querySelectorAll('.ag-item').forEach(item => {
        const matchCliente = !clienteFilter || item.dataset.cliente === clienteFilter;
        const matchEstado = !estadoFilter || item.dataset.estado === estadoFilter;
        item.style.display = (matchCliente && matchEstado) ? '' : 'none';
    });
}

// Enviar invitación
document.querySelectorAll('.btn-send-invite').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const btnEl = this;
        btnEl.disabled = true;
        btnEl.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        fetch('/inspecciones/agendamiento/send-invitation/' + id, { method: 'POST', headers: {'X-Requested-With': 'XMLHttpRequest', '<?= csrf_token() ?>': '<?= csrf_hash() ?>'} })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({icon:'success', title:'Enviado', text: data.message, timer: 2000, showConfirmButton: false});
                    setTimeout(() => location.reload(), 2000);
                } else {
                    Swal.fire({icon:'error', title:'Error', text: data.error});
                    btnEl.disabled = false;
                    btnEl.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar';
                }
            })
            .catch(() => {
                Swal.fire({icon:'error', title:'Error', text:'Error de conexión'});
                btnEl.disabled = false;
                btnEl.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar';
            });
    });
});

// Cancelar agendamiento
document.querySelectorAll('.btn-cancel-ag').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        Swal.fire({
            title: 'Cancelar agendamiento?',
            text: 'Se notificará al cliente y consultor',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#c0392b',
            confirmButtonText: 'Sí, cancelar',
            cancelButtonText: 'No'
        }).then(result => {
            if (result.isConfirmed) {
                fetch('/inspecciones/agendamiento/cancel/' + id, { method: 'POST', headers: {'X-Requested-With': 'XMLHttpRequest', '<?= csrf_token() ?>': '<?= csrf_hash() ?>'} })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({icon:'success', title:'Cancelado', timer: 1500, showConfirmButton: false});
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            Swal.fire({icon:'error', title:'Error', text: data.error});
                        }
                    });
            }
        });
    });
});
</script>
