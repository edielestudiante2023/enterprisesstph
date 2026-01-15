<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>1.2.2 Programa de Inducción y Reinducción</title>
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

        /* ********************ESTILOS DEL FORMATO************************************* */
        .beta-subtitulo {
            font-size: 1.5em;
            font-weight: bold;
            margin-top: 20px;
        }

        .gamma-parrafo {
            font-size: 1em;
            margin-bottom: 10px;
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

                    Fecha: <?= isset($latestVersion['sin_contrato']) && $latestVersion['sin_contrato'] ? '<span style="color: red; font-weight: bold;">PENDIENTE DE CONTRATO</span>' : strftime('%d de %B de %Y', strtotime($latestVersion['created_at'])); ?>

                </td>
            </tr>
        </table>
    </div>

    <div style="text-align: center;" class="beta-subtitulo">INDUCCIÓN Y REINDUCCIÓN DE SEGURIDAD Y SALUD EN EL TRABAJO</div>

    <div class="beta-subtitulo">1. OBJETIVO</div>
    <p class="gamma-parrafo">
        Facilitar a las partes interesadas de <strong><?= $client['nombre_cliente'] ?></strong> el conocimiento técnico básico y los fundamentos normativos frente al Sistema de Gestión en Seguridad y Salud en el Trabajo (SG-SST), mediante información de los objetivos generales, metas actuales, reglamentaciones y procedimientos, valores y características especiales, con una mirada técnica en las ciencias de la salud ocupacional.
    </p>

    <div class="beta-subtitulo">2. ALCANCE</div>
    <p class="gamma-parrafo">
        Se aplica a todos los contratistas y proveedores de servicios mediante talento humano asignado a <strong><?= $client['nombre_cliente'] ?></strong> inmediatamente después de su vinculación comercial.
    </p>

    <div class="beta-subtitulo">3. REQUISITOS GENERALES</div>
    <p class="gamma-parrafo">
        El proceso de inducción en <strong><?= $client['nombre_cliente'] ?></strong> forma parte fundamental para la formación y desarrollo de todas las partes interesadas, tales como el consejo de administración, el vigía SST, el administrador de la copropiedad y el talento humano asignado por contratistas y proveedores. La inducción general es un proceso de aprendizaje que incorpora, ubica y orienta a todas las partes interesadas hacia la seguridad y salud dentro de la copropiedad.
    </p>

    <div class="beta-subtitulo">4. CONTENIDO: ESQUEMA GENERAL DEL PROCESO DE INDUCCIÓN</div>

    <div class="beta-subtitulo">ETAPA 1: INTRODUCCIÓN</div>
    <ul class="delta-lista">
        <li class="gamma-parrafo">SG-SST en propiedad horizontal.</li>
    </ul>

    <div class="beta-subtitulo">ETAPA 2: SALUD OCUPACIONAL Y SEGURIDAD INDUSTRIAL</div>
    <ul class="delta-lista">
        <li class="gamma-parrafo">Aspectos generales y legales en Seguridad y Salud en el Trabajo.</li>
        <li class="gamma-parrafo">Objetivos del SG-SST.</li>
        <li class="gamma-parrafo">¿Qué es una obligación?</li>
        <li class="gamma-parrafo">Obligaciones de contratistas.</li>
        <li class="gamma-parrafo">Vigía SST.</li>
        <li class="gamma-parrafo">Brigada de Emergencia.</li>
        <li class="gamma-parrafo">Actos y condiciones de inseguridad.</li>
        <li class="gamma-parrafo">Riesgos y accidentes de trabajo de proveedores y contratistas.</li>
        <li class="gamma-parrafo">Políticas de seguridad.</li>
        <li class="gamma-parrafo">Emergencias.</li>
    </ul>

    <div class="beta-subtitulo">EVALUACIÓN Y CONTROL</div>
    <p class="gamma-parrafo">
        Una vez finalizado el programa de inducción, este debe ser evaluado por el responsable mediante el diligenciamiento de las matrices del control de indicadores y plan de trabajo anual.
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
                    <td><?= isset($version['sin_contrato']) && $version['sin_contrato'] ? '<span style="color: red; font-weight: bold;">PENDIENTE DE CONTRATO</span>' : strftime('%d de %B de %Y', strtotime($version['created_at'])); ?></td>
                    <td><?= $version['change_control'] ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </footer>

    <br>

    <!-- <div>
        <a href="<?= base_url('/generatePdf_prgInduccion') ?>" target="_blank">
            <button type="button">PDF</button>
        </a>
    </div> -->

</body>

</html>