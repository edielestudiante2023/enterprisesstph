<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\ProgramaPlagasModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use Dompdf\Dompdf;

class ProgramaPlagasController extends BaseController
{
    protected ProgramaPlagasModel $inspeccionModel;

    public function __construct()
    {
        $this->inspeccionModel = new ProgramaPlagasModel();
    }

    public function list()
    {
        $userId = session()->get('user_id');
        $role = session()->get('role');

        if ($role === 'admin') {
            $inspecciones = $this->inspeccionModel
                ->select('tbl_programa_plagas.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
                ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_programa_plagas.id_cliente', 'left')
                ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_programa_plagas.id_consultor', 'left')
                ->orderBy('tbl_programa_plagas.fecha_programa', 'DESC')
                ->findAll();
        } else {
            $inspecciones = $this->inspeccionModel->getByConsultor($userId);
        }

        return view('inspecciones/layout_pwa', [
            'title'   => 'Control Integrado de Plagas',
            'content' => view('inspecciones/control-plagas/list', [
                'inspecciones' => $inspecciones,
                'role'         => $role,
            ]),
        ]);
    }

    public function create($idCliente = null)
    {
        return view('inspecciones/layout_pwa', [
            'title'   => 'Nuevo Programa Plagas',
            'content' => view('inspecciones/control-plagas/form', [
                'inspeccion' => null,
                'idCliente'  => $idCliente,
            ]),
        ]);
    }

    public function store()
    {
        $rules = [
            'id_cliente'      => 'required|integer',
            'fecha_programa'  => 'required|valid_date',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Por favor completa los campos requeridos.');
        }

        $data = [
            'id_cliente'          => $this->request->getPost('id_cliente'),
            'id_consultor'        => session()->get('user_id'),
            'fecha_programa'      => $this->request->getPost('fecha_programa'),
            'nombre_responsable'  => $this->request->getPost('nombre_responsable'),
            'estado'              => 'borrador',
        ];

        $this->inspeccionModel->insert($data);
        $id = $this->inspeccionModel->getInsertID();

        return redirect()->to("/inspecciones/control-plagas/edit/{$id}")->with('msg', 'Programa guardado como borrador.');
    }

    public function edit($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/control-plagas')->with('error', 'Registro no encontrado.');
        }

        if ($inspeccion['estado'] === 'completo') {
            return redirect()->to("/inspecciones/control-plagas/view/{$id}");
        }

        $clientModel = new ClientModel();
        $cliente = $clientModel->find($inspeccion['id_cliente']);

        return view('inspecciones/layout_pwa', [
            'title'   => 'Editar Programa Plagas',
            'content' => view('inspecciones/control-plagas/form', [
                'inspeccion' => $inspeccion,
                'idCliente'  => $inspeccion['id_cliente'],
                'cliente'    => $cliente,
            ]),
        ]);
    }

    public function update($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || $inspeccion['estado'] === 'completo') {
            return redirect()->to('/inspecciones/control-plagas')->with('error', 'No se puede editar.');
        }

        $data = [
            'id_cliente'          => $this->request->getPost('id_cliente'),
            'fecha_programa'      => $this->request->getPost('fecha_programa'),
            'nombre_responsable'  => $this->request->getPost('nombre_responsable'),
        ];

        $this->inspeccionModel->update($id, $data);

        if ($this->request->getPost('finalizar')) {
            return $this->finalizar($id);
        }

        return redirect()->to("/inspecciones/control-plagas/edit/{$id}")->with('msg', 'Cambios guardados.');
    }

    public function view($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/control-plagas')->with('error', 'Registro no encontrado.');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $cliente = $clientModel->find($inspeccion['id_cliente']);
        $consultor = $consultantModel->find($inspeccion['id_consultor']);

        return view('inspecciones/layout_pwa', [
            'title'   => 'Control Integrado de Plagas',
            'content' => view('inspecciones/control-plagas/view', [
                'inspeccion' => $inspeccion,
                'cliente'    => $cliente,
                'consultor'  => $consultor,
            ]),
        ]);
    }

    public function finalizar($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/control-plagas')->with('error', 'Registro no encontrado.');
        }

        $pdfPath = $this->generarPdfInterno($id);

        $this->inspeccionModel->update($id, [
            'estado'   => 'completo',
            'ruta_pdf' => $pdfPath,
        ]);

        $inspeccion = $this->inspeccionModel->find($id);
        $this->uploadToReportes($inspeccion, $pdfPath);

        return redirect()->to("/inspecciones/control-plagas/view/{$id}")->with('msg', 'Programa finalizado y PDF generado.');
    }

    public function generatePdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/control-plagas')->with('error', 'Registro no encontrado.');
        }

        $pdfPath = $this->generarPdfInterno($id);

        $pdfContent = file_get_contents(FCPATH . $pdfPath);
        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="programa_plagas_' . $id . '.pdf"')
            ->setBody($pdfContent);
    }

    public function delete($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/control-plagas')->with('error', 'Registro no encontrado.');
        }

        if ($inspeccion['estado'] === 'completo') {
            return redirect()->to('/inspecciones/control-plagas')->with('error', 'No se puede eliminar un programa finalizado.');
        }

        if (!empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) {
            unlink(FCPATH . $inspeccion['ruta_pdf']);
        }

        $this->inspeccionModel->delete($id);

        return redirect()->to('/inspecciones/control-plagas')->with('msg', 'Programa eliminado.');
    }

    // ── Métodos privados ──────────────────────────────────────

        public function regenerarPdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || ($inspeccion['estado'] ?? '') !== 'completo') {
            return redirect()->to('/inspecciones/control-plagas')->with('error', 'Solo se puede regenerar un registro finalizado.');
        }

        $pdfPath = $this->generarPdfInterno($id);

        $this->inspeccionModel->update($id, [
            'ruta_pdf' => $pdfPath,
        ]);

        $inspeccion = $this->inspeccionModel->find($id);
        $this->uploadToReportes($inspeccion, $pdfPath);

        return redirect()->to("/inspecciones/control-plagas/view/{$id}")->with('msg', 'PDF regenerado exitosamente.');
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
                $mime = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        $data = [
            'inspeccion' => $inspeccion,
            'cliente'    => $cliente,
            'consultor'  => $consultor,
            'logoBase64' => $logoBase64,
        ];

        $html = view('inspecciones/control-plagas/pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $pdfDir = 'uploads/inspecciones/control-plagas/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) {
            mkdir(FCPATH . $pdfDir, 0755, true);
        }

        if (!empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) {
            unlink(FCPATH . $inspeccion['ruta_pdf']);
        }

        $pdfFileName = 'programa_plagas_' . $id . '_' . date('Ymd_His') . '.pdf';
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
            ->where('id_detailreport', 30)
            ->like('observaciones', 'prog_plag_id:' . $inspeccion['id'])
            ->first();

        $destDir = ROOTPATH . 'public/uploads/' . $nitCliente;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $fileName = 'programa_plagas_' . $inspeccion['id'] . '_' . date('Ymd_His') . '.pdf';
        $destPath = $destDir . '/' . $fileName;
        copy(FCPATH . $pdfPath, $destPath);

        $data = [
            'titulo_reporte'  => 'PROGRAMA CONTROL INTEGRADO DE PLAGAS - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $inspeccion['fecha_programa'],
            'id_detailreport' => 30,
            'id_report_type'  => 6,
            'id_cliente'      => $inspeccion['id_cliente'],
            'estado'          => 'Activo',
            'observaciones'   => 'Generado automaticamente desde modulo de inspecciones. prog_plag_id:' . $inspeccion['id'],
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
