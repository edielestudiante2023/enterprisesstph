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
$action = $isEdit ? '/inspecciones/kpi-limpieza/update/' . $inspeccion['id'] : '/inspecciones/kpi-limpieza/store';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i><?= esc($title) ?></h5>
    <a href="/inspecciones/kpi-limpieza" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
</div>

<form method="post" action="<?= $action ?>" enctype="multipart/form-data">
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
            <i class="fas fa-chart-line me-1"></i> Indicador de Gestión
        </div>
        <div class="card-body p-3">
            <div class="mb-3">
                <label class="form-label" style="font-size:12px;">Indicador <span class="text-danger">*</span></label>
                <select name="indicador" class="form-select form-select-sm" required>
                    <option value="">Seleccione indicador...</option>
                    <?php foreach ($indicadores as $ind): ?>
                        <option value="<?= esc($ind) ?>" <?= ($isEdit && $inspeccion['indicador'] === $ind) ? 'selected' : '' ?>>
                            <?= esc($ind) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label" style="font-size:12px;">Cumplimiento (%) <span class="text-danger">*</span></label>
                <input type="number" name="cumplimiento" class="form-control form-control-sm"
                       min="0" max="100" step="0.1"
                       value="<?= $isEdit ? esc($inspeccion['cumplimiento']) : '' ?>" required>
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
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-photo-camera" style="font-size:11px; padding:2px 6px;">
                                <i class="fas fa-camera"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-photo-gallery" style="font-size:11px; padding:2px 6px;">
                                <i class="fas fa-images"></i>
                            </button>
                        </div>
                        <div class="preview-img mt-1"></div>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>

    <!-- Botones -->
    <div class="d-flex gap-2 mb-4">
        <button type="submit" class="btn btn-pwa btn-pwa-primary flex-fill">
            <i class="fas fa-save me-1"></i> Guardar Borrador
        </button>
        <?php if ($isEdit): ?>
        <button type="submit" name="finalizar" value="1" class="btn btn-success flex-fill"
                onclick="return confirm('¿Finalizar y generar PDF? No podrá editar después.')">
            <i class="fas fa-check-circle me-1"></i> Finalizar
        </button>
        <?php endif; ?>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select2 AJAX para clientes
    var clienteVal = '<?= $isEdit ? $inspeccion['id_cliente'] : ($idCliente ?? '') ?>';
    $('#selectCliente').select2({
        ajax: {
            url: '/inspecciones/api/clientes',
            dataType: 'json',
            delay: 250,
            processResults: function(data) {
                return {
                    results: data.map(function(c) {
                        return { id: c.id_cliente, text: c.nombre_cliente };
                    })
                };
            },
            cache: true
        },
        placeholder: 'Buscar cliente...',
        minimumInputLength: 0,
        width: '100%'
    });

    if (clienteVal) {
        $.ajax({
            url: '/inspecciones/api/clientes',
            dataType: 'json',
            success: function(data) {
                var found = data.find(function(c) { return c.id_cliente == clienteVal; });
                if (found) {
                    var opt = new Option(found.nombre_cliente, found.id_cliente, true, true);
                    $('#selectCliente').append(opt).trigger('change');
                }
            }
        });
    }

    // Photo buttons
    document.querySelectorAll('.btn-photo-camera').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var input = this.closest('.photo-input-group').querySelector('.file-preview');
            input.setAttribute('capture', 'environment');
            input.click();
        });
    });
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
});

function openPhoto(src) {
    Swal.fire({ imageUrl: src, imageAlt: 'Foto', showConfirmButton: false, showCloseButton: true, width: 'auto' });
}
</script>
