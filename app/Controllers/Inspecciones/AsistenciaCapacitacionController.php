<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\AsistenciaCapacitacionModel;
use App\Models\AsistenciaCapacitacionAsistenteModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use App\Libraries\InspeccionEmailNotifier;
use Dompdf\Dompdf;
use App\Traits\AutosaveJsonTrait;
use App\Traits\ImagenCompresionTrait;

class AsistenciaCapacitacionController extends BaseController
{
    use AutosaveJsonTrait;
    use ImagenCompresionTrait;
    use \App\Traits\PreventDuplicateBorradorTrait;
    use \App\Traits\InspeccionesTransactionalTrait;
    protected AsistenciaCapacitacionModel $inspeccionModel;

    public const TIPOS_CHARLA = [
        'induccion_reinduccion' => 'Induccion / Reinduccion',
        'reunion'              => 'Reunion',
        'charla'               => 'Charla',
        'capacitacion'         => 'Capacitacion',
        'otros_temas'          => 'Otros Temas',
    ];

    public function __construct()
    {
        $this->inspeccionModel = new AsistenciaCapacitacionModel();
    }

    public function list()
    {
        $inspecciones = $this->inspeccionModel
            ->select('tbl_asistencia_capacitacion.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_asistencia_capacitacion.id_cliente', 'left')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_asistencia_capacitacion.id_consultor', 'left')
            ->orderBy('tbl_asistencia_capacitacion.fecha_sesion', 'DESC')
            ->findAll();

        $data = [
            'title'        => 'Asistencia Capacitación',
            'inspecciones' => $inspecciones,
            'tiposCharla'  => self::TIPOS_CHARLA,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/asistencia-capacitacion/list', $data),
            'title'   => 'Asistencia Capacitación',
        ]);
    }

    public function create($idCliente = null)
    {
        $data = [
            'title'       => 'Nueva Asistencia',
            'inspeccion'  => null,
            'asistentes'  => [],
            'idCliente'   => $idCliente,
            'tiposCharla' => self::TIPOS_CHARLA,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/asistencia-capacitacion/form', $data),
            'title'   => 'Nueva Asistencia',
        ]);
    }

    public function store()
    {
        $existing = $this->reuseExistingBorrador($this->inspeccionModel, 'fecha_sesion', '/inspecciones/asistencia-capacitacion/edit/');
        if ($existing) return $existing;

        $isAutosave = $this->isAutosaveRequest();

        if ($isAutosave) {
            if ($err = $this->validateAutosaveMinimum('id_cliente', 'fecha_sesion')) return $err;
        } else {
            if (!$this->validate(['id_cliente' => 'required|integer', 'fecha_sesion' => 'required|valid_date'])) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }
        }

        $userId = session()->get('user_id');
        $idInspeccion = 0;

        $txResult = $this->runTransactional(function () use ($userId, &$idInspeccion) {
            $data = $this->getInspeccionPostData();
            $data['id_consultor'] = $userId;
            $data['estado'] = 'borrador';
            $this->inspeccionModel->insert($data);
            $idInspeccion = $this->inspeccionModel->getInsertID();
            $this->saveAsistentes($idInspeccion);
            return true;
        });

        if ($txResult instanceof \CodeIgniter\HTTP\ResponseInterface) return $txResult;

        if ($isAutosave) {
            return $this->autosaveJsonSuccess($idInspeccion);
        }

        if ($this->request->getPost('accion') === 'borrador') {
            return redirect()->to('/inspecciones/asistencia-capacitacion')
                ->with('msg', 'Borrador guardado.');
        }

        return redirect()->to('/inspecciones/asistencia-capacitacion/registrar/' . $idInspeccion)
            ->with('msg', 'Asistencia creada. Registra los asistentes uno a uno.');
    }

    public function edit($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/asistencia-capacitacion')->with('error', 'Registro no encontrado');
        }
        $asistenteModel = new AsistenciaCapacitacionAsistenteModel();

        $data = [
            'title'       => 'Editar Asistencia',
            'inspeccion'  => $inspeccion,
            'asistentes'  => $asistenteModel->getByAsistencia($id),
            'idCliente'   => $inspeccion['id_cliente'],
            'tiposCharla' => self::TIPOS_CHARLA,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/asistencia-capacitacion/form', $data),
            'title'   => 'Editar Asistencia',
        ]);
    }

    public function update($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            if ($this->isAutosaveRequest()) {
                return $this->autosaveJsonError('No encontrada', 404);
            }
            return redirect()->to('/inspecciones/asistencia-capacitacion')->with('error', 'No se puede editar');
        }

        $isAutosave = $this->isAutosaveRequest();
        if ($isAutosave) {
            if ($err = $this->validateAutosaveMinimum('id_cliente', 'fecha_sesion')) return $err;
        }

        // Asistentes se gestionan exclusivamente vía AJAX (storeAsistente/deleteAsistente).
        // update() solo actualiza metadatos de la sesión.
        $txResult = $this->runTransactional(function () use ($id) {
            $this->inspeccionModel->update($id, $this->getInspeccionPostData());
            return true;
        });

        if ($txResult instanceof \CodeIgniter\HTTP\ResponseInterface) return $txResult;

        if ($isAutosave) {
            return $this->autosaveJsonSuccess((int)$id);
        }

        if ($this->request->getPost('accion') === 'borrador') {
            return redirect()->to('/inspecciones/asistencia-capacitacion')
                ->with('msg', 'Borrador guardado.');
        }

        return redirect()->to('/inspecciones/asistencia-capacitacion/registrar/' . $id)
            ->with('msg', 'Asistencia actualizada.');
    }

    public function view($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/asistencia-capacitacion')->with('error', 'No encontrado');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $asistenteModel = new AsistenciaCapacitacionAsistenteModel();

        $data = [
            'title'       => 'Ver Asistencia',
            'inspeccion'  => $inspeccion,
            'cliente'     => $clientModel->find($inspeccion['id_cliente']),
            'consultor'   => $consultantModel->find($inspeccion['id_consultor']),
            'asistentes'  => $asistenteModel->getByAsistencia($id),
            'tiposCharla' => self::TIPOS_CHARLA,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/asistencia-capacitacion/view', $data),
            'title'   => 'Ver Asistencia',
        ]);
    }

    /**
     * Nueva vista: registro asistente por asistente con firma integrada
     */
    public function registrar($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/asistencia-capacitacion')->with('error', 'No encontrado');
        }
        $asistenteModel = new AsistenciaCapacitacionAsistenteModel();
        $data = [
            'title'      => 'Registrar Asistentes',
            'inspeccion' => $inspeccion,
            'asistentes' => $asistenteModel->getByAsistencia($id),
        ];
        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/asistencia-capacitacion/registrar', $data),
            'title'   => 'Registrar Asistentes',
        ]);
    }

    /**
     * AJAX: guardar un asistente con firma integrada
     */
    public function storeAsistente($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return $this->response->setJSON(['success' => false, 'error' => 'No encontrado']);
        }

        $nombre = trim($this->request->getPost('nombre') ?? '');
        if (!$nombre) {
            return $this->response->setJSON(['success' => false, 'error' => 'Nombre requerido']);
        }

        $firmaBase64 = $this->request->getPost('firma');
        $firmaPath = '';
        if (!empty($firmaBase64)) {
            $firmaData = str_replace('data:image/png;base64,', '', $firmaBase64);
            $firmaData = str_replace(' ', '+', $firmaData);
            $decoded = base64_decode($firmaData);
            $dir = 'uploads/inspecciones/asistencia-induccion/firmas/';
            if (!is_dir(FCPATH . $dir)) {
                mkdir(FCPATH . $dir, 0755, true);
            }
            $fileName = 'firma_' . $id . '_' . time() . '_' . mt_rand(100, 999) . '.png';
            file_put_contents(FCPATH . $dir . $fileName, $decoded);
            $firmaPath = $dir . $fileName;
        }

        $asistenteModel = new AsistenciaCapacitacionAsistenteModel();
        $asistenteModel->insert([
            'id_asistencia' => $id,
            'nombre'        => $nombre,
            'cedula'        => $this->request->getPost('cedula') ?? '',
            'cargo'         => $this->request->getPost('cargo') ?? '',
            'firma'         => $firmaPath,
        ]);
        $idAsistente = $asistenteModel->getInsertID();
        $total = $asistenteModel->where('id_asistencia', $id)->countAllResults();

        return $this->response->setJSON([
            'success'      => true,
            'id_asistente' => $idAsistente,
            'total'        => $total,
            'csrf_hash'    => csrf_hash(),
        ]);
    }

    /**
     * AJAX: eliminar un asistente
     */
    public function deleteAsistente($idAsistente)
    {
        $asistenteModel = new AsistenciaCapacitacionAsistenteModel();
        $asistente = $asistenteModel->find($idAsistente);
        if (!$asistente) {
            return $this->response->setJSON(['success' => false]);
        }
        if (!empty($asistente['firma']) && file_exists(FCPATH . $asistente['firma'])) {
            unlink(FCPATH . $asistente['firma']);
        }
        $idAsistencia = $asistente['id_asistencia'];
        $asistenteModel->delete($idAsistente);
        $total = $asistenteModel->where('id_asistencia', $idAsistencia)->countAllResults();

        return $this->response->setJSON([
            'success'   => true,
            'total'     => $total,
            'csrf_hash' => csrf_hash(),
        ]);
    }

    public function firmas($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/asistencia-capacitacion')->with('error', 'No encontrado');
        }

        $asistenteModel = new AsistenciaCapacitacionAsistenteModel();

        $data = [
            'title'      => 'Firmas Asistencia',
            'inspeccion' => $inspeccion,
            'asistentes' => $asistenteModel->getByAsistencia($id),
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/asistencia-capacitacion/firmas', $data),
            'title'   => 'Firmas Asistencia',
        ]);
    }

    public function guardarFirma($idAsistente)
    {
        $asistenteModel = new AsistenciaCapacitacionAsistenteModel();
        $asistente = $asistenteModel->find($idAsistente);
        if (!$asistente) {
            return $this->response->setJSON(['success' => false, 'error' => 'No encontrado']);
        }

        $firmaData = $this->request->getPost('firma');
        if (empty($firmaData)) {
            return $this->response->setJSON(['success' => false, 'error' => 'Firma vacia']);
        }

        // Decode base64 PNG
        $firmaData = str_replace('data:image/png;base64,', '', $firmaData);
        $firmaData = str_replace(' ', '+', $firmaData);
        $decoded = base64_decode($firmaData);

        $dir = 'uploads/inspecciones/asistencia-induccion/firmas/';
        if (!is_dir(FCPATH . $dir)) {
            mkdir(FCPATH . $dir, 0755, true);
        }

        // Delete old firma if exists
        if (!empty($asistente['firma']) && file_exists(FCPATH . $asistente['firma'])) {
            unlink(FCPATH . $asistente['firma']);
        }

        $fileName = 'firma_' . $idAsistente . '_' . time() . '.png';
        file_put_contents(FCPATH . $dir . $fileName, $decoded);

        $asistenteModel->update($idAsistente, ['firma' => $dir . $fileName]);

        return $this->response->setJSON(['success' => true, 'firma_url' => '/' . $dir . $fileName]);
    }

    public function finalizar($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/asistencia-capacitacion')->with('error', 'No encontrado');
        }

        if ($r = $this->guardFinalizado($inspeccion, '/inspecciones/asistencia-capacitacion/view/' . $id)) return $r;

        // Validar que todos los asistentes hayan firmado
        $asistenteModel = new AsistenciaCapacitacionAsistenteModel();
        $asistentes = $asistenteModel->getByAsistencia($id);
        if (empty($asistentes)) {
            return redirect()->to('/inspecciones/asistencia-capacitacion/edit/' . $id)->with('error', 'Debe agregar al menos un asistente antes de finalizar.');
        }
        $sinFirma = array_filter($asistentes, fn($a) => empty($a['firma']));
        if (!empty($sinFirma)) {
            return redirect()->to('/inspecciones/asistencia-capacitacion/firmas/' . $id)->with('error', 'Todos los asistentes deben firmar antes de finalizar. Faltan ' . count($sinFirma) . ' firma(s).');
        }

        $result = $this->generarPdfInterno($id);
        if (!$result) {
            return redirect()->back()->with('error', 'Error al generar PDF');
        }

        $updateData = [
            'estado'              => 'completo',
            'ruta_pdf_asistencia' => $result['asistencia'],
        ];
        if (!empty($result['responsabilidades'])) {
            $updateData['ruta_pdf_responsabilidades'] = $result['responsabilidades'];
        }

        $this->inspeccionModel->update($id, $updateData);

        $inspeccion = $this->inspeccionModel->find($id);

        // Upload FT-SST-005 (always) — id_detailreport=34 Asistencia Inducción
        $this->uploadToReportes($inspeccion, $result['asistencia'], 34, 'asist_ind_id:');

        // Upload FT-SST-003 (only if induccion_reinduccion) — id_detailreport=35 Responsabilidades SST
        if (!empty($result['responsabilidades'])) {
            $this->uploadToReportes($inspeccion, $result['responsabilidades'], 35, 'asist_ind_resp_id:');
        }

        // Enviar email con PDF(s) adjunto(s)
        $extraAttachments = [];
        if (!empty($result['responsabilidades'])) {
            $extraAttachments[] = [
                'path'     => $result['responsabilidades'],
                'filename' => 'responsabilidades_sst_' . $inspeccion['id'] . '.pdf',
            ];
        }
        $emailResult = InspeccionEmailNotifier::enviar(
            (int) $inspeccion['id_cliente'],
            (int) $inspeccion['id_consultor'],
            'LISTADO ASISTENCIA SST',
            $inspeccion['fecha_sesion'],
            $result['asistencia'],
            (int) $inspeccion['id'],
            'AsistenciaInduccion',
            $inspeccion['capacitador'] ?? '',
            $extraAttachments
        );
        $msg = 'Finalizado y PDF generado.';
        if ($emailResult['success']) {
            $msg .= ' ' . $emailResult['message'];
        } else {
            $msg .= ' (Email no enviado: ' . $emailResult['error'] . ')';
        }

        return redirect()->to('/inspecciones/asistencia-capacitacion/view/' . $id)
            ->with('msg', $msg);
    }

    public function generatePdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/asistencia-capacitacion')->with('error', 'No encontrado');
        }

        // Serve cached PDF if it already exists
        if (!empty($inspeccion['ruta_pdf_asistencia']) && file_exists(FCPATH . $inspeccion['ruta_pdf_asistencia'])) {
            $fullPath = FCPATH . $inspeccion['ruta_pdf_asistencia'];
        } else {
            // Generate only if no cached PDF
            $result = $this->generarPdfInterno($id);
            $this->inspeccionModel->update($id, [
                'ruta_pdf_asistencia'        => $result['asistencia'],
                'ruta_pdf_responsabilidades' => $result['responsabilidades'],
            ]);
            $fullPath = FCPATH . $result['asistencia'];
        }

        if (!file_exists($fullPath)) {
            return redirect()->back()->with('error', 'PDF no encontrado');
        }

        $this->servirPdf($fullPath, 'asistencia_induccion_' . $id . '.pdf');
    }

    public function generatePdfResponsabilidades($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || $inspeccion['tipo_charla'] !== 'induccion_reinduccion') {
            return redirect()->to('/inspecciones/asistencia-capacitacion/view/' . $id)->with('error', 'Este registro no tiene PDF de responsabilidades.');
        }

        if (!empty($inspeccion['ruta_pdf_responsabilidades']) && file_exists(FCPATH . $inspeccion['ruta_pdf_responsabilidades'])) {
            $fullPath = FCPATH . $inspeccion['ruta_pdf_responsabilidades'];
        } else {
            $result = $this->generarPdfInterno($id);
            $this->inspeccionModel->update($id, [
                'ruta_pdf_asistencia'        => $result['asistencia'],
                'ruta_pdf_responsabilidades' => $result['responsabilidades'],
            ]);
            $fullPath = FCPATH . $result['responsabilidades'];
        }

        if (!file_exists($fullPath)) {
            return redirect()->back()->with('error', 'PDF no encontrado');
        }

        $this->servirPdf($fullPath, 'responsabilidades_sst_' . $id . '.pdf');
    }

    public function delete($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/asistencia-capacitacion')->with('error', 'No encontrado');
        }
        // Delete attendee firmas
        $asistenteModel = new AsistenciaCapacitacionAsistenteModel();
        $asistentes = $asistenteModel->getByAsistencia($id);
        foreach ($asistentes as $a) {
            if (!empty($a['firma']) && file_exists(FCPATH . $a['firma'])) {
                unlink(FCPATH . $a['firma']);
            }
        }

        // Delete attendees
        $asistenteModel->where('id_asistencia', $id)->delete();

        // Delete PDFs
        foreach (['ruta_pdf_asistencia', 'ruta_pdf_responsabilidades'] as $campo) {
            if (!empty($inspeccion[$campo]) && file_exists(FCPATH . $inspeccion[$campo])) {
                unlink(FCPATH . $inspeccion[$campo]);
            }
        }

        $this->inspeccionModel->delete($id);

        return redirect()->to('/inspecciones/asistencia-capacitacion')->with('msg', 'Registro eliminado');
    }

    // ===== PRIVATE METHODS =====

        public function regenerarPdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || ($inspeccion['estado'] ?? '') !== 'completo') {
            return redirect()->to('/inspecciones/asistencia-capacitacion')->with('error', 'Solo se puede regenerar un registro finalizado.');
        }

        $result = $this->generarPdfInterno($id);

        $this->inspeccionModel->update($id, [
            'ruta_pdf_asistencia'        => $result['asistencia'],
            'ruta_pdf_responsabilidades' => $result['responsabilidades'],
        ]);

        $inspeccion = $this->inspeccionModel->find($id);

        // Upload FT-SST-005 (always) — id_detailreport=34 Asistencia Inducción
        $this->uploadToReportes($inspeccion, $result['asistencia'], 34, 'asist_ind_id:');

        // Upload FT-SST-003 (only if induccion_reinduccion) — id_detailreport=35 Responsabilidades SST
        if (!empty($result['responsabilidades'])) {
            $this->uploadToReportes($inspeccion, $result['responsabilidades'], 35, 'asist_ind_resp_id:');
        }

        return redirect()->to("/inspecciones/asistencia-capacitacion/view/{$id}")->with('msg', 'PDF regenerado exitosamente.');
    }

    private function getInspeccionPostData(): array
    {
        return [
            'id_cliente'   => $this->request->getPost('id_cliente'),
            'fecha_sesion' => $this->request->getPost('fecha_sesion'),
            'tema'         => $this->request->getPost('tema'),
            'lugar'        => $this->request->getPost('lugar'),
            'objetivo'     => $this->request->getPost('objetivo'),
            'capacitador'  => $this->request->getPost('capacitador'),
            'tipo_charla'  => $this->request->getPost('tipo_charla'),
            'material'     => $this->request->getPost('material'),
            'tiempo_horas' => $this->request->getPost('tiempo_horas'),
            'observaciones'=> $this->request->getPost('observaciones'),
        ];
    }

    private function saveAsistentes(int $idInspeccion, array $firmasPrevias = []): void
    {
        $asistenteModel = new AsistenciaCapacitacionAsistenteModel();

        $nombres = $this->request->getPost('asistente_nombre');
        $cedulas = $this->request->getPost('asistente_cedula');
        $cargos  = $this->request->getPost('asistente_cargo');

        if (is_array($nombres)) {
            for ($i = 0; $i < count($nombres); $i++) {
                if (!empty($nombres[$i])) {
                    $firmaPrevia = '';
                    $cedula = $cedulas[$i] ?? '';
                    if (!empty($cedula) && isset($firmasPrevias[$cedula])) {
                        $firmaPrevia = $firmasPrevias[$cedula];
                    }

                    $asistenteModel->insert([
                        'id_asistencia' => $idInspeccion,
                        'nombre'        => $nombres[$i],
                        'cedula'        => $cedula,
                        'cargo'         => $cargos[$i] ?? '',
                        'firma'         => $firmaPrevia,
                    ]);
                }
            }
        }
    }

    private function generarPdfInterno(int $id): ?array
    {
        $inspeccion = $this->inspeccionModel->find($id);
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $asistenteModel = new AsistenciaCapacitacionAsistenteModel();
        $cliente = $clientModel->find($inspeccion['id_cliente']);
        $consultor = $consultantModel->find($inspeccion['id_consultor']);

        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoBase64 = $this->fotoABase64ParaPdf($logoPath);
            }
        }

        // Convert firmas to base64
        $asistentes = $asistenteModel->getByAsistencia($id);
        foreach ($asistentes as &$a) {
            $a['firma_base64'] = '';
            if (!empty($a['firma'])) {
                $firmaPath = FCPATH . $a['firma'];
                if (file_exists($firmaPath)) {
                    $mime = mime_content_type($firmaPath);
                    $a['firma_base64'] = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($firmaPath));
                }
            }
        }
        unset($a);

        $data = [
            'inspeccion'  => $inspeccion,
            'cliente'     => $cliente,
            'consultor'   => $consultor,
            'asistentes'  => $asistentes,
            'tiposCharla' => self::TIPOS_CHARLA,
            'logoBase64'  => $logoBase64,
        ];

        $pdfDir = 'uploads/inspecciones/asistencia-induccion/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) {
            mkdir(FCPATH . $pdfDir, 0755, true);
        }

        // PDF 1: FT-SST-005 (always)
        $data['pdfType'] = 'asistencia';
        $html1 = view('inspecciones/asistencia-capacitacion/pdf', $data);

        $options1 = new \Dompdf\Options();
        $options1->set('isRemoteEnabled', true);
        $options1->set('isHtml5ParserEnabled', true);
        $dompdf1 = new Dompdf($options1);
        $dompdf1->loadHtml($html1);
        $dompdf1->setPaper('letter', 'portrait');
        $dompdf1->render();

        $pdfFileName1 = 'asistencia_induccion_' . $id . '_' . date('Ymd_His') . '.pdf';
        $pdfPath1 = $pdfDir . $pdfFileName1;

        // Delete old PDF if exists
        if (!empty($inspeccion['ruta_pdf_asistencia']) && file_exists(FCPATH . $inspeccion['ruta_pdf_asistencia'])) {
            unlink(FCPATH . $inspeccion['ruta_pdf_asistencia']);
        }

        file_put_contents(FCPATH . $pdfPath1, $dompdf1->output());

        // PDF 2: FT-SST-003 (only if induccion_reinduccion)
        $pdfPath2 = null;
        if ($inspeccion['tipo_charla'] === 'induccion_reinduccion') {
            $data['pdfType'] = 'responsabilidades';
            $html2 = view('inspecciones/asistencia-capacitacion/pdf', $data);

            $options2 = new \Dompdf\Options();
            $options2->set('isRemoteEnabled', true);
            $options2->set('isHtml5ParserEnabled', true);
            $dompdf2 = new Dompdf($options2);
            $dompdf2->loadHtml($html2);
            $dompdf2->setPaper('letter', 'portrait');
            $dompdf2->render();

            $pdfFileName2 = 'responsabilidades_sst_' . $id . '_' . date('Ymd_His') . '.pdf';
            $pdfPath2 = $pdfDir . $pdfFileName2;

            // Delete old PDF if exists
            if (!empty($inspeccion['ruta_pdf_responsabilidades']) && file_exists(FCPATH . $inspeccion['ruta_pdf_responsabilidades'])) {
                unlink(FCPATH . $inspeccion['ruta_pdf_responsabilidades']);
            }

            file_put_contents(FCPATH . $pdfPath2, $dompdf2->output());
        }

        return ['asistencia' => $pdfPath1, 'responsabilidades' => $pdfPath2];
    }

    // ── Email ─────────────────────────────────────────────────

    public function enviarEmail($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || $inspeccion['estado'] !== 'completo' || empty($inspeccion['ruta_pdf_asistencia'])) {
            return redirect()->to("/inspecciones/asistencia-capacitacion/view/{$id}")->with('error', 'Debe estar finalizado con PDF para enviar email.');
        }

        $extraAttachments = [];
        if (!empty($inspeccion['ruta_pdf_responsabilidades'])) {
            $extraAttachments[] = [
                'path'     => $inspeccion['ruta_pdf_responsabilidades'],
                'filename' => 'responsabilidades_sst_' . $inspeccion['id'] . '.pdf',
            ];
        }
        $result = InspeccionEmailNotifier::enviar(
            (int) $inspeccion['id_cliente'],
            (int) $inspeccion['id_consultor'],
            'LISTADO ASISTENCIA SST',
            $inspeccion['fecha_sesion'],
            $inspeccion['ruta_pdf_asistencia'],
            (int) $inspeccion['id'],
            'AsistenciaInduccion',
            $inspeccion['capacitador'] ?? '',
            $extraAttachments
        );

        if ($result['success']) {
            return redirect()->to("/inspecciones/asistencia-capacitacion/view/{$id}")->with('msg', $result['message']);
        }
        return redirect()->to("/inspecciones/asistencia-capacitacion/view/{$id}")->with('error', $result['error']);
    }

    private function uploadToReportes(array $inspeccion, string $pdfPath, int $idDetailReport, string $tag): bool
    {
        $reporteModel = new ReporteModel();
        $clientModel = new ClientModel();
        $cliente = $clientModel->find($inspeccion['id_cliente']);
        if (!$cliente) return false;

        $nitCliente = $cliente['nit_cliente'];

        $existente = $reporteModel
            ->where('id_cliente', $inspeccion['id_cliente'])
            ->where('id_report_type', 6)
            ->where('id_detailreport', $idDetailReport)
            ->like('observaciones', $tag . $inspeccion['id'])
            ->first();

        $destDir = UPLOADS_PATH . $nitCliente;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $fileName = basename($pdfPath);
        $destPath = $destDir . '/' . $fileName;
        copy(FCPATH . $pdfPath, $destPath);

        $tipoLabel = $idDetailReport === 22 ? 'LISTADO ASISTENCIA' : 'RESPONSABILIDADES SST';

        $data = [
            'titulo_reporte'  => $tipoLabel . ' - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $inspeccion['fecha_sesion'],
            'id_detailreport' => $idDetailReport,
            'id_report_type'  => 6,
            'id_cliente'      => $inspeccion['id_cliente'],
            'estado'          => 'CERRADO',
            'observaciones'   => 'Generado automaticamente desde modulo de inspecciones. ' . $tag . $inspeccion['id'],
            'enlace'          => base_url(UPLOADS_URL_PREFIX . '/' . $nitCliente . '/' . $fileName),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        if ($existente) {
            return $reporteModel->update($existente['id_reporte'], $data);
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        return $reporteModel->save($data);
    }

    /**
     * API: genera el objetivo de una sesión de inducción/capacitación usando OpenAI.
     */
    public function generarObjetivo()
    {
        $tema = trim($this->request->getJSON(true)['tema'] ?? '');

        if (!$tema) {
            return $this->response->setJSON(['error' => 'Tema vacío.'])->setStatusCode(400);
        }

        $apiKey = env('OPENAI_API_KEY');
        if (!$apiKey) {
            return $this->response->setJSON(['error' => 'API key no configurada.'])->setStatusCode(500);
        }

        $prompt = "Eres un experto en Seguridad y Salud en el Trabajo (SST) para propiedades horizontales colombianas (conjuntos residenciales y edificios). El personal que asiste a las sesiones son principalmente contratistas de aseo y vigilancia, y ocasionalmente residentes y administración.

Redacta el objetivo de la siguiente sesión de inducción o capacitación en SST: «{$tema}».

El objetivo debe:
- Ser claro, concreto y profesional
- Estar en infinitivo (Capacitar, Sensibilizar, Fortalecer, Instruir, etc.)
- Tener máximo 3 oraciones
- Mencionar el perfil del personal cuando aplique (contratistas de aseo, vigilancia, residentes o administración)
- No incluir títulos ni numeración, solo el texto del objetivo";

        $payload = json_encode([
            'model'       => env('OPENAI_MODEL', 'gpt-4o-mini'),
            'messages'    => [['role' => 'user', 'content' => $prompt]],
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
            CURLOPT_TIMEOUT => 20,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!$response || $httpCode !== 200) {
            log_message('error', 'AsistenciaInduccion generarObjetivo OpenAI HTTP ' . $httpCode . ': ' . $response);
            return $this->response->setJSON(['error' => 'Error al contactar la IA. Intenta de nuevo.'])->setStatusCode(500);
        }

        $data = json_decode($response, true);
        $objetivo = trim($data['choices'][0]['message']['content'] ?? '');

        if (!$objetivo) {
            return $this->response->setJSON(['error' => 'La IA no devolvió respuesta.'])->setStatusCode(500);
        }

        return $this->response->setJSON(['objetivo' => $objetivo]);
    }
}
