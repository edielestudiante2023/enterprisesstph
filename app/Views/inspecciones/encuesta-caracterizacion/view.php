<div class="container-fluid px-3">
    <div class="d-flex align-items-center gap-2 mt-2 mb-3">
        <a href="<?= base_url('/inspecciones/encuesta-caracterizacion') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h6 class="mb-0" style="font-size:15px; font-weight:700;"><?= esc($encuesta['titulo']) ?></h6>
    </div>

    <?php if (session()->getFlashdata('msg')): ?>
    <div class="alert alert-success" style="font-size:13px;"><?= session()->getFlashdata('msg') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger" style="font-size:13px;"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-body">
            <div style="font-size:14px; font-weight:700;"><?= esc($cliente['nombre_cliente'] ?? 'Sin cliente') ?></div>
            <div style="font-size:12px; color:#888;">Creada <?= !empty($encuesta['created_at']) ? date('d/m/Y', strtotime($encuesta['created_at'])) : '-' ?></div>
            <div class="d-flex gap-2 mt-2 flex-wrap">
                <a href="<?= base_url('/inspecciones/encuesta-caracterizacion/delete/' . $encuesta['id']) ?>"
                   class="btn btn-sm btn-outline-danger"
                   onclick="return confirm('Eliminar esta encuesta tambien borrara sus respuestas. Continuar?');">
                    <i class="fas fa-trash"></i> Eliminar
                </a>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body text-center">
            <h6 class="card-title" style="font-size:13px; color:#999; text-transform:uppercase;">QR Items Nucleares SG-SST</h6>
            <?php if (!empty($qrBase64)): ?>
            <div class="my-2">
                <img src="<?= $qrBase64 ?>" alt="QR" style="width:65vw; max-width:240px; height:auto; border:2px solid #e0e0e0; border-radius:10px; padding:8px; background:#fff;">
            </div>
            <?php endif; ?>
            <div class="input-group input-group-sm mx-auto mt-2" style="max-width:520px;">
                <input type="text" class="form-control" id="encuestaLinkInput" value="<?= esc($url) ?>" readonly style="font-size:11px;">
                <button class="btn btn-outline-secondary" type="button" onclick="copyEncuestaLink()"><i class="fas fa-copy"></i></button>
            </div>
            <small class="text-muted d-block mt-1" style="font-size:11px;">Enlace publico para diligenciar el formulario</small>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h6 style="font-size:12px; color:#999; text-transform:uppercase; margin-bottom:10px;">Resumen</h6>
            <div class="row text-center g-0">
                <div class="col-6">
                    <div style="font-size:24px; font-weight:800; color:#1a2340;"><?= count($respuestas) ?></div>
                    <div style="font-size:10px; color:#999;">Respuestas</div>
                </div>
                <div class="col-6">
                    <div style="font-size:24px; font-weight:800; color:#bd9751;"><?= count($preguntas) ?></div>
                    <div style="font-size:10px; color:#999;">Campos</div>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($respuestas)): ?>
    <div class="text-center py-4 text-muted">
        <i class="fas fa-inbox fa-2x mb-2" style="opacity:0.3;"></i>
        <p style="font-size:13px;">Aun no hay respuestas registradas.</p>
    </div>
    <?php else: ?>
        <?php foreach ($respuestas as $idx => $r): ?>
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong style="font-size:13px;">Respuesta #<?= count($respuestas) - $idx ?></strong>
                    <span class="text-muted" style="font-size:11px;">
                        <?= !empty($r['created_at']) ? date('d/m/Y H:i', strtotime($r['created_at'])) : '-' ?>
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0" style="font-size:11px;">
                        <tbody>
                        <?php foreach ($preguntas as $field => $label): ?>
                            <tr>
                                <th style="width:42%; background:#f8f9fa;"><?= esc($label) ?></th>
                                <td><?= esc($r[$field] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
function copyEncuestaLink() {
    navigator.clipboard.writeText(document.getElementById('encuestaLinkInput').value)
        .then(function() {
            Swal.fire({ icon:'success', title:'Copiado', timer:1200, showConfirmButton:false });
        });
}
</script>
