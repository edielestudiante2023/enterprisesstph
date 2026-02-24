<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\InspeccionComunicacionModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use Dompdf\Dompdf;

class InspeccionComunicacionController extends BaseController
{
    protected InspeccionComunicacionModel $inspeccionModel;

    /**
     * 8 tipos de equipo de comunicación en caso de emergencias.
     */
    public const EQUIPOS = [
        'telefono_fijo'       => ['label' => 'Telefono Fijo',                  'icon' => 'fa-phone'],
        'telefonia_celular'   => ['label' => 'Telefonia Celular',              'icon' => 'fa-mobile-alt'],
        'radio_onda_corta'    => ['label' => 'Radio de Onda Corta',            'icon' => 'fa-broadcast-tower'],
        'software_citofonia'  => ['label' => 'Software Citofonia',             'icon' => 'fa-headset'],
        'megafonia'           => ['label' => 'Sistemas de Megafonia',          'icon' => 'fa-bullhorn'],
        'cctv_audio'          => ['label' => 'CCTV con Audio',                 'icon' => 'fa-video'],
        'alarma_comunicacion' => ['label' => 'Alarma con Comunicacion',        'icon' => 'fa-bell'],
        'voip'                => ['label' => 'Voz sobre IP (VOIP)',            'icon' => 'fa-wifi'],
    ];

    public function __construct()
    {
        $this->inspeccionModel = new InspeccionComunicacionModel();
    }

    public function list()
    {
        $userId = session()->get('user_id');
        $role = session()->get('role');

        if ($role === 'admin') {
            $inspecciones = $this->inspeccionModel
                ->select('tbl_inspeccion_comunicaciones.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
                ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_comunicaciones.id_cliente', 'left')
                ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_comunicaciones.id_consultor', 'left')
                ->orderBy('tbl_inspeccion_comunicaciones.fecha_inspeccion', 'DESC')
                ->findAll();
        } else {
            $inspecciones = $this->inspeccionModel->getByConsultor($userId);
        }

        $data = [
            'title'        => 'Inspeccion Equipos de Comunicacion',
            'inspecciones' => $inspecciones,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/comunicaciones/list', $data),
            'title'   => 'Comunicaciones',
        ]);
    }

    public function create($idCliente = null)
    {
        $data = [
            'title'      => 'Nueva Inspeccion de Comunicaciones',
            'inspeccion' => null,
            'idCliente'  => $idCliente,
            'equipos'    => self::EQUIPOS,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/comunicaciones/form', $data),
            'title'   => 'Nueva Comunicaciones',
        ]);
    }

    public function store()
    {
        $userId = session()->get('user_id');

        if (!$this->validate(['id_cliente' => 'required|integer', 'fecha_inspeccion' => 'required|valid_date'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->getInspeccionPostData();
        $data['id_consultor'] = $userId;
        $data['estado'] = 'borrador';

        // Fotos evidencia
        foreach (['foto_1', 'foto_2'] as $campo) {
            $data[$campo] = $this->uploadFoto($campo, 'uploads/inspecciones/comunicaciones/fotos/');
        }

        $this->inspeccionModel->insert($data);
        $idInspeccion = $this->inspeccionModel->getInsertID();

        return redirect()->to('/inspecciones/comunicaciones/edit/' . $idInspeccion)
            ->with('msg', 'Inspeccion guardada como borrador');
    }

    public function edit($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/comunicaciones')->with('error', 'Inspeccion no encontrada');
        }

        if ($inspeccion['estado'] === 'completo') {
            return redirect()->to('/inspecciones/comunicaciones/view/' . $id);
        }

        $data = [
            'title'      => 'Editar Inspeccion de Comunicaciones',
            'inspeccion' => $inspeccion,
            'idCliente'  => $inspeccion['id_cliente'],
            'equipos'    => self::EQUIPOS,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/comunicaciones/form', $data),
            'title'   => 'Editar Comunicaciones',
        ]);
    }

    public function update($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || $inspeccion['estado'] === 'completo') {
            return redirect()->to('/inspecciones/comunicaciones')->with('error', 'No se puede editar');
        }

        $data = $this->getInspeccionPostData();

        // Fotos (preservar si no se sube nueva)
        foreach (['foto_1', 'foto_2'] as $campo) {
            $nueva = $this->uploadFoto($campo, 'uploads/inspecciones/comunicaciones/fotos/');
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

        return redirect()->to('/inspecciones/comunicaciones/edit/' . $id)
            ->with('msg', 'Inspeccion actualizada');
    }

    public function view($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/comunicaciones')->with('error', 'No encontrada');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $data = [
            'title'      => 'Ver Inspeccion de Comunicaciones',
            'inspeccion' => $inspeccion,
            'cliente'    => $clientModel->find($inspeccion['id_cliente']),
            'consultor'  => $consultantModel->find($inspeccion['id_consultor']),
            'equipos'    => self::EQUIPOS,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/comunicaciones/view', $data),
            'title'   => 'Ver Comunicaciones',
        ]);
    }

    public function finalizar($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/comunicaciones')->with('error', 'No encontrada');
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

        return redirect()->to('/inspecciones/comunicaciones/view/' . $id)
            ->with('msg', 'Inspeccion finalizada y PDF generado');
    }

    public function generatePdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/comunicaciones')->with('error', 'No encontrada');
        }

        $pdfPath = $this->generarPdfInterno($id);
        $fullPath = FCPATH . $pdfPath;
        if (!file_exists($fullPath)) {
            return redirect()->back()->with('error', 'PDF no encontrado');
        }

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="comunicaciones_' . $id . '.pdf"')
            ->setBody(file_get_contents($fullPath));
    }

    public function delete($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/comunicaciones')->with('error', 'No encontrada');
        }
        if ($inspeccion['estado'] === 'completo') {
            return redirect()->to('/inspecciones/comunicaciones')->with('error', 'No se puede eliminar una inspeccion completa');
        }

        // Eliminar fotos
        foreach (['foto_1', 'foto_2'] as $campo) {
            if (!empty($inspeccion[$campo]) && file_exists(FCPATH . $inspeccion[$campo])) {
                unlink(FCPATH . $inspeccion[$campo]);
            }
        }

        if (!empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) {
            unlink(FCPATH . $inspeccion['ruta_pdf']);
        }

        $this->inspeccionModel->delete($id);

        return redirect()->to('/inspecciones/comunicaciones')->with('msg', 'Inspeccion eliminada');
    }

    // ===== MÉTODOS PRIVADOS =====

    private function getInspeccionPostData(): array
    {
        $data = [
            'id_cliente'           => $this->request->getPost('id_cliente'),
            'fecha_inspeccion'     => $this->request->getPost('fecha_inspeccion'),
            'observaciones_finales' => $this->request->getPost('observaciones_finales'),
        ];

        foreach (self::EQUIPOS as $key => $info) {
            $data['cant_' . $key] = (int) $this->request->getPost('cant_' . $key);
            $data['obs_' . $key]  = $this->request->getPost('obs_' . $key);
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
        return $dir . $fileName;
    }

    private function generarPdfInterno(int $id): ?string
    {
        $inspeccion = $this->inspeccionModel->find($id);
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $cliente = $clientModel->find($inspeccion['id_cliente']);
        $consultor = $consultantModel->find($inspeccion['id_consultor']);

        // Logo en base64
        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoMime = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        // Fotos evidencia a base64
        $fotosBase64 = [];
        foreach (['foto_1', 'foto_2'] as $campo) {
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
            'equipos'     => self::EQUIPOS,
            'logoBase64'  => $logoBase64,
            'fotosBase64' => $fotosBase64,
        ];

        $html = view('inspecciones/comunicaciones/pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $pdfDir = 'uploads/inspecciones/comunicaciones/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) {
            mkdir(FCPATH . $pdfDir, 0755, true);
        }

        $pdfFileName = 'comunicaciones_' . $id . '_' . date('Ymd_His') . '.pdf';
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
            ->where('id_detailreport', 15)
            ->like('observaciones', 'insp_com_id:' . $inspeccion['id'])
            ->first();

        $destDir = ROOTPATH . 'public/uploads/' . $nitCliente;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $fileName = 'comunicaciones_' . $inspeccion['id'] . '_' . date('Ymd_His') . '.pdf';
        $destPath = $destDir . '/' . $fileName;
        copy(FCPATH . $pdfPath, $destPath);

        $data = [
            'titulo_reporte'  => 'INSPECCION COMUNICACIONES - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $inspeccion['fecha_inspeccion'],
            'id_detailreport' => 15,
            'id_report_type'  => 6,
            'id_cliente'      => $inspeccion['id_cliente'],
            'estado'          => 'Activo',
            'observaciones'   => 'Generado automaticamente desde modulo de inspecciones. insp_com_id:' . $inspeccion['id'],
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
