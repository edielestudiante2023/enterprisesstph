<?php
/**
 * @var array|null $registro  null = crear, array = editar
 */
$esEditar = !is_null($registro);
$action   = $esEditar
    ? base_url('/inspecciones/proveedor-servicio/update/' . $registro['id'])
    : base_url('/inspecciones/proveedor-servicio/store');

$v = function(string $campo, mixed $default = '') use ($registro) {
    return old($campo, $registro[$campo] ?? $default);
};
?>
<div class="container-fluid px-3">
    <form method="post" action="<?= $action ?>" id="formProveedor">
        <?= csrf_field() ?>

        <?php if ($flash = session()->getFlashdata('error')): ?>
        <div class="alert alert-danger mt-2" style="font-size:14px;"><?= esc($flash) ?></div>
        <?php endif; ?>

        <!-- Cliente -->
        <div class="mb-3 mt-2">
            <label class="form-label">Cliente *</label>
            <?php if ($esEditar): ?>
                <input type="hidden" name="id_cliente" value="<?= $v('id_cliente') ?>">
                <input type="text" class="form-control" value="<?= esc($registro['nombre_cliente'] ?? '') ?>" readonly>
            <?php else: ?>
                <select name="id_cliente" id="selectCliente" class="form-select" required>
                    <option value="">Seleccionar cliente...</option>
                </select>
            <?php endif; ?>
        </div>

        <!-- Tipo de servicio -->
        <div class="mb-3">
            <label class="form-label">Tipo de servicio *</label>
            <select name="tipo_servicio" id="selectTipo" class="form-select" required>
                <option value="">Seleccionar...</option>
                <option value="Aseo"       <?= $v('tipo_servicio') === 'Aseo'       ? 'selected' : '' ?>>Aseo</option>
                <option value="Vigilancia" <?= $v('tipo_servicio') === 'Vigilancia' ? 'selected' : '' ?>>Vigilancia</option>
                <option value="Otro"       <?= $v('tipo_servicio') === 'Otro'       ? 'selected' : '' ?>>Otro</option>
            </select>
        </div>

        <div class="mb-3" id="campoOtro" style="display:none;">
            <label class="form-label">Especificar tipo *</label>
            <input type="text" name="tipo_servicio_otro" class="form-control"
                placeholder="Ej: Mantenimiento locativo"
                value="<?= esc($v('tipo_servicio_otro')) ?>">
        </div>

        <!-- Razón social -->
        <div class="mb-3">
            <label class="form-label">Razón social *</label>
            <input type="text" name="razon_social" class="form-control"
                value="<?= esc($v('razon_social')) ?>" required
                placeholder="Nombre de la empresa proveedora">
        </div>

        <!-- NIT -->
        <div class="mb-3">
            <label class="form-label">NIT</label>
            <input type="text" name="nit" class="form-control"
                value="<?= esc($v('nit')) ?>"
                placeholder="900.123.456-7">
        </div>

        <hr class="my-3">
        <p class="form-label mb-2" style="font-weight:700;color:#1c2437;">Contacto empresa</p>

        <div class="row g-2 mb-3">
            <div class="col-12 col-md-6">
                <label class="form-label">Email empresa</label>
                <input type="email" name="email_empresa" class="form-control"
                    value="<?= esc($v('email_empresa')) ?>"
                    placeholder="contacto@empresa.com">
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label">Teléfono empresa</label>
                <input type="text" name="telefono_empresa" class="form-control"
                    value="<?= esc($v('telefono_empresa')) ?>"
                    placeholder="601 234 5678">
            </div>
        </div>

        <hr class="my-3">
        <p class="form-label mb-2" style="font-weight:700;color:#1c2437;">Responsable SST</p>

        <div class="mb-3">
            <label class="form-label">Nombre responsable SST</label>
            <input type="text" name="nombre_responsable_sst" class="form-control"
                value="<?= esc($v('nombre_responsable_sst')) ?>"
                placeholder="Nombre completo">
        </div>

        <div class="mb-3">
            <label class="form-label">Cargo responsable SST</label>
            <input type="text" name="cargo_responsable_sst" class="form-control"
                value="<?= esc($v('cargo_responsable_sst')) ?>"
                placeholder="Ej: Coordinador SST">
        </div>

        <div class="row g-2 mb-3">
            <div class="col-12 col-md-6">
                <label class="form-label">Email responsable SST</label>
                <input type="email" name="email_responsable_sst" class="form-control"
                    value="<?= esc($v('email_responsable_sst')) ?>"
                    placeholder="sst@empresa.com">
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label">Teléfono responsable SST</label>
                <input type="text" name="telefono_responsable_sst" class="form-control"
                    value="<?= esc($v('telefono_responsable_sst')) ?>"
                    placeholder="310 123 4567">
            </div>
        </div>

        <?php if ($esEditar): ?>
        <div class="mb-3">
            <label class="form-label">Estado</label>
            <select name="estado" class="form-select">
                <option value="activo"   <?= $v('estado') === 'activo'   ? 'selected' : '' ?>>Activo</option>
                <option value="inactivo" <?= $v('estado') === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
            </select>
        </div>
        <?php endif; ?>

        <div class="d-grid gap-3 mt-3 mb-5 pb-3">
            <button type="submit" class="btn btn-pwa btn-pwa-primary py-3" style="font-size:17px;">
                <i class="fas fa-save"></i> <?= $esEditar ? 'Actualizar' : 'Guardar' ?>
            </button>
            <a href="<?= base_url('/inspecciones/proveedor-servicio') ?>" class="btn btn-outline-secondary py-3" style="font-size:17px;">
                Cancelar
            </a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    <?php if (!$esEditar): ?>
    // Cargar clientes con Select2
    $.ajax({
        url: '<?= base_url('/inspecciones/api/clientes') ?>',
        dataType: 'json',
        success: function(data) {
            var select = document.getElementById('selectCliente');
            data.forEach(function(c) {
                var opt = document.createElement('option');
                opt.value = c.id_cliente;
                opt.textContent = c.nombre_cliente;
                select.appendChild(opt);
            });
            $('#selectCliente').select2({ placeholder: 'Buscar cliente...', allowClear: true, width: '100%' });
        }
    });
    <?php endif; ?>

    // Mostrar/ocultar campo "Otro"
    var selectTipo = document.getElementById('selectTipo');
    var campoOtro  = document.getElementById('campoOtro');
    var inputOtro  = campoOtro.querySelector('input');

    function toggleOtro() {
        var esOtro = selectTipo.value === 'Otro';
        campoOtro.style.display = esOtro ? 'block' : 'none';
        inputOtro.required = esOtro;
    }
    selectTipo.addEventListener('change', toggleOtro);
    toggleOtro(); // estado inicial (edición)
});
</script>
