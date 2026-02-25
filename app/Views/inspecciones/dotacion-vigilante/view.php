<?php
$colorMap = [
    'bueno' => 'success', 'regular' => 'warning', 'deficiente' => 'danger',
    'no_tiene' => 'secondary', 'no_aplica' => 'light',
];
?>
<div class="container-fluid px-3">
    <div class="mt-2 mb-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Dotacion Vigilante</h6>
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
                <?php if (!empty($inspeccion['contratista'])): ?>
                <tr><td class="text-muted">Contratista</td><td><?= esc($inspeccion['contratista']) ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($inspeccion['servicio'])): ?>
                <tr><td class="text-muted">Servicio</td><td><?= esc($inspeccion['servicio']) ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($inspeccion['nombre_cargo'])): ?>
                <tr><td class="text-muted">Nombre / Cargo</td><td><?= esc($inspeccion['nombre_cargo']) ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($inspeccion['actividades_frecuentes'])): ?>
                <tr><td class="text-muted">Actividades</td><td><?= nl2br(esc($inspeccion['actividades_frecuentes'])) ?></td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <!-- Fotos -->
    <?php if (!empty($inspeccion['foto_cuerpo_completo']) || !empty($inspeccion['foto_cuarto_almacenamiento'])): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">REGISTRO FOTOGRAFICO</h6>
            <div class="d-flex gap-2 flex-wrap">
                <?php if (!empty($inspeccion['foto_cuerpo_completo'])): ?>
                <div>
                    <small class="text-muted">Cuerpo completo</small>
                    <img src="/<?= esc($inspeccion['foto_cuerpo_completo']) ?>" class="img-fluid rounded d-block"
                        style="max-height:120px; cursor:pointer; border:1px solid #ddd;" onclick="openPhoto(this.src)">
                </div>
                <?php endif; ?>
                <?php if (!empty($inspeccion['foto_cuarto_almacenamiento'])): ?>
                <div>
                    <small class="text-muted">Cuarto almacenamiento</small>
                    <img src="/<?= esc($inspeccion['foto_cuarto_almacenamiento']) ?>" class="img-fluid rounded d-block"
                        style="max-height:120px; cursor:pointer; border:1px solid #ddd;" onclick="openPhoto(this.src)">
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Estado EPP -->
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">ESTADO DE DOTACION EPP</h6>
            <?php foreach ($itemsEpp as $key => $info):
                $estado = $inspeccion['estado_' . $key] ?? '';
                $badgeColor = $colorMap[$estado] ?? 'secondary';
                $estadoLabel = $estadosEpp[$estado] ?? 'Sin evaluar';
            ?>
            <div class="border rounded p-2 mb-2 d-flex justify-content-between align-items-center" style="border-color:#dee2e6 !important;">
                <div class="d-flex align-items-center">
                    <i class="fas <?= $info['icon'] ?> text-primary me-2" style="font-size:14px;"></i>
                    <span style="font-size:13px;"><?= $info['label'] ?></span>
                </div>
                <span class="badge bg-<?= $badgeColor ?>" style="font-size:11px;"><?= $estadoLabel ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Concepto final -->
    <?php if (!empty($inspeccion['concepto_final'])): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">CONCEPTO FINAL</h6>
            <p style="font-size:14px; margin:0;"><?= nl2br(esc($inspeccion['concepto_final'])) ?></p>
        </div>
    </div>
    <?php endif; ?>

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
        <a href="/inspecciones/dotacion-vigilante/pdf/<?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-primary" target="_blank">
            <i class="fas fa-file-pdf"></i> Ver PDF
        </a>
        <?php endif; ?>
        <?php if ($inspeccion['estado'] !== 'completo'): ?>
        <a href="/inspecciones/dotacion-vigilante/edit/<?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline">
            <i class="fas fa-edit"></i> Editar
        </a>
        <?php endif; ?>
    </div>
</div>
