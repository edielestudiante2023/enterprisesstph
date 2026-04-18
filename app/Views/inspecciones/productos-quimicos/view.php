<?php
$calificaciones = [
    'C'  => ['label' => 'Cumple',         'color' => '#28a745'],
    'CP' => ['label' => 'Cumple Parcial', 'color' => '#ffc107'],
    'NC' => ['label' => 'No Cumple',      'color' => '#dc3545'],
    'NA' => ['label' => 'No Aplica',      'color' => '#6c757d'],
];

$pct = $inspeccion['porcentaje_cumplimiento'] ?? null;
$nivel = $inspeccion['nivel_riesgo'] ?? null;
$nivelMap = [
    'alto'  => ['Alto - Control adecuado',    '#28a745'],
    'medio' => ['Medio - Requiere mejoras',   '#ffc107'],
    'bajo'  => ['Bajo - Riesgo significativo','#dc3545'],
];
$nivelInfo = $nivel ? ($nivelMap[$nivel] ?? [$nivel, '#6c757d']) : null;
?>

<div class="container-fluid px-3">
    <div class="mt-2 mb-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Inspeccion Productos Quimicos</h6>
        <span class="badge badge-<?= esc($inspeccion['estado']) ?>">
            <?= $inspeccion['estado'] === 'completo' ? 'Completo' : 'Borrador' ?>
        </span>
    </div>

    <!-- Score -->
    <?php if ($pct !== null): ?>
    <div class="card mb-3" style="border:none; background:linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-6 text-center">
                    <small class="text-muted d-block" style="font-size:11px;">CUMPLIMIENTO</small>
                    <div style="font-size:38px; font-weight:700; color:#1c2437;"><?= number_format((float)$pct, 1) ?>%</div>
                </div>
                <div class="col-6 text-center">
                    <?php if ($nivelInfo): ?>
                    <div style="background:<?= $nivelInfo[1] ?>; color:#fff; padding:10px 12px; border-radius:10px; font-weight:600; font-size:14px;">
                        <?= esc($nivelInfo[0]) ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Datos generales -->
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">DATOS GENERALES</h6>
            <table class="table table-sm mb-0" style="font-size:14px;">
                <tr><td class="text-muted" style="width:45%;">Cliente</td><td><strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong></td></tr>
                <tr><td class="text-muted">Fecha inspeccion</td><td><?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td></tr>
                <tr><td class="text-muted">Consultor</td><td><?= esc($consultor['nombre_consultor'] ?? '') ?></td></tr>
                <?php if (!empty($inspeccion['ubicacion'])): ?>
                <tr><td class="text-muted">Ubicacion</td><td><?= esc($inspeccion['ubicacion']) ?></td></tr>
                <?php endif; ?>
                <tr><td class="text-muted">Guadaniadora / combustible</td><td><strong class="<?= !empty($inspeccion['tiene_guadaniadora']) ? 'text-warning' : 'text-muted' ?>"><?= !empty($inspeccion['tiene_guadaniadora']) ? 'SI - incluye preguntas 16-17' : 'NO aplica' ?></strong></td></tr>
            </table>
        </div>
    </div>

    <!-- Checklist -->
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">LISTA DE CHEQUEO</h6>
            <table class="table table-sm mb-0" style="font-size:12px;">
                <thead style="background:#f8f9fa;">
                    <tr>
                        <th style="width:35px;">#</th>
                        <th>Pregunta</th>
                        <th style="width:90px; text-align:center;">Calificacion</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $num => $cfg):
                        $col = 'cal_item_' . str_pad($num, 2, '0', STR_PAD_LEFT);
                        $val = $inspeccion[$col] ?? null;
                        $esCondicional = $cfg['grupo'] === 'condicional';
                        if ($esCondicional && empty($inspeccion['tiene_guadaniadora'])) continue;
                        $info = $val ? ($calificaciones[$val] ?? null) : null;
                    ?>
                    <tr>
                        <td><?= $num ?></td>
                        <td><?= esc($cfg['label']) ?> <?php if ($esCondicional): ?><span class="badge bg-warning text-dark" style="font-size:9px;">Combustible</span><?php endif; ?></td>
                        <td style="text-align:center;">
                            <?php if ($info): ?>
                                <span style="background:<?= $info['color'] ?>; color:#fff; padding:2px 8px; border-radius:4px; font-weight:600; font-size:11px;"><?= $val ?></span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Fotos -->
    <?php if (!empty($fotos)): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">FOTOS DE EVIDENCIA (<?= count($fotos) ?>)</h6>
            <div class="row g-2">
                <?php foreach ($fotos as $i => $f): ?>
                <div class="col-6">
                    <?php if (!empty($f['foto'])): ?>
                    <img src="/<?= esc($f['foto']) ?>" class="img-fluid rounded"
                         style="max-height:140px; width:100%; object-fit:cover; cursor:pointer; border:1px solid #ddd;"
                         onclick="openPhoto(this.src)">
                    <?php endif; ?>
                    <small class="text-muted d-block mt-1" style="font-size:11px;"><strong>Foto #<?= $i+1 ?></strong></small>
                    <?php if (!empty($f['observacion'])): ?>
                    <p style="font-size:12px; margin:0; color:#555;"><?= nl2br(esc($f['observacion'])) ?></p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Observaciones finales -->
    <?php if (!empty($inspeccion['observaciones_finales'])): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">OBSERVACIONES FINALES</h6>
            <p style="font-size:14px; margin:0;"><?= nl2br(esc($inspeccion['observaciones_finales'])) ?></p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Modal foto -->
    <div class="modal fade" id="photoModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-dark">
                <div class="modal-body p-1 text-center">
                    <img id="photoFull" src="" class="img-fluid" style="max-height:80vh;">
                </div>
            </div>
        </div>
    </div>
    <script>
    function openPhoto(src) {
        document.getElementById('photoFull').src = src;
        new bootstrap.Modal(document.getElementById('photoModal')).show();
    }
    </script>

    <!-- Acciones -->
    <div class="mb-4">
        <?php if ($inspeccion['estado'] === 'completo' && !empty($inspeccion['ruta_pdf'])): ?>
        <a href="<?= base_url('/inspecciones/productos-quimicos/pdf/') ?><?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-primary" target="_blank">
            <i class="fas fa-file-pdf"></i> Ver PDF
        </a>
        <?php endif; ?>
        <?php if ($inspeccion['estado'] === 'completo'): ?>
        <a href="<?= base_url('/inspecciones/productos-quimicos/regenerar/') ?><?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline" onclick="return confirm('Regenerar el PDF con la plantilla actual?')">
            <i class="fas fa-sync-alt me-2"></i>Regenerar PDF
        </a>
        <a href="<?= base_url('/inspecciones/productos-quimicos/enviar-email/') ?><?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline" onclick="return confirm('Enviar el PDF por email?')">
            <i class="fas fa-envelope me-2"></i>Enviar por Email
        </a>
        <?php endif; ?>

        <?php if ($inspeccion['estado'] !== 'completo'): ?>
        <a href="<?= base_url('/inspecciones/productos-quimicos/edit/') ?><?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline">
            <i class="fas fa-edit"></i> Editar
        </a>
        <?php endif; ?>
    </div>
</div>
