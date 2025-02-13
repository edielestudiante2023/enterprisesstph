<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\VencimientosMantenimientoModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\MantenimientoModel;
use SendGrid\Mail\Mail;

class VencimientosMantenimientoController extends BaseController
{
    // ... Otras funciones (add, edit, delete, list) sin cambios ...

    /**
     * Enviar correos electrónicos para vencimientos próximos (envío manual).
     */
    public function sendEmailsForUpcomingVencimientos()
    {
        $vencimientosModel    = new VencimientosMantenimientoModel();
        $clientModel          = new ClientModel();
        $consultantModel      = new ConsultantModel();
        $mantenimientoModel   = new MantenimientoModel();
    
        // Obtener vencimientos próximos (la función getUpcomingVencimientos() debe traer todos los registros)
        $vencimientos = $vencimientosModel->getUpcomingVencimientos();
    
        log_message('debug', 'Intentando obtener vencimientos entre ' . date('Y-m-d') . ' y ' . date('Y-m-d', strtotime('+30 days')));
        log_message('debug', 'Vencimientos encontrados: ' . print_r($vencimientos, true));
    
        if (empty($vencimientos)) {
            log_message('error', '❌ No hay vencimientos próximos para enviar correos.');
            return redirect()->to(base_url('vencimientos'))->with('msg', 'No hay vencimientos próximos para enviar.');
        }
    
        foreach ($vencimientos as $vencimiento) {
            // Normalizamos las fechas (establecemos la hora a 00:00:00)
            $fechaVencimiento = new \DateTime($vencimiento['fecha_vencimiento']);
            $fechaVencimiento->setTime(0, 0, 0);
            $hoy = new \DateTime();
            $hoy->setTime(0, 0, 0);
    
            // Si la fecha de vencimiento es anterior o igual a hoy o la diferencia es menor a 30 días, se omite
            if ($fechaVencimiento <= $hoy) {
                continue;
            }
            $interval = $hoy->diff($fechaVencimiento);
            if ($interval->days < 30) {
                continue;
            }
    
            $cliente = $clientModel->find($vencimiento['id_cliente']);
            $consultor = $consultantModel->find($vencimiento['id_consultor']);
            $mantenimiento = $mantenimientoModel->find($vencimiento['id_mantenimiento']);
    
            // Validar que cliente y consultor existan y tengan correos válidos
            if (!$cliente || !$consultor) {
                log_message('error', "⚠️ Error: Cliente o consultor no encontrados para vencimiento ID: {$vencimiento['id_vencimientos_mmttos']}");
                continue;
            }
            if (empty($cliente['correo_cliente']) || !filter_var($cliente['correo_cliente'], FILTER_VALIDATE_EMAIL)) {
                log_message('error', "⚠️ Correo del cliente no válido o vacío: " . ($cliente['correo_cliente'] ?? 'No definido'));
                continue;
            }
            if (empty($consultor['correo_consultor']) || !filter_var($consultor['correo_consultor'], FILTER_VALIDATE_EMAIL)) {
                log_message('error', "⚠️ Correo del consultor no válido o vacío: " . ($consultor['correo_consultor'] ?? 'No definido'));
                continue;
            }
    
            // Enviar el correo
            $destinatarios = array_unique([$cliente['correo_cliente'], $consultor['correo_consultor']]);
            log_message('debug', '📧 Destinatarios del correo: ' . implode(', ', $destinatarios));
    
            $email = new \SendGrid\Mail\Mail();
            $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "Cycloid Talent");
            $email->setSubject("🔔 Recordatorio de Vencimiento");
    
            foreach ($destinatarios as $correo) {
                $email->addTo($correo);
            }
    
            $email->addContent(
                "text/html",
                "<p>🔔 <strong>Estimado/a {$cliente['nombre_cliente']}</strong>,</p>
                 <p>El mantenimiento <strong>{$mantenimiento['detalle_mantenimiento']}</strong> tiene su fecha de vencimiento programada para el <strong>{$vencimiento['fecha_vencimiento']}</strong>.</p>
                 <p>Por favor, tome las medidas necesarias para realizar el mantenimiento con la anticipación requerida.</p>
                 <p>Saludos cordiales,</p>
                 <p><strong>Cycloid Talent</strong></p>"
            );
    
            $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
            try {
                $response = $sendgrid->send($email);
                log_message('info', "✅ Correo enviado con código: " . $response->statusCode());
            } catch (\Exception $e) {
                log_message('error', "❌ Error al enviar correo: " . $e->getMessage());
            }
        }
    
        return redirect()->to(base_url('vencimientos'))->with('msg', '📩 Correos enviados correctamente.');
    }
    
    /**
     * Enviar correos electrónicos a los registros seleccionados (nueva función).
     */
    public function sendSelectedEmails()
    {
        // Recoger los IDs enviados (arreglo de IDs)
        $selectedIds = $this->request->getPost('selected');
    
        if (empty($selectedIds)) {
            return redirect()->to(base_url('vencimientos'))->with('msg', 'No se seleccionaron vencimientos.');
        }
    
        $vencimientosModel    = new VencimientosMantenimientoModel();
        $clientModel          = new ClientModel();
        $consultantModel      = new ConsultantModel();
        $mantenimientoModel   = new MantenimientoModel();
    
        foreach ($selectedIds as $id) {
            $vencimiento = $vencimientosModel->find($id);
            if (!$vencimiento) {
                continue;
            }
    
            // Normalizamos las fechas
            $fechaVencimiento = new \DateTime($vencimiento['fecha_vencimiento']);
            $fechaVencimiento->setTime(0, 0, 0);
            $hoy = new \DateTime();
            $hoy->setTime(0, 0, 0);
    
            if ($fechaVencimiento <= $hoy) {
                continue;
            }
            $interval = $hoy->diff($fechaVencimiento);
            if ($interval->days < 30) {
                continue;
            }
    
            $cliente = $clientModel->find($vencimiento['id_cliente']);
            $consultor = $consultantModel->find($vencimiento['id_consultor']);
            $mantenimiento = $mantenimientoModel->find($vencimiento['id_mantenimiento']);
    
            if (!$cliente || !$consultor) {
                continue;
            }
            if (empty($cliente['correo_cliente']) || !filter_var($cliente['correo_cliente'], FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            if (empty($consultor['correo_consultor']) || !filter_var($consultor['correo_consultor'], FILTER_VALIDATE_EMAIL)) {
                continue;
            }
    
            $emailContent = "
                <p>Hola <strong>{$cliente['nombre_cliente']}</strong>,</p>
                <p>El mantenimiento <strong>{$mantenimiento['detalle_mantenimiento']}</strong> tiene su fecha de vencimiento programada para el <strong>{$vencimiento['fecha_vencimiento']}</strong>.</p>
                <p>Por favor, verifique los detalles y tome las medidas necesarias.</p>
            ";
    
            $this->sendEmail(
                $cliente['correo_cliente'],
                $consultor['correo_consultor'],
                'Recordatorio de Vencimiento de Mantenimiento',
                $emailContent
            );
        }
    
        return redirect()->to(base_url('vencimientos'))->with('msg', 'Correos enviados a los registros seleccionados.');
    }
    
    /**
     * Enviar correos electrónicos automáticamente (por ejemplo, vía cron).
     */
    public function sendEmailsAutomatically()
    {
        $vencimientosModel    = new VencimientosMantenimientoModel();
        $clientModel          = new ClientModel();
        $consultantModel      = new ConsultantModel();
        $mantenimientoModel   = new MantenimientoModel();
    
        // Obtener vencimientos próximos
        $vencimientos = $vencimientosModel->getUpcomingVencimientos();
    
        if (empty($vencimientos)) {
            log_message('info', 'No hay vencimientos próximos para enviar correos.');
            return;
        }
    
        foreach ($vencimientos as $vencimiento) {
            // Normalizamos las fechas
            $fechaVencimiento = new \DateTime($vencimiento['fecha_vencimiento']);
            $fechaVencimiento->setTime(0, 0, 0);
            $hoy = new \DateTime();
            $hoy->setTime(0, 0, 0);
    
            if ($fechaVencimiento <= $hoy) {
                continue;
            }
            $interval = $hoy->diff($fechaVencimiento);
            if ($interval->days < 30) {
                continue;
            }
    
            $cliente = $clientModel->find($vencimiento['id_cliente']);
            $consultor = $consultantModel->find($vencimiento['id_consultor']);
    
            if (empty($cliente['correo_cliente']) || empty($consultor['correo_consultor'])) {
                log_message('error', "Faltan correos electrónicos para el vencimiento ID: {$vencimiento['id_vencimientos_mmttos']}");
                continue;
            }
    
            $mantenimiento = $mantenimientoModel->find($vencimiento['id_mantenimiento']);
            $tituloMantenimiento = $mantenimiento ? $mantenimiento['detalle_mantenimiento'] : 'Mantenimiento no especificado';
    
            $emailContent = "
                <h3>Recordatorio de Vencimiento</h3>
                <p>Estimado/a <strong>{$cliente['nombre_cliente']}</strong> y Consultor <strong>{$consultor['nombre_consultor']}</strong>,</p>
                <p>El mantenimiento <strong>{$tituloMantenimiento}</strong> está próximo a vencer el día <strong>{$vencimiento['fecha_vencimiento']}</strong>.</p>
                <p>Por favor, tomen las acciones necesarias para su ejecución antes de la fecha de vencimiento.</p>
                <p>Saludos,</p>
                <p><strong>Cycloid Talent</strong></p>
            ";
    
            $this->sendEmail(
                $cliente['correo_cliente'],
                $consultor['correo_consultor'],
                'Recordatorio de Vencimiento de Mantenimiento',
                $emailContent
            );
        }
    
        log_message('info', 'Correos electrónicos de recordatorio enviados automáticamente.');
    }
    
    /**
     * Función auxiliar para enviar correos electrónicos utilizando SendGrid.
     *
     * @param string $clientEmail     Correo electrónico del cliente.
     * @param string $consultantEmail Correo electrónico del consultor.
     * @param string $subject         Asunto del correo.
     * @param string $content         Contenido HTML del correo.
     */
    private function sendEmail($clientEmail, $consultantEmail, $subject, $content)
    {
        $email = new Mail();
        $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "Cycloid Talent");
        $email->setSubject($subject);
        $email->addTo($clientEmail);
        $email->addTo($consultantEmail);
        $email->addContent("text/html", $content);

        $sendgridApiKey = getenv('SENDGRID_API_KEY');

        if (!$sendgridApiKey) {
            log_message('error', 'Clave API de SendGrid no configurada.');
            return;
        }

        $sendgrid = new \SendGrid($sendgridApiKey);

        try {
            $response = $sendgrid->send($email);
            log_message('debug', 'Correo enviado con éxito. Status Code: ' . $response->statusCode());
        } catch (\Exception $e) {
            log_message('error', 'Error al enviar el correo: ' . $e->getMessage());
        }
    }
    
    /**
     * Enviar correo de prueba para un vencimiento específico.
     */
    public function testEmailForVencimiento($id)
    {
        $vencimientosModel    = new VencimientosMantenimientoModel();
        $clientModel          = new ClientModel();
        $consultantModel      = new ConsultantModel();
        $mantenimientoModel   = new MantenimientoModel();

        $vencimiento = $vencimientosModel->find($id);
        if (!$vencimiento) {
            return 'Vencimiento no encontrado.';
        }

        $cliente = $clientModel->find($vencimiento['id_cliente']);
        $consultor = $consultantModel->find($vencimiento['id_consultor']);
        $mantenimiento = $mantenimientoModel->find($vencimiento['id_mantenimiento']);

        $email = new \SendGrid\Mail\Mail();
        $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "Cycloid Talent");
        $email->setSubject("Recordatorio de Vencimiento");
        $email->addTo($cliente['correo_cliente']);
        $email->addTo($consultor['correo_consultor']);
        $email->addContent(
            "text/html",
            "<p>El mantenimiento <strong>{$mantenimiento['detalle_mantenimiento']}</strong> está próximo a vencer el <strong>{$vencimiento['fecha_vencimiento']}</strong>.</p>"
        );

        $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
        try {
            $response = $sendgrid->send($email);
            log_message('info', 'SendGrid Response: ' . $response->body());
            return "Correo enviado. Código de estado: " . $response->statusCode() . "<br>Respuesta de SendGrid: " . $response->body();
        } catch (\Exception $e) {
            log_message('error', 'Error al enviar correo de prueba: ' . $e->getMessage());
            return "Error al enviar correo: " . $e->getMessage();
        }
    }
}
