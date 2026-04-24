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

        // Pool de clientes activos con al menos una actividad PTA (para cascadeo bidireccional)
        $clientesCascade = $db->table('tbl_clientes cl')
            ->distinct()
            ->select('cl.id_cliente, cl.nombre_cliente, cl.id_consultor, c.nombre_consultor, cl.consultor_externo, cl.estandares')
            ->join('tbl_consultor c', 'c.id_consultor = cl.id_consultor', 'left')
            ->join('tbl_pta_cliente pta', 'pta.id_cliente = cl.id_cliente')
            ->where('cl.estado', 'activo')
            ->orderBy('cl.nombre_cliente', 'ASC')
            ->get()->getResultArray();

        // Lista inicial para el Select2 de Cliente
        $clientes = $clientesCascade;

        // Obtener TODAS las actividades con JOIN a clientes y consultor
        $actividades = $ptaModel
            ->select('tbl_pta_cliente.*, tbl_clientes.nombre_cliente, tbl_clientes.id_cliente, tbl_clientes.id_consultor, tbl_clientes.consultor_externo, tbl_clientes.estandares, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_pta_cliente.id_cliente')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_clientes.id_consultor', 'left')
            ->findAll();

        // Consultores principales (derivados del pool de clientes cascadeables)
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

        // Consultores externos distintos (del mismo pool)
        $consultoresExternosUnicos = [];
        foreach ($clientesCascade as $cl) {
            if (!empty($cl['consultor_externo'])) {
                $consultoresExternosUnicos[$cl['consultor_externo']] = ['consultor_externo' => $cl['consultor_externo']];
            }
        }
        ksort($consultoresExternosUnicos);
        $consultoresExternosUnicos = array_values($consultoresExternosUnicos);

        // Estándares distintos (del mismo pool)
        $estandaresUnicos = [];
        foreach ($clientesCascade as $cl) {
            if (!empty($cl['estandares'])) {
                $estandaresUnicos[$cl['estandares']] = ['estandares' => $cl['estandares']];
            }
        }
        ksort($estandaresUnicos);
        $estandaresUnicos = array_values($estandaresUnicos);

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

        // Agrupar por cliente (reemplaza gráfico de responsables)
        $clienteCounts = [];
        foreach ($actividades as $act) {
            $idC = $act['id_cliente'] ?? null;
            if ($idC === null) continue;
            if (!isset($clienteCounts[$idC])) {
                $clienteCounts[$idC] = [
                    'id_cliente'    => $idC,
                    'nombre_cliente'=> $act['nombre_cliente'] ?? 'SIN NOMBRE',
                    'count'         => 0,
                ];
            }
            $clienteCounts[$idC]['count']++;
        }
        usort($clienteCounts, fn($a, $b) => $b['count'] <=> $a['count']);

        // Estados del sistema (lista fija, no derivada)
        $estadosUnicos = ['ABIERTA', 'CERRADA', 'GESTIONANDO', 'CERRADA SIN EJECUCIÓN'];

        // PHVAs únicos
        $phvasUnicos = array_unique(array_filter(array_column($actividades, 'phva_plandetrabajo')));
        sort($phvasUnicos);

        // Años disponibles: desde el mínimo derivado de los datos hasta el año actual (descendente)
        $anioActual = (int) date('Y');
        $anioMinimo = $anioActual;
        foreach ($actividades as $act) {
            if (!empty($act['fecha_propuesta'])) {
                $y = (int) substr($act['fecha_propuesta'], 0, 4);
                if ($y > 0 && $y < $anioMinimo) {
                    $anioMinimo = $y;
                }
            }
        }
        $aniosDisponibles = [];
        for ($y = $anioActual; $y >= $anioMinimo; $y--) {
            $aniosDisponibles[] = $y;
        }

        $data = [
            'clientes'                   => $clientes,
            'clientesCascade'            => $clientesCascade,
            'actividades'                => $actividades,
            'totalActividades'           => $totalActividades,
            'estadoCounts'               => $estadoCounts,
            'phvaCounts'                 => $phvaCounts,
            'clienteCounts'              => $clienteCounts,
            'estadosUnicos'              => array_filter($estadosUnicos),
            'phvasUnicos'                => $phvasUnicos,
            'consultoresUnicos'          => array_values($consultoresUnicos),
            'consultoresExternosUnicos'  => $consultoresExternosUnicos,
            'estandaresUnicos'           => $estandaresUnicos,
            'aniosDisponibles'           => $aniosDisponibles,
            'anioActual'                 => $anioActual,
        ];

        return view('consultant/dashboard_plan_trabajo', $data);
    }
}
