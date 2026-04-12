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

        .main-title { text-align: center; font-size: 12px; font-weight: bold; margin: 8px 0 2px; color: #1c2437; }
        .main-subtitle { text-align: center; font-size: 8px; color: #555; margin-bottom: 6px; font-style: italic; }

        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; border: 1px solid #ccc; }
        .info-table td { padding: 3px 6px; font-size: 9px; border: 1px solid #ccc; }
        .info-label { font-weight: bold; color: #444; width: 130px; background: #f7f7f7; }

        .section-title { background: #1c2437; color: white; padding: 3px 8px; font-weight: bold; font-size: 9px; margin: 8px 0 4px; }

        .piscina-card { border: 1px solid #aaa; margin: 6px 0; page-break-inside: avoid; }
        .piscina-header { background: #e8e8e8; padding: 3px 6px; font-size: 9px; font-weight: bold; border-bottom: 1px solid #aaa; }
        .piscina-body { padding: 4px 6px; }

        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        .meta-table td { border: 1px solid #ccc; padding: 2px 4px; font-size: 8px; }
        .meta-label { background: #f7f7f7; font-weight: bold; width: 90px; }

        .zona { border: 1px solid #ccc; margin: 4px 0; padding: 3px 5px; }
        .zona-title { font-size: 8px; font-weight: bold; color: #1c2437; text-transform: uppercase; border-bottom: 1px solid #ddd; padding-bottom: 1px; margin-bottom: 2px; }
        .crit-table { width: 100%; border-collapse: collapse; }
        .crit-table td { padding: 1px 3px; font-size: 7px; border: none; }
        .crit-label { width: 60%; color: #555; }

        .val-si    { color: #155724; font-weight: bold; }
        .val-no    { color: #721c24; font-weight: bold; }
        .val-na    { color: #6c757d; }
        .val-bueno { color: #155724; font-weight: bold; }
        .val-regular { color: #856404; font-weight: bold; }
        .val-malo  { color: #721c24; font-weight: bold; }
        .val-critico { color: #fff; background: #721c24; padding: 1px 3px; }

        .ext-img { max-width: 90px; max-height: 70px; border: 1px solid #ccc; }
        .marco { background:#f7f7f7; border-left:3px solid #bd9751; padding:6px 8px; font-size:8px; line-height:1.4; margin:6px 0; text-align:justify; }
        .firma-table { width:100%; margin-top:18px; border-collapse:collapse; }
        .firma-table td { width:50%; text-align:center; padding:20px 6px 4px; border-top:1px solid #333; font-size:8px; }
    </style>
</head>
<body>

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
            <td class="header-code">Codigo: FT-SST-246<br>Version: 001</td>
        </tr>
        <tr>
            <td class="header-title" style="font-size:10px;">FORMATO DE INSPECCION DE PISCINAS</td>
            <td class="header-code">Fecha: <?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td>
        </tr>
    </table>

    <div class="main-title">INSPECCION DE PISCINAS — Conforme a Ley 1209 de 2008 y Decreto 554 de 2015</div>
    <div class="main-subtitle">Normas de seguridad y adecuacion de instalaciones en piscinas de uso colectivo</div>

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
            <td class="info-label">TOTAL PISCINAS:</td>
            <td><?= (int)($inspeccion['total_piscinas'] ?? 0) ?></td>
        </tr>
    </table>

    <div class="section-title">EMPRESA DE MANTENIMIENTO Y CERTIFICACION MUNICIPAL</div>
    <table class="info-table">
        <tr>
            <td class="info-label">EMPRESA MTTO:</td>
            <td><?= esc($inspeccion['empresa_mantenimiento'] ?? '-') ?></td>
            <td class="info-label">NIT:</td>
            <td><?= esc($inspeccion['nit_empresa_mantenimiento'] ?? '-') ?></td>
        </tr>
        <tr>
            <td class="info-label">CONTACTO:</td>
            <td colspan="3"><?= esc($inspeccion['contacto_empresa_mantenimiento'] ?? '-') ?></td>
        </tr>
        <tr>
            <td class="info-label">CERT. MPIO VIGENTE:</td>
            <td><?= esc($inspeccion['certificado_municipal_vigente'] ?? '-') ?></td>
            <td class="info-label">VENCIMIENTO:</td>
            <td><?= !empty($inspeccion['fecha_vencimiento_certificado_mpio']) ? date('d/m/Y', strtotime($inspeccion['fecha_vencimiento_certificado_mpio'])) : '-' ?></td>
        </tr>
    </table>

    <div class="section-title">DETALLE DE PISCINAS INSPECCIONADAS (<?= count($piscinas) ?>)</div>

    <?php
    $colorClass = function($val) {
        if ($val === 'SI') return 'val-si';
        if ($val === 'NO') return 'val-no';
        if ($val === 'NA') return 'val-na';
        if ($val === 'BUENO') return 'val-bueno';
        if ($val === 'REGULAR') return 'val-regular';
        if ($val === 'MALO') return 'val-malo';
        if ($val === 'CRITICO') return 'val-critico';
        return '';
    };
    ?>

    <?php if (!empty($piscinas)): ?>
        <?php foreach ($piscinas as $i => $p): ?>
        <div class="piscina-card">
            <div class="piscina-header">
                Piscina #<?= $i + 1 ?> — <?= esc($p['identificador'] ?? '') ?>
                <?php if (!empty($p['estado_general'])): ?>
                    &nbsp;|&nbsp; Estado general: <span class="<?= $colorClass($p['estado_general']) ?>"><?= esc($p['estado_general']) ?></span>
                <?php endif; ?>
            </div>
            <div class="piscina-body">
                <table class="meta-table">
                    <tr>
                        <td class="meta-label">Tipo</td><td><?= esc($p['tipo'] ?? '-') ?></td>
                        <td class="meta-label">Prof. min (m)</td><td><?= esc($p['profundidad_minima_m'] ?? '-') ?></td>
                        <td class="meta-label">Prof. max (m)</td><td><?= esc($p['profundidad_maxima_m'] ?? '-') ?></td>
                    </tr>
                </table>

                <?php foreach ($zonas as $zKey => $zCfg): ?>
                <div class="zona">
                    <div class="zona-title"><?= $zCfg['label'] ?></div>
                    <table class="crit-table">
                        <?php
                        $crits = array_keys($zCfg['criterios']);
                        $chunks = array_chunk($crits, 2);
                        foreach ($chunks as $pair):
                        ?>
                        <tr>
                        <?php foreach ($pair as $cKey):
                            $cCfg = $zCfg['criterios'][$cKey];
                            $val = $p[$cKey] ?? '';
                        ?>
                            <td class="crit-label"><?= $cCfg['label'] ?>:</td>
                            <td class="<?= $colorClass($val) ?>"><?= esc($val) ?></td>
                        <?php endforeach; ?>
                        <?php if (count($pair) === 1): ?>
                            <td></td><td></td>
                        <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endforeach; ?>

                <?php if (!empty($p['foto_base64'])): ?>
                <div style="margin-top:4px;">
                    <img src="<?= $p['foto_base64'] ?>" class="ext-img">
                </div>
                <?php endif; ?>

                <?php if (!empty($p['observaciones'])): ?>
                <div style="margin-top:3px; font-size:8px;"><strong>Observaciones:</strong> <?= esc($p['observaciones']) ?></div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="color:#888; font-style:italic; font-size:8px;">No se inspeccionaron piscinas.</p>
    <?php endif; ?>

    <?php if (!empty($inspeccion['recomendaciones_generales'])): ?>
    <div class="section-title">RECOMENDACIONES GENERALES</div>
    <p style="font-size:9px; line-height:1.4;"><?= nl2br(esc($inspeccion['recomendaciones_generales'])) ?></p>
    <?php endif; ?>

    <div class="section-title">MARCO NORMATIVO CONSULTADO</div>
    <div class="marco">
        <?= nl2br(esc($inspeccion['marco_normativo'] ?? $marcoNormativo ?? '')) ?>
        <br><br>
        <em>Aviso: La presente inspeccion documenta condiciones observadas en sitio bajo criterios SST. NO sustituye la certificacion de seguridad de la piscina expedida por la autoridad municipal o distrital competente (Ley 1209 de 2008 y Decreto 554 de 2015). Los hallazgos deben ser remitidos al administrador y a la empresa de mantenimiento para cierre de las acciones correctivas.</em>
    </div>

    <table class="firma-table">
        <tr>
            <td>Consultor SST<br><strong><?= esc($consultor['nombre_consultor'] ?? '') ?></strong></td>
            <td>Recibido por el cliente<br><strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong></td>
        </tr>
    </table>

</body>
</html>
