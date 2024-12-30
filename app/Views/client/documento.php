<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Documento SG-SST</title>
</head>

<body>
    <h2>ASIGNACION PARA EL DISEÑO E IMPLEMENTACION DE SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO</h2>

    <p>Bogotá <?= date('d') ?> de <?= date('F') ?> de <?= date('Y') ?></p>

    <p>Yo, <?= $cliente['nombre_rep_legal'] ?> identificado(a) con cédula de ciudadanía número CC. <?= $cliente['cedula_rep_legal'] ?> como representante legal del <?= $cliente['nombre_cliente'] ?> con Nit. <?= $cliente['nit_cliente'] ?> he delegado para el diseño y administración del Sistema de Gestión de Seguridad y Salud en el Trabajo bajo la Resolución 0312 del 2019 a la empresa CYCLOID TALENT S.A.S con Nit. 901653912, asignando a la profesional <?= $consultor['nombre_consultor'] ?> identificada con CC. <?= $consultor['cedula_consultor'] ?> de Bogotá D.C con número de licencia <?= $consultor['numero_licencia'] ?>.</p>

    <?php if (!empty($consultor['foto_consultor'])): ?>
        <p><img src="<?= base_url('uploads/' . $consultor['foto_consultor']) ?>" alt="Foto del Consultor" width="150"></p>
    <?php endif; ?>

    <h3>Firma del Consultor:</h3>
    <?php if (!empty($consultant['firma_consultor'])): ?>
        <img src="<?= base_url('uploads/' . $consultant['firma_consultor']) ?>" alt="Firma del Consultor" width="200">
    <?php else: ?>
        <p>No disponible</p>
    <?php endif; ?>


    <p>______________________________</p>
    <p>______________________________</p>
    <p><?= $cliente['nombre_rep_legal'] ?></p>
    <p><?= $consultor['nombre_consultor'] ?></p>
    <p>Representante Legal</p>
    <p>Responsable del SG - SST</p>

    <br>
    <a href="<?= base_url('/dashboardclient') ?>">Volver al Dashboard</a>
</body>

</html>