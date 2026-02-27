<?php
$perfilesSeleccionados = [];
if (!empty($inspeccion['perfil_asistentes'])) {
    $perfilesSeleccionados = explode(',', $inspeccion['perfil_asistentes']);
}
$perfilesLabels = [];
foreach ($perfilesSeleccionados as $p) {
    $perfilesLabels[] = $perfilesAsistentes[trim($p)] ?? trim($p);
}
$cobertura = 0;
if (!empty($inspeccion['numero_programados']) && $inspeccion['numero_programados'] > 0) {
    $cobertura = round(($inspeccion['numero_asistentes'] / $inspeccion['numero_programados']) * 100, 1);
}
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

        .indicadores-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; border: 1px solid #ccc; }
        .indicadores-table th { background: #e8e8e8; padding: 4px 6px; font-size: 9px; border: 1px solid #ccc; text-align: left; }
        .indicadores-table td { padding: 3px 6px; font-size: 9px; border: 1px solid #ccc; }

        .foto-container { text-align: center; margin: 4px 0; }
        .foto-container img { max-width: 220px; max-height: 160px; border: 1px solid #ccc; }

        .badge-perfil { padding: 2px 6px; background: #17a2b8; color: white; font-size: 8px; font-weight: bold; margin-right: 3px; }
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
            <td class="header-code">Codigo: FT-SST-211<br>Version: 001</td>
        </tr>
        <tr>
            <td class="header-title" style="font-size:10px;">REPORTE DE CAPACITACION</td>
            <td class="header-code">Fecha: <?= date('d/m/Y', strtotime($inspeccion['fecha_capacitacion'])) ?></td>
        </tr>
    </table>

    <!-- TITULO -->
    <div class="main-title">REPORTE DE CAPACITACION</div>
    <div class="main-subtitle"><?= esc($cliente['nombre_cliente'] ?? '') ?></div>

    <!-- INTRODUCCION -->
    <div class="section-title">INTRODUCCION</div>
    <p class="intro-text">
        Las capacitaciones en Seguridad y Salud en el Trabajo (SST) son una herramienta fundamental para promover la prevencion de riesgos laborales, fomentar una cultura de seguridad y cumplir con la normativa vigente en Colombia, especialmente el Decreto 1072 de 2015 y la Resolucion 0312 de 2019.
    </p>

    <!-- DATOS DE LA CAPACITACION -->
    <div class="section-title">DATOS DE LA CAPACITACION</div>
    <table class="info-table">
        <tr>
            <td class="info-label">CLIENTE:</td>
            <td colspan="3"><?= esc($cliente['nombre_cliente'] ?? '') ?></td>
        </tr>
        <tr>
            <td class="info-label">FECHA:</td>
            <td><?= date('d/m/Y', strtotime($inspeccion['fecha_capacitacion'])) ?></td>
            <td class="info-label">CONSULTOR:</td>
            <td><?= esc($consultor['nombre_consultor'] ?? '') ?></td>
        </tr>
        <tr>
            <td class="info-label">NOMBRE CAPACITACION:</td>
            <td colspan="3"><?= esc($inspeccion['nombre_capacitacion'] ?? '') ?></td>
        </tr>
        <tr>
            <td class="info-label">OBJETIVO:</td>
            <td colspan="3"><?= nl2br(esc($inspeccion['objetivo_capacitacion'] ?? '')) ?></td>
        </tr>
        <tr>
            <td class="info-label">PERFIL ASISTENTES:</td>
            <td colspan="3">
                <?php foreach ($perfilesLabels as $label): ?>
                    <span class="badge-perfil"><?= esc($label) ?></span>
                <?php endforeach; ?>
            </td>
        </tr>
        <tr>
            <td class="info-label">CAPACITADOR:</td>
            <td><?= esc($inspeccion['nombre_capacitador'] ?? '') ?></td>
            <td class="info-label">HORAS DURACION:</td>
            <td><?= esc($inspeccion['horas_duracion'] ?? '') ?></td>
        </tr>
    </table>

    <!-- INDICADORES -->
    <div class="section-title">INDICADORES DE ASISTENCIA Y EVALUACION</div>
    <table class="indicadores-table">
        <tr>
            <th>Indicador</th>
            <th style="width:25%; text-align:center;">Valor</th>
        </tr>
        <tr>
            <td>Numero de asistentes</td>
            <td style="text-align:center;"><?= esc($inspeccion['numero_asistentes'] ?? 0) ?></td>
        </tr>
        <tr>
            <td>Numero de programados</td>
            <td style="text-align:center;"><?= esc($inspeccion['numero_programados'] ?? 0) ?></td>
        </tr>
        <tr>
            <td>Numero de evaluados</td>
            <td style="text-align:center;"><?= esc($inspeccion['numero_evaluados'] ?? 0) ?></td>
        </tr>
        <tr>
            <td>% Cobertura (asistentes / programados x 100)</td>
            <td style="text-align:center; font-weight:bold;"><?= $cobertura ?>%</td>
        </tr>
        <tr>
            <td>Promedio de calificaciones</td>
            <td style="text-align:center; font-weight:bold;"><?= esc($inspeccion['promedio_calificaciones'] ?? '-') ?></td>
        </tr>
    </table>

    <!-- REGISTRO FOTOGRAFICO -->
    <?php
    $fotosCampos = [
        'foto_listado_asistencia' => 'Listado de Asistencia',
        'foto_capacitacion'       => 'Capacitacion',
        'foto_evaluacion'         => 'Evaluacion',
        'foto_otros_1'            => 'Otros 1',
        'foto_otros_2'            => 'Otros 2',
    ];
    $fotosDisponibles = [];
    foreach ($fotosCampos as $campo => $label) {
        if (!empty($fotosBase64[$campo])) {
            $fotosDisponibles[$campo] = ['label' => $label, 'src' => $fotosBase64[$campo]];
        }
    }
    ?>
    <?php if (!empty($fotosDisponibles)): ?>
    <div class="section-title">REGISTRO FOTOGRAFICO</div>
    <table style="width:100%; border-collapse:collapse; margin-bottom:8px;">
        <?php
        $chunks = array_chunk($fotosDisponibles, 2);
        foreach ($chunks as $row):
        ?>
        <tr>
            <?php foreach ($row as $foto): ?>
            <td style="width:50%; text-align:center; padding:4px; vertical-align:top;">
                <div style="font-size:8px; font-weight:bold; margin-bottom:3px;"><?= $foto['label'] ?></div>
                <img src="<?= $foto['src'] ?>" style="max-width:200px; max-height:160px; border:1px solid #ccc;">
            </td>
            <?php endforeach; ?>
            <?php if (count($row) === 1): ?>
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
