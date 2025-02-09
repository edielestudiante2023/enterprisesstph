<?php

namespace App\Controllers;

use App\Models\PendientesModel;
use App\Models\ClientModel;
use CodeIgniter\Controller;

class PendientesController extends Controller
{
    // Renderiza la vista AJAX para pendientes
    public function listPendientesAjax()
    {
        return view('consultant/list_pendientes_ajax');
    }

    // API: Retorna la lista de clientes en formato JSON
    public function getClientes()
    {
        $clientModel = new ClientModel();
        $clientes = $clientModel->findAll();
        $data = [];
        foreach ($clientes as $cliente) {
            $data[] = [
                'id'     => $cliente['id_cliente'],
                'nombre' => $cliente['nombre_cliente']
            ];
        }
        return $this->response->setJSON($data);
    }

    // API: Retorna la lista de pendientes filtrada por el parámetro 'cliente'
    public function getPendientesAjax()
    {
        $clienteID = $this->request->getGet('cliente');
        $pendientesModel = new PendientesModel();
        $clientModel = new ClientModel();

        if (empty($clienteID)) {
            return $this->response->setJSON([]);
        }

        $pendientes = $pendientesModel->where('id_cliente', $clienteID)->findAll();

        // Enriquecer cada pendiente con el nombre del cliente
        foreach ($pendientes as &$pendiente) {
            $cliente = $clientModel->find($pendiente['id_cliente']);
            $pendiente['nombre_cliente'] = $cliente['nombre_cliente'] ?? 'Cliente desconocido';
            // Generar botones de acciones
            $pendiente['acciones'] = '<a href="' . base_url('editPendiente/' . $pendiente['id_pendientes']) . '" class="btn btn-warning btn-sm">Editar</a> ' .
                '<a href="' . base_url('deletePendiente/' . $pendiente['id_pendientes']) . '" class="btn btn-danger btn-sm" onclick="return confirm(\'¿Estás seguro de eliminar este pendiente?\');">Eliminar</a>';
        }

        return $this->response->setJSON($pendientes);
    }

    // API: Actualiza un campo específico de un pendiente (para inline editing)
    public function updatePendiente()
    {
        $id = $this->request->getPost('id');
        $field = $this->request->getPost('field');
        $value = $this->request->getPost('value');

        // Definir los campos permitidos para actualización
        $allowedFields = ['tarea_actividad', 'fecha_cierre', 'estado', 'estado_avance', 'evidencia_para_cerrarla', 'fecha_asignacion'];
        if (!in_array($field, $allowedFields)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Campo no permitido']);
        }

        $model = new PendientesModel();
        $pendiente = $model->find($id);
        if (!$pendiente) {
            return $this->response->setJSON(['success' => false, 'message' => 'Pendiente no encontrado']);
        }

        $updateData = [$field => $value];

        // Si el campo afecta el cálculo de 'conteo_dias'
        $fechaAsignacion = strtotime($pendiente['fecha_asignacion']);
        $estado = ($field === 'estado') ? $value : $pendiente['estado'];
        $fechaCierre = ($field === 'fecha_cierre') ? $value : $pendiente['fecha_cierre'];

        if ($estado === 'ABIERTA') {
            $updateData['conteo_dias'] = (int) floor((time() - $fechaAsignacion) / (60 * 60 * 24));
        } elseif ($estado === 'CERRADA' && !empty($fechaCierre)) {
            $updateData['conteo_dias'] = (int) floor((strtotime($fechaCierre) - $fechaAsignacion) / (60 * 60 * 24));
        } else {
            $updateData['conteo_dias'] = 0;
        }

        if ($model->update($id, $updateData)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Registro actualizado correctamente',
                'updatedValue' => $updateData['conteo_dias']
            ]);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Error al actualizar el registro']);
        }
    }
}
