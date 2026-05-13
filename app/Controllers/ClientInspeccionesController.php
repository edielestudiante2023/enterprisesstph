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
use App\Models\InspeccionProductosQuimicosModel;
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
use App\Models\InspeccionBrigadaSimulacrosModel;
use App\Models\InspeccionAscensoresModel;
use App\Models\InspeccionPiscinasModel;
use App\Models\InspeccionPiscineroModel;
use App\Models\ProgramaLimpiezaModel;
use App\Models\ProgramaResiduosModel;
use App\Models\ProgramaPlagasModel;
use App\Models\ProgramaAguaPotableModel;
use App\Models\PlanSaneamientoModel;
use App\Models\PlanContingenciaPlagasModel;
use App\Models\PlanContingenciaLimpiezaDesinfeccionModel;
use App\Models\PlanContingenciaAguaModel;
use App\Models\PlanContingenciaBasuraModel;
use App\Models\CertificadoServicioModel;
use App\Models\PlanillaSSModel;
use App\Models\KpiLimpiezaModel;
use App\Models\KpiResiduosModel;
use App\Models\KpiPlagasModel;
use App\Models\KpiAguaPotableModel;
use App\Models\DotacionVigilanteModel;
use App\Models\DotacionAseadoraModel;
use App\Models\DotacionToderoModel;
use App\Models\AuditoriaZonaResiduosModel;
use App\Models\AsistenciaCapacitacionModel;
use App\Models\AsistenciaCapacitacionAsistenteModel;
use App\Models\EvaluacionCapacitacionModel;
use App\Models\ReporteCapacitacionModel;
use App\Models\PreparacionSimulacroModel;
use App\Models\InspeccionNoAplicaModel;
use App\Controllers\Inspecciones\InspeccionBotiquinController;
use App\Controllers\Inspecciones\InspeccionExtintoresController;
use App\Controllers\Inspecciones\InspeccionComunicacionController;
use App\Controllers\Inspecciones\InspeccionGabineteController;
use App\Controllers\Inspecciones\MatrizVulnerabilidadController;
use App\Controllers\Inspecciones\ProbabilidadPeligrosController;
use App\Controllers\Inspecciones\InspeccionRecursosSeguridadController;
use App\Controllers\Inspecciones\PlanEmergenciaController;
use App\Controllers\Inspecciones\DotacionVigilanteController;
use App\Controllers\Inspecciones\DotacionAseadoraController;
use App\Controllers\Inspecciones\DotacionToderoController;
use App\Controllers\Inspecciones\AuditoriaZonaResiduosController;
use App\Controllers\Inspecciones\AsistenciaCapacitacionController;
use App\Controllers\Inspecciones\ReporteCapacitacionController;
use App\Controllers\Inspecciones\PreparacionSimulacroController;
use App\Controllers\Inspecciones\DashboardSaneamientoController;
use CodeIgniter\Controller;

class ClientInspeccionesController extends Controller
{
    /**
     * Verify client session and return client ID, or redirect.
     * Consultores y admins pueden ver inspecciones de un cliente pasando el ID por parámetro.
     */
    private function getClientId(?int $idCliente = null)
    {
        $session = session();
        $role = $session->get('role');

        // Consultor o admin: si pasan idCliente, guardarlo en sesión para sub-páginas
        if (in_array($role, ['consultant', 'admin'])) {
            if ($idCliente) {
                $session->set('viewing_client_id', $idCliente);
                return $idCliente;
            }
            // Sub-páginas sin parámetro: leer de sesión
            $viewingId = $session->get('viewing_client_id');
            if ($viewingId) {
                return $viewingId;
            }
        }

        // Cliente viendo sus propias inspecciones
        if ($role === 'client') {
            return $session->get('user_id');
        }

        return null;
    }

    /**
     * Hub principal: cards por tipo de inspección con conteo y última fecha
     */
    public function dashboard(?int $idCliente = null)
    {
        $clientId = $this->getClientId($idCliente);
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
        $productosQuimicosModel = new InspeccionProductosQuimicosModel();
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
        $brigadaSimulacrosModel = new InspeccionBrigadaSimulacrosModel();
        $ascensoresModel = new InspeccionAscensoresModel();
        $piscinasModel = new InspeccionPiscinasModel();
        $piscineroModel = new InspeccionPiscineroModel();
        $progLimpModel = new ProgramaLimpiezaModel();
        $progResModel = new ProgramaResiduosModel();
        $progPlagModel = new ProgramaPlagasModel();
        $progAguaModel = new ProgramaAguaPotableModel();
        $planSanModel = new PlanSaneamientoModel();
        $contPlagasModel = new PlanContingenciaPlagasModel();
        $contLimpiezaModel = new PlanContingenciaLimpiezaDesinfeccionModel();
        $contAguaModel = new PlanContingenciaAguaModel();
        $contBasuraModel = new PlanContingenciaBasuraModel();
        $certificadoModel = new CertificadoServicioModel();
        $planillaModel = new PlanillaSSModel();
        $kpiLimpModel = new KpiLimpiezaModel();
        $kpiResModel = new KpiResiduosModel();
        $kpiPlagModel = new KpiPlagasModel();
        $kpiAguaModel = new KpiAguaPotableModel();
        $dotVigilanteModel = new DotacionVigilanteModel();
        $dotAseadoraModel = new DotacionAseadoraModel();
        $dotToderoModel = new DotacionToderoModel();
        $audResiduosModel = new AuditoriaZonaResiduosModel();
        $asistInducModel = new AsistenciaCapacitacionModel();
        $evalCapacModel = new EvaluacionCapacitacionModel();
        $repCapacModel = new ReporteCapacitacionModel();
        $prepSimulacroModel = new PreparacionSimulacroModel();

        $tipos = [
            [
                'nombre'  => 'Actas de Visita',
                'slug_matriz' => 'acta-visita',
                'icono'   => 'fa-file-signature',
                'color'   => '#1c2437',
                'url'     => base_url('client/inspecciones/actas-visita'),
                'conteo'  => $actaModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $actaModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_visita', 'DESC')->first(),
                'campo_fecha' => 'fecha_visita',
            ],
            [
                'nombre'  => 'Inspecciones Locativas',
                'slug_matriz' => 'inspeccion-locativa',
                'icono'   => 'fa-building',
                'color'   => '#bd9751',
                'url'     => base_url('client/inspecciones/locativas'),
                'conteo'  => $locativaModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $locativaModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->first(),
                'campo_fecha' => 'fecha_inspeccion',
            ],
            [
                'nombre'  => 'Inspecciones de Señalización',
                'slug_matriz' => 'senalizacion',
                'icono'   => 'fa-sign',
                'color'   => '#28a745',
                'url'     => base_url('client/inspecciones/senalizacion'),
                'conteo'  => $senalizacionModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $senalizacionModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->first(),
                'campo_fecha' => 'fecha_inspeccion',
            ],
            [
                'nombre'  => 'Inspecciones de Botiquín',
                'slug_matriz' => 'botiquin',
                'icono'   => 'fa-first-aid',
                'color'   => '#dc3545',
                'url'     => base_url('client/inspecciones/botiquin'),
                'conteo'  => $botiquinModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $botiquinModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->first(),
                'campo_fecha' => 'fecha_inspeccion',
            ],
            [
                'nombre'  => 'Inspecciones de Extintores',
                'slug_matriz' => 'extintores',
                'icono'   => 'fa-fire-extinguisher',
                'color'   => '#fd7e14',
                'url'     => base_url('client/inspecciones/extintores'),
                'conteo'  => $extintoresModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $extintoresModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->first(),
                'campo_fecha' => 'fecha_inspeccion',
            ],
            [
                'nombre'  => 'Productos Químicos',
                'slug_matriz' => 'productos-quimicos',
                'icono'   => 'fa-flask',
                'color'   => '#7b1fa2',
                'url'     => base_url('client/inspecciones/productos-quimicos'),
                'conteo'  => $productosQuimicosModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $productosQuimicosModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->first(),
                'campo_fecha' => 'fecha_inspeccion',
            ],
            [
                'nombre'  => 'Equipos de Comunicación',
                'slug_matriz' => 'comunicaciones',
                'icono'   => 'fa-broadcast-tower',
                'color'   => '#6f42c1',
                'url'     => base_url('client/inspecciones/comunicaciones'),
                'conteo'  => $comunicacionModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $comunicacionModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->first(),
                'campo_fecha' => 'fecha_inspeccion',
            ],
            [
                'nombre'  => 'Inspecciones de Gabinetes',
                'slug_matriz' => 'gabinetes',
                'icono'   => 'fa-shower',
                'color'   => '#20c997',
                'url'     => base_url('client/inspecciones/gabinetes'),
                'conteo'  => $gabineteModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $gabineteModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->first(),
                'campo_fecha' => 'fecha_inspeccion',
            ],
            [
                'nombre'  => 'Cartas de Vigía',
                'slug_matriz' => 'carta-vigia',
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
                'slug_matriz' => 'matriz-vulnerabilidad',
                'icono'   => 'fa-shield-alt',
                'color'   => '#e83e8c',
                'url'     => base_url('client/inspecciones/matriz-vulnerabilidad'),
                'conteo'  => $matrizModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $matrizModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->first(),
                'campo_fecha' => 'fecha_inspeccion',
            ],
            [
                'nombre'  => 'Probabilidad de Peligros',
                'slug_matriz' => 'probabilidad-peligros',
                'icono'   => 'fa-exclamation-triangle',
                'color'   => '#343a40',
                'url'     => base_url('client/inspecciones/probabilidad-peligros'),
                'conteo'  => $probabilidadModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $probabilidadModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->first(),
                'campo_fecha' => 'fecha_inspeccion',
            ],
            [
                'nombre'  => 'Recursos de Seguridad',
                'slug_matriz' => 'recursos-seguridad',
                'icono'   => 'fa-hard-hat',
                'color'   => '#795548',
                'url'     => base_url('client/inspecciones/recursos-seguridad'),
                'conteo'  => $recursosModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $recursosModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->first(),
                'campo_fecha' => 'fecha_inspeccion',
            ],
            [
                'nombre'  => 'Brigada y Simulacros',
                'slug_matriz' => 'brigada-simulacros',
                'icono'   => 'fa-people-carry',
                'color'   => '#455a64',
                'url'     => base_url('client/inspecciones/brigada-simulacros'),
                'conteo'  => $brigadaSimulacrosModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $brigadaSimulacrosModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->first(),
                'campo_fecha' => 'fecha_inspeccion',
            ],
            [
                'nombre'  => 'Ascensores',
                'slug_matriz' => 'ascensores',
                'icono'   => 'fa-sort',
                'color'   => '#3949ab',
                'url'     => base_url('client/inspecciones/ascensores'),
                'conteo'  => $ascensoresModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $ascensoresModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->first(),
                'campo_fecha' => 'fecha_inspeccion',
            ],
            [
                'nombre'  => 'Piscinas',
                'slug_matriz' => 'piscinas',
                'icono'   => 'fa-swimming-pool',
                'color'   => '#0288d1',
                'url'     => base_url('client/inspecciones/piscinas'),
                'conteo'  => $piscinasModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $piscinasModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->first(),
                'campo_fecha' => 'fecha_inspeccion',
            ],
            [
                'nombre'  => 'Piscinero / Salvavidas',
                'slug_matriz' => 'piscinero',
                'icono'   => 'fa-swimmer',
                'color'   => '#0097a7',
                'url'     => base_url('client/inspecciones/piscinero'),
                'conteo'  => $piscineroModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $piscineroModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->first(),
                'campo_fecha' => 'fecha_inspeccion',
            ],
            [
                'nombre'  => 'HV Brigadistas',
                'slug_matriz' => 'hv-brigadista',
                'icono'   => 'fa-id-card-alt',
                'color'   => '#00bcd4',
                'url'     => base_url('client/inspecciones/hv-brigadista'),
                'conteo'  => $hvBrigadistaModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $hvBrigadistaModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('created_at', 'DESC')->first(),
                'campo_fecha' => 'created_at',
            ],
            [
                'nombre'  => 'Plan de Emergencia',
                'slug_matriz' => 'plan-emergencia',
                'icono'   => 'fa-route',
                'color'   => '#ff5722',
                'url'     => base_url('client/inspecciones/plan-emergencia'),
                'conteo'  => $planEmergenciaModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $planEmergenciaModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_visita', 'DESC')->first(),
                'campo_fecha' => 'fecha_visita',
            ],
            [
                'nombre'  => 'Evaluación Simulacro',
                'slug_matriz' => 'simulacro',
                'icono'   => 'fa-running',
                'color'   => '#607d8b',
                'url'     => base_url('client/inspecciones/simulacro'),
                'conteo'  => $simulacroModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $simulacroModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha', 'DESC')->first(),
                'campo_fecha' => 'fecha',
            ],
            [
                'nombre'  => 'Limpieza y Desinfección',
                'slug_matriz' => 'limpieza-desinfeccion',
                'icono'   => 'fa-pump-soap',
                'color'   => '#4caf50',
                'url'     => base_url('client/inspecciones/limpieza-desinfeccion'),
                'conteo'  => $progLimpModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $progLimpModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_programa', 'DESC')->first(),
                'campo_fecha' => 'fecha_programa',
            ],
            [
                'nombre'  => 'Residuos Sólidos',
                'slug_matriz' => 'residuos-solidos',
                'icono'   => 'fa-recycle',
                'color'   => '#2e7d32',
                'url'     => base_url('client/inspecciones/residuos-solidos'),
                'conteo'  => $progResModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $progResModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_programa', 'DESC')->first(),
                'campo_fecha' => 'fecha_programa',
            ],
            [
                'nombre'  => 'Control Plagas',
                'slug_matriz' => 'control-plagas',
                'icono'   => 'fa-bug',
                'color'   => '#5d4037',
                'url'     => base_url('client/inspecciones/control-plagas'),
                'conteo'  => $progPlagModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $progPlagModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_programa', 'DESC')->first(),
                'campo_fecha' => 'fecha_programa',
            ],
            [
                'nombre'  => 'Agua Potable',
                'slug_matriz' => 'agua-potable',
                'icono'   => 'fa-tint',
                'color'   => '#0277bd',
                'url'     => base_url('client/inspecciones/agua-potable'),
                'conteo'  => $progAguaModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $progAguaModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_programa', 'DESC')->first(),
                'campo_fecha' => 'fecha_programa',
            ],
            [
                'nombre'  => 'Plan Saneamiento',
                'slug_matriz' => 'plan-saneamiento',
                'icono'   => 'fa-shield-alt',
                'color'   => '#4a148c',
                'url'     => base_url('client/inspecciones/plan-saneamiento'),
                'conteo'  => $planSanModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $planSanModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_programa', 'DESC')->first(),
                'campo_fecha' => 'fecha_programa',
            ],
            [
                'nombre'  => 'Contingencia Plagas',
                'slug_matriz' => 'contingencia-plagas',
                'icono'   => 'fa-bug',
                'color'   => '#6d4c41',
                'url'     => base_url('client/inspecciones/contingencia-plagas'),
                'conteo'  => $contPlagasModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $contPlagasModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_programa', 'DESC')->first(),
                'campo_fecha' => 'fecha_programa',
            ],
            [
                'nombre'  => 'Contingencia Limpieza y Desinfección',
                'slug_matriz' => 'contingencia-limpieza-desinfeccion',
                'icono'   => 'fa-spray-can',
                'color'   => '#00796b',
                'url'     => base_url('client/inspecciones/contingencia-limpieza-desinfeccion'),
                'conteo'  => $contLimpiezaModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $contLimpiezaModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_programa', 'DESC')->first(),
                'campo_fecha' => 'fecha_programa',
            ],
            [
                'nombre'  => 'Contingencia Sin Agua',
                'slug_matriz' => 'contingencia-agua',
                'icono'   => 'fa-tint-slash',
                'color'   => '#1565c0',
                'url'     => base_url('client/inspecciones/contingencia-agua'),
                'conteo'  => $contAguaModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $contAguaModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_programa', 'DESC')->first(),
                'campo_fecha' => 'fecha_programa',
            ],
            [
                'nombre'  => 'Contingencia Basura',
                'slug_matriz' => 'contingencia-basura',
                'icono'   => 'fa-trash-alt',
                'color'   => '#5d4037',
                'url'     => base_url('client/inspecciones/contingencia-basura'),
                'conteo'  => $contBasuraModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $contBasuraModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_programa', 'DESC')->first(),
                'campo_fecha' => 'fecha_programa',
            ],
            [
                'nombre'  => 'Lavado de Tanques',
                'slug_matriz' => 'lavado-tanques',
                'icono'   => 'fa-water',
                'color'   => '#0288d1',
                'url'     => base_url('client/inspecciones/lavado-tanques'),
                'conteo'  => $certificadoModel->where('id_cliente', $clientId)->where('id_mantenimiento', 2)->countAllResults(false),
                'ultima'  => $certificadoModel->where('id_cliente', $clientId)->where('id_mantenimiento', 2)->orderBy('fecha_servicio', 'DESC')->first(),
                'campo_fecha' => 'fecha_servicio',
            ],
            [
                'nombre'  => 'Fumigación',
                'slug_matriz' => 'fumigacion',
                'icono'   => 'fa-spray-can',
                'color'   => '#7cb342',
                'url'     => base_url('client/inspecciones/fumigacion'),
                'conteo'  => $certificadoModel->where('id_cliente', $clientId)->where('id_mantenimiento', 3)->countAllResults(false),
                'ultima'  => $certificadoModel->where('id_cliente', $clientId)->where('id_mantenimiento', 3)->orderBy('fecha_servicio', 'DESC')->first(),
                'campo_fecha' => 'fecha_servicio',
            ],
            [
                'nombre'  => 'Desratización',
                'slug_matriz' => 'desratizacion',
                'icono'   => 'fa-mouse',
                'color'   => '#8d6e63',
                'url'     => base_url('client/inspecciones/desratizacion'),
                'conteo'  => $certificadoModel->where('id_cliente', $clientId)->where('id_mantenimiento', 4)->countAllResults(false),
                'ultima'  => $certificadoModel->where('id_cliente', $clientId)->where('id_mantenimiento', 4)->orderBy('fecha_servicio', 'DESC')->first(),
                'campo_fecha' => 'fecha_servicio',
            ],
            [
                'nombre'  => 'Planilla Seg. Social',
                'slug_matriz' => 'planilla-seg-social',
                'icono'   => 'fa-file-invoice',
                'color'   => '#546e7a',
                'url'     => base_url('client/inspecciones/planilla-ss'),
                'conteo'  => $planillaModel->where('id_cliente', $clientId)->countAllResults(false),
                'ultima'  => $planillaModel->where('id_cliente', $clientId)->orderBy('created_at', 'DESC')->first(),
                'campo_fecha' => 'created_at',
            ],
            [
                'nombre'  => 'KPI Limpieza',
                'slug_matriz' => 'kpi-limpieza',
                'icono'   => 'fa-chart-line',
                'color'   => '#00897b',
                'url'     => base_url('client/inspecciones/kpi-limpieza'),
                'conteo'  => $kpiLimpModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $kpiLimpModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->first(),
                'campo_fecha' => 'fecha_inspeccion',
            ],
            [
                'nombre'  => 'KPI Residuos',
                'slug_matriz' => 'kpi-residuos',
                'icono'   => 'fa-chart-bar',
                'color'   => '#558b2f',
                'url'     => base_url('client/inspecciones/kpi-residuos'),
                'conteo'  => $kpiResModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $kpiResModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->first(),
                'campo_fecha' => 'fecha_inspeccion',
            ],
            [
                'nombre'  => 'KPI Plagas',
                'slug_matriz' => 'kpi-plagas',
                'icono'   => 'fa-chart-pie',
                'color'   => '#795548',
                'url'     => base_url('client/inspecciones/kpi-plagas'),
                'conteo'  => $kpiPlagModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $kpiPlagModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->first(),
                'campo_fecha' => 'fecha_inspeccion',
            ],
            [
                'nombre'  => 'KPI Agua Potable',
                'slug_matriz' => 'kpi-agua-potable',
                'icono'   => 'fa-chart-area',
                'color'   => '#01579b',
                'url'     => base_url('client/inspecciones/kpi-agua-potable'),
                'conteo'  => $kpiAguaModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $kpiAguaModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->first(),
                'campo_fecha' => 'fecha_inspeccion',
            ],
            [
                'nombre'  => 'Dashboard Saneamiento',
                'icono'   => 'fa-clipboard-check',
                'color'   => '#6a1b9a',
                'url'     => base_url('client/inspecciones/dashboard-saneamiento'),
                'conteo'  => null,
                'ultima'  => null,
                'campo_fecha' => null,
                'es_dashboard' => true,
            ],
            [
                'nombre'  => 'Dotación Vigilante',
                'slug_matriz' => 'dotacion-vigilante',
                'icono'   => 'fa-user-tie',
                'color'   => '#37474f',
                'url'     => base_url('client/inspecciones/dotacion-vigilante'),
                'conteo'  => $dotVigilanteModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $dotVigilanteModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->first(),
                'campo_fecha' => 'fecha_inspeccion',
            ],
            [
                'nombre'  => 'Dotación Aseadora',
                'slug_matriz' => 'dotacion-aseadora',
                'icono'   => 'fa-broom',
                'color'   => '#8d6e63',
                'url'     => base_url('client/inspecciones/dotacion-aseadora'),
                'conteo'  => $dotAseadoraModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $dotAseadoraModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->first(),
                'campo_fecha' => 'fecha_inspeccion',
            ],
            [
                'nombre'  => 'Dotación Todero',
                'slug_matriz' => 'dotacion-todero',
                'icono'   => 'fa-hard-hat',
                'color'   => '#ff8f00',
                'url'     => base_url('client/inspecciones/dotacion-todero'),
                'conteo'  => $dotToderoModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $dotToderoModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->first(),
                'campo_fecha' => 'fecha_inspeccion',
            ],
            [
                'nombre'  => 'Auditoría Zona de Residuos',
                'slug_matriz' => 'auditoria-zona-residuos',
                'icono'   => 'fa-recycle',
                'color'   => '#2e7d32',
                'url'     => base_url('client/inspecciones/auditoria-zona-residuos'),
                'conteo'  => $audResiduosModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $audResiduosModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->first(),
                'campo_fecha' => 'fecha_inspeccion',
            ],
            [
                'nombre'  => 'Asistencia Inducción',
                'icono'   => 'fa-chalkboard-teacher',
                'color'   => '#1565c0',
                'url'     => base_url('client/inspecciones/asistencia-capacitacion'),
                'conteo'  => $asistInducModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $asistInducModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_sesion', 'DESC')->first(),
                'campo_fecha' => 'fecha_sesion',
            ],
            [
                'nombre'  => 'Evaluación Capacitación',
                'slug_matriz' => 'evaluacion-capacitacion',
                'icono'   => 'fa-pen-fancy',
                'color'   => '#6a1b9a',
                'url'     => base_url('client/inspecciones/evaluacion-capacitacion'),
                'conteo'  => $evalCapacModel->where('id_cliente', $clientId)->countAllResults(false),
                'ultima'  => $evalCapacModel->where('id_cliente', $clientId)->orderBy('created_at', 'DESC')->first(),
                'campo_fecha' => 'created_at',
            ],
            [
                'nombre'  => 'Reportes de Capacitación',
                'slug_matriz' => 'acta-capacitacion',
                'icono'   => 'fa-graduation-cap',
                'color'   => '#ad1457',
                'url'     => base_url('client/inspecciones/reporte-capacitacion'),
                'conteo'  => $repCapacModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $repCapacModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_capacitacion', 'DESC')->first(),
                'campo_fecha' => 'fecha_capacitacion',
            ],
            [
                'nombre'  => 'Preparación Simulacro',
                'slug_matriz' => 'preparacion-simulacro',
                'icono'   => 'fa-clipboard-list',
                'color'   => '#546e7a',
                'url'     => base_url('client/inspecciones/preparacion-simulacro'),
                'conteo'  => $prepSimulacroModel->where('id_cliente', $clientId)->where('estado', 'completo')->countAllResults(false),
                'ultima'  => $prepSimulacroModel->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_simulacro', 'DESC')->first(),
                'campo_fecha' => 'fecha_simulacro',
            ],
        ];

        $noAplica = (new InspeccionNoAplicaModel())->getByCliente((int) $clientId);
        if (!empty($noAplica)) {
            $tipos = array_values(array_filter($tipos, static function (array $tipo) use ($noAplica): bool {
                $slug = $tipo['slug_matriz'] ?? null;
                return !$slug || !isset($noAplica[$slug]);
            }));
        }

        return view('client/inspecciones/layout', [
            'client'  => $client,
            'title'   => 'Mis Inspecciones',
            'content' => view('client/inspecciones/dashboard', ['tipos' => $tipos]),
        ]);
    }

    private function listGenericModule(
        object $model,
        string $titulo,
        string $tipo,
        string $baseUrl,
        string $campoFecha,
        ?string $estado = 'completo',
        array $extraWhere = []
    ) {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $client = (new ClientModel())->find($clientId);
        $builder = $model->where('id_cliente', $clientId);
        foreach ($extraWhere as $field => $value) {
            $builder->where($field, $value);
        }
        if ($estado !== null) {
            $builder->where('estado', $estado);
        }
        $inspecciones = $builder->orderBy($campoFecha, 'DESC')->findAll();

        return view('client/inspecciones/layout', [
            'client' => $client,
            'title' => $titulo,
            'content' => view('client/inspecciones/list', [
                'titulo'       => $titulo,
                'tipo'         => $tipo,
                'inspecciones' => $inspecciones,
                'campo_fecha'  => $campoFecha,
                'base_url'     => $baseUrl,
            ]),
        ]);
    }

    private function viewGenericModule(
        object $model,
        int $id,
        string $titulo,
        string $tipo,
        string $backUrl,
        string $campoFecha,
        ?string $pdfRoute = null,
        ?string $estado = 'completo',
        array $extraWhere = []
    ) {
        $clientId = $this->getClientId();
        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $builder = $model->where('id', $id)->where('id_cliente', $clientId);
        foreach ($extraWhere as $field => $value) {
            $builder->where($field, $value);
        }
        if ($estado !== null) {
            $builder->where('estado', $estado);
        }
        $registro = $builder->first();
        if (!$registro) {
            return redirect()->to('/client/inspecciones')->with('error', 'Documento no encontrado.');
        }

        $client = (new ClientModel())->find($clientId);
        $pdfUrl = $pdfRoute ? rtrim($pdfRoute, '/') . '/' . $id : null;

        return view('client/inspecciones/layout', [
            'client' => $client,
            'title' => $titulo,
            'content' => view('client/inspecciones/generic_view', [
                'titulo'      => $titulo,
                'tipo'        => $tipo,
                'icono'       => $this->clientModuleIcon($tipo),
                'cliente'     => $client,
                'registro'    => $registro,
                'fecha_campo' => $campoFecha,
                'back_url'    => $backUrl,
                'pdf_url'     => $pdfUrl,
            ]),
        ]);
    }

    private function clientModuleIcon(string $tipo): string
    {
        $map = [
            'productos_quimicos' => 'fa-flask',
            'brigada_simulacros' => 'fa-people-carry',
            'ascensores' => 'fa-sort',
            'piscinas' => 'fa-swimming-pool',
            'piscinero' => 'fa-swimmer',
            'evaluacion_capacitacion' => 'fa-pen-fancy',
            'contingencia_plagas' => 'fa-bug',
            'contingencia_limpieza' => 'fa-spray-can',
            'contingencia_agua' => 'fa-tint-slash',
            'contingencia_basura' => 'fa-trash-alt',
            'lavado_tanques' => 'fa-water',
            'fumigacion' => 'fa-spray-can',
            'desratizacion' => 'fa-mouse',
            'planilla_ss' => 'fa-file-invoice',
        ];
        return $map[$tipo] ?? 'fa-clipboard-list';
    }

    public function listProductosQuimicos()
    {
        return $this->listGenericModule(new InspeccionProductosQuimicosModel(), 'Productos Químicos', 'productos_quimicos', 'client/inspecciones/productos-quimicos', 'fecha_inspeccion');
    }

    public function viewProductosQuimicos(int $id)
    {
        return $this->viewGenericModule(new InspeccionProductosQuimicosModel(), $id, 'Productos Químicos', 'productos_quimicos', 'client/inspecciones/productos-quimicos', 'fecha_inspeccion', 'inspecciones/productos-quimicos/pdf');
    }

    public function listBrigadaSimulacros()
    {
        return $this->listGenericModule(new InspeccionBrigadaSimulacrosModel(), 'Brigada y Simulacros', 'brigada_simulacros', 'client/inspecciones/brigada-simulacros', 'fecha_inspeccion');
    }

    public function viewBrigadaSimulacros(int $id)
    {
        return $this->viewGenericModule(new InspeccionBrigadaSimulacrosModel(), $id, 'Brigada y Simulacros', 'brigada_simulacros', 'client/inspecciones/brigada-simulacros', 'fecha_inspeccion', 'inspecciones/brigada-simulacros/pdf');
    }

    public function listAscensores()
    {
        return $this->listGenericModule(new InspeccionAscensoresModel(), 'Ascensores', 'ascensores', 'client/inspecciones/ascensores', 'fecha_inspeccion');
    }

    public function viewAscensores(int $id)
    {
        return $this->viewGenericModule(new InspeccionAscensoresModel(), $id, 'Ascensores', 'ascensores', 'client/inspecciones/ascensores', 'fecha_inspeccion', 'inspecciones/ascensores/pdf');
    }

    public function listPiscinas()
    {
        return $this->listGenericModule(new InspeccionPiscinasModel(), 'Piscinas', 'piscinas', 'client/inspecciones/piscinas', 'fecha_inspeccion');
    }

    public function viewPiscinas(int $id)
    {
        return $this->viewGenericModule(new InspeccionPiscinasModel(), $id, 'Piscinas', 'piscinas', 'client/inspecciones/piscinas', 'fecha_inspeccion', 'inspecciones/piscinas/pdf');
    }

    public function listPiscinero()
    {
        return $this->listGenericModule(new InspeccionPiscineroModel(), 'Piscinero / Salvavidas', 'piscinero', 'client/inspecciones/piscinero', 'fecha_inspeccion');
    }

    public function viewPiscinero(int $id)
    {
        return $this->viewGenericModule(new InspeccionPiscineroModel(), $id, 'Piscinero / Salvavidas', 'piscinero', 'client/inspecciones/piscinero', 'fecha_inspeccion', 'inspecciones/piscinero/pdf');
    }

    public function listEvaluacionCapacitacion()
    {
        return $this->listGenericModule(new EvaluacionCapacitacionModel(), 'Evaluación Capacitación', 'evaluacion_capacitacion', 'client/inspecciones/evaluacion-capacitacion', 'created_at', null);
    }

    public function viewEvaluacionCapacitacion(int $id)
    {
        return $this->viewGenericModule(new EvaluacionCapacitacionModel(), $id, 'Evaluación Capacitación', 'evaluacion_capacitacion', 'client/inspecciones/evaluacion-capacitacion', 'created_at', null, null);
    }

    public function listContingenciaPlagas()
    {
        return $this->listGenericModule(new PlanContingenciaPlagasModel(), 'Contingencia Plagas', 'contingencia_plagas', 'client/inspecciones/contingencia-plagas', 'fecha_programa');
    }

    public function viewContingenciaPlagas(int $id)
    {
        return $this->viewGenericModule(new PlanContingenciaPlagasModel(), $id, 'Contingencia Plagas', 'contingencia_plagas', 'client/inspecciones/contingencia-plagas', 'fecha_programa', 'inspecciones/contingencia-plagas/pdf');
    }

    public function listContingenciaLimpieza()
    {
        return $this->listGenericModule(new PlanContingenciaLimpiezaDesinfeccionModel(), 'Contingencia Limpieza y Desinfección', 'contingencia_limpieza', 'client/inspecciones/contingencia-limpieza-desinfeccion', 'fecha_programa');
    }

    public function viewContingenciaLimpieza(int $id)
    {
        return $this->viewGenericModule(new PlanContingenciaLimpiezaDesinfeccionModel(), $id, 'Contingencia Limpieza y Desinfección', 'contingencia_limpieza', 'client/inspecciones/contingencia-limpieza-desinfeccion', 'fecha_programa', 'inspecciones/contingencia-limpieza-desinfeccion/pdf');
    }

    public function listContingenciaAgua()
    {
        return $this->listGenericModule(new PlanContingenciaAguaModel(), 'Contingencia Sin Agua', 'contingencia_agua', 'client/inspecciones/contingencia-agua', 'fecha_programa');
    }

    public function viewContingenciaAgua(int $id)
    {
        return $this->viewGenericModule(new PlanContingenciaAguaModel(), $id, 'Contingencia Sin Agua', 'contingencia_agua', 'client/inspecciones/contingencia-agua', 'fecha_programa', 'inspecciones/contingencia-agua/pdf');
    }

    public function listContingenciaBasura()
    {
        return $this->listGenericModule(new PlanContingenciaBasuraModel(), 'Contingencia Basura', 'contingencia_basura', 'client/inspecciones/contingencia-basura', 'fecha_programa');
    }

    public function viewContingenciaBasura(int $id)
    {
        return $this->viewGenericModule(new PlanContingenciaBasuraModel(), $id, 'Contingencia Basura', 'contingencia_basura', 'client/inspecciones/contingencia-basura', 'fecha_programa', 'inspecciones/contingencia-basura/pdf');
    }

    public function listLavadoTanques()
    {
        return $this->listGenericModule(new CertificadoServicioModel(), 'Lavado de Tanques', 'lavado_tanques', 'client/inspecciones/lavado-tanques', 'fecha_servicio', null, ['id_mantenimiento' => 2]);
    }

    public function viewLavadoTanques(int $id)
    {
        return $this->viewGenericModule(new CertificadoServicioModel(), $id, 'Lavado de Tanques', 'lavado_tanques', 'client/inspecciones/lavado-tanques', 'fecha_servicio', null, null, ['id_mantenimiento' => 2]);
    }

    public function listFumigacion()
    {
        return $this->listGenericModule(new CertificadoServicioModel(), 'Fumigación', 'fumigacion', 'client/inspecciones/fumigacion', 'fecha_servicio', null, ['id_mantenimiento' => 3]);
    }

    public function viewFumigacion(int $id)
    {
        return $this->viewGenericModule(new CertificadoServicioModel(), $id, 'Fumigación', 'fumigacion', 'client/inspecciones/fumigacion', 'fecha_servicio', null, null, ['id_mantenimiento' => 3]);
    }

    public function listDesratizacion()
    {
        return $this->listGenericModule(new CertificadoServicioModel(), 'Desratización', 'desratizacion', 'client/inspecciones/desratizacion', 'fecha_servicio', null, ['id_mantenimiento' => 4]);
    }

    public function viewDesratizacion(int $id)
    {
        return $this->viewGenericModule(new CertificadoServicioModel(), $id, 'Desratización', 'desratizacion', 'client/inspecciones/desratizacion', 'fecha_servicio', null, null, ['id_mantenimiento' => 4]);
    }

    public function listPlanillaSS()
    {
        return $this->listGenericModule(new PlanillaSSModel(), 'Planilla Seg. Social', 'planilla_ss', 'client/inspecciones/planilla-ss', 'created_at', null);
    }

    public function viewPlanillaSS(int $id)
    {
        return $this->viewGenericModule(new PlanillaSSModel(), $id, 'Planilla Seg. Social', 'planilla_ss', 'client/inspecciones/planilla-ss', 'created_at', null, null);
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

    // ─── RESIDUOS SÓLIDOS ───────────────────────────────────

    public function listResiduos()
    {
        $clientId = $this->getClientId();
        if (!$clientId) return redirect()->to('/login')->with('error', 'Acceso no autorizado.');

        $client = (new ClientModel())->find($clientId);
        $model = new ProgramaResiduosModel();
        $inspecciones = $model->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_programa', 'DESC')->findAll();

        return view('client/inspecciones/layout', [
            'client'  => $client,
            'title'   => 'Residuos Sólidos',
            'content' => view('client/inspecciones/list', [
                'inspecciones' => $inspecciones,
                'tipo'         => 'residuos',
                'titulo'       => 'Programas de Manejo Integral de Residuos Sólidos',
                'campo_fecha'  => 'fecha_programa',
                'base_url'     => 'client/inspecciones/residuos-solidos',
            ]),
        ]);
    }

    public function viewResiduos($id)
    {
        $clientId = $this->getClientId();
        if (!$clientId) return redirect()->to('/login')->with('error', 'Acceso no autorizado.');

        $model = new ProgramaResiduosModel();
        $inspeccion = $model->find($id);
        if (!$inspeccion || (int)$inspeccion['id_cliente'] !== (int)$clientId) {
            return redirect()->to('/client/inspecciones')->with('error', 'Documento no encontrado.');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        return view('client/inspecciones/layout', [
            'client'  => $clientModel->find($clientId),
            'title'   => 'Programa Residuos Sólidos',
            'content' => view('client/inspecciones/residuos_view', [
                'inspeccion' => $inspeccion,
                'cliente'    => $clientModel->find($inspeccion['id_cliente']),
                'consultor'  => $consultantModel->find($inspeccion['id_consultor']),
            ]),
        ]);
    }

    // ─── CONTROL DE PLAGAS ──────────────────────────────────

    public function listPlagas()
    {
        $clientId = $this->getClientId();
        if (!$clientId) return redirect()->to('/login')->with('error', 'Acceso no autorizado.');

        $client = (new ClientModel())->find($clientId);
        $model = new ProgramaPlagasModel();
        $inspecciones = $model->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_programa', 'DESC')->findAll();

        return view('client/inspecciones/layout', [
            'client'  => $client,
            'title'   => 'Control de Plagas',
            'content' => view('client/inspecciones/list', [
                'inspecciones' => $inspecciones,
                'tipo'         => 'plagas',
                'titulo'       => 'Programas de Control Integrado de Plagas',
                'campo_fecha'  => 'fecha_programa',
                'base_url'     => 'client/inspecciones/control-plagas',
            ]),
        ]);
    }

    public function viewPlagas($id)
    {
        $clientId = $this->getClientId();
        if (!$clientId) return redirect()->to('/login')->with('error', 'Acceso no autorizado.');

        $model = new ProgramaPlagasModel();
        $inspeccion = $model->find($id);
        if (!$inspeccion || (int)$inspeccion['id_cliente'] !== (int)$clientId) {
            return redirect()->to('/client/inspecciones')->with('error', 'Documento no encontrado.');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        return view('client/inspecciones/layout', [
            'client'  => $clientModel->find($clientId),
            'title'   => 'Programa Control de Plagas',
            'content' => view('client/inspecciones/plagas_view', [
                'inspeccion' => $inspeccion,
                'cliente'    => $clientModel->find($inspeccion['id_cliente']),
                'consultor'  => $consultantModel->find($inspeccion['id_consultor']),
            ]),
        ]);
    }

    // ─── AGUA POTABLE ───────────────────────────────────────

    public function listAguaPotable()
    {
        $clientId = $this->getClientId();
        if (!$clientId) return redirect()->to('/login')->with('error', 'Acceso no autorizado.');

        $client = (new ClientModel())->find($clientId);
        $model = new ProgramaAguaPotableModel();
        $inspecciones = $model->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_programa', 'DESC')->findAll();

        return view('client/inspecciones/layout', [
            'client'  => $client,
            'title'   => 'Agua Potable',
            'content' => view('client/inspecciones/list', [
                'inspecciones' => $inspecciones,
                'tipo'         => 'agua_potable',
                'titulo'       => 'Programas de Abastecimiento y Control de Agua Potable',
                'campo_fecha'  => 'fecha_programa',
                'base_url'     => 'client/inspecciones/agua-potable',
            ]),
        ]);
    }

    public function viewAguaPotable($id)
    {
        $clientId = $this->getClientId();
        if (!$clientId) return redirect()->to('/login')->with('error', 'Acceso no autorizado.');

        $model = new ProgramaAguaPotableModel();
        $inspeccion = $model->find($id);
        if (!$inspeccion || (int)$inspeccion['id_cliente'] !== (int)$clientId) {
            return redirect()->to('/client/inspecciones')->with('error', 'Documento no encontrado.');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        return view('client/inspecciones/layout', [
            'client'  => $clientModel->find($clientId),
            'title'   => 'Programa Agua Potable',
            'content' => view('client/inspecciones/agua_potable_view', [
                'inspeccion' => $inspeccion,
                'cliente'    => $clientModel->find($inspeccion['id_cliente']),
                'consultor'  => $consultantModel->find($inspeccion['id_consultor']),
            ]),
        ]);
    }

    // ─── PLAN DE SANEAMIENTO BÁSICO ────────────────────────

    public function listSaneamiento()
    {
        $clientId = $this->getClientId();
        if (!$clientId) return redirect()->to('/login')->with('error', 'Acceso no autorizado.');

        $client = (new ClientModel())->find($clientId);
        $model = new PlanSaneamientoModel();
        $inspecciones = $model->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_programa', 'DESC')->findAll();

        return view('client/inspecciones/layout', [
            'client'  => $client,
            'title'   => 'Plan Saneamiento',
            'content' => view('client/inspecciones/list', [
                'inspecciones' => $inspecciones,
                'tipo'         => 'plan_saneamiento',
                'titulo'       => 'Plan de Saneamiento Básico',
                'campo_fecha'  => 'fecha_programa',
                'base_url'     => 'client/inspecciones/plan-saneamiento',
            ]),
        ]);
    }

    public function viewSaneamiento($id)
    {
        $clientId = $this->getClientId();
        if (!$clientId) return redirect()->to('/login')->with('error', 'Acceso no autorizado.');

        $model = new PlanSaneamientoModel();
        $inspeccion = $model->find($id);
        if (!$inspeccion || (int)$inspeccion['id_cliente'] !== (int)$clientId) {
            return redirect()->to('/client/inspecciones')->with('error', 'Documento no encontrado.');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        return view('client/inspecciones/layout', [
            'client'  => $clientModel->find($clientId),
            'title'   => 'Plan de Saneamiento Básico',
            'content' => view('client/inspecciones/saneamiento_view', [
                'inspeccion' => $inspeccion,
                'cliente'    => $clientModel->find($inspeccion['id_cliente']),
                'consultor'  => $consultantModel->find($inspeccion['id_consultor']),
            ]),
        ]);
    }

    // ─── DOTACIÓN VIGILANTE ─────────────────────────────────

    public function listDotacionVigilante()
    {
        $clientId = $this->getClientId();
        if (!$clientId) return redirect()->to('/login')->with('error', 'Acceso no autorizado.');

        $client = (new ClientModel())->find($clientId);
        $model = new DotacionVigilanteModel();
        $inspecciones = $model->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->findAll();

        return view('client/inspecciones/layout', [
            'client'  => $client,
            'title'   => 'Dotación Vigilante',
            'content' => view('client/inspecciones/list', [
                'inspecciones' => $inspecciones,
                'tipo'         => 'dotacion_vigilante',
                'titulo'       => 'Dotación Vigilante',
                'campo_fecha'  => 'fecha_inspeccion',
                'base_url'     => 'client/inspecciones/dotacion-vigilante',
            ]),
        ]);
    }

    public function viewDotacionVigilante($id)
    {
        $clientId = $this->getClientId();
        if (!$clientId) return redirect()->to('/login')->with('error', 'Acceso no autorizado.');

        $model = new DotacionVigilanteModel();
        $inspeccion = $model->find($id);
        if (!$inspeccion || (int)$inspeccion['id_cliente'] !== (int)$clientId) {
            return redirect()->to('/client/inspecciones')->with('error', 'Inspección no encontrada.');
        }

        $data = [
            'inspeccion' => $inspeccion,
            'cliente'    => (new ClientModel())->find($inspeccion['id_cliente']),
            'itemsEpp'   => DotacionVigilanteController::ITEMS_EPP,
            'estadosEpp' => DotacionVigilanteController::ESTADOS_EPP,
        ];

        return view('client/inspecciones/layout', [
            'client'  => (new ClientModel())->find($clientId),
            'title'   => 'Dotación Vigilante',
            'content' => view('client/inspecciones/dotacion_vigilante_view', $data),
        ]);
    }

    // ─── DOTACIÓN ASEADORA ──────────────────────────────────

    public function listDotacionAseadora()
    {
        $clientId = $this->getClientId();
        if (!$clientId) return redirect()->to('/login')->with('error', 'Acceso no autorizado.');

        $client = (new ClientModel())->find($clientId);
        $model = new DotacionAseadoraModel();
        $inspecciones = $model->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->findAll();

        return view('client/inspecciones/layout', [
            'client'  => $client,
            'title'   => 'Dotación Aseadora',
            'content' => view('client/inspecciones/list', [
                'inspecciones' => $inspecciones,
                'tipo'         => 'dotacion_aseadora',
                'titulo'       => 'Dotación Aseadora',
                'campo_fecha'  => 'fecha_inspeccion',
                'base_url'     => 'client/inspecciones/dotacion-aseadora',
            ]),
        ]);
    }

    public function viewDotacionAseadora($id)
    {
        $clientId = $this->getClientId();
        if (!$clientId) return redirect()->to('/login')->with('error', 'Acceso no autorizado.');

        $model = new DotacionAseadoraModel();
        $inspeccion = $model->find($id);
        if (!$inspeccion || (int)$inspeccion['id_cliente'] !== (int)$clientId) {
            return redirect()->to('/client/inspecciones')->with('error', 'Inspección no encontrada.');
        }

        $data = [
            'inspeccion' => $inspeccion,
            'cliente'    => (new ClientModel())->find($inspeccion['id_cliente']),
            'itemsEpp'   => DotacionAseadoraController::ITEMS_EPP,
            'estadosEpp' => DotacionAseadoraController::ESTADOS_EPP,
        ];

        return view('client/inspecciones/layout', [
            'client'  => (new ClientModel())->find($clientId),
            'title'   => 'Dotación Aseadora',
            'content' => view('client/inspecciones/dotacion_aseadora_view', $data),
        ]);
    }

    // ─── DOTACIÓN TODERO ────────────────────────────────────

    public function listDotacionTodero()
    {
        $clientId = $this->getClientId();
        if (!$clientId) return redirect()->to('/login')->with('error', 'Acceso no autorizado.');

        $client = (new ClientModel())->find($clientId);
        $model = new DotacionToderoModel();
        $inspecciones = $model->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->findAll();

        return view('client/inspecciones/layout', [
            'client'  => $client,
            'title'   => 'Dotación Todero',
            'content' => view('client/inspecciones/list', [
                'inspecciones' => $inspecciones,
                'tipo'         => 'dotacion_todero',
                'titulo'       => 'Dotación Todero',
                'campo_fecha'  => 'fecha_inspeccion',
                'base_url'     => 'client/inspecciones/dotacion-todero',
            ]),
        ]);
    }

    public function viewDotacionTodero($id)
    {
        $clientId = $this->getClientId();
        if (!$clientId) return redirect()->to('/login')->with('error', 'Acceso no autorizado.');

        $model = new DotacionToderoModel();
        $inspeccion = $model->find($id);
        if (!$inspeccion || (int)$inspeccion['id_cliente'] !== (int)$clientId) {
            return redirect()->to('/client/inspecciones')->with('error', 'Inspección no encontrada.');
        }

        $data = [
            'inspeccion' => $inspeccion,
            'cliente'    => (new ClientModel())->find($inspeccion['id_cliente']),
            'itemsEpp'   => DotacionToderoController::ITEMS_EPP,
            'estadosEpp' => DotacionToderoController::ESTADOS_EPP,
        ];

        return view('client/inspecciones/layout', [
            'client'  => (new ClientModel())->find($clientId),
            'title'   => 'Dotación Todero',
            'content' => view('client/inspecciones/dotacion_todero_view', $data),
        ]);
    }

    // ─── AUDITORÍA ZONA DE RESIDUOS ─────────────────────────

    public function listAuditoriaResiduos()
    {
        $clientId = $this->getClientId();
        if (!$clientId) return redirect()->to('/login')->with('error', 'Acceso no autorizado.');

        $client = (new ClientModel())->find($clientId);
        $model = new AuditoriaZonaResiduosModel();
        $inspecciones = $model->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->findAll();

        return view('client/inspecciones/layout', [
            'client'  => $client,
            'title'   => 'Auditoría Zona de Residuos',
            'content' => view('client/inspecciones/list', [
                'inspecciones' => $inspecciones,
                'tipo'         => 'auditoria_residuos',
                'titulo'       => 'Auditoría Zona de Residuos',
                'campo_fecha'  => 'fecha_inspeccion',
                'base_url'     => 'client/inspecciones/auditoria-zona-residuos',
            ]),
        ]);
    }

    public function viewAuditoriaResiduos($id)
    {
        $clientId = $this->getClientId();
        if (!$clientId) return redirect()->to('/login')->with('error', 'Acceso no autorizado.');

        $model = new AuditoriaZonaResiduosModel();
        $inspeccion = $model->find($id);
        if (!$inspeccion || (int)$inspeccion['id_cliente'] !== (int)$clientId) {
            return redirect()->to('/client/inspecciones')->with('error', 'Inspección no encontrada.');
        }

        $data = [
            'inspeccion' => $inspeccion,
            'cliente'    => (new ClientModel())->find($inspeccion['id_cliente']),
            'itemsZona'  => AuditoriaZonaResiduosController::ITEMS_ZONA,
            'estadosZona' => AuditoriaZonaResiduosController::ESTADOS_ZONA,
        ];

        return view('client/inspecciones/layout', [
            'client'  => (new ClientModel())->find($clientId),
            'title'   => 'Auditoría Zona de Residuos',
            'content' => view('client/inspecciones/auditoria_zona_residuos_view', $data),
        ]);
    }

    // ─── ASISTENCIA INDUCCIÓN ───────────────────────────────

    public function listAsistenciaCapacitacion()
    {
        $clientId = $this->getClientId();
        if (!$clientId) return redirect()->to('/login')->with('error', 'Acceso no autorizado.');

        $client = (new ClientModel())->find($clientId);
        $model = new AsistenciaCapacitacionModel();
        $inspecciones = $model->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_sesion', 'DESC')->findAll();

        return view('client/inspecciones/layout', [
            'client'  => $client,
            'title'   => 'Asistencia Inducción',
            'content' => view('client/inspecciones/list', [
                'inspecciones' => $inspecciones,
                'tipo'         => 'asistencia_induccion',
                'titulo'       => 'Asistencia Inducción',
                'campo_fecha'  => 'fecha_sesion',
                'base_url'     => 'client/inspecciones/asistencia-capacitacion',
            ]),
        ]);
    }

    public function viewAsistenciaCapacitacion($id)
    {
        $clientId = $this->getClientId();
        if (!$clientId) return redirect()->to('/login')->with('error', 'Acceso no autorizado.');

        $model = new AsistenciaCapacitacionModel();
        $inspeccion = $model->find($id);
        if (!$inspeccion || (int)$inspeccion['id_cliente'] !== (int)$clientId) {
            return redirect()->to('/client/inspecciones')->with('error', 'Registro no encontrado.');
        }

        $asistentes = (new AsistenciaCapacitacionAsistenteModel())->where('id_asistencia_induccion', $id)->findAll();

        $data = [
            'inspeccion'   => $inspeccion,
            'cliente'      => (new ClientModel())->find($inspeccion['id_cliente']),
            'asistentes'   => $asistentes,
            'tiposCharla'  => AsistenciaCapacitacionController::TIPOS_CHARLA,
        ];

        return view('client/inspecciones/layout', [
            'client'  => (new ClientModel())->find($clientId),
            'title'   => 'Asistencia Inducción',
            'content' => view('client/inspecciones/asistencia_capacitacion_view', $data),
        ]);
    }

    // ─── REPORTE DE CAPACITACIÓN ────────────────────────────

    public function listReporteCapacitacion()
    {
        $clientId = $this->getClientId();
        if (!$clientId) return redirect()->to('/login')->with('error', 'Acceso no autorizado.');

        $client = (new ClientModel())->find($clientId);
        $model = new ReporteCapacitacionModel();
        $inspecciones = $model->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_capacitacion', 'DESC')->findAll();

        return view('client/inspecciones/layout', [
            'client'  => $client,
            'title'   => 'Reportes de Capacitación',
            'content' => view('client/inspecciones/list', [
                'inspecciones' => $inspecciones,
                'tipo'         => 'reporte_capacitacion',
                'titulo'       => 'Reportes de Capacitación',
                'campo_fecha'  => 'fecha_capacitacion',
                'base_url'     => 'client/inspecciones/reporte-capacitacion',
            ]),
        ]);
    }

    public function viewReporteCapacitacion($id)
    {
        $clientId = $this->getClientId();
        if (!$clientId) return redirect()->to('/login')->with('error', 'Acceso no autorizado.');

        $model = new ReporteCapacitacionModel();
        $inspeccion = $model->find($id);
        if (!$inspeccion || (int)$inspeccion['id_cliente'] !== (int)$clientId) {
            return redirect()->to('/client/inspecciones')->with('error', 'Reporte no encontrado.');
        }

        $data = [
            'inspeccion'         => $inspeccion,
            'cliente'            => (new ClientModel())->find($inspeccion['id_cliente']),
            'perfilesAsistentes' => ReporteCapacitacionController::PERFILES_ASISTENTES,
        ];

        return view('client/inspecciones/layout', [
            'client'  => (new ClientModel())->find($clientId),
            'title'   => 'Reporte de Capacitación',
            'content' => view('client/inspecciones/reporte_capacitacion_view', $data),
        ]);
    }

    // ─── PREPARACIÓN SIMULACRO ──────────────────────────────

    public function listPreparacionSimulacro()
    {
        $clientId = $this->getClientId();
        if (!$clientId) return redirect()->to('/login')->with('error', 'Acceso no autorizado.');

        $client = (new ClientModel())->find($clientId);
        $model = new PreparacionSimulacroModel();
        $inspecciones = $model->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_simulacro', 'DESC')->findAll();

        return view('client/inspecciones/layout', [
            'client'  => $client,
            'title'   => 'Preparación Simulacro',
            'content' => view('client/inspecciones/list', [
                'inspecciones' => $inspecciones,
                'tipo'         => 'preparacion_simulacro',
                'titulo'       => 'Preparación Simulacro',
                'campo_fecha'  => 'fecha_simulacro',
                'base_url'     => 'client/inspecciones/preparacion-simulacro',
            ]),
        ]);
    }

    public function viewPreparacionSimulacro($id)
    {
        $clientId = $this->getClientId();
        if (!$clientId) return redirect()->to('/login')->with('error', 'Acceso no autorizado.');

        $model = new PreparacionSimulacroModel();
        $inspeccion = $model->find($id);
        if (!$inspeccion || (int)$inspeccion['id_cliente'] !== (int)$clientId) {
            return redirect()->to('/client/inspecciones')->with('error', 'Registro no encontrado.');
        }

        $data = [
            'inspeccion'          => $inspeccion,
            'cliente'             => (new ClientModel())->find($inspeccion['id_cliente']),
            'opcionesAlarma'      => PreparacionSimulacroController::OPCIONES_ALARMA,
            'opcionesDistintivos' => PreparacionSimulacroController::OPCIONES_DISTINTIVOS,
            'opcionesEquipos'     => PreparacionSimulacroController::OPCIONES_EQUIPOS,
            'cronogramaItems'     => PreparacionSimulacroController::CRONOGRAMA_ITEMS,
        ];

        return view('client/inspecciones/layout', [
            'client'  => (new ClientModel())->find($clientId),
            'title'   => 'Preparación Simulacro',
            'content' => view('client/inspecciones/preparacion_simulacro_view', $data),
        ]);
    }

    // ─── KPI LIMPIEZA Y DESINFECCIÓN ────────────────────────

    public function listKpiLimpieza()
    {
        $clientId = $this->getClientId();
        if (!$clientId) return redirect()->to('/login')->with('error', 'Acceso no autorizado.');

        $client = (new ClientModel())->find($clientId);
        $model = new KpiLimpiezaModel();
        $inspecciones = $model->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->findAll();

        return view('client/inspecciones/layout', [
            'client'  => $client,
            'title'   => 'KPI Limpieza',
            'content' => view('client/inspecciones/list', [
                'inspecciones' => $inspecciones,
                'tipo'         => 'kpi_limpieza',
                'titulo'       => 'KPI Programa de Limpieza y Desinfección',
                'campo_fecha'  => 'fecha_inspeccion',
                'base_url'     => 'client/inspecciones/kpi-limpieza',
            ]),
        ]);
    }

    public function viewKpiLimpieza($id)
    {
        $clientId = $this->getClientId();
        if (!$clientId) return redirect()->to('/login')->with('error', 'Acceso no autorizado.');

        $model = new KpiLimpiezaModel();
        $inspeccion = $model->find($id);
        if (!$inspeccion || (int)$inspeccion['id_cliente'] !== (int)$clientId) {
            return redirect()->to('/client/inspecciones')->with('error', 'Documento no encontrado.');
        }

        return view('client/inspecciones/layout', [
            'client'  => (new ClientModel())->find($clientId),
            'title'   => 'KPI Limpieza',
            'content' => view('client/inspecciones/kpi_limpieza_view', [
                'inspeccion' => $inspeccion,
                'cliente'    => (new ClientModel())->find($inspeccion['id_cliente']),
                'consultor'  => (new ConsultantModel())->find($inspeccion['id_consultor']),
            ]),
        ]);
    }

    // ─── KPI RESIDUOS SÓLIDOS ───────────────────────────────

    public function listKpiResiduos()
    {
        $clientId = $this->getClientId();
        if (!$clientId) return redirect()->to('/login')->with('error', 'Acceso no autorizado.');

        $client = (new ClientModel())->find($clientId);
        $model = new KpiResiduosModel();
        $inspecciones = $model->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->findAll();

        return view('client/inspecciones/layout', [
            'client'  => $client,
            'title'   => 'KPI Residuos',
            'content' => view('client/inspecciones/list', [
                'inspecciones' => $inspecciones,
                'tipo'         => 'kpi_residuos',
                'titulo'       => 'KPI Programa de Manejo Integral de Residuos Sólidos',
                'campo_fecha'  => 'fecha_inspeccion',
                'base_url'     => 'client/inspecciones/kpi-residuos',
            ]),
        ]);
    }

    public function viewKpiResiduos($id)
    {
        $clientId = $this->getClientId();
        if (!$clientId) return redirect()->to('/login')->with('error', 'Acceso no autorizado.');

        $model = new KpiResiduosModel();
        $inspeccion = $model->find($id);
        if (!$inspeccion || (int)$inspeccion['id_cliente'] !== (int)$clientId) {
            return redirect()->to('/client/inspecciones')->with('error', 'Documento no encontrado.');
        }

        return view('client/inspecciones/layout', [
            'client'  => (new ClientModel())->find($clientId),
            'title'   => 'KPI Residuos',
            'content' => view('client/inspecciones/kpi_residuos_view', [
                'inspeccion' => $inspeccion,
                'cliente'    => (new ClientModel())->find($inspeccion['id_cliente']),
                'consultor'  => (new ConsultantModel())->find($inspeccion['id_consultor']),
            ]),
        ]);
    }

    // ─── KPI CONTROL DE PLAGAS ──────────────────────────────

    public function listKpiPlagas()
    {
        $clientId = $this->getClientId();
        if (!$clientId) return redirect()->to('/login')->with('error', 'Acceso no autorizado.');

        $client = (new ClientModel())->find($clientId);
        $model = new KpiPlagasModel();
        $inspecciones = $model->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->findAll();

        return view('client/inspecciones/layout', [
            'client'  => $client,
            'title'   => 'KPI Plagas',
            'content' => view('client/inspecciones/list', [
                'inspecciones' => $inspecciones,
                'tipo'         => 'kpi_plagas',
                'titulo'       => 'KPI Programa de Control Integrado de Plagas',
                'campo_fecha'  => 'fecha_inspeccion',
                'base_url'     => 'client/inspecciones/kpi-plagas',
            ]),
        ]);
    }

    public function viewKpiPlagas($id)
    {
        $clientId = $this->getClientId();
        if (!$clientId) return redirect()->to('/login')->with('error', 'Acceso no autorizado.');

        $model = new KpiPlagasModel();
        $inspeccion = $model->find($id);
        if (!$inspeccion || (int)$inspeccion['id_cliente'] !== (int)$clientId) {
            return redirect()->to('/client/inspecciones')->with('error', 'Documento no encontrado.');
        }

        return view('client/inspecciones/layout', [
            'client'  => (new ClientModel())->find($clientId),
            'title'   => 'KPI Plagas',
            'content' => view('client/inspecciones/kpi_plagas_view', [
                'inspeccion' => $inspeccion,
                'cliente'    => (new ClientModel())->find($inspeccion['id_cliente']),
                'consultor'  => (new ConsultantModel())->find($inspeccion['id_consultor']),
            ]),
        ]);
    }

    // ─── KPI AGUA POTABLE ───────────────────────────────────

    public function listKpiAguaPotable()
    {
        $clientId = $this->getClientId();
        if (!$clientId) return redirect()->to('/login')->with('error', 'Acceso no autorizado.');

        $client = (new ClientModel())->find($clientId);
        $model = new KpiAguaPotableModel();
        $inspecciones = $model->where('id_cliente', $clientId)->where('estado', 'completo')->orderBy('fecha_inspeccion', 'DESC')->findAll();

        return view('client/inspecciones/layout', [
            'client'  => $client,
            'title'   => 'KPI Agua Potable',
            'content' => view('client/inspecciones/list', [
                'inspecciones' => $inspecciones,
                'tipo'         => 'kpi_agua_potable',
                'titulo'       => 'KPI Programa de Abastecimiento y Control de Agua Potable',
                'campo_fecha'  => 'fecha_inspeccion',
                'base_url'     => 'client/inspecciones/kpi-agua-potable',
            ]),
        ]);
    }

    public function viewKpiAguaPotable($id)
    {
        $clientId = $this->getClientId();
        if (!$clientId) return redirect()->to('/login')->with('error', 'Acceso no autorizado.');

        $model = new KpiAguaPotableModel();
        $inspeccion = $model->find($id);
        if (!$inspeccion || (int)$inspeccion['id_cliente'] !== (int)$clientId) {
            return redirect()->to('/client/inspecciones')->with('error', 'Documento no encontrado.');
        }

        return view('client/inspecciones/layout', [
            'client'  => (new ClientModel())->find($clientId),
            'title'   => 'KPI Agua Potable',
            'content' => view('client/inspecciones/kpi_agua_potable_view', [
                'inspeccion' => $inspeccion,
                'cliente'    => (new ClientModel())->find($inspeccion['id_cliente']),
                'consultor'  => (new ConsultantModel())->find($inspeccion['id_consultor']),
            ]),
        ]);
    }

    public function dashboardSaneamiento()
    {
        $clientId = $this->getClientId();
        if (!$clientId) return redirect()->to('/login')->with('error', 'Acceso no autorizado.');

        $resultados = DashboardSaneamientoController::consolidar($clientId);

        return view('client/inspecciones/layout', [
            'client'  => (new ClientModel())->find($clientId),
            'title'   => 'Dashboard Saneamiento',
            'content' => view('client/inspecciones/dashboard_saneamiento', [
                'resultados' => $resultados,
            ]),
        ]);
    }
}
