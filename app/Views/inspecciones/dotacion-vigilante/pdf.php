<?php
$colorMap = [
    'bueno' => '#28a745', 'regular' => '#ffc107', 'deficiente' => '#dc3545',
    'no_tiene' => '#6c757d', 'no_aplica' => '#adb5bd',
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

        .epp-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; border: 1px solid #ccc; }
        .epp-table th { background: #e8e8e8; padding: 4px 6px; font-size: 9px; border: 1px solid #ccc; text-align: left; }
        .epp-table td { padding: 3px 6px; font-size: 9px; border: 1px solid #ccc; }
        .epp-badge { padding: 2px 6px; color: white; font-size: 8px; font-weight: bold; }

        .foto-container { text-align: center; margin: 4px 0; }
        .foto-container img { max-width: 220px; max-height: 160px; border: 1px solid #ccc; }

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
                    <strong style="font-size:7px;"><?= esc($cliente['nombre_cliente'] ?? '') ?></strong>
                <?php endif; ?>
            </td>
            <td class="header-title">SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO</td>
            <td class="header-code">Codigo: FT-SST-213<br>Version: 001</td>
        </tr>
        <tr>
            <td class="header-title" style="font-size:10px;">REPORTE DE INSPECCION DE DOTACIONES</td>
            <td class="header-code">Fecha: <?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td>
        </tr>
    </table>

    <!-- TITULO -->
    <div class="main-title">REPORTE DE INSPECCION DE DOTACIONES</div>
    <div class="main-subtitle">VIGILANTE - <?= esc($cliente['nombre_cliente'] ?? '') ?></div>

    <!-- INTRODUCCION -->
    <div class="section-title">INTRODUCCION</div>
    <p class="intro-text">
        Los Elementos de Proteccion Personal (EPP) son dispositivos, accesorios y vestimentas de diversos disenos que emplea el trabajador para protegerse contra posibles lesiones. Los equipos de proteccion personal constituyen uno de los conceptos mas basicos en cuanto a la seguridad en el lugar de trabajo y son necesarios cuando los peligros no han podido ser eliminados por completo o controlados por otros medios.
    </p>

    <!-- MARCO LEGAL -->
    <div class="section-title">MARCO LEGAL</div>
    <p class="intro-text">
        <strong>Articulo 230 del Codigo Sustantivo del Trabajo:</strong> Obliga al empleador a suministrar calzado y vestido de labor al trabajador cuya remuneracion mensual sea hasta dos salarios minimos legales mensuales vigentes, tres veces al ano.<br>
        <strong>Decreto 1072 de 2015 (Decreto Unico Reglamentario del Sector Trabajo):</strong> Compila las normas relacionadas con la seguridad y salud en el trabajo, incluyendo las disposiciones sobre EPP.<br>
        <strong>Resolucion 0312 de 2019:</strong> Establece los estandares minimos del SG-SST e incluye requisitos sobre la dotacion de EPP.
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
            <td><?= esc($consultor['nombre_consultor'] ?? '') ?></td>
            <td class="info-label">CONTRATISTA:</td>
            <td><?= esc($inspeccion['contratista'] ?? '') ?></td>
        </tr>
        <tr>
            <td class="info-label">SERVICIO:</td>
            <td><?= esc($inspeccion['servicio'] ?? '') ?></td>
            <td class="info-label">NOMBRE / CARGO:</td>
            <td><?= esc($inspeccion['nombre_cargo'] ?? '') ?></td>
        </tr>
        <?php if (!empty($inspeccion['actividades_frecuentes'])): ?>
        <tr>
            <td class="info-label">ACTIVIDADES:</td>
            <td colspan="3"><?= nl2br(esc($inspeccion['actividades_frecuentes'])) ?></td>
        </tr>
        <?php endif; ?>
    </table>

    <!-- REGISTRO FOTOGRAFICO -->
    <?php if (!empty($fotosBase64['foto_cuerpo_completo']) || !empty($fotosBase64['foto_cuarto_almacenamiento'])): ?>
    <div class="section-title">REGISTRO FOTOGRAFICO</div>
    <table style="width:100%; border-collapse:collapse; margin-bottom:8px;">
        <tr>
            <?php if (!empty($fotosBase64['foto_cuerpo_completo'])): ?>
            <td style="width:50%; text-align:center; padding:4px; vertical-align:top;">
                <div style="font-size:8px; font-weight:bold; margin-bottom:3px;">Cuerpo Completo</div>
                <img src="<?= $fotosBase64['foto_cuerpo_completo'] ?>" style="max-width:200px; max-height:160px; border:1px solid #ccc;">
            </td>
            <?php endif; ?>
            <?php if (!empty($fotosBase64['foto_cuarto_almacenamiento'])): ?>
            <td style="width:50%; text-align:center; padding:4px; vertical-align:top;">
                <div style="font-size:8px; font-weight:bold; margin-bottom:3px;">Cuarto de Almacenamiento</div>
                <img src="<?= $fotosBase64['foto_cuarto_almacenamiento'] ?>" style="max-width:200px; max-height:160px; border:1px solid #ccc;">
            </td>
            <?php endif; ?>
        </tr>
    </table>
    <?php endif; ?>

    <!-- ESTADO DE DOTACION EPP -->
    <div class="section-title">ESTADO DE DOTACION EPP</div>
    <table class="epp-table">
        <tr>
            <th style="width:5%;">#</th>
            <th>Elemento</th>
            <th style="width:25%;">Estado</th>
        </tr>
        <?php $num = 1; foreach ($itemsEpp as $key => $info):
            $estado = $inspeccion['estado_' . $key] ?? '';
            $estadoLabel = $estadosEpp[$estado] ?? 'Sin evaluar';
            $color = $colorMap[$estado] ?? '#6c757d';
        ?>
        <tr>
            <td style="text-align:center;"><?= $num++ ?></td>
            <td><?= $info['label'] ?></td>
            <td>
                <span class="epp-badge" style="background:<?= $color ?>;"><?= $estadoLabel ?></span>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <!-- CONCEPTO FINAL -->
    <?php if (!empty($inspeccion['concepto_final'])): ?>
    <div class="section-title">CONCEPTO FINAL</div>
    <p class="content-text"><?= nl2br(esc($inspeccion['concepto_final'])) ?></p>
    <?php endif; ?>

    <!-- OBSERVACIONES -->
    <?php if (!empty($inspeccion['observaciones'])): ?>
    <div class="section-title">OBSERVACIONES</div>
    <p class="content-text"><?= nl2br(esc($inspeccion['observaciones'])) ?></p>
    <?php endif; ?>

</body>
</html>
