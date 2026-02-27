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
        Se identifican cuatro origenes de los peligros:<br>
        <strong>Natural:</strong> Incluye eventos como terremotos, inundaciones, vendavales e incendios forestales.<br>
        <strong>Social:</strong> Comprende situaciones como atentados terroristas, amenazas y robos.<br>
        <strong>Tecnologico:</strong> Abarca riesgos asociados a la presencia de empresas vecinas, almacenamiento de gases toxicos, inflamabilidad de sustancias, operacion de aeropuertos y la movilidad vehicular.
    </p>

    <p class="intro-subtitle">Definicion de los niveles de probabilidad:</p>
    <p class="intro-text">
        <strong>Poco probable:</strong> Se refiere a un evento o fenomeno que tiene baja probabilidad de ocurrir. Es decir, existe una alta posibilidad de que no suceda. Se puede expresar como "improbable", "con baja probabilidad" o "con pocas posibilidades de ocurrir". Rango de probabilidad: entre 10% y 33%.<br><br>
        <strong>Probable:</strong> Indica un evento o fenomeno que tiene moderada probabilidad de ocurrir. Es decir, existe una posibilidad razonable de que suceda. Se puede expresar como "con posibilidades de ocurrir", "es posible que suceda" o "no se puede descartar su ocurrencia". Rango de probabilidad: entre 33% y 66%.<br><br>
        <strong>Muy probable:</strong> Se refiere a un evento o fenomeno que tiene alta probabilidad de ocurrir. Es decir, existe una gran posibilidad de que suceda. Se puede expresar como "altamente probable", "con alta probabilidad" o "es casi seguro que suceda". Rango de probabilidad: entre 66% y 99%.
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
<div class="section-title">CARACTERIZACION DE LOS PELIGROS</div>

    <p class="desc-title">SISMOS, CAIDA DE ESTRUCTURAS:</p>
    <p class="desc-text">Ninguna edificacion, aun las construidas recientemente, se encuentran exentas de ser afectadas por la accion de las vibraciones derivadas del choque de las placas que forman la superficie de la tierra y que se mueven continuamente en direcciones diferentes acumulando y liberando energia que sacude la superficie, fenomeno que se conoce como terremoto, sismo o temblor de tierra. La magnitud e intensidad, las caracteristicas del suelo (suelos blandos o rellenos pueden aumentar la capacidad destructiva en determinadas edificaciones), la resistencia de las edificaciones (una falla estructural de planta fisica puede hacer colapsar de manera parcial o total estructuras de la edificacion con alteracion directa de su capacidad portante y dano a sus elementos y ocupantes), y la preparacion que se tenga por parte de las personas e instituciones para actuar y reaccionar en forma adecuada, antes, durante y despues del fenomeno, dependen los danos que este cause. Es de anotar que la ciudad de Bogota D.C. y sus alrededores estan asentados en una zona de riesgo sismico intermedio.</p>

    <p class="desc-title">INUNDACIONES:</p>
    <p class="desc-text">Se presentan generalmente despues de una lluvia fuerte o una granizada, por sustraccion de drenajes, por taponamiento de sifones, de desagues o de bajantes de canales; cuando se presenta acumulacion de residuos o basuras o por diametros muy reducidos de los tubos de la caneria; por mala inclinacion de los desniveles hacia los respectivos desagues, o por estar la edificacion en zonas bajas inundables como cerca de rios, lagos o por estar construida en zonas pantanosas. Por presentarse en la capital epocas de fuertes y prolongados inviernos, es muy probable que se presenten este tipo de amenazas.</p>

    <p class="desc-title">VENDAVALES, GRANIZADA Y TORMENTAS ELECTRICAS:</p>
    <p class="desc-text">Los cambios climaticos y meteorologicos se pueden encontrar acompanados de vientos, lluvias, granizadas, tormentas electricas. La accion de vientos fuertes no solo puede romper ventanales y levantar tejas en las cubiertas, sino hacer caer antenas y pararrayos. Las tormentas electricas pueden traer como consecuencia accidentes fatales de trabajadores y la probabilidad de incendios con perdidas materiales.</p>

    <p class="desc-title">ATENTADOS TERRORISTAS:</p>
    <p class="desc-text">En este se incluyen aquellas acciones en que ademas de bombas, o proyectiles dirigidos desde cierta distancia hacia algun objetivo en particular y que generalmente puede afectar instalaciones o viviendas aledanas y ajenas a las que se proponian hacer dano sin que por eso importe algo tambien puede tratarse de acciones de que inciten a sembrar terror en la poblacion esto puede incluir acciones como el secuestro ya sea por grupos organizados como la guerrilla o bandas criminales.</p>

    <p class="desc-title">ASALTO Y HURTO:</p>
    <p class="desc-text">Existe la posibilidad de tener este riesgo principalmente en horas nocturnas ocasionado por la gran inseguridad que se presenta en la actualidad en el Distrito Capital, pero es importante acotar que el conjunto residencial cuenta con un sistema de vigilancia privado contratado para salvaguardar los bienes y servicios de los residentes pero esto se limita solo a la propiedad horizontal, esto no desconoce la problematica que se presenta en las areas perimetrales del sector del conjunto residencial.</p>

    <p class="desc-title">VANDALISMO:</p>
    <p class="desc-text">Por la gran descomposicion social que se vive hoy en dia esta es una de las amenazas con un riesgo de probabilidad considerable sin que tenga que ver el tipo de empresa o area habitacional que pueda ser afectada es simplemente el deseo de producir panico y sembrar el miedo entre la poblacion. La probabilidad de que suceda esta amenaza es poco probable debido a la ubicacion que presenta el conjunto residencial, sin embargo se debe considerar que existen muchas formas de hacerlo: a traves de paquetes, de sobres, de vehiculos y de variedad de articulos incluyendo extintores.</p>

    <p class="desc-title">INCENDIOS:</p>
    <p class="desc-text">Entre las amenazas mas importantes se hace referencia a las de incendio, la cual es caracteristica de toda edificacion cuya destinacion sea de caracter industrial, comercial, de servicios o residencia. Esta amenaza no solamente se presenta por una eventual vecindad a fuentes de ignicion o detonacion, fuentes de calor, fuentes electricas, presencia de cargas estaticas y tambien por diferentes cargas combustibles de materiales solidos presentes en las instalaciones del conjunto residencial y a los trabajos que en el se realicen. Debido a que el conjunto se almacenan diferentes combustibles como vestuario, telones, madera, alfombras, carton, plasticos, equipos de oficina; presencia de gas natural y demas combustibles que pueden ocasionar un incendio de grandes proporciones.</p>

    <p class="desc-title">EXPLOSIONES:</p>
    <p class="desc-text">Es un riesgo que viene relacionado con el manejo de cargas combustibles del tipo B como el almacenamiento y manipulacion de liquidos y gases inflamables, la reactividad por escape de gases comprimidos como el caso de gas natural, y en el manejo de solventes, lacas, pinturas, varsol que normalmente emanan gases con propiedades inflamables detonantes lo mismo que eventualmente lo podria hacer pero con menos posibilidad el ACPM.</p>

    <p class="desc-title">INHALACION DE GASES:</p>
    <p class="desc-text">Estas afectaciones en la salud se pueden causar debido a la acumulacion de gases nocivos para las personas, esto se puede agravar en el caso del parqueadero de vehiculos del conjunto residencial si no se cuenta con la cultura de la revision periodica de los vehiculos automotores y si las personas se quedan bajo periodos largos en este sitio, para lo cual se hace necesario implementar la cultura de esperar que los propietarios de los vehiculos realicen el calentamiento del motor en un area ventilada.</p>

    <p class="desc-title">FALLA ESTRUCTURAL:</p>
    <p class="desc-text">La vulnerabilidad estructural se encuentra determinada por la capacidad de soporte vertical y resistencia a cargas horizontales de la edificacion, la cual en terminos generales presenta buen aspecto. Un gran porcentaje de las instalaciones esta construida en muros de ladrillo y cemento, pisos en cemento, techos en placa de concreto. Con el objeto de determinar la capacidad sismo resistente de las edificaciones se recomienda realizar un estudio tecnico de las mismas y conforme a su valoracion reforzar las estructuras o realizar las modificaciones arquitectonicas necesarias, en consonancia con las exigencias del codigo colombiano de construcciones sismo resistentes, adoptado por el Decreto 400 con vigencia desde el ano de 1984 y actualizado por la Ley 400 de 1997 y el Decreto 33 de 1998.</p>

    <p class="desc-title">INTOXICACION POR ALIMENTOS:</p>
    <p class="desc-text">Esta amenaza representa un peligro biologico (o quimico, segun el caso). Se refiere a las afectaciones en la salud causadas por la ingesta de alimentos contaminados con microorganismos patogenos, toxinas o sustancias quimicas perjudiciales.</p>

    <p class="desc-title">DENSIDAD POBLACIONAL:</p>
    <p class="desc-text">Este riesgo se considera de origen social o organizacional. Se refiere al riesgo asociado con la alta concentracion de personas en un area determinada, lo que puede aumentar la probabilidad de accidentes, propagacion de enfermedades contagiosas y dificultades para ejecutar evacuaciones en caso de emergencia.</p>

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
