<div class="container-fluid px-3">
    <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
        <h6 class="mb-0"><i class="fas fa-calendar-check me-1"></i> Agendamientos</h6>
        <a href="<?= base_url('/inspecciones/agendamiento/create') ?>" class="btn btn-sm btn-pwa-primary" style="width:auto; padding: 8px 16px;">
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

    <!-- Toggle vista -->
    <div class="btn-group btn-group-sm w-100 mb-3" role="group">
        <button type="button" class="btn btn-dark active" id="btnVistaConsultores">
            <i class="fas fa-users me-1"></i> Por consultor
        </button>
        <button type="button" class="btn btn-outline-dark" id="btnVistaTodos">
            <i class="fas fa-list me-1"></i> Todas las visitas
        </button>
    </div>

    <!-- ===== VISTA: POR CONSULTOR ===== -->
    <div id="vistaConsultores">
        <!-- Consultores Internos -->
        <?php if (!empty($consultoresInternos)): ?>
        <div class="mb-3">
            <div class="d-flex align-items-center mb-2">
                <i class="fas fa-user-tie me-2" style="color: var(--gold-primary);"></i>
                <span style="font-size:14px; font-weight:600; color:#555;">Consultores</span>
            </div>
            <?php foreach ($consultoresInternos as $c): ?>
            <a href="<?= base_url('/inspecciones/agendamiento/anios?tipo=interno&id=' . $c['id_consultor']) ?>"
               class="card card-inspeccion mb-2" style="text-decoration:none; color:inherit; display:block;">
                <div class="card-body py-3 px-3">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <?php if (!empty($c['foto_consultor'])): ?>
                                <img src="<?= base_url('/uploads/' . $c['foto_consultor']) ?>" class="rounded-circle" width="45" height="45" style="object-fit:cover;">
                            <?php else: ?>
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:45px;height:45px;background:var(--primary-dark);">
                                    <i class="fas fa-user-tie" style="color:var(--gold-primary);font-size:18px;"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div style="flex:1;">
                            <strong style="font-size:15px;"><?= esc($c['nombre_consultor']) ?></strong>
                            <div class="d-flex gap-2 mt-1 flex-wrap" style="font-size:12px;">
                                <span class="badge bg-dark"><?= $c['total'] ?> total</span>
                                <?php if ($c['pendientes'] > 0): ?>
                                    <span class="badge bg-warning text-dark"><?= $c['pendientes'] ?> pend.</span>
                                <?php endif; ?>
                                <?php if ($c['confirmados'] > 0): ?>
                                    <span class="badge bg-success"><?= $c['confirmados'] ?> conf.</span>
                                <?php endif; ?>
                                <?php if ($c['completados'] > 0): ?>
                                    <span class="badge bg-primary"><?= $c['completados'] ?> comp.</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Consultores Externos -->
        <?php if (!empty($consultoresExternos)): ?>
        <div class="mb-3">
            <div class="d-flex align-items-center mb-2">
                <i class="fas fa-user-shield me-2" style="color: var(--gold-primary);"></i>
                <span style="font-size:14px; font-weight:600; color:#555;">Consultores Externos</span>
            </div>
            <?php foreach ($consultoresExternos as $c): ?>
            <a href="<?= base_url('/inspecciones/agendamiento/anios?tipo=externo&nombre=' . urlencode($c['consultor_externo'])) ?>"
               class="card card-inspeccion mb-2" style="text-decoration:none; color:inherit; display:block;">
                <div class="card-body py-3 px-3">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:45px;height:45px;background:#2c3e50;">
                                <i class="fas fa-user-shield" style="color:#e0c97f;font-size:18px;"></i>
                            </div>
                        </div>
                        <div style="flex:1;">
                            <strong style="font-size:15px;"><?= esc($c['consultor_externo']) ?></strong>
                            <div class="d-flex gap-2 mt-1 flex-wrap" style="font-size:12px;">
                                <span class="badge bg-dark"><?= $c['total'] ?> total</span>
                                <?php if ($c['pendientes'] > 0): ?>
                                    <span class="badge bg-warning text-dark"><?= $c['pendientes'] ?> pend.</span>
                                <?php endif; ?>
                                <?php if ($c['confirmados'] > 0): ?>
                                    <span class="badge bg-success"><?= $c['confirmados'] ?> conf.</span>
                                <?php endif; ?>
                                <?php if ($c['completados'] > 0): ?>
                                    <span class="badge bg-primary"><?= $c['completados'] ?> comp.</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (empty($consultoresInternos) && empty($consultoresExternos)): ?>
            <div class="text-center text-muted py-5">
                <i class="fas fa-calendar-alt fa-3x mb-3" style="opacity:0.3;"></i>
                <p>No hay agendamientos</p>
                <a href="<?= base_url('/inspecciones/agendamiento/create') ?>" class="btn btn-pwa-primary" style="width:auto; padding: 8px 24px;">
                    Crear primer agendamiento
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- ===== VISTA: TODAS LAS VISITAS ===== -->
    <div id="vistaTodos" style="display:none;">

        <!-- Filtro por cliente -->
        <div class="mb-2">
            <select id="filterCliente" class="form-select form-select-sm">
                <option value="">Todos los clientes</option>
                <?php foreach ($clientesFlat as $nombre): ?>
                    <option value="<?= strtolower(esc($nombre)) ?>"><?= esc($nombre) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Pills de mes (dinámicos) -->
        <div class="mb-2">
            <div class="d-flex gap-1 flex-wrap" id="mesPills">
                <button type="button" class="btn btn-sm btn-dark filter-mes active" data-mes="">Todos</button>
            </div>
        </div>

        <!-- Rango de fechas -->
        <div class="mb-2 d-flex gap-2 align-items-center">
            <div style="flex:1;">
                <input type="date" id="filterDesde" class="form-control form-control-sm" placeholder="Desde">
            </div>
            <span style="font-size:12px;color:#999;">→</span>
            <div style="flex:1;">
                <input type="date" id="filterHasta" class="form-control form-control-sm" placeholder="Hasta">
            </div>
            <button type="button" id="btnLimpiarFechas" class="btn btn-sm btn-outline-secondary" title="Limpiar fechas">
                <i class="fas fa-times"></i>
            </button>
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

        <!-- Contador -->
        <div id="resultadosCount" class="text-muted mb-2" style="font-size:12px;"></div>

        <!-- Cards de visitas -->
        <?php if (empty($agendamientos)): ?>
            <div class="text-center text-muted py-5">
                <i class="fas fa-calendar-alt fa-3x mb-3" style="opacity:0.3;"></i>
                <p>No hay agendamientos</p>
            </div>
        <?php else: ?>
        <div id="agendamientosList">
        <?php foreach ($agendamientos as $ag): ?>
            <div class="card card-inspeccion <?= esc($ag['estado']) ?> ag-item mb-2"
                 data-cliente="<?= strtolower(esc($ag['nombre_cliente'] ?? '')) ?>"
                 data-estado="<?= esc($ag['estado']) ?>"
                 data-fecha="<?= esc($ag['fecha_visita']) ?>"
                 data-mes="<?= date('Y-m', strtotime($ag['fecha_visita'])) ?>">
                <div class="card-body py-3 px-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div style="flex:1;">
                            <strong><?= esc($ag['nombre_cliente'] ?? 'Sin cliente') ?></strong>
                            <?php if (!empty($ag['nombre_consultor'])): ?>
                                <div style="font-size:12px; color:#888;">
                                    <i class="fas fa-user-tie me-1"></i><?= esc($ag['nombre_consultor']) ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($ag['consultor_externo'])): ?>
                                <div style="font-size:12px; color:#888;">
                                    <i class="fas fa-user-shield me-1"></i><?= esc($ag['consultor_externo']) ?>
                                </div>
                            <?php endif; ?>
                            <div class="text-muted" style="font-size: 13px;">
                                <i class="fas fa-calendar me-1"></i>
                                <?= date('d/m/Y', strtotime($ag['fecha_visita'])) ?>
                                - <?= date('g:i A', strtotime($ag['hora_visita'])) ?>
                            </div>
                            <div style="font-size: 12px; color: #888; margin-top: 2px;">
                                <i class="fas fa-sync-alt me-1"></i>
                                <?= ucfirst(esc($ag['frecuencia'])) ?>
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
                        <a href="<?= base_url('/inspecciones/agendamiento/edit/') ?><?= $ag['id'] ?>" class="btn btn-sm btn-outline-dark">
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
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnConsultores = document.getElementById('btnVistaConsultores');
    const btnTodos       = document.getElementById('btnVistaTodos');
    const vistaC         = document.getElementById('vistaConsultores');
    const vistaT         = document.getElementById('vistaTodos');

    btnConsultores.addEventListener('click', function() {
        btnConsultores.classList.add('active');
        btnConsultores.classList.remove('btn-outline-dark');
        btnConsultores.classList.add('btn-dark');
        btnTodos.classList.remove('active');
        btnTodos.classList.add('btn-outline-dark');
        btnTodos.classList.remove('btn-dark');
        vistaC.style.display = '';
        vistaT.style.display = 'none';
    });

    btnTodos.addEventListener('click', function() {
        btnTodos.classList.add('active');
        btnTodos.classList.remove('btn-outline-dark');
        btnTodos.classList.add('btn-dark');
        btnConsultores.classList.remove('active');
        btnConsultores.classList.add('btn-outline-dark');
        btnConsultores.classList.remove('btn-dark');
        vistaC.style.display = 'none';
        vistaT.style.display = '';
        initFilters();
    });

    // Inicializar Select2 para cliente
    function initFilters() {
        if (!$('#filterCliente').data('select2')) {
            $('#filterCliente').select2({ placeholder: 'Todos los clientes', allowClear: true, width: '100%' });
            $('#filterCliente').on('change', filterItems);
        }

        // Generar pills de mes desde los datos reales
        if (document.getElementById('mesPills').querySelectorAll('.filter-mes').length === 1) {
            const mesesVistos = {};
            const nombresMes = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
            document.querySelectorAll('.ag-item').forEach(item => {
                const m = item.dataset.mes;
                if (m && !mesesVistos[m]) {
                    mesesVistos[m] = true;
                    const [anio, mes] = m.split('-');
                    const label = nombresMes[parseInt(mes) - 1] + ' ' + anio;
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'btn btn-sm btn-outline-dark filter-mes';
                    btn.dataset.mes = m;
                    btn.textContent = label;
                    document.getElementById('mesPills').appendChild(btn);
                }
            });

            document.getElementById('mesPills').addEventListener('click', function(e) {
                const btn = e.target.closest('.filter-mes');
                if (!btn) return;
                document.querySelectorAll('.filter-mes').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                filterItems();
            });

            document.querySelectorAll('.filter-estado').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.filter-estado').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    filterItems();
                });
            });

            document.getElementById('filterDesde').addEventListener('change', filterItems);
            document.getElementById('filterHasta').addEventListener('change', filterItems);
            document.getElementById('btnLimpiarFechas').addEventListener('click', function() {
                document.getElementById('filterDesde').value = '';
                document.getElementById('filterHasta').value = '';
                filterItems();
            });
        }

        filterItems();
    }
});

function filterItems() {
    const clienteFilter = (document.getElementById('filterCliente').value || '').toLowerCase();
    const estadoFilter  = document.querySelector('.filter-estado.active')?.dataset.estado || '';
    const mesFilter     = document.querySelector('.filter-mes.active')?.dataset.mes || '';
    const desde         = document.getElementById('filterDesde').value;
    const hasta         = document.getElementById('filterHasta').value;

    let visibles = 0;
    document.querySelectorAll('.ag-item').forEach(item => {
        const matchCliente = !clienteFilter || item.dataset.cliente === clienteFilter;
        const matchEstado  = !estadoFilter  || item.dataset.estado === estadoFilter;
        const matchMes     = !mesFilter     || item.dataset.mes === mesFilter;
        const fecha        = item.dataset.fecha || '';
        const matchDesde   = !desde || fecha >= desde;
        const matchHasta   = !hasta || fecha <= hasta;

        const visible = matchCliente && matchEstado && matchMes && matchDesde && matchHasta;
        item.style.display = visible ? '' : 'none';
        if (visible) visibles++;
    });

    const total = document.querySelectorAll('.ag-item').length;
    const counter = document.getElementById('resultadosCount');
    if (counter) {
        counter.textContent = visibles === total
            ? `${total} visita${total !== 1 ? 's' : ''}`
            : `${visibles} de ${total} visita${total !== 1 ? 's' : ''}`;
    }
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
