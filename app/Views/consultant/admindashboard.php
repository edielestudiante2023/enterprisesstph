<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Administración PH</title>
    <link rel="icon" href="<?= base_url('favicon.ico') ?>" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-dark: #1c2437;
            --secondary-dark: #2c3e50;
            --gold-primary: #bd9751;
            --gold-secondary: #d4af37;
            --white-primary: #ffffff;
            --white-secondary: #f8f9fa;
            --gradient-bg: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            --shadow-deep: 0 10px 30px rgba(0, 0, 0, 0.3);
            --shadow-medium: 0 5px 20px rgba(0, 0, 0, 0.15);
            --shadow-light: 0 2px 10px rgba(0, 0, 0, 0.1);
            --border-radius: 12px;
            --border-radius-large: 25px;
            --transition: all 0.3s ease;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: var(--gradient-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--primary-dark);
            line-height: 1.6;
            overflow-x: hidden;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes shimmer {
            0% { background-position: -200% center; }
            100% { background-position: 200% center; }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        /* Navbar */
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

        .header-logos-custom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }

        .header-logos-custom img {
            max-height: 70px;
            margin-right: 15px;
            transition: var(--transition);
            filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.3));
        }

        .header-logos-custom img:hover {
            transform: translateY(-3px) scale(1.05);
            filter: drop-shadow(0 8px 20px rgba(189, 151, 81, 0.4));
        }

        /* Content */
        .content-wrapper-custom {
            margin-top: 120px;
            padding: 0 15px;
            animation: fadeInUp 0.8s ease;
        }

        /* Banner de bienvenida */
        .welcome-banner-custom {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--secondary-dark) 50%, var(--gold-primary) 100%);
            padding: 40px 30px;
            border-radius: var(--border-radius-large);
            text-align: center;
            color: var(--white-primary);
            box-shadow: var(--shadow-deep);
            margin-bottom: 30px;
            animation: fadeInUp 0.8s ease 0.2s both;
        }

        .welcome-banner-custom::before {
            content: "";
            position: absolute;
            top: -50%; left: -50%;
            width: 200%; height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: float 6s ease-in-out infinite;
            z-index: 1;
        }

        .welcome-banner-custom::after {
            content: "";
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(189,151,81,0.1) 50%, transparent 70%);
            animation: shimmer 3s ease-in-out infinite;
            z-index: 1;
        }

        .welcome-banner-custom .content-custom { position: relative; z-index: 2; }

        .welcome-banner-custom h3 {
            font-size: 2.5rem;
            margin-bottom: 15px;
            font-weight: 700;
            background: linear-gradient(135deg, var(--white-primary), var(--gold-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .welcome-banner-custom h4 {
            font-size: 1.8rem;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--white-secondary);
        }

        .welcome-banner-custom p {
            font-size: 1.4rem;
            font-weight: 500;
            color: var(--white-primary);
        }

        /* Buscador */
        .search-container {
            max-width: 500px;
            margin: 0 auto 30px;
            position: relative;
        }

        .search-container input {
            width: 100%;
            padding: 12px 20px 12px 45px;
            border-radius: 50px;
            border: 2px solid #e0e0e0;
            font-size: 1rem;
            transition: var(--transition);
            box-shadow: var(--shadow-light);
        }

        .search-container input:focus {
            outline: none;
            border-color: var(--gold-primary);
            box-shadow: 0 0 0 3px rgba(189, 151, 81, 0.2);
        }

        .search-container .search-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        /* Sección de categoría */
        .category-section {
            margin-bottom: 28px;
            animation: fadeInUp 0.5s ease both;
        }

        .category-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 14px;
            padding-bottom: 8px;
            border-bottom: 2px solid rgba(189, 151, 81, 0.3);
        }

        .category-header h5 {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--primary-dark);
            margin: 0;
        }

        .category-badge {
            background: var(--primary-dark);
            color: var(--gold-secondary);
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 12px;
            font-weight: 600;
        }

        /* Grid de tarjetas */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 14px;
        }

        /* Tarjeta individual */
        .access-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            background: var(--white-primary);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-light);
            text-decoration: none;
            color: var(--primary-dark);
            transition: var(--transition);
            border-left: 4px solid transparent;
            cursor: pointer;
        }

        .access-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-medium);
            color: var(--primary-dark);
            text-decoration: none;
        }

        .access-card .card-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: white;
            font-size: 1.1rem;
        }

        .access-card .card-text { min-width: 0; }

        .access-card .card-title {
            font-size: 0.88rem;
            font-weight: 600;
            line-height: 1.2;
            margin-bottom: 2px;
        }

        .access-card .card-desc {
            font-size: 0.72rem;
            color: #6c757d;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Botón logout */
        .logout-container-custom { text-align: center; margin: 30px 0; }

        .btn-logout-custom {
            background: linear-gradient(135deg, #ff4d4d, #e63939);
            border: none;
            color: white;
            padding: 14px 40px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: var(--transition);
            box-shadow: var(--shadow-light);
        }

        .btn-logout-custom:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-medium);
            color: white;
        }

        /* Footer */
        .footer-custom {
            background: linear-gradient(135deg, var(--primary-dark), var(--secondary-dark));
            color: var(--white-secondary);
            padding: 40px 0 20px;
            margin-top: 40px;
            text-align: center;
        }

        .footer-custom a { color: var(--gold-secondary); text-decoration: none; }
        .footer-custom a:hover { color: var(--gold-primary); }

        .social-icons-custom {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 15px;
        }

        .social-icons-custom img {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            transition: var(--transition);
        }

        .social-icons-custom img:hover { transform: scale(1.2); }

        /* No results */
        .no-results {
            text-align: center;
            padding: 40px;
            color: #999;
            display: none;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .welcome-banner-custom { padding: 25px 15px; }
            .welcome-banner-custom h3 { font-size: 1.6rem; }
            .welcome-banner-custom h4 { font-size: 1.2rem; }
            .welcome-banner-custom p { font-size: 1rem; }
            .cards-grid { grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 10px; }
            .access-card { padding: 10px 12px; gap: 10px; }
            .access-card .card-icon { width: 36px; height: 36px; font-size: 0.95rem; }
            .access-card .card-title { font-size: 0.8rem; }
            .access-card .card-desc { display: none; }
            .header-logos-custom img { max-height: 45px; }
            .content-wrapper-custom { margin-top: 90px; }
        }

        @media (max-width: 480px) {
            .cards-grid { grid-template-columns: 1fr 1fr; gap: 8px; }
            .access-card .card-icon { width: 32px; height: 32px; font-size: 0.85rem; }
        }
    </style>
</head>

<body>
    <!-- Cabecera -->
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
    <main class="container-fluid content-wrapper-custom">
        <!-- Banner de Bienvenida -->
        <div class="welcome-banner-custom">
            <div class="content-custom">
                <h3><i class="fas fa-users-cog me-3"></i>Dashboard de Administración PH</h3>
                <h4><i class="fas fa-user me-2"></i>Bienvenido, <strong><?= session()->get('nombre_usuario') ?? 'Usuario' ?></strong></h4>
                <p class="mb-0"><i class="fas fa-tools me-2"></i>Centro de Control - Gestión Avanzada de Procesos SST</p>
            </div>
        </div>

        <!-- Buscador -->
        <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="searchInput" placeholder="Buscar acceso..." autocomplete="off">
        </div>

        <!-- Categorías con tarjetas -->
        <div id="categoriesContainer">
            <?php
            $catIcons = [
                'IA y Asistencia' => 'fas fa-robot',
                'Operación Diaria' => 'fas fa-calendar-alt',
                'Gestión Clientes' => 'fas fa-building',
                'Inspecciones y Auditoría' => 'fas fa-clipboard-check',
                'Cumplimiento y Control' => 'fas fa-gavel',
                'Planeación SST' => 'fas fa-project-diagram',
                'Dashboards Analíticos' => 'fas fa-chart-bar',
                'Gestión Documental' => 'fas fa-folder-open',
                'Carga Masiva CSV' => 'fas fa-file-csv',
                'Usuarios y Accesos' => 'fas fa-users-cog',
                'Configuración' => 'fas fa-cog',
                'Administración' => 'fas fa-tools',
            ];
            ?>
            <?php foreach ($grouped as $categoria => $items): ?>
                <div class="category-section" data-category="<?= esc($categoria) ?>">
                    <div class="category-header">
                        <i class="<?= $catIcons[$categoria] ?? 'fas fa-folder' ?>" style="color: var(--gold-primary); font-size: 1.2rem;"></i>
                        <h5><?= esc($categoria) ?></h5>
                        <span class="category-badge"><?= count($items) ?></span>
                    </div>
                    <div class="cards-grid">
                        <?php foreach ($items as $item):
                            $colors = explode(',', $item['color_gradiente'] ?? '#6c757d,#495057');
                            $color1 = trim($colors[0]);
                            $color2 = trim($colors[1] ?? $colors[0]);
                            $isModal = str_starts_with($item['accion_url'], '#');
                            $target = (!$isModal && $item['target_blank']) ? 'target="_blank"' : '';
                            $href = $isModal ? 'javascript:void(0)' : base_url($item['accion_url']);
                            $modalAttr = $isModal ? 'data-bs-toggle="modal" data-bs-target="' . esc($item['accion_url']) . '"' : '';
                        ?>
                            <a href="<?= $href ?>" <?= $target ?> <?= $modalAttr ?>
                               class="access-card"
                               style="border-left-color: <?= $color1 ?>;"
                               data-search="<?= esc(strtolower($item['detalle'] . ' ' . $item['descripcion'] . ' ' . $categoria)) ?>">
                                <div class="card-icon" style="background: linear-gradient(135deg, <?= $color1 ?>, <?= $color2 ?>);">
                                    <i class="<?= esc($item['icono'] ?? 'fas fa-link') ?>"></i>
                                </div>
                                <div class="card-text">
                                    <div class="card-title"><?= esc($item['detalle']) ?></div>
                                    <div class="card-desc"><?= esc($item['descripcion']) ?></div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Sin resultados -->
        <div class="no-results" id="noResults">
            <i class="fas fa-search" style="font-size: 3rem; margin-bottom: 15px; display: block;"></i>
            <h5>No se encontraron accesos</h5>
            <p>Intenta con otro termino de busqueda</p>
        </div>

        <!-- Cerrar Sesión -->
        <div class="logout-container-custom">
            <a href="<?= base_url('/logout') ?>" rel="noopener noreferrer">
                <button type="button" class="btn btn-logout-custom">
                    <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesion
                </button>
            </a>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer-custom">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h5 class="fw-bold mb-3"><i class="fas fa-building me-2"></i>Cycloid Talent SAS</h5>
                    <p class="mb-2">Todos los derechos reservados &copy; <span id="currentYear"></span></p>
                    <p class="mb-2"><i class="fas fa-id-card me-2"></i>NIT: 901.653.912</p>
                    <p class="mb-3">
                        <i class="fas fa-globe me-2"></i>Sitio oficial:
                        <a href="https://cycloidtalent.com/" target="_blank" rel="noopener noreferrer">https://cycloidtalent.com/</a>
                    </p>
                    <div class="mt-4">
                        <strong><i class="fas fa-share-alt me-2"></i>Nuestras Redes Sociales:</strong>
                        <div class="social-icons-custom">
                            <a href="https://www.facebook.com/CycloidTalent" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                                <img src="https://cdn-icons-png.flaticon.com/512/733/733547.png" alt="Facebook">
                            </a>
                            <a href="https://co.linkedin.com/company/cycloid-talent" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn">
                                <img src="https://cdn-icons-png.flaticon.com/512/733/733561.png" alt="LinkedIn">
                            </a>
                            <a href="https://www.instagram.com/cycloid_talent?igsh=Nmo4d2QwZDg5dHh0" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                                <img src="https://cdn-icons-png.flaticon.com/512/733/733558.png" alt="Instagram">
                            </a>
                            <a href="https://www.tiktok.com/@cycloid_talent?_t=8qBSOu0o1ZN&_r=1" target="_blank" rel="noopener noreferrer" aria-label="TikTok">
                                <img src="https://cdn-icons-png.flaticon.com/512/3046/3046126.png" alt="TikTok">
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.getElementById('currentYear').textContent = new Date().getFullYear();

        // Buscador en tiempo real
        var searchInput = document.getElementById('searchInput');
        var categories = document.querySelectorAll('.category-section');
        var noResults = document.getElementById('noResults');

        searchInput.addEventListener('input', function() {
            var term = this.value.toLowerCase().trim();
            var anyVisible = false;

            categories.forEach(function(section) {
                var cards = section.querySelectorAll('.access-card');
                var visibleCards = 0;

                cards.forEach(function(card) {
                    var text = card.getAttribute('data-search');
                    if (!term || text.indexOf(term) !== -1) {
                        card.style.display = '';
                        visibleCards++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                if (visibleCards > 0) {
                    section.style.display = '';
                    anyVisible = true;
                } else {
                    section.style.display = 'none';
                }
            });

            noResults.style.display = anyVisible ? 'none' : 'block';
        });
    </script>

</body>

</html>
