<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Agregar Usuario</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f8f9fa;
            color: #343a40;
        }

        .form-container {
            max-width: 700px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .entity-section {
            display: none;
            margin-top: 15px;
            padding: 15px;
            background-color: #f1f3f4;
            border-radius: 5px;
        }

        .select2-container {
            width: 100% !important;
        }
    </style>
</head>

<body>

    <!-- Navbar Fijo -->
    <nav style="background-color: white; position: fixed; top: 0; width: 100%; z-index: 1000; padding: 10px 0; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; max-width: 1200px; margin: 0 auto;">
            <div>
                <a href="https://dashboard.cycloidtalent.com/login">
                    <img src="<?= base_url('uploads/logoenterprisesstblancoslogan.png') ?>" alt="Enterprisesst Logo" style="height: 100px;">
                </a>
            </div>
            <div>
                <a href="https://cycloidtalent.com/index.php/consultoria-sst">
                    <img src="<?= base_url('uploads/logosst.png') ?>" alt="SST Logo" style="height: 100px;">
                </a>
            </div>
            <div>
                <a href="https://cycloidtalent.com/">
                    <img src="<?= base_url('uploads/logocycloidsinfondo.png') ?>" alt="Cycloids Logo" style="height: 100px;">
                </a>
            </div>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 10px auto 0; padding: 0 20px;">
            <div style="text-align: center;">
                <h2 style="margin: 0; font-size: 16px;">Volver</h2>
                <a href="<?= base_url('/admin/users') ?>" class="btn btn-secondary btn-sm mt-2">Volver a Lista</a>
            </div>
            <div style="text-align: center;">
                <h2 style="margin: 0; font-size: 16px;">Dashboard</h2>
                <a href="<?= base_url('/admin/dashboard') ?>" class="btn btn-primary btn-sm mt-2">Ir a Dashboard</a>
            </div>
        </div>
    </nav>

    <div style="height: 200px;"></div>

    <div class="container">
        <?php if (session()->getFlashdata('msg')): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <?= session()->getFlashdata('msg') ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <h2 class="mb-4 text-center">Agregar Nuevo Usuario</h2>

            <form action="<?= base_url('/admin/users/add') ?>" method="post">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="nombre_completo">Nombre Completo <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nombre_completo" name="nombre_completo" required
                           value="<?= old('nombre_completo') ?>" placeholder="Nombre completo del usuario">
                </div>

                <div class="form-group">
                    <label for="email">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="email" name="email" required
                           value="<?= old('email') ?>" placeholder="correo@ejemplo.com">
                </div>

                <div class="form-group">
                    <label for="password">Contraseña <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password" required
                               placeholder="Mínimo 6 caracteres" minlength="6">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-secondary" id="togglePassword" tabindex="-1">
                                <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                                    <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="tipo_usuario">Tipo de Usuario <span class="text-danger">*</span></label>
                    <select class="form-control" id="tipo_usuario" name="tipo_usuario" required>
                        <option value="">Seleccione un tipo</option>
                        <option value="admin" <?= old('tipo_usuario') === 'admin' ? 'selected' : '' ?>>Administrador</option>
                        <option value="consultant" <?= old('tipo_usuario') === 'consultant' ? 'selected' : '' ?>>Consultor</option>
                        <option value="client" <?= old('tipo_usuario') === 'client' ? 'selected' : '' ?>>Cliente</option>
                    </select>
                </div>

                <!-- Sección para vincular consultor -->
                <div id="consultant_section" class="entity-section">
                    <label for="id_consultor">Vincular con Consultor (opcional)</label>
                    <select class="form-control select2" id="id_consultor" name="id_consultor">
                        <option value="">Sin vincular</option>
                        <?php foreach ($consultants as $consultant): ?>
                            <option value="<?= $consultant['id_consultor'] ?>">
                                <?= htmlspecialchars($consultant['nombre_consultor']) ?> - <?= htmlspecialchars($consultant['correo_consultor']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text text-muted">Vincula este usuario con un consultor existente para heredar sus datos.</small>
                </div>

                <!-- Sección para vincular cliente -->
                <div id="client_section" class="entity-section">
                    <label for="id_cliente">Vincular con Cliente (opcional)</label>
                    <select class="form-control select2" id="id_cliente" name="id_cliente">
                        <option value="">Sin vincular</option>
                        <?php foreach ($clients as $client): ?>
                            <option value="<?= $client['id_cliente'] ?>">
                                <?= htmlspecialchars($client['nombre_cliente']) ?> - <?= htmlspecialchars($client['correo_cliente']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text text-muted">Vincula este usuario con un cliente existente para que pueda acceder a su información.</small>
                </div>

                <div class="form-group">
                    <label for="estado">Estado</label>
                    <select class="form-control" id="estado" name="estado">
                        <option value="activo" selected>Activo</option>
                        <option value="inactivo">Inactivo</option>
                        <option value="pendiente">Pendiente</option>
                    </select>
                </div>

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-success btn-block">Crear Usuario</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer style="background-color: white; padding: 20px 0; border-top: 1px solid #B0BEC5; margin-top: 40px; color: #3A3F51; font-size: 14px; text-align: center;">
        <div style="max-width: 1200px; margin: 0 auto; display: flex; flex-direction: column; align-items: center;">
            <p style="margin: 0; font-weight: bold;">Cycloid Talent SAS</p>
            <p style="margin: 5px 0;">Todos los derechos reservados © 2024</p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inicializar Select2
            $('.select2').select2({
                placeholder: "Buscar...",
                allowClear: true
            });

            // Mostrar/ocultar secciones según tipo de usuario
            $('#tipo_usuario').on('change', function() {
                var tipo = $(this).val();

                $('#consultant_section').hide();
                $('#client_section').hide();

                if (tipo === 'admin' || tipo === 'consultant') {
                    $('#consultant_section').show();
                } else if (tipo === 'client') {
                    $('#client_section').show();
                }
            });

            // Trigger inicial
            $('#tipo_usuario').trigger('change');

            // Toggle mostrar/ocultar contraseña
            $('#togglePassword').on('click', function() {
                var input = $('#password');
                var icon = $('#eyeIcon');
                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    icon.html('<path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7.028 7.028 0 0 0-2.79.588l.77.771A5.944 5.944 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.134 13.134 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755-.165.165-.337.328-.517.486l.708.709z"/><path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829l.822.822zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829z"/><path d="M3.35 5.47c-.18.16-.353.322-.518.487A13.134 13.134 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7.029 7.029 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12-.708.708z"/>');
                } else {
                    input.attr('type', 'password');
                    icon.html('<path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>');
                }
            });
        });
    </script>

</body>

</html>
