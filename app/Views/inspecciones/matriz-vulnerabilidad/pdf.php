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

        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .data-table th { background: #e8e8e8; border: 1px solid #aaa; padding: 4px 4px; font-size: 7.5px; text-align: center; }
        .data-table td { border: 1px solid #ccc; padding: 3px 4px; font-size: 7.5px; vertical-align: middle; }

        .opt-a { background: #d4edda; color: #155724; font-weight: bold; text-align: center; }
        .opt-b { background: #fff3cd; color: #856404; font-weight: bold; text-align: center; }
        .opt-c { background: #f8d7da; color: #721c24; font-weight: bold; text-align: center; }

        .intro-text { font-size: 8px; line-height: 1.4; margin-bottom: 6px; text-align: justify; }

        .result-table { width: 80%; border-collapse: collapse; margin: 6px auto; }
        .result-table td { border: 1px solid #ccc; padding: 4px 8px; font-size: 8px; }
        .result-table .result-label { font-weight: bold; width: 30%; }

        .range-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .range-table td { border: 1px solid #ccc; padding: 3px 6px; font-size: 8px; }
        .range-table .range-val { font-weight: bold; text-align: center; width: 60px; }

        .content-text { font-size: 9px; line-height: 1.4; margin-bottom: 5px; }
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
            <td class="header-code">Codigo: FT-SST-207<br>Version: 001</td>
        </tr>
        <tr>
            <td class="header-title" style="font-size:10px;">FORMATO MATRIZ DE ANALISIS DE VULNERABILIDAD POR AMENAZA</td>
            <td class="header-code">Fecha: <?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td>
        </tr>
    </table>

    <!-- TITULO -->
    <div class="main-title">MATRIZ DE ANALISIS DE VULNERABILIDAD POR AMENAZA</div>
    <div class="main-subtitle"><?= esc($cliente['nombre_cliente'] ?? '') ?></div>

    <!-- INTRODUCCION -->
    <div class="section-title">VULNERABILIDAD POR AMENAZA</div>
    <p class="intro-text">
        La matriz de vulnerabilidad por amenaza es una herramienta fundamental para evaluar la seguridad estructural de una copropiedad frente a diferentes tipos de amenazas, como sismos, inundaciones, incendios y vientos fuertes. Esta matriz permite identificar las debilidades de la estructura y tomar las medidas correctivas necesarias para minimizar el riesgo de colapso u otros danos durante una emergencia.
    </p>
    <p class="intro-text">
        <strong>Puntaje:</strong> Cada criterio se evalua con tres opciones: A (1.0 puntos), B (0.5 puntos) y C (0.0 puntos). El puntaje total se calcula multiplicando la suma por 4, obteniendo un valor de 0 a 100.
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

    <!-- TABLA DE EVALUACION -->
    <div class="section-title">EVALUACION DE CRITERIOS</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:5%;">N</th>
                <th style="width:55%;">ITEM / CRITERIO</th>
                <th style="width:25%;">CALIFICACION</th>
                <th style="width:15%;">PUNTAJE</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $puntajes = ['a' => 1.0, 'b' => 0.5, 'c' => 0.0];
            $opClasses = ['a' => 'opt-a', 'b' => 'opt-b', 'c' => 'opt-c'];
            $opLetters = ['a' => 'A', 'b' => 'B', 'c' => 'C'];
            foreach ($criterios as $key => $criterio):
                $val = $inspeccion[$key] ?? null;
                $ptje = $val ? ($puntajes[$val] ?? 0) : 0;
                $cellClass = $val ? ($opClasses[$val] ?? '') : '';
                $letter = $val ? ($opLetters[$val] ?? '-') : '-';
            ?>
            <tr>
                <td style="text-align:center; font-weight:bold;"><?= $criterio['numero'] ?></td>
                <td><?= $criterio['titulo'] ?></td>
                <td class="<?= $cellClass ?>"><?= $letter ?></td>
                <td style="text-align:center; font-weight:bold;"><?= number_format($ptje, 1) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- RESULTADO CONSOLIDADO -->
<div class="section-title">RESULTADO DE LA EVALUACION</div>
    <table class="result-table">
        <tr>
            <td class="result-label">PUNTAJE TOTAL:</td>
            <td style="text-align:center; font-weight:bold; font-size:14px;"><?= number_format($puntaje, 1) ?> / 100</td>
        </tr>
        <tr>
            <td class="result-label">CLASIFICACION:</td>
            <td style="text-align:center; font-weight:bold; font-size:10px; background:<?= $clasificacion['color'] ?>; color:<?= $clasificacion['text_color'] ?>;">
                <?= strtoupper($clasificacion['label']) ?>
            </td>
        </tr>
    </table>
    <p class="intro-text" style="margin-top:6px;"><?= $clasificacion['desc'] ?></p>

    <!-- TABLA DE RANGOS -->
    <div class="section-title">TABLA DE COMPARACION DEL NIVEL DE VULNERABILIDAD</div>
    <table class="range-table">
        <tr>
            <td class="range-val" style="background:#f8d7da; color:#721c24;">0 - 50</td>
            <td>Alta vulnerabilidad funcional. Se deben revisar todos los aspectos que puedan estar representando riesgo para las personas que permanecen en la copropiedad en un momento de emergencia.</td>
        </tr>
        <tr>
            <td class="range-val" style="background:#fff3cd; color:#856404;">51 - 70</td>
            <td>Vulnerabilidad media-alta y plan para emergencia incompleto, que solo podria ser activado parcialmente en caso de emergencia.</td>
        </tr>
        <tr>
            <td class="range-val" style="background:#cce5ff; color:#004085;">71 - 90</td>
            <td>Baja vulnerabilidad y plan para emergencia apenas funcional que debe optimizarse.</td>
        </tr>
        <tr>
            <td class="range-val" style="background:#d4edda; color:#155724;">91 - 100</td>
            <td>Vulnerabilidad minima y plan en estado optimo de aplicacion.</td>
        </tr>
    </table>

    <!-- DETALLE DE OPCIONES POR CRITERIO -->
    <div class="section-title">DETALLE DE CRITERIOS EVALUADOS</div>
    <?php foreach ($criterios as $key => $criterio):
        $val = $inspeccion[$key] ?? null;
    ?>
    <table class="data-table" style="margin-bottom:4px;">
        <tr>
            <td style="width:5%; text-align:center; font-weight:bold; background:#f0f0f0;"><?= $criterio['numero'] ?></td>
            <td style="font-weight:bold; background:#f0f0f0;"><?= strtoupper($criterio['titulo']) ?></td>
            <td style="width:10%; text-align:center; font-weight:bold; <?= $val ? 'background:' . ($val === 'a' ? '#d4edda' : ($val === 'b' ? '#fff3cd' : '#f8d7da')) : '' ?>">
                <?= $val ? strtoupper($val) : '-' ?>
            </td>
        </tr>
        <?php foreach ($criterio['opciones'] as $opKey => $opText):
            $isSelected = ($val === $opKey);
        ?>
        <tr>
            <td style="text-align:center; <?= $isSelected ? 'font-weight:bold;' : 'color:#999;' ?>"><?= strtoupper($opKey) ?></td>
            <td colspan="2" style="<?= $isSelected ? 'font-weight:bold;' : 'color:#888;' ?>"><?= $opText ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endforeach; ?>

    <!-- OBSERVACIONES DEL CONSULTOR -->
    <?php if (!empty($inspeccion['observaciones'])): ?>
    <div class="section-title">OBSERVACIONES DEL CONSULTOR</div>
    <p class="content-text"><?= nl2br(esc($inspeccion['observaciones'])) ?></p>
    <?php endif; ?>

</body>
</html>
