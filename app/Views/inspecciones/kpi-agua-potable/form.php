<?php if (session()->getFlashdata('msg')): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= session()->getFlashdata('msg') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (session()->getFlashdata('errors')): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?php foreach (session()->getFlashdata('errors') as $e): ?>
        <div><?= esc($e) ?></div>
    <?php endforeach; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php
$isEdit = !empty($inspeccion);
$action = $isEdit ? base_url('/inspecciones/kpi-agua-potable/update/') . $inspeccion['id'] : base_url('/inspecciones/kpi-agua-potable/store');
$storageKey = $isEdit ? 'kpi_agua_potable_draft_' . $inspeccion['id'] : 'kpi_agua_potable_draft_new';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0"><i class="fas fa-chart-area me-2"></i><?= esc($title) ?></h5>
    <a href="<?= base_url('/inspecciones/kpi-agua-potable') ?>" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
</div>

<form method="post" action="<?= $action ?>" enctype="multipart/form-data" id="kpiAguaPotableForm">
    <?= csrf_field() ?>

    <!-- Datos Generales -->
    <div class="card mb-3">
        <div class="card-header bg-dark text-white py-2 px-3" style="font-size:13px;">
            <i class="fas fa-info-circle me-1"></i> Datos Generales
        </div>
        <div class="card-body p-3">
            <div class="mb-3">
                <label class="form-label" style="font-size:12px;">Cliente <span class="text-danger">*</span></label>
                <select name="id_cliente" id="selectCliente" class="form-select" required>
                    <option value="">Seleccione...</option>
                </select>
            </div>
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="form-label" style="font-size:12px;">Fecha inspección <span class="text-danger">*</span></label>
                    <input type="date" name="fecha_inspeccion" class="form-control form-control-sm"
                           value="<?= $isEdit ? esc($inspeccion['fecha_inspeccion']) : date('Y-m-d') ?>" required>
                </div>
                <div class="col-6">
                    <label class="form-label" style="font-size:12px;">Responsable</label>
                    <input type="text" name="nombre_responsable" class="form-control form-control-sm"
                           value="<?= $isEdit ? esc($inspeccion['nombre_responsable']) : '' ?>">
                </div>
            </div>
        </div>
    </div>

    <!-- Indicador y Cumplimiento -->
    <div class="card mb-3">
        <div class="card-header bg-dark text-white py-2 px-3" style="font-size:13px;">
            <i class="fas fa-chart-area me-1"></i> Indicador de Gestión
        </div>
        <div class="card-body p-3">
            <div class="mb-3">
                <label class="form-label" style="font-size:12px;">Indicador <span class="text-danger">*</span></label>
                <select name="indicador" id="selectIndicador" class="form-select form-select-sm" required>
                    <option value="">Seleccione indicador...</option>
                    <?php foreach ($indicadores as $ind): ?>
                        <option value="<?= esc($ind) ?>" <?= ($isEdit && $inspeccion['indicador'] === $ind) ? 'selected' : '' ?>>
                            <?= esc($ind) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Fórmula y Meta -->
            <div id="formulaBox" class="alert alert-info py-2 px-3 mb-3" style="font-size:12px; display:none;">
                <i class="fas fa-calculator me-1"></i> <strong>Fórmula:</strong> <span id="formulaTexto"></span>
                <br><i class="fas fa-bullseye me-1"></i> <strong>Meta:</strong> <span id="metaTexto"></span>
            </div>

            <!-- Numerador / Denominador -->
            <div id="formulaInputs" style="display:none;">
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <label class="form-label" style="font-size:11px;" id="labelNumerador">Numerador</label>
                        <input type="number" name="valor_numerador" id="inputNumerador" class="form-control form-control-sm"
                               min="0" step="1" value="<?= $isEdit ? esc($inspeccion['valor_numerador'] ?? '') : '' ?>">
                    </div>
                    <div class="col-6">
                        <label class="form-label" style="font-size:11px;" id="labelDenominador">Denominador</label>
                        <input type="number" name="valor_denominador" id="inputDenominador" class="form-control form-control-sm"
                               min="1" step="1" value="<?= $isEdit ? esc($inspeccion['valor_denominador'] ?? '') : '' ?>">
                    </div>
                </div>

                <!-- Resultado auto-calculado -->
                <div class="row g-2 mb-2 align-items-end">
                    <div class="col-6">
                        <label class="form-label" style="font-size:11px;">Cumplimiento (%)</label>
                        <input type="number" name="cumplimiento" id="inputCumplimiento" class="form-control form-control-sm bg-light"
                               min="0" max="100" step="0.01" readonly
                               value="<?= $isEdit ? esc($inspeccion['cumplimiento']) : '' ?>">
                    </div>
                    <div class="col-6 text-center">
                        <span id="badgeCualitativa" class="badge fs-6" style="display:none;">—</span>
                    </div>
                </div>
            </div>

            <!-- Observaciones -->
            <div class="mb-0">
                <label class="form-label" style="font-size:12px;">Observaciones</label>
                <textarea name="observaciones" class="form-control form-control-sm" rows="3"
                          placeholder="Ej: Se verificó la calidad del agua en todos los puntos de suministro"><?= $isEdit ? esc($inspeccion['observaciones'] ?? '') : '' ?></textarea>
            </div>
        </div>
    </div>

    <!-- Evidencias fotográficas -->
    <div class="card mb-3">
        <div class="card-header bg-dark text-white py-2 px-3" style="font-size:13px;">
            <i class="fas fa-camera me-1"></i> Registros y Formatos (Evidencias)
        </div>
        <div class="card-body p-3">
            <div class="row g-2">
                <?php for ($i = 1; $i <= 4; $i++): ?>
                <div class="col-6 mb-3">
                    <label class="form-label" style="font-size:12px;">Evidencia <?= $i ?></label>
                    <?php $campo = "registro_formato_$i"; ?>
                    <?php if ($isEdit && !empty($inspeccion[$campo])): ?>
                        <div class="mb-1">
                            <img src="/<?= esc($inspeccion[$campo]) ?>" class="img-fluid rounded"
                                 style="max-height:80px; object-fit:cover; cursor:pointer; border:2px solid #28a745;"
                                 onclick="openPhoto(this.src)">
                        </div>
                    <?php endif; ?>
                    <div class="photo-input-group">
                        <input type="file" name="<?= $campo ?>" class="file-preview" accept="image/*" style="display:none;">
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-sm btn-outline-primary btn-photo-gallery" style="font-size:11px; padding:2px 6px;">
                                <i class="fas fa-images"></i> Foto
                            </button>
                        </div>
                        <div class="preview-img mt-1"></div>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>

        <div id="autoguardadoIndicador" class="text-center text-muted mb-3" style="font-size: 12px; display: none;">
        <i class="fas fa-save"></i> Guardado local: <span id="autoguardadoHora"></span>
    </div>

    <!-- Botones -->
    <div class="d-grid gap-3 mt-3 mb-5 pb-3">
        <button type="submit" class="btn btn-pwa btn-pwa-primary py-3" style="font-size:17px;">
            <i class="fas fa-save me-1"></i> Guardar Borrador
        </button>
        <?php if ($isEdit): ?>
        <button type="button" id="btnFinalizar" class="btn btn-success py-3" style="font-size:17px;">
            <i class="fas fa-check-circle me-1"></i> Finalizar
        </button>
        <?php endif; ?>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configuración de indicadores
    var indicadorConfig = <?= json_encode($indicadorConfig ?? [], JSON_UNESCAPED_UNICODE) ?>;
    var selInd = document.getElementById('selectIndicador');
    var formulaBox = document.getElementById('formulaBox');
    var formulaInputs = document.getElementById('formulaInputs');
    var formulaTexto = document.getElementById('formulaTexto');
    var metaTexto = document.getElementById('metaTexto');
    var labelNum = document.getElementById('labelNumerador');
    var labelDen = document.getElementById('labelDenominador');
    var inputNum = document.getElementById('inputNumerador');
    var inputDen = document.getElementById('inputDenominador');
    var inputCumpl = document.getElementById('inputCumplimiento');
    var badgeCual = document.getElementById('badgeCualitativa');

    function onIndicadorChange() {
        var val = selInd.value;
        var cfg = indicadorConfig[val];
        if (!cfg) {
            formulaBox.style.display = 'none';
            formulaInputs.style.display = 'none';
            return;
        }
        formulaTexto.textContent = cfg.formula;
        metaTexto.textContent = cfg.meta_texto;
        formulaBox.style.display = 'block';
        formulaInputs.style.display = 'block';
        labelNum.textContent = cfg.label_numerador;
        labelDen.textContent = cfg.label_denominador;
        if (cfg.denominador_fijo) {
            inputDen.value = cfg.denominador_fijo;
            inputDen.readOnly = true;
        } else {
            inputDen.readOnly = false;
        }
        recalcular();
    }

    function recalcular() {
        var num = parseInt(inputNum.value) || 0;
        var den = parseInt(inputDen.value) || 0;
        if (den > 0) {
            var pct = ((num / den) * 100).toFixed(2);
            inputCumpl.value = pct;
            var cfg = indicadorConfig[selInd.value];
            if (cfg) {
                if (parseFloat(pct) >= cfg.meta) {
                    badgeCual.textContent = 'CUMPLE';
                    badgeCual.className = 'badge fs-6 bg-success';
                } else {
                    badgeCual.textContent = 'NO CUMPLE';
                    badgeCual.className = 'badge fs-6 bg-danger';
                }
                badgeCual.style.display = 'inline-block';
            }
        } else {
            inputCumpl.value = '';
            badgeCual.style.display = 'none';
        }
    }

    selInd.addEventListener('change', onIndicadorChange);
    inputNum.addEventListener('input', recalcular);
    inputDen.addEventListener('input', recalcular);
    onIndicadorChange();

    // Select2 clientes (pre-carga)
    var clienteVal = '<?= $isEdit ? $inspeccion['id_cliente'] : ($idCliente ?? '') ?>';
    $.ajax({
        url: '<?= base_url('/inspecciones/api/clientes') ?>',
        dataType: 'json',
        success: function(data) {
            var select = document.getElementById('selectCliente');
            data.forEach(function(c) {
                var opt = document.createElement('option');
                opt.value = c.id_cliente;
                opt.textContent = c.nombre_cliente;
                if (clienteVal && c.id_cliente == clienteVal) opt.selected = true;
                select.appendChild(opt);
            });
            $('#selectCliente').select2({ placeholder: 'Buscar cliente...', width: '100%' });
            if (window._pendingClientRestore) {
                $('#selectCliente').val(window._pendingClientRestore).trigger('change');
                window._pendingClientRestore = null;
            }
        }
    });

    // Photo buttons
    document.querySelectorAll('.btn-photo-gallery').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var input = this.closest('.photo-input-group').querySelector('.file-preview');
            input.removeAttribute('capture');
            input.click();
        });
    });
    document.querySelectorAll('.file-preview').forEach(function(input) {
        input.addEventListener('change', function() {
            var preview = this.closest('.photo-input-group').querySelector('.preview-img');
            if (this.files && this.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = '<img src="' + e.target.result + '" class="img-fluid rounded" style="max-height:80px; object-fit:cover;">';
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    });

    // Botón Finalizar con SweetAlert
    var btnFinalizar = document.getElementById('btnFinalizar');
    if (btnFinalizar) {
        btnFinalizar.addEventListener('click', function() {
            Swal.fire({
                icon: 'question',
                title: 'Finalizar reporte',
                html: '<p>Se finalizarán <strong>todos los indicadores</strong> de este cliente y fecha, y se generará el PDF.</p><p>No podrá editar después.</p>',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-check-circle"></i> Sí, finalizar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#28a745',
            }).then(function(result) {
                if (result.isConfirmed) {
                    window.location.href = '<?= base_url('/inspecciones/' . esc($slug ?? 'kpi-agua-potable') . '/finalizar-grupo/' . ($inspeccion['id'] ?? '')) ?>';
                }
            });
        });
    }

    // ============================================================
    // AUTOGUARDADO EN LOCALSTORAGE (restaurar borradores)
    // ============================================================
    const STORAGE_KEY = 'kpi_agua_potable_draft_<?= $inspeccion['id'] ?? 'new' ?>';
    const isEditLocal = <?= $isEdit ? 'true' : 'false' ?>;

    function restoreFromLocal(data) {
        if (data.id_cliente) window._pendingClientRestore = data.id_cliente;
        if (data.fecha_inspeccion) document.querySelector('[name="fecha_inspeccion"]').value = data.fecha_inspeccion;
        if (data.nombre_responsable) document.querySelector('[name="nombre_responsable"]').value = data.nombre_responsable;
        if (data.indicador) { document.querySelector('[name="indicador"]').value = data.indicador; onIndicadorChange(); }
        if (data.valor_numerador) document.querySelector('[name="valor_numerador"]').value = data.valor_numerador;
        if (data.valor_denominador) document.querySelector('[name="valor_denominador"]').value = data.valor_denominador;
        if (data.observaciones) document.querySelector('[name="observaciones"]').value = data.observaciones;
        recalcular();
    }

    if (!isEditLocal) {
        try {
            const saved = localStorage.getItem(STORAGE_KEY);
            if (saved) {
                const data = JSON.parse(saved);
                const hoursAgo = ((Date.now() - new Date(data._savedAt).getTime()) / 3600000).toFixed(1);
                if (hoursAgo < 24) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Borrador recuperado',
                        html: 'Tienes un borrador guardado hace <strong>' + hoursAgo + ' horas</strong>.<br>Deseas restaurarlo?',
                        showCancelButton: true,
                        confirmButtonText: 'Si, restaurar',
                        cancelButtonText: 'No, empezar de cero',
                        confirmButtonColor: '#bd9751',
                    }).then(result => {
                        if (result.isConfirmed) restoreFromLocal(data);
                        else localStorage.removeItem(STORAGE_KEY);
                    });
                } else {
                    localStorage.removeItem(STORAGE_KEY);
                }
            }
        } catch(e) {}
    }

    // ============================================================
    // AUTOGUARDADO SERVIDOR (cada 60s)
    // ============================================================
    initAutosave({
        formId: 'kpiAguaPotableForm',
        storeUrl: base_url('/inspecciones/kpi-agua-potable/store'),
        updateUrlBase: base_url('/inspecciones/kpi-agua-potable/update/'),
        editUrlBase: base_url('/inspecciones/kpi-agua-potable/edit/'),
        recordId: <?= $inspeccion['id'] ?? 'null' ?>,
        isEdit: <?= $isEdit ? 'true' : 'false' ?>,
        storageKey: STORAGE_KEY,
        intervalSeconds: 60,
    });
});

function openPhoto(src) {
    Swal.fire({ imageUrl: src, imageAlt: 'Foto', showConfirmButton: false, showCloseButton: true, width: 'auto' });
}

<?php if (session()->getFlashdata('saved_cliente_id')): ?>
(function() {
    var savedCliente = '<?= session()->getFlashdata('saved_cliente_id') ?>';
    var savedIndicador = '<?= esc(session()->getFlashdata('saved_indicador') ?? '') ?>';
    var currentId = <?= $inspeccion['id'] ?? 'null' ?>;
    var indicadores = <?= json_encode(array_keys($indicadorConfig ?? []), JSON_UNESCAPED_UNICODE) ?>;
    var pendientes = indicadores.filter(function(i) { return i !== savedIndicador; });
    var slug = '<?= esc($slug ?? 'kpi-agua-potable') ?>';

    setTimeout(function() {
        var htmlMsg = '<p>Borrador guardado correctamente.</p>';
        if (pendientes.length > 0) {
            htmlMsg += '<p style="font-size:13px; color:#666;">Indicador(es) pendiente(s): <strong>' + pendientes.join(', ') + '</strong></p>';
        }
        htmlMsg += '<p style="font-size:13px;">¿Qué desea hacer?</p>';

        Swal.fire({
            icon: 'success',
            title: 'Borrador guardado',
            html: htmlMsg,
            showDenyButton: pendientes.length > 0,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-check-circle"></i> Finalizar reporte',
            denyButtonText: pendientes.length > 0 ? '<i class="fas fa-plus-circle"></i> Crear otro indicador' : '',
            cancelButtonText: '<i class="fas fa-edit"></i> Quedarme aquí',
            confirmButtonColor: '#28a745',
            denyButtonColor: '#bd9751',
        }).then(function(result) {
            if (result.isConfirmed && currentId) {
                window.location.href = '<?= base_url('/inspecciones/' . ($slug ?? 'kpi-agua-potable') . '/finalizar-grupo/') ?>' + currentId;
            } else if (result.isDenied) {
                window.location.href = '<?= base_url('/inspecciones/' . ($slug ?? 'kpi-agua-potable') . '/create/') ?>' + savedCliente;
            }
        });
    }, 500);
})();
<?php endif; ?>
</script>
