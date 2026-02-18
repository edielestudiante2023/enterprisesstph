<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paz y Salvo por Todo Concepto</title>
</head>
<body style="margin:0;padding:0;background-color:#f0f4f8;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0f4f8;padding:40px 20px;">
  <tr>
    <td align="center">
      <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.10);">

        <!-- HEADER -->
        <tr>
          <td style="background:linear-gradient(135deg,#0d6efd 0%,#0a58ca 100%);padding:36px 40px;text-align:center;">
            <p style="margin:0 0 8px 0;color:rgba(255,255,255,0.85);font-size:13px;letter-spacing:2px;text-transform:uppercase;">Cycloid Talent SAS</p>
            <h1 style="margin:0;color:#ffffff;font-size:26px;font-weight:700;line-height:1.3;">
              Paz y Salvo<br>por Todo Concepto
            </h1>
            <p style="margin:12px 0 0 0;color:rgba(255,255,255,0.80);font-size:13px;">
              Fecha de emisión: <?= esc($fechaEmision) ?>
            </p>
          </td>
        </tr>

        <!-- SELLO VERIFICADO -->
        <tr>
          <td style="background:#e8f5e9;padding:18px 40px;text-align:center;border-bottom:1px solid #c8e6c9;">
            <p style="margin:0;color:#2e7d32;font-size:15px;font-weight:600;">
              ✅ &nbsp;Documento válido emitido por el sistema EnterpriseSST
            </p>
          </td>
        </tr>

        <!-- CUERPO PRINCIPAL -->
        <tr>
          <td style="padding:36px 40px;">

            <p style="margin:0 0 20px 0;color:#333;font-size:15px;line-height:1.7;">
              Estimado(a) <strong><?= esc($nombreCliente) ?></strong>,
            </p>

            <p style="margin:0 0 20px 0;color:#333;font-size:15px;line-height:1.7;">
              Por medio de la presente, <strong>Cycloid Talent SAS</strong> certifica que
              <strong><?= esc($nombreCliente) ?></strong>, identificado(a) con NIT
              <strong><?= esc($nitCliente) ?></strong><?= !empty($ciudadCliente) ? ', con sede en <strong>' . esc($ciudadCliente) . '</strong>' : '' ?>,
              se encuentra a <strong>paz y salvo por todo concepto</strong> en relación
              a los servicios de asesoría en Seguridad y Salud en el Trabajo (SST)
              prestados por <strong>Cycloid Talent SAS</strong>.
            </p>

            <p style="margin:0 0 24px 0;color:#333;font-size:15px;line-height:1.7;">
              Al momento de la emisión del presente documento, se ha verificado el cierre
              satisfactorio de todos los módulos de seguimiento:
            </p>

            <!-- CHECKLIST DE MÓDULOS -->
            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
              <tr>
                <td style="padding:12px 16px;background:#f8f9fa;border-left:4px solid #28a745;border-radius:6px;margin-bottom:8px;">
                  <p style="margin:0;color:#1a1a1a;font-size:14px;">
                    <span style="color:#28a745;font-size:17px;">✔</span>
                    &nbsp;<strong>Plan de Trabajo Anual</strong> — Sin actividades abiertas o en gestión.
                  </p>
                </td>
              </tr>
              <tr><td style="height:8px;"></td></tr>
              <tr>
                <td style="padding:12px 16px;background:#f8f9fa;border-left:4px solid #28a745;border-radius:6px;">
                  <p style="margin:0;color:#1a1a1a;font-size:14px;">
                    <span style="color:#28a745;font-size:17px;">✔</span>
                    &nbsp;<strong>Cronograma de Capacitación</strong> — Sin sesiones programadas o reprogramadas pendientes.
                  </p>
                </td>
              </tr>
              <tr><td style="height:8px;"></td></tr>
              <tr>
                <td style="padding:12px 16px;background:#f8f9fa;border-left:4px solid #28a745;border-radius:6px;">
                  <p style="margin:0;color:#1a1a1a;font-size:14px;">
                    <span style="color:#28a745;font-size:17px;">✔</span>
                    &nbsp;<strong>Pendientes</strong> — Sin ítems abiertos o sin respuesta.
                  </p>
                </td>
              </tr>
              <tr><td style="height:8px;"></td></tr>
              <tr>
                <td style="padding:12px 16px;background:#f8f9fa;border-left:4px solid #28a745;border-radius:6px;">
                  <p style="margin:0;color:#1a1a1a;font-size:14px;">
                    <span style="color:#28a745;font-size:17px;">✔</span>
                    &nbsp;<strong>Vencimientos y Mantenimientos</strong> — Sin tareas sin ejecutar.
                  </p>
                </td>
              </tr>
            </table>

            <p style="margin:0 0 20px 0;color:#333;font-size:15px;line-height:1.7;">
              Este paz y salvo se expide a solicitud de la parte interesada, en la fecha
              indicada en el encabezado del presente documento.
            </p>

            <!-- FIRMA -->
            <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:32px;border-top:2px solid #e9ecef;padding-top:24px;">
              <tr>
                <td>
                  <p style="margin:0 0 4px 0;color:#555;font-size:13px;">Emitido por:</p>
                  <p style="margin:0 0 2px 0;color:#1a1a1a;font-size:15px;font-weight:700;"><?= esc($nombreConsultor) ?></p>
                  <p style="margin:0;color:#555;font-size:13px;">Consultor SST — Cycloid Talent SAS</p>
                </td>
                <td align="right" valign="middle">
                  <div style="display:inline-block;background:#0d6efd;color:#fff;padding:10px 22px;border-radius:6px;font-size:13px;font-weight:600;letter-spacing:1px;">
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
              Este email fue generado automáticamente por el sistema <strong>EnterpriseSST</strong>.<br>
              Por favor no responda a este correo. Para consultas, contacte a su consultor asignado.
            </p>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>

</body>
</html>
