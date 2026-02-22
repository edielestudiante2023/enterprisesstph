<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; line-height: 1.4; }

        .header-table { width: 100%; border-collapse: collapse; border: 1px solid #333; margin-bottom: 15px; }
        .header-table td { border: 1px solid #333; padding: 6px 8px; vertical-align: middle; }
        .header-logo { width: 100px; text-align: center; }
        .header-logo img { max-width: 90px; max-height: 60px; }
        .header-title { text-align: center; font-weight: bold; font-size: 11px; }
        .header-code { width: 140px; font-size: 10px; }

        .main-title { text-align: center; font-size: 13px; font-weight: bold; margin: 15px 0 10px; color: #1c2437; }

        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .info-table td { padding: 3px 6px; font-size: 11px; }
        .info-label { font-weight: bold; color: #555; width: 100px; }

        .section-title { background: #1c2437; color: white; padding: 5px 10px; font-weight: bold; font-size: 11px; margin: 12px 0 6px; }

        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .data-table th { background: #e8e8e8; border: 1px solid #aaa; padding: 5px 8px; font-size: 10px; text-align: left; }
        .data-table td { border: 1px solid #ccc; padding: 4px 8px; font-size: 10px; }

        .firma-img { max-width: 120px; max-height: 50px; }
        .firma-block { display: inline-block; text-align: center; width: 30%; margin: 10px 1%; vertical-align: top; }
        .firma-block .line { border-top: 1px solid #333; margin-top: 5px; padding-top: 3px; font-size: 9px; }

        .status-ok { color: #28a745; }
        .status-warn { color: #dc3545; }

        .content-text { font-size: 11px; line-height: 1.5; margin-bottom: 8px; }

        .page-break { page-break-before: always; }
    </style>
</head>
<body>

    <!-- HEADER -->
    <table class="header-table">
        <tr>
            <td class="header-logo" rowspan="3">
                <?php if (!empty($logoBase64)): ?>
                    <img src="<?= $logoBase64 ?>">
                <?php else: ?>
                    <strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong>
                <?php endif; ?>
            </td>
            <td class="header-title">SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO</td>
            <td class="header-code">Codigo: FT-SST-007</td>
        </tr>
        <tr>
            <td class="header-title">ACTA DE REUNION</td>
            <td class="header-code">Version: 001</td>
        </tr>
        <tr>
            <td class="header-title">&nbsp;</td>
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
            <td colspan="3"><?= esc($acta['modalidad']) ?></td>
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
                <td style="text-align:center;">
                    <?php
                    $rol = strtoupper($int['rol']);
                    if ($rol === 'ADMINISTRADOR' && !empty($firmas['administrador'])) {
                        echo '<img src="' . $firmas['administrador'] . '" class="firma-img">';
                    } elseif (stripos($rol, 'VIG') !== false && !empty($firmas['vigia'])) {
                        echo '<img src="' . $firmas['vigia'] . '" class="firma-img">';
                    } elseif (stripos($rol, 'CONSULTOR') !== false && !empty($firmas['consultor'])) {
                        echo '<img src="' . $firmas['consultor'] . '" class="firma-img">';
                    }
                    ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- TEMAS ABIERTOS Y VENCIDOS -->
    <div class="section-title">TEMAS ABIERTOS Y VENCIDOS</div>

    <!-- Mantenimientos -->
    <p style="font-weight:bold; margin:6px 0 3px;">MANTENIMIENTOS:</p>
    <?php if (empty($mantenimientos)): ?>
        <p class="status-ok">Sin mantenimientos por vencer en los proximos 30 dias.</p>
    <?php else: ?>
        <table class="data-table">
            <thead><tr><th>MANTENIMIENTO</th><th>VENCIMIENTO</th></tr></thead>
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
    <p style="font-weight:bold; margin:6px 0 3px;">PENDIENTES ABIERTOS:</p>
    <?php if (empty($pendientesAbiertos)): ?>
        <p class="status-ok">Sin pendientes abiertos.</p>
    <?php else: ?>
        <table class="data-table">
            <thead><tr><th>ACTIVIDAD</th><th>RESPONSABLE</th><th>DIAS</th></tr></thead>
            <tbody>
            <?php foreach ($pendientesAbiertos as $p): ?>
                <tr>
                    <td><?= esc($p['tarea_actividad']) ?></td>
                    <td><?= esc($p['responsable'] ?? '') ?></td>
                    <td><?= $p['conteo_dias'] ?? '-' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- 2. TEMAS -->
    <div class="section-title">2. TEMAS</div>
    <?php foreach ($temas as $i => $tema): ?>
        <p class="content-text"><strong>TEMA <?= $i + 1 ?>:</strong> <?= esc($tema['descripcion']) ?></p>
    <?php endforeach; ?>

    <!-- 4. OBSERVACIONES -->
    <?php if (!empty($acta['observaciones'])): ?>
    <div class="section-title">4. OBSERVACIONES</div>
    <p class="content-text"><?= nl2br(esc($acta['observaciones'])) ?></p>
    <?php endif; ?>

    <!-- 5. CARTERA -->
    <?php if (!empty($acta['cartera'])): ?>
    <div class="section-title">5. CARTERA</div>
    <p class="content-text"><?= nl2br(esc($acta['cartera'])) ?></p>
    <?php endif; ?>

    <!-- 6. COMPROMISOS -->
    <?php if (!empty($compromisos)): ?>
    <div class="section-title">6. COMPROMISOS</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>ACTIVIDAD</th>
                <th>FECHA DE CIERRE</th>
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

    <!-- PRÓXIMA REUNIÓN -->
    <?php if (!empty($acta['proxima_reunion_fecha'])): ?>
    <div class="section-title">PROXIMA REUNION</div>
    <p class="content-text">
        <strong>Fecha:</strong> <?= date('d/m/Y', strtotime($acta['proxima_reunion_fecha'])) ?>
        <?php if (!empty($acta['proxima_reunion_hora'])): ?>
        &nbsp;&nbsp; <strong>Hora:</strong> <?= date('g:i A', strtotime($acta['proxima_reunion_hora'])) ?>
        <?php endif; ?>
    </p>
    <?php endif; ?>

    <!-- FIRMAS AL PIE -->
    <div style="margin-top: 30px; text-align: center;">
        <?php if (!empty($firmas['administrador'])): ?>
        <div class="firma-block">
            <img src="<?= $firmas['administrador'] ?>" class="firma-img"><br>
            <div class="line">ADMINISTRADOR</div>
        </div>
        <?php endif; ?>

        <?php if (!empty($firmas['vigia'])): ?>
        <div class="firma-block">
            <img src="<?= $firmas['vigia'] ?>" class="firma-img"><br>
            <div class="line">VIGIA SST</div>
        </div>
        <?php endif; ?>

        <?php if (!empty($firmas['consultor'])): ?>
        <div class="firma-block">
            <img src="<?= $firmas['consultor'] ?>" class="firma-img"><br>
            <div class="line">CONSULTOR - <?= esc($consultor['nombre_consultor'] ?? '') ?></div>
        </div>
        <?php endif; ?>
    </div>

</body>
</html>
