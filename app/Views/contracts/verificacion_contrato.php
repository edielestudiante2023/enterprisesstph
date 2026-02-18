<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $valido ? 'Contrato Verificado' : 'Verificacion No Valida' ?> - EnterpriseSST</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f0f2f5; color: #333; }
        .container { max-width: 700px; margin: 30px auto; padding: 0 15px; }
        .card { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .banner-valid { background: linear-gradient(135deg, #065f46, #10b981); color: white; padding: 30px; text-align: center; }
        .banner-invalid { background: linear-gradient(135deg, #991b1b, #dc2626); color: white; padding: 30px; text-align: center; }
        .banner-valid h1, .banner-invalid h1 { font-size: 24px; margin-bottom: 8px; }
        .banner-valid .code { font-size: 18px; font-family: 'Courier New', monospace; letter-spacing: 4px; background: rgba(255,255,255,0.2); display: inline-block; padding: 8px 20px; border-radius: 6px; margin-top: 10px; }
        .content { padding: 30px; }
        .legal-note { background: #ecfdf5; border-left: 4px solid #065f46; padding: 15px; margin-bottom: 25px; border-radius: 0 8px 8px 0; font-size: 14px; color: #065f46; }
        .section-title { font-size: 16px; font-weight: 700; color: #065f46; margin: 25px 0 12px; padding-bottom: 6px; border-bottom: 2px solid #d1fae5; }
        table.info { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.info td { padding: 10px 12px; border: 1px solid #e5e7eb; font-size: 14px; }
        table.info td:first-child { background: #f9fafb; font-weight: 600; width: 35%; color: #374151; }
        .badge-firmado { display: inline-block; background: #065f46; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .firma-box { border: 1px solid #e5e7eb; border-radius: 8px; padding: 15px; text-align: center; background: #fafafa; }
        .firma-box img { max-height: 80px; }
        .qr-section { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; }
        .qr-section img { width: 120px; height: 120px; border-radius: 8px; }
        .qr-section .url { font-size: 11px; color: #6b7280; margin-top: 8px; word-break: break-all; }
        .legal-footer { font-size: 11px; color: #6b7280; margin-top: 25px; padding-top: 15px; border-top: 1px solid #e5e7eb; line-height: 1.5; }
        .footer { background: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #999; border-top: 1px solid #eee; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <?php if ($valido): ?>
                <div class="banner-valid">
                    <h1>CONTRATO VERIFICADO</h1>
                    <p>El documento ha sido firmado electronicamente</p>
                    <div class="code"><?= esc($codigoVerificacion) ?></div>
                </div>

                <div class="content">
                    <div class="legal-note">
                        Este documento fue firmado electronicamente conforme a la
                        <strong>Ley 527 de 1999</strong> y el <strong>Decreto 2364 de 2012</strong>
                        de la Republica de Colombia.
                    </div>

                    <div class="section-title">Informacion del Contrato</div>
                    <table class="info">
                        <tr>
                            <td>Contrato N°</td>
                            <td><?= esc($contrato['numero_contrato']) ?></td>
                        </tr>
                        <tr>
                            <td>Empresa</td>
                            <td><?= esc($cliente['nombre_cliente'] ?? '') ?></td>
                        </tr>
                        <tr>
                            <td>NIT</td>
                            <td><?= esc($cliente['nit_cliente'] ?? '') ?></td>
                        </tr>
                        <tr>
                            <td>Vigencia</td>
                            <td><?= date('d/m/Y', strtotime($contrato['fecha_inicio'])) ?> al <?= date('d/m/Y', strtotime($contrato['fecha_fin'])) ?></td>
                        </tr>
                        <tr>
                            <td>Estado</td>
                            <td><span class="badge-firmado">FIRMADO</span></td>
                        </tr>
                    </table>

                    <div class="section-title">Datos del Firmante</div>
                    <table class="info">
                        <tr>
                            <td>Nombre</td>
                            <td><?= esc($contrato['firma_cliente_nombre'] ?? '') ?></td>
                        </tr>
                        <tr>
                            <td>Cedula</td>
                            <td><?= esc($contrato['firma_cliente_cedula'] ?? '') ?></td>
                        </tr>
                        <tr>
                            <td>Rol</td>
                            <td>Representante Legal - Contratante</td>
                        </tr>
                        <tr>
                            <td>Fecha de firma</td>
                            <td><?= !empty($contrato['firma_cliente_fecha']) ? date('d/m/Y H:i:s', strtotime($contrato['firma_cliente_fecha'])) : '' ?></td>
                        </tr>
                        <tr>
                            <td>Direccion IP</td>
                            <td><?= esc($contrato['firma_cliente_ip'] ?? '') ?></td>
                        </tr>
                    </table>

                    <?php if (!empty($contrato['firma_cliente_imagen'])): ?>
                    <div class="firma-box">
                        <img src="<?= base_url($contrato['firma_cliente_imagen']) ?>" alt="Firma del cliente">
                    </div>
                    <?php endif; ?>

                    <div class="qr-section">
                        <img src="<?= esc($qrImage) ?>" alt="QR de verificacion">
                        <p class="url">Verifique este documento en:<br><?= base_url("contrato/verificar/{$codigoVerificacion}") ?></p>
                    </div>

                    <div class="legal-footer">
                        <strong>Validez Legal:</strong> La firma electronica tiene la misma validez y efectos juridicos
                        que la firma manuscrita segun la Ley 527 de 1999 (Comercio Electronico) y el Decreto 2364
                        de 2012 (Firma Electronica) de la Republica de Colombia.
                    </div>
                </div>

            <?php else: ?>
                <div class="banner-invalid">
                    <h1>Verificacion No Valida</h1>
                    <p>El codigo ingresado no corresponde a ningun contrato firmado en el sistema.</p>
                </div>
                <div class="content">
                    <p style="text-align: center; color: #666; padding: 20px 0;">
                        Si cree que esto es un error, por favor contacte a su asesor comercial o al administrador del sistema.
                    </p>
                </div>
            <?php endif; ?>

            <div class="footer">
                Enterprise SST - Sistema de Gestion de Seguridad y Salud en el Trabajo<br>
                Powered by Cycloid Talent S.A.S.
            </div>
        </div>
    </div>
</body>
</html>
