<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informes de Avances - SG-SST</title>
    <link rel="icon" href="<?= base_url('favicon.ico') ?>" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        :root {
            --primary-dark: #1c2437;
            --gold-primary: #bd9751;
            --gold-secondary: #d4af37;
            --gradient-bg: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
        body { background: var(--gradient-bg); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; min-height: 100vh; }
        .navbar-custom { background: #fff; box-shadow: 0 8px 32px rgba(28,36,55,0.15); padding: 15px 0; border-bottom: 2px solid var(--gold-primary); }
        .card-custom { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .badge-avance-significativo { background: #28a745; color: #fff; }
        .badge-avance-moderado { background: #17a2b8; color: #fff; }
        .badge-estable { background: #ffc107; color: #333; }
        .badge-reinicio { background: #dc3545; color: #fff; }
        .badge-borrador { background: #6c757d; color: #fff; }
        .badge-completo { background: #28a745; color: #fff; }
        .btn-gold { background: var(--gold-primary); color: #fff; border: none; }
        .btn-gold:hover { background: var(--gold-secondary); color: #fff; }
        .card-consultor {
            border: 2px solid transparent;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.25s ease;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        .card-consultor:hover {
            border-color: var(--gold-primary);
            box-shadow: 0 4px 16px rgba(189,151,81,0.25);
            transform: translateY(-2px);
        }
        .card-consultor.active {
            border-color: var(--gold-primary);
            background: linear-gradient(135deg, #fffbe6 0%, #fff 100%);
            box-shadow: 0 4px 16px rgba(189,151,81,0.3);
        }
        .card-consultor .count-badge {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--primary-dark);
        }
        .card-consultor .consultor-name {
            font-size: 0.85rem;
            font-weight: 600;
            color: #555;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-custom mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?= base_url('consultant/admindashboard') ?>" style="color: var(--primary-dark);">
                <i class="fas fa-arrow-left me-2"></i>Dashboard
            </a>
            <span class="fw-bold" style="color: var(--primary-dark);">
                <i class="fas fa-chart-line me-2" style="color: var(--gold-primary);"></i>Informes de Avances
            </span>
        </div>
    </nav>

    <div class="container-fluid mt-3 px-4">
        <?php if (session()->getFlashdata('msg')): ?>
            <div class="alert alert-success alert-dismissible fade show"><?= session()->getFlashdata('msg') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show"><?= session()->getFlashdata('error') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <!-- ─── CARDS CONSULTORES ─── -->
        <div class="mb-4">
            <h5 class="mb-3"><i class="fas fa-user-tie me-2" style="color: var(--gold-primary);"></i>Informes por Consultor</h5>
            <div class="row g-3" id="cardsConsultores">
                <!-- Card "Todos" -->
                <div class="col-6 col-md-3 col-lg-2">
                    <div class="card card-consultor active text-center p-3" data-consultor-id="">
                        <div class="count-badge" id="countTodos">0</div>
                        <div class="consultor-name">Todos</div>
                    </div>
                </div>
                <?php foreach ($consultores as $cons): ?>
                <div class="col-6 col-md-3 col-lg-2">
                    <div class="card card-consultor text-center p-3" data-consultor-id="<?= esc($cons['id_consultor']) ?>">
                        <div class="count-badge" id="count-<?= esc($cons['id_consultor']) ?>">0</div>
                        <div class="consultor-name" title="<?= esc($cons['nombre_consultor']) ?>"><?= esc($cons['nombre_consultor']) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card card-custom mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Listado de Informes</h5>
                    <a href="<?= base_url('informe-avances/create') ?>" class="btn btn-gold">
                        <i class="fas fa-plus me-1"></i>Nuevo Informe
                    </a>
                </div>

                <div class="row mb-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Filtrar por cliente:</label>
                        <select id="filtroCliente" class="form-select">
                            <option value="">Todos los clientes</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Año:</label>
                        <select id="filtroAnio" class="form-select">
                            <option value="">Todos</option>
                            <?php for ($y = date('Y'); $y >= date('Y') - 3; $y--): ?>
                                <option value="<?= $y ?>"><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Mes:</label>
                        <select id="filtroMes" class="form-select">
                            <option value="">Todos</option>
                            <option value="1">Enero</option>
                            <option value="2">Febrero</option>
                            <option value="3">Marzo</option>
                            <option value="4">Abril</option>
                            <option value="5">Mayo</option>
                            <option value="6">Junio</option>
                            <option value="7">Julio</option>
                            <option value="8">Agosto</option>
                            <option value="9">Septiembre</option>
                            <option value="10">Octubre</option>
                            <option value="11">Noviembre</option>
                            <option value="12">Diciembre</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Desde:</label>
                        <input type="date" id="filtroDesde" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Hasta:</label>
                        <input type="date" id="filtroHasta" class="form-control">
                    </div>
                    <div class="col-md-1 text-end">
                        <button id="btnLimpiar" class="btn btn-outline-secondary btn-sm" title="Limpiar filtros"><i class="fas fa-eraser"></i></button>
                    </div>
                </div>

                <table id="tablaInformes" class="table table-striped table-hover" style="width:100%">
                    <thead class="table-dark">
                        <tr>
                            <th>Cliente</th>
                            <th>Periodo</th>
                            <th>Puntaje</th>
                            <th>Estado Avance</th>
                            <th>Estado</th>
                            <th>Creado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($informes as $inf): ?>
                        <tr data-cliente="<?= esc($inf['nombre_cliente'] ?? '') ?>" data-anio="<?= esc($inf['anio'] ?? '') ?>" data-consultor-id="<?= esc($inf['id_consultor'] ?? '') ?>" data-created="<?= esc($inf['created_at'] ?? '') ?>">
                            <td><?= esc($inf['nombre_cliente'] ?? 'N/A') ?></td>
                            <td><?= date('d/m/Y', strtotime($inf['fecha_desde'])) ?> - <?= date('d/m/Y', strtotime($inf['fecha_hasta'])) ?></td>
                            <td>
                                <strong><?= number_format($inf['puntaje_actual'] ?? 0, 1) ?>%</strong>
                                <?php if ($inf['diferencia_neta'] > 0): ?>
                                    <small class="text-success"><i class="fas fa-arrow-up"></i> +<?= number_format($inf['diferencia_neta'], 1) ?></small>
                                <?php elseif ($inf['diferencia_neta'] < 0): ?>
                                    <small class="text-danger"><i class="fas fa-arrow-down"></i> <?= number_format($inf['diferencia_neta'], 1) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                    $estadoAvance = $inf['estado_avance'] ?? 'Estable';
                                    $badgeClass = match(true) {
                                        str_contains($estadoAvance, 'significativo') => 'badge-avance-significativo',
                                        str_contains($estadoAvance, 'moderado')      => 'badge-avance-moderado',
                                        str_contains($estadoAvance, 'leve')          => 'badge-avance-moderado',
                                        str_contains($estadoAvance, 'Estable')        => 'badge-estable',
                                        str_contains($estadoAvance, 'atención')      => 'badge-reinicio',
                                        default                                      => 'badge-estable',
                                    };
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= esc($estadoAvance) ?></span>
                            </td>
                            <td>
                                <span class="badge <?= $inf['estado'] === 'completo' ? 'badge-completo' : 'badge-borrador' ?>">
                                    <?= ucfirst($inf['estado']) ?>
                                </span>
                            </td>
                            <td data-order="<?= esc($inf['created_at'] ?? '') ?>">
                                <?= $inf['created_at'] ? date('d/m/Y', strtotime($inf['created_at'])) : '-' ?>
                            </td>
                            <td style="white-space:nowrap;">
                                <a href="<?= base_url('informe-avances/edit/' . $inf['id']) ?>" class="btn btn-sm btn-outline-primary" title="Editar"><i class="fas fa-edit"></i></a>
                                <?php if ($inf['estado'] === 'completo'): ?>
                                    <a href="<?= base_url('informe-avances/view/' . $inf['id']) ?>" class="btn btn-sm btn-outline-info" title="Ver"><i class="fas fa-eye"></i></a>
                                    <a href="<?= base_url('informe-avances/pdf/' . $inf['id']) ?>" class="btn btn-sm btn-outline-success" title="PDF" target="_blank"><i class="fas fa-file-pdf"></i></a>
                                <?php endif; ?>
                                <a href="<?= base_url('informe-avances/delete/' . $inf['id']) ?>" class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="return confirm('¿Eliminar este informe?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
    $(document).ready(function() {
        var selectedConsultor = '';

        var table = $('#tablaInformes').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
            order: [[1, 'desc']],
            pageLength: 25,
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                 "<'row'<'col-sm-12'B>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel me-1"></i>Exportar Excel',
                    className: 'btn btn-success btn-sm mb-2',
                    title: 'Informes de Avances',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5],
                        format: {
                            body: function(data, row, column, node) {
                                if (column === 5) {
                                    var iso = $(node).attr('data-order');
                                    return iso ? iso.substring(0, 10) : '';
                                }
                                return $(node).text().trim();
                            }
                        }
                    }
                }
            ],
            columnDefs: [
                { orderable: false, targets: 6 }
            ]
        });

        // Populate client filter from table data
        var clientes = {};
        table.rows().every(function() {
            var c = $(this.node()).data('cliente');
            if (c) clientes[c] = true;
        });
        Object.keys(clientes).sort().forEach(function(c) {
            $('#filtroCliente').append('<option value="'+c+'">'+c+'</option>');
        });

        $('#filtroCliente').select2({ theme: 'bootstrap-5', allowClear: true, placeholder: 'Todos los clientes' });

        // ─── MONTH DROPDOWN → auto-fill dates ───
        $('#filtroMes').on('change', function() {
            var mes = $(this).val();
            if (!mes) {
                $('#filtroDesde').val('');
                $('#filtroHasta').val('');
            } else {
                var anio = $('#filtroAnio').val() || new Date().getFullYear();
                var m = String(mes).padStart(2, '0');
                var lastDay = new Date(anio, mes, 0).getDate();
                $('#filtroDesde').val(anio + '-' + m + '-01');
                $('#filtroHasta').val(anio + '-' + m + '-' + String(lastDay).padStart(2, '0'));
            }
            applyFilters();
        });

        // ─── DATE INPUTS → clear month dropdown if manually changed ───
        $('#filtroDesde, #filtroHasta').on('change', function() {
            applyFilters();
        });

        // ─── CONSULTANT CARDS ───
        $('#cardsConsultores').on('click', '.card-consultor', function() {
            $('.card-consultor').removeClass('active');
            $(this).addClass('active');
            selectedConsultor = String($(this).data('consultor-id'));
            applyFilters();
        });

        // ─── CUSTOM FILTER ───
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            var row = table.row(dataIndex).node();
            var $row = $(row);

            // Client filter
            var clienteFilter = $('#filtroCliente').val();
            if (clienteFilter && $row.data('cliente') !== clienteFilter) return false;

            // Year filter
            var anioFilter = $('#filtroAnio').val();
            if (anioFilter && String($row.data('anio')) !== anioFilter) return false;

            // Consultant filter (from cards)
            if (selectedConsultor && String($row.data('consultor-id')) !== selectedConsultor) return false;

            // Date range filter on created_at
            var desde = $('#filtroDesde').val();
            var hasta = $('#filtroHasta').val();
            var created = $row.data('created');
            if (created && (desde || hasta)) {
                var createdDate = created.substring(0, 10); // YYYY-MM-DD
                if (desde && createdDate < desde) return false;
                if (hasta && createdDate > hasta) return false;
            } else if ((desde || hasta) && !created) {
                return false;
            }

            return true;
        });

        // ─── APPLY FILTERS & UPDATE CARDS ───
        function applyFilters() {
            table.draw();
            updateCardCounts();
        }

        function updateCardCounts() {
            var counts = {};
            var total = 0;

            table.rows().every(function() {
                var row = this.node();
                var $row = $(row);
                var consultorId = String($row.data('consultor-id'));

                // Check all filters EXCEPT consultant
                var clienteFilter = $('#filtroCliente').val();
                if (clienteFilter && $row.data('cliente') !== clienteFilter) return;

                var anioFilter = $('#filtroAnio').val();
                if (anioFilter && String($row.data('anio')) !== anioFilter) return;

                var desde = $('#filtroDesde').val();
                var hasta = $('#filtroHasta').val();
                var created = $row.data('created');
                if (created && (desde || hasta)) {
                    var createdDate = created.substring(0, 10);
                    if (desde && createdDate < desde) return;
                    if (hasta && createdDate > hasta) return;
                } else if ((desde || hasta) && !created) {
                    return;
                }

                counts[consultorId] = (counts[consultorId] || 0) + 1;
                total++;
            });

            // Update "Todos" card
            $('#countTodos').text(total);

            // Update each consultant card
            $('.card-consultor[data-consultor-id!=""]').each(function() {
                var id = String($(this).data('consultor-id'));
                $(this).find('.count-badge').text(counts[id] || 0);
            });
        }

        $('#filtroCliente, #filtroAnio').on('change', function() { applyFilters(); });

        // ─── CLEAR ALL FILTERS ───
        $('#btnLimpiar').on('click', function() {
            $('#filtroCliente').val('').trigger('change.select2');
            $('#filtroAnio').val('');
            $('#filtroMes').val('');
            $('#filtroDesde').val('');
            $('#filtroHasta').val('');
            selectedConsultor = '';
            $('.card-consultor').removeClass('active');
            $('.card-consultor[data-consultor-id=""]').addClass('active');
            applyFilters();
        });

        // Initial card counts
        updateCardCounts();
    });
    </script>
</body>
</html>
