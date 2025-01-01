<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plan de Trabajo Anual</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <style>
        body {
            background-color: #f9f9f9;
            font-family: Arial, sans-serif;
        }

        h2 {
            color: #343a40;
            font-weight: 600;
        }

        .container {
            background-color: #ffffff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            /* Ajusta el ancho al 100% */
            max-width: 100%;
            /* Asegúrate de que no haya un límite máximo */
        }


        .btn-dashboard {
            margin-bottom: 1.5rem;
        }

        /* Estilo para el tooltip */
        .tooltip-inner {
            max-width: 300px;
            white-space: normal;
            font-size: 1.25rem;
            /* Aumenta el tamaño de la fuente */
            padding: 10px 15px;
            /* Aumenta el padding para más espacio */
            background-color: #333;
            /* Cambia el color de fondo si es necesario */
            color: #fff;
            /* Cambia el color del texto */
            border-radius: 5px;
            /* Opcional: redondea las esquinas */
        }

        .tooltip {
            font-size: 1.25rem;
            /* Asegúrate de que el contenedor también refleje el tamaño */
        }


        tbody tr {
            height: 45px;
        }

        .actividad-column {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .table-wrapper {
            max-height: 50vh;
            /* Altura máxima del contenedor */
            overflow-y: auto;
            /* Habilita el scroll vertical */
        }

        thead th {
            position: sticky;
            top: 0;
            background-color: #343a40;
            /* Fondo del encabezado */
            color: white;
            z-index: 1;
        }
    </style>
</head>

<body>

    <nav style="background-color: white; position: fixed; top: 0; width: 100%; z-index: 1000; padding: 10px 0; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; max-width: 1200px; margin: 0 auto;">
            <div>
                <a href="https://dashboard.cycloidtalent.com/login">
                    <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo" style="height: 100px;">
                </a>
            </div>
            <div>
                <a href="https://cycloidtalent.com/index.php/consultoria-sst">
                    <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST Logo" style="height: 100px;">
                </a>
            </div>
            <div>
                <a href="https://cycloidtalent.com/">
                    <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloids Logo" style="height: 100px;">
                </a>
            </div>
        </div>
    </nav>

    <div style="height: 120px;"></div>

    <div class="container mt-5">
        <h2 class="text-center mb-4">Plan de Trabajo Anual</h2>

        <?php if (session()->getFlashdata('msg')): ?>
            <div class="alert alert-success text-center"><?= session()->getFlashdata('msg') ?></div>
        <?php endif; ?>

        <div class="table-responsive">
            <!-- Botón para restablecer filtros -->
            <button id="clearState" class="btn btn-danger btn-sm mb-3">Restablecer Filtros</button>
            <!-- Botón para descargar Excel -->
            <button id="downloadExcel" class="btn btn-primary btn-sm mb-3">Descargar Excel</button>

            <table id="planesTable" class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>

                        <th>PHVA</th>

                        <th>Actividad</th>
                        <th>Fecha Propuesta</th>
                        <th>Fecha Cierre</th>
                        <th>Responsable Definido</th>
                        <th>Estado de Actividad</th>
                        <th>Porcentaje de Avance</th>
                        <!-- <th>Semana</th> -->
                        <th>Observaciones</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>

                        <th>PHVA</th>

                        <th>Actividad</th>
                        <th>Fecha Propuesta</th>
                        <th>Fecha Cierre</th>
                        <th>Responsable Definido</th>
                        <th>Estado de Actividad</th>
                        <th>Porcentaje de Avance</th>
                        <!-- <th>Semana</th> -->
                        <th>Observaciones</th>
                    </tr>
                </tfoot>
                <tbody>
                    <?php foreach ($planes as $plan): ?>
                        <tr>

                            <td data-bs-toggle="tooltip" title="<?= esc($plan['phva_plandetrabajo']) ?>"><?= esc($plan['phva_plandetrabajo']) ?></td>

                            <td class="actividad-column" data-bs-toggle="tooltip" title="<?= esc($plan['nombre_actividad']) ?>">
                                <?= strlen(esc($plan['nombre_actividad'])) > 40 ? substr(esc($plan['nombre_actividad']), 0, 40) . '...' : esc($plan['nombre_actividad']) ?>
                            </td>
                            <td style="font-size: smaller; width: 11ch;" data-bs-toggle="tooltip" title="<?= esc($plan['fecha_propuesta']) ?>"><?= esc($plan['fecha_propuesta']) ?></td>
                            <td style="font-size: smaller; width: 11ch;" data-bs-toggle="tooltip" title="<?= esc($plan['fecha_cierre']) ?>"><?= esc($plan['fecha_cierre']) ?></td>

                            <td data-bs-toggle="tooltip" title="<?= esc($plan['responsable_definido_paralaactividad']) ?>"><?= esc($plan['responsable_definido_paralaactividad']) ?></td>
                            <td data-bs-toggle="tooltip" title="<?= esc($plan['estado_actividad']) ?>"><?= esc($plan['estado_actividad']) ?></td>
                            <td data-bs-toggle="tooltip" title="<?= esc($plan['porcentaje_avance'] * 100) ?>%"><?= esc($plan['porcentaje_avance'] * 100) ?>%</td>
                            <!-- <td data-bs-toggle="tooltip" title="<?= esc($plan['semana']) ?>"><?= esc($plan['semana']) ?></td> -->
                            <td style="font-size: smaller;" data-bs-toggle="tooltip" title="<?= esc($plan['observaciones']) ?>"><?= esc($plan['observaciones']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script>
        $(document).ready(function() {
            var table = $('#planesTable').DataTable({
                stateSave: true,
                initComplete: function() {
                    this.api().columns().every(function() {
                        var column = this;
                        var select = $('<select class="form-control form-control-sm"><option value="">Todos</option></select>')
                            .appendTo($(column.footer()).empty())
                            .on('change', function() {
                                var val = $.fn.dataTable.util.escapeRegex($(this).val());
                                column.search(val ? '^' + val + '$' : '', true, false).draw();
                            });
                        column.data().unique().sort().each(function(d) {
                            select.append('<option value="' + d + '">' + d + '</option>');
                        });
                    });
                }
            });

            $('#clearState').on('click', function() {
                localStorage.removeItem('DataTables_planesTable_/');
                table.state.clear();
                location.reload();
            });

            $('#downloadExcel').on('click', function() {
                var wb = XLSX.utils.table_to_book(document.getElementById('planesTable'), {
                    sheet: "Plan de Trabajo"
                });
                XLSX.writeFile(wb, 'Plan_de_Trabajo.xlsx');
            });

            // Inicializar tooltips de Bootstrap en todas las celdas con el atributo data-bs-toggle="tooltip"
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);

            });

        });
    </script>
</body>

</html>