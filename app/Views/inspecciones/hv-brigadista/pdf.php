<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 100px 60px 70px 70px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9px;
            color: #333;
            line-height: 1.3;
            padding: 10px 15px;
        }

        .header-table { width: 100%; border-collapse: collapse; border: 1.5px solid #333; margin-bottom: 8px; }
        .header-table td { border: 1px solid #333; padding: 4px 6px; vertical-align: middle; }
        .header-logo { width: 100px; text-align: center; font-size: 8px; }
        .header-logo img { max-width: 85px; max-height: 50px; }
        .header-title { text-align: center; font-weight: bold; font-size: 9px; }
        .header-code { width: 120px; font-size: 8px; }

        .main-title { text-align: center; font-size: 11px; font-weight: bold; margin: 8px 0 6px; color: #1c2437; }

        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; border: 1px solid #ccc; }
        .info-table td { padding: 3px 6px; font-size: 9px; border: 1px solid #ccc; }
        .info-label { font-weight: bold; color: #444; width: 160px; background: #f7f7f7; }

        .section-title { background: #1c2437; color: white; padding: 3px 8px; font-weight: bold; font-size: 9px; margin: 8px 0 4px; }

        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .data-table th { background: #e8e8e8; border: 1px solid #aaa; padding: 3px 4px; font-size: 8px; text-align: center; }
        .data-table td { border: 1px solid #ccc; padding: 2px 4px; font-size: 8px; text-align: center; vertical-align: middle; }

        .content-text { font-size: 8px; line-height: 1.4; margin-bottom: 4px; text-align: justify; }
        .theory-title { font-weight: bold; font-size: 9px; margin: 6px 0 3px; color: #1c2437; }

        .foto-inline { max-width: 120px; max-height: 150px; border: 1px solid #ccc; }
        .firma-inline { max-width: 200px; max-height: 80px; border: 1px solid #ccc; }
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
            <td class="header-code">Codigo: FT-SST-221<br>Version: 001</td>
        </tr>
        <tr>
            <td class="header-title">HOJA DE VIDA BRIGADISTA</td>
            <td class="header-code">Fecha: <?= !empty($cliente['fecha_sgsst']) ? date('d/m/Y', strtotime($cliente['fecha_sgsst'])) : date('d/m/Y') ?></td>
        </tr>
    </table>

    <!-- ====== MARCO TEORICO ====== -->
    <div class="theory-title">1. OBJETIVO</div>
    <p class="content-text">Registrar y mantener actualizada la informacion personal, de salud y de formacion de cada brigadista de la copropiedad <?= esc($cliente['nombre_cliente'] ?? '') ?>, con el fin de garantizar que el personal que integra la brigada de emergencias cumple con las condiciones fisicas, medicas y de capacitacion necesarias para responder de manera efectiva ante situaciones de emergencia.</p>

    <div class="theory-title">2. ALCANCE</div>
    <p class="content-text">Este formato aplica a todos los miembros de las brigadas de emergencia de las copropiedades atendidas por CYCLOID SAS, de acuerdo con la Resolucion 0312 de 2019 (Estandares Minimos del SG-SST) y el Decreto 1072 de 2015 (Decreto Unico Reglamentario del Sector Trabajo). Cubre el registro de datos personales, antecedentes de salud, formacion academica relacionada con emergencias y la aptitud fisica del brigadista.</p>

    <div class="theory-title">3. DEFINICIONES</div>
    <p class="content-text"><strong>Brigadista:</strong> Persona voluntaria, capacitada y entrenada para prevenir, controlar y reaccionar en situaciones de emergencia.<br>
    <strong>Brigada de Emergencias:</strong> Grupo organizado de personas capacitadas para actuar antes, durante y despues de una emergencia dentro de la copropiedad.<br>
    <strong>Hoja de Vida:</strong> Documento que consolida la informacion personal, medica y de formacion del brigadista, permitiendo evaluar su idoneidad para las funciones asignadas.<br>
    <strong>Cuestionario PAR-Q:</strong> Cuestionario de Aptitud para la Actividad Fisica (Physical Activity Readiness Questionnaire) utilizado para identificar contraindicaciones medicas previas a la participacion en actividades fisicas.</p>

    <div class="theory-title">4. METODOLOGIA</div>
    <p class="content-text">El proceso de registro comprende: (1) Identificacion de la copropiedad y del brigadista, (2) Recoleccion de datos personales incluyendo registro fotografico, (3) Documentacion de estudios relacionados con la respuesta ante emergencias, (4) Evaluacion del estado de salud mediante cuestionario PAR-Q de 14 preguntas, (5) Registro de restricciones medicas y habitos de actividad fisica, (6) Firma del brigadista como constancia de veracidad de la informacion.</p>

    <!-- ====== RESULTADOS ====== -->
<table class="header-table">
        <tr>
            <td class="header-logo" rowspan="2">
                <?php if (!empty($logoBase64)): ?>
                    <img src="<?= $logoBase64 ?>">
                <?php else: ?>
                    <strong style="font-size:7px;"><?= esc($cliente['nombre_cliente'] ?? '') ?></strong>
                <?php endif; ?>
            </td>
            <td class="header-title">HOJA DE VIDA BRIGADISTA - DATOS</td>
            <td class="header-code">Codigo: FT-SST-221<br>Version: 001</td>
        </tr>
        <tr>
            <td class="header-title"><?= esc($cliente['nombre_cliente'] ?? '') ?></td>
            <td class="header-code">Pagina 2</td>
        </tr>
    </table>

    <!-- Seccion 5: Datos personales + foto -->
    <div class="section-title">5. DATOS PERSONALES</div>
    <table class="info-table">
        <tr>
            <td class="info-label">Copropiedad</td>
            <td><?= esc($cliente['nombre_cliente'] ?? 'N/A') ?></td>
            <td rowspan="7" style="width:130px; text-align:center; vertical-align:middle;">
                <?php if (!empty($fotoBase64)): ?>
                    <img src="<?= $fotoBase64 ?>" class="foto-inline">
                <?php else: ?>
                    <span style="font-size:7px; color:#999;">Sin foto</span>
                <?php endif; ?>
            </td>
        </tr>
        <tr><td class="info-label">Nombre completo</td><td><?= esc($hv['nombre_completo'] ?? 'N/A') ?></td></tr>
        <tr><td class="info-label">Documento identidad</td><td><?= esc($hv['documento_identidad'] ?? 'N/A') ?></td></tr>
        <tr><td class="info-label">Fecha nacimiento</td><td><?= !empty($hv['f_nacimiento']) ? date('d/m/Y', strtotime($hv['f_nacimiento'])) : 'N/A' ?></td></tr>
        <tr><td class="info-label">Edad</td><td><?= esc($hv['edad'] ?? 'N/A') ?> anios</td></tr>
        <tr><td class="info-label">Email</td><td><?= esc($hv['email'] ?? 'N/A') ?></td></tr>
        <tr><td class="info-label">Telefono</td><td><?= esc($hv['telefono'] ?? 'N/A') ?></td></tr>
    </table>
    <table class="info-table">
        <tr><td class="info-label">Direccion</td><td><?= esc($hv['direccion_residencia'] ?? 'N/A') ?></td></tr>
        <tr><td class="info-label">EPS</td><td><?= esc($hv['eps'] ?? 'N/A') ?></td></tr>
        <tr>
            <td class="info-label">RH</td><td style="width:80px;"><?= esc($hv['rh'] ?? 'N/A') ?></td>
        </tr>
        <tr>
            <td class="info-label">Peso (kg)</td><td><?= esc($hv['peso'] ?? 'N/A') ?></td>
        </tr>
        <tr>
            <td class="info-label">Estatura (cm)</td><td><?= esc($hv['estatura'] ?? 'N/A') ?></td>
        </tr>
        <tr><td class="info-label">Consultor SST</td><td><?= esc($consultorNombre ?: 'N/A') ?></td></tr>
    </table>

    <!-- Seccion 6: Estudios -->
    <div class="section-title">6. ESTUDIOS RELACIONADOS CON RESPUESTA ANTE EMERGENCIAS</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:10%;">#</th>
                <th style="width:40%;">Estudio</th>
                <th style="width:35%;">Institucion</th>
                <th style="width:15%;">Anio</th>
            </tr>
        </thead>
        <tbody>
            <?php for ($i = 1; $i <= 3; $i++):
                $est = $hv["estudios_$i"] ?? '';
                $lugar = $hv["lugar_estudio_$i"] ?? '';
                $anio = $hv["anio_estudio_$i"] ?? '';
            ?>
            <tr>
                <td><?= $i ?></td>
                <td style="text-align:left; padding-left:8px;"><?= esc($est ?: '-') ?></td>
                <td style="text-align:left; padding-left:8px;"><?= esc($lugar ?: '-') ?></td>
                <td><?= esc($anio ?: '-') ?></td>
            </tr>
            <?php endfor; ?>
        </tbody>
    </table>

    <!-- Seccion 7: Condiciones de salud -->
    <div class="section-title">7. CONDICIONES DE SALUD</div>
    <table class="info-table">
        <tr><td class="info-label">Enfermedades importantes</td><td><?= esc($hv['enfermedades_importantes'] ?? 'N/A') ?></td></tr>
        <tr><td class="info-label">Medicamentos</td><td><?= esc($hv['medicamentos'] ?? 'N/A') ?></td></tr>
    </table>

    <!-- ====== PAGINA 3 ====== -->
<table class="header-table">
        <tr>
            <td class="header-logo" rowspan="2">
                <?php if (!empty($logoBase64)): ?>
                    <img src="<?= $logoBase64 ?>">
                <?php else: ?>
                    <strong style="font-size:7px;"><?= esc($cliente['nombre_cliente'] ?? '') ?></strong>
                <?php endif; ?>
            </td>
            <td class="header-title">HOJA DE VIDA BRIGADISTA - CUESTIONARIO</td>
            <td class="header-code">Codigo: FT-SST-221<br>Version: 001</td>
        </tr>
        <tr>
            <td class="header-title"><?= esc($cliente['nombre_cliente'] ?? '') ?></td>
            <td class="header-code">Pagina 3</td>
        </tr>
    </table>

    <!-- Seccion 8: Cuestionario medico -->
    <div class="section-title">8. CUESTIONARIO MEDICO (PAR-Q)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:8%;">#</th>
                <th style="width:77%; text-align:left;">Pregunta</th>
                <th style="width:15%;">Respuesta</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $preguntasPdf = [
                'cardiaca'         => 'Alguna vez su doctor le ha dicho que tenga alguna enfermedad cardiaca y le ha recomendado que solo practique ejercicio medicamente prescrito?',
                'pechoactividad'   => 'Siente dolor en el pecho cuando hace alguna actividad fisica?',
                'dolorpecho'       => 'En el mes pasado ha tenido dolor en el pecho en reposo?',
                'conciencia'       => 'Pierde usted el equilibrio por mareo o ha perdido la conciencia alguna vez?',
                'huesos'           => 'Tiene usted algun problema de los huesos o articulaciones que pudieran empeorar por un cambio en la actividad fisica?',
                'medicamentos_bool'=> 'Esta su medico prescribiendo actualmente medicamentos para la presion arterial, diabetes o alguna condicion cardiaca?',
                'actividadfisica'  => 'Conoce alguna otra razon por la cual usted no pueda realizar actividad fisica?',
                'convulsiones'     => 'Ha sufrido o sufre de convulsiones o epilepsia?',
                'vertigo'          => 'Sufre o ha sufrido de vertigo?',
                'oidos'            => 'Ha sufrido de enfermedades en los oidos?',
                'lugarescerrados'  => 'Ha sufrido de miedo a los lugares cerrados (ascensores, cuartos pequenos)?',
                'miedoalturas'     => 'Ha sufrido de miedo a las alturas (aviones, puentes peatonales, terrazas)?',
                'haceejercicio'    => 'Hace en la semana ejercicio?',
                'miedo_ver_sangre' => 'Sufre de miedo al ver sangre?',
            ];
            $n = 1;
            foreach ($preguntasPdf as $key => $texto):
                $val = $hv[$key] ?? '-';
            ?>
            <tr>
                <td><?= $n ?></td>
                <td style="text-align:left; padding-left:8px; font-size:8px;"><?= esc($texto) ?></td>
                <td style="font-weight:bold; <?= $val === 'SI' ? 'color:#c0392b;' : '' ?>"><?= esc($val) ?></td>
            </tr>
            <?php $n++; endforeach; ?>
        </tbody>
    </table>

    <!-- Seccion 9: Restricciones y actividad -->
    <div class="section-title">9. RESTRICCIONES MEDICAS Y ACTIVIDAD FISICA</div>
    <table class="info-table">
        <tr><td class="info-label">Restricciones medicas</td><td><?= esc($hv['restricciones_medicas'] ?? 'N/A') ?></td></tr>
        <tr><td class="info-label">Deportes y horas/semana</td><td><?= esc($hv['deporte_semana'] ?? 'N/A') ?></td></tr>
    </table>

    <!-- Seccion 10: Firma -->
    <div class="section-title">10. FIRMA DEL BRIGADISTA</div>
    <table style="width:100%; border-collapse:collapse; margin-top:6px;">
        <tr>
            <td style="text-align:center; padding:10px;">
                <?php if (!empty($firmaBase64)): ?>
                    <img src="<?= $firmaBase64 ?>" class="firma-inline">
                    <br><span style="font-size:8px; color:#666;"><?= esc($hv['nombre_completo'] ?? '') ?></span>
                    <br><span style="font-size:7px; color:#999;">CC <?= esc($hv['documento_identidad'] ?? '') ?></span>
                <?php else: ?>
                    <span style="font-size:8px; color:#999;">Sin firma</span>
                <?php endif; ?>
            </td>
        </tr>
    </table>

    <!-- Pie -->
    <div style="text-align:center; margin-top:15px; font-size:7px; color:#888; border-top:1px solid #ccc; padding-top:4px;">
        Documento generado automaticamente por CYCLOID SAS - Sistema de Gestion SST
        | <?= date('d/m/Y H:i') ?>
    </div>

</body>
</html>
