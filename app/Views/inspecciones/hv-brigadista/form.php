<?php
$h = $hv;
$c = $cliente;
?>
<div class="container-fluid px-3">
    <?php if (session()->getFlashdata('msg')): ?>
    <div class="alert alert-success mt-2" style="font-size:14px;"><?= session()->getFlashdata('msg') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger mt-2" style="font-size:14px;"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <h5 class="mt-2 mb-3"><i class="fas fa-id-card-alt"></i> <?= esc($title) ?></h5>

    <form action="/inspecciones/hv-brigadista/update/<?= $h['id'] ?>" method="post" enctype="multipart/form-data" id="formHV">

        <!-- Cliente + Fecha -->
        <div class="card mb-2">
            <div class="card-header py-2" style="font-size:13px; font-weight:600;">
                <i class="fas fa-building"></i> Cliente / Fecha
            </div>
            <div class="card-body py-2">
                <div class="mb-2">
                    <label class="form-label" style="font-size:13px;">Cliente</label>
                    <input type="text" class="form-control form-control-sm" value="<?= esc($c['nombre_cliente'] ?? 'Sin cliente') ?>" disabled>
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:13px;">Fecha inscripcion</label>
                    <input type="date" name="fecha_inscripcion" class="form-control form-control-sm" value="<?= esc($h['fecha_inscripcion'] ?? $h['created_at'] ?? '') ?>">
                </div>
            </div>
        </div>

        <!-- Datos personales -->
        <div class="card mb-2">
            <div class="card-header py-2" style="font-size:13px; font-weight:600;">
                <i class="fas fa-user"></i> Datos Personales
            </div>
            <div class="card-body py-2">
                <div class="mb-2">
                    <label class="form-label" style="font-size:13px;">Nombre completo</label>
                    <input type="text" name="nombre_completo" class="form-control form-control-sm" value="<?= esc($h['nombre_completo'] ?? '') ?>">
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:13px;">Documento de identidad</label>
                    <input type="text" name="documento_identidad" class="form-control form-control-sm" value="<?= esc($h['documento_identidad'] ?? '') ?>">
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:13px;">Fecha de nacimiento</label>
                    <input type="date" name="f_nacimiento" id="f_nacimiento" class="form-control form-control-sm" value="<?= esc($h['f_nacimiento'] ?? '') ?>">
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:13px;">Edad</label>
                    <input type="number" name="edad" id="edad" class="form-control form-control-sm" value="<?= esc($h['edad'] ?? '') ?>" readonly style="background:#e9ecef;">
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:13px;">Email</label>
                    <input type="email" name="email" class="form-control form-control-sm" value="<?= esc($h['email'] ?? '') ?>">
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:13px;">Telefono</label>
                    <input type="text" name="telefono" class="form-control form-control-sm" value="<?= esc($h['telefono'] ?? '') ?>">
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:13px;">Direccion de residencia</label>
                    <input type="text" name="direccion_residencia" class="form-control form-control-sm" value="<?= esc($h['direccion_residencia'] ?? '') ?>">
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:13px;">EPS</label>
                    <input type="text" name="eps" class="form-control form-control-sm" value="<?= esc($h['eps'] ?? '') ?>">
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:13px;">Tipo de sangre (RH)</label>
                    <select name="rh" class="form-select form-select-sm">
                        <option value="">-- Seleccionar --</option>
                        <?php foreach (['O+','O-','A+','A-','B+','B-','AB+','AB-'] as $rh): ?>
                        <option value="<?= $rh ?>" <?= ($h['rh'] ?? '') === $rh ? 'selected' : '' ?>><?= $rh ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <label class="form-label" style="font-size:13px;">Peso (kg)</label>
                        <input type="number" name="peso" class="form-control form-control-sm" step="0.1" min="20" max="300" value="<?= esc($h['peso'] ?? '') ?>">
                    </div>
                    <div class="col-6">
                        <label class="form-label" style="font-size:13px;">Estatura (cm)</label>
                        <input type="number" name="estatura" class="form-control form-control-sm" step="0.1" min="80" max="250" value="<?= esc($h['estatura'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Foto brigadista -->
        <div class="card mb-2">
            <div class="card-header py-2" style="font-size:13px; font-weight:600;">
                <i class="fas fa-camera"></i> Foto del Brigadista
            </div>
            <div class="card-body py-2">
                <?php if (!empty($h['foto_brigadista'])): ?>
                <div class="mb-2 text-center">
                    <img src="/<?= esc($h['foto_brigadista']) ?>" style="max-height:160px; border-radius:8px;" id="fotoActual">
                    <div class="text-muted mt-1" style="font-size:11px;">Foto actual</div>
                </div>
                <?php endif; ?>
                <input type="file" name="foto_brigadista" class="form-control form-control-sm" accept="image/*">
                <small class="text-muted">Seleccione una nueva foto para reemplazar la actual</small>
            </div>
        </div>

        <!-- Estudios -->
        <div class="card mb-2">
            <div class="card-header py-2" style="font-size:13px; font-weight:600;">
                <i class="fas fa-graduation-cap"></i> Estudios
            </div>
            <div class="card-body py-2">
                <?php for ($i = 1; $i <= 3; $i++): ?>
                <div class="p-2 mb-2" style="background:#f8f9fa; border:1px solid #dee2e6; border-radius:8px;">
                    <strong style="font-size:12px;">Estudio <?= $i ?></strong>
                    <div class="mb-1">
                        <label class="form-label" style="font-size:12px;">Que estudio?</label>
                        <input type="text" name="estudios_<?= $i ?>" class="form-control form-control-sm" value="<?= esc($h['estudios_'.$i] ?? '') ?>" placeholder="Ej: Primeros Auxilios">
                    </div>
                    <div class="mb-1">
                        <label class="form-label" style="font-size:12px;">Donde?</label>
                        <input type="text" name="lugar_estudio_<?= $i ?>" class="form-control form-control-sm" value="<?= esc($h['lugar_estudio_'.$i] ?? '') ?>" placeholder="Ej: Cruz Roja, SENA">
                    </div>
                    <div>
                        <label class="form-label" style="font-size:12px;">Anio</label>
                        <input type="number" name="anio_estudio_<?= $i ?>" class="form-control form-control-sm" value="<?= esc($h['anio_estudio_'.$i] ?? '') ?>" min="1950" max="2030" placeholder="Ej: 2020">
                    </div>
                </div>
                <?php endfor; ?>
            </div>
        </div>

        <!-- Salud -->
        <div class="card mb-2">
            <div class="card-header py-2" style="font-size:13px; font-weight:600;">
                <i class="fas fa-heartbeat"></i> Informacion de Salud
            </div>
            <div class="card-body py-2">
                <div class="mb-2">
                    <label class="form-label" style="font-size:13px;">Enfermedades importantes</label>
                    <textarea name="enfermedades_importantes" class="form-control form-control-sm" rows="2"><?= esc($h['enfermedades_importantes'] ?? '') ?></textarea>
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:13px;">Medicamentos</label>
                    <textarea name="medicamentos" class="form-control form-control-sm" rows="2"><?= esc($h['medicamentos'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Cuestionario Medico PAR-Q -->
        <div class="card mb-2">
            <div class="card-header py-2" style="font-size:13px; font-weight:600;">
                <i class="fas fa-notes-medical"></i> Cuestionario Medico
            </div>
            <div class="card-body py-2">
                <?php
                $preguntas = [
                    'cardiaca'          => '1. Alguna vez su doctor le ha dicho que tenga alguna enfermedad cardiaca?',
                    'pechoactividad'    => '2. Siente dolor en el pecho cuando hace actividad fisica?',
                    'dolorpecho'        => '3. En el mes pasado ha tenido dolor en el pecho en reposo?',
                    'conciencia'        => '4. Pierde el equilibrio por mareo o ha perdido la conciencia?',
                    'huesos'            => '5. Problema de huesos o articulaciones que empeore con actividad fisica?',
                    'medicamentos_bool' => '6. Su medico prescribe medicamentos para presion, diabetes o condicion cardiaca?',
                    'actividadfisica'   => '7. Conoce otra razon por la cual no pueda realizar actividad fisica?',
                    'convulsiones'      => '8. Ha sufrido o sufre de convulsiones o epilepsia?',
                    'vertigo'           => '9. Sufre o ha sufrido de vertigo?',
                    'oidos'             => '10. Ha sufrido de enfermedades en los oidos?',
                    'lugarescerrados'   => '11. Miedo a lugares cerrados (ascensores, cuartos pequenos)?',
                    'miedoalturas'      => '12. Miedo a las alturas (aviones, puentes peatonales, terrazas)?',
                    'haceejercicio'     => '13. Hace ejercicio en la semana?',
                    'miedo_ver_sangre'  => '14. Sufre de miedo al ver sangre?',
                ];
                foreach ($preguntas as $campo => $label): ?>
                <div class="mb-2">
                    <label class="form-label" style="font-size:12px;"><?= $label ?></label>
                    <select name="<?= $campo ?>" class="form-select form-select-sm">
                        <option value="">--</option>
                        <option value="SI" <?= ($h[$campo] ?? '') === 'SI' ? 'selected' : '' ?>>SI</option>
                        <option value="NO" <?= ($h[$campo] ?? '') === 'NO' ? 'selected' : '' ?>>NO</option>
                    </select>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Restricciones y Actividad Fisica -->
        <div class="card mb-2">
            <div class="card-header py-2" style="font-size:13px; font-weight:600;">
                <i class="fas fa-running"></i> Restricciones y Actividad Fisica
            </div>
            <div class="card-body py-2">
                <div class="mb-2">
                    <label class="form-label" style="font-size:13px;">Restricciones medicas</label>
                    <textarea name="restricciones_medicas" class="form-control form-control-sm" rows="2"><?= esc($h['restricciones_medicas'] ?? '') ?></textarea>
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:13px;">Deportes y horas por semana</label>
                    <textarea name="deporte_semana" class="form-control form-control-sm" rows="2"><?= esc($h['deporte_semana'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Firma -->
        <div class="card mb-2">
            <div class="card-header py-2" style="font-size:13px; font-weight:600;">
                <i class="fas fa-signature"></i> Firma del Brigadista
            </div>
            <div class="card-body py-2">
                <?php if (!empty($h['firma'])): ?>
                <div class="mb-2 text-center">
                    <img src="/<?= esc($h['firma']) ?>" style="max-height:120px; border:1px solid #ccc; border-radius:8px;" id="firmaActual">
                    <div class="text-muted mt-1" style="font-size:11px;">Firma actual (solo se reemplaza si dibuja una nueva)</div>
                </div>
                <?php endif; ?>
                <p class="text-muted mb-2" style="font-size:12px;">Dibuje una nueva firma para reemplazar la actual:</p>
                <canvas id="canvasFirma" style="border:2px dashed #ccc; border-radius:8px; background:#fafafa; touch-action:none; cursor:crosshair; width:100%; height:160px;"></canvas>
                <input type="hidden" name="firma_imagen" id="firma_imagen" value="">
                <div class="mt-1">
                    <button type="button" id="btnLimpiarFirma" class="btn btn-sm btn-outline-secondary"><i class="fas fa-eraser"></i> Limpiar</button>
                </div>
            </div>
        </div>

        <!-- Botones -->
        <div class="d-flex gap-2 mt-3 mb-4">
            <button type="submit" class="btn btn-pwa btn-pwa-primary flex-fill">
                <i class="fas fa-save"></i> Guardar
            </button>
            <?php if (($h['estado'] ?? '') === 'borrador'): ?>
            <button type="submit" name="finalizar" value="1" class="btn btn-success flex-fill">
                <i class="fas fa-check"></i> Finalizar
            </button>
            <?php endif; ?>
            <a href="/inspecciones/hv-brigadista" class="btn btn-outline-secondary flex-fill">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-calcular edad desde fecha de nacimiento
    var fnac = document.getElementById('f_nacimiento');
    var edadInput = document.getElementById('edad');

    function calcEdad() {
        if (!fnac.value) return;
        var b = new Date(fnac.value), t = new Date();
        var e = t.getFullYear() - b.getFullYear();
        var m = t.getMonth() - b.getMonth();
        if (m < 0 || (m === 0 && t.getDate() < b.getDate())) e--;
        edadInput.value = e >= 0 ? e : '';
    }
    fnac.addEventListener('change', calcEdad);

    // ===== FIRMA: Canvas HTML5 =====
    var canvas = document.getElementById('canvasFirma');
    var ctx = canvas.getContext('2d');
    var dibujando = false;
    var tieneContenido = false;

    function initCanvas() {
        canvas.width = canvas.offsetWidth;
        canvas.height = 160;
        ctx.strokeStyle = '#000';
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
    }
    initCanvas();
    window.addEventListener('resize', function() { if (!tieneContenido) initCanvas(); });

    function getPos(e) {
        var rect = canvas.getBoundingClientRect();
        var x = (e.clientX || e.touches[0].clientX) - rect.left;
        var y = (e.clientY || e.touches[0].clientY) - rect.top;
        return { x: x, y: y };
    }

    function iniciarDibujo(e) {
        dibujando = true;
        var pos = getPos(e);
        ctx.beginPath();
        ctx.moveTo(pos.x, pos.y);
        e.preventDefault();
    }

    function dibujar(e) {
        if (!dibujando) return;
        var pos = getPos(e);
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
        tieneContenido = true;
        e.preventDefault();
    }

    function terminarDibujo() { dibujando = false; }

    canvas.addEventListener('mousedown', iniciarDibujo);
    canvas.addEventListener('mousemove', dibujar);
    canvas.addEventListener('mouseup', terminarDibujo);
    canvas.addEventListener('mouseleave', terminarDibujo);
    // Touch: multi-touch protection
    canvas.addEventListener('touchstart', function(e) {
        if (e.touches.length > 1) return;
        iniciarDibujo(e);
    });
    canvas.addEventListener('touchmove', function(e) {
        if (e.touches.length > 1) { terminarDibujo(); return; }
        dibujar(e);
    });
    canvas.addEventListener('touchend', terminarDibujo);

    document.getElementById('btnLimpiarFirma').addEventListener('click', function() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        tieneContenido = false;
        document.getElementById('firma_imagen').value = '';
    });

    // Validar pixeles oscuros (min 100)
    function contarPixelesOscuros() {
        var imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        var data = imageData.data;
        var count = 0;
        for (var i = 0; i < data.length; i += 4) {
            if (data[i] < 128 && data[i+1] < 128 && data[i+2] < 128 && data[i+3] > 50) count++;
        }
        return count;
    }

    // Al enviar form: capturar firma si canvas tiene contenido
    document.getElementById('formHV').addEventListener('submit', function(e) {
        if (tieneContenido) {
            if (contarPixelesOscuros() < 100) {
                e.preventDefault();
                Swal.fire('Firma muy pequena', 'Dibuje una firma mas visible o limpie el canvas', 'warning');
                return;
            }
            document.getElementById('firma_imagen').value = canvas.toDataURL('image/png');
        }
    });
});
</script>
