<?php

namespace App\Controllers;

use App\Models\HistorialPlanTrabajoModel;
use App\Models\ConsultantModel;
use CodeIgniter\Controller;

class EvolucionPlanTrabajoController extends Controller
{
    public function index()
    {
        $session = session();

        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Debe iniciar sesion');
        }

        $role = $session->get('role');
        if (!in_array($role, ['admin', 'consultant'])) {
            return redirect()->to('/login')->with('error', 'No tiene permisos para acceder');
        }

        $model = new HistorialPlanTrabajoModel();

        // Si es consultor, filtrar solo sus clientes
        $nombreConsultorFiltro = null;
        if ($role === 'consultant') {
            $consultantModel = new ConsultantModel();
            $consultor = $consultantModel->find($session->get('user_id'));
            $nombreConsultorFiltro = $consultor['nombre_consultor'] ?? null;
        }

        if ($nombreConsultorFiltro) {
            $registros = $model->where('nombre_consultor', $nombreConsultorFiltro)->findAll();
        } else {
            $registros = $model->findAll();
        }

        // Valores únicos para filtros
        $consultoresUnicos = array_values(array_unique(array_filter(array_column($registros, 'nombre_consultor'))));
        $clientesUnicos = array_values(array_unique(array_filter(array_column($registros, 'nombre_cliente'))));
        $estandaresUnicos = array_values(array_unique(array_filter(array_column($registros, 'estandares'))));

        // Fechas únicas (formato Y-m)
        $fechasRaw = array_column($registros, 'fecha_extraccion');
        $fechasMes = array_values(array_unique(array_map(function ($f) {
            return substr($f, 0, 7);
        }, $fechasRaw)));
        sort($fechasMes);

        // Contar actividades totales
        $totalActividades = array_sum(array_column($registros, 'total_actividades'));

        $data = [
            'registros'          => $registros,
            'consultoresUnicos'  => $consultoresUnicos,
            'clientesUnicos'     => $clientesUnicos,
            'estandaresUnicos'   => $estandaresUnicos,
            'fechasMes'          => $fechasMes,
            'role'               => $role,
            'totalClientes'      => count($clientesUnicos),
            'totalActividades'   => $totalActividades,
        ];

        return view('consultant/evolucion_plan_trabajo', $data);
    }
}
