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
        El presente documento tiene como finalidad formalizar la entrega y aceptacion de responsabilidades en materia de Seguridad y Salud en el Trabajo (SST) dentro de la PROPIEDAD HORIZONTAL, de acuerdo con la normativa vigente y con el objetivo de garantizar la gestion efectiva de los riesgos laborales y la proteccion de los trabajadores y contratistas.
    </p>

    <div class="resp-title">1. RESPONSABILIDADES DEL ADMINISTRADOR:</div>
    <p class="resp-text">
        1. Definir, firmar y divulgar la politica de Seguridad y Salud en el Trabajo, asegurando su cumplimiento y revision periodica.<br>
        2. Asignar y comunicar las responsabilidades especificas en SST a todos los niveles de la PROPIEDAD HORIZONTAL.<br>
        3. Presentar informes de rendicion de cuentas sobre el desempeno en SST ante el consejo y la comunidad.<br>
        4. Garantizar los recursos necesarios (financieros, tecnicos y de personal) para el diseno, implementacion y mejora del SG-SST.<br>
        5. Asegurar la consulta y participacion del asesor en SST y del vigia en la identificacion de peligros y la implementacion de medidas de control.<br>
        6. Revisar el cumplimiento del plan de trabajo anual de SST y la ejecucion de los recursos asignados.<br>
        7. Evaluar al menos una vez al ano la gestion de SST e implementar mejoras necesarias.<br>
        8. Garantizar la disponibilidad de personal competente para liderar el SG-SST.<br>
        9. Asegurar la ejecucion de programas de capacitacion en SST, incluyendo induccion y entrenamiento a contratistas.<br>
        10. Realizar auditorias internas anuales al SG-SST.
    </p>

    <div class="resp-title">2. RESPONSABILIDADES DEL RESPONSABLE DEL SG-SST:</div>
    <p class="resp-text">
        1. Elaborar y ejecutar el programa anual de capacitacion en prevencion de riesgos laborales.<br>
        2. Reportar al administrador cualquier situacion que pueda afectar la seguridad y salud de los trabajadores.<br>
        3. Gestionar la documentacion requerida a contratistas en materia de SST.<br>
        4. Actualizar la matriz de riesgos y asegurar su aplicacion efectiva.<br>
        5. Realizar inspecciones programadas y no programadas para verificar el cumplimiento de SST.<br>
        6. Participar en la investigacion de incidentes, accidentes y enfermedades laborales, asegurando la implementacion de correctivos.<br>
        7. Atender auditorias externas y visitas de entes reguladores.<br>
        8. Preparar y presentar el Plan Anual de SST para su aprobacion.<br>
        9. Ejecutar planes de accion derivados de auditorias e inspecciones.<br>
        10. Mantener actualizado el sistema de indicadores de SST.
    </p>

    <div class="resp-title">3. RESPONSABILIDADES DEL VIGIA DE SEGURIDAD Y SALUD EN EL TRABAJO:</div>
    <p class="resp-text">
        1. Proponer actividades para mejorar las condiciones de seguridad y salud en el trabajo.<br>
        2. Participar en actividades de capacitacion en SST y promover su cumplimiento.<br>
        3. Colaborar con autoridades en inspecciones y auditorias.<br>
        4. Vigilar el cumplimiento de las normas de SST en la PROPIEDAD HORIZONTAL.<br>
        5. Reportar condiciones de riesgo y sugerir medidas de control.<br>
        6. Servir de enlace entre la administracion y los contratistas en temas de SST.<br>
        7. Apoyar la revision de estadisticas de accidentalidad y enfermedades laborales.<br>
        8. Mantener registros de actividades realizadas en SST.<br>
        9. Coordinar acciones de respuesta ante emergencias.<br>
        10. Cumplir con las demas funciones establecidas en la normativa de SST.
    </p>

    <div class="resp-title">4. RESPONSABILIDADES DE LOS TRABAJADORES Y CONTRATISTAS:</div>
    <p class="resp-text">
        1. Conocer, entender y aplicar la politica de SST.<br>
        2. Identificar y reportar peligros y riesgos en su entorno de trabajo.<br>
        3. Utilizar correctamente los Elementos de Proteccion Personal (EPP).<br>
        4. Asistir a capacitaciones y entrenamientos en SST.<br>
        5. Cumplir con las normas de seguridad establecidas.<br>
        6. Informar de inmediato cualquier accidente o incidente de trabajo.<br>
        7. Mantener el orden y la limpieza en su area de trabajo.<br>
        8. Aplicar los procedimientos establecidos en emergencias.<br>
        9. Participar en la evaluacion de peligros y riesgos en el lugar de trabajo.<br>
        10. Colaborar en la implementacion de medidas de prevencion y control.
    </p>

    <div class="section-title">5. FIRMA DE ACEPTACION DE RESPONSABILIDADES</div>
    <p class="resp-text">
        Con la firma de este documento, cada una de las partes acepta las responsabilidades establecidas en materia de Seguridad y Salud en el Trabajo dentro de la PROPIEDAD HORIZONTAL.
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
