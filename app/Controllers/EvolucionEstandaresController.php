<?php

namespace App\Controllers;

use App\Models\HistorialEstandaresModel;
use CodeIgniter\Controller;

class EvolucionEstandaresController extends Controller
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

        $model = new HistorialEstandaresModel();

        // Todos los usuarios ven todos los datos; el filtro por consultor lo hace el Select2 en JS
        $registros = $model
            ->select('historial_resumen_estandares.*, tbl_clientes.consultor_externo as cliente_consultor_externo, tbl_clientes.estado as cliente_estado_actual')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = historial_resumen_estandares.id_cliente', 'left')
            ->findAll();

        // Valores únicos para dropdowns iniciales
        $consultoresUnicos = array_values(array_unique(array_filter(array_column($registros, 'nombre_consultor'))));
        sort($consultoresUnicos);
        $clientesUnicos = array_values(array_unique(array_filter(array_column($registros, 'nombre_cliente'))));
        sort($clientesUnicos);
        $estandaresFrecUnicos = array_values(array_unique(array_filter(array_column($registros, 'estandares'))));
        sort($estandaresFrecUnicos);
        $consultoresExternosUnicos = array_values(array_unique(array_filter(array_column($registros, 'cliente_consultor_externo'))));
        sort($consultoresExternosUnicos);

        // Métricas globales
        $totalValor   = array_sum(array_map(fn($r) => floatval($r['total_valor'] ?? 0), $registros));
        $totalPuntaje = array_sum(array_map(fn($r) => floatval($r['total_puntaje'] ?? 0), $registros));
        $pctCumplimiento = $totalPuntaje > 0 ? round(($totalValor / $totalPuntaje) * 100, 1) : 0;

        // Año actual para defaults del período (Q9=B)
        $anioActual = (int) date('Y');

        $data = [
            'registros'                  => $registros,
            'consultoresUnicos'          => $consultoresUnicos,
            'clientesUnicos'             => $clientesUnicos,
            'estandaresFrecUnicos'       => $estandaresFrecUnicos,
            'consultoresExternosUnicos'  => $consultoresExternosUnicos,
            'role'                       => $role,
            'totalClientes'              => count($clientesUnicos),
            'pctCumplimiento'            => $pctCumplimiento,
            'anioActual'                 => $anioActual,
        ];

        return view('consultant/evolucion_estandares', $data);
    }
}
