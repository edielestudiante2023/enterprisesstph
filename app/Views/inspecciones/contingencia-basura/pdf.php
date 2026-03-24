<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    @page { margin: 80px 50px 60px 60px; }
    body { font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif; font-size: 10px; color: #333; line-height: 1.5; padding: 0; margin: 0; }
    .header-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
    .header-table td { border: 2px solid #333; padding: 6px 8px; vertical-align: middle; }
    .header-table .logo-cell { width: 100px; text-align: center; }
    .header-table .logo-cell img { max-width: 90px; max-height: 55px; }
    .header-table .title-cell { text-align: center; font-weight: bold; font-size: 10px; }
    .header-table .code-cell { width: 120px; font-size: 9px; }
    .main-title { text-align: center; font-weight: bold; font-size: 13px; color: #1c2437; margin: 20px 0 5px; }
    .subtitle { text-align: center; font-weight: bold; font-size: 11px; color: #333; margin-bottom: 15px; }
    .section-title { font-weight: bold; font-size: 11px; color: #1c2437; margin-top: 18px; margin-bottom: 6px; border-bottom: 1px solid #1c2437; padding-bottom: 3px; }
    .subsection-title { font-weight: bold; font-size: 10px; color: #1c2437; margin-top: 12px; margin-bottom: 4px; }
    .data-table { width: 100%; border-collapse: collapse; margin: 8px 0 12px; font-size: 9px; }
    .data-table th { background: #1c2437; color: white; padding: 5px 7px; text-align: center; font-weight: bold; border: 1px solid #1c2437; }
    .data-table td { border: 1px solid #aaa; padding: 4px 6px; vertical-align: top; }
    .data-table tr:nth-child(even) td { background: #f5f5f5; }
    .alert-box { background: #d4edda; border: 1.5px solid #155724; border-radius: 4px; padding: 7px 10px; margin: 8px 0; font-size: 9.5px; }
    .warning-box { background: #fff3cd; border: 1.5px solid #e6a800; border-radius: 4px; padding: 7px 10px; margin: 8px 0; font-size: 9.5px; }
    .step-box { border: 1px solid #1c2437; border-radius: 3px; padding: 6px 10px; margin: 5px 0; font-size: 9.5px; }
    .step-num { font-weight: bold; color: #1c2437; }
    p { margin: 5px 0 8px; font-size: 10px; }
    ul, ol { margin: 4px 0 8px 18px; font-size: 10px; }
    li { margin-bottom: 2px; }
    .nota { font-size: 8.5px; color: #555; font-style: italic; margin: 4px 0; }
    .firma-table { width: 100%; border-collapse: collapse; margin-top: 30px; }
    .firma-table td { border: 1px solid #999; padding: 8px; text-align: center; width: 33%; font-size: 9px; }
    .firma-cell-top { border-bottom: none; height: 40px; }
    .firma-cell-bot { border-top: 1px solid #666; }
</style>
</head>
<body>

<!-- ENCABEZADO -->
<table class="header-table">
    <tr>
        <td class="logo-cell">
            <?php if (!empty($logoBase64)): ?>
            <img src="<?= $logoBase64 ?>" alt="Logo">
            <?php else: ?>
            <span style="font-size:8px; color:#999;">LOGO</span>
            <?php endif; ?>
        </td>
        <td class="title-cell">
            PLAN DE CONTINGENCIA SI LA RECOLECCIÓN DE BASURA NO PASA<br>
            <span style="font-weight:normal;"><?= esc($cliente['nombre_cliente'] ?? '') ?></span>
        </td>
        <td class="code-cell">
            <strong>Código:</strong> FT-SST-235<br>
            <strong>Versión:</strong> 001<br>
            <strong>Fecha:</strong> <?= !empty($inspeccion['fecha_programa']) ? date('d/m/Y', strtotime($inspeccion['fecha_programa'])) : date('d/m/Y') ?><br>
            <strong>Responsable:</strong> <?= esc($inspeccion['nombre_responsable'] ?? 'Administrador(a)') ?>
        </td>
    </tr>
</table>

<div class="main-title">PLAN DE CONTINGENCIA SI LA RECOLECCIÓN DE BASURA NO PASA</div>
<div class="subtitle">Propiedad Horizontal — Salud y Seguridad en el Trabajo</div>

<!-- 1. OBJETIVO -->
<div class="section-title">1. OBJETIVO</div>
<p>Establecer las medidas de respuesta ante la falta de recolección de residuos sólidos por parte del prestador del servicio de aseo, garantizando las condiciones sanitarias, la correcta gestión temporal de los residuos y la protección de la salud de los residentes, empleados y visitantes de <strong><?= esc($cliente['nombre_cliente'] ?? 'la copropiedad') ?></strong>.</p>

<!-- 2. ALCANCE -->
<div class="section-title">2. ALCANCE</div>
<p>Este plan aplica a todos los residuos sólidos generados en las unidades privadas y áreas comunes de la copropiedad, durante eventos de interrupción del servicio de recolección de residuos por parte de la empresa de aseo contratada o del operador del servicio público.</p>

<!-- 3. MARCO LEGAL -->
<div class="section-title">3. MARCO LEGAL</div>
<ul>
    <li><strong>Ley 142 de 1994</strong> — Régimen de Servicios Públicos Domiciliarios. Obligaciones del prestador del servicio de aseo.</li>
    <li><strong>Decreto 1077 de 2015</strong> — Decreto Único Reglamentario del Sector Vivienda, Ciudad y Territorio. Gestión integral de residuos sólidos.</li>
    <li><strong>Resolución 2184 de 2019</strong> — Código de colores para la separación de residuos sólidos en la fuente.</li>
    <li><strong>Ley 9 de 1979</strong> — Código Sanitario Nacional. Obligaciones en el manejo de residuos sólidos.</li>
    <li><strong>Decreto 1072 de 2015</strong> — SG-SST: gestión de condiciones de trabajo ante situaciones de emergencia sanitaria.</li>
    <li><strong>Ley 675 de 2001</strong> — Régimen de Propiedad Horizontal. Responsabilidad del administrador en el mantenimiento de condiciones sanitarias.</li>
</ul>

<!-- 4. INFORMACIÓN DEL PRESTADOR -->
<div class="section-title">4. INFORMACIÓN DEL PRESTADOR DEL SERVICIO DE ASEO</div>
<table style="width:100%; border:1.5px solid #1c2437; border-collapse:collapse; margin-bottom:8px;">
    <tr>
        <td style="background:#1c2437; color:white; padding:6px 10px; font-weight:bold; width:40%;">EMPRESA PRESTADORA DE ASEO</td>
        <td style="padding:7px 10px;"><?= esc($inspeccion['empresa_aseo'] ?? 'Por definir') ?></td>
    </tr>
    <tr>
        <td style="background:#f5f5f5; padding:6px 10px; font-weight:bold; border:1px solid #ccc;">HORARIO NORMAL DE RECOLECCIÓN</td>
        <td style="padding:7px 10px; border:1px solid #ccc;"><?= esc($inspeccion['horario_recoleccion_actual'] ?? 'Por definir') ?></td>
    </tr>
</table>

<!-- 5. CAUSAS DE INTERRUPCIÓN -->
<div class="section-title">5. CAUSAS POSIBLES DE INTERRUPCIÓN DEL SERVICIO DE RECOLECCIÓN</div>
<table class="data-table">
    <tr><th>CAUSA</th><th>DURACIÓN PROBABLE</th><th>NIVEL DE RIESGO SANITARIO</th></tr>
    <tr><td>Paro o huelga del operador de aseo</td><td>Días a semanas</td><td>Alto</td></tr>
    <tr><td>Falla mecánica del vehículo recolector</td><td>Horas a días</td><td>Medio</td></tr>
    <tr><td>Emergencia ambiental o desastre natural</td><td>Días a semanas</td><td>Muy alto</td></tr>
    <tr><td>Festivos o cierre de relleno sanitario</td><td>1-2 días</td><td>Bajo</td></tr>
    <tr><td>Acceso vial bloqueado (manifestaciones, obras)</td><td>Horas a días</td><td>Medio</td></tr>
    <tr><td>Suspensión por mora en el pago del servicio</td><td>Días</td><td>Alto</td></tr>
</table>

<!-- 6. PROTOCOLO DE ACTUACIÓN -->
<div class="section-title">6. PROTOCOLO DE ACTUACIÓN ANTE FALTA DE RECOLECCIÓN</div>

<div class="subsection-title">6.1 Primeras 24 horas</div>
<div class="step-box"><span class="step-num">PASO 1:</span> El recuperador o personal de aseo detecta que el vehículo recolector no realizó la ruta. Reporta inmediatamente a la administración.</div>
<div class="step-box"><span class="step-num">PASO 2:</span> La administración llama a la línea de atención de la empresa prestadora para conocer el motivo y la fecha estimada de reanudación del servicio.</div>
<div class="step-box"><span class="step-num">PASO 3:</span> Verificar que el cuarto de residuos tenga capacidad suficiente para almacenar los residuos de manera temporal con las condiciones mínimas sanitarias.</div>
<div class="step-box"><span class="step-num">PASO 4:</span> Comunicar a los residentes la situación e instruirlos para reducir la generación de residuos y mantener la separación en la fuente.</div>

<div class="subsection-title">6.2 Entre 24 y 72 horas</div>
<div class="step-box"><span class="step-num">PASO 5:</span> Si el servicio no se reanuda en 24 horas, contactar a la empresa para buscar un servicio de recolección especial o a terceros autorizados por la autoridad ambiental.</div>
<div class="step-box"><span class="step-num">PASO 6:</span> Intensificar la limpieza y desinfección del cuarto de residuos (mínimo 2 veces al día) para prevenir la proliferación de plagas y vectores.</div>
<div class="step-box"><span class="step-num">PASO 7:</span> Aplicar medidas de control de olores: aspersión de desodorizante industrial, sellado hermético de bolsas.</div>
<div class="step-box"><span class="step-num">PASO 8:</span> Reportar la situación a la Superintendencia de Servicios Públicos Domiciliarios mediante la radicación de una PQRS formal.</div>

<div class="subsection-title">6.3 Más de 72 horas</div>
<div class="step-box"><span class="step-num">PASO 9:</span> Notificar a la Secretaría de Salud Municipal y/o a la autoridad ambiental competente (DAGMA, CVC, CAR, etc. según el municipio) para solicitar apoyo en la gestión de los residuos acumulados.</div>
<div class="step-box"><span class="step-num">PASO 10:</span> Si es necesario, contratar un servicio de transporte especial de residuos con empresa autorizada para llevarlos directamente al sitio de disposición final autorizado.</div>
<div class="step-box"><span class="step-num">PASO 11:</span> Documentar toda la situación con registros fotográficos, comunicaciones y costos incurridos para gestionar el cobro ante la empresa prestadora o en acciones legales posteriores.</div>

<!-- 7. MANEJO DEL CUARTO DE RESIDUOS EN CONTINGENCIA -->
<div class="section-title">7. MANEJO DEL CUARTO DE RESIDUOS DURANTE LA CONTINGENCIA</div>
<table class="data-table">
    <tr><th>ACCIÓN</th><th>FRECUENCIA</th><th>RESPONSABLE</th></tr>
    <tr><td>Limpieza y desinfección del cuarto</td><td>Mínimo 2 veces al día</td><td>Recuperador / Personal de aseo</td></tr>
    <tr><td>Sellado hermético de bolsas de residuos</td><td>Permanente</td><td>Recuperador / Residentes</td></tr>
    <tr><td>Control de olores (desodorizante industrial)</td><td>2 veces al día</td><td>Recuperador / Personal de aseo</td></tr>
    <tr><td>Verificación de contenedores (tapas cerradas)</td><td>Cada 4 horas</td><td>Recuperador</td></tr>
    <tr><td>Control de acceso de plagas (trampas)</td><td>Diario</td><td>Recuperador / Mantenimiento</td></tr>
    <tr><td>Registro fotográfico del estado del cuarto</td><td>Diario</td><td>Administración</td></tr>
</table>

<!-- 8. INSTRUCCIONES PARA RESIDENTES -->
<div class="section-title">8. INSTRUCCIONES PARA RESIDENTES DURANTE LA CONTINGENCIA</div>
<ul>
    <li>Mantener la separación en la fuente según el Código de Colores (Resolución 2184 de 2019): verde (orgánicos), blanco (aprovechables), negro (no aprovechables), rojo (peligrosos).</li>
    <li>Compactar los residuos al máximo para reducir el volumen.</li>
    <li>Bajar los residuos únicamente en los horarios establecidos por la administración para la contingencia.</li>
    <li>No acumular residuos en zonas de paso, pasillos, escaleras o áreas comunes distintas al cuarto de residuos.</li>
    <li>Reducir la generación de residuos orgánicos durante la contingencia (evitar desperdicios de comida).</li>
    <li>Reportar cualquier signo de plaga o condición insalubre a la administración de inmediato.</li>
</ul>

<div class="warning-box">
    <strong>⚠ PROHIBIDO:</strong> Disponer residuos en vía pública, zonas verdes, parqueaderos u otras áreas del conjunto distintas al cuarto de residuos. Esta práctica genera multas económicas y riesgo sanitario para toda la comunidad.
</div>

<!-- 9. COMUNICACIÓN -->
<div class="section-title">9. COMUNICACIÓN Y ESCALAMIENTO</div>
<table class="data-table">
    <tr><th>ENTIDAD</th><th>CUÁNDO CONTACTAR</th><th>MEDIO</th></tr>
    <tr><td>Empresa prestadora de aseo</td><td>Desde el primer día sin recolección</td><td>Línea de atención — PQRS escrita</td></tr>
    <tr><td>Superintendencia Servicios Públicos</td><td>Si el servicio no se restablece en 24 h</td><td>Portal web — Radicado PQRS</td></tr>
    <tr><td>Secretaría de Salud Municipal</td><td>Si el acúmulo genera riesgo sanitario (&gt;72 h)</td><td>Visita o línea de emergencias</td></tr>
    <tr><td>Autoridad ambiental (DAGMA/CVC/CAR)</td><td>Si se requiere disposición especial</td><td>Solicitud formal</td></tr>
    <tr><td>Consejo de Administración</td><td>Para autorización de gastos extraordinarios</td><td>Convocatoria urgente</td></tr>
</table>

<!-- 10. RESPONSABLES -->
<div class="section-title">10. RESPONSABLES</div>
<table class="data-table">
    <tr><th>ROL</th><th>RESPONSABILIDAD</th></tr>
    <tr><td><strong>Administrador(a)</strong></td><td>Activar el plan, contactar a la empresa de aseo y autoridades, notificar a residentes, gestionar soluciones alternativas de recolección.</td></tr>
    <tr><td><strong>Consultor SST</strong></td><td>Asesorar el plan, verificar condiciones sanitarias del cuarto de residuos, recomendar medidas de control de plagas.</td></tr>
    <tr><td><strong>Recuperador / Personal de aseo</strong></td><td>Intensificar la limpieza y desinfección del cuarto, verificar estado de contenedores, reportar señales de plaga.</td></tr>
    <tr><td><strong>Consejo de Administración</strong></td><td>Aprobar recursos para contratación de servicios especiales de recolección o gestión alternativa de residuos.</td></tr>
    <tr><td><strong>Residentes</strong></td><td>Seguir instrucciones de la administración, mantener separación en la fuente, reducir generación de residuos.</td></tr>
</table>

<!-- 11. REGISTROS -->
<div class="section-title">11. REGISTROS Y DOCUMENTACIÓN</div>
<ul>
    <li>Registro de cada día sin servicio de recolección (fecha, comunicación con la empresa, respuesta obtenida).</li>
    <li>Radicado de PQRS ante la empresa prestadora y la Superintendencia de Servicios Públicos.</li>
    <li>Registro fotográfico del cuarto de residuos durante la contingencia.</li>
    <li>Circular de notificación a residentes (fechada y firmada).</li>
    <li>Factura de servicios especiales de recolección si aplica.</li>
    <li>Acta del Consejo de Administración si se aprobaron recursos extraordinarios.</li>
</ul>

<div class="alert-box">
    <strong>✓ BUENA PRÁCTICA:</strong> Mantener siempre actualizado el contacto del proveedor alternativo de recolección y realizar una simulación del plan al menos una vez al año, coordinada con el Consultor SST.
</div>

<!-- FIRMAS -->
<table class="firma-table">
    <tr>
        <td class="firma-cell-top"></td>
        <td class="firma-cell-top"></td>
        <td class="firma-cell-top"></td>
    </tr>
    <tr>
        <td class="firma-cell-bot">
            <strong>Administrador(a)</strong><br>
            <?= esc($inspeccion['nombre_responsable'] ?? '___________________') ?><br>
            <?= esc($cliente['nombre_cliente'] ?? '') ?>
        </td>
        <td class="firma-cell-bot">
            <strong>Consultor SST</strong><br>
            <?= esc($consultor['nombre_consultor'] ?? '___________________') ?>
        </td>
        <td class="firma-cell-bot">
            <strong>Representante Consejo</strong><br>
            ___________________<br>
            Consejo de Administración
        </td>
    </tr>
</table>

</body>
</html>
