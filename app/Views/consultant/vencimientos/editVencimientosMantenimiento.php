<!-- app/Views/consultant/vencimientos/editVencimientosMantenimiento.php -->

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Editar Vencimiento de Mantenimiento</title>
    <!-- Agrega tus estilos y scripts aquí (por ejemplo, Bootstrap) -->
    <style>
        form {
            max-width: 600px;
            margin-top: 20px;
        }
        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }
        input, select, textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            box-sizing: border-box;
        }
        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        .btn {
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 15px;
        }
        .btn-primary {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }
        .btn-secondary {
            background-color: #6c757d;
            color: white;
            border: none;
            cursor: pointer;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <h1>Editar Vencimiento de Mantenimiento</h1>

    <!-- Mensajes de error -->
    <?php if(session()->getFlashdata('msg')): ?>
        <div class="alert alert-danger">
            <?= session()->getFlashdata('msg') ?>
        </div>
    <?php endif; ?>

    <!-- Formulario para editar vencimiento -->
    <form action="<?= base_url('vencimientos/editpost/' . esc($vencimiento['id_vencimientos_mmttos'])) ?>" method="post">


        <?= csrf_field() ?>

        <label for="id_cliente">Cliente:</label>
        <select name="id_cliente" id="id_cliente" required>
            <option value="">Seleccione un cliente</option>
            <?php foreach($clientes as $cliente): ?>
                <option value="<?= esc($cliente['id_cliente']) ?>" <?= ($vencimiento['id_cliente'] == $cliente['id_cliente']) ? 'selected' : '' ?>><?= esc($cliente['nombre_cliente']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="id_consultor">Consultor:</label>
        <select name="id_consultor" id="id_consultor" required>
            <option value="">Seleccione un consultor</option>
            <?php foreach($consultores as $consultor): ?>
                <option value="<?= esc($consultor['id_consultor']) ?>" <?= ($vencimiento['id_consultor'] == $consultor['id_consultor']) ? 'selected' : '' ?>><?= esc($consultor['nombre_consultor']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="id_mantenimiento">Mantenimiento:</label>
        <select name="id_mantenimiento" id="id_mantenimiento" required>
            <option value="">Seleccione un mantenimiento</option>
            <?php foreach($mantenimientos as $mantenimiento): ?>
                <option value="<?= esc($mantenimiento['id_mantenimiento']) ?>" <?= ($vencimiento['id_mantenimiento'] == $mantenimiento['id_mantenimiento']) ? 'selected' : '' ?>><?= esc($mantenimiento['detalle_mantenimiento']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="fecha_vencimiento">Fecha de Vencimiento:</label>
        <input type="date" name="fecha_vencimiento" id="fecha_vencimiento" value="<?= esc($vencimiento['fecha_vencimiento']) ?>" required>

        <label for="estado_actividad">Estado de la Actividad:</label>
        <select name="estado_actividad" id="estado_actividad" required>
            <option value="sin ejecutar" <?= ($vencimiento['estado_actividad'] == 'sin ejecutar') ? 'selected' : '' ?>>Sin Ejecutar</option>
            <option value="ejecutado" <?= ($vencimiento['estado_actividad'] == 'ejecutado') ? 'selected' : '' ?>>Ejecutado</option>
        </select>

        <label for="fecha_realizacion">Fecha de Realización:</label>
        <input type="date" name="fecha_realizacion" id="fecha_realizacion" value="<?= esc($vencimiento['fecha_realizacion']) ?>">

        <label for="observaciones">Observaciones:</label>
        <textarea name="observaciones" id="observaciones"><?= esc($vencimiento['observaciones']) ?></textarea>

        <button type="submit" class="btn btn-primary">Actualizar Vencimiento</button>
        <a href="<?= base_url('vencimientos') ?>" class="btn btn-secondary">Volver al Listado</a>

    </form>
</body>
</html>
