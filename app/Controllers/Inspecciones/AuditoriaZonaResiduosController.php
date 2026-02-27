<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\AuditoriaZonaResiduosModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use Dompdf\Dompdf;

class AuditoriaZonaResiduosController extends BaseController
{
    protected AuditoriaZonaResiduosModel $inspeccionModel;

    public const ITEMS_ZONA = [
        'acceso'                => ['label' => 'Acceso', 'icon' => 'fa-door-open', 'tipo' => 'enum'],
        'techo_pared_pisos'     => ['label' => 'Techo, Pared y Pisos', 'icon' => 'fa-building', 'tipo' => 'enum'],
        'ventilacion'           => ['label' => 'Ventilacion', 'icon' => 'fa-wind', 'tipo' => 'enum'],
        'prevencion_incendios'  => ['label' => 'Prevencion y Control de Incendios', 'icon' => 'fa-fire-extinguisher', 'tipo' => 'enum'],
        'drenajes'              => ['label' => 'Drenajes', 'icon' => 'fa-water', 'tipo' => 'enum'],
        'proliferacion_plagas'  => ['label' => 'Proliferacion de Plagas', 'icon' => 'fa-bug', 'tipo' => 'texto_libre'],
        'recipientes'           => ['label' => 'Recipientes', 'icon' => 'fa-trash-can', 'tipo' => 'enum'],
        'reciclaje'             => ['label' => 'Reciclaje', 'icon' => 'fa-recycle', 'tipo' => 'enum'],
        'iluminarias'           => ['label' => 'Iluminarias', 'icon' => 'fa-lightbulb', 'tipo' => 'enum'],
        'senalizacion'          => ['label' => 'Senalizacion', 'icon' => 'fa-triangle-exclamation', 'tipo' => 'enum'],
        'limpieza_desinfeccion' => ['label' => 'Limpieza y Desinfeccion', 'icon' => 'fa-spray-can', 'tipo' => 'enum'],
        'poseta'                => ['label' => 'Poceta', 'icon' => 'fa-faucet-drip', 'tipo' => 'enum'],
    ];

    public const ESTADOS_ZONA = [
        'bueno'      => 'Bueno',
        'regular'    => 'Regular',
        'malo'       => 'Malo',
        'deficiente' => 'Deficiente',
        'no_tiene'   => 'No Tiene',
        'no_aplica'  => 'No Aplica',
    ];

    public function __construct()
    {
        $this->inspeccionModel = new AuditoriaZonaResiduosModel();
    }

    public function list()
    {
        $userId = session()->get('user_id');
        $role = session()->get('role');

        if ($role === 'admin') {
            $inspecciones = $this->inspeccionModel
                ->select('tbl_auditoria_zona_residuos.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
                ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_auditoria_zona_residuos.id_cliente', 'left')
                ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_auditoria_zona_residuos.id_consultor', 'left')
                ->orderBy('tbl_auditoria_zona_residuos.fecha_inspeccion', 'DESC')
                ->findAll();
        } else {
            $inspecciones = $this->inspeccionModel->getByConsultor($userId);
        }

        $data = [
            'title'        => 'Auditoria Zona Residuos',
            'inspecciones' => $inspecciones,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/auditoria-zona-residuos/list', $data),
            'title'   => 'Auditoria Zona Residuos',
        ]);
    }

    public function create($idCliente = null)
    {
        $data = [
            'title'       => 'Nueva Auditoria Zona Residuos',
            'inspeccion'  => null,
            'idCliente'   => $idCliente,
            'itemsZona'   => self::ITEMS_ZONA,
            'estadosZona' => self::ESTADOS_ZONA,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/auditoria-zona-residuos/form', $data),
            'title'   => 'Nueva Aud. Zona Residuos',
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

        // Upload foto for each item
        foreach (self::ITEMS_ZONA as $key => $info) {
            $data['foto_' . $key] = $this->uploadFoto('foto_' . $key, 'uploads/inspecciones/zona-residuos/');
        }

        $this->inspeccionModel->insert($data);
        $idInspeccion = $this->inspeccionModel->getInsertID();

        return redirect()->to('/inspecciones/auditoria-zona-residuos/edit/' . $idInspeccion)
            ->with('msg', 'Inspeccion guardada como borrador');
    }

    public function edit($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/auditoria-zona-residuos')->with('error', 'Inspeccion no encontrada');
        }

        if ($inspeccion['estado'] === 'completo') {
            return redirect()->to('/inspecciones/auditoria-zona-residuos/view/' . $id);
        }

        $data = [
            'title'       => 'Editar Auditoria Zona Residuos',
            'inspeccion'  => $inspeccion,
            'idCliente'   => $inspeccion['id_cliente'],
            'itemsZona'   => self::ITEMS_ZONA,
            'estadosZona' => self::ESTADOS_ZONA,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/auditoria-zona-residuos/form', $data),
            'title'   => 'Editar Aud. Zona Residuos',
        ]);
    }

    public function update($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || $inspeccion['estado'] === 'completo') {
            return redirect()->to('/inspecciones/auditoria-zona-residuos')->with('error', 'No se puede editar');
        }

        $data = $this->getInspeccionPostData();

        // Upload foto for each item
        foreach (self::ITEMS_ZONA as $key => $info) {
            $campo = 'foto_' . $key;
            $nueva = $this->uploadFoto($campo, 'uploads/inspecciones/zona-residuos/');
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

        return redirect()->to('/inspecciones/auditoria-zona-residuos/edit/' . $id)
            ->with('msg', 'Inspeccion actualizada');
    }

    public function view($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/auditoria-zona-residuos')->with('error', 'No encontrada');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $data = [
            'title'       => 'Ver Auditoria Zona Residuos',
            'inspeccion'  => $inspeccion,
            'cliente'     => $clientModel->find($inspeccion['id_cliente']),
            'consultor'   => $consultantModel->find($inspeccion['id_consultor']),
            'itemsZona'   => self::ITEMS_ZONA,
            'estadosZona' => self::ESTADOS_ZONA,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/auditoria-zona-residuos/view', $data),
            'title'   => 'Ver Aud. Zona Residuos',
        ]);
    }

    public function finalizar($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/auditoria-zona-residuos')->with('error', 'No encontrada');
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

        return redirect()->to('/inspecciones/auditoria-zona-residuos/view/' . $id)
            ->with('msg', 'Inspeccion finalizada y PDF generado');
    }

    public function generatePdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/auditoria-zona-residuos')->with('error', 'No encontrada');
        }

        $pdfPath = $this->generarPdfInterno($id);
        $fullPath = FCPATH . $pdfPath;
        if (!file_exists($fullPath)) {
            return redirect()->back()->with('error', 'PDF no encontrado');
        }

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="auditoria_zona_residuos_' . $id . '.pdf"')
            ->setBody(file_get_contents($fullPath));
    }

    public function delete($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/auditoria-zona-residuos')->with('error', 'No encontrada');
        }
        if ($inspeccion['estado'] === 'completo') {
            return redirect()->to('/inspecciones/auditoria-zona-residuos')->with('error', 'No se puede eliminar una inspeccion completa');
        }

        // Delete all 12 item photos
        foreach (self::ITEMS_ZONA as $key => $info) {
            $campo = 'foto_' . $key;
            if (!empty($inspeccion[$campo]) && file_exists(FCPATH . $inspeccion[$campo])) {
                unlink(FCPATH . $inspeccion[$campo]);
            }
        }

        if (!empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) {
            unlink(FCPATH . $inspeccion['ruta_pdf']);
        }

        $this->inspeccionModel->delete($id);

        return redirect()->to('/inspecciones/auditoria-zona-residuos')->with('msg', 'Inspeccion eliminada');
    }

    // ===== METODOS PRIVADOS =====

        public function regenerarPdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || ($inspeccion['estado'] ?? '') !== 'completo') {
            return redirect()->to('/inspecciones/auditoria-zona-residuos')->with('error', 'Solo se puede regenerar un registro finalizado.');
        }

        $pdfPath = $this->generarPdfInterno($id);

        $this->inspeccionModel->update($id, [
            'ruta_pdf' => $pdfPath,
        ]);

        $inspeccion = $this->inspeccionModel->find($id);
        $this->uploadToReportes($inspeccion, $pdfPath);

        return redirect()->to("/inspecciones/auditoria-zona-residuos/view/{$id}")->with('msg', 'PDF regenerado exitosamente.');
    }

    private function getInspeccionPostData(): array
    {
        $data = [
            'id_cliente'       => $this->request->getPost('id_cliente'),
            'fecha_inspeccion' => $this->request->getPost('fecha_inspeccion'),
            'observaciones'    => $this->request->getPost('observaciones'),
        ];

        foreach (self::ITEMS_ZONA as $key => $info) {
            if ($info['tipo'] === 'enum') {
                $data['estado_' . $key] = $this->request->getPost('estado_' . $key);
            } else {
                // texto_libre: field name is the key itself (e.g. proliferacion_plagas)
                $data[$key] = $this->request->getPost($key);
            }
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

        // Convert all 12 item photos to base64
        $fotosBase64 = [];
        foreach (self::ITEMS_ZONA as $key => $info) {
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

        $data = [
            'inspeccion'  => $inspeccion,
            'cliente'     => $cliente,
            'consultor'   => $consultor,
            'itemsZona'   => self::ITEMS_ZONA,
            'estadosZona' => self::ESTADOS_ZONA,
            'logoBase64'  => $logoBase64,
            'fotosBase64' => $fotosBase64,
        ];

        $html = view('inspecciones/auditoria-zona-residuos/pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $pdfDir = 'uploads/inspecciones/zona-residuos/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) {
            mkdir(FCPATH . $pdfDir, 0755, true);
        }

        $pdfFileName = 'auditoria_zona_residuos_' . $id . '_' . date('Ymd_His') . '.pdf';
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
            ->where('id_detailreport', 27)
            ->like('observaciones', 'aud_res_id:' . $inspeccion['id'])
            ->first();

        $destDir = ROOTPATH . 'public/uploads/' . $nitCliente;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $fileName = 'auditoria_zona_residuos_' . $inspeccion['id'] . '_' . date('Ymd_His') . '.pdf';
        $destPath = $destDir . '/' . $fileName;
        copy(FCPATH . $pdfPath, $destPath);

        $data = [
            'titulo_reporte'  => 'AUDITORIA ZONA RESIDUOS - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $inspeccion['fecha_inspeccion'],
            'id_detailreport' => 27,
            'id_report_type'  => 6,
            'id_cliente'      => $inspeccion['id_cliente'],
            'estado'          => 'Activo',
            'observaciones'   => 'Generado automaticamente desde modulo de inspecciones. aud_res_id:' . $inspeccion['id'],
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
