<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informe de Avances - <?= esc($cliente['nombre_cliente'] ?? '') ?></title>
    <link rel="icon" href="<?= base_url('favicon.ico') ?>" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary-dark: #1c2437; --gold-primary: #bd9751; --gold-secondary: #d4af37; --gradient-bg: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); }
        body { background: var(--gradient-bg); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; min-height: 100vh; }
        .navbar-custom { background: #fff; box-shadow: 0 8px 32px rgba(28,36,55,0.15); padding: 15px 0; border-bottom: 2px solid var(--gold-primary); }
        .card-section { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); margin-bottom: 1.5rem; }
        .card-section .card-header { background: var(--primary-dark); color: #fff; border-radius: 16px 16px 0 0; font-weight: 600; }
        .card-section .card-header i { color: var(--gold-primary); }
        .btn-gold { background: var(--gold-primary); color: #fff; border: none; }
        .btn-gold:hover { background: var(--gold-secondary); color: #fff; }
        .metric-box { background: #f8f9fa; border-radius: 12px; padding: 15px; text-align: center; border: 1px solid #e9ecef; }
        .metric-box .value { font-size: 1.8rem; font-weight: 700; color: var(--primary-dark); }
        .metric-box .label { font-size: 0.85rem; color: #6c757d; }
        .progress-custom { height: 24px; border-radius: 12px; }
        .progress-custom .progress-bar { border-radius: 12px; font-weight: 600; font-size: 0.8rem; }
        .resumen-text { white-space: pre-wrap; line-height: 1.7; color: #333; }
    </style>
</head>
<body>
    <nav class="navbar navbar-custom mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?= base_url('informe-avances') ?>" style="color: var(--primary-dark);">
                <i class="fas fa-arrow-left me-2"></i>Volver al listado
            </a>
            <div>
                <a href="<?= base_url('informe-avances/pdf/' . $informe['id']) ?>" class="btn btn-gold btn-sm" target="_blank">
                    <i class="fas fa-file-pdf me-1"></i>Ver PDF
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-3">
        <?php if (session()->getFlashdata('msg')): ?>
            <div class="alert alert-success alert-dismissible fade show"><?= session()->getFlashdata('msg') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <!-- Header info -->
        <div class="card card-section">
            <div class="card-header py-3"><i class="fas fa-building me-2"></i>Informe de Avances</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Cliente:</strong> <?= esc($cliente['nombre_cliente'] ?? 'N/A') ?></p>
                        <p><strong>NIT:</strong> <?= esc($cliente['nit_cliente'] ?? 'N/A') ?></p>
                        <p><strong>Consultor:</strong> <?= esc($consultor['nombre_consultor'] ?? 'N/A') ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Periodo:</strong> <?= date('d/m/Y', strtotime($informe['fecha_desde'])) ?> - <?= date('d/m/Y', strtotime($informe['fecha_hasta'])) ?></p>
                        <p><strong>Anio:</strong> <?= esc($informe['anio']) ?></p>
                        <p><strong>Estado:</strong> <span class="badge <?= $informe['estado'] === 'completo' ? 'bg-success' : 'bg-secondary' ?>"><?= ucfirst($informe['estado']) ?></span></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metricas -->
        <div class="card card-section">
            <div class="card-header py-3"><i class="fas fa-chart-bar me-2"></i>Metricas</div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <div class="metric-box">
                            <div class="label">Puntaje Anterior</div>
                            <div class="value"><?= number_format($informe['puntaje_anterior'] ?? 0, 1) ?>%</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="metric-box">
                            <div class="label">Puntaje Actual</div>
                            <div class="value" style="color: var(--gold-primary);"><?= number_format($informe['puntaje_actual'] ?? 0, 1) ?>%</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="metric-box">
                            <div class="label">Diferencia Neta</div>
                            <?php $dif = floatval($informe['diferencia_neta']); ?>
                            <div class="value" style="color: <?= $dif > 0 ? '#28a745' : ($dif < 0 ? '#dc3545' : '#6c757d') ?>">
                                <?= $dif > 0 ? '+' : '' ?><?= number_format($dif, 1) ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="metric-box">
                            <div class="label">Estado de Avance</div>
                            <?php
                                $ea = $informe['estado_avance'];
                                $eaClass = match(true) {
                                    str_contains($ea, 'SIGNIFICATIVO') => 'bg-success',
                                    str_contains($ea, 'MODERADO')      => 'bg-info',
                                    str_contains($ea, 'ESTABLE')       => 'bg-warning text-dark',
                                    default                            => 'bg-danger',
                                };
                            ?>
                            <span class="badge <?= $eaClass ?> mt-2" style="font-size:0.85rem;"><?= esc($ea) ?></span>
                        </div>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Indicador Plan de Trabajo</label>
                        <div class="progress progress-custom">
                            <div class="progress-bar bg-info" style="width: <?= ($informe['indicador_plan_trabajo'] ?? 0) ?>%">
                                <?= number_format($informe['indicador_plan_trabajo'] ?? 0, 1) ?>%
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Indicador Capacitacion</label>
                        <div class="progress progress-custom">
                            <div class="progress-bar bg-success" style="width: <?= ($informe['indicador_capacitacion'] ?? 0) ?>%">
                                <?= number_format($informe['indicador_capacitacion'] ?? 0, 1) ?>%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resumen -->
        <?php if (!empty($informe['resumen_avance'])): ?>
        <div class="card card-section">
            <div class="card-header py-3"><i class="fas fa-file-alt me-2"></i>Resumen de Avance</div>
            <div class="card-body">
                <div class="resumen-text"><?= nl2br(esc($informe['resumen_avance'])) ?></div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Actividades cerradas -->
        <?php if (!empty($informe['actividades_cerradas_periodo'])): ?>
        <div class="card card-section">
            <div class="card-header py-3"><i class="fas fa-check-circle me-2"></i>Actividades PTA Cerradas en el Periodo</div>
            <div class="card-body">
                <div class="resumen-text"><?= nl2br(esc($informe['actividades_cerradas_periodo'])) ?></div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Actividades abiertas -->
        <?php if (!empty($informe['actividades_abiertas'])): ?>
        <div class="card card-section">
            <div class="card-header py-3"><i class="fas fa-exclamation-triangle me-2"></i>Actividades Abiertas</div>
            <div class="card-body">
                <div class="resumen-text"><?= nl2br(esc($informe['actividades_abiertas'])) ?></div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Observaciones -->
        <?php if (!empty($informe['observaciones'])): ?>
        <div class="card card-section">
            <div class="card-header py-3"><i class="fas fa-comment-alt me-2"></i>Observaciones</div>
            <div class="card-body">
                <div class="resumen-text"><?= nl2br(esc($informe['observaciones'])) ?></div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Soportes -->
        <?php
            $haySoportes = false;
            for ($i = 1; $i <= 4; $i++) {
                if (!empty($informe["soporte_{$i}_texto"]) || !empty($informe["soporte_{$i}_imagen"])) { $haySoportes = true; break; }
            }
        ?>
        <?php if ($haySoportes): ?>
        <div class="card card-section">
            <div class="card-header py-3"><i class="fas fa-paperclip me-2"></i>Soportes</div>
            <div class="card-body">
                <div class="row g-3">
                    <?php for ($i = 1; $i <= 4; $i++): ?>
                        <?php if (!empty($informe["soporte_{$i}_texto"]) || !empty($informe["soporte_{$i}_imagen"])): ?>
                        <div class="col-md-6">
                            <div class="border rounded p-3">
                                <h6 class="fw-bold">Soporte <?= $i ?></h6>
                                <?php if (!empty($informe["soporte_{$i}_texto"])): ?>
                                    <p><?= esc($informe["soporte_{$i}_texto"]) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($informe["soporte_{$i}_imagen"])): ?>
                                    <img src="<?= base_url($informe["soporte_{$i}_imagen"]) ?>" class="img-fluid rounded" style="max-height: 300px; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#modalImg<?= $i ?>">
                                    <!-- Modal -->
                                    <div class="modal fade" id="modalImg<?= $i ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-body p-0">
                                                    <img src="<?= base_url($informe["soporte_{$i}_imagen"]) ?>" class="img-fluid w-100">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="mb-5"></div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
