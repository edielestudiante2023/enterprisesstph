<?php
$mapSiNoParcial = ['si' => 'Sí', 'no' => 'No', 'parcial' => 'Parcial'];
$mapSiNo        = ['si' => 'Sí', 'no' => 'No'];
$mapTipoSim     = [
    'no_realizado' => 'No realizado',
    'escritorio'   => 'De escritorio',
    'parcial'      => 'Parcial',
    'general'      => 'General',
];

function brig_cls_siNoParcial(string $v): string {
    return $v === 'si' ? 'text-success' : ($v === 'parcial' ? 'text-warning' : 'text-danger');
}
function brig_cls_siNo(string $v): string {
    return $v === 'si' ? 'text-success' : 'text-danger';
}

$todasFotos = [
    'foto_brigada_1'      => 'Foto brigada 1',
    'foto_brigada_2'      => 'Foto brigada 2',
    'foto_dotacion'       => 'Foto dotación',
    'foto_acta_simulacro' => 'Foto acta simulacro',
];
$hayFotos = false;
foreach (array_keys($todasFotos) as $c) { if (!empty($inspeccion[$c])) { $hayFotos = true; break; } }
?>
<div class="container-fluid px-3">
    <div class="mt-2 mb-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Inspección de Brigada y Simulacros</h6>
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
                <tr><td class="text-muted">Fecha inspección</td><td><?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td></tr>
                <tr><td class="text-muted">Consultor</td><td><?= esc($consultor['nombre_consultor'] ?? '') ?></td></tr>
            </table>
        </div>
    </div>

    <!-- Estado actual -->
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">ESTADO ACTUAL DE LA BRIGADA</h6>
            <table class="table table-sm mb-0" style="font-size:13px;">
                <tr>
                    <td class="text-muted" style="width:50%;">¿Existe brigada?</td>
                    <td><strong class="<?= brig_cls_siNoParcial($inspeccion['existe_brigada'] ?? 'no') ?>"><?= esc($mapSiNoParcial[$inspeccion['existe_brigada'] ?? 'no'] ?? 'No') ?></strong></td>
                </tr>
                <?php if (!empty($inspeccion['fecha_conformacion'])): ?>
                <tr><td class="text-muted">Fecha conformación</td><td><?= date('d/m/Y', strtotime($inspeccion['fecha_conformacion'])) ?></td></tr>
                <?php endif; ?>
                <tr><td class="text-muted">Número brigadistas</td><td><?= (int)($inspeccion['numero_brigadistas'] ?? 0) ?></td></tr>
                <?php if (!empty($inspeccion['nombre_jefe_brigada'])): ?>
                <tr><td class="text-muted">Jefe de brigada</td><td><?= esc($inspeccion['nombre_jefe_brigada']) ?></td></tr>
                <?php endif; ?>
                <tr>
                    <td class="text-muted">¿Brigada capacitada?</td>
                    <td><strong class="<?= brig_cls_siNoParcial($inspeccion['brigada_capacitada'] ?? 'no') ?>"><?= esc($mapSiNoParcial[$inspeccion['brigada_capacitada'] ?? 'no'] ?? 'No') ?></strong></td>
                </tr>
                <tr>
                    <td class="text-muted">¿Cuenta con dotación?</td>
                    <td><strong class="<?= brig_cls_siNoParcial($inspeccion['cuenta_dotacion'] ?? 'no') ?>"><?= esc($mapSiNoParcial[$inspeccion['cuenta_dotacion'] ?? 'no'] ?? 'No') ?></strong></td>
                </tr>
                <?php if (!empty($inspeccion['detalle_dotacion'])): ?>
                <tr><td class="text-muted">Detalle dotación</td><td><?= nl2br(esc($inspeccion['detalle_dotacion'])) ?></td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <!-- Fotos -->
    <?php if ($hayFotos): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">FOTOS</h6>
            <div class="row g-2">
                <?php foreach ($todasFotos as $campo => $lbl): ?>
                    <?php if (!empty($inspeccion[$campo])): ?>
                    <div class="col-6">
                        <small class="text-muted d-block" style="font-size:11px;"><?= $lbl ?></small>
                        <img src="/<?= esc($inspeccion[$campo]) ?>" class="img-fluid rounded"
                             style="max-height:140px; object-fit:cover; cursor:pointer; border:1px solid #ddd;"
                             onclick="openPhoto(this.src)">
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Capacitaciones -->
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">CAPACITACIONES</h6>
            <table class="table table-sm mb-0" style="font-size:13px;">
                <?php
                $capFields = [
                    'capacitacion_primeros_auxilios' => 'Primeros auxilios',
                    'capacitacion_extintores'        => 'Manejo de extintores',
                    'capacitacion_evacuacion'        => 'Evacuación',
                    'capacitacion_busqueda_rescate'  => 'Búsqueda y rescate',
                    'capacitacion_comunicaciones'    => 'Comunicaciones',
                ];
                foreach ($capFields as $campo => $lbl):
                    $val = $inspeccion[$campo] ?? 'no';
                ?>
                <tr>
                    <td class="text-muted" style="width:60%;"><?= $lbl ?></td>
                    <td><strong class="<?= brig_cls_siNo($val) ?>"><?= esc($mapSiNo[$val] ?? 'No') ?></strong></td>
                </tr>
                <?php endforeach; ?>
                <?php if (!empty($inspeccion['fecha_ultima_capacitacion'])): ?>
                <tr><td class="text-muted">Última capacitación</td><td><?= date('d/m/Y', strtotime($inspeccion['fecha_ultima_capacitacion'])) ?></td></tr>
                <?php endif; ?>
            </table>
            <?php if (!empty($inspeccion['capacitaciones_12m'])): ?>
            <div class="mt-2" style="font-size:13px;">
                <strong class="text-muted">Capacitaciones 12 meses:</strong><br>
                <?= nl2br(esc($inspeccion['capacitaciones_12m'])) ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Simulacros -->
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">SIMULACROS</h6>
            <table class="table table-sm mb-0" style="font-size:13px;">
                <?php if (!empty($inspeccion['fecha_ultimo_simulacro'])): ?>
                <tr><td class="text-muted" style="width:55%;">Fecha último simulacro</td><td><?= date('d/m/Y', strtotime($inspeccion['fecha_ultimo_simulacro'])) ?></td></tr>
                <?php endif; ?>
                <tr><td class="text-muted">Tipo simulacro</td><td><?= esc($mapTipoSim[$inspeccion['tipo_simulacro'] ?? 'no_realizado'] ?? 'No realizado') ?></td></tr>
                <tr>
                    <td class="text-muted">Participó en simulacro nacional</td>
                    <td><strong class="<?= brig_cls_siNo($inspeccion['participo_simulacro_nacional'] ?? 'no') ?>"><?= esc($mapSiNo[$inspeccion['participo_simulacro_nacional'] ?? 'no'] ?? 'No') ?></strong></td>
                </tr>
                <tr><td class="text-muted">Cantidad simulacros 12m</td><td><?= (int)($inspeccion['cantidad_simulacros_12m'] ?? 0) ?></td></tr>
            </table>
        </div>
    </div>

    <!-- Hallazgos -->
    <?php
    $hallazgos = [
        'fortalezas'      => 'Fortalezas',
        'debilidades'     => 'Debilidades',
        'recomendaciones' => 'Recomendaciones',
        'observaciones'   => 'Observaciones',
    ];
    $hayHall = false;
    foreach (array_keys($hallazgos) as $c) { if (!empty($inspeccion[$c])) { $hayHall = true; break; } }
    ?>
    <?php if ($hayHall): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:14px; color:#999;">HALLAZGOS Y RECOMENDACIONES</h6>
            <?php foreach ($hallazgos as $campo => $lbl): ?>
                <?php if (!empty($inspeccion[$campo])): ?>
                <div class="mb-2" style="font-size:13px;">
                    <strong class="text-muted"><?= $lbl ?>:</strong><br>
                    <?= nl2br(esc($inspeccion[$campo])) ?>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Botones -->
    <div class="d-flex gap-2 mt-3 mb-4 flex-wrap">
        <?php if ($inspeccion['estado'] !== 'completo'): ?>
            <a href="<?= base_url('/inspecciones/brigada-simulacros/edit/'.$inspeccion['id']) ?>" class="btn btn-pwa-primary flex-grow-1"><i class="fas fa-edit"></i> Editar</a>
            <form method="post" action="<?= base_url('/inspecciones/brigada-simulacros/finalizar/'.$inspeccion['id']) ?>" class="flex-grow-1 m-0" id="frmFinal">
                <?= csrf_field() ?>
                <button type="button" id="btnFinalizarView" class="btn btn-success w-100"><i class="fas fa-check-double"></i> Finalizar</button>
            </form>
        <?php else: ?>
            <a href="<?= base_url('/inspecciones/brigada-simulacros/pdf/'.$inspeccion['id']) ?>" target="_blank" class="btn btn-outline-success flex-grow-1"><i class="fas fa-file-pdf"></i> Generar PDF</a>
        <?php endif; ?>
        <a href="<?= base_url('/inspecciones/brigada-simulacros') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Volver</a>
        <button type="button" id="btnDel" class="btn btn-outline-danger"><i class="fas fa-trash"></i></button>
    </div>

    <!-- Modal foto -->
    <div class="modal fade" id="photoModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-body p-2 text-center">
            <img id="photoModalImg" src="" class="img-fluid">
        </div></div></div>
    </div>
</div>

<script>
function openPhoto(src) {
    document.getElementById('photoModalImg').src = src;
    new bootstrap.Modal(document.getElementById('photoModal')).show();
}

document.addEventListener('DOMContentLoaded', function() {
    const btnFin = document.getElementById('btnFinalizarView');
    if (btnFin) {
        btnFin.addEventListener('click', function() {
            Swal.fire({
                title: '¿Finalizar inspección?',
                html: 'Se generará el PDF y no podrás editar después.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, finalizar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#bd9751',
            }).then(result => {
                if (result.isConfirmed) {
                    document.getElementById('frmFinal').submit();
                }
            });
        });
    }

    document.getElementById('btnDel').addEventListener('click', function() {
        Swal.fire({
            title: 'Eliminar inspección',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545',
        }).then(result => {
            if (result.isConfirmed) {
                window.location.href = '<?= base_url('/inspecciones/brigada-simulacros/delete/'.$inspeccion['id']) ?>';
            }
        });
    });
});
</script>
