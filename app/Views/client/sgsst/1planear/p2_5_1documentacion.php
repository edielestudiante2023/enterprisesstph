<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2.5.1 Procedimiento para el Control y Conservación de Documentos del SG-SST</title>
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

        .alfa-title {
            font-size: 1.5em;
            font-weight: bold;
            text-align: center;
            margin-top: 20px;
        }

        .beta-subtitle {
            font-size: 1.2em;
            font-weight: bold;
            margin-top: 15px;
        }

        .beta-parrafo {
            margin-bottom: 10px;
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

    <div class="alfa-title" style="text-align: center;">
    <h2>Procedimiento para Control y Conservación de Documentos del SG-SST</h2>
</div>

<p class="beta-parrafo"><strong>1. Introducción</strong></p>
<p class="beta-parrafo">
    <strong><?= $client['nombre_cliente'] ?></strong> conservará los registros y documentos que soportan el SG-SST, de manera controlada, garantizando que sean legibles, fácilmente identificables y accesibles, protegidos contra daño, deterioro o pérdida. La conservación se hará de forma electrónica y física. Los siguientes documentos y registros deben ser conservados por un período mínimo de 20 años, contados a partir del momento en que cese la relación comercial con proveedores y contratistas.
</p>

<p class="beta-parrafo"><strong>2. Objetivo</strong></p>
<p class="beta-parrafo">
    Establecer los lineamientos para la elaboración, identificación, revisión, aprobación, divulgación, actualización, identificación de cambios, utilización en los puntos de uso y retiro por obsoletos, de los documentos del Sistema de Gestión de Seguridad y Salud en el Trabajo.
</p>

<p class="beta-parrafo"><strong>3. Alcance</strong></p>
<p class="beta-parrafo">
    Este procedimiento aplica para la generación y control de todos los documentos relacionados con el SG-SST de <strong><?= $client['nombre_cliente'] ?></strong>, relacionados en el Listado Maestro de Documentos.
</p>

<p class="beta-parrafo"><strong>4. Definiciones</strong></p>
<ul class="delta-lista">
    <li><strong>SG-SST:</strong> Sistema de Gestión de Seguridad y Salud en el Trabajo.</li>
    <li><strong>P.V.H.A:</strong> Corresponde a las siglas del Planear, Hacer, Verificar y Actuar.</li>
    <li><strong>Gestión Documental:</strong> Conjunto de actividades administrativas y técnicas tendientes a la planificación, manejo y organización de la documentación producida y recibida por las entidades, desde su origen hasta su destino final, con el objeto de facilitar su utilización y conservación.</li>
    <li><strong>Documento:</strong> Información y su medio de soporte, como papel, disco magnético, óptico o electrónico, fotografía o una combinación de estos.</li>
    <li><strong>Documento Interno:</strong> Elaborado por los procesos de la entidad, incluidos los registros, para demostrar el cumplimiento de sus funciones.</li>
    <li><strong>Documento Externo:</strong> Elaborado por entes externos pero utilizado en los procesos del SG-SST.</li>
    <li><strong>Elaboración:</strong> Proceso de reunir información y preparar el borrador del documento.</li>
    <li><strong>Revisión:</strong> Verificación de la adecuación de los documentos antes de su aprobación.</li>
    <li><strong>Aprobación:</strong> Aceptación de un documento para garantizar su aplicación.</li>
    <li><strong>Distribución:</strong> Entrega de un documento aprobado a los interesados para su aplicación.</li>
    <li><strong>Documento Obsoleto:</strong> Documento que ha perdido su vigencia.</li>
    <li><strong>Plan:</strong> Documento que organiza metas, estrategias y acciones para alcanzar un objetivo.</li>
    <li><strong>Formato:</strong> Documento diseñado para la recolección de información y que proporciona evidencia de una acción.</li>
    <li><strong>Registro:</strong> Documento que presenta resultados obtenidos o evidencia de actividades ejecutadas.</li>
</ul>

<p class="beta-parrafo"><strong>5. Generalidades del Procedimiento</strong></p>
<p class="beta-parrafo">
    Los documentos entrarán en vigor una vez estén aprobados y/o publicados. Este proceso garantiza el uso de las versiones vigentes y pertinentes del SG-SST.
</p>

<p class="beta-parrafo"><strong>6. Responsables</strong></p>
<ul class="delta-lista">
    <li><strong>6.1 Responsable del mantenimiento:</strong> Responsable del SG-SST.</li>
    <li><strong>6.2 Responsable de la ejecución:</strong> Responsable del SG-SST.</li>
</ul>

<p class="beta-parrafo"><strong>7. Procedimiento para Documentos y Registros del SG-SST</strong></p>
<table class="beta-tabla">
    <thead>
        <tr>
            <th>Actividad</th>
            <th>Descripción</th>
            <th>Registro</th>
            <th>Responsable</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>1</td>
            <td>Identificar necesidad de crear documento.</td>
            <td>N/A</td>
            <td>Responsable del SG-SST</td>
        </tr>
        <tr>
            <td>2</td>
            <td>Evaluar y analizar la necesidad de creación, modificación o eliminación de documento.</td>
            <td>N/A</td>
            <td>Responsable del SG-SST</td>
        </tr>
        <tr>
            <td>3</td>
            <td>Elaborar o modificar el documento según lo requerido.</td>
            <td>N/A</td>
            <td>Responsable del SG-SST</td>
        </tr>
        <tr>
            <td>4</td>
            <td>Revisión del documento final y ajustes necesarios.</td>
            <td>N/A</td>
            <td>Responsable del SG-SST / Vigía SST / Administrador</td>
        </tr>
        <tr>
            <td>5</td>
            <td>Socializar o publicar documento y actualizar carpetas.</td>
            <td>Listado maestro de documentos</td>
            <td>Responsable del SG-SST</td>
        </tr>
    </tbody>
</table>

<p class="beta-parrafo"><strong>8. Codificación de los Documentos</strong></p>
<p class="beta-parrafo">La identificación de los documentos se realizará mediante código y título, excepto los registros. A continuación, la tabla sinóptica de codificación:</p>
<table class="beta-tabla">
    <thead>
        <tr>
            <th>Tipo de Documento</th>
            <th>Codificación</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Procedimientos</td>
            <td>PRC-SST-XXX</td>
        </tr>
        <tr>
            <td>Programas</td>
            <td>PRG-SST-XXX</td>
        </tr>
        <tr>
            <td>Planes</td>
            <td>PLA-SST-XXX</td>
        </tr>
        <tr>
            <td>Políticas</td>
            <td>PLT-SST-XXX</td>
        </tr>
        <tr>
            <td>Formatos</td>
            <td>FT-SST-XXX</td>
        </tr>
        <tr>
            <td>Reglamentos</td>
            <td>REG-SST-XXX</td>
        </tr>
    </tbody>
</table>

<p class="beta-parrafo"><strong>9. Actualización</strong></p>
<p class="beta-parrafo">
    Cada cambio en el SG-SST implicará la revisión y actualización de los documentos afectados. Se mantendrá una lista de referencia con la última revisión aprobada y los responsables.
</p>

<p class="beta-parrafo"><strong>10. Archivo y Conservación</strong></p>
<p class="beta-parrafo">
    Los documentos se archivarán en formato físico y digital, con un almacenamiento mínimo de 20 años, según lo estipulado por el Decreto 1072 de 2015.
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

    <br>

    <!--  <div>
        <a href="<?= base_url('/generatePdf_documentosSgsst') ?>" target="_blank">
            <button type="button">PDF</button>
        </a>
    </div> -->

</body>

</html>