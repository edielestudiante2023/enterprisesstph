<div class="container-fluid px-3">
    <!-- Saludo -->
    <div class="mt-2 mb-3">
        <h5 class="mb-0">Hola, <?= esc($nombre) ?></h5>
        <small class="text-muted"><?= date('d \d\e F, Y') ?></small>
    </div>

    <!-- Documentos pendientes -->
    <?php if (!empty($pendientes)): ?>
    <div class="section-title">Pendientes</div>
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
            <div class="mt-2">
                <?php if ($doc['estado'] === 'borrador'): ?>
                    <a href="/inspecciones/acta-visita/edit/<?= $doc['id'] ?>" class="btn btn-sm btn-outline-dark">
                        Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                <?php else: ?>
                    <a href="/inspecciones/acta-visita/firma/<?= $doc['id'] ?>" class="btn btn-sm btn-outline-warning">
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
            <div class="mt-2">
                <a href="/inspecciones/inspeccion-locativa/edit/<?= $loc['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
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
            <div class="mt-2">
                <a href="/inspecciones/senalizacion/edit/<?= $sen['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
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
            <div class="mt-2">
                <a href="/inspecciones/extintores/edit/<?= $ext['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
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
            <div class="mt-2">
                <a href="/inspecciones/botiquin/edit/<?= $bot['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
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
            <div class="mt-2">
                <a href="/inspecciones/gabinetes/edit/<?= $gab['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
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
            <div class="mt-2">
                <a href="/inspecciones/comunicaciones/edit/<?= $com['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
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
            <div class="mt-2">
                <a href="/inspecciones/recursos-seguridad/edit/<?= $rec['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
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
            <div class="mt-2">
                <a href="/inspecciones/probabilidad-peligros/edit/<?= $pp['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
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
            <div class="mt-2">
                <a href="/inspecciones/matriz-vulnerabilidad/edit/<?= $mv['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
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
            <div class="mt-2">
                <a href="/inspecciones/plan-emergencia/edit/<?= $pe['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
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
            <div class="mt-2">
                <a href="/inspecciones/simulacro/view/<?= $sim['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Ver <i class="fas fa-eye ms-1"></i>
                </a>
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
            <div class="mt-2">
                <a href="/inspecciones/hv-brigadista/view/<?= $hvb['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Ver <i class="fas fa-eye ms-1"></i>
                </a>
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
            <div class="mt-2">
                <a href="/inspecciones/dotacion-vigilante/edit/<?= $dv['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
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
            <div class="mt-2">
                <a href="/inspecciones/dotacion-aseadora/edit/<?= $da['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
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
            <div class="mt-2">
                <a href="/inspecciones/dotacion-todero/edit/<?= $dt['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
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
            <div class="mt-2">
                <a href="/inspecciones/auditoria-zona-residuos/edit/<?= $ar['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
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
            <div class="mt-2">
                <a href="/inspecciones/reporte-capacitacion/edit/<?= $rc['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
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
            <div class="mt-2">
                <a href="/inspecciones/preparacion-simulacro/edit/<?= $ps['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
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
            <div class="mt-2">
                <a href="/inspecciones/asistencia-induccion/edit/<?= $ai['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
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
            <div class="mt-2">
                <a href="/inspecciones/residuos-solidos/edit/<?= $pr['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
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
            <div class="mt-2">
                <a href="/inspecciones/control-plagas/edit/<?= $pp['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
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
            <div class="mt-2">
                <a href="/inspecciones/limpieza-desinfeccion/edit/<?= $pl['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
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
            <div class="mt-2">
                <a href="/inspecciones/agua-potable/edit/<?= $pa['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
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
            <div class="mt-2">
                <a href="/inspecciones/plan-saneamiento/edit/<?= $ps['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
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
            <div class="mt-2">
                <a href="/inspecciones/kpi-limpieza/edit/<?= $pk['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
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
            <div class="mt-2">
                <a href="/inspecciones/kpi-residuos/edit/<?= $pk['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
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
            <div class="mt-2">
                <a href="/inspecciones/kpi-plagas/edit/<?= $pk['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
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
            <div class="mt-2">
                <a href="/inspecciones/kpi-agua-potable/edit/<?= $pk['id'] ?>" class="btn btn-sm btn-outline-dark">
                    Continuar editando <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
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
    <a href="/inspecciones/agendamiento" class="card mb-3 border-0" style="background: linear-gradient(135deg, #1c2437, #2c3e50); border-radius: 12px; text-decoration:none;">
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

    <!-- Grid de inspecciones -->
    <div class="section-title">Inspecciones</div>
    <div class="grid-inspecciones mb-4">
        <a href="/inspecciones/acta-visita" class="card-tipo">
            <i class="fas fa-clipboard-list"></i>
            <div><strong>Actas de Visita</strong></div>
            <div class="count">(<?= $totalActas ?>)</div>
        </a>
        <a href="/inspecciones/senalizacion" class="card-tipo">
            <i class="fas fa-search"></i>
            <div><strong>Senalizacion</strong></div>
            <div class="count">(<?= $totalSenalizacion ?>)</div>
        </a>
        <a href="/inspecciones/inspeccion-locativa" class="card-tipo">
            <i class="fas fa-hard-hat"></i>
            <div><strong>Locativas</strong></div>
            <div class="count">(<?= $totalLocativas ?>)</div>
        </a>
        <a href="/inspecciones/extintores" class="card-tipo">
            <i class="fas fa-fire-extinguisher"></i>
            <div><strong>Extintores</strong></div>
            <div class="count">(<?= $totalExtintores ?>)</div>
        </a>
        <a href="/inspecciones/botiquin" class="card-tipo">
            <i class="fas fa-first-aid"></i>
            <div><strong>Botiquin</strong></div>
            <div class="count">(<?= $totalBotiquin ?>)</div>
        </a>
        <a href="/inspecciones/gabinetes" class="card-tipo">
            <i class="fas fa-shower"></i>
            <div><strong>Gabinetes</strong></div>
            <div class="count">(<?= $totalGabinetes ?>)</div>
        </a>
        <a href="/inspecciones/comunicaciones" class="card-tipo">
            <i class="fas fa-walkie-talkie"></i>
            <div><strong>Comunicaciones</strong></div>
            <div class="count">(<?= $totalComunicaciones ?>)</div>
        </a>
        <a href="/inspecciones/recursos-seguridad" class="card-tipo">
            <i class="fas fa-shield-alt"></i>
            <div><strong>Rec. Seguridad</strong></div>
            <div class="count">(<?= $totalRecursosSeg ?>)</div>
        </a>
        <a href="/inspecciones/probabilidad-peligros" class="card-tipo">
            <i class="fas fa-exclamation-triangle"></i>
            <div><strong>Prob. Peligros</strong></div>
            <div class="count">(<?= $totalProbPeligros ?>)</div>
        </a>
        <a href="/inspecciones/matriz-vulnerabilidad" class="card-tipo">
            <i class="fas fa-th-list"></i>
            <div><strong>Matriz Vuln.</strong></div>
            <div class="count">(<?= $totalMatrizVul ?>)</div>
        </a>
        <a href="/inspecciones/plan-emergencia" class="card-tipo">
            <i class="fas fa-file-medical"></i>
            <div><strong>Plan Emergencia</strong></div>
            <div class="count">(<?= $totalPlanEmergencia ?>)</div>
        </a>
        <a href="/inspecciones/simulacro" class="card-tipo">
            <i class="fas fa-running"></i>
            <div><strong>Ev. Simulacro</strong></div>
            <div class="count">(<?= $totalSimulacro ?>)</div>
        </a>
        <a href="/inspecciones/hv-brigadista" class="card-tipo">
            <i class="fas fa-id-card-alt"></i>
            <div><strong>HV Brigadista</strong></div>
            <div class="count">(<?= $totalHvBrigadista ?>)</div>
        </a>
        <a href="/inspecciones/dotacion-vigilante" class="card-tipo">
            <i class="fas fa-user-shield"></i>
            <div><strong>Dot. Vigilante</strong></div>
            <div class="count">(<?= $totalDotVig ?>)</div>
        </a>
        <a href="/inspecciones/dotacion-aseadora" class="card-tipo">
            <i class="fas fa-spray-can-sparkles"></i>
            <div><strong>Dot. Aseadora</strong></div>
            <div class="count">(<?= $totalDotAse ?>)</div>
        </a>
        <a href="/inspecciones/dotacion-todero" class="card-tipo">
            <i class="fas fa-broom"></i>
            <div><strong>Dot. Todero</strong></div>
            <div class="count">(<?= $totalDotTod ?>)</div>
        </a>
        <a href="/inspecciones/auditoria-zona-residuos" class="card-tipo">
            <i class="fas fa-dumpster"></i>
            <div><strong>Zona Residuos</strong></div>
            <div class="count">(<?= $totalAudRes ?>)</div>
        </a>
        <a href="/inspecciones/reporte-capacitacion" class="card-tipo">
            <i class="fas fa-chalkboard-teacher"></i>
            <div><strong>Capacitaciones</strong></div>
            <div class="count">(<?= $totalRepCap ?>)</div>
        </a>
        <a href="/inspecciones/preparacion-simulacro" class="card-tipo">
            <i class="fas fa-clipboard-check"></i>
            <div><strong>Prep. Simulacro</strong></div>
            <div class="count">(<?= $totalPrepSim ?>)</div>
        </a>
        <a href="/inspecciones/asistencia-induccion" class="card-tipo">
            <i class="fas fa-clipboard-list"></i>
            <div><strong>Asistencia</strong></div>
            <div class="count">(<?= $totalAsistInd ?>)</div>
        </a>
        <a href="/inspecciones/limpieza-desinfeccion" class="card-tipo">
            <i class="fas fa-pump-soap"></i>
            <div><strong>Limpieza y Des.</strong></div>
            <div class="count">(<?= $totalProgLimp ?>)</div>
        </a>
        <a href="/inspecciones/residuos-solidos" class="card-tipo">
            <i class="fas fa-recycle"></i>
            <div><strong>Residuos Sólidos</strong></div>
            <div class="count">(<?= $totalProgRes ?>)</div>
        </a>
        <a href="/inspecciones/control-plagas" class="card-tipo">
            <i class="fas fa-bug"></i>
            <div><strong>Control Plagas</strong></div>
            <div class="count">(<?= $totalProgPlag ?>)</div>
        </a>
        <a href="/inspecciones/agua-potable" class="card-tipo">
            <i class="fas fa-tint"></i>
            <div><strong>Agua Potable</strong></div>
            <div class="count">(<?= $totalProgAgua ?>)</div>
        </a>
        <a href="/inspecciones/plan-saneamiento" class="card-tipo">
            <i class="fas fa-shield-alt"></i>
            <div><strong>Plan Saneamiento</strong></div>
            <div class="count">(<?= $totalPlanSan ?>)</div>
        </a>
        <a href="/inspecciones/kpi-limpieza" class="card-tipo">
            <i class="fas fa-chart-line"></i>
            <div><strong>KPI Limpieza</strong></div>
            <div class="count">(<?= $totalKpiLimp ?>)</div>
        </a>
        <a href="/inspecciones/kpi-residuos" class="card-tipo">
            <i class="fas fa-chart-bar"></i>
            <div><strong>KPI Residuos</strong></div>
            <div class="count">(<?= $totalKpiRes ?>)</div>
        </a>
        <a href="/inspecciones/kpi-plagas" class="card-tipo">
            <i class="fas fa-chart-pie"></i>
            <div><strong>KPI Plagas</strong></div>
            <div class="count">(<?= $totalKpiPlag ?>)</div>
        </a>
        <a href="/inspecciones/kpi-agua-potable" class="card-tipo">
            <i class="fas fa-chart-area"></i>
            <div><strong>KPI Agua Potable</strong></div>
            <div class="count">(<?= $totalKpiAgua ?>)</div>
        </a>
        <a href="/inspecciones/carta-vigia" class="card-tipo">
            <i class="fas fa-user-shield"></i>
            <div><strong>Carta Vigia</strong></div>
            <div class="count">(<?= $totalCartasVigiaPend ?> pend.)</div>
        </a>
        <a href="/inspecciones/mantenimientos" class="card-tipo">
            <i class="fas fa-wrench"></i>
            <div><strong>Mantenimientos</strong></div>
            <div class="count">(<?= $totalVencimientos ?> pend.)</div>
        </a>
        <a href="/inspecciones/pendientes" class="card-tipo">
            <i class="fas fa-tasks"></i>
            <div><strong>Pendientes</strong></div>
            <div class="count">(<?= $totalPendientesAbiertos ?> abiertas)</div>
        </a>
        <a href="/inspecciones/urls" class="card-tipo">
            <i class="fas fa-link"></i>
            <div><strong>Accesos Rápidos</strong></div>
            <div class="count">URLs</div>
        </a>
    </div>
</div>

<script>
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
