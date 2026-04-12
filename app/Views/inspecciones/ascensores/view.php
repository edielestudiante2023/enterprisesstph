<div class="container-fluid px-3">
    <div class="mt-2 mb-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Inspeccion de Ascensores</h6>
        <span class="badge badge-<?= esc($inspeccion['estado']) ?>">
            <?= $inspeccion['estado'] === 'completo' ? 'Completo' : 'Borrador' ?>
        </span>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">DATOS GENERALES</h6>
            <table class="table table-sm mb-0" style="font-size:14px;">
                <tr><td class="text-muted" style="width:45%;">Cliente</td><td><strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong></td></tr>
                <tr><td class="text-muted">Fecha inspeccion</td><td><?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td></tr>
                <tr><td class="text-muted">Consultor</td><td><?= esc($consultor['nombre_consultor'] ?? '') ?></td></tr>
                <tr><td class="text-muted">Total ascensores</td><td><strong><?= $inspeccion['total_ascensores'] ?? 0 ?></strong></td></tr>
            </table>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">EMPRESA DE MANTENIMIENTO Y ONAC</h6>
            <table class="table table-sm mb-0" style="font-size:13px;">
                <tr><td class="text-muted">Empresa</td><td><?= esc($inspeccion['empresa_mantenimiento'] ?? '-') ?></td></tr>
                <tr><td class="text-muted">NIT</td><td><?= esc($inspeccion['nit_empresa_mantenimiento'] ?? '-') ?></td></tr>
                <tr><td class="text-muted">Contacto</td><td><?= esc($inspeccion['contacto_empresa_mantenimiento'] ?? '-') ?></td></tr>
                <tr><td class="text-muted">Organismo ONAC</td><td><?= esc($inspeccion['organismo_certificador_onac'] ?? '-') ?></td></tr>
                <tr><td class="text-muted">Ultimo certificado</td><td><?= !empty($inspeccion['fecha_ultimo_certificado_onac']) ? date('d/m/Y', strtotime($inspeccion['fecha_ultimo_certificado_onac'])) : '-' ?></td></tr>
                <tr><td class="text-muted">Vencimiento certificado</td><td><?= !empty($inspeccion['fecha_vencimiento_certificado_onac']) ? date('d/m/Y', strtotime($inspeccion['fecha_vencimiento_certificado_onac'])) : '-' ?></td></tr>
                <tr><td class="text-muted">Certificado visible</td><td><?= esc($inspeccion['certificado_visible_al_publico'] ?? '-') ?></td></tr>
                <tr><td class="text-muted">Cronograma anual</td><td><?= esc($inspeccion['cronograma_mantenimiento_anual'] ?? '-') ?></td></tr>
                <tr><td class="text-muted">Reportes tecnicos</td><td><?= esc($inspeccion['reportes_tecnicos_disponibles'] ?? '-') ?></td></tr>
            </table>
        </div>
    </div>

    <?php if (!empty($ascensores)): ?>
    <?php
    $colorClass = function($val) {
        if (in_array($val, ['BUENO','SI'])) return 'text-success';
        if ($val === 'REGULAR') return 'text-warning';
        if (in_array($val, ['MALO','NO','CRITICO'])) return 'text-danger';
        return 'text-muted';
    };
    ?>
    <div class="accordion mb-3" id="accordionViewAsc">
        <?php foreach ($ascensores as $i => $asc): ?>
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#vasc_<?= $i ?>">
                    Ascensor #<?= $i + 1 ?> - <?= esc($asc['identificador'] ?? '') ?>
                    <?php if (!empty($asc['estado_general'])): ?>
                    <span class="badge ms-2" style="font-size:10px; background:#1c2437;"><?= esc($asc['estado_general']) ?></span>
                    <?php endif; ?>
                </button>
            </h2>
            <div id="vasc_<?= $i ?>" class="accordion-collapse collapse" data-bs-parent="#accordionViewAsc">
                <div class="accordion-body p-2">
                    <table class="table table-sm mb-2" style="font-size:12px;">
                        <tr><td class="text-muted" style="width:50%;">Capacidad kg</td><td><?= esc($asc['capacidad_kg'] ?? '-') ?></td></tr>
                        <tr><td class="text-muted">Personas</td><td><?= esc($asc['capacidad_personas'] ?? '-') ?></td></tr>
                        <tr><td class="text-muted">Pisos servidos</td><td><?= esc($asc['pisos_servidos'] ?? '-') ?></td></tr>
                        <tr><td class="text-muted">Tipo</td><td><?= esc($asc['tipo'] ?? '-') ?></td></tr>
                    </table>

                    <?php foreach ($zonas as $zKey => $zCfg): ?>
                    <div class="mb-2" style="border:1px solid #eee; border-radius:4px; padding:6px;">
                        <div style="font-size:11px; font-weight:700; color:#1c2437; text-transform:uppercase; border-bottom:1px solid #ddd; padding-bottom:2px; margin-bottom:4px;"><?= $zCfg['label'] ?></div>
                        <table class="table table-sm mb-0" style="font-size:11px;">
                            <?php foreach ($zCfg['criterios'] as $cKey => $cCfg): ?>
                            <tr>
                                <td class="text-muted" style="width:60%;"><?= $cCfg['label'] ?></td>
                                <td><strong class="<?= $colorClass($asc[$cKey] ?? '') ?>"><?= esc($asc[$cKey] ?? '') ?></strong></td>
                            </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                    <?php endforeach; ?>

                    <?php if (!empty($asc['foto'])): ?>
                    <div class="mb-2">
                        <img src="<?= base_url($asc['foto']) ?>" class="img-fluid rounded"
                             style="max-height:120px; object-fit:cover; cursor:pointer; border:1px solid #ddd;"
                             onclick="openPhoto(this.src)">
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($asc['observaciones'])): ?>
                    <p class="text-muted" style="font-size:12px; margin:0;"><i class="fas fa-comment-alt"></i> <?= esc($asc['observaciones']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($inspeccion['recomendaciones_generales'])): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">RECOMENDACIONES GENERALES</h6>
            <p style="font-size:14px; margin:0;"><?= nl2br(esc($inspeccion['recomendaciones_generales'])) ?></p>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($inspeccion['marco_normativo'])): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">MARCO NORMATIVO CONSULTADO</h6>
            <p style="font-size:12px; margin:0; color:#444;"><?= nl2br(esc($inspeccion['marco_normativo'])) ?></p>
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
        <a href="<?= base_url('/inspecciones/ascensores/pdf/') ?><?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-primary" target="_blank">
            <i class="fas fa-file-pdf"></i> Ver PDF
        </a>
        <?php endif; ?>
        <?php if ($inspeccion['estado'] === 'completo'): ?>
        <a href="<?= base_url('/inspecciones/ascensores/regenerar/') ?><?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline" onclick="return confirm('¿Regenerar el PDF con la plantilla actual?')">
            <i class="fas fa-sync-alt me-2"></i>Regenerar PDF
        </a>
        <a href="<?= base_url('/inspecciones/ascensores/enviar-email/') ?><?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline" onclick="return confirm('¿Enviar el PDF por email al cliente, consultor y consultor externo?')">
            <i class="fas fa-envelope me-2"></i>Enviar por Email
        </a>
        <?php endif; ?>

        <?php if ($inspeccion['estado'] !== 'completo'): ?>
        <a href="<?= base_url('/inspecciones/ascensores/edit/') ?><?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline">
            <i class="fas fa-edit"></i> Editar
        </a>
        <?php endif; ?>
    </div>
</div>
