<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Añadir Vigía</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <nav style="background-color: white; position: fixed; top: 0; width: 100%; z-index: 1000; padding: 10px 0; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; max-width: 1200px; margin: 0 auto; padding: 0 20px;">

            <!-- Logo izquierdo -->
            <div>
                <a href="https://dashboard.cycloidtalent.com/login">
                    <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo" style="height: 100px;">
                </a>
            </div>

            <!-- Logo centro -->
            <div>
                <a href="https://cycloidtalent.com/index.php/consultoria-sst">
                    <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST Logo" style="height: 100px;">
                </a>
            </div>

            <!-- Logo derecho -->
            <div>
                <a href="https://cycloidtalent.com/">
                    <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloids Logo" style="height: 100px;">
                </a>
            </div>

            <!-- Botón -->
            <div style="text-align: center;">
                <h2 style="margin: 0; font-size: 16px;">Ir a Dashboard</h2>
                <a href="<?= base_url('/dashboardconsultant') ?>" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; font-size: 14px; margin-top: 5px;">Ir a DashBoard</a>
            </div>
        </div>
    </nav>

    <!-- Ajustar el espaciado para evitar que el contenido se oculte bajo el navbar fijo -->
    <div style="height: 160px;"></div>
    <div class="container mt-5">
        <h2 class="mb-4 text-center">Añadir Vigía</h2>
        <form action="<?= base_url('saveVigia') ?>" method="post" enctype="multipart/form-data" class="p-4 bg-white rounded shadow-sm">
            <div class="mb-3">
                <label for="nombre_vigia" class="form-label">Nombre del Vigía:</label>
                <input type="text" name="nombre_vigia" id="nombre_vigia" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="cedula_vigia" class="form-label">Cédula:</label>
                <input type="text" name="cedula_vigia" id="cedula_vigia" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="periodo_texto" class="form-label">Período:</label>
                <input type="text" name="periodo_texto" id="periodo_texto" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="firma_vigia" class="form-label">Firma (Imagen):</label>
                <input type="file" name="firma_vigia" id="firma_vigia" class="form-control" accept="image/*">
            </div>
            <div class="mb-3">
                <label for="search_cliente" class="form-label">Buscar Cliente:</label>
                <input type="text" id="search_cliente" class="form-control mb-2" placeholder="Escriba para buscar...">

                <label for="id_cliente" class="form-label">Cliente:</label>
                <select name="id_cliente" id="id_cliente" class="form-select">
                    <option value="">Seleccione un cliente</option>
                    <?php foreach ($clients as $client): ?>
                        <option value="<?= $client['id_cliente'] ?>"><?= $client['nombre_cliente'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="d-grid">
                <input type="submit" value="Guardar Vigía" class="btn btn-primary">
            </div>
        </form>
    </div>

    <footer style="background-color: white; padding: 20px 0; border-top: 1px solid #B0BEC5; margin-top: 40px; color: #3A3F51; font-size: 14px; text-align: center;">
        <div style="max-width: 1200px; margin: 0 auto; display: flex; flex-direction: column; align-items: center;">
            <!-- Company and Rights -->
            <p style="margin: 0; font-weight: bold;">Cycloid Talent SAS</p>
            <p style="margin: 5px 0;">Todos los derechos reservados © 2024</p>
            <p style="margin: 5px 0;">NIT: 901.653.912</p>

            <!-- Website Link -->
            <p style="margin: 5px 0;">
                Sitio oficial: <a href="https://cycloidtalent.com/" target="_blank" style="color: #007BFF; text-decoration: none;">https://cycloidtalent.com/</a>
            </p>

            <!-- Social Media Links -->
            <p style="margin: 15px 0 5px;"><strong>Nuestras Redes Sociales:</strong></p>
            <div style="display: flex; gap: 15px; justify-content: center;">
                <a href="https://www.facebook.com/CycloidTalent" target="_blank" style="color: #3A3F51; text-decoration: none;">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733547.png" alt="Facebook" style="height: 24px; width: 24px;">
                </a>
                <a href="https://co.linkedin.com/company/cycloid-talent" target="_blank" style="color: #3A3F51; text-decoration: none;">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733561.png" alt="LinkedIn" style="height: 24px; width: 24px;">
                </a>
                <a href="https://www.instagram.com/cycloid_talent?igsh=Nmo4d2QwZDg5dHh0" target="_blank" style="color: #3A3F51; text-decoration: none;">
                    <img src="https://cdn-icons-png.flaticon.com/512/733/733558.png" alt="Instagram" style="height: 24px; width: 24px;">
                </a>
                <a href="https://www.tiktok.com/@cycloid_talent?_t=8qBSOu0o1ZN&_r=1" target="_blank" style="color: #3A3F51; text-decoration: none;">
                    <img src="https://cdn-icons-png.flaticon.com/512/3046/3046126.png" alt="TikTok" style="height: 24px; width: 24px;">
                </a>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search_cliente');
            const selectCliente = document.getElementById('id_cliente');
            const originalOptions = Array.from(selectCliente.options);

            searchInput.addEventListener('input', function(e) {
                const searchText = e.target.value.toLowerCase();

                // Clear current options
                selectCliente.innerHTML = '<option value="">Seleccione un cliente</option>';

                // Filter and add matching options
                originalOptions.forEach(option => {
                    if (option.text.toLowerCase().includes(searchText)) {
                        selectCliente.appendChild(option.cloneNode(true));
                    }
                });
            });
        });
    </script>
</body>

</html>