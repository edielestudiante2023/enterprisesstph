<?php
$isEdit = !empty($inspeccion);
$action = $isEdit
    ? base_url('/inspecciones/brigada-simulacros/update/') . $inspeccion['id']
    : base_url('/inspecciones/brigada-simulacros/store');

$existeOps = ['si' => 'Sí', 'parcial' => 'Parcial', 'no' => 'No'];
$siNoOps   = ['si' => 'Sí', 'no' => 'No'];
$tiposSim  = [
    'no_realizado' => 'No realizado',
    'escritorio'   => 'De escritorio',
    'parcial'      => 'Parcial',
    'general'      => 'General',
];

$fotoLabels = [
    'foto_brigada_1'      => 'Foto brigada 1',
    'foto_brigada_2'      => 'Foto brigada 2',
    'foto_dotacion'       => 'Foto dotación',
    'foto_acta_simulacro' => 'Foto acta simulacro',
];

function brig_sel(array $ops, ?string $val, string $default = 'no'): string {
    $v = $val ?: $default;
    $out = '';
    foreach ($ops as $k => $lbl) {
        $sel = ($v === $k) ? ' selected' : '';
        $out .= '<option value="' . $k . '"' . $sel . '>' . esc($lbl) . '</option>';
    }
    return $out;
}
?>

<div class="container-fluid px-3">
    <form method="post" action="<?= $action ?>" enctype="multipart/form-data" id="brigForm">
        <?= csrf_field() ?>

        <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger mt-2" style="font-size:14px;">
            <ul class="mb-0">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('msg')): ?>
        <div class="alert alert-success mt-2" style="font-size:14px;">
            <?= session()->getFlashdata('msg') ?>
        </div>
        <?php endif; ?>

        <div class="accordion mt-2" id="accordionBrig">

            <!-- DATOS GENERALES -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#secDatos">
                        Datos Generales
                    </button>
                </h2>
                <div id="secDatos" class="accordion-collapse collapse show" data-bs-parent="#accordionBrig">
                    <div class="accordion-body">
                        <div class="mb-3">
                            <label class="form-label">Cliente *</label>
                            <select name="id_cliente" id="selectCliente" class="form-select" required>
                                <option value="">Seleccionar cliente...</option>
                            </select>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-12">
                                <label class="form-label">Fecha inspección *</label>
                                <input type="date" name="fecha_inspeccion" class="form-control"
                                    value="<?= esc($inspeccion['fecha_inspeccion'] ?? date('Y-m-d')) ?>" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ESTADO ACTUAL DE LA BRIGADA -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secBrigada">
                        Estado actual de la brigada
                    </button>
                </h2>
                <div id="secBrigada" class="accordion-collapse collapse" data-bs-parent="#accordionBrig">
                    <div class="accordion-body">
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label" style="font-size:12px;">¿Existe brigada?</label>
                                <select name="existe_brigada" class="form-select form-select-sm">
                                    <?= brig_sel($existeOps, $inspeccion['existe_brigada'] ?? null, 'no') ?>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label" style="font-size:12px;">Fecha conformación</label>
                                <input type="date" name="fecha_conformacion" class="form-control form-control-sm"
                                    value="<?= esc($inspeccion['fecha_conformacion'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label" style="font-size:12px;">Número de brigadistas</label>
                                <input type="number" min="0" name="numero_brigadistas" class="form-control form-control-sm"
                                    value="<?= esc($inspeccion['numero_brigadistas'] ?? 0) ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label" style="font-size:12px;">Jefe de brigada</label>
                                <input type="text" name="nombre_jefe_brigada" class="form-control form-control-sm"
                                    value="<?= esc($inspeccion['nombre_jefe_brigada'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label" style="font-size:12px;">¿Brigada capacitada?</label>
                                <select name="brigada_capacitada" class="form-select form-select-sm">
                                    <?= brig_sel($existeOps, $inspeccion['brigada_capacitada'] ?? null, 'no') ?>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label" style="font-size:12px;">¿Cuenta con dotación?</label>
                                <select name="cuenta_dotacion" class="form-select form-select-sm">
                                    <?= brig_sel($existeOps, $inspeccion['cuenta_dotacion'] ?? null, 'no') ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" style="font-size:12px;">Detalle de la dotación</label>
                            <textarea name="detalle_dotacion" class="form-control form-control-sm" rows="3"
                                placeholder="Chalecos, cascos, linternas, radios, botiquín, pitos..."><?= esc($inspeccion['detalle_dotacion'] ?? '') ?></textarea>
                        </div>

                        <!-- Fotos brigada / dotación -->
                        <div class="row g-2">
                            <?php foreach (['foto_brigada_1' => 'Foto brigada 1', 'foto_brigada_2' => 'Foto brigada 2', 'foto_dotacion' => 'Foto dotación'] as $campo => $lbl): ?>
                            <div class="col-4">
                                <label class="form-label" style="font-size:12px;"><?= $lbl ?></label>
                                <div class="photo-input-group">
                                    <input type="file" name="<?= $campo ?>" class="file-preview" accept="image/*" capture="environment" style="display:none;">
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-primary btn-photo-gallery" style="font-size:11px; padding:2px 6px;"><i class="fas fa-images"></i> Foto</button>
                                    </div>
                                    <div class="preview-img mt-1">
                                        <?php if (!empty($inspeccion[$campo])): ?>
                                        <img src="/<?= esc($inspeccion[$campo]) ?>" class="img-fluid rounded" style="max-height:60px; object-fit:cover; cursor:pointer; border:2px solid #28a745;" onclick="openPhoto(this.src)">
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CAPACITACIONES -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secCapac">
                        Capacitaciones
                    </button>
                </h2>
                <div id="secCapac" class="accordion-collapse collapse" data-bs-parent="#accordionBrig">
                    <div class="accordion-body">
                        <?php
                        $capFields = [
                            'capacitacion_primeros_auxilios' => 'Primeros auxilios',
                            'capacitacion_extintores'        => 'Manejo de extintores',
                            'capacitacion_evacuacion'        => 'Evacuación',
                            'capacitacion_busqueda_rescate'  => 'Búsqueda y rescate',
                            'capacitacion_comunicaciones'    => 'Comunicaciones',
                        ];
                        ?>
                        <div class="row g-2 mb-3">
                            <?php foreach ($capFields as $campo => $lbl): ?>
                            <div class="col-6 col-md-4">
                                <label class="form-label" style="font-size:12px;"><?= $lbl ?></label>
                                <select name="<?= $campo ?>" class="form-select form-select-sm">
                                    <?= brig_sel($siNoOps, $inspeccion[$campo] ?? null, 'no') ?>
                                </select>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-12">
                                <label class="form-label" style="font-size:12px;">Fecha última capacitación</label>
                                <input type="date" name="fecha_ultima_capacitacion" class="form-control form-control-sm"
                                    value="<?= esc($inspeccion['fecha_ultima_capacitacion'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-size:12px;">Capacitaciones realizadas en los últimos 12 meses</label>
                            <textarea name="capacitaciones_12m" class="form-control form-control-sm" rows="3"
                                placeholder="Listar temas, fechas y entidad capacitadora..."><?= esc($inspeccion['capacitaciones_12m'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SIMULACROS -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secSim">
                        Simulacros
                    </button>
                </h2>
                <div id="secSim" class="accordion-collapse collapse" data-bs-parent="#accordionBrig">
                    <div class="accordion-body">
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label" style="font-size:12px;">Fecha último simulacro</label>
                                <input type="date" name="fecha_ultimo_simulacro" class="form-control form-control-sm"
                                    value="<?= esc($inspeccion['fecha_ultimo_simulacro'] ?? '') ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label" style="font-size:12px;">Tipo de simulacro</label>
                                <select name="tipo_simulacro" class="form-select form-select-sm">
                                    <?= brig_sel($tiposSim, $inspeccion['tipo_simulacro'] ?? null, 'no_realizado') ?>
                                </select>
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label" style="font-size:12px;">¿Participó en simulacro nacional?</label>
                                <select name="participo_simulacro_nacional" class="form-select form-select-sm">
                                    <?= brig_sel($siNoOps, $inspeccion['participo_simulacro_nacional'] ?? null, 'no') ?>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label" style="font-size:12px;">Cantidad simulacros 12m</label>
                                <input type="number" min="0" name="cantidad_simulacros_12m" class="form-control form-control-sm"
                                    value="<?= esc($inspeccion['cantidad_simulacros_12m'] ?? 0) ?>">
                            </div>
                        </div>
                        <div>
                            <label class="form-label" style="font-size:12px;">Foto acta de simulacro</label>
                            <div class="photo-input-group">
                                <input type="file" name="foto_acta_simulacro" class="file-preview" accept="image/*" capture="environment" style="display:none;">
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-photo-gallery" style="font-size:11px; padding:2px 6px;"><i class="fas fa-images"></i> Foto</button>
                                </div>
                                <div class="preview-img mt-1">
                                    <?php if (!empty($inspeccion['foto_acta_simulacro'])): ?>
                                    <img src="/<?= esc($inspeccion['foto_acta_simulacro']) ?>" class="img-fluid rounded" style="max-height:80px; object-fit:cover; cursor:pointer; border:2px solid #28a745;" onclick="openPhoto(this.src)">
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- HALLAZGOS -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secHall">
                        Hallazgos y recomendaciones
                    </button>
                </h2>
                <div id="secHall" class="accordion-collapse collapse" data-bs-parent="#accordionBrig">
                    <div class="accordion-body">
                        <div class="mb-2">
                            <label class="form-label" style="font-size:12px;">Fortalezas</label>
                            <textarea name="fortalezas" class="form-control form-control-sm" rows="3"><?= esc($inspeccion['fortalezas'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-size:12px;">Debilidades</label>
                            <textarea name="debilidades" class="form-control form-control-sm" rows="3"><?= esc($inspeccion['debilidades'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-size:12px;">Recomendaciones</label>
                            <textarea name="recomendaciones" class="form-control form-control-sm" rows="3"><?= esc($inspeccion['recomendaciones'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-size:12px;">Observaciones</label>
                            <textarea name="observaciones" class="form-control form-control-sm" rows="2"><?= esc($inspeccion['observaciones'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Modal foto -->
        <div class="modal fade" id="photoModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-body p-2 text-center">
                <img id="photoModalImg" src="" class="img-fluid">
            </div></div></div>
        </div>

        <!-- Botones -->
        <div class="d-flex gap-2 mt-3 mb-4">
            <button type="submit" class="btn btn-pwa-primary flex-grow-1"><i class="fas fa-save"></i> Guardar</button>
            <?php if ($isEdit && ($inspeccion['estado'] ?? '') !== 'completo'): ?>
            <button type="button" id="btnFinalizar" class="btn btn-success flex-grow-1"><i class="fas fa-check-double"></i> Finalizar</button>
            <?php endif; ?>
            <a href="<?= base_url('/inspecciones/brigada-simulacros') ?>" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
        </div>
    </form>
</div>

<script>
function openPhoto(src) {
    document.getElementById('photoModalImg').src = src;
    new bootstrap.Modal(document.getElementById('photoModal')).show();
}

document.addEventListener('DOMContentLoaded', function() {
    const clienteId = '<?= $idCliente ?? '' ?>';

    // Select2 clientes
    $.ajax({
        url: '<?= base_url('/inspecciones/api/clientes') ?>',
        dataType: 'json',
        success: function(data) {
            const select = document.getElementById('selectCliente');
            data.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id_cliente;
                opt.textContent = c.nombre_cliente;
                if (clienteId && c.id_cliente == clienteId) opt.selected = true;
                select.appendChild(opt);
            });
            $('#selectCliente').select2({ placeholder: 'Seleccionar cliente...', width: '100%' });
        }
    });

    // Galeria
    document.addEventListener('click', function(e) {
        const galleryBtn = e.target.closest('.btn-photo-gallery');
        if (!galleryBtn) return;
        const group = galleryBtn.closest('.photo-input-group');
        const input = group.querySelector('input[type="file"]');
        input.removeAttribute('capture');
        input.click();
    });

    // Preview fotos
    document.addEventListener('change', function(e) {
        if (!e.target.classList.contains('file-preview')) return;
        const input = e.target;
        const group = input.closest('.photo-input-group');
        const previewDiv = group ? group.querySelector('.preview-img') : null;
        if (!previewDiv) return;
        previewDiv.innerHTML = '';
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(ev) {
                previewDiv.innerHTML = '<img src="' + ev.target.result + '" class="img-fluid rounded" style="max-height:60px; object-fit:cover; cursor:pointer; border:2px solid #28a745;" onclick="openPhoto(this.src)">' +
                    '<div style="font-size:11px; color:#28a745; margin-top:2px;"><i class="fas fa-check-circle"></i> Foto lista</div>';
            };
            reader.readAsDataURL(input.files[0]);
        }
    });

    // Finalizar
    const btnFin = document.getElementById('btnFinalizar');
    if (btnFin) {
        btnFin.addEventListener('click', function() {
            const cliente = document.getElementById('selectCliente').value;
            if (!cliente) {
                Swal.fire({ icon: 'warning', title: 'Selecciona un cliente', confirmButtonColor: '#bd9751' });
                return;
            }
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
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'finalizar';
                    input.value = '1';
                    document.getElementById('brigForm').appendChild(input);
                    document.getElementById('brigForm').submit();
                }
            });
        });
    }
});
</script>
