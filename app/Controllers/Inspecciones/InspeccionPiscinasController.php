<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\InspeccionPiscinasModel;
use App\Models\PiscinaDetalleModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use Dompdf\Dompdf;
use App\Libraries\InspeccionEmailNotifier;
use App\Traits\AutosaveJsonTrait;
use App\Traits\ImagenCompresionTrait;

class InspeccionPiscinasController extends BaseController
{
    use AutosaveJsonTrait;
    use ImagenCompresionTrait;
    use \App\Traits\PreventDuplicateBorradorTrait;
    use \App\Traits\InspeccionesTransactionalTrait;

    protected InspeccionPiscinasModel $inspeccionModel;
    protected PiscinaDetalleModel $detalleModel;

    public const MARCO_NORMATIVO = 'Ley 1209 de 2008 y Decreto Reglamentario 554 de 2015 — Normas de seguridad, adecuación de instalaciones y evitamiento de accidentes en piscinas de uso colectivo. Esta inspección SST identifica hallazgos de riesgo para la copropiedad y NO reemplaza la certificación de seguridad de la piscina expedida por la autoridad municipal o distrital competente.';

    public const ZONAS = [
        'cerramientos' => [
            'label' => 'Cerramientos (Art. 5, 14)',
            'criterios' => [
                'cerramiento_perimetral' => ['label' => 'Cerramiento perimetral', 'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
                'puerta_control_acceso'  => ['label' => 'Puerta con control de acceso', 'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
            ],
        ],
        'alarmas' => [
            'label' => 'Alarmas (Art. 6, 11g, 14)',
            'criterios' => [
                'alarma_inmersion'      => ['label' => 'Alarma de inmersion', 'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
                'alarma_80db_funcional' => ['label' => 'Alarma 80dB funcional', 'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
            ],
        ],
        'drenajes' => [
            'label' => 'Drenajes (Art. 12)',
            'criterios' => [
                'drenaje_antiatrapamiento'  => ['label' => 'Drenaje antiatrapamiento', 'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
                'minimo_dos_drenajes'       => ['label' => 'Minimo dos drenajes', 'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
                'sistema_liberacion_vacio'  => ['label' => 'Sistema liberacion de vacio', 'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
            ],
        ],
        'senalizacion' => [
            'label' => 'Senalizacion (Art. 13)',
            'criterios' => [
                'senalizacion_profundidad'     => ['label' => 'Senalizacion de profundidad', 'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
                'baldosas_cambio_profundidad'  => ['label' => 'Baldosas cambio de profundidad', 'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
            ],
        ],
        'emergencia' => [
            'label' => 'Emergencia (Art. 11c, d, f)',
            'criterios' => [
                'botiquin_primeros_auxilios'   => ['label' => 'Botiquin de primeros auxilios', 'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
                'flotadores_circulares_min_2'  => ['label' => 'Flotadores circulares (min 2)', 'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
                'baston_con_gancho'            => ['label' => 'Baston con gancho', 'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
                'citofono_24h'                 => ['label' => 'Citofono 24h', 'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
            ],
        ],
        'avisos' => [
            'label' => 'Avisos (Art. 14)',
            'criterios' => [
                'aviso_menores_12_anos'    => ['label' => 'Aviso menores de 12 anos', 'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
                'aviso_reglamento_visible' => ['label' => 'Aviso reglamento visible', 'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
            ],
        ],
        'agua' => [
            'label' => 'Agua (Art. 11b)',
            'criterios' => [
                'agua_limpia_visualmente'       => ['label' => 'Agua limpia visualmente', 'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
                'registro_cloro_diario'         => ['label' => 'Registro de cloro diario', 'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
                'registro_ph_diario'            => ['label' => 'Registro de pH diario', 'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
                'desinfeccion_quimica_vigente'  => ['label' => 'Desinfeccion quimica vigente', 'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
            ],
        ],
        'equipos' => [
            'label' => 'Equipos',
            'criterios' => [
                'equipo_bombeo_operativo' => ['label' => 'Equipo de bombeo operativo', 'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
                'filtros_operativos'      => ['label' => 'Filtros operativos', 'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
                'dosificador_quimicos'    => ['label' => 'Dosificador de quimicos', 'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
            ],
        ],
        'higiene' => [
            'label' => 'Higiene',
            'criterios' => [
                'duchas_previas_obligatorias' => ['label' => 'Duchas previas obligatorias', 'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
            ],
        ],
        'resultado' => [
            'label' => 'Resultado',
            'criterios' => [
                'estado_general' => ['label' => 'Estado general', 'opciones' => ['BUENO','REGULAR','MALO','CRITICO'], 'default' => 'BUENO'],
            ],
        ],
    ];

    public function __construct()
    {
        $this->inspeccionModel = new InspeccionPiscinasModel();
        $this->detalleModel = new PiscinaDetalleModel();
    }

    public function list()
    {
        $inspecciones = $this->inspeccionModel
            ->select('tbl_inspeccion_piscinas.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_piscinas.id_cliente', 'left')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_piscinas.id_consultor', 'left')
            ->orderBy('tbl_inspeccion_piscinas.fecha_inspeccion', 'DESC')
            ->findAll();

        foreach ($inspecciones as &$insp) {
            $insp['total_detalles'] = $this->detalleModel->where('id_inspeccion', $insp['id'])->countAllResults(false);
        }

        $data = [
            'title'        => 'Inspeccion de Piscinas',
            'inspecciones' => $inspecciones,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/piscinas/list', $data),
            'title'   => 'Piscinas',
        ]);
    }

    public function create($idCliente = null)
    {
        $data = [
            'title'      => 'Nueva Inspeccion de Piscinas',
            'inspeccion' => null,
            'idCliente'  => $idCliente,
            'piscinas'   => [],
            'zonas'      => self::ZONAS,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/piscinas/form', $data),
            'title'   => 'Nueva Piscinas',
        ]);
    }

    public function store()
    {
        $existing = $this->reuseExistingBorrador($this->inspeccionModel, 'fecha_inspeccion', '/inspecciones/piscinas/edit/');
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
            $inspeccionData = $this->collectMasterFields($userId, true);
            $this->inspeccionModel->insert($inspeccionData);
            $idInspeccion = $this->inspeccionModel->getInsertID();
            $detailIds = $this->savePiscinas($idInspeccion);
            return true;
        });

        if ($txResult instanceof \CodeIgniter\HTTP\ResponseInterface) return $txResult;

        if ($isAutosave) {
            return $this->autosaveJsonSuccess($idInspeccion, ['detail_ids' => $detailIds]);
        }

        return redirect()->to('/inspecciones/piscinas/edit/' . $idInspeccion)
            ->with('msg', 'Inspeccion guardada como borrador');
    }

    public function edit($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/piscinas')->with('error', 'Inspeccion no encontrada');
        }

        $data = [
            'title'      => 'Editar Inspeccion de Piscinas',
            'inspeccion' => $inspeccion,
            'idCliente'  => $inspeccion['id_cliente'],
            'piscinas'   => $this->detalleModel->getByInspeccion($id),
            'zonas'      => self::ZONAS,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/piscinas/form', $data),
            'title'   => 'Editar Piscinas',
        ]);
    }

    public function update($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            if ($this->isAutosaveRequest()) {
                return $this->autosaveJsonError('No encontrada', 404);
            }
            return redirect()->to('/inspecciones/piscinas')->with('error', 'No se puede editar');
        }

        $isAutosave = $this->isAutosaveRequest();
        if ($isAutosave) {
            if ($err = $this->validateAutosaveMinimum()) return $err;
        }

        $userId = $inspeccion['id_consultor'];
        $detailIds = [];

        $txResult = $this->runTransactional(function () use ($id, $userId, &$detailIds) {
            $this->inspeccionModel->update($id, $this->collectMasterFields($userId, false));
            $detailIds = $this->savePiscinas($id);
            return true;
        });

        if ($txResult instanceof \CodeIgniter\HTTP\ResponseInterface) return $txResult;

        if ($this->request->getPost('finalizar')) {
            return $this->finalizar($id);
        }

        if ($isAutosave) {
            return $this->autosaveJsonSuccess((int)$id, ['detail_ids' => $detailIds]);
        }

        return redirect()->to('/inspecciones/piscinas/edit/' . $id)
            ->with('msg', 'Inspeccion actualizada');
    }

    public function view($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/piscinas')->with('error', 'No encontrada');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $data = [
            'title'      => 'Ver Inspeccion de Piscinas',
            'inspeccion' => $inspeccion,
            'cliente'    => $clientModel->find($inspeccion['id_cliente']),
            'consultor'  => $consultantModel->find($inspeccion['id_consultor']),
            'piscinas'   => $this->detalleModel->getByInspeccion($id),
            'zonas'      => self::ZONAS,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/piscinas/view', $data),
            'title'   => 'Ver Piscinas',
        ]);
    }

    public function finalizar($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/piscinas')->with('error', 'No encontrada');
        }

        if ($r = $this->guardFinalizado($inspeccion, '/inspecciones/piscinas/view/' . $id)) return $r;

        $total = $this->detalleModel->where('id_inspeccion', $id)->countAllResults();
        $this->inspeccionModel->update($id, [
            'marco_normativo' => self::MARCO_NORMATIVO,
            'total_piscinas'  => $total,
        ]);

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

        $emailResult = InspeccionEmailNotifier::enviar(
            (int) $inspeccion['id_cliente'],
            (int) $inspeccion['id_consultor'],
            'INSPECCIÓN PISCINAS',
            $inspeccion['fecha_inspeccion'],
            $pdfPath,
            (int) $inspeccion['id'],
            'InspeccionPiscinas'
        );
        $msg = 'Inspección finalizada y PDF generado.';
        if ($emailResult['success']) {
            $msg .= ' ' . $emailResult['message'];
        } else {
            $msg .= ' (Email no enviado: ' . $emailResult['error'] . ')';
        }

        return redirect()->to('/inspecciones/piscinas/view/' . $id)->with('msg', $msg);
    }

    public function generatePdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/piscinas')->with('error', 'No encontrada');
        }

        $pdfPath = $this->generarPdfInterno($id);
        $this->inspeccionModel->update($id, ['ruta_pdf' => $pdfPath]);
        $fullPath = FCPATH . $pdfPath;
        if (!file_exists($fullPath)) {
            return redirect()->back()->with('error', 'PDF no encontrado');
        }

        $this->servirPdf($fullPath, 'piscinas_' . $id . '.pdf');
        return;
    }

    public function regenerarPdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || ($inspeccion['estado'] ?? '') !== 'completo') {
            return redirect()->to('/inspecciones/piscinas')->with('error', 'Solo se puede regenerar un registro finalizado.');
        }

        $pdfPath = $this->generarPdfInterno($id);
        $this->inspeccionModel->update($id, ['ruta_pdf' => $pdfPath]);

        $inspeccion = $this->inspeccionModel->find($id);
        $this->uploadToReportes($inspeccion, $pdfPath);

        return redirect()->to("/inspecciones/piscinas/view/{$id}")->with('msg', 'PDF regenerado exitosamente.');
    }

    public function delete($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/piscinas')->with('error', 'No encontrada');
        }

        $piscinas = $this->detalleModel->getByInspeccion($id);
        foreach ($piscinas as $p) {
            if (!empty($p['foto']) && file_exists(FCPATH . $p['foto'])) {
                unlink(FCPATH . $p['foto']);
            }
        }

        if (!empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) {
            unlink(FCPATH . $inspeccion['ruta_pdf']);
        }

        $this->inspeccionModel->delete($id);

        return redirect()->to('/inspecciones/piscinas')->with('msg', 'Inspeccion eliminada');
    }

    public function enviarEmail($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || $inspeccion['estado'] !== 'completo' || empty($inspeccion['ruta_pdf'])) {
            return redirect()->to("/inspecciones/piscinas/view/{$id}")->with('error', 'Debe estar finalizado con PDF para enviar email.');
        }

        $result = InspeccionEmailNotifier::enviar(
            (int) $inspeccion['id_cliente'],
            (int) $inspeccion['id_consultor'],
            'INSPECCIÓN PISCINAS',
            $inspeccion['fecha_inspeccion'],
            $inspeccion['ruta_pdf'],
            (int) $inspeccion['id'],
            'InspeccionPiscinas'
        );

        if ($result['success']) {
            return redirect()->to("/inspecciones/piscinas/view/{$id}")->with('msg', $result['message']);
        }
        return redirect()->to("/inspecciones/piscinas/view/{$id}")->with('error', $result['error']);
    }

    // ===== PRIVADOS =====

    private function collectMasterFields($userId, bool $isInsert): array
    {
        $data = [
            'id_cliente'                         => $this->request->getPost('id_cliente'),
            'fecha_inspeccion'                   => $this->request->getPost('fecha_inspeccion'),
            'empresa_mantenimiento'              => $this->request->getPost('empresa_mantenimiento'),
            'nit_empresa_mantenimiento'          => $this->request->getPost('nit_empresa_mantenimiento'),
            'contacto_empresa_mantenimiento'     => $this->request->getPost('contacto_empresa_mantenimiento'),
            'certificado_municipal_vigente'      => $this->request->getPost('certificado_municipal_vigente') ?: 'NA',
            'fecha_vencimiento_certificado_mpio' => $this->request->getPost('fecha_vencimiento_certificado_mpio') ?: null,
            'total_piscinas'                     => (int)$this->request->getPost('total_piscinas'),
            'recomendaciones_generales'          => $this->request->getPost('recomendaciones_generales'),
        ];

        if ($isInsert) {
            $data['id_consultor'] = $userId;
            $data['estado']       = 'borrador';
        }

        return $data;
    }

    private function savePiscinas(int $idInspeccion): array
    {
        $identificadores = $this->request->getPost('item_identificador') ?? [];
        $itemIds         = $this->request->getPost('item_id') ?? [];

        $existentes = [];
        $existentesPorOrden = [];
        foreach ($this->detalleModel->getByInspeccion($idInspeccion) as $row) {
            $existentes[$row['id']] = $row;
            $existentesPorOrden[(int)$row['orden']] = $row;
        }

        $this->detalleModel->deleteByInspeccion($idInspeccion);

        $dir = FCPATH . 'uploads/inspecciones/piscinas/fotos/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $files = $this->request->getFiles();
        $newIds = [];

        $allCriterios = [];
        foreach (self::ZONAS as $zKey => $zCfg) {
            foreach ($zCfg['criterios'] as $cKey => $cCfg) {
                $allCriterios[$cKey] = $cCfg;
            }
        }

        foreach ($identificadores as $i => $identificador) {
            $existenteId = $itemIds[$i] ?? null;
            $existente   = $existenteId ? ($existentes[$existenteId] ?? null) : null;
            if (!$existente) {
                $existente = $existentesPorOrden[$i + 1] ?? null;
            }

            $fotoPath = $existente['foto'] ?? null;
            if (isset($files['item_foto'][$i]) && $files['item_foto'][$i]->isValid() && !$files['item_foto'][$i]->hasMoved()) {
                $file = $files['item_foto'][$i];
                $fileName = $file->getRandomName();
                $file->move($dir, $fileName);
                $this->comprimirImagen($dir . $fileName);
                $fotoPath = 'uploads/inspecciones/piscinas/fotos/' . $fileName;
            }

            $row = [
                'id_inspeccion'         => $idInspeccion,
                'orden'                 => $i + 1,
                'identificador'         => $identificador ?: ('Piscina ' . ($i + 1)),
                'tipo'                  => ($this->request->getPost('item_tipo') ?? [])[$i] ?? 'ADULTOS',
                'profundidad_minima_m'  => ($this->request->getPost('item_profundidad_minima_m') ?? [])[$i] ?? null,
                'profundidad_maxima_m'  => ($this->request->getPost('item_profundidad_maxima_m') ?? [])[$i] ?? null,
                'foto'                  => $fotoPath,
                'observaciones'         => ($this->request->getPost('item_observaciones') ?? [])[$i] ?? null,
            ];

            foreach ($allCriterios as $cKey => $cCfg) {
                $arr = $this->request->getPost('item_' . $cKey) ?? [];
                $row[$cKey] = $arr[$i] ?? $cCfg['default'];
            }

            $this->detalleModel->insert($row);
            $newIds[] = $this->detalleModel->getInsertID();
        }

        return $newIds;
    }

    private function generarPdfInterno(int $id): ?string
    {
        $inspeccion = $this->inspeccionModel->find($id);
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $cliente = $clientModel->find($inspeccion['id_cliente']);
        $consultor = $consultantModel->find($inspeccion['id_consultor']);
        $piscinas = $this->detalleModel->getByInspeccion($id);

        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoBase64 = $this->fotoABase64ParaPdf($logoPath);
            }
        }

        foreach ($piscinas as &$p) {
            $p['foto_base64'] = '';
            if (!empty($p['foto'])) {
                $fotoPath = FCPATH . $p['foto'];
                if (file_exists($fotoPath)) {
                    $p['foto_base64'] = $this->fotoABase64ParaPdf($fotoPath);
                }
            }
        }

        $data = [
            'inspeccion'     => $inspeccion,
            'cliente'        => $cliente,
            'consultor'      => $consultor,
            'piscinas'       => $piscinas,
            'zonas'          => self::ZONAS,
            'logoBase64'     => $logoBase64,
            'marcoNormativo' => self::MARCO_NORMATIVO,
        ];

        $html = view('inspecciones/piscinas/pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $pdfDir = 'uploads/inspecciones/piscinas/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) {
            mkdir(FCPATH . $pdfDir, 0755, true);
        }

        $pdfFileName = 'piscinas_' . $id . '_' . date('Ymd_His') . '.pdf';
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
            ->where('id_detailreport', 46)
            ->like('observaciones', 'insp_pis_id:' . $inspeccion['id'])
            ->first();

        $destDir = UPLOADS_PATH . $nitCliente;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $fileName = 'piscinas_' . $inspeccion['id'] . '_' . date('Ymd_His') . '.pdf';
        $destPath = $destDir . '/' . $fileName;
        copy(FCPATH . $pdfPath, $destPath);

        $data = [
            'titulo_reporte'  => 'INSPECCION PISCINAS - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $inspeccion['fecha_inspeccion'],
            'id_detailreport' => 46,
            'id_report_type'  => 6,
            'id_cliente'      => $inspeccion['id_cliente'],
            'estado'          => 'CERRADO',
            'observaciones'   => 'Generado automaticamente desde modulo de inspecciones. insp_pis_id:' . $inspeccion['id'],
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
