<div class="container-fluid px-3">
    <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
        <h6 class="mb-0" style="font-size:15px; font-weight:700;">Temas de Evaluación</h6>
        <a href="<?= base_url('/inspecciones/evaluacion-tema/create') ?>" class="btn btn-sm btn-pwa">
            <i class="fas fa-plus"></i> Nuevo tema
        </a>
    </div>

    <?php if (session()->getFlashdata('msg')): ?>
    <div class="alert alert-success" style="font-size:13px;"><?= session()->getFlashdata('msg') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger" style="font-size:13px;"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="alert alert-info" style="font-size:12px;">
        <i class="fas fa-info-circle me-1"></i>
        Cada <strong>tema</strong> agrupa preguntas de un área específica (Inducción SST, Riesgo Locativo, etc.).
        Al crear una evaluación, se elige el tema — el QR permanece único por evaluación pero las preguntas vienen del tema.
    </div>

    <?php if (empty($temas)): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas fa-book" style="font-size:48px; color:#ddd;"></i>
        <p class="mt-2" style="font-size:13px;">No hay temas creados aún.</p>
    </div>
    <?php else: ?>
    <?php foreach ($temas as $t): ?>
    <div class="card mb-2" style="border-left:4px solid <?= $t['estado'] === 'activo' ? '#28a745' : '#aaa' ?>;">
        <div class="card-body py-2 px-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div style="font-size:14px; font-weight:600; color:#222;"><?= esc($t['nombre']) ?></div>
                    <?php if (!empty($t['descripcion'])): ?>
                    <div style="font-size:11px; color:#888;"><?= esc(mb_substr($t['descripcion'], 0, 80)) ?><?= strlen($t['descripcion']) > 80 ? '...' : '' ?></div>
                    <?php endif; ?>
                </div>
                <div class="text-end">
                    <span class="badge bg-<?= $t['estado'] === 'activo' ? 'success' : 'secondary' ?>" style="font-size:10px;">
                        <?= $t['estado'] === 'activo' ? 'Activo' : 'Inactivo' ?>
                    </span>
                    <div style="font-size:11px; color:#888; margin-top:3px;">
                        <i class="fas fa-question-circle"></i> <?= $t['total_preguntas'] ?> preguntas
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2 mt-2">
                <a href="<?= base_url('/inspecciones/evaluacion-tema/edit/') ?><?= $t['id'] ?>" class="btn btn-xs btn-outline-secondary" style="font-size:11px; padding:2px 8px;">
                    <i class="fas fa-edit"></i> Editar
                </a>
                <a href="<?= base_url('/inspecciones/evaluacion-tema/delete/') ?><?= $t['id'] ?>"
                   class="btn btn-xs btn-outline-danger" style="font-size:11px; padding:2px 8px;"
                   onclick="return confirm('¿Eliminar tema y todas sus preguntas? Esta acción no se puede deshacer.')">
                    <i class="fas fa-trash"></i> Eliminar
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>
