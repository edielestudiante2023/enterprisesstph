<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cargar Evaluaciones Iniciales</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container my-5">
        <!-- Información sobre generación automática -->
        <div class="alert alert-info mb-4" role="alert">
            <h5 class="alert-heading">
                <i class="bi bi-info-circle-fill"></i> Generación Automática de Estándares Mínimos
            </h5>
            <p class="mb-2">
                Los <strong>Estándares Mínimos</strong> se generan automáticamente cuando se crea un nuevo cliente.
                Ya no es necesario cargar manualmente el archivo CSV cada vez.
            </p>
            <hr>
            <p class="mb-0">
                <small>
                    <i class="bi bi-check-circle text-success"></i>
                    <strong>Archivo CSV maestro:</strong>
                    <?php if (isset($csv_exists) && $csv_exists): ?>
                        <span class="text-success">Disponible</span>
                    <?php else: ?>
                        <span class="text-danger">No encontrado</span>
                    <?php endif; ?>
                </small>
            </p>
        </div>

        <!-- Formulario manual (solo para casos excepcionales) -->
        <div class="card shadow-sm">
            <div class="card-header bg-secondary text-white text-center">
                <h2 class="mb-0">Carga Manual (Solo para casos excepcionales)</h2>
            </div>
            <div class="card-body">
                <form action="<?= base_url('consultant/csvevaluacioninicial/upload') ?>" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="file" class="form-label">Seleccione un archivo (CSV):</label>
                        <input type="file" class="form-control" name="file" id="file" accept=".csv" required>
                        <div class="invalid-feedback">Por favor seleccione un archivo CSV.</div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Cargar Archivo</button>
                    </div>
                </form>

                <!-- Mostrar mensajes de éxito o error -->
                <?php if (session()->getFlashdata('success')) : ?>
                    <div class="alert alert-success mt-3" role="alert">
                        <?= session()->getFlashdata('success') ?>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')) : ?>
                    <div class="alert alert-danger mt-3" role="alert">
                        <?= session()->getFlashdata('error') ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
