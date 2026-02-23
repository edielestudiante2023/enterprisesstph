<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 100px 70px 80px 90px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.4;
            padding: 15px 20px;
        }

        /* Header corporativo */
        .header-table { width: 100%; border-collapse: collapse; border: 1.5px solid #333; margin-bottom: 12px; }
        .header-table td { border: 1px solid #333; padding: 5px 8px; vertical-align: middle; }
        .header-logo { width: 110px; text-align: center; font-size: 9px; }
        .header-logo img { max-width: 95px; max-height: 55px; }
        .header-title { text-align: center; font-weight: bold; font-size: 10px; }
        .header-code { width: 130px; font-size: 9px; }

        /* Titulo principal */
        .main-title { text-align: center; font-size: 12px; font-weight: bold; margin: 10px 0 8px; color: #1c2437; }

        /* Tabla datos generales */
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; border: 1px solid #ccc; }
        .info-table td { padding: 4px 8px; font-size: 10px; border: 1px solid #ccc; }
        .info-label { font-weight: bold; color: #444; width: 90px; background: #f7f7f7; }

        /* Titulos de seccion */
        .section-title { background: #1c2437; color: white; padding: 4px 10px; font-weight: bold; font-size: 10px; margin: 10px 0 5px; }

        /* Tablas de datos */
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .data-table th { background: #e8e8e8; border: 1px solid #aaa; padding: 4px 6px; font-size: 9px; text-align: left; }
        .data-table td { border: 1px solid #ccc; padding: 3px 6px; font-size: 9px; }

        /* Firmas en tabla integrantes */
        .firma-inline { max-width: 70px; max-height: 30px; }

        /* Sub-titulos dentro de secciones */
        .sub-title { font-weight: bold; font-size: 10px; margin: 6px 0 3px; }

        /* Texto sin datos */
        .empty-text { color: #888; font-style: italic; font-size: 9px; margin-bottom: 6px; }

        /* Texto contenido */
        .content-text { font-size: 10px; line-height: 1.5; margin-bottom: 6px; }

        /* Firmas al pie - usar tabla */
        .firma-table { width: 100%; border-collapse: collapse; margin-top: 25px; }
        .firma-table td { text-align: center; vertical-align: bottom; padding: 5px 10px; width: 33%; }
        .firma-table img { max-width: 100px; max-height: 45px; }
        .firma-label { border-top: 1px solid #333; margin-top: 4px; padding-top: 3px; font-size: 8px; font-weight: bold; color: #555; }

        .page-break { page-break-before: always; }
    </style>
</head>
<body>

    <!-- HEADER CORPORATIVO -->
    <table class="header-table">
        <tr>
            <td class="header-logo" rowspan="2">
                <?php if (!empty($logoBase64)): ?>
                    <img src="<?= $logoBase64 ?>">
                <?php else: ?>
                    <strong style="font-size:8px;"><?= esc($cliente['nombre_cliente'] ?? '') ?></strong>
                <?php endif; ?>
            </td>
            <td class="header-title">SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO</td>
            <td class="header-code">Codigo: FT-SST-007<br>Version: 001</td>
        </tr>
        <tr>
            <td class="header-title" style="font-size:11px;">ACTA DE REUNION</td>
            <td class="header-code">Fecha: <?= date('d/m/Y', strtotime($acta['fecha_visita'])) ?></td>
        </tr>
    </table>

    <!-- TITULO -->
    <div class="main-title">ACTA DE VISITA Y SEGUIMIENTO AL SISTEMA</div>

    <!-- DATOS DE LA VISITA -->
    <table class="info-table">
        <tr>
            <td class="info-label">MOTIVO:</td>
            <td><?= esc($acta['motivo']) ?></td>
            <td class="info-label">HORARIO:</td>
            <td><?= date('g:i A', strtotime($acta['hora_visita'])) ?></td>
        </tr>
        <tr>
            <td class="info-label">CLIENTE:</td>
            <td><?= esc($cliente['nombre_cliente'] ?? '') ?></td>
            <td class="info-label">FECHA:</td>
            <td><?= date('d/m/Y', strtotime($acta['fecha_visita'])) ?></td>
        </tr>
        <?php if (!empty($acta['modalidad'])): ?>
        <tr>
            <td class="info-label">MODALIDAD:</td>
            <td colspan="3"><?= esc(ucfirst($acta['modalidad'])) ?></td>
        </tr>
        <?php endif; ?>
    </table>

    <!-- 1. INTEGRANTES -->
    <div class="section-title">1. INTEGRANTES</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:40%;">NOMBRE</th>
                <th style="width:30%;">ROL</th>
                <th style="width:30%;">FIRMA</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($integrantes as $int): ?>
            <tr>
                <td><?= esc($int['nombre']) ?></td>
                <td><?= esc($int['rol']) ?></td>
                <td style="text-align:center; padding: 2px;">
                    <?php
                    $rol = strtoupper($int['rol']);
                    if ($rol === 'ADMINISTRADOR' && !empty($firmas['administrador'])) {
                        echo '<img src="' . $firmas['administrador'] . '" class="firma-inline">';
                    } elseif (stripos($rol, 'ASISTENTE') !== false && !empty($firmas['administrador'])) {
                        echo '<img src="' . $firmas['administrador'] . '" class="firma-inline">';
                    } elseif (stripos($rol, 'VIG') !== false && !empty($firmas['vigia'])) {
                        echo '<img src="' . $firmas['vigia'] . '" class="firma-inline">';
                    } elseif (stripos($rol, 'CONSULTOR') !== false && !empty($firmas['consultor'])) {
                        echo '<img src="' . $firmas['consultor'] . '" class="firma-inline">';
                    }
                    ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- 2. TEMAS ABIERTOS Y VENCIDOS -->
    <div class="section-title">2. TEMAS ABIERTOS Y VENCIDOS</div>

    <!-- Mantenimientos -->
    <p class="sub-title">MANTENIMIENTOS:</p>
    <?php if (empty($mantenimientos)): ?>
        <p class="empty-text">Sin mantenimientos por vencer en los proximos 30 dias.</p>
    <?php else: ?>
        <table class="data-table">
            <thead><tr><th>MANTENIMIENTO</th><th style="width:100px;">VENCIMIENTO</th></tr></thead>
            <tbody>
            <?php foreach ($mantenimientos as $m): ?>
                <tr>
                    <td><?= esc($m['detalle_mantenimiento'] ?? 'Mantenimiento') ?></td>
                    <td><?= date('d/m/Y', strtotime($m['fecha_vencimiento'])) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- Pendientes -->
    <p class="sub-title">PENDIENTES ABIERTOS:</p>
    <?php if (empty($pendientesAbiertos)): ?>
        <p class="empty-text">Sin pendientes abiertos.</p>
    <?php else: ?>
        <table class="data-table">
            <thead><tr><th>ACTIVIDAD</th><th>RESPONSABLE</th><th style="width:55px;">ASIGNADO</th><th style="width:55px;">CIERRE</th><th style="width:35px;">DIAS</th></tr></thead>
            <tbody>
            <?php foreach ($pendientesAbiertos as $p): ?>
                <tr>
                    <td><?= esc($p['tarea_actividad']) ?></td>
                    <td><?= esc($p['responsable'] ?? '') ?></td>
                    <td><?= !empty($p['fecha_asignacion']) ? date('d/m/Y', strtotime($p['fecha_asignacion'])) : '-' ?></td>
                    <td><?= !empty($p['fecha_cierre']) ? date('d/m/Y', strtotime($p['fecha_cierre'])) : '-' ?></td>
                    <td style="text-align:center;"><?= $p['conteo_dias'] ?? '-' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- 3. TEMAS TRATADOS -->
    <div class="section-title">3. TEMAS TRATADOS</div>
    <?php if (!empty($temas)): ?>
        <?php foreach ($temas as $i => $tema): ?>
            <p class="content-text"><strong>TEMA <?= $i + 1 ?>:</strong> <?= esc($tema['descripcion']) ?></p>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="empty-text">No se registraron temas.</p>
    <?php endif; ?>

    <!-- 4. OBSERVACIONES -->
    <?php if (!empty($acta['observaciones'])): ?>
    <div class="section-title">4. OBSERVACIONES</div>
    <p class="content-text"><?= nl2br(esc($acta['observaciones'])) ?></p>
    <?php endif; ?>

    <!-- 5. COMPROMISOS -->
    <?php if (!empty($compromisos)): ?>
    <div class="section-title">5. COMPROMISOS</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>ACTIVIDAD</th>
                <th style="width:80px;">FECHA CIERRE</th>
                <th>RESPONSABLE</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($compromisos as $comp): ?>
            <tr>
                <td><?= esc($comp['tarea_actividad']) ?></td>
                <td><?= !empty($comp['fecha_cierre']) ? date('d/m/Y', strtotime($comp['fecha_cierre'])) : '-' ?></td>
                <td><?= esc($comp['responsable'] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- FIRMAS AL PIE -->
    <table class="firma-table">
        <tr>
            <?php if (!empty($firmas['administrador'])): ?>
            <td>
                <img src="<?= $firmas['administrador'] ?>"><br>
                <div class="firma-label">ADMINISTRADOR</div>
            </td>
            <?php endif; ?>

            <?php if (!empty($firmas['vigia'])): ?>
            <td>
                <img src="<?= $firmas['vigia'] ?>"><br>
                <div class="firma-label">VIGIA SST</div>
            </td>
            <?php endif; ?>

            <?php if (!empty($firmas['consultor'])): ?>
            <td>
                <img src="<?= $firmas['consultor'] ?>"><br>
                <div class="firma-label">CONSULTOR - <?= esc($consultor['nombre_consultor'] ?? '') ?></div>
            </td>
            <?php endif; ?>
        </tr>
    </table>

</body>
</html>
