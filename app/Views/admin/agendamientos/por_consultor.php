<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendamientos - <?= esc($consultor['nombre_consultor']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-dark: #1c2437;
            --secondary-dark: #2c3e50;
            --gold-primary: #bd9751;
        }
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .page-header {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--secondary-dark) 100%);
            color: white;
            padding: 25px 30px;
            border-radius: 15px;
            margin-bottom: 25px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
        }
        .page-header h2 { color: var(--gold-primary); margin: 0; }
        .stat-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            text-align: center;
            padding: 20px;
        }
        .stat-card .number { font-size: 32px; font-weight: 700; }
        .stat-card .label { font-size: 13px; color: #6c757d; text-transform: uppercase; }
        .client-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            margin-bottom: 10px;
        }
        .client-card.sin-agendar {
            border-left: 4px solid #dc3545;
        }
        .client-card.agendado {
            border-left: 4px solid #28a745;
        }
        .badge-sin { background: #f8d7da; color: #721c24; font-size: 12px; padding: 4px 10px; border-radius: 10px; }
        .badge-ok  { background: #d4edda; color: #155724; font-size: 12px; padding: 4px 10px; border-radius: 10px; }
        .btn-back {
            background: var(--gold-primary);
            color: var(--primary-dark);
            border: none;
            font-weight: 600;
        }
        .btn-back:hover { background: #d4af37; color: var(--primary-dark); }
    </style>
</head>
<body>
    <div class="container-xl">
        <!-- Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <?php if (!empty($consultor['foto_consultor'])): ?>
                        <img src="/uploads/<?= esc($consultor['foto_consultor']) ?>" class="rounded-circle" width="60" height="60" style="object-fit:cover; border: 2px solid var(--gold-primary);">
                    <?php else: ?>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:60px;height:60px;background:rgba(189,151,81,0.2);">
                            <i class="fas fa-user-tie" style="color:var(--gold-primary);font-size:24px;"></i>
                        </div>
                    <?php endif; ?>
                    <div>
                        <h2><?= esc($consultor['nombre_consultor']) ?></h2>
                        <p style="color:#adb5bd; margin:0;"><?= esc($consultor['correo_consultor'] ?? '') ?></p>
                    </div>
                </div>
                <a href="/admin/agendamientos" class="btn btn-back">
                    <i class="fas fa-arrow-left me-1"></i> Volver
                </a>
            </div>
        </div>

        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stat-card bg-white">
                    <div class="number"><?= $totalActivos ?></div>
                    <div class="label">Clientes activos</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card bg-white">
                    <div class="number text-success"><?= $agendados ?></div>
                    <div class="label">Agendados</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card bg-white">
                    <div class="number <?= $sinAgendar > 0 ? 'text-danger' : 'text-success' ?>"><?= $sinAgendar ?></div>
                    <div class="label">Sin agendar</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card bg-white">
                    <div class="number" style="color:var(--gold-primary);"><?= $pct ?>%</div>
                    <div class="label">Cumplimiento</div>
                </div>
            </div>
        </div>

        <!-- Progreso visual -->
        <div class="mb-4">
            <div class="progress" style="height: 8px; border-radius: 10px;">
                <div class="progress-bar <?php
                    if ($pct >= 80) echo 'bg-success';
                    elseif ($pct >= 50) echo 'bg-warning';
                    else echo 'bg-danger';
                ?>" style="width: <?= $pct ?>%"></div>
            </div>
        </div>

        <!-- Lista de clientes -->
        <h5 class="mb-3"><i class="fas fa-building me-2"></i>Clientes Activos</h5>

        <?php foreach ($clientes as $cli): ?>
            <?php $tieneAg = !empty($cli['proximo_agendamiento']); ?>
            <div class="card client-card <?= $tieneAg ? 'agendado' : 'sin-agendar' ?>">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong><?= esc($cli['nombre_cliente']) ?></strong>
                            <?php if ($cli['correo_cliente']): ?>
                                <div style="font-size:12px;" class="text-muted"><?= esc($cli['correo_cliente']) ?></div>
                            <?php endif; ?>
                            <div style="font-size:13px; margin-top:4px;">
                                <i class="fas fa-history me-1 text-muted"></i>
                                Última visita:
                                <?php if ($cli['ultima_visita']): ?>
                                    <strong><?= date('d/m/Y', strtotime($cli['ultima_visita'])) ?></strong>
                                <?php else: ?>
                                    <span class="text-muted">Sin visitas</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="text-end">
                            <?php if ($tieneAg): ?>
                                <span class="badge-ok">
                                    <i class="fas fa-calendar-check me-1"></i>
                                    <?= date('d/m/Y', strtotime($cli['proximo_agendamiento']['fecha_visita'])) ?>
                                    <?= date('g:i A', strtotime($cli['proximo_agendamiento']['hora_visita'])) ?>
                                </span>
                                <div style="font-size:12px; margin-top:4px;">
                                    <?= ucfirst($cli['proximo_agendamiento']['frecuencia']) ?>
                                    &middot;
                                    <?= ucfirst($cli['proximo_agendamiento']['estado']) ?>
                                    <?php if ($cli['proximo_agendamiento']['email_enviado']): ?>
                                        <i class="fas fa-envelope-circle-check text-success ms-1"></i>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <span class="badge-sin">
                                    <i class="fas fa-exclamation-circle me-1"></i>Sin agendar
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($clientes)): ?>
            <div class="text-center text-muted py-5">
                <i class="fas fa-building fa-3x mb-3" style="opacity:0.3;"></i>
                <p>Este consultor no tiene clientes activos</p>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
