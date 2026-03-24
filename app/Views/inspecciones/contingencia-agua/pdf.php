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
    .alert-box { background: #d1ecf1; border: 1.5px solid #0c5460; border-radius: 4px; padding: 7px 10px; margin: 8px 0; font-size: 9.5px; }
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
            PLAN DE CONTINGENCIA SIN SUMINISTRO DE AGUA<br>
            <span style="font-weight:normal;"><?= esc($cliente['nombre_cliente'] ?? '') ?></span>
        </td>
        <td class="code-cell">
            <strong>Código:</strong> FT-SST-234<br>
            <strong>Versión:</strong> 001<br>
            <strong>Fecha:</strong> <?= !empty($inspeccion['fecha_programa']) ? date('d/m/Y', strtotime($inspeccion['fecha_programa'])) : date('d/m/Y') ?><br>
            <strong>Responsable:</strong> <?= esc($inspeccion['nombre_responsable'] ?? 'Administrador(a)') ?>
        </td>
    </tr>
</table>

<div class="main-title">PLAN DE CONTINGENCIA SIN SUMINISTRO DE AGUA</div>
<div class="subtitle">Propiedad Horizontal — Salud y Seguridad en el Trabajo</div>

<!-- 1. OBJETIVO -->
<div class="section-title">1. OBJETIVO</div>
<p>Establecer las acciones de respuesta inmediata y las alternativas de suministro ante una interrupción del servicio de agua potable en <strong><?= esc($cliente['nombre_cliente'] ?? 'la copropiedad') ?></strong>, garantizando el bienestar de los residentes, la continuidad de las actividades esenciales de higiene y la preservación de las condiciones sanitarias del conjunto.</p>

<!-- 2. ALCANCE -->
<div class="section-title">2. ALCANCE</div>
<p>Este plan aplica a todos los residentes, empleados y visitantes de <strong><?= esc($cliente['nombre_cliente'] ?? 'la copropiedad') ?></strong> durante eventos de interrupción del suministro de agua, ya sea por cortes programados de la empresa prestadora del servicio, daños en la red interna o externa, o emergencias ambientales.</p>

<!-- 3. MARCO LEGAL -->
<div class="section-title">3. MARCO LEGAL</div>
<ul>
    <li><strong>Ley 142 de 1994</strong> — Régimen de Servicios Públicos Domiciliarios. Obligaciones de las empresas prestadoras ante interrupciones.</li>
    <li><strong>Decreto 1076 de 2015</strong> — Decreto Único Reglamentario del Sector Ambiente y Desarrollo Sostenible.</li>
    <li><strong>Resolución 2115 de 2007</strong> — Características, instrumentos básicos y frecuencias del sistema de control y vigilancia para la calidad del agua para consumo humano.</li>
    <li><strong>Decreto 1072 de 2015</strong> — SG-SST: medidas de emergencia ante situaciones que afecten condiciones de trabajo.</li>
    <li><strong>Ley 675 de 2001</strong> — Régimen de Propiedad Horizontal. Responsabilidad del administrador en la gestión de servicios esenciales.</li>
    <li><strong>NTC-ISO 22301</strong> — Sistemas de gestión de continuidad del negocio (referencia para planes de contingencia).</li>
</ul>

<!-- 4. CAUSAS DE INTERRUPCIÓN -->
<div class="section-title">4. CAUSAS POSIBLES DE INTERRUPCIÓN DEL SERVICIO</div>
<table class="data-table">
    <tr><th>CAUSA</th><th>ORIGEN</th><th>DURACIÓN PROBABLE</th></tr>
    <tr><td>Corte programado por la empresa prestadora</td><td>Externo</td><td>Horas (comunicado previo)</td></tr>
    <tr><td>Daño en red de distribución externa</td><td>Externo</td><td>Horas a días</td></tr>
    <tr><td>Daño en tubería interna del conjunto</td><td>Interno</td><td>Horas a días</td></tr>
    <tr><td>Contaminación de la fuente de agua</td><td>Externo/Emergencia</td><td>Días a semanas</td></tr>
    <tr><td>Falla en sistema de bombeo del conjunto</td><td>Interno</td><td>Horas</td></tr>
    <tr><td>Racionamiento por sequía o emergencia ambiental</td><td>Externo/Ambiental</td><td>Días a semanas</td></tr>
</table>

<!-- 5. CAPACIDAD DE RESERVA -->
<div class="section-title">5. CAPACIDAD DE RESERVA DEL CONJUNTO</div>
<?php if (!empty($inspeccion['capacidad_reserva'])): ?>
<table style="width:100%; border:1.5px solid #1c2437; border-collapse:collapse; margin-bottom:8px;">
    <tr>
        <td style="background:#1c2437; color:white; padding:6px 10px; font-weight:bold; width:40%;">CAPACIDAD DE ALMACENAMIENTO</td>
        <td style="padding:7px 10px;"><?= esc($inspeccion['capacidad_reserva']) ?></td>
    </tr>
</table>
<?php else: ?>
<p class="nota">Capacidad de reserva: por verificar — documentar tanques y volumen total antes de activar el plan.</p>
<?php endif; ?>
<p>La copropiedad debe mantener sus tanques de almacenamiento en condiciones óptimas de limpieza y desinfección, revisados mínimo cada 6 meses, conforme a la Resolución 2115 de 2007.</p>

<!-- 6. PROVEEDOR DE AGUA ALTERNATIVA -->
<div class="section-title">6. PROVEEDOR DE AGUA ALTERNATIVA (CARROTANQUE)</div>
<?php if (!empty($inspeccion['empresa_carrotanque'])): ?>
<table style="width:100%; border:1.5px solid #1c2437; border-collapse:collapse; margin-bottom:8px;">
    <tr>
        <td style="background:#1c2437; color:white; padding:6px 10px; font-weight:bold; width:40%;">PROVEEDOR CARROTANQUE</td>
        <td style="padding:7px 10px;"><?= nl2br(esc($inspeccion['empresa_carrotanque'])) ?></td>
    </tr>
</table>
<?php else: ?>
<p class="nota">Proveedor de carrotanque: por definir — identificar antes de activar el plan.</p>
<?php endif; ?>
<p>El proveedor de agua por carrotanque debe contar con:</p>
<ul>
    <li>Certificado de calidad del agua suministrada (análisis fisicoquímico y microbiológico vigente).</li>
    <li>Registro sanitario del vehículo cisterna ante la Secretaría de Salud.</li>
    <li>Capacidad de suministro suficiente para cubrir la demanda del conjunto.</li>
</ul>

<!-- 7. PROTOCOLO DE ACTUACIÓN -->
<div class="section-title">7. PROTOCOLO DE ACTUACIÓN ANTE CORTE DEL SERVICIO</div>

<div class="subsection-title">7.1 Corte programado (con aviso previo)</div>
<div class="step-box"><span class="step-num">PASO 1:</span> Al recibir notificación de corte, la administración verifica el nivel de los tanques de almacenamiento y la duración estimada del corte.</div>
<div class="step-box"><span class="step-num">PASO 2:</span> Si los tanques tienen capacidad suficiente para la duración del corte, comunicar a los residentes recomendando uso racional del agua.</div>
<div class="step-box"><span class="step-num">PASO 3:</span> Si los tanques no tienen capacidad suficiente, contactar al proveedor de carrotanque con mínimo 4 horas de anticipación y coordinar la entrega.</div>
<div class="step-box"><span class="step-num">PASO 4:</span> Circular informativa a todos los residentes indicando: duración estimada del corte, disponibilidad de agua en tanques/carrotanque y recomendaciones de uso racional.</div>

<div class="subsection-title">7.2 Corte no programado (emergencia)</div>
<div class="step-box"><span class="step-num">PASO 1:</span> Verificar de inmediato si el corte es de la empresa prestadora (llamar a la línea de atención) o interno (revisar sistema de bombeo y tuberías).</div>
<div class="step-box"><span class="step-num">PASO 2:</span> Contactar al proveedor de carrotanque para solicitud urgente de agua.</div>
<div class="step-box"><span class="step-num">PASO 3:</span> Si el daño es interno, contactar al plomero o empresa de mantenimiento para reparación urgente.</div>
<div class="step-box"><span class="step-num">PASO 4:</span> Comunicar a los residentes la situación y las acciones en curso mediante mensajes por el sistema de comunicación del conjunto.</div>
<div class="step-box"><span class="step-num">PASO 5:</span> Si el corte supera 24 horas, notificar a la Secretaría de Salud Municipal y solicitar apoyo institucional.</div>

<!-- 8. MEDIDAS DE USO RACIONAL -->
<div class="section-title">8. MEDIDAS DE USO RACIONAL DEL AGUA DURANTE LA CONTINGENCIA</div>
<ul>
    <li>Suspender temporalmente el riego de jardines y lavado de zonas comunes.</li>
    <li>Priorizar el uso del agua para consumo humano, higiene personal y preparación de alimentos.</li>
    <li>Instruir a los residentes para llenar recipientes de almacenamiento personal (baldes, ollas) mientras haya presión.</li>
    <li>Evitar el uso de la piscina y zonas recreativas que consuman agua.</li>
    <li>Reportar inmediatamente cualquier fuga o desperdicio detectado.</li>
</ul>

<div class="alert-box">
    <strong>ℹ NOTA IMPORTANTE:</strong> El agua suministrada por carrotanque en contingencia está destinada únicamente para uso sanitario esencial (consumo, higiene, inodoros). No debe utilizarse para lavado de vehículos, riego o piscinas.
</div>

<!-- 9. RESPONSABLES -->
<div class="section-title">9. RESPONSABLES</div>
<table class="data-table">
    <tr><th>ROL</th><th>RESPONSABILIDAD</th></tr>
    <tr><td><strong>Administrador(a)</strong></td><td>Activar el plan, contactar al proveedor de carrotanque, notificar a residentes y autoridades, gestionar el registro de la contingencia.</td></tr>
    <tr><td><strong>Consultor SST</strong></td><td>Asesorar el plan, verificar condiciones sanitarias del agua alternativa, capacitar al personal.</td></tr>
    <tr><td><strong>Personal de mantenimiento</strong></td><td>Verificar estado del sistema de bombeo y tuberías, reportar daños internos y coordinar reparaciones.</td></tr>
    <tr><td><strong>Consejo de Administración</strong></td><td>Aprobar la contratación del proveedor de carrotanque y gestionar el presupuesto de emergencia.</td></tr>
</table>

<!-- 10. REGISTROS -->
<div class="section-title">10. REGISTROS Y DOCUMENTACIÓN</div>
<ul>
    <li>Registro de cada evento de corte: fecha, hora, duración, causa, acciones tomadas.</li>
    <li>Factura o recibo del servicio de carrotanque (volumen, costo, fecha).</li>
    <li>Circular de notificación a residentes.</li>
    <li>Reporte a la empresa prestadora del servicio si el corte es externo.</li>
    <li>Acta del Consejo de Administración si el evento requirió aprobación de recursos.</li>
</ul>

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
