<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cargar Plan de Trabajo</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container my-5">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">Cargar Archivo de Plan de Trabajo</h2>
                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#renewPlanModal">
                        <i class="bi bi-arrow-clockwise"></i> Renovar Plan de Trabajo
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Información de formato -->
                <div class="alert alert-info" role="alert">
                    <h6 class="alert-heading"><i class="bi bi-info-circle-fill"></i> Información del formato</h6>
                    <p class="mb-2"><strong>Encabezados requeridos (en orden):</strong></p>
                    <ol class="mb-2 small">
                        <li>id_cliente</li>
                        <li>phva_plandetrabajo</li>
                        <li>numeral_plandetrabajo</li>
                        <li>actividad_plandetrabajo</li>
                        <li>responsable_sugerido_plandetrabajo</li>
                        <li>observaciones</li>
                        <li>fecha_propuesta</li>
                    </ol>
                    <p class="mb-2 small"><strong>Formatos de fecha aceptados:</strong> dd/mm/yyyy, dd-mm-yyyy, yyyy-mm-dd, yyyy/mm/dd, dd.mm.yyyy, etc.</p>
                    <div class="alert alert-success mb-0 py-2" role="alert">
                        <small><i class="bi bi-check-circle"></i> <strong>Nota:</strong> Las actividades importadas se crearán automáticamente con estado <strong>ABIERTA</strong> y porcentaje de avance <strong>0%</strong>.</small>
                    </div>
                </div>

                <form action="<?= base_url('consultant/plan/upload') ?>" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="file" class="form-label"><i class="bi bi-file-earmark-spreadsheet"></i> Seleccione un archivo (Excel o CSV):</label>
                        <input type="file" class="form-control" name="file" id="file" accept=".xlsx, .xls, .csv" required>
                        <div class="invalid-feedback">Por favor seleccione un archivo válido (.xlsx, .xls, .csv).</div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="bi bi-cloud-upload"></i> Cargar Archivo
                        </button>
                    </div>
                </form>

                <hr class="my-4">

                <!-- Sección de Generación Automática -->
                <div class="alert alert-warning" role="alert">
                    <h6 class="alert-heading"><i class="bi bi-magic"></i> Generación Automática de Plan de Trabajo</h6>
                    <p class="mb-2 small">Si desea generar automáticamente el plan de trabajo desde las plantillas predefinidas, haga clic en el botón <strong>"Renovar Plan de Trabajo"</strong> en la parte superior.</p>
                    <p class="mb-0 small"><i class="bi bi-info-circle"></i> Esta opción carga las actividades estándar según el año del SGSST y tipo de servicio del cliente.</p>
                </div>

                <!-- Mostrar mensajes de éxito, advertencia o error -->
                <?php if (session()->getFlashdata('success')) : ?>
                    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                        <h5 class="alert-heading"><i class="bi bi-check-circle-fill"></i> ¡Éxito!</h5>
                        <hr>
                        <?= session()->getFlashdata('success') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('warning')) : ?>
                    <div class="alert alert-warning alert-dismissible fade show mt-3" role="alert">
                        <h5 class="alert-heading"><i class="bi bi-exclamation-triangle-fill"></i> Advertencia</h5>
                        <hr>
                        <?= session()->getFlashdata('warning') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')) : ?>
                    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                        <h5 class="alert-heading"><i class="bi bi-x-circle-fill"></i> Error</h5>
                        <hr>
                        <?= session()->getFlashdata('error') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal para Renovar Plan de Trabajo -->
    <div class="modal fade" id="renewPlanModal" tabindex="-1" aria-labelledby="renewPlanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="renewPlanModalLabel">
                        <i class="bi bi-arrow-clockwise"></i> Renovar Plan de Trabajo
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="<?= base_url('consultant/plan/generate') ?>" method="post" id="renewPlanForm">
                    <div class="modal-body">
                        <div class="alert alert-info" role="alert">
                            <small><i class="bi bi-info-circle-fill"></i> Esta opción generará automáticamente las actividades del plan de trabajo según las plantillas predefinidas.</small>
                        </div>

                        <!-- Selector de Cliente -->
                        <div class="mb-3">
                            <label for="id_cliente" class="form-label">Cliente <span class="text-danger">*</span></label>
                            <select class="form-select" name="id_cliente" id="id_cliente" required>
                                <option value="">Seleccione un cliente...</option>
                                <!-- Aquí se cargarán los clientes dinámicamente -->
                            </select>
                            <div class="invalid-feedback">Por favor seleccione un cliente.</div>
                        </div>

                        <!-- Selector de Año -->
                        <div class="mb-3">
                            <label for="year" class="form-label">Año del SGSST <span class="text-danger">*</span></label>
                            <select class="form-select" name="year" id="year" required>
                                <option value="">Seleccione el año...</option>
                                <option value="1">Año 1</option>
                                <option value="2">Año 2</option>
                                <option value="3">Año 3</option>
                            </select>
                            <div class="invalid-feedback">Por favor seleccione el año del SGSST.</div>
                        </div>

                        <!-- Selector de Tipo de Servicio -->
                        <div class="mb-3">
                            <label for="service_type" class="form-label">Tipo de Servicio <span class="text-danger">*</span></label>
                            <select class="form-select" name="service_type" id="service_type" required>
                                <option value="">Seleccione el tipo de servicio...</option>
                                <option value="mensual">Mensual</option>
                                <option value="bimensual">Bimensual</option>
                                <option value="trimestral">Trimestral</option>
                                <option value="proyecto">Proyecto</option>
                            </select>
                            <div class="invalid-feedback">Por favor seleccione el tipo de servicio.</div>
                        </div>

                        <div class="alert alert-warning mb-0" role="alert">
                            <small><i class="bi bi-exclamation-triangle-fill"></i> <strong>Importante:</strong> Se crearán nuevas actividades con estado ABIERTA, porcentaje 0% y fecha del día actual. Las actividades anteriores se mantendrán en el sistema.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-check-circle"></i> Generar Plan de Trabajo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS (with Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validación del formulario de carga
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

        // Cargar clientes al abrir el modal
        document.getElementById('renewPlanModal').addEventListener('show.bs.modal', function () {
            fetch('<?= base_url('consultant/plan/getClients') ?>')
                .then(response => response.json())
                .then(data => {
                    const clientSelect = document.getElementById('id_cliente');
                    clientSelect.innerHTML = '<option value="">Seleccione un cliente...</option>';

                    data.forEach(client => {
                        const option = document.createElement('option');
                        option.value = client.id_cliente;
                        option.textContent = client.nombre_cliente;
                        clientSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error al cargar clientes:', error);
                    alert('Error al cargar la lista de clientes');
                });
        });

        // Validación del formulario de renovación
        document.getElementById('renewPlanForm').addEventListener('submit', function (event) {
            if (!this.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            this.classList.add('was-validated');
        });
    </script>
</body>
</html>
