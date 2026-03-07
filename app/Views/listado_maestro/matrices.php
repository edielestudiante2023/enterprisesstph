<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Matrices SST - <?= esc($cliente['nombre_cliente']) ?></title>
    <link rel="icon" href="<?= base_url('favicon.ico') ?>" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); min-height: 100vh; }
        .main-card { max-width: 800px; margin: 40px auto; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.12); overflow: hidden; }
        .card-header-custom { background: linear-gradient(135deg, #1c2437, #2c3e50); color: white; padding: 24px 28px; }
        .card-header-custom h2 { margin: 0; font-weight: 700; font-size: 1.3rem; }
        .card-header-custom p { margin: 6px 0 0; opacity: 0.8; font-size: 0.85rem; }
        .card-body-custom { padding: 28px; }
        .matriz-card { border: 1px solid #dee2e6; border-radius: 12px; padding: 20px; margin-bottom: 16px; transition: all 0.2s; background: white; }
        .matriz-card:hover { box-shadow: 0 4px 15px rgba(0,0,0,0.1); transform: translateY(-2px); }
        .matriz-icon { font-size: 2rem; width: 56px; height: 56px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; flex-shrink: 0; }
        .btn-download { border-radius: 8px; font-weight: 600; padding: 8px 20px; white-space: nowrap; }
        .client-info { background: #f8f9fa; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; font-size: 0.9rem; }
        .client-logo { max-height: 50px; max-width: 80px; object-fit: contain; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="main-card card">
            <div class="card-header-custom">
                <h2><i class="fas fa-table me-2"></i>Matrices Interactivas SST</h2>
                <p>Generar matrices personalizadas con datos del cliente</p>
            </div>
            <div class="card-body-custom">
                <div class="client-info d-flex align-items-center gap-3">
                    <?php if (!empty($cliente['logo'])): ?>
                        <img src="<?= base_url('uploads/' . $cliente['logo']) ?>" alt="Logo" class="client-logo">
                    <?php endif; ?>
                    <div>
                        <strong><?= esc($cliente['nombre_cliente']) ?></strong><br>
                        <small class="text-muted">
                            NIT: <?= esc($cliente['nit_cliente'] ?? 'N/A') ?>
                            <?php if ($contrato): ?>
                                | Contrato: <?= date('d/m/Y', strtotime($contrato['fecha_inicio'])) ?> - <?= date('d/m/Y', strtotime($contrato['fecha_fin'])) ?>
                            <?php else: ?>
                                | <span class="text-danger">Sin contrato activo</span>
                            <?php endif; ?>
                        </small>
                    </div>
                </div>

                <div class="matriz-card d-flex align-items-center gap-3">
                    <div class="matriz-icon" style="background: linear-gradient(135deg, #11998e, #38ef7d);">
                        <i class="fas fa-hard-hat"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">MT-SST-002 Matriz de Elementos de Proteccion Personal</h6>
                        <small class="text-muted">6 hojas: EPP y Dotacion por cargo</small>
                    </div>
                    <a href="<?= base_url('listado-maestro/matriz-epp/' . $cliente['id_cliente']) ?>" class="btn btn-success btn-download">
                        <i class="fas fa-download me-1"></i> Descargar
                    </a>
                </div>

                <div class="matriz-card d-flex align-items-center gap-3">
                    <div class="matriz-icon" style="background: linear-gradient(135deg, #e74c3c, #c0392b);">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">MT-SST-001 Matriz de Identificacion de Peligros y Valoracion de Riesgos</h6>
                        <small class="text-muted">7 hojas: Administracion, Serv. Generales, Operaciones, Seguridad, Parqueadero + Tablas</small>
                    </div>
                    <a href="<?= base_url('listado-maestro/matriz-peligros/' . $cliente['id_cliente']) ?>" class="btn btn-danger btn-download">
                        <i class="fas fa-download me-1"></i> Descargar
                    </a>
                </div>

                <div class="matriz-card d-flex align-items-center gap-3" style="border: 2px solid #1c2437;">
                    <div class="matriz-icon" style="background: linear-gradient(135deg, #1c2437, #2c3e50);">
                        <i class="fas fa-file-archive"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">Descargar todas las matrices</h6>
                        <small class="text-muted">Archivo ZIP con ambas matrices personalizadas</small>
                    </div>
                    <a href="<?= base_url('listado-maestro/matrices-todas/' . $cliente['id_cliente']) ?>" class="btn btn-dark btn-download">
                        <i class="fas fa-download me-1"></i> ZIP
                    </a>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <a href="<?= base_url('listado-maestro/' . $cliente['id_cliente']) ?>" class="text-decoration-none text-muted">
                        <i class="fas fa-arrow-left me-1"></i> Volver al Listado Maestro
                    </a>
                    <a href="<?= base_url('listado-maestro') ?>" class="text-decoration-none text-muted">
                        <i class="fas fa-exchange-alt me-1"></i> Cambiar cliente
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
