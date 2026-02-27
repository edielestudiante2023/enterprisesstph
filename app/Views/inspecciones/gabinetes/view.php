<?php
function estadoColorGab(string $estado): string {
    $map = [
        'BUENO'    => 'text-success',
        'REGULAR'  => 'text-warning',
        'MALO'     => 'text-danger',
        'NO TIENE' => 'text-danger',
    ];
    return $map[$estado] ?? 'text-dark';
}
function sinoColor(string $val): string {
    return $val === 'SI' ? 'text-success' : 'text-danger';
}
?>

<div class="container-fluid px-3">
    <div class="mt-2 mb-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Inspeccion de Gabinetes</h6>
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

    <!-- Gabinetes contra incendio -->
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">GABINETES CONTRA INCENDIO</h6>
            <table class="table table-sm mb-0" style="font-size:14px;">
                <tr>
                    <td class="text-muted" style="width:55%;">Cuenta con gabinetes</td>
                    <td><strong class="<?= sinoColor($inspeccion['tiene_gabinetes'] ?? 'SI') ?>"><?= esc($inspeccion['tiene_gabinetes'] ?? 'SI') ?></strong></td>
                </tr>
                <tr>
                    <td class="text-muted">Entregados por constructora</td>
                    <td><strong class="<?= sinoColor($inspeccion['entregados_constructora'] ?? 'SI') ?>"><?= esc($inspeccion['entregados_constructora'] ?? 'SI') ?></strong></td>
                </tr>
                <tr><td class="text-muted">Cantidad de gabinetes</td><td><strong><?= $inspeccion['cantidad_gabinetes'] ?? 0 ?></strong></td></tr>
                <?php if (!empty($inspeccion['elementos_gabinete'])): ?>
                <tr><td class="text-muted">Elementos por gabinete</td><td><?= nl2br(esc($inspeccion['elementos_gabinete'])) ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($inspeccion['ubicacion_gabinetes'])): ?>
                <tr><td class="text-muted">Ubicacion</td><td><?= nl2br(esc($inspeccion['ubicacion_gabinetes'])) ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($inspeccion['estado_senalizacion_gab'])): ?>
                <tr><td class="text-muted">Estado senalizacion</td><td><?= nl2br(esc($inspeccion['estado_senalizacion_gab'])) ?></td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <!-- Fotos gabinetes generales -->
    <?php if (!empty($inspeccion['foto_gab_1']) || !empty($inspeccion['foto_gab_2'])): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">FOTOS GABINETES</h6>
            <div class="row g-2">
                <?php foreach (['foto_gab_1', 'foto_gab_2'] as $campo): ?>
                    <?php if (!empty($inspeccion[$campo])): ?>
                    <div class="col-6">
                        <img src="/<?= esc($inspeccion[$campo]) ?>" class="img-fluid rounded"
                             style="max-height:120px; object-fit:cover; cursor:pointer; border:1px solid #ddd;"
                             onclick="openPhoto(this.src)">
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Observaciones gabinetes -->
    <?php if (!empty($inspeccion['observaciones_gabinetes'])): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">OBSERVACIONES GABINETES</h6>
            <p style="font-size:14px; margin:0;"><?= nl2br(esc($inspeccion['observaciones_gabinetes'])) ?></p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Gabinetes individuales -->
    <?php if (!empty($gabinetes)): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">GABINETES INDIVIDUALES (<?= count($gabinetes) ?>)</h6>
            <?php foreach ($gabinetes as $gab): ?>
            <div class="card mb-2" style="border-left:3px solid #0d6efd;">
                <div class="card-body p-2">
                    <strong style="font-size:13px;"><i class="fas fa-shower text-primary"></i> Gabinete #<?= $gab['numero'] ?></strong>
                    <?php if (!empty($gab['ubicacion'])): ?>
                    <div class="text-muted" style="font-size:12px;"><?= esc($gab['ubicacion']) ?></div>
                    <?php endif; ?>
                    <table class="table table-sm mb-0 mt-1" style="font-size:12px;">
                        <?php
                        $sinoFields = ['tiene_manguera'=>'Manguera','tiene_hacha'=>'Hacha','tiene_extintor'=>'Extintor',
                                       'tiene_valvula'=>'Valvula','tiene_boquilla'=>'Boquilla','tiene_llave_spanner'=>'Llave spanner'];
                        foreach ($sinoFields as $key => $label):
                            $val = $gab[$key] ?? 'SI';
                        ?>
                        <tr>
                            <td class="text-muted" style="width:55%;"><?= $label ?></td>
                            <td><strong class="<?= sinoColor($val) ?>"><?= esc($val) ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td class="text-muted">Estado general</td>
                            <td><strong class="<?= estadoColorGab($gab['estado'] ?? 'BUENO') ?>"><?= esc($gab['estado'] ?? 'BUENO') ?></strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Senalizacion</td>
                            <td><strong class="<?= estadoColorGab($gab['senalizacion'] ?? 'BUENO') ?>"><?= esc($gab['senalizacion'] ?? 'BUENO') ?></strong></td>
                        </tr>
                    </table>
                    <?php if (!empty($gab['foto'])): ?>
                    <div class="mt-1">
                        <img src="/<?= esc($gab['foto']) ?>" class="img-fluid rounded"
                             style="max-height:80px; cursor:pointer; border:1px solid #ddd;"
                             onclick="openPhoto(this.src)">
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($gab['observaciones'])): ?>
                    <p class="mt-1 mb-0" style="font-size:12px;"><i class="fas fa-comment-alt text-muted"></i> <?= esc($gab['observaciones']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Detectores de humo -->
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">DETECTORES DE HUMO</h6>
            <table class="table table-sm mb-0" style="font-size:14px;">
                <tr>
                    <td class="text-muted" style="width:55%;">Existen detectores</td>
                    <td><strong class="<?= sinoColor($inspeccion['tiene_detectores'] ?? 'SI') ?>"><?= esc($inspeccion['tiene_detectores'] ?? 'SI') ?></strong></td>
                </tr>
                <tr>
                    <td class="text-muted">Entregados por constructora</td>
                    <td><strong class="<?= sinoColor($inspeccion['detectores_entregados'] ?? 'SI') ?>"><?= esc($inspeccion['detectores_entregados'] ?? 'SI') ?></strong></td>
                </tr>
                <tr><td class="text-muted">Cantidad de detectores</td><td><strong><?= $inspeccion['cantidad_detectores'] ?? 0 ?></strong></td></tr>
                <?php if (!empty($inspeccion['ubicacion_detectores'])): ?>
                <tr><td class="text-muted">Ubicacion</td><td><?= nl2br(esc($inspeccion['ubicacion_detectores'])) ?></td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <!-- Fotos detectores -->
    <?php if (!empty($inspeccion['foto_det_1']) || !empty($inspeccion['foto_det_2'])): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">FOTOS DETECTORES</h6>
            <div class="row g-2">
                <?php foreach (['foto_det_1', 'foto_det_2'] as $campo): ?>
                    <?php if (!empty($inspeccion[$campo])): ?>
                    <div class="col-6">
                        <img src="/<?= esc($inspeccion[$campo]) ?>" class="img-fluid rounded"
                             style="max-height:120px; object-fit:cover; cursor:pointer; border:1px solid #ddd;"
                             onclick="openPhoto(this.src)">
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Observaciones detectores -->
    <?php if (!empty($inspeccion['observaciones_detectores'])): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">OBSERVACIONES DETECTORES</h6>
            <p style="font-size:14px; margin:0;"><?= nl2br(esc($inspeccion['observaciones_detectores'])) ?></p>
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
        <a href="/inspecciones/gabinetes/pdf/<?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-primary" target="_blank">
            <i class="fas fa-file-pdf"></i> Ver PDF
        </a>
        <?php endif; ?>
    <?php if ($inspeccion['estado'] === 'completo'): ?>
    <a href="/inspecciones/gabinetes/regenerar/<?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline" onclick="return confirm('¿Regenerar el PDF con la plantilla actual?')">
        <i class="fas fa-sync-alt me-2"></i>Regenerar PDF
    </a>
    <?php endif; ?>

        <?php if ($inspeccion['estado'] !== 'completo'): ?>
        <a href="/inspecciones/gabinetes/edit/<?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline">
            <i class="fas fa-edit"></i> Editar
        </a>
        <?php endif; ?>
    </div>
</div>
