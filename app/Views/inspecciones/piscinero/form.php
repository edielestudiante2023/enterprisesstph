<?php
$isEdit = !empty($inspeccion);
$action = $isEdit ? base_url('/inspecciones/piscinero/update/') . $inspeccion['id'] : base_url('/inspecciones/piscinero/store');
$sn = function($name) use ($inspeccion) {
    $v = $inspeccion[$name] ?? 'NA';
    $html = '';
    foreach (['SI','NO','NA'] as $opt) {
        $sel = $v === $opt ? 'selected' : '';
        $html .= '<option value="'.$opt.'" '.$sel.'>'.$opt.'</option>';
    }
    return $html;
};
?>

<div class="container-fluid px-3">
    <form method="post" action="<?= $action ?>" enctype="multipart/form-data" id="piscineroForm">
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
        <div class="alert alert-success mt-2" style="font-size:14px;"><?= session()->getFlashdata('msg') ?></div>
        <?php endif; ?>

        <div class="accordion mt-2" id="accordionPiscinero">

            <!-- DATOS GENERALES -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#secDatos">
                        Datos Generales
                    </button>
                </h2>
                <div id="secDatos" class="accordion-collapse collapse show" data-bs-parent="#accordionPiscinero">
                    <div class="accordion-body">
                        <div class="mb-3">
                            <label class="form-label">Cliente *</label>
                            <select name="id_cliente" id="selectCliente" class="form-select" required>
                                <option value="">Seleccionar cliente...</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fecha inspeccion *</label>
                            <input type="date" name="fecha_inspeccion" class="form-control" value="<?= $inspeccion['fecha_inspeccion'] ?? date('Y-m-d') ?>" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DATOS PERSONALES -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secPersonal">
                        Datos personales del operador de piscina
                    </button>
                </h2>
                <div id="secPersonal" class="accordion-collapse collapse" data-bs-parent="#accordionPiscinero">
                    <div class="accordion-body">
                        <div class="mb-2">
                            <label class="form-label" style="font-size:12px;">Nombre completo</label>
                            <input type="text" name="nombre_piscinero" class="form-control form-control-sm" value="<?= esc($inspeccion['nombre_piscinero'] ?? '') ?>">
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label" style="font-size:12px;">Cedula</label>
                                <input type="text" name="cedula" class="form-control form-control-sm" value="<?= esc($inspeccion['cedula'] ?? '') ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label" style="font-size:12px;">Telefono</label>
                                <input type="text" name="telefono" class="form-control form-control-sm" value="<?= esc($inspeccion['telefono'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-size:12px;">Tipo de vinculacion</label>
                            <select name="vinculacion" class="form-select form-select-sm">
                                <?php $vc = $inspeccion['vinculacion'] ?? 'DIRECTO_COPROPIEDAD'; ?>
                                <?php foreach (['DIRECTO_COPROPIEDAD','EMPRESA_ASEO','EMPRESA_ESPECIALIZADA','OTRA'] as $opt): ?>
                                <option value="<?= $opt ?>" <?= $vc === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-7">
                                <label class="form-label" style="font-size:12px;">Empresa contratista</label>
                                <input type="text" name="empresa_contratista" class="form-control form-control-sm" value="<?= esc($inspeccion['empresa_contratista'] ?? '') ?>">
                            </div>
                            <div class="col-5">
                                <label class="form-label" style="font-size:12px;">NIT</label>
                                <input type="text" name="nit_empresa_contratista" class="form-control form-control-sm" value="<?= esc($inspeccion['nit_empresa_contratista'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-size:12px;">Foto del operador de piscina</label>
                            <input type="file" name="foto_piscinero" class="foto-input-pwa" accept="image/*" data-label="Foto del operador"<?= !empty($inspeccion['foto_piscinero']) ? ' data-previous-url="' . base_url($inspeccion['foto_piscinero']) . '"' : '' ?>>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CERTIFICACIONES -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secCert">
                        Certificaciones (RCP y Salvamento)
                    </button>
                </h2>
                <div id="secCert" class="accordion-collapse collapse" data-bs-parent="#accordionPiscinero">
                    <div class="accordion-body">
                        <div class="row g-2 mb-2 align-items-center">
                            <div class="col-7"><label class="form-label mb-0" style="font-size:12px;">Certificacion RCP vigente</label></div>
                            <div class="col-5">
                                <select name="certificacion_rcp_vigente" class="form-select form-select-sm"><?= $sn('certificacion_rcp_vigente') ?></select>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-size:12px;">Fecha vencimiento RCP</label>
                            <input type="date" name="fecha_vencimiento_rcp" class="form-control form-control-sm" value="<?= $inspeccion['fecha_vencimiento_rcp'] ?? '' ?>">
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-size:12px;">Foto certificado RCP</label>
                            <input type="file" name="foto_certificado_rcp" class="foto-input-pwa" accept="image/*" data-label="Certificado RCP"<?= !empty($inspeccion['foto_certificado_rcp']) ? ' data-previous-url="' . base_url($inspeccion['foto_certificado_rcp']) . '"' : '' ?>>
                        </div>
                        <hr>
                        <div class="row g-2 mb-2 align-items-center">
                            <div class="col-7"><label class="form-label mb-0" style="font-size:12px;">Curso salvamento acuatico</label></div>
                            <div class="col-5">
                                <select name="curso_salvamento_acuatico" class="form-select form-select-sm"><?= $sn('curso_salvamento_acuatico') ?></select>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-size:12px;">Fecha vencimiento salvamento</label>
                            <input type="date" name="fecha_vencimiento_salvamento" class="form-control form-control-sm" value="<?= $inspeccion['fecha_vencimiento_salvamento'] ?? '' ?>">
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-size:12px;">Foto certificado salvamento</label>
                            <input type="file" name="foto_certificado_salvamento" class="foto-input-pwa" accept="image/*" data-label="Certificado salvamento"<?= !empty($inspeccion['foto_certificado_salvamento']) ? ' data-previous-url="' . base_url($inspeccion['foto_certificado_salvamento']) . '"' : '' ?>>
                        </div>

                        <hr>
                        <h6 class="mb-1">Operador de piscinas certificado (Res 234/2026 Art. 11 num 7)</h6>
                        <div class="form-text mb-2" style="font-size:11px; line-height:1.3;">
                            <strong>Distinto del salvavidas RCP (Ley 1209).</strong> Es la persona que opera quimicos y mantenimiento.
                            La ausencia de operador certificado es factor de priorizacion sanitaria.
                            Entidades: SENA, IDEAM, universidades o autoridad sanitaria municipal.
                        </div>
                        <div class="row g-1 mb-2 align-items-center">
                            <div class="col-7"><label class="form-label mb-0" style="font-size:12px;">Operador de piscinas certificado</label></div>
                            <div class="col-5">
                                <select name="certificacion_operador_piscinas" class="form-select form-select-sm"><?= $sn('certificacion_operador_piscinas') ?></select>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-size:12px;">Entidad certificadora</label>
                            <input type="text" name="operador_entidad_certificadora" class="form-control form-control-sm" value="<?= esc($inspeccion['operador_entidad_certificadora'] ?? '') ?>" placeholder="Ej: SENA, IDEAM, Universidad...">
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-size:12px;">Vigencia certificacion</label>
                            <input type="date" name="operador_vigencia" class="form-control form-control-sm" value="<?= $inspeccion['operador_vigencia'] ?? '' ?>">
                        </div>
                        <div class="mb-2">
                            <label class="form-label" style="font-size:12px;"><i class="fas fa-images me-1"></i> Foto certificado operador</label>
                            <input type="file" name="foto_certificado_operador" class="foto-input-pwa" accept="image/*" data-label="Certificado operador"<?= !empty($inspeccion['foto_certificado_operador']) ? ' data-previous-url="' . base_url($inspeccion['foto_certificado_operador']) . '"' : '' ?>>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AFILIACIONES SST -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secAfil">
                        Afiliaciones SST (ARL, EPS, Examenes)
                    </button>
                </h2>
                <div id="secAfil" class="accordion-collapse collapse" data-bs-parent="#accordionPiscinero">
                    <div class="accordion-body">
                        <?php
                        $afil = [
                            'afiliacion_arl_vigente'         => 'Afiliacion ARL vigente',
                            'afiliacion_eps_vigente'         => 'Afiliacion EPS vigente',
                            'examenes_medicos_ocupacionales' => 'Examenes medicos ocupacionales',
                        ];
                        foreach ($afil as $f => $l): ?>
                        <div class="row g-1 mb-2 align-items-center">
                            <div class="col-7"><label class="form-label mb-0" style="font-size:12px;"><?= $l ?></label></div>
                            <div class="col-5">
                                <select name="<?= $f ?>" class="form-select form-select-sm"><?= $sn($f) ?></select>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <div class="mb-2">
                            <label class="form-label" style="font-size:12px;">Fecha ultimo examen medico</label>
                            <input type="date" name="fecha_ultimo_examen_medico" class="form-control form-control-sm" value="<?= $inspeccion['fecha_ultimo_examen_medico'] ?? '' ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- DOTACION EPP -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secEpp">
                        Dotacion EPP
                    </button>
                </h2>
                <div id="secEpp" class="accordion-collapse collapse" data-bs-parent="#accordionPiscinero">
                    <div class="accordion-body">
                        <?php
                        $epp = [
                            'dotacion_epp_entregada'   => 'Dotacion EPP entregada',
                            'gafas_proteccion_quimica' => 'Gafas proteccion quimica',
                            'guantes_nitrilo'          => 'Guantes de nitrilo',
                            'careta_proteccion'        => 'Careta de proteccion',
                            'delantal_impermeable'     => 'Delantal impermeable',
                        ];
                        foreach ($epp as $f => $l): ?>
                        <div class="row g-1 mb-2 align-items-center">
                            <div class="col-7"><label class="form-label mb-0" style="font-size:12px;"><?= $l ?></label></div>
                            <div class="col-5">
                                <select name="<?= $f ?>" class="form-select form-select-sm"><?= $sn($f) ?></select>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- CAPACITACION -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secCap">
                        Capacitacion
                    </button>
                </h2>
                <div id="secCap" class="accordion-collapse collapse" data-bs-parent="#accordionPiscinero">
                    <div class="accordion-body">
                        <?php
                        $cap = [
                            'capacitacion_manejo_quimicos'      => 'Capacitacion manejo de quimicos',
                            'capacitacion_dosificacion_quimica' => 'Capacitacion en dosificacion quimica (Art. 5 Res 234)',
                            'conocimiento_hojas_seguridad'      => 'Conocimiento de hojas de seguridad',
                            'conocimiento_plan_emergencia'      => 'Conocimiento del plan de emergencia',
                        ];
                        foreach ($cap as $f => $l): ?>
                        <div class="row g-1 mb-2 align-items-center">
                            <div class="col-7"><label class="form-label mb-0" style="font-size:12px;"><?= $l ?></label></div>
                            <div class="col-5">
                                <select name="<?= $f ?>" class="form-select form-select-sm"><?= $sn($f) ?></select>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- HORARIO -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secHor">
                        Horario Operativo
                    </button>
                </h2>
                <div id="secHor" class="accordion-collapse collapse" data-bs-parent="#accordionPiscinero">
                    <div class="accordion-body">
                        <div class="row g-1 mb-2 align-items-center">
                            <div class="col-7"><label class="form-label mb-0" style="font-size:12px;">Horario cubre operacion de la piscina</label></div>
                            <div class="col-5">
                                <select name="horario_cubre_operacion_piscina" class="form-select form-select-sm"><?= $sn('horario_cubre_operacion_piscina') ?></select>
                            </div>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label" style="font-size:12px;">Horario inicio</label>
                                <input type="time" name="horario_inicio" class="form-control form-control-sm" value="<?= $inspeccion['horario_inicio'] ?? '' ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label" style="font-size:12px;">Horario fin</label>
                                <input type="time" name="horario_fin" class="form-control form-control-sm" value="<?= $inspeccion['horario_fin'] ?? '' ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /accordion -->

        <div class="card mt-3">
            <div class="card-body p-2">
                <label class="form-label" style="font-size:13px;">Observaciones</label>
                <textarea name="observaciones" class="form-control" rows="3"><?= esc($inspeccion['observaciones'] ?? '') ?></textarea>
            </div>
        </div>

        <div id="autoSaveStatus" style="font-size:12px; color:#999; text-align:center; padding:4px 0;">
            <i class="fas fa-cloud"></i> Autoguardado activado
        </div>

        <div class="d-grid gap-3 mt-3 mb-5 pb-3">
            <button type="submit" class="btn btn-pwa btn-pwa-outline py-3" style="font-size:17px;">
                <i class="fas fa-save"></i> Guardar borrador
            </button>
            <button type="submit" name="finalizar" value="1" class="btn btn-pwa btn-pwa-primary py-3" style="font-size:17px;" id="btnFinalizar">
                <i class="fas fa-check-circle"></i> Finalizar inspeccion
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const clienteId = '<?= $idCliente ?? '' ?>';

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

            if (window._pendingClientRestore) {
                $('#selectCliente').val(window._pendingClientRestore).trigger('change');
                window._pendingClientRestore = null;
            }
        }
    });

    document.getElementById('btnFinalizar').addEventListener('click', function(e) {
        const cliente = document.getElementById('selectCliente').value;
        if (!cliente) {
            e.preventDefault();
            Swal.fire({ icon: 'warning', title: 'Selecciona un cliente', confirmButtonColor: '#bd9751' });
            return;
        }
        e.preventDefault();
        Swal.fire({
            title: 'Finalizar inspeccion?',
            html: 'Se generara el PDF y no podras editar despues.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Si, finalizar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#bd9751',
        }).then(result => {
            if (result.isConfirmed) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'finalizar';
                input.value = '1';
                document.getElementById('piscineroForm').appendChild(input);
                document.getElementById('piscineroForm').submit();
            }
        });
    });

    const STORAGE_KEY = 'piscinero_draft_<?= $inspeccion['id'] ?? 'new' ?>';
    initAutosave({
        formId: 'piscineroForm',
        storeUrl: base_url('/inspecciones/piscinero/store'),
        updateUrlBase: base_url('/inspecciones/piscinero/update/'),
        editUrlBase: base_url('/inspecciones/piscinero/edit/'),
        recordId: <?= $inspeccion['id'] ?? 'null' ?>,
        isEdit: <?= $isEdit ? 'true' : 'false' ?>,
        storageKey: STORAGE_KEY,
        intervalSeconds: 60,
    });
});

// Preview en vivo de fotos al seleccionar archivo
document.addEventListener('change', function(e) {
    const input = e.target;
    if (!(input && input.tagName === 'INPUT' && input.type === 'file')) return;
    if (!input.files || input.files.length === 0) return;
    const file = input.files[0];
    if (!file.type.startsWith('image/')) return;
    let preview = input.previousElementSibling;
    const isPreview = preview && preview.classList && preview.classList.contains('file-live-preview');
    if (!isPreview) {
        preview = document.createElement('div');
        preview.className = 'file-live-preview';
        preview.style.cssText = 'margin: 2px 0 4px 0;';
        input.parentNode.insertBefore(preview, input);
    }
    const existingImg = preview.querySelector('img');
    if (existingImg && existingImg.src.startsWith('blob:')) URL.revokeObjectURL(existingImg.src);
    const url = URL.createObjectURL(file);
    const nombreCorto = file.name.length > 28 ? file.name.slice(0, 25) + '...' : file.name;
    preview.innerHTML = '<img src="' + url + '" style="max-width:110px;max-height:85px;border:2px solid #1b7e3f;border-radius:4px;display:block;margin-bottom:2px;"><span style="font-size:10px;color:#1b7e3f;"><i class="fas fa-check"></i> ' + nombreCorto + '</span>';
});
</script>
