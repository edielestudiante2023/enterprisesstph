<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Vencimientos de Mantenimiento</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        .dataTables_filter {
            margin-bottom: 1rem;
        }
        .dt-buttons {
            margin-bottom: 1rem;
        }
        .action-buttons .btn {
            margin: 2px;
        }
        .table thead th {
            background-color: #f8f9fa;
        }
        .loading {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        tfoot input, tfoot select {
            width: 100%;
            padding: 3px;
            box-sizing: border-box;
        }
        .date-range-filter {
            margin-bottom: 15px;
        }
        .date-range-filter input {
            width: 150px;
            margin-right: 10px;
        }
    </style>
</head>

<body>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h2 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Listado de Vencimientos de Mantenimiento</h2>
            </div>
            <div class="card-body">
                <!-- Mensajes de éxito -->
                <?php if (session()->getFlashdata('msg')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('msg') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Botones de acciones principales -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="btn-group">
                            <a href="<?= site_url('vencimientos/add') ?>" class="btn btn-success">
                                <i class="fas fa-plus me-2"></i>Agregar Nuevo
                            </a>
                            <a href="<?= base_url('vencimientos/send-emails') ?>" class="btn btn-warning">
                                <i class="fas fa-envelope me-2"></i>Enviar Recordatorios
                            </a>
                            <button type="submit" class="btn btn-info" form="sendSelectedForm">
                                <i class="fas fa-paper-plane me-2"></i>Enviar a Seleccionados
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Filtros superiores -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="topFilter_cliente"><strong>Filtrar por Cliente:</strong></label>
                        <input type="text" id="topFilter_cliente" class="form-control" placeholder="Buscar Cliente" />
                    </div>
                    <div class="col-md-6">
                        <label><strong>Rango de Fechas de Vencimiento:</strong></label>
                        <div class="date-range-filter">
                            <input type="date" id="filter_fecha_vencimiento_inicio" class="form-control d-inline-block" placeholder="Desde" />
                            <input type="date" id="filter_fecha_vencimiento_fin" class="form-control d-inline-block" placeholder="Hasta" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button type="button" id="resetFilters" class="btn btn-secondary mt-4">
                            <i class="fas fa-undo me-2"></i>Limpiar Filtros
                        </button>
                    </div>
                </div>

                <!-- Tabla de Vencimientos -->
                <form id="sendSelectedForm" method="post" action="<?= site_url('vencimientos/send-selected-emails') ?>">
                    <table id="vencimientosTable" class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAll" class="form-check-input" /></th>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Consultor</th>
                                <th>Mantenimiento</th>
                                <th>Fecha de Vencimiento</th>
                                <th>Estado</th>
                                <th>Fecha de Realización</th>
                                <th>Observaciones</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th></th>
                                <th>
                                    <select id="filter_id" class="form-select">
                                        <option value="">Todos</option>
                                        <?php
                                        $ids = array_unique(array_column($vencimientos, 'id'));
                                        sort($ids);
                                        foreach ($ids as $id) {
                                            echo '<option value="' . esc($id) . '">' . esc($id) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </th>
                                <th>
                                    <select id="filter_cliente" class="form-select">
                                        <option value="">Todos</option>
                                        <?php
                                        $clientes = array_unique(array_column($vencimientos, 'cliente'));
                                        sort($clientes);
                                        foreach ($clientes as $cliente) {
                                            if (!empty($cliente)) {
                                                echo '<option value="' . esc($cliente) . '">' . esc($cliente) . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </th>
                                <th>
                                    <select id="filter_consultor" class="form-select">
                                        <option value="">Todos</option>
                                        <?php
                                        $consultores = array_unique(array_column($vencimientos, 'consultor'));
                                        sort($consultores);
                                        foreach ($consultores as $consultor) {
                                            if (!empty($consultor)) {
                                                echo '<option value="' . esc($consultor) . '">' . esc($consultor) . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </th>
                                <th>
                                    <select id="filter_mantenimiento" class="form-select">
                                        <option value="">Todos</option>
                                        <?php
                                        $mantenimientos = array_unique(array_column($vencimientos, 'mantenimiento'));
                                        sort($mantenimientos);
                                        foreach ($mantenimientos as $mantenimiento) {
                                            if (!empty($mantenimiento)) {
                                                echo '<option value="' . esc($mantenimiento) . '">' . esc($mantenimiento) . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </th>
                                <th></th>
                                <th>
                                    <select id="filter_estado" class="form-select">
                                        <option value="">Todos</option>
                                        <?php
                                        $estados = array_unique(array_column($vencimientos, 'estado_actividad'));
                                        sort($estados);
                                        foreach ($estados as $estado) {
                                            if (!empty($estado)) {
                                                echo '<option value="' . esc($estado) . '">' . esc($estado) . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </th>
                                <th></th>
                                <th>
                                    <select id="filter_observaciones" class="form-select">
                                        <option value="">Todos</option>
                                        <?php
                                        $observaciones = array_unique(array_column($vencimientos, 'observaciones'));
                                        sort($observaciones);
                                        foreach ($observaciones as $observacion) {
                                            if (!empty($observacion)) {
                                                echo '<option value="' . esc($observacion) . '">' . esc($observacion) . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </th>
                                <th></th>
                            </tr>
                        </tfoot>
                        <tbody>
                            <?php if (!empty($vencimientos) && is_array($vencimientos)): ?>
                                <?php foreach ($vencimientos as $vencimiento): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input email-checkbox" 
                                                   name="selected[]" value="<?= esc($vencimiento['id']) ?>" />
                                        </td>
                                        <td><?= esc($vencimiento['id']) ?></td>
                                        <td><?= esc($vencimiento['cliente']) ?></td>
                                        <td><?= esc($vencimiento['consultor']) ?></td>
                                        <td><?= esc($vencimiento['mantenimiento']) ?></td>
                                        <td data-order="<?= strtotime(esc($vencimiento['fecha_vencimiento'])) ?>">
                                            <?= date('d/m/Y', strtotime(esc($vencimiento['fecha_vencimiento']))) ?>
                                        </td>
                                        <td><?= esc($vencimiento['estado_actividad']) ?></td>
                                        <td data-order="<?= strtotime(esc($vencimiento['fecha_realizacion'])) ?>">
                                            <?= ($vencimiento['fecha_realizacion'] != '0000-00-00') ? date('d/m/Y', strtotime(esc($vencimiento['fecha_realizacion']))) : '' ?>
                                        </td>
                                        <td><?= esc($vencimiento['observaciones']) ?></td>
                                        <td class="action-buttons">
                                            <a href="<?= site_url('vencimientos/edit/' . esc($vencimiento['id'])) ?>" 
                                               class="btn btn-sm btn-primary" data-bs-toggle="tooltip" 
                                               title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= site_url('vencimientos/delete/' . esc($vencimiento['id'])) ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('¿Estás seguro de eliminar este vencimiento?');"
                                               data-bs-toggle="tooltip" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
    </div>

    <!-- Loading indicator -->
    <div class="loading d-none">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inicializar tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });

            // Configurar DataTables
            var table = $('#vencimientosTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
                },
                order: [[5, 'asc']], // Ordenar por fecha de vencimiento por defecto
                pageLength: 25,
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel me-2"></i>Exportar a Excel',
                        className: 'btn btn-success',
                        exportOptions: {
                            columns: [1,2,3,4,5,6,7,8]
                        }
                    }
                ],
                columnDefs: [
                    {
                        targets: [0, 9], // Checkbox y columna de acciones
                        orderable: false,
                        searchable: false
                    },
                    {
                        targets: [5, 7], // Columnas de fecha
                        type: 'date'
                    }
                ],
                responsive: true
            });

            // Aplicar filtros de pie de tabla
            table.columns().every(function(index) {
                var column = this;
                var select = $('select', this.footer());
                
                if (select.length > 0) {
                    select.on('change', function() {
                        var val = $.fn.dataTable.util.escapeRegex($(this).val());
                        column.search(val ? '^'+val+'$' : '', true, false).draw();
                    });
                }
            });

            // Filtro de rango de fechas (utilizando el atributo data-order que contiene el timestamp en segundos)
            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {
                    var min = $('#filter_fecha_vencimiento_inicio').val();
                    var max = $('#filter_fecha_vencimiento_fin').val();

                    // Si no se especifica ningún filtro, se muestran todas las filas
                    if (!min && !max) return true;

                    // Obtener el elemento de la celda de la columna de fecha de vencimiento
                    var cell = $(table.row(dataIndex).node()).find('td:eq(5)');
                    // Convertir el timestamp a milisegundos (el valor en data-order está en segundos)
                    var timestamp = parseInt(cell.attr('data-order')) * 1000;

                    // Convertir los valores de los inputs de fecha a timestamp en milisegundos
                    var minDate = min ? new Date(min).setHours(0, 0, 0, 0) : null;
                    var maxDate = max ? new Date(max).setHours(23, 59, 59, 999) : null;

                    if (minDate && !maxDate) {
                        return timestamp >= minDate;
                    }
                    if (!minDate && maxDate) {
                        return timestamp <= maxDate;
                    }
                    if (minDate && maxDate) {
                        return timestamp >= minDate && timestamp <= maxDate;
                    }
                    return true;
                }
            );

            // Eventos para filtros de fecha
            $('#filter_fecha_vencimiento_inicio, #filter_fecha_vencimiento_fin').change(function() {
                table.draw();
            });

            // Sincronizar filtro superior de cliente con el de pie de tabla
            $('#topFilter_cliente').on('keyup change', function() {
                var val = $.fn.dataTable.util.escapeRegex($(this).val());
                table.column(2).search(val ? val : '', true, false).draw();
            });

            // Manejar checkbox "Seleccionar todos"
            $('#selectAll').change(function() {
                $('.email-checkbox').prop('checked', $(this).prop('checked'));
            });

            // Actualizar checkbox "Seleccionar todos" cuando se cambian los individuales
            $('.email-checkbox').change(function() {
                var allChecked = $('.email-checkbox:checked').length === $('.email-checkbox').length;
                $('#selectAll').prop('checked', allChecked);
            });

            // Botón de reinicio de filtros
            $('#resetFilters').click(function() {
                // Limpiar filtros superiores
                $('#topFilter_cliente').val('');
                $('#filter_fecha_vencimiento_inicio').val('');
                $('#filter_fecha_vencimiento_fin').val('');
                
                // Limpiar filtros de pie de tabla
                table.columns().search('').draw();
                $('tfoot select').val('');
                
                // Redibujar la tabla
                table.draw();
            });

            // Mostrar/ocultar indicador de carga
            $(document)
                .ajaxStart(function() {
                    $('.loading').removeClass('d-none');
                })
                .ajaxStop(function() {
                    $('.loading').addClass('d-none');
                });
        });
    </script>
</body>
</html>
