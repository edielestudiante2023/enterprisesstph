<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard de Documentos - Enterprisesst</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/dataTables.bootstrap5.min.css" />
  <!-- DataTables Buttons CSS para Bootstrap 5 -->
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css" />
  <style>
    /* Asegura que html y body ocupen toda la pantalla */
    html, body {
      height: 100%;
      margin: 0;
      padding: 0;
      overflow: hidden;
    }
    
    body {
      background-color: #f9f9f9;
      color: #333;
    }

    /* Utilizamos container-fluid para que ocupe el 100% del ancho */
    .container-fluid {
      margin-top: 30px;
      width: 100%;
      height: calc(100vh - 140px); /* Ajuste para navbar y footer */
      display: flex;
      flex-direction: column;
    }

    /* Contenedor de la tabla que se expande y permite scroll interno */
    .table-container {
      background-color: #fff;
      border-radius: 8px;
      padding: 20px;
      margin-top: 20px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      flex: 1;
      overflow: auto;
      display: flex;
      flex-direction: column;
    }

    .table-container h2 {
      color: #333;
      font-weight: 600;
      font-size: 24px;
      margin-bottom: 15px;
    }

    /* La tabla ocupará todo el ancho del contenedor */
    table {
      width: 100%;
    }

    .table th {
      background-color: #0066cc;
      color: #fff;
      text-align: center;
      font-size: 16px;
    }

    .table td {
      font-size: 15px;
      vertical-align: middle;
      text-align: center;
    }

    .table td a {
      color: #0066cc;
      text-decoration: underline;
      font-weight: 500;
    }

    .table td a:hover {
      color: #004c99;
    }

    .empty-message {
      color: #333;
      font-size: 18px;
      font-weight: bold;
      text-align: center;
      padding: 20px;
    }

    /* Estilos adicionales para los filtros en el pie de la tabla */
    tfoot {
      background-color: #f1f1f1;
    }

    tfoot th {
      padding: 8px 10px;
    }

    .filter-select {
      width: 100%;
      padding: 4px;
      border-radius: 4px;
      border: 1px solid #ccc;
    }

    /* Espaciado para los botones de filtros */
    .button-group {
      margin-bottom: 10px;
    }
  </style>
</head>

<body>
  <!-- Navbar -->
  <nav style="background-color: white; position: fixed; top: 0; width: 100%; z-index: 1000; padding: 10px 0; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; max-width: 1200px; margin: 0 auto;">
      <!-- Logos -->
      <div>
        <a href="https://dashboard.cycloidtalent.com/login">
          <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo" style="height: 100px;" />
        </a>
      </div>
      <div>
        <a href="https://cycloidtalent.com/index.php/consultoria-sst">
          <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST Logo" style="height: 100px;" />
        </a>
      </div>
      <div>
        <a href="https://cycloidtalent.com/">
          <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloids Logo" style="height: 100px;" />
        </a>
      </div>
    </div>
  </nav>

  <!-- Espacio para el Navbar Fijo -->
  <div style="height: 120px;"></div>

  <div class="container-fluid">
    <!-- Tabla de Reportes -->
    <div class="table-container">
      <h2>Reportes</h2>
      <div class="button-group">
        <!-- Botón para Restablecer Filtros del stateSave -->
        <button id="clearState" class="btn btn-danger btn-sm">Restablecer Filtros</button>
        <!-- Botón para limpiar los filtros de los tfoot -->
        <button id="clearFilters" class="btn btn-secondary btn-sm">Soltar Filtros</button>
      </div>

      <?php if (!empty($reports)) : ?>
      <table id="reportsTable" class="table table-striped table-hover">
        <thead>
          <tr>
            <th>Título</th>
            <th>Enlace</th>
            <th>Estado</th>
            <th>Observaciones</th>
            <th>Detalle Reporte</th>
            <th>Tipo Reporte</th>
            <th>Fecha Creación</th>
          </tr>
        </thead>
        <tfoot>
          <tr>
            <th>Título</th>
            <th>Enlace</th>
            <th>Estado</th>
            <th>Observaciones</th>
            <th>Detalle Reporte</th>
            <th>Tipo Reporte</th>
            <th>Fecha Creación</th>
          </tr>
        </tfoot>
        <tbody>
          <?php foreach ($reports as $report): ?>
          <tr>
            <td><?= esc($report['titulo_reporte']) ?></td>
            <td><a href="<?= esc($report['enlace']) ?>" target="_blank" data-bs-toggle="tooltip" data-bs-placement="top" title="Abrir enlace">Ver</a></td>
            <td><?= esc($report['estado']) ?></td>
            <td><?= esc($report['observaciones']) ?></td>
            <td><?= esc($report['detalle_reporte']) ?></td>
            <td><?= esc($report['tipo_reporte']) ?></td>
            <td data-order="<?= esc($report['created_at']) ?>"><?= esc($report['created_at']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php else : ?>
      <p class="empty-message">No hay reportes disponibles.</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- Footer -->
  <footer style="background-color: white; padding: 20px 0; border-top: 1px solid #B0BEC5; margin-top: 40px; color: #3A3F51; font-size: 14px; text-align: center;">
    <p style="margin: 0;">Cycloid Talent SAS - Todos los derechos reservados © 2024</p>
  </footer>

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- DataTables JS -->
  <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
  <!-- DataTables Bootstrap 5 JS -->
  <script src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap5.min.js"></script>
  <!-- DataTables Buttons JS -->
  <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.bootstrap5.min.js"></script>
  <!-- JSZip para exportación a Excel -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
  <!-- Botón HTML5 para exportar -->
  <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    $(document).ready(function () {
      // Inicializar DataTable con botones, stateSave, filtros por columna y opciones de registros
      var table = $("#reportsTable").DataTable({
        dom: 'lBfrtip', // Se incluye "l" para mostrar el selector de cantidad de registros
        buttons: [
          {
            extend: 'excelHtml5',
            text: 'Exportar a Excel',
            titleAttr: 'Exportar tabla a Excel'
          }
        ],
        order: [
          [6, 'desc'],  // Por "Fecha Creación" descendente
          [4, 'asc'],   // Por "Detalle Reporte"
          [0, 'asc'],   // Por "Título"
          [2, 'asc']    // Por "Estado"
        ],
        paging: true,
        searching: true,
        lengthChange: true,
        pageLength: 10, // Registros por defecto
        lengthMenu: [[5, 10, 20, 50, 100], [5, 10, 20, 50, 100]], // Opciones de registros
        stateSave: true,
        language: {
          url: "//cdn.datatables.net/plug-ins/1.11.3/i18n/es_es.json"
        },
        initComplete: function () {
          this.api().columns().every(function () {
            var column = this;
            var select = $('<select class="filter-select"><option value="">Todos</option></select>')
              .appendTo($(column.footer()).empty())
              .on("change", function () {
                var val = $.fn.dataTable.util.escapeRegex($(this).val());
                column.search(val ? "^" + val + "$" : "", true, false).draw();
              });

            column.data().unique().sort().each(function (d, j) {
              if (d) {
                select.append('<option value="' + d + '">' + d + "</option>");
              }
            });
          });
        }
      });

      // Botón para restablecer el stateSave
      $("#clearState").on("click", function () {
        localStorage.removeItem("DataTables_reportsTable_/");
        table.state.clear();
        location.reload();
      });

      // Botón para limpiar los filtros del tfoot
      $("#clearFilters").on("click", function(){
        $('#reportsTable tfoot select').val('').trigger('change');
      });

      var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });
    });
  </script>
</body>

</html>
