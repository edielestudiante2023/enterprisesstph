<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - Protocolo Alturas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #2c3e50 0%, #1a252f 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
    </style>
</head>
<body>
<div style="max-width: 450px; padding: 15px;">
    <div class="card border-0 shadow-lg" style="border-radius: 12px;">
        <div class="card-body text-center p-4">
            <i class="fas fa-exclamation-circle fa-3x text-danger mb-3"></i>
            <h5>No se puede procesar la firma</h5>
            <p class="text-muted"><?= esc($mensaje ?? 'Enlace no válido.') ?></p>
            <p class="text-muted" style="font-size: 12px;">Si necesita ayuda, contacte a su consultor SST o escriba a notificacion.cycloidtalent@cycloidtalent.com</p>
        </div>
    </div>
</div>
</body>
</html>
