<?php
$colorMap = [
    'bueno' => '#28a745', 'regular' => '#ffc107', 'deficiente' => '#dc3545',
    'no_tiene' => '#6c757d', 'no_aplica' => '#adb5bd',
];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 80px 50px 60px 50px; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 9px; color: #333; line-height: 1.3; padding: 10px 15px; }

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
        .info-label { font-weight: bold; color: #444; width: 160px; background: #f7f7f7; }

        .section-title { background: #1c2437; color: white; padding: 3px 8px; font-weight: bold; font-size: 9px; margin: 8px 0 4px; }
        .content-text { font-size: 9px; line-height: 1.4; margin-bottom: 5px; }
        .intro-text { font-size: 8px; line-height: 1.4; margin-bottom: 6px; text-align: justify; }

        .epp-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; border: 1px solid #ccc; }
        .epp-table th { background: #e8e8e8; padding: 4px 6px; font-size: 9px; border: 1px solid #ccc; text-align: left; }
        .epp-table td { padding: 3px 6px; font-size: 9px; border: 1px solid #ccc; }
        .epp-badge { padding: 2px 6px; color: white; font-size: 8px; font-weight: bold; }

        .foto-container { text-align: center; margin: 4px 0; }
        .foto-container img { max-width: 220px; max-height: 160px; border: 1px solid #ccc; }
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
            <td class="header-code">Codigo: FT-SST-213<br>Version: 001</td>
        </tr>
        <tr>
            <td class="header-title" style="font-size:10px;">REPORTE DE INSPECCION DE DOTACIONES</td>
            <td class="header-code">Fecha: <?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td>
        </tr>
    </table>

    <!-- TITULO -->
    <div class="main-title">REPORTE DE INSPECCION DE DOTACIONES</div>
    <div class="main-subtitle">VIGILANTE - <?= esc($cliente['nombre_cliente'] ?? '') ?></div>

    <!-- INTRODUCCION -->
    <div class="section-title">INTRODUCCION</div>
    <p class="intro-text">
        Cycloid Talent, como empresa comprometida con la seguridad y bienestar de sus colaboradores, establece el presente marco teorico como base fundamental para la realizacion de inspecciones de dotaciones de seguridad. Este documento tiene como objetivo principal consolidar los fundamentos legales y conceptuales que rigen la dotacion de elementos de proteccion personal (EPP) en el contexto laboral colombiano, en concordancia con las politicas internas de la organizacion.
    </p>

    <!-- MARCO LEGAL -->
    <div class="section-title">MARCO LEGAL</div>
    <p class="intro-text">
        <strong>Articulo 230 del Codigo Sustantivo del Trabajo:</strong> Este articulo establece que es obligacion del empleador proporcionar a sus trabajadores los elementos de proteccion personal (EPP) necesarios para garantizar su seguridad y salud en el trabajo.<br>
        <strong>Ley 1072 de 2011:</strong> Esta ley establece la obligacion legal del empleador de suministrar a sus trabajadores los EPP necesarios para desarrollar sus labores de manera segura y saludable.<br>
        <strong>Decreto 1443 de 2014:</strong> Este decreto reglamenta el Sistema General de Riesgos Profesionales y define los requisitos minimos de los EPP.<br>
        <strong>Resolucion 1072 de 2018:</strong> Esta resolucion establece los estandares tecnicos minimos para la fabricacion, importacion y comercializacion de EPP.
    </p>

    <!-- NORMAS TECNICAS COLOMBIANAS -->
    <div class="section-title">NORMAS TECNICAS COLOMBIANAS (NTC)</div>
    <p class="intro-text">
        El Programa de Elementos de Proteccion Personal (EPP) del Ministerio de Salud y Proteccion Social, publicado en Bogota en septiembre de 2017, establece diversas Normas Tecnicas Colombianas (NTC) para asegurar la seguridad y eficacia de los EPP utilizados en el pais. Entre estas normativas se incluyen:
    </p>
    <p class="intro-text">
        NTC 1523: Higiene y Seguridad. Cascos de Seguridad Industrial.<br>
        NTC 1726: Guantes de Caucho para Uso Industrial.<br>
        NTC 1825: Higiene y Seguridad. Protectores Individuales de Ojos.<br>
        NTC 2190: Guantes de proteccion. Requisitos Generales y Metodos de Ensayo.<br>
        NTC 3610: Higiene y Seguridad. Caretas para Soldar y Protectores Faciales.<br>
        NTC 1826: Higiene y Seguridad. Protectores Individuales de Ojos. Metodos de Ensayo No Opticos.<br>
        NTC 2859-10: Procedimientos de Muestreo para Inspeccion por Atributos Parte 10.<br>
        NTC 1584: Equipos de Proteccion Respiratoria. Definiciones y Clasificacion.<br>
        NTC 1589: Higiene y Seguridad. Equipos de Proteccion Respiratoria. Metodos de Ensayo.<br>
        NTC 1771: Higiene y Seguridad. Protectores de Ojos. Vocabulario.<br>
        NTC 2307: Cuero de Ganado Bovino para la Fabricacion de Guantes de Seguridad para Uso Industrial.<br>
        NTC 3398: Ropa Protectora. Proteccion Contra Sustancias Quimicas Liquidas.<br>
        NTC 5684: Guantes de Proteccion Contra Riesgos Mecanicos.
    </p>

    <!-- CONCEPTOS BASICOS -->
    <div class="section-title">CONCEPTOS BASICOS</div>
    <p class="intro-text">
        <strong>Elementos de Proteccion Personal (EPP):</strong> Cycloid Talent define los EPP como equipos o dispositivos disenados para proteger a sus colaboradores de los riesgos laborales. Estos se clasifican en tres categorias:<br>
        Primera categoria: Protegen contra riesgos de bajo impacto (golpes, cortes, raspaduras).<br>
        Segunda categoria: Protegen contra riesgos de mediano impacto (caidas de altura, proyeccion de particulas, productos quimicos).<br>
        Tercera categoria: Protegen contra riesgos de alto impacto (gases toxicos, radiaciones, ambientes confinados).<br><br>
        <strong>Dotacion de Seguridad:</strong> Cycloid Talent entiende la dotacion de seguridad como el conjunto de EPP que se suministran a los trabajadores de acuerdo a los riesgos especificos de su labor.<br><br>
        <strong>Inspeccion de Dotaciones:</strong> Cycloid Talent establece la inspeccion de dotaciones como un proceso fundamental para verificar el cumplimiento de la normativa legal sobre la dotacion de seguridad, asi como el estado y condiciones de los EPP suministrados a los colaboradores.
    </p>

    <!-- IMPORTANCIA DE LA INSPECCION -->
    <div class="section-title">IMPORTANCIA DE LA INSPECCION DE DOTACIONES</div>
    <p class="intro-text">
        Cycloid Talent considera las inspecciones de dotaciones como un pilar fundamental para garantizar la seguridad y salud de su talento humano asignado por terceros y proveedores dentro de la copropiedad, ya que permiten:<br>
        Verificar el cumplimiento de la normativa legal: Se comprueba que la empresa contratista este suministrando los EPP adecuados y en cantidad suficiente a sus trabajadores.<br>
        Evaluar el estado de los EPP: Se inspecciona que los EPP se encuentren en buen estado de conservacion, funcionamiento y limpieza.<br>
        Capacitar a los colaboradores de los contratistas: Se brinda informacion a los trabajadores sobre el uso correcto y mantenimiento de los EPP.<br>
        Prevenir accidentes de trabajo y enfermedades profesionales: Al garantizar el uso adecuado de los EPP, se reduce la probabilidad de que los trabajadores sufran accidentes o enfermedades relacionadas con su trabajo.
    </p>

    <!-- ASPECTOS A CONSIDERAR -->
    <div class="section-title">ASPECTOS A CONSIDERAR EN LA INSPECCION</div>
    <p class="intro-text">
        Cycloid Talent establece los siguientes aspectos como elementos clave a considerar en la inspeccion de dotaciones:<br>
        Identificacion de los riesgos laborales: Se deben identificar los riesgos especificos a los que estan expuestos los colaboradores en cada area de trabajo.<br>
        Seleccion de los EPP: De acuerdo a los riesgos identificados, se deben seleccionar los EPP adecuados para cada colaborador, considerando las caracteristicas especificas de cada puesto de trabajo.<br>
        Verificacion del estado de los EPP: Se debe inspeccionar que los EPP se encuentren en buen estado de conservacion, funcionamiento y limpieza, siguiendo las instrucciones del fabricante y las politicas internas de la empresa.<br>
        Capacitacion sobre el uso y mantenimiento de los EPP: Se debe brindar a los colaboradores informacion clara y actualizada sobre el uso correcto y mantenimiento de los EPP, incluyendo sesiones de entrenamiento y material informativo.<br>
        Registro de la inspeccion: Se debe realizar un registro detallado de la inspeccion que incluya la fecha, el nombre del responsable, los hallazgos encontrados, las acciones correctivas tomadas y las recomendaciones para futuras inspecciones.
    </p>

    <!-- DATOS DE LA INSPECCION -->
    <div class="section-title">DATOS DE LA INSPECCION</div>
    <table class="info-table">
        <tr>
            <td class="info-label">CLIENTE:</td>
            <td><?= esc($cliente['nombre_cliente'] ?? '') ?></td>
            <td class="info-label">FECHA:</td>
            <td><?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td>
        </tr>
        <tr>
            <td class="info-label">CONSULTOR:</td>
            <td><?= esc($consultor['nombre_consultor'] ?? '') ?></td>
            <td class="info-label">CONTRATISTA:</td>
            <td><?= esc($inspeccion['contratista'] ?? '') ?></td>
        </tr>
        <tr>
            <td class="info-label">SERVICIO:</td>
            <td><?= esc($inspeccion['servicio'] ?? '') ?></td>
            <td class="info-label">NOMBRE / CARGO:</td>
            <td><?= esc($inspeccion['nombre_cargo'] ?? '') ?></td>
        </tr>
        <?php if (!empty($inspeccion['actividades_frecuentes'])): ?>
        <tr>
            <td class="info-label">ACTIVIDADES:</td>
            <td colspan="3"><?= nl2br(esc($inspeccion['actividades_frecuentes'])) ?></td>
        </tr>
        <?php endif; ?>
    </table>

    <!-- REGISTRO FOTOGRAFICO -->
    <?php if (!empty($fotosBase64['foto_cuerpo_completo']) || !empty($fotosBase64['foto_cuarto_almacenamiento'])): ?>
    <div class="section-title">REGISTRO FOTOGRAFICO</div>
    <table style="width:100%; border-collapse:collapse; margin-bottom:8px;">
        <tr>
            <?php if (!empty($fotosBase64['foto_cuerpo_completo'])): ?>
            <td style="width:50%; text-align:center; padding:4px; vertical-align:top;">
                <div style="font-size:8px; font-weight:bold; margin-bottom:3px;">Cuerpo Completo</div>
                <img src="<?= $fotosBase64['foto_cuerpo_completo'] ?>" style="max-width:200px; max-height:160px; border:1px solid #ccc;">
            </td>
            <?php endif; ?>
            <?php if (!empty($fotosBase64['foto_cuarto_almacenamiento'])): ?>
            <td style="width:50%; text-align:center; padding:4px; vertical-align:top;">
                <div style="font-size:8px; font-weight:bold; margin-bottom:3px;">Cuarto de Almacenamiento</div>
                <img src="<?= $fotosBase64['foto_cuarto_almacenamiento'] ?>" style="max-width:200px; max-height:160px; border:1px solid #ccc;">
            </td>
            <?php endif; ?>
        </tr>
    </table>
    <?php endif; ?>

    <!-- ESTADO DE DOTACION EPP -->
    <div class="section-title">ESTADO DE DOTACION EPP</div>
    <table class="epp-table">
        <tr>
            <th style="width:5%;">#</th>
            <th>Elemento</th>
            <th style="width:25%;">Estado</th>
        </tr>
        <?php $num = 1; foreach ($itemsEpp as $key => $info):
            $estado = $inspeccion['estado_' . $key] ?? '';
            $estadoLabel = $estadosEpp[$estado] ?? 'Sin evaluar';
            $color = $colorMap[$estado] ?? '#6c757d';
        ?>
        <tr>
            <td style="text-align:center;"><?= $num++ ?></td>
            <td><?= $info['label'] ?></td>
            <td>
                <span class="epp-badge" style="background:<?= $color ?>;"><?= $estadoLabel ?></span>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <!-- CONCEPTO FINAL -->
    <?php if (!empty($inspeccion['concepto_final'])): ?>
    <div class="section-title">CONCEPTO FINAL</div>
    <p class="content-text"><?= nl2br(esc($inspeccion['concepto_final'])) ?></p>
    <?php endif; ?>

    <!-- OBSERVACIONES -->
    <?php if (!empty($inspeccion['observaciones'])): ?>
    <div class="section-title">OBSERVACIONES</div>
    <p class="content-text"><?= nl2br(esc($inspeccion['observaciones'])) ?></p>
    <?php endif; ?>

</body>
</html>
