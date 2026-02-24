<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 80px 50px 60px 50px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9px;
            color: #333;
            line-height: 1.3;
            padding: 10px 15px;
        }

        .header-table { width: 100%; border-collapse: collapse; border: 1.5px solid #333; margin-bottom: 10px; }
        .header-table td { border: 1px solid #333; padding: 4px 6px; vertical-align: middle; }
        .header-logo { width: 100px; text-align: center; font-size: 8px; }
        .header-logo img { max-width: 85px; max-height: 50px; }
        .header-title { text-align: center; font-weight: bold; font-size: 9px; }
        .header-code { width: 120px; font-size: 8px; }

        .main-title { text-align: center; font-size: 11px; font-weight: bold; margin: 8px 0 4px; color: #1c2437; }
        .main-subtitle { text-align: center; font-size: 9px; font-weight: bold; margin: 0 0 6px; color: #444; }

        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; border: 1px solid #ccc; }
        .info-table td { padding: 3px 6px; font-size: 9px; border: 1px solid #ccc; }
        .info-label { font-weight: bold; color: #444; width: 180px; background: #f7f7f7; }

        .section-title { background: #1c2437; color: white; padding: 3px 8px; font-weight: bold; font-size: 9px; margin: 8px 0 4px; }

        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .data-table th { background: #e8e8e8; border: 1px solid #aaa; padding: 4px 6px; font-size: 8px; text-align: center; }
        .data-table td { border: 1px solid #ccc; padding: 3px 6px; font-size: 8px; vertical-align: middle; }

        .freq-poco { background: #d4edda; color: #155724; font-weight: bold; text-align: center; }
        .freq-probable { background: #fff3cd; color: #856404; font-weight: bold; text-align: center; }
        .freq-muy { background: #f8d7da; color: #721c24; font-weight: bold; text-align: center; }

        .content-text { font-size: 9px; line-height: 1.4; margin-bottom: 5px; }
        .intro-text { font-size: 8px; line-height: 1.4; margin-bottom: 6px; text-align: justify; }
        .intro-subtitle { font-weight: bold; font-size: 8px; margin: 4px 0 2px; }

        .desc-title { font-weight: bold; font-size: 8px; margin: 4px 0 1px; color: #1c2437; }
        .desc-text { font-size: 7.5px; line-height: 1.3; margin-bottom: 4px; text-align: justify; }

        .result-table { width: 60%; border-collapse: collapse; margin: 6px auto; }
        .result-table td { border: 1px solid #ccc; padding: 4px 8px; font-size: 9px; }
        .result-table .result-label { font-weight: bold; width: 50%; }

        .page-break { page-break-before: always; }
    </style>
</head>
<body>

    <!-- HEADER CORPORATIVO -->
    <table class="header-table">
        <tr>
            <td class="header-logo" rowspan="2">
                <?php if (!empty($logoBase64)): ?>
                    <img src="<?= $logoBase64 ?>">
                <?php else: ?>
                    <strong style="font-size:7px;"><?= esc($cliente['nombre_cliente'] ?? '') ?></strong>
                <?php endif; ?>
            </td>
            <td class="header-title">SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO</td>
            <td class="header-code">Codigo: FT-SST-208<br>Version: 001</td>
        </tr>
        <tr>
            <td class="header-title" style="font-size:10px;">FORMATO DE INSPECCION PROBABILIDAD DE OCURRENCIA DE PELIGROS</td>
            <td class="header-code">Fecha: <?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td>
        </tr>
    </table>

    <!-- TITULO -->
    <div class="main-title">PROBABILIDAD DE OCURRENCIA DE PELIGROS</div>
    <div class="main-subtitle"><?= esc($cliente['nombre_cliente'] ?? '') ?></div>

    <!-- INTRODUCCION -->
    <div class="section-title">IDENTIFICACION Y CARACTERIZACION DE LOS PELIGROS Y LAS AMENAZAS</div>
    <p class="intro-text">
        La identificacion y caracterizacion de los peligros y las amenazas es un paso fundamental en la gestion de riesgos. Permite conocer los potenciales eventos que podrian generar danos a personas, bienes o al medio ambiente, y asi tomar medidas para prevenirlos o mitigar sus efectos.
    </p>
    <p class="intro-subtitle">Origen del peligro:</p>
    <p class="intro-text">
        Se identifican tres origenes: <strong>Natural</strong> (terremotos, inundaciones, vendavales), <strong>Social</strong> (atentados terroristas, amenazas, robos, vandalismo) y <strong>Tecnologico</strong> (incendios, explosiones, fugas de sustancias, fallas estructurales).
    </p>
    <p class="intro-subtitle">Niveles de probabilidad:</p>
    <p class="intro-text">
        <strong>Poco probable:</strong> Baja probabilidad de ocurrencia (10%-33%). <strong>Probable:</strong> Moderada probabilidad (33%-66%). <strong>Muy probable:</strong> Alta probabilidad (66%-99%).
    </p>

    <!-- DATOS DE LA INSPECCION -->
    <div class="section-title">DATOS DE LA INSPECCION</div>
    <table class="info-table">
        <tr>
            <td class="info-label">CLIENTE:</td>
            <td><?= esc($cliente['nombre_cliente'] ?? '') ?></td>
            <td class="info-label">FECHA INSPECCION:</td>
            <td><?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td>
        </tr>
        <tr>
            <td class="info-label">CONSULTOR:</td>
            <td colspan="3"><?= esc($consultor['nombre_consultor'] ?? '') ?></td>
        </tr>
    </table>

    <!-- TABLA DE PELIGROS -->
    <div class="section-title">PROBABILIDAD DE OCURRENCIA</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:18%;">ORIGEN</th>
                <th style="width:42%;">TIPO DE PELIGRO</th>
                <th style="width:40%;">FRECUENCIA</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $freqLabels = ['poco_probable' => 'POCO PROBABLE', 'probable' => 'PROBABLE', 'muy_probable' => 'MUY PROBABLE'];
            $freqClasses = ['poco_probable' => 'freq-poco', 'probable' => 'freq-probable', 'muy_probable' => 'freq-muy'];
            foreach ($peligros as $grupoKey => $grupo):
                $items = $grupo['items'];
                $count = count($items);
                $first = true;
                foreach ($items as $key => $label):
                    $val = $inspeccion[$key] ?? null;
                    $freqClass = $val ? ($freqClasses[$val] ?? '') : '';
                    $freqLabel = $val ? ($freqLabels[$val] ?? '-') : '-';
            ?>
            <tr>
                <?php if ($first): ?>
                <td rowspan="<?= $count ?>" style="font-weight:bold; vertical-align:middle; text-align:center; background:#f0f0f0;">
                    <?= strtoupper($grupo['label']) ?>
                </td>
                <?php $first = false; endif; ?>
                <td><?= $label ?></td>
                <td class="<?= $freqClass ?>"><?= $freqLabel ?></td>
            </tr>
            <?php endforeach; endforeach; ?>
        </tbody>
    </table>

    <!-- DESCRIPCIONES DE PELIGROS -->
    <div class="page-break"></div>
    <div class="section-title">CARACTERIZACION DE LOS PELIGROS</div>

    <p class="desc-title">SISMOS, CAIDA DE ESTRUCTURAS:</p>
    <p class="desc-text">Ninguna edificacion, aun las construidas recientemente, se encuentran exentas de ser afectadas por la accion de las vibraciones derivadas del choque de las placas que forman la superficie de la tierra. La magnitud e intensidad, las caracteristicas del suelo, la resistencia de las edificaciones y la preparacion de las personas e instituciones determinan los danos que este cause. La ciudad de Bogota D.C. y sus alrededores estan asentados en una zona de riesgo sismico intermedio.</p>

    <p class="desc-title">INUNDACIONES:</p>
    <p class="desc-text">Se presentan generalmente despues de una lluvia fuerte o una granizada, por sustraccion de drenajes, por taponamiento de sifones, de desagues o de bajantes de canales; cuando se presenta acumulacion de residuos o basuras o por diametros muy reducidos de los tubos de la caneria; por mala inclinacion de los desniveles hacia los respectivos desagues, o por estar la edificacion en zonas bajas inundables.</p>

    <p class="desc-title">VENDAVALES, GRANIZADA Y TORMENTAS ELECTRICAS:</p>
    <p class="desc-text">Los cambios climaticos y meteorologicos pueden encontrarse acompanados de vientos, lluvias, granizadas, tormentas electricas. La accion de vientos fuertes puede romper ventanales y levantar tejas en las cubiertas, asi como hacer caer antenas y pararrayos.</p>

    <p class="desc-title">ATENTADOS TERRORISTAS:</p>
    <p class="desc-text">Incluye acciones con bombas, proyectiles dirigidos hacia algun objetivo en particular que generalmente puede afectar instalaciones o viviendas aledanas. Tambien puede tratarse de acciones que inciten a sembrar terror en la poblacion, incluyendo secuestros por grupos organizados o bandas criminales.</p>

    <p class="desc-title">ASALTO Y HURTO:</p>
    <p class="desc-text">Existe la posibilidad de tener este riesgo principalmente en horas nocturnas ocasionado por la inseguridad que se presenta en la actualidad. El conjunto residencial cuenta con un sistema de vigilancia privado contratado para salvaguardar los bienes y servicios de los residentes, sin desconocer la problematica que se presenta en las areas perimetrales.</p>

    <p class="desc-title">VANDALISMO:</p>
    <p class="desc-text">Por la descomposicion social que se vive actualmente, esta es una amenaza con un riesgo de probabilidad considerable sin que tenga que ver el tipo de empresa o area habitacional. Es simplemente el deseo de producir panico y sembrar el miedo entre la poblacion a traves de diversas formas.</p>

    <p class="desc-title">INCENDIOS:</p>
    <p class="desc-text">Esta amenaza es caracteristica de toda edificacion cuya destinacion sea de caracter industrial, comercial, de servicios o residencial. Se presenta por eventual vecindad a fuentes de ignicion, fuentes de calor, fuentes electricas, presencia de cargas estaticas y por diferentes cargas combustibles de materiales solidos presentes en las instalaciones.</p>

    <p class="desc-title">EXPLOSIONES:</p>
    <p class="desc-text">Riesgo relacionado con el manejo de cargas combustibles del tipo B como almacenamiento y manipulacion de liquidos y gases inflamables, reactividad por escape de gases comprimidos como gas natural, y manejo de solventes, lacas, pinturas con propiedades inflamables detonantes.</p>

    <p class="desc-title">INHALACION DE GASES:</p>
    <p class="desc-text">Afectaciones en la salud causadas por acumulacion de gases nocivos, especialmente en parqueaderos de vehiculos sin adecuada ventilacion. Se hace necesario implementar la cultura de esperar que los propietarios realicen el calentamiento del motor en un area ventilada.</p>

    <p class="desc-title">FALLA ESTRUCTURAL:</p>
    <p class="desc-text">La vulnerabilidad estructural esta determinada por la capacidad de soporte vertical y resistencia a cargas horizontales de la edificacion. Se recomienda realizar un estudio tecnico de las edificaciones y reforzar las estructuras segun las exigencias del codigo colombiano de construcciones sismo resistentes (Ley 400 de 1997).</p>

    <p class="desc-title">INTOXICACION POR ALIMENTOS:</p>
    <p class="desc-text">Esta amenaza representa un peligro biologico o quimico. Se refiere a las afectaciones en la salud causadas por la ingesta de alimentos contaminados con microorganismos patogenos, toxinas o sustancias quimicas perjudiciales.</p>

    <p class="desc-title">DENSIDAD POBLACIONAL:</p>
    <p class="desc-text">Riesgo de origen social u organizacional asociado con la alta concentracion de personas en un area determinada, lo que puede aumentar la probabilidad de accidentes, propagacion de enfermedades contagiosas y dificultades para ejecutar evacuaciones en caso de emergencia.</p>

    <!-- RESULTADOS CONSOLIDADOS -->
    <div class="section-title">RESULTADOS CONSOLIDADOS</div>
    <table class="result-table">
        <tr>
            <td class="result-label freq-poco">POCO PROBABLE</td>
            <td style="text-align:center; font-weight:bold;"><?= number_format($porcentajes['poco_probable'] * 100, 1) ?>%</td>
        </tr>
        <tr>
            <td class="result-label freq-probable">PROBABLE</td>
            <td style="text-align:center; font-weight:bold;"><?= number_format($porcentajes['probable'] * 100, 1) ?>%</td>
        </tr>
        <tr>
            <td class="result-label freq-muy">MUY PROBABLE</td>
            <td style="text-align:center; font-weight:bold;"><?= number_format($porcentajes['muy_probable'] * 100, 1) ?>%</td>
        </tr>
    </table>

    <!-- OBSERVACIONES DEL CONSULTOR -->
    <?php if (!empty($inspeccion['observaciones'])): ?>
    <div class="section-title">OBSERVACIONES DEL CONSULTOR</div>
    <p class="content-text"><?= nl2br(esc($inspeccion['observaciones'])) ?></p>
    <?php endif; ?>

</body>
</html>
