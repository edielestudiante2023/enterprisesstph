<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lista de Matrices Interactivas Cliente</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- DataTables CSS -->
  <link href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <!-- Select2 -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
  <style>
    .table tfoot th {
      vertical-align: top !important;
      padding: 8px !important;
    }
    .table tfoot th select,
    .table tfoot th input {
      width: 100%;
      padding: 4px 8px;
      font-size: 0.9em;
      border: 1px solid #ddd;
      border-radius: 4px;
    }
    .table tfoot th select:focus,
    .table tfoot th input:focus {
      outline: none;
      border-color: #80bdff;
      box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }
    #datatable th {
      min-width: 120px;
      position: relative;
    }
    #datatable th:first-child {
      min-width: 60px;
      width: 60px;
    }
    #datatable th:last-child {
      min-width: 150px;
    }
    .container {
      max-width: 100% !important;
      padding: 0 30px;
    }
    .table {
      width: 100% !important;
    }
    .filter-active {
      background-color: #e8f0fe !important;
    }
  </style>
</head>
<body>
  <div class="container mt-5">
    <?php if (session()->getFlashdata('msg')): ?>
      <div class="alert alert-info alert-dismissible fade show" role="alert">
        <?= session()->getFlashdata('msg') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="text-primary">Lista de Matrices</h1>
      <div>
        <a href="<?= base_url('matrices/list?export=excel') ?>" class="btn btn-primary me-2">
          <i class="fas fa-file-excel me-1"></i> Exportar a Excel
        </a>
        <a href="<?= base_url('matrices/add') ?>" class="btn btn-success me-2">
          <i class="fas fa-plus me-1"></i> Agregar Enlace
        </a>
        <a href="<?= base_url('matrices/generar-todos') ?>" class="btn btn-warning me-2"
           onclick="return confirm('Esto generara matrices personalizadas para todos los clientes que no las tengan. ¿Continuar?')">
          <i class="fas fa-magic me-1"></i> Generar Todos
        </a>
      </div>
    </div>

    <!-- Generar para un cliente específico -->
    <div class="card mb-4">
      <div class="card-body">
        <h6 class="card-title mb-3"><i class="fas fa-cogs me-1"></i> Generar Matrices Personalizadas por Cliente</h6>
        <div class="row align-items-end">
          <div class="col-md-8">
            <label for="clienteGenerar" class="form-label">Seleccionar Cliente</label>
            <select id="clienteGenerar" class="form-select">
              <option value="">Buscar cliente...</option>
              <?php foreach ($clients as $client): ?>
                <option value="<?= $client['id_cliente'] ?>"><?= htmlspecialchars($client['nombre_cliente'], ENT_QUOTES, 'UTF-8') ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4">
            <button id="btnGenerarCliente" class="btn btn-dark w-100" disabled>
              <i class="fas fa-hard-hat me-1"></i> Generar Matrices EPP + Peligros
            </button>
          </div>
        </div>
        <small class="text-muted mt-1 d-block">Genera las matrices MT-SST-002 (EPP) y MT-SST-001 (Peligros) personalizadas con el logo, nombre y fecha del contrato del cliente.</small>
      </div>
    </div>

    <table id="datatable" class="table table-striped table-bordered">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Tipo</th>
          <th>Descripción</th>
          <th>Observaciones</th>
          <th>Enlace</th>
          <th>Cliente</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($matrices as $matriz): ?>
          <tr>
            <td><?= $matriz['id_matriz'] ?></td>
            <td><?= htmlspecialchars($matriz['tipo'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($matriz['descripcion'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($matriz['observaciones'], ENT_QUOTES, 'UTF-8') ?></td>
            <td>
              <?php
                $enlace = $matriz['enlace'];
                $isLocal = strpos($enlace, 'uploads/matrices/') !== false;
                $href = $isLocal ? base_url($enlace) : $enlace;
              ?>
              <?php if ($isLocal): ?>
                <a href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>"
                   download class="btn btn-sm btn-outline-success text-decoration-none">
                  <i class="fas fa-download me-1"></i>Descargar
                </a>
              <?php else: ?>
                <a href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>"
                   target="_blank" rel="noopener noreferrer"
                   class="btn btn-link text-decoration-none">Ver</a>
              <?php endif; ?>
            </td>
            <td>
              <?= $clients[array_search($matriz['id_cliente'], array_column($clients, 'id_cliente'))]['nombre_cliente'] ?>
            </td>
            <td>
              <a href="<?= base_url('matrices/edit/' . $matriz['id_matriz']) ?>" class="btn btn-warning btn-sm">Editar</a>
              <a href="<?= base_url('matrices/delete/' . $matriz['id_matriz']) ?>" 
                 class="btn btn-danger btn-sm" 
                 onclick="return confirm('¿Estás seguro de eliminar este registro?')">Eliminar</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <th>ID</th>
          <th>Tipo</th>
          <th>Descripción</th>
          <th>Observaciones</th>
          <th>Enlace</th>
          <th>Cliente</th>
          <th>Acciones</th>
        </tr>
      </tfoot>
    </table>
  </div>

  <!-- JavaScript Libraries -->
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script>
    $(document).ready(function() {
      // Función para decodificar entidades HTML
      function decodeHtmlEntities(text) {
        var textArea = document.createElement('textarea');
        textArea.innerHTML = text;
        return textArea.value;
      }

      var table = $('#datatable').DataTable({
        language: {
          url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"
        },
        responsive: true,
        lengthMenu: [5, 10, 25, 50],
        pageLength: 10,
        order: [[0, 'desc']], // Ordenar por ID de forma descendente
        initComplete: function () {
          var api = this.api();

          // Recorremos cada columna para asignar el filtro correspondiente
          api.columns().every(function () {
            var column = this;
            var colIdx = column.index();
            var footerCell = $(column.footer());
            footerCell.empty(); // Limpiar el contenido actual

            // Función para actualizar el estilo del filtro (si tiene texto o no)
            function updateFilterStyle(element) {
              if (column.search()) {
                footerCell.addClass('filter-active');
                element.addClass('filter-active');
              } else {
                footerCell.removeClass('filter-active');
                element.removeClass('filter-active');
              }
            }

            // Según el índice de columna asignamos el filtro:
            // Índices de columnas:
            // 0: ID (sin filtro)
            // 1: Tipo -> dropdown
            // 2: Descripción -> dropdown
            // 3: Observaciones -> input de texto
            // 4: Enlace (sin filtro)
            // 5: Cliente -> input de texto
            // 6: Acciones (sin filtro)
            if (colIdx === 1 || colIdx === 2) {
              // Dropdown filtering para "Tipo" y "Descripción"
              var headerText = $(column.header()).text();
              var select = $('<select class="form-select form-select-sm"><option value="">Filtrar ' + headerText + '</option></select>')
                .appendTo(footerCell)
                .on('click', function(e) {
                  e.stopPropagation();
                })
                .on('change', function () {
                  var val = $.fn.dataTable.util.escapeRegex($(this).val());
                  column.search(val ? val : '', true, false).draw();
                  updateFilterStyle($(this));
                });

              // Recopilar datos únicos de la columna (decodificando entidades HTML)
              var uniqueData = new Set();
              column.data().each(function(d) {
                uniqueData.add(decodeHtmlEntities(d));
              });
              Array.from(uniqueData).sort().forEach(function(d) {
                select.append('<option value="' + d + '">' + d + '</option>');
              });
            } else if (colIdx === 3 || colIdx === 5) {
              // Text input filtering para "Observaciones" y "Cliente"
              var headerText = $(column.header()).text();
              var input = $('<input type="text" class="form-control form-control-sm" placeholder="Filtrar ' + headerText + '">')
                .appendTo(footerCell)
                .on('click', function(e) {
                  e.stopPropagation();
                })
                .on('keyup change', function () {
                  if (column.search() !== this.value) {
                    column.search(this.value).draw();
                    updateFilterStyle($(this));
                  }
                });

              // Si hay un filtro inicial, aplicarlo
              if (column.search()) {
                input.val(column.search());
                updateFilterStyle(input);
              }
            }
            // Para las demás columnas (0, 4, 6) no se añade filtro (se deja vacío)
          });
        }
      });

      // Agregar tooltips a los encabezados de columna
      $('#datatable thead th').each(function() {
        $(this).attr('title', 'Ordenar por ' + $(this).text());
      });

      // Select2 para generar por cliente
      $('#clienteGenerar').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Buscar cliente...',
        allowClear: true
      });

      $('#clienteGenerar').on('change', function() {
        $('#btnGenerarCliente').prop('disabled', !$(this).val());
      });

      $('#btnGenerarCliente').on('click', function() {
        var clienteId = $('#clienteGenerar').val();
        if (!clienteId) return;
        if (confirm('Esto generara (o regenerara) las matrices personalizadas para este cliente. ¿Continuar?')) {
          window.location.href = '<?= base_url('matrices/generar') ?>/' + clienteId;
        }
      });
    });
  </script>
</body>
</html>
