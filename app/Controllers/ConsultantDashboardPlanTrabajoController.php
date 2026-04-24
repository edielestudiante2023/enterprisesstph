<?php

namespace App\Controllers;

use App\Models\PtaClienteNuevaModel;
use App\Models\ClientModel;
use CodeIgniter\Controller;

class ConsultantDashboardPlanTrabajoController extends Controller
{
    public function index()
    {
        $session = session();

        // Verificar que el usuario esté autenticado y tenga rol permitido
        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Debe iniciar sesión');
        }

        $role = $session->get('role');
        if (!in_array($role, ['admin', 'consultant'])) {
            return redirect()->to('/login')->with('error', 'No tiene permisos para acceder');
        }

        $ptaModel = new PtaClienteNuevaModel();
        $clientModel = new ClientModel();
        $db = \Config\Database::connect();

        // Obtener solo clientes activos (para el Select2 de filtro)
        $clientes = $clientModel
            ->where('estado', 'activo')
            ->orderBy('nombre_cliente', 'ASC')
            ->findAll();

        // Obtener TODAS las actividades con JOIN a clientes y consultor
        $actividades = $ptaModel
            ->select('tbl_pta_cliente.*, tbl_clientes.nombre_cliente, tbl_clientes.id_cliente, tbl_clientes.id_consultor, tbl_clientes.consultor_externo, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_pta_cliente.id_cliente')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_clientes.id_consultor', 'left')
            ->findAll();

        // Consultores principales que tengan clientes activos con al menos una actividad PTA
        $consultoresUnicos = $db->table('tbl_consultor')
            ->distinct()
            ->select('tbl_consultor.id_consultor, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', "tbl_clientes.id_consultor = tbl_consultor.id_consultor AND tbl_clientes.estado = 'activo'")
            ->join('tbl_pta_cliente', 'tbl_pta_cliente.id_cliente = tbl_clientes.id_cliente')
            ->where('tbl_consultor.nombre_consultor IS NOT NULL')
            ->where('tbl_consultor.nombre_consultor !=', '')
            ->orderBy('tbl_consultor.nombre_consultor', 'ASC')
            ->get()->getResultArray();

        // Consultores externos distintos (de clientes activos con al menos una actividad PTA)
        $consultoresExternosUnicos = $db->table('tbl_clientes')
            ->distinct()
            ->select('tbl_clientes.consultor_externo')
            ->join('tbl_pta_cliente', 'tbl_pta_cliente.id_cliente = tbl_clientes.id_cliente')
            ->where('tbl_clientes.estado', 'activo')
            ->where('tbl_clientes.consultor_externo IS NOT NULL')
            ->where('tbl_clientes.consultor_externo !=', '')
            ->orderBy('tbl_clientes.consultor_externo', 'ASC')
            ->get()->getResultArray();

        // Métricas globales
        $totalActividades = count($actividades);

        // Agrupar por estado
        $estadoCounts = [];
        foreach ($actividades as $act) {
            $estado = $act['estado_actividad'] ?? 'SIN ESTADO';
            $estadoCounts[$estado] = ($estadoCounts[$estado] ?? 0) + 1;
        }

        // Agrupar por PHVA
        $phvaCounts = [];
        foreach ($actividades as $act) {
            $phva = $act['phva_plandetrabajo'] ?? 'SIN PHVA';
            $phvaCounts[$phva] = ($phvaCounts[$phva] ?? 0) + 1;
        }

        // Agrupar por responsable (usar definido o sugerido como fallback)
        $responsableCounts = [];
        foreach ($actividades as $act) {
            $resp = $act['responsable_definido_paralaactividad'] ?? $act['responsable_sugerido_plandetrabajo'] ?? 'SIN ASIGNAR';
            $responsableCounts[$resp] = ($responsableCounts[$resp] ?? 0) + 1;
        }

        // Obtener valores únicos para los selectores
        // Incluir todos los estados posibles del sistema, no solo los que existen en los datos
        $estadosUnicos = ['ABIERTA', 'CERRADA', 'GESTIONANDO', 'CERRADA SIN EJECUCIÓN'];

        // Para responsables, usar responsable_definido o sugerido como fallback
        $responsablesUnicos = [];
        foreach ($actividades as $act) {
            $responsable = $act['responsable_definido_paralaactividad'] ?? $act['responsable_sugerido_plandetrabajo'] ?? null;
            if ($responsable) {
                $responsablesUnicos[] = $responsable;
            }
        }
        $responsablesUnicos = array_unique($responsablesUnicos);

        $phvasUnicos = array_unique(array_column($actividades, 'phva_plandetrabajo'));

        // Obtener fechas únicas para el selector
        $fechasPropuestaUnicas = [];
        foreach ($actividades as $act) {
            if (!empty($act['fecha_propuesta'])) {
                $fecha = date('Y-m', strtotime($act['fecha_propuesta']));
                $fechasPropuestaUnicas[$fecha] = date('F Y', strtotime($act['fecha_propuesta']));
            }
        }

        $data = [
            'clientes' => $clientes,
            'actividades' => $actividades,
            'totalActividades' => $totalActividades,
            'estadoCounts' => $estadoCounts,
            'phvaCounts' => $phvaCounts,
            'responsableCounts' => $responsableCounts,
            'estadosUnicos' => array_filter($estadosUnicos),
            'responsablesUnicos' => array_filter($responsablesUnicos),
            'phvasUnicos' => array_filter($phvasUnicos),
            'fechasPropuestaUnicas' => $fechasPropuestaUnicas,
            'consultoresUnicos' => $consultoresUnicos,
            'consultoresExternosUnicos' => $consultoresExternosUnicos
        ];

        return view('consultant/dashboard_plan_trabajo', $data);
    }
}
