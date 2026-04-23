<?php
$colorClass = function($val) {
    if (in_array($val, ['BUENO','SI'])) return 'text-success';
    if (in_array($val, ['MALO','NO','CRITICO'])) return 'text-danger';
    return 'text-muted';
};
$fDate = function($v) { return !empty($v) ? date('d/m/Y', strtotime($v)) : '-'; };
?>
<div class="container-fluid px-3">
    <div class="mt-2 mb-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Inspeccion del operador de piscina</h6>
        <span class="badge badge-<?= esc($inspeccion['estado']) ?>">
            <?= $inspeccion['estado'] === 'completo' ? 'Completo' : 'Borrador' ?>
        </span>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">DATOS GENERALES</h6>
            <table class="table table-sm mb-0" style="font-size:14px;">
                <tr><td class="text-muted" style="width:45%;">Cliente</td><td><strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong></td></tr>
                <tr><td class="text-muted">Fecha inspeccion</td><td><?= $fDate($inspeccion['fecha_inspeccion']) ?></td></tr>
                <tr><td class="text-muted">Consultor</td><td><?= esc($consultor['nombre_consultor'] ?? '') ?></td></tr>
            </table>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">DATOS PERSONALES</h6>
            <?php if (!empty($inspeccion['foto_piscinero'])): ?>
            <div class="mb-2 text-center"><img src="<?= base_url($inspeccion['foto_piscinero']) ?>" class="img-fluid rounded" style="max-height:140px;"></div>
            <?php endif; ?>
            <table class="table table-sm mb-0" style="font-size:13px;">
                <tr><td class="text-muted" style="width:45%;">Nombre</td><td><?= esc($inspeccion['nombre_piscinero'] ?? '-') ?></td></tr>
                <tr><td class="text-muted">Cedula</td><td><?= esc($inspeccion['cedula'] ?? '-') ?></td></tr>
                <tr><td class="text-muted">Telefono</td><td><?= esc($inspeccion['telefono'] ?? '-') ?></td></tr>
                <tr><td class="text-muted">Vinculacion</td><td><?= esc($inspeccion['vinculacion'] ?? '-') ?></td></tr>
                <tr><td class="text-muted">Empresa contratista</td><td><?= esc($inspeccion['empresa_contratista'] ?? '-') ?></td></tr>
                <tr><td class="text-muted">NIT contratista</td><td><?= esc($inspeccion['nit_empresa_contratista'] ?? '-') ?></td></tr>
            </table>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">CERTIFICACIONES</h6>
            <table class="table table-sm mb-0" style="font-size:13px;">
                <tr><td class="text-muted" style="width:55%;">RCP vigente</td><td><strong class="<?= $colorClass($inspeccion['certificacion_rcp_vigente'] ?? '') ?>"><?= esc($inspeccion['certificacion_rcp_vigente'] ?? '-') ?></strong></td></tr>
                <tr><td class="text-muted">Vencimiento RCP</td><td><?= $fDate($inspeccion['fecha_vencimiento_rcp']) ?></td></tr>
                <tr><td class="text-muted">Curso salvamento acuatico</td><td><strong class="<?= $colorClass($inspeccion['curso_salvamento_acuatico'] ?? '') ?>"><?= esc($inspeccion['curso_salvamento_acuatico'] ?? '-') ?></strong></td></tr>
                <tr><td class="text-muted">Vencimiento salvamento</td><td><?= $fDate($inspeccion['fecha_vencimiento_salvamento']) ?></td></tr>
            </table>
            <div class="row g-2 mt-2">
                <?php if (!empty($inspeccion['foto_certificado_rcp'])): ?>
                <div class="col-6"><small class="text-muted">Cert. RCP</small><br><img src="<?= base_url($inspeccion['foto_certificado_rcp']) ?>" class="img-fluid rounded" style="max-height:100px;"></div>
                <?php endif; ?>
                <?php if (!empty($inspeccion['foto_certificado_salvamento'])): ?>
                <div class="col-6"><small class="text-muted">Cert. Salvamento</small><br><img src="<?= base_url($inspeccion['foto_certificado_salvamento']) ?>" class="img-fluid rounded" style="max-height:100px;"></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">AFILIACIONES SST</h6>
            <table class="table table-sm mb-0" style="font-size:13px;">
                <tr><td class="text-muted" style="width:55%;">ARL vigente</td><td><strong class="<?= $colorClass($inspeccion['afiliacion_arl_vigente'] ?? '') ?>"><?= esc($inspeccion['afiliacion_arl_vigente'] ?? '-') ?></strong></td></tr>
                <tr><td class="text-muted">EPS vigente</td><td><strong class="<?= $colorClass($inspeccion['afiliacion_eps_vigente'] ?? '') ?>"><?= esc($inspeccion['afiliacion_eps_vigente'] ?? '-') ?></strong></td></tr>
                <tr><td class="text-muted">Examenes medicos ocupacionales</td><td><strong class="<?= $colorClass($inspeccion['examenes_medicos_ocupacionales'] ?? '') ?>"><?= esc($inspeccion['examenes_medicos_ocupacionales'] ?? '-') ?></strong></td></tr>
                <tr><td class="text-muted">Fecha ultimo examen medico</td><td><?= $fDate($inspeccion['fecha_ultimo_examen_medico']) ?></td></tr>
            </table>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">DOTACION EPP</h6>
            <table class="table table-sm mb-0" style="font-size:13px;">
                <?php foreach ([
                    'dotacion_epp_entregada'   => 'Dotacion EPP entregada',
                    'gafas_proteccion_quimica' => 'Gafas proteccion quimica',
                    'guantes_nitrilo'          => 'Guantes de nitrilo',
                    'careta_proteccion'        => 'Careta de proteccion',
                    'delantal_impermeable'     => 'Delantal impermeable',
                ] as $k => $l): ?>
                <tr><td class="text-muted" style="width:55%;"><?= $l ?></td><td><strong class="<?= $colorClass($inspeccion[$k] ?? '') ?>"><?= esc($inspeccion[$k] ?? '-') ?></strong></td></tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">CAPACITACION</h6>
            <table class="table table-sm mb-0" style="font-size:13px;">
                <?php foreach ([
                    'capacitacion_manejo_quimicos' => 'Capacitacion manejo de quimicos',
                    'conocimiento_hojas_seguridad' => 'Conocimiento hojas de seguridad',
                    'conocimiento_plan_emergencia' => 'Conocimiento plan de emergencia',
                ] as $k => $l): ?>
                <tr><td class="text-muted" style="width:55%;"><?= $l ?></td><td><strong class="<?= $colorClass($inspeccion[$k] ?? '') ?>"><?= esc($inspeccion[$k] ?? '-') ?></strong></td></tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">HORARIO OPERATIVO</h6>
            <table class="table table-sm mb-0" style="font-size:13px;">
                <tr><td class="text-muted" style="width:55%;">Horario cubre operacion</td><td><strong class="<?= $colorClass($inspeccion['horario_cubre_operacion_piscina'] ?? '') ?>"><?= esc($inspeccion['horario_cubre_operacion_piscina'] ?? '-') ?></strong></td></tr>
                <tr><td class="text-muted">Horario inicio</td><td><?= esc($inspeccion['horario_inicio'] ?? '-') ?></td></tr>
                <tr><td class="text-muted">Horario fin</td><td><?= esc($inspeccion['horario_fin'] ?? '-') ?></td></tr>
            </table>
        </div>
    </div>

    <?php if (!empty($inspeccion['observaciones'])): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">OBSERVACIONES</h6>
            <p style="font-size:14px; margin:0;"><?= nl2br(esc($inspeccion['observaciones'])) ?></p>
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

    <div class="mb-4">
        <?php if ($inspeccion['estado'] === 'completo' && !empty($inspeccion['ruta_pdf'])): ?>
        <a href="<?= base_url('/inspecciones/piscinero/pdf/') ?><?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-primary" target="_blank">
            <i class="fas fa-file-pdf"></i> Ver PDF
        </a>
        <?php endif; ?>
        <?php if ($inspeccion['estado'] === 'completo'): ?>
        <a href="<?= base_url('/inspecciones/piscinero/regenerar/') ?><?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline" onclick="return confirm('¿Regenerar el PDF?')">
            <i class="fas fa-sync-alt me-2"></i>Regenerar PDF
        </a>
        <a href="<?= base_url('/inspecciones/piscinero/enviar-email/') ?><?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline" onclick="return confirm('¿Enviar el PDF por email?')">
            <i class="fas fa-envelope me-2"></i>Enviar por Email
        </a>
        <?php endif; ?>

        <?php if ($inspeccion['estado'] !== 'completo'): ?>
        <a href="<?= base_url('/inspecciones/piscinero/edit/') ?><?= $inspeccion['id'] ?>" class="btn btn-pwa btn-pwa-outline">
            <i class="fas fa-edit"></i> Editar
        </a>
        <?php endif; ?>
    </div>
</div>
