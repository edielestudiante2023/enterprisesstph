<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    @page { margin: 60px 50px; }
    body { font-family: sans-serif; font-size: 11px; color: #333; line-height: 1.5; }

    .header-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
    .header-table td { border: 1px solid #333; padding: 6px 10px; vertical-align: middle; }
    .header-logo { width: 100px; text-align: center; }
    .header-logo img { max-width: 90px; max-height: 60px; }
    .header-titulo { text-align: center; font-size: 10px; font-weight: bold; }
    .header-codigo { width: 100px; text-align: center; font-size: 9px; }

    h2 { text-align: center; font-size: 13px; margin: 20px 0 15px; text-transform: uppercase; }
    h3 { font-size: 11px; margin: 15px 0 8px; text-transform: uppercase; border-bottom: 1px solid #999; padding-bottom: 3px; }

    .datos-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
    .datos-table td { border: 1px solid #ccc; padding: 5px 8px; font-size: 10px; }
    .datos-table .label { background: #f5f5f5; font-weight: bold; width: 30%; }

    .texto { text-align: justify; margin-bottom: 10px; font-size: 10.5px; }

    .funciones-list { margin: 8px 0 15px 20px; font-size: 10.5px; }
    .funciones-list li { margin-bottom: 4px; }

    .normativa { margin: 8px 0 15px 15px; font-size: 10px; }
    .normativa li { margin-bottom: 3px; }

    .firma-section { margin-top: 40px; text-align: center; }
    .firma-img { max-width: 200px; max-height: 80px; }
    .firma-linea { border-top: 1px solid #333; width: 250px; margin: 5px auto 0; padding-top: 4px; font-size: 10px; }

    .verificacion { margin-top: 20px; text-align: center; font-size: 9px; color: #666; border-top: 1px solid #ddd; padding-top: 8px; }

    .habeas-data { margin-top: 20px; padding: 10px; border: 1px solid #ddd; background: #fafafa; font-size: 9px; text-align: justify; }
    .habeas-data strong { font-size: 9.5px; }
</style>
</head>
<body>

<!-- ENCABEZADO -->
<table class="header-table">
    <tr>
        <td class="header-logo">
            <?php if (!empty($logoBase64)): ?>
                <img src="<?= $logoBase64 ?>">
            <?php else: ?>
                <strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong>
            <?php endif; ?>
        </td>
        <td class="header-titulo">
            SISTEMA DE GESTION DE SEGURIDAD<br>Y SALUD EN EL TRABAJO<br><br>
            CARTA DE DESIGNACION DEL VIGIA DE<br>SEGURIDAD Y SALUD EN EL TRABAJO
        </td>
        <td class="header-codigo">
            Codigo<br>FT-SST-020<br><br>Version<br>001
        </td>
    </tr>
</table>

<!-- DATOS GENERALES -->
<table class="datos-table">
    <tr>
        <td class="label">Fecha:</td>
        <td><?= date('d/m/Y', strtotime($carta['created_at'])) ?></td>
        <td class="label">Cliente:</td>
        <td><?= esc($cliente['nombre_cliente'] ?? '') ?></td>
    </tr>
    <tr>
        <td class="label">NIT:</td>
        <td><?= esc($cliente['nit_cliente'] ?? '') ?></td>
        <td class="label">Consultor SST:</td>
        <td><?= esc($consultor['nombre_consultor'] ?? '') ?></td>
    </tr>
</table>

<!-- TÍTULO -->
<h2>Carta de Designacion del Vigia en Seguridad y Salud en el Trabajo</h2>

<!-- TEXTO PRINCIPAL -->
<p class="texto">
    Por medio de la presente, la administracion de <strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong>,
    identificada con NIT <?= esc($cliente['nit_cliente'] ?? '') ?>, designa al(a) senor(a)
    <strong><?= esc($carta['nombre_vigia']) ?></strong>, identificado(a) con documento de identidad
    No. <strong><?= esc($carta['documento_vigia']) ?></strong>, como <strong>VIGIA DE SEGURIDAD Y SALUD EN EL TRABAJO</strong>,
    de conformidad con la normatividad vigente en materia de Seguridad y Salud en el Trabajo.
</p>

<p class="texto">
    La designacion se realiza en cumplimiento de las disposiciones legales que regulan el Sistema de Gestion
    de Seguridad y Salud en el Trabajo (SG-SST), y tiene como proposito velar por el bienestar y la seguridad
    de los trabajadores y residentes del conjunto.
</p>

<!-- MARCO NORMATIVO -->
<h3>Marco Normativo</h3>
<ul class="normativa">
    <li><strong>Decreto 1072 de 2015</strong> - Decreto Unico Reglamentario del Sector Trabajo. Articulo 2.2.4.6.8: Obligaciones de los empleadores. Articulo 2.2.4.6.12: Documentacion del SG-SST.</li>
    <li><strong>Resolucion 0312 de 2019</strong> - Estandares Minimos del SG-SST. Articulo 3: Estandares minimos para empresas de 10 o menos trabajadores. Articulo 4: Estandares minimos para empresas de 11 a 50 trabajadores.</li>
    <li><strong>Ley 1562 de 2012</strong> - Por la cual se modifica el Sistema de Riesgos Laborales. Articulo 11: Se establece que cuando el empleador cuente con menos de 10 trabajadores, designara un Vigia de Seguridad y Salud en el Trabajo.</li>
    <li><strong>Decreto 1295 de 1994</strong> - Por el cual se determina la organizacion y administracion del Sistema General de Riesgos Profesionales.</li>
    <li><strong>Resolucion 2013 de 1986</strong> - Reglamenta la organizacion y funcionamiento de los Comites de Medicina, Higiene y Seguridad Industrial (hoy COPASST/Vigia SST).</li>
</ul>

<!-- FUNCIONES DEL VIGÍA -->
<h3>Funciones del Vigia de Seguridad y Salud en el Trabajo</h3>
<p class="texto">El Vigia de SST tendra las siguientes funciones y responsabilidades:</p>
<ol class="funciones-list">
    <li>Proponer a la administracion la adopcion de medidas y el desarrollo de actividades que procuren y mantengan la salud en los lugares y ambientes de trabajo.</li>
    <li>Proponer y participar en actividades de capacitacion en Seguridad y Salud en el Trabajo dirigidas a trabajadores, contratistas y residentes.</li>
    <li>Vigilar el desarrollo de las actividades que en materia de medicina, higiene y seguridad industrial se realicen en el conjunto.</li>
    <li>Colaborar con el analisis de las causas de los accidentes de trabajo y enfermedades laborales, y proponer al empleador las medidas correctivas necesarias.</li>
    <li>Visitar periodicamente los lugares de trabajo e inspeccionar los ambientes, maquinas, equipos y las operaciones realizadas por el personal, informando al empleador sobre la existencia de factores de riesgo.</li>
    <li>Servir como organismo de coordinacion entre el empleador y los trabajadores en la solucion de problemas relativos a la Seguridad y Salud en el Trabajo.</li>
    <li>Recibir copias de las conclusiones sobre inspecciones e investigaciones que realicen las autoridades de Seguridad y Salud en el Trabajo.</li>
    <li>Participar en la investigacion de los accidentes de trabajo que se presenten.</li>
</ol>

<p class="texto">
    El periodo del Vigia sera de dos (2) anos, contados a partir de la fecha de la presente designacion,
    y podra ser reelegido indefinidamente.
</p>

<!-- AUTORIZACIÓN DATOS PERSONALES -->
<div class="habeas-data">
    <strong>AUTORIZACION PARA EL TRATAMIENTO DE DATOS PERSONALES</strong><br><br>
    En cumplimiento de la <strong>Ley 1581 de 2012</strong> (Ley de Proteccion de Datos Personales) y el
    <strong>Decreto 1377 de 2013</strong>, autorizo de manera libre, expresa, previa e informada a
    <strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong> y a <strong>CYCLOID TALENT S.A.S.</strong>
    (Enterprise SST) para que realice la recoleccion, almacenamiento, uso, circulacion y tratamiento de mis
    datos personales suministrados en el presente documento, con las siguientes finalidades:<br><br>
    1) Cumplimiento de obligaciones legales en materia de Seguridad y Salud en el Trabajo.<br>
    2) Gestion del Sistema de Gestion de SST (SG-SST).<br>
    3) Comunicaciones relacionadas con mis funciones como Vigia SST.<br>
    4) Reporte a entidades gubernamentales cuando sea requerido por ley.<br><br>
    Declaro que he sido informado(a) de mis derechos como titular de datos personales, incluyendo el derecho
    a conocer, actualizar, rectificar y solicitar la supresion de mis datos, asi como a revocar la autorizacion
    otorgada, mediante comunicacion escrita dirigida al responsable del tratamiento.
</div>

<!-- SECCIÓN DE FIRMA -->
<div class="firma-section">
    <?php if (!empty($firmaBase64)): ?>
        <img src="<?= $firmaBase64 ?>" class="firma-img"><br>
    <?php else: ?>
        <br><br><br>
    <?php endif; ?>
    <div class="firma-linea">
        <strong><?= esc($carta['nombre_vigia']) ?></strong><br>
        CC <?= esc($carta['documento_vigia']) ?><br>
        Vigia de Seguridad y Salud en el Trabajo
        <?php if (!empty($carta['firma_fecha'])): ?>
            <br>Firmado: <?= date('d/m/Y H:i', strtotime($carta['firma_fecha'])) ?>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($carta['codigo_verificacion'])): ?>
<div class="verificacion">
    Documento firmado digitalmente | Codigo de verificacion: <strong><?= esc($carta['codigo_verificacion']) ?></strong>
    <br>Verificar en: <?= base_url('carta-vigia/verificar/' . $carta['codigo_verificacion']) ?>
    <?php if (!empty($carta['firma_ip'])): ?>
        | IP: <?= esc($carta['firma_ip']) ?>
    <?php endif; ?>
</div>
<?php endif; ?>

</body>
</html>
