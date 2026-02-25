<?php
$alarmaSeleccionados = !empty($inspeccion['tipo_alarma']) ? explode(',', $inspeccion['tipo_alarma']) : [];
$distintivosSeleccionados = !empty($inspeccion['distintivos_brigadistas']) ? explode(',', $inspeccion['distintivos_brigadistas']) : [];
$equiposSeleccionados = !empty($inspeccion['equipos_emergencia']) ? explode(',', $inspeccion['equipos_emergencia']) : [];

$tiempoTotal = '';
if (!empty($inspeccion['hora_inicio']) && !empty($inspeccion['agradecimiento_cierre'])) {
    $inicio = new \DateTime($inspeccion['hora_inicio']);
    $fin = new \DateTime($inspeccion['agradecimiento_cierre']);
    $diff = $inicio->diff($fin);
    $tiempoTotal = $diff->format('%H:%I:%S');
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

        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; border: 1px solid #ccc; }
        .data-table th { background: #e8e8e8; padding: 4px 6px; font-size: 9px; border: 1px solid #ccc; text-align: left; }
        .data-table td { padding: 3px 6px; font-size: 9px; border: 1px solid #ccc; }

        .badge-item { display: inline-block; padding: 2px 6px; background: #1c2437; color: white; font-size: 8px; font-weight: bold; margin: 1px 2px; }

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
            <td class="header-code">Codigo: FT-SST-223<br>Version: 001</td>
        </tr>
        <tr>
            <td class="header-title" style="font-size:10px;">PREPARACION Y GUION DEL SIMULACRO</td>
            <td class="header-code">Fecha: <?= date('d/m/Y', strtotime($inspeccion['fecha_simulacro'])) ?></td>
        </tr>
    </table>

    <!-- TITULO -->
    <div class="main-title">PREPARACION Y GUION DEL SIMULACRO</div>
    <div class="main-subtitle"><?= esc($cliente['nombre_cliente'] ?? '') ?></div>

    <!-- DATOS DE LA INSPECCION -->
    <div class="section-title">DATOS GENERALES</div>
    <table class="info-table">
        <tr>
            <td class="info-label">CLIENTE:</td>
            <td><?= esc($cliente['nombre_cliente'] ?? '') ?></td>
            <td class="info-label">FECHA:</td>
            <td><?= date('d/m/Y', strtotime($inspeccion['fecha_simulacro'])) ?></td>
        </tr>
        <tr>
            <td class="info-label">DIRECCION:</td>
            <td><?= esc($inspeccion['direccion'] ?? '') ?></td>
            <td class="info-label">UBICACION:</td>
            <td><?= esc($inspeccion['ubicacion'] ?? '') ?></td>
        </tr>
        <tr>
            <td class="info-label">CONSULTOR:</td>
            <td colspan="3"><?= esc($consultor['nombre_consultor'] ?? '') ?></td>
        </tr>
    </table>

    <!-- CONFIGURACION DEL SIMULACRO -->
    <div class="section-title">CONFIGURACION DEL SIMULACRO</div>
    <table class="info-table">
        <tr>
            <td class="info-label">EVENTO SIMULADO:</td>
            <td><?= esc(ucfirst($inspeccion['evento_simulado'] ?? '')) ?></td>
            <td class="info-label">ALCANCE:</td>
            <td><?= esc(ucfirst($inspeccion['alcance_simulacro'] ?? '')) ?></td>
        </tr>
        <tr>
            <td class="info-label">TIPO EVACUACION:</td>
            <td><?= esc(ucfirst($inspeccion['tipo_evacuacion'] ?? '')) ?></td>
            <td class="info-label">PERSONAL NO EVACUA:</td>
            <td><?= nl2br(esc($inspeccion['personal_no_evacua'] ?? '')) ?></td>
        </tr>
    </table>

    <!-- ALARMA Y DISTINTIVOS -->
    <div class="section-title">ALARMA Y DISTINTIVOS</div>
    <table class="info-table">
        <tr>
            <td class="info-label">TIPO DE ALARMA:</td>
            <td>
                <?php foreach ($alarmaSeleccionados as $val): ?>
                <span class="badge-item"><?= esc($opcionesAlarma[$val] ?? $val) ?></span>
                <?php endforeach; ?>
                <?php if (empty($alarmaSeleccionados)): ?>-<?php endif; ?>
            </td>
        </tr>
        <tr>
            <td class="info-label">DISTINTIVOS BRIGADISTAS:</td>
            <td>
                <?php foreach ($distintivosSeleccionados as $val): ?>
                <span class="badge-item"><?= esc($opcionesDistintivos[$val] ?? $val) ?></span>
                <?php endforeach; ?>
                <?php if (empty($distintivosSeleccionados)): ?>-<?php endif; ?>
            </td>
        </tr>
    </table>

    <!-- LOGISTICA -->
    <div class="section-title">LOGISTICA</div>
    <table class="info-table">
        <tr>
            <td class="info-label">PUNTOS DE ENCUENTRO:</td>
            <td><?= nl2br(esc($inspeccion['puntos_encuentro'] ?? '')) ?></td>
        </tr>
        <tr>
            <td class="info-label">RECURSO HUMANO:</td>
            <td><?= nl2br(esc($inspeccion['recurso_humano'] ?? '')) ?></td>
        </tr>
        <tr>
            <td class="info-label">EQUIPOS DE EMERGENCIA:</td>
            <td>
                <?php foreach ($equiposSeleccionados as $val): ?>
                <span class="badge-item"><?= esc($opcionesEquipos[$val] ?? $val) ?></span>
                <?php endforeach; ?>
                <?php if (empty($equiposSeleccionados)): ?>-<?php endif; ?>
            </td>
        </tr>
    </table>

    <!-- BRIGADISTA LIDER -->
    <div class="section-title">BRIGADISTA LIDER</div>
    <table class="info-table">
        <tr>
            <td class="info-label">NOMBRE:</td>
            <td><?= esc($inspeccion['nombre_brigadista_lider'] ?? '') ?></td>
            <td class="info-label">EMAIL:</td>
            <td><?= esc($inspeccion['email_brigadista_lider'] ?? '') ?></td>
        </tr>
        <tr>
            <td class="info-label">WHATSAPP:</td>
            <td colspan="3"><?= esc($inspeccion['whatsapp_brigadista_lider'] ?? '') ?></td>
        </tr>
    </table>

    <!-- CRONOGRAMA -->
    <div class="section-title">CRONOGRAMA</div>
    <table class="data-table">
        <tr>
            <th style="width:5%;">#</th>
            <th>Etapa</th>
            <th style="width:20%;">Hora</th>
        </tr>
        <?php $num = 1; foreach ($cronogramaItems as $key => $label): ?>
        <tr>
            <td style="text-align:center;"><?= $num++ ?></td>
            <td><?= $label ?></td>
            <td><?= !empty($inspeccion[$key]) ? esc($inspeccion[$key]) : '-' ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if ($tiempoTotal): ?>
        <tr style="background:#e8e8e8; font-weight:bold;">
            <td colspan="2" style="text-align:right; padding-right:10px;">TIEMPO TOTAL</td>
            <td><?= $tiempoTotal ?></td>
        </tr>
        <?php endif; ?>
    </table>

    <!-- EVALUACION -->
    <div class="section-title">EVALUACION</div>
    <table class="info-table">
        <tr>
            <td class="info-label">ENTREGA FORMATO EVALUACION:</td>
            <td><?= esc(ucfirst($inspeccion['entrega_formato_evaluacion'] ?? '')) ?></td>
        </tr>
    </table>

    <!-- REGISTRO FOTOGRAFICO -->
    <?php if (!empty($fotosBase64['imagen_1']) || !empty($fotosBase64['imagen_2'])): ?>
    <div class="section-title">REGISTRO FOTOGRAFICO</div>
    <table style="width:100%; border-collapse:collapse; margin-bottom:8px;">
        <tr>
            <?php if (!empty($fotosBase64['imagen_1'])): ?>
            <td style="width:50%; text-align:center; padding:4px; vertical-align:top;">
                <div style="font-size:8px; font-weight:bold; margin-bottom:3px;">Imagen 1</div>
                <img src="<?= $fotosBase64['imagen_1'] ?>" style="max-width:200px; max-height:160px; border:1px solid #ccc;">
            </td>
            <?php endif; ?>
            <?php if (!empty($fotosBase64['imagen_2'])): ?>
            <td style="width:50%; text-align:center; padding:4px; vertical-align:top;">
                <div style="font-size:8px; font-weight:bold; margin-bottom:3px;">Imagen 2</div>
                <img src="<?= $fotosBase64['imagen_2'] ?>" style="max-width:200px; max-height:160px; border:1px solid #ccc;">
            </td>
            <?php endif; ?>
        </tr>
    </table>
    <?php endif; ?>

    <!-- OBSERVACIONES -->
    <?php if (!empty($inspeccion['observaciones'])): ?>
    <div class="section-title">OBSERVACIONES</div>
    <p class="content-text"><?= nl2br(esc($inspeccion['observaciones'])) ?></p>
    <?php endif; ?>

</body>
</html>
