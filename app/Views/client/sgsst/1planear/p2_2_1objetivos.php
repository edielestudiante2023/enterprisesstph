<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2.1.7 Objetivos del Sistema de Gestión de la Seguridad y Salud en el Trabajo (SG-SST)</title>
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


        .alpha-title {
            font-size: 1.5em;
            font-weight: bold;
            text-align: center;
            margin-top: 20px;
        }

        .beta-parrafo {
            margin-bottom: 10px;
            font-size: 1em;
        }

        .delta-lista {
            margin-left: 20px;
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

    <div class="alpha-title">OBJETIVOS DEL SISTEMA DE GESTIÓN DEL SEGURIDAD Y SALUD EN EL TRABAJO</div>

    <p class="beta-parrafo">
        <strong><?= $client['nombre_cliente'] ?></strong>, con el propósito de garantizar el cumplimiento de los planes de seguridad y salud, tanto para contratistas y proveedores que desempeñan sus funciones en nuestro conjunto residencial, como para otras personas expuestas a riesgos o accidentes, emite la presente declaración de objetivos. Se considera este enfoque como un núcleo fundamental para el desarrollo humano y laboral.
    </p>

    <p class="beta-parrafo">
        Esta prioridad se encuentra alineada con los estándares establecidos por la legislación colombiana en materia de Seguridad y Salud en el Trabajo, abarcando diversos aspectos relacionados con recursos, políticas y objetivos en esta área.
    </p>

    <p class="beta-parrafo">
        Con el firme propósito de cumplir con este compromiso, nos hemos planteado los siguientes objetivos estratégicos:
    </p>

    <ul class="delta-lista">
        <li class="beta-parrafo">Diseñar un sistema de gestión en seguridad y salud en el trabajo que promueva el bienestar de acuerdo con la normatividad vigente. Este sistema facilitará el control de los factores de riesgo inherentes al personal asignado por nuestros proveedores y contratistas, con el objetivo de prevenir accidentes y enfermedades laborales que puedan resultar en incapacidades, invalidez o incluso pérdida de vidas.</li>
        <li class="beta-parrafo">Identificar los factores de riesgo presentes en la copropiedad y aplicar medidas de control para mejorar las condiciones de trabajo y la salud del personal asignado por nuestros proveedores y contratistas.</li>
        <li class="beta-parrafo">Planificar, organizar y llevar a cabo eventos de instrucción y capacitación que sirvan como elementos integrales para fortalecer competencias en la prevención del riesgo, fomentando la participación de proveedores y contratistas.</li>
        <li class="beta-parrafo">Diseñar e implementar un plan de emergencias para estar preparados ante eventos inesperados que puedan poner en riesgo a las personas o la propiedad, capacitando a la brigada de emergencia.</li>
        <li class="beta-parrafo">Garantizar que todas las acciones y estrategias definidas en el plan de trabajo del Programa de Seguridad y Salud en el Trabajo se lleven a cabo de manera completa y oportuna.</li>
        <li class="beta-parrafo">Implementar mecanismos de supervisión para asegurar el desarrollo adecuado de las actividades programadas, verificando su conformidad con los lineamientos establecidos.</li>
        <li class="beta-parrafo">Identificar oportunidades de mejora en la ejecución del programa, realizando ajustes conforme sea necesario para optimizar su eficacia y adaptarlo a las condiciones cambiantes del entorno laboral.</li>
        <li class="beta-parrafo">Monitorizar de manera constante los indicadores de gestión asociados al Programa de Seguridad y Salud en el Trabajo, asegurando que se mantengan en niveles elevados de cumplimiento y proponiendo acciones correctivas en caso de desviaciones.</li>
    </ul>


    <div class="signature-container">
        <div class="signature">
            <img src="<?= base_url('uploads/' . $client['firma_representante_legal']) ?>" alt="Firma rep. legal">
            <div class="name"><b><?= $client['nombre_rep_legal'] ?></b></div>
            <div class="title">Representante Legal</div>
        </div>

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

    <!--     <div>
        <a href="<?= base_url('/generatePdf_oBjetivos') ?>" target="_blank">
            <button type="button">PDF</button>
        </a>
    </div> -->

</body>

</html>