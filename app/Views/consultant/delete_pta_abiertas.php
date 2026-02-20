<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar PTA Abiertas</title>
    <link rel="icon" href="<?= base_url('favicon.ico') ?>" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-dark: #1c2437;
            --secondary-dark: #2c3e50;
            --gold-primary: #bd9751;
            --gold-secondary: #d4af37;
            --white-primary: #ffffff;
            --white-secondary: #f8f9fa;
            --gradient-bg: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            --warning-orange: #f39c12;
            --warning-orange-hover: #e67e22;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: var(--gradient-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--primary-dark);
            min-height: 100vh;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: var(--gradient-bg);
            z-index: -1;
        }

        .navbar-custom {
            background: #fff;
            backdrop-filter: blur(20px);
            box-shadow: 0 8px 32px rgba(28, 36, 55, 0.3);
            padding: 20px 0;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            border-bottom: 2px solid var(--gold-primary);
        }

        .header-logos-custom img {
            max-height: 70px;
            margin-right: 20px;
            filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.3));
            transition: all 0.3s ease;
        }

        .header-logos-custom img:hover {
            transform: translateY(-3px) scale(1.05);
            filter: drop-shadow(0 8px 20px rgba(189, 151, 81, 0.4));
        }

        .content-wrapper-custom {
            margin-top: 120px;
            min-height: calc(100vh - 200px);
        }

        .card-main {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(28, 36, 55, 0.15);
            padding: 40px;
            max-width: 700px;
            margin: 0 auto;
        }

        .card-main h2 {
            color: var(--primary-dark);
            font-weight: 700;
            margin-bottom: 10px;
        }

        .card-main .subtitle {
            color: #6c757d;
            margin-bottom: 30px;
        }

        .btn-delete {
            background: linear-gradient(135deg, var(--warning-orange), var(--warning-orange-hover));
            border: none;
            color: white;
            font-weight: 600;
            padding: 12px 30px;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(243, 156, 18, 0.3);
        }

        .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(243, 156, 18, 0.5);
            color: white;
        }

        .btn-delete:disabled {
            opacity: 0.6;
            transform: none;
            cursor: not-allowed;
        }

        .btn-back {
            background: linear-gradient(135deg, var(--primary-dark), var(--secondary-dark));
            border: none;
            color: white;
            font-weight: 600;
            padding: 12px 30px;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(28, 36, 55, 0.4);
            color: white;
        }

        .footer-custom {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--secondary-dark) 100%);
            color: var(--white-primary);
            padding: 30px 0;
            margin-top: 60px;
            position: relative;
            overflow: hidden;
        }

        .footer-custom::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 3px;
            background: linear-gradient(90deg, var(--gold-primary), var(--gold-secondary), var(--gold-primary));
        }

        .footer-custom a {
            color: var(--gold-secondary);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-custom a:hover {
            color: var(--white-primary);
        }
    </style>
</head>

<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-custom">
            <div class="container-fluid">
                <div class="header-logos-custom d-flex justify-content-between align-items-center w-100">
                    <div>
                        <a href="https://dashboard.cycloidtalent.com/login" target="_blank" rel="noopener noreferrer">
                            <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo">
                        </a>
                    </div>
                    <div>
                        <a href="https://cycloidtalent.com/index.php/consultoria-sst" target="_blank" rel="noopener noreferrer">
                            <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST Logo">
                        </a>
                    </div>
                    <div>
                        <a href="https://cycloidtalent.com/" target="_blank" rel="noopener noreferrer">
                            <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloids Logo">
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <div class="content-wrapper-custom">
        <main class="container-fluid content">
            <div class="card-main">
                <div class="text-center mb-4">
                    <i class="fas fa-trash-alt fa-3x" style="color: var(--warning-orange);"></i>
                </div>
                <h2 class="text-center">Eliminar Actividades ABIERTAS del PTA</h2>
                <p class="subtitle text-center">Seleccione un cliente para eliminar todas las actividades en estado <strong>ABIERTA</strong> de su Plan de Trabajo.</p>

                <div class="mb-4">
                    <label for="id_cliente" class="form-label fw-bold">
                        <i class="fas fa-building me-1"></i> Cliente
                    </label>
                    <select id="id_cliente" class="form-select" style="width: 100%;">
                        <option value="">Buscar un cliente...</option>
                        <?php foreach ($clients as $client): ?>
                            <option value="<?= esc($client['id_cliente']) ?>">
                                <?= esc($client['nombre_cliente']) ?> - NIT: <?= esc($client['nit_cliente']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="d-flex justify-content-center gap-3">
                    <a href="<?= base_url('/admindashboard') ?>" class="btn btn-back">
                        <i class="fas fa-arrow-left me-2"></i>Volver al Dashboard
                    </a>
                    <button type="button" id="btnEliminar" class="btn btn-delete" disabled>
                        <i class="fas fa-trash-alt me-2"></i>Eliminar Actividades ABIERTAS
                    </button>
                </div>
            </div>
        </main>
    </div>

    <footer class="footer-custom">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <p class="fw-bold mb-2 fs-5">
                        <i class="fas fa-building me-2"></i>Cycloid Talent SAS
                    </p>
                    <p class="mb-2">Todos los derechos reservados &copy; <span id="currentYear"></span></p>
                    <p class="mb-2"><i class="fas fa-id-card me-2"></i>NIT: 901.653.912</p>
                    <p class="mb-0">
                        <i class="fas fa-globe me-2"></i>Sitio oficial:
                        <a href="https://cycloidtalent.com/" target="_blank" rel="noopener noreferrer">https://cycloidtalent.com/</a>
                    </p>
                </div>
                <div class="col-md-4">
                    <div class="mt-3">
                        <strong><i class="fas fa-share-alt me-2"></i>Nuestras Redes Sociales:</strong>
                        <div class="d-flex justify-content-center gap-3 mt-3">
                            <a href="https://www.facebook.com/CycloidTalent" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                                <img src="https://cdn-icons-png.flaticon.com/512/733/733547.png" alt="Facebook" style="height: 32px; width: 32px;">
                            </a>
                            <a href="https://co.linkedin.com/company/cycloid-talent" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn">
                                <img src="https://cdn-icons-png.flaticon.com/512/733/733561.png" alt="LinkedIn" style="height: 32px; width: 32px;">
                            </a>
                            <a href="https://www.instagram.com/cycloid_talent?igsh=Nmo4d2QwZDg5dHh0" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                                <img src="https://cdn-icons-png.flaticon.com/512/733/733558.png" alt="Instagram" style="height: 32px; width: 32px;">
                            </a>
                            <a href="https://www.tiktok.com/@cycloid_talent?_t=8qBSOu0o1ZN&_r=1" target="_blank" rel="noopener noreferrer" aria-label="TikTok">
                                <img src="https://cdn-icons-png.flaticon.com/512/3046/3046126.png" alt="TikTok" style="height: 32px; width: 32px;">
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Select2
            $('#id_cliente').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Buscar un cliente...',
                allowClear: true,
                language: {
                    noResults: function() {
                        return "No se encontraron resultados";
                    }
                }
            });

            // Habilitar/deshabilitar botón según selección
            $('#id_cliente').on('change', function() {
                $('#btnEliminar').prop('disabled', !$(this).val());
            });

            // Botón eliminar
            $('#btnEliminar').on('click', function() {
                var idCliente = $('#id_cliente').val();
                var nombreCliente = $('#id_cliente option:selected').text().trim();

                if (!idCliente) return;

                // Obtener conteo antes de confirmar
                $.ajax({
                    url: '<?= base_url("/admin/count-pta-abiertas") ?>',
                    type: 'POST',
                    data: {
                        id_cliente: idCliente,
                        '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.count === 0) {
                            Swal.fire({
                                icon: 'info',
                                title: 'Sin actividades',
                                text: 'El cliente "' + nombreCliente + '" no tiene actividades en estado ABIERTA.',
                                confirmButtonColor: '#1c2437'
                            });
                            return;
                        }

                        Swal.fire({
                            title: 'Confirmar eliminacion',
                            html: 'Se eliminarán <strong>' + response.count + '</strong> actividad(es) en estado <strong>ABIERTA</strong> del cliente:<br><br><strong>' + nombreCliente + '</strong><br><br><span style="color: #e74c3c;">Esta acción no se puede deshacer.</span>',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#f39c12',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Si, eliminar',
                            cancelButtonText: 'Cancelar'
                        }).then(function(result) {
                            if (result.isConfirmed) {
                                // Ejecutar eliminación
                                $.ajax({
                                    url: '<?= base_url("/admin/delete-pta-abiertas") ?>',
                                    type: 'POST',
                                    data: {
                                        id_cliente: idCliente,
                                        '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                                    },
                                    dataType: 'json',
                                    success: function(res) {
                                        if (res.success) {
                                            Swal.fire({
                                                icon: 'success',
                                                title: 'Eliminacion exitosa',
                                                text: res.message,
                                                confirmButtonColor: '#1c2437'
                                            }).then(function() {
                                                // Resetear select
                                                $('#id_cliente').val('').trigger('change');
                                            });
                                        } else {
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Error',
                                                text: res.message,
                                                confirmButtonColor: '#e74c3c'
                                            });
                                        }
                                    },
                                    error: function() {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: 'Ocurrió un error al procesar la solicitud.',
                                            confirmButtonColor: '#e74c3c'
                                        });
                                    }
                                });
                            }
                        });
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo obtener la información del cliente.',
                            confirmButtonColor: '#e74c3c'
                        });
                    }
                });
            });

            // Año actual en footer
            document.getElementById('currentYear').textContent = new Date().getFullYear();
        });
    </script>
</body>

</html>
