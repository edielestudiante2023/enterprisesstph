<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Protocolo Trabajo en Alturas - <?= esc($cliente['nombre_cliente'] ?? '') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #2c3e50 0%, #1a252f 100%); min-height: 100vh; }
        .firma-container { max-width: 650px; margin: 0 auto; padding: 15px; }
        .card-firma { border: none; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); }
        .header-protocolo { background: linear-gradient(135deg, #c0392b 0%, #96281b 100%); color: white; border-radius: 12px 12px 0 0; padding: 20px; text-align: center; }
        .firma-canvas { border: 2px dashed #ccc; border-radius: 8px; background: #fafafa; cursor: crosshair; width: 100%; touch-action: none; }
        .firma-canvas:hover { border-color: #c0392b; }
        .btn-firmar { background: linear-gradient(135deg, #28a745, #218838); border: none; padding: 12px 30px; font-size: 1rem; color: white; border-radius: 8px; width: 100%; }
        .btn-firmar:hover { background: linear-gradient(135deg, #218838, #1e7e34); color: white; }
        .risk-box { background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; padding: 12px; font-size: 13px; color: #721c24; }
        .info-box { background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 12px; font-size: 13px; color: #856404; }
        .action-box { background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; padding: 12px; font-size: 13px; color: #155724; }
        .protocolo-text { font-size: 13px; text-align: justify; line-height: 1.6; }
        .protocolo-text h6 { font-size: 14px; margin-top: 15px; }
    </style>
</head>
<body>

<div class="firma-container">
    <div class="card card-firma mt-3 mb-4">
        <div class="header-protocolo">
            <i class="fas fa-hard-hat fa-2x mb-2"></i>
            <h5 class="mb-1">Protocolo de Notificación de Trabajo en Alturas</h5>
            <p class="mb-0" style="font-size: 13px; opacity: 0.85;"><?= esc($cliente['nombre_cliente'] ?? '') ?></p>
        </div>

        <div class="card-body p-3">
            <!-- Datos del representante legal -->
            <div class="mb-3" style="font-size: 14px;">
                <strong>Representante Legal:</strong> <?= esc($cliente['nombre_rep_legal'] ?? 'No registrado') ?><br>
                <strong>NIT:</strong> <?= esc($cliente['nit_cliente'] ?? '') ?>
            </div>

            <!-- Contenido del protocolo -->
            <div class="protocolo-text mb-3">
                <div class="info-box mb-3">
                    <i class="fas fa-gavel"></i> <strong>Fundamento Legal:</strong> Resolución 4272 de 2021, Ministerio de Trabajo. Todo trabajo realizado a 1.50 metros o más sobre el nivel del piso requiere: personal con curso vigente de alturas, afiliación a EPS, ARL y pensión, permiso de trabajo documentado y equipos certificados.
                </div>

                <div class="risk-box mb-3">
                    <i class="fas fa-exclamation-triangle"></i> <strong>Responsabilidad:</strong> Si la copropiedad autoriza o permite trabajos en alturas con personal sin curso de alturas, sin EPS, sin ARL o sin seguridad social, y ocurre un accidente grave o mortal:
                    <ul class="mb-0 mt-1">
                        <li>El administrador como representante legal responde civil y penalmente</li>
                        <li>La ARL no cubre el accidente si el trabajador no está afiliado</li>
                        <li>La copropiedad asume la totalidad de costos médicos, indemnizaciones y sanciones</li>
                    </ul>
                </div>

                <div class="action-box mb-3">
                    <i class="fas fa-clipboard-check"></i> <strong>Protocolo:</strong> Antes de autorizar cualquier trabajo en alturas en las instalaciones de la copropiedad, el administrador DEBE notificar formalmente al consultor SST asignado por Cycloid Talent para verificar el cumplimiento de requisitos legales del contratista.
                </div>

                <p><strong>Cycloid Talent SAS</strong>, como consultor externo del SG-SST, <strong>no asume responsabilidad</strong> por accidentes derivados de trabajos en alturas que no hayan sido notificados formalmente, conforme a lo establecido en el contrato de prestación de servicios vigente.</p>

                <p style="font-size: 12px; color: #6c757d;">Al firmar este documento, usted declara haber sido informado sobre las obligaciones legales relacionadas con trabajo en alturas y acepta el protocolo de notificación establecido. Tratamiento de datos conforme a la Ley 1581 de 2012.</p>
            </div>

            <!-- Tabs: Dibujar o Subir -->
            <div class="mb-3">
                <label class="form-label fw-bold"><i class="fas fa-signature"></i> Firma del Representante Legal</label>
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabDibujar" role="tab"><i class="fas fa-pen"></i> Dibujar</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabSubir" role="tab"><i class="fas fa-upload"></i> Subir imagen</a></li>
                </ul>
                <div class="tab-content border border-top-0 rounded-bottom p-2">
                    <div class="tab-pane fade show active" id="tabDibujar" role="tabpanel">
                        <canvas id="firmaCanvas" class="firma-canvas" height="200"></canvas>
                        <div class="d-flex justify-content-end mt-1">
                            <button type="button" id="btnLimpiar" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-eraser"></i> Limpiar
                            </button>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="tabSubir" role="tabpanel">
                        <div class="text-center py-3">
                            <input type="file" id="inputFirmaFile" accept="image/*" class="form-control mb-2" style="max-width: 400px; margin: 0 auto;">
                            <img id="previewFirma" src="" alt="" style="max-width: 100%; max-height: 200px; border: 1px solid #ddd; border-radius: 6px; display: none; margin-top: 8px;">
                            <p style="font-size: 12px; color: #6c757d; margin-top: 5px;">Formatos: JPG, PNG. Fondo blanco preferido.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botón firmar -->
            <button type="button" id="btnFirmar" class="btn btn-firmar">
                <i class="fas fa-file-signature"></i> Firmar Protocolo
            </button>
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
    canvas.addEventListener('mouseup', stopDraw);
    canvas.addEventListener('mouseleave', stopDraw);
    canvas.addEventListener('touchstart', startDraw, { passive: false });
    canvas.addEventListener('touchmove', draw, { passive: false });
    canvas.addEventListener('touchend', stopDraw);

    document.getElementById('btnLimpiar').addEventListener('click', function() {
        ctx.clearRect(0, 0, canvas.width / dpr, canvas.height / dpr);
    });

    // Preview de imagen subida
    var uploadedImage = null;
    document.getElementById('inputFirmaFile').addEventListener('change', function(e) {
        var file = e.target.files[0];
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function(ev) {
            uploadedImage = ev.target.result;
            var preview = document.getElementById('previewFirma');
            preview.src = uploadedImage;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    });

    document.getElementById('btnFirmar').addEventListener('click', function() {
        var tabSubirActivo = document.querySelector('#tabSubir.show.active') !== null;
        var firmaImagen = null;

        if (tabSubirActivo) {
            if (!uploadedImage) {
                Swal.fire('Firma requerida', 'Por favor seleccione una imagen de firma.', 'warning');
                return;
            }
            firmaImagen = uploadedImage;
        } else {
            var imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            var pixelesOscuros = 0;
            for (var i = 3; i < imageData.data.length; i += 4) {
                if (imageData.data[i] > 128) pixelesOscuros++;
            }
            if (pixelesOscuros < 100) {
                Swal.fire('Firma requerida', 'Por favor dibuje su firma en el recuadro.', 'warning');
                return;
            }
            firmaImagen = canvas.toDataURL('image/png');
        }

        Swal.fire({
            title: '¿Confirmar firma?',
            html: '<p style="font-size:13px;">Al confirmar, usted acepta haber sido informado sobre el protocolo de trabajo en alturas y las responsabilidades legales asociadas.</p>' +
                  '<img src="' + firmaImagen + '" style="max-width:100%;border:1px solid #ddd;border-radius:6px;margin-top:8px;">',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, firmar protocolo',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#28a745',
        }).then(function(result) {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Procesando firma...', allowOutsideClick: false, didOpen: function() { Swal.showLoading(); } });

                var formData = new FormData();
                formData.append('token', '<?= esc($token) ?>');
                formData.append('firma_imagen', firmaImagen);

                fetch('/protocolo-alturas/procesar-firma', {
                    method: 'POST',
                    body: formData
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Protocolo firmado',
                            html: '<p>Gracias por formalizar la adopción del protocolo de trabajo en alturas.</p><p>Recuerde: antes de autorizar cualquier trabajo en alturas, notifique a su consultor SST.</p>',
                            confirmButtonColor: '#28a745',
                            allowOutsideClick: false,
                        });
                    } else {
                        Swal.fire('Error', data.error || 'No se pudo procesar la firma', 'error');
                    }
                })
                .catch(function() {
                    Swal.fire('Error', 'Error de conexión. Intente nuevamente.', 'error');
                });
            }
        });
    });
});
</script>
</body>
</html>
