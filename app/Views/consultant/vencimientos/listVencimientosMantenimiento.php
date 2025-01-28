<!-- app/Views/consultant/vencimientos/listVencimientosMantenimiento.php -->

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Listado de Vencimientos de Mantenimiento</title>
    <!-- Agrega tus estilos y scripts aquí (por ejemplo, Bootstrap) -->
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .btn {
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 4px;
            margin-right: 5px;
        }
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        .btn-success {
            background-color: #28a745;
            color: white;
        }
        .btn-warning {
            background-color: #ffc107;
            color: black;
        }
    </style>
</head>
<body>
    <h1>Listado de Vencimientos de Mantenimiento</h1>

    <!-- Mensajes de éxito -->
    <?php if(session()->getFlashdata('msg')): ?>
        <div class="alert alert-success">
            <?= session()->getFlashdata('msg') ?>
        </div>
    <?php endif; ?>

    <!-- Botones de acciones -->
    <div>
        <a href="<?= site_url('vencimientos/add') ?>" class="btn btn-success">Agregar Nuevo Vencimiento</a>
        <a href="<?= base_url('vencimientos/send-emails') ?>" class="btn btn-warning">Enviar Recordatorios por Correo</a>

    </div>

    <!-- Tabla de vencimientos -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Consultor</th>
                <th>Mantenimiento</th>
                <th>Fecha de Vencimiento</th>
                <th>Estado</th>
                <th>Fecha de Realización</th>
                <th>Observaciones</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($vencimientos) && is_array($vencimientos)): ?>
                <?php foreach ($vencimientos as $vencimiento): ?>
                    <tr>
                        <td><?= esc($vencimiento['id']) ?></td>
                        <td><?= esc($vencimiento['cliente']) ?></td>
                        <td><?= esc($vencimiento['consultor']) ?></td>
                        <td><?= esc($vencimiento['mantenimiento']) ?></td>
                        <td><?= esc($vencimiento['fecha_vencimiento']) ?></td>
                        <td><?= esc($vencimiento['estado_actividad']) ?></td>
                        <td><?= esc($vencimiento['fecha_realizacion']) ?></td>
                        <td><?= esc($vencimiento['observaciones']) ?></td>
                        <td>
                            <a href="<?= site_url('vencimientos/edit/' . esc($vencimiento['id'])) ?>" class="btn btn-primary">Editar</a>
                            <a href="<?= site_url('vencimientos/delete/' . esc($vencimiento['id'])) ?>" class="btn btn-danger" onclick="return confirm('¿Estás seguro de eliminar este vencimiento?');">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9">No hay vencimientos registrados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
