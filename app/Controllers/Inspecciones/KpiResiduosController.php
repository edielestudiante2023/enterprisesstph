<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\KpiResiduosModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use Dompdf\Dompdf;

class KpiResiduosController extends BaseController
{
    protected KpiResiduosModel $model;

    protected const INDICADORES = [
        'Cumplimiento de condiciones higiénico–sanitarias del cuarto de residuos',
        'Cumplimiento en separación adecuada de residuos sólidos',
    ];

    protected const PDF_CODE     = 'FT-SST-230';
    protected const PDF_TITLE    = 'KPI PROGRAMA DE MANEJO INTEGRAL DE RESIDUOS SÓLIDOS';
    protected const PDF_INTRO    = 'Con el fin de verificar la eficacia sanitaria y operativa del <strong>Programa De Manejo Integral De Residuos Sólidos</strong> y garantizar su mejora continua en';
    protected const ROUTE_SLUG   = 'kpi-residuos';
    protected const FOTO_DIR     = 'uploads/inspecciones/kpi-residuos/fotos/';
    protected const PDF_DIR      = 'uploads/inspecciones/kpi-residuos/pdfs/';
    protected const DETAIL_ID    = 34;
    protected const TAG_PREFIX   = 'kpi_res_id';
    protected const MODULE_LABEL = 'KPI Residuos';
    protected const VIEW_DIR     = 'inspecciones/kpi-residuos';

    public function __construct()
    {
        $this->model = new KpiResiduosModel();
    }

    public function list()
    {
        $role   = session()->get('role');
        $userId = session()->get('user_id');

        if ($role === 'admin') {
            $inspecciones = $this->model
                ->select('tbl_kpi_residuos.*, tbl_clientes.nombre_cliente')
                ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_kpi_residuos.id_cliente')
                ->orderBy('tbl_kpi_residuos.fecha_inspeccion', 'DESC')
                ->findAll();
        } else {
            $inspecciones = $this->model->getByConsultor($userId);
        }

        return view('inspecciones/layout_pwa', [
            'content' => view(static::VIEW_DIR . '/list', [
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
            'content' => view(static::VIEW_DIR . '/form', [
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
        if ($inspeccion['estado'] === 'completo') {
            return redirect()->to('/inspecciones/' . static::ROUTE_SLUG . '/view/' . $id);
        }

        return view('inspecciones/layout_pwa', [
            'content' => view(static::VIEW_DIR . '/form', [
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
        if (!$inspeccion || $inspeccion['estado'] === 'completo') {
            return redirect()->to('/inspecciones/' . static::ROUTE_SLUG)->with('error', 'No se puede editar');
        }

        $updateData = [
            'id_cliente'          => $this->request->getPost('id_cliente'),
            'fecha_inspeccion'    => $this->request->getPost('fecha_inspeccion'),
            'nombre_responsable'  => $this->request->getPost('nombre_responsable'),
            'indicador'           => $this->request->getPost('indicador'),
            'cumplimiento'        => (float)($this->request->getPost('cumplimiento') ?? 0),
        ];

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

        return view('inspecciones/layout_pwa', [
            'content' => view(static::VIEW_DIR . '/view', [
                'title'      => static::MODULE_LABEL,
                'inspeccion' => $inspeccion,
                'cliente'    => (new ClientModel())->find($inspeccion['id_cliente']),
                'consultor'  => (new ConsultantModel())->find($inspeccion['id_consultor']),
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
        $this->model->update($id, ['estado' => 'completo', 'ruta_pdf' => $pdfPath]);
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
                ->setHeader('Content-Disposition', 'inline; filename="kpi-residuos-' . $id . '.pdf"')
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

    private function uploadFoto(string $fieldName, string $dir): ?string
    {
        $file = $this->request->getFile($fieldName);
        if (!$file || !$file->isValid() || $file->hasMoved()) return null;
        if (!is_dir(FCPATH . $dir)) mkdir(FCPATH . $dir, 0755, true);
        $fileName = $file->getRandomName();
        $file->move(FCPATH . $dir, $fileName);
        return $dir . $fileName;
    }

    private function generarPdfInterno(int $id): ?string
    {
        $inspeccion = $this->model->find($id);
        $cliente    = (new ClientModel())->find($inspeccion['id_cliente']);
        $consultor  = (new ConsultantModel())->find($inspeccion['id_consultor']);

        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoBase64 = 'data:' . mime_content_type($logoPath) . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        $fotosBase64 = [];
        for ($i = 1; $i <= 4; $i++) {
            $campo = "registro_formato_$i";
            $fotosBase64[$campo] = '';
            if (!empty($inspeccion[$campo]) && file_exists(FCPATH . $inspeccion[$campo])) {
                $fotosBase64[$campo] = 'data:' . mime_content_type(FCPATH . $inspeccion[$campo]) . ';base64,' . base64_encode(file_get_contents(FCPATH . $inspeccion[$campo]));
            }
        }

        $html = view(static::VIEW_DIR . '/pdf', [
            'inspeccion'  => $inspeccion,
            'cliente'     => $cliente,
            'consultor'   => $consultor,
            'logoBase64'  => $logoBase64,
            'fotosBase64' => $fotosBase64,
            'pdfCode'     => static::PDF_CODE,
            'pdfTitle'    => static::PDF_TITLE,
            'pdfIntro'    => static::PDF_INTRO,
        ]);

        $dompdf = new Dompdf();
        $dompdf->set_option('isRemoteEnabled', true);
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $dir = FCPATH . static::PDF_DIR;
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $fileName = 'kpi-residuos-' . $id . '-' . date('Ymd_His') . '.pdf';
        file_put_contents($dir . $fileName, $dompdf->output());
        return static::PDF_DIR . $fileName;
    }

    private function uploadToReportes(int $id, string $pdfPath): void
    {
        $inspeccion = $this->model->find($id);
        $reporteModel = new ReporteModel();
        $tag = static::TAG_PREFIX . ':' . $id;
        if ($reporteModel->where('tag', $tag)->first()) return;
        $reporteModel->insert([
            'id_report_type'  => 6,
            'id_detailreport' => static::DETAIL_ID,
            'id_cliente'      => $inspeccion['id_cliente'],
            'id_consultor'    => $inspeccion['id_consultor'],
            'report_url'      => base_url($pdfPath),
            'tag'             => $tag,
            'created_at'      => date('Y-m-d H:i:s'),
        ]);
    }
}
