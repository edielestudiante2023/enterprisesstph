<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RESULTADO</title>
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
        .alpha-container {
            width: 96%;
            padding: 20px;
        }

        .alpha-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .alpha-table th,
        .alpha-table td {
            border: 1px solid black;
            padding: 10px;
            text-align: left;
        }

        .alpha-table th {
            background-color: #f2f2f2;
        }

        .alpha-title {
            text-align: center;
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 20px;
        }

        .beta-section-title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin-top: 30px;
            margin-bottom: 10px;
        }

        .gamma-p {
            font-size: 14px;
            margin-bottom: 15px;
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
                    Fecha: <?= date('d M Y', strtotime($latestVersion['created_at'])) ?>
                </td>
            </tr>
        </table>
    </div>


    <h1>MATRIZ GENERAL DE INDICADORES</h1>

    <!-- Mostrar el KPI del primer cliente -->
    <?php if (!empty($clientKpis)): ?>
        <h3>POLÍTICA SST : <?= esc($clientKpis[0]['kpi_policy']) ?></h3>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Cliente</th>
                <!-- <th>Política</th> -->
                <th>Objetivo</th>
                <th>KPI</th>
                <th>Tipo de KPI</th>
                    <!-- <th>Definición</th>
                    <th>Meta</th>
                    <th>Fórmula</th>
                    <th>Responsable del Dato</th>
                    <th>Fuente de Datos</th>
                    <th>Gran Total Indicador</th> -->
                <th>Promedio de Indicadores</th>
                <th>Periodicidad</th>
                <!--  <th>Análisis de Datos</th>
                    <th>Seguimiento 1</th>
                    <th>Seguimiento 2</th>
                    <th>Seguimiento 3</th> -->
            </tr>
        </thead>
        <tbody>
            <?php foreach ($clientKpis as $kpi): ?>
                <tr>
                    <td><?= esc($kpi['cliente']) ?></td>
                    <!-- <td><?= esc($kpi['kpi_policy']) ?></td> -->
                    <td><?= esc($kpi['objective']) ?></td>
                    <td><?= esc($kpi['kpi']) ?></td>
                     <td><?= esc($kpi['kpi_type']) ?></td><!-- 
                        <td><?= esc($kpi['kpi_definition']) ?></td>
                        <td><?= esc($kpi['kpi_target']) ?></td>
                        <td><?= esc($kpi['kpi_formula']) ?></td>
                        <td><?= esc($kpi['data_owner']) ?></td>
                        <td><?= esc($kpi['data_source']) ?></td>
                        <td><?= esc($kpi['gran_total_indicador']) ?></td> -->
                    <td><?= number_format($kpi['promedio_indicadores'], 2) ?>%</td>
                    <td><?= esc($kpi['periodicidad']) ?></td>
                    <!--  <td><?= esc($kpi['analisis_datos']) ?></td>
                        <td><?= esc($kpi['seguimiento1']) ?></td>
                        <td><?= esc($kpi['seguimiento2']) ?></td>
                        <td><?= esc($kpi['seguimiento3']) ?></td> -->
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>



    <footer>
        <h2>Historial de Versiones</h2>
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
                    <td><?= date('d M Y', strtotime($version['created_at'])) ?></td>
                    <td><?= $version['change_control'] ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </footer>



</body>

</html>