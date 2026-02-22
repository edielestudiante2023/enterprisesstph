<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Plan de Trabajo Anual</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
    <style>
        body {
            padding: 20px;
        }

        .dataTables_wrapper .dataTables_filter {
            float: right;
            text-align: right;
        }

        td.editable {
            cursor: pointer;
        }

        .dt-buttons {
            margin-bottom: 15px;
        }

        .dt-buttons .btn {
            margin-right: 5px;
        }

        .dt-button-collection {
            padding: 8px;
            max-height: 400px;
            overflow-y: auto;
        }

        .dt-button-collection .dt-button {
            display: block !important;
            padding: 6px 14px !important;
            margin: 2px 0 !important;
            width: 100%;
            text-align: left;
            border-radius: 4px;
            font-size: 13px;
        }

        .dt-button-collection .dt-button.active {
            background: #4e73df !important;
            color: #fff !important;
        }

        .dt-button {
            display: inline-block !important;
            padding: 8px 16px !important;
            margin: 5px !important;
        }

        .table-scroll-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .btn-warning {
            color: #000;
            background-color: #ffc107;
            border-color: #ffc107;
        }

        .btn-warning:hover {
            color: #000;
            background-color: #ffca2c;
            border-color: #ffc720;
        }

        /* Estilos mejorados para los filtros */
        .filter-card {
            background: #fff;
            border: 1px solid #e3e6f0;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .filter-section {
            border-left: 3px solid #4e73df;
            padding-left: 15px;
            margin-bottom: 1rem;
        }

        .filter-section h6 {
            color: #4e73df;
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-label {
            font-weight: 500;
            color: #5a5c69;
            margin-bottom: 0.3rem;
        }

        .form-label i {
            margin-right: 0.5rem;
            color: #858796;
        }

        .form-control, .form-select {
            border: 1px solid #d1d3e2;
            border-radius: 6px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.15);
        }

        .required-field {
            border-left: 3px solid #e74a3b;
        }

        .btn-group-filters {
            background: #f8f9fc;
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid #e3e6f0;
        }

        .btn-primary {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            border: none;
            border-radius: 6px;
            font-weight: 500;
            padding: 0.6rem 1.2rem;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(78, 115, 223, 0.3);
        }

        .btn-success {
            background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
            border: none;
            border-radius: 6px;
            font-weight: 500;
            padding: 0.6rem 1.2rem;
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #545b62 100%);
            border: none;
            border-radius: 6px;
            font-weight: 500;
            padding: 0.6rem 1.2rem;
        }

        .date-range-group {
            background: #f1f3ff;
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid #d1d9ff;
        }

        .quick-filters {
            background: #fff8e1;
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid #ffe082;
        }

        /* Estilos para tarjeta de contrato */
        .contract-card {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border-radius: 12px;
            color: white;
            box-shadow: 0 4px 15px rgba(17, 153, 142, 0.3);
        }

        .contract-card .contract-header {
            font-size: 1.1rem;
            font-weight: 600;
            border-bottom: 1px solid rgba(255,255,255,0.3);
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .contract-card .contract-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .contract-card .contract-item:last-child {
            border-bottom: none;
        }

        .contract-card .contract-label {
            font-size: 0.85rem;
            opacity: 0.9;
        }

        .contract-card .contract-value {
            font-weight: 600;
            font-size: 0.95rem;
        }

        .contract-card .frecuencia-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 1rem;
            text-transform: uppercase;
        }

        .frecuencia-mensual {
            background-color: #ffc107;
            color: #000;
        }

        .frecuencia-bimensual {
            background-color: #17a2b8;
            color: #fff;
        }

        .frecuencia-trimestral {
            background-color: #6f42c1;
            color: #fff;
        }

        .frecuencia-default {
            background-color: #6c757d;
            color: #fff;
        }

        .contract-status {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-activo {
            background-color: #28a745;
        }

        .status-vencido {
            background-color: #dc3545;
        }

        .status-cancelado {
            background-color: #6c757d;
        }

        .no-contract-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            color: white;
            text-align: center;
            padding: 20px;
        }

        /* Estilos para tarjetas clickeables */
        .card-clickable {
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .card-clickable:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .card-clickable.active {
            border: 3px solid #ffeb3b !important;
            box-shadow: 0 0 25px rgba(255, 235, 59, 0.8), 0 0 10px rgba(255, 255, 255, 0.5) !important;
            transform: scale(1.08) !important;
            position: relative;
        }

        .card-clickable.active::after {
            content: '✓';
            position: absolute;
            top: 5px;
            right: 5px;
            background: #ffeb3b;
            color: #000;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 16px;
        }

        .card-year {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            min-height: 80px;
        }

        .card-month {
            min-height: 70px;
        }

        .card-status {
            min-height: 90px;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #4e73df;
            border-left: 4px solid #4e73df;
            padding-left: 10px;
            margin: 20px 0 15px 0;
        }

        /* Estilos para botones de gestión rápida de meses */
        .btn-month {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: 2px solid #6c757d;
            background-color: #fff;
            color: #495057;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            padding: 0;
        }

        .btn-month:hover {
            background-color: #007bff;
            color: #fff;
            border-color: #007bff;
            transform: scale(1.1);
        }

        .btn-month.has-date {
            background-color: #28a745;
            color: #fff;
            border-color: #28a745;
        }

        .btn-month:active {
            transform: scale(0.95);
        }

        .month-buttons {
            max-width: 200px;
        }

        /* ============ TEXTO TRUNCADO EXPANDIBLE ============ */
        .cell-truncate {
            max-height: 60px;
            overflow: hidden;
            position: relative;
            transition: max-height 0.3s ease;
        }
        .cell-truncate.expanded {
            max-height: 2000px;
        }
        .btn-expand {
            display: inline-block;
            font-size: 11px;
            color: #4e73df;
            cursor: pointer;
            font-weight: 600;
            margin-top: 2px;
            user-select: none;
        }
        .btn-expand:hover {
            color: #224abe;
            text-decoration: underline;
        }

        /* ============ BADGES DE ESTADO ============ */
        .estado-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            white-space: nowrap;
        }
        .estado-abierta { background: #fff3e0; color: #e65100; border: 1px solid #ffcc80; }
        .estado-cerrada { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
        .estado-gestionando { background: #e3f2fd; color: #1565c0; border: 1px solid #90caf9; }
        .estado-cerrada-sin { background: #fce4ec; color: #c62828; border: 1px solid #ef9a9a; }

        /* ============ MINI PROGRESS BAR ============ */
        .mini-progress {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 100px;
        }
        .mini-progress-bar {
            flex: 1;
            height: 14px;
            background: #dee2e6;
            border-radius: 7px;
            overflow: hidden;
            min-width: 60px;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
        }
        .mini-progress-fill {
            height: 100%;
            border-radius: 7px;
            transition: width 0.3s ease;
            min-width: 2px;
        }
        .mini-progress-text {
            font-size: 13px;
            font-weight: 800;
            min-width: 40px;
            text-align: right;
            color: #333;
        }

        /* ============ BOTONES ACCIONES COMPACTOS ============ */
        .btn-action {
            width: 30px;
            height: 30px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            font-size: 13px;
            border: none;
            transition: all 0.2s ease;
        }
        .btn-action:hover { transform: scale(1.1); }
        .btn-action-edit { background: #ffc107; color: #000; }
        .btn-action-edit:hover { background: #ffca2c; color: #000; }
        .btn-action-delete { background: #dc3545; color: #fff; }
        .btn-action-delete:hover { background: #e04050; color: #fff; }
        .action-group { display: flex; gap: 4px; justify-content: center; }

        /* ============ FILAS COMPACTAS Y TABLA ESTILIZADA ============ */
        #ptaTable {
            border-collapse: separate;
            border-spacing: 0;
        }
        #ptaTable thead th {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: #fff;
            font-size: 13px;
            font-weight: 600;
            padding: 10px 8px;
            white-space: nowrap;
            border: none;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        #ptaTable thead th:first-child { border-radius: 8px 0 0 0; }
        #ptaTable thead th:last-child { border-radius: 0 8px 0 0; }
        #ptaTable tbody td {
            vertical-align: middle;
            padding: 8px 8px;
            font-size: 14.5px;
            border-bottom: 1px solid #e9ecef;
        }
        #ptaTable tbody tr:hover td {
            background-color: #f0f4ff !important;
        }
        #ptaTable tbody tr:nth-child(even) td {
            background-color: #f8f9fc;
        }

        /* ============ ACORDEON DE FILTROS ============ */
        .filter-toggle-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .filter-toggle-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        .filter-toggle-btn .fa-chevron-down {
            transition: transform 0.3s ease;
        }
        .filter-toggle-btn.collapsed .fa-chevron-down {
            transform: rotate(-90deg);
        }
        #cardFiltersPanel {
            transition: all 0.35s ease;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <!-- Enlaces de navegación -->
        <div class="d-flex gap-2 mb-3">
            <a href="<?= base_url('/dashboardconsultant') ?>" class="btn btn-primary btn-sm">Ir a DashBoard</a>
            <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#renewPlanModal">
                <i class="fas fa-sync-alt"></i> Renovar Plan de Trabajo
            </button>
            <?php if (!empty($filters['cliente'])): ?>
            <button type="button" id="btnSocializarPlanTrabajo" class="btn btn-success btn-sm" title="Enviar Plan de Trabajo por email al cliente y consultor">
                <i class="fas fa-envelope"></i> Socializar Plan de Trabajo
            </button>
            <?php endif; ?>
        </div>

        <!-- Mensaje informativo -->
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle"></i>
            <strong>Filtros Dinámicos:</strong> Las tarjetas de año, estado y mes son interactivas.
            Haz clic sobre ellas para filtrar la tabla instantáneamente. Puedes combinar múltiples filtros.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <!-- Sección de Información del Contrato y Filtros por Año -->
        <div class="row mb-4">
            <!-- Tarjeta de Contrato -->
            <div class="col-lg-4 mb-3">
                <?php if (!empty($lastContract)): ?>
                    <?php
                        // Determinar la clase de frecuencia
                        $frecuencia = strtolower($lastContract['frecuencia_visitas'] ?? '');
                        $frecuenciaClass = 'frecuencia-default';
                        if (strpos($frecuencia, 'mensual') !== false && strpos($frecuencia, 'bimensual') === false) {
                            $frecuenciaClass = 'frecuencia-mensual';
                        } elseif (strpos($frecuencia, 'bimensual') !== false) {
                            $frecuenciaClass = 'frecuencia-bimensual';
                        } elseif (strpos($frecuencia, 'trimestral') !== false) {
                            $frecuenciaClass = 'frecuencia-trimestral';
                        }

                        // Determinar estado del contrato
                        $estadoContrato = strtolower($lastContract['estado'] ?? 'activo');
                        $estadoClass = 'status-' . $estadoContrato;
                    ?>
                    <div class="contract-card p-3 h-100">
                        <div class="contract-header">
                            <i class="fas fa-file-contract me-2"></i> Contrato
                            <span class="contract-status <?= $estadoClass ?> float-end">
                                <?= esc(ucfirst($lastContract['estado'] ?? 'Activo')) ?>
                            </span>
                        </div>

                        <div class="text-center mb-2">
                            <strong style="font-size: 0.95rem;"><?= esc($selectedClient['nombre_cliente'] ?? 'Cliente') ?></strong>
                        </div>

                        <div class="text-center mb-3">
                            <span class="frecuencia-badge <?= $frecuenciaClass ?>">
                                <i class="fas fa-calendar-check me-1"></i>
                                <?= esc($lastContract['frecuencia_visitas'] ?? 'No definida') ?>
                            </span>
                        </div>

                        <div class="contract-item">
                            <span class="contract-label"><i class="fas fa-hashtag me-1"></i> Número:</span>
                            <span class="contract-value"><?= esc($lastContract['numero_contrato'] ?? 'N/A') ?></span>
                        </div>
                        <div class="contract-item">
                            <span class="contract-label"><i class="fas fa-play-circle me-1"></i> Inicio:</span>
                            <span class="contract-value"><?= !empty($lastContract['fecha_inicio']) ? date('d/m/Y', strtotime($lastContract['fecha_inicio'])) : 'N/A' ?></span>
                        </div>
                        <div class="contract-item">
                            <span class="contract-label"><i class="fas fa-stop-circle me-1"></i> Fin:</span>
                            <span class="contract-value"><?= !empty($lastContract['fecha_fin']) ? date('d/m/Y', strtotime($lastContract['fecha_fin'])) : 'N/A' ?></span>
                        </div>
                        <div class="mt-3 text-center">
                            <a href="<?= base_url('/contracts/view/' . $lastContract['id_contrato']) ?>" class="btn btn-light btn-sm">
                                <i class="fas fa-eye me-1"></i> Ver Contrato
                            </a>
                        </div>
                    </div>
                <?php elseif (!empty($filters['cliente'])): ?>
                    <div class="no-contract-card h-100 d-flex flex-column justify-content-center">
                        <i class="fas fa-file-contract fa-3x mb-3 opacity-75"></i>
                        <h5>Sin Contrato Registrado</h5>
                        <p class="mb-3 opacity-75">Este cliente no tiene contratos registrados en el sistema.</p>
                        <a href="<?= base_url('/contracts/create/' . $filters['cliente']) ?>" class="btn btn-light btn-sm">
                            <i class="fas fa-plus me-1"></i> Crear Contrato
                        </a>
                    </div>
                <?php else: ?>
                    <div class="no-contract-card h-100 d-flex flex-column justify-content-center">
                        <i class="fas fa-hand-pointer fa-3x mb-3 opacity-75"></i>
                        <h5>Seleccione un Cliente</h5>
                        <p class="mb-0 opacity-75">Seleccione un cliente para ver la información de su contrato.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Tarjetas de Año (dentro del accordion) -->
            <div class="col-lg-8">
                <!-- Toggle de filtros por tarjetas -->
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <button class="filter-toggle-btn collapsed" type="button"
                            data-bs-toggle="collapse" data-bs-target="#cardFiltersPanel"
                            aria-expanded="false">
                        <i class="fas fa-layer-group me-2"></i>Filtros por Tarjetas (Año / Estado / Mes)
                        <i class="fas fa-chevron-down ms-2"></i>
                    </button>
                    <button type="button" id="btnClearCardFilters" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-times"></i> Limpiar Filtros
                    </button>
                </div>
            </div>
        </div>

        <div class="collapse" id="cardFiltersPanel">
            <!-- Tarjetas de Año -->
            <div class="section-title">
                <i class="fas fa-calendar-alt"></i> Filtrar por Año
            </div>
            <div class="row mt-2 mb-4" id="yearCards">
                <!-- Se generarán dinámicamente con JavaScript -->
            </div>

            <!-- Tarjetas de Estados (clickeables) -->
            <div class="section-title">
                <i class="fas fa-tasks"></i> Filtrar por Estado
            </div>
            <div class="row mb-4">
                <div class="col-md-2">
                    <div class="card text-white bg-primary card-clickable card-status" data-status="ABIERTA">
                        <div class="card-body text-center">
                            <h5 class="card-title">Activas</h5>
                            <p class="card-text display-6" id="countActivas">0</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card text-white bg-danger card-clickable card-status" data-status="CERRADA">
                        <div class="card-body text-center">
                            <h5 class="card-title">Cerradas</h5>
                            <p class="card-text display-6" id="countCerradas">0</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card text-white bg-warning card-clickable card-status" data-status="GESTIONANDO">
                        <div class="card-body text-center">
                            <h5 class="card-title">Gestionando</h5>
                            <p class="card-text display-6" id="countGestionando">0</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-dark card-clickable card-status" data-status="CERRADA SIN EJECUCIÓN">
                        <div class="card-body text-center">
                            <h5 class="card-title">Cerradas Sin Ejecución</h5>
                            <p class="card-text display-6" id="countCerradasSinEjecucion">0</p>
                        </div>
                    </div>
                </div>
                <!-- Tarjeta para total de actividades -->
                <div class="col-md-3">
                    <div class="card text-white bg-secondary card-clickable card-status" data-status="ALL">
                        <div class="card-body text-center">
                            <h5 class="card-title">Total</h5>
                            <p class="card-text display-6" id="countTotal">0</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tarjetas mensuales (clickeables) -->
            <div class="section-title">
                <i class="fas fa-calendar-week"></i> Filtrar por Mes
            </div>
            <div class="row mb-4">
                <div class="col-6 col-md-1">
                    <div class="card text-white bg-info card-clickable card-month" data-month="1">
                        <div class="card-body p-2">
                            <h6 class="card-title text-center mb-0">Enero</h6>
                            <p class="card-text text-center" id="countEnero">0</p>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-1">
                    <div class="card text-white bg-info card-clickable card-month" data-month="2">
                        <div class="card-body p-2">
                            <h6 class="card-title text-center mb-0">Febrero</h6>
                            <p class="card-text text-center" id="countFebrero">0</p>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-1">
                    <div class="card text-white bg-info card-clickable card-month" data-month="3">
                        <div class="card-body p-2">
                            <h6 class="card-title text-center mb-0">Marzo</h6>
                            <p class="card-text text-center" id="countMarzo">0</p>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-1">
                    <div class="card text-white bg-info card-clickable card-month" data-month="4">
                        <div class="card-body p-2">
                            <h6 class="card-title text-center mb-0">Abril</h6>
                            <p class="card-text text-center" id="countAbril">0</p>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-1">
                    <div class="card text-white bg-info card-clickable card-month" data-month="5">
                        <div class="card-body p-2">
                            <h6 class="card-title text-center mb-0">Mayo</h6>
                            <p class="card-text text-center" id="countMayo">0</p>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-1">
                    <div class="card text-white bg-info card-clickable card-month" data-month="6">
                        <div class="card-body p-2">
                            <h6 class="card-title text-center mb-0">Junio</h6>
                            <p class="card-text text-center" id="countJunio">0</p>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-1">
                    <div class="card text-white bg-info card-clickable card-month" data-month="7">
                        <div class="card-body p-2">
                            <h6 class="card-title text-center mb-0">Julio</h6>
                            <p class="card-text text-center" id="countJulio">0</p>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-1">
                    <div class="card text-white bg-info card-clickable card-month" data-month="8">
                        <div class="card-body p-2">
                            <h6 class="card-title text-center mb-0">Agosto</h6>
                            <p class="card-text text-center" id="countAgosto">0</p>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-1">
                    <div class="card text-white bg-info card-clickable card-month" data-month="9">
                        <div class="card-body p-2">
                            <h6 class="card-title text-center mb-0">Sept.</h6>
                            <p class="card-text text-center" id="countSeptiembre">0</p>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-1">
                    <div class="card text-white bg-info card-clickable card-month" data-month="10">
                        <div class="card-body p-2">
                            <h6 class="card-title text-center mb-0">Oct.</h6>
                            <p class="card-text text-center" id="countOctubre">0</p>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-1">
                    <div class="card text-white bg-info card-clickable card-month" data-month="11">
                        <div class="card-body p-2">
                            <h6 class="card-title text-center mb-0">Nov.</h6>
                            <p class="card-text text-center" id="countNoviembre">0</p>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-1">
                    <div class="card text-white bg-info card-clickable card-month" data-month="12">
                        <div class="card-body p-2">
                            <h6 class="card-title text-center mb-0">Dic.</h6>
                            <p class="card-text text-center" id="countDiciembre">0</p>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- Fin cardFiltersPanel collapse -->

        <h1 class="mb-4">Plan de Trabajo Anual Cliente</h1>
        
        <!-- FORMULARIO DE FILTROS MEJORADO -->
        <div class="filter-card">
            <form id="filterForm" method="get" action="<?= site_url('/pta-cliente-nueva/list') ?>">

                <!-- Filtros en una sola fila -->
                <div class="filter-section">
                    <h6><i class="fas fa-filter"></i> Filtros de Búsqueda</h6>
                    <div class="row mb-3">
                        <!-- Cliente (Campo requerido) -->
                        <div class="col-lg-4">
                            <label for="cliente" class="form-label">
                                <i class="fas fa-user-tie"></i> Cliente *
                            </label>
                            <select name="cliente" id="cliente" class="form-select required-field">
                                <option value="">Seleccione un Cliente</option>
                                <?php if (isset($clients) && !empty($clients)): ?>
                                    <?php foreach ($clients as $client): ?>
                                        <option value="<?= esc($client['id_cliente']) ?>"
                                            <?= (service('request')->getGet('cliente') == $client['id_cliente']) ? 'selected' : '' ?>>
                                            <?= esc($client['nombre_cliente']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Fecha Desde -->
                        <div class="col-lg-4">
                            <label for="fecha_desde" class="form-label">
                                <i class="fas fa-calendar-plus"></i> Fecha Desde
                            </label>
                            <input type="date" name="fecha_desde" id="fecha_desde"
                                   class="form-control"
                                   value="<?= esc(service('request')->getGet('fecha_desde')) ?>">
                        </div>

                        <!-- Fecha Hasta -->
                        <div class="col-lg-4">
                            <label for="fecha_hasta" class="form-label">
                                <i class="fas fa-calendar-minus"></i> Fecha Hasta
                            </label>
                            <input type="date" name="fecha_hasta" id="fecha_hasta"
                                   class="form-control"
                                   value="<?= esc(service('request')->getGet('fecha_hasta')) ?>">
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="btn-group-filters">
                    <div class="row">
                        <div class="col-md-8">
                            <button type="submit" class="btn btn-primary me-2" id="btnBuscar">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                            <button type="button" id="btnMostrarTodos" class="btn btn-success me-2">
                                <i class="fas fa-eye"></i> Ver Todos
                            </button>
                            <button type="reset" id="resetFilters" class="btn btn-secondary me-2">
                                <i class="fas fa-undo"></i> Limpiar
                            </button>
                        </div>
                        <div class="col-md-4 text-end">
                            <button type="button" id="btnCalificarCerradas" class="btn btn-warning me-2">
                                <i class="fas fa-check-double"></i> Calificar Cerradas
                            </button>
                            <a href="<?= base_url('/pta-cliente-nueva/add?' . http_build_query($filters)) ?>" class="btn btn-info">
                                <i class="fas fa-plus"></i> Nuevo
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Mostrar la tabla solo si existen registros -->
        <?php if (!empty($records)): ?>
            <div class="table-scroll-wrapper">
                <table id="ptaTable" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>Acciones</th>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Fuente de la Actividad</th>
                            <th>PHVA</th>
                            <th>Numeral Plan Trabajo</th>
                            <th>Actividad</th>
                            <th>Responsable Sugerido</th>
                            <th>Fecha Propuesta</th>
                            <th>Fecha Cierre</th>
                            <th>Estado Actividad</th>
                            <th>Porcentaje Avance</th>
                            <th>Observaciones</th>
                            <th>Responsable Definido</th>
                            <th>Semana</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                            <th style="min-width: 200px;">📅 Gestión Rápida</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $row): ?>
                            <tr>
                                <td>
                                    <div class="action-group">
                                        <a href="<?= base_url('/pta-cliente-nueva/edit/' . esc($row['id_ptacliente']) . '?' . http_build_query($filters)) ?>"
                                           class="btn-action btn-action-edit" title="Editar">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <a href="<?= base_url('/pta-cliente-nueva/delete/' . esc($row['id_ptacliente']) . '?' . http_build_query($filters)) ?>"
                                           class="btn-action btn-action-delete" title="Eliminar"
                                           onclick="return confirm('¿Seguro que deseas eliminar este registro?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                                <td><?= esc($row['id_ptacliente']) ?></td>
                                <td class="editable"><?= esc($row['nombre_cliente']) ?></td>
                                <td><?= esc($row['tipo_servicio']) ?></td>
                                <td class="editable"><?= esc($row['phva_plandetrabajo']) ?></td>
                                <td class="editable"><?= esc($row['numeral_plandetrabajo']) ?></td>
                                <td class="editable">
                                    <div class="cell-truncate"><?= esc($row['actividad_plandetrabajo']) ?></div>
                                </td>
                                <td class="editable"><?= esc($row['responsable_sugerido_plandetrabajo']) ?></td>
                                <td class="editable"><?= esc($row['fecha_propuesta']) ?></td>
                                <td class="editable"><?= esc($row['fecha_cierre']) ?></td>
                                <td class="editable">
                                    <?php
                                    $estado = $row['estado_actividad'];
                                    $badgeClass = 'estado-abierta';
                                    if ($estado === 'CERRADA') $badgeClass = 'estado-cerrada';
                                    elseif ($estado === 'GESTIONANDO') $badgeClass = 'estado-gestionando';
                                    elseif ($estado === 'CERRADA SIN EJECUCIÓN') $badgeClass = 'estado-cerrada-sin';
                                    ?>
                                    <span class="estado-badge <?= $badgeClass ?>"><?= esc($estado) ?></span>
                                </td>
                                <td class="editable">
                                    <?php
                                    $pct = (float)($row['porcentaje_avance'] ?? 0);
                                    $barColor = '#e74a3b';
                                    if ($pct >= 100) $barColor = '#1cc88a';
                                    elseif ($pct >= 50) $barColor = '#4e73df';
                                    elseif ($pct > 0) $barColor = '#f6c23e';
                                    $barWidth = max($pct, 2);
                                    ?>
                                    <div class="mini-progress">
                                        <div class="mini-progress-bar">
                                            <div class="mini-progress-fill" style="width:<?= $barWidth ?>%;background:<?= $barColor ?>"></div>
                                        </div>
                                        <span class="mini-progress-text"><?= number_format($pct, 0) ?>%</span>
                                    </div>
                                </td>
                                <td class="editable">
                                    <div class="cell-truncate"><?= esc($row['observaciones']) ?></div>
                                </td>
                                <td><?= esc($row['responsable_definido_paralaactividad']) ?></td>
                                <td><?= esc($row['semana']) ?></td>
                                <td><?= esc($row['created_at']) ?></td>
                                <td><?= esc($row['updated_at']) ?></td>
                                <td class="text-center">
                                    <!-- Botones de meses (1-12) organizados en 3 filas de 4 -->
                                    <div class="month-buttons" style="display: grid; grid-template-columns: repeat(4, 32px); gap: 4px; justify-content: center;">
                                        <?php
                                        $mesesEspanol = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                                        for ($month = 1; $month <= 12; $month++):
                                        ?>
                                            <button type="button"
                                                    class="btn-month"
                                                    data-id="<?= esc($row['id_ptacliente']) ?>"
                                                    data-month="<?= $month ?>"
                                                    title="<?= $mesesEspanol[$month - 1] ?>">
                                                <?= $month ?>
                                            </button>
                                        <?php endfor; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th></th>
                            <th><input type="text" placeholder="Buscar ID" class="form-control form-control-sm"></th>
                            <th><input type="text" placeholder="Buscar Cliente" class="form-control form-control-sm"></th>
                            <th></th>
                            <th><input type="text" placeholder="Buscar PHVA" class="form-control form-control-sm"></th>
                            <th><input type="text" placeholder="Buscar Numeral Plan Trabajo" class="form-control form-control-sm"></th>
                            <th><input type="text" placeholder="Buscar Actividad" class="form-control form-control-sm"></th>
                            <th><input type="text" placeholder="Buscar Responsable Sugerido" class="form-control form-control-sm"></th>
                            <th><input type="date" placeholder="Buscar Fecha Propuesta" class="form-control form-control-sm"></th>
                            <th><input type="date" placeholder="Buscar Fecha Cierre" class="form-control form-control-sm"></th>
                            <th>
                                <select class="form-select form-select-sm">
                                    <option value="">Todos</option>
                                    <option value="ABIERTA">ABIERTA</option>
                                    <option value="CERRADA">CERRADA</option>
                                    <option value="GESTIONANDO">GESTIONANDO</option>
                                    <option value="CERRADA SIN EJECUCIÓN">CERRADA SIN EJECUCIÓN</option>
                                </select>
                            </th>
                            <th><input type="text" placeholder="Buscar Porcentaje Avance" class="form-control form-control-sm"></th>
                            <th><input type="text" placeholder="Buscar Observaciones" class="form-control form-control-sm"></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php endif; ?>

        <!-- Mensajes flash -->
        <?php if (session()->has('message')): ?>
            <div class="alert alert-success mt-3"><?= session('message') ?></div>
        <?php endif; ?>
        <?php if (session()->has('error')): ?>
            <div class="alert alert-danger mt-3"><?= session('error') ?></div>
        <?php endif; ?>
        <?php if (session()->has('warning')): ?>
            <div class="alert alert-warning mt-3">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?= session('warning') ?>
            </div>
        <?php endif; ?>
        <?php if (session()->has('info')): ?>
            <div class="alert alert-info mt-3">
                <i class="fas fa-info-circle me-2"></i>
                <?= session('info') ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- jQuery, Bootstrap 5 y DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.colVis.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Variables globales para filtros activos
            var activeYear = null;
            var activeMonth = null;
            var activeStatus = null;

            // Initialize Select2 on client dropdown
            $('#cliente').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Buscar o seleccionar cliente...',
                allowClear: true,
                minimumInputLength: 0,
                language: {
                    noResults: function() {
                        return "No se encontraron resultados";
                    },
                    searching: function() {
                        return "Buscando...";
                    }
                }
            });

            // ============================================
            // Precargar cliente desde localStorage (Quick Access)
            // ============================================
            var storedClient = localStorage.getItem('selectedClient');
            var currentClientParam = '<?= service('request')->getGet('cliente') ?? '' ?>';

            // Solo precargar si no hay cliente ya seleccionado por URL y hay uno guardado en localStorage
            if (storedClient && !currentClientParam) {
                // Verificar que el cliente exista en las opciones
                if ($('#cliente option[value="' + storedClient + '"]').length > 0) {
                    $('#cliente').val(storedClient).trigger('change');
                    console.log('Cliente precargado desde Quick Access: ' + storedClient);

                    // Enviar formulario automáticamente usando la lógica de "Ver Todos"
                    $('#filterForm').data('via-todos', true);
                    setTimeout(function() {
                        $('#filterForm').submit();
                    }, 500);
                }
            }

            // Guardar cliente en localStorage cuando se seleccione manualmente
            $('#cliente').on('change', function() {
                var clientId = $(this).val();
                if (clientId) {
                    localStorage.setItem('selectedClient', clientId);
                } else {
                    localStorage.removeItem('selectedClient');
                }
            });

            // Generar tarjetas de años dinámicamente
            function generateYearCards() {
                if (!table) return;

                var yearCounts = {};

                // Contar actividades por año
                table.rows({search: 'applied'}).every(function() {
                    var data = this.data();
                    var fechaPropuesta = data[8]; // Columna "Fecha Propuesta"
                    if (fechaPropuesta) {
                        var parts = fechaPropuesta.split("-");
                        if (parts.length >= 1) {
                            var year = parts[0];
                            yearCounts[year] = (yearCounts[year] || 0) + 1;
                        }
                    }
                });

                var yearArray = Object.keys(yearCounts).sort().reverse();
                var yearCardsHtml = '';

                yearArray.forEach(function(year) {
                    var count = yearCounts[year];
                    yearCardsHtml += `
                        <div class="col-6 col-md-2">
                            <div class="card text-white card-year card-clickable" data-year="${year}">
                                <div class="card-body text-center p-3">
                                    <h4 class="card-title mb-1">${year}</h4>
                                    <p class="mb-0" style="font-size: 1.5rem; font-weight: bold;">${count}</p>
                                    <small style="font-size: 0.75rem;">actividades</small>
                                </div>
                            </div>
                        </div>
                    `;
                });

                $('#yearCards').html(yearCardsHtml);
            }

            // Función para aplicar filtros combinados
            function applyFilters() {
                if (!table) return;

                $.fn.dataTable.ext.search.pop(); // Limpiar filtros personalizados previos

                $.fn.dataTable.ext.search.push(
                    function(settings, data, dataIndex) {
                        var fechaPropuesta = data[8] || ''; // Columna 8: Fecha Propuesta
                        // STRIP HTML del estado antes de comparar
                        var estadoActividad = $('<div/>').html(data[10] || '').text().trim();

                        // Filtro por año
                        if (activeYear) {
                            if (!fechaPropuesta.startsWith(activeYear)) {
                                return false;
                            }
                        }

                        // Filtro por mes
                        if (activeMonth) {
                            if (fechaPropuesta) {
                                var parts = fechaPropuesta.split("-");
                                if (parts.length >= 2) {
                                    var month = parseInt(parts[1], 10);
                                    if (month !== parseInt(activeMonth)) {
                                        return false;
                                    }
                                } else {
                                    return false;
                                }
                            } else {
                                return false;
                            }
                        }

                        // Filtro por estado
                        if (activeStatus && activeStatus !== 'ALL') {
                            if (estadoActividad.trim() !== activeStatus) {
                                return false;
                            }
                        }

                        return true;
                    }
                );

                table.draw();

                // Actualizar tarjetas de año después de aplicar filtros
                generateYearCards();
            }

            // Click en tarjetas de año
            $(document).on('click', '.card-year', function() {
                var year = $(this).data('year');

                if ($(this).hasClass('active')) {
                    // Desactivar filtro
                    $(this).removeClass('active');
                    activeYear = null;
                } else {
                    // Activar filtro
                    $('.card-year').removeClass('active');
                    $(this).addClass('active');
                    activeYear = year;
                }

                applyFilters();
            });

            // Click en tarjetas de mes
            $(document).on('click', '.card-month', function() {
                var month = $(this).data('month');

                if ($(this).hasClass('active')) {
                    // Desactivar filtro
                    $(this).removeClass('active');
                    activeMonth = null;
                } else {
                    // Activar filtro
                    $('.card-month').removeClass('active');
                    $(this).addClass('active');
                    activeMonth = month;
                }

                applyFilters();
            });

            // Click en tarjetas de estado
            $(document).on('click', '.card-status', function() {
                var status = $(this).data('status');

                if ($(this).hasClass('active')) {
                    // Desactivar filtro
                    $(this).removeClass('active');
                    activeStatus = null;
                } else {
                    // Activar filtro
                    $('.card-status').removeClass('active');
                    $(this).addClass('active');
                    activeStatus = status;
                }

                applyFilters();
            });

            // Botón para limpiar todos los filtros de tarjetas
            $('#btnClearCardFilters').on('click', function() {
                // Limpiar estados
                activeYear = null;
                activeMonth = null;
                activeStatus = null;

                // Remover clases activas
                $('.card-year').removeClass('active');
                $('.card-month').removeClass('active');
                $('.card-status').removeClass('active');

                // Limpiar filtros personalizados de DataTables
                $.fn.dataTable.ext.search.pop();

                if (table) {
                    table.draw();
                    generateYearCards(); // Regenerar tarjetas de año
                }

                showAlert('Filtros de tarjetas limpiados. Mostrando todos los registros.', 'info');
            });

            // Botón para mostrar todos los registros (limpiar filtros de fecha)
            $('#btnMostrarTodos').on('click', function() {
                var cliente = $('#cliente').val();
                if (!cliente) {
                    showAlert('Primero debe seleccionar un Cliente antes de usar "Ver Todos".', 'warning');
                    return;
                }

                // Limpiar todos los filtros de fecha
                $('#fecha_desde').val('');
                $('#fecha_hasta').val('');

                showAlert('Mostrando todos los registros del cliente seleccionado...', 'success');

                // Marcar que viene del botón "Ver Todos" para evitar validación de fechas
                $('#filterForm').data('via-todos', true);

                // Enviar automáticamente el formulario después de limpiar las fechas
                setTimeout(function() {
                    $('#filterForm').submit();
                }, 1000); // Esperar 1 segundo para que el usuario vea el mensaje
            });

            $('#filterForm').on('submit', function(e) {
                var cliente = $('#cliente').val();
                var fechaDesde = $('#fecha_desde').val();
                var fechaHasta = $('#fecha_hasta').val();

                // Validar que se haya seleccionado un cliente
                if (!cliente) {
                    showAlert('Debe seleccionar un Cliente.', 'error');
                    e.preventDefault();
                    return false;
                }

                // Validar filtros de búsqueda
                var esViaTodos = $(this).data('via-todos') === true;
                var tieneFechas = fechaDesde && fechaHasta;

                // PERMITIR búsqueda si:
                // 1. Viene del botón "Ver Todos"
                // 2. Tiene fechas completas
                var puedeEjecutar = esViaTodos || tieneFechas;

                if (!puedeEjecutar) {
                    showAlert('Debe especificar:\n• Rango de fechas (Fecha Desde y Fecha Hasta)\n• O hacer clic en "Ver Todos" para mostrar todos los registros del cliente', 'warning');
                    e.preventDefault();
                    return false;
                }

                // Limpiar el flag después de usarlo
                $(this).removeData('via-todos');

                // Si tiene fechas manuales incompletas, avisar
                if ((fechaDesde && !fechaHasta) || (!fechaDesde && fechaHasta)) {
                    showAlert('Para usar rango manual debe completar tanto "Fecha Desde" como "Fecha Hasta".', 'warning');
                    e.preventDefault();
                    return false;
                }
            });

            // Función para mostrar alertas mejoradas
            function showAlert(message, type = 'info') {
                const alertClass = {
                    'error': 'alert-danger',
                    'warning': 'alert-warning',
                    'success': 'alert-success',
                    'info': 'alert-info'
                }[type] || 'alert-info';
                
                const icon = {
                    'error': 'fas fa-exclamation-circle',
                    'warning': 'fas fa-exclamation-triangle',
                    'success': 'fas fa-check-circle',
                    'info': 'fas fa-info-circle'
                }[type] || 'fas fa-info-circle';
                
                // Remover alertas previas
                $('.custom-alert').remove();
                
                // Crear nueva alerta
                const alertHtml = `
                    <div class="alert ${alertClass} alert-dismissible fade show custom-alert" role="alert" style="position: relative; z-index: 1050;">
                        <i class="${icon} me-2"></i>
                        <strong>${message.replace(/\n/g, '<br>')}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
                
                // Insertar antes del formulario
                $('.filter-card').before(alertHtml);
                
                // Auto-ocultar después de 8 segundos
                setTimeout(function() {
                    $('.custom-alert').fadeOut();
                }, 8000);
            }

            var table;
            if ($('#ptaTable').length) {
                // Helper: extraer texto plano de HTML
                function stripHtml(html) {
                    return $('<div/>').html(html).text().trim();
                }

                // Construir badge de estado con la clase CSS correcta
                function buildEstadoBadge(estado) {
                    var cls = 'estado-abierta';
                    if (estado === 'CERRADA') cls = 'estado-cerrada';
                    else if (estado === 'GESTIONANDO') cls = 'estado-gestionando';
                    else if (estado === 'CERRADA SIN EJECUCIÓN') cls = 'estado-cerrada-sin';
                    return '<span class="estado-badge ' + cls + '">' + estado + '</span>';
                }

                // Construir progress bar con color segun porcentaje
                function buildProgressBar(pct) {
                    pct = parseFloat(pct) || 0;
                    var color = '#e74a3b';
                    if (pct >= 100) color = '#1cc88a';
                    else if (pct >= 50) color = '#4e73df';
                    else if (pct > 0) color = '#f6c23e';
                    var w = Math.max(pct, 2);
                    return '<div class="mini-progress">'
                         + '<div class="mini-progress-bar">'
                         + '<div class="mini-progress-fill" style="width:' + w + '%;background:' + color + '"></div>'
                         + '</div>'
                         + '<span class="mini-progress-text">' + pct + '%</span>'
                         + '</div>';
                }

                // Construir celda truncada (escapa HTML del texto)
                function buildTruncateCell(text) {
                    return '<div class="cell-truncate">' + $('<span/>').text(text).html() + '</div>';
                }

                table = $('#ptaTable').DataTable({
                    "lengthChange": true,
                    "responsive": false,
                    "scrollX": true,
                    "autoWidth": false,
                    "order": [
                        [10, 'asc'],
                        [8, 'asc'],
                        [4, 'asc'],
                        [6, 'asc']
                    ],
                    "columnDefs": [
                        { "visible": false, "targets": [0, 1, 2, 3, 4, 5, 13, 14, 15, 16] }
                    ],
                    "dom": '<"row"<"col-sm-12"B>><"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
                    "buttons": [
                    {
                        extend: 'colvis',
                        text: '<i class="fas fa-columns"></i> Columnas Visibles',
                        className: 'btn btn-outline-primary',
                        columns: ':not(:last-child)'
                    },
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel"></i> Exportar a Excel',
                        className: 'btn btn-success',
                        title: 'Lista_PTA_Cliente',
                        charset: 'UTF-8',
                        bom: true,
                        exportOptions: {
                            columns: ':visible',
                            format: {
                                body: function(data, row, column, node) {
                                    return $('<div/>').html(data).text();
                                }
                            }
                        }
                    }],
                    "initComplete": function() {
                        this.api().columns().every(function() {
                            var column = this;
                            var select = $('select', column.footer());
                            var input = $('input', column.footer());
                            if (select.length) {
                                // Si la columna no es "Estado Actividad" (índice 10), agregamos las opciones
                                if (column.index() !== 10) {
                                    column.data().unique().sort().each(function(d) {
                                        if (d) {
                                            select.append('<option value="' + d + '">' + d + '</option>');
                                        }
                                    });
                                }
                                // En cualquier caso, asignamos el evento change
                                select.on('change', function() {
                                    var val = $.fn.dataTable.util.escapeRegex($(this).val());
                                    column.search(val ? '^' + val + '$' : '', true, false).draw();
                                });
                            }
                            if (input.length) {
                                input.on('keyup change clear', function() {
                                    if (column.search() !== this.value) {
                                        column.search(this.value).draw();
                                    }
                                });
                            }
                        });
                    }
                });

                // Función para actualizar los contadores de las tarjetas superiores
                function updateCardCounts() {
                    var data = table.column(10, {
                        search: 'applied'
                    }).data().toArray();
                    var countActivas = data.filter(function(x) {
                        return stripHtml(x) === 'ABIERTA';
                    }).length;
                    var countCerradas = data.filter(function(x) {
                        return stripHtml(x) === 'CERRADA';
                    }).length;
                    var countGestionando = data.filter(function(x) {
                        return stripHtml(x) === 'GESTIONANDO';
                    }).length;
                    var countCerradasSinEjecucion = data.filter(function(x) {
                        return stripHtml(x) === 'CERRADA SIN EJECUCIÓN';
                    }).length;
                    $('#countActivas').text(countActivas);
                    $('#countCerradas').text(countCerradas);
                    $('#countGestionando').text(countGestionando);
                    $('#countCerradasSinEjecucion').text(countCerradasSinEjecucion);
                    // Total es la suma de todas las filas filtradas
                    $('#countTotal').text(table.rows({
                        search: 'applied'
                    }).data().length);
                }

                // Función para actualizar los contadores mensuales basado en la fecha propuesta (columna 8)
                function updateMonthlyCounts() {
                    var monthlyCounts = Array(12).fill(0);
                    var data = table.rows({
                        search: 'applied'
                    }).data().toArray();
                    data.forEach(function(row) {
                        var fechaPropuesta = row[8]; // Columna "Fecha Propuesta"
                        if (fechaPropuesta) {
                            // Se asume formato YYYY-MM-DD
                            var parts = fechaPropuesta.split("-");
                            if (parts.length >= 2) {
                                var month = parseInt(parts[1], 10);
                                if (!isNaN(month) && month >= 1 && month <= 12) {
                                    monthlyCounts[month - 1]++;
                                }
                            }
                        }
                    });
                    // Actualizar las cajitas de cada mes
                    var monthIds = ["countEnero", "countFebrero", "countMarzo", "countAbril", "countMayo", "countJunio", "countJulio", "countAgosto", "countSeptiembre", "countOctubre", "countNoviembre", "countDiciembre"];
                    monthIds.forEach(function(id, index) {
                        $('#' + id).text(monthlyCounts[index]);
                    });
                }

                // ===================================================================
                // TEXTO TRUNCADO EXPANDIBLE
                // ===================================================================
                function initTruncateButtons() {
                    $('.cell-truncate').each(function() {
                        var $el = $(this);
                        $el.next('.btn-expand').remove();
                        $el.removeClass('expanded');
                        if (this.scrollHeight > 65) {
                            if ($el.next('.btn-expand').length === 0) {
                                $el.after('<span class="btn-expand">ver m&aacute;s &#9660;</span>');
                            }
                        }
                    });
                }

                $(document).on('click', '.btn-expand', function() {
                    var $btn = $(this);
                    var $cell = $btn.prev('.cell-truncate');
                    if ($cell.hasClass('expanded')) {
                        $cell.removeClass('expanded');
                        $btn.html('ver m&aacute;s &#9660;');
                    } else {
                        $cell.addClass('expanded');
                        $btn.html('ver menos &#9650;');
                    }
                });

                table.on('draw', function() {
                    updateCardCounts();
                    updateMonthlyCounts();
                    initTruncateButtons();
                });
                updateCardCounts();
                updateMonthlyCounts();
                generateYearCards();
                initTruncateButtons();

                $('#ptaTable tbody').on('dblclick', 'td.editable', function() {
                    var cell = table.cell(this);
                    var originalHtml = cell.data();
                    var $td = $(this);
                    if ($td.find('input, select').length > 0) return;
                    var colIndex = table.cell($td).index().column;
                    var editableMapping = {
                        4: 'phva_plandetrabajo',
                        5: 'numeral_plandetrabajo',
                        6: 'actividad_plandetrabajo',
                        7: 'responsable_sugerido_plandetrabajo',
                        8: 'fecha_propuesta',
                        9: 'fecha_cierre',
                        10: 'estado_actividad',
                        11: 'porcentaje_avance',
                        12: 'observaciones'
                    };
                    var disallowed = [0, 1, 2, 3, 13, 14, 15, 16];
                    if (disallowed.indexOf(colIndex) !== -1 || !editableMapping.hasOwnProperty(colIndex)) {
                        cell.data(originalHtml).draw();
                        return;
                    }

                    // Extraer valor plano segun la columna
                    var plainValue = stripHtml(originalHtml);
                    // Para porcentaje, quitar el '%'
                    if (colIndex === 11) plainValue = plainValue.replace('%', '').trim();

                    var inputElement;
                    if (colIndex === 8 || colIndex === 9) {
                        inputElement = $('<input type="date" class="form-control form-control-sm" />').val(plainValue);
                    } else if (colIndex === 10) {
                        inputElement = $('<select class="form-select form-select-sm"></select>');
                        var options = ["ABIERTA", "CERRADA", "GESTIONANDO", "CERRADA SIN EJECUCIÓN"];
                        $.each(options, function(i, option) {
                            var selected = (plainValue === option) ? "selected" : "";
                            inputElement.append('<option value="' + option + '" ' + selected + '>' + option + '</option>');
                        });
                    } else if (colIndex === 11) {
                        inputElement = $('<input type="number" class="form-control form-control-sm" min="0" max="100" step="1" />').val(plainValue);
                    } else {
                        inputElement = $('<input type="text" class="form-control form-control-sm" />').val(plainValue);
                    }

                    $td.empty().append(inputElement);
                    inputElement.focus();

                    inputElement.on('blur keydown', function(e) {
                        if (e.type === 'blur' || (e.type === 'keydown' && e.which === 13)) {
                            var newValue = (colIndex === 10) ? inputElement.find("option:selected").val() : $(this).val();
                            if (newValue === stripHtml(originalHtml).replace(colIndex === 11 ? '%' : '', '').trim()) {
                                cell.data(originalHtml).draw();
                                return;
                            }
                            var fieldName = editableMapping[colIndex];
                            var rowData = table.row($td.closest('tr')).data();
                            var id = rowData[1];
                            var dataToSend = {
                                id: id
                            };
                            dataToSend[fieldName] = newValue;

                            // Si se está editando la fecha de cierre (columna 9) y tiene un valor, también enviar estado_actividad = CERRADA
                            if (colIndex === 9 && newValue && newValue.trim() !== '') {
                                dataToSend['estado_actividad'] = 'CERRADA';
                            }

                            dataToSend["<?= csrf_token() ?>"] = "<?= csrf_hash() ?>";

                            $.ajax({
                                url: "<?= site_url('/pta-cliente-nueva/editinginline') ?>",
                                method: "POST",
                                data: dataToSend,
                                dataType: "json",
                                success: function(response) {
                                    if (response.status === 'success') {
                                        // RECONSTRUIR HTML segun la columna
                                        if (colIndex === 10) {
                                            cell.data(buildEstadoBadge(newValue)).draw();
                                        } else if (colIndex === 11) {
                                            cell.data(buildProgressBar(newValue)).draw();
                                        } else if (colIndex === 6 || colIndex === 12) {
                                            cell.data(buildTruncateCell(newValue)).draw();
                                        } else {
                                            cell.data(newValue).draw();
                                        }

                                        // Fecha cierre -> estado CERRADA (actualizar badge)
                                        if (colIndex === 9 && newValue && newValue.trim() !== '') {
                                            var estadoCell = table.cell($td.closest('tr'), 10);
                                            estadoCell.data(buildEstadoBadge('CERRADA')).draw();
                                        }

                                        // Estado cambio -> actualizar progress bar
                                        if (fieldName === 'estado_actividad' && response.porcentaje_avance !== undefined) {
                                            var porcentajeCell = table.cell($td.closest('tr'), 11);
                                            porcentajeCell.data(buildProgressBar(response.porcentaje_avance)).draw();
                                        }

                                        // Fecha cierre -> actualizar progress bar
                                        if (colIndex === 9 && response.porcentaje_avance !== undefined) {
                                            var porcentajeCell = table.cell($td.closest('tr'), 11);
                                            porcentajeCell.data(buildProgressBar(response.porcentaje_avance)).draw();
                                        }

                                        updateCardCounts();
                                        updateMonthlyCounts();
                                        initTruncateButtons();
                                    } else {
                                        alert('Error: ' + response.message);
                                        cell.data(originalHtml).draw();
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error("AJAX error:", status, error, xhr.responseText);
                                    alert('Error en la comunicación con el servidor.\nStatus: ' + xhr.status + '\nError: ' + error + '\nRespuesta: ' + (xhr.responseText || '').substring(0, 300));
                                    cell.data(originalHtml).draw();
                                }
                            });
                        }
                    });
                });
            }

            $('#resetFilters').click(function() {
                $('#filterForm')[0].reset();
                window.location.href = "<?= site_url('/pta-cliente-nueva/list') ?>";
            });

            // Manejador para el botón Calificar Cerradas
            $('#btnCalificarCerradas').click(function() {
                if (!$('#ptaTable').length) {
                    alert('Primero debe realizar una búsqueda para obtener registros');
                    return;
                }

                var ids = [];
                table.rows().every(function() {
                    var data = this.data();
                    if (stripHtml(data[10]) === 'CERRADA') {
                        ids.push(data[1]);
                    }
                });

                if (ids.length === 0) {
                    alert('No se encontraron registros con estado CERRADA');
                    return;
                }

                $.ajax({
                    url: '<?= site_url('/pta-cliente-nueva/updateCerradas') ?>',
                    method: 'POST',
                    data: {
                        ids: ids,
                        '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            table.rows().every(function() {
                                var data = this.data();
                                if (stripHtml(data[10]) === 'CERRADA') {
                                    data[11] = buildProgressBar(100);
                                    this.data(data);
                                }
                            });
                            updateCardCounts();
                            updateMonthlyCounts();
                            alert(response.message);
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Error en la comunicación con el servidor');
                        console.error(error);
                    }
                });
            });

            // ===================================================================
            // GESTIÓN DE BOTONES MENSUALES (Asignación rápida de fecha por mes)
            // ===================================================================
            $(document).on('click', '.btn-month', function() {
                var $button = $(this);
                var activityId = $button.data('id');
                var month = $button.data('month');

                // Mostrar feedback visual inmediato
                $button.prop('disabled', true).css('opacity', '0.5');

                $.ajax({
                    url: '<?= site_url('/pta-cliente-nueva/updateDateByMonth') ?>',
                    method: 'POST',
                    data: {
                        id: activityId,
                        month: month,
                        '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Actualizar la celda de fecha_propuesta en la tabla
                            var row = table.row(function(idx, data, node) {
                                return data[1] == activityId; // data[1] es id_ptacliente
                            });

                            if (row.length > 0) {
                                var rowData = row.data();
                                rowData[8] = response.newDate; // Columna 8 es fecha_propuesta
                                row.data(rowData).draw(false);
                            }

                            // Agregar clase visual de éxito al botón
                            $button.addClass('has-date');

                            // Mostrar mensaje sutil de éxito
                            var monthName = new Date(2000, month - 1, 1).toLocaleString('es', { month: 'long' });
                            var successMsg = $('<small class="text-success ms-2"><i class="fas fa-check"></i></small>');
                            $button.parent().append(successMsg);
                            setTimeout(function() {
                                successMsg.fadeOut(function() { $(this).remove(); });
                            }, 2000);

                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Error al actualizar la fecha: ' + error);
                        console.error('Error AJAX:', xhr.responseText);
                    },
                    complete: function() {
                        // Re-habilitar botón
                        $button.prop('disabled', false).css('opacity', '1');
                    }
                });
            });

            // ===================================================================
            // ACCORDION DE FILTROS - Toggle clase collapsed
            // ===================================================================
            $('#cardFiltersPanel').on('show.bs.collapse', function() {
                $('.filter-toggle-btn').removeClass('collapsed');
            }).on('hide.bs.collapse', function() {
                $('.filter-toggle-btn').addClass('collapsed');
            });
        });
    </script>

    <!-- Modal para Renovar Plan de Trabajo -->
    <div class="modal fade" id="renewPlanModal" tabindex="-1" aria-labelledby="renewPlanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="renewPlanModalLabel">
                        <i class="fas fa-sync-alt"></i> Renovar Plan de Trabajo
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="<?= base_url('consultant/plan/generate') ?>" method="post" id="renewPlanForm">
                    <div class="modal-body">
                        <div class="alert alert-info" role="alert">
                            <small><i class="fas fa-info-circle"></i> Esta opción generará automáticamente las actividades del plan de trabajo según las plantillas predefinidas.</small>
                        </div>

                        <!-- Selector de Cliente -->
                        <div class="mb-3">
                            <label for="id_cliente_modal" class="form-label">Cliente <span class="text-danger">*</span></label>
                            <select class="form-select" name="id_cliente" id="id_cliente_modal" required>
                                <option value="">Seleccione un cliente...</option>
                                <!-- Aquí se cargarán los clientes dinámicamente -->
                            </select>
                            <div class="invalid-feedback">Por favor seleccione un cliente.</div>
                        </div>

                        <!-- Selector de Año -->
                        <div class="mb-3">
                            <label for="year_modal" class="form-label">Año del SGSST <span class="text-danger">*</span></label>
                            <select class="form-select" name="year" id="year_modal" required>
                                <option value="">Seleccione el año...</option>
                                <option value="1">Año 1</option>
                                <option value="2">Año 2</option>
                                <option value="3">Año 3</option>
                            </select>
                            <div class="invalid-feedback">Por favor seleccione el año del SGSST.</div>
                        </div>

                        <!-- Selector de Tipo de Servicio -->
                        <div class="mb-3">
                            <label for="service_type_modal" class="form-label">Tipo de Servicio <span class="text-danger">*</span></label>
                            <select class="form-select" name="service_type" id="service_type_modal" required>
                                <option value="">Seleccione el tipo de servicio...</option>
                                <option value="mensual">Mensual</option>
                                <option value="bimensual">Bimensual</option>
                                <option value="trimestral">Trimestral</option>
                                <option value="proyecto">Proyecto</option>
                            </select>
                            <div class="invalid-feedback">Por favor seleccione el tipo de servicio.</div>
                        </div>

                        <div class="alert alert-warning mb-0" role="alert">
                            <small><i class="fas fa-exclamation-triangle"></i> <strong>Importante:</strong> Se crearán nuevas actividades con estado ABIERTA, porcentaje 0% y fecha del día actual. Las actividades anteriores se mantendrán en el sistema.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-check"></i> Generar Plan de Trabajo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Cargar clientes al abrir el modal de renovación
        document.getElementById('renewPlanModal').addEventListener('show.bs.modal', function () {
            fetch('<?= base_url('consultant/plan/getClients') ?>')
                .then(response => response.json())
                .then(data => {
                    const clientSelect = document.getElementById('id_cliente_modal');
                    clientSelect.innerHTML = '<option value="">Seleccione un cliente...</option>';

                    data.forEach(client => {
                        const option = document.createElement('option');
                        option.value = client.id_cliente;
                        option.textContent = client.nombre_cliente;
                        clientSelect.appendChild(option);
                    });

                    // Inicializar Select2 después de cargar los clientes
                    $('#id_cliente_modal').select2({
                        theme: 'bootstrap-5',
                        placeholder: 'Buscar cliente...',
                        allowClear: true,
                        dropdownParent: $('#renewPlanModal')
                    });
                })
                .catch(error => {
                    console.error('Error al cargar clientes:', error);
                    alert('Error al cargar la lista de clientes');
                });
        });

        // Destruir Select2 al cerrar el modal para evitar problemas
        document.getElementById('renewPlanModal').addEventListener('hidden.bs.modal', function () {
            if ($('#id_cliente_modal').hasClass('select2-hidden-accessible')) {
                $('#id_cliente_modal').select2('destroy');
            }
        });

        // Validación del formulario de renovación
        document.getElementById('renewPlanForm').addEventListener('submit', function (event) {
            if (!this.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            this.classList.add('was-validated');
        });

        // Manejador para el botón de Socializar Plan de Trabajo
        $(document).ready(function() {
            $('#btnSocializarPlanTrabajo').on('click', function() {
                var clienteId = '<?= $filters['cliente'] ?? '' ?>';

                if (!clienteId) {
                    alert('Debe seleccionar un cliente primero.');
                    return;
                }

                if (!confirm('¿Desea enviar el Plan de Trabajo por email al cliente y al consultor?')) {
                    return;
                }

                var $btn = $(this);
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Enviando...');

                $.ajax({
                    url: '<?= base_url('/socializacion/send-plan-trabajo') ?>',
                    method: 'POST',
                    data: {
                        id_cliente: clienteId,
                        '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert('Email enviado exitosamente.\n\n' + response.message);
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Error al enviar el email: ' + error);
                        console.error('Error AJAX:', xhr.responseText);
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html('<i class="fas fa-envelope"></i> Socializar Plan de Trabajo');
                    }
                });
            });
        });
    </script>
</body>

</html>