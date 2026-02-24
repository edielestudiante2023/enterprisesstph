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
    .filter-pill.active { background: #bd9751; color: white; }
    .filter-pill .pill-count {
        background: rgba(0,0,0,0.15);
        border-radius: 10px;
        padding: 0 6px;
        font-size: 11px;
        min-width: 18px;
        text-align: center;
    }
    .filter-pill.active .pill-count { background: rgba(255,255,255,0.3); }

    .card-carta {
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 12px;
        border: none;
        border-left: 4px solid #6c757d;
        background: white;
    }
    .card-carta.pendiente_firma { border-left-color: #ffc107; }
    .card-carta.firmado { border-left-color: #28a745; }
    .card-carta.sin_enviar { border-left-color: #6c757d; }

    .badge-pendiente_firma { background: #ffc107; color: #333; }
    .badge-firmado { background: #28a745; color: white; }
    .badge-sin_enviar { background: #6c757d; color: white; }

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
        $conteoPendiente = $conteos['pendiente_firma'] ?? 0;
        $conteoFirmado = $conteos['firmado'] ?? 0;
        $conteoTotal = count($cartas);
        ?>

        <!-- Filter pills -->
        <div class="filter-pills">
            <div class="filter-pill active" data-filter="pendiente_firma">
                Pendientes <span class="pill-count"><?= $conteoPendiente ?></span>
            </div>
            <div class="filter-pill" data-filter="firmado">
                Firmadas <span class="pill-count"><?= $conteoFirmado ?></span>
            </div>
            <div class="filter-pill" data-filter="todos">
                Todas <span class="pill-count"><?= $conteoTotal ?></span>
            </div>
        </div>

        <?php if (empty($cartas)): ?>
            <div class="empty-state">
                <i class="fas fa-user-shield"></i>
                No hay cartas de vigia para este cliente.
            </div>
        <?php endif; ?>

        <!-- Cards -->
        <div id="cards-container">
        <?php foreach ($cartas as $c):
            $estado = $c['estado_firma'];
            $badgeLabel = $estado === 'pendiente_firma' ? 'Pendiente firma' : ($estado === 'firmado' ? 'Firmada' : 'Sin enviar');
        ?>
        <div class="card card-carta <?= $estado ?>" data-estado="<?= esc($estado) ?>">
            <div class="card-body py-3 px-3">
                <div>
                    <strong><i class="fas fa-user-shield"></i> <?= esc($c['nombre_vigia']) ?></strong>
                    <div class="text-muted" style="font-size: 13px;">
                        CC <?= esc($c['documento_vigia']) ?>
                        &middot;
                        <span class="badge badge-<?= $estado ?>" style="font-size: 11px;"><?= $badgeLabel ?></span>
                    </div>
                    <div class="text-muted" style="font-size: 12px;">
                        <i class="fas fa-envelope"></i> <?= esc($c['email_vigia']) ?>
                        <?php if (!empty($c['telefono_vigia'])): ?>
                            &middot; <i class="fas fa-phone"></i> <?= esc($c['telefono_vigia']) ?>
                        <?php endif; ?>
                    </div>
                    <?php if ($estado === 'firmado' && !empty($c['firma_fecha'])): ?>
                    <div class="text-muted mt-1" style="font-size: 12px;">
                        <i class="fas fa-check-circle text-success"></i>
                        Firmado: <?= date('d/m/Y H:i', strtotime($c['firma_fecha'])) ?>
                        <?php if (!empty($c['codigo_verificacion'])): ?>
                            &middot; Cod: <?= esc($c['codigo_verificacion']) ?>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="d-flex gap-1 mt-2">
                    <?php if ($estado === 'firmado'): ?>
                        <a href="/inspecciones/carta-vigia/pdf/<?= $c['id'] ?>" target="_blank" class="btn btn-sm btn-success btn-action">
                            <i class="fas fa-file-pdf"></i> Ver PDF
                        </a>
                    <?php else: ?>
                        <button type="button" class="btn btn-sm btn-outline-warning btn-action btn-reenviar" data-id="<?= $c['id'] ?>">
                            <i class="fas fa-paper-plane"></i> Reenviar
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-action btn-eliminar" data-id="<?= $c['id'] ?>" data-nombre="<?= esc($c['nombre_vigia']) ?>">
                            <i class="fas fa-trash"></i>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        </div>

        <!-- Botón nueva carta -->
        <div class="mt-3 mb-4">
            <a href="/inspecciones/carta-vigia/create/<?= $idCliente ?>" class="btn btn-pwa btn-pwa-primary">
                <i class="fas fa-plus"></i> Nueva Carta Vigia
            </a>
        </div>

    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-hand-pointer"></i>
            Selecciona un cliente para ver las cartas de vigia.
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filter pills
    var pills = document.querySelectorAll('.filter-pill');
    var cards = document.querySelectorAll('.card-carta');

    pills.forEach(function(pill) {
        pill.addEventListener('click', function() {
            pills.forEach(function(p) { p.classList.remove('active'); });
            this.classList.add('active');
            var filter = this.dataset.filter;
            cards.forEach(function(card) {
                card.style.display = (filter === 'todos' || card.dataset.estado === filter) ? '' : 'none';
            });
        });
    });

    var defaultPill = document.querySelector('.filter-pill[data-filter="pendiente_firma"]');
    if (defaultPill) defaultPill.click();

    // Select2
    $('#selectCliente').select2({
        placeholder: 'Buscar cliente...',
        allowClear: true,
        ajax: {
            url: '/inspecciones/api/clientes',
            dataType: 'json',
            delay: 250,
            processResults: function(data) {
                return { results: data.map(function(c) { return { id: c.id_cliente, text: c.nombre_cliente }; }) };
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
        window.location.href = id ? '/inspecciones/carta-vigia/cliente/' + id : '/inspecciones/carta-vigia';
    });

    // Reenviar email
    document.querySelectorAll('.btn-reenviar').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.dataset.id;
            Swal.fire({
                title: 'Reenviar email de firma?',
                text: 'Se generara un nuevo enlace de 7 dias.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Si, reenviar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#bd9751',
            }).then(function(result) {
                if (result.isConfirmed) {
                    fetch('/inspecciones/carta-vigia/reenviar/' + id, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.success) {
                            Swal.fire({ icon: 'success', title: 'Email reenviado', toast: true, position: 'top', showConfirmButton: false, timer: 2000 });
                        } else {
                            Swal.fire('Error', data.message || 'No se pudo enviar', 'error');
                        }
                    })
                    .catch(function() { Swal.fire('Error', 'Error de conexion', 'error'); });
                }
            });
        });
    });

    // Eliminar
    document.querySelectorAll('.btn-eliminar').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.dataset.id;
            var nombre = this.dataset.nombre;
            Swal.fire({
                title: 'Eliminar carta?',
                text: 'Carta de ' + nombre,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Si, eliminar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#dc3545',
            }).then(function(result) {
                if (result.isConfirmed) {
                    window.location.href = '/inspecciones/carta-vigia/delete/' + id;
                }
            });
        });
    });
});
</script>
