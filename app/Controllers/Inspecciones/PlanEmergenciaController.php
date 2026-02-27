<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\PlanEmergenciaModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use App\Models\InspeccionLocativaModel;
use App\Models\MatrizVulnerabilidadModel;
use App\Models\ProbabilidadPeligrosModel;
use App\Models\InspeccionExtintoresModel;
use App\Models\InspeccionBotiquinModel;
use App\Models\InspeccionRecursosSeguridadModel;
use App\Models\InspeccionComunicacionModel;
use App\Models\InspeccionGabineteModel;
use Dompdf\Dompdf;

class PlanEmergenciaController extends BaseController
{
    protected PlanEmergenciaModel $model;

    public const TELEFONOS = [
        'bogota' => [
            'Acueducto'            => '116',
            'Bomberos'             => '119',
            'Cruz Roja'            => '132',
            'Defensa Civil'        => '144',
            'GAULA'                => '165',
            'Policia'              => '123',
            'Secretaria de Salud'  => '(601) 364-9090',
            'Secretaria de Movilidad' => '#797',
        ],
        'soacha' => [
            'Acueducto'            => '116',
            'Bomberos'             => '119',
            'Cruz Roja'            => '132',
            'Defensa Civil'        => '144',
            'GAULA'                => '165',
            'Policia'              => '123',
            'Secretaria de Salud'  => '(601) 730-5500',
            'Secretaria de Movilidad' => '(601) 840-0223',
        ],
    ];

    public const EMPRESAS_ASEO = [
        'urbaser_soacha'  => 'Urbaser Soacha S.A. E.S.P.',
        'bogota_limpia'   => 'Bogota Limpia',
        'promoambiental'  => 'Promoambiental Distrito',
        'ciudad_limpia'   => 'Ciudad Limpia',
        'area_limpia'     => 'Area Limpia',
        'lime'            => 'LIME',
    ];

    public const FOTO_FIELDS = [
        'foto_fachada', 'foto_panorama', 'foto_torres_1', 'foto_torres_2',
        'foto_parqueaderos_carros', 'foto_parqueaderos_motos', 'foto_oficina_admin',
        'foto_circulacion_vehicular', 'foto_circulacion_peatonal_1', 'foto_circulacion_peatonal_2',
        'foto_salida_emergencia_1', 'foto_salida_emergencia_2', 'foto_ingresos_peatonales',
        'foto_acceso_vehicular_1', 'foto_acceso_vehicular_2',
        'foto_ruta_evacuacion_1', 'foto_ruta_evacuacion_2',
        'foto_punto_encuentro_1', 'foto_punto_encuentro_2',
    ];

    public function __construct()
    {
        $this->model = new PlanEmergenciaModel();
    }

    // ===== CRUD =====

    public function list()
    {
        $userId = session()->get('user_id');
        $role = session()->get('role');

        if ($role === 'admin') {
            $inspecciones = $this->model
                ->select('tbl_plan_emergencia.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
                ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_plan_emergencia.id_cliente', 'left')
                ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_plan_emergencia.id_consultor', 'left')
                ->orderBy('tbl_plan_emergencia.fecha_visita', 'DESC')
                ->findAll();
        } else {
            $inspecciones = $this->model->getByConsultor($userId);
        }

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/plan-emergencia/list', [
                'title'        => 'Plan de Emergencia',
                'inspecciones' => $inspecciones,
            ]),
            'title' => 'Plan de Emergencia',
        ]);
    }

    public function create($idCliente = null)
    {
        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/plan-emergencia/form', [
                'title'         => 'Nuevo Plan de Emergencia',
                'inspeccion'    => null,
                'idCliente'     => $idCliente,
                'telefonos'     => self::TELEFONOS,
                'empresasAseo'  => self::EMPRESAS_ASEO,
            ]),
            'title' => 'Nuevo Plan Emergencia',
        ]);
    }

    public function store()
    {
        $userId = session()->get('user_id');

        if (!$this->validate(['id_cliente' => 'required|integer', 'fecha_visita' => 'required|valid_date'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->getInspeccionPostData();
        $data['id_consultor'] = $userId;
        $data['estado'] = 'borrador';

        // Fotos
        foreach (self::FOTO_FIELDS as $campo) {
            $foto = $this->uploadFoto($campo, 'uploads/inspecciones/plan-emergencia/fotos/');
            if ($foto) {
                $data[$campo] = $foto;
            }
        }

        $this->model->insert($data);
        $idPlan = $this->model->getInsertID();

        return redirect()->to('/inspecciones/plan-emergencia/edit/' . $idPlan)
            ->with('msg', 'Plan guardado como borrador');
    }

    public function edit($id)
    {
        $inspeccion = $this->model->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/plan-emergencia')->with('error', 'Plan no encontrado');
        }
        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/plan-emergencia/form', [
                'title'         => 'Editar Plan de Emergencia',
                'inspeccion'    => $inspeccion,
                'idCliente'     => $inspeccion['id_cliente'],
                'telefonos'     => self::TELEFONOS,
                'empresasAseo'  => self::EMPRESAS_ASEO,
            ]),
            'title' => 'Editar Plan Emergencia',
        ]);
    }

    public function update($id)
    {
        $inspeccion = $this->model->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/plan-emergencia')->with('error', 'No se puede editar');
        }

        $data = $this->getInspeccionPostData();

        // Fotos: subir nueva o mantener existente
        foreach (self::FOTO_FIELDS as $campo) {
            $nueva = $this->uploadFoto($campo, 'uploads/inspecciones/plan-emergencia/fotos/');
            if ($nueva) {
                if (!empty($inspeccion[$campo]) && file_exists(FCPATH . $inspeccion[$campo])) {
                    unlink(FCPATH . $inspeccion[$campo]);
                }
                $data[$campo] = $nueva;
            }
        }

        $this->model->update($id, $data);

        if ($this->request->getPost('finalizar')) {
            return $this->finalizar($id);
        }

        return redirect()->to('/inspecciones/plan-emergencia/edit/' . $id)
            ->with('msg', 'Plan actualizado');
    }

    public function view($id)
    {
        $inspeccion = $this->model->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/plan-emergencia')->with('error', 'No encontrado');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/plan-emergencia/view', [
                'title'        => 'Ver Plan de Emergencia',
                'inspeccion'   => $inspeccion,
                'cliente'      => $clientModel->find($inspeccion['id_cliente']),
                'consultor'    => $consultantModel->find($inspeccion['id_consultor']),
                'telefonos'    => self::TELEFONOS,
                'empresasAseo' => self::EMPRESAS_ASEO,
            ]),
            'title' => 'Ver Plan Emergencia',
        ]);
    }

    public function finalizar($id)
    {
        $inspeccion = $this->model->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/plan-emergencia')->with('error', 'No encontrado');
        }

        // Verificar que todas las inspecciones previas estan completas
        $faltantes = $this->verificarInspeccionesCompletas($inspeccion['id_cliente'], $inspeccion);
        if (!empty($faltantes)) {
            $lista = implode(', ', $faltantes);
            return redirect()->back()->with('error', 'Faltan inspecciones completas para este cliente: ' . $lista);
        }

        $pdfPath = $this->generarPdfInterno($id);
        if (!$pdfPath) {
            return redirect()->back()->with('error', 'Error al generar PDF');
        }

        $this->model->update($id, [
            'estado'   => 'completo',
            'ruta_pdf' => $pdfPath,
        ]);

        $inspeccion = $this->model->find($id);
        $this->uploadToReportes($inspeccion, $pdfPath);

        return redirect()->to('/inspecciones/plan-emergencia/view/' . $id)
            ->with('msg', 'Plan de Emergencia finalizado y PDF generado');
    }

    public function generatePdf($id)
    {
        $inspeccion = $this->model->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/plan-emergencia')->with('error', 'No encontrado');
        }

        $pdfPath = $this->generarPdfInterno($id);
        $this->model->update($id, ['ruta_pdf' => $pdfPath]);
        $fullPath = FCPATH . $pdfPath;
        if (!file_exists($fullPath)) {
            return redirect()->back()->with('error', 'PDF no encontrado');
        }

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="plan_emergencia_' . $id . '.pdf"')
            ->setBody(file_get_contents($fullPath));
    }

    public function delete($id)
    {
        $inspeccion = $this->model->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/plan-emergencia')->with('error', 'No encontrado');
        }
        // Eliminar fotos
        foreach (self::FOTO_FIELDS as $campo) {
            if (!empty($inspeccion[$campo]) && file_exists(FCPATH . $inspeccion[$campo])) {
                unlink(FCPATH . $inspeccion[$campo]);
            }
        }

        if (!empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) {
            unlink(FCPATH . $inspeccion['ruta_pdf']);
        }

        $this->model->delete($id);
        return redirect()->to('/inspecciones/plan-emergencia')->with('msg', 'Plan eliminado');
    }

    public function checkInspeccionesCompletas($idCliente)
    {
        $faltantes = $this->verificarInspeccionesCompletas((int)$idCliente);
        return $this->response->setJSON([
            'completas' => empty($faltantes),
            'faltantes' => $faltantes,
        ]);
    }

    // ===== METODOS PRIVADOS =====

        public function regenerarPdf($id)
    {
        $inspeccion = $this->model->find($id);
        if (!$inspeccion || ($inspeccion['estado'] ?? '') !== 'completo') {
            return redirect()->to('/inspecciones/plan-emergencia')->with('error', 'Solo se puede regenerar un registro finalizado.');
        }

        $pdfPath = $this->generarPdfInterno($id);

        $this->model->update($id, [
            'ruta_pdf' => $pdfPath,
        ]);

        $inspeccion = $this->model->find($id);
        $this->uploadToReportes($inspeccion, $pdfPath);

        return redirect()->to("/inspecciones/plan-emergencia/view/{$id}")->with('msg', 'PDF regenerado exitosamente.');
    }

    private function getInspeccionPostData(): array
    {
        $post = $this->request;

        $data = [
            'id_cliente'    => $post->getPost('id_cliente'),
            'fecha_visita'  => $post->getPost('fecha_visita'),
        ];

        // ENUMs con validacion
        $enumFields = [
            'casas_o_apartamentos'          => ['casas', 'apartamentos'],
            'tiene_gabinetes_hidraulico'    => ['si', 'no'],
            'hay_parqueadero_privado'       => ['si', 'no'],
            'tiene_oficina_admin'           => ['si', 'no'],
            'registro_visitantes_emergencia' => ['si', 'no'],
            'cuenta_megafono'               => ['si', 'no'],
            'ciudad'                        => ['bogota', 'soacha'],
            'empresa_aseo'                  => array_keys(self::EMPRESAS_ASEO),
        ];

        foreach ($enumFields as $field => $validValues) {
            $val = $post->getPost($field);
            $data[$field] = in_array($val, $validValues) ? $val : null;
        }

        // SMALLINT fields
        $intFields = [
            'anio_construccion', 'numero_torres', 'numero_unidades_habitacionales',
            'parqueaderos_carros_residentes', 'parqueaderos_carros_visitantes',
            'parqueaderos_motos_residentes', 'parqueaderos_motos_visitantes',
            'cantidad_salones_comunales', 'cantidad_locales_comerciales',
        ];

        foreach ($intFields as $field) {
            $val = $post->getPost($field);
            $data[$field] = ($val !== null && $val !== '') ? (int)$val : null;
        }

        // VARCHAR fields
        $varcharFields = [
            'sismo_resistente', 'casas_pisos',
            'cai_cercano', 'bomberos_cercanos',
            'proveedor_vigilancia', 'proveedor_aseo',
            'nombre_administrador', 'horarios_administracion',
            'cuadrante', 'frecuencia_basura',
        ];

        foreach ($varcharFields as $field) {
            $val = $post->getPost($field);
            $data[$field] = $val ? trim($val) : null;
        }

        // TEXT fields
        $textFields = [
            'tanque_agua', 'planta_electrica',
            'circulacion_vehicular', 'circulacion_peatonal',
            'salidas_emergencia', 'ingresos_peatonales', 'accesos_vehiculares',
            'concepto_entradas_salidas', 'hidrantes',
            'otros_proveedores',
            'registro_visitantes_forma',
            'ruta_evacuacion', 'mapa_evacuacion',
            'puntos_encuentro', 'sistema_alarma', 'codigos_alerta',
            'energia_emergencia', 'deteccion_fuego', 'vias_transito',
            'personal_aseo', 'personal_vigilancia',
            'ruta_residuos_solidos', 'servicios_sanitarios',
            'detalle_mascotas', 'detalle_dependencias',
            'observaciones',
        ];

        foreach ($textFields as $field) {
            $val = $post->getPost($field);
            $data[$field] = $val ? trim($val) : null;
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

    private function verificarInspeccionesCompletas(int $idCliente, ?array $inspeccion = null): array
    {
        $faltantes = [];

        $checks = [
            'Inspeccion Locativa'        => new InspeccionLocativaModel(),
            'Matriz de Vulnerabilidad'   => new MatrizVulnerabilidadModel(),
            'Probabilidad de Peligros'   => new ProbabilidadPeligrosModel(),
            'Revision de Extintores'     => new InspeccionExtintoresModel(),
            'Revision de Botiquines'     => new InspeccionBotiquinModel(),
            'Recursos de Seguridad'      => new InspeccionRecursosSeguridadModel(),
            'Equipos de Comunicaciones'  => new InspeccionComunicacionModel(),
        ];

        foreach ($checks as $nombre => $model) {
            $existe = $model->where('id_cliente', $idCliente)
                ->where('estado', 'completo')
                ->first();
            if (!$existe) {
                $faltantes[] = $nombre;
            }
        }

        // Gabinetes es condicional: solo si tiene_gabinetes_hidraulico = 'si'
        $tieneGabinetes = $inspeccion['tiene_gabinetes_hidraulico'] ?? null;
        if ($tieneGabinetes === 'si') {
            $gabModel = new InspeccionGabineteModel();
            $existe = $gabModel->where('id_cliente', $idCliente)
                ->where('estado', 'completo')
                ->first();
            if (!$existe) {
                $faltantes[] = 'Revision de Gabinetes Contra Incendio';
            }
        }

        return $faltantes;
    }

    private function generarPdfInterno(int $id): ?string
    {
        $inspeccion = $this->model->find($id);
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $cliente = $clientModel->find($inspeccion['id_cliente']);
        $consultor = $consultantModel->find($inspeccion['id_consultor']);

        // Logo en base64
        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoMime = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        // Fotos del plan a base64
        $fotosBase64 = [];
        foreach (self::FOTO_FIELDS as $campo) {
            $fotosBase64[$campo] = '';
            if (!empty($inspeccion[$campo])) {
                $fotoPath = FCPATH . $inspeccion[$campo];
                if (file_exists($fotoPath)) {
                    $mime = mime_content_type($fotoPath);
                    $fotosBase64[$campo] = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fotoPath));
                }
            }
        }

        // Cargar datos de inspecciones previas del mismo cliente
        $idCliente = $inspeccion['id_cliente'];

        $locativaModel = new InspeccionLocativaModel();
        $ultimaLocativa = $locativaModel->where('id_cliente', $idCliente)
            ->where('estado', 'completo')
            ->orderBy('fecha_inspeccion', 'DESC')->first();

        $matrizModel = new MatrizVulnerabilidadModel();
        $ultimaMatriz = $matrizModel->where('id_cliente', $idCliente)
            ->where('estado', 'completo')
            ->orderBy('fecha_inspeccion', 'DESC')->first();

        $probModel = new ProbabilidadPeligrosModel();
        $ultimaProb = $probModel->where('id_cliente', $idCliente)
            ->where('estado', 'completo')
            ->orderBy('fecha_inspeccion', 'DESC')->first();

        $extModel = new InspeccionExtintoresModel();
        $ultimaExt = $extModel->where('id_cliente', $idCliente)
            ->where('estado', 'completo')
            ->orderBy('fecha_inspeccion', 'DESC')->first();

        $botModel = new InspeccionBotiquinModel();
        $ultimaBot = $botModel->where('id_cliente', $idCliente)
            ->where('estado', 'completo')
            ->orderBy('fecha_inspeccion', 'DESC')->first();

        $recModel = new InspeccionRecursosSeguridadModel();
        $ultimaRec = $recModel->where('id_cliente', $idCliente)
            ->where('estado', 'completo')
            ->orderBy('fecha_inspeccion', 'DESC')->first();

        $comModel = new InspeccionComunicacionModel();
        $ultimaCom = $comModel->where('id_cliente', $idCliente)
            ->where('estado', 'completo')
            ->orderBy('fecha_inspeccion', 'DESC')->first();

        $gabModel = new InspeccionGabineteModel();
        $ultimaGab = null;
        if ($inspeccion['tiene_gabinetes_hidraulico'] === 'si') {
            $ultimaGab = $gabModel->where('id_cliente', $idCliente)
                ->where('estado', 'completo')
                ->orderBy('fecha_inspeccion', 'DESC')->first();
        }

        // Hallazgos de la locativa (si existe)
        $hallazgosLocativa = [];
        if ($ultimaLocativa) {
            $db = \Config\Database::connect();
            $hallazgosLocativa = $db->table('tbl_hallazgos_locativa')
                ->where('id_inspeccion', $ultimaLocativa['id'])
                ->orderBy('orden', 'ASC')
                ->get()->getResultArray();
        }

        // Diagrama de emergencias (imagen estatica)
        $diagramaPath = FCPATH . 'uploads/imagenesplanemergencias/emergencias1.jpg';
        $diagramaBase64 = null;
        if (file_exists($diagramaPath)) {
            $diagramaMime = mime_content_type($diagramaPath);
            $diagramaBase64 = 'data:' . $diagramaMime . ';base64,' . base64_encode(file_get_contents($diagramaPath));
        }

        $data = [
            'inspeccion'         => $inspeccion,
            'cliente'            => $cliente,
            'consultor'          => $consultor,
            'logoBase64'         => $logoBase64,
            'fotosBase64'        => $fotosBase64,
            'telefonos'          => self::TELEFONOS,
            'empresasAseo'       => self::EMPRESAS_ASEO,
            'diagramaBase64'     => $diagramaBase64,
            'ultimaLocativa'     => $ultimaLocativa,
            'hallazgosLocativa'  => $hallazgosLocativa,
            'ultimaMatriz'       => $ultimaMatriz,
            'ultimaProb'         => $ultimaProb,
            'ultimaExt'          => $ultimaExt,
            'ultimaBot'          => $ultimaBot,
            'ultimaRec'          => $ultimaRec,
            'ultimaCom'          => $ultimaCom,
            'ultimaGab'          => $ultimaGab,
        ];

        $html = view('inspecciones/plan-emergencia/pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $pdfDir = 'uploads/inspecciones/plan-emergencia/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) {
            mkdir(FCPATH . $pdfDir, 0755, true);
        }

        $pdfFileName = 'plan_emergencia_' . $id . '_' . date('Ymd_His') . '.pdf';
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
            ->where('id_detailreport', 19)
            ->like('observaciones', 'plan_emg_id:' . $inspeccion['id'])
            ->first();

        $destDir = ROOTPATH . 'public/uploads/' . $nitCliente;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $fileName = 'plan_emergencia_' . $inspeccion['id'] . '_' . date('Ymd_His') . '.pdf';
        $destPath = $destDir . '/' . $fileName;
        copy(FCPATH . $pdfPath, $destPath);

        $data = [
            'titulo_reporte'  => 'PLAN DE EMERGENCIA - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $inspeccion['fecha_visita'],
            'id_detailreport' => 19,
            'id_report_type'  => 6,
            'id_cliente'      => $inspeccion['id_cliente'],
            'estado'          => 'Activo',
            'observaciones'   => 'Generado automaticamente desde modulo de inspecciones. plan_emg_id:' . $inspeccion['id'],
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
