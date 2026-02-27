<?php
$colorMap = [
    'bueno' => '#28a745', 'regular' => '#ffc107', 'malo' => '#fd7e14',
    'deficiente' => '#dc3545', 'no_tiene' => '#6c757d', 'no_aplica' => '#adb5bd',
];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 80px 50px 60px 50px; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 9px; color: #333; line-height: 1.3; padding: 10px 15px; }

        .header-table { width: 100%; border-collapse: collapse; border: 1.5px solid #333; margin-bottom: 10px; }
        .header-table td { border: 1px solid #333; padding: 4px 6px; vertical-align: middle; }
        .header-logo { width: 100px; text-align: center; font-size: 8px; }
        .header-logo img { max-width: 85px; max-height: 50px; }
        .header-title { text-align: center; font-weight: bold; font-size: 9px; }
        .header-code { width: 120px; font-size: 8px; }

        .main-title { text-align: center; font-size: 11px; font-weight: bold; margin: 8px 0 4px; color: #1c2437; }
        .main-subtitle { text-align: center; font-size: 9px; font-weight: bold; margin: 0 0 6px; color: #444; }

        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; border: 1px solid #ccc; }
        .info-table td { padding: 3px 6px; font-size: 9px; border: 1px solid #ccc; }
        .info-label { font-weight: bold; color: #444; width: 160px; background: #f7f7f7; }

        .section-title { background: #1c2437; color: white; padding: 3px 8px; font-weight: bold; font-size: 9px; margin: 8px 0 4px; }
        .content-text { font-size: 9px; line-height: 1.4; margin-bottom: 5px; }
        .intro-text { font-size: 8px; line-height: 1.4; margin-bottom: 6px; text-align: justify; }

        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; border: 1px solid #ccc; }
        .items-table th { background: #e8e8e8; padding: 4px 6px; font-size: 9px; border: 1px solid #ccc; text-align: left; }
        .items-table td { padding: 3px 6px; font-size: 9px; border: 1px solid #ccc; }
        .estado-badge { padding: 2px 6px; color: white; font-size: 8px; font-weight: bold; }

        .foto-container { text-align: center; margin: 4px 0; }
        .foto-container img { max-width: 200px; max-height: 140px; border: 1px solid #ccc; }
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
                    <strong style="font-size:7px;"><?= esc($cliente['nombre_cliente'] ?? '') ?></strong>
                <?php endif; ?>
            </td>
            <td class="header-title">SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO</td>
            <td class="header-code">Codigo: FT-SST-214<br>Version: 001</td>
        </tr>
        <tr>
            <td class="header-title" style="font-size:10px;">AUDITORIA ZONA DE RESIDUOS</td>
            <td class="header-code">Fecha: <?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td>
        </tr>
    </table>

    <!-- TITULO -->
    <div class="main-title">AUDITORIA ZONA DE RESIDUOS</div>
    <div class="main-subtitle"><?= esc($cliente['nombre_cliente'] ?? '') ?></div>

    <!-- INTRODUCCION -->
    <div class="section-title">INTRODUCCION</div>
    <p class="intro-text">
        La correcta gestion de la zona de residuos en las propiedades horizontales es fundamental para garantizar un entorno seguro, saludable y en cumplimiento de la normativa ambiental vigente.
    </p>

    <!-- DATOS DE LA INSPECCION -->
    <div class="section-title">DATOS DE LA INSPECCION</div>
    <table class="info-table">
        <tr>
            <td class="info-label">CLIENTE:</td>
            <td><?= esc($cliente['nombre_cliente'] ?? '') ?></td>
            <td class="info-label">FECHA:</td>
            <td><?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td>
        </tr>
        <tr>
            <td class="info-label">CONSULTOR:</td>
            <td colspan="3"><?= esc($consultor['nombre_consultor'] ?? '') ?></td>
        </tr>
    </table>

    <!-- ITEMS DE INSPECCION -->
    <div class="section-title">ITEMS DE INSPECCION</div>
    <table class="items-table">
        <tr>
            <th style="width:5%;">#</th>
            <th>Item</th>
            <th style="width:25%;">Estado / Valor</th>
        </tr>
        <?php $num = 1; foreach ($itemsZona as $key => $info): ?>
        <tr>
            <td style="text-align:center;"><?= $num++ ?></td>
            <td><?= $info['label'] ?></td>
            <td>
                <?php if ($info['tipo'] === 'enum'):
                    $estado = $inspeccion['estado_' . $key] ?? '';
                    $estadoLabel = $estadosZona[$estado] ?? 'Sin evaluar';
                    $color = $colorMap[$estado] ?? '#6c757d';
                ?>
                <span class="estado-badge" style="background:<?= $color ?>;"><?= $estadoLabel ?></span>
                <?php else: ?>
                <?= esc($inspeccion[$key] ?? 'Sin informacion') ?>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <!-- REGISTRO FOTOGRAFICO POR ITEM -->
    <?php
    $fotosConImagen = [];
    foreach ($itemsZona as $key => $info) {
        $campo = 'foto_' . $key;
        if (!empty($fotosBase64[$campo])) {
            $fotosConImagen[] = ['key' => $key, 'label' => $info['label'], 'base64' => $fotosBase64[$campo]];
        }
    }
    ?>
    <?php if (!empty($fotosConImagen)): ?>
<div class="section-title">REGISTRO FOTOGRAFICO</div>
    <table style="width:100%; border-collapse:collapse; margin-bottom:8px;">
        <?php $chunks = array_chunk($fotosConImagen, 2); ?>
        <?php foreach ($chunks as $chunk): ?>
        <tr>
            <?php foreach ($chunk as $foto): ?>
            <td style="width:50%; text-align:center; padding:6px; vertical-align:top;">
                <div style="font-size:8px; font-weight:bold; margin-bottom:3px;"><?= $foto['label'] ?></div>
                <img src="<?= $foto['base64'] ?>" style="max-width:200px; max-height:140px; border:1px solid #ccc;">
            </td>
            <?php endforeach; ?>
            <?php if (count($chunk) === 1): ?>
            <td style="width:50%;"></td>
            <?php endif; ?>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>

    <!-- OBSERVACIONES -->
    <?php if (!empty($inspeccion['observaciones'])): ?>
    <div class="section-title">OBSERVACIONES</div>
    <p class="content-text"><?= nl2br(esc($inspeccion['observaciones'])) ?></p>
    <?php endif; ?>

</body>
</html>
