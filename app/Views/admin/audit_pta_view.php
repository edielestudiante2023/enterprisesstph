<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Auditoría - PTA</title>
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
        .detail-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            max-width: 900px;
            margin: 0 auto;
        }
        .detail-header {
            background: linear-gradient(135deg, var(--primary-dark) 0%, #2c3e50 100%);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 25px;
        }
        .info-row {
            display: flex;
            border-bottom: 1px solid #e9ecef;
            padding: 15px 0;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            width: 200px;
            font-weight: 600;
            color: #6c757d;
        }
        .info-value {
            flex: 1;
        }
        .badge-action {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
        }
        .badge-insert { background: #28a745; color: white; }
        .badge-update { background: #ffc107; color: #000; }
        .badge-delete { background: #dc3545; color: white; }
        .badge-bulk { background: #6f42c1; color: white; }
        .valor-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            border-left: 4px solid var(--gold-primary);
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="detail-card">
        <div class="detail-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1"><i class="fas fa-search me-2"></i>Detalle del Registro de Auditoría</h4>
                    <small class="opacity-75">ID: #<?= esc($record['id_audit']) ?></small>
                </div>
                <a href="<?= base_url('/audit-pta') ?>" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left me-2"></i>Volver
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <h5 class="mb-3"><i class="fas fa-info-circle me-2 text-primary"></i>Información del Cambio</h5>

                <div class="info-row">
                    <div class="info-label">Fecha y Hora:</div>
                    <div class="info-value">
                        <strong><?= date('d/m/Y H:i:s', strtotime($record['fecha_accion'])) ?></strong>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-label">Acción:</div>
                    <div class="info-value">
                        <?php
                        $badgeClass = [
                            'INSERT' => 'badge-insert',
                            'UPDATE' => 'badge-update',
                            'DELETE' => 'badge-delete',
                            'BULK_UPDATE' => 'badge-bulk',
                        ][$record['accion']] ?? 'badge-secondary';
                        ?>
                        <span class="badge badge-action <?= $badgeClass ?>"><?= $record['accion'] ?></span>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-label">ID Registro PTA:</div>
                    <div class="info-value">
                        <a href="<?= base_url('/audit-pta/history/' . $record['id_ptacliente']) ?>">#<?= $record['id_ptacliente'] ?></a>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-label">Cliente:</div>
                    <div class="info-value"><?= esc($cliente['nombre_cliente'] ?? 'N/A') ?></div>
                </div>

                <div class="info-row">
                    <div class="info-label">Campo Modificado:</div>
                    <div class="info-value">
                        <code><?= esc($record['campo_modificado'] ?? '-') ?></code>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-label">Método:</div>
                    <div class="info-value">
                        <small class="text-muted"><?= esc($record['metodo'] ?? '-') ?></small>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <h5 class="mb-3"><i class="fas fa-user me-2 text-success"></i>Usuario que realizó el cambio</h5>

                <div class="info-row">
                    <div class="info-label">Nombre:</div>
                    <div class="info-value"><strong><?= esc($record['nombre_usuario']) ?></strong></div>
                </div>

                <div class="info-row">
                    <div class="info-label">Email:</div>
                    <div class="info-value"><?= esc($record['email_usuario'] ?? '-') ?></div>
                </div>

                <div class="info-row">
                    <div class="info-label">Rol:</div>
                    <div class="info-value">
                        <span class="badge bg-secondary"><?= esc($record['rol_usuario'] ?? '-') ?></span>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-label">Dirección IP:</div>
                    <div class="info-value"><code><?= esc($record['ip_address'] ?? '-') ?></code></div>
                </div>

                <div class="info-row">
                    <div class="info-label">Navegador:</div>
                    <div class="info-value">
                        <small class="text-muted"><?= esc(substr($record['user_agent'] ?? '-', 0, 100)) ?></small>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4">

        <h5 class="mb-3"><i class="fas fa-exchange-alt me-2 text-warning"></i>Valores</h5>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label text-muted"><i class="fas fa-minus-circle me-1 text-danger"></i>Valor Anterior:</label>
                <div class="valor-box" style="border-color: #dc3545;">
                    <?= esc($record['valor_anterior'] ?? '(vacío)') ?>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label text-muted"><i class="fas fa-plus-circle me-1 text-success"></i>Valor Nuevo:</label>
                <div class="valor-box" style="border-color: #28a745;">
                    <?= esc($record['valor_nuevo'] ?? '(vacío)') ?>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <label class="form-label text-muted"><i class="fas fa-comment me-1"></i>Descripción:</label>
            <div class="valor-box">
                <?= esc($record['descripcion'] ?? '-') ?>
            </div>
        </div>

        <?php if (!empty($historial) && count($historial) > 1): ?>
        <hr class="my-4">
        <h5 class="mb-3"><i class="fas fa-history me-2 text-info"></i>Otros cambios en este registro</h5>
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Usuario</th>
                        <th>Acción</th>
                        <th>Campo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($historial, 0, 10) as $h): ?>
                    <?php if ($h['id_audit'] != $record['id_audit']): ?>
                    <tr>
                        <td><small><?= date('d/m/Y H:i', strtotime($h['fecha_accion'])) ?></small></td>
                        <td><small><?= esc($h['nombre_usuario']) ?></small></td>
                        <td><span class="badge bg-secondary"><?= $h['accion'] ?></span></td>
                        <td><small><?= esc($h['campo_modificado'] ?? '-') ?></small></td>
                    </tr>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
