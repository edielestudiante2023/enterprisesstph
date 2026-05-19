<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\ActaCapacitacionModel;
use App\Models\ActaCapacitacionAsistenteModel;
use App\Models\ActaCronogramaModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use App\Models\CronogcapacitacionModel;
use App\Libraries\InspeccionEmailNotifier;
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
    protected ActaCronogramaModel $vinculoModel;

    public function __construct()
    {
        $this->actaModel = new ActaCapacitacionModel();
        $this->asistenteModel = new ActaCapacitacionAsistenteModel();
        $this->vinculoModel = new ActaCronogramaModel();
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
            'title'   => 'Reportes de Capacitación',
        ]);
    }

    public function create($idCliente = null)
    {
        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/acta_capacitacion/form', [
                'title'      => 'Nuevo Reporte de Capacitación',
                'acta'       => null,
                'asistentes' => [],
                'idCliente'  => $idCliente,
                'contexto'   => 'consultor',
            ]),
            'title' => 'Nuevo Reporte de Capacitación',
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
            'observaciones'              => $this->request->getPost('observaciones'),
            'numero_programados'         => $this->request->getPost('numero_programados') !== null && $this->request->getPost('numero_programados') !== ''
                                              ? (int) $this->request->getPost('numero_programados') : null,
            'estado'                     => 'borrador',
        ];

        $dirFotos = 'uploads/inspecciones/acta-capacitacion/';
        foreach (['foto_capacitacion','foto_otros_1','foto_otros_2'] as $campo) {
            $nueva = $this->uploadFoto($campo, $dirFotos);
            if ($nueva) $data[$campo] = $nueva;
        }

        $this->actaModel->insert($data);
        $idActa = $this->actaModel->getInsertID();

        // Sincronizar cronogramas vinculados (tabla puente N:M)
        $idCronogs = $this->request->getPost('id_cronogramas');
        if (is_array($idCronogs) && !empty($idCronogs)) {
            $this->vinculoModel->syncForActa((int)$idActa, $idCronogs);
        }

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
                'title'        => 'Editar Reporte de Capacitación',
                'acta'         => $acta,
                'asistentes'   => $this->asistenteModel->getByActa((int)$id),
                'vinculos'     => $this->vinculoModel->getByActa((int)$id),
                'idCronogIds'  => $this->vinculoModel->getIdsCronogramaByActa((int)$id),
                'idCliente'    => $acta['id_cliente'],
                'contexto'     => 'consultor',
            ]),
            'title' => 'Editar Reporte de Capacitación',
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
            'observaciones'              => $this->request->getPost('observaciones'),
            'numero_programados'         => $this->request->getPost('numero_programados') !== null && $this->request->getPost('numero_programados') !== ''
                                              ? (int) $this->request->getPost('numero_programados') : null,
        ];

        $dirFotos = 'uploads/inspecciones/acta-capacitacion/';
        foreach (['foto_capacitacion','foto_otros_1','foto_otros_2'] as $campo) {
            if ($this->request->getPost($campo . '__delete') === '1') {
                if (!empty($acta[$campo]) && file_exists(FCPATH . $acta[$campo])) {
                    unlink(FCPATH . $acta[$campo]);
                }
                $data[$campo] = null;
            }
            $nueva = $this->uploadFoto($campo, $dirFotos);
            if ($nueva) {
                if (!empty($acta[$campo]) && file_exists(FCPATH . $acta[$campo])) {
                    unlink(FCPATH . $acta[$campo]);
                }
                $data[$campo] = $nueva;
            }
        }

        $this->actaModel->update($id, $data);

        // Sincronizar cronogramas vinculados (solo si vienen — autosave puede no enviarlos)
        $idCronogs = $this->request->getPost('id_cronogramas');
        if (is_array($idCronogs)) {
            $this->vinculoModel->syncForActa((int)$id, $idCronogs);
        }

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
                'vinculos'     => $this->vinculoModel->getByActa((int)$id),
                'contexto'     => 'consultor',
            ]),
            'title' => 'Ver Reporte de Capacitación',
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
                <h2 style='color: white; margin: 0;'>Solicitud de Firma - Reporte de Capacitación</h2>
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
                        Firmar Reporte de Capacitación
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

        $vinculos = $this->vinculoModel->getByActa((int)$id);

        // ─── Modo legacy: acta sin cronogramas vinculados → 1 PDF genérico ───
        if (empty($vinculos)) {
            $result = $this->generarPdfInterno((int)$id, null);
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

            // Email al cliente + consultor con el PDF adjunto (modo legacy)
            InspeccionEmailNotifier::enviar(
                (int) $acta['id_cliente'],
                (int) $acta['id_consultor'],
                'REPORTE DE CAPACITACIÓN',
                $acta['fecha_capacitacion'],
                $result['acta'],
                (int) $acta['id'],
                'ActaCapacitacion'
            );
            if (!empty($result['responsabilidades'])) {
                InspeccionEmailNotifier::enviar(
                    (int) $acta['id_cliente'],
                    (int) $acta['id_consultor'],
                    'RESPONSABILIDADES SST',
                    $acta['fecha_capacitacion'],
                    $result['responsabilidades'],
                    (int) $acta['id'],
                    'ActaCapacitacion'
                );
            }

            return redirect()->to('/inspecciones/acta-capacitacion/view/' . $id)
                ->with('msg', 'Reporte finalizado. PDF genérico generado (sin cronograma vinculado).');
        }

        // ─── Modo nuevo: acta con N cronogramas → N PDFs (uno por capacitación) ───
        $generadosOk = 0;
        $errores = [];
        foreach ($vinculos as $v) {
            $idVinculo  = (int)$v['id_acta_cronograma'];
            $idCronog   = (int)$v['id_cronograma'];
            $nombreCap  = $v['nombre_capacitacion'] ?? ($acta['tema'] ?? 'Capacitación');

            // Detectar si esta capacitación es Inducción/Reinducción → genera FT-SST-003
            $nombreNorm = mb_strtolower($nombreCap);
            $esInduccion = (strpos($nombreNorm, 'inducción') !== false)
                        || (strpos($nombreNorm, 'induccion') !== false)
                        || (strpos($nombreNorm, 'reinducción') !== false)
                        || (strpos($nombreNorm, 'reinduccion') !== false);

            // 1. Generar objetivo IA específico para esta capacitación
            $objetivoIa = $this->generarObjetivoIaInterno($nombreCap);

            // 2. Calcular puntaje IA (cliente + tema=nombre_capacitacion)
            $puntajeData = $this->calcularPuntajeIA(
                (int) $acta['id_cliente'],
                $acta['fecha_capacitacion'],
                $nombreCap
            );
            $promedio       = $puntajeData['promedio']        ?? null;
            $evaluados      = $puntajeData['evaluados']       ?? null;
            $idEvaluacion   = $puntajeData['id_evaluacion']   ?? null;
            $temaEvaluacion = $puntajeData['tema_evaluacion'] ?? null;

            // 2b. Traer respuestas individuales de la evaluación matcheada (para el PDF)
            // Filtramos por cliente + fecha del acta para no acumular respuestas históricas.
            $respuestasEval = $idEvaluacion
                ? $this->getRespuestasEvaluacion(
                    (int) $idEvaluacion,
                    (int) ($acta['id_cliente'] ?? 0) ?: null,
                    $acta['fecha_capacitacion'] ?? null
                )
                : [];

            // 3. Persistir IA del vínculo (objetivo + promedio + evaluados)
            $this->vinculoModel->update($idVinculo, [
                'objetivo_ia'             => $objetivoIa,
                'promedio_calificaciones' => $promedio,
                'numero_evaluados'        => $evaluados,
            ]);

            // 4. Generar PDF específico de esta capacitación.
            //    Si es inducción, además genera el FT-SST-003 (responsabilidades).
            $cronogramaCtx = [
                'id_cronograma'       => $idCronog,
                'nombre_capacitacion' => $nombreCap,
                'objetivo_ia'         => $objetivoIa,
                'promedio'            => $promedio,
                'evaluados'           => $evaluados,
                'tema_evaluacion'     => $temaEvaluacion,
                'respuestas_eval'     => $respuestasEval,
            ];
            $result = $this->generarPdfInterno((int)$id, $cronogramaCtx, $esInduccion);
            if (empty($result['acta'])) {
                $errores[] = $nombreCap;
                continue;
            }

            // 5. Guardar ruta_pdf en el vínculo
            $this->vinculoModel->update($idVinculo, ['ruta_pdf' => $result['acta']]);

            // 6. Subir a tbl_reportes (uno por PDF, idempotente por id_vinculo)
            $this->uploadToReportes($acta, $result['acta'], $idVinculo, $nombreCap);
            if (!empty($result['responsabilidades'])) {
                $this->uploadResponsabilidadesToReportes($acta, $result['responsabilidades'], $idVinculo, $nombreCap);
            }

            // 7. Sync al cronograma específico (estado=EJECUTADA)
            $this->syncToCronogramaIndividual(
                (int)$idCronog,
                $acta,
                (int)($evaluados ?? 0),
                $promedio
            );

            // 8. Enviar email al cliente + consultor con el PDF adjunto
            //    (uno por capacitación, igual que el patrón de ActaVisita)
            InspeccionEmailNotifier::enviar(
                (int) $acta['id_cliente'],
                (int) $acta['id_consultor'],
                'REPORTE DE CAPACITACIÓN - ' . $nombreCap,
                $acta['fecha_capacitacion'],
                $result['acta'],
                (int) $acta['id'],
                'ActaCapacitacion'
            );

            // 9. Si la capacitación es Inducción/Reinducción, enviar también el FT-SST-003
            if (!empty($result['responsabilidades'])) {
                InspeccionEmailNotifier::enviar(
                    (int) $acta['id_cliente'],
                    (int) $acta['id_consultor'],
                    'RESPONSABILIDADES SST - ' . $nombreCap,
                    $acta['fecha_capacitacion'],
                    $result['responsabilidades'],
                    (int) $acta['id'],
                    'ActaCapacitacion'
                );
            }

            $generadosOk++;
        }

        $this->actaModel->update($id, ['estado' => 'completo']);

        $msg = "Acta finalizada. {$generadosOk} PDF(s) generado(s).";
        if (!empty($errores)) $msg .= ' Errores: ' . implode(', ', $errores);

        return redirect()->to('/inspecciones/acta-capacitacion/view/' . $id)->with('msg', $msg);
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
     *   - 'responsabilidades' (FT-SST-003): si $forzarResponsabilidades (modo nuevo, decisión por vínculo)
     *      o si acta.tipo_charla === 'induccion_reinduccion' (modo legacy sin cronograma)
     *
     * @param int        $id                       ID del acta
     * @param array|null $cronogramaCtx            Contexto del cronograma cuando se genera 1 PDF por capacitación.
     *                                             Claves: id_cronograma, nombre_capacitacion, objetivo_ia, promedio, evaluados.
     * @param bool       $forzarResponsabilidades  Si true, genera el FT-SST-003 (decisión de finalizar por nombre del cronograma).
     */
    private function generarPdfInterno(int $id, ?array $cronogramaCtx = null, bool $forzarResponsabilidades = false): array
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

        // Vigencia del documento = fecha de inicio del contrato más reciente del cliente.
        // Fallback a fecha_capacitacion si no hay contrato.
        $vigenciaContrato = $acta['fecha_capacitacion'];
        $db = \Config\Database::connect();
        $contrato = $db->table('tbl_contratos')
            ->select('fecha_inicio')
            ->where('id_cliente', (int)$acta['id_cliente'])
            ->where('fecha_inicio IS NOT NULL', null, false)
            ->orderBy('fecha_inicio', 'DESC')
            ->limit(1)
            ->get()->getRowArray();
        if ($contrato && !empty($contrato['fecha_inicio'])) {
            $vigenciaContrato = $contrato['fecha_inicio'];
        }

        $fotosBase64 = [];
        foreach (['foto_capacitacion','foto_otros_1','foto_otros_2'] as $campo) {
            $fotosBase64[$campo] = '';
            if (!empty($acta[$campo]) && file_exists(FCPATH . $acta[$campo])) {
                $fotosBase64[$campo] = $this->fotoABase64ParaPdf(FCPATH . $acta[$campo]);
            }
        }

        // Firmas: doble fallback. base64 + path absoluto (DOMPDF a veces falla con data URIs muy largos).
        foreach ($asistentes as &$a) {
            $a['firma_base64']    = '';
            $a['firma_full_path'] = '';
            if (!empty($a['firma_path']) && file_exists(FCPATH . $a['firma_path'])) {
                $absPath = FCPATH . $a['firma_path'];
                $a['firma_full_path'] = $absPath;
                $a['firma_base64']    = 'data:image/png;base64,' . base64_encode(file_get_contents($absPath));
            }
        }
        unset($a);

        $pdfDir = 'uploads/inspecciones/actas_capacitacion/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) mkdir(FCPATH . $pdfDir, 0755, true);

        $resultado = ['acta' => null, 'responsabilidades' => null];

        $opts = new \Dompdf\Options();
        $opts->set('isRemoteEnabled', true);
        $opts->set('isHtml5ParserEnabled', true);

        // Sufijo en nombre de archivo (incluye cronograma si aplica)
        $sufijoCronog = $cronogramaCtx ? ('_c' . (int)$cronogramaCtx['id_cronograma']) : '';

        // ---------- PDF 1: acta (FT-SST-252) ----------
        $html = view('inspecciones/acta_capacitacion/pdf', [
            'pdfType'        => 'acta',
            'acta'           => $acta,
            'cliente'        => $cliente,
            'consultor'      => $consultor,
            'realizadoPor'   => null,
            'asistentes'     => $asistentes,
            'logoBase64'      => $logoBase64,
            'fotosBase64'     => $fotosBase64,
            'cronogramaCtx'   => $cronogramaCtx,
            'vigenciaContrato'=> $vigenciaContrato,
        ]);
        $dompdf = new Dompdf($opts);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();
        $pdfPath = $pdfDir . 'acta_capacitacion_' . $id . $sufijoCronog . '_' . date('Ymd_His') . '.pdf';
        file_put_contents(FCPATH . $pdfPath, $dompdf->output());
        $resultado['acta'] = $pdfPath;

        // ---------- PDF 2: responsabilidades (FT-SST-003) — solo inducción ----------
        // Modo nuevo: decisión por vínculo (la toma finalizar() por nombre del cronograma).
        // Modo legacy (sin cronogramaCtx): mantiene el flag tipo_charla del acta.
        $generarResponsabilidades = $forzarResponsabilidades
            || ($cronogramaCtx === null && ($acta['tipo_charla'] ?? '') === 'induccion_reinduccion');
        if ($generarResponsabilidades) {
            // Vista dedicada FT-SST-003 — contenido fijo de responsabilidades SST.
            $html2 = view('inspecciones/acta_capacitacion/pdf_responsabilidades', [
                'acta'             => $acta,
                'cliente'          => $cliente,
                'consultor'        => $consultor,
                'asistentes'       => $asistentes,
                'logoBase64'       => $logoBase64,
                'vigenciaContrato' => $vigenciaContrato,
            ]);
            $dompdf2 = new Dompdf($opts);
            $dompdf2->loadHtml($html2);
            $dompdf2->setPaper('letter', 'portrait');
            $dompdf2->render();
            $pdfPath2 = $pdfDir . 'responsabilidades_sst_' . $id . $sufijoCronog . '_' . date('Ymd_His') . '.pdf';
            file_put_contents(FCPATH . $pdfPath2, $dompdf2->output());
            $resultado['responsabilidades'] = $pdfPath2;
        }

        return $resultado;
    }

    /**
     * Genera el objetivo SST con OpenAI dado el nombre de la capacitación.
     * Reusa el mismo prompt que generarObjetivo() público pero como helper interno.
     */
    private function generarObjetivoIaInterno(string $nombreCapacitacion): string
    {
        $nombre = trim($nombreCapacitacion);
        if (!$nombre) return '';

        $prompt = "Eres un experto en Seguridad y Salud en el Trabajo (SST) para propiedades horizontales colombianas (conjuntos residenciales y edificios). El personal capacitado son principalmente contratistas de aseo y vigilancia, y ocasionalmente la comunidad (residentes y administración).

Redacta el objetivo de la siguiente capacitación en SST: «{$nombre}».

El objetivo debe:
- Ser claro, concreto y profesional
- Estar en infinitivo (Capacitar, Sensibilizar, Fortalecer, etc.)
- Tener máximo 3 oraciones
- Mencionar el perfil del personal (contratistas de aseo, vigilancia o comunidad cuando aplique)
- No incluir títulos ni numeración, solo el texto del objetivo";

        $texto = $this->llamarOpenAI($prompt, 200, 0.6);
        return $texto ?: '';
    }

    /**
     * Sync individual a tbl_cronog_capacitacion para UN cronograma específico.
     * Llamado por finalizar() una vez por cada vínculo del acta.
     */
    private function syncToCronogramaIndividual(int $idCronog, array $acta, int $evaluados, ?float $promedio): void
    {
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
            'numero_de_personas_evaluadas'               => $evaluados,
            'promedio_de_calificaciones'                 => $promedio !== null ? (string)$promedio : '',
            'observaciones'                              => $acta['observaciones'] ?? '',
            'id_reporte_capacitacion'                    => (int)$acta['id'],
        ]);
    }

    /**
     * Sube PDF a tbl_reporte. Si se pasa $idVinculo + $tituloCapacitacion, el reporte
     * se identifica por vínculo (un reporte por cada capacitación dentro del acta).
     * En modo legacy (sin idVinculo) usa el id del acta como identificador.
     */
    private function uploadToReportes(array $acta, string $pdfPath, ?int $idVinculo = null, ?string $tituloCapacitacion = null): bool
    {
        $reporteModel = new ReporteModel();
        $clientModel = new ClientModel();
        $cliente = $clientModel->find($acta['id_cliente']);
        if (!$cliente) return false;

        $nitCliente = $cliente['nit_cliente'] ?? '';
        $destDir = ROOTPATH . 'public/uploads/' . $nitCliente;
        if (!is_dir($destDir)) mkdir($destDir, 0755, true);

        $fileName = 'acta_capacitacion_' . $acta['id']
            . ($idVinculo ? '_v' . $idVinculo : '')
            . '_' . date('Ymd_His') . '.pdf';
        copy(FCPATH . $pdfPath, $destDir . '/' . $fileName);

        $marcadorIdempotencia = $idVinculo
            ? 'acta_cap_vinculo_id:' . $idVinculo
            : 'acta_capacitacion_id:' . $acta['id'];

        $titulo = $tituloCapacitacion
            ? 'REPORTE DE CAPACITACION - ' . $tituloCapacitacion . ' - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $acta['fecha_capacitacion']
            : 'REPORTE DE CAPACITACION - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $acta['fecha_capacitacion'];

        $data = [
            'titulo_reporte'  => $titulo,
            'id_detailreport' => 18,
            'id_report_type'  => 4,
            'id_cliente'      => $acta['id_cliente'],
            'estado'          => 'CERRADO',
            'observaciones'   => 'Generado por consultor. ' . $marcadorIdempotencia,
            'enlace'          => base_url('uploads/' . $nitCliente . '/' . $fileName),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        $existente = $reporteModel->where('id_cliente', $acta['id_cliente'])
            ->where('id_report_type', 4)
            ->like('observaciones', $marcadorIdempotencia)
            ->first();

        if ($existente) return $reporteModel->update($existente['id_reporte'], $data);
        $data['created_at'] = date('Y-m-d H:i:s');
        return $reporteModel->save($data);
    }

    /**
     * Sube el PDF FT-SST-003 (Responsabilidades SST) a tbl_reporte
     * con id_detailreport=35. Idempotente por vínculo si se pasa $idVinculo,
     * o por acta en modo legacy.
     */
    private function uploadResponsabilidadesToReportes(array $acta, string $pdfPath, ?int $idVinculo = null, ?string $tituloCapacitacion = null): bool
    {
        $reporteModel = new ReporteModel();
        $clientModel = new ClientModel();
        $cliente = $clientModel->find($acta['id_cliente']);
        if (!$cliente) return false;

        $nitCliente = $cliente['nit_cliente'] ?? '';
        $destDir = ROOTPATH . 'public/uploads/' . $nitCliente;
        if (!is_dir($destDir)) mkdir($destDir, 0755, true);

        $fileName = 'responsabilidades_sst_' . $acta['id']
            . ($idVinculo ? '_v' . $idVinculo : '')
            . '_' . date('Ymd_His') . '.pdf';
        copy(FCPATH . $pdfPath, $destDir . '/' . $fileName);

        $marcadorIdempotencia = $idVinculo
            ? 'acta_cap_resp_vinculo_id:' . $idVinculo
            : 'acta_cap_resp_id:' . $acta['id'];

        $titulo = $tituloCapacitacion
            ? 'RESPONSABILIDADES SST - ' . $tituloCapacitacion . ' - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $acta['fecha_capacitacion']
            : 'RESPONSABILIDADES SST - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $acta['fecha_capacitacion'];

        $data = [
            'titulo_reporte'  => $titulo,
            'id_detailreport' => 35,
            'id_report_type'  => 4,
            'id_cliente'      => $acta['id_cliente'],
            'estado'          => 'CERRADO',
            'observaciones'   => 'Generado por consultor. ' . $marcadorIdempotencia,
            'enlace'          => base_url('uploads/' . $nitCliente . '/' . $fileName),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        $existente = $reporteModel->where('id_cliente', $acta['id_cliente'])
            ->where('id_report_type', 4)
            ->like('observaciones', $marcadorIdempotencia)
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
     * API: evaluaciones registradas para un cliente y fecha (±7d).
     * Devuelve resumen por tema + listado de respuestas individuales.
     * Usado por el form de acta-capacitacion para que el consultor vea
     * antes de finalizar quiénes ya hicieron evaluación y con qué nota.
     */
    public function apiEvaluacionesDelDia()
    {
        $idCliente = (int) $this->request->getGet('id_cliente');
        $fecha     = $this->request->getGet('fecha');

        if (!$idCliente || !$fecha) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Cliente y fecha requeridos.']);
        }

        $db = \Config\Database::connect();
        $fechaDesde = date('Y-m-d', strtotime($fecha . ' -7 days'));
        $fechaHasta = date('Y-m-d', strtotime($fecha . ' +7 days'));

        // Resumen por tema (lo que la IA usará después en el match)
        $resumen = $db->table('tbl_evaluacion_respuestas r')
            ->select('t.nombre AS tema, COUNT(*) AS total, AVG(r.calificacion) AS promedio')
            ->join('tbl_evaluaciones e', 'e.id = r.id_evaluacion', 'left')
            ->join('tbl_evaluacion_tema t', 't.id = e.id_tema', 'left')
            ->where('r.id_cliente_conjunto', $idCliente)
            ->where('DATE(r.created_at) >=', $fechaDesde)
            ->where('DATE(r.created_at) <=', $fechaHasta)
            ->where('t.nombre IS NOT NULL')
            ->groupBy('t.nombre')
            ->orderBy('t.nombre', 'ASC')
            ->get()->getResultArray();

        // Detalle por persona (todas las respuestas en el rango)
        $detalle = $db->table('tbl_evaluacion_respuestas r')
            ->select('r.nombre, r.cedula, r.cargo, r.calificacion, t.nombre AS tema, r.created_at')
            ->join('tbl_evaluaciones e', 'e.id = r.id_evaluacion', 'left')
            ->join('tbl_evaluacion_tema t', 't.id = e.id_tema', 'left')
            ->where('r.id_cliente_conjunto', $idCliente)
            ->where('DATE(r.created_at) >=', $fechaDesde)
            ->where('DATE(r.created_at) <=', $fechaHasta)
            ->orderBy('t.nombre', 'ASC')
            ->orderBy('r.calificacion', 'DESC')
            ->get()->getResultArray();

        return $this->response->setJSON([
            'success'     => true,
            'total'       => count($detalle),
            'temas_count' => count($resumen),
            'resumen'     => $resumen,
            'detalle'     => $detalle,
        ]);
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
     * Calcula el puntaje promedio + count de evaluados + id_evaluacion matcheada
     * usando match semántico con IA (OpenAI gpt-4o-mini) entre el tema del acta
     * y los temas evaluados ese día (±7d) para el cliente.
     *
     * @return array{promedio: float, evaluados: int, id_evaluacion: int, tema_evaluacion: string}|null
     */
    private function calcularPuntajeIA(int $idCliente, string $fecha, string $tema): ?array
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
            return [
                'promedio'        => round((float) $rows[0]['promedio'], 2),
                'evaluados'       => (int) $rows[0]['n'],
                'id_evaluacion'   => (int) $rows[0]['id_evaluacion'],
                'tema_evaluacion' => $rows[0]['tema_nombre'],
            ];
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
                return [
                    'promedio'        => round((float) $r['promedio'], 2),
                    'evaluados'       => (int) $r['n'],
                    'id_evaluacion'   => (int) $r['id_evaluacion'],
                    'tema_evaluacion' => $r['tema_nombre'],
                ];
            }
        }
        return null;
    }

    /**
     * Devuelve las respuestas individuales de una evaluación, filtradas por cliente y día del acta.
     * Una misma evaluación (ej. "Inducción SST") es reutilizada por muchas actas/clientes,
     * por lo que hay que filtrar por id_cliente_conjunto + fecha_dia para no traer las respuestas
     * históricas acumuladas. (Fix bug PDF mostrando 93 calificaciones para 5 asistentes.)
     */
    private function getRespuestasEvaluacion(int $idEvaluacion, ?int $idCliente = null, ?string $fechaDia = null): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table('tbl_evaluacion_respuestas')
            ->select('nombre, cedula, cargo, calificacion')
            ->where('id_evaluacion', $idEvaluacion)
            ->orderBy('calificacion', 'DESC');
        if ($idCliente) {
            $builder->where('id_cliente_conjunto', $idCliente);
        }
        if ($fechaDia) {
            $builder->where('fecha_dia', $fechaDia);
        }
        return $builder->get()->getResultArray();
    }
}
