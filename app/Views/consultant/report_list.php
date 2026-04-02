<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Lista de Reportes</title>
  <!-- Bootstrap CSS, DataTables y DataTables Buttons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
  <link href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css" rel="stylesheet" />
  <!-- Select2 CSS para el select con input text -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
  <!-- Iconos Bootstrap -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    /* Variables CSS para consistencia */
    :root {
      --primary-color: #007bff;
      --primary-dark: #0056b3;
      --secondary-color: #6c757d;
      --success-color: #28a745;
      --warning-color: #ffc107;
      --danger-color: #dc3545;
      --light-bg: #f8f9fa;
      --border-radius: 8px;
      --box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    /* Estilos generales mejorados */
    .container-fluid {
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      border-radius: var(--border-radius);
      padding: 30px;
      margin-top: 20px;
      box-shadow: var(--box-shadow);
    }

    h2, h3 {
      color: var(--primary-dark);
      font-weight: 600;
      margin-bottom: 25px;
    }

    /* Estilos para filtros superiores */
    .row.g-3 {
      background: white;
      padding: 20px;
      border-radius: var(--border-radius);
      box-shadow: var(--box-shadow);
      margin-bottom: 25px;
    }

    .form-label {
      color: var(--primary-dark);
      font-weight: 500;
      margin-bottom: 8px;
    }

    .form-control, .form-select {
      border: 2px solid #e9ecef;
      border-radius: var(--border-radius);
      padding: 10px 15px;
      transition: all 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
    }

    /* La tabla ocupa el ancho completo */
    table.dataTable {
      width: 100%;
      background: white;
      border-radius: var(--border-radius);
      overflow: hidden;
      box-shadow: var(--box-shadow);
    }

    /* Estilos generales para celdas de la tabla */
    table.dataTable thead th {
      background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
      color: white;
      font-weight: 600;
      border: none;
      padding: 15px 8px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      vertical-align: middle;
    }

    table.dataTable tbody td {
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      vertical-align: middle;
      padding: 12px 8px;
      border-bottom: 1px solid #e9ecef;
    }

    table.dataTable tbody tr:hover {
      background-color: rgba(0, 123, 255, 0.05);
    }

    table.dataTable tfoot th {
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      vertical-align: middle;
    }

    /* Clases para columnas sin truncamiento */
    td.title-col,
    td.tipodoc-col,
    td.tiporeporte-col {
      white-space: normal;
      overflow: visible;
      text-overflow: clip;
    }

    /* Columna Observaciones (se trunca a 40 caracteres) */
    td.observaciones-col {
      max-width: 40ch;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    /* Columna Enlace: muestra solo un icono */
    td.enlace-col {
      text-align: center;
    }

    /* Estilo para los filtros en el tfoot */
    tfoot th {
      padding: 12px 10px;
      background: linear-gradient(135deg, var(--light-bg), #dee2e6);
      border-top: 2px solid var(--primary-color);
    }

    /* Alinear la búsqueda a la izquierda y mejorar visibilidad */
    div.dataTables_filter {
      text-align: left !important;
      margin-bottom: 20px;
      padding: 15px;
      background: linear-gradient(135deg, #007bff, #0056b3);
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
    }

    div.dataTables_filter label {
      color: white !important;
      font-weight: bold;
      font-size: 16px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    div.dataTables_filter input {
      margin-left: 10px !important;
      padding: 8px 15px !important;
      border: 2px solid #ffffff !important;
      border-radius: 25px !important;
      font-size: 14px !important;
      width: 300px !important;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1) !important;
      transition: all 0.3s ease !important;
    }

    div.dataTables_filter input:focus {
      outline: none !important;
      border-color: #ffc107 !important;
      box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.3) !important;
      transform: scale(1.02) !important;
    }

    /* Botones armonizados */
    .btn {
      border-radius: var(--border-radius);
      font-weight: 500;
      padding: 8px 16px;
      border: none;
      transition: all 0.3s ease;
    }

    .btn-primary {
      background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
      box-shadow: 0 2px 8px rgba(0, 123, 255, 0.3);
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(0, 123, 255, 0.4);
    }

    .btn-success {
      background: linear-gradient(135deg, var(--success-color), #1e7e34);
      box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
    }

    .btn-success:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);
    }

    .btn-warning {
      background: linear-gradient(135deg, var(--warning-color), #d39e00);
      color: #212529;
      box-shadow: 0 2px 8px rgba(255, 193, 7, 0.3);
    }

    .btn-warning:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(255, 193, 7, 0.4);
    }

    .btn-danger {
      background: linear-gradient(135deg, var(--danger-color), #bd2130);
      box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
    }

    .btn-danger:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4);
    }

    /* Botones de DataTables */
    .dt-buttons {
      margin-bottom: 20px;
    }

    .dt-buttons .btn {
      margin-right: 8px;
      border-radius: var(--border-radius);
    }

    /* Icono de detalles: cursor pointer */
    .details-control {
      cursor: pointer;
      margin-right: 5px;
      color: var(--primary-color);
      font-size: 18px;
      transition: all 0.3s ease;
    }

    .details-control:hover {
      color: var(--primary-dark);
      transform: scale(1.1);
    }

    /* Alertas mejoradas */
    .alert {
      border-radius: var(--border-radius);
      border: none;
      box-shadow: var(--box-shadow);
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
  </style>
</head>

<body class="bg-light">
  <!-- Navbar (sin cambios en estructura) -->
  <nav style="background-color: white; position: fixed; top: 0; width: 100%; z-index: 1000; padding: 10px 0; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; max-width: 1200px; margin: 0 auto;">
      <!-- Logo izquierdo -->
      <div>
        <a href="https://dashboard.cycloidtalent.com/login">
          <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo" style="height: 100px;">
        </a>
      </div>
      <!-- Logo centro -->
      <div>
        <a href="https://cycloidtalent.com/index.php/consultoria-sst">
          <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST Logo" style="height: 100px;">
        </a>
      </div>
      <!-- Logo derecho -->
      <div>
        <a href="https://cycloidtalent.com/">
          <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloids Logo" style="height: 100px;">
        </a>
      </div>
    </div>
    <!-- Fila de botones -->
    <div style="display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 10px auto 0; padding: 0 20px;">
      <!-- Botón izquierdo -->
      <div style="text-align: center;">
        <h2 style="margin: 0; font-size: 16px;">Ir a Dashboard</h2>
        <a href="<?= base_url('/dashboardconsultant') ?>" class="btn btn-primary btn-sm mt-1">Ir a DashBoard</a>
      </div>
      <!-- Botón derecho -->
      <div style="text-align: center;">
        <h2 style="margin: 0; font-size: 16px;">Añadir Registro</h2>
        <a href="<?= base_url('/addReport') ?>" class="btn btn-success btn-sm mt-1">Añadir Registro</a>
      </div>
    </div>
  </nav>

  <!-- Espaciado para evitar que el contenido quede oculto por el navbar -->
  <div style="height: 200px;"></div>

  <!-- Contenedor fluid -->
  <div class="container-fluid my-4">
    <!-- Encabezado con título y filtros -->
    <div class="mb-4">
      <h2 class="mb-3">Lista de Reportes</h2>

      <!-- Selector de Año (server-side) -->
      <div class="d-flex align-items-center gap-3 mb-3 p-3" style="background: white; border-radius: var(--border-radius); box-shadow: var(--box-shadow);">
        <div class="section-title mb-0" style="margin: 0; white-space: nowrap;">
          <i class="fas fa-calendar-alt"></i> Año
        </div>
        <select id="yearSelector" class="form-select" style="max-width: 200px;">
          <?php foreach ($availableYears as $year): ?>
            <option value="<?= $year ?>" <?= ($selectedYear == $year) ? 'selected' : '' ?>><?= $year ?></option>
          <?php endforeach; ?>
          <option value="all" <?= ($selectedYear === 'all') ? 'selected' : '' ?>>Todos los años</option>
        </select>
        <span class="text-muted small" id="reportCountLabel">Cargando...</span>
      </div>

      <!-- Mensaje informativo -->
      <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="fas fa-info-circle"></i>
        <strong>Filtros:</strong> Usa el selector de año para cargar reportes de un período específico.
        Las tarjetas de mes filtran dentro del año seleccionado.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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

      <div class="row g-3 align-items-end">
        <!-- Filtro por Cliente -->
        <div class="col-md-4">
          <label for="clientFilter" class="form-label">Filtrar por Cliente:</label>
          <select id="clientFilter" class="form-select">
            <option value="">Todos</option>
            <?php foreach ($clients as $client) : ?>
              <option value="<?= htmlspecialchars($client['nombre_cliente']) ?>"><?= htmlspecialchars($client['nombre_cliente']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <!-- Filtro por Mes -->
        <div class="col-md-2">
          <label for="monthFilter" class="form-label"><i class="fas fa-calendar-week"></i> Mes:</label>
          <select id="monthFilter" class="form-select">
            <option value="">Todos</option>
            <option value="current">Mes actual</option>
            <option value="previous">Mes anterior</option>
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
        <!-- Filtro por Fecha Desde -->
        <div class="col-md-3">
          <label for="dateFrom" class="form-label">Fecha Desde:</label>
          <input type="date" id="dateFrom" class="form-control">
        </div>
        <!-- Filtro por Fecha Hasta -->
        <div class="col-md-3">
          <label for="dateTo" class="form-label">Fecha Hasta:</label>
          <input type="date" id="dateTo" class="form-control">
        </div>
      </div>

    </div>

    <h3 class="mb-3">Reportes</h3>

    <?php if (session()->get('msg')) : ?>
      <div class="alert alert-info" style="background: linear-gradient(135deg, #d1ecf1, #bee5eb); border-left: 4px solid var(--primary-color);">
        <i class="bi bi-info-circle"></i> <?= session()->get('msg') ?>
      </div>
    <?php endif; ?>

      <!-- Botón para restablecer filtros y exportar -->
      <div class="mb-3" style="background: white; padding: 15px; border-radius: var(--border-radius); box-shadow: var(--box-shadow);">
        <button id="clearState" class="btn btn-danger">
          <i class="bi bi-arrow-clockwise"></i> Restablecer Filtros
        </button>
      </div>

      <table id="reportTable" class="table table-striped table-hover">
        <thead>
          <tr>
            <th>Acciones</th>
            <th>Fecha de Creación</th>
            <th>Enlace</th>
            <th>ID</th>
            <th>Título del Reporte</th>
            <th>Tipo de Documento</th>
            <th>Tipo de Reporte</th>
            <th>Estado</th>
            <th>Observaciones</th>
            <th>ID Cliente</th>
            <th>Nombre del Cliente</th>
          </tr>
        </thead>
        <tfoot>
          <tr>
            <th>Acciones</th>
            <th>Fecha de Creación</th>
            <th>Enlace</th>
            <th>ID</th>
            <th>Título del Reporte</th>
            <th>Tipo de Documento</th>
            <th>Tipo de Reporte</th>
            <th>Estado</th>
            <th>Observaciones</th>
            <th>ID Cliente</th>
            <th>Nombre del Cliente</th>
          </tr>
        </tfoot>
        <tbody>
          <!-- Server-side DataTables fills this via AJAX -->
        </tbody>
      </table>

    <!-- Sección para Descargar Documentación por Contrato -->
    <div class="mt-5 mb-4">
      <div class="section-title">
        <i class="fas fa-download"></i> Descargar Documentación por Contrato
      </div>
      <div class="row g-3 align-items-end" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); padding: 20px; border-radius: 10px;">
        <div class="col-md-6">
          <label for="clientDownload" class="form-label text-white fw-bold">Seleccionar Cliente:</label>
          <select id="clientDownload" class="form-select">
            <option value="">-- Seleccione un cliente --</option>
            <?php foreach ($clients as $client) : ?>
              <option value="<?= $client['id_cliente'] ?>"><?= htmlspecialchars($client['nombre_cliente']) ?> (NIT: <?= htmlspecialchars($client['nit_cliente']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <button type="button" id="btnVerDocumentacion" class="btn btn-light w-100" disabled>
            <i class="fas fa-eye"></i> Ver Documentación
          </button>
        </div>
        <div class="col-md-3">
          <button type="button" id="btnDescargarZip" class="btn btn-warning w-100" disabled>
            <i class="fas fa-file-archive"></i> Descargar ZIP
          </button>
        </div>
        <div class="col-12">
          <small class="text-white">
            <i class="fas fa-info-circle"></i> Seleccione un cliente para ver o descargar todos los documentos registrados durante el período de su último contrato.
          </small>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer (sin cambios) -->
  <footer style="background-color: white; padding: 20px 0; border-top: 1px solid #B0BEC5; margin-top: 40px; color: #3A3F51; font-size: 14px; text-align: center;">
    <div style="max-width: 1200px; margin: 0 auto; display: flex; flex-direction: column; align-items: center;">
      <p style="margin: 0; font-weight: bold;">Cycloid Talent SAS</p>
      <p style="margin: 5px 0;">Todos los derechos reservados © 2024</p>
      <p style="margin: 5px 0;">NIT: 901.653.912</p>
      <p style="margin: 5px 0;">
        Sitio oficial:
        <a href="https://cycloidtalent.com/" target="_blank" style="color: #007BFF; text-decoration: none;">
          https://cycloidtalent.com/
        </a>
      </p>
      <p style="margin: 15px 0 5px;"><strong>Nuestras Redes Sociales:</strong></p>
      <div style="display: flex; gap: 15px; justify-content: center;">
        <a href="https://www.facebook.com/CycloidTalent" target="_blank" style="color: #3A3F51; text-decoration: none;">
          <img src="https://cdn-icons-png.flaticon.com/512/733/733547.png" alt="Facebook" style="height: 24px; width: 24px;">
        </a>
        <a href="https://co.linkedin.com/company/cycloid-talent" target="_blank" style="color: #3A3F51; text-decoration: none;">
          <img src="https://cdn-icons-png.flaticon.com/512/733/733561.png" alt="LinkedIn" style="height: 24px; width: 24px;">
        </a>
        <a href="https://www.instagram.com/cycloid_talent?igsh=Nmo4d2QwZDg5dHh0" target="_blank" style="color: #3A3F51; text-decoration: none;">
          <img src="https://cdn-icons-png.flaticon.com/512/733/733558.png" alt="Instagram" style="height: 24px; width: 24px;">
        </a>
        <a href="https://www.tiktok.com/@cycloid_talent?_t=8qBSOu0o1ZN&_r=1" target="_blank" style="color: #3A3F51; text-decoration: none;">
          <img src="https://cdn-icons-png.flaticon.com/512/3046/3046126.png" alt="TikTok" style="height: 24px; width: 24px;">
        </a>
      </div>
    </div>
  </footer>

  <!-- Scripts: jQuery, Bootstrap, DataTables y Buttons -->
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.colVis.min.js"></script>
  <!-- Select2 JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

  <script>
    // Función para generar el contenido del row child details
    function format(d) {
      // d[0]: Acciones (omitido)
      // d[1]: Fecha de Creación
      // d[2]: Enlace
      // d[3]: ID
      // d[4]: Título del Reporte
      // d[5]: Tipo de Documento
      // d[6]: Tipo de Reporte
      // d[7]: Estado
      // d[8]: Observaciones
      // d[9]: ID Cliente
      // d[10]: Nombre del Cliente
      return '<div style="overflow:auto;">' +
        '<table style="width:100%;" class="table table-sm table-borderless">' +
        '<tr><td style="width:30%;"><strong>Fecha de Creación:</strong></td><td style="width:70%;">' + d[1] + '</td></tr>' +
        '<tr><td style="width:30%;"><strong>Enlace:</strong></td><td style="width:70%;">' + d[2] + '</td></tr>' +
        '<tr><td style="width:30%;"><strong>ID:</strong></td><td style="width:70%;">' + d[3] + '</td></tr>' +
        '<tr><td style="width:30%;"><strong>Título del Reporte:</strong></td><td style="width:70%;">' + d[4] + '</td></tr>' +
        '<tr><td style="width:30%;"><strong>Tipo de Documento:</strong></td><td style="width:70%;">' + d[5] + '</td></tr>' +
        '<tr><td style="width:30%;"><strong>Tipo de Reporte:</strong></td><td style="width:70%;">' + d[6] + '</td></tr>' +
        '<tr><td style="width:30%;"><strong>Estado:</strong></td><td style="width:70%;">' + d[7] + '</td></tr>' +
        '<tr><td style="width:30%;"><strong>Observaciones:</strong></td><td style="width:70%;">' + d[8] + '</td></tr>' +
        '<tr><td style="width:30%;"><strong>ID Cliente:</strong></td><td style="width:70%;">' + d[9] + '</td></tr>' +
        '<tr><td style="width:30%;"><strong>Nombre del Cliente:</strong></td><td style="width:70%;">' + d[10] + '</td></tr>' +
        '</table>' +
        '</div>';
    }

    $(document).ready(function () {
      // Variable global para filtro activo de mes
      var activeMonth = null;
      var selectedYear = '<?= $selectedYear ?>';

      // Mapeos cliente para Quick Access
      var reverseClientMap = {
        <?php foreach ($clients as $client): ?>
        '<?= addslashes($client['nombre_cliente']) ?>': '<?= $client['id_cliente'] ?>',
        <?php endforeach; ?>
      };
      var clientMap = {
        <?php foreach ($clients as $client): ?>
        '<?= $client['id_cliente'] ?>': '<?= addslashes($client['nombre_cliente']) ?>',
        <?php endforeach; ?>
      };

      // Helper: get current filter values
      function getFilterParams() {
        return {
          year: selectedYear,
          client: $('#clientFilter').val() || '',
          dateFrom: $('#dateFrom').val() || '',
          dateTo: $('#dateTo').val() || '',
          month: activeMonth || ''
        };
      }

      // Year selector: update var and reload table (no full page reload)
      $('#yearSelector').on('change', function() {
        selectedYear = $(this).val();
        // Update URL without reload for bookmarking
        var newUrl = '<?= base_url('/reportList') ?>?year=' + selectedYear;
        window.history.replaceState({}, '', newUrl);
        table.ajax.reload();
      });

      // ============================================
      // DataTables — Server-Side Processing
      // ============================================
      const table = $('#reportTable').DataTable({
        serverSide: true,
        processing: true,
        ajax: {
          url: '<?= base_url('/api/reportList') ?>',
          data: function(d) {
            var filters = getFilterParams();
            d.year     = filters.year;
            d.client   = filters.client;
            d.dateFrom = filters.dateFrom;
            d.dateTo   = filters.dateTo;
            d.month    = filters.month;
          },
          dataSrc: function(json) {
            // Update month cards from server response
            if (json.monthCounts) {
              updateMonthCardsFromServer(json.monthCounts);
            }
            // Update count label
            var yearLabel = selectedYear === 'all' ? 'todos los años' : selectedYear;
            $('#reportCountLabel').text('Mostrando ' + json.recordsFiltered + ' reportes de ' + yearLabel);
            return json.data;
          }
        },
        language: {
          url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
        },
        pageLength: 20,
        lengthMenu: [
          [20, 50, 100],
          [20, 50, 100]
        ],
        order: [[1, "desc"]],
        columnDefs: [
          {
            targets: [0, 2],
            orderable: false,
            searchable: false
          },
          {
            targets: 8,
            className: "observaciones-col"
          }
        ],
        stateSave: true,
        stateSaveCallback: function (settings, data) {
          localStorage.setItem('DataTables_reportTable', JSON.stringify(data));
        },
        stateLoadCallback: function (settings) {
          return JSON.parse(localStorage.getItem('DataTables_reportTable'));
        },
        dom: 'Blfrtip',
        buttons: [
          {
            text: '<i class="bi bi-file-earmark-excel"></i> Exportar a Excel',
            className: 'btn btn-success btn-sm',
            titleAttr: 'Exportar todos los registros filtrados a Excel',
            action: function() {
              var params = getFilterParams();
              var search = table.search() || '';
              var url = '<?= base_url('/api/reportList/export') ?>?'
                + 'year=' + encodeURIComponent(params.year)
                + '&client=' + encodeURIComponent(params.client)
                + '&dateFrom=' + encodeURIComponent(params.dateFrom)
                + '&dateTo=' + encodeURIComponent(params.dateTo)
                + '&month=' + encodeURIComponent(params.month)
                + '&search=' + encodeURIComponent(search);
              // Agregar filtros por columna activos
              table.columns().every(function(idx) {
                var colSearch = this.search();
                if (colSearch) {
                  url += '&columns[' + idx + ']=' + encodeURIComponent(colSearch);
                }
              });
              window.location.href = url;
            }
          },
          {
            extend: 'colvis',
            text: '<i class="bi bi-eye"></i> Visibilidad de Columnas',
            className: 'btn btn-secondary btn-sm',
            titleAttr: 'Mostrar u Ocultar Columnas'
          }
        ],
        initComplete: function () {
          var api = this.api();
          api.columns().every(function () {
            var column = this;
            var columnIdx = column.index();
            var $footerCell = $(column.footer()).empty();

            // Text input filters: Título (4), Observaciones (8), Nombre Cliente (10)
            if ([4, 8, 10].indexOf(columnIdx) !== -1) {
              var searchTimer = null;
              var input = $('<input type="text" class="form-control form-control-sm" placeholder="Buscar...">')
                .appendTo($footerCell)
                .on('keyup change', function () {
                  var val = this.value;
                  clearTimeout(searchTimer);
                  searchTimer = setTimeout(function() {
                    if (column.search() !== val) {
                      column.search(val).draw();
                    }
                  }, 400);
                });
              var state = api.state.loaded();
              if (state && state.columns[columnIdx].search && state.columns[columnIdx].search.search) {
                input.val(state.columns[columnIdx].search.search);
              }
            }
            // Dropdown filters: Tipo Documento (5), Tipo Reporte (6), Estado (7)
            else if ([5, 6, 7].indexOf(columnIdx) !== -1) {
              var select = $('<select class="form-select form-select-sm"><option value="">Todos</option></select>')
                .appendTo($footerCell)
                .on('change', function () {
                  var val = $.fn.dataTable.util.escapeRegex($(this).val());
                  column.search(val ? '^' + val + '$' : '', true, false).draw();
                });
              $footerCell.data('select', select);

              var state = api.state.loaded();
              if (state && state.columns[columnIdx].search && state.columns[columnIdx].search.search) {
                var searchVal = state.columns[columnIdx].search.search.replace(/^\^|\$$/g, '');
                select.val(searchVal);
              }
            }
          });

          // Populate dropdown options via separate AJAX for distinct values
          loadDropdownOptions();
        },
        drawCallback: function () {
          // Re-populate dropdown options after each server draw
          loadDropdownOptions();
        }
      });

      // Load distinct values for dropdown filters from current data context
      function loadDropdownOptions() {
        var api = table;
        // For server-side mode, we populate from the current page data
        [5, 6, 7].forEach(function(columnIdx) {
          var column = api.column(columnIdx);
          var $footerCell = $(column.footer());
          var select = $footerCell.data('select');
          if (select) {
            var currentVal = select.val();
            select.find('option:not(:first)').remove();
            var seen = {};
            column.data().each(function(d) {
              var text = $('<div>').html(d).text().trim();
              if (text && !seen[text]) {
                seen[text] = true;
                select.append('<option value="' + text + '">' + text + '</option>');
              }
            });
            select.val(currentVal);
          }
        });
      }

      // Update month card counters from server response
      function updateMonthCardsFromServer(monthCounts) {
        var monthNames = ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
                          'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
        for (var i = 1; i <= 12; i++) {
          $('#count' + monthNames[i-1]).text(monthCounts[i] || 0);
        }
      }

      // Helper: resolve month dropdown value to actual month number
      function resolveMonthValue(val) {
        if (val === 'current') {
          return new Date().getMonth() + 1; // 1-12
        } else if (val === 'previous') {
          var m = new Date().getMonth(); // 0-11
          return m === 0 ? 12 : m; // Diciembre si estamos en Enero
        }
        return val ? parseInt(val) : null;
      }

      // Month dropdown filter
      $('#monthFilter').on('change', function() {
        var val = $(this).val();
        var monthNum = resolveMonthValue(val);

        // Sync cards
        $('.card-month').removeClass('active');
        if (monthNum) {
          $('.card-month[data-month="' + monthNum + '"]').addClass('active');
          activeMonth = monthNum;
        } else {
          activeMonth = null;
        }

        table.ajax.reload();
      });

      // Click en tarjetas de mes — triggers server-side filter
      $(document).on('click', '.card-month', function() {
        var month = $(this).data('month');

        if ($(this).hasClass('active')) {
          $(this).removeClass('active');
          activeMonth = null;
          $('#monthFilter').val(''); // Sync dropdown
        } else {
          $('.card-month').removeClass('active');
          $(this).addClass('active');
          activeMonth = month;
          $('#monthFilter').val(String(month)); // Sync dropdown
        }

        table.ajax.reload();
      });

      // Expandable row child details
      $('#reportTable tbody').on('click', 'td .details-control', function () {
        var tr = $(this).closest('tr');
        var row = table.row(tr);

        if (row.child.isShown()) {
          row.child.hide();
          $(this).removeClass('bi-dash-square').addClass('bi-plus-square');
        } else {
          row.child(format(row.data())).show();
          $(this).removeClass('bi-plus-square').addClass('bi-dash-square');
        }
      });

      // Select2 initialization
      $('#clientFilter').select2({
        placeholder: "Seleccione un cliente",
        allowClear: true,
        width: 'resolve'
      });
      $('#clientDownload').select2({
        placeholder: "-- Seleccione un cliente --",
        allowClear: true,
        width: '100%'
      });

      // Client filter — triggers server-side reload
      $('#clientFilter').on('change', function () {
        var selected = $(this).val();

        // Save to localStorage for Quick Access
        if (selected) {
          var clientId = reverseClientMap[selected];
          if (clientId) {
            localStorage.setItem('selectedClient', clientId);
          }
        } else {
          localStorage.removeItem('selectedClient');
        }

        table.ajax.reload();
      });

      // Preload client from localStorage (Quick Access)
      var storedClientId = localStorage.getItem('selectedClient');
      if (storedClientId) {
        var clientName = clientMap[storedClientId];
        if (clientName) {
          $('#clientFilter').val(clientName).trigger('change');
        }
      }

      // Escuchar cambios de cliente desde Quick Access Dashboard (otras pestañas)
      function _syncClientFromQA(newClientId) {
        console.log('[ReportList] Sync recibido, cliente:', newClientId);
        var name = clientMap[newClientId];
        if (name) {
          $('#clientFilter').val(name).trigger('change');
          console.log('[ReportList] Cliente cambiado a:', name);
        }
      }
      window.addEventListener('storage', function(e) {
        if (e.key === 'clientSyncTrigger' && e.newValue) {
          var clientId = e.newValue.split('|')[0];
          _syncClientFromQA(clientId);
        }
      });
      if (typeof BroadcastChannel !== 'undefined') {
        var _qaSyncCh = new BroadcastChannel('quick_access_sync');
        _qaSyncCh.onmessage = function(e) {
          if (e.data && e.data.type === 'clientChange') _syncClientFromQA(e.data.clientId);
        };
      }

      // Date range filters — trigger server-side reload
      $('#dateFrom, #dateTo').on('change', function() {
        table.ajax.reload();
      });

      // Reset all filters
      $('#clearState').on('click', function () {
        localStorage.removeItem('DataTables_reportTable');
        localStorage.removeItem('selectedClient');
        table.state.clear();
        activeMonth = null;
        $('.card-month').removeClass('active');
        $('#monthFilter').val('');
        $('#clientFilter').val('').trigger('change.select2');
        $('#dateFrom, #dateTo').val('');
        table.search('');
        table.columns().search('');
        table.ajax.reload();
      });

      // ============================================
      // Descarga de Documentación por Contrato
      // ============================================

      $('#clientDownload').on('change', function() {
        var clientId = $(this).val();
        if (clientId) {
          $('#btnVerDocumentacion, #btnDescargarZip').prop('disabled', false);
        } else {
          $('#btnVerDocumentacion, #btnDescargarZip').prop('disabled', true);
        }
      });

      $('#btnVerDocumentacion').on('click', function() {
        var clientId = $('#clientDownload').val();
        if (clientId) {
          window.open('<?= base_url("/contracts/seleccionar-documentacion/") ?>' + clientId, '_blank');
        }
      });

      $('#btnDescargarZip').on('click', function() {
        var clientId = $('#clientDownload').val();
        if (clientId) {
          window.open('<?= base_url("/contracts/seleccionar-documentacion/") ?>' + clientId, '_blank');
        }
      });
    });
  </script>

</body>

</html>
