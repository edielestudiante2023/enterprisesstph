<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\AsistenciaInduccionModel;
use App\Models\AsistenciaInduccionAsistenteModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use Dompdf\Dompdf;

class AsistenciaInduccionController extends BaseController
{
    protected AsistenciaInduccionModel $inspeccionModel;

    public const TIPOS_CHARLA = [
        'induccion_reinduccion' => 'Induccion / Reinduccion',
        'reunion'              => 'Reunion',
        'charla'               => 'Charla',
        'capacitacion'         => 'Capacitacion',
        'otros_temas'          => 'Otros Temas',
    ];

    public function __construct()
    {
        $this->inspeccionModel = new AsistenciaInduccionModel();
    }

    public function list()
    {
        $userId = session()->get('user_id');
        $role = session()->get('role');

        if ($role === 'admin') {
            $inspecciones = $this->inspeccionModel
                ->select('tbl_asistencia_induccion.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
                ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_asistencia_induccion.id_cliente', 'left')
                ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_asistencia_induccion.id_consultor', 'left')
                ->orderBy('tbl_asistencia_induccion.fecha_sesion', 'DESC')
                ->findAll();
        } else {
            $inspecciones = $this->inspeccionModel->getByConsultor($userId);
        }

        $data = [
            'title'        => 'Asistencia Induccion',
            'inspecciones' => $inspecciones,
            'tiposCharla'  => self::TIPOS_CHARLA,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/asistencia-induccion/list', $data),
            'title'   => 'Asistencia Induccion',
        ]);
    }

    public function create($idCliente = null)
    {
        $data = [
            'title'       => 'Nueva Asistencia Induccion',
            'inspeccion'  => null,
            'asistentes'  => [],
            'idCliente'   => $idCliente,
            'tiposCharla' => self::TIPOS_CHARLA,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/asistencia-induccion/form', $data),
            'title'   => 'Nueva Asistencia',
        ]);
    }

    public function store()
    {
        $userId = session()->get('user_id');

        if (!$this->validate(['id_cliente' => 'required|integer', 'fecha_sesion' => 'required|valid_date'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->getInspeccionPostData();
        $data['id_consultor'] = $userId;
        $data['estado'] = 'borrador';

        $this->inspeccionModel->insert($data);
        $idInspeccion = $this->inspeccionModel->getInsertID();

        // Insert attendees
        $this->saveAsistentes($idInspeccion);

        return redirect()->to('/inspecciones/asistencia-induccion/edit/' . $idInspeccion)
            ->with('msg', 'Asistencia guardada como borrador');
    }

    public function edit($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/asistencia-induccion')->with('error', 'Registro no encontrado');
        }

        if ($inspeccion['estado'] === 'completo') {
            return redirect()->to('/inspecciones/asistencia-induccion/view/' . $id);
        }

        $asistenteModel = new AsistenciaInduccionAsistenteModel();

        $data = [
            'title'       => 'Editar Asistencia Induccion',
            'inspeccion'  => $inspeccion,
            'asistentes'  => $asistenteModel->getByAsistencia($id),
            'idCliente'   => $inspeccion['id_cliente'],
            'tiposCharla' => self::TIPOS_CHARLA,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/asistencia-induccion/form', $data),
            'title'   => 'Editar Asistencia',
        ]);
    }

    public function update($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || $inspeccion['estado'] === 'completo') {
            return redirect()->to('/inspecciones/asistencia-induccion')->with('error', 'No se puede editar');
        }

        $data = $this->getInspeccionPostData();
        $this->inspeccionModel->update($id, $data);

        // Delete existing attendees and re-insert
        $asistenteModel = new AsistenciaInduccionAsistenteModel();
        $existentes = $asistenteModel->getByAsistencia($id);

        // Preserve firma paths before deleting
        $firmasPrevias = [];
        foreach ($existentes as $ex) {
            if (!empty($ex['cedula'])) {
                $firmasPrevias[$ex['cedula']] = $ex['firma'] ?? '';
            }
        }

        $asistenteModel->where('id_asistencia', $id)->delete();
        $this->saveAsistentes($id, $firmasPrevias);

        if ($this->request->getPost('finalizar')) {
            return $this->finalizar($id);
        }

        return redirect()->to('/inspecciones/asistencia-induccion/edit/' . $id)
            ->with('msg', 'Asistencia actualizada');
    }

    public function view($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/asistencia-induccion')->with('error', 'No encontrado');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $asistenteModel = new AsistenciaInduccionAsistenteModel();

        $data = [
            'title'       => 'Ver Asistencia Induccion',
            'inspeccion'  => $inspeccion,
            'cliente'     => $clientModel->find($inspeccion['id_cliente']),
            'consultor'   => $consultantModel->find($inspeccion['id_consultor']),
            'asistentes'  => $asistenteModel->getByAsistencia($id),
            'tiposCharla' => self::TIPOS_CHARLA,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/asistencia-induccion/view', $data),
            'title'   => 'Ver Asistencia',
        ]);
    }

    public function firmas($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/asistencia-induccion')->with('error', 'No encontrado');
        }

        $asistenteModel = new AsistenciaInduccionAsistenteModel();

        $data = [
            'title'      => 'Firmas Asistencia',
            'inspeccion' => $inspeccion,
            'asistentes' => $asistenteModel->getByAsistencia($id),
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/asistencia-induccion/firmas', $data),
            'title'   => 'Firmas Asistencia',
        ]);
    }

    public function guardarFirma($idAsistente)
    {
        $asistenteModel = new AsistenciaInduccionAsistenteModel();
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
            return redirect()->to('/inspecciones/asistencia-induccion')->with('error', 'No encontrado');
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

        // Upload FT-SST-005 (always)
        $this->uploadToReportes($inspeccion, $result['asistencia'], 22, 'asist_ind_id:');

        // Upload FT-SST-003 (only if induccion_reinduccion)
        if (!empty($result['responsabilidades'])) {
            $this->uploadToReportes($inspeccion, $result['responsabilidades'], 23, 'asist_ind_resp_id:');
        }

        return redirect()->to('/inspecciones/asistencia-induccion/view/' . $id)
            ->with('msg', 'Asistencia finalizada y PDF(s) generado(s)');
    }

    public function generatePdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/asistencia-induccion')->with('error', 'No encontrado');
        }

        $result = $this->generarPdfInterno($id);
        $fullPath = FCPATH . $result['asistencia'];
        if (!file_exists($fullPath)) {
            return redirect()->back()->with('error', 'PDF no encontrado');
        }

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="asistencia_induccion_' . $id . '.pdf"')
            ->setBody(file_get_contents($fullPath));
    }

    public function delete($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/asistencia-induccion')->with('error', 'No encontrado');
        }
        if ($inspeccion['estado'] === 'completo') {
            return redirect()->to('/inspecciones/asistencia-induccion')->with('error', 'No se puede eliminar una asistencia completa');
        }

        // Delete attendee firmas
        $asistenteModel = new AsistenciaInduccionAsistenteModel();
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

        return redirect()->to('/inspecciones/asistencia-induccion')->with('msg', 'Registro eliminado');
    }

    // ===== PRIVATE METHODS =====

        public function regenerarPdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || ($inspeccion['estado'] ?? '') !== 'completo') {
            return redirect()->to('/inspecciones/asistencia-induccion')->with('error', 'Solo se puede regenerar un registro finalizado.');
        }

        $pdfPath = $this->generarPdfInterno($id);

        $this->inspeccionModel->update($id, [
            'ruta_pdf' => $pdfPath,
        ]);

        $inspeccion = $this->inspeccionModel->find($id);
        $this->uploadToReportes($inspeccion, $pdfPath);

        return redirect()->to("/inspecciones/asistencia-induccion/view/{$id}")->with('msg', 'PDF regenerado exitosamente.');
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
        $asistenteModel = new AsistenciaInduccionAsistenteModel();

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
        $asistenteModel = new AsistenciaInduccionAsistenteModel();
        $cliente = $clientModel->find($inspeccion['id_cliente']);
        $consultor = $consultantModel->find($inspeccion['id_consultor']);

        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoMime = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode(file_get_contents($logoPath));
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
        $html1 = view('inspecciones/asistencia-induccion/pdf', $data);

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
            $html2 = view('inspecciones/asistencia-induccion/pdf', $data);

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

        $destDir = ROOTPATH . 'public/uploads/' . $nitCliente;
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
            'estado'          => 'Activo',
            'observaciones'   => 'Generado automaticamente desde modulo de inspecciones. ' . $tag . $inspeccion['id'],
            'enlace'          => base_url('uploads/' . $nitCliente . '/' . $fileName),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        if ($existente) {
            return $reporteModel->update($existente['id_reporte'], $data);
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        return $reporteModel->save($data);
    }
}
