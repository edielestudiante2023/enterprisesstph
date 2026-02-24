<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
    <!-- Header -->
    <div style="background: linear-gradient(135deg, #2c3e50 0%, #1a252f 100%); padding: 30px 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <div style="font-size: 32px; color: #bd9751; margin-bottom: 8px;">&#128737;</div>
        <h2 style="color: white; margin: 0; font-size: 20px;">Designacion como Vigia SST</h2>
        <p style="color: rgba(255,255,255,0.7); margin: 5px 0 0; font-size: 14px;">Sistema de Gestion de Seguridad y Salud en el Trabajo</p>
    </div>

    <!-- Body -->
    <div style="padding: 30px 25px; background: #f8f9fa;">
        <p style="color: #333; line-height: 1.6;">
            Estimado/a <strong><?= esc($carta['nombre_vigia']) ?></strong>,
        </p>

        <p style="color: #333; line-height: 1.6;">
            Se le ha designado como <strong>Vigia de Seguridad y Salud en el Trabajo</strong>
            en <strong><?= esc($cliente['nombre_cliente'] ?? '') ?></strong>.
        </p>

        <!-- Datos box -->
        <div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border: 1px solid #e0e0e0;">
            <p style="margin: 0 0 8px; font-size: 14px;"><strong>Nombre:</strong> <?= esc($carta['nombre_vigia']) ?></p>
            <p style="margin: 0 0 8px; font-size: 14px;"><strong>Documento:</strong> CC <?= esc($carta['documento_vigia']) ?></p>
            <p style="margin: 0; font-size: 14px;"><strong>Entidad:</strong> <?= esc($cliente['nombre_cliente'] ?? '') ?></p>
        </div>

        <p style="color: #333; line-height: 1.6;">
            Se requiere su firma digital para formalizar la designacion. Por favor haga clic en el siguiente boton
            para revisar la carta y firmarla:
        </p>

        <!-- CTA Button -->
        <div style="text-align: center; margin: 30px 0;">
            <a href="<?= esc($urlFirma) ?>"
               style="display: inline-block; padding: 15px 40px; background: linear-gradient(135deg, #28a745, #218838);
                      color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px;
                      box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);">
                Revisar y Firmar Carta
            </a>
        </div>

        <!-- Fallback URL -->
        <p style="color: #666; font-size: 12px;">O copie este enlace en su navegador:</p>
        <p style="word-break: break-all; background: #e9ecef; padding: 10px; border-radius: 4px; font-size: 12px; color: #555;">
            <?= esc($urlFirma) ?>
        </p>

        <!-- Info -->
        <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 15px; margin: 20px 0;">
            <p style="margin: 0; font-size: 13px; color: #856404;">
                <strong>&#9888; Importante:</strong><br>
                &bull; Este enlace tiene validez de <strong>7 dias</strong>.<br>
                &bull; El enlace es personal e intransferible.<br>
                &bull; Al firmar, acepta su designacion como Vigia SST y autoriza el tratamiento de sus datos personales conforme a la Ley 1581 de 2012.
            </p>
        </div>
    </div>

    <!-- Footer -->
    <div style="padding: 20px; text-align: center; border-top: 1px solid #e0e0e0; background: #f0f0f0; border-radius: 0 0 8px 8px;">
        <p style="color: #999; font-size: 12px; margin: 0;">
            Este correo fue enviado por Cycloid Talent SAS<br>
            <a href="https://cycloidtalent.com" style="color: #bd9751;">www.cycloidtalent.com</a>
        </p>
        <p style="color: #bbb; font-size: 11px; margin: 8px 0 0;">
            Si usted no esperaba este correo, puede ignorarlo.
        </p>
    </div>
</div>
