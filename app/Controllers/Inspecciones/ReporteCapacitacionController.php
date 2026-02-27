<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\ReporteCapacitacionModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use Dompdf\Dompdf;

class ReporteCapacitacionController extends BaseController
{
    protected ReporteCapacitacionModel $inspeccionModel;

    public function __construct()
    {
        $this->inspeccionModel = new ReporteCapacitacionModel();
    }

    public function list()
    {
        $userId = session()->get('user_id');
        $role = session()->get('role');

        if ($role === 'admin') {
            $inspecciones = $this->inspeccionModel
                ->select('tbl_reporte_capacitacion.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
                ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_reporte_capacitacion.id_cliente', 'left')
                ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_reporte_capacitacion.id_consultor', 'left')
                ->orderBy('tbl_reporte_capacitacion.fecha_capacitacion', 'DESC')
                ->findAll();
        } else {
            $inspecciones = $this->inspeccionModel->getByConsultor($userId);
        }

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
        $userId = session()->get('user_id');

        if (!$this->validate(['id_cliente' => 'required|integer', 'fecha_capacitacion' => 'required|valid_date'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->getInspeccionPostData();
        $data['id_consultor'] = $userId;
        $data['estado'] = 'borrador';

        $data['foto_listado_asistencia'] = $this->uploadFoto('foto_listado_asistencia', 'uploads/inspecciones/reporte-capacitacion/');
        $data['foto_capacitacion'] = $this->uploadFoto('foto_capacitacion', 'uploads/inspecciones/reporte-capacitacion/');
        $data['foto_evaluacion'] = $this->uploadFoto('foto_evaluacion', 'uploads/inspecciones/reporte-capacitacion/');
        $data['foto_otros_1'] = $this->uploadFoto('foto_otros_1', 'uploads/inspecciones/reporte-capacitacion/');
        $data['foto_otros_2'] = $this->uploadFoto('foto_otros_2', 'uploads/inspecciones/reporte-capacitacion/');

        $this->inspeccionModel->insert($data);
        $idInspeccion = $this->inspeccionModel->getInsertID();

        return redirect()->to('/inspecciones/reporte-capacitacion/edit/' . $idInspeccion)
            ->with('msg', 'Reporte guardado como borrador');
    }

    public function edit($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/reporte-capacitacion')->with('error', 'Reporte no encontrado');
        }

        if ($inspeccion['estado'] === 'completo') {
            return redirect()->to('/inspecciones/reporte-capacitacion/view/' . $id);
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
        if (!$inspeccion || $inspeccion['estado'] === 'completo') {
            return redirect()->to('/inspecciones/reporte-capacitacion')->with('error', 'No se puede editar');
        }

        $data = $this->getInspeccionPostData();

        foreach (['foto_listado_asistencia', 'foto_capacitacion', 'foto_evaluacion', 'foto_otros_1', 'foto_otros_2'] as $campo) {
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

        return redirect()->to('/inspecciones/reporte-capacitacion/edit/' . $id)
            ->with('msg', 'Reporte actualizado');
    }

    public function view($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/reporte-capacitacion')->with('error', 'No encontrado');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $data = [
            'title'      => 'Ver Reporte de Capacitacion',
            'inspeccion' => $inspeccion,
            'cliente'    => $clientModel->find($inspeccion['id_cliente']),
            'consultor'  => $consultantModel->find($inspeccion['id_consultor']),
            'perfilesAsistentes' => ReporteCapacitacionModel::PERFILES_ASISTENTES,
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

        return redirect()->to('/inspecciones/reporte-capacitacion/view/' . $id)
            ->with('msg', 'Reporte finalizado y PDF generado');
    }

    public function generatePdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/reporte-capacitacion')->with('error', 'No encontrado');
        }

        $pdfPath = $this->generarPdfInterno($id);
        $fullPath = FCPATH . $pdfPath;
        if (!file_exists($fullPath)) {
            return redirect()->back()->with('error', 'PDF no encontrado');
        }

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="reporte_capacitacion_' . $id . '.pdf"')
            ->setBody(file_get_contents($fullPath));
    }

    public function delete($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/reporte-capacitacion')->with('error', 'No encontrado');
        }
        if ($inspeccion['estado'] === 'completo') {
            return redirect()->to('/inspecciones/reporte-capacitacion')->with('error', 'No se puede eliminar un reporte completo');
        }

        foreach (['foto_listado_asistencia', 'foto_capacitacion', 'foto_evaluacion', 'foto_otros_1', 'foto_otros_2'] as $campo) {
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
                $logoMime = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        $fotosBase64 = [];
        foreach (['foto_listado_asistencia', 'foto_capacitacion', 'foto_evaluacion', 'foto_otros_1', 'foto_otros_2'] as $campo) {
            $fotosBase64[$campo] = '';
            if (!empty($inspeccion[$campo])) {
                $fotoPath = FCPATH . $inspeccion[$campo];
                if (file_exists($fotoPath)) {
                    $mime = mime_content_type($fotoPath);
                    $fotosBase64[$campo] = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fotoPath));
                }
            }
        }

        $data = [
            'inspeccion'  => $inspeccion,
            'cliente'     => $cliente,
            'consultor'   => $consultor,
            'perfilesAsistentes' => ReporteCapacitacionModel::PERFILES_ASISTENTES,
            'logoBase64'  => $logoBase64,
            'fotosBase64' => $fotosBase64,
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

        $destDir = ROOTPATH . 'public/uploads/' . $nitCliente;
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
            'estado'          => 'Activo',
            'observaciones'   => 'Generado automaticamente desde modulo de inspecciones. rep_cap_id:' . $inspeccion['id'],
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
