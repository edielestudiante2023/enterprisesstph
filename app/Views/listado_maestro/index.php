<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FT-SST-020 Listado Maestro - <?= esc($cliente['nombre_cliente']) ?></title>
    <link rel="icon" href="<?= base_url('favicon.ico') ?>" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f5f7fa; font-family: 'Segoe UI', Arial, sans-serif; }
        .toolbar { background: white; border-bottom: 2px solid #1a5f7a; padding: 10px 20px; position: sticky; top: 0; z-index: 100; }
        .encabezado-formal { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .encabezado-formal td { border: 1px solid #333; vertical-align: middle; }
        .encabezado-logo { width: 120px; padding: 8px; text-align: center; background: #fff; }
        .encabezado-logo img { max-width: 100px; max-height: 60px; }
        .encabezado-titulo { text-align: center; padding: 0; }
        .encabezado-titulo .sistema { font-size: 10pt; font-weight: bold; padding: 6px 10px; border-bottom: 1px solid #333; }
        .encabezado-titulo .nombre-doc { font-size: 10pt; font-weight: bold; padding: 6px 10px; }
        .encabezado-info { width: 160px; padding: 0; }
        .encabezado-info-table { width: 100%; border-collapse: collapse; }
        .encabezado-info-table td { border: none; border-bottom: 1px solid #333; padding: 3px 6px; font-size: 8pt; }
        .encabezado-info-table tr:last-child td { border-bottom: none; }
        .encabezado-info-table .label { font-weight: bold; }
        .info-empresa { margin-bottom: 15px; padding: 10px 15px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; }
        .info-empresa p { margin: 3px 0; font-size: 10pt; }
        .doc-table { width: 100%; border-collapse: collapse; font-size: 9pt; }
        .doc-table th { background: #2c3e50; color: white; padding: 8px 6px; text-align: left; font-size: 9pt; white-space: nowrap; }
        .doc-table td { border: 1px solid #dee2e6; padding: 6px; vertical-align: middle; }
        .doc-table tbody tr:hover { background: #e8f4f8; }
        .badge-vigente { background: #28a745; color: white; padding: 3px 8px; border-radius: 4px; font-size: 8pt; }
        .badge-obsoleto { background: #dc3545; color: white; padding: 3px 8px; border-radius: 4px; font-size: 8pt; }
        .badge-revision { background: #ffc107; color: #333; padding: 3px 8px; border-radius: 4px; font-size: 8pt; }
        .footer-doc { text-align: center; font-size: 8pt; color: #666; margin-top: 20px; border-top: 1px solid #ddd; padding-top: 10px; }
        @media print {
            .toolbar { display: none !important; }
            body { padding: 0; background: white; }
        }
    </style>
</head>
<body>
    <!-- Toolbar -->
    <div class="toolbar d-flex justify-content-between align-items-center no-print">
        <div>
            <a href="<?= base_url('listado-maestro') ?>" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('listado-maestro/pdf/' . $cliente['id_cliente']) ?>" class="btn btn-danger btn-sm" target="_blank">
                <i class="fas fa-file-pdf me-1"></i> PDF
            </a>
            <a href="<?= base_url('listado-maestro/excel/' . $cliente['id_cliente']) ?>" class="btn btn-success btn-sm">
                <i class="fas fa-file-excel me-1"></i> Excel
            </a>
            <a href="<?= base_url('listado-maestro/matrices/' . $cliente['id_cliente']) ?>" class="btn btn-warning btn-sm">
                <i class="fas fa-table me-1"></i> Matrices
            </a>
            <button onclick="window.print()" class="btn btn-secondary btn-sm">
                <i class="fas fa-print me-1"></i> Imprimir
            </button>
        </div>
    </div>

    <div class="container-fluid" style="max-width: 1100px; padding: 20px;">
        <!-- Encabezado Formal -->
        <table class="encabezado-formal" cellpadding="0" cellspacing="0">
            <tr>
                <td class="encabezado-logo" rowspan="2">
                    <?php if (!empty($cliente['logo'])): ?>
                        <img src="<?= base_url('uploads/' . $cliente['logo']) ?>" alt="Logo">
                    <?php else: ?>
                        <div style="font-size:8pt; font-weight:bold;"><?= esc($cliente['nombre_cliente']) ?></div>
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
                    <th style="width:5%;">ID</th>
                    <th style="width:14%;">TIPO DE DOCUMENTO</th>
                    <th style="width:10%;">CODIGO</th>
                    <th style="width:33%;">NOMBRE DEL DOCUMENTO</th>
                    <th style="width:6%;">VERSION</th>
                    <th style="width:14%;">UBICACION</th>
                    <th style="width:8%;">FECHA</th>
                    <th style="width:7%;">ESTADO</th>
                    <th style="width:3%;">CTRL</th>
                </tr>
            </thead>
            <tbody>
                <?php $idx = 1; foreach ($documentos as $doc): ?>
                <tr>
                    <td class="text-center"><?= $idx ?></td>
                    <td><?= esc($doc['tipo_documento']) ?></td>
                    <td><strong><?= esc($doc['codigo']) ?></strong></td>
                    <td><?= esc($doc['nombre_documento']) ?></td>
                    <td class="text-center"><?= esc($doc['version']) ?></td>
                    <td><?= esc($doc['ubicacion']) ?></td>
                    <td class="text-center"><?= $doc['fecha'] ? date('d/m/Y', strtotime($doc['fecha'])) : '' ?></td>
                    <td class="text-center">
                        <?php
                        $badgeClass = match($doc['estado']) {
                            'Vigente' => 'badge-vigente',
                            'Obsoleto' => 'badge-obsoleto',
                            'En revision' => 'badge-revision',
                            default => 'badge-vigente'
                        };
                        ?>
                        <span class="<?= $badgeClass ?>"><?= esc($doc['estado']) ?></span>
                    </td>
                    <td><?= esc($doc['control_cambios'] ?? '') ?></td>
                </tr>
                <?php $idx++; endforeach; ?>
            </tbody>
        </table>

        <!-- Footer -->
        <div class="footer-doc">
            <?= $codigoDocumento ?> | <?= $tituloDocumento ?> | <?= esc($cliente['nombre_cliente']) ?> | Generado: <?= date('d/m/Y H:i') ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
