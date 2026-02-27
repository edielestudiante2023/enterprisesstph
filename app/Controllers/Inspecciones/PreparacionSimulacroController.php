<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\PreparacionSimulacroModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use Dompdf\Dompdf;

class PreparacionSimulacroController extends BaseController
{
    protected PreparacionSimulacroModel $inspeccionModel;

    public const OPCIONES_ALARMA = [
        'sirena' => 'Sirena',
        'megafono' => 'Megafono',
        'radio_interno' => 'Radio interno',
    ];

    public const OPCIONES_DISTINTIVOS = [
        'chaleco' => 'Chaleco',
        'brazalete' => 'Brazalete',
        'ninguno' => 'Ninguno',
    ];

    public const OPCIONES_EQUIPOS = [
        'paletas_pare_siga' => 'Paletas de PARE y SIGA',
        'chaleco_reflectivo' => 'Chaleco reflectivo',
        'megafono_pito' => 'Megafono o pito',
        'camilla' => 'Camilla',
        'botiquin' => 'Botiquin',
        'radio_onda_corta' => 'Radio de onda corta',
        'paleta_punto_encuentro' => 'Paleta Punto de Encuentro',
    ];

    public const CRONOGRAMA_ITEMS = [
        'hora_inicio'              => 'Hora de Inicio',
        'alistamiento_recursos'    => 'Alistamiento de Recursos',
        'asumir_roles'             => 'Asumir Roles',
        'suena_alarma'             => 'Suena la Alarma',
        'distribucion_roles'       => 'Distribucion de Roles',
        'llegada_punto_encuentro'  => 'Llegada al Punto de Encuentro',
        'agrupacion_por_afinidad'  => 'Agrupacion por Afinidad',
        'conteo_personal'          => 'Conteo de Personal',
        'agradecimiento_cierre'    => 'Agradecimiento y Cierre',
    ];

    public function __construct()
    {
        $this->inspeccionModel = new PreparacionSimulacroModel();
    }

    public function list()
    {
        $userId = session()->get('user_id');
        $role = session()->get('role');

        if ($role === 'admin') {
            $inspecciones = $this->inspeccionModel
                ->select('tbl_preparacion_simulacro.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
                ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_preparacion_simulacro.id_cliente', 'left')
                ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_preparacion_simulacro.id_consultor', 'left')
                ->orderBy('tbl_preparacion_simulacro.fecha_simulacro', 'DESC')
                ->findAll();
        } else {
            $inspecciones = $this->inspeccionModel->getByConsultor($userId);
        }

        $data = [
            'title'        => 'Preparacion Simulacro',
            'inspecciones' => $inspecciones,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/preparacion-simulacro/list', $data),
            'title'   => 'Preparacion Simulacro',
        ]);
    }

    public function create($idCliente = null)
    {
        $data = [
            'title'              => 'Nueva Preparacion Simulacro',
            'inspeccion'         => null,
            'idCliente'          => $idCliente,
            'opcionesAlarma'     => self::OPCIONES_ALARMA,
            'opcionesDistintivos' => self::OPCIONES_DISTINTIVOS,
            'opcionesEquipos'    => self::OPCIONES_EQUIPOS,
            'cronogramaItems'    => self::CRONOGRAMA_ITEMS,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/preparacion-simulacro/form', $data),
            'title'   => 'Nueva Prep. Simulacro',
        ]);
    }

    public function store()
    {
        $userId = session()->get('user_id');

        if (!$this->validate(['id_cliente' => 'required|integer', 'fecha_simulacro' => 'required|valid_date'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->getInspeccionPostData();
        $data['id_consultor'] = $userId;
        $data['estado'] = 'borrador';

        $data['imagen_1'] = $this->uploadFoto('imagen_1', 'uploads/inspecciones/preparacion-simulacro/');
        $data['imagen_2'] = $this->uploadFoto('imagen_2', 'uploads/inspecciones/preparacion-simulacro/');

        $this->inspeccionModel->insert($data);
        $idInspeccion = $this->inspeccionModel->getInsertID();

        return redirect()->to('/inspecciones/preparacion-simulacro/edit/' . $idInspeccion)
            ->with('msg', 'Inspeccion guardada como borrador');
    }

    public function edit($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/preparacion-simulacro')->with('error', 'Inspeccion no encontrada');
        }

        if ($inspeccion['estado'] === 'completo') {
            return redirect()->to('/inspecciones/preparacion-simulacro/view/' . $id);
        }

        $data = [
            'title'              => 'Editar Preparacion Simulacro',
            'inspeccion'         => $inspeccion,
            'idCliente'          => $inspeccion['id_cliente'],
            'opcionesAlarma'     => self::OPCIONES_ALARMA,
            'opcionesDistintivos' => self::OPCIONES_DISTINTIVOS,
            'opcionesEquipos'    => self::OPCIONES_EQUIPOS,
            'cronogramaItems'    => self::CRONOGRAMA_ITEMS,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/preparacion-simulacro/form', $data),
            'title'   => 'Editar Prep. Simulacro',
        ]);
    }

    public function update($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || $inspeccion['estado'] === 'completo') {
            return redirect()->to('/inspecciones/preparacion-simulacro')->with('error', 'No se puede editar');
        }

        $data = $this->getInspeccionPostData();

        foreach (['imagen_1', 'imagen_2'] as $campo) {
            $nueva = $this->uploadFoto($campo, 'uploads/inspecciones/preparacion-simulacro/');
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

        return redirect()->to('/inspecciones/preparacion-simulacro/edit/' . $id)
            ->with('msg', 'Inspeccion actualizada');
    }

    public function view($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/preparacion-simulacro')->with('error', 'No encontrada');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $data = [
            'title'              => 'Ver Preparacion Simulacro',
            'inspeccion'         => $inspeccion,
            'cliente'            => $clientModel->find($inspeccion['id_cliente']),
            'consultor'          => $consultantModel->find($inspeccion['id_consultor']),
            'opcionesAlarma'     => self::OPCIONES_ALARMA,
            'opcionesDistintivos' => self::OPCIONES_DISTINTIVOS,
            'opcionesEquipos'    => self::OPCIONES_EQUIPOS,
            'cronogramaItems'    => self::CRONOGRAMA_ITEMS,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/preparacion-simulacro/view', $data),
            'title'   => 'Ver Prep. Simulacro',
        ]);
    }

    public function finalizar($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/preparacion-simulacro')->with('error', 'No encontrada');
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

        return redirect()->to('/inspecciones/preparacion-simulacro/view/' . $id)
            ->with('msg', 'Inspeccion finalizada y PDF generado');
    }

    public function generatePdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/preparacion-simulacro')->with('error', 'No encontrada');
        }

        $pdfPath = $this->generarPdfInterno($id);
        $fullPath = FCPATH . $pdfPath;
        if (!file_exists($fullPath)) {
            return redirect()->back()->with('error', 'PDF no encontrado');
        }

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="preparacion_simulacro_' . $id . '.pdf"')
            ->setBody(file_get_contents($fullPath));
    }

    public function delete($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/preparacion-simulacro')->with('error', 'No encontrada');
        }
        if ($inspeccion['estado'] === 'completo') {
            return redirect()->to('/inspecciones/preparacion-simulacro')->with('error', 'No se puede eliminar una inspeccion completa');
        }

        foreach (['imagen_1', 'imagen_2'] as $campo) {
            if (!empty($inspeccion[$campo]) && file_exists(FCPATH . $inspeccion[$campo])) {
                unlink(FCPATH . $inspeccion[$campo]);
            }
        }

        if (!empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) {
            unlink(FCPATH . $inspeccion['ruta_pdf']);
        }

        $this->inspeccionModel->delete($id);

        return redirect()->to('/inspecciones/preparacion-simulacro')->with('msg', 'Inspeccion eliminada');
    }

    // ===== METODOS PRIVADOS =====

        public function regenerarPdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || ($inspeccion['estado'] ?? '') !== 'completo') {
            return redirect()->to('/inspecciones/preparacion-simulacro')->with('error', 'Solo se puede regenerar un registro finalizado.');
        }

        $pdfPath = $this->generarPdfInterno($id);

        $this->inspeccionModel->update($id, [
            'ruta_pdf' => $pdfPath,
        ]);

        $inspeccion = $this->inspeccionModel->find($id);
        $this->uploadToReportes($inspeccion, $pdfPath);

        return redirect()->to("/inspecciones/preparacion-simulacro/view/{$id}")->with('msg', 'PDF regenerado exitosamente.');
    }

    private function getInspeccionPostData(): array
    {
        $data = [
            'id_cliente'                 => $this->request->getPost('id_cliente'),
            'fecha_simulacro'            => $this->request->getPost('fecha_simulacro'),
            'ubicacion'                  => $this->request->getPost('ubicacion'),
            'direccion'                  => $this->request->getPost('direccion'),
            'evento_simulado'            => $this->request->getPost('evento_simulado'),
            'alcance_simulacro'          => $this->request->getPost('alcance_simulacro'),
            'tipo_evacuacion'            => $this->request->getPost('tipo_evacuacion'),
            'personal_no_evacua'         => $this->request->getPost('personal_no_evacua'),
            'puntos_encuentro'           => $this->request->getPost('puntos_encuentro'),
            'recurso_humano'             => $this->request->getPost('recurso_humano'),
            'nombre_brigadista_lider'    => $this->request->getPost('nombre_brigadista_lider'),
            'email_brigadista_lider'     => $this->request->getPost('email_brigadista_lider'),
            'whatsapp_brigadista_lider'  => $this->request->getPost('whatsapp_brigadista_lider'),
            'entrega_formato_evaluacion' => $this->request->getPost('entrega_formato_evaluacion'),
            'observaciones'              => $this->request->getPost('observaciones'),
        ];

        // EnumList checkboxes → comma-separated
        foreach (['tipo_alarma', 'distintivos_brigadistas', 'equipos_emergencia'] as $campo) {
            $vals = $this->request->getPost($campo);
            $data[$campo] = is_array($vals) ? implode(',', $vals) : '';
        }

        // 9 TIME fields
        foreach (self::CRONOGRAMA_ITEMS as $key => $label) {
            $data[$key] = $this->request->getPost($key);
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
                $logoMime = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        $fotosBase64 = [];
        foreach (['imagen_1', 'imagen_2'] as $campo) {
            $fotosBase64[$campo] = '';
            if (!empty($inspeccion[$campo])) {
                $fotoPath = FCPATH . $inspeccion[$campo];
                if (file_exists($fotoPath)) {
                    $mime = mime_content_type($fotoPath);
                    $fotosBase64[$campo] = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fotoPath));
                }
            }
        }

        $data = [
            'inspeccion'         => $inspeccion,
            'cliente'            => $cliente,
            'consultor'          => $consultor,
            'opcionesAlarma'     => self::OPCIONES_ALARMA,
            'opcionesDistintivos' => self::OPCIONES_DISTINTIVOS,
            'opcionesEquipos'    => self::OPCIONES_EQUIPOS,
            'cronogramaItems'    => self::CRONOGRAMA_ITEMS,
            'logoBase64'         => $logoBase64,
            'fotosBase64'        => $fotosBase64,
        ];

        $html = view('inspecciones/preparacion-simulacro/pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $pdfDir = 'uploads/inspecciones/preparacion-simulacro/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) {
            mkdir(FCPATH . $pdfDir, 0755, true);
        }

        $pdfFileName = 'preparacion_simulacro_' . $id . '_' . date('Ymd_His') . '.pdf';
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
            ->where('id_detailreport', 28)
            ->like('observaciones', 'prep_sim_id:' . $inspeccion['id'])
            ->first();

        $destDir = ROOTPATH . 'public/uploads/' . $nitCliente;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $fileName = 'preparacion_simulacro_' . $inspeccion['id'] . '_' . date('Ymd_His') . '.pdf';
        $destPath = $destDir . '/' . $fileName;
        copy(FCPATH . $pdfPath, $destPath);

        $data = [
            'titulo_reporte'  => 'PREPARACION SIMULACRO - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $inspeccion['fecha_simulacro'],
            'id_detailreport' => 28,
            'id_report_type'  => 6,
            'id_cliente'      => $inspeccion['id_cliente'],
            'estado'          => 'Activo',
            'observaciones'   => 'Generado automaticamente desde modulo de inspecciones. prep_sim_id:' . $inspeccion['id'],
            'enlace'          => base_url('uploads/' . $nitCliente . '/' . $fileName),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        if ($existente) {
            return $reporteModel->update($existente['id_reporte'], $data);
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        return $reporteModel->save($data);
    }
}
