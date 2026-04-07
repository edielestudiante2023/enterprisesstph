<div class="container-fluid px-3">
    <!-- Saludo -->
    <div class="mt-2 mb-3">
        <h5 class="mb-0">Hola, <?= esc($nombre) ?></h5>
        <small class="text-muted"><?= date('d \d\e F, Y') ?></small>
    </div>

    <!-- Documentos pendientes — acordeón -->
    <?php
    $totalPend = count($pendientes ?? [])
        + count($pendientesLocativas ?? [])
        + count($pendientesSenalizacion ?? [])
        + count($pendientesExtintores ?? [])
        + count($pendientesBotiquin ?? [])
        + count($pendientesGabinetes ?? [])
        + count($pendientesComunicaciones ?? [])
        + count($pendientesRecursosSeg ?? [])
        + count($pendientesProbPeligros ?? [])
        + count($pendientesMatrizVul ?? [])
        + count($pendientesPlanEmg ?? [])
        + count($pendientesSimulacro ?? [])
        + count($pendientesHvBrig ?? [])
        + count($pendientesDotVig ?? [])
        + count($pendientesDotAse ?? [])
        + count($pendientesDotTod ?? [])
        + count($pendientesAudRes ?? [])
        + count($pendientesRepCap ?? [])
        + count($pendientesPrepSim ?? [])
        + count($pendientesAsistInd ?? [])
        + count($pendientesProgLimp ?? [])
        + count($pendientesProgRes ?? [])
        + count($pendientesProgPlag ?? [])
        + count($pendientesProgAgua ?? [])
        + count($pendientesPlanSan ?? [])
        + count($pendientesKpiLimp ?? [])
        + count($pendientesKpiRes ?? [])
        + count($pendientesKpiPlag ?? [])
        + count($pendientesKpiAgua ?? []);
    ?>
    <?php if ($totalPend > 0): ?>
    <div class="accordion mb-3" id="accordionPendientes">
        <div class="accordion-item" style="border:none;">
            <h2 class="accordion-header">
                <button class="accordion-button <?= $totalPend === 0 ? 'collapsed' : '' ?>"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapsePendientes"
                        style="background:#fff3cd; color:#856404; font-weight:600; font-size:14px;">
                    <i class="fas fa-clock me-2"></i>
                    Pendientes
                    <span class="badge bg-warning text-dark ms-2"><?= $totalPend ?></span>
                </button>
            </h2>
            <div id="collapsePendientes" class="accordion-collapse collapse show">
                <div class="accordion-body px-0 pt-2 pb-0">

    <?php if (!empty($pendientes)): ?>
    <?php foreach ($pendientes as $doc): ?>
    <div class="card card-inspeccion <?= esc($doc['estado']) ?>">
        <div class="card-body py-3 px-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong>
                        <?php if ($doc['estado'] === 'borrador'): ?>
                            <i class="fas fa-edit text-warning"></i>
                        <?php else: ?>
                            <i class="fas fa-signature text-orange"></i>
                        <?php endif; ?>
                        Acta - <?= esc($doc['nombre_cliente'] ?? 'Sin cliente') ?>
                    </strong>
                    <div class="text-muted" style="font-size: 13px;">
                        <?= date('d/m/Y', strtotime($doc['fecha_visita'])) ?>
                        &middot;
                        <span class="badge badge-<?= esc($doc['estado']) ?>" style="font-size: 11px;">
                            <?= $doc['estado'] === 'borrador' ? 'Borrador' : 'Pend. Firma' ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="mt-2 d-flex gap-1">
                <?php if ($doc['estado'] === 'borrador'): ?>
                    <a href="<?= base_url('/inspecciones/acta-visita/edit/') ?><?= $doc['id'] ?>" class="btn btn-sm btn-outline-dark">
                        Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmarEliminar('<?= base_url('/inspecciones/acta-visita/delete/') ?><?= $doc['id'] ?>')">
                        <i class="fas fa-trash"></i>
                    </button>
                <?php else: ?>
                    <a href="<?= base_url('/inspecciones/acta-visita/firma/') ?><?= $doc['id'] ?>" class="btn btn-sm btn-outline-warning">
                        Ir a firmas <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Pendientes locativas -->
    <?php if (!empty($pendientesLocativas)): ?>
    <?php foreach ($pendientesLocativas as $loc): ?>
    <div class="card card-inspeccion borrador">
        <div class="card-body py-3 px-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong>
                        <i class="fas fa-edit text-warning"></i>
                        Locativa - <?= esc($loc['nombre_cliente'] ?? 'Sin cliente') ?>
                    </strong>
                    <div class="text-muted" style="font-size: 13px;">
                        <?= date('d/m/Y', strtotime($loc['fecha_inspeccion'])) ?>
                        &middot;
                        <span class="badge badge-borrador" style="font-size: 11px;">Borrador</span>
                    </div>
                </div>
            </div>
            <div class="mt-2 d-flex gap-1">
                <a href="<?= base_url('/inspecciones/inspeccion-locativa/edit/') ?><?= $loc['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmarEliminar('<?= base_url('/inspecciones/inspeccion-locativa/delete/') ?><?= $loc['id'] ?>')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Pendientes señalización -->
    <?php if (!empty($pendientesSenalizacion)): ?>
    <?php foreach ($pendientesSenalizacion as $sen): ?>
    <div class="card card-inspeccion borrador">
        <div class="card-body py-3 px-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong>
                        <i class="fas fa-edit text-warning"></i>
                        Senalizacion - <?= esc($sen['nombre_cliente'] ?? 'Sin cliente') ?>
                    </strong>
                    <div class="text-muted" style="font-size: 13px;">
                        <?= date('d/m/Y', strtotime($sen['fecha_inspeccion'])) ?>
                        &middot;
                        <span class="badge badge-borrador" style="font-size: 11px;">Borrador</span>
                    </div>
                </div>
            </div>
            <div class="mt-2 d-flex gap-1">
                <a href="<?= base_url('/inspecciones/senalizacion/edit/') ?><?= $sen['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmarEliminar('<?= base_url('/inspecciones/senalizacion/delete/') ?><?= $sen['id'] ?>')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Pendientes extintores -->
    <?php if (!empty($pendientesExtintores)): ?>
    <?php foreach ($pendientesExtintores as $ext): ?>
    <div class="card card-inspeccion borrador">
        <div class="card-body py-3 px-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong>
                        <i class="fas fa-edit text-warning"></i>
                        Extintores - <?= esc($ext['nombre_cliente'] ?? 'Sin cliente') ?>
                    </strong>
                    <div class="text-muted" style="font-size: 13px;">
                        <?= date('d/m/Y', strtotime($ext['fecha_inspeccion'])) ?>
                        &middot;
                        <span class="badge badge-borrador" style="font-size: 11px;">Borrador</span>
                    </div>
                </div>
            </div>
            <div class="mt-2 d-flex gap-1">
                <a href="<?= base_url('/inspecciones/extintores/edit/') ?><?= $ext['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmarEliminar('<?= base_url('/inspecciones/extintores/delete/') ?><?= $ext['id'] ?>')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Pendientes botiquín -->
    <?php if (!empty($pendientesBotiquin)): ?>
    <?php foreach ($pendientesBotiquin as $bot): ?>
    <div class="card card-inspeccion borrador">
        <div class="card-body py-3 px-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong>
                        <i class="fas fa-edit text-warning"></i>
                        Botiquin - <?= esc($bot['nombre_cliente'] ?? 'Sin cliente') ?>
                    </strong>
                    <div class="text-muted" style="font-size: 13px;">
                        <?= date('d/m/Y', strtotime($bot['fecha_inspeccion'])) ?>
                        &middot;
                        <span class="badge badge-borrador" style="font-size: 11px;">Borrador</span>
                    </div>
                </div>
            </div>
            <div class="mt-2 d-flex gap-1">
                <a href="<?= base_url('/inspecciones/botiquin/edit/') ?><?= $bot['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmarEliminar('<?= base_url('/inspecciones/botiquin/delete/') ?><?= $bot['id'] ?>')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Pendientes gabinetes -->
    <?php if (!empty($pendientesGabinetes)): ?>
    <?php foreach ($pendientesGabinetes as $gab): ?>
    <div class="card card-inspeccion borrador">
        <div class="card-body py-3 px-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong>
                        <i class="fas fa-edit text-warning"></i>
                        Gabinetes - <?= esc($gab['nombre_cliente'] ?? 'Sin cliente') ?>
                    </strong>
                    <div class="text-muted" style="font-size: 13px;">
                        <?= date('d/m/Y', strtotime($gab['fecha_inspeccion'])) ?>
                        &middot;
                        <span class="badge badge-borrador" style="font-size: 11px;">Borrador</span>
                    </div>
                </div>
            </div>
            <div class="mt-2 d-flex gap-1">
                <a href="<?= base_url('/inspecciones/gabinetes/edit/') ?><?= $gab['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmarEliminar('<?= base_url('/inspecciones/gabinetes/delete/') ?><?= $gab['id'] ?>')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Pendientes comunicaciones -->
    <?php if (!empty($pendientesComunicaciones)): ?>
    <?php foreach ($pendientesComunicaciones as $com): ?>
    <div class="card card-inspeccion borrador">
        <div class="card-body py-3 px-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong>
                        <i class="fas fa-edit text-warning"></i>
                        Comunicaciones - <?= esc($com['nombre_cliente'] ?? 'Sin cliente') ?>
                    </strong>
                    <div class="text-muted" style="font-size: 13px;">
                        <?= date('d/m/Y', strtotime($com['fecha_inspeccion'])) ?>
                        &middot;
                        <span class="badge badge-borrador" style="font-size: 11px;">Borrador</span>
                    </div>
                </div>
            </div>
            <div class="mt-2 d-flex gap-1">
                <a href="<?= base_url('/inspecciones/comunicaciones/edit/') ?><?= $com['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmarEliminar('<?= base_url('/inspecciones/comunicaciones/delete/') ?><?= $com['id'] ?>')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Pendientes recursos seguridad -->
    <?php if (!empty($pendientesRecursosSeg)): ?>
    <?php foreach ($pendientesRecursosSeg as $rec): ?>
    <div class="card card-inspeccion borrador">
        <div class="card-body py-3 px-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong>
                        <i class="fas fa-edit text-warning"></i>
                        Rec. Seguridad - <?= esc($rec['nombre_cliente'] ?? 'Sin cliente') ?>
                    </strong>
                    <div class="text-muted" style="font-size: 13px;">
                        <?= date('d/m/Y', strtotime($rec['fecha_inspeccion'])) ?>
                        &middot;
                        <span class="badge badge-borrador" style="font-size: 11px;">Borrador</span>
                    </div>
                </div>
            </div>
            <div class="mt-2 d-flex gap-1">
                <a href="<?= base_url('/inspecciones/recursos-seguridad/edit/') ?><?= $rec['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmarEliminar('<?= base_url('/inspecciones/recursos-seguridad/delete/') ?><?= $rec['id'] ?>')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Pendientes probabilidad peligros -->
    <?php if (!empty($pendientesProbPeligros)): ?>
    <?php foreach ($pendientesProbPeligros as $pp): ?>
    <div class="card card-inspeccion borrador">
        <div class="card-body py-3 px-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong>
                        <i class="fas fa-edit text-warning"></i>
                        Prob. Peligros - <?= esc($pp['nombre_cliente'] ?? 'Sin cliente') ?>
                    </strong>
                    <div class="text-muted" style="font-size: 13px;">
                        <?= date('d/m/Y', strtotime($pp['fecha_inspeccion'])) ?>
                        &middot;
                        <span class="badge badge-borrador" style="font-size: 11px;">Borrador</span>
                    </div>
                </div>
            </div>
            <div class="mt-2 d-flex gap-1">
                <a href="<?= base_url('/inspecciones/probabilidad-peligros/edit/') ?><?= $pp['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmarEliminar('<?= base_url('/inspecciones/probabilidad-peligros/delete/') ?><?= $pp['id'] ?>')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Pendientes matriz vulnerabilidad -->
    <?php if (!empty($pendientesMatrizVul)): ?>
    <?php foreach ($pendientesMatrizVul as $mv): ?>
    <div class="card card-inspeccion borrador">
        <div class="card-body py-3 px-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong>
                        <i class="fas fa-edit text-warning"></i>
                        Matriz Vuln. - <?= esc($mv['nombre_cliente'] ?? 'Sin cliente') ?>
                    </strong>
                    <div class="text-muted" style="font-size: 13px;">
                        <?= date('d/m/Y', strtotime($mv['fecha_inspeccion'])) ?>
                        &middot;
                        <span class="badge badge-borrador" style="font-size: 11px;">Borrador</span>
                    </div>
                </div>
            </div>
            <div class="mt-2 d-flex gap-1">
                <a href="<?= base_url('/inspecciones/matriz-vulnerabilidad/edit/') ?><?= $mv['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmarEliminar('<?= base_url('/inspecciones/matriz-vulnerabilidad/delete/') ?><?= $mv['id'] ?>')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Pendientes plan emergencia -->
    <?php if (!empty($pendientesPlanEmg)): ?>
    <?php foreach ($pendientesPlanEmg as $pe): ?>
    <div class="card card-inspeccion borrador">
        <div class="card-body py-3 px-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong>
                        <i class="fas fa-edit text-warning"></i>
                        Plan Emerg. - <?= esc($pe['nombre_cliente'] ?? 'Sin cliente') ?>
                    </strong>
                    <div class="text-muted" style="font-size: 13px;">
                        <?= date('d/m/Y', strtotime($pe['fecha_visita'])) ?>
                        &middot;
                        <span class="badge badge-borrador" style="font-size: 11px;">Borrador</span>
                    </div>
                </div>
            </div>
            <div class="mt-2 d-flex gap-1">
                <a href="<?= base_url('/inspecciones/plan-emergencia/edit/') ?><?= $pe['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmarEliminar('<?= base_url('/inspecciones/plan-emergencia/delete/') ?><?= $pe['id'] ?>')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Pendientes simulacro -->
    <?php if (!empty($pendientesSimulacro)): ?>
    <?php foreach ($pendientesSimulacro as $sim): ?>
    <div class="card card-inspeccion borrador">
        <div class="card-body py-3 px-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong>
                        <i class="fas fa-edit text-warning"></i>
                        Ev. Simulacro - <?= esc($sim['nombre_cliente'] ?? 'Sin cliente') ?>
                    </strong>
                    <div class="text-muted" style="font-size: 13px;">
                        <?= date('d/m/Y', strtotime($sim['fecha'])) ?>
                        <?php if (!empty($sim['nombre_brigadista_lider'])): ?>
                            &middot; <?= esc($sim['nombre_brigadista_lider']) ?>
                        <?php endif; ?>
                        &middot;
                        <span class="badge badge-borrador" style="font-size: 11px;">Borrador</span>
                    </div>
                </div>
            </div>
            <div class="mt-2 d-flex gap-1">
                <a href="<?= base_url('/inspecciones/simulacro/view/') ?><?= $sim['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Ver <i class="fas fa-eye ms-1"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmarEliminar('<?= base_url('/inspecciones/simulacro/delete/') ?><?= $sim['id'] ?>')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Pendientes HV brigadista -->
    <?php if (!empty($pendientesHvBrig)): ?>
    <?php foreach ($pendientesHvBrig as $hvb): ?>
    <div class="card card-inspeccion borrador">
        <div class="card-body py-3 px-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong>
                        <i class="fas fa-edit text-warning"></i>
                        HV Brigadista - <?= esc($hvb['nombre_cliente'] ?? 'Sin cliente') ?>
                    </strong>
                    <div class="text-muted" style="font-size: 13px;">
                        <?= esc($hvb['nombre_completo'] ?? '') ?>
                        &middot; CC <?= esc($hvb['documento_identidad'] ?? '') ?>
                        &middot;
                        <span class="badge badge-borrador" style="font-size: 11px;">Borrador</span>
                    </div>
                </div>
            </div>
            <div class="mt-2 d-flex gap-1">
                <a href="<?= base_url('/inspecciones/hv-brigadista/view/') ?><?= $hvb['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Ver <i class="fas fa-eye ms-1"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmarEliminar('<?= base_url('/inspecciones/hv-brigadista/delete/') ?><?= $hvb['id'] ?>')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Pendientes dotación vigilante -->
    <?php if (!empty($pendientesDotVig)): ?>
    <?php foreach ($pendientesDotVig as $dv): ?>
    <div class="card card-inspeccion borrador">
        <div class="card-body py-3 px-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong><i class="fas fa-edit text-warning"></i> Dot. Vigilante - <?= esc($dv['nombre_cliente'] ?? 'Sin cliente') ?></strong>
                    <div class="text-muted" style="font-size: 13px;">
                        <?= date('d/m/Y', strtotime($dv['fecha_inspeccion'])) ?> &middot;
                        <span class="badge badge-borrador" style="font-size: 11px;">Borrador</span>
                    </div>
                </div>
            </div>
            <div class="mt-2 d-flex gap-1">
                <a href="<?= base_url('/inspecciones/dotacion-vigilante/edit/') ?><?= $dv['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmarEliminar('<?= base_url('/inspecciones/dotacion-vigilante/delete/') ?><?= $dv['id'] ?>')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Pendientes dotación aseadora -->
    <?php if (!empty($pendientesDotAse)): ?>
    <?php foreach ($pendientesDotAse as $da): ?>
    <div class="card card-inspeccion borrador">
        <div class="card-body py-3 px-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong><i class="fas fa-edit text-warning"></i> Dot. Aseadora - <?= esc($da['nombre_cliente'] ?? 'Sin cliente') ?></strong>
                    <div class="text-muted" style="font-size: 13px;">
                        <?= date('d/m/Y', strtotime($da['fecha_inspeccion'])) ?> &middot;
                        <span class="badge badge-borrador" style="font-size: 11px;">Borrador</span>
                    </div>
                </div>
            </div>
            <div class="mt-2 d-flex gap-1">
                <a href="<?= base_url('/inspecciones/dotacion-aseadora/edit/') ?><?= $da['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmarEliminar('<?= base_url('/inspecciones/dotacion-aseadora/delete/') ?><?= $da['id'] ?>')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Pendientes dotación todero -->
    <?php if (!empty($pendientesDotTod)): ?>
    <?php foreach ($pendientesDotTod as $dt): ?>
    <div class="card card-inspeccion borrador">
        <div class="card-body py-3 px-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong><i class="fas fa-edit text-warning"></i> Dot. Todero - <?= esc($dt['nombre_cliente'] ?? 'Sin cliente') ?></strong>
                    <div class="text-muted" style="font-size: 13px;">
                        <?= date('d/m/Y', strtotime($dt['fecha_inspeccion'])) ?> &middot;
                        <span class="badge badge-borrador" style="font-size: 11px;">Borrador</span>
                    </div>
                </div>
            </div>
            <div class="mt-2 d-flex gap-1">
                <a href="<?= base_url('/inspecciones/dotacion-todero/edit/') ?><?= $dt['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmarEliminar('<?= base_url('/inspecciones/dotacion-todero/delete/') ?><?= $dt['id'] ?>')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Pendientes auditoría zona residuos -->
    <?php if (!empty($pendientesAudRes)): ?>
    <?php foreach ($pendientesAudRes as $ar): ?>
    <div class="card card-inspeccion borrador">
        <div class="card-body py-3 px-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong><i class="fas fa-edit text-warning"></i> Zona Residuos - <?= esc($ar['nombre_cliente'] ?? 'Sin cliente') ?></strong>
                    <div class="text-muted" style="font-size: 13px;">
                        <?= date('d/m/Y', strtotime($ar['fecha_inspeccion'])) ?> &middot;
                        <span class="badge badge-borrador" style="font-size: 11px;">Borrador</span>
                    </div>
                </div>
            </div>
            <div class="mt-2 d-flex gap-1">
                <a href="<?= base_url('/inspecciones/auditoria-zona-residuos/edit/') ?><?= $ar['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmarEliminar('<?= base_url('/inspecciones/auditoria-zona-residuos/delete/') ?><?= $ar['id'] ?>')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Pendientes reporte capacitación -->
    <?php if (!empty($pendientesRepCap)): ?>
    <?php foreach ($pendientesRepCap as $rc): ?>
    <div class="card card-inspeccion borrador">
        <div class="card-body py-3 px-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong><i class="fas fa-edit text-warning"></i> Capacitacion - <?= esc($rc['nombre_cliente'] ?? 'Sin cliente') ?></strong>
                    <div class="text-muted" style="font-size: 13px;">
                        <?= date('d/m/Y', strtotime($rc['fecha_capacitacion'])) ?> &middot;
                        <span class="badge badge-borrador" style="font-size: 11px;">Borrador</span>
                    </div>
                </div>
            </div>
            <div class="mt-2 d-flex gap-1">
                <a href="<?= base_url('/inspecciones/reporte-capacitacion/edit/') ?><?= $rc['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmarEliminar('<?= base_url('/inspecciones/reporte-capacitacion/delete/') ?><?= $rc['id'] ?>')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Pendientes preparación simulacro -->
    <?php if (!empty($pendientesPrepSim)): ?>
    <?php foreach ($pendientesPrepSim as $ps): ?>
    <div class="card card-inspeccion borrador">
        <div class="card-body py-3 px-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong><i class="fas fa-edit text-warning"></i> Prep. Simulacro - <?= esc($ps['nombre_cliente'] ?? 'Sin cliente') ?></strong>
                    <div class="text-muted" style="font-size: 13px;">
                        <?= date('d/m/Y', strtotime($ps['fecha_simulacro'])) ?> &middot;
                        <span class="badge badge-borrador" style="font-size: 11px;">Borrador</span>
                    </div>
                </div>
            </div>
            <div class="mt-2 d-flex gap-1">
                <a href="<?= base_url('/inspecciones/preparacion-simulacro/edit/') ?><?= $ps['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmarEliminar('<?= base_url('/inspecciones/preparacion-simulacro/delete/') ?><?= $ps['id'] ?>')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Pendientes asistencia inducción -->
    <?php if (!empty($pendientesAsistInd)): ?>
    <?php foreach ($pendientesAsistInd as $ai): ?>
    <div class="card card-inspeccion borrador">
        <div class="card-body py-3 px-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong><i class="fas fa-edit text-warning"></i> Asistencia - <?= esc($ai['nombre_cliente'] ?? 'Sin cliente') ?></strong>
                    <div class="text-muted" style="font-size: 13px;">
                        <?= date('d/m/Y', strtotime($ai['fecha_sesion'])) ?> &middot;
                        <span class="badge badge-borrador" style="font-size: 11px;">Borrador</span>
                    </div>
                </div>
            </div>
            <div class="mt-2 d-flex gap-1">
                <a href="<?= base_url('/inspecciones/asistencia-induccion/edit/') ?><?= $ai['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmarEliminar('<?= base_url('/inspecciones/asistencia-induccion/delete/') ?><?= $ai['id'] ?>')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Pendientes programa residuos -->
    <?php if (!empty($pendientesProgRes)): ?>
    <?php foreach ($pendientesProgRes as $pr): ?>
    <div class="card card-inspeccion borrador">
        <div class="card-body py-3 px-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong><i class="fas fa-edit text-warning"></i> Residuos - <?= esc($pr['nombre_cliente'] ?? 'Sin cliente') ?></strong>
                    <div class="text-muted" style="font-size: 13px;">
                        <?= date('d/m/Y', strtotime($pr['fecha_programa'])) ?> &middot;
                        <span class="badge badge-borrador" style="font-size: 11px;">Borrador</span>
                    </div>
                </div>
            </div>
            <div class="mt-2 d-flex gap-1">
                <a href="<?= base_url('/inspecciones/residuos-solidos/edit/') ?><?= $pr['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmarEliminar('<?= base_url('/inspecciones/residuos-solidos/delete/') ?><?= $pr['id'] ?>')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Pendientes programa plagas -->
    <?php if (!empty($pendientesProgPlag)): ?>
    <?php foreach ($pendientesProgPlag as $pp): ?>
    <div class="card card-inspeccion borrador">
        <div class="card-body py-3 px-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong><i class="fas fa-edit text-warning"></i> Plagas - <?= esc($pp['nombre_cliente'] ?? 'Sin cliente') ?></strong>
                    <div class="text-muted" style="font-size: 13px;">
                        <?= date('d/m/Y', strtotime($pp['fecha_programa'])) ?> &middot;
                        <span class="badge badge-borrador" style="font-size: 11px;">Borrador</span>
                    </div>
                </div>
            </div>
            <div class="mt-2 d-flex gap-1">
                <a href="<?= base_url('/inspecciones/control-plagas/edit/') ?><?= $pp['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmarEliminar('<?= base_url('/inspecciones/control-plagas/delete/') ?><?= $pp['id'] ?>')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Pendientes programa limpieza -->
    <?php if (!empty($pendientesProgLimp)): ?>
    <?php foreach ($pendientesProgLimp as $pl): ?>
    <div class="card card-inspeccion borrador">
        <div class="card-body py-3 px-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong><i class="fas fa-edit text-warning"></i> Limpieza - <?= esc($pl['nombre_cliente'] ?? 'Sin cliente') ?></strong>
                    <div class="text-muted" style="font-size: 13px;">
                        <?= date('d/m/Y', strtotime($pl['fecha_programa'])) ?> &middot;
                        <span class="badge badge-borrador" style="font-size: 11px;">Borrador</span>
                    </div>
                </div>
            </div>
            <div class="mt-2 d-flex gap-1">
                <a href="<?= base_url('/inspecciones/limpieza-desinfeccion/edit/') ?><?= $pl['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmarEliminar('<?= base_url('/inspecciones/limpieza-desinfeccion/delete/') ?><?= $pl['id'] ?>')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Pendientes programa agua potable -->
    <?php if (!empty($pendientesProgAgua)): ?>
    <?php foreach ($pendientesProgAgua as $pa): ?>
    <div class="card card-inspeccion borrador">
        <div class="card-body py-3 px-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong><i class="fas fa-edit text-warning"></i> Agua Potable - <?= esc($pa['nombre_cliente'] ?? 'Sin cliente') ?></strong>
                    <div class="text-muted" style="font-size: 13px;">
                        <?= date('d/m/Y', strtotime($pa['fecha_programa'])) ?> &middot;
                        <span class="badge badge-borrador" style="font-size: 11px;">Borrador</span>
                    </div>
                </div>
            </div>
            <div class="mt-2 d-flex gap-1">
                <a href="<?= base_url('/inspecciones/agua-potable/edit/') ?><?= $pa['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmarEliminar('<?= base_url('/inspecciones/agua-potable/delete/') ?><?= $pa['id'] ?>')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Pendientes plan saneamiento -->
    <?php if (!empty($pendientesPlanSan)): ?>
    <?php foreach ($pendientesPlanSan as $ps): ?>
    <div class="card card-inspeccion borrador">
        <div class="card-body py-3 px-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong><i class="fas fa-edit text-warning"></i> Plan Saneamiento - <?= esc($ps['nombre_cliente'] ?? 'Sin cliente') ?></strong>
                    <div class="text-muted" style="font-size: 13px;">
                        <?= date('d/m/Y', strtotime($ps['fecha_programa'])) ?> &middot;
                        <span class="badge badge-borrador" style="font-size: 11px;">Borrador</span>
                    </div>
                </div>
            </div>
            <div class="mt-2 d-flex gap-1">
                <a href="<?= base_url('/inspecciones/plan-saneamiento/edit/') ?><?= $ps['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmarEliminar('<?= base_url('/inspecciones/plan-saneamiento/delete/') ?><?= $ps['id'] ?>')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Pendientes Contingencia Plagas -->
    <?php if (!empty($pendientesContPlagas)): ?>
    <?php foreach ($pendientesContPlagas as $cp): ?>
    <div class="card card-inspeccion borrador">
        <div class="card-body py-3 px-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong><i class="fas fa-edit text-warning"></i> Contingencia Plagas - <?= esc($cp['nombre_cliente'] ?? 'Sin cliente') ?></strong>
                    <div class="text-muted" style="font-size: 13px;">
                        <?= date('d/m/Y', strtotime($cp['fecha_programa'])) ?> &middot;
                        <span class="badge badge-borrador" style="font-size: 11px;">Borrador</span>
                    </div>
                </div>
            </div>
            <div class="mt-2 d-flex gap-1">
                <a href="<?= base_url('/inspecciones/contingencia-plagas/edit/') ?><?= $cp['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmarEliminar('<?= base_url('/inspecciones/contingencia-plagas/delete/') ?><?= $cp['id'] ?>')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Pendientes Contingencia Agua -->
    <?php if (!empty($pendientesContAgua)): ?>
    <?php foreach ($pendientesContAgua as $ca): ?>
    <div class="card card-inspeccion borrador">
        <div class="card-body py-3 px-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong><i class="fas fa-edit text-warning"></i> Contingencia Agua - <?= esc($ca['nombre_cliente'] ?? 'Sin cliente') ?></strong>
                    <div class="text-muted" style="font-size: 13px;">
                        <?= date('d/m/Y', strtotime($ca['fecha_programa'])) ?> &middot;
                        <span class="badge badge-borrador" style="font-size: 11px;">Borrador</span>
                    </div>
                </div>
            </div>
            <div class="mt-2 d-flex gap-1">
                <a href="<?= base_url('/inspecciones/contingencia-agua/edit/') ?><?= $ca['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmarEliminar('<?= base_url('/inspecciones/contingencia-agua/delete/') ?><?= $ca['id'] ?>')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Pendientes Contingencia Basura -->
    <?php if (!empty($pendientesContBasura)): ?>
    <?php foreach ($pendientesContBasura as $cb): ?>
    <div class="card card-inspeccion borrador">
        <div class="card-body py-3 px-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong><i class="fas fa-edit text-warning"></i> Contingencia Basura - <?= esc($cb['nombre_cliente'] ?? 'Sin cliente') ?></strong>
                    <div class="text-muted" style="font-size: 13px;">
                        <?= date('d/m/Y', strtotime($cb['fecha_programa'])) ?> &middot;
                        <span class="badge badge-borrador" style="font-size: 11px;">Borrador</span>
                    </div>
                </div>
            </div>
            <div class="mt-2 d-flex gap-1">
                <a href="<?= base_url('/inspecciones/contingencia-basura/edit/') ?><?= $cb['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmarEliminar('<?= base_url('/inspecciones/contingencia-basura/delete/') ?><?= $cb['id'] ?>')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Pendientes KPI Limpieza -->
    <?php if (!empty($pendientesKpiLimp)): ?>
    <?php foreach ($pendientesKpiLimp as $pk): ?>
    <div class="card card-inspeccion borrador">
        <div class="card-body py-3 px-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong><i class="fas fa-edit text-warning"></i> KPI Limpieza - <?= esc($pk['nombre_cliente'] ?? 'Sin cliente') ?></strong>
                    <div class="text-muted" style="font-size: 13px;">
                        <?= date('d/m/Y', strtotime($pk['fecha_inspeccion'])) ?> &middot;
                        <span class="badge badge-borrador" style="font-size: 11px;">Borrador</span>
                    </div>
                </div>
            </div>
            <div class="mt-2 d-flex gap-1">
                <a href="<?= base_url('/inspecciones/kpi-limpieza/edit/') ?><?= $pk['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmarEliminar('<?= base_url('/inspecciones/kpi-limpieza/delete/') ?><?= $pk['id'] ?>')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Pendientes KPI Residuos -->
    <?php if (!empty($pendientesKpiRes)): ?>
    <?php foreach ($pendientesKpiRes as $pk): ?>
    <div class="card card-inspeccion borrador">
        <div class="card-body py-3 px-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong><i class="fas fa-edit text-warning"></i> KPI Residuos - <?= esc($pk['nombre_cliente'] ?? 'Sin cliente') ?></strong>
                    <div class="text-muted" style="font-size: 13px;">
                        <?= date('d/m/Y', strtotime($pk['fecha_inspeccion'])) ?> &middot;
                        <span class="badge badge-borrador" style="font-size: 11px;">Borrador</span>
                    </div>
                </div>
            </div>
            <div class="mt-2 d-flex gap-1">
                <a href="<?= base_url('/inspecciones/kpi-residuos/edit/') ?><?= $pk['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmarEliminar('<?= base_url('/inspecciones/kpi-residuos/delete/') ?><?= $pk['id'] ?>')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Pendientes KPI Plagas -->
    <?php if (!empty($pendientesKpiPlag)): ?>
    <?php foreach ($pendientesKpiPlag as $pk): ?>
    <div class="card card-inspeccion borrador">
        <div class="card-body py-3 px-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong><i class="fas fa-edit text-warning"></i> KPI Plagas - <?= esc($pk['nombre_cliente'] ?? 'Sin cliente') ?></strong>
                    <div class="text-muted" style="font-size: 13px;">
                        <?= date('d/m/Y', strtotime($pk['fecha_inspeccion'])) ?> &middot;
                        <span class="badge badge-borrador" style="font-size: 11px;">Borrador</span>
                    </div>
                </div>
            </div>
            <div class="mt-2 d-flex gap-1">
                <a href="<?= base_url('/inspecciones/kpi-plagas/edit/') ?><?= $pk['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmarEliminar('<?= base_url('/inspecciones/kpi-plagas/delete/') ?><?= $pk['id'] ?>')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Pendientes KPI Agua Potable -->
    <?php if (!empty($pendientesKpiAgua)): ?>
    <?php foreach ($pendientesKpiAgua as $pk): ?>
    <div class="card card-inspeccion borrador">
        <div class="card-body py-3 px-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong><i class="fas fa-edit text-warning"></i> KPI Agua Potable - <?= esc($pk['nombre_cliente'] ?? 'Sin cliente') ?></strong>
                    <div class="text-muted" style="font-size: 13px;">
                        <?= date('d/m/Y', strtotime($pk['fecha_inspeccion'])) ?> &middot;
                        <span class="badge badge-borrador" style="font-size: 11px;">Borrador</span>
                    </div>
                </div>
            </div>
            <div class="mt-2 d-flex gap-1">
                <a href="<?= base_url('/inspecciones/kpi-agua-potable/edit/') ?><?= $pk['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmarEliminar('<?= base_url('/inspecciones/kpi-agua-potable/delete/') ?><?= $pk['id'] ?>')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

                </div><!-- /accordion-body -->
            </div><!-- /collapse -->
        </div><!-- /accordion-item -->
    </div><!-- /accordion -->
    <?php endif; ?>

    <!-- Buscador de inspecciones -->
    <div class="mb-3 mt-2">
        <div class="input-group">
            <span class="input-group-text" style="background:#1c2437; color:#bd9751; border:none;"><i class="fas fa-search"></i></span>
            <input type="text" id="buscarInspeccion" class="form-control" placeholder="Buscar inspección..." style="border:1px solid #dee2e6; font-size:14px;">
        </div>
    </div>

    <!-- Card Agendamiento destacada -->
    <div class="section-title">Agendamiento</div>
    <a href="<?= base_url('/inspecciones/agendamiento') ?>" class="card mb-3 border-0" style="background: linear-gradient(135deg, #1c2437, #2c3e50); border-radius: 12px; text-decoration:none;">
        <div class="card-body py-3 px-3 d-flex align-items-center justify-content-between">
            <div>
                <div style="color: #bd9751; font-weight: 700; font-size: 16px;">
                    <i class="fas fa-calendar-alt me-2"></i>Agendamientos
                </div>
                <div style="color: #adb5bd; font-size: 13px;">
                    <?= $totalAgendamientos ?> visita<?= $totalAgendamientos !== 1 ? 's' : '' ?> pendiente<?= $totalAgendamientos !== 1 ? 's' : '' ?>
                </div>
            </div>
            <div style="color: #bd9751; font-size: 24px;">
                <i class="fas fa-arrow-right"></i>
            </div>
        </div>
    </a>

    <!-- Mini-universo Plan de Emergencia -->
    <div class="section-title">Plan de Emergencia</div>
    <div class="card mb-3 border-0" style="background: linear-gradient(135deg, #7B2D3B 0%, #5C1A28 100%); border-radius: 12px; padding: 14px 10px 10px;">
        <div style="text-align:center; margin-bottom:8px;">
            <span style="color:#f0d6a2; font-size:11px; font-weight:600; letter-spacing:0.5px; text-transform:uppercase;">
                <i class="fas fa-project-diagram me-1"></i>Estos modulos alimentan el Plan de Emergencia
            </span>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:8px;">
            <a href="<?= base_url('/inspecciones/inspeccion-locativa') ?>" style="padding:12px 6px; margin:0; border-radius:10px; background:rgba(255,255,255,0.12); text-align:center; text-decoration:none; transition:transform .2s;">
                <i class="fas fa-hard-hat" style="font-size:22px; color:#fff; display:block; margin-bottom:2px;"></i>
                <div><strong style="font-size:11px; color:#fff;">Locativa</strong></div>
                <div style="font-size:11px; color:rgba(255,255,255,0.6);">(<?= $totalLocativas ?>)</div>
            </a>
            <a href="<?= base_url('/inspecciones/extintores') ?>" style="padding:12px 6px; margin:0; border-radius:10px; background:rgba(255,255,255,0.12); text-align:center; text-decoration:none; transition:transform .2s;">
                <i class="fas fa-fire-extinguisher" style="font-size:22px; color:#fff; display:block; margin-bottom:2px;"></i>
                <div><strong style="font-size:11px; color:#fff;">Extintores</strong></div>
                <div style="font-size:11px; color:rgba(255,255,255,0.6);">(<?= $totalExtintores ?>)</div>
            </a>
            <a href="<?= base_url('/inspecciones/botiquin') ?>" style="padding:12px 6px; margin:0; border-radius:10px; background:rgba(255,255,255,0.12); text-align:center; text-decoration:none; transition:transform .2s;">
                <i class="fas fa-first-aid" style="font-size:22px; color:#fff; display:block; margin-bottom:2px;"></i>
                <div><strong style="font-size:11px; color:#fff;">Botiquín</strong></div>
                <div style="font-size:11px; color:rgba(255,255,255,0.6);">(<?= $totalBotiquin ?>)</div>
            </a>
            <a href="<?= base_url('/inspecciones/gabinetes') ?>" style="padding:12px 6px; margin:0; border-radius:10px; background:rgba(255,255,255,0.12); text-align:center; text-decoration:none; transition:transform .2s;">
                <i class="fas fa-shower" style="font-size:22px; color:#fff; display:block; margin-bottom:2px;"></i>
                <div><strong style="font-size:11px; color:#fff;">Gabinetes</strong></div>
                <div style="font-size:11px; color:rgba(255,255,255,0.6);">(<?= $totalGabinetes ?>)</div>
            </a>
            <a href="<?= base_url('/inspecciones/comunicaciones') ?>" style="padding:12px 6px; margin:0; border-radius:10px; background:rgba(255,255,255,0.12); text-align:center; text-decoration:none; transition:transform .2s;">
                <i class="fas fa-walkie-talkie" style="font-size:22px; color:#fff; display:block; margin-bottom:2px;"></i>
                <div><strong style="font-size:11px; color:#fff;">Comunic.</strong></div>
                <div style="font-size:11px; color:rgba(255,255,255,0.6);">(<?= $totalComunicaciones ?>)</div>
            </a>
            <a href="<?= base_url('/inspecciones/recursos-seguridad') ?>" style="padding:12px 6px; margin:0; border-radius:10px; background:rgba(255,255,255,0.12); text-align:center; text-decoration:none; transition:transform .2s;">
                <i class="fas fa-shield-alt" style="font-size:22px; color:#fff; display:block; margin-bottom:2px;"></i>
                <div><strong style="font-size:11px; color:#fff;">Rec. Seguridad</strong></div>
                <div style="font-size:11px; color:rgba(255,255,255,0.6);">(<?= $totalRecursosSeg ?>)</div>
            </a>
            <a href="<?= base_url('/inspecciones/probabilidad-peligros') ?>" style="padding:12px 6px; margin:0; border-radius:10px; background:rgba(255,255,255,0.12); text-align:center; text-decoration:none; transition:transform .2s;">
                <i class="fas fa-exclamation-triangle" style="font-size:22px; color:#fff; display:block; margin-bottom:2px;"></i>
                <div><strong style="font-size:11px; color:#fff;">Prob. Peligros</strong></div>
                <div style="font-size:11px; color:rgba(255,255,255,0.6);">(<?= $totalProbPeligros ?>)</div>
            </a>
            <a href="<?= base_url('/inspecciones/matriz-vulnerabilidad') ?>" style="padding:12px 6px; margin:0; border-radius:10px; background:rgba(255,255,255,0.12); text-align:center; text-decoration:none; transition:transform .2s;">
                <i class="fas fa-th-list" style="font-size:22px; color:#fff; display:block; margin-bottom:2px;"></i>
                <div><strong style="font-size:11px; color:#fff;">Matriz Vuln.</strong></div>
                <div style="font-size:11px; color:rgba(255,255,255,0.6);">(<?= $totalMatrizVul ?>)</div>
            </a>
            <a href="<?= base_url('/inspecciones/plan-emergencia') ?>" style="padding:12px 6px; margin:0; border-radius:10px; background:rgba(255,255,255,0.18); text-align:center; text-decoration:none; border:2px solid #bd9751; transition:transform .2s;">
                <i class="fas fa-file-medical" style="font-size:22px; color:#bd9751; display:block; margin-bottom:2px;"></i>
                <div><strong style="font-size:11px; color:#bd9751;">Plan Emerg.</strong></div>
                <div style="font-size:11px; color:rgba(255,255,255,0.6);">(<?= $totalPlanEmergencia ?>)</div>
            </a>
        </div>
    </div>

    <!-- Inspecciones generales -->
    <div class="section-title">Inspecciones</div>
    <div class="grid-inspecciones mb-4">
        <a href="<?= base_url('/inspecciones/acta-visita') ?>" class="card-tipo">
            <i class="fas fa-clipboard-list"></i>
            <div><strong>Actas de Visita</strong></div>
            <div class="count">(<?= $totalActas ?>)</div>
        </a>
        <a href="<?= base_url('/inspecciones/senalizacion') ?>" class="card-tipo">
            <i class="fas fa-search"></i>
            <div><strong>Senalizacion</strong></div>
            <div class="count">(<?= $totalSenalizacion ?>)</div>
        </a>
        <a href="<?= base_url('/inspecciones/carta-vigia') ?>" class="card-tipo">
            <i class="fas fa-user-shield"></i>
            <div><strong>Carta Vigia</strong></div>
            <div class="count">(<?= $totalCartasVigiaPend ?> pend.)</div>
        </a>
        <a href="<?= base_url('/inspecciones/planilla-seg-social') ?>" class="card-tipo">
            <i class="fas fa-file-invoice"></i>
            <div><strong>Planilla SS</strong></div>
            <div class="count">(<?= $totalPlanillaSS ?>)</div>
        </a>
        <a href="<?= base_url('/inspecciones/urls') ?>" class="card-tipo">
            <i class="fas fa-link"></i>
            <div><strong>Accesos Rápidos</strong></div>
            <div class="count">URLs</div>
        </a>
    </div>

    <!-- Simulacros -->
    <div class="section-title">Simulacros</div>
    <div class="card mb-3 border-0" style="background:#eef4fc; border-radius:12px; padding:10px 8px 6px;">
        <div class="grid-inspecciones">
            <a href="<?= base_url('/inspecciones/simulacro') ?>" class="card-tipo">
                <i class="fas fa-running"></i>
                <div><strong>Ev. Simulacro</strong></div>
                <div class="count">(<?= $totalSimulacro ?>)</div>
            </a>
            <a href="<?= base_url('/inspecciones/hv-brigadista') ?>" class="card-tipo">
                <i class="fas fa-id-card-alt"></i>
                <div><strong>HV Brigadista</strong></div>
                <div class="count">(<?= $totalHvBrigadista ?>)</div>
            </a>
            <a href="<?= base_url('/inspecciones/preparacion-simulacro') ?>" class="card-tipo">
                <i class="fas fa-clipboard-check"></i>
                <div><strong>Prep. Simulacro</strong></div>
                <div class="count">(<?= $totalPrepSim ?>)</div>
            </a>
        </div>
    </div>

    <!-- Capacitaciones -->
    <div class="section-title">Capacitaciones</div>
    <div class="card mb-3 border-0" style="background:#eefcf0; border-radius:12px; padding:10px 8px 6px;">
        <div class="grid-inspecciones">
            <a href="<?= base_url('/inspecciones/asistencia-induccion') ?>" class="card-tipo">
                <i class="fas fa-clipboard-list"></i>
                <div><strong>Asistencia</strong></div>
                <div class="count">(<?= $totalAsistInd ?>)</div>
            </a>
            <a href="<?= base_url('/inspecciones/reporte-capacitacion') ?>" class="card-tipo">
                <i class="fas fa-chalkboard-teacher"></i>
                <div><strong>Capacitaciones</strong></div>
                <div class="count">(<?= $totalRepCap ?>)</div>
            </a>
            <a href="<?= base_url('/inspecciones/evaluacion-induccion') ?>" class="card-tipo">
                <i class="fas fa-spell-check"></i>
                <div><strong>Evaluaciones</strong></div>
                <div class="count">(<?= $totalEvalInd ?>)</div>
            </a>
        </div>
    </div>

    <!-- Dotaciones -->
    <div class="section-title">Dotaciones</div>
    <div class="card mb-3 border-0" style="background:#fefce8; border-radius:12px; padding:10px 8px 6px;">
        <div class="grid-inspecciones">
            <a href="<?= base_url('/inspecciones/dotacion-aseadora') ?>" class="card-tipo">
                <i class="fas fa-spray-can-sparkles"></i>
                <div><strong>Dot. Aseadora</strong></div>
                <div class="count">(<?= $totalDotAse ?>)</div>
            </a>
            <a href="<?= base_url('/inspecciones/dotacion-todero') ?>" class="card-tipo">
                <i class="fas fa-broom"></i>
                <div><strong>Dot. Todero</strong></div>
                <div class="count">(<?= $totalDotTod ?>)</div>
            </a>
            <a href="<?= base_url('/inspecciones/dotacion-vigilante') ?>" class="card-tipo">
                <i class="fas fa-user-shield"></i>
                <div><strong>Dot. Vigilante</strong></div>
                <div class="count">(<?= $totalDotVig ?>)</div>
            </a>
        </div>
    </div>

    <!-- Datos de enterprisesst -->
    <div class="section-title">Datos de enterprisesst</div>
    <div class="card mb-3 border-0" style="background:#fef0e8; border-radius:12px; padding:10px 8px 6px;">
        <div class="grid-inspecciones">
            <a href="<?= base_url('/inspecciones/mantenimientos') ?>" class="card-tipo">
                <i class="fas fa-wrench"></i>
                <div><strong>Mantenimientos</strong></div>
                <div class="count">(<?= $totalVencimientos ?> pend.)</div>
            </a>
            <a href="<?= base_url('/inspecciones/pendientes') ?>" class="card-tipo">
                <i class="fas fa-tasks"></i>
                <div><strong>Pendientes</strong></div>
                <div class="count">(<?= $totalPendientesAbiertos ?> abiertas)</div>
            </a>
            <a href="<?= base_url('/inspecciones/proveedor-servicio') ?>" class="card-tipo">
                <i class="fas fa-handshake"></i>
                <div><strong>Proveedores</strong></div>
                <div class="count">(<?= $totalProveedores ?>)</div>
            </a>
        </div>
    </div>

    <!-- Mini-universo Saneamiento Básico -->
    <div class="section-title">Saneamiento Básico</div>
    <div class="card mb-3 border-0" style="background: linear-gradient(135deg, #2E7D4F 0%, #1B5E3A 100%); border-radius: 12px; padding: 14px 10px 10px;">
        <div style="text-align:center; margin-bottom:8px;">
            <span style="color:#a8e6cf; font-size:11px; font-weight:600; letter-spacing:0.5px; text-transform:uppercase;">
                <i class="fas fa-leaf me-1"></i>Programas de Saneamiento Básico
            </span>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:8px;">
            <!-- FILA 1: Limpieza y Residuos -->
            <a href="<?= base_url('/inspecciones/limpieza-desinfeccion') ?>" style="padding:12px 6px; margin:0; border-radius:10px; background:rgba(255,255,255,0.12); text-align:center; text-decoration:none; transition:transform .2s;">
                <i class="fas fa-pump-soap" style="font-size:22px; color:#fff; display:block; margin-bottom:2px;"></i>
                <div><strong style="font-size:11px; color:#fff;">Limpieza y Des.</strong></div>
                <div style="font-size:11px; color:rgba(255,255,255,0.6);">(<?= $totalProgLimp ?>)</div>
            </a>
            <a href="<?= base_url('/inspecciones/residuos-solidos') ?>" style="padding:12px 6px; margin:0; border-radius:10px; background:rgba(255,255,255,0.12); text-align:center; text-decoration:none; transition:transform .2s;">
                <i class="fas fa-recycle" style="font-size:22px; color:#fff; display:block; margin-bottom:2px;"></i>
                <div><strong style="font-size:11px; color:#fff;">Residuos Sólidos</strong></div>
                <div style="font-size:11px; color:rgba(255,255,255,0.6);">(<?= $totalProgRes ?>)</div>
            </a>
            <a href="<?= base_url('/inspecciones/auditoria-zona-residuos') ?>" style="padding:12px 6px; margin:0; border-radius:10px; background:rgba(255,255,255,0.12); text-align:center; text-decoration:none; transition:transform .2s;">
                <i class="fas fa-dumpster" style="font-size:22px; color:#fff; display:block; margin-bottom:2px;"></i>
                <div><strong style="font-size:11px; color:#fff;">Zona Residuos</strong></div>
                <div style="font-size:11px; color:rgba(255,255,255,0.6);">(<?= $totalAudRes ?>)</div>
            </a>
            <!-- FILA 2: Agua -->
            <a href="<?= base_url('/inspecciones/agua-potable') ?>" style="padding:12px 6px; margin:0; border-radius:10px; background:rgba(255,255,255,0.12); text-align:center; text-decoration:none; transition:transform .2s;">
                <i class="fas fa-tint" style="font-size:22px; color:#fff; display:block; margin-bottom:2px;"></i>
                <div><strong style="font-size:11px; color:#fff;">Agua Potable</strong></div>
                <div style="font-size:11px; color:rgba(255,255,255,0.6);">(<?= $totalProgAgua ?>)</div>
            </a>
            <a href="<?= base_url('/inspecciones/lavado-tanques') ?>" style="padding:12px 6px; margin:0; border-radius:10px; background:rgba(255,255,255,0.12); text-align:center; text-decoration:none; transition:transform .2s;">
                <i class="fas fa-water" style="font-size:22px; color:#fff; display:block; margin-bottom:2px;"></i>
                <div><strong style="font-size:11px; color:#fff;">Lavado Tanques</strong></div>
                <div style="font-size:11px; color:rgba(255,255,255,0.6);">(<?= $totalLavadoTanques ?>)</div>
            </a>
            <a href="<?= base_url('/inspecciones/contingencia-agua') ?>" style="padding:12px 6px; margin:0; border-radius:10px; background:rgba(255,255,255,0.12); text-align:center; text-decoration:none; transition:transform .2s;">
                <i class="fas fa-tint-slash" style="font-size:22px; color:#fff; display:block; margin-bottom:2px;"></i>
                <div><strong style="font-size:11px; color:#fff;">Cont. Sin Agua</strong></div>
                <div style="font-size:11px; color:rgba(255,255,255,0.6);">(<?= $totalContAgua ?>)</div>
            </a>
            <!-- FILA 3: Plagas -->
            <a href="<?= base_url('/inspecciones/control-plagas') ?>" style="padding:12px 6px; margin:0; border-radius:10px; background:rgba(255,255,255,0.12); text-align:center; text-decoration:none; transition:transform .2s;">
                <i class="fas fa-bug" style="font-size:22px; color:#fff; display:block; margin-bottom:2px;"></i>
                <div><strong style="font-size:11px; color:#fff;">Control Plagas</strong></div>
                <div style="font-size:11px; color:rgba(255,255,255,0.6);">(<?= $totalProgPlag ?>)</div>
            </a>
            <a href="<?= base_url('/inspecciones/fumigacion') ?>" style="padding:12px 6px; margin:0; border-radius:10px; background:rgba(255,255,255,0.12); text-align:center; text-decoration:none; transition:transform .2s;">
                <i class="fas fa-bug" style="font-size:22px; color:#fff; display:block; margin-bottom:2px;"></i>
                <div><strong style="font-size:11px; color:#fff;">Fumigación</strong></div>
                <div style="font-size:11px; color:rgba(255,255,255,0.6);">(<?= $totalFumigacion ?>)</div>
            </a>
            <a href="<?= base_url('/inspecciones/desratizacion') ?>" style="padding:12px 6px; margin:0; border-radius:10px; background:rgba(255,255,255,0.12); text-align:center; text-decoration:none; transition:transform .2s;">
                <i class="fas fa-mouse" style="font-size:22px; color:#fff; display:block; margin-bottom:2px;"></i>
                <div><strong style="font-size:11px; color:#fff;">Desratización</strong></div>
                <div style="font-size:11px; color:rgba(255,255,255,0.6);">(<?= $totalDesratizacion ?>)</div>
            </a>
            <!-- FILA 4: Contingencias -->
            <a href="<?= base_url('/inspecciones/contingencia-plagas') ?>" style="padding:12px 6px; margin:0; border-radius:10px; background:rgba(255,255,255,0.12); text-align:center; text-decoration:none; transition:transform .2s;">
                <i class="fas fa-bug" style="font-size:22px; color:#fff; display:block; margin-bottom:2px;"></i>
                <div><strong style="font-size:11px; color:#fff;">Cont. Plagas</strong></div>
                <div style="font-size:11px; color:rgba(255,255,255,0.6);">(<?= $totalContPlagas ?>)</div>
            </a>
            <a href="<?= base_url('/inspecciones/contingencia-basura') ?>" style="padding:12px 6px; margin:0; border-radius:10px; background:rgba(255,255,255,0.12); text-align:center; text-decoration:none; transition:transform .2s;">
                <i class="fas fa-trash-alt" style="font-size:22px; color:#fff; display:block; margin-bottom:2px;"></i>
                <div><strong style="font-size:11px; color:#fff;">Cont. Basura</strong></div>
                <div style="font-size:11px; color:rgba(255,255,255,0.6);">(<?= $totalContBasura ?>)</div>
            </a>
            <a href="<?= base_url('/inspecciones/plan-saneamiento') ?>" style="padding:12px 6px; margin:0; border-radius:10px; background:rgba(255,255,255,0.12); text-align:center; text-decoration:none; transition:transform .2s;">
                <i class="fas fa-shield-alt" style="font-size:22px; color:#fff; display:block; margin-bottom:2px;"></i>
                <div><strong style="font-size:11px; color:#fff;">Plan Saneamiento</strong></div>
                <div style="font-size:11px; color:rgba(255,255,255,0.6);">(<?= $totalPlanSan ?>)</div>
            </a>
            <!-- FILA 5: KPIs -->
            <a href="<?= base_url('/inspecciones/kpi-limpieza') ?>" style="padding:12px 6px; margin:0; border-radius:10px; background:rgba(255,255,255,0.12); text-align:center; text-decoration:none; transition:transform .2s;">
                <i class="fas fa-chart-line" style="font-size:22px; color:#fff; display:block; margin-bottom:2px;"></i>
                <div><strong style="font-size:11px; color:#fff;">KPI Limpieza</strong></div>
                <div style="font-size:11px; color:rgba(255,255,255,0.6);">(<?= $totalKpiLimp ?>)</div>
            </a>
            <a href="<?= base_url('/inspecciones/kpi-residuos') ?>" style="padding:12px 6px; margin:0; border-radius:10px; background:rgba(255,255,255,0.12); text-align:center; text-decoration:none; transition:transform .2s;">
                <i class="fas fa-chart-bar" style="font-size:22px; color:#fff; display:block; margin-bottom:2px;"></i>
                <div><strong style="font-size:11px; color:#fff;">KPI Residuos</strong></div>
                <div style="font-size:11px; color:rgba(255,255,255,0.6);">(<?= $totalKpiRes ?>)</div>
            </a>
            <a href="<?= base_url('/inspecciones/kpi-plagas') ?>" style="padding:12px 6px; margin:0; border-radius:10px; background:rgba(255,255,255,0.12); text-align:center; text-decoration:none; transition:transform .2s;">
                <i class="fas fa-chart-pie" style="font-size:22px; color:#fff; display:block; margin-bottom:2px;"></i>
                <div><strong style="font-size:11px; color:#fff;">KPI Plagas</strong></div>
                <div style="font-size:11px; color:rgba(255,255,255,0.6);">(<?= $totalKpiPlag ?>)</div>
            </a>
            <!-- FILA 6: KPI Agua + Dashboard Consolidado -->
            <a href="<?= base_url('/inspecciones/kpi-agua-potable') ?>" style="padding:12px 6px; margin:0; border-radius:10px; background:rgba(255,255,255,0.12); text-align:center; text-decoration:none; transition:transform .2s;">
                <i class="fas fa-chart-area" style="font-size:22px; color:#fff; display:block; margin-bottom:2px;"></i>
                <div><strong style="font-size:11px; color:#fff;">KPI Agua Potable</strong></div>
                <div style="font-size:11px; color:rgba(255,255,255,0.6);">(<?= $totalKpiAgua ?>)</div>
            </a>
            <a href="<?= base_url('/inspecciones/dashboard-saneamiento') ?>" style="padding:12px 6px; margin:0; border-radius:10px; background:rgba(255,255,255,0.18); text-align:center; text-decoration:none; border:2px solid #a8e6cf; transition:transform .2s; grid-column: span 2;">
                <i class="fas fa-clipboard-check" style="font-size:22px; color:#a8e6cf; display:block; margin-bottom:2px;"></i>
                <div><strong style="font-size:11px; color:#a8e6cf;">Dashboard Saneamiento</strong></div>
                <div style="font-size:11px; color:rgba(255,255,255,0.6);">Consolidado KPIs</div>
            </a>
        </div>
    </div>
</div>

<script>
function confirmarEliminar(url) {
    var ops = ['+', '-', 'x'];
    var op = ops[Math.floor(Math.random() * ops.length)];
    var a, b, respuesta;
    if (op === '+') {
        a = Math.floor(Math.random() * 20) + 1;
        b = Math.floor(Math.random() * 20) + 1;
        respuesta = a + b;
    } else if (op === '-') {
        a = Math.floor(Math.random() * 20) + 10;
        b = Math.floor(Math.random() * a);
        respuesta = a - b;
    } else {
        a = Math.floor(Math.random() * 9) + 2;
        b = Math.floor(Math.random() * 9) + 2;
        respuesta = a * b;
    }

    Swal.fire({
        title: 'Eliminar registro',
        html: '<p style="color:#666;font-size:14px;">Esta accion no se puede deshacer.<br>Para confirmar, resuelve la operacion:</p>' +
              '<div style="font-size:24px;font-weight:700;color:#1c2437;margin:10px 0;">' + a + ' ' + op + ' ' + b + ' = ?</div>',
        input: 'number',
        inputPlaceholder: 'Tu respuesta',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Eliminar',
        cancelButtonText: 'Cancelar',
        inputValidator: function(value) {
            if (!value && value !== '0') return 'Debes ingresar un numero';
            if (parseInt(value) !== respuesta) return 'Respuesta incorrecta. Intenta de nuevo.';
        }
    }).then(function(result) {
        if (!result.isConfirmed) return;
        // Segunda validación
        var a2, b2, resp2;
        var op2 = ops[Math.floor(Math.random() * ops.length)];
        if (op2 === '+') {
            a2 = Math.floor(Math.random() * 20) + 1;
            b2 = Math.floor(Math.random() * 20) + 1;
            resp2 = a2 + b2;
        } else if (op2 === '-') {
            a2 = Math.floor(Math.random() * 20) + 10;
            b2 = Math.floor(Math.random() * a2);
            resp2 = a2 - b2;
        } else {
            a2 = Math.floor(Math.random() * 9) + 2;
            b2 = Math.floor(Math.random() * 9) + 2;
            resp2 = a2 * b2;
        }

        Swal.fire({
            title: 'Confirmar eliminacion',
            html: '<p style="color:#dc3545;font-size:14px;font-weight:600;">Segunda verificacion</p>' +
                  '<div style="font-size:24px;font-weight:700;color:#1c2437;margin:10px 0;">' + a2 + ' ' + op2 + ' ' + b2 + ' = ?</div>',
            input: 'number',
            inputPlaceholder: 'Tu respuesta',
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Confirmar eliminacion',
            cancelButtonText: 'Cancelar',
            inputValidator: function(value) {
                if (!value && value !== '0') return 'Debes ingresar un numero';
                if (parseInt(value) !== resp2) return 'Respuesta incorrecta. Intenta de nuevo.';
            }
        }).then(function(result2) {
            if (result2.isConfirmed) {
                window.location.href = url;
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    var input = document.getElementById('buscarInspeccion');
    if (!input) return;
    input.addEventListener('input', function() {
        var term = this.value.toLowerCase().trim();
        document.querySelectorAll('.grid-inspecciones .card-tipo').forEach(function(card) {
            var text = card.textContent.toLowerCase();
            card.style.display = (!term || text.indexOf(term) !== -1) ? '' : 'none';
        });
    });
});
</script>
