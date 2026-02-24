<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\PendientesModel;
use App\Models\ClientModel;

class PendientesPwaController extends BaseController
{
    protected $pendientesModel;
    protected $clientModel;

    public function __construct()
    {
        $this->pendientesModel = new PendientesModel();
        $this->clientModel = new ClientModel();
    }

    /**
     * Listado de pendientes por cliente
     */
    public function list($idCliente = null)
    {
        $userId = session()->get('user_id');
        $role = session()->get('role');

        $pendientes = [];
        $clienteSeleccionado = null;

        if ($idCliente) {
            $clienteSeleccionado = $this->clientModel->find($idCliente);

            $builder = $this->pendientesModel
                ->select('tbl_pendientes.*, tbl_clientes.nombre_cliente')
                ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_pendientes.id_cliente', 'left')
                ->where('tbl_pendientes.id_cliente', $idCliente);

            if ($role !== 'admin') {
                // Consultores ven pendientes de sus clientes
                $builder->join('tbl_contratos', "tbl_contratos.id_cliente = tbl_pendientes.id_cliente AND tbl_contratos.estado = 'activo'", 'inner')
                    ->where('tbl_clientes.id_consultor', $userId);
            }

            $pendientes = $builder->orderBy('tbl_pendientes.fecha_asignacion', 'DESC')
                ->findAll();
        }

        // Conteos por estado
        $conteos = ['ABIERTA' => 0, 'CERRADA' => 0, 'SIN RESPUESTA DEL CLIENTE' => 0];
        $hoy = date('Y-m-d');

        foreach ($pendientes as &$p) {
            $estado = $p['estado'];
            $conteos[$estado] = ($conteos[$estado] ?? 0) + 1;

            // Calcular días transcurridos
            if ($estado === 'ABIERTA' && !empty($p['fecha_asignacion'])) {
                $diff = (strtotime($hoy) - strtotime($p['fecha_asignacion'])) / 86400;
                $p['dias'] = (int) round($diff);
            } else {
                $p['dias'] = $p['conteo_dias'] ?? 0;
            }
        }
        unset($p);

        $data = [
            'title'               => 'Pendientes',
            'idCliente'           => $idCliente,
            'clienteSeleccionado' => $clienteSeleccionado,
            'pendientes'          => $pendientes,
            'conteos'             => $conteos,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/pendientes/list', $data),
            'title'   => 'Pendientes',
        ]);
    }

    /**
     * Formulario crear nuevo pendiente
     */
    public function create($idCliente = null)
    {
        $data = [
            'title'     => 'Nuevo Pendiente',
            'idCliente' => $idCliente,
            'cliente'   => $idCliente ? $this->clientModel->find($idCliente) : null,
            'edit'      => false,
            'pendiente' => null,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/pendientes/form', $data),
            'title'   => 'Nuevo Pendiente',
        ]);
    }

    /**
     * Guardar nuevo pendiente
     */
    public function store()
    {
        $idCliente = $this->request->getPost('id_cliente');
        $tarea = $this->request->getPost('tarea_actividad');
        $responsable = $this->request->getPost('responsable');
        $fechaCierre = $this->request->getPost('fecha_cierre');

        if (!$idCliente || !$tarea) {
            session()->setFlashdata('error', 'Faltan campos obligatorios');
            return redirect()->back();
        }

        $this->pendientesModel->insert([
            'id_cliente'        => $idCliente,
            'tarea_actividad'   => $tarea,
            'responsable'       => $responsable,
            'fecha_asignacion'  => date('Y-m-d'),
            'fecha_cierre'      => $fechaCierre ?: null,
            'estado'            => 'ABIERTA',
            'estado_avance'     => '',
        ]);

        session()->setFlashdata('msg', 'Pendiente creado');
        return redirect()->to('/inspecciones/pendientes/cliente/' . $idCliente);
    }

    /**
     * Formulario editar pendiente
     */
    public function edit($id)
    {
        $pendiente = $this->pendientesModel->find($id);

        if (!$pendiente) {
            session()->setFlashdata('error', 'Pendiente no encontrado');
            return redirect()->to('/inspecciones/pendientes');
        }

        $data = [
            'title'     => 'Editar Pendiente',
            'idCliente' => $pendiente['id_cliente'],
            'cliente'   => $this->clientModel->find($pendiente['id_cliente']),
            'edit'      => true,
            'pendiente' => $pendiente,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/pendientes/form', $data),
            'title'   => 'Editar Pendiente',
        ]);
    }

    /**
     * Guardar cambios de pendiente
     */
    public function update($id)
    {
        $pendiente = $this->pendientesModel->find($id);
        if (!$pendiente) {
            session()->setFlashdata('error', 'Pendiente no encontrado');
            return redirect()->to('/inspecciones/pendientes');
        }

        $this->pendientesModel->update($id, [
            'tarea_actividad'          => $this->request->getPost('tarea_actividad'),
            'responsable'              => $this->request->getPost('responsable'),
            'fecha_cierre'             => $this->request->getPost('fecha_cierre') ?: null,
            'estado'                   => $this->request->getPost('estado'),
            'estado_avance'            => $this->request->getPost('estado_avance'),
            'evidencia_para_cerrarla'  => $this->request->getPost('evidencia_para_cerrarla'),
        ]);

        session()->setFlashdata('msg', 'Pendiente actualizado');
        return redirect()->to('/inspecciones/pendientes/cliente/' . $pendiente['id_cliente']);
    }

    /**
     * AJAX: Cambiar estado rápido
     */
    public function changeEstado($id)
    {
        $pendiente = $this->pendientesModel->find($id);
        if (!$pendiente) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'No encontrado']);
        }

        $nuevoEstado = $this->request->getJSON(true)['estado'] ?? '';
        $estadosValidos = ['ABIERTA', 'CERRADA', 'SIN RESPUESTA DEL CLIENTE'];

        if (!in_array($nuevoEstado, $estadosValidos)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Estado no valido']);
        }

        $updateData = ['estado' => $nuevoEstado];
        if ($nuevoEstado === 'CERRADA' && empty($pendiente['fecha_cierre'])) {
            $updateData['fecha_cierre'] = date('Y-m-d');
        }

        $this->pendientesModel->update($id, $updateData);

        return $this->response->setJSON(['success' => true]);
    }

    /**
     * Eliminar pendiente (solo ABIERTA)
     */
    public function delete($id)
    {
        $pendiente = $this->pendientesModel->find($id);
        if (!$pendiente) {
            session()->setFlashdata('error', 'No encontrado');
            return redirect()->to('/inspecciones/pendientes');
        }

        if ($pendiente['estado'] !== 'ABIERTA') {
            session()->setFlashdata('error', 'Solo se pueden eliminar pendientes abiertos');
            return redirect()->to('/inspecciones/pendientes/cliente/' . $pendiente['id_cliente']);
        }

        $idCliente = $pendiente['id_cliente'];
        $this->pendientesModel->delete($id);

        session()->setFlashdata('msg', 'Pendiente eliminado');
        return redirect()->to('/inspecciones/pendientes/cliente/' . $idCliente);
    }
}
