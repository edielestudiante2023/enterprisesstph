<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Auditoría de Visitas</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.7.0/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; color: #343a40; }
        table { background-color: #fff; }
        table.dataTable tbody tr { height: 50px; }
        table.dataTable th, table.dataTable td { white-space: nowrap; font-size: 13px; }
        .badge-cumple { background-color: #28a745; color: #fff; }
        .badge-incumple { background-color: #dc3545; color: #fff; }
        .badge-pendiente { background-color: #ffc107; color: #333; }
        .header-bar {
            background: linear-gradient(135deg, #1c2437, #2c3e50);
            padding: 20px 30px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .header-bar h4 { color: #bd9751; margin: 0; }
        .header-bar p { color: #adb5bd; margin: 0; font-size: 14px; }

        /* Cards clickeables */
        .filter-card {
            cursor: pointer;
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 10px;
            transition: all 0.2s ease;
            border: 2px solid transparent;
            text-align: center;
            min-height: 80px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            width: 100%;
        }
        .cards-row { display: flex; flex-wrap: wrap; gap: 10px; }
        .cards-row > .card-col { flex: 1; min-width: 120px; }
        .filter-row { margin-bottom: 15px; }
        .filter-row select { font-size: 13px; }
        .filter-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .filter-card.active { border-color: #bd9751; box-shadow: 0 0 0 2px #bd9751; }
        .filter-card .card-count { font-size: 24px; font-weight: bold; }
        .filter-card .card-label { font-size: 11px; margin-top: 2px; }

        /* Colores de cards */
        .card-consultor { background: linear-gradient(135deg, #2c3e50, #34495e); color: #fff; }
        .card-externo { background: linear-gradient(135deg, #8e44ad, #9b59b6); color: #fff; }
        .card-cumple { background: linear-gradient(135deg, #27ae60, #2ecc71); color: #fff; }
        .card-incumple { background: linear-gradient(135deg, #c0392b, #e74c3c); color: #fff; }
        .card-pendiente-status { background: linear-gradient(135deg, #f39c12, #f1c40f); color: #333; }
        .card-all { background: linear-gradient(135deg, #2980b9, #3498db); color: #fff; }

        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <nav style="background-color: white; position: fixed; top: 0; width: 100%; z-index: 1000; padding: 10px 0; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; max-width: 1200px; margin: 0 auto; padding: 0 20px;">
            <div><a href="https://dashboard.cycloidtalent.com/login"><img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo" style="height: 100px;"></a></div>
            <div><a href="https://cycloidtalent.com/index.php/consultoria-sst"><img src="<?= base_url('uploads/logosst.png') ?>" alt="SST Logo" style="height: 100px;"></a></div>
            <div><a href="https://cycloidtalent.com/"><img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloids Logo" style="height: 100px;"></a></div>
            <div style="text-align: center;">
                <h2 style="margin: 0; font-size: 16px;">Ir a Dashboard</h2>
                <a href="<?= base_url('/dashboardconsultant') ?>" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; font-size: 14px; margin-top: 5px;">Ir a DashBoard</a>
            </div>
        </div>
    </nav>
    <div style="height: 160px;"></div>

    <div class="container-fluid px-4">
        <div class="header-bar">
            <h4><i class="fas fa-clipboard-check"></i> Auditoría de Visitas</h4>
            <p>Control de cumplimiento de visitas agendadas por consultor y cliente</p>
        </div>

        <?php if (session()->getFlashdata('msg')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert" style="font-size:13px;">
                <?= session()->getFlashdata('msg') ?>
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert" style="font-size:13px;">
                <?= session()->getFlashdata('error') ?>
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        <?php endif; ?>

        <?php
        // Preparar datos para cards
        $porConsultor = [];
        $porExterno = [];
        $statusAgenda = ['cumple' => 0, 'incumple' => 0, 'pendiente' => 0];
        $statusMes = ['cumple' => 0, 'incumple' => 0, 'pendiente' => 0];
        $mesesNombre = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',
            7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];

        foreach ($ciclos as $c) {
            $nc = $c['nombre_consultor'] ?? 'Sin consultor';
            $porConsultor[$nc] = ($porConsultor[$nc] ?? 0) + 1;

            $ext = trim($c['consultor_externo'] ?? '');
            if ($ext !== '' && $ext !== '—') {
                $porExterno[$ext] = ($porExterno[$ext] ?? 0) + 1;
            }

            $sa = $c['estatus_agenda'] ?? 'pendiente';
            $sm = $c['estatus_mes'] ?? 'pendiente';
            $statusAgenda[$sa] = ($statusAgenda[$sa] ?? 0) + 1;
            $statusMes[$sm] = ($statusMes[$sm] ?? 0) + 1;
        }
        asort($porConsultor);
        asort($porExterno);
        ?>

        <!-- ═══ CARDS: CONSULTOR INTERNO ═══ -->
        <div class="section-title"><i class="fas fa-user-tie"></i> Consultor Interno</div>
        <div class="cards-row">
            <div class="card-col">
                <div class="filter-card card-all active" data-filter="consultor" data-value="" title="Mostrar todos">
                    <div class="card-count"><?= count($ciclos) ?></div>
                    <div class="card-label">Todos</div>
                </div>
            </div>
            <?php foreach ($porConsultor as $nombre => $count): ?>
            <div class="card-col">
                <div class="filter-card card-consultor" data-filter="consultor" data-value="<?= esc($nombre) ?>" title="<?= esc($nombre) ?>">
                    <div class="card-count"><?= $count ?></div>
                    <div class="card-label"><?= esc($nombre) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- ═══ CARDS: CONSULTOR EXTERNO ═══ -->
        <?php if (!empty($porExterno)): ?>
        <div class="section-title"><i class="fas fa-user-shield"></i> Consultor Externo</div>
        <div class="cards-row">
            <div class="card-col">
                <div class="filter-card card-all active" data-filter="externo" data-value="" title="Mostrar todos">
                    <div class="card-count"><?= count($ciclos) ?></div>
                    <div class="card-label">Todos</div>
                </div>
            </div>
            <?php foreach ($porExterno as $nombre => $count): ?>
            <div class="card-col">
                <div class="filter-card card-externo" data-filter="externo" data-value="<?= esc($nombre) ?>" title="<?= esc($nombre) ?>">
                    <div class="card-count"><?= $count ?></div>
                    <div class="card-label"><?= esc($nombre) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- ═══ CARDS: ESTATUS (lado a lado) ═══ -->
        <div class="row">
            <div class="col-md-6">
                <div class="section-title"><i class="fas fa-calendar-check"></i> Estatus Agenda</div>
                <div class="cards-row">
                    <div class="card-col">
                        <div class="filter-card card-all active" data-filter="estatus_agenda" data-value="">
                            <div class="card-count"><?= count($ciclos) ?></div>
                            <div class="card-label">Todos</div>
                        </div>
                    </div>
                    <div class="card-col">
                        <div class="filter-card card-cumple" data-filter="estatus_agenda" data-value="Cumple">
                            <div class="card-count"><?= $statusAgenda['cumple'] ?></div>
                            <div class="card-label">Cumple</div>
                        </div>
                    </div>
                    <div class="card-col">
                        <div class="filter-card card-incumple" data-filter="estatus_agenda" data-value="Incumple">
                            <div class="card-count"><?= $statusAgenda['incumple'] ?></div>
                            <div class="card-label">Incumple</div>
                        </div>
                    </div>
                    <div class="card-col">
                        <div class="filter-card card-pendiente-status" data-filter="estatus_agenda" data-value="Pendiente">
                            <div class="card-count"><?= $statusAgenda['pendiente'] ?></div>
                            <div class="card-label">Pendiente</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="section-title"><i class="fas fa-calendar-alt"></i> Estatus Mes</div>
                <div class="cards-row">
                    <div class="card-col">
                        <div class="filter-card card-all active" data-filter="estatus_mes" data-value="">
                            <div class="card-count"><?= count($ciclos) ?></div>
                            <div class="card-label">Todos</div>
                        </div>
                    </div>
                    <div class="card-col">
                        <div class="filter-card card-cumple" data-filter="estatus_mes" data-value="Cumple">
                            <div class="card-count"><?= $statusMes['cumple'] ?></div>
                            <div class="card-label">Cumple</div>
                        </div>
                    </div>
                    <div class="card-col">
                        <div class="filter-card card-incumple" data-filter="estatus_mes" data-value="Incumple">
                            <div class="card-count"><?= $statusMes['incumple'] ?></div>
                            <div class="card-label">Incumple</div>
                        </div>
                    </div>
                    <div class="card-col">
                        <div class="filter-card card-pendiente-status" data-filter="estatus_mes" data-value="Pendiente">
                            <div class="card-count"><?= $statusMes['pendiente'] ?></div>
                            <div class="card-label">Pendiente</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══ FILTROS DESPLEGABLES ═══ -->
        <div class="row filter-row mt-3">
            <div class="col-md-3">
                <select id="filtroConsultor" class="form-control form-control-sm">
                    <option value="">Todos los consultores</option>
                    <?php foreach ($consultores as $co): ?>
                        <option value="<?= esc($co['nombre_consultor']) ?>"><?= esc($co['nombre_consultor']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select id="filtroMes" class="form-control form-control-sm">
                    <option value="">Todos los meses</option>
                    <?php foreach ($meses as $num => $nombre): ?>
                        <option value="<?= $nombre ?>"><?= $nombre ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select id="filtroEstatusAgenda" class="form-control form-control-sm">
                    <option value="">Estatus Agenda: Todos</option>
                    <option value="Cumple">Cumple</option>
                    <option value="Incumple">Incumple</option>
                    <option value="Pendiente">Pendiente</option>
                </select>
            </div>
            <div class="col-md-2">
                <select id="filtroEstatusMes" class="form-control form-control-sm">
                    <option value="">Estatus Mes: Todos</option>
                    <option value="Cumple">Cumple</option>
                    <option value="Incumple">Incumple</option>
                    <option value="Pendiente">Pendiente</option>
                </select>
            </div>
        </div>

        <!-- ═══ TABLA ═══ -->
        <table id="tablaAuditoria" class="table table-striped table-bordered" style="width:100%">
            <thead class="thead-dark">
                <tr>
                    <th>Cliente</th>
                    <th>Consultor</th>
                    <th>Consultor Ext.</th>
                    <th>Periodicidad</th>
                    <th>Mes Esperado</th>
                    <th>Fecha Agendada</th>
                    <th>Fecha Acta</th>
                    <th>Estatus Agenda</th>
                    <th>Estatus Mes</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ciclos as $c): ?>
                    <tr>
                        <td><?= esc($c['nombre_cliente'] ?? '—') ?></td>
                        <td><?= esc($c['nombre_consultor'] ?? '—') ?></td>
                        <td><?= esc($c['consultor_externo'] ?? '—') ?></td>
                        <td><?= esc($c['estandar'] ?? '—') ?></td>
                        <td><?= $mesesNombre[$c['mes_esperado']] ?? $c['mes_esperado'] ?> <?= $c['anio'] ?></td>
                        <td><?= $c['fecha_agendada'] ? date('d/m/Y', strtotime($c['fecha_agendada'])) : '—' ?></td>
                        <td><?= $c['fecha_acta'] ? date('d/m/Y', strtotime($c['fecha_acta'])) : '—' ?></td>
                        <td>
                            <span class="badge badge-<?= $c['estatus_agenda'] ?>">
                                <?= ucfirst($c['estatus_agenda']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-<?= $c['estatus_mes'] ?>">
                                <?= ucfirst($c['estatus_mes']) ?>
                            </span>
                        </td>
                        <td>
                            <a href="/consultant/auditoria-visitas/edit/<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button class="btn btn-sm btn-outline-danger btn-delete" data-id="<?= $c['id'] ?>" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.0/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.0/js/buttons.html5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    $(document).ready(function() {
        var table = $('#tablaAuditoria').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json' },
            pageLength: 50,
            order: [[4, 'asc'], [0, 'asc']],
            dom: 'Bfrtip',
            buttons: ['copy', 'csv', 'excel']
        });

        // ═══ FILTROS CUSTOM (bypass column().search) ═══
        var activeFilters = {
            consultor: '',
            externo: '',
            mes: '',
            estatus_agenda: '',
            estatus_mes: ''
        };

        // Leer celdas directamente del DOM (bypass cache interno DataTables)
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            var $cells = $(table.row(dataIndex).node()).children('td');
            if (activeFilters.consultor) {
                var t = $cells.eq(1).text().trim();
                if (t.toUpperCase().indexOf(activeFilters.consultor.toUpperCase()) === -1) return false;
            }
            if (activeFilters.externo) {
                var t = $cells.eq(2).text().trim();
                if (t.toUpperCase().indexOf(activeFilters.externo.toUpperCase()) === -1) return false;
            }
            if (activeFilters.mes) {
                var t = $cells.eq(4).text().trim();
                if (t.indexOf(activeFilters.mes) === -1) return false;
            }
            if (activeFilters.estatus_agenda) {
                var t = $cells.eq(7).text().trim();
                if (t.toLowerCase() !== activeFilters.estatus_agenda.toLowerCase()) return false;
            }
            if (activeFilters.estatus_mes) {
                var t = $cells.eq(8).text().trim();
                if (t.toLowerCase() !== activeFilters.estatus_mes.toLowerCase()) return false;
            }
            return true;
        });

        // ═══ DATOS JSON DESDE PHP (evita parsear DOM de DataTables) ═══
        var ciclosRaw = <?= json_encode(array_values(array_map(function($c) {
            $mn = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',
                   7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
            return [
                'con' => $c['nombre_consultor'] ?? 'Sin consultor',
                'ext' => trim($c['consultor_externo'] ?? ''),
                'sa'  => ucfirst($c['estatus_agenda'] ?? 'pendiente'),
                'sm'  => ucfirst($c['estatus_mes'] ?? 'pendiente'),
                'mes' => ($mn[$c['mes_esperado']] ?? $c['mes_esperado']) . ' ' . $c['anio'],
            ];
        }, $ciclos)), JSON_UNESCAPED_UNICODE) ?>;

        function applyFilter(filterType, value) {
            activeFilters[filterType] = (typeof value === 'string') ? value.trim() : value;
            table.draw();
            recalcCards();
        }

        function recalcCards() {
            var counts = { consultor: {}, externo: {}, estatus_agenda: {}, estatus_mes: {} };
            var total = 0;

            ciclosRaw.forEach(function(c) {
                if (activeFilters.consultor && c.con.toUpperCase().indexOf(activeFilters.consultor.toUpperCase()) === -1) return;
                if (activeFilters.externo && c.ext.toUpperCase().indexOf(activeFilters.externo.toUpperCase()) === -1) return;
                if (activeFilters.mes && c.mes.indexOf(activeFilters.mes) === -1) return;
                if (activeFilters.estatus_agenda && c.sa.toLowerCase() !== activeFilters.estatus_agenda.toLowerCase()) return;
                if (activeFilters.estatus_mes && c.sm.toLowerCase() !== activeFilters.estatus_mes.toLowerCase()) return;

                total++;
                counts.consultor[c.con] = (counts.consultor[c.con] || 0) + 1;
                if (c.ext && c.ext !== '—' && c.ext !== '') {
                    counts.externo[c.ext] = (counts.externo[c.ext] || 0) + 1;
                }
                counts.estatus_agenda[c.sa] = (counts.estatus_agenda[c.sa] || 0) + 1;
                counts.estatus_mes[c.sm] = (counts.estatus_mes[c.sm] || 0) + 1;
            });

            $('[data-filter][data-value=""] .card-count').text(total);
            $('.filter-card').each(function() {
                var v = $(this).attr('data-value');
                if (!v) return;
                var ft = $(this).attr('data-filter');
                var c = (counts[ft] && counts[ft][v]) || 0;
                $(this).find('.card-count').text(c);
            });
        }

        // Click en cards → filtrar + sincronizar dropdown
        $(document).on('click', '.filter-card', function() {
            var filterType = $(this).data('filter');
            var value = $(this).data('value') || '';

            $('[data-filter="' + filterType + '"]').removeClass('active');
            $(this).addClass('active');
            applyFilter(filterType, value);

            // Sincronizar dropdown correspondiente
            var dropdownMap = { 'consultor':'#filtroConsultor', 'estatus_agenda':'#filtroEstatusAgenda', 'estatus_mes':'#filtroEstatusMes' };
            if (dropdownMap[filterType]) $(dropdownMap[filterType]).val(value);
        });

        // Dropdowns → filtrar + sincronizar cards
        $('#filtroConsultor').on('change', function() {
            var val = this.value;
            applyFilter('consultor', val);
            $('[data-filter="consultor"]').removeClass('active');
            $('[data-filter="consultor"][data-value="' + val + '"]').addClass('active');
        });
        $('#filtroMes').on('change', function() {
            applyFilter('mes', this.value);
        });
        $('#filtroEstatusAgenda').on('change', function() {
            var val = this.value;
            applyFilter('estatus_agenda', val);
            $('[data-filter="estatus_agenda"]').removeClass('active');
            $('[data-filter="estatus_agenda"][data-value="' + val + '"]').addClass('active');
        });
        $('#filtroEstatusMes').on('change', function() {
            var val = this.value;
            applyFilter('estatus_mes', val);
            $('[data-filter="estatus_mes"]').removeClass('active');
            $('[data-filter="estatus_mes"][data-value="' + val + '"]').addClass('active');
        });

        // Eliminar
        $(document).on('click', '.btn-delete', function() {
            var id = $(this).data('id');
            var row = $(this).closest('tr');
            Swal.fire({
                title: 'Eliminar registro',
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('/consultant/auditoria-visitas/delete/' + id, {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            table.row(row).remove().draw();
                            Swal.fire('Eliminado', data.message, 'success');
                        } else {
                            Swal.fire('Error', data.error, 'error');
                        }
                    });
                }
            });
        });
    });
    </script>
</body>
</html>
