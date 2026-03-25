<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\ActaVisitaModel;
use App\Models\InspeccionLocativaModel;
use App\Models\InspeccionSenalizacionModel;
use App\Models\InspeccionExtintoresModel;
use App\Models\InspeccionBotiquinModel;
use App\Models\InspeccionGabineteModel;
use App\Models\InspeccionComunicacionModel;
use App\Models\InspeccionRecursosSeguridadModel;
use App\Models\ProbabilidadPeligrosModel;
use App\Models\MatrizVulnerabilidadModel;
use App\Models\ClientModel;
use App\Models\PendientesModel;
use App\Models\VencimientosMantenimientoModel;
use App\Models\CartaVigiaModel;
use App\Models\PlanEmergenciaModel;
use App\Models\EvaluacionSimulacroModel;
use App\Models\HvBrigadistaModel;
use App\Models\DotacionVigilanteModel;
use App\Models\DotacionAseadoraModel;
use App\Models\DotacionToderoModel;
use App\Models\AuditoriaZonaResiduosModel;
use App\Models\ReporteCapacitacionModel;
use App\Models\PreparacionSimulacroModel;
use App\Models\AsistenciaInduccionModel;
use App\Models\EvaluacionInduccionModel;
use App\Models\ProgramaLimpiezaModel;
use App\Models\ProgramaResiduosModel;
use App\Models\ProgramaPlagasModel;
use App\Models\ProgramaAguaPotableModel;
use App\Models\PlanSaneamientoModel;
use App\Models\PlanContingenciaPlagasModel;
use App\Models\PlanContingenciaAguaModel;
use App\Models\PlanContingenciaBasuraModel;
use App\Models\KpiLimpiezaModel;
use App\Models\KpiResiduosModel;
use App\Models\KpiPlagasModel;
use App\Models\KpiAguaPotableModel;
use App\Models\AgendamientoModel;
use App\Models\CertificadoServicioModel;
use App\Models\PlanillaSSModel;

class InspeccionesController extends BaseController
{
    /**
     * Dashboard principal de inspecciones (PWA)
     */
    public function dashboard()
    {
        $actaModel = new ActaVisitaModel();
        $pendientes = $actaModel->getAllPendientes();
        $totalActas = $actaModel->where('estado', 'completo')->countAllResults();

        $locativaModel = new InspeccionLocativaModel();
        $totalLocativas = $locativaModel->where('estado', 'completo')->countAllResults();
        $pendientesLocativas = $locativaModel->getAllPendientes();

        $senalizacionModel = new InspeccionSenalizacionModel();
        $totalSenalizacion = $senalizacionModel->where('estado', 'completo')->countAllResults();
        $pendientesSenalizacion = $senalizacionModel
            ->select('tbl_inspeccion_senalizacion.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_senalizacion.id_cliente', 'left')
            ->where('tbl_inspeccion_senalizacion.estado', 'borrador')
            ->orderBy('tbl_inspeccion_senalizacion.updated_at', 'DESC')
            ->findAll();

        $extintoresModel = new InspeccionExtintoresModel();
        $totalExtintores = $extintoresModel->where('estado', 'completo')->countAllResults();
        $pendientesExtintores = $extintoresModel
            ->select('tbl_inspeccion_extintores.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_extintores.id_cliente', 'left')
            ->where('tbl_inspeccion_extintores.estado', 'borrador')
            ->orderBy('tbl_inspeccion_extintores.updated_at', 'DESC')
            ->findAll();

        $botiquinModel = new InspeccionBotiquinModel();
        $totalBotiquin = $botiquinModel->where('estado', 'completo')->countAllResults();
        $pendientesBotiquin = $botiquinModel
            ->select('tbl_inspeccion_botiquin.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_botiquin.id_cliente', 'left')
            ->where('tbl_inspeccion_botiquin.estado', 'borrador')
            ->orderBy('tbl_inspeccion_botiquin.updated_at', 'DESC')
            ->findAll();

        $gabineteModel = new InspeccionGabineteModel();
        $totalGabinetes = $gabineteModel->where('estado', 'completo')->countAllResults();
        $pendientesGabinetes = $gabineteModel->getAllPendientes();

        $comunicacionModel = new InspeccionComunicacionModel();
        $totalComunicaciones = $comunicacionModel->where('estado', 'completo')->countAllResults();
        $pendientesComunicaciones = $comunicacionModel->getAllPendientes();

        $recursosSeguridadModel = new InspeccionRecursosSeguridadModel();
        $totalRecursosSeg = $recursosSeguridadModel->where('estado', 'completo')->countAllResults();
        $pendientesRecursosSeg = $recursosSeguridadModel->getAllPendientes();

        $probPeligrosModel = new ProbabilidadPeligrosModel();
        $totalProbPeligros = $probPeligrosModel->where('estado', 'completo')->countAllResults();
        $pendientesProbPeligros = $probPeligrosModel->getAllPendientes();

        $matrizVulModel = new MatrizVulnerabilidadModel();
        $totalMatrizVul = $matrizVulModel->where('estado', 'completo')->countAllResults();
        $pendientesMatrizVul = $matrizVulModel->getAllPendientes();

        $planEmgModel = new PlanEmergenciaModel();
        $totalPlanEmergencia = $planEmgModel->where('estado', 'completo')->countAllResults();
        $pendientesPlanEmg = $planEmgModel->getAllPendientes();

        $evalSimModel = new EvaluacionSimulacroModel();
        $totalSimulacro = $evalSimModel->where('estado', 'completo')->countAllResults();
        $pendientesSimulacro = $evalSimModel
            ->select('tbl_evaluacion_simulacro.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_evaluacion_simulacro.id_cliente', 'left')
            ->where('tbl_evaluacion_simulacro.estado', 'borrador')
            ->orderBy('tbl_evaluacion_simulacro.updated_at', 'DESC')
            ->findAll();

        $hvBrigModel = new HvBrigadistaModel();
        $totalHvBrigadista = $hvBrigModel->where('estado', 'completo')->countAllResults();
        $pendientesHvBrig = $hvBrigModel
            ->select('tbl_hv_brigadista.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_hv_brigadista.id_cliente', 'left')
            ->where('tbl_hv_brigadista.estado', 'borrador')
            ->orderBy('tbl_hv_brigadista.updated_at', 'DESC')
            ->findAll();

        $dotVigModel = new DotacionVigilanteModel();
        $totalDotVig = $dotVigModel->where('estado', 'completo')->countAllResults();
        $pendientesDotVig = $dotVigModel->getAllPendientes();

        $dotAseModel = new DotacionAseadoraModel();
        $totalDotAse = $dotAseModel->where('estado', 'completo')->countAllResults();
        $pendientesDotAse = $dotAseModel->getAllPendientes();

        $dotTodModel = new DotacionToderoModel();
        $totalDotTod = $dotTodModel->where('estado', 'completo')->countAllResults();
        $pendientesDotTod = $dotTodModel->getAllPendientes();

        $audResModel = new AuditoriaZonaResiduosModel();
        $totalAudRes = $audResModel->where('estado', 'completo')->countAllResults();
        $pendientesAudRes = $audResModel->getAllPendientes();

        $repCapModel = new ReporteCapacitacionModel();
        $totalRepCap = $repCapModel->where('estado', 'completo')->countAllResults();
        $pendientesRepCap = $repCapModel->getAllPendientes();

        $prepSimModel = new PreparacionSimulacroModel();
        $totalPrepSim = $prepSimModel->where('estado', 'completo')->countAllResults();
        $pendientesPrepSim = $prepSimModel->getAllPendientes();

        $asistIndModel = new AsistenciaInduccionModel();
        $totalAsistInd = $asistIndModel->where('estado', 'completo')->countAllResults();
        $pendientesAsistInd = $asistIndModel->getAllPendientes();

        $totalEvalInd = (new EvaluacionInduccionModel())->countAllResults();

        $progLimpModel = new ProgramaLimpiezaModel();
        $totalProgLimp = $progLimpModel->where('estado', 'completo')->countAllResults();
        $pendientesProgLimp = $progLimpModel->getAllPendientes();

        $progResModel = new ProgramaResiduosModel();
        $totalProgRes = $progResModel->where('estado', 'completo')->countAllResults();
        $pendientesProgRes = $progResModel->getAllPendientes();

        $progPlagModel = new ProgramaPlagasModel();
        $totalProgPlag = $progPlagModel->where('estado', 'completo')->countAllResults();
        $pendientesProgPlag = $progPlagModel->getAllPendientes();

        $progAguaModel = new ProgramaAguaPotableModel();
        $totalProgAgua = $progAguaModel->where('estado', 'completo')->countAllResults();
        $pendientesProgAgua = $progAguaModel->getAllPendientes();

        $planSanModel = new PlanSaneamientoModel();
        $totalPlanSan = $planSanModel->where('estado', 'completo')->countAllResults();
        $pendientesPlanSan = $planSanModel->getAllPendientes();

        $contPlagasModel = new PlanContingenciaPlagasModel();
        $totalContPlagas = $contPlagasModel->where('estado', 'completo')->countAllResults();
        $pendientesContPlagas = $contPlagasModel->getAllPendientes();

        $contAguaModel = new PlanContingenciaAguaModel();
        $totalContAgua = $contAguaModel->where('estado', 'completo')->countAllResults();
        $pendientesContAgua = $contAguaModel->getAllPendientes();

        $contBasuraModel = new PlanContingenciaBasuraModel();
        $totalContBasura = $contBasuraModel->where('estado', 'completo')->countAllResults();
        $pendientesContBasura = $contBasuraModel->getAllPendientes();

        $kpiLimpModel = new KpiLimpiezaModel();
        $totalKpiLimp = $kpiLimpModel->where('estado', 'completo')->countAllResults();
        $pendientesKpiLimp = $kpiLimpModel->getAllPendientes();

        $kpiResModel = new KpiResiduosModel();
        $totalKpiRes = $kpiResModel->where('estado', 'completo')->countAllResults();
        $pendientesKpiRes = $kpiResModel->getAllPendientes();

        $kpiPlagModel = new KpiPlagasModel();
        $totalKpiPlag = $kpiPlagModel->where('estado', 'completo')->countAllResults();
        $pendientesKpiPlag = $kpiPlagModel->getAllPendientes();

        $kpiAguaModel = new KpiAguaPotableModel();
        $totalKpiAgua = $kpiAguaModel->where('estado', 'completo')->countAllResults();
        $pendientesKpiAgua = $kpiAguaModel->getAllPendientes();

        $totalVencimientos = (new VencimientosMantenimientoModel())->where('estado_actividad', 'sin ejecutar')->countAllResults();
        $totalPendientesAbiertos = (new PendientesModel())->where('estado', 'ABIERTA')->countAllResults();
        $totalCartasVigiaPend = (new CartaVigiaModel())->where('estado_firma', 'pendiente_firma')->countAllResults();

        $certModel = new CertificadoServicioModel();
        $totalLavadoTanques  = $certModel->where('id_mantenimiento', 2)->countAllResults();
        $totalFumigacion     = $certModel->where('id_mantenimiento', 3)->countAllResults();
        $totalDesratizacion  = $certModel->where('id_mantenimiento', 4)->countAllResults();
        $totalPlanillaSS     = (new PlanillaSSModel())->countAllResults();
        $totalProveedores    = (new \App\Models\ProveedorServicioModel())->countAllResults();

        $data = [
            'title'            => 'Inspecciones SST',
            'pendientes'       => $pendientes,
            'pendientesLocativas' => $pendientesLocativas,
            'pendientesSenalizacion' => $pendientesSenalizacion,
            'pendientesExtintores' => $pendientesExtintores,
            'pendientesBotiquin' => $pendientesBotiquin,
            'pendientesGabinetes' => $pendientesGabinetes,
            'pendientesComunicaciones' => $pendientesComunicaciones,
            'pendientesRecursosSeg' => $pendientesRecursosSeg,
            'pendientesProbPeligros' => $pendientesProbPeligros,
            'pendientesMatrizVul' => $pendientesMatrizVul,
            'pendientesPlanEmg' => $pendientesPlanEmg,
            'pendientesSimulacro' => $pendientesSimulacro,
            'pendientesHvBrig' => $pendientesHvBrig,
            'pendientesDotVig' => $pendientesDotVig,
            'pendientesDotAse' => $pendientesDotAse,
            'pendientesDotTod' => $pendientesDotTod,
            'pendientesAudRes' => $pendientesAudRes,
            'pendientesRepCap' => $pendientesRepCap,
            'pendientesPrepSim' => $pendientesPrepSim,
            'pendientesAsistInd' => $pendientesAsistInd,
            'pendientesProgLimp' => $pendientesProgLimp,
            'pendientesProgRes' => $pendientesProgRes,
            'pendientesProgPlag' => $pendientesProgPlag,
            'pendientesProgAgua' => $pendientesProgAgua,
            'pendientesPlanSan' => $pendientesPlanSan,
            'pendientesContPlagas' => $pendientesContPlagas,
            'pendientesContAgua' => $pendientesContAgua,
            'pendientesContBasura' => $pendientesContBasura,
            'pendientesKpiLimp' => $pendientesKpiLimp,
            'pendientesKpiRes' => $pendientesKpiRes,
            'pendientesKpiPlag' => $pendientesKpiPlag,
            'pendientesKpiAgua' => $pendientesKpiAgua,
            'totalActas'       => $totalActas,
            'totalLocativas'   => $totalLocativas,
            'totalSenalizacion' => $totalSenalizacion,
            'totalExtintores'  => $totalExtintores,
            'totalBotiquin'    => $totalBotiquin,
            'totalGabinetes'   => $totalGabinetes,
            'totalComunicaciones' => $totalComunicaciones,
            'totalRecursosSeg' => $totalRecursosSeg,
            'totalProbPeligros' => $totalProbPeligros,
            'totalMatrizVul'   => $totalMatrizVul,
            'totalPlanEmergencia' => $totalPlanEmergencia,
            'totalSimulacro'   => $totalSimulacro,
            'totalHvBrigadista' => $totalHvBrigadista,
            'totalDotVig'      => $totalDotVig,
            'totalDotAse'      => $totalDotAse,
            'totalDotTod'      => $totalDotTod,
            'totalAudRes'      => $totalAudRes,
            'totalRepCap'      => $totalRepCap,
            'totalPrepSim'     => $totalPrepSim,
            'totalAsistInd'    => $totalAsistInd,
            'totalEvalInd'     => $totalEvalInd,
            'totalProgLimp'    => $totalProgLimp,
            'totalProgRes'     => $totalProgRes,
            'totalProgPlag'    => $totalProgPlag,
            'totalProgAgua'    => $totalProgAgua,
            'totalPlanSan'     => $totalPlanSan,
            'totalContPlagas'  => $totalContPlagas,
            'totalContAgua'    => $totalContAgua,
            'totalContBasura'  => $totalContBasura,
            'totalKpiLimp'     => $totalKpiLimp,
            'totalKpiRes'      => $totalKpiRes,
            'totalKpiPlag'     => $totalKpiPlag,
            'totalKpiAgua'     => $totalKpiAgua,
            'totalVencimientos' => $totalVencimientos,
            'totalPendientesAbiertos' => $totalPendientesAbiertos,
            'totalCartasVigiaPend' => $totalCartasVigiaPend,
            'totalAgendamientos' => (new AgendamientoModel())->whereIn('estado', ['pendiente', 'confirmado'])->countAllResults(),
            'totalLavadoTanques'  => $totalLavadoTanques,
            'totalFumigacion'     => $totalFumigacion,
            'totalDesratizacion'  => $totalDesratizacion,
            'totalPlanillaSS'     => $totalPlanillaSS,
            'totalProveedores'    => $totalProveedores,
            'nombre'           => session()->get('nombre_usuario'),
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/dashboard', $data),
            'title'   => 'Inspecciones SST',
        ]);
    }

    /**
     * API: Clientes del consultor con contrato activo
     */
    public function getClientes()
    {
        $clientModel = new ClientModel();
        $role = session()->get('role');
        $userId = session()->get('user_id');

        $term = $this->request->getGet('term');
        $q = $clientModel->select('tbl_clientes.id_cliente, tbl_clientes.nombre_cliente, tbl_clientes.nit_cliente')
            ->where('tbl_clientes.estado', 'activo');
        if ($term) {
            $q->like('tbl_clientes.nombre_cliente', $term);
        }
        $clientes = $q->orderBy('tbl_clientes.nombre_cliente', 'ASC')->findAll();

        return $this->response->setJSON($clientes);
    }

    /**
     * API: Pendientes abiertos de un cliente
     */
    public function getPendientes(int $idCliente)
    {
        $model = new PendientesModel();
        $pendientes = $model->where('id_cliente', $idCliente)
            ->where('estado', 'ABIERTA')
            ->orderBy('fecha_asignacion', 'DESC')
            ->findAll();

        return $this->response->setJSON($pendientes);
    }

    /**
     * API: Mantenimientos por vencer de un cliente (próx. 30 días + vencidos)
     */
    public function getMantenimientos(int $idCliente)
    {
        $model = new VencimientosMantenimientoModel();
        $dateThreshold = date('Y-m-d', strtotime('+30 days'));

        $mantenimientos = $model->select('tbl_vencimientos_mantenimientos.*, tbl_mantenimientos.detalle_mantenimiento')
            ->join('tbl_mantenimientos', 'tbl_mantenimientos.id_mantenimiento = tbl_vencimientos_mantenimientos.id_mantenimiento', 'left')
            ->where('tbl_vencimientos_mantenimientos.id_cliente', $idCliente)
            ->where('tbl_vencimientos_mantenimientos.estado_actividad', 'sin ejecutar')
            ->where('tbl_vencimientos_mantenimientos.fecha_vencimiento <=', $dateThreshold)
            ->orderBy('tbl_vencimientos_mantenimientos.fecha_vencimiento', 'ASC')
            ->findAll();

        return $this->response->setJSON($mantenimientos);
    }
}
