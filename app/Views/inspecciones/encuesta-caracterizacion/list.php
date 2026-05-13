<div class="container-fluid px-3">
    <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
        <h6 class="mb-0" style="font-size:15px; font-weight:700;">Items Nucleares SG-SST</h6>
        <a href="<?= base_url('/inspecciones/encuesta-caracterizacion/create') ?>" class="btn btn-sm btn-pwa">
            <i class="fas fa-plus"></i> Nueva
        </a>
    </div>

    <?php if (session()->getFlashdata('msg')): ?>
    <div class="alert alert-success" style="font-size:13px;"><?= session()->getFlashdata('msg') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger" style="font-size:13px;"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <?php if (empty($encuestas)): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas fa-qrcode" style="font-size:48px; color:#ddd;"></i>
        <p class="mt-2" style="font-size:13px;">No hay formularios creados aun.</p>
    </div>
    <?php else: ?>
        <?php foreach ($encuestas as $e): ?>
        <a href="<?= base_url('/inspecciones/encuesta-caracterizacion/view/' . $e['id']) ?>" class="text-decoration-none">
            <div class="card mb-2" style="border-left:4px solid <?= (int)$e['total_respuestas'] > 0 ? '#28a745' : '#cbd5e1' ?>;">
                <div class="card-body py-2 px-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div style="font-size:14px; font-weight:600; color:#222;"><?= esc($e['titulo']) ?></div>
                            <div style="font-size:12px; color:#888;">
                                <i class="fas fa-building me-1"></i><?= esc($e['nombre_cliente'] ?? 'Sin cliente') ?>
                            </div>
                            <div style="font-size:11px; color:#aaa;">
                                Creada <?= !empty($e['created_at']) ? date('d/m/Y', strtotime($e['created_at'])) : '-' ?>
                            </div>
                        </div>
                        <div class="text-end" style="font-size:12px; color:#6b7280;">
                            <i class="fas fa-users"></i> <?= (int)$e['total_respuestas'] ?> resp.
                            <div class="mt-1">
                                <span class="badge <?= ($e['estado'] ?? '') === 'activa' ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= esc($e['estado'] ?? '-') ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
