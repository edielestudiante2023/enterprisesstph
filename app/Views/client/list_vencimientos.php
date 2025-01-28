<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Vencimientos</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        /* Personalización de badges de estado */
        .estado {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .estado-sin-ejecutar {
            background-color: #6c757d;
            color: white;
        }
        .estado-ejecutado {
            background-color: #198754;
            color: white;
        }
        .estado-pendiente {
            background-color: #0d6efd;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container my-5">
        <h1 class="mb-4">Vencimientos de Mantenimiento para <?= esc($cliente) ?></h1>

        <?php if (!empty($vencimientos) && is_array($vencimientos)): ?>
            <div class="table-responsive">
                <table id="vencimientosTable" class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Mantenimiento</th>
                            <th>Fecha de Vencimiento</th>
                            <th>Estado</th>
                            <th>Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vencimientos as $vencimiento): ?>
                            <tr>
                                <td><?= esc($vencimiento['detalle_mantenimiento']) ?></td>
                                <td><?= esc($vencimiento['fecha_vencimiento']) ?></td>
                                <td>
                                    <?php
                                        // Determinar la clase del estado
                                        $estadoClase = 'estado-sin-ejecutar'; // Valor por defecto
                                        switch (strtolower($vencimiento['estado_actividad'])) {
                                            case 'ejecutado':
                                                $estadoClase = 'estado-ejecutado';
                                                break;
                                            case 'pendiente':
                                                $estadoClase = 'estado-pendiente';
                                                break;
                                            // Agrega más casos según sea necesario
                                        }
                                    ?>
                                    <span class="estado <?= $estadoClase ?>">
                                        <?= esc($vencimiento['estado_actividad']) ?>
                                    </span>
                                </td>
                                <td><?= esc($vencimiento['observaciones'] ?? 'N/A') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info" role="alert">
                En este momento no tienes vencimientos asignados.
            </div>
        <?php endif; ?>
    </div>

    <!-- jQuery (requerido por DataTables) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#vencimientosTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
                },
                "columnDefs": [
                    { "orderable": false, "targets": [3] } // Desactivar ordenamiento en Observaciones
                ],
                "pageLength": 10,
                "lengthMenu": [5, 10, 25, 50, 100]
            });
        });
    </script>
</body>
</html>
