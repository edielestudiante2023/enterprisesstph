<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2.1.6 Reglamento de Higiene y Seguridad Industrial</title>
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
            font-size: 1.1em;
            text-align: justify;
        }

        .gamma-section {
            margin-bottom: 20px;
            text-align: justify;
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

    <div class="alpha-title">Reglamento de Higiene y Seguridad Industrial</div> <br>

    <div class="gamma-section">
        <table width=100% border="1" cellpadding="10" cellspacing="0">
            <tr>
                <td><strong>EMPRESA:</strong></td>
                <td><strong><?= $client['nombre_cliente'] ?></strong></td>
            </tr>
            <tr>
                <td><strong>IDENTIFICACION:</strong></td>
                <td><?= $client['nit_cliente'] ?></td>
            </tr>
            <tr>
                <td><strong>DIRECCION:</strong></td>
                <td><?= $client['direccion_cliente'] ?></td>
            </tr>
            <tr>
                <td><strong>TELEFONO:</strong></td>
                <td><?= $client['telefono_1_cliente'] ?></td>
            </tr>
            <tr>
                <td><strong>CIUDAD:</strong></td>
                <td><?= $client['ciudad_cliente'] ?></td>
            </tr>
            <tr>
                <td><strong>SUCURSALES:</strong></td>
                <td><?= $clientPolicy['policy_content'] ?></td>
            </tr>
            <tr>
                <td><strong>CODIGO DE LA ACTIVIDAD ECONOMICA:</strong></td>
                <td><?= $client['codigo_actividad_economica'] ?></td>
            </tr>
        </table>

    </div>

    <div class="gamma-section">
        <p class="beta-parrafo"><strong>ARTÍCULO 1:</strong> La Propiedad Horizontal <strong><?= $client['nombre_cliente'] ?></strong> se compromete a cumplir rigurosamente con todas las disposiciones legales vigentes relacionadas con la Seguridad y Salud en el Trabajo (SG-SST) dentro del ámbito de una propiedad horizontal. Este compromiso se enmarca en las leyes y regulaciones colombianas, las cuales buscan garantizar los mecanismos que aseguren una prevención adecuada y oportuna de los accidentes de trabajo y enfermedades laborales.</p>

        <p class="beta-parrafo">Este compromiso se respalda y fundamenta en los siguientes dispositivos legales: Artículos 34, 57, 58, 205, 206, 217, 220, 221, 282, 283, 348, 349, 350 y 351 del Código Sustantivo del Trabajo, la Ley 9 de 1979, Resolución 2400 de 1979, Decreto 614 de 1984, Resolución 2013 de 1986, Decreto 1295 de 1994, Decreto 1772 de 1994, Ley 1010 de 2006, Resolución 1401 de 2007, Resolución 1918 de 2009, Resolución 2346 de 2007, Resolución 2646 de 2008, Resolución 1409 de 2012, Resolución 1356 de 2012, Resolución 652 de 2012, Ley 1562 de 2012, Resolución 1903 de 2013, Decreto 723 de 2013, Decreto 1477 de 2014, Decreto Único Reglamentario del Sector Trabajo 1072 de 2015, Decreto 1273 de 2018 y Resolución 0312 de 2019.</p>

        <p class="beta-parrafo">Cabe destacar que la Ley 962 de 2005, en su artículo 55, eliminó el requisito de revisión y aprobación del Reglamento por parte del Ministerio de Protección Social, así como cualquier otra normativa que pueda establecerse con dicho propósito.</p>
    </div>

    <div class="gamma-section">
        <p class="beta-parrafo"><strong>ARTÍCULO 2:</strong> La Propiedad Horizontal <strong><?= $client['nombre_cliente'] ?></strong> asume el compromiso de fomentar y garantizar la designación de un Vigía de Salud y Seguridad en el Trabajo, en estricta conformidad con las disposiciones establecidas por el Decreto 614 de 1984, la Resolución 2013 de 1986, la Resolución 1016 de 1989, el Decreto 1295 de 1994, el Decreto 1072 de 2015 y demás normativas aplicables.</p>

        <p class="beta-parrafo">Además, se compromete a impulsar y asegurar la formación y operación del Vigía quien es la figura del Comité Paritario de Seguridad y Salud en el Trabajo (COPASST), de acuerdo con lo establecido por el Decreto 614 de 1984, la Resolución 2013 de 1986, el Decreto 1295 de 1994, el Decreto 1771 de 1994, la Ley 1562 de 2012, el Decreto Único Reglamentario del Sector Trabajo 1072 de 2015, la Resolución 0312 de 2019 y cualquier otra normativa pertinente que pueda establecerse con ese propósito.</p>
    </div>

    <div class="gamma-section">
        <p class="beta-parrafo"><strong>ARTÍCULO 3:</strong> La Propiedad Horizontal <strong><?= $client['nombre_cliente'] ?></strong> se compromete a garantizar que los contratistas designados para llevar a cabo labores permanentes dentro de sus instalaciones implementen un Sistema de Gestión de Seguridad y Salud en el Trabajo (SGSST).</p>

        <p class="beta-parrafo">Dicho sistema deberá contemplar, como mínimo, los siguientes aspectos:</p>
        <ul class="delta-lista">
            <li class="beta-parrafo"><strong>Subprograma de Medicina Preventiva y del Trabajo:</strong> Fomentar y mantener el bienestar físico, mental y social de su personal.</li>
            <li class="beta-parrafo"><strong>Subprograma de Higiene y Seguridad Industrial:</strong> Establecer condiciones de saneamiento básico y controlar los factores de riesgo presentes en el entorno laboral.</li>
        </ul>
    </div>

    <div class="gamma-section">
        <p class="beta-parrafo"><strong>ARTÍCULO 4:</strong> La Propiedad Horizontal <strong><?= $client['nombre_cliente'] ?></strong> se compromete a asignar los recursos necesarios para llevar a cabo actividades programadas, en consonancia con el Sistema de Gestión de la Seguridad y Salud en el Trabajo.</p>

        <p class="beta-parrafo">Este sistema incluirá, como mínimo, los siguientes aspectos:</p>
        <ul class="delta-lista">
            <li class="beta-parrafo">Gestión Integral de Seguridad y Salud en el Trabajo</li>
            <li class="beta-parrafo">Gestión de Peligros y Riesgos</li>
            <li class="beta-parrafo">Verificación y Mejora Continua</li>
        </ul>
    </div>

    <div class="gamma-section">
        <p class="beta-parrafo"><strong>ARTÍCULO 5:</strong> Los riesgos presentes en la propiedad horizontal de <strong><?= $client['nombre_cliente'] ?></strong> incluyen riesgos biológicos, físicos, químicos, psicosociales, biomecánicos, mecánicos, eléctricos, locativos, tecnológicos, públicos, accidentes de tránsito y fenómenos naturales.</p>
    </div>

    <div class="gamma-section">
        <p class="beta-parrafo"><strong>ARTÍCULO 6:</strong> Los contratistas deberán cumplir estrictamente con las disposiciones legales, así como con las normas técnicas e internas que se adopten para lograr la implementación del SG-SST.</p>
    </div>

    <div class="gamma-section">
        <p class="beta-parrafo"><strong>ARTÍCULO 7:</strong> La propiedad horizontal llevará a cabo procesos de inducción y/o reinducción para contratistas, orientándolos en medidas de prevención y seguridad.</p>
    </div>

    <div class="gamma-section">
        <p class="beta-parrafo"><strong>ARTÍCULO 8:</strong> Este reglamento estará expuesto en lugares visibles y será comunicado a los trabajadores asignados.</p>
    </div>

    <div class="gamma-section">
        <p class="beta-parrafo"><strong>ARTÍCULO 9:</strong> El presente reglamento entrará en vigencia a partir de la fecha de aprobación por el administrador de <strong><?= $client['nombre_cliente'] ?></strong>.</p>
    </div>

    <div class="gamma-section">
        <p class="beta-parrafo"><strong>ARTÍCULO 10:</strong> Este reglamento, conforme al artículo 55 de la Ley 962 de 2005, no requiere aprobación formal del Ministerio de Trabajo.</p>
        <p class="beta-parrafo">Se expide en la fecha: <p>FECHA: <strong><?= strftime('%d de %B de %Y', strtotime($client['fecha_ingreso'])); ?></strong>.</p>
        <p class="beta-parrafo">Publíquese, comuníquese y cúmplase.</p>
    </div>


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

    <!-- <div>
        <a href="<?= base_url('/generatePdf_regHigsegind') ?>" target="_blank">
            <button type="button">PDF</button>
        </a>
    </div>
 -->
</body>

</html>