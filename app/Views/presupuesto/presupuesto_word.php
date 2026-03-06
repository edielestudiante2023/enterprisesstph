<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:w="urn:schemas-microsoft-com:office:word"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!--[if gte mso 9]>
    <xml>
        <w:WordDocument>
            <w:View>Print</w:View>
            <w:Zoom>100</w:Zoom>
            <w:DoNotOptimizeForBrowser/>
        </w:WordDocument>
    </xml>
    <![endif]-->
    <style>
        @page { size: Letter portrait; margin: 2cm 2cm 2cm 2cm; }
        body { font-family: Arial, sans-serif; font-size: 10pt; color: #333333; }
        table { border-collapse: collapse; width: 100%; }
        .header-table { margin-bottom: 20px; }
        .header-table td { border: 1px solid #000000; padding: 8px; vertical-align: middle; }
        .monto { text-align: right; font-family: 'Courier New', monospace; }
    </style>
</head>
<body>
    <!-- Encabezado Word -->
    <table width="100%" border="1" cellpadding="0" cellspacing="0" style="border-collapse:collapse; border:1px solid #333; margin-bottom:15px;">
        <tr>
            <td width="80" rowspan="2" align="center" valign="middle" bgcolor="#FFFFFF" style="border:1px solid #333; padding:5px;">
                <?php if (!empty($logoBase64)): ?>
                    <img src="<?= $logoBase64 ?>" width="70" height="45" alt="Logo">
                <?php else: ?>
                    <b style="font-size:8pt;"><?= esc($cliente['nombre_cliente']) ?></b>
                <?php endif; ?>
            </td>
            <td align="center" valign="middle" style="border:1px solid #333; padding:5px; font-size:9pt; font-weight:bold;">
                SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO
            </td>
            <td width="120" rowspan="2" valign="middle" style="border:1px solid #333; padding:0; font-size:8pt;">
                <table width="100%" cellpadding="2" cellspacing="0" style="border-collapse:collapse;">
                    <tr><td style="border-bottom:1px solid #333;"><b>Codigo:</b></td><td style="border-bottom:1px solid #333;"><?= $codigoDocumento ?? 'FT-SST-001' ?></td></tr>
                    <tr><td style="border-bottom:1px solid #333;"><b>Version:</b></td><td style="border-bottom:1px solid #333;"><?= $versionDocumento ?? '001' ?></td></tr>
                    <tr><td><b>Vigencia:</b></td><td><?= date('d/m/Y') ?></td></tr>
                </table>
            </td>
        </tr>
        <tr>
            <td align="center" valign="middle" style="border:1px solid #333; padding:5px; font-size:9pt; font-weight:bold;">
                <?= esc(strtoupper($tituloDocumento ?? 'ASIGNACION DE RECURSOS PARA EL SG-SST')) ?>
            </td>
        </tr>
    </table>

    <!-- Info empresa -->
    <table width="100%" cellpadding="8" cellspacing="0" style="margin-bottom:15px; background-color:#f8f9fa; border:1px solid #dee2e6;">
        <tr>
            <td style="font-size:10pt;">
                <p style="margin:4px 0;"><b>Empresa:</b> <?= esc($cliente['nombre_cliente']) ?></p>
                <p style="margin:4px 0;"><b>NIT:</b> <?= esc($cliente['nit_cliente'] ?? 'N/A') ?></p>
                <p style="margin:4px 0;"><b>Periodo:</b> Ano <?= $anio ?></p>
                <?php
                $estado = trim($presupuesto['estado'] ?? '');
                $estado = empty($estado) ? 'borrador' : $estado;
                $estadoTexto = match($estado) { 'aprobado' => 'APROBADO', 'cerrado' => 'CERRADO', default => 'BORRADOR' };
                ?>
                <p style="margin:4px 0;"><b>Estado:</b> <?= $estadoTexto ?></p>
            </td>
        </tr>
    </table>

    <!-- Tabla de Items -->
    <p style="background-color:#1a5f7a; color:#ffffff; padding:8px 12px; margin:15px 0 10px 0; font-weight:bold; font-size:11pt;">
        DETALLE DE ASIGNACION DE RECURSOS
    </p>

    <table width="100%" border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse; margin-bottom:15px;">
        <tr bgcolor="#2c3e50" style="background-color:#2c3e50;">
            <th width="8%" style="border:1px solid #333; color:#ffffff; text-align:left; font-size:9pt;">Item</th>
            <th width="52%" style="border:1px solid #333; color:#ffffff; text-align:left; font-size:9pt;">Actividad / Descripcion</th>
            <th width="20%" style="border:1px solid #333; color:#ffffff; text-align:right; font-size:9pt;">Presupuestado</th>
            <th width="20%" style="border:1px solid #333; color:#ffffff; text-align:right; font-size:9pt;">Ejecutado</th>
        </tr>
        <?php foreach ($itemsPorCategoria as $codigoCat => $categoria): ?>
            <tr bgcolor="#e9ecef" style="background-color:#e9ecef;">
                <td colspan="4" style="border:1px solid #dee2e6; font-weight:bold; color:#1a5f7a; font-size:9pt;"><?= $codigoCat ?>. <?= esc($categoria['nombre']) ?></td>
            </tr>
            <?php foreach ($categoria['items'] as $item): ?>
            <tr>
                <td style="border:1px solid #dee2e6; font-size:9pt;"><?= esc($item['codigo_item']) ?></td>
                <td style="border:1px solid #dee2e6; padding-left:15px; font-size:9pt;">
                    <?= esc($item['actividad']) ?>
                    <?php if (!empty($item['descripcion'])): ?>
                        <br><span style="color:#666; font-size:8pt;"><?= esc($item['descripcion']) ?></span>
                    <?php endif; ?>
                </td>
                <td style="border:1px solid #dee2e6; text-align:right; font-family:'Courier New',monospace; font-size:9pt;">$<?= number_format($item['total_presupuestado'], 0, ',', '.') ?></td>
                <td style="border:1px solid #dee2e6; text-align:right; font-family:'Courier New',monospace; font-size:9pt;">$<?= number_format($item['total_ejecutado'], 0, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
            <?php $totCat = $totales['por_categoria'][$codigoCat] ?? ['presupuestado' => 0, 'ejecutado' => 0]; ?>
            <tr bgcolor="#d4edda" style="background-color:#d4edda;">
                <td colspan="2" style="border:1px solid #dee2e6; text-align:right; font-weight:bold; font-size:9pt;">Subtotal <?= $codigoCat ?>:</td>
                <td style="border:1px solid #dee2e6; text-align:right; font-weight:bold; font-family:'Courier New',monospace; font-size:9pt;">$<?= number_format($totCat['presupuestado'], 0, ',', '.') ?></td>
                <td style="border:1px solid #dee2e6; text-align:right; font-weight:bold; font-family:'Courier New',monospace; font-size:9pt;">$<?= number_format($totCat['ejecutado'], 0, ',', '.') ?></td>
            </tr>
        <?php endforeach; ?>
        <tr bgcolor="#1a5f7a" style="background-color:#1a5f7a;">
            <td colspan="2" style="border:1px solid #333; text-align:right; color:#ffffff; font-weight:bold; font-size:10pt;">TOTAL GENERAL:</td>
            <td style="border:1px solid #333; text-align:right; color:#ffffff; font-weight:bold; font-family:'Courier New',monospace; font-size:10pt;">$<?= number_format($totales['general_presupuestado'], 0, ',', '.') ?></td>
            <td style="border:1px solid #333; text-align:right; color:#ffffff; font-weight:bold; font-family:'Courier New',monospace; font-size:10pt;">$<?= number_format($totales['general_ejecutado'], 0, ',', '.') ?></td>
        </tr>
    </table>

    <!-- Resumen -->
    <table width="100%" border="2" cellpadding="8" cellspacing="0" style="border-collapse:collapse; border:2px solid #1a5f7a; margin:15px 0;">
        <tr bgcolor="#1a5f7a" style="background-color:#1a5f7a;">
            <th colspan="2" style="color:#ffffff; text-align:left; font-size:10pt;">RESUMEN PRESUPUESTO <?= $anio ?></th>
        </tr>
        <?php foreach ($itemsPorCategoria as $codigoCat => $categoria):
            $totCat = $totales['por_categoria'][$codigoCat] ?? ['presupuestado' => 0];
        ?>
        <tr>
            <td width="70%" style="border-bottom:1px dotted #ccc; font-size:9pt;"><?= $codigoCat ?>. <?= esc($categoria['nombre']) ?></td>
            <td width="30%" style="border-bottom:1px dotted #ccc; text-align:right; font-weight:bold; font-family:'Courier New',monospace; font-size:9pt;">$<?= number_format($totCat['presupuestado'], 0, ',', '.') ?></td>
        </tr>
        <?php endforeach; ?>
        <tr style="border-top:2px solid #1a5f7a;">
            <td style="font-weight:bold; font-size:11pt;">TOTAL PRESUPUESTO APROBADO</td>
            <td style="text-align:right; font-weight:bold; color:#1a5f7a; font-family:'Courier New',monospace; font-size:11pt;">$<?= number_format($totales['general_presupuestado'], 0, ',', '.') ?></td>
        </tr>
    </table>

    <!-- Footer -->
    <p style="text-align:center; font-size:8pt; color:#666; margin-top:30px; border-top:1px solid #ddd; padding-top:10px;">
        <?= $codigoDocumento ?? 'FT-SST-001' ?> | <?= $tituloDocumento ?? 'Asignacion de Recursos para el SG-SST' ?> | <?= esc($cliente['nombre_cliente']) ?> | Ano <?= $anio ?>
    </p>
</body>
</html>
