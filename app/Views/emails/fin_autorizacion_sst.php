<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comunicado Oficial — Terminación de Respaldo SST</title>
</head>
<body style="margin:0;padding:0;background-color:#f0f4f8;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0f4f8;padding:40px 20px;">
  <tr>
    <td align="center">
      <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.10);">

        <!-- HEADER -->
        <tr>
          <td style="background:linear-gradient(135deg,#7b1fa2 0%,#4a148c 100%);padding:36px 40px;text-align:center;">
            <p style="margin:0 0 8px 0;color:rgba(255,255,255,0.85);font-size:13px;letter-spacing:2px;text-transform:uppercase;">Cycloid Talent SAS</p>
            <h1 style="margin:0;color:#ffffff;font-size:24px;font-weight:700;line-height:1.3;">
              Comunicado Oficial<br>
              <span style="font-size:17px;font-weight:400;">Terminación de Respaldo y Autorización en SST</span>
            </h1>
            <p style="margin:12px 0 0 0;color:rgba(255,255,255,0.80);font-size:13px;">
              Fecha de emisión: <?= esc($fechaEmision) ?>
            </p>
          </td>
        </tr>

        <!-- AVISO LEGAL -->
        <tr>
          <td style="background:#fce4ec;padding:18px 40px;text-align:center;border-bottom:1px solid #f8bbd0;">
            <p style="margin:0;color:#b71c1c;font-size:15px;font-weight:600;">
              ⚠️ &nbsp;Comunicado con efectos legales inmediatos — Conserve este documento
            </p>
          </td>
        </tr>

        <!-- CUERPO PRINCIPAL -->
        <tr>
          <td style="padding:36px 40px;">

            <p style="margin:0 0 6px 0;color:#555;font-size:13px;"><?= esc($ciudadCliente) ?>, <?= esc($fechaEmision) ?></p>

            <p style="margin:0 0 20px 0;color:#333;font-size:15px;line-height:1.7;">
              Señores<br>
              <strong><?= esc($nombreCliente) ?></strong><br>
              NIT <strong><?= esc($nitCliente) ?></strong>
            </p>

            <p style="margin:0 0 20px 0;color:#333;font-size:15px;line-height:1.7;">
              Por medio del presente, <strong>Cycloid Talent SAS</strong> notifica formalmente que,
              a partir de la fecha de este comunicado, <strong>ha terminado el vínculo contractual
              de asesoría en Seguridad y Salud en el Trabajo (SST)</strong> con su organización.
            </p>

            <p style="margin:0 0 20px 0;color:#333;font-size:15px;line-height:1.7;">
              En consecuencia, la siguiente persona:
            </p>

            <!-- TABLA DEL CONSULTOR -->
            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px;border:1px solid #e0e0e0;border-radius:8px;overflow:hidden;">
              <tr style="background:#f3e5f5;">
                <td style="padding:12px 20px;font-size:13px;color:#555;font-weight:600;width:40%;border-bottom:1px solid #e0e0e0;">Nombre completo</td>
                <td style="padding:12px 20px;font-size:14px;color:#1a1a1a;font-weight:700;border-bottom:1px solid #e0e0e0;"><?= esc($nombreConsultor) ?></td>
              </tr>
              <tr style="background:#fafafa;">
                <td style="padding:12px 20px;font-size:13px;color:#555;font-weight:600;border-bottom:1px solid #e0e0e0;">Cédula de ciudadanía</td>
                <td style="padding:12px 20px;font-size:14px;color:#1a1a1a;font-weight:700;border-bottom:1px solid #e0e0e0;"><?= esc($cedulaConsultor) ?></td>
              </tr>
              <tr style="background:#f3e5f5;">
                <td style="padding:12px 20px;font-size:13px;color:#555;font-weight:600;">Licencia en SST No.</td>
                <td style="padding:12px 20px;font-size:14px;color:#1a1a1a;font-weight:700;"><?= esc($licenciaConsultor) ?></td>
              </tr>
            </table>

            <!-- BLOQUE DE PROHIBICIÓN -->
            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
              <tr>
                <td style="padding:20px 24px;background:#ffebee;border-left:5px solid #c62828;border-radius:6px;">
                  <p style="margin:0 0 8px 0;color:#b71c1c;font-size:15px;font-weight:700;">
                    NO ESTÁ AUTORIZADA, a partir de esta fecha, para:
                  </p>
                  <p style="margin:0;color:#333;font-size:14px;line-height:1.9;">
                    ✗ &nbsp;Realizar actuaciones, trámites o visitas en SST en nombre de <strong><?= esc($nombreCliente) ?></strong><br>
                    ✗ &nbsp;Firmar o avalar documentos de SST como representante de su organización<br>
                    ✗ &nbsp;Presentar soportes, informes o licitaciones invocando el respaldo de Cycloid Talent SAS<br>
                    ✗ &nbsp;Realizar cualquier gestión ante el Ministerio del Trabajo, ARL, Secretaría de Salud u otras entidades en nombre de su empresa
                  </p>
                </td>
              </tr>
            </table>

            <p style="margin:0 0 20px 0;color:#333;font-size:15px;line-height:1.7;">
              Cualquier uso del nombre, cédula, firma o número de licencia de la persona mencionada
              en documentos, procesos o actuaciones posteriores a esta fecha <strong>constituye un uso
              no autorizado de credenciales profesionales</strong>, con las implicaciones legales que esto conlleva.
            </p>

            <p style="margin:0 0 32px 0;color:#333;font-size:15px;line-height:1.7;">
              Le instamos a abstenerse de presentar soportes, licitaciones o gestiones en SST
              invocando el respaldo de <strong>Cycloid Talent SAS</strong> o del profesional indicado.
            </p>

            <!-- FIRMA -->
            <table width="100%" cellpadding="0" cellspacing="0" style="border-top:2px solid #e9ecef;padding-top:24px;">
              <tr>
                <td style="padding-top:24px;">
                  <p style="margin:0 0 4px 0;color:#555;font-size:13px;">Emitido por:</p>
                  <p style="margin:0 0 2px 0;color:#1a1a1a;font-size:15px;font-weight:700;">Cycloid Talent SAS</p>
                  <p style="margin:0;color:#555;font-size:13px;">NIT: 901.653.912 — Sistema EnterpriseSST</p>
                </td>
                <td align="right" valign="middle" style="padding-top:24px;">
                  <div style="display:inline-block;background:#7b1fa2;color:#fff;padding:10px 22px;border-radius:6px;font-size:13px;font-weight:600;letter-spacing:1px;">
                    CYCLOID TALENT
                  </div>
                </td>
              </tr>
            </table>

          </td>
        </tr>

        <!-- FOOTER -->
        <tr>
          <td style="background:#f8f9fa;padding:20px 40px;border-top:1px solid #e9ecef;text-align:center;">
            <p style="margin:0;color:#888;font-size:12px;line-height:1.6;">
              Este comunicado fue generado automáticamente por el sistema <strong>EnterpriseSST</strong>
              al momento del retiro del cliente. Tiene validez como documento oficial.<br>
              Para consultas, visítenos en <a href="https://cycloidtalent.com/" style="color:#7b1fa2;">cycloidtalent.com</a>
            </p>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>

</body>
</html>
