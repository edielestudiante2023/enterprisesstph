<?php
$mesesNombres = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',
                 7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
$mesesIconos  = [1=>'snowflake',2=>'heart',3=>'wind',4=>'seedling',5=>'sun',6=>'umbrella-beach',
                 7=>'sun',8=>'sun',9=>'leaf',10=>'ghost',11=>'cloud-rain',12=>'gifts'];

$backParams = $tipo === 'interno'
    ? 'tipo=interno&id=' . urlencode($id)
    : 'tipo=externo&nombre=' . urlencode($nombre);
?>
<div class="container-fluid px-3">
    <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
        <div>
            <a href="<?= base_url('/inspecciones/agendamiento/anios?' . $backParams) ?>" class="text-muted" style="font-size:13px; text-decoration:none;">
                <i class="fas fa-arrow-left me-1"></i> <?= esc($nombreConsultor) ?>
            </a>
            <h6 class="mb-0 mt-1"><i class="fas fa-calendar me-1" style="color:var(--gold-primary);"></i> <?= $anio ?></h6>
        </div>
        <a href="<?= base_url('/inspecciones/agendamiento/create') ?>" class="btn btn-sm btn-pwa-primary" style="width:auto; padding: 8px 16px;">
            <i class="fas fa-plus"></i> Nuevo
        </a>
    </div>

    <?php if (empty($meses)): ?>
        <div class="text-center text-muted py-5">
            <i class="fas fa-calendar-times fa-3x mb-3" style="opacity:0.3;"></i>
            <p>Sin agendamientos para este a&ntilde;o</p>
        </div>
    <?php else: ?>
        <div class="mb-2" style="font-size:13px; color:#888;">Seleccione un mes</div>
        <div class="row g-2">
        <?php foreach ($meses as $m): ?>
        <?php
            $mesNum = (int)$m['mes'];
            $detalleParams = $backParams . '&anio=' . $anio . '&mes=' . $mesNum;
        ?>
        <div class="col-6">
            <a href="<?= base_url('/inspecciones/agendamiento/detalle?' . $detalleParams) ?>"
               class="card card-inspeccion" style="text-decoration:none; color:inherit; display:block;">
                <div class="card-body py-3 px-3 text-center">
                    <i class="fas fa-<?= $mesesIconos[$mesNum] ?? 'calendar-day' ?> mb-1" style="font-size:20px; color:var(--gold-primary);"></i>
                    <div style="font-size:15px; font-weight:600;"><?= $mesesNombres[$mesNum] ?? $mesNum ?></div>
                    <div class="d-flex justify-content-center gap-1 mt-2 flex-wrap" style="font-size:11px;">
                        <span class="badge bg-dark"><?= $m['total'] ?></span>
                        <?php if ($m['pendientes'] > 0): ?>
                            <span class="badge bg-warning text-dark"><?= $m['pendientes'] ?></span>
                        <?php endif; ?>
                        <?php if ($m['confirmados'] > 0): ?>
                            <span class="badge bg-success"><?= $m['confirmados'] ?></span>
                        <?php endif; ?>
                        <?php if ($m['completados'] > 0): ?>
                            <span class="badge bg-primary"><?= $m['completados'] ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
