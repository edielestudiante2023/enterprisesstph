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
use App\Controllers\Inspecciones\InspeccionBotiquinController;
use App\Controllers\Inspecciones\InspeccionExtintoresController;
use App\Controllers\Inspecciones\InspeccionComunicacionController;
use App\Controllers\Inspecciones\InspeccionGabineteController;
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
}
