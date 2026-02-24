<?php
function estadoColorGabClient(string $estado): string {
    $map = [
        'BUENO'    => 'color:#28a745;',
        'REGULAR'  => 'color:#ffc107;',
        'MALO'     => 'color:#dc3545;',
        'NO TIENE' => 'color:#dc3545;',
    ];
    return $map[$estado] ?? 'color:#333;';
}
function sinoColorClient(string $val): string {
    return $val === 'SI' ? 'color:#28a745;' : 'color:#dc3545;';
}
?>

<div class="page-header">
    <h1><i class="fas fa-shower me-2"></i> Inspección de Gabinetes</h1>
    <a href="<?= base_url('client/inspecciones/gabinetes') ?>" class="btn-back">
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

<!-- Gabinetes contra incendio -->
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">GABINETES CONTRA INCENDIO</h6>
        <table class="table table-sm mb-0" style="font-size:14px;">
            <tr>
                <td class="text-muted" style="width:50%;">Cuenta con gabinetes</td>
                <td><strong style="<?= sinoColorClient($inspeccion['tiene_gabinetes'] ?? 'SI') ?>"><?= esc($inspeccion['tiene_gabinetes'] ?? 'SI') ?></strong></td>
            </tr>
            <tr>
                <td class="text-muted">Entregados por constructora</td>
                <td><strong style="<?= sinoColorClient($inspeccion['entregados_constructora'] ?? 'SI') ?>"><?= esc($inspeccion['entregados_constructora'] ?? 'SI') ?></strong></td>
            </tr>
            <tr><td class="text-muted">Cantidad de gabinetes</td><td><strong><?= $inspeccion['cantidad_gabinetes'] ?? 0 ?></strong></td></tr>
            <?php if (!empty($inspeccion['elementos_gabinete'])): ?>
            <tr><td class="text-muted">Elementos por gabinete</td><td><?= nl2br(esc($inspeccion['elementos_gabinete'])) ?></td></tr>
            <?php endif; ?>
            <?php if (!empty($inspeccion['ubicacion_gabinetes'])): ?>
            <tr><td class="text-muted">Ubicación</td><td><?= nl2br(esc($inspeccion['ubicacion_gabinetes'])) ?></td></tr>
            <?php endif; ?>
            <?php if (!empty($inspeccion['estado_senalizacion_gab'])): ?>
            <tr><td class="text-muted">Estado señalización</td><td><?= nl2br(esc($inspeccion['estado_senalizacion_gab'])) ?></td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<!-- Fotos gabinetes generales -->
<?php if (!empty($inspeccion['foto_gab_1']) || !empty($inspeccion['foto_gab_2'])): ?>
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">FOTOS GABINETES</h6>
        <div class="row g-2">
            <?php foreach (['foto_gab_1', 'foto_gab_2'] as $campo): ?>
                <?php if (!empty($inspeccion[$campo])): ?>
                <div class="col-6">
                    <img src="/<?= esc($inspeccion[$campo]) ?>" class="img-fluid rounded"
                         style="max-height:200px; object-fit:cover; cursor:pointer; border:1px solid #ddd; width:100%;"
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
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">OBSERVACIONES GABINETES</h6>
        <p style="font-size:14px; margin:0;"><?= nl2br(esc($inspeccion['observaciones_gabinetes'])) ?></p>
    </div>
</div>
<?php endif; ?>

<!-- Gabinetes individuales -->
<?php if (!empty($gabinetes)): ?>
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">GABINETES INDIVIDUALES (<?= count($gabinetes) ?>)</h6>

        <div class="accordion" id="accordionGabinetes">
            <?php foreach ($gabinetes as $i => $gab): ?>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button <?= $i > 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#gabinete<?= $i ?>">
                        <i class="fas fa-shower me-2" style="color:#20c997;"></i>
                        Gabinete #<?= $gab['numero'] ?>
                        <?php if (!empty($gab['ubicacion'])): ?>
                            <small class="text-muted ms-2">— <?= esc($gab['ubicacion']) ?></small>
                        <?php endif; ?>
                    </button>
                </h2>
                <div id="gabinete<?= $i ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>" data-bs-parent="#accordionGabinetes">
                    <div class="accordion-body" style="padding:12px;">
                        <table class="table table-sm mb-0" style="font-size:13px;">
                            <?php
                            $sinoFields = [
                                'tiene_manguera'      => 'Manguera',
                                'tiene_hacha'         => 'Hacha',
                                'tiene_extintor'      => 'Extintor',
                                'tiene_valvula'       => 'Válvula',
                                'tiene_boquilla'      => 'Boquilla',
                                'tiene_llave_spanner' => 'Llave spanner',
                            ];
                            foreach ($sinoFields as $key => $label):
                                $val = $gab[$key] ?? 'SI';
                            ?>
                            <tr>
                                <td class="text-muted" style="width:50%;"><?= $label ?></td>
                                <td><strong style="<?= sinoColorClient($val) ?>"><?= esc($val) ?></strong></td>
                            </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td class="text-muted">Estado general</td>
                                <td><strong style="<?= estadoColorGabClient($gab['estado'] ?? 'BUENO') ?>"><?= esc($gab['estado'] ?? 'BUENO') ?></strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Señalización</td>
                                <td><strong style="<?= estadoColorGabClient($gab['senalizacion'] ?? 'BUENO') ?>"><?= esc($gab['senalizacion'] ?? 'BUENO') ?></strong></td>
                            </tr>
                        </table>
                        <?php if (!empty($gab['foto'])): ?>
                        <div class="mt-2">
                            <img src="/<?= esc($gab['foto']) ?>" class="img-fluid rounded"
                                 style="max-height:150px; cursor:pointer; border:1px solid #ddd;"
                                 onclick="openPhoto(this.src)">
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($gab['observaciones'])): ?>
                        <p class="mt-2 mb-0" style="font-size:12px; color:#777;">
                            <i class="fas fa-comment-alt"></i> <?= esc($gab['observaciones']) ?>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Detectores de humo -->
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">DETECTORES DE HUMO</h6>
        <table class="table table-sm mb-0" style="font-size:14px;">
            <tr>
                <td class="text-muted" style="width:50%;">Existen detectores</td>
                <td><strong style="<?= sinoColorClient($inspeccion['tiene_detectores'] ?? 'SI') ?>"><?= esc($inspeccion['tiene_detectores'] ?? 'SI') ?></strong></td>
            </tr>
            <tr>
                <td class="text-muted">Entregados por constructora</td>
                <td><strong style="<?= sinoColorClient($inspeccion['detectores_entregados'] ?? 'SI') ?>"><?= esc($inspeccion['detectores_entregados'] ?? 'SI') ?></strong></td>
            </tr>
            <tr><td class="text-muted">Cantidad de detectores</td><td><strong><?= $inspeccion['cantidad_detectores'] ?? 0 ?></strong></td></tr>
            <?php if (!empty($inspeccion['ubicacion_detectores'])): ?>
            <tr><td class="text-muted">Ubicación</td><td><?= nl2br(esc($inspeccion['ubicacion_detectores'])) ?></td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<!-- Fotos detectores -->
<?php if (!empty($inspeccion['foto_det_1']) || !empty($inspeccion['foto_det_2'])): ?>
<div class="card mb-3">
    <div class="card-body">
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">FOTOS DETECTORES</h6>
        <div class="row g-2">
            <?php foreach (['foto_det_1', 'foto_det_2'] as $campo): ?>
                <?php if (!empty($inspeccion[$campo])): ?>
                <div class="col-6">
                    <img src="/<?= esc($inspeccion[$campo]) ?>" class="img-fluid rounded"
                         style="max-height:200px; object-fit:cover; cursor:pointer; border:1px solid #ddd; width:100%;"
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
        <h6 style="font-size:14px; color:#999; font-weight:700; margin-bottom:12px;">OBSERVACIONES DETECTORES</h6>
        <p style="font-size:14px; margin:0;"><?= nl2br(esc($inspeccion['observaciones_detectores'])) ?></p>
    </div>
</div>
<?php endif; ?>

<!-- Modal foto -->
<div class="modal fade" id="photoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="background:#000;">
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
    <?php if (!empty($inspeccion['ruta_pdf'])): ?>
    <a href="/inspecciones/gabinetes/pdf/<?= $inspeccion['id'] ?>" class="btn btn-primary" target="_blank" style="background:#bd9751; border-color:#bd9751;">
        <i class="fas fa-file-pdf"></i> Ver PDF
    </a>
    <?php endif; ?>
</div>
