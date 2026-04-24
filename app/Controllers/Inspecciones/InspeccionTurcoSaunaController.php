<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\InspeccionTurcoSaunaModel;
use App\Models\TurcoSaunaDetalleModel;
use App\Models\TurcoSaunaEvidenciaMaestroModel;
use App\Models\TurcoSaunaDetalleEvidenciaModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use Dompdf\Dompdf;
use App\Libraries\InspeccionEmailNotifier;
use App\Traits\AutosaveJsonTrait;
use App\Traits\ImagenCompresionTrait;

/**
 * FT-SST-249 — Inspeccion Turco + Sauna + Jacuzzi (zonas complementarias).
 *
 * Patron corregido (aprendido del bug del gym):
 *   - view() NO llama guardFinalizado (evita ERR_TOO_MANY_REDIRECTS).
 *   - edit() y update() SI usan guardFinalizado para proteger ediciones.
 *   - finalizar() hace early return si ya es 'completo', y envia email SOLO
 *     en la transicion borrador->completo.
 *   - regenerarPdf() no envia email (ese boton solo regenera PDF).
 */
class InspeccionTurcoSaunaController extends BaseController
{
    use AutosaveJsonTrait;
    use ImagenCompresionTrait;
    use \App\Traits\PreventDuplicateBorradorTrait;
    use \App\Traits\InspeccionesTransactionalTrait;

    protected InspeccionTurcoSaunaModel $inspeccionModel;
    protected TurcoSaunaDetalleModel $detalleModel;
    protected TurcoSaunaEvidenciaMaestroModel $categoriaModel;
    protected TurcoSaunaDetalleEvidenciaModel $evidenciaModel;

    public const TOTAL_SLOTS = 6;
    public const RECINTOS = ['TURCO', 'SAUNA', 'JACUZZI'];

    /** Checklist maestro (comun a todos los recintos aplicados). */
    public const CHECKS_MAESTRO = [
        'reglamento_visible'              => ['codigo' => 'TS-01',  'label' => 'Reglamento de uso visible',                   'fundamento' => 'Ley 675 + Criterio SST'],
        'reglamento_prohibe_menores_solos'=> ['codigo' => 'TS-01b', 'label' => 'Reglamento prohibe menores sin adulto',        'fundamento' => 'Ley 675 + Criterio SST'],
        'aforo_senalizado'                => ['codigo' => 'TS-02',  'label' => 'Aforo maximo senalizado',                      'fundamento' => 'Ley 675 + Reglamento interno'],
        'timbre_emergencia_funcional'     => ['codigo' => 'TS-03',  'label' => 'Timbre/pulsador de emergencia funcional',      'fundamento' => 'NFPA 101 + Criterio SST'],
        'punto_hidratacion'               => ['codigo' => 'TS-04',  'label' => 'Punto de hidratacion cercano',                 'fundamento' => 'Criterio SST'],
        'control_temp_protegido'          => ['codigo' => 'TS-05',  'label' => 'Control de temperatura protegido',             'fundamento' => 'Criterio SST'],
        'piso_antideslizante_acceso'      => ['codigo' => 'TS-07',  'label' => 'Piso antideslizante en acceso y deck',         'fundamento' => 'Res 2400/1979 + Criterio SST'],
        'iluminacion_protegida_humedad'   => ['codigo' => 'TS-08',  'label' => 'Iluminacion protegida para alta humedad',      'fundamento' => 'RETIE + Res 2400/1979'],
        'alarma_humo_zona_adyacente'      => ['codigo' => 'TS-15',  'label' => 'Alarma de humo en area adyacente',             'fundamento' => 'NFPA 72 + Ley 1523/2012'],
        'cronometro_visible'              => ['codigo' => 'TS-16',  'label' => 'Cronometro visible para tiempo de exposicion', 'fundamento' => 'Criterio SST'],
    ];

    /** Checks del detalle por recinto. key => [codigo,label,fundamento,aplica=array de recintos]. */
    public const CHECKS_DETALLE = [
        'piso_antideslizante_interior'      => ['codigo' => 'TS-07b', 'label' => 'Piso antideslizante interior',                'fundamento' => 'Res 2400/1979',        'aplica' => ['TURCO','SAUNA','JACUZZI']],
        'iluminacion_adecuada'              => ['codigo' => 'TS-08b', 'label' => 'Iluminacion interior adecuada',               'fundamento' => 'Res 2400/1979',        'aplica' => ['TURCO','SAUNA','JACUZZI']],
        'aislamiento_electrico_ok'          => ['codigo' => 'TS-AE',  'label' => 'Aislamiento electrico OK',                    'fundamento' => 'RETIE',                 'aplica' => ['TURCO','SAUNA','JACUZZI']],
        'puerta_abre_hacia_fuera'           => ['codigo' => 'TS-06',  'label' => 'Puerta abre hacia afuera',                    'fundamento' => 'NFPA 101',              'aplica' => ['TURCO','SAUNA']],
        'puerta_polarizada_visible_exterior'=> ['codigo' => 'TS-06b', 'label' => 'Puerta polarizada con visual exterior',       'fundamento' => 'Criterio SST',          'aplica' => ['TURCO','SAUNA']],
        'ventilacion_rendijas'              => ['codigo' => 'TS-09',  'label' => 'Sistema de ventilacion / rendijas',           'fundamento' => 'Ley 9/1979',            'aplica' => ['TURCO','SAUNA']],
        'desague_piso_funcional'            => ['codigo' => 'TS-10',  'label' => 'Desague funcional en piso',                   'fundamento' => 'Ley 9/1979',            'aplica' => ['TURCO','JACUZZI']],
        'generador_vapor_mant_vigente'      => ['codigo' => 'TS-11',  'label' => 'Generador de vapor con mantenimiento vigente','fundamento' => 'NTC 2505 + Criterio SST','aplica' => ['TURCO']],
        'hornillo_aislado_asiento'          => ['codigo' => 'TS-12',  'label' => 'Hornillo/piedras aislado del asiento',        'fundamento' => 'Criterio SST',          'aplica' => ['SAUNA']],
        'madera_sin_danos_tornillos'        => ['codigo' => 'TS-13',  'label' => 'Madera sin danos, sin tornillos expuestos',   'fundamento' => 'Criterio SST',          'aplica' => ['SAUNA']],
        'aviso_prohibido_aceites'           => ['codigo' => 'TS-14',  'label' => 'Aviso prohibido aceites/inflamables',         'fundamento' => 'Criterio SST',          'aplica' => ['SAUNA']],
        'tiene_agarraderas_pasamanos'       => ['codigo' => 'TS-17',  'label' => 'Agarraderas / pasamanos de acceso',           'fundamento' => 'Criterio SST',          'aplica' => ['JACUZZI']],
        'gfci_rcd_circuito'                 => ['codigo' => 'TS-18',  'label' => 'GFCI/RCD en circuito electrico',              'fundamento' => 'RETIE + NFPA 70',       'aplica' => ['JACUZZI']],
        'profundidad_senalizada'            => ['codigo' => 'TS-19',  'label' => 'Profundidad senalizada en borde',             'fundamento' => 'Ley 1209 + Criterio SST','aplica' => ['JACUZZI']],
        'cobertura_tapa_fuera_uso'          => ['codigo' => 'TS-20',  'label' => 'Cobertura/tapa cuando no esta en uso',        'fundamento' => 'Criterio SST',          'aplica' => ['JACUZZI']],
        'cartel_prohibiciones_visibles'     => ['codigo' => 'TS-21',  'label' => 'Cartel: prohibido menores sin adulto / alcohol','fundamento' => 'Ley 675 + Criterio SST','aplica' => ['JACUZZI']],
    ];

    public function __construct()
    {
        $this->inspeccionModel = new InspeccionTurcoSaunaModel();
        $this->detalleModel    = new TurcoSaunaDetalleModel();
        $this->categoriaModel  = new TurcoSaunaEvidenciaMaestroModel();
        $this->evidenciaModel  = new TurcoSaunaDetalleEvidenciaModel();
    }

    public function list()
    {
        $inspecciones = $this->inspeccionModel
            ->select('tbl_inspeccion_turco_sauna.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_turco_sauna.id_cliente', 'left')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_turco_sauna.id_consultor', 'left')
            ->orderBy('tbl_inspeccion_turco_sauna.fecha_inspeccion', 'DESC')
            ->findAll();

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/turco-sauna/list', [
                'title'        => 'Inspeccion Turco + Sauna + Jacuzzi (FT-SST-249)',
                'inspecciones' => $inspecciones,
            ]),
            'title'   => 'Turco / Sauna / Jacuzzi',
        ]);
    }

    public function create($idCliente = null)
    {
        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/turco-sauna/form', [
                'title'         => 'Nueva Inspeccion Turco + Sauna + Jacuzzi',
                'inspeccion'    => null,
                'idCliente'     => $idCliente,
                'checksMaestro' => self::CHECKS_MAESTRO,
                'checksDetalle' => self::CHECKS_DETALLE,
                'recintos'      => self::RECINTOS,
                'detalleMapa'   => ['TURCO' => null, 'SAUNA' => null, 'JACUZZI' => null],
                'categorias'    => $this->categoriaModel->getActivas(),
                'evidenciaMapa' => array_fill(1, self::TOTAL_SLOTS, null),
                'totalSlots'    => self::TOTAL_SLOTS,
            ]),
            'title'   => 'Nuevo Turco/Sauna/Jacuzzi',
        ]);
    }

    public function store()
    {
        $existing = $this->reuseExistingBorrador($this->inspeccionModel, 'fecha_inspeccion', '/inspecciones/turco-sauna/edit/');
        if ($existing) return $existing;

        $isAutosave = $this->isAutosaveRequest();
        if (!$isAutosave) {
            if (!$this->validate([
                'id_cliente' => 'required|integer',
                'fecha_inspeccion' => 'required|valid_date',
            ])) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }
        }

        $data = $this->getInspeccionPostData();
        if (!$isAutosave && !$this->alMenosUnRecintoAplica($data)) {
            return redirect()->back()->withInput()->with('errors', ['aplica' => 'Debe marcar al menos un recinto (turco, sauna o jacuzzi).']);
        }

        $data['id_consultor'] = session()->get('user_id');
        $data['estado'] = 'borrador';

        $this->inspeccionModel->insert($data);
        $idInspeccion = (int) $this->inspeccionModel->getInsertID();

        $this->sincronizarDetalles($idInspeccion, $data);
        $this->procesarEvidencias($idInspeccion);

        if ($isAutosave) return $this->autosaveJsonSuccess($idInspeccion);

        return redirect()->to('/inspecciones/turco-sauna/edit/' . $idInspeccion)
            ->with('msg', 'Inspeccion guardada como borrador');
    }

    public function edit($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/turco-sauna')->with('error', 'Inspeccion no encontrada');
        }
        if ($r = $this->guardFinalizado($inspeccion, '/inspecciones/turco-sauna/view/' . $id)) return $r;

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/turco-sauna/form', [
                'title'         => 'Editar Inspeccion Turco + Sauna + Jacuzzi',
                'inspeccion'    => $inspeccion,
                'idCliente'     => $inspeccion['id_cliente'],
                'checksMaestro' => self::CHECKS_MAESTRO,
                'checksDetalle' => self::CHECKS_DETALLE,
                'recintos'      => self::RECINTOS,
                'detalleMapa'   => $this->detalleModel->mapaPorRecinto((int) $id),
                'categorias'    => $this->categoriaModel->getActivas(),
                'evidenciaMapa' => $this->evidenciaModel->mapaPorSlot((int) $id, self::TOTAL_SLOTS),
                'totalSlots'    => self::TOTAL_SLOTS,
            ]),
            'title'   => 'Editar Turco/Sauna/Jacuzzi',
        ]);
    }

    public function update($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            if ($this->isAutosaveRequest()) return $this->autosaveJsonError('No encontrada', 404);
            return redirect()->to('/inspecciones/turco-sauna')->with('error', 'No se puede editar');
        }
        if ($r = $this->guardFinalizado($inspeccion, '/inspecciones/turco-sauna/view/' . $id)) return $r;

        $data = $this->getInspeccionPostData();
        if (!$this->isAutosaveRequest() && !$this->alMenosUnRecintoAplica($data)) {
            return redirect()->back()->withInput()->with('errors', ['aplica' => 'Debe marcar al menos un recinto.']);
        }

        $this->inspeccionModel->update($id, $data);
        $this->sincronizarDetalles((int) $id, $data);
        $this->procesarEvidencias((int) $id);

        if ($this->request->getPost('finalizar')) return $this->finalizar($id);
        if ($this->isAutosaveRequest()) return $this->autosaveJsonSuccess((int) $id);

        return redirect()->to('/inspecciones/turco-sauna/edit/' . $id)->with('msg', 'Inspeccion actualizada');
    }

    public function view($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/turco-sauna')->with('error', 'No encontrada');
        }
        // NO llamamos guardFinalizado aqui: view DEBE mostrar el finalizado.

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/turco-sauna/view', [
                'title'         => 'Ver Inspeccion Turco + Sauna + Jacuzzi',
                'inspeccion'    => $inspeccion,
                'cliente'       => $clientModel->find($inspeccion['id_cliente']),
                'consultor'     => $consultantModel->find($inspeccion['id_consultor']),
                'checksMaestro' => self::CHECKS_MAESTRO,
                'checksDetalle' => self::CHECKS_DETALLE,
                'recintos'      => self::RECINTOS,
                'detalleMapa'   => $this->detalleModel->mapaPorRecinto((int) $id),
                'evidenciaMapa' => $this->evidenciaModel->mapaPorSlot((int) $id, self::TOTAL_SLOTS),
                'totalSlots'    => self::TOTAL_SLOTS,
            ]),
            'title'   => 'Ver Turco/Sauna/Jacuzzi',
        ]);
    }

    public function finalizar($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) return redirect()->to('/inspecciones/turco-sauna')->with('error', 'No encontrada');

        // Early return: si ya esta finalizado, no re-envia email ni re-inserta reporte.
        if (($inspeccion['estado'] ?? '') === 'completo') {
            return redirect()->to('/inspecciones/turco-sauna/view/' . $id)
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

        // Email SOLO en la transicion borrador -> completo (hemos pasado el early return).
        $emailResult = InspeccionEmailNotifier::enviar(
            (int) $inspeccion['id_cliente'],
            (int) $inspeccion['id_consultor'],
            'INSPECCION TURCO / SAUNA / JACUZZI',
            $inspeccion['fecha_inspeccion'],
            $pdfPath,
            (int) $inspeccion['id'],
            'InspeccionTurcoSauna'
        );
        $msg = 'Inspeccion finalizada y PDF generado.';
        $msg .= $emailResult['success']
            ? ' ' . $emailResult['message']
            : ' (Email no enviado: ' . $emailResult['error'] . ')';

        return redirect()->to('/inspecciones/turco-sauna/view/' . $id)->with('msg', $msg);
    }

    public function generatePdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) return redirect()->to('/inspecciones/turco-sauna')->with('error', 'No encontrada');

        $pdfPath = $this->generarPdfInterno($id);
        $this->inspeccionModel->update($id, ['ruta_pdf' => $pdfPath]);
        $fullPath = FCPATH . $pdfPath;
        if (!file_exists($fullPath)) return redirect()->back()->with('error', 'PDF no encontrado');

        $this->servirPdf($fullPath, 'turco_sauna_' . $id . '.pdf');
        return;
    }

    public function regenerarPdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || ($inspeccion['estado'] ?? '') !== 'completo') {
            return redirect()->to('/inspecciones/turco-sauna')->with('error', 'Solo se puede regenerar un registro finalizado.');
        }
        $pdfPath = $this->generarPdfInterno($id);
        $this->inspeccionModel->update($id, ['ruta_pdf' => $pdfPath]);
        $inspeccion = $this->inspeccionModel->find($id);
        $this->uploadToReportes($inspeccion, $pdfPath);
        return redirect()->to("/inspecciones/turco-sauna/view/{$id}")->with('msg', 'PDF regenerado exitosamente.');
    }

    public function delete($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) return redirect()->to('/inspecciones/turco-sauna')->with('error', 'No encontrada');

        foreach ($this->evidenciaModel->getByInspeccion((int) $id) as $ev) {
            if (!empty($ev['ruta_foto']) && file_exists(FCPATH . $ev['ruta_foto'])) {
                @unlink(FCPATH . $ev['ruta_foto']);
            }
        }
        if (!empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) {
            @unlink(FCPATH . $inspeccion['ruta_pdf']);
        }

        $this->inspeccionModel->delete($id);
        return redirect()->to('/inspecciones/turco-sauna')->with('msg', 'Inspeccion eliminada');
    }

    public function enviarEmail($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || $inspeccion['estado'] !== 'completo' || empty($inspeccion['ruta_pdf'])) {
            return redirect()->to("/inspecciones/turco-sauna/view/{$id}")->with('error', 'Debe estar finalizado con PDF para enviar email.');
        }
        $result = InspeccionEmailNotifier::enviar(
            (int) $inspeccion['id_cliente'],
            (int) $inspeccion['id_consultor'],
            'INSPECCION TURCO / SAUNA / JACUZZI',
            $inspeccion['fecha_inspeccion'],
            $inspeccion['ruta_pdf'],
            (int) $inspeccion['id'],
            'InspeccionTurcoSauna'
        );
        if ($result['success']) {
            return redirect()->to("/inspecciones/turco-sauna/view/{$id}")->with('msg', $result['message']);
        }
        return redirect()->to("/inspecciones/turco-sauna/view/{$id}")->with('error', $result['error']);
    }

    // ====== PRIVADOS ======

    private function alMenosUnRecintoAplica(array $data): bool
    {
        return !empty($data['aplica_turco']) || !empty($data['aplica_sauna']) || !empty($data['aplica_jacuzzi']);
    }

    private function getInspeccionPostData(): array
    {
        $post = fn($k, $d = null) => $this->request->getPost($k) ?? $d;
        $intOrNull = fn($v) => ($v === null || $v === '') ? null : (int) $v;

        $data = [
            'id_cliente'        => $post('id_cliente'),
            'fecha_inspeccion'  => $post('fecha_inspeccion'),
            'aplica_turco'      => $post('aplica_turco') ? 1 : 0,
            'aplica_sauna'      => $post('aplica_sauna') ? 1 : 0,
            'aplica_jacuzzi'    => $post('aplica_jacuzzi') ? 1 : 0,
            'aforo_maximo_turco'   => $intOrNull($post('aforo_maximo_turco')),
            'aforo_maximo_sauna'   => $intOrNull($post('aforo_maximo_sauna')),
            'aforo_maximo_jacuzzi' => $intOrNull($post('aforo_maximo_jacuzzi')),
            'horario_operacion'    => $post('horario_operacion'),
            'observaciones_generales'   => $post('observaciones_generales'),
            'recomendaciones_generales' => $post('recomendaciones_generales'),
        ];

        foreach (array_keys(self::CHECKS_MAESTRO) as $col) {
            $val = $post($col);
            $data[$col] = in_array($val, ['SI', 'NO', 'NA'], true) ? $val : 'NA';
        }

        return $data;
    }

    /**
     * Sincroniza filas en tbl_turco_sauna_detalle segun flags aplica_*.
     * Usa UNIQUE KEY (id_inspeccion, recinto): una fila por recinto aplicado.
     * Si un recinto ya no aplica, borra la fila.
     */
    private function sincronizarDetalles(int $idInspeccion, array $master): void
    {
        $mapa = $this->detalleModel->mapaPorRecinto($idInspeccion);
        $aplica = [
            'TURCO'   => !empty($master['aplica_turco']),
            'SAUNA'   => !empty($master['aplica_sauna']),
            'JACUZZI' => !empty($master['aplica_jacuzzi']),
        ];

        foreach (self::RECINTOS as $recinto) {
            $row = $mapa[$recinto];

            if (!$aplica[$recinto]) {
                // No aplica: borrar fila si existia
                if ($row) $this->detalleModel->delete($row['id']);
                continue;
            }

            // Aplica: construir payload solo con los checks relevantes al recinto
            $payload = ['id_inspeccion' => $idInspeccion, 'recinto' => $recinto];
            foreach (self::CHECKS_DETALLE as $col => $info) {
                if (!in_array($recinto, $info['aplica'], true)) continue;
                $name = $recinto . '_' . $col;
                $v = $this->request->getPost($name);
                $payload[$col] = in_array($v, ['SI','NO','NA'], true) ? $v : 'NA';
            }

            // Campos libres por recinto
            foreach (['material_interno','fuente_calor','temperatura_operacion','sistema_ventilacion','observaciones'] as $campo) {
                $payload[$campo] = $this->request->getPost($recinto . '_' . $campo);
            }

            if ($recinto === 'JACUZZI') {
                $p = $this->request->getPost('JACUZZI_profundidad_m');
                $t = $this->request->getPost('JACUZZI_temperatura_agua_c');
                $payload['profundidad_m']     = ($p === null || $p === '') ? null : $p;
                $payload['temperatura_agua_c']= ($t === null || $t === '') ? null : $t;
            }

            if ($row) {
                $this->detalleModel->update($row['id'], $payload);
            } else {
                $this->detalleModel->insert($payload);
            }
        }
    }

    private function procesarEvidencias(int $idInspeccion): void
    {
        $dir = 'uploads/inspecciones/turco-sauna/fotos/';
        if (!is_dir(FCPATH . $dir)) @mkdir(FCPATH . $dir, 0755, true);

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
             . "Ley 1523 de 2012; NFPA 72 (Alarmas humo); NFPA 101 (Life Safety Code); "
             . "NTC 2505 (instalaciones gas, aplica a generador de vapor); RETIE / NFPA 70 "
             . "(proteccion electrica en zonas humedas); Criterio profesional del consultor SST.";
    }

    private function generarPdfInterno(int $id): ?string
    {
        $inspeccion = $this->inspeccionModel->find($id);
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $cliente = $clientModel->find($inspeccion['id_cliente']);
        $consultor = $consultantModel->find($inspeccion['id_consultor']);

        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) $logoBase64 = $this->fotoABase64ParaPdf($logoPath);
        }

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
            'checksMaestro'    => self::CHECKS_MAESTRO,
            'checksDetalle'    => self::CHECKS_DETALLE,
            'recintos'         => self::RECINTOS,
            'detalleMapa'      => $this->detalleModel->mapaPorRecinto($id),
            'logoBase64'       => $logoBase64,
            'evidenciasBase64' => $evidenciasBase64,
        ];

        $html = view('inspecciones/turco-sauna/pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $pdfDir = 'uploads/inspecciones/turco-sauna/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) @mkdir(FCPATH . $pdfDir, 0755, true);

        $pdfFileName = 'turco_sauna_' . $id . '_' . date('Ymd_His') . '.pdf';
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
            ->where('id_detailreport', 50)
            ->like('observaciones', 'insp_tsj_id:' . $inspeccion['id'])
            ->first();

        $destDir = UPLOADS_PATH . $nitCliente;
        if (!is_dir($destDir)) mkdir($destDir, 0755, true);

        $fileName = 'turco_sauna_' . $inspeccion['id'] . '_' . date('Ymd_His') . '.pdf';
        $destPath = $destDir . '/' . $fileName;
        copy(FCPATH . $pdfPath, $destPath);

        $data = [
            'titulo_reporte'  => 'INSPECCION TURCO/SAUNA/JACUZZI - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $inspeccion['fecha_inspeccion'],
            'id_detailreport' => 50,
            'id_report_type'  => 6,
            'id_cliente'      => $inspeccion['id_cliente'],
            'estado'          => 'CERRADO',
            'observaciones'   => 'Generado automaticamente desde modulo de inspecciones. insp_tsj_id:' . $inspeccion['id'],
            'enlace'          => base_url(UPLOADS_URL_PREFIX . '/' . $nitCliente . '/' . $fileName),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        if ($existente) return $reporteModel->update($existente['id_reporte'], $data);
        $data['created_at'] = date('Y-m-d H:i:s');
        return $reporteModel->save($data);
    }
}
