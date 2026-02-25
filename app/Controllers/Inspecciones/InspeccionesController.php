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

class InspeccionesController extends BaseController
{
    /**
     * Dashboard principal de inspecciones (PWA)
     */
    public function dashboard()
    {
        $role = session()->get('role');
        $userId = session()->get('user_id');

        $actaModel = new ActaVisitaModel();

        // Documentos pendientes del consultor (o todos si es admin)
        if ($role === 'admin') {
            $pendientes = $actaModel->getAllPendientes();
        } else {
            $pendientes = $actaModel->getPendientesByConsultor($userId);
        }

        // Conteo de actas completas
        $totalActas = $actaModel->where('id_consultor', $userId)
            ->where('estado', 'completo')
            ->countAllResults();

        // Conteo de locativas completas
        $locativaModel = new InspeccionLocativaModel();
        $totalLocativas = $locativaModel->where('id_consultor', $userId)
            ->where('estado', 'completo')
            ->countAllResults();

        // Pendientes de locativas (borradores)
        if ($role === 'admin') {
            $pendientesLocativas = $locativaModel->getAllPendientes();
        } else {
            $pendientesLocativas = $locativaModel->getPendientesByConsultor($userId);
        }

        // Conteo de señalización completas
        $senalizacionModel = new InspeccionSenalizacionModel();
        $totalSenalizacion = $senalizacionModel->where('id_consultor', $userId)
            ->where('estado', 'completo')
            ->countAllResults();

        // Pendientes de señalización (borradores)
        if ($role === 'admin') {
            $pendientesSenalizacion = $senalizacionModel
                ->select('tbl_inspeccion_senalizacion.*, tbl_clientes.nombre_cliente')
                ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_senalizacion.id_cliente', 'left')
                ->where('tbl_inspeccion_senalizacion.estado', 'borrador')
                ->orderBy('tbl_inspeccion_senalizacion.updated_at', 'DESC')
                ->findAll();
        } else {
            $pendientesSenalizacion = $senalizacionModel->getPendientesByConsultor($userId);
        }

        // Conteo de extintores completas
        $extintoresModel = new InspeccionExtintoresModel();
        $totalExtintores = $extintoresModel->where('id_consultor', $userId)
            ->where('estado', 'completo')
            ->countAllResults();

        // Pendientes de extintores (borradores)
        if ($role === 'admin') {
            $pendientesExtintores = $extintoresModel
                ->select('tbl_inspeccion_extintores.*, tbl_clientes.nombre_cliente')
                ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_extintores.id_cliente', 'left')
                ->where('tbl_inspeccion_extintores.estado', 'borrador')
                ->orderBy('tbl_inspeccion_extintores.updated_at', 'DESC')
                ->findAll();
        } else {
            $pendientesExtintores = $extintoresModel->getPendientesByConsultor($userId);
        }

        // Conteo de botiquín completas
        $botiquinModel = new InspeccionBotiquinModel();
        $totalBotiquin = $botiquinModel->where('id_consultor', $userId)
            ->where('estado', 'completo')
            ->countAllResults();

        // Pendientes de botiquín (borradores)
        if ($role === 'admin') {
            $pendientesBotiquin = $botiquinModel
                ->select('tbl_inspeccion_botiquin.*, tbl_clientes.nombre_cliente')
                ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_botiquin.id_cliente', 'left')
                ->where('tbl_inspeccion_botiquin.estado', 'borrador')
                ->orderBy('tbl_inspeccion_botiquin.updated_at', 'DESC')
                ->findAll();
        } else {
            $pendientesBotiquin = $botiquinModel->getPendientesByConsultor($userId);
        }

        // Conteo de gabinetes completas
        $gabineteModel = new InspeccionGabineteModel();
        $totalGabinetes = $gabineteModel->where('id_consultor', $userId)
            ->where('estado', 'completo')
            ->countAllResults();

        // Pendientes de gabinetes (borradores)
        if ($role === 'admin') {
            $pendientesGabinetes = $gabineteModel->getAllPendientes();
        } else {
            $pendientesGabinetes = $gabineteModel->getPendientesByConsultor($userId);
        }

        // Conteo de comunicaciones completas
        $comunicacionModel = new InspeccionComunicacionModel();
        $totalComunicaciones = $comunicacionModel->where('id_consultor', $userId)
            ->where('estado', 'completo')
            ->countAllResults();

        // Pendientes de comunicaciones (borradores)
        if ($role === 'admin') {
            $pendientesComunicaciones = $comunicacionModel->getAllPendientes();
        } else {
            $pendientesComunicaciones = $comunicacionModel->getPendientesByConsultor($userId);
        }

        // Conteo de recursos seguridad completas
        $recursosSeguridadModel = new InspeccionRecursosSeguridadModel();
        $totalRecursosSeg = $recursosSeguridadModel->where('id_consultor', $userId)
            ->where('estado', 'completo')
            ->countAllResults();

        // Pendientes de recursos seguridad (borradores)
        if ($role === 'admin') {
            $pendientesRecursosSeg = $recursosSeguridadModel->getAllPendientes();
        } else {
            $pendientesRecursosSeg = $recursosSeguridadModel->getPendientesByConsultor($userId);
        }

        // Conteo de probabilidad peligros completas
        $probPeligrosModel = new ProbabilidadPeligrosModel();
        $totalProbPeligros = $probPeligrosModel->where('id_consultor', $userId)
            ->where('estado', 'completo')
            ->countAllResults();

        // Pendientes de probabilidad peligros (borradores)
        if ($role === 'admin') {
            $pendientesProbPeligros = $probPeligrosModel->getAllPendientes();
        } else {
            $pendientesProbPeligros = $probPeligrosModel->getPendientesByConsultor($userId);
        }

        // Conteo de matriz vulnerabilidad completas
        $matrizVulModel = new MatrizVulnerabilidadModel();
        $totalMatrizVul = $matrizVulModel->where('id_consultor', $userId)
            ->where('estado', 'completo')
            ->countAllResults();

        // Pendientes de matriz vulnerabilidad (borradores)
        if ($role === 'admin') {
            $pendientesMatrizVul = $matrizVulModel->getAllPendientes();
        } else {
            $pendientesMatrizVul = $matrizVulModel->getPendientesByConsultor($userId);
        }

        // Conteo de plan emergencia completas
        $planEmgModel = new PlanEmergenciaModel();
        $totalPlanEmergencia = $planEmgModel->where('id_consultor', $userId)
            ->where('estado', 'completo')
            ->countAllResults();

        // Pendientes de plan emergencia (borradores)
        if ($role === 'admin') {
            $pendientesPlanEmg = $planEmgModel->getAllPendientes();
        } else {
            $pendientesPlanEmg = $planEmgModel->getPendientesByConsultor($userId);
        }

        // Conteo de evaluaciones simulacro completas (derivado via tbl_clientes)
        $evalSimModel = new EvaluacionSimulacroModel();
        if ($role === 'admin') {
            $totalSimulacro = $evalSimModel->where('estado', 'completo')->countAllResults();
            $pendientesSimulacro = $evalSimModel
                ->select('tbl_evaluacion_simulacro.*, tbl_clientes.nombre_cliente')
                ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_evaluacion_simulacro.id_cliente', 'left')
                ->where('tbl_evaluacion_simulacro.estado', 'borrador')
                ->orderBy('tbl_evaluacion_simulacro.updated_at', 'DESC')
                ->findAll();
        } else {
            $totalSimulacro = $evalSimModel
                ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_evaluacion_simulacro.id_cliente')
                ->where('tbl_clientes.id_consultor', $userId)
                ->where('tbl_evaluacion_simulacro.estado', 'completo')
                ->countAllResults();
            $pendientesSimulacro = $evalSimModel->getPendientesByConsultor($userId);
        }

        // Conteo de HV brigadista completas (derivado via tbl_clientes)
        $hvBrigModel = new HvBrigadistaModel();
        if ($role === 'admin') {
            $totalHvBrigadista = $hvBrigModel->where('estado', 'completo')->countAllResults();
            $pendientesHvBrig = $hvBrigModel
                ->select('tbl_hv_brigadista.*, tbl_clientes.nombre_cliente')
                ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_hv_brigadista.id_cliente', 'left')
                ->where('tbl_hv_brigadista.estado', 'borrador')
                ->orderBy('tbl_hv_brigadista.updated_at', 'DESC')
                ->findAll();
        } else {
            $totalHvBrigadista = $hvBrigModel
                ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_hv_brigadista.id_cliente')
                ->where('tbl_clientes.id_consultor', $userId)
                ->where('tbl_hv_brigadista.estado', 'completo')
                ->countAllResults();
            $pendientesHvBrig = $hvBrigModel->getPendientesByConsultor($userId);
        }

        // Conteo de vencimientos de mantenimiento sin ejecutar
        $vencimientoModel = new VencimientosMantenimientoModel();
        $vencBuilder = $vencimientoModel->where('estado_actividad', 'sin ejecutar');
        if ($role !== 'admin') {
            $vencBuilder->where('id_consultor', $userId);
        }
        $totalVencimientos = $vencBuilder->countAllResults();

        // Conteo de pendientes (compromisos) abiertos
        $pendientesCountModel = new PendientesModel();
        $pendCountBuilder = $pendientesCountModel->where('tbl_pendientes.estado', 'ABIERTA');
        if ($role !== 'admin') {
            $pendCountBuilder->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_pendientes.id_cliente')
                ->where('tbl_clientes.id_consultor', $userId);
        }
        $totalPendientesAbiertos = $pendCountBuilder->countAllResults();

        // Conteo de cartas vigía pendientes de firma
        $cartaVigiaModel = new CartaVigiaModel();
        $cartaBuilder = $cartaVigiaModel->where('estado_firma', 'pendiente_firma');
        if ($role !== 'admin') {
            $cartaBuilder->where('id_consultor', $userId);
        }
        $totalCartasVigiaPend = $cartaBuilder->countAllResults();

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
            'totalVencimientos' => $totalVencimientos,
            'totalPendientesAbiertos' => $totalPendientesAbiertos,
            'totalCartasVigiaPend' => $totalCartasVigiaPend,
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

        $builder = $clientModel->select('tbl_clientes.id_cliente, tbl_clientes.nombre_cliente, tbl_clientes.nit_cliente')
            ->join('tbl_contratos', "tbl_contratos.id_cliente = tbl_clientes.id_cliente AND tbl_contratos.estado = 'activo'");

        if ($role === 'consultant') {
            $builder->where('tbl_clientes.id_consultor', $userId);
        }

        $clientes = $builder->orderBy('tbl_clientes.nombre_cliente', 'ASC')->findAll();

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
