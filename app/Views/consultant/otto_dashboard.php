<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Otto — Logs de Conversaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --gold: #bd9751; --gold-dark: #8B6914; }
        body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; }

        .topbar {
            background: linear-gradient(135deg, #1c2437, #2d3a52);
            color: white;
            padding: 14px 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 3px solid var(--gold);
        }
        .topbar img { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; background: #1d2638; }
        .topbar h1 { font-size: 1.2rem; margin: 0; font-weight: 600; }
        .topbar .back-btn {
            margin-left: auto;
            background: rgba(255,255,255,0.15);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 8px;
            padding: 6px 16px;
            text-decoration: none;
            font-size: 0.85rem;
        }
        .topbar .back-btn:hover { background: rgba(255,255,255,0.25); color: white; }

        .kpi-card {
            background: white;
            border-radius: 14px;
            padding: 22px;
            text-align: center;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            border-top: 4px solid var(--gold);
        }
        .kpi-card .kpi-value { font-size: 2.2rem; font-weight: 700; color: #1c2437; }
        .kpi-card .kpi-label { font-size: 0.82rem; color: #6c757d; margin-top: 4px; }
        .kpi-card .kpi-icon { font-size: 1.5rem; margin-bottom: 8px; color: var(--gold); }

        .section-title {
            font-size: 1rem;
            font-weight: 700;
            color: #1c2437;
            border-left: 4px solid var(--gold);
            padding-left: 10px;
            margin: 28px 0 14px;
        }

        .user-card {
            background: white;
            border-radius: 12px;
            padding: 18px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            height: 100%;
            transition: transform .15s;
        }
        .user-card:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(0,0,0,0.1); }
        .user-card .card-name {
            font-weight: 700;
            font-size: 0.92rem;
            color: #1c2437;
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .user-card .card-date { font-size: 0.75rem; color: #aaa; margin-bottom: 12px; }
        .user-card .stat { display: flex; justify-content: space-between; font-size: 0.8rem; color: #555; padding: 3px 0; border-bottom: 1px solid #f0f0f0; }
        .user-card .stat:last-child { border-bottom: none; }
        .user-card .stat span:last-child { font-weight: 600; color: #1c2437; }
        .badge-rol { font-size: 0.68rem; padding: 2px 8px; border-radius: 20px; }
        .badge-client { background: #e8f5e9; color: #2e7d32; }
        .badge-consultant { background: #e3f2fd; color: #1565c0; }

        .activity-table td { font-size: 0.82rem; vertical-align: middle; }
        .op-badge { font-size: 0.7rem; padding: 2px 8px; border-radius: 12px; font-weight: 600; }
        .op-select  { background: #e3f2fd; color: #1565c0; }
        .op-message { background: #f3e5f5; color: #6a1b9a; }
        .op-write   { background: #fff3e0; color: #e65100; }
        .detalle-cell { max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #555; }
    </style>
</head>
<body>

<div class="topbar">
    <img src="<?= base_url('otto/otto.png?v=2') ?>" alt="Otto">
    <h1>Otto — Monitor de Conversaciones</h1>
    <a href="<?= base_url('/consultor/dashboard') ?>" class="back-btn"><i class="fas fa-arrow-left me-1"></i> Dashboard</a>
</div>

<div class="container-fluid px-4 py-4">

    <!-- KPIs -->
    <div class="row g-3 mb-2">
        <div class="col-6 col-md-2">
            <div class="kpi-card">
                <div class="kpi-icon"><i class="fas fa-building"></i></div>
                <div class="kpi-value"><?= $kpis['clientes_activos'] ?? 0 ?></div>
                <div class="kpi-label">Clientes activos</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="kpi-card">
                <div class="kpi-icon"><i class="fas fa-user-tie"></i></div>
                <div class="kpi-value"><?= $kpis['consultores_activos'] ?? 0 ?></div>
                <div class="kpi-label">Consultores activos</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="kpi-card">
                <div class="kpi-icon"><i class="fas fa-comments"></i></div>
                <div class="kpi-value"><?= number_format($kpis['total_mensajes'] ?? 0) ?></div>
                <div class="kpi-label">Mensajes enviados</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="kpi-card">
                <div class="kpi-icon"><i class="fas fa-database"></i></div>
                <div class="kpi-value"><?= number_format($kpis['total_consultas'] ?? 0) ?></div>
                <div class="kpi-label">Consultas SQL</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="kpi-card">
                <div class="kpi-icon"><i class="fas fa-pen"></i></div>
                <div class="kpi-value"><?= number_format($kpis['total_escrituras'] ?? 0) ?></div>
                <div class="kpi-label">Escrituras ejecutadas</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="kpi-card">
                <div class="kpi-icon"><i class="fas fa-calendar-check"></i></div>
                <div class="kpi-value"><?= $kpis['dias_con_actividad'] ?? 0 ?></div>
                <div class="kpi-label">Días con actividad</div>
            </div>
        </div>
    </div>

    <!-- CLIENTES -->
    <div class="section-title"><i class="fas fa-building me-2"></i>Uso por Cliente</div>
    <?php if (empty($clientes)): ?>
        <p class="text-muted">Ningún cliente ha usado Otto aún.</p>
    <?php else: ?>
    <div class="row g-3">
        <?php foreach ($clientes as $c): ?>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="user-card">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge badge-rol badge-client">Cliente</span>
                </div>
                <div class="card-name" title="<?= esc($c['nombre']) ?>"><?= esc($c['nombre']) ?></div>
                <div class="card-date"><i class="fas fa-clock me-1"></i><?= date('d/m/Y H:i', strtotime($c['ultima_actividad'])) ?></div>
                <div class="stat"><span>Mensajes</span><span><?= $c['mensajes'] ?></span></div>
                <div class="stat"><span>Consultas SQL</span><span><?= $c['consultas'] ?></span></div>
                <div class="stat"><span>Sesiones (días)</span><span><?= $c['sesiones'] ?></span></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- CONSULTORES -->
    <div class="section-title"><i class="fas fa-user-tie me-2"></i>Uso por Consultor</div>
    <?php if (empty($consultores)): ?>
        <p class="text-muted">Ningún consultor ha usado Otto aún.</p>
    <?php else: ?>
    <div class="row g-3">
        <?php foreach ($consultores as $c): ?>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="user-card">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge badge-rol badge-consultant">Consultor</span>
                </div>
                <div class="card-name" title="<?= esc($c['nombre']) ?>"><?= esc($c['nombre']) ?></div>
                <div class="card-date"><i class="fas fa-clock me-1"></i><?= date('d/m/Y H:i', strtotime($c['ultima_actividad'])) ?></div>
                <div class="stat"><span>Mensajes</span><span><?= $c['mensajes'] ?></span></div>
                <div class="stat"><span>Consultas SQL</span><span><?= $c['consultas'] ?></span></div>
                <div class="stat"><span>Escrituras</span><span><?= $c['escrituras'] ?></span></div>
                <div class="stat"><span>Días activo</span><span><?= $c['dias_activo'] ?></span></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- ACTIVIDAD RECIENTE -->
    <div class="section-title"><i class="fas fa-history me-2"></i>Actividad Reciente</div>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table id="tablaActividad" class="table table-hover mb-0 activity-table">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Operación</th>
                        <th>Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recientes as $r): ?>
                    <?php
                        $opClass = match(true) {
                            $r['tipo_operacion'] === 'tool_select'  => 'op-select',
                            $r['tipo_operacion'] === 'user_message' => 'op-message',
                            default                                 => 'op-write',
                        };
                        $opLabel = match($r['tipo_operacion']) {
                            'user_message' => 'Mensaje',
                            'tool_select'  => 'SELECT',
                            'tool_update'  => 'UPDATE',
                            'tool_insert'  => 'INSERT',
                            'tool_delete'  => 'DELETE',
                            default        => $r['tipo_operacion'],
                        };
                    ?>
                    <tr>
                        <td class="text-nowrap"><?= date('d/m H:i', strtotime($r['created_at'])) ?></td>
                        <td><?= esc($r['nombre']) ?></td>
                        <td><span class="badge badge-rol <?= $r['rol'] === 'client' ? 'badge-client' : 'badge-consultant' ?>"><?= $r['rol'] ?></span></td>
                        <td><span class="op-badge <?= $opClass ?>"><?= $opLabel ?></span></td>
                        <td class="detalle-cell" title="<?= esc($r['detalle']) ?>"><?= esc(mb_substr($r['detalle'] ?? '', 0, 120)) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script>
$('#tablaActividad').DataTable({
    order: [[0, 'desc']],
    pageLength: 15,
    language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
    columnDefs: [{ orderable: false, targets: 4 }]
});
</script>
</body>
</html>
