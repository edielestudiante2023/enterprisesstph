<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2.5.4 Manual de Contratación para Proveedores y Contratistas en Seguridad y Salud en el Trabajo (SST)</title>
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
            text-align: center;
            margin-top: 20px;
        }

        .beta-titulo {
            font-size: 1.2em;
            font-weight: bold;
            margin-top: 15px;
        }

        .beta-parrafo {
            margin-bottom: 10px;
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


    <div class="alfa-parrafo">
    <p class="beta-titulo">INTRODUCCIÓN</p>
    <p>
        <strong><?= $client['nombre_cliente'] ?></strong> establece normas básicas que determinan los criterios relacionados con el Sistema de Gestión de la Seguridad y Salud en el Trabajo para la suscripción y ejecución de contratos con personas naturales y/o jurídicas que presten servicios o suministren bienes. Las disposiciones establecidas serán de obligatorio cumplimiento en la actividad contractual hasta la finalización de la prestación del servicio.
    </p>

    <p class="beta-titulo">OBJETIVO GENERAL</p>
    <p>
        Establecer los requerimientos del Sistema de Gestión de la Seguridad y Salud en el Trabajo que deben cumplir los Proveedores, Contratistas y subcontratistas de <strong><?= $client['nombre_cliente'] ?></strong> y su aplicación en todos los procesos de compras y contratación.
    </p>

    <p class="beta-titulo">OBJETIVOS</p>
    <ul>
        <li>Garantizar que los Proveedores y Contratistas conozcan y establezcan rápidamente el contexto en el que se desarrollará su labor.</li>
        <li>Dar cumplimiento a requisitos legales del Sistema de Gestión de Seguridad y Salud en el Trabajo.</li>
        <li>Definir los términos bajo los cuales el Proveedor o Contratista se debe regir para prestar un servicio a la copropiedad.</li>
    </ul>

    <p class="beta-titulo">DESCRIPCIÓN DEL PROCEDIMIENTO</p>
    <p class="gamma-subtitulo">A. DEFINICIONES</p>
    <ul>
        <li><strong>Compra:</strong> Adquisición del derecho de uso o explotación de un bien o servicio, a cambio de la entrega de otro bien representado en dinero.</li>
        <li><strong>Insumos:</strong> Todo elemento de consumo interno necesario para el normal desarrollo de las actividades administrativas en la propiedad horizontal.</li>
        <li><strong>No conformidad:</strong> No cumplimiento de un requisito, pudiendo ser una desviación de estándares, prácticas, procedimientos, requisitos normativos, entre otros.</li>
        <li><strong>Parte interesada:</strong> Persona o grupo involucrado o afectado por el desempeño en seguridad y salud en el trabajo en la propiedad horizontal.</li>
        <li><strong>Producto:</strong> Resultado del desarrollo de actividades o procesos aplicados a materias primas e insumos para generar un elemento tangible.</li>
        <li><strong>Proveedor:</strong> Organización o persona que proporciona producto o servicio a la propiedad horizontal.</li>
        <li><strong>Proveedores críticos:</strong> Aquellos que pueden afectar de manera significativa la seguridad y salud en el trabajo de la copropiedad.</li>
        <li><strong>Proveedor único o exclusivo:</strong> Organización que por las características especiales de su producto y/o servicio no tiene competencia alguna.</li>
        <li><strong>Requisito Normativo:</strong> Requisito de seguridad y salud en el trabajo impuesto por una norma vigente y aplicable.</li>
        <li><strong>Servicio:</strong> Labor o trabajo realizado a favor de la propiedad horizontal por empresas o personas, sin implicar transformación de materia prima.</li>
    </ul>

    <p class="gamma-subtitulo">B. CRITERIOS DE SELECCIÓN DE PROVEEDORES</p>
    <p>Detalla los criterios específicos de selección de proveedores adaptados a las necesidades particulares de la propiedad horizontal:</p>
    <ul>
        <li>Experiencia en servicios similares para propiedades horizontales.</li>
        <li>Cumplimiento de normativas de seguridad y salud específicas para la propiedad horizontal.</li>
        <li>Referencias de trabajos anteriores relacionados con comunidades residenciales.</li>
        <li>Capacidades técnicas y recursos disponibles adaptados a las instalaciones de la propiedad horizontal.</li>
        <li>Cumplimiento de plazos y tiempos de entrega ajustados a las necesidades de la copropiedad.</li>
    </ul>

    <p class="gamma-subtitulo">C. PROCEDIMIENTO DE EVALUACIÓN DE PROVEEDORES</p>
    <ol>
        <li><strong>Criterios de Evaluación:</strong>
            <ul>
                <li>Experiencia en servicios para propiedades horizontales.</li>
                <li>Cumplimiento de normativas de seguridad y salud aplicables.</li>
                <li>Referencias de trabajos anteriores relacionados con comunidades residenciales.</li>
                <li>Capacidades técnicas y recursos adecuados para las instalaciones.</li>
                <li>Cumplimiento de plazos y tiempos de entrega ajustados.</li>
            </ul>
        </li>
        <li><strong>Proceso de Evaluación:</strong>
            <ul>
                <li>Recepción de propuestas y documentación de proveedores interesados.</li>
                <li>Revisión de la documentación en función de los criterios establecidos.</li>
                <li>Entrevistas o visitas para evaluar in situ la capacidad del proveedor.</li>
                <li>Puntuación y clasificación de los proveedores según criterios.</li>
                <li>Aprobación final basada en la evaluación integral.</li>
            </ul>
        </li>
        <li><strong>Comunicación de Resultados:</strong>
            <ul>
                <li>Notificación a los proveedores sobre los resultados de la evaluación.</li>
                <li>Retroalimentación detallada sobre los aspectos evaluados.</li>
                <li>Comunicación transparente de los motivos de aprobación o rechazo.</li>
            </ul>
        </li>
    </ol>

    <p class="gamma-subtitulo">D. REGISTRO DE PROVEEDORES APROBADOS</p>
    <ul>
        <li><strong>Creación del Registro:</strong> Documentación de proveedores aprobados y detalles relevantes.</li>
        <li><strong>Actualización Periódica:</strong> Revisión y actualización regular del registro.</li>
        <li><strong>Acceso Controlado:</strong> Limitación del acceso al registro a personal autorizado.</li>
    </ul>

    <p class="gamma-subtitulo">E. REVISIÓN PERIÓDICA DE DESEMPEÑO</p>
    <ol>
        <li><strong>Frecuencia de Revisiones:</strong>
            <ul>
                <li>Establecimiento de un calendario para revisiones periódicas.</li>
                <li>Revisiones anuales como mínimo.</li>
            </ul>
        </li>
        <li><strong>Criterios de Evaluación:</strong>
            <ul>
                <li>Cumplimiento de los términos contractuales.</li>
                <li>Calidad del servicio proporcionado.</li>
                <li>Cumplimiento de normativas de seguridad y salud.</li>
                <li>Retroalimentación de los residentes y usuarios.</li>
            </ul>
        </li>
        <li><strong>Acciones Correctivas:</strong> Implementación de planes de mejora continua.</li>
    </ol>

    <p class="gamma-subtitulo">F. PLAN DE CONTINGENCIA</p>
    <ul>
        <li><strong>Identificación de Riesgos:</strong> Evaluación de criticidad de riesgos.</li>
        <li><strong>Acciones Preventivas:</strong> Implementación de medidas preventivas.</li>
        <li><strong>Escalada de Problemas:</strong> Procedimientos para escalar problemas.</li>
    </ul>

    <p class="gamma-subtitulo">G. TRANSPARENCIA EN LA SELECCIÓN</p>
    <ul>
        <li><strong>Divulgación de Requisitos:</strong> Comunicación clara de los requisitos.</li>
        <li><strong>Proceso de Evaluación Transparente:</strong> Garantía de objetividad en la evaluación.</li>
    </ul>

    <p class="gamma-subtitulo">H. CANAL DE COMUNICACIÓN CON PROVEEDORES</p>
    <ul>
        <li><strong>Reuniones Periódicas:</strong> Programación de reuniones regulares con proveedores.</li>
        <li><strong>Feedback Constructivo:</strong> Fomento de un ambiente abierto para la retroalimentación constructiva.</li>
        <li><strong>Mecanismos de Comunicación:</strong> Establecimiento de canales formales e informales de comunicación.</li>
    </ul>

    <p class="beta-titulo">EVALUACIÓN Y MEJORA CONTINUA DE PROVEEDORES</p>
    <p>
        Con el objetivo de robustecer las prácticas de seguridad y salud en el trabajo, <strong><?= $client['nombre_cliente'] ?></strong> implementa un enfoque proactivo para asegurar el cumplimiento de requisitos legales y la mejora continua en la gestión de proveedores y contratistas.
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
        footer table th, footer table td {
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
            width: 35%; /* Más ancho para la columna Observaciones */
        }
        footer table th:nth-child(1),
        footer table td:nth-child(1) {
            width: 10%; /* Más estrecho para la columna Versión */
        }
        footer table th:nth-child(2),
        footer table td:nth-child(2),
        footer table th:nth-child(3),
        footer table td:nth-child(3),
        footer table th:nth-child(4),
        footer table td:nth-child(4) {
            width: 15%; /* Ancho uniforme para las demás columnas */
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
        <a href="<?= base_url('/generatePdf_manProveedores') ?>" target="_blank">
            <button type="button">PDF</button>
        </a>
    </div> -->

</body>

</html>