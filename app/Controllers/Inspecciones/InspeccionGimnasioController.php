<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\InspeccionGimnasioModel;
use App\Models\GimnasioEvidenciaMaestroModel;
use App\Models\GimnasioDetalleEvidenciaModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use Dompdf\Dompdf;
use App\Libraries\InspeccionEmailNotifier;
use App\Traits\AutosaveJsonTrait;
use App\Traits\ImagenCompresionTrait;

class InspeccionGimnasioController extends BaseController
{
    use AutosaveJsonTrait;
    use ImagenCompresionTrait;
    use \App\Traits\PreventDuplicateBorradorTrait;
    use \App\Traits\InspeccionesTransactionalTrait;

    protected InspeccionGimnasioModel $inspeccionModel;
    protected GimnasioEvidenciaMaestroModel $categoriaModel;
    protected GimnasioDetalleEvidenciaModel $evidenciaModel;

    public const TOTAL_SLOTS = 6;

    /** Checklist GYM-01..GYM-13 — columna -> etiqueta + fundamento. */
    public const CHECKS = [
        'aforo_senalizado'              => ['codigo' => 'GYM-01', 'label' => 'Aforo maximo senalizado',                         'fundamento' => 'Ley 675 + Reglamento interno'],
        'reglamento_visible'            => ['codigo' => 'GYM-02', 'label' => 'Reglamento de uso visible',                       'fundamento' => 'Ley 675 + Criterio SST'],
        'piso_antideslizante'           => ['codigo' => 'GYM-03', 'label' => 'Piso antideslizante / amortiguado',               'fundamento' => 'Res 2400/1979 art 205 + NTC 1700'],
        'ventilacion_adecuada'          => ['codigo' => 'GYM-04', 'label' => 'Ventilacion natural o mecanica adecuada',         'fundamento' => 'Res 2400/1979 art 63'],
        'iluminacion_adecuada'          => ['codigo' => 'GYM-05', 'label' => 'Iluminacion >=300 lux en zona de ejercicio',      'fundamento' => 'Res 2400/1979 art 79'],
        'extintor_vigente_senalizado'   => ['codigo' => 'GYM-06', 'label' => 'Extintor ABC vigente y senalizado',               'fundamento' => 'Decreto 1072/2015 + NTC 1700'],
        'botiquin_visible_dotado'       => ['codigo' => 'GYM-07', 'label' => 'Botiquin primeros auxilios visible y dotado',     'fundamento' => 'Decreto 1072/2015'],
        'plano_evacuacion_visible'      => ['codigo' => 'GYM-08', 'label' => 'Plano de evacuacion visible',                     'fundamento' => 'NFPA 101 + NTC 1700'],
        'espejos_seguros'               => ['codigo' => 'GYM-09', 'label' => 'Espejos anclados, sin bordes vivos',              'fundamento' => 'Res 2400/1979 + Criterio SST'],
        'punto_hidratacion'             => ['codigo' => 'GYM-10', 'label' => 'Punto de hidratacion disponible',                 'fundamento' => 'Res 2400/1979 art 44'],
        'vestier_ordenado'              => ['codigo' => 'GYM-11', 'label' => 'Vestier limpio y con orden',                      'fundamento' => 'Decreto 1072/2015 + Ley 9/1979'],
        'salida_emergencia_libre'       => ['codigo' => 'GYM-12', 'label' => 'Salida de emergencia libre de obstrucciones',     'fundamento' => 'NFPA 101'],
        'pulsador_emergencia_funcional' => ['codigo' => 'GYM-13', 'label' => 'Pulsador de emergencia / intercom funcional',     'fundamento' => 'Criterio SST'],
    ];

    public function __construct()
    {
        $this->inspeccionModel = new InspeccionGimnasioModel();
        $this->categoriaModel  = new GimnasioEvidenciaMaestroModel();
        $this->evidenciaModel  = new GimnasioDetalleEvidenciaModel();
    }

    public function list()
    {
        $inspecciones = $this->inspeccionModel
            ->select('tbl_inspeccion_gimnasio.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_gimnasio.id_cliente', 'left')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_gimnasio.id_consultor', 'left')
            ->orderBy('tbl_inspeccion_gimnasio.fecha_inspeccion', 'DESC')
            ->findAll();

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/gimnasio/list', [
                'title'        => 'Inspeccion Gimnasio (FT-SST-250)',
                'inspecciones' => $inspecciones,
            ]),
            'title'   => 'Gimnasio',
        ]);
    }

    public function create($idCliente = null)
    {
        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/gimnasio/form', [
                'title'         => 'Nueva Inspeccion de Gimnasio',
                'inspeccion'    => null,
                'idCliente'     => $idCliente,
                'checks'        => self::CHECKS,
                'categorias'    => $this->categoriaModel->getActivas(),
                'evidenciaMapa' => array_fill(1, self::TOTAL_SLOTS, null),
                'totalSlots'    => self::TOTAL_SLOTS,
            ]),
            'title'   => 'Nuevo Gimnasio',
        ]);
    }

    public function store()
    {
        $existing = $this->reuseExistingBorrador($this->inspeccionModel, 'fecha_inspeccion', '/inspecciones/gimnasio/edit/');
        if ($existing) return $existing;

        $isAutosave = $this->isAutosaveRequest();
        if (!$isAutosave) {
            if (!$this->validate(['id_cliente' => 'required|integer', 'fecha_inspeccion' => 'required|valid_date'])) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }
        }

        $data = $this->getInspeccionPostData();
        $data['id_consultor'] = session()->get('user_id');
        $data['estado'] = 'borrador';

        $this->inspeccionModel->insert($data);
        $idInspeccion = $this->inspeccionModel->getInsertID();

        $this->procesarEvidencias((int) $idInspeccion);

        if ($isAutosave) return $this->autosaveJsonSuccess($idInspeccion);

        return redirect()->to('/inspecciones/gimnasio/edit/' . $idInspeccion)
            ->with('msg', 'Inspeccion guardada como borrador');
    }

    public function edit($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/gimnasio')->with('error', 'Inspeccion no encontrada');
        }
        if ($r = $this->guardFinalizado($inspeccion, '/inspecciones/gimnasio/view/' . $id)) return $r;

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/gimnasio/form', [
                'title'         => 'Editar Inspeccion de Gimnasio',
                'inspeccion'    => $inspeccion,
                'idCliente'     => $inspeccion['id_cliente'],
                'checks'        => self::CHECKS,
                'categorias'    => $this->categoriaModel->getActivas(),
                'evidenciaMapa' => $this->evidenciaModel->mapaPorSlot((int) $id, self::TOTAL_SLOTS),
                'totalSlots'    => self::TOTAL_SLOTS,
            ]),
            'title'   => 'Editar Gimnasio',
        ]);
    }

    public function update($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            if ($this->isAutosaveRequest()) return $this->autosaveJsonError('No encontrada', 404);
            return redirect()->to('/inspecciones/gimnasio')->with('error', 'No se puede editar');
        }
        if ($r = $this->guardFinalizado($inspeccion, '/inspecciones/gimnasio/view/' . $id)) return $r;

        $data = $this->getInspeccionPostData();
        $this->inspeccionModel->update($id, $data);

        $this->procesarEvidencias((int) $id);

        if ($this->request->getPost('finalizar')) {
            return $this->finalizar($id);
        }

        if ($this->isAutosaveRequest()) return $this->autosaveJsonSuccess((int) $id);

        return redirect()->to('/inspecciones/gimnasio/edit/' . $id)->with('msg', 'Inspeccion actualizada');
    }

    public function view($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/gimnasio')->with('error', 'No encontrada');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/gimnasio/view', [
                'title'         => 'Ver Inspeccion de Gimnasio',
                'inspeccion'    => $inspeccion,
                'cliente'       => $clientModel->find($inspeccion['id_cliente']),
                'consultor'     => $consultantModel->find($inspeccion['id_consultor']),
                'checks'        => self::CHECKS,
                'evidenciaMapa' => $this->evidenciaModel->mapaPorSlot((int) $id, self::TOTAL_SLOTS),
                'totalSlots'    => self::TOTAL_SLOTS,
            ]),
            'title'   => 'Ver Gimnasio',
        ]);
    }

    public function finalizar($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) return redirect()->to('/inspecciones/gimnasio')->with('error', 'No encontrada');

        if (($inspeccion['estado'] ?? '') === 'completo') {
            return redirect()->to('/inspecciones/gimnasio/view/' . $id)
                ->with('msg', 'Esta inspeccion ya fue finalizada anteriormente.');
        }

        $pdfPath = $this->generarPdfInterno($id);
        if (!$pdfPath) return redirect()->back()->with('error', 'Error al generar PDF');

        $this->inspeccionModel->update($id, [
            'estado'          => 'completo',
            'ruta_pdf'        => $pdfPath,
            'marco_normativo' => $this->marcoNormativoCongelado(),
        ]);

        $inspeccion = $this->inspeccionModel->find($id);
        $this->uploadToReportes($inspeccion, $pdfPath);

        $emailResult = InspeccionEmailNotifier::enviar(
            (int) $inspeccion['id_cliente'],
            (int) $inspeccion['id_consultor'],
            'INSPECCION GIMNASIO',
            $inspeccion['fecha_inspeccion'],
            $pdfPath,
            (int) $inspeccion['id'],
            'InspeccionGimnasio'
        );
        $msg = 'Inspeccion finalizada y PDF generado.';
        $msg .= $emailResult['success'] ? ' ' . $emailResult['message'] : ' (Email no enviado: ' . $emailResult['error'] . ')';

        return redirect()->to('/inspecciones/gimnasio/view/' . $id)->with('msg', $msg);
    }

    public function generatePdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) return redirect()->to('/inspecciones/gimnasio')->with('error', 'No encontrada');

        $pdfPath = $this->generarPdfInterno($id);
        $this->inspeccionModel->update($id, ['ruta_pdf' => $pdfPath]);
        $fullPath = FCPATH . $pdfPath;
        if (!file_exists($fullPath)) return redirect()->back()->with('error', 'PDF no encontrado');

        $this->servirPdf($fullPath, 'gimnasio_' . $id . '.pdf');
        return;
    }

    public function regenerarPdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || ($inspeccion['estado'] ?? '') !== 'completo') {
            return redirect()->to('/inspecciones/gimnasio')->with('error', 'Solo se puede regenerar un registro finalizado.');
        }
        $pdfPath = $this->generarPdfInterno($id);
        $this->inspeccionModel->update($id, ['ruta_pdf' => $pdfPath]);
        $inspeccion = $this->inspeccionModel->find($id);
        $this->uploadToReportes($inspeccion, $pdfPath);
        return redirect()->to("/inspecciones/gimnasio/view/{$id}")->with('msg', 'PDF regenerado exitosamente.');
    }

    public function delete($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) return redirect()->to('/inspecciones/gimnasio')->with('error', 'No encontrada');

        // Eliminar evidencias
        $evidencias = $this->evidenciaModel->getByInspeccion((int) $id);
        foreach ($evidencias as $ev) {
            if (!empty($ev['ruta_foto']) && file_exists(FCPATH . $ev['ruta_foto'])) {
                @unlink(FCPATH . $ev['ruta_foto']);
            }
        }
        if (!empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) {
            @unlink(FCPATH . $inspeccion['ruta_pdf']);
        }

        $this->inspeccionModel->delete($id);
        return redirect()->to('/inspecciones/gimnasio')->with('msg', 'Inspeccion eliminada');
    }

    public function enviarEmail($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || $inspeccion['estado'] !== 'completo' || empty($inspeccion['ruta_pdf'])) {
            return redirect()->to("/inspecciones/gimnasio/view/{$id}")->with('error', 'Debe estar finalizado con PDF para enviar email.');
        }
        $result = InspeccionEmailNotifier::enviar(
            (int) $inspeccion['id_cliente'],
            (int) $inspeccion['id_consultor'],
            'INSPECCION GIMNASIO',
            $inspeccion['fecha_inspeccion'],
            $inspeccion['ruta_pdf'],
            (int) $inspeccion['id'],
            'InspeccionGimnasio'
        );
        if ($result['success']) {
            return redirect()->to("/inspecciones/gimnasio/view/{$id}")->with('msg', $result['message']);
        }
        return redirect()->to("/inspecciones/gimnasio/view/{$id}")->with('error', $result['error']);
    }

    // ===== PRIVADOS =====

    private function getInspeccionPostData(): array
    {
        // introduccion / alcance / justificacion NO vienen del form — se generan
        // automaticamente en el PDF (texto fijo segun plantilla FT-SST-250).
        // area_aproximada_m2 se retiro del form (dificil de medir en campo).
        $data = [
            'id_cliente'               => $this->request->getPost('id_cliente'),
            'fecha_inspeccion'         => $this->request->getPost('fecha_inspeccion'),
            'aforo_maximo'             => $this->request->getPost('aforo_maximo') !== null && $this->request->getPost('aforo_maximo') !== '' ? (int) $this->request->getPost('aforo_maximo') : null,
            'horario_operacion'        => $this->request->getPost('horario_operacion'),
            'observaciones_generales'  => $this->request->getPost('observaciones_generales'),
            'recomendaciones_generales' => $this->request->getPost('recomendaciones_generales'),
        ];

        foreach (array_keys(self::CHECKS) as $col) {
            $val = $this->request->getPost($col);
            $data[$col] = in_array($val, ['SI', 'NO', 'NA'], true) ? $val : 'NA';
        }

        return $data;
    }

    /**
     * Procesa los 6 slots de evidencia: para cada slot puede venir archivo nuevo,
     * categoria y descripcion. Si hay archivo se reemplaza la foto previa.
     */
    private function procesarEvidencias(int $idInspeccion): void
    {
        $dir = 'uploads/inspecciones/gimnasio/fotos/';
        if (!is_dir(FCPATH . $dir)) {
            @mkdir(FCPATH . $dir, 0755, true);
        }

        $existente = $this->evidenciaModel->mapaPorSlot($idInspeccion, self::TOTAL_SLOTS);

        for ($slot = 1; $slot <= self::TOTAL_SLOTS; $slot++) {
            $categoria   = $this->request->getPost('slot_categoria_' . $slot);
            $descripcion = $this->request->getPost('slot_descripcion_' . $slot);

            $file = $this->request->getFile('slot_foto_' . $slot);
            $nuevaRuta = null;
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $fileName = $file->getRandomName();
                $file->move(FCPATH . $dir, $fileName);
                $this->comprimirImagen(FCPATH . $dir . $fileName);
                $nuevaRuta = $dir . $fileName;

                // Eliminar foto previa si existia
                if (!empty($existente[$slot]['ruta_foto']) && file_exists(FCPATH . $existente[$slot]['ruta_foto'])) {
                    @unlink(FCPATH . $existente[$slot]['ruta_foto']);
                }
            }

            $row = $existente[$slot];
            if ($row) {
                $upd = [
                    'categoria'   => $categoria ?: $row['categoria'],
                    'descripcion' => $descripcion ?? $row['descripcion'],
                ];
                if ($nuevaRuta) $upd['ruta_foto'] = $nuevaRuta;
                $this->evidenciaModel->update($row['id'], $upd);
            } elseif ($nuevaRuta || $categoria || $descripcion) {
                $this->evidenciaModel->insert([
                    'id_inspeccion' => $idInspeccion,
                    'slot'          => $slot,
                    'categoria'     => $categoria,
                    'descripcion'   => $descripcion,
                    'ruta_foto'     => $nuevaRuta,
                ]);
            }
        }
    }

    private function marcoNormativoCongelado(): string
    {
        return "Ley 675 de 2001; Ley 9 de 1979; Resolucion 2400 de 1979; Decreto 1072 de 2015; "
             . "NTC 1700 (medios de evacuacion); NFPA 101 (Life Safety Code); "
             . "Criterio profesional del consultor SST.";
    }

    private function generarPdfInterno(int $id): ?string
    {
        $inspeccion = $this->inspeccionModel->find($id);
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $cliente = $clientModel->find($inspeccion['id_cliente']);
        $consultor = $consultantModel->find($inspeccion['id_consultor']);

        // Logo en base64
        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) $logoBase64 = $this->fotoABase64ParaPdf($logoPath);
        }

        // Evidencias a base64
        $evidenciaMapa = $this->evidenciaModel->mapaPorSlot($id, self::TOTAL_SLOTS);
        $evidenciasBase64 = [];
        foreach ($evidenciaMapa as $slot => $ev) {
            if (!empty($ev['ruta_foto']) && file_exists(FCPATH . $ev['ruta_foto'])) {
                $evidenciasBase64[$slot] = [
                    'base64'      => $this->fotoABase64ParaPdf(FCPATH . $ev['ruta_foto']),
                    'categoria'   => $ev['categoria'],
                    'descripcion' => $ev['descripcion'],
                ];
            }
        }

        $data = [
            'inspeccion'       => $inspeccion,
            'cliente'          => $cliente,
            'consultor'        => $consultor,
            'checks'           => self::CHECKS,
            'logoBase64'       => $logoBase64,
            'evidenciasBase64' => $evidenciasBase64,
        ];

        $html = view('inspecciones/gimnasio/pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $pdfDir = 'uploads/inspecciones/gimnasio/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) @mkdir(FCPATH . $pdfDir, 0755, true);

        $pdfFileName = 'gimnasio_' . $id . '_' . date('Ymd_His') . '.pdf';
        $pdfPath = $pdfDir . $pdfFileName;

        if (!empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) {
            @unlink(FCPATH . $inspeccion['ruta_pdf']);
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
            ->where('id_detailreport', 49)
            ->like('observaciones', 'insp_gym_id:' . $inspeccion['id'])
            ->first();

        $destDir = UPLOADS_PATH . $nitCliente;
        if (!is_dir($destDir)) mkdir($destDir, 0755, true);

        $fileName = 'gimnasio_' . $inspeccion['id'] . '_' . date('Ymd_His') . '.pdf';
        $destPath = $destDir . '/' . $fileName;
        copy(FCPATH . $pdfPath, $destPath);

        $data = [
            'titulo_reporte'  => 'INSPECCION GIMNASIO - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $inspeccion['fecha_inspeccion'],
            'id_detailreport' => 49,
            'id_report_type'  => 6,
            'id_cliente'      => $inspeccion['id_cliente'],
            'estado'          => 'CERRADO',
            'observaciones'   => 'Generado automaticamente desde modulo de inspecciones. insp_gym_id:' . $inspeccion['id'],
            'enlace'          => base_url(UPLOADS_URL_PREFIX . '/' . $nitCliente . '/' . $fileName),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        if ($existente) return $reporteModel->update($existente['id_reporte'], $data);
        $data['created_at'] = date('Y-m-d H:i:s');
        return $reporteModel->save($data);
    }
}
