<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\CartaVigiaModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use Dompdf\Dompdf;

class CartaVigiaPwaController extends BaseController
{
    protected $cartaModel;
    protected $clientModel;

    public function __construct()
    {
        $this->cartaModel = new CartaVigiaModel();
        $this->clientModel = new ClientModel();
    }

    // ===========================
    // MÉTODOS PWA (autenticados)
    // ===========================

    /**
     * Listado de cartas por cliente
     */
    public function list($idCliente = null)
    {
        $userId = session()->get('user_id');
        $role = session()->get('role');

        $cartas = [];
        $clienteSeleccionado = null;

        if ($idCliente) {
            $clienteSeleccionado = $this->clientModel->find($idCliente);

            $builder = $this->cartaModel
                ->select('tbl_carta_vigia.*, tbl_clientes.nombre_cliente')
                ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_carta_vigia.id_cliente', 'left')
                ->where('tbl_carta_vigia.id_cliente', $idCliente);

            if ($role !== 'admin') {
                $builder->where('tbl_carta_vigia.id_consultor', $userId);
            }

            $cartas = $builder->orderBy('tbl_carta_vigia.created_at', 'DESC')
                ->findAll();
        }

        // Conteos por estado
        $conteos = ['pendiente_firma' => 0, 'firmado' => 0, 'sin_enviar' => 0];
        foreach ($cartas as $c) {
            $conteos[$c['estado_firma']] = ($conteos[$c['estado_firma']] ?? 0) + 1;
        }

        $data = [
            'title'               => 'Carta Vigia',
            'idCliente'           => $idCliente,
            'clienteSeleccionado' => $clienteSeleccionado,
            'cartas'              => $cartas,
            'conteos'             => $conteos,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/carta_vigia/list', $data),
            'title'   => 'Carta Vigia',
        ]);
    }

    /**
     * Formulario crear nueva carta
     */
    public function create($idCliente = null)
    {
        $data = [
            'title'     => 'Nueva Carta Vigia',
            'idCliente' => $idCliente,
            'cliente'   => $idCliente ? $this->clientModel->find($idCliente) : null,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/carta_vigia/form', $data),
            'title'   => 'Nueva Carta Vigia',
        ]);
    }

    /**
     * Guardar carta + generar PDF + enviar email
     */
    public function store()
    {
        $idCliente = $this->request->getPost('id_cliente');
        $nombre = trim($this->request->getPost('nombre_vigia') ?? '');
        $documento = trim($this->request->getPost('documento_vigia') ?? '');
        $email = trim($this->request->getPost('email_vigia') ?? '');
        $telefono = trim($this->request->getPost('telefono_vigia') ?? '');

        if (!$idCliente || !$nombre || !$documento || !$email) {
            session()->setFlashdata('error', 'Faltan campos obligatorios');
            return redirect()->back();
        }

        $userId = session()->get('user_id');

        // Generar token de firma
        $token = bin2hex(random_bytes(32));
        $expiracion = date('Y-m-d H:i:s', strtotime('+7 days'));

        // Insertar registro
        $this->cartaModel->insert([
            'id_cliente'              => $idCliente,
            'id_consultor'            => $userId,
            'nombre_vigia'            => $nombre,
            'documento_vigia'         => $documento,
            'email_vigia'             => $email,
            'telefono_vigia'          => $telefono,
            'token_firma'             => $token,
            'token_firma_expiracion'  => $expiracion,
            'estado_firma'            => 'pendiente_firma',
        ]);

        $id = $this->cartaModel->getInsertID();

        // Generar PDF (sin firma)
        $pdfPath = $this->generarPdf($id);
        if ($pdfPath) {
            $this->cartaModel->update($id, ['ruta_pdf' => $pdfPath]);
        }

        // Enviar email
        $carta = $this->cartaModel->find($id);
        $cliente = $this->clientModel->find($idCliente);
        $urlFirma = base_url("carta-vigia/firmar/{$token}");
        $this->enviarEmailFirma($carta, $cliente, $urlFirma);

        session()->setFlashdata('msg', 'Carta generada y enviada a ' . $email);
        return redirect()->to('/inspecciones/carta-vigia/cliente/' . $idCliente);
    }

    /**
     * Eliminar carta (solo sin_enviar o pendiente_firma)
     */
    public function delete($id)
    {
        $carta = $this->cartaModel->find($id);
        if (!$carta) {
            session()->setFlashdata('error', 'No encontrada');
            return redirect()->to('/inspecciones/carta-vigia');
        }

        if ($carta['estado_firma'] === 'firmado') {
            session()->setFlashdata('error', 'No se puede eliminar una carta ya firmada');
            return redirect()->to('/inspecciones/carta-vigia/cliente/' . $carta['id_cliente']);
        }

        // Eliminar PDF del disco
        if (!empty($carta['ruta_pdf']) && file_exists(FCPATH . $carta['ruta_pdf'])) {
            unlink(FCPATH . $carta['ruta_pdf']);
        }

        $idCliente = $carta['id_cliente'];
        $this->cartaModel->delete($id);

        session()->setFlashdata('msg', 'Carta eliminada');
        return redirect()->to('/inspecciones/carta-vigia/cliente/' . $idCliente);
    }

    /**
     * AJAX: Reenviar email de firma (genera nuevo token)
     */
    public function reenviar($id)
    {
        $carta = $this->cartaModel->find($id);
        if (!$carta) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'No encontrada']);
        }

        if ($carta['estado_firma'] === 'firmado') {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Ya esta firmada']);
        }

        // Nuevo token
        $token = bin2hex(random_bytes(32));
        $expiracion = date('Y-m-d H:i:s', strtotime('+7 days'));

        $this->cartaModel->update($id, [
            'token_firma'            => $token,
            'token_firma_expiracion' => $expiracion,
            'estado_firma'           => 'pendiente_firma',
        ]);

        $carta['token_firma'] = $token;
        $cliente = $this->clientModel->find($carta['id_cliente']);
        $urlFirma = base_url("carta-vigia/firmar/{$token}");
        $sent = $this->enviarEmailFirma($carta, $cliente, $urlFirma);

        return $this->response->setJSON([
            'success' => $sent,
            'message' => $sent ? 'Email reenviado' : 'Error al enviar email',
        ]);
    }

    /**
     * Ver PDF firmado
     */
    public function verPdf($id)
    {
        $carta = $this->cartaModel->find($id);
        if (!$carta || empty($carta['ruta_pdf'])) {
            session()->setFlashdata('error', 'PDF no disponible');
            return redirect()->back();
        }

        $fullPath = FCPATH . $carta['ruta_pdf'];
        if (!file_exists($fullPath)) {
            session()->setFlashdata('error', 'Archivo PDF no encontrado');
            return redirect()->back();
        }

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="carta_vigia_' . $id . '.pdf"')
            ->setBody(file_get_contents($fullPath));
    }

    // ================================
    // MÉTODOS PÚBLICOS (sin auth)
    // ================================

    /**
     * Página pública de firma (canvas)
     */
    public function firmar($token)
    {
        $carta = $this->cartaModel->where('token_firma', $token)->first();

        if (!$carta) {
            return view('inspecciones/carta_vigia/firma_error', [
                'mensaje' => 'El enlace de firma no es valido o ya fue utilizado.',
            ]);
        }

        if (strtotime($carta['token_firma_expiracion']) < time()) {
            return view('inspecciones/carta_vigia/firma_error', [
                'mensaje' => 'El enlace de firma ha expirado. Solicite al consultor que reenvie el email.',
            ]);
        }

        if ($carta['estado_firma'] !== 'pendiente_firma') {
            return view('inspecciones/carta_vigia/firma_error', [
                'mensaje' => 'Esta carta ya fue firmada.',
            ]);
        }

        $cliente = $this->clientModel->find($carta['id_cliente']);

        // URL del PDF para preview
        $pdfUrl = !empty($carta['ruta_pdf']) ? base_url($carta['ruta_pdf']) : '';

        return view('inspecciones/carta_vigia/firma', [
            'carta'   => $carta,
            'cliente' => $cliente,
            'token'   => $token,
            'pdfUrl'  => $pdfUrl,
        ]);
    }

    /**
     * Procesar firma (POST público, sin auth)
     */
    public function procesarFirma()
    {
        $token = $this->request->getPost('token');
        $firmaImagen = $this->request->getPost('firma_imagen');

        $carta = $this->cartaModel->where('token_firma', $token)->first();

        if (!$carta || $carta['estado_firma'] !== 'pendiente_firma') {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Token invalido']);
        }

        if (strtotime($carta['token_firma_expiracion']) < time()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Token expirado']);
        }

        if (empty($firmaImagen)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Firma requerida']);
        }

        // Guardar imagen de firma
        $firmaDir = FCPATH . 'uploads/inspecciones/firmas/';
        if (!is_dir($firmaDir)) {
            mkdir($firmaDir, 0755, true);
        }

        $firmaData = str_replace('data:image/png;base64,', '', $firmaImagen);
        $firmaData = base64_decode($firmaData);
        $firmaFileName = 'firma_vigia_' . $carta['id'] . '_' . time() . '.png';
        file_put_contents($firmaDir . $firmaFileName, $firmaData);
        $firmaPath = 'uploads/inspecciones/firmas/' . $firmaFileName;

        // Generar código de verificación
        $hash = hash('sha256', $token . '|' . $carta['id'] . '|' . $carta['documento_vigia']);
        $codigoVerificacion = strtoupper(substr($hash, 0, 12));

        // Actualizar registro
        $this->cartaModel->update($carta['id'], [
            'estado_firma'        => 'firmado',
            'firma_imagen'        => $firmaPath,
            'firma_ip'            => $this->request->getIPAddress(),
            'firma_fecha'         => date('Y-m-d H:i:s'),
            'codigo_verificacion' => $codigoVerificacion,
            'token_firma'         => null,
        ]);

        // Regenerar PDF con firma estampada
        $pdfPath = $this->generarPdf($carta['id']);
        if ($pdfPath) {
            $this->cartaModel->update($carta['id'], ['ruta_pdf' => $pdfPath]);
        }

        return $this->response->setJSON([
            'success'            => true,
            'message'            => 'Carta firmada exitosamente',
            'codigoVerificacion' => $codigoVerificacion,
        ]);
    }

    /**
     * Página pública de verificación
     */
    public function verificar($codigo)
    {
        $carta = $this->cartaModel->where('codigo_verificacion', strtoupper($codigo))->first();

        if (!$carta) {
            return view('inspecciones/carta_vigia/firma_error', [
                'mensaje' => 'Codigo de verificacion no valido.',
            ]);
        }

        $cliente = $this->clientModel->find($carta['id_cliente']);

        return view('inspecciones/carta_vigia/firma_success', [
            'carta'   => $carta,
            'cliente' => $cliente,
            'verificacion' => true,
        ]);
    }

    // ===========================
    // MÉTODOS PRIVADOS
    // ===========================

    /**
     * Generar PDF con DOMPDF
     */
    private function generarPdf(int $id): ?string
    {
        $carta = $this->cartaModel->find($id);
        $cliente = $this->clientModel->find($carta['id_cliente']);
        $consultantModel = new ConsultantModel();
        $consultor = $consultantModel->find($carta['id_consultor']);

        // Logo del cliente en base64
        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoMime = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        // Firma en base64 (si ya firmó)
        $firmaBase64 = '';
        if (!empty($carta['firma_imagen'])) {
            $firmaPath = FCPATH . $carta['firma_imagen'];
            if (file_exists($firmaPath)) {
                $firmaMime = mime_content_type($firmaPath);
                $firmaBase64 = 'data:' . $firmaMime . ';base64,' . base64_encode(file_get_contents($firmaPath));
            }
        }

        $data = [
            'carta'       => $carta,
            'cliente'     => $cliente,
            'consultor'   => $consultor,
            'logoBase64'  => $logoBase64,
            'firmaBase64' => $firmaBase64,
        ];

        $html = view('inspecciones/carta_vigia/pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        // Guardar PDF
        $pdfDir = 'uploads/inspecciones/cartas_vigia/';
        if (!is_dir(FCPATH . $pdfDir)) {
            mkdir(FCPATH . $pdfDir, 0755, true);
        }

        $pdfFileName = 'carta_vigia_' . $id . '_' . date('Ymd_His') . '.pdf';
        $pdfPath = $pdfDir . $pdfFileName;

        // Eliminar PDF anterior
        $cartaActual = $this->cartaModel->find($id);
        if (!empty($cartaActual['ruta_pdf']) && file_exists(FCPATH . $cartaActual['ruta_pdf'])) {
            unlink(FCPATH . $cartaActual['ruta_pdf']);
        }

        file_put_contents(FCPATH . $pdfPath, $dompdf->output());

        return $pdfPath;
    }

    /**
     * Enviar email con SendGrid
     */
    private function enviarEmailFirma(array $carta, array $cliente, string $urlFirma): bool
    {
        $apiKey = env('SENDGRID_API_KEY');
        if (empty($apiKey)) {
            log_message('error', 'SENDGRID_API_KEY no configurada');
            return false;
        }

        $htmlEmail = view('inspecciones/carta_vigia/email_firma', [
            'carta'    => $carta,
            'cliente'  => $cliente,
            'urlFirma' => $urlFirma,
        ]);

        $subject = "Designacion como Vigia SST - " . ($cliente['nombre_cliente'] ?? 'Cliente');
        $fromEmail = env('SENDGRID_FROM_EMAIL', 'notificacion.cycloidtalent@cycloidtalent.com');
        $fromName = env('SENDGRID_FROM_NAME', 'Enterprise SST');

        $data = [
            'personalizations' => [
                [
                    'to' => [['email' => $carta['email_vigia'], 'name' => $carta['nombre_vigia']]],
                    'subject' => $subject,
                ]
            ],
            'from' => [
                'email' => $fromEmail,
                'name' => $fromName,
            ],
            'content' => [
                ['type' => 'text/html', 'value' => $htmlEmail]
            ]
        ];

        $ch = curl_init('https://api.sendgrid.com/v3/mail/send');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
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
            log_message('error', "SendGrid Error (carta vigia) - HTTP {$httpCode}: {$response} | cURL: {$curlError}");
        }

        return $httpCode >= 200 && $httpCode < 300;
    }
}
