<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lista de Cronogramas de Capacitación</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- DataTables CSS -->
  <link href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <!-- DataTables Buttons CSS -->
  <link href="https://cdn.datatables.net/buttons/2.3.3/css/buttons.bootstrap5.min.css" rel="stylesheet">
  <!-- Select2 CSS para select buscable -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

  <style>
    body {
      background-color: #f9f9f9;
      font-family: Arial, sans-serif;
    }

    h1 {
      margin: 20px 0;
      text-align: center;
      color: #333;
    }

    table {
      width: 100%;
    }

    .dataTables_filter input {
      background-color: #f0f0f0;
      border-radius: 5px;
      border: 1px solid #ccc;
      padding: 6px;
    }

    .dataTables_length select {
      background-color: #f0f0f0;
      border-radius: 5px;
      padding: 6px;
    }

    td,
    th {
      max-width: 20ch;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      height: 25px;
    }

    .tooltip-inner {
      max-width: 300px;
      word-wrap: break-word;
      z-index: 1050;
    }

    .filters select {
      width: 100%;
      padding: 4px;
      border-radius: 4px;
      border: 1px solid #ccc;
    }

    /* Columna para fila expandible */
    td.details-control {
      background: url('https://www.datatables.net/examples/resources/details_open.png') no-repeat center center;
      cursor: pointer;
    }

    tr.shown td.details-control {
      background: url('https://www.datatables.net/examples/resources/details_close.png') no-repeat center center;
    }

    /* Para celdas editables: se asignan estilos mínimos para que siempre contengan contenido (por ejemplo, un espacio no separable) */
    .editable,
    .editable-select,
    .editable-date {
      min-height: 1em;
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

    /* Estilos para botones mensuales */
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
      padding: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s ease;
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

    /* Ajustar columna de capacitación */
    td.capacitacion-col,
    th.capacitacion-col {
      max-width: 250px !important;
      min-width: 180px;
      white-space: normal !important;
      word-wrap: break-word;
    }
  </style>
</head>

<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm">
    <div class="container-fluid">
      <div class="d-flex align-items-center">
        <a href="https://dashboard.cycloidtalent.com/login" class="me-3">
          <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo" height="60">
        </a>
        <a href="https://cycloidtalent.com/index.php/consultoria-sst" class="me-3">
          <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST Logo" height="60">
        </a>
        <a href="https://cycloidtalent.com/">
          <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloids Logo" height="60">
        </a>
      </div>
      <div class="ms-auto d-flex">
        <div class="text-center me-3">
          <h6 class="mb-1" style="font-size: 16px;">Ir a Dashboard</h6>
          <a href="<?= base_url('/dashboardconsultant') ?>" class="btn btn-primary btn-sm">Ir a DashBoard</a>
        </div>
        <div class="text-center me-3">
          <h6 class="mb-1" style="font-size: 16px;">Cargar Cronograma</h6>
          <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#generateTrainingModal">
            <i class="fas fa-magic"></i> Generar Automático
          </button>
        </div>
        <div class="text-center">
          <h6 class="mb-1" style="font-size: 16px;">Añadir Registro</h6>
          <a href="<?= base_url('/addcronogCapacitacion') ?>" class="btn btn-success btn-sm" target="_blank">Añadir Registro</a>
        </div>
      </div>
    </div>
  </nav>

  <!-- Espaciado para el navbar fijo -->
  <div style="height: 100px;"></div>

  <div class="container-fluid px-2 mt-2">
    <h1 class="text-center mb-3">Lista de Cronogramas de Capacitación</h1>

    <!-- Mensajes Flash -->
    <?php if (session()->getFlashdata('success')): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('warning')): ?>
      <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?= session()->getFlashdata('warning') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

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
        <div id="contractCardContainer">
          <div class="no-contract-card h-100 d-flex flex-column justify-content-center">
            <i class="fas fa-hand-pointer fa-3x mb-3 opacity-75"></i>
            <h5>Seleccione un Cliente</h5>
            <p class="mb-0 opacity-75">Seleccione un cliente para ver la información de su contrato.</p>
          </div>
        </div>
      </div>

      <!-- Tarjetas de Año -->
      <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center">
          <div class="section-title mb-0">
            <i class="fas fa-calendar-alt"></i> Filtrar por Año
          </div>
          <button type="button" id="btnClearCardFilters" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-times"></i> Limpiar Filtros de Tarjetas
          </button>
        </div>
        <div class="row mt-2" id="yearCards">
          <!-- Se generarán dinámicamente con JavaScript -->
        </div>
      </div>
    </div>

    <!-- Tarjetas de Estados (clickeables) -->
    <div class="section-title">
      <i class="fas fa-tasks"></i> Filtrar por Estado
    </div>
    <div class="row mb-4">
      <div class="col-md-3">
        <div class="card text-white bg-primary card-clickable card-status" data-status="PROGRAMADA">
          <div class="card-body text-center">
            <h5 class="card-title">Programada</h5>
            <p class="card-text display-6" id="countProgramada">0</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-white bg-success card-clickable card-status" data-status="EJECUTADA">
          <div class="card-body text-center">
            <h5 class="card-title">Ejecutada</h5>
            <p class="card-text display-6" id="countEjecutada">0</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-white bg-danger card-clickable card-status" data-status="CANCELADA POR EL CLIENTE">
          <div class="card-body text-center">
            <h5 class="card-title">Cancelada</h5>
            <p class="card-text display-6" id="countCancelada">0</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-white bg-warning card-clickable card-status" data-status="REPROGRAMADA">
          <div class="card-body text-center">
            <h5 class="card-title">Reprogramada</h5>
            <p class="card-text display-6" id="countReprogramada">0</p>
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

    <!-- Bloque para seleccionar cliente -->
    <div class="row mb-2">
      <div class="col-md-3">
        <label for="clientSelect">Selecciona un Cliente:</label>
        <select id="clientSelect" class="form-select">
          <option value="">Seleccione un cliente</option>
        </select>
      </div>
      <div class="col-md-2 align-self-end">
        <button id="loadData" class="btn btn-primary">Cargar Datos</button>
      </div>
      <div class="col-md-7 align-self-end">
        <button id="clearState" class="btn btn-danger btn-sm me-2">Restablecer Filtros</button>
        <div id="buttonsContainer" class="d-inline-block"></div>
      </div>
    </div>

    <div class="table-responsive" style="overflow-x: auto; max-width: 100%;">
      <table id="cronogramaTable" class="table table-striped table-bordered nowrap" style="width:100%">
        <thead class="table-light">
          <tr>
            <!-- Columna para fila expandible -->
            <th></th>
            <th>#</th>
            <th>Acciones</th>
            <th style="min-width: 200px;">📅 Gestión Rápida</th>
            <th class="capacitacion-col">Capacitación</th>
            <!-- <th>Objetivo</th> -->
            <th>Cliente</th>
            <th>*Fecha Programada</th>
            <th>*Fecha de Realización</th>
            <th>*Estado</th>
            <th>*Perfil de Asistentes</th>
            <th>*Capacitador</th>
            <th>*Horas de Duración</th>
            <th>*Indicador de Realización</th>
            <th>*Asistentes</th>
            <th>*Total Programados</th>
            <th>% Cobertura</th>
            <th>*Evaluadas</th>
            <th>*Promedio</th>
            <th>*Observaciones</th>
          </tr>
        </thead>
        <tfoot class="table-light">
          <tr class="filters">
            <th></th>
            <th><input type="text" class="form-control form-control-sm filter-search" placeholder="Filtrar ID"></th>
            <th></th>
            <th></th>
            <th><input type="text" class="form-control form-control-sm filter-search" placeholder="Filtrar Capacitación"></th>
            <!-- <th><input type="text" class="form-control form-control-sm filter-search" placeholder="Filtrar Objetivo"></th> -->
            <th><input type="text" class="form-control form-control-sm filter-search" placeholder="Filtrar Cliente"></th>
            <th><input type="text" class="form-control form-control-sm filter-search" placeholder="Filtrar Fecha"></th>
            <th><input type="text" class="form-control form-control-sm filter-search" placeholder="Filtrar Fecha"></th>
            <th>
              <select class="form-select form-select-sm filter-search">
                <option value="">Todos</option>
                <option value="PROGRAMADA">PROGRAMADA</option>
                <option value="EJECUTADA">EJECUTADA</option>
                <option value="CANCELADA POR EL CLIENTE">CANCELADA POR EL CLIENTE</option>
                <option value="REPROGRAMADA">REPROGRAMADA</option>
              </select>
            </th>
            <th>
              <select class="form-select form-select-sm filter-search">
                <option value="">Todos</option>
                <option value="TODOS">TODOS</option>
                <option value="DIRECTIVOS_ALTA_GERENCIA">DIRECTIVOS_ALTA_GERENCIA</option>
                <option value="JEFES_Y_SUPERVISORES">JEFES_Y_SUPERVISORES</option>
                <option value="VIGIA_SST">VIGIA_SST</option>
                <option value="BRIGADA_EMERGENCIAS">BRIGADA_EMERGENCIAS</option>
                <option value="COMITE_SEGURIDAD_VIAL">COMITE_SEGURIDAD_VIAL</option>
                <option value="MIEMBROS_COPASST">MIEMBROS_COPASST</option>
                <option value="MIEMBROS_COMITE_CONVIVENCIA">MIEMBROS_COMITE_CONVIVENCIA</option>
                <option value="TRABAJADORES_RIESGOS_CRITICOS">TRABAJADORES_RIESGOS_CRITICOS</option>
                <option value="PERSONAL_ASEO_MANTENIMIENTO">PERSONAL_ASEO_MANTENIMIENTO</option>
                <option value="BRIGADA">BRIGADA</option>
              </select>
            </th>
            <th><input type="text" class="form-control form-control-sm filter-search" placeholder="Filtrar Capacitador"></th>
            <th><input type="text" class="form-control form-control-sm filter-search" placeholder="Filtrar Horas"></th>
            <th>
              <select class="form-select form-select-sm filter-search">
                <option value="">Todos</option>
                <option value="SE EJECUTO EN LA FECHA O ANTES">SE EJECUTO EN LA FECHA O ANTES</option>
                <option value="SE EJECUTO DESPUES">SE EJECUTO DESPUES</option>
                <option value="DECLINADA">DECLINADA</option>
                <option value="NO SE REALIZÓ">NO SE REALIZÓ</option>
              </select>
            </th>
            <th><input type="text" class="form-control form-control-sm filter-search" placeholder="Filtrar Asistentes"></th>
            <th><input type="text" class="form-control form-control-sm filter-search" placeholder="Filtrar Total"></th>
            <th><input type="text" class="form-control form-control-sm filter-search" placeholder="Filtrar % Cobertura"></th>
            <th><input type="text" class="form-control form-control-sm filter-search" placeholder="Filtrar Evaluadas"></th>
            <th><input type="text" class="form-control form-control-sm filter-search" placeholder="Filtrar Promedio"></th>
            <th><input type="text" class="form-control form-control-sm filter-search" placeholder="Filtrar Observaciones"></th>
          </tr>
        </tfoot>
        <tbody>
          <!-- Los datos se cargarán vía AJAX -->
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal para Generar Cronograma Automáticamente -->
  <div class="modal fade" id="generateTrainingModal" tabindex="-1" aria-labelledby="generateTrainingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-warning text-dark">
          <h5 class="modal-title" id="generateTrainingModalLabel">
            <i class="fas fa-magic"></i> Generar Cronograma de Capacitación Automáticamente
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="formGenerateTraining" action="<?= base_url('/cronogCapacitacion/generate') ?>" method="post">
          <div class="modal-body">
            <div class="alert alert-info">
              <i class="fas fa-info-circle"></i>
              <strong>Información:</strong> Esta función generará automáticamente el cronograma de capacitación basado en el tipo de servicio del cliente.
            </div>

            <!-- Cliente -->
            <div class="mb-3">
              <label for="modalClientSelect" class="form-label">
                <i class="fas fa-building"></i> Cliente <span class="text-danger">*</span>
              </label>
              <select name="id_cliente" id="modalClientSelect" class="form-select" required>
                <option value="">Seleccione un cliente...</option>
              </select>
            </div>

            <!-- Tipo de Servicio -->
            <div class="mb-3">
              <label for="serviceTypeSelect" class="form-label">
                <i class="fas fa-concierge-bell"></i> Tipo de Servicio <span class="text-danger">*</span>
              </label>
              <select name="service_type" id="serviceTypeSelect" class="form-select" required>
                <option value="">Seleccione tipo de servicio...</option>
                <option value="mensual">Mensual (Todas las capacitaciones)</option>
                <option value="bimensual">Bimensual (Capacitaciones seleccionadas)</option>
                <option value="trimestral">Trimestral (Capacitaciones mínimas)</option>
                <option value="proyecto">Proyecto (Capacitaciones mínimas)</option>
              </select>
            </div>

            <div class="alert alert-warning">
              <i class="fas fa-exclamation-triangle"></i>
              <strong>Nota:</strong> Esta acción agregará múltiples registros de capacitación al cronograma del cliente seleccionado.
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              <i class="fas fa-times"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-warning">
              <i class="fas fa-check"></i> Generar Cronograma
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="bg-white py-2 border-top mt-2">
    <div class="container-fluid text-center">
      <p class="fw-bold mb-1">Cycloid Talent SAS</p>
      <p class="mb-1">Todos los derechos reservados © 2024</p>
      <p class="mb-1">NIT: 901.653.912</p>
      <p class="mb-1">
        Sitio oficial: <a href="https://cycloidtalent.com/" target="_blank">https://cycloidtalent.com/</a>
      </p>
    </div>
  </footer>

  <!-- Scripts -->
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Bootstrap Bundle (incluye Popper.js) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Select2 JS -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <!-- DataTables JS -->
  <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>
  <!-- DataTables Buttons JS -->
  <script src="https://cdn.datatables.net/buttons/2.3.3/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.bootstrap5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.colVis.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.html5.min.js"></script>

  <script>
    // Función para formatear la fila expandible (detalles) con 30% para el nombre y 70% para el texto (con overflow auto)
    function format(rowData) {
      var html = '<table class="table table-sm table-borderless" style="width:100%;">';
      html += '<tr><td style="width:30%;"><strong>Capacitación:</strong></td><td style="width:70%; overflow:auto;">' + (rowData.nombre_capacitacion || '') + '</td></tr>';
      html += '<tr><td style="width:30%;"><strong>Objetivo:</strong></td><td style="width:70%; overflow:auto;">' + (rowData.objetivo_capacitacion || '') + '</td></tr>';
      html += '<tr><td style="width:30%;"><strong>Cliente:</strong></td><td style="width:70%; overflow:auto;">' + (rowData.nombre_cliente || '') + '</td></tr>';
      html += '<tr><td style="width:30%;"><strong>Fecha Programada:</strong></td><td style="width:70%; overflow:auto;">' + (rowData.fecha_programada || '') + '</td></tr>';
      html += '<tr><td style="width:30%;"><strong>Fecha de Realización:</strong></td><td style="width:70%; overflow:auto;">' + (rowData.fecha_de_realizacion || '') + '</td></tr>';
      html += '<tr><td style="width:30%;"><strong>Estado:</strong></td><td style="width:70%; overflow:auto;">' + (rowData.estado || '') + '</td></tr>';
      html += '<tr><td style="width:30%;"><strong>Perfil de Asistentes:</strong></td><td style="width:70%; overflow:auto;">' + (rowData.perfil_de_asistentes || '') + '</td></tr>';
      html += '<tr><td style="width:30%;"><strong>Capacitador:</strong></td><td style="width:70%; overflow:auto;">' + (rowData.nombre_del_capacitador || '') + '</td></tr>';
      html += '<tr><td style="width:30%;"><strong>Horas de Duración:</strong></td><td style="width:70%; overflow:auto;">' + (rowData.horas_de_duracion_de_la_capacitacion || '') + '</td></tr>';
      html += '<tr><td style="width:30%;"><strong>Indicador de Realización:</strong></td><td style="width:70%; overflow:auto;">' + (rowData.indicador_de_realizacion_de_la_capacitacion || '') + '</td></tr>';
      html += '<tr><td style="width:30%;"><strong>Nº Asistentes:</strong></td><td style="width:70%; overflow:auto;">' + (rowData.numero_de_asistentes_a_capacitacion || '') + '</td></tr>';
      html += '<tr><td style="width:30%;"><strong>Total Programados:</strong></td><td style="width:70%; overflow:auto;">' + (rowData.numero_total_de_personas_programadas || '') + '</td></tr>';
      html += '<tr><td style="width:30%;"><strong>% Cobertura:</strong></td><td style="width:70%; overflow:auto;">' + (rowData.porcentaje_cobertura || '') + '</td></tr>';
      html += '<tr><td style="width:30%;"><strong>Personas Evaluadas:</strong></td><td style="width:70%; overflow:auto;">' + (rowData.numero_de_personas_evaluadas || '') + '</td></tr>';
      html += '<tr><td style="width:30%;"><strong>Promedio:</strong></td><td style="width:70%; overflow:auto;">' + (rowData.promedio_de_calificaciones || '') + '</td></tr>';
      html += '<tr><td style="width:30%;"><strong>Observaciones:</strong></td><td style="width:70%; overflow:auto;">' + (rowData.observaciones || '') + '</td></tr>';
      html += '</table>';
      return html;
    }

    $(document).ready(function() {
      // Variables globales para filtros activos
      var activeYear = null;
      var activeMonth = null;
      var activeStatus = null;

      // Inicializar el select con Select2
      $('#clientSelect').select2({
        placeholder: 'Seleccione un cliente',
        allowClear: true,
        width: '100%'
      });

      // Cargar clientes vía AJAX usando las claves 'id' y 'nombre'
      $.ajax({
        url: "<?= base_url('/api/getClientes') ?>",
        method: "GET",
        dataType: "json",
        success: function(data) {
          data.forEach(function(cliente) {
            $("#clientSelect").append('<option value="' + cliente.id + '">' + cliente.nombre + '</option>');
          });
          var storedClient = localStorage.getItem('selectedClient');
          if (storedClient) {
            $("#clientSelect").val(storedClient).trigger('change');
          }
        },
        error: function() {
          alert('Error al cargar la lista de clientes.');
        }
      });

      // Inicializar DataTable con fila expandible y render para inline editing
      var table = $('#cronogramaTable').DataTable({
        stateSave: true,
        order: [[6, 'asc']], // Ordenar por fecha programada ASC por defecto
        language: {
          url: "//cdn.datatables.net/plug-ins/1.13.1/i18n/es-ES.json"
        },
        pagingType: "full_numbers",
        responsive: true,
        autoWidth: false,
        dom: 'Bfltip',
        pageLength: 25,
        scrollX: true,
        scrollCollapse: true,
        buttons: [{
            extend: 'excelHtml5',
            text: 'Exportar a Excel',
            className: 'btn btn-success btn-sm'
          },
          {
            extend: 'colvis',
            text: 'Seleccionar Columnas',
            className: 'btn btn-secondary btn-sm'
          }
        ],
        ajax: {
          url: "<?= base_url('/api/getCronogramasAjax') ?>",
          data: function(d) {
            d.cliente = $("#clientSelect").val();
          },
          dataSrc: ''
        },
        columns: [{
            data: null,
            orderable: false,
            className: 'details-control',
            defaultContent: ''
          },
          {
            data: 'id_cronograma_capacitacion'
          },
          {
            data: 'acciones',
            orderable: false
          },
          {
            data: null,
            orderable: false,
            searchable: false,
            render: function(data, type, row) {
              var mesesEspanol = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
              var html = '<div class="month-buttons" style="display: grid; grid-template-columns: repeat(4, 32px); gap: 4px; justify-content: center;">';
              for (var month = 1; month <= 12; month++) {
                html += '<button type="button" class="btn-month" data-id="' + row.id_cronograma_capacitacion + '" data-month="' + month + '" title="' + mesesEspanol[month - 1] + '">' + month + '</button>';
              }
              html += '</div>';
              return html;
            }
          },
          {
            data: 'nombre_capacitacion',
            className: 'capacitacion-col',
            render: function(data, type, row) {
              data = (data === null || data === "") ? "" : data;
              var displayText = data || '&nbsp;';
              return '<span class="editable" data-field="nombre_capacitacion" data-id="' + row.id_cronograma_capacitacion + '" data-bs-toggle="tooltip" title="' + data + '" style="white-space: normal; word-wrap: break-word;">' + displayText + '</span>';
            }
          },
          // Columna Objetivo oculta
          // {
          //   data: 'objetivo_capacitacion'
          // },
          {
            data: 'nombre_cliente',
            render: function(data, type, row) {
              data = (data === null || data === "") ? "" : data;
              var displayText = data || '&nbsp;';
              return '<span data-bs-toggle="tooltip" title="' + data + '">' + displayText + '</span>';
            }
          },
          {
            data: 'fecha_programada',
            render: function(data, type, row) {
              data = (data === null || data === "") ? "" : data;
              var displayText = data || '&nbsp;';
              return '<span class="editable-date" data-field="fecha_programada" data-id="' + row.id_cronograma_capacitacion + '">' + displayText + '</span>';
            }
          },
          {
            data: 'fecha_de_realizacion',
            render: function(data, type, row) {
              data = (data === null || data === "") ? "" : data;
              var displayText = data || '&nbsp;';
              return '<span class="editable-date" data-field="fecha_de_realizacion" data-id="' + row.id_cronograma_capacitacion + '">' + displayText + '</span>';
            }
          },
          {
            data: 'estado',
            render: function(data, type, row) {
              data = (data === null || data === "") ? "" : data;
              var displayText = data || '&nbsp;';
              return '<span class="editable-select" data-field="estado" data-id="' + row.id_cronograma_capacitacion + '" data-bs-toggle="tooltip" title="' + data + '">' + displayText + '</span>';
            }
          },
          {
            data: 'perfil_de_asistentes',
            render: function(data, type, row) {
              data = (data === null || data === "") ? "" : data;
              var displayText = data || '&nbsp;';
              return '<span class="editable-select" data-field="perfil_de_asistentes" data-id="' + row.id_cronograma_capacitacion + '" data-bs-toggle="tooltip" title="' + data + '">' + displayText + '</span>';
            }
          },
          {
            data: 'nombre_del_capacitador',
            render: function(data, type, row) {
              data = (data === null || data === "") ? "" : data;
              var displayText = data || '&nbsp;';
              return '<span class="editable" data-field="nombre_del_capacitador" data-id="' + row.id_cronograma_capacitacion + '" data-bs-toggle="tooltip" title="' + data + '">' + displayText + '</span>';
            }
          },
          {
            data: 'horas_de_duracion_de_la_capacitacion',
            render: function(data, type, row) {
              data = (data === null || data === "") ? "" : data;
              var displayText = data || '&nbsp;';
              // Se elimina tooltip en esta columna
              return '<span class="editable" data-field="horas_de_duracion_de_la_capacitacion" data-id="' + row.id_cronograma_capacitacion + '">' + displayText + '</span>';
            }
          },
          {
            data: 'indicador_de_realizacion_de_la_capacitacion',
            render: function(data, type, row) {
              data = (data === null || data === "") ? "" : data;
              var displayText = data || '&nbsp;';
              return '<span class="editable-select" data-field="indicador_de_realizacion_de_la_capacitacion" data-id="' + row.id_cronograma_capacitacion + '" data-bs-toggle="tooltip" title="' + data + '">' + displayText + '</span>';
            }
          },
          {
            data: 'numero_de_asistentes_a_capacitacion',
            render: function(data, type, row) {
              data = (data === null || data === "") ? "" : data;
              var displayText = data || '&nbsp;';
              // Se elimina tooltip en esta columna
              return '<span class="editable" data-field="numero_de_asistentes_a_capacitacion" data-id="' + row.id_cronograma_capacitacion + '">' + displayText + '</span>';
            }
          },
          {
            data: 'numero_total_de_personas_programadas',
            render: function(data, type, row) {
              data = (data === null || data === "") ? "" : data;
              var displayText = data || '&nbsp;';
              // Se elimina tooltip en esta columna
              return '<span class="editable" data-field="numero_total_de_personas_programadas" data-id="' + row.id_cronograma_capacitacion + '">' + displayText + '</span>';
            }
          },
          {
            data: 'porcentaje_cobertura',
            render: function(data, type, row) {
              // Calcular % Cobertura automáticamente
              var asistentes = parseFloat(row.numero_de_asistentes_a_capacitacion) || 0;
              var programados = parseFloat(row.numero_total_de_personas_programadas) || 0;
              var porcentaje = programados > 0 ? Math.round((asistentes / programados) * 100) : 0;
              return porcentaje + '%';
            }
          },
          {
            data: 'numero_de_personas_evaluadas',
            render: function(data, type, row) {
              data = (data === null || data === "") ? "" : data;
              var displayText = data || '&nbsp;';
              // Se elimina tooltip en esta columna
              return '<span class="editable" data-field="numero_de_personas_evaluadas" data-id="' + row.id_cronograma_capacitacion + '">' + displayText + '</span>';
            }
          },
          {
            data: 'promedio_de_calificaciones',
            render: function(data, type, row) {
              data = (data === null || data === "") ? "" : data;
              var displayText = data || '&nbsp;';
              // Se elimina tooltip en esta columna
              return '<span class="editable" data-field="promedio_de_calificaciones" data-id="' + row.id_cronograma_capacitacion + '">' + displayText + '</span>';
            }
          },
          {
            data: 'observaciones',
            render: function(data, type, row) {
              data = (data === null || data === "") ? "" : data;
              var displayText = data || '&nbsp;';
              return '<span class="editable" data-field="observaciones" data-id="' + row.id_cronograma_capacitacion + '" data-bs-toggle="tooltip" title="' + data + '">' + displayText + '</span>';
            }
          }
        ],
        initComplete: function() {
          var api = this.api();
          api.columns().every(function() {
            var column = this;
            var headerIndex = column.index();
            var filterElement = $('tfoot tr.filters th').eq(headerIndex).find('.filter-search');
            if (filterElement.length) {
              column.data().unique().sort().each(function(d) {
                if (d !== null && d !== '' && filterElement.find('option[value="' + d + '"]').length === 0) {
                  filterElement.append('<option value="' + d + '">' + d + '</option>');
                }
              });
              var search = column.search();
              if (search) {
                filterElement.val(search);
              }
            }
          });
        }
      });

      table.buttons().container().appendTo('#buttonsContainer');

      // Generar tarjetas de años dinámicamente
      function generateYearCards() {
        if (!table) return;

        var yearCounts = {};

        // Contar cronogramas por año basado en fecha_programada
        table.rows({search: 'applied'}).every(function() {
          var data = this.data();
          var fechaProgramada = data.fecha_programada; // Acceder por nombre de propiedad
          if (fechaProgramada) {
            var parts = fechaProgramada.split("-");
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
                  <small style="font-size: 0.75rem;">capacitaciones</small>
                </div>
              </div>
            </div>
          `;
        });

        $('#yearCards').html(yearCardsHtml);
      }

      // Actualizar contadores de estados
      function updateStatusCounts() {
        if (!table) return;

        var countProgramada = 0;
        var countEjecutada = 0;
        var countCancelada = 0;
        var countReprogramada = 0;

        table.rows({search: 'applied'}).every(function() {
          var data = this.data();
          var estado = data.estado; // Acceder por nombre de propiedad
          if (estado === 'PROGRAMADA') {
            countProgramada++;
          } else if (estado === 'EJECUTADA') {
            countEjecutada++;
          } else if (estado === 'CANCELADA POR EL CLIENTE') {
            countCancelada++;
          } else if (estado === 'REPROGRAMADA') {
            countReprogramada++;
          }
        });

        $('#countProgramada').text(countProgramada);
        $('#countEjecutada').text(countEjecutada);
        $('#countCancelada').text(countCancelada);
        $('#countReprogramada').text(countReprogramada);
      }

      // Actualizar contadores de meses
      function updateMonthlyCounts() {
        if (!table) return;

        var monthlyCounts = {
          1: 0, 2: 0, 3: 0, 4: 0, 5: 0, 6: 0,
          7: 0, 8: 0, 9: 0, 10: 0, 11: 0, 12: 0
        };

        table.rows({search: 'applied'}).every(function() {
          var data = this.data();
          var fechaProgramada = data.fecha_programada; // Acceder por nombre de propiedad
          if (fechaProgramada) {
            var parts = fechaProgramada.split("-");
            if (parts.length >= 2) {
              var month = parseInt(parts[1], 10);
              if (month >= 1 && month <= 12) {
                monthlyCounts[month]++;
              }
            }
          }
        });

        $('#countEnero').text(monthlyCounts[1]);
        $('#countFebrero').text(monthlyCounts[2]);
        $('#countMarzo').text(monthlyCounts[3]);
        $('#countAbril').text(monthlyCounts[4]);
        $('#countMayo').text(monthlyCounts[5]);
        $('#countJunio').text(monthlyCounts[6]);
        $('#countJulio').text(monthlyCounts[7]);
        $('#countAgosto').text(monthlyCounts[8]);
        $('#countSeptiembre').text(monthlyCounts[9]);
        $('#countOctubre').text(monthlyCounts[10]);
        $('#countNoviembre').text(monthlyCounts[11]);
        $('#countDiciembre').text(monthlyCounts[12]);
      }

      // Función para aplicar filtros combinados
      function applyFilters() {
        if (!table) return;

        $.fn.dataTable.ext.search.pop(); // Limpiar filtros personalizados previos

        $.fn.dataTable.ext.search.push(
          function(settings, data, dataIndex) {
            // Obtener los datos del objeto row
            var rowData = table.row(dataIndex).data();
            var fechaProgramada = rowData.fecha_programada || '';
            var estado = rowData.estado || '';

            // Filtro por año
            if (activeYear) {
              if (!fechaProgramada.startsWith(activeYear)) {
                return false;
              }
            }

            // Filtro por mes
            if (activeMonth) {
              if (fechaProgramada) {
                var parts = fechaProgramada.split("-");
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
            if (activeStatus) {
              if (estado.trim() !== activeStatus) {
                return false;
              }
            }

            return true;
          }
        );

        table.draw();
        generateYearCards();
        updateStatusCounts();
        updateMonthlyCounts();
      }

      // Click en tarjetas de año
      $(document).on('click', '.card-year', function() {
        var year = $(this).data('year');

        if ($(this).hasClass('active')) {
          $(this).removeClass('active');
          activeYear = null;
        } else {
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
          $(this).removeClass('active');
          activeMonth = null;
        } else {
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
          $(this).removeClass('active');
          activeStatus = null;
        } else {
          $('.card-status').removeClass('active');
          $(this).addClass('active');
          activeStatus = status;
        }

        applyFilters();
      });

      // Botón para limpiar todos los filtros de tarjetas
      $('#btnClearCardFilters').on('click', function() {
        activeYear = null;
        activeMonth = null;
        activeStatus = null;

        $('.card-year').removeClass('active');
        $('.card-month').removeClass('active');
        $('.card-status').removeClass('active');

        $.fn.dataTable.ext.search.pop();

        if (table) {
          table.draw();
          generateYearCards();
          updateStatusCounts();
          updateMonthlyCounts();
        }
      });

      // Actualizar contadores cuando la tabla se redibuja
      table.on('draw', function() {
        updateStatusCounts();
        updateMonthlyCounts();
        generateYearCards();
      });

      // Inicializar contadores y tarjetas de año
      updateStatusCounts();
      updateMonthlyCounts();
      generateYearCards();

      // Filtros por columna (global o por select en tfoot)
      $('tfoot .filter-search').on('keyup change', function() {
        var index = $(this).parent().index();
        table.column(index).search(this.value).draw();
      });

      // Evento para expandir/contraer la fila (child row)
      $('#cronogramaTable tbody').on('click', 'td.details-control', function() {
        var tr = $(this).closest('tr');
        var row = table.row(tr);
        if (row.child.isShown()) {
          row.child.hide();
          tr.removeClass('shown');
        } else {
          row.child(format(row.data())).show();
          tr.addClass('shown');
        }
      });

      // Inline editing: detecta clic en celdas con clases editable, editable-select o editable-date
      $('#cronogramaTable').on('click', '.editable, .editable-select, .editable-date', function(e) {
        e.stopPropagation(); // Evita que se active la expansión de fila
        if ($(this).find('input, select').length) return;
        var cell = $(this);
        var field = cell.data('field');
        var id = cell.data('id');
        var currentValue = cell.text().trim();
        currentValue = currentValue === '&nbsp;' ? '' : currentValue;

        if (cell.hasClass('editable-date')) {
          var input = $('<input>', {
            type: 'date',
            class: 'form-control form-control-sm',
            value: currentValue
          });
          cell.html(input);
          input.focus();
          input.on('blur change', function() {
            var newValue = input.val();
            cell.html(newValue || '&nbsp;');
            updateField(id, field, newValue, cell);
          });
        } else if (cell.hasClass('editable-select')) {
          var options = [];
          if (field === 'estado') {
            options = ['PROGRAMADA', 'EJECUTADA', 'CANCELADA POR EL CLIENTE', 'REPROGRAMADA'];
          } else if (field === 'perfil_de_asistentes') {
            options = ['TODOS', 'DIRECTIVOS_ALTA_GERENCIA', 'JEFES_Y_SUPERVISORES', 'VIGIA_SST', 'BRIGADA_EMERGENCIAS', 'COMITE_SEGURIDAD_VIAL', 'MIEMBROS_COPASST', 'MIEMBROS_COMITE_CONVIVENCIA', 'TRABAJADORES_RIESGOS_CRITICOS', 'PERSONAL_ASEO_MANTENIMIENTO', 'BRIGADA'];
          } else if (field === 'indicador_de_realizacion_de_la_capacitacion') {
            options = ['SE EJECUTO EN LA FECHA O ANTES', 'SE EJECUTO DESPUES', 'DECLINADA', 'NO SE REALIZÓ'];
          }
          var select = $('<select>', {
            class: 'form-select form-select-sm'
          });
          options.forEach(function(option) {
            select.append($('<option>', {
              value: option,
              text: option,
              selected: option === currentValue
            }));
          });
          cell.html(select);
          select.focus();
          select.on('blur change', function() {
            setTimeout(function() {
              var newValue = select.val();
              cell.html(newValue || '&nbsp;');
              updateField(id, field, newValue, cell);
            }, 200);
          });
        } else {
          var input = $('<input>', {
            type: 'text',
            class: 'form-control form-control-sm',
            value: currentValue
          });
          cell.html(input);
          input.focus();
          input.on('blur keypress', function(e) {
            if (e.type === 'keypress' && e.which !== 13) return; // Solo procesar en blur o Enter
            var newValue = input.val();
            cell.html(newValue || '&nbsp;');
            updateField(id, field, newValue, cell);
          });
        }
      });

      // Función para enviar la actualización vía AJAX
      function updateField(id, field, value, cell) {
        $.ajax({
          url: '<?= base_url('/api/updatecronogCapacitacion') ?>',
          method: 'POST',
          data: {
            id: id,
            field: field,
            value: value
          },
          success: function(response) {
            if (response.success) {
              console.log('Registro actualizado correctamente');
              
              // Si se actualizaron los campos que afectan el % Cobertura, actualizar manualmente
              if (field === 'numero_de_asistentes_a_capacitacion' || field === 'numero_total_de_personas_programadas') {
                var row = table.row(cell.closest('tr'));
                var rowData = row.data();
                
                // Actualizar el dato en el objeto de la fila
                rowData[field] = value;
                
                // Recalcular y actualizar la columna de % Cobertura
                var asistentes = parseFloat(rowData.numero_de_asistentes_a_capacitacion) || 0;
                var programados = parseFloat(rowData.numero_total_de_personas_programadas) || 0;
                var porcentaje = programados > 0 ? Math.round((asistentes / programados) * 100) : 0;
                
                // Encontrar y actualizar la celda del % Cobertura (columna 15)
                var coberturaCell = cell.closest('tr').find('td').eq(15);
                coberturaCell.text(porcentaje + '%');
              }
            } else {
              alert('Error: ' + response.message);
            }
          },
          error: function(xhr, status, error) {
            console.error('Error al comunicarse con el servidor:', error);
            alert('Error al comunicarse con el servidor: ' + error);
          }
        });
      }

      // Botón para cargar datos cuando se haga clic
      $("#loadData").click(function() {
        var clientId = $("#clientSelect").val();
        if (clientId) {
          localStorage.setItem('selectedClient', clientId);
          table.ajax.reload(function() {
            updateStatusCounts();
            updateMonthlyCounts();
            generateYearCards();
          });
        } else {
          alert('Por favor, seleccione un cliente.');
        }
      });

      // Recargar la tabla automáticamente al cambiar el select
      $('#clientSelect').on('change', function() {
        var clientId = $(this).val();
        if (clientId) {
          localStorage.setItem('selectedClient', clientId);
          table.ajax.reload(function() {
            updateStatusCounts();
            updateMonthlyCounts();
            generateYearCards();
          });
          // Cargar información del contrato
          loadClientContract(clientId);
        } else {
          // Mostrar mensaje de "Seleccione un cliente"
          $('#contractCardContainer').html(`
            <div class="no-contract-card h-100 d-flex flex-column justify-content-center">
              <i class="fas fa-hand-pointer fa-3x mb-3 opacity-75"></i>
              <h5>Seleccione un Cliente</h5>
              <p class="mb-0 opacity-75">Seleccione un cliente para ver la información de su contrato.</p>
            </div>
          `);
        }
      });

      // Función para cargar el contrato del cliente
      function loadClientContract(clientId) {
        $.ajax({
          url: '<?= base_url('/cronogCapacitacion/getClientContract') ?>',
          method: 'GET',
          data: { id_cliente: clientId },
          dataType: 'json',
          success: function(response) {
            if (response.success && response.contract) {
              var contract = response.contract;

              // Determinar clase de frecuencia
              var frecuencia = (contract.frecuencia_visitas || '').toLowerCase();
              var frecuenciaClass = 'frecuencia-default';
              if (frecuencia.indexOf('mensual') !== -1 && frecuencia.indexOf('bimensual') === -1) {
                frecuenciaClass = 'frecuencia-mensual';
              } else if (frecuencia.indexOf('bimensual') !== -1) {
                frecuenciaClass = 'frecuencia-bimensual';
              } else if (frecuencia.indexOf('trimestral') !== -1) {
                frecuenciaClass = 'frecuencia-trimestral';
              }

              // Determinar clase de estado
              var estadoContrato = (contract.estado || 'activo').toLowerCase();
              var estadoClass = 'status-' + estadoContrato;

              // Formatear fechas
              var fechaInicio = contract.fecha_inicio ? formatDate(contract.fecha_inicio) : 'N/A';
              var fechaFin = contract.fecha_fin ? formatDate(contract.fecha_fin) : 'N/A';

              var html = `
                <div class="contract-card p-3 h-100">
                  <div class="contract-header">
                    <i class="fas fa-file-contract me-2"></i> Último Contrato
                    <span class="contract-status ${estadoClass} float-end">
                      ${capitalizeFirst(contract.estado || 'Activo')}
                    </span>
                  </div>
                  <div class="text-center mb-3">
                    <span class="frecuencia-badge ${frecuenciaClass}">
                      <i class="fas fa-calendar-check me-1"></i>
                      ${contract.frecuencia_visitas || 'No definida'}
                    </span>
                  </div>
                  <div class="contract-item">
                    <span class="contract-label"><i class="fas fa-hashtag me-1"></i> Número:</span>
                    <span class="contract-value">${contract.numero_contrato || 'N/A'}</span>
                  </div>
                  <div class="contract-item">
                    <span class="contract-label"><i class="fas fa-play-circle me-1"></i> Inicio:</span>
                    <span class="contract-value">${fechaInicio}</span>
                  </div>
                  <div class="contract-item">
                    <span class="contract-label"><i class="fas fa-stop-circle me-1"></i> Fin:</span>
                    <span class="contract-value">${fechaFin}</span>
                  </div>
                  <div class="mt-3 text-center">
                    <a href="<?= base_url('/contracts/view/') ?>${contract.id_contrato}" class="btn btn-light btn-sm">
                      <i class="fas fa-eye me-1"></i> Ver Contrato
                    </a>
                  </div>
                </div>
              `;
              $('#contractCardContainer').html(html);
            } else {
              // No hay contrato
              $('#contractCardContainer').html(`
                <div class="no-contract-card h-100 d-flex flex-column justify-content-center">
                  <i class="fas fa-file-contract fa-3x mb-3 opacity-75"></i>
                  <h5>Sin Contrato Registrado</h5>
                  <p class="mb-3 opacity-75">Este cliente no tiene contratos registrados en el sistema.</p>
                  <a href="<?= base_url('/contracts/create/') ?>${clientId}" class="btn btn-light btn-sm">
                    <i class="fas fa-plus me-1"></i> Crear Contrato
                  </a>
                </div>
              `);
            }
          },
          error: function() {
            $('#contractCardContainer').html(`
              <div class="no-contract-card h-100 d-flex flex-column justify-content-center">
                <i class="fas fa-exclamation-triangle fa-3x mb-3 opacity-75"></i>
                <h5>Error al cargar</h5>
                <p class="mb-0 opacity-75">No se pudo cargar la información del contrato.</p>
              </div>
            `);
          }
        });
      }

      // Función auxiliar para formatear fecha
      function formatDate(dateString) {
        if (!dateString) return 'N/A';
        var parts = dateString.split('-');
        if (parts.length === 3) {
          return parts[2] + '/' + parts[1] + '/' + parts[0];
        }
        return dateString;
      }

      // Función auxiliar para capitalizar primera letra
      function capitalizeFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
      }

      // Cargar contrato si hay un cliente almacenado
      var storedClient = localStorage.getItem('selectedClient');
      if (storedClient) {
        loadClientContract(storedClient);
      }

      // Manejador de clic para los botones mensuales (asignación rápida de fecha)
      $(document).on('click', '.btn-month', function() {
        var $button = $(this);
        var trainingId = $button.data('id');
        var month = $button.data('month');

        // Deshabilitar el botón mientras se procesa
        $button.prop('disabled', true).css('opacity', '0.5');

        $.ajax({
          url: '<?= base_url('/cronogCapacitacion/updateDateByMonth') ?>',
          method: 'POST',
          data: {
            id: trainingId,
            month: month,
            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
          },
          dataType: 'json',
          success: function(response) {
            if (response.success) {
              // Recargar la tabla sin resetear la paginación
              table.ajax.reload(null, false);

              // Marcar el botón como "tiene fecha"
              $button.addClass('has-date');

              // Mostrar mensaje de éxito temporal
              var mesesEspanol = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
              var mesNombre = mesesEspanol[month - 1];

              // Crear toast de Bootstrap para feedback visual
              var toastHtml = '<div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">' +
                '<div class="d-flex">' +
                  '<div class="toast-body">' +
                    '✓ Fecha programada actualizada a ' + mesNombre + ' (' + response.formatted + ')' +
                  '</div>' +
                  '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>' +
                '</div>' +
              '</div>';

              $('body').append(toastHtml);
              var toastElement = $('.toast').last()[0];
              var toast = new bootstrap.Toast(toastElement, { autohide: true, delay: 3000 });
              toast.show();

              // Eliminar el toast del DOM después de que se oculte
              toastElement.addEventListener('hidden.bs.toast', function() {
                $(toastElement).remove();
              });

            } else {
              alert('Error: ' + response.message);
            }
          },
          error: function(xhr, status, error) {
            alert('Error al actualizar la fecha: ' + error);
            console.error('Error AJAX:', xhr.responseText);
          },
          complete: function() {
            // Rehabilitar el botón
            $button.prop('disabled', false).css('opacity', '1');
          }
        });
      });

      // Botón para restablecer filtros y estado guardado
      $("#clearState").on("click", function() {
        localStorage.removeItem('selectedClient');
        var storageKey = 'DataTables_' + table.table().node().id + '_' + window.location.pathname;
        localStorage.removeItem(storageKey);
        table.state.clear();
        $('tfoot .filter-search').each(function() {
          $(this).val('');
        });
        table.columns().search('').draw();
        $("#clientSelect").val(null).trigger("change");
      });

      // Inicializar tooltips de Bootstrap
      function initializeTooltips() {
        // Limpiar tooltips existentes para evitar duplicados
        $('[data-bs-toggle="tooltip"]').each(function() {
          var tooltip = bootstrap.Tooltip.getInstance(this);
          if (tooltip) {
            tooltip.dispose();
          }
        });
        
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
          return new bootstrap.Tooltip(tooltipTriggerEl, {
            trigger: 'hover focus',
            delay: { show: 500, hide: 100 }
          });
        });
      }
      initializeTooltips();
      table.on('draw.dt', function() {
        setTimeout(initializeTooltips, 100);
      });

      // Cargar lista de clientes en el modal cuando se abre
      $('#generateTrainingModal').on('show.bs.modal', function() {
        $.ajax({
          url: '<?= base_url('/cronogCapacitacion/getClients') ?>',
          method: 'GET',
          dataType: 'json',
          success: function(clients) {
            var $select = $('#modalClientSelect');
            $select.empty();
            $select.append('<option value="">Seleccione un cliente...</option>');

            clients.forEach(function(client) {
              $select.append('<option value="' + client.id_cliente + '">' + client.nombre_cliente + '</option>');
            });

            // Inicializar Select2 en el modal si está disponible
            if ($.fn.select2) {
              $select.select2({
                dropdownParent: $('#generateTrainingModal'),
                placeholder: 'Buscar cliente...',
                allowClear: true
              });
            }
          },
          error: function(xhr, status, error) {
            console.error('Error al cargar clientes:', error);
            alert('Error al cargar la lista de clientes');
          }
        });
      });
    });

  </script>
</body>

</html>
