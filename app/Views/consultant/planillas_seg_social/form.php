<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $planilla ? 'Editar' : 'Subir' ?> Planilla de Seguridad Social</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        :root {
            --primary-dark: #1a3a5c;
            --gold-primary: #d4a843;
            --gold-secondary: #c19a3e;
        }
        body {
            background-color: #f4f4f7;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar-custom {
            background: linear-gradient(135deg, var(--primary-dark), #2c5f8a);
            padding: 10px 0;
        }
        .navbar-custom img { height: 45px; }
        .page-header {
            background: linear-gradient(135deg, var(--primary-dark), #2c5f8a);
            color: white;
            padding: 25px 30px;
            border-radius: 10px;
            margin-bottom: 25px;
        }
        .form-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            padding: 30px;
            max-width: 700px;
            margin: 0 auto;
        }
        .btn-gold {
            background: linear-gradient(135deg, var(--gold-primary), var(--gold-secondary));
            color: white;
            border: none;
            font-weight: 600;
        }
        .btn-gold:hover { background: linear-gradient(135deg, var(--gold-secondary), #b08930); color: white; }
        .file-info {
            background: #f0f5fa;
            border-left: 4px solid #2c5f8a;
            padding: 12px 16px;
            border-radius: 0 6px 6px 0;
            margin-top: 8px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-custom">
        <div class="container-fluid d-flex justify-content-between align-items-center px-4">
            <a href="<?= base_url('/dashboardconsultant') ?>">
                <img src="<?= base_url('assets/img/logotipo_enterprisesst.png') ?>" alt="Logo">
            </a>
            <a href="<?= base_url('planillas-seguridad-social') ?>" class="btn btn-outline-light btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Volver a Planillas
            </a>
        </div>
    </nav>

    <div class="container py-4">
        <!-- Header -->
        <div class="page-header text-center">
            <h3 class="mb-1">
                <i class="fas fa-<?= $planilla ? 'edit' : 'cloud-upload-alt' ?> me-2"></i>
                <?= $planilla ? 'Editar Planilla' : 'Subir Nueva Planilla' ?>
            </h3>
            <p class="mb-0 opacity-75">Planilla de Seguridad Social</p>
        </div>

        <!-- Flash messages -->
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <div class="form-container">
            <form action="<?= $planilla ? base_url('planillas-seguridad-social/update/' . $planilla['id']) : base_url('planillas-seguridad-social/store') ?>"
                  method="post" enctype="multipart/form-data" id="formPlanilla">
                <?= csrf_field() ?>

                <!-- Mes de aportes -->
                <div class="mb-4">
                    <label for="mes_aportes" class="form-label fw-bold">
                        <i class="fas fa-calendar-alt me-1 text-primary"></i> Mes de Aportes <span class="text-danger">*</span>
                    </label>
                    <input type="month" class="form-control form-control-lg" id="mes_aportes" name="mes_aportes"
                           value="<?= $planilla ? esc($planilla['mes_aportes']) : date('Y-m') ?>" required>
                    <div class="form-text">Seleccione el período al que corresponde la planilla.</div>
                </div>

                <!-- Archivo PDF -->
                <div class="mb-4">
                    <label for="archivo_pdf" class="form-label fw-bold">
                        <i class="fas fa-file-pdf me-1 text-danger"></i> Archivo PDF
                        <?php if (!$planilla): ?><span class="text-danger">*</span><?php endif; ?>
                    </label>
                    <input type="file" class="form-control form-control-lg" id="archivo_pdf" name="archivo_pdf"
                           accept=".pdf" <?= $planilla ? '' : 'required' ?>>
                    <?php if ($planilla): ?>
                        <div class="file-info">
                            <i class="fas fa-paperclip me-1"></i>
                            Archivo actual: <strong><?= esc($planilla['archivo_pdf']) ?></strong>
                            <br><small class="text-muted">Suba un nuevo archivo solo si desea reemplazarlo.</small>
                        </div>
                    <?php else: ?>
                        <div class="form-text">Solo se permiten archivos PDF.</div>
                    <?php endif; ?>
                </div>

                <!-- Notas -->
                <div class="mb-4">
                    <label for="notas" class="form-label fw-bold">
                        <i class="fas fa-sticky-note me-1 text-warning"></i> Notas (opcional)
                    </label>
                    <textarea class="form-control" id="notas" name="notas" rows="3"
                              placeholder="Observaciones o comentarios sobre esta planilla..."><?= $planilla ? esc($planilla['notas'] ?? '') : '' ?></textarea>
                </div>

                <!-- Buttons -->
                <div class="d-flex justify-content-between">
                    <a href="<?= base_url('planillas-seguridad-social') ?>" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-gold btn-lg">
                        <i class="fas fa-save me-1"></i> <?= $planilla ? 'Actualizar' : 'Guardar' ?> Planilla
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>
