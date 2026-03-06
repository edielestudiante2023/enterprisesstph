<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Listado Maestro - Seleccionar Cliente</title>
    <link rel="icon" href="<?= base_url('favicon.ico') ?>" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        :root { --primary-dark: #1c2437; --gold-primary: #bd9751; --gold-secondary: #d4af37; }
        body { background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); min-height: 100vh; }
        .main-card { max-width: 700px; margin: 60px auto; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.12); overflow: hidden; }
        .card-header-custom { background: linear-gradient(135deg, var(--primary-dark), #2c3e50); color: white; padding: 28px 32px; text-align: center; }
        .card-header-custom h2 { margin: 0; font-weight: 700; font-size: 1.5rem; }
        .card-header-custom p { margin: 8px 0 0; opacity: 0.8; font-size: 0.9rem; }
        .card-body-custom { padding: 32px; }
        .btn-ir { background: linear-gradient(135deg, #e67e22, #f39c12); border: none; color: white; font-weight: 600; padding: 12px 32px; border-radius: 8px; font-size: 1rem; transition: all 0.3s; }
        .btn-ir:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(230,126,34,0.4); color: white; }
        .btn-ir:disabled { opacity: 0.5; transform: none; }
        .btn-volver { color: #6c757d; text-decoration: none; font-weight: 500; transition: color 0.2s; }
        .btn-volver:hover { color: var(--primary-dark); }
        .select2-container--bootstrap-5 .select2-selection { min-height: 46px; padding: 8px 12px; font-size: 1rem; }
    </style>
</head>
<body>
    <div class="container">
        <div class="main-card card">
            <div class="card-header-custom">
                <h2><i class="fas fa-list-alt me-2"></i>Listado Maestro de Documentos</h2>
                <p>Formato FT-SST-020 del SG-SST</p>
            </div>
            <div class="card-body-custom">
                <div class="mb-4">
                    <label for="selectCliente" class="form-label fw-semibold">
                        <i class="fas fa-building me-1"></i> Seleccionar Cliente
                    </label>
                    <select id="selectCliente" class="form-select" style="width:100%;">
                        <option value="">Buscar cliente...</option>
                        <?php foreach ($clientes as $c): ?>
                            <option value="<?= $c['id_cliente'] ?>"><?= esc($c['nombre_cliente']) ?> - NIT: <?= esc($c['nit_cliente'] ?? 'N/A') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <a href="<?= base_url('consultant/dashboard') ?>" class="btn-volver">
                        <i class="fas fa-arrow-left me-1"></i> Volver al Dashboard
                    </a>
                    <button id="btnIr" class="btn btn-ir" disabled>
                        <i class="fas fa-arrow-right me-2"></i>Ver Listado Maestro
                    </button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#selectCliente').select2({ theme: 'bootstrap-5', placeholder: 'Buscar cliente por nombre o NIT...', allowClear: true });
            $('#selectCliente').on('change', function() { $('#btnIr').prop('disabled', !$(this).val()); });
            $('#btnIr').on('click', function() {
                const id = $('#selectCliente').val();
                if (id) window.location.href = '<?= base_url("listado-maestro") ?>/' + id;
            });
        });
    </script>
</body>
</html>
