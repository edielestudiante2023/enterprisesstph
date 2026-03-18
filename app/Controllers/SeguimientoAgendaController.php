<?php

namespace App\Controllers;

use App\Models\SeguimientoAgendaModel;
use App\Models\SeguimientoHistorialModel;
use App\Models\ClientModel;

class SeguimientoAgendaController extends BaseController
{
    private SeguimientoAgendaModel   $model;
    private SeguimientoHistorialModel $historial;
    private ClientModel              $clientModel;

    public function __construct()
    {
        $this->model       = new SeguimientoAgendaModel();
        $this->historial   = new SeguimientoHistorialModel();
        $this->clientModel = new ClientModel();
    }

    public function index(): string
    {
        $seguimientos = $this->model->getAllConCliente();
        $clientes     = $this->clientModel->where('estado', 'activo')->orderBy('nombre_cliente')->findAll();

        return view('seguimiento/index', [
            'seguimientos' => $seguimientos,
            'clientes'     => $clientes,
        ]);
    }

    public function store()
    {
        $data = [
            'id_cliente'      => $this->request->getPost('id_cliente'),
            'asunto'          => $this->request->getPost('asunto'),
            'mensaje'         => $this->request->getPost('mensaje'),
            'opciones_fechas' => $this->request->getPost('opciones_fechas'),
            'consultor'       => $this->request->getPost('consultor') ?: 'Edison Cuervo',
            'cargo_consultor' => $this->request->getPost('cargo_consultor') ?: 'Consultor SST',
            'activo'          => 1,
            'detenido'        => 0,
        ];

        // Evitar duplicado activo para el mismo cliente
        $existe = $this->model->where('id_cliente', $data['id_cliente'])
            ->where('activo', 1)->where('detenido', 0)->first();

        if ($existe) {
            return $this->response->setJSON(['success' => false, 'message' => 'Este cliente ya tiene un seguimiento activo.']);
        }

        $this->model->save($data);
        return $this->response->setJSON(['success' => true, 'message' => 'Seguimiento activado correctamente.']);
    }

    public function detener($id)
    {
        $seg = $this->model->find($id);
        if (!$seg) {
            return $this->response->setJSON(['success' => false, 'message' => 'No encontrado.']);
        }

        $motivo = $this->request->getPost('motivo') ?: 'Detenido manualmente';
        $this->model->update($id, [
            'activo'           => 0,
            'detenido'         => 1,
            'motivo_detencion' => $motivo,
        ]);

        return $this->response->setJSON(['success' => true, 'message' => 'Seguimiento detenido.']);
    }

    public function reactivar($id)
    {
        $this->model->update($id, [
            'activo'           => 1,
            'detenido'         => 0,
            'motivo_detencion' => null,
        ]);
        return $this->response->setJSON(['success' => true, 'message' => 'Seguimiento reactivado.']);
    }

    public function destroy($id)
    {
        $this->model->delete($id);
        return $this->response->setJSON(['success' => true]);
    }

    public function historialCliente($id)
    {
        $registros = $this->historial
            ->where('id_seguimiento', $id)
            ->orderBy('fecha_envio', 'DESC')
            ->findAll(30);

        return $this->response->setJSON(['success' => true, 'data' => $registros]);
    }
}
