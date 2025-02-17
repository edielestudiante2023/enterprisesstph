<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Looker Studio</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.1/dist/sweetalert2.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <!-- Tarjeta de Edición -->
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white text-center">
                <h2>Editar Looker Studio</h2>
            </div>
            <div class="card-body">
                <form action="<?= base_url('lookerstudio/editPost/' . $lookerStudio['id_looker']) ?>" method="post" id="editForm">
                    <div class="mb-3">
                        <label for="tipodedashboard" class="form-label">Tipo de Dashboard</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="tipodedashboard" 
                            name="tipodedashboard" 
                            value="<?= htmlspecialchars($lookerStudio['tipodedashboard'], ENT_QUOTES, 'UTF-8') ?>" 
                            required>
                        <div class="invalid-feedback">Por favor, ingrese el tipo de dashboard.</div>
                    </div>
                    <div class="mb-3">
                        <label for="enlace" class="form-label">Enlace</label>
                        <input 
                            type="url" 
                            class="form-control" 
                            id="enlace" 
                            name="enlace" 
                            value="<?= htmlspecialchars($lookerStudio['enlace'], ENT_QUOTES, 'UTF-8') ?>" 
                            required>
                        <div class="invalid-feedback">Por favor, ingrese un enlace válido.</div>
                    </div>
                    <div class="mb-3">
                        <label for="id_cliente" class="form-label">Cliente</label>
                        <select class="form-select" id="id_cliente" name="id_cliente" required>
                            <option value="" disabled>Seleccione un cliente</option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?= $client['id_cliente'] ?>" 
                                    <?= $client['id_cliente'] == $lookerStudio['id_cliente'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($client['nombre_cliente'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Por favor, seleccione un cliente.</div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-success">Guardar Cambios</button>
                        <a href="<?= base_url('lookerstudio/list') ?>" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.1/dist/sweetalert2.all.min.js"></script>
    <script>
        // Inicializar Select2
        $(document).ready(function() {
            $('#id_cliente').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Buscar cliente...',
                allowClear: false,
                language: {
                    noResults: function() {
                        return "No se encontraron resultados";
                    },
                    searching: function() {
                        return "Buscando...";
                    }
                }
            });
        });

        // Validación nativa de Bootstrap
        (function () {
            'use strict';
            var forms = document.querySelectorAll('.needs-validation');
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

        // Confirmación con SweetAlert2
        $('#editForm').on('submit', function(e) {
            e.preventDefault(); // Evita el envío inmediato del formulario
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Los cambios serán guardados",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, guardar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit(); // Enviar el formulario si el usuario confirma
                }
            });
        });
    </script>
</body>
</html>
