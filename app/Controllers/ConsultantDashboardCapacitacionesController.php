<?php

namespace App\Controllers;

use App\Models\CronogcapacitacionModel;
use App\Models\ClientModel;
use CodeIgniter\Controller;

class ConsultantDashboardCapacitacionesController extends Controller
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

        $capacitacionModel = new CronogcapacitacionModel();
        $clientModel = new ClientModel();
        $db = \Config\Database::connect();

        // Pool de clientes activos con al menos una capacitación (para cascadeo bidireccional)
        $clientesCascade = $db->table('tbl_clientes cl')
            ->distinct()
            ->select('cl.id_cliente, cl.nombre_cliente, cl.id_consultor, c.nombre_consultor, cl.consultor_externo, cl.estandares')
            ->join('tbl_consultor c', 'c.id_consultor = cl.id_consultor', 'left')
            ->join('tbl_cronog_capacitacion cc', 'cc.id_cliente = cl.id_cliente')
            ->where('cl.estado', 'activo')
            ->orderBy('cl.nombre_cliente', 'ASC')
            ->get()->getResultArray();

        // Lista inicial para el Select2 de Cliente
        $clientes = $clientesCascade;

        // Obtener capacitaciones solo de clientes activos
        $capacitaciones = $capacitacionModel
            ->select('tbl_cronog_capacitacion.*, tbl_clientes.nombre_cliente, tbl_clientes.id_cliente, tbl_clientes.id_consultor, tbl_clientes.consultor_externo, tbl_clientes.estandares, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_cronog_capacitacion.id_cliente')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_clientes.id_consultor', 'left')
            ->where('tbl_clientes.estado', 'activo')
            ->findAll();

        // Consultores principales (derivados del pool)
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

        // Consultores externos distintos
        $consultoresExternosUnicos = [];
        foreach ($clientesCascade as $cl) {
            if (!empty($cl['consultor_externo'])) {
                $consultoresExternosUnicos[$cl['consultor_externo']] = ['consultor_externo' => $cl['consultor_externo']];
            }
        }
        ksort($consultoresExternosUnicos);
        $consultoresExternosUnicos = array_values($consultoresExternosUnicos);

        // Frecuencia de Visita (estandares) distintos
        $estandaresUnicos = [];
        foreach ($clientesCascade as $cl) {
            if (!empty($cl['estandares'])) {
                $estandaresUnicos[$cl['estandares']] = ['estandares' => $cl['estandares']];
            }
        }
        ksort($estandaresUnicos);
        $estandaresUnicos = array_values($estandaresUnicos);

        // Métricas globales
        $totalCapacitaciones = count($capacitaciones);
        $totalClientes = count(array_unique(array_filter(array_column($capacitaciones, 'id_cliente'), fn($v) => $v !== null)));

        $asistentesTotal = 0;
        $calificacionesTotal = 0;
        $countAsistentes = 0;
        $countCalificaciones = 0;

        foreach ($capacitaciones as $cap) {
            if (!empty($cap['numero_de_asistentes_a_capacitacion'])) {
                $asistentesTotal += intval($cap['numero_de_asistentes_a_capacitacion']);
                $countAsistentes++;
            }
            if (!empty($cap['promedio_de_calificaciones'])) {
                $calificacionesTotal += floatval($cap['promedio_de_calificaciones']);
                $countCalificaciones++;
            }
        }

        $promedioAsistentes = $countAsistentes > 0 ? round($asistentesTotal / $countAsistentes, 2) : 0;
        $promedioCalificaciones = $countCalificaciones > 0 ? round($calificacionesTotal / $countCalificaciones, 2) : 0;

        // Agrupar por estado
        $estadoCounts = [];
        foreach ($capacitaciones as $cap) {
            $estado = $cap['estado'] ?? 'SIN ESTADO';
            $estadoCounts[$estado] = ($estadoCounts[$estado] ?? 0) + 1;
        }

        // Agrupar por cliente (reemplaza gráfico de Tipo de Participantes)
        $clienteCounts = [];
        foreach ($capacitaciones as $cap) {
            $idC = $cap['id_cliente'] ?? null;
            if ($idC === null) continue;
            if (!isset($clienteCounts[$idC])) {
                $clienteCounts[$idC] = [
                    'id_cliente'    => $idC,
                    'nombre_cliente'=> $cap['nombre_cliente'] ?? 'SIN NOMBRE',
                    'count'         => 0,
                ];
            }
            $clienteCounts[$idC]['count']++;
        }
        usort($clienteCounts, fn($a, $b) => $b['count'] <=> $a['count']);

        // Estados únicos derivados de los datos
        $estadosUnicos = array_unique(array_filter(array_column($capacitaciones, 'estado')));
        sort($estadosUnicos);

        // Años disponibles: desde el mínimo derivado de los datos hasta el año actual
        $anioActual = (int) date('Y');
        $anioMinimo = $anioActual;
        foreach ($capacitaciones as $cap) {
            if (!empty($cap['fecha_programada'])) {
                $y = (int) substr($cap['fecha_programada'], 0, 4);
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
            'capacitaciones'             => $capacitaciones,
            'totalCapacitaciones'        => $totalCapacitaciones,
            'totalClientes'              => $totalClientes,
            'promedioAsistentes'         => $promedioAsistentes,
            'promedioCalificaciones'     => $promedioCalificaciones,
            'estadoCounts'               => $estadoCounts,
            'clienteCounts'              => $clienteCounts,
            'estadosUnicos'              => $estadosUnicos,
            'consultoresUnicos'          => array_values($consultoresUnicos),
            'consultoresExternosUnicos'  => $consultoresExternosUnicos,
            'estandaresUnicos'           => $estandaresUnicos,
            'aniosDisponibles'           => $aniosDisponibles,
            'anioActual'                 => $anioActual,
        ];

        return view('consultant/dashboard_capacitaciones', $data);
    }
}
