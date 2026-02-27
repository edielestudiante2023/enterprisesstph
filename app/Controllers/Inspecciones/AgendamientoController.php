<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\AgendamientoModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;

class AgendamientoController extends BaseController
{
    protected AgendamientoModel $model;

    public function __construct()
    {
        $this->model = new AgendamientoModel();
    }

    /**
     * Listado de agendamientos del consultor (o todos si admin)
     */
    public function list()
    {
        $userId = session()->get('user_id');
        $role   = session()->get('role');

        if ($role === 'admin') {
            $agendamientos = $this->model->getAll();
        } else {
            $agendamientos = $this->model->getByConsultor($userId);
        }

        // Agregar última visita a cada agendamiento
        foreach ($agendamientos as &$ag) {
            $ultima = $this->model->getUltimaVisita($ag['id_cliente']);
            $ag['ultima_visita'] = $ultima ? $ultima['fecha_visita'] : null;
        }

        // Clientes activos del consultor para el filtro
        $clientModel = new ClientModel();
        if ($role === 'admin') {
            $clientes = $clientModel->where('estado', 'activo')->orderBy('nombre_cliente')->findAll();
        } else {
            $clientes = $clientModel->where('id_consultor', $userId)->where('estado', 'activo')->orderBy('nombre_cliente')->findAll();
        }

        $data = [
            'title'          => 'Agendamientos',
            'agendamientos'  => $agendamientos,
            'clientes'       => $clientes,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/agendamiento/list', $data),
            'title'   => 'Agendamientos',
        ]);
    }

    /**
     * Formulario de nuevo agendamiento
     */
    public function create()
    {
        $userId = session()->get('user_id');
        $role   = session()->get('role');

        $clientModel = new ClientModel();
        if ($role === 'admin') {
            $clientes = $clientModel->where('estado', 'activo')->orderBy('nombre_cliente')->findAll();
        } else {
            $clientes = $clientModel->where('id_consultor', $userId)->where('estado', 'activo')->orderBy('nombre_cliente')->findAll();
        }

        $data = [
            'title'        => 'Nuevo Agendamiento',
            'agendamiento' => null,
            'clientes'     => $clientes,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/agendamiento/form', $data),
            'title'   => 'Nuevo Agendamiento',
        ]);
    }

    /**
     * Guardar nuevo agendamiento
     */
    public function store()
    {
        $userId = session()->get('user_id');

        $rules = [
            'id_cliente'   => 'required|integer',
            'fecha_visita' => 'required|valid_date',
            'hora_visita'  => 'required',
            'frecuencia'   => 'required|in_list[mensual,bimensual,trimestral]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id_cliente'          => $this->request->getPost('id_cliente'),
            'id_consultor'        => $userId,
            'fecha_visita'        => $this->request->getPost('fecha_visita'),
            'hora_visita'         => $this->request->getPost('hora_visita'),
            'frecuencia'          => $this->request->getPost('frecuencia'),
            'estado'              => 'pendiente',
            'preparacion_cliente' => $this->request->getPost('preparacion_cliente') ?: null,
            'observaciones'       => $this->request->getPost('observaciones') ?: null,
        ];

        $this->model->insert($data);
        $id = $this->model->getInsertID();

        // Enviar invitación si se marcó el checkbox
        if ($this->request->getPost('enviar_invitacion')) {
            $this->enviarInvitacion($id);
        }

        return redirect()->to('/inspecciones/agendamiento')
            ->with('msg', 'Agendamiento creado correctamente');
    }

    /**
     * Formulario de edición
     */
    public function edit($id)
    {
        $agendamiento = $this->model->find($id);
        if (!$agendamiento) {
            return redirect()->to('/inspecciones/agendamiento')->with('error', 'Agendamiento no encontrado');
        }

        $userId = session()->get('user_id');
        $role   = session()->get('role');

        // Solo el consultor dueño o admin pueden editar
        if ($role !== 'admin' && (int)$agendamiento['id_consultor'] !== (int)$userId) {
            return redirect()->to('/inspecciones/agendamiento')->with('error', 'No autorizado');
        }

        $clientModel = new ClientModel();
        if ($role === 'admin') {
            $clientes = $clientModel->where('estado', 'activo')->orderBy('nombre_cliente')->findAll();
        } else {
            $clientes = $clientModel->where('id_consultor', $userId)->where('estado', 'activo')->orderBy('nombre_cliente')->findAll();
        }

        $data = [
            'title'        => 'Editar Agendamiento',
            'agendamiento' => $agendamiento,
            'clientes'     => $clientes,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/agendamiento/form', $data),
            'title'   => 'Editar Agendamiento',
        ]);
    }

    /**
     * Actualizar agendamiento
     */
    public function update($id)
    {
        $agendamiento = $this->model->find($id);
        if (!$agendamiento) {
            return redirect()->to('/inspecciones/agendamiento')->with('error', 'Agendamiento no encontrado');
        }

        $rules = [
            'fecha_visita' => 'required|valid_date',
            'hora_visita'  => 'required',
            'frecuencia'   => 'required|in_list[mensual,bimensual,trimestral]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'fecha_visita'        => $this->request->getPost('fecha_visita'),
            'hora_visita'         => $this->request->getPost('hora_visita'),
            'frecuencia'          => $this->request->getPost('frecuencia'),
            'preparacion_cliente' => $this->request->getPost('preparacion_cliente') ?: null,
            'observaciones'       => $this->request->getPost('observaciones') ?: null,
        ];

        $this->model->update($id, $data);

        // Re-enviar invitación si se marcó
        if ($this->request->getPost('enviar_invitacion')) {
            $this->enviarInvitacion($id);
        }

        return redirect()->to('/inspecciones/agendamiento')
            ->with('msg', 'Agendamiento actualizado');
    }

    /**
     * Cancelar agendamiento
     */
    public function cancel($id)
    {
        $agendamiento = $this->model->find($id);
        if (!$agendamiento) {
            return $this->response->setJSON(['success' => false, 'error' => 'No encontrado']);
        }

        $this->model->update($id, ['estado' => 'cancelado']);

        // Si ya se envió invitación, enviar cancelación
        if ($agendamiento['email_enviado']) {
            $this->enviarCancelacion($id, $agendamiento);
        }

        return $this->response->setJSON(['success' => true, 'message' => 'Agendamiento cancelado']);
    }

    /**
     * Enviar invitación .ics via SendGrid (endpoint AJAX)
     */
    public function sendInvitation($id)
    {
        $result = $this->enviarInvitacion($id);
        return $this->response->setJSON($result);
    }

    /**
     * API: Info del cliente + última visita + fecha sugerida
     */
    public function apiClienteInfo($idCliente)
    {
        $clientModel = new ClientModel();
        $cliente = $clientModel->find($idCliente);

        if (!$cliente) {
            return $this->response->setJSON(['success' => false, 'error' => 'Cliente no encontrado']);
        }

        $ultimaVisita = $this->model->getUltimaVisita($idCliente);
        $fechaSugerida = $this->model->sugerirProximaFecha($idCliente, 'mensual');

        return $this->response->setJSON([
            'success'        => true,
            'nombre_cliente' => $cliente['nombre_cliente'],
            'correo_cliente' => $cliente['correo_cliente'],
            'direccion'      => $cliente['direccion_cliente'],
            'ciudad'         => $cliente['ciudad_cliente'],
            'telefono'       => $cliente['telefono_1_cliente'],
            'ultima_visita'  => $ultimaVisita ? $ultimaVisita['fecha_visita'] : null,
            'fecha_sugerida' => $fechaSugerida,
        ]);
    }

    // ─── MÉTODOS PRIVADOS ───

    /**
     * Genera archivo .ics para evento de calendario
     */
    private function generateIcs(array $agendamiento, array $cliente, array $consultor, string $method = 'REQUEST'): string
    {
        $uid = 'agendamiento-' . $agendamiento['id'] . '@cycloidtalent.com';

        // Fecha/hora en formato iCalendar (YYYYMMDDTHHMMSS)
        $dtStart = date('Ymd\THis', strtotime($agendamiento['fecha_visita'] . ' ' . $agendamiento['hora_visita']));
        $dtEnd   = date('Ymd\THis', strtotime($agendamiento['fecha_visita'] . ' ' . $agendamiento['hora_visita'] . ' +1 hour'));

        $summary     = 'Visita SST - ' . ($cliente['nombre_cliente'] ?? 'Cliente');
        $description = 'Visita del consultor ' . ($consultor['nombre_consultor'] ?? '') . ' al cliente ' . ($cliente['nombre_cliente'] ?? '');
        $location    = trim(($cliente['direccion_cliente'] ?? '') . ', ' . ($cliente['ciudad_cliente'] ?? ''), ', ');

        $status   = $method === 'CANCEL' ? 'CANCELLED' : 'CONFIRMED';
        $sequence = $method === 'CANCEL' ? '1' : '0';

        $correoConsultor = $consultor['correo_consultor'] ?? '';
        $correoCliente   = $cliente['correo_cliente'] ?? '';
        $nombreConsultor = $consultor['nombre_consultor'] ?? 'Consultor';
        $nombreCliente   = $cliente['nombre_cliente'] ?? 'Cliente';

        $ics  = "BEGIN:VCALENDAR\r\n";
        $ics .= "VERSION:2.0\r\n";
        $ics .= "PRODID:-//Cycloid Talent//SG-SST//ES\r\n";
        $ics .= "METHOD:{$method}\r\n";
        $ics .= "BEGIN:VEVENT\r\n";
        $ics .= "UID:{$uid}\r\n";
        $ics .= "DTSTART:{$dtStart}\r\n";
        $ics .= "DTEND:{$dtEnd}\r\n";
        $ics .= "SUMMARY:{$summary}\r\n";
        $ics .= "DESCRIPTION:{$description}\r\n";
        $ics .= "LOCATION:{$location}\r\n";
        $ics .= "ORGANIZER;CN=Cycloid Talent:mailto:notificacion.cycloidtalent@cycloidtalent.com\r\n";
        if ($correoConsultor) {
            $ics .= "ATTENDEE;CN={$nombreConsultor};RSVP=TRUE:mailto:{$correoConsultor}\r\n";
        }
        if ($correoCliente) {
            $ics .= "ATTENDEE;CN={$nombreCliente};RSVP=TRUE:mailto:{$correoCliente}\r\n";
        }
        $ics .= "STATUS:{$status}\r\n";
        $ics .= "SEQUENCE:{$sequence}\r\n";
        $ics .= "END:VEVENT\r\n";
        $ics .= "END:VCALENDAR\r\n";

        return $ics;
    }

    /**
     * Envía invitación de calendario via SendGrid
     */
    private function enviarInvitacion(int $id): array
    {
        $agendamiento = $this->model->find($id);
        if (!$agendamiento) {
            return ['success' => false, 'error' => 'Agendamiento no encontrado'];
        }

        $clientModel = new ClientModel();
        $cliente = $clientModel->find($agendamiento['id_cliente']);
        if (!$cliente) {
            return ['success' => false, 'error' => 'Cliente no encontrado'];
        }

        $consultantModel = new ConsultantModel();
        $consultor = $consultantModel->find($agendamiento['id_consultor']);
        if (!$consultor) {
            return ['success' => false, 'error' => 'Consultor no encontrado'];
        }

        $sendgridApiKey = getenv('SENDGRID_API_KEY');
        if (!$sendgridApiKey) {
            return ['success' => false, 'error' => 'SENDGRID_API_KEY no configurada'];
        }

        $nombreCliente   = $cliente['nombre_cliente'] ?? 'Cliente';
        $correoCliente   = $cliente['correo_cliente'] ?? '';
        $correoConsultor = $consultor['correo_consultor'] ?? '';
        $nombreConsultor = $consultor['nombre_consultor'] ?? 'Consultor';

        if (!$correoCliente && !$correoConsultor) {
            return ['success' => false, 'error' => 'No hay correos destinatarios configurados'];
        }

        $fechaFormateada = date('d/m/Y', strtotime($agendamiento['fecha_visita']));
        $horaFormateada  = date('g:i A', strtotime($agendamiento['hora_visita']));
        $direccion       = trim(($cliente['direccion_cliente'] ?? '') . ', ' . ($cliente['ciudad_cliente'] ?? ''), ', ');

        $subject = "Visita SST Agendada - {$nombreCliente} - {$fechaFormateada}";

        $htmlContent = "
        <div style='font-family: Segoe UI, Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: #1c2437; padding: 20px; text-align: center; border-radius: 10px 10px 0 0;'>
                <h1 style='color: #bd9751; margin: 0; font-size: 20px;'>VISITA SST AGENDADA</h1>
            </div>
            <div style='padding: 25px; background: #f8f9fa; border-radius: 0 0 10px 10px;'>
                <p>Se ha agendado una visita de Seguridad y Salud en el Trabajo:</p>
                <table style='width: 100%; border-collapse: collapse; margin: 15px 0;'>
                    <tr>
                        <td style='padding: 10px; background: #fff; border: 1px solid #ddd;'><strong>Cliente:</strong></td>
                        <td style='padding: 10px; background: #fff; border: 1px solid #ddd;'>{$nombreCliente}</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px; background: #fff; border: 1px solid #ddd;'><strong>Fecha:</strong></td>
                        <td style='padding: 10px; background: #fff; border: 1px solid #ddd;'>{$fechaFormateada}</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px; background: #fff; border: 1px solid #ddd;'><strong>Hora:</strong></td>
                        <td style='padding: 10px; background: #fff; border: 1px solid #ddd;'>{$horaFormateada}</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px; background: #fff; border: 1px solid #ddd;'><strong>Dirección:</strong></td>
                        <td style='padding: 10px; background: #fff; border: 1px solid #ddd;'>{$direccion}</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px; background: #fff; border: 1px solid #ddd;'><strong>Consultor:</strong></td>
                        <td style='padding: 10px; background: #fff; border: 1px solid #ddd;'>{$nombreConsultor}</td>
                    </tr>
                </table>";

        if (!empty($agendamiento['observaciones'])) {
            $obs = htmlspecialchars($agendamiento['observaciones']);
            $htmlContent .= "<p><strong>Observaciones:</strong> {$obs}</p>";
        }

        $htmlContent .= "
                <p style='color: #666; font-size: 12px; margin-top: 30px;'>
                    Abra el archivo adjunto (.ics) para agregar este evento a su calendario.
                </p>
                <p style='color: #999; font-size: 11px;'>Generado por SG-SST Cycloid Talent.</p>
            </div>
        </div>";

        // Generar .ics
        $icsContent = $this->generateIcs($agendamiento, $cliente, $consultor, 'REQUEST');

        // Enviar con SendGrid SDK
        require_once ROOTPATH . 'vendor/autoload.php';

        $email = new \SendGrid\Mail\Mail();
        $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "Cycloid Talent - SG-SST");
        $email->setSubject($subject);

        if ($correoCliente) {
            $email->addTo($correoCliente, $nombreCliente);
        }
        if ($correoConsultor) {
            $email->addTo($correoConsultor, $nombreConsultor);
        }

        $email->addContent("text/html", $htmlContent);

        // Adjuntar .ics
        $email->addAttachment(
            base64_encode($icsContent),
            'text/calendar; method=REQUEST',
            'invitacion.ics',
            'attachment'
        );

        $sendgrid = new \SendGrid($sendgridApiKey);

        try {
            $response = $sendgrid->send($email);

            if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                // Actualizar registro
                $this->model->update($id, [
                    'email_enviado'      => 1,
                    'fecha_email_enviado' => date('Y-m-d H:i:s'),
                    'estado'             => 'confirmado',
                    'confirmacion_calendar' => 'Agendado: enviado a ' . ($correoCliente ?: $correoConsultor),
                ]);

                $destinatarios = array_filter([$correoCliente, $correoConsultor]);
                log_message('info', "Agendamiento #{$id}: Invitación enviada a " . implode(', ', $destinatarios));

                return ['success' => true, 'message' => 'Invitación enviada a ' . implode(', ', $destinatarios)];
            } else {
                log_message('error', "Agendamiento #{$id}: Error SendGrid. Status: {$response->statusCode()}. Body: {$response->body()}");
                return ['success' => false, 'error' => 'Error al enviar. Status: ' . $response->statusCode()];
            }
        } catch (\Exception $e) {
            log_message('error', "Agendamiento #{$id}: Exception SendGrid: " . $e->getMessage());
            return ['success' => false, 'error' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Envía email de cancelación con .ics METHOD:CANCEL
     */
    private function enviarCancelacion(int $id, array $agendamiento): void
    {
        $clientModel = new ClientModel();
        $cliente = $clientModel->find($agendamiento['id_cliente']);
        $consultantModel = new ConsultantModel();
        $consultor = $consultantModel->find($agendamiento['id_consultor']);

        if (!$cliente || !$consultor) return;

        $sendgridApiKey = getenv('SENDGRID_API_KEY');
        if (!$sendgridApiKey) return;

        $nombreCliente   = $cliente['nombre_cliente'] ?? 'Cliente';
        $correoCliente   = $cliente['correo_cliente'] ?? '';
        $correoConsultor = $consultor['correo_consultor'] ?? '';
        $nombreConsultor = $consultor['nombre_consultor'] ?? 'Consultor';
        $fechaFormateada = date('d/m/Y', strtotime($agendamiento['fecha_visita']));

        $icsContent = $this->generateIcs($agendamiento, $cliente, $consultor, 'CANCEL');

        require_once ROOTPATH . 'vendor/autoload.php';

        $email = new \SendGrid\Mail\Mail();
        $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "Cycloid Talent - SG-SST");
        $email->setSubject("CANCELADA: Visita SST - {$nombreCliente} - {$fechaFormateada}");

        if ($correoCliente) $email->addTo($correoCliente, $nombreCliente);
        if ($correoConsultor) $email->addTo($correoConsultor, $nombreConsultor);

        $email->addContent("text/html", "<div style='font-family:Segoe UI,Arial;max-width:600px;margin:0 auto;'><div style='background:#c0392b;padding:20px;text-align:center;border-radius:10px 10px 0 0;'><h1 style='color:#fff;margin:0;font-size:20px;'>VISITA CANCELADA</h1></div><div style='padding:25px;background:#f8f9fa;border-radius:0 0 10px 10px;'><p>La visita SST programada para el <strong>{$fechaFormateada}</strong> con <strong>{$nombreCliente}</strong> ha sido cancelada.</p><p style='color:#999;font-size:11px;'>Generado por SG-SST Cycloid Talent.</p></div></div>");

        $email->addAttachment(base64_encode($icsContent), 'text/calendar; method=CANCEL', 'cancelacion.ics', 'attachment');

        try {
            $sendgrid = new \SendGrid($sendgridApiKey);
            $sendgrid->send($email);
        } catch (\Exception $e) {
            log_message('error', "Agendamiento #{$id}: Error enviando cancelación: " . $e->getMessage());
        }
    }
}
