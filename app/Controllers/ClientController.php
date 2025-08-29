<?php

namespace App\Controllers;

require __DIR__ . '/../../vendor/autoload.php';

use App\Models\ClientModel;
use App\Models\AccesoModel;
use App\Models\EstandarModel;
use App\Models\EstandarAccesoModel;
use CodeIgniter\Controller;
use App\Models\ReporteModel;
use SendGrid\Mail\Mail;

class ClientController extends Controller
{
    public function index()
    {
        $session = session();
        $clientId = $session->get('user_id');

        $model = new ClientModel();
        $client = $model->find($clientId);

        if (!$client) {
            return redirect()->to('/login')->with('error', 'Cliente no encontrado');
        }

        $data = [
            'client' => $client
        ];

        return view('client/dashboard', $data);
    }

    public function dashboard()
    {
        try {
            $session = session();

            // Obtener el ID del cliente desde la sesión
            $id_cliente = $session->get('user_id');
            if (!$id_cliente) {
                return redirect()->to('/login')->with('error', 'Cliente no autenticado.');
            }

            // Obtener el cliente
            $clientModel = new ClientModel();
            $client = $clientModel->find($id_cliente);
            if (!$client) {
                return redirect()->to('/login')->with('error', 'Cliente no encontrado.');
            }

            // Inicializar $accesos como un array vacío
            $accesos = [];

            // Obtener el estándar del cliente (por ejemplo '7A')
            $estandarNombre = $client['estandares'];

            // Instanciar el modelo de estandares y obtener el ID del estándar (por ejemplo 1 para '7A')
            $estandarModel = new EstandarModel();
            $estandar = $estandarModel->where('nombre', $estandarNombre)->first();

            if (!$estandar) {
                return redirect()->to('/login')->with('error', 'Estándar no encontrado.');
            }

            $id_estandar = $estandar['id_estandar'];  // Esto nos da el ID numérico del estándar

            // Obtener los accesos permitidos para el estándar usando el modelo EstandarAccesoModel
            $estandarAccesoModel = new EstandarAccesoModel();
            $accesosData = $estandarAccesoModel->where('id_estandar', $id_estandar)->findAll();

            // Si no hay accesos asociados al estándar
            if (empty($accesosData)) {
                // Enviar notificación por email
                $this->sendMissingAccessNotification($id_cliente, $client['nombre_cliente'], $estandarNombre);
                
                // Pasar array vacío a la vista para que muestre el mensaje
                $accesos = [];
            } else {
                // Instanciar el modelo de accesos para obtener los detalles de cada acceso ordenado por la dimensión
                $accesoModel = new AccesoModel();

                // Obtener todos los accesos relacionados con el estándar y ordenarlos por la dimensión
                $accesos = $accesoModel
                    ->whereIn('id_acceso', array_column($accesosData, 'id_acceso'))
                    ->findAll();

                // Ordenar en PHP usando el ciclo PHVA
                $orden = ["Planear", "Hacer", "Verificar", "Actuar", "Indicadores"];

                usort($accesos, function ($a, $b) use ($orden) {
                    return array_search($a['dimension'], $orden) - array_search($b['dimension'], $orden);
                });
            }


            // Pasar los accesos a la vista `dashboardclient`
            return view('client/dashboard', [
                'accesos' => $accesos,
                'client' => $client
            ]);
        } catch (\Exception $e) {
            echo "Ocurrió un error: " . $e->getMessage();
            exit;
        }
    }

    private function getReportsForType($reportModel, $clientId, $reportTypeId)
    {
        return $reportModel
            ->select('
                tbl_reporte.id_reporte,
                tbl_reporte.titulo_reporte,
                tbl_reporte.enlace,
                tbl_reporte.estado,
                tbl_reporte.observaciones,
                tbl_reporte.created_at,
                tbl_reporte.updated_at,
                detail_report.detail_report AS detalle_reporte,
                report_type_table.report_type AS tipo_reporte,
                tbl_clientes.nombre_cliente AS cliente_nombre
            ')
            ->join('detail_report', 'detail_report.id_detailreport = tbl_reporte.id_detailreport', 'left')
            ->join('report_type_table', 'report_type_table.id_report_type = tbl_reporte.id_report_type', 'left')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_reporte.id_cliente', 'left')
            ->where('tbl_reporte.id_cliente', $clientId)
            ->where('tbl_reporte.id_report_type', $reportTypeId)
            ->orderBy('tbl_reporte.created_at', 'DESC')
            ->findAll();
    }

    public function viewDocuments()
    {
        $reportModel = new ReporteModel();

        // 1) Obtener el ID del cliente desde la sesión
        $clientId = session()->get('user_id');
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Sesión no válida.');
        }

        // 2) Mapeo de claves ⇒ ID de reporte
        $reportTypes = [
            'inspecciones'       => 1,
            'reportes'           => 2,
            'aseo'               => 3,
            'vigilancia'         => 4,
            'ambiental'          => 5,
            'actasdevisita'      => 6,
            'capacitaciones'     => 7,
            'cincuentahoras'     => 8,
            'reporteministerio'  => 9,
            'cierredemes'        => 10,
            'emergencias'        => 11,
            'otrosproveedores'   => 12,
            'secretariasalud'    => 13,
            'lavadotanques'      => 14,
            'localescomerciales' => 15,
            'fumigaciones'       => 16,
            'normatividad'       => 17,
            'contrato'           => 19,
            'saneamiento'        => 20,
            'consultor'          => 21,
        ];

        // 3) Mapeo de claves ⇒ títulos para el menú (topicsList)
        $topicsList = [
            'inspecciones'       => 'Inspecciones',
            'reportes'           => 'Reportes Generales',
            'aseo'               => 'Servicios de Aseo',
            'vigilancia'         => 'Vigilancia',
            'ambiental'          => 'Plan de Gestión Ambiental',
            'actasdevisita'      => 'Actas de Visita SST',
            'capacitaciones'     => 'Capacitaciones en SST',
            'cincuentahoras'     => 'Programa 50 Horas',
            'reporteministerio'  => 'Reportes Ministerio',
            'cierredemes'        => 'Cierre de Meses',
            'emergencias'        => 'Protocolos de Emergencia',
            'otrosproveedores'   => 'Otros Proveedores',
            'secretariasalud'    => 'Secretaría de Salud',
            'lavadotanques'      => 'Lavado de Tanques',
            'localescomerciales' => 'Locales Comerciales',
            'fumigaciones'       => 'Fumigaciones',
            'normatividad'       => 'Documentación Normativa',
            'contrato'           => 'Contratos',
            'saneamiento'        => 'Saneamiento Básico',
            'consultor'          => 'Informes de Consultor',
        ];

        // 4) Inicializar data con topicsList
        $data = [
            'topicsList' => $topicsList
        ];

        // 5) Cargar los reportes en cada key
        foreach ($reportTypes as $key => $typeId) {
            $data[$key] = $this->getReportsForType($reportModel, $clientId, $typeId);
        }

        // 6) Enviar todo a la vista
        return view('client/document_view', $data);
    }

    public function sendMissingAccessNotification($clientId, $clientName, $standard)
    {
        log_message('info', "Enviando notificación - Cliente ID: $clientId");
        
        try {
            $email = new \SendGrid\Mail\Mail();
            $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "Sistema Cycloid Talent");
            $email->setSubject("Cliente sin accesos a documentación - ID: $clientId");
            $email->addTo("edison.cuervo@cycloidtalent.com", "Edison Cuervo");
            
            $htmlContent = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <h2 style='color: #d32f2f; border-bottom: 2px solid #d32f2f; padding-bottom: 10px;'>
                        ⚠️ Cliente sin Accesos a Documentación
                    </h2>
                    <div style='background-color: #f5f5f5; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                        <h3 style='margin-top: 0; color: #333;'>Detalles del Evento:</h3>
                        <p><strong>ID Cliente:</strong> $clientId</p>
                        <p><strong>Nombre Cliente:</strong> $clientName</p>
                        <p><strong>Estándar:</strong> $standard</p>
                        <p><strong>Fecha y Hora:</strong> " . date('Y-m-d H:i:s') . "</p>
                    </div>
                    <div style='background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0;'>
                        <p><strong>Acción Requerida:</strong></p>
                        <p>El cliente intentó acceder a su documentación pero no tiene tablas maestras configuradas. 
                        Por favor, revise y configure los accesos correspondientes para este cliente.</p>
                    </div>
                    <hr style='margin: 30px 0;'>
                    <p style='color: #666; font-size: 14px;'>
                        Este mensaje fue generado automáticamente por el sistema Cycloid Talent.
                    </p>
                </div>
            ";
            
            $email->addContent("text/html", $htmlContent);
            
            $plainContent = "
CLIENTE SIN ACCESOS A DOCUMENTACIÓN

ID Cliente: $clientId
Nombre Cliente: $clientName
Estándar: $standard
Fecha y Hora: " . date('Y-m-d H:i:s') . "

El cliente intentó acceder a su documentación pero no tiene tablas maestras configuradas.
Por favor, revise y configure los accesos correspondientes para este cliente.

---
Este mensaje fue generado automáticamente por el sistema Cycloid Talent.
            ";
            
            $email->addContent("text/plain", $plainContent);

            $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
            $response = $sendgrid->send($email);
            
            log_message('info', "Email enviado - Cliente ID: $clientId - Status Code: " . $response->statusCode());
            
        } catch (\Exception $e) {
            log_message('error', "Error enviando email para cliente $clientId: " . $e->getMessage());
        }
    }
}
