<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Documentos - Enterprisesst</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f9f9f9;
            color: #333;
        }

        .container {
            margin-top: 30px;
            max-width: 1200px;
        }

        .table-container {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .table-container h2 {
            color: #333;
            font-weight: 600;
            font-size: 24px;
            margin-bottom: 15px;
        }

        .table th {
            background-color: #0066cc;
            color: #fff;
            text-align: center;
            font-size: 16px;
        }

        .table td {
            font-size: 15px;
            vertical-align: middle;
        }

        .table td a {
            color: #0066cc;
            text-decoration: underline;
            font-weight: 500;
        }

        .table td a:hover {
            color: #004c99;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 20px;
            background-color: #0066cc;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .back-link:hover {
            background-color: #004c99;
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
        <!-- <a href="<?= base_url('/dashboardclient') ?>" class="back-link">Volver al Dashboard</a> -->

        <div class="table-container">
            <h2>Hojas de Cálculo Interactivas</h2>
            <?php if (!empty($hojasDeCalculo)) : ?>
                <table id="hojasCalculoTable" class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Enlace</th>
                            <th>Estado</th>
                            <th>Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($hojasDeCalculo as $reporte) : ?>
                            <tr>
                                <td><?= esc($reporte['titulo_reporte']) ?></td>
                                <td>
                                    <?php
                                    // Validar si el enlace es relativo o absoluto
                                    if (!empty($reporte['enlace'])):
                                        // Si es un enlace completo (URL absoluta)
                                        if (filter_var($reporte['enlace'], FILTER_VALIDATE_URL)): ?>
                                            <a href="<?= esc($reporte['enlace']) ?>" target="_blank">Ver Documento</a>
                                        <?php else:
                                            // Si es una ruta relativa, construir la URL completa
                                        ?>
                                            <a href="<?= base_url(esc($reporte['enlace'])) ?>" target="_blank">Ver Documento</a>
                                        <?php endif;
                                    else: ?>
                                        <span class="text-muted">Enlace no disponible</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($reporte['estado']) ?></td>
                                <td><?= esc($reporte['observaciones']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>

                </table>
            <?php else : ?>
                <p class="empty-message">No hay documentos de Hojas de Cálculo Interactivas disponibles.</p>
            <?php endif; ?>
        </div>

        <div class="table-container">
            <h2>Matrices</h2>
            <?php if (!empty($matrices)) : ?>
                <table id="matricesTable" class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Enlace</th>
                            <th>Estado</th>
                            <th>Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($matrices as $reporte) : ?>
                            <tr>
                                <td><?= esc($reporte['titulo_reporte']) ?></td>
                                <td><a href="<?= esc($reporte['enlace']) ?>" target="_blank">Ver Documento</a></td>
                                <td><?= esc($reporte['estado']) ?></td>
                                <td><?= esc($reporte['observaciones']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p class="empty-message">No hay documentos de Matrices disponibles.</p>
            <?php endif; ?>
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


    <!-- DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#hojasCalculoTable').DataTable({
                paging: true,
                searching: true,
                lengthChange: true,
                pageLength: 5,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.3/i18n/es_es.json'
                }
            });
            $('#matricesTable').DataTable({
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