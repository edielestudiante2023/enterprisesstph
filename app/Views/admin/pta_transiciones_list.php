<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transiciones PTA - Actividades Gestionadas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-dark: #1c2437;
            --secondary-dark: #2c3e50;
            --gold-primary: #bd9751;
            --gold-secondary: #d4af37;
        }
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .page-header {
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
            color: white;
            padding: 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .filter-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .stats-card .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #0d6efd, var(--gold-primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .stats-card .stats-label {
            color: #6c757d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .table-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .badge-estado {
            padding: 6px 14px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.78rem;
        }
        .badge-gestionando { background: #ffc107; color: #000; }
        .badge-cerrada { background: #28a745; color: white; }
        .badge-otro { background: #6f42c1; color: white; }
        .btn-gold {
            background: linear-gradient(135deg, var(--gold-primary), var(--gold-secondary));
            border: none;
            color: white;
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 600;
        }
        .btn-gold:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(189, 151, 81, 0.4);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2"><i class="fas fa-exchange-alt me-3"></i>Transiciones del Plan de Trabajo</h2>
                    <p class="mb-0 opacity-75">Actividades que salieron de estado ABIERTA</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="<?= base_url('/dashboardconsultant') ?>" class="btn btn-outline-light me-2">
                        <i class="fas fa-arrow-left me-2"></i>Volver
                    </a>
                    <?php if (!empty($records)): ?>
                    <a href="<?= base_url('/pta-transiciones/export?' . http_build_query($filters)) ?>" class="btn btn-light">
                        <i class="fas fa-file-excel me-2"></i>Exportar
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filter-card">
            <h5 class="mb-3"><i class="fas fa-filter me-2"></i>Filtros</h5>
            <form method="get" action="<?= base_url('/pta-transiciones') ?>">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label"><i class="fas fa-building me-1"></i>Cliente</label>
                        <select name="cliente" id="selectCliente" class="form-select">
                            <option value="">Todos los clientes</option>
                            <?php foreach ($clients as $client): ?>
                            <option value="<?= $client['id_cliente'] ?>" <?= ($filters['cliente'] ?? '') == $client['id_cliente'] ? 'selected' : '' ?>>
                                <?= esc($client['nombre_cliente']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label"><i class="fas fa-tag me-1"></i>Estado Nuevo</label>
                        <select name="estado_nuevo" class="form-select">
                            <option value="">Todos</option>
                            <option value="GESTIONANDO" <?= ($filters['estado_nuevo'] ?? '') == 'GESTIONANDO' ? 'selected' : '' ?>>GESTIONANDO</option>
                            <option value="CERRADA" <?= ($filters['estado_nuevo'] ?? '') == 'CERRADA' ? 'selected' : '' ?>>CERRADA</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><i class="fas fa-calendar me-1"></i>Rango de Fechas</label>
                        <div class="input-group">
                            <input type="date" name="fecha_desde" class="form-control" value="<?= $filters['fecha_desde'] ?? '' ?>">
                            <input type="date" name="fecha_hasta" class="form-control" value="<?= $filters['fecha_hasta'] ?? '' ?>">
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-gold">
                            <i class="fas fa-search me-2"></i>Buscar
                        </button>
                        <a href="<?= base_url('/pta-transiciones') ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-undo me-2"></i>Limpiar
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <?php if (!empty($stats)): ?>
        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="stats-number"><?= !empty($records) ? number_format(count($records)) : '0' ?></div>
                    <div class="stats-label">Total Transiciones</div>
                </div>
            </div>
            <?php foreach ($stats as $stat): ?>
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="stats-number"><?= number_format($stat['cantidad']) ?></div>
                    <div class="stats-label"><?= esc($stat['estado_nuevo']) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($records)): ?>
        <!-- Tabla -->
        <div class="table-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Transiciones Registradas (<?= count($records) ?>)</h5>
            </div>
            <div class="table-responsive">
                <table id="transicionesTable" class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Fecha/Hora</th>
                            <th>Actividad</th>
                            <th>Numeral</th>
                            <th>Estado Anterior</th>
                            <th>Estado Nuevo</th>
                            <th>Estado Actual</th>
                            <th>Usuario</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $row): ?>
                        <tr>
                            <td>
                                <small>
                                    <strong><?= date('d/m/Y', strtotime($row['fecha_transicion'])) ?></strong><br>
                                    <span class="text-muted"><?= date('H:i:s', strtotime($row['fecha_transicion'])) ?></span>
                                </small>
                            </td>
                            <td>
                                <span title="<?= esc($row['actividad_plandetrabajo'] ?? '') ?>">
                                    <?= esc(mb_substr($row['actividad_plandetrabajo'] ?? '-', 0, 60)) ?><?= mb_strlen($row['actividad_plandetrabajo'] ?? '') > 60 ? '...' : '' ?>
                                </span>
                            </td>
                            <td><small><?= esc($row['numeral_plandetrabajo'] ?? '-') ?></small></td>
                            <td><span class="badge bg-secondary"><?= esc($row['estado_anterior']) ?></span></td>
                            <td>
                                <?php
                                $badgeClass = match($row['estado_nuevo']) {
                                    'GESTIONANDO' => 'badge-gestionando',
                                    'CERRADA'     => 'badge-cerrada',
                                    default       => 'badge-otro',
                                };
                                ?>
                                <span class="badge badge-estado <?= $badgeClass ?>"><?= esc($row['estado_nuevo']) ?></span>
                            </td>
                            <td>
                                <?php
                                $actual = $row['estado_actual'] ?? '-';
                                $badgeActual = match($actual) {
                                    'ABIERTA'     => 'bg-warning text-dark',
                                    'GESTIONANDO' => 'bg-info text-dark',
                                    'CERRADA'     => 'bg-success',
                                    default       => 'bg-secondary',
                                };
                                ?>
                                <span class="badge <?= $badgeActual ?>"><?= esc($actual) ?></span>
                            </td>
                            <td>
                                <small><strong><?= esc($row['nombre_usuario'] ?? 'Sistema') ?></strong></small>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>No se han registrado transiciones aún. Aparecerán aquí cuando una actividad cambie de ABIERTA a otro estado.
        </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#selectCliente').select2({
                theme: 'bootstrap-5',
                placeholder: 'Seleccione un cliente...',
                allowClear: true
            });

            if ($('#transicionesTable').length && $('#transicionesTable tbody tr').length > 0) {
                $('#transicionesTable').DataTable({
                    order: [[0, 'desc']],
                    pageLength: 25,
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
                    }
                });
            }
        });
    </script>
</body>
</html>
