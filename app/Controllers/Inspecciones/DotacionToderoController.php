<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\DotacionToderoModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use Dompdf\Dompdf;

class DotacionToderoController extends BaseController
{
    protected DotacionToderoModel $inspeccionModel;

    public const ITEMS_EPP = [
        'tapabocas'         => ['label' => 'Tapabocas Desechable', 'icon' => 'fa-head-side-mask'],
        'guantes_nitrilo'   => ['label' => 'Guantes de Nitrilo', 'icon' => 'fa-hand'],
        'mascarilla_polvo'  => ['label' => 'Mascarilla Para Polvo Desechable Sin Filtro', 'icon' => 'fa-mask-face'],
        'guantes_nylon'     => ['label' => 'Guantes Nylon Recubierto Con Poliuretano', 'icon' => 'fa-hand'],
        'guantes_caucho'    => ['label' => 'Guantes de Caucho Calibre 20, 25, 50', 'icon' => 'fa-mitten'],
        'gafas'             => ['label' => 'Gafas de Seguridad', 'icon' => 'fa-glasses'],
        'uniforme'          => ['label' => 'Uniforme - Camisa Pantalon - Overol', 'icon' => 'fa-shirt'],
        'sombrero'          => ['label' => 'Sombrero / Gorra', 'icon' => 'fa-hat-cowboy'],
        'zapato'            => ['label' => 'Zapato Antideslizante', 'icon' => 'fa-shoe-prints'],
        'casco'             => ['label' => 'Casco de Seguridad Con Rachet', 'icon' => 'fa-hard-hat'],
        'careta'            => ['label' => 'Careta de Proteccion', 'icon' => 'fa-head-side-virus'],
        'protector_auditivo'=> ['label' => 'Protector Auditivo de Copa', 'icon' => 'fa-headphones'],
        'respirador'        => ['label' => 'Respirador de Media Cara', 'icon' => 'fa-mask-ventilator'],
        'guantes_vaqueta'   => ['label' => 'Guantes de Vaqueta', 'icon' => 'fa-mitten'],
        'botas_dielectricas'=> ['label' => 'Botas de Seguridad Dielectricas', 'icon' => 'fa-boot'],
        'delantal_pvc'      => ['label' => 'Delantal de PVC', 'icon' => 'fa-vest'],
    ];

    public const ESTADOS_EPP = [
        'bueno'      => 'Bueno',
        'regular'    => 'Regular',
        'deficiente' => 'Deficiente',
        'no_tiene'   => 'No Tiene',
        'no_aplica'  => 'No Aplica',
    ];

    public function __construct()
    {
        $this->inspeccionModel = new DotacionToderoModel();
    }

    public function list()
    {
        $userId = session()->get('user_id');
        $role = session()->get('role');

        if ($role === 'admin') {
            $inspecciones = $this->inspeccionModel
                ->select('tbl_dotacion_todero.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
                ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_dotacion_todero.id_cliente', 'left')
                ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_dotacion_todero.id_consultor', 'left')
                ->orderBy('tbl_dotacion_todero.fecha_inspeccion', 'DESC')
                ->findAll();
        } else {
            $inspecciones = $this->inspeccionModel->getByConsultor($userId);
        }

        $data = [
            'title'        => 'Dotacion Todero',
            'inspecciones' => $inspecciones,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/dotacion-todero/list', $data),
            'title'   => 'Dotacion Todero',
        ]);
    }

    public function create($idCliente = null)
    {
        $data = [
            'title'      => 'Nueva Inspeccion Dotacion Todero',
            'inspeccion' => null,
            'idCliente'  => $idCliente,
            'itemsEpp'   => self::ITEMS_EPP,
            'estadosEpp' => self::ESTADOS_EPP,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/dotacion-todero/form', $data),
            'title'   => 'Nueva Dot. Todero',
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

        $data['foto_cuerpo_completo'] = $this->uploadFoto('foto_cuerpo_completo', 'uploads/inspecciones/dotacion-todero/');
        $data['foto_cuarto_almacenamiento'] = $this->uploadFoto('foto_cuarto_almacenamiento', 'uploads/inspecciones/dotacion-todero/');

        $this->inspeccionModel->insert($data);
        $idInspeccion = $this->inspeccionModel->getInsertID();

        return redirect()->to('/inspecciones/dotacion-todero/edit/' . $idInspeccion)
            ->with('msg', 'Inspeccion guardada como borrador');
    }

    public function edit($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/dotacion-todero')->with('error', 'Inspeccion no encontrada');
        }
        $data = [
            'title'      => 'Editar Dotacion Todero',
            'inspeccion' => $inspeccion,
            'idCliente'  => $inspeccion['id_cliente'],
            'itemsEpp'   => self::ITEMS_EPP,
            'estadosEpp' => self::ESTADOS_EPP,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/dotacion-todero/form', $data),
            'title'   => 'Editar Dot. Todero',
        ]);
    }

    public function update($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/dotacion-todero')->with('error', 'No se puede editar');
        }

        $data = $this->getInspeccionPostData();

        foreach (['foto_cuerpo_completo', 'foto_cuarto_almacenamiento'] as $campo) {
            $nueva = $this->uploadFoto($campo, 'uploads/inspecciones/dotacion-todero/');
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

        return redirect()->to('/inspecciones/dotacion-todero/edit/' . $id)
            ->with('msg', 'Inspeccion actualizada');
    }

    public function view($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/dotacion-todero')->with('error', 'No encontrada');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $data = [
            'title'      => 'Ver Dotacion Todero',
            'inspeccion' => $inspeccion,
            'cliente'    => $clientModel->find($inspeccion['id_cliente']),
            'consultor'  => $consultantModel->find($inspeccion['id_consultor']),
            'itemsEpp'   => self::ITEMS_EPP,
            'estadosEpp' => self::ESTADOS_EPP,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/dotacion-todero/view', $data),
            'title'   => 'Ver Dot. Todero',
        ]);
    }

    public function finalizar($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/dotacion-todero')->with('error', 'No encontrada');
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

        return redirect()->to('/inspecciones/dotacion-todero/view/' . $id)
            ->with('msg', 'Inspeccion finalizada y PDF generado');
    }

    public function generatePdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/dotacion-todero')->with('error', 'No encontrada');
        }

        $pdfPath = $this->generarPdfInterno($id);
        $fullPath = FCPATH . $pdfPath;
        if (!file_exists($fullPath)) {
            return redirect()->back()->with('error', 'PDF no encontrado');
        }

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="dotacion_todero_' . $id . '.pdf"')
            ->setBody(file_get_contents($fullPath));
    }

    public function delete($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/dotacion-todero')->with('error', 'No encontrada');
        }
        foreach (['foto_cuerpo_completo', 'foto_cuarto_almacenamiento'] as $campo) {
            if (!empty($inspeccion[$campo]) && file_exists(FCPATH . $inspeccion[$campo])) {
                unlink(FCPATH . $inspeccion[$campo]);
            }
        }

        if (!empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) {
            unlink(FCPATH . $inspeccion['ruta_pdf']);
        }

        $this->inspeccionModel->delete($id);

        return redirect()->to('/inspecciones/dotacion-todero')->with('msg', 'Inspeccion eliminada');
    }

    // ===== METODOS PRIVADOS =====

        public function regenerarPdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || ($inspeccion['estado'] ?? '') !== 'completo') {
            return redirect()->to('/inspecciones/dotacion-todero')->with('error', 'Solo se puede regenerar un registro finalizado.');
        }

        $pdfPath = $this->generarPdfInterno($id);

        $this->inspeccionModel->update($id, [
            'ruta_pdf' => $pdfPath,
        ]);

        $inspeccion = $this->inspeccionModel->find($id);
        $this->uploadToReportes($inspeccion, $pdfPath);

        return redirect()->to("/inspecciones/dotacion-todero/view/{$id}")->with('msg', 'PDF regenerado exitosamente.');
    }

    private function getInspeccionPostData(): array
    {
        $data = [
            'id_cliente'             => $this->request->getPost('id_cliente'),
            'fecha_inspeccion'       => $this->request->getPost('fecha_inspeccion'),
            'contratista'            => $this->request->getPost('contratista'),
            'servicio'               => $this->request->getPost('servicio'),
            'nombre_cargo'           => $this->request->getPost('nombre_cargo'),
            'actividades_frecuentes' => $this->request->getPost('actividades_frecuentes'),
            'concepto_final'         => $this->request->getPost('concepto_final'),
            'observaciones'          => $this->request->getPost('observaciones'),
        ];

        foreach (self::ITEMS_EPP as $key => $info) {
            $data['estado_' . $key] = $this->request->getPost('estado_' . $key);
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

        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoMime = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        $fotosBase64 = [];
        foreach (['foto_cuerpo_completo', 'foto_cuarto_almacenamiento'] as $campo) {
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
            'itemsEpp'    => self::ITEMS_EPP,
            'estadosEpp'  => self::ESTADOS_EPP,
            'logoBase64'  => $logoBase64,
            'fotosBase64' => $fotosBase64,
        ];

        $html = view('inspecciones/dotacion-todero/pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $pdfDir = 'uploads/inspecciones/dotacion-todero/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) {
            mkdir(FCPATH . $pdfDir, 0755, true);
        }

        $pdfFileName = 'dotacion_todero_' . $id . '_' . date('Ymd_His') . '.pdf';
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
            ->where('id_detailreport', 25)
            ->like('observaciones', 'dot_tod_id:' . $inspeccion['id'])
            ->first();

        $destDir = ROOTPATH . 'public/uploads/' . $nitCliente;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $fileName = 'dotacion_todero_' . $inspeccion['id'] . '_' . date('Ymd_His') . '.pdf';
        $destPath = $destDir . '/' . $fileName;
        copy(FCPATH . $pdfPath, $destPath);

        $data = [
            'titulo_reporte'  => 'DOTACION TODERO - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $inspeccion['fecha_inspeccion'],
            'id_detailreport' => 25,
            'id_report_type'  => 6,
            'id_cliente'      => $inspeccion['id_cliente'],
            'estado'          => 'Activo',
            'observaciones'   => 'Generado automaticamente desde modulo de inspecciones. dot_tod_id:' . $inspeccion['id'],
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
