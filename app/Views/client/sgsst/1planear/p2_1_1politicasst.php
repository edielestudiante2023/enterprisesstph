<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2.1.1 Política de Seguridad y Salud en el Trabajo</title>
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
            padding: 10px;
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





        .alpha-title {
            font-size: 1.5em;
            font-weight: bold;
            text-align: center;
            margin-top: 0px;
        }

        .beta-parrafo {
            margin-bottom: 0px;
            font-size: 0.9em;
            text-align: justify;
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

                    Fecha: <?= isset($latestVersion['sin_contrato']) && $latestVersion['sin_contrato'] ? '<span style="color: red; font-weight: bold;">PENDIENTE DE CONTRATO</span>' : strftime('%d de %B de %Y', strtotime($latestVersion['created_at'])); ?>

                </td>
            </tr>
        </table>
    </div>

    <div class="alpha-title">Política de Seguridad y Salud en el Trabajo</div>

    <p class="beta-parrafo">
        <strong><?= $client['nombre_cliente'] ?></strong>, nos comprometemos de manera prioritaria a salvaguardar la salud, la integridad física, social y psicológica de nuestros residentes, visitantes y contratistas. Esto se logra mediante la identificación, control y seguimiento de los factores de riesgo a los que pueden estar expuestos en nuestras instalaciones.
    </p>

    <div class="subtitle">Compromiso General</div>
    <p class="beta-parrafo">
        Reconocemos formalmente nuestro compromiso con la seguridad y salud en el trabajo como un valor fundamental. En este sentido, todos los niveles de nuestra copropiedad asumen la responsabilidad de crear y mantener entornos seguros y saludables. Cumpliremos con todos los requisitos legales aplicables en la implementación del sistema de gestión de seguridad y salud en el trabajo.
    </p>

    <div class="subtitle">Asignación de Recursos</div>
    <p class="beta-parrafo">
        Destinamos los recursos humanos, físicos, tecnológicos y financieros necesarios para llevar a cabo una gestión efectiva de la salud y seguridad en el trabajo. Contamos con el respaldo y la colaboración del vigía en Seguridad y Salud en el Trabajo, del consejo de administración y del administrador de la copropiedad. Trabajaremos en conjunto para optimizar los recursos presupuestados y alcanzar nuestras metas de seguridad y salud.
    </p>

    <div class="subtitle">Enfoque basado en Riesgos</div>
    <p class="beta-parrafo">
        Nuestros programas están diseñados para impulsar un enfoque basado en riesgos y el autocuidado. Nos dedicamos a la identificación y corrección proactiva de condiciones inseguras, con el propósito de reducir la incidencia de accidentes en nuestros residentes, visitantes y contratistas. Además, nos preparamos para responder de manera efectiva en situaciones de emergencia.
    </p>

    <div class="subtitle">Responsabilidad Compartida</div>
    <p class="beta-parrafo">
        Cada residente, visitante y contratista comparte la responsabilidad de cumplir con las normas y procedimientos de seguridad. Este compromiso es esencial para garantizar entornos seguros y productivos. Asimismo, se espera que notifiquen de inmediato cualquier condición que pueda generar consecuencias o contingencias, tanto para las personas asignadas a la copropiedad por parte de nuestros proveedores, como para prevenir daños o perjuicios a cualquier otra persona dentro de la propiedad horizontal.
    </p>

    <p class="beta-parrafo">
        En <strong><?= $client['nombre_cliente'] ?></strong>, mantenemos un firme compromiso con la seguridad y salud en el trabajo, y trabajamos en equipo para alcanzar nuestros objetivos en este ámbito. Esta política se revisará y actualizará regularmente para garantizar su eficacia y pertinencia.
    </p>

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
                    <td><?= isset($version['sin_contrato']) && $version['sin_contrato'] ? '<span style="color: red; font-weight: bold;">PENDIENTE DE CONTRATO</span>' : strftime('%d de %B de %Y', strtotime($version['created_at'])); ?></td>
                    <td><?= $version['change_control'] ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </footer>

    <br>

    <!--  <div>
        <a href="<?= base_url('/generatePdf_politicaSst') ?>" target="_blank">
            <button type="button">PDF</button>
        </a>
    </div> -->

</body>

</html>