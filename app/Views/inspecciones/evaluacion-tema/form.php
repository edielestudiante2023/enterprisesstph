<?php
$isEdit = !empty($tema);
$action = $isEdit
    ? base_url('/inspecciones/evaluacion-tema/update/') . $tema['id']
    : base_url('/inspecciones/evaluacion-tema/store');
?>
<div class="container-fluid px-3">
    <div class="d-flex align-items-center gap-2 mt-2 mb-3">
        <a href="<?= base_url('/inspecciones/evaluacion-tema') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left"></i></a>
        <h6 class="mb-0" style="font-size:15px; font-weight:700;"><?= $isEdit ? 'Editar' : 'Nuevo' ?> Tema</h6>
    </div>

    <?php if (session()->getFlashdata('msg')): ?>
    <div class="alert alert-success" style="font-size:13px;"><?= session()->getFlashdata('msg') ?></div>
    <?php endif; ?>

    <form method="post" action="<?= $action ?>" id="temaForm">
        <?= csrf_field() ?>

        <!-- ── INFO DEL TEMA ── -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 style="font-size:12px; color:#999; text-transform:uppercase; margin-bottom:12px;">Información del tema</h6>
                <div class="mb-3">
                    <label class="form-label" style="font-size:13px;">Nombre *</label>
                    <input type="text" name="nombre" class="form-control form-control-sm" required
                        value="<?= esc($tema['nombre'] ?? '') ?>"
                        placeholder="Ej: Inducción SST PH, Riesgo Locativo...">
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:13px;">Descripción</label>
                    <textarea name="descripcion" class="form-control form-control-sm" rows="2"
                        placeholder="Descripción breve del tema"><?= esc($tema['descripcion'] ?? '') ?></textarea>
                </div>
                <?php if ($isEdit): ?>
                <div class="mb-0">
                    <label class="form-label" style="font-size:13px;">Estado</label>
                    <select name="estado" class="form-select form-select-sm">
                        <option value="activo" <?= ($tema['estado'] ?? '') === 'activo' ? 'selected' : '' ?>>Activo</option>
                        <option value="inactivo" <?= ($tema['estado'] ?? '') === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                    </select>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ── PREGUNTAS ── -->
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 style="font-size:12px; color:#999; text-transform:uppercase; margin:0;">Preguntas</h6>
            <button type="button" class="btn btn-sm btn-pwa" id="btnAgregarPregunta">
                <i class="fas fa-plus"></i> Agregar pregunta
            </button>
        </div>

        <div id="preguntasContainer">
            <?php foreach ($preguntas as $i => $p): ?>
            <div class="card mb-3 pregunta-card" data-idx="<?= $i ?>">
                <div class="card-body pb-2">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span style="font-size:12px; font-weight:700; color:#bd9751;">Pregunta <?= $i + 1 ?></span>
                        <button type="button" class="btn btn-xs btn-outline-danger btn-eliminar-pregunta" style="font-size:11px; padding:1px 7px;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size:11px; color:#888;">Enunciado *</label>
                        <textarea name="p_texto[]" class="form-control form-control-sm" rows="2" required
                            placeholder="Escriba la pregunta..."><?= esc($p['texto']) ?></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size:11px; color:#888;">Opción A</label>
                        <input type="text" name="p_opc_a[]" class="form-control form-control-sm" value="<?= esc($p['opciones']['a'] ?? '') ?>" placeholder="Texto opción A">
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size:11px; color:#888;">Opción B</label>
                        <input type="text" name="p_opc_b[]" class="form-control form-control-sm" value="<?= esc($p['opciones']['b'] ?? '') ?>" placeholder="Texto opción B">
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size:11px; color:#888;">Opción C</label>
                        <input type="text" name="p_opc_c[]" class="form-control form-control-sm" value="<?= esc($p['opciones']['c'] ?? '') ?>" placeholder="Texto opción C">
                    </div>
                    <div class="mb-2">
                        <label class="form-label" style="font-size:11px; color:#888;">Opción D</label>
                        <input type="text" name="p_opc_d[]" class="form-control form-control-sm" value="<?= esc($p['opciones']['d'] ?? '') ?>" placeholder="Texto opción D">
                    </div>
                    <div class="mb-0">
                        <label class="form-label" style="font-size:11px; color:#888;">Respuesta correcta *</label>
                        <select name="p_correcta[]" class="form-select form-select-sm" required>
                            <option value="a" <?= ($p['correcta'] ?? '') === 'a' ? 'selected' : '' ?>>A</option>
                            <option value="b" <?= ($p['correcta'] ?? '') === 'b' ? 'selected' : '' ?>>B</option>
                            <option value="c" <?= ($p['correcta'] ?? '') === 'c' ? 'selected' : '' ?>>C</option>
                            <option value="d" <?= ($p['correcta'] ?? '') === 'd' ? 'selected' : '' ?>>D</option>
                        </select>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center text-muted py-3" id="emptyMsg" style="font-size:13px; <?= !empty($preguntas) ? 'display:none;' : '' ?>">
            <i class="fas fa-question-circle fa-2x mb-2" style="opacity:0.3;"></i>
            <p>Agrega al menos una pregunta.</p>
        </div>

        <button type="submit" class="btn btn-pwa btn-pwa-outline w-100 mb-4">
            <i class="fas fa-save"></i> <?= $isEdit ? 'Guardar cambios' : 'Crear tema' ?>
        </button>
    </form>
</div>

<template id="tplPregunta">
    <div class="card mb-3 pregunta-card">
        <div class="card-body pb-2">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="pregunta-num-label" style="font-size:12px; font-weight:700; color:#bd9751;">Pregunta</span>
                <button type="button" class="btn btn-xs btn-outline-danger btn-eliminar-pregunta" style="font-size:11px; padding:1px 7px;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mb-2">
                <label class="form-label" style="font-size:11px; color:#888;">Enunciado *</label>
                <textarea name="p_texto[]" class="form-control form-control-sm" rows="2" required placeholder="Escriba la pregunta..."></textarea>
            </div>
            <div class="mb-2">
                <label class="form-label" style="font-size:11px; color:#888;">Opción A</label>
                <input type="text" name="p_opc_a[]" class="form-control form-control-sm" placeholder="Texto opción A">
            </div>
            <div class="mb-2">
                <label class="form-label" style="font-size:11px; color:#888;">Opción B</label>
                <input type="text" name="p_opc_b[]" class="form-control form-control-sm" placeholder="Texto opción B">
            </div>
            <div class="mb-2">
                <label class="form-label" style="font-size:11px; color:#888;">Opción C</label>
                <input type="text" name="p_opc_c[]" class="form-control form-control-sm" placeholder="Texto opción C">
            </div>
            <div class="mb-2">
                <label class="form-label" style="font-size:11px; color:#888;">Opción D</label>
                <input type="text" name="p_opc_d[]" class="form-control form-control-sm" placeholder="Texto opción D">
            </div>
            <div class="mb-0">
                <label class="form-label" style="font-size:11px; color:#888;">Respuesta correcta *</label>
                <select name="p_correcta[]" class="form-select form-select-sm" required>
                    <option value="a">A</option>
                    <option value="b">B</option>
                    <option value="c">C</option>
                    <option value="d">D</option>
                </select>
            </div>
        </div>
    </div>
</template>

<script>
(function() {
    function renumerarPreguntas() {
        document.querySelectorAll('#preguntasContainer .pregunta-card').forEach(function(card, idx) {
            const lbl = card.querySelector('.pregunta-num-label');
            if (lbl) lbl.textContent = 'Pregunta ' + (idx + 1);
        });
        const empty = document.getElementById('emptyMsg');
        const count = document.querySelectorAll('#preguntasContainer .pregunta-card').length;
        empty.style.display = count === 0 ? '' : 'none';
    }

    document.getElementById('btnAgregarPregunta').addEventListener('click', function() {
        const tpl    = document.getElementById('tplPregunta');
        const clone  = tpl.content.cloneNode(true);
        document.getElementById('preguntasContainer').appendChild(clone);
        renumerarPreguntas();
    });

    document.getElementById('preguntasContainer').addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-eliminar-pregunta');
        if (!btn) return;
        btn.closest('.pregunta-card').remove();
        renumerarPreguntas();
    });

    renumerarPreguntas();
})();
</script>
