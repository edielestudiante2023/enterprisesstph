<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $codigoDocumento ?? 'FT-SST-001' ?> - Presupuesto SST <?= $anio ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            .documento-contenido { padding: 20px !important; box-shadow: none !important; }
            body { background: white; font-size: 11pt; }
        }
        .documento-contenido { background: white; padding: 40px; max-width: 900px; margin: 0 auto; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .encabezado-formal { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .encabezado-formal td { border: 1px solid #333; vertical-align: middle; }
        .encabezado-logo { width: 150px; padding: 10px; text-align: center; background-color: #ffffff; }
        .encabezado-logo img { max-width: 130px; max-height: 70px; object-fit: contain; }
        .encabezado-titulo-central { text-align: center; padding: 0; }
        .encabezado-titulo-central .sistema { font-size: 0.85rem; font-weight: bold; color: #333; padding: 8px 15px; border-bottom: 1px solid #333; }
        .encabezado-titulo-central .nombre-doc { font-size: 0.85rem; font-weight: bold; color: #333; padding: 8px 15px; }
        .encabezado-info { width: 170px; padding: 0; }
        .encabezado-info-table { width: 100%; border-collapse: collapse; }
        .encabezado-info-table td { border: none; border-bottom: 1px solid #333; padding: 4px 8px; font-size: 0.75rem; }
        .encabezado-info-table tr:last-child td { border-bottom: none; }
        .encabezado-info-table .label { font-weight: bold; }
        .info-empresa { margin-bottom: 25px; padding: 15px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; }
        .info-empresa p { margin: 5px 0; font-size: 0.9rem; }
        .seccion-titulo { font-size: 1.1rem; font-weight: bold; color: #0d6efd; border-bottom: 2px solid #e9ecef; padding-bottom: 8px; margin-bottom: 15px; margin-top: 25px; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; font-size: 0.85rem; }
        .items-table th { background-color: #2c3e50; color: white; padding: 10px 8px; text-align: left; }
        .items-table th.monto { text-align: right; width: 120px; }
        .items-table td { border-bottom: 1px solid #dee2e6; padding: 8px; }
        .items-table .categoria { background-color: #e9ecef; font-weight: bold; color: #1a5f7a; }
        .items-table .item-row td { padding-left: 20px; }
        .items-table .subtotal { background-color: #d4edda; font-weight: bold; }
        .items-table .total { background-color: #1a5f7a; color: white; font-weight: bold; }
        .monto { text-align: right; font-family: 'Courier New', monospace; }
        .resumen-box { border: 2px solid #1a5f7a; padding: 20px; margin: 25px 0; background-color: #f8f9fa; }
        .resumen-box h3 { color: #1a5f7a; margin-bottom: 15px; font-size: 1.1rem; }
        .resumen-item { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px dotted #ccc; }
        .resumen-valor { font-family: 'Courier New', monospace; font-weight: bold; }
        .resumen-total { margin-top: 15px; padding-top: 15px; border-top: 2px solid #1a5f7a; border-bottom: none; font-size: 1.1rem; }
        .resumen-total .resumen-valor { color: #1a5f7a; }
        .panel-estado { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; padding: 20px; margin-bottom: 25px; color: white; }
        .estado-badge { display: inline-block; padding: 6px 14px; border-radius: 4px; font-size: 0.85rem; font-weight: bold; }
        .estado-aprobado { background-color: #28a745; color: white; }
        .estado-borrador { background-color: #6c757d; color: white; }
    </style>
</head>
<body class="bg-light">
    <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show m-3 no-print" role="alert">
        <i class="bi bi-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show m-3 no-print" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i><?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Barra de herramientas -->
    <div class="no-print bg-dark text-white py-2 sticky-top">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <a href="<?= base_url('presupuesto/' . $cliente['id_cliente'] . '/' . $anio) ?>" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>Volver a Edicion
                    </a>
                    <span class="ms-3 d-none d-md-inline"><?= esc($cliente['nombre_cliente']) ?> - Presupuesto <?= $anio ?></span>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="<?= base_url('presupuesto/pdf/' . $cliente['id_cliente'] . '/' . $anio) ?>" class="btn btn-danger btn-sm" target="_blank">
                        <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                    </a>
                    <a href="<?= base_url('presupuesto/word/' . $cliente['id_cliente'] . '/' . $anio) ?>" class="btn btn-primary btn-sm" target="_blank">
                        <i class="bi bi-file-earmark-word me-1"></i>Word
                    </a>
                    <a href="<?= base_url('presupuesto/excel/' . $cliente['id_cliente'] . '/' . $anio) ?>" class="btn btn-success btn-sm" target="_blank">
                        <i class="bi bi-file-earmark-excel me-1"></i>Excel
                    </a>
                    <button class="btn btn-outline-light btn-sm" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i>Imprimir
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="container my-4">
        <div class="documento-contenido">
            <!-- Panel de Estado -->
            <div class="panel-estado no-print">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <span class="badge bg-dark" style="font-size: 0.9rem;"><?= esc($codigoDocumento ?? 'FT-SST-001') ?></span>
                    <?php
                    $estadoPanel = $presupuesto['estado'] ?? 'borrador';
                    $estadoClass = match($estadoPanel) {
                        'aprobado', 'cerrado' => 'estado-aprobado',
                        default => 'estado-borrador'
                    };
                    $estadoTexto = match($estadoPanel) {
                        'aprobado' => 'Aprobado',
                        'cerrado' => 'Cerrado',
                        default => ucfirst($estadoPanel)
                    };
                    ?>
                    <span class="estado-badge <?= $estadoClass ?>"><?= $estadoTexto ?></span>
                </div>
                <small class="opacity-75">Total presupuestado: <strong>$<?= number_format($totales['general_presupuestado'] ?? 0, 0, ',', '.') ?></strong></small>
            </div>

            <!-- Encabezado formal -->
            <table class="encabezado-formal">
                <tr>
                    <td class="encabezado-logo" rowspan="2">
                        <?php if (!empty($cliente['logo'])): ?>
                            <img src="<?= base_url('uploads/' . $cliente['logo']) ?>" alt="Logo">
                        <?php else: ?>
                            <strong style="font-size: 0.7rem;"><?= esc($cliente['nombre_cliente']) ?></strong>
                        <?php endif; ?>
                    </td>
                    <td class="encabezado-titulo-central">
                        <div class="sistema">SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO</div>
                    </td>
                    <td class="encabezado-info" rowspan="2">
                        <table class="encabezado-info-table">
                            <tr><td class="label">Codigo:</td><td><?= $codigoDocumento ?? 'FT-SST-001' ?></td></tr>
                            <tr><td class="label">Version:</td><td><?= $versionDocumento ?? '001' ?></td></tr>
                            <tr><td class="label">Fecha:</td><td><?= date('d M Y') ?></td></tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="encabezado-titulo-central">
                        <div class="nombre-doc"><?= strtoupper($tituloDocumento ?? 'ASIGNACION DE RECURSOS PARA EL SG-SST') ?></div>
                    </td>
                </tr>
            </table>

            <div class="info-empresa">
                <p><strong>Empresa:</strong> <?= esc($cliente['nombre_cliente']) ?></p>
                <p><strong>NIT:</strong> <?= esc($cliente['nit_cliente'] ?? 'N/A') ?></p>
                <p><strong>Periodo:</strong> Ano <?= $anio ?></p>
            </div>

            <div class="seccion-titulo">DETALLE DE ASIGNACION DE RECURSOS</div>

            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 8%;">Item</th>
                        <th style="width: 52%;">Actividad / Descripcion</th>
                        <th class="monto">Presupuestado</th>
                        <th class="monto">Ejecutado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($itemsPorCategoria as $codigoCat => $categoria): ?>
                        <tr class="categoria"><td colspan="4"><?= $codigoCat ?>. <?= esc($categoria['nombre']) ?></td></tr>
                        <?php foreach ($categoria['items'] as $item): ?>
                        <tr class="item-row">
                            <td><?= esc($item['codigo_item']) ?></td>
                            <td><?= esc($item['actividad']) ?><?php if (!empty($item['descripcion'])): ?><br><small class="text-muted"><?= esc($item['descripcion']) ?></small><?php endif; ?></td>
                            <td class="monto">$<?= number_format($item['total_presupuestado'], 0, ',', '.') ?></td>
                            <td class="monto">$<?= number_format($item['total_ejecutado'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php $totCat = $totales['por_categoria'][$codigoCat] ?? ['presupuestado' => 0, 'ejecutado' => 0]; ?>
                        <tr class="subtotal">
                            <td colspan="2" style="text-align: right;">Subtotal <?= $codigoCat ?>:</td>
                            <td class="monto">$<?= number_format($totCat['presupuestado'], 0, ',', '.') ?></td>
                            <td class="monto">$<?= number_format($totCat['ejecutado'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="total">
                        <td colspan="2" style="text-align: right;">TOTAL GENERAL:</td>
                        <td class="monto">$<?= number_format($totales['general_presupuestado'] ?? 0, 0, ',', '.') ?></td>
                        <td class="monto">$<?= number_format($totales['general_ejecutado'] ?? 0, 0, ',', '.') ?></td>
                    </tr>
                </tbody>
            </table>

            <div class="resumen-box">
                <h3>RESUMEN PRESUPUESTO <?= $anio ?></h3>
                <?php foreach ($itemsPorCategoria as $codigoCat => $categoria): $totCat = $totales['por_categoria'][$codigoCat] ?? ['presupuestado' => 0]; ?>
                <div class="resumen-item">
                    <span><?= $codigoCat ?>. <?= esc($categoria['nombre']) ?></span>
                    <span class="resumen-valor">$<?= number_format($totCat['presupuestado'], 0, ',', '.') ?></span>
                </div>
                <?php endforeach; ?>
                <div class="resumen-item resumen-total">
                    <span>TOTAL PRESUPUESTO APROBADO</span>
                    <span class="resumen-valor">$<?= number_format($totales['general_presupuestado'] ?? 0, 0, ',', '.') ?></span>
                </div>
            </div>

            <!-- Firmas simples (nombres, sin firma digital) -->
            <div style="margin-top: 40px; page-break-inside: avoid;">
                <?php
                $consultorNombre = $consultor['nombre_consultor'] ?? '';
                $consultorCargo = $consultor['cargo_consultor'] ?? 'Consultor SST';
                $consultorLicencia = $consultor['numero_licencia'] ?? '';
                $repLegalNombre = $contexto['representante_legal_nombre'] ?? '';
                $repLegalCargo = $contexto['representante_legal_cargo'] ?? 'Representante Legal';
                $delegadoNombre = $contexto['responsable_sst_nombre'] ?? $contexto['delegado_sst_nombre'] ?? '';
                $delegadoCargo = $contexto['responsable_sst_cargo'] ?? $contexto['delegado_sst_cargo'] ?? 'Responsable SG-SST';
                ?>
                <div style="background: linear-gradient(90deg, #198754, #20c997); color: white; padding: 10px 15px; border-radius: 5px; margin-bottom: 0; font-weight: bold; font-size: 1rem;">
                    <i class="bi bi-pen me-2"></i>FIRMAS DE APROBACION
                </div>
                <table class="table table-bordered mb-0" style="font-size: 0.85rem;">
                    <tr>
                        <th style="width: 33.33%; background-color: #e9ecef; text-align: center;">Elaboro / Consultor SST</th>
                        <th style="width: 33.33%; background-color: #e9ecef; text-align: center;">Aprobo / Representante Legal</th>
                        <th style="width: 33.34%; background-color: #e9ecef; text-align: center;">Reviso / Responsable SG-SST</th>
                    </tr>
                    <tr>
                        <td style="vertical-align: top; padding: 25px; height: 150px;">
                            <div style="margin-bottom: 10px;"><strong>Nombre:</strong> <span style="border-bottom: 1px dotted #999; display: inline-block; min-width: 200px;"><?= esc($consultorNombre) ?></span></div>
                            <div style="margin-bottom: 10px;"><strong>Cargo:</strong> <span style="border-bottom: 1px dotted #999; display: inline-block; min-width: 200px;"><?= esc($consultorCargo) ?></span></div>
                            <?php if (!empty($consultorLicencia)): ?>
                            <div style="margin-bottom: 10px;"><strong>Licencia SST:</strong> <span style="border-bottom: 1px dotted #999; display: inline-block; min-width: 150px;"><?= esc($consultorLicencia) ?></span></div>
                            <?php endif; ?>
                            <div style="text-align: center; margin-top: 30px;">
                                <div style="border-top: 1px solid #333; width: 60%; margin: 0 auto; padding-top: 5px;"><small style="color: #666;">Firma</small></div>
                            </div>
                        </td>
                        <td style="vertical-align: top; padding: 25px; height: 150px;">
                            <div style="margin-bottom: 10px;"><strong>Nombre:</strong> <span style="border-bottom: 1px dotted #999; display: inline-block; min-width: 200px;"><?= esc($repLegalNombre) ?></span></div>
                            <div style="margin-bottom: 10px;"><strong>Cargo:</strong> <span style="border-bottom: 1px dotted #999; display: inline-block; min-width: 200px;"><?= esc($repLegalCargo) ?></span></div>
                            <div style="text-align: center; margin-top: 30px;">
                                <div style="border-top: 1px solid #333; width: 60%; margin: 0 auto; padding-top: 5px;"><small style="color: #666;">Firma</small></div>
                            </div>
                        </td>
                        <td style="vertical-align: top; padding: 25px; height: 150px;">
                            <div style="margin-bottom: 10px;"><strong>Nombre:</strong> <span style="border-bottom: 1px dotted #999; display: inline-block; min-width: 200px;"><?= esc($delegadoNombre) ?></span></div>
                            <div style="margin-bottom: 10px;"><strong>Cargo:</strong> <span style="border-bottom: 1px dotted #999; display: inline-block; min-width: 200px;"><?= esc($delegadoCargo) ?></span></div>
                            <div style="text-align: center; margin-top: 30px;">
                                <div style="border-top: 1px solid #333; width: 60%; margin: 0 auto; padding-top: 5px;"><small style="color: #666;">Firma</small></div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Pie de documento -->
            <div class="text-center text-muted mt-4 pt-3 border-top" style="font-size: 0.75rem;">
                <p class="mb-1">Documento generado el <?= date('d/m/Y') ?> - Sistema de Gestion SST</p>
                <p class="mb-0"><?= esc($cliente['nombre_cliente']) ?> - NIT: <?= esc($cliente['nit_cliente'] ?? '') ?></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
