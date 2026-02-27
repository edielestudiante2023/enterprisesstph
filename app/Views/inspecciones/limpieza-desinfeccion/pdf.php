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
    /* Header */
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
    /* Títulos */
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
    /* Tablas de datos */
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
    /* Listas */
    .lista-items {
        margin: 4px 0 8px 20px;
        padding: 0;
    }
    .lista-items li {
        margin-bottom: 3px;
    }
    /* Texto general */
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
    /* Page breaks */
    /* Indicadores */
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
            <strong>Código:</strong> FT-SST-225<br>
            <strong>Versión:</strong> 001
        </td>
    </tr>
    <tr>
        <td class="title-cell">
            PROGRAMA DE LIMPIEZA Y DESINFECCIÓN
        </td>
        <td class="code-cell">
            <strong>Fecha:</strong> <?= !empty($inspeccion['fecha_programa']) ? date('d/m/Y', strtotime($inspeccion['fecha_programa'])) : '' ?>
        </td>
    </tr>
</table>

<!-- TÍTULO PRINCIPAL -->
<div class="main-title">PROGRAMA DE LIMPIEZA Y DESINFECCIÓN</div>
<div class="subtitle"><?= esc($nombreCliente) ?></div>

<!-- 1.1 OBJETIVO -->
<div class="section-title">1.1 OBJETIVO</div>
<p>Definir, planificar y establecer las actividades, insumos, recursos humanos, elementos de protección personal (EPP), sustancias químicas autorizadas, responsabilidades, frecuencias, mecanismos de control e indicadores, así como los procedimientos técnicos necesarios, para ejecutar de manera segura, eficiente, sistemática y continua las labores de limpieza y desinfección en las áreas comunes del en <?= esc($nombreCliente) ?>, con el fin de prevenir riesgos sanitarios, controlar agentes contaminantes de origen biológico, físico y químico, y garantizar condiciones adecuadas de salubridad para residentes, trabajadores, contratistas, visitantes y demás usuarios de la copropiedad.</p>
<p>El presente Programa se desarrolla en cumplimiento de lo dispuesto en la Ley 9 de 1979, la Ley 675 de 2001, el Decreto 1072 de 2015 (SG-SST), y demás normas sanitarias y ambientales vigentes aplicables a las copropiedades, y se articula con el Plan de Saneamiento Básico como uno de sus componentes técnicos.</p>

<!-- 1.2 ALCANCE -->
<div class="section-title">1.2 ALCANCE</div>
<p>El presente Programa aplica a todas las áreas comunes en <?= esc($nombreCliente) ?>, bajo la responsabilidad de la Administración de la Propiedad Horizontal, e involucra al personal propio, personal de servicios generales y/o empresas contratistas encargadas de las labores de aseo, limpieza y desinfección.</p>
<p>Las actividades de limpieza y desinfección se desarrollarán, como mínimo, en las siguientes áreas:</p>
<ul class="lista-items">
    <li>Zonas comunes</li>
    <li>Pasillos</li>
    <li>Escaleras y rampas</li>
    <li>Pocetas de lavado</li>
    <li>Andenes perimetrales</li>
    <li>Cuartos de almacenamiento</li>
    <li>Contenedores de residuos transitorios</li>
    <li>Oficina de Administración</li>
    <li>Baños de Administración</li>
    <li>Salón social</li>
    <li>Baños de salones sociales</li>
    <li>Unidad de almacenamiento de residuos</li>
    <li>Techos y partes altas, internas y externas</li>
    <li>Canales de aguas lluvias</li>
    <li>Canales de aguas negras</li>
    <li>Parqueaderos comunales</li>
</ul>
<p>El Programa contempla la ejecución de actividades rutinarias, periódicas y extraordinarias, de acuerdo con los cronogramas establecidos, garantizando el uso exclusivo de productos de limpieza y desinfección debidamente rotulados, aprobados para uso doméstico o institucional, y aplicados conforme a las fichas técnicas y hojas de seguridad (SDS) suministradas por el fabricante.</p>

<!-- 1.3 DEFINICIONES -->
<div class="section-title">1.3 DEFINICIONES</div>
<p>Para efectos del presente Programa de Limpieza y Desinfección en <?= esc($nombreCliente) ?>, se adoptan las siguientes definiciones:</p>

<div class="definition-term">Ambiente:</div>
<p class="definition-text">Conjunto de elementos naturales y artificiales que rodean a las personas, incluyendo el aire, el agua, el suelo y su interrelación, así como las relaciones entre estos elementos y los seres vivos, que pueden influir en la salud y el bienestar humano. (Decreto 1843 de 1991).</p>

<div class="definition-term">Barrido:</div>
<p class="definition-text">Actividad que consiste en el retiro de residuos sólidos y partículas sueltas (polvo, arena, papeles u otros desechos visibles) presentes en superficies como pisos, andenes, escaleras y zonas comunes, mediante el uso de escobas, cepillos u otros implementos manuales.</p>

<div class="definition-term">Limpieza:</div>
<p class="definition-text">Proceso mediante el cual se elimina la suciedad visible, residuos orgánicos e inorgánicos de superficies, equipos o áreas, utilizando agua, detergente y acción mecánica, con el fin de preparar las superficies para una posterior desinfección y reducir la carga contaminante.</p>

<div class="definition-term">Desinfección:</div>
<p class="definition-text">Proceso orientado a la destrucción o inactivación de microorganismos patógenos presentes en superficies, ambientes u objetos, mediante la aplicación de agentes físicos o químicos, posterior a una adecuada limpieza. (Decreto 2257 de 1986).</p>

<div class="definition-term">Detergente:</div>
<p class="definition-text">Sustancia química utilizada en los procesos de limpieza cuya función es remover y disolver la suciedad adherida a las superficies. Debe ser soluble en agua, no corrosivo, no tóxico, biodegradable, de fácil enjuague y seguro para la salud humana y las superficies tratadas.</p>

<div class="definition-term">Desinfectante:</div>
<p class="definition-text">Sustancia química utilizada para eliminar o reducir significativamente microorganismos patógenos en superficies previamente limpias. Según su composición, pueden clasificarse en:</p>
<ul class="lista-items">
    <li><strong>Cloro y sus compuestos:</strong> eficaces contra una amplia variedad de microorganismos. Requieren superficies limpias, no deben mezclarse con otros productos, no se recomienda su uso en superficies metálicas sin enjuague posterior y no deben aplicarse con agua caliente.</li>
    <li><strong>Yodo y yodóforos:</strong> presentan alto poder microbicida. Se utilizan diluidos en agua y requieren enjuague posterior.</li>
    <li><strong>Desinfectantes de origen orgánico:</strong> elaborados a partir de componentes orgánicos, como extractos cítricos, que no dejan residuos nocivos para la salud.</li>
    <li><strong>Alcoholes:</strong> etanol o isopropanol, utilizados principalmente como antisépticos para la higiene de manos, con acción rápida y evaporación inmediata.</li>
</ul>

<div class="definition-term">Higiene:</div>
<p class="definition-text">Conjunto de prácticas y condiciones destinadas a mantener la limpieza de instalaciones, equipos, superficies y personas, con el fin de prevenir la contaminación y proteger la salud.</p>

<div class="definition-term">Hábitos higiénicos:</div>
<p class="definition-text">Conjunto de comportamientos y prácticas personales y laborales orientadas a prevenir la contaminación, la proliferación de microorganismos y la transmisión de agentes patógenos en las áreas comunes.</p>

<div class="definition-term">Inspección sanitaria:</div>
<p class="definition-text">Actividad de verificación y control realizada para evaluar las condiciones higiénico-sanitarias de personas, áreas, instalaciones o procesos, con el fin de comprobar el cumplimiento de la normatividad sanitaria vigente. (Decreto 2257 de 1986).</p>

<div class="definition-term">Inocuidad:</div>
<p class="definition-text">Condición que garantiza que un ambiente, superficie o proceso no representa riesgo para la salud humana cuando se utiliza de acuerdo con su finalidad prevista.</p>

<div class="definition-term">Solución:</div>
<p class="definition-text">Mezcla homogénea obtenida a partir de la dilución de una sustancia sólida o concentrada en agua, utilizada para procesos de limpieza o desinfección conforme a las indicaciones del fabricante.</p>

<div class="definition-term">Trampa de grasas:</div>
<p class="definition-text">Dispositivo hidráulico diseñado para retener grasas, aceites y sólidos flotantes provenientes de las aguas residuales, evitando su ingreso al sistema de alcantarillado y facilitando su manejo y disposición adecuada. (RAS 2000).</p>

<div class="definition-term">Vertimiento:</div>
<p class="definition-text">Descarga de aguas residuales o sustancias líquidas que, por su composición, pueden generar contaminación cuando son conducidas al sistema de alcantarillado o a cuerpos de agua.</p>

<div class="definition-term">Residuo corto punzante:</div>
<p class="definition-text">Elemento que, por sus características físicas, puede causar cortes o punciones y generar riesgo de accidente o infección, tales como vidrios rotos, cuchillas u objetos similares.</p>

<div class="definition-term">Residuos especiales:</div>
<p class="definition-text">Residuos que, por su composición o peligrosidad, requieren manejo diferenciado, tales como envases de productos químicos, plaguicidas, sobrantes o elementos contaminados durante actividades de mantenimiento o control de plagas. (Decreto 1843 de 1991).</p>

<!-- 1.4 RESPONSABLES -->
<div class="section-title">1.4 RESPONSABLES</div>
<p>La implementación, ejecución, seguimiento, evaluación y mejora continua del Programa de Limpieza y Desinfección estará a cargo de los siguientes actores:</p>

<div class="definition-term">Administrador(a) de la Copropiedad:</div>
<p class="definition-text">Responsable de garantizar la implementación integral del Programa, asignar los recursos humanos, técnicos y financieros necesarios, aprobar el cronograma de actividades, supervisar su cumplimiento, verificar el seguimiento de los indicadores establecidos y conservar los registros y soportes documentales.</p>

<div class="definition-term">Personal de servicios generales / aseo:</div>
<p class="definition-text">Encargado de ejecutar las actividades de limpieza y desinfección conforme a los procedimientos establecidos, utilizar correctamente los elementos de protección personal (EPP), diligenciar los formatos de registro y reportar oportunamente hallazgos o condiciones inseguras.</p>

<div class="definition-term">Empresas contratistas de aseo (cuando aplique):</div>
<p class="definition-text">Responsables de cumplir con los protocolos definidos en el presente Programa, suministrar personal capacitado, elementos de protección personal adecuados, productos autorizados con su respectiva ficha técnica y hoja de seguridad (SDS), y entregar los informes o certificaciones correspondientes a la Administración.</p>

<div class="definition-term">Consejo de Administración o Comité de Convivencia (cuando aplique):</div>
<p class="definition-text">Apoyar la verificación del cumplimiento del Programa, revisar informes de seguimiento e impulsar acciones de mejora cuando se identifiquen desviaciones o hallazgos sanitarios.</p>

<div class="definition-term">Responsable del SG-SST del Conjunto Residencial:</div>
<p class="definition-text">Velar por el cumplimiento del cronograma del Programa, articular su ejecución con el Sistema de Gestión de Seguridad y Salud en el Trabajo, coordinar con la Administración las actividades desarrolladas por empresas contratistas, verificar el uso adecuado de EPP y realizar seguimiento a los informes, indicadores y certificados entregados.</p>

<!-- 1.5 EPP -->
<div class="section-title">1.5 ELEMENTOS DE PROTECCIÓN PERSONAL (EPP)</div>
<p>Para la ejecución de las actividades de limpieza y desinfección, el personal deberá utilizar como mínimo los siguientes elementos de protección personal, conforme a la identificación de peligros y evaluación de riesgos:</p>
<ul class="lista-items">
    <li>Guantes de caucho o nitrilo resistentes a productos químicos</li>
    <li>Tapabocas o respirador, según el producto utilizado</li>
    <li>Gafas de seguridad</li>
    <li>Botas antideslizantes</li>
    <li>Delantal o overol impermeable</li>
    <li>Gorra o protección para cabeza (cuando aplique)</li>
</ul>
<p>El uso de EPP es obligatorio durante toda la actividad y deberá cumplir con lo establecido en el Decreto 1072 de 2015 y la normativa vigente en materia de seguridad y salud en el trabajo.</p>
<p>La Administración deberá garantizar el suministro oportuno, reposición, capacitación en uso adecuado y conservación de los EPP.</p>

<!-- 1.6 INSUMOS -->
<div class="section-title">1.6 INSUMOS Y PRODUCTOS DE LIMPIEZA Y DESINFECCIÓN</div>
<p>Los productos utilizados en el Programa deberán contar con registro sanitario vigente o autorización para uso doméstico o institucional, mantenerse debidamente rotulados y almacenarse conforme a las condiciones indicadas en sus fichas técnicas y hojas de seguridad (SDS).</p>
<p>Entre los insumos y productos autorizados se encuentran:</p>
<ul class="lista-items">
    <li>Detergente multiusos</li>
    <li>Desinfectante (hipoclorito de sodio, amonio cuaternario u otros autorizados)</li>
    <li>Jabón líquido</li>
    <li>Limpiavidrios</li>
    <li>Desengrasante</li>
    <li>Bolsas para residuos según código de colores</li>
    <li>Traperos, mopas, escobas y cepillos</li>
    <li>Paños de limpieza diferenciados por área</li>
</ul>
<p>Se prohíbe expresamente la mezcla de productos químicos, especialmente aquellos que puedan generar reacciones peligrosas, vapores tóxicos o pérdida de efectividad del principio activo.</p>
<p>Los productos deberán utilizarse exclusivamente conforme a la concentración y tiempo de contacto establecidos por el fabricante.</p>

<!-- Matriz insumos -->
<div class="subsection-title">Matriz de insumos, productos, usos e implementos</div>
<table class="data-table">
    <tr>
        <th>ACTIVIDAD</th>
        <th>SUSTANCIAS</th>
        <th>CONCENTRACIÓN / CONTACTO</th>
        <th>FORMA DE USO</th>
        <th>EPP</th>
        <th>IMPLEMENTOS</th>
    </tr>
    <tr>
        <td>Barrido</td>
        <td>Agua (si se requiere)</td>
        <td>Agua en pequeñas cantidades</td>
        <td>Aspersión ligera, barrido manual</td>
        <td>Overol, Tapabocas, Guantes de hule</td>
        <td>Escoba, Recogedor, Cepillos, Caneca</td>
    </tr>
    <tr>
        <td>Limpieza general pisos y superficies</td>
        <td>Detergente líquido o industrial</td>
        <td>Según ficha técnica</td>
        <td>Aplicar detergente, frotar y enjuagar</td>
        <td>Overol, Tapabocas, Guantes, Botas, Delantal</td>
        <td>Traperos, Cepillos, Caneca, Escoba</td>
    </tr>
    <tr>
        <td>Limpieza profunda</td>
        <td>Detergente industrial</td>
        <td>Según ficha técnica</td>
        <td>Aplicar detergente, frotar y enjuagar con presión</td>
        <td>Overol, Tapabocas, Guantes, Botas</td>
        <td>Hidrolavadora, Mangueras, Cepillos</td>
    </tr>
    <tr>
        <td>Limpieza de partes altas</td>
        <td>Detergente industrial</td>
        <td>Según ficha técnica</td>
        <td>Aplicar con equipos de alcance y enjuagar</td>
        <td>Arnés, Cuerdas, Casco, Overol, Guantes, Botas</td>
        <td>Escaleras, Andamios, Hidrolavadora</td>
    </tr>
    <tr>
        <td>Desinfección de superficies</td>
        <td>Hipoclorito de sodio (cloro)</td>
        <td>0,05% – 0,1% / 10 min</td>
        <td>Aplicar sobre superficie limpia, dejar actuar</td>
        <td>Overol, Tapabocas, Guantes, Botas, Delantal</td>
        <td>Traperos, Cepillos, Caneca</td>
    </tr>
    <tr>
        <td>Desinfección partes altas</td>
        <td>Hipoclorito de sodio (cloro)</td>
        <td>0,05% – 0,1% / 10 min</td>
        <td>Aspersión controlada con ventilación</td>
        <td>Arnés, Cuerdas, Casco, Overol, Guantes, Botas</td>
        <td>Hidrolavadora, Mangueras, Andamios</td>
    </tr>
</table>

<!-- 1.7 TÉCNICAS -->
<div class="section-title">1.7 TÉCNICAS DE LIMPIEZA Y DESINFECCIÓN</div>
<p>Con el fin de garantizar la efectividad del proceso de limpieza y desinfección y prevenir la contaminación cruzada, se deberán aplicar de manera obligatoria las siguientes técnicas inductivas:</p>

<div class="definition-term">Arriba – Abajo:</div>
<p class="definition-text">El proceso de limpieza y desinfección debe iniciar en las partes altas (techos, paredes, luminarias y ventanas) y finalizar en las partes medias y bajas (mesones, zócalos y pisos), evitando que la suciedad desprendida contamine superficies previamente intervenidas.</p>

<div class="definition-term">Adentro – Afuera:</div>
<p class="definition-text">Las actividades deben realizarse desde el interior del área hacia el exterior, tomando como referencia el punto de ingreso, con el fin de evitar el arrastre de partículas contaminantes hacia las zonas ya limpias.</p>

<div class="definition-term">De lo más limpio a lo más contaminado:</div>
<p class="definition-text">El procedimiento debe comenzar en las superficies con menor nivel de contaminación y finalizar en aquellas con mayor exposición a agentes contaminantes, reduciendo el riesgo de dispersión de microorganismos.</p>

<div class="definition-term">Lo más seco posible:</div>
<p class="definition-text">Se debe minimizar el uso excesivo de agua, utilizando únicamente la cantidad necesaria, con el fin de prevenir accidentes por superficies resbaladizas, evitar deterioro de la infraestructura y optimizar el consumo del recurso hídrico.</p>

<div class="definition-term">Técnica en zig–zag:</div>
<p class="definition-text">Consiste en realizar movimientos continuos en forma de zig–zag, permitiendo el arrastre efectivo de partículas sin devolver la contaminación a áreas previamente intervenidas, asegurando la cobertura total de la superficie.</p>

<!-- 1.8 PROCEDIMIENTO GENERAL -->
<div class="section-title">1.8 PROCEDIMIENTO GENERAL DE LIMPIEZA Y DESINFECCIÓN</div>
<p>Las actividades de limpieza y desinfección se desarrollarán conforme al Manual de Mantenimiento de la Infraestructura, enmarcadas en la conservación y mantenimiento preventivo, correctivo y recurrente de las instalaciones en <?= esc($nombreCliente) ?>, garantizando la protección de la infraestructura y la seguridad del personal.</p>
<p><strong>Procedimiento:</strong></p>
<ol class="lista-items">
    <li>Colocarse correctamente los elementos de protección personal antes de iniciar la labor.</li>
    <li>Preparar y verificar los insumos, equipos y herramientas requeridos.</li>
    <li>Realizar un análisis previo de condiciones inseguras que puedan generar accidentes laborales.</li>
    <li>Despejar completamente el área a intervenir.</li>
    <li>Barrer y recolectar los residuos, disponiéndolos en los contenedores según su clasificación.</li>
    <li>Preparar la solución detergente de acuerdo con las especificaciones del fabricante.</li>
    <li>Aplicar la solución detergente mediante aspersión, dejar actuar durante 10 minutos y restregar con escoba de cerda dura, cepillo o esponjilla.</li>
    <li>Restregar paredes, puertas, ventanas y mesones utilizando solución detergente.</li>
    <li>Restregar los pisos hasta eliminar completamente mugre y grasa.</li>
    <li>Enjuagar con pequeñas cantidades de agua utilizando hidrolavadora o manguera a presión, escurrir hacia las rejillas de desagüe.</li>
    <li>Preparar la solución desinfectante (cloro) según especificaciones del fabricante, mezclando durante mínimo 30 segundos.</li>
    <li>Aplicar el desinfectante en paredes, puertas, ventanas, mesones y pisos, dejando actuar por 10 minutos.</li>
    <li>Restregar las superficies para distribuir uniformemente el desinfectante.</li>
    <li>Enjuagar con pequeñas cantidades de agua hasta retirar completamente el producto.</li>
    <li>Secar al aire libre o con paños absorbentes y traperos previamente lavados y desinfectados.</li>
</ol>
<p class="nota">Nota 1: En caso de utilizar productos con acción detergente y desinfectante simultánea, se omiten los pasos 11, 12, 13 y 14.</p>

<!-- 1.9 PROCEDIMIENTO BAÑOS -->
<div class="section-title">1.9 PROCEDIMIENTO DE LIMPIEZA Y DESINFECCIÓN DE BAÑOS</div>
<p>Los servicios sanitarios son considerados áreas críticas, por lo que requieren un manejo riguroso para prevenir riesgos sanitarios.</p>
<ol class="lista-items">
    <li>Colocarse los elementos de protección personal.</li>
    <li>Preparar los insumos y equipos necesarios.</li>
    <li>Analizar condiciones inseguras del área.</li>
    <li>Despejar la zona al máximo.</li>
    <li>Aplicar solución detergente en inodoros, orinales y contenedores; dejar actuar 20 minutos y restregar.</li>
    <li>Aplicar solución detergente en pisos, paredes y techos; dejar actuar 10 minutos.</li>
    <li>Restregar todas las superficies para eliminar suciedad y grasa.</li>
    <li>Aplicar solución desinfectante en paredes, pisos y techos.</li>
    <li>Enjuagar con agua a presión y escurrir hacia desagües.</li>
    <li>Preparar nuevamente solución detergente según fabricante.</li>
    <li>Restregar superficies.</li>
    <li>Enjuagar hasta retirar completamente el desinfectante.</li>
    <li>Secar al aire libre con paños y traperos desinfectados.</li>
</ol>

<!-- 1.10 PROCEDIMIENTO PARTES ALTAS -->
<div class="section-title">1.10 PROCEDIMIENTO DE LIMPIEZA Y DESINFECCIÓN DE PARTES ALTAS</div>
<p>Aplica para techos, paredes, ventanas y áreas de difícil acceso.</p>
<ol class="lista-items">
    <li>Informar previamente a los usuarios para la protección de sus espacios.</li>
    <li>Colocarse los elementos de protección personal, incluidos los requeridos para trabajo en alturas.</li>
    <li>Preparar los equipos y herramientas.</li>
    <li>Analizar condiciones inseguras.</li>
    <li>Instalar escaleras, andamios o equipos certificados.</li>
    <li>Retirar manualmente la suciedad gruesa.</li>
    <li>Disponer los residuos en bolsas adecuadas.</li>
    <li>Retirar suciedad restante con cepillo o escobilla.</li>
    <li>Aplicar solución detergente y restregar hasta eliminar grasa y suciedad.</li>
    <li>Retirar el detergente con paños o espumas húmedas.</li>
    <li>Dejar secar al aire libre.</li>
    <li>Aplicar solución desinfectante (cloro) según fabricante.</li>
    <li>Retirar exceso de producto con paños absorbentes.</li>
    <li>Dejar secar al aire libre.</li>
</ol>
<p class="nota">Nota 2: Si se utilizan productos con acción detergente y desinfectante conjunta, se omiten los pasos 12, 13 y 14.</p>

<!-- 1.11 FRECUENCIA -->
<div class="section-title">1.11 FRECUENCIA DE LIMPIEZA Y DESINFECCIÓN</div>
<p>La frecuencia de ejecución de las actividades de limpieza y desinfección se establece de acuerdo con el nivel de uso, criticidad sanitaria y riesgo asociado a cada área, así:</p>
<table class="data-table">
    <tr>
        <th style="width: 60%;">ÁREA</th>
        <th>FRECUENCIA</th>
    </tr>
    <tr><td>Zonas comunes y pasillos</td><td>Diaria</td></tr>
    <tr><td>Escaleras y rampas</td><td>Diaria</td></tr>
    <tr><td>Baños administrativos y sociales</td><td>Diaria</td></tr>
    <tr><td>Oficina de administración</td><td>Diaria</td></tr>
    <tr><td>Salón social</td><td>Antes y después de cada uso</td></tr>
    <tr><td>Parqueaderos</td><td>Trimestral</td></tr>
    <tr><td>Andenes perimetrales</td><td>Mensual</td></tr>
    <tr><td>Cuartos de almacenamiento</td><td>Semanal</td></tr>
    <tr><td>Contenedores y unidad de residuos</td><td>Diaria</td></tr>
    <tr><td>Canales de aguas lluvias y negras</td><td>Semestral</td></tr>
    <tr><td>Techos y partes altas</td><td>Semestral</td></tr>
</table>
<p>Las frecuencias podrán ajustarse cuando se evidencien condiciones especiales de suciedad, incremento del uso de áreas, hallazgos sanitarios, brotes, quejas recurrentes o requerimientos de la autoridad sanitaria.</p>

<!-- 1.12 REGISTROS -->
<div class="section-title">1.12 REGISTROS Y FORMATOS</div>
<p>Para evidenciar la correcta ejecución, control y trazabilidad del Programa de Limpieza y Desinfección, se mantendrán como mínimo los siguientes registros:</p>
<ol class="lista-items">
    <li>Formato de limpieza y desinfección por área</li>
    <li>Cronograma mensual de actividades</li>
    <li>Registro de entrega, reposición y uso de EPP</li>
    <li>Hojas de seguridad (SDS) y fichas técnicas de los productos químicos</li>
    <li>Listas de chequeo de inspección sanitaria</li>
</ol>
<p>Todos los registros deberán conservarse en archivo físico o digital, encontrarse actualizados y estar disponibles para revisión por parte de la Administración y autoridades competentes.</p>

<!-- 1.13 MEDIDAS DE CONTROL -->
<div class="section-title">1.13 MEDIDAS DE CONTROL, SEGUIMIENTO Y MEJORA</div>
<p>La Administración del <?= esc($nombreCliente) ?> realizará el control y seguimiento al Programa mediante:</p>
<ol class="lista-items">
    <li>Inspecciones periódicas a las áreas intervenidas.</li>
    <li>Verificación mensual del cumplimiento del cronograma.</li>
    <li>Revisión del estado de limpieza y desinfección de las áreas comunes.</li>
    <li>Registro, análisis y corrección de no conformidades.</li>
    <li>Seguimiento a los indicadores del Programa.</li>
    <li>Implementación de acciones correctivas y preventivas cuando se identifiquen desviaciones.</li>
    <li>Actualización anual del Programa o cuando se presenten cambios operativos, locativos o normativos.</li>
</ol>

<!-- 1.14 INDICADORES -->
<div class="section-title">1.14 INDICADORES DEL PROGRAMA</div>
<p>Con el fin de verificar la ejecución efectiva del Programa de Limpieza y Desinfección y garantizar su mejora continua, se establecen los siguientes indicadores de gestión:</p>

<div class="subsection-title">1.14.1 Cumplimiento de actividades de limpieza y desinfección</div>
<div class="indicador-box">
    <table class="data-table" style="margin: 0;">
        <tr><td class="ind-label" style="width: 40%; background: #f0f0f0;">Nombre del indicador</td><td>Cumplimiento de actividades de limpieza y desinfección</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Tipo de indicador</td><td>Proceso</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Fórmula de cálculo</td><td>(Actividades ejecutadas / Actividades programadas) × 100</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Meta</td><td>Mayor o igual a 95%</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Periodicidad de medición</td><td>Mensual</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Fuente de información</td><td>Cronograma mensual de actividades y formatos diligenciados</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Responsable</td><td>Administración del Conjunto Residencial</td></tr>
    </table>
</div>
<p>Este indicador permite verificar el grado de cumplimiento del cronograma establecido, evidenciando si las actividades programadas se están ejecutando conforme a lo planificado. Un resultado inferior a la meta establecida deberá generar acciones correctivas.</p>

<div class="subsection-title">1.14.2 Cobertura de desinfección en áreas críticas</div>
<div class="indicador-box">
    <table class="data-table" style="margin: 0;">
        <tr><td class="ind-label" style="width: 40%; background: #f0f0f0;">Nombre del indicador</td><td>Cobertura de desinfección en áreas críticas</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Tipo de indicador</td><td>Proceso</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Fórmula de cálculo</td><td>(Áreas críticas desinfectadas / Áreas críticas programadas) × 100</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Meta</td><td>100%</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Periodicidad de medición</td><td>Mensual</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Áreas críticas</td><td>Baños, pasamanos, ascensores, barandas, botones, manijas y superficies de alto contacto</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Fuente de información</td><td>Formato de desinfección de áreas críticas</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Responsable</td><td>Administración del Conjunto Residencial</td></tr>
    </table>
</div>
<p>Este indicador permite verificar que todas las superficies consideradas de alto riesgo sanitario reciban el tratamiento de desinfección conforme a lo programado. El incumplimiento deberá generar revisión inmediata de la ejecución del programa.</p>

</body>
</html>
