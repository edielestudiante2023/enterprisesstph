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
    .alert-box { background: #fff3cd; border: 1.5px solid #e6a800; border-radius: 4px; padding: 7px 10px; margin: 8px 0; font-size: 9.5px; }
    .step-box { border: 1px solid #1c2437; border-radius: 3px; padding: 6px 10px; margin: 5px 0; font-size: 9.5px; }
    .step-num { font-weight: bold; color: #1c2437; }
    .num-circle { display: inline-block; width: 18px; height: 18px; line-height: 18px; text-align: center; background: #bd9751; color: white; border-radius: 50%; font-weight: bold; font-size: 9px; }
    p { margin: 5px 0 8px; font-size: 10px; }
    ul, ol { margin: 4px 0 8px 18px; font-size: 10px; }
    li { margin-bottom: 2px; }
    .nota { font-size: 8.5px; color: #555; font-style: italic; margin: 4px 0; }
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
            PLAN DE CONTINGENCIAS LIMPIEZA Y DESINFECCIÓN<br>
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

<div class="main-title">PLAN DE CONTINGENCIAS LIMPIEZA Y DESINFECCIÓN</div>
<div class="subtitle">Propiedad Horizontal — Salud y Seguridad en el Trabajo</div>

<!-- 1. OBJETIVO -->
<div class="section-title">1. OBJETIVO</div>
<p>Establecer las acciones de prevención, control y respuesta ante eventos que afecten la continuidad del servicio de limpieza y desinfección de las áreas comunes (agotamiento de insumos, derrames, mezclas accidentales de químicos, fallas operativas, brotes sanitarios u otros), con el fin de proteger la salud de residentes, empleados y visitantes, y minimizar el riesgo químico y biológico en la copropiedad.</p>

<!-- 2. ALCANCE -->
<div class="section-title">2. ALCANCE</div>
<p>Este plan aplica a todas las áreas comunes y zonas de servicio de <strong><?= esc($cliente['nombre_cliente'] ?? 'la copropiedad') ?></strong>, incluyendo: portería, lobby, ascensores, escaleras, pasillos, salones comunales, gimnasio, piscina, cuarto de residuos, cuartos técnicos, parqueaderos, jardines y baños comunes. Cubre tanto las actividades rutinarias de limpieza como los eventos de contingencia que las interrumpan.</p>

<!-- 3. MARCO LEGAL -->
<div class="section-title">3. MARCO LEGAL</div>
<ul>
    <li><strong>Ley 9 de 1979</strong> — Código Sanitario Nacional. Obligaciones de saneamiento básico y manejo de sustancias químicas.</li>
    <li><strong>Decreto 1072 de 2015</strong> — Sistema de Gestión de Seguridad y Salud en el Trabajo (SG-SST).</li>
    <li><strong>Resolución 0312 de 2019</strong> — Estándares mínimos del SG-SST.</li>
    <li><strong>Decreto 1496 de 2018</strong> — Adopción del Sistema Globalmente Armonizado (SGA) de clasificación y etiquetado de productos químicos.</li>
    <li><strong>Resolución 2400 de 1979</strong> — Disposiciones sobre vivienda, higiene y seguridad en establecimientos de trabajo.</li>
    <li><strong>Ley 675 de 2001</strong> — Régimen de propiedad horizontal. Obligaciones del administrador sobre el mantenimiento de condiciones sanitarias.</li>
</ul>

<!-- 4. DEFINICIONES -->
<div class="section-title">4. DEFINICIONES</div>
<table class="data-table">
    <tr><th style="width:30%;">TÉRMINO</th><th>DEFINICIÓN</th></tr>
    <tr><td><strong>Limpieza</strong></td><td>Remoción mecánica de suciedad visible (polvo, basura, residuos orgánicos) usando agua y detergente.</td></tr>
    <tr><td><strong>Desinfección</strong></td><td>Proceso químico que reduce o elimina microorganismos patógenos sobre superficies previamente limpiadas.</td></tr>
    <tr><td><strong>SGA</strong></td><td>Sistema Globalmente Armonizado de clasificación y etiquetado de productos químicos.</td></tr>
    <tr><td><strong>MSDS / FDS</strong></td><td>Ficha de Datos de Seguridad de un producto químico (Material Safety Data Sheet).</td></tr>
    <tr><td><strong>Dilución</strong></td><td>Preparación de un producto químico concentrado con agua, respetando la dosis indicada por el fabricante.</td></tr>
    <tr><td><strong>Kit antiderrames</strong></td><td>Conjunto de elementos (sorbentes, EPP, bolsas, palas) para contener un derrame de químicos.</td></tr>
    <tr><td><strong>EPP</strong></td><td>Elemento de Protección Personal (guantes, gafas, mascarilla, delantal, botas).</td></tr>
</table>

<!-- 5. PROBABILIDADES DE OCURRENCIA -->
<div class="section-title">5. PROBABILIDADES DE OCURRENCIA</div>
<p>Eventos que pueden interrumpir o afectar la prestación del servicio de limpieza y desinfección:</p>
<table class="data-table">
    <tr><th style="width:5%;">#</th><th style="width:35%;">EVENTO</th><th>IMPLICACIÓN</th></tr>
    <tr><td><strong>1</strong></td><td>Agotamiento de insumos (hipoclorito, detergentes, jabón)</td><td>Suspende limpieza rutinaria; aumenta riesgo de contaminación cruzada.</td></tr>
    <tr><td><strong>2</strong></td><td>Derrame accidental de químicos durante almacenamiento o uso</td><td>Riesgo de exposición dérmica/respiratoria; contaminación del área.</td></tr>
    <tr><td><strong>3</strong></td><td>Mezcla accidental de químicos incompatibles (ej. hipoclorito + amoniaco)</td><td>Genera gases tóxicos (cloramina); intoxicación grave del personal.</td></tr>
    <tr><td><strong>4</strong></td><td>Vencimiento de productos químicos almacenados</td><td>Pérdida de eficacia desinfectante; falso sentido de seguridad.</td></tr>
    <tr><td><strong>5</strong></td><td>Contaminación cruzada por uso de paños/utensilios sin descontaminar entre áreas</td><td>Traslado de microorganismos entre baños y áreas de alimentos.</td></tr>
    <tr><td><strong>6</strong></td><td>Falla de suministro de agua</td><td>Impide preparar diluciones y realizar enjuagues; suspende operación.</td></tr>
    <tr><td><strong>7</strong></td><td>Falla eléctrica que afecta equipos (aspiradoras, hidrolavadoras, secadores)</td><td>Reduce eficacia de limpieza profunda; reprogramación de jornadas.</td></tr>
    <tr><td><strong>8</strong></td><td>Ausencia o incapacidad del personal de aseo</td><td>Acumulación de suciedad, residuos sin recolectar; quejas y riesgos sanitarios.</td></tr>
    <tr><td><strong>9</strong></td><td>Falla o rotura del equipo de aplicación (atomizadores, traperos, hidrolavadoras)</td><td>Reduce capacidad operativa; obliga a métodos manuales más lentos.</td></tr>
    <tr><td><strong>10</strong></td><td>Brote infeccioso o sanitario (COVID, gripe, infestación)</td><td>Exige protocolos intensificados y mayor consumo de insumos no presupuestados.</td></tr>
</table>

<!-- 6. VULNERABILIDADES -->
<div class="section-title">6. VULNERABILIDADES</div>
<p>Factores presentes en la operación que aumentan la probabilidad o el impacto de los eventos anteriores:</p>
<table class="data-table">
    <tr><th style="width:5%;">#</th><th style="width:45%;">VULNERABILIDAD</th><th>MITIGACIÓN ASOCIADA</th></tr>
    <tr><td><strong>1</strong></td><td>Falta de inventario actualizado y control de stock mínimo de insumos</td><td>Implementar planilla de control y punto de reorden por producto.</td></tr>
    <tr><td><strong>2</strong></td><td>Almacenamiento inadecuado (sin ventilación, junto a alimentos, sin rotulación)</td><td>Adecuar cuarto de productos químicos con ventilación, rotulado SGA y separación de alimentos.</td></tr>
    <tr><td><strong>3</strong></td><td>Personal sin capacitación en SGA y manejo de MSDS/FDS</td><td>Capacitación anual obligatoria, registro firmado.</td></tr>
    <tr><td><strong>4</strong></td><td>Ausencia o mal estado de EPP (guantes, gafas, mascarilla)</td><td>Dotación trimestral, inspección visual mensual, reemplazo inmediato.</td></tr>
    <tr><td><strong>5</strong></td><td>Falta de procedimientos escritos para preparación de diluciones</td><td>Cartilla plastificada por producto con dosis, frecuencia y precauciones.</td></tr>
    <tr><td><strong>6</strong></td><td>Sin kit antiderrames ni protocolo definido</td><td>Dotar kit en cuarto de aseo; entrenamiento práctico semestral.</td></tr>
    <tr><td><strong>7</strong></td><td>No segregación por código de color (paños/baldes baños vs cocina vs áreas comunes)</td><td>Adoptar sistema rojo-azul-amarillo-verde; señalética y dotación diferenciada.</td></tr>
    <tr><td><strong>8</strong></td><td>Único proveedor de insumos sin proveedores de respaldo</td><td>Mantener mínimo 2 proveedores aprobados y precalificados.</td></tr>
    <tr><td><strong>9</strong></td><td>Falta de mantenimiento preventivo de equipos eléctricos de aseo</td><td>Cronograma de mantenimiento; bitácora por equipo.</td></tr>
    <tr><td><strong>10</strong></td><td>Personal sin relevo capacitado / falta de polivalencia</td><td>Capacitación cruzada del personal; protocolo de cobertura ante ausencias.</td></tr>
</table>

<!-- 7. EMPRESA PRESTADORA DEL SERVICIO -->
<div class="section-title">7. EMPRESA PRESTADORA DEL SERVICIO CONTRATADA</div>
<?php if (!empty($inspeccion['empresa_limpieza'])): ?>
<table style="width:100%; border:1.5px solid #1c2437; border-collapse:collapse;">
    <tr>
        <td style="background:#1c2437; color:white; padding:6px 10px; font-weight:bold; width:35%;">EMPRESA PRESTADORA</td>
        <td style="padding:7px 10px;"><?= nl2br(esc($inspeccion['empresa_limpieza'])) ?></td>
    </tr>
</table>
<?php else: ?>
<p class="nota">Empresa prestadora del servicio: por definir — completar antes de activar el plan.</p>
<?php endif; ?>
<p style="margin-top:8px;">La empresa contratada (interna o externa) debe contar con:</p>
<ul>
    <li>Personal con afiliación vigente al sistema general de seguridad social.</li>
    <li>Programa documentado de capacitación en SGA, manejo de químicos y EPP.</li>
    <li>Hojas de Datos de Seguridad (FDS / MSDS) de todos los productos utilizados.</li>
    <li>Procedimientos operativos escritos para preparación de diluciones y atención de derrames.</li>
    <li>Dotación de EPP completa, vigente y en buen estado.</li>
    <li>Inventario mínimo de insumos de respaldo (mínimo 1 semana de operación).</li>
</ul>

<!-- 8. PROTOCOLO DE ACTUACIÓN -->
<div class="section-title">8. PROTOCOLO DE ACTUACIÓN ANTE CONTINGENCIAS</div>

<div class="subsection-title">8.1 Detección y reporte</div>
<div class="step-box"><span class="step-num">PASO 1:</span> Cualquier persona que detecte una contingencia (derrame, falta de insumos, mezcla accidental, falla de equipo, brote sanitario) debe reportarla de inmediato a la administración indicando: tipo de evento, área afectada, hora y personas involucradas.</div>
<div class="step-box"><span class="step-num">PASO 2:</span> La administración registra el reporte en la bitácora de contingencias sanitarias y realiza una verificación dentro de la siguiente hora.</div>

<div class="subsection-title">8.2 Clasificación del evento</div>
<table class="data-table">
    <tr><th>NIVEL</th><th>DESCRIPCIÓN</th><th>ACCIÓN</th><th>TIEMPO RESPUESTA</th></tr>
    <tr><td><strong>Nivel 1 — Bajo</strong></td><td>Faltante puntual de insumo o derrame menor (&lt; 1 L) sin afectados</td><td>Reposición desde stock de seguridad y limpieza del derrame con kit.</td><td>2 horas</td></tr>
    <tr><td><strong>Nivel 2 — Medio</strong></td><td>Falla de equipo, mezcla accidental sin afectados, ausencia de personal clave</td><td>Activar proveedor de respaldo, aislar área afectada, ventilar, sustituir personal.</td><td>8 horas</td></tr>
    <tr><td><strong>Nivel 3 — Alto</strong></td><td>Derrame mayor, intoxicación de persona, brote sanitario, contaminación de área crítica</td><td>Evacuación del área, atención médica, notificación a autoridades sanitarias.</td><td>Inmediato</td></tr>
</table>

<div class="subsection-title">8.3 Atención de un derrame químico</div>
<ol>
    <li>Evacuar al personal no esencial del área. Ventilar abriendo ventanas y puertas.</li>
    <li>Colocarse el EPP completo (guantes, gafas, mascarilla, delantal).</li>
    <li>Contener el derrame con sorbentes (arena, aserrín o material absorbente del kit).</li>
    <li>NO mezclar con otros químicos. NO usar agua si la FDS indica reacción adversa.</li>
    <li>Recoger los residuos en bolsa roja rotulada como "Residuo químico contaminado".</li>
    <li>Disponer la bolsa según ruta de residuos peligrosos definida en el plan de saneamiento.</li>
    <li>Registrar el evento, causa, cantidad derramada y acciones tomadas.</li>
</ol>

<div class="subsection-title">8.4 Atención de una mezcla accidental de químicos</div>
<ol>
    <li>Evacuar inmediatamente el área. <strong>NO inhalar los vapores.</strong></li>
    <li>Abrir ventanas y puertas para ventilar.</li>
    <li>Si hay personas expuestas: alejar del área, llevar a sitio ventilado, llamar a la línea de emergencia (123).</li>
    <li>Mantener el área cerrada al menos 30 minutos antes de reingresar con EPP.</li>
    <li>Reportar a la ARL y registrar como incidente de seguridad y salud en el trabajo.</li>
</ol>

<!-- 9. MEDIDAS PREVENTIVAS PERMANENTES -->
<div class="section-title">9. MEDIDAS PREVENTIVAS PERMANENTES</div>
<table class="data-table">
    <tr><th>MEDIDA</th><th>FRECUENCIA</th><th>RESPONSABLE</th></tr>
    <tr><td>Inventario y control de stock mínimo de insumos</td><td>Semanal</td><td>Administración / Recuperador</td></tr>
    <tr><td>Inspección visual del cuarto de productos químicos (orden, etiquetado, ventilación)</td><td>Mensual</td><td>Consultor SST</td></tr>
    <tr><td>Capacitación en SGA, MSDS, manejo de derrames y mezclas</td><td>Anual</td><td>Consultor SST</td></tr>
    <tr><td>Verificación de fechas de vencimiento de productos</td><td>Mensual</td><td>Personal de aseo</td></tr>
    <tr><td>Inspección y reposición de EPP</td><td>Trimestral</td><td>Administración</td></tr>
    <tr><td>Mantenimiento preventivo de equipos de aseo</td><td>Trimestral</td><td>Mantenimiento</td></tr>
    <tr><td>Verificación del kit antiderrames (completo y vigente)</td><td>Trimestral</td><td>Consultor SST</td></tr>
    <tr><td>Actualización del listado de proveedores de respaldo</td><td>Semestral</td><td>Administración</td></tr>
</table>

<!-- 10. COMUNICACIÓN -->
<div class="section-title">10. COMUNICACIÓN CON RESIDENTES Y AUTORIDADES</div>
<ul>
    <li><strong>Comunicación interna:</strong> Cuando una contingencia afecte el uso de áreas comunes (ej. evacuación por derrame), notificar a residentes por circular y avisos visibles indicando el área restringida y la hora estimada de retorno.</li>
    <li><strong>Comunicación a ARL:</strong> Todo evento que afecte a un trabajador debe reportarse a la ARL en las 48 horas siguientes.</li>
    <li><strong>Comunicación a Secretaría de Salud:</strong> En casos Nivel 3 (intoxicación, brote sanitario), notificar a la Secretaría de Salud Municipal.</li>
    <li><strong>Registro en actas de Consejo:</strong> Documentar la situación y las acciones tomadas en el acta de la siguiente reunión.</li>
</ul>

<div class="alert-box">
    <strong>⚠ ATENCIÓN:</strong> Está estrictamente prohibido <strong>mezclar productos químicos</strong> (hipoclorito + amoniaco, hipoclorito + ácidos, peróxidos + alcoholes, entre otros). El personal solo debe aplicar las diluciones documentadas en las cartillas de procedimiento entregadas y firmadas.
</div>

<!-- 11. RESPONSABLES -->
<div class="section-title">11. RESPONSABLES</div>
<table class="data-table">
    <tr><th>ROL</th><th>RESPONSABILIDAD</th></tr>
    <tr><td><strong>Administrador(a)</strong></td><td>Activar el plan, autorizar compras de respaldo, notificar autoridades, mantener bitácora.</td></tr>
    <tr><td><strong>Consultor SST</strong></td><td>Asesorar el plan, capacitar al personal, verificar EPP/kit antiderrames y cumplimiento del SGA.</td></tr>
    <tr><td><strong>Personal de aseo</strong></td><td>Reportar inmediatamente eventos, aplicar protocolos, usar EPP, seguir cartillas de dilución.</td></tr>
    <tr><td><strong>Mantenimiento</strong></td><td>Mantenimiento preventivo de equipos eléctricos de aseo. Reparación tras fallas.</td></tr>
    <tr><td><strong>Empresa prestadora del servicio</strong></td><td>Cumplir procedimientos, suministrar insumos certificados, capacitar a su personal.</td></tr>
    <tr><td><strong>Consejo de Administración</strong></td><td>Aprobar contrataciones y presupuesto para insumos, EPP y mantenimiento.</td></tr>
</table>

</body>
</html>
