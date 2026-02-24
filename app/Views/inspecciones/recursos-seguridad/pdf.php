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

        .recurso-card { border: 1px solid #ccc; margin-bottom: 6px; }
        .recurso-header { background: #e8e8e8; padding: 3px 8px; font-weight: bold; font-size: 9px; border-bottom: 1px solid #ccc; }
        .recurso-hint { font-weight: normal; font-size: 8px; color: #666; }
        .recurso-body { padding: 4px 8px; }
        .recurso-obs { font-size: 8px; line-height: 1.4; margin-bottom: 3px; }
        .recurso-foto { text-align: center; margin: 3px 0; }
        .recurso-foto img { max-width: 220px; max-height: 140px; border: 1px solid #ccc; }
        .recurso-empty { font-size: 8px; color: #999; font-style: italic; }

        .content-text { font-size: 9px; line-height: 1.4; margin-bottom: 5px; }
        .intro-text { font-size: 8px; line-height: 1.4; margin-bottom: 6px; text-align: justify; }

        .page-break { page-break-before: always; }
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
            <td class="header-code">Codigo: FT-SST-210<br>Version: 001</td>
        </tr>
        <tr>
            <td class="header-title" style="font-size:10px;">INSPECCION DE RECURSOS PARA LA SEGURIDAD</td>
            <td class="header-code">Fecha: <?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td>
        </tr>
    </table>

    <!-- TITULO -->
    <div class="main-title">INSPECCION DE RECURSOS PARA LA SEGURIDAD</div>
    <div class="main-subtitle">RECURSOS Y CARACTERISTICAS RELACIONADOS CON LA SEGURIDAD<br><?= esc($cliente['nombre_cliente'] ?? '') ?></div>

    <!-- INTRODUCCION -->
    <div class="section-title">INTRODUCCION</div>
    <p class="intro-text">
        Las propiedades horizontales, como condominios y conjuntos residenciales, albergan a una comunidad de personas que comparten espacios comunes y servicios. La seguridad y el bienestar de los residentes, visitantes y personal que habita estos espacios deben ser una prioridad absoluta. La minimizacion de riesgos estructurales, arquitectonicos y administrativos es un aspecto fundamental para garantizar una convivencia armoniosa y segura.
    </p>
    <p class="intro-text">
        Un especialista en Seguridad y Salud en el Trabajo (SST) posee la formacion, experiencia y herramientas necesarias para asesorar a la administracion y copropietarios en la identificacion, evaluacion y control de los riesgos. Sus funciones incluyen: inspecciones estructurales y arquitectonicas, evaluacion de riesgos administrativos, elaboracion e implementacion de un plan de gestion de riesgos, y monitoreo continuo del plan.
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

    <!-- RECURSOS INSPECCIONADOS -->
    <div class="section-title">RECURSOS DE SEGURIDAD INSPECCIONADOS</div>

    <?php $num = 1; foreach ($recursos as $key => $info):
        $obs = $inspeccion['obs_' . $key] ?? '';
        $fotoB64 = (!empty($info['tiene_foto']) && isset($fotosBase64['foto_' . $key])) ? $fotosBase64['foto_' . $key] : '';
    ?>
    <div class="recurso-card">
        <div class="recurso-header">
            <?= $num++ ?>. <?= $info['label'] ?>
            <?php if (!empty($info['hint'])): ?>
                <span class="recurso-hint"> - <?= $info['hint'] ?></span>
            <?php endif; ?>
        </div>
        <div class="recurso-body">
            <?php if (!empty($obs)): ?>
                <div class="recurso-obs"><?= nl2br(esc($obs)) ?></div>
            <?php endif; ?>
            <?php if (!empty($fotoB64)): ?>
                <div class="recurso-foto">
                    <img src="<?= $fotoB64 ?>">
                </div>
            <?php endif; ?>
            <?php if (empty($obs) && empty($fotoB64)): ?>
                <div class="recurso-empty">Sin informacion registrada</div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- OBSERVACIONES GENERALES -->
    <?php if (!empty($inspeccion['observaciones'])): ?>
    <div class="section-title">OBSERVACIONES GENERALES</div>
    <p class="content-text"><?= nl2br(esc($inspeccion['observaciones'])) ?></p>
    <?php endif; ?>

</body>
</html>
