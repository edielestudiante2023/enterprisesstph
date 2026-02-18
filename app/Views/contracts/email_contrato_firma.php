<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px;">
    <div style="max-width: 700px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">

        <!-- Header -->
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center;">
            <h1 style="color: white; margin: 0; font-size: 22px;">Contrato de Prestacion de Servicios SST</h1>
            <p style="color: rgba(255,255,255,0.8); margin: 10px 0 0;">Solicitud de Firma Digital</p>
        </div>

        <!-- Body -->
        <div style="padding: 30px;">
            <p style="color: #333; font-size: 16px;">
                Estimado(a) <strong><?= esc($nombreFirmante) ?></strong>,
            </p>

            <p style="color: #555;">
                <?= esc($mensaje) ?>
            </p>

            <!-- Resumen del contrato -->
            <div style="background: #f8f9fa; border-radius: 8px; padding: 20px; margin: 20px 0;">
                <h3 style="color: #667eea; margin-top: 0; font-size: 16px;">Resumen del Contrato</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; color: #666; font-weight: bold; width: 40%;">Numero:</td>
                        <td style="padding: 8px 0; color: #333;"><?= esc($contrato['numero_contrato']) ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #666; font-weight: bold;">Contratante:</td>
                        <td style="padding: 8px 0; color: #333;"><?= esc($contrato['nombre_cliente']) ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #666; font-weight: bold;">Contratista:</td>
                        <td style="padding: 8px 0; color: #333;">CYCLOID TALENT S.A.S.</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #666; font-weight: bold;">Vigencia:</td>
                        <td style="padding: 8px 0; color: #333;">
                            <?= date('d/m/Y', strtotime($contrato['fecha_inicio'])) ?> al <?= date('d/m/Y', strtotime($contrato['fecha_fin'])) ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #666; font-weight: bold;">Valor:</td>
                        <td style="padding: 8px 0; color: #333;">$<?= number_format($contrato['valor_contrato'], 0, ',', '.') ?> COP</td>
                    </tr>
                </table>
            </div>

            <!-- CONTRATO COMPLETO -->
            <div style="border: 2px solid #667eea; border-radius: 8px; padding: 25px; margin: 20px 0;">
                <h2 style="text-align: center; color: #333; margin-top: 0; font-size: 18px;">CONTRATO DE PRESTACION DE SERVICIOS</h2>
                <h3 style="text-align: center; color: #555; font-size: 14px; margin-bottom: 20px;">
                    ENTRE <?= strtoupper(esc($contrato['nombre_cliente'])) ?> - PROPIEDAD HORIZONTAL Y CYCLOID TALENT S.A.S.
                </h3>

                <!-- Introduccion -->
                <p style="color: #333; font-size: 13px; text-align: justify; line-height: 1.6;">
                    Entre <strong><?= strtoupper(esc($contrato['nombre_cliente'])) ?></strong> NIT <strong><?= esc($contrato['nit_cliente']) ?></strong>; entidad legalmente existente y constituida, con domicilio principal en <?= esc($contrato['direccion_cliente']) ?>, representado por <strong><?= strtoupper(esc($contrato['nombre_rep_legal_cliente'])) ?></strong>, mayor de edad, identificada con cedula de ciudadania numero <strong><?= esc($contrato['cedula_rep_legal_cliente']) ?></strong>, en adelante y para los efectos del presente contrato se denominara <strong>EL CONTRATANTE</strong> de una parte, y de la otra <strong>CYCLOID TALENT S.A.S</strong>, NIT. <strong>901.653.912-2</strong>; entidad legalmente existente y constituida, con domicilio principal en la ciudad de Soacha Cundinamarca, Cl 13 No. 31 - 106, representada por <strong><?= strtoupper(esc($contrato['nombre_rep_legal_contratista'])) ?></strong>, mayor de edad, identificada con cedula de ciudadania numero <strong><?= esc($contrato['cedula_rep_legal_contratista']) ?></strong>, en adelante y para los efectos del presente contrato se denominara <strong>EL CONTRATISTA</strong>, han acordado celebrar un contrato de prestacion de servicios el cual se regira por las siguientes:
                </p>

                <h3 style="text-align: center; color: #667eea; font-size: 15px;">CLAUSULAS</h3>

                <!-- Clausula Primera -->
                <div style="margin-bottom: 15px;">
                    <h4 style="color: #333; font-size: 13px; margin-bottom: 5px;">PRIMERA - OBJETO DEL CONTRATO</h4>
                    <p style="color: #333; font-size: 13px; text-align: justify; line-height: 1.6; margin-top: 0;">
                        <strong>EL CONTRATISTA</strong> se compromete a proporcionar servicios de consultoria para la gestion del Sistema de Gestion de Seguridad y Salud en el Trabajo (SG-SST) a favor de <strong>EL CONTRATANTE</strong> mediante la plataforma <strong>EnterpriseSST</strong>. Esta plataforma facilita la gestion documental, la programacion de actividades y el monitoreo en tiempo real de los planes de trabajo. Ademas, se asignara al profesional SG-SST <strong><?= esc($contrato['nombre_responsable_sgsst'] ?? '') ?></strong>, identificado con cedula de ciudadania <strong><?= esc($contrato['cedula_responsable_sgsst'] ?? '') ?></strong> y licencia ocupacional numero <strong><?= esc($contrato['licencia_responsable_sgsst'] ?? '') ?></strong>, para garantizar el cumplimiento de los estandares minimos de la <strong>Resolucion 0312 de 2019</strong>. Estos servicios incluiran la supervision y seguimiento continuo del sistema, la capacitacion a colaboradores en mision y la implementacion de medidas preventivas que contribuyan a mejorar la seguridad laboral.
                    </p>
                </div>

                <!-- Clausula Segunda -->
                <div style="margin-bottom: 15px;">
                    <h4 style="color: #333; font-size: 13px; margin-bottom: 5px;">SEGUNDA - EJECUCION DEL CONTRATO</h4>
                    <p style="color: #333; font-size: 13px; text-align: justify; line-height: 1.6; margin-top: 0;">
                        La ejecucion de este contrato se realizara principalmente mediante la plataforma <strong>EnterpriseSST</strong>, que proporcionara acceso continuo a toda la documentacion, cronogramas y recursos necesarios para la gestion del SG-SST. Adicionalmente, <strong>EL CONTRATISTA</strong> llevara a cabo visitas presenciales periodicas, con una frecuencia minima <strong><?= strtoupper(esc($contrato['frecuencia_visitas'] ?? '')) ?></strong> de acuerdo con el cronograma de actividades anual, y concertadas con <strong>EL CONTRATANTE</strong>.
                    </p>
                </div>

                <!-- Clausula Tercera -->
                <div style="margin-bottom: 15px;">
                    <h4 style="color: #333; font-size: 13px; margin-bottom: 5px;">TERCERA - OBLIGACIONES</h4>
                    <p style="color: #333; font-size: 13px; text-align: justify; line-height: 1.6; margin-top: 0;">
                        <strong>DE PARTE DEL CONTRATANTE:</strong><br>
                        1. Realizar el pago del valor estipulado en la clausula septima.<br>
                        2. Verificar los documentos proporcionados por <strong>EL CONTRATISTA</strong>, que acrediten su idoneidad, licencia vigente y planilla de seguridad social mensual.<br>
                        3. Participar activamente en la construccion y ejecucion de los planes de accion propuestos por <strong>EL CONTRATISTA</strong>.<br>
                        4. Asegurar el acceso y uso adecuado de la plataforma <strong>EnterpriseSST</strong>.<br>
                        5. En caso de ser necesario, contratar a un profesional idoneo para auditar la gestion llevada a cabo por <strong>EL CONTRATISTA</strong>.<br><br>
                        <strong>OBLIGACIONES DE EL CONTRATISTA:</strong><br>
                        1. Evaluar los estandares minimos segun la <strong>Resolucion 0312 de 2019</strong>, demostrando un nivel de cumplimiento igual o superior al <strong>86.75%</strong>.<br>
                        2. Mantener y actualizar continuamente el sistema de gestion de SST en la plataforma <strong>EnterpriseSST</strong>.<br>
                        3. Proporcionar todos los documentos, reportes y evidencias requeridos a traves de la plataforma.<br>
                        4. Realizar modificaciones necesarias a los formatos de gestion, previa aprobacion de la administracion.<br>
                        5. Planificar, organizar y dirigir las actividades que promuevan el cumplimiento de los estandares minimos.<br>
                        6. Reportar al Ministerio de Trabajo, manteniendo toda la informacion documentada en la plataforma.<br>
                        7. Realizar visitas en campo cuando sea necesario.<br>
                        8. Entregar informes detallados de cada visita y auditoria.
                    </p>
                </div>

                <!-- Clausula Cuarta -->
                <div style="margin-bottom: 15px;">
                    <h4 style="color: #333; font-size: 13px; margin-bottom: 5px;">CUARTA - DURACION</h4>
                    <p style="color: #333; font-size: 13px; text-align: justify; line-height: 1.6; margin-top: 0;">
                        <?php
                            $fechaInicioObj = new \DateTime($contrato['fecha_inicio']);
                            $fechaFinObj = new \DateTime($contrato['fecha_fin']);
                            $diff = $fechaInicioObj->diff($fechaFinObj);
                            $mesesContrato = ($diff->y * 12) + $diff->m;
                            $mesesNombres = ['01'=>'enero','02'=>'febrero','03'=>'marzo','04'=>'abril','05'=>'mayo','06'=>'junio','07'=>'julio','08'=>'agosto','09'=>'septiembre','10'=>'octubre','11'=>'noviembre','12'=>'diciembre'];
                        ?>
                        <?php if (!empty($contrato['clausula_cuarta_duracion'])): ?>
                            <?php
                                $c4 = esc($contrato['clausula_cuarta_duracion']);
                                // Convertir markdown **texto** → <strong>texto</strong>
                                $c4 = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $c4);
                                // Eliminar prefijos de encabezado markdown (# ## ###)
                                $c4 = preg_replace('/^#{1,4}\s*/m', '', $c4);
                                echo nl2br($c4);
                            ?>
                        <?php else: ?>
                            La duracion de este contrato es de <strong>(<?= $mesesContrato ?>) meses</strong> desde el <strong><?= $fechaInicioObj->format('d') ?> de <?= $mesesNombres[$fechaInicioObj->format('m')] ?? '' ?> de <?= $fechaInicioObj->format('Y') ?></strong> y con finalizacion maxima a <strong><?= $fechaFinObj->format('d') ?> de <?= $mesesNombres[$fechaFinObj->format('m')] ?? '' ?> de <?= $fechaFinObj->format('Y') ?></strong>.<br><br>
                            <strong>PARAGRAFO:</strong> Sobre el presente contrato no opera la prorroga automatica. La intencion de prorroga debera ser discutida entre las partes al finalizar el plazo inicialmente pactado y debera constar por escrito.
                        <?php endif; ?>
                    </p>
                </div>

                <!-- Clausula Quinta -->
                <div style="margin-bottom: 15px;">
                    <h4 style="color: #333; font-size: 13px; margin-bottom: 5px;">QUINTA - EXCLUSION DE LA RELACION LABORAL</h4>
                    <p style="color: #333; font-size: 13px; text-align: justify; line-height: 1.6; margin-top: 0;">
                        Dada la naturaleza de este contrato, no existira relacion laboral alguna entre <strong>EL CONTRATANTE</strong> y <strong>EL CONTRATISTA</strong>, o el personal que este contrate para apoyar la ejecucion del objeto contractual. <strong>EL CONTRATISTA</strong> se compromete con <strong>EL CONTRATANTE</strong> a ejecutar en forma independiente y con plena autonomia tecnica, el objeto mencionado en este documento.
                    </p>
                </div>

                <!-- Clausula Sexta -->
                <div style="margin-bottom: 15px;">
                    <h4 style="color: #333; font-size: 13px; margin-bottom: 5px;">SEXTA - CLAUSULA DE CONFIDENCIALIDAD</h4>
                    <p style="color: #333; font-size: 13px; text-align: justify; line-height: 1.6; margin-top: 0;">
                        <strong>EL CONTRATISTA</strong> debera mantener la confidencialidad sobre toda la informacion de <strong>EL CONTRATANTE</strong> que conozca o a la que tenga acceso. Se tendra como informacion confidencial cualquier informacion no divulgada que posea legitimamente <strong>EL CONTRATANTE</strong> que pueda usarse en alguna actividad academica, productiva, industrial o comercial y que sea susceptible de comunicarse a un tercero.
                    </p>
                </div>

                <!-- Clausula Septima -->
                <div style="margin-bottom: 15px;">
                    <h4 style="color: #333; font-size: 13px; margin-bottom: 5px;">SEPTIMA - VALOR DEL CONTRATO - FORMA DE PAGO Y PENALIDADES</h4>
                    <p style="color: #333; font-size: 13px; text-align: justify; line-height: 1.6; margin-top: 0;">
                        El valor del presente contrato es de <strong>$<?= number_format($contrato['valor_contrato'], 0, ',', '.') ?> PESOS M/CTE ANTES DE IVA</strong>, de forma <strong>MENSUAL</strong> en <strong><?= esc($contrato['numero_cuotas'] ?? '') ?> facturas</strong> por valor de <strong>$<?= number_format($contrato['valor_mensual'] ?? 0, 0, ',', '.') ?> PESOS ANTES DE IVA</strong>. Las facturas emitidas por <strong>EL CONTRATISTA</strong> deberan ser pagadas por <strong>EL CONTRATANTE</strong> dentro los <strong>ocho (8) dias calendario</strong> contados a partir de la fecha de su emision.<br><br>
                        <strong>INTERESES POR MORA:</strong> Si el pago de una factura no se ha realizado a los <strong>sesenta (60) dias calendario</strong> posterior a su fecha de vencimiento, <strong>EL CONTRATANTE</strong> debera pagar a <strong>EL CONTRATISTA</strong> un interes de mora del <strong>uno punto cinco por ciento (1,5%) mensual</strong>. <strong>EL CONTRATISTA</strong> hara la presentacion de factura por transferencia bancaria al banco <strong><?= esc($contrato['banco'] ?? '') ?></strong>, cuenta de <strong><?= esc($contrato['tipo_cuenta'] ?? '') ?></strong> No. <strong><?= esc($contrato['cuenta_bancaria'] ?? '') ?></strong> a nombre de <strong>EL CONTRATISTA</strong>.<br><br>
                        <strong>PARAGRAFO:</strong> Seran requisitos indispensables para el pago que <strong>EL CONTRATISTA</strong> presente planilla integrada de liquidacion de aportes (PILA).
                    </p>
                </div>

                <!-- Clausula Octava -->
                <div style="margin-bottom: 15px;">
                    <h4 style="color: #333; font-size: 13px; margin-bottom: 5px;">OCTAVA - PROCEDENCIA DE RECURSOS</h4>
                    <p style="color: #333; font-size: 13px; text-align: justify; line-height: 1.6; margin-top: 0;">
                        <strong>EL CONTRATANTE</strong> declara bajo la gravedad de juramento que los recursos, fondos, dineros, activos o bienes relacionados con este contrato, son de procedencia licita y no estan vinculados con el lavado de activos ni con ninguno de sus delitos fuente.
                    </p>
                </div>

                <!-- Clausula Novena -->
                <div style="margin-bottom: 15px;">
                    <h4 style="color: #333; font-size: 13px; margin-bottom: 5px;">NOVENA - CESION</h4>
                    <p style="color: #333; font-size: 13px; text-align: justify; line-height: 1.6; margin-top: 0;">
                        <strong>EL CONTRATISTA</strong> no podra ceder total ni parcialmente, asi como subcontratar, la ejecucion del presente contrato, salvo previa autorizacion expresa y escrita de <strong>EL CONTRATANTE</strong>.
                    </p>
                </div>

                <!-- Clausula Decima -->
                <div style="margin-bottom: 15px;">
                    <h4 style="color: #333; font-size: 13px; margin-bottom: 5px;">DECIMA - LEALTAD PROFESIONAL</h4>
                    <p style="color: #333; font-size: 13px; text-align: justify; line-height: 1.6; margin-top: 0;">
                        Las partes acuerdan que no podran vincular laboralmente dentro de sus companias a personal de planta, del cual hubiera conocido su desempeno profesional a causa de la relacion que surgio en la ejecucion del presente contrato. En caso de que alguna de las partes, omita esta clausula habra lugar a efectuar un cobro equivalente a <strong>doce (12) salarios minimos mensuales vigentes</strong> por cada trabajador.
                    </p>
                </div>

                <!-- Clausula Onceava -->
                <div style="margin-bottom: 15px;">
                    <h4 style="color: #333; font-size: 13px; margin-bottom: 5px;">ONCEAVA - PREVENCION DEL RIESGO DE LAVADO DE ACTIVOS (SAGRILAFT)</h4>
                    <p style="color: #333; font-size: 13px; text-align: justify; line-height: 1.6; margin-top: 0;">
                        <strong>LAS PARTES</strong> certifican que sus recursos no provienen ni se destinan al ejercicio de ninguna actividad ilicita o de actividades conexas al lavado de activos, provenientes de estas o de actividades relacionadas con la financiacion del terrorismo.
                    </p>
                </div>

                <!-- Clausula Doceava -->
                <div style="margin-bottom: 15px;">
                    <h4 style="color: #333; font-size: 13px; margin-bottom: 5px;">DOCEAVA - ALTO RIESGO EN LA COPROPIEDAD</h4>
                    <p style="color: #333; font-size: 13px; text-align: justify; line-height: 1.6; margin-top: 0;">
                        Toda actividad ejecutada dentro de la copropiedad que implique alto riesgo, como trabajos en espacios confinados o trabajos en alturas, debera contar con la aprobacion y la revision documental de los contratistas responsables antes de su ejecucion. En caso de que el profesional a cargo del SG-SST no tenga conocimiento previo de dichas actividades, <strong>EL CONTRATISTA</strong> no asumira ninguna responsabilidad administrativa ni civil por cualquier accidente o incidente que pueda ocurrir.
                    </p>
                </div>

                <!-- Clausula Treceava -->
                <div style="margin-bottom: 15px;">
                    <h4 style="color: #333; font-size: 13px; margin-bottom: 5px;">TRECEAVA - AUTORIZACION PARA USO DIGITAL DE LA FIRMA</h4>
                    <p style="color: #333; font-size: 13px; text-align: justify; line-height: 1.6; margin-top: 0;">
                        <strong>EL CONTRATANTE</strong> autoriza expresamente a <strong>EL CONTRATISTA</strong> a utilizar la firma digital consignada en el presente contrato para su extraccion y uso en formato digital. Esta firma digital podra ser aplicada a todos los documentos relacionados con el Sistema de Gestion de Seguridad y Salud en el Trabajo (SG-SST). <strong>EL Cliente</strong> manifiesta que la firma digital consignada en el presente documento tendra el mismo valor juridico que su firma manuscrita.
                    </p>
                </div>

                <!-- Terminacion -->
                <div style="margin-bottom: 15px;">
                    <h4 style="color: #333; font-size: 13px; margin-bottom: 5px;">TERMINACION DEL CONTRATO</h4>
                    <p style="color: #333; font-size: 13px; text-align: justify; line-height: 1.6; margin-top: 0;">
                        El presente contrato se terminara por las siguientes causas: <strong>1.</strong> Mutuo acuerdo. <strong>2.</strong> Incumplimiento de las obligaciones. <strong>3.</strong> Liquidacion obligatoria de cualquiera de las partes. <strong>4.</strong> Inclusion en listados sobre financiacion del terrorismo o lavado de activos. <strong>5.</strong> Actuar en forma contraria a las buenas costumbres. <strong>6.</strong> Cualquier otra causa prevista en la ley. <strong>7.</strong> Fuerza mayor o caso fortuito. <strong>8.</strong> Incumplimiento en la confidencialidad o uso indebido de la informacion.
                    </p>
                </div>
            </div>

            <!-- Boton CTA -->
            <div style="text-align: center; margin: 30px 0;">
                <a href="<?= esc($urlFirma) ?>"
                   style="display: inline-block; background: linear-gradient(135deg, #28a745 0%, #218838 100%); color: white; padding: 15px 40px; text-decoration: none; border-radius: 8px; font-size: 16px; font-weight: bold;">
                    Revisar y Firmar Contrato
                </a>
            </div>

            <div style="background: #fff3cd; border-radius: 8px; padding: 15px; margin: 20px 0;">
                <p style="color: #856404; margin: 0; font-size: 13px;">
                    <strong>Importante:</strong> Este enlace es personal e intransferible. Tiene validez de 7 dias a partir de la fecha de envio.
                </p>
            </div>

            <div style="background: #e8f4fd; border-radius: 8px; padding: 15px; margin: 20px 0; border-left: 4px solid #0d6efd;">
                <p style="color: #0d6efd; margin: 0; font-size: 13px;">
                    <strong>Sobre el documento PDF:</strong> Si desea obtener el contrato en formato PDF, puede solicitarlo a su asesor comercial. Una vez firmado el contrato, recibira sus credenciales de acceso a la plataforma <strong>EnterpriseSST</strong> donde podra descargarlo en la seccion de Documentos.
                </p>
            </div>

            <?php if (!empty($esCopia)): ?>
            <div style="background: #d1ecf1; border-radius: 8px; padding: 15px; margin: 20px 0;">
                <p style="color: #0c5460; margin: 0; font-size: 13px;">
                    <strong>Nota:</strong> Este correo es una copia informativa. La firma debe ser realizada por el Representante Legal del cliente.
                </p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Footer -->
        <div style="background: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #eee;">
            <p style="color: #999; font-size: 12px; margin: 0;">
                Enterprise SST - Sistema de Gestion de Seguridad y Salud en el Trabajo<br>
                Este es un mensaje automatico, por favor no responda a este correo.<br>
                Enviado el <?= date('d/m/Y H:i:s') ?>
            </p>
        </div>
    </div>
</body>
</html>
