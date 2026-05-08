<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 100px 70px 80px 90px; }
        body { margin: 0; padding: 0; font-family: DejaVu Sans, Arial, sans-serif; font-size: 10pt; line-height: 1.15; color: #333; }
        p, h1, h2, h3, h4, h5, h6, table, div { margin: 0; padding: 0; }
        *, *::before, *::after { box-sizing: border-box; }

        .seccion { margin-bottom: 8px; }
        .seccion-titulo { font-size: 11pt; font-weight: bold; color: #0d6efd; border-bottom: 1px solid #e9ecef; padding-bottom: 3px; margin-bottom: 5px; margin-top: 8px; }
        .seccion-contenido { text-align: justify; line-height: 1.2; }
        .seccion-contenido p { margin: 3px 0; }

        table.tabla-contenido { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 9pt; }
        table.tabla-contenido th, table.tabla-contenido td { border: 1px solid #999; padding: 5px 8px; vertical-align: top; }
        table.tabla-contenido th { background-color: #0d6efd; color: white; font-weight: bold; text-align: center; }

        table.datos-general { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 9pt; }
        table.datos-general td { border: 1px solid #999; padding: 5px 8px; }
        .datos-label { font-weight: bold; width: 22%; background:#f8f9fa; }

        .firma-img { max-width: 180px; max-height: 80px; border-bottom: 1px solid #999; filter: contrast(1.6) brightness(0.6); }
        .empty-text { color: #888; font-style: italic; font-size: 9pt; }
        .pendiente-text { color:#d97706; font-style:italic; font-size:9pt; }

        .pie-documento { margin-top: 15px; padding-top: 8px; border-top: 1px solid #ccc; text-align: center; font-size: 8pt; color: #666; }
    </style>
</head>
<body>

    <!-- ENCABEZADO -->
    <table style="width:100%; border-collapse:collapse; margin-bottom:20px;" cellpadding="0" cellspacing="0">
        <tr>
            <td rowspan="2" style="width:100px; border:1px solid #333; padding:8px; text-align:center; vertical-align:middle; background:#fff;">
                <?php if (!empty($logoBase64)): ?>
                    <img src="<?= $logoBase64 ?>" style="max-width:80px; max-height:50px;">
                <?php else: ?>
                    <div style="font-size:8pt; font-weight:bold;"><?= esc($cliente['nombre_cliente'] ?? '') ?></div>
                <?php endif; ?>
            </td>
            <td style="border:1px solid #333; text-align:center; padding:6px 10px; vertical-align:middle;">
                <div style="font-size:10pt; font-weight:bold; color:#333;">
                    SISTEMA DE GESTIÓN DE SEGURIDAD Y SALUD EN EL TRABAJO
                </div>
            </td>
            <td rowspan="2" style="width:130px; border:1px solid #333; padding:0; vertical-align:middle;">
                <table style="width:100%; border-collapse:collapse;" cellpadding="0" cellspacing="0">
                    <tr><td style="border-bottom:1px solid #333; padding:3px 6px; font-size:8pt;"><span style="font-weight:bold;">Código:</span> FT-SST-252</td></tr>
                    <tr><td style="border-bottom:1px solid #333; padding:3px 6px; font-size:8pt;"><span style="font-weight:bold;">Versión:</span> 001</td></tr>
                    <tr><td style="padding:3px 6px; font-size:8pt;"><span style="font-weight:bold;">Vigencia:</span> <?= !empty($vigenciaContrato) ? date('d/m/Y', strtotime($vigenciaContrato)) : date('d/m/Y', strtotime($acta['fecha_capacitacion'])) ?></td></tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="border:1px solid #333; text-align:center; padding:6px 10px; vertical-align:middle;">
                <div style="font-size:10pt; font-weight:bold; color:#333;">
                    REPORTE DE CAPACITACIÓN
                </div>
            </td>
        </tr>
    </table>

    <?php
    // Si hay contexto de cronograma específico (modo nuevo), el TEMA viene del cronograma.
    // Si no (modo legacy), usa el campo libre del acta.
    $temaPDF = !empty($cronogramaCtx['nombre_capacitacion'])
        ? $cronogramaCtx['nombre_capacitacion']
        : ($acta['tema'] ?? '');
    // Objetivos: si hay contexto, usa el generado por IA; si no, los del acta.
    $objetivosPDF = !empty($cronogramaCtx['objetivo_ia'])
        ? $cronogramaCtx['objetivo_ia']
        : ($acta['objetivos'] ?? '');
    ?>

    <!-- DATOS GENERALES -->
    <table class="datos-general">
        <tr>
            <td class="datos-label">CLIENTE:</td>
            <td colspan="3"><?= esc($cliente['nombre_cliente'] ?? '') ?></td>
        </tr>
        <tr>
            <td class="datos-label">CAPACITACIÓN:</td>
            <td colspan="3"><strong><?= esc($temaPDF) ?></strong></td>
        </tr>
        <tr>
            <td class="datos-label">FECHA:</td>
            <td><?= date('d/m/Y', strtotime($acta['fecha_capacitacion'])) ?></td>
            <td class="datos-label">MODALIDAD:</td>
            <td><?= ucfirst($acta['modalidad']) ?></td>
        </tr>
        <?php if (!empty($acta['hora_inicio']) || !empty($acta['hora_fin'])): ?>
        <tr>
            <td class="datos-label">HORA INICIO:</td>
            <td><?= !empty($acta['hora_inicio']) ? date('g:i A', strtotime($acta['hora_inicio'])) : '-' ?></td>
            <td class="datos-label">HORA FIN:</td>
            <td><?= !empty($acta['hora_fin']) ? date('g:i A', strtotime($acta['hora_fin'])) : '-' ?></td>
        </tr>
        <?php endif; ?>
        <tr>
            <td class="datos-label">DICTADA POR:</td>
            <td><?= esc($acta['dictada_por']) ?></td>
            <td class="datos-label">ENTIDAD:</td>
            <td><?= esc($acta['entidad_capacitadora'] ?? '-') ?></td>
        </tr>
        <?php if (!empty($acta['nombre_capacitador'])): ?>
        <tr>
            <td class="datos-label">CAPACITADOR:</td>
            <td colspan="3"><?= esc($acta['nombre_capacitador']) ?></td>
        </tr>
        <?php endif; ?>
        <tr>
            <td class="datos-label"><?= !empty($realizadoPor) ? 'REGISTRADA POR:' : 'CONSULTOR:' ?></td>
            <td colspan="3">
                <?php if (!empty($realizadoPor)): ?>
                    <?= esc($realizadoPor) ?> (Comité)
                <?php elseif (!empty($consultor)): ?>
                    <?= esc($consultor['nombre_consultor'] ?? '') ?>
                <?php else: ?>-<?php endif; ?>
            </td>
        </tr>
    </table>

    <!-- OBJETIVOS (generados por IA según la capacitación específica) -->
    <?php if (!empty($objetivosPDF)): ?>
    <div class="seccion">
        <div class="seccion-titulo">OBJETIVOS</div>
        <div class="seccion-contenido"><p><?= nl2br(esc($objetivosPDF)) ?></p></div>
    </div>
    <?php endif; ?>

    <!-- RESULTADOS DE LA EVALUACIÓN (matcheada con IA por tema) -->
    <?php if (!empty($cronogramaCtx['respuestas_eval'])):
        $resp = $cronogramaCtx['respuestas_eval'];
        $promCalc = !empty($cronogramaCtx['promedio']) ? $cronogramaCtx['promedio'] : 0;
    ?>
    <div class="seccion">
        <div class="seccion-titulo">RESULTADOS DE LA EVALUACIÓN</div>
        <?php if (!empty($cronogramaCtx['tema_evaluacion'])): ?>
        <p style="font-size:9pt; margin:2px 0 6px 0; color:#555;">
            <strong>Tema evaluado:</strong> <?= esc($cronogramaCtx['tema_evaluacion']) ?>
        </p>
        <?php endif; ?>
        <table class="tabla-contenido">
            <thead>
                <tr>
                    <th style="width:5%;">#</th>
                    <th style="width:35%;">NOMBRE</th>
                    <th style="width:18%;">CÉDULA</th>
                    <th style="width:25%;">CARGO</th>
                    <th style="width:17%; text-align:center;">CALIFICACIÓN</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resp as $i => $r): ?>
                <tr>
                    <td style="text-align:center;"><?= $i + 1 ?></td>
                    <td><?= esc($r['nombre'] ?? '') ?></td>
                    <td><?= esc($r['cedula'] ?? '') ?></td>
                    <td><?= esc($r['cargo'] ?? '') ?></td>
                    <td style="text-align:center; font-weight:bold; color:<?= ((float)$r['calificacion'] >= 70) ? '#198754' : '#dc3545' ?>;">
                        <?= number_format((float)$r['calificacion'], 1) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="background:#f8f9fa;">
                    <td colspan="4" style="text-align:right; font-weight:bold;">PROMEDIO:</td>
                    <td style="text-align:center; font-weight:bold; font-size:11pt;"><?= number_format((float)$promCalc, 2) ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <?php endif; ?>

    <!-- ASISTENTES Y FIRMAS -->
    <div class="seccion">
        <div class="seccion-titulo">REGISTRO DE ASISTENCIA Y FIRMAS</div>
        <?php if (!empty($asistentes)): ?>
        <table class="tabla-contenido">
            <thead>
                <tr>
                    <th style="width:4%;">#</th>
                    <th style="width:28%;">NOMBRE COMPLETO</th>
                    <th style="width:18%;">DOCUMENTO</th>
                    <th style="width:18%;">CARGO</th>
                    <th style="width:32%;">FIRMA</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($asistentes as $i => $a): ?>
                <tr>
                    <td style="text-align:center; font-weight:bold;"><?= $i + 1 ?></td>
                    <td><?= esc($a['nombre_completo']) ?></td>
                    <td><?= esc($a['tipo_documento'] ?? '') ?> <?= esc($a['numero_documento'] ?? '') ?></td>
                    <td>
                        <?= esc($a['cargo'] ?? '-') ?>
                        <?php if (!empty($a['area_dependencia'])): ?>
                            <br><small style="font-size:8pt; color:#666;"><strong>Contratista:</strong> <?= esc($a['area_dependencia']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:center; vertical-align:middle;">
                        <?php
                        // Preferir data URI base64 (funciona universalmente en DOMPDF).
                        // Path absoluto solo como fallback (no funciona en Windows con DOMPDF).
                        $firmaSrc = '';
                        if (!empty($a['firma_base64'])) {
                            $firmaSrc = $a['firma_base64'];
                        } elseif (!empty($a['firma_full_path'])) {
                            $firmaSrc = $a['firma_full_path'];
                        }
                        ?>
                        <?php if ($firmaSrc): ?>
                            <img src="<?= $firmaSrc ?>" class="firma-img">
                            <div style="font-size:7pt; color:#666;"><?= !empty($a['firmado_at']) ? date('d/m/Y H:i', strtotime($a['firmado_at'])) : '' ?></div>
                        <?php else: ?>
                            <span class="pendiente-text">Sin firma</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p class="empty-text">Sin asistentes registrados.</p>
        <?php endif; ?>
    </div>

    <!-- REGISTRO FOTOGRÁFICO (solo en PDF acta, NO en responsabilidades) -->
    <?php
    $tienePdfFotos = ($pdfType ?? 'acta') === 'acta'
        && !empty($fotosBase64)
        && (
            !empty($fotosBase64['foto_capacitacion'])
            || !empty($fotosBase64['foto_otros_1'])
            || !empty($fotosBase64['foto_otros_2'])
        );
    if ($tienePdfFotos):
    ?>
    <div class="seccion">
        <div class="seccion-titulo">REGISTRO FOTOGRÁFICO</div>
        <table style="width:100%; border-collapse:collapse; margin-top:6px;">
            <tr>
                <?php foreach (['foto_capacitacion','foto_otros_1','foto_otros_2'] as $f): ?>
                    <?php if (!empty($fotosBase64[$f])): ?>
                        <td style="width:33%; padding:4px; text-align:center; vertical-align:top;">
                            <img src="<?= $fotosBase64[$f] ?>" style="max-width:100%; max-height:200px; border:1px solid #999;">
                        </td>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tr>
        </table>
    </div>
    <?php endif; ?>

    <!-- OBSERVACIONES -->
    <?php if (!empty($acta['observaciones'])): ?>
    <div class="seccion">
        <div class="seccion-titulo">OBSERVACIONES</div>
        <div class="seccion-contenido"><p><?= nl2br(esc($acta['observaciones'])) ?></p></div>
    </div>
    <?php endif; ?>

    <div class="pie-documento">
        <p>Documento generado por EnterpriseSST &middot; <?= date('d/m/Y H:i') ?></p>
    </div>
</body>
</html>
