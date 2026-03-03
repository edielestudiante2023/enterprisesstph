<?php

namespace App\Controllers;

use App\Models\PlanillaSegSocialModel;
use App\Models\ClientModel;
use SendGrid\Mail\Mail;

class PlanillaSegSocialController extends BaseController
{
    protected $planillaModel;
    protected $uploadPath;

    public function __construct()
    {
        $this->planillaModel = new PlanillaSegSocialModel();
        $this->uploadPath = ROOTPATH . 'public/uploads/planillas-seguridad-social';

        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0777, true);
        }
    }

    /**
     * Lista todas las planillas
     */
    public function index()
    {
        $planillas = $this->planillaModel->orderBy('mes_aportes', 'DESC')->findAll();
        return view('consultant/planillas_seg_social/index', [
            'planillas' => $planillas,
        ]);
    }

    /**
     * Formulario para crear nueva planilla
     */
    public function create()
    {
        return view('consultant/planillas_seg_social/form', [
            'planilla' => null,
        ]);
    }

    /**
     * Guardar nueva planilla
     */
    public function store()
    {
        $archivo = $this->request->getFile('archivo_pdf');

        if (!$archivo || !$archivo->isValid() || $archivo->hasMoved()) {
            return redirect()->back()->with('error', 'Debe seleccionar un archivo PDF válido.');
        }

        if ($archivo->getClientMimeType() !== 'application/pdf') {
            return redirect()->back()->with('error', 'El archivo debe ser un PDF.');
        }

        $nombreArchivo = $archivo->getRandomName();
        $archivo->move($this->uploadPath, $nombreArchivo);

        $this->planillaModel->insert([
            'mes_aportes'  => $this->request->getPost('mes_aportes'),
            'archivo_pdf'  => $nombreArchivo,
            'fecha_cargue' => date('Y-m-d H:i:s'),
            'notas'        => $this->request->getPost('notas'),
        ]);

        return redirect()->to(base_url('planillas-seguridad-social'))->with('success', 'Planilla cargada exitosamente.');
    }

    /**
     * Formulario para editar planilla
     */
    public function edit($id)
    {
        $planilla = $this->planillaModel->find($id);
        if (!$planilla) {
            return redirect()->to(base_url('planillas-seguridad-social'))->with('error', 'Planilla no encontrada.');
        }

        return view('consultant/planillas_seg_social/form', [
            'planilla' => $planilla,
        ]);
    }

    /**
     * Actualizar planilla existente
     */
    public function update($id)
    {
        $planilla = $this->planillaModel->find($id);
        if (!$planilla) {
            return redirect()->to(base_url('planillas-seguridad-social'))->with('error', 'Planilla no encontrada.');
        }

        $data = [
            'mes_aportes' => $this->request->getPost('mes_aportes'),
            'notas'       => $this->request->getPost('notas'),
        ];

        // Si se sube un nuevo PDF, reemplazar el anterior
        $archivo = $this->request->getFile('archivo_pdf');
        if ($archivo && $archivo->isValid() && !$archivo->hasMoved()) {
            if ($archivo->getClientMimeType() !== 'application/pdf') {
                return redirect()->back()->with('error', 'El archivo debe ser un PDF.');
            }

            // Eliminar archivo anterior
            $archivoAnterior = $this->uploadPath . '/' . $planilla['archivo_pdf'];
            if (file_exists($archivoAnterior)) {
                unlink($archivoAnterior);
            }

            $nombreArchivo = $archivo->getRandomName();
            $archivo->move($this->uploadPath, $nombreArchivo);
            $data['archivo_pdf'] = $nombreArchivo;
        }

        $this->planillaModel->update($id, $data);

        return redirect()->to(base_url('planillas-seguridad-social'))->with('success', 'Planilla actualizada exitosamente.');
    }

    /**
     * Eliminar planilla
     */
    public function delete($id)
    {
        $planilla = $this->planillaModel->find($id);
        if (!$planilla) {
            return redirect()->to(base_url('planillas-seguridad-social'))->with('error', 'Planilla no encontrada.');
        }

        // Eliminar archivo físico
        $archivoPath = $this->uploadPath . '/' . $planilla['archivo_pdf'];
        if (file_exists($archivoPath)) {
            unlink($archivoPath);
        }

        $this->planillaModel->delete($id);

        return redirect()->to(base_url('planillas-seguridad-social'))->with('success', 'Planilla eliminada exitosamente.');
    }

    /**
     * Descargar PDF de la planilla
     */
    public function download($id)
    {
        $planilla = $this->planillaModel->find($id);
        if (!$planilla) {
            return redirect()->to(base_url('planillas-seguridad-social'))->with('error', 'Planilla no encontrada.');
        }

        $filePath = $this->uploadPath . '/' . $planilla['archivo_pdf'];
        if (!file_exists($filePath)) {
            return redirect()->to(base_url('planillas-seguridad-social'))->with('error', 'El archivo PDF no existe en el servidor.');
        }

        $mesFormateado = $this->formatearMes($planilla['mes_aportes']);
        $downloadName = 'Planilla_Seguridad_Social_' . str_replace(' ', '_', $mesFormateado) . '.pdf';

        return $this->response->download($filePath, null)->setFileName($downloadName);
    }

    /**
     * Enviar planilla por email a todos los clientes activos
     */
    public function enviar($id)
    {
        $planilla = $this->planillaModel->find($id);
        if (!$planilla) {
            return $this->response->setJSON(['success' => false, 'message' => 'Planilla no encontrada.']);
        }

        $filePath = $this->uploadPath . '/' . $planilla['archivo_pdf'];
        if (!file_exists($filePath)) {
            return $this->response->setJSON(['success' => false, 'message' => 'El archivo PDF no existe en el servidor.']);
        }

        // Obtener clientes activos con email válido
        $clientModel = new ClientModel();
        $clientesActivos = $clientModel->where('estado', 'activo')->findAll();

        $destinatarios = [];
        foreach ($clientesActivos as $cliente) {
            if (!empty($cliente['correo_cliente']) && filter_var($cliente['correo_cliente'], FILTER_VALIDATE_EMAIL)) {
                $destinatarios[] = $cliente;
            }
        }

        if (empty($destinatarios)) {
            return $this->response->setJSON(['success' => false, 'message' => 'No hay clientes activos con email válido.']);
        }

        // Preparar archivo adjunto (base64)
        $pdfBase64 = base64_encode(file_get_contents($filePath));
        $mesFormateado = $this->formatearMes($planilla['mes_aportes']);
        $pdfFilename = 'Planilla_Seguridad_Social_' . str_replace(' ', '_', $mesFormateado) . '.pdf';

        $sendgridApiKey = getenv('SENDGRID_API_KEY');
        if (!$sendgridApiKey) {
            return $this->response->setJSON(['success' => false, 'message' => 'Clave API de SendGrid no configurada.']);
        }

        require_once ROOTPATH . 'vendor/autoload.php';

        $enviosExitosos = 0;
        $errores = [];

        foreach ($destinatarios as $cliente) {
            try {
                $email = new Mail();
                $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "Cycloid Talent - Enterprise SST");
                $email->setSubject("Planilla de Seguridad Social - " . $mesFormateado);
                $email->addTo($cliente['correo_cliente'], $cliente['nombre_cliente']);
                $email->addContent("text/html", $this->buildEmailTemplate($cliente, $mesFormateado));
                $email->addAttachment($pdfBase64, "application/pdf", $pdfFilename, "attachment");

                $sendgrid = new \SendGrid($sendgridApiKey);
                $response = $sendgrid->send($email);

                if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                    $enviosExitosos++;
                    log_message('info', "PlanillaSegSocial: Email enviado a {$cliente['correo_cliente']} ({$cliente['nombre_cliente']})");
                } else {
                    $errores[] = $cliente['nombre_cliente'];
                    log_message('error', "PlanillaSegSocial: Error enviando a {$cliente['correo_cliente']}. Status: {$response->statusCode()}");
                }
            } catch (\Exception $e) {
                $errores[] = $cliente['nombre_cliente'];
                log_message('error', "PlanillaSegSocial: Excepción enviando a {$cliente['correo_cliente']}: " . $e->getMessage());
            }
        }

        // Actualizar registro
        $this->planillaModel->update($id, [
            'cantidad_envios' => $enviosExitosos,
            'fecha_envio'     => date('Y-m-d H:i:s'),
            'estado_envio'    => 'enviado',
        ]);

        $totalDestinatarios = count($destinatarios);
        $message = "Planilla enviada a {$enviosExitosos} de {$totalDestinatarios} clientes activos.";
        if (!empty($errores)) {
            $message .= " Falló el envío a: " . implode(', ', $errores);
        }

        return $this->response->setJSON([
            'success'  => $enviosExitosos > 0,
            'message'  => $message,
            'enviados' => $enviosExitosos,
            'total'    => $totalDestinatarios,
            'errores'  => $errores,
        ]);
    }

    /**
     * Construir template HTML del email
     */
    private function buildEmailTemplate($cliente, $mesFormateado)
    {
        $nombreCliente = esc($cliente['nombre_cliente']);

        return '<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"></head>
<body style="margin:0; padding:0; background-color:#f4f4f7; font-family:Segoe UI,Tahoma,Geneva,Verdana,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f7; padding:20px 0;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,0.1);">

    <!-- Header -->
    <tr>
        <td style="background:linear-gradient(135deg,#1a3a5c,#2c5f8a); padding:30px 40px; text-align:center;">
            <h1 style="color:#d4a843; margin:0; font-size:22px; font-weight:700;">Enterprise SST</h1>
            <p style="color:#ffffff; margin:8px 0 0; font-size:13px; opacity:0.9;">Cycloid Talent - Sistemas que Evolucionan</p>
        </td>
    </tr>

    <!-- Body -->
    <tr>
        <td style="padding:35px 40px;">
            <h2 style="color:#1a3a5c; margin:0 0 20px; font-size:18px;">Planilla de Seguridad Social</h2>
            <p style="color:#333; font-size:15px; line-height:1.6; margin:0 0 15px;">
                Estimado/a <strong>' . $nombreCliente . '</strong>,
            </p>
            <p style="color:#333; font-size:15px; line-height:1.6; margin:0 0 15px;">
                Adjuntamos la planilla de pago de seguridad social correspondiente al período de <strong>' . $mesFormateado . '</strong>.
            </p>
            <p style="color:#333; font-size:15px; line-height:1.6; margin:0 0 15px;">
                Este documento certifica el cumplimiento de nuestras obligaciones en materia de seguridad social para el período mencionado.
            </p>

            <!-- Info box -->
            <table width="100%" cellpadding="0" cellspacing="0" style="margin:25px 0;">
            <tr>
                <td style="background-color:#f0f5fa; border-left:4px solid #2c5f8a; padding:15px 20px; border-radius:0 6px 6px 0;">
                    <p style="margin:0; color:#1a3a5c; font-size:14px;">
                        <strong>Período:</strong> ' . $mesFormateado . '<br>
                        <strong>Documento:</strong> Planilla de Seguridad Social (PDF adjunto)
                    </p>
                </td>
            </tr>
            </table>

            <p style="color:#666; font-size:14px; line-height:1.6; margin:0;">
                Si tiene alguna inquietud, no dude en contactarnos.
            </p>
        </td>
    </tr>

    <!-- Footer -->
    <tr>
        <td style="background-color:#f8f9fa; padding:20px 40px; border-top:1px solid #e9ecef;">
            <p style="margin:0; color:#999; font-size:12px; text-align:center;">
                Este correo fue enviado automáticamente por <strong>Enterprise SST - Cycloid Talent</strong>.<br>
                &copy; ' . date('Y') . ' Cycloid Talent. Todos los derechos reservados.
            </p>
        </td>
    </tr>

</table>
</td></tr>
</table>
</body>
</html>';
    }

    /**
     * Formatear mes_aportes (2026-03) a texto legible (Marzo 2026)
     */
    private function formatearMes($mesAportes)
    {
        $meses = [
            '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo',
            '04' => 'Abril', '05' => 'Mayo', '06' => 'Junio',
            '07' => 'Julio', '08' => 'Agosto', '09' => 'Septiembre',
            '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre',
        ];

        $partes = explode('-', $mesAportes);
        if (count($partes) === 2) {
            $nombreMes = $meses[$partes[1]] ?? $partes[1];
            return $nombreMes . ' ' . $partes[0];
        }

        return $mesAportes;
    }
}
