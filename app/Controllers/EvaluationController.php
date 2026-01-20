<?php

namespace App\Controllers;

use App\Models\EvaluationModel;
use App\Models\ClientModel;
use CodeIgniter\Controller;

class EvaluationController extends Controller
{

    public function listEvaluacionesAjax()
    {
        return view('consultant/list_evaluaciones_ajax');
    }
    
    // API: Retorna la lista de clientes en formato JSON con las claves "id" y "nombre"
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
    
    // API: Retorna la lista de evaluaciones filtrada por el parámetro 'cliente'
    public function getEvaluaciones()
    {
        $clienteID = $this->request->getGet('cliente');
        $evaluationModel = new EvaluationModel();
        $clientModel = new ClientModel();
        
        if (empty($clienteID)) {
            return $this->response->setJSON([]);
        }
        
        $evaluaciones = $evaluationModel->where('id_cliente', $clienteID)->findAll();
        
        $clientes = $clientModel->findAll();
        $clientsMap = [];
        foreach ($clientes as $cliente) {
            $clientsMap[$cliente['id_cliente']] = $cliente['nombre_cliente'];
        }
        
        foreach ($evaluaciones as &$evaluacion) {
            $evaluacion['nombre_cliente'] = isset($clientsMap[$evaluacion['id_cliente']]) ? $clientsMap[$evaluacion['id_cliente']] : 'Cliente no encontrado';
            $evaluacion['acciones'] = '<a href="' . base_url('editEvaluacion/' . $evaluacion['id_ev_ini']) . '" class="btn btn-sm btn-warning">Editar</a> ' .
                                      '<a href="' . base_url('deleteEvaluacion/' . $evaluacion['id_ev_ini']) . '" class="btn btn-sm btn-danger" onclick="return confirm(\'¿Estás seguro de que quieres eliminar esta evaluación?\');">Eliminar</a>';
        }
        
        return $this->response->setJSON($evaluaciones);
    }
    
    // API: Actualiza la evaluación vía AJAX para edición inline
    public function updateEvaluacion()
    {
        $id = $this->request->getPost('id');
        $field = $this->request->getPost('field');
        $value = $this->request->getPost('value');
        
        $allowedFields = ['evaluacion_inicial', 'observaciones'];
        if (!in_array($field, $allowedFields)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Campo no permitido']);
        }
        
        $model = new EvaluationModel();
        $updateData = [$field => $value];
        
        if ($field === 'evaluacion_inicial') {
            $evaluation = $model->find($id);
            $valor = isset($evaluation['valor']) ? $evaluation['valor'] : 0;
            $puntaje_cuantitativo = in_array($value, ['CUMPLE TOTALMENTE', 'NO APLICA']) ? $valor : 0;
            $updateData['puntaje_cuantitativo'] = $puntaje_cuantitativo;
        }
        
        if ($model->update($id, $updateData)) {
            $updatedRecord = $model->find($id);
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Registro actualizado correctamente',
                'puntaje_cuantitativo' => $updatedRecord['puntaje_cuantitativo']
            ]);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Error al actualizar el registro']);
        }
    }
    // Listar las evaluaciones
    public function listEvaluaciones()
    {
        $evaluationModel = new EvaluationModel();
        $clientModel = new ClientModel();

        // Obtener todas las evaluaciones
        $evaluaciones = $evaluationModel->findAll();

        // Obtener los datos de los clientes para incluir los nombres
        $clients = $clientModel->findAll();

        // Crear un array asociando los id_cliente con los nombres de cliente
        $clientsMap = [];
        foreach ($clients as $client) {
            $clientsMap[$client['id_cliente']] = $client['nombre_cliente'];
        }

        // Agregar los nombres de los clientes a las evaluaciones
        foreach ($evaluaciones as &$evaluacion) {
            $evaluacion['nombre_cliente'] = $clientsMap[$evaluacion['id_cliente']] ?? 'Cliente no encontrado';
        }

        return view('consultant/list_evaluaciones', ['evaluaciones' => $evaluaciones]);
    }

    // Mostrar el formulario para añadir una nueva evaluación
    public function addEvaluacion()
    {
        $clientModel = new ClientModel();
        $clients = $clientModel->findAll();

        return view('consultant/add_evaluacion', ['clients' => $clients]);
    }

    // Guardar una nueva evaluación en la base de datos
    public function addEvaluacionPost()
    {
        $model = new EvaluationModel();

        $valor = $this->request->getVar('valor');
        $evaluacion_inicial = $this->request->getVar('evaluacion_inicial');

        // Lógica del CASE para puntaje_cuantitativo
        $puntaje_cuantitativo = 0;
        if ($evaluacion_inicial == 'CUMPLE TOTALMENTE' || $evaluacion_inicial == 'NO APLICA') {
            $puntaje_cuantitativo = $valor;
        }

        $data = [
            'id_cliente' => $this->request->getVar('id_cliente'),
            'ciclo' => $this->request->getVar('ciclo'),
            'estandar' => $this->request->getVar('estandar'),
            'detalle_estandar' => $this->request->getVar('detalle_estandar'),
            'estandares_minimos' => $this->request->getVar('estandares_minimos'),
            'numeral' => $this->request->getVar('numeral'),
            'numerales_del_cliente' => $this->request->getVar('numerales_del_cliente'),
            'siete' => $this->request->getVar('siete'),
            'veintiun' => $this->request->getVar('veintiun'),
            'sesenta' => $this->request->getVar('sesenta'),
            'item_del_estandar' => $this->request->getVar('item_del_estandar'),
            'evaluacion_inicial' => $evaluacion_inicial,
            'valor' => $valor,
            'puntaje_cuantitativo' => $puntaje_cuantitativo,
            'item' => $this->request->getVar('item'),
            'criterio' => $this->request->getVar('criterio'),
            'modo_de_verificacion' => $this->request->getVar('modo_de_verificacion'),
            'calificacion' => $this->request->getVar('calificacion'),
            'nivel_de_evaluacion' => $this->request->getVar('nivel_de_evaluacion'),
            'observaciones' => $this->request->getVar('observaciones'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($model->insert($data)) {
            return redirect()->to('/listEvaluaciones')->with('msg', 'Evaluación agregada exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al agregar evaluación');
        }
    }

    // Editar una evaluación existente
    public function editEvaluacion($id)
    {
        $evaluationModel = new EvaluationModel();
        $clientModel = new ClientModel();

        $data['evaluacion'] = $evaluationModel->find($id);
        $data['clients'] = $clientModel->findAll();

        return view('consultant/edit_evaluacion', $data);
    }

    // Actualizar una evaluación existente
    public function editEvaluacionPost($id)
    {
        $model = new EvaluationModel();

        $valor = $this->request->getVar('valor');
        $evaluacion_inicial = $this->request->getVar('evaluacion_inicial');

        // Lógica del CASE para puntaje_cuantitativo
        $puntaje_cuantitativo = 0;
        if ($evaluacion_inicial == 'CUMPLE TOTALMENTE' || $evaluacion_inicial == 'NO APLICA') {
            $puntaje_cuantitativo = $valor;
        }

        $data = [
            'id_cliente' => $this->request->getVar('id_cliente'),
            'ciclo' => $this->request->getVar('ciclo'),
            'estandar' => $this->request->getVar('estandar'),
            'detalle_estandar' => $this->request->getVar('detalle_estandar'),
            'estandares_minimos' => $this->request->getVar('estandares_minimos'),
            'numeral' => $this->request->getVar('numeral'),
            'numerales_del_cliente' => $this->request->getVar('numerales_del_cliente'),
            'siete' => $this->request->getVar('siete'),
            'veintiun' => $this->request->getVar('veintiun'),
            'sesenta' => $this->request->getVar('sesenta'),
            'item_del_estandar' => $this->request->getVar('item_del_estandar'),
            'evaluacion_inicial' => $evaluacion_inicial,
            'valor' => $valor,
            'puntaje_cuantitativo' => $puntaje_cuantitativo,
            'item' => $this->request->getVar('item'),
            'criterio' => $this->request->getVar('criterio'),
            'modo_de_verificacion' => $this->request->getVar('modo_de_verificacion'),
            'calificacion' => $this->request->getVar('calificacion'),
            'nivel_de_evaluacion' => $this->request->getVar('nivel_de_evaluacion'),
            'observaciones' => $this->request->getVar('observaciones'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($model->update($id, $data)) {
            return redirect()->to('/listEvaluaciones')->with('msg', 'Evaluación actualizada exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al actualizar evaluación');
        }
    }

    // Eliminar una evaluación
    public function deleteEvaluacion($id)
    {
        $model = new EvaluationModel();
        $model->delete($id);

        return redirect()->to('/listEvaluaciones')->with('msg', 'Evaluación eliminada exitosamente');
    }

    // API: Retorna los indicadores de un cliente específico
    public function getClientIndicators()
    {
        $clienteID = $this->request->getGet('cliente_id');
        $evaluationModel = new EvaluationModel();
        
        if (empty($clienteID)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cliente ID requerido']);
        }
        
        // Obtener todas las evaluaciones del cliente
        $evaluaciones = $evaluationModel->where('id_cliente', $clienteID)->findAll();
        
        if (empty($evaluaciones)) {
            return $this->response->setJSON(['success' => false, 'message' => 'No hay evaluaciones para este cliente']);
        }
        
        // Sumar puntaje_cuantitativo y valor
        $sum_puntaje_cuantitativo = array_sum(array_column($evaluaciones, 'puntaje_cuantitativo'));
        $sum_valor = array_sum(array_column($evaluaciones, 'valor'));

        // Calcular el indicador general
        $indicador_general = ($sum_valor != 0) ? $sum_puntaje_cuantitativo / $sum_valor : 0;

        // Contar y sumar valores por cada categoría (Cumple Totalmente, No Cumple, No Aplica)
        $count_cumple = $count_no_cumple = $count_no_aplica = 0;
        $sum_puntaje_cumple = $sum_puntaje_no_cumple = $sum_puntaje_no_aplica = 0;
        $sum_valor_cumple = $sum_valor_no_cumple = $sum_valor_no_aplica = 0;

        foreach ($evaluaciones as $evaluacion) {
            switch ($evaluacion['evaluacion_inicial']) {
                case 'CUMPLE TOTALMENTE':
                    $count_cumple++;
                    $sum_puntaje_cumple += $evaluacion['puntaje_cuantitativo'];
                    $sum_valor_cumple += $evaluacion['valor'];
                    break;
                case 'NO CUMPLE':
                    $count_no_cumple++;
                    $sum_puntaje_no_cumple += $evaluacion['puntaje_cuantitativo'];
                    $sum_valor_no_cumple += $evaluacion['valor'];
                    break;
                case 'NO APLICA':
                    $count_no_aplica++;
                    $sum_puntaje_no_aplica += $evaluacion['puntaje_cuantitativo'];
                    $sum_valor_no_aplica += $evaluacion['valor'];
                    break;
            }
        }

        // Calcular indicadores por categoría
        $indicador_cumple = ($sum_valor_cumple != 0) ? $sum_puntaje_cumple / $sum_valor_cumple : 0;
        $indicador_no_cumple = ($sum_valor_no_cumple != 0) ? $sum_puntaje_no_cumple / $sum_valor_no_cumple : 0;
        $indicador_no_aplica = ($sum_valor_no_aplica != 0) ? $sum_puntaje_no_aplica / $sum_valor_no_aplica : 0;
        
        return $this->response->setJSON([
            'success' => true,
            'sum_puntaje_cuantitativo' => $sum_puntaje_cuantitativo,
            'sum_valor' => $sum_valor,
            'indicador_general' => $indicador_general,
            'count_cumple' => $count_cumple,
            'sum_puntaje_cumple' => $sum_puntaje_cumple,
            'sum_valor_cumple' => $sum_valor_cumple,
            'indicador_cumple' => $indicador_cumple,
            'count_no_cumple' => $count_no_cumple,
            'sum_puntaje_no_cumple' => $sum_puntaje_no_cumple,
            'sum_valor_no_cumple' => $sum_valor_no_cumple,
            'indicador_no_cumple' => $indicador_no_cumple,
            'count_no_aplica' => $count_no_aplica,
            'sum_puntaje_no_aplica' => $sum_puntaje_no_aplica,
            'sum_valor_no_aplica' => $sum_valor_no_aplica,
            'indicador_no_aplica' => $indicador_no_aplica
        ]);
    }

    /**
     * Resetear evaluaciones del ciclo PHVA para un cliente específico
     * Solo resetea los items que se renuevan anualmente según el Decreto 1072
     */
    public function resetCicloPHVA()
    {
        $idCliente = $this->request->getPost('id_cliente');

        if (!$idCliente) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID de cliente no proporcionado'
            ]);
        }

        $evaluationModel = new EvaluationModel();
        $clientModel = new ClientModel();

        // Verificar que el cliente existe
        $cliente = $clientModel->find($idCliente);
        if (!$cliente) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Cliente no encontrado'
            ]);
        }

        // Numerales de los items que se resetean en el ciclo PHVA anual
        // Usamos el campo 'numeral' que es más estandarizado
        $numeralesARenovar = [
            '1.1.2',  // Responsabilidades en el SG-SST
            '1.1.3',  // Asignación de recursos
            '1.1.4',  // Afiliación al Sistema de Riesgos Laborales
            '1.1.5',  // Identificación trabajadores alto riesgo
            '1.1.6',  // Conformación COPASST
            '1.1.7',  // Capacitación COPASST
            '1.1.8',  // Conformación Comité de Convivencia
            '1.2.1',  // Programa Capacitación PYP
            '1.2.2',  // Inducción y Reinducción
            '1.2.3',  // Responsables con curso 50 horas
            '2.1.1',  // Política del SG-SST
            '2.2.1',  // Objetivos del SG-SST
            '2.4.1',  // Plan de trabajo anual
            '2.5.1',  // Archivo documental
            '2.9.1',  // Adquisición de productos
            '2.10.1', // Evaluación de proveedores
            '3.1.1',  // Diagnóstico de Condiciones de Salud
            '3.1.2',  // Actividades de Promoción y Prevención
            '3.1.4',  // Evaluaciones médicas ocupacionales
            '3.1.5',  // Custodia de Historias Clínicas
            '3.1.6',  // Restricciones médico laborales
            '3.1.7',  // Estilos de vida saludables
            '3.1.8',  // Agua potable y servicios sanitarios
            '3.1.9',  // Eliminación de residuos
            '3.2.1',  // Reporte de AT y EL
            '3.2.2',  // Investigación de Incidentes
            '3.2.3',  // Registro estadístico
            '3.3.6',  // Medición del ausentismo
            '4.1.2',  // Identificación de peligros
            '4.2.1',  // Medidas de prevención y control
            '4.2.4',  // Inspecciones
            '4.2.5',  // Mantenimiento periódico
            '4.2.6',  // Entrega de EPP
            '5.1.1',  // Plan de emergencias
            '5.1.2',  // Brigada de prevención
            '6.1.3',  // Revisión anual de la alta dirección
            '7.1.1'   // Acciones preventivas y correctivas
        ];

        $db = \Config\Database::connect();
        $builder = $db->table('evaluacion_inicial_sst');

        // Resetear: poner evaluacion_inicial = '' y puntaje_cuantitativo = 0
        // Usamos el campo 'numeral' para hacer match exacto
        $affectedRows = $builder
            ->where('id_cliente', $idCliente)
            ->whereIn('numeral', $numeralesARenovar)
            ->update([
                'evaluacion_inicial' => '',
                'puntaje_cuantitativo' => 0,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

        $countAffected = $db->affectedRows();

        if ($countAffected > 0) {
            log_message('info', "Ciclo PHVA reseteado para cliente {$cliente['nombre_cliente']} (ID: {$idCliente}). Items afectados: {$countAffected}");
            return $this->response->setJSON([
                'success' => true,
                'message' => "Se resetearon {$countAffected} evaluaciones del ciclo PHVA para {$cliente['nombre_cliente']}",
                'affected_rows' => $countAffected
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No se encontraron evaluaciones para resetear o ya estaban vacías'
            ]);
        }
    }

    /**
     * Obtener lista de clientes para el modal de reseteo
     */
    public function getClientesParaReseteo()
    {
        $clientModel = new ClientModel();
        $clientes = $clientModel->orderBy('nombre_cliente', 'ASC')->findAll();

        $data = [];
        foreach ($clientes as $cliente) {
            $data[] = [
                'id' => $cliente['id_cliente'],
                'nombre' => $cliente['nombre_cliente']
            ];
        }

        return $this->response->setJSON($data);
    }

}
