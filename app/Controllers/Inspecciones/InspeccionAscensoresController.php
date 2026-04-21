<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\InspeccionAscensoresModel;
use App\Models\AscensorDetalleModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use Dompdf\Dompdf;
use App\Libraries\InspeccionEmailNotifier;
use App\Traits\AutosaveJsonTrait;
use App\Traits\ImagenCompresionTrait;

class InspeccionAscensoresController extends BaseController
{
    use AutosaveJsonTrait;
    use ImagenCompresionTrait;
    use \App\Traits\PreventDuplicateBorradorTrait;
    use \App\Traits\InspeccionesTransactionalTrait;

    protected InspeccionAscensoresModel $inspeccionModel;
    protected AscensorDetalleModel $detalleModel;

    public const MARCO_NORMATIVO = 'NTC 5926-1:2012 — Criterios para las inspecciones periódicas, reparaciones, mejoras importantes y modificaciones de ascensores instalados. Esta inspección SST identifica hallazgos de riesgo y NO reemplaza la certificación técnico-mecánica por organismo acreditado ONAC ni el mantenimiento preventivo mensual por empresa especializada.';

    /**
     * Criterios por ascensor agrupados por zona.
     * Cada criterio: opciones válidas + default. Tipos comunes:
     *   SI/NO/NA  → checklist
     *   BUENO/REGULAR/MALO/NA → estado
     */
    public const ZONAS = [
        'cabina' => [
            'label' => 'Cabina',
            'criterios' => [
                'cab_piso_antideslizante'      => ['label' => 'Piso antideslizante',          'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
                'cab_iluminacion_normal'       => ['label' => 'Iluminacion normal',           'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
                'cab_iluminacion_emergencia'   => ['label' => 'Iluminacion de emergencia',    'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
                'cab_ventilacion'              => ['label' => 'Ventilacion',                  'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
                'cab_pasamanos'                => ['label' => 'Pasamanos',                    'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
                'cab_botonera_operativa'       => ['label' => 'Botonera operativa',           'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
                'cab_display_piso'             => ['label' => 'Display de piso',              'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
                'cab_sensor_sobrecarga'        => ['label' => 'Sensor de sobrecarga',         'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
                'cab_placa_capacidad_visible'  => ['label' => 'Placa capacidad visible',      'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
                'cab_intercomunicador_funcional' => ['label' => 'Intercomunicador funcional', 'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
            ],
        ],
        'puertas' => [
            'label' => 'Puertas',
            'criterios' => [
                'pue_alineacion'         => ['label' => 'Alineacion',           'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
                'pue_fotocelula_cortina' => ['label' => 'Fotocelula / cortina', 'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
                'pue_mecanismo_cierre'   => ['label' => 'Mecanismo de cierre',  'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
                'pue_enclavamientos'    => ['label' => 'Enclavamientos',       'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
                'pue_nivelacion_piso'    => ['label' => 'Nivelacion al piso',   'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
            ],
        ],
        'cuarto_maquinas' => [
            'label' => 'Cuarto de maquinas',
            'criterios' => [
                'cm_maquina_tractora'      => ['label' => 'Maquina tractora',        'opciones' => ['BUENO','REGULAR','MALO','NA'], 'default' => 'BUENO'],
                'cm_poleas_cables'         => ['label' => 'Poleas y cables',         'opciones' => ['BUENO','REGULAR','MALO','NA'], 'default' => 'BUENO'],
                'cm_sistema_freno'         => ['label' => 'Sistema de freno',        'opciones' => ['BUENO','REGULAR','MALO','NA'], 'default' => 'BUENO'],
                'cm_tablero_control'       => ['label' => 'Tablero de control',      'opciones' => ['BUENO','REGULAR','MALO','NA'], 'default' => 'BUENO'],
                'cm_iluminacion_ventilacion' => ['label' => 'Iluminacion / ventilacion', 'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
                'cm_orden_aseo'            => ['label' => 'Orden y aseo',            'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
                'cm_extintor_vigente'      => ['label' => 'Extintor vigente',        'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
                'cm_acceso_restringido'    => ['label' => 'Acceso restringido',      'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
            ],
        ],
        'foso' => [
            'label' => 'Foso',
            'criterios' => [
                'foso_amortiguadores'     => ['label' => 'Amortiguadores',        'opciones' => ['BUENO','REGULAR','MALO','NA'], 'default' => 'BUENO'],
                'foso_limpieza'           => ['label' => 'Limpieza',              'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
                'foso_sin_agua_residuos'  => ['label' => 'Sin agua / residuos',   'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
                'foso_interruptor_parada' => ['label' => 'Interruptor de parada', 'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
                'foso_escalera_acceso'    => ['label' => 'Escalera de acceso',    'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
                'foso_iluminacion'        => ['label' => 'Iluminacion',           'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
            ],
        ],
        'shaft' => [
            'label' => 'Shaft / ducto',
            'criterios' => [
                'shaft_integridad_estructural' => ['label' => 'Integridad estructural', 'opciones' => ['BUENO','REGULAR','MALO','NA'], 'default' => 'BUENO'],
                'shaft_estado_guias'           => ['label' => 'Estado de guias',        'opciones' => ['BUENO','REGULAR','MALO','NA'], 'default' => 'BUENO'],
                'shaft_sin_cableado_ajeno'     => ['label' => 'Sin cableado ajeno',     'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
            ],
        ],
        'electricos' => [
            'label' => 'Sistemas electricos / seguridad',
            'criterios' => [
                'elec_puesta_tierra'                 => ['label' => 'Puesta a tierra',                'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
                'elec_limitador_velocidad'           => ['label' => 'Limitador de velocidad',         'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
                'elec_paracaidas'                    => ['label' => 'Paracaidas',                     'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
                'elec_final_carrera'                 => ['label' => 'Final de carrera',               'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
                'elec_protecciones_termomagneticas'  => ['label' => 'Protecciones termomagneticas',   'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
            ],
        ],
        'contrapeso' => [
            'label' => 'Contrapeso',
            'criterios' => [
                'cp_guias_estado'   => ['label' => 'Estado de guias', 'opciones' => ['BUENO','REGULAR','MALO','NA'], 'default' => 'BUENO'],
                'cp_sin_obstaculos' => ['label' => 'Sin obstaculos',  'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
            ],
        ],
        'senalizacion' => [
            'label' => 'Senalizacion',
            'criterios' => [
                'sen_placa_capacidad'         => ['label' => 'Placa capacidad',          'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
                'sen_instrucciones_emergencia' => ['label' => 'Instrucciones emergencia', 'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
                'sen_numero_emergencia'       => ['label' => 'Numero de emergencia',     'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
                'sen_certificado_visible'     => ['label' => 'Certificado visible',      'opciones' => ['SI','NO','NA'], 'default' => 'SI'],
            ],
        ],
        'resultado' => [
            'label' => 'Resultado',
            'criterios' => [
                'estado_general'           => ['label' => 'Estado general',          'opciones' => ['BUENO','REGULAR','MALO','CRITICO'], 'default' => 'BUENO'],
                'certificado_onac_vigente' => ['label' => 'Certificado ONAC vigente','opciones' => ['SI','NO','NA'], 'default' => 'SI'],
            ],
        ],
    ];

    public function __construct()
    {
        $this->inspeccionModel = new InspeccionAscensoresModel();
        $this->detalleModel = new AscensorDetalleModel();
    }

    public function list()
    {
        $inspecciones = $this->inspeccionModel
            ->select('tbl_inspeccion_ascensores.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_ascensores.id_cliente', 'left')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_ascensores.id_consultor', 'left')
            ->orderBy('tbl_inspeccion_ascensores.fecha_inspeccion', 'DESC')
            ->findAll();

        foreach ($inspecciones as &$insp) {
            $insp['total_detalles'] = $this->detalleModel->where('id_inspeccion', $insp['id'])->countAllResults(false);
        }

        $data = [
            'title'        => 'Inspeccion de Ascensores',
            'inspecciones' => $inspecciones,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/ascensores/list', $data),
            'title'   => 'Ascensores',
        ]);
    }

    public function create($idCliente = null)
    {
        $data = [
            'title'      => 'Nueva Inspeccion de Ascensores',
            'inspeccion' => null,
            'idCliente'  => $idCliente,
            'ascensores' => [],
            'zonas'      => self::ZONAS,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/ascensores/form', $data),
            'title'   => 'Nueva Ascensores',
        ]);
    }

    public function store()
    {
        $existing = $this->reuseExistingBorrador($this->inspeccionModel, 'fecha_inspeccion', '/inspecciones/ascensores/edit/');
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
            $detailIds = $this->saveAscensores($idInspeccion);
            return true;
        });

        if ($txResult instanceof \CodeIgniter\HTTP\ResponseInterface) return $txResult;

        if ($isAutosave) {
            return $this->autosaveJsonSuccess($idInspeccion, ['detail_ids' => $detailIds]);
        }

        return redirect()->to('/inspecciones/ascensores/edit/' . $idInspeccion)
            ->with('msg', 'Inspeccion guardada como borrador');
    }

    public function edit($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/ascensores')->with('error', 'Inspeccion no encontrada');
        }

        $data = [
            'title'      => 'Editar Inspeccion de Ascensores',
            'inspeccion' => $inspeccion,
            'idCliente'  => $inspeccion['id_cliente'],
            'ascensores' => $this->detalleModel->getByInspeccion($id),
            'zonas'      => self::ZONAS,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/ascensores/form', $data),
            'title'   => 'Editar Ascensores',
        ]);
    }

    public function update($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            if ($this->isAutosaveRequest()) {
                return $this->autosaveJsonError('No encontrada', 404);
            }
            return redirect()->to('/inspecciones/ascensores')->with('error', 'No se puede editar');
        }

        $isAutosave = $this->isAutosaveRequest();
        if ($isAutosave) {
            if ($err = $this->validateAutosaveMinimum()) return $err;
        }

        $userId = $inspeccion['id_consultor'];
        $detailIds = [];

        $txResult = $this->runTransactional(function () use ($id, $userId, &$detailIds) {
            $this->inspeccionModel->update($id, $this->collectMasterFields($userId, false));
            $detailIds = $this->saveAscensores($id);
            return true;
        });

        if ($txResult instanceof \CodeIgniter\HTTP\ResponseInterface) return $txResult;

        if ($this->request->getPost('finalizar')) {
            return $this->finalizar($id);
        }

        if ($isAutosave) {
            return $this->autosaveJsonSuccess((int)$id, ['detail_ids' => $detailIds]);
        }

        return redirect()->to('/inspecciones/ascensores/edit/' . $id)
            ->with('msg', 'Inspeccion actualizada');
    }

    public function view($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/ascensores')->with('error', 'No encontrada');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $data = [
            'title'      => 'Ver Inspeccion de Ascensores',
            'inspeccion' => $inspeccion,
            'cliente'    => $clientModel->find($inspeccion['id_cliente']),
            'consultor'  => $consultantModel->find($inspeccion['id_consultor']),
            'ascensores' => $this->detalleModel->getByInspeccion($id),
            'zonas'      => self::ZONAS,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/ascensores/view', $data),
            'title'   => 'Ver Ascensores',
        ]);
    }

    public function finalizar($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/ascensores')->with('error', 'No encontrada');
        }

        if ($r = $this->guardFinalizado($inspeccion, '/inspecciones/ascensores/view/' . $id)) return $r;

        // Asegurar marco normativo + total_ascensores antes de generar PDF
        $totalAsc = $this->detalleModel->where('id_inspeccion', $id)->countAllResults();
        $this->inspeccionModel->update($id, [
            'marco_normativo'  => self::MARCO_NORMATIVO,
            'total_ascensores' => $totalAsc,
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
            'INSPECCIÓN ASCENSORES',
            $inspeccion['fecha_inspeccion'],
            $pdfPath,
            (int) $inspeccion['id'],
            'InspeccionAscensores'
        );
        $msg = 'Inspección finalizada y PDF generado.';
        if ($emailResult['success']) {
            $msg .= ' ' . $emailResult['message'];
        } else {
            $msg .= ' (Email no enviado: ' . $emailResult['error'] . ')';
        }

        return redirect()->to('/inspecciones/ascensores/view/' . $id)->with('msg', $msg);
    }

    public function generatePdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/ascensores')->with('error', 'No encontrada');
        }

        $pdfPath = $this->generarPdfInterno($id);
        $this->inspeccionModel->update($id, ['ruta_pdf' => $pdfPath]);
        $fullPath = FCPATH . $pdfPath;
        if (!file_exists($fullPath)) {
            return redirect()->back()->with('error', 'PDF no encontrado');
        }

        $this->servirPdf($fullPath, 'ascensores_' . $id . '.pdf');
        return;
    }

    public function regenerarPdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || ($inspeccion['estado'] ?? '') !== 'completo') {
            return redirect()->to('/inspecciones/ascensores')->with('error', 'Solo se puede regenerar un registro finalizado.');
        }

        $pdfPath = $this->generarPdfInterno($id);
        $this->inspeccionModel->update($id, ['ruta_pdf' => $pdfPath]);

        $inspeccion = $this->inspeccionModel->find($id);
        $this->uploadToReportes($inspeccion, $pdfPath);

        return redirect()->to("/inspecciones/ascensores/view/{$id}")->with('msg', 'PDF regenerado exitosamente.');
    }

    public function delete($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/ascensores')->with('error', 'No encontrada');
        }

        $ascensores = $this->detalleModel->getByInspeccion($id);
        foreach ($ascensores as $asc) {
            if (!empty($asc['foto']) && file_exists(FCPATH . $asc['foto'])) {
                unlink(FCPATH . $asc['foto']);
            }
        }

        if (!empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) {
            unlink(FCPATH . $inspeccion['ruta_pdf']);
        }

        $this->inspeccionModel->delete($id);

        return redirect()->to('/inspecciones/ascensores')->with('msg', 'Inspeccion eliminada');
    }

    public function enviarEmail($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || $inspeccion['estado'] !== 'completo' || empty($inspeccion['ruta_pdf'])) {
            return redirect()->to("/inspecciones/ascensores/view/{$id}")->with('error', 'Debe estar finalizado con PDF para enviar email.');
        }

        $result = InspeccionEmailNotifier::enviar(
            (int) $inspeccion['id_cliente'],
            (int) $inspeccion['id_consultor'],
            'INSPECCIÓN ASCENSORES',
            $inspeccion['fecha_inspeccion'],
            $inspeccion['ruta_pdf'],
            (int) $inspeccion['id'],
            'InspeccionAscensores'
        );

        if ($result['success']) {
            return redirect()->to("/inspecciones/ascensores/view/{$id}")->with('msg', $result['message']);
        }
        return redirect()->to("/inspecciones/ascensores/view/{$id}")->with('error', $result['error']);
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
            'organismo_certificador_onac'        => $this->request->getPost('organismo_certificador_onac'),
            'fecha_ultimo_certificado_onac'      => $this->request->getPost('fecha_ultimo_certificado_onac') ?: null,
            'fecha_vencimiento_certificado_onac' => $this->request->getPost('fecha_vencimiento_certificado_onac') ?: null,
            'certificado_visible_al_publico'     => $this->request->getPost('certificado_visible_al_publico') ?: 'NA',
            'cronograma_mantenimiento_anual'     => $this->request->getPost('cronograma_mantenimiento_anual') ?: 'NA',
            'reportes_tecnicos_disponibles'      => $this->request->getPost('reportes_tecnicos_disponibles') ?: 'NA',
            'total_ascensores'                   => (int)$this->request->getPost('total_ascensores'),
            'recomendaciones_generales'          => $this->request->getPost('recomendaciones_generales'),
        ];

        if ($isInsert) {
            $data['id_consultor'] = $userId;
            $data['estado']       = 'borrador';
        }

        return $data;
    }

    private function saveAscensores(int $idInspeccion): array
    {
        // identificador[] determina cuántas filas; si falta, no hay filas
        $identificadores = $this->request->getPost('item_identificador') ?? [];
        $itemIds         = $this->request->getPost('item_id') ?? [];

        // Preservar fotos por ID y por orden (fallback ante race-condition autosave)
        $existentes = [];
        $existentesPorOrden = [];
        foreach ($this->detalleModel->getByInspeccion($idInspeccion) as $row) {
            $existentes[$row['id']] = $row;
            $existentesPorOrden[(int)$row['orden']] = $row;
        }

        $this->detalleModel->deleteByInspeccion($idInspeccion);

        $dir = FCPATH . 'uploads/inspecciones/ascensores/fotos/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $files = $this->request->getFiles();
        $newIds = [];

        // Lista plana de columnas zona->criterio para iterar
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

            // Foto: preservar la previa si no se subió nueva
            $fotoPath = $existente['foto'] ?? null;
            if (isset($files['item_foto'][$i]) && $files['item_foto'][$i]->isValid() && !$files['item_foto'][$i]->hasMoved()) {
                $file = $files['item_foto'][$i];
                $fileName = $file->getRandomName();
                $file->move($dir, $fileName);
                $this->comprimirImagen($dir . $fileName);
                $fotoPath = 'uploads/inspecciones/ascensores/fotos/' . $fileName;
            }

            $row = [
                'id_inspeccion'      => $idInspeccion,
                'orden'              => $i + 1,
                'identificador'      => $identificador ?: ('Ascensor ' . ($i + 1)),
                'capacidad_kg'       => ($this->request->getPost('item_capacidad_kg') ?? [])[$i] ?? null,
                'capacidad_personas' => ($this->request->getPost('item_capacidad_personas') ?? [])[$i] ?? null,
                'pisos_servidos'     => ($this->request->getPost('item_pisos_servidos') ?? [])[$i] ?? null,
                'tipo'               => ($this->request->getPost('item_tipo') ?? [])[$i] ?? 'NA',
                'foto'               => $fotoPath,
                'observaciones'      => ($this->request->getPost('item_observaciones') ?? [])[$i] ?? null,
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
        $ascensores = $this->detalleModel->getByInspeccion($id);

        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoBase64 = $this->fotoABase64ParaPdf($logoPath);
            }
        }

        foreach ($ascensores as &$asc) {
            $asc['foto_base64'] = '';
            if (!empty($asc['foto'])) {
                $fotoPath = FCPATH . $asc['foto'];
                if (file_exists($fotoPath)) {
                    $asc['foto_base64'] = $this->fotoABase64ParaPdf($fotoPath);
                }
            }
        }

        $data = [
            'inspeccion' => $inspeccion,
            'cliente'    => $cliente,
            'consultor'  => $consultor,
            'ascensores' => $ascensores,
            'zonas'      => self::ZONAS,
            'logoBase64' => $logoBase64,
            'marcoNormativo' => self::MARCO_NORMATIVO,
        ];

        $html = view('inspecciones/ascensores/pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $pdfDir = 'uploads/inspecciones/ascensores/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) {
            mkdir(FCPATH . $pdfDir, 0755, true);
        }

        $pdfFileName = 'ascensores_' . $id . '_' . date('Ymd_His') . '.pdf';
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
            ->where('id_detailreport', 45)
            ->like('observaciones', 'insp_asc_id:' . $inspeccion['id'])
            ->first();

        $destDir = UPLOADS_PATH . $nitCliente;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $fileName = 'ascensores_' . $inspeccion['id'] . '_' . date('Ymd_His') . '.pdf';
        $destPath = $destDir . '/' . $fileName;
        copy(FCPATH . $pdfPath, $destPath);

        $data = [
            'titulo_reporte'  => 'INSPECCION ASCENSORES - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $inspeccion['fecha_inspeccion'],
            'id_detailreport' => 45,
            'id_report_type'  => 6,
            'id_cliente'      => $inspeccion['id_cliente'],
            'estado'          => 'CERRADO',
            'observaciones'   => 'Generado automaticamente desde modulo de inspecciones. insp_asc_id:' . $inspeccion['id'],
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
