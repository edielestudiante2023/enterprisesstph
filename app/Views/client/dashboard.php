<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Enterprisesst</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f7fa;
        }

        /* Estilos de la barra superior */
        .navbar {
            background-color: #e9f4ff;
            border-bottom: 1px solid #c8e1f9;
        }

        .navbar-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 20px;
        }

        .navbar-content img {
            max-height: 80px;
        }

        .btn-logout {
            background-color: #ff4d4d;
            color: white;
            font-weight: bold;
            padding: 10px 20px;
            border-radius: 5px;
            margin-top: 20px;
        }

        .btn-logout:hover {
            background-color: #e63939;
        }

        /* Estilo general */
        .welcome-header {
            margin-top: 2rem;
            text-align: center;
            color: #003366;
        }

        .quick-access {
            margin-top: 2rem;
        }

        .quick-access .btn {
            font-size: 1.2rem;
            font-weight: bold;
            padding: 1rem;
            border-radius: 8px;
        }

        .quick-access .btn i {
            margin-right: 0.5rem;
        }

        .asesoria-card {
            background-color: #e9ecef;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .asesoria-card img {
            max-height: 100px;
            margin: 1rem auto;
        }

        .table th {
            background-color: #b3d9ff;
            color: #003366;
        }

        .table td a {
            color: #003366;
            text-decoration: none;
        }

        .table td a:hover {
            color: #007bff;
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar">
        <div class="container navbar-content">
            <!-- Logo izquierdo -->
            <a href="https://dashboard.cycloidtalent.com/login" target="_blank">
                <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Logo Enterprisesst">
            </a>
            <!-- Logo central -->
            <a href="https://cycloidtalent.com/index.php/consultoria-sst" target="_blank">
                <img src="<?= base_url('uploads/logosst.png') ?>" alt="Logo SST">
            </a>
            <!-- Logo derecho -->
            <a href="https://cycloidtalent.com/" target="_blank">
                <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Logo Cycloid">
            </a>
        </div>
    </nav>

    <!-- Header -->
    <div class="container">
        <div class="welcome-header">
            <h1> <?= esc($client['nombre_cliente']) ?>!</h1>
            <p>Bienvenido a Enterprisesst, tu aplicativo especializado en SG-SST</p>
        </div>

        

        <!-- Quick Access Buttons -->
        <div class="quick-access text-center">
            <div class="row justify-content-center">
                <div class="col-md-4 mb-3">
                    <a href="<?= base_url('listPlanTrabajoCliente/' . $client['id_cliente']) ?>" target="_blank" class="btn btn-primary w-100">
                        <i class="fas fa-calendar-alt"></i> Plan de Trabajo
                    </a>
                </div>
                <div class="col-md-4 mb-3">
                    <a href="<?= base_url('/report_dashboard') ?>" target="_blank" class="btn btn-success w-100">
                        <i class="fas fa-file-alt"></i> Documentos
                    </a>
                </div>
                <div class="col-md-4 mb-3">
                    <a href="<?= base_url('client/panel') ?>" target="_blank" class="btn btn-info w-100">
                        <i class="fas fa-chart-line"></i> Panel de Gestión
                    </a>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="mt-5">
            <h2 class="text-center text-primary">Dispositivos Documentales Sistema de Gestión en Seguridad y Salud en el Trabajo</h2>
            <table id="accesosTable" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Dimensión</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $current_dimension = '';
                    $index = 1;

                    foreach ($accesos as $acceso):
                        if ($current_dimension !== $acceso['dimension']):
                            $current_dimension = $acceso['dimension']; ?>
                            <!-- Nueva fila para mostrar la dimensión como una sección separada -->
                            <tr>
                                <td><?= $index ?></td>
                                <td><strong><?= esc($current_dimension) ?></strong></td>
                            </tr>
                        <?php endif; ?>
                        <!-- Fila con datos del acceso -->
                        <tr>
                            <td><?= $index++ ?></td>
                            <td><a href="<?= base_url($acceso['url']) ?>" target="_blank"><?= esc($acceso['nombre']) ?></a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Botón de cerrar sesión -->
        <div class="text-center">
            <button class="btn btn-logout" onclick="logout()">Cerrar Sesión</button>
        </div>

        <!-- Asesoría Section -->
        <div class="asesoria-card mt-5">
            <h2>¿Necesitas Asesoría?</h2>
            <p>Contáctanos para obtener ayuda en la gestión de tu SST.</p>
            <div>
                <img src="<?= base_url('uploads/logocycloid.png') ?>" alt="Cycloid">
            </div>
            <p><strong>Email:</strong> diana.cuestas@cycloidtalent.com</p>
            <p><strong>Teléfono:</strong> 3229074371</p>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center mt-5 py-4" style="background-color: #e9f4ff; color: #003366;">
        <p>&copy; 2024 Cycloid Talent SAS. Todos los derechos reservados.</p>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            $('#accesosTable').DataTable({
                paging: true,
                searching: true,
                lengthChange: true,
                pageLength: 20, // Mostrar 20 registros por defecto
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.3/i18n/es_es.json'
                }
            });

            // SweetAlert greeting
            Swal.fire({
                title: '¡Bienvenido!',
                text: 'Hola <?= esc($client['nombre_cliente']) ?>, nos alegra tenerte en Enterprisesst - Empowered by Cycloid Talent.   ',
                icon: 'info',
                confirmButtonText: 'Gracias'
            });
        });

        function logout() {
            Swal.fire({
                title: 'Cerrar Sesión',
                text: '¿Estás seguro de que deseas salir?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, salir',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "<?= base_url('/logout') ?>";
                }
            });
        }
    </script>

</body>

</html>
