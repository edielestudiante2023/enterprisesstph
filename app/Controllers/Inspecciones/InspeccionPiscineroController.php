<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\InspeccionPiscineroModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use Dompdf\Dompdf;
use App\Libraries\InspeccionEmailNotifier;
use App\Traits\AutosaveJsonTrait;
use App\Traits\ImagenCompresionTrait;

class InspeccionPiscineroController extends BaseController
{
    use AutosaveJsonTrait;
    use ImagenCompresionTrait;
    use \App\Traits\PreventDuplicateBorradorTrait;
    use AppTraitsInspeccionesTransactionalTrait;

    protected InspeccionPiscineroModel $inspeccionModel;

    public const MARCO_NORMATIVO = 'Ley 1209 de 2008 Artículo 14 — Obligatoriedad de salvavidas con certificación vigente en Resucitación Cardiopulmonar (RCP) por cada piscina de uso colectivo en conjuntos residenciales. Esta inspección SST verifica la idoneidad del personal a cargo de la piscina y NO reemplaza la certificación del personal por entidades acreditadas.';

    public function __construct()
    {
        $this->inspeccionModel = new InspeccionPiscineroModel();
    }

    public function list()
    {
        $inspecciones = $this->inspeccionModel
            ->select('tbl_inspeccion_piscinero.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_piscinero.id_cliente', 'left')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_piscinero.id_consultor', 'left')
            ->orderBy('tbl_inspeccion_piscinero.fecha_inspeccion', 'DESC')
            ->findAll();

        $data = [
            'title'        => 'Inspeccion del Piscinero / Salvavidas',
            'inspecciones' => $inspecciones,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/piscinero/list', $data),
            'title'   => 'Piscinero',
        ]);
    }

    public function create($idCliente = null)
    {
        $data = [
            'title'      => 'Nueva Inspeccion del Piscinero',
            'inspeccion' => null,
            'idCliente'  => $idCliente,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/piscinero/form', $data),
            'title'   => 'Nuevo Piscinero',
        ]);
    }

    public function store()
    {
        $existing = $this->reuseExistingBorrador($this->inspeccionModel, 'fecha_inspeccion', '/inspecciones/piscinero/edit/');
        if ($existing) return $existing;

        $userId = session()->get('user_id');
        $isAutosave = $this->isAutosaveRequest();

        if (!$isAutosave) {
            if (!$this->validate(['id_cliente' => 'required|integer', 'fecha_inspeccion' => 'required|valid_date'])) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }
        }

        $inspeccionData = $this->getInspeccionPostData($userId);
        $inspeccionData['estado'] = 'borrador';
        $inspeccionData['foto_piscinero']            = $this->uploadFoto('foto_piscinero', 'uploads/inspecciones/piscinero/fotos/');
        $inspeccionData['foto_certificado_rcp']      = $this->uploadFoto('foto_certificado_rcp', 'uploads/inspecciones/piscinero/fotos/');
        $inspeccionData['foto_certificado_salvamento'] = $this->uploadFoto('foto_certificado_salvamento', 'uploads/inspecciones/piscinero/fotos/');

        $this->inspeccionModel->insert($inspeccionData);
        $idInspeccion = $this->inspeccionModel->getInsertID();

        if ($isAutosave) {
            return $this->autosaveJsonSuccess($idInspeccion);
        }

        return redirect()->to('/inspecciones/piscinero/edit/' . $idInspeccion)
            ->with('msg', 'Inspeccion guardada como borrador');
    }

    public function edit($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/piscinero')->with('error', 'Inspeccion no encontrada');
        }

        $data = [
            'title'      => 'Editar Inspeccion del Piscinero',
            'inspeccion' => $inspeccion,
            'idCliente'  => $inspeccion['id_cliente'],
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/piscinero/form', $data),
            'title'   => 'Editar Piscinero',
        ]);
    }

    public function update($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            if ($this->isAutosaveRequest()) {
                return $this->autosaveJsonError('No encontrada', 404);
            }
            return redirect()->to('/inspecciones/piscinero')->with('error', 'No se puede editar');
        }

        $userId = $inspeccion['id_consultor'];
        $updateData = $this->getInspeccionPostData($userId);

        $camposFoto = ['foto_piscinero', 'foto_certificado_rcp', 'foto_certificado_salvamento'];
        foreach ($camposFoto as $campo) {
            $nueva = $this->uploadFoto($campo, 'uploads/inspecciones/piscinero/fotos/');
            if ($nueva) {
                if (!empty($inspeccion[$campo]) && file_exists(FCPATH . $inspeccion[$campo])) {
                    unlink(FCPATH . $inspeccion[$campo]);
                }
                $updateData[$campo] = $nueva;
            }
        }

        $this->inspeccionModel->update($id, $updateData);

        if ($this->request->getPost('finalizar')) {
            return $this->finalizar($id);
        }

        if ($this->isAutosaveRequest()) {
            return $this->autosaveJsonSuccess((int)$id);
        }

        return redirect()->to('/inspecciones/piscinero/edit/' . $id)
            ->with('msg', 'Inspeccion actualizada');
    }

    public function view($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/piscinero')->with('error', 'No encontrada');
        }

        if ( = ->guardFinalizado(, '/inspecciones/piscinero/view/' . )) return ;

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $data = [
            'title'      => 'Ver Inspeccion del Piscinero',
            'inspeccion' => $inspeccion,
            'cliente'    => $clientModel->find($inspeccion['id_cliente']),
            'consultor'  => $consultantModel->find($inspeccion['id_consultor']),
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/piscinero/view', $data),
            'title'   => 'Ver Piscinero',
        ]);
    }

    public function finalizar($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/piscinero')->with('error', 'No encontrada');
        }

        $this->inspeccionModel->update($id, [
            'marco_normativo' => self::MARCO_NORMATIVO,
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
            'INSPECCIÓN PISCINERO / SALVAVIDAS',
            $inspeccion['fecha_inspeccion'],
            $pdfPath,
            (int) $inspeccion['id'],
            'InspeccionPiscinero'
        );
        $msg = 'Inspección finalizada y PDF generado.';
        if ($emailResult['success']) {
            $msg .= ' ' . $emailResult['message'];
        } else {
            $msg .= ' (Email no enviado: ' . $emailResult['error'] . ')';
        }

        return redirect()->to('/inspecciones/piscinero/view/' . $id)->with('msg', $msg);
    }

    public function generatePdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/piscinero')->with('error', 'No encontrada');
        }

        $pdfPath = $this->generarPdfInterno($id);
        $this->inspeccionModel->update($id, ['ruta_pdf' => $pdfPath]);
        $fullPath = FCPATH . $pdfPath;
        if (!file_exists($fullPath)) {
            return redirect()->back()->with('error', 'PDF no encontrado');
        }

        $this->servirPdf($fullPath, 'piscinero_' . $id . '.pdf');
        return;
    }

    public function regenerarPdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || ($inspeccion['estado'] ?? '') !== 'completo') {
            return redirect()->to('/inspecciones/piscinero')->with('error', 'Solo se puede regenerar un registro finalizado.');
        }

        $pdfPath = $this->generarPdfInterno($id);
        $this->inspeccionModel->update($id, ['ruta_pdf' => $pdfPath]);

        $inspeccion = $this->inspeccionModel->find($id);
        $this->uploadToReportes($inspeccion, $pdfPath);

        return redirect()->to("/inspecciones/piscinero/view/{$id}")->with('msg', 'PDF regenerado exitosamente.');
    }

    public function delete($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/piscinero')->with('error', 'No encontrada');
        }

        $camposFoto = ['foto_piscinero', 'foto_certificado_rcp', 'foto_certificado_salvamento'];
        foreach ($camposFoto as $campo) {
            if (!empty($inspeccion[$campo]) && file_exists(FCPATH . $inspeccion[$campo])) {
                unlink(FCPATH . $inspeccion[$campo]);
            }
        }

        if (!empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) {
            unlink(FCPATH . $inspeccion['ruta_pdf']);
        }

        $this->inspeccionModel->delete($id);

        return redirect()->to('/inspecciones/piscinero')->with('msg', 'Inspeccion eliminada');
    }

    public function enviarEmail($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || $inspeccion['estado'] !== 'completo' || empty($inspeccion['ruta_pdf'])) {
            return redirect()->to("/inspecciones/piscinero/view/{$id}")->with('error', 'Debe estar finalizado con PDF para enviar email.');
        }

        $result = InspeccionEmailNotifier::enviar(
            (int) $inspeccion['id_cliente'],
            (int) $inspeccion['id_consultor'],
            'INSPECCIÓN PISCINERO / SALVAVIDAS',
            $inspeccion['fecha_inspeccion'],
            $inspeccion['ruta_pdf'],
            (int) $inspeccion['id'],
            'InspeccionPiscinero'
        );

        if ($result['success']) {
            return redirect()->to("/inspecciones/piscinero/view/{$id}")->with('msg', $result['message']);
        }
        return redirect()->to("/inspecciones/piscinero/view/{$id}")->with('error', $result['error']);
    }

    // ===== PRIVADOS =====

    private function getInspeccionPostData(int $userId): array
    {
        return [
            'id_cliente'                      => $this->request->getPost('id_cliente'),
            'id_consultor'                    => $userId,
            'fecha_inspeccion'                => $this->request->getPost('fecha_inspeccion'),
            'nombre_piscinero'                => $this->request->getPost('nombre_piscinero'),
            'cedula'                          => $this->request->getPost('cedula'),
            'telefono'                        => $this->request->getPost('telefono'),
            'vinculacion'                     => $this->request->getPost('vinculacion') ?: 'DIRECTO_COPROPIEDAD',
            'empresa_contratista'             => $this->request->getPost('empresa_contratista'),
            'nit_empresa_contratista'         => $this->request->getPost('nit_empresa_contratista'),
            'certificacion_rcp_vigente'       => $this->request->getPost('certificacion_rcp_vigente') ?: 'NA',
            'fecha_vencimiento_rcp'           => $this->request->getPost('fecha_vencimiento_rcp') ?: null,
            'curso_salvamento_acuatico'       => $this->request->getPost('curso_salvamento_acuatico') ?: 'NA',
            'fecha_vencimiento_salvamento'    => $this->request->getPost('fecha_vencimiento_salvamento') ?: null,
            'afiliacion_arl_vigente'          => $this->request->getPost('afiliacion_arl_vigente') ?: 'NA',
            'afiliacion_eps_vigente'          => $this->request->getPost('afiliacion_eps_vigente') ?: 'NA',
            'examenes_medicos_ocupacionales'  => $this->request->getPost('examenes_medicos_ocupacionales') ?: 'NA',
            'fecha_ultimo_examen_medico'      => $this->request->getPost('fecha_ultimo_examen_medico') ?: null,
            'dotacion_epp_entregada'          => $this->request->getPost('dotacion_epp_entregada') ?: 'NA',
            'gafas_proteccion_quimica'        => $this->request->getPost('gafas_proteccion_quimica') ?: 'NA',
            'guantes_nitrilo'                 => $this->request->getPost('guantes_nitrilo') ?: 'NA',
            'careta_proteccion'               => $this->request->getPost('careta_proteccion') ?: 'NA',
            'delantal_impermeable'            => $this->request->getPost('delantal_impermeable') ?: 'NA',
            'capacitacion_manejo_quimicos'    => $this->request->getPost('capacitacion_manejo_quimicos') ?: 'NA',
            'conocimiento_hojas_seguridad'    => $this->request->getPost('conocimiento_hojas_seguridad') ?: 'NA',
            'conocimiento_plan_emergencia'    => $this->request->getPost('conocimiento_plan_emergencia') ?: 'NA',
            'horario_cubre_operacion_piscina' => $this->request->getPost('horario_cubre_operacion_piscina') ?: 'NA',
            'horario_inicio'                  => $this->request->getPost('horario_inicio') ?: null,
            'horario_fin'                     => $this->request->getPost('horario_fin') ?: null,
            'observaciones'                   => $this->request->getPost('observaciones'),
        ];
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
        $inspeccion = $this->inspeccionModel->find($id);
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $cliente = $clientModel->find($inspeccion['id_cliente']);
        $consultor = $consultantModel->find($inspeccion['id_consultor']);

        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoBase64 = $this->fotoABase64ParaPdf($logoPath);
            }
        }

        $fotosBase64 = [];
        foreach (['foto_piscinero', 'foto_certificado_rcp', 'foto_certificado_salvamento'] as $campo) {
            $fotosBase64[$campo] = '';
            if (!empty($inspeccion[$campo])) {
                $p = FCPATH . $inspeccion[$campo];
                if (file_exists($p)) {
                    $fotosBase64[$campo] = $this->fotoABase64ParaPdf($p);
                }
            }
        }

        $data = [
            'inspeccion'     => $inspeccion,
            'cliente'        => $cliente,
            'consultor'      => $consultor,
            'logoBase64'     => $logoBase64,
            'fotosBase64'    => $fotosBase64,
            'marcoNormativo' => self::MARCO_NORMATIVO,
        ];

        $html = view('inspecciones/piscinero/pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $pdfDir = 'uploads/inspecciones/piscinero/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) {
            mkdir(FCPATH . $pdfDir, 0755, true);
        }

        $pdfFileName = 'piscinero_' . $id . '_' . date('Ymd_His') . '.pdf';
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
            ->where('id_detailreport', 47)
            ->like('observaciones', 'insp_piscinero_id:' . $inspeccion['id'])
            ->first();

        $destDir = UPLOADS_PATH . $nitCliente;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $fileName = 'piscinero_' . $inspeccion['id'] . '_' . date('Ymd_His') . '.pdf';
        $destPath = $destDir . '/' . $fileName;
        copy(FCPATH . $pdfPath, $destPath);

        $data = [
            'titulo_reporte'  => 'INSPECCION PISCINERO - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $inspeccion['fecha_inspeccion'],
            'id_detailreport' => 47,
            'id_report_type'  => 6,
            'id_cliente'      => $inspeccion['id_cliente'],
            'estado'          => 'CERRADO',
            'observaciones'   => 'Generado automaticamente desde modulo de inspecciones. insp_piscinero_id:' . $inspeccion['id'],
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
