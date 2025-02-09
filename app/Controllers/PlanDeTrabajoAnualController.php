<?php

namespace App\Controllers;

use App\Models\PtaclienteModel;
use App\Models\ClientModel;
use CodeIgniter\Controller;

class PlanDeTrabajoAnualController extends Controller
{
    /**
     * Endpoint API para obtener la lista de clientes.
     * Se retorna un JSON con un arreglo de objetos, donde cada objeto contiene:
     * - id: Identificador del cliente (se asume que en la BD se llama "id_cliente").
     * - nombre: Nombre del cliente (se asume que en la BD se llama "nombre_cliente").
     */
    public function getClientes()
    {
        $clientModel = new ClientModel();
        $clientes = $clientModel->findAll();
        $data = [];

        foreach ($clientes as $cliente) {
            $data[] = [
                'id'     => $cliente['id_cliente'],      // Ajusta según el campo real
                'nombre' => $cliente['nombre_cliente']   // Ajusta según el campo real
            ];
        }

        return $this->response->setJSON($data);
    }

    /**
     * Endpoint API para obtener las actividades filtradas por cliente.
     * Se espera recibir por GET el parámetro 'cliente' con el ID del cliente.
     * Si no se envía, se retorna un arreglo vacío.
     */
    public function getActividades()
    {
        $ptaModel = new PtaclienteModel();
        $clientModel = new ClientModel();
        $clienteID = $this->request->getGet('cliente');

        if (!$clienteID) {
            return $this->response->setJSON(["data" => []]);
        }

        $data = $ptaModel->where('id_cliente', $clienteID)->findAll();

        // Enriquecer cada registro con el nombre del cliente
        foreach ($data as &$actividad) {
            $cliente = $clientModel->find($actividad['id_cliente']);
            $actividad['nombre_cliente'] = $cliente ? $cliente['nombre_cliente'] : 'Cliente no encontrado';
        }
        return $this->response->setJSON(["data" => $data]);
    }


    /**
     * Lista todos los planes de trabajo anual.
     * Se procesa cada registro para calcular la semana (si aún no está asignada)
     * y se enriquecen los datos con la información del cliente.
     */
    public function listPlanDeTrabajoAnual()
    {
        $ptaModel    = new PtaclienteModel();
        $clientModel = new ClientModel();

        $planes      = $ptaModel->asArray()->findAll();
        $actividades = [];

        foreach ($planes as $plan) {
            // Si no se ha definido la semana y hay fecha propuesta, se calcula la semana.
            if (empty($plan['semana']) && !empty($plan['fecha_propuesta'])) {
                $plan['semana'] = date('W', strtotime($plan['fecha_propuesta']));
                $ptaModel->update($plan['id_ptacliente'], ['semana' => $plan['semana']]);
            }

            $cliente = $clientModel->asArray()->find($plan['id_cliente']);
            $actividades[] = [
                'id_ptacliente'                        => $plan['id_ptacliente'],
                'nombre_cliente'                       => $cliente ? $cliente['nombre_cliente'] : 'Cliente no encontrado',
                'tipo_servicio'                        => $plan['tipo_servicio'],
                'phva_plandetrabajo'                   => $plan['phva_plandetrabajo'],
                'numeral_plandetrabajo'                => $plan['numeral_plandetrabajo'],
                'actividad_plandetrabajo'              => $plan['actividad_plandetrabajo'],
                'responsable_sugerido_plandetrabajo'     => $plan['responsable_sugerido_plandetrabajo'],
                'fecha_propuesta'                      => $plan['fecha_propuesta'],
                'fecha_cierre'                         => $plan['fecha_cierre'],
                'responsable_definido_paralaactividad' => $plan['responsable_definido_paralaactividad'],
                'estado_actividad'                     => $plan['estado_actividad'],
                'porcentaje_avance'                    => $plan['porcentaje_avance'],
                'semana'                               => $plan['semana'],
                'observaciones'                        => $plan['observaciones'],
                'created_at'                           => $plan['created_at'],
                'updated_at'                           => $plan['updated_at'],
            ];
        }

        $data['actividades'] = $actividades;
        return view('consultant/listplantrabajoanual', $data);
    }

    /**
     * Muestra el formulario para agregar un nuevo plan de trabajo anual.
     */
    public function addPlanDeTrabajoAnual()
    {
        $clientModel = new ClientModel();
        // Obtener la lista de clientes para el formulario.
        $data['clientes'] = $clientModel->findAll();

        return view('consultant/add_plantrabajoanual', $data);
    }

    /**
     * Procesa el formulario para agregar un nuevo plan de trabajo anual.
     */
    public function addPlanDeTrabajoAnualPost()
    {
        $ptaModel = new PtaclienteModel();

        // Recoger los datos del formulario.
        $fecha_propuesta = $this->request->getPost('fecha_propuesta');

        $data = [
            'id_cliente'                         => $this->request->getPost('id_cliente'),
            'tipo_servicio'                      => $this->request->getPost('tipo_servicio'),
            'phva_plandetrabajo'                 => $this->request->getPost('phva_plandetrabajo'),
            'numeral_plandetrabajo'              => $this->request->getPost('numeral_plandetrabajo'),
            'actividad_plandetrabajo'            => $this->request->getPost('actividad_plandetrabajo'),
            'responsable_sugerido_plandetrabajo'   => $this->request->getPost('responsable_sugerido_plandetrabajo'),
            'fecha_propuesta'                    => $fecha_propuesta,
            'fecha_cierre'                       => $this->request->getPost('fecha_cierre'),
            'responsable_definido_paralaactividad' => $this->request->getPost('responsable_definido_paralaactividad'),
            'estado_actividad'                   => $this->request->getPost('estado_actividad'),
            'porcentaje_avance'                  => $this->request->getPost('porcentaje_avance') !== '' ? (float)$this->request->getPost('porcentaje_avance') : 0.00,
            'semana'                             => !empty($fecha_propuesta) ? (int)date('W', strtotime($fecha_propuesta)) : 1,
            'observaciones'                      => $this->request->getPost('observaciones'),
        ];

        log_message('debug', 'Datos enviados: ' . print_r($this->request->getPost(), true));

        // Intentar insertar los datos en la base de datos.
        if ($ptaModel->insert($data)) {
            return redirect()->to('/listPlanDeTrabajoAnual')->with('msg', 'Plan de trabajo anual agregado exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al agregar plan de trabajo anual');
        }
    }

    /**
     * Muestra el formulario para editar un plan de trabajo anual.
     */
    public function editPlanDeTrabajoAnual($id)
    {
        $ptaModel    = new PtaclienteModel();
        $clientModel = new ClientModel();

        // Obtener el plan de trabajo específico.
        $data['plan'] = $ptaModel->find($id);

        // Obtener la lista de clientes para el formulario.
        $data['clientes'] = $clientModel->findAll();

        // Verificar si el plan existe.
        if (!$data['plan']) {
            return redirect()->to('/listPlanDeTrabajoAnual')->with('msg', 'Plan de trabajo no encontrado');
        }

        return view('consultant/edit_plantrabajoanual', $data);
    }

    /**
     * Procesa el formulario de edición de un plan de trabajo anual.
     */
    public function editPlanDeTrabajoAnualPost($id)
    {
        $ptaModel = new PtaclienteModel();

        // Recoger los datos del formulario.
        $fecha_propuesta = $this->request->getPost('fecha_propuesta');

        $data = [
            'id_cliente'                         => $this->request->getPost('id_cliente'),
            'tipo_servicio'                      => $this->request->getPost('tipo_servicio'),
            'phva_plandetrabajo'                 => $this->request->getPost('phva_plandetrabajo'),
            'numeral_plandetrabajo'              => $this->request->getPost('numeral_plandetrabajo'),
            'actividad_plandetrabajo'            => $this->request->getPost('actividad_plandetrabajo'),
            'responsable_sugerido_plandetrabajo'   => $this->request->getPost('responsable_sugerido_plandetrabajo'),
            'fecha_propuesta'                    => $fecha_propuesta,
            'fecha_cierre'                       => $this->request->getPost('fecha_cierre'),
            'responsable_definido_paralaactividad' => $this->request->getPost('responsable_definido_paralaactividad'),
            'estado_actividad'                   => $this->request->getPost('estado_actividad'),
            'porcentaje_avance'                  => $this->request->getPost('porcentaje_avance'),
            'semana'                             => !empty($fecha_propuesta) ? date('W', strtotime($fecha_propuesta)) : null,
            'observaciones'                      => $this->request->getPost('observaciones'),
        ];

        log_message('debug', 'Datos recibidos del formulario: ' . print_r($this->request->getPost(), true));

        // Intentar actualizar los datos en la base de datos.
        if ($ptaModel->update($id, $data)) {
            return redirect()->to('/listPlanDeTrabajoAnual')->with('msg', 'Plan de trabajo anual actualizado exitosamente');
        } else {
            log_message('error', 'Errores al actualizar plan de trabajo: ' . print_r($ptaModel->errors(), true));
            return redirect()->back()->with('msg', 'Error al actualizar plan de trabajo anual');
        }
    }

    /**
     * Elimina un plan de trabajo anual según su ID.
     */
    public function deletePlanDeTrabajoAnual($id)
    {
        $ptaModel = new PtaclienteModel();

        // Verificar que el plan exista.
        $plan = $ptaModel->find($id);
        if (!$plan) {
            return redirect()->to('/listPlanDeTrabajoAnual')->with('msg', 'El plan de trabajo no existe');
        }

        // Intentar eliminar el plan.
        if ($ptaModel->delete($id)) {
            return redirect()->to('/listPlanDeTrabajoAnual')->with('msg', 'Plan de trabajo anual eliminado exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al eliminar el plan de trabajo anual');
        }
    }

    /**
     * Actualiza un campo específico de un plan de trabajo (útil para edición en línea).
     */
    public function updatePlanDeTrabajo()
    {
        $ptaModel = new PtaclienteModel();

        // Obtener los datos enviados por POST.
        $id    = $this->request->getPost('id');
        $field = $this->request->getPost('field');
        $value = $this->request->getPost('value');

        // Definir los campos permitidos para actualización.
        $allowedFields = [
            'fecha_cierre',
            'responsable_definido_paralaactividad',
            'responsable_sugerido_plandetrabajo',
            'estado_actividad',
            'porcentaje_avance',
            'observaciones',
            'fecha_propuesta'
        ];

        // Verificar que el campo a actualizar esté permitido.
        if (!in_array($field, $allowedFields)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Campo no permitido']);
        }

        // Validar el valor para el campo 'estado_actividad'.
        if ($field === 'estado_actividad') {
            $allowedStates = ['ABIERTA', 'CERRADA', 'GESTIONANDO'];
            if (!in_array(strtoupper($value), $allowedStates)) {
                return $this->response->setJSON(['success' => false, 'message' => 'Estado no permitido']);
            }
        }

        $updateData = [$field => $value];

        // Si se actualiza la 'fecha_propuesta', se recalcula la semana.
        if ($field === 'fecha_propuesta') {
            $week = date('W', strtotime($value));
            $updateData['semana'] = $week;
            log_message('debug', "Fecha propuesta actualizada: $value, Semana recalculada: $week");
        }

        // Intentar actualizar el registro.
        if ($ptaModel->update($id, $updateData)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Registro actualizado']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'No se pudo actualizar el registro']);
        }
    }
}
