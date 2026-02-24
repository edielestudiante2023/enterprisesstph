<div class="page-header">
    <h1><i class="fas fa-clipboard-check me-2"></i> Mis Inspecciones</h1>
    <a href="<?= base_url('/dashboard') ?>" class="btn-back">
        <i class="fas fa-arrow-left me-1"></i> Dashboard
    </a>
</div>

<div class="row g-4">
    <?php foreach ($tipos as $tipo): ?>
    <div class="col-md-4 col-lg-4">
        <a href="<?= $tipo['url'] ?>" style="text-decoration:none;">
            <div class="card h-100" style="border-left: 4px solid <?= $tipo['color'] ?>; transition: all 0.3s ease; cursor:pointer;">
                <div class="card-body text-center py-4">
                    <div style="width:60px; height:60px; border-radius:50%; background:<?= $tipo['color'] ?>; display:inline-flex; align-items:center; justify-content:center; margin-bottom:1rem;">
                        <i class="fas <?= $tipo['icono'] ?> text-white" style="font-size:1.5rem;"></i>
                    </div>
                    <h5 style="color:#1c2437; font-weight:700;"><?= esc($tipo['nombre']) ?></h5>
                    <div style="font-size:2rem; font-weight:700; color:<?= $tipo['color'] ?>;">
                        <?= $tipo['conteo'] ?>
                    </div>
                    <small class="text-muted">inspecciones completadas</small>
                    <?php if ($tipo['ultima']): ?>
                        <div class="mt-2" style="font-size:12px; color:#999;">
                            <i class="fas fa-calendar-alt me-1"></i>
                            Última: <?= date('d/m/Y', strtotime($tipo['ultima'][$tipo['campo_fecha']])) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </a>
    </div>
    <?php endforeach; ?>
</div>

<?php if (array_sum(array_column($tipos, 'conteo')) === 0): ?>
<div class="text-center mt-4">
    <div class="card">
        <div class="card-body py-5">
            <i class="fas fa-clipboard-list" style="font-size:3rem; color:#ccc;"></i>
            <h5 class="mt-3 text-muted">Aún no hay inspecciones completadas</h5>
            <p class="text-muted">Cuando su consultor finalice inspecciones, aparecerán aquí.</p>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
    }
</style>
