<?php
$isEdit = !empty($procedimiento);
$action = $isEdit
    ? base_url('/inspecciones/procedimiento-emergencia-area/update/') . $procedimiento['id']
    : base_url('/inspecciones/procedimiento-emergencia-area/store');
?>
<div class="container-fluid px-3">
    <form method="post" action="<?= $action ?>" id="peaForm">
        <?= csrf_field() ?>

        <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger mt-2" style="font-size:14px;">
            <ul class="mb-0"><?php foreach ((array)session()->getFlashdata('errors') as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?></ul>
        </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('msg')): ?>
        <div class="alert alert-success mt-2" style="font-size:14px;"><?= session()->getFlashdata('msg') ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger mt-2" style="font-size:14px;"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <div class="card mt-2">
            <div class="card-body">
                <h6 class="mb-3">Datos generales</h6>
                <div class="mb-3">
                    <label class="form-label">Cliente *</label>
                    <select name="id_cliente" id="selectCliente" class="form-select" required <?= $isEdit ? 'disabled' : '' ?>>
                        <option value="">Seleccionar cliente...</option>
                    </select>
                    <?php if ($isEdit): ?><input type="hidden" name="id_cliente" value="<?= (int)$procedimiento['id_cliente'] ?>"><?php endif; ?>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label">Fecha elaboracion *</label>
                        <input type="date" name="fecha_elaboracion" class="form-control" value="<?= $procedimiento['fecha_elaboracion'] ?? date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Area *</label>
                        <select name="area" class="form-select" <?= $isEdit ? 'disabled' : '' ?>>
                            <?php foreach ($areasLabels as $v => $lbl): ?>
                            <option value="<?= $v ?>" <?= ($area ?? 'PISCINA') === $v ? 'selected' : '' ?>><?= esc($lbl) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($isEdit): ?><input type="hidden" name="area" value="<?= esc($procedimiento['area']) ?>"><?php endif; ?>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;">Nombre descriptivo del area</label>
                    <input type="text" name="nombre_area_descriptivo" class="form-control form-control-sm"
                           value="<?= esc($procedimiento['nombre_area_descriptivo'] ?? '') ?>"
                           placeholder="Ej: Piscina adultos Club House">
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-12 col-md-4"><label class="form-label" style="font-size:12px;">Responsable (nombre)</label><input type="text" name="responsable_area_nombre" class="form-control form-control-sm" value="<?= esc($procedimiento['responsable_area_nombre'] ?? '') ?>"></div>
                    <div class="col-6 col-md-4"><label class="form-label" style="font-size:12px;">Cargo</label><input type="text" name="responsable_area_cargo" class="form-control form-control-sm" value="<?= esc($procedimiento['responsable_area_cargo'] ?? '') ?>"></div>
                    <div class="col-6 col-md-4"><label class="form-label" style="font-size:12px;">Contacto</label><input type="text" name="responsable_area_contacto" class="form-control form-control-sm" value="<?= esc($procedimiento['responsable_area_contacto'] ?? '') ?>"></div>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-8"><label class="form-label" style="font-size:12px;">Horario de operacion</label><input type="text" name="horario_operacion" class="form-control form-control-sm" value="<?= esc($procedimiento['horario_operacion'] ?? '') ?>" placeholder="Ej: Mar-Vie 8am-10pm; Sab-Dom 7am-6pm"></div>
                    <div class="col-4"><label class="form-label" style="font-size:12px;">Aforo maximo</label><input type="number" min="0" name="aforo_maximo" class="form-control form-control-sm" value="<?= esc($procedimiento['aforo_maximo'] ?? '') ?>"></div>
                </div>
                <div class="mb-2"><label class="form-label" style="font-size:12px;">Telefonos de emergencia</label><textarea name="telefonos_emergencia" class="form-control form-control-sm" rows="2" placeholder="Bomberos 123, Cruz Roja, ambulancia, clinica..."><?= esc($procedimiento['telefonos_emergencia'] ?? '') ?></textarea></div>
                <div class="mb-2"><label class="form-label" style="font-size:12px;">Recursos disponibles en el area</label><textarea name="recursos_disponibles" class="form-control form-control-sm" rows="2" placeholder="DEA, botiquin tipo B, camilla rigida, radio, salvavidas..."><?= esc($procedimiento['recursos_disponibles'] ?? '') ?></textarea></div>
                <div class="mb-2"><label class="form-label" style="font-size:12px;">Observaciones de contexto</label><textarea name="observaciones_contexto" class="form-control form-control-sm" rows="2"><?= esc($procedimiento['observaciones_contexto'] ?? '') ?></textarea></div>
            </div>
        </div>

        <?php if ($isEdit): ?>
        <div class="card mt-3">
            <div class="card-body">
                <h6 class="mb-3">Escenarios de emergencia</h6>
                <div class="alert alert-info" style="font-size:12px;">
                    Usa el boton "Generar con IA" en cada escenario para autocompletar los 5 bloques (que hacer, que no hacer, cuando, quien, recursos). Revisa y ajusta manualmente, luego marca "Aprobado" para cada escenario.
                </div>

                <?php foreach ($escenarios as $idx => $esc): ?>
                <div class="escenario-block mb-3" data-id="<?= (int)$esc['id'] ?>">
                    <input type="hidden" name="esc_id[]" value="<?= (int)$esc['id'] ?>">
                    <input type="hidden" name="esc_codigo[]" value="<?= esc($esc['escenario_codigo']) ?>">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0"><?= $idx + 1 ?>. <span class="editable-title"><?= esc($esc['escenario_nombre']) ?></span></h6>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-ia" data-id="<?= (int)$esc['id'] ?>"><i class="fas fa-wand-magic-sparkles"></i> Generar con IA</button>
                            <label class="ms-2" style="font-size:12px;"><input type="checkbox" name="esc_aprobado[<?= $idx ?>]" value="1" <?= (int)$esc['aprobado_por_consultor'] === 1 ? 'checked' : '' ?>> Aprobado</label>
                        </div>
                    </div>
                    <input type="text" name="esc_nombre[]" class="form-control form-control-sm mb-2" value="<?= esc($esc['escenario_nombre']) ?>">

                    <div class="row g-2">
                        <div class="col-12 col-md-6"><label class="form-label" style="font-size:11px;">Que hacer</label><textarea name="esc_que_hacer[]" class="form-control form-control-sm esc-que-hacer" rows="4"><?= esc($esc['que_hacer'] ?? '') ?></textarea></div>
                        <div class="col-12 col-md-6"><label class="form-label" style="font-size:11px;">Que NO hacer</label><textarea name="esc_que_no_hacer[]" class="form-control form-control-sm esc-que-no-hacer" rows="4"><?= esc($esc['que_no_hacer'] ?? '') ?></textarea></div>
                        <div class="col-12 col-md-6"><label class="form-label" style="font-size:11px;">Cuando</label><textarea name="esc_cuando[]" class="form-control form-control-sm esc-cuando" rows="3"><?= esc($esc['cuando'] ?? '') ?></textarea></div>
                        <div class="col-12 col-md-6"><label class="form-label" style="font-size:11px;">Quien</label><textarea name="esc_quien[]" class="form-control form-control-sm esc-quien" rows="3"><?= esc($esc['quien'] ?? '') ?></textarea></div>
                        <div class="col-12"><label class="form-label" style="font-size:11px;">Recursos</label><textarea name="esc_recursos[]" class="form-control form-control-sm esc-recursos" rows="2"><?= esc($esc['recursos'] ?? '') ?></textarea></div>
                        <div class="col-12"><label class="form-label" style="font-size:11px;">Observaciones</label><textarea name="esc_observaciones[]" class="form-control form-control-sm" rows="2"><?= esc($esc['observaciones'] ?? '') ?></textarea></div>
                    </div>
                    <?php if ((int)$esc['generado_con_ia'] === 1): ?>
                    <div style="font-size:10px;color:#888;">Generado por IA (<?= esc($esc['modelo_ia'] ?? '') ?>) el <?= $esc['updated_at'] ?></div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-info mt-3" style="font-size:12px;">Al guardar, el sistema pre-cargara los escenarios estandar para el area seleccionada. Luego podras generarlos con IA uno a uno.</div>
        <?php endif; ?>

        <div class="d-grid gap-3 mt-3 mb-5 pb-3">
            <button type="submit" class="btn btn-pwa btn-pwa-outline py-3" style="font-size:17px;"><i class="fas fa-save"></i> Guardar</button>
            <?php if ($isEdit): ?>
            <button type="submit" name="finalizar" value="1" class="btn btn-pwa btn-pwa-primary py-3" style="font-size:17px;" id="btnFinalizar"><i class="fas fa-check-circle"></i> Finalizar y generar PDF</button>
            <?php endif; ?>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!$isEdit): ?>
    // Select2 clientes via AJAX
    const clienteIdPrefillPea = '<?= $idCliente ?? '' ?>';
    $.ajax({
        url: '<?= base_url('/inspecciones/api/clientes') ?>',
        dataType: 'json',
        success: function(data) {
            const select = document.getElementById('selectCliente');
            data.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id_cliente;
                opt.textContent = c.nombre_cliente;
                if (clienteIdPrefillPea && c.id_cliente == clienteIdPrefillPea) opt.selected = true;
                select.appendChild(opt);
            });
            $('#selectCliente').select2({ placeholder: 'Seleccionar cliente...', width: '100%' });
        }
    });
    <?php endif; ?>

    <?php if ($isEdit): ?>
    const URL_IA = '<?= base_url('/inspecciones/procedimiento-emergencia-area/generar-escenario-ia/' . $procedimiento['id']) ?>';
    const CSRF_NAME = '<?= csrf_token() ?>';
    const CSRF_HASH = '<?= csrf_hash() ?>';

    document.querySelectorAll('.btn-ia').forEach(btn => {
        btn.addEventListener('click', async function() {
            const id = this.dataset.id;
            const block = this.closest('.escenario-block');
            const original = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando...';
            this.disabled = true;

            const fd = new FormData();
            fd.append('id_escenario', id);
            fd.append(CSRF_NAME, CSRF_HASH);

            try {
                const resp = await fetch(URL_IA, { method: 'POST', body: fd });
                const data = await resp.json();
                if (!data.ok) throw new Error(data.error || 'Error IA');

                block.querySelector('.esc-que-hacer').value = data.data.que_hacer || '';
                block.querySelector('.esc-que-no-hacer').value = data.data.que_no_hacer || '';
                block.querySelector('.esc-cuando').value = data.data.cuando || '';
                block.querySelector('.esc-quien').value = data.data.quien || '';
                block.querySelector('.esc-recursos').value = data.data.recursos || '';

                Swal.fire({
                    icon: 'success', title: 'Escenario generado',
                    html: 'Tokens: ' + (data.tokens?.in || 0) + ' in / ' + (data.tokens?.out || 0) + ' out',
                    confirmButtonColor: '#bd9751', timer: 2500
                });
            } catch (err) {
                Swal.fire({ icon: 'error', title: 'Error IA', text: err.message });
            } finally {
                this.innerHTML = original;
                this.disabled = false;
            }
        });
    });

    document.getElementById('btnFinalizar')?.addEventListener('click', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Finalizar procedimiento?',
            html: 'Se generara el PDF. Debes tener al menos un escenario aprobado con contenido.',
            icon: 'question', showCancelButton: true, confirmButtonText: 'Si, finalizar',
            cancelButtonText: 'Cancelar', confirmButtonColor: '#bd9751',
        }).then(r => {
            if (r.isConfirmed) {
                const inp = document.createElement('input');
                inp.type = 'hidden'; inp.name = 'finalizar'; inp.value = '1';
                document.getElementById('peaForm').appendChild(inp);
                document.getElementById('peaForm').submit();
            }
        });
    });
    <?php endif; ?>
});
</script>

<style>
.escenario-block { border:1px solid #e6e6e6; border-radius:6px; padding:8px; background:#fafafa; }
.badge-borrador { background:#f39c12; color:#fff; }
.badge-completo { background:#27ae60; color:#fff; }
</style>
