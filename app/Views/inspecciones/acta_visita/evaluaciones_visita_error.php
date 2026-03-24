<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Enlace inválido</title>
    <style>
        body { font-family: Segoe UI, Arial, sans-serif; background: #f0f2f5; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .box { background: white; border-radius: 12px; padding: 32px 24px; max-width: 380px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .icon { font-size: 48px; margin-bottom: 16px; }
        h2 { color: #1c2437; margin: 0 0 12px; }
        p { color: #666; font-size: 14px; }
    </style>
</head>
<body>
    <div class="box">
        <div class="icon">⚠️</div>
        <h2>Enlace no válido</h2>
        <p><?= esc($mensaje) ?></p>
    </div>
</body>
</html>
