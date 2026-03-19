<?php
$aprobadosTotal = array_filter($respuestas, fn($r) => $r['calificacion'] >= 70);
$evalUrl        = base_url('evaluar/' . $evaluacion['token']);
$esActiva       = $evaluacion['estado'] === 'activo';
?>
<div class="container-fluid px-3">
    <div class="d-flex align-items-center gap-2 mt-2 mb-3">
        <a href="<?= base_url('/inspecciones/evaluacion-induccion') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left"></i></a>
        <h6 class="mb-0" style="font-size:15px; font-weight:700;"><?= esc($evaluacion['titulo']) ?></h6>
    </div>

    <?php if (session()->getFlashdata('msg')): ?>
    <div class="alert alert-success" style="font-size:13px;"><?= session()->getFlashdata('msg') ?></div>
    <?php endif; ?>

    <!-- INFO + ACCIONES -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div style="font-size:13px; color:#888;"><?= esc($evaluacion['titulo']) ?></div>
                    <div style="font-size:11px; color:#aaa;">Creada <?= date('d/m/Y', strtotime($evaluacion['created_at'])) ?></div>
                </div>
                <span class="badge bg-<?= $esActiva ? 'success' : 'secondary' ?>"><?= $esActiva ? 'Activa' : 'Cerrada' ?></span>
            </div>
            <div class="d-flex gap-2 mt-2 flex-wrap">
                <a href="<?= base_url('/inspecciones/evaluacion-induccion/edit/') ?><?= $evaluacion['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i> Editar</a>
                <a href="<?= base_url('/inspecciones/evaluacion-induccion/toggle/') ?><?= $evaluacion['id'] ?>" class="btn btn-sm btn-outline-<?= $esActiva ? 'warning' : 'success' ?>">
                    <i class="fas fa-<?= $esActiva ? 'lock' : 'unlock' ?>"></i> <?= $esActiva ? 'Cerrar' : 'Reabrir' ?>
                </a>
            </div>
        </div>
    </div>

    <!-- QR + ENLACE -->
    <div class="card mb-3">
        <div class="card-body text-center">
            <h6 class="card-title" style="font-size:13px; color:#999; text-transform:uppercase;">QR · Mismo código para todas las sesiones</h6>
            <?php if (!empty($qrBase64)): ?>
            <div class="my-2">
                <img src="<?= $qrBase64 ?>" alt="QR" style="width:65vw; max-width:240px; height:auto; border:2px solid #e0e0e0; border-radius:10px; padding:8px; background:#fff;">
            </div>
            <?php endif; ?>
            <div class="input-group input-group-sm mx-auto mt-2" style="max-width:460px;">
                <input type="text" class="form-control" id="evalLinkInput" value="<?= esc($evalUrl) ?>" readonly style="font-size:11px;">
                <button class="btn btn-outline-secondary" onclick="copyLink()"><i class="fas fa-copy"></i></button>
            </div>
            <small class="text-muted d-block mt-1" style="font-size:11px;">El asistente selecciona su conjunto al responder</small>
        </div>
    </div>

    <!-- RESUMEN GLOBAL -->
    <div class="card mb-3">
        <div class="card-body">
            <h6 style="font-size:12px; color:#999; text-transform:uppercase; margin-bottom:10px;">Resumen global</h6>
            <div class="row text-center g-0">
                <div class="col-3">
                    <div style="font-size:24px; font-weight:800; color:#1a2340;"><?= count($respuestas) ?></div>
                    <div style="font-size:10px; color:#999;">Respondieron</div>
                </div>
                <div class="col-3">
                    <div style="font-size:24px; font-weight:800; color:#bd9751;"><?= number_format($promedio, 1) ?>%</div>
                    <div style="font-size:10px; color:#999;">Promedio</div>
                </div>
                <div class="col-3">
                    <div style="font-size:24px; font-weight:800; color:#28a745;"><?= count($aprobadosTotal) ?></div>
                    <div style="font-size:10px; color:#999;">Aprobados</div>
                </div>
                <div class="col-3">
                    <div style="font-size:24px; font-weight:800; color:#6c757d;"><?= count($sesiones) ?></div>
                    <div style="font-size:10px; color:#999;">Clientes</div>
                </div>
            </div>
        </div>
    </div>

    <!-- RESULTADOS POR CLIENTE / SESIÓN -->
    <?php if (!empty($sesiones)): ?>
    <h6 style="font-size:12px; color:#999; text-transform:uppercase; margin-bottom:8px;">
        <i class="fas fa-building me-1"></i>Resultados por cliente
    </h6>

    <?php foreach ($sesiones as $s): ?>
    <div class="card mb-3" style="border-left: 4px solid <?= $s['promedio'] >= 70 ? '#28a745' : '#dc3545' ?>;">
        <div class="card-body pb-2">
            <!-- Header sesión -->
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <div style="font-size:14px; font-weight:700; color:#1a2340;"><?= esc($s['nombre_cliente']) ?></div>
                    <div style="font-size:12px; color:#888;">
                        <i class="fas fa-calendar me-1"></i><?= date('d/m/Y', strtotime($s['fecha_sesion'])) ?>
                    </div>
                </div>
                <div class="text-end">
                    <div style="font-size:10px; color:#999; font-weight:600; letter-spacing:0.5px;">CÓDIGO</div>
                    <div style="font-size:15px; font-weight:800; color:#bd9751; font-family:monospace;"><?= esc($s['codigo']) ?></div>
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="d-flex gap-3 mb-2" style="font-size:12px;">
                <span><i class="fas fa-users me-1 text-muted"></i><?= $s['total'] ?> resp.</span>
                <span style="font-weight:700; color:<?= $s['promedio'] >= 70 ? '#28a745' : '#dc3545' ?>;">
                    <?= number_format($s['promedio'], 1) ?>% prom.
                </span>
                <span style="color:#28a745;"><i class="fas fa-check me-1"></i><?= $s['aprobados'] ?>/<?= $s['total'] ?> aprobados</span>
            </div>

            <!-- Tabla de resultados -->
            <?php if (!empty($s['respuestas'])): ?>
            <button class="btn btn-sm btn-outline-secondary w-100 mb-2" type="button"
                data-bs-toggle="collapse" data-bs-target="#sesion-<?= $s['id'] ?>">
                <i class="fas fa-table me-1"></i>Ver calificaciones individuales
            </button>
            <div class="collapse" id="sesion-<?= $s['id'] ?>">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0" style="font-size:11px;">
                        <thead class="table-light">
                            <tr><th>#</th><th>Nombre</th><th>Cédula</th><th>Cargo</th><th class="text-center">Calif.</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($s['respuestas'] as $i => $r):
                            $aprobado = $r['calificacion'] >= 70; ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= esc($r['nombre']) ?></td>
                            <td><?= esc($r['cedula']) ?></td>
                            <td><?= esc($r['cargo']) ?></td>
                            <td class="text-center fw-bold <?= $aprobado ? 'text-success' : 'text-danger' ?>">
                                <?= number_format($r['calificacion'], 1) ?>%
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- SIN CLIENTE ASIGNADO -->
    <?php if (!empty($sinCliente)): ?>
    <div class="card mb-3 border-warning">
        <div class="card-body">
            <div style="font-size:13px; font-weight:600; color:#856404; margin-bottom:8px;">
                <i class="fas fa-exclamation-triangle me-1"></i>Sin conjunto asignado (<?= count($sinCliente) ?>)
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0" style="font-size:11px;">
                    <thead class="table-light">
                        <tr><th>#</th><th>Nombre</th><th>Cédula</th><th>Cargo</th><th>Empresa</th><th class="text-center">Calif.</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($sinCliente as $i => $r):
                        $aprobado = $r['calificacion'] >= 70; ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= esc($r['nombre']) ?></td>
                        <td><?= esc($r['cedula']) ?></td>
                        <td><?= esc($r['cargo']) ?></td>
                        <td><?= esc($r['empresa_contratante']) ?></td>
                        <td class="text-center fw-bold <?= $aprobado ? 'text-success' : 'text-danger' ?>">
                            <?= number_format($r['calificacion'], 1) ?>%
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (empty($sesiones) && empty($sinCliente)): ?>
    <div class="text-center py-4 text-muted">
        <i class="fas fa-inbox fa-2x mb-2" style="opacity:0.3;"></i>
        <p style="font-size:13px;">Aún no hay respuestas registradas.</p>
    </div>
    <?php endif; ?>
</div>

<script>
function copyLink() {
    navigator.clipboard.writeText(document.getElementById('evalLinkInput').value)
        .then(() => Swal.fire({ icon:'success', title:'Copiado', timer:1200, showConfirmButton:false }));
}
</script>
