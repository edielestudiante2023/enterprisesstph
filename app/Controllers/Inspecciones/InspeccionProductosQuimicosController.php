<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\InspeccionProductosQuimicosModel;
use App\Models\InspeccionProductosQuimicosFotoModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use Dompdf\Dompdf;
use App\Libraries\InspeccionEmailNotifier;
use App\Traits\AutosaveJsonTrait;
use App\Traits\ImagenCompresionTrait;

class InspeccionProductosQuimicosController extends BaseController
{
    use AutosaveJsonTrait;
    use ImagenCompresionTrait;
    use \App\Traits\PreventDuplicateBorradorTrait;
    use \App\Traits\InspeccionesTransactionalTrait;

    protected InspeccionProductosQuimicosModel $inspeccionModel;
    protected InspeccionProductosQuimicosFotoModel $fotoModel;

    /**
     * 17 items de la lista de chequeo. Los items del grupo 'condicional'
     * se muestran solo si tiene_guadaniadora=1.
     */
    public const ITEMS = [
        1  => ['label' => 'El area de almacenamiento se encuentra ordenada, limpia y senalizada?',                           'grupo' => 'general'],
        2  => ['label' => 'Los productos quimicos estan correctamente identificados y rotulados?',                           'grupo' => 'general'],
        3  => ['label' => 'Se dispone de las Fichas de Datos de Seguridad (FDS) de los productos utilizados?',                'grupo' => 'general'],
        4  => ['label' => 'El personal conoce los riesgos basicos de los productos y evita mezclas peligrosas (ej. cloro con acidos)?', 'grupo' => 'general'],
        5  => ['label' => 'El personal utiliza los Elementos de Proteccion Personal (EPP) requeridos (guantes, gafas, etc.)?', 'grupo' => 'general'],
        6  => ['label' => 'Los productos se almacenan en sus envases originales o en envases correctamente rotulados?',       'grupo' => 'general'],
        7  => ['label' => 'Se evita el uso de envases reutilizados no controlados (botellas de bebidas, etc.)?',              'grupo' => 'general'],
        8  => ['label' => 'Los envases se encuentran en buen estado, sin fugas o deterioro?',                                 'grupo' => 'general'],
        9  => ['label' => 'El area cuenta con ventilacion e iluminacion adecuadas?',                                          'grupo' => 'general'],
        10 => ['label' => 'Se evita almacenar productos quimicos junto con alimentos o bebidas?',                             'grupo' => 'general'],
        11 => ['label' => 'Se cuenta con elementos basicos para atencion de derrames (absorbente, trapo, arena u otro)?',     'grupo' => 'general'],
        12 => ['label' => 'Se dispone de un extintor cercano y en condiciones operativas?',                                   'grupo' => 'general'],
        13 => ['label' => 'Las conexiones electricas del area estan en buen estado y protegidas?',                            'grupo' => 'general'],
        14 => ['label' => 'Se identifican productos vencidos o en mal estado?',                                               'grupo' => 'general'],
        15 => ['label' => 'Los residuos de envases quimicos se disponen adecuadamente?',                                      'grupo' => 'general'],
        16 => ['label' => 'La gasolina se almacena en recipiente adecuado, cerrado y rotulado?',                              'grupo' => 'condicional'],
        17 => ['label' => 'Se almacena en un lugar ventilado y separado de fuentes de calor?',                                'grupo' => 'condicional'],
    ];

    public const FACTORES = ['C' => 1.0, 'CP' => 0.5, 'NC' => 0.0, 'NA' => null];

    public function __construct()
    {
        $this->inspeccionModel = new InspeccionProductosQuimicosModel();
        $this->fotoModel = new InspeccionProductosQuimicosFotoModel();
    }

    public function list()
    {
        $inspecciones = $this->inspeccionModel
            ->select('tbl_inspeccion_productos_quimicos.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_productos_quimicos.id_cliente', 'left')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_productos_quimicos.id_consultor', 'left')
            ->orderBy('tbl_inspeccion_productos_quimicos.fecha_inspeccion', 'DESC')
            ->findAll();

        $data = [
            'title'        => 'Inspeccion de Productos Quimicos',
            'inspecciones' => $inspecciones,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/productos-quimicos/list', $data),
            'title'   => 'Productos Quimicos',
        ]);
    }

    public function create($idCliente = null)
    {
        $data = [
            'title'      => 'Nueva Inspeccion de Productos Quimicos',
            'inspeccion' => null,
            'idCliente'  => $idCliente,
            'items'      => self::ITEMS,
            'fotos'      => [],
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/productos-quimicos/form', $data),
            'title'   => 'Nueva Productos Quimicos',
        ]);
    }

    public function store()
    {
        $existing = $this->reuseExistingBorrador($this->inspeccionModel, 'fecha_inspeccion', '/inspecciones/productos-quimicos/edit/');
        if ($existing) return $existing;

        $isAutosave = $this->isAutosaveRequest();

        if ($isAutosave) {
            if ($err = $this->validateAutosaveMinimum()) return $err;
        } else {
            if (!$this->validate(['id_cliente' => 'required|integer', 'fecha_inspeccion' => 'required|valid_date'])) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }
        }

        $userId = session()->get('user_id');
        $idInspeccion = 0;
        $detailIds = [];

        $txResult = $this->runTransactional(function () use ($userId, &$idInspeccion, &$detailIds) {
            $inspeccionData = $this->getInspeccionPostData($userId);
            $inspeccionData['estado'] = 'borrador';
            $this->inspeccionModel->insert($inspeccionData);
            $idInspeccion = $this->inspeccionModel->getInsertID();
            $detailIds = $this->saveFotos($idInspeccion);
            return true;
        });

        if ($txResult instanceof \CodeIgniter\HTTP\ResponseInterface) return $txResult;

        if ($isAutosave) {
            return $this->autosaveJsonSuccess($idInspeccion, ['detail_ids' => $detailIds]);
        }

        return redirect()->to('/inspecciones/productos-quimicos/edit/' . $idInspeccion)
            ->with('msg', 'Inspeccion guardada como borrador');
    }

    public function edit($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/productos-quimicos')->with('error', 'Inspeccion no encontrada');
        }
        if ($r = $this->guardFinalizado($inspeccion, '/inspecciones/productos-quimicos/view/' . $id)) return $r;

        $data = [
            'title'      => 'Editar Inspeccion de Productos Quimicos',
            'inspeccion' => $inspeccion,
            'idCliente'  => $inspeccion['id_cliente'],
            'items'      => self::ITEMS,
            'fotos'      => $this->fotoModel->getByInspeccion($id),
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/productos-quimicos/form', $data),
            'title'   => 'Editar Productos Quimicos',
        ]);
    }

    public function update($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            if ($this->isAutosaveRequest()) {
                return $this->autosaveJsonError('No encontrada', 404);
            }
            return redirect()->to('/inspecciones/productos-quimicos')->with('error', 'No se puede editar');
        }
        if ($r = $this->guardFinalizado($inspeccion, '/inspecciones/productos-quimicos/view/' . $id)) return $r;

        $isAutosave = $this->isAutosaveRequest();
        if ($isAutosave) {
            if ($err = $this->validateAutosaveMinimum()) return $err;
        }

        $userId = session()->get('user_id');
        $detailIds = [];

        $txResult = $this->runTransactional(function () use ($id, $userId, &$detailIds) {
            $this->inspeccionModel->update($id, $this->getInspeccionPostData($userId));
            $detailIds = $this->saveFotos($id);
            return true;
        });

        if ($txResult instanceof \CodeIgniter\HTTP\ResponseInterface) return $txResult;

        if ($this->request->getPost('finalizar')) {
            return $this->finalizar($id);
        }

        if ($isAutosave) {
            return $this->autosaveJsonSuccess((int)$id, ['detail_ids' => $detailIds]);
        }

        return redirect()->to('/inspecciones/productos-quimicos/edit/' . $id)
            ->with('msg', 'Inspeccion actualizada');
    }

    public function view($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/productos-quimicos')->with('error', 'No encontrada');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $data = [
            'title'      => 'Ver Inspeccion de Productos Quimicos',
            'inspeccion' => $inspeccion,
            'cliente'    => $clientModel->find($inspeccion['id_cliente']),
            'consultor'  => $consultantModel->find($inspeccion['id_consultor']),
            'items'      => self::ITEMS,
            'fotos'      => $this->fotoModel->getByInspeccion($id),
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/productos-quimicos/view', $data),
            'title'   => 'Ver Productos Quimicos',
        ]);
    }

    public function finalizar($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/productos-quimicos')->with('error', 'No encontrada');
        }

        if ($r = $this->guardFinalizado($inspeccion, '/inspecciones/productos-quimicos/view/' . $id)) return $r;

        // Calcular % y nivel
        $score = $this->calcularCumplimiento($inspeccion);

        $pdfPath = $this->generarPdfInterno($id, $score);
        if (!$pdfPath) {
            return redirect()->back()->with('error', 'Error al generar PDF');
        }

        $this->inspeccionModel->update($id, [
            'estado'                  => 'completo',
            'ruta_pdf'                => $pdfPath,
            'porcentaje_cumplimiento' => $score['pct'],
            'nivel_riesgo'            => $score['nivel'],
        ]);

        $inspeccion = $this->inspeccionModel->find($id);
        $this->uploadToReportes($inspeccion, $pdfPath);

        $emailResult = InspeccionEmailNotifier::enviar(
            (int) $inspeccion['id_cliente'],
            (int) $inspeccion['id_consultor'],
            'INSPECCION PRODUCTOS QUIMICOS',
            $inspeccion['fecha_inspeccion'],
            $pdfPath,
            (int) $inspeccion['id'],
            'InspeccionProductosQuimicos'
        );
        $msg = 'Inspeccion finalizada y PDF generado.';
        if ($emailResult['success']) {
            $msg .= ' ' . $emailResult['message'];
        } else {
            $msg .= ' (Email no enviado: ' . $emailResult['error'] . ')';
        }

        return redirect()->to('/inspecciones/productos-quimicos/view/' . $id)->with('msg', $msg);
    }

    public function generatePdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/productos-quimicos')->with('error', 'No encontrada');
        }

        $score = $this->calcularCumplimiento($inspeccion);
        $pdfPath = $this->generarPdfInterno($id, $score);
        $this->inspeccionModel->update($id, ['ruta_pdf' => $pdfPath]);

        $fullPath = FCPATH . $pdfPath;
        if (!file_exists($fullPath)) {
            return redirect()->back()->with('error', 'PDF no encontrado');
        }
        $this->servirPdf($fullPath, 'productos_quimicos_' . $id . '.pdf');
        return;
    }

    public function regenerarPdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || ($inspeccion['estado'] ?? '') !== 'completo') {
            return redirect()->to('/inspecciones/productos-quimicos')->with('error', 'Solo se puede regenerar un registro finalizado.');
        }

        $score = $this->calcularCumplimiento($inspeccion);
        $pdfPath = $this->generarPdfInterno($id, $score);
        $this->inspeccionModel->update($id, [
            'ruta_pdf'                => $pdfPath,
            'porcentaje_cumplimiento' => $score['pct'],
            'nivel_riesgo'            => $score['nivel'],
        ]);

        $inspeccion = $this->inspeccionModel->find($id);
        $this->uploadToReportes($inspeccion, $pdfPath);

        return redirect()->to("/inspecciones/productos-quimicos/view/{$id}")->with('msg', 'PDF regenerado exitosamente.');
    }

    public function delete($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/productos-quimicos')->with('error', 'No encontrada');
        }

        foreach ($this->fotoModel->getByInspeccion($id) as $foto) {
            if (!empty($foto['foto']) && file_exists(FCPATH . $foto['foto'])) {
                unlink(FCPATH . $foto['foto']);
            }
        }

        if (!empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) {
            unlink(FCPATH . $inspeccion['ruta_pdf']);
        }

        $this->inspeccionModel->delete($id);
        return redirect()->to('/inspecciones/productos-quimicos')->with('msg', 'Inspeccion eliminada');
    }

    public function enviarEmail($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || $inspeccion['estado'] !== 'completo' || empty($inspeccion['ruta_pdf'])) {
            return redirect()->to("/inspecciones/productos-quimicos/view/{$id}")->with('error', 'Debe estar finalizado con PDF para enviar email.');
        }

        $result = InspeccionEmailNotifier::enviar(
            (int) $inspeccion['id_cliente'],
            (int) $inspeccion['id_consultor'],
            'INSPECCION PRODUCTOS QUIMICOS',
            $inspeccion['fecha_inspeccion'],
            $inspeccion['ruta_pdf'],
            (int) $inspeccion['id'],
            'InspeccionProductosQuimicos'
        );

        if ($result['success']) {
            return redirect()->to("/inspecciones/productos-quimicos/view/{$id}")->with('msg', $result['message']);
        }
        return redirect()->to("/inspecciones/productos-quimicos/view/{$id}")->with('error', $result['error']);
    }

    // ===== METODOS PRIVADOS =====

    private function getInspeccionPostData(int $userId): array
    {
        $data = [
            'id_cliente'            => $this->request->getPost('id_cliente'),
            'id_consultor'          => $userId,
            'fecha_inspeccion'      => $this->request->getPost('fecha_inspeccion'),
            'ubicacion'             => $this->request->getPost('ubicacion'),
            'tiene_guadaniadora'    => $this->request->getPost('tiene_guadaniadora') ? 1 : 0,
            'observaciones_finales' => $this->request->getPost('observaciones_finales'),
        ];

        for ($i = 1; $i <= 17; $i++) {
            $col = 'cal_item_' . str_pad($i, 2, '0', STR_PAD_LEFT);
            $val = $this->request->getPost($col);
            $data[$col] = in_array($val, ['C', 'CP', 'NC', 'NA'], true) ? $val : null;
        }

        return $data;
    }

    private function saveFotos(int $idInspeccion): array
    {
        $obs = $this->request->getPost('foto_obs') ?? [];
        $fotoIds = $this->request->getPost('foto_id') ?? [];

        $existentes = [];
        $existentesPorOrden = [];
        foreach ($this->fotoModel->getByInspeccion($idInspeccion) as $f) {
            $existentes[$f['id']] = $f;
            $existentesPorOrden[(int)$f['orden']] = $f;
        }

        $this->fotoModel->deleteByInspeccion($idInspeccion);

        $dir = FCPATH . 'uploads/inspecciones/productos-quimicos/fotos/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $files = $this->request->getFiles();
        $newIds = [];

        foreach ($obs as $i => $observacion) {
            $existenteId = $fotoIds[$i] ?? null;
            $existente = $existenteId ? ($existentes[$existenteId] ?? null) : null;
            if (!$existente) {
                $existente = $existentesPorOrden[$i + 1] ?? null;
            }

            $fotoPath = $existente['foto'] ?? null;
            if (isset($files['foto_file'][$i]) && $files['foto_file'][$i]->isValid() && !$files['foto_file'][$i]->hasMoved()) {
                $file = $files['foto_file'][$i];
                $fileName = $file->getRandomName();
                $file->move($dir, $fileName);
                $this->comprimirImagen($dir . $fileName);
                $fotoPath = 'uploads/inspecciones/productos-quimicos/fotos/' . $fileName;
            }

            $this->fotoModel->insert([
                'id_inspeccion' => $idInspeccion,
                'orden'         => $i + 1,
                'foto'          => $fotoPath,
                'observacion'   => $observacion,
            ]);
            $newIds[] = $this->fotoModel->getInsertID();
        }

        return $newIds;
    }

    private function calcularCumplimiento(array $inspeccion): array
    {
        $aplicables = 0;
        $suma = 0.0;

        foreach (self::ITEMS as $num => $cfg) {
            if ($cfg['grupo'] === 'condicional' && empty($inspeccion['tiene_guadaniadora'])) {
                continue;
            }
            $col = 'cal_item_' . str_pad($num, 2, '0', STR_PAD_LEFT);
            $cal = $inspeccion[$col] ?? null;
            if ($cal === null || $cal === 'NA') {
                continue;
            }
            $factor = self::FACTORES[$cal] ?? null;
            if ($factor === null) {
                continue;
            }
            $aplicables++;
            $suma += $factor;
        }

        if ($aplicables === 0) {
            return ['pct' => 0.0, 'nivel' => 'bajo', 'aplicables' => 0];
        }

        $pct = round(($suma / $aplicables) * 100, 2);
        $nivel = $pct >= 90 ? 'alto' : ($pct >= 70 ? 'medio' : 'bajo');
        return ['pct' => $pct, 'nivel' => $nivel, 'aplicables' => $aplicables];
    }

    private function generarPdfInterno(int $id, array $score): ?string
    {
        $inspeccion = $this->inspeccionModel->find($id);
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $cliente = $clientModel->find($inspeccion['id_cliente']);
        $consultor = $consultantModel->find($inspeccion['id_consultor']);
        $fotos = $this->fotoModel->getByInspeccion($id);

        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoBase64 = $this->fotoABase64ParaPdf($logoPath);
            }
        }

        foreach ($fotos as &$f) {
            $f['foto_base64'] = '';
            if (!empty($f['foto'])) {
                $fotoPath = FCPATH . $f['foto'];
                if (file_exists($fotoPath)) {
                    $f['foto_base64'] = $this->fotoABase64ParaPdf($fotoPath);
                }
            }
        }

        $data = [
            'inspeccion' => $inspeccion,
            'cliente'    => $cliente,
            'consultor'  => $consultor,
            'items'      => self::ITEMS,
            'fotos'      => $fotos,
            'logoBase64' => $logoBase64,
            'score'      => $score,
        ];

        $html = view('inspecciones/productos-quimicos/pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $pdfDir = 'uploads/inspecciones/productos-quimicos/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) {
            mkdir(FCPATH . $pdfDir, 0755, true);
        }

        $pdfFileName = 'productos_quimicos_' . $id . '_' . date('Ymd_His') . '.pdf';
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
            ->where('id_detailreport', 17)
            ->like('observaciones', 'insp_pq_id:' . $inspeccion['id'])
            ->first();

        $destDir = UPLOADS_PATH . $nitCliente;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $fileName = 'productos_quimicos_' . $inspeccion['id'] . '_' . date('Ymd_His') . '.pdf';
        $destPath = $destDir . '/' . $fileName;
        copy(FCPATH . $pdfPath, $destPath);

        $data = [
            'titulo_reporte'  => 'INSPECCION PRODUCTOS QUIMICOS - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $inspeccion['fecha_inspeccion'],
            'id_detailreport' => 17,
            'id_report_type'  => 6,
            'id_cliente'      => $inspeccion['id_cliente'],
            'estado'          => 'CERRADO',
            'observaciones'   => 'Generado automaticamente desde modulo de inspecciones. insp_pq_id:' . $inspeccion['id'],
            'enlace'          => base_url(UPLOADS_URL_PREFIX . '/' . $nitCliente . '/' . $fileName),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        if ($existente) {
            return $reporteModel->update($existente['id_reporte'], $data);
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        return $reporteModel->save($data);
    }
}
