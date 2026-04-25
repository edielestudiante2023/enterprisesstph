<?php

namespace App\Controllers;

use App\Models\EvaluationModel;
use App\Models\ClientModel;
use CodeIgniter\Controller;

class ConsultantDashboardEstandaresController extends Controller
{
    public function index()
    {
        $session = session();

        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Debe iniciar sesión');
        }

        $role = $session->get('role');
        if (!in_array($role, ['admin', 'consultant'])) {
            return redirect()->to('/login')->with('error', 'No tiene permisos para acceder');
        }

        $evaluationModel = new EvaluationModel();
        $clientModel = new ClientModel();
        $db = \Config\Database::connect();

        // Pool de clientes activos con al menos una evaluación (para cascadeo)
        $clientesCascade = $db->table('tbl_clientes cl')
            ->distinct()
            ->select('cl.id_cliente, cl.nombre_cliente, cl.id_consultor, c.nombre_consultor, cl.consultor_externo, cl.estandares')
            ->join('tbl_consultor c', 'c.id_consultor = cl.id_consultor', 'left')
            ->join('evaluacion_inicial_sst e', 'e.id_cliente = cl.id_cliente')
            ->where('cl.estado', 'activo')
            ->orderBy('cl.nombre_cliente', 'ASC')
            ->get()->getResultArray();

        $clientes = $clientesCascade;

        // Evaluaciones solo de clientes activos (Q3=A: sin toggle "Incluir retirados")
        $evaluaciones = $evaluationModel
            ->select('evaluacion_inicial_sst.*, tbl_clientes.nombre_cliente, tbl_clientes.id_cliente, tbl_clientes.id_consultor, tbl_clientes.consultor_externo, tbl_clientes.estandares, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = evaluacion_inicial_sst.id_cliente')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_clientes.id_consultor', 'left')
            ->where('tbl_clientes.estado', 'activo')
            ->findAll();

        // Consultores principales
        $consultoresUnicos = [];
        foreach ($clientesCascade as $cl) {
            if (!empty($cl['id_consultor']) && !empty($cl['nombre_consultor'])) {
                $consultoresUnicos[$cl['id_consultor']] = [
                    'id_consultor'    => $cl['id_consultor'],
                    'nombre_consultor'=> $cl['nombre_consultor'],
                ];
            }
        }
        usort($consultoresUnicos, fn($a, $b) => strcmp($a['nombre_consultor'], $b['nombre_consultor']));

        // Consultores externos
        $consultoresExternosUnicos = [];
        foreach ($clientesCascade as $cl) {
            if (!empty($cl['consultor_externo'])) {
                $consultoresExternosUnicos[$cl['consultor_externo']] = ['consultor_externo' => $cl['consultor_externo']];
            }
        }
        ksort($consultoresExternosUnicos);
        $consultoresExternosUnicos = array_values($consultoresExternosUnicos);

        // Frecuencia de Visita
        $estandaresFrecUnicos = [];
        foreach ($clientesCascade as $cl) {
            if (!empty($cl['estandares'])) {
                $estandaresFrecUnicos[$cl['estandares']] = ['estandares' => $cl['estandares']];
            }
        }
        ksort($estandaresFrecUnicos);
        $estandaresFrecUnicos = array_values($estandaresFrecUnicos);

        // Métricas globales
        $totalCalificado = 0;
        $totalPosible = 0;
        foreach ($evaluaciones as $ev) {
            $totalCalificado += floatval($ev['valor'] ?? 0);
            $totalPosible += floatval($ev['puntaje_cuantitativo'] ?? 0);
        }
        $porcentajeCumplimiento = $totalPosible > 0 ? round(($totalCalificado / $totalPosible) * 100, 1) : 0;
        $totalItems = count($evaluaciones);
        $totalClientes = count(array_unique(array_filter(array_column($evaluaciones, 'id_cliente'), fn($v) => $v !== null)));

        // Agrupar por ciclo PHVA
        $phvaCounts = [];
        foreach ($evaluaciones as $ev) {
            $ciclo = $ev['ciclo'] ?? '';
            if (!empty($ciclo)) {
                $phvaCounts[$ciclo] = ($phvaCounts[$ciclo] ?? 0) + 1;
            }
        }

        // Agrupar por calificación (incluir vacíos como "SIN EVALUAR")
        $calificacionCounts = [];
        foreach ($evaluaciones as $ev) {
            $calif = $ev['evaluacion_inicial'] ?? '';
            if (empty($calif)) $calif = 'SIN EVALUAR';
            $calificacionCounts[$calif] = ($calificacionCounts[$calif] ?? 0) + 1;
        }

        // Agrupar por dimensión (estandar) sumando valor
        $dimensionCounts = [];
        foreach ($evaluaciones as $ev) {
            $dim = $ev['estandar'] ?? '';
            if (!empty($dim)) {
                $dimensionCounts[$dim] = ($dimensionCounts[$dim] ?? 0) + floatval($ev['valor'] ?? 0);
            }
        }

        // Únicos para selects (incluir SIN EVALUAR para calificacion)
        $dimensionesUnicas = array_unique(array_filter(array_column($evaluaciones, 'estandar')));
        sort($dimensionesUnicas);

        $calificacionesUnicas = [];
        foreach ($evaluaciones as $ev) {
            $calif = $ev['evaluacion_inicial'] ?? '';
            $calificacionesUnicas[] = empty($calif) ? 'SIN EVALUAR' : $calif;
        }
        $calificacionesUnicas = array_unique($calificacionesUnicas);
        sort($calificacionesUnicas);

        $ciclosUnicos = array_unique(array_filter(array_column($evaluaciones, 'ciclo')));
        sort($ciclosUnicos);

        $data = [
            'clientes'                   => $clientes,
            'clientesCascade'            => $clientesCascade,
            'evaluaciones'               => $evaluaciones,
            'totalCalificado'            => round($totalCalificado, 2),
            'totalPosible'               => round($totalPosible, 2),
            'porcentajeCumplimiento'     => $porcentajeCumplimiento,
            'totalItems'                 => $totalItems,
            'totalClientes'              => $totalClientes,
            'phvaCounts'                 => $phvaCounts,
            'calificacionCounts'         => $calificacionCounts,
            'dimensionCounts'            => $dimensionCounts,
            'dimensionesUnicas'          => $dimensionesUnicas,
            'calificacionesUnicas'       => $calificacionesUnicas,
            'ciclosUnicos'               => $ciclosUnicos,
            'consultoresUnicos'          => array_values($consultoresUnicos),
            'consultoresExternosUnicos'  => $consultoresExternosUnicos,
            'estandaresFrecUnicos'       => $estandaresFrecUnicos,
        ];

        return view('consultant/dashboard_estandares_minimos', $data);
    }
}
