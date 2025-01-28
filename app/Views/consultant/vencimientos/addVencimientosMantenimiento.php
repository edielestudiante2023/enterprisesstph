<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Agregar Vencimiento de Mantenimiento</title>
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
        .btn {
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 15px;
            display: inline-block;
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
            text-decoration: none;
        }
    </style>
</head>
<body>
    <h1>Agregar Vencimiento de Mantenimiento</h1>

    <!-- Mensaje de error -->
    <?php if (session()->getFlashdata('msg')): ?>
        <div style="color: red;">
            <?= session()->getFlashdata('msg') ?>
        </div>
    <?php endif; ?>

    <!-- Formulario para agregar vencimiento -->
    <form action="<?= base_url('vencimientos/addpost') ?>" method="post">
        <?= csrf_field() ?>

        <label for="id_cliente">Cliente:</label>
        <select name="id_cliente" id="id_cliente" required>
            <option value="">Seleccione un cliente</option>
            <?php foreach ($clientes as $cliente): ?>
                <option value="<?= esc($cliente['id_cliente']) ?>" <?= set_select('id_cliente', $cliente['id_cliente']) ?>>
                    <?= esc($cliente['nombre_cliente']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="id_consultor">Consultor:</label>
        <select name="id_consultor" id="id_consultor" required>
            <option value="">Seleccione un consultor</option>
            <?php foreach ($consultores as $consultor): ?>
                <option value="<?= esc($consultor['id_consultor']) ?>" <?= set_select('id_consultor', $consultor['id_consultor']) ?>>
                    <?= esc($consultor['nombre_consultor']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="id_mantenimiento">Mantenimiento:</label>
        <select name="id_mantenimiento" id="id_mantenimiento" required>
            <option value="">Seleccione un mantenimiento</option>
            <?php foreach ($mantenimientos as $mantenimiento): ?>
                <option value="<?= esc($mantenimiento['id_mantenimiento']) ?>" <?= set_select('id_mantenimiento', $mantenimiento['id_mantenimiento']) ?>>
                    <?= esc($mantenimiento['detalle_mantenimiento']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="fecha_vencimiento">Fecha de Vencimiento:</label>
        <input type="date" name="fecha_vencimiento" id="fecha_vencimiento" value="<?= set_value('fecha_vencimiento') ?>" required>

        <label for="estado_actividad">Estado de la Actividad:</label>
        <select name="estado_actividad" id="estado_actividad" required>
            <option value="sin ejecutar" <?= set_select('estado_actividad', 'sin ejecutar', true) ?>>Sin Ejecutar</option>
            <option value="ejecutado" <?= set_select('estado_actividad', 'ejecutado') ?>>Ejecutado</option>
        </select>

        <label for="fecha_realizacion">Fecha de Realización:</label>
        <input type="date" name="fecha_realizacion" id="fecha_realizacion" value="<?= set_value('fecha_realizacion') ?>">

        <label for="observaciones">Observaciones:</label>
        <textarea name="observaciones" id="observaciones"><?= set_value('observaciones') ?></textarea>

        <button type="submit" class="btn btn-primary">Agregar Vencimiento</button>
        <a href="<?= base_url('vencimientos') ?>" class="btn btn-secondary">Volver al Listado</a>
    </form>
</body>
</html>
