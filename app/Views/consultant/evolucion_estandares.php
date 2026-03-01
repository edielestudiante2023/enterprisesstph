<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evolucion Estandares Minimos - Enterprise SST</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        }

        .metric-card h6 {
            color: #6c757d;
            font-size: 0.9rem;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }

        .metric-card h2 {
            color: #764ba2;
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

        .filter-section {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }

        .table-container {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }

        .btn-clear {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
        }

        .btn-clear:hover {
            background: linear-gradient(135deg, #764ba2, #667eea);
            color: white;
        }

        footer {
            background: white;
            padding: 20px 0;
            border-top: 1px solid #dee2e6;
            margin-top: 40px;
            text-align: center;
            font-size: 14px;
            color: #6c757d;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar-logos">
        <div class="d-flex justify-content-between align-items-center px-4" style="max-width: 1400px; margin: 0 auto;">
            <div>
                <a href="<?= base_url('/admin/dashboard') ?>">
                    <img src="<?= base_url('uploads/logocycloidhorizontal.png') ?>" alt="Cycloid" style="height: 50px;">
                </a>
            </div>
            <div>
                <h5 class="mb-0 fw-bold" style="color: #1c2437;">EVOLUCION ESTANDARES MINIMOS CLIENTES</h5>
            </div>
            <div class="d-flex align-items-center">
                <span class="badge bg-secondary me-2">nombre_cliente</span>
                <span class="badge bg-primary fs-6" id="badgeTotalClientes"><?= $totalClientes ?></span>
                <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprise SST" style="height: 50px; margin-left: 15px;">
            </div>
        </div>
    </nav>

    <div style="height: 80px;"></div>

    <div class="container-fluid px-4" style="max-width: 1400px; margin: 0 auto;">

        <!-- Filtros -->
        <div class="filter-section">
            <div class="row g-3 align-items-end">
                <?php if ($role === 'admin'): ?>
                <div class="col-md-3">
                    <label class="form-label fw-bold"><i class="fas fa-user-tie me-1"></i> CONSULTOR</label>
                    <select id="filterConsultor" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach ($consultoresUnicos as $c): ?>
                            <option value="<?= esc($c) ?>"><?= esc($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <div class="col-md-3">
                    <label class="form-label fw-bold"><i class="fas fa-building me-1"></i> CLIENTE</label>
                    <select id="filterCliente" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach ($clientesUnicos as $cl): ?>
                            <option value="<?= esc($cl) ?>"><?= esc($cl) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold"><i class="fas fa-tags me-1"></i> TIPO DE CLIENTE</label>
                    <select id="filterEstandar" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach ($estandaresUnicos as $e): ?>
                            <option value="<?= esc($e) ?>"><?= esc($e) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold"><i class="fas fa-calendar me-1"></i> MES EVALUADO</label>
                    <select id="filterMes" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach ($fechasMes as $fm): ?>
                            <option value="<?= esc($fm) ?>"><?= esc($fm) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button id="btnClear" class="btn btn-clear w-100">
                        <i class="fas fa-eraser me-1"></i> Limpiar Filtros
                    </button>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row">
            <!-- Donut -->
            <div class="col-md-4">
                <div class="chart-container">
                    <h5>Distribucion por Consultor</h5>
                    <canvas id="chartDonut"></canvas>
                </div>
            </div>
            <!-- Línea evolutiva -->
            <div class="col-md-8">
                <div class="chart-container">
                    <h5>Evolucion % Cumplimiento por Consultor</h5>
                    <canvas id="chartLinea"></canvas>
                </div>
            </div>
        </div>

        <!-- Slider porcentaje -->
        <div class="filter-section">
            <div class="row align-items-center">
                <div class="col-md-2">
                    <label class="form-label fw-bold"><i class="fas fa-calendar-alt me-1"></i> FECHA DE EXTRACCION</label>
                    <select id="filterFechaExtraccion" class="form-select">
                        <option value="">Todas</option>
                        <?php foreach ($fechasMes as $fm): ?>
                            <option value="<?= esc($fm) ?>"><?= esc($fm) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">porcentaje_cumplimiento</label>
                    <div class="d-flex align-items-center gap-2">
                        <input type="number" class="form-control form-control-sm" id="filterPctMin" min="0" max="100" value="0" style="width: 80px;">
                        <input type="range" class="form-range flex-grow-1" id="rangeMin" min="0" max="100" value="0">
                        <input type="range" class="form-range flex-grow-1" id="rangeMax" min="0" max="100" value="100">
                        <input type="number" class="form-control form-control-sm" id="filterPctMax" min="0" max="100" value="100" style="width: 80px;">
                    </div>
                </div>
            </div>
        </div>

        <!-- DataTable -->
        <div class="table-container">
            <div class="table-responsive">
                <table id="tablaEstandares" class="table table-striped table-bordered" style="width: 100%;">
                    <thead class="table-dark">
                        <tr>
                            <th>nombre_cliente</th>
                            <th>total_valor</th>
                            <th>total_puntaje</th>
                            <th>porcentaje_cumplimiento</th>
                        </tr>
                    </thead>
                    <tbody id="tablaBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <footer>
        <p class="mb-0"><strong>Cycloid Talent SAS</strong> - Todos los derechos reservados &copy; <?= date('Y') ?></p>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // ========== DATA ==========
        const originalData = <?= json_encode($registros) ?>;
        const role = '<?= $role ?>';

        const colorsLine = [
            '#4facfe', '#f5576c', '#43e97b', '#fa709a', '#667eea',
            '#ffc107', '#20c997', '#e44d26', '#6f42c1', '#fd7e14'
        ];
        const colorsDonut = [
            'rgba(79, 172, 254, 0.8)', 'rgba(245, 87, 108, 0.8)',
            'rgba(67, 233, 123, 0.8)', 'rgba(250, 112, 154, 0.8)',
            'rgba(102, 126, 234, 0.8)', 'rgba(255, 193, 7, 0.8)',
            'rgba(32, 201, 151, 0.8)', 'rgba(228, 77, 38, 0.8)'
        ];

        // ========== CHARTS ==========
        let chartLinea = null;
        let chartDonut = null;

        // ========== SELECT2 ==========
        <?php if ($role === 'admin'): ?>
        $('#filterConsultor').select2({ theme: 'bootstrap-5', placeholder: 'Todos', allowClear: true, width: '100%' });
        <?php endif; ?>
        $('#filterCliente').select2({ theme: 'bootstrap-5', placeholder: 'Todos', allowClear: true, width: '100%' });

        // ========== DATATABLE ==========
        const dt = $('#tablaEstandares').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
            pageLength: 25,
            order: [[3, 'asc']],
            columns: [
                { data: 'nombre_cliente' },
                { data: 'total_valor' },
                { data: 'total_puntaje' },
                { data: 'porcentaje_cumplimiento' }
            ]
        });

        // ========== FILTERS ==========
        function getFiltered() {
            let data = [...originalData];

            const consultor = $('#filterConsultor').val();
            const cliente = $('#filterCliente').val();
            const estandar = $('#filterEstandar').val();
            const mes = $('#filterMes').val();
            const fechaExt = $('#filterFechaExtraccion').val();
            const pctMin = parseFloat($('#filterPctMin').val()) || 0;
            const pctMax = parseFloat($('#filterPctMax').val()) || 100;

            if (consultor) data = data.filter(r => r.nombre_consultor === consultor);
            if (cliente) data = data.filter(r => r.nombre_cliente === cliente);
            if (estandar) data = data.filter(r => r.estandares === estandar);
            if (mes) data = data.filter(r => r.fecha_extraccion && r.fecha_extraccion.substring(0, 7) === mes);
            if (fechaExt) data = data.filter(r => r.fecha_extraccion && r.fecha_extraccion.substring(0, 7) === fechaExt);
            data = data.filter(r => {
                const pct = parseFloat(r.porcentaje_cumplimiento) || 0;
                return pct >= pctMin && pct <= pctMax;
            });

            return data;
        }

        function updateAll() {
            const filtered = getFiltered();
            updateLineChart(filtered);
            updateDonutChart(filtered);
            updateTable(filtered);
            updateBadge(filtered);
        }

        // ========== LINE CHART ==========
        function updateLineChart(data) {
            const consultores = [...new Set(data.map(r => r.nombre_consultor))].filter(Boolean);
            const meses = [...new Set(data.map(r => r.fecha_extraccion ? r.fecha_extraccion.substring(0, 7) : null).filter(Boolean))].sort();

            const datasets = consultores.map((consultor, idx) => {
                const points = meses.map(mes => {
                    const regs = data.filter(r => r.nombre_consultor === consultor && r.fecha_extraccion && r.fecha_extraccion.substring(0, 7) === mes);
                    if (regs.length === 0) return null;
                    const avg = regs.reduce((sum, r) => sum + parseFloat(r.porcentaje_cumplimiento || 0), 0) / regs.length;
                    return Math.round(avg * 100) / 100;
                });
                return {
                    label: consultor,
                    data: points,
                    borderColor: colorsLine[idx % colorsLine.length],
                    backgroundColor: colorsLine[idx % colorsLine.length],
                    fill: false,
                    tension: 0.3,
                    spanGaps: true,
                    pointRadius: 5,
                    pointHoverRadius: 7
                };
            });

            const labels = meses.map(m => {
                const [y, mo] = m.split('-');
                const names = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
                return names[parseInt(mo) - 1] + ' ' + y;
            });

            if (chartLinea) chartLinea.destroy();
            chartLinea = new Chart(document.getElementById('chartLinea').getContext('2d'), {
                type: 'line',
                data: { labels, datasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { position: 'top', labels: { usePointStyle: true } }
                    },
                    scales: {
                        y: { beginAtZero: true, max: 100, title: { display: true, text: '% Cumplimiento' } }
                    }
                }
            });
        }

        // ========== DONUT CHART ==========
        function updateDonutChart(data) {
            const consultores = [...new Set(data.map(r => r.nombre_consultor))].filter(Boolean);

            // Para cada consultor, contar registros
            const counts = consultores.map(c => data.filter(r => r.nombre_consultor === c).length);
            const total = counts.reduce((a, b) => a + b, 0) || 1;
            const labels = consultores.map((c, i) => {
                const pct = ((counts[i] / total) * 100).toFixed(1);
                return c + ' (' + pct + '%)';
            });

            if (chartDonut) chartDonut.destroy();
            chartDonut = new Chart(document.getElementById('chartDonut').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: consultores,
                    datasets: [{
                        data: counts,
                        backgroundColor: colorsDonut.slice(0, consultores.length),
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { position: 'bottom', labels: { usePointStyle: true } },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    const pct = ((ctx.raw / total) * 100).toFixed(1);
                                    return ctx.label + ': ' + pct + '%';
                                }
                            }
                        }
                    }
                }
            });
        }

        // ========== TABLE ==========
        function updateTable(data) {
            // Para la tabla, agrupar por cliente tomando el último registro
            const latest = {};
            data.forEach(r => {
                const key = r.nombre_cliente;
                if (!latest[key] || r.fecha_extraccion > latest[key].fecha_extraccion) {
                    latest[key] = r;
                }
            });

            dt.clear();
            Object.values(latest).forEach(r => {
                dt.row.add({
                    nombre_cliente: r.nombre_cliente,
                    total_valor: r.total_valor,
                    total_puntaje: r.total_puntaje,
                    porcentaje_cumplimiento: r.porcentaje_cumplimiento
                });
            });
            dt.draw();
        }

        // ========== BADGE ==========
        function updateBadge(data) {
            const clientes = [...new Set(data.map(r => r.nombre_cliente))];
            document.getElementById('badgeTotalClientes').textContent = clientes.length;
        }

        // ========== EVENT LISTENERS ==========
        <?php if ($role === 'admin'): ?>
        $('#filterConsultor').on('change', updateAll);
        <?php endif; ?>
        $('#filterCliente').on('change', updateAll);
        $('#filterEstandar').on('change', updateAll);
        $('#filterMes').on('change', updateAll);
        $('#filterFechaExtraccion').on('change', updateAll);
        $('#filterPctMin').on('change', updateAll);
        $('#filterPctMax').on('change', updateAll);
        $('#rangeMin').on('input', function() {
            $('#filterPctMin').val(this.value);
            updateAll();
        });
        $('#rangeMax').on('input', function() {
            $('#filterPctMax').val(this.value);
            updateAll();
        });

        // Limpiar
        $('#btnClear').on('click', function () {
            <?php if ($role === 'admin'): ?>
            $('#filterConsultor').val('').trigger('change');
            <?php endif; ?>
            $('#filterCliente').val('').trigger('change');
            $('#filterEstandar').val('');
            $('#filterMes').val('');
            $('#filterFechaExtraccion').val('');
            $('#filterPctMin').val(0);
            $('#filterPctMax').val(100);
            $('#rangeMin').val(0);
            $('#rangeMax').val(100);
            updateAll();
        });

        // Inicializar
        updateAll();
    });
    </script>
</body>

</html>
