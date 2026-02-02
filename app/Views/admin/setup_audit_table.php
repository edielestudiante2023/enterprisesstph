<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración de Tabla de Auditoría - PTA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-dark: #1c2437;
            --gold-primary: #bd9751;
            --gold-secondary: #d4af37;
        }
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 40px 0;
        }
        .setup-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            padding: 40px;
            max-width: 800px;
            margin: 0 auto;
        }
        .setup-header {
            background: linear-gradient(135deg, var(--primary-dark) 0%, #2c3e50 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
        }
        .btn-setup {
            padding: 20px 40px;
            font-size: 1.1rem;
            border-radius: 15px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-setup:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .btn-local {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
        }
        .btn-production {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border: none;
            color: white;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .status-exists {
            background: #d4edda;
            color: #155724;
        }
        .status-missing {
            background: #f8d7da;
            color: #721c24;
        }
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .result-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            display: none;
        }
        .result-box.success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
        }
        .result-box.error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="setup-card">
            <div class="setup-header">
                <i class="fas fa-database fa-3x mb-3"></i>
                <h2 class="mb-2">Configuración de Tabla de Auditoría</h2>
                <p class="mb-0">Plan de Trabajo Anual (PTA) - Sistema de Auditoría</p>
            </div>

            <div class="info-box">
                <h5><i class="fas fa-info-circle me-2"></i>Información</h5>
                <p class="mb-2">Este asistente creará la tabla <code>tbl_pta_cliente_audit</code> para registrar todos los cambios realizados en el Plan de Trabajo Anual.</p>
                <p class="mb-0"><strong>Campos que se registrarán:</strong> Usuario, acción (INSERT/UPDATE/DELETE), campo modificado, valor anterior, valor nuevo, fecha, IP, navegador.</p>
            </div>

            <!-- Estado actual -->
            <div class="mb-4">
                <h5><i class="fas fa-check-circle me-2"></i>Estado Actual de las Tablas</h5>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <h6 class="card-title"><i class="fas fa-laptop me-2"></i>Base de Datos LOCAL</h6>
                                <div id="statusLocal" class="mt-3">
                                    <span class="status-badge" style="background: #fff3cd; color: #856404;">
                                        <i class="fas fa-spinner fa-spin me-2"></i>Verificando...
                                    </span>
                                </div>
                                <div id="countLocal" class="mt-2 small text-muted"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <h6 class="card-title"><i class="fas fa-cloud me-2"></i>Base de Datos PRODUCCIÓN</h6>
                                <div id="statusProduction" class="mt-3">
                                    <span class="status-badge" style="background: #fff3cd; color: #856404;">
                                        <i class="fas fa-spinner fa-spin me-2"></i>Verificando...
                                    </span>
                                </div>
                                <div id="countProduction" class="mt-2 small text-muted"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <button type="button" class="btn btn-setup btn-local w-100" id="btnCreateLocal">
                        <i class="fas fa-laptop me-2"></i>
                        Crear Tabla en LOCAL
                    </button>
                </div>
                <div class="col-md-6">
                    <button type="button" class="btn btn-setup btn-production w-100" id="btnCreateProduction">
                        <i class="fas fa-cloud me-2"></i>
                        Crear Tabla en PRODUCCIÓN
                    </button>
                </div>
            </div>

            <!-- Resultado -->
            <div id="resultBox" class="result-box">
                <h5 id="resultTitle"></h5>
                <p id="resultMessage" class="mb-0"></p>
            </div>

            <!-- Botón volver -->
            <div class="text-center mt-4">
                <a href="<?= base_url('/admindashboard') ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Volver al Dashboard
                </a>
                <button type="button" class="btn btn-outline-primary ms-2" onclick="checkStatus()">
                    <i class="fas fa-sync-alt me-2"></i>Actualizar Estado
                </button>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function checkStatus() {
            $('#statusLocal, #statusProduction').html('<span class="status-badge" style="background: #fff3cd; color: #856404;"><i class="fas fa-spinner fa-spin me-2"></i>Verificando...</span>');
            $('#countLocal, #countProduction').empty();

            $.get('<?= base_url('/setup-audit-table/check-status') ?>', function(response) {
                if (response.success) {
                    // Local
                    if (response.status.local) {
                        $('#statusLocal').html('<span class="status-badge status-exists"><i class="fas fa-check-circle me-2"></i>Tabla Existe</span>');
                        $('#countLocal').html('<strong>' + response.status.local_count + '</strong> registros de auditoría');
                    } else {
                        $('#statusLocal').html('<span class="status-badge status-missing"><i class="fas fa-times-circle me-2"></i>No Existe</span>');
                    }

                    // Producción
                    if (response.status.production) {
                        $('#statusProduction').html('<span class="status-badge status-exists"><i class="fas fa-check-circle me-2"></i>Tabla Existe</span>');
                        $('#countProduction').html('<strong>' + response.status.production_count + '</strong> registros de auditoría');
                    } else {
                        $('#statusProduction').html('<span class="status-badge status-missing"><i class="fas fa-times-circle me-2"></i>No Existe</span>');
                        if (response.status.production_error) {
                            $('#countProduction').html('<small class="text-danger">' + response.status.production_error + '</small>');
                        }
                    }
                }
            }).fail(function(xhr) {
                $('#statusLocal, #statusProduction').html('<span class="status-badge status-missing"><i class="fas fa-exclamation-triangle me-2"></i>Error</span>');
            });
        }

        function showResult(success, title, message) {
            var $box = $('#resultBox');
            $box.removeClass('success error').addClass(success ? 'success' : 'error');
            $('#resultTitle').html('<i class="fas fa-' + (success ? 'check-circle text-success' : 'times-circle text-danger') + ' me-2"></i>' + title);
            $('#resultMessage').text(message);
            $box.slideDown();
        }

        $(document).ready(function() {
            checkStatus();

            $('#btnCreateLocal').click(function() {
                var $btn = $(this);
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Creando...');

                $.post('<?= base_url('/setup-audit-table/create-local') ?>', function(response) {
                    showResult(response.success, response.success ? 'Éxito' : 'Error', response.message + (response.details ? ' - ' + response.details : ''));
                    checkStatus();
                }).fail(function(xhr) {
                    showResult(false, 'Error', 'Error de conexión: ' + xhr.statusText);
                }).always(function() {
                    $btn.prop('disabled', false).html('<i class="fas fa-laptop me-2"></i>Crear Tabla en LOCAL');
                });
            });

            $('#btnCreateProduction').click(function() {
                if (!confirm('¿Está seguro de crear la tabla en PRODUCCIÓN?\n\nEsta acción es segura pero verifique que sea necesario.')) {
                    return;
                }

                var $btn = $(this);
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Creando...');

                $.post('<?= base_url('/setup-audit-table/create-production') ?>', function(response) {
                    showResult(response.success, response.success ? 'Éxito' : 'Error', response.message + (response.details ? ' - ' + response.details : ''));
                    checkStatus();
                }).fail(function(xhr) {
                    showResult(false, 'Error', 'Error de conexión: ' + xhr.statusText);
                }).always(function() {
                    $btn.prop('disabled', false).html('<i class="fas fa-cloud me-2"></i>Crear Tabla en PRODUCCIÓN');
                });
            });
        });
    </script>
</body>
</html>
