<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $codigoDocumento ?> Listado Maestro - <?= esc($cliente['nombre_cliente']) ?></title>
    <style>
        @page { size: letter landscape; margin: 30px 40px; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 8pt; line-height: 1.2; color: #333; }

        .encabezado-formal { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .encabezado-formal td { border: 1px solid #333; vertical-align: middle; }
        .encabezado-logo { width: 100px; padding: 6px; text-align: center; background: #fff; }
        .encabezado-logo img { max-width: 80px; max-height: 50px; }
        .encabezado-titulo { text-align: center; padding: 0; }
        .encabezado-titulo .sistema { font-size: 9pt; font-weight: bold; padding: 5px 8px; border-bottom: 1px solid #333; }
        .encabezado-titulo .nombre-doc { font-size: 9pt; font-weight: bold; padding: 5px 8px; }
        .encabezado-info { width: 130px; padding: 0; }
        .encabezado-info-table { width: 100%; border-collapse: collapse; }
        .encabezado-info-table td { border: none; border-bottom: 1px solid #333; padding: 2px 5px; font-size: 7pt; }
        .encabezado-info-table tr:last-child td { border-bottom: none; }
        .label { font-weight: bold; }

        .info-empresa { margin-bottom: 10px; padding: 8px 10px; background: #f8f9fa; border: 1px solid #dee2e6; }
        .info-empresa p { margin: 2px 0; font-size: 8pt; }

        .doc-table { width: 100%; border-collapse: collapse; }
        .doc-table th { background-color: #2c3e50; color: #ffffff; padding: 5px 4px; text-align: left; font-size: 7pt; white-space: nowrap; border: 1px solid #333; }
        .doc-table td { border: 1px solid #dee2e6; padding: 4px; font-size: 7pt; vertical-align: middle; }
        .doc-table tr:nth-child(even) { background-color: #f8f9fa; }

        .badge-vigente { background-color: #28a745; color: #fff; padding: 1px 5px; font-size: 7pt; }
        .badge-obsoleto { background-color: #dc3545; color: #fff; padding: 1px 5px; font-size: 7pt; }

        .footer { text-align: center; font-size: 7pt; color: #666; margin-top: 10px; border-top: 1px solid #ddd; padding-top: 5px; }
    </style>
</head>
<body>
    <!-- Encabezado Formal -->
    <table class="encabezado-formal" cellpadding="0" cellspacing="0">
        <tr>
            <td class="encabezado-logo" rowspan="2">
                <?php if (!empty($logoBase64)): ?>
                    <img src="<?= $logoBase64 ?>" style="max-width:80px; max-height:50px;">
                <?php else: ?>
                    <div style="font-size:7pt; font-weight:bold;"><?= esc($cliente['nombre_cliente']) ?></div>
                <?php endif; ?>
            </td>
            <td class="encabezado-titulo">
                <div class="sistema">SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO</div>
            </td>
            <td class="encabezado-info" rowspan="2">
                <table class="encabezado-info-table" cellpadding="0" cellspacing="0">
                    <tr><td class="label">Codigo:</td><td><?= $codigoDocumento ?></td></tr>
                    <tr><td class="label">Version:</td><td><?= $versionDocumento ?></td></tr>
                    <tr><td class="label">Fecha:</td><td><?= date('d/m/Y') ?></td></tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="encabezado-titulo">
                <div class="nombre-doc"><?= esc($tituloDocumento) ?></div>
            </td>
        </tr>
    </table>

    <!-- Info Empresa -->
    <div class="info-empresa">
        <p><strong>Empresa:</strong> <?= esc($cliente['nombre_cliente']) ?></p>
        <p><strong>NIT:</strong> <?= esc($cliente['nit_cliente'] ?? 'N/A') ?></p>
        <p><strong>Fecha de ingreso:</strong> <?= esc($cliente['fecha_ingreso'] ?? 'N/A') ?></p>
    </div>

    <!-- Tabla de Documentos -->
    <table class="doc-table">
        <thead>
            <tr>
                <th style="width:4%;">ID</th>
                <th style="width:13%;">TIPO DE DOCUMENTO</th>
                <th style="width:10%;">CODIGO</th>
                <th style="width:35%;">NOMBRE DEL DOCUMENTO</th>
                <th style="width:5%;">VER.</th>
                <th style="width:13%;">UBICACION</th>
                <th style="width:8%;">FECHA</th>
                <th style="width:7%;">ESTADO</th>
                <th style="width:5%;">CTRL</th>
            </tr>
        </thead>
        <tbody>
            <?php $idx = 1; foreach ($documentos as $doc): ?>
            <tr>
                <td style="text-align:center;"><?= $idx ?></td>
                <td><?= esc($doc['tipo_documento']) ?></td>
                <td><strong><?= esc($doc['codigo']) ?></strong></td>
                <td><?= esc($doc['nombre_documento']) ?></td>
                <td style="text-align:center;"><?= esc($doc['version']) ?></td>
                <td><?= esc($doc['ubicacion']) ?></td>
                <td style="text-align:center;"><?= $doc['fecha'] ? date('d/m/Y', strtotime($doc['fecha'])) : '' ?></td>
                <td style="text-align:center;">
                    <span class="<?= $doc['estado'] === 'Vigente' ? 'badge-vigente' : 'badge-obsoleto' ?>"><?= esc($doc['estado']) ?></span>
                </td>
                <td><?= esc($doc['control_cambios'] ?? '') ?></td>
            </tr>
            <?php $idx++; endforeach; ?>
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <?= $codigoDocumento ?> | <?= $tituloDocumento ?> | <?= esc($cliente['nombre_cliente']) ?> | Generado: <?= date('d/m/Y H:i') ?>
    </div>
</body>
</html>
