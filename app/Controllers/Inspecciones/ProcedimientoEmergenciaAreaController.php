<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\ProcedimientoEmergenciaAreaModel;
use App\Models\ProcedimientoEmergenciaEscenarioModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use App\Libraries\EmergencyProcedureIAService;
use App\Libraries\InspeccionEmailNotifier;
use Dompdf\Dompdf;

class ProcedimientoEmergenciaAreaController extends BaseController
{
    use \App\Traits\InspeccionesTransactionalTrait;

    protected ProcedimientoEmergenciaAreaModel $areaModel;
    protected ProcedimientoEmergenciaEscenarioModel $escenarioModel;

    public const MARCO_NORMATIVO = 'Decreto 1072 de 2015 (SG-SST), Ley 1523 de 2012 (gestión del riesgo de desastres), NTC 1700 y — cuando aplique al área — Ley 1209 de 2008 y Resolución 234 de 2026 del Ministerio de Salud. Este procedimiento es un instrumento operativo de reacción en emergencia y complementa — no reemplaza — el Plan de Emergencia del inmueble.';

    public const AREAS = [
        'PISCINA'     => 'Piscina / zona húmeda',
        'BANO_TURCO'  => 'Baño turco',
        'SAUNA'       => 'Sauna',
        'GYM'         => 'Gimnasio',
        'ZONA_BBQ'    => 'Zona BBQ',
    ];

    public function __construct()
    {
        $this->areaModel      = new ProcedimientoEmergenciaAreaModel();
        $this->escenarioModel = new ProcedimientoEmergenciaEscenarioModel();
    }

    // ==================================================================
    // CRUD
    // ==================================================================

    public function list()
    {
        $rows = $this->areaModel
            ->select('tbl_procedimiento_emergencia_area.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_procedimiento_emergencia_area.id_cliente', 'left')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_procedimiento_emergencia_area.id_consultor', 'left')
            ->orderBy('tbl_procedimiento_emergencia_area.updated_at', 'DESC')
            ->findAll();

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/procedimiento-emergencia-area/list', [
                'title'           => 'Procedimientos de Emergencia por Área',
                'procedimientos'  => $rows,
                'areasLabels'     => self::AREAS,
            ]),
            'title' => 'Procedimientos Emergencia',
        ]);
    }

    public function create($idCliente = null)
    {
        $area = strtoupper($this->request->getGet('area') ?? 'PISCINA');
        if (!isset(self::AREAS[$area])) $area = 'PISCINA';

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/procedimiento-emergencia-area/form', [
                'title'       => 'Nuevo Procedimiento de Emergencia por Área',
                'procedimiento' => null,
                'escenarios'  => [],
                'idCliente'   => $idCliente,
                'area'        => $area,
                'areasLabels' => self::AREAS,
                'escenariosCatalogo' => EmergencyProcedureIAService::escenariosPorArea($area),
            ]),
            'title' => 'Nuevo Procedimiento',
        ]);
    }

    public function store()
    {
        $errors = [];
        $idCliente = (int)$this->request->getPost('id_cliente');
        $fecha     = $this->request->getPost('fecha_elaboracion');
        $area      = strtoupper($this->request->getPost('area') ?: 'PISCINA');

        if ($idCliente <= 0) $errors[] = 'Cliente requerido';
        if (!$fecha) $errors[] = 'Fecha requerida';
        if (!isset(self::AREAS[$area])) $errors[] = 'Área inválida';

        if (!empty($errors)) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $userId = session()->get('user_id');
        $data = $this->collectMasterFields($userId, true);
        $this->areaModel->insert($data);
        $id = $this->areaModel->getInsertID();

        // Pre-cargar los escenarios del catálogo para esta área
        $catalogo = EmergencyProcedureIAService::escenariosPorArea($area);
        foreach ($catalogo as $i => $esc) {
            $this->escenarioModel->insert([
                'id_procedimiento'       => $id,
                'orden'                  => $i + 1,
                'escenario_codigo'       => $esc['codigo'],
                'escenario_nombre'       => $esc['nombre'],
                'que_hacer'              => '',
                'que_no_hacer'           => '',
                'cuando'                 => '',
                'quien'                  => '',
                'recursos'               => '',
                'generado_con_ia'        => 0,
                'aprobado_por_consultor' => 0,
            ]);
        }

        return redirect()->to('/inspecciones/procedimiento-emergencia-area/edit/' . $id)
            ->with('msg', 'Procedimiento creado. Use el botón "IA" por escenario para autocompletar.');
    }

    public function edit($id)
    {
        $procedimiento = $this->areaModel->find($id);
        if (!$procedimiento) return redirect()->to('/inspecciones/procedimiento-emergencia-area')->with('error', 'No encontrada');
        if ($r = $this->guardFinalizado($procedimiento, '/inspecciones/procedimiento-emergencia-area/view/' . $id)) return $r;

        $escenarios = $this->escenarioModel->getByProcedimiento($id);

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/procedimiento-emergencia-area/form', [
                'title'         => 'Editar Procedimiento de Emergencia',
                'procedimiento' => $procedimiento,
                'escenarios'    => $escenarios,
                'idCliente'     => $procedimiento['id_cliente'],
                'area'          => $procedimiento['area'],
                'areasLabels'   => self::AREAS,
                'escenariosCatalogo' => EmergencyProcedureIAService::escenariosPorArea($procedimiento['area']),
            ]),
            'title' => 'Editar Procedimiento',
        ]);
    }

    public function update($id)
    {
        $procedimiento = $this->areaModel->find($id);
        if (!$procedimiento) return redirect()->to('/inspecciones/procedimiento-emergencia-area')->with('error', 'No encontrada');
        if ($r = $this->guardFinalizado($procedimiento, '/inspecciones/procedimiento-emergencia-area/view/' . $id)) return $r;

        $userId = $procedimiento['id_consultor'];
        $this->areaModel->update($id, $this->collectMasterFields($userId, false));

        $this->saveEscenarios((int)$id);

        if ($this->request->getPost('finalizar')) return $this->finalizar($id);

        return redirect()->to('/inspecciones/procedimiento-emergencia-area/edit/' . $id)
            ->with('msg', 'Procedimiento actualizado');
    }

    public function view($id)
    {
        $procedimiento = $this->areaModel->find($id);
        if (!$procedimiento) return redirect()->to('/inspecciones/procedimiento-emergencia-area')->with('error', 'No encontrada');

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/procedimiento-emergencia-area/view', [
                'title'         => 'Ver Procedimiento de Emergencia',
                'procedimiento' => $procedimiento,
                'cliente'       => $clientModel->find($procedimiento['id_cliente']),
                'consultor'     => $consultantModel->find($procedimiento['id_consultor']),
                'escenarios'    => $this->escenarioModel->getByProcedimiento($id),
                'areasLabels'   => self::AREAS,
            ]),
            'title' => 'Ver Procedimiento',
        ]);
    }

    public function finalizar($id)
    {
        $procedimiento = $this->areaModel->find($id);
        if (!$procedimiento) return redirect()->to('/inspecciones/procedimiento-emergencia-area')->with('error', 'No encontrada');

        if (($procedimiento['estado'] ?? '') === 'completo') {
            return redirect()->to('/inspecciones/procedimiento-emergencia-area/view/' . $id)
                ->with('msg', 'Este procedimiento ya fue finalizado anteriormente.');
        }

        // Validación: al menos un escenario aprobado con contenido
        $escenarios = $this->escenarioModel->getByProcedimiento($id);
        $aprobados = 0;
        foreach ($escenarios as $e) {
            if ((int)$e['aprobado_por_consultor'] === 1 && !empty($e['que_hacer'])) $aprobados++;
        }
        if ($aprobados === 0) {
            return redirect()->back()->with('error', 'Debe aprobar al menos un escenario con contenido antes de finalizar.');
        }

        $this->areaModel->update($id, ['marco_normativo' => self::MARCO_NORMATIVO]);

        $pdfPath = $this->generarPdfInterno($id);
        if (!$pdfPath) return redirect()->back()->with('error', 'Error al generar PDF');

        $this->areaModel->update($id, ['estado' => 'completo', 'ruta_pdf' => $pdfPath]);

        $procedimiento = $this->areaModel->find($id);
        $this->uploadToReportes($procedimiento, $pdfPath);

        $emailResult = InspeccionEmailNotifier::enviar(
            (int) $procedimiento['id_cliente'],
            (int) $procedimiento['id_consultor'],
            'PROCEDIMIENTO EMERGENCIA ' . (self::AREAS[$procedimiento['area']] ?? $procedimiento['area']),
            $procedimiento['fecha_elaboracion'],
            $pdfPath,
            (int) $procedimiento['id'],
            'ProcedimientoEmergenciaArea'
        );

        $msg = 'Procedimiento finalizado y PDF generado.';
        if ($emailResult['success']) $msg .= ' ' . $emailResult['message'];
        else $msg .= ' (Email no enviado: ' . $emailResult['error'] . ')';

        return redirect()->to('/inspecciones/procedimiento-emergencia-area/view/' . $id)->with('msg', $msg);
    }

    public function generatePdf($id)
    {
        $procedimiento = $this->areaModel->find($id);
        if (!$procedimiento) return redirect()->to('/inspecciones/procedimiento-emergencia-area')->with('error', 'No encontrada');

        $pdfPath = $this->generarPdfInterno($id);
        $this->areaModel->update($id, ['ruta_pdf' => $pdfPath]);
        $fullPath = FCPATH . $pdfPath;
        if (!file_exists($fullPath)) return redirect()->back()->with('error', 'PDF no encontrado');

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="procedimiento_' . $id . '.pdf"');
        readfile($fullPath);
        exit;
    }

    public function regenerarPdf($id)
    {
        $procedimiento = $this->areaModel->find($id);
        if (!$procedimiento || ($procedimiento['estado'] ?? '') !== 'completo') {
            return redirect()->to('/inspecciones/procedimiento-emergencia-area')->with('error', 'Solo se puede regenerar un registro finalizado.');
        }

        $pdfPath = $this->generarPdfInterno($id);
        $this->areaModel->update($id, ['ruta_pdf' => $pdfPath]);
        $procedimiento = $this->areaModel->find($id);
        $this->uploadToReportes($procedimiento, $pdfPath);

        return redirect()->to("/inspecciones/procedimiento-emergencia-area/view/{$id}")->with('msg', 'PDF regenerado exitosamente.');
    }

    public function delete($id)
    {
        $procedimiento = $this->areaModel->find($id);
        if (!$procedimiento) return redirect()->to('/inspecciones/procedimiento-emergencia-area')->with('error', 'No encontrada');

        if (!empty($procedimiento['ruta_pdf']) && file_exists(FCPATH . $procedimiento['ruta_pdf'])) {
            unlink(FCPATH . $procedimiento['ruta_pdf']);
        }

        $this->areaModel->delete($id); // cascada borra escenarios

        return redirect()->to('/inspecciones/procedimiento-emergencia-area')->with('msg', 'Procedimiento eliminado');
    }

    public function enviarEmail($id)
    {
        $procedimiento = $this->areaModel->find($id);
        if (!$procedimiento || $procedimiento['estado'] !== 'completo' || empty($procedimiento['ruta_pdf'])) {
            return redirect()->to("/inspecciones/procedimiento-emergencia-area/view/{$id}")
                ->with('error', 'Debe estar finalizado con PDF para enviar email.');
        }

        $result = InspeccionEmailNotifier::enviar(
            (int) $procedimiento['id_cliente'],
            (int) $procedimiento['id_consultor'],
            'PROCEDIMIENTO EMERGENCIA ' . (self::AREAS[$procedimiento['area']] ?? $procedimiento['area']),
            $procedimiento['fecha_elaboracion'],
            $procedimiento['ruta_pdf'],
            (int) $procedimiento['id'],
            'ProcedimientoEmergenciaArea'
        );

        if ($result['success']) {
            return redirect()->to("/inspecciones/procedimiento-emergencia-area/view/{$id}")->with('msg', $result['message']);
        }
        return redirect()->to("/inspecciones/procedimiento-emergencia-area/view/{$id}")->with('error', $result['error']);
    }

    // ==================================================================
    // IA — generar escenario con Haiku
    // ==================================================================

    /**
     * AJAX: recibe id_escenario + contexto, llama a Haiku y devuelve el JSON.
     * El frontend decide si persistirlo vía el formulario normal.
     *
     * POST: id_escenario
     * Opcional: observaciones_contexto_extra
     */
    public function generarEscenarioIA($id)
    {
        $procedimiento = $this->areaModel->find($id);
        if (!$procedimiento) {
            return $this->response->setStatusCode(404)->setJSON(['ok' => false, 'error' => 'Procedimiento no encontrado']);
        }

        $idEscenario = (int)$this->request->getPost('id_escenario');
        $escenario = $this->escenarioModel->find($idEscenario);
        if (!$escenario || (int)$escenario['id_procedimiento'] !== (int)$id) {
            return $this->response->setStatusCode(404)->setJSON(['ok' => false, 'error' => 'Escenario no encontrado']);
        }

        $clientModel = new ClientModel();
        $cliente = $clientModel->find($procedimiento['id_cliente']);

        $contexto = [
            'cliente'             => $cliente['nombre_cliente'] ?? '',
            'direccion'           => $cliente['direccion'] ?? '',
            'tipo_copropiedad'    => $cliente['tipo_copropiedad'] ?? '',
            'area_descriptiva'    => $procedimiento['nombre_area_descriptivo'] ?? '',
            'responsable_area'    => $procedimiento['responsable_area_nombre'] ?? '',
            'cargo_responsable'   => $procedimiento['responsable_area_cargo'] ?? '',
            'contacto_responsable'=> $procedimiento['responsable_area_contacto'] ?? '',
            'horario_operacion'   => $procedimiento['horario_operacion'] ?? '',
            'aforo_maximo'        => $procedimiento['aforo_maximo'] ?? '',
            'telefonos_emergencia'=> $procedimiento['telefonos_emergencia'] ?? '',
            'recursos_disponibles'=> $procedimiento['recursos_disponibles'] ?? '',
        ];

        $svc = new EmergencyProcedureIAService();
        $r = $svc->generarEscenario($procedimiento['area'], $escenario['escenario_nombre'], $contexto);

        if (!$r['ok']) {
            return $this->response->setStatusCode(502)->setJSON(['ok' => false, 'error' => $r['error'] ?? 'Error IA']);
        }

        // Persistir automáticamente el resultado devuelto (el consultor después puede editar y aprobar)
        $this->escenarioModel->update($idEscenario, [
            'que_hacer'       => $r['data']['que_hacer'] ?? '',
            'que_no_hacer'    => $r['data']['que_no_hacer'] ?? '',
            'cuando'          => $r['data']['cuando'] ?? '',
            'quien'           => $r['data']['quien'] ?? '',
            'recursos'        => $r['data']['recursos'] ?? '',
            'generado_con_ia' => 1,
            'modelo_ia'       => $r['modelo'] ?? null,
        ]);

        return $this->response->setJSON([
            'ok'     => true,
            'data'   => $r['data'],
            'tokens' => $r['tokens'] ?? null,
        ]);
    }

    // ==================================================================
    // PRIVADOS
    // ==================================================================

    private function collectMasterFields($userId, bool $isInsert): array
    {
        $data = [
            'id_cliente'                => $this->request->getPost('id_cliente'),
            'fecha_elaboracion'         => $this->request->getPost('fecha_elaboracion'),
            'area'                      => strtoupper($this->request->getPost('area') ?: 'PISCINA'),
            'nombre_area_descriptivo'   => $this->request->getPost('nombre_area_descriptivo'),
            'responsable_area_nombre'   => $this->request->getPost('responsable_area_nombre'),
            'responsable_area_cargo'    => $this->request->getPost('responsable_area_cargo'),
            'responsable_area_contacto' => $this->request->getPost('responsable_area_contacto'),
            'horario_operacion'         => $this->request->getPost('horario_operacion'),
            'aforo_maximo'              => $this->request->getPost('aforo_maximo') ?: null,
            'telefonos_emergencia'      => $this->request->getPost('telefonos_emergencia'),
            'recursos_disponibles'      => $this->request->getPost('recursos_disponibles'),
            'observaciones_contexto'    => $this->request->getPost('observaciones_contexto'),
        ];

        if ($isInsert) {
            $data['id_consultor'] = $userId;
            $data['estado']       = 'borrador';
        }

        return $data;
    }

    private function saveEscenarios(int $idProcedimiento): void
    {
        $ids      = $this->request->getPost('esc_id') ?? [];
        $codigos  = $this->request->getPost('esc_codigo') ?? [];
        $nombres  = $this->request->getPost('esc_nombre') ?? [];
        $queHacer = $this->request->getPost('esc_que_hacer') ?? [];
        $queNo    = $this->request->getPost('esc_que_no_hacer') ?? [];
        $cuando   = $this->request->getPost('esc_cuando') ?? [];
        $quien    = $this->request->getPost('esc_quien') ?? [];
        $recursos = $this->request->getPost('esc_recursos') ?? [];
        $aprobados = $this->request->getPost('esc_aprobado') ?? [];
        $observ   = $this->request->getPost('esc_observaciones') ?? [];

        foreach ($ids as $i => $idEsc) {
            $idEsc = (int)$idEsc;
            if ($idEsc <= 0) continue;

            $esc = $this->escenarioModel->find($idEsc);
            if (!$esc || (int)$esc['id_procedimiento'] !== $idProcedimiento) continue;

            $aprobado = isset($aprobados[$i]) ? 1 : 0;
            $aprobadoAt = ($aprobado && !$esc['aprobado_at']) ? date('Y-m-d H:i:s') : $esc['aprobado_at'];

            $this->escenarioModel->update($idEsc, [
                'escenario_codigo'       => $codigos[$i] ?? $esc['escenario_codigo'],
                'escenario_nombre'       => $nombres[$i]  ?? $esc['escenario_nombre'],
                'que_hacer'              => $queHacer[$i] ?? '',
                'que_no_hacer'           => $queNo[$i] ?? '',
                'cuando'                 => $cuando[$i] ?? '',
                'quien'                  => $quien[$i] ?? '',
                'recursos'               => $recursos[$i] ?? '',
                'aprobado_por_consultor' => $aprobado,
                'aprobado_at'            => $aprobadoAt,
                'observaciones'          => $observ[$i] ?? '',
            ]);
        }
    }

    // ==================================================================
    // PDF
    // ==================================================================

    private function generarPdfInterno(int $id): ?string
    {
        $procedimiento = $this->areaModel->find($id);
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $cliente = $clientModel->find($procedimiento['id_cliente']);
        $consultor = $consultantModel->find($procedimiento['id_consultor']);
        $escenarios = $this->escenarioModel->getByProcedimiento($id);

        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $ext = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
                $mime = ($ext === 'png') ? 'image/png' : 'image/jpeg';
                $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        $data = [
            'procedimiento'  => $procedimiento,
            'cliente'        => $cliente,
            'consultor'      => $consultor,
            'escenarios'     => $escenarios,
            'areasLabels'    => self::AREAS,
            'logoBase64'     => $logoBase64,
            'marcoNormativo' => self::MARCO_NORMATIVO,
        ];

        $html = view('inspecciones/procedimiento-emergencia-area/pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $pdfDir = 'uploads/procedimiento-emergencia-area/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) mkdir(FCPATH . $pdfDir, 0755, true);

        $pdfFileName = 'procedimiento_' . $id . '_' . date('Ymd_His') . '.pdf';
        $pdfPath = $pdfDir . $pdfFileName;

        if (!empty($procedimiento['ruta_pdf']) && file_exists(FCPATH . $procedimiento['ruta_pdf'])) {
            unlink(FCPATH . $procedimiento['ruta_pdf']);
        }

        file_put_contents(FCPATH . $pdfPath, $dompdf->output());
        return $pdfPath;
    }

    private function uploadToReportes(array $procedimiento, string $pdfPath): bool
    {
        $reporteModel = new ReporteModel();
        $clientModel = new ClientModel();
        $cliente = $clientModel->find($procedimiento['id_cliente']);
        if (!$cliente) return false;

        $nitCliente = $cliente['nit_cliente'];

        $existente = $reporteModel
            ->where('id_cliente', $procedimiento['id_cliente'])
            ->where('id_report_type', 6)
            ->where('id_detailreport', 47)
            ->like('observaciones', 'proc_em_area_id:' . $procedimiento['id'])
            ->first();

        $destDir = UPLOADS_PATH . $nitCliente;
        if (!is_dir($destDir)) mkdir($destDir, 0755, true);

        $fileName = 'procedimiento_' . $procedimiento['area'] . '_' . $procedimiento['id'] . '_' . date('Ymd_His') . '.pdf';
        $destPath = $destDir . '/' . $fileName;
        copy(FCPATH . $pdfPath, $destPath);

        $areaLabel = self::AREAS[$procedimiento['area']] ?? $procedimiento['area'];
        $data = [
            'titulo_reporte'  => 'PROCEDIMIENTO EMERGENCIA ' . $areaLabel . ' - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $procedimiento['fecha_elaboracion'],
            'id_detailreport' => 47,
            'id_report_type'  => 6,
            'id_cliente'      => $procedimiento['id_cliente'],
            'estado'          => 'CERRADO',
            'observaciones'   => 'Generado automaticamente desde modulo de procedimientos de emergencia por area. proc_em_area_id:' . $procedimiento['id'],
            'enlace'          => base_url(UPLOADS_URL_PREFIX . '/' . $nitCliente . '/' . $fileName),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        if ($existente) return $reporteModel->update($existente['id_reporte'], $data);

        $data['created_at'] = date('Y-m-d H:i:s');
        return $reporteModel->save($data);
    }
}
