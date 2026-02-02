<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auditoría Plan de Trabajo - PTA</title>
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
        .audit-header {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--secondary-dark) 100%);
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
            background: linear-gradient(135deg, var(--primary-dark), var(--gold-primary));
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
        .badge-action {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.75rem;
        }
        .badge-insert { background: #28a745; color: white; }
        .badge-update { background: #ffc107; color: #000; }
        .badge-delete { background: #dc3545; color: white; }
        .badge-bulk { background: #6f42c1; color: white; }
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
        .valor-cell {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .valor-cell:hover {
            white-space: normal;
            overflow: visible;
            position: relative;
            z-index: 10;
            background: #f8f9fa;
            padding: 5px;
            border-radius: 5px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--gold-primary), var(--gold-secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.8rem;
        }
        .campo-badge {
            background: #e9ecef;
            color: #495057;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Header -->
        <div class="audit-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2"><i class="fas fa-history me-3"></i>Auditoría del Plan de Trabajo Anual</h2>
                    <p class="mb-0 opacity-75">Registro de todos los cambios realizados en el PTA</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="<?= base_url('/dashboardconsultant') ?>" class="btn btn-outline-light me-2">
                        <i class="fas fa-arrow-left me-2"></i>Volver
                    </a>
                    <?php if (!empty($records)): ?>
                    <a href="<?= base_url('/audit-pta/export?' . http_build_query($filters)) ?>" class="btn btn-light">
                        <i class="fas fa-file-excel me-2"></i>Exportar
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filter-card">
            <h5 class="mb-3"><i class="fas fa-filter me-2"></i>Filtros de Búsqueda</h5>
            <form method="get" action="<?= base_url('/audit-pta') ?>">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label"><i class="fas fa-building me-1"></i>Cliente</label>
                        <select name="cliente" id="cliente" class="form-select">
                            <option value="">Todos los clientes</option>
                            <?php foreach ($clients as $client): ?>
                            <option value="<?= $client['id_cliente'] ?>" <?= ($filters['cliente'] ?? '') == $client['id_cliente'] ? 'selected' : '' ?>>
                                <?= esc($client['nombre_cliente']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label"><i class="fas fa-user me-1"></i>Usuario</label>
                        <select name="usuario" class="form-select">
                            <option value="">Todos</option>
                            <?php foreach ($usuarios as $user): ?>
                            <option value="<?= $user['id_usuario'] ?>" <?= ($filters['usuario'] ?? '') == $user['id_usuario'] ? 'selected' : '' ?>>
                                <?= esc($user['nombre_usuario']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label"><i class="fas fa-bolt me-1"></i>Acción</label>
                        <select name="accion" class="form-select">
                            <option value="">Todas</option>
                            <option value="INSERT" <?= ($filters['accion'] ?? '') == 'INSERT' ? 'selected' : '' ?>>Creación</option>
                            <option value="UPDATE" <?= ($filters['accion'] ?? '') == 'UPDATE' ? 'selected' : '' ?>>Modificación</option>
                            <option value="DELETE" <?= ($filters['accion'] ?? '') == 'DELETE' ? 'selected' : '' ?>>Eliminación</option>
                            <option value="BULK_UPDATE" <?= ($filters['accion'] ?? '') == 'BULK_UPDATE' ? 'selected' : '' ?>>Masiva</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label"><i class="fas fa-columns me-1"></i>Campo</label>
                        <select name="campo" class="form-select">
                            <option value="">Todos</option>
                            <?php foreach ($campos as $campo): ?>
                            <option value="<?= $campo['campo_modificado'] ?>" <?= ($filters['campo'] ?? '') == $campo['campo_modificado'] ? 'selected' : '' ?>>
                                <?= esc($campo['campo_modificado']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label"><i class="fas fa-calendar me-1"></i>Rango de Fechas</label>
                        <div class="input-group">
                            <input type="date" name="fecha_desde" class="form-control" value="<?= $filters['fecha_desde'] ?? '' ?>">
                            <input type="date" name="fecha_hasta" class="form-control" value="<?= $filters['fecha_hasta'] ?? '' ?>">
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-gold me-2">
                        <i class="fas fa-search me-2"></i>Buscar
                    </button>
                    <a href="<?= base_url('/audit-pta') ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-undo me-2"></i>Limpiar
                    </a>
                </div>
            </form>
        </div>

        <?php if (!empty($stats)): ?>
        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="stats-number"><?= number_format($stats['total']) ?></div>
                    <div class="stats-label">Total de Registros</div>
                </div>
            </div>
            <?php foreach ($stats['por_accion'] as $stat): ?>
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="stats-number"><?= number_format($stat['cantidad']) ?></div>
                    <div class="stats-label">
                        <?php
                        $labels = ['INSERT' => 'Creaciones', 'UPDATE' => 'Modificaciones', 'DELETE' => 'Eliminaciones', 'BULK_UPDATE' => 'Masivas'];
                        echo $labels[$stat['accion']] ?? $stat['accion'];
                        ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($records)): ?>
        <!-- Tabla de resultados -->
        <div class="table-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Registros de Auditoría (<?= count($records) ?>)</h5>
            </div>
            <div class="table-responsive">
                <table id="auditTable" class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Fecha/Hora</th>
                            <th>Usuario</th>
                            <th>Acción</th>
                            <th>Campo</th>
                            <th>Valor Anterior</th>
                            <th>Valor Nuevo</th>
                            <th>Descripción</th>
                            <th>IP</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $row): ?>
                        <tr>
                            <td>
                                <small>
                                    <strong><?= date('d/m/Y', strtotime($row['fecha_accion'])) ?></strong><br>
                                    <span class="text-muted"><?= date('H:i:s', strtotime($row['fecha_accion'])) ?></span>
                                </small>
                            </td>
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar">
                                        <?= strtoupper(substr($row['nombre_usuario'] ?? 'U', 0, 1)) ?>
                                    </div>
                                    <div>
                                        <strong><?= esc($row['nombre_usuario'] ?? 'Sistema') ?></strong><br>
                                        <small class="text-muted"><?= esc($row['rol_usuario'] ?? '') ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php
                                $badgeClass = [
                                    'INSERT'      => 'badge-insert',
                                    'UPDATE'      => 'badge-update',
                                    'DELETE'      => 'badge-delete',
                                    'BULK_UPDATE' => 'badge-bulk',
                                ][$row['accion']] ?? 'badge-secondary';
                                $badgeIcon = [
                                    'INSERT'      => 'fa-plus',
                                    'UPDATE'      => 'fa-edit',
                                    'DELETE'      => 'fa-trash',
                                    'BULK_UPDATE' => 'fa-layer-group',
                                ][$row['accion']] ?? 'fa-question';
                                ?>
                                <span class="badge badge-action <?= $badgeClass ?>">
                                    <i class="fas <?= $badgeIcon ?> me-1"></i><?= $row['accion'] ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($row['campo_modificado'])): ?>
                                <span class="campo-badge"><?= esc($row['campo_modificado']) ?></span>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="valor-cell" title="<?= esc($row['valor_anterior'] ?? '') ?>">
                                <?= esc(substr($row['valor_anterior'] ?? '-', 0, 50)) ?>
                                <?= strlen($row['valor_anterior'] ?? '') > 50 ? '...' : '' ?>
                            </td>
                            <td class="valor-cell" title="<?= esc($row['valor_nuevo'] ?? '') ?>">
                                <?= esc(substr($row['valor_nuevo'] ?? '-', 0, 50)) ?>
                                <?= strlen($row['valor_nuevo'] ?? '') > 50 ? '...' : '' ?>
                            </td>
                            <td>
                                <small><?= esc($row['descripcion'] ?? '') ?></small>
                            </td>
                            <td>
                                <small class="text-muted"><?= esc($row['ip_address'] ?? '-') ?></small>
                            </td>
                            <td>
                                <a href="<?= base_url('/audit-pta/view/' . $row['id_audit']) ?>" class="btn btn-sm btn-outline-primary" title="Ver detalle">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?= base_url('/audit-pta/history/' . $row['id_ptacliente']) ?>" class="btn btn-sm btn-outline-secondary" title="Ver historial del registro">
                                    <i class="fas fa-history"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php elseif (!empty($filters['cliente']) || !empty($filters['usuario']) || !empty($filters['accion'])): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>No se encontraron registros de auditoría con los filtros seleccionados.
        </div>
        <?php else: ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>Seleccione al menos un filtro para ver los registros de auditoría.
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
            // Inicializar Select2
            $('#cliente').select2({
                theme: 'bootstrap-5',
                placeholder: 'Seleccione un cliente...',
                allowClear: true
            });

            // Inicializar DataTable
            if ($('#auditTable').length && $('#auditTable tbody tr').length > 0) {
                $('#auditTable').DataTable({
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
