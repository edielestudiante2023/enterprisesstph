<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Mantenimiento</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Agregar Mantenimiento</h1>
        
        <!-- Formulario de agregar mantenimiento -->
        <form method="post" action="<?= base_url('mantenimientos/addpost') ?>" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="detalle_mantenimiento" class="form-label">Detalle del Mantenimiento:</label>
                <input type="text" class="form-control" name="detalle_mantenimiento" id="detalle_mantenimiento" required>
                <div class="invalid-feedback">
                    Por favor, ingresa el detalle del mantenimiento.
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Guardar</button>
        </form>
    </div>

    <!-- Bootstrap 5 JS y dependencias -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

    <!-- Validación del formulario -->
    <script>
        (function () {
            'use strict';

            // Seleccionar todos los formularios que necesitan validación
            var forms = document.querySelectorAll('.needs-validation');

            // Iterar sobre los formularios y prevenir el envío si no son válidos
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
</body>
</html>