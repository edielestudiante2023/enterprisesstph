<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cliente — <?= esc($client['nombre_cliente']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --color-activo:   #198754;
            --color-inactivo: #dc3545;
            --color-pendiente:#fd7e14;
        }
        body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; }

        /* Navbar */
        .top-navbar {
            background: #fff;
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 1030;
            box-shadow: 0 2px 10px rgba(0,0,0,.08);
            padding: 8px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 80px;
        }
        .top-navbar img { height: 60px; }
        .top-navbar .btn-dashboard {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: #fff; border: none;
            border-radius: 8px;
            padding: 8px 20px;
            font-size: 13px;
            text-decoration: none;
            transition: transform .15s, box-shadow .15s;
        }
        .top-navbar .btn-dashboard:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,123,255,.35);
        }

        /* Layout principal */
        .page-wrapper { padding-top: 100px; padding-bottom: 60px; }

        /* Card base */
        .main-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,.08);
            overflow: hidden;
        }

        /* Header del card según estado */
        .client-header {
            padding: 28px 32px;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .client-header.estado-activo   { background: linear-gradient(135deg, #198754, #20c997); }
        .client-header.estado-inactivo { background: linear-gradient(135deg, #dc3545, #c0392b); }
        .client-header.estado-pendiente{ background: linear-gradient(135deg, #fd7e14, #e67e22); }

        .client-avatar {
            width: 72px; height: 72px;
            border-radius: 50%;
            border: 3px solid rgba(255,255,255,.4);
            object-fit: contain;
            background: rgba(255,255,255,.15);
            padding: 4px;
            flex-shrink: 0;
        }
        .client-avatar-placeholder {
            width: 72px; height: 72px;
            border-radius: 50%;
            background: rgba(255,255,255,.2);
            display: flex; align-items: center; justify-content: center;
            font-size: 28px;
            flex-shrink: 0;
        }
        .client-header-info h4 { margin: 0; font-weight: 700; font-size: 1.25rem; }
        .client-header-info small { opacity: .85; font-size: .82rem; }
        .estado-badge {
            margin-left: auto;
            background: rgba(255,255,255,.25);
            color: #fff;
            border-radius: 20px;
            padding: 6px 16px;
            font-size: .8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .5px;
            white-space: nowrap;
        }

        /* Panel de acciones */
        .action-panel {
            background: #fff;
            border-bottom: 1px solid #e9ecef;
            padding: 20px 32px;
        }
        .action-panel h6 {
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #6c757d;
            margin-bottom: 12px;
        }
        .action-buttons { display: flex; gap: 10px; flex-wrap: wrap; }
        .action-buttons .btn {
            border-radius: 10px;
            padding: 10px 22px;
            font-size: .88rem;
            font-weight: 600;
            display: flex; align-items: center; gap: 7px;
            transition: transform .15s, box-shadow .15s;
        }
        .action-buttons .btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(0,0,0,.15);
        }
        .action-buttons .btn:disabled {
            opacity: .45;
            cursor: not-allowed;
        }

        /* Secciones del formulario */
        .form-body { padding: 28px 32px; background: #fff; }
        .section-card {
            border: 1px solid #e9ecef;
            border-radius: 12px;
            margin-bottom: 20px;
            overflow: hidden;
        }
        .section-card .section-header {
            background: #f8f9fa;
            padding: 12px 20px;
            border-bottom: 1px solid #e9ecef;
            display: flex; align-items: center; gap: 10px;
        }
        .section-card .section-header i {
            width: 32px; height: 32px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: .85rem;
            color: #fff;
        }
        .section-card .section-header span {
            font-weight: 600;
            font-size: .9rem;
            color: #343a40;
        }
        .section-card .section-body { padding: 20px; }

        .form-label {
            font-size: .82rem;
            font-weight: 600;
            color: #495057;
            margin-bottom: 5px;
        }
        .form-label i { width: 16px; color: #6c757d; margin-right: 4px; }
        .form-control, .form-select {
            border-radius: 8px;
            border-color: #dee2e6;
            font-size: .88rem;
            transition: border-color .15s, box-shadow .15s;
        }
        .form-control:focus, .form-select:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 .2rem rgba(13,110,253,.12);
        }

        /* Preview imágenes */
        .img-preview {
            max-height: 80px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            padding: 4px;
            margin-top: 8px;
            background: #f8f9fa;
        }

        /* Botón guardar */
        .btn-guardar {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 12px 32px;
            font-size: .95rem;
            font-weight: 600;
            transition: transform .15s, box-shadow .15s;
        }
        .btn-guardar:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(0,123,255,.35);
            color: #fff;
        }

        /* Modales */
        .modal-header { border-bottom: none; padding-bottom: 0; }
        .modal-icon-box {
            width: 64px; height: 64px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.6rem;
            margin: 0 auto 12px;
        }
        .modal-icon-box.danger  { background: #fff0f0; color: #dc3545; }
        .modal-icon-box.success { background: #f0fff4; color: #198754; }
        .modal-icon-box.warning { background: #fff8e6; color: #fd7e14; }
        .modal-content { border: none; border-radius: 16px; }
        .modal-body { padding: 24px 32px; }
        .modal-footer { border-top: 1px solid #f0f0f0; padding: 16px 24px; }

        /* Footer */
        .site-footer {
            background: #fff;
            border-top: 1px solid #e9ecef;
            padding: 24px;
            text-align: center;
            font-size: .82rem;
            color: #6c757d;
        }
        .site-footer .social-icon img {
            height: 22px; width: 22px;
            filter: grayscale(1);
            transition: filter .2s;
        }
        .site-footer .social-icon:hover img { filter: grayscale(0); }
    </style>
</head>
<body>

<!-- ═══ NAVBAR ═══════════════════════════════════════════════════════════════ -->
<nav class="top-navbar">
    <a href="https://dashboard.cycloidtalent.com/login">
        <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst">
    </a>
    <a href="https://cycloidtalent.com/index.php/consultoria-sst">
        <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST">
    </a>
    <a href="https://cycloidtalent.com/">
        <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloid">
    </a>
    <a href="<?= base_url('/dashboardconsultant') ?>" class="btn-dashboard">
        <i class="fas fa-tachometer-alt me-1"></i> Dashboard
    </a>
</nav>

<!-- ═══ CONTENIDO ════════════════════════════════════════════════════════════ -->
<div class="page-wrapper">
<div class="container" style="max-width: 1000px;">

    <!-- Alertas flash -->
    <?php if (session()->getFlashdata('msg')): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('msg') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb" style="font-size:.83rem;">
            <li class="breadcrumb-item"><a href="<?= base_url('/dashboardconsultant') ?>" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('/listClients') ?>" class="text-decoration-none">Clientes</a></li>
            <li class="breadcrumb-item active"><?= esc($client['nombre_cliente']) ?></li>
        </ol>
    </nav>

    <!-- ═══ CARD PRINCIPAL ══════════════════════════════════════════════════ -->
    <div class="main-card mb-5">

        <!-- Header dinámico según estado -->
        <?php
            $estado = $client['estado'] ?? 'activo';
            $headerClass = 'estado-' . $estado;
            $estadoLabel = strtoupper($estado);
            $estadoIcon  = $estado === 'activo' ? 'fa-circle-check' : ($estado === 'inactivo' ? 'fa-circle-xmark' : 'fa-clock');
        ?>
        <div class="client-header <?= $headerClass ?>">
            <?php if (!empty($client['logo'])): ?>
                <img src="<?= base_url('uploads/' . $client['logo']) ?>" alt="Logo" class="client-avatar">
            <?php else: ?>
                <div class="client-avatar-placeholder"><i class="fas fa-building"></i></div>
            <?php endif; ?>
            <div class="client-header-info">
                <h4><?= esc($client['nombre_cliente']) ?></h4>
                <small>
                    <i class="fas fa-id-card me-1"></i>NIT: <?= esc($client['nit_cliente']) ?>
                    &nbsp;·&nbsp;
                    <i class="fas fa-calendar-plus me-1"></i>Ingreso: <?= esc($client['fecha_ingreso']) ?>
                </small>
            </div>
            <span class="estado-badge"><i class="fas <?= $estadoIcon ?> me-1"></i><?= $estadoLabel ?></span>
        </div>

        <!-- ═══ PANEL DE ACCIONES DE ESTADO ════════════════════════════════ -->
        <div class="action-panel">
            <h6><i class="fas fa-sliders me-1"></i>Acciones de Estado del Cliente</h6>
            <div class="action-buttons">

                <!-- Botón Reactivar -->
                <button type="button"
                    class="btn btn-success <?= $estado === 'activo' ? 'disabled' : '' ?>"
                    <?= $estado === 'activo' ? 'disabled' : 'data-bs-toggle="modal" data-bs-target="#modalReactivar"' ?>>
                    <i class="fas fa-rotate-right"></i> Reactivar
                </button>

                <!-- Botón Pendiente -->
                <button type="button"
                    class="btn btn-warning <?= $estado === 'pendiente' ? 'disabled' : '' ?>"
                    style="color:#fff;"
                    <?= $estado === 'pendiente' ? 'disabled' : 'data-bs-toggle="modal" data-bs-target="#modalPendiente"' ?>>
                    <i class="fas fa-clock"></i> Pendiente
                </button>

                <!-- Botón Retirar -->
                <button type="button"
                    class="btn btn-danger <?= $estado === 'inactivo' ? 'disabled' : '' ?>"
                    <?= $estado === 'inactivo' ? 'disabled' : 'data-bs-toggle="modal" data-bs-target="#modalRetirar"' ?>>
                    <i class="fas fa-user-xmark"></i> Retirar Cliente
                </button>

                <!-- Botón Paz y Salvo -->
                <button type="button"
                    class="btn btn-info"
                    style="color:#fff;"
                    data-bs-toggle="modal" data-bs-target="#modalPazYSalvo">
                    <i class="fas fa-file-circle-check"></i> Paz y Salvo
                </button>

                <div class="ms-auto text-muted d-flex align-items-center" style="font-size:.8rem;">
                    <i class="fas fa-info-circle me-1"></i>
                    El botón del estado actual aparece deshabilitado
                </div>
            </div>
        </div>

        <!-- ═══ FORMULARIO DE EDICIÓN ══════════════════════════════════════ -->
        <div class="form-body">
            <form action="<?= base_url('/updateClient/' . $client['id_cliente']) ?>" method="post" enctype="multipart/form-data">

                <!-- SECCIÓN 1 — Información Básica -->
                <div class="section-card">
                    <div class="section-header">
                        <i class="fas fa-building" style="background:#4361ee;"></i>
                        <span>Información Básica</span>
                    </div>
                    <div class="section-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label"><i class="fas fa-calendar-plus"></i>Fecha de Ingreso</label>
                                <input type="date" name="fecha_ingreso" value="<?= esc($client['fecha_ingreso']) ?>" class="form-control" readonly title="No editable — es el historial del cliente">
                                <small class="text-muted" style="font-size:.74rem;"><i class="fas fa-lock me-1"></i>Campo histórico</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><i class="fas fa-id-card"></i>NIT Cliente</label>
                                <input type="text" name="nit_cliente" value="<?= esc($client['nit_cliente']) ?>" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><i class="fas fa-building"></i>Nombre Cliente</label>
                                <input type="text" name="nombre_cliente" value="<?= esc($client['nombre_cliente']) ?>" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><i class="fas fa-user"></i>Usuario</label>
                                <input type="text" name="usuario" value="<?= esc($client['usuario']) ?>" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><i class="fas fa-calendar-xmark"></i>Fecha Fin de Contrato</label>
                                <input type="date" name="fecha_fin_contrato" value="<?= esc($client['fecha_fin_contrato']) ?>" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><i class="fas fa-city"></i>Ciudad</label>
                                <input type="text" name="ciudad_cliente" value="<?= esc($client['ciudad_cliente']) ?>" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><i class="fas fa-list-check"></i>Tipo de Servicio</label>
                                <select name="estandares" class="form-select">
                                    <option value="Mensual"    <?= $client['estandares'] === 'Mensual'    ? 'selected' : '' ?>>Mensual</option>
                                    <option value="Bimensual"  <?= $client['estandares'] === 'Bimensual'  ? 'selected' : '' ?>>Bimensual</option>
                                    <option value="Trimestral" <?= $client['estandares'] === 'Trimestral' ? 'selected' : '' ?>>Trimestral</option>
                                    <option value="Proyecto"   <?= $client['estandares'] === 'Proyecto'   ? 'selected' : '' ?>>Proyecto</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><i class="fas fa-code-branch"></i>Código Actividad Económica</label>
                                <input type="text" name="codigo_actividad_economica" value="<?= esc($client['codigo_actividad_economica']) ?>" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><i class="fas fa-user-tie"></i>Consultor Asignado</label>
                                <select name="id_consultor" class="form-select">
                                    <?php foreach ($consultants as $consultant): ?>
                                        <option value="<?= $consultant['id_consultor'] ?>"
                                            <?= $consultant['id_consultor'] == $client['id_consultor'] ? 'selected' : '' ?>>
                                            <?= esc($consultant['nombre_consultor']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 2 — Contacto -->
                <div class="section-card">
                    <div class="section-header">
                        <i class="fas fa-address-book" style="background:#7209b7;"></i>
                        <span>Información de Contacto</span>
                    </div>
                    <div class="section-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label"><i class="fas fa-envelope"></i>Correo Cliente</label>
                                <input type="email" name="correo_cliente" value="<?= esc($client['correo_cliente']) ?>" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label"><i class="fas fa-phone"></i>Teléfono 1</label>
                                <input type="text" name="telefono_1_cliente" value="<?= esc($client['telefono_1_cliente']) ?>" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label"><i class="fas fa-phone-flip"></i>Teléfono 2</label>
                                <input type="text" name="telefono_2_cliente" value="<?= esc($client['telefono_2_cliente'] ?? '') ?>" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="fas fa-map-marker-alt"></i>Dirección</label>
                                <input type="text" name="direccion_cliente" value="<?= esc($client['direccion_cliente']) ?>" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="fas fa-user-gear"></i>Persona de Contacto para Compras</label>
                                <input type="text" name="persona_contacto_compras" value="<?= esc($client['persona_contacto_compras']) ?>" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 3 — Representante Legal -->
                <div class="section-card">
                    <div class="section-header">
                        <i class="fas fa-scale-balanced" style="background:#d62828;"></i>
                        <span>Representante Legal</span>
                    </div>
                    <div class="section-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label"><i class="fas fa-user-shield"></i>Nombre del Representante Legal</label>
                                <input type="text" name="nombre_rep_legal" value="<?= esc($client['nombre_rep_legal']) ?>" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="fas fa-id-badge"></i>Cédula del Representante Legal</label>
                                <input type="text" name="cedula_rep_legal" value="<?= esc($client['cedula_rep_legal']) ?>" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 4 — Archivos -->
                <div class="section-card">
                    <div class="section-header">
                        <i class="fas fa-paperclip" style="background:#f77f00;"></i>
                        <span>Archivos del Cliente</span>
                    </div>
                    <div class="section-body">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label"><i class="fas fa-image"></i>Logo</label>
                                <input type="file" name="logo" class="form-control" accept="image/*">
                                <?php if (!empty($client['logo'])): ?>
                                    <img src="<?= base_url('uploads/' . $client['logo']) ?>" alt="Logo actual" class="img-preview">
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="fas fa-signature"></i>Firma Representante Legal</label>
                                <input type="file" name="firma_representante_legal" class="form-control" accept="image/*">
                                <?php if (!empty($client['firma_representante_legal'])): ?>
                                    <img src="<?= base_url('uploads/' . $client['firma_representante_legal']) ?>" alt="Firma actual" class="img-preview">
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones del formulario -->
                <div class="d-flex align-items-center gap-3 mt-2">
                    <button type="submit" class="btn btn-guardar">
                        <i class="fas fa-floppy-disk me-2"></i>Guardar Cambios
                    </button>
                    <a href="<?= base_url('/listClients') ?>" class="btn btn-outline-secondary rounded-3">
                        <i class="fas fa-arrow-left me-1"></i> Volver al listado
                    </a>
                </div>

            </form>
        </div><!-- /form-body -->
    </div><!-- /main-card -->
</div><!-- /container -->
</div><!-- /page-wrapper -->

<!-- ═══ MODAL: REACTIVAR ════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalReactivar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div class="modal-icon-box success"><i class="fas fa-rotate-right"></i></div>
                <h5 class="fw-bold mb-2">Reactivar Cliente</h5>
                <p class="text-muted mb-3">
                    Esta acción pondrá al cliente en estado <strong>Activo</strong> y
                    <strong class="text-danger">eliminará permanentemente</strong> todos los registros relacionados en:
                </p>
                <ul class="list-group list-group-flush text-start mb-3">
                    <li class="list-group-item py-1 px-0"><i class="fas fa-table me-2 text-danger"></i>Plan de Trabajo Anual</li>
                    <li class="list-group-item py-1 px-0"><i class="fas fa-table me-2 text-danger"></i>Cronograma de Capacitación</li>
                    <li class="list-group-item py-1 px-0"><i class="fas fa-table me-2 text-danger"></i>Pendientes</li>
                    <li class="list-group-item py-1 px-0"><i class="fas fa-table me-2 text-danger"></i>Vencimientos y Mantenimientos</li>
                </ul>
                <div class="alert alert-success py-2 mb-0" style="font-size:.83rem;">
                    <i class="fas fa-shield-check me-1"></i>
                    Se conservarán: <strong>Nombre, NIT y Fecha de Ingreso</strong>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
                <form action="<?= base_url('/cliente/reactivar/' . $client['id_cliente']) ?>" method="post" class="d-inline">
                    <button type="submit" class="btn btn-success rounded-3 fw-semibold">
                        <i class="fas fa-rotate-right me-1"></i> Sí, Reactivar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ═══ MODAL: PENDIENTE ════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalPendiente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div class="modal-icon-box warning"><i class="fas fa-clock"></i></div>
                <h5 class="fw-bold mb-2">Marcar como Pendiente</h5>
                <p class="text-muted mb-0">
                    El cliente pasará a estado <strong>Pendiente</strong>.<br>
                    No se eliminarán ni modificarán sus actividades relacionadas.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
                <form action="<?= base_url('/cliente/pendiente/' . $client['id_cliente']) ?>" method="post" class="d-inline">
                    <button type="submit" class="btn btn-warning rounded-3 fw-semibold" style="color:#fff;">
                        <i class="fas fa-clock me-1"></i> Confirmar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ═══ MODAL: RETIRAR ══════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalRetirar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div class="modal-icon-box danger"><i class="fas fa-user-xmark"></i></div>
                <h5 class="fw-bold mb-2">Retirar Cliente</h5>
                <p class="text-muted mb-3">
                    El cliente pasará a estado <strong>Inactivo</strong>.
                    Todas sus actividades abiertas serán marcadas como:
                </p>
                <div class="alert alert-danger py-2 mb-3" style="font-size:.88rem;">
                    <i class="fas fa-ban me-1"></i><strong>CERRADA POR FIN CONTRATO</strong>
                </div>
                <ul class="list-group list-group-flush text-start mb-3">
                    <li class="list-group-item py-1 px-0"><i class="fas fa-check-circle me-2 text-success"></i>Plan de Trabajo Anual</li>
                    <li class="list-group-item py-1 px-0"><i class="fas fa-check-circle me-2 text-success"></i>Cronograma de Capacitación</li>
                    <li class="list-group-item py-1 px-0"><i class="fas fa-check-circle me-2 text-success"></i>Pendientes</li>
                    <li class="list-group-item py-1 px-0"><i class="fas fa-check-circle me-2 text-success"></i>Vencimientos y Mantenimientos</li>
                </ul>
                <p class="text-muted" style="font-size:.82rem;">Esta acción no elimina datos, solo cambia su estado.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
                <form action="<?= base_url('/cliente/retirar/' . $client['id_cliente']) ?>" method="post" class="d-inline">
                    <button type="submit" class="btn btn-danger rounded-3 fw-semibold">
                        <i class="fas fa-user-xmark me-1"></i> Sí, Retirar Cliente
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ═══ MODAL PAZ Y SALVO ════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalPazYSalvo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div class="modal-icon-box" style="background:rgba(13,202,240,.15);color:#0dcaf0;">
                    <i class="fas fa-file-circle-check"></i>
                </div>
                <h5 class="fw-bold mb-2">Emitir Paz y Salvo por Todo Concepto</h5>
                <p class="text-muted mb-3">
                    Se enviará un email certificando que <strong><?= esc($client['nombre_cliente']) ?></strong>
                    se encuentra a paz y salvo en todos los módulos SST.
                </p>
                <div class="alert alert-warning py-2 mb-3 text-start" style="font-size:.85rem;">
                    <i class="fas fa-triangle-exclamation me-1"></i>
                    <strong>Requisito estricto:</strong> el sistema verificará automáticamente
                    que no existan actividades abiertas antes de enviar. Si hay alguna pendiente,
                    el email no se enviará y se mostrará el detalle.
                </div>
                <p class="text-muted mb-2" style="font-size:.84rem;">
                    <i class="fas fa-envelope me-1"></i>
                    <strong>Destinatario:</strong> <?= esc($client['correo_cliente'] ?: 'Sin correo registrado') ?>
                </p>
                <p class="text-muted" style="font-size:.84rem;">
                    <i class="fas fa-copy me-1"></i>
                    <strong>Con copia a:</strong> consultor asignado, Head Consultant y Diana Cuestas
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
                <form action="<?= base_url('/cliente/paz-y-salvo/' . $client['id_cliente']) ?>" method="post" class="d-inline">
                    <button type="submit" class="btn btn-info rounded-3 fw-semibold" style="color:#fff;">
                        <i class="fas fa-paper-plane me-1"></i> Sí, Enviar Paz y Salvo
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ═══ FOOTER ═══════════════════════════════════════════════════════════════ -->
<footer class="site-footer">
    <p class="mb-1 fw-bold">Cycloid Talent SAS</p>
    <p class="mb-1">Todos los derechos reservados © 2024 &nbsp;·&nbsp; NIT: 901.653.912</p>
    <p class="mb-2">
        <a href="https://cycloidtalent.com/" target="_blank" class="text-decoration-none" style="color:#007bff;">cycloidtalent.com</a>
    </p>
    <div class="d-flex gap-3 justify-content-center">
        <a href="https://www.facebook.com/CycloidTalent" target="_blank" class="social-icon">
            <img src="https://cdn-icons-png.flaticon.com/512/733/733547.png" alt="Facebook">
        </a>
        <a href="https://co.linkedin.com/company/cycloid-talent" target="_blank" class="social-icon">
            <img src="https://cdn-icons-png.flaticon.com/512/733/733561.png" alt="LinkedIn">
        </a>
        <a href="https://www.instagram.com/cycloid_talent" target="_blank" class="social-icon">
            <img src="https://cdn-icons-png.flaticon.com/512/733/733558.png" alt="Instagram">
        </a>
        <a href="https://www.tiktok.com/@cycloid_talent" target="_blank" class="social-icon">
            <img src="https://cdn-icons-png.flaticon.com/512/3046/3046126.png" alt="TikTok">
        </a>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
