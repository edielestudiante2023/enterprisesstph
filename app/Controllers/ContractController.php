<?php

namespace App\Controllers;

use App\Models\ContractModel;
use App\Models\ClientModel;
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
            'clausula_cuarta_duracion' => $this->request->getPost('clausula_cuarta_duracion')
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
