<?php
$nombreCliente = esc($cliente['nombre_cliente'] ?? '');
$direccion = esc($cliente['direccion_cliente'] ?? '');
$ciudad = $inspeccion['ciudad'] ?? null;
$telefonosCiudad = ($ciudad && isset($telefonos[$ciudad])) ? $telefonos[$ciudad] : [];
$enumSiNo = ['si' => 'SI', 'no' => 'NO'];
$tipoInmueble = ['casas' => 'CASAS', 'apartamentos' => 'APARTAMENTOS'];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 70px 50px 60px 50px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9px;
            color: #333;
            line-height: 1.4;
            padding: 10px 15px;
        }

        .header-table { width: 100%; border-collapse: collapse; border: 1.5px solid #333; margin-bottom: 10px; }
        .header-table td { border: 1px solid #333; padding: 4px 6px; vertical-align: middle; }
        .header-logo { width: 100px; text-align: center; font-size: 8px; }
        .header-logo img { max-width: 85px; max-height: 50px; }
        .header-title { text-align: center; font-weight: bold; font-size: 9px; }
        .header-code { width: 120px; font-size: 8px; }

        .cover-page { text-align: center; padding-top: 120px; }
        .cover-title { font-size: 18px; font-weight: bold; color: #1c2437; margin-bottom: 10px; }
        .cover-subtitle { font-size: 14px; font-weight: bold; color: #444; margin-bottom: 30px; }
        .cover-img { max-width: 400px; max-height: 280px; border: 2px solid #ccc; }

        .main-title { text-align: center; font-size: 11px; font-weight: bold; margin: 8px 0 4px; color: #1c2437; }

        .section-title { background: #1c2437; color: white; padding: 4px 8px; font-weight: bold; font-size: 9px; margin: 10px 0 5px; }
        .section-subtitle { font-weight: bold; font-size: 9px; color: #1c2437; margin: 6px 0 3px; }

        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; border: 1px solid #ccc; }
        .info-table td { padding: 3px 6px; font-size: 9px; border: 1px solid #ccc; }
        .info-label { font-weight: bold; color: #444; width: 45%; background: #f7f7f7; }

        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .data-table th { background: #e8e8e8; border: 1px solid #aaa; padding: 3px 5px; font-size: 8px; text-align: center; }
        .data-table td { border: 1px solid #ccc; padding: 3px 5px; font-size: 8px; vertical-align: middle; }

        .content-text { font-size: 8.5px; line-height: 1.4; margin-bottom: 5px; text-align: justify; }
        .content-bold { font-size: 8.5px; font-weight: bold; margin: 5px 0 2px; color: #1c2437; }

        .foto-block { text-align: center; margin: 6px 0; }
        .foto-block img { max-width: 300px; max-height: 200px; border: 1px solid #ccc; }
        .foto-caption { font-size: 7px; color: #666; margin-top: 2px; }

        .foto-row { width: 100%; margin: 6px 0; }
        .foto-row td { text-align: center; padding: 4px; vertical-align: top; }
        .foto-row img { max-width: 220px; max-height: 150px; border: 1px solid #ccc; }

        .annex-title { background: #2c3e50; color: white; padding: 5px 8px; font-weight: bold; font-size: 10px; margin: 10px 0 6px; text-align: center; }

        .opt-a { background: #d4edda; color: #155724; font-weight: bold; text-align: center; }
        .opt-b { background: #fff3cd; color: #856404; font-weight: bold; text-align: center; }
        .opt-c { background: #f8d7da; color: #721c24; font-weight: bold; text-align: center; }

        .freq-poco { background: #d4edda; color: #155724; font-weight: bold; text-align: center; }
        .freq-probable { background: #fff3cd; color: #856404; font-weight: bold; text-align: center; }
        .freq-muy { background: #f8d7da; color: #721c24; font-weight: bold; text-align: center; }
    </style>
</head>
<body>

    <!-- ============ PORTADA ============ -->
    <div class="cover-page">
        <?php if (!empty($logoBase64)): ?>
        <div style="margin-bottom:20px;"><img src="<?= $logoBase64 ?>" style="max-width:180px; max-height:100px;"></div>
        <?php endif; ?>
        <div class="cover-title">PLAN DE EMERGENCIA</div>
        <div class="cover-subtitle"><?= $nombreCliente ?></div>
        <?php if (!empty($fotosBase64['foto_fachada'])): ?>
        <div style="margin-top:30px;"><img src="<?= $fotosBase64['foto_fachada'] ?>" class="cover-img"></div>
        <?php endif; ?>
    </div>

    <!-- ============ HEADER CORPORATIVO ============ -->
<table class="header-table">
        <tr>
            <td class="header-logo" rowspan="2">
                <?php if (!empty($logoBase64)): ?>
                    <img src="<?= $logoBase64 ?>">
                <?php else: ?>
                    <strong style="font-size:7px;"><?= $nombreCliente ?></strong>
                <?php endif; ?>
            </td>
            <td class="header-title">SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO</td>
            <td class="header-code">Codigo: FT-SST-001<br>Version: 001</td>
        </tr>
        <tr>
            <td class="header-title" style="font-size:10px;">PLAN DE EMERGENCIA Y CONTINGENCIA</td>
            <td class="header-code">Fecha: <?= date('d/m/Y', strtotime($inspeccion['fecha_visita'])) ?></td>
        </tr>
    </table>

    <!-- ============ INTRODUCCION ============ -->
    <div class="section-title">INTRODUCCION</div>
    <p class="content-text">De acuerdo con la Ley 675 de 2001, se considera edificio a una construccion de uno o varios pisos levantados sobre un lote o terreno, cuya estructura comprende un numero plural de unidades independientes; y conjunto al desarrollo inmobiliario conformado por varios edificios levantados sobre uno o varios lotes de terreno, que comparten areas y servicios de uso y utilidad general, como vias internas, estacionamiento, zonas verdes, muros de cerramiento, porterias, entre otros. Cuando uno u otro se destina a la vivienda de las personas, se denomina edificio o conjunto de uso residencial (Congreso de Colombia Ley 675/01, 2001).</p>
    <p class="content-text">Es posible que todo edificio o instalacion que albergue personas se convierta en un lugar de desastre en el evento que se produzca una emergencia repentina que adolezca de la oportuna y optima intervencion de esta. La emergencia puede agravarse con el panico que usualmente se despierta cuando se carece de la preparacion adecuada para afrontar un suceso.</p>
    <p class="content-text">Todas las entidades ya sean del sector publico o privado tienen la responsabilidad de administrar situaciones generadas por los desastres o por las emergencias que puedan presentarse, como consecuencia del riesgo al cual se encuentran expuestos.</p>
    <p class="content-text">Un plan de emergencia es el conjunto de medidas anticipadas a una emergencia, que permite a sus usuarios la posibilidad de no ser afectados si esta sucede. Su proposito es proporcionar los elementos necesarios a todos los miembros que hacen parte del Conjunto residencial, criterios basicos que le permitan responder de forma adecuada a los eventos catastroficos que en una edificacion pueden ocurrir.</p>
    <p class="content-text">Existe la responsabilidad de estar preparados para hacerle frente a las situaciones adversas, las cuales pueden ser de diferente origen: naturales (vendavales, inundaciones, sismos, tormentas electricas, y algunos otros), tecnologicas (incendios, explosiones, derrames de combustibles, fallas electricas, fallas estructurales, entre otras) y sociales (atentados, vandalismo, terrorismos, amenazas de diferente indole y otras acciones). Lo anterior muestra la variedad de emergencias que en cualquier momento pueden afectar de manera individual o colectiva el cotidiano vivir de los residentes con resultados como lesiones o muerte, dano a bienes, afectacion del medio ambiente, alteracion del funcionamiento del conjunto y perdidas economicas.</p>

    <!-- ============ JUSTIFICACION ============ -->
    <div class="section-title">JUSTIFICACION</div>
    <p class="content-text">La gestion del riesgo, de acuerdo con la Ley 1523 de 2012, es un proceso social orientado a la formulacion, ejecucion, seguimiento y evaluacion de politicas, estrategias, planes, programas, regulaciones, instrumentos, medidas y acciones permanentes para el conocimiento y la reduccion del riesgo, y para el manejo de desastres, con el proposito explicito de contribuir a la seguridad, el bienestar, la calidad de vida de las personas y al desarrollo sostenible.</p>
    <p class="content-text">Frente a la imposibilidad de eliminar por completo la probabilidad de ocurrencia de una situacion de emergencia, se ha evidenciado la necesidad de establecer un proceso que permita contrarrestar y minimizar las consecuencias adversas que se presentan en una situacion de crisis. Este proceso es conocido como "Plan de preparacion para emergencias y contingencias", el cual es empleado para prevenir y controlar aquellos eventos que puedan catalogarse como un riesgo.</p>
    <p class="content-text">El plan de emergencias es una herramienta que permite poner en conocimiento todos los factores de riesgo (amenaza y vulnerabilidad) frente a las personas y los bienes. Asi mismo, debe ser divulgado a todas las personas que intervienen en el, e implementarlo por medio de simulacros periodicos, por esto se hace necesaria la participacion de todos los miembros de <?= $nombreCliente ?>.</p>
    <p class="content-text">La mitigacion de la afectacion en la salud de las personas es el principal factor del plan de emergencias del conjunto residencial, es por esto por lo que se planteo un panorama de riesgos que permite evaluar cada una de las estructuras que hacen parte de este.</p>
    <p class="content-text"><?= $nombreCliente ?> implementara el plan de emergencias con la seguridad de que su aplicacion le permitira disponer de una herramienta de trabajo agil en la planificacion de tratamientos de emergencias. Se deberan considerar las politicas y procedimientos, ya que en algun momento cada persona tendra funciones y responsabilidades en cooperacion con la Administracion del Conjunto; debido a lo anterior, se conformaran brigadas de emergencia con sus diferentes acciones y responsabilidades, el equipo de evacuacion, el equipo de primeros auxilios y el equipo control de incendios, con el fin de contar con un ambiente seguro, con la proteccion adecuada para la salud de los residentes y sus trabajadores, brindando una atencion de emergencias de manera eficiente y eficaz.</p>

    <!-- ============ OBJETIVOS ============ -->
    <div class="section-title">OBJETIVOS</div>
    <div class="section-subtitle">OBJETIVO GENERAL</div>
    <p class="content-text">Elaborar el Plan de Emergencias de <?= $nombreCliente ?> para que sirva como guia en el desarrollo de actividades orientadas en la prevencion y atencion de eventos que pueden ocasionar lesiones a los residentes y trabajadores y de igual forma a la infraestructura del conjunto.</p>
    <div class="section-subtitle">OBJETIVOS ESPECIFICOS</div>
    <p class="content-text">Proporcionar a los residentes de <?= $nombreCliente ?> los elementos adecuados que les permitan responder con eficacia en la prevencion y atencion de emergencias para reducir el impacto al interior del conjunto residencial.</p>
    <p class="content-text">Contar con una estructura organizativa eficiente y preparada para actuar en situaciones de emergencia, permitiendo la identificacion oportuna de amenazas, la evaluacion de vulnerabilidades y la definicion precisa de niveles de riesgo.</p>
    <p class="content-text">Minimizar los danos a la comunidad y su ambiente.</p>

    <!-- ============ ALCANCE ============ -->
    <div class="section-title">ALCANCE</div>
    <p class="content-text">Este documento, denominado Plan de Preparacion y Respuesta ante Situaciones de Emergencia (PPRSE), tiene como enfoque principal todas las areas pertenecientes a la copropiedad. Ademas, abarca a todo el personal que forma parte de esta comunidad, incluyendo servidores publicos, contratistas, pasantes, judiciales, visitantes y otras partes interesadas.</p>

    <!-- ============ CONCEPTOS ============ -->
<div class="section-title">CONCEPTOS</div>
    <p class="content-text">De acuerdo con el articulo 4 de la Ley 675 de 2001 por medio de la cual se expide el Regimen de Propiedad Horizontal, cada edificio o conjunto de uso residencial debe constituirse en persona juridica, por tanto, la legislacion colombiana alrededor del tema de Seguridad y Salud en el Trabajo es perfectamente aplicable a las administraciones y consejos de administracion.</p>
    <p class="content-text">Para mayor comprension de la diferente terminologia que se va a tratar en el documento, se describe a continuacion algunos de los conceptos:</p>
    <?php
    $conceptos = [
        'ALARMA O PITO' => 'Sistema sonoro que permite avisar inmediatamente se accione a la comunidad la presencia de un riesgo que pone en grave peligro sus vidas.',
        'ALERTA' => 'Acciones de respuesta especificas frente a una emergencia.',
        'AMENAZA' => 'Factor externo de origen natural, tecnologico o social que puede afectar a la comunidad y a la copropiedad, provocando lesiones y/o muerte a las personas o danos a la infraestructura fisica y economica.',
        'ANALISIS DE VULNERABILIDAD' => 'Es la medida o grado de ser afectado por amenazas o riesgos segun la frecuencia y la severidad de estos. La vulnerabilidad depende de varios factores entre otros: la posibilidad de ocurrencia del evento, la frecuencia de la ocurrencia de este, los planes y programas preventivos existentes y la posibilidad de programacion anual entre otros.',
        'AYUDA INSTITUCIONAL' => 'Es aquella prestada por entidades publicas y/o privadas de caracter comunitario, organizados con el fin especifico de responder de oficio a los desastres.',
        'COMBUSTION' => 'Reaccion mediante la cual una sustancia denominada combustible interactua quimicamente con otra llamada oxidante o comburente y da como resultado gases toxicos irritantes y asfixiantes, humo que obstaculiza la visibilidad y afecta la respiracion, llamas y calor que generan lesiones de diversa intensidad en las personas.',
        'CONTINGENCIA' => 'Evento que puede suceder o no suceder para el cual debemos estar preparados.',
        'CONTROL' => 'Accion de eliminar o limitar el desarrollo de un siniestro, para evitar o minimizar sus consecuencias.',
        'DESASTRE' => 'Es el dano o alteracion grave de las condiciones normales de la vida, causado por fenomenos naturales o accion del hombre en forma accidental.',
        'EMERGENCIA' => 'Estado de alteracion parcial o total de las actividades de una propiedad horizontal, ocasionado por la ocurrencia de un evento que genera peligro inminente y cuyo control supera la capacidad de respuesta de las personas y de las organizaciones.',
        'ESCENARIO' => 'Representacion o descripcion detallada de una situacion o conjunto de circunstancias hipoteticas, ya sea en el presente o en el futuro. Los escenarios se utilizan comunmente en la planificacion estrategica, la toma de decisiones, la gestion de riesgos y la narrativa, para explorar posibles resultados o eventos. En el contexto de un plan de emergencias, un escenario podria ser una representacion detallada de una situacion de emergencia hipotetica que se utiliza como base para la planificacion y preparacion de respuestas.',
        'EVENTO' => 'Descripcion de un fenomeno natural, tecnologico o provocado por el hombre en terminos de sus caracteristicas, su severidad, ubicacion y area de influencia.',
        'EVACUACION' => 'Es el conjunto de acciones tendientes a desplazar las personas de una zona de mayor amenaza a otra de menor peligro.',
        'IMPACTO' => 'Accion directa de una amenaza o un riesgo en un grupo de personas.',
        'MITIGACION' => 'Acciones desarrolladas antes, durante y despues de un siniestro, tendientes a contrarrestar sus efectos criticos, y asegurar la supervivencia del sistema hasta tanto se efectue la recuperacion.',
        'PLAN DE ACCION' => 'Es un trabajo colectivo que establece en un documento las medidas preventivas para evitar los posibles desastres especificos de cada comunidad y que indica las operaciones, tareas, y responsabilidades de toda la comunidad para situaciones de inminente peligro.',
        'PLAN DE CONTINGENCIAS' => 'Componente del plan de emergencias y desastres que contiene los procedimientos para la pronta respuesta en caso de presentarse un evento especifico.',
        'PLAN DE EMERGENCIAS' => 'Definicion de politicas, organizaciones y metodos que indican la manera de enfrentar una situacion de emergencia o desastre, en lo general y en lo particular, en sus distintas fases.',
        'PREPARACION' => 'Se lleva a cabo mediante la organizacion institucional, prediccion de eventos y planificacion de acciones de alerta, busqueda, rescate, traslado, evacuacion y asistencia de personas, salvamento de bienes y de rehabilitacion y reconstruccion de la copropiedad o comunidad.',
        'PREVENCION' => 'Accion para evitar la ocurrencia de desastres.',
        'RECUPERACION' => 'Actividad final en el proceso de respuesta a una emergencia. Consiste en restablecer la operatividad de un sistema interferido.',
        'RIESGO' => 'Una amenaza evaluada en cuanto su probabilidad de ocurrencia y su gravedad potencial esperada.',
        'SALVAMENTO' => 'Acciones o actividades desarrolladas individualmente o por grupos, tendientes a proteger los bienes materiales y/o activos de una compania, que pueden verse afectados en caso de una emergencia en sus instalaciones.',
        'SIMULACRO' => 'Ejercicio de juego de roles que se lleva a cabo en un escenario real o construccion en la forma posible para asemejarlo.',
        'SINIESTRO' => 'Es un evento no deseado, no esperado, que puede producir efectos negativos en las personas y en los bienes materiales. El siniestro genera la emergencia si la capacidad de respuesta es insuficiente para controlarlo.',
        'VIA DE EVACUACION' => 'Se usa normalmente como via de ingreso y de salida en los edificios. Su tramo seguro puede estar estructurado como zona vertical de seguridad.',
        'VULNERABILIDAD' => 'Condiciones en las que se encuentran las personas y los bienes expuestos a una amenaza. Se relaciona con la incapacidad de una comunidad para afrontar con sus propios recursos una situacion de emergencia.',
        'VULNERABILIDAD FISICA O ESTRUCTURAL' => 'Se refiere a la construccion misma de la edificacion y a las caracteristicas de seguridad o inseguridad que ofrece a los trabajadores que permanecen en ella durante la jornada laboral.',
        'VULNERABILIDAD FUNCIONAL' => 'Se refiere a la existencia o no de los recursos para enfrentar situaciones de emergencia como extintores, sistemas de control de fuentes de agua, combustible, herramientas para usar en situaciones de emergencia.',
        'VULNERABILIDAD SOCIAL' => 'Se refiere al conocimiento y entrenamiento del personal para afrontar situaciones de emergencia.',
    ];
    foreach ($conceptos as $term => $def): ?>
    <p class="content-text"><strong><?= $term ?>:</strong> <?= $def ?></p>
    <?php endforeach; ?>

    <!-- ============ INFORMACION GENERAL DEL CONJUNTO ============ -->
<div class="section-title">INFORMACION GENERAL DEL CONJUNTO RESIDENCIAL</div>

    <div class="section-subtitle">UBICACION</div>
    <p class="content-text"><?= $nombreCliente ?> se encuentra localizado en la Direccion: <?= $direccion ?></p>

    <?php if (!empty($fotosBase64['foto_panorama'])): ?>
    <div class="section-subtitle">VISTA DE PANORAMA</div>
    <div class="foto-block"><img src="<?= $fotosBase64['foto_panorama'] ?>"></div>
    <?php endif; ?>

    <div class="section-subtitle">DESCRIPCION DETALLADA DE LAS INSTALACIONES</div>
    <table class="info-table">
        <tr><td class="info-label">TIPO DE INMUEBLE</td><td><?= $tipoInmueble[$inspeccion['casas_o_apartamentos'] ?? ''] ?? '-' ?></td></tr>
        <?php if (($inspeccion['casas_o_apartamentos'] ?? '') === 'apartamentos'): ?>
        <tr><td class="info-label">NUMERO DE TORRES</td><td><?= $inspeccion['numero_torres'] ?? '-' ?></td></tr>
        <?php elseif (($inspeccion['casas_o_apartamentos'] ?? '') === 'casas'): ?>
        <tr><td class="info-label">CASAS DE CUANTOS PISOS</td><td><?= esc($inspeccion['casas_pisos'] ?? '-') ?></td></tr>
        <?php endif; ?>
        <tr><td class="info-label">ESTRUCTURA SISMO RESISTENTE</td><td><?= esc($inspeccion['sismo_resistente'] ?? '-') ?></td></tr>
        <tr><td class="info-label">ANO DE CONSTRUCCION</td><td><?= $inspeccion['anio_construccion'] ?? '-' ?></td></tr>
        <tr><td class="info-label">UNIDADES HABITACIONALES</td><td><?= $inspeccion['numero_unidades_habitacionales'] ?? '-' ?></td></tr>
        <tr><td class="info-label">PARQUEADEROS CARROS RESIDENTES</td><td><?= $inspeccion['parqueaderos_carros_residentes'] ?? '0' ?></td></tr>
        <tr><td class="info-label">PARQUEADEROS CARROS VISITANTES</td><td><?= $inspeccion['parqueaderos_carros_visitantes'] ?? '0' ?></td></tr>
        <tr><td class="info-label">PARQUEADEROS MOTOS RESIDENTES</td><td><?= $inspeccion['parqueaderos_motos_residentes'] ?? '0' ?></td></tr>
        <tr><td class="info-label">PARQUEADEROS MOTOS VISITANTES</td><td><?= $inspeccion['parqueaderos_motos_visitantes'] ?? '0' ?></td></tr>
        <tr><td class="info-label">PARQUEADERO PRIVADO</td><td><?= $enumSiNo[$inspeccion['hay_parqueadero_privado'] ?? ''] ?? '-' ?></td></tr>
        <tr><td class="info-label">SALONES COMUNALES</td><td><?= $inspeccion['cantidad_salones_comunales'] ?? '0' ?></td></tr>
        <tr><td class="info-label">LOCALES COMERCIALES</td><td><?= $inspeccion['cantidad_locales_comerciales'] ?? '0' ?></td></tr>
        <tr><td class="info-label">OFICINA DE ADMINISTRACION</td><td><?= $enumSiNo[$inspeccion['tiene_oficina_admin'] ?? ''] ?? '-' ?></td></tr>
        <tr><td class="info-label">TANQUE DE AGUA</td><td><?= esc($inspeccion['tanque_agua'] ?? '-') ?></td></tr>
        <tr><td class="info-label">PLANTA ELECTRICA</td><td><?= esc($inspeccion['planta_electrica'] ?? '-') ?></td></tr>
    </table>

    <!-- Fotos de torres/casas y parqueaderos -->
    <?php
    $fotosInst = [
        'foto_torres_1' => 'Torres o Casas 1', 'foto_torres_2' => 'Torres o Casas 2',
        'foto_parqueaderos_carros' => 'Parqueaderos Carros', 'foto_parqueaderos_motos' => 'Parqueaderos Motos',
        'foto_oficina_admin' => 'Oficina Administracion',
    ];
    foreach ($fotosInst as $campo => $caption):
        if (!empty($fotosBase64[$campo])): ?>
    <div class="foto-block"><img src="<?= $fotosBase64[$campo] ?>"><div class="foto-caption"><?= $caption ?></div></div>
    <?php endif; endforeach; ?>

    <!-- ============ CIRCULACIONES Y ACCESOS ============ -->
<div class="section-title">CIRCULACIONES Y ACCESOS</div>

    <?php
    $seccCirc = [
        ['titulo' => 'CIRCULACION VEHICULAR', 'campo' => 'circulacion_vehicular', 'fotos' => ['foto_circulacion_vehicular' => 'Zona Vehicular']],
        ['titulo' => 'CIRCULACION PEATONAL', 'campo' => 'circulacion_peatonal', 'fotos' => ['foto_circulacion_peatonal_1' => 'Peatonal 1', 'foto_circulacion_peatonal_2' => 'Peatonal 2']],
        ['titulo' => 'SALIDAS DE EMERGENCIA', 'campo' => 'salidas_emergencia', 'fotos' => ['foto_salida_emergencia_1' => 'Salida Emergencia 1', 'foto_salida_emergencia_2' => 'Salida Emergencia 2']],
        ['titulo' => 'INGRESOS PEATONALES', 'campo' => 'ingresos_peatonales', 'fotos' => ['foto_ingresos_peatonales' => 'Ingresos Peatonales']],
        ['titulo' => 'ACCESOS VEHICULARES', 'campo' => 'accesos_vehiculares', 'fotos' => ['foto_acceso_vehicular_1' => 'Acceso Vehicular 1', 'foto_acceso_vehicular_2' => 'Acceso Vehicular 2']],
    ];
    foreach ($seccCirc as $sec): ?>
    <div class="section-subtitle"><?= $sec['titulo'] ?></div>
    <?php if (!empty($inspeccion[$sec['campo']])): ?>
    <p class="content-text"><?= nl2br(esc($inspeccion[$sec['campo']])) ?></p>
    <?php endif; ?>
    <?php foreach ($sec['fotos'] as $campo => $caption):
        if (!empty($fotosBase64[$campo])): ?>
    <div class="foto-block"><img src="<?= $fotosBase64[$campo] ?>"><div class="foto-caption"><?= $caption ?></div></div>
    <?php endif; endforeach; ?>
    <?php endforeach; ?>

    <!-- Concepto del consultor -->
    <div class="section-subtitle">CONCEPTO DEL CONSULTOR - ENTRADAS Y SALIDAS</div>
    <p class="content-text"><?= nl2br(esc($inspeccion['concepto_entradas_salidas'] ?? '-')) ?></p>
    <div class="section-subtitle">HIDRANTES</div>
    <p class="content-text"><?= nl2br(esc($inspeccion['hidrantes'] ?? '-')) ?></p>

    <!-- Entorno -->
    <div class="section-subtitle">ENTORNO</div>
    <table class="info-table">
        <tr><td class="info-label">CAI MAS CERCANO</td><td><?= esc($inspeccion['cai_cercano'] ?? '-') ?></td></tr>
        <tr><td class="info-label">ESTACION BOMBEROS MAS CERCANA</td><td><?= esc($inspeccion['bomberos_cercanos'] ?? '-') ?></td></tr>
    </table>

    <!-- Proveedores -->
    <div class="section-subtitle">PROVEEDORES</div>
    <table class="info-table">
        <tr><td class="info-label">PROVEEDOR DE VIGILANCIA</td><td><?= esc($inspeccion['proveedor_vigilancia'] ?? '-') ?></td></tr>
        <tr><td class="info-label">PROVEEDOR DE ASEO</td><td><?= esc($inspeccion['proveedor_aseo'] ?? '-') ?></td></tr>
        <?php if (!empty($inspeccion['otros_proveedores'])): ?>
        <tr><td class="info-label">OTROS PROVEEDORES</td><td><?= esc($inspeccion['otros_proveedores']) ?></td></tr>
        <?php endif; ?>
    </table>

    <!-- Control visitantes -->
    <div class="section-subtitle">CONTROL DE VISITANTES</div>
    <table class="info-table">
        <tr><td class="info-label">FORMA DE REGISTRO</td><td><?= esc($inspeccion['registro_visitantes_forma'] ?? '-') ?></td></tr>
        <tr><td class="info-label">PERMITE SABER PERSONAS EN EMERGENCIA</td><td><?= $enumSiNo[$inspeccion['registro_visitantes_emergencia'] ?? ''] ?? '-' ?></td></tr>
        <tr><td class="info-label">CUENTA CON MEGAFONO</td><td><?= $enumSiNo[$inspeccion['cuenta_megafono'] ?? ''] ?? '-' ?></td></tr>
    </table>

    <!-- Ruta de evacuacion -->
    <?php if (!empty($inspeccion['ruta_evacuacion']) || !empty($inspeccion['mapa_evacuacion'])): ?>
    <div class="section-subtitle">RUTA DE EVACUACION</div>
    <?php if (!empty($inspeccion['ruta_evacuacion'])): ?>
    <p class="content-text"><?= nl2br(esc($inspeccion['ruta_evacuacion'])) ?></p>
    <?php endif; ?>
    <?php if (!empty($inspeccion['mapa_evacuacion'])): ?>
    <p class="content-text"><strong>Mapa de evacuacion:</strong> <?= nl2br(esc($inspeccion['mapa_evacuacion'])) ?></p>
    <?php endif; ?>
    <?php foreach (['foto_ruta_evacuacion_1' => 'Ruta Evacuacion 1', 'foto_ruta_evacuacion_2' => 'Ruta Evacuacion 2'] as $f => $c):
        if (!empty($fotosBase64[$f])): ?>
    <div class="foto-block"><img src="<?= $fotosBase64[$f] ?>"><div class="foto-caption"><?= $c ?></div></div>
    <?php endif; endforeach; endif; ?>

    <!-- Puntos de encuentro -->
    <?php if (!empty($inspeccion['puntos_encuentro'])): ?>
    <div class="section-subtitle">PUNTOS DE ENCUENTRO</div>
    <p class="content-text"><?= nl2br(esc($inspeccion['puntos_encuentro'])) ?></p>
    <?php foreach (['foto_punto_encuentro_1' => 'Punto Encuentro 1', 'foto_punto_encuentro_2' => 'Punto Encuentro 2'] as $f => $c):
        if (!empty($fotosBase64[$f])): ?>
    <div class="foto-block"><img src="<?= $fotosBase64[$f] ?>"><div class="foto-caption"><?= $c ?></div></div>
    <?php endif; endforeach; endif; ?>

    <!-- Sistemas alarma y emergencia -->
    <div class="section-subtitle">SISTEMAS DE ALARMA Y EMERGENCIA</div>
    <table class="info-table">
        <?php if (!empty($inspeccion['sistema_alarma'])): ?><tr><td class="info-label">SISTEMA DE ALARMA</td><td><?= esc($inspeccion['sistema_alarma']) ?></td></tr><?php endif; ?>
        <?php if (!empty($inspeccion['codigos_alerta'])): ?><tr><td class="info-label">CODIGOS DE ALERTA</td><td><?= esc($inspeccion['codigos_alerta']) ?></td></tr><?php endif; ?>
        <?php if (!empty($inspeccion['energia_emergencia'])): ?><tr><td class="info-label">ENERGIA DE EMERGENCIA</td><td><?= esc($inspeccion['energia_emergencia']) ?></td></tr><?php endif; ?>
        <?php if (!empty($inspeccion['deteccion_fuego'])): ?><tr><td class="info-label">DETECCION DE FUEGO</td><td><?= esc($inspeccion['deteccion_fuego']) ?></td></tr><?php endif; ?>
        <?php if (!empty($inspeccion['vias_transito'])): ?><tr><td class="info-label">VIAS DE TRANSITO CERCANAS</td><td><?= esc($inspeccion['vias_transito']) ?></td></tr><?php endif; ?>
    </table>

    <!-- ============ LEGISLACION ============ -->
<div class="section-title">LEGISLACION</div>
    <div class="section-subtitle">LEGISLACION NACIONAL</div>
    <p class="content-text"><strong>LEY 9 DE 1979</strong> Codigo Sanitario Titulo III: Relativo a la salud ocupacional. Art.93 - Areas de Circulacion: Claramente demarcadas, tener amplitud suficiente para el transito seguro de las personas y provistas de senalizacion adecuada. Art.93 - Puertas de Salida: En numero suficiente y de caracteristicas apropiadas para facilitar la evacuacion del personal en caso de emergencia, las cuales no podran mantenerse obstruidas o con seguro durante la jornada de trabajo. Art.114 - Prevencion y Extincion de Incendios: Disponer de personal capacitado, metodos, equipos y materiales adecuados y suficientes. Art. 116 - Equipos y dispositivos para la Extincion de Incendios: Con diseno, construccion y mantenimiento que permita su uso inmediato con la maxima eficiencia. Art. 117 - Equipos, herramientas, instalaciones y redes electricas: Disenados, construidos, instalados, mantenidos, accionados y senalizados de manera que prevenga los riesgos de incendio o contacto con elementos sometidos a tension. Art. 127 - Todo lugar de trabajo tendra las facilidades y los recursos necesarios para la prestacion de los primeros auxilios a los trabajadores.</p>
    <p class="content-text"><strong>LEY 9 DE 1979</strong> Codigo Sanitario Titulo VIII - Desastres. Art 501 - Cada Comite de Emergencias debera elaborar un plan de contingencia para su respectiva jurisdiccion con los resultados obtenidos en los analisis de vulnerabilidad. Art 502 - El Ministerio de Salud coordinara los programas de entrenamiento y capacitacion para planes de contingencia en los aspectos sanitarios vinculados a urgencias o desastres.</p>
    <p class="content-text"><strong>RESOLUCION 2400 DE 1979</strong> "Por el cual se establecen disposiciones sobre vivienda, higiene y seguridad industrial en los establecimientos de trabajo". Art. 2 - Todos los empleadores estan obligados a Organizar y desarrollar programas permanentes de Medicina Preventiva, Higiene y Seguridad Industrial. Art. 4 - Edificios y Locales: Construccion segura y firme. Art. 14 - Escaleras de Comunicacion entre plantas del edificio: Espaciosas, con condiciones de solidez, estabilidad y seguridad. Art. 205 - Peligro de Incendio o explosion en centros de trabajo: Provistos de tomas de agua con sus correspondientes mangueras, tanques de reserva y extintores. Art. 206 - Construcciones bajo riesgo de Incendio o Explosion: Dotadas de muros corta-fuegos. Art. 207 - Salidas de Emergencia: Suficientes, libres de obstaculos y convenientemente construidas. Art. 220 - Extintores: Adecuados segun combustible utilizado y clase de incendio. Art. 223 - Brigada contra Incendio: Debidamente entrenada y preparada.</p>
    <p class="content-text"><strong>CONPES 3146 de 2001</strong> Estrategia para consolidar la ejecucion del Plan Nacional para la Prevencion y Atencion de Desastres - PNPAD, en el corto y mediano plazo.</p>
    <p class="content-text"><strong>LEY 46/88</strong> "Por la cual se crea y organiza el Sistema Nacional para la Prevencion y Atencion de Desastres". Art. 3 - Plan Nacional para la Prevencion y Atencion de Desastres. El Plan incluira y determinara todas las orientaciones, acciones, programas y proyectos referidos a: las fases de prevencion, atencion inmediata, reconstruccion y desarrollo; los temas de orden tecnico, cientifico, economico; la educacion, capacitacion y participacion comunitaria; los sistemas integrados de informacion y comunicacion; la coordinacion interinstitucional e intersectorial; y los sistemas y procedimientos de control y evaluacion. Art. 14 - Plan de Accion Especifico para la Atencion de Desastre.</p>
    <p class="content-text"><strong>DECRETO LEY 919/89</strong> "Por el cual se organiza el Sistema Nacional para la Prevencion y Atencion de Desastres". Art. 3 - Plan Nacional para la Prevencion y Atencion de Desastres. Art. 13 - Planes de contingencia: Los Comites elaboraran, con base en los analisis de vulnerabilidad, planes de contingencia para facilitar la prevencion o para atender adecuada y oportunamente los desastres probables. Art. 14 - Aspectos sanitarios de los planes de contingencia.</p>
    <p class="content-text"><strong>DECRETO 1295/94</strong> "Por el cual se determina la organizacion y administracion del Sistema General de Riesgos Profesionales". Art. 2 - Establecer las actividades de promocion y prevencion tendientes a mejorar las condiciones de trabajo y salud de la poblacion trabajadora.</p>
    <p class="content-text"><strong>LEY 322 DE 1996</strong> SISTEMA NACIONAL DE BOMBEROS. Art. 1 - La prevencion de incendios es responsabilidad de todas las autoridades y los habitantes del territorio colombiano. En cumplimiento de esta responsabilidad los organismos publicos y privados deberan contemplar la contingencia de este riesgo en los bienes inmuebles.</p>
    <p class="content-text"><strong>LEY 769 DE 2002</strong> CODIGO NACIONAL DE TRANSITO. Art. 1 - Las normas regulan la circulacion de los peatones, usuarios, pasajeros, conductores, motociclistas, ciclistas, agentes de transito y vehiculos por las vias publicas o privadas que esten abiertas al publico.</p>
    <p class="content-text"><strong>DECRETO No. 3888/07</strong> Plan Nacional de Emergencias y Contingencia para Eventos de Afluencia Masiva de Publico. Art. 2 - Servir como instrumento rector para el diseno y realizacion de actividades dirigidas a prevenir, mitigar y dotar al Sistema Nacional de una herramienta que permita coordinar y planear el control y atencion de riesgos. Art. 20 - Planes institucionales: Los organismos operativos elaboraran sus propios planes institucionales para la atencion de eventos de afluencia masiva de publico.</p>
    <p class="content-text"><strong>DECRETO 1347 DE 2021</strong> Programa de Prevencion de Accidentes Mayores - PPAM.</p>
    <p class="content-text"><strong>DECRETO 4272 DE 2021</strong> Requisitos minimos de seguridad para trabajo en alturas.</p>
    <p class="content-text"><strong>RESOLUCION 20223040040595 DE 2022</strong> Metodologia para el diseno, implementacion y verificacion de los Planes Estrategicos de Seguridad Vial.</p>
    <p class="content-text"><strong>DECRETO 1478 DE 2022</strong> Actualizacion del Plan Nacional de Gestion del Riesgo de Desastres.</p>

    <div class="section-subtitle">LEGISLACION DISTRITAL</div>
    <p class="content-text"><strong>RESOLUCION 1428 DE 2002</strong> "Por la cual se adoptan los Planes Tipo de Emergencias en seis escenarios Distritales, se modifica y adiciona la Resolucion 0151 del 06 de febrero de 2002".</p>
    <p class="content-text"><strong>DECRETO 332/04</strong> "Por el cual se organiza el regimen y el Sistema para la Prevencion y Atencion de Emergencias". Art. 7 - Planes de Emergencias: Se adoptaran para cada una de las entidades y comites sectoriales. Art. 8 - Planes de Contingencia.</p>
    <p class="content-text"><strong>DECRETO 423/06</strong> "Por el cual se adopta el Plan Distrital para la prevencion y Atencion de Emergencias". Art. 18 - Planes de Emergencias: Instrumentos para la coordinacion general y actuacion frente a situaciones de calamidad, desastre o emergencia. Art. 19 - Planes de Contingencia.</p>
    <p class="content-text"><strong>RESOLUCION No. 375/06</strong> Condiciones basicas para la copropiedad que prestan el servicio de logistica en las aglomeraciones de publico.</p>
    <p class="content-text"><strong>RESOLUCION No. 137/07</strong> Parametros e instrucciones para la administracion de emergencias en Bogota - Plan de Emergencias de Bogota.</p>
    <p class="content-text"><strong>DECRETO 633/07</strong> Disposiciones en materia de prevencion de riesgos en los lugares donde se presenten aglomeraciones de publico. Art. 5 - Planes de Contingencia.</p>

    <div class="section-subtitle">NORMAS TECNICAS COLOMBIANAS</div>
    <p class="content-text"><strong>NTC-5254:</strong> Gestion de Riesgo. Guia Tecnica Colombiana 202/06: Sistema de Gestion de Continuidad del Negocio.</p>
    <p class="content-text"><strong>NTC-1700:</strong> Higiene y Seguridad. Medidas de Seguridad en Edificaciones. Medios de Evacuacion y Codigo NFPA 101. Establece los requerimientos que debe cumplir las edificaciones en cuanto a salidas de evacuacion, escaleras de emergencia, iluminacion de evacuacion, sistema de proteccion especiales, numero de personas maximo por unidad de area.</p>
    <p class="content-text"><strong>NTC-2885:</strong> Higiene y Seguridad. Extintores Portatiles. Establece los requisitos para la inspeccion y mantenimiento de portatiles.</p>
    <p class="content-text"><strong>NTC-4764:</strong> Cruces peatonales a nivel y elevados o puentes peatonales. <strong>NTC-4140:</strong> Edificios - Pasillos y corredores. <strong>NTC-4143:</strong> Edificios - Rampas fijas. <strong>NTC-4144:</strong> Edificios - Senalizacion. <strong>NTC-4145:</strong> Edificios - Escaleras. <strong>NTC-4201:</strong> Edificios - Equipamientos, bordillos, pasamanos y agarraderas. <strong>NTC-4279:</strong> Vias de circulacion peatonal planas. <strong>NTC-4695:</strong> Senalizacion para transito peatonal. <strong>NTC-2388:</strong> Simbolos para la informacion del publico. <strong>NTC-1867:</strong> Sistemas de senales contra incendio.</p>
    <p class="content-text"><strong>NFPA 101/06:</strong> Life Safety Code (Codigo de Seguridad Humana). <strong>NFPA 1600/07:</strong> Standard on Disaster/Emergency Management and Business Continuity Programs (Norma sobre manejo de Desastres, Emergencias y Programas para la Continuidad del Negocio).</p>

    <!-- ============ ANALISIS DE RIESGOS ============ -->
<div class="section-title">REALIZACION DEL ANALISIS DE RIESGOS</div>
    <p class="content-text">Objetivo: Identificar y evaluar cuales son aquellos eventos o condiciones que pueden llegar a ocasionar una emergencia en <?= $nombreCliente ?>, de tal manera que este analisis se convierta en una herramienta para establecer las medidas de prevencion y control de los riesgos asociados.</p>

    <div class="section-subtitle">IDENTIFICACION Y CARACTERIZACION DE PELIGROS Y AMENAZAS</div>
    <table class="data-table">
        <thead><tr><th style="width:25%;">ORIGEN</th><th>PELIGRO</th></tr></thead>
        <tbody>
            <tr><td rowspan="3" style="font-weight:bold; text-align:center; background:#f0f0f0;">NATURAL</td><td>Presencia de una falla geologica (Terremotos, sismos)</td></tr>
            <tr><td>Condiciones atmosfericas adversas a la zona (inundaciones, vendavales)</td></tr>
            <tr><td>Incendios Forestales</td></tr>
            <tr><td rowspan="2" style="font-weight:bold; text-align:center; background:#f0f0f0;">SOCIAL</td><td>Condiciones sociales insatisfechas (atentados terroristas, amenazas)</td></tr>
            <tr><td>Condiciones politicas y sociales de la region (robos)</td></tr>
            <tr><td rowspan="5" style="font-weight:bold; text-align:center; background:#f0f0f0;">TECNOLOGICO</td><td>Presencia copropiedades vecinas (Explosiones, incendios)</td></tr>
            <tr><td>Almacenamiento de gases toxicos (fugas de sustancias nocivas)</td></tr>
            <tr><td>Inflamabilidad de una sustancia (incendios, explosiones)</td></tr>
            <tr><td>Presencia Aeropuerto (paso de aviones)</td></tr>
            <tr><td>Movilidad vehiculos automotores</td></tr>
        </tbody>
    </table>

    <!-- Probabilidad de ocurrencia (datos de la inspeccion previa) -->
    <?php if ($ultimaProb): ?>
    <div class="section-subtitle">PROBABILIDAD DE OCURRENCIA DE LOS PELIGROS</div>
    <?php
    $freqLabels = ['poco_probable' => 'POCO PROBABLE', 'probable' => 'PROBABLE', 'muy_probable' => 'MUY PROBABLE'];
    $freqClasses = ['poco_probable' => 'freq-poco', 'probable' => 'freq-probable', 'muy_probable' => 'freq-muy'];
    $probFields = [
        'NATURALES' => [
            'p_sismos' => 'Sismos, caida de estructuras',
            'p_inundaciones' => 'Inundaciones',
            'p_vendavales' => 'Vendavales, granizada, tormentas electricas',
        ],
        'SOCIALES' => [
            'p_atentados' => 'Atentados terroristas',
            'p_asalto_hurto' => 'Asalto, hurto',
            'p_vandalismo' => 'Vandalismo',
        ],
        'TECNOLOGICOS' => [
            'p_incendios' => 'Incendios',
            'p_explosiones' => 'Explosiones',
            'p_inhalacion_gases' => 'Inhalacion de gases',
            'p_falla_estructural' => 'Falla estructural',
            'p_intoxicacion_alimentos' => 'Intoxicacion por alimentos',
            'p_densidad_poblacional' => 'Densidad poblacional',
        ],
    ];
    ?>
    <table class="data-table">
        <thead><tr><th style="width:18%;">ORIGEN</th><th style="width:42%;">TIPO</th><th style="width:40%;">FRECUENCIA</th></tr></thead>
        <tbody>
        <?php foreach ($probFields as $origen => $items):
            $count = count($items);
            $first = true;
            foreach ($items as $key => $label):
                $val = $ultimaProb[$key] ?? null;
                $freqClass = $val ? ($freqClasses[$val] ?? '') : '';
                $freqLabel = $val ? ($freqLabels[$val] ?? '-') : '-';
        ?>
            <tr>
                <?php if ($first): ?>
                <td rowspan="<?= $count ?>" style="font-weight:bold; vertical-align:middle; text-align:center; background:#f0f0f0;"><?= $origen ?></td>
                <?php $first = false; endif; ?>
                <td><?= $label ?></td>
                <td class="<?= $freqClass ?>"><?= $freqLabel ?></td>
            </tr>
        <?php endforeach; endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- Descripciones de riesgos (texto estatico) -->
<div class="section-subtitle">RIESGOS NATURALES</div>
    <p class="content-text"><strong>SISMOS, CAIDA DE ESTRUCTURAS:</strong> Ninguna edificacion, aun las construidas recientemente, se encuentran exentas de ser afectadas por la accion de las vibraciones derivadas del choque de las placas que forman la superficie de la tierra y que se mueven continuamente en direcciones diferentes acumulando y liberando energia que sacude la superficie, fenomeno que se conoce como terremoto, sismo o temblor de tierra. La magnitud e intensidad, las caracteristicas del suelo (suelos blandos o rellenos pueden aumentar la capacidad destructiva en determinadas edificaciones), la resistencia de las edificaciones (una falla estructural de planta fisica puede hacer colapsar de manera parcial o total estructuras de la edificacion con alteracion directa de su capacidad portante y dano a sus elementos y ocupantes), y la preparacion que se tenga por parte de las personas e instituciones para actuar y reaccionar en forma adecuada, antes, durante y despues del fenomeno, dependen los danos que este cause.<br>CLASIFICACION DEL RIESGO: PROBABLE</p>
    <p class="content-text"><strong>VENDAVALES, GRANIZADA Y TORMENTAS ELECTRICAS:</strong> Los cambios climaticos y meteorologicos se pueden encontrar acompanados de vientos, lluvias, granizadas, tormentas electricas. La accion de vientos fuertes no solo puede romper ventanales y levantar tejas en las cubiertas, sino hacer caer antenas y pararrayos. Las tormentas electricas cuando no existe la proteccion de las edificaciones e instalaciones con pararrayos debidamente conectados a tierra pueden traer como consecuencia accidentes fatales de trabajadores y la probabilidad de incendios con perdidas materiales.<br>CLASIFICACION DEL RIESGO: POCO PROBABLE</p>
    <p class="content-text"><strong>INUNDACIONES:</strong> Se presentan generalmente despues de una lluvia fuerte o una granizada, por sustraccion de drenajes, por taponamiento de sifones, de desagues o de bajantes de canales; cuando se presenta acumulacion de residuos o basuras o por diametros muy reducidos de los tubos de la caneria; por mala inclinacion de los desniveles hacia los respectivos desagues, o por estar la edificacion en zonas bajas inundables como cerca de rios, lagos o por estar construida en zonas pantanosas. Por presentarse en la capital epocas de fuertes y prolongados inviernos, es muy probable que se presenten este tipo de amenazas.<br>CLASIFICACION DEL RIESGO: POCO PROBABLE</p>

    <div class="section-subtitle">RIESGOS TECNOLOGICOS</div>
    <p class="content-text"><strong>INCENDIO:</strong> Entre las amenazas mas importantes se hace referencia a las de incendio, la cual es caracteristica de toda edificacion cuya destinacion sea de caracter industrial, comercial, de servicios o residencia. Esta amenaza no solamente se presenta por una eventual vecindad a fuentes de ignicion o detonacion, fuentes de calor, fuentes electricas, presencia de cargas estaticas y tambien por diferentes cargas combustibles de materiales solidos presentes en las instalaciones del conjunto residencial y a los trabajos que en el se realicen. Debido a que en el conjunto se almacenan diferentes combustibles como vestuario, telones, madera, alfombras, carton, plasticos, equipos de oficina; presencia de gas natural y demas combustibles que pueden ocasionar un incendio de grandes proporciones.<br>CLASIFICACION DEL RIESGO: MUY PROBABLE</p>
    <p class="content-text"><strong>EXPLOSION:</strong> Es un riesgo que viene relacionado con el manejo de cargas combustibles del tipo B como el almacenamiento y manipulacion de liquidos y gases inflamables, la reactividad por escape de gases comprimidos como el caso de gas natural, y en el manejo de solventes, lacas, pinturas, Varsol que normalmente emanan gases con propiedades inflamables detonantes lo mismo que eventualmente lo podria hacer pero con menos posibilidad el ACPM.<br>CLASIFICACION DEL RIESGO: MUY PROBABLE</p>
    <p class="content-text"><strong>FALLA ESTRUCTURAL:</strong> La vulnerabilidad estructural se encuentra determinada por la capacidad de soporte vertical y resistencia a cargas horizontales de la edificacion, la cual en terminos generales presenta buen aspecto. Un gran porcentaje de las instalaciones esta construido en muros de ladrillo y cemento, pisos en cemento, techos en placa de concreto. Con el objeto de determinar la capacidad sismo resistente de las edificaciones se recomienda realizar un estudio tecnico de las mismas y conforme a su valoracion reforzar las estructuras o realizar las modificaciones arquitectonicas necesarias, en consonancia con las exigencias del codigo colombiano de construcciones sismo resistentes, adoptado por el Decreto 400 con vigencia desde el ano de 1984 y actualizado por la Ley 400 de 1997 y el Decreto 33 de 1998.<br>CLASIFICACION DEL RIESGO: POCO PROBABLE</p>
    <p class="content-text"><strong>INTOXICACIONES POR INHALACION DE VAPORES:</strong> Estas afectaciones en la salud se pueden causar debido a la acumulacion de gases nocivos para las personas, esto se puede agravar en el caso del parqueadero de vehiculos del conjunto residencial si no se cuenta con la cultura de la revision periodica de los vehiculos automotores y si las personas se quedan bajo periodos largos en este sitio, para lo cual se hace necesario implementar la cultura de esperar que los propietarios de los vehiculos realicen el calentamiento del motor en un area ventilada.<br>CLASIFICACION DEL RIESGO: POCO PROBABLE</p>

    <div class="section-subtitle">RIESGOS SOCIALES</div>
    <p class="content-text"><strong>VANDALISMO:</strong> Por la gran descomposicion social que se vive hoy en dia esta es una de las amenazas con un riesgo de probabilidad considerable sin que tenga que ver el tipo de copropiedad o area habitacional que pueda ser afectada es simplemente el deseo de producir panico y sembrar el miedo entre la poblacion. La probabilidad que suceda esta amenaza es poco probable debido a la ubicacion que presenta el conjunto residencial, sin embargo, se debe considerar que existen muchas formas de hacerlo: a traves de paquetes, de sobres, de vehiculos y de variedad de articulos incluyendo extintores.<br>CLASIFICACION DEL RIESGO: PROBABLE</p>
    <p class="content-text"><strong>ATENTADOS TERRORISTAS:</strong> En este se incluyen aquellas acciones en que ademas de bombas, o proyectiles dirigidos desde cierta distancia hacia algun objetivo en particular y que generalmente puede afectar instalaciones o viviendas aledanas y ajenas a las que se proponlan hacer dano sin que por eso importe algo tambien puede tratarse de acciones de que inciten a sembrar terror en la poblacion esto puede incluir acciones como el secuestro ya sea por grupos organizados como la guerrilla o bandas criminales.<br>CLASIFICACION DEL RIESGO: PROBABLE</p>
    <p class="content-text"><strong>ASALTO Y HURTO:</strong> Existe la posibilidad de tener este riesgo principalmente en horas nocturnas ocasionado por la gran inseguridad que se presenta en la actualidad en el Distrito Capital, pero es importante acotar que el conjunto residencial cuenta con un sistema de vigilancia privado contratado para salvaguardar los bienes y servicios de los residentes pero esto se limita solo a la propiedad horizontal, esto no desconoce la problematica que se presenta en las areas perimetrales del sector del conjunto residencial.<br>CLASIFICACION DEL RIESGO: PROBABLE</p>

    <!-- ============ CARGA COMBUSTIBLE ============ -->
<div class="section-title">CARGA COMBUSTIBLE</div>
    <p class="content-text">La edificacion presenta diferentes tipos de material combustible, segun sus caracteristicas:</p>
    <div class="section-subtitle">CLASE A</div>
    <p class="content-text">Papel: en documentos, informes, papeleria, archivo en oficinas administrativas (facturacion, contabilidad, tesoreria y recepcion) y demas documentos que hagan parte del personal administrativo y de los residentes.<br>Carton: En los puntos de acopio de los residuos solidos, y en los diferentes empaques que se encuentren en el conjunto residencial.<br>Telas: En apartamentos, salones de recepcion y areas de atencion al publico dentro del conjunto residencial.<br>Madera: en muebles, sillas, puertas, divisiones, en las areas de los apartamentos, areas de recreacion, administracion y recepcion.<br>Materiales acrilicos: en computadores, impresoras, telefonos, calculadoras.<br>Cuero: en algunas sillas de los apartamentos.</p>
    <div class="section-subtitle">CLASE B</div>
    <p class="content-text">Solventes, pinturas, esmaltes, vinilos, quimicos (area piscina y/o contratistas de aseo y mantenimiento).<br>Liquidos inflamables (Gasolina, ACPM).<br>Gas natural el cual se distribuye a todos los apartamentos y demas equipos que funcionan en el conjunto residencial.</p>
    <div class="section-subtitle">CLASE C</div>
    <p class="content-text">Redes electricas energizadas, tomas, interruptores y luminarias en todas las instalaciones. Cuarto de contadores en el parqueadero.<br>Computadores, impresoras, telefonos, televisores, DVD entre otros. Area de materiales con los que se realizan los arreglos locativos. Equipos de bombeo del agua.<br>Planta electrica en el parqueadero.</p>

    <div class="section-subtitle">RECOMENDACIONES SEGUN EL TIPO DE COMBUSTIBLE</div>
    <p class="content-text">Las siguientes recomendaciones estan encaminadas a disminuir el riesgo de presentarse una emergencia en el conjunto residencial, la cual debe ser aplicada por todo el personal que reside y realiza diferentes actividades propias o derivadas de su oficio.</p>
    <p class="content-text"><strong>PARA RIESGO CLASE A:</strong><br>Evitar cajas con papeleria y documentos bajo las mesas. Utilizar archivadores y bibliotecas unicamente.<br>No almacenar cajas con material tipo A cerca de bombillos incandescentes (minimo 50 CMS de distancia).<br>No dejar trapos, pedazos de estopa con grasa, cera o Varsol por fuera de recipientes metalicos cerrados.<br>No dejar cerca de estufas, grecas, hornos o cafeteras prendidas: limpiones, trapos o coge ollas.<br>No usar papeles para encender la estufa.<br>No almacenar papeles impregnados de liquidos inflamables (Gasolina, ACPM, Varsol, Pinturas, Grasa y demas elementos que pueda ocasionar una ignicion o incendio.</p>
    <p class="content-text"><strong>PARA RIESGOS CLASE B:</strong><br>Disponer de un lugar ventilado y en buenas condiciones de orden y aseo para almacenar todos los elementos que se requieren en el conjunto residencial para labores de mantenimiento.<br>Guardar todos los materiales inflamables en recipientes hermeticos y dentro de gabinetes metalicos con puerta.<br>Todo recipiente que contenga algun liquido inflamable debe encontrarse rotulado especificando su nombre comercial y en lo posible las fichas de seguridad de los productos.<br>Mantener materiales inflamables en lugares aireados y alejados de fuentes de calor o de tomas o instalaciones electricas de riesgo.<br>Evitar el uso de gas propano en areas cerradas.<br>Se debe tener un kit anti derrames en el conjunto en caso de presentarse un derrame especialmente en el area del parqueadero.</p>
    <p class="content-text"><strong>PARA RIESGOS CLASE C:</strong><br>Evitar el uso de elementos para produccion de calor en areas donde pueda acumularse material combustible como papel, plasticos, telas y madera principalmente, y dejarlos desconectados en horas de la noche, los equipos electricos.<br>Identificar cajas de tacos de corriente en todos los lugares donde se ubiquen.<br>Restringir la entrada a las areas de cuartos electricos de mediana o alta tension.<br>Realizar revisiones periodicas de posibles humedades que se encuentren en cercania a algun elemento electrico esto se puede presentar en las areas comunes como al interior de los apartamentos o casas.</p>

    <!-- ============ PON CODIGO 7 ============ -->
<div class="section-title">PROCEDIMIENTO OPERATIVO NORMALIZADO (PON) - CODIGO 7</div>
    <div class="section-subtitle">Falla de ascensor con personas en su interior</div>
    <p class="content-text"><strong>Codigo de Emergencia:</strong> CODIGO 7 - Persona(s) atrapada(s) en ascensor</p>
    <p class="content-text"><strong>Introduccion:</strong> En edificaciones residenciales y comerciales que cuentan con ascensores, es posible que se presenten fallas tecnicas, cortes electricos u otros incidentes que provoquen la detencion del equipo con ocupantes en su interior. Este procedimiento operativo establece las acciones especificas para responder de manera rapida, segura y coordinada, minimizando riesgos y evitando danos fisicos o psicologicos a las personas involucradas.</p>
    <p class="content-text"><strong>Objetivo:</strong> Establecer el procedimiento seguro y estandarizado para la atencion de emergencias por fallas de ascensor con personas atrapadas, asegurando la proteccion de la vida, la salud y la integridad de los ocupantes, asi como la coordinacion con organismos de socorro y personal tecnico especializado.</p>
    <p class="content-text"><strong>Alcance:</strong> Aplica para todo el personal de vigilancia, administracion, brigadas de emergencia, personal de mantenimiento y demas personas que participen en la atencion de emergencias dentro del conjunto residencial o edificio.</p>
    <p class="content-text"><strong>Definiciones clave:</strong><br>Falla de ascensor: Cese repentino o irregular del funcionamiento del ascensor por razones mecanicas, electricas o electronicas.<br>Rescate tecnico: Intervencion de personal calificado para liberar de forma segura a las personas atrapadas.<br>Emergencia critica: Situacion en la que la vida o la salud de los ocupantes esta en riesgo inmediato.</p>
    <p class="content-text"><strong>Responsables de la ejecucion:</strong> Personal de vigilancia. Administrador del conjunto. Brigada de emergencias (si aplica). Empresa mantenedora del ascensor. Organismos de socorro (Bomberos, Defensa Civil, etc., si es necesario).</p>
    <p class="content-text"><strong>Procedimiento:</strong></p>
    <p class="content-text"><strong>1. Activacion del Codigo 7:</strong> Ante el aviso de personas atrapadas, el personal que reciba la alerta debe anunciar por el canal de comunicacion interna: "Codigo 7 activo en el piso y lugar donde se presento la novedad". Registrar la hora y ubicacion exacta en la minuta de seguridad.</p>
    <p class="content-text"><strong>2. Evaluacion inicial:</strong> Confirmar cuantas personas estan atrapadas. Identificar si hay menores de edad, personas con discapacidad, adultos mayores o embarazadas. Determinar si existe riesgo inminente (falta de aire, humo, fuego, agua, lesiones graves).</p>
    <p class="content-text"><strong>3. Comunicacion con los ocupantes:</strong> Mantener contacto verbal desde el exterior o por intercomunicador. Indicar que el rescate esta en proceso. Recomendar mantener la calma, no forzar puertas ni intentar salir por sus medios.</p>
    <p class="content-text"><strong>4. Corte de energia:</strong> Desconectar el ascensor desde el tablero electrico, unicamente si se tiene claridad y autorizacion para hacerlo. Esto evita movimientos involuntarios durante el rescate.</p>
    <p class="content-text"><strong>5. Notificacion inmediata a:</strong> Empresa de mantenimiento (numero visible en porteria). Administracion del conjunto. Bomberos, si hay riesgo vital o el tiempo de espera excede 20 minutos.</p>
    <p class="content-text"><strong>6. Esperar personal capacitado:</strong> No realizar maniobras improvisadas de apertura. Asegurar que los ocupantes cuenten con ventilacion adecuada hasta que llegue el personal tecnico.</p>
    <p class="content-text"><strong>7. Rescate asistido (solo con personal autorizado):</strong> Coordinado por empresa mantenedora o bomberos. Verificar alineacion del ascensor con el piso antes de abrir puertas. Garantizar que las personas salgan de forma controlada.</p>
    <p class="content-text"><strong>8. Atencion posterior al rescate:</strong> Verificar condicion fisica y emocional de los ocupantes. Ofrecer primeros auxilios y asistencia psicologica si es necesario.</p>
    <p class="content-text"><strong>9. Cierre del evento:</strong> Levantar informe detallado con hora de inicio, tiempo de respuesta, personal interviniente, causa probable y medidas correctivas. Prohibir el uso del ascensor hasta que la empresa mantenedora emita certificacion de funcionamiento seguro.</p>
    <p class="content-text"><strong>Medidas preventivas:</strong><br>Mantener al dia el mantenimiento preventivo y correctivo del ascensor.<br>Contar con senalizacion visible de contacto de emergencia.<br>Capacitar al personal sobre este PON al menos una vez al ano.<br>Incluir simulacros de procedimiento en el plan de emergencias.</p>
    <p class="content-text"><strong>Recomendaciones:</strong><br>No permitir que personal no capacitado intente rescates.<br>Garantizar que el tablero de codigos de emergencia incluya: "Codigo 7 - Falla de ascensor con personas en su interior".<br>Verificar periodicamente la operatividad del sistema de comunicacion interna del ascensor.</p>

    <!-- ============ DIAGRAMA DE ACTUACION EN EMERGENCIAS ============ -->
    <?php if (!empty($diagramaBase64)): ?>
<div class="section-title">DIAGRAMA DE ACTUACION EN CASO DE EMERGENCIA</div>
    <p class="content-text">El siguiente diagrama de flujo establece el protocolo general de actuacion ante diferentes tipos de emergencia que puedan presentarse en la propiedad horizontal. Permite identificar rapidamente las acciones a seguir segun el tipo de evento.</p>
    <div style="text-align: center; margin: 15px 0;">
        <img src="<?= $diagramaBase64 ?>" style="max-width: 100%; max-height: 700px;">
    </div>
    <?php endif; ?>

    <!-- ============ ANEXOS - EVALUACIONES DE SEGURIDAD ============ -->
<div class="annex-title">ANEXOS - EVALUACIONES DE SEGURIDAD</div>
    <p class="content-text">La gestion eficiente de la seguridad en propiedades horizontales requiere un enfoque integral que permita identificar y mitigar los riesgos. Cycloid Talent SAS ha llevado a cabo una revision exhaustiva de los principales elementos de seguridad necesarios para la creacion de este Plan de Emergencias.</p>

    <!-- ANEXO: INSPECCION LOCATIVA -->
    <?php if ($ultimaLocativa && !empty($hallazgosLocativa)): ?>
    <div class="section-title">INSPECCION LOCATIVA GENERAL</div>
    <p class="content-text">La inspeccion general se refiere a la revision periodica de todas las areas comunes con el fin de identificar posibles riesgos para la seguridad de los residentes, visitantes y trabajadores.</p>
    <table class="data-table">
        <thead><tr><th style="width:60%;">HALLAZGO IDENTIFICADO</th><th style="width:20%;">FECHA</th><th style="width:20%;">IMAGEN</th></tr></thead>
        <tbody>
        <?php foreach ($hallazgosLocativa as $h): ?>
        <tr>
            <td><?= esc($h['descripcion_imagen'] ?? '') ?></td>
            <td style="text-align:center;"><?= !empty($h['fecha_registro']) ? date('d/m/Y', strtotime($h['fecha_registro'])) : '-' ?></td>
            <td style="text-align:center;">
            <?php if (!empty($h['imagen'])):
                $hFoto = FCPATH . $h['imagen'];
                if (file_exists($hFoto)):
                    $hMime = mime_content_type($hFoto);
                    $hB64 = 'data:' . $hMime . ';base64,' . base64_encode(file_get_contents($hFoto));
            ?>
                <img src="<?= $hB64 ?>" style="max-width:80px; max-height:60px;">
            <?php endif; endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- ANEXO: MATRIZ VULNERABILIDAD -->
    <?php if ($ultimaMatriz): ?>
<div class="section-title">MATRIZ DE VULNERABILIDAD</div>
    <p class="content-text">La matriz de vulnerabilidad es una herramienta utilizada para evaluar los riesgos a los que esta expuesta una copropiedad, analizando aspectos de seguridad fisica, infraestructura y procesos de mantenimiento.</p>
    <?php
    $matrizCriterios = [
        'c1_plan_evacuacion' => '1. El plan de evacuacion',
        'c2_alarma_evacuacion' => '2. Alarma para evacuacion',
        'c3_ruta_evacuacion' => '3. Ruta de evacuacion',
        'c4_visitantes_rutas' => '4. Los visitantes conocen las rutas de evacuacion',
        'c5_puntos_reunion' => '5. Los puntos de reunion en una evacuacion',
        'c6_puntos_reunion_2' => '6. Los puntos de reunion (parte 2)',
        'c7_senalizacion_evacuacion' => '7. La senalizacion para evacuacion',
        'c8_rutas_evacuacion' => '8. Las rutas de evacuacion son',
        'c9_ruta_principal' => '9. La ruta principal de evacuacion',
        'c10_senal_alarma' => '10. La senal de alarma',
        'c11_sistema_deteccion' => '11. Sistema de deteccion',
        'c12_iluminacion' => '12. El sistema de iluminacion',
        'c13_iluminacion_emergencia' => '13. El sistema de iluminacion de emergencia',
        'c14_sistema_contra_incendio' => '14. El sistema contra incendio',
        'c15_extintores' => '15. Los extintores para incendio',
        'c16_divulgacion_plan' => '16. Divulgacion del plan de emergencia',
        'c17_coordinador_plan' => '17. Coordinador del plan de emergencia',
        'c18_brigada_emergencia' => '18. La brigada de emergencia',
        'c19_simulacros' => '19. Se han realizado simulacros',
        'c20_entidades_socorro' => '20. Entidades de socorro externas',
        'c21_ocupantes' => '21. Los ocupantes del conjunto son',
        'c22_plano_evacuacion' => '22. En la entrada del conjunto o en cada piso',
        'c23_rutas_circulacion' => '23. Las rutas de circulacion',
        'c24_puertas_salida' => '24. Las puertas de salida del conjunto',
        'c25_estructura_construccion' => '25. Estructura y tipo de construccion',
    ];
    $puntajes = ['a' => 1.0, 'b' => 0.5, 'c' => 0.0];
    $sumaMatriz = 0;
    foreach ($matrizCriterios as $k => $l) {
        $v = $ultimaMatriz[$k] ?? null;
        $sumaMatriz += $v ? ($puntajes[$v] ?? 0) : 0;
    }
    $puntajeTotal = $sumaMatriz * 4;
    ?>
    <table class="data-table">
        <thead><tr><th style="width:70%;">ITEM</th><th style="width:15%;">CALIFICACION</th><th style="width:15%;">PUNTAJE</th></tr></thead>
        <tbody>
        <?php foreach ($matrizCriterios as $key => $label):
            $val = $ultimaMatriz[$key] ?? null;
            $ptje = $val ? ($puntajes[$val] ?? 0) : 0;
            $cellClass = $val ? ('opt-' . $val) : '';
        ?>
        <tr>
            <td><?= $label ?></td>
            <td class="<?= $cellClass ?>" style="text-align:center;"><?= $val ? strtoupper($val) : '-' ?></td>
            <td style="text-align:center; font-weight:bold;"><?= number_format($ptje, 1) ?></td>
        </tr>
        <?php endforeach; ?>
        <tr style="background:#f0f0f0;">
            <td style="font-weight:bold;">RESULTADO DE LA EVALUACION</td>
            <td colspan="2" style="text-align:center; font-weight:bold; font-size:10px;"><?= number_format($puntajeTotal, 1) ?> / 100</td>
        </tr>
        </tbody>
    </table>
    <?php if (!empty($ultimaMatriz['observaciones'])): ?>
    <p class="content-text"><strong>Observaciones del consultor:</strong> <?= nl2br(esc($ultimaMatriz['observaciones'])) ?></p>
    <?php endif; endif; ?>

    <!-- ANEXO: EXTINTORES -->
    <?php if ($ultimaExt): ?>
<div class="section-title">REVISION DE EXTINTORES</div>
    <p class="content-text">Los extintores portatiles contra incendios son un equipo esencial para la seguridad. En caso de incendio, un extintor portatil puede ayudar a controlar o extinguir el fuego, lo que puede salvar vidas y proteger la propiedad.</p>
    <table class="info-table">
        <tr><td class="info-label">FECHA DE LA INSPECCION</td><td><?= !empty($ultimaExt['fecha_inspeccion']) ? date('d/m/Y', strtotime($ultimaExt['fecha_inspeccion'])) : '-' ?></td></tr>
        <tr><td class="info-label">FECHA DE VENCIMIENTO GLOBAL</td><td><?= esc($ultimaExt['fecha_vencimiento_global'] ?? '-') ?></td></tr>
        <tr><td class="info-label">NUMERO TOTAL DE EXTINTORES</td><td><?= $ultimaExt['numero_extintores_totales'] ?? '-' ?></td></tr>
        <tr><td class="info-label">CANTIDAD ABC (MULTIPROPOSITO)</td><td><?= $ultimaExt['cantidad_abc'] ?? '-' ?></td></tr>
        <tr><td class="info-label">CANTIDAD CO2</td><td><?= $ultimaExt['cantidad_co2'] ?? '-' ?></td></tr>
        <tr><td class="info-label">CANTIDAD SOLKAFLAM 123</td><td><?= $ultimaExt['cantidad_solkaflam'] ?? '-' ?></td></tr>
        <tr><td class="info-label">CANTIDAD EXTINTORES DE AGUA</td><td><?= $ultimaExt['cantidad_agua'] ?? '-' ?></td></tr>
        <tr><td class="info-label">CAPACIDAD (LIBRAS)</td><td><?= esc($ultimaExt['capacidad_libras'] ?? '-') ?></td></tr>
    </table>
    <?php if (!empty($ultimaExt['recomendaciones_generales'])): ?>
    <p class="content-text"><strong>Recomendaciones:</strong> <?= nl2br(esc($ultimaExt['recomendaciones_generales'])) ?></p>
    <?php endif; endif; ?>

    <!-- ANEXO: BOTIQUIN -->
    <?php if ($ultimaBot): ?>
<div class="section-title">REVISION DE BOTIQUIN</div>
    <p class="content-text">Los botiquines en propiedades horizontales deben estar equipados con los suministros de primeros auxilios necesarios para atender emergencias menores, garantizando una respuesta rapida ante accidentes hasta que llegue la asistencia medica profesional.</p>
    <table class="info-table">
        <tr><td class="info-label">FECHA DE LA INSPECCION</td><td><?= !empty($ultimaBot['fecha_inspeccion']) ? date('d/m/Y', strtotime($ultimaBot['fecha_inspeccion'])) : '-' ?></td></tr>
        <tr><td class="info-label">UBICACION DEL BOTIQUIN</td><td><?= esc($ultimaBot['ubicacion_botiquin'] ?? '-') ?></td></tr>
        <tr><td class="info-label">INSTALADO EN LA PARED</td><td><?= esc($ultimaBot['instalado_pared'] ?? '-') ?></td></tr>
        <tr><td class="info-label">LIBRE DE OBSTACULOS</td><td><?= esc($ultimaBot['libre_obstaculos'] ?? '-') ?></td></tr>
        <tr><td class="info-label">LUGAR VISIBLE</td><td><?= esc($ultimaBot['lugar_visible'] ?? '-') ?></td></tr>
        <tr><td class="info-label">CON SENALIZACION</td><td><?= esc($ultimaBot['con_senalizacion'] ?? '-') ?></td></tr>
        <tr><td class="info-label">ESTADO DEL BOTIQUIN</td><td><?= esc($ultimaBot['estado_botiquin'] ?? '-') ?></td></tr>
    </table>
    <?php if (!empty($ultimaBot['recomendaciones_inspeccion'])): ?>
    <p class="content-text"><strong>Recomendaciones:</strong> <?= nl2br(esc($ultimaBot['recomendaciones_inspeccion'])) ?></p>
    <?php endif; endif; ?>

    <!-- ANEXO: RECURSOS SEGURIDAD -->
    <?php if ($ultimaRec): ?>
<div class="section-title">RECURSOS DE SEGURIDAD</div>
    <p class="content-text">Los recursos de seguridad incluyen equipo fisico (camaras, alarmas, cercas electricas, sistemas de control de acceso) y personal de seguridad capacitado, destinados a proteger a los residentes y garantizar el control de accesos y la vigilancia de areas comunes.</p>
    <table class="info-table">
        <tr><td class="info-label">FECHA DE LA INSPECCION</td><td><?= !empty($ultimaRec['fecha_inspeccion']) ? date('d/m/Y', strtotime($ultimaRec['fecha_inspeccion'])) : '-' ?></td></tr>
        <?php
        $recursosCampos = [
            'obs_lamparas_emergencia' => 'LAMPARAS DE EMERGENCIA',
            'obs_antideslizantes' => 'ANTIDESLIZANTES',
            'obs_pasamanos' => 'PASAMANOS',
            'obs_vigilancia_control' => 'SISTEMAS DE VIGILANCIA Y CONTROL',
            'obs_iluminacion_exterior' => 'ILUMINACION EXTERIOR',
            'obs_planes_respuesta' => 'PLANES DE RESPUESTA A EMERGENCIAS',
        ];
        foreach ($recursosCampos as $campo => $label):
            if (!empty($ultimaRec[$campo])): ?>
        <tr><td class="info-label"><?= $label ?></td><td><?= esc($ultimaRec[$campo]) ?></td></tr>
        <?php endif; endforeach; ?>
    </table>
    <?php if (!empty($ultimaRec['observaciones'])): ?>
    <p class="content-text"><strong>Observaciones:</strong> <?= nl2br(esc($ultimaRec['observaciones'])) ?></p>
    <?php endif; endif; ?>

    <!-- ANEXO: COMUNICACIONES -->
    <?php if ($ultimaCom): ?>
<div class="section-title">EQUIPOS DE COMUNICACIONES</div>
    <p class="content-text">Los equipos de comunicaciones en una copropiedad son esenciales para coordinar las actividades del personal de seguridad, administracion y mantenimiento. Incluyen radios, intercomunicadores y telefonos para comunicacion rapida y efectiva.</p>
    <table class="info-table">
        <tr><td class="info-label">FECHA DE LA INSPECCION</td><td><?= !empty($ultimaCom['fecha_inspeccion']) ? date('d/m/Y', strtotime($ultimaCom['fecha_inspeccion'])) : '-' ?></td></tr>
    </table>
    <?php if (!empty($ultimaCom['observaciones'])): ?>
    <p class="content-text"><strong>Observaciones:</strong> <?= nl2br(esc($ultimaCom['observaciones'])) ?></p>
    <?php endif; endif; ?>

    <!-- ANEXO: GABINETES (condicional) -->
    <?php if ($ultimaGab): ?>
    <div class="section-title">GABINETES CONTRA INCENDIO</div>
    <table class="info-table">
        <tr><td class="info-label">FECHA DE LA INSPECCION</td><td><?= !empty($ultimaGab['fecha_inspeccion']) ? date('d/m/Y', strtotime($ultimaGab['fecha_inspeccion'])) : '-' ?></td></tr>
    </table>
    <?php if (!empty($ultimaGab['observaciones'])): ?>
    <p class="content-text"><strong>Observaciones:</strong> <?= nl2br(esc($ultimaGab['observaciones'])) ?></p>
    <?php endif; endif; ?>

    <!-- ============ TELEFONOS DE EMERGENCIA ============ -->
<div class="section-title">TELEFONOS DE EMERGENCIA</div>
    <?php if ($ciudad): ?>
    <p class="content-text"><strong>Ciudad:</strong> <?= ucfirst($ciudad) ?></p>
    <?php if (!empty($telefonosCiudad)): ?>
    <table class="data-table">
        <thead><tr><th style="width:50%;">ENTIDAD</th><th style="width:50%;">TELEFONO</th></tr></thead>
        <tbody>
        <?php foreach ($telefonosCiudad as $entidad => $numero): ?>
        <tr><td style="font-weight:bold;"><?= esc($entidad) ?></td><td style="text-align:center;"><?= esc($numero) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; endif; ?>
    <?php if (!empty($inspeccion['cuadrante'])): ?>
    <p class="content-text"><strong>Cuadrante de policia:</strong> <?= esc($inspeccion['cuadrante']) ?></p>
    <?php endif; ?>

    <!-- GABINETES HIDRAULICOS -->
    <table class="info-table" style="margin-top:8px;">
        <tr><td class="info-label">TIENE GABINETES CON PUNTO HIDRAULICO</td><td><?= $enumSiNo[$inspeccion['tiene_gabinetes_hidraulico'] ?? ''] ?? '-' ?></td></tr>
    </table>

    <!-- ============ ADMINISTRACION Y PERSONAL ============ -->
    <div class="section-title">ADMINISTRACION Y PERSONAL</div>
    <table class="info-table">
        <tr><td class="info-label">NOMBRE DEL ADMINISTRADOR</td><td><?= esc($inspeccion['nombre_administrador'] ?? '-') ?></td></tr>
        <tr><td class="info-label">HORARIOS DE ADMINISTRACION</td><td><?= esc($inspeccion['horarios_administracion'] ?? '-') ?></td></tr>
        <tr><td class="info-label">PERSONAL DE ASEO</td><td><?= esc($inspeccion['personal_aseo'] ?? '-') ?></td></tr>
        <tr><td class="info-label">PERSONAL DE VIGILANCIA</td><td><?= esc($inspeccion['personal_vigilancia'] ?? '-') ?></td></tr>
    </table>

    <!-- ============ SERVICIOS GENERALES ============ -->
    <div class="section-title">SERVICIOS GENERALES</div>
    <table class="info-table">
        <?php if (!empty($inspeccion['ruta_residuos_solidos'])): ?>
        <tr><td class="info-label">RUTA DE RESIDUOS SOLIDOS</td><td><?= esc($inspeccion['ruta_residuos_solidos']) ?></td></tr>
        <?php endif; ?>
        <tr><td class="info-label">EMPRESA DE ASEO</td><td><?= $empresasAseo[$inspeccion['empresa_aseo'] ?? ''] ?? esc($inspeccion['empresa_aseo'] ?? '-') ?></td></tr>
        <?php if (!empty($inspeccion['servicios_sanitarios'])): ?>
        <tr><td class="info-label">SERVICIOS SANITARIOS</td><td><?= esc($inspeccion['servicios_sanitarios']) ?></td></tr>
        <?php endif; ?>
        <?php if (!empty($inspeccion['frecuencia_basura'])): ?>
        <tr><td class="info-label">FRECUENCIA RECOLECCION BASURA</td><td><?= esc($inspeccion['frecuencia_basura']) ?></td></tr>
        <?php endif; ?>
        <?php if (!empty($inspeccion['detalle_mascotas'])): ?>
        <tr><td class="info-label">DETALLE MASCOTAS</td><td><?= esc($inspeccion['detalle_mascotas']) ?></td></tr>
        <?php endif; ?>
        <?php if (!empty($inspeccion['detalle_dependencias'])): ?>
        <tr><td class="info-label">DETALLE DEPENDENCIAS</td><td><?= esc($inspeccion['detalle_dependencias']) ?></td></tr>
        <?php endif; ?>
    </table>

    <!-- ============ OBSERVACIONES Y RECOMENDACIONES ============ -->
    <?php if (!empty($inspeccion['observaciones'])): ?>
    <div class="section-title">OBSERVACIONES Y RECOMENDACIONES</div>
    <p class="content-text"><?= nl2br(esc($inspeccion['observaciones'])) ?></p>
    <?php endif; ?>

</body>
</html>
