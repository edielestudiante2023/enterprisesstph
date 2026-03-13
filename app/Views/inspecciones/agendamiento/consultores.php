<div class="container-fluid px-3">
    <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
        <h6 class="mb-0"><i class="fas fa-calendar-check me-1"></i> Agendamientos</h6>
        <a href="<?= base_url('/inspecciones/agendamiento/create') ?>" class="btn btn-sm btn-pwa-primary" style="width:auto; padding: 8px 16px;">
            <i class="fas fa-plus"></i> Nuevo
        </a>
    </div>

    <!-- Alertas -->
    <?php if (session()->getFlashdata('msg')): ?>
        <div class="alert alert-success alert-dismissible fade show py-2" style="font-size:13px;">
            <?= session()->getFlashdata('msg') ?>
            <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show py-2" style="font-size:13px;">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Consultores Internos -->
    <?php if (!empty($consultoresInternos)): ?>
    <div class="mb-3">
        <div class="d-flex align-items-center mb-2">
            <i class="fas fa-user-tie me-2" style="color: var(--gold-primary);"></i>
            <span style="font-size:14px; font-weight:600; color:#555;">Consultores</span>
        </div>
        <?php foreach ($consultoresInternos as $c): ?>
        <a href="<?= base_url('/inspecciones/agendamiento/anios?tipo=interno&id=' . $c['id_consultor']) ?>"
           class="card card-inspeccion mb-2" style="text-decoration:none; color:inherit; display:block;">
            <div class="card-body py-3 px-3">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <?php if (!empty($c['foto_consultor'])): ?>
                            <img src="<?= base_url('/uploads/' . $c['foto_consultor']) ?>" class="rounded-circle" width="45" height="45" style="object-fit:cover;">
                        <?php else: ?>
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:45px;height:45px;background:var(--primary-dark);">
                                <i class="fas fa-user-tie" style="color:var(--gold-primary);font-size:18px;"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div style="flex:1;">
                        <strong style="font-size:15px;"><?= esc($c['nombre_consultor']) ?></strong>
                        <div class="d-flex gap-2 mt-1 flex-wrap" style="font-size:12px;">
                            <span class="badge bg-dark"><?= $c['total'] ?> total</span>
                            <?php if ($c['pendientes'] > 0): ?>
                                <span class="badge bg-warning text-dark"><?= $c['pendientes'] ?> pend.</span>
                            <?php endif; ?>
                            <?php if ($c['confirmados'] > 0): ?>
                                <span class="badge bg-success"><?= $c['confirmados'] ?> conf.</span>
                            <?php endif; ?>
                            <?php if ($c['completados'] > 0): ?>
                                <span class="badge bg-primary"><?= $c['completados'] ?> comp.</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-muted"></i>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Consultores Externos -->
    <?php if (!empty($consultoresExternos)): ?>
    <div class="mb-3">
        <div class="d-flex align-items-center mb-2">
            <i class="fas fa-user-shield me-2" style="color: var(--gold-primary);"></i>
            <span style="font-size:14px; font-weight:600; color:#555;">Consultores Externos</span>
        </div>
        <?php foreach ($consultoresExternos as $c): ?>
        <a href="<?= base_url('/inspecciones/agendamiento/anios?tipo=externo&nombre=' . urlencode($c['consultor_externo'])) ?>"
           class="card card-inspeccion mb-2" style="text-decoration:none; color:inherit; display:block;">
            <div class="card-body py-3 px-3">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:45px;height:45px;background:#2c3e50;">
                            <i class="fas fa-user-shield" style="color:#e0c97f;font-size:18px;"></i>
                        </div>
                    </div>
                    <div style="flex:1;">
                        <strong style="font-size:15px;"><?= esc($c['consultor_externo']) ?></strong>
                        <div class="d-flex gap-2 mt-1 flex-wrap" style="font-size:12px;">
                            <span class="badge bg-dark"><?= $c['total'] ?> total</span>
                            <?php if ($c['pendientes'] > 0): ?>
                                <span class="badge bg-warning text-dark"><?= $c['pendientes'] ?> pend.</span>
                            <?php endif; ?>
                            <?php if ($c['confirmados'] > 0): ?>
                                <span class="badge bg-success"><?= $c['confirmados'] ?> conf.</span>
                            <?php endif; ?>
                            <?php if ($c['completados'] > 0): ?>
                                <span class="badge bg-primary"><?= $c['completados'] ?> comp.</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-muted"></i>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (empty($consultoresInternos) && empty($consultoresExternos)): ?>
        <div class="text-center text-muted py-5">
            <i class="fas fa-calendar-alt fa-3x mb-3" style="opacity:0.3;"></i>
            <p>No hay agendamientos</p>
            <a href="<?= base_url('/inspecciones/agendamiento/create') ?>" class="btn btn-pwa-primary" style="width:auto; padding: 8px 24px;">
                Crear primer agendamiento
            </a>
        </div>
    <?php endif; ?>
</div>
