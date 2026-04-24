<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Plan de Trabajo - Consultor</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar-logos {
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 10px 0;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        .header-section {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .header-section h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .metric-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin-bottom: 1.5rem;
            transition: transform 0.3s ease;
        }

        .metric-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }

        .metric-card h6 {
            color: #6c757d;
            font-size: 0.9rem;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .metric-card h2 {
            color: #f5576c;
            font-size: 2.5rem;
            font-weight: bold;
            margin: 0;
        }

        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
            min-height: 350px;
        }

        .chart-container h5 {
            text-align: center;
            color: #1c2437;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .chart-container .chart-hint {
            display: block;
            text-align: center;
            color: #6c757d;
            font-size: 0.75rem;
            margin-top: 0.25rem;
        }

        .table-container {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        canvas {
            max-height: 300px;
        }

        .btn-volver {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-volver:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            color: white;
        }
    </style>
</head>

<body>
    <!-- Navbar con logos -->
    <nav class="navbar-logos">
        <div class="container-fluid d-flex justify-content-around align-items-center">
            <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprise SST" height="60">
            <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST" height="60">
            <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloid" height="60">
        </div>
    </nav>

    <div style="height: 100px;"></div>

    <!-- Contenido principal -->
    <div class="container-fluid px-4">
        <!-- Header con métricas -->
        <div class="header-section">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="fas fa-tasks"></i> Dashboard Plan de Trabajo - Consultor</h1>
                    <p class="mb-0">Vista consolidada de todos los clientes</p>
                    <p class="mb-0"><i class="fas fa-user me-2"></i>Sesión: <strong><?= session()->get('nombre_usuario') ?? 'Usuario' ?></strong></p>
                </div>
                <div class="col-md-4">
                    <div class="row text-center">
                        <div class="col-6">
                            <div style="font-size: 3rem; font-weight: bold; line-height: 1;">
                                <span id="metricTotal"><?= $totalActividades ?></span>
                            </div>
                            <p class="mb-0">Total Actividades</p>
                        </div>
                        <div class="col-6">
                            <div style="font-size: 3rem; font-weight: bold; line-height: 1;">
                                <span id="metricClientes"><?= $totalClientes ?></span>
                            </div>
                            <p class="mb-0">Total Clientes</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botón volver -->
        <div class="mb-3">
            <a href="<?= base_url('dashboardconsultant') ?>" class="btn-volver">
                <i class="fas fa-arrow-left"></i> Volver al Dashboard
            </a>
        </div>

        <!-- Filtros/Selectores -->
        <div class="row mb-4">
            <div class="col-md-3">
                <label class="form-label fw-bold"><i class="fas fa-building"></i> Seleccione Cliente</label>
                <select class="form-select" id="filterCliente">
                    <option value="">Todos los clientes</option>
                    <?php foreach ($clientes as $cliente): ?>
                        <option value="<?= $cliente['id_cliente'] ?>"><?= esc($cliente['nombre_cliente']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold"><i class="fas fa-user-tie"></i> Seleccione Consultor</label>
                <select class="form-select" id="filterConsultor">
                    <option value="">Todos los consultores</option>
                    <?php foreach ($consultoresUnicos as $c): ?>
                        <option value="<?= $c['id_consultor'] ?>"><?= esc($c['nombre_consultor']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold"><i class="fas fa-user-friends"></i> Seleccione Consultor Externo</label>
                <select class="form-select" id="filterConsultorExterno">
                    <option value="">Todos los consultores externos</option>
                    <?php foreach ($consultoresExternosUnicos as $ce): ?>
                        <option value="<?= esc($ce['consultor_externo']) ?>"><?= esc($ce['consultor_externo']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold"><i class="fas fa-filter"></i> Seleccione Estado</label>
                <select class="form-select" id="filterEstado">
                    <option value="">Todos los estados</option>
                    <?php foreach ($estadosUnicos as $estado): ?>
                        <?php if (!empty($estado)): ?>
                            <option value="<?= esc($estado) ?>"><?= esc($estado) ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3">
                <label class="form-label fw-bold"><i class="fas fa-sync-alt"></i> Seleccione PHVA</label>
                <select class="form-select" id="filterPhva">
                    <option value="">Todos los PHVA</option>
                    <?php foreach ($phvasUnicos as $phva): ?>
                        <?php if (!empty($phva)): ?>
                            <option value="<?= esc($phva) ?>"><?= esc($phva) ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold"><i class="fas fa-layer-group"></i> Seleccione Estándares</label>
                <select class="form-select" id="filterEstandares">
                    <option value="">Todos los estándares</option>
                    <?php foreach ($estandaresUnicos as $es): ?>
                        <option value="<?= esc($es['estandares']) ?>"><?= esc($es['estandares']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold"><i class="fas fa-calendar"></i> Seleccione Año</label>
                <select class="form-select" id="filterAnio">
                    <option value="">Todos los años</option>
                    <?php foreach ($aniosDisponibles as $y): ?>
                        <option value="<?= $y ?>" <?= $y === $anioActual ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold"><i class="fas fa-calendar-day"></i> Seleccione Mes</label>
                <select class="form-select" id="filterMes">
                    <option value="">Todos los meses</option>
                    <option value="01">Enero</option>
                    <option value="02">Febrero</option>
                    <option value="03">Marzo</option>
                    <option value="04">Abril</option>
                    <option value="05">Mayo</option>
                    <option value="06">Junio</option>
                    <option value="07">Julio</option>
                    <option value="08">Agosto</option>
                    <option value="09">Septiembre</option>
                    <option value="10">Octubre</option>
                    <option value="11">Noviembre</option>
                    <option value="12">Diciembre</option>
                </select>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3">
                <label class="form-label fw-bold"><i class="fas fa-calendar"></i> Fecha Desde</label>
                <input type="date" class="form-control" id="filterFechaDesde" value="<?= $anioActual ?>-01-01">
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold"><i class="fas fa-calendar"></i> Fecha Hasta</label>
                <input type="date" class="form-control" id="filterFechaHasta" value="<?= $anioActual ?>-12-31">
            </div>

            <div class="col-md-3 d-flex align-items-end">
                <button type="button" class="btn btn-secondary w-100" id="btnLimpiarFiltros">
                    <i class="fas fa-eraser"></i> Limpiar Filtros
                </button>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="row">
            <!-- Gráfico Clientes (Barras Horizontales, clickeable) -->
            <div class="col-md-6">
                <div class="chart-container">
                    <h5><i class="fas fa-building"></i> Actividades por Cliente</h5>
                    <span class="chart-hint">Click en una barra para filtrar por ese cliente</span>
                    <canvas id="chartClientes"></canvas>
                </div>
            </div>

            <!-- Gráfico Estado (Donut, clickeable) -->
            <div class="col-md-3">
                <div class="chart-container">
                    <h5><i class="fas fa-toggle-on"></i> Estado de Actividades</h5>
                    <span class="chart-hint">Click en un segmento para filtrar por estado</span>
                    <canvas id="chartEstado"></canvas>
                </div>
            </div>

            <!-- Gráfico PHVA (Donut, clickeable) -->
            <div class="col-md-3">
                <div class="chart-container">
                    <h5><i class="fas fa-sync-alt"></i> Ciclo PHVA</h5>
                    <span class="chart-hint">Click en un segmento para filtrar por PHVA</span>
                    <canvas id="chartPhva"></canvas>
                </div>
            </div>
        </div>

        <!-- Tabla de datos -->
        <div class="row">
            <div class="col-12">
                <div class="table-container">
                    <h5 class="mb-3"><i class="fas fa-table"></i> Detalle de Actividades</h5>
                    <table id="actividadesTable" class="table table-striped table-bordered table-hover" style="width:100%">
                        <thead class="table-dark">
                            <tr>
                                <th>CLIENTE</th>
                                <th>ACTIVIDAD</th>
                                <th>FECHA PROPUESTA</th>
                                <th>FECHA CIERRE</th>
                                <th>ESTADO</th>
                                <th>PHVA</th>
                                <th>% AVANCE</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($actividades as $act): ?>
                                <tr>
                                    <td><?= esc($act['nombre_cliente']) ?></td>
                                    <td><?= esc($act['actividad_plandetrabajo'] ?? 'N/A') ?></td>
                                    <td><?= esc($act['fecha_propuesta'] ?? 'N/A') ?></td>
                                    <td><?= esc($act['fecha_cierre'] ?? 'N/A') ?></td>
                                    <td>
                                        <span class="badge bg-<?= ($act['estado_actividad'] ?? '') === 'CERRADA CON EJECUCIÓN' ? 'success' : (($act['estado_actividad'] ?? '') === 'ABIERTA' ? 'warning' : 'secondary') ?>">
                                            <?= esc($act['estado_actividad'] ?? 'N/A') ?>
                                        </span>
                                    </td>
                                    <td><?= esc($act['phva_plandetrabajo'] ?? 'N/A') ?></td>
                                    <td class="text-center"><?= esc($act['porcentaje_avance'] ?? '0') ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div style="height: 50px;"></div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <!-- Chart.js Datalabels Plugin para etiquetas visibles -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>

    <script>
        // Registrar el plugin de datalabels globalmente
        Chart.register(ChartDataLabels);

        // Datos originales
        var originalData = <?= json_encode($actividades) ?>;
        var clientesCascade = <?= json_encode($clientesCascade) ?>;

        // Paleta amplia para el gráfico de clientes
        var CLIENTES_PALETTE = [
            '#36A2EB', '#FF6384', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40',
            '#28a745', '#dc3545', '#17a2b8', '#6f42c1', '#fd7e14', '#20c997'
        ];

        // Variables globales para gráficos
        var chartClientes, chartEstado, chartPhva;
        var dataTable;
        // Guardo los id_cliente paralelos a las labels del bar chart
        var chartClientesIds = [];

        // Bandera para evitar cascadas recursivas mientras actualizo los selects
        var suspendCascade = false;

        $(document).ready(function() {
            var select2Lang = {
                noResults: function() { return "No se encontraron resultados"; },
                searching: function() { return "Buscando..."; }
            };

            $('#filterCliente').select2({ theme: 'bootstrap-5', placeholder: 'Seleccione un cliente', allowClear: true, width: '100%', language: select2Lang });
            $('#filterConsultor').select2({ theme: 'bootstrap-5', placeholder: 'Seleccione un consultor', allowClear: true, width: '100%', language: select2Lang });
            $('#filterConsultorExterno').select2({ theme: 'bootstrap-5', placeholder: 'Seleccione un consultor externo', allowClear: true, width: '100%', language: select2Lang });
            $('#filterEstandares').select2({ theme: 'bootstrap-5', placeholder: 'Seleccione un estándar', allowClear: true, width: '100%', language: select2Lang });
            $('#filterPhva').select2({ theme: 'bootstrap-5', placeholder: 'Seleccione PHVA', allowClear: true, width: '100%', language: select2Lang });
            $('#filterEstado').select2({ theme: 'bootstrap-5', placeholder: 'Seleccione estado', allowClear: true, width: '100%', language: select2Lang });
            $('#filterAnio').select2({ theme: 'bootstrap-5', placeholder: 'Todos los años', allowClear: true, width: '100%', language: select2Lang });
            $('#filterMes').select2({ theme: 'bootstrap-5', placeholder: 'Todos los meses', allowClear: true, width: '100%', language: select2Lang });

            // DataTable
            dataTable = $('#actividadesTable').DataTable({
                language: { url: "//cdn.datatables.net/plug-ins/1.13.1/i18n/es-ES.json" },
                dom: 'Bfrtip',
                buttons: [{
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i> Exportar a Excel',
                    className: 'btn btn-success btn-sm',
                    title: 'Plan de Trabajo - Consultor',
                    exportOptions: { columns: ':visible' }
                }],
                pageLength: 25,
                order: [[2, 'desc']],
                responsive: true
            });

            initCharts();

            // Filtros que participan del cascadeo (bidireccional, afectan el pool de clientes)
            var cascadeSelectors = '#filterCliente, #filterConsultor, #filterConsultorExterno, #filterEstandares';
            $(cascadeSelectors).on('change', function() {
                if (suspendCascade) return;
                updateCascadeDropdowns();
                applyFilters();
            });

            // Filtros que no cascadean y disparan applyFilters directamente
            $('#filterEstado, #filterPhva, #filterFechaDesde, #filterFechaHasta').on('change', function() {
                applyFilters();
            });

            // Año y Mes: auto-rellenan Desde/Hasta y luego aplican filtros
            $('#filterAnio, #filterMes').on('change', function() {
                autoFillFechas();
                applyFilters();
            });

            // Botón limpiar filtros
            $('#btnLimpiarFiltros').on('click', function() {
                suspendCascade = true;
                $('#filterCliente, #filterConsultor, #filterConsultorExterno, #filterEstandares, #filterEstado, #filterPhva, #filterAnio, #filterMes').val('').trigger('change');
                $('#filterFechaDesde').val('');
                $('#filterFechaHasta').val('');
                suspendCascade = false;
                updateCascadeDropdowns();
                applyFilters();
            });

            // Aplicar filtro inicial (año actual por defecto, Desde/Hasta ya pre-rellenados desde PHP)
            applyFilters();
        });

        // Auto-rellena Fecha Desde/Hasta segun Año + Mes seleccionados
        function autoFillFechas() {
            var year = $('#filterAnio').val();
            var month = $('#filterMes').val();
            if (!year) {
                $('#filterFechaDesde').val('');
                $('#filterFechaHasta').val('');
                return;
            }
            if (!month) {
                $('#filterFechaDesde').val(year + '-01-01');
                $('#filterFechaHasta').val(year + '-12-31');
                return;
            }
            var y = parseInt(year, 10);
            var m = parseInt(month, 10);
            var lastDay = new Date(y, m, 0).getDate(); // day 0 del mes siguiente = ultimo dia del mes actual
            var mm = String(m).padStart(2, '0');
            var dd = String(lastDay).padStart(2, '0');
            $('#filterFechaDesde').val(year + '-' + mm + '-01');
            $('#filterFechaHasta').val(year + '-' + mm + '-' + dd);
        }

        // === Cascadeo bidireccional de los 4 filtros basados en el cliente ===
        function filterClientesPool(opts) {
            return clientesCascade.filter(function(c) {
                if (opts.cliente && String(c.id_cliente) !== String(opts.cliente)) return false;
                if (opts.consultor && String(c.id_consultor || '') !== String(opts.consultor)) return false;
                if (opts.externo && (c.consultor_externo || '') !== opts.externo) return false;
                if (opts.estandares && (c.estandares || '') !== opts.estandares) return false;
                return true;
            });
        }

        function rebuildSelect($sel, options, currentVal, placeholderText) {
            var html = '<option value="">' + placeholderText + '</option>';
            var stillValid = false;
            options.forEach(function(opt) {
                var sel = (String(opt.value) === String(currentVal)) ? ' selected' : '';
                if (sel) stillValid = true;
                html += '<option value="' + $('<div>').text(opt.value).html() + '"' + sel + '>' +
                        $('<div>').text(opt.label).html() + '</option>';
            });
            $sel.html(html);
            if (!stillValid && currentVal) {
                $sel.val('');
            }
        }

        function updateCascadeDropdowns() {
            var sCliente = $('#filterCliente').val();
            var sConsultor = $('#filterConsultor').val();
            var sExterno = $('#filterConsultorExterno').val();
            var sEstandares = $('#filterEstandares').val();

            // Clientes disponibles: pool filtrado por consultor/externo/estandares
            var poolCliente = filterClientesPool({ consultor: sConsultor, externo: sExterno, estandares: sEstandares });
            var clienteOptions = [];
            var seenCli = {};
            poolCliente.forEach(function(c) {
                if (!seenCli[c.id_cliente]) {
                    seenCli[c.id_cliente] = true;
                    clienteOptions.push({ value: c.id_cliente, label: c.nombre_cliente });
                }
            });
            clienteOptions.sort(function(a, b) { return String(a.label).localeCompare(String(b.label)); });

            // Consultores disponibles: pool filtrado por cliente/externo/estandares
            var poolConsultor = filterClientesPool({ cliente: sCliente, externo: sExterno, estandares: sEstandares });
            var consultorOptions = [];
            var seenCons = {};
            poolConsultor.forEach(function(c) {
                if (c.id_consultor && c.nombre_consultor && !seenCons[c.id_consultor]) {
                    seenCons[c.id_consultor] = true;
                    consultorOptions.push({ value: c.id_consultor, label: c.nombre_consultor });
                }
            });
            consultorOptions.sort(function(a, b) { return String(a.label).localeCompare(String(b.label)); });

            // Consultores externos disponibles
            var poolExterno = filterClientesPool({ cliente: sCliente, consultor: sConsultor, estandares: sEstandares });
            var externoOptions = [];
            var seenExt = {};
            poolExterno.forEach(function(c) {
                var ext = c.consultor_externo || '';
                if (ext && !seenExt[ext]) {
                    seenExt[ext] = true;
                    externoOptions.push({ value: ext, label: ext });
                }
            });
            externoOptions.sort(function(a, b) { return String(a.label).localeCompare(String(b.label)); });

            // Estándares disponibles
            var poolEst = filterClientesPool({ cliente: sCliente, consultor: sConsultor, externo: sExterno });
            var estandaresOptions = [];
            var seenEst = {};
            poolEst.forEach(function(c) {
                var e = c.estandares || '';
                if (e && !seenEst[e]) {
                    seenEst[e] = true;
                    estandaresOptions.push({ value: e, label: e });
                }
            });
            estandaresOptions.sort(function(a, b) { return String(a.label).localeCompare(String(b.label)); });

            suspendCascade = true;
            rebuildSelect($('#filterCliente'), clienteOptions, sCliente, 'Todos los clientes');
            rebuildSelect($('#filterConsultor'), consultorOptions, sConsultor, 'Todos los consultores');
            rebuildSelect($('#filterConsultorExterno'), externoOptions, sExterno, 'Todos los consultores externos');
            rebuildSelect($('#filterEstandares'), estandaresOptions, sEstandares, 'Todos los estándares');
            $('#filterCliente, #filterConsultor, #filterConsultorExterno, #filterEstandares').trigger('change.select2');
            suspendCascade = false;
        }

        // === Gráficos ===
        function initCharts() {
            // Bar chart: Actividades por Cliente
            var ctxClientes = document.getElementById('chartClientes').getContext('2d');
            var clientesInit = computeClienteCounts(originalData);
            chartClientesIds = clientesInit.map(function(x) { return x.id_cliente; });
            chartClientes = new Chart(ctxClientes, {
                type: 'bar',
                data: {
                    labels: clientesInit.map(function(x) { return x.nombre_cliente; }),
                    datasets: [{
                        label: 'Actividades',
                        data: clientesInit.map(function(x) { return x.count; }),
                        backgroundColor: clientesInit.map(function(_, i) { return CLIENTES_PALETTE[i % CLIENTES_PALETTE.length]; })
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: true,
                    layout: { padding: { right: 30 } },
                    onClick: function(evt, elements) {
                        if (!elements.length) return;
                        var idx = elements[0].index;
                        var clickedId = chartClientesIds[idx];
                        if (clickedId === undefined || clickedId === null) return;
                        var current = $('#filterCliente').val();
                        var newVal = (String(current) === String(clickedId)) ? '' : String(clickedId);
                        $('#filterCliente').val(newVal).trigger('change');
                    },
                    plugins: {
                        legend: { display: false },
                        datalabels: {
                            color: '#333', anchor: 'end', align: 'end',
                            font: { weight: 'bold', size: 11 },
                            formatter: function(value) { return value > 0 ? value : ''; }
                        }
                    },
                    scales: {
                        x: { beginAtZero: true },
                        y: {
                            ticks: {
                                autoSkip: false,
                                font: { size: 10 },
                                callback: function(value) {
                                    var label = this.getLabelForValue(value);
                                    if (!label) return label;
                                    return label.length > 35 ? label.substring(0, 33) + '…' : label;
                                }
                            }
                        }
                    }
                }
            });

            // Donut Estado
            var ctxEstado = document.getElementById('chartEstado').getContext('2d');
            var estadoData = <?= json_encode($estadoCounts) ?>;
            chartEstado = new Chart(ctxEstado, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(estadoData),
                    datasets: [{
                        data: Object.values(estadoData),
                        backgroundColor: ['#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6c757d', '#6f42c1']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    onClick: function(evt, elements) {
                        if (!elements.length) return;
                        var idx = elements[0].index;
                        var clickedLabel = chartEstado.data.labels[idx];
                        if (!clickedLabel) return;
                        var current = $('#filterEstado').val();
                        var newVal = (current === clickedLabel) ? '' : clickedLabel;
                        $('#filterEstado').val(newVal).trigger('change');
                    },
                    plugins: {
                        legend: { position: 'bottom' },
                        datalabels: {
                            color: '#fff',
                            font: { weight: 'bold', size: 12 },
                            formatter: function(value, context) {
                                var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                var percentage = total > 0 ? ((value / total) * 100).toFixed(1) + '%' : '0%';
                                return value > 0 ? percentage : '';
                            }
                        }
                    }
                }
            });

            // Donut PHVA
            var ctxPhva = document.getElementById('chartPhva').getContext('2d');
            var phvaData = <?= json_encode($phvaCounts) ?>;
            chartPhva = new Chart(ctxPhva, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(phvaData),
                    datasets: [{
                        data: Object.values(phvaData),
                        backgroundColor: ['#36A2EB', '#FF6384', '#FFCE56', '#4BC0C0']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    onClick: function(evt, elements) {
                        if (!elements.length) return;
                        var idx = elements[0].index;
                        var clickedLabel = chartPhva.data.labels[idx];
                        if (!clickedLabel) return;
                        var current = $('#filterPhva').val();
                        var newVal = (current === clickedLabel) ? '' : clickedLabel;
                        $('#filterPhva').val(newVal).trigger('change');
                    },
                    plugins: {
                        legend: { position: 'bottom' },
                        datalabels: {
                            color: '#fff',
                            font: { weight: 'bold', size: 12 },
                            formatter: function(value, context) {
                                var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                var percentage = total > 0 ? ((value / total) * 100).toFixed(1) + '%' : '0%';
                                return value > 0 ? percentage : '';
                            }
                        }
                    }
                }
            });
        }

        function computeClienteCounts(acts) {
            var byId = {};
            acts.forEach(function(a) {
                var id = a.id_cliente;
                if (id === undefined || id === null) return;
                if (!byId[id]) {
                    byId[id] = { id_cliente: id, nombre_cliente: a.nombre_cliente || 'SIN NOMBRE', count: 0 };
                }
                byId[id].count++;
            });
            var arr = Object.keys(byId).map(function(k) { return byId[k]; });
            arr.sort(function(a, b) { return b.count - a.count; });
            return arr;
        }

        function applyFilters() {
            var filterCliente = $('#filterCliente').val();
            var filterConsultor = $('#filterConsultor').val();
            var filterConsultorExterno = $('#filterConsultorExterno').val();
            var filterEstandares = $('#filterEstandares').val();
            var filterEstado = $('#filterEstado').val();
            var filterPhva = $('#filterPhva').val();
            var filterFechaDesde = $('#filterFechaDesde').val();
            var filterFechaHasta = $('#filterFechaHasta').val();

            var filteredData = originalData.filter(function(item) {
                if (filterCliente && String(item.id_cliente) !== String(filterCliente)) return false;
                if (filterConsultor && String(item.id_consultor || '') !== String(filterConsultor)) return false;
                if (filterConsultorExterno && (item.consultor_externo || '') !== filterConsultorExterno) return false;
                if (filterEstandares && (item.estandares || '') !== filterEstandares) return false;
                if (filterEstado && item.estado_actividad !== filterEstado) return false;
                if (filterPhva && item.phva_plandetrabajo !== filterPhva) return false;

                if (filterFechaDesde || filterFechaHasta) {
                    if (!item.fecha_propuesta) return false;
                    if (filterFechaDesde && item.fecha_propuesta < filterFechaDesde) return false;
                    if (filterFechaHasta && item.fecha_propuesta > filterFechaHasta) return false;
                }
                return true;
            });

            $('#metricTotal').text(filteredData.length);

            var clientesDistintos = {};
            filteredData.forEach(function(item) {
                if (item.id_cliente !== undefined && item.id_cliente !== null) {
                    clientesDistintos[item.id_cliente] = true;
                }
            });
            $('#metricClientes').text(Object.keys(clientesDistintos).length);

            var estadoCounts = {};
            var phvaCounts = {};
            filteredData.forEach(function(item) {
                var estado = item.estado_actividad || 'SIN ESTADO';
                estadoCounts[estado] = (estadoCounts[estado] || 0) + 1;
                var phva = item.phva_plandetrabajo || 'SIN PHVA';
                phvaCounts[phva] = (phvaCounts[phva] || 0) + 1;
            });

            var clienteCounts = computeClienteCounts(filteredData);

            updateCharts(clienteCounts, estadoCounts, phvaCounts);

            dataTable.clear();
            filteredData.forEach(function(item) {
                var estadoBadge = '';
                if (item.estado_actividad === 'CERRADA CON EJECUCIÓN') {
                    estadoBadge = '<span class="badge bg-success">' + (item.estado_actividad || 'N/A') + '</span>';
                } else if (item.estado_actividad === 'ABIERTA') {
                    estadoBadge = '<span class="badge bg-warning">' + (item.estado_actividad || 'N/A') + '</span>';
                } else {
                    estadoBadge = '<span class="badge bg-secondary">' + (item.estado_actividad || 'N/A') + '</span>';
                }

                dataTable.row.add([
                    item.nombre_cliente,
                    item.actividad_plandetrabajo || 'N/A',
                    item.fecha_propuesta || 'N/A',
                    item.fecha_cierre || 'N/A',
                    estadoBadge,
                    item.phva_plandetrabajo || 'N/A',
                    (item.porcentaje_avance || '0') + '%'
                ]);
            });
            dataTable.draw();
        }

        function updateCharts(clienteCounts, estadoCounts, phvaCounts) {
            // Clientes
            chartClientes.data.labels = clienteCounts.map(function(x) { return x.nombre_cliente; });
            chartClientes.data.datasets[0].data = clienteCounts.map(function(x) { return x.count; });
            chartClientes.data.datasets[0].backgroundColor = clienteCounts.map(function(_, i) { return CLIENTES_PALETTE[i % CLIENTES_PALETTE.length]; });
            chartClientesIds = clienteCounts.map(function(x) { return x.id_cliente; });
            chartClientes.update();

            // Estado
            chartEstado.data.labels = Object.keys(estadoCounts);
            chartEstado.data.datasets[0].data = Object.values(estadoCounts);
            chartEstado.update();

            // PHVA
            chartPhva.data.labels = Object.keys(phvaCounts);
            chartPhva.data.datasets[0].data = Object.values(phvaCounts);
            chartPhva.update();
        }
    </script>
</body>

</html>
