<?php

namespace App\Controllers;

use App\Models\PtaTransicionesModel;
use App\Models\ClientModel;
use CodeIgniter\Controller;

class PtaTransicionesController extends Controller
{
    public function index()
    {
        $session = session();
        $rol = $session->get('role');
        if (!in_array($rol, ['admin', 'consultant'])) {
            return redirect()->to('/login')->with('error', 'No tiene permisos para acceder.');
        }

        $request = service('request');
        $idCliente  = $request->getGet('cliente');
        $estadoNuevo = $request->getGet('estado_nuevo');
        $fechaDesde = $request->getGet('fecha_desde');
        $fechaHasta = $request->getGet('fecha_hasta');

        $filters = [];
        if (!empty($idCliente))   $filters['id_cliente']   = $idCliente;
        if (!empty($estadoNuevo)) $filters['estado_nuevo']  = $estadoNuevo;
        if (!empty($fechaDesde))  $filters['fecha_desde']   = $fechaDesde;
        if (!empty($fechaHasta))  $filters['fecha_hasta']   = $fechaHasta;

        $transModel = new PtaTransicionesModel();
        $records = $transModel->getWithFilters($filters);
        $stats   = $transModel->getStatsByEstado($filters);

        $clientModel = new ClientModel();
        $clients = $clientModel->findAll();

        $data = [
            'clients' => $clients,
            'records' => $records,
            'stats'   => $stats,
            'filters' => [
                'cliente'     => $idCliente,
                'estado_nuevo' => $estadoNuevo,
                'fecha_desde' => $fechaDesde,
                'fecha_hasta' => $fechaHasta,
            ],
        ];

        return view('admin/pta_transiciones_list', $data);
    }

    /**
     * Exportar transiciones a Excel
     */
    public function export()
    {
        $session = session();
        $rol = $session->get('role');
        if (!in_array($rol, ['admin', 'consultant'])) {
            return redirect()->to('/login')->with('error', 'No tiene permisos.');
        }

        $request = service('request');
        $filters = array_filter([
            'id_cliente'   => $request->getGet('cliente'),
            'estado_nuevo' => $request->getGet('estado_nuevo'),
            'fecha_desde'  => $request->getGet('fecha_desde'),
            'fecha_hasta'  => $request->getGet('fecha_hasta'),
        ]);

        $transModel = new PtaTransicionesModel();
        $records = $transModel->getWithFilters($filters);

        // Mapear nombres de clientes
        $clientModel = new ClientModel();
        $clientsArr = [];
        foreach ($clientModel->findAll() as $c) {
            $clientsArr[$c['id_cliente']] = $c['nombre_cliente'];
        }

        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment;filename="transiciones_pta_' . date('Y-m-d_His') . '.xls"');
        header('Cache-Control: max-age=0');
        echo "\xEF\xBB\xBF";

        $output = fopen('php://output', 'w');
        fputcsv($output, [
            'ID', 'ID Actividad', 'Cliente', 'Actividad', 'Numeral',
            'Estado Anterior', 'Estado Nuevo', 'Usuario', 'Fecha Transición'
        ], "\t");

        foreach ($records as $row) {
            fputcsv($output, [
                $row['id_transicion'],
                $row['id_ptacliente'],
                $clientsArr[$row['id_cliente']] ?? 'N/A',
                $row['actividad_plandetrabajo'] ?? '-',
                $row['numeral_plandetrabajo'] ?? '-',
                $row['estado_anterior'],
                $row['estado_nuevo'],
                $row['nombre_usuario'] ?? 'Sistema',
                $row['fecha_transicion'],
            ], "\t");
        }

        fclose($output);
        exit;
    }
}
