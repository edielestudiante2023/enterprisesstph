<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Reportes</title>
    <!-- Enlaces de Bootstrap CSS y DataTables -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">
</head>

<style>
    /* Estilo para truncar el texto en la columna "Enlace" */
    td.enlace-col {
        max-width: 100px;
        /* Ancho máximo de la celda */
        white-space: nowrap;
        /* Evita que el texto haga saltos de línea */
        overflow: hidden;
        /* Oculta el texto que exceda el ancho */
        text-overflow: ellipsis;
        /* Muestra "..." al final si el texto es muy largo */
    }
</style>


<body class="bg-light">

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
                <a href="<?= base_url('/dashboardconsultant') ?>" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; font-size: 14px; margin-top: 5px;">Ir a DashBoard</a>
            </div>

            <!-- Botón derecho -->
            <div style="text-align: center;">
                <h2 style="margin: 0; font-size: 16px;">Añadir Registro</h2>
                <a href="<?= base_url('/addReport') ?>" style="display: inline-block; padding: 10px 20px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px; font-size: 14px; margin-top: 5px;" target="_blank">Añadir Registro</a>
            </div>
        </div>
    </nav>

    <!-- Ajustar el espaciado para evitar que el contenido se oculte bajo el navbar fijo -->
    <div style="height: 200px;"></div>


    <div class="container my-4">
        <h2 class="text-center mb-4">Lista de Reportes</h2>


        <!-- Tabla de Reportes -->
        <h3 class="mb-3">Reportes</h3>

        <?php if (session()->get('msg')): ?>
            <div class="alert alert-info">
                <?= session()->get('msg') ?>
            </div>
        <?php endif; ?>

        <?php if (isset($reports) && !empty($reports)) : ?>
            <table id="reportTable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título del Reporte</th>
                        <th>Tipo de Documento</th>
                        <th>Enlace</th>
                        <th>Estado</th>
                        <th>Observaciones</th>
                        <th>ID Cliente</th>
                        <th>Nombre del Cliente</th>
                        <th>Tipo de Reporte</th>
                        <th>Fecha de Creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reports as $report) : ?>
                        <tr>
                            <td><?= $report['id_reporte'] ?></td>
                            <td><?= $report['titulo_reporte'] ?></td>
                            <td><?= $report['Tipo_documento'] ?></td>
                            <td class="enlace-col"><a href="<?= $report['enlace'] ?>" target="_blank" title="<?= $report['enlace'] ?>"><?= $report['enlace'] ?></a></td>

                            <td><?= $report['estado'] ?></td>
                            <td><?= $report['observaciones'] ?></td>
                            <td><?= $report['id_cliente'] ?></td>
                            <td><?= $clients[array_search($report['id_cliente'], array_column($clients, 'id_cliente'))]['nombre_cliente'] ?></td>
                            <td><?= $reportTypes[array_search($report['id_report_type'], array_column($reportTypes, 'id_report_type'))]['report_type'] ?></td>
                            <td><?= $report['created_at'] ?></td>
                            <td>
                                <a href="<?= base_url('/editReport/' . $report['id_reporte']) ?>" class="btn btn-sm btn-warning">Editar</a>
                                <a href="<?= base_url('/deleteReport/' . $report['id_reporte']) ?>" class="btn btn-sm btn-danger">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p class="text-muted">No hay reportes disponibles.</p>
        <?php endif; ?>
    </div>


    <footer style="background-color: white; padding: 20px 0; border-top: 1px solid #B0BEC5; margin-top: 40px; color: #3A3F51; font-size: 14px; text-align: center;">
        <div style="max-width: 1200px; margin: 0 auto; display: flex; flex-direction: column; align-items: center;">
            <!-- Company and Rights -->
            <p style="margin: 0; font-weight: bold;">Cycloid Talent SAS</p>
            <p style="margin: 5px 0;">Todos los derechos reservados © 2024</p>
            <p style="margin: 5px 0;">NIT: 901.653.912</p>

            <!-- Website Link -->
            <p style="margin: 5px 0;">
                Sitio oficial: <a href="https://cycloidtalent.com/" target="_blank" style="color: #007BFF; text-decoration: none;">https://cycloidtalent.com/</a>
            </p>

            <!-- Social Media Links -->
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


    <!-- Scripts de jQuery, Bootstrap y DataTables -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script>
    $(document).ready(function() {
        // Inicializa DataTable con configuraciones personalizadas
        const table = $('#reportTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json"
            },
            "pageLength": 50, // Mostrar 50 filas por defecto
            "order": [[9, "desc"]], // Ordenar por Fecha de Creación (índice 9)
            "columnDefs": [
                {
                    "targets": 3, // Índice de la columna Enlace
                    "width": "10%", // Fijar el ancho a 10%
                    "className": "text-truncate" // Clase CSS para truncar el texto
                }
            ],
            "initComplete": function() {
                // Iterar sobre las columnas específicas para agregar filtros precargados
                this.api().columns([7, 8, 1, 4]).every(function() { // Índices: Nombre Cliente, Tipo Reporte, Título del Reporte, Estado
                    var column = this;

                    // Crear un contenedor para el nombre de la columna y el filtro
                    var container = $('<div class="d-flex flex-column"></div>');

                    // Agregar el nombre de la columna
                    container.append('<span>' + $(column.header()).text() + '</span>');

                    // Crear un select para los filtros
                    var select = $('<select class="form-select form-select-sm mt-1"><option value="">Todos</option></select>')
                        .appendTo(container)
                        .on('change', function() {
                            var val = $.fn.dataTable.util.escapeRegex($(this).val());
                            column.search(val ? '^' + val + '$' : '', true, false).draw();
                        });

                    // Precargar los datos únicos de la columna en el filtro
                    column.data().unique().sort().each(function(d, j) {
                        select.append('<option value="' + d + '">' + d + '</option>');
                    });

                    // Vaciar y reemplazar el encabezado con el contenedor
                    $(column.header()).empty().append(container);
                });
            }
        });
    });
</script>






</body>

</html>