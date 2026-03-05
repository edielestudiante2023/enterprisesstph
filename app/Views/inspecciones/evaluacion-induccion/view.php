<?php
$aprobados  = array_filter($respuestas, fn($r) => $r['calificacion'] >= 70);
$evalUrl    = base_url('evaluar/' . $evaluacion['token']);
$esActiva   = $evaluacion['estado'] === 'activo';
?>
<div class="container-fluid px-3">
    <div class="d-flex align-items-center gap-2 mt-2 mb-3">
        <a href="/inspecciones/evaluacion-induccion" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left"></i></a>
        <h6 class="mb-0" style="font-size:15px; font-weight:700;">Evaluación Inducción</h6>
    </div>

    <?php if (session()->getFlashdata('msg')): ?>
    <div class="alert alert-success" style="font-size:13px;"><?= session()->getFlashdata('msg') ?></div>
    <?php endif; ?>

    <!-- INFO + ACCIONES -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div style="font-size:15px; font-weight:700; color:#1a2340;"><?= esc($cliente['nombre_cliente'] ?? '-') ?></div>
                    <div style="font-size:12px; color:#888;"><?= esc($evaluacion['titulo']) ?> — <?= date('d/m/Y', strtotime($evaluacion['created_at'])) ?></div>
                </div>
                <span class="badge bg-<?= $esActiva ? 'success' : 'secondary' ?>"><?= $esActiva ? 'Activa' : 'Cerrada' ?></span>
            </div>
            <div class="d-flex gap-2 mt-2 flex-wrap">
                <a href="/inspecciones/evaluacion-induccion/edit/<?= $evaluacion['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i> Editar</a>
                <a href="/inspecciones/evaluacion-induccion/toggle/<?= $evaluacion['id'] ?>" class="btn btn-sm btn-outline-<?= $esActiva ? 'warning' : 'success' ?>">
                    <i class="fas fa-<?= $esActiva ? 'lock' : 'unlock' ?>"></i> <?= $esActiva ? 'Cerrar' : 'Reabrir' ?>
                </a>
            </div>
        </div>
    </div>

    <!-- ENLACE + QR -->
    <div class="card mb-3">
        <div class="card-body text-center">
            <h6 class="card-title" style="font-size:13px; color:#999; text-transform:uppercase;">Enlace y QR para compartir</h6>

            <!-- QR grande, optimizado para escanear desde celular -->
            <?php if (!empty($qrBase64)): ?>
            <div class="my-3">
                <img src="<?= $qrBase64 ?>" alt="QR Evaluación"
                    style="width:70vw; max-width:280px; height:auto; border:2px solid #e0e0e0; border-radius:12px; padding:10px; background:#fff;">
            </div>
            <?php endif; ?>
            <div style="font-size:12px; color:#888; margin-bottom:10px;">Escanear para acceder a la evaluación</div>

            <!-- Enlace copiable -->
            <div class="input-group input-group-sm mx-auto" style="max-width:500px;">
                <input type="text" class="form-control form-control-sm" id="evalLinkInput" value="<?= esc($evalUrl) ?>" readonly style="font-size:11px;">
                <button class="btn btn-outline-secondary" type="button" onclick="copyLink()"><i class="fas fa-copy"></i></button>
            </div>
            <div class="mt-2">
                <a href="<?= esc($evalUrl) ?>" target="_blank" class="btn btn-sm btn-outline-primary" style="font-size:12px;">
                    <i class="fas fa-external-link-alt"></i> Abrir formulario
                </a>
            </div>
        </div>
    </div>

    <!-- RESUMEN -->
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="card-title" style="font-size:13px; color:#999; text-transform:uppercase;">Resumen</h6>
            <div class="row text-center">
                <div class="col-4">
                    <div style="font-size:28px; font-weight:800; color:#1a2340;"><?= count($respuestas) ?></div>
                    <div style="font-size:11px; color:#999;">Respondieron</div>
                </div>
                <div class="col-4">
                    <div style="font-size:28px; font-weight:800; color:#bd9751;"><?= number_format($promedio, 1) ?>%</div>
                    <div style="font-size:11px; color:#999;">Promedio</div>
                </div>
                <div class="col-4">
                    <div style="font-size:28px; font-weight:800; color:#28a745;"><?= count($aprobados) ?></div>
                    <div style="font-size:11px; color:#999;">Aprobados</div>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLA DE CALIFICACIONES -->
    <div class="card mb-3">
        <div class="card-body p-2">
            <h6 class="card-title px-2 pt-1" style="font-size:13px; color:#999; text-transform:uppercase;">Calificaciones</h6>
            <?php if (empty($respuestas)): ?>
            <p class="text-muted text-center py-3" style="font-size:13px;">Aún no hay respuestas registradas.</p>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm table-bordered" style="font-size:12px;">
                    <thead class="table-light">
                        <tr><th>#</th><th>Nombre</th><th>Cédula</th><th>Cargo</th><th class="text-center">Calif.</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($respuestas as $i => $r):
                            $aprobado = $r['calificacion'] >= 70;
                        ?>
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
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="4" class="text-end fw-bold">Promedio:</td>
                            <td class="text-center fw-bold"><?= number_format($promedio, 1) ?>%</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function copyLink() {
    var input = document.getElementById('evalLinkInput');
    navigator.clipboard.writeText(input.value).then(function() {
        Swal.fire({ icon: 'success', title: 'Copiado', text: 'Enlace copiado al portapapeles.', timer: 1500, showConfirmButton: false });
    });
}
</script>
