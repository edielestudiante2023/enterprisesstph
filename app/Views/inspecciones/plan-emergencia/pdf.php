<?php
$nombreCliente = esc($cliente['nombre_cliente'] ?? '');
$direccion = esc($cliente['direccion_cliente'] ?? '');
$ciudad = $inspeccion['ciudad'] ?? null;
$telefonosCiudad = ($ciudad && isset($telefonos[$ciudad])) ? $telefonos[$ciudad] : [];
$enumSiNo = ['si' => 'SI', 'no' => 'NO'];
$tipoInmueble = ['casas' => 'CASAS', 'apartamentos' => 'APARTAMENTOS'];
$debugMode = $debugMode ?? false;
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

        .cover-page { text-align: center; padding-top: 180px; padding-bottom: 180px; page-break-after: always; }
        .cover-title { font-size: 22px; font-weight: bold; color: #1c2437; margin-bottom: 14px; }
        .cover-subtitle { font-size: 16px; font-weight: bold; color: #444; margin-bottom: 40px; }
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

        <?php if ($debugMode): ?>
        /* ===== DEBUG MODE — colores por módulo ===== */
        .dbg { border: 2px dashed; padding: 8px; margin: 10px 0; border-radius: 4px; }
        .dbg-label { display:inline-block; font-weight:bold; font-size:10px; padding:2px 8px; color:#fff; border-radius:3px; margin-bottom:6px; letter-spacing:1px; }
        .dbg-plan      { border-color:#8e44ad; background:#f5eef8; }
        .dbg-plan      .dbg-label { background:#8e44ad; }
        .dbg-plan, .dbg-plan * { color:#5b2c6f !important; }
        .dbg-prob      { border-color:#27ae60; background:#eafaf1; }
        .dbg-prob      .dbg-label { background:#27ae60; }
        .dbg-prob, .dbg-prob * { color:#145a32 !important; }
        .dbg-locativa  { border-color:#e67e22; background:#fdf2e9; }
        .dbg-locativa  .dbg-label { background:#e67e22; }
        .dbg-locativa, .dbg-locativa * { color:#6e2c00 !important; }
        .dbg-matriz    { border-color:#2980b9; background:#ebf5fb; }
        .dbg-matriz    .dbg-label { background:#2980b9; }
        .dbg-matriz, .dbg-matriz * { color:#154360 !important; }
        .dbg-ext       { border-color:#c0392b; background:#fdedec; }
        .dbg-ext       .dbg-label { background:#c0392b; }
        .dbg-ext, .dbg-ext * { color:#641e16 !important; }
        .dbg-bot       { border-color:#d35400; background:#fef5e7; }
        .dbg-bot       .dbg-label { background:#d35400; }
        .dbg-bot, .dbg-bot * { color:#7e3a0c !important; }
        .dbg-rec       { border-color:#16a085; background:#e8f8f5; }
        .dbg-rec       .dbg-label { background:#16a085; }
        .dbg-rec, .dbg-rec * { color:#0e6251 !important; }
        .dbg-com       { border-color:#884ea0; background:#f4ecf7; }
        .dbg-com       .dbg-label { background:#884ea0; }
        .dbg-com, .dbg-com * { color:#4a235a !important; }
        .dbg-gab       { border-color:#b03a2e; background:#fadbd8; }
        .dbg-gab       .dbg-label { background:#b03a2e; }
        .dbg-gab, .dbg-gab * { color:#5d1a14 !important; }
        .dbg-missing   { border:2px solid #e74c3c; background:#fdedec; padding:8px; margin:10px 0; color:#c0392b !important; font-weight:bold; }
        .dbg-legend    { background:#2c3e50; color:#fff; padding:10px; margin-bottom:15px; border-radius:4px; font-size:10px; }
        .dbg-legend span { display:inline-block; margin:3px 6px; padding:2px 8px; border-radius:3px; }
        <?php endif; ?>
    </style>
</head>
<body>

    <?php if ($debugMode): ?>
    <div class="dbg-legend">
        <strong>🔍 MODO DEBUG — Plan de Emergencia</strong><br>
        Cada bloque de color corresponde a un módulo que alimenta el plan. Los bloques <span style="background:#e74c3c; color:#fff;">⚠ rojos</span> indican módulos sin datos.<br>
        <span style="background:#8e44ad;">PLAN EMERG.</span>
        <span style="background:#27ae60;">PROB. PELIGROS</span>
        <span style="background:#e67e22;">LOCATIVA</span>
        <span style="background:#2980b9;">MATRIZ VULN.</span>
        <span style="background:#c0392b;">EXTINTORES</span>
        <span style="background:#d35400;">BOTIQUIN</span>
        <span style="background:#16a085;">REC. SEGURIDAD</span>
        <span style="background:#884ea0;">COMUNIC.</span>
        <span style="background:#b03a2e;">GABINETES</span>
    </div>
    <?php endif; ?>

    <!-- ============ PORTADA ============ -->
    <div class="cover-page">
        <?php if (!empty($logoBase64)): ?>
        <div style="margin-bottom:20px;"><img src="<?= $logoBase64 ?>" style="max-width:180px; max-height:100px;"></div>
        <?php endif; ?>
        <div class="cover-title">PLAN DE EMERGENCIA Y CONTINGENCIA</div>
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
    <p class="content-text">Este documento, denominado Plan de Emergencia y Contingencia, tiene como enfoque principal todas las areas pertenecientes a la copropiedad. Ademas, abarca a todo el personal que forma parte de esta comunidad, incluyendo residentes, administracion, personal de vigilancia, aseo, mantenimiento, contratistas, visitantes y demas partes interesadas que se encuentren dentro de las instalaciones de <?= $nombreCliente ?>. El alcance territorial del Plan cubre la totalidad de las areas privadas, comunes, circulaciones, accesos, parqueaderos, zonas tecnicas y demas espacios que hacen parte de la propiedad horizontal.</p>
    <p class="content-text">El presente Plan aplica durante las veinticuatro (24) horas del dia, los siete (7) dias de la semana, y considera escenarios de emergencia de origen natural, tecnologico y social que puedan afectar a las personas, los bienes y la continuidad de los servicios de la copropiedad. Los procedimientos aqui definidos deben ser conocidos, divulgados, capacitados y ejecutados por todos los integrantes de la comunidad, con especial responsabilidad del administrador, el consejo de administracion y la brigada de emergencia.</p>

    <!-- ============ RESPONSABLE Y SEGUIMIENTO DEL PLAN (PGRDEPP) ============ -->
    <div class="section-title">RESPONSABLE Y SEGUIMIENTO DEL PLAN (PGRDEPP)</div>
    <p class="content-text">En los terminos del Decreto 2157 de 2017, reglamentario del articulo 42 de la Ley 1523 de 2012, la formulacion, implementacion, socializacion, actualizacion y seguimiento del presente Plan de Gestion del Riesgo de Desastres de Entidad Privada (PGRDEPP) es responsabilidad del representante legal de la copropiedad, rol que en <?= $nombreCliente ?> es ejercido por el Administrador debidamente designado por la Asamblea General de Propietarios o el Consejo de Administracion.</p>
    <p class="content-text">El Plan sera revisado y actualizado como minimo una (1) vez al ano o cuando se produzcan cambios significativos en la infraestructura, la ocupacion, las amenazas identificadas o el marco normativo aplicable. El resultado de cada revision quedara documentado y sera socializado con la brigada, el personal operativo, el consejo de administracion y los residentes.</p>

    <?php if (!empty($matrizResponsablesIA ?? null)): ?>
        <div class="section-subtitle">MATRIZ DE RESPONSABLES DEL PLAN</div>
        <?php $filas = $matrizResponsablesIA['filas'] ?? []; ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:25%;">ROL</th>
                    <th style="width:55%;">RESPONSABILIDAD PRINCIPAL</th>
                    <th style="width:20%;">FRECUENCIA</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($filas as $fila): ?>
                <tr>
                    <td style="font-weight:bold; vertical-align:top;"><?= esc($fila['rol'] ?? '-') ?></td>
                    <td style="vertical-align:top;"><?= esc($fila['responsabilidad'] ?? '-') ?></td>
                    <td style="vertical-align:top; text-align:center;"><?= esc($fila['frecuencia'] ?? '-') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- ============ MARCO NORMATIVO NACIONAL ============ -->
    <div class="section-title">MARCO NORMATIVO NACIONAL</div>
    <p class="content-text">El presente Plan de Emergencia y Contingencia se formula en cumplimiento del marco normativo vigente en la Republica de Colombia aplicable a la propiedad horizontal y a la gestion del riesgo de desastres. Este marco constituye el sustento legal del Plan y es transversal a todo el territorio nacional.</p>
    <p class="content-text"><strong>DECRETO 1072 DE 2015 — Articulo 2.2.4.6.25.</strong> Decreto Unico Reglamentario del Sector Trabajo. Establece la obligacion del empleador de implementar y mantener un Plan de Prevencion, Preparacion y Respuesta ante Emergencias con: (i) analisis de amenazas y vulnerabilidad con alcance sobre todos los trabajadores, contratistas, visitantes y demas personas que se encuentren en la copropiedad; (ii) recursos humanos, tecnicos y financieros para su implementacion; (iii) procedimientos escritos para prevenir y controlar las amenazas identificadas; (iv) informar, capacitar y entrenar a todos los trabajadores sobre el plan; (v) conformar, capacitar, entrenar y dotar la brigada de prevencion, preparacion y respuesta ante emergencias; (vi) realizar simulacros como minimo una (1) vez al ano con la participacion de todos los trabajadores; (vii) inspeccionar con la periodicidad definida los equipos de deteccion, alarma, control y atencion de emergencias, asi como los sistemas de senalizacion y las rutas de evacuacion.</p>
    <p class="content-text"><strong>RESOLUCION 0312 DE 2019 — Ministerio del Trabajo.</strong> "Por la cual se definen los Estandares Minimos del Sistema de Gestion de la Seguridad y Salud en el Trabajo SG-SST". Exige a los empleadores, en funcion del numero de trabajadores y la clase de riesgo, realizar el analisis de vulnerabilidad ante amenazas, formular y ejecutar el plan de prevencion, preparacion y respuesta ante emergencias con las intervenciones que se deriven de dicho analisis, y conformar, capacitar y dotar la brigada de prevencion, preparacion y respuesta ante emergencias. El cumplimiento de estos estandares es verificable por el Ministerio del Trabajo.</p>
    <p class="content-text"><strong>LEY 1523 DE 2012 — Articulo 42.</strong> Por la cual se adopta la Politica Nacional de Gestion del Riesgo de Desastres y se establece el Sistema Nacional de Gestion del Riesgo de Desastres. El articulo 42 obliga a todas las entidades publicas y privadas a realizar un analisis especifico de riesgo que considere los posibles efectos de eventos naturales sobre la infraestructura expuesta y aquellos que se deriven de los danos de la misma en su area de influencia, asi como los que se deriven de su operacion. Con base en este analisis, disenaran e implementaran las medidas de reduccion del riesgo y el plan de emergencia y contingencia.</p>
    <p class="content-text"><strong>DECRETO 2157 DE 2017.</strong> "Por medio del cual se adoptan directrices generales para la elaboracion del Plan de Gestion del Riesgo de Desastres de las Entidades Publicas y Privadas (PGRDEPP) en el marco del articulo 42 de la Ley 1523 de 2012". Establece que el representante legal de la entidad — en el caso de la propiedad horizontal, el administrador designado por la asamblea — es el responsable de la formulacion, implementacion, socializacion, actualizacion y seguimiento del PGRDEPP. Contenido obligatorio: (i) proceso de conocimiento del riesgo con analisis especifico; (ii) proceso de reduccion del riesgo con medidas de intervencion correctiva y prospectiva; (iii) proceso de manejo del desastre con los planes de emergencia y contingencia.</p>
    <p class="content-text"><strong>LEY 675 DE 2001.</strong> Regimen de Propiedad Horizontal. Establece la personeria juridica de los edificios y conjuntos sometidos a propiedad horizontal, por lo cual les resulta aplicable la legislacion colombiana en materia de Seguridad y Salud en el Trabajo y de Gestion del Riesgo de Desastres. La administracion actua como representante legal de la persona juridica y asume las obligaciones correlativas en materia de emergencias.</p>
    <p class="content-text"><strong>NSR-10 — DECRETO 926 DE 2010.</strong> Reglamento Colombiano de Construccion Sismo Resistente. Adoptado mediante el Decreto 926 de 2010 y sus actos modificatorios, constituye la norma tecnica vigente en Colombia para el diseno y construccion sismo resistente de edificaciones. Es referente obligatorio para la evaluacion de la vulnerabilidad estructural de la copropiedad.</p>
    <p class="content-text"><strong>NTC 1700 (ICONTEC).</strong> Higiene y Seguridad. Medidas de Seguridad en Edificaciones. Medios de Evacuacion, en correlato con el Codigo NFPA 101 (Life Safety Code). Establece los requerimientos que deben cumplir las edificaciones en cuanto a salidas de evacuacion, escaleras de emergencia, iluminacion de evacuacion y sistemas de proteccion especiales. Requisitos minimos aplicables a propiedad horizontal: (i) ancho libre minimo de las puertas de salida de setenta centimetros (70 cm); (ii) minimo dos (2) salidas independientes por piso; (iii) instalacion de barra antipanico en aquellas puertas que sirvan a areas con carga ocupacional superior a cien (100) personas; (iv) demarcacion y senalizacion permanente de la ruta de evacuacion; (v) iluminacion de emergencia con autonomia suficiente para garantizar la evacuacion segura.</p>
    <p class="content-text"><strong>RESOLUCION 0256 DE 2014 — Direccion Nacional de Bomberos de Colombia.</strong> "Por medio de la cual se reglamenta la conformacion, capacitacion y entrenamiento para las brigadas contraincendios de los sectores energetico, industrial, petrolero, minero, portuario, comercial y similar en Colombia". Establece los lineamientos tecnicos para la conformacion de brigadas, contenidos minimos de capacitacion, niveles de formacion (basico, intermedio, avanzado), dotacion y entrenamiento periodico aplicables como referente a las brigadas de copropiedades en regimen de propiedad horizontal.</p>
    <p class="content-text"><strong>NORMAS TECNICAS COMPLEMENTARIAS.</strong> NTC-ISO 31000 (Gestion del Riesgo — Principios y Directrices, que reemplaza a la antigua NTC 5254); NTC-2885 (Extintores Portatiles — inspeccion y mantenimiento); NTC-1867 (Sistemas de senales contra incendio); NTC-4144 y NTC-4145 (Edificios — senalizacion y escaleras); Ley 322 de 1996 (Sistema Nacional de Bomberos); NFPA 101 (Life Safety Code) y NFPA 1600 (Disaster/Emergency Management and Business Continuity).</p>

    <!-- ============ MARCO CONCEPTUAL / DEFINICIONES ============ -->
    <div class="section-title">MARCO CONCEPTUAL Y DEFINICIONES</div>
    <p class="content-text">Para la adecuada comprension e interpretacion del presente Plan, a continuacion se presentan las definiciones tecnicas fundamentales, alineadas con la Ley 1523 de 2012, el Decreto 1072 de 2015 y la terminologia adoptada por la UNGRD:</p>
    <p class="content-text"><strong>AMENAZA:</strong> peligro latente de que un evento fisico de origen natural, socio-natural, tecnologico o antropico no intencional se presente con una severidad suficiente para causar perdida de vidas, lesiones u otros impactos en la salud, danos y perdidas en los bienes, la infraestructura, los medios de sustento, la prestacion de servicios y los recursos ambientales.</p>
    <p class="content-text"><strong>VULNERABILIDAD:</strong> susceptibilidad o fragilidad fisica, economica, social, ambiental o institucional que tiene una comunidad de ser afectada o de sufrir efectos adversos en caso de que un evento fisico peligroso se presente.</p>
    <p class="content-text"><strong>RIESGO DE DESASTRES:</strong> danos o perdidas potenciales que pueden presentarse debido a los eventos fisicos peligrosos de origen natural, socio-natural, tecnologico, biosanitario o humano no intencional, en un periodo de tiempo especifico y que son determinados por la vulnerabilidad de los elementos expuestos.</p>
    <p class="content-text"><strong>EMERGENCIA:</strong> situacion caracterizada por la alteracion o interrupcion intensa y grave de las condiciones normales de funcionamiento u operacion de una comunidad, causada por un evento adverso o por la inminencia del mismo, que obliga a una reaccion inmediata y que requiere la respuesta de las instituciones del Estado, los medios de comunicacion y de la comunidad en general.</p>
    <p class="content-text"><strong>DESASTRE:</strong> resultado que se desencadena de la manifestacion de uno o varios eventos naturales o antropogenicos no intencionales que al encontrar condiciones propicias de vulnerabilidad en las personas, los bienes, la infraestructura, los medios de subsistencia, la prestacion de servicios o los recursos ambientales, causa danos o perdidas que superan la capacidad de la comunidad para responder con sus propios recursos.</p>
    <p class="content-text"><strong>BRIGADA DE EMERGENCIA:</strong> grupo organizado, entrenado, capacitado y dotado para prevenir, controlar y atender situaciones de emergencia dentro de una instalacion, en el marco del Plan de Prevencion, Preparacion y Respuesta ante Emergencias.</p>
    <p class="content-text"><strong>PROCEDIMIENTO OPERATIVO NORMALIZADO (PON):</strong> documento que describe de manera estructurada y secuencial las acciones especificas que debe ejecutar la brigada y el personal asignado ante un escenario de emergencia determinado, con el objetivo de estandarizar la respuesta y reducir la improvisacion.</p>
    <p class="content-text"><strong>SIMULACRO:</strong> ejercicio practico en terreno que permite evaluar, entrenar y ajustar los procedimientos del Plan de Emergencia mediante la representacion de un escenario de emergencia, con participacion real de las personas y activacion de los recursos previstos.</p>
    <p class="content-text"><strong>PLAN DE EMERGENCIA:</strong> instrumento que define las politicas, los sistemas de organizacion y los procedimientos generales aplicables para enfrentar de manera oportuna, eficiente y eficaz las situaciones de calamidad, desastre o emergencia en sus distintas fases.</p>
    <p class="content-text"><strong>PLAN DE CONTINGENCIA:</strong> componente del Plan de Emergencia que contiene los procedimientos especificos de respuesta para un escenario o amenaza determinada.</p>
    <p class="content-text"><strong>MEDEVAC (Medical Evacuation):</strong> protocolo de evacuacion medica de personas lesionadas o enfermas hacia los centros asistenciales, mediante la coordinacion con organismos de socorro y servicios de ambulancia.</p>

    <!-- CONCEPTOS ADICIONALES eliminado: era duplicado del MARCO CONCEPTUAL Y DEFINICIONES. El parrafo introductorio sobre Ley 675 quedo cubierto en MARCO NORMATIVO NACIONAL. -->

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
    <?php if ($debugMode): ?><div class="dbg dbg-prob"><span class="dbg-label">PROB. PELIGROS — $ultimaProb</span><?php endif; ?>
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
    <?php else: ?>
    <?php if ($debugMode): ?><div class="dbg-missing">⚠ $ultimaProb está vacío — no hay inspección de Probabilidad de Peligros cargada</div><?php endif; ?>
    <?php endif; ?>
    <?php if ($debugMode): ?></div><?php endif; ?>

    <!-- Descripciones de riesgos (texto estatico) -->
<div class="section-subtitle">RIESGOS NATURALES</div>
    <p class="content-text"><strong>SISMOS, CAIDA DE ESTRUCTURAS:</strong> Ninguna edificacion, aun las construidas recientemente, se encuentran exentas de ser afectadas por la accion de las vibraciones derivadas del choque de las placas que forman la superficie de la tierra y que se mueven continuamente en direcciones diferentes acumulando y liberando energia que sacude la superficie, fenomeno que se conoce como terremoto, sismo o temblor de tierra. La magnitud e intensidad, las caracteristicas del suelo (suelos blandos o rellenos pueden aumentar la capacidad destructiva en determinadas edificaciones), la resistencia de las edificaciones (una falla estructural de planta fisica puede hacer colapsar de manera parcial o total estructuras de la edificacion con alteracion directa de su capacidad portante y dano a sus elementos y ocupantes), y la preparacion que se tenga por parte de las personas e instituciones para actuar y reaccionar en forma adecuada, antes, durante y despues del fenomeno, dependen los danos que este cause.<br>CLASIFICACION DEL RIESGO: PROBABLE</p>
    <p class="content-text"><strong>VENDAVALES, GRANIZADA Y TORMENTAS ELECTRICAS:</strong> Los cambios climaticos y meteorologicos se pueden encontrar acompanados de vientos, lluvias, granizadas, tormentas electricas. La accion de vientos fuertes no solo puede romper ventanales y levantar tejas en las cubiertas, sino hacer caer antenas y pararrayos. Las tormentas electricas cuando no existe la proteccion de las edificaciones e instalaciones con pararrayos debidamente conectados a tierra pueden traer como consecuencia accidentes fatales de trabajadores y la probabilidad de incendios con perdidas materiales.<br>CLASIFICACION DEL RIESGO: POCO PROBABLE</p>
    <p class="content-text"><strong>INUNDACIONES:</strong> Se presentan generalmente despues de una lluvia fuerte o una granizada, por sustraccion de drenajes, por taponamiento de sifones, de desagues o de bajantes de canales; cuando se presenta acumulacion de residuos o basuras o por diametros muy reducidos de los tubos de la caneria; por mala inclinacion de los desniveles hacia los respectivos desagues, o por estar la edificacion en zonas bajas inundables como cerca de rios, lagos o por estar construida en zonas pantanosas. Por presentarse en la capital epocas de fuertes y prolongados inviernos, es muy probable que se presenten este tipo de amenazas.<br>CLASIFICACION DEL RIESGO: POCO PROBABLE</p>

    <div class="section-subtitle">RIESGOS TECNOLOGICOS</div>
    <p class="content-text"><strong>INCENDIO:</strong> Entre las amenazas mas importantes se hace referencia a las de incendio, la cual es caracteristica de toda edificacion cuya destinacion sea de caracter industrial, comercial, de servicios o residencia. Esta amenaza no solamente se presenta por una eventual vecindad a fuentes de ignicion o detonacion, fuentes de calor, fuentes electricas, presencia de cargas estaticas y tambien por diferentes cargas combustibles de materiales solidos presentes en las instalaciones del conjunto residencial y a los trabajos que en el se realicen. Debido a que en el conjunto se almacenan diferentes combustibles como vestuario, telones, madera, alfombras, carton, plasticos, equipos de oficina; presencia de gas natural y demas combustibles que pueden ocasionar un incendio de grandes proporciones.<br>CLASIFICACION DEL RIESGO: MUY PROBABLE</p>
    <p class="content-text"><strong>EXPLOSION:</strong> Es un riesgo que viene relacionado con el manejo de cargas combustibles del tipo B como el almacenamiento y manipulacion de liquidos y gases inflamables, la reactividad por escape de gases comprimidos como el caso de gas natural, y en el manejo de solventes, lacas, pinturas, Varsol que normalmente emanan gases con propiedades inflamables detonantes lo mismo que eventualmente lo podria hacer pero con menos posibilidad el ACPM.<br>CLASIFICACION DEL RIESGO: MUY PROBABLE</p>
    <p class="content-text"><strong>FALLA ESTRUCTURAL:</strong> La vulnerabilidad estructural se encuentra determinada por la capacidad de soporte vertical y resistencia a cargas horizontales de la edificacion, la cual en terminos generales presenta buen aspecto. Un gran porcentaje de las instalaciones esta construido en muros de ladrillo y cemento, pisos en cemento, techos en placa de concreto. Con el objeto de determinar la capacidad sismo resistente de las edificaciones se recomienda realizar un estudio tecnico de las mismas y conforme a su valoracion reforzar las estructuras o realizar las modificaciones arquitectonicas necesarias, en consonancia con las exigencias del Reglamento Colombiano de Construccion Sismo Resistente NSR-10, adoptado mediante el Decreto 926 de 2010 y sus actos modificatorios, el cual constituye la norma tecnica vigente en Colombia para el diseno y construccion sismo resistente de edificaciones.<br>CLASIFICACION DEL RIESGO: POCO PROBABLE</p>
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

    <!-- ============ PROCEDIMIENTOS OPERATIVOS NORMALIZADOS (PON) ============ -->
<div class="section-title">PROCEDIMIENTOS OPERATIVOS NORMALIZADOS (PON)</div>
<p class="content-text">Los siguientes Procedimientos Operativos Normalizados (PON) establecen las acciones estandarizadas que deben ejecutarse ante cada tipo de emergencia identificada. Cada PON esta estructurado con objetivo, alcance, definiciones, responsables, procedimiento paso a paso, medidas preventivas y recomendaciones, conforme al Decreto 1072 de 2015 art. 2.2.4.6.25 y las directrices de la UNGRD.</p>
<!-- TODO Fase 2: IA (Claude) enriquecera cada PON con aspectos especificos del cliente segun $ultimaProb (probabilidad de cada amenaza) y $ultimaMatriz (vulnerabilidades), agregando un adendo personalizado por PON. -->

<div class="section-subtitle">NOTA ACLARATORIA SOBRE LA BRIGADA DE EMERGENCIA EN PROPIEDAD HORIZONTAL</div>
<p class="content-text">Conforme al Decreto 1072 de 2015 art. 2.2.4.6.25 y la Resolucion 0256 de 2014, toda copropiedad debe conformar una Brigada de Prevencion, Preparacion y Respuesta ante Emergencias. En el contexto real de la propiedad horizontal residencial, esta Brigada esta integrada por <strong>residentes y personal contratista voluntario</strong> (vigilancia, aseo, mantenimiento) que reciben <strong>capacitacion teorico-practica basica</strong> en: primeros auxilios, tipos y manejo de extintores, control de conatos de incendio, protocolos de evacuacion e instruccion para el Simulacro Nacional de Evacuacion anual.</p>
<p class="content-text">La Brigada de la copropiedad <strong>no sustituye a los organismos oficiales de socorro</strong>. Su funcion es contener el evento en su fase inicial, proteger vidas y facilitar la llegada y actuacion de Bomberos, Policia, Cruz Roja y Defensa Civil. Las acciones de rescate tecnico, busqueda en estructuras colapsadas, manejo de materiales peligrosos, desactivacion de artefactos explosivos y cualquier intervencion que requiera equipamiento especializado corresponden exclusivamente a las entidades oficiales.</p>
<p class="content-text">El presente Plan reconoce que <?= $nombreCliente ?> cuenta con <strong>una sola Brigada de Emergencia</strong> para toda la copropiedad, y que todas las referencias a brigadistas, coordinadores de evacuacion o grupos funcionales se entienden aplicadas al personal efectivamente capacitado y disponible al momento del evento. Donde no haya brigada constituida, las funciones operativas iniciales seran asumidas por el <strong>personal de vigilancia en turno</strong>, con el respaldo del Administrador y de los residentes voluntarios capacitados.</p>

<?php
$ponesCanonicos = require APPPATH . 'Config/PonesCanonicos.php';
foreach ($ponesCanonicos as $pon):
?>
    <div class="section-subtitle">PON CODIGO <?= esc($pon['codigo']) ?> — <?= esc($pon['titulo']) ?></div>

    <p class="content-text"><strong>Objetivo:</strong> <?= esc($pon['objetivo']) ?></p>
    <p class="content-text"><strong>Alcance:</strong> <?= esc($pon['alcance']) ?></p>

    <?php if (!empty($pon['definiciones'])): ?>
    <p class="content-bold">Definiciones clave:</p>
    <?php foreach ($pon['definiciones'] as $termino => $definicion): ?>
    <p class="content-text"><strong><?= esc($termino) ?>:</strong> <?= esc($definicion) ?></p>
    <?php endforeach; ?>
    <?php endif; ?>

    <?php if (!empty($pon['responsables'])): ?>
    <p class="content-bold">Responsables de la ejecucion:</p>
        <?php if (!empty($pon['responsables']['internos'])): ?>
        <p class="content-text"><strong>Actores internos del conjunto:</strong> <?= esc(implode(' | ', $pon['responsables']['internos'])) ?></p>
        <?php endif; ?>
        <?php if (!empty($pon['responsables']['contratistas_externos'])): ?>
        <p class="content-text"><strong>Contratistas externos:</strong> <?= esc(implode(' | ', $pon['responsables']['contratistas_externos'])) ?></p>
        <?php endif; ?>
        <?php if (!empty($pon['responsables']['organismos_socorro'])): ?>
        <p class="content-text"><strong>Organismos de socorro:</strong> <?= esc(implode(' | ', $pon['responsables']['organismos_socorro'])) ?></p>
        <?php endif; ?>
    <?php endif; ?>

    <p class="content-bold">Procedimiento:</p>
    <?php foreach ($pon['procedimiento'] as $paso): ?>
    <p class="content-text"><?= esc($paso) ?></p>
    <?php endforeach; ?>

    <?php if (!empty($pon['medidas_preventivas'])): ?>
    <p class="content-bold">Medidas preventivas:</p>
    <?php foreach ($pon['medidas_preventivas'] as $medida): ?>
    <p class="content-text">• <?= esc($medida) ?></p>
    <?php endforeach; ?>
    <?php endif; ?>

    <?php if (!empty($pon['recomendaciones'])): ?>
    <p class="content-bold">Recomendaciones:</p>
    <?php foreach ($pon['recomendaciones'] as $rec): ?>
    <p class="content-text">• <?= esc($rec) ?></p>
    <?php endforeach; ?>
    <?php endif; ?>

    <?php
    // Adendo personalizado generado por IA (Fase 2)
    $ponKey = array_search($pon, $ponesCanonicos, true);
    $adendoIA = $ponsIaAdendo[$ponKey] ?? null;
    if (!empty($adendoIA)):
    ?>
    <p class="content-bold" style="background:#f5eef8; color:#5b2c6f; padding:3px 6px; border-left:3px solid #8e44ad;">Adendo personalizado para <?= esc($nombreCliente) ?>:</p>
    <p class="content-text" style="background:#fbf6fc; padding:6px; border-left:3px solid #8e44ad;"><?= nl2br(esc($adendoIA)) ?></p>
    <?php endif; ?>

<?php endforeach; ?>

    <!-- ============ DIAGRAMA DE ACTUACION EN EMERGENCIAS ============ -->
    <?php if (!empty($diagramaNodos ?? null)): ?>
<div class="section-title">DIAGRAMA DE ACTUACION EN CASO DE EMERGENCIA</div>
    <p class="content-text">El siguiente diagrama de flujo establece el protocolo general de actuacion ante diferentes tipos de emergencia que puedan presentarse en la propiedad horizontal, personalizado segun las amenazas identificadas en el analisis de vulnerabilidad y probabilidad de peligros del conjunto <?= $nombreCliente ?>. Permite identificar rapidamente las acciones a seguir segun el tipo de evento.</p>
    <?php if (true): ?>
        <?php
        $inicio = $diagramaNodos['inicio'] ?? 'DETECCION DE EMERGENCIA';
        $ramas  = $diagramaNodos['ramas'] ?? [];
        ?>
        <table style="width:100%; border-collapse:collapse; margin: 8px 0;">
            <tr>
                <td style="text-align:center; padding:8px; background:#1c2437; color:#fff; font-weight:bold; font-size:11px; border:2px solid #1c2437;">
                    <?= esc($inicio) ?>
                </td>
            </tr>
            <tr><td style="text-align:center; font-size:14px; padding:2px;">&#9660;</td></tr>
            <tr>
                <td style="text-align:center; padding:6px; background:#bd9751; color:#fff; font-weight:bold; font-size:10px; border:2px solid #bd9751;">
                    TIPO DE EVENTO DETECTADO
                </td>
            </tr>
            <tr><td style="text-align:center; font-size:14px; padding:2px;">&#9660;</td></tr>
        </table>
        <table style="width:100%; border-collapse:collapse; margin: 4px 0;">
            <?php foreach ($ramas as $rama): ?>
            <tr>
                <td style="width:25%; padding:6px; background:#f5eef8; border:1px solid #8e44ad; vertical-align:top; text-align:center; font-weight:bold; color:#5b2c6f; font-size:9px;">
                    <?= esc($rama['tipo'] ?? '-') ?>
                </td>
                <td style="width:75%; padding:6px; background:#fff; border:1px solid #ccc; vertical-align:top; font-size:8.5px;">
                    <?php $pasos = $rama['pasos'] ?? []; ?>
                    <?php foreach ($pasos as $i => $paso): ?>
                    <?= ($i + 1) ?>. <?= esc($paso) ?><br>
                    <?php endforeach; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <p class="content-text" style="font-size:8px; color:#666; font-style:italic; margin-top:6px;">Diagrama de actuacion generado por IA personalizado para <?= esc($nombreCliente) ?> segun amenazas identificadas en la matriz de vulnerabilidad y probabilidad de peligros.</p>
    <?php endif; ?>
    <?php endif; // cierre del if $diagramaNodos externo ?>

    <!-- ============ CONFORMACION DE BRIGADA DE EMERGENCIA ============ -->
    <div class="section-title">CONFORMACION DE LA BRIGADA DE EMERGENCIA</div>
    <p class="content-text">En cumplimiento del numeral 5 del articulo 2.2.4.6.25 del Decreto 1072 de 2015, de la Resolucion 0312 de 2019 y de la Resolucion 0256 de 2014 de la Direccion Nacional de Bomberos de Colombia, <?= $nombreCliente ?> conformara una Brigada de Prevencion, Preparacion y Respuesta ante Emergencias, integrada por personal voluntario de la administracion, vigilancia, aseo, mantenimiento y residentes que manifiesten disposicion de servicio.</p>
    <div class="section-subtitle">ESTRUCTURA DE LA BRIGADA</div>
    <p class="content-text">La brigada se organizara bajo el modelo del Sistema Comando de Incidentes con las siguientes posiciones: <strong>Jefe de Brigada</strong> (administrador o delegado), <strong>Subjefe de Brigada</strong> y los siguientes grupos funcionales: <strong>(i) Grupo de Evacuacion y Rescate:</strong> responsable de guiar a residentes y visitantes por las rutas de evacuacion hasta los puntos de encuentro y de realizar la busqueda y rescate basico de personas que no hayan evacuado. <strong>(ii) Grupo de Primeros Auxilios:</strong> responsable de brindar atencion inicial a lesionados hasta la llegada de los organismos de socorro. <strong>(iii) Grupo de Control de Incendios:</strong> responsable del manejo de extintores portatiles y gabinetes contra incendio en conatos y del apoyo al Cuerpo de Bomberos. <strong>(iv) Grupo de Comunicaciones:</strong> responsable de activar las alarmas, efectuar las llamadas a organismos externos de socorro y llevar el registro del evento.</p>
    <div class="section-subtitle">PERFIL Y REQUISITOS DEL BRIGADISTA</div>
    <p class="content-text">Mayor de edad, en condiciones fisicas y mentales aptas para la actividad, con disponibilidad para recibir capacitacion y entrenamiento periodico, disposicion de servicio, liderazgo, capacidad de trabajo en equipo y compromiso con la gestion del riesgo de la copropiedad. Los brigadistas deberan acreditar examen medico ocupacional que certifique su aptitud para actividades de brigada.</p>
    <div class="section-subtitle">FUNCIONES ANTES, DURANTE Y DESPUES</div>
    <p class="content-text"><strong>Antes:</strong> participar en las capacitaciones, inspeccionar periodicamente los equipos de emergencia (extintores, gabinetes, botiquines, senalizacion, rutas), divulgar el Plan a residentes y visitantes, participar en simulacros y verificar la operatividad de los sistemas de alarma y comunicacion.<br><strong>Durante:</strong> activar el sistema de alarma, ejecutar las acciones propias de su grupo funcional segun los procedimientos operativos normalizados (PON), coordinar con organismos externos de socorro, mantener la calma y proteger la vida como prioridad absoluta por encima de los bienes materiales.<br><strong>Despues:</strong> verificar que todos los ocupantes hayan evacuado hacia los puntos de encuentro, rendir informe de la novedad, participar en la evaluacion del evento, apoyar las labores de recuperacion y contribuir a la actualizacion del Plan con las lecciones aprendidas.</p>
    <div class="section-subtitle">DOTACION MINIMA</div>
    <p class="content-text">La administracion del conjunto dotara a la brigada con los elementos minimos de proteccion personal e identificacion: casco tipo brigadista con barbiquejo, chaleco reflectivo con identificacion del rol, guantes de carnaza y de nitrilo, linterna de mano con baterias de repuesto, pito de emergencia, radio de comunicacion y botiquin portatil de brigadista. La dotacion sera verificada y repuesta periodicamente.</p>
    <?php if (!empty($inspeccion['brigada_ia_texto'])): ?>
    <div class="section-subtitle">SITUACION ACTUAL DE LA BRIGADA EN <?= esc(strtoupper($nombreCliente)) ?></div>
    <p class="content-text" style="background:#fdf2e9; padding:8px; border-left:3px solid #d35400;"><?= nl2br(esc($inspeccion['brigada_ia_texto'])) ?></p>
    <p class="content-text" style="font-size:8px; color:#666; font-style:italic;">Diagnostico personalizado generado por IA con base en la inspeccion de Brigada+Simulacros del cliente.</p>
    <?php endif; ?>

    <!-- ============ PROGRAMA DE CAPACITACION Y SIMULACROS ============ -->
    <div class="section-title">PROGRAMA DE CAPACITACION Y SIMULACROS</div>
    <p class="content-text">En cumplimiento del numeral 6 del articulo 2.2.4.6.25 del Decreto 1072 de 2015 y de los estandares minimos de la Resolucion 0312 de 2019, <?= $nombreCliente ?> realizara simulacros de emergencia como minimo una (1) vez al ano, con la participacion de todos los trabajadores, brigadistas, administracion y, en lo posible, los residentes del conjunto.</p>
    <div class="section-subtitle">TEMAS OBLIGATORIOS DE CAPACITACION</div>
    <p class="content-text">La brigada y el personal vinculado recibiran capacitacion minima anual en: (i) nociones basicas de gestion del riesgo de desastres y marco normativo; (ii) analisis de amenazas y vulnerabilidad aplicado a la copropiedad; (iii) evacuacion, rutas y puntos de encuentro; (iv) primeros auxilios basicos y RCP; (v) uso y manejo de extintores portatiles; (vi) uso de gabinetes contra incendio cuando aplique; (vii) busqueda y rescate basico; (viii) comunicaciones en emergencia y activacion de alarmas; (ix) funcionamiento del Sistema Comando de Incidentes; (x) protocolos de articulacion con organismos de socorro.</p>
    <div class="section-subtitle">TIPOS DE SIMULACRO</div>
    <p class="content-text"><strong>Simulacro de escritorio (tabletop):</strong> ejercicio teorico en el cual la brigada y la administracion analizan la respuesta a un escenario hipotetico sin desplazamiento fisico.<br><strong>Simulacro parcial:</strong> ejercicio en terreno que evalua uno o varios procedimientos especificos (evacuacion de una torre, uso de extintores, activacion de la alarma).<br><strong>Simulacro general:</strong> ejercicio en terreno que activa todos los procedimientos del Plan, incluida la evacuacion total hacia los puntos de encuentro.<br><strong>Simulacro avisado / no avisado:</strong> segun se informe previamente a los participantes o no, con el fin de evaluar el comportamiento espontaneo de la comunidad.</p>
    <div class="section-subtitle">FRECUENCIA Y PROGRAMACION ANUAL</div>
    <p class="content-text">La administracion programara al inicio de cada vigencia el cronograma de simulacros del ano, incluyendo como minimo un (1) simulacro general anual con participacion del mayor numero posible de residentes, un (1) simulacro parcial por semestre y ejercicios de tabletop para la brigada. Se recomienda alinear la programacion con el Simulacro Nacional de Respuesta a Emergencias convocado por la UNGRD.</p>
    <div class="section-subtitle">CRITERIOS DE EVALUACION Y REGISTRO</div>
    <p class="content-text">Cada simulacro sera evaluado por un observador externo o por un brigadista no participante, con base en los siguientes criterios: (i) tiempo de respuesta desde la activacion de la alarma hasta el inicio de la evacuacion; (ii) tiempo total de evacuacion; (iii) cumplimiento del procedimiento operativo normalizado; (iv) comportamiento de los ocupantes; (v) efectividad de las comunicaciones; (vi) estado de los equipos de emergencia. Se levantara acta del ejercicio con fecha, hora, escenario, numero de participantes, hallazgos, desviaciones respecto del procedimiento y plan de mejora. Los registros reposaran en el archivo del SG-SST de la copropiedad y estaran disponibles para verificacion por parte del Ministerio del Trabajo.</p>
    <?php if (!empty($inspeccion['simulacros_ia_texto'])): ?>
    <div class="section-subtitle">CRONOGRAMA DE SIMULACROS Y CAPACITACIONES PERSONALIZADO PARA <?= esc(strtoupper($nombreCliente)) ?></div>
    <p class="content-text" style="background:#fdf2e9; padding:8px; border-left:3px solid #d35400;"><?= nl2br(esc($inspeccion['simulacros_ia_texto'])) ?></p>
    <p class="content-text" style="font-size:8px; color:#666; font-style:italic;">Programa personalizado generado por IA con base en la inspeccion de Brigada+Simulacros del cliente.</p>
    <?php endif; ?>

    <!-- ============ PLAN DE AYUDA MUTUA ============ -->
    <div class="section-title">PLAN DE AYUDA MUTUA</div>
    <p class="content-text">El Plan de Ayuda Mutua (PAM) es el conjunto de acuerdos, protocolos y canales de comunicacion mediante los cuales <?= $nombreCliente ?> se articula con los organismos de socorro, las autoridades competentes y las copropiedades vecinas para optimizar la respuesta ante situaciones de emergencia cuya magnitud supere la capacidad de la brigada interna.</p>
    <div class="section-subtitle">ARTICULACION CON ORGANISMOS DE SOCORRO</div>
    <p class="content-text">La administracion mantendra actualizado en porteria y en la oficina administrativa el directorio telefonico con los contactos de: <strong>Cuerpo Oficial de Bomberos</strong> (linea 119 y estacion mas cercana identificada en la seccion ENTORNO del presente Plan), <strong>Policia Nacional</strong> (linea 123 y CAI mas cercano), <strong>Defensa Civil Colombiana</strong>, <strong>Cruz Roja Colombiana</strong>, <strong>hospitales y centros de salud de primer y segundo nivel cercanos</strong>, <strong>empresa de servicios publicos de energia, gas y acueducto</strong> y <strong>empresa mantenedora de ascensores</strong>. Estos contactos seran verificados trimestralmente por el administrador.</p>
    <div class="section-subtitle">COORDINACION CON COPROPIEDADES VECINAS</div>
    <p class="content-text">Se promovera la suscripcion de acuerdos de ayuda mutua con copropiedades vecinas para compartir recursos (puntos de encuentro alternos, equipos de primeros auxilios, apoyo logistico) en caso de emergencia mayor. Estos acuerdos quedaran documentados y seran socializados con la brigada y con los administradores de las copropiedades firmantes.</p>
    <div class="section-subtitle">PROTOCOLO DE ACTIVACION EXTERNA</div>
    <p class="content-text">Cuando la magnitud del evento supere la capacidad de respuesta interna, el jefe de brigada o el administrador activara el protocolo de ayuda externa: (i) llamada inmediata al numero unico de emergencias 123; (ii) comunicacion directa con el Cuerpo de Bomberos; (iii) notificacion al Consejo Distrital/Municipal de Gestion del Riesgo de Desastres cuando corresponda; (iv) atencion y guia a los organismos de socorro al momento de su llegada, entregando informacion clave sobre ubicacion del evento, numero de afectados y recursos disponibles.</p>

    <!-- ============ PLAN DE CONTINUIDAD Y RECUPERACION ============ -->
    <div class="section-title">PLAN DE CONTINUIDAD Y RECUPERACION</div>
    <p class="content-text">El Plan de Continuidad y Recuperacion reune las acciones que <?= $nombreCliente ?> ejecutara con posterioridad a una emergencia para restablecer la normalidad de los servicios, la infraestructura y la vida comunitaria de la copropiedad, en concordancia con el proceso de manejo del desastre establecido en el Decreto 2157 de 2017.</p>
    <div class="section-subtitle">EVALUACION POST-EVENTO</div>
    <p class="content-text">Inmediatamente despues de controlado el evento, el jefe de brigada, el administrador y un representante tecnico (cuando aplique, ingeniero o arquitecto) realizaran una inspeccion visual de la infraestructura para identificar danos en elementos estructurales, instalaciones electricas, instalaciones de gas, redes hidraulicas, ascensores y sistemas de seguridad. El resultado de esta inspeccion quedara consignado en un acta de novedad post-evento.</p>
    <div class="section-subtitle">RESTABLECIMIENTO DE SERVICIOS</div>
    <p class="content-text">Con base en la evaluacion anterior, se priorizara el restablecimiento de los servicios esenciales en el siguiente orden: (i) energia electrica y alumbrado de emergencia; (ii) suministro de agua potable; (iii) servicio de gas (previa verificacion de ausencia de fugas por la empresa prestadora); (iv) ascensores (previa certificacion de la empresa mantenedora); (v) sistemas de comunicacion interna y alarmas.</p>
    <div class="section-subtitle">ATENCION PSICOSOCIAL</div>
    <p class="content-text">Se coordinara con la EPS, ARL y organismos de salud el acompanamiento psicosocial a las personas afectadas por el evento, con especial atencion a menores, adultos mayores, personas con discapacidad y familias de victimas.</p>
    <div class="section-subtitle">LECCIONES APRENDIDAS Y ACTUALIZACION DEL PLAN</div>
    <p class="content-text">Dentro de los treinta (30) dias calendario siguientes al evento, el administrador convocara a una reunion de evaluacion con la brigada, el consejo de administracion y, cuando sea pertinente, con representantes de los organismos de socorro intervinientes. De esta reunion se derivaran las lecciones aprendidas y las acciones de mejora, que se incorporaran al presente Plan como parte del proceso de actualizacion anual.</p>

    <!-- ============ ANEXOS - EVALUACIONES DE SEGURIDAD ============ -->
<div class="annex-title">ANEXOS - EVALUACIONES DE SEGURIDAD</div>
    <p class="content-text">La gestion eficiente de la seguridad en propiedades horizontales requiere un enfoque integral que permita identificar y mitigar los riesgos. Cycloid Talent SAS ha llevado a cabo una revision exhaustiva de los principales elementos de seguridad necesarios para la creacion de este Plan de Emergencias.</p>

    <!-- ANEXO: INSPECCION LOCATIVA -->
    <?php if ($debugMode): ?><div class="dbg dbg-locativa"><span class="dbg-label">LOCATIVA — $ultimaLocativa + $hallazgosLocativa</span><?php endif; ?>
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
    <?php else: ?>
    <?php if ($debugMode): ?><div class="dbg-missing">⚠ $ultimaLocativa / $hallazgosLocativa vacíos — no hay inspección Locativa cargada</div><?php endif; ?>
    <?php endif; ?>
    <?php if ($debugMode): ?></div><?php endif; ?>

    <!-- ANEXO: MATRIZ VULNERABILIDAD -->
    <?php if ($debugMode): ?><div class="dbg dbg-matriz"><span class="dbg-label">MATRIZ VULNERABILIDAD — $ultimaMatriz</span><?php endif; ?>
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
    <?php endif; ?>
    <?php else: ?>
    <?php if ($debugMode): ?><div class="dbg-missing">⚠ $ultimaMatriz vacío — no hay Matriz de Vulnerabilidad</div><?php endif; ?>
    <?php endif; ?>
    <?php if ($debugMode): ?></div><?php endif; ?>

    <!-- ANEXO: EXTINTORES -->
    <?php if ($debugMode): ?><div class="dbg dbg-ext"><span class="dbg-label">EXTINTORES — $ultimaExt</span><?php endif; ?>
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
    <?php endif; ?>
    <?php else: ?>
    <?php if ($debugMode): ?><div class="dbg-missing">⚠ $ultimaExt vacío — no hay Inspección de Extintores</div><?php endif; ?>
    <?php endif; ?>
    <?php if ($debugMode): ?></div><?php endif; ?>

    <!-- ANEXO: BOTIQUIN -->
    <?php if ($debugMode): ?><div class="dbg dbg-bot"><span class="dbg-label">BOTIQUIN — $ultimaBot</span><?php endif; ?>
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
    <?php endif; ?>
    <?php else: ?>
    <?php if ($debugMode): ?><div class="dbg-missing">⚠ $ultimaBot vacío — no hay Inspección de Botiquín</div><?php endif; ?>
    <?php endif; ?>
    <?php if ($debugMode): ?></div><?php endif; ?>

    <!-- ANEXO: RECURSOS SEGURIDAD -->
    <?php if ($debugMode): ?><div class="dbg dbg-rec"><span class="dbg-label">REC. SEGURIDAD — $ultimaRec</span><?php endif; ?>
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
    <?php endif; ?>
    <?php else: ?>
    <?php if ($debugMode): ?><div class="dbg-missing">⚠ $ultimaRec vacío — no hay Inspección de Recursos de Seguridad</div><?php endif; ?>
    <?php endif; ?>
    <?php if ($debugMode): ?></div><?php endif; ?>

    <!-- ANEXO: COMUNICACIONES -->
    <?php if ($debugMode): ?><div class="dbg dbg-com"><span class="dbg-label">COMUNICACIONES — $ultimaCom</span><?php endif; ?>
    <?php if ($ultimaCom): ?>
<div class="section-title">EQUIPOS DE COMUNICACIONES</div>
    <p class="content-text">Los equipos de comunicaciones en una copropiedad son esenciales para coordinar las actividades del personal de seguridad, administracion y mantenimiento. Incluyen radios, intercomunicadores y telefonos para comunicacion rapida y efectiva.</p>
    <table class="info-table">
        <tr><td class="info-label">FECHA DE LA INSPECCION</td><td><?= !empty($ultimaCom['fecha_inspeccion']) ? date('d/m/Y', strtotime($ultimaCom['fecha_inspeccion'])) : '-' ?></td></tr>
    </table>
    <?php if (!empty($ultimaCom['observaciones'])): ?>
    <p class="content-text"><strong>Observaciones:</strong> <?= nl2br(esc($ultimaCom['observaciones'])) ?></p>
    <?php endif; ?>
    <?php else: ?>
    <?php if ($debugMode): ?><div class="dbg-missing">⚠ $ultimaCom vacío — no hay Inspección de Comunicaciones</div><?php endif; ?>
    <?php endif; ?>
    <?php if ($debugMode): ?></div><?php endif; ?>

    <!-- ANEXO: GABINETES (condicional) -->
    <?php if ($debugMode): ?><div class="dbg dbg-gab"><span class="dbg-label">GABINETES (condicional) — $ultimaGab</span><?php endif; ?>
    <?php if ($ultimaGab): ?>
    <div class="section-title">GABINETES CONTRA INCENDIO</div>
    <table class="info-table">
        <tr><td class="info-label">FECHA DE LA INSPECCION</td><td><?= !empty($ultimaGab['fecha_inspeccion']) ? date('d/m/Y', strtotime($ultimaGab['fecha_inspeccion'])) : '-' ?></td></tr>
    </table>
    <?php if (!empty($ultimaGab['observaciones'])): ?>
    <p class="content-text"><strong>Observaciones:</strong> <?= nl2br(esc($ultimaGab['observaciones'])) ?></p>
    <?php endif; ?>
    <?php else: ?>
    <?php if ($debugMode): ?><div class="dbg-missing">⚠ $ultimaGab vacío — solo aplica si `tiene_gabinetes_hidraulico = si`</div><?php endif; ?>
    <?php endif; ?>
    <?php if ($debugMode): ?></div><?php endif; ?>

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

    <!-- ============ OBSERVACIONES Y RECOMENDACIONES ============ -->
    <?php if (!empty($inspeccion['observaciones'])): ?>
    <div class="section-title">OBSERVACIONES Y RECOMENDACIONES</div>
    <p class="content-text"><?= nl2br(esc($inspeccion['observaciones'])) ?></p>
    <?php endif; ?>

</body>
</html>
