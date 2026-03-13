<div class="container-fluid px-3">
    <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
        <div>
            <a href="<?= base_url('/inspecciones/agendamiento') ?>" class="text-muted" style="font-size:13px; text-decoration:none;">
                <i class="fas fa-arrow-left me-1"></i> Consultores
            </a>
            <h6 class="mb-0 mt-1"><i class="fas fa-<?= $tipo === 'externo' ? 'user-shield' : 'user-tie' ?> me-1" style="color:var(--gold-primary);"></i> <?= esc($nombreConsultor) ?></h6>
        </div>
        <a href="<?= base_url('/inspecciones/agendamiento/create') ?>" class="btn btn-sm btn-pwa-primary" style="width:auto; padding: 8px 16px;">
            <i class="fas fa-plus"></i> Nuevo
        </a>
    </div>

    <?php if (empty($anios)): ?>
        <div class="text-center text-muted py-5">
            <i class="fas fa-calendar fa-3x mb-3" style="opacity:0.3;"></i>
            <p>Sin agendamientos para este consultor</p>
        </div>
    <?php else: ?>
        <div class="mb-2" style="font-size:13px; color:#888;">Seleccione un a&ntilde;o</div>
        <?php foreach ($anios as $a): ?>
        <?php
            $params = $tipo === 'interno'
                ? 'tipo=interno&id=' . urlencode($id) . '&anio=' . $a['anio']
                : 'tipo=externo&nombre=' . urlencode($nombre) . '&anio=' . $a['anio'];
        ?>
        <a href="<?= base_url('/inspecciones/agendamiento/meses?' . $params) ?>"
           class="card card-inspeccion mb-2" style="text-decoration:none; color:inherit; display:block;">
            <div class="card-body py-3 px-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div style="font-size:22px; font-weight:700; color:var(--primary-dark);">
                            <i class="fas fa-calendar me-2" style="color:var(--gold-primary);"></i><?= $a['anio'] ?>
                        </div>
                        <div class="d-flex gap-2 mt-1 flex-wrap" style="font-size:12px;">
                            <span class="badge bg-dark"><?= $a['total'] ?> total</span>
                            <?php if ($a['pendientes'] > 0): ?>
                                <span class="badge bg-warning text-dark"><?= $a['pendientes'] ?> pend.</span>
                            <?php endif; ?>
                            <?php if ($a['confirmados'] > 0): ?>
                                <span class="badge bg-success"><?= $a['confirmados'] ?> conf.</span>
                            <?php endif; ?>
                            <?php if ($a['completados'] > 0): ?>
                                <span class="badge bg-primary"><?= $a['completados'] ?> comp.</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-muted"></i>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
