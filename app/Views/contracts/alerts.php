<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alertas de Contratos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .stats-card {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }
        .table-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-bell"></i> Alertas de Contratos
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="<?= base_url('/contracts') ?>">
                    <i class="fas fa-file-contract"></i> Contratos
                </a>
                <a class="nav-link" href="<?= base_url('/dashboardconsultant') ?>">
                    <i class="fas fa-dashboard"></i> Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <?php
            $alta = array_filter($alerts, fn($a) => $a['urgencia'] === 'alta');
            $media = array_filter($alerts, fn($a) => $a['urgencia'] === 'media');
            $baja = array_filter($alerts, fn($a) => $a['urgencia'] === 'baja');
        ?>
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card stats-card border-danger">
                    <div class="card-body text-center">
                        <h3 class="text-danger"><?= count($alta) ?></h3>
                        <p class="mb-0"><i class="fas fa-exclamation-triangle text-danger"></i> Urgencia Alta (0-7 dias)</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card border-warning">
                    <div class="card-body text-center">
                        <h3 class="text-warning"><?= count($media) ?></h3>
                        <p class="mb-0"><i class="fas fa-exclamation-circle text-warning"></i> Urgencia Media (8-15 dias)</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card border-info">
                    <div class="card-body text-center">
                        <h3 class="text-info"><?= count($baja) ?></h3>
                        <p class="mb-0"><i class="fas fa-info-circle text-info"></i> Urgencia Baja (16-30 dias)</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-container">
            <?php if (empty($alerts)): ?>
                <div class="alert alert-success text-center">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <h5>No hay contratos proximos a vencer en los proximos 30 dias</h5>
                </div>
            <?php else: ?>
                <table id="tablaAlertas" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Urgencia</th>
                            <th>Cliente</th>
                            <th>N. Contrato</th>
                            <th>Fecha Fin</th>
                            <th>Dias Restantes</th>
                            <th>Correo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($alerts as $alert): ?>
                        <tr>
                            <td>
                                <span class="badge bg-<?= esc($alert['color']) ?>">
                                    <?= ucfirst(esc($alert['urgencia'])) ?>
                                </span>
                            </td>
                            <td><?= esc($alert['nombre_cliente']) ?></td>
                            <td><?= esc($alert['numero_contrato'] ?? '-') ?></td>
                            <td><?= date('d/m/Y', strtotime($alert['fecha_fin'])) ?></td>
                            <td>
                                <span class="fw-bold text-<?= esc($alert['color']) ?>">
                                    <?= $alert['dias_restantes'] ?> dias
                                </span>
                            </td>
                            <td><?= esc($alert['correo_cliente'] ?? '-') ?></td>
                            <td>
                                <a href="<?= base_url('/contracts/view/' . $alert['id_contrato']) ?>" class="btn btn-sm btn-outline-primary" title="Ver contrato">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?= base_url('/contracts/renew/' . $alert['id_contrato']) ?>" class="btn btn-sm btn-outline-success" title="Renovar">
                                    <i class="fas fa-redo"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        $('#tablaAlertas').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            },
            order: [[4, 'asc']],
            pageLength: 25
        });
    });
    </script>
</body>
</html>
