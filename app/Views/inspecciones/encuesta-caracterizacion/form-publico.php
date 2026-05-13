<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($encuesta['titulo']) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        body { background: #f5f6fa; font-family: 'Segoe UI', sans-serif; }
        .enc-header { background: #1a2340; color: #fff; padding: 20px 16px 16px; text-align: center; }
        .enc-header img { height: 48px; margin-bottom: 8px; display: block; margin: 0 auto 8px; }
        .enc-header h1 { font-size: 18px; font-weight: 700; margin: 0; }
        .enc-header p { font-size: 13px; margin: 4px 0 0; color: #c9d1e0; }
        .card { border: none; border-radius: 10px; box-shadow: 0 1px 6px rgba(0,0,0,.08); margin-bottom: 16px; }
        .card-title { font-size: 13px; font-weight: 700; color: #888; text-transform: uppercase; letter-spacing: .5px; }
        .form-label { font-size: 13px; font-weight: 600; color: #374151; }
        .form-label.req::after { content: ' *'; color: #dc3545; }
        .form-control { font-size: 16px; border-radius: 8px; }
        .btn-enviar { background: #bd9751; color: #fff; border: none; font-weight: 700; font-size: 15px; padding: 14px; border-radius: 10px; width: 100%; }
        .btn-enviar:hover { background: #a07e3e; color: #fff; }
    </style>
</head>
<body>
<div class="enc-header">
    <img src="<?= base_url('icons/icon-96x96.png') ?>" alt="SST-PH">
    <h1>Items Nucleares SG-SST</h1>
    <p><?= esc($cliente['nombre_cliente'] ?? $encuesta['titulo']) ?></p>
</div>

<form method="post" action="<?= base_url('encuesta-caracterizacion/' . $encuesta['token'] . '/submit') ?>" id="encuestaForm">
    <?= csrf_field() ?>
    <div class="container-fluid px-3 pt-3">
        <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger" style="font-size:13px;"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Informacion de la copropiedad</h6>
                <?php foreach ($preguntas as $field => $label): ?>
                <div class="mb-3">
                    <label class="form-label req" for="<?= esc($field) ?>"><?= esc($label) ?></label>
                    <input type="text" name="<?= esc($field) ?>" id="<?= esc($field) ?>" class="form-control" maxlength="255" value="<?= esc(old($field) ?? '') ?>" required>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <button type="submit" class="btn-enviar mb-4" id="btnEnviar">Enviar formulario</button>
    </div>
</form>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('encuestaForm').addEventListener('submit', function(e) {
    var btn = document.getElementById('btnEnviar');
    var firstMissing = null;
    var missingLabel = '';

    document.querySelectorAll('#encuestaForm input[required]').forEach(function(input) {
        if (firstMissing || input.value.trim() !== '') {
            return;
        }

        firstMissing = input;
        var label = document.querySelector('label[for="' + input.id + '"]');
        missingLabel = label ? label.textContent.replace('*', '').trim() : 'un campo obligatorio';
    });

    if (firstMissing) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Faltan campos por diligenciar',
            text: 'Completa el campo: ' + missingLabel,
            confirmButtonColor: '#bd9751',
        }).then(function() {
            firstMissing.focus();
            firstMissing.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
        return false;
    }

    if (btn.disabled) {
        e.preventDefault();
        return false;
    }
    btn.disabled = true;
    btn.textContent = 'Enviando...';
    btn.style.opacity = '0.6';
});
</script>
</body>
</html>
