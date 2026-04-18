<?php
$isEdit = !empty($evaluacion);
$action = $isEdit
    ? base_url('/inspecciones/evaluacion-capacitacion/update/') . $evaluacion['id']
    : base_url('/inspecciones/evaluacion-capacitacion/store');
?>
<div class="container-fluid px-3">
    <div class="d-flex align-items-center gap-2 mt-2 mb-3">
        <a href="<?= base_url('/inspecciones/evaluacion-capacitacion') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left"></i></a>
        <h6 class="mb-0" style="font-size:15px; font-weight:700;"><?= $isEdit ? 'Editar' : 'Nueva' ?> Evaluación</h6>
    </div>

    <form method="post" action="<?= $action ?>">
        <?= csrf_field() ?>

        <div class="card mb-3">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label" style="font-size:13px;">Tema de preguntas *</label>
                    <select name="id_tema" id="selectTema" class="form-select form-select-sm" required>
                        <option value="">Seleccionar tema...</option>
                        <?php foreach ($temas as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= ($evaluacion['id_tema'] ?? '') == $t['id'] ? 'selected' : '' ?>>
                            <?= esc($t['nombre']) ?> (<?= $t['total_preguntas'] ?> preguntas)
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text" style="font-size:11px;">
                        <a href="<?= base_url('/inspecciones/evaluacion-tema') ?>" target="_blank">
                            <i class="fas fa-cog"></i> Gestionar temas y preguntas
                        </a>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" style="font-size:13px;">Cliente (opcional)</label>
                    <select name="id_cliente" id="selectCliente" class="form-select form-select-sm">
                        <option value="">— Transversal (todos los clientes) —</option>
                        <?php foreach ($clientes as $c): ?>
                        <option value="<?= $c['id_cliente'] ?>" <?= ($evaluacion['id_cliente'] ?? '') == $c['id_cliente'] ? 'selected' : '' ?>>
                            <?= esc($c['nombre_cliente']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text" style="font-size:11px;">Dejar vacío si la evaluación aplica a todos los clientes.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label" style="font-size:13px;">Título</label>
                    <input type="text" name="titulo" class="form-control form-control-sm"
                        value="<?= esc($evaluacion['titulo'] ?? 'Evaluación de Capacitación') ?>"
                        placeholder="Evaluación de Capacitación">
                </div>

                <?php if ($isEdit): ?>
                <div class="mb-2">
                    <label class="form-label" style="font-size:13px;">Estado</label>
                    <select name="estado" class="form-select form-select-sm">
                        <option value="activo" <?= ($evaluacion['estado'] ?? '') === 'activo' ? 'selected' : '' ?>>Activa (acepta respuestas)</option>
                        <option value="cerrado" <?= ($evaluacion['estado'] ?? '') === 'cerrado' ? 'selected' : '' ?>>Cerrada</option>
                    </select>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <button type="submit" class="btn btn-pwa btn-pwa-outline w-100">
            <i class="fas fa-save"></i> <?= $isEdit ? 'Actualizar' : 'Crear evaluación' ?>
        </button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function initSelect2() {
        if (typeof $ !== 'undefined' && $.fn.select2) {
            $('#selectCliente').select2({ placeholder: 'Seleccionar...', width: '100%' });
            $('#selectTema').select2({ placeholder: 'Seleccionar tema...', width: '100%' });
        } else {
            setTimeout(initSelect2, 50);
        }
    }
    initSelect2();
});
</script>
