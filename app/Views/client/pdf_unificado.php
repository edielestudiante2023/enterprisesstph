<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generar PDF Unificado - SG-SST</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: #1c2437;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 3px solid #bd9751;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1030;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }

        .navbar-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 20px;
        }

        .navbar-content img {
            max-height: 70px;
        }

        .content-wrapper {
            margin-top: 120px;
            padding-bottom: 50px;
        }

        .main-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            padding: 2rem;
        }

        .header-section {
            background: linear-gradient(135deg, #1c2437 0%, #2c3e50 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            text-align: center;
        }

        .header-section h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .header-section p {
            opacity: 0.9;
            margin-bottom: 0;
        }

        .info-card {
            background: linear-gradient(135deg, #bd9751 0%, #d4af37 100%);
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .info-card h5 {
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .info-card ul {
            margin-bottom: 0;
            padding-left: 1.2rem;
        }

        .info-card li {
            margin-bottom: 0.5rem;
        }

        .document-list {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 2rem;
        }

        .document-item {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            border-bottom: 1px solid #e9ecef;
            transition: background 0.2s;
        }

        .document-item:last-child {
            border-bottom: none;
        }

        .document-item:hover {
            background: #f8f9fa;
        }

        .document-item .icon {
            width: 35px;
            height: 35px;
            background: linear-gradient(135deg, #bd9751 0%, #d4af37 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 1rem;
            flex-shrink: 0;
        }

        .document-item .dimension-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 15px;
            margin-left: auto;
        }

        .dimension-planear { background: #4CAF50; color: white; }
        .dimension-hacer { background: #2196F3; color: white; }
        .dimension-verificar { background: #FF9800; color: white; }
        .dimension-actuar { background: #9C27B0; color: white; }

        .btn-generate {
            background: linear-gradient(135deg, #1c2437 0%, #2c3e50 100%);
            color: white;
            font-weight: 600;
            padding: 1rem 2rem;
            border-radius: 50px;
            border: none;
            font-size: 1.1rem;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(28, 36, 55, 0.3);
        }

        .btn-generate:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(28, 36, 55, 0.4);
            color: white;
        }

        .btn-generate:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .btn-back {
            background: transparent;
            color: #1c2437;
            border: 2px solid #1c2437;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-back:hover {
            background: #1c2437;
            color: white;
        }

        .progress-container {
            display: none;
            margin-top: 2rem;
        }

        .progress-container.active {
            display: block;
        }

        .progress {
            height: 25px;
            border-radius: 15px;
            background: #e9ecef;
        }

        .progress-bar {
            background: linear-gradient(135deg, #bd9751 0%, #d4af37 100%);
            border-radius: 15px;
            transition: width 0.3s ease;
        }

        .progress-text {
            text-align: center;
            margin-top: 1rem;
            font-weight: 500;
            color: #1c2437;
        }

        .alert-warning-custom {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .stats-row {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #bd9751;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #6c757d;
        }

        footer {
            background: linear-gradient(135deg, #1c2437 0%, #2c3e50 100%);
            color: #ffffff;
            padding: 20px 0;
            margin-top: 3rem;
        }

        @media (max-width: 768px) {
            .navbar-content img {
                max-height: 50px;
            }

            .header-section h1 {
                font-size: 1.5rem;
            }

            .stats-row {
                flex-direction: column;
                gap: 1rem;
            }
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container navbar-content">
            <a href="https://dashboard.cycloidtalent.com/login" target="_blank">
                <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Logo Enterprisesst">
            </a>
            <a href="https://cycloidtalent.com/index.php/consultoria-sst" target="_blank">
                <img src="<?= base_url('uploads/logosst.png') ?>" alt="Logo SST">
            </a>
            <a href="https://cycloidtalent.com/" target="_blank">
                <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Logo Cycloid">
            </a>
        </div>
    </nav>

    <!-- Contenido principal -->
    <div class="content-wrapper">
        <div class="container">
            <div class="main-card">
                <!-- Header -->
                <div class="header-section">
                    <h1><i class="fas fa-file-pdf me-2"></i>Generador de PDF Unificado</h1>
                    <p>Descarga todos los documentos del SG-SST en un solo archivo PDF</p>
                </div>

                <!-- Alertas -->
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?= session()->getFlashdata('error') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Info del cliente -->
                <div class="alert-warning-custom">
                    <i class="fas fa-building me-2"></i>
                    <strong>Cliente:</strong> <?= esc($client['nombre_cliente']) ?>
                    <span class="ms-3"><i class="fas fa-layer-group me-2"></i><strong>Estándar:</strong> <?= esc($client['estandares']) ?></span>
                </div>

                <!-- Estadísticas -->
                <div class="stats-row">
                    <div class="stat-item">
                        <div class="stat-number"><?= $totalDocumentos ?></div>
                        <div class="stat-label">Documentos a incluir</div>
                    </div>
                </div>

                <!-- Info -->
                <div class="info-card">
                    <h5><i class="fas fa-info-circle me-2"></i>Acerca de esta funcionalidad</h5>
                    <ul>
                        <li>Se generarán todos los documentos del SG-SST disponibles para su estándar.</li>
                        <li>Los documentos se fusionarán en un solo archivo PDF para facilitar su descarga.</li>
                        <li>El proceso puede tomar varios minutos dependiendo de la cantidad de documentos.</li>
                        <li>No cierre esta ventana mientras se genera el PDF.</li>
                    </ul>
                </div>

                <!-- Lista de documentos -->
                <h5 class="mb-3"><i class="fas fa-list me-2"></i>Documentos que se incluirán:</h5>
                <div class="document-list">
                    <?php
                    $currentDimension = '';
                    foreach ($accesos as $acceso):
                        if ($currentDimension !== $acceso['dimension']):
                            $currentDimension = $acceso['dimension'];
                    ?>
                        <div class="document-item" style="background: #f8f9fa; font-weight: 600;">
                            <div class="icon" style="background: #1c2437;">
                                <i class="fas fa-folder"></i>
                            </div>
                            <span><?= esc($currentDimension) ?></span>
                        </div>
                    <?php endif; ?>
                        <div class="document-item">
                            <div class="icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <span><?= esc($acceso['nombre']) ?></span>
                            <span class="dimension-badge dimension-<?= strtolower($acceso['dimension']) ?>">
                                <?= esc($acceso['dimension']) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Barra de progreso -->
                <div class="progress-container" id="progressContainer">
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: 0%;" id="progressBar">0%</div>
                    </div>
                    <div class="progress-text" id="progressText">Preparando documentos...</div>
                </div>

                <!-- Botones -->
                <div class="text-center mt-4">
                    <a href="<?= base_url('/dashboard') ?>" class="btn btn-back me-3">
                        <i class="fas fa-arrow-left me-2"></i>Volver al Dashboard
                    </a>
                    <form action="<?= base_url('/generarPdfUnificado') ?>" method="post" id="formGenerarPdf" style="display: inline;">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-generate" id="btnGenerar">
                            <i class="fas fa-download me-2"></i>Generar y Descargar PDF Unificado
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center">
        <p>&copy; 2024 Cycloid Talent SAS. Todos los derechos reservados.</p>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('formGenerarPdf').addEventListener('submit', function(e) {
            e.preventDefault();

            const btn = document.getElementById('btnGenerar');
            const progressContainer = document.getElementById('progressContainer');
            const progressBar = document.getElementById('progressBar');
            const progressText = document.getElementById('progressText');
            const form = this;

            // Deshabilitar botón y mostrar progreso
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generando PDF...';
            progressContainer.classList.add('active');

            // Simular progreso
            let progress = 0;
            const interval = setInterval(function() {
                progress += Math.random() * 3;
                if (progress > 95) progress = 95;

                progressBar.style.width = progress + '%';
                progressBar.textContent = Math.round(progress) + '%';
                progressText.textContent = 'Generando documentos... Por favor espere.';
            }, 500);

            // Crear un token único para detectar la descarga
            const downloadToken = Date.now();
            document.cookie = 'downloadToken=' + downloadToken + '; path=/';

            // Crear un iframe oculto para la descarga
            let iframe = document.createElement('iframe');
            iframe.style.display = 'none';
            iframe.name = 'downloadFrame';
            document.body.appendChild(iframe);

            // Modificar el form para usar el iframe
            form.target = 'downloadFrame';
            form.submit();

            // Verificar periódicamente si la descarga comenzó
            let checkCount = 0;
            const maxChecks = 120; // 2 minutos máximo
            const checkDownload = setInterval(function() {
                checkCount++;

                // Después de un tiempo razonable, asumir que la descarga se completó
                if (checkCount >= 40) { // ~20 segundos
                    clearInterval(interval);
                    clearInterval(checkDownload);

                    // Mostrar éxito
                    progressBar.style.width = '100%';
                    progressBar.textContent = '100%';
                    progressBar.classList.remove('bg-warning');
                    progressBar.classList.add('bg-success');
                    progressText.innerHTML = '<i class="fas fa-check-circle text-success me-2"></i>PDF generado exitosamente. La descarga debería haber comenzado.';

                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-download me-2"></i>Generar y Descargar PDF Unificado';

                    // Limpiar iframe después de un momento
                    setTimeout(function() {
                        if (iframe.parentNode) {
                            iframe.parentNode.removeChild(iframe);
                        }
                    }, 5000);
                }

                if (checkCount >= maxChecks) {
                    clearInterval(checkDownload);
                    clearInterval(interval);
                }
            }, 500);
        });
    </script>
</body>

</html>
