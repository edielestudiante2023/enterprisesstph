<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Firma Acta de Visita</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #bd9751 0%, #8b6914 100%); min-height: 100vh; }
        .firma-container { max-width: 600px; margin: 0 auto; padding: 15px; }
        .card-firma { border: none; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
        .header-acta { background: linear-gradient(135deg, #2c3e50 0%, #1a252f 100%); color: white; border-radius: 12px 12px 0 0; padding: 20px; text-align: center; }
        .header-acta h5 { margin: 0; font-size: 16px; }
        .header-acta p { margin: 5px 0 0; font-size: 13px; opacity: 0.8; }
        .firma-canvas { border: 2px dashed #ccc; border-radius: 8px; background: #fafafa; cursor: crosshair; width: 100%; touch-action: none; }
        .firma-canvas:hover { border-color: #bd9751; }
        .btn-firmar { background: linear-gradient(135deg, #28a745, #218838); border: none; padding: 12px 30px; font-size: 1rem; color: white; border-radius: 8px; width: 100%; }
        .rol-badge { font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; }
        .dato-acta { background: #f8f9fa; border-radius: 8px; padding: 12px; margin-bottom: 12px; font-size: 13px; }
        .dato-acta strong { color: #555; font-size: 11px; text-transform: uppercase; display: block; margin-bottom: 2px; }
    </style>
</head>
<body>

<div class="firma-container">
    <div class="card card-firma mt-3 mb-4">
        <!-- Header -->
        <div class="header-acta">
            <i class="fas fa-file-signature fa-2x mb-2"></i>
            <h5>Acta de Visita SST</h5>
            <p><?= esc($cliente['nombre_cliente'] ?? '') ?></p>
        </div>

        <div class="card-body p-3">
            <!-- Datos del acta -->
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <div class="dato-acta">
                        <strong>Fecha</strong>
                        <?= date('d/m/Y', strtotime($acta['fecha_visita'])) ?>
                    </div>
                </div>
                <div class="col-6">
                    <div class="dato-acta">
                        <strong>Hora</strong>
                        <?= date('g:i A', strtotime($acta['hora_visita'])) ?>
                    </div>
                </div>
                <div class="col-12">
                    <div class="dato-acta">
                        <strong>Motivo</strong>
                        <?= esc($acta['motivo']) ?>
                    </div>
                </div>
            </div>

            <!-- Rol firmante -->
            <div class="d-flex align-items-center gap-2 mb-3">
                <span class="badge bg-dark rol-badge">
                    <?php
                    $tipoLabel = ['administrador' => 'Administrador', 'vigia' => 'Vigia SST', 'consultor' => 'Consultor'];
                    echo $tipoLabel[$tipo] ?? ucfirst($tipo);
                    ?>
                </span>
                <?php if ($nombreFirmante): ?>
                    <span style="font-size:14px; font-weight:600;"><?= esc($nombreFirmante) ?></span>
                <?php endif; ?>
            </div>

            <div class="alert alert-warning py-2 mb-3" style="font-size:12px;">
                <i class="fas fa-info-circle me-1"></i>
                Al firmar, confirma su participación en esta visita de seguimiento SST y autoriza el tratamiento de sus datos personales (Ley 1581/2012).
            </div>

            <!-- Canvas firma -->
            <div class="mb-3">
                <label class="form-label fw-bold" style="font-size:14px;">Dibuje su firma</label>
                <canvas id="firmaCanvas" class="firma-canvas" height="200"></canvas>
                <div class="d-flex justify-content-end mt-1">
                    <button type="button" id="btnLimpiar" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-eraser"></i> Limpiar
                    </button>
                </div>
            </div>

            <!-- Botón firmar -->
            <button type="button" id="btnFirmar" class="btn btn-firmar">
                <i class="fas fa-signature"></i> Firmar Acta
            </button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var canvas = document.getElementById('firmaCanvas');
    var ctx    = canvas.getContext('2d');
    var drawing = false;
    var dpr    = window.devicePixelRatio || 1;

    function resizeCanvas() {
        var rect = canvas.getBoundingClientRect();
        canvas.width = rect.width * dpr;
        canvas.height = 200 * dpr;
        canvas.style.height = '200px';
        ctx.scale(dpr, dpr);
        ctx.strokeStyle = '#000';
        ctx.lineWidth = 3;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
    }
    resizeCanvas();

    function getPos(e) {
        var rect = canvas.getBoundingClientRect();
        var src = (e.touches && e.touches.length > 0) ? e.touches[0] : e;
        return { x: src.clientX - rect.left, y: src.clientY - rect.top };
    }

    function startDraw(e) {
        if (e.touches && e.touches.length > 1) return;
        drawing = true;
        var pos = getPos(e);
        ctx.beginPath();
        ctx.moveTo(pos.x, pos.y);
        e.preventDefault();
    }

    function draw(e) {
        if (!drawing) return;
        if (e.touches && e.touches.length > 1) { drawing = false; return; }
        var pos = getPos(e);
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
        e.preventDefault();
    }

    function stopDraw() { drawing = false; }

    canvas.addEventListener('mousedown', startDraw);
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('mouseup',   stopDraw);
    canvas.addEventListener('mouseleave',stopDraw);
    canvas.addEventListener('touchstart', startDraw, { passive: false });
    canvas.addEventListener('touchmove',  draw,       { passive: false });
    canvas.addEventListener('touchend',   stopDraw);

    document.getElementById('btnLimpiar').addEventListener('click', function() {
        ctx.clearRect(0, 0, canvas.width / dpr, canvas.height / dpr);
    });

    document.getElementById('btnFirmar').addEventListener('click', function() {
        // Validar píxeles
        var imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        var pixeles = 0;
        for (var i = 3; i < imageData.data.length; i += 4) {
            if (imageData.data[i] > 128) pixeles++;
        }
        if (pixeles < 100) {
            Swal.fire('Firma requerida', 'Por favor dibuje su firma en el recuadro.', 'warning');
            return;
        }

        var firmaImagen = canvas.toDataURL('image/png');

        Swal.fire({
            title: 'Confirmar firma',
            html: '<p style="font-size:13px;">Verifique que su firma es correcta:</p>' +
                  '<img src="' + firmaImagen + '" style="max-width:100%;border:1px solid #ddd;border-radius:6px;margin-top:8px;">',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, firmar',
            cancelButtonText: 'Repetir',
            confirmButtonColor: '#28a745',
        }).then(function(result) {
            if (!result.isConfirmed) return;

            Swal.fire({ title: 'Guardando firma...', allowOutsideClick: false, didOpen: function() { Swal.showLoading(); } });

            var formData = new FormData();
            formData.append('token', '<?= esc($token) ?>');
            formData.append('firma_imagen', firmaImagen);

            fetch('/acta-visita/procesar-firma-remota', {
                method: 'POST',
                body: formData,
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Firma registrada!',
                        text: 'Su firma ha sido guardada exitosamente.',
                        confirmButtonColor: '#28a745',
                        allowOutsideClick: false,
                    });
                } else {
                    Swal.fire('Error', data.error || 'No se pudo guardar la firma', 'error');
                }
            })
            .catch(function() {
                Swal.fire('Error', 'Error de conexión. Intente nuevamente.', 'error');
            });
        });
    });
});
</script>
</body>
</html>
