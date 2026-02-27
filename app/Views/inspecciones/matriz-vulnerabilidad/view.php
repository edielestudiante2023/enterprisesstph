<?php
$opLabels = ['a' => 'A (1.0)', 'b' => 'B (0.5)', 'c' => 'C (0.0)'];
$opStyles = [
    'a' => 'background:#d4edda; color:#155724;',
    'b' => 'background:#fff3cd; color:#856404;',
    'c' => 'background:#f8d7da; color:#721c24;',
];
?>
<div class="container-fluid px-3">
    <div class="mt-2 mb-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Matriz de Vulnerabilidad</h6>
        <span class="badge badge-<?= esc($inspeccion['estado']) ?>">
            <?= $inspeccion['estado'] === 'completo' ? 'Completo' : 'Borrador' ?>
        </span>
    </div>

    <!-- Datos generales -->
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">DATOS GENERALES</h6>
            <table class="table table-sm mb-0" style="font-size:14px;">
                <tr><td class="text-muted" style="width:45%;">Cliente</td><td><strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong></td></tr>
                <tr><td class="text-muted">Fecha inspeccion</td><td><?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td></tr>
                <tr><td class="text-muted">Consultor</td><td><?= esc($consultor['nombre_consultor'] ?? '') ?></td></tr>
            </table>
        </div>
    </div>

    <!-- Puntaje y clasificacion -->
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">RESULTADO DE LA EVALUACION</h6>
            <div class="text-center mb-2">
                <span style="font-size:32px; font-weight:bold; color:<?= $clasificacion['text_color'] ?>;">
                    <?= number_format($puntaje, 1) ?>
                </span>
                <span style="font-size:14px; color:#999;">/100</span>
            </div>
            <div class="text-center">
                <span class="badge" style="<?= $clasificacion['color'] ?> color:<?= $clasificacion['text_color'] ?>; font-size:13px; padding:6px 12px;">
                    <?= $clasificacion['label'] ?>
                </span>
            </div>
            <div class="progress mt-2" style="height:10px;">
                <div class="progress-bar" style="width:<?= $puntaje ?>%; background:<?= $clasificacion['text_color'] ?>;"></div>
            </div>
            <p class="mt-2" style="font-size:12px; color:#666;"><?= $clasificacion['desc'] ?></p>
        </div>
    </div>

    <!-- Criterios evaluados -->
    <?php foreach ($criterios as $key => $criterio):
        $val = $inspeccion[$key] ?? null;
    ?>
    <div class="card mb-2">
        <div class="card-body py-2 px-3">
            <div class="d-flex justify-content-between align-items-start">
                <span style="font-size:12px; font-weight:bold; color:#555; flex:1;">
                    <?= $criterio['numero'] ?>. <?= $criterio['titulo'] ?>
                </span>
                <?php if ($val && isset($opLabels[$val])): ?>
                <span class="badge" style="<?= $opStyles[$val] ?> font-size:11px; padding:4px 8px;">
                    <?= $opLabels[$val] ?>
                </span>
                <?php else: ?>
                <span class="text-muted" style="font-size:11px; font-style:italic;">Sin evaluar</span>
                <?php endif; ?>
            </div>
            <?php if ($val && isset($criterio['opciones'][$val])): ?>
            <p style="font-size:11px; color:#777; margin:4px 0 0;"><?= $criterio['opciones'][$val] ?></p>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Observaciones -->
    <?php if (!empty($inspeccion['observaciones'])): ?>
    <div class="card mb-3 mt-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">OBSERVACIONES DEL CONSULTOR</h6>
            <p style="font-size:14px; margin:0;"><?= nl2br(esc($inspeccion['observaciones'])) ?></p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Acciones -->
    <div class="mb-4">
        <?php if ($inspeccion['estado'] === 'completo' && !empty($inspeccion['ruta_pdf'])): ?>
        <a href="/inspecciones/matriz-vulnerabilidad/pdf/<?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-primary" target="_blank">
            <i class="fas fa-file-pdf"></i> Ver PDF
        </a>
        <?php endif; ?>
    <?php if ($inspeccion['estado'] === 'completo'): ?>
    <a href="/inspecciones/matriz-vulnerabilidad/regenerar/<?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline" onclick="return confirm('¿Regenerar el PDF con la plantilla actual?')">
        <i class="fas fa-sync-alt me-2"></i>Regenerar PDF
    </a>
    <?php endif; ?>

        <?php if ($inspeccion['estado'] !== 'completo'): ?>
        <a href="/inspecciones/matriz-vulnerabilidad/edit/<?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline">
            <i class="fas fa-edit"></i> Editar
        </a>
        <?php endif; ?>
    </div>
</div>
