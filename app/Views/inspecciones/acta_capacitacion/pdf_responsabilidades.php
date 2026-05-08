<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 100px 70px 80px 90px; }
        body { margin: 0; padding: 0; font-family: DejaVu Sans, Arial, sans-serif; font-size: 10pt; line-height: 1.3; color: #333; }
        p, h1, h2, h3, h4, h5, h6, table, div, ul, li { margin: 0; padding: 0; }
        *, *::before, *::after { box-sizing: border-box; }

        .seccion { margin-bottom: 10px; }
        .seccion-titulo { font-size: 11pt; font-weight: bold; color: #0d6efd; border-bottom: 1px solid #e9ecef; padding-bottom: 3px; margin-bottom: 6px; margin-top: 10px; }
        .seccion-contenido { text-align: justify; }
        .seccion-contenido p { margin: 4px 0; }
        ul.responsabilidades { padding-left: 18px; margin: 4px 0; }
        ul.responsabilidades li { margin: 3px 0; }

        table.datos-general { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 9pt; }
        table.datos-general td { border: 1px solid #999; padding: 5px 8px; }
        .datos-label { font-weight: bold; width: 30%; background:#f8f9fa; }

        .firma-img { max-width: 200px; max-height: 90px; border-bottom: 1px solid #999; filter: contrast(1.6) brightness(0.6); }
        .empty-text { color: #888; font-style: italic; font-size: 9pt; }
        .pendiente-text { color:#d97706; font-style:italic; font-size:9pt; }

        .pie-documento { margin-top: 15px; padding-top: 8px; border-top: 1px solid #ccc; text-align: center; font-size: 8pt; color: #666; }
    </style>
</head>
<body>

    <!-- ENCABEZADO -->
    <table style="width:100%; border-collapse:collapse; margin-bottom:18px;" cellpadding="0" cellspacing="0">
        <tr>
            <td rowspan="2" style="width:100px; border:1px solid #333; padding:8px; text-align:center; vertical-align:middle; background:#fff;">
                <?php if (!empty($logoBase64)): ?>
                    <img src="<?= $logoBase64 ?>" style="max-width:80px; max-height:50px;">
                <?php else: ?>
                    <div style="font-size:8pt; font-weight:bold;"><?= esc($cliente['nombre_cliente'] ?? '') ?></div>
                <?php endif; ?>
            </td>
            <td style="border:1px solid #333; text-align:center; padding:6px 10px; vertical-align:middle;">
                <div style="font-size:10pt; font-weight:bold; color:#333;">
                    SISTEMA DE GESTIÓN DE SEGURIDAD Y SALUD EN EL TRABAJO
                </div>
            </td>
            <td rowspan="2" style="width:130px; border:1px solid #333; padding:0; vertical-align:middle;">
                <table style="width:100%; border-collapse:collapse;" cellpadding="0" cellspacing="0">
                    <tr><td style="border-bottom:1px solid #333; padding:3px 6px; font-size:8pt;"><span style="font-weight:bold;">Código:</span> FT-SST-003</td></tr>
                    <tr><td style="border-bottom:1px solid #333; padding:3px 6px; font-size:8pt;"><span style="font-weight:bold;">Versión:</span> 001</td></tr>
                    <tr><td style="padding:3px 6px; font-size:8pt;"><span style="font-weight:bold;">Vigencia:</span> <?= !empty($vigenciaContrato) ? date('d/m/Y', strtotime($vigenciaContrato)) : date('d/m/Y', strtotime($acta['fecha_capacitacion'])) ?></td></tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="border:1px solid #333; text-align:center; padding:6px 10px; vertical-align:middle;">
                <div style="font-size:10pt; font-weight:bold; color:#333;">
                    ACTA DE ACEPTACIÓN DE RESPONSABILIDADES SST
                </div>
            </td>
        </tr>
    </table>

    <!-- DATOS DEL ACTA -->
    <table class="datos-general">
        <tr>
            <td class="datos-label">NOMBRE DE LA PROPIEDAD HORIZONTAL:</td>
            <td><?= esc($cliente['nombre_cliente'] ?? '') ?></td>
        </tr>
        <tr>
            <td class="datos-label">FECHA:</td>
            <td><?= date('d/m/Y', strtotime($acta['fecha_capacitacion'])) ?></td>
        </tr>
    </table>

    <?php $nombreCli = esc($cliente['nombre_cliente'] ?? 'la copropiedad'); ?>

    <!-- OBJETO -->
    <div class="seccion">
        <div class="seccion-titulo">OBJETO DEL ACTA</div>
        <div class="seccion-contenido">
            <p>El presente documento tiene como finalidad formalizar la entrega y aceptación de responsabilidades en materia de Seguridad y Salud en el Trabajo (SST) dentro de <?= $nombreCli ?>, de acuerdo con la normativa vigente y con el objetivo de garantizar la gestión efectiva de los riesgos laborales y la protección de los trabajadores y contratistas.</p>
        </div>
    </div>

    <!-- 1. RESPONSABILIDADES DEL ADMINISTRADOR -->
    <div class="seccion">
        <div class="seccion-titulo">1. RESPONSABILIDADES DEL ADMINISTRADOR</div>
        <ul class="responsabilidades">
            <li>Definir, firmar y divulgar la política de Seguridad y Salud en el Trabajo, asegurando su cumplimiento y revisión periódica.</li>
            <li>Asignar y comunicar las responsabilidades específicas en SST a todos los niveles de <?= $nombreCli ?>.</li>
            <li>Presentar informes de rendición de cuentas sobre el desempeño en SST ante el consejo y la comunidad.</li>
            <li>Garantizar los recursos necesarios (financieros, técnicos y de personal) para el diseño, implementación y mejora del SG-SST.</li>
            <li>Asegurar la consulta y participación del asesor en SST y del vigía en la identificación de peligros y la implementación de medidas de control.</li>
            <li>Revisar el cumplimiento del plan de trabajo anual de SST y la ejecución de los recursos asignados.</li>
            <li>Evaluar al menos una vez al año la gestión de SST e implementar mejoras necesarias.</li>
            <li>Garantizar la disponibilidad de personal competente para liderar el SG-SST.</li>
            <li>Asegurar la ejecución de programas de capacitación en SST, incluyendo inducción y entrenamiento a contratistas.</li>
            <li>Realizar auditorías internas anuales al SG-SST.</li>
        </ul>
    </div>

    <!-- 2. RESPONSABILIDADES DEL RESPONSABLE DEL SG-SST -->
    <div class="seccion">
        <div class="seccion-titulo">2. RESPONSABILIDADES DEL RESPONSABLE DEL SG-SST</div>
        <ul class="responsabilidades">
            <li>Elaborar y ejecutar el programa anual de capacitación en prevención de riesgos laborales.</li>
            <li>Reportar al administrador cualquier situación que pueda afectar la seguridad y salud de los trabajadores.</li>
            <li>Gestionar la documentación requerida a contratistas en materia de SST.</li>
            <li>Actualizar la matriz de riesgos y asegurar su aplicación efectiva.</li>
            <li>Realizar inspecciones programadas y no programadas para verificar el cumplimiento de SST.</li>
            <li>Participar en la investigación de incidentes, accidentes y enfermedades laborales, asegurando la implementación de correctivos.</li>
            <li>Atender auditorías externas y visitas de entes reguladores.</li>
            <li>Preparar y presentar el Plan Anual de SST para su aprobación.</li>
            <li>Ejecutar planes de acción derivados de auditorías e inspecciones.</li>
            <li>Mantener actualizado el sistema de indicadores de SST.</li>
        </ul>
    </div>

    <!-- 3. RESPONSABILIDADES DEL VIGÍA SST -->
    <div class="seccion">
        <div class="seccion-titulo">3. RESPONSABILIDADES DEL VIGÍA DE SEGURIDAD Y SALUD EN EL TRABAJO</div>
        <ul class="responsabilidades">
            <li>Proponer actividades para mejorar las condiciones de seguridad y salud en el trabajo.</li>
            <li>Participar en actividades de capacitación en SST y promover su cumplimiento.</li>
            <li>Colaborar con autoridades en inspecciones y auditorías.</li>
            <li>Vigilar el cumplimiento de las normas de SST en <?= $nombreCli ?>.</li>
            <li>Reportar condiciones de riesgo y sugerir medidas de control.</li>
            <li>Servir de enlace entre la administración y los contratistas en temas de SST.</li>
            <li>Apoyar la revisión de estadísticas de accidentalidad y enfermedades laborales.</li>
            <li>Mantener registros de actividades realizadas en SST.</li>
            <li>Coordinar acciones de respuesta ante emergencias.</li>
            <li>Cumplir con las demás funciones establecidas en la normativa de SST.</li>
        </ul>
    </div>

    <!-- 4. RESPONSABILIDADES DE LOS TRABAJADORES Y CONTRATISTAS -->
    <div class="seccion">
        <div class="seccion-titulo">4. RESPONSABILIDADES DE LOS TRABAJADORES Y CONTRATISTAS</div>
        <ul class="responsabilidades">
            <li>Conocer, entender y aplicar la política de SST.</li>
            <li>Identificar y reportar peligros y riesgos en su entorno de trabajo.</li>
            <li>Utilizar correctamente los Elementos de Protección Personal (EPP).</li>
            <li>Asistir a capacitaciones y entrenamientos en SST.</li>
            <li>Cumplir con las normas de seguridad establecidas.</li>
            <li>Informar de inmediato cualquier accidente o incidente de trabajo.</li>
            <li>Mantener el orden y la limpieza en su área de trabajo.</li>
            <li>Aplicar los procedimientos establecidos en emergencias.</li>
            <li>Participar en la evaluación de peligros y riesgos en el lugar de trabajo.</li>
            <li>Colaborar en la implementación de medidas de prevención y control.</li>
        </ul>
    </div>

    <!-- 5. FIRMAS DE ACEPTACIÓN -->
    <div class="seccion">
        <div class="seccion-titulo">5. FIRMA DE ACEPTACIÓN DE RESPONSABILIDADES</div>
        <div class="seccion-contenido">
            <p>Con la firma de este documento, cada una de las partes acepta las responsabilidades establecidas en materia de Seguridad y Salud en el Trabajo dentro de <?= $nombreCli ?>.</p>
        </div>

        <?php if (!empty($asistentes)): ?>
        <table class="datos-general" style="margin-top:8px;">
            <thead>
                <tr style="background:#f0f0f0;">
                    <th style="width:5%; border:1px solid #999; padding:5px;">#</th>
                    <th style="width:35%; border:1px solid #999; padding:5px;">NOMBRE COMPLETO</th>
                    <th style="width:20%; border:1px solid #999; padding:5px;">DOCUMENTO</th>
                    <th style="width:40%; border:1px solid #999; padding:5px;">FIRMA</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($asistentes as $i => $a):
                    // Prioridad base64 (funciona universal en DOMPDF Windows + Linux).
                    $firmaSrc = '';
                    if (!empty($a['firma_base64'])) $firmaSrc = $a['firma_base64'];
                    elseif (!empty($a['firma_full_path'])) $firmaSrc = $a['firma_full_path'];
                ?>
                <tr>
                    <td style="border:1px solid #999; padding:4px; text-align:center;"><?= $i + 1 ?></td>
                    <td style="border:1px solid #999; padding:4px;"><?= esc($a['nombre_completo']) ?></td>
                    <td style="border:1px solid #999; padding:4px;"><?= esc($a['tipo_documento'] ?? '') ?> <?= esc($a['numero_documento'] ?? '') ?></td>
                    <td style="border:1px solid #999; padding:4px; text-align:center;">
                        <?php if ($firmaSrc): ?>
                            <img src="<?= $firmaSrc ?>" class="firma-img">
                        <?php else: ?>
                            <span class="pendiente-text">Sin firma</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p class="empty-text">Sin asistentes registrados.</p>
        <?php endif; ?>
    </div>

    <div class="pie-documento">
        <p>Documento generado por EnterpriseSST &middot; <?= date('d/m/Y H:i') ?> &middot; FT-SST-003 v001</p>
    </div>
</body>
</html>
