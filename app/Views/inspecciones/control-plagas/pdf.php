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
    .page-break {
        page-break-before: always;
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
            <strong>Código:</strong> FT-SST-227<br>
            <strong>Versión:</strong> 001
        </td>
    </tr>
    <tr>
        <td class="title-cell">
            PROGRAMA DE CONTROL INTEGRADO DE PLAGAS
        </td>
        <td class="code-cell">
            <strong>Fecha:</strong> <?= !empty($inspeccion['fecha_programa']) ? date('d/m/Y', strtotime($inspeccion['fecha_programa'])) : '' ?>
        </td>
    </tr>
</table>

<!-- TÍTULO PRINCIPAL -->
<div class="main-title">1. PROGRAMA PARA EL CONTROL INTEGRAL DE PLAGAS</div>
<div class="subtitle"><?= esc($nombreCliente) ?></div>

<!-- 1.1 OBJETIVO -->
<div class="section-title">1.1 OBJETIVO</div>
<p>Prevenir, controlar y minimizar la presencia y proliferación de plagas y roedores en <?= esc($nombreCliente) ?>, mediante la implementación de acciones preventivas, correctivas y de control, orientadas a la protección de la salud de residentes, visitantes y trabajadores, garantizando condiciones adecuadas de salubridad en todas las áreas comunes de la copropiedad.</p>

<!-- 1.2 ALCANCE -->
<div class="section-title">1.2 ALCANCE</div>
<p>El presente Programa aplica para todas las áreas comunes de <?= esc($nombreCliente) ?>, incluyendo zonas de circulación, cuartos técnicos, cuartos eléctricos, áreas verdes, zonas de residuos, cuartos de almacenamiento, parqueaderos, sótanos, zonas perimetrales, y espacios o módulos en situación de abandono, donde puedan generarse condiciones favorables para la proliferación de plagas.</p>
<p>El Programa es de obligatorio cumplimiento para el personal administrativo, personal de servicios generales, empresas contratistas y demás terceros que desarrollen actividades dentro de la copropiedad.</p>

<!-- 1.3 DEFINICIONES -->
<div class="section-title">1.3 DEFINICIONES</div>
<div class="definition-term">Proliferación de plagas:</div>
<p class="definition-text">Condición en la cual organismos indeseables como roedores, insectos, aves u otros animales incrementan su presencia, reproducción y permanencia en un área, generando riesgo sanitario, ambiental y estructural.</p>
<p>Las plagas representan una amenaza para la salud pública y las condiciones sanitarias, por lo cual deben controlarse de manera sistemática las condiciones que favorecen su anidamiento y reproducción. Esto se logra mediante un adecuado saneamiento, inspección permanente y vigilancia continua, limitando el uso de productos químicos únicamente cuando sea estrictamente necesario.</p>
<p>El Programa de Limpieza y Desinfección, en conjunto con el Programa de Control Integral de Plagas y Roedores, debe aplicarse de manera articulada, entendiendo el control integral como la implementación de un conjunto de operaciones físicas, químicas y de gestión, orientadas a minimizar la presencia de plagas.</p>
<p>Las principales condiciones que favorecen la proliferación de plagas son:</p>
<ul class="lista-items">
    <li>Humedad</li>
    <li>Aire</li>
    <li>Alimento</li>
    <li>Refugio</li>
    <li>Calor</li>
</ul>
<p>Para evitar su desarrollo, se deberán implementar de forma continua las siguientes acciones preventivas:</p>
<ol class="lista-items" type="a">
    <li>Limpiar diariamente los restos de alimentos en superficies y áreas comunes.</li>
    <li>Eliminar la grasa acumulada en zonas donde se manipulen alimentos.</li>
    <li>Barrer y limpiar los pisos, incluyendo áreas debajo de mesas, equipos y cercanas a paredes.</li>
    <li>Mantener limpios los desagües.</li>
    <li>Eliminar aguas estancadas y limpiar derrames de manera inmediata.</li>
    <li>Recoger trapos, delantales, servilletas y manteles sucios; lavar con frecuencia los elementos de tela.</li>
    <li>Evitar el almacenamiento de objetos en cajas de cartón o directamente sobre el suelo.</li>
    <li>Almacenar las cajas en estanterías, preferiblemente metálicas.</li>
    <li>No depositar residuos sólidos cerca de áreas de manipulación o consumo de alimentos.</li>
</ol>
<p>La aplicación permanente de estas medidas genera condiciones adversas que dificultan el desarrollo y permanencia de plagas.</p>
<p>Las plagas más comunes identificadas en copropiedades son:</p>
<ul class="lista-items">
    <li>Roedores</li>
    <li>Palomas y otras aves</li>
    <li>Insectos rastreros y voladores</li>
    <li>Animales ferales (como gatos)</li>
</ul>

<!-- 1.4 CONSIDERACIONES GENERALES -->
<div class="page-break"></div>
<div class="section-title">1.4 CONSIDERACIONES GENERALES</div>
<p>Los roedores, como ratas y ratones, son especies que se han adaptado al entorno humano, representando un riesgo significativo para la salud pública, el ambiente y las instalaciones. Por esta razón, se les denomina roedores domésticos.</p>
<p>Pertenecen al orden Rodentia, familia Muridae, destacándose las siguientes especies:</p>
<ul class="lista-items">
    <li>Ratón (<em>Mus musculus</em>)</li>
    <li>Rata gris o de alcantarilla (<em>Rattus norvegicus</em>)</li>
    <li>Rata negra o de techo (<em>Rattus rattus</em>)</li>
</ul>
<p>Estos animales son portadores de enfermedades y parásitos, contaminan alimentos, deterioran materiales y generan daños estructurales, poniendo en riesgo la salud humana.</p>

<div class="subsection-title">Riesgos asociados a la presencia de roedores</div>
<ol class="lista-items" type="a">
    <li>Contaminación de alimentos mediante excrementos, orina, pelos y agentes patógenos.</li>
    <li>Deterioro de materiales de embalaje y almacenamiento.</li>
    <li>Daños en instalaciones al roer maderas, tuberías, estructuras y cables eléctricos.</li>
    <li>Transmisión directa e indirecta de enfermedades al ser humano, asociadas a más de 200 organismos patógenos, incluyendo virus, bacterias y parásitos.</li>
</ol>

<!-- 1.5 CONTROL DE ROEDORES -->
<div class="section-title">1.5 CONTROL DE ROEDORES</div>
<p>El control de roedores en <?= esc($nombreCliente) ?> deberá desarrollarse bajo un enfoque integral, combinando acciones preventivas, físicas y químicas, priorizando siempre las medidas de saneamiento básico y control ambiental.</p>

<div class="subsection-title">Control químico – Rodenticidas anticoagulantes de segunda y tercera generación</div>
<p>Se recomienda el uso de rodenticidas anticoagulantes de segunda y tercera generación, debido a que requieren una sola dosis para producir su efecto, reduciendo la probabilidad de generación de resistencia en las poblaciones de roedores.</p>
<p>Estos productos deberán cumplir con las siguientes características:</p>
<ul class="lista-items">
    <li>Ser biodegradables y de baja toxicidad para otras especies.</li>
    <li>Ser insípidos e inodoros.</li>
    <li>Permitir su fácil manipulación y dosificación en cebos.</li>
</ul>
<p>Su mecanismo de acción consiste en la inhibición de la coagulación sanguínea mediante el bloqueo de la vitamina K, lo que provoca hemorragias internas y la muerte del roedor entre los tres (3) y cuatro (4) días posteriores a la ingestión, sin generar rechazo al alimento.</p>
<p>En caso de intoxicación accidental, el antídoto correspondiente es Vitamina K, la cual deberá ser administrada bajo supervisión médica.</p>

<div class="subsection-title">Ubicación de las estaciones de cebado</div>
<ul class="lista-items">
    <li>Ubicar cebos en grupos de tres (3) a cuatro (4) unidades por estación.</li>
    <li>Mantener distancias máximas de cinco (5) metros entre estaciones en áreas abiertas.</li>
    <li>En zonas cerradas, ubicar estaciones en puntos altos y bajos, según evidencia de actividad.</li>
    <li>En áreas donde exista manipulación o consumo de alimentos, priorizar métodos no químicos como trampas adhesivas o jaulas de doble entrada.</li>
    <li>Instalar estaciones en perímetros externos para prevenir el ingreso de roedores desde áreas colindantes.</li>
</ul>

<div class="subsection-title">Cambio y rotación de productos</div>
<p>Para prevenir el desarrollo de resistencia, se deberá realizar rotación periódica de rodenticidas, alternando productos de segunda y tercera generación en cada jornada de desratización o según recomendación del proveedor especializado.</p>

<div class="subsection-title">Identificación de presencia de roedores</div>
<p>Los principales indicadores de infestación son:</p>
<ol class="lista-items" type="a">
    <li>Observación directa de roedores vivos o muertos.</li>
    <li>Presencia de excrementos.</li>
    <li>Huellas y rastros de desplazamiento.</li>
    <li>Roeduras recientes en materiales y estructuras.</li>
    <li>Materiales utilizados para la construcción de nidos.</li>
    <li>Agujeros, madrigueras o túneles activos.</li>
    <li>Olor fuerte y característico a orina.</li>
</ol>

<!-- 1.6 CONTROL DE INSECTOS -->
<div class="page-break"></div>
<div class="section-title">1.6 CONTROL DE INSECTOS RASTREROS Y VOLADORES</div>
<p>En <?= esc($nombreCliente) ?> se deberá implementar un control integrado de insectos rastreros y voladores, basado en la prevención, el saneamiento ambiental, el control físico y, cuando sea necesario, la aplicación controlada de productos químicos autorizados.</p>
<p>A continuación, se presenta una síntesis de los insectos rastreros y voladores de mayor incidencia, su clasificación, principales enfermedades asociadas y medidas preventivas.</p>

<div class="subsection-title">1.6.1 CUCARACHA</div>
<p><strong>Clasificación:</strong> Aproximadamente 4.000 especies – Orden Blattidae. Las especies más comunes son: <em>Blattella germanica</em>, <em>Periplaneta americana</em> y <em>Blatta orientalis</em>.</p>
<p><strong>Enfermedades asociadas:</strong></p>
<ul class="lista-items">
    <li>Salmonelosis</li>
    <li>Hepatitis</li>
    <li>Gastroenteritis</li>
    <li>Disentería</li>
    <li>Fiebre tifoidea</li>
</ul>
<p><strong>Medidas preventivas:</strong></p>
<ul class="lista-items">
    <li>No dejar desperdicios orgánicos expuestos.</li>
    <li>Eliminar la grasa acumulada en cocinas, cuartos de residuos y zonas comunes.</li>
    <li>Sellar grietas, hendiduras y juntas de baldosas.</li>
    <li>Revisar cajas, empaques y materiales que ingresen a áreas de almacenamiento o manipulación de alimentos.</li>
</ul>

<div class="subsection-title">1.6.2 MOSCA</div>
<p><strong>Clasificación:</strong> Aproximadamente 120.000 especies – Orden Diptera. La especie de mayor importancia sanitaria es la mosca doméstica (<em>Musca domestica</em>).</p>
<p><strong>Enfermedades asociadas:</strong></p>
<ul class="lista-items">
    <li>Cólera</li>
    <li>Disentería</li>
    <li>Fiebre tifoidea</li>
    <li>Tuberculosis</li>
</ul>
<p><strong>Medidas preventivas:</strong></p>
<ul class="lista-items">
    <li>No dejar residuos orgánicos expuestos (alimentos crudos, preparados o residuos sólidos).</li>
    <li>Mantener los recipientes de basura cerrados y con recolección frecuente.</li>
    <li>Garantizar limpieza diaria de superficies, pisos y áreas comunes.</li>
</ul>

<div class="subsection-title">1.6.3 MOSQUITO</div>
<p><strong>Clasificación:</strong> Aproximadamente 4.500 especies – Orden Diptera. Las especies de mayor riesgo sanitario son: <em>Aedes aegypti</em>, <em>Anopheles</em> y <em>Aedes albifasciatus</em>.</p>
<p><strong>Enfermedades asociadas:</strong></p>
<ul class="lista-items">
    <li>Dengue</li>
    <li>Malaria</li>
    <li>Fiebre amarilla</li>
    <li>Encefalitis equina</li>
    <li>Filariasis linfática</li>
</ul>
<p><strong>Medidas preventivas:</strong></p>
<ul class="lista-items">
    <li>Evitar la acumulación de aguas estancadas.</li>
    <li>Eliminar encharcamientos en zonas comunes y áreas verdes.</li>
    <li>No mantener recipientes, baldes, materas u objetos con agua acumulada.</li>
    <li>Realizar limpieza y drenaje periódico de sumideros y desagües.</li>
</ul>

<!-- 1.6.4 EVALUACIÓN INFESTACIÓN CUCARACHAS -->
<div class="subsection-title">1.6.4 EVALUACIÓN DEL GRADO DE INFESTACIÓN DE CUCARACHAS</div>
<p>Para determinar el grado de infestación de cucarachas, se deberán realizar inspecciones periódicas en las áreas de mayor riesgo de <?= esc($nombreCliente) ?>, tales como zonas de preparación o consumo de alimentos, áreas de almacenamiento, cuartos de residuos y demás áreas donde se manipulen o dispongan alimentos.</p>
<p>Las inspecciones deberán efectuarse utilizando linterna, especialmente en zonas de bajo tránsito, rincones, áreas ocultas o puntos donde se sospeche la presencia de infestación. De manera complementaria, se recomienda el uso de un espejo con mango, similar al empleado en odontología, con el fin de inspeccionar áreas de difícil acceso como:</p>
<ul class="lista-items">
    <li>Alrededores de tuberías de agua potable y desagüe.</li>
    <li>Drenajes.</li>
    <li>Conductos eléctricos.</li>
    <li>Fisuras, grietas y uniones en paredes y pisos.</li>
</ul>
<p>Se considerarán signos evidentes de infestación los siguientes:</p>
<ol class="lista-items" type="a">
    <li>Presencia de ejemplares vivos o muertos.</li>
    <li>Olor aceitoso fuerte, que puede estar acompañado de olor a moho en infestaciones severas.</li>
    <li>Presencia de bolitas de secreciones y excrementos, de aproximadamente 1 mm a 2 mm de ancho y de longitudes variables.</li>
</ol>

<div class="subsection-title">Signos adicionales de infestación de cucarachas</div>
<ol class="lista-items" type="a">
    <li><strong>Ootecas (bolsas de huevos):</strong> estructuras en forma de pequeñas cápsulas o esferas segmentadas, de color oscuro, con longitud aproximada entre 5 mm y 8 mm, superficie tersa y brillante.</li>
    <li><strong>Restos biológicos:</strong> presencia de tegumentos de ninfas vacíos, pelos, alas o fragmentos de insectos, evidenciando procesos de muda o actividad reproductiva.</li>
</ol>

<!-- 1.6.5 CONTROL QUÍMICO -->
<div class="page-break"></div>
<div class="subsection-title">1.6.5 CONTROL QUÍMICO – INSECTICIDAS</div>
<p>Muchos de los insectos vectores de enfermedades parasitarias se encuentran estrechamente vinculados a ecosistemas acuáticos, por lo cual el control efectivo debe priorizar la gestión ambiental, minimizando las condiciones favorables para su reproducción.</p>
<p>Las principales acciones de manejo ambiental incluyen:</p>
<ul class="lista-items">
    <li>Evitar mantener agua almacenada por más de un día.</li>
    <li>Prevenir encharcamientos dentro de las instalaciones y en zonas perimetrales.</li>
</ul>

<div class="subsection-title">1.6.6 PIRETROIDES Y ORGANOFOSFORADOS</div>
<p>Estos grupos de insecticidas son efectivos para el control de insectos voladores y rastreros, con un poder residual estimado entre cuatro (4) y seis (6) meses, permitiendo eliminar más de una generación de insectos.</p>
<p><strong>Características generales:</strong></p>
<ul class="lista-items">
    <li>Toxicidad moderada.</li>
    <li>Baja volatilidad.</li>
    <li>No corrosivos ni manchantes.</li>
    <li>Incoloros y estables durante su almacenamiento.</li>
    <li>Biodegradables y específicos (tóxicos únicamente para la especie objetivo).</li>
    <li>Actúan principalmente por contacto e ingestión.</li>
</ul>

<div class="subsection-title">1.6.7 INSECTICIDAS PIRETROIDES</div>
<p>Son insecticidas sintéticos de origen natural, derivados de la flor del crisantemo, modificados para mejorar su estabilidad ambiental. No cuentan con antídoto específico en caso de intoxicación.</p>

<div class="subsection-title">1.6.8 INSECTICIDAS ORGANOFOSFORADOS</div>
<p>Son ésteres del ácido fosfórico con alta liposolubilidad, lo que facilita su absorción en organismos vivos. Poseen baja presión de vapor y su principal vía de degradación ambiental es la hidrólisis, especialmente en condiciones alcalinas.</p>
<p>En caso de intoxicación accidental, el antídoto indicado es atropina.</p>

<div class="subsection-title">1.6.9 APLICACIÓN DE LOS PRODUCTOS</div>
<p>No se deberán realizar fumigaciones en condiciones meteorológicas adversas, especialmente en días lluviosos, ya que la humedad reduce la eficacia de los principios activos.</p>
<p>Los insecticidas deberán aplicarse mediante nebulización, utilizando máquinas termo-nebulizadoras, con el producto químico preparado conforme a ficha técnica y hoja de seguridad.</p>
<p>La Administración deberá informar previamente a los residentes y/o comerciantes sobre las fechas y horarios de aplicación.</p>

<div class="subsection-title">1.6.10 ROTACIÓN DE PRODUCTOS</div>
<p>Con el fin de evitar la resistencia o inmunidad de las plagas a los insecticidas, se deberá realizar rotación de principios activos, alternando piretroides y organofosforados en cada jornada de fumigación.</p>
<p>En ningún caso se deberá utilizar el mismo producto en aplicaciones consecutivas.</p>

<!-- 1.7 CONTROL DE PALOMAS -->
<div class="page-break"></div>
<div class="section-title">1.7 CONTROL DE PALOMAS</div>
<p>La paloma es una de las especies que mejor se ha adaptado al entorno urbano debido a la disponibilidad de alimento, la ausencia de depredadores naturales y la similitud del ambiente urbano con su hábitat natural.</p>
<p>En Colombia se registran treinta y nueve (39) especies de palomas y afines, de las cuales una (1) es endémica, nueve (9) son casi endémicas, tres (3) son migratorias boreales, dos (2) son introducidas y tres (3) se encuentran en peligro de extinción.</p>

<div class="subsection-title">Riesgos asociados a la presencia de palomas</div>
<ul class="lista-items">
    <li><strong>Riesgo ecológico:</strong> Crecimiento poblacional descontrolado debido a condiciones ambientales favorables.</li>
    <li><strong>Riesgo económico:</strong> Deterioro de edificaciones, mobiliario urbano, techos y vehículos por acumulación de heces, nidos, plumas y restos orgánicos.</li>
    <li><strong>Riesgo sanitario:</strong> Asociación con enfermedades respiratorias, como neumonías atípicas, y transmisión de ectoparásitos que afectan a personas y animales de compañía.</li>
</ul>
<p>La presencia excesiva de palomas puede deberse a:</p>
<ul class="lista-items">
    <li>Alimentación directa por parte de personas en áreas públicas cercanas.</li>
    <li>Proximidad de edificaciones abandonadas o en mal estado que favorecen la nidificación.</li>
</ul>
<p>Estas situaciones deberán ser notificadas a la Administración de <?= esc($nombreCliente) ?> y, de ser necesario, a la Secretaría de Salud o Alcaldía Local.</p>

<div class="subsection-title">Acciones preventivas para el control de palomas</div>
<ol class="lista-items" type="a">
    <li>Instalación de alambres tensados o monofilamentos en cornisas y salientes.</li>
    <li>Uso de dispositivos disuasivos no traumáticos para impedir el perchado.</li>
    <li>Instalación de espirales antiposamiento en bajantes y salientes.</li>
    <li>Implementación de sistemas mecánicos tipo "daddy", con o sin motor.</li>
    <li>Colocación de redes especiales para protección de fachadas y patios interiores.</li>
    <li>Uso de mallas para bloquear accesos a huecos, aleros y ventanucos.</li>
    <li>Aplicación profesional de geles repelentes.</li>
    <li>Uso controlado de dispositivos eléctricos o electromagnéticos disuasivos.</li>
    <li>Instalación de siluetas adhesivas en superficies acristaladas para evitar colisiones.</li>
</ol>
<p class="nota">No se considera efectivo el uso de bolsas plásticas, CDs, cintas u objetos similares. Queda estrictamente prohibido el uso de armas, venenos o agentes tóxicos.</p>

<!-- 1.8 PRECAUCIONES -->
<div class="section-title">1.8 PRECAUCIONES DE SEGURIDAD Y OBSERVACIONES GENERALES</div>
<ul class="lista-items">
    <li>Previo a la aplicación de productos, se deberá evacuar personas, animales y alimentos.</li>
    <li>Suspender actividades relacionadas con manipulación de alimentos.</li>
    <li>Uso obligatorio de Elementos de Protección Personal (EPP) durante toda la actividad.</li>
    <li>Informar a usuarios y residentes sobre las medidas antes, durante y después del control de plagas.</li>
    <li>Conocer y aplicar las hojas de seguridad (SDS) y fichas técnicas de los productos utilizados.</li>
    <li>Limpiar y desinfectar los EPP al finalizar la actividad.</li>
    <li>Disponer adecuadamente los residuos generados, conforme a la normatividad ambiental vigente.</li>
</ul>

<!-- 1.9 RESPONSABILIDADES -->
<div class="section-title">1.9 RESPONSABILIDADES</div>

<div class="definition-term">Administración de <?= esc($nombreCliente) ?></div>
<ul class="lista-items">
    <li>Garantizar la implementación, ejecución y actualización del Programa de Control Integral de Plagas.</li>
    <li>Contratar empresas legalmente constituidas, con personal capacitado, licencia sanitaria vigente y cumplimiento de la normatividad aplicable.</li>
    <li>Informar oportunamente a residentes, comerciantes y usuarios sobre fechas, horarios y medidas preventivas.</li>
    <li>Realizar inspecciones periódicas a áreas comunes y zonas críticas.</li>
    <li>Aplicar llamados de atención, medidas correctivas y sanciones conforme al reglamento interno.</li>
    <li>Custodiar registros, certificados, actas e informes derivados del programa.</li>
</ul>

<div class="definition-term">Empresa contratada de control de plagas</div>
<ul class="lista-items">
    <li>Ejecutar actividades conforme a la normatividad sanitaria, ambiental y SG-SST.</li>
    <li>Utilizar únicamente productos autorizados.</li>
    <li>Garantizar EPP y capacitación del personal.</li>
    <li>Entregar certificado y protocolo de cada intervención.</li>
    <li>Informar hallazgos relevantes.</li>
</ul>

<div class="definition-term">Residentes, locales y comerciantes</div>
<ul class="lista-items">
    <li>Cumplir medidas preventivas.</li>
    <li>Facilitar acceso durante inspecciones.</li>
    <li>Acatar recomendaciones.</li>
    <li>Reportar presencia de plagas.</li>
</ul>

<div class="definition-term">Responsable del SG-SST del Conjunto Residencial</div>
<ul class="lista-items">
    <li>Velar por el cumplimiento del cronograma.</li>
    <li>Coordinar con administración y empresa contratada.</li>
    <li>Hacer seguimiento a informes y certificados.</li>
</ul>

<!-- 1.10 FRECUENCIA -->
<div class="page-break"></div>
<div class="section-title">1.10 FRECUENCIA DE LOS SERVICIOS DE CONTROL PREVENTIVO DE PLAGAS</div>
<ul class="lista-items">
    <li><strong>Insectos rastreros y voladores:</strong> Semestral.</li>
    <li><strong>Roedores:</strong> Semestral con inspecciones mensuales de estaciones de cebado.</li>
    <li><strong>Palomas:</strong> Semestral o según condiciones.</li>
    <li><strong>Inspecciones sanitarias internas:</strong> Trimestral.</li>
</ul>
<p>La frecuencia podrá incrementarse según nivel de infestación o requerimiento de autoridad sanitaria.</p>

<!-- 1.11 SEGUIMIENTO -->
<div class="section-title">1.11 SEGUIMIENTO AL PROGRAMA DE CONTROL DE PLAGAS Y ROEDORES</div>
<p>Reuniones periódicas para verificar cumplimiento. Evaluación conjunta entre Administración, empresa contratada, consejo y SG-SST. Inspecciones mensuales a áreas comunes. Aplicación de medidas correctivas y sanciones si aplica. Verificación del cronograma por parte del Administrador.</p>
<p>La empresa contratada deberá entregar certificado y protocolo que contenga como mínimo:</p>
<ol class="lista-items" type="a">
    <li>Razón social y NIT.</li>
    <li>Fecha de ejecución.</li>
    <li>Evidencias y observaciones.</li>
    <li>Fichas técnicas y SDS.</li>
    <li>Permiso sanitario vigente.</li>
</ol>

<!-- 1.12 INDICADORES -->
<div class="section-title">1.12 INDICADORES</div>
<p>Con el fin de evaluar el cumplimiento del Programa de Control Integral de Plagas y Roedores en <?= esc($nombreCliente) ?>, se establecen los siguientes indicadores de ejecución mínima:</p>

<div class="subsection-title">Cumplimiento de fumigación semestral</div>
<div class="indicador-box">
    <table class="data-table" style="margin: 0;">
        <tr><td class="ind-label" style="width: 40%; background: #f0f0f0;">Nombre del indicador</td><td>Ejecución de fumigación semestral</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Fórmula</td><td>(Número de fumigaciones realizadas en el semestre / 1) × 100</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Tipo de indicador</td><td>Proceso</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Meta sugerida</td><td>100 %</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Periodicidad</td><td>Semestral</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Fuente de información</td><td>Certificados de fumigación emitidos por empresa contratada</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Responsable del seguimiento</td><td>Administración / Responsable SG-SST</td></tr>
    </table>
</div>
<p>Verifica que se haya realizado al menos una (1) fumigación contra insectos rastreros y voladores dentro de cada semestre. Si no existe certificado en el periodo evaluado, se considera incumplimiento.</p>

<div class="subsection-title">Cumplimiento de desratización semestral</div>
<div class="indicador-box">
    <table class="data-table" style="margin: 0;">
        <tr><td class="ind-label" style="width: 40%; background: #f0f0f0;">Nombre del indicador</td><td>Ejecución de desratización semestral</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Fórmula</td><td>(Número de desratizaciones realizadas en el semestre / 1) × 100</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Tipo de indicador</td><td>Proceso</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Meta sugerida</td><td>100 %</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Periodicidad</td><td>Semestral</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Fuente de información</td><td>Certificados de desratización emitidos por empresa contratada</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Responsable del seguimiento</td><td>Administración / Responsable SG-SST</td></tr>
    </table>
</div>
<p>Verifica que se haya realizado al menos una (1) jornada de desratización dentro de cada semestre. La ausencia de certificado implica incumplimiento.</p>

</body>
</html>
