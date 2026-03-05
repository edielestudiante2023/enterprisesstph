<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\KpiAguaPotableModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use Dompdf\Dompdf;
use App\Libraries\InspeccionEmailNotifier;
use App\Traits\AutosaveJsonTrait;

class KpiAguaPotableController extends BaseController
{
    use AutosaveJsonTrait;
    protected KpiAguaPotableModel $model;

    protected const INDICADORES = [
        'Continuidad del servicio de agua potable en situaciones de suspensión',
        'Ejecución de limpieza y desinfección de tanques de agua potable',
    ];

    protected const PDF_CODE     = 'FT-SST-232';
    protected const PDF_TITLE    = 'KPI PROGRAMA DE ABASTECIMIENTO Y CONTROL DE AGUA POTABLE';
    protected const PDF_INTRO    = 'Con el fin de verificar la correcta ejecución, eficacia sanitaria y operativa del <strong>Programa de Abastecimiento y Control de Agua Potable</strong> y garantizar su mejora continua en';
    protected const ROUTE_SLUG   = 'kpi-agua-potable';
    protected const FOTO_DIR     = 'uploads/inspecciones/kpi-agua-potable/fotos/';
    protected const PDF_DIR      = 'uploads/inspecciones/kpi-agua-potable/pdfs/';
    protected const DETAIL_ID    = 36;
    protected const TAG_PREFIX   = 'kpi_agua_id';
    protected const MODULE_LABEL = 'KPI Agua Potable';
    protected const VIEW_DIR     = 'inspecciones/kpi-agua-potable';

    public function __construct()
    {
        $this->model = new KpiAguaPotableModel();
    }

    public function list()
    {
        $inspecciones = $this->model
            ->select('tbl_kpi_agua_potable.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_kpi_agua_potable.id_cliente')
            ->orderBy('tbl_kpi_agua_potable.fecha_inspeccion', 'DESC')
            ->findAll();

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
        $isAutosave = $this->isAutosaveRequest();

        if (!$isAutosave) {
            if (!$this->validate(['id_cliente' => 'required|integer', 'fecha_inspeccion' => 'required|valid_date'])) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }
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

        if ($isAutosave) {
            return $this->autosaveJsonSuccess($id);
        }

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
        if (!$inspeccion) {
            if ($this->isAutosaveRequest()) {
                return $this->autosaveJsonError('No encontrada', 404);
            }
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

        if ($this->isAutosaveRequest()) {
            return $this->autosaveJsonSuccess((int)$id);
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

        // Enviar email con PDF adjunto
        $inspeccion = $this->model->find($id);
        $emailResult = InspeccionEmailNotifier::enviar(
            (int) $inspeccion['id_cliente'],
            (int) $inspeccion['id_consultor'],
            'KPI PROGRAMA DE ABASTECIMIENTO Y CONTROL DE AGUA POTABLE',
            $inspeccion['fecha_inspeccion'],
            $pdfPath,
            (int) $inspeccion['id'],
            'KpiAguaPotable',
            $inspeccion['nombre_responsable'] ?? ''
        );
        $msg = 'KPI finalizado y PDF generado.';
        if ($emailResult['success']) {
            $msg .= ' ' . $emailResult['message'];
        } else {
            $msg .= ' (Email no enviado: ' . $emailResult['error'] . ')';
        }

        return redirect()->to('/inspecciones/' . static::ROUTE_SLUG . '/view/' . $id)
            ->with('msg', $msg);
    }

    public function generatePdf($id)
    {
        $pdfPath = $this->generarPdfInterno($id);
        if ($pdfPath && file_exists(FCPATH . $pdfPath)) {
            $this->model->update($id, ['ruta_pdf' => $pdfPath]);
            return $this->response
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'inline; filename="kpi-agua-potable-' . $id . '.pdf"')
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
            if (!empty($inspeccion[$campo]) && file_exists(FCPATH . $inspeccion[$campo])) unlink(FCPATH . $inspeccion[$campo]);
        }
        if (!empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) unlink(FCPATH . $inspeccion['ruta_pdf']);
        $this->model->delete($id);
        return redirect()->to('/inspecciones/' . static::ROUTE_SLUG)->with('msg', 'KPI eliminado');
    }

        public function regenerarPdf($id)
    {
        $inspeccion = $this->model->find($id);
        if (!$inspeccion || ($inspeccion['estado'] ?? '') !== 'completo') {
            return redirect()->to('/inspecciones/kpi-agua-potable')->with('error', 'Solo se puede regenerar un registro finalizado.');
        }

        $pdfPath = $this->generarPdfInterno($id);

        $this->model->update($id, [
            'ruta_pdf' => $pdfPath,
        ]);

        $this->uploadToReportes($id, $pdfPath);

        return redirect()->to("/inspecciones/kpi-agua-potable/view/{$id}")->with('msg', 'PDF regenerado exitosamente.');
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
            'inspeccion'  => $inspeccion, 'cliente' => $cliente, 'consultor' => $consultor,
            'logoBase64'  => $logoBase64, 'fotosBase64' => $fotosBase64,
            'pdfCode' => static::PDF_CODE, 'pdfTitle' => static::PDF_TITLE, 'pdfIntro' => static::PDF_INTRO,
        ]);

        $dompdf = new Dompdf();
        $dompdf->set_option('isRemoteEnabled', true);
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $dir = FCPATH . static::PDF_DIR;
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $fileName = 'kpi-agua-potable-' . $id . '-' . date('Ymd_His') . '.pdf';
        file_put_contents($dir . $fileName, $dompdf->output());
        return static::PDF_DIR . $fileName;
    }

    // ── Email ─────────────────────────────────────────────────

    public function enviarEmail($id)
    {
        $inspeccion = $this->model->find($id);
        if (!$inspeccion || $inspeccion['estado'] !== 'completo' || empty($inspeccion['ruta_pdf'])) {
            return redirect()->to("/inspecciones/kpi-agua-potable/view/{$id}")->with('error', 'Debe estar finalizado con PDF para enviar email.');
        }

        $result = InspeccionEmailNotifier::enviar(
            (int) $inspeccion['id_cliente'],
            (int) $inspeccion['id_consultor'],
            'KPI PROGRAMA DE ABASTECIMIENTO Y CONTROL DE AGUA POTABLE',
            $inspeccion['fecha_inspeccion'],
            $inspeccion['ruta_pdf'],
            (int) $inspeccion['id'],
            'KpiAguaPotable',
            $inspeccion['nombre_responsable'] ?? ''
        );

        if ($result['success']) {
            return redirect()->to("/inspecciones/kpi-agua-potable/view/{$id}")->with('msg', $result['message']);
        }
        return redirect()->to("/inspecciones/kpi-agua-potable/view/{$id}")->with('error', $result['error']);
    }

    private function uploadToReportes(int $id, string $pdfPath): void
    {
        $inspeccion = $this->model->find($id);
        $reporteModel = new ReporteModel();
        $clientModel = new ClientModel();
        $cliente = $clientModel->find($inspeccion['id_cliente']);
        if (!$cliente) return;

        $nitCliente = $cliente['nit_cliente'];
        $tag = static::TAG_PREFIX . ':' . $id;

        $existente = $reporteModel->where('tag', $tag)->first();

        $destDir = ROOTPATH . 'public/uploads/' . $nitCliente;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $fileName = strtolower(str_replace(' ', '_', static::ROUTE_SLUG)) . '_' . $id . '_' . date('Ymd_His') . '.pdf';
        $destPath = $destDir . '/' . $fileName;
        copy(FCPATH . $pdfPath, $destPath);

        $data = [
            'titulo_reporte'  => static::PDF_TITLE . ' - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . ($inspeccion['fecha_inspeccion'] ?? ''),
            'id_detailreport' => static::DETAIL_ID,
            'id_report_type'  => 6,
            'id_cliente'      => $inspeccion['id_cliente'],
            'id_consultor'    => $inspeccion['id_consultor'],
            'estado'          => 'CERRADO',
            'observaciones'   => 'Generado automaticamente desde modulo de inspecciones. ' . $tag,
            'enlace'          => base_url('uploads/' . $nitCliente . '/' . $fileName),
            'tag'             => $tag,
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        if ($existente) {
            $reporteModel->update($existente['id_reporte'], $data);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $reporteModel->save($data);
        }
    }
}
