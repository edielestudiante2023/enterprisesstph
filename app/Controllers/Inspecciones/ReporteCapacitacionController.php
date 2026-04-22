<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\ReporteCapacitacionModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use App\Models\AsistenciaCapacitacionModel;
use App\Models\AsistenciaCapacitacionAsistenteModel;
use App\Models\EvaluacionCapacitacionRespuestaModel;
use App\Models\CronogcapacitacionModel;
use App\Libraries\InspeccionEmailNotifier;
use Dompdf\Dompdf;
use App\Traits\AutosaveJsonTrait;
use App\Traits\ImagenCompresionTrait;

class ReporteCapacitacionController extends BaseController
{
    use AutosaveJsonTrait;
    use ImagenCompresionTrait;
    use \App\Traits\PreventDuplicateBorradorTrait;
    use AppTraitsInspeccionesTransactionalTrait;
    protected ReporteCapacitacionModel $inspeccionModel;

    public function __construct()
    {
        $this->inspeccionModel = new ReporteCapacitacionModel();
    }

    public function list()
    {
        $inspecciones = $this->inspeccionModel
            ->select('tbl_reporte_capacitacion.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_reporte_capacitacion.id_cliente', 'left')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_reporte_capacitacion.id_consultor', 'left')
            ->orderBy('tbl_reporte_capacitacion.fecha_capacitacion', 'DESC')
            ->findAll();

        $data = [
            'title'        => 'Reporte de Capacitacion',
            'inspecciones' => $inspecciones,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/reporte-capacitacion/list', $data),
            'title'   => 'Reporte de Capacitacion',
        ]);
    }

    public function create($idCliente = null)
    {
        $data = [
            'title'      => 'Nuevo Reporte de Capacitacion',
            'inspeccion' => null,
            'idCliente'  => $idCliente,
            'perfilesAsistentes' => ReporteCapacitacionModel::PERFILES_ASISTENTES,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/reporte-capacitacion/form', $data),
            'title'   => 'Nueva Capacitacion',
        ]);
    }

    public function store()
    {
        $existing = $this->reuseExistingBorrador($this->inspeccionModel, 'fecha_capacitacion', '/inspecciones/reporte-capacitacion/edit/');
        if ($existing) return $existing;

        $userId = session()->get('user_id');
        $isAutosave = $this->isAutosaveRequest();

        if (!$isAutosave) {
            if (!$this->validate(['id_cliente' => 'required|integer', 'fecha_capacitacion' => 'required|valid_date'])) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }
        }

        $data = $this->getInspeccionPostData();
        $data['id_consultor'] = $userId;
        $data['estado'] = 'borrador';

        $data['foto_capacitacion'] = $this->uploadFoto('foto_capacitacion', 'uploads/inspecciones/reporte-capacitacion/');
        $data['foto_otros_1'] = $this->uploadFoto('foto_otros_1', 'uploads/inspecciones/reporte-capacitacion/');
        $data['foto_otros_2'] = $this->uploadFoto('foto_otros_2', 'uploads/inspecciones/reporte-capacitacion/');

        $this->inspeccionModel->insert($data);
        $idInspeccion = $this->inspeccionModel->getInsertID();

        if ($isAutosave) {
            return $this->autosaveJsonSuccess($idInspeccion);
        }

        return redirect()->to('/inspecciones/reporte-capacitacion/edit/' . $idInspeccion)
            ->with('msg', 'Reporte guardado como borrador');
    }

    public function edit($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/reporte-capacitacion')->with('error', 'Reporte no encontrado');
        }
        $data = [
            'title'      => 'Editar Reporte de Capacitacion',
            'inspeccion' => $inspeccion,
            'idCliente'  => $inspeccion['id_cliente'],
            'perfilesAsistentes' => ReporteCapacitacionModel::PERFILES_ASISTENTES,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/reporte-capacitacion/form', $data),
            'title'   => 'Editar Capacitacion',
        ]);
    }

    public function update($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            if ($this->isAutosaveRequest()) {
                return $this->autosaveJsonError('No encontrada', 404);
            }
            return redirect()->to('/inspecciones/reporte-capacitacion')->with('error', 'No se puede editar');
        }

        $data = $this->getInspeccionPostData();

        foreach (['foto_capacitacion', 'foto_otros_1', 'foto_otros_2'] as $campo) {
            $nueva = $this->uploadFoto($campo, 'uploads/inspecciones/reporte-capacitacion/');
            if ($nueva) {
                if (!empty($inspeccion[$campo]) && file_exists(FCPATH . $inspeccion[$campo])) {
                    unlink(FCPATH . $inspeccion[$campo]);
                }
                $data[$campo] = $nueva;
            }
        }

        $this->inspeccionModel->update($id, $data);

        if ($this->request->getPost('finalizar')) {
            return $this->finalizar($id);
        }

        if ($this->isAutosaveRequest()) {
            return $this->autosaveJsonSuccess((int)$id);
        }

        return redirect()->to('/inspecciones/reporte-capacitacion/edit/' . $id)
            ->with('msg', 'Reporte actualizado');
    }

    public function view($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/reporte-capacitacion')->with('error', 'No encontrado');
        }

        if ($r = $this->guardFinalizado($inspeccion, '/inspecciones/reporte-capacitacion/view/' . $id)) return $r;

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $asistentes   = $this->fetchAsistentes((int) $inspeccion['id_cliente'], $inspeccion['fecha_capacitacion']);
        $evaluaciones = $this->fetchEvaluaciones((int) $inspeccion['id_cliente'], $inspeccion['fecha_capacitacion']);

        $data = [
            'title'      => 'Ver Reporte de Capacitacion',
            'inspeccion' => $inspeccion,
            'cliente'    => $clientModel->find($inspeccion['id_cliente']),
            'consultor'  => $consultantModel->find($inspeccion['id_consultor']),
            'perfilesAsistentes' => ReporteCapacitacionModel::PERFILES_ASISTENTES,
            'asistentes'   => $asistentes,
            'evaluaciones' => $evaluaciones,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/reporte-capacitacion/view', $data),
            'title'   => 'Ver Capacitacion',
        ]);
    }

    public function finalizar($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/reporte-capacitacion')->with('error', 'No encontrado');
        }

        $pdfPath = $this->generarPdfInterno($id);
        if (!$pdfPath) {
            return redirect()->back()->with('error', 'Error al generar PDF');
        }

        $this->inspeccionModel->update($id, [
            'estado'   => 'completo',
            'ruta_pdf' => $pdfPath,
        ]);

        $inspeccion = $this->inspeccionModel->find($id);
        $this->uploadToReportes($inspeccion, $pdfPath);

        // Sincronizar datos al cronograma de capacitación
        $this->syncToCronograma($inspeccion);

        // Enviar email con PDF adjunto
        $emailResult = InspeccionEmailNotifier::enviar(
            (int) $inspeccion['id_cliente'],
            (int) $inspeccion['id_consultor'],
            'REPORTE DE CAPACITACIÓN',
            $inspeccion['fecha_capacitacion'],
            $pdfPath,
            (int) $inspeccion['id'],
            'ReporteCapacitacion'
        );
        $msg = 'Finalizado y PDF generado.';
        if ($emailResult['success']) {
            $msg .= ' ' . $emailResult['message'];
        } else {
            $msg .= ' (Email no enviado: ' . $emailResult['error'] . ')';
        }

        return redirect()->to('/inspecciones/reporte-capacitacion/view/' . $id)
            ->with('msg', $msg);
    }

    public function generatePdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/reporte-capacitacion')->with('error', 'No encontrado');
        }

        $pdfPath = $this->generarPdfInterno($id);
        $this->inspeccionModel->update($id, ['ruta_pdf' => $pdfPath]);
        $fullPath = FCPATH . $pdfPath;
        if (!file_exists($fullPath)) {
            return redirect()->back()->with('error', 'PDF no encontrado');
        }

        $this->servirPdf($fullPath, 'reporte_capacitacion_' . $id . '.pdf');
        return;
    }

    public function delete($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/reporte-capacitacion')->with('error', 'No encontrado');
        }
        foreach (['foto_capacitacion', 'foto_otros_1', 'foto_otros_2'] as $campo) {
            if (!empty($inspeccion[$campo]) && file_exists(FCPATH . $inspeccion[$campo])) {
                unlink(FCPATH . $inspeccion[$campo]);
            }
        }

        if (!empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) {
            unlink(FCPATH . $inspeccion['ruta_pdf']);
        }

        $this->inspeccionModel->delete($id);

        return redirect()->to('/inspecciones/reporte-capacitacion')->with('msg', 'Reporte eliminado');
    }

    // ===== METODOS PRIVADOS =====

        public function regenerarPdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || ($inspeccion['estado'] ?? '') !== 'completo') {
            return redirect()->to('/inspecciones/reporte-capacitacion')->with('error', 'Solo se puede regenerar un registro finalizado.');
        }

        $pdfPath = $this->generarPdfInterno($id);

        $this->inspeccionModel->update($id, [
            'ruta_pdf' => $pdfPath,
        ]);

        $inspeccion = $this->inspeccionModel->find($id);
        $this->uploadToReportes($inspeccion, $pdfPath);

        return redirect()->to("/inspecciones/reporte-capacitacion/view/{$id}")->with('msg', 'PDF regenerado exitosamente.');
    }

    private function getInspeccionPostData(): array
    {
        $data = [
            'id_cliente'             => $this->request->getPost('id_cliente'),
            'fecha_capacitacion'     => $this->request->getPost('fecha_capacitacion'),
            'nombre_capacitacion'    => $this->request->getPost('nombre_capacitacion'),
            'objetivo_capacitacion'  => $this->request->getPost('objetivo_capacitacion'),
            'nombre_capacitador'     => $this->request->getPost('nombre_capacitador'),
            'horas_duracion'         => $this->request->getPost('horas_duracion'),
            'numero_asistentes'      => (int) $this->request->getPost('numero_asistentes'),
            'numero_programados'     => (int) $this->request->getPost('numero_programados'),
            'numero_evaluados'       => (int) $this->request->getPost('numero_evaluados'),
            'promedio_calificaciones' => $this->request->getPost('promedio_calificaciones'),
            'observaciones'          => $this->request->getPost('observaciones'),
        ];

        // perfil_asistentes: checkboxes -> comma-separated string
        $perfiles = $this->request->getPost('perfil_asistentes');
        $data['perfil_asistentes'] = is_array($perfiles) ? implode(',', $perfiles) : '';

        $data['mostrar_evaluacion_capacitacion'] = $this->request->getPost('mostrar_evaluacion_capacitacion') ? 1 : 0;

        $idCronog = $this->request->getPost('id_cronograma_capacitacion');
        if ($idCronog) {
            $data['id_cronograma_capacitacion'] = (int) $idCronog;
        }

        return $data;
    }

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

    private function generarPdfInterno(int $id): ?string
    {
        $inspeccion = $this->inspeccionModel->find($id);
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $cliente = $clientModel->find($inspeccion['id_cliente']);
        $consultor = $consultantModel->find($inspeccion['id_consultor']);

        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoBase64 = $this->fotoABase64ParaPdf($logoPath);
            }
        }

        $fotosBase64 = [];
        foreach (['foto_capacitacion', 'foto_otros_1', 'foto_otros_2'] as $campo) {
            $fotosBase64[$campo] = '';
            if (!empty($inspeccion[$campo])) {
                $fotoPath = FCPATH . $inspeccion[$campo];
                if (file_exists($fotoPath)) {
                    $fotosBase64[$campo] = $this->fotoABase64ParaPdf($fotoPath);
                }
            }
        }

        $asistentes   = $this->fetchAsistentes((int) $inspeccion['id_cliente'], $inspeccion['fecha_capacitacion']);
        $evaluaciones = $this->fetchEvaluaciones((int) $inspeccion['id_cliente'], $inspeccion['fecha_capacitacion']);

        $data = [
            'inspeccion'  => $inspeccion,
            'cliente'     => $cliente,
            'consultor'   => $consultor,
            'perfilesAsistentes' => ReporteCapacitacionModel::PERFILES_ASISTENTES,
            'logoBase64'  => $logoBase64,
            'fotosBase64' => $fotosBase64,
            'asistentes'   => $asistentes,
            'evaluaciones' => $evaluaciones,
        ];

        $html = view('inspecciones/reporte-capacitacion/pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $pdfDir = 'uploads/inspecciones/reporte-capacitacion/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) {
            mkdir(FCPATH . $pdfDir, 0755, true);
        }

        $pdfFileName = 'reporte_capacitacion_' . $id . '_' . date('Ymd_His') . '.pdf';
        $pdfPath = $pdfDir . $pdfFileName;

        if (!empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) {
            unlink(FCPATH . $inspeccion['ruta_pdf']);
        }

        file_put_contents(FCPATH . $pdfPath, $dompdf->output());

        return $pdfPath;
    }

    // ── Email ─────────────────────────────────────────────────

    public function enviarEmail($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || $inspeccion['estado'] !== 'completo' || empty($inspeccion['ruta_pdf'])) {
            return redirect()->to("/inspecciones/reporte-capacitacion/view/{$id}")->with('error', 'Debe estar finalizado con PDF para enviar email.');
        }

        $result = InspeccionEmailNotifier::enviar(
            (int) $inspeccion['id_cliente'],
            (int) $inspeccion['id_consultor'],
            'REPORTE DE CAPACITACIÓN',
            $inspeccion['fecha_capacitacion'],
            $inspeccion['ruta_pdf'],
            (int) $inspeccion['id'],
            'ReporteCapacitacion'
        );

        if ($result['success']) {
            return redirect()->to("/inspecciones/reporte-capacitacion/view/{$id}")->with('msg', $result['message']);
        }
        return redirect()->to("/inspecciones/reporte-capacitacion/view/{$id}")->with('error', $result['error']);
    }

    /**
     * API: genera el objetivo de una capacitación SST usando OpenAI.
     */
    public function generarObjetivo()
    {
        $nombre = $this->request->getJSON(true)['nombre_capacitacion'] ?? '';
        $nombre = trim($nombre);

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

        $payload = json_encode([
            'model'       => env('OPENAI_MODEL', 'gpt-4o-mini'),
            'messages'    => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'max_tokens'  => 200,
            'temperature' => 0.6,
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
            CURLOPT_TIMEOUT        => 20,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!$response || $httpCode !== 200) {
            log_message('error', 'generarObjetivo OpenAI error HTTP ' . $httpCode . ': ' . $response);
            return $this->response->setJSON(['error' => 'Error al contactar la IA. Intenta de nuevo.'])->setStatusCode(500);
        }

        $data = json_decode($response, true);
        $objetivo = trim($data['choices'][0]['message']['content'] ?? '');

        if (!$objetivo) {
            return $this->response->setJSON(['error' => 'La IA no devolvió respuesta.'])->setStatusCode(500);
        }

        return $this->response->setJSON(['objetivo' => $objetivo]);
    }

    /**
     * API: devuelve asistentes de tbl_asistencia_induccion_asistente
     * que correspondan al cliente + fecha dados.
     */
    public function apiAsistentes()
    {
        $idCliente = (int) $this->request->getGet('id_cliente');
        $fecha     = $this->request->getGet('fecha');

        if (!$idCliente || !$fecha) {
            return $this->response->setJSON([]);
        }

        $asistentes = $this->fetchAsistentes($idCliente, $fecha);
        return $this->response->setJSON($asistentes);
    }

    /**
     * Busca sesiones de asistencia_induccion para el cliente+fecha
     * y devuelve los asistentes de esas sesiones.
     */
    private function fetchAsistentes(int $idCliente, string $fecha): array
    {
        $induccionModel  = new AsistenciaCapacitacionModel();
        $asistenteModel  = new AsistenciaCapacitacionAsistenteModel();

        $sesiones = $induccionModel
            ->where('id_cliente', $idCliente)
            ->where('fecha_sesion', $fecha)
            ->findAll();

        if (empty($sesiones)) {
            return [];
        }

        $ids = array_column($sesiones, 'id');
        return $asistenteModel
            ->whereIn('id_asistencia', $ids)
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    /**
     * Devuelve los resultados de evaluación del cliente en la fecha dada.
     */
    private function fetchEvaluaciones(int $idCliente, string $fecha): array
    {
        $db         = \Config\Database::connect();
        $fechaDesde = date('Y-m-d', strtotime($fecha . ' -7 days'));
        $fechaHasta = date('Y-m-d', strtotime($fecha . ' +7 days'));
        return $db->table('tbl_evaluacion_respuestas')
            ->where('id_cliente_conjunto', $idCliente)
            ->where('DATE(created_at) >=', $fechaDesde)
            ->where('DATE(created_at) <=', $fechaHasta)
            ->select('nombre, cedula, empresa_contratante, cargo, calificacion')
            ->orderBy('calificacion', 'DESC')
            ->get()->getResultArray();
    }

    /**
     * Sincroniza los datos del reporte finalizado al cronograma de capacitación vinculado.
     */
    private function syncToCronograma(array $inspeccion): void
    {
        $idCronog = $inspeccion['id_cronograma_capacitacion'] ?? null;
        if (!$idCronog) {
            return;
        }

        $cronogModel = new CronogcapacitacionModel();
        $cronograma = $cronogModel->find($idCronog);
        if (!$cronograma) {
            return;
        }

        $numAsistentes = (int) ($inspeccion['numero_asistentes'] ?? 0);
        $numProgramados = (int) ($inspeccion['numero_programados'] ?? 0);
        $porcentaje = $numProgramados > 0
            ? number_format(($numAsistentes / $numProgramados) * 100, 2) . '%'
            : '0%';

        $cronogModel->update($idCronog, [
            'fecha_de_realizacion'                       => $inspeccion['fecha_capacitacion'],
            'estado'                                     => 'EJECUTADA',
            'nombre_del_capacitador'                     => $inspeccion['nombre_capacitador'] ?? '',
            'horas_de_duracion_de_la_capacitacion'       => $inspeccion['horas_duracion'] ?? '',
            'numero_de_asistentes_a_capacitacion'        => $numAsistentes,
            'numero_total_de_personas_programadas'       => $numProgramados,
            'porcentaje_cobertura'                       => $porcentaje,
            'numero_de_personas_evaluadas'               => (int) ($inspeccion['numero_evaluados'] ?? 0),
            'promedio_de_calificaciones'                 => $inspeccion['promedio_calificaciones'] ?? '',
            'observaciones'                              => $inspeccion['observaciones'] ?? '',
            'id_reporte_capacitacion'                    => (int) $inspeccion['id'],
        ]);
    }

    /**
     * API: devuelve las capacitaciones pendientes del cronograma para un cliente.
     */
    public function apiCronogramasPendientes()
    {
        $idCliente = (int) $this->request->getGet('id_cliente');
        if (!$idCliente) {
            return $this->response->setJSON([]);
        }

        $cronogModel = new CronogcapacitacionModel();
        $idReporte   = (int) $this->request->getGet('id_reporte');

        // Traer cronogramas del cliente que no tengan reporte vinculado,
        // o que ya estén vinculados al reporte que se está editando.
        $cronogramas = $cronogModel
            ->where('id_cliente', $idCliente)
            ->groupStart()
                ->where('id_reporte_capacitacion IS NULL')
                ->orWhere('id_reporte_capacitacion', 0)
                ->orWhere('id_reporte_capacitacion', $idReporte ?: 0)
            ->groupEnd()
            ->orderBy('fecha_programada', 'ASC')
            ->findAll();

        return $this->response->setJSON($cronogramas);
    }

    private function uploadToReportes(array $inspeccion, string $pdfPath): bool
    {
        $reporteModel = new ReporteModel();
        $clientModel = new ClientModel();
        $cliente = $clientModel->find($inspeccion['id_cliente']);
        if (!$cliente) return false;

        $nitCliente = $cliente['nit_cliente'];

        $existente = $reporteModel
            ->where('id_cliente', $inspeccion['id_cliente'])
            ->where('id_report_type', 6)
            ->where('id_detailreport', 18)
            ->like('observaciones', 'rep_cap_id:' . $inspeccion['id'])
            ->first();

        $destDir = UPLOADS_PATH . $nitCliente;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $fileName = 'reporte_capacitacion_' . $inspeccion['id'] . '_' . date('Ymd_His') . '.pdf';
        $destPath = $destDir . '/' . $fileName;
        copy(FCPATH . $pdfPath, $destPath);

        $data = [
            'titulo_reporte'  => 'REPORTE DE CAPACITACION - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $inspeccion['fecha_capacitacion'],
            'id_detailreport' => 18,
            'id_report_type'  => 6,
            'id_cliente'      => $inspeccion['id_cliente'],
            'estado'          => 'CERRADO',
            'observaciones'   => 'Generado automaticamente desde modulo de inspecciones. rep_cap_id:' . $inspeccion['id'],
            'enlace'          => base_url(UPLOADS_URL_PREFIX . '/' . $nitCliente . '/' . $fileName),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        if ($existente) {
            return $reporteModel->update($existente['id_reporte'], $data);
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        return $reporteModel->save($data);
    }
}
