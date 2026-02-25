<?php
$opLabels = ['a' => 'A (1.0)', 'b' => 'B (0.5)', 'c' => 'C (0.0)'];
$opStyles = [
    'a' => 'background:#d4edda; color:#155724;',
    'b' => 'background:#fff3cd; color:#856404;',
    'c' => 'background:#f8d7da; color:#721c24;',
];
?>

<div class="page-header">
    <h1><i class="fas fa-shield-alt me-2"></i> Matriz de Vulnerabilidad</h1>
    <a href="<?= base_url('client/inspecciones/matriz-vulnerabilidad') ?>" class="btn-back">
        <i class="fas fa-arrow-left me-1"></i> Volver
    </a>
</div>

<!-- Datos generales -->
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">DATOS GENERALES</h6>
        <table class="table table-sm mb-0" style="font-size:14px;">
            <tr><td class="text-muted" style="width:40%;">Cliente</td><td><strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong></td></tr>
            <tr><td class="text-muted">Fecha inspección</td><td><?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td></tr>
            <tr><td class="text-muted">Consultor</td><td><?= esc($consultor['nombre_consultor'] ?? '') ?></td></tr>
        </table>
    </div>
</div>

<!-- Puntaje y clasificación -->
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">RESULTADO DE LA EVALUACIÓN</h6>
        <div class="text-center mb-2">
            <span style="font-size:36px; font-weight:bold; color:<?= $clasificacion['text_color'] ?>;">
                <?= number_format($puntaje, 1) ?>
            </span>
            <span style="font-size:14px; color:#999;">/100</span>
        </div>
        <div class="text-center">
            <span class="badge" style="background:<?= $clasificacion['color'] ?>; color:<?= $clasificacion['text_color'] ?>; font-size:13px; padding:6px 14px;">
                <?= $clasificacion['label'] ?>
            </span>
        </div>
        <div class="progress mt-3" style="height:10px; border-radius:5px;">
            <div class="progress-bar" style="width:<?= $puntaje ?>%; background:<?= $clasificacion['text_color'] ?>; border-radius:5px;"></div>
        </div>
        <p class="mt-2" style="font-size:12px; color:#666;"><?= $clasificacion['desc'] ?></p>
    </div>
</div>

<!-- Criterios evaluados -->
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">CRITERIOS EVALUADOS (<?= count($criterios) ?>)</h6>
        <?php foreach ($criterios as $key => $criterio):
            $val = $inspeccion[$key] ?? null;
        ?>
        <div style="border:1px solid #eee; border-radius:8px; padding:10px 12px; margin-bottom:8px;">
            <div class="d-flex justify-content-between align-items-start">
                <span style="font-size:13px; font-weight:600; color:#555; flex:1;">
                    <?= $criterio['numero'] ?>. <?= $criterio['titulo'] ?>
                </span>
                <?php if ($val && isset($opLabels[$val])): ?>
                <span class="badge" style="<?= $opStyles[$val] ?> font-size:11px; padding:4px 10px;">
                    <?= $opLabels[$val] ?>
                </span>
                <?php else: ?>
                <span style="font-size:11px; color:#999; font-style:italic;">Sin evaluar</span>
                <?php endif; ?>
            </div>
            <?php if ($val && isset($criterio['opciones'][$val])): ?>
            <p style="font-size:11px; color:#777; margin:6px 0 0;"><?= $criterio['opciones'][$val] ?></p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Observaciones -->
<?php if (!empty($inspeccion['observaciones'])): ?>
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">OBSERVACIONES DEL CONSULTOR</h6>
        <p style="font-size:14px; margin:0;"><?= nl2br(esc($inspeccion['observaciones'])) ?></p>
    </div>
</div>
<?php endif; ?>

<!-- Acciones -->
<div class="mb-4">
    <?php if (!empty($inspeccion['ruta_pdf'])): ?>
    <a href="/inspecciones/matriz-vulnerabilidad/pdf/<?= $inspeccion['id'] ?>" class="btn btn-primary" target="_blank" style="background:#bd9751; border-color:#bd9751;">
        <i class="fas fa-file-pdf"></i> Ver PDF
    </a>
    <?php endif; ?>
</div>
