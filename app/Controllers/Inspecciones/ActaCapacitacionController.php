<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\ActaCapacitacionModel;
use App\Models\ActaCapacitacionAsistenteModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use App\Models\CronogcapacitacionModel;
use App\Traits\AutosaveJsonTrait;
use App\Traits\ImagenCompresionTrait;
use Dompdf\Dompdf;

/**
 * Acta de Capacitación — flujo del consultor + endpoints públicos (firma remota + auto-inscripción QR).
 */
class ActaCapacitacionController extends BaseController
{
    use AutosaveJsonTrait;
    use ImagenCompresionTrait;

    protected ActaCapacitacionModel $actaModel;
    protected ActaCapacitacionAsistenteModel $asistenteModel;

    public function __construct()
    {
        $this->actaModel = new ActaCapacitacionModel();
        $this->asistenteModel = new ActaCapacitacionAsistenteModel();
    }

    public function list()
    {
        $actas = $this->actaModel
            ->select('tbl_acta_capacitacion.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_acta_capacitacion.id_cliente', 'left')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_acta_capacitacion.id_consultor', 'left')
            ->orderBy('tbl_acta_capacitacion.fecha_capacitacion', 'DESC')
            ->findAll();

        foreach ($actas as &$a) {
            $a['total_asistentes'] = $this->asistenteModel
                ->where('id_acta_capacitacion', $a['id'])->countAllResults(false);
            $a['total_firmados'] = $this->asistenteModel
                ->where('id_acta_capacitacion', $a['id'])
                ->where('firma_path IS NOT NULL', null, false)->countAllResults(false);
        }

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/acta_capacitacion/list', ['actas' => $actas]),
            'title'   => 'Actas de Capacitación',
        ]);
    }

    public function create($idCliente = null)
    {
        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/acta_capacitacion/form', [
                'title'      => 'Nueva Acta de Capacitación',
                'acta'       => null,
                'asistentes' => [],
                'idCliente'  => $idCliente,
                'contexto'   => 'consultor',
            ]),
            'title' => 'Nueva Acta de Capacitación',
        ]);
    }

    public function store()
    {
        $userId = session()->get('user_id');
        $isAutosave = $this->isAutosaveRequest();

        if (!$isAutosave) {
            if (!$this->validate([
                'id_cliente'         => 'required|integer',
                'fecha_capacitacion' => 'required|valid_date',
                'tema'               => 'required|min_length[3]',
            ])) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }
        }

        $idComite = $this->request->getPost('id_comite');
        $idCronog = $this->request->getPost('id_cronograma_capacitacion');
        $data = [
            'id_cliente'                 => $this->request->getPost('id_cliente'),
            'id_comite'                  => $idComite ? (int)$idComite : null,
            'creado_por_tipo'            => 'consultor',
            'id_consultor'               => $userId,
            'tema'                       => $this->request->getPost('tema'),
            'fecha_capacitacion'         => $this->request->getPost('fecha_capacitacion'),
            'hora_inicio'                => $this->request->getPost('hora_inicio') ?: null,
            'hora_fin'                   => $this->request->getPost('hora_fin') ?: null,
            'dictada_por'                => $this->request->getPost('dictada_por') ?: 'ARL',
            'nombre_capacitador'         => $this->request->getPost('nombre_capacitador'),
            'entidad_capacitadora'       => $this->request->getPost('entidad_capacitadora'),
            'modalidad'                  => $this->request->getPost('modalidad') ?: 'virtual',
            'tipo_charla'                => $this->request->getPost('tipo_charla') ?: 'capacitacion',
            'id_cronograma_capacitacion' => $idCronog ? (int)$idCronog : null,
            'enlace_grabacion'           => $this->request->getPost('enlace_grabacion'),
            'objetivos'                  => $this->request->getPost('objetivos'),
            'contenido'                  => $this->request->getPost('contenido'),
            'observaciones'              => $this->request->getPost('observaciones'),
            'numero_programados'         => $this->request->getPost('numero_programados') !== null && $this->request->getPost('numero_programados') !== ''
                                              ? (int) $this->request->getPost('numero_programados') : null,
            'numero_evaluados'           => $this->request->getPost('numero_evaluados') !== null && $this->request->getPost('numero_evaluados') !== ''
                                              ? (int) $this->request->getPost('numero_evaluados') : null,
            'promedio_calificaciones'    => $this->request->getPost('promedio_calificaciones') !== null && $this->request->getPost('promedio_calificaciones') !== ''
                                              ? $this->request->getPost('promedio_calificaciones') : null,
            'estado'                     => 'borrador',
        ];

        $dirFotos = 'uploads/inspecciones/acta-capacitacion/';
        foreach (['foto_capacitacion','foto_otros_1','foto_otros_2'] as $campo) {
            $nueva = $this->uploadFoto($campo, $dirFotos);
            if ($nueva) $data[$campo] = $nueva;
        }

        $this->actaModel->insert($data);
        $idActa = $this->actaModel->getInsertID();

        $this->saveAsistentes($idActa);

        if ($isAutosave) return $this->autosaveJsonSuccess($idActa);
        return redirect()->to('/inspecciones/acta-capacitacion/edit/' . $idActa)
            ->with('msg', 'Guardada como borrador');
    }

    public function edit($id)
    {
        $acta = $this->actaModel->find($id);
        if (!$acta) return redirect()->to('/inspecciones/acta-capacitacion')->with('error', 'No encontrada');
        if ($acta['estado'] === 'completo') return redirect()->to('/inspecciones/acta-capacitacion/view/' . $id);

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/acta_capacitacion/form', [
                'title'      => 'Editar Acta de Capacitación',
                'acta'       => $acta,
                'asistentes' => $this->asistenteModel->getByActa((int)$id),
                'idCliente'  => $acta['id_cliente'],
                'contexto'   => 'consultor',
            ]),
            'title' => 'Editar Acta de Capacitación',
        ]);
    }

    public function update($id)
    {
        $acta = $this->actaModel->find($id);
        if (!$acta) {
            if ($this->isAutosaveRequest()) return $this->autosaveJsonError('No encontrada', 404);
            return redirect()->to('/inspecciones/acta-capacitacion');
        }
        if ($acta['estado'] === 'completo') {
            if ($this->isAutosaveRequest()) return $this->autosaveJsonError('No editable', 400);
            return redirect()->to('/inspecciones/acta-capacitacion/view/' . $id);
        }

        $idComite = $this->request->getPost('id_comite');
        $idCronog = $this->request->getPost('id_cronograma_capacitacion');
        $data = [
            'id_cliente'                 => $this->request->getPost('id_cliente'),
            'id_comite'                  => $idComite ? (int)$idComite : null,
            'tema'                       => $this->request->getPost('tema'),
            'fecha_capacitacion'         => $this->request->getPost('fecha_capacitacion'),
            'hora_inicio'                => $this->request->getPost('hora_inicio') ?: null,
            'hora_fin'                   => $this->request->getPost('hora_fin') ?: null,
            'dictada_por'                => $this->request->getPost('dictada_por') ?: 'ARL',
            'nombre_capacitador'         => $this->request->getPost('nombre_capacitador'),
            'entidad_capacitadora'       => $this->request->getPost('entidad_capacitadora'),
            'modalidad'                  => $this->request->getPost('modalidad') ?: 'virtual',
            'tipo_charla'                => $this->request->getPost('tipo_charla') ?: 'capacitacion',
            'id_cronograma_capacitacion' => $idCronog ? (int)$idCronog : null,
            'enlace_grabacion'           => $this->request->getPost('enlace_grabacion'),
            'objetivos'                  => $this->request->getPost('objetivos'),
            'contenido'                  => $this->request->getPost('contenido'),
            'observaciones'              => $this->request->getPost('observaciones'),
            'numero_programados'         => $this->request->getPost('numero_programados') !== null && $this->request->getPost('numero_programados') !== ''
                                              ? (int) $this->request->getPost('numero_programados') : null,
            'numero_evaluados'           => $this->request->getPost('numero_evaluados') !== null && $this->request->getPost('numero_evaluados') !== ''
                                              ? (int) $this->request->getPost('numero_evaluados') : null,
            'promedio_calificaciones'    => $this->request->getPost('promedio_calificaciones') !== null && $this->request->getPost('promedio_calificaciones') !== ''
                                              ? $this->request->getPost('promedio_calificaciones') : null,
        ];

        $dirFotos = 'uploads/inspecciones/acta-capacitacion/';
        foreach (['foto_capacitacion','foto_otros_1','foto_otros_2'] as $campo) {
            $nueva = $this->uploadFoto($campo, $dirFotos);
            if ($nueva) {
                if (!empty($acta[$campo]) && file_exists(FCPATH . $acta[$campo])) {
                    unlink(FCPATH . $acta[$campo]);
                }
                $data[$campo] = $nueva;
            }
        }

        $this->actaModel->update($id, $data);

        $this->saveAsistentes((int)$id);

        if ($this->request->getPost('finalizar')) return $this->finalizar($id);
        if ($this->isAutosaveRequest()) return $this->autosaveJsonSuccess((int)$id);

        return redirect()->to('/inspecciones/acta-capacitacion/edit/' . $id)->with('msg', 'Actualizada');
    }

    public function view($id)
    {
        $acta = $this->actaModel->find($id);
        if (!$acta) return redirect()->to('/inspecciones/acta-capacitacion');

        $clienteModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/acta_capacitacion/view', [
                'acta'         => $acta,
                'cliente'      => $clienteModel->find($acta['id_cliente']),
                'consultor'    => $acta['id_consultor'] ? $consultantModel->find($acta['id_consultor']) : null,
                'realizadoPor' => null,
                'asistentes'   => $this->asistenteModel->getByActa((int)$id),
                'contexto'     => 'consultor',
            ]),
            'title' => 'Ver Acta de Capacitación',
        ]);
    }

    /**
     * AJAX (auth): genera token de firma remota para un asistente.
     */
    public function generarTokenFirma(int $idAsistente)
    {
        $asistente = $this->asistenteModel->find($idAsistente);
        if (!$asistente) return $this->response->setJSON(['success' => false, 'error' => 'Asistente no encontrado']);

        $acta = $this->actaModel->find($asistente['id_acta_capacitacion']);
        if (!$acta) return $this->response->setJSON(['success' => false, 'error' => 'Acta no encontrada']);
        if (!empty($asistente['firma_path'])) {
            return $this->response->setJSON(['success' => false, 'error' => 'Este asistente ya firmó']);
        }

        $token = bin2hex(random_bytes(32));
        $this->asistenteModel->update($idAsistente, [
            'token_firma'      => $token,
            'token_expiracion' => date('Y-m-d H:i:s', strtotime('+7 days')),
        ]);

        $url = base_url("acta-capacitacion/firmar-remoto/{$token}");
        return $this->response->setJSON(['success' => true, 'url' => $url, 'nombre' => $asistente['nombre_completo']]);
    }

    /**
     * AJAX: guarda/actualiza UN asistente individual (sin tocar el resto del form).
     */
    public function saveAsistente(int $idActa)
    {
        $acta = $this->actaModel->find($idActa);
        if (!$acta) return $this->response->setJSON(['success' => false, 'error' => 'Acta no encontrada']);
        if ($acta['estado'] === 'completo') {
            return $this->response->setJSON(['success' => false, 'error' => 'Acta ya finalizada']);
        }

        $nombre = trim((string)$this->request->getPost('nombre_completo'));
        if ($nombre === '') {
            return $this->response->setJSON(['success' => false, 'error' => 'Nombre requerido']);
        }

        $payload = [
            'id_acta_capacitacion' => $idActa,
            'nombre_completo'      => $nombre,
            'tipo_documento'       => $this->request->getPost('tipo_documento') ?: 'CC',
            'numero_documento'     => $this->request->getPost('numero_documento') ?: null,
            'cargo'                => $this->request->getPost('cargo') ?: null,
            'area_dependencia'     => $this->request->getPost('area_dependencia') ?: null,
            'email'                => $this->request->getPost('email') ?: null,
            'celular'              => $this->request->getPost('celular') ?: null,
            'orden'                => (int)($this->request->getPost('orden') ?: 1),
        ];

        $idAsistente = $this->request->getPost('id_asistente');
        if ($idAsistente && ($existente = $this->asistenteModel->find((int)$idAsistente))
            && (int)$existente['id_acta_capacitacion'] === $idActa) {
            $this->asistenteModel->update((int)$idAsistente, $payload);
            $id = (int)$idAsistente;
        } else {
            $this->asistenteModel->insert($payload);
            $id = (int)$this->asistenteModel->getInsertID();
        }

        return $this->response->setJSON([
            'success'   => true,
            'id'        => $id,
            'asistente' => $this->asistenteModel->find($id),
        ]);
    }

    /**
     * AJAX: genera token (si no existe) y envía email con el enlace de firma al asistente.
     */
    public function enviarEmailFirma(int $idAsistente)
    {
        $asistente = $this->asistenteModel->find($idAsistente);
        if (!$asistente) return $this->response->setJSON(['success' => false, 'error' => 'Asistente no encontrado']);

        $acta = $this->actaModel->find($asistente['id_acta_capacitacion']);
        if (!$acta) return $this->response->setJSON(['success' => false, 'error' => 'Acta no encontrada']);
        if (!empty($asistente['firma_path'])) {
            return $this->response->setJSON(['success' => false, 'error' => 'Este asistente ya firmó']);
        }
        if (empty($asistente['email'])) {
            return $this->response->setJSON(['success' => false, 'error' => 'Este asistente no tiene email registrado']);
        }

        $token = $asistente['token_firma'];
        $vigente = $token && $asistente['token_expiracion'] && strtotime($asistente['token_expiracion']) > time();
        if (!$vigente) {
            $token = bin2hex(random_bytes(32));
            $this->asistenteModel->update($idAsistente, [
                'token_firma'      => $token,
                'token_expiracion' => date('Y-m-d H:i:s', strtotime('+7 days')),
            ]);
        }

        $cliente = (new ClientModel())->find($acta['id_cliente']);
        $ok = $this->enviarEmailFirmaCapacitacion($asistente, $token, $acta, $cliente);

        return $this->response->setJSON([
            'success' => $ok,
            'email'   => $asistente['email'],
            'error'   => $ok ? null : 'No se pudo enviar el email',
        ]);
    }

    private function enviarEmailFirmaCapacitacion(array $asistente, string $token, array $acta, ?array $cliente): bool
    {
        $urlFirma = base_url("acta-capacitacion/firmar-remoto/{$token}");
        $tema = esc($acta['tema'] ?? '');
        $fecha = date('d/m/Y', strtotime($acta['fecha_capacitacion']));
        $modalidad = ucfirst($acta['modalidad'] ?? 'virtual');
        $nombreCliente = esc($cliente['nombre_cliente'] ?? '');
        $nombre = esc($asistente['nombre_completo']);

        $mensaje = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%); padding: 20px; text-align: center;'>
                <h2 style='color: white; margin: 0;'>Solicitud de Firma - Acta de Capacitación</h2>
            </div>
            <div style='padding: 30px; background: #f8f9fa;'>
                <p>Estimado/a <strong>{$nombre}</strong>,</p>
                <p>Se requiere su firma electrónica para confirmar la asistencia a la siguiente capacitación:</p>
                <div style='background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #bd9751;'>
                    <p style='margin: 5px 0;'><strong>Empresa:</strong> {$nombreCliente}</p>
                    <p style='margin: 5px 0;'><strong>Tema:</strong> {$tema}</p>
                    <p style='margin: 5px 0;'><strong>Fecha:</strong> {$fecha}</p>
                    <p style='margin: 5px 0;'><strong>Modalidad:</strong> {$modalidad}</p>
                </div>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$urlFirma}' style='background: #bd9751; color: white; padding: 15px 40px; text-decoration: none; border-radius: 8px; font-size: 16px; display: inline-block;'>
                        Firmar Acta de Capacitación
                    </a>
                </div>
                <p style='color: #666; font-size: 12px;'>O copie este enlace en su navegador:</p>
                <p style='word-break: break-all; background: #e9ecef; padding: 10px; border-radius: 4px; font-size: 12px;'>{$urlFirma}</p>
                <hr style='border: none; border-top: 1px solid #dee2e6; margin: 20px 0;'>
                <p style='color: #666; font-size: 11px;'>
                    <strong>Importante:</strong> Este enlace es personal e intransferible. No lo comparta con nadie.<br>
                    El enlace expirará en 7 días.
                </p>
            </div>
            <div style='background: #1e3a5f; padding: 15px; text-align: center;'>
                <p style='color: #94a3b8; font-size: 11px; margin: 0;'>EnterpriseSST - Sistema de Gestión de Seguridad y Salud en el Trabajo</p>
            </div>
        </div>";

        try {
            $email = new \SendGrid\Mail\Mail();
            $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "EnterpriseSST");
            $email->setSubject("Firma requerida: Capacitación - {$tema} - {$nombreCliente}");
            $email->addTo($asistente['email'], $asistente['nombre_completo']);
            $email->addContent("text/html", $mensaje);
            $sg = new \SendGrid(getenv('SENDGRID_API_KEY'));
            $response = $sg->send($email);
            return $response->statusCode() >= 200 && $response->statusCode() < 300;
        } catch (\Exception $e) {
            log_message('error', 'Error email firma capacitacion: ' . $e->getMessage());
            return false;
        }
    }

    public function finalizar($id)
    {
        $acta = $this->actaModel->find($id);
        if (!$acta) return redirect()->to('/inspecciones/acta-capacitacion');

        // Pre-cálculo de puntaje promedio con IA si está vacío y hay tema
        if (empty($acta['promedio_calificaciones']) && !empty($acta['tema'])) {
            $puntaje = $this->calcularPuntajeIA(
                (int) $acta['id_cliente'],
                $acta['fecha_capacitacion'],
                $acta['tema']
            );
            if ($puntaje !== null) {
                $this->actaModel->update($id, ['promedio_calificaciones' => $puntaje]);
                $acta['promedio_calificaciones'] = $puntaje;
            }
        }

        $result = $this->generarPdfInterno((int)$id);
        if (empty($result['acta'])) return redirect()->back()->with('error', 'Error al generar PDF');

        $update = ['estado' => 'completo', 'ruta_pdf' => $result['acta']];
        if (!empty($result['responsabilidades'])) {
            $update['ruta_pdf_responsabilidades'] = $result['responsabilidades'];
        }
        $this->actaModel->update($id, $update);
        $acta = $this->actaModel->find($id);

        $this->uploadToReportes($acta, $result['acta']);
        if (!empty($result['responsabilidades'])) {
            $this->uploadResponsabilidadesToReportes($acta, $result['responsabilidades']);
        }

        $this->syncToCronograma($acta);

        return redirect()->to('/inspecciones/acta-capacitacion/view/' . $id)->with('msg', 'Acta finalizada.');
    }

    public function generatePdf($id)
    {
        $acta = $this->actaModel->find($id);
        if (!$acta) return redirect()->to('/inspecciones/acta-capacitacion');

        $result = $this->generarPdfInterno((int)$id);
        $fullPath = FCPATH . $result['acta'];

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="acta_capacitacion_' . $id . '.pdf"')
            ->setBody(file_get_contents($fullPath));
    }

    public function generatePdfResponsabilidades($id)
    {
        $acta = $this->actaModel->find($id);
        if (!$acta) return redirect()->to('/inspecciones/acta-capacitacion');
        if (($acta['tipo_charla'] ?? '') !== 'induccion_reinduccion') {
            return redirect()->to('/inspecciones/acta-capacitacion/view/' . $id)
                ->with('error', 'Esta acta no es de inducción/reinducción.');
        }

        $result = $this->generarPdfInterno((int)$id);
        $fullPath = !empty($result['responsabilidades']) ? FCPATH . $result['responsabilidades'] : null;
        if (!$fullPath || !file_exists($fullPath)) {
            return redirect()->back()->with('error', 'No se pudo generar el PDF de responsabilidades.');
        }

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="responsabilidades_sst_' . $id . '.pdf"')
            ->setBody(file_get_contents($fullPath));
    }

    public function delete($id)
    {
        $acta = $this->actaModel->find($id);
        if (!$acta) return redirect()->to('/inspecciones/acta-capacitacion');
        if ($acta['estado'] === 'completo') {
            return redirect()->back()->with('error', 'No se pueden borrar actas finalizadas');
        }
        $this->actaModel->delete($id);
        return redirect()->to('/inspecciones/acta-capacitacion')->with('msg', 'Acta eliminada');
    }

    // ============================================================
    // ENDPOINTS PÚBLICOS (sin auth — token es la autenticación)
    // ============================================================

    /**
     * Página pública: canvas de firma para el asistente.
     */
    public function firmarRemoto(string $token)
    {
        $asistente = $this->asistenteModel->getByToken($token);
        if (!$asistente) {
            return view('inspecciones/acta_capacitacion/firma_remota_error', [
                'mensaje' => 'Este enlace no es válido o ya fue usado.'
            ]);
        }
        if ($asistente['token_expiracion'] && strtotime($asistente['token_expiracion']) < time()) {
            return view('inspecciones/acta_capacitacion/firma_remota_error', [
                'mensaje' => 'Este enlace ha expirado (7 días). Pida uno nuevo al organizador.'
            ]);
        }
        if (!empty($asistente['firma_path'])) {
            return view('inspecciones/acta_capacitacion/firma_remota_error', [
                'mensaje' => 'Esta firma ya fue registrada.'
            ]);
        }

        $acta = $this->actaModel->find($asistente['id_acta_capacitacion']);
        if (!$acta) {
            return view('inspecciones/acta_capacitacion/firma_remota_error', [
                'mensaje' => 'Acta no encontrada.'
            ]);
        }

        $cliente = (new ClientModel())->find($acta['id_cliente']);
        $todosAsistentes = $this->asistenteModel->getByActa((int)$acta['id']);

        return view('inspecciones/acta_capacitacion/firma_remota', [
            'token'      => $token,
            'acta'       => $acta,
            'cliente'    => $cliente,
            'asistente'  => $asistente,
            'asistentes' => $todosAsistentes,
        ]);
    }

    /**
     * AJAX público: recibe y guarda la firma remota.
     */
    public function procesarFirmaRemota()
    {
        $token       = $this->request->getPost('token');
        $firmaBase64 = $this->request->getPost('firma_imagen');

        if (!$token || !$firmaBase64) {
            return $this->response->setJSON(['success' => false, 'error' => 'Datos incompletos']);
        }

        $asistente = $this->asistenteModel->getByToken($token);
        if (!$asistente) {
            return $this->response->setJSON(['success' => false, 'error' => 'Enlace inválido']);
        }
        if ($asistente['token_expiracion'] && strtotime($asistente['token_expiracion']) < time()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Enlace expirado']);
        }
        if (!empty($asistente['firma_path'])) {
            return $this->response->setJSON(['success' => false, 'error' => 'Ya firmado']);
        }

        $firmaData    = explode(',', $firmaBase64);
        $firmaDecoded = base64_decode(end($firmaData));
        if ($firmaDecoded === false) {
            return $this->response->setJSON(['success' => false, 'error' => 'Firma inválida']);
        }

        $dir = FCPATH . 'uploads/inspecciones/firmas_capacitacion/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $nombreArchivo = 'firma_cap_' . $asistente['id'] . '_' . time() . '.png';
        file_put_contents($dir . $nombreArchivo, $firmaDecoded);

        $this->asistenteModel->update($asistente['id'], [
            'firma_path'       => 'uploads/inspecciones/firmas_capacitacion/' . $nombreArchivo,
            'firmado_at'       => date('Y-m-d H:i:s'),
            'token_firma'      => null,
            'token_expiracion' => null,
        ]);

        return $this->response->setJSON(['success' => true]);
    }

    // ===== PRIVADOS =====

    private function saveAsistentes(int $idActa): void
    {
        $ids       = $this->request->getPost('asistente_id') ?? [];
        $nombres   = $this->request->getPost('asistente_nombre') ?? [];
        $tiposDoc  = $this->request->getPost('asistente_tipo_doc') ?? [];
        $numsDoc   = $this->request->getPost('asistente_num_doc') ?? [];
        $cargos    = $this->request->getPost('asistente_cargo') ?? [];
        $areas     = $this->request->getPost('asistente_area') ?? [];
        $emails    = $this->request->getPost('asistente_email') ?? [];
        $celulares = $this->request->getPost('asistente_celular') ?? [];

        $existentes = [];
        foreach ($this->asistenteModel->getByActa($idActa) as $a) {
            $existentes[$a['id']] = $a;
        }

        foreach ($nombres as $i => $nombre) {
            if (empty(trim($nombre))) continue;

            $existenteId = isset($ids[$i]) && $ids[$i] !== '' ? (int)$ids[$i] : null;
            $payload = [
                'id_acta_capacitacion' => $idActa,
                'nombre_completo'      => trim($nombre),
                'tipo_documento'       => $tiposDoc[$i] ?? 'CC',
                'numero_documento'     => $numsDoc[$i] ?? null,
                'cargo'                => $cargos[$i] ?? null,
                'area_dependencia'     => $areas[$i] ?? null,
                'email'                => $emails[$i] ?? null,
                'celular'              => $celulares[$i] ?? null,
                'orden'                => $i + 1,
            ];

            if ($existenteId && isset($existentes[$existenteId])) {
                $this->asistenteModel->update($existenteId, $payload);
            } else {
                $this->asistenteModel->insert($payload);
            }
        }
    }

    /**
     * AJAX (auth): genera o reutiliza el token de auto-inscripcion del acta.
     * Devuelve la URL publica para imprimir como QR y mostrar a los asistentes.
     */
    public function generarTokenInscripcion(int $idActa)
    {
        $acta = $this->actaModel->find($idActa);
        if (!$acta) {
            return $this->response->setJSON(['success' => false, 'error' => 'Acta no encontrada']);
        }
        if ($acta['estado'] === 'completo') {
            return $this->response->setJSON(['success' => false, 'error' => 'Acta finalizada, no acepta inscripciones']);
        }

        $token = $acta['token_inscripcion'] ?? null;
        $regenerar = $this->request->getPost('regenerar') === '1';
        if (!$token || $regenerar) {
            $token = bin2hex(random_bytes(24));
            $this->actaModel->update($idActa, ['token_inscripcion' => $token]);
        }

        $url = base_url("acta-capacitacion/inscripcion/{$token}");
        return $this->response->setJSON([
            'success' => true,
            'token'   => $token,
            'url'     => $url,
            'qr_svg'  => $this->generarQrSvg($url),
        ]);
    }

    private function generarQrSvg(string $url): string
    {
        if (class_exists('\\chillerlan\\QRCode\\QRCode')) {
            try {
                $opts = new \chillerlan\QRCode\QROptions([
                    'outputType' => \chillerlan\QRCode\QRCode::OUTPUT_MARKUP_SVG,
                    'eccLevel'   => \chillerlan\QRCode\QRCode::ECC_M,
                    'scale'      => 8,
                    'imageBase64'=> false,
                ]);
                return (new \chillerlan\QRCode\QRCode($opts))->render($url);
            } catch (\Throwable $e) {
                log_message('error', 'QR local fallo, fallback a api externa: ' . $e->getMessage());
            }
        }
        $apiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=' . urlencode($url);
        return '<img src="' . esc($apiUrl) . '" alt="QR" style="width:100%;height:auto;">';
    }

    // ============================================================
    // ENDPOINTS PUBLICOS DE AUTO-INSCRIPCION (sin auth, token = autenticacion)
    // ============================================================

    public function inscripcion(string $token)
    {
        $acta = $this->actaModel->findByTokenInscripcion($token);
        if (!$acta) {
            return view('inspecciones/acta_capacitacion/inscripcion_error', [
                'mensaje' => 'Este enlace no es valido. Solicita uno nuevo al organizador.'
            ]);
        }
        if ($acta['estado'] === 'completo') {
            return view('inspecciones/acta_capacitacion/inscripcion_error', [
                'mensaje' => 'Esta acta ya fue cerrada y no acepta nuevas inscripciones.'
            ]);
        }

        $cliente = (new ClientModel())->find($acta['id_cliente']);
        return view('inspecciones/acta_capacitacion/inscripcion_publica', [
            'token'   => $token,
            'acta'    => $acta,
            'cliente' => $cliente,
        ]);
    }

    public function procesarInscripcion()
    {
        $token  = trim((string)$this->request->getPost('token'));
        $nombre = trim((string)$this->request->getPost('nombre_completo'));
        $tipoDoc = trim((string)$this->request->getPost('tipo_documento')) ?: 'CC';
        $numDoc = trim((string)$this->request->getPost('numero_documento'));
        $cargo  = trim((string)$this->request->getPost('cargo'));
        $area   = trim((string)$this->request->getPost('area_dependencia'));
        $email  = trim((string)$this->request->getPost('email'));
        $celular = trim((string)$this->request->getPost('celular'));

        if (!$token || !$nombre || !$numDoc) {
            return $this->response->setJSON([
                'success' => false,
                'error'   => 'Nombre completo y numero de documento son obligatorios.',
            ]);
        }

        $acta = $this->actaModel->findByTokenInscripcion($token);
        if (!$acta) {
            return $this->response->setJSON(['success' => false, 'error' => 'Enlace invalido o expirado.']);
        }
        if ($acta['estado'] === 'completo') {
            return $this->response->setJSON(['success' => false, 'error' => 'Esta acta ya fue cerrada.']);
        }

        $existe = $this->asistenteModel
            ->where('id_acta_capacitacion', $acta['id'])
            ->where('numero_documento', $numDoc)
            ->first();
        if ($existe) {
            return $this->response->setJSON([
                'success' => false,
                'duplicado' => true,
                'error'   => 'Ya hay un asistente registrado con este numero de documento.',
            ]);
        }

        $ultimo = $this->asistenteModel
            ->select('MAX(orden) AS max_orden')
            ->where('id_acta_capacitacion', $acta['id'])
            ->first();
        $orden = isset($ultimo['max_orden']) ? ((int)$ultimo['max_orden']) + 1 : 1;

        $this->asistenteModel->insert([
            'id_acta_capacitacion' => $acta['id'],
            'nombre_completo'      => $nombre,
            'tipo_documento'       => $tipoDoc,
            'numero_documento'     => $numDoc,
            'cargo'                => $cargo ?: null,
            'area_dependencia'     => $area ?: null,
            'email'                => $email ?: null,
            'celular'              => $celular ?: null,
            'orden'                => $orden,
        ]);
        $idAsistente = (int)$this->asistenteModel->getInsertID();

        $tokenFirma = bin2hex(random_bytes(32));
        $this->asistenteModel->update($idAsistente, [
            'token_firma'      => $tokenFirma,
            'token_expiracion' => date('Y-m-d H:i:s', strtotime('+7 days')),
        ]);

        return $this->response->setJSON([
            'success' => true,
            'id_asistente' => $idAsistente,
            'url_firmar' => base_url("acta-capacitacion/firmar-remoto/{$tokenFirma}"),
        ]);
    }

    public function getAsistentesStatus(int $idActa)
    {
        $acta = $this->actaModel->find($idActa);
        if (!$acta) {
            return $this->response->setJSON(['success' => false, 'error' => 'Acta no encontrada']);
        }

        $asistentes = $this->asistenteModel->getByActa($idActa);
        $resumen = [];
        $firmados = 0;
        foreach ($asistentes as $a) {
            $tieneFirma = !empty($a['firma_path']);
            if ($tieneFirma) $firmados++;
            $resumen[] = [
                'id'              => (int)$a['id'],
                'nombre_completo' => $a['nombre_completo'],
                'firmado'         => $tieneFirma,
                'firmado_at'      => $a['firmado_at'] ?? null,
                'enlace_enviado'  => !$tieneFirma && !empty($a['token_firma']),
            ];
        }

        $total = count($resumen);
        return $this->response->setJSON([
            'success'  => true,
            'total'    => $total,
            'firmados' => $firmados,
            'pct'      => $total > 0 ? (int) round($firmados * 100 / $total) : 0,
            'asistentes' => $resumen,
        ]);
    }

    public function deleteAsistente(int $idActa, int $idAsistente)
    {
        $acta = $this->actaModel->find($idActa);
        if (!$acta) {
            return $this->response->setJSON(['success' => false, 'error' => 'Acta no encontrada']);
        }
        if ($acta['estado'] === 'completo') {
            return $this->response->setJSON(['success' => false, 'error' => 'Acta finalizada, no se puede modificar']);
        }

        $asistente = $this->asistenteModel->find($idAsistente);
        if (!$asistente || (int)$asistente['id_acta_capacitacion'] !== $idActa) {
            return $this->response->setJSON(['success' => false, 'error' => 'Asistente no encontrado en esta acta']);
        }
        if (!empty($asistente['firma_path']) || !empty($asistente['firmado_at'])) {
            return $this->response->setJSON(['success' => false, 'error' => 'No se puede eliminar: este asistente ya firmo']);
        }

        $this->asistenteModel->delete($idAsistente);
        return $this->response->setJSON(['success' => true]);
    }

    /**
     * Genera 1 o 2 PDFs:
     *   - 'acta' (FT-SST-252): siempre
     *   - 'responsabilidades' (FT-SST-003): solo si tipo_charla === 'induccion_reinduccion'
     */
    private function generarPdfInterno(int $id): array
    {
        $acta = $this->actaModel->find($id);
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $cliente = $clientModel->find($acta['id_cliente']);
        $consultor = $acta['id_consultor'] ? $consultantModel->find($acta['id_consultor']) : null;
        $asistentes = $this->asistenteModel->getByActa($id);

        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoBase64 = 'data:' . mime_content_type($logoPath) . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        $fotosBase64 = [];
        foreach (['foto_capacitacion','foto_otros_1','foto_otros_2'] as $campo) {
            $fotosBase64[$campo] = '';
            if (!empty($acta[$campo]) && file_exists(FCPATH . $acta[$campo])) {
                $fotosBase64[$campo] = $this->fotoABase64ParaPdf(FCPATH . $acta[$campo]);
            }
        }

        foreach ($asistentes as &$a) {
            $a['firma_base64'] = '';
            if (!empty($a['firma_path']) && file_exists(FCPATH . $a['firma_path'])) {
                $a['firma_base64'] = 'data:image/png;base64,' . base64_encode(file_get_contents(FCPATH . $a['firma_path']));
            }
        }
        unset($a);

        $pdfDir = 'uploads/inspecciones/actas_capacitacion/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) mkdir(FCPATH . $pdfDir, 0755, true);

        $resultado = ['acta' => null, 'responsabilidades' => null];

        $opts = new \Dompdf\Options();
        $opts->set('isRemoteEnabled', true);
        $opts->set('isHtml5ParserEnabled', true);

        // ---------- PDF 1: acta (FT-SST-252) ----------
        $html = view('inspecciones/acta_capacitacion/pdf', [
            'pdfType'      => 'acta',
            'acta'         => $acta,
            'cliente'      => $cliente,
            'consultor'    => $consultor,
            'realizadoPor' => null,
            'asistentes'   => $asistentes,
            'logoBase64'   => $logoBase64,
            'fotosBase64'  => $fotosBase64,
        ]);
        $dompdf = new Dompdf($opts);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();
        $pdfPath = $pdfDir . 'acta_capacitacion_' . $id . '_' . date('Ymd_His') . '.pdf';
        if (!empty($acta['ruta_pdf']) && file_exists(FCPATH . $acta['ruta_pdf'])) {
            unlink(FCPATH . $acta['ruta_pdf']);
        }
        file_put_contents(FCPATH . $pdfPath, $dompdf->output());
        $resultado['acta'] = $pdfPath;

        // ---------- PDF 2: responsabilidades (FT-SST-003) — solo inducción ----------
        if (($acta['tipo_charla'] ?? '') === 'induccion_reinduccion') {
            $html2 = view('inspecciones/acta_capacitacion/pdf', [
                'pdfType'      => 'responsabilidades',
                'acta'         => $acta,
                'cliente'      => $cliente,
                'consultor'    => $consultor,
                'realizadoPor' => null,
                'asistentes'   => $asistentes,
                'logoBase64'   => $logoBase64,
                'fotosBase64'  => $fotosBase64,
            ]);
            $dompdf2 = new Dompdf($opts);
            $dompdf2->loadHtml($html2);
            $dompdf2->setPaper('letter', 'portrait');
            $dompdf2->render();
            $pdfPath2 = $pdfDir . 'responsabilidades_sst_' . $id . '_' . date('Ymd_His') . '.pdf';
            if (!empty($acta['ruta_pdf_responsabilidades']) && file_exists(FCPATH . $acta['ruta_pdf_responsabilidades'])) {
                unlink(FCPATH . $acta['ruta_pdf_responsabilidades']);
            }
            file_put_contents(FCPATH . $pdfPath2, $dompdf2->output());
            $resultado['responsabilidades'] = $pdfPath2;
        }

        return $resultado;
    }

    private function uploadToReportes(array $acta, string $pdfPath): bool
    {
        $reporteModel = new ReporteModel();
        $clientModel = new ClientModel();
        $cliente = $clientModel->find($acta['id_cliente']);
        if (!$cliente) return false;

        $nitCliente = $cliente['nit_cliente'] ?? '';
        $destDir = ROOTPATH . 'public/uploads/' . $nitCliente;
        if (!is_dir($destDir)) mkdir($destDir, 0755, true);

        $fileName = 'acta_capacitacion_' . $acta['id'] . '_' . date('Ymd_His') . '.pdf';
        copy(FCPATH . $pdfPath, $destDir . '/' . $fileName);

        $data = [
            'titulo_reporte'  => 'ACTA DE CAPACITACION - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $acta['fecha_capacitacion'],
            'id_detailreport' => 6,
            'id_report_type'  => 4,
            'id_cliente'      => $acta['id_cliente'],
            'estado'          => 'CERRADO',
            'observaciones'   => 'Generado por consultor. acta_capacitacion_id:' . $acta['id'],
            'enlace'          => base_url('uploads/' . $nitCliente . '/' . $fileName),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        $existente = $reporteModel->where('id_cliente', $acta['id_cliente'])
            ->where('id_report_type', 4)
            ->where('id_detailreport', 6)
            ->like('observaciones', 'acta_capacitacion_id:' . $acta['id'])
            ->first();

        if ($existente) return $reporteModel->update($existente['id_reporte'], $data);
        $data['created_at'] = date('Y-m-d H:i:s');
        return $reporteModel->save($data);
    }

    /**
     * Sube el PDF FT-SST-003 (Responsabilidades SST) a tbl_reporte
     * con id_detailreport=35, idempotente por acta_cap_resp_id.
     */
    private function uploadResponsabilidadesToReportes(array $acta, string $pdfPath): bool
    {
        $reporteModel = new ReporteModel();
        $clientModel = new ClientModel();
        $cliente = $clientModel->find($acta['id_cliente']);
        if (!$cliente) return false;

        $nitCliente = $cliente['nit_cliente'] ?? '';
        $destDir = ROOTPATH . 'public/uploads/' . $nitCliente;
        if (!is_dir($destDir)) mkdir($destDir, 0755, true);

        $fileName = 'responsabilidades_sst_' . $acta['id'] . '_' . date('Ymd_His') . '.pdf';
        copy(FCPATH . $pdfPath, $destDir . '/' . $fileName);

        $data = [
            'titulo_reporte'  => 'RESPONSABILIDADES SST - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $acta['fecha_capacitacion'],
            'id_detailreport' => 35,
            'id_report_type'  => 4,
            'id_cliente'      => $acta['id_cliente'],
            'estado'          => 'CERRADO',
            'observaciones'   => 'Generado por consultor. acta_cap_resp_id:' . $acta['id'],
            'enlace'          => base_url('uploads/' . $nitCliente . '/' . $fileName),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        $existente = $reporteModel->where('id_cliente', $acta['id_cliente'])
            ->where('id_report_type', 4)
            ->where('id_detailreport', 35)
            ->like('observaciones', 'acta_cap_resp_id:' . $acta['id'])
            ->first();

        if ($existente) return $reporteModel->update($existente['id_reporte'], $data);
        $data['created_at'] = date('Y-m-d H:i:s');
        return $reporteModel->save($data);
    }

    /**
     * Sube una foto del form, la comprime y devuelve la ruta relativa.
     */
    private function uploadFoto(string $campo, string $dir): ?string
    {
        $file = $this->request->getFile($campo);
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return null;
        }

        if (!is_dir(FCPATH . $dir)) {
            mkdir(FCPATH . $dir, 0755, true);
        }

        $fileName = $file->getRandomName();
        $file->move(FCPATH . $dir, $fileName);
        $this->comprimirImagen(FCPATH . $dir . $fileName);
        return $dir . $fileName;
    }

    /**
     * API: cronogramas de capacitación pendientes del cliente (sin reporte vinculado).
     */
    public function apiCronogramasPendientes()
    {
        $idCliente = (int) $this->request->getGet('id_cliente');
        if (!$idCliente) {
            return $this->response->setJSON([]);
        }

        $cronogModel = new CronogcapacitacionModel();
        $idActa = (int) $this->request->getGet('id_acta');

        $cronogramas = $cronogModel
            ->where('id_cliente', $idCliente)
            ->where('estado', 'PROGRAMADA')
            ->groupStart()
                ->where('id_reporte_capacitacion IS NULL')
                ->orWhere('id_reporte_capacitacion', 0)
                ->orWhere('id_reporte_capacitacion', $idActa ?: 0)
            ->groupEnd()
            ->orderBy('fecha_programada', 'ASC')
            ->findAll();

        return $this->response->setJSON($cronogramas);
    }

    /**
     * API IA: genera el objetivo de la capacitación con OpenAI.
     */
    public function generarObjetivo()
    {
        $payload = $this->request->getJSON(true) ?? [];
        $nombre = trim((string)($payload['nombre_capacitacion'] ?? ''));

        if (!$nombre) {
            return $this->response->setJSON(['error' => 'Nombre vacío.'])->setStatusCode(400);
        }

        $apiKey = env('OPENAI_API_KEY');
        if (!$apiKey) {
            return $this->response->setJSON(['error' => 'API key no configurada.'])->setStatusCode(500);
        }

        $prompt = "Eres un experto en Seguridad y Salud en el Trabajo (SST) para propiedades horizontales colombianas (conjuntos residenciales y edificios). El personal capacitado son principalmente contratistas de aseo y vigilancia, y ocasionalmente la comunidad (residentes y administración).

Redacta el objetivo de la siguiente capacitación en SST: «{$nombre}».

El objetivo debe:
- Ser claro, concreto y profesional
- Estar en infinitivo (Capacitar, Sensibilizar, Fortalecer, etc.)
- Tener máximo 3 oraciones
- Mencionar el perfil del personal (contratistas de aseo, vigilancia o comunidad cuando aplique)
- No incluir títulos ni numeración, solo el texto del objetivo";

        $texto = $this->llamarOpenAI($prompt, 200, 0.6);
        if ($texto === null) {
            return $this->response->setJSON(['error' => 'Error al contactar la IA. Intenta de nuevo.'])->setStatusCode(500);
        }
        return $this->response->setJSON(['objetivo' => $texto]);
    }

    /**
     * API IA: genera el contenido temático de la capacitación con OpenAI.
     * Recibe nombre + objetivo y devuelve un temario en lista corta.
     */
    public function generarContenido()
    {
        $payload = $this->request->getJSON(true) ?? [];
        $nombre   = trim((string)($payload['nombre_capacitacion'] ?? ''));
        $objetivo = trim((string)($payload['objetivo_capacitacion'] ?? ''));

        if (!$nombre) {
            return $this->response->setJSON(['error' => 'Nombre vacío.'])->setStatusCode(400);
        }

        $apiKey = env('OPENAI_API_KEY');
        if (!$apiKey) {
            return $this->response->setJSON(['error' => 'API key no configurada.'])->setStatusCode(500);
        }

        $contextoObj = $objetivo ? "El objetivo de la capacitación es: «{$objetivo}»." : "";

        $prompt = "Eres un experto en Seguridad y Salud en el Trabajo (SST) para propiedades horizontales colombianas (conjuntos residenciales y edificios). El personal capacitado son principalmente contratistas de aseo y vigilancia, y ocasionalmente la comunidad.

Para la capacitación «{$nombre}». {$contextoObj}

Redacta el contenido temático en formato lista de 4 a 6 puntos clave, conciso y profesional.

El contenido debe:
- Tener entre 4 y 6 ítems numerados (1., 2., 3., ...)
- Cada ítem en una sola línea breve
- Ser específico al tema, no genérico
- No incluir títulos, encabezados ni introducción, solo la lista";

        $texto = $this->llamarOpenAI($prompt, 350, 0.6);
        if ($texto === null) {
            return $this->response->setJSON(['error' => 'Error al contactar la IA. Intenta de nuevo.'])->setStatusCode(500);
        }
        return $this->response->setJSON(['contenido' => $texto]);
    }

    /**
     * Helper compartido para llamar a OpenAI Chat Completions.
     */
    private function llamarOpenAI(string $prompt, int $maxTokens = 300, float $temperature = 0.6): ?string
    {
        $apiKey = env('OPENAI_API_KEY');
        if (!$apiKey) return null;

        $payload = json_encode([
            'model'       => env('OPENAI_MODEL', 'gpt-4o-mini'),
            'messages'    => [['role' => 'user', 'content' => $prompt]],
            'max_tokens'  => $maxTokens,
            'temperature' => $temperature,
        ]);

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
            CURLOPT_TIMEOUT        => 25,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!$response || $httpCode !== 200) {
            log_message('error', 'ActaCap llamarOpenAI HTTP ' . $httpCode . ': ' . $response);
            return null;
        }

        $data = json_decode($response, true);
        $texto = trim($data['choices'][0]['message']['content'] ?? '');
        return $texto ?: null;
    }

    /**
     * Sincroniza los datos del acta finalizada al cronograma de capacitación vinculado,
     * marcándolo como EJECUTADA con sus estadísticas.
     */
    private function syncToCronograma(array $acta): void
    {
        $idCronog = $acta['id_cronograma_capacitacion'] ?? null;
        if (!$idCronog) return;

        $cronogModel = new CronogcapacitacionModel();
        $cronograma = $cronogModel->find($idCronog);
        if (!$cronograma) return;

        $asistentes = $this->asistenteModel->getByActa((int)$acta['id']);
        $totalFirmados = 0;
        foreach ($asistentes as $a) {
            if (!empty($a['firma_path'])) $totalFirmados++;
        }

        $programados = (int) ($acta['numero_programados'] ?? 0);
        $cobertura = $programados > 0
            ? number_format(($totalFirmados / $programados) * 100, 2) . '%'
            : '0%';

        $cronogModel->update($idCronog, [
            'fecha_de_realizacion'                       => $acta['fecha_capacitacion'],
            'estado'                                     => 'EJECUTADA',
            'nombre_del_capacitador'                     => $acta['nombre_capacitador'] ?? '',
            'horas_de_duracion_de_la_capacitacion'       => $acta['hora_inicio'] && $acta['hora_fin']
                ? round((strtotime($acta['hora_fin']) - strtotime($acta['hora_inicio'])) / 3600, 1) . 'h'
                : '',
            'numero_de_asistentes_a_capacitacion'        => $totalFirmados,
            'numero_total_de_personas_programadas'       => $programados,
            'porcentaje_cobertura'                       => $cobertura,
            'numero_de_personas_evaluadas'               => (int) ($acta['numero_evaluados'] ?? 0),
            'promedio_de_calificaciones'                 => $acta['promedio_calificaciones'] ?? '',
            'observaciones'                              => $acta['observaciones'] ?? '',
            'id_reporte_capacitacion'                    => (int)$acta['id'],
        ]);
    }

    // ============================================================
    // STORE-BATCH: crear N actas hermanas (una por cada cronograma marcado)
    // ============================================================

    /**
     * Crea N actas hermanas, una por cada cronograma marcado con checkbox.
     * Datos comunes (cliente, fecha, asistentes esperados, fotos, observaciones,
     * capacitador, etc.) se copian a todos. Cada acta queda en estado borrador
     * vinculada a su cronograma. Redirige a la vista lote.
     */
    public function storeBatch()
    {
        $userId = session()->get('user_id');

        $idCliente   = (int) $this->request->getPost('id_cliente');
        $fecha       = $this->request->getPost('fecha_capacitacion');
        $idCronogs   = $this->request->getPost('id_cronogramas');
        $idCronogs   = is_array($idCronogs) ? array_map('intval', $idCronogs) : [];
        $idCronogs   = array_values(array_unique(array_filter($idCronogs)));

        if (!$idCliente || !$fecha) {
            return redirect()->back()->withInput()->with('error', 'Cliente y fecha son obligatorios.');
        }

        if (empty($idCronogs)) {
            // Sin checkboxes — flujo legacy: 1 acta sin cronograma vinculado
            return $this->store();
        }

        $cronogModel = new CronogcapacitacionModel();
        $cronogramas = $cronogModel->whereIn('id_cronograma_capacitacion', $idCronogs)->findAll();
        if (empty($cronogramas)) {
            return redirect()->back()->withInput()->with('error', 'Cronogramas no encontrados.');
        }

        $idComite = $this->request->getPost('id_comite');

        // Datos comunes
        $comun = [
            'id_cliente'              => $idCliente,
            'id_comite'               => $idComite ? (int)$idComite : null,
            'creado_por_tipo'         => 'consultor',
            'id_consultor'            => $userId,
            'fecha_capacitacion'      => $fecha,
            'hora_inicio'             => $this->request->getPost('hora_inicio') ?: null,
            'hora_fin'                => $this->request->getPost('hora_fin') ?: null,
            'dictada_por'             => $this->request->getPost('dictada_por') ?: 'ARL',
            'nombre_capacitador'      => $this->request->getPost('nombre_capacitador'),
            'entidad_capacitadora'    => $this->request->getPost('entidad_capacitadora'),
            'modalidad'               => $this->request->getPost('modalidad') ?: 'virtual',
            'tipo_charla'             => $this->request->getPost('tipo_charla') ?: 'capacitacion',
            'enlace_grabacion'        => $this->request->getPost('enlace_grabacion'),
            'objetivos'               => $this->request->getPost('objetivos'),
            'contenido'               => $this->request->getPost('contenido'),
            'observaciones'           => $this->request->getPost('observaciones'),
            'numero_programados'      => $this->request->getPost('numero_programados') !== null && $this->request->getPost('numero_programados') !== ''
                                          ? (int) $this->request->getPost('numero_programados') : null,
            'numero_evaluados'        => $this->request->getPost('numero_evaluados') !== null && $this->request->getPost('numero_evaluados') !== ''
                                          ? (int) $this->request->getPost('numero_evaluados') : null,
            'promedio_calificaciones' => $this->request->getPost('promedio_calificaciones') !== null && $this->request->getPost('promedio_calificaciones') !== ''
                                          ? $this->request->getPost('promedio_calificaciones') : null,
            'estado'                  => 'borrador',
        ];

        // Fotos: subir y luego copiar para que cada acta tenga las suyas
        $dirFotos = 'uploads/inspecciones/acta-capacitacion/';
        $fotoCap  = $this->uploadFoto('foto_capacitacion', $dirFotos);
        $fotoOtr1 = $this->uploadFoto('foto_otros_1',     $dirFotos);
        $fotoOtr2 = $this->uploadFoto('foto_otros_2',     $dirFotos);

        $idsCreados = [];
        foreach ($cronogramas as $cronog) {
            $data = $comun;
            $data['id_cronograma_capacitacion'] = (int) $cronog['id_cronograma_capacitacion'];
            $data['tema']                       = $cronog['nombre_capacitacion'] ?? ($this->request->getPost('tema') ?: 'Capacitación');
            // Si el cronograma tiene objetivo y el form no, usarlo
            if (empty($data['objetivos']) && !empty($cronog['objetivo_capacitacion'])) {
                $data['objetivos'] = $cronog['objetivo_capacitacion'];
            }
            // Capacitador / horas: si no se digitó, usar lo del cronograma
            if (empty($data['nombre_capacitador']) && !empty($cronog['nombre_del_capacitador'])) {
                $data['nombre_capacitador'] = $cronog['nombre_del_capacitador'];
            }
            // Programados: si no se digitó, usar el del cronograma
            if (empty($data['numero_programados']) && !empty($cronog['numero_total_de_personas_programadas'])) {
                $data['numero_programados'] = (int) $cronog['numero_total_de_personas_programadas'];
            }

            // Copiar fotos físicamente
            $data['foto_capacitacion'] = $this->copiarArchivo($fotoCap);
            $data['foto_otros_1']      = $this->copiarArchivo($fotoOtr1);
            $data['foto_otros_2']      = $this->copiarArchivo($fotoOtr2);

            $this->actaModel->insert($data);
            $idsCreados[] = (int) $this->actaModel->getInsertID();
        }

        // Borrar las fotos originales (ya copiadas)
        foreach ([$fotoCap, $fotoOtr1, $fotoOtr2] as $orig) {
            if ($orig && file_exists(FCPATH . $orig)) {
                @unlink(FCPATH . $orig);
            }
        }

        if (count($idsCreados) === 1) {
            return redirect()->to('/inspecciones/acta-capacitacion/edit/' . $idsCreados[0])
                ->with('msg', 'Acta creada vinculada al cronograma.');
        }

        return redirect()->to('/inspecciones/acta-capacitacion/lote/' . implode(',', $idsCreados))
            ->with('msg', count($idsCreados) . ' actas creadas, una por cada capacitación seleccionada.');
    }

    /**
     * Vista de lote: muestra la lista de N actas hermanas recién creadas.
     */
    public function lote(string $idsCsv)
    {
        $ids = array_map('intval', array_filter(explode(',', $idsCsv)));
        if (empty($ids)) {
            return redirect()->to('/inspecciones/acta-capacitacion');
        }

        $actas = $this->actaModel
            ->select('tbl_acta_capacitacion.*, tbl_cronog_capacitacion.nombre_capacitacion AS cronog_nombre, tbl_cronog_capacitacion.fecha_programada')
            ->join('tbl_cronog_capacitacion', 'tbl_cronog_capacitacion.id_cronograma_capacitacion = tbl_acta_capacitacion.id_cronograma_capacitacion', 'left')
            ->whereIn('tbl_acta_capacitacion.id', $ids)
            ->orderBy('tbl_acta_capacitacion.id', 'ASC')
            ->findAll();

        $cliente = !empty($actas) ? (new ClientModel())->find($actas[0]['id_cliente']) : null;

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/acta_capacitacion/lote', [
                'actas'   => $actas,
                'cliente' => $cliente,
            ]),
            'title'   => 'Lote de capacitaciones del día',
        ]);
    }

    /**
     * Copia un archivo subido a un nombre nuevo único en el mismo directorio.
     */
    private function copiarArchivo(?string $rutaRelativa): ?string
    {
        if (!$rutaRelativa) return null;
        $origAbs = FCPATH . $rutaRelativa;
        if (!file_exists($origAbs)) return null;

        $info = pathinfo($rutaRelativa);
        $nuevoNombre = uniqid('ac_', true) . '.' . ($info['extension'] ?? 'jpg');
        $nuevoRel    = $info['dirname'] . '/' . $nuevoNombre;
        $nuevoAbs    = FCPATH . $nuevoRel;

        if (!@copy($origAbs, $nuevoAbs)) return $rutaRelativa;
        return $nuevoRel;
    }

    /**
     * Calcula el puntaje promedio de evaluación que corresponde a una capacitación
     * usando match semántico con IA (OpenAI gpt-4o-mini) entre el tema del acta
     * y los temas evaluados ese día (±7d) para el cliente.
     *
     * @return float|null  Promedio (0-100) o null si no hay match.
     */
    private function calcularPuntajeIA(int $idCliente, string $fecha, string $tema): ?float
    {
        $tema = trim($tema);
        if (!$tema) return null;

        $db = \Config\Database::connect();
        $fechaDesde = date('Y-m-d', strtotime($fecha . ' -7 days'));
        $fechaHasta = date('Y-m-d', strtotime($fecha . ' +7 days'));

        $rows = $db->table('tbl_evaluacion_respuestas r')
            ->select('r.id_evaluacion, t.id AS id_tema, t.nombre AS tema_nombre, AVG(r.calificacion) AS promedio, COUNT(*) AS n')
            ->join('tbl_evaluaciones e', 'e.id = r.id_evaluacion', 'left')
            ->join('tbl_evaluacion_tema t', 't.id = e.id_tema', 'left')
            ->where('r.id_cliente_conjunto', $idCliente)
            ->where('DATE(r.created_at) >=', $fechaDesde)
            ->where('DATE(r.created_at) <=', $fechaHasta)
            ->where('t.nombre IS NOT NULL')
            ->groupBy('r.id_evaluacion, t.id, t.nombre')
            ->get()->getResultArray();

        if (empty($rows)) return null;

        // Si solo hay 1 tema, match directo (no gastar IA)
        if (count($rows) === 1) {
            return round((float) $rows[0]['promedio'], 2);
        }

        $temas = array_column($rows, 'tema_nombre');
        $listaTemas = "- " . implode("\n- ", $temas);

        $prompt = "Eres un asistente que matchea nombres de capacitaciones SST con temas de evaluación.

Capacitación dictada: «{$tema}»

Temas evaluados disponibles:
{$listaTemas}

Devuelve SOLO el texto exacto del tema que mejor corresponde a la capacitación dictada. Si NINGUNO corresponde semánticamente, responde exactamente: NONE

Sin comillas, sin explicación, solo el texto del tema o NONE.";

        $match = $this->llamarOpenAI($prompt, 80, 0.0);
        if (!$match || strtoupper(trim($match)) === 'NONE') return null;
        $match = trim($match);

        foreach ($rows as $r) {
            if (strcasecmp(trim($r['tema_nombre']), $match) === 0) {
                return round((float) $r['promedio'], 2);
            }
        }
        return null;
    }
}
