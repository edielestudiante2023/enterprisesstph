<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Cambios - Registro PTA #<?= $idPtaCliente ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-dark: #1c2437;
            --gold-primary: #bd9751;
        }
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 30px;
        }
        .history-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            max-width: 1200px;
            margin: 0 auto;
        }
        .history-header {
            background: linear-gradient(135deg, var(--primary-dark) 0%, #2c3e50 100%);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 25px;
        }
        .registro-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
        }
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e9ecef;
        }
        .timeline-item {
            position: relative;
            padding-bottom: 25px;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -24px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--gold-primary);
            border: 2px solid white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .timeline-item.insert::before { background: #28a745; }
        .timeline-item.update::before { background: #ffc107; }
        .timeline-item.delete::before { background: #dc3545; }
        .timeline-content {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 15px;
            transition: all 0.3s ease;
        }
        .timeline-content:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateX(5px);
        }
        .badge-action {
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-insert { background: #28a745; color: white; }
        .badge-update { background: #ffc107; color: #000; }
        .badge-delete { background: #dc3545; color: white; }
        .badge-bulk { background: #6f42c1; color: white; }
        .change-values {
            display: flex;
            gap: 15px;
            margin-top: 10px;
        }
        .change-box {
            flex: 1;
            padding: 10px;
            border-radius: 8px;
            font-size: 0.9rem;
        }
        .change-box.old {
            background: #fff5f5;
            border: 1px solid #ffcccc;
        }
        .change-box.new {
            background: #f0fff0;
            border: 1px solid #ccffcc;
        }
    </style>
</head>
<body>
    <div class="history-card">
        <div class="history-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1"><i class="fas fa-history me-2"></i>Historial de Cambios</h4>
                    <small class="opacity-75">Registro PTA #<?= $idPtaCliente ?></small>
                </div>
                <a href="<?= base_url('/audit-pta') ?>" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left me-2"></i>Volver a Auditoría
                </a>
            </div>
        </div>

        <?php if (!empty($registro)): ?>
        <div class="registro-info">
            <h6 class="mb-3"><i class="fas fa-file-alt me-2"></i>Información del Registro</h6>
            <div class="row">
                <div class="col-md-4">
                    <strong>Cliente:</strong> <?= esc($cliente['nombre_cliente'] ?? 'N/A') ?>
                </div>
                <div class="col-md-4">
                    <strong>Actividad:</strong> <?= esc(substr($registro['actividad_plandetrabajo'] ?? '', 0, 50)) ?>...
                </div>
                <div class="col-md-4">
                    <strong>Estado Actual:</strong>
                    <span class="badge bg-<?= $registro['estado_actividad'] == 'CERRADA' ? 'success' : ($registro['estado_actividad'] == 'ABIERTA' ? 'primary' : 'warning') ?>">
                        <?= esc($registro['estado_actividad']) ?>
                    </span>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($historial)): ?>
        <h5 class="mb-4"><i class="fas fa-stream me-2"></i>Línea de Tiempo (<?= count($historial) ?> cambios)</h5>

        <div class="timeline">
            <?php foreach ($historial as $item): ?>
            <div class="timeline-item <?= strtolower($item['accion']) ?>">
                <div class="timeline-content">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <?php
                            $badgeClass = [
                                'INSERT' => 'badge-insert',
                                'UPDATE' => 'badge-update',
                                'DELETE' => 'badge-delete',
                                'BULK_UPDATE' => 'badge-bulk',
                            ][$item['accion']] ?? 'badge-secondary';
                            ?>
                            <span class="badge badge-action <?= $badgeClass ?>"><?= $item['accion'] ?></span>

                            <?php if (!empty($item['campo_modificado'])): ?>
                            <span class="badge bg-light text-dark ms-2"><?= esc($item['campo_modificado']) ?></span>
                            <?php endif; ?>
                        </div>
                        <small class="text-muted">
                            <?= date('d/m/Y H:i:s', strtotime($item['fecha_accion'])) ?>
                        </small>
                    </div>

                    <div class="mt-2">
                        <strong><?= esc($item['nombre_usuario']) ?></strong>
                        <small class="text-muted">(<?= esc($item['rol_usuario'] ?? '') ?>)</small>
                    </div>

                    <p class="mb-2 text-muted small"><?= esc($item['descripcion'] ?? '') ?></p>

                    <?php if ($item['accion'] === 'UPDATE' && !empty($item['campo_modificado'])): ?>
                    <div class="change-values">
                        <div class="change-box old">
                            <small class="text-muted d-block mb-1"><i class="fas fa-minus-circle text-danger"></i> Antes:</small>
                            <?= esc($item['valor_anterior'] ?? '(vacío)') ?>
                        </div>
                        <div class="change-box new">
                            <small class="text-muted d-block mb-1"><i class="fas fa-plus-circle text-success"></i> Después:</small>
                            <?= esc($item['valor_nuevo'] ?? '(vacío)') ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="mt-2">
                        <small class="text-muted">
                            <i class="fas fa-globe me-1"></i><?= esc($item['ip_address'] ?? '-') ?>
                        </small>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>No hay registros de auditoría para este elemento.
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
