<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\PlanContingenciaPlagasModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use App\Libraries\InspeccionEmailNotifier;
use Dompdf\Dompdf;
use App\Traits\AutosaveJsonTrait;
use App\Traits\ImagenCompresionTrait;

class PlanContingenciaPlagasController extends BaseController
{
    use AutosaveJsonTrait;
    use ImagenCompresionTrait;
    protected PlanContingenciaPlagasModel $inspeccionModel;

    public function __construct()
    {
        $this->inspeccionModel = new PlanContingenciaPlagasModel();
    }

    public function list()
    {
        $inspecciones = $this->inspeccionModel
            ->select('tbl_plan_contingencia_plagas.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_plan_contingencia_plagas.id_cliente', 'left')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_plan_contingencia_plagas.id_consultor', 'left')
            ->orderBy('tbl_plan_contingencia_plagas.fecha_programa', 'DESC')
            ->findAll();

        return view('inspecciones/layout_pwa', [
            'title'   => 'Plan de Contingencia — Plagas',
            'content' => view('inspecciones/contingencia-plagas/list', [
                'inspecciones' => $inspecciones,
            ]),
        ]);
    }

    public function create($idCliente = null)
    {
        return view('inspecciones/layout_pwa', [
            'title'   => 'Nuevo Plan Contingencia Plagas',
            'content' => view('inspecciones/contingencia-plagas/form', [
                'inspeccion' => null,
                'idCliente'  => $idCliente,
            ]),
        ]);
    }

    public function store()
    {
        $userId = session()->get('user_id');
        $isAutosave = $this->isAutosaveRequest();

        if (!$isAutosave) {
            $rules = [
                'id_cliente'     => 'required|integer',
                'fecha_programa' => 'required|valid_date',
            ];
            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('error', 'Por favor completa los campos requeridos.');
            }
        }

        $data = [
            'id_cliente'         => $this->request->getPost('id_cliente'),
            'id_consultor'       => $userId,
            'fecha_programa'     => $this->request->getPost('fecha_programa'),
            'nombre_responsable' => $this->request->getPost('nombre_responsable'),
            'empresa_fumigadora' => $this->request->getPost('empresa_fumigadora'),
            'estado'             => 'borrador',
        ];

        $this->inspeccionModel->insert($data);
        $id = $this->inspeccionModel->getInsertID();

        if ($isAutosave) {
            return $this->autosaveJsonSuccess($id);
        }

        return redirect()->to("/inspecciones/contingencia-plagas/edit/{$id}")->with('msg', 'Plan guardado como borrador.');
    }

    public function edit($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/contingencia-plagas')->with('error', 'Registro no encontrado.');
        }

        return view('inspecciones/layout_pwa', [
            'title'   => 'Editar Plan Contingencia Plagas',
            'content' => view('inspecciones/contingencia-plagas/form', [
                'inspeccion' => $inspeccion,
                'idCliente'  => $inspeccion['id_cliente'],
            ]),
        ]);
    }

    public function update($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            if ($this->isAutosaveRequest()) {
                return $this->autosaveJsonError('No encontrada', 404);
            }
            return redirect()->to('/inspecciones/contingencia-plagas')->with('error', 'No se puede editar.');
        }

        $data = [
            'id_cliente'         => $this->request->getPost('id_cliente'),
            'fecha_programa'     => $this->request->getPost('fecha_programa'),
            'nombre_responsable' => $this->request->getPost('nombre_responsable'),
            'empresa_fumigadora' => $this->request->getPost('empresa_fumigadora'),
        ];

        $this->inspeccionModel->update($id, $data);

        if ($this->request->getPost('finalizar')) {
            return $this->finalizar($id);
        }

        if ($this->isAutosaveRequest()) {
            return $this->autosaveJsonSuccess((int)$id);
        }

        return redirect()->to("/inspecciones/contingencia-plagas/edit/{$id}")->with('msg', 'Cambios guardados.');
    }

    public function view($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/contingencia-plagas')->with('error', 'Registro no encontrado.');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        return view('inspecciones/layout_pwa', [
            'title'   => 'Plan de Contingencia — Plagas',
            'content' => view('inspecciones/contingencia-plagas/view', [
                'inspeccion' => $inspeccion,
                'cliente'    => $clientModel->find($inspeccion['id_cliente']),
                'consultor'  => $consultantModel->find($inspeccion['id_consultor']),
            ]),
        ]);
    }

    public function finalizar($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/contingencia-plagas')->with('error', 'Registro no encontrado.');
        }

        $pdfPath = $this->generarPdfInterno($id);
        $this->inspeccionModel->update($id, ['estado' => 'completo', 'ruta_pdf' => $pdfPath]);

        $inspeccion = $this->inspeccionModel->find($id);
        $this->uploadToReportes($inspeccion, $pdfPath);

        $emailResult = InspeccionEmailNotifier::enviar(
            (int) $inspeccion['id_cliente'],
            (int) $inspeccion['id_consultor'],
            'PLAN DE CONTINGENCIA — INFESTACIÓN DE PLAGAS',
            $inspeccion['fecha_programa'],
            $pdfPath,
            (int) $inspeccion['id'],
            'PlanContingenciaPlagas',
            $inspeccion['nombre_responsable'] ?? ''
        );

        $msg = 'Plan finalizado y PDF generado.';
        if ($emailResult['success']) {
            $msg .= ' ' . $emailResult['message'];
        } else {
            $msg .= ' (Email no enviado: ' . $emailResult['error'] . ')';
        }

        return redirect()->to("/inspecciones/contingencia-plagas/view/{$id}")->with('msg', $msg);
    }

    public function generatePdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/contingencia-plagas')->with('error', 'Registro no encontrado.');
        }

        $pdfPath = $this->generarPdfInterno($id);
        $this->inspeccionModel->update($id, ['ruta_pdf' => $pdfPath]);

        $pdfContent = file_get_contents(FCPATH . $pdfPath);
        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="contingencia_plagas_' . $id . '.pdf"')
            ->setBody($pdfContent);
    }

    public function regenerarPdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || ($inspeccion['estado'] ?? '') !== 'completo') {
            return redirect()->to('/inspecciones/contingencia-plagas')->with('error', 'Solo se puede regenerar un registro finalizado.');
        }

        $pdfPath = $this->generarPdfInterno($id);
        $this->inspeccionModel->update($id, ['ruta_pdf' => $pdfPath]);
        $inspeccion = $this->inspeccionModel->find($id);
        $this->uploadToReportes($inspeccion, $pdfPath);

        return redirect()->to("/inspecciones/contingencia-plagas/view/{$id}")->with('msg', 'PDF regenerado exitosamente.');
    }

    public function delete($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if ($inspeccion && !empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) {
            unlink(FCPATH . $inspeccion['ruta_pdf']);
        }
        $this->inspeccionModel->delete($id);
        return redirect()->to('/inspecciones/contingencia-plagas')->with('msg', 'Plan eliminado.');
    }

    public function enviarEmail($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || $inspeccion['estado'] !== 'completo' || empty($inspeccion['ruta_pdf'])) {
            return redirect()->to("/inspecciones/contingencia-plagas/view/{$id}")->with('error', 'El documento debe estar finalizado con PDF para enviar email.');
        }

        $result = InspeccionEmailNotifier::enviar(
            (int) $inspeccion['id_cliente'],
            (int) $inspeccion['id_consultor'],
            'PLAN DE CONTINGENCIA — INFESTACIÓN DE PLAGAS',
            $inspeccion['fecha_programa'],
            $inspeccion['ruta_pdf'],
            (int) $inspeccion['id'],
            'PlanContingenciaPlagas',
            $inspeccion['nombre_responsable'] ?? ''
        );

        if ($result['success']) {
            return redirect()->to("/inspecciones/contingencia-plagas/view/{$id}")->with('msg', $result['message']);
        }
        return redirect()->to("/inspecciones/contingencia-plagas/view/{$id}")->with('error', $result['error']);
    }

    public function documento()
    {
        $data = [
            'inspeccion' => [
                'fecha_programa'     => date('Y-m-d'),
                'nombre_responsable' => 'Administrador(a) del Conjunto',
                'empresa_fumigadora' => '',
            ],
            'cliente'    => ['nombre_cliente' => 'CONJUNTO RESIDENCIAL — EJEMPLO'],
            'consultor'  => ['nombre_consultor' => 'Consultor SST'],
            'logoBase64' => '',
        ];
        $html = view('inspecciones/contingencia-plagas/pdf', $data);
        $css = '<style>body{font-size:20px!important;line-height:1.8!important;max-width:1100px;margin:40px auto!important;padding:30px!important}.main-title{font-size:30px!important}.subtitle{font-size:24px!important}.section-title{font-size:24px!important;margin-top:32px!important;padding-bottom:6px!important}.subsection-title{font-size:22px!important;margin-top:24px!important}td,th,li,p{font-size:20px!important}.data-table td,.data-table th{padding:10px 14px!important}.header-table .title-cell{font-size:18px!important}.header-table .code-cell{font-size:16px!important}</style>';
        return str_replace('</head>', $css . '</head>', $html);
    }

    private function generarPdfInterno($id): string
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

        $data = [
            'inspeccion' => $inspeccion,
            'cliente'    => $cliente,
            'consultor'  => $consultor,
            'logoBase64' => $logoBase64,
        ];

        $html = view('inspecciones/contingencia-plagas/pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $pdfDir = 'uploads/inspecciones/contingencia-plagas/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) {
            mkdir(FCPATH . $pdfDir, 0755, true);
        }

        if (!empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) {
            unlink(FCPATH . $inspeccion['ruta_pdf']);
        }

        $pdfFileName = 'contingencia_plagas_' . $id . '_' . date('Ymd_His') . '.pdf';
        $pdfPath = $pdfDir . $pdfFileName;
        file_put_contents(FCPATH . $pdfPath, $dompdf->output());

        return $pdfPath;
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
            ->where('id_detailreport', 36)
            ->like('observaciones', 'cont_plagas_id:' . $inspeccion['id'])
            ->first();

        $destDir = ROOTPATH . 'public/uploads/' . $nitCliente;
        if (!is_dir($destDir)) mkdir($destDir, 0755, true);

        $fileName = 'contingencia_plagas_' . $inspeccion['id'] . '_' . date('Ymd_His') . '.pdf';
        $destPath = $destDir . '/' . $fileName;
        copy(FCPATH . $pdfPath, $destPath);

        $data = [
            'titulo_reporte'  => 'PLAN CONTINGENCIA PLAGAS - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $inspeccion['fecha_programa'],
            'id_detailreport' => 36,
            'id_report_type'  => 6,
            'id_cliente'      => $inspeccion['id_cliente'],
            'estado'          => 'CERRADO',
            'observaciones'   => 'Generado automaticamente desde modulo de inspecciones. cont_plagas_id:' . $inspeccion['id'],
            'enlace'          => base_url('uploads/' . $nitCliente . '/' . $fileName),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        if ($existente) {
            return $reporteModel->update($existente['id_reporte'], $data);
        }
        $data['created_at'] = date('Y-m-d H:i:s');
        return (bool) $reporteModel->save($data);
    }
}
