<?php

namespace App\Controllers;

use App\Models\AgendamientoModel;
use App\Models\ConsultantModel;
use App\Models\ClientModel;

class AdminAgendamientoController extends BaseController
{
    protected AgendamientoModel $model;

    public function __construct()
    {
        $this->model = new AgendamientoModel();
    }

    /**
     * Panel de supervisión: resumen por consultor + lista detallada
     */
    public function index()
    {
        $role = session()->get('role');
        if ($role !== 'admin') {
            return redirect()->to('/inspecciones/agendamiento');
        }

        $resumen = $this->model->getResumenPorConsultor();

        // Lista detallada de todos los clientes activos con agendamiento
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $clientesActivos = $clientModel
            ->select('tbl_clientes.id_cliente, tbl_clientes.nombre_cliente, tbl_clientes.correo_cliente, tbl_clientes.id_consultor, tbl_clientes.estado')
            ->where('tbl_clientes.estado', 'activo')
            ->orderBy('tbl_clientes.nombre_cliente')
            ->findAll();

        $consultores = $consultantModel->findAll();
        $consultoresMap = [];
        foreach ($consultores as $c) {
            $consultoresMap[$c['id_consultor']] = $c;
        }

        $mesActual = date('Y-m');
        $db = \Config\Database::connect();

        foreach ($clientesActivos as &$cli) {
            // Nombre del consultor
            $cli['nombre_consultor'] = $consultoresMap[$cli['id_consultor']]['nombre_consultor'] ?? 'Sin asignar';

            // Última visita
            $ultima = $this->model->getUltimaVisita($cli['id_cliente']);
            $cli['ultima_visita'] = $ultima ? $ultima['fecha_visita'] : null;

            // Próximo agendamiento
            $proximo = $db->table('tbl_agendamientos')
                ->select('id, fecha_visita, hora_visita, frecuencia, estado, email_enviado')
                ->where('id_cliente', $cli['id_cliente'])
                ->whereIn('estado', ['pendiente', 'confirmado'])
                ->orderBy('fecha_visita', 'ASC')
                ->limit(1)
                ->get()
                ->getRowArray();

            $cli['proximo_agendamiento'] = $proximo;
        }

        $data = [
            'title'     => 'Supervisión de Agendamientos',
            'resumen'   => $resumen,
            'clientes'  => $clientesActivos,
            'consultoresMap' => $consultoresMap,
        ];

        return view('admin/agendamientos/index', $data);
    }

    /**
     * Drill-down: clientes de un consultor específico
     */
    public function porConsultor($idConsultor)
    {
        $role = session()->get('role');
        if ($role !== 'admin') {
            return redirect()->to('/inspecciones/agendamiento');
        }

        $consultantModel = new ConsultantModel();
        $consultor = $consultantModel->find($idConsultor);
        if (!$consultor) {
            return redirect()->to('/admin/agendamientos')->with('error', 'Consultor no encontrado');
        }

        $detalle = $this->model->getDetalleConsultor($idConsultor);

        // Calcular resumen
        $totalActivos = count($detalle);
        $agendados = 0;
        foreach ($detalle as $cli) {
            if (!empty($cli['proximo_agendamiento'])) $agendados++;
        }

        $data = [
            'title'        => 'Agendamientos - ' . $consultor['nombre_consultor'],
            'consultor'    => $consultor,
            'clientes'     => $detalle,
            'totalActivos' => $totalActivos,
            'agendados'    => $agendados,
            'sinAgendar'   => $totalActivos - $agendados,
            'pct'          => $totalActivos > 0 ? round(($agendados / $totalActivos) * 100) : 0,
        ];

        return view('admin/agendamientos/por_consultor', $data);
    }

    /**
     * API: Resumen JSON para cards dinámicas
     */
    public function apiResumen()
    {
        $role = session()->get('role');
        if ($role !== 'admin') {
            return $this->response->setJSON(['success' => false, 'error' => 'No autorizado']);
        }

        return $this->response->setJSON([
            'success' => true,
            'data'    => $this->model->getResumenPorConsultor(),
        ]);
    }
}
