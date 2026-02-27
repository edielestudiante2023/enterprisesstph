<?php
$colorMap = [
    'bueno' => 'success', 'regular' => 'warning', 'malo' => 'orange',
    'deficiente' => 'danger', 'no_tiene' => 'secondary', 'no_aplica' => 'light',
];
?>
<style>
.badge-orange { background-color: #fd7e14; color: white; }
</style>
<div class="container-fluid px-3">
    <div class="mt-2 mb-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Auditoria Zona Residuos</h6>
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

    <!-- Items de inspeccion -->
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">ITEMS DE INSPECCION</h6>
            <?php foreach ($itemsZona as $key => $info): ?>
            <div class="border rounded p-2 mb-2" style="border-color:#dee2e6 !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="fas <?= $info['icon'] ?> text-primary me-2" style="font-size:14px;"></i>
                        <span style="font-size:13px;"><?= $info['label'] ?></span>
                    </div>
                    <?php if ($info['tipo'] === 'enum'):
                        $estado = $inspeccion['estado_' . $key] ?? '';
                        $badgeColor = $colorMap[$estado] ?? 'secondary';
                        $estadoLabel = $estadosZona[$estado] ?? 'Sin evaluar';
                    ?>
                    <span class="badge bg-<?= $badgeColor ?> <?= $badgeColor === 'orange' ? 'badge-orange' : '' ?>" style="font-size:11px;"><?= $estadoLabel ?></span>
                    <?php else: ?>
                    <span style="font-size:12px; color:#555;"><?= esc($inspeccion[$key] ?? 'Sin informacion') ?></span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($inspeccion['foto_' . $key])): ?>
                <div class="mt-2">
                    <img src="/<?= esc($inspeccion['foto_' . $key]) ?>" class="img-fluid rounded"
                        style="max-height:120px; cursor:pointer; border:1px solid #ddd;" onclick="openPhoto(this.src)">
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Observaciones -->
    <?php if (!empty($inspeccion['observaciones'])): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">OBSERVACIONES</h6>
            <p style="font-size:14px; margin:0;"><?= nl2br(esc($inspeccion['observaciones'])) ?></p>
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
        <a href="/inspecciones/auditoria-zona-residuos/pdf/<?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-primary" target="_blank">
            <i class="fas fa-file-pdf"></i> Ver PDF
        </a>
        <?php endif; ?>
    <?php if ($inspeccion['estado'] === 'completo'): ?>
    <a href="/inspecciones/auditoria-zona-residuos/regenerar/<?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline" onclick="return confirm('¿Regenerar el PDF con la plantilla actual?')">
        <i class="fas fa-sync-alt me-2"></i>Regenerar PDF
    </a>
    <?php endif; ?>
        <?php if ($inspeccion['estado'] !== 'completo'): ?>
        <a href="/inspecciones/auditoria-zona-residuos/edit/<?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline">
            <i class="fas fa-edit"></i> Editar
        </a>
        <?php endif; ?>
    </div>
</div>
