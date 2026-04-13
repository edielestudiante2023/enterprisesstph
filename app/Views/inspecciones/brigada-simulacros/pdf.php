<?php
$mapSiNoParcial = ['si' => 'SI', 'no' => 'NO', 'parcial' => 'PARCIAL'];
$mapSiNo        = ['si' => 'SI', 'no' => 'NO'];
$mapTipoSim     = [
    'no_realizado' => 'NO REALIZADO',
    'escritorio'   => 'DE ESCRITORIO',
    'parcial'      => 'PARCIAL',
    'general'      => 'GENERAL',
];

function brig_pdf_cls(string $v): string {
    if ($v === 'si') return 'val-bueno';
    if ($v === 'parcial') return 'val-regular';
    return 'val-malo';
}
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
        .info-label { font-weight: bold; color: #444; width: 180px; background: #f7f7f7; }

        .section-title { background: #1c2437; color: white; padding: 3px 8px; font-weight: bold; font-size: 9px; margin: 8px 0 4px; }

        .intro-text { font-size: 8px; line-height: 1.4; margin-bottom: 4px; text-align: justify; }
        .intro-subtitle { font-weight: bold; font-size: 8px; margin: 3px 0 2px; }

        .val-bueno { color: #155724; font-weight: bold; }
        .val-regular { color: #856404; font-weight: bold; }
        .val-malo { color: #721c24; font-weight: bold; }

        .foto-small { max-width: 180px; max-height: 130px; border: 1px solid #ccc; }
        .text-block { font-size: 9px; line-height: 1.4; padding: 4px 6px; border: 1px solid #ddd; background: #fafafa; margin-bottom: 4px; white-space: pre-wrap; }
    </style>
</head>
<body>

    <!-- HEADER -->
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
            <td class="header-title" style="font-size:10px;">FORMATO INSPECCION DE BRIGADA Y SIMULACROS</td>
            <td class="header-code">Fecha: <?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td>
        </tr>
    </table>

    <div class="main-title">INSPECCION DE BRIGADA DE EMERGENCIA Y SIMULACROS<br><?= esc($cliente['nombre_cliente'] ?? '') ?></div>

    <!-- FUNDAMENTACION -->
    <div class="section-title">FUNDAMENTACION NORMATIVA</div>
    <p class="intro-text">
        La presente inspeccion se fundamenta en el marco legal colombiano aplicable a la organizacion de brigadas de emergencia y la realizacion de simulacros en copropiedades y lugares de trabajo.
    </p>
    <p class="intro-subtitle">1. Marco legal:</p>
    <p class="intro-text">
        <strong>Decreto 1072 de 2015 (art. 2.2.4.6.25):</strong> Obliga a los empleadores a implementar y mantener un plan de prevencion, preparacion y respuesta ante emergencias, incluyendo la conformacion, capacitacion y dotacion de brigadas de emergencia.<br>
        <strong>Resolucion 0312 de 2019:</strong> Estandares minimos del SG-SST, incluyendo la existencia y funcionamiento de la brigada de emergencia y la ejecucion de simulacros.<br>
        <strong>Ley 675 de 2001:</strong> Regimen de propiedad horizontal. El administrador tiene el deber de velar por la seguridad de bienes y personas en las zonas comunes.<br>
        <strong>NTC 1700 y NTC 2885:</strong> Criterios tecnicos para la evacuacion de edificios y el manejo de extintores portatiles.
    </p>
    <p class="intro-subtitle">2. Objetivo de la inspeccion:</p>
    <p class="intro-text">
        Verificar el estado de conformacion, capacitacion, dotacion y entrenamiento operativo de la brigada de emergencia, asi como el cumplimiento del programa anual de simulacros, con el fin de identificar brechas frente a la normatividad aplicable y generar recomendaciones tecnicas.
    </p>

    <!-- DATOS INSPECCION -->
    <div class="section-title">DATOS DE LA INSPECCION</div>
    <table class="info-table">
        <tr>
            <td class="info-label">CLIENTE:</td>
            <td><?= esc($cliente['nombre_cliente'] ?? '') ?></td>
        </tr>
        <tr>
            <td class="info-label">FECHA DE INSPECCION:</td>
            <td><?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td>
        </tr>
        <tr>
            <td class="info-label">CONSULTOR RESPONSABLE:</td>
            <td><?= esc($consultor['nombre_consultor'] ?? '') ?></td>
        </tr>
    </table>

    <!-- ESTADO BRIGADA -->
    <div class="section-title">ESTADO ACTUAL DE LA BRIGADA</div>
    <table class="info-table">
        <tr>
            <td class="info-label">Existe brigada conformada:</td>
            <td class="<?= brig_pdf_cls($inspeccion['existe_brigada'] ?? 'no') ?>">
                <?= esc($mapSiNoParcial[$inspeccion['existe_brigada'] ?? 'no'] ?? 'NO') ?>
            </td>
        </tr>
        <?php if (!empty($inspeccion['fecha_conformacion'])): ?>
        <tr>
            <td class="info-label">Fecha de conformacion:</td>
            <td><?= date('d/m/Y', strtotime($inspeccion['fecha_conformacion'])) ?></td>
        </tr>
        <?php endif; ?>
        <tr>
            <td class="info-label">Numero de brigadistas:</td>
            <td><?= (int)($inspeccion['numero_brigadistas'] ?? 0) ?></td>
        </tr>
        <?php if (!empty($inspeccion['nombre_jefe_brigada'])): ?>
        <tr>
            <td class="info-label">Jefe de brigada:</td>
            <td><?= esc($inspeccion['nombre_jefe_brigada']) ?></td>
        </tr>
        <?php endif; ?>
        <tr>
            <td class="info-label">Brigada capacitada:</td>
            <td class="<?= brig_pdf_cls($inspeccion['brigada_capacitada'] ?? 'no') ?>">
                <?= esc($mapSiNoParcial[$inspeccion['brigada_capacitada'] ?? 'no'] ?? 'NO') ?>
            </td>
        </tr>
        <tr>
            <td class="info-label">Cuenta con dotacion:</td>
            <td class="<?= brig_pdf_cls($inspeccion['cuenta_dotacion'] ?? 'no') ?>">
                <?= esc($mapSiNoParcial[$inspeccion['cuenta_dotacion'] ?? 'no'] ?? 'NO') ?>
            </td>
        </tr>
    </table>
    <?php if (!empty($inspeccion['detalle_dotacion'])): ?>
    <p class="intro-subtitle">Detalle de la dotacion:</p>
    <div class="text-block"><?= esc($inspeccion['detalle_dotacion']) ?></div>
    <?php endif; ?>

    <!-- CAPACITACIONES -->
    <div class="section-title">CAPACITACIONES</div>
    <table class="info-table">
        <?php
        $capFields = [
            'capacitacion_primeros_auxilios' => 'Primeros auxilios',
            'capacitacion_extintores'        => 'Manejo de extintores',
            'capacitacion_evacuacion'        => 'Evacuacion',
            'capacitacion_busqueda_rescate'  => 'Busqueda y rescate',
            'capacitacion_comunicaciones'    => 'Comunicaciones',
        ];
        foreach ($capFields as $campo => $lbl):
            $v = $inspeccion[$campo] ?? 'no';
        ?>
        <tr>
            <td class="info-label"><?= $lbl ?>:</td>
            <td class="<?= brig_pdf_cls($v) ?>"><?= esc($mapSiNo[$v] ?? 'NO') ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!empty($inspeccion['fecha_ultima_capacitacion'])): ?>
        <tr>
            <td class="info-label">Fecha ultima capacitacion:</td>
            <td><?= date('d/m/Y', strtotime($inspeccion['fecha_ultima_capacitacion'])) ?></td>
        </tr>
        <?php endif; ?>
    </table>
    <?php if (!empty($inspeccion['capacitaciones_12m'])): ?>
    <p class="intro-subtitle">Capacitaciones realizadas en los ultimos 12 meses:</p>
    <div class="text-block"><?= esc($inspeccion['capacitaciones_12m']) ?></div>
    <?php endif; ?>

    <!-- SIMULACROS -->
    <div class="section-title">SIMULACROS</div>
    <table class="info-table">
        <?php if (!empty($inspeccion['fecha_ultimo_simulacro'])): ?>
        <tr>
            <td class="info-label">Fecha ultimo simulacro:</td>
            <td><?= date('d/m/Y', strtotime($inspeccion['fecha_ultimo_simulacro'])) ?></td>
        </tr>
        <?php endif; ?>
        <tr>
            <td class="info-label">Tipo de simulacro:</td>
            <td><?= esc($mapTipoSim[$inspeccion['tipo_simulacro'] ?? 'no_realizado'] ?? 'NO REALIZADO') ?></td>
        </tr>
        <tr>
            <td class="info-label">Participo en simulacro nacional:</td>
            <td class="<?= brig_pdf_cls($inspeccion['participo_simulacro_nacional'] ?? 'no') ?>">
                <?= esc($mapSiNo[$inspeccion['participo_simulacro_nacional'] ?? 'no'] ?? 'NO') ?>
            </td>
        </tr>
        <tr>
            <td class="info-label">Cantidad de simulacros (12 meses):</td>
            <td><?= (int)($inspeccion['cantidad_simulacros_12m'] ?? 0) ?></td>
        </tr>
    </table>

    <!-- HALLAZGOS -->
    <?php
    $hallazgos = [
        'fortalezas'      => 'FORTALEZAS',
        'debilidades'     => 'DEBILIDADES',
        'recomendaciones' => 'RECOMENDACIONES',
        'observaciones'   => 'OBSERVACIONES',
    ];
    $hayHall = false;
    foreach (array_keys($hallazgos) as $c) { if (!empty($inspeccion[$c])) { $hayHall = true; break; } }
    ?>
    <?php if ($hayHall): ?>
    <div class="section-title">HALLAZGOS Y RECOMENDACIONES</div>
    <?php foreach ($hallazgos as $campo => $lbl): ?>
        <?php if (!empty($inspeccion[$campo])): ?>
        <p class="intro-subtitle"><?= $lbl ?>:</p>
        <div class="text-block"><?= esc($inspeccion[$campo]) ?></div>
        <?php endif; ?>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- REGISTRO FOTOGRAFICO -->
    <?php
    $hayFotos = false;
    foreach (array_keys($fotoLabels) as $c) { if (!empty($fotosBase64[$c])) { $hayFotos = true; break; } }
    ?>
    <?php if ($hayFotos): ?>
    <div class="section-title">REGISTRO FOTOGRAFICO</div>
    <table style="width:100%; border-collapse: collapse;">
        <tr>
        <?php $col = 0; foreach ($fotoLabels as $campo => $lbl): ?>
            <?php if (!empty($fotosBase64[$campo])): ?>
                <?php if ($col > 0 && $col % 2 === 0): ?></tr><tr><?php endif; ?>
                <td style="width:50%; padding:4px; text-align:center; border:1px solid #ddd;">
                    <div style="font-size:8px; color:#555; margin-bottom:2px;"><?= esc($lbl) ?></div>
                    <img src="<?= $fotosBase64[$campo] ?>" class="foto-small">
                </td>
                <?php $col++; ?>
            <?php endif; ?>
        <?php endforeach; ?>
        <?php if ($col % 2 !== 0): ?><td style="width:50%; border:1px solid #ddd;">&nbsp;</td><?php endif; ?>
        </tr>
    </table>
    <?php endif; ?>

    <!-- FIRMA -->
    <br><br>
    <table style="width:100%; margin-top: 20px;">
        <tr>
            <td style="width:50%; text-align:center; padding: 10px;">
                <div style="border-top:1px solid #333; padding-top:4px; font-size:9px;">
                    <?= esc($consultor['nombre_consultor'] ?? '') ?><br>
                    <strong>Consultor responsable SST</strong>
                </div>
            </td>
            <td style="width:50%; text-align:center; padding: 10px;">
                <div style="border-top:1px solid #333; padding-top:4px; font-size:9px;">
                    <br>
                    <strong>Administracion / Representante</strong>
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
