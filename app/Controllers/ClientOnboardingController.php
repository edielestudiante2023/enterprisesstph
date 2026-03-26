<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\PlanModel;
use App\Models\CronogcapacitacionModel;
use App\Models\SimpleEvaluationModel;
use App\Models\CicloVisitaModel;
use App\Models\UserModel;
use App\Libraries\ContractLibrary;
use App\Libraries\WorkPlanLibrary;
use App\Libraries\TrainingLibrary;
use App\Libraries\StandardsLibrary;
use App\Libraries\ClientDocumentInitializerLibrary;
use App\Libraries\MatricesGeneratorLibrary;
use CodeIgniter\Controller;

/**
 * ClientOnboardingController
 *
 * Flujo contrato-primero: el contrato es el punto de entrada para
 * clientes nuevos. Crea tbl_clientes + tbl_contratos atómicamente
 * y deriva tbl_clientes.estandares desde frecuencia_visitas del contrato.
 */
class ClientOnboardingController extends Controller
{
    /** Mapa frecuencia_visitas (contrato) → estandares (cliente) */
    private const FREQ_MAP = [
        'MENSUAL'    => 'Mensual',
        'BIMENSUAL'  => 'Bimensual',
        'TRIMESTRAL' => 'Trimestral',
        'PROYECTO'   => 'Proyecto',
        'SEMESTRAL'  => 'Proyecto',
        'ANUAL'      => 'Proyecto',
    ];

    public function create()
    {
        $consultantModel = new ConsultantModel();
        return view('clients/onboarding', [
            'consultants' => $consultantModel->findAll(),
        ]);
    }

    public function store()
    {
        $request = $this->request;

        // ── Validaciones básicas ──────────────────────────────────────────
        $idConsultor       = $request->getPost('id_consultor');
        $frecuenciaVisitas = strtoupper(trim($request->getPost('frecuencia_visitas') ?? 'MENSUAL'));
        $correoCliente     = $request->getPost('correo_cliente');
        $passwordPlano     = $request->getPost('password');
        $nitCliente        = $request->getPost('nit_cliente');

        if (empty($idConsultor)) {
            return redirect()->back()->withInput()->with('error', 'Debe seleccionar un consultor.');
        }
        if (empty($frecuenciaVisitas)) {
            return redirect()->back()->withInput()->with('error', 'Debe seleccionar la frecuencia de visitas.');
        }

        // ── Derivar estandares desde frecuencia_visitas ──────────────────
        $estandares = self::FREQ_MAP[$frecuenciaVisitas] ?? 'Mensual';

        // ── File uploads (todos opcionales) ──────────────────────────────
        $uploadPath = ROOTPATH . 'public/uploads';

        $logoName       = $this->moveFile($request->getFile('logo'), $uploadPath);
        $firmaName      = $this->moveFile($request->getFile('firma_representante_legal'), $uploadPath);
        $rutName        = $this->moveFile($request->getFile('rut'), $uploadPath);
        $camaraName     = $this->moveFile($request->getFile('camara_comercio'), $uploadPath);
        $cedulaDocName  = $this->moveFile($request->getFile('cedula_rep_legal_doc'), $uploadPath);
        $ofertaName     = $this->moveFile($request->getFile('oferta_comercial'), $uploadPath);

        // Generar thumbnail del logo si se subió
        if ($logoName) {
            $this->generateLogoThumbnail($uploadPath, $logoName);
        }

        // ── Crear cliente ─────────────────────────────────────────────────
        $fechaCierre    = $request->getPost('fecha_cierre_facturacion');
        $fechaAsignacion = $request->getPost('fecha_asignacion_cronograma');

        $clientData = [
            'datetime'                    => date('Y-m-d H:i:s'),
            'fecha_ingreso'               => $request->getPost('fecha_ingreso') ?: $request->getPost('fecha_inicio'),
            'nit_cliente'                 => $nitCliente,
            'nombre_cliente'              => $request->getPost('nombre_cliente'),
            'usuario'                     => $request->getPost('usuario'),
            'password'                    => password_hash($passwordPlano, PASSWORD_BCRYPT),
            'correo_cliente'              => $correoCliente,
            'correo_consejo_admon'        => $request->getPost('correo_consejo_admon'),
            'telefono_1_cliente'          => $request->getPost('telefono_1_cliente'),
            'telefono_2_cliente'          => $request->getPost('telefono_2_cliente'),
            'direccion_cliente'           => $request->getPost('direccion_cliente'),
            'persona_contacto_compras'    => $request->getPost('persona_contacto_compras'),
            'persona_contacto_operaciones'=> $request->getPost('persona_contacto_operaciones'),
            'persona_contacto_pagos'      => $request->getPost('persona_contacto_pagos'),
            'horarios_y_dias'             => $request->getPost('horarios_y_dias'),
            'codigo_actividad_economica'  => $request->getPost('codigo_actividad_economica'),
            'nombre_rep_legal'            => $request->getPost('nombre_rep_legal'),
            'cedula_rep_legal'            => $request->getPost('cedula_rep_legal'),
            'ciudad_cliente'              => $request->getPost('ciudad_cliente'),
            'estado'                      => 'prospecto',
            'id_consultor'                => $idConsultor,
            'vendedor'                    => $request->getPost('vendedor'),
            'plazo_cartera'               => $request->getPost('plazo_cartera'),
            'fecha_cierre_facturacion'    => !empty($fechaCierre) ? (int)$fechaCierre : null,
            'fecha_asignacion_cronograma' => !empty($fechaAsignacion) ? $fechaAsignacion : null,
            'logo'                        => $logoName,
            'rut'                         => $rutName,
            'camara_comercio'             => $camaraName,
            'cedula_rep_legal_doc'        => $cedulaDocName,
            'oferta_comercial'            => $ofertaName,
            'firma_representante_legal'   => $firmaName,
            'estandares'                  => $estandares,   // derivado del contrato, no del form
            'consultor_externo'           => $request->getPost('consultor_externo'),
            'email_consultor_externo'     => $request->getPost('email_consultor_externo'),
        ];

        $clientModel = new ClientModel();
        if (!$clientModel->save($clientData)) {
            return redirect()->back()->withInput()->with('error', 'Error al crear el cliente: ' . implode(', ', $clientModel->errors()));
        }
        $clientId = $clientModel->getInsertID();

        // ── Carpeta NIT ───────────────────────────────────────────────────
        $nitPath = UPLOADS_PATH . $nitCliente;
        if (!is_dir($nitPath)) {
            mkdir($nitPath, 0777, true);
        }

        // ── Crear contrato (source of truth para estandares) ─────────────
        $contractLibrary = new ContractLibrary();
        $contractData = [
            'id_cliente'              => $clientId,
            'fecha_inicio'            => $request->getPost('fecha_inicio'),
            'fecha_fin'               => $request->getPost('fecha_fin'),
            'valor_contrato'          => $request->getPost('valor_contrato'),
            'valor_mensual'           => $request->getPost('valor_mensual'),
            'numero_cuotas'           => $request->getPost('numero_cuotas'),
            'frecuencia_visitas'      => $frecuenciaVisitas,
            'tipo_contrato'           => 'inicial',
            'estado'                  => 'activo',
            'observaciones'           => $request->getPost('observaciones'),
            'clausula_cuarta_duracion'=> $request->getPost('clausula_cuarta_duracion'),
        ];

        // skip_init=true: no activa cliente ni genera PTA — se dispara al firmar
        $contractResult = $contractLibrary->createContract($contractData, ['skip_init' => true]);

        if (!$contractResult['success']) {
            log_message('error', "Onboarding: contrato no creado para cliente {$clientId}: " . $contractResult['message']);
            // El cliente ya existe — redirigir informando para que creen el contrato manualmente
            return redirect()->to('/listClients')
                ->with('warning', 'Cliente creado pero hubo un error al crear el contrato: ' . $contractResult['message'] . '. Por favor cree el contrato manualmente desde /contracts/create/' . $clientId);
        }

        $contractId = $contractResult['contract_id'];

        // ── Sin inicializaciones ──────────────────────────────────────────
        // El cliente queda como 'prospecto'. Toda la inicialización
        // (CicloVisita, PTA, Capacitaciones, Estándares, Matrices, Usuario)
        // se dispara en ContractController cuando el cliente firma el contrato.

        return redirect()->to('/contracts/view/' . $contractId)
            ->with('success', 'Prospecto creado. Envíe el contrato a firma para activar al cliente.');
    }

    // ────────────────────────────────────────────────────────────────────
    // Helpers privados
    // ────────────────────────────────────────────────────────────────────

    private function moveFile($file, string $uploadPath): ?string
    {
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $name = $file->getRandomName();
            $file->move($uploadPath, $name);
            return $name;
        }
        return null;
    }

    private function generateLogoThumbnail(string $uploadPath, string $logoName): void
    {
        try {
            $ext = strtolower(pathinfo($logoName, PATHINFO_EXTENSION));
            $src = $uploadPath . '/' . $logoName;
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                return;
            }
            \Config\Services::image()
                ->withFile($src)
                ->fit(200, 200, 'center')
                ->save($uploadPath . '/thumb_' . $logoName);
        } catch (\Exception $e) {
            log_message('error', 'Onboarding: error thumbnail logo: ' . $e->getMessage());
        }
    }

    private function sendWelcomeCredentialsEmail(
        string $nombreCliente,
        string $usuario,
        string $password,
        string $correo,
        string $nombreConsultor
    ): bool {
        $apiKey = getenv('SENDGRID_API_KEY');
        if (!$apiKey) {
            log_message('error', 'Onboarding: SENDGRID_API_KEY no definida');
            return false;
        }
        $subject = 'Bienvenido a CycloidTalent SST — Sus credenciales de acceso';
        $body    = "
            <p>Estimado/a <strong>" . htmlspecialchars($nombreCliente) . "</strong>,</p>
            <p>Su portal de Seguridad y Salud en el Trabajo ha sido activado. A continuación sus credenciales:</p>
            <ul>
                <li><strong>Usuario:</strong> " . htmlspecialchars($usuario) . "</li>
                <li><strong>Contraseña:</strong> " . htmlspecialchars($password) . "</li>
            </ul>
            <p>Su consultor asignado es <strong>" . htmlspecialchars($nombreConsultor) . "</strong>.</p>
            <p>Por favor cambie su contraseña después del primer acceso.</p>
            <p>Atentamente,<br>Equipo CycloidTalent SST</p>
        ";
        $payload = json_encode([
            'personalizations' => [[
                'to' => [['email' => $correo]],
            ]],
            'from'    => ['email' => 'noreply@cycloidtalent.com', 'name' => 'CycloidTalent SST'],
            'subject' => $subject,
            'content' => [['type' => 'text/html', 'value' => $body]],
        ]);
        $ch = curl_init('https://api.sendgrid.com/v3/mail/send');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
            ],
        ]);
        $response   = curl_exec($ch);
        $httpCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $httpCode >= 200 && $httpCode < 300;
    }
}
