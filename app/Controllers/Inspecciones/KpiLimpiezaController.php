<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\KpiLimpiezaModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use Dompdf\Dompdf;

class KpiLimpiezaController extends BaseController
{
    protected KpiLimpiezaModel $model;

    protected const INDICADORES = [
        'Cumplimiento de actividades de limpieza y desinfección',
        'Cobertura de desinfección en áreas críticas',
    ];

    protected const PDF_CODE     = 'FT-SST-229';
    protected const PDF_TITLE    = 'KPI PROGRAMA DE LIMPIEZA Y DESINFECCIÓN';
    protected const PDF_INTRO    = 'Con el fin de verificar la ejecución efectiva del <strong>Programa de Limpieza y Desinfección</strong> y garantizar su mejora continua en';
    protected const ROUTE_SLUG   = 'kpi-limpieza';
    protected const FOTO_DIR     = 'uploads/inspecciones/kpi-limpieza/fotos/';
    protected const PDF_DIR      = 'uploads/inspecciones/kpi-limpieza/pdfs/';
    protected const DETAIL_ID    = 33;
    protected const TAG_PREFIX   = 'kpi_limp_id';
    protected const MODULE_LABEL = 'KPI Limpieza';

    public function __construct()
    {
        $this->model = new KpiLimpiezaModel();
    }

    public function list()
    {
        $role   = session()->get('role');
        $userId = session()->get('user_id');

        if ($role === 'admin') {
            $inspecciones = $this->model
                ->select('tbl_kpi_limpieza.*, tbl_clientes.nombre_cliente')
                ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_kpi_limpieza.id_cliente')
                ->orderBy('tbl_kpi_limpieza.fecha_inspeccion', 'DESC')
                ->findAll();
        } else {
            $inspecciones = $this->model->getByConsultor($userId);
        }

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/kpi-limpieza/list', [
                'title'        => static::MODULE_LABEL,
                'inspecciones' => $inspecciones,
                'slug'         => static::ROUTE_SLUG,
            ]),
            'title' => static::MODULE_LABEL,
        ]);
    }

    public function create($idCliente = null)
    {
        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/kpi-limpieza/form', [
                'title'       => 'Nuevo ' . static::MODULE_LABEL,
                'inspeccion'  => null,
                'idCliente'   => $idCliente,
                'indicadores' => static::INDICADORES,
                'slug'        => static::ROUTE_SLUG,
            ]),
            'title' => 'Nuevo ' . static::MODULE_LABEL,
        ]);
    }

    public function store()
    {
        $userId = session()->get('user_id');

        if (!$this->validate(['id_cliente' => 'required|integer', 'fecha_inspeccion' => 'required|valid_date'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id_cliente'          => $this->request->getPost('id_cliente'),
            'id_consultor'        => $userId,
            'fecha_inspeccion'    => $this->request->getPost('fecha_inspeccion'),
            'nombre_responsable'  => $this->request->getPost('nombre_responsable'),
            'indicador'           => $this->request->getPost('indicador'),
            'cumplimiento'        => (float)($this->request->getPost('cumplimiento') ?? 0),
            'estado'              => 'borrador',
        ];

        // Upload fotos
        for ($i = 1; $i <= 4; $i++) {
            $data["registro_formato_$i"] = $this->uploadFoto("registro_formato_$i", static::FOTO_DIR);
        }

        $this->model->insert($data);
        $id = $this->model->getInsertID();

        return redirect()->to('/inspecciones/' . static::ROUTE_SLUG . '/edit/' . $id)
            ->with('msg', 'KPI guardado como borrador');
    }

    public function edit($id)
    {
        $inspeccion = $this->model->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/' . static::ROUTE_SLUG)->with('error', 'No encontrado');
        }
        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/kpi-limpieza/form', [
                'title'       => 'Editar ' . static::MODULE_LABEL,
                'inspeccion'  => $inspeccion,
                'idCliente'   => $inspeccion['id_cliente'],
                'indicadores' => static::INDICADORES,
                'slug'        => static::ROUTE_SLUG,
            ]),
            'title' => 'Editar ' . static::MODULE_LABEL,
        ]);
    }

    public function update($id)
    {
        $inspeccion = $this->model->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/' . static::ROUTE_SLUG)->with('error', 'No se puede editar');
        }

        $updateData = [
            'id_cliente'          => $this->request->getPost('id_cliente'),
            'fecha_inspeccion'    => $this->request->getPost('fecha_inspeccion'),
            'nombre_responsable'  => $this->request->getPost('nombre_responsable'),
            'indicador'           => $this->request->getPost('indicador'),
            'cumplimiento'        => (float)($this->request->getPost('cumplimiento') ?? 0),
        ];

        // Fotos — solo si se sube nueva
        for ($i = 1; $i <= 4; $i++) {
            $campo = "registro_formato_$i";
            $nuevaFoto = $this->uploadFoto($campo, static::FOTO_DIR);
            if ($nuevaFoto) {
                if (!empty($inspeccion[$campo]) && file_exists(FCPATH . $inspeccion[$campo])) {
                    unlink(FCPATH . $inspeccion[$campo]);
                }
                $updateData[$campo] = $nuevaFoto;
            }
        }

        $this->model->update($id, $updateData);

        if ($this->request->getPost('finalizar')) {
            return $this->finalizar($id);
        }

        return redirect()->to('/inspecciones/' . static::ROUTE_SLUG . '/edit/' . $id)
            ->with('msg', 'KPI actualizado');
    }

    public function view($id)
    {
        $inspeccion = $this->model->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/' . static::ROUTE_SLUG)->with('error', 'No encontrado');
        }

        $clientModel     = new ClientModel();
        $consultantModel = new ConsultantModel();

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/kpi-limpieza/view', [
                'title'      => static::MODULE_LABEL,
                'inspeccion' => $inspeccion,
                'cliente'    => $clientModel->find($inspeccion['id_cliente']),
                'consultor'  => $consultantModel->find($inspeccion['id_consultor']),
                'slug'       => static::ROUTE_SLUG,
            ]),
            'title' => static::MODULE_LABEL,
        ]);
    }

    public function finalizar($id)
    {
        $inspeccion = $this->model->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/' . static::ROUTE_SLUG)->with('error', 'No encontrado');
        }

        $pdfPath = $this->generarPdfInterno($id);

        $this->model->update($id, [
            'estado'   => 'completo',
            'ruta_pdf' => $pdfPath,
        ]);

        $this->uploadToReportes($id, $pdfPath);

        return redirect()->to('/inspecciones/' . static::ROUTE_SLUG . '/view/' . $id)
            ->with('msg', 'KPI finalizado y PDF generado');
    }

    public function generatePdf($id)
    {
        $pdfPath = $this->generarPdfInterno($id);

        if ($pdfPath && file_exists(FCPATH . $pdfPath)) {
            return $this->response
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'inline; filename="kpi-limpieza-' . $id . '.pdf"')
                ->setBody(file_get_contents(FCPATH . $pdfPath));
        }

        return redirect()->back()->with('error', 'No se pudo generar el PDF');
    }

    public function delete($id)
    {
        $inspeccion = $this->model->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/' . static::ROUTE_SLUG)->with('error', 'No encontrado');
        }

        // Delete photos
        for ($i = 1; $i <= 4; $i++) {
            $campo = "registro_formato_$i";
            if (!empty($inspeccion[$campo]) && file_exists(FCPATH . $inspeccion[$campo])) {
                unlink(FCPATH . $inspeccion[$campo]);
            }
        }
        if (!empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) {
            unlink(FCPATH . $inspeccion['ruta_pdf']);
        }

        $this->model->delete($id);
        return redirect()->to('/inspecciones/' . static::ROUTE_SLUG)->with('msg', 'KPI eliminado');
    }

    // ─── Private helpers ──────────────────────────────

        public function regenerarPdf($id)
    {
        $inspeccion = $this->model->find($id);
        if (!$inspeccion || ($inspeccion['estado'] ?? '') !== 'completo') {
            return redirect()->to('/inspecciones/kpi-limpieza')->with('error', 'Solo se puede regenerar un registro finalizado.');
        }

        $pdfPath = $this->generarPdfInterno($id);

        $this->model->update($id, [
            'ruta_pdf' => $pdfPath,
        ]);

        $inspeccion = $this->model->find($id);
        $this->uploadToReportes($inspeccion, $pdfPath);

        return redirect()->to("/inspecciones/kpi-limpieza/view/{$id}")->with('msg', 'PDF regenerado exitosamente.');
    }

    private function uploadFoto(string $fieldName, string $dir): ?string
    {
        $file = $this->request->getFile($fieldName);
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
        $inspeccion      = $this->model->find($id);
        $clientModel     = new ClientModel();
        $consultantModel = new ConsultantModel();
        $cliente         = $clientModel->find($inspeccion['id_cliente']);
        $consultor       = $consultantModel->find($inspeccion['id_consultor']);

        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $mime = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        $fotosBase64 = [];
        for ($i = 1; $i <= 4; $i++) {
            $campo = "registro_formato_$i";
            $fotosBase64[$campo] = '';
            if (!empty($inspeccion[$campo])) {
                $fotoPath = FCPATH . $inspeccion[$campo];
                if (file_exists($fotoPath)) {
                    $mime = mime_content_type($fotoPath);
                    $fotosBase64[$campo] = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fotoPath));
                }
            }
        }

        $html = view('inspecciones/kpi-limpieza/pdf', [
            'inspeccion'   => $inspeccion,
            'cliente'      => $cliente,
            'consultor'    => $consultor,
            'logoBase64'   => $logoBase64,
            'fotosBase64'  => $fotosBase64,
            'pdfCode'      => static::PDF_CODE,
            'pdfTitle'     => static::PDF_TITLE,
            'pdfIntro'     => static::PDF_INTRO,
        ]);

        $dompdf = new Dompdf();
        $dompdf->set_option('isRemoteEnabled', true);
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $dir = FCPATH . static::PDF_DIR;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $fileName = 'kpi-limpieza-' . $id . '-' . date('Ymd_His') . '.pdf';
        file_put_contents($dir . $fileName, $dompdf->output());

        return static::PDF_DIR . $fileName;
    }

    private function uploadToReportes(int $id, string $pdfPath): void
    {
        $inspeccion = $this->model->find($id);
        $reporteModel = new ReporteModel();

        $tag = static::TAG_PREFIX . ':' . $id;
        $existente = $reporteModel->where('tag', $tag)->first();
        if ($existente) {
            return;
        }

        $reporteModel->insert([
            'id_report_type'   => 6,
            'id_detailreport'  => static::DETAIL_ID,
            'id_cliente'       => $inspeccion['id_cliente'],
            'id_consultor'     => $inspeccion['id_consultor'],
            'report_url'       => base_url($pdfPath),
            'tag'              => $tag,
            'created_at'       => date('Y-m-d H:i:s'),
        ]);
    }
}
