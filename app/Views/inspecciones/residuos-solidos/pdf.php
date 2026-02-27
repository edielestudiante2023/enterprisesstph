<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    @page {
        margin: 80px 50px 60px 60px;
    }
    body {
        font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
        font-size: 10px;
        color: #333;
        line-height: 1.5;
        padding: 0;
        margin: 0;
    }
    .header-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
    }
    .header-table td {
        border: 2px solid #333;
        padding: 6px 8px;
        vertical-align: middle;
    }
    .header-table .logo-cell {
        width: 100px;
        text-align: center;
    }
    .header-table .logo-cell img {
        max-width: 90px;
        max-height: 55px;
    }
    .header-table .title-cell {
        text-align: center;
        font-weight: bold;
        font-size: 10px;
    }
    .header-table .code-cell {
        width: 120px;
        font-size: 9px;
    }
    .main-title {
        text-align: center;
        font-weight: bold;
        font-size: 13px;
        color: #1c2437;
        margin: 20px 0 5px;
    }
    .subtitle {
        text-align: center;
        font-weight: bold;
        font-size: 11px;
        color: #333;
        margin-bottom: 15px;
    }
    .section-title {
        font-weight: bold;
        font-size: 11px;
        color: #1c2437;
        margin-top: 18px;
        margin-bottom: 6px;
        border-bottom: 1px solid #1c2437;
        padding-bottom: 3px;
    }
    .subsection-title {
        font-weight: bold;
        font-size: 10px;
        color: #1c2437;
        margin-top: 12px;
        margin-bottom: 4px;
    }
    .data-table {
        width: 100%;
        border-collapse: collapse;
        margin: 8px 0 12px;
        font-size: 9px;
    }
    .data-table th {
        background: #1c2437;
        color: white;
        padding: 5px 6px;
        text-align: left;
        font-weight: bold;
        border: 1px solid #333;
    }
    .data-table td {
        padding: 4px 6px;
        border: 1px solid #ccc;
        vertical-align: top;
    }
    .data-table tr:nth-child(even) td {
        background: #f9f9f9;
    }
    .lista-items {
        margin: 4px 0 8px 20px;
        padding: 0;
    }
    .lista-items li {
        margin-bottom: 3px;
    }
    p {
        margin: 4px 0 8px;
        text-align: justify;
    }
    .definition-term {
        font-weight: bold;
        margin-top: 8px;
        margin-bottom: 2px;
    }
    .definition-text {
        margin: 0 0 6px 0;
        text-align: justify;
    }
    .nota {
        font-style: italic;
        color: #555;
        margin: 6px 0;
    }
    .indicador-box {
        border: 1px solid #ccc;
        padding: 8px;
        margin: 8px 0;
        background: #fafafa;
    }
    .indicador-box .ind-label {
        font-weight: bold;
        color: #1c2437;
    }
</style>
</head>
<body>

<?php $nombreCliente = $cliente['nombre_cliente'] ?? 'CONJUNTO RESIDENCIAL'; ?>

<!-- HEADER -->
<table class="header-table">
    <tr>
        <td class="logo-cell" rowspan="2">
            <?php if (!empty($logoBase64)): ?>
                <img src="<?= $logoBase64 ?>" alt="Logo">
            <?php else: ?>
                <strong style="font-size: 8px;">LOGO</strong>
            <?php endif; ?>
        </td>
        <td class="title-cell">
            SISTEMA DE GESTIÓN DE SEGURIDAD Y SALUD EN EL TRABAJO
        </td>
        <td class="code-cell">
            <strong>Código:</strong> FT-SST-226<br>
            <strong>Versión:</strong> 001
        </td>
    </tr>
    <tr>
        <td class="title-cell">
            PROGRAMA DE MANEJO INTEGRAL DE RESIDUOS SÓLIDOS
        </td>
        <td class="code-cell">
            <strong>Fecha:</strong> <?= !empty($inspeccion['fecha_programa']) ? date('d/m/Y', strtotime($inspeccion['fecha_programa'])) : '' ?>
        </td>
    </tr>
</table>

<!-- TÍTULO PRINCIPAL -->
<div class="main-title">PROGRAMA DE DESECHOS SÓLIDOS – PROPIEDAD HORIZONTAL</div>
<div class="subtitle"><?= esc($nombreCliente) ?></div>

<!-- 1.1 OBJETIVO -->
<div class="section-title">1.1 OBJETIVO</div>
<p>Definir, planificar y estandarizar las actividades, responsabilidades, elementos, áreas, recursos humanos, mecanismos de control, indicadores y procedimientos que garanticen una gestión integral, segura y eficiente de los residuos sólidos generados en <?= esc($nombreCliente) ?>, desde su generación, separación en la fuente, recolección interna, almacenamiento temporal, aprovechamiento y/o disposición final, en cumplimiento de la normatividad ambiental y sanitaria vigente aplicable a la Propiedad Horizontal.</p>

<!-- 1.2 ALCANCE -->
<div class="section-title">1.2 ALCANCE</div>
<p>El presente Programa aplica a todos los residuos sólidos generados por <?= esc($nombreCliente) ?>, derivados de las siguientes actividades:</p>
<ul class="lista-items">
    <li>Administración del conjunto</li>
    <li>Actividades residenciales</li>
    <li>Uso de zonas comunes</li>
    <li>Usuarios, visitantes, proveedores y contratistas</li>
</ul>
<p>El Programa es de obligatorio cumplimiento para residentes, personal administrativo, personal de servicios generales, empresas contratistas y terceros que desarrollen actividades dentro de la copropiedad.</p>
<p>Incluye las etapas de separación en la fuente, recolección interna, transporte interno, almacenamiento temporal, aprovechamiento, entrega al prestador del servicio público de aseo y manejo de residuos peligrosos cuando se generen.</p>

<!-- 1.3 DEFINICIONES -->
<div class="section-title">1.3 DEFINICIONES</div>
<p>Para efectos del Programa de Desechos Sólidos de <?= esc($nombreCliente) ?>, se adoptan las siguientes definiciones:</p>

<div class="definition-term">Almacenamiento:</div>
<p class="definition-text">Acción de depositar temporalmente los residuos en recipientes o contenedores debidamente identificados, mientras se realiza su recolección interna, aprovechamiento o disposición final.</p>

<div class="definition-term">Aprovechamiento:</div>
<p class="definition-text">Proceso mediante el cual los residuos recuperables son reincorporados al ciclo productivo mediante reciclaje, reutilización o compostaje.</p>

<div class="definition-term">Caracterización de residuos:</div>
<p class="definition-text">Identificación cualitativa y cuantitativa de los residuos generados en la copropiedad, de acuerdo con su tipo y origen.</p>

<div class="definition-term">Disposición final:</div>
<p class="definition-text">Confinamiento definitivo de residuos no aprovechables en sitios autorizados por la autoridad competente.</p>

<div class="definition-term">Gestión Integral de Residuos Sólidos (GIRS):</div>
<p class="definition-text">Conjunto de acciones técnicas, operativas, educativas y administrativas orientadas a la reducción, separación en la fuente, aprovechamiento y disposición adecuada de los residuos sólidos.</p>

<div class="definition-term">Generador:</div>
<p class="definition-text">Residente, usuario, visitante, trabajador o contratista que produce residuos dentro de la copropiedad.</p>

<div class="definition-term">Punto ecológico:</div>
<p class="definition-text">Área señalizada con recipientes diferenciados para la separación en la fuente de los residuos, conforme al código de colores establecido.</p>

<div class="definition-term">Ruta sanitaria:</div>
<p class="definition-text">Recorrido interno establecido para la recolección de residuos desde los puntos ecológicos hasta la unidad de almacenamiento temporal.</p>

<div class="definition-term">Unidad de almacenamiento temporal:</div>
<p class="definition-text">Espacio físico destinado exclusivamente para el almacenamiento interno de residuos, debidamente señalizado, ventilado y de acceso restringido.</p>

<!-- 1.4 PRECAUCIONES -->
<div class="section-title">1.4 PRECAUCIONES DE SEGURIDAD Y OBSERVACIONES GENERALES</div>
<p>Para la adecuada implementación del Programa de Desechos Sólidos en <?= esc($nombreCliente) ?>, se deberán cumplir las siguientes medidas:</p>
<ul class="lista-items">
    <li>Uso permanente y obligatorio de los Elementos de Protección Personal (EPP) por parte del personal de servicios generales.</li>
    <li>Higienización de manos al finalizar las actividades de recolección y manejo de residuos.</li>
    <li>Cabello recogido y cubierto durante la ejecución de las labores.</li>
    <li>Prohibición de fumar, comer o beber durante las actividades de manejo de residuos.</li>
    <li>Reporte inmediato de condiciones inseguras a la Administración.</li>
    <li>Evitar el contacto directo con residuos cortopunzantes o peligrosos.</li>
</ul>

<!-- 1.5 RECOLECCIÓN EN LA FUENTE -->
<div class="section-title">1.5 RECOLECCIÓN DE RESIDUOS EN LA FUENTE</div>
<ol class="lista-items" type="a">
    <li>El personal de servicios generales deberá colocarse los EPP antes de iniciar la recolección.</li>
    <li>Preparar los implementos necesarios: carro recolector (practicwagon o similar), escoba, recogedor y bolsas según clasificación.</li>
    <li>Verificar el adecuado estado físico, de limpieza y desinfección del carro recolector.</li>
    <li>Identificar y controlar condiciones inseguras.</li>
    <li>Realizar el recorrido conforme a la ruta sanitaria definida por la Administración del Conjunto.</li>
    <li>Recolectar los residuos depositados en los puntos ecológicos y áreas comunes.</li>
    <li>Transportar los residuos hasta la unidad de almacenamiento temporal y disponerlos según su clasificación.</li>
    <li>Realizar lavado de manos al finalizar la labor.</li>
</ol>

<!-- 1.6 NORMAS DE BIOSEGURIDAD -->
<div class="section-title">1.6 NORMAS DE BIOSEGURIDAD</div>
<ol class="lista-items" type="a">
    <li>El acceso a la unidad de almacenamiento temporal de residuos estará restringido exclusivamente al personal autorizado por la Administración.</li>
    <li>Clasificar los residuos conforme al esquema de separación en la fuente establecido por la copropiedad.</li>
    <li>Utilizar obligatoriamente los EPP durante las labores de recolección, limpieza y desinfección.</li>
    <li>Garantizar capacitación periódica al personal en riesgos, manejo seguro de residuos y procedimientos establecidos.</li>
    <li>Mantener el cuarto de residuos en condiciones óptimas de orden, aseo y desinfección.</li>
    <li>Prohibido fumar, comer o beber dentro del cuarto de almacenamiento y durante la manipulación de residuos.</li>
</ol>

<!-- 1.7 RESPONSABLES -->
<div class="section-title">1.7 RESPONSABLES</div>

<div class="definition-term">Consejo de Administración:</div>
<p class="definition-text">Aprobación del Programa y seguimiento a su cumplimiento.</p>

<div class="definition-term">Administración del Conjunto:</div>
<p class="definition-text">Implementación, control, seguimiento y mejora continua del Programa.</p>

<div class="definition-term">Personal de Servicios Generales:</div>
<p class="definition-text">Ejecución operativa de la recolección, limpieza y manejo interno de los residuos.</p>

<div class="definition-term">Residentes y Usuarios:</div>
<p class="definition-text">Separación en la fuente y disposición adecuada de los residuos.</p>

<div class="definition-term">Responsable del SG-SST del Conjunto Residencial:</div>
<p class="definition-text">Velar por la identificación de peligros asociados al manejo de residuos, verificar el uso adecuado de EPP y hacer seguimiento al cumplimiento del cronograma del Programa de Gestión Integral de Residuos Sólidos.</p>

<!-- 1.8 INSUMOS -->
<div class="section-title">1.8 INSUMOS Y ELEMENTOS NECESARIOS</div>
<ul class="lista-items">
    <li>Guantes de caucho</li>
    <li>Tapabocas</li>
    <li>Botas plásticas</li>
    <li>Delantal plástico</li>
    <li>Carro recolector</li>
    <li>Bolsas según código de colores</li>
    <li>Escobas, recogedores y cepillos</li>
    <li>Señalización para puntos ecológicos</li>
</ul>
<p>Una vez finalizadas las actividades, los elementos utilizados deberán ser limpiados, desinfectados y almacenados en el área designada, dejando las zonas intervenidas en óptimas condiciones de orden y aseo.</p>

<!-- 1.9 EPP -->
<div class="section-title">1.9 ELEMENTOS DE PROTECCIÓN PERSONAL NECESARIOS PARA EL MANEJO DE DESECHOS SÓLIDOS</div>

<table class="data-table">
    <tr>
        <th>ACTIVIDAD</th>
        <th>ELEMENTOS DE PROTECCIÓN PERSONAL (EPP)</th>
        <th>IMPLEMENTOS REQUERIDOS</th>
    </tr>
    <tr>
        <td>Recolección interna de residuos</td>
        <td>Overol, tapabocas, gafas de seguridad, guantes de hule, botas de seguridad</td>
        <td>Carro de recolección interna de residuos</td>
    </tr>
    <tr>
        <td>Clasificación de desechos</td>
        <td>Overol, tapabocas, gafas de seguridad, guantes de hule, botas de seguridad</td>
        <td>Recipientes impermeables, resistentes, de fácil limpieza y preferiblemente biodegradables</td>
    </tr>
    <tr>
        <td>Transporte interno de residuos</td>
        <td>Overol, tapabocas, gafas de seguridad, guantes de hule, botas de seguridad</td>
        <td>Carro de recolección interna, zorra de recolección</td>
    </tr>
    <tr>
        <td>Almacenamiento temporal de residuos</td>
        <td>Overol, tapabocas, gafas de seguridad, guantes de hule, botas de seguridad</td>
        <td>Contenedores plásticos impermeables, estibas</td>
    </tr>
    <tr>
        <td>Disposición final de residuos</td>
        <td>Overol, tapabocas, gafas de seguridad, guantes de hule, botas de seguridad</td>
        <td>Zorra de recolección, escoba, recogedor, bolsas, canecas de 55 galones</td>
    </tr>
    <tr>
        <td>Entrega al prestador del servicio de aseo</td>
        <td>Overol, tapabocas, guantes de hule, botas de seguridad</td>
        <td>Carro de recolección interna</td>
    </tr>
</table>
<p class="nota">Nota: Los elementos de protección personal deberán encontrarse en buen estado, limpios y ser de uso exclusivo para las labores de manejo de residuos sólidos del conjunto residencial, conforme a los lineamientos del SG-SST.</p>

<!-- 1.10 FUENTES DE GENERACIÓN -->
<div class="section-title">1.10 FUENTES DE GENERACIÓN Y CLASIFICACIÓN DE RESIDUOS SÓLIDOS Y PELIGROSOS</div>
<p>De conformidad con lo establecido en la Ley 99 de 1993, la Ley 142 de 1994, el Decreto 1077 de 2015, la Resolución 2184 de 2019 (código de colores para la separación en la fuente) y la Resolución 1362 de 2007 para residuos peligrosos, en <?= esc($nombreCliente) ?> se identifican las siguientes fuentes de generación de residuos sólidos y peligrosos, derivadas del uso, ocupación y operación normal de la copropiedad.</p>

<div class="subsection-title">1.10.1 FUENTES DE GENERACIÓN DE RESIDUOS</div>

<div class="definition-term">a. Área de Administración</div>
<p class="definition-text">En esta área se generan principalmente residuos asociados a actividades administrativas y de gestión, tales como:</p>
<ul class="lista-items">
    <li>Residuos aprovechables (reciclables): papel, cartón, carpetas, sobres, archivos, botellas plásticas y envases limpios.</li>
    <li>Residuos no aprovechables (ordinarios): vasos desechables, empaques contaminados, residuos sanitarios.</li>
    <li>Residuos peligrosos (RESPEL): tóners de impresora, pilas, baterías, luminarias, envases contaminados con sustancias peligrosas.</li>
    <li>RAEE – Residuos de Aparatos Eléctricos y Electrónicos: equipos eléctricos o electrónicos fuera de uso, tales como computadores, impresoras, monitores, teclados, cables, cargadores y otros dispositivos similares, los cuales requieren manejo diferencial y entrega a gestores autorizados.</li>
</ul>

<div class="definition-term">b. Áreas comunes y zonas de uso colectivo</div>
<p class="definition-text">Incluyen pasillos, escaleras, parqueaderos, salones sociales, baños comunes, zonas verdes y áreas recreativas. En estas zonas se generan:</p>
<ul class="lista-items">
    <li>Residuos aprovechables: envases plásticos, cartón, papel, vidrio limpio.</li>
    <li>Residuos no aprovechables: residuos sanitarios, colillas, empaques contaminados, residuos de barrido.</li>
    <li>Residuos orgánicos: restos de poda, jardinería y residuos vegetales provenientes del mantenimiento de zonas verdes.</li>
</ul>

<div class="definition-term">c. Unidad de almacenamiento temporal de residuos</div>
<p class="definition-text">Corresponde al sitio destinado dentro de la copropiedad para la consolidación y presentación de los residuos al prestador del servicio público de aseo o a gestores autorizados. En esta área se concentran:</p>
<ul class="lista-items">
    <li>Residuos aprovechables previamente segregados.</li>
    <li>Residuos no aprovechables debidamente embolsados.</li>
    <li>Residuos peligrosos (RESPEL) almacenados de forma independiente, señalizada y segura, conforme a la normativa vigente.</li>
    <li>RAEE almacenados en área diferenciada, señalizada y bajo condiciones de seguridad.</li>
</ul>

<!-- 1.10.2 CLASIFICACIÓN -->
<div class="subsection-title">1.10.2 CLASIFICACIÓN DE LOS RESIDUOS GENERADOS</div>
<p>La clasificación de los residuos sólidos generados en <?= esc($nombreCliente) ?> se realizará conforme a la Resolución 2184 de 2019, así:</p>

<div class="definition-term">a. Residuos Aprovechables – Color Blanco</div>
<p class="definition-text">Son aquellos residuos que pueden reincorporarse al ciclo productivo mediante procesos de reciclaje o reutilización: papel y cartón limpios, plásticos, vidrio, metales, envases y empaques reciclables.</p>

<div class="definition-term">b. Residuos Orgánicos Aprovechables – Color Verde</div>
<p class="definition-text">Corresponden a residuos biodegradables susceptibles de aprovechamiento orgánico: restos de alimentos, residuos de poda y jardinería, residuos vegetales.</p>

<div class="definition-term">c. Residuos No Aprovechables – Color Negro</div>
<p class="definition-text">Son aquellos residuos que, por sus características, no pueden ser reciclados ni aprovechados: papel higiénico, servilletas usadas, residuos sanitarios, empaques contaminados, residuos de barrido.</p>

<div class="definition-term">d. Residuos Peligrosos (RESPEL)</div>
<p class="definition-text">Son aquellos residuos que por sus características corrosivas, reactivas, explosivas, tóxicas, inflamables, infecciosas o radiactivas representan riesgo para la salud humana y el ambiente, conforme a la Resolución 1362 de 2007. Incluyen: pilas y baterías, luminarias, tóner y cartuchos de impresión, envases contaminados con sustancias peligrosas. Estos residuos deberán almacenarse de forma separada, señalizada y entregarse exclusivamente a gestores autorizados, conservando los respectivos soportes y registros.</p>

<div class="definition-term">e. RAEE – Residuos de Aparatos Eléctricos y Electrónicos</div>
<p class="definition-text">Corresponden a equipos eléctricos y electrónicos que han llegado al final de su vida útil, tales como computadores, impresoras, monitores, teclados, cables, cargadores, electrodomésticos y otros dispositivos similares. Estos residuos deberán almacenarse en un área diferenciada, señalizada y entregarse a programas posconsumo o gestores autorizados, conservando los respectivos soportes de entrega.</p>

<!-- 1.11 PROCEDIMIENTO -->
<div class="section-title">1.11 PROCEDIMIENTO DE RECOLECCIÓN, ALMACENAMIENTO Y DISPOSICIÓN FINAL</div>
<p>El presente procedimiento establece los lineamientos para la correcta recolección, transporte interno, almacenamiento temporal y disposición final de los residuos sólidos y peligrosos generados en <?= esc($nombreCliente) ?>, de conformidad con la Ley 142 de 1994, el Decreto 1077 de 2015, la Resolución 2184 de 2019, la Resolución 1362 de 2007 y demás normas concordantes.</p>

<div class="subsection-title">1.11.1 Recolección de residuos generados en la fuente (áreas comunes y zonas de uso colectivo)</div>
<ol class="lista-items" type="a">
    <li>El operario(a) de servicios generales deberá colocarse previamente los elementos de protección personal (EPP) establecidos para la actividad.</li>
    <li>Preparar los implementos requeridos para la recolección: carro de recolección interna (buggie, practiwagon o similar), escoba, recogedor y bolsas según el tipo de residuo.</li>
    <li>Verificar que el carro de recolección se encuentre en adecuadas condiciones físicas, de limpieza y desinfección.</li>
    <li>Identificar condiciones inseguras que puedan generar riesgo durante la actividad.</li>
    <li>Iniciar el recorrido conforme a las rutas sanitarias internas definidas por la Administración del conjunto.</li>
    <li>Realizar la recolección de residuos en los puntos ecológicos y recipientes dispuestos en áreas comunes.</li>
    <li>Transportar los residuos a la unidad de almacenamiento temporal, disponiéndolos de acuerdo con su clasificación.</li>
    <li>Realizar higiene de manos al finalizar la actividad.</li>
</ol>

<div class="subsection-title">1.11.2 Recolección de residuos generados en puntos administrativos</div>
<ol class="lista-items" type="a">
    <li>Colocarse los EPP antes de iniciar la labor.</li>
    <li>Preparar los implementos: carro de recolección, bolsas, escoba y recogedor.</li>
    <li>Verificar el estado físico y sanitario del carro de recolección.</li>
    <li>Identificar condiciones inseguras.</li>
    <li>Ingresar a las oficinas administrativas y vaciar los recipientes de residuos.</li>
    <li>Transportar los residuos al cuarto de almacenamiento temporal y disponerlos según clasificación.</li>
    <li>Lavarse las manos al finalizar la actividad.</li>
</ol>

<div class="subsection-title">1.11.3 Recolección y manejo de residuos peligrosos (RESPEL y RAEE)</div>
<p>El manejo de residuos peligrosos generados en <?= esc($nombreCliente) ?> se realizará conforme a la Resolución 1362 de 2007, los programas posconsumo vigentes y los lineamientos de la autoridad ambiental competente.</p>
<ol class="lista-items" type="a">
    <li>Colocarse los EPP antes de iniciar la labor.</li>
    <li>Preparar los implementos requeridos para la recolección.</li>
    <li>Verificar el adecuado estado del carro de recolección.</li>
    <li>Identificar condiciones inseguras.</li>
    <li>Realizar el recorrido por los puntos designados para la disposición de residuos peligrosos.</li>
    <li>Recolectar residuos como pilas, baterías, luminarias, tóners y RAEE, debidamente empacados y rotulados.</li>
    <li>Disponerlos en el área exclusiva y señalizada del cuarto de almacenamiento temporal.</li>
    <li>Entregar los residuos a gestores autorizados o programas posconsumo.</li>
    <li>Conservar manifiestos, actas de recolección y certificados de disposición final.</li>
</ol>

<div class="subsection-title">1.11.4 Recolección de residuos orgánicos</div>
<ol class="lista-items" type="a">
    <li>Colocarse los EPP antes de iniciar la labor.</li>
    <li>Preparar los implementos necesarios.</li>
    <li>Verificar el estado del carro de recolección.</li>
    <li>Identificar condiciones inseguras.</li>
    <li>Recolectar los residuos orgánicos depositados en contenedores verdes.</li>
    <li>Transportarlos a la unidad de almacenamiento temporal, asegurando que los recipientes permanezcan cerrados.</li>
    <li>Entregar los residuos orgánicos al gestor externo autorizado para aprovechamiento.</li>
    <li>Conservar los manifiestos de recolección y transporte.</li>
</ol>

<!-- 1.11.5 ALTERNATIVAS -->
<div class="subsection-title">1.11.5 Alternativas para la optimización del manejo de residuos</div>
<ol class="lista-items" type="a">
    <li>Separación en la fuente: Implementación estricta del código de colores conforme a la Resolución 2184 de 2019.</li>
    <li>Aprovechamiento de residuos orgánicos: Entrega a gestores autorizados para compostaje o aprovechamiento.</li>
    <li>Reducción y reutilización: Reutilización de embalajes, cajas y costales en buen estado.</li>
    <li>Reciclaje: Fortalecer la cultura de separación con apoyo de recicladores de oficio autorizados.</li>
    <li>Control de calidad: Sensibilización a residentes y personal sobre la reducción del desperdicio y manejo responsable de residuos.</li>
</ol>

<!-- 1.12 CAPACITACIÓN -->
<div class="section-title">1.12 CAPACITACIÓN Y EDUCACIÓN CONTINUA</div>
<p>La Administración del conjunto deberá:</p>
<ul class="lista-items">
    <li>Identificar los actores internos involucrados en la generación de residuos.</li>
    <li>Implementar capacitaciones periódicas sobre separación en la fuente y manejo adecuado de residuos.</li>
    <li>Incluir personal operativo, administrativo y residentes.</li>
    <li>Difundir los lineamientos a través de carteleras, circulares y medios internos.</li>
    <li>Promover la corresponsabilidad ambiental y el cumplimiento normativo.</li>
</ul>

<!-- 1.13 CONTROL -->
<div class="section-title">1.13 CONTROL Y SEGUIMIENTO A LA IMPLEMENTACIÓN DEL PROGRAMA</div>
<p>La Administración llevará el control y seguimiento mediante registros mensuales de:</p>
<ul class="lista-items">
    <li>Residuos aprovechables</li>
    <li>Residuos no aprovechables</li>
    <li>Residuos peligrosos</li>
</ul>
<p>Estos registros deberán conservarse como soporte para auditorías de Secretaría de Salud y autoridades ambientales, y estarán disponibles en los archivos administrativos del conjunto residencial.</p>

<!-- 1.14 INDICADORES -->
<div class="section-title">1.14 INDICADORES</div>
<p>Con el fin de evaluar la eficacia sanitaria y operativa del Programa de Desechos Sólidos en <?= esc($nombreCliente) ?>, se establecen los siguientes indicadores:</p>

<div class="subsection-title">1.14.1 Condición sanitaria del cuarto de almacenamiento temporal de residuos</div>
<div class="indicador-box">
    <table class="data-table" style="margin: 0;">
        <tr><td class="ind-label" style="width: 40%; background: #f0f0f0;">Nombre del indicador</td><td>Cumplimiento de condiciones higiénico–sanitarias del cuarto de residuos</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Fórmula</td><td>(Inspecciones conformes / Inspecciones realizadas) × 100</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Tipo de indicador</td><td>Resultado</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Meta sugerida</td><td>100%</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Periodicidad</td><td>Mensual</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Fuente de información</td><td>Lista de chequeo de inspección sanitaria del cuarto de residuos + registro fotográfico</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Responsable</td><td>Administración del conjunto / Responsable SG-SST</td></tr>
    </table>
</div>
<p>Mide si el cuarto de almacenamiento temporal de residuos se encuentra limpio, desinfectado, ordenado, señalizado, sin presencia de plagas, con recipientes tapados y con separación adecuada de residuos. Un resultado inferior a la meta indica riesgo sanitario y requiere acción correctiva inmediata.</p>

<div class="subsection-title">1.14.2 Nivel de correcta separación en la fuente</div>
<div class="indicador-box">
    <table class="data-table" style="margin: 0;">
        <tr><td class="ind-label" style="width: 40%; background: #f0f0f0;">Nombre del indicador</td><td>Cumplimiento en separación adecuada de residuos sólidos</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Fórmula</td><td>(Puntos ecológicos correctamente separados / Puntos ecológicos inspeccionados) × 100</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Tipo de indicador</td><td>Resultado</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Meta sugerida</td><td>≥ 90%</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Periodicidad</td><td>Mensual</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Fuente de información</td><td>Listas de chequeo de inspección sanitaria + registros fotográficos</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Responsable</td><td>Administración del conjunto / Responsable SG-SST</td></tr>
    </table>
</div>
<p>Evalúa el nivel de cumplimiento en la separación adecuada de residuos según el código de colores vigente, evidenciando el grado de apropiación del programa por parte de residentes, usuarios y personal operativo.</p>

</body>
</html>
