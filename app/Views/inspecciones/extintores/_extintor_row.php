<div class="card mb-2 extintor-row" style="border-left:3px solid #dc3545;">
    <div class="card-body p-2">
        <input type="hidden" name="ext_id[]" value="<?= $ext['id'] ?? '' ?>">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <strong style="font-size:13px;"><i class="fas fa-fire-extinguisher text-danger"></i> Extintor #<span class="ext-num"><?= $i + 1 ?></span></strong>
            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-ext" style="min-height:32px;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="row g-1">
            <?php foreach ($criterios as $key => $cfg): ?>
            <div class="col-6 mb-1">
                <label class="form-label" style="font-size:11px;"><?= $cfg['label'] ?></label>
                <select name="ext_<?= $key ?>[]" class="form-select form-select-sm" style="font-size:12px;">
                    <?php foreach ($cfg['opciones'] as $opt): ?>
                    <option value="<?= $opt ?>" <?= ($ext[$key] ?? $cfg['default']) === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="row g-2 mt-1">
            <div class="col-6">
                <label class="form-label" style="font-size:11px;">Fecha vencimiento</label>
                <input type="date" name="ext_fecha_vencimiento[]" class="form-control form-control-sm" value="<?= $ext['fecha_vencimiento'] ?? '' ?>">
            </div>
            <div class="col-6">
                <label class="form-label" style="font-size:11px;">Foto</label>
                <input type="file" name="ext_foto[]" class="foto-input-pwa" accept="image/*" data-label="Foto extintor"<?= !empty($ext['foto']) ? ' data-previous-url="' . base_url($ext['foto']) . '"' : '' ?>>
            </div>
        </div>
        <div class="mt-1">
            <label class="form-label" style="font-size:11px;">Observaciones</label>
            <input type="text" name="ext_observaciones[]" class="form-control form-control-sm" placeholder="Observaciones..." value="<?= esc($ext['observaciones'] ?? '') ?>">
        </div>
    </div>
</div>
