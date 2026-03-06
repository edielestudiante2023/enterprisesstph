<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Cliente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
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

        /* Layout */
        .page-wrapper { padding-top: 100px; padding-bottom: 60px; }

        /* Card base */
        .main-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,.08);
            overflow: hidden;
        }

        /* Header */
        .card-header-custom {
            background: linear-gradient(135deg, var(--primary), #7209b7);
            padding: 28px 32px;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .card-header-custom .icon-box {
            width: 56px; height: 56px;
            border-radius: 14px;
            background: rgba(255,255,255,.2);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem;
            flex-shrink: 0;
        }
        .card-header-custom h4 { margin: 0; font-weight: 700; font-size: 1.25rem; }
        .card-header-custom small { opacity: .85; font-size: .82rem; }

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

        /* Botón guardar */
        .btn-guardar {
            background: linear-gradient(135deg, #198754, #20c997);
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
            box-shadow: 0 6px 18px rgba(25,135,84,.35);
            color: #fff;
        }

        /* Indicador de requerido */
        .required-dot {
            color: #dc3545;
            font-weight: 700;
            margin-left: 2px;
        }

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

<!-- NAVBAR -->
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

<!-- CONTENIDO -->
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
            <li class="breadcrumb-item active">Nuevo Cliente</li>
        </ol>
    </nav>

    <!-- CARD PRINCIPAL -->
    <div class="main-card mb-5">

        <!-- Header -->
        <div class="card-header-custom">
            <div class="icon-box"><i class="fas fa-user-plus"></i></div>
            <div>
                <h4>Agregar Nuevo Cliente</h4>
                <small><i class="fas fa-info-circle me-1"></i>Complete la información para registrar un nuevo cliente en el sistema</small>
            </div>
        </div>

        <!-- Formulario -->
        <div class="form-body">
            <form action="<?= base_url('/addClientPost') ?>" method="post" enctype="multipart/form-data">

                <!-- SECCIÓN 1 — Información Básica -->
                <div class="section-card">
                    <div class="section-header">
                        <i class="fas fa-building" style="background:#4361ee;"></i>
                        <span>Información Básica</span>
                    </div>
                    <div class="section-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label"><i class="fas fa-calendar-plus"></i>Fecha Inicio Contrato <span class="required-dot">*</span></label>
                                <input type="date" name="fecha_ingreso" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><i class="fas fa-id-card"></i>NIT Cliente <span class="required-dot">*</span></label>
                                <input type="text" name="nit_cliente" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><i class="fas fa-building"></i>Nombre del Tercero (RUT) <span class="required-dot">*</span></label>
                                <input type="text" name="nombre_cliente" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><i class="fas fa-user"></i>Usuario <span class="required-dot">*</span></label>
                                <input type="text" name="usuario" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><i class="fas fa-lock"></i>Password <span class="required-dot">*</span></label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><i class="fas fa-calendar-xmark"></i>Fecha Fin de Contrato <span class="required-dot">*</span></label>
                                <input type="date" name="fecha_fin_contrato" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><i class="fas fa-city"></i>Ciudad <span class="required-dot">*</span></label>
                                <input type="text" name="ciudad_cliente" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><i class="fas fa-list-check"></i>Tipo de Servicio <span class="required-dot">*</span></label>
                                <select name="estandares" class="form-select" required>
                                    <option value="Mensual">Mensual</option>
                                    <option value="Bimensual">Bimensual</option>
                                    <option value="Trimestral">Trimestral</option>
                                    <option value="Proyecto">Proyecto</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><i class="fas fa-code-branch"></i>Código Actividad Económica <span class="required-dot">*</span></label>
                                <input type="text" name="codigo_actividad_economica" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><i class="fas fa-toggle-on"></i>Estado</label>
                                <select name="estado" class="form-select" required>
                                    <option value="activo" selected>Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                    <option value="pendiente">Pendiente</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><i class="fas fa-user-tie"></i>Consultor Asignado <span class="required-dot">*</span></label>
                                <select name="id_consultor" class="form-select" required>
                                    <option value="">Seleccione un Consultor</option>
                                    <?php foreach ($consultants as $consultant): ?>
                                        <?php if ($consultant['id_consultor'] != 1): ?>
                                            <option value="<?= $consultant['id_consultor'] ?>"><?= esc($consultant['nombre_consultor']) ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><i class="fas fa-user-check"></i>Vendedor <span class="required-dot">*</span></label>
                                <select name="vendedor" class="form-select" required>
                                    <option value="">Seleccione un Vendedor</option>
                                    <option value="DIANA CUESTAS">DIANA CUESTAS</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><i class="fas fa-credit-card"></i>Plazo de Cartera</label>
                                <select name="plazo_cartera_select" id="plazo_cartera_select_add" class="form-select">
                                    <option value="">Seleccione</option>
                                    <option value="PAGO INMEDIATO">PAGO INMEDIATO</option>
                                    <option value="8 DÍAS">8 DÍAS</option>
                                    <option value="15 DÍAS">15 DÍAS</option>
                                    <option value="21 DÍAS">21 DÍAS</option>
                                    <option value="30 DÍAS">30 DÍAS</option>
                                    <option value="45 DÍAS">45 DÍAS</option>
                                    <option value="60 DÍAS">60 DÍAS</option>
                                    <option value="90 DÍAS">90 DÍAS</option>
                                    <option value="OTRO">OTRO PLAZO</option>
                                </select>
                                <input type="text" name="plazo_cartera_otro" id="plazo_cartera_otro_add" class="form-control mt-2 d-none" placeholder="Digite el plazo">
                                <input type="hidden" name="plazo_cartera" id="plazo_cartera_hidden_add" value="">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><i class="fas fa-calendar-day"></i>Cierre Facturación (día)</label>
                                <input type="number" name="fecha_cierre_facturacion" class="form-control" min="1" max="31" placeholder="Ej: 20">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><i class="fas fa-calendar-check"></i>Fecha Asignación Cronograma</label>
                                <input type="date" name="fecha_asignacion_cronograma" class="form-control">
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
                                <label class="form-label"><i class="fas fa-envelope"></i>Correo del Cliente <span class="required-dot">*</span></label>
                                <input type="email" name="correo_cliente" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="fas fa-envelope-open-text"></i>Correo Consejo de Administración</label>
                                <input type="email" name="correo_consejo_admon" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="fas fa-user-tie"></i>Consultor Externo</label>
                                <input type="text" name="consultor_externo" class="form-control" placeholder="Nombre del consultor externo">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="fas fa-envelope"></i>Email Consultor Externo</label>
                                <input type="email" name="email_consultor_externo" class="form-control" placeholder="correo@ejemplo.com">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label"><i class="fas fa-phone"></i>Teléfono 1 <span class="required-dot">*</span></label>
                                <input type="text" name="telefono_1_cliente" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label"><i class="fas fa-phone-flip"></i>Teléfono 2</label>
                                <input type="text" name="telefono_2_cliente" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="fas fa-map-marker-alt"></i>Dirección <span class="required-dot">*</span></label>
                                <input type="text" name="direccion_cliente" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="fas fa-user-gear"></i>Persona de Contacto Compras <span class="required-dot">*</span></label>
                                <input type="text" name="persona_contacto_compras" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="fas fa-user-cog"></i>Persona de Contacto Operaciones</label>
                                <input type="text" name="persona_contacto_operaciones" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="fas fa-user-tag"></i>Persona de Contacto Pagos / Tesorería</label>
                                <input type="text" name="persona_contacto_pagos" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="fas fa-clock"></i>Horarios y Días de Atención</label>
                                <textarea name="horarios_y_dias" class="form-control" rows="2" placeholder="Ej: Lunes a Viernes 8:00 AM - 5:00 PM"></textarea>
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
                                <label class="form-label"><i class="fas fa-user-shield"></i>Nombre del Representante Legal <span class="required-dot">*</span></label>
                                <input type="text" name="nombre_rep_legal" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="fas fa-id-badge"></i>Cédula del Representante Legal <span class="required-dot">*</span></label>
                                <input type="text" name="cedula_rep_legal" class="form-control" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 4 — Archivos -->
                <div class="section-card">
                    <div class="section-header">
                        <i class="fas fa-paperclip" style="background:#f77f00;"></i>
                        <span>Archivos y Documentos</span>
                    </div>
                    <div class="section-body">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label"><i class="fas fa-image"></i>Logo</label>
                                <input type="file" name="logo" class="form-control" accept="image/*">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="fas fa-signature"></i>Firma Representante Legal</label>
                                <input type="file" name="firma_representante_legal" class="form-control" accept="image/*">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="fas fa-file-invoice"></i>RUT</label>
                                <input type="file" name="rut" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="fas fa-file-contract"></i>Cámara de Comercio</label>
                                <input type="file" name="camara_comercio" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="fas fa-id-card"></i>Cédula Rep. Legal (documento)</label>
                                <input type="file" name="cedula_rep_legal_doc" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="fas fa-file-lines"></i>Oferta Comercial</label>
                                <input type="file" name="oferta_comercial" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="d-flex align-items-center gap-3 mt-2">
                    <button type="submit" class="btn btn-guardar">
                        <i class="fas fa-user-plus me-2"></i>Agregar Cliente
                    </button>
                    <a href="<?= base_url('/listClients') ?>" class="btn btn-outline-secondary rounded-3">
                        <i class="fas fa-arrow-left me-1"></i> Volver al listado
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>
</div>

<!-- FOOTER -->
<footer class="site-footer">
    <p class="mb-1 fw-bold">Cycloid Talent SAS</p>
    <p class="mb-1">Todos los derechos reservados &copy; 2024 &nbsp;&middot;&nbsp; NIT: 901.653.912</p>
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sel = document.getElementById('plazo_cartera_select_add');
    const otro = document.getElementById('plazo_cartera_otro_add');
    const hidden = document.getElementById('plazo_cartera_hidden_add');

    function syncPlazo() {
        if (sel.value === 'OTRO') {
            otro.classList.remove('d-none');
            hidden.value = otro.value;
        } else {
            otro.classList.add('d-none');
            otro.value = '';
            hidden.value = sel.value;
        }
    }

    sel.addEventListener('change', syncPlazo);
    otro.addEventListener('input', function() { hidden.value = otro.value; });
    syncPlazo();
});
</script>
</body>
</html>
