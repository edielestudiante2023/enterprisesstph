<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - Carta Vigia SST</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #dc3545 0%, #a71d2a 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .error-card { max-width: 500px; margin: 15px; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); text-align: center; }
        .error-icon { font-size: 64px; color: #dc3545; margin-bottom: 16px; }
    </style>
</head>
<body>
<div class="card error-card">
    <div class="card-body p-4">
        <div class="error-icon">
            <i class="fas fa-times-circle"></i>
        </div>
        <h4 class="mb-3">Enlace no disponible</h4>
        <p class="text-muted">
            <?= esc($mensaje ?? 'El enlace de firma ha expirado o no es valido.') ?>
        </p>
        <div class="mt-3">
            <p style="font-size: 13px; color: #888;">
                <i class="fas fa-info-circle"></i>
                Contacte al consultor de SST para que le reenvie el enlace de firma.
            </p>
        </div>
    </div>
</div>
</body>
</html>
