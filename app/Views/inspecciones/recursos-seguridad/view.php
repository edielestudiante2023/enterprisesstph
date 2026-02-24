<div class="container-fluid px-3">
    <div class="mt-2 mb-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Inspeccion Recursos de Seguridad</h6>
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

    <!-- Recursos de seguridad -->
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">RECURSOS DE SEGURIDAD</h6>
            <?php foreach ($recursos as $key => $info):
                $obs = $inspeccion['obs_' . $key] ?? '';
                $foto = (!empty($info['tiene_foto'])) ? ($inspeccion['foto_' . $key] ?? '') : '';
            ?>
            <div class="border rounded p-2 mb-2" style="border-color:#dee2e6 !important;">
                <div class="d-flex align-items-center mb-1">
                    <i class="fas <?= $info['icon'] ?> text-primary me-1" style="font-size:14px;"></i>
                    <strong style="font-size:13px;"><?= $info['label'] ?></strong>
                </div>
                <?php if (!empty($info['hint'])): ?>
                <p class="text-muted mb-1" style="font-size:11px;">(<?= $info['hint'] ?>)</p>
                <?php endif; ?>
                <?php if (!empty($obs)): ?>
                <p class="mt-1 mb-1 text-muted" style="font-size:12px;">
                    <i class="fas fa-comment-alt"></i> <?= nl2br(esc($obs)) ?>
                </p>
                <?php endif; ?>
                <?php if (!empty($foto)): ?>
                <div class="mt-1">
                    <img src="/<?= esc($foto) ?>" class="img-fluid rounded"
                         style="max-height:100px; object-fit:cover; cursor:pointer; border:1px solid #ddd;"
                         onclick="openPhoto(this.src)">
                </div>
                <?php endif; ?>
                <?php if (empty($obs) && empty($foto)): ?>
                <p class="text-muted mb-0" style="font-size:12px; font-style:italic;">Sin informacion</p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Observaciones generales -->
    <?php if (!empty($inspeccion['observaciones'])): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">OBSERVACIONES GENERALES</h6>
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
        <a href="/inspecciones/recursos-seguridad/pdf/<?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-primary" target="_blank">
            <i class="fas fa-file-pdf"></i> Ver PDF
        </a>
        <?php endif; ?>

        <?php if ($inspeccion['estado'] !== 'completo'): ?>
        <a href="/inspecciones/recursos-seguridad/edit/<?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline">
            <i class="fas fa-edit"></i> Editar
        </a>
        <?php endif; ?>
    </div>
</div>
