<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    @page {
        margin: 80px 50px 60px 60px;
    }
    body {
        font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
        font-size: 10px;
        color: #333;
        line-height: 1.5;
        padding: 0;
        margin: 0;
    }
    .header-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
    }
    .header-table td {
        border: 2px solid #333;
        padding: 6px 8px;
        vertical-align: middle;
    }
    .header-table .logo-cell {
        width: 100px;
        text-align: center;
    }
    .header-table .logo-cell img {
        max-width: 90px;
        max-height: 55px;
    }
    .header-table .title-cell {
        text-align: center;
        font-weight: bold;
        font-size: 10px;
    }
    .header-table .code-cell {
        width: 120px;
        font-size: 9px;
    }
    .main-title {
        text-align: center;
        font-weight: bold;
        font-size: 13px;
        color: #1c2437;
        margin: 20px 0 5px;
    }
    .subtitle {
        text-align: center;
        font-weight: bold;
        font-size: 11px;
        color: #333;
        margin-bottom: 15px;
    }
    .section-title {
        font-weight: bold;
        font-size: 11px;
        color: #1c2437;
        margin-top: 18px;
        margin-bottom: 6px;
        border-bottom: 1px solid #1c2437;
        padding-bottom: 3px;
    }
    .subsection-title {
        font-weight: bold;
        font-size: 10px;
        color: #1c2437;
        margin-top: 12px;
        margin-bottom: 4px;
    }
    .data-table {
        width: 100%;
        border-collapse: collapse;
        margin: 8px 0 12px;
        font-size: 9px;
    }
    .data-table th {
        background: #1c2437;
        color: white;
        padding: 5px 6px;
        text-align: left;
        font-weight: bold;
        border: 1px solid #333;
    }
    .data-table td {
        padding: 4px 6px;
        border: 1px solid #ccc;
        vertical-align: top;
    }
    .data-table tr:nth-child(even) td {
        background: #f9f9f9;
    }
    .lista-items {
        margin: 4px 0 8px 20px;
        padding: 0;
    }
    .lista-items li {
        margin-bottom: 3px;
    }
    p {
        margin: 4px 0 8px;
        text-align: justify;
    }
    .definition-term {
        font-weight: bold;
        margin-top: 8px;
        margin-bottom: 2px;
    }
    .definition-text {
        margin: 0 0 6px 0;
        text-align: justify;
    }
    .nota {
        font-style: italic;
        color: #555;
        margin: 6px 0;
    }
    .page-break {
        page-break-before: always;
    }
    .indicador-box {
        border: 1px solid #ccc;
        padding: 8px;
        margin: 8px 0;
        background: #fafafa;
    }
    .indicador-box .ind-label {
        font-weight: bold;
        color: #1c2437;
    }
    .tanque-info {
        border: 1px solid #1c2437;
        padding: 8px 12px;
        margin: 8px 0 12px;
        background: #f5f8ff;
    }
    .tanque-info .tanque-label {
        font-weight: bold;
        color: #1c2437;
    }
</style>
</head>
<body>

<?php
$nombreCliente = $cliente['nombre_cliente'] ?? 'CONJUNTO RESIDENCIAL';
$cantTanques = $inspeccion['cantidad_tanques'] ?? '—';
$capIndividual = $inspeccion['capacidad_individual'] ?? '—';
$capTotal = $inspeccion['capacidad_total'] ?? '—';
?>

<!-- HEADER -->
<table class="header-table">
    <tr>
        <td class="logo-cell" rowspan="2">
            <?php if (!empty($logoBase64)): ?>
                <img src="<?= $logoBase64 ?>" alt="Logo">
            <?php else: ?>
                <strong style="font-size: 8px;">LOGO</strong>
            <?php endif; ?>
        </td>
        <td class="title-cell">
            SISTEMA DE GESTIÓN DE SEGURIDAD Y SALUD EN EL TRABAJO
        </td>
        <td class="code-cell">
            <strong>Código:</strong> FT-SST-228<br>
            <strong>Versión:</strong> 001
        </td>
    </tr>
    <tr>
        <td class="title-cell">
            PROGRAMA DE ABASTECIMIENTO Y CONTROL DE AGUA POTABLE
        </td>
        <td class="code-cell">
            <strong>Fecha:</strong> <?= !empty($inspeccion['fecha_programa']) ? date('d/m/Y', strtotime($inspeccion['fecha_programa'])) : '' ?>
        </td>
    </tr>
</table>

<!-- TÍTULO PRINCIPAL -->
<div class="main-title">1. PROGRAMA DE ABASTECIMIENTO DE AGUA POTABLE</div>
<div class="subtitle"><?= esc($nombreCliente) ?></div>

<!-- 1.1 OBJETIVO -->
<div class="section-title">1.1 OBJETIVO</div>
<p>Definir los elementos, actividades y responsabilidades necesarias para garantizar el suministro continuo de agua potable en <?= esc($nombreCliente) ?>, asegurando su calidad para consumo humano, conforme a la normatividad sanitaria vigente.</p>

<!-- 1.2 ALCANCE -->
<div class="section-title">1.2 ALCANCE</div>
<p>Aplica para <?= esc($nombreCliente) ?> y para los contratistas encargados de las actividades de limpieza, desinfección y mantenimiento de tanques y redes de distribución de agua potable.</p>
<p><?= esc($nombreCliente) ?> cuenta con tanques de almacenamiento de agua potable que garantizan el suministro del recurso incluso en situaciones de emergencia.</p>

<div class="tanque-info">
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td class="tanque-label" style="width: 50%; padding: 4px 0;">CANTIDAD DE TANQUES:</td>
            <td style="padding: 4px 0;"><?= esc($cantTanques) ?></td>
        </tr>
        <tr>
            <td class="tanque-label" style="padding: 4px 0;">CAPACIDAD INDIVIDUAL DE CADA TANQUE:</td>
            <td style="padding: 4px 0;"><?= esc($capIndividual) ?></td>
        </tr>
        <tr>
            <td class="tanque-label" style="padding: 4px 0;">CAPACIDAD TOTAL DE ALMACENAMIENTO:</td>
            <td style="padding: 4px 0;"><?= esc($capTotal) ?></td>
        </tr>
    </table>
</div>

<!-- 1.3 DEFINICIONES -->
<div class="section-title">1.3 DEFINICIONES</div>

<div class="definition-term">Abastecimiento de Agua Potable:</div>
<p class="definition-text">De acuerdo con el Reglamento Técnico del Sector de Agua Potable y Saneamiento Básico – RAS 2000, el agua para consumo humano no debe contener microorganismos patógenos ni sustancias tóxicas o nocivas para la salud, y debe cumplir con los requisitos de calidad microbiológicos y fisicoquímicos establecidos en la normatividad sanitaria vigente.</p>

<div class="definition-term">Agua Potable:</div>
<p class="definition-text">Agua que cumple con los requisitos físicos, químicos, microbiológicos y organolépticos exigidos para el consumo humano.</p>

<div class="definition-term">Desinfección:</div>
<p class="definition-text">Proceso físico y/o químico mediante el cual se eliminan o inactivan microorganismos patógenos presentes en el agua o en superficies.</p>

<div class="definition-term">Red de Distribución:</div>
<p class="definition-text">Conjunto de tuberías, accesorios y uniones que transportan el agua desde la fuente de captación hasta los puntos de consumo.</p>

<!-- 1.4 PROCESO DE ABASTECIMIENTO -->
<div class="section-title">1.4 PROCESO DE ABASTECIMIENTO DE AGUA POTABLE</div>

<div class="definition-term">a. Fuente de captación:</div>
<p class="definition-text">El agua utilizada en <?= esc($nombreCliente) ?> proviene de la red pública de acueducto suministrada por la Empresa de Servicios Públicos del municipio correspondiente, la cual garantiza condiciones adecuadas en sus características físicas, químicas y microbiológicas.</p>

<div class="definition-term">b. Sistema de captación:</div>
<p class="definition-text">La captación del agua se realiza mediante tanques aéreos y/o subterráneos, los cuales permiten garantizar el suministro del recurso en situaciones de emergencia o suspensión temporal del servicio.</p>

<div class="definition-term">c. Distribución:</div>
<p class="definition-text">El sistema de distribución está conformado por tuberías que transportan el agua desde los tanques de almacenamiento hacia los puntos de suministro del conjunto residencial.</p>

<div class="definition-term">d. Almacenamiento:</div>
<p class="definition-text"><?= esc($nombreCliente) ?> debe garantizar un abastecimiento mínimo de agua potable para un periodo de setenta y dos (72) horas, para lo cual se realizará un aforo del consumo, siguiendo el siguiente procedimiento:</p>
<ul class="lista-items">
    <li>Llenar los tanques hasta su capacidad máxima conocida.</li>
    <li>Cerrar el suministro de entrada y permitir únicamente la salida de agua hacia el conjunto.</li>
    <li>Medir el volumen consumido durante un periodo de veinticuatro (24) horas.</li>
    <li>Multiplicar el consumo diario por tres (3) para determinar la capacidad mínima requerida de almacenamiento.</li>
</ul>
<p>Este aforo permitirá identificar variaciones en el consumo, fugas, sobreuso del recurso o la necesidad de ajustes en el sistema.</p>

<!-- 1.5 MANTENIMIENTO -->
<div class="page-break"></div>
<div class="section-title">1.5 MANTENIMIENTO, LIMPIEZA Y DESINFECCIÓN</div>
<p>La limpieza y desinfección de los tanques de almacenamiento y redes de distribución se debe realizar como mínimo cada seis (6) meses, a través de una empresa especializada que cuente con:</p>
<ul class="lista-items">
    <li>Personal capacitado y certificado en trabajo en alturas.</li>
    <li>Elementos de protección personal acordes a la actividad.</li>
    <li>Cumplimiento de la normatividad sanitaria y de seguridad y salud en el trabajo.</li>
    <li>Entrega de certificado de limpieza y desinfección.</li>
    <li>Concepto sanitario favorable vigente, cuando aplique.</li>
</ul>

<!-- 1.6 CONTROLES -->
<div class="section-title">1.6 CONTROLES</div>
<p>Las muestras de agua deben ser representativas y permitir la evaluación de la calidad microbiológica, principalmente para identificar contaminación de origen fecal.</p>
<p>El análisis microbiológico se realizará de manera semestral, conforme a lo establecido en la Resolución 2115 de 2007, con el fin de verificar la efectividad del proceso de limpieza y desinfección de los tanques de almacenamiento.</p>

<!-- 1.7 RESPONSABILIDADES -->
<div class="section-title">1.7 RESPONSABILIDADES</div>

<div class="definition-term">Administración de <?= esc($nombreCliente) ?></div>
<ul class="lista-items">
    <li>Garantizar la implementación del Programa de Abastecimiento de Agua Potable.</li>
    <li>Contratar empresas certificadas para la limpieza y desinfección de tanques.</li>
    <li>Custodiar certificados, informes y resultados de análisis de agua.</li>
    <li>Programar y supervisar el cumplimiento del mantenimiento semestral.</li>
</ul>

<div class="definition-term">Empresa contratista</div>
<ul class="lista-items">
    <li>Ejecutar la limpieza y desinfección conforme a la normatividad vigente.</li>
    <li>Utilizar productos autorizados y entregar fichas técnicas y hojas de seguridad.</li>
    <li>Entregar certificado de la actividad realizada.</li>
</ul>

<div class="definition-term">Personal operativo</div>
<ul class="lista-items">
    <li>Reportar fugas, daños o condiciones inseguras en el sistema.</li>
    <li>Facilitar las actividades de inspección, limpieza y mantenimiento.</li>
</ul>

<div class="definition-term">Responsable del SG-SST</div>
<ul class="lista-items">
    <li>Verificar condiciones seguras de trabajo durante la ejecución de las actividades.</li>
    <li>Hacer seguimiento a los informes y certificados entregados.</li>
</ul>

<!-- 1.8 PROCEDIMIENTO -->
<div class="section-title">1.8 PROCEDIMIENTO DE LIMPIEZA Y DESINFECCIÓN DE TANQUES DE AGUA</div>
<ol class="lista-items">
    <li>Garantizar que el personal cuente con EPP certificados para trabajo en alturas.</li>
    <li>Verificar previamente posición segura de trabajo, puntos de anclaje, accesos y desagües.</li>
    <li>Disponer implementos necesarios (cepillos, escobas, baldes, hidrolavadora).</li>
    <li>Cerrar válvula de entrada y abrir salida para vaciado total.</li>
    <li>Ingresar cuando el nivel esté entre 20 y 30 cm.</li>
    <li>Retirar sedimentos del fondo.</li>
    <li>Cepillar paredes y pisos solo con agua.</li>
    <li>Enjuagar completamente el tanque antes de la desinfección.</li>
</ol>

<!-- 1.9 DESINFECCIÓN -->
<div class="page-break"></div>
<div class="section-title">1.9 DESINFECCIÓN DE TANQUES DE AGUA</div>
<ol class="lista-items" type="a">
    <li>Preparar la solución desinfectante de la siguiente manera: en un recipiente de mil (1000) mililitros, adicionar cien (100) mililitros de Hipoclorito de Sodio al cinco por ciento (5 %) y mezclar de forma homogénea. Para un tanque de mil (1000) litros, se deben aplicar diez (10) litros de esta solución, lo que equivale a un (1) litro de hipoclorito de sodio al cinco por ciento (5 %).</li>
    <li>Aplicar la solución de hipoclorito de sodio según la concentración indicada, utilizando rociadores o máquinas aspersoras totalmente plásticas, realizando la actividad en el menor tiempo posible para asegurar la efectividad del desinfectante.</li>
    <li>Restregar enérgicamente las paredes y el piso del tanque con la solución desinfectante, con el fin de eliminar cualquier residuo de suciedad y garantizar la desinfección completa de las superficies.</li>
    <li>Retirar todos los residuos generados durante la desinfección, realizando un enjuague con agua limpia.</li>
    <li>Cerrar el desagüe del tanque y permitir nuevamente la entrada de agua potable.</li>
    <li>Verificar el cloro residual, el cual deberá encontrarse hasta en cinco (5) ppm, y confirmar que el pH del agua esté dentro del rango de seis (6) a siete (7), conforme a la normatividad sanitaria vigente.</li>
    <li>Permitir el llenado total del tanque y restablecer el suministro de agua potable a <?= esc($nombreCliente) ?>.</li>
</ol>

<!-- 1.10 CONTROL Y SEGUIMIENTO -->
<div class="section-title">1.10 CONTROL Y SEGUIMIENTO</div>
<p>La Administración de <?= esc($nombreCliente) ?> será responsable de verificar el cumplimiento de la frecuencia mínima de limpieza y desinfección de los tanques de almacenamiento de agua potable.</p>
<p>La empresa contratista deberá entregar el certificado de limpieza y desinfección con la información mínima exigida, el cual será archivado como soporte del programa.</p>

<!-- 1.11 INDICADORES -->
<div class="section-title">1.11 INDICADORES</div>
<p>Con el fin de verificar la correcta ejecución, continuidad del servicio y eficacia sanitaria del Programa de Abastecimiento y Control de Agua Potable en <?= esc($nombreCliente) ?>, se evalúan los siguientes indicadores de gestión:</p>

<div class="subsection-title">Garantía de continuidad del suministro de agua potable</div>
<div class="indicador-box">
    <table class="data-table" style="margin: 0;">
        <tr><td class="ind-label" style="width: 40%; background: #f0f0f0;">Nombre del indicador</td><td>Continuidad del servicio de agua potable en situaciones de suspensión</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Fórmula</td><td>(Eventos sin afectación del suministro / Eventos de suspensión del servicio) × 100</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Tipo de indicador</td><td>Resultado</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Meta sugerida</td><td>100 %</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Periodicidad</td><td>Semestral</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Fuente de información</td><td>Registros de novedades del servicio de acueducto + bitácora de administración + reportes de mantenimiento</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Responsable del seguimiento</td><td>Administración / Responsable SG-SST</td></tr>
    </table>
</div>
<p>Mide si, ante suspensiones del servicio público de acueducto, <?= esc($nombreCliente) ?> logra mantener el abastecimiento de agua potable a través de su sistema de almacenamiento. Un resultado inferior al 100 % indica insuficiencia en la capacidad de almacenamiento o fallas en el sistema.</p>

<div class="subsection-title">Cumplimiento de limpieza y desinfección semestral de tanques</div>
<div class="indicador-box">
    <table class="data-table" style="margin: 0;">
        <tr><td class="ind-label" style="width: 40%; background: #f0f0f0;">Nombre del indicador</td><td>Ejecución de limpieza y desinfección de tanques de agua potable</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Fórmula</td><td>(Número de limpiezas y desinfecciones realizadas en el semestre / 1) × 100</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Tipo de indicador</td><td>Proceso</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Meta sugerida</td><td>100 %</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Periodicidad</td><td>Semestral</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Fuente de información</td><td>Certificados de limpieza y desinfección de tanques emitidos por empresa contratada</td></tr>
        <tr><td class="ind-label" style="background: #f0f0f0;">Responsable del seguimiento</td><td>Administración / Responsable SG-SST</td></tr>
    </table>
</div>
<p>Verifica que se haya realizado como mínimo una (1) limpieza y desinfección de los tanques de almacenamiento dentro de cada semestre. La ausencia de certificado implica incumplimiento del programa.</p>

</body>
</html>
