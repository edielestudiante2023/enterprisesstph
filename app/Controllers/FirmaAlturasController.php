<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use Dompdf\Dompdf;
use Dompdf\Options;

class FirmaAlturasController extends BaseController
{
    protected ClientModel $clientModel;

    public function __construct()
    {
        $this->clientModel = new ClientModel();
    }

    /**
     * Vista pública: mostrar protocolo + canvas de firma
     */
    public function firmar($token)
    {
        $cliente = $this->clientModel->where('token_firma_alturas', $token)->first();

        if (!$cliente) {
            return view('firma_alturas/error', [
                'mensaje' => 'El enlace de firma no es válido o ya fue utilizado.',
            ]);
        }

        if (!empty($cliente['token_firma_alturas_exp']) && strtotime($cliente['token_firma_alturas_exp']) < time()) {
            return view('firma_alturas/error', [
                'mensaje' => 'El enlace de firma ha expirado. Contacte a su consultor SST para solicitar uno nuevo.',
            ]);
        }

        if ($cliente['protocolo_alturas_firmado'] == 1) {
            return view('firma_alturas/error', [
                'mensaje' => 'Este protocolo ya fue firmado por el representante legal.',
            ]);
        }

        return view('firma_alturas/form', [
            'cliente' => $cliente,
            'token'   => $token,
        ]);
    }

    /**
     * Procesar firma (POST público, sin auth)
     */
    public function procesarFirma()
    {
        $token = $this->request->getPost('token');
        $firmaImagen = $this->request->getPost('firma_imagen');

        $cliente = $this->clientModel->where('token_firma_alturas', $token)->first();

        if (!$cliente || $cliente['protocolo_alturas_firmado'] == 1) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Token inválido o ya firmado']);
        }

        if (!empty($cliente['token_firma_alturas_exp']) && strtotime($cliente['token_firma_alturas_exp']) < time()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Token expirado']);
        }

        if (empty($firmaImagen)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Firma requerida']);
        }

        // Guardar imagen de firma
        $firmaDir = FCPATH . 'uploads/firmas-representantes/';
        if (!is_dir($firmaDir)) {
            mkdir($firmaDir, 0755, true);
        }

        $firmaData = str_replace('data:image/png;base64,', '', $firmaImagen);
        $firmaData = base64_decode($firmaData);
        $firmaFileName = 'firma_rep_legal_' . $cliente['id_cliente'] . '_' . time() . '.png';
        file_put_contents($firmaDir . $firmaFileName, $firmaData);

        // Actualizar tbl_clientes
        $this->clientModel->update($cliente['id_cliente'], [
            'firma_representante_legal'  => 'firmas-representantes/' . $firmaFileName,
            'protocolo_alturas_firmado'  => 1,
            'firma_alturas_fecha'        => date('Y-m-d H:i:s'),
            'firma_alturas_ip'           => $this->request->getIPAddress(),
            'token_firma_alturas'        => null, // Invalidar token
        ]);

        // Propagar firma a contratos del cliente que no tengan firma
        $this->propagarFirmaAContratos($cliente['id_cliente'], 'firmas-representantes/' . $firmaFileName);

        // Generar PDF y subir a reportes
        $fechaFirma = date('Y-m-d H:i:s');
        $ipFirma = $this->request->getIPAddress();
        $firmaPath = $firmaDir . $firmaFileName;

        try {
            $pdfPath = $this->generarPdf($cliente, $firmaPath, $fechaFirma, $ipFirma);
            $this->uploadToReportes($cliente, $pdfPath, $fechaFirma);
        } catch (\Exception $e) {
            log_message('error', 'Error generando PDF protocolo alturas: ' . $e->getMessage());
        }

        // Notificar al consultor asignado
        $this->notificarConsultor($cliente);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Protocolo firmado exitosamente',
        ]);
    }

    /**
     * Generar PDF del protocolo firmado usando DOMPDF
     */
    private function generarPdf(array $cliente, string $firmaAbsPath, string $fechaFirma, string $ipFirma): string
    {
        // Logo del cliente en base64
        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $mime = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        // Firma en base64
        $firmaBase64 = '';
        if (file_exists($firmaAbsPath)) {
            $mime = mime_content_type($firmaAbsPath);
            $firmaBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($firmaAbsPath));
        }

        $html = view('firma_alturas/pdf', [
            'cliente'     => $cliente,
            'logoBase64'  => $logoBase64,
            'firmaBase64' => $firmaBase64,
            'fechaFirma'  => $fechaFirma,
            'ipFirma'     => $ipFirma,
        ]);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        // Guardar PDF
        $pdfDir = FCPATH . 'uploads/inspecciones/pdfs/';
        if (!is_dir($pdfDir)) {
            mkdir($pdfDir, 0755, true);
        }

        $pdfFileName = 'protocolo_alturas_' . $cliente['id_cliente'] . '_' . date('Ymd_His') . '.pdf';
        file_put_contents($pdfDir . $pdfFileName, $dompdf->output());

        return 'uploads/inspecciones/pdfs/' . $pdfFileName;
    }

    /**
     * Copiar PDF a carpeta del cliente y registrar en tbl_reporte
     */
    private function uploadToReportes(array $cliente, string $pdfPath, string $fechaFirma): bool
    {
        $reporteModel = new ReporteModel();
        $nitCliente = $cliente['nit_cliente'] ?? '';

        if (empty($nitCliente)) {
            return false;
        }

        // Verificar si ya existe reporte para este cliente
        $existente = $reporteModel
            ->where('id_cliente', $cliente['id_cliente'])
            ->where('id_report_type', 6)
            ->where('id_detailreport', 44)
            ->like('observaciones', 'protocolo_alturas_cliente:' . $cliente['id_cliente'])
            ->first();

        // Copiar a UPLOADS_PATH/{nit_cliente}/
        $destDir = UPLOADS_PATH . $nitCliente;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $fileName = 'protocolo_alturas_' . $cliente['id_cliente'] . '_' . date('Ymd_His') . '.pdf';
        $destPath = $destDir . '/' . $fileName;
        copy(FCPATH . $pdfPath, $destPath);

        $data = [
            'titulo_reporte'  => 'PROTOCOLO ALTURAS - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . date('Y-m-d', strtotime($fechaFirma)),
            'id_detailreport' => 44,
            'id_report_type'  => 6,
            'id_cliente'      => $cliente['id_cliente'],
            'estado'          => 'CERRADO',
            'observaciones'   => 'Generado automaticamente al firmar protocolo de trabajo en alturas. protocolo_alturas_cliente:' . $cliente['id_cliente'],
            'enlace'          => base_url(UPLOADS_URL_PREFIX . '/' . $nitCliente . '/' . $fileName),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        if ($existente) {
            return $reporteModel->update($existente['id_reporte'], $data);
        }

        $data['created_at'] = $fechaFirma;
        return $reporteModel->save($data);
    }

    /**
     * Enviar email al consultor confirmando la firma
     */
    private function notificarConsultor(array $cliente): void
    {
        if (empty($cliente['id_consultor'])) return;

        $consultorModel = new ConsultantModel();
        $consultor = $consultorModel->find($cliente['id_consultor']);
        if (!$consultor || empty($consultor['correo_consultor'])) return;

        $email = new \SendGrid\Mail\Mail();
        $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "Cycloid Talent");
        $email->setSubject("Protocolo Alturas firmado - " . $cliente['nombre_cliente']);
        $email->addTo($consultor['correo_consultor'], $consultor['nombre_consultor']);
        $email->addContent("text/html",
            "<p>El representante legal de <strong>" . htmlspecialchars($cliente['nombre_cliente']) .
            "</strong> ha firmado el Protocolo de Notificación de Trabajo en Alturas.</p>" .
            "<p>Fecha: " . date('Y-m-d H:i:s') . "</p>"
        );

        try {
            $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
            $sendgrid->send($email);
        } catch (\Exception $e) {
            log_message('error', 'Error notificando consultor firma alturas: ' . $e->getMessage());
        }
    }

    /**
     * Generar token y enviar email a un cliente específico.
     * Usado por el comando Spark y por el onboarding.
     */
    public static function enviarProtocolo(int $idCliente): array
    {
        $clientModel = new ClientModel();
        $cliente = $clientModel->find($idCliente);
        if (!$cliente) return ['success' => false, 'error' => 'Cliente no encontrado'];
        if (empty($cliente['correo_cliente'])) return ['success' => false, 'error' => 'Sin correo'];

        // Generar token
        $token = bin2hex(random_bytes(32));
        $expiracion = date('Y-m-d H:i:s', strtotime('+30 days'));

        $clientModel->update($idCliente, [
            'token_firma_alturas'     => $token,
            'token_firma_alturas_exp' => $expiracion,
        ]);

        $urlFirma = base_url("protocolo-alturas/firmar/{$token}");
        $nombreCliente = $cliente['nombre_cliente'];
        $nombreRepLegal = $cliente['nombre_rep_legal'] ?? 'Representante Legal';

        // Email HTML
        $html = self::buildEmailHtml($nombreCliente, $nombreRepLegal, $urlFirma);

        $email = new \SendGrid\Mail\Mail();
        $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "Cycloid Talent");
        $email->setSubject("Actualización SG-SST: Protocolo obligatorio de notificación de trabajos en alturas");
        $email->addTo($cliente['correo_cliente'], $nombreCliente);

        // BCC a Edison para seguimiento
        $email->addBcc('edison.cuervo@cycloidtalent.com', 'Edison Cuervo');

        $email->addContent("text/html", $html);

        // Desactivar click tracking para evitar reescritura de links por SendGrid
        $trackingSettings = new \SendGrid\Mail\TrackingSettings();
        $clickTracking = new \SendGrid\Mail\ClickTracking();
        $clickTracking->setEnable(false);
        $clickTracking->setEnableText(false);
        $trackingSettings->setClickTracking($clickTracking);
        $email->setTrackingSettings($trackingSettings);

        try {
            $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
            $response = $sendgrid->send($email);
            if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                return ['success' => true, 'message' => 'Email enviado a ' . $cliente['correo_cliente']];
            }
            return ['success' => false, 'error' => 'SendGrid status ' . $response->statusCode()];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Copia la firma del protocolo alturas a los contratos del cliente que no tengan firma
     */
    private function propagarFirmaAContratos(int $idCliente, string $firmaPath): void
    {
        $contractModel = new \App\Models\ContractModel();
        $contratos = $contractModel->where('id_cliente', $idCliente)
            ->groupStart()
                ->where('firma_cliente_imagen IS NULL')
                ->orWhere('firma_cliente_imagen', '')
            ->groupEnd()
            ->findAll();

        foreach ($contratos as $contrato) {
            $contractModel->update($contrato['id_contrato'], [
                'firma_cliente_imagen' => $firmaPath,
                'firma_cliente_fecha'  => date('Y-m-d H:i:s'),
            ]);
            log_message('info', "Firma propagada a contrato #{$contrato['id_contrato']} del cliente #{$idCliente}");
        }
    }

    /**
     * Propaga firmas existentes (protocolo alturas ya firmado) a contratos sin firma.
     * Uso: FirmaAlturasController::propagarFirmasExistentes()
     */
    public static function propagarFirmasExistentes(): array
    {
        $clientModel = new ClientModel();
        $contractModel = new \App\Models\ContractModel();

        $firmados = $clientModel
            ->where('protocolo_alturas_firmado', 1)
            ->where('firma_representante_legal IS NOT NULL')
            ->where('firma_representante_legal !=', '')
            ->findAll();

        $resultados = [];
        foreach ($firmados as $cliente) {
            $contratos = $contractModel->where('id_cliente', $cliente['id_cliente'])
                ->groupStart()
                    ->where('firma_cliente_imagen IS NULL')
                    ->orWhere('firma_cliente_imagen', '')
                ->groupEnd()
                ->findAll();

            foreach ($contratos as $contrato) {
                $contractModel->update($contrato['id_contrato'], [
                    'firma_cliente_imagen' => $cliente['firma_representante_legal'],
                    'firma_cliente_fecha'  => $cliente['firma_alturas_fecha'],
                ]);
                $resultados[] = [
                    'cliente'  => $cliente['nombre_cliente'],
                    'contrato' => $contrato['numero_contrato'] ?? $contrato['id_contrato'],
                    'firma'    => $cliente['firma_representante_legal'],
                ];
            }
        }

        return $resultados;
    }

    private static function buildEmailHtml(string $nombreCliente, string $nombreRepLegal, string $urlFirma): string
    {
        return '
        <div style="font-family: Arial, sans-serif; max-width: 650px; margin: 0 auto; background: #f8f9fa; padding: 20px;">
            <div style="background: linear-gradient(135deg, #2c3e50 0%, #1a252f 100%); color: white; padding: 25px; border-radius: 10px 10px 0 0; text-align: center;">
                <h2 style="margin: 0; font-size: 18px;">Cycloid Talent SAS</h2>
                <p style="margin: 5px 0 0; opacity: 0.8; font-size: 13px;">Gestión Integral SG-SST para Propiedad Horizontal</p>
            </div>
            <div style="background: white; padding: 25px; border-radius: 0 0 10px 10px; border: 1px solid #e0e0e0;">
                <p>Estimado/a Administrador/a,</p>
                <p><strong>' . htmlspecialchars($nombreCliente) . '</strong></p>

                <p style="text-align: justify;">Como responsables de la gestión del Sistema de Gestión de Seguridad y Salud en el Trabajo (SG-SST) de su copropiedad, nos permitimos informarle sobre la implementación de un protocolo de obligatorio cumplimiento relacionado con <strong>trabajos en alturas</strong> realizados en las instalaciones del conjunto.</p>

                <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 15px; margin: 15px 0;">
                    <h4 style="margin: 0 0 8px; color: #856404;">¿Por qué es importante?</h4>
                    <p style="margin: 0; font-size: 14px; color: #856404; text-align: justify;">La <strong>Resolución 4272 de 2021</strong> del Ministerio de Trabajo establece que TODO trabajo realizado a <strong>1.50 metros o más</strong> sobre el nivel del piso debe cumplir con: personal con curso vigente de trabajo en alturas, afiliación activa a EPS, ARL y pensión, permiso de trabajo documentado, y equipos de protección certificados.</p>
                </div>

                <div style="background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; padding: 15px; margin: 15px 0;">
                    <h4 style="margin: 0 0 8px; color: #721c24;">¿Cuál es el riesgo?</h4>
                    <p style="margin: 0; font-size: 14px; color: #721c24; text-align: justify;">Si la copropiedad autoriza o permite trabajos en alturas con personal que <strong>no cuenta con curso de alturas, EPS, ARL o seguridad social</strong>, y ocurre un accidente grave o mortal:</p>
                    <ul style="font-size: 14px; color: #721c24; margin: 8px 0 0;">
                        <li>El administrador como representante legal responde civil y penalmente</li>
                        <li>La ARL no cubre el accidente si el trabajador no está afiliado</li>
                        <li>La copropiedad asume costos médicos, indemnizaciones y sanciones</li>
                        <li><strong>Cycloid Talent SAS</strong> no asume responsabilidad por trabajos no notificados</li>
                    </ul>
                </div>

                <div style="background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; padding: 15px; margin: 15px 0;">
                    <h4 style="margin: 0 0 8px; color: #155724;">¿Qué debe hacer?</h4>
                    <p style="margin: 0; font-size: 14px; color: #155724; text-align: justify;"><strong>Antes de autorizar cualquier trabajo en alturas</strong>, notifique formalmente a su consultor SST asignado para verificar que el contratista cumpla con todos los requisitos legales.</p>
                </div>

                <p style="text-align: justify;">Para formalizar la adopción de este protocolo en su copropiedad, requerimos su firma digital como representante legal:</p>

                <div style="text-align: center; margin: 25px 0;">
                    <a href="' . $urlFirma . '" style="display: inline-block; padding: 15px 40px; background: linear-gradient(135deg, #28a745, #218838); color: white; text-decoration: none; border-radius: 25px; font-size: 16px; font-weight: bold; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">Firmar protocolo de trabajo en alturas</a>
                </div>

                <p style="font-size: 12px; color: #6c757d; text-align: center;">Este documento queda como soporte de que usted fue informado sobre las obligaciones legales y el procedimiento de notificación vigente.</p>

                <hr style="border: none; border-top: 1px solid #e0e0e0; margin: 20px 0;">
                <p style="font-size: 12px; color: #6c757d; text-align: center;">
                    <strong>Cycloid Talent SAS</strong> — Gestión Integral SG-SST para Propiedad Horizontal<br>
                    notificacion.cycloidtalent@cycloidtalent.com
                </p>
            </div>
        </div>';
    }
}
