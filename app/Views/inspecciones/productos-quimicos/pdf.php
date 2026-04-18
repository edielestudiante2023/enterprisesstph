<?php
$calificaciones = [
    'C'  => ['label' => 'Cumple',         'color' => '#155724', 'bg' => '#d4edda'],
    'CP' => ['label' => 'Cumple Parcial', 'color' => '#856404', 'bg' => '#fff3cd'],
    'NC' => ['label' => 'No Cumple',      'color' => '#721c24', 'bg' => '#f8d7da'],
    'NA' => ['label' => 'No Aplica',      'color' => '#6c757d', 'bg' => '#e9ecef'],
];

$nivelMap = [
    'alto'  => ['ALTO - CONTROL ADECUADO',     '#28a745'],
    'medio' => ['MEDIO - REQUIERE MEJORAS',    '#ffc107'],
    'bajo'  => ['BAJO - RIESGO SIGNIFICATIVO', '#dc3545'],
];
$nivel = $score['nivel'] ?? 'bajo';
$nivelInfo = $nivelMap[$nivel] ?? [$nivel, '#6c757d'];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 100px 70px 80px 90px; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9px;
            color: #333;
            line-height: 1.3;
            padding: 15px 20px;
        }

        .header-table { width: 100%; border-collapse: collapse; border: 1.5px solid #333; margin-bottom: 10px; }
        .header-table td { border: 1px solid #333; padding: 4px 6px; vertical-align: middle; }
        .header-logo { width: 100px; text-align: center; font-size: 8px; }
        .header-logo img { max-width: 85px; max-height: 50px; }
        .header-title { text-align: center; font-weight: bold; font-size: 9px; }
        .header-code { width: 120px; font-size: 8px; }

        .main-title { text-align: center; font-size: 11px; font-weight: bold; margin: 8px 0 6px; color: #1c2437; }

        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; border: 1px solid #ccc; }
        .info-table td { padding: 3px 6px; font-size: 9px; border: 1px solid #ccc; }
        .info-label { font-weight: bold; color: #444; width: 140px; background: #f7f7f7; }

        .section-title { background: #1c2437; color: white; padding: 3px 8px; font-weight: bold; font-size: 9px; margin: 8px 0 4px; }

        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .data-table th { background: #e8e8e8; border: 1px solid #aaa; padding: 3px 4px; font-size: 8px; text-align: center; }
        .data-table td { border: 1px solid #ccc; padding: 3px 4px; font-size: 8px; vertical-align: middle; }

        .intro-text { font-size: 8px; line-height: 1.4; margin-bottom: 4px; text-align: justify; }

        .score-box { border: 2px solid #1c2437; padding: 8px 12px; margin: 8px 0; text-align: center; }
        .score-pct { font-size: 28px; font-weight: bold; color: #1c2437; display: inline-block; vertical-align: middle; }
        .score-nivel { display: inline-block; padding: 6px 12px; color: #fff; font-weight: bold; font-size: 10px; margin-left: 15px; vertical-align: middle; }

        .cal-cell { text-align: center; font-weight: bold; padding: 2px 5px; }

        .foto-small { max-width: 180px; max-height: 130px; border: 1px solid #ccc; }
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
            <td class="header-code">Codigo: FT-SST-220<br>Version: 001</td>
        </tr>
        <tr>
            <td class="header-title" style="font-size:10px;">INSPECCION DE PRODUCTOS QUIMICOS</td>
            <td class="header-code">Fecha: <?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td>
        </tr>
    </table>

    <!-- TITULO -->
    <div class="main-title">LISTA DE CHEQUEO - PRODUCTOS QUIMICOS<br><?= esc($cliente['nombre_cliente'] ?? '') ?></div>

    <!-- FUNDAMENTACION -->
    <div class="section-title">FUNDAMENTACION</div>
    <p class="intro-text">
        La presente lista de chequeo busca verificar el correcto almacenamiento, rotulado y manipulacion de los productos quimicos utilizados en la propiedad horizontal (aseo, mantenimiento, jardineria), en cumplimiento de la normatividad colombiana aplicable (Decreto 1496 de 2018 - Sistema Globalmente Armonizado, Resolucion 0773 de 2021, NTC 4435 sobre Fichas de Datos de Seguridad), y con el objeto de prevenir accidentes por contacto, inhalacion, mezcla incompatible, derrames o incendios.
    </p>
    <p class="intro-text">
        La evaluacion se realiza mediante una escala cualitativa de cuatro niveles: Cumple (C) equivale a factor 1.0, Cumple Parcial (CP) equivale a factor 0.5, No Cumple (NC) equivale a factor 0.0, y No Aplica (NA) se excluye del calculo. El porcentaje de cumplimiento resulta de la suma de factores dividida por el total de items aplicables.
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
            <td class="info-label">UBICACION:</td>
            <td><?= esc($inspeccion['ubicacion'] ?? '-') ?></td>
        </tr>
        <tr>
            <td class="info-label">GUADANIADORA / COMBUSTIBLE:</td>
            <td colspan="3"><?= !empty($inspeccion['tiene_guadaniadora']) ? 'SI - se evaluaron items adicionales 16-17' : 'NO aplica' ?></td>
        </tr>
    </table>

    <!-- SCORE Y SEMAFORO -->
    <div class="score-box">
        <span class="score-pct"><?= number_format((float)$score['pct'], 1) ?>%</span>
        <span class="score-nivel" style="background:<?= $nivelInfo[1] ?>;"><?= $nivelInfo[0] ?></span>
        <div style="font-size:8px; color:#666; margin-top:4px;">
            Items aplicables: <?= $score['aplicables'] ?? 0 ?> | C=1.0, CP=0.5, NC=0.0, NA=excluido
        </div>
    </div>

    <!-- TABLA DE CHEQUEO -->
    <div class="section-title">LISTA DE CHEQUEO</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:30px;">#</th>
                <th style="text-align:left;">Criterio evaluado</th>
                <th style="width:70px;">Calificacion</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $num => $cfg):
            $col = 'cal_item_' . str_pad($num, 2, '0', STR_PAD_LEFT);
            $val = $inspeccion[$col] ?? null;
            $esCondicional = $cfg['grupo'] === 'condicional';
            if ($esCondicional && empty($inspeccion['tiene_guadaniadora'])) continue;
            $info = $val ? ($calificaciones[$val] ?? null) : null;
        ?>
            <tr>
                <td style="text-align:center; font-weight:bold;"><?= $num ?></td>
                <td style="text-align:left;">
                    <?= esc($cfg['label']) ?>
                    <?php if ($esCondicional): ?><span style="color:#856404; font-size:7px;"> [COMBUSTIBLE]</span><?php endif; ?>
                </td>
                <td class="cal-cell" style="<?= $info ? 'background:'.$info['bg'].'; color:'.$info['color'].';' : 'color:#888;' ?>">
                    <?= $val ?: '-' ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <!-- SEMAFORO DE INTERPRETACION -->
    <div class="section-title">INTERPRETACION DEL RESULTADO</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:90px;">Rango</th>
                <th style="width:80px;">Nivel</th>
                <th style="text-align:left;">Interpretacion</th>
            </tr>
        </thead>
        <tbody>
            <tr <?= $nivel === 'alto' ? 'style="background:#d4edda; font-weight:bold;"' : '' ?>>
                <td style="text-align:center;">90% - 100%</td>
                <td style="text-align:center; color:#155724;">Alto</td>
                <td style="text-align:left;">Control adecuado. El area cumple con los requisitos de seguridad quimica.</td>
            </tr>
            <tr <?= $nivel === 'medio' ? 'style="background:#fff3cd; font-weight:bold;"' : '' ?>>
                <td style="text-align:center;">70% - 89%</td>
                <td style="text-align:center; color:#856404;">Medio</td>
                <td style="text-align:left;">Requiere mejoras. Existen brechas que deben corregirse en el corto plazo.</td>
            </tr>
            <tr <?= $nivel === 'bajo' ? 'style="background:#f8d7da; font-weight:bold;"' : '' ?>>
                <td style="text-align:center;">&lt; 70%</td>
                <td style="text-align:center; color:#721c24;">Bajo</td>
                <td style="text-align:left;">Riesgo significativo. Se requieren acciones inmediatas de control.</td>
            </tr>
        </tbody>
    </table>

    <!-- FOTOS -->
    <?php if (!empty($fotos)): ?>
    <div class="section-title">EVIDENCIA FOTOGRAFICA</div>
    <table style="width:100%;">
        <?php foreach (array_chunk($fotos, 2) as $par): ?>
        <tr>
            <?php foreach ($par as $f): ?>
            <td style="width:50%; text-align:center; vertical-align:top; padding:6px;">
                <?php if (!empty($f['foto_base64'])): ?>
                <img src="<?= $f['foto_base64'] ?>" class="foto-small"><br>
                <?php endif; ?>
                <div style="font-size:8px; color:#333; margin-top:3px; text-align:left; padding:3px; background:#f7f7f7; border:1px solid #ddd;">
                    <strong>Foto #<?= $f['orden'] ?>:</strong>
                    <?= !empty($f['observacion']) ? esc($f['observacion']) : '<span style="color:#888;">Sin observacion</span>' ?>
                </div>
            </td>
            <?php endforeach; ?>
            <?php if (count($par) === 1): ?><td style="width:50%;">&nbsp;</td><?php endif; ?>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>

    <!-- OBSERVACIONES FINALES -->
    <?php if (!empty($inspeccion['observaciones_finales'])): ?>
    <div class="section-title">OBSERVACIONES FINALES</div>
    <p style="font-size:9px; line-height:1.4;"><?= nl2br(esc($inspeccion['observaciones_finales'])) ?></p>
    <?php endif; ?>

</body>
</html>
