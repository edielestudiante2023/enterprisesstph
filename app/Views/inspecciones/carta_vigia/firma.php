<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Firma Carta Vigia - <?= esc($cliente['nombre_cliente'] ?? '') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #bd9751 0%, #8b6914 100%); min-height: 100vh; }
        .firma-container { max-width: 600px; margin: 0 auto; padding: 15px; }
        .card-firma { border: none; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
        .header-carta { background: linear-gradient(135deg, #2c3e50 0%, #1a252f 100%); color: white; border-radius: 12px 12px 0 0; padding: 20px; text-align: center; }
        .header-carta h5 { margin: 0; font-size: 16px; }
        .header-carta p { margin: 5px 0 0; font-size: 13px; opacity: 0.8; }
        .firma-canvas { border: 2px dashed #ccc; border-radius: 8px; background: #fafafa; cursor: crosshair; width: 100%; touch-action: none; }
        .firma-canvas:hover { border-color: #bd9751; }
        .btn-firmar { background: linear-gradient(135deg, #28a745, #218838); border: none; padding: 12px 30px; font-size: 1rem; color: white; border-radius: 8px; }
        .btn-firmar:hover { background: linear-gradient(135deg, #218838, #1e7e34); color: white; }
        .info-box { background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 12px; font-size: 12px; color: #856404; }
        .pdf-preview { border: 1px solid #ddd; border-radius: 8px; overflow: hidden; margin-bottom: 15px; }
        .pdf-preview iframe { width: 100%; height: 400px; border: none; }
    </style>
</head>
<body>

<div class="firma-container">
    <div class="card card-firma mt-3 mb-4">
        <!-- Header -->
        <div class="header-carta">
            <i class="fas fa-user-shield fa-2x mb-2"></i>
            <h5>Carta de Designacion - Vigia SST</h5>
            <p><?= esc($cliente['nombre_cliente'] ?? '') ?></p>
        </div>

        <div class="card-body p-3">
            <!-- Datos del vigía -->
            <div class="mb-3">
                <div style="font-size: 14px; font-weight: 600;"><?= esc($carta['nombre_vigia']) ?></div>
                <div class="text-muted" style="font-size: 13px;">CC <?= esc($carta['documento_vigia']) ?></div>
            </div>

            <!-- Preview PDF -->
            <?php if (!empty($pdfUrl)): ?>
            <div class="pdf-preview">
                <iframe src="<?= $pdfUrl ?>" title="Carta de designacion"></iframe>
            </div>
            <?php endif; ?>

            <div class="info-box mb-3">
                <i class="fas fa-info-circle"></i>
                Al firmar este documento, usted acepta su designacion como Vigia de Seguridad y Salud en el Trabajo
                y autoriza el tratamiento de sus datos personales conforme a la Ley 1581 de 2012.
            </div>

            <!-- Canvas de firma -->
            <div class="mb-3">
                <label class="form-label fw-bold">Su firma</label>
                <canvas id="firmaCanvas" class="firma-canvas" height="200"></canvas>
                <div class="d-flex justify-content-end mt-1">
                    <button type="button" id="btnLimpiar" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-eraser"></i> Limpiar
                    </button>
                </div>
            </div>

            <!-- Botón firmar -->
            <div class="d-grid">
                <button type="button" id="btnFirmar" class="btn btn-firmar">
                    <i class="fas fa-signature"></i> Firmar Carta
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var canvas = document.getElementById('firmaCanvas');
    var ctx = canvas.getContext('2d');
    var drawing = false;
    var dpr = window.devicePixelRatio || 1;

    // Ajustar canvas para alta DPI
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
        var clientX, clientY;
        if (e.touches && e.touches.length > 0) {
            clientX = e.touches[0].clientX;
            clientY = e.touches[0].clientY;
        } else {
            clientX = e.clientX;
            clientY = e.clientY;
        }
        return { x: clientX - rect.left, y: clientY - rect.top };
    }

    function startDraw(e) {
        // Protección pinch-zoom: ignorar multi-touch
        if (e.touches && e.touches.length > 1) return;
        drawing = true;
        var pos = getPos(e);
        ctx.beginPath();
        ctx.moveTo(pos.x, pos.y);
        e.preventDefault();
    }

    function draw(e) {
        if (!drawing) return;
        // Si se detecta multi-touch durante dibujo, detener
        if (e.touches && e.touches.length > 1) { drawing = false; return; }
        var pos = getPos(e);
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
        e.preventDefault();
    }

    function stopDraw() {
        drawing = false;
    }

    // Mouse events
    canvas.addEventListener('mousedown', startDraw);
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('mouseup', stopDraw);
    canvas.addEventListener('mouseleave', stopDraw);

    // Touch events
    canvas.addEventListener('touchstart', startDraw, { passive: false });
    canvas.addEventListener('touchmove', draw, { passive: false });
    canvas.addEventListener('touchend', stopDraw);

    // Limpiar
    document.getElementById('btnLimpiar').addEventListener('click', function() {
        ctx.clearRect(0, 0, canvas.width / dpr, canvas.height / dpr);
    });

    // Firmar
    document.getElementById('btnFirmar').addEventListener('click', function() {
        // Validar mínimo de píxeles oscuros
        var imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        var pixelesOscuros = 0;
        for (var i = 3; i < imageData.data.length; i += 4) {
            if (imageData.data[i] > 128) pixelesOscuros++;
        }

        if (pixelesOscuros < 100) {
            Swal.fire('Firma requerida', 'Por favor dibuje su firma en el recuadro.', 'warning');
            return;
        }

        var firmaImagen = canvas.toDataURL('image/png');

        // SweetAlert2 con preview
        Swal.fire({
            title: 'Confirmar firma?',
            html: '<p style="font-size:13px;">Al confirmar, acepta su designacion como Vigia SST y el tratamiento de sus datos personales.</p>' +
                  '<img src="' + firmaImagen + '" style="max-width:100%;border:1px solid #ddd;border-radius:6px;margin-top:8px;">',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Si, firmar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#28a745',
        }).then(function(result) {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Procesando firma...', allowOutsideClick: false, didOpen: function() { Swal.showLoading(); } });

                var formData = new FormData();
                formData.append('token', '<?= esc($token) ?>');
                formData.append('firma_imagen', firmaImagen);

                fetch('/carta-vigia/procesar-firma', {
                    method: 'POST',
                    body: formData
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Carta firmada exitosamente',
                            text: 'Su designacion como Vigia SST ha sido registrada.',
                            confirmButtonColor: '#28a745',
                            allowOutsideClick: false,
                        }).then(function() {
                            if (data.codigoVerificacion) {
                                window.location.href = '/carta-vigia/verificar/' + data.codigoVerificacion;
                            }
                        });
                    } else {
                        Swal.fire('Error', data.error || 'No se pudo procesar la firma', 'error');
                    }
                })
                .catch(function() {
                    Swal.fire('Error', 'Error de conexion. Intente nuevamente.', 'error');
                });
            }
        });
    });
});
</script>
</body>
</html>
