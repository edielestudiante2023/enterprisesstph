<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nuevo Documento de Apoyo</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            color: #343a40;
            font-family: Arial, sans-serif;
            /* Se añade padding superior para que el contenido no quede tapado por el navbar fijo */
            padding-top: 120px;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-light bg-white fixed-top shadow-sm">
        <div class="container">
            <div class="row w-100 align-items-center">
                <!-- Logos: se agrupan en una fila en pantallas medianas y grandes -->
                <div class="col-md-8 col-12 d-flex justify-content-between align-items-center">
                    <a href="https://dashboard.cycloidtalent.com/login" class="d-inline-block">
                        <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo" class="img-fluid" style="max-height: 60px;">
                    </a>
                    <a href="https://cycloidtalent.com/index.php/consultoria-sst" class="d-inline-block mx-2">
                        <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST Logo" class="img-fluid" style="max-height: 60px;">
                    </a>
                    <a href="https://cycloidtalent.com/" class="d-inline-block">
                        <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloids Logo" class="img-fluid" style="max-height: 60px;">
                    </a>
                </div>
                <!-- Botón de Dashboard -->
                <div class="col-md-4 col-12 text-center mt-2 mt-md-0">
                    <div>
                        <small class="d-block">Ir a Dashboard</small>
                        <a href="<?= base_url('/dashboardconsultant') ?>" class="btn btn-primary btn-sm">Ir a Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <div class="container my-5">
        <div class="card mx-auto" style="max-width: 700px;">
            <div class="card-body">
                <h1 class="card-title text-center mb-4">Crear Nuevo Documento de Apoyo</h1>
                <form action="<?= base_url('addMatrizCycloidPost') ?>" method="post">
                    <div class="mb-3">
                        <label for="titulo_matriz" class="form-label">Título</label>
                        <input type="text" name="titulo_matriz" id="titulo_matriz" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="Tipo_documento" class="form-label">Tipo de Documento</label>
                        <input type="text" name="Tipo_documento" id="Tipo_documento" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="enlace" class="form-label">Enlace al Documento</label>
                        <input type="text" name="enlace" id="enlace" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="observaciones" class="form-label">Observaciones</label>
                        <textarea name="observaciones" id="observaciones" rows="4" class="form-control"></textarea>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Crear </button>
                </form>
                <div class="text-center mt-3">
                    <a href="/listMatricesCycloid" class="btn btn-link">Volver a la lista de matrices</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white py-4 border-top" style="color: #3A3F51; font-size: 14px;">
        <div class="container text-center">
            <p class="mb-0 fw-bold">Cycloid Talent SAS</p>
            <p class="mb-1">Todos los derechos reservados © 2024</p>
            <p class="mb-1">NIT: 901.653.912</p>
            <p class="mb-1">
                Sitio oficial:
                <a href="https://cycloidtalent.com/" target="_blank" class="text-decoration-none text-primary">
                    https://cycloidtalent.com/
                </a>
            </p>
            <p class="mb-2"><strong>Nuestras Redes Sociales:</strong></p>
            <div class="d-flex justify-content-center gap-3">
                <a href="https://www.facebook.com/CycloidTalent" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733547.png" alt="Facebook" style="height: 24px;">
                </a>
                <a href="https://co.linkedin.com/company/cycloid-talent" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733561.png" alt="LinkedIn" style="height: 24px;">
                </a>
                <a href="https://www.instagram.com/cycloid_talent?igsh=Nmo4d2QwZDg5dHh0" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733558.png" alt="Instagram" style="height: 24px;">
                </a>
                <a href="https://www.tiktok.com/@cycloid_talent?_t=8qBSOu0o1ZN&_r=1" target="_blank">
                    <img src="https://cdn-icons-png.flaticon.com/512/3046/3046126.png" alt="TikTok" style="height: 24px;">
                </a>
            </div>
        </div>
    </footer>

    <!-- jQuery y DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('table').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
                }
            });
        });
    </script>
    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>

</html>