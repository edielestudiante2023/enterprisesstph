<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\DotacionVigilanteModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use Dompdf\Dompdf;

class DotacionVigilanteController extends BaseController
{
    protected DotacionVigilanteModel $inspeccionModel;

    public const ITEMS_EPP = [
        'uniforme' => ['label' => 'Uniforme Camisa, Pantalon y Corbata/Panoleta', 'icon' => 'fa-shirt'],
        'chaqueta' => ['label' => 'Chaqueta', 'icon' => 'fa-vest-patches'],
        'radio'    => ['label' => 'Radio de Onda Corta', 'icon' => 'fa-walkie-talkie'],
        'baston'   => ['label' => 'Baston Tonfa', 'icon' => 'fa-gavel'],
        'calzado'  => ['label' => 'Calzado', 'icon' => 'fa-shoe-prints'],
        'gorra'    => ['label' => 'Gorra', 'icon' => 'fa-hat-cowboy'],
        'carne'    => ['label' => 'Carne', 'icon' => 'fa-id-card'],
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
        $this->inspeccionModel = new DotacionVigilanteModel();
    }

    public function list()
    {
        $userId = session()->get('user_id');
        $role = session()->get('role');

        if ($role === 'admin') {
            $inspecciones = $this->inspeccionModel
                ->select('tbl_dotacion_vigilante.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
                ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_dotacion_vigilante.id_cliente', 'left')
                ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_dotacion_vigilante.id_consultor', 'left')
                ->orderBy('tbl_dotacion_vigilante.fecha_inspeccion', 'DESC')
                ->findAll();
        } else {
            $inspecciones = $this->inspeccionModel->getByConsultor($userId);
        }

        $data = [
            'title'        => 'Dotacion Vigilante',
            'inspecciones' => $inspecciones,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/dotacion-vigilante/list', $data),
            'title'   => 'Dotacion Vigilante',
        ]);
    }

    public function create($idCliente = null)
    {
        $data = [
            'title'      => 'Nueva Inspeccion Dotacion Vigilante',
            'inspeccion' => null,
            'idCliente'  => $idCliente,
            'itemsEpp'   => self::ITEMS_EPP,
            'estadosEpp' => self::ESTADOS_EPP,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/dotacion-vigilante/form', $data),
            'title'   => 'Nueva Dot. Vigilante',
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

        $data['foto_cuerpo_completo'] = $this->uploadFoto('foto_cuerpo_completo', 'uploads/inspecciones/dotacion-vigilante/');
        $data['foto_cuarto_almacenamiento'] = $this->uploadFoto('foto_cuarto_almacenamiento', 'uploads/inspecciones/dotacion-vigilante/');

        $this->inspeccionModel->insert($data);
        $idInspeccion = $this->inspeccionModel->getInsertID();

        return redirect()->to('/inspecciones/dotacion-vigilante/edit/' . $idInspeccion)
            ->with('msg', 'Inspeccion guardada como borrador');
    }

    public function edit($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/dotacion-vigilante')->with('error', 'Inspeccion no encontrada');
        }

        if ($inspeccion['estado'] === 'completo') {
            return redirect()->to('/inspecciones/dotacion-vigilante/view/' . $id);
        }

        $data = [
            'title'      => 'Editar Dotacion Vigilante',
            'inspeccion' => $inspeccion,
            'idCliente'  => $inspeccion['id_cliente'],
            'itemsEpp'   => self::ITEMS_EPP,
            'estadosEpp' => self::ESTADOS_EPP,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/dotacion-vigilante/form', $data),
            'title'   => 'Editar Dot. Vigilante',
        ]);
    }

    public function update($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || $inspeccion['estado'] === 'completo') {
            return redirect()->to('/inspecciones/dotacion-vigilante')->with('error', 'No se puede editar');
        }

        $data = $this->getInspeccionPostData();

        foreach (['foto_cuerpo_completo', 'foto_cuarto_almacenamiento'] as $campo) {
            $nueva = $this->uploadFoto($campo, 'uploads/inspecciones/dotacion-vigilante/');
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

        return redirect()->to('/inspecciones/dotacion-vigilante/edit/' . $id)
            ->with('msg', 'Inspeccion actualizada');
    }

    public function view($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/dotacion-vigilante')->with('error', 'No encontrada');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $data = [
            'title'      => 'Ver Dotacion Vigilante',
            'inspeccion' => $inspeccion,
            'cliente'    => $clientModel->find($inspeccion['id_cliente']),
            'consultor'  => $consultantModel->find($inspeccion['id_consultor']),
            'itemsEpp'   => self::ITEMS_EPP,
            'estadosEpp' => self::ESTADOS_EPP,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/dotacion-vigilante/view', $data),
            'title'   => 'Ver Dot. Vigilante',
        ]);
    }

    public function finalizar($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/dotacion-vigilante')->with('error', 'No encontrada');
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

        return redirect()->to('/inspecciones/dotacion-vigilante/view/' . $id)
            ->with('msg', 'Inspeccion finalizada y PDF generado');
    }

    public function generatePdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/dotacion-vigilante')->with('error', 'No encontrada');
        }

        $pdfPath = $this->generarPdfInterno($id);
        $fullPath = FCPATH . $pdfPath;
        if (!file_exists($fullPath)) {
            return redirect()->back()->with('error', 'PDF no encontrado');
        }

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="dotacion_vigilante_' . $id . '.pdf"')
            ->setBody(file_get_contents($fullPath));
    }

    public function delete($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/dotacion-vigilante')->with('error', 'No encontrada');
        }
        if ($inspeccion['estado'] === 'completo') {
            return redirect()->to('/inspecciones/dotacion-vigilante')->with('error', 'No se puede eliminar una inspeccion completa');
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

        return redirect()->to('/inspecciones/dotacion-vigilante')->with('msg', 'Inspeccion eliminada');
    }

    // ===== MÉTODOS PRIVADOS =====

        public function regenerarPdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || ($inspeccion['estado'] ?? '') !== 'completo') {
            return redirect()->to('/inspecciones/dotacion-vigilante')->with('error', 'Solo se puede regenerar un registro finalizado.');
        }

        $pdfPath = $this->generarPdfInterno($id);

        $this->inspeccionModel->update($id, [
            'ruta_pdf' => $pdfPath,
        ]);

        $inspeccion = $this->inspeccionModel->find($id);
        $this->uploadToReportes($inspeccion, $pdfPath);

        return redirect()->to("/inspecciones/dotacion-vigilante/view/{$id}")->with('msg', 'PDF regenerado exitosamente.');
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

        $html = view('inspecciones/dotacion-vigilante/pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $pdfDir = 'uploads/inspecciones/dotacion-vigilante/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) {
            mkdir(FCPATH . $pdfDir, 0755, true);
        }

        $pdfFileName = 'dotacion_vigilante_' . $id . '_' . date('Ymd_His') . '.pdf';
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
            ->where('id_detailreport', 14)
            ->like('observaciones', 'dot_vig_id:' . $inspeccion['id'])
            ->first();

        $destDir = ROOTPATH . 'public/uploads/' . $nitCliente;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $fileName = 'dotacion_vigilante_' . $inspeccion['id'] . '_' . date('Ymd_His') . '.pdf';
        $destPath = $destDir . '/' . $fileName;
        copy(FCPATH . $pdfPath, $destPath);

        $data = [
            'titulo_reporte'  => 'DOTACION VIGILANTE - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $inspeccion['fecha_inspeccion'],
            'id_detailreport' => 14,
            'id_report_type'  => 6,
            'id_cliente'      => $inspeccion['id_cliente'],
            'estado'          => 'Activo',
            'observaciones'   => 'Generado automaticamente desde modulo de inspecciones. dot_vig_id:' . $inspeccion['id'],
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
