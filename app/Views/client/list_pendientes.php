<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Pendientes</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css" rel="stylesheet">
    <!-- DataTables Bootstrap 5 Integration -->
    <link href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- DataTables Buttons CSS -->
    <link href="https://cdn.datatables.net/buttons/2.3.3/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <!-- DataTables Responsive CSS (Mejora de Responsividad) -->
    <link href="https://cdn.datatables.net/responsive/2.4.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
    <!-- Font Awesome para iconos (opcional) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            color: #343a40;
        }

        h2 {
            color: #495057;
            font-weight: 700;
        }

        .table-container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .alert {
            border-radius: 8px;
        }

        /* Ajuste de altura de filas */
        table.dataTable tbody tr {
            height: 40px; /* Altura ajustada a 40px */
        }

        /* Ajuste de ancho de columnas y prevención de desbordamiento */
        table.dataTable thead th,
        table.dataTable tfoot th {
            white-space: nowrap;
            text-align: center;
        }

        table.dataTable tbody td {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 200px; /* Ajustar según sea necesario */
            padding: 8px; /* Padding ajustado para mejor apariencia */
        }

        /* Estilo para los filtros desplegables en <tfoot> */
        table.dataTable tfoot select,
        table.dataTable tfoot input {
            width: 100%;
            height: 30px;
        }

        /* Estilo para los botones de DataTables */
        .dt-buttons .btn {
            margin-right: 5px;
        }

        /* Responsividad adicional */
        @media (max-width: 768px) {
            table.dataTable tbody td {
                max-width: 150px; /* Ajuste para pantallas más pequeñas */
            }
        }

        /* Centrar el contenido de las columnas de fecha */
        .fecha-col {
            text-align: center;
        }

        /* Estilo para la columna de control de expansión */
        td.dt-control {
            text-align: center;
            cursor: pointer;
            width: 15px;
        }

        /* Iconos para control de expansión */
        td.dt-control::before {
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            content: "\f067"; /* Icono de más */
            display: inline-block;
            margin-right: 5px;
        }

        tr.shown td.dt-control::before {
            content: "\f068"; /* Icono de menos */
        }

        /* Asegurar que la primera columna (control) no tenga filtros */
        tfoot th:first-child {
            display: none;
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center w-100">
                <!-- Logos -->
                <div class="d-flex align-items-center">
                    <a href="https://dashboard.cycloidtalent.com/login" class="me-3">
                        <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo" style="height: 60px;">
                    </a>
                    <a href="https://cycloidtalent.com/index.php/consultoria-sst" class="me-3">
                        <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST Logo" style="height: 60px;">
                    </a>
                    <a href="https://cycloidtalent.com/">
                        <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloids Logo" style="height: 60px;">
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Espaciado para el navbar fijo -->
    <div style="height: 100px;"></div>

    <div class="container-fluid my-5">
        <h2 class="text-center mb-4">Listado de Pendientes</h2>

        <?php if (session()->getFlashdata('msg')): ?>
            <div class="alert alert-success text-center"><?= session()->getFlashdata('msg') ?></div>
        <?php endif; ?>

        <div class="d-flex justify-content-end mb-3">
            <button id="clearState" class="btn btn-danger btn-sm">Restablecer Filtros</button>
        </div>

        <div class="table-responsive table-container">
            <table id="pendientesTable" class="table table-bordered table-striped w-100 responsive nowrap">
                <thead class="table-light">
                    <tr>
                        <th></th> <!-- Nueva Columna para Control de Expansión -->
                        <th>Cliente</th>
                        <th>Responsable</th>
                        <th>Tarea Actividad</th>
                        <th>Fecha de Asignación</th> <!-- Nueva Columna -->
                        <th>Fecha Cierre</th>
                        <th>Estado</th>
                        <th>Conteo Días</th>
                        <th>Estado Avance</th>
                        <th>Evidencia</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th></th> <!-- Filtro para la nueva columna de control (sin filtro) -->
                        <th>Cliente</th>
                        <th>Responsable</th>
                        <th>Tarea Actividad</th>
                        <th>Fecha de Asignación</th>
                        <th>Fecha Cierre</th>
                        <th>Estado</th>
                        <th>Conteo Días</th>
                        <th>Estado Avance</th>
                        <th>Evidencia</th>
                    </tr>
                </tfoot>
                <tbody>
                    <?php if (empty($pendientes)): ?>
                        <tr>
                            <td colspan="10" class="text-center">No hay pendientes registrados.</td> <!-- Actualizado colspan -->
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pendientes as $pendiente): ?>
                            <tr>
                                <td class="dt-control"></td> <!-- Celda para Control de Expansión -->
                                <td data-bs-toggle="tooltip" title="<?= esc($pendiente['nombre_cliente']); ?>"><?= esc($pendiente['nombre_cliente']); ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($pendiente['responsable']); ?>"><?= esc($pendiente['responsable']); ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($pendiente['tarea_actividad']); ?>"><?= esc($pendiente['tarea_actividad']); ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($pendiente['fecha_asignacion']); ?>" class="fecha-col">
                                    <?= date('Y-m-d', strtotime($pendiente['fecha_asignacion'])); ?>
                                </td>
                                <td data-bs-toggle="tooltip" title="<?= esc($pendiente['fecha_cierre']); ?>" class="fecha-col">
                                    <?= esc($pendiente['fecha_cierre']); ?>
                                </td>
                                <td data-bs-toggle="tooltip" title="<?= esc($pendiente['estado']); ?>"><?= esc($pendiente['estado']); ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($pendiente['conteo_dias']); ?>"><?= esc($pendiente['conteo_dias']); ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($pendiente['estado_avance']); ?>"><?= esc($pendiente['estado_avance']); ?></td>
                                <td data-bs-toggle="tooltip" title="<?= esc($pendiente['evidencia_para_cerrarla']); ?>"><?= esc($pendiente['evidencia_para_cerrarla']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white py-4 border-top">
        <div class="container text-center">
            <p class="fw-bold mb-1">Cycloid Talent SAS</p>
            <p class="mb-1">Todos los derechos reservados © 2024</p>
            <p class="mb-1">NIT: 901.653.912</p>
            <p class="mb-3">
                Sitio oficial: <a href="https://cycloidtalent.com/" target="_blank" class="text-primary text-decoration-none">https://cycloidtalent.com/</a>
            </p>
            <p class="mb-2"><strong>Nuestras Redes Sociales:</strong></p>
            <div class="d-flex justify-content-center gap-3">
                <a href="https://www.facebook.com/CycloidTalent" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733547.png" alt="Facebook" style="height: 24px; width: 24px;">
                </a>
                <a href="https://co.linkedin.com/company/cycloid-talent" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733561.png" alt="LinkedIn" style="height: 24px; width: 24px;">
                </a>
                <a href="https://www.instagram.com/cycloid_talent?igsh=Nmo4d2QwZDg5dHh0" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733558.png" alt="Instagram" style="height: 24px; width: 24px;">
                </a>
                <a href="https://www.tiktok.com/@cycloid_talent?_t=8qBSOu0o1ZN&_r=1" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/512/3046/3046126.png" alt="TikTok" style="height: 24px; width: 24px;">
                </a>
            </div>
        </div>
    </footer>

    <!-- jQuery 3.6.0 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 5 JS Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <!-- DataTables Bootstrap 5 Integration -->
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>
    <!-- DataTables Buttons JS -->
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.bootstrap5.min.js"></script>
    <!-- JSZip para exportar a Excel -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <!-- Buttons HTML5 export -->
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.html5.min.js"></script>
    <!-- Buttons ColVis -->
    <script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.colVis.min.js"></script>
    <!-- DataTables Responsive JS -->
    <script src="https://cdn.datatables.net/responsive/2.4.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.0/js/responsive.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function () {
            // Función para formatear la fila hija
            function format(d) {
                // 'd' es un arreglo que contiene los datos de la fila
                return `
                    <div class="child-content">
                        <strong>Detalles Adicionales:</strong><br>
                        <p><strong>Cliente:</strong> ${d[1]}</p>
                        <p><strong>Responsable:</strong> ${d[2]}</p>
                        <p><strong>Tarea Actividad:</strong> ${d[3]}</p>
                        <p><strong>Fecha de Asignación:</strong> ${d[4]}</p>
                        <p><strong>Fecha Cierre:</strong> ${d[5]}</p>
                        <p><strong>Estado:</strong> ${d[6]}</p>
                        <p><strong>Conteo Días:</strong> ${d[7]}</p>
                        <p><strong>Estado Avance:</strong> ${d[8]}</p>
                        <p><strong>Evidencia:</strong> ${d[9]}</p>
                    </div>
                `;
            }

            // Inicializar los tooltips de Bootstrap
            function initTooltips() {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }

            // Configurar los filtros en <tfoot>
            $('#pendientesTable tfoot th').each(function (index) {
                if (index === 0) {
                    // La primera columna es para el control de expansión, sin filtro
                    $(this).html('');
                } else {
                    var title = $(this).text();
                    if (title === 'Fecha de Asignación' || title === 'Fecha Cierre') {
                        // Usar un input de fecha para las columnas de fecha
                        $(this).html('<input type="date" class="form-control form-control-sm" placeholder="Filtrar ' + title + '" />');
                    } else {
                        $(this).html('<select class="form-select form-select-sm"><option value="">' + title + '</option></select>');
                    }
                }
            });

            var table = $('#pendientesTable').DataTable({
                responsive: {
                    details: {
                        type: 'column',
                        target: 0 // La primera columna es el control
                    }
                }, // Activar responsividad con detalles en la primera columna
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.1/i18n/es-ES.json"
                },
                dom: 'Bfltip',
                buttons: [
                    {
                        extend: 'colvis',
                        text: 'Columnas',
                        className: 'btn btn-secondary btn-sm'
                    },
                    {
                        extend: 'excelHtml5',
                        text: 'Descargar Excel',
                        className: 'btn btn-success btn-sm',
                        title: 'Listado de Pendientes',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                ],
                order: [[4, 'desc']], // Ordenar por Fecha de Asignación descendente (índice ajustado)
                stateSave: true, // Habilitar la persistencia del estado
                scrollY: '50vh',
                scrollX: true, // Habilitar el desplazamiento horizontal
                scrollCollapse: true,
                paging: true,
                lengthChange: true,
                searching: true,
                ordering: true,
                info: true,
                autoWidth: false,
                columnDefs: [
                    {
                        className: 'dt-control',
                        orderable: false,
                        data: null,
                        defaultContent: '',
                        targets: 0
                    },
                    { visible: false, targets: 1 }, // Ocultar la columna "Cliente"
                    { width: '15%', targets: 2 }, // Responsable
                    { width: '15%', targets: 3 }, // Tarea Actividad
                    { width: '10%', targets: 4, className: 'fecha-col' }, // Fecha de Asignación
                    { width: '10%', targets: 5, className: 'fecha-col' }, // Fecha Cierre
                    { width: '10%', targets: 6 }, // Estado
                    { width: '10%', targets: 7 }, // Conteo Días
                    { width: '10%', targets: 8 }, // Estado Avance
                    { width: '10%', targets: 9 }  // Evidencia
                ],
                initComplete: function () {
                    var api = this.api();

                    // Para cada columna, llenar el filtro con valores únicos o configurar el input de fecha
                    api.columns().every(function () {
                        var column = this;
                        var footerCell = $(column.footer());

                        if (column.index() === 0) {
                            // Primera columna (control), sin filtro
                            return;
                        }

                        if (footerCell.find('input').length) {
                            // Configurar el filtro para columnas de fecha
                            $('input', column.footer()).on('change', function () {
                                var val = $.fn.dataTable.util.escapeRegex($(this).val());

                                if (val) {
                                    column
                                        .search(val, true, false)
                                        .draw();
                                } else {
                                    column
                                        .search('', true, false)
                                        .draw();
                                }
                            });
                        } else {
                            var select = $('select', column.footer());

                            column.data().unique().sort().each(function (d, j) {
                                if (d !== null && d !== "") {
                                    select.append('<option value="' + d + '">' + d + '</option>');
                                }
                            });

                            // Aplicar el filtro al seleccionar una opción
                            select.on('change', function () {
                                var val = $.fn.dataTable.util.escapeRegex(
                                    $(this).val()
                                );

                                column
                                    .search(val ? '^' + val + '$' : '', true, false)
                                    .draw();
                            });
                        }
                    });

                    // Restaurar los filtros desde localStorage si existen
                    var state = table.state.loaded();
                    if (state) {
                        table.columns().every(function (index) {
                            if (index === 0) return; // Primera columna, sin filtro
                            var colSearch = state.columns[index].search.search;
                            if (colSearch) {
                                var footerCell = $('th', table.column(index).footer()).text();
                                if (footerCell === 'Fecha de Asignación' || footerCell === 'Fecha Cierre') {
                                    var input = $('input', table.column(index).footer());
                                    input.val(colSearch);
                                } else {
                                    var select = $('select', table.column(index).footer());
                                    var val = colSearch.replace('^', '').replace('$', '');
                                    select.val(val);
                                }
                            }
                        });
                    }

                    // Inicializar los tooltips después de que se complete la inicialización de DataTables
                    initTooltips();
                },
                drawCallback: function () {
                    // Re-inicializar los tooltips después de cada redibujado de la tabla
                    initTooltips();
                }
            });

            // Botón para restablecer filtros
            $('#clearState').on('click', function () {
                // Borrar estado guardado en localStorage
                table.state.clear();
                // Recargar la página para aplicar los cambios
                location.reload();
            });

            // Manejar el evento de clic en la columna de control para expandir/contraer filas
            $('#pendientesTable tbody').on('click', 'td.dt-control', function () {
                var tr = $(this).closest('tr');
                var row = table.row(tr);

                if (row.child.isShown()) {
                    // Esta fila ya está abierta - cerrar
                    row.child.hide();
                    tr.removeClass('shown');
                }
                else {
                    // Abrir esta fila
                    row.child(format(row.data())).show();
                    tr.addClass('shown');
                }
            });

            // Inicializar los tooltips después de cargar la tabla
            initTooltips();
        });
    </script>
</body>

</html>
