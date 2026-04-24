<div class="container-fluid px-3">
    <div class="mt-2 mb-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Inspeccion Gimnasio (FT-SST-250)</h6>
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
                <tr><td class="text-muted">Aforo maximo</td><td><?= esc($inspeccion['aforo_maximo'] ?? '—') ?></td></tr>
                <tr><td class="text-muted">Horario</td><td><?= esc($inspeccion['horario_operacion'] ?? '—') ?></td></tr>
            </table>
        </div>
    </div>

    <!-- Checklist riesgos -->
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">CHECKLIST RIESGOS LOCATIVOS</h6>
            <?php foreach ($checks as $col => $info):
                $val = $inspeccion[$col] ?? 'NA';
                $clase = $val === 'SI' ? 'text-success' : ($val === 'NO' ? 'text-danger' : 'text-muted');
            ?>
            <div class="border rounded p-2 mb-2">
                <div class="d-flex justify-content-between">
                    <div style="font-size:13px;"><strong><?= $info['codigo'] ?></strong> — <?= esc($info['label']) ?></div>
                    <strong class="<?= $clase ?>" style="font-size:14px;"><?= $val ?></strong>
                </div>
                <div style="font-size:11px; color:#888;"><?= esc($info['fundamento']) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Evidencias -->
    <?php
    $tieneEvidencias = false;
    foreach ($evidenciaMapa as $ev) { if ($ev && !empty($ev['ruta_foto'])) { $tieneEvidencias = true; break; } }
    if ($tieneEvidencias):
    ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">EVIDENCIAS</h6>
            <div class="row g-2">
                <?php foreach ($evidenciaMapa as $slot => $ev): if (!$ev || empty($ev['ruta_foto'])) continue; ?>
                <div class="col-6 col-md-4">
                    <img src="/<?= esc($ev['ruta_foto']) ?>" class="img-fluid rounded" style="max-height:120px; object-fit:cover; cursor:pointer; border:1px solid #ddd;" onclick="openPhoto(this.src)">
                    <div style="font-size:11px; color:#666;" class="mt-1">
                        <strong>Slot <?= $slot ?></strong> — <?= esc($ev['categoria'] ?? '') ?><br>
                        <?= esc($ev['descripcion'] ?? '') ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Observaciones -->
    <?php if (!empty($inspeccion['observaciones_generales']) || !empty($inspeccion['recomendaciones_generales'])): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">OBSERVACIONES Y RECOMENDACIONES</h6>
            <?php if (!empty($inspeccion['observaciones_generales'])): ?>
            <p style="font-size:13px;"><strong>Observaciones:</strong><br><?= nl2br(esc($inspeccion['observaciones_generales'])) ?></p>
            <?php endif; ?>
            <?php if (!empty($inspeccion['recomendaciones_generales'])): ?>
            <p style="font-size:13px;"><strong>Recomendaciones:</strong><br><?= nl2br(esc($inspeccion['recomendaciones_generales'])) ?></p>
            <?php endif; ?>
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
        <a href="<?= base_url('/inspecciones/gimnasio/pdf/') ?><?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-primary" target="_blank">
            <i class="fas fa-file-pdf"></i> Ver PDF
        </a>
        <?php endif; ?>
        <?php if ($inspeccion['estado'] === 'completo'): ?>
        <a href="<?= base_url('/inspecciones/gimnasio/regenerar/') ?><?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline" onclick="return confirm('Regenerar el PDF con la plantilla actual?')">
            <i class="fas fa-sync-alt me-2"></i>Regenerar PDF
        </a>
        <a href="<?= base_url('/inspecciones/gimnasio/enviar-email/') ?><?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline" onclick="return confirm('Enviar el PDF por email al cliente y consultor?')">
            <i class="fas fa-envelope me-2"></i>Enviar por Email
        </a>
        <?php endif; ?>

        <?php if ($inspeccion['estado'] !== 'completo'): ?>
        <a href="<?= base_url('/inspecciones/gimnasio/edit/') ?><?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline">
            <i class="fas fa-edit"></i> Editar
        </a>
        <?php endif; ?>
    </div>
</div>
