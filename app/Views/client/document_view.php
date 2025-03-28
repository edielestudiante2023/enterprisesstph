<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Documentos por Subtema - Enterprisesst</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <!-- DataTables CSS -->
  <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
  <!-- DataTables Buttons CSS -->
  <link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css" rel="stylesheet" />
  <!-- SweetAlert2 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.min.css" rel="stylesheet" />
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />

  <style>
    html {
      scroll-behavior: smooth;
    }

    body {
      background-color: #f9f9f9;
      color: #333;
    }

    .container-fluid {
      margin-top: 30px;
    }

    .table-container {
      background-color: #fff;
      border-radius: 8px;
      padding: 20px;
      margin-top: 20px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      /* Evita que el contenido quede oculto detrás del navbar fijo */
      scroll-margin-top: 130px;
    }

    .table-container h2 {
      color: #333;
      font-weight: 600;
      font-size: 24px;
      margin-bottom: 15px;
    }

    .dataTables_wrapper .dataTables_filter input {
      margin-left: 0.5em;
      display: inline-block;
      width: auto;
    }

    .empty-message {
      color: #333;
      font-size: 18px;
      text-align: center;
      padding: 20px;
    }

    .observaciones-cell {
      max-width: 40ch;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    @media (max-width: 768px) {
      .table-container {
        padding: 10px;
      }

      .table-container h2 {
        font-size: 20px;
      }

      .table th,
      .table td {
        font-size: 14px;
      }
    }
  </style>
</head>

<body>
  <nav style="background-color: white; position: fixed; top: 0; width: 100%; z-index: 1100; padding: 10px 0; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; max-width: 1200px; margin: 0 auto;">
      <div>
        <a href="https://dashboard.cycloidtalent.com/login">
          <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo" style="height: 100px;" />
        </a>
      </div>
      <div style="display: flex; align-items: center; gap: 15px;">
        <a href="https://cycloidtalent.com/index.php/consultoria-sst">
          <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST Logo" style="height: 100px;" />
        </a>
        <a href="https://cycloidtalent.com/">
          <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloids Logo" style="height: 100px;" />
        </a>
        <!-- Dropdown para navegar entre secciones -->
        <?php
        // Definir secciones y sus títulos
        $sections = [
          'inspecciones'       => 'Inspecciones en la Copropiedad',
          'reportes'           => 'Reportes',
          'aseo'               => 'Aseo',
          'vigilancia'         => 'Vigilancia',
          'ambiental'          => 'Ambiental',
          'actasdevisita'      => 'Actas de Visita',
          'capacitaciones'     => 'Capacitaciones',
          'cincuentahoras'     => 'Cincuenta Horas',
          'reporteministerio'  => 'Reporte Ministerio',
          'cierredemes'        => 'Cierre de Mes',
          'emergencias'        => 'Emergencias',
          'otrosproveedores'   => 'Otros Proveedores',
          'secretariasalud'    => 'Secretaría de Salud',
          'lavadotanques'      => 'Lavado de Tanques',
          'localescomerciales' => 'Locales Comerciales',
          'fumigaciones'       => 'Fumigaciones',
          'normatividad'       => 'Normatividad y Cierre de Numerales',
          'contrato'           => 'Contrato',
          'saneamiento'        => 'Saneamiento Básico',
          'consultor'          => 'Documentos Consultor'
        ];

        // Crear una copia del arreglo y ordenarlo alfabéticamente por el título
        $sorted_sections = $sections;
        asort($sorted_sections);
        ?>
        <div class="dropdown">
          <button class="btn btn-primary dropdown-toggle" type="button" id="sectionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            Ir a sección
          </button>
          <ul class="dropdown-menu" aria-labelledby="sectionsDropdown">
            <?php foreach ($sorted_sections as $key => $title) : ?>
              <li>
                <a class="dropdown-item" href="#<?= esc($key) ?>"><?= esc($title) ?></a>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    </div>
  </nav>

  <!-- Espacio para el Navbar Fijo -->
  <div style="height: 120px;"></div>

  <div class="container-fluid">
    <?php foreach ($sections as $key => $title) : ?>
      <!-- El contenedor utiliza el id para el anclaje -->
      <div class="table-container" id="<?= esc($key) ?>">
        <h2><?= esc($title) ?></h2>
        <?php if (!empty($$key)) : ?>
          <div class="table-responsive">
            <!-- La tabla recibe un id distinto (prefijado con "table-") -->
            <table id="table-<?= esc($key) ?>" class="table table-hover table-striped dt-responsive nowrap" style="width:100%">
              <thead>
                <tr>
                  <th>Título</th>
                  <th>Enlace</th>
                  <th>Estado</th>
                  <th>Tipo de Reporte</th>
                  <th>Detalle</th>
                  <th>Observaciones</th>
                  <th>Creado el</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($$key as $reporte) : ?>
                  <tr>
                    <td><?= esc($reporte['titulo_reporte']) ?></td>
                    <td>
                      <a href="<?= esc($reporte['enlace']) ?>" target="_blank" class="text-primary">
                        <i class="fas fa-file-alt me-1"></i> Ver
                      </a>
                    </td>
                    <td><?= esc($reporte['estado']) ?></td>
                    <td><?= esc($reporte['tipo_reporte']) ?></td>
                    <td><?= esc($reporte['detalle_reporte']) ?></td>
                    <td data-bs-toggle="tooltip" data-bs-placement="top" title="<?= esc($reporte['observaciones']) ?>">
                      <div class="observaciones-cell">
                        <?= (strlen($reporte['observaciones']) > 40)
                          ? esc(substr($reporte['observaciones'], 0, 40)) . '...'
                          : esc($reporte['observaciones']) ?>
                      </div>
                    </td>
                    <td><?= esc($reporte['created_at']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else : ?>
          <p class="empty-message">Aún no hay reportes de <?= esc($title) ?> disponibles.</p>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Bootstrap Bundle JS (Incluye Popper) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- DataTables JS -->
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <!-- DataTables Buttons JS -->
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js"></script>
  <!-- SweetAlert2 JS -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js"></script>

  <script>
    $(document).ready(function () {
      // Inicializar tooltips de Bootstrap
      var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });

      // Lista de IDs de las tablas (usando el prefijo "table-")
      var tableIds = [
        'table-inspecciones', 'table-reportes', 'table-aseo', 'table-vigilancia', 'table-ambiental', 'table-actasdevisita',
        'table-capacitaciones', 'table-cincuentahoras', 'table-reporteministerio', 'table-cierredemes',
        'table-emergencias', 'table-otrosproveedores', 'table-secretariasalud', 'table-lavadotanques',
        'table-localescomerciales', 'table-fumigaciones', 'table-normatividad', 'table-contrato', 'table-saneamiento', 'table-consultor'
      ];

      // Inicializar DataTables para cada tabla
      tableIds.forEach(function (id) {
        if ($('#' + id).length) {
          $('#' + id).DataTable({
            responsive: true,
            dom: 'Bfrtip',
            buttons: ['copy', 'csv', 'excel', 'pdf', 'print', 'colvis'],
            // Ordenar por la columna "Creado el" (índice 6) de forma descendente (Z-A)
            order: [
              [6, 'desc']
            ],
            paging: true,
            searching: true,
            lengthChange: true,
            pageLength: 5,
            language: {
              url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
            }
          });
        }
      });

      // Manejar el clic en el botón de eliminar (si existe)
      $('.delete-btn').on('click', function () {
        var reporteId = $(this).data('id');
        var row = $(this).closest('tr');

        Swal.fire({
          title: '¿Estás seguro?',
          text: "¡No podrás revertir esto!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonColor: '#6c757d',
          confirmButtonText: 'Sí, eliminar',
          cancelButtonText: 'Cancelar'
        }).then((result) => {
          if (result.isConfirmed) {
            var table = $(this).closest('table').DataTable();
            table.row(row).remove().draw();

            Swal.fire(
              'Eliminado!',
              'El registro ha sido eliminado.',
              'success'
            );
          }
        });
      });
    });
  </script>
</body>

</html>
