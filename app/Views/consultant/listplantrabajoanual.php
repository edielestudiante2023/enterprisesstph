<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Actividades - Plan de Trabajo Anual</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- DataTables Buttons CSS -->
    <link href="https://cdn.datatables.net/buttons/2.3.3/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <!-- Select2 CSS para select buscable -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* Estilos personalizados */
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm">
        <!-- Contenido del navbar -->
    </nav>

    <div style="height: 100px;"></div>

    <div class="container-fluid mt-5">
        <h2 class="text-center mb-4">Lista de Actividades del Plan de Trabajo Anual</h2>

        <!-- Filtro de cliente -->
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="clienteSelect">Seleccionar Cliente:</label>
                <select id="clienteSelect" class="form-select">
                    <option value="">Seleccione un cliente</option>
                </select>
            </div>
            <div class="col-md-2 align-self-end">
                <button id="loadData" class="btn btn-primary">Cargar Datos</button>
            </div>
        </div>

        <div class="table-responsive">
            <table id="actividadesTable" class="table table-striped table-bordered" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Actividad</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Los datos se cargarán vía AJAX -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Cargar clientes vía AJAX
            $.ajax({
                url: "<?= base_url('/api/getClientes') ?>",
                method: "GET",
                dataType: "json",
                success: function(data) {
                    data.forEach(function(cliente) {
                        $("#clienteSelect").append('<option value="' + cliente.id + '">' + cliente.nombre + '</option>');
                    });
                },
                error: function() {
                    alert('Error al cargar la lista de clientes.');
                }
            });

            // Cargar datos filtrados por cliente
            $("#loadData").click(function() {
                var clienteID = $("#clienteSelect").val();
                if (clienteID) {
                    $('#actividadesTable').DataTable({
                        ajax: {
                            url: "<?= base_url('/api/getActividadesAjax') ?>?cliente=" + clienteID,
                            dataSrc: 'data'
                        },
                        columns: [
                            { data: "id_ptacliente" },
                            { data: "nombre_cliente" },
                            { data: "actividad_plandetrabajo" },
                            { data: "estado_actividad" },
                            { data: null, render: function(data, type, row) {
                                return '<a href="<?= base_url("editPlanDeTrabajoAnual") ?>/' + row.id_ptacliente + '" class="btn btn-warning btn-sm">Editar</a>';
                            }}
                        ]
                    });
                } else {
                    alert('Por favor, seleccione un cliente.');
                }
            });
        });
    </script>
</body>
</html>
