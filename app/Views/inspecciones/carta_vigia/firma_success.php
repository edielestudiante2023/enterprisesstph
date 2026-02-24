<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carta Vigia SST - Verificacion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .success-card { max-width: 500px; margin: 15px; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); text-align: center; }
        .success-icon { font-size: 64px; color: #28a745; margin-bottom: 16px; }
        .codigo-box { background: #f8f9fa; border: 2px dashed #28a745; border-radius: 8px; padding: 12px; margin: 16px 0; font-family: monospace; font-size: 18px; letter-spacing: 2px; color: #155724; }
        .info-row { font-size: 13px; color: #555; text-align: left; margin-bottom: 6px; }
        .info-row strong { color: #333; }
    </style>
</head>
<body>
<div class="card success-card">
    <div class="card-body p-4">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>

        <?php if (!empty($verificacion)): ?>
            <h4 class="mb-3">Documento Verificado</h4>
            <p class="text-muted">
                La carta de designacion como Vigia de SST es <strong class="text-success">autentica</strong>.
            </p>
        <?php else: ?>
            <h4 class="mb-3">Carta Firmada Exitosamente</h4>
            <p class="text-muted">
                Su designacion como Vigia de Seguridad y Salud en el Trabajo ha sido registrada correctamente.
            </p>
        <?php endif; ?>

        <!-- Datos del documento -->
        <div style="background: #f8f9fa; border-radius: 8px; padding: 16px; margin: 16px 0; text-align: left;">
            <div class="info-row"><strong>Cliente:</strong> <?= esc($cliente['nombre_cliente'] ?? '') ?></div>
            <div class="info-row"><strong>Vigia:</strong> <?= esc($carta['nombre_vigia'] ?? '') ?></div>
            <div class="info-row"><strong>Documento:</strong> CC <?= esc($carta['documento_vigia'] ?? '') ?></div>
            <?php if (!empty($carta['firma_fecha'])): ?>
            <div class="info-row"><strong>Fecha firma:</strong> <?= date('d/m/Y H:i', strtotime($carta['firma_fecha'])) ?></div>
            <?php endif; ?>
        </div>

        <?php if (!empty($carta['codigo_verificacion'])): ?>
        <div class="codigo-box">
            <small class="d-block text-muted mb-1" style="font-family: sans-serif; font-size: 12px;">Codigo de verificacion</small>
            <?= esc($carta['codigo_verificacion']) ?>
        </div>
        <?php if (empty($verificacion)): ?>
        <p style="font-size: 12px; color: #666;">
            Guarde este codigo. Puede verificar la autenticidad de su carta en cualquier momento.
        </p>
        <?php endif; ?>
        <?php endif; ?>

        <div class="mt-3">
            <p style="font-size: 13px; color: #888;">
                <i class="fas fa-info-circle"></i>
                <?php if (!empty($verificacion)): ?>
                    Documento firmado digitalmente via Enterprise SST.
                <?php else: ?>
                    Puede cerrar esta ventana. El documento firmado quedara disponible en el sistema.
                <?php endif; ?>
            </p>
        </div>
    </div>
</div>
</body>
</html>
