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
    .program-list-item {
        margin: 4px 0 4px 20px;
        padding-left: 5px;
    }
    .resultado-table {
        width: 100%;
        border-collapse: collapse;
        margin: 8px 0 12px;
        font-size: 9px;
    }
    .resultado-table th {
        background: #1c2437;
        color: white;
        padding: 5px 6px;
        text-align: left;
        font-weight: bold;
        border: 1px solid #333;
    }
    .resultado-table td {
        padding: 4px 6px;
        border: 1px solid #ccc;
        vertical-align: top;
    }
    .resultado-table tr:nth-child(even) td {
        background: #f9f9f9;
    }
</style>
</head>
<body>

<?php
$nombreCliente = $cliente['nombre_cliente'] ?? 'CLIENTE';
$fechaDoc = !empty($inspeccion['fecha_programa']) ? date('d/m/Y', strtotime($inspeccion['fecha_programa'])) : date('d/m/Y');
?>

<!-- HEADER CORPORATIVO -->
<table class="header-table">
    <tr>
        <td class="logo-cell">
            <?php if (!empty($logoBase64)): ?>
                <img src="<?= $logoBase64 ?>" alt="Logo">
            <?php else: ?>
                <strong>LOGO</strong>
            <?php endif; ?>
        </td>
        <td class="title-cell">
            SG-SST<br>
            PLAN DE SANEAMIENTO BÁSICO
        </td>
        <td class="code-cell">
            <strong>Código:</strong> FT-SST-219<br>
            <strong>Versión:</strong> 001<br>
            <strong>Fecha:</strong> <?= $fechaDoc ?>
        </td>
    </tr>
</table>

<!-- TÍTULO PRINCIPAL -->
<div class="main-title">PLAN DE SANEAMIENTO BÁSICO</div>
<div class="subtitle"><?= esc($nombreCliente) ?></div>

<!-- INTRODUCCIÓN -->
<div class="section-title">INTRODUCCIÓN</div>

<p>La Propiedad Horizontal, en su condición de espacio colectivo destinado a la vivienda, el comercio y/o el uso mixto, tiene la responsabilidad de garantizar condiciones sanitarias y ambientales adecuadas que protejan la salud, la seguridad y el bienestar de los residentes, trabajadores, visitantes y demás usuarios de las áreas comunes.</p>

<p>El Plan de Saneamiento Básico (PSB) de <strong><?= esc($nombreCliente) ?></strong> se establece como una herramienta preventiva, técnica y de control, orientada a minimizar los riesgos sanitarios y ambientales derivados de la inadecuada gestión de residuos, deficiencias en la limpieza y desinfección, presencia de plagas y fallas en el suministro de agua potable, en cumplimiento de la normatividad vigente aplicable a las copropiedades en Colombia.</p>

<p>El marco constitucional que soporta este Plan se fundamenta en la Constitución Política de Colombia de 1991, particularmente en los artículos 49 y 79, los cuales consagran el derecho fundamental a la salud, al saneamiento ambiental y el derecho colectivo a gozar de un ambiente sano, así como el deber del Estado y de los particulares de proteger y conservar el ambiente.</p>

<p>En concordancia con lo anterior, el Código Sanitario Nacional – Ley 9 de 1979, establece la obligación de prevenir y controlar los factores de riesgo biológicos, físicos y químicos que puedan afectar la salud humana, asignando responsabilidades tanto a entidades públicas como a personas naturales y jurídicas, dentro de las cuales se encuentran las copropiedades sometidas al régimen de propiedad horizontal.</p>

<p>Así mismo, el presente Plan se estructura conforme a lo dispuesto en la Ley 675 de 2001, que regula el régimen de Propiedad Horizontal en Colombia, la cual establece como deber del administrador velar por la conservación, salubridad y adecuada utilización de los bienes comunes, garantizando condiciones seguras y saludables para la comunidad.</p>

<p>En materia de gestión ambiental y manejo de residuos sólidos, el Plan se soporta en la Ley 99 de 1993, la Ley 142 de 1994, el Decreto 1077 de 2015 (Decreto Único Reglamentario del Sector Vivienda), el Decreto 596 de 2016 sobre aprovechamiento de residuos sólidos, y la Resolución 2184 de 2019, que establece el código de colores para la separación en la fuente, aplicable a las áreas comunes de la copropiedad.</p>

<p>En lo relacionado con la calidad del agua para consumo humano, se adopta lo dispuesto en el Decreto 1575 de 2007 y la Resolución 2115 de 2007, que definen las características, instrumentos básicos y frecuencias de control para garantizar el suministro de agua potable en edificaciones residenciales, incluyendo el mantenimiento de tanques de almacenamiento.</p>

<p>El Plan de Saneamiento Básico de <strong><?= esc($nombreCliente) ?></strong> contempla la ejecución sistemática y documentada de actividades de limpieza, desinfección, control integral de plagas, manejo integral de residuos sólidos y aseguramiento del abastecimiento de agua potable, como acciones fundamentales para preservar condiciones higiénico-sanitarias óptimas en las zonas comunes y de servicio.</p>

<p>Este documento incluye los siguientes programas, cada uno con sus respectivos procedimientos, frecuencias, registros, listas de verificación, indicadores y responsables, para asegurar su correcta implementación, seguimiento y mejora continua:</p>

<ul class="lista-items">
    <li>Programa de Limpieza y Desinfección</li>
    <li>Programa de Manejo Integral de Residuos Sólidos</li>
    <li>Programa de Control Integral de Plagas</li>
    <li>Programa de Abastecimiento y Control de Agua Potable</li>
</ul>

<p>La aplicación del presente Plan reafirma el compromiso de la copropiedad con la salud pública, la convivencia, la prevención de riesgos sanitarios y el cumplimiento de la normatividad legal vigente, contribuyendo de manera directa a la calidad de vida de todos los habitantes del conjunto residencial.</p>

<!-- CONSOLIDACIÓN DE INDICADORES -->
<div class="page-break"></div>

<div class="section-title">CONSOLIDACIÓN DE INDICADORES Y DISPONIBILIDAD DE PROGRAMAS</div>

<p>Cada uno de los programas que integran el Plan de Saneamiento Básico de <strong><?= esc($nombreCliente) ?></strong> corresponde a un documento técnico independiente, el cual contiene su respectivo objetivo, alcance, definiciones, procedimientos, responsabilidades, registros, formatos y controles operativos.</p>

<p>No obstante, con el fin de facilitar la verificación por parte de las entidades de control territorial, autoridades sanitarias, auditorías internas y externas, el presente Plan de Saneamiento Básico consolida dentro de su estructura los indicadores de gestión, proceso y resultado asociados a cada programa.</p>

<p>Lo anterior permite contar, en un solo documento, con una visión integrada del desempeño sanitario de la copropiedad, sin que ello signifique que los programas se sustituyen o se unifican en un único archivo.</p>

<p>En consecuencia:</p>
<ul class="lista-items">
    <li>Los programas completos se encuentran disponibles para consulta cuando sean solicitados por la autoridad competente.</li>
    <li>Los indicadores consolidados constituyen evidencia objetiva de planificación, ejecución, seguimiento y mejora continua.</li>
    <li>Los resultados consolidados a la fecha del presente documento son considerados evidencia válida de gestión del Plan de Saneamiento Básico.</li>
</ul>

<!-- TABLA CONSOLIDADA DE INDICADORES -->
<div class="section-title">TABLA CONSOLIDADA DE INDICADORES DEL PLAN DE SANEAMIENTO BÁSICO</div>

<!-- Agua Potable -->
<div class="subsection-title">PROGRAMA DE ABASTECIMIENTO Y CONTROL DE AGUA POTABLE</div>
<table class="data-table">
    <tr>
        <th>Programa</th>
        <th>Indicador</th>
        <th>Tipo</th>
        <th>Meta</th>
        <th>Periodicidad</th>
        <th>Responsable</th>
    </tr>
    <tr>
        <td>Abastecimiento y control de agua potable</td>
        <td>Continuidad del servicio de agua potable en situaciones de suspensión</td>
        <td>Resultado</td>
        <td>100 %</td>
        <td>Semestral</td>
        <td>Administración / Responsable SG-SST</td>
    </tr>
    <tr>
        <td>Abastecimiento y control de agua potable</td>
        <td>Ejecución de limpieza y desinfección de tanques de agua potable</td>
        <td>Proceso</td>
        <td>100 %</td>
        <td>Semestral</td>
        <td>Administración / Responsable SG-SST</td>
    </tr>
</table>

<!-- Plagas -->
<div class="subsection-title">PROGRAMA DE CONTROL INTEGRAL DE PLAGAS Y ROEDORES</div>
<table class="data-table">
    <tr>
        <th>Programa</th>
        <th>Indicador</th>
        <th>Tipo</th>
        <th>Meta</th>
        <th>Periodicidad</th>
        <th>Responsable</th>
    </tr>
    <tr>
        <td>Control integral de plagas y roedores</td>
        <td>Ejecución de fumigación semestral</td>
        <td>Proceso</td>
        <td>100 %</td>
        <td>Semestral</td>
        <td>Administración / Responsable SG-SST</td>
    </tr>
    <tr>
        <td>Control integral de plagas y roedores</td>
        <td>Ejecución de desratización semestral</td>
        <td>Proceso</td>
        <td>100 %</td>
        <td>Semestral</td>
        <td>Administración / Responsable SG-SST</td>
    </tr>
</table>

<!-- Residuos -->
<div class="subsection-title">PROGRAMA DE MANEJO INTEGRAL DE RESIDUOS SÓLIDOS</div>
<table class="data-table">
    <tr>
        <th>Programa</th>
        <th>Indicador</th>
        <th>Tipo</th>
        <th>Meta</th>
        <th>Periodicidad</th>
        <th>Responsable</th>
    </tr>
    <tr>
        <td>Manejo integral de residuos sólidos</td>
        <td>Cumplimiento de condiciones higiénico–sanitarias del cuarto de residuos</td>
        <td>Resultado</td>
        <td>100 %</td>
        <td>Mensual</td>
        <td>Administración / Responsable SG-SST</td>
    </tr>
    <tr>
        <td>Manejo integral de residuos sólidos</td>
        <td>Cumplimiento en separación adecuada de residuos sólidos</td>
        <td>Resultado</td>
        <td>≥ 90 %</td>
        <td>Mensual</td>
        <td>Administración / Responsable SG-SST</td>
    </tr>
</table>

<!-- Limpieza -->
<div class="subsection-title">PROGRAMA DE LIMPIEZA Y DESINFECCIÓN</div>
<table class="data-table">
    <tr>
        <th>Programa</th>
        <th>Indicador</th>
        <th>Tipo</th>
        <th>Meta</th>
        <th>Periodicidad</th>
        <th>Responsable</th>
    </tr>
    <tr>
        <td>Limpieza y desinfección</td>
        <td>Cumplimiento de actividades de limpieza y desinfección</td>
        <td>Proceso</td>
        <td>≥ 95 %</td>
        <td>Mensual</td>
        <td>Administración del Conjunto</td>
    </tr>
    <tr>
        <td>Limpieza y desinfección</td>
        <td>Cobertura de desinfección en áreas críticas</td>
        <td>Proceso</td>
        <td>100 %</td>
        <td>Mensual</td>
        <td>Administración del Conjunto</td>
    </tr>
</table>

<!-- RESULTADOS CON CORTE A LA FECHA -->
<div class="page-break"></div>

<div class="section-title">RESULTADOS CON CORTE A LA FECHA</div>
<p><strong>Fecha del registro:</strong> <?= $fechaDoc ?></p>

<table class="resultado-table">
    <tr>
        <th style="width: 30%;">Campo</th>
        <th style="width: 50%;">Descripción</th>
        <th style="width: 20%;">Resultado</th>
    </tr>
    <tr>
        <td><strong>Promedio cumplimiento residuos</strong></td>
        <td>Promedio porcentual histórico del nivel de cumplimiento del Programa de Manejo Integral de Residuos Sólidos del cliente, calculado con todos los registros de inspección disponibles en el sistema.</td>
        <td style="text-align: center;">Consultar programa individual</td>
    </tr>
    <tr>
        <td><strong>Nivel residuos</strong></td>
        <td>Clasificación cualitativa del resultado del Programa de Manejo Integral de Residuos Sólidos, obtenida a partir del promedio de cumplimiento (cumple sobresaliente, cumple satisfactorio, cumple aceptable, cumple básico o no cumple).</td>
        <td style="text-align: center;">Consultar programa individual</td>
    </tr>
    <tr>
        <td><strong>Promedio cumplimiento limpieza y desinfección</strong></td>
        <td>Promedio porcentual histórico del nivel de cumplimiento del Programa de Limpieza y Desinfección del cliente, calculado con todos los registros de inspección disponibles en el sistema.</td>
        <td style="text-align: center;">Consultar programa individual</td>
    </tr>
    <tr>
        <td><strong>Nivel limpieza y desinfección</strong></td>
        <td>Clasificación cualitativa del resultado del Programa de Limpieza y Desinfección, obtenida a partir del promedio de cumplimiento (cumple sobresaliente, cumple satisfactorio, cumple aceptable, cumple básico o no cumple).</td>
        <td style="text-align: center;">Consultar programa individual</td>
    </tr>
    <tr>
        <td><strong>Promedio cumplimiento agua potable</strong></td>
        <td>Promedio porcentual histórico del nivel de cumplimiento del Programa de Abastecimiento y Control de Agua Potable del cliente, calculado con todos los registros de inspección disponibles en el sistema.</td>
        <td style="text-align: center;">Consultar programa individual</td>
    </tr>
    <tr>
        <td><strong>Nivel agua potable</strong></td>
        <td>Clasificación cualitativa del resultado del Programa de Abastecimiento y Control de Agua Potable, obtenida a partir del promedio de cumplimiento (cumple sobresaliente, cumple satisfactorio, cumple aceptable, cumple básico o no cumple).</td>
        <td style="text-align: center;">Consultar programa individual</td>
    </tr>
    <tr>
        <td><strong>Promedio cumplimiento plagas</strong></td>
        <td>Promedio porcentual histórico del nivel de cumplimiento del Programa de Control Integrado de Plagas del cliente, calculado con todos los registros de inspección disponibles en el sistema.</td>
        <td style="text-align: center;">Consultar programa individual</td>
    </tr>
    <tr>
        <td><strong>Nivel plagas</strong></td>
        <td>Clasificación cualitativa del resultado del Programa de Control Integrado de Plagas, obtenida a partir del promedio de cumplimiento (cumple sobresaliente, cumple satisfactorio, cumple aceptable, cumple básico o no cumple).</td>
        <td style="text-align: center;">Consultar programa individual</td>
    </tr>
</table>

<p class="nota">Nota: Los resultados detallados de cumplimiento y niveles de clasificación se encuentran disponibles en cada programa individual (FT-SST-225 Limpieza y Desinfección, FT-SST-226 Manejo Integral de Residuos Sólidos, FT-SST-227 Control Integrado de Plagas, FT-SST-228 Abastecimiento y Control de Agua Potable). Estos programas pueden ser consultados de manera independiente cuando la autoridad competente lo requiera.</p>

</body>
</html>