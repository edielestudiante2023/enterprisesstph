<?php
$isAsistencia = ($pdfType ?? 'asistencia') === 'asistencia';
$codigoPdf = $isAsistencia ? 'FT-SST-005' : 'FT-SST-003';
$tituloPdf = $isAsistencia ? 'LISTADO DE ASISTENCIA' : 'ACTA DE RESPONSABILIDADES EN SST';
$tipoLabel = $tiposCharla[$inspeccion['tipo_charla'] ?? ''] ?? $inspeccion['tipo_charla'] ?? '';
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
        .info-label { font-weight: bold; color: #444; width: 130px; background: #f7f7f7; }

        .section-title { background: #1c2437; color: white; padding: 3px 8px; font-weight: bold; font-size: 9px; margin: 8px 0 4px; }

        .asist-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; border: 1px solid #ccc; }
        .asist-table th { background: #e8e8e8; padding: 4px 6px; font-size: 8px; border: 1px solid #ccc; text-align: left; }
        .asist-table td { padding: 3px 6px; font-size: 8px; border: 1px solid #ccc; }

        .content-text { font-size: 9px; line-height: 1.4; margin-bottom: 5px; }
        .resp-text { font-size: 8.5px; line-height: 1.5; margin-bottom: 6px; text-align: justify; }
        .resp-title { font-size: 9px; font-weight: bold; margin: 6px 0 3px; color: #1c2437; }

        .firma-img { max-width: 80px; max-height: 40px; }
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
            <td class="header-code">Codigo: <?= $codigoPdf ?><br>Version: V001</td>
        </tr>
        <tr>
            <td class="header-title" style="font-size:10px;"><?= $tituloPdf ?></td>
            <td class="header-code">Fecha: <?= date('d/m/Y', strtotime($inspeccion['fecha_sesion'])) ?></td>
        </tr>
    </table>

    <!-- TITULO -->
    <div class="main-title"><?= $tituloPdf ?></div>
    <div class="main-subtitle"><?= esc($cliente['nombre_cliente'] ?? '') ?></div>

<?php if ($isAsistencia): ?>
    <!-- ==================== FT-SST-005 LISTADO DE ASISTENCIA ==================== -->

    <!-- DATOS DE LA SESION -->
    <div class="section-title">DATOS DE LA SESION</div>
    <table class="info-table">
        <tr>
            <td class="info-label">TEMA:</td>
            <td colspan="3"><?= esc($inspeccion['tema'] ?? '') ?></td>
        </tr>
        <tr>
            <td class="info-label">LUGAR:</td>
            <td><?= esc($inspeccion['lugar'] ?? '') ?></td>
            <td class="info-label">FECHA:</td>
            <td><?= date('d/m/Y', strtotime($inspeccion['fecha_sesion'])) ?></td>
        </tr>
        <tr>
            <td class="info-label">OBJETIVO:</td>
            <td colspan="3"><?= nl2br(esc($inspeccion['objetivo'] ?? '')) ?></td>
        </tr>
        <tr>
            <td class="info-label">MATERIAL:</td>
            <td><?= esc($inspeccion['material'] ?? '') ?></td>
            <td class="info-label">TIPO:</td>
            <td><?= esc($tipoLabel) ?></td>
        </tr>
        <tr>
            <td class="info-label">TIEMPO (HORAS):</td>
            <td><?= esc($inspeccion['tiempo_horas'] ?? '') ?></td>
            <td class="info-label">CAPACITADOR:</td>
            <td><?= esc($inspeccion['capacitador'] ?? '') ?></td>
        </tr>
    </table>

    <!-- LISTADO DE ASISTENTES -->
    <div class="section-title">LISTADO DE ASISTENTES</div>
    <table class="asist-table">
        <tr>
            <th style="width:5%; text-align:center;">#</th>
            <th style="width:30%;">NOMBRE</th>
            <th style="width:18%;">CEDULA</th>
            <th style="width:22%;">CARGO</th>
            <th style="width:25%; text-align:center;">FIRMA</th>
        </tr>
        <?php $num = 1; foreach ($asistentes as $a): ?>
        <tr>
            <td style="text-align:center;"><?= $num++ ?></td>
            <td><?= esc($a['nombre']) ?></td>
            <td><?= esc($a['cedula']) ?></td>
            <td><?= esc($a['cargo']) ?></td>
            <td style="text-align:center;">
                <?php if (!empty($a['firma_base64'])): ?>
                <img src="<?= $a['firma_base64'] ?>" class="firma-img">
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <!-- OBSERVACIONES -->
    <?php if (!empty($inspeccion['observaciones'])): ?>
    <div class="section-title">OBSERVACIONES</div>
    <p class="content-text"><?= nl2br(esc($inspeccion['observaciones'])) ?></p>
    <?php endif; ?>

<?php else: ?>
    <!-- ==================== FT-SST-003 ACTA DE RESPONSABILIDADES EN SST ==================== -->

    <div class="section-title">OBJETO</div>
    <p class="resp-text">
        Dar a conocer las responsabilidades en materia de Seguridad y Salud en el Trabajo a los trabajadores, contratistas y subcontratistas.
    </p>

    <div class="resp-title">RESPONSABILIDADES DE LA ADMINISTRACION:</div>
    <p class="resp-text">
        1. Definir, firmar y divulgar la politica de SST.<br>
        2. Asignar y comunicar responsabilidades en SST.<br>
        3. Rendir cuentas del desempeno del SG-SST.<br>
        4. Cumplir los requisitos normativos aplicables.<br>
        5. Realizar el plan de trabajo anual en SST.
    </p>

    <div class="resp-title">RESPONSABILIDADES DEL RESPONSABLE DEL SG-SST:</div>
    <p class="resp-text">
        1. Planificar, organizar, dirigir y controlar el SG-SST.<br>
        2. Informar a la alta direccion sobre el desempeno del SG-SST.<br>
        3. Promover la participacion de todos los miembros de la organizacion.
    </p>

    <div class="resp-title">RESPONSABILIDADES DE LOS VIGIAS:</div>
    <p class="resp-text">
        1. Proponer medidas preventivas y correctivas.<br>
        2. Participar en las actividades del SG-SST.<br>
        3. Vigilar el cumplimiento de las normas de SST.
    </p>

    <div class="resp-title">RESPONSABILIDADES DE LOS TRABAJADORES Y CONTRATISTAS:</div>
    <p class="resp-text">
        1. Procurar el cuidado integral de su salud.<br>
        2. Cumplir normas y reglamentos de SST.<br>
        3. Participar en las actividades de capacitacion.<br>
        4. Informar condiciones de trabajo que afecten su seguridad.<br>
        5. Usar adecuadamente los EPP.
    </p>

    <!-- LISTADO DE ASISTENTES (firmas de aceptacion) -->
    <div class="section-title">REGISTRO DE ASISTENTES - ACEPTACION DE RESPONSABILIDADES</div>
    <table class="asist-table">
        <tr>
            <th style="width:5%; text-align:center;">#</th>
            <th style="width:30%;">NOMBRE</th>
            <th style="width:18%;">CEDULA</th>
            <th style="width:22%;">CARGO</th>
            <th style="width:25%; text-align:center;">FIRMA</th>
        </tr>
        <?php $num = 1; foreach ($asistentes as $a): ?>
        <tr>
            <td style="text-align:center;"><?= $num++ ?></td>
            <td><?= esc($a['nombre']) ?></td>
            <td><?= esc($a['cedula']) ?></td>
            <td><?= esc($a['cargo']) ?></td>
            <td style="text-align:center;">
                <?php if (!empty($a['firma_base64'])): ?>
                <img src="<?= $a['firma_base64'] ?>" class="firma-img">
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

<?php endif; ?>

</body>
</html>
