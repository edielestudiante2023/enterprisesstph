<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervisión de Agendamientos - SG-SST</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--secondary-dark) 100%);
            color: white;
            padding: 25px 30px;
            border-radius: 15px;
            margin-bottom: 25px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
        }
        .page-header h2 { color: var(--gold-primary); margin: 0; }
        .page-header p { color: #adb5bd; margin: 5px 0 0; }

        .card-consultor {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
        }
        .card-consultor:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            color: inherit;
        }
        .pct-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
        }
        .pct-high { background: #d4edda; color: #155724; }
        .pct-mid  { background: #fff3cd; color: #856404; }
        .pct-low  { background: #f8d7da; color: #721c24; }

        .stat-number { font-size: 24px; font-weight: 700; }
        .stat-label  { font-size: 12px; color: #6c757d; text-transform: uppercase; }

        .badge-sin-agendar {
            background: #f8d7da;
            color: #721c24;
            font-size: 12px;
            padding: 4px 10px;
            border-radius: 10px;
        }
        .badge-agendado {
            background: #d4edda;
            color: #155724;
            font-size: 12px;
            padding: 4px 10px;
            border-radius: 10px;
        }
        .btn-back {
            background: var(--gold-primary);
            color: var(--primary-dark);
            border: none;
            font-weight: 600;
        }
        .btn-back:hover { background: var(--gold-secondary); color: var(--primary-dark); }
    </style>
</head>
<body>
    <div class="container-xl">
        <!-- Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><i class="fas fa-calendar-check me-2"></i>Supervisión de Agendamientos</h2>
                    <p>Control de visitas agendadas por consultor — <?= date('F Y') ?></p>
                </div>
                <a href="/dashboardconsultant" class="btn btn-back">
                    <i class="fas fa-arrow-left me-1"></i> Volver
                </a>
            </div>
        </div>

        <!-- Resumen por consultor -->
        <h5 class="mb-3"><i class="fas fa-users me-2"></i>Resumen por Consultor</h5>
        <div class="row g-3 mb-4">
            <?php foreach ($resumen as $r): ?>
            <div class="col-md-4 col-lg-3">
                <a href="/admin/agendamientos/consultor/<?= $r['id_consultor'] ?>" class="card card-consultor d-block">
                    <div class="card-body text-center">
                        <div class="mb-2">
                            <?php if (!empty($r['foto_consultor'])): ?>
                                <img src="/uploads/<?= esc($r['foto_consultor']) ?>" class="rounded-circle" width="50" height="50" style="object-fit:cover;">
                            <?php else: ?>
                                <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center" style="width:50px;height:50px;">
                                    <i class="fas fa-user-tie" style="color:var(--gold-primary);font-size:20px;"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <h6 class="mb-2"><?= esc($r['nombre_consultor']) ?></h6>
                        <div class="row text-center mb-2">
                            <div class="col-4">
                                <div class="stat-number"><?= $r['total_activos'] ?></div>
                                <div class="stat-label">Activos</div>
                            </div>
                            <div class="col-4">
                                <div class="stat-number text-success"><?= $r['agendados'] ?></div>
                                <div class="stat-label">Agendados</div>
                            </div>
                            <div class="col-4">
                                <div class="stat-number <?= $r['sin_agendar'] > 0 ? 'text-danger' : 'text-success' ?>"><?= $r['sin_agendar'] ?></div>
                                <div class="stat-label">Sin agendar</div>
                            </div>
                        </div>
                        <span class="pct-badge <?php
                            if ($r['pct_cumplimiento'] >= 80) echo 'pct-high';
                            elseif ($r['pct_cumplimiento'] >= 50) echo 'pct-mid';
                            else echo 'pct-low';
                        ?>">
                            <?= $r['pct_cumplimiento'] ?>% cumplimiento
                        </span>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Lista detallada de clientes -->
        <h5 class="mb-3"><i class="fas fa-list me-2"></i>Detalle por Cliente</h5>

        <!-- Filtro por consultor -->
        <div class="mb-3" style="max-width:350px;">
            <select id="filterConsultor" class="form-select form-select-sm">
                <option value="">Todos los consultores</option>
                <?php foreach ($resumen as $r): ?>
                    <option value="<?= esc($r['nombre_consultor']) ?>"><?= esc($r['nombre_consultor']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="card border-0 shadow-sm" style="border-radius:12px;">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="tablaClientes">
                        <thead style="background:var(--primary-dark); color:white;">
                            <tr>
                                <th style="border-radius:12px 0 0 0;">Cliente</th>
                                <th>Consultor</th>
                                <th>Última Visita</th>
                                <th>Próxima Visita</th>
                                <th>Frecuencia</th>
                                <th style="border-radius:0 12px 0 0;">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clientes as $cli): ?>
                            <tr data-consultor="<?= esc($cli['nombre_consultor']) ?>">
                                <td>
                                    <strong><?= esc($cli['nombre_cliente']) ?></strong>
                                    <?php if ($cli['correo_cliente']): ?>
                                        <div style="font-size:11px;" class="text-muted"><?= esc($cli['correo_cliente']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($cli['nombre_consultor']) ?></td>
                                <td>
                                    <?php if ($cli['ultima_visita']): ?>
                                        <?= date('d/m/Y', strtotime($cli['ultima_visita'])) ?>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($cli['proximo_agendamiento'])): ?>
                                        <?= date('d/m/Y', strtotime($cli['proximo_agendamiento']['fecha_visita'])) ?>
                                        <span class="text-muted"><?= date('g:i A', strtotime($cli['proximo_agendamiento']['hora_visita'])) ?></span>
                                    <?php else: ?>
                                        <span class="badge-sin-agendar"><i class="fas fa-exclamation-circle me-1"></i>Sin agendar</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($cli['proximo_agendamiento']['frecuencia'])): ?>
                                        <?= ucfirst($cli['proximo_agendamiento']['frecuencia']) ?>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($cli['proximo_agendamiento'])): ?>
                                        <?php
                                        $est = $cli['proximo_agendamiento']['estado'];
                                        $emailOk = $cli['proximo_agendamiento']['email_enviado'];
                                        ?>
                                        <span class="badge-agendado">
                                            <?= ucfirst($est) ?>
                                            <?php if ($emailOk): ?>
                                                <i class="fas fa-envelope-circle-check ms-1"></i>
                                            <?php endif; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge-sin-agendar">Pendiente</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            var table = $('#tablaClientes').DataTable({
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
                pageLength: 25,
                order: [[3, 'asc']], // por próxima visita
                columnDefs: [{ orderable: false, targets: [] }]
            });

            // Filtro por consultor
            $('#filterConsultor').on('change', function() {
                var val = $(this).val();
                table.column(1).search(val ? '^' + $.fn.dataTable.util.escapeRegex(val) + '$' : '', true, false).draw();
            });
        });
    </script>
</body>
</html>
