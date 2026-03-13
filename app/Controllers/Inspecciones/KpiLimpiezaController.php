<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\KpiLimpiezaModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use Dompdf\Dompdf;
use App\Libraries\InspeccionEmailNotifier;
use App\Traits\AutosaveJsonTrait;
use App\Traits\ImagenCompresionTrait;

class KpiLimpiezaController extends BaseController
{
    use AutosaveJsonTrait;
    use ImagenCompresionTrait;
    protected KpiLimpiezaModel $model;

    protected const INDICADOR_CONFIG = [
        'Cumplimiento de actividades de limpieza y desinfección' => [
            'formula'           => '(N.° días registrados en planilla ÷ Días hábiles del periodo) × 100',
            'label_numerador'   => 'N.° de días registrados en la planilla de limpieza',
            'label_denominador' => 'Días hábiles del periodo evaluado',
            'meta'              => 95,
            'meta_texto'        => '≥ 95%',
        ],
        'Estado de los elementos de limpieza' => [
            'formula'           => '(N.° elementos en buen estado ÷ N.° total elementos verificados) × 100',
            'label_numerador'   => 'N.° de elementos de limpieza en buen estado',
            'label_denominador' => 'N.° total de elementos de limpieza verificados',
            'meta'              => 90,
            'meta_texto'        => '≥ 90%',
        ],
    ];

    protected const PDF_CODE     = 'FT-SST-229';
    protected const PDF_TITLE    = 'KPI PROGRAMA DE LIMPIEZA Y DESINFECCIÓN';
    protected const PDF_INTRO    = 'Con el fin de verificar la ejecución efectiva del <strong>Programa de Limpieza y Desinfección</strong> y garantizar su mejora continua en';
    protected const ROUTE_SLUG   = 'kpi-limpieza';
    protected const FOTO_DIR     = 'uploads/inspecciones/kpi-limpieza/fotos/';
    protected const PDF_DIR      = 'uploads/inspecciones/kpi-limpieza/pdfs/';
    protected const DETAIL_ID    = 26;
    protected const TAG_PREFIX   = 'kpi_limp_id';
    protected const MODULE_LABEL = 'KPI Limpieza';

    public function __construct()
    {
        $this->model = new KpiLimpiezaModel();
    }

    public function list()
    {
        $inspecciones = $this->model
            ->select('tbl_kpi_limpieza.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_kpi_limpieza.id_cliente')
            ->orderBy('tbl_kpi_limpieza.fecha_inspeccion', 'DESC')
            ->findAll();

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
                'title'              => 'Nuevo ' . static::MODULE_LABEL,
                'inspeccion'         => null,
                'idCliente'          => $idCliente,
                'indicadores'        => array_keys(static::INDICADOR_CONFIG),
                'indicadorConfig'    => static::INDICADOR_CONFIG,
                'slug'               => static::ROUTE_SLUG,
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

        $indicador = $this->request->getPost('indicador');
        $num = $this->request->getPost('valor_numerador') !== null && $this->request->getPost('valor_numerador') !== '' ? (int) $this->request->getPost('valor_numerador') : null;
        $den = $this->request->getPost('valor_denominador') !== null && $this->request->getPost('valor_denominador') !== '' ? (int) $this->request->getPost('valor_denominador') : null;
        $cumplimiento = ($den !== null && $den > 0) ? round(($num / $den) * 100, 2) : (float)($this->request->getPost('cumplimiento') ?? 0);
        $meta = static::INDICADOR_CONFIG[$indicador]['meta'] ?? 100;
        $calificacion = ($den !== null && $den > 0) ? ($cumplimiento >= $meta ? 'CUMPLE' : 'NO CUMPLE') : null;

        $data = [
            'id_cliente'                => $this->request->getPost('id_cliente'),
            'id_consultor'              => $userId,
            'fecha_inspeccion'          => $this->request->getPost('fecha_inspeccion'),
            'nombre_responsable'        => $this->request->getPost('nombre_responsable'),
            'indicador'                 => $indicador,
            'cumplimiento'              => $cumplimiento,
            'valor_numerador'           => $num,
            'valor_denominador'         => $den,
            'calificacion_cualitativa'  => $calificacion,
            'observaciones'             => $this->request->getPost('observaciones'),
            'estado'                    => 'borrador',
        ];

        // Upload fotos
        for ($i = 1; $i <= 4; $i++) {
            $data["registro_formato_$i"] = $this->uploadFoto("registro_formato_$i", static::FOTO_DIR);
        }

        $this->model->insert($data);
        $id = $this->model->getInsertID();

        if ($isAutosave) {
            return $this->autosaveJsonSuccess($id);
        }

        return redirect()->to('/inspecciones/' . static::ROUTE_SLUG . '/edit/' . $id)
            ->with('msg', 'KPI guardado como borrador')
            ->with('saved_cliente_id', $this->request->getPost('id_cliente'))
            ->with('saved_indicador', $indicador);
    }

    public function edit($id)
    {
        $inspeccion = $this->model->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/' . static::ROUTE_SLUG)->with('error', 'No encontrado');
        }
        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/kpi-limpieza/form', [
                'title'              => 'Editar ' . static::MODULE_LABEL,
                'inspeccion'         => $inspeccion,
                'idCliente'          => $inspeccion['id_cliente'],
                'indicadores'        => array_keys(static::INDICADOR_CONFIG),
                'indicadorConfig'    => static::INDICADOR_CONFIG,
                'slug'               => static::ROUTE_SLUG,
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

        $indicador = $this->request->getPost('indicador');
        $num = $this->request->getPost('valor_numerador') !== null && $this->request->getPost('valor_numerador') !== '' ? (int) $this->request->getPost('valor_numerador') : null;
        $den = $this->request->getPost('valor_denominador') !== null && $this->request->getPost('valor_denominador') !== '' ? (int) $this->request->getPost('valor_denominador') : null;
        $cumplimiento = ($den !== null && $den > 0) ? round(($num / $den) * 100, 2) : (float)($this->request->getPost('cumplimiento') ?? 0);
        $meta = static::INDICADOR_CONFIG[$indicador]['meta'] ?? 100;
        $calificacion = ($den !== null && $den > 0) ? ($cumplimiento >= $meta ? 'CUMPLE' : 'NO CUMPLE') : null;

        $updateData = [
            'id_cliente'                => $this->request->getPost('id_cliente'),
            'fecha_inspeccion'          => $this->request->getPost('fecha_inspeccion'),
            'nombre_responsable'        => $this->request->getPost('nombre_responsable'),
            'indicador'                 => $indicador,
            'cumplimiento'              => $cumplimiento,
            'valor_numerador'           => $num,
            'valor_denominador'         => $den,
            'calificacion_cualitativa'  => $calificacion,
            'observaciones'             => $this->request->getPost('observaciones'),
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

        if ($this->isAutosaveRequest()) {
            return $this->autosaveJsonSuccess((int)$id);
        }

        return redirect()->to('/inspecciones/' . static::ROUTE_SLUG . '/edit/' . $id)
            ->with('msg', 'KPI actualizado')
            ->with('saved_cliente_id', $this->request->getPost('id_cliente'))
            ->with('saved_indicador', $indicador);
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
                'slug'           => static::ROUTE_SLUG,
                'indicadorConfig' => static::INDICADOR_CONFIG,
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

        // Enviar email con PDF adjunto
        $inspeccion = $this->model->find($id);
        $emailResult = InspeccionEmailNotifier::enviar(
            (int) $inspeccion['id_cliente'],
            (int) $inspeccion['id_consultor'],
            'KPI PROGRAMA DE LIMPIEZA Y DESINFECCIÓN',
            $inspeccion['fecha_inspeccion'],
            $pdfPath,
            (int) $inspeccion['id'],
            'KpiLimpieza',
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
            $this->servirPdf(FCPATH . $pdfPath, 'kpi-limpieza-' . $id . '.pdf');
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

        $this->uploadToReportes($id, $pdfPath);

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
        $this->comprimirImagen(FCPATH . $dir . $fileName);
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
                $logoBase64 = $this->fotoABase64ParaPdf($logoPath);
            }
        }

        $fotosBase64 = [];
        for ($i = 1; $i <= 4; $i++) {
            $campo = "registro_formato_$i";
            $fotosBase64[$campo] = '';
            if (!empty($inspeccion[$campo])) {
                $fotoPath = FCPATH . $inspeccion[$campo];
                if (file_exists($fotoPath)) {
                    $fotosBase64[$campo] = $this->fotoABase64ParaPdf($fotoPath);
                }
            }
        }

        // Histórico de indicadores del programa
        $kpiPrograma = [];
        foreach (static::INDICADOR_CONFIG as $nombre => $cfg) {
            $ultimo = $this->model
                ->where('id_cliente', $inspeccion['id_cliente'])
                ->where('estado', 'completo')
                ->where('indicador', $nombre)
                ->orderBy('fecha_inspeccion', 'DESC')
                ->first();
            $kpiPrograma[] = [
                'indicador'    => $nombre,
                'meta_texto'   => $cfg['meta_texto'],
                'cumplimiento' => $ultimo ? (float) $ultimo['cumplimiento'] : null,
                'calificacion' => $ultimo['calificacion_cualitativa'] ?? null,
                'fecha'        => $ultimo ? $ultimo['fecha_inspeccion'] : null,
            ];
        }

        $html = view('inspecciones/kpi-limpieza/pdf', [
            'inspeccion'   => $inspeccion,
            'cliente'      => $cliente,
            'consultor'    => $consultor,
            'logoBase64'   => $logoBase64,
            'fotosBase64'  => $fotosBase64,
            'pdfCode'      => static::PDF_CODE,
            'pdfTitle'        => static::PDF_TITLE,
            'pdfIntro'        => static::PDF_INTRO,
            'indicadorConfig' => static::INDICADOR_CONFIG,
            'kpiPrograma'     => $kpiPrograma,
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

    // ── Email ─────────────────────────────────────────────────

    public function enviarEmail($id)
    {
        $inspeccion = $this->model->find($id);
        if (!$inspeccion || $inspeccion['estado'] !== 'completo' || empty($inspeccion['ruta_pdf'])) {
            return redirect()->to("/inspecciones/kpi-limpieza/view/{$id}")->with('error', 'Debe estar finalizado con PDF para enviar email.');
        }

        $result = InspeccionEmailNotifier::enviar(
            (int) $inspeccion['id_cliente'],
            (int) $inspeccion['id_consultor'],
            'KPI PROGRAMA DE LIMPIEZA Y DESINFECCIÓN',
            $inspeccion['fecha_inspeccion'],
            $inspeccion['ruta_pdf'],
            (int) $inspeccion['id'],
            'KpiLimpieza',
            $inspeccion['nombre_responsable'] ?? ''
        );

        if ($result['success']) {
            return redirect()->to("/inspecciones/kpi-limpieza/view/{$id}")->with('msg', $result['message']);
        }
        return redirect()->to("/inspecciones/kpi-limpieza/view/{$id}")->with('error', $result['error']);
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
