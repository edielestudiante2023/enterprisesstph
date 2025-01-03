<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2.11.0 Procedimiento para las Funciones y Responsabilidades</title>
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
            max-width: 100px;
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
        .alfa-contenedor {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .gamma-titulo {
            font-size: 1.5em;
            font-weight: bold;

            margin-bottom: 20px;
        }

        .zeta-tabla {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .zeta-tabla th,
        .zeta-tabla td {
            border: 1px solid #000;
            padding: 10px;
            text-align: left;
        }

        .zeta-tabla th {
            background-color: #f2f2f2;
        }

        .delta-lista {
            list-style-type: none;
            padding-left: 0;
        }

        .delta-lista li::before {
            content: "• ";
            font-weight: bold;
        }

        .beta-subtitulo {
            font-size: 1.2em;
            font-weight: bold;
            margin-top: 20px;
        }

        p,
        li {
            text-align: justify;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        header {
            text-align: center;
            padding: 10px;
            background-color: #f4f4f4;
            border-bottom: 2px solid #ccc;
        }

        section {
            padding: 20px;
            max-width: 800px;
            margin: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        table,
        th,
        td {
            border: 1px solid #ccc;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }

        h1,
        h2 {
            color: #333;
        }

        p {
            margin: 10px 0;
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

    <div class="alfa-contenedor">
        <h3 class="gamma-titulo">Introducción</h3>

        <p>
            El Sistema de gestión en Seguridad y Salud en el trabajo de <b><?= $client['nombre_cliente'] ?></b>
            depende de un modelo de administración efectivo fundamentado bajo principios de calidad a partir del ciclo PHVA.
            Sin embargo, su desarrollo efectivo se alcanzará en la medida que la copropiedad logre una concepción clara de la importancia
            de éste en todos los riesgos que pueda tener la copropiedad y su responsabilidad contractual con contratistas, proveedores y residentes.
            Es por esto por lo que se plantean los siguientes niveles de participación, sus funciones y responsabilidades.
        </p>
        <h2>Roles y Responsabilidades</h2>
        <h2>Administrador</h2>
        <table>
            <thead>
                <tr>
                    <th>Responsabilidad</th>
                    <th>Descripción</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Gestión de Riesgos</td>
                    <td>El administrador debe asegurar la gestión de los riesgos aplicables a contratantes de personal bajo modalidad de contrato civil, comercial o administrativo, que presten sus servicios a la copropiedad, a fin de minimizar el riesgo legal.</td>
                </tr>
                <tr>
                    <td>Política de Seguridad y Salud en el Trabajo</td>
                    <td>Definir, firmar y divulgar la política de Seguridad y Salud en el Trabajo a través de documento escrito.</td>
                </tr>
                <tr>
                    <td>Asignación y Comunicación de Responsabilidades</td>
                    <td>Debe asignar, documentar y comunicar las responsabilidades específicas en Seguridad y Salud en el Trabajo SST a los residentes, consejo, comités de la copropiedad, contratistas, y prestadores de servicios.</td>
                </tr>
                <tr>
                    <td>Definición de Recursos</td>
                    <td>Asesora y orienta a la administración del conjunto y consejo de administración la estructura del gasto para la asignación de los recursos financieros, técnicos y el personal necesario para el diseño, implementación, revisión evaluación y mejora de las medidas de prevención y control, para la gestión eficaz de los peligros y riesgos en la copropiedad.</td>
                </tr>
                <tr>
                    <td>Cumplimiento de los Requisitos Normativos Aplicables</td>
                    <td>Debe garantizar que opera bajo el cumplimiento de la normatividad nacional vigente aplicable en materia de seguridad y salud en el trabajo.</td>
                </tr>
                <tr>
                    <td>Rendición de cuentas al interior de la copropiedad</td>
                    <td>A quienes se les hayan delegado responsabilidades en el Sistema de Gestión de la Seguridad y Salud en el Trabajo SG-SST, tienen la obligación de rendir cuentas internamente en relación con su desempeño. Esta rendición de cuentas se podrá hacer a través de medios escritos, electrónicos, verbales o los que sean considerados por los responsables. La rendición se hará como mínimo anualmente y deberá quedar documentada.</td>
                </tr>
                <tr>
                    <td>Gestión de los Peligros y Riesgos</td>
                    <td>Debe adoptar disposiciones efectivas para desarrollar las medidas de identificación de peligros, evaluación y valoración de los riesgos y establecimiento de controles que prevengan daños en la salud de los residentes y/o contratistas, en los equipos e instalaciones.</td>
                </tr>
                <tr>
                    <td>Plan de Trabajo Anual en SST</td>
                    <td>Debe diseñar y desarrollar un plan de trabajo anual para alcanzar cada uno de los objetivos propuestos en el Sistema de Gestión de la Seguridad y Salud en el Trabajo SG-SST, el cual debe identificar claramente metas, responsabilidades, recursos y cronograma de actividades.</td>
                </tr>
                <tr>
                    <td>Prevención y Promoción de Riesgos Laborales de contratistas y prestadores de servicios</td>
                    <td>La copropiedad debe implementar y desarrollar controles a sus contratistas y prestadores de servicio para la prevención de accidentes de trabajo y enfermedades laborales, así como de promoción de la salud en el Sistema de Gestión de la Seguridad y Salud en el Trabajo SG-SST.</td>
                </tr>
                <tr>
                    <td>Participación de los contratistas, prestadores de servicios, consejo y residentes</td>
                    <td>Debe asegurar la adopción de medidas eficaces que garanticen la participación de todos los actores de la copropiedad, vista como un sistema de naturaleza abierta que interactúa con otros sistemas, y sus representantes ante los residentes como son el consejo de administración y el vigía en Seguridad y Salud en el Trabajo. Estos representantes de la copropiedad frente al SG – SST deben participar en la ejecución de la política y funcionamiento del sistema, a fin de que cuenten con el tiempo y demás recursos necesarios, acorde con la normatividad vigente que les es aplicable.</td>
                </tr>
                <tr>
                    <td>Capacitación</td>
                    <td>Garantizar un programa de capacitación acorde con las necesidades específicas detectadas en la identificación de peligros, evaluación y valoración de riesgos. Garantizar un programa de inducción y entrenamiento para los contratistas y prestadores de servicio de la copropiedad, independientemente de su forma de vínculo contractual. La capacitación de nivel estratégico para consejo de administración y comités también es parte de la gestión del sistema al menos una vez al año.</td>
                </tr>
                <tr>
                    <td>Comunicación</td>
                    <td>Garantizar información oportuna sobre la gestión de la seguridad y salud en el trabajo y canales de comunicación que permitan recolectar información manifestada por los contratistas y el administrador de la copropiedad.</td>
                </tr>
                <tr>
                    <td>Identificación de Riesgos</td>
                    <td>Identificación de peligros, la evaluación y valoración de riesgos relacionados con las actividades de los contratistas, incluidas las disposiciones relativas a las situaciones de emergencia, dentro de la jornada laboral de la prestación del servicio de los contratistas.</td>
                </tr>
                <tr>
                    <td>Integración</td>
                    <td>La copropiedad debe involucrar los aspectos de Seguridad y Salud en el Trabajo frente a los demás procesos, procedimientos y decisiones en la copropiedad.</td>
                </tr>
            </tbody>
        </table>

        <h2>Responsable de SG-SST</h2>
        <table>
            <thead>
                <tr>
                    <th>Responsabilidad</th>
                    <th>Descripción</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Planear, organizar, dirigir, desarrollar</td>
                    <td>Planear, organizar, dirigir, desarrollar y aplicar el Sistema de Gestión de la Seguridad y Salud en el Trabajo SG-SST, y como mínimo una (1) vez al año, realizar su evaluación.</td>
                </tr>
                <tr>
                    <td>Informar al consejo y administrador</td>
                    <td>Informar al consejo y administrador sobre el funcionamiento y los resultados del Sistema de Gestión de la Seguridad y Salud en el Trabajo SG-SST.</td>
                </tr>
                <tr>
                    <td>Promover la participación</td>
                    <td>Promover la participación de todos los miembros del consejo de administración y del administrador en la implementación del SG-SST.</td>
                </tr>
                <tr>
                    <td>Identificación, evaluación y valoración de riesgos</td>
                    <td>Elaboración y actualización de la matriz de identificación de peligros, evaluación y valoración de riesgos y hacer la priorización para focalizar la intervención.</td>
                </tr>
                <tr>
                    <td>Comunicación</td>
                    <td>Promover la comprensión de las políticas en los contratistas y residentes. Informar sobre las necesidades de capacitación y entrenamiento en Seguridad y Salud en el Trabajo según los riesgos prioritarios y los niveles de la copropiedad.</td>
                </tr>
                <tr>
                    <td>Incidentes y accidentes</td>
                    <td>Participar en la investigación de los incidentes, accidentes de trabajo de los contratistas, adicionalmente con las enfermedades laborales (si aplica). Participar en la construcción, pero no en la ejecución de planes de acción, toda vez que esto depende del contratista.</td>
                </tr>
                <tr>
                    <td>Recursos</td>
                    <td>Gestionar los recursos para cumplir con el plan de Seguridad y Salud en el Trabajo y hacer seguimiento a los indicadores.</td>
                </tr>
                <tr>
                    <td>Participar</td>
                    <td>Participar de las reuniones con el administrador y el vigía de la seguridad y salud en el trabajo de acuerdo con el plan de trabajo.</td>
                </tr>
            </tbody>
        </table>

        <h2>Trabajadores (Contratistas)</h2>
        <table>
            <thead>
                <tr>
                    <th>Responsabilidad</th>
                    <th>Descripción</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Autocuidado</td>
                    <td>Procurar el cuidado integral de su salud.</td>
                </tr>
                <tr>
                    <td>Información</td>
                    <td>Suministrar información clara, veraz y completa sobre su estado de salud.</td>
                </tr>
                <tr>
                    <td>Cumplir</td>
                    <td>Cumplir las normas, reglamentos e instrucciones del Sistema de Gestión de la Seguridad y Salud en la copropiedad.</td>
                </tr>
                <tr>
                    <td>Comunicación</td>
                    <td>Informar oportunamente al administrador de la copropiedad y al contratante acerca de los peligros y riesgos latentes en su sitio de trabajo.</td>
                </tr>
                <tr>
                    <td>Capacitarse</td>
                    <td>Participar en las actividades de capacitación en seguridad y salud en el trabajo definidas en el plan de capacitación del SG-SST.</td>
                </tr>
                <tr>
                    <td>Participar y contribuir</td>
                    <td>Participar y contribuir al cumplimiento de los objetivos del Sistema de Gestión de la Seguridad y Salud en el Trabajo SG-SST.</td>
                </tr>
                <tr>
                    <td>Reporte</td>
                    <td>Reportar inmediatamente todo accidente o incidente de trabajo tanto a la empresa contratista como al administrador de la copropiedad.</td>
                </tr>
            </tbody>
        </table>
        <h2>Vigía en Seguridad y Salud en el Trabajo</h2>
        <table>
            <thead>
                <tr>
                    <th>Responsabilidad</th>
                    <th>Descripción</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Investigar</td>
                    <td>Accidentes, incidentes, y otras estadísticas de los contratistas de la copropiedad.</td>
                </tr>
                <tr>
                    <td>Capacitar</td>
                    <td>Participar en actividades de capacitación y retroalimentar al consejo de administración.</td>
                </tr>
                <tr>
                    <td>Coordinar</td>
                    <td>Entre consejo de administración, contratistas, y administrador de la copropiedad frente a temas inherentes a la Seguridad y Salud en el Trabajo.</td>
                </tr>
                <tr>
                    <td>Inspeccionar</td>
                    <td>Las instalaciones locativas, máquinas, equipos, herramientas, etc. Esta actividad es esencial para la prevención de accidentes de trabajo y enfermedades laborales, ya que permite detectar las causas y posibilita la reducción o eliminación del riesgo.</td>
                </tr>
                <tr>
                    <td>Vigilar</td>
                    <td>El cumplimiento del Sistema de Gestión de la Seguridad y la Salud en el Trabajo de la copropiedad. Así mismo podrá solicitar a contratistas el Reglamento de Higiene y Seguridad Industrial y las demás normas legales vigentes.</td>
                </tr>
                <tr>
                    <td>Informar</td>
                    <td>Informar al consejo de administración y al administrador de la copropiedad las inquietudes de los trabajadores de los contratistas y de los residentes de la copropiedad.</td>
                </tr>
            </tbody>
        </table>

        <h2>Brigadistas</h2>
        <table>
            <thead>
                <tr>
                    <th>Responsabilidad</th>
                    <th>Descripción</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Señalización y despeje de vías</td>
                    <td>Debe encargarse de señalizar y mantener siempre despejadas las vías de evacuación en caso de emergencia.</td>
                </tr>
                <tr>
                    <td>Dirección de la evacuación</td>
                    <td>Dirigir de manera ordenada y adecuada la evacuación en caso de emergencia.</td>
                </tr>
                <tr>
                    <td>Control del personal</td>
                    <td>Mantener siempre un control real y efectivo sobre el personal para evitar tumultos innecesarios y situaciones de pánico colectivo.</td>
                </tr>
                <tr>
                    <td>Rescate</td>
                    <td>Ejecutar de manera segura y técnica el rescate de las personas que requieran ser evacuadas, quienes se encuentren heridas o atrapadas.</td>
                </tr>
                <tr>
                    <td>Gestión de la evacuación</td>
                    <td>Debe gestionar la evacuación de la copropiedad, teniendo claro el manejo de rutas de evacuación, punto de encuentro, alarmas, conteo de residentes, manejo de elementos de evacuación como camillas y botiquines.</td>
                </tr>
                <tr>
                    <td>Diseño de planes y simulacros</td>
                    <td>Es necesario diseñar el plan a seguir en caso de emergencia, así como realizar los simulacros correspondientes de evacuación.</td>
                </tr>
                <tr>
                    <td>Capacitación</td>
                    <td>Capacitar e instruir a todo el personal de la copropiedad en el plan de evacuación vigente, el cual debe ser mejorado constantemente de acuerdo con la realidad de cada copropiedad.</td>
                </tr>
                <tr>
                    <td>Selección y capacitación de coordinadores</td>
                    <td>Deben seleccionar y capacitar a los coordinadores de evacuación, que deben ser personas adecuadas que cumplan el perfil que garantice una adecuada evacuación en caso de emergencia.</td>
                </tr>
                <tr>
                    <td>Atención inmediata</td>
                    <td>Atender inmediatamente en un lugar asegurado al trabajador afectado y lesionado.</td>
                </tr>
                <tr>
                    <td>Análisis de consecuencias</td>
                    <td>Analizar las consecuencias de una emergencia y clasificar al personal de acuerdo con la gravedad de sus lesiones a fin de brindar una mejor atención.</td>
                </tr>
                <tr>
                    <td>Optimización y preparación</td>
                    <td>Optimizar las condiciones actuales y preparar al personal considerado de urgencia para su pronta evacuación hacia un centro de atención especializado.</td>
                </tr>
            </tbody>
        </table>







        <h2>Conclusión</h2>
        <p>
            Este procedimiento detalla los roles y responsabilidades fundamentales para el Sistema de Gestión en Seguridad y Salud en el Trabajo,
            asegurando la participación y el cumplimiento de las normativas aplicables en la copropiedad.
        </p>
    </div>

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

    <br>

    <!-- <div>
        <a href="<?= base_url('/generatePdf_entregaDotacion') ?>" target="_blank">
            <button type="button">PDF</button>
        </a>
    </div> -->

</body>

</html>