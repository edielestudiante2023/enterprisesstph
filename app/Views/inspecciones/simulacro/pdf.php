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

        .foto-inline { max-width: 200px; max-height: 150px; border: 1px solid #ccc; }
        .page-break { page-break-before: always; }

        .score-box { text-align: center; font-size: 14px; font-weight: bold; padding: 4px; }
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
            <td class="header-code">Codigo: FT-SST-222<br>Version: 001</td>
        </tr>
        <tr>
            <td class="header-title">EVALUACION SIMULACRO DE EVACUACION</td>
            <td class="header-code">Fecha: <?= !empty($cliente['fecha_sgsst']) ? date('d/m/Y', strtotime($cliente['fecha_sgsst'])) : date('d/m/Y') ?></td>
        </tr>
    </table>

    <!-- ====== MARCO TEORICO ====== -->
    <div class="theory-title">1. OBJETIVO</div>
    <p class="content-text">Evaluar la capacidad de respuesta del personal de la copropiedad <?= esc($cliente['nombre_cliente'] ?? '') ?> ante un evento de emergencia simulado, identificando fortalezas, debilidades y oportunidades de mejora en los procedimientos de evacuacion, comunicacion y liderazgo de la brigada de emergencias.</p>

    <div class="theory-title">2. ALCANCE</div>
    <p class="content-text">Este formato aplica a todos los simulacros de evacuacion realizados en las copropiedades atendidas por CYCLOID SAS, de acuerdo con la Ley 1523 de 2012 (Gestion del Riesgo de Desastres) y la Resolucion 0312 de 2019 (Estandares Minimos del SG-SST). Cubre desde la preparacion previa hasta el cierre del ejercicio, incluyendo el registro de tiempos, conteo de evacuados y evaluacion cuantitativa y cualitativa.</p>

    <div class="theory-title">3. DEFINICIONES</div>
    <p class="content-text"><strong>Simulacro:</strong> Ejercicio practico que replica una situacion de emergencia con el fin de evaluar y mejorar los planes de respuesta.<br>
    <strong>Evacuacion:</strong> Proceso organizado de desplazamiento de personas desde una zona de riesgo hacia un punto seguro.<br>
    <strong>Brigadista:</strong> Persona capacitada para liderar y coordinar las acciones de respuesta ante emergencias.<br>
    <strong>Punto de encuentro:</strong> Lugar seguro predeterminado donde se concentra el personal evacuado para realizar el conteo y verificacion.</p>

    <div class="theory-title">4. METODOLOGIA</div>
    <p class="content-text">El simulacro se desarrolla en las siguientes fases: (1) Planeacion y alistamiento de recursos, (2) Activacion de la alarma y asignacion de roles, (3) Evacuacion hacia puntos de encuentro, (4) Conteo y verificacion de personal, (5) Cierre y retroalimentacion. Durante el ejercicio se registran los tiempos de cada fase mediante cronometro digital, se documenta el evento con fotografias y se evaluan cinco criterios clave en una escala de 1 a 10.</p>

    <!-- ====== RESULTADOS ====== -->
    <div class="page-break"></div>

    <table class="header-table">
        <tr>
            <td class="header-logo" rowspan="2">
                <?php if (!empty($logoBase64)): ?>
                    <img src="<?= $logoBase64 ?>">
                <?php else: ?>
                    <strong style="font-size:7px;"><?= esc($cliente['nombre_cliente'] ?? '') ?></strong>
                <?php endif; ?>
            </td>
            <td class="header-title">EVALUACION SIMULACRO DE EVACUACION - RESULTADOS</td>
            <td class="header-code">Codigo: FT-SST-222<br>Version: 001</td>
        </tr>
        <tr>
            <td class="header-title"><?= esc($cliente['nombre_cliente'] ?? '') ?></td>
            <td class="header-code">Pagina 2</td>
        </tr>
    </table>

    <!-- Seccion 1: Identificacion -->
    <div class="section-title">1. IDENTIFICACION</div>
    <table class="info-table">
        <tr><td class="info-label">Copropiedad</td><td><?= esc($cliente['nombre_cliente'] ?? 'N/A') ?></td></tr>
        <tr><td class="info-label">NIT</td><td><?= esc($cliente['nit_cliente'] ?? 'N/A') ?></td></tr>
        <tr><td class="info-label">Direccion</td><td><?= esc($eval['direccion'] ?? 'N/A') ?></td></tr>
        <tr><td class="info-label">Fecha del simulacro</td><td><?= !empty($eval['fecha']) ? date('d/m/Y', strtotime($eval['fecha'])) : 'N/A' ?></td></tr>
        <tr><td class="info-label">Consultor SST</td><td><?= esc($consultorNombre ?: 'N/A') ?></td></tr>
    </table>

    <!-- Seccion 2: Info General -->
    <div class="section-title">2. INFORMACION GENERAL DEL SIMULACRO</div>
    <table class="info-table">
        <tr><td class="info-label">Evento simulado</td><td><?= esc($eval['evento_simulado'] ?? 'N/A') ?></td></tr>
        <tr><td class="info-label">Alcance del simulacro</td><td><?= esc($eval['alcance_simulacro'] ?? 'N/A') ?></td></tr>
        <tr><td class="info-label">Tipo de evacuacion</td><td><?= esc($eval['tipo_evacuacion'] ?? 'N/A') ?></td></tr>
        <tr><td class="info-label">Personal que no evacua</td><td><?= esc($eval['personal_no_evacua'] ?? 'N/A') ?></td></tr>
        <tr><td class="info-label">Tipo de alarma</td><td><?= esc($eval['tipo_alarma'] ?? 'N/A') ?></td></tr>
        <tr><td class="info-label">Puntos de encuentro</td><td><?= esc($eval['puntos_encuentro'] ?? 'N/A') ?></td></tr>
        <tr><td class="info-label">Recurso humano</td><td><?= esc($eval['recurso_humano'] ?? 'N/A') ?></td></tr>
        <tr><td class="info-label">Equipos de emergencia</td><td><?= esc($eval['equipos_emergencia'] ?? 'N/A') ?></td></tr>
    </table>

    <!-- Seccion 3: Brigadista -->
    <div class="section-title">3. BRIGADISTA LIDER</div>
    <table class="info-table">
        <tr><td class="info-label">Nombre</td><td><?= esc($eval['nombre_brigadista_lider'] ?? 'N/A') ?></td></tr>
        <tr><td class="info-label">Email</td><td><?= esc($eval['email_brigadista_lider'] ?? 'N/A') ?></td></tr>
        <tr><td class="info-label">WhatsApp</td><td><?= esc($eval['whatsapp_brigadista_lider'] ?? 'N/A') ?></td></tr>
        <tr><td class="info-label">Distintivos</td><td><?= esc($eval['distintivos_brigadistas'] ?? 'N/A') ?></td></tr>
    </table>

    <!-- Seccion 4: Cronograma -->
    <div class="section-title">4. CRONOGRAMA DEL SIMULACRO</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:60%;">Fase / Actividad</th>
                <th style="width:40%;">Hora</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $cronoPasos = [
                'hora_inicio' => 'Hora de Inicio',
                'alistamiento_recursos' => 'Alistamiento de Recursos',
                'asumir_roles' => 'Asumir roles',
                'suena_alarma' => 'Suena alarma',
                'distribucion_roles' => 'Distribucion de roles',
                'llegada_punto_encuentro' => 'Llegada punto de encuentro',
                'agrupacion_por_afinidad' => 'Agrupacion por afinidad',
                'conteo_personal' => 'Conteo de personal',
                'agradecimiento_y_cierre' => 'Agradecimiento y cierre',
            ];
            foreach ($cronoPasos as $key => $label):
                $valor = $eval[$key] ?? null;
            ?>
            <tr>
                <td style="text-align:left; padding-left:8px;"><?= esc($label) ?></td>
                <td style="font-weight:bold;"><?= $valor ? date('H:i:s', strtotime($valor)) : '--:--:--' ?></td>
            </tr>
            <?php endforeach; ?>
            <tr style="background:#e8f4fd;">
                <td style="text-align:left; padding-left:8px; font-weight:bold;">TIEMPO TOTAL</td>
                <td style="font-weight:bold; font-size:10px;"><?= esc($eval['tiempo_total'] ?? '--:--:--') ?></td>
            </tr>
        </tbody>
    </table>

    <!-- Seccion 5: Conteo -->
    <div class="section-title">5. CONTEO DE EVACUADOS</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Hombres (18-59)</th>
                <th>Mujeres (18-59)</th>
                <th>Ninos (&lt;18)</th>
                <th>Adultos Mayores (60+)</th>
                <th>Discapacidad</th>
                <th>Mascotas</th>
                <th style="background:#d4edda; font-weight:bold;">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?= (int)($eval['hombre'] ?? 0) ?></td>
                <td><?= (int)($eval['mujer'] ?? 0) ?></td>
                <td><?= (int)($eval['ninos'] ?? 0) ?></td>
                <td><?= (int)($eval['adultos_mayores'] ?? 0) ?></td>
                <td><?= (int)($eval['discapacidad'] ?? 0) ?></td>
                <td><?= (int)($eval['mascotas'] ?? 0) ?></td>
                <td style="font-weight:bold; font-size:10px;"><?= (int)($eval['total'] ?? 0) ?></td>
            </tr>
        </tbody>
    </table>

    <!-- Seccion 6: Evaluacion -->
    <div class="section-title">6. EVALUACION CUANTITATIVA</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Alarma efectiva</th>
                <th>Orden evacuacion</th>
                <th>Liderazgo brigadistas</th>
                <th>Organizacion punto encuentro</th>
                <th>Participacion general</th>
                <th style="background:#d4edda; font-weight:bold;">PROMEDIO</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?= ($eval['alarma_efectiva'] ?? '-') ?></td>
                <td><?= ($eval['orden_evacuacion'] ?? '-') ?></td>
                <td><?= ($eval['liderazgo_brigadistas'] ?? '-') ?></td>
                <td><?= ($eval['organizacion_punto_encuentro'] ?? '-') ?></td>
                <td><?= ($eval['participacion_general'] ?? '-') ?></td>
                <td style="font-weight:bold; font-size:10px;"><?= esc($eval['evaluacion_cuantitativa'] ?? 'N/A') ?></td>
            </tr>
        </tbody>
    </table>

    <?php if (!empty($eval['evaluacion_cualitativa'])): ?>
    <div style="margin-top:4px;">
        <strong style="font-size:8px;">Evaluacion cualitativa:</strong>
        <p class="content-text"><?= esc($eval['evaluacion_cualitativa']) ?></p>
    </div>
    <?php endif; ?>

    <!-- Seccion 7: Observaciones -->
    <?php if (!empty($eval['observaciones'])): ?>
    <div class="section-title">7. OBSERVACIONES</div>
    <p class="content-text"><?= esc($eval['observaciones']) ?></p>
    <?php endif; ?>

    <!-- Seccion 8: Evidencias fotograficas -->
    <?php if (!empty($fotosBase64['imagen_1']) || !empty($fotosBase64['imagen_2'])): ?>
    <div class="section-title"><?= !empty($eval['observaciones']) ? '8' : '7' ?>. EVIDENCIAS FOTOGRAFICAS</div>
    <table style="width:100%; border-collapse:collapse;">
        <tr>
            <?php if (!empty($fotosBase64['imagen_1'])): ?>
            <td style="width:50%; text-align:center; padding:4px;">
                <img src="<?= $fotosBase64['imagen_1'] ?>" class="foto-inline">
                <br><small style="font-size:7px;">Foto 1</small>
            </td>
            <?php endif; ?>
            <?php if (!empty($fotosBase64['imagen_2'])): ?>
            <td style="width:50%; text-align:center; padding:4px;">
                <img src="<?= $fotosBase64['imagen_2'] ?>" class="foto-inline">
                <br><small style="font-size:7px;">Foto 2</small>
            </td>
            <?php endif; ?>
        </tr>
    </table>
    <?php endif; ?>

    <!-- Pie -->
    <div style="text-align:center; margin-top:15px; font-size:7px; color:#888; border-top:1px solid #ccc; padding-top:4px;">
        Documento generado automaticamente por CYCLOID SAS - Sistema de Gestion SST
        | <?= date('d/m/Y H:i') ?>
    </div>

</body>
</html>
