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
        .info-label { font-weight: bold; color: #444; width: 100px; background: #f7f7f7; }

        /* Titulos de seccion */
        .section-title { background: #1c2437; color: white; padding: 4px 10px; font-weight: bold; font-size: 10px; margin: 10px 0 5px; }

        /* Tablas de datos */
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .data-table th { background: #e8e8e8; border: 1px solid #aaa; padding: 4px 6px; font-size: 9px; text-align: left; }
        .data-table td { border: 1px solid #ccc; padding: 3px 6px; font-size: 9px; vertical-align: top; }

        /* Texto contenido */
        .content-text { font-size: 10px; line-height: 1.5; margin-bottom: 6px; }
        .empty-text { color: #888; font-style: italic; font-size: 9px; margin-bottom: 6px; }

        /* Fotos hallazgos */
        .hallazgo-img { max-width: 160px; max-height: 120px; border: 1px solid #ccc; }
        .hallazgo-estado { padding: 2px 6px; font-size: 8px; font-weight: bold; }
        .estado-abierto { background: #fff3cd; color: #856404; }
        .estado-cerrado { background: #d4edda; color: #155724; }
        .estado-excedido { background: #f8d7da; color: #721c24; }

        .page-break { page-break-before: always; }
        .intro-text { font-size: 9px; line-height: 1.5; margin-bottom: 8px; text-align: justify; }
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
            <td class="header-code">Codigo: FT-SST-216<br>Version: 001</td>
        </tr>
        <tr>
            <td class="header-title" style="font-size:11px;">FORMATO DE INSPECCION LOCATIVA</td>
            <td class="header-code">Fecha: <?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td>
        </tr>
    </table>

    <!-- TITULO -->
    <div class="main-title">INSPECCION DE CONDICIONES LOCATIVAS</div>

    <!-- DATOS GENERALES -->
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

    <!-- TEXTO INTRODUCTORIO -->
    <div class="section-title">INTRODUCCION</div>
    <p class="intro-text">
        Las inspecciones de condiciones locativas son una herramienta fundamental dentro del Sistema de Gestion de Seguridad y Salud en el Trabajo (SG-SST), cuyo objetivo es identificar, evaluar y controlar los riesgos asociados al estado fisico de las instalaciones, estructuras, pisos, techos, escaleras, areas comunes y demas elementos que conforman el entorno de trabajo.
    </p>
    <p class="intro-text">
        Estas inspecciones permiten detectar de manera oportuna condiciones subestandar que puedan generar accidentes de trabajo o enfermedades laborales, tales como pisos en mal estado, grietas en paredes o techos, falta de señalizacion, iluminacion deficiente, obstruccion de rutas de evacuacion, entre otros factores que afectan la seguridad de los trabajadores, residentes y visitantes.
    </p>
    <p class="intro-text">
        La realizacion periodica de estas inspecciones es un requisito establecido en la normatividad colombiana vigente, en particular en el Decreto 1072 de 2015 y la Resolucion 0312 de 2019, que establecen la obligacion de implementar acciones de identificacion de peligros, evaluacion y valoracion de riesgos, asi como la adopcion de medidas preventivas y correctivas.
    </p>

    <!-- HALLAZGOS -->
    <div class="section-title">HALLAZGOS DE LA INSPECCION</div>

    <?php if (!empty($hallazgos)): ?>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:5%;">#</th>
                <th style="width:30%;">DESCRIPCION</th>
                <th style="width:25%;">IMAGEN HALLAZGO</th>
                <th style="width:25%;">IMAGEN CORRECCION</th>
                <th style="width:15%;">ESTADO</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($hallazgos as $i => $h): ?>
            <tr>
                <td style="text-align:center;"><?= $i + 1 ?></td>
                <td>
                    <?= esc($h['descripcion']) ?>
                    <?php if (!empty($h['observaciones'])): ?>
                        <br><em style="font-size:8px; color:#666;">Obs: <?= esc($h['observaciones']) ?></em>
                    <?php endif; ?>
                </td>
                <td style="text-align:center; padding:4px;">
                    <?php if (!empty($h['imagen_base64'])): ?>
                        <img src="<?= $h['imagen_base64'] ?>" class="hallazgo-img">
                        <?php if (!empty($h['fecha_hallazgo'])): ?>
                            <br><small style="font-size:7px;"><?= date('d/m/Y', strtotime($h['fecha_hallazgo'])) ?></small>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="empty-text">Sin foto</span>
                    <?php endif; ?>
                </td>
                <td style="text-align:center; padding:4px;">
                    <?php if (!empty($h['correccion_base64'])): ?>
                        <img src="<?= $h['correccion_base64'] ?>" class="hallazgo-img">
                        <?php if (!empty($h['fecha_correccion'])): ?>
                            <br><small style="font-size:7px;"><?= date('d/m/Y', strtotime($h['fecha_correccion'])) ?></small>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="empty-text">-</span>
                    <?php endif; ?>
                </td>
                <td style="text-align:center;">
                    <?php
                    $estadoClass = 'estado-abierto';
                    if ($h['estado'] === 'CERRADO') $estadoClass = 'estado-cerrado';
                    elseif (strpos($h['estado'], 'EXCEDIDO') !== false) $estadoClass = 'estado-excedido';
                    ?>
                    <span class="hallazgo-estado <?= $estadoClass ?>"><?= esc($h['estado']) ?></span>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p class="empty-text">No se registraron hallazgos en esta inspeccion.</p>
    <?php endif; ?>

    <!-- OBSERVACIONES -->
    <?php if (!empty($inspeccion['observaciones'])): ?>
    <div class="section-title">OBSERVACIONES GENERALES</div>
    <p class="content-text"><?= nl2br(esc($inspeccion['observaciones'])) ?></p>
    <?php endif; ?>

</body>
</html>
