<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2.5.0 Procedimiento de Reporte de Accidentes e Incidentes de Trabajo</title>
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
        <h3 class="gamma-titulo">1. INTRODUCCIÓN</h3>
        <p>
            La notificación e investigación de los accidentes de trabajo que se presenten eventualmente en <strong><?= $client['nombre_cliente'] ?></strong> es responsabilidad del personal asignado de manera permanente por nuestros contratistas o de manera eventual por nuestros proveedores. Estas actividades son fundamentales para lograr un mejoramiento continuo de las condiciones de salud, seguridad y medio ambiente de sus proveedores y contratistas.
        </p>
        <p>
            Es fundamental tener presente que, en el ámbito de las propiedades horizontales, donde no existen trabajadores directos ni son consideradas empresas en sí mismas, nuestra empresa, CYCLOID TALENT, se especializa en la gestión de la Seguridad y Salud en el Trabajo (SST). En este contexto, <strong><?= $client['nombre_cliente'] ?></strong> cuenta con proveedores que asignan talento humano de forma permanente y contratistas que destinan personal para actividades específicas.
        </p>
        <p>
            Dentro de este marco, la Resolución 1570 de 2005 establece las variables y mecanismos para la recolección de información en salud ocupacional y riesgos laborales. Asimismo, esta resolución impone la obligación de registrar de manera clara y completa el Formato Único de Reporte de Accidentes de Trabajo (FURAT).
        </p>
        <p>
            La importancia de este registro es tal que, en caso de faltantes en la información, el Ministerio correspondiente puede notificar por escrito a los empleadores, solicitando explicaciones. Esta situación podría conllevar sanciones para los empresarios, en forma de multas sucesivas mensuales de hasta 500 SMLMV. Adicionalmente, las Administradoras de Riesgos Laborales también están sujetas a sanciones si no solicitan la información faltante a los empleadores o no la reportan al Ministerio, pudiendo alcanzar multas de hasta 1000 SMLMV. En CYCLOID TALENT, nos comprometemos a garantizar la adecuada gestión de la información requerida, evitando posibles sanciones y contribuyendo al cumplimiento normativo en materia de SST en propiedades horizontales.
        </p>
        <p>
            <strong>Término:</strong> El artículo 62 del Decreto 1295 de 1994 establece para el empleador la obligatoriedad de reportar todo accidente de trabajo o enfermedad laboral que ocurra en una empresa o actividad económica dentro de los dos días hábiles siguientes a la ocurrencia del accidente o diagnóstico de la enfermedad.
        </p>

        <h3 class="gamma-titulo">2. DEFINICIÓN DE ACCIDENTE DE TRABAJO</h3>
        <p>
            Según el artículo 9 del decreto 1295 de 1994, el accidente de trabajo “es todo suceso repentino que sobrevenga por causa o con ocasión del trabajo y que produzca en el trabajador una lesión orgánica, una perturbación funcional, una invalidez o la muerte”.
        </p>
        <p>
            Igualmente, se considera accidente de trabajo aquel que se produce durante la ejecución de órdenes del empleador o durante la ejecución de una labor bajo su autoridad, aun fuera del lugar y horas del trabajo.
        </p>
        <p><strong>No es accidente de trabajo:</strong></p>
        <ul class="delta-lista">
            <li>El que se produzca por la ejecución de actividades diferentes para las que fue contratado el trabajador, tales como labores recreativas, deportivas o culturales.</li>
            <li>El sufrido por el trabajador, fuera de la empresa, durante los permisos remunerados o sin remuneración, incluidos los permisos sindicales.</li>
        </ul>

        <h3 class="gamma-titulo">3. PROCEDIMIENTO PARA LA NOTIFICACIÓN DEL PRESUNTO ACCIDENTE DE TRABAJO</h3>
        <p>
            En el contexto específico de <strong><?= $client['nombre_cliente'] ?></strong>, es esencial tener presente el artículo 62 del Decreto 1295 de 1994. Dicho artículo establece que "todo accidente de trabajo o enfermedad profesional que ocurra en una empresa o actividad deberá ser informado por el respectivo empleador a la entidad Administradora de Riesgos Laborales (ARL) y a la Entidad Promotora de Salud (EPS) en forma simultánea dentro de los dos días hábiles siguientes (48 horas) de ocurrido el accidente o enfermedad profesional".
        </p>
        <p>¿Qué hacer cuando ocurra un accidente de trabajo?</p>
        <ul class="delta-lista">
            <li>Prestar los primeros auxilios: Proporcionar los primeros auxilios al trabajador afectado si se dispone de los medios necesarios.</li>
            <li>Verificar si necesita atención médica: Evaluar la gravedad del incidente y, si es necesario, trasladarlo a la institución de salud más cercana.</li>
            <li>Comunicar el incidente al administrador de la copropiedad y al supervisor/líder para reporte correspondiente.</li>
            <li>Determinar si el accidente es de origen laboral.</li>
            <li>Contactar a la línea efectiva de la ARL del proveedor o contratista para solicitar orientación.</li>
            <li>Solicitar el diligenciamiento del Formato Único de Reporte de Accidentes de Trabajo (FURAT) y una copia del mismo por parte del proveedor o contratista.</li>
            <li>Confirmar el envío de la copia del FURAT a la EPS y la ARL.</li>
            <li>Monitorear el envío del original a la ARL del proveedor o contratista dentro de los plazos establecidos.</li>
            <li>Realizar una investigación del accidente junto con los líderes de SST de los contratistas o proveedores y de la propiedad horizontal para definir acciones correctivas.</li>
        </ul>

        <h3 class="gamma-titulo">4. BENEFICIOS</h3>
        <ul class="delta-lista">
            <li>Facilita la identificación de las causas durante la investigación.</li>
            <li>Evita obstáculos en la calificación profesional del evento.</li>
            <li>Entrega información al Ministerio para determinar políticas y programas.</li>
            <li>Previene posibles sanciones de los entes de control debido a información incompleta.</li>
            <li>Manifiesta el compromiso de los proveedores y contratistas con la seguridad de su personal.</li>
        </ul>
        <p>
            Tanto para la propiedad horizontal como para el talento humano asignado por parte de proveedores y contratistas, es fundamental colaborar en la investigación para identificar las causas, definir acciones preventivas y compartir aprendizajes que mejoren la seguridad laboral.
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

    <!--  <div>
        <a href="<?= base_url('/generatePdf_reporteAccidente') ?>" target="_blank">
            <button type="button">PDF</button>
        </a>
    </div> -->

</body>

</html>