<?php
// Ordenar el array $vencimientos de forma descendente por id
usort($vencimientos, function($a, $b) {
    return $b['id'] - $a['id'];
});
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Listado de Vencimientos de Mantenimiento</title>
    <!-- Agrega tus estilos y scripts aquí (por ejemplo, Bootstrap) -->
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        tfoot select,
        tfoot input {
            width: 100%;
            padding: 6px;
            box-sizing: border-box;
        }

        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid transparent;
            border-radius: 4px;
        }

        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        .btn {
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 4px;
            margin-right: 5px;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn-success {
            background-color: #28a745;
            color: white;
        }

        .btn-warning {
            background-color: #ffc107;
            color: black;
        }
    </style>
</head>

<body>
    <h1>Listado de Vencimientos de Mantenimiento</h1>

    <!-- Mensajes de éxito -->
    <?php if (session()->getFlashdata('msg')): ?>
        <div class="alert alert-success">
            <?= session()->getFlashdata('msg') ?>
        </div>
    <?php endif; ?>

    <!-- Botones de acciones -->
    <div style="margin-bottom: 10px;">
        <a href="<?= site_url('vencimientos/add') ?>" class="btn btn-success">Agregar Nuevo Vencimiento</a>
        <a href="<?= base_url('vencimientos/send-emails') ?>" class="btn btn-warning">Enviar Recordatorios por Correo</a>
    </div>
    <!-- Botón para enviar emails a los registros seleccionados -->
    <div style="margin-top: 10px;">
        <button type="submit" class="btn btn-warning">Enviar Emails a Seleccionados</button>
    </div>
    <!-- Formulario para enviar emails a registros seleccionados -->
    <form id="sendSelectedForm" method="post" action="<?= site_url('vencimientos/send-selected-emails') ?>">
        <table id="vencimientosTable">
            <thead>
                <tr>
                    <!-- Columna para checkboxes con opción "Select All" -->
                    <th>
                        <input type="checkbox" id="selectAll" />
                    </th>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Consultor</th>
                    <th>Mantenimiento</th>
                    <th>Fecha de Vencimiento</th>
                    <th>Estado</th>
                    <th>Fecha de Realización</th>
                    <th>Observaciones</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <!-- Sin filtro para la columna de selección -->
                    <th></th>
                    <!-- Filtro para ID -->
                    <th>
                        <select id="filter_id">
                            <option value="">Todos</option>
                            <?php
                            $ids = array_unique(array_column($vencimientos, 'id'));
                            foreach ($ids as $id) {
                                echo '<option value="' . esc($id) . '">' . esc($id) . '</option>';
                            }
                            ?>
                        </select>
                    </th>
                    <!-- Filtro para Cliente (Text Input para búsqueda) -->
                    <th>
                        <input type="text" id="filter_cliente" placeholder="Buscar Cliente" />
                    </th>
                    <!-- Filtro para Consultor -->
                    <th>
                        <select id="filter_consultor">
                            <option value="">Todos</option>
                            <?php
                            $consultores = array_unique(array_column($vencimientos, 'consultor'));
                            foreach ($consultores as $consultor) {
                                echo '<option value="' . esc($consultor) . '">' . esc($consultor) . '</option>';
                            }
                            ?>
                        </select>
                    </th>
                    <!-- Filtro para Mantenimiento -->
                    <th>
                        <select id="filter_mantenimiento">
                            <option value="">Todos</option>
                            <?php
                            $mantenimientos = array_unique(array_column($vencimientos, 'mantenimiento'));
                            foreach ($mantenimientos as $mantenimiento) {
                                echo '<option value="' . esc($mantenimiento) . '">' . esc($mantenimiento) . '</option>';
                            }
                            ?>
                        </select>
                    </th>
                    <!-- Filtro para Fecha de Vencimiento -->
                    <th>
                        <input type="date" id="filter_fecha_vencimiento" placeholder="Buscar Fecha Venc." />
                    </th>
                    <!-- Filtro para Estado -->
                    <th>
                        <select id="filter_estado">
                            <option value="">Todos</option>
                            <?php
                            $estados = array_unique(array_column($vencimientos, 'estado_actividad'));
                            foreach ($estados as $estado) {
                                echo '<option value="' . esc($estado) . '">' . esc($estado) . '</option>';
                            }
                            ?>
                        </select>
                    </th>
                    <!-- Filtro para Fecha de Realización -->
                    <th>
                        <input type="date" id="filter_fecha_realizacion" placeholder="Buscar Fecha Real." />
                    </th>
                    <!-- Filtro para Observaciones -->
                    <th>
                        <select id="filter_observaciones">
                            <option value="">Todos</option>
                            <?php
                            $observaciones = array_unique(array_column($vencimientos, 'observaciones'));
                            foreach ($observaciones as $observacion) {
                                echo '<option value="' . esc($observacion) . '">' . esc($observacion) . '</option>';
                            }
                            ?>
                        </select>
                    </th>
                    <!-- Sin filtro para Acciones -->
                    <th></th>
                </tr>
            </tfoot>
            <tbody>
                <?php if (!empty($vencimientos) && is_array($vencimientos)): ?>
                    <?php foreach ($vencimientos as $vencimiento): ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="email-checkbox" name="selected[]" value="<?= esc($vencimiento['id']) ?>" />
                            </td>
                            <td><?= esc($vencimiento['id']) ?></td>
                            <td><?= esc($vencimiento['cliente']) ?></td>
                            <td><?= esc($vencimiento['consultor']) ?></td>
                            <td><?= esc($vencimiento['mantenimiento']) ?></td>
                            <td><?= esc($vencimiento['fecha_vencimiento']) ?></td>
                            <td><?= esc($vencimiento['estado_actividad']) ?></td>
                            <td><?= esc($vencimiento['fecha_realizacion']) ?></td>
                            <td><?= esc($vencimiento['observaciones']) ?></td>
                            <td>
                                <a href="<?= site_url('vencimientos/edit/' . esc($vencimiento['id'])) ?>" class="btn btn-primary">Editar</a>
                                <a href="<?= site_url('vencimientos/delete/' . esc($vencimiento['id'])) ?>" class="btn btn-danger" onclick="return confirm('¿Estás seguro de eliminar este vencimiento?');">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10">No hay vencimientos registrados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </form>

    <!-- Script de filtrado y manejo de checkboxes -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Checkbox "Select All"
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.email-checkbox');
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(function(checkbox) {
                    checkbox.checked = selectAll.checked;
                });
            });

            // Filtros de la tabla (se ajusta el índice de celdas por la columna de checkbox)
            const filters = {
                id: document.getElementById('filter_id'),
                cliente: document.getElementById('filter_cliente'),
                consultor: document.getElementById('filter_consultor'),
                mantenimiento: document.getElementById('filter_mantenimiento'),
                fecha_vencimiento: document.getElementById('filter_fecha_vencimiento'),
                estado: document.getElementById('filter_estado'),
                fecha_realizacion: document.getElementById('filter_fecha_realizacion'),
                observaciones: document.getElementById('filter_observaciones'),
            };

            const table = document.getElementById('vencimientosTable');
            const tbody = table.getElementsByTagName('tbody')[0];
            const rows = tbody.getElementsByTagName('tr');

            function filterTable() {
                const filterValues = {
                    id: filters.id.value.toLowerCase(),
                    cliente: filters.cliente.value.toLowerCase(),
                    consultor: filters.consultor.value.toLowerCase(),
                    mantenimiento: filters.mantenimiento.value.toLowerCase(),
                    fecha_vencimiento: filters.fecha_vencimiento.value,
                    estado: filters.estado.value.toLowerCase(),
                    fecha_realizacion: filters.fecha_realizacion.value,
                    observaciones: filters.observaciones.value.toLowerCase(),
                };

                // Recorremos cada fila (recordando que la primera celda es para el checkbox)
                for (let i = 0; i < rows.length; i++) {
                    const cells = rows[i].getElementsByTagName('td');
                    let showRow = true;

                    // Filtro para ID (celda 1)
                    if (filterValues.id && !cells[1].textContent.toLowerCase().includes(filterValues.id)) {
                        showRow = false;
                    }
                    // Filtro para Cliente (celda 2)
                    if (filterValues.cliente && !cells[2].textContent.toLowerCase().includes(filterValues.cliente)) {
                        showRow = false;
                    }
                    // Filtro para Consultor (celda 3)
                    if (filterValues.consultor && !cells[3].textContent.toLowerCase().includes(filterValues.consultor)) {
                        showRow = false;
                    }
                    // Filtro para Mantenimiento (celda 4)
                    if (filterValues.mantenimiento && !cells[4].textContent.toLowerCase().includes(filterValues.mantenimiento)) {
                        showRow = false;
                    }
                    // Filtro para Fecha de Vencimiento (celda 5)
                    if (filterValues.fecha_vencimiento && cells[5].textContent !== filterValues.fecha_vencimiento) {
                        showRow = false;
                    }
                    // Filtro para Estado (celda 6)
                    if (filterValues.estado && !cells[6].textContent.toLowerCase().includes(filterValues.estado)) {
                        showRow = false;
                    }
                    // Filtro para Fecha de Realización (celda 7)
                    if (filterValues.fecha_realizacion && cells[7].textContent !== filterValues.fecha_realizacion) {
                        showRow = false;
                    }
                    // Filtro para Observaciones (celda 8)
                    if (filterValues.observaciones && !cells[8].textContent.toLowerCase().includes(filterValues.observaciones)) {
                        showRow = false;
                    }

                    rows[i].style.display = showRow ? '' : 'none';
                }
            }

            // Asociar eventos a los filtros
            for (let key in filters) {
                if (filters.hasOwnProperty(key)) {
                    filters[key].addEventListener('change', filterTable);
                    filters[key].addEventListener('keyup', filterTable);
                }
            }
        });
    </script>
</body>

</html>
