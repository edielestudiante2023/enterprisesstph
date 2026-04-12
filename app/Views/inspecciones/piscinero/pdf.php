<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 80px 50px 60px 50px; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9px; color: #333; line-height: 1.3; padding: 10px 15px;
        }
        .header-table { width: 100%; border-collapse: collapse; border: 1.5px solid #333; margin-bottom: 10px; }
        .header-table td { border: 1px solid #333; padding: 4px 6px; vertical-align: middle; }
        .header-logo { width: 100px; text-align: center; font-size: 8px; }
        .header-logo img { max-width: 85px; max-height: 50px; }
        .header-title { text-align: center; font-weight: bold; font-size: 9px; }
        .header-code { width: 120px; font-size: 8px; }

        .main-title { text-align: center; font-size: 12px; font-weight: bold; margin: 8px 0 2px; color: #1c2437; }
        .main-subtitle { text-align: center; font-size: 8px; color: #555; margin-bottom: 6px; font-style: italic; }

        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; border: 1px solid #ccc; }
        .info-table td { padding: 3px 6px; font-size: 9px; border: 1px solid #ccc; }
        .info-label { font-weight: bold; color: #444; width: 150px; background: #f7f7f7; }

        .section-title { background: #1c2437; color: white; padding: 3px 8px; font-weight: bold; font-size: 9px; margin: 8px 0 4px; }

        .val-si    { color: #155724; font-weight: bold; }
        .val-no    { color: #721c24; font-weight: bold; }
        .val-na    { color: #6c757d; }

        .foto-box { text-align: center; border: 1px solid #ccc; padding: 4px; }
        .foto-box img { max-width: 150px; max-height: 120px; }
        .foto-box .cap { font-size: 7px; color: #555; margin-top: 2px; }

        .marco { background:#f7f7f7; border-left:3px solid #bd9751; padding:6px 8px; font-size:8px; line-height:1.4; margin:6px 0; text-align:justify; }
        .firma-table { width:100%; margin-top:18px; border-collapse:collapse; }
        .firma-table td { width:50%; text-align:center; padding:20px 6px 4px; border-top:1px solid #333; font-size:8px; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td class="header-logo" rowspan="2">
                <?php if (!empty($logoBase64)): ?>
                    <img src="<?= $logoBase64 ?>">
                <?php else: ?>
                    <strong style="font-size:7px;"><?= esc($cliente['nombre_cliente'] ?? '') ?></strong>
                <?php endif; ?>
            </td>
            <td class="header-title">SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO</td>
            <td class="header-code">Codigo: FT-SST-247<br>Version: 001</td>
        </tr>
        <tr>
            <td class="header-title" style="font-size:10px;">INSPECCION DEL PISCINERO / SALVAVIDAS</td>
            <td class="header-code">Fecha: <?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td>
        </tr>
    </table>

    <div class="main-title">INSPECCION DEL PISCINERO / SALVAVIDAS — Conforme a Ley 1209 de 2008 Art. 14</div>
    <div class="main-subtitle">Verificacion de idoneidad del personal a cargo de la piscina</div>

    <?php
    $v = function($val) {
        $val = $val ?? '';
        $cls = '';
        if ($val === 'SI') $cls = 'val-si';
        elseif ($val === 'NO') $cls = 'val-no';
        elseif ($val === 'NA') $cls = 'val-na';
        return '<span class="'.$cls.'">'.esc($val).'</span>';
    };
    $fDate = function($v) { return !empty($v) ? date('d/m/Y', strtotime($v)) : '-'; };
    ?>

    <div class="section-title">DATOS DE LA INSPECCION</div>
    <table class="info-table">
        <tr>
            <td class="info-label">CLIENTE:</td>
            <td><?= esc($cliente['nombre_cliente'] ?? '') ?></td>
            <td class="info-label">FECHA:</td>
            <td><?= date('d/m/Y', strtotime($inspeccion['fecha_inspeccion'])) ?></td>
        </tr>
        <tr>
            <td class="info-label">CONSULTOR:</td>
            <td colspan="3"><?= esc($consultor['nombre_consultor'] ?? '') ?></td>
        </tr>
    </table>

    <div class="section-title">DATOS PERSONALES</div>
    <table class="info-table">
        <tr>
            <td class="info-label">NOMBRE:</td>
            <td colspan="3"><?= esc($inspeccion['nombre_piscinero'] ?? '-') ?></td>
        </tr>
        <tr>
            <td class="info-label">CEDULA:</td>
            <td><?= esc($inspeccion['cedula'] ?? '-') ?></td>
            <td class="info-label">TELEFONO:</td>
            <td><?= esc($inspeccion['telefono'] ?? '-') ?></td>
        </tr>
        <tr>
            <td class="info-label">VINCULACION:</td>
            <td colspan="3"><?= esc($inspeccion['vinculacion'] ?? '-') ?></td>
        </tr>
        <tr>
            <td class="info-label">EMPRESA CONTRATISTA:</td>
            <td><?= esc($inspeccion['empresa_contratista'] ?? '-') ?></td>
            <td class="info-label">NIT:</td>
            <td><?= esc($inspeccion['nit_empresa_contratista'] ?? '-') ?></td>
        </tr>
    </table>

    <?php if (!empty($fotosBase64['foto_piscinero'])): ?>
    <div class="foto-box" style="margin:6px 0;">
        <img src="<?= $fotosBase64['foto_piscinero'] ?>">
        <div class="cap">Fotografia del piscinero</div>
    </div>
    <?php endif; ?>

    <div class="section-title">CERTIFICACIONES</div>
    <table class="info-table">
        <tr>
            <td class="info-label">CERT. RCP VIGENTE:</td>
            <td><?= $v($inspeccion['certificacion_rcp_vigente'] ?? '') ?></td>
            <td class="info-label">VENCIMIENTO RCP:</td>
            <td><?= $fDate($inspeccion['fecha_vencimiento_rcp']) ?></td>
        </tr>
        <tr>
            <td class="info-label">CURSO SALVAMENTO:</td>
            <td><?= $v($inspeccion['curso_salvamento_acuatico'] ?? '') ?></td>
            <td class="info-label">VENCIMIENTO SALV.:</td>
            <td><?= $fDate($inspeccion['fecha_vencimiento_salvamento']) ?></td>
        </tr>
    </table>

    <?php if (!empty($fotosBase64['foto_certificado_rcp']) || !empty($fotosBase64['foto_certificado_salvamento'])): ?>
    <table style="width:100%; margin:4px 0;">
        <tr>
            <?php if (!empty($fotosBase64['foto_certificado_rcp'])): ?>
            <td style="width:50%; text-align:center;">
                <div class="foto-box"><img src="<?= $fotosBase64['foto_certificado_rcp'] ?>"><div class="cap">Certificado RCP</div></div>
            </td>
            <?php endif; ?>
            <?php if (!empty($fotosBase64['foto_certificado_salvamento'])): ?>
            <td style="width:50%; text-align:center;">
                <div class="foto-box"><img src="<?= $fotosBase64['foto_certificado_salvamento'] ?>"><div class="cap">Certificado salvamento</div></div>
            </td>
            <?php endif; ?>
        </tr>
    </table>
    <?php endif; ?>

    <div class="section-title">AFILIACIONES SST</div>
    <table class="info-table">
        <tr>
            <td class="info-label">ARL VIGENTE:</td>
            <td><?= $v($inspeccion['afiliacion_arl_vigente'] ?? '') ?></td>
            <td class="info-label">EPS VIGENTE:</td>
            <td><?= $v($inspeccion['afiliacion_eps_vigente'] ?? '') ?></td>
        </tr>
        <tr>
            <td class="info-label">EXAMENES MED. OCUP.:</td>
            <td><?= $v($inspeccion['examenes_medicos_ocupacionales'] ?? '') ?></td>
            <td class="info-label">ULT. EXAMEN:</td>
            <td><?= $fDate($inspeccion['fecha_ultimo_examen_medico']) ?></td>
        </tr>
    </table>

    <div class="section-title">DOTACION EPP</div>
    <table class="info-table">
        <tr>
            <td class="info-label">DOTACION ENTREGADA:</td>
            <td><?= $v($inspeccion['dotacion_epp_entregada'] ?? '') ?></td>
            <td class="info-label">GAFAS QUIMICAS:</td>
            <td><?= $v($inspeccion['gafas_proteccion_quimica'] ?? '') ?></td>
        </tr>
        <tr>
            <td class="info-label">GUANTES NITRILO:</td>
            <td><?= $v($inspeccion['guantes_nitrilo'] ?? '') ?></td>
            <td class="info-label">CARETA:</td>
            <td><?= $v($inspeccion['careta_proteccion'] ?? '') ?></td>
        </tr>
        <tr>
            <td class="info-label">DELANTAL IMPERM.:</td>
            <td colspan="3"><?= $v($inspeccion['delantal_impermeable'] ?? '') ?></td>
        </tr>
    </table>

    <div class="section-title">CAPACITACION</div>
    <table class="info-table">
        <tr>
            <td class="info-label">MANEJO QUIMICOS:</td>
            <td><?= $v($inspeccion['capacitacion_manejo_quimicos'] ?? '') ?></td>
            <td class="info-label">HOJAS SEGURIDAD:</td>
            <td><?= $v($inspeccion['conocimiento_hojas_seguridad'] ?? '') ?></td>
        </tr>
        <tr>
            <td class="info-label">PLAN EMERGENCIA:</td>
            <td colspan="3"><?= $v($inspeccion['conocimiento_plan_emergencia'] ?? '') ?></td>
        </tr>
    </table>

    <div class="section-title">HORARIO OPERATIVO</div>
    <table class="info-table">
        <tr>
            <td class="info-label">CUBRE OPERACION:</td>
            <td><?= $v($inspeccion['horario_cubre_operacion_piscina'] ?? '') ?></td>
            <td class="info-label">INICIO:</td>
            <td><?= esc($inspeccion['horario_inicio'] ?? '-') ?></td>
        </tr>
        <tr>
            <td class="info-label">FIN:</td>
            <td colspan="3"><?= esc($inspeccion['horario_fin'] ?? '-') ?></td>
        </tr>
    </table>

    <?php if (!empty($inspeccion['observaciones'])): ?>
    <div class="section-title">OBSERVACIONES</div>
    <p style="font-size:9px; line-height:1.4;"><?= nl2br(esc($inspeccion['observaciones'])) ?></p>
    <?php endif; ?>

    <div class="section-title">MARCO NORMATIVO CONSULTADO</div>
    <div class="marco">
        <?= nl2br(esc($inspeccion['marco_normativo'] ?? $marcoNormativo ?? '')) ?>
        <br><br>
        <em>Aviso: La presente inspeccion documenta condiciones observadas en sitio bajo criterios SST. NO sustituye la certificacion del personal salvavidas por entidades acreditadas en RCP y salvamento acuatico conforme al articulo 14 de la Ley 1209 de 2008. Los hallazgos deben ser remitidos al administrador y a la empresa contratista para cierre de las acciones correctivas.</em>
    </div>

    <table class="firma-table">
        <tr>
            <td>Consultor SST<br><strong><?= esc($consultor['nombre_consultor'] ?? '') ?></strong></td>
            <td>Recibido por el cliente<br><strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong></td>
        </tr>
    </table>

</body>
</html>
