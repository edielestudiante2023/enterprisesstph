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

        .main-title { text-align: center; font-size: 11px; font-weight: bold; margin: 8px 0 6px; color: #1c2437; }

        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; border: 1px solid #ccc; }
        .info-table td { padding: 3px 6px; font-size: 9px; border: 1px solid #ccc; }
        .info-label { font-weight: bold; color: #444; width: 180px; background: #f7f7f7; }

        .section-title { background: #1c2437; color: white; padding: 3px 8px; font-weight: bold; font-size: 9px; margin: 8px 0 4px; }

        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .data-table th { background: #e8e8e8; border: 1px solid #aaa; padding: 3px 4px; font-size: 7px; text-align: center; }
        .data-table td { border: 1px solid #ccc; padding: 2px 3px; font-size: 7px; text-align: center; vertical-align: middle; }

        .content-text { font-size: 9px; line-height: 1.4; margin-bottom: 5px; }
        .intro-text { font-size: 8px; line-height: 1.4; margin-bottom: 6px; text-align: justify; }
        .intro-subtitle { font-weight: bold; font-size: 8px; margin: 4px 0 2px; }
        .intro-list { font-size: 8px; margin: 0 0 4px 15px; }

        .gab-img { max-width: 80px; max-height: 60px; border: 1px solid #ccc; }
        .photo-row { margin: 4px 0; }
        .photo-row img { max-width: 200px; max-height: 120px; border: 1px solid #ccc; }

        .val-si { color: #155724; font-weight: bold; }
        .val-no { color: #721c24; font-weight: bold; }
        .val-bueno { color: #155724; }
        .val-regular { color: #856404; }
        .val-malo { color: #721c24; }
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
            <td class="header-code">Codigo: FT-SST-203<br>Version: 001</td>
        </tr>
        <tr>
            <td class="header-title" style="font-size:10px;">INSPECCION DE GABINETES CONTRA INCENDIO Y SENSORES</td>
            <td class="header-code">Fecha: <?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td>
        </tr>
    </table>

    <!-- TITULO -->
    <div class="main-title">INSPECCION DE GABINETES CONTRA INCENDIO<br><?= esc($cliente['nombre_cliente'] ?? '') ?></div>

    <!-- INTRODUCCION -->
    <div class="section-title">INTRODUCCION</div>
    <p class="intro-subtitle">Norma Tecnica Colombiana para Gabinetes Contra Incendios en Propiedad Horizontal</p>
    <p class="intro-text">
        En el contexto de la propiedad horizontal en Colombia, la seguridad contra incendios cobra especial relevancia debido a la concentracion de personas y bienes en un mismo espacio. La Norma Tecnica Colombiana (NTC) 1800, que establece los requisitos minimos para los gabinetes contra incendios, se convierte en un elemento crucial para garantizar la proteccion de las personas y bienes en este tipo de edificaciones.
    </p>
    <p class="intro-subtitle">Alcance y Aplicacion de la Norma NTC 1800</p>
    <p class="intro-text">
        La NTC 1800 se aplica a los gabinetes contra incendios tipo I, II y III instalados en edificios de propiedad horizontal en Colombia. Estos gabinetes se conectan a la red de tuberias de agua contra incendios y permiten combatir incendios de manera efectiva.
    </p>
    <p class="intro-subtitle">Componentes Esenciales de los Gabinetes Contra Incendios en Propiedad Horizontal</p>
    <p class="intro-text">
        En el contexto de la propiedad horizontal, la NTC 1800 establece los mismos componentes esenciales para los gabinetes contra incendios que en otros tipos de edificaciones:<br><br>
        <strong>Manguera:</strong> La manguera es indispensable para dirigir un chorro de agua hacia la base del fuego. Su longitud y tipo dependeran del tamano del edificio, el numero de unidades y el nivel de riesgo de incendio.<br><br>
        <strong>Hacha:</strong> El hacha es una herramienta multifuncional que puede ser utilizada para romper obstaculos, derribar escombros e incluso cortar materiales en caso de ser necesario.<br><br>
        <strong>Valvula:</strong> La valvula permite controlar el flujo de agua desde la red contra incendios hacia la manguera. Debe ser facil de operar y estar claramente identificada.<br><br>
        <strong>Boquilla:</strong> La boquilla permite ajustar el tipo de chorro de agua (recto, nebulizado, etc.) que se dirige hacia el fuego.<br><br>
        <strong>Soporte para manguera:</strong> El soporte para manguera permite almacenar la manguera de forma ordenada y segura dentro del gabinete.<br><br>
        <strong>Gabinete:</strong> El gabinete alberga todos los componentes del sistema contra incendios y debe ser fabricado con materiales resistentes al fuego y a la corrosion. Su tamano debe ser adecuado para albergar todo el equipo necesario.<br><br>
        <strong>Senalizacion:</strong> El gabinete debe estar claramente identificado con la senal de "Gabinete contra incendios" para facilitar su ubicacion en caso de una emergencia.
    </p>
    <p class="intro-subtitle">Requisitos Adicionales para los Gabinetes Contra Incendios en Propiedad Horizontal</p>
    <p class="intro-text">
        Ademas de los componentes esenciales, la NTC 1800 establece requisitos adicionales para los gabinetes contra incendios en propiedad horizontal, tomando en cuenta las caracteristicas especificas de este tipo de edificaciones:<br><br>
        <strong>Ubicacion estrategica:</strong> Los gabinetes contra incendios deben ubicarse en puntos estrategicos de la edificacion, considerando las vias de evacuacion y los accesos a las unidades habitacionales.<br><br>
        <strong>Accesibilidad:</strong> Los gabinetes deben ser facilmente accesibles para todos los residentes, incluyendo personas con movilidad reducida.<br><br>
        <strong>Senalizacion clara:</strong> La senalizacion de los gabinetes debe ser clara, visible y comprensible para todas las personas, utilizando simbolos universales y lenguaje sencillo.<br><br>
        <strong>Capacitacion y entrenamiento:</strong> Es fundamental que los residentes reciban capacitacion y entrenamiento en el uso correcto de los gabinetes contra incendios, incluyendo la operacion de la manguera, la valvula y la boquilla.<br><br>
        <strong>Planes de emergencia:</strong> La propiedad horizontal debe contar con planes de emergencia que incluyan procedimientos especificos para la utilizacion de los gabinetes contra incendios en caso de un incendio.
    </p>
    <p class="intro-subtitle">Importancia de la Norma NTC 1800 en Propiedad Horizontal</p>
    <p class="intro-text">
        El cumplimiento de la NTC 1800 en propiedad horizontal es de vital importancia para garantizar la seguridad contra incendios en este tipo de edificaciones. Los gabinetes contra incendios, disenados y mantenidos de acuerdo con la norma, pueden ser la clave para salvar vidas y proteger bienes en caso de un incendio.
    </p>
    <p class="intro-subtitle">Responsabilidades en la Propiedad Horizontal</p>
    <p class="intro-text">
        En el contexto de la propiedad horizontal, la responsabilidad del cumplimiento de la NTC 1800 recae en la administracion de la unidad residencial, quien debe velar por la instalacion, inspeccion, mantenimiento y pruebas periodicas de los gabinetes contra incendios.
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

    <!-- GABINETES CONTRA INCENDIO -->
    <div class="section-title">GABINETES CONTRA INCENDIO</div>
    <table class="info-table">
        <tr>
            <td class="info-label">Cuenta con gabinetes:</td>
            <td class="<?= ($inspeccion['tiene_gabinetes'] ?? 'SI') === 'SI' ? 'val-si' : 'val-no' ?>"><?= esc($inspeccion['tiene_gabinetes'] ?? 'SI') ?></td>
            <td class="info-label">Entregados por constructora:</td>
            <td class="<?= ($inspeccion['entregados_constructora'] ?? 'SI') === 'SI' ? 'val-si' : 'val-no' ?>"><?= esc($inspeccion['entregados_constructora'] ?? 'SI') ?></td>
        </tr>
        <tr>
            <td class="info-label">Cantidad de gabinetes:</td>
            <td style="font-weight:bold;"><?= $inspeccion['cantidad_gabinetes'] ?? 0 ?></td>
            <td class="info-label">&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <?php if (!empty($inspeccion['elementos_gabinete'])): ?>
        <tr>
            <td class="info-label">Elementos por gabinete:</td>
            <td colspan="3"><?= nl2br(esc($inspeccion['elementos_gabinete'])) ?></td>
        </tr>
        <?php endif; ?>
        <?php if (!empty($inspeccion['ubicacion_gabinetes'])): ?>
        <tr>
            <td class="info-label">Ubicacion de gabinetes:</td>
            <td colspan="3"><?= nl2br(esc($inspeccion['ubicacion_gabinetes'])) ?></td>
        </tr>
        <?php endif; ?>
        <?php if (!empty($inspeccion['estado_senalizacion_gab'])): ?>
        <tr>
            <td class="info-label">Estado senalizacion:</td>
            <td colspan="3"><?= nl2br(esc($inspeccion['estado_senalizacion_gab'])) ?></td>
        </tr>
        <?php endif; ?>
    </table>

    <!-- Fotos gabinetes generales -->
    <?php if (!empty($fotosBase64['foto_gab_1']) || !empty($fotosBase64['foto_gab_2'])): ?>
    <table style="width:100%; margin-bottom:6px;">
        <tr>
            <?php if (!empty($fotosBase64['foto_gab_1'])): ?>
            <td style="text-align:center; padding:4px;"><img src="<?= $fotosBase64['foto_gab_1'] ?>" style="max-width:200px; max-height:120px; border:1px solid #ccc;"></td>
            <?php endif; ?>
            <?php if (!empty($fotosBase64['foto_gab_2'])): ?>
            <td style="text-align:center; padding:4px;"><img src="<?= $fotosBase64['foto_gab_2'] ?>" style="max-width:200px; max-height:120px; border:1px solid #ccc;"></td>
            <?php endif; ?>
        </tr>
    </table>
    <?php endif; ?>

    <!-- Observaciones gabinetes -->
    <?php if (!empty($inspeccion['observaciones_gabinetes'])): ?>
    <div class="section-title">OBSERVACIONES Y RECOMENDACIONES - GABINETES</div>
    <p class="content-text"><?= nl2br(esc($inspeccion['observaciones_gabinetes'])) ?></p>
    <?php endif; ?>

    <!-- GABINETES INDIVIDUALES -->
    <?php if (!empty($gabinetes)): ?>
<div class="section-title">DETALLE DE GABINETES INSPECCIONADOS (<?= count($gabinetes) ?>)</div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width:3%;">#</th>
                <th>Ubicacion</th>
                <th>Manguera</th>
                <th>Hacha</th>
                <th>Extintor</th>
                <th>Valvula</th>
                <th>Boquilla</th>
                <th>Llave</th>
                <th>Estado</th>
                <th>Senal.</th>
                <th style="width:8%;">Foto</th>
                <th style="width:12%;">Obs.</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($gabinetes as $i => $gab):
                $sinoClass = function($val) {
                    return $val === 'SI' ? 'val-si' : 'val-no';
                };
                $estadoClass = function($val) {
                    if ($val === 'BUENO') return 'val-bueno';
                    if ($val === 'REGULAR') return 'val-regular';
                    return 'val-malo';
                };
            ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td style="text-align:left; font-size:7px;"><?= esc($gab['ubicacion'] ?? '') ?></td>
                <td class="<?= $sinoClass($gab['tiene_manguera']) ?>"><?= esc($gab['tiene_manguera']) ?></td>
                <td class="<?= $sinoClass($gab['tiene_hacha']) ?>"><?= esc($gab['tiene_hacha']) ?></td>
                <td class="<?= $sinoClass($gab['tiene_extintor']) ?>"><?= esc($gab['tiene_extintor']) ?></td>
                <td class="<?= $sinoClass($gab['tiene_valvula']) ?>"><?= esc($gab['tiene_valvula']) ?></td>
                <td class="<?= $sinoClass($gab['tiene_boquilla']) ?>"><?= esc($gab['tiene_boquilla']) ?></td>
                <td class="<?= $sinoClass($gab['tiene_llave_spanner']) ?>"><?= esc($gab['tiene_llave_spanner']) ?></td>
                <td class="<?= $estadoClass($gab['estado']) ?>"><?= esc($gab['estado']) ?></td>
                <td class="<?= $estadoClass($gab['senalizacion']) ?>"><?= esc($gab['senalizacion']) ?></td>
                <td>
                    <?php if (!empty($gab['foto_base64'])): ?>
                        <img src="<?= $gab['foto_base64'] ?>" class="gab-img">
                    <?php else: ?>
                        <span style="color:#999; font-size:7px;">Sin foto</span>
                    <?php endif; ?>
                </td>
                <td style="text-align:left; font-size:7px;"><?= esc($gab['observaciones'] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- DETECTORES DE HUMO -->
    <div class="section-title">DETECTORES DE HUMO</div>
    <table class="info-table">
        <tr>
            <td class="info-label">Existen detectores de humo:</td>
            <td class="<?= ($inspeccion['tiene_detectores'] ?? 'SI') === 'SI' ? 'val-si' : 'val-no' ?>"><?= esc($inspeccion['tiene_detectores'] ?? 'SI') ?></td>
            <td class="info-label">Entregados por constructora:</td>
            <td class="<?= ($inspeccion['detectores_entregados'] ?? 'SI') === 'SI' ? 'val-si' : 'val-no' ?>"><?= esc($inspeccion['detectores_entregados'] ?? 'SI') ?></td>
        </tr>
        <tr>
            <td class="info-label">Cantidad de detectores:</td>
            <td style="font-weight:bold;"><?= $inspeccion['cantidad_detectores'] ?? 0 ?></td>
            <td class="info-label">&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <?php if (!empty($inspeccion['ubicacion_detectores'])): ?>
        <tr>
            <td class="info-label">Ubicacion de detectores:</td>
            <td colspan="3"><?= nl2br(esc($inspeccion['ubicacion_detectores'])) ?></td>
        </tr>
        <?php endif; ?>
    </table>

    <!-- Fotos detectores -->
    <?php if (!empty($fotosBase64['foto_det_1']) || !empty($fotosBase64['foto_det_2'])): ?>
    <table style="width:100%; margin-bottom:6px;">
        <tr>
            <?php if (!empty($fotosBase64['foto_det_1'])): ?>
            <td style="text-align:center; padding:4px;"><img src="<?= $fotosBase64['foto_det_1'] ?>" style="max-width:200px; max-height:120px; border:1px solid #ccc;"></td>
            <?php endif; ?>
            <?php if (!empty($fotosBase64['foto_det_2'])): ?>
            <td style="text-align:center; padding:4px;"><img src="<?= $fotosBase64['foto_det_2'] ?>" style="max-width:200px; max-height:120px; border:1px solid #ccc;"></td>
            <?php endif; ?>
        </tr>
    </table>
    <?php endif; ?>

    <!-- Observaciones detectores -->
    <?php if (!empty($inspeccion['observaciones_detectores'])): ?>
    <div class="section-title">OBSERVACIONES O RECOMENDACIONES - DETECTORES</div>
    <p class="content-text"><?= nl2br(esc($inspeccion['observaciones_detectores'])) ?></p>
    <?php endif; ?>

</body>
</html>
