<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\InspeccionZonaBbqModel;
use App\Models\ZonaBbqAsadorModel;
use App\Models\ZonaBbqEvidenciaMaestroModel;
use App\Models\ZonaBbqDetalleEvidenciaModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use Dompdf\Dompdf;
use App\Libraries\InspeccionEmailNotifier;
use App\Traits\AutosaveJsonTrait;
use App\Traits\ImagenCompresionTrait;

/**
 * FT-SST-251 — Inspeccion Zona BBQ.
 * Patron corregido (como turco-sauna): view() sin guardFinalizado; edit/update
 * con guard; finalizar con early return y email solo en transicion.
 */
class InspeccionZonaBbqController extends BaseController
{
    use AutosaveJsonTrait;
    use ImagenCompresionTrait;
    use \App\Traits\PreventDuplicateBorradorTrait;
    use \App\Traits\InspeccionesTransactionalTrait;

    protected InspeccionZonaBbqModel $inspeccionModel;
    protected ZonaBbqAsadorModel $asadorModel;
    protected ZonaBbqEvidenciaMaestroModel $categoriaModel;
    protected ZonaBbqDetalleEvidenciaModel $evidenciaModel;

    public const TOTAL_SLOTS = 6;

    public const CHECKS = [
        'reglamento_visible'              => ['codigo' => 'BBQ-01', 'label' => 'Reglamento de uso visible (horario, reserva, supervision menores)', 'fundamento' => 'Ley 675'],
        'extintor_cercano_vigente'        => ['codigo' => 'BBQ-02', 'label' => 'Extintor cercano vigente y senalizado',        'fundamento' => 'NTC 2505 + NFPA 58 + Dec 1072/2015'],
        'distancia_vegetacion_ok'         => ['codigo' => 'BBQ-03', 'label' => 'Asador >=1.5 m de vegetacion / combustible',   'fundamento' => 'NFPA 58 + Criterio SST'],
        'distancia_vivienda_ok'           => ['codigo' => 'BBQ-04', 'label' => 'Asador >=3 m de fachadas/ventanas',            'fundamento' => 'NFPA 58 + Criterio SST'],
        'prueba_fugas_gas_vigente'        => ['codigo' => 'BBQ-05', 'label' => 'Prueba de fugas de gas <=12 meses',            'fundamento' => 'NTC 2505 + NFPA 58'],
        'valvula_corte_accesible'         => ['codigo' => 'BBQ-06', 'label' => 'Valvula de corte accesible y senalizada',      'fundamento' => 'NTC 2505 + NFPA 58'],
        'cilindro_glp_exterior_ventilado' => ['codigo' => 'BBQ-07', 'label' => 'Cilindro GLP en exterior ventilado',           'fundamento' => 'NFPA 58 + Reg. GLP MinEnergia'],
        'ventilacion_adecuada'            => ['codigo' => 'BBQ-08', 'label' => 'Ventilacion adecuada (no espacio confinado)',  'fundamento' => 'Res 2400/1979 + NFPA 58'],
        'punto_agua_accesible'            => ['codigo' => 'BBQ-09', 'label' => 'Punto de agua accesible (manguera/llave)',     'fundamento' => 'Criterio SST'],
        'punto_electrico_gfci'            => ['codigo' => 'BBQ-10', 'label' => 'Punto electrico con GFCI / proteccion humedad','fundamento' => 'RETIE + Criterio SST'],
        'superficie_no_combustible'       => ['codigo' => 'BBQ-11', 'label' => 'Superficie del piso no combustible bajo asador','fundamento' => 'NFPA 58 + Criterio SST'],
        'senal_prohibido_menores_solos'   => ['codigo' => 'BBQ-12', 'label' => 'Senal: prohibido menores sin adulto',          'fundamento' => 'Ley 675 + Criterio SST'],
        'senal_riesgo_quemadura'          => ['codigo' => 'BBQ-13', 'label' => 'Senal: riesgo de quemadura / no tocar',        'fundamento' => 'Criterio SST'],
        'mecheros_fuera_alcance'          => ['codigo' => 'BBQ-14', 'label' => 'Mecheros / encendedores fuera del alcance',    'fundamento' => 'Criterio SST'],
        'recipiente_cenizas_metalico'     => ['codigo' => 'BBQ-15', 'label' => 'Recipiente metalico para cenizas/carbon',      'fundamento' => 'Criterio SST'],
        'alarma_humo_adyacente'           => ['codigo' => 'BBQ-16', 'label' => 'Alarma de humo en zona cubierta adyacente',    'fundamento' => 'NFPA 72 + Ley 1523/2012'],
        'plan_emergencia_documentado'     => ['codigo' => 'BBQ-17', 'label' => 'Plan de emergencia especifico documentado',    'fundamento' => 'Ley 1523/2012'],
    ];

    public const COMBUSTIBLES = [
        'GAS_LP'      => 'Gas LP (GLP)',
        'GAS_NATURAL' => 'Gas natural',
        'LENA'        => 'Leña',
        'CARBON'      => 'Carbon',
        'ELECTRICO'   => 'Electrico',
        'MIXTO'       => 'Mixto',
    ];

    public function __construct()
    {
        $this->inspeccionModel = new InspeccionZonaBbqModel();
        $this->asadorModel     = new ZonaBbqAsadorModel();
        $this->categoriaModel  = new ZonaBbqEvidenciaMaestroModel();
        $this->evidenciaModel  = new ZonaBbqDetalleEvidenciaModel();
    }

    public function list()
    {
        $inspecciones = $this->inspeccionModel
            ->select('tbl_inspeccion_zona_bbq.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_zona_bbq.id_cliente', 'left')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_zona_bbq.id_consultor', 'left')
            ->orderBy('tbl_inspeccion_zona_bbq.fecha_inspeccion', 'DESC')
            ->findAll();

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/zona-bbq/list', [
                'title'        => 'Inspeccion Zona BBQ (FT-SST-251)',
                'inspecciones' => $inspecciones,
            ]),
            'title'   => 'Zona BBQ',
        ]);
    }

    public function create($idCliente = null)
    {
        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/zona-bbq/form', [
                'title'         => 'Nueva Inspeccion Zona BBQ',
                'inspeccion'    => null,
                'idCliente'     => $idCliente,
                'checks'        => self::CHECKS,
                'combustibles'  => self::COMBUSTIBLES,
                'asadores'      => [],
                'categorias'    => $this->categoriaModel->getActivas(),
                'evidenciaMapa' => array_fill(1, self::TOTAL_SLOTS, null),
                'totalSlots'    => self::TOTAL_SLOTS,
            ]),
            'title'   => 'Nuevo Zona BBQ',
        ]);
    }

    public function store()
    {
        $existing = $this->reuseExistingBorrador($this->inspeccionModel, 'fecha_inspeccion', '/inspecciones/zona-bbq/edit/');
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
        $idInspeccion = (int) $this->inspeccionModel->getInsertID();

        $this->sincronizarAsadores($idInspeccion);
        $this->procesarEvidencias($idInspeccion);

        if ($isAutosave) return $this->autosaveJsonSuccess($idInspeccion);

        return redirect()->to('/inspecciones/zona-bbq/edit/' . $idInspeccion)
            ->with('msg', 'Inspeccion guardada como borrador');
    }

    public function edit($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) return redirect()->to('/inspecciones/zona-bbq')->with('error', 'Inspeccion no encontrada');
        if ($r = $this->guardFinalizado($inspeccion, '/inspecciones/zona-bbq/view/' . $id)) return $r;

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/zona-bbq/form', [
                'title'         => 'Editar Inspeccion Zona BBQ',
                'inspeccion'    => $inspeccion,
                'idCliente'     => $inspeccion['id_cliente'],
                'checks'        => self::CHECKS,
                'combustibles'  => self::COMBUSTIBLES,
                'asadores'      => $this->asadorModel->getByInspeccion((int) $id),
                'categorias'    => $this->categoriaModel->getActivas(),
                'evidenciaMapa' => $this->evidenciaModel->mapaPorSlot((int) $id, self::TOTAL_SLOTS),
                'totalSlots'    => self::TOTAL_SLOTS,
            ]),
            'title'   => 'Editar Zona BBQ',
        ]);
    }

    public function update($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            if ($this->isAutosaveRequest()) return $this->autosaveJsonError('No encontrada', 404);
            return redirect()->to('/inspecciones/zona-bbq')->with('error', 'No se puede editar');
        }
        if ($r = $this->guardFinalizado($inspeccion, '/inspecciones/zona-bbq/view/' . $id)) return $r;

        $data = $this->getInspeccionPostData();
        $this->inspeccionModel->update($id, $data);
        $this->sincronizarAsadores((int) $id);
        $this->procesarEvidencias((int) $id);

        if ($this->request->getPost('finalizar')) return $this->finalizar($id);
        if ($this->isAutosaveRequest()) return $this->autosaveJsonSuccess((int) $id);

        return redirect()->to('/inspecciones/zona-bbq/edit/' . $id)->with('msg', 'Inspeccion actualizada');
    }

    public function view($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) return redirect()->to('/inspecciones/zona-bbq')->with('error', 'No encontrada');
        // NO guardFinalizado aqui (view debe mostrar el finalizado).

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/zona-bbq/view', [
                'title'         => 'Ver Inspeccion Zona BBQ',
                'inspeccion'    => $inspeccion,
                'cliente'       => $clientModel->find($inspeccion['id_cliente']),
                'consultor'     => $consultantModel->find($inspeccion['id_consultor']),
                'checks'        => self::CHECKS,
                'combustibles'  => self::COMBUSTIBLES,
                'asadores'      => $this->asadorModel->getByInspeccion((int) $id),
                'evidenciaMapa' => $this->evidenciaModel->mapaPorSlot((int) $id, self::TOTAL_SLOTS),
                'totalSlots'    => self::TOTAL_SLOTS,
            ]),
            'title'   => 'Ver Zona BBQ',
        ]);
    }

    public function finalizar($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) return redirect()->to('/inspecciones/zona-bbq')->with('error', 'No encontrada');

        if (($inspeccion['estado'] ?? '') === 'completo') {
            return redirect()->to('/inspecciones/zona-bbq/view/' . $id)
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
            'INSPECCION ZONA BBQ',
            $inspeccion['fecha_inspeccion'],
            $pdfPath,
            (int) $inspeccion['id'],
            'InspeccionZonaBbq'
        );
        $msg = 'Inspeccion finalizada y PDF generado.';
        $msg .= $emailResult['success']
            ? ' ' . $emailResult['message']
            : ' (Email no enviado: ' . $emailResult['error'] . ')';

        return redirect()->to('/inspecciones/zona-bbq/view/' . $id)->with('msg', $msg);
    }

    public function generatePdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) return redirect()->to('/inspecciones/zona-bbq')->with('error', 'No encontrada');

        $pdfPath = $this->generarPdfInterno($id);
        $this->inspeccionModel->update($id, ['ruta_pdf' => $pdfPath]);
        $fullPath = FCPATH . $pdfPath;
        if (!file_exists($fullPath)) return redirect()->back()->with('error', 'PDF no encontrado');

        $this->servirPdf($fullPath, 'zona_bbq_' . $id . '.pdf');
        return;
    }

    public function regenerarPdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || ($inspeccion['estado'] ?? '') !== 'completo') {
            return redirect()->to('/inspecciones/zona-bbq')->with('error', 'Solo se puede regenerar un registro finalizado.');
        }
        $pdfPath = $this->generarPdfInterno($id);
        $this->inspeccionModel->update($id, ['ruta_pdf' => $pdfPath]);
        $inspeccion = $this->inspeccionModel->find($id);
        $this->uploadToReportes($inspeccion, $pdfPath);
        return redirect()->to("/inspecciones/zona-bbq/view/{$id}")->with('msg', 'PDF regenerado exitosamente.');
    }

    public function delete($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) return redirect()->to('/inspecciones/zona-bbq')->with('error', 'No encontrada');

        foreach ($this->evidenciaModel->getByInspeccion((int) $id) as $ev) {
            if (!empty($ev['ruta_foto']) && file_exists(FCPATH . $ev['ruta_foto'])) {
                @unlink(FCPATH . $ev['ruta_foto']);
            }
        }
        if (!empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) {
            @unlink(FCPATH . $inspeccion['ruta_pdf']);
        }
        $this->inspeccionModel->delete($id);
        return redirect()->to('/inspecciones/zona-bbq')->with('msg', 'Inspeccion eliminada');
    }

    public function enviarEmail($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || $inspeccion['estado'] !== 'completo' || empty($inspeccion['ruta_pdf'])) {
            return redirect()->to("/inspecciones/zona-bbq/view/{$id}")->with('error', 'Debe estar finalizado con PDF para enviar email.');
        }
        $result = InspeccionEmailNotifier::enviar(
            (int) $inspeccion['id_cliente'],
            (int) $inspeccion['id_consultor'],
            'INSPECCION ZONA BBQ',
            $inspeccion['fecha_inspeccion'],
            $inspeccion['ruta_pdf'],
            (int) $inspeccion['id'],
            'InspeccionZonaBbq'
        );
        if ($result['success']) return redirect()->to("/inspecciones/zona-bbq/view/{$id}")->with('msg', $result['message']);
        return redirect()->to("/inspecciones/zona-bbq/view/{$id}")->with('error', $result['error']);
    }

    // ====== PRIVADOS ======

    private function getInspeccionPostData(): array
    {
        $post = fn($k, $d = null) => $this->request->getPost($k) ?? $d;
        $intOrNull = fn($v) => ($v === null || $v === '') ? null : (int) $v;
        $decimalOrNull = fn($v) => ($v === null || $v === '') ? null : $v;

        $data = [
            'id_cliente'                => $post('id_cliente'),
            'fecha_inspeccion'          => $post('fecha_inspeccion'),
            'numero_asadores'           => max(1, (int) $post('numero_asadores', 1)),
            'tipo_combustible'          => array_key_exists($post('tipo_combustible'), self::COMBUSTIBLES) ? $post('tipo_combustible') : 'GAS_LP',
            'aforo_maximo'              => $intOrNull($post('aforo_maximo')),
            'horario_operacion'         => $post('horario_operacion'),
            'tipo_extintor'             => $post('tipo_extintor'),
            'distancia_vegetacion_m'    => $decimalOrNull($post('distancia_vegetacion_m')),
            'distancia_vivienda_m'      => $decimalOrNull($post('distancia_vivienda_m')),
            'observaciones_generales'   => $post('observaciones_generales'),
            'recomendaciones_generales' => $post('recomendaciones_generales'),
            'tiene_sistema_reserva'     => in_array($post('tiene_sistema_reserva'), ['SI','NO','NA'], true) ? $post('tiene_sistema_reserva') : 'NA',
        ];

        foreach (array_keys(self::CHECKS) as $col) {
            $v = $post($col);
            $data[$col] = in_array($v, ['SI','NO','NA'], true) ? $v : 'NA';
        }
        return $data;
    }

    /**
     * Sincroniza asadores desde arrays POST:
     *   asador_numero[], asador_estado_parrilla[], asador_estado_gas[],
     *   asador_fecha_prueba[], asador_obs[], asador_id[] (si existente).
     *
     * Estrategia: borra todos los existentes + re-inserta (simple y robusto).
     */
    private function sincronizarAsadores(int $idInspeccion): void
    {
        $numeros = $this->request->getPost('asador_numero') ?? [];
        if (!is_array($numeros)) return;

        // Borrar los existentes
        $this->asadorModel->where('id_inspeccion', $idInspeccion)->delete();

        $ests  = $this->request->getPost('asador_estado_parrilla') ?? [];
        $gases = $this->request->getPost('asador_estado_gas') ?? [];
        $fechas= $this->request->getPost('asador_fecha_prueba') ?? [];
        $obs   = $this->request->getPost('asador_obs') ?? [];

        $orden = 0;
        foreach ($numeros as $i => $num) {
            $num = trim((string) $num);
            if ($num === '') continue;
            $this->asadorModel->insert([
                'id_inspeccion'            => $idInspeccion,
                'numero'                   => $num,
                'estado_parrilla'          => in_array($ests[$i] ?? '', ['operativo','danado','requiere_mant'], true) ? $ests[$i] : 'operativo',
                'estado_conexion_gas'      => in_array($gases[$i] ?? '', ['operativo','fuga_detectada','sin_conexion','no_aplica'], true) ? $gases[$i] : 'no_aplica',
                'fecha_ultima_prueba_fuga' => !empty($fechas[$i]) ? $fechas[$i] : null,
                'observaciones'            => $obs[$i] ?? null,
                'orden'                    => $orden++,
            ]);
        }
    }

    private function procesarEvidencias(int $idInspeccion): void
    {
        $dir = 'uploads/inspecciones/zona-bbq/fotos/';
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
        return "Ley 675 de 2001; Ley 1523 de 2012; Decreto 1072 de 2015; Res 2400/1979; "
             . "NTC 2505 (suministro de gas); NFPA 58 (Liquefied Petroleum Gas Code); "
             . "NFPA 72 (alarmas humo); Reglamento Tecnico Sector GLP (MinEnergia); "
             . "RETIE (proteccion electrica en zonas humedas); Criterio profesional del consultor SST.";
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
            'checks'           => self::CHECKS,
            'combustibles'     => self::COMBUSTIBLES,
            'asadores'         => $this->asadorModel->getByInspeccion($id),
            'logoBase64'       => $logoBase64,
            'evidenciasBase64' => $evidenciasBase64,
        ];

        $html = view('inspecciones/zona-bbq/pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $pdfDir = 'uploads/inspecciones/zona-bbq/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) @mkdir(FCPATH . $pdfDir, 0755, true);

        $pdfFileName = 'zona_bbq_' . $id . '_' . date('Ymd_His') . '.pdf';
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
            ->where('id_detailreport', 51)
            ->like('observaciones', 'insp_bbq_id:' . $inspeccion['id'])
            ->first();

        $destDir = UPLOADS_PATH . $nitCliente;
        if (!is_dir($destDir)) mkdir($destDir, 0755, true);

        $fileName = 'zona_bbq_' . $inspeccion['id'] . '_' . date('Ymd_His') . '.pdf';
        $destPath = $destDir . '/' . $fileName;
        copy(FCPATH . $pdfPath, $destPath);

        $data = [
            'titulo_reporte'  => 'INSPECCION ZONA BBQ - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $inspeccion['fecha_inspeccion'],
            'id_detailreport' => 51,
            'id_report_type'  => 6,
            'id_cliente'      => $inspeccion['id_cliente'],
            'estado'          => 'CERRADO',
            'observaciones'   => 'Generado automaticamente desde modulo de inspecciones. insp_bbq_id:' . $inspeccion['id'],
            'enlace'          => base_url(UPLOADS_URL_PREFIX . '/' . $nitCliente . '/' . $fileName),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        if ($existente) return $reporteModel->update($existente['id_reporte'], $data);
        $data['created_at'] = date('Y-m-d H:i:s');
        return $reporteModel->save($data);
    }
}
