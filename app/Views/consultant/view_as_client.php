<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ver Vista de Cliente - Enterprisesst PH</title>
    <link rel="icon" href="<?= base_url('favicon.ico') ?>" type="image/x-icon">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Select2 CSS -->
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
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: var(--gradient-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--primary-dark);
            line-height: 1.6;
            min-height: 100vh;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Navbar */
        .navbar-custom {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--secondary-dark) 100%);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            padding: 20px 0;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        .header-logos-custom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }

        .header-logos-custom img {
            max-height: 70px;
            transition: all 0.3s ease;
            filter: brightness(1.1);
        }

        .header-logos-custom img:hover {
            transform: translateY(-3px) scale(1.05);
            filter: brightness(1.3);
        }

        /* Content */
        .content-wrapper {
            margin-top: 130px;
            padding: 0 15px 60px;
        }

        /* Banner */
        .welcome-banner {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--secondary-dark) 50%, var(--gold-primary) 100%);
            padding: 40px 30px;
            border-radius: 25px;
            text-align: center;
            color: var(--white-primary);
            box-shadow: 0 20px 60px rgba(28, 36, 55, 0.4);
            margin-bottom: 40px;
            animation: fadeInUp 0.8s ease;
        }

        .welcome-banner h3 {
            font-size: 2.2rem;
            margin-bottom: 10px;
            font-weight: 700;
            background: linear-gradient(45deg, var(--white-primary), var(--gold-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .welcome-banner p {
            font-size: 1.2rem;
            color: var(--white-secondary);
        }

        /* Card del selector */
        .selector-card {
            background: var(--white-primary);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(28, 36, 55, 0.15);
            padding: 40px;
            margin-bottom: 40px;
            border: 1px solid rgba(189, 151, 81, 0.2);
            animation: fadeInUp 0.8s ease 0.2s both;
        }

        .selector-card h4 {
            color: var(--primary-dark);
            font-weight: 700;
            margin-bottom: 25px;
        }

        .selector-card .icon-header {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--gold-primary), var(--gold-secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 1.5rem;
        }

        /* Select2 custom */
        .select2-container--bootstrap-5 .select2-selection {
            border: 2px solid rgba(189, 151, 81, 0.4);
            border-radius: 12px;
            padding: 10px 15px;
            min-height: 50px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .select2-container--bootstrap-5 .select2-selection:focus,
        .select2-container--bootstrap-5.select2-container--focus .select2-selection {
            border-color: var(--gold-primary);
            box-shadow: 0 0 15px rgba(189, 151, 81, 0.3);
        }

        .select2-container--bootstrap-5 .select2-results__option--highlighted {
            background: linear-gradient(135deg, var(--gold-primary), var(--gold-secondary)) !important;
        }

        .select2-container--bootstrap-5 .select2-dropdown {
            border: 2px solid var(--gold-primary);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        /* Botón de acceder */
        .btn-view-client {
            background: linear-gradient(135deg, var(--gold-primary), var(--gold-secondary));
            border: none;
            color: var(--white-primary);
            border-radius: 25px;
            padding: 15px 50px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-view-client::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s ease;
        }

        .btn-view-client:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(189, 151, 81, 0.4);
            color: var(--white-primary);
        }

        .btn-view-client:hover::before {
            left: 100%;
        }

        .btn-view-client:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        /* Botón volver */
        .btn-back {
            background: linear-gradient(135deg, var(--primary-dark), var(--secondary-dark));
            border: none;
            color: var(--white-primary);
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(28, 36, 55, 0.3);
            color: var(--white-primary);
        }

        /* Info del cliente seleccionado */
        .client-info-card {
            display: none;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 15px;
            padding: 25px;
            margin-top: 25px;
            border-left: 5px solid var(--gold-primary);
            animation: fadeInUp 0.5s ease;
        }

        .client-info-card.show {
            display: block;
        }

        .client-info-card .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .client-info-card .info-item i {
            color: var(--gold-primary);
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        /* Footer */
        .footer-custom {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--secondary-dark) 100%);
            color: var(--white-primary);
            padding: 30px 0;
            text-align: center;
            margin-top: 40px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-logos-custom {
                flex-direction: column;
                gap: 15px;
            }

            .header-logos-custom img {
                max-height: 50px;
            }

            .content-wrapper {
                margin-top: 180px;
            }

            .welcome-banner h3 {
                font-size: 1.6rem;
            }

            .selector-card {
                padding: 25px;
            }
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <header>
        <nav class="navbar navbar-expand-lg navbar-custom">
            <div class="container">
                <div class="header-logos-custom">
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

    <!-- Contenido principal -->
    <main class="content-wrapper">
        <div class="container">
            <!-- Banner -->
            <div class="welcome-banner">
                <h3><i class="fas fa-eye me-3"></i>Ver Vista de Cliente</h3>
                <p><i class="fas fa-user me-2"></i><?= esc($nombreUsuario) ?> - <?= $role === 'admin' ? 'Administrador' : 'Consultor' ?></p>
                <p class="mb-0"><small>Selecciona un cliente para ver su dashboard como si fueras ese cliente</small></p>
            </div>

            <!-- Botón Volver -->
            <div class="mb-4">
                <a href="<?= base_url($role === 'admin' ? '/admin/dashboard' : '/consultor/dashboard') ?>" class="btn btn-back">
                    <i class="fas fa-arrow-left me-2"></i>Volver al Dashboard
                </a>
            </div>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?= session()->getFlashdata('error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            <?php endif; ?>

            <!-- Card del Selector -->
            <div class="selector-card">
                <div class="text-center">
                    <div class="icon-header">
                        <i class="fas fa-users"></i>
                    </div>
                    <h4><i class="fas fa-search me-2"></i>Seleccionar Cliente</h4>
                    <p class="text-muted mb-4">Busca y selecciona un cliente para acceder a su vista completa del dashboard</p>
                </div>

                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="mb-4">
                            <label for="selectCliente" class="form-label fw-bold">
                                <i class="fas fa-building me-2" style="color: var(--gold-primary);"></i>Cliente
                            </label>
                            <select id="selectCliente" class="form-select" style="width: 100%;">
                                <option value="">Buscar cliente por nombre o NIT...</option>
                                <?php foreach ($clients as $client): ?>
                                    <option value="<?= esc($client['id_cliente']) ?>"
                                            data-nit="<?= esc($client['nit_cliente']) ?>"
                                            data-ciudad="<?= esc($client['ciudad_cliente']) ?>"
                                            data-estado="<?= esc($client['estado']) ?>"
                                            data-estandar="<?= esc($client['estandares']) ?>">
                                        <?= esc($client['nombre_cliente']) ?> - NIT: <?= esc($client['nit_cliente']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Info del cliente seleccionado -->
                        <div id="clientInfoCard" class="client-info-card">
                            <h5 class="fw-bold mb-3"><i class="fas fa-info-circle me-2" style="color: var(--gold-primary);"></i>Información del Cliente</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <i class="fas fa-building"></i>
                                        <span><strong>Nombre:</strong> <span id="infoNombre"></span></span>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-id-card"></i>
                                        <span><strong>NIT:</strong> <span id="infoNit"></span></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><strong>Ciudad:</strong> <span id="infoCiudad"></span></span>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-layer-group"></i>
                                        <span><strong>Estándar:</strong> <span id="infoEstandar"></span></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botón de acceder -->
                        <div class="text-center mt-4">
                            <button id="btnViewClient" class="btn btn-view-client" disabled>
                                <i class="fas fa-eye me-2"></i>Ver Dashboard del Cliente
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer-custom">
        <p class="mb-0">&copy; <span id="currentYear"></span> Cycloid Talent SAS. Todos los derechos reservados.</p>
    </footer>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inicializar Select2
            $('#selectCliente').select2({
                theme: 'bootstrap-5',
                placeholder: 'Buscar cliente por nombre o NIT...',
                allowClear: true,
                width: '100%',
                language: {
                    noResults: function() {
                        return "No se encontraron clientes";
                    },
                    searching: function() {
                        return "Buscando...";
                    }
                }
            });

            // Al seleccionar un cliente
            $('#selectCliente').on('change', function() {
                var clienteId = $(this).val();
                var $btn = $('#btnViewClient');
                var $infoCard = $('#clientInfoCard');

                if (clienteId) {
                    var $selected = $(this).find('option:selected');
                    var nombre = $selected.text().split(' - NIT:')[0].trim();
                    var nit = $selected.data('nit');
                    var ciudad = $selected.data('ciudad') || 'No especificada';
                    var estandar = $selected.data('estandar') || 'No definido';

                    // Mostrar info del cliente
                    $('#infoNombre').text(nombre);
                    $('#infoNit').text(nit);
                    $('#infoCiudad').text(ciudad);
                    $('#infoEstandar').text(estandar);
                    $infoCard.addClass('show');

                    // Habilitar botón
                    $btn.prop('disabled', false);
                } else {
                    $infoCard.removeClass('show');
                    $btn.prop('disabled', true);
                }
            });

            // Abrir vista del cliente en nueva pestaña
            $('#btnViewClient').on('click', function() {
                var clienteId = $('#selectCliente').val();
                if (clienteId) {
                    window.open('<?= base_url('/vista-cliente/') ?>' + clienteId, '_blank');
                }
            });

            // Año actual en footer
            $('#currentYear').text(new Date().getFullYear());
        });
    </script>
</body>

</html>
