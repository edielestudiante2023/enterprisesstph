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
    /**
     * Mostrar el formulario para agregar un nuevo vencimiento.
     */
    public function addVencimientosMantenimiento()
    {
        // Instanciar los modelos necesarios
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $mantenimientoModel = new MantenimientoModel();

        // Obtener los datos para los select de clientes, consultores y mantenimientos
        $clientes = $clientModel->findAll();
        $consultores = $consultantModel->findAll();
        $mantenimientos = $mantenimientoModel->findAll();

        // Cargar la vista con los datos
        return view('consultant/vencimientos/addVencimientosMantenimiento', [
            'clientes' => $clientes,
            'consultores' => $consultores,
            'mantenimientos' => $mantenimientos,
        ]);
    }

    /**
     * Procesar el formulario de agregar un nuevo vencimiento.
     */
    public function addpostVencimientosMantenimiento()
    {
        // Instanciar el modelo de vencimientos
        $vencimientosModel = new VencimientosMantenimientoModel();

        // Recoger los datos del formulario
        $data = [
            'id_mantenimiento'    => $this->request->getVar('id_mantenimiento'),
            'id_cliente'          => $this->request->getVar('id_cliente'),
            'id_consultor'        => $this->request->getVar('id_consultor'),
            'fecha_vencimiento'   => $this->request->getVar('fecha_vencimiento'),
            'estado_actividad'    => $this->request->getVar('estado_actividad'),
            'fecha_realizacion'   => $this->request->getVar('fecha_realizacion'),
            'observaciones'       => $this->request->getVar('observaciones'),
        ];

        // Guardar los datos en la base de datos
        if ($vencimientosModel->save($data)) {
            return redirect()->to(base_url('vencimientos'))->with('msg', 'Vencimiento agregado exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al guardar el vencimiento.')->withInput();
        }
    }

    /**
     * Mostrar el formulario para editar un vencimiento existente.
     *
     * @param int $id ID del vencimiento a editar.
     */
    public function editVencimientosMantenimiento($id)
    {
        // Instanciar los modelos necesarios
        $vencimientosModel = new VencimientosMantenimientoModel();
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $mantenimientoModel = new MantenimientoModel();

        // Buscar el vencimiento por ID
        $vencimiento = $vencimientosModel->find($id);

        if (!$vencimiento) {
            return redirect()->back()->with('msg', 'Vencimiento no encontrado.');
        }

        // Obtener los datos para los select
        $clientes = $clientModel->findAll();
        $consultores = $consultantModel->findAll();
        $mantenimientos = $mantenimientoModel->findAll();

        // Cargar la vista con los datos
        return view('consultant/vencimientos/editVencimientosMantenimiento', [
            'vencimiento'   => $vencimiento,
            'clientes'      => $clientes,
            'consultores'   => $consultores,
            'mantenimientos' => $mantenimientos,
        ]);
    }

    /**
     * Procesar el formulario de edición de un vencimiento.
     *
     * @param int $id ID del vencimiento a actualizar.
     */
    public function editpostVencimientosMantenimiento($id)
    {
        // Instanciar el modelo de vencimientos
        $vencimientosModel = new VencimientosMantenimientoModel();

        // Recoger los datos del formulario
        $data = [
            'id_vencimientos_mmttos' => $id,
            'id_mantenimiento'       => $this->request->getVar('id_mantenimiento'),
            'id_cliente'             => $this->request->getVar('id_cliente'),
            'id_consultor'           => $this->request->getVar('id_consultor'),
            'fecha_vencimiento'      => $this->request->getVar('fecha_vencimiento'),
            'estado_actividad'       => $this->request->getVar('estado_actividad'),
            'fecha_realizacion'      => $this->request->getVar('fecha_realizacion'),
            'observaciones'          => $this->request->getVar('observaciones'),
        ];

        // Actualizar los datos en la base de datos
        if ($vencimientosModel->save($data)) {
            return redirect()->to(base_url('vencimientos'))->with('msg', 'Vencimiento actualizado exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al actualizar el vencimiento.')->withInput();
        }
    }

    /**
     * Eliminar un vencimiento específico.
     *
     * @param int $id ID del vencimiento a eliminar.
     */
    public function deleteVencimientosMantenimiento($id)
    {
        // Instanciar el modelo de vencimientos
        $vencimientosModel = new VencimientosMantenimientoModel();

        // Eliminar el vencimiento
        if ($vencimientosModel->delete($id)) {
            return redirect()->to(base_url('vencimientos'))->with('msg', 'Vencimiento eliminado exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al eliminar el vencimiento.');
        }
    }

    /**
     * Listar todos los vencimientos con información descriptiva.
     */
    public function listVencimientosMantenimiento()
    {
        // Instanciar los modelos necesarios
        $vencimientosModel = new VencimientosMantenimientoModel();
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $mantenimientoModel = new MantenimientoModel();

        // Obtener todos los vencimientos
        $vencimientos = $vencimientosModel->findAll();

        // Preparar los datos descriptivos para la vista
        $dataVencimientos = [];

        foreach ($vencimientos as $vencimiento) {
            $cliente = $clientModel->find($vencimiento['id_cliente']);
            $consultor = $consultantModel->find($vencimiento['id_consultor']);
            $mantenimiento = $mantenimientoModel->find($vencimiento['id_mantenimiento']);

            $dataVencimientos[] = [
                'id'                 => $vencimiento['id_vencimientos_mmttos'],
                'cliente'            => $cliente ? $cliente['nombre_cliente'] : 'Desconocido',
                'consultor'          => $consultor ? $consultor['nombre_consultor'] : 'Desconocido',
                'mantenimiento'      => $mantenimiento ? $mantenimiento['detalle_mantenimiento'] : 'No especificado',
                'fecha_vencimiento'  => $vencimiento['fecha_vencimiento'],
                'estado_actividad'   => ucfirst($vencimiento['estado_actividad']),
                'fecha_realizacion'  => $vencimiento['fecha_realizacion'] ?? 'N/A',
                'observaciones'      => $vencimiento['observaciones'] ?? 'N/A',
            ];
        }

        // Cargar la vista con los datos
        return view('consultant/vencimientos/listVencimientosMantenimiento', [
            'vencimientos' => $dataVencimientos,
        ]);
    }

    /**
     * Enviar correos electrónicos para vencimientos próximos.
     */
    public function sendEmailsForUpcomingVencimientos()
    {
        $vencimientosModel = new VencimientosMantenimientoModel();
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $mantenimientoModel = new MantenimientoModel();

        // Obtener vencimientos próximos
        $vencimientos = $vencimientosModel->getUpcomingVencimientos();

        // Log de depuración para ver fechas y resultados
        log_message('debug', 'Intentando obtener vencimientos entre ' . date('Y-m-d') . ' y ' . date('Y-m-d', strtotime('+30 days')));
        log_message('debug', 'Vencimientos encontrados: ' . print_r($vencimientos, true));

        if (empty($vencimientos)) {
            log_message('error', '❌ No hay vencimientos próximos para enviar correos.');
            return redirect()->to(base_url('vencimientos'))->with('msg', 'No hay vencimientos próximos para enviar.');
        }

        foreach ($vencimientos as $vencimiento) {
            $cliente = $clientModel->find($vencimiento['id_cliente']);
            $consultor = $consultantModel->find($vencimiento['id_consultor']);
            $mantenimiento = $mantenimientoModel->find($vencimiento['id_mantenimiento']);

            // Validar que cliente y consultor existan
            if (!$cliente || !$consultor) {
                log_message('error', "⚠️ Error: Cliente o consultor no encontrados para vencimiento ID: {$vencimiento['id_vencimientos_mmttos']}");
                continue;
            }

            // Verificar si los correos son válidos
            if (empty($cliente['correo_cliente']) || !filter_var($cliente['correo_cliente'], FILTER_VALIDATE_EMAIL)) {
                log_message('error', "⚠️ Correo del cliente no válido o vacío: " . ($cliente['correo_cliente'] ?? 'No definido'));
                continue;
            }

            if (empty($consultor['correo_consultor']) || !filter_var($consultor['correo_consultor'], FILTER_VALIDATE_EMAIL)) {
                log_message('error', "⚠️ Correo del consultor no válido o vacío: " . ($consultor['correo_consultor'] ?? 'No definido'));
                continue;
            }

            // Eliminar correos duplicados
            $destinatarios = array_unique([$cliente['correo_cliente'], $consultor['correo_consultor']]);

            // Log de destinatarios
            log_message('debug', '📧 Destinatarios del correo: ' . implode(', ', $destinatarios));

            $email = new \SendGrid\Mail\Mail();
            $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "Cycloid Talent");
            $email->setSubject("🔔 Recordatorio de Vencimiento");

            // Agregar destinatarios únicos
            foreach ($destinatarios as $correo) {
                $email->addTo($correo);
            }

            $email->addContent(
                "text/html",
                "<p>🔔 <strong>Estimado/a {$cliente['nombre_cliente']}</strong>,</p>
                
                 <p>Nos dirigimos a usted con el firme propósito de recordarle que el mantenimiento <strong>{$mantenimiento['detalle_mantenimiento']}</strong> tiene su fecha de vencimiento programada para el <strong>{$vencimiento['fecha_vencimiento']}</strong>.</p>
            
                 <p>En <strong>Cycloid Talent</strong>, entendemos la importancia de un mantenimiento oportuno para garantizar la seguridad y el correcto funcionamiento de sus instalaciones. Como expertos en <strong>Seguridad y Salud en el Trabajo (SST)</strong>, estamos comprometidos en apoyarle en la planificación y control de estas actividades, asegurando que cada proceso se ejecute de manera eficiente y conforme a la normativa.</p>
            
                 <p>💡 Le recomendamos gestionar este mantenimiento con antelación para evitar riesgos y garantizar la continuidad operativa. Si necesita orientación o soporte, nuestro equipo está disponible para asistirle en cada paso.</p>
            
                 <p>✨ <strong>¡Su seguridad y tranquilidad son nuestra prioridad!</strong> No dude en contactarnos para cualquier consulta adicional.</p>
            
                 <p>Saludos cordiales,</p>
                 <p><strong>Cycloid Talent</strong></p>"
            );


            // Intentar enviar el correo
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
     * Función auxiliar para enviar correos electrónicos utilizando SendGrid.
     *
     * @param string $clientEmail      Correo electrónico del cliente.
     * @param string $consultantEmail  Correo electrónico del consultor.
     * @param string $subject          Asunto del correo.
     * @param string $content          Contenido HTML del correo.
     */
    private function sendEmail($clientEmail, $consultantEmail, $subject, $content)
    {
        $email = new Mail();
        $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "Cycloid Talent");
        $email->setSubject($subject);
        $email->addTo($clientEmail);
        $email->addTo($consultantEmail);
        $email->addContent("text/html", $content);

        // Obtener la clave API de SendGrid desde las variables de entorno
        $sendgridApiKey = getenv('SENDGRID_API_KEY');

        if (!$sendgridApiKey) {
            log_message('error', 'Clave API de SendGrid no configurada.');
            return;
        }

        $sendgrid = new \SendGrid($sendgridApiKey);

        try {
            // Enviar el correo
            $response = $sendgrid->send($email);
            log_message('debug', 'Correo enviado con éxito. Status Code: ' . $response->statusCode());
        } catch (\Exception $e) {
            log_message('error', 'Error al enviar el correo: ' . $e->getMessage());
        }
    }

    public function sendEmailsAutomatically()
    {
        // Instanciar los modelos necesarios
        $vencimientosModel = new VencimientosMantenimientoModel();
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $mantenimientoModel = new MantenimientoModel();

        // Obtener vencimientos próximos a menos de 30 días y sin ejecutar
        $vencimientos = $vencimientosModel->getUpcomingVencimientos();



        if (empty($vencimientos)) {
            log_message('info', 'No hay vencimientos próximos para enviar correos.');
            return;
        }

        foreach ($vencimientos as $vencimiento) {
            // Obtener datos del cliente y consultor
            $cliente = $clientModel->find($vencimiento['id_cliente']);
            $consultor = $consultantModel->find($vencimiento['id_consultor']);

            // Validar que ambos tengan correo registrado
            if (empty($cliente['correo_cliente']) || empty($consultor['correo_consultor'])) {
                log_message('error', "Faltan correos electrónicos para el vencimiento ID: {$vencimiento['id_vencimientos_mmttos']}");
                continue;
            }

            // Obtener detalles del mantenimiento
            $mantenimiento = $mantenimientoModel->find($vencimiento['id_mantenimiento']);
            $tituloMantenimiento = $mantenimiento ? $mantenimiento['detalle_mantenimiento'] : 'Mantenimiento no especificado';

            // Crear contenido del correo
            $emailContent = "
            <h3>Recordatorio de Vencimiento</h3>
            <p>Estimado/a <strong>{$cliente['nombre_cliente']}</strong> y Consultor <strong>{$consultor['nombre_consultor']}</strong>,</p>
            <p>El mantenimiento <strong>{$tituloMantenimiento}</strong> está próximo a vencer el día <strong>{$vencimiento['fecha_vencimiento']}</strong>.</p>
            <p>Por favor, tomen las acciones necesarias para su ejecución antes de la fecha de vencimiento.</p>
            <p>Saludos,</p>
            <p><strong>Cycloid Talent</strong></p>
        ";

            // Enviar correos electrónicos
            $this->sendEmail(
                $cliente['correo_cliente'],
                $consultor['correo_consultor'],
                'Recordatorio de Vencimiento de Mantenimiento',
                $emailContent
            );
        }



        log_message('info', 'Correos electrónicos de recordatorio enviados automáticamente.');
    }

    public function testEmailForVencimiento($id)
    {
        $vencimientosModel = new VencimientosMantenimientoModel();
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $mantenimientoModel = new MantenimientoModel();

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
            log_message('info', 'SendGrid Response: ' . $response->body()); // Ver el cuerpo de la respuesta
            return "Correo enviado. Código de estado: " . $response->statusCode() . "<br>Respuesta de SendGrid: " . $response->body();
        } catch (\Exception $e) {
            log_message('error', 'Error al enviar correo de prueba: ' . $e->getMessage());
            return "Error al enviar correo: " . $e->getMessage();
        }
    }

    public function sendSelectedEmails()
    {
        // Recoger los IDs enviados (arreglo de IDs)
        $selectedIds = $this->request->getPost('selected');

        if (empty($selectedIds)) {
            return redirect()->to(base_url('vencimientos'))->with('msg', 'No se seleccionaron vencimientos.');
        }

        // Instanciar los modelos necesarios
        $vencimientosModel = new VencimientosMantenimientoModel();
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $mantenimientoModel = new MantenimientoModel();

        // Recorrer cada ID seleccionado y enviar el correo
        foreach ($selectedIds as $id) {
            $vencimiento = $vencimientosModel->find($id);
            if (!$vencimiento) {
                continue;
            }

            $cliente = $clientModel->find($vencimiento['id_cliente']);
            $consultor = $consultantModel->find($vencimiento['id_consultor']);
            $mantenimiento = $mantenimientoModel->find($vencimiento['id_mantenimiento']);

            // Validar que existan y que los correos sean válidos
            if (!$cliente || !$consultor) {
                continue;
            }
            if (empty($cliente['correo_cliente']) || !filter_var($cliente['correo_cliente'], FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            if (empty($consultor['correo_consultor']) || !filter_var($consultor['correo_consultor'], FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            // Preparar el contenido del correo (ajusta el HTML según tus necesidades)
            $emailContent = "
          <p>Hola <strong>{$cliente['nombre_cliente']}</strong>,</p>
          <p>El mantenimiento <strong>{$mantenimiento['detalle_mantenimiento']}</strong> tiene su fecha de vencimiento programada para el <strong>{$vencimiento['fecha_vencimiento']}</strong>.</p>
          <p>Por favor, verifica los detalles y toma las medidas necesarias.</p>
        ";

            // Reutilizamos el método sendEmail existente para enviar el correo
            $this->sendEmail(
                $cliente['correo_cliente'],
                $consultor['correo_consultor'],
                'Recordatorio de Vencimiento de Mantenimiento',
                $emailContent
            );
        }

        return redirect()->to(base_url('vencimientos'))->with('msg', 'Correos enviados a los registros seleccionados.');
    }
}
