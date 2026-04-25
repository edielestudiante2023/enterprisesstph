<?php

namespace App\Controllers;

use App\Models\PendientesModel;
use App\Models\ClientModel;
use CodeIgniter\Controller;

class ConsultantDashboardPendientesController extends Controller
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

        $pendientesModel = new PendientesModel();
        $clientModel = new ClientModel();
        $db = \Config\Database::connect();

        // Pool de clientes activos con al menos un pendiente (para cascadeo bidireccional)
        $clientesCascade = $db->table('tbl_clientes cl')
            ->distinct()
            ->select('cl.id_cliente, cl.nombre_cliente, cl.id_consultor, c.nombre_consultor, cl.consultor_externo, cl.estandares')
            ->join('tbl_consultor c', 'c.id_consultor = cl.id_consultor', 'left')
            ->join('tbl_pendientes p', 'p.id_cliente = cl.id_cliente')
            ->where('cl.estado', 'activo')
            ->orderBy('cl.nombre_cliente', 'ASC')
            ->get()->getResultArray();

        $clientes = $clientesCascade;

        // Pendientes solo de clientes activos
        $pendientes = $pendientesModel
            ->select('tbl_pendientes.*, tbl_clientes.nombre_cliente, tbl_clientes.id_cliente, tbl_clientes.id_consultor, tbl_clientes.consultor_externo, tbl_clientes.estandares, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_pendientes.id_cliente')
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
        $estandaresUnicos = [];
        foreach ($clientesCascade as $cl) {
            if (!empty($cl['estandares'])) {
                $estandaresUnicos[$cl['estandares']] = ['estandares' => $cl['estandares']];
            }
        }
        ksort($estandaresUnicos);
        $estandaresUnicos = array_values($estandaresUnicos);

        // Métricas globales
        $totalPendientes = count($pendientes);
        $totalClientes = count(array_unique(array_filter(array_column($pendientes, 'id_cliente'), fn($v) => $v !== null)));

        $diasTotal = 0;
        $countDias = 0;
        foreach ($pendientes as $pend) {
            if (!empty($pend['conteo_dias'])) {
                $diasTotal += intval($pend['conteo_dias']);
                $countDias++;
            }
        }
        $promedioDias = $countDias > 0 ? round($diasTotal / $countDias, 2) : 0;

        // Agrupar por estado
        $estadoCounts = [];
        foreach ($pendientes as $pend) {
            $estado = $pend['estado'] ?? 'SIN ESTADO';
            $estadoCounts[$estado] = ($estadoCounts[$estado] ?? 0) + 1;
        }

        // Agrupar por responsable
        $responsableCounts = [];
        foreach ($pendientes as $pend) {
            $resp = $pend['responsable'] ?? 'SIN ASIGNAR';
            $responsableCounts[$resp] = ($responsableCounts[$resp] ?? 0) + 1;
        }

        // Agrupar por cliente (gráfico nuevo "Pendientes por Cliente")
        $clienteCounts = [];
        foreach ($pendientes as $pend) {
            $idC = $pend['id_cliente'] ?? null;
            if ($idC === null) continue;
            if (!isset($clienteCounts[$idC])) {
                $clienteCounts[$idC] = [
                    'id_cliente'    => $idC,
                    'nombre_cliente'=> $pend['nombre_cliente'] ?? 'SIN NOMBRE',
                    'count'         => 0,
                ];
            }
            $clienteCounts[$idC]['count']++;
        }
        usort($clienteCounts, fn($a, $b) => $b['count'] <=> $a['count']);

        // Estados/Responsables únicos
        $estadosUnicos = array_unique(array_filter(array_column($pendientes, 'estado')));
        sort($estadosUnicos);
        $responsablesUnicos = array_unique(array_filter(array_column($pendientes, 'responsable')));
        sort($responsablesUnicos);

        // Meses de cierre (fecha_plazo) — filtro secundario independiente
        $mesesCierreUnicos = [];
        foreach ($pendientes as $pend) {
            if (!empty($pend['fecha_plazo']) && substr($pend['fecha_plazo'], 0, 7) > '0000-01') {
                $fecha = substr($pend['fecha_plazo'], 0, 7);
                $mesesCierreUnicos[$fecha] = date('F Y', strtotime($pend['fecha_plazo']));
            }
        }
        ksort($mesesCierreUnicos);

        // Años disponibles (sobre fecha_asignacion según Q6=A)
        $anioActual = (int) date('Y');
        $anioMinimo = $anioActual;
        foreach ($pendientes as $pend) {
            if (!empty($pend['fecha_asignacion'])) {
                $y = (int) substr($pend['fecha_asignacion'], 0, 4);
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
            'pendientes'                 => $pendientes,
            'totalPendientes'            => $totalPendientes,
            'totalClientes'              => $totalClientes,
            'promedioDias'               => $promedioDias,
            'estadoCounts'               => $estadoCounts,
            'responsableCounts'          => $responsableCounts,
            'clienteCounts'              => $clienteCounts,
            'estadosUnicos'              => $estadosUnicos,
            'responsablesUnicos'         => $responsablesUnicos,
            'consultoresUnicos'          => array_values($consultoresUnicos),
            'consultoresExternosUnicos'  => $consultoresExternosUnicos,
            'estandaresUnicos'           => $estandaresUnicos,
            'mesesCierreUnicos'          => $mesesCierreUnicos,
            'aniosDisponibles'           => $aniosDisponibles,
            'anioActual'                 => $anioActual,
        ];

        return view('consultant/dashboard_pendientes', $data);
    }
}
