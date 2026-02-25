<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ActaVisitaModel;
use App\Models\ActaVisitaIntegranteModel;
use App\Models\ActaVisitaTemaModel;
use App\Models\ActaVisitaFotoModel;
use App\Models\PendientesModel;
use App\Models\InspeccionLocativaModel;
use App\Models\HallazgoLocativoModel;
use App\Models\InspeccionSenalizacionModel;
use App\Models\ItemSenalizacionModel;
use App\Models\InspeccionBotiquinModel;
use App\Models\ElementoBotiquinModel;
use App\Models\InspeccionExtintoresModel;
use App\Models\ExtintorDetalleModel;
use App\Models\InspeccionComunicacionModel;
use App\Models\InspeccionGabineteModel;
use App\Models\GabineteDetalleModel;
use App\Models\CartaVigiaModel;
use App\Models\VencimientosMantenimientoModel;
use App\Models\MantenimientoModel;
use App\Models\MatrizVulnerabilidadModel;
use App\Models\ProbabilidadPeligrosModel;
use App\Models\InspeccionRecursosSeguridadModel;
use App\Models\HvBrigadistaModel;
use App\Models\PlanEmergenciaModel;
use App\Models\EvaluacionSimulacroModel;
use App\Models\ProgramaLimpiezaModel;
use App\Controllers\Inspecciones\InspeccionBotiquinController;
use App\Controllers\Inspecciones\InspeccionExtintoresController;
use App\Controllers\Inspecciones\InspeccionComunicacionController;
use App\Controllers\Inspecciones\InspeccionGabineteController;
use App\Controllers\Inspecciones\MatrizVulnerabilidadController;
use App\Controllers\Inspecciones\ProbabilidadPeligrosController;
use App\Controllers\Inspecciones\InspeccionRecursosSeguridadController;
use App\Controllers\Inspecciones\PlanEmergenciaController;
use CodeIgniter\Controller;

class ClientInspeccionesController extends Controller
{
    /**
     * Verify client session and return client ID, or redirect.
     */
    private function getClientId()
    {
        $session = session();
        if ($session->get('role') !== 'client') {
            return null;
        }
        return $session->get('user_id');
    }

    /**
     * Hub principal: cards por tipo de inspección con conteo y última fecha
     */
    public function dashboard()
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $clientModel = new ClientModel();
        $client = $clientModel->find($clientId);
        if (!$client) {
            return redirect()->to('/login')->with('error', 'Cliente no encontrado.');
        }

        $actaModel = new ActaVisitaModel();
        $locativaModel = new InspeccionLocativaModel();
        $senalizacionModel = new InspeccionSenalizacionModel();
        $botiquinModel = new InspeccionBotiquinModel();
        $extintoresModel = new InspeccionExtintoresModel();
        $comunicacionModel = new InspeccionComunicacionModel();
        $gabineteModel = new InspeccionGabineteModel();
        $cartaVigiaModel = new CartaVigiaModel();
        $vencimientoModel = new VencimientosMantenimientoModel();
        $matrizModel = new MatrizVulnerabilidadModel();
        $probabilidadModel = new ProbabilidadPeligrosModel();
        $recursosModel = new InspeccionRecursosSeguridadModel();
        $hvBrigadistaModel = new HvBrigadistaModel();
        $planEmergenciaModel = new PlanEmergenciaModel();
        $simulacroModel = new EvaluacionSimulacroModel();
        $progLimpModel = new ProgramaLimpiezaModel();

        $tipos = [
            [
                'nombre'  => 'Actas de Visita',
                'icono'   => 'fa-file-signature',
                'color'   => '#1c2437',
                'url'     => base_url('client/inspecciones/actas-visita'),
                'conteo'  => $actaModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $actaModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_visita', 'DESC')->first(),
                'campo_fecha' => 'fecha_visita',
            ],
            [
                'nombre'  => 'Inspecciones Locativas',
                'icono'   => 'fa-building',
                'color'   => '#bd9751',
                'url'     => base_url('client/inspecciones/locativas'),
                'conteo'  => $locativaModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $locativaModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->first(),
                'campo_fecha' => 'fecha_inspeccion',
            ],
            [
                'nombre'  => 'Inspecciones de Señalización',
                'icono'   => 'fa-sign',
                'color'   => '#28a745',
                'url'     => base_url('client/inspecciones/senalizacion'),
                'conteo'  => $senalizacionModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $senalizacionModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->first(),
                'campo_fecha' => 'fecha_inspeccion',
            ],
            [
                'nombre'  => 'Inspecciones de Botiquín',
                'icono'   => 'fa-first-aid',
                'color'   => '#dc3545',
                'url'     => base_url('client/inspecciones/botiquin'),
                'conteo'  => $botiquinModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $botiquinModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->first(),
                'campo_fecha' => 'fecha_inspeccion',
            ],
            [
                'nombre'  => 'Inspecciones de Extintores',
                'icono'   => 'fa-fire-extinguisher',
                'color'   => '#fd7e14',
                'url'     => base_url('client/inspecciones/extintores'),
                'conteo'  => $extintoresModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $extintoresModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->first(),
                'campo_fecha' => 'fecha_inspeccion',
            ],
            [
                'nombre'  => 'Equipos de Comunicación',
                'icono'   => 'fa-broadcast-tower',
                'color'   => '#6f42c1',
                'url'     => base_url('client/inspecciones/comunicaciones'),
                'conteo'  => $comunicacionModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $comunicacionModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->first(),
                'campo_fecha' => 'fecha_inspeccion',
            ],
            [
                'nombre'  => 'Inspecciones de Gabinetes',
                'icono'   => 'fa-shower',
                'color'   => '#20c997',
                'url'     => base_url('client/inspecciones/gabinetes'),
                'conteo'  => $gabineteModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $gabineteModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->first(),
                'campo_fecha' => 'fecha_inspeccion',
            ],
            [
                'nombre'  => 'Cartas de Vigía',
                'icono'   => 'fa-user-shield',
                'color'   => '#17a2b8',
                'url'     => base_url('client/inspecciones/carta-vigia'),
                'conteo'  => $cartaVigiaModel->where('id_cliente', $clientId)->where('estado_firma', 'firmado')->countAllResults(false),
                'ultima'  => $cartaVigiaModel->where('id_cliente', $clientId)->where('estado_firma', 'firmado')->orderBy('firma_fecha', 'DESC')->first(),
                'campo_fecha' => 'firma_fecha',
            ],
            [
                'nombre'  => 'Mantenimientos',
                'icono'   => 'fa-wrench',
                'color'   => '#6610f2',
                'url'     => base_url('client/inspecciones/mantenimientos'),
                'conteo'  => $vencimientoModel->where('id_cliente', $clientId)->countAllResults(false),
                'ultima'  => $vencimientoModel->where('id_cliente', $clientId)->orderBy('fecha_vencimiento', 'DESC')->first(),
                'campo_fecha' => 'fecha_vencimiento',
            ],
            [
                'nombre'  => 'Matriz de Vulnerabilidad',
                'icono'   => 'fa-shield-alt',
                'color'   => '#e83e8c',
                'url'     => base_url('client/inspecciones/matriz-vulnerabilidad'),
                'conteo'  => $matrizModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $matrizModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->first(),
                'campo_fecha' => 'fecha_inspeccion',
            ],
            [
                'nombre'  => 'Probabilidad de Peligros',
                'icono'   => 'fa-exclamation-triangle',
                'color'   => '#343a40',
                'url'     => base_url('client/inspecciones/probabilidad-peligros'),
                'conteo'  => $probabilidadModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $probabilidadModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->first(),
                'campo_fecha' => 'fecha_inspeccion',
            ],
            [
                'nombre'  => 'Recursos de Seguridad',
                'icono'   => 'fa-hard-hat',
                'color'   => '#795548',
                'url'     => base_url('client/inspecciones/recursos-seguridad'),
                'conteo'  => $recursosModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $recursosModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->first(),
                'campo_fecha' => 'fecha_inspeccion',
            ],
            [
                'nombre'  => 'HV Brigadistas',
                'icono'   => 'fa-id-card-alt',
                'color'   => '#00bcd4',
                'url'     => base_url('client/inspecciones/hv-brigadista'),
                'conteo'  => $hvBrigadistaModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $hvBrigadistaModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('created_at', 'DESC')->first(),
                'campo_fecha' => 'created_at',
            ],
            [
                'nombre'  => 'Plan de Emergencia',
                'icono'   => 'fa-route',
                'color'   => '#ff5722',
                'url'     => base_url('client/inspecciones/plan-emergencia'),
                'conteo'  => $planEmergenciaModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $planEmergenciaModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_visita', 'DESC')->first(),
                'campo_fecha' => 'fecha_visita',
            ],
            [
                'nombre'  => 'Evaluación Simulacro',
                'icono'   => 'fa-running',
                'color'   => '#607d8b',
                'url'     => base_url('client/inspecciones/simulacro'),
                'conteo'  => $simulacroModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $simulacroModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha', 'DESC')->first(),
                'campo_fecha' => 'fecha',
            ],
            [
                'nombre'  => 'Limpieza y Desinfección',
                'icono'   => 'fa-pump-soap',
                'color'   => '#4caf50',
                'url'     => base_url('client/inspecciones/limpieza-desinfeccion'),
                'conteo'  => $progLimpModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $progLimpModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_programa', 'DESC')->first(),
                'campo_fecha' => 'fecha_programa',
            ],
        ];

        return view('client/inspecciones/layout', [
            'client'  => $client,
            'title'   => 'Mis Inspecciones',
            'content' => view('client/inspecciones/dashboard', ['tipos' => $tipos]),
        ]);
    }

    // ─── ACTAS DE VISITA ────────────────────────────────────

    public function listActas()
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $clientModel = new ClientModel();
        $client = $clientModel->find($clientId);

        $actaModel = new ActaVisitaModel();
        $inspecciones = $actaModel
            ->where('id_cliente', $clientId)
            ->where('estado', 'completo')
            ->orderBy('fecha_visita', 'DESC')
            ->findAll();

        return view('client/inspecciones/layout', [
            'client'  => $client,
            'title'   => 'Actas de Visita',
            'content' => view('client/inspecciones/list', [
                'inspecciones' => $inspecciones,
                'tipo'         => 'acta_visita',
                'titulo'       => 'Actas de Visita',
                'campo_fecha'  => 'fecha_visita',
                'base_url'     => 'client/inspecciones/actas-visita',
            ]),
        ]);
    }

    public function viewActa($id)
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $actaModel = new ActaVisitaModel();
        $acta = $actaModel->find($id);
        if (!$acta || (int)$acta['id_cliente'] !== (int)$clientId) {
            return redirect()->to('/client/inspecciones')->with('error', 'Inspección no encontrada.');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $data = [
            'acta'        => $acta,
            'cliente'     => $clientModel->find($acta['id_cliente']),
            'consultor'   => $consultantModel->find($acta['id_consultor']),
            'integrantes' => (new ActaVisitaIntegranteModel())->getByActa($id),
            'temas'       => (new ActaVisitaTemaModel())->getByActa($id),
            'fotos'       => (new ActaVisitaFotoModel())->getByActa($id),
            'compromisos' => (new PendientesModel())->where('id_acta_visita', $id)->findAll(),
        ];

        return view('client/inspecciones/layout', [
            'client'  => $clientModel->find($clientId),
            'title'   => 'Acta de Visita',
            'content' => view('client/inspecciones/acta_visita_view', $data),
        ]);
    }

    // ─── INSPECCIONES LOCATIVAS ─────────────────────────────

    public function listLocativas()
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $clientModel = new ClientModel();
        $client = $clientModel->find($clientId);

        $model = new InspeccionLocativaModel();
        $inspecciones = $model
            ->where('id_cliente', $clientId)
            ->where('estado', 'completo')
            ->orderBy('fecha_inspeccion', 'DESC')
            ->findAll();

        return view('client/inspecciones/layout', [
            'client'  => $client,
            'title'   => 'Inspecciones Locativas',
            'content' => view('client/inspecciones/list', [
                'inspecciones' => $inspecciones,
                'tipo'         => 'locativa',
                'titulo'       => 'Inspecciones Locativas',
                'campo_fecha'  => 'fecha_inspeccion',
                'base_url'     => 'client/inspecciones/locativas',
            ]),
        ]);
    }

    public function viewLocativa($id)
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $model = new InspeccionLocativaModel();
        $inspeccion = $model->find($id);
        if (!$inspeccion || (int)$inspeccion['id_cliente'] !== (int)$clientId) {
            return redirect()->to('/client/inspecciones')->with('error', 'Inspección no encontrada.');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $data = [
            'inspeccion' => $inspeccion,
            'cliente'    => $clientModel->find($inspeccion['id_cliente']),
            'consultor'  => $consultantModel->find($inspeccion['id_consultor']),
            'hallazgos'  => (new HallazgoLocativoModel())->getByInspeccion($id),
        ];

        return view('client/inspecciones/layout', [
            'client'  => $clientModel->find($clientId),
            'title'   => 'Inspección Locativa',
            'content' => view('client/inspecciones/locativa_view', $data),
        ]);
    }

    // ─── INSPECCIONES DE SEÑALIZACIÓN ───────────────────────

    public function listSenalizacion()
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $clientModel = new ClientModel();
        $client = $clientModel->find($clientId);

        $model = new InspeccionSenalizacionModel();
        $inspecciones = $model
            ->where('id_cliente', $clientId)
            ->where('estado', 'completo')
            ->orderBy('fecha_inspeccion', 'DESC')
            ->findAll();

        return view('client/inspecciones/layout', [
            'client'  => $client,
            'title'   => 'Inspecciones de Señalización',
            'content' => view('client/inspecciones/list', [
                'inspecciones' => $inspecciones,
                'tipo'         => 'senalizacion',
                'titulo'       => 'Inspecciones de Señalización',
                'campo_fecha'  => 'fecha_inspeccion',
                'base_url'     => 'client/inspecciones/senalizacion',
            ]),
        ]);
    }

    public function viewSenalizacion($id)
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $model = new InspeccionSenalizacionModel();
        $inspeccion = $model->find($id);
        if (!$inspeccion || (int)$inspeccion['id_cliente'] !== (int)$clientId) {
            return redirect()->to('/client/inspecciones')->with('error', 'Inspección no encontrada.');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $data = [
            'inspeccion'   => $inspeccion,
            'cliente'      => $clientModel->find($inspeccion['id_cliente']),
            'consultor'    => $consultantModel->find($inspeccion['id_consultor']),
            'itemsGrouped' => (new ItemSenalizacionModel())->getByInspeccionGrouped($id),
        ];

        return view('client/inspecciones/layout', [
            'client'  => $clientModel->find($clientId),
            'title'   => 'Inspección de Señalización',
            'content' => view('client/inspecciones/senalizacion_view', $data),
        ]);
    }

    // ─── INSPECCIONES DE BOTIQUÍN ───────────────────────────

    public function listBotiquin()
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $clientModel = new ClientModel();
        $client = $clientModel->find($clientId);

        $model = new InspeccionBotiquinModel();
        $inspecciones = $model
            ->where('id_cliente', $clientId)
            ->where('estado', 'completo')
            ->orderBy('fecha_inspeccion', 'DESC')
            ->findAll();

        return view('client/inspecciones/layout', [
            'client'  => $client,
            'title'   => 'Inspecciones de Botiquín',
            'content' => view('client/inspecciones/list', [
                'inspecciones' => $inspecciones,
                'tipo'         => 'botiquin',
                'titulo'       => 'Inspecciones de Botiquín',
                'campo_fecha'  => 'fecha_inspeccion',
                'base_url'     => 'client/inspecciones/botiquin',
            ]),
        ]);
    }

    public function viewBotiquin($id)
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $model = new InspeccionBotiquinModel();
        $inspeccion = $model->find($id);
        if (!$inspeccion || (int)$inspeccion['id_cliente'] !== (int)$clientId) {
            return redirect()->to('/client/inspecciones')->with('error', 'Inspección no encontrada.');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $elementosRaw = (new ElementoBotiquinModel())->getByInspeccion($id);
        $elementosData = [];
        foreach ($elementosRaw as $elem) {
            $elementosData[$elem['clave']] = $elem;
        }

        $data = [
            'inspeccion'    => $inspeccion,
            'cliente'       => $clientModel->find($inspeccion['id_cliente']),
            'consultor'     => $consultantModel->find($inspeccion['id_consultor']),
            'elementos'     => InspeccionBotiquinController::ELEMENTOS,
            'elementosData' => $elementosData,
        ];

        return view('client/inspecciones/layout', [
            'client'  => $clientModel->find($clientId),
            'title'   => 'Inspección de Botiquín',
            'content' => view('client/inspecciones/botiquin_view', $data),
        ]);
    }

    // ─── INSPECCIONES DE EXTINTORES ─────────────────────────

    public function listExtintores()
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $clientModel = new ClientModel();
        $client = $clientModel->find($clientId);

        $model = new InspeccionExtintoresModel();
        $inspecciones = $model
            ->where('id_cliente', $clientId)
            ->where('estado', 'completo')
            ->orderBy('fecha_inspeccion', 'DESC')
            ->findAll();

        return view('client/inspecciones/layout', [
            'client'  => $client,
            'title'   => 'Inspecciones de Extintores',
            'content' => view('client/inspecciones/list', [
                'inspecciones' => $inspecciones,
                'tipo'         => 'extintores',
                'titulo'       => 'Inspecciones de Extintores',
                'campo_fecha'  => 'fecha_inspeccion',
                'base_url'     => 'client/inspecciones/extintores',
            ]),
        ]);
    }

    public function viewExtintores($id)
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $model = new InspeccionExtintoresModel();
        $inspeccion = $model->find($id);
        if (!$inspeccion || (int)$inspeccion['id_cliente'] !== (int)$clientId) {
            return redirect()->to('/client/inspecciones')->with('error', 'Inspección no encontrada.');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $data = [
            'inspeccion' => $inspeccion,
            'cliente'    => $clientModel->find($inspeccion['id_cliente']),
            'consultor'  => $consultantModel->find($inspeccion['id_consultor']),
            'extintores' => (new ExtintorDetalleModel())->getByInspeccion($id),
            'criterios'  => InspeccionExtintoresController::CRITERIOS,
        ];

        return view('client/inspecciones/layout', [
            'client'  => $clientModel->find($clientId),
            'title'   => 'Inspección de Extintores',
            'content' => view('client/inspecciones/extintores_view', $data),
        ]);
    }

    // ─── EQUIPOS DE COMUNICACIÓN ─────────────────────────────

    public function listComunicaciones()
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $clientModel = new ClientModel();
        $client = $clientModel->find($clientId);

        $model = new InspeccionComunicacionModel();
        $inspecciones = $model
            ->where('id_cliente', $clientId)
            ->where('estado', 'completo')
            ->orderBy('fecha_inspeccion', 'DESC')
            ->findAll();

        return view('client/inspecciones/layout', [
            'client'  => $client,
            'title'   => 'Equipos de Comunicación',
            'content' => view('client/inspecciones/list', [
                'inspecciones' => $inspecciones,
                'tipo'         => 'comunicaciones',
                'titulo'       => 'Equipos de Comunicación',
                'campo_fecha'  => 'fecha_inspeccion',
                'base_url'     => 'client/inspecciones/comunicaciones',
            ]),
        ]);
    }

    public function viewComunicacion($id)
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $model = new InspeccionComunicacionModel();
        $inspeccion = $model->find($id);
        if (!$inspeccion || (int)$inspeccion['id_cliente'] !== (int)$clientId) {
            return redirect()->to('/client/inspecciones')->with('error', 'Inspección no encontrada.');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $data = [
            'inspeccion' => $inspeccion,
            'cliente'    => $clientModel->find($inspeccion['id_cliente']),
            'consultor'  => $consultantModel->find($inspeccion['id_consultor']),
            'equipos'    => InspeccionComunicacionController::EQUIPOS,
        ];

        return view('client/inspecciones/layout', [
            'client'  => $clientModel->find($clientId),
            'title'   => 'Equipos de Comunicación',
            'content' => view('client/inspecciones/comunicaciones_view', $data),
        ]);
    }

    // ─── INSPECCIONES DE GABINETES ───────────────────────────

    public function listGabinetes()
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $clientModel = new ClientModel();
        $client = $clientModel->find($clientId);

        $model = new InspeccionGabineteModel();
        $inspecciones = $model
            ->where('id_cliente', $clientId)
            ->where('estado', 'completo')
            ->orderBy('fecha_inspeccion', 'DESC')
            ->findAll();

        return view('client/inspecciones/layout', [
            'client'  => $client,
            'title'   => 'Inspecciones de Gabinetes',
            'content' => view('client/inspecciones/list', [
                'inspecciones' => $inspecciones,
                'tipo'         => 'gabinetes',
                'titulo'       => 'Inspecciones de Gabinetes',
                'campo_fecha'  => 'fecha_inspeccion',
                'base_url'     => 'client/inspecciones/gabinetes',
            ]),
        ]);
    }

    public function viewGabinete($id)
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $model = new InspeccionGabineteModel();
        $inspeccion = $model->find($id);
        if (!$inspeccion || (int)$inspeccion['id_cliente'] !== (int)$clientId) {
            return redirect()->to('/client/inspecciones')->with('error', 'Inspección no encontrada.');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $data = [
            'inspeccion' => $inspeccion,
            'cliente'    => $clientModel->find($inspeccion['id_cliente']),
            'consultor'  => $consultantModel->find($inspeccion['id_consultor']),
            'gabinetes'  => (new GabineteDetalleModel())->getByInspeccion($id),
            'criterios'  => InspeccionGabineteController::CRITERIOS,
        ];

        return view('client/inspecciones/layout', [
            'client'  => $clientModel->find($clientId),
            'title'   => 'Inspección de Gabinetes',
            'content' => view('client/inspecciones/gabinetes_view', $data),
        ]);
    }

    // ─── CARTAS DE VIGÍA ─────────────────────────────────────

    public function listCartasVigia()
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $clientModel = new ClientModel();
        $client = $clientModel->find($clientId);

        $model = new CartaVigiaModel();
        $cartas = $model
            ->where('id_cliente', $clientId)
            ->where('estado_firma', 'firmado')
            ->orderBy('firma_fecha', 'DESC')
            ->findAll();

        return view('client/inspecciones/layout', [
            'client'  => $client,
            'title'   => 'Cartas de Vigía',
            'content' => view('client/inspecciones/carta_vigia_list', [
                'cartas' => $cartas,
            ]),
        ]);
    }

    // ─── MANTENIMIENTOS ──────────────────────────────────────

    public function listMantenimientos()
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $clientModel = new ClientModel();
        $client = $clientModel->find($clientId);

        $vencimientoModel = new VencimientosMantenimientoModel();
        $vencimientos = $vencimientoModel
            ->select('tbl_vencimientos_mantenimientos.*, tbl_mantenimientos.detalle_mantenimiento')
            ->join('tbl_mantenimientos', 'tbl_mantenimientos.id_mantenimiento = tbl_vencimientos_mantenimientos.id_mantenimiento', 'left')
            ->where('tbl_vencimientos_mantenimientos.id_cliente', $clientId)
            ->orderBy('tbl_vencimientos_mantenimientos.fecha_vencimiento', 'ASC')
            ->findAll();

        // Enriquecer con estado visual
        $hoy = date('Y-m-d');
        foreach ($vencimientos as &$v) {
            $estado = $v['estado_actividad'];
            if ($estado === 'sin ejecutar') {
                $diff = (strtotime($v['fecha_vencimiento']) - strtotime($hoy)) / 86400;
                $v['dias_diff'] = (int)$diff;
                if ($diff < 0) {
                    $v['color'] = 'danger';
                    $v['label'] = 'Vencido (' . abs((int)$diff) . ' días)';
                } elseif ($diff <= 15) {
                    $v['color'] = 'warning';
                    $v['label'] = 'Próximo (' . (int)$diff . ' días)';
                } else {
                    $v['color'] = 'gold';
                    $v['label'] = 'Vigente (' . (int)$diff . ' días)';
                }
            } else {
                $v['color'] = ($estado === 'ejecutado') ? 'success' : 'secondary';
                $v['label'] = $estado;
            }
        }
        unset($v);

        return view('client/inspecciones/layout', [
            'client'  => $client,
            'title'   => 'Mantenimientos',
            'content' => view('client/inspecciones/mantenimientos_list', [
                'vencimientos' => $vencimientos,
            ]),
        ]);
    }

    // ─── MATRIZ DE VULNERABILIDAD ────────────────────────────

    public function listMatrizVulnerabilidad()
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $clientModel = new ClientModel();
        $client = $clientModel->find($clientId);

        $model = new MatrizVulnerabilidadModel();
        $inspecciones = $model
            ->where('id_cliente', $clientId)
            ->where('estado', 'completo')
            ->orderBy('fecha_inspeccion', 'DESC')
            ->findAll();

        return view('client/inspecciones/layout', [
            'client'  => $client,
            'title'   => 'Matriz de Vulnerabilidad',
            'content' => view('client/inspecciones/list', [
                'inspecciones' => $inspecciones,
                'tipo'         => 'matriz_vulnerabilidad',
                'titulo'       => 'Matrices de Vulnerabilidad',
                'campo_fecha'  => 'fecha_inspeccion',
                'base_url'     => 'client/inspecciones/matriz-vulnerabilidad',
            ]),
        ]);
    }

    public function viewMatrizVulnerabilidad($id)
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $model = new MatrizVulnerabilidadModel();
        $inspeccion = $model->find($id);
        if (!$inspeccion || (int)$inspeccion['id_cliente'] !== (int)$clientId) {
            return redirect()->to('/client/inspecciones')->with('error', 'Inspección no encontrada.');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $matrizCtrl = new MatrizVulnerabilidadController();
        $puntaje = $matrizCtrl->calcularPuntaje($inspeccion);
        $clasificacion = $matrizCtrl->getClasificacion($puntaje);

        $data = [
            'inspeccion'    => $inspeccion,
            'cliente'       => $clientModel->find($inspeccion['id_cliente']),
            'consultor'     => $consultantModel->find($inspeccion['id_consultor']),
            'criterios'     => MatrizVulnerabilidadController::CRITERIOS,
            'puntaje'       => $puntaje,
            'clasificacion' => $clasificacion,
        ];

        return view('client/inspecciones/layout', [
            'client'  => $clientModel->find($clientId),
            'title'   => 'Matriz de Vulnerabilidad',
            'content' => view('client/inspecciones/matriz_vulnerabilidad_view', $data),
        ]);
    }

    // ─── PROBABILIDAD DE PELIGROS ────────────────────────────

    public function listProbabilidadPeligros()
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $clientModel = new ClientModel();
        $client = $clientModel->find($clientId);

        $model = new ProbabilidadPeligrosModel();
        $inspecciones = $model
            ->where('id_cliente', $clientId)
            ->where('estado', 'completo')
            ->orderBy('fecha_inspeccion', 'DESC')
            ->findAll();

        return view('client/inspecciones/layout', [
            'client'  => $client,
            'title'   => 'Probabilidad de Peligros',
            'content' => view('client/inspecciones/list', [
                'inspecciones' => $inspecciones,
                'tipo'         => 'probabilidad_peligros',
                'titulo'       => 'Probabilidad de Peligros',
                'campo_fecha'  => 'fecha_inspeccion',
                'base_url'     => 'client/inspecciones/probabilidad-peligros',
            ]),
        ]);
    }

    public function viewProbabilidadPeligros($id)
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $model = new ProbabilidadPeligrosModel();
        $inspeccion = $model->find($id);
        if (!$inspeccion || (int)$inspeccion['id_cliente'] !== (int)$clientId) {
            return redirect()->to('/client/inspecciones')->with('error', 'Inspección no encontrada.');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        // Calcular porcentajes
        $peligros = ProbabilidadPeligrosController::PELIGROS;
        $total = 0;
        $conteo = ['poco_probable' => 0, 'probable' => 0, 'muy_probable' => 0];
        foreach ($peligros as $grupo) {
            foreach ($grupo['items'] as $key => $label) {
                $val = $inspeccion[$key] ?? null;
                if ($val && isset($conteo[$val])) {
                    $conteo[$val]++;
                    $total++;
                }
            }
        }
        $porcentajes = $total === 0
            ? ['poco_probable' => 0, 'probable' => 0, 'muy_probable' => 0]
            : [
                'poco_probable' => round($conteo['poco_probable'] / $total, 4),
                'probable'      => round($conteo['probable'] / $total, 4),
                'muy_probable'  => round($conteo['muy_probable'] / $total, 4),
            ];

        $data = [
            'inspeccion'  => $inspeccion,
            'cliente'     => $clientModel->find($inspeccion['id_cliente']),
            'consultor'   => $consultantModel->find($inspeccion['id_consultor']),
            'peligros'    => $peligros,
            'frecuencias' => ProbabilidadPeligrosController::FRECUENCIAS,
            'porcentajes' => $porcentajes,
        ];

        return view('client/inspecciones/layout', [
            'client'  => $clientModel->find($clientId),
            'title'   => 'Probabilidad de Peligros',
            'content' => view('client/inspecciones/probabilidad_peligros_view', $data),
        ]);
    }

    // ─── RECURSOS DE SEGURIDAD ───────────────────────────────

    public function listRecursosSeguridad()
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $clientModel = new ClientModel();
        $client = $clientModel->find($clientId);

        $model = new InspeccionRecursosSeguridadModel();
        $inspecciones = $model
            ->where('id_cliente', $clientId)
            ->where('estado', 'completo')
            ->orderBy('fecha_inspeccion', 'DESC')
            ->findAll();

        return view('client/inspecciones/layout', [
            'client'  => $client,
            'title'   => 'Recursos de Seguridad',
            'content' => view('client/inspecciones/list', [
                'inspecciones' => $inspecciones,
                'tipo'         => 'recursos_seguridad',
                'titulo'       => 'Recursos de Seguridad',
                'campo_fecha'  => 'fecha_inspeccion',
                'base_url'     => 'client/inspecciones/recursos-seguridad',
            ]),
        ]);
    }

    public function viewRecursosSeguridad($id)
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $model = new InspeccionRecursosSeguridadModel();
        $inspeccion = $model->find($id);
        if (!$inspeccion || (int)$inspeccion['id_cliente'] !== (int)$clientId) {
            return redirect()->to('/client/inspecciones')->with('error', 'Inspección no encontrada.');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $data = [
            'inspeccion' => $inspeccion,
            'cliente'    => $clientModel->find($inspeccion['id_cliente']),
            'consultor'  => $consultantModel->find($inspeccion['id_consultor']),
            'recursos'   => InspeccionRecursosSeguridadController::RECURSOS,
        ];

        return view('client/inspecciones/layout', [
            'client'  => $clientModel->find($clientId),
            'title'   => 'Recursos de Seguridad',
            'content' => view('client/inspecciones/recursos_seguridad_view', $data),
        ]);
    }

    // ─── HV BRIGADISTAS ──────────────────────────────────────

    public function listHvBrigadista()
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $clientModel = new ClientModel();
        $client = $clientModel->find($clientId);

        $model = new HvBrigadistaModel();
        $inspecciones = $model
            ->where('id_cliente', $clientId)
            ->where('estado', 'completo')
            ->orderBy('created_at', 'DESC')
            ->findAll();

        return view('client/inspecciones/layout', [
            'client'  => $client,
            'title'   => 'HV Brigadistas',
            'content' => view('client/inspecciones/list', [
                'inspecciones' => $inspecciones,
                'tipo'         => 'hv_brigadista',
                'titulo'       => 'Hojas de Vida Brigadistas',
                'campo_fecha'  => 'created_at',
                'base_url'     => 'client/inspecciones/hv-brigadista',
            ]),
        ]);
    }

    public function viewHvBrigadista($id)
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $model = new HvBrigadistaModel();
        $hv = $model->find($id);
        if (!$hv || (int)$hv['id_cliente'] !== (int)$clientId) {
            return redirect()->to('/client/inspecciones')->with('error', 'Registro no encontrado.');
        }

        $clientModel = new ClientModel();

        $data = [
            'hv'      => $hv,
            'cliente' => $clientModel->find($hv['id_cliente']),
        ];

        return view('client/inspecciones/layout', [
            'client'  => $clientModel->find($clientId),
            'title'   => 'HV Brigadista',
            'content' => view('client/inspecciones/hv_brigadista_view', $data),
        ]);
    }

    // ─── PLAN DE EMERGENCIA ──────────────────────────────────

    public function listPlanEmergencia()
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $clientModel = new ClientModel();
        $client = $clientModel->find($clientId);

        $model = new PlanEmergenciaModel();
        $inspecciones = $model
            ->where('id_cliente', $clientId)
            ->where('estado', 'completo')
            ->orderBy('fecha_visita', 'DESC')
            ->findAll();

        return view('client/inspecciones/layout', [
            'client'  => $client,
            'title'   => 'Plan de Emergencia',
            'content' => view('client/inspecciones/list', [
                'inspecciones' => $inspecciones,
                'tipo'         => 'plan_emergencia',
                'titulo'       => 'Planes de Emergencia',
                'campo_fecha'  => 'fecha_visita',
                'base_url'     => 'client/inspecciones/plan-emergencia',
            ]),
        ]);
    }

    public function viewPlanEmergencia($id)
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $model = new PlanEmergenciaModel();
        $inspeccion = $model->find($id);
        if (!$inspeccion || (int)$inspeccion['id_cliente'] !== (int)$clientId) {
            return redirect()->to('/client/inspecciones')->with('error', 'Inspección no encontrada.');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $data = [
            'inspeccion'   => $inspeccion,
            'cliente'      => $clientModel->find($inspeccion['id_cliente']),
            'consultor'    => $consultantModel->find($inspeccion['id_consultor']),
            'telefonos'    => PlanEmergenciaController::TELEFONOS,
            'empresasAseo' => PlanEmergenciaController::EMPRESAS_ASEO,
        ];

        return view('client/inspecciones/layout', [
            'client'  => $clientModel->find($clientId),
            'title'   => 'Plan de Emergencia',
            'content' => view('client/inspecciones/plan_emergencia_view', $data),
        ]);
    }

    // ─── EVALUACIÓN SIMULACRO ────────────────────────────────

    public function listSimulacro()
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $clientModel = new ClientModel();
        $client = $clientModel->find($clientId);

        $model = new EvaluacionSimulacroModel();
        $inspecciones = $model
            ->where('id_cliente', $clientId)
            ->where('estado', 'completo')
            ->orderBy('fecha', 'DESC')
            ->findAll();

        return view('client/inspecciones/layout', [
            'client'  => $client,
            'title'   => 'Evaluación Simulacro',
            'content' => view('client/inspecciones/list', [
                'inspecciones' => $inspecciones,
                'tipo'         => 'simulacro',
                'titulo'       => 'Evaluaciones de Simulacro',
                'campo_fecha'  => 'fecha',
                'base_url'     => 'client/inspecciones/simulacro',
            ]),
        ]);
    }

    public function viewSimulacro($id)
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $model = new EvaluacionSimulacroModel();
        $eval = $model->find($id);
        if (!$eval || (int)$eval['id_cliente'] !== (int)$clientId) {
            return redirect()->to('/client/inspecciones')->with('error', 'Evaluación no encontrada.');
        }

        $clientModel = new ClientModel();

        $data = [
            'eval'    => $eval,
            'cliente' => $clientModel->find($eval['id_cliente']),
        ];

        return view('client/inspecciones/layout', [
            'client'  => $clientModel->find($clientId),
            'title'   => 'Evaluación Simulacro',
            'content' => view('client/inspecciones/simulacro_view', $data),
        ]);
    }

    // ─── PROGRAMA LIMPIEZA Y DESINFECCIÓN ───────────────────

    public function listLimpieza()
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $clientModel = new ClientModel();
        $client = $clientModel->find($clientId);

        $model = new ProgramaLimpiezaModel();
        $inspecciones = $model
            ->where('id_cliente', $clientId)
            ->where('estado', 'completo')
            ->orderBy('fecha_programa', 'DESC')
            ->findAll();

        return view('client/inspecciones/layout', [
            'client'  => $client,
            'title'   => 'Limpieza y Desinfección',
            'content' => view('client/inspecciones/list', [
                'inspecciones' => $inspecciones,
                'tipo'         => 'limpieza',
                'titulo'       => 'Programas de Limpieza y Desinfección',
                'campo_fecha'  => 'fecha_programa',
                'base_url'     => 'client/inspecciones/limpieza-desinfeccion',
            ]),
        ]);
    }

    public function viewLimpieza($id)
    {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $model = new ProgramaLimpiezaModel();
        $inspeccion = $model->find($id);
        if (!$inspeccion || (int)$inspeccion['id_cliente'] !== (int)$clientId) {
            return redirect()->to('/client/inspecciones')->with('error', 'Documento no encontrado.');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $data = [
            'inspeccion' => $inspeccion,
            'cliente'    => $clientModel->find($inspeccion['id_cliente']),
            'consultor'  => $consultantModel->find($inspeccion['id_consultor']),
        ];

        return view('client/inspecciones/layout', [
            'client'  => $clientModel->find($clientId),
            'title'   => 'Programa Limpieza y Desinfección',
            'content' => view('client/inspecciones/limpieza_view', $data),
        ]);
    }
}
