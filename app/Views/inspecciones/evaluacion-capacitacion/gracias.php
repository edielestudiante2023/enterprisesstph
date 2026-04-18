<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluación enviada</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background: #f5f6fa; font-family: 'Segoe UI', sans-serif; }
        .eval-header { background: #1a2340; color: #fff; padding: 20px 16px 16px; text-align: center; }
        .eval-header img { height: 48px; display: block; margin: 0 auto 8px; }
        .eval-header h1 { font-size: 18px; font-weight: 700; margin: 0; }
        .score-circle { width: 120px; height: 120px; border-radius: 50%; display: flex;
            align-items: center; justify-content: center; font-size: 32px; font-weight: 800;
            margin: 0 auto 16px; border: 6px solid; }
        .score-high   { border-color: #28a745; color: #28a745; background: #eafbee; }
        .score-mid    { border-color: #ffc107; color: #856404; background: #fff9e6; }
        .score-low    { border-color: #dc3545; color: #dc3545; background: #fdecea; }
    </style>
</head>
<body>
<div class="eval-header">
    <img src="/public/icons/icon-96x96.png" alt="SST-PH">
    <h1>Evaluación enviada</h1>
</div>

<?php
$cal = round($calificacion, 1);
$scoreClass = $cal >= 70 ? 'score-high' : ($cal >= 50 ? 'score-mid' : 'score-low');
$msg = $cal >= 70 ? '¡Felicitaciones! Aprobó la evaluación.' : ($cal >= 50 ? 'Resultado regular. Refuerce los temas SST.' : 'No aprobó. Requiere refuerzo en SST.');
?>

<div class="container-fluid px-3 pt-4 text-center">
    <div class="score-circle <?= $scoreClass ?>"><?= $cal ?>%</div>
    <h5 style="font-weight:700; margin-bottom:6px;"><?= $msg ?></h5>
    <p class="text-muted" style="font-size:13px;">Sus respuestas han sido registradas exitosamente.</p>
    <p class="text-muted" style="font-size:12px; margin-top:20px;">Puede cerrar esta ventana.</p>
</div>
</body>
</html>
