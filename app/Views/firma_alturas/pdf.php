<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 100px 70px 80px 90px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.4;
            padding: 15px 20px;
        }

        /* Header corporativo */
        .header-table { width: 100%; border-collapse: collapse; border: 1.5px solid #333; margin-bottom: 12px; }
        .header-table td { border: 1px solid #333; padding: 5px 8px; vertical-align: middle; }
        .header-logo { width: 110px; text-align: center; font-size: 9px; }
        .header-logo img { max-width: 95px; max-height: 55px; }
        .header-title { text-align: center; font-weight: bold; font-size: 10px; }
        .header-code { width: 130px; font-size: 9px; }

        .main-title { text-align: center; font-size: 13px; font-weight: bold; margin: 15px 0 10px; color: #1c2437; }

        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; border: 1px solid #ccc; }
        .info-table td { padding: 5px 8px; font-size: 10px; border: 1px solid #ccc; }
        .info-label { font-weight: bold; color: #444; width: 140px; background: #f7f7f7; }

        .section-box { border: 1px solid #ccc; border-radius: 4px; padding: 10px 12px; margin-bottom: 10px; font-size: 10px; line-height: 1.5; }
        .section-box-yellow { background: #fff9e6; border-color: #e6c200; }
        .section-box-red { background: #fef2f2; border-color: #e8a0a0; }
        .section-box-green { background: #f0faf0; border-color: #a0d8a0; }

        .section-box-title { font-weight: bold; font-size: 10px; margin-bottom: 4px; }
        .section-box ul { margin: 5px 0 0 15px; }
        .section-box li { margin-bottom: 3px; }

        .content-text { font-size: 10px; line-height: 1.6; margin-bottom: 8px; text-align: justify; }

        .legal-note { font-size: 9px; color: #666; margin-top: 15px; text-align: justify; line-height: 1.5; }

        .firma-table { width: 100%; border-collapse: collapse; margin-top: 30px; }
        .firma-table td { text-align: center; vertical-align: bottom; padding: 5px 15px; }
        .firma-table img { max-width: 150px; max-height: 60px; }
        .firma-label { border-top: 1px solid #333; margin-top: 4px; padding-top: 4px; font-size: 9px; font-weight: bold; color: #555; }
        .firma-date { font-size: 8px; color: #888; margin-top: 2px; }
    </style>
</head>
<body>

    <!-- HEADER CORPORATIVO -->
    <table class="header-table">
        <tr>
            <td class="header-logo" rowspan="2">
                <?php if (!empty($logoBase64)): ?>
                    <img src="<?= $logoBase64 ?>">
                <?php else: ?>
                    <strong style="font-size:8px;"><?= esc($cliente['nombre_cliente'] ?? '') ?></strong>
                <?php endif; ?>
            </td>
            <td class="header-title">SISTEMA DE GESTIÓN DE SEGURIDAD Y SALUD EN EL TRABAJO</td>
            <td class="header-code">Código: FT-SST-ALT<br>Versión: 001</td>
        </tr>
        <tr>
            <td class="header-title">PROTOCOLO DE NOTIFICACIÓN DE TRABAJO EN ALTURAS</td>
            <td class="header-code">Fecha: <?= esc($fechaFirma ?? date('Y-m-d')) ?></td>
        </tr>
    </table>

    <div class="main-title">PROTOCOLO DE NOTIFICACIÓN DE TRABAJO EN ALTURAS</div>

    <!-- DATOS GENERALES -->
    <table class="info-table">
        <tr>
            <td class="info-label">Copropiedad:</td>
            <td><?= esc($cliente['nombre_cliente'] ?? '') ?></td>
        </tr>
        <tr>
            <td class="info-label">NIT:</td>
            <td><?= esc($cliente['nit_cliente'] ?? '') ?></td>
        </tr>
        <tr>
            <td class="info-label">Representante Legal:</td>
            <td><?= esc($cliente['nombre_rep_legal'] ?? 'No registrado') ?></td>
        </tr>
        <tr>
            <td class="info-label">Dirección:</td>
            <td><?= esc($cliente['direccion_cliente'] ?? '') ?></td>
        </tr>
    </table>

    <!-- FUNDAMENTO LEGAL -->
    <div class="section-box section-box-yellow">
        <div class="section-box-title">⚖ Fundamento Legal</div>
        Resolución 4272 de 2021, Ministerio de Trabajo. Todo trabajo realizado a 1.50 metros o más sobre el nivel del piso requiere: personal con curso vigente de alturas, afiliación a EPS, ARL y pensión, permiso de trabajo documentado y equipos certificados.
    </div>

    <!-- RESPONSABILIDAD -->
    <div class="section-box section-box-red">
        <div class="section-box-title">⚠ Responsabilidad</div>
        Si la copropiedad autoriza o permite trabajos en alturas con personal sin curso de alturas, sin EPS, sin ARL o sin seguridad social, y ocurre un accidente grave o mortal:
        <ul>
            <li>El administrador como representante legal responde civil y penalmente</li>
            <li>La ARL no cubre el accidente si el trabajador no está afiliado</li>
            <li>La copropiedad asume la totalidad de costos médicos, indemnizaciones y sanciones</li>
        </ul>
    </div>

    <!-- PROTOCOLO -->
    <div class="section-box section-box-green">
        <div class="section-box-title">✅ Protocolo</div>
        Antes de autorizar cualquier trabajo en alturas en las instalaciones de la copropiedad, el administrador DEBE notificar formalmente al consultor SST asignado por Cycloid Talent para verificar el cumplimiento de requisitos legales del contratista.
    </div>

    <p class="content-text">
        <strong>Cycloid Talent SAS</strong>, como consultor externo del SG-SST, <strong>no asume responsabilidad</strong> por accidentes derivados de trabajos en alturas que no hayan sido notificados formalmente, conforme a lo establecido en el contrato de prestación de servicios vigente.
    </p>

    <p class="content-text">
        Al firmar este documento, el representante legal declara haber sido informado sobre las obligaciones legales relacionadas con trabajo en alturas y acepta el protocolo de notificación establecido.
    </p>

    <!-- FIRMA -->
    <table class="firma-table">
        <tr>
            <td>
                <?php if (!empty($firmaBase64)): ?>
                    <img src="<?= $firmaBase64 ?>">
                <?php endif; ?>
                <div class="firma-label">
                    <?= esc($cliente['nombre_rep_legal'] ?? 'Representante Legal') ?><br>
                    Representante Legal<br>
                    <?= esc($cliente['nombre_cliente'] ?? '') ?>
                </div>
                <div class="firma-date">
                    Firmado: <?= esc($fechaFirma ?? '') ?><br>
                    IP: <?= esc($ipFirma ?? '') ?>
                </div>
            </td>
        </tr>
    </table>

    <p class="legal-note">
        Tratamiento de datos conforme a la Ley 1581 de 2012. Este documento fue generado electrónicamente y constituye soporte de que el representante legal fue informado sobre las obligaciones legales y el procedimiento de notificación vigente para trabajos en alturas según la Resolución 4272 de 2021.
    </p>

</body>
</html>
