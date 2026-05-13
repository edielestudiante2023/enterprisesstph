<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario enviado</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background: #f5f6fa; font-family: 'Segoe UI', sans-serif; }
        .enc-header { background: #1a2340; color: #fff; padding: 20px 16px 16px; text-align: center; }
        .enc-header img { height: 48px; display: block; margin: 0 auto 8px; }
        .enc-header h1 { font-size: 18px; font-weight: 700; margin: 0; }
    </style>
</head>
<body>
<div class="enc-header">
    <img src="<?= base_url('icons/icon-96x96.png') ?>" alt="SST-PH">
    <h1>Formulario enviado</h1>
</div>
<div class="container-fluid px-3 pt-4 text-center">
    <div class="alert alert-success" style="font-size:14px;">
        Sus respuestas han sido registradas correctamente.
    </div>
    <p class="text-muted" style="font-size:12px;">Puede cerrar esta ventana.</p>
</div>
</body>
</html>
