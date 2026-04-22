<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\KpiResiduosModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use Dompdf\Dompdf;
use App\Libraries\InspeccionEmailNotifier;
use App\Traits\AutosaveJsonTrait;
use App\Traits\ImagenCompresionTrait;

class KpiResiduosController extends BaseController
{
    use AutosaveJsonTrait;
    use ImagenCompresionTrait;
    use AppTraitsInspeccionesTransactionalTrait;
    protected KpiResiduosModel $model;

    protected const INDICADOR_CONFIG = [
        'Cumplimiento de condiciones higiénico–sanitarias del cuarto de residuos' => [
            'formula'           => '(Inspecciones conformes ÷ Inspecciones realizadas) × 100',
            'label_numerador'   => 'N.° de inspecciones conformes',
            'label_denominador' => 'N.° de inspecciones realizadas',
            'meta'              => 100,
            'meta_texto'        => '100%',
        ],
        'Cumplimiento en separación adecuada de residuos sólidos' => [
            'formula'           => '(Puntos ecológicos correctamente separados ÷ Puntos ecológicos inspeccionados) × 100',
            'label_numerador'   => 'N.° de puntos ecológicos correctamente separados',
            'label_denominador' => 'N.° de puntos ecológicos inspeccionados',
            'meta'              => 90,
            'meta_texto'        => '≥ 90%',
        ],
    ];

    protected const PDF_CODE     = 'FT-SST-230';
    protected const PDF_TITLE    = 'KPI PROGRAMA DE MANEJO INTEGRAL DE RESIDUOS SÓLIDOS';
    protected const PDF_INTRO    = 'Con el fin de verificar la eficacia sanitaria y operativa del <strong>Programa De Manejo Integral De Residuos Sólidos</strong> y garantizar su mejora continua en';
    protected const ROUTE_SLUG   = 'kpi-residuos';
    protected const FOTO_DIR     = 'uploads/inspecciones/kpi-residuos/fotos/';
    protected const PDF_DIR      = 'uploads/inspecciones/kpi-residuos/pdfs/';
    protected const DETAIL_ID    = 27;
    protected const TAG_PREFIX   = 'kpi_res_id';
    protected const MODULE_LABEL = 'KPI Residuos';
    protected const VIEW_DIR     = 'inspecciones/kpi-residuos';

    public function __construct()
    {
        $this->model = new KpiResiduosModel();
    }

    public function list()
    {
        $inspecciones = $this->model
            ->select('tbl_kpi_residuos.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_kpi_residuos.id_cliente')
            ->orderBy('tbl_kpi_residuos.fecha_inspeccion', 'DESC')
            ->findAll();

        // Agrupar por cliente+fecha como un solo reporte
        $grupos = [];
        foreach ($inspecciones as $insp) {
            $key = $insp['id_cliente'] . '_' . $insp['fecha_inspeccion'];
            if (!isset($grupos[$key])) {
                $grupos[$key] = [
                    'id_cliente'       => $insp['id_cliente'],
                    'nombre_cliente'   => $insp['nombre_cliente'],
                    'fecha_inspeccion' => $insp['fecha_inspeccion'],
                    'indicadores'      => [],
                    'estado'           => 'completo',
                    'first_id'         => $insp['id'],
                    'ruta_pdf'         => null,
                ];
            }
            $grupos[$key]['indicadores'][] = $insp;
            if ($insp['estado'] !== 'completo') {
                $grupos[$key]['estado'] = 'borrador';
            }
            if (!empty($insp['ruta_pdf'])) {
                $grupos[$key]['ruta_pdf'] = $insp['ruta_pdf'];
            }
        }

        return view('inspecciones/layout_pwa', [
            'content' => view(static::VIEW_DIR . '/list', [
                'title'  => static::MODULE_LABEL,
                'grupos' => array_values($grupos),
                'slug'   => static::ROUTE_SLUG,
                'totalIndicadores' => count(static::INDICADOR_CONFIG),
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

        for ($i = 1; $i <= 4; $i++) {
            $data["registro_formato_$i"] = $this->uploadFoto("registro_formato_$i", static::FOTO_DIR);
        }

        // Upsert por (id_cliente, fecha_inspeccion, indicador): autosaves concurrentes no deben crear duplicados
        $existing = $this->model
            ->where('id_cliente', $data['id_cliente'])
            ->where('fecha_inspeccion', $data['fecha_inspeccion'])
            ->where('indicador', $data['indicador'])
            ->first();

        if ($existing) {
            $updateData = $data;
            for ($i = 1; $i <= 4; $i++) {
                if ($updateData["registro_formato_$i"] === null) {
                    unset($updateData["registro_formato_$i"]);
                }
            }
            if (!empty($existing['estado']) && $existing['estado'] === 'completo') {
                unset($updateData['estado']);
            }
            $this->model->update($existing['id'], $updateData);
            $id = $existing['id'];
        } else {
            $this->model->insert($data);
            $id = $this->model->getInsertID();
        }

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

        if ( = ->guardFinalizado(, '/inspecciones/' . static::ROUTE_SLUG . '/view/' . )) return ;
        return view('inspecciones/layout_pwa', [
            'content' => view(static::VIEW_DIR . '/form', [
                'title'       => 'Editar ' . static::MODULE_LABEL,
                'inspeccion'  => $inspeccion,
                'idCliente'   => $inspeccion['id_cliente'],
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

        return view('inspecciones/layout_pwa', [
            'content' => view(static::VIEW_DIR . '/view', [
                'title'      => static::MODULE_LABEL,
                'inspeccion' => $inspeccion,
                'cliente'    => (new ClientModel())->find($inspeccion['id_cliente']),
                'consultor'  => (new ConsultantModel())->find($inspeccion['id_consultor']),
                'indicadorConfig' => static::INDICADOR_CONFIG,
                'slug'       => static::ROUTE_SLUG,
            ]),
            'title' => static::MODULE_LABEL,
        ]);
    }

    /**
     * Finaliza TODOS los indicadores del mismo cliente+fecha como un solo reporte.
     */
    public function finalizar($id)
    {
        $inspeccion = $this->model->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/' . static::ROUTE_SLUG)->with('error', 'No encontrado');
        }

        // Buscar TODOS los indicadores del mismo cliente+fecha
        $grupo = $this->model
            ->where('id_cliente', $inspeccion['id_cliente'])
            ->where('fecha_inspeccion', $inspeccion['fecha_inspeccion'])
            ->findAll();

        // Generar PDF combinado con todos los indicadores
        $pdfPath = $this->generarPdfInterno($id);

        // Marcar TODOS como completo con el mismo PDF
        foreach ($grupo as $rec) {
            $this->model->update($rec['id'], ['estado' => 'completo', 'ruta_pdf' => $pdfPath]);
        }

        // Subir a reportes una sola vez
        $this->uploadToReportes((int) $inspeccion['id'], $pdfPath);

        // Enviar email con PDF adjunto una sola vez
        $emailResult = InspeccionEmailNotifier::enviar(
            (int) $inspeccion['id_cliente'],
            (int) $inspeccion['id_consultor'],
            static::PDF_TITLE,
            $inspeccion['fecha_inspeccion'],
            $pdfPath,
            (int) $inspeccion['id'],
            str_replace(' ', '', static::MODULE_LABEL),
            $inspeccion['nombre_responsable'] ?? ''
        );
        $n = count($grupo);
        $msg = "{$n} indicador(es) finalizado(s) y PDF generado.";
        if ($emailResult['success']) {
            $msg .= ' ' . $emailResult['message'];
        } else {
            $msg .= ' (Email no enviado: ' . $emailResult['error'] . ')';
        }

        return redirect()->to('/inspecciones/' . static::ROUTE_SLUG . '/view/' . $id)
            ->with('msg', $msg);
    }

    /**
     * Endpoint GET para finalizar grupo desde SweetAlert.
     */
    public function finalizarGrupo($id)
    {
        return $this->finalizar($id);
    }

    public function generatePdf($id)
    {
        $pdfPath = $this->generarPdfInterno($id);
        if ($pdfPath && file_exists(FCPATH . $pdfPath)) {
            $this->model->update($id, ['ruta_pdf' => $pdfPath]);
            $this->servirPdf(FCPATH . $pdfPath, 'kpi-residuos-' . $id . '.pdf');
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

        public function regenerarPdf($id)
    {
        $inspeccion = $this->model->find($id);
        if (!$inspeccion || ($inspeccion['estado'] ?? '') !== 'completo') {
            return redirect()->to('/inspecciones/kpi-residuos')->with('error', 'Solo se puede regenerar un registro finalizado.');
        }

        $pdfPath = $this->generarPdfInterno($id);

        $this->model->update($id, [
            'ruta_pdf' => $pdfPath,
        ]);

        $this->uploadToReportes($id, $pdfPath);

        return redirect()->to("/inspecciones/kpi-residuos/view/{$id}")->with('msg', 'PDF regenerado exitosamente.');
    }

    private function uploadFoto(string $fieldName, string $dir): ?string
    {
        $file = $this->request->getFile($fieldName);
        if (!$file || !$file->isValid() || $file->hasMoved()) return null;
        if (!is_dir(FCPATH . $dir)) mkdir(FCPATH . $dir, 0755, true);
        $fileName = $file->getRandomName();
        $file->move(FCPATH . $dir, $fileName);
        $this->comprimirImagen(FCPATH . $dir . $fileName);
        return $dir . $fileName;
    }

    private function generarPdfInterno(int $id): ?string
    {
        $inspeccion = $this->model->find($id);
        $cliente    = (new ClientModel())->find($inspeccion['id_cliente']);
        $consultor  = (new ConsultantModel())->find($inspeccion['id_consultor']);

        // Buscar TODOS los indicadores del mismo cliente+fecha
        $grupo = $this->model
            ->where('id_cliente', $inspeccion['id_cliente'])
            ->where('fecha_inspeccion', $inspeccion['fecha_inspeccion'])
            ->findAll();

        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoBase64 = $this->fotoABase64ParaPdf($logoPath);
            }
        }

        // Preparar fotos de TODOS los indicadores
        $indicadoresData = [];
        foreach ($grupo as $rec) {
            $fotosBase64 = [];
            for ($i = 1; $i <= 4; $i++) {
                $campo = "registro_formato_$i";
                $fotosBase64[$campo] = '';
                if (!empty($rec[$campo]) && file_exists(FCPATH . $rec[$campo])) {
                    $fotosBase64[$campo] = $this->fotoABase64ParaPdf(FCPATH . $rec[$campo]);
                }
            }
            $indicadoresData[] = [
                'registro'    => $rec,
                'fotosBase64' => $fotosBase64,
            ];
        }

        $html = view(static::VIEW_DIR . '/pdf', [
            'inspeccion'      => $inspeccion, // primer registro (para datos generales)
            'indicadoresData' => $indicadoresData,
            'cliente'         => $cliente,
            'consultor'       => $consultor,
            'logoBase64'      => $logoBase64,
            'indicadorConfig' => static::INDICADOR_CONFIG,
            'pdfCode'         => static::PDF_CODE,
            'pdfTitle'        => static::PDF_TITLE,
            'pdfIntro'        => static::PDF_INTRO,
        ]);

        $dompdf = new Dompdf();
        $dompdf->set_option('isRemoteEnabled', true);
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $dir = FCPATH . static::PDF_DIR;
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $slug = static::ROUTE_SLUG;
        $fileName = "{$slug}-{$id}-" . date('Ymd_His') . '.pdf';
        file_put_contents($dir . $fileName, $dompdf->output());
        return static::PDF_DIR . $fileName;
    }

    // ── Email ─────────────────────────────────────────────────

    public function enviarEmail($id)
    {
        $inspeccion = $this->model->find($id);
        if (!$inspeccion || $inspeccion['estado'] !== 'completo' || empty($inspeccion['ruta_pdf'])) {
            return redirect()->to("/inspecciones/kpi-residuos/view/{$id}")->with('error', 'Debe estar finalizado con PDF para enviar email.');
        }

        $result = InspeccionEmailNotifier::enviar(
            (int) $inspeccion['id_cliente'],
            (int) $inspeccion['id_consultor'],
            'KPI PROGRAMA DE MANEJO INTEGRAL DE RESIDUOS SÓLIDOS',
            $inspeccion['fecha_inspeccion'],
            $inspeccion['ruta_pdf'],
            (int) $inspeccion['id'],
            'KpiResiduos',
            $inspeccion['nombre_responsable'] ?? ''
        );

        if ($result['success']) {
            return redirect()->to("/inspecciones/kpi-residuos/view/{$id}")->with('msg', $result['message']);
        }
        return redirect()->to("/inspecciones/kpi-residuos/view/{$id}")->with('error', $result['error']);
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

        $destDir = UPLOADS_PATH . $nitCliente;
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
            'enlace'          => base_url(UPLOADS_URL_PREFIX . '/' . $nitCliente . '/' . $fileName),
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
