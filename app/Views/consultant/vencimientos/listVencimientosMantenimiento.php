<?php
// Ordenar el array $vencimientos de forma descendente por id
usort($vencimientos, function ($a, $b) {
    return $b['id'] - $a['id'];
});
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Listado de Vencimientos de Mantenimiento</title>
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
            cursor: pointer;
            /* Indica que es clickable */
        }

        th.sortable:hover {
            background-color: #e0e0e0;
        }

        /* Indicadores de ordenación */
        th.sort-asc::after {
            content: " ▲";
        }

        th.sort-desc::after {
            content: " ▼";
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
        <button type="submit" class="btn btn-warning" form="sendSelectedForm">Enviar Emails a Seleccionados</button>
    </div>

    
    

    <!-- Filtro por cliente en la parte superior -->
    <div style="margin: 10px 0;">
        <label for="topFilter_cliente"><strong>Filtrar por Cliente:</strong></label>
        <input type="text" id="topFilter_cliente" placeholder="Buscar Cliente" />
        <button type="button" id="resetFilters" class="btn btn-secondary">Limpiar Filtros</button>
    </div>


    <!-- Formulario para enviar emails a registros seleccionados -->
    <form id="sendSelectedForm" method="post" action="<?= site_url('vencimientos/send-selected-emails') ?>">
        <table id="vencimientosTable">
            <thead>
                <tr>
                    <!-- Columna para checkboxes con opción "Select All" -->
                    <th><input type="checkbox" id="selectAll" /></th>
                    <th class="sortable" data-col="1">ID</th>
                    <th class="sortable" data-col="2">Cliente</th>
                    <th class="sortable" data-col="3">Consultor</th>
                    <th class="sortable" data-col="4">Mantenimiento</th>
                    <th class="sortable" data-col="5">Fecha de Vencimiento</th>
                    <th class="sortable" data-col="6">Estado</th>
                    <th class="sortable" data-col="7">Fecha de Realización</th>
                    <th class="sortable" data-col="8">Observaciones</th>
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
                    <!-- Filtro para Cliente (Texto) -->
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
                    <!-- Filtro para Fecha de Vencimiento (rango de fechas) -->
                    <th>
                        <input type="date" id="filter_fecha_vencimiento_inicio" placeholder="Desde" style="margin-bottom:4px;" />
                        <input type="date" id="filter_fecha_vencimiento_fin" placeholder="Hasta" />
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Función de ayuda para obtener elementos por ID
            function $(id) {
                return document.getElementById(id);
            }

            // --- Persistencia de filtros usando localStorage ---
            const filterIds = ['filter_id', 'filter_cliente', 'filter_consultor', 'filter_mantenimiento', 'filter_fecha_vencimiento_inicio', 'filter_fecha_vencimiento_fin', 'filter_estado', 'filter_fecha_realizacion', 'filter_observaciones'];
            filterIds.forEach(function(filterId) {
                const storedValue = localStorage.getItem(filterId);
                if (storedValue) {
                    $(filterId).value = storedValue;
                }
            });
            // Sincronizar el filtro superior de cliente con el de pie de tabla
            const storedTopCliente = localStorage.getItem('topFilter_cliente');
            if (storedTopCliente) {
                $('topFilter_cliente').value = storedTopCliente;
                $('filter_cliente').value = storedTopCliente;
            }

            // Guardar el estado de cada filtro
            filterIds.concat(['topFilter_cliente']).forEach(function(filterId) {
                $(filterId).addEventListener('change', function() {
                    localStorage.setItem(filterId, this.value);
                    if (filterId === 'topFilter_cliente') {
                        $('filter_cliente').value = this.value;
                    }
                    filterTable();
                });
                $(filterId).addEventListener('keyup', function() {
                    localStorage.setItem(filterId, this.value);
                    if (filterId === 'topFilter_cliente') {
                        $('filter_cliente').value = this.value;
                    }
                    filterTable();
                });
            });

            // --- Manejador del checkbox "Select All" ---
            const selectAll = $('selectAll');
            const checkboxes = document.querySelectorAll('.email-checkbox');
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(function(checkbox) {
                    checkbox.checked = selectAll.checked;
                });
            });

            // --- Función de filtrado de la tabla ---
            const filters = {
                id: $('filter_id'),
                cliente: $('filter_cliente'),
                consultor: $('filter_consultor'),
                mantenimiento: $('filter_mantenimiento'),
                fecha_vencimiento_inicio: $('filter_fecha_vencimiento_inicio'),
                fecha_vencimiento_fin: $('filter_fecha_vencimiento_fin'),
                estado: $('filter_estado'),
                fecha_realizacion: $('filter_fecha_realizacion'),
                observaciones: $('filter_observaciones')
            };

            const table = $('vencimientosTable');
            const tbody = table.getElementsByTagName('tbody')[0];
            const rows = tbody.getElementsByTagName('tr');

            function filterTable() {
                const filterValues = {
                    id: filters.id.value.toLowerCase(),
                    cliente: filters.cliente.value.toLowerCase(),
                    consultor: filters.consultor.value.toLowerCase(),
                    mantenimiento: filters.mantenimiento.value.toLowerCase(),
                    fecha_vencimiento_inicio: filters.fecha_vencimiento_inicio.value,
                    fecha_vencimiento_fin: filters.fecha_vencimiento_fin.value,
                    estado: filters.estado.value.toLowerCase(),
                    fecha_realizacion: filters.fecha_realizacion.value,
                    observaciones: filters.observaciones.value.toLowerCase()
                };

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
                    // Filtro para Fecha de Vencimiento (celda 5) por rango
                    let cellDate = cells[5].textContent.trim();
                    if (filterValues.fecha_vencimiento_inicio || filterValues.fecha_vencimiento_fin) {
                        if (cellDate === "") {
                            showRow = false;
                        } else {
                            let rowDate = new Date(cellDate);
                            if (filterValues.fecha_vencimiento_inicio) {
                                let startDate = new Date(filterValues.fecha_vencimiento_inicio);
                                if (rowDate < startDate) showRow = false;
                            }
                            if (filterValues.fecha_vencimiento_fin) {
                                let endDate = new Date(filterValues.fecha_vencimiento_fin);
                                if (rowDate > endDate) showRow = false;
                            }
                        }
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

            for (let key in filters) {
                if (filters.hasOwnProperty(key)) {
                    filters[key].addEventListener('change', filterTable);
                    filters[key].addEventListener('keyup', filterTable);
                }
            }

            // --- Funcionalidad de ordenación ---
            let currentSortCol = localStorage.getItem('sortCol') || null;
            let currentSortOrder = localStorage.getItem('sortOrder') || 'asc';

            function sortTableByColumn(colIndex, sortOrder) {
                let rowsArray = Array.from(rows);
                rowsArray.sort(function(a, b) {
                    let aText = a.getElementsByTagName('td')[colIndex].textContent.trim();
                    let bText = b.getElementsByTagName('td')[colIndex].textContent.trim();

                    // Si son numéricos, comparar como números
                    let aNum = parseFloat(aText);
                    let bNum = parseFloat(bText);
                    if (!isNaN(aNum) && !isNaN(bNum)) {
                        return sortOrder === 'asc' ? aNum - bNum : bNum - aNum;
                    }
                    // De lo contrario, comparar como cadenas
                    return sortOrder === 'asc' ? aText.localeCompare(bText) : bText.localeCompare(aText);
                });
                rowsArray.forEach(function(row) {
                    tbody.appendChild(row);
                });
            }

            const headers = document.querySelectorAll('th.sortable');
            headers.forEach(function(header) {
                header.addEventListener('click', function() {
                    let colIndex = parseInt(this.getAttribute('data-col'));
                    if (currentSortCol == colIndex) {
                        currentSortOrder = (currentSortOrder === 'asc') ? 'desc' : 'asc';
                    } else {
                        currentSortOrder = 'asc';
                        currentSortCol = colIndex;
                    }
                    localStorage.setItem('sortCol', currentSortCol);
                    localStorage.setItem('sortOrder', currentSortOrder);
                    headers.forEach(function(h) {
                        h.classList.remove('sort-asc', 'sort-desc');
                    });
                    this.classList.add(currentSortOrder === 'asc' ? 'sort-asc' : 'sort-desc');
                    sortTableByColumn(colIndex, currentSortOrder);
                });
            });

            // Aplicar ordenación guardada (si existe)
            if (currentSortCol) {
                headers.forEach(function(header) {
                    if (parseInt(header.getAttribute('data-col')) === parseInt(currentSortCol)) {
                        header.classList.add(currentSortOrder === 'asc' ? 'sort-asc' : 'sort-desc');
                    }
                });
                sortTableByColumn(parseInt(currentSortCol), currentSortOrder);
            }
        });

        document.getElementById('resetFilters').addEventListener('click', function() {
            const filterIds = [
                'filter_id',
                'filter_cliente',
                'filter_consultor',
                'filter_mantenimiento',
                'filter_fecha_vencimiento_inicio',
                'filter_fecha_vencimiento_fin',
                'filter_estado',
                'filter_fecha_realizacion',
                'filter_observaciones',
                'topFilter_cliente'
            ];

            filterIds.forEach(function(filterId) {
                localStorage.removeItem(filterId);
                document.getElementById(filterId).value = '';
            });

            // Actualiza la tabla sin filtros
            filterTable();
        });
    </script>
</body>

</html>