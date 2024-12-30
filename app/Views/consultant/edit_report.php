<!DOCTYPE html>
<html>

<head>
    <title>Editar Reporte</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
</head>

<body class="bg-light">

    <nav style="background-color: white; position: fixed; top: 0; width: 100%; z-index: 1000; padding: 10px 0; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; max-width: 1200px; margin: 0 auto; padding: 0 20px;">

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

            <!-- Botón -->
            <div style="text-align: center;">
                <h2 style="margin: 0; font-size: 16px;">Ir a Dashboard</h2>
                <a href="<?= base_url('/dashboardconsultant') ?>" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; font-size: 14px; margin-top: 5px;">Ir a DashBoard</a>
            </div>
        </div>
    </nav>

    <!-- Ajustar el espaciado para evitar que el contenido se oculte bajo el navbar fijo -->
    <div style="height: 160px;"></div>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h2>Editar Reporte</h2>
            </div>
            <div class="card-body">
                <?php if (session()->getFlashdata('msg')): ?>
                    <div class="alert alert-warning">
                        <?= session()->getFlashdata('msg') ?>
                    </div>
                <?php endif; ?>
                <form action="<?= base_url('/editReportPost/' . $report['id_reporte']) ?>" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="titulo_reporte">Título del Reporte:</label>
                        <input type="text" class="form-control" id="titulo_reporte" name="titulo_reporte" value="<?= $report['titulo_reporte'] ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="Tipo_documento">Tipo de Documento:</label>
                        <select class="form-control" id="Tipo_documento" name="Tipo_documento" required>
                            <option value="PDF" <?= $report['Tipo_documento'] == 'PDF' ? 'selected' : '' ?>>PDF</option>
                            <option value="HOJA DE CALCULO" <?= $report['Tipo_documento'] == 'HOJA DE CALCULO' ? 'selected' : '' ?>>HOJA DE CALCULO</option>
                            <option value="DOC" <?= $report['Tipo_documento'] == 'DOC' ? 'selected' : '' ?>>DOC</option>
                            <option value="VIDEO" <?= $report['Tipo_documento'] == 'VIDEO' ? 'selected' : '' ?>>VIDEO</option>
                            <option value="IMAGEN" <?= $report['Tipo_documento'] == 'IMAGEN' ? 'selected' : '' ?>>IMAGEN</option>
                        </select>
                    </div>

                    <!-- Mostrar archivo actual con botón para verlo -->
                    <div class="form-group">
                        <label>Documento Actual:</label>
                        <a href="<?= $report['enlace'] ?>" target="_blank" class="btn btn-info">Ver Documento</a>
                    </div>

                    <!-- Permitir subir un nuevo archivo -->
                    <div class="form-group">
                        <label for="archivo">Subir Nuevo Archivo (Opcional):</label>
                        <input type="file" class="form-control" id="archivo" name="archivo" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.png">
                    </div>

                    <div class="form-group">
                        <label for="estado">Estado:</label>
                        <select class="form-control" id="estado" name="estado" required>
                            <option value="ABIERTO" <?= $report['estado'] == 'ABIERTO' ? 'selected' : '' ?>>ABIERTO</option>
                            <option value="GESTIONANDO" <?= $report['estado'] == 'GESTIONANDO' ? 'selected' : '' ?>>GESTIONANDO</option>
                            <option value="CERRADO" <?= $report['estado'] == 'CERRADO' ? 'selected' : '' ?>>CERRADO</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="observaciones">Observaciones:</label>
                        <textarea class="form-control" id="observaciones" name="observaciones"><?= $report['observaciones'] ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="id_cliente">Cliente:</label>
                        <select class="form-control" id="id_cliente" name="id_cliente" required>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?= $client['id_cliente'] ?>" <?= $report['id_cliente'] == $client['id_cliente'] ? 'selected' : '' ?>>
                                    <?= $client['nombre_cliente'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="id_report_type">Tipo de Reporte:</label>
                        <select class="form-control" id="id_report_type" name="id_report_type" required>
                            <?php foreach ($reportTypes as $type): ?>
                                <option value="<?= $type['id_report_type'] ?>" <?= $report['id_report_type'] == $type['id_report_type'] ? 'selected' : '' ?>>
                                    <?= $type['report_type'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Actualizar Reporte</button>
                </form>

            </div>
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

    <script>
        $(document).ready(function() {
            $('table').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/es_es.json"
                }
            });
        });
    </script>
</body>

</html>