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
        .container { margin-top: 80px; }
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
        .filter-row { margin-bottom: 15px; }
        .filter-row select { font-size: 13px; }
    </style>
</head>
<body>
    <?= view('partials/navbar') ?>

    <div class="container-fluid px-4">
        <div class="header-bar">
            <h4><i class="fas fa-clipboard-check me-2"></i> Auditoría de Visitas</h4>
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

        <!-- Filtros -->
        <div class="row filter-row">
            <div class="col-md-3">
                <select id="filtroConsultor" class="form-control form-control-sm">
                    <option value="">Todos los consultores</option>
                    <?php foreach ($consultores as $c): ?>
                        <option value="<?= esc($c['nombre_consultor']) ?>"><?= esc($c['nombre_consultor']) ?></option>
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
                    <option value="cumple">Cumple</option>
                    <option value="incumple">Incumple</option>
                    <option value="pendiente">Pendiente</option>
                </select>
            </div>
            <div class="col-md-2">
                <select id="filtroEstatusMes" class="form-control form-control-sm">
                    <option value="">Estatus Mes: Todos</option>
                    <option value="cumple">Cumple</option>
                    <option value="incumple">Incumple</option>
                    <option value="pendiente">Pendiente</option>
                </select>
            </div>
        </div>

        <table id="tablaAuditoria" class="table table-striped table-bordered" style="width:100%">
            <thead class="thead-dark">
                <tr>
                    <th>Cliente</th>
                    <th>Consultor</th>
                    <th>Consultor Ext.</th>
                    <th>Estándar</th>
                    <th>Mes Esperado</th>
                    <th>Fecha Agendada</th>
                    <th>Fecha Acta</th>
                    <th>Estatus Agenda</th>
                    <th>Estatus Mes</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $mesesNombre = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',
                    7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
                ?>
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

        // Filtros
        $('#filtroConsultor').on('change', function() {
            table.column(1).search(this.value).draw();
        });
        $('#filtroMes').on('change', function() {
            table.column(4).search(this.value).draw();
        });
        $('#filtroEstatusAgenda').on('change', function() {
            table.column(7).search(this.value).draw();
        });
        $('#filtroEstatusMes').on('change', function() {
            table.column(8).search(this.value).draw();
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
