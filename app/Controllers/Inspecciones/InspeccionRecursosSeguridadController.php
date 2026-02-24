<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\InspeccionRecursosSeguridadModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use Dompdf\Dompdf;

class InspeccionRecursosSeguridadController extends BaseController
{
    protected InspeccionRecursosSeguridadModel $inspeccionModel;

    /**
     * 6 tipos de recurso de seguridad.
     * planes_respuesta NO tiene foto.
     */
    public const RECURSOS = [
        'lamparas' => [
            'label'      => 'Lamparas de Emergencia',
            'icon'       => 'fa-lightbulb',
            'hint'       => 'Ubicacion Estrategica - Mantenimiento Regular - Senalizacion Clara',
            'tiene_foto' => true,
        ],
        'antideslizantes' => [
            'label'      => 'Antideslizantes',
            'icon'       => 'fa-shoe-prints',
            'hint'       => 'Superficies Seguras - Mantenimiento Regular - Senalizacion Preventiva',
            'tiene_foto' => true,
        ],
        'pasamanos' => [
            'label'      => 'Pasamanos',
            'icon'       => 'fa-hand-holding',
            'hint'       => 'Instalacion Segura - Altura y Ubicacion Adecuadas - Material Resistente',
            'tiene_foto' => true,
        ],
        'vigilancia' => [
            'label'      => 'Sistemas de Vigilancia y Control de Acceso',
            'icon'       => 'fa-camera',
            'hint'       => 'Camaras de Seguridad y Control de Acceso para Monitorear y Restringir Ingreso',
            'tiene_foto' => true,
        ],
        'iluminacion' => [
            'label'      => 'Iluminacion Exterior',
            'icon'       => 'fa-sun',
            'hint'       => 'Iluminacion Adecuada en Areas Exteriores para Disuadir Actividad Delictiva',
            'tiene_foto' => true,
        ],
        'planes_respuesta' => [
            'label'      => 'Planes de Respuesta a Emergencias',
            'icon'       => 'fa-file-alt',
            'hint'       => 'Desarrollo de Planes de Respuesta para Seguridad de Residentes',
            'tiene_foto' => false,
        ],
    ];

    public function __construct()
    {
        $this->inspeccionModel = new InspeccionRecursosSeguridadModel();
    }

    public function list()
    {
        $userId = session()->get('user_id');
        $role = session()->get('role');

        if ($role === 'admin') {
            $inspecciones = $this->inspeccionModel
                ->select('tbl_inspeccion_recursos_seguridad.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
                ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_recursos_seguridad.id_cliente', 'left')
                ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_recursos_seguridad.id_consultor', 'left')
                ->orderBy('tbl_inspeccion_recursos_seguridad.fecha_inspeccion', 'DESC')
                ->findAll();
        } else {
            $inspecciones = $this->inspeccionModel->getByConsultor($userId);
        }

        $data = [
            'title'        => 'Inspeccion Recursos de Seguridad',
            'inspecciones' => $inspecciones,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/recursos-seguridad/list', $data),
            'title'   => 'Recursos Seguridad',
        ]);
    }

    public function create($idCliente = null)
    {
        $data = [
            'title'      => 'Nueva Inspeccion de Recursos de Seguridad',
            'inspeccion' => null,
            'idCliente'  => $idCliente,
            'recursos'   => self::RECURSOS,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/recursos-seguridad/form', $data),
            'title'   => 'Nueva Rec. Seguridad',
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

        // Fotos por recurso
        foreach (self::RECURSOS as $key => $info) {
            if (!empty($info['tiene_foto'])) {
                $data['foto_' . $key] = $this->uploadFoto('foto_' . $key, 'uploads/inspecciones/recursos-seguridad/fotos/');
            }
        }

        $this->inspeccionModel->insert($data);
        $idInspeccion = $this->inspeccionModel->getInsertID();

        return redirect()->to('/inspecciones/recursos-seguridad/edit/' . $idInspeccion)
            ->with('msg', 'Inspeccion guardada como borrador');
    }

    public function edit($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/recursos-seguridad')->with('error', 'Inspeccion no encontrada');
        }

        if ($inspeccion['estado'] === 'completo') {
            return redirect()->to('/inspecciones/recursos-seguridad/view/' . $id);
        }

        $data = [
            'title'      => 'Editar Inspeccion de Recursos de Seguridad',
            'inspeccion' => $inspeccion,
            'idCliente'  => $inspeccion['id_cliente'],
            'recursos'   => self::RECURSOS,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/recursos-seguridad/form', $data),
            'title'   => 'Editar Rec. Seguridad',
        ]);
    }

    public function update($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || $inspeccion['estado'] === 'completo') {
            return redirect()->to('/inspecciones/recursos-seguridad')->with('error', 'No se puede editar');
        }

        $data = $this->getInspeccionPostData();

        // Fotos por recurso (preservar si no se sube nueva)
        foreach (self::RECURSOS as $key => $info) {
            if (!empty($info['tiene_foto'])) {
                $campo = 'foto_' . $key;
                $nueva = $this->uploadFoto($campo, 'uploads/inspecciones/recursos-seguridad/fotos/');
                if ($nueva) {
                    if (!empty($inspeccion[$campo]) && file_exists(FCPATH . $inspeccion[$campo])) {
                        unlink(FCPATH . $inspeccion[$campo]);
                    }
                    $data[$campo] = $nueva;
                }
            }
        }

        $this->inspeccionModel->update($id, $data);

        if ($this->request->getPost('finalizar')) {
            return $this->finalizar($id);
        }

        return redirect()->to('/inspecciones/recursos-seguridad/edit/' . $id)
            ->with('msg', 'Inspeccion actualizada');
    }

    public function view($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/recursos-seguridad')->with('error', 'No encontrada');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $data = [
            'title'      => 'Ver Inspeccion de Recursos de Seguridad',
            'inspeccion' => $inspeccion,
            'cliente'    => $clientModel->find($inspeccion['id_cliente']),
            'consultor'  => $consultantModel->find($inspeccion['id_consultor']),
            'recursos'   => self::RECURSOS,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/recursos-seguridad/view', $data),
            'title'   => 'Ver Rec. Seguridad',
        ]);
    }

    public function finalizar($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/recursos-seguridad')->with('error', 'No encontrada');
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

        return redirect()->to('/inspecciones/recursos-seguridad/view/' . $id)
            ->with('msg', 'Inspeccion finalizada y PDF generado');
    }

    public function generatePdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/recursos-seguridad')->with('error', 'No encontrada');
        }

        $pdfPath = $this->generarPdfInterno($id);
        $fullPath = FCPATH . $pdfPath;
        if (!file_exists($fullPath)) {
            return redirect()->back()->with('error', 'PDF no encontrado');
        }

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="recursos_seguridad_' . $id . '.pdf"')
            ->setBody(file_get_contents($fullPath));
    }

    public function delete($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/recursos-seguridad')->with('error', 'No encontrada');
        }
        if ($inspeccion['estado'] === 'completo') {
            return redirect()->to('/inspecciones/recursos-seguridad')->with('error', 'No se puede eliminar una inspeccion completa');
        }

        // Eliminar fotos por recurso
        foreach (self::RECURSOS as $key => $info) {
            if (!empty($info['tiene_foto'])) {
                $campo = 'foto_' . $key;
                if (!empty($inspeccion[$campo]) && file_exists(FCPATH . $inspeccion[$campo])) {
                    unlink(FCPATH . $inspeccion[$campo]);
                }
            }
        }

        if (!empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) {
            unlink(FCPATH . $inspeccion['ruta_pdf']);
        }

        $this->inspeccionModel->delete($id);

        return redirect()->to('/inspecciones/recursos-seguridad')->with('msg', 'Inspeccion eliminada');
    }

    // ===== MÉTODOS PRIVADOS =====

    private function getInspeccionPostData(): array
    {
        $data = [
            'id_cliente'       => $this->request->getPost('id_cliente'),
            'fecha_inspeccion' => $this->request->getPost('fecha_inspeccion'),
            'observaciones'    => $this->request->getPost('observaciones'),
        ];

        foreach (self::RECURSOS as $key => $info) {
            $data['obs_' . $key] = $this->request->getPost('obs_' . $key);
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

        // Fotos por recurso a base64
        $fotosBase64 = [];
        foreach (self::RECURSOS as $key => $info) {
            if (!empty($info['tiene_foto'])) {
                $campo = 'foto_' . $key;
                $fotosBase64[$campo] = '';
                if (!empty($inspeccion[$campo])) {
                    $fotoPath = FCPATH . $inspeccion[$campo];
                    if (file_exists($fotoPath)) {
                        $mime = mime_content_type($fotoPath);
                        $fotosBase64[$campo] = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fotoPath));
                    }
                }
            }
        }

        $data = [
            'inspeccion'  => $inspeccion,
            'cliente'     => $cliente,
            'consultor'   => $consultor,
            'recursos'    => self::RECURSOS,
            'logoBase64'  => $logoBase64,
            'fotosBase64' => $fotosBase64,
        ];

        $html = view('inspecciones/recursos-seguridad/pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $pdfDir = 'uploads/inspecciones/recursos-seguridad/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) {
            mkdir(FCPATH . $pdfDir, 0755, true);
        }

        $pdfFileName = 'recursos_seguridad_' . $id . '_' . date('Ymd_His') . '.pdf';
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
            ->where('id_detailreport', 16)
            ->like('observaciones', 'insp_rec_id:' . $inspeccion['id'])
            ->first();

        $destDir = ROOTPATH . 'public/uploads/' . $nitCliente;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $fileName = 'recursos_seguridad_' . $inspeccion['id'] . '_' . date('Ymd_His') . '.pdf';
        $destPath = $destDir . '/' . $fileName;
        copy(FCPATH . $pdfPath, $destPath);

        $data = [
            'titulo_reporte'  => 'INSPECCION RECURSOS SEGURIDAD - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $inspeccion['fecha_inspeccion'],
            'id_detailreport' => 16,
            'id_report_type'  => 6,
            'id_cliente'      => $inspeccion['id_cliente'],
            'estado'          => 'Activo',
            'observaciones'   => 'Generado automaticamente desde modulo de inspecciones. insp_rec_id:' . $inspeccion['id'],
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
