<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Snapshot Datos - Enterprise SST</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar-fixed {
            background: white;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            padding: 10px 0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border-bottom: 2px solid #bd9751;
        }

        .page-header {
            background: linear-gradient(135deg, #1c2437, #2c3e50);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
        }

        .page-header h2 {
            margin: 0;
            font-weight: 700;
        }

        .page-header p {
            margin: 10px 0 0;
            opacity: 0.8;
        }

        .snapshot-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .btn-snapshot {
            background: linear-gradient(135deg, #e44d26, #f16529);
            border: none;
            color: white;
            padding: 15px 40px;
            font-size: 1.2rem;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .btn-snapshot:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(228, 77, 38, 0.4);
            color: white;
        }

        .btn-snapshot:disabled {
            opacity: 0.6;
            transform: none;
        }

        .info-item {
            padding: 15px;
            border-left: 4px solid #bd9751;
            background: #f8f9fa;
            border-radius: 0 8px 8px 0;
            margin-bottom: 15px;
        }

        .result-card {
            display: none;
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            border: 1px solid #28a745;
            border-radius: 15px;
            padding: 25px;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar-fixed">
        <div class="d-flex justify-content-between align-items-center px-4" style="max-width: 1200px; margin: 0 auto;">
            <a href="<?= base_url('/admin/dashboard') ?>">
                <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Logo" style="height: 60px;">
            </a>
            <div>
                <a href="<?= base_url('/admin/dashboard') ?>" class="btn btn-primary btn-sm me-2">
                    <i class="fas fa-home me-1"></i> Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div style="height: 100px;"></div>

    <div class="container" style="max-width: 900px;">
        <!-- Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2><i class="fas fa-camera me-2"></i>Snapshot de Datos</h2>
                    <p>Captura el estado actual de los datos para el seguimiento evolutivo</p>
                    <p class="mb-0"><i class="fas fa-user me-2"></i>Admin: <strong><?= session()->get('nombre_usuario') ?? 'Usuario' ?></strong></p>
                </div>
                <div class="col-md-4 text-end">
                    <i class="fas fa-database" style="font-size: 4rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>

        <!-- Explicación -->
        <div class="snapshot-card">
            <h4 class="mb-4"><i class="fas fa-info-circle me-2 text-primary"></i>Que hace este proceso?</h4>

            <div class="info-item">
                <strong><i class="fas fa-chart-bar me-2"></i>Snapshot Estandares Minimos</strong>
                <p class="mb-0 mt-1 text-muted">Copia los datos actuales de <code>resumen_estandares_cliente</code> hacia <code>historial_resumen_estandares</code> con la fecha actual.</p>
            </div>

            <div class="info-item">
                <strong><i class="fas fa-tasks me-2"></i>Snapshot Plan de Trabajo</strong>
                <p class="mb-0 mt-1 text-muted">Copia los datos actuales de <code>resumen_mensual_plan_trabajo</code> hacia <code>historial_resumen_plan_trabajo</code> con la fecha actual.</p>
            </div>

            <div class="alert alert-warning mt-3 mb-0">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Nota:</strong> Si ya existe un registro para el mismo cliente en la misma fecha, se actualizara en lugar de duplicarse (UPSERT).
            </div>
        </div>

        <!-- Botón de acción -->
        <div class="snapshot-card text-center">
            <button id="btnSnapshot" class="btn btn-snapshot">
                <i class="fas fa-camera me-2"></i>Tomar Snapshot Ahora
            </button>

            <!-- Resultado -->
            <div id="resultCard" class="result-card text-start">
                <h5 class="text-success"><i class="fas fa-check-circle me-2"></i>Snapshot Completado</h5>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Estandares Minimos:</strong> <span id="resEstandares">-</span> filas afectadas</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Plan de Trabajo:</strong> <span id="resPlanTrabajo">-</span> filas afectadas</p>
                    </div>
                </div>
                <p class="text-muted mb-0"><i class="fas fa-clock me-2"></i>Ejecutado: <span id="resTimestamp">-</span></p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer style="background: white; padding: 20px 0; border-top: 1px solid #dee2e6; margin-top: 40px; text-align: center; font-size: 14px; color: #6c757d;">
        <p class="mb-0"><strong>Cycloid Talent SAS</strong> - Todos los derechos reservados &copy; <?= date('Y') ?></p>
    </footer>

    <script>
        document.getElementById('btnSnapshot').addEventListener('click', function () {
            const btn = this;

            Swal.fire({
                title: 'Confirmar Snapshot',
                html: 'Se tomara una captura de los datos actuales de:<br><br>' +
                    '<strong>1.</strong> Estandares Minimos<br>' +
                    '<strong>2.</strong> Plan de Trabajo<br><br>' +
                    '<small class="text-muted">Los datos se guardaran en las tablas de historial.</small>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#e44d26',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-camera"></i> Ejecutar Snapshot',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Ejecutando...';

                    fetch('<?= base_url('/admin/snapshots/ejecutar') ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-camera me-2"></i>Tomar Snapshot Ahora';

                        if (data.success) {
                            document.getElementById('resEstandares').textContent = data.estandares_rows;
                            document.getElementById('resPlanTrabajo').textContent = data.plan_trabajo_rows;
                            document.getElementById('resTimestamp').textContent = data.timestamp;
                            document.getElementById('resultCard').style.display = 'block';

                            Swal.fire({
                                title: 'Snapshot Exitoso',
                                html: '<strong>Estandares:</strong> ' + data.estandares_rows + ' filas<br>' +
                                    '<strong>Plan de Trabajo:</strong> ' + data.plan_trabajo_rows + ' filas<br><br>' +
                                    '<small>' + data.timestamp + '</small>',
                                icon: 'success',
                                confirmButtonColor: '#28a745'
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: data.error || 'Error desconocido al ejecutar el snapshot.',
                                icon: 'error'
                            });
                        }
                    })
                    .catch(err => {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-camera me-2"></i>Tomar Snapshot Ahora';

                        Swal.fire({
                            title: 'Error de conexion',
                            text: 'No se pudo conectar con el servidor: ' + err.message,
                            icon: 'error'
                        });
                    });
                }
            });
        });
    </script>
</body>

</html>
