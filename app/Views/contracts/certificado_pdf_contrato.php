<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #333; margin: 0; padding: 20px; }
        .header { background: #065f46; color: white; padding: 25px; text-align: center; border-radius: 8px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 18px; letter-spacing: 1px; }
        .header .code { font-size: 16px; font-family: 'Courier New', monospace; letter-spacing: 4px; margin-top: 8px; background: rgba(255,255,255,0.2); display: inline-block; padding: 6px 16px; border-radius: 4px; }
        .legal-note { background: #ecfdf5; border-left: 4px solid #065f46; padding: 12px; margin-bottom: 20px; font-size: 10px; color: #065f46; }
        h3 { color: #065f46; font-size: 13px; margin: 20px 0 8px; padding-bottom: 4px; border-bottom: 2px solid #d1fae5; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        td { padding: 7px 10px; border: 1px solid #d1d5db; font-size: 10px; }
        td:first-child { background: #f3f4f6; font-weight: bold; width: 35%; color: #374151; }
        .badge { display: inline-block; background: #065f46; color: white; padding: 2px 10px; border-radius: 10px; font-size: 9px; }
        .firma-img { max-height: 60px; }
        .qr-section { text-align: center; margin-top: 25px; padding-top: 15px; border-top: 1px solid #d1d5db; }
        .qr-section img { width: 100px; height: 100px; }
        .qr-section p { font-size: 9px; color: #6b7280; margin-top: 5px; }
        .legal-footer { font-size: 9px; color: #6b7280; margin-top: 20px; padding-top: 12px; border-top: 1px solid #d1d5db; line-height: 1.5; }
        .footer { text-align: center; font-size: 8px; color: #9ca3af; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>CERTIFICADO DE FIRMA ELECTRONICA</h1>
        <p style="margin: 5px 0 0; font-size: 11px; opacity: 0.8;">Contrato de Prestacion de Servicios SST</p>
        <div class="code"><?= $codigoVerificacion ?></div>
    </div>

    <div class="legal-note">
        Este certificado acredita que el contrato fue firmado electronicamente conforme a la
        <strong>Ley 527 de 1999</strong> y el <strong>Decreto 2364 de 2012</strong> de la Republica de Colombia.
    </div>

    <h3>Informacion del Contrato</h3>
    <table>
        <tr><td>Contrato N°</td><td><?= esc($contrato['numero_contrato']) ?></td></tr>
        <tr><td>Empresa Contratante</td><td><?= esc($cliente['nombre_cliente'] ?? '') ?></td></tr>
        <tr><td>NIT</td><td><?= esc($cliente['nit_cliente'] ?? '') ?></td></tr>
        <tr><td>Empresa Contratista</td><td>CYCLOID TALENT S.A.S. - NIT 901.653.912-2</td></tr>
        <tr><td>Vigencia</td><td><?= date('d/m/Y', strtotime($contrato['fecha_inicio'])) ?> al <?= date('d/m/Y', strtotime($contrato['fecha_fin'])) ?></td></tr>
        <tr><td>Valor</td><td>$<?= number_format($contrato['valor_contrato'] ?? 0, 0, ',', '.') ?> COP</td></tr>
        <tr><td>Estado</td><td><span class="badge">FIRMADO</span></td></tr>
    </table>

    <h3>Registro de Firma</h3>
    <table>
        <tr><td>Firmante</td><td><?= esc($contrato['firma_cliente_nombre'] ?? '') ?></td></tr>
        <tr><td>Documento de Identidad</td><td><?= esc($contrato['firma_cliente_cedula'] ?? '') ?></td></tr>
        <tr><td>Rol</td><td>Representante Legal - Contratante</td></tr>
        <tr><td>Fecha y hora de firma</td><td><?= !empty($contrato['firma_cliente_fecha']) ? date('d/m/Y H:i:s', strtotime($contrato['firma_cliente_fecha'])) : '' ?></td></tr>
        <tr><td>Direccion IP</td><td><?= esc($contrato['firma_cliente_ip'] ?? '') ?></td></tr>
        <tr>
            <td>Firma</td>
            <td>
                <?php if (!empty($contrato['firma_cliente_imagen'])): ?>
                    <img src="<?= FCPATH . $contrato['firma_cliente_imagen'] ?>" class="firma-img">
                <?php else: ?>
                    Firma registrada digitalmente
                <?php endif; ?>
            </td>
        </tr>
    </table>

    <div class="qr-section">
        <img src="<?= $qrImage ?>" alt="QR">
        <p>Verifique este documento en:<br><?= base_url("contrato/verificar/{$codigoVerificacion}") ?></p>
    </div>

    <div class="legal-footer">
        <strong>Validez Legal:</strong> Este certificado acredita que el contrato fue firmado electronicamente
        conforme a la Ley 527 de 1999 (Comercio Electronico) y el Decreto 2364 de 2012 (Firma Electronica)
        de la Republica de Colombia. La firma electronica tiene la misma validez y efectos juridicos que la firma manuscrita.
    </div>

    <div class="footer">
        Enterprise SST - Sistema de Gestion de Seguridad y Salud en el Trabajo | Cycloid Talent S.A.S. NIT 901.653.912-2<br>
        Documento generado el <?= date('d/m/Y H:i:s') ?>
    </div>
</body>
</html>
