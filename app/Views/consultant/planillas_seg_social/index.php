<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planillas de Seguridad Social</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        :root {
            --primary-dark: #1a3a5c;
            --gold-primary: #d4a843;
            --gold-secondary: #c19a3e;
        }
        body {
            background-color: #f4f4f7;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar-custom {
            background: linear-gradient(135deg, var(--primary-dark), #2c5f8a);
            padding: 10px 0;
        }
        .navbar-custom img { height: 45px; }
        .page-header {
            background: linear-gradient(135deg, var(--primary-dark), #2c5f8a);
            color: white;
            padding: 25px 30px;
            border-radius: 10px;
            margin-bottom: 25px;
        }
        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            padding: 25px;
        }
        .btn-gold {
            background: linear-gradient(135deg, var(--gold-primary), var(--gold-secondary));
            color: white;
            border: none;
            font-weight: 600;
        }
        .btn-gold:hover { background: linear-gradient(135deg, var(--gold-secondary), #b08930); color: white; }
        .badge-enviado { background-color: #28a745; }
        .badge-sin-enviar { background-color: #6c757d; }
        .btn-enviar {
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
            color: white;
            border: none;
        }
        .btn-enviar:hover { background: linear-gradient(135deg, #0b5ed7, #094db8); color: white; }
        .btn-enviar:disabled { opacity: 0.6; cursor: not-allowed; }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-custom">
        <div class="container-fluid d-flex justify-content-between align-items-center px-4">
            <a href="<?= base_url('/dashboardconsultant') ?>">
                <img src="<?= base_url('assets/img/logotipo_enterprisesst.png') ?>" alt="Logo">
            </a>
            <a href="<?= base_url('/dashboardconsultant') ?>" class="btn btn-outline-light btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Volver al Dashboard
            </a>
        </div>
    </nav>

    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h3 class="mb-1"><i class="fas fa-file-invoice-dollar me-2"></i>Planillas de Seguridad Social</h3>
                <p class="mb-0 opacity-75">Repositorio de planillas y envío masivo a clientes activos</p>
            </div>
            <a href="<?= base_url('planillas-seguridad-social/create') ?>" class="btn btn-gold btn-lg mt-2 mt-md-0">
                <i class="fas fa-plus me-2"></i>Subir Planilla
            </a>
        </div>

        <!-- Flash messages -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Table -->
        <div class="table-container">
            <table id="tablaPlanillas" class="table table-hover table-striped" style="width:100%">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Mes de Aportes</th>
                        <th>Fecha de Cargue</th>
                        <th>PDF</th>
                        <th>Estado Envío</th>
                        <th>Clientes Enviados</th>
                        <th>Fecha de Envío</th>
                        <th>Notas</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($planillas as $i => $p): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><strong><?= esc($p['mes_aportes']) ?></strong></td>
                        <td><?= date('d/m/Y H:i', strtotime($p['fecha_cargue'])) ?></td>
                        <td>
                            <a href="<?= base_url('planillas-seguridad-social/download/' . $p['id']) ?>" class="btn btn-sm btn-outline-primary" title="Descargar PDF">
                                <i class="fas fa-file-pdf me-1"></i>Descargar
                            </a>
                        </td>
                        <td>
                            <?php if ($p['estado_envio'] === 'enviado'): ?>
                                <span class="badge badge-enviado rounded-pill px-3 py-2">
                                    <i class="fas fa-check me-1"></i>Enviado
                                </span>
                            <?php else: ?>
                                <span class="badge badge-sin-enviar rounded-pill px-3 py-2">
                                    <i class="fas fa-clock me-1"></i>Sin enviar
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($p['cantidad_envios'] > 0): ?>
                                <span class="badge bg-info rounded-pill px-3 py-2"><?= $p['cantidad_envios'] ?> clientes</span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= $p['fecha_envio'] ? date('d/m/Y H:i', strtotime($p['fecha_envio'])) : '<span class="text-muted">-</span>' ?>
                        </td>
                        <td><?= esc($p['notas'] ?? '') ?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <!-- Enviar -->
                                <button type="button" class="btn btn-enviar btn-enviar-planilla"
                                        data-id="<?= $p['id'] ?>"
                                        data-mes="<?= esc($p['mes_aportes']) ?>"
                                        title="Enviar a clientes activos">
                                    <i class="fas fa-paper-plane me-1"></i>Enviar
                                </button>
                                <!-- Editar -->
                                <a href="<?= base_url('planillas-seguridad-social/edit/' . $p['id']) ?>" class="btn btn-warning" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <!-- Eliminar -->
                                <button type="button" class="btn btn-danger btn-eliminar-planilla"
                                        data-id="<?= $p['id'] ?>"
                                        data-mes="<?= esc($p['mes_aportes']) ?>"
                                        title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Form oculto para eliminar -->
    <form id="formEliminar" method="post" style="display:none;">
        <?= csrf_field() ?>
    </form>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    $(document).ready(function() {
        $('#tablaPlanillas').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            },
            order: [[1, 'desc']],
            pageLength: 25,
            responsive: true
        });

        // Enviar a clientes activos
        $(document).on('click', '.btn-enviar-planilla', function() {
            const btn = $(this);
            const id = btn.data('id');
            const mes = btn.data('mes');

            Swal.fire({
                title: '¿Enviar planilla?',
                html: `Se enviará la planilla de <strong>${mes}</strong> como adjunto PDF a <strong>todos los clientes activos</strong> por correo electrónico.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-paper-plane me-1"></i> Sí, enviar',
                cancelButtonText: 'Cancelar',
                showLoaderOnConfirm: true,
                allowOutsideClick: () => !Swal.isLoading(),
                preConfirm: () => {
                    return fetch(`<?= base_url('planillas-seguridad-social/enviar/') ?>${id}`, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'application/json',
                            '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success && data.enviados === 0) {
                            throw new Error(data.message);
                        }
                        return data;
                    })
                    .catch(error => {
                        Swal.showValidationMessage(error.message || 'Error al enviar');
                    });
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    const data = result.value;
                    Swal.fire({
                        title: data.success ? '¡Envío completado!' : 'Envío parcial',
                        html: data.message,
                        icon: data.success ? 'success' : 'warning',
                        confirmButtonColor: '#0d6efd'
                    }).then(() => {
                        location.reload();
                    });
                }
            });
        });

        // Eliminar planilla
        $(document).on('click', '.btn-eliminar-planilla', function() {
            const id = $(this).data('id');
            const mes = $(this).data('mes');

            Swal.fire({
                title: '¿Eliminar planilla?',
                html: `Se eliminará permanentemente la planilla de <strong>${mes}</strong> y su archivo PDF.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-trash me-1"></i> Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('formEliminar');
                    form.action = `<?= base_url('planillas-seguridad-social/delete/') ?>${id}`;
                    form.submit();
                }
            });
        });
    });
    </script>
</body>
</html>
