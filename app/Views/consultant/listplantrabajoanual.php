<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Actividades - Plan de Trabajo Anual</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f9f9f9;
            font-family: Arial, sans-serif;
        }

        h2 {
            margin: 20px 0;
            text-align: center;
            color: #333;
        }

        .btn {
            margin-bottom: 20px;
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
            max-width: 80ch;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            height: 50px;
        }

        td[title],
        th[title] {
            cursor: help;
        }

        .tooltip-inner {
            max-width: 300px;
            white-space: normal;
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <!-- Navbar -->
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
            <a href="<?= base_url('/addPlanDeTrabajoAnual') ?>" style="display: inline-block; padding: 10px 20px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px; font-size: 14px; margin-top: 5px;">Añadir Registro</a>
        </div>
    </div>
</nav>

<!-- Ajustar el espaciado para evitar que el contenido se oculte bajo el navbar fijo -->
<div style="height: 160px;"></div>


    <div class="container mt-5">


        <h2 class="text-center mb-4">Lista de Actividades del Plan de Trabajo Anual</h2>

        <div class="table-responsive">
            <table id="actividadesTable" class="table table-striped table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>ID Plan de Trabajo</th>
                        <th>PHVA</th>
                        <th>Numeral</th>
                        <th>Actividad</th>
                        <th>Responsable Sugerido</th>
                        <th>Fecha Propuesta</th>
                        <th>Fecha Cierre</th>
                        <th>Responsable Definido</th>
                        <th>Estado Actividad</th>
                        <th>Porcentaje Avance</th>
                        <th>Semana</th>
                        <th>Observaciones</th>
                        <th>Creado en</th>
                        <th>Actualizado en</th>
                        <th>Acciones</th>
                    </tr>
                    <tr class="filters">
                        <th></th>
                        <th><select class="form-control form-control-sm filter-select"></select></th>
                        <th></th>
                        <th><select class="form-control form-control-sm filter-select"></select></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th><select class="form-control form-control-sm filter-select"></select></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($actividades)) : ?>
                        <?php foreach ($actividades as $actividad) : ?>
                            <tr>
                                <td><?= esc($actividad['id_ptacliente']) ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($actividad['nombre_cliente']) ?>"><?= esc($actividad['nombre_cliente']) ?></td>
                                <td><?= esc($actividad['id_plandetrabajo']) ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($actividad['phva_plandetrabajo']) ?>"><?= esc($actividad['phva_plandetrabajo']) ?></td>
                                <td><?= esc($actividad['numeral_plandetrabajo']) ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($actividad['actividad_plandetrabajo']) ?>"><?= esc($actividad['actividad_plandetrabajo']) ?></td>
                                <td><?= esc($actividad['responsable_sugerido_plandetrabajo']) ?></td>
                                <td><?= esc($actividad['fecha_propuesta']) ?></td>
                                <td><?= esc($actividad['fecha_cierre']) ?></td>
                                <td><?= esc($actividad['responsable_definido_paralaactividad']) ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($actividad['estado_actividad']) ?>"><?= esc($actividad['estado_actividad']) ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($actividad['porcentaje_avance']) ?>%"><?= esc($actividad['porcentaje_avance']) ?>%</td>
                                <td><?= esc($actividad['semana']) ?></td>
                                <td><?= esc($actividad['observaciones']) ?></td>
                                <td><?= esc($actividad['created_at']) ?></td>
                                <td><?= esc($actividad['updated_at']) ?></td>
                                <td>
                                    <a href="<?= base_url('editPlanDeTrabajoAnual/' . $actividad['id_ptacliente']) ?>" class="btn btn-warning btn-sm">Editar</a>
                                    <a href="<?= base_url('deletePlanDeTrabajoAnual/' . $actividad['id_ptacliente']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar esta actividad?')">Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="17" class="text-center">No se encontraron actividades.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
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


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            var table = $('#actividadesTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.1/i18n/es-ES.json"
                },
                "pagingType": "full_numbers",
                "responsive": true,
                "autoWidth": false,
                "initComplete": function() {
                    this.api().columns([1, 3, 10]).every(function() {
                        var column = this;
                        var select = $(column.header()).find('select');
                        column.data().unique().sort().each(function(d, j) {
                            select.append('<option value="' + d + '">' + d + '</option>');
                        });
                    });
                }
            });

            // Apply the search filters
            $('.filter-select').on('change', function() {
                var column = $(this).parent().index();
                table.column(column).search($(this).val()).draw();
            });

            // Inicializar tooltips de Bootstrap
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</body>

</html>