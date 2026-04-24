<?php
$recintoLabels = ['TURCO' => 'Baño turco', 'SAUNA' => 'Sauna', 'JACUZZI' => 'Jacuzzi'];
$recintoIcons = ['TURCO' => 'fa-cloud', 'SAUNA' => 'fa-fire-flame-simple', 'JACUZZI' => 'fa-hot-tub-person'];
$recintoFlags = ['TURCO' => 'aplica_turco', 'SAUNA' => 'aplica_sauna', 'JACUZZI' => 'aplica_jacuzzi'];
?>
<div class="container-fluid px-3">
    <div class="mt-2 mb-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Inspeccion Turco + Sauna + Jacuzzi (FT-SST-249)</h6>
        <span class="badge badge-<?= esc($inspeccion['estado']) ?>">
            <?= $inspeccion['estado'] === 'completo' ? 'Completo' : 'Borrador' ?>
        </span>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">DATOS GENERALES</h6>
            <table class="table table-sm mb-0" style="font-size:14px;">
                <tr><td class="text-muted" style="width:45%;">Cliente</td><td><strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong></td></tr>
                <tr><td class="text-muted">Fecha</td><td><?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td></tr>
                <tr><td class="text-muted">Consultor</td><td><?= esc($consultor['nombre_consultor'] ?? '') ?></td></tr>
                <tr><td class="text-muted">Horario</td><td><?= esc($inspeccion['horario_operacion'] ?? '—') ?></td></tr>
                <tr><td class="text-muted">Recintos</td><td>
                    <?php $rcs = []; foreach (['TURCO','SAUNA','JACUZZI'] as $r) if (!empty($inspeccion[$recintoFlags[$r]])) $rcs[] = $recintoLabels[$r]; echo esc(implode(' / ', $rcs) ?: '—'); ?>
                </td></tr>
            </table>
        </div>
    </div>

    <!-- Checklist maestro -->
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">CHECKLIST COMUN</h6>
            <?php foreach ($checksMaestro as $col => $info):
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

    <!-- Secciones por recinto -->
    <?php foreach (['TURCO','SAUNA','JACUZZI'] as $rc):
        if (empty($inspeccion[$recintoFlags[$rc]])) continue;
        $det = $detalleMapa[$rc] ?? null;
        $aforoField = 'aforo_maximo_' . strtolower($rc);
    ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#1c2437;">
                <i class="fas <?= $recintoIcons[$rc] ?>"></i> <?= $recintoLabels[$rc] ?>
            </h6>
            <table class="table table-sm" style="font-size:13px;">
                <tr><td class="text-muted" style="width:45%;">Aforo maximo</td><td><?= esc($inspeccion[$aforoField] ?? '—') ?></td></tr>
                <?php if ($det): ?>
                <tr><td class="text-muted">Material interno</td><td><?= esc($det['material_interno'] ?? '—') ?></td></tr>
                <tr><td class="text-muted">Fuente de calor</td><td><?= esc($det['fuente_calor'] ?? '—') ?></td></tr>
                <tr><td class="text-muted">Temperatura operacion</td><td><?= esc($det['temperatura_operacion'] ?? '—') ?></td></tr>
                <?php if ($rc === 'JACUZZI'): ?>
                <tr><td class="text-muted">Profundidad (m)</td><td><?= esc($det['profundidad_m'] ?? '—') ?></td></tr>
                <tr><td class="text-muted">Temp. agua (°C)</td><td><?= esc($det['temperatura_agua_c'] ?? '—') ?></td></tr>
                <?php endif; ?>
                <?php endif; ?>
            </table>

            <?php if ($det): ?>
            <?php foreach ($checksDetalle as $col => $info):
                if (!in_array($rc, $info['aplica'], true)) continue;
                $val = $det[$col] ?? 'NA';
                $clase = $val === 'SI' ? 'text-success' : ($val === 'NO' ? 'text-danger' : 'text-muted');
            ?>
            <div class="border rounded p-2 mb-2" style="background:#fafbfc;">
                <div class="d-flex justify-content-between">
                    <div style="font-size:12.5px;"><strong><?= $info['codigo'] ?></strong> — <?= esc($info['label']) ?></div>
                    <strong class="<?= $clase ?>" style="font-size:13px;"><?= $val ?></strong>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (!empty($det['observaciones'])): ?>
            <p style="font-size:12px;"><strong>Observaciones:</strong> <?= nl2br(esc($det['observaciones'])) ?></p>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Evidencias -->
    <?php $tieneEv = false; foreach ($evidenciaMapa as $ev) if ($ev && !empty($ev['ruta_foto'])) { $tieneEv = true; break; } ?>
    <?php if ($tieneEv): ?>
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

    <div class="mb-4">
        <?php if ($inspeccion['estado'] === 'completo' && !empty($inspeccion['ruta_pdf'])): ?>
        <a href="<?= base_url('/inspecciones/turco-sauna/pdf/') ?><?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-primary" target="_blank">
            <i class="fas fa-file-pdf"></i> Ver PDF
        </a>
        <?php endif; ?>
        <?php if ($inspeccion['estado'] === 'completo'): ?>
        <a href="<?= base_url('/inspecciones/turco-sauna/regenerar/') ?><?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline" onclick="return confirm('Regenerar el PDF con la plantilla actual?')">
            <i class="fas fa-sync-alt me-2"></i>Regenerar PDF
        </a>
        <a href="<?= base_url('/inspecciones/turco-sauna/enviar-email/') ?><?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline" onclick="return confirm('Enviar el PDF por email al cliente y consultor?')">
            <i class="fas fa-envelope me-2"></i>Enviar por Email
        </a>
        <?php endif; ?>
        <?php if ($inspeccion['estado'] !== 'completo'): ?>
        <a href="<?= base_url('/inspecciones/turco-sauna/edit/') ?><?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline">
            <i class="fas fa-edit"></i> Editar
        </a>
        <?php endif; ?>
    </div>
</div>
