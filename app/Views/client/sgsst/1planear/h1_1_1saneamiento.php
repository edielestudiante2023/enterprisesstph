<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plan de Saneamiento Básico</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
            line-height: 1.6;
            background-color: white;
        }

        /* Estilos aplicados al footer */
        footer {
            text-align: center;
            margin-top: 50px;
            background-color: white;
            padding: 20px;
            border-top: 1px solid #ccc;
            font-size: 14px;
        }

        footer table {
            width: 100%;
            border-collapse: collapse;
        }

        footer th,
        footer td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }

        /* Estilos aplicados a la sección .centered-content */
        .centered-content {
            width: 100%;
            margin: 0 auto;
            padding: 0 0 20px 0;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .centered-content table {
            width: 100%;
            text-align: center;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid black;
            padding: 10px;
            text-align: left;
            height: 30px;
        }

        /* Estilos aplicados a las clases internas de la tabla */
        .logo {
            width: 20%;
            text-align: center;
        }

        .main-title {
            width: 50%;
            font-weight: bold;
            font-size: 14px;
            text-align: center;
        }

        .code {
            width: 30%;
            font-weight: bold;
            font-size: 14px;
        }

        .subtitle {
            font-weight: bold;
            font-size: 16px;
            text-align: center;
        }

        .right {
            text-align: left;
            padding-left: 10px;
        }

        .signature-container {
            display: flex;
            /* Ensures that the divs are displayed in a row */
            justify-content: space-evenly;
            /* Adds space between the items */
            align-items: center;
            /* Aligns the items vertically in the center */
            margin-top: 20px;
        }

        .signature {
            text-align: center;
            width: 90%;
            /* Adjust the width of each signature block */
        }

        .signature img {
            max-width: 200px;
            /* Adjust the size of the images as needed */
            height: auto;
        }

        .signature .name {
            font-weight: bold;
        }

        .signature .title {
            font-style: italic;
        }

        /* ***************************************************************************** */

        .alfa-title {
            font-size: 1.5em;
            font-weight: bold;

            margin-top: 20px;
        }

        .beta-titulo {
            font-size: 1.2em;
            font-weight: bold;
            margin-top: 15px;
        }

        .beta-parrafo {
            margin-bottom: 10px;
            text-align: justify;
        }

        .gamma-subtitulo {
            font-size: 1.1em;
            font-weight: bold;
            margin-top: 10px;
        }

        .delta-lista {
            margin-left: 20px;
        }

        .zeta-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .zeta-table,
        .zeta-th,
        .zeta-td {
            border: 1px solid black;
        }

        .zeta-th,
        .zeta-td {
            padding: 8px;
            text-align: left;
        }
    </style>


</head>

<body>
    <div class="centered-content">
        <table>
            <tr>
                <td rowspan="2" class="logo">
                    <img src="<?= base_url('uploads/' . $client['logo']) ?>" alt="Logo de <?= $client['nombre_cliente'] ?>" width="100%">
                </td>
                <td class="main-title">
                    SISTEMA DE GESTION EN SEGURIDAD Y SALUD EN EL TRABAJO
                </td>
                <td class="code">
                    <?= $latestVersion['document_type'] ?>-<?= $latestVersion['acronym'] ?>
                </td>
            </tr>
            <tr>
                <td class="subtitle">
                    <?= $policyType['type_name'] ?>
                </td>
                <td class="code right">
                    Versión: <?= $latestVersion['version_number'] ?><br>
                    <?php
                    setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'Spanish_Spain'); // Configura el idioma español
                    ?>

                    Fecha: <?= strftime('%d de %B de %Y', strtotime($latestVersion['created_at'])); ?>

                </td>
            </tr>
        </table>
    </div>

    <!-- ----------------------------------------------------------------------------------- -->


    <h1>PLAN DE SANEAMIENTO BÁSICO</h1>
    <p>
        El desarrollo con eficacia y eficiencia de este plan es responsabilidad directa del representante legal, estará disponible en medio escrito y magnético, a disposición de la autoridad sanitaria competente, e incluye los siguientes programas los cuales <strong><?= $client['nombre_cliente'] ?></strong> debe implementar (hacer) y desarrollar (ponerlo en práctica).
    </p>
    <ul>
        <li>Programa de Saneamiento Básico</li>
        <li>Programa de Manejo de los Desechos Sólidos y Líquidos</li>
        <li>Programa de Control de Plagas.</li>
        <li>Programa de Agua Potable.</li>
    </ul>
    <p>
        Estos programas incluyen una serie de normas o disposiciones, con el fin de mantener al establecimiento libre de posibles focos de contaminación, prevenir condiciones que pueden ser ofensivas a la población residente de la copropiedad y a sus contratistas, así como proporcionar espacios de trabajo y convivencia de manera limpia, saludable y segura, su acatamiento asegura una reducción en el riesgo de afectaciones a la salud, por ende, este plan de saneamiento básico ha sido diseñado y ejecutado para contribuir de manera significativa a mejorar la calidad de vida de los habitantes de una propiedad horizontal.
    </p>

    <h2>PLAN DE SANEAMIENTO</h2>
    <h3>INTRODUCCIÓN: PLAN DE SANEAMIENTO BÁSICO PARA PROPIEDAD HORIZONTAL</h3>
    <p>
        La salud y el bienestar de los residentes en una propiedad horizontal dependen en gran medida de la calidad del entorno en el que viven. La adopción de un enfoque integral hacia la gestión y mantenimiento de las áreas comunes es esencial para garantizar un ambiente limpio, seguro y saludable. Con el objetivo de salvaguardar la integridad sanitaria de nuestra comunidad y promover prácticas sostenibles, presentamos el "Plan de Saneamiento Básico para Propiedad Horizontal".
    </p>
    <p>
        Este documento establece las directrices fundamentales y los procedimientos específicos que orientarán las actividades de saneamiento en nuestra propiedad. Enfocado en cuatro pilares esenciales —limpieza y desinfección, manejo de desechos sólidos y líquidos, control de plagas y garantía de agua potable— el plan busca no solo cumplir con estándares normativos, sino también fomentar una cultura de responsabilidad ambiental y comunitaria.
    </p>
    <p>
        La implementación de este plan no solo tiene como objetivo prevenir la propagación de enfermedades y garantizar la seguridad de nuestras instalaciones, sino también fortalecer el sentido de pertenencia y colaboración entre los residentes. Al abordar de manera proactiva los aspectos clave de saneamiento, estamos construyendo un ambiente propicio para el desarrollo integral y la calidad de vida de todos los habitantes de nuestra propiedad horizontal.
    </p>
    <p>
        Este plan no es estático; evolucionará con las necesidades cambiantes de la comunidad y las actualizaciones normativas. Su éxito dependerá de la participación de cada residente y del compromiso continuo con las prácticas establecidas. A través de este documento, invitamos a todos los miembros de nuestra comunidad a unirse en la creación y mantenimiento de un entorno que refleje nuestro compromiso con la salud, la sostenibilidad y la convivencia armoniosa.
    </p>
    <p>
        Juntos, construyamos un hogar que no solo sea un refugio, sino un espacio que promueva el bienestar y la prosperidad para todos.
    </p>

    <h3>OBJETIVOS DEL PLAN DE SANEAMIENTO BÁSICO PARA PROPIEDAD HORIZONTAL</h3>
    <h4>OBJETIVO GENERAL:</h4>
    <p>
        Establecer un marco integral de saneamiento que garantice la salubridad, seguridad y calidad de vida en nuestra propiedad horizontal, previniendo la propagación de enfermedades, protegiendo el medio ambiente y promoviendo una convivencia armoniosa.
    </p>

    <h4>OBJETIVOS ESPECÍFICOS:</h4>
    <ul>
        <li><strong>Mantenimiento de Áreas Comunes:</strong> Implementar procedimientos efectivos de limpieza y desinfección en áreas comunes, asegurando un ambiente higiénico y seguro para todos los residentes.</li>
        <li><strong>Manejo Eficiente de Residuos:</strong> Establecer prácticas de manejo de residuos sólidos y líquidos que cumplan con normativas ambientales locales, evitando la contaminación y promoviendo la separación adecuada de desechos.</li>
        <li><strong>Control Sostenible de Plagas:</strong> Desarrollar un programa integral para prevenir y controlar la presencia de plagas, minimizando riesgos para la salud y protegiendo la infraestructura de la propiedad.</li>
        <li><strong>Garantía de Calidad del Agua:</strong> Asegurar la calidad del agua potable suministrada a la propiedad mediante monitoreo regular, mantenimiento de las instalaciones y cumplimiento de normativas de potabilidad.</li>
        <li><strong>Concientización y Participación Comunitaria:</strong> Fomentar la participación de los residentes mediante programas de educación sobre buenas prácticas de saneamiento, promoviendo la responsabilidad individual y colectiva.</li>
        <li><strong>Establecimiento de Procedimientos de Monitoreo:</strong> Desarrollar y mantener procedimientos efectivos de monitoreo, registro y control de los procesos de limpieza y desinfección, garantizando la consistencia y eficacia a lo largo del tiempo.</li>
        <li><strong>Cumplimiento Normativo:</strong> Cumplir con los estándares y regulaciones locales relacionados con saneamiento básico, asegurando la conformidad con las leyes y normativas vigentes.</li>
    </ul>
    <p>
        Este conjunto de objetivos refleja nuestro compromiso con la creación de un entorno seguro, limpio y sostenible. A través de la implementación de estos objetivos, aspiramos a fortalecer la calidad de vida de todos los residentes y a consolidar nuestra propiedad horizontal como un espacio ejemplar en términos de saneamiento y convivencia comunitaria.
    </p>

    <h3>ALCANCE DEL PROGRAMA DE SANEAMIENTO BÁSICO PARA PROPIEDAD HORIZONTAL</h3>
    <p>
        El alcance del programa abarca una serie de áreas y aspectos críticos en la propiedad horizontal, con el propósito de asegurar un entorno saludable, seguro y sostenible para todos los residentes. Este programa engloba las siguientes dimensiones:
    </p>
    <ul>
        <li><strong>Limpieza y Desinfección de Áreas Comunes:</strong> Incluye la implementación de procedimientos exhaustivos de limpieza y desinfección en áreas compartidas, como pasillos, escaleras, ascensores, vestíbulos y otras zonas de uso común.</li>
        <li><strong>Manejo Integral de Residuos:</strong> Cubre la gestión eficiente de residuos sólidos y líquidos, desde su generación hasta su disposición final, siguiendo prácticas ambientalmente responsables y cumpliendo con las normativas locales.</li>
        <li><strong>Control Sostenible de Plagas:</strong> Enfocado en la prevención y control de plagas, este componente abarca inspecciones periódicas, acciones preventivas, y la contratación de servicios profesionales según sea necesario.</li>
        <li><strong>Garantía de Calidad del Agua:</strong> Engloba la supervisión continua de la calidad del agua potable suministrada a la propiedad, la implementación de medidas preventivas para evitar contaminaciones, y el cumplimiento de estándares de potabilidad.</li>
        <li><strong>Concientización y Participación Comunitaria:</strong> Involucra actividades de educación y sensibilización para fomentar la participación de los residentes en la implementación y sostenibilidad de las prácticas de saneamiento.</li>
        <li><strong>Procedimientos de Monitoreo Continuo:</strong> Establece sistemas de monitoreo regulares, registros detallados y controles efectivos para evaluar y mantener la eficacia de los procesos de limpieza, desinfección y manejo de residuos.</li>
        <li><strong>Cumplimiento Normativo:</strong> Se asegura de que todas las prácticas de saneamiento estén alineadas con las leyes y regulaciones locales, garantizando así la conformidad con los estándares establecidos.</li>
    </ul>

    <h4>Cualidades de un Buen Producto de Limpieza:</h4>
    <ul>
        <li>Poder trabajar a muy bajas concentraciones.</li>
        <li>Gran afinidad con grasas y suciedades en las superficies a limpiar.</li>
        <li>Fuertemente hidrofílicos para mantener en suspensión en el agua las suciedades removidas.</li>
        <li>Buena solubilidad en el agua.</li>
        <li>Buen poder humectante, dispersante y emulsionante.</li>
        <li>Mínimamente corrosivo.</li>
        <li>Económico.</li>
        <li>Estable durante el almacenamiento.</li>
        <li>No forma grumos.</li>
        <li>Medible fácilmente.</li>
    </ul>


    <h1>MARCO TEÓRICO</h1>
    <h2>CRITERIOS PARA LA SELECCIÓN DE LOS PRODUCTOS PARA LA LIMPIEZA Y DESINFECCIÓN</h2>
    <p>Para seleccionar un producto se deben tener en cuenta las siguientes condiciones:</p>
    <ul>
        <li>La superficie para limpiar y desinfectar y la naturaleza de la mugre.</li>
        <li>El procedimiento adecuado previo y posterior al lavado.</li>
        <li>El restregado adecuado para eliminar toda la suciedad.</li>
        <li>Temperatura correcta del proceso.</li>
    </ul>

    <h3>Limpieza manual</h3>
    <p>La limpieza manual se aplica con la ayuda de una acción mecánica fuerte como el frotado o fregado con cepillo y otros elementos.</p>
    <h4>Ventajas:</h4>
    <ul>
        <li>Disminuye probabilidad de remoción de las incrustaciones.</li>
        <li>Útil cuando se desarman los equipos.</li>
    </ul>
    <h4>Desventajas:</h4>
    <ul>
        <li>Se emplea más tiempo.</li>
        <li>Su efectividad disminuye frente a otros sistemas de lavado y desinfección.</li>
        <li>Aumenta el gasto de agua, de productos y desinfectantes.</li>
        <li>Aumenta la mano de obra.</li>
    </ul>

    <h2>RECOMENDACIONES DE DOSIFICACIÓN PARA LA PREPARACIÓN DE LA SOLUCIÓN</h2>
    <h3>DESINFECTANTE CON HIPOCLORITO DE SODIO COMERCIAL (5.25%)</h3>
    <table border="1">
        <thead>
            <tr>
                <th>Elementos a Desinfectar</th>
                <th>Cantidad de Agua</th>
                <th>Cantidad de Desinfectante</th>
                <th>Tiempo de Acción</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Utensilios y Equipos</td>
                <td>1 litro</td>
                <td>2ml – 4ml</td>
                <td>5 – 10 min</td>
            </tr>
            <tr>
                <td>Superficies</td>
                <td>1 litro</td>
                <td>4ml</td>
                <td>10 – 15 min</td>
            </tr>
            <tr>
                <td>Paredes, techos y pisos</td>
                <td>1 litro</td>
                <td>4ml – 6ml</td>
                <td>10 – 15 min</td>
            </tr>
            <tr>
                <td>Baños</td>
                <td>1 litro</td>
                <td>7ml – 8ml</td>
                <td>10 – 15 min</td>
            </tr>
            <tr>
                <td>Uniformes</td>
                <td>1 litro</td>
                <td>4ml</td>
                <td>10 – 15 min</td>
            </tr>
            <tr>
                <td>Ambientes</td>
                <td>1 litro</td>
                <td>6ml</td>
                <td>-------------</td>
            </tr>
        </tbody>
    </table>

    <h2>GLOSARIO PARA EL PLAN DE SANEAMIENTO BÁSICO EN PROPIEDAD HORIZONTAL</h2>
    <dl>
        <dt><strong>Limpieza:</strong></dt>
        <dd>Es la acción de eliminar impurezas presentes en las superficies mediante el lavado y fregado con agua caliente, jabón o detergente adecuado.</dd>
        <dt><strong>Enjuague:</strong></dt>
        <dd>Proceso que implica la eliminación de detergentes, agentes químicos y otros productos utilizados en las operaciones de limpieza, higienización y desinfección.</dd>
        <dt><strong>Detergente:</strong></dt>
        <dd>Sustancia que facilita la separación de materias extrañas presentes en superficies sólidas cuando se emplea un disolvente, generalmente agua.</dd>
        <dt><strong>Desinfección:</strong></dt>
        <dd>Proceso que implica la destrucción de microorganismos infecciosos mediante la aplicación de agentes químicos o medios físicos.</dd>
        <dt><strong>Esterilización:</strong></dt>
        <dd>Destrucción de todos los microorganismos por medios químicos o físicos.</dd>
        <dt><strong>Desinfectante:</strong></dt>
        <dd>Agente químico que mata microorganismos en crecimiento, aunque no necesariamente sus formas resistentes, como esporas.</dd>
    </dl>

    <h1>PROGRAMA DE RESIDUOS SÓLIDOS</h1>
    <h2>INTRODUCCIÓN</h2>
    <p>
        En la búsqueda constante de promover comunidades sostenibles y ambientalmente responsables, nos complace presentar el Programa Integral de Manejo de Residuos para la Propiedad Horizontal. Reconociendo la importancia de cumplir con la legislación sanitaria vigente y en especial con los Decretos 605 de 1996, 1713 del 2002, 1140 del 2003, y Resolución 2184 de diciembre de 2019, así como con la Resolución 2674 de 2013 para la estructuración del Plan de Saneamiento, este programa se erige como una iniciativa clave para el bienestar de nuestra comunidad y la preservación del entorno.
    </p>
    <p>
        En consonancia con los principios de gestión integral de residuos, este programa se propone no solo cumplir con los requisitos normativos, sino también fomentar una cultura de responsabilidad ambiental entre los residentes de la propiedad horizontal. A través de la implementación de prácticas efectivas y la participación de cada miembro de la comunidad, aspiramos a alcanzar niveles óptimos de reducción, reutilización, reciclaje y disposición final segura de los residuos generados en nuestro entorno.
    </p>
    <p>
        Este programa aborda diferentes aspectos clave, desde la separación adecuada de residuos hasta la incorporación de estrategias innovadoras para la gestión de desechos peligrosos, hospitalarios, eléctricos y electrónicos.
    </p>
    <p>
        La colaboración de todos los residentes es esencial para el éxito de este programa. Invitamos a cada miembro de nuestra comunidad a comprometerse con la visión de un entorno más limpio, saludable y sostenible. Juntos, podemos construir un futuro donde el cuidado del medio ambiente sea parte integral de nuestro estilo de vida.
    </p>

    <h2>OBJETIVOS DEL PROGRAMA</h2>
    <h3>Objetivo General:</h3>
    <p>
        Implementar un Programa Integral de Manejo de Residuos en la propiedad horizontal que cumpla con la legislación sanitaria vigente y promueva prácticas sostenibles, fomentando la participación de la comunidad para lograr una gestión eficiente y responsable de los residuos.
    </p>
    <h3>Objetivos Específicos:</h3>
    <ul>
        <li>Optimizar la Separación en la Fuente: Garantizar la correcta separación de los residuos en origen, promoviendo la conciencia ambiental entre los residentes.</li>
        <li>Establecer Programas de Reciclaje y Compostaje: Desarrollar programas específicos de reciclaje para los diferentes tipos de materiales y fomentar el compostaje de residuos orgánicos.</li>
        <li>Gestionar Residuos Especiales de Forma Segura: Establecer protocolos adecuados para la gestión de residuos especiales, como electrónicos, hospitalarios y peligrosos.</li>
        <li>Elaborar un Plan de Saneamiento Básico: Estructurar y poner en marcha un Plan de Saneamiento Básico que contemple metas y programas concretos.</li>
        <li>Promover la Educación Ambiental: Diseñar programas de educación ambiental dirigidos a los residentes, sensibilizando sobre la importancia del manejo adecuado de residuos.</li>
    </ul>



    <h1>ALCANCE</h1>
    <h2>ALCANCE DEL PROGRAMA INTEGRAL DE MANEJO DE RESIDUOS PARA LA PROPIEDAD HORIZONTAL:</h2>
    <p>
        El Programa Integral de Manejo de Residuos para la Propiedad Horizontal abarcará todas las actividades relacionadas con la gestión de residuos sólidos en la comunidad, con el objetivo de cumplir con la legislación sanitaria vigente y promover prácticas ambientales sostenibles. El alcance del programa se detalla de la siguiente manera:
    </p>
    <ul>
        <li>
            <strong>Residuos Comunes:</strong> Incluye la implementación de estrategias para la separación en la fuente, recolección selectiva y disposición final adecuada de los residuos comunes generados por los residentes en la propiedad horizontal.
        </li>
        <li>
            <strong>Reciclaje y Compostaje:</strong> Comprende la creación y operación de programas específicos para el reciclaje de materiales como papel, cartón, plástico, vidrio y metal, así como la promoción y facilitación del compostaje de residuos orgánicos.
        </li>
        <li>
            <strong>Gestión de Residuos Especiales:</strong> Engloba la implementación de medidas para la gestión segura de residuos especiales, como los electrónicos, hospitalarios y peligrosos, abarcando la recolección diferenciada, el transporte seguro y la disposición final acorde con la normativa vigente.
        </li>
        <li>
            <strong>Plan de Saneamiento Básico:</strong> Involucra la estructuración y ejecución de un Plan de Saneamiento Básico adaptado a las necesidades específicas de la propiedad horizontal, incluyendo diagnósticos, metas, programas, participación comunitaria, infraestructura necesaria y mecanismos de seguimiento y evaluación.
        </li>
        <li>
            <strong>Capacitación y Educación Ambiental:</strong> Incorpora programas de capacitación y educación ambiental dirigidos a los residentes, con el propósito de promover la conciencia ambiental, la correcta separación de residuos, y el fomento de prácticas sostenibles en la propiedad horizontal.
        </li>
        <li>
            <strong>Colaboración Comunitaria:</strong> Propone la activa participación y colaboración de todos los residentes, administradores y personal de la propiedad horizontal en la ejecución de las estrategias del programa, buscando crear un compromiso colectivo para el cuidado del medio ambiente.
        </li>
    </ul>

    <h2>DEFINICIONES</h2>
    <dl>
        <dt><strong>Basura:</strong></dt>
        <dd>Todo residuo sólido o semisólido, putrescible o no putrescible, con excepción de excretas de origen humano o animal.</dd>
        <dt><strong>Desecho:</strong></dt>
        <dd>Cualquier producto deficiente, inservible o inutilizado que su poseedor destina al abandono o del cual quiere desprenderse.</dd>
        <dt><strong>Desperdicio:</strong></dt>
        <dd>Todo residuo sólido o semisólido de origen animal o vegetal, sujeto a putrefacción, proveniente de la manipulación, preparación y consumo de alimentos.</dd>
        <dt><strong>Disposición sanitaria de basuras:</strong></dt>
        <dd>El proceso mediante el cual las basuras son colocadas en forma definitiva, sea en el agua o en el suelo.</dd>
        <dt><strong>Enterramiento de basuras:</strong></dt>
        <dd>La técnica que consiste en colocarlas en una excavación, aislándolas posteriormente con tierra u otro material de cobertura.</dd>
        <dt><strong>Entidad de aseo:</strong></dt>
        <dd>La persona natural o jurídica, pública o privada, encargada o responsable en un municipio o distrito de la prestación del servicio de aseo.</dd>
        <dt><strong>Residuo sólido:</strong></dt>
        <dd>Todo objeto, sustancia o elemento en estado sólido, que se abandona, bota o rechaza, o puede ser reutilizable.</dd>
        <dt><strong>Residuo sólido comercial:</strong></dt>
        <dd>Aquel que es generado en establecimientos comerciales y mercantiles tales como almacenes, depósitos, hoteles, restaurantes, cafeterías y plazas de mercado.</dd>
        <dt><strong>Residuo sólido domiciliario:</strong></dt>
        <dd>El que por su naturaleza, composición, cantidad y volumen es generado en actividades realizadas en viviendas o en cualquier establecimiento asimilable a éstas.</dd>
        <dt><strong>Tratamiento:</strong></dt>
        <dd>El proceso de transformación física, química o biológica de los residuos sólidos para modificar sus características o aprovechar su potencial.</dd>
    </dl>

    <h2>MARCO NORMATIVO</h2>
    <ol>
        <li>
            <strong>Decreto 605 de 1996:</strong> Este decreto establece las normas para la gestión integral de residuos sólidos. Define los principios y objetivos para la minimización, aprovechamiento, reciclaje, tratamiento y disposición final de los residuos sólidos.
        </li>
        <li>
            <strong>Decreto 1713 del 2002:</strong> Reglamenta la gestión integral de residuos sólidos peligrosos en Colombia. Establece normas para la clasificación, almacenamiento, transporte, tratamiento y disposición final de estos residuos.
        </li>
        <li>
            <strong>Decreto 1140 del 2003:</strong> Reglamenta la gestión integral de residuos hospitalarios y similares. Establece normas para la clasificación, almacenamiento, transporte, tratamiento y disposición final de los residuos generados en establecimientos de salud.
        </li>
        <li>
            <strong>Resolución 2184 de diciembre de 2019:</strong> Establece los criterios y estándares para la gestión integral de residuos eléctricos y electrónicos en Colombia. Define los procedimientos para la recolección, transporte, tratamiento y disposición final de estos residuos.
        </li>
        <li>
            <strong>Resolución 2674 de 2013:</strong> Establece las pautas y los requisitos para la estructuración del Plan de Saneamiento Básico, con énfasis en la gestión integral de residuos sólidos. Incluye:
            <ul>
                <li><strong>Diagnóstico de la Situación Actual:</strong> Un análisis de la situación actual de la gestión de residuos sólidos.</li>
                <li><strong>Metas y Programas:</strong> Estrategias para la reducción en la fuente, reutilización, reciclaje y disposición final adecuada.</li>
                <li><strong>Participación Comunitaria:</strong> Fomento de la participación comunitaria en la gestión integral de residuos sólidos.</li>
                <li><strong>Infraestructura y Equipamiento:</strong> Definición de la infraestructura y el equipamiento necesario.</li>
                <li><strong>Financiación:</strong> Estrategias para financiar las actividades relacionadas con la gestión de residuos sólidos.</li>
                <li><strong>Seguimiento y Evaluación:</strong> Establecimiento de mecanismos de seguimiento y evaluación del plan.</li>
                <li><strong>Plazos:</strong> Plazos para la formulación y presentación del Plan de Saneamiento Básico.</li>
            </ul>
        </li>
    </ol>



    <h1>PROGRAMA DE CONTROL DE PLAGAS</h1>
    <h2>INTRODUCCIÓN</h2>
    <p>
        Nos complace iniciar un programa integral de control de plagas en nuestra propiedad horizontal, con el objetivo de promover un ambiente seguro, saludable y confortable para todos nosotros. Sabemos que la calidad de vida en nuestra comunidad es de suma importancia, y el control efectivo de plagas es esencial para mantener nuestros espacios habitables de manera óptima.
    </p>
    <p>
        Las plagas no solo pueden ser una molestia, sino que también representan una amenaza para la salud y la integridad estructural de nuestros hogares. En este sentido, hemos decidido implementar un programa exhaustivo que aborde no solo la erradicación de plagas existentes, sino también estrategias preventivas para evitar su retorno.
    </p>
    <p>
        Este programa se basa en las normativas locales y nacionales, incluyendo la Resolución 2674 de 2013 en Colombia, que establece pautas para la gestión integral de residuos sólidos, un componente clave en el control de plagas. Reconocemos la importancia de la participación de todos los residentes en este esfuerzo colectivo, y alentamos su colaboración para lograr un entorno residencial más saludable y sostenible.
    </p>
    <p>
        A lo largo de este proceso, se llevarán a cabo evaluaciones periódicas, se establecerán metas claras y se implementarán medidas preventivas para garantizar que nuestra propiedad se mantenga libre de plagas de manera continua. La transparencia y la comunicación abierta serán fundamentales para el éxito de este programa, y estaremos encantados de recibir cualquier comentario o sugerencia que puedan tener.
    </p>

    <h2>OBJETIVOS DEL PROGRAMA</h2>
    <h3>Objetivo General:</h3>
    <p>
        Implementar un programa de control de plagas en la propiedad horizontal con el fin de salvaguardar la salud, el bienestar y la integridad de los residentes, promoviendo un entorno habitable libre de amenazas asociadas a la presencia de plagas.
    </p>
    <h3>Objetivos Específicos:</h3>
    <ul>
        <li><strong>Erradicación y Control Efectivo:</strong> Ejecutar acciones inmediatas para la erradicación de plagas existentes, implementando métodos efectivos y seguros que minimicen los riesgos para la salud y el medio ambiente.</li>
        <li><strong>Prevención y Monitoreo Continuo:</strong> Establecer un sistema de monitoreo regular para detectar tempranamente la presencia de plagas, permitiendo la aplicación oportuna de medidas preventivas. Esto incluirá inspecciones periódicas y la identificación de posibles puntos de entrada.</li>
        <li><strong>Promoción de Prácticas Sostenibles:</strong> Fomentar entre los residentes prácticas cotidianas que contribuyan a la prevención de plagas, como el manejo adecuado de residuos sólidos, la limpieza constante y la eliminación de posibles criaderos. Se realizarán campañas de concientización y capacitación para garantizar la participación de la comunidad.</li>
        <li><strong>Colaboración Comunitaria:</strong> Establecer un canal efectivo de comunicación entre la administración y los residentes, promoviendo la colaboración y la retroalimentación. Se buscará la participación de la comunidad en la identificación de posibles problemas y en la implementación de soluciones, fortaleciendo así la responsabilidad compartida en el control de plagas.</li>
        <li><strong>Cumplimiento Normativo:</strong> Asegurar que todas las acciones realizadas en el marco del programa cumplan con las normativas locales y nacionales, en especial aquellas establecidas en la Resolución 2674 de 2013 en Colombia, para la gestión integral de residuos sólidos y el control de plagas. Se llevará a cabo una revisión periódica para garantizar la alineación continua con las regulaciones vigentes.</li>
    </ul>

    <h2>ALCANCE</h2>
    <p>
        El programa de control de plagas en la propiedad horizontal abarcará una serie de acciones y estrategias destinadas a garantizar un entorno residencial saludable, seguro y libre de amenazas asociadas a la presencia de plagas. El alcance del programa incluirá, pero no se limitará, a las siguientes áreas:
    </p>
    <ol>
        <li>
            <strong>Erradicación y Control:</strong>
            <ul>
                <li>Identificación y tratamiento de plagas existentes, utilizando métodos seguros y efectivos.</li>
                <li>Implementación de medidas de control que minimicen el impacto ambiental y protejan la salud de los residentes.</li>
            </ul>
        </li>
        <li>
            <strong>Prevención y Monitoreo Continuo:</strong>
            <ul>
                <li>Establecimiento de un sistema de monitoreo periódico para la detección temprana de plagas.</li>
                <li>Inspecciones regulares para identificar y abordar posibles puntos de entrada y áreas propensas a la proliferación de plagas.</li>
            </ul>
        </li>
        <li>
            <strong>Promoción de Prácticas Sostenibles:</strong>
            <ul>
                <li>Desarrollo y ejecución de campañas de concientización sobre prácticas cotidianas que contribuyan a la prevención de plagas.</li>
                <li>Capacitación a los residentes sobre el manejo adecuado de residuos sólidos, la limpieza regular y la eliminación de posibles criaderos.</li>
            </ul>
        </li>
        <li>
            <strong>Colaboración Comunitaria:</strong>
            <ul>
                <li>Establecimiento de canales de comunicación efectivos para promover la colaboración entre la administración y los residentes.</li>
                <li>Fomento de la participación de la comunidad en la identificación de problemas, implementación de soluciones y reporte de posibles incidencias de plagas.</li>
            </ul>
        </li>
        <li>
            <strong>Cumplimiento Normativo:</strong>
            <ul>
                <li>Aseguramiento de que todas las acciones y estrategias estén en plena conformidad con las normativas locales y nacionales, incluyendo la Resolución 2674 de 2013 en Colombia.</li>
                <li>Revisión periódica del programa para garantizar la alineación continua con las regulaciones vigentes.</li>
            </ul>
        </li>
        <li>
            <strong>Seguimiento y Evaluación:</strong>
            <ul>
                <li>Establecimiento de un sistema de seguimiento y evaluación para medir la eficacia de las acciones implementadas.</li>
                <li>Ajustes continuos en el programa según sea necesario, con base en los resultados de las evaluaciones y el feedback de la comunidad.</li>
            </ul>
        </li>
    </ol>

    <h2>GLOSARIO</h2>
    <dl>
        <dt><strong>Desratización:</strong></dt>
        <dd>Tiene como objetivo el control de los roedores (ratas y ratones) dentro y fuera de las instalaciones. Se fundamenta en la prevención, impidiendo que los roedores penetren, vivan o proliferen en los locales o instalaciones.</dd>
        <dt><strong>Fumigación:</strong></dt>
        <dd>Método de control químico de plagas.</dd>
        <dt><strong>Infección:</strong></dt>
        <dd>Es la presencia de virus, bacterias dentro de un determinado cuerpo.</dd>
        <dt><strong>Infestación:</strong></dt>
        <dd>Es la presencia y multiplicación de plagas que pueden contaminar o deteriorar los alimentos y/o las materias primas.</dd>
        <dt><strong>Medida preventiva:</strong></dt>
        <dd>Son todas aquellas actividades encaminadas a reducir la probabilidad de aparición de un suceso no deseado.</dd>
        <dt><strong>Plaga:</strong></dt>
        <dd>Numerosas especies de plantas o animales indeseables que pueden contaminar o deteriorar los alimentos y/o las materias primas.</dd>
        <dt><strong>Plaguicida:</strong></dt>
        <dd>Cualquier sustancia o mezcla de sustancias destinadas a prevenir o controlar toda especie de plantas o animales indeseables.</dd>
        <dt><strong>Roedor:</strong></dt>
        <dd>Constituyen el orden más numeroso de los mamíferos, dotados de incisivos largos y fuertes de crecimiento continuo.</dd>
        <dt><strong>Vector:</strong></dt>
        <dd>Artrópodo u otro invertebrado que transmite infecciones por inoculación en piel y/o mucosas o por siembra de microorganismos.</dd>
        <dt><strong>Zoonosis:</strong></dt>
        <dd>Enfermedades transmisibles en común al hombre y a los animales.</dd>
    </dl>


    <h1>PROGRAMA DE ABASTECIMIENTO DE AGUA</h1>
    <h2>INTRODUCCIÓN AL PROGRAMA DE ABASTECIMIENTO DE AGUA PARA LA PROPIEDAD HORIZONTAL</h2>
    <p>
        La provisión sostenible y eficiente de agua es un pilar fundamental para el bienestar y la calidad de vida en nuestra comunidad de propiedad horizontal. En este contexto, presentamos un programa integral de abastecimiento de agua diseñado para satisfacer las necesidades específicas de nuestros residentes, promoviendo un uso responsable y consciente de este recurso vital.
    </p>
    <p>
        La planificación cuidadosa de la infraestructura hídrica es esencial para garantizar un suministro constante y de alta calidad, considerando tanto las demandas diarias como las fluctuaciones estacionales. Nuestro enfoque se centra en la optimización de recursos, la implementación de tecnologías eficientes y la adopción de prácticas que fomenten la conservación del agua.
    </p>
    <p>
        Además, reconocemos la importancia de la transparencia y la equidad en la gestión del agua. Por ello, incorporamos contadores individuales para cada unidad residencial, permitiendo una medición precisa del consumo y una distribución justa de costos.
    </p>
    <p>
        Este programa no solo busca establecer una infraestructura robusta y confiable, sino también fomentar una cultura comunitaria en la que todos los residentes participen activamente en la preservación de nuestro recurso hídrico. La educación sobre prácticas de uso eficiente, la detección temprana de posibles fugas y la colaboración en iniciativas de conservación son aspectos clave que promovemos.
    </p>
    <p>
        Asimismo, estamos comprometidos con el cumplimiento de todas las regulaciones locales y la obtención de los permisos necesarios para asegurar la legalidad y sostenibilidad de nuestro programa.
    </p>
    <p>
        A través de este programa de abastecimiento de agua, aspiramos a crear una comunidad responsable, consciente y resiliente, donde el acceso al agua sea confiable, equitativo y en armonía con el entorno. Invitamos a todos los residentes a participar activamente en este esfuerzo colectivo para preservar y mejorar nuestro entorno y calidad de vida.
    </p>

    <h2>OBJETIVO</h2>
    <h3>Objetivo General:</h3>
    <p>
        Garantizar un suministro continuo y una disponibilidad constante de agua para todos los residentes de la propiedad horizontal, promoviendo un uso eficiente y consciente del recurso hídrico.
    </p>
    <h3>Objetivos Específicos:</h3>
    <ul>
        <li>
            <strong>Optimización del Uso del Agua:</strong>
            <ul>
                <li>Fomentar prácticas de uso eficiente del agua entre los residentes mediante campañas educativas y materiales informativos.</li>
                <li>Implementar medidas sencillas, como la reparación de fugas y la instalación de dispositivos de ahorro, para minimizar las pérdidas y optimizar el consumo.</li>
            </ul>
        </li>
        <li>
            <strong>Monitoreo y Detección Temprana de Problemas:</strong>
            <ul>
                <li>Establecer un sistema de monitoreo regular para identificar y abordar de manera proactiva posibles problemas en la red de suministro.</li>
                <li>Incentivar a los residentes a informar sobre fugas o irregularidades para una respuesta rápida y eficaz.</li>
            </ul>
        </li>
    </ul>

    <h2>ALCANCE</h2>
    <p>
        El programa se enfoca en establecer prácticas y medidas que aseguren un suministro constante de agua para todos los residentes de la propiedad horizontal, sin incurrir en costos significativos o inversiones adicionales. Las acciones incluidas en el alcance se dividen en las siguientes áreas:
    </p>
    <ol>
        <li>
            <strong>Optimización del Uso del Agua:</strong>
            <ul>
                <li>Desarrollar campañas educativas para concientizar a los residentes sobre prácticas de uso eficiente del agua.</li>
                <li>Proporcionar materiales informativos y comunicados regulares que destaquen la importancia del ahorro de agua.</li>
                <li>Realizar inspecciones periódicas para identificar y reparar fugas menores en las áreas comunes y en las unidades residenciales.</li>
            </ul>
        </li>
        <li>
            <strong>Implementación de Medidas de Ahorro:</strong>
            <ul>
                <li>Instalar dispositivos de ahorro de agua, como grifos y cabezales de ducha de bajo flujo, en áreas comunes y unidades residenciales, promoviendo un consumo responsable.</li>
                <li>Realizar ajustes y reparaciones en la red de suministro para minimizar pérdidas y garantizar una distribución eficiente.</li>
            </ul>
        </li>
        <li>
            <strong>Monitoreo y Detección de Problemas:</strong>
            <ul>
                <li>Establecer un sistema de monitoreo continuo para evaluar el rendimiento del sistema de abastecimiento de agua.</li>
                <li>Incentivar a los residentes a informar sobre posibles fugas o problemas en el suministro para una intervención rápida.</li>
                <li>Desarrollar un protocolo de respuesta para abordar de manera eficaz cualquier irregularidad identificado.</li>
            </ul>
        </li>
        <li>
            <strong>Evaluación de Resultados:</strong>
            <ul>
                <li>Realizar revisiones periódicas para evaluar la efectividad de las medidas implementadas.</li>
                <li>Recopilar datos sobre el consumo de agua y las mejoras en la eficiencia del sistema.</li>
                <li>Ajustar estrategias según sea necesario para optimizar continuamente el programa.</li>
            </ul>
        </li>
        <li>
            <strong>Participación Comunitaria:</strong>
            <ul>
                <li>Fomentar la participación de los residentes a través de reuniones informativas y actividades comunitarias.</li>
                <li>Establecer canales de comunicación para recibir retroalimentación y sugerencias de mejora por parte de la comunidad.</li>
            </ul>
        </li>
    </ol>

    <h2>FUENTES DE AGUA</h2>
    <p>
        La fuente de agua principal para la propiedad horizontal es el acueducto de Bogotá, que se utiliza para todos los fines previstos. Es esencial destacar que, para aquellas actividades relacionadas con la limpieza, desinfección y procesamiento de alimentos que estén en contacto directo con los mismos, se utilizará exclusivamente agua potable. Esta medida garantiza la seguridad y la salud de los residentes, cumpliendo con los estándares establecidos para el uso adecuado del recurso hídrico.
    </p>

    <h2>TRATAMIENTOS DE POTABILIZACIÓN</h2>
    <p>
        Dado que la fuente de agua proviene del acueducto de Bogotá, es imperativo asegurar que el agua en la propiedad cumpla con los requisitos establecidos en la resolución 2115 de 2007. Este proceso de potabilización se llevará a cabo de manera continua para garantizar que el agua cumpla con los estándares de calidad y seguridad establecidos por las autoridades competentes.
    </p>

    <h2>SISTEMAS DE ALMACENAMIENTO</h2>
    <p>
        Para asegurar un suministro constante y adecuado, se implementará un sistema de almacenamiento de agua con capacidad suficiente para satisfacer, como mínimo, las necesidades correspondientes de la propiedad horizontal. La presión del agua se ajustará para garantizar un rendimiento óptimo en todas las operaciones diarias.
    </p>
    <p>
        Adicionalmente, se establecerá un protocolo de limpieza y desinfección de los sistemas de almacenamiento cada 6 meses. Este proceso será llevado a cabo por una empresa especializada contratada para asegurar la calidad del agua y prevenir la acumulación de sedimentos o contaminantes en el sistema.
    </p>
    <p>
        Este enfoque integral en la fuente de agua, tratamientos de potabilización y sistemas de almacenamiento busca no solo cumplir con los estándares regulatorios sino también garantizar un suministro confiable y seguro para todas las actividades dentro de la propiedad horizontal.
    </p>



    <footer>
        <h2>Historial de Versiones</h2>
        <style>
            footer table {
                width: 100%;
                border-collapse: collapse;
                table-layout: fixed;
            }

            footer table th,
            footer table td {
                border: 1px solid #ddd;
                text-align: center;
                vertical-align: middle;
                padding: 8px;
                word-wrap: break-word;
            }

            footer table th {
                background-color: #f4f4f4;
                font-weight: bold;
            }

            footer table tr:nth-child(even) {
                background-color: #f9f9f9;
            }

            footer table tr:hover {
                background-color: #f1f1f1;
            }

            /* Ajuste del ancho de las columnas */
            footer table th:nth-child(5),
            footer table td:nth-child(5) {
                width: 35%;
                /* Más ancho para la columna Observaciones */
            }

            footer table th:nth-child(1),
            footer table td:nth-child(1) {
                width: 10%;
                /* Más estrecho para la columna Versión */
            }

            footer table th:nth-child(2),
            footer table td:nth-child(2),
            footer table th:nth-child(3),
            footer table td:nth-child(3),
            footer table th:nth-child(4),
            footer table td:nth-child(4) {
                width: 15%;
                /* Ancho uniforme para las demás columnas */
            }
        </style>
        <table>
            <tr>
                <th>Versión</th>
                <th>Tipo de Documento</th>
                <th>Acrónimo</th>
                <th>Fecha de Creación</th>
                <th>Observaciones</th>
            </tr>
            <?php foreach ($allVersions as $version): ?>
                <tr>
                    <td><?= $version['version_number'] ?></td>
                    <td><?= $version['document_type'] ?></td>
                    <td><?= $version['acronym'] ?></td>
                    <td><?= strftime('%d de %B de %Y', strtotime($version['created_at'])); ?></td>
                    <td><?= $version['change_control'] ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </footer>


</body>

</html>