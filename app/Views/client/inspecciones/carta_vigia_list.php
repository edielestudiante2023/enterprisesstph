<div class="page-header">
    <h1><i class="fas fa-user-shield me-2"></i> Cartas de Vigía</h1>
    <a href="<?= base_url('client/inspecciones') ?>" class="btn-back">
        <i class="fas fa-arrow-left me-1"></i> Volver
    </a>
</div>

<?php if (empty($cartas)): ?>
<div class="card">
    <div class="card-body text-center py-5">
        <i class="fas fa-user-shield" style="font-size:3rem; color:#ccc;"></i>
        <h5 class="mt-3 text-muted">No hay cartas de vigía firmadas</h5>
    </div>
</div>
<?php else: ?>
<div class="row g-3">
    <?php foreach ($cartas as $carta): ?>
    <div class="col-md-6 col-lg-4">
        <div class="card h-100" style="border-left:4px solid #28a745;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <strong style="font-size:14px; color:#1c2437;">
                        <i class="fas fa-user-shield" style="color:#17a2b8;"></i>
                        <?= esc($carta['nombre_vigia']) ?>
                    </strong>
                    <span class="badge bg-success" style="font-size:11px;">Firmada</span>
                </div>
                <div style="font-size:13px; color:#555;">
                    <div><i class="fas fa-id-card me-1" style="color:#bd9751;"></i> CC <?= esc($carta['documento_vigia']) ?></div>
                    <div><i class="fas fa-envelope me-1" style="color:#bd9751;"></i> <?= esc($carta['email_vigia']) ?></div>
                    <?php if (!empty($carta['telefono_vigia'])): ?>
                    <div><i class="fas fa-phone me-1" style="color:#bd9751;"></i> <?= esc($carta['telefono_vigia']) ?></div>
                    <?php endif; ?>
                </div>
                <?php if (!empty($carta['firma_fecha'])): ?>
                <div class="mt-2" style="font-size:12px; color:#28a745;">
                    <i class="fas fa-check-circle"></i>
                    Firmado: <?= date('d/m/Y H:i', strtotime($carta['firma_fecha'])) ?>
                </div>
                <?php endif; ?>
                <?php if (!empty($carta['codigo_verificacion'])): ?>
                <div style="font-size:11px; color:#999;">
                    Cod: <?= esc($carta['codigo_verificacion']) ?>
                </div>
                <?php endif; ?>
                <?php if (!empty($carta['ruta_pdf'])): ?>
                <div class="mt-3 text-end">
                    <a href="/inspecciones/carta-vigia/pdf/<?= $carta['id'] ?>" target="_blank"
                       style="font-size:12px; color:#bd9751; font-weight:600; text-decoration:none;">
                        <i class="fas fa-file-pdf"></i> Ver PDF <i class="fas fa-chevron-right ms-1"></i>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<style>
    .card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.12) !important;
    }
</style>
