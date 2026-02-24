<style>
    .filter-pills {
        display: flex;
        gap: 6px;
        overflow-x: auto;
        padding: 2px 0 8px;
        -webkit-overflow-scrolling: touch;
    }
    .filter-pills::-webkit-scrollbar { display: none; }
    .filter-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
        cursor: pointer;
        border: 2px solid #bd9751;
        background: white;
        color: #bd9751;
        transition: all .2s;
    }
    .filter-pill.active {
        background: #bd9751;
        color: white;
    }
    .filter-pill .pill-count {
        background: rgba(0,0,0,0.15);
        border-radius: 10px;
        padding: 0 6px;
        font-size: 11px;
        min-width: 18px;
        text-align: center;
    }
    .filter-pill.active .pill-count {
        background: rgba(255,255,255,0.3);
    }

    .card-pendiente {
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 12px;
        border: none;
        border-left: 4px solid #bd9751;
        background: white;
    }
    .card-pendiente.abierta { border-left-color: #dc3545; }
    .card-pendiente.cerrada { border-left-color: #28a745; }
    .card-pendiente.sin-respuesta { border-left-color: #ffc107; }

    .badge-abierta { background: #dc3545; color: white; }
    .badge-cerrada { background: #28a745; color: white; }
    .badge-sin-respuesta { background: #ffc107; color: #333; }

    .btn-action {
        min-height: 36px;
        font-size: 13px;
        border-radius: 6px;
        padding: 4px 10px;
    }

    .empty-state {
        text-align: center;
        color: #999;
        padding: 32px 16px;
        font-size: 15px;
    }
    .empty-state i {
        font-size: 48px;
        color: #ddd;
        display: block;
        margin-bottom: 12px;
    }

    .dias-badge {
        font-size: 11px;
        padding: 2px 6px;
        border-radius: 4px;
        background: #f8f9fa;
        color: #666;
    }
    .dias-badge.alerta { background: #fff3cd; color: #856404; }
    .dias-badge.critico { background: #f8d7da; color: #721c24; }
</style>

<div class="container-fluid px-3">
    <!-- Selector de cliente -->
    <div class="mt-2 mb-3">
        <label class="form-label">Cliente</label>
        <select id="selectCliente" class="form-select" style="width:100%;">
            <option value="">Seleccionar cliente...</option>
        </select>
    </div>

    <?php if ($idCliente): ?>

        <?php
        $conteoAbierta = $conteos['ABIERTA'] ?? 0;
        $conteoCerrada = $conteos['CERRADA'] ?? 0;
        $conteoSinRespuesta = $conteos['SIN RESPUESTA DEL CLIENTE'] ?? 0;
        $conteoTotal = count($pendientes);
        ?>

        <!-- Filter pills -->
        <div class="filter-pills">
            <div class="filter-pill active" data-filter="ABIERTA">
                Abiertas <span class="pill-count"><?= $conteoAbierta ?></span>
            </div>
            <div class="filter-pill" data-filter="SIN RESPUESTA DEL CLIENTE">
                Sin Respuesta <span class="pill-count"><?= $conteoSinRespuesta ?></span>
            </div>
            <div class="filter-pill" data-filter="CERRADA">
                Cerradas <span class="pill-count"><?= $conteoCerrada ?></span>
            </div>
            <div class="filter-pill" data-filter="todos">
                Todos <span class="pill-count"><?= $conteoTotal ?></span>
            </div>
        </div>

        <?php if (empty($pendientes)): ?>
            <div class="empty-state">
                <i class="fas fa-tasks"></i>
                No hay pendientes registrados para este cliente.
            </div>
        <?php endif; ?>

        <!-- Cards -->
        <div id="cards-container">
        <?php foreach ($pendientes as $p):
            $estado = $p['estado'];
            $dias = $p['dias'] ?? 0;

            if ($estado === 'ABIERTA') {
                $cssClass = 'abierta';
                $badgeClass = 'badge-abierta';
            } elseif ($estado === 'CERRADA') {
                $cssClass = 'cerrada';
                $badgeClass = 'badge-cerrada';
            } else {
                $cssClass = 'sin-respuesta';
                $badgeClass = 'badge-sin-respuesta';
            }

            // Color de días según antigüedad
            $diasBadgeClass = 'dias-badge';
            if ($estado === 'ABIERTA') {
                if ($dias > 30) $diasBadgeClass .= ' critico';
                elseif ($dias > 15) $diasBadgeClass .= ' alerta';
            }
        ?>
        <div class="card card-pendiente <?= $cssClass ?>"
             id="card-<?= $p['id_pendientes'] ?>"
             data-estado="<?= esc($estado) ?>">
            <div class="card-body py-3 px-3">
                <div>
                    <strong><?= esc($p['tarea_actividad'] ?? 'Sin descripcion') ?></strong>
                    <div class="text-muted" style="font-size: 13px;">
                        <?php if (!empty($p['responsable'])): ?>
                            <i class="fas fa-user"></i> <?= esc($p['responsable']) ?> &middot;
                        <?php endif; ?>
                        <span class="badge <?= $badgeClass ?>" style="font-size: 11px;"><?= esc($estado) ?></span>
                        &middot;
                        <span class="<?= $diasBadgeClass ?>"><?= $dias ?> dia(s)</span>
                    </div>
                    <div class="text-muted mt-1" style="font-size: 12px;">
                        Asignado: <?= !empty($p['fecha_asignacion']) ? date('d/m/Y', strtotime($p['fecha_asignacion'])) : '—' ?>
                        <?php if (!empty($p['fecha_cierre'])): ?>
                            &middot; Cierre: <?= date('d/m/Y', strtotime($p['fecha_cierre'])) ?>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($p['estado_avance'])): ?>
                    <div class="text-muted mt-1" style="font-size: 12px;">
                        <i class="fas fa-clipboard-check"></i> <?= esc($p['estado_avance']) ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="d-flex gap-1 mt-2">
                    <a href="/inspecciones/pendientes/edit/<?= $p['id_pendientes'] ?>" class="btn btn-sm btn-outline-dark btn-action">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <?php if ($estado === 'ABIERTA'): ?>
                    <button type="button" class="btn btn-sm btn-success btn-action btn-cerrar" data-id="<?= $p['id_pendientes'] ?>">
                        <i class="fas fa-check"></i> Cerrar
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-action btn-eliminar" data-id="<?= $p['id_pendientes'] ?>" data-nombre="<?= esc($p['tarea_actividad'] ?? '') ?>">
                        <i class="fas fa-trash"></i>
                    </button>
                    <?php elseif ($estado === 'CERRADA'): ?>
                    <button type="button" class="btn btn-sm btn-outline-warning btn-action btn-reabrir" data-id="<?= $p['id_pendientes'] ?>">
                        <i class="fas fa-undo"></i> Reabrir
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        </div>

        <!-- Botón nuevo -->
        <div class="mt-3 mb-4">
            <a href="/inspecciones/pendientes/create/<?= $idCliente ?>" class="btn btn-pwa btn-pwa-primary">
                <i class="fas fa-plus"></i> Nuevo Pendiente
            </a>
        </div>

    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-hand-pointer"></i>
            Selecciona un cliente para ver sus pendientes.
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // === Filter pills ===
    var pills = document.querySelectorAll('.filter-pill');
    var cards = document.querySelectorAll('.card-pendiente');

    pills.forEach(function(pill) {
        pill.addEventListener('click', function() {
            pills.forEach(function(p) { p.classList.remove('active'); });
            this.classList.add('active');

            var filter = this.dataset.filter;
            cards.forEach(function(card) {
                if (filter === 'todos' || card.dataset.estado === filter) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    // Aplicar filtro default
    var defaultPill = document.querySelector('.filter-pill[data-filter="ABIERTA"]');
    if (defaultPill) defaultPill.click();

    // === Select2 clientes via AJAX ===
    $('#selectCliente').select2({
        placeholder: 'Buscar cliente...',
        allowClear: true,
        ajax: {
            url: '/inspecciones/api/clientes',
            dataType: 'json',
            delay: 250,
            processResults: function(data) {
                return {
                    results: data.map(function(c) {
                        return { id: c.id_cliente, text: c.nombre_cliente };
                    })
                };
            },
            cache: true
        },
        minimumInputLength: 0
    });

    <?php if ($idCliente && $clienteSeleccionado): ?>
    var option = new Option('<?= esc($clienteSeleccionado['nombre_cliente']) ?>', '<?= $idCliente ?>', true, true);
    $('#selectCliente').append(option).trigger('change');
    <?php endif; ?>

    $('#selectCliente').on('change', function() {
        var id = $(this).val();
        if (id) {
            window.location.href = '/inspecciones/pendientes/cliente/' + id;
        } else {
            window.location.href = '/inspecciones/pendientes';
        }
    });

    // === Cerrar pendiente (AJAX) ===
    document.querySelectorAll('.btn-cerrar').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.dataset.id;
            Swal.fire({
                title: 'Cerrar pendiente?',
                text: 'Se marcara como CERRADA con fecha de hoy.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Si, cerrar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#28a745',
            }).then(function(result) {
                if (result.isConfirmed) {
                    fetch('/inspecciones/pendientes/estado/' + id, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        body: JSON.stringify({ estado: 'CERRADA' })
                    })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.success) {
                            Swal.fire({ icon: 'success', title: 'Pendiente cerrado', toast: true, position: 'top', showConfirmButton: false, timer: 2000 });
                            setTimeout(function() { location.reload(); }, 1000);
                        }
                    })
                    .catch(function() { Swal.fire('Error', 'No se pudo actualizar', 'error'); });
                }
            });
        });
    });

    // === Reabrir pendiente (AJAX) ===
    document.querySelectorAll('.btn-reabrir').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.dataset.id;
            Swal.fire({
                title: 'Reabrir pendiente?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Si, reabrir',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#ffc107',
            }).then(function(result) {
                if (result.isConfirmed) {
                    fetch('/inspecciones/pendientes/estado/' + id, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        body: JSON.stringify({ estado: 'ABIERTA' })
                    })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.success) {
                            Swal.fire({ icon: 'success', title: 'Pendiente reabierto', toast: true, position: 'top', showConfirmButton: false, timer: 2000 });
                            setTimeout(function() { location.reload(); }, 1000);
                        }
                    })
                    .catch(function() { Swal.fire('Error', 'No se pudo actualizar', 'error'); });
                }
            });
        });
    });

    // === Eliminar ===
    document.querySelectorAll('.btn-eliminar').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.dataset.id;
            var nombre = this.dataset.nombre;
            Swal.fire({
                title: 'Eliminar pendiente?',
                text: nombre,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Si, eliminar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#dc3545',
            }).then(function(result) {
                if (result.isConfirmed) {
                    window.location.href = '/inspecciones/pendientes/delete/' + id;
                }
            });
        });
    });
});
</script>
