<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\InspeccionGabineteModel;
use App\Models\GabineteDetalleModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use Dompdf\Dompdf;

class InspeccionGabineteController extends BaseController
{
    protected InspeccionGabineteModel $inspeccionModel;
    protected GabineteDetalleModel $detalleModel;

    /**
     * Criterios de inspección por gabinete individual (NTC 1800).
     */
    public const CRITERIOS = [
        'tiene_manguera'      => ['label' => 'Manguera',       'type' => 'sino'],
        'tiene_hacha'         => ['label' => 'Hacha',           'type' => 'sino'],
        'tiene_extintor'      => ['label' => 'Extintor',        'type' => 'sino'],
        'tiene_valvula'       => ['label' => 'Valvula',         'type' => 'sino'],
        'tiene_boquilla'      => ['label' => 'Boquilla',        'type' => 'sino'],
        'tiene_llave_spanner' => ['label' => 'Llave spanner',   'type' => 'sino'],
        'estado'              => ['label' => 'Estado general',  'type' => 'estado', 'opciones' => ['BUENO', 'REGULAR', 'MALO']],
        'senalizacion'        => ['label' => 'Senalizacion',    'type' => 'estado', 'opciones' => ['BUENO', 'REGULAR', 'MALO', 'NO TIENE']],
    ];

    public function __construct()
    {
        $this->inspeccionModel = new InspeccionGabineteModel();
        $this->detalleModel = new GabineteDetalleModel();
    }

    public function list()
    {
        $userId = session()->get('user_id');
        $role = session()->get('role');

        if ($role === 'admin') {
            $inspecciones = $this->inspeccionModel
                ->select('tbl_inspeccion_gabinetes.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
                ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_gabinetes.id_cliente', 'left')
                ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_gabinetes.id_consultor', 'left')
                ->orderBy('tbl_inspeccion_gabinetes.fecha_inspeccion', 'DESC')
                ->findAll();
        } else {
            $inspecciones = $this->inspeccionModel->getByConsultor($userId);
        }

        foreach ($inspecciones as &$insp) {
            $insp['total_gabinetes'] = $this->detalleModel->where('id_inspeccion', $insp['id'])->countAllResults(false);
        }

        $data = [
            'title'        => 'Inspeccion de Gabinetes',
            'inspecciones' => $inspecciones,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/gabinetes/list', $data),
            'title'   => 'Gabinetes',
        ]);
    }

    public function create($idCliente = null)
    {
        $data = [
            'title'      => 'Nueva Inspeccion de Gabinetes',
            'inspeccion'  => null,
            'idCliente'  => $idCliente,
            'gabinetes'  => [],
            'criterios'  => self::CRITERIOS,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/gabinetes/form', $data),
            'title'   => 'Nueva Gabinetes',
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

        // Fotos generales
        foreach (['foto_gab_1', 'foto_gab_2', 'foto_det_1', 'foto_det_2'] as $campo) {
            $data[$campo] = $this->uploadFoto($campo, 'uploads/inspecciones/gabinetes/fotos/');
        }

        $this->inspeccionModel->insert($data);
        $idInspeccion = $this->inspeccionModel->getInsertID();

        $this->saveGabinetes($idInspeccion);

        return redirect()->to('/inspecciones/gabinetes/edit/' . $idInspeccion)
            ->with('msg', 'Inspeccion guardada como borrador');
    }

    public function edit($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/gabinetes')->with('error', 'Inspeccion no encontrada');
        }

        if ($inspeccion['estado'] === 'completo') {
            return redirect()->to('/inspecciones/gabinetes/view/' . $id);
        }

        $data = [
            'title'      => 'Editar Inspeccion de Gabinetes',
            'inspeccion'  => $inspeccion,
            'idCliente'  => $inspeccion['id_cliente'],
            'gabinetes'  => $this->detalleModel->getByInspeccion($id),
            'criterios'  => self::CRITERIOS,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/gabinetes/form', $data),
            'title'   => 'Editar Gabinetes',
        ]);
    }

    public function update($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || $inspeccion['estado'] === 'completo') {
            return redirect()->to('/inspecciones/gabinetes')->with('error', 'No se puede editar');
        }

        $data = $this->getInspeccionPostData();

        // Fotos generales (preservar si no se sube nueva)
        foreach (['foto_gab_1', 'foto_gab_2', 'foto_det_1', 'foto_det_2'] as $campo) {
            $nueva = $this->uploadFoto($campo, 'uploads/inspecciones/gabinetes/fotos/');
            if ($nueva) {
                if (!empty($inspeccion[$campo]) && file_exists(FCPATH . $inspeccion[$campo])) {
                    unlink(FCPATH . $inspeccion[$campo]);
                }
                $data[$campo] = $nueva;
            }
        }

        $this->inspeccionModel->update($id, $data);
        $this->saveGabinetes($id);

        if ($this->request->getPost('finalizar')) {
            return $this->finalizar($id);
        }

        return redirect()->to('/inspecciones/gabinetes/edit/' . $id)
            ->with('msg', 'Inspeccion actualizada');
    }

    public function view($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/gabinetes')->with('error', 'No encontrada');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $data = [
            'title'      => 'Ver Inspeccion de Gabinetes',
            'inspeccion'  => $inspeccion,
            'cliente'    => $clientModel->find($inspeccion['id_cliente']),
            'consultor'  => $consultantModel->find($inspeccion['id_consultor']),
            'gabinetes'  => $this->detalleModel->getByInspeccion($id),
            'criterios'  => self::CRITERIOS,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/gabinetes/view', $data),
            'title'   => 'Ver Gabinetes',
        ]);
    }

    public function finalizar($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/gabinetes')->with('error', 'No encontrada');
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

        return redirect()->to('/inspecciones/gabinetes/view/' . $id)
            ->with('msg', 'Inspeccion finalizada y PDF generado');
    }

    public function generatePdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/gabinetes')->with('error', 'No encontrada');
        }

        $pdfPath = $this->generarPdfInterno($id);
        $fullPath = FCPATH . $pdfPath;
        if (!file_exists($fullPath)) {
            return redirect()->back()->with('error', 'PDF no encontrado');
        }

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="gabinetes_' . $id . '.pdf"')
            ->setBody(file_get_contents($fullPath));
    }

    public function delete($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/gabinetes')->with('error', 'No encontrada');
        }
        if ($inspeccion['estado'] === 'completo') {
            return redirect()->to('/inspecciones/gabinetes')->with('error', 'No se puede eliminar una inspeccion completa');
        }

        // Eliminar fotos de gabinetes individuales
        $gabinetes = $this->detalleModel->getByInspeccion($id);
        foreach ($gabinetes as $gab) {
            if (!empty($gab['foto']) && file_exists(FCPATH . $gab['foto'])) {
                unlink(FCPATH . $gab['foto']);
            }
        }

        // Eliminar fotos generales
        foreach (['foto_gab_1', 'foto_gab_2', 'foto_det_1', 'foto_det_2'] as $campo) {
            if (!empty($inspeccion[$campo]) && file_exists(FCPATH . $inspeccion[$campo])) {
                unlink(FCPATH . $inspeccion[$campo]);
            }
        }

        if (!empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) {
            unlink(FCPATH . $inspeccion['ruta_pdf']);
        }

        $this->inspeccionModel->delete($id);

        return redirect()->to('/inspecciones/gabinetes')->with('msg', 'Inspeccion eliminada');
    }

    // ===== MÉTODOS PRIVADOS =====

        public function regenerarPdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || ($inspeccion['estado'] ?? '') !== 'completo') {
            return redirect()->to('/inspecciones/gabinetes')->with('error', 'Solo se puede regenerar un registro finalizado.');
        }

        $pdfPath = $this->generarPdfInterno($id);

        $this->inspeccionModel->update($id, [
            'ruta_pdf' => $pdfPath,
        ]);

        $inspeccion = $this->inspeccionModel->find($id);
        $this->uploadToReportes($inspeccion, $pdfPath);

        return redirect()->to("/inspecciones/gabinetes/view/{$id}")->with('msg', 'PDF regenerado exitosamente.');
    }

    private function getInspeccionPostData(): array
    {
        return [
            'id_cliente'              => $this->request->getPost('id_cliente'),
            'fecha_inspeccion'        => $this->request->getPost('fecha_inspeccion'),
            'tiene_gabinetes'         => $this->request->getPost('tiene_gabinetes') ?: 'SI',
            'entregados_constructora' => $this->request->getPost('entregados_constructora') ?: 'SI',
            'cantidad_gabinetes'      => (int)$this->request->getPost('cantidad_gabinetes'),
            'elementos_gabinete'      => $this->request->getPost('elementos_gabinete'),
            'ubicacion_gabinetes'     => $this->request->getPost('ubicacion_gabinetes'),
            'estado_senalizacion_gab' => $this->request->getPost('estado_senalizacion_gab'),
            'observaciones_gabinetes' => $this->request->getPost('observaciones_gabinetes'),
            'tiene_detectores'        => $this->request->getPost('tiene_detectores') ?: 'SI',
            'detectores_entregados'   => $this->request->getPost('detectores_entregados') ?: 'SI',
            'cantidad_detectores'     => (int)$this->request->getPost('cantidad_detectores'),
            'ubicacion_detectores'    => $this->request->getPost('ubicacion_detectores'),
            'observaciones_detectores' => $this->request->getPost('observaciones_detectores'),
        ];
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

    private function saveGabinetes(int $idInspeccion): void
    {
        $ubicaciones = $this->request->getPost('gab_ubicacion') ?? [];
        $gabIds = $this->request->getPost('gab_id') ?? [];

        // Obtener existentes para preservar fotos
        $existentes = [];
        foreach ($this->detalleModel->getByInspeccion($idInspeccion) as $gab) {
            $existentes[$gab['id']] = $gab;
        }

        $this->detalleModel->deleteByInspeccion($idInspeccion);

        $dir = FCPATH . 'uploads/inspecciones/gabinetes/fotos/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $files = $this->request->getFiles();

        foreach ($ubicaciones as $i => $ubicacion) {
            $existenteId = $gabIds[$i] ?? null;
            $existente = $existenteId ? ($existentes[$existenteId] ?? null) : null;

            // Foto
            $fotoPath = $existente['foto'] ?? null;
            if (isset($files['gab_foto'][$i]) && $files['gab_foto'][$i]->isValid() && !$files['gab_foto'][$i]->hasMoved()) {
                $file = $files['gab_foto'][$i];
                $fileName = $file->getRandomName();
                $file->move($dir, $fileName);
                $fotoPath = 'uploads/inspecciones/gabinetes/fotos/' . $fileName;
            }

            $this->detalleModel->insert([
                'id_inspeccion'       => $idInspeccion,
                'numero'              => $i + 1,
                'ubicacion'           => $ubicacion,
                'tiene_manguera'      => ($this->request->getPost('gab_tiene_manguera') ?? [])[$i] ?? 'SI',
                'tiene_hacha'         => ($this->request->getPost('gab_tiene_hacha') ?? [])[$i] ?? 'SI',
                'tiene_extintor'      => ($this->request->getPost('gab_tiene_extintor') ?? [])[$i] ?? 'NO',
                'tiene_valvula'       => ($this->request->getPost('gab_tiene_valvula') ?? [])[$i] ?? 'SI',
                'tiene_boquilla'      => ($this->request->getPost('gab_tiene_boquilla') ?? [])[$i] ?? 'SI',
                'tiene_llave_spanner' => ($this->request->getPost('gab_tiene_llave_spanner') ?? [])[$i] ?? 'NO',
                'estado'              => ($this->request->getPost('gab_estado') ?? [])[$i] ?? 'BUENO',
                'senalizacion'        => ($this->request->getPost('gab_senalizacion') ?? [])[$i] ?? 'BUENO',
                'observaciones'       => ($this->request->getPost('gab_observaciones') ?? [])[$i] ?? null,
                'foto'                => $fotoPath,
            ]);
        }
    }

    private function generarPdfInterno(int $id): ?string
    {
        $inspeccion = $this->inspeccionModel->find($id);
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $cliente = $clientModel->find($inspeccion['id_cliente']);
        $consultor = $consultantModel->find($inspeccion['id_consultor']);
        $gabinetes = $this->detalleModel->getByInspeccion($id);

        // Logo en base64
        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoMime = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        // Fotos generales a base64
        $fotosBase64 = [];
        foreach (['foto_gab_1', 'foto_gab_2', 'foto_det_1', 'foto_det_2'] as $campo) {
            $fotosBase64[$campo] = '';
            if (!empty($inspeccion[$campo])) {
                $fotoPath = FCPATH . $inspeccion[$campo];
                if (file_exists($fotoPath)) {
                    $mime = mime_content_type($fotoPath);
                    $fotosBase64[$campo] = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fotoPath));
                }
            }
        }

        // Fotos de gabinetes individuales a base64
        foreach ($gabinetes as &$gab) {
            $gab['foto_base64'] = '';
            if (!empty($gab['foto'])) {
                $fotoPath = FCPATH . $gab['foto'];
                if (file_exists($fotoPath)) {
                    $mime = mime_content_type($fotoPath);
                    $gab['foto_base64'] = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fotoPath));
                }
            }
        }

        $data = [
            'inspeccion'   => $inspeccion,
            'cliente'      => $cliente,
            'consultor'    => $consultor,
            'gabinetes'    => $gabinetes,
            'criterios'    => self::CRITERIOS,
            'logoBase64'   => $logoBase64,
            'fotosBase64'  => $fotosBase64,
        ];

        $html = view('inspecciones/gabinetes/pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $pdfDir = 'uploads/inspecciones/gabinetes/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) {
            mkdir(FCPATH . $pdfDir, 0755, true);
        }

        $pdfFileName = 'gabinetes_' . $id . '_' . date('Ymd_His') . '.pdf';
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
            ->like('observaciones', 'insp_gab_id:' . $inspeccion['id'])
            ->first();

        $destDir = ROOTPATH . 'public/uploads/' . $nitCliente;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $fileName = 'gabinetes_' . $inspeccion['id'] . '_' . date('Ymd_His') . '.pdf';
        $destPath = $destDir . '/' . $fileName;
        copy(FCPATH . $pdfPath, $destPath);

        $data = [
            'titulo_reporte'  => 'INSPECCION GABINETES - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $inspeccion['fecha_inspeccion'],
            'id_detailreport' => 14,
            'id_report_type'  => 6,
            'id_cliente'      => $inspeccion['id_cliente'],
            'estado'          => 'Activo',
            'observaciones'   => 'Generado automaticamente desde modulo de inspecciones. insp_gab_id:' . $inspeccion['id'],
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
