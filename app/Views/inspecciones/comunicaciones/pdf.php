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
        .data-table th { background: #e8e8e8; border: 1px solid #aaa; padding: 4px 6px; font-size: 8px; text-align: center; }
        .data-table td { border: 1px solid #ccc; padding: 3px 6px; font-size: 8px; vertical-align: middle; }

        .content-text { font-size: 9px; line-height: 1.4; margin-bottom: 5px; }
        .intro-text { font-size: 8px; line-height: 1.4; margin-bottom: 6px; text-align: justify; }
        .intro-subtitle { font-weight: bold; font-size: 8px; margin: 4px 0 2px; }

        .cant-cero { color: #721c24; font-weight: bold; }
        .cant-positivo { color: #155724; font-weight: bold; }

        .photo-row { margin: 4px 0; }
        .photo-row img { max-width: 200px; max-height: 120px; border: 1px solid #ccc; }
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
            <td class="header-code">Codigo: FT-SST-204<br>Version: 001</td>
        </tr>
        <tr>
            <td class="header-title" style="font-size:10px;">INSPECCION DE EQUIPOS DE COMUNICACION EN CASO DE EMERGENCIAS</td>
            <td class="header-code">Fecha: <?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td>
        </tr>
    </table>

    <!-- TITULO -->
    <div class="main-title">EQUIPOS DE COMUNICACION EN CASO DE EMERGENCIAS<br><?= esc($cliente['nombre_cliente'] ?? '') ?></div>

    <!-- INTRODUCCION -->
    <div class="section-title">INTRODUCCION</div>
    <p class="intro-text">
        Las emergencias en propiedades horizontales pueden surgir de diversas situaciones, como incendios, terremotos, inundaciones o intrusiones. En estos momentos criticos, la comunicacion efectiva es crucial para salvar vidas, minimizar danos y coordinar la respuesta de emergencia. Los equipos de comunicacion juegan un papel fundamental en este proceso, brindando a los residentes, administradores y personal de seguridad la capacidad de comunicarse de manera clara, oportuna y confiable.
    </p>

    <p class="intro-subtitle">1. Importancia de los Equipos de Comunicacion:</p>
    <p class="intro-text">
        Facilitar la comunicacion durante emergencias permitiendo que los residentes reciban informacion esencial sobre la situacion, las instrucciones de evacuacion y la asistencia disponible. Coordinar la respuesta de emergencia entre residentes, personal de seguridad, autoridades y proveedores de servicios. La comunicacion oportuna puede ayudar a guiar a los residentes hacia la seguridad, evitar riesgos y solicitar ayuda medica de manera rapida.
    </p>

    <p class="intro-subtitle">2. Tipos de Equipos de Comunicacion:</p>
    <p class="intro-text">
        Sistemas de telefonia interna, sistemas de altavoces para difusion de anuncios, alarmas contra incendios, sistemas de iluminacion de emergencia, radios de dos vias, y aplicaciones moviles para enviar alertas y facilitar la comunicacion durante emergencias.
    </p>

    <p class="intro-subtitle">3. Seleccion e Implementacion:</p>
    <p class="intro-text">
        Considerar el tamano y la distribucion de la propiedad, identificar las necesidades especificas de la comunidad, establecer un presupuesto adecuado, consultar con expertos en seguridad, instalar los equipos de manera profesional, capacitar a los residentes y al personal, realizar pruebas y simulacros periodicos, y mantener los equipos actualizados.
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

    <!-- TABLA DE EQUIPOS DE COMUNICACION -->
    <div class="section-title">EQUIPOS DE COMUNICACION INSPECCIONADOS</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:5%;">#</th>
                <th style="width:30%;">EQUIPO DE COMUNICACION</th>
                <th style="width:10%;">CANTIDAD</th>
                <th style="width:55%;">INSPECCION DEL CONSULTOR</th>
            </tr>
        </thead>
        <tbody>
            <?php $num = 1; foreach ($equipos as $key => $info):
                $cant = (int)($inspeccion['cant_' . $key] ?? 0);
                $obs = $inspeccion['obs_' . $key] ?? '';
                $cantClass = $cant > 0 ? 'cant-positivo' : 'cant-cero';
            ?>
            <tr>
                <td style="text-align:center;"><?= $num++ ?></td>
                <td style="font-weight:bold;"><?= $info['label'] ?></td>
                <td style="text-align:center;" class="<?= $cantClass ?>"><?= $cant ?></td>
                <td style="text-align:left;"><?= nl2br(esc($obs)) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- FOTOS EVIDENCIA -->
    <?php if (!empty($fotosBase64['foto_1']) || !empty($fotosBase64['foto_2'])): ?>
    <div class="section-title">EVIDENCIA FOTOGRAFICA</div>
    <table style="width:100%; margin-bottom:6px;">
        <tr>
            <?php if (!empty($fotosBase64['foto_1'])): ?>
            <td style="text-align:center; padding:4px;"><img src="<?= $fotosBase64['foto_1'] ?>" style="max-width:200px; max-height:120px; border:1px solid #ccc;"></td>
            <?php endif; ?>
            <?php if (!empty($fotosBase64['foto_2'])): ?>
            <td style="text-align:center; padding:4px;"><img src="<?= $fotosBase64['foto_2'] ?>" style="max-width:200px; max-height:120px; border:1px solid #ccc;"></td>
            <?php endif; ?>
        </tr>
    </table>
    <?php endif; ?>

    <!-- OBSERVACIONES FINALES -->
    <?php if (!empty($inspeccion['observaciones_finales'])): ?>
    <div class="section-title">OBSERVACIONES FINALES</div>
    <p class="content-text"><?= nl2br(esc($inspeccion['observaciones_finales'])) ?></p>
    <?php endif; ?>

</body>
</html>
