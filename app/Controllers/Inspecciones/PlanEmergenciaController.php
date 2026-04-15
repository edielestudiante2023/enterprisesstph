<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\PlanEmergenciaModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use App\Libraries\InspeccionEmailNotifier;
use App\Models\InspeccionLocativaModel;
use App\Models\MatrizVulnerabilidadModel;
use App\Models\ProbabilidadPeligrosModel;
use App\Models\InspeccionExtintoresModel;
use App\Models\InspeccionBotiquinModel;
use App\Models\InspeccionRecursosSeguridadModel;
use App\Models\InspeccionComunicacionModel;
use App\Models\InspeccionGabineteModel;
use Dompdf\Dompdf;
use App\Traits\AutosaveJsonTrait;
use App\Traits\ImagenCompresionTrait;

class PlanEmergenciaController extends BaseController
{
    use AutosaveJsonTrait;
    use ImagenCompresionTrait;
    use \App\Traits\PreventDuplicateBorradorTrait;
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
        $inspecciones = $this->model
            ->select('tbl_plan_emergencia.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_plan_emergencia.id_cliente', 'left')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_plan_emergencia.id_consultor', 'left')
            ->orderBy('tbl_plan_emergencia.fecha_visita', 'DESC')
            ->findAll();

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
        $existing = $this->reuseExistingBorrador($this->model, 'fecha_visita', '/inspecciones/plan-emergencia/edit/');
        if ($existing) return $existing;

        $userId = session()->get('user_id');
        $isAutosave = $this->isAutosaveRequest();

        if (!$isAutosave) {
            if (!$this->validate(['id_cliente' => 'required|integer', 'fecha_visita' => 'required|valid_date'])) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }
        }

        $data = $this->getInspeccionPostData();
        $data['id_consultor'] = $userId;
        $data['estado'] = 'borrador';

        // Fotos (solo en submit normal, no en autosave)
        if (!$isAutosave) {
            foreach (self::FOTO_FIELDS as $campo) {
                $foto = $this->uploadFoto($campo, 'uploads/inspecciones/plan-emergencia/fotos/');
                if ($foto) {
                    $data[$campo] = $foto;
                }
            }
        }

        $this->model->insert($data);
        $idPlan = $this->model->getInsertID();

        if ($isAutosave) {
            return $this->autosaveJsonSuccess($idPlan);
        }

        // Si el consultor presiono "Guardar y Revisar con IA" lo llevamos a la vista de revision
        if ($this->request->getPost('ir_ia_review')) {
            return redirect()->to('/inspecciones/plan-emergencia/ia-review/' . $idPlan)
                ->with('msg', 'Plan guardado. Ahora puedes revisar y generar el contenido con IA.');
        }

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
            if ($this->isAutosaveRequest()) {
                return $this->autosaveJsonError('No encontrada', 404);
            }
            return redirect()->to('/inspecciones/plan-emergencia')->with('error', 'No se puede editar');
        }

        $data = $this->getInspeccionPostData();

        // Fotos: subir nueva o mantener existente (solo en submit normal)
        if (!$this->isAutosaveRequest()) {
            foreach (self::FOTO_FIELDS as $campo) {
                $nueva = $this->uploadFoto($campo, 'uploads/inspecciones/plan-emergencia/fotos/');
                if ($nueva) {
                    if (!empty($inspeccion[$campo]) && file_exists(FCPATH . $inspeccion[$campo])) {
                        unlink(FCPATH . $inspeccion[$campo]);
                    }
                    $data[$campo] = $nueva;
                }
            }
        }

        $this->model->update($id, $data);

        if ($this->request->getPost('finalizar')) {
            return $this->finalizar($id);
        }

        if ($this->isAutosaveRequest()) {
            return $this->autosaveJsonSuccess((int)$id);
        }

        // Si el consultor presiono "Guardar y Revisar con IA" lo llevamos a la vista de revision
        if ($this->request->getPost('ir_ia_review')) {
            return redirect()->to('/inspecciones/plan-emergencia/ia-review/' . $id)
                ->with('msg', 'Cambios guardados. Revisa y genera el contenido con IA.');
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

        // El contenido IA (PONs, Diagrama, Matriz, Brigada) debe haberse generado y aprobado
        // previamente en la Vista de Revision IA (/ia-review/{id}). Aqui solo generamos el
        // PDF con el contenido actualmente en BD (puede estar parcial — el pdf.php omite
        // las secciones que no tienen contenido para evitar espacios muertos).

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

        // Enviar email con PDF adjunto
        $emailResult = InspeccionEmailNotifier::enviar(
            (int) $inspeccion['id_cliente'],
            (int) $inspeccion['id_consultor'],
            'PLAN DE EMERGENCIA',
            $inspeccion['fecha_visita'],
            $pdfPath,
            (int) $inspeccion['id'],
            'PlanEmergencia'
        );
        $msg = 'Plan de Emergencia finalizado y PDF generado.';
        if ($emailResult['success']) {
            $msg .= ' ' . $emailResult['message'];
        } else {
            $msg .= ' (Email no enviado: ' . $emailResult['error'] . ')';
        }

        return redirect()->to('/inspecciones/plan-emergencia/view/' . $id)
            ->with('msg', $msg);
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

        $this->servirPdf($fullPath, 'plan_emergencia_' . $id . '.pdf');
        return;
    }

    public function debugPdf()
    {
        $logoPath = FCPATH . 'uploads/logoenterprisesstdorado.jpg';
        $logoBase64 = null;
        if (file_exists($logoPath)) {
            $mime = mime_content_type($logoPath);
            $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
        }

        $cliente = [
            'nombre_cliente'    => 'CONJUNTO RESIDENCIAL DEMO DEBUG',
            'direccion_cliente' => 'Calle 123 #45-67, Bogota (DATOS DE PRUEBA)',
            'logo'              => 'logoenterprisesstdorado.jpg',
        ];

        $consultor = [
            'nombre_consultor' => 'Consultor Demo',
            'email_consultor'  => 'demo@test.com',
        ];

        $inspeccion = [
            'id'                             => 0,
            'id_cliente'                     => 0,
            'ciudad'                         => 'bogota',
            'cuadrante'                      => 'CAI DEMO - Cuadrante 99',
            'tiene_gabinetes_hidraulico'     => 'si',
            'casas_o_apartamentos'           => 'apartamentos',
            'numero_torres'                  => 4,
            'casas_pisos'                    => '-',
            'sismo_resistente'               => 'SI',
            'anio_construccion'              => 2010,
            'numero_unidades_habitacionales' => 120,
            'parqueaderos_carros_residentes' => 80,
            'parqueaderos_carros_visitantes' => 10,
            'parqueaderos_motos_residentes'  => 30,
            'parqueaderos_motos_visitantes'  => 5,
            'hay_parqueadero_privado'        => 'si',
            'cantidad_salones_comunales'     => 2,
            'cantidad_locales_comerciales'   => 3,
            'tiene_oficina_admin'            => 'si',
            'tanque_agua'                    => 'Subterraneo y elevado',
            'planta_electrica'               => 'SI - diesel 100kVA',
            'concepto_entradas_salidas'      => 'Dos entradas vehiculares y una peatonal.',
            'hidrantes'                      => 'Hidrante publico a 50m.',
            'cai_cercano'                    => 'CAI Demo',
            'bomberos_cercanos'              => 'Estacion B-5',
            'proveedor_vigilancia'           => 'Vigilancia Demo SAS',
            'proveedor_aseo'                 => 'Aseo Demo SAS',
            'otros_proveedores'              => 'Mantenimiento ascensores',
            'registro_visitantes_forma'      => 'Sistema digital',
            'registro_visitantes_emergencia' => 'si',
            'cuenta_megafono'                => 'si',
            'ruta_evacuacion'                => 'Por escaleras de emergencia hacia el parqueadero.',
            'mapa_evacuacion'                => 'Publicado en cada piso.',
            'puntos_encuentro'               => 'Parqueadero de visitantes.',
            'sistema_alarma'                 => 'Sirena electronica',
            'codigos_alerta'                 => 'Codigo rojo / verde / amarillo',
            'energia_emergencia'             => 'UPS 2h + planta',
            'deteccion_fuego'                => 'Detectores de humo',
            'vias_transito'                  => 'Calle 123 y Cra 45',
            'nombre_administrador'           => 'Admin Demo',
            'horarios_administracion'        => 'L-V 8am-5pm',
            'personal_aseo'                  => '3 personas',
            'personal_vigilancia'            => '4 personas',
            'ruta_residuos_solidos'          => 'Cuarto de basuras',
            'empresa_aseo'                   => 'bogota_limpia',
            'servicios_sanitarios'           => 'Baños en cada piso',
            'frecuencia_basura'              => '3 veces por semana',
            'detalle_mascotas'               => 'Permitidas con registro',
            'detalle_dependencias'           => 'Salones, gym, piscina',
            'observaciones'                  => 'Observaciones demo del plan de emergencia (DATO INVENTADO).',
            'fecha_visita'                   => date('Y-m-d'),
            'fecha_inspeccion'               => date('Y-m-d'),
        ];

        $fotosBase64 = [
            'foto_fachada'  => $logoBase64,
            'foto_panorama' => $logoBase64,
        ];

        $dummyLocativa = [
            'id' => 1, 'estado' => 'completo',
            'fecha_inspeccion' => date('Y-m-d'),
        ];
        $dummyHallazgos = [
            ['descripcion_imagen' => 'Hallazgo demo 1 (DATO INVENTADO)', 'fecha_registro' => date('Y-m-d'), 'imagen' => null],
            ['descripcion_imagen' => 'Hallazgo demo 2 (DATO INVENTADO)', 'fecha_registro' => date('Y-m-d'), 'imagen' => null],
        ];

        $dummyMatriz = [
            'fecha_inspeccion' => date('Y-m-d'),
            'observaciones' => 'Observaciones demo de la matriz de vulnerabilidad (DATO INVENTADO).',
        ];
        foreach (['c1_plan_evacuacion','c2_alarma_evacuacion','c3_ruta_evacuacion','c4_visitantes_rutas','c5_puntos_reunion','c6_puntos_reunion_2','c7_senalizacion_evacuacion','c8_rutas_evacuacion','c9_ruta_principal','c10_senal_alarma','c11_sistema_deteccion','c12_iluminacion','c13_iluminacion_emergencia','c14_sistema_contra_incendio','c15_extintores','c16_divulgacion_plan','c17_coordinador_plan','c18_brigada_emergencia','c19_simulacros','c20_entidades_socorro','c21_ocupantes','c22_plano_evacuacion','c23_rutas_circulacion','c24_puertas_salida','c25_estructura_construccion'] as $i => $k) {
            $dummyMatriz[$k] = ['a','b','c'][$i % 3];
        }

        $dummyProb = [
            'fecha_inspeccion' => date('Y-m-d'),
            'p_sismos' => 'probable', 'p_inundaciones' => 'poco_probable', 'p_vendavales' => 'poco_probable',
            'p_atentados' => 'poco_probable', 'p_asalto_hurto' => 'probable', 'p_vandalismo' => 'probable',
            'p_incendios' => 'muy_probable', 'p_explosiones' => 'poco_probable', 'p_inhalacion_gases' => 'poco_probable',
            'p_falla_estructural' => 'poco_probable', 'p_intoxicacion_alimentos' => 'poco_probable', 'p_densidad_poblacional' => 'probable',
        ];

        $dummyExt = [
            'fecha_inspeccion' => date('Y-m-d'),
            'fecha_vencimiento_global' => date('Y-m-d', strtotime('+1 year')),
            'numero_extintores_totales' => 5,
            'cantidad_abc' => 3, 'cantidad_co2' => 1, 'cantidad_solkaflam' => 1, 'cantidad_agua' => 0,
            'capacidad_libras' => '10',
            'recomendaciones_generales' => 'Recomendacion demo extintores (DATO INVENTADO).',
        ];

        $dummyBot = [
            'fecha_inspeccion' => date('Y-m-d'),
            'ubicacion_botiquin' => 'Porteria principal',
            'instalado_pared' => 'si', 'libre_obstaculos' => 'si', 'lugar_visible' => 'si',
            'con_senalizacion' => 'si', 'estado_botiquin' => 'bueno',
            'recomendaciones_inspeccion' => 'Recomendacion demo botiquin (DATO INVENTADO).',
        ];

        $dummyRec = [
            'fecha_inspeccion' => date('Y-m-d'),
            'obs_lamparas_emergencia' => 'Instaladas en todos los pisos',
            'obs_antideslizantes' => 'En escaleras principales',
            'obs_pasamanos' => 'En buen estado',
            'obs_planes_respuesta' => 'Documentados y divulgados',
            'observaciones' => 'Observaciones demo recursos (DATO INVENTADO).',
        ];

        $dummyCom = [
            'fecha_inspeccion' => date('Y-m-d'),
            'observaciones' => 'Observaciones demo comunicaciones (DATO INVENTADO).',
        ];

        $dummyGab = [
            'fecha_inspeccion' => date('Y-m-d'),
            'observaciones' => 'Observaciones demo gabinetes (DATO INVENTADO).',
        ];

        $data = [
            'inspeccion'         => $inspeccion,
            'cliente'            => $cliente,
            'consultor'          => $consultor,
            'logoBase64'         => $logoBase64,
            'fotosBase64'        => $fotosBase64,
            'telefonos'          => self::TELEFONOS,
            'empresasAseo'       => self::EMPRESAS_ASEO,
            'diagramaBase64'     => $logoBase64,
            'ultimaLocativa'     => $dummyLocativa,
            'hallazgosLocativa'  => $dummyHallazgos,
            'ultimaMatriz'       => $dummyMatriz,
            'ultimaProb'         => $dummyProb,
            'ultimaExt'          => $dummyExt,
            'ultimaBot'          => $dummyBot,
            'ultimaRec'          => $dummyRec,
            'ultimaCom'          => $dummyCom,
            'ultimaGab'          => $dummyGab,
            'ponsIaAdendo'       => [],
            'diagramaNodos'      => null,
            'matrizResponsablesIA' => null,
            'debugMode'          => true,
        ];

        return view('inspecciones/plan-emergencia/pdf', $data);
    }

    /**
     * Genera/regenera el adendo IA personalizado para los 10 PONs canonicos
     * de un Plan de Emergencia especifico. Guarda el JSON en pons_ia_json.
     *
     * Fase 2 - Plan de Emergencia.
     */
    public function enriquecerPONsConIA($id)
    {
        @set_time_limit(300);
        log_message('info', '[IA PONs] start id=' . $id . ' ajax=' . ($this->request->isAJAX() ? 'Y' : 'N'));

        $inspeccion = $this->model->find($id);
        if (!$inspeccion) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['ok' => false, 'error' => 'Plan no encontrado (id=' . $id . ')']);
            }
            return redirect()->to('/inspecciones/plan-emergencia')->with('error', 'Plan no encontrado');
        }

        $idCliente = (int) $inspeccion['id_cliente'];
        $cliente   = (new ClientModel())->find($idCliente);
        if (!$cliente) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['ok' => false, 'error' => 'Cliente no encontrado (id=' . $idCliente . ')']);
            }
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        // Cargar las inspecciones del cliente que sirven de contexto a la IA
        $contextoCliente = [
            'cliente'        => $cliente,
            'inspeccion'     => $inspeccion,
            'ultimaLocativa' => (new InspeccionLocativaModel())->where('id_cliente', $idCliente)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->first(),
            'ultimaMatriz'   => (new MatrizVulnerabilidadModel())->where('id_cliente', $idCliente)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->first(),
            'ultimaProb'     => (new ProbabilidadPeligrosModel())->where('id_cliente', $idCliente)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->first(),
            'ultimaExt'      => (new InspeccionExtintoresModel())->where('id_cliente', $idCliente)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->first(),
            'ultimaBot'      => (new InspeccionBotiquinModel())->where('id_cliente', $idCliente)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->first(),
            'ultimaRec'      => (new InspeccionRecursosSeguridadModel())->where('id_cliente', $idCliente)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->first(),
            'ultimaCom'      => (new InspeccionComunicacionModel())->where('id_cliente', $idCliente)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->first(),
            'ultimaGab'      => (new InspeccionGabineteModel())->where('id_cliente', $idCliente)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->first(),
        ];

        $ponesCanonicos = require APPPATH . 'Config/PonesCanonicos.php';

        $contextoExtra = $this->leerContextoIA($inspeccion, 'pons');

        try {
            $svc  = new \App\Libraries\PlanEmergenciaIAService();
            $resp = $svc->enriquecerPONs($contextoCliente, $ponesCanonicos, $contextoExtra);
        } catch (\Throwable $e) {
            log_message('error', '[IA PONs] EXCEPTION id=' . $id . ': ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['ok' => false, 'error' => 'Excepcion PHP: ' . $e->getMessage()]);
            }
            return redirect()->back()->with('error', 'Excepcion PHP: ' . $e->getMessage());
        }

        if (!$resp['ok']) {
            log_message('error', '[IA PONs] FAIL id=' . $id . ' resp=' . substr(json_encode($resp), 0, 500));
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['ok' => false, 'error' => $resp['error'] ?? ('Servicio retorno sin error: ' . substr(json_encode($resp), 0, 200))]);
            }
            return redirect()->back()->with('error', 'Error generando IA: ' . ($resp['error'] ?? 'desconocido'));
        }

        log_message('info', '[IA PONs] OK id=' . $id . ' tokens_in=' . ($resp['tokens']['in'] ?? 0) . ' tokens_out=' . ($resp['tokens']['out'] ?? 0));

        $this->model->update($id, [
            'pons_ia_json'   => json_encode($resp['data'], JSON_UNESCAPED_UNICODE),
            'ia_generado_at' => date('Y-m-d H:i:s'),
        ]);

        $tokensIn  = $resp['tokens']['in']  ?? 0;
        $tokensOut = $resp['tokens']['out'] ?? 0;
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['ok' => true, 'bloque' => 'pons', 'data' => $resp['data'], 'tokens' => ['in' => $tokensIn, 'out' => $tokensOut]]);
        }
        $msg = sprintf('PONs enriquecidos con IA. Tokens: %d in / %d out.', $tokensIn, $tokensOut);
        return redirect()->back()->with('msg', $msg);
    }

    /**
     * Genera el Diagrama de Actuacion como arbol JSON personalizado por IA.
     * Guarda en diagrama_ia_json.
     */
    public function generarDiagramaIA($id)
    {
        @set_time_limit(300);
        log_message('info', '[IA Diagrama] start id=' . $id . ' ajax=' . ($this->request->isAJAX() ? 'Y' : 'N'));

        $inspeccion = $this->model->find($id);
        if (!$inspeccion) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['ok' => false, 'error' => 'Plan no encontrado (id=' . $id . ')']);
            }
            return redirect()->to('/inspecciones/plan-emergencia')->with('error', 'Plan no encontrado');
        }

        $idCliente = (int) $inspeccion['id_cliente'];
        $cliente   = (new ClientModel())->find($idCliente);
        if (!$cliente) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['ok' => false, 'error' => 'Cliente no encontrado']);
            }
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $contextoCliente = [
            'cliente'    => $cliente,
            'inspeccion' => $inspeccion,
            'ultimaProb' => (new ProbabilidadPeligrosModel())->where('id_cliente', $idCliente)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->first(),
            'ultimaMatriz' => (new MatrizVulnerabilidadModel())->where('id_cliente', $idCliente)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->first(),
        ];

        $contextoExtra = $this->leerContextoIA($inspeccion, 'diagrama');

        try {
            $svc  = new \App\Libraries\PlanEmergenciaIAService();
            $resp = $svc->generarDiagramaActuacion($contextoCliente, $contextoExtra);
        } catch (\Throwable $e) {
            log_message('error', '[IA Diagrama] EXCEPTION id=' . $id . ': ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['ok' => false, 'error' => 'Excepcion PHP: ' . $e->getMessage()]);
            }
            return redirect()->back()->with('error', 'Excepcion PHP: ' . $e->getMessage());
        }

        if (!$resp['ok']) {
            log_message('error', '[IA Diagrama] FAIL id=' . $id . ' resp=' . substr(json_encode($resp), 0, 500));
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['ok' => false, 'error' => $resp['error'] ?? ('Servicio retorno sin error: ' . substr(json_encode($resp), 0, 200))]);
            }
            return redirect()->back()->with('error', 'Error generando IA: ' . ($resp['error'] ?? 'desconocido'));
        }

        log_message('info', '[IA Diagrama] OK id=' . $id . ' tokens_in=' . ($resp['tokens']['in'] ?? 0) . ' tokens_out=' . ($resp['tokens']['out'] ?? 0));

        $this->model->update($id, [
            'diagrama_ia_json' => json_encode($resp['data'], JSON_UNESCAPED_UNICODE),
            'ia_generado_at'   => date('Y-m-d H:i:s'),
        ]);

        $tokensIn  = $resp['tokens']['in']  ?? 0;
        $tokensOut = $resp['tokens']['out'] ?? 0;
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['ok' => true, 'bloque' => 'diagrama', 'data' => $resp['data'], 'tokens' => ['in' => $tokensIn, 'out' => $tokensOut]]);
        }
        return redirect()->back()->with('msg', sprintf('Diagrama de actuacion generado con IA. Tokens: %d in / %d out.', $tokensIn, $tokensOut));
    }

    /**
     * Genera la Matriz de Responsables del Plan personalizada por IA.
     * Guarda en matriz_responsables_ia_json.
     */
    public function generarMatrizResponsablesIA($id)
    {
        @set_time_limit(300);
        log_message('info', '[IA Matriz] start id=' . $id . ' ajax=' . ($this->request->isAJAX() ? 'Y' : 'N'));

        $inspeccion = $this->model->find($id);
        if (!$inspeccion) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['ok' => false, 'error' => 'Plan no encontrado (id=' . $id . ')']);
            }
            return redirect()->to('/inspecciones/plan-emergencia')->with('error', 'Plan no encontrado');
        }

        $idCliente = (int) $inspeccion['id_cliente'];
        $cliente   = (new ClientModel())->find($idCliente);
        if (!$cliente) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['ok' => false, 'error' => 'Cliente no encontrado']);
            }
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $contextoCliente = [
            'cliente'    => $cliente,
            'inspeccion' => $inspeccion,
        ];

        $contextoExtra = $this->leerContextoIA($inspeccion, 'matriz');

        try {
            $svc  = new \App\Libraries\PlanEmergenciaIAService();
            $resp = $svc->generarMatrizResponsables($contextoCliente, $contextoExtra);
        } catch (\Throwable $e) {
            log_message('error', '[IA Matriz] EXCEPTION id=' . $id . ': ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['ok' => false, 'error' => 'Excepcion PHP: ' . $e->getMessage()]);
            }
            return redirect()->back()->with('error', 'Excepcion PHP: ' . $e->getMessage());
        }

        if (!$resp['ok']) {
            log_message('error', '[IA Matriz] FAIL id=' . $id . ' resp=' . substr(json_encode($resp), 0, 500));
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['ok' => false, 'error' => $resp['error'] ?? ('Servicio retorno sin error: ' . substr(json_encode($resp), 0, 200))]);
            }
            return redirect()->back()->with('error', 'Error generando IA: ' . ($resp['error'] ?? 'desconocido'));
        }

        log_message('info', '[IA Matriz] OK id=' . $id . ' tokens_in=' . ($resp['tokens']['in'] ?? 0) . ' tokens_out=' . ($resp['tokens']['out'] ?? 0));

        $this->model->update($id, [
            'matriz_responsables_ia_json' => json_encode($resp['data'], JSON_UNESCAPED_UNICODE),
            'ia_generado_at'              => date('Y-m-d H:i:s'),
        ]);

        $tokensIn  = $resp['tokens']['in']  ?? 0;
        $tokensOut = $resp['tokens']['out'] ?? 0;
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['ok' => true, 'bloque' => 'matriz', 'data' => $resp['data'], 'tokens' => ['in' => $tokensIn, 'out' => $tokensOut]]);
        }
        return redirect()->back()->with('msg', sprintf('Matriz de responsables generada con IA. Tokens: %d in / %d out.', $tokensIn, $tokensOut));
    }

    /**
     * Genera el texto de Brigada y Simulacros personalizado por IA.
     * Lee la inspeccion de Brigada+Simulacros del cliente y enriquece con Claude.
     * Guarda en brigada_ia_texto y simulacros_ia_texto.
     */
    public function generarBrigadaSimulacrosIA($id)
    {
        @set_time_limit(300);
        log_message('info', '[IA Brigada] start id=' . $id . ' ajax=' . ($this->request->isAJAX() ? 'Y' : 'N'));

        $inspeccion = $this->model->find($id);
        if (!$inspeccion) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['ok' => false, 'error' => 'Plan no encontrado (id=' . $id . ')']);
            }
            return redirect()->to('/inspecciones/plan-emergencia')->with('error', 'Plan no encontrado');
        }

        $idCliente = (int) $inspeccion['id_cliente'];
        $cliente   = (new ClientModel())->find($idCliente);
        if (!$cliente) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['ok' => false, 'error' => 'Cliente no encontrado']);
            }
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        // Cargar la ultima inspeccion de Brigada+Simulacros del cliente (puede ser null)
        $brigadaInspeccion = null;
        if (class_exists('\\App\\Models\\InspeccionBrigadaSimulacrosModel')) {
            $modelClass = '\\App\\Models\\InspeccionBrigadaSimulacrosModel';
            $brigadaInspeccion = (new $modelClass())->where('id_cliente', $idCliente)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->first();
        }

        $contextoCliente = [
            'cliente'           => $cliente,
            'inspeccion'        => $inspeccion,
            'brigadaSimulacros' => $brigadaInspeccion ?: [],
        ];

        $contextoExtra = $this->leerContextoIA($inspeccion, 'brigada');

        try {
            $svc  = new \App\Libraries\PlanEmergenciaIAService();
            $resp = $svc->generarBrigadaSimulacros($contextoCliente, $contextoExtra);
        } catch (\Throwable $e) {
            log_message('error', '[IA Brigada] EXCEPTION id=' . $id . ': ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['ok' => false, 'error' => 'Excepcion PHP: ' . $e->getMessage()]);
            }
            return redirect()->back()->with('error', 'Excepcion PHP: ' . $e->getMessage());
        }

        if (!$resp['ok']) {
            log_message('error', '[IA Brigada] FAIL id=' . $id . ' resp=' . substr(json_encode($resp), 0, 500));
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['ok' => false, 'error' => $resp['error'] ?? ('Servicio retorno sin error: ' . substr(json_encode($resp), 0, 200))]);
            }
            return redirect()->back()->with('error', 'Error generando IA: ' . ($resp['error'] ?? 'desconocido'));
        }

        log_message('info', '[IA Brigada] OK id=' . $id . ' tokens_in=' . ($resp['tokens']['in'] ?? 0) . ' tokens_out=' . ($resp['tokens']['out'] ?? 0));

        $data = $resp['data'];
        $this->model->update($id, [
            'brigada_ia_texto'    => $data['brigada_texto']    ?? null,
            'simulacros_ia_texto' => $data['simulacros_texto'] ?? null,
            'ia_generado_at'      => date('Y-m-d H:i:s'),
        ]);

        $tokensIn  = $resp['tokens']['in']  ?? 0;
        $tokensOut = $resp['tokens']['out'] ?? 0;
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['ok' => true, 'bloque' => 'brigada', 'data' => $data, 'tokens' => ['in' => $tokensIn, 'out' => $tokensOut]]);
        }
        return redirect()->back()->with('msg', sprintf('Brigada y Simulacros generados con IA. Tokens: %d in / %d out.', $tokensIn, $tokensOut));
    }

    /**
     * Helper: lee el contexto adicional escrito por el consultor desde la
     * columna ia_contexto_json para un bloque especifico (pons|diagrama|matriz|brigada).
     * Retorna string vacio si no hay contexto.
     */
    private function leerContextoIA(array $inspeccion, string $bloque): string
    {
        if (empty($inspeccion['ia_contexto_json'])) return '';
        $decoded = json_decode($inspeccion['ia_contexto_json'], true);
        if (!is_array($decoded)) return '';
        return (string) ($decoded[$bloque] ?? '');
    }

    /**
     * Vista de Revision IA — el consultor ve los 4 bloques, agrega contexto
     * adicional, genera/regenera y aprueba cada uno antes de finalizar el plan.
     */
    public function iaReview($id)
    {
        $inspeccion = $this->model->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/plan-emergencia')->with('error', 'Plan no encontrado');
        }

        $cliente = (new ClientModel())->find($inspeccion['id_cliente']);

        // Decodificar estado actual
        $ponsIaAdendo = !empty($inspeccion['pons_ia_json']) ? json_decode($inspeccion['pons_ia_json'], true) : [];
        $diagramaNodos = !empty($inspeccion['diagrama_ia_json']) ? json_decode($inspeccion['diagrama_ia_json'], true) : null;
        $matrizResponsablesIA = !empty($inspeccion['matriz_responsables_ia_json']) ? json_decode($inspeccion['matriz_responsables_ia_json'], true) : null;
        $contextoIA = !empty($inspeccion['ia_contexto_json']) ? json_decode($inspeccion['ia_contexto_json'], true) : [];
        $aprobadoIA = !empty($inspeccion['ia_aprobado_json']) ? json_decode($inspeccion['ia_aprobado_json'], true) : [];

        $ponesCanonicos = require APPPATH . 'Config/PonesCanonicos.php';

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/plan-emergencia/ia-review', [
                'inspeccion'          => $inspeccion,
                'cliente'             => $cliente,
                'ponsIaAdendo'        => is_array($ponsIaAdendo) ? $ponsIaAdendo : [],
                'diagramaNodos'       => $diagramaNodos,
                'matrizResponsablesIA'=> $matrizResponsablesIA,
                'contextoIA'          => is_array($contextoIA) ? $contextoIA : [],
                'aprobadoIA'          => is_array($aprobadoIA) ? $aprobadoIA : [],
                'ponesCanonicos'      => $ponesCanonicos,
                'title'               => 'Revision IA - Plan de Emergencia',
            ]),
            'title' => 'Revision IA',
        ]);
    }

    /**
     * Guarda el contexto adicional para un bloque especifico.
     * POST con: bloque=pons|diagrama|matriz|brigada, contexto=texto
     */
    public function iaSaveContexto($id)
    {
        $inspeccion = $this->model->find($id);
        if (!$inspeccion) {
            return $this->response->setJSON(['ok' => false, 'error' => 'Plan no encontrado'])->setStatusCode(404);
        }

        $bloque   = (string) $this->request->getPost('bloque');
        $contexto = (string) $this->request->getPost('contexto');
        $bloquesValidos = ['pons', 'diagrama', 'matriz', 'brigada'];
        if (!in_array($bloque, $bloquesValidos, true)) {
            return $this->response->setJSON(['ok' => false, 'error' => 'Bloque invalido'])->setStatusCode(400);
        }

        $actual = !empty($inspeccion['ia_contexto_json']) ? json_decode($inspeccion['ia_contexto_json'], true) : [];
        if (!is_array($actual)) $actual = [];
        $actual[$bloque] = $contexto;

        $this->model->update($id, [
            'ia_contexto_json' => json_encode($actual, JSON_UNESCAPED_UNICODE),
        ]);

        return $this->response->setJSON(['ok' => true]);
    }

    /**
     * Marca o desmarca un bloque como aprobado.
     * POST con: bloque=pons|diagrama|matriz|brigada, aprobado=1|0
     */
    public function iaAprobar($id)
    {
        $inspeccion = $this->model->find($id);
        if (!$inspeccion) {
            return $this->response->setJSON(['ok' => false, 'error' => 'Plan no encontrado'])->setStatusCode(404);
        }

        $bloque   = (string) $this->request->getPost('bloque');
        $aprobado = (bool) $this->request->getPost('aprobado');
        $bloquesValidos = ['pons', 'diagrama', 'matriz', 'brigada'];
        if (!in_array($bloque, $bloquesValidos, true)) {
            return $this->response->setJSON(['ok' => false, 'error' => 'Bloque invalido'])->setStatusCode(400);
        }

        $actual = !empty($inspeccion['ia_aprobado_json']) ? json_decode($inspeccion['ia_aprobado_json'], true) : [];
        if (!is_array($actual)) $actual = [];
        $actual[$bloque] = $aprobado;

        $this->model->update($id, [
            'ia_aprobado_json' => json_encode($actual, JSON_UNESCAPED_UNICODE),
        ]);

        return $this->response->setJSON(['ok' => true, 'aprobados' => $actual]);
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
        $idCliente = (int) $idCliente;

        $checks = [
            'locativa'        => ['label' => 'Locativa',        'model' => new InspeccionLocativaModel()],
            'extintores'      => ['label' => 'Extintores',      'model' => new InspeccionExtintoresModel()],
            'botiquin'        => ['label' => 'Botiquín',        'model' => new InspeccionBotiquinModel()],
            'gabinetes'       => ['label' => 'Gabinetes',       'model' => new InspeccionGabineteModel()],
            'comunicaciones'  => ['label' => 'Comunicaciones',  'model' => new InspeccionComunicacionModel()],
            'recursos'        => ['label' => 'Rec. Seguridad',  'model' => new InspeccionRecursosSeguridadModel()],
            'probabilidad'    => ['label' => 'Prob. Peligros',  'model' => new ProbabilidadPeligrosModel()],
            'matriz'          => ['label' => 'Matriz Vuln.',    'model' => new MatrizVulnerabilidadModel()],
        ];

        $modulos  = [];
        $faltantes = [];

        foreach ($checks as $key => $info) {
            $existe = $info['model']->where('id_cliente', $idCliente)
                ->where('estado', 'completo')
                ->first();
            $modulos[$key] = !empty($existe);
            if (empty($existe)) {
                $faltantes[] = $info['label'];
            }
        }

        // Plan Emergencia propio
        $planExiste = $this->model->where('id_cliente', $idCliente)
            ->where('estado', 'completo')
            ->first();
        $modulos['plan_emergencia'] = !empty($planExiste);

        return $this->response->setJSON([
            'completas' => empty($faltantes),
            'modulos'   => $modulos,
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
        $this->comprimirImagen(FCPATH . $dir . $fileName);
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
                $logoBase64 = $this->fotoABase64ParaPdf($logoPath);
            }
        }

        // Fotos del plan a base64
        $fotosBase64 = [];
        foreach (self::FOTO_FIELDS as $campo) {
            $fotosBase64[$campo] = '';
            if (!empty($inspeccion[$campo])) {
                $fotoPath = FCPATH . $inspeccion[$campo];
                if (file_exists($fotoPath)) {
                    $fotosBase64[$campo] = $this->fotoABase64ParaPdf($fotoPath);
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

        // Hallazgos de la locativa: la tabla tbl_hallazgos_locativa es legacy
        // y no existe en la BD actual. Se mantiene la variable para compatibilidad
        // con la vista pdf.php pero inicializada vacia. Si en el futuro se crea
        // una tabla detalle de hallazgos, se puede re-habilitar aqui.
        $hallazgosLocativa = [];

        // Diagrama de emergencias (imagen estatica)
        $diagramaPath = FCPATH . 'uploads/imagenesplanemergencias/emergencias1.jpg';
        $diagramaBase64 = null;
        if (file_exists($diagramaPath)) {
            $diagramaBase64 = $this->fotoABase64ParaPdf($diagramaPath);
        }

        // Adendo IA por PON (Fase 2). Se decodifica el JSON guardado en BD por enriquecerPONsConIA().
        $ponsIaAdendo = [];
        if (!empty($inspeccion['pons_ia_json'])) {
            $decoded = json_decode($inspeccion['pons_ia_json'], true);
            if (is_array($decoded)) {
                $ponsIaAdendo = $decoded;
            }
        }

        // Diagrama IA (Fase 2)
        $diagramaNodos = null;
        if (!empty($inspeccion['diagrama_ia_json'])) {
            $decoded = json_decode($inspeccion['diagrama_ia_json'], true);
            if (is_array($decoded)) {
                $diagramaNodos = $decoded;
            }
        }

        // Matriz de responsables IA (Fase 2)
        $matrizResponsablesIA = null;
        if (!empty($inspeccion['matriz_responsables_ia_json'])) {
            $decoded = json_decode($inspeccion['matriz_responsables_ia_json'], true);
            if (is_array($decoded)) {
                $matrizResponsablesIA = $decoded;
            }
        }

        $data = [
            'inspeccion'           => $inspeccion,
            'cliente'              => $cliente,
            'consultor'            => $consultor,
            'logoBase64'           => $logoBase64,
            'fotosBase64'          => $fotosBase64,
            'telefonos'            => self::TELEFONOS,
            'empresasAseo'         => self::EMPRESAS_ASEO,
            'diagramaBase64'       => $diagramaBase64,
            'ultimaLocativa'       => $ultimaLocativa,
            'hallazgosLocativa'    => $hallazgosLocativa,
            'ultimaMatriz'         => $ultimaMatriz,
            'ultimaProb'           => $ultimaProb,
            'ultimaExt'            => $ultimaExt,
            'ultimaBot'            => $ultimaBot,
            'ultimaRec'            => $ultimaRec,
            'ultimaCom'            => $ultimaCom,
            'ultimaGab'            => $ultimaGab,
            'ponsIaAdendo'         => $ponsIaAdendo,
            'diagramaNodos'        => $diagramaNodos,
            'matrizResponsablesIA' => $matrizResponsablesIA,
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

    // ── Email ─────────────────────────────────────────────────

    public function enviarEmail($id)
    {
        $inspeccion = $this->model->find($id);
        if (!$inspeccion || $inspeccion['estado'] !== 'completo' || empty($inspeccion['ruta_pdf'])) {
            return redirect()->to("/inspecciones/plan-emergencia/view/{$id}")->with('error', 'Debe estar finalizado con PDF para enviar email.');
        }

        $result = InspeccionEmailNotifier::enviar(
            (int) $inspeccion['id_cliente'],
            (int) $inspeccion['id_consultor'],
            'PLAN DE EMERGENCIA',
            $inspeccion['fecha_visita'],
            $inspeccion['ruta_pdf'],
            (int) $inspeccion['id'],
            'PlanEmergencia'
        );

        if ($result['success']) {
            return redirect()->to("/inspecciones/plan-emergencia/view/{$id}")->with('msg', $result['message']);
        }
        return redirect()->to("/inspecciones/plan-emergencia/view/{$id}")->with('error', $result['error']);
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

        $destDir = UPLOADS_PATH . $nitCliente;
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
            'estado'          => 'CERRADO',
            'observaciones'   => 'Generado automaticamente desde modulo de inspecciones. plan_emg_id:' . $inspeccion['id'],
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
