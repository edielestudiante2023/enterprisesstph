<div class="container-fluid px-3">
    <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
        <h6 class="mb-0" style="font-size:15px; font-weight:700;">Evaluaciones Inducción SST</h6>
        <a href="/inspecciones/evaluacion-induccion/create" class="btn btn-sm btn-pwa">
            <i class="fas fa-plus"></i> Nueva
        </a>
    </div>

    <?php if (session()->getFlashdata('msg')): ?>
    <div class="alert alert-success" style="font-size:13px;"><?= session()->getFlashdata('msg') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger" style="font-size:13px;"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <?php if (empty($evaluaciones)): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas fa-clipboard-check" style="font-size:48px; color:#ddd;"></i>
        <p class="mt-2" style="font-size:13px;">No hay evaluaciones creadas aún.</p>
    </div>
    <?php else: ?>
    <?php foreach ($evaluaciones as $e): ?>
    <a href="/inspecciones/evaluacion-induccion/view/<?= $e['id'] ?>" class="text-decoration-none">
        <div class="card mb-2" style="border-left:4px solid <?= $e['estado'] === 'activo' ? '#28a745' : '#999' ?>;">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div style="font-size:14px; font-weight:600; color:#222;">
                            <?= esc($e['nombre_cliente'] ?? 'Sin cliente') ?>
                        </div>
                        <div style="font-size:12px; color:#888;">
                            <?= esc($e['titulo']) ?> — <?= date('d/m/Y', strtotime($e['created_at'])) ?>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-<?= $e['estado'] === 'activo' ? 'success' : 'secondary' ?>" style="font-size:10px;">
                            <?= $e['estado'] === 'activo' ? 'Activa' : 'Cerrada' ?>
                        </span>
                        <div style="font-size:12px; color:#888; margin-top:2px;">
                            <i class="fas fa-users"></i> <?= $e['total_respuestas'] ?> respuesta(s)
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </a>
    <?php endforeach; ?>
    <?php endif; ?>
</div>
