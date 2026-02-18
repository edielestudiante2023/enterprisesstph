<?php

namespace App\Controllers;

use App\Models\ContractModel;
use App\Models\ClientModel;
use App\Models\UserModel;
use App\Libraries\ContractLibrary;
use App\Libraries\ContractPDFGenerator;
use CodeIgniter\Controller;
use SendGrid\Mail\Mail;

class ContractController extends Controller
{
    protected $contractModel;
    protected $clientModel;
    protected $contractLibrary;

    public function __construct()
    {
        $this->contractModel = new ContractModel();
        $this->clientModel = new ClientModel();
        $this->contractLibrary = new ContractLibrary();
        helper('contract');
    }

    /**
     * Lista todos los contratos con filtros
     */
    public function index()
    {
        $session = session();
        $role = $session->get('role');
        $idConsultor = $session->get('id_consultor');

        // Filtros
        $estado = $this->request->getGet('estado');
        $tipo = $this->request->getGet('tipo');
        $idCliente = $this->request->getGet('id_cliente');
        $estadoCliente = $this->request->getGet('estado_cliente');

        $builder = $this->contractModel->builder();
        $builder->select('tbl_contratos.*, tbl_clientes.nombre_cliente, tbl_clientes.nit_cliente, tbl_clientes.estado as estado_cliente')
                ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_contratos.id_cliente');

        // Filtrar por consultor si es consultor
        if ($role === 'consultor') {
            $builder->where('tbl_clientes.id_consultor', $idConsultor);
        }

        // Aplicar filtros
        if ($estado) {
            $builder->where('tbl_contratos.estado', $estado);
        }

        if ($tipo) {
            $builder->where('tbl_contratos.tipo_contrato', $tipo);
        }

        if ($idCliente) {
            $builder->where('tbl_contratos.id_cliente', $idCliente);
        }

        // Filtro por estado del cliente (activo/inactivo/pendiente)
        if ($estadoCliente) {
            $builder->where('tbl_clientes.estado', $estadoCliente);
        }

        $contracts = $builder->orderBy('tbl_contratos.created_at', 'DESC')->get()->getResultArray();

        // Obtener estadísticas (filtradas por estado del cliente si se especifica)
        $stats = $this->contractLibrary->getContractStats(
            $role === 'consultor' ? $idConsultor : null,
            $estadoCliente
        );

        // Obtener lista de clientes para el filtro
        $clients = $role === 'consultor'
            ? $this->clientModel->where('id_consultor', $idConsultor)->findAll()
            : $this->clientModel->findAll();

        $data = [
            'contracts' => $contracts,
            'stats' => $stats,
            'clients' => $clients,
            'filters' => [
                'estado' => $estado,
                'tipo' => $tipo,
                'id_cliente' => $idCliente,
                'estado_cliente' => $estadoCliente
            ]
        ];

        return view('contracts/list', $data);
    }

    /**
     * Ver detalles de un contrato
     */
    public function view($idContrato)
    {
        $contract = $this->contractLibrary->getContractWithClient($idContrato);

        if (!$contract) {
            return redirect()->to('/contracts')->with('error', 'Contrato no encontrado');
        }

        // Verificar permisos
        $session = session();
        $role = $session->get('role');
        $idConsultor = $session->get('id_consultor');

        if ($role === 'consultor') {
            $client = $this->clientModel->find($contract['id_cliente']);
            if ($client['id_consultor'] != $idConsultor) {
                return redirect()->to('/contracts')->with('error', 'No tiene permisos para ver este contrato');
            }
        }

        // Obtener historial del cliente
        $history = $this->contractLibrary->getClientContractHistory($contract['id_cliente']);

        $data = [
            'contract' => $contract,
            'history' => $history
        ];

        return view('contracts/view', $data);
    }

    /**
     * Formulario para crear un nuevo contrato
     */
    public function create($idCliente = null)
    {
        $session = session();
        $role = $session->get('role');
        $idConsultor = $session->get('id_consultor');

        // Obtener clientes según el rol
        if ($idCliente) {
            $client = $this->clientModel->find($idCliente);

            // Verificar permisos
            if ($role === 'consultor' && $client['id_consultor'] != $idConsultor) {
                return redirect()->to('/contracts')->with('error', 'No tiene permisos');
            }

            $clients = [$client];
        } else {
            $clients = $role === 'consultor'
                ? $this->clientModel->where('id_consultor', $idConsultor)->findAll()
                : $this->clientModel->findAll();
        }

        $data = [
            'clients' => $clients,
            'selected_client' => $idCliente
        ];

        return view('contracts/create', $data);
    }

    /**
     * Procesar la creación de un nuevo contrato
     */
    public function store()
    {
        $data = [
            'id_cliente' => $this->request->getPost('id_cliente'),
            'fecha_inicio' => $this->request->getPost('fecha_inicio'),
            'fecha_fin' => $this->request->getPost('fecha_fin'),
            'valor_contrato' => $this->request->getPost('valor_contrato'),
            'valor_mensual' => $this->request->getPost('valor_mensual'),
            'numero_cuotas' => $this->request->getPost('numero_cuotas'),
            'frecuencia_visitas' => $this->request->getPost('frecuencia_visitas'),
            'tipo_contrato' => $this->request->getPost('tipo_contrato'),
            'estado' => $this->request->getPost('estado') ?: 'activo',
            'observaciones' => $this->request->getPost('observaciones'),
            'clausula_cuarta_duracion' => $this->request->getPost('clausula_cuarta_duracion')
        ];

        // Validar que no se superpongan fechas
        $validation = $this->contractLibrary->canCreateContract(
            $data['id_cliente'],
            $data['fecha_inicio'],
            $data['fecha_fin']
        );

        if (!$validation['can_create']) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', $validation['message']);
        }

        // Crear el contrato
        $result = $this->contractLibrary->createContract($data);

        if ($result['success']) {
            return redirect()->to('/contracts/view/' . $result['contract_id'])
                           ->with('success', $result['message']);
        }

        return redirect()->back()
                       ->withInput()
                       ->with('error', $result['message']);
    }

    /**
     * Formulario para renovar un contrato
     */
    public function renew($idContrato)
    {
        $contract = $this->contractModel->find($idContrato);

        if (!$contract) {
            return redirect()->to('/contracts')->with('error', 'Contrato no encontrado');
        }

        // Verificar permisos
        $session = session();
        $role = $session->get('role');
        $idConsultor = $session->get('id_consultor');

        if ($role === 'consultor') {
            $client = $this->clientModel->find($contract['id_cliente']);
            if ($client['id_consultor'] != $idConsultor) {
                return redirect()->to('/contracts')->with('error', 'No tiene permisos');
            }
        }

        $client = $this->clientModel->find($contract['id_cliente']);

        $data = [
            'contract' => $contract,
            'client' => $client
        ];

        return view('contracts/renew', $data);
    }

    /**
     * Procesar la renovación de un contrato
     */
    public function processRenewal()
    {
        $idContrato = $this->request->getPost('id_contrato');
        $fechaFin = $this->request->getPost('fecha_fin');
        $valorContrato = $this->request->getPost('valor_contrato');
        $observaciones = $this->request->getPost('observaciones');

        $result = $this->contractLibrary->renewContract($idContrato, $fechaFin, $valorContrato, $observaciones);

        if ($result['success']) {
            return redirect()->to('/contracts/view/' . $result['contract_id'])
                           ->with('success', $result['message']);
        }

        return redirect()->back()
                       ->withInput()
                       ->with('error', $result['message']);
    }

    /**
     * Cancelar un contrato
     */
    public function cancel($idContrato)
    {
        if ($this->request->getMethod() === 'post') {
            $motivo = $this->request->getPost('motivo');

            $result = $this->contractLibrary->cancelContract($idContrato, $motivo);

            if ($result['success']) {
                return redirect()->to('/contracts')
                               ->with('success', $result['message']);
            }

            return redirect()->back()
                           ->with('error', $result['message']);
        }

        $contract = $this->contractLibrary->getContractWithClient($idContrato);

        if (!$contract) {
            return redirect()->to('/contracts')->with('error', 'Contrato no encontrado');
        }

        return view('contracts/cancel', ['contract' => $contract]);
    }

    /**
     * Ver historial de contratos de un cliente
     */
    public function clientHistory($idCliente)
    {
        $client = $this->clientModel->find($idCliente);

        if (!$client) {
            return redirect()->to('/contracts')->with('error', 'Cliente no encontrado');
        }

        // Verificar permisos
        $session = session();
        $role = $session->get('role');
        $idConsultor = $session->get('id_consultor');

        if ($role === 'consultor' && $client['id_consultor'] != $idConsultor) {
            return redirect()->to('/contracts')->with('error', 'No tiene permisos');
        }

        $history = $this->contractLibrary->getClientContractHistory($idCliente);

        $data = [
            'client' => $client,
            'history' => $history
        ];

        return view('contracts/client_history', $data);
    }

    /**
     * Dashboard de alertas de contratos
     */
    public function alerts()
    {
        $session = session();
        $role = $session->get('role');
        $idConsultor = $session->get('id_consultor');

        $alerts = $this->contractLibrary->getContractAlerts(
            $role === 'consultor' ? $idConsultor : null,
            30
        );

        $data = [
            'alerts' => $alerts
        ];

        return view('contracts/alerts', $data);
    }

    /**
     * Ejecutar mantenimiento de contratos (cron job)
     */
    public function maintenance()
    {
        // Verificar que sea llamado desde CLI o con token de seguridad
        if (!is_cli()) {
            $token = $this->request->getGet('token');
            $expectedToken = env('CRON_TOKEN', 'changeme');

            if ($token !== $expectedToken) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No autorizado'
                ])->setStatusCode(401);
            }
        }

        $result = $this->contractLibrary->runMaintenance();

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Mantenimiento ejecutado',
            'data' => $result
        ]);
    }

    /**
     * API: Obtener contrato activo de un cliente
     */
    public function getActiveContract($idCliente)
    {
        $contract = $this->contractModel->getActiveContract($idCliente);

        return $this->response->setJSON([
            'success' => true,
            'data' => $contract
        ]);
    }

    /**
     * API: Obtener estadísticas de contratos
     */
    public function getStats()
    {
        $session = session();
        $role = $session->get('role');
        $idConsultor = $session->get('id_consultor');

        $stats = $this->contractLibrary->getContractStats($role === 'consultor' ? $idConsultor : null);

        return $this->response->setJSON([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Muestra el formulario para editar datos antes de generar el contrato
     */
    public function editContractData($idContrato)
    {
        $contract = $this->contractLibrary->getContractWithClient($idContrato);

        if (!$contract) {
            return redirect()->to('/contracts')->with('error', 'Contrato no encontrado');
        }

        // Verificar permisos
        $session = session();
        $role = $session->get('role');
        $idConsultor = $session->get('id_consultor');

        if ($role === 'consultor') {
            $client = $this->clientModel->find($contract['id_cliente']);
            if ($client['id_consultor'] != $idConsultor) {
                return redirect()->to('/contracts')->with('error', 'No tiene permisos');
            }
        }

        // Obtener lista de consultores para el select
        $consultorModel = new \App\Models\ConsultorModel();
        $consultores = $consultorModel->findAll();

        $data = [
            'contract' => $contract,
            'consultores' => $consultores
        ];

        return view('contracts/edit_contract_data', $data);
    }

    /**
     * Guarda los datos del contrato y genera el PDF
     */
    public function saveAndGeneratePDF($idContrato)
    {
        // Obtener datos del formulario
        $data = [
            'fecha_inicio' => $this->request->getPost('fecha_inicio'),
            'fecha_fin' => $this->request->getPost('fecha_fin'),
            'valor_contrato' => $this->request->getPost('valor_contrato'),
            'valor_mensual' => $this->request->getPost('valor_mensual'),
            'numero_cuotas' => $this->request->getPost('numero_cuotas'),
            'frecuencia_visitas' => $this->request->getPost('frecuencia_visitas'),
            'nombre_rep_legal_cliente' => $this->request->getPost('nombre_rep_legal_cliente'),
            'cedula_rep_legal_cliente' => $this->request->getPost('cedula_rep_legal_cliente'),
            'direccion_cliente' => $this->request->getPost('direccion_cliente'),
            'telefono_cliente' => $this->request->getPost('telefono_cliente'),
            'email_cliente' => $this->request->getPost('email_cliente'),
            'nombre_rep_legal_contratista' => $this->request->getPost('nombre_rep_legal_contratista'),
            'cedula_rep_legal_contratista' => $this->request->getPost('cedula_rep_legal_contratista'),
            'email_contratista' => $this->request->getPost('email_contratista'),
            'id_consultor_responsable' => $this->request->getPost('id_consultor_responsable'),
            'nombre_responsable_sgsst' => $this->request->getPost('nombre_responsable_sgsst'),
            'cedula_responsable_sgsst' => $this->request->getPost('cedula_responsable_sgsst'),
            'licencia_responsable_sgsst' => $this->request->getPost('licencia_responsable_sgsst'),
            'email_responsable_sgsst' => $this->request->getPost('email_responsable_sgsst'),
            'banco' => $this->request->getPost('banco'),
            'tipo_cuenta' => $this->request->getPost('tipo_cuenta'),
            'cuenta_bancaria' => $this->request->getPost('cuenta_bancaria'),
            'clausula_cuarta_duracion' => $this->request->getPost('clausula_cuarta_duracion'),
        ];

        // Actualizar el contrato con los nuevos datos
        if (!$this->contractModel->update($idContrato, $data)) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error al guardar los datos del contrato');
        }

        // Obtener el contrato actualizado con datos del cliente
        $contract = $this->contractLibrary->getContractWithClient($idContrato);

        try {
            // 1. Generar el PDF
            $pdfGenerator = new ContractPDFGenerator();
            $pdfGenerator->generateContract($contract);

            // 2. Crear directorio si no existe
            $uploadDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'contratos' . DIRECTORY_SEPARATOR;
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }
            // Asegurar permisos de escritura
            if (!is_writable($uploadDir)) {
                chmod($uploadDir, 0775);
            }

            // 3. Guardar el PDF
            $fileName = 'contrato_' . $contract['numero_contrato'] . '_' . date('Ymd_His') . '.pdf';
            $filePath = realpath($uploadDir) . DIRECTORY_SEPARATOR . $fileName;
            log_message('info', 'Guardando contrato PDF en: ' . $filePath);
            $pdfGenerator->save($filePath);

            // 4. Actualizar base de datos con información de generación
            $this->contractModel->update($idContrato, [
                'contrato_generado' => 1,
                'fecha_generacion_contrato' => date('Y-m-d H:i:s'),
                'ruta_pdf_contrato' => 'uploads/contratos/' . $fileName
            ]);

            // 5. Enviar email con SendGrid
            $emailSent = $this->sendContractEmail($contract, $filePath, $fileName);

            if ($emailSent) {
                $this->contractModel->update($idContrato, [
                    'contrato_enviado' => 1,
                    'fecha_envio_contrato' => date('Y-m-d H:i:s'),
                    'email_envio_contrato' => 'diana.cuestas@cycloidtalent.com, edison.cuervo@cycloidtalent.com'
                ]);

                return redirect()->to('/contracts/view/' . $idContrato)
                               ->with('success', 'Contrato generado y enviado exitosamente a diana.cuestas@cycloidtalent.com y edison.cuervo@cycloidtalent.com');
            } else {
                return redirect()->to('/contracts/view/' . $idContrato)
                               ->with('warning', 'Contrato generado correctamente, pero hubo un error al enviar el email. Puede descargarlo manualmente.');
            }

        } catch (\Exception $e) {
            log_message('error', 'Error generando contrato PDF: ' . $e->getMessage());
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error al generar el PDF: ' . $e->getMessage());
        }
    }

    /**
     * Envía el contrato por email usando SendGrid
     */
    private function sendContractEmail($contract, $filePath, $fileName)
    {
        try {
            $email = new Mail();
            $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "Cycloid Talent");
            $email->setSubject("Nuevo Contrato Generado - " . $contract['numero_contrato']);
            $email->addTo("diana.cuestas@cycloidtalent.com", "Diana Cuestas");
            $email->addTo("edison.cuervo@cycloidtalent.com", "Edison Cuervo");

            // Cuerpo del email en HTML
            $htmlContent = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <h2 style='color: #667eea;'>Contrato Generado Exitosamente</h2>

                    <p>Se ha generado un nuevo contrato con los siguientes datos:</p>

                    <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                        <tr>
                            <td style='padding: 8px; background-color: #f5f5f5; font-weight: bold;'>Número de Contrato:</td>
                            <td style='padding: 8px;'>" . htmlspecialchars($contract['numero_contrato']) . "</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px; background-color: #f5f5f5; font-weight: bold;'>Cliente:</td>
                            <td style='padding: 8px;'>" . htmlspecialchars($contract['nombre_cliente']) . "</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px; background-color: #f5f5f5; font-weight: bold;'>NIT:</td>
                            <td style='padding: 8px;'>" . htmlspecialchars($contract['nit_cliente']) . "</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px; background-color: #f5f5f5; font-weight: bold;'>Fecha de Inicio:</td>
                            <td style='padding: 8px;'>" . date('d/m/Y', strtotime($contract['fecha_inicio'])) . "</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px; background-color: #f5f5f5; font-weight: bold;'>Fecha de Finalización:</td>
                            <td style='padding: 8px;'>" . date('d/m/Y', strtotime($contract['fecha_fin'])) . "</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px; background-color: #f5f5f5; font-weight: bold;'>Valor del Contrato:</td>
                            <td style='padding: 8px;'>$" . number_format($contract['valor_contrato'], 0, ',', '.') . " COP</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px; background-color: #f5f5f5; font-weight: bold;'>Responsable SG-SST:</td>
                            <td style='padding: 8px;'>" . htmlspecialchars($contract['nombre_responsable_sgsst']) . "</td>
                        </tr>
                    </table>

                    <p>El contrato PDF se encuentra adjunto a este correo.</p>

                    <p style='color: #666; font-size: 12px; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 15px;'>
                        Este es un mensaje automático del sistema de gestión de contratos de Cycloid Talent.<br>
                        Generado el " . date('d/m/Y H:i:s') . "
                    </p>
                </div>
            ";

            $email->addContent("text/html", $htmlContent);

            // Adjuntar el PDF
            $fileData = base64_encode(file_get_contents($filePath));
            $email->addAttachment($fileData, "application/pdf", $fileName, "attachment");

            // Enviar con SendGrid
            $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
            $response = $sendgrid->send($email);

            // Verificar que se envió correctamente (código 202 = aceptado)
            if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                log_message('info', 'Contrato enviado por email exitosamente. Código: ' . $response->statusCode());
                return true;
            } else {
                log_message('error', 'Error al enviar email. Código: ' . $response->statusCode() . ' Body: ' . $response->body());
                return false;
            }

        } catch (\Exception $e) {
            log_message('error', 'Excepción al enviar email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Descarga el PDF del contrato generado
     */
    public function downloadPDF($idContrato)
    {
        $contract = $this->contractModel->find($idContrato);

        if (!$contract || !$contract['ruta_pdf_contrato']) {
            return redirect()->to('/contracts/view/' . $idContrato)
                           ->with('error', 'PDF no disponible');
        }

        // Verificar permisos
        $session = session();
        $role = $session->get('role');
        $idConsultor = $session->get('id_consultor');

        if ($role === 'consultor') {
            $client = $this->clientModel->find($contract['id_cliente']);
            if ($client['id_consultor'] != $idConsultor) {
                return redirect()->to('/contracts')->with('error', 'No tiene permisos');
            }
        }

        $filePath = FCPATH . $contract['ruta_pdf_contrato'];

        if (!file_exists($filePath)) {
            return redirect()->to('/contracts/view/' . $idContrato)
                           ->with('error', 'El archivo PDF no existe en el servidor');
        }

        // Descargar el archivo
        return $this->response->download($filePath, null)->setFileName(basename($filePath));
    }

    /**
     * Genera la cláusula cuarta usando OpenAI
     */
    public function generateClausulaIA()
    {
        // Verificar que sea una petición AJAX
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Petición no válida'
            ])->setStatusCode(400);
        }

        $instrucciones = $this->request->getPost('instrucciones');
        $nombreCliente = $this->request->getPost('nombre_cliente');
        $fechaInicio = $this->request->getPost('fecha_inicio');
        $fechaFin = $this->request->getPost('fecha_fin');
        $valorContrato = $this->request->getPost('valor_contrato');
        $tipoContrato = $this->request->getPost('tipo_contrato');

        if (empty($instrucciones)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Por favor ingrese las instrucciones para generar la cláusula'
            ]);
        }

        // Obtener API key de OpenAI
        $apiKey = getenv('OPENAI_API_KEY');

        if (empty($apiKey)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'API key de OpenAI no configurada'
            ]);
        }

        // Construir el prompt del sistema
        $systemPrompt = "Eres un experto en redacción de contratos de servicios de Seguridad y Salud en el Trabajo (SG-SST) en Colombia.
Tu tarea es redactar la CLÁUSULA CUARTA de un contrato de prestación de servicios.

La cláusula debe incluir:
1. CUARTA-PLAZO DE EJECUCIÓN: El plazo para la ejecución de actividades
2. CUARTA-DURACIÓN: La duración total del contrato
3. PARÁGRAFO PRIMERO: Condiciones de terminación anticipada
4. PARÁGRAFO SEGUNDO: Condiciones sobre prórroga automática

Usa un lenguaje formal y legal apropiado para contratos en Colombia.
NO incluyas saludos ni explicaciones, solo el texto de la cláusula.";

        // Construir el prompt del usuario
        $userPrompt = "Genera la CLÁUSULA CUARTA para un contrato con los siguientes datos:

DATOS DEL CONTRATO:
- Cliente: " . ($nombreCliente ?: 'Por definir') . "
- Fecha de inicio: " . ($fechaInicio ?: 'Por definir') . "
- Fecha de finalización: " . ($fechaFin ?: 'Por definir') . "
- Valor del contrato: $" . ($valorContrato ? number_format($valorContrato, 0, ',', '.') : 'Por definir') . " COP
- Tipo de contrato: " . ($tipoContrato ?: 'inicial') . "

INSTRUCCIONES ESPECÍFICAS DEL VENDEDOR:
" . $instrucciones . "

Genera únicamente el texto de la cláusula, listo para insertar en el contrato.";

        try {
            // Llamar a la API de OpenAI
            $ch = curl_init('https://api.openai.com/v1/chat/completions');

            $payload = [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt]
                ],
                'temperature' => 0.7,
                'max_tokens' => 1500
            ];

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $apiKey
                ],
                CURLOPT_TIMEOUT => 60
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                log_message('error', 'Error cURL OpenAI: ' . $curlError);
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error de conexión: ' . $curlError
                ]);
            }

            $data = json_decode($response, true);

            if ($httpCode !== 200) {
                $errorMsg = $data['error']['message'] ?? 'Error desconocido de OpenAI';
                log_message('error', 'Error OpenAI API: ' . $errorMsg);
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error de OpenAI: ' . $errorMsg
                ]);
            }

            $clausulaGenerada = $data['choices'][0]['message']['content'] ?? '';

            if (empty($clausulaGenerada)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No se pudo generar la cláusula'
                ]);
            }

            // Log de uso de tokens para monitoreo
            $tokensUsados = $data['usage']['total_tokens'] ?? 0;
            log_message('info', 'OpenAI - Cláusula generada. Tokens usados: ' . $tokensUsados);

            return $this->response->setJSON([
                'success' => true,
                'clausula' => $clausulaGenerada,
                'tokens_usados' => $tokensUsados
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Excepción al llamar OpenAI: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al generar la cláusula: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Cron: Envía reporte semanal de contratos vencidos y próximos a vencer
     * Endpoint: GET /contracts/weekly-report?token=CRON_TOKEN
     */
    public function sendWeeklyContractReport()
    {
        // Verificar acceso: CLI o token de seguridad
        if (!is_cli()) {
            $token = $this->request->getGet('token');
            $expectedToken = env('CRON_TOKEN', 'changeme');

            if ($token !== $expectedToken) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No autorizado'
                ])->setStatusCode(401);
            }
        }

        // Obtener contratos vencidos (activos con fecha_fin pasada)
        $expiredContracts = $this->contractModel->getExpiredActiveContracts();

        // Obtener contratos próximos a vencer en 30 días
        $expiringContracts = $this->contractModel->getExpiringContracts(30);

        // Si no hay contratos en ninguna categoría, no enviar email
        if (empty($expiredContracts) && empty($expiringContracts)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'No hay contratos vencidos ni próximos a vencer. No se envió email.',
                'expired_count' => 0,
                'expiring_count' => 0
            ]);
        }

        // Construir HTML del email
        $htmlContent = $this->buildWeeklyReportHtml($expiredContracts, $expiringContracts);

        // Enviar email vía SendGrid
        try {
            $email = new Mail();
            $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "Cycloid Talent");
            $email->setSubject("Reporte Semanal de Contratos - " . date('d/m/Y'));
            $email->addTo("diana.cuestas@cycloidtalent.com", "Diana Cuestas");
            $email->addContent("text/html", $htmlContent);

            $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
            $response = $sendgrid->send($email);

            if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                log_message('info', 'Reporte semanal de contratos enviado exitosamente. Código: ' . $response->statusCode());
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Reporte semanal enviado exitosamente',
                    'expired_count' => count($expiredContracts),
                    'expiring_count' => count($expiringContracts)
                ]);
            } else {
                log_message('error', 'Error al enviar reporte semanal. Código: ' . $response->statusCode() . ' Body: ' . $response->body());
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error al enviar el email. Código: ' . $response->statusCode()
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Excepción al enviar reporte semanal: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al enviar el reporte: ' . $e->getMessage()
            ]);
        }
    }

    // =========================================================================
    // FIRMA DIGITAL DE CONTRATOS (Sistema independiente)
    // =========================================================================

    /**
     * Envía solicitud de firma digital al representante legal del cliente
     */
    public function enviarFirma()
    {
        // Soportar tanto JSON (fetch) como form POST
        $json = $this->request->getJSON(true);
        $idContrato = $json['id_contrato'] ?? $this->request->getPost('id_contrato');
        $isAjax = $this->request->isAJAX() || $this->request->getHeaderLine('Content-Type') === 'application/json';

        $contract = $this->contractLibrary->getContractWithClient($idContrato);
        if (!$contract) {
            if ($isAjax) {
                return $this->response->setJSON(['success' => false, 'message' => 'Contrato no encontrado']);
            }
            return redirect()->to('/contracts')->with('error', 'Contrato no encontrado');
        }

        // Validar que tenga PDF generado
        if (empty($contract['contrato_generado'])) {
            $msg = 'Debe generar el PDF del contrato antes de enviarlo a firmar';
            if ($isAjax) {
                return $this->response->setJSON(['success' => false, 'message' => $msg]);
            }
            return redirect()->to('/contracts/view/' . $idContrato)->with('error', $msg);
        }

        // Validar que no esté ya firmado
        if (($contract['estado_firma'] ?? '') === 'firmado') {
            $msg = 'Este contrato ya fue firmado';
            if ($isAjax) {
                return $this->response->setJSON(['success' => false, 'message' => $msg]);
            }
            return redirect()->to('/contracts/view/' . $idContrato)->with('error', $msg);
        }

        // Validar email del cliente
        $emailCliente = $contract['email_cliente'] ?? '';
        if (empty($emailCliente)) {
            $msg = 'El contrato no tiene email del representante legal del cliente';
            if ($isAjax) {
                return $this->response->setJSON(['success' => false, 'message' => $msg]);
            }
            return redirect()->to('/contracts/view/' . $idContrato)->with('error', $msg);
        }

        // Generar token
        $token = bin2hex(random_bytes(32));
        $expiracion = date('Y-m-d H:i:s', strtotime('+7 days'));

        // Actualizar contrato
        $this->contractModel->update($idContrato, [
            'token_firma' => $token,
            'token_firma_expiracion' => $expiracion,
            'estado_firma' => 'pendiente_firma'
        ]);

        // URL de firma
        $urlFirma = base_url("contrato/firmar/{$token}");
        $nombreFirmante = $contract['nombre_rep_legal_cliente'] ?? 'Representante Legal';

        // Enviar email principal al representante legal del cliente
        $enviado = $this->enviarEmailFirmaContrato(
            $emailCliente,
            $nombreFirmante,
            $contract,
            $urlFirma,
            'Se requiere su firma digital para el contrato de prestacion de servicios SST.',
            false
        );

        if (!$enviado) {
            // Revertir si falla el envío
            $this->contractModel->update($idContrato, [
                'token_firma' => null,
                'token_firma_expiracion' => null,
                'estado_firma' => 'sin_enviar'
            ]);
            $msg = 'Error al enviar el correo. Verifique la configuracion de SendGrid.';
            if ($isAjax) {
                return $this->response->setJSON(['success' => false, 'message' => $msg]);
            }
            return redirect()->to('/contracts/view/' . $idContrato)->with('error', $msg);
        }

        // Enviar copia informativa al responsable SG-SST si tiene email
        $emailResponsable = $contract['email_responsable_sgsst'] ?? '';
        if (!empty($emailResponsable) && $emailResponsable !== $emailCliente) {
            $this->enviarEmailFirmaContrato(
                $emailResponsable,
                $contract['nombre_responsable_sgsst'] ?? 'Responsable SST',
                $contract,
                $urlFirma,
                'El Representante Legal debe firmar este contrato. Se le envia copia informativa.',
                true
            );
        }

        $msg = 'Solicitud de firma enviada correctamente a ' . $emailCliente;
        if ($isAjax) {
            return $this->response->setJSON(['success' => true, 'message' => $msg]);
        }
        return redirect()->to('/contracts/view/' . $idContrato)->with('success', $msg);
    }

    /**
     * Página pública de firma del contrato (sin auth)
     */
    public function paginaFirmaContrato($token)
    {
        $db = \Config\Database::connect();

        $contrato = $db->table('tbl_contratos')
            ->select('tbl_contratos.*, tbl_clientes.nombre_cliente, tbl_clientes.nit_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_contratos.id_cliente')
            ->where('tbl_contratos.token_firma', $token)
            ->get()->getRowArray();

        if (!$contrato) {
            return view('contracts/firma_error_contrato', [
                'mensaje' => 'El enlace de firma no es valido o ya fue utilizado.'
            ]);
        }

        // Verificar estado
        $estadoFirma = $contrato['estado_firma'] ?? '';
        if ($estadoFirma === 'firmado') {
            return view('contracts/firma_error_contrato', [
                'mensaje' => 'Este contrato ya fue firmado anteriormente.'
            ]);
        }

        if ($estadoFirma !== 'pendiente_firma') {
            return view('contracts/firma_error_contrato', [
                'mensaje' => 'Este contrato no esta disponible para firma.'
            ]);
        }

        // Verificar expiración
        if (!empty($contrato['token_firma_expiracion']) && strtotime($contrato['token_firma_expiracion']) < time()) {
            return view('contracts/firma_error_contrato', [
                'mensaje' => 'El enlace de firma ha expirado. Solicite un nuevo enlace.'
            ]);
        }

        // URL pública del PDF para visor embebido
        $pdfUrl = '';
        if (!empty($contrato['ruta_pdf_contrato'])) {
            $pdfUrl = base_url($contrato['ruta_pdf_contrato']);
        }

        return view('contracts/contrato_firma', [
            'contrato' => $contrato,
            'token' => $token,
            'pdfUrl' => $pdfUrl
        ]);
    }

    /**
     * Procesar firma digital del contrato (público, sin auth)
     */
    public function procesarFirmaContrato()
    {
        $token = $this->request->getPost('token');
        $firmaNombre = $this->request->getPost('firma_nombre');
        $firmaCedula = $this->request->getPost('firma_cedula');
        $firmaImagen = $this->request->getPost('firma_imagen');

        $db = \Config\Database::connect();

        // Validar token
        $contrato = $db->table('tbl_contratos')
            ->where('token_firma', $token)
            ->where('estado_firma', 'pendiente_firma')
            ->get()->getRowArray();

        if (!$contrato) {
            return $this->response->setJSON(['success' => false, 'message' => 'Token no valido']);
        }

        // Verificar expiración
        if (!empty($contrato['token_firma_expiracion']) && strtotime($contrato['token_firma_expiracion']) < time()) {
            return $this->response->setJSON(['success' => false, 'message' => 'El enlace ha expirado']);
        }

        // Guardar imagen de firma
        $rutaFirma = null;
        if ($firmaImagen) {
            $firmaData = explode(',', $firmaImagen);
            $firmaDecoded = base64_decode(end($firmaData));
            $nombreArchivo = 'firma_contrato_' . $contrato['id_contrato'] . '_' . time() . '.png';
            $rutaFirma = 'uploads/firmas/' . $nombreArchivo;

            if (!is_dir(FCPATH . 'uploads/firmas')) {
                mkdir(FCPATH . 'uploads/firmas', 0755, true);
            }

            file_put_contents(FCPATH . $rutaFirma, $firmaDecoded);
        }

        // Generar código de verificación ANTES de anular el token
        $hash = hash('sha256', $contrato['token_firma'] . '|' . $contrato['id_contrato'] . '|' . $firmaCedula);
        $codigoVerificacion = strtoupper(substr($hash, 0, 12));

        // Actualizar contrato
        $db->table('tbl_contratos')
            ->where('id_contrato', $contrato['id_contrato'])
            ->update([
                'estado_firma' => 'firmado',
                'firma_cliente_nombre' => $firmaNombre,
                'firma_cliente_cedula' => $firmaCedula,
                'firma_cliente_imagen' => $rutaFirma,
                'firma_cliente_ip' => $this->request->getIPAddress(),
                'firma_cliente_fecha' => date('Y-m-d H:i:s'),
                'codigo_verificacion' => $codigoVerificacion,
                'token_firma' => null,
                'token_firma_expiracion' => null
            ]);

        // Regenerar el PDF del contrato para incluir la firma del cliente
        try {
            $contractData = $this->contractLibrary->getContractWithClient($contrato['id_contrato']);
            if ($contractData) {
                $pdfGenerator = new ContractPDFGenerator();
                $pdfGenerator->generateContract($contractData);

                $uploadDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'contratos' . DIRECTORY_SEPARATOR;
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0775, true);
                }

                $fileName = 'contrato_' . $contractData['numero_contrato'] . '_firmado_' . date('Ymd_His') . '.pdf';
                $filePath = realpath($uploadDir) . DIRECTORY_SEPARATOR . $fileName;
                $pdfGenerator->save($filePath);

                // Actualizar ruta del PDF firmado
                $db->table('tbl_contratos')
                    ->where('id_contrato', $contrato['id_contrato'])
                    ->update([
                        'ruta_pdf_contrato' => 'uploads/contratos/' . $fileName,
                        'fecha_generacion_contrato' => date('Y-m-d H:i:s')
                    ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error regenerando PDF con firma: ' . $e->getMessage());
        }

        // Enviar credenciales al cliente si no tiene usuario en tbl_usuarios
        try {
            $cliente = $db->table('tbl_clientes')
                ->where('id_cliente', $contrato['id_cliente'])
                ->get()->getRowArray();

            if ($cliente && !empty($cliente['correo_cliente'])) {
                $userModel = new UserModel();
                $existeUsuario = $userModel->where('id_entidad', $contrato['id_cliente'])
                    ->where('tipo_usuario', 'client')
                    ->first();

                if (!$existeUsuario) {
                    // Verificar que el email no esté ya registrado
                    $emailExiste = $userModel->findByEmail($cliente['correo_cliente']);
                    if (!$emailExiste) {
                        $tempPassword = 'Ent' . rand(10000, 99999) . '!';
                        $userId = $userModel->createUser([
                            'email' => $cliente['correo_cliente'],
                            'password' => $tempPassword,
                            'nombre_completo' => $cliente['nombre_cliente'],
                            'tipo_usuario' => 'client',
                            'id_entidad' => $contrato['id_cliente'],
                            'estado' => 'activo',
                        ]);

                        if ($userId) {
                            $this->enviarEmailCredenciales(
                                $cliente['correo_cliente'],
                                $cliente['nombre_cliente'],
                                $tempPassword
                            );
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Error creando credenciales del cliente: ' . $e->getMessage());
        }

        // Notificar al equipo interno y al consultor que el contrato fue firmado
        try {
            $contractData = $contractData ?? $this->contractLibrary->getContractWithClient($contrato['id_contrato']);
            if ($contractData) {
                $this->enviarEmailNotificacionFirma($contractData, $firmaNombre, $firmaCedula);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error enviando notificacion de firma: ' . $e->getMessage());
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Contrato firmado correctamente'
        ]);
    }

    /**
     * Regenerar el PDF del contrato incluyendo la firma del cliente
     * POST /contracts/regenerar-pdf-firmado
     */
    public function regenerarPDFFirmado()
    {
        $json = $this->request->getJSON(true);
        $idContrato = $json['id_contrato'] ?? $this->request->getPost('id_contrato');

        $contract = $this->contractLibrary->getContractWithClient($idContrato);
        if (!$contract) {
            return $this->response->setJSON(['success' => false, 'message' => 'Contrato no encontrado']);
        }

        if (($contract['estado_firma'] ?? '') !== 'firmado') {
            return $this->response->setJSON(['success' => false, 'message' => 'El contrato aún no ha sido firmado']);
        }

        try {
            $pdfGenerator = new ContractPDFGenerator();
            $pdfGenerator->generateContract($contract);

            $uploadDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'contratos' . DIRECTORY_SEPARATOR;
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }

            $fileName = 'contrato_' . $contract['numero_contrato'] . '_firmado_' . date('Ymd_His') . '.pdf';
            $filePath = realpath($uploadDir) . DIRECTORY_SEPARATOR . $fileName;
            $pdfGenerator->save($filePath);

            $this->contractModel->update($idContrato, [
                'ruta_pdf_contrato' => 'uploads/contratos/' . $fileName,
                'fecha_generacion_contrato' => date('Y-m-d H:i:s')
            ]);

            return $this->response->setJSON(['success' => true, 'message' => 'PDF regenerado con la firma del cliente']);
        } catch (\Exception $e) {
            log_message('error', 'Error regenerando PDF firmado: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Error al regenerar el PDF: ' . $e->getMessage()]);
        }
    }

    /**
     * Consultar estado de firma de un contrato (autenticado)
     */
    public function estadoFirma($idContrato)
    {
        $contract = $this->contractModel->find($idContrato);

        if (!$contract) {
            return $this->response->setJSON(['success' => false, 'message' => 'Contrato no encontrado']);
        }

        $data = [
            'success' => true,
            'estado_firma' => $contract['estado_firma'] ?? 'sin_enviar',
        ];

        if (($contract['estado_firma'] ?? '') === 'firmado') {
            $data['firma'] = [
                'nombre' => $contract['firma_cliente_nombre'],
                'cedula' => $contract['firma_cliente_cedula'],
                'fecha' => $contract['firma_cliente_fecha'],
                'ip' => $contract['firma_cliente_ip'],
            ];
        }

        return $this->response->setJSON($data);
    }

    /**
     * Envía email de solicitud de firma de contrato via SendGrid
     */
    private function enviarEmailFirmaContrato($email, $nombreFirmante, $contrato, $urlFirma, $mensaje, $esCopia = false)
    {
        $apiKey = env('SENDGRID_API_KEY');
        if (empty($apiKey)) {
            log_message('error', 'SENDGRID_API_KEY no configurada');
            return false;
        }

        // Renderizar template de email
        $htmlEmail = view('contracts/email_contrato_firma', [
            'nombreFirmante' => $nombreFirmante,
            'contrato' => $contrato,
            'urlFirma' => $urlFirma,
            'mensaje' => $mensaje,
            'esCopia' => $esCopia
        ]);

        $subject = $esCopia
            ? "[Copia] Solicitud de Firma: Contrato SST - {$contrato['nombre_cliente']}"
            : "Solicitud de Firma: Contrato SST - {$contrato['nombre_cliente']}";

        $fromEmail = env('SENDGRID_FROM_EMAIL', 'notificacion.cycloidtalent@cycloidtalent.com');
        $fromName = env('SENDGRID_FROM_NAME', 'Enterprise SST');

        $data = [
            'personalizations' => [
                [
                    'to' => [['email' => $email, 'name' => $nombreFirmante]],
                    'subject' => $subject
                ]
            ],
            'from' => [
                'email' => $fromEmail,
                'name' => $fromName
            ],
            'content' => [
                ['type' => 'text/html', 'value' => $htmlEmail]
            ]
        ];

        $ch = curl_init('https://api.sendgrid.com/v3/mail/send');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode < 200 || $httpCode >= 300) {
            log_message('error', "SendGrid Error (contrato firma) - HTTP {$httpCode}: {$response} | cURL: {$curlError}");
        }

        return $httpCode >= 200 && $httpCode < 300;
    }

    /**
     * Enviar email con credenciales de acceso a la plataforma al cliente
     */
    private function enviarEmailCredenciales($email, $nombre, $password)
    {
        $apiKey = env('SENDGRID_API_KEY');
        if (empty($apiKey)) {
            return false;
        }

        $loginUrl = base_url('/login');

        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head>
        <body style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px;">
        <div style="max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
            <div style="background: linear-gradient(135deg, #28a745 0%, #218838 100%); padding: 30px; text-align: center;">
                <h1 style="color: white; margin: 0; font-size: 22px;">Bienvenido a EnterpriseSST</h1>
                <p style="color: rgba(255,255,255,0.8); margin: 10px 0 0;">Su contrato ha sido firmado exitosamente</p>
            </div>
            <div style="padding: 30px;">
                <p style="color: #333; font-size: 16px;">Estimado(a) <strong>' . htmlspecialchars($nombre) . '</strong>,</p>
                <p style="color: #555;">Su contrato de prestacion de servicios SST ha sido firmado correctamente. A continuacion encontrara sus credenciales de acceso a la plataforma <strong>EnterpriseSST</strong>, donde podra consultar toda la documentacion, descargar el contrato firmado en PDF y hacer seguimiento a su sistema de gestion.</p>

                <div style="background: #f8f9fa; border-radius: 8px; padding: 20px; margin: 20px 0; text-align: center;">
                    <h3 style="color: #28a745; margin-top: 0;">Sus Credenciales de Acceso</h3>
                    <table style="margin: 0 auto; text-align: left;">
                        <tr>
                            <td style="padding: 8px 15px; color: #666; font-weight: bold;">Correo:</td>
                            <td style="padding: 8px 15px; color: #333; font-size: 16px;">' . htmlspecialchars($email) . '</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 15px; color: #666; font-weight: bold;">Contrasena:</td>
                            <td style="padding: 8px 15px;"><span style="font-size: 20px; font-weight: bold; color: #28a745; letter-spacing: 2px;">' . htmlspecialchars($password) . '</span></td>
                        </tr>
                    </table>
                </div>

                <div style="text-align: center; margin: 30px 0;">
                    <a href="' . $loginUrl . '" style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 40px; text-decoration: none; border-radius: 8px; font-size: 16px; font-weight: bold;">Ingresar a la Plataforma</a>
                </div>

                <div style="background: #fff3cd; border-radius: 8px; padding: 15px; margin: 20px 0;">
                    <p style="color: #856404; margin: 0; font-size: 13px;"><strong>Importante:</strong> Por seguridad, le recomendamos cambiar su contrasena despues del primer inicio de sesion.</p>
                </div>
            </div>
            <div style="background: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #eee;">
                <p style="color: #999; font-size: 12px; margin: 0;">Enterprise SST - Sistema de Gestion de Seguridad y Salud en el Trabajo<br>Este es un mensaje automatico, por favor no responda a este correo.</p>
            </div>
        </div></body></html>';

        $data = [
            'personalizations' => [
                [
                    'to' => [['email' => $email, 'name' => $nombre]],
                    'subject' => 'Bienvenido a EnterpriseSST - Sus Credenciales de Acceso'
                ]
            ],
            'from' => [
                'email' => env('SENDGRID_FROM_EMAIL', 'notificacion.cycloidtalent@cycloidtalent.com'),
                'name' => env('SENDGRID_FROM_NAME', 'Enterprise SST')
            ],
            'content' => [
                ['type' => 'text/html', 'value' => $html]
            ]
        ];

        $ch = curl_init('https://api.sendgrid.com/v3/mail/send');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode < 200 || $httpCode >= 300) {
            log_message('error', "SendGrid Error (credenciales cliente) - HTTP {$httpCode}: {$response} | cURL: {$curlError}");
        }

        return $httpCode >= 200 && $httpCode < 300;
    }

    // =========================================================================
    // VERIFICACIÓN PÚBLICA Y CERTIFICADO PDF DE CONTRATOS
    // =========================================================================

    /**
     * Verificación pública de contrato firmado
     * GET /contrato/verificar/{codigo} — Pública, sin auth
     */
    public function verificarFirma($codigoVerificacion)
    {
        $contrato = $this->contractModel
            ->where('codigo_verificacion', strtoupper($codigoVerificacion))
            ->where('estado_firma', 'firmado')
            ->first();

        if (!$contrato) {
            return view('contracts/verificacion_contrato', ['valido' => false]);
        }

        $cliente = $this->clientModel->find($contrato['id_cliente']);

        $urlVerificacion = base_url("contrato/verificar/{$contrato['codigo_verificacion']}");
        $qrImage = $this->generarQRContrato($urlVerificacion);

        return view('contracts/verificacion_contrato', [
            'valido'             => true,
            'contrato'           => $contrato,
            'cliente'            => $cliente,
            'codigoVerificacion' => $contrato['codigo_verificacion'],
            'qrImage'            => $qrImage,
        ]);
    }

    /**
     * Descargar Certificado PDF de firma del contrato
     * GET /contrato/certificado-pdf/{id} — Pública
     */
    public function certificadoPDF($idContrato)
    {
        $contrato = $this->contractModel->find($idContrato);

        if (!$contrato || ($contrato['estado_firma'] ?? '') !== 'firmado') {
            return redirect()->back()->with('error', 'Contrato no encontrado o no firmado');
        }

        $cliente = $this->clientModel->find($contrato['id_cliente']);

        $urlVerificacion = base_url("contrato/verificar/{$contrato['codigo_verificacion']}");
        $qrImage = $this->generarQRContrato($urlVerificacion);

        $html = view('contracts/certificado_pdf_contrato', [
            'contrato'           => $contrato,
            'cliente'            => $cliente,
            'codigoVerificacion' => $contrato['codigo_verificacion'],
            'qrImage'            => $qrImage,
        ]);

        $dompdf = new \Dompdf\Dompdf(['isRemoteEnabled' => true]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $filename = "Certificado_Firma_Contrato_{$contrato['numero_contrato']}_{$contrato['codigo_verificacion']}.pdf";
        $dompdf->stream($filename, ['Attachment' => true]);
        exit;
    }

    /**
     * Notifica al equipo interno y al consultor cuando se firma un contrato
     */
    private function enviarEmailNotificacionFirma($contrato, $firmaNombre, $firmaCedula)
    {
        $apiKey = env('SENDGRID_API_KEY');
        if (empty($apiKey)) {
            return;
        }

        $urlContrato = base_url('contracts/view/' . $contrato['id_contrato']);
        $fechaFirma  = date('d/m/Y H:i:s');

        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head>
        <body style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px;">
        <div style="max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
            <div style="background: linear-gradient(135deg, #28a745 0%, #218838 100%); padding: 25px; text-align: center;">
                <h2 style="color: white; margin: 0; font-size: 20px;">&#10003; Contrato Firmado Digitalmente</h2>
                <p style="color: rgba(255,255,255,0.85); margin: 8px 0 0; font-size: 14px;">Notificacion automatica del sistema EnterpriseSST</p>
            </div>
            <div style="padding: 30px;">
                <p style="color: #333; font-size: 15px; margin-top: 0;">El siguiente contrato ha sido firmado digitalmente por el representante legal del cliente:</p>

                <table style="width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 14px;">
                    <tr style="background: #f8f9fa;">
                        <td style="padding: 10px 15px; font-weight: bold; color: #555; width: 40%;">Numero de Contrato:</td>
                        <td style="padding: 10px 15px; color: #333;">' . htmlspecialchars($contrato['numero_contrato']) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 15px; font-weight: bold; color: #555;">Cliente:</td>
                        <td style="padding: 10px 15px; color: #333;">' . htmlspecialchars($contrato['nombre_cliente']) . '</td>
                    </tr>
                    <tr style="background: #f8f9fa;">
                        <td style="padding: 10px 15px; font-weight: bold; color: #555;">Firmado por:</td>
                        <td style="padding: 10px 15px; color: #333;">' . htmlspecialchars($firmaNombre) . ' (CC: ' . htmlspecialchars($firmaCedula) . ')</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 15px; font-weight: bold; color: #555;">Fecha y Hora:</td>
                        <td style="padding: 10px 15px; color: #333;">' . $fechaFirma . '</td>
                    </tr>
                    <tr style="background: #f8f9fa;">
                        <td style="padding: 10px 15px; font-weight: bold; color: #555;">Vigencia:</td>
                        <td style="padding: 10px 15px; color: #333;">' . date('d/m/Y', strtotime($contrato['fecha_inicio'])) . ' al ' . date('d/m/Y', strtotime($contrato['fecha_fin'])) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 15px; font-weight: bold; color: #555;">Valor:</td>
                        <td style="padding: 10px 15px; color: #333;">$' . number_format($contrato['valor_contrato'], 0, ',', '.') . ' COP</td>
                    </tr>
                    <tr style="background: #f8f9fa;">
                        <td style="padding: 10px 15px; font-weight: bold; color: #555;">Consultor:</td>
                        <td style="padding: 10px 15px; color: #333;">' . htmlspecialchars($contrato['nombre_consultor'] ?? 'No asignado') . '</td>
                    </tr>
                </table>

                <div style="text-align: center; margin: 25px 0;">
                    <a href="' . $urlContrato . '" style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px 35px; text-decoration: none; border-radius: 8px; font-size: 15px; font-weight: bold;">Ver Contrato en el Sistema</a>
                </div>
            </div>
            <div style="background: #f8f9fa; padding: 15px; text-align: center; border-top: 1px solid #eee;">
                <p style="color: #999; font-size: 12px; margin: 0;">EnterpriseSST - Notificacion automatica. No responda este correo.</p>
            </div>
        </div></body></html>';

        // Construir lista de destinatarios
        $destinatarios = [
            ['email' => 'diana.cuestas@cycloidtalent.com', 'name' => 'Diana Cuestas']
        ];

        // Agregar consultor si tiene email registrado
        if (!empty($contrato['id_consultor_responsable'])) {
            $consultorModel = new \App\Models\ConsultorModel();
            $consultor = $consultorModel->find($contrato['id_consultor_responsable']);
            if ($consultor && !empty($consultor['correo_consultor'])) {
                $destinatarios[] = [
                    'email' => $consultor['correo_consultor'],
                    'name'  => $consultor['nombre_consultor'] ?? 'Consultor'
                ];
            }
        }

        $toList = array_map(fn($d) => ['email' => $d['email'], 'name' => $d['name']], $destinatarios);

        $payload = [
            'personalizations' => [[
                'to'      => $toList,
                'subject' => 'Contrato Firmado: ' . $contrato['numero_contrato'] . ' - ' . $contrato['nombre_cliente']
            ]],
            'from'    => [
                'email' => env('SENDGRID_FROM_EMAIL', 'notificacion.cycloidtalent@cycloidtalent.com'),
                'name'  => env('SENDGRID_FROM_NAME', 'Enterprise SST')
            ],
            'content' => [['type' => 'text/html', 'value' => $html]]
        ];

        $ch = curl_init('https://api.sendgrid.com/v3/mail/send');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode < 200 || $httpCode >= 300) {
            log_message('error', "SendGrid Error (notificacion firma) - HTTP {$httpCode}: {$response} | cURL: {$curlError}");
        }
    }

    /**
     * Genera URL de QR code para verificación de contratos
     */
    private function generarQRContrato(string $url): string
    {
        return 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($url);
    }

    // =========================================================================
    // GENERACIÓN DE CLÁUSULAS CON IA (OpenAI via IADocumentacionService)
    // =========================================================================

    /**
     * Genera Clausula Cuarta (Duracion) con IA
     * POST /contracts/generar-clausula-ia
     */
    public function generarClausulaIA()
    {
        $json = $this->request->getJSON(true);

        $idCliente = $json['id_cliente'] ?? null;
        $plazoEjecucion = $json['plazo_ejecucion'] ?? '';
        $duracionContrato = $json['duracion_contrato'] ?? '';
        $fechaInicio = $json['fecha_inicio'] ?? '';
        $fechaFin = $json['fecha_fin'] ?? '';
        $porcentajeAnticipo = $json['porcentaje_anticipo'] ?? '';
        $condicionesPago = $json['condiciones_pago'] ?? '';
        $terminacionAnticipada = $json['terminacion_anticipada'] ?? '';
        $obligacionesEspeciales = $json['obligaciones_especiales'] ?? '';
        $contextoAdicional = $json['contexto_adicional'] ?? '';
        $textoActual = $json['texto_actual'] ?? '';
        $modoRefinamiento = $json['modo_refinamiento'] ?? false;

        $fechaInicioF = $fechaInicio ? $this->formatearFechaLarga($fechaInicio) : '';
        $fechaFinF = $fechaFin ? $this->formatearFechaLarga($fechaFin) : '';

        $infoCliente = '';
        if ($idCliente) {
            $client = $this->clientModel->find($idCliente);
            if ($client) {
                $infoCliente = "Datos del cliente (EL CONTRATANTE): {$client['nombre_cliente']}, NIT: {$client['nit_cliente']}.";
            }
        }

        if ($modoRefinamiento && !empty($textoActual)) {
            $prompt = "Eres un abogado experto en contratos SST en Colombia.\n\n" .
                "Tienes este texto de Clausula Cuarta:\n\n--- TEXTO ACTUAL ---\n{$textoActual}\n--- FIN ---\n\n" .
                "Aplica estas modificaciones:\n{$contextoAdicional}\n\n" .
                ($infoCliente ? $infoCliente . "\n\n" : "") .
                "REGLAS: Partes en MAYUSCULAS (EL CONTRATANTE, EL CONTRATISTA). Fechas reales, nunca placeholders.\n" .
                "Responde SOLO con el texto de la clausula.";
        } else {
            $acuerdos = [];
            if ($plazoEjecucion) $acuerdos[] = "Plazo: {$plazoEjecucion}";
            if ($duracionContrato) $acuerdos[] = "Duracion: {$duracionContrato}";
            if ($fechaInicioF) $acuerdos[] = "Inicio: {$fechaInicioF}";
            if ($fechaFinF) $acuerdos[] = "Fin: {$fechaFinF}";
            if ($porcentajeAnticipo) $acuerdos[] = "Anticipo: {$porcentajeAnticipo}";
            if ($condicionesPago) $acuerdos[] = "Pago: {$condicionesPago}";
            if ($terminacionAnticipada) $acuerdos[] = "Terminacion anticipada: {$terminacionAnticipada}";
            if ($obligacionesEspeciales) $acuerdos[] = "Obligaciones: {$obligacionesEspeciales}";

            $prompt = "Eres un abogado experto en contratos de prestacion de servicios SST en Colombia.\n\n" .
                "Genera la CLAUSULA CUARTA (Duracion y Plazo) con estos acuerdos:\n\n" .
                ($infoCliente ? $infoCliente . "\n\n" : "") .
                implode("\n", $acuerdos) . "\n\n" .
                "Incluir: 1) Plazo de ejecucion 2) Duracion 3) PARAGRAFO PRIMERO (terminacion anticipada) 4) PARAGRAFO SEGUNDO (sin prorroga automatica)\n" .
                "REGLAS: Partes en MAYUSCULAS. Fechas reales en espanol largo. Lenguaje juridico formal.\n" .
                "Responde SOLO con el texto.";
        }

        try {
            $iaService = new \App\Services\IADocumentacionService();
            $texto = $iaService->generarContenido($prompt, 1500);
            return $this->response->setJSON(['success' => true, 'texto' => trim($texto)]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()])->setStatusCode(500);
        }
    }

    /**
     * Genera Clausula Primera (Objeto) con IA
     * POST /contracts/generar-clausula1-ia
     */
    public function generarClausula1IA()
    {
        $json = $this->request->getJSON(true);

        $idCliente = $json['id_cliente'] ?? null;
        $descripcion = $json['descripcion_servicio'] ?? 'Diseno e implementacion del SG-SST';
        $tipoConsultor = $json['tipo_consultor'] ?? 'externo';
        $nombre = $json['nombre_coordinador'] ?? '';
        $cedula = $json['cedula_coordinador'] ?? '';
        $licencia = $json['licencia_coordinador'] ?? '';
        $contexto = $json['contexto_adicional'] ?? '';
        $textoActual = $json['texto_actual'] ?? '';
        $modoRefinar = $json['modo_refinamiento'] ?? false;

        $infoCliente = '';
        if ($idCliente) {
            $client = $this->clientModel->find($idCliente);
            if ($client) $infoCliente = "Cliente: {$client['nombre_cliente']}, NIT: {$client['nit_cliente']}.";
        }

        $infoCoord = $nombre ? "Profesional SST: {$nombre}, cedula {$cedula}, licencia {$licencia}." : '';

        if ($modoRefinar && !empty($textoActual)) {
            $prompt = "Eres abogado experto SST Colombia.\n\nTexto actual:\n{$textoActual}\n\nModificaciones:\n{$contexto}\n\n{$infoCliente}\n{$infoCoord}\n\nRespuesta: solo texto clausula.";
        } else {
            $delegacion = $tipoConsultor === 'externo'
                ? "\nIMPORTANTE: Incluir parrafo de DELEGACION DE VISITAS (consultor externo puede delegar visitas a otros profesionales del equipo).\n"
                : '';
            $prompt = "Eres abogado experto SST Colombia.\n\nGenera CLAUSULA PRIMERA (Objeto) con:\n{$infoCliente}\nServicio: {$descripcion}\n{$infoCoord}\n{$delegacion}\n" .
                "Mencionar plataforma EnterpriseSST. Referenciar Resolucion 0312 de 2019.\n" .
                "Partes en MAYUSCULAS. Responde SOLO con texto.";
        }

        try {
            $iaService = new \App\Services\IADocumentacionService();
            $texto = $iaService->generarContenido($prompt, 1500);
            return $this->response->setJSON(['success' => true, 'texto' => trim($texto)]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()])->setStatusCode(500);
        }
    }

    /**
     * Formatea fecha a español largo (ej: "15 de febrero de 2026")
     */
    private function formatearFechaLarga(string $fecha): string
    {
        $meses = [1=>'enero',2=>'febrero',3=>'marzo',4=>'abril',5=>'mayo',6=>'junio',
                  7=>'julio',8=>'agosto',9=>'septiembre',10=>'octubre',11=>'noviembre',12=>'diciembre'];
        $ts = strtotime($fecha);
        if (!$ts) return $fecha;
        return (int)date('j',$ts) . ' de ' . $meses[(int)date('n',$ts)] . ' de ' . date('Y',$ts);
    }

    /**
     * Construye el HTML del reporte semanal de contratos
     */
    private function buildWeeklyReportHtml($expiredContracts, $expiringContracts)
    {
        $html = "
            <div style='font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto;'>
                <h2 style='color: #667eea;'>Reporte Semanal de Contratos</h2>
                <p style='color: #666;'>Generado el " . date('d/m/Y H:i:s') . "</p>
                <hr style='border: 1px solid #ddd;'>";

        // Sección: Contratos vencidos
        $html .= "<h3 style='color: #e53e3e; margin-top: 25px;'>Contratos Vencidos (" . count($expiredContracts) . ")</h3>";

        if (!empty($expiredContracts)) {
            $html .= "
                <table style='width: 100%; border-collapse: collapse; margin: 15px 0;'>
                    <thead>
                        <tr style='background-color: #e53e3e; color: white;'>
                            <th style='padding: 10px; text-align: left; border: 1px solid #ddd;'>Cliente</th>
                            <th style='padding: 10px; text-align: left; border: 1px solid #ddd;'>N° Contrato</th>
                            <th style='padding: 10px; text-align: center; border: 1px solid #ddd;'>Fecha Inicio</th>
                            <th style='padding: 10px; text-align: center; border: 1px solid #ddd;'>Fecha Fin</th>
                            <th style='padding: 10px; text-align: center; border: 1px solid #ddd;'>Días Vencido</th>
                            <th style='padding: 10px; text-align: right; border: 1px solid #ddd;'>Valor</th>
                        </tr>
                    </thead>
                    <tbody>";

            foreach ($expiredContracts as $contract) {
                $fechaFin = new \DateTime($contract['fecha_fin']);
                $hoy = new \DateTime();
                $diasVencido = $hoy->diff($fechaFin)->days;

                $html .= "
                        <tr>
                            <td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($contract['nombre_cliente']) . "</td>
                            <td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($contract['numero_contrato']) . "</td>
                            <td style='padding: 8px; text-align: center; border: 1px solid #ddd;'>" . date('d/m/Y', strtotime($contract['fecha_inicio'])) . "</td>
                            <td style='padding: 8px; text-align: center; border: 1px solid #ddd;'>" . date('d/m/Y', strtotime($contract['fecha_fin'])) . "</td>
                            <td style='padding: 8px; text-align: center; border: 1px solid #ddd; color: #e53e3e; font-weight: bold;'>" . $diasVencido . " días</td>
                            <td style='padding: 8px; text-align: right; border: 1px solid #ddd;'>$" . number_format($contract['valor_contrato'] ?? 0, 0, ',', '.') . "</td>
                        </tr>";
            }

            $html .= "</tbody></table>";
        } else {
            $html .= "<p style='color: #38a169;'>No hay contratos vencidos actualmente.</p>";
        }

        // Sección: Contratos próximos a vencer
        $html .= "<h3 style='color: #dd6b20; margin-top: 25px;'>Contratos Próximos a Vencer - 30 días (" . count($expiringContracts) . ")</h3>";

        if (!empty($expiringContracts)) {
            $html .= "
                <table style='width: 100%; border-collapse: collapse; margin: 15px 0;'>
                    <thead>
                        <tr style='background-color: #dd6b20; color: white;'>
                            <th style='padding: 10px; text-align: left; border: 1px solid #ddd;'>Cliente</th>
                            <th style='padding: 10px; text-align: left; border: 1px solid #ddd;'>N° Contrato</th>
                            <th style='padding: 10px; text-align: center; border: 1px solid #ddd;'>Fecha Inicio</th>
                            <th style='padding: 10px; text-align: center; border: 1px solid #ddd;'>Fecha Fin</th>
                            <th style='padding: 10px; text-align: center; border: 1px solid #ddd;'>Días Restantes</th>
                            <th style='padding: 10px; text-align: right; border: 1px solid #ddd;'>Valor</th>
                        </tr>
                    </thead>
                    <tbody>";

            foreach ($expiringContracts as $contract) {
                $fechaFin = new \DateTime($contract['fecha_fin']);
                $hoy = new \DateTime();
                $diasRestantes = (int)$hoy->diff($fechaFin)->format('%r%a');

                $colorDias = $diasRestantes <= 7 ? '#e53e3e' : ($diasRestantes <= 15 ? '#dd6b20' : '#38a169');

                $html .= "
                        <tr>
                            <td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($contract['nombre_cliente']) . "</td>
                            <td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($contract['numero_contrato']) . "</td>
                            <td style='padding: 8px; text-align: center; border: 1px solid #ddd;'>" . date('d/m/Y', strtotime($contract['fecha_inicio'])) . "</td>
                            <td style='padding: 8px; text-align: center; border: 1px solid #ddd;'>" . date('d/m/Y', strtotime($contract['fecha_fin'])) . "</td>
                            <td style='padding: 8px; text-align: center; border: 1px solid #ddd; color: " . $colorDias . "; font-weight: bold;'>" . $diasRestantes . " días</td>
                            <td style='padding: 8px; text-align: right; border: 1px solid #ddd;'>$" . number_format($contract['valor_contrato'] ?? 0, 0, ',', '.') . "</td>
                        </tr>";
            }

            $html .= "</tbody></table>";
        } else {
            $html .= "<p style='color: #38a169;'>No hay contratos próximos a vencer en los siguientes 30 días.</p>";
        }

        $html .= "
                <p style='color: #666; font-size: 12px; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 15px;'>
                    Este es un reporte automático generado cada lunes por el sistema de gestión de contratos de Cycloid Talent.<br>
                    Para más detalles, ingrese a <a href='https://phorizontal.cycloidtalent.com/contracts'>phorizontal.cycloidtalent.com/contracts</a>
                </p>
            </div>";

        return $html;
    }
}
