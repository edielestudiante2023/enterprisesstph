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
    </div>
</div>
