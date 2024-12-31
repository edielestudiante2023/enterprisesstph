<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>1.2.3 Evaluación de la Inducción y/o Reinducción</title>
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

        .gamma-section {
            margin: 20px 0;
        }

        .delta-label {
            font-weight: bold;
        }

        .epsilon-question {
            margin-top: 20px;
        }

        .epsilon-question li {
            margin-top: 5px;
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

    <div class="alpha-title">Evaluación de Seguridad y Salud en el Trabajo</div>

    <div class="gamma-section">
        <p class="delta-label">Nombre: ______________________________</p>
        <p class="delta-label">Fecha: ______________________________</p>
    </div>

    <div class="epsilon-question">
        <p><strong>1. ¿Cuál es el principal objetivo del Sistema de Gestión de Seguridad y Salud en el Trabajo (SG-SST)?</strong></p>
        <ul>
            <li>a) Maximizar los beneficios económicos.</li>
            <li>b) Prevenir enfermedades en los residentes.</li>
            <li>c) Minimizar los riesgos legales de la propiedad horizontal en caso de un eventual accidente.</li>
            <li>d) Fomentar el consumo de alcohol y tabaco en el trabajo.</li>
        </ul>
    </div>

    <div class="epsilon-question">
        <p><strong>2. ¿Quiénes deben implementar el SG-SST en una propiedad horizontal?</strong></p>
        <ul>
            <li>a) Solo los residentes.</li>
            <li>b) Solo los empleados.</li>
            <li>c) Solo los contratistas.</li>
            <li>d) Los contratantes de personal bajo modalidad de contrato civil, comercial o administrativo.</li>
        </ul>
    </div>

    <div class="epsilon-question">
        <p><strong>3. ¿Qué es un "peligro" en el contexto de seguridad y salud en el trabajo?</strong></p>
        <ul>
            <li>a) Un evento inesperado.</li>
            <li>b) Un acto inseguro.</li>
            <li>c) Una fuente, situación o acto con potencial de daño.</li>
            <li>d) Un accidente laboral.</li>
        </ul>
    </div>

    <div class="epsilon-question">
        <p><strong>4. ¿Cuál es la diferencia entre un "peligro" y un "riesgo"?</strong></p>
        <ul>
            <li>a) No hay diferencia.</li>
            <li>b) El riesgo es un acto inseguro.</li>
            <li>c) El peligro es un evento inesperado.</li>
            <li>d) El riesgo es la combinación de la probabilidad de que ocurra un peligro y la severidad de la lesión que puede causar.</li>
        </ul>
    </div>

    <div class="epsilon-question">
        <p><strong>5. ¿Qué función desempeña la "Brigada de Emergencia" en la propiedad horizontal?</strong></p>
        <ul>
            <li>a) Mantener orden y limpieza en las áreas comunes.</li>
            <li>b) Promover la cultura de la prevención y reaccionar en caso de emergencias como sismos o incendios.</li>
            <li>c) Organizar fiestas y eventos.</li>
            <li>d) Gestionar la seguridad en las zonas comunes.</li>
        </ul>
    </div>

    <div class="epsilon-question">
        <p><strong>6. ¿Cuál es el propósito de un "FURAT" en el contexto de seguridad y salud en el trabajo?</strong></p>
        <ul>
            <li>a) Registrar la asistencia de los residentes a cursos de seguridad.</li>
            <li>b) Informar a la ARL sobre la ocurrencia de un accidente de trabajo.</li>
            <li>c) Realizar pruebas de alcoholemia a los trabajadores.</li>
            <li>d) Organizar simulacros de evacuación.</li>
        </ul>
    </div>

    <div class="epsilon-question">
        <p><strong>7. ¿Qué debe exigir la copropiedad en cuanto a las dotaciones de proveedores y contratistas?</strong></p>
        <ul>
            <li>a) Equipos de oficina.</li>
            <li>b) Programas de entretenimiento para residentes.</li>
            <li>c) Programas de capacitación para empleados.</li>
            <li>d) Equipos de protección personal (EPP) adecuados.</li>
        </ul>
    </div>

    <div class="epsilon-question">
        <p><strong>8. ¿Cuál es la política sobre el consumo de alcohol, tabaco y drogas?</strong></p>
        <ul>
            <li>a) Prohibir el consumo solo para los residentes.</li>
            <li>b) Permitir el consumo en áreas designadas.</li>
            <li>c) Prohibir el consumo para proveedores y contratistas.</li>
            <li>d) Promover el consumo de drogas en eventos sociales.</li>
        </ul>
    </div>

    <div class="epsilon-question">
        <p><strong>9. ¿Cuál es el objetivo de la política de prevención, preparación y respuesta ante emergencias?</strong></p>
        <ul>
            <li>a) Fomentar el uso de dispositivos móviles.</li>
            <li>b) Proporcionar entretenimiento a los residentes.</li>
            <li>c) Salvaguardar la salud y la seguridad de las personas en la propiedad.</li>
            <li>d) Controlar el consumo de alimentos en la copropiedad.</li>
        </ul>
    </div>

    <div class="epsilon-question">
        <p><strong>10. ¿Qué tipo de emergencia se menciona como ejemplo en la información proporcionada?</strong></p>
        <ul>
            <li>a) Emergencia tecnológica.</li>
            <li>b) Emergencia natural.</li>
            <li>c) Emergencia social.</li>
            <li>d) Todas las anteriores.</li>
        </ul>
    </div>

    <div class="gamma-section">
        <p class="delta-label">Nombre del Evaluador: ______________________________</p>
        <p class="delta-label">Calificación: ______________________________</p>
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
    <!-- 
    <div>
        <a href="<?= base_url('/generatePdf_ftevaluacionInduccion') ?>" target="_blank">
            <button type="button">PDF</button>
        </a>
    </div> -->

</body>

</html>