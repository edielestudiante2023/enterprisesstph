<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\InspeccionComunicacionModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use Dompdf\Dompdf;
use App\Libraries\InspeccionEmailNotifier;
use App\Traits\AutosaveJsonTrait;
use App\Traits\ImagenCompresionTrait;

class InspeccionComunicacionController extends BaseController
{
    use AutosaveJsonTrait;
    use ImagenCompresionTrait;
    use \App\Traits\PreventDuplicateBorradorTrait;
    use \App\Traits\InspeccionesTransactionalTrait;
    protected InspeccionComunicacionModel $inspeccionModel;

    /**
     * 8 tipos de equipo de comunicación en caso de emergencias.
     */
    public const EQUIPOS = [
        'telefono_fijo'       => ['label' => 'Telefono Fijo',                  'icon' => 'fa-phone'],
        'telefonia_celular'   => ['label' => 'Telefonia Celular',              'icon' => 'fa-mobile-alt'],
        'radio_onda_corta'    => ['label' => 'Radio de Onda Corta',            'icon' => 'fa-broadcast-tower'],
        'software_citofonia'  => ['label' => 'Software Citofonia',             'icon' => 'fa-headset'],
        'megafonia'           => ['label' => 'Sistemas de Megafonia',          'icon' => 'fa-bullhorn'],
        'cctv_audio'          => ['label' => 'CCTV con Audio',                 'icon' => 'fa-video'],
        'alarma_comunicacion' => ['label' => 'Alarma con Comunicacion',        'icon' => 'fa-bell'],
        'voip'                => ['label' => 'Voz sobre IP (VOIP)',            'icon' => 'fa-wifi'],
    ];

    public function __construct()
    {
        $this->inspeccionModel = new InspeccionComunicacionModel();
    }

    public function list()
    {
        $inspecciones = $this->inspeccionModel
            ->select('tbl_inspeccion_comunicaciones.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_comunicaciones.id_cliente', 'left')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_comunicaciones.id_consultor', 'left')
            ->orderBy('tbl_inspeccion_comunicaciones.fecha_inspeccion', 'DESC')
            ->findAll();

        $data = [
            'title'        => 'Inspeccion Equipos de Comunicacion',
            'inspecciones' => $inspecciones,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/comunicaciones/list', $data),
            'title'   => 'Comunicaciones',
        ]);
    }

    public function create($idCliente = null)
    {
        $data = [
            'title'      => 'Nueva Inspeccion de Comunicaciones',
            'inspeccion' => null,
            'idCliente'  => $idCliente,
            'equipos'    => self::EQUIPOS,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/comunicaciones/form', $data),
            'title'   => 'Nueva Comunicaciones',
        ]);
    }

    public function store()
    {
        $existing = $this->reuseExistingBorrador($this->inspeccionModel, 'fecha_inspeccion', '/inspecciones/comunicaciones/edit/');
        if ($existing) return $existing;

        $userId = session()->get('user_id');
        $isAutosave = $this->isAutosaveRequest();

        if (!$isAutosave) {
            if (!$this->validate(['id_cliente' => 'required|integer', 'fecha_inspeccion' => 'required|valid_date'])) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }
        }

        $data = $this->getInspeccionPostData();
        $data['id_consultor'] = $userId;
        $data['estado'] = 'borrador';

        // Fotos evidencia
        foreach (['foto_1', 'foto_2'] as $campo) {
            $data[$campo] = $this->uploadFoto($campo, 'uploads/inspecciones/comunicaciones/fotos/');
        }

        $this->inspeccionModel->insert($data);
        $idInspeccion = $this->inspeccionModel->getInsertID();

        if ($isAutosave) {
            return $this->autosaveJsonSuccess($idInspeccion);
        }

        return redirect()->to('/inspecciones/comunicaciones/edit/' . $idInspeccion)
            ->with('msg', 'Inspeccion guardada como borrador');
    }

    public function edit($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/comunicaciones')->with('error', 'Inspeccion no encontrada');
        }
        if ($r = $this->guardFinalizado($inspeccion, '/inspecciones/comunicaciones/view/' . $id)) return $r;

        $data = [
            'title'      => 'Editar Inspeccion de Comunicaciones',
            'inspeccion' => $inspeccion,
            'idCliente'  => $inspeccion['id_cliente'],
            'equipos'    => self::EQUIPOS,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/comunicaciones/form', $data),
            'title'   => 'Editar Comunicaciones',
        ]);
    }

    public function update($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            if ($this->isAutosaveRequest()) {
                return $this->autosaveJsonError('No encontrada', 404);
            }
            return redirect()->to('/inspecciones/comunicaciones')->with('error', 'No se puede editar');
        }
        if ($r = $this->guardFinalizado($inspeccion, '/inspecciones/comunicaciones/view/' . $id)) return $r;

        $data = $this->getInspeccionPostData();

        // Fotos (preservar si no se sube nueva)
        foreach (['foto_1', 'foto_2'] as $campo) {
            $nueva = $this->uploadFoto($campo, 'uploads/inspecciones/comunicaciones/fotos/');
            if ($nueva) {
                if (!empty($inspeccion[$campo]) && file_exists(FCPATH . $inspeccion[$campo])) {
                    unlink(FCPATH . $inspeccion[$campo]);
                }
                $data[$campo] = $nueva;
            }
        }

        $this->inspeccionModel->update($id, $data);

        if ($this->request->getPost('finalizar')) {
            return $this->finalizar($id);
        }

        if ($this->isAutosaveRequest()) {
            return $this->autosaveJsonSuccess((int)$id);
        }

        return redirect()->to('/inspecciones/comunicaciones/edit/' . $id)
            ->with('msg', 'Inspeccion actualizada');
    }

    public function view($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/comunicaciones')->with('error', 'No encontrada');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $data = [
            'title'      => 'Ver Inspeccion de Comunicaciones',
            'inspeccion' => $inspeccion,
            'cliente'    => $clientModel->find($inspeccion['id_cliente']),
            'consultor'  => $consultantModel->find($inspeccion['id_consultor']),
            'equipos'    => self::EQUIPOS,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/comunicaciones/view', $data),
            'title'   => 'Ver Comunicaciones',
        ]);
    }

    public function finalizar($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/comunicaciones')->with('error', 'No encontrada');
        }

        if (($inspeccion['estado'] ?? '') === 'completo') {
            return redirect()->to('/inspecciones/comunicaciones/view/' . $id)
                ->with('msg', 'Esta inspeccion ya fue finalizada anteriormente.');
        }

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

        // Enviar email con PDF adjunto
        $emailResult = InspeccionEmailNotifier::enviar(
            (int) $inspeccion['id_cliente'],
            (int) $inspeccion['id_consultor'],
            'INSPECCIÓN COMUNICACIONES',
            $inspeccion['fecha_inspeccion'],
            $pdfPath,
            (int) $inspeccion['id'],
            'InspeccionComunicacion'
        );
        $msg = 'Inspección finalizada y PDF generado.';
        if ($emailResult['success']) {
            $msg .= ' ' . $emailResult['message'];
        } else {
            $msg .= ' (Email no enviado: ' . $emailResult['error'] . ')';
        }

        return redirect()->to('/inspecciones/comunicaciones/view/' . $id)
            ->with('msg', $msg);
    }

    public function generatePdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/comunicaciones')->with('error', 'No encontrada');
        }

        $pdfPath = $this->generarPdfInterno($id);
        $this->inspeccionModel->update($id, ['ruta_pdf' => $pdfPath]);
        $fullPath = FCPATH . $pdfPath;
        if (!file_exists($fullPath)) {
            return redirect()->back()->with('error', 'PDF no encontrado');
        }

        $this->servirPdf($fullPath, 'comunicaciones_' . $id . '.pdf');
        return;
    }

    public function delete($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/comunicaciones')->with('error', 'No encontrada');
        }
        // Eliminar fotos
        foreach (['foto_1', 'foto_2'] as $campo) {
            if (!empty($inspeccion[$campo]) && file_exists(FCPATH . $inspeccion[$campo])) {
                unlink(FCPATH . $inspeccion[$campo]);
            }
        }

        if (!empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) {
            unlink(FCPATH . $inspeccion['ruta_pdf']);
        }

        $this->inspeccionModel->delete($id);

        return redirect()->to('/inspecciones/comunicaciones')->with('msg', 'Inspeccion eliminada');
    }

    // ===== MÉTODOS PRIVADOS =====

        public function regenerarPdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || ($inspeccion['estado'] ?? '') !== 'completo') {
            return redirect()->to('/inspecciones/comunicaciones')->with('error', 'Solo se puede regenerar un registro finalizado.');
        }

        $pdfPath = $this->generarPdfInterno($id);

        $this->inspeccionModel->update($id, [
            'ruta_pdf' => $pdfPath,
        ]);

        $inspeccion = $this->inspeccionModel->find($id);
        $this->uploadToReportes($inspeccion, $pdfPath);

        return redirect()->to("/inspecciones/comunicaciones/view/{$id}")->with('msg', 'PDF regenerado exitosamente.');
    }

    private function getInspeccionPostData(): array
    {
        $data = [
            'id_cliente'           => $this->request->getPost('id_cliente'),
            'fecha_inspeccion'     => $this->request->getPost('fecha_inspeccion'),
            'observaciones_finales' => $this->request->getPost('observaciones_finales'),
        ];

        foreach (self::EQUIPOS as $key => $info) {
            $data['cant_' . $key] = (int) $this->request->getPost('cant_' . $key);
            $data['obs_' . $key]  = $this->request->getPost('obs_' . $key);
        }

        return $data;
    }

    private function uploadFoto(string $campo, string $dir): ?string
    {
        $file = $this->request->getFile($campo);
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

        // Logo en base64
        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoBase64 = $this->fotoABase64ParaPdf($logoPath);
            }
        }

        // Fotos evidencia a base64
        $fotosBase64 = [];
        foreach (['foto_1', 'foto_2'] as $campo) {
            $fotosBase64[$campo] = '';
            if (!empty($inspeccion[$campo])) {
                $fotoPath = FCPATH . $inspeccion[$campo];
                if (file_exists($fotoPath)) {
                    $fotosBase64[$campo] = $this->fotoABase64ParaPdf($fotoPath);
                }
            }
        }

        $data = [
            'inspeccion'  => $inspeccion,
            'cliente'     => $cliente,
            'consultor'   => $consultor,
            'equipos'     => self::EQUIPOS,
            'logoBase64'  => $logoBase64,
            'fotosBase64' => $fotosBase64,
        ];

        $html = view('inspecciones/comunicaciones/pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $pdfDir = 'uploads/inspecciones/comunicaciones/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) {
            mkdir(FCPATH . $pdfDir, 0755, true);
        }

        $pdfFileName = 'comunicaciones_' . $id . '_' . date('Ymd_His') . '.pdf';
        $pdfPath = $pdfDir . $pdfFileName;

        if (!empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) {
            unlink(FCPATH . $inspeccion['ruta_pdf']);
        }

        file_put_contents(FCPATH . $pdfPath, $dompdf->output());

        return $pdfPath;
    }

    // ── Email ─────────────────────────────────────────────────

    public function enviarEmail($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || $inspeccion['estado'] !== 'completo' || empty($inspeccion['ruta_pdf'])) {
            return redirect()->to("/inspecciones/comunicaciones/view/{$id}")->with('error', 'Debe estar finalizado con PDF para enviar email.');
        }

        $result = InspeccionEmailNotifier::enviar(
            (int) $inspeccion['id_cliente'],
            (int) $inspeccion['id_consultor'],
            'INSPECCIÓN COMUNICACIONES',
            $inspeccion['fecha_inspeccion'],
            $inspeccion['ruta_pdf'],
            (int) $inspeccion['id'],
            'InspeccionComunicacion'
        );

        if ($result['success']) {
            return redirect()->to("/inspecciones/comunicaciones/view/{$id}")->with('msg', $result['message']);
        }
        return redirect()->to("/inspecciones/comunicaciones/view/{$id}")->with('error', $result['error']);
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
            ->where('id_detailreport', 15)
            ->like('observaciones', 'insp_com_id:' . $inspeccion['id'])
            ->first();

        $destDir = UPLOADS_PATH . $nitCliente;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $fileName = 'comunicaciones_' . $inspeccion['id'] . '_' . date('Ymd_His') . '.pdf';
        $destPath = $destDir . '/' . $fileName;
        copy(FCPATH . $pdfPath, $destPath);

        $data = [
            'titulo_reporte'  => 'INSPECCION COMUNICACIONES - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $inspeccion['fecha_inspeccion'],
            'id_detailreport' => 15,
            'id_report_type'  => 6,
            'id_cliente'      => $inspeccion['id_cliente'],
            'estado'          => 'CERRADO',
            'observaciones'   => 'Generado automaticamente desde modulo de inspecciones. insp_com_id:' . $inspeccion['id'],
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
