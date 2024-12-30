<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Cronogramas de Capacitación</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            color: #333;
        }

        .container {
            margin-top: 30px;
            max-width: 1200px;
        }

        h2 {
            color: #333;
            font-weight: bold;
            text-align: center;
            margin-bottom: 30px;
        }

        .table-container {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .table th {
            background-color: #007bff;
            color: #fff;
            text-align: center;
        }

        .table td {
            text-align: center;
            font-size: 15px;
            vertical-align: middle;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            font-weight: bold;
            border-radius: 5px;
        }

        .empty-message {
            color: #333;
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            padding: 20px;
        }
    </style>
</head>

<body>

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
    </nav>

    <!-- Ajustar el espaciado para evitar que el contenido se oculte bajo el navbar fijo -->
    <div style="height: 120px;"></div>

    <div class="container">
        <h2>Listado de Cronogramas de Capacitación</h2>

        <?php if (session()->getFlashdata('msg')) : ?>
            <div class="alert alert-success text-center"><?= session()->getFlashdata('msg') ?></div>
        <?php endif; ?>

        <div class="table-container">
            <table id="cronogramasTable" class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th>ID Cronograma</th>
                        <th>Cliente</th>
                        <th>Capacitación</th>
                        <th>Fecha Programada</th>
                        <th>Fecha de Realización</th>
                        <th>Estado</th>
                        <th>Perfil de Asistentes</th>
                        <th>Nombre del Capacitador</th>
                        <th>Horas de Duración</th>
                        <th>Indicador de Realización</th>
                        <th>Número de Asistentes</th>
                        <th>Número Total de Personas Programadas</th>
                        <th>Porcentaje de Cobertura</th>
                        <th>Número de Personas Evaluadas</th>
                        <th>Promedio de Calificaciones</th>
                        <th>Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($cronogramas)) : ?>
                        <tr>
                            <td colspan="16" class="empty-message">No hay cronogramas de capacitación registrados.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($cronogramas as $cronograma) : ?>
                            <tr>
                                <td><?= esc($cronograma['id_cronograma_capacitacion']) ?></td>
                                <td><?= esc($cronograma['nombre_cliente']) ?></td>
                                <td><?= esc($cronograma['nombre_capacitacion']) ?></td>
                                <td><?= esc($cronograma['fecha_programada']) ?></td>
                                <td><?= esc($cronograma['fecha_de_realizacion']) ?></td>
                                <td><?= esc($cronograma['estado']) ?></td>
                                <td><?= esc($cronograma['perfil_de_asistentes']) ?></td>
                                <td><?= esc($cronograma['nombre_del_capacitador']) ?></td>
                                <td><?= esc($cronograma['horas_de_duracion_de_la_capacitacion']) ?></td>
                                <td><?= esc($cronograma['indicador_de_realizacion_de_la_capacitacion']) ?></td>
                                <td><?= esc($cronograma['numero_de_asistentes_a_capacitacion']) ?></td>
                                <td><?= esc($cronograma['numero_total_de_personas_programadas']) ?></td>
                                <td><?= esc($cronograma['porcentaje_cobertura']) ?></td>
                                <td><?= esc($cronograma['numero_de_personas_evaluadas']) ?></td>
                                <td><?= esc($cronograma['promedio_de_calificaciones']) ?></td>
                                <td><?= esc($cronograma['observaciones']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Footer -->
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


    <!-- jQuery, DataTables and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#cronogramasTable').DataTable({
                paging: true,
                searching: true,
                lengthChange: true,
                pageLength: 5,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.3/i18n/es_es.json'
                }
            });
        });
    </script>
</body>

</html>