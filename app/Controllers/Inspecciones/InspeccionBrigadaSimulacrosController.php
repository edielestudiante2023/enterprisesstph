<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\InspeccionBrigadaSimulacrosModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use Dompdf\Dompdf;

class InspeccionBrigadaSimulacrosController extends BaseController
{
    use \App\Traits\AutosaveJsonTrait;
    use \App\Traits\InspeccionesTransactionalTrait;

    protected InspeccionBrigadaSimulacrosModel $inspeccionModel;

    const FOTO_FIELDS = [
        'foto_brigada_1'      => 'Foto Brigada 1',
        'foto_brigada_2'      => 'Foto Brigada 2',
        'foto_dotacion'       => 'Foto Dotacion',
        'foto_acta_simulacro' => 'Foto Acta Simulacro',
    ];

    const UPLOAD_DIR = 'uploads/inspecciones/brigada/';

    public function __construct()
    {
        $this->inspeccionModel = new InspeccionBrigadaSimulacrosModel();
    }

    public function list()
    {
        $inspecciones = $this->inspeccionModel
            ->select('tbl_inspeccion_brigada_simulacros.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_brigada_simulacros.id_cliente', 'left')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_brigada_simulacros.id_consultor', 'left')
            ->orderBy('tbl_inspeccion_brigada_simulacros.fecha_inspeccion', 'DESC')
            ->findAll();

        $data = [
            'title'        => 'Inspeccion de Brigada y Simulacros',
            'inspecciones' => $inspecciones,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/brigada-simulacros/list', $data),
            'title'   => 'Brigada y Simulacros',
        ]);
    }

    public function create($idCliente = null)
    {
        $data = [
            'title'      => 'Nueva Inspeccion de Brigada y Simulacros',
            'inspeccion' => null,
            'idCliente'  => $idCliente,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/brigada-simulacros/form', $data),
            'title'   => 'Nueva Brigada y Simulacros',
        ]);
    }

    public function store()
    {
        $userId = session()->get('user_id');

        if (!$this->validate([
            'id_cliente'       => 'required|integer',
            'fecha_inspeccion' => 'required|valid_date',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $inspeccionData = $this->getInspeccionPostData($userId);
        $inspeccionData['estado'] = 'borrador';

        foreach (array_keys(self::FOTO_FIELDS) as $campo) {
            $ruta = $this->uploadFoto($campo);
            if ($ruta !== null) {
                $inspeccionData[$campo] = $ruta;
            }
        }

        $this->inspeccionModel->insert($inspeccionData);
        $idInspeccion = $this->inspeccionModel->getInsertID();

        return redirect()->to('/inspecciones/brigada-simulacros/edit/' . $idInspeccion)
            ->with('msg', 'Inspeccion guardada como borrador');
    }

    public function edit($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/brigada-simulacros')
                ->with('error', 'Inspeccion no encontrada');
        }

        $data = [
            'title'      => 'Editar Inspeccion de Brigada y Simulacros',
            'inspeccion' => $inspeccion,
            'idCliente'  => $inspeccion['id_cliente'],
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/brigada-simulacros/form', $data),
            'title'   => 'Editar Brigada y Simulacros',
        ]);
    }

    public function update($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/brigada-simulacros')
                ->with('error', 'No se puede editar');
        }

        $userId     = session()->get('user_id');
        $updateData = $this->getInspeccionPostData($userId);

        foreach (array_keys(self::FOTO_FIELDS) as $campo) {
            $nuevaFoto = $this->uploadFoto($campo);
            if ($nuevaFoto !== null) {
                if (!empty($inspeccion[$campo]) && file_exists(FCPATH . $inspeccion[$campo])) {
                    @unlink(FCPATH . $inspeccion[$campo]);
                }
                $updateData[$campo] = $nuevaFoto;
            }
        }

        $this->inspeccionModel->update($id, $updateData);

        if ($this->request->getPost('finalizar')) {
            return $this->finalizar($id);
        }

        return redirect()->to('/inspecciones/brigada-simulacros/edit/' . $id)
            ->with('msg', 'Inspeccion actualizada');
    }

    public function view($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/brigada-simulacros')
                ->with('error', 'No encontrada');
        }

        $clientModel     = new ClientModel();
        $consultantModel = new ConsultantModel();

        $data = [
            'title'      => 'Ver Inspeccion de Brigada y Simulacros',
            'inspeccion' => $inspeccion,
            'cliente'    => $clientModel->find($inspeccion['id_cliente']),
            'consultor'  => $consultantModel->find($inspeccion['id_consultor']),
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/brigada-simulacros/view', $data),
            'title'   => 'Ver Brigada y Simulacros',
        ]);
    }

    public function finalizar($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/brigada-simulacros')
                ->with('error', 'No encontrada');
        }

        if ($r = $this->guardFinalizado($inspeccion, '/inspecciones/brigada-simulacros/view/' . $id)) return $r;

        $pdfPath = $this->generarPdfInterno($id);
        if (!$pdfPath) {
            return redirect()->back()->with('error', 'Error al generar PDF');
        }

        $this->inspeccionModel->update($id, [
            'estado'   => 'completo',
            'ruta_pdf' => $pdfPath,
        ]);

        // Carga automatica al listado de reportes del cliente
        $inspeccionActualizada = $this->inspeccionModel->find($id);
        $this->uploadToReportes($inspeccionActualizada, $pdfPath);

        return redirect()->to('/inspecciones/brigada-simulacros/view/' . $id)
            ->with('msg', 'Inspeccion finalizada, PDF generado y publicada en reportes del cliente.');
    }

    public function generatePdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/brigada-simulacros')
                ->with('error', 'No encontrada');
        }

        $pdfPath = $this->generarPdfInterno($id);
        $this->inspeccionModel->update($id, ['ruta_pdf' => $pdfPath]);

        // Si la inspeccion ya estaba finalizada, actualizar tambien el reporte del cliente
        if (($inspeccion['estado'] ?? '') === 'completo') {
            $inspeccionActualizada = $this->inspeccionModel->find($id);
            $this->uploadToReportes($inspeccionActualizada, $pdfPath);
        }

        $fullPath = FCPATH . $pdfPath;
        if (!file_exists($fullPath)) {
            return redirect()->back()->with('error', 'PDF no encontrado');
        }

        $this->servirPdf($fullPath, 'brigada_simulacros_' . $id . '.pdf');
        return;
    }

    /**
     * Sube/actualiza el PDF de la inspeccion al listado central de reportes del cliente
     * (tbl_reporte). Usa id_report_type=6, id_detailreport=48 (Brigada y Simulacros).
     * Es idempotente: si existe un reporte previo para esta inspeccion, lo actualiza.
     */
    private function uploadToReportes(array $inspeccion, string $pdfPath): bool
    {
        $reporteModel = new ReporteModel();
        $clientModel  = new ClientModel();
        $cliente = $clientModel->find($inspeccion['id_cliente']);
        if (!$cliente) return false;

        $nitCliente = $cliente['nit_cliente'] ?? '';

        $existente = $reporteModel
            ->where('id_cliente', $inspeccion['id_cliente'])
            ->where('id_report_type', 6)
            ->where('id_detailreport', 48)
            ->like('observaciones', 'insp_brig_id:' . $inspeccion['id'])
            ->first();

        // Copia del PDF al directorio publico de reportes del cliente (si aplica)
        if (defined('UPLOADS_PATH') && !empty($nitCliente)) {
            $destDir = UPLOADS_PATH . $nitCliente;
            if (!is_dir($destDir)) {
                @mkdir($destDir, 0755, true);
            }
            $fileName = 'brigada_simulacros_' . $inspeccion['id'] . '_' . date('Ymd_His') . '.pdf';
            $destPath = $destDir . '/' . $fileName;
            @copy(FCPATH . $pdfPath, $destPath);
            $enlace = base_url((defined('UPLOADS_URL_PREFIX') ? UPLOADS_URL_PREFIX : 'uploads') . '/' . $nitCliente . '/' . $fileName);
        } else {
            $enlace = base_url($pdfPath);
        }

        $data = [
            'titulo_reporte'  => 'INSPECCION BRIGADA Y SIMULACROS - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . ($inspeccion['fecha_inspeccion'] ?? date('Y-m-d')),
            'id_detailreport' => 48,
            'id_report_type'  => 6,
            'id_cliente'      => $inspeccion['id_cliente'],
            'estado'          => 'CERRADO',
            'observaciones'   => 'Generado automaticamente desde modulo de inspecciones. insp_brig_id:' . $inspeccion['id'],
            'enlace'          => $enlace,
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        if ($existente) {
            return (bool) $reporteModel->update($existente['id_reporte'], $data);
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        return (bool) $reporteModel->save($data);
    }

    public function delete($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/brigada-simulacros')
                ->with('error', 'No encontrada');
        }

        foreach (array_keys(self::FOTO_FIELDS) as $campo) {
            if (!empty($inspeccion[$campo]) && file_exists(FCPATH . $inspeccion[$campo])) {
                @unlink(FCPATH . $inspeccion[$campo]);
            }
        }

        if (!empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) {
            @unlink(FCPATH . $inspeccion['ruta_pdf']);
        }

        $this->inspeccionModel->delete($id);

        return redirect()->to('/inspecciones/brigada-simulacros')
            ->with('msg', 'Inspeccion eliminada');
    }

    // ===== METODOS PRIVADOS =====

    private function getInspeccionPostData(int $userId): array
    {
        $req = $this->request;
        return [
            'id_cliente'                     => $req->getPost('id_cliente'),
            'id_consultor'                   => $userId,
            'fecha_inspeccion'               => $req->getPost('fecha_inspeccion'),
            // Estado actual
            'existe_brigada'                 => $req->getPost('existe_brigada') ?: 'no',
            'fecha_conformacion'             => $req->getPost('fecha_conformacion') ?: null,
            'numero_brigadistas'             => (int) ($req->getPost('numero_brigadistas') ?: 0),
            'nombre_jefe_brigada'            => $req->getPost('nombre_jefe_brigada'),
            'brigada_capacitada'             => $req->getPost('brigada_capacitada') ?: 'no',
            'cuenta_dotacion'                => $req->getPost('cuenta_dotacion') ?: 'no',
            'detalle_dotacion'               => $req->getPost('detalle_dotacion'),
            // Capacitaciones
            'capacitacion_primeros_auxilios' => $req->getPost('capacitacion_primeros_auxilios') ?: 'no',
            'capacitacion_extintores'        => $req->getPost('capacitacion_extintores') ?: 'no',
            'capacitacion_evacuacion'        => $req->getPost('capacitacion_evacuacion') ?: 'no',
            'capacitacion_busqueda_rescate'  => $req->getPost('capacitacion_busqueda_rescate') ?: 'no',
            'capacitacion_comunicaciones'    => $req->getPost('capacitacion_comunicaciones') ?: 'no',
            'fecha_ultima_capacitacion'      => $req->getPost('fecha_ultima_capacitacion') ?: null,
            'capacitaciones_12m'             => $req->getPost('capacitaciones_12m'),
            // Simulacros
            'fecha_ultimo_simulacro'         => $req->getPost('fecha_ultimo_simulacro') ?: null,
            'tipo_simulacro'                 => $req->getPost('tipo_simulacro') ?: 'no_realizado',
            'participo_simulacro_nacional'   => $req->getPost('participo_simulacro_nacional') ?: 'no',
            'cantidad_simulacros_12m'        => (int) ($req->getPost('cantidad_simulacros_12m') ?: 0),
            // Hallazgos
            'fortalezas'                     => $req->getPost('fortalezas'),
            'debilidades'                    => $req->getPost('debilidades'),
            'recomendaciones'                => $req->getPost('recomendaciones'),
            'observaciones'                  => $req->getPost('observaciones'),
        ];
    }

    private function uploadFoto(string $fieldName): ?string
    {
        $file = $this->request->getFile($fieldName);
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return null;
        }

        $dir = self::UPLOAD_DIR;
        if (!is_dir(FCPATH . $dir)) {
            mkdir(FCPATH . $dir, 0755, true);
        }

        $fileName = $file->getRandomName();
        $file->move(FCPATH . $dir, $fileName);
        return $dir . $fileName;
    }

    private function fotoABase64ParaPdf(string $path): string
    {
        $mime = function_exists('mime_content_type') ? mime_content_type($path) : 'image/jpeg';
        $data = @file_get_contents($path);
        if ($data === false) {
            return '';
        }
        return 'data:' . $mime . ';base64,' . base64_encode($data);
    }

    private function generarPdfInterno(int $id): ?string
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return null;
        }

        $clientModel     = new ClientModel();
        $consultantModel = new ConsultantModel();
        $cliente         = $clientModel->find($inspeccion['id_cliente']);
        $consultor       = $consultantModel->find($inspeccion['id_consultor']);

        // Logo
        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoBase64 = $this->fotoABase64ParaPdf($logoPath);
            }
        }

        // Fotos a base64
        $fotosBase64 = [];
        foreach (array_keys(self::FOTO_FIELDS) as $campo) {
            $fotosBase64[$campo] = '';
            if (!empty($inspeccion[$campo])) {
                $fotoPath = FCPATH . $inspeccion[$campo];
                if (file_exists($fotoPath)) {
                    $fotosBase64[$campo] = $this->fotoABase64ParaPdf($fotoPath);
                }
            }
        }

        $data = [
            'inspeccion'  => $inspeccion,
            'cliente'     => $cliente,
            'consultor'   => $consultor,
            'logoBase64'  => $logoBase64,
            'fotosBase64' => $fotosBase64,
            'fotoLabels'  => self::FOTO_FIELDS,
        ];

        $html = view('inspecciones/brigada-simulacros/pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $pdfDir = 'uploads/inspecciones/brigada/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) {
            mkdir(FCPATH . $pdfDir, 0755, true);
        }

        $pdfFileName = 'brigada_simulacros_' . $id . '_' . date('Ymd_His') . '.pdf';
        $pdfPath     = $pdfDir . $pdfFileName;

        if (!empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) {
            @unlink(FCPATH . $inspeccion['ruta_pdf']);
        }

        file_put_contents(FCPATH . $pdfPath, $dompdf->output());

        return $pdfPath;
    }

    private function servirPdf(string $fullPath, string $nombre): void
    {
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $nombre . '"');
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
        exit;
    }
}
