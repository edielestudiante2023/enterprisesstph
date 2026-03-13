<?php
$colorMap = [
    'bueno' => '#28a745', 'regular' => '#ffc107', 'malo' => '#fd7e14',
    'deficiente' => '#dc3545', 'no_tiene' => '#6c757d', 'no_aplica' => '#adb5bd',
];
$textColorMap = [
    'bueno' => 'white', 'regular' => '#333', 'malo' => 'white',
    'deficiente' => 'white', 'no_tiene' => 'white', 'no_aplica' => '#333',
];
// Calcular resumen de estados (solo ítems tipo enum)
$conteoEstados = ['bueno' => 0, 'regular' => 0, 'malo' => 0, 'deficiente' => 0, 'no_tiene' => 0, 'no_aplica' => 0];
$totalEvaluados = 0;
foreach ($itemsZona as $key => $info) {
    if ($info['tipo'] !== 'enum') continue;
    $estado = $inspeccion['estado_' . $key] ?? '';
    if (isset($conteoEstados[$estado])) {
        $conteoEstados[$estado]++;
    }
    if ($estado !== 'no_aplica' && !empty($estado)) {
        $totalEvaluados++;
    }
}
$puntajeBueno = ($conteoEstados['bueno'] ?? 0);
$porcentaje = $totalEvaluados > 0 ? round(($puntajeBueno / $totalEvaluados) * 100) : 0;
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
        .items-table td { padding: 3px 6px; font-size: 9px; border: 1px solid #ccc; vertical-align: top; }
        .estado-badge { padding: 2px 6px; font-size: 8px; font-weight: bold; display: inline-block; }

        .resumen-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; border: 1px solid #ccc; }
        .resumen-table td, .resumen-table th { padding: 3px 6px; font-size: 9px; border: 1px solid #ccc; text-align: center; }
        .resumen-table th { background: #e8e8e8; }

        .leyenda-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; border: 1px solid #ccc; }
        .leyenda-table td { padding: 2px 6px; font-size: 8px; border: 1px solid #ccc; }
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
        La correcta gestion de la zona de residuos en las propiedades horizontales es fundamental para garantizar un entorno seguro, saludable y en cumplimiento de la normativa ambiental vigente. La presente auditoria evalua las condiciones fisicas, de limpieza, seguridad y senalizacion del cuarto de basuras, asignando una calificacion a cada aspecto inspeccionado con su respectiva evidencia fotografica.
    </p>

    <!-- LEYENDA DE CRITERIOS -->
    <div class="section-title">CRITERIOS DE EVALUACION</div>
    <table class="leyenda-table">
        <tr>
            <td style="width:15%; text-align:center;"><span class="estado-badge" style="background:#28a745; color:white;">Bueno</span></td>
            <td>El aspecto cumple satisfactoriamente con los requisitos. No requiere accion correctiva.</td>
        </tr>
        <tr>
            <td style="text-align:center;"><span class="estado-badge" style="background:#ffc107; color:#333;">Regular</span></td>
            <td>El aspecto presenta condiciones aceptables pero con oportunidades de mejora. Se recomienda atencion preventiva.</td>
        </tr>
        <tr>
            <td style="text-align:center;"><span class="estado-badge" style="background:#fd7e14; color:white;">Malo</span></td>
            <td>El aspecto presenta deficiencias que requieren correccion a corto plazo para evitar riesgos.</td>
        </tr>
        <tr>
            <td style="text-align:center;"><span class="estado-badge" style="background:#dc3545; color:white;">Deficiente</span></td>
            <td>El aspecto presenta condiciones criticas que requieren accion correctiva inmediata.</td>
        </tr>
        <tr>
            <td style="text-align:center;"><span class="estado-badge" style="background:#6c757d; color:white;">No Tiene</span></td>
            <td>El elemento no existe en la zona de residuos. Debe evaluarse si es requerido por normativa.</td>
        </tr>
        <tr>
            <td style="text-align:center;"><span class="estado-badge" style="background:#adb5bd; color:#333;">No Aplica</span></td>
            <td>El aspecto no es aplicable a las caracteristicas de esta propiedad horizontal.</td>
        </tr>
    </table>

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

    <!-- HALLAZGOS DE LA INSPECCION (items + fotos inline) -->
    <div class="section-title">HALLAZGOS DE LA INSPECCION</div>
    <?php $num = 1; foreach ($itemsZona as $key => $info):
        $campo = 'foto_' . $key;
        $tieneFoto = !empty($fotosBase64[$campo]);
        if ($info['tipo'] === 'enum') {
            $estado = $inspeccion['estado_' . $key] ?? '';
            $estadoLabel = $estadosZona[$estado] ?? 'Sin evaluar';
            $color = $colorMap[$estado] ?? '#6c757d';
            $textColor = $textColorMap[$estado] ?? 'white';
        }
    ?>
    <table class="items-table" style="margin-bottom:4px;">
        <tr style="background:#e8e8e8;">
            <td style="width:5%; text-align:center; font-weight:bold;"><?= $num++ ?></td>
            <td style="font-weight:bold;"><?= $info['label'] ?></td>
            <td style="width:25%; text-align:center;">
                <?php if ($info['tipo'] === 'enum'): ?>
                <span class="estado-badge" style="background:<?= $color ?>; color:<?= $textColor ?>;"><?= $estadoLabel ?></span>
                <?php else: ?>
                <span style="font-size:8px; color:#555;"><?= esc($inspeccion[$key] ?? 'Sin informacion') ?></span>
                <?php endif; ?>
            </td>
        </tr>
        <?php if ($tieneFoto): ?>
        <tr>
            <td colspan="3" style="text-align:center; padding:6px;">
                <img src="<?= $fotosBase64[$campo] ?>" style="max-width:280px; max-height:180px; border:1px solid #ccc;">
            </td>
        </tr>
        <?php endif; ?>
    </table>
    <?php endforeach; ?>

    <!-- RESUMEN DE CALIFICACION -->
    <div class="section-title">RESUMEN DE CALIFICACION</div>
    <table class="resumen-table">
        <tr>
            <th>Estado</th>
            <?php foreach ($estadosZona as $val => $label): ?>
            <th><span class="estado-badge" style="background:<?= $colorMap[$val] ?? '#6c757d' ?>; color:<?= $textColorMap[$val] ?? 'white' ?>; font-size:7px;"><?= $label ?></span></th>
            <?php endforeach; ?>
            <th>Total Evaluados</th>
        </tr>
        <tr>
            <td style="font-weight:bold;">Cantidad</td>
            <?php foreach ($estadosZona as $val => $label): ?>
            <td><?= $conteoEstados[$val] ?? 0 ?></td>
            <?php endforeach; ?>
            <td style="font-weight:bold;"><?= $totalEvaluados ?></td>
        </tr>
    </table>

    <table class="info-table" style="margin-top:4px;">
        <tr>
            <td class="info-label" style="width:200px;">CUMPLIMIENTO (% BUENO):</td>
            <td style="text-align:center; font-weight:bold; font-size:11px;
                color:<?= $porcentaje >= 80 ? '#28a745' : ($porcentaje >= 50 ? '#fd7e14' : '#dc3545') ?>;">
                <?= $porcentaje ?>%
            </td>
            <td class="info-label" style="width:200px;">CALIFICACION:</td>
            <td style="text-align:center; font-weight:bold;
                color:<?= $porcentaje >= 80 ? '#28a745' : ($porcentaje >= 50 ? '#fd7e14' : '#dc3545') ?>;">
                <?php if ($porcentaje >= 80): ?>
                    SATISFACTORIO
                <?php elseif ($porcentaje >= 50): ?>
                    REQUIERE MEJORA
                <?php else: ?>
                    CRITICO
                <?php endif; ?>
            </td>
        </tr>
    </table>

    <!-- OBSERVACIONES -->
    <?php if (!empty($inspeccion['observaciones'])): ?>
    <div class="section-title">OBSERVACIONES Y RECOMENDACIONES</div>
    <p class="content-text"><?= nl2br(esc($inspeccion['observaciones'])) ?></p>
    <?php endif; ?>

</body>
</html>
