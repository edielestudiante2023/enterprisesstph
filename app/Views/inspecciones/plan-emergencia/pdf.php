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

        .page-break { page-break-before: always; }

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
    <div class="page-break"></div>
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
    <p class="content-text">Frente a la imposibilidad de eliminar por completo la probabilidad de ocurrencia de una situacion de emergencia, se ha evidenciado la necesidad de establecer un proceso que permita contrarrestar y minimizar las consecuencias adversas que se presentan en una situacion de crisis. Este proceso es conocido como "Plan de preparacion para emergencias y contingencias".</p>
    <p class="content-text">El plan de emergencias es una herramienta que permite poner en conocimiento todos los factores de riesgo (amenaza y vulnerabilidad) frente a las personas y los bienes. Asi mismo, debe ser divulgado a todas las personas que intervienen en el, e implementarlo por medio de simulacros periodicos, por esto se hace necesaria la participacion de todos los miembros de <?= $nombreCliente ?>.</p>
    <p class="content-text"><?= $nombreCliente ?> implementara el plan de emergencias con la seguridad de que su aplicacion le permitira disponer de una herramienta de trabajo agil en la planificacion de tratamientos de emergencias. Se deberan considerar las politicas y procedimientos, ya que en algun momento cada persona tendra funciones y responsabilidades en cooperacion con la Administracion del Conjunto; debido a lo anterior, se conformaran brigadas de emergencia con sus diferentes acciones y responsabilidades.</p>

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
    <div class="page-break"></div>
    <div class="section-title">CONCEPTOS</div>
    <p class="content-text">De acuerdo con el articulo 4 de la Ley 675 de 2001 por medio de la cual se expide el Regimen de Propiedad Horizontal, cada edificio o conjunto de uso residencial debe constituirse en persona juridica, por tanto, la legislacion colombiana alrededor del tema de Seguridad y Salud en el Trabajo es perfectamente aplicable a las administraciones y consejos de administracion.</p>
    <p class="content-text">Para mayor comprension de la diferente terminologia que se va a tratar en el documento, se describe a continuacion algunos de los conceptos:</p>
    <?php
    $conceptos = [
        'ALARMA O PITO' => 'Sistema sonoro que permite avisar inmediatamente se accione a la comunidad la presencia de un riesgo que pone en grave peligro sus vidas.',
        'ALERTA' => 'Acciones de respuesta especificas frente a una emergencia.',
        'AMENAZA' => 'Factor externo de origen natural, tecnologico o social que puede afectar a la comunidad y a la copropiedad, provocando lesiones y/o muerte a las personas o danos a la infraestructura fisica y economica.',
        'ANALISIS DE VULNERABILIDAD' => 'Es la medida o grado de ser afectado por amenazas o riesgos segun la frecuencia y la severidad de estos.',
        'AYUDA INSTITUCIONAL' => 'Es aquella prestada por entidades publicas y/o privadas de caracter comunitario, organizados con el fin especifico de responder de oficio a los desastres.',
        'COMBUSTION' => 'Reaccion mediante la cual una sustancia denominada combustible interactua quimicamente con otra llamada oxidante o comburente y da como resultado gases toxicos irritantes y asfixiantes, humo, llamas y calor.',
        'CONTINGENCIA' => 'Evento que puede suceder o no suceder para el cual debemos estar preparados.',
        'CONTROL' => 'Accion de eliminar o limitar el desarrollo de un siniestro, para evitar o minimizar sus consecuencias.',
        'DESASTRE' => 'Es el dano o alteracion grave de las condiciones normales de la vida, causado por fenomenos naturales o accion del hombre en forma accidental.',
        'EMERGENCIA' => 'Estado de alteracion parcial o total de las actividades de una propiedad horizontal, ocasionado por la ocurrencia de un evento que genera peligro inminente.',
        'EVACUACION' => 'Es el conjunto de acciones tendientes a desplazar las personas de una zona de mayor amenaza a otra de menor peligro.',
        'IMPACTO' => 'Accion directa de una amenaza o un riesgo en un grupo de personas.',
        'MITIGACION' => 'Acciones desarrolladas antes, durante y despues de un siniestro, tendientes a contrarrestar sus efectos criticos.',
        'PLAN DE ACCION' => 'Es un trabajo colectivo que establece en un documento las medidas preventivas para evitar los posibles desastres especificos de cada comunidad.',
        'PLAN DE CONTINGENCIAS' => 'Componente del plan de emergencias que contiene los procedimientos para la pronta respuesta en caso de presentarse un evento especifico.',
        'PLAN DE EMERGENCIAS' => 'Definicion de politicas, organizaciones y metodos que indican la manera de enfrentar una situacion de emergencia o desastre.',
        'PREVENCION' => 'Accion para evitar la ocurrencia de desastres.',
        'RECUPERACION' => 'Actividad final en el proceso de respuesta a una emergencia. Consiste en restablecer la operatividad de un sistema interferido.',
        'RIESGO' => 'Una amenaza evaluada en cuanto su probabilidad de ocurrencia y su gravedad potencial esperada.',
        'SIMULACRO' => 'Ejercicio de juego de roles que se lleva a cabo en un escenario real o construccion en la forma posible para asemejarlo.',
        'SINIESTRO' => 'Es un evento no deseado, no esperado, que puede producir efectos negativos en las personas y en los bienes materiales.',
        'VIA DE EVACUACION' => 'Se usa normalmente como via de ingreso y de salida en los edificios. Su tramo seguro puede estar estructurado como zona vertical de seguridad.',
        'VULNERABILIDAD' => 'Condiciones en las que se encuentran las personas y los bienes expuestos a una amenaza. Se relaciona con la incapacidad de una comunidad para afrontar con sus propios recursos una situacion de emergencia.',
    ];
    foreach ($conceptos as $term => $def): ?>
    <p class="content-text"><strong><?= $term ?>:</strong> <?= $def ?></p>
    <?php endforeach; ?>

    <!-- ============ INFORMACION GENERAL DEL CONJUNTO ============ -->
    <div class="page-break"></div>
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
    <div class="page-break"></div>
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
    <div class="page-break"></div>
    <div class="section-title">LEGISLACION</div>
    <div class="section-subtitle">LEGISLACION NACIONAL</div>
    <p class="content-text"><strong>LEY 9 DE 1979</strong> Codigo Sanitario Titulo III: Relativo a la salud ocupacional. Art.93 - Areas de Circulacion: Claramente demarcadas, tener amplitud suficiente para el transito seguro de las personas y provistas de senalizacion adecuada. Art.114 - Prevencion y Extincion de Incendios: Disponer de personal capacitado, metodos, equipos y materiales adecuados y suficientes. Art. 127 - Todo lugar de trabajo tendra las facilidades y los recursos necesarios para la prestacion de los primeros auxilios a los trabajadores.</p>
    <p class="content-text"><strong>RESOLUCION 2400 DE 1979</strong> "Por el cual se establecen disposiciones sobre vivienda, higiene y seguridad industrial en los establecimientos de trabajo". Art. 205 - Peligro de Incendio o explosion en centros de trabajo: Provistos de tomas de agua con sus correspondientes mangueras, tanques de reserva y extintores. Art. 223 - Brigada contra Incendio: Debidamente entrenada y preparada.</p>
    <p class="content-text"><strong>CONPES 3146 de 2001</strong> Estrategia para consolidar la ejecucion del Plan Nacional para la Prevencion y Atencion de Desastres.</p>
    <p class="content-text"><strong>LEY 46/88</strong> "Por la cual se crea y organiza el Sistema Nacional para la Prevencion y Atencion de Desastres". Art. 3 - Plan Nacional para la Prevencion y Atencion de Desastres.</p>
    <p class="content-text"><strong>DECRETO LEY 919/89</strong> "Por el cual se organiza el Sistema Nacional para la Prevencion y Atencion de Desastres". Art. 13 - Planes de contingencia.</p>
    <p class="content-text"><strong>DECRETO 1295/94</strong> "Por el cual se determina la organizacion y administracion del Sistema General de Riesgos Profesionales".</p>
    <p class="content-text"><strong>LEY 322 DE 1996</strong> SISTEMA NACIONAL DE BOMBEROS. Art. 1 - La prevencion de incendios es responsabilidad de todas las autoridades y los habitantes del territorio colombiano.</p>
    <p class="content-text"><strong>LEY 769 DE 2002</strong> CODIGO NACIONAL DE TRANSITO.</p>
    <p class="content-text"><strong>DECRETO No. 3888/07</strong> Plan Nacional de Emergencias y Contingencia para Eventos de Afluencia Masiva de Publico.</p>
    <p class="content-text"><strong>DECRETO 1347 DE 2021</strong> Programa de Prevencion de Accidentes Mayores - PPAM.</p>
    <p class="content-text"><strong>DECRETO 4272 DE 2021</strong> Requisitos minimos de seguridad para trabajo en alturas.</p>

    <div class="section-subtitle">LEGISLACION DISTRITAL</div>
    <p class="content-text"><strong>RESOLUCION 1428 DE 2002</strong> Planes Tipo de Emergencias en escenarios Distritales.</p>
    <p class="content-text"><strong>DECRETO 332/04</strong> Organizacion del regimen y el Sistema para la Prevencion y Atencion de Emergencias.</p>
    <p class="content-text"><strong>DECRETO 423/06</strong> Plan Distrital para la prevencion y Atencion de Emergencias.</p>

    <div class="section-subtitle">NORMAS TECNICAS COLOMBIANAS</div>
    <p class="content-text">NTC-5254: Gestion de Riesgo. NTC-1700: Medidas de Seguridad en Edificaciones. NTC-2885: Extintores Portatiles. NTC-4764: Cruces peatonales. NTC-4140: Edificios - Pasillos y corredores. NTC-4144: Edificios - Senalizacion. NTC-4145: Edificios - Escaleras. NFPA 101/06: Life Safety Code. NFPA 1600/07: Standard on Disaster/Emergency Management.</p>

    <!-- ============ ANALISIS DE RIESGOS ============ -->
    <div class="page-break"></div>
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
    <div class="page-break"></div>
    <div class="section-subtitle">RIESGOS NATURALES</div>
    <p class="content-text"><strong>SISMOS, CAIDA DE ESTRUCTURAS:</strong> Ninguna edificacion, aun las construidas recientemente, se encuentran exentas de ser afectadas por la accion de las vibraciones derivadas del choque de las placas que forman la superficie de la tierra. La magnitud e intensidad, las caracteristicas del suelo, la resistencia de las edificaciones y la preparacion que se tenga por parte de las personas e instituciones para actuar y reaccionar de forma adecuada, dependen los danos que este cause. La ciudad de Bogota D.C. y sus alrededores estan asentados en una zona de riesgo sismico intermedio.</p>
    <p class="content-text"><strong>VENDAVALES, GRANIZADA Y TORMENTAS ELECTRICAS:</strong> Los cambios climaticos pueden encontrarse acompanados de vientos, lluvias, granizadas, tormentas electricas. La accion de vientos fuertes no solo puede romper ventanales y levantar tejas en las cubiertas, sino hacer caer antenas y pararrayos.</p>
    <p class="content-text"><strong>INUNDACIONES:</strong> Se presentan generalmente despues de una lluvia fuerte o granizada, por taponamiento de sifones, de desagues o de bajantes de canales; cuando se presenta acumulacion de residuos o basuras o por diametros muy reducidos de los tubos de la caneria.</p>

    <div class="section-subtitle">RIESGOS TECNOLOGICOS</div>
    <p class="content-text"><strong>INCENDIO:</strong> Amenaza caracteristica de toda edificacion cuya destinacion sea de caracter residencial. Se presenta por eventual vecindad a fuentes de ignicion, fuentes de calor, fuentes electricas, presencia de cargas estaticas y por diferentes cargas combustibles de materiales solidos presentes en las instalaciones.</p>
    <p class="content-text"><strong>EXPLOSION:</strong> Riesgo relacionado con el manejo de cargas combustibles del tipo B como almacenamiento y manipulacion de liquidos y gases inflamables, reactividad por escape de gases comprimidos como gas natural.</p>
    <p class="content-text"><strong>FALLA ESTRUCTURAL:</strong> La vulnerabilidad estructural se encuentra determinada por la capacidad de soporte vertical y resistencia a cargas horizontales de la edificacion. Se recomienda realizar un estudio tecnico y reforzar las estructuras segun las exigencias del codigo colombiano de construcciones sismo resistentes.</p>
    <p class="content-text"><strong>INTOXICACION POR INHALACION DE VAPORES:</strong> Afectaciones en la salud causadas por acumulacion de gases nocivos, especialmente en parqueaderos de vehiculos sin adecuada ventilacion.</p>

    <div class="section-subtitle">RIESGOS SOCIALES</div>
    <p class="content-text"><strong>VANDALISMO:</strong> Por la descomposicion social que se vive actualmente, esta es una amenaza con riesgo de probabilidad considerable.</p>
    <p class="content-text"><strong>ATENTADOS TERRORISTAS:</strong> Acciones con bombas o proyectiles dirigidos hacia algun objetivo que puede afectar instalaciones o viviendas aledanas. Incluye acciones de secuestro.</p>
    <p class="content-text"><strong>ASALTO Y HURTO:</strong> Posibilidad de riesgo principalmente en horas nocturnas. El conjunto residencial cuenta con vigilancia privada.</p>

    <!-- ============ CARGA COMBUSTIBLE ============ -->
    <div class="page-break"></div>
    <div class="section-title">CARGA COMBUSTIBLE</div>
    <p class="content-text">La edificacion presenta diferentes tipos de material combustible, segun sus caracteristicas:</p>
    <div class="section-subtitle">CLASE A</div>
    <p class="content-text">Papel en documentos e informes. Carton en puntos de acopio. Telas en apartamentos y salones. Madera en muebles, sillas, puertas, divisiones. Materiales acrilicos en computadores, impresoras, telefonos. Cuero en algunas sillas.</p>
    <div class="section-subtitle">CLASE B</div>
    <p class="content-text">Solventes, pinturas, esmaltes, vinilos, quimicos. Liquidos inflamables (Gasolina, ACPM). Gas natural distribuido a todos los apartamentos.</p>
    <div class="section-subtitle">CLASE C</div>
    <p class="content-text">Redes electricas energizadas, tomas, interruptores y luminarias. Cuarto de contadores en el parqueadero. Computadores, impresoras, telefonos, televisores. Equipos de bombeo del agua. Planta electrica.</p>

    <div class="section-subtitle">RECOMENDACIONES SEGUN EL TIPO DE COMBUSTIBLE</div>
    <p class="content-text"><strong>PARA RIESGO CLASE A:</strong> Evitar cajas con papeleria bajo las mesas. No almacenar cajas cerca de bombillos incandescentes. No dejar trapos con grasa fuera de recipientes metalicos cerrados. No almacenar papeles impregnados de liquidos inflamables.</p>
    <p class="content-text"><strong>PARA RIESGOS CLASE B:</strong> Disponer de un lugar ventilado para almacenar elementos de mantenimiento. Guardar materiales inflamables en recipientes hermeticos. Mantener materiales inflamables alejados de fuentes de calor. Tener un kit anti derrames.</p>
    <p class="content-text"><strong>PARA RIESGOS CLASE C:</strong> Evitar uso de elementos de calor en areas con material combustible. Identificar cajas de tacos de corriente. Restringir entrada a cuartos electricos. Realizar revisiones periodicas de posibles humedades.</p>

    <!-- ============ PON CODIGO 7 ============ -->
    <div class="page-break"></div>
    <div class="section-title">PROCEDIMIENTO OPERATIVO NORMALIZADO (PON) - CODIGO 7</div>
    <div class="section-subtitle">Falla de ascensor con personas en su interior</div>
    <p class="content-text"><strong>Introduccion:</strong> En edificaciones residenciales que cuentan con ascensores, es posible que se presenten fallas tecnicas, cortes electricos u otros incidentes que provoquen la detencion del equipo con ocupantes en su interior. Este procedimiento establece las acciones especificas para responder de manera rapida, segura y coordinada.</p>
    <p class="content-text"><strong>Objetivo:</strong> Establecer el procedimiento seguro y estandarizado para la atencion de emergencias por fallas de ascensor con personas atrapadas.</p>
    <p class="content-text"><strong>Alcance:</strong> Aplica para todo el personal de vigilancia, administracion, brigadas de emergencia, personal de mantenimiento y demas personas que participen en la atencion de emergencias.</p>
    <p class="content-text"><strong>Procedimiento:</strong> 1) Activacion del Codigo 7: Anunciar por canal de comunicacion interna. 2) Evaluacion inicial: Confirmar cantidad de personas, identificar personas vulnerables. 3) Comunicacion con ocupantes: Mantener contacto verbal, recomendar calma. 4) Corte de energia del ascensor si esta autorizado. 5) Notificar a empresa de mantenimiento, administracion y bomberos si aplica. 6) Esperar personal capacitado - no realizar maniobras improvisadas. 7) Rescate asistido solo por personal autorizado. 8) Atencion posterior: verificar condicion fisica y emocional. 9) Cierre: levantar informe detallado, prohibir uso del ascensor hasta certificacion.</p>
    <p class="content-text"><strong>Medidas preventivas:</strong> Mantener mantenimiento preventivo al dia. Senalizacion visible de contacto de emergencia. Capacitar al personal al menos una vez al ano. Incluir simulacros en el plan de emergencias.</p>

    <!-- ============ DIAGRAMA DE ACTUACION EN EMERGENCIAS ============ -->
    <?php if (!empty($diagramaBase64)): ?>
    <div class="page-break"></div>
    <div class="section-title">DIAGRAMA DE ACTUACION EN CASO DE EMERGENCIA</div>
    <p class="content-text">El siguiente diagrama de flujo establece el protocolo general de actuacion ante diferentes tipos de emergencia que puedan presentarse en la propiedad horizontal. Permite identificar rapidamente las acciones a seguir segun el tipo de evento.</p>
    <div style="text-align: center; margin: 15px 0;">
        <img src="<?= $diagramaBase64 ?>" style="max-width: 100%; max-height: 700px;">
    </div>
    <?php endif; ?>

    <!-- ============ ANEXOS - EVALUACIONES DE SEGURIDAD ============ -->
    <div class="page-break"></div>
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
    <div class="page-break"></div>
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
    <div class="page-break"></div>
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
    <div class="page-break"></div>
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
    <div class="page-break"></div>
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
    <div class="page-break"></div>
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
    <div class="page-break"></div>
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
