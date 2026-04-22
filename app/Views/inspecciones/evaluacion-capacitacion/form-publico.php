<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($evaluacion['titulo']) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-5-theme/1.3.0/select2-bootstrap-5-theme.min.css">
    <style>
        body { background: #f5f6fa; font-family: 'Segoe UI', sans-serif; }
        .eval-header { background: #1a2340; color: #fff; padding: 20px 16px 16px; text-align: center; }
        .eval-header img { height: 48px; margin-bottom: 8px; display: block; margin: 0 auto 8px; }
        .eval-header h1 { font-size: 18px; font-weight: 700; margin: 0; }
        .eval-header p  { font-size: 13px; margin: 4px 0 0; color: #c9d1e0; }
        .card { border: none; border-radius: 10px; box-shadow: 0 1px 6px rgba(0,0,0,.08); margin-bottom: 16px; }
        .card-title { font-size: 13px; font-weight: 700; color: #888; text-transform: uppercase; letter-spacing: .5px; }
        .opcion-label { display: flex; align-items: flex-start; gap: 10px; padding: 10px 12px;
            border: 1px solid #e0e0e0; border-radius: 8px; cursor: pointer; margin-bottom: 8px;
            transition: background .15s, border-color .15s; font-size: 14px; }
        .opcion-label input { margin-top: 2px; flex-shrink: 0; accent-color: #bd9751; }
        .opcion-label:has(input:checked) { background: #fdf6e3; border-color: #bd9751; }
        .pregunta-num { font-size: 12px; color: #999; font-weight: 600; margin-bottom: 6px; }
        .pregunta-texto { font-size: 14px; font-weight: 600; margin-bottom: 10px; color: #222; }
        .tratamiento-box { background: #f8f9ff; border: 1px solid #d0d8f0; border-radius: 8px;
            padding: 14px; font-size: 13px; line-height: 1.6; color: #444; max-height: 200px;
            overflow-y: auto; margin-bottom: 12px; }
        .btn-enviar { background: #bd9751; color: #fff; border: none; font-weight: 700;
            font-size: 15px; padding: 14px; border-radius: 10px; width: 100%; }
        .btn-enviar:hover { background: #a07e3e; color: #fff; }
        .select2-container { width: 100% !important; }
    </style>
</head>
<body>

<div class="eval-header">
    <img src="/public/icons/icon-96x96.png" alt="SST-PH">
    <h1>Evaluación SST</h1>
    <p><?= esc($evaluacion['titulo']) ?></p>
</div>

<?php if (session()->getFlashdata('error')): ?>
<div class="alert alert-danger m-3" style="font-size:13px;"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<form method="post" action="/evaluar/<?= esc($evaluacion['token']) ?>/submit" id="evalForm">
    <?= csrf_field() ?>
    <div class="container-fluid px-3 pt-3">

        <!-- ── AUTORIZACIÓN TRATAMIENTO DE DATOS ── -->
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Autorización de tratamiento de datos personales</h6>
                <div class="tratamiento-box">
                    <strong>Autorización de Tratamiento de Datos Personales</strong><br><br>
                    De conformidad con la <strong>Ley 1581 de 2012</strong> y el Decreto 1377 de 2013 sobre Protección de Datos Personales en Colombia, al diligenciar este formulario usted autoriza de manera libre, expresa e informada a <strong>Enterprises SST S.A.S.</strong> para recolectar, almacenar, usar y circular sus datos personales (nombre, número de identificación, cargo y datos de contacto) con las siguientes finalidades:<br><br>
                    • Gestionar el registro de asistencia y evaluación de la inducción en Seguridad y Salud en el Trabajo (SG-SST).<br>
                    • Elaborar informes y reportes de gestión del SG-SST para la copropiedad.<br>
                    • Cumplir con obligaciones legales en materia de SST ante entidades reguladoras (ARL, Ministerio de Trabajo).<br><br>
                    Sus datos serán tratados con total confidencialidad y no serán cedidos a terceros sin su consentimiento, salvo obligación legal. Puede ejercer sus derechos de acceso, corrección, cancelación u oposición (ARCO) contactándonos en <strong>info@enterprisessst.com</strong>.<br><br>
                    <em>Al marcar la casilla y enviar este formulario, usted declara haber leído y aceptado esta autorización.</em>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="acepta_tratamiento" id="chkTratamiento" value="1" required>
                    <label class="form-check-label" for="chkTratamiento" style="font-size:13px; font-weight:600;">
                        Acepto el tratamiento de mis datos personales según la Ley 1581 de 2012.
                    </label>
                </div>
            </div>
        </div>

        <!-- ── DATOS PERSONALES ── -->
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Datos personales</h6>
                <div class="mb-3">
                    <label class="form-label" style="font-size:13px;">Nombre completo *</label>
                    <input type="text" name="nombre" class="form-control form-control-sm" required
                        placeholder="Ej: Juan Pérez García">
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size:13px;">Número de documento *</label>
                    <input type="text" name="cedula" class="form-control form-control-sm" required
                        placeholder="CC, CE, PEP, PPT, pasaporte (sin puntos ni guiones)">
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size:13px;">Número de WhatsApp *</label>
                    <input type="text" name="whatsapp" class="form-control form-control-sm" required
                        placeholder="Ej: 3001234567" inputmode="numeric">
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size:13px;">Conjunto en el cual trabaja *</label>
                    <select name="id_cliente_conjunto" id="selectConjunto" class="form-select form-select-sm" required>
                        <option value="">Seleccionar conjunto...</option>
                        <?php foreach ($conjuntos as $c): ?>
                        <option value="<?= $c['id_cliente'] ?>"><?= esc($c['nombre_cliente']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size:13px;">Nombre de su empresa contratante *</label>
                    <input type="text" name="empresa_contratante" class="form-control form-control-sm" required
                        placeholder="Empresa para la que trabaja">
                </div>
                <div class="mb-2">
                    <label class="form-label" style="font-size:13px;">¿Cuál es su cargo? *</label>
                    <input type="text" name="cargo" class="form-control form-control-sm" required
                        placeholder="Ej: Guarda de seguridad, Todero...">
                </div>
            </div>
        </div>

        <!-- ── PREGUNTAS ── -->
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Evaluación de conocimientos SST</h6>
                <small class="text-muted d-block mb-3" style="font-size:12px;">
                    Seleccione la respuesta correcta para cada pregunta. Total: <?= count($preguntas) ?> preguntas.
                </small>

                <?php foreach ($preguntas as $i => $p): ?>
                <div class="mb-4">
                    <div class="pregunta-num">Pregunta <?= $i + 1 ?> de <?= count($preguntas) ?></div>
                    <div class="pregunta-texto"><?= esc($p['texto']) ?></div>
                    <?php foreach ($p['opciones'] as $letra => $opcion): ?>
                    <label class="opcion-label">
                        <input type="radio" name="respuesta[<?= $i ?>]" value="<?= $letra ?>" required>
                        <span><?= esc($opcion) ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <button type="submit" class="btn-enviar mb-4" id="btnEnviar">
            Enviar evaluación
        </button>
    </div>
</form>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
$(function() {
    $('#selectConjunto').select2({
        theme: 'bootstrap-5',
        placeholder: 'Seleccionar conjunto...',
        allowClear: true,
    });

    // Flag para impedir doble submit (multi-tap, doble-tap iOS, o Enter repetido)
    var _evalSubmitted = false;

    $('#evalForm').on('submit', function(e) {
        if (_evalSubmitted) {
            e.preventDefault();
            return false;
        }
        if (!$('#selectConjunto').val()) {
            e.preventDefault();
            alert('Debe seleccionar el conjunto en el cual trabaja.');
            $('#selectConjunto').next('.select2').find('.select2-selection').css('border-color','#dc3545');
            return false;
        }
        _evalSubmitted = true;
        $('#btnEnviar').prop('disabled', true).text('Enviando...').css('opacity', '0.6');
    });

    // Bloquear clicks directos en el boton incluso antes del submit
    $('#btnEnviar').on('click', function(e) {
        if (_evalSubmitted) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
    });

    $('#selectConjunto').on('change', function() {
        $(this).next('.select2').find('.select2-selection').css('border-color','');
    });
});
</script>
</body>
</html>
