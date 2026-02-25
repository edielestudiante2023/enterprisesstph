<?php
$colorMap = [
    'danger'    => ['border' => '#dc3545', 'badge_bg' => '#dc3545', 'badge_text' => '#fff'],
    'warning'   => ['border' => '#ffc107', 'badge_bg' => '#ffc107', 'badge_text' => '#333'],
    'gold'      => ['border' => '#bd9751', 'badge_bg' => '#bd9751', 'badge_text' => '#fff'],
    'success'   => ['border' => '#28a745', 'badge_bg' => '#28a745', 'badge_text' => '#fff'],
    'secondary' => ['border' => '#6c757d', 'badge_bg' => '#6c757d', 'badge_text' => '#fff'],
];
?>

<div class="page-header">
    <h1><i class="fas fa-wrench me-2"></i> Mantenimientos</h1>
    <a href="<?= base_url('client/inspecciones') ?>" class="btn-back">
        <i class="fas fa-arrow-left me-1"></i> Volver
    </a>
</div>

<?php if (empty($vencimientos)): ?>
<div class="card">
    <div class="card-body text-center py-5">
        <i class="fas fa-wrench" style="font-size:3rem; color:#ccc;"></i>
        <h5 class="mt-3 text-muted">No hay mantenimientos registrados</h5>
    </div>
</div>
<?php else: ?>

<!-- Filtros -->
<div class="d-flex gap-2 mb-3" style="overflow-x:auto; padding-bottom:4px;">
    <button class="btn btn-sm filter-btn active" data-filter="all" style="white-space:nowrap; border-radius:20px; font-size:13px; font-weight:600; border:2px solid #bd9751; background:#bd9751; color:white;">
        Todos (<?= count($vencimientos) ?>)
    </button>
    <?php
    $sinEjecutar = array_filter($vencimientos, fn($v) => $v['estado_actividad'] === 'sin ejecutar');
    $ejecutados = array_filter($vencimientos, fn($v) => $v['estado_actividad'] === 'ejecutado');
    $cerrados = array_filter($vencimientos, fn($v) => in_array($v['estado_actividad'], ['CERRADA', 'CERRADA POR FIN CONTRATO']));
    ?>
    <button class="btn btn-sm filter-btn" data-filter="sin ejecutar" style="white-space:nowrap; border-radius:20px; font-size:13px; font-weight:600; border:2px solid #bd9751; background:white; color:#bd9751;">
        Pendientes (<?= count($sinEjecutar) ?>)
    </button>
    <button class="btn btn-sm filter-btn" data-filter="ejecutado" style="white-space:nowrap; border-radius:20px; font-size:13px; font-weight:600; border:2px solid #bd9751; background:white; color:#bd9751;">
        Ejecutados (<?= count($ejecutados) ?>)
    </button>
    <?php if (count($cerrados) > 0): ?>
    <button class="btn btn-sm filter-btn" data-filter="cerrada" style="white-space:nowrap; border-radius:20px; font-size:13px; font-weight:600; border:2px solid #bd9751; background:white; color:#bd9751;">
        Cerrados (<?= count($cerrados) ?>)
    </button>
    <?php endif; ?>
</div>

<div id="cards-container">
    <?php foreach ($vencimientos as $v):
        $c = $colorMap[$v['color']] ?? $colorMap['secondary'];
        $estado = $v['estado_actividad'];
        $esCerrada = in_array($estado, ['CERRADA', 'CERRADA POR FIN CONTRATO']);
        $filterAttr = $esCerrada ? 'cerrada' : $estado;
    ?>
    <div class="card mb-2 maint-card" data-estado="<?= esc($filterAttr) ?>" style="border-left:4px solid <?= $c['border'] ?>;">
        <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-start">
                <strong style="font-size:14px; color:#1c2437;"><?= esc($v['detalle_mantenimiento'] ?? 'Sin detalle') ?></strong>
                <span class="badge" style="background:<?= $c['badge_bg'] ?>; color:<?= $c['badge_text'] ?>; font-size:11px;">
                    <?= esc($v['label']) ?>
                </span>
            </div>
            <div class="mt-1" style="font-size:13px; color:#555;">
                <?php if ($estado === 'sin ejecutar'): ?>
                    <i class="fas fa-calendar-alt me-1" style="color:#bd9751;"></i>
                    Vence: <?= date('d/m/Y', strtotime($v['fecha_vencimiento'])) ?>
                <?php else: ?>
                    <?php if (!empty($v['fecha_realizacion'])): ?>
                    <i class="fas fa-check-circle me-1" style="color:#28a745;"></i>
                    Realizado: <?= date('d/m/Y', strtotime($v['fecha_realizacion'])) ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <?php if (!empty($v['observaciones'])): ?>
            <div class="mt-1" style="font-size:12px; color:#777;">
                <i class="fas fa-comment-dots"></i> <?= esc($v['observaciones']) ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<style>
    .filter-btn.active {
        background: #bd9751 !important;
        color: white !important;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var btns = document.querySelectorAll('.filter-btn');
    var cards = document.querySelectorAll('.maint-card');

    btns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            btns.forEach(function(b) {
                b.classList.remove('active');
                b.style.background = 'white';
                b.style.color = '#bd9751';
            });
            this.classList.add('active');
            this.style.background = '#bd9751';
            this.style.color = 'white';

            var filter = this.dataset.filter;
            cards.forEach(function(card) {
                card.style.display = (filter === 'all' || card.dataset.estado === filter) ? '' : 'none';
            });
        });
    });
});
</script>
