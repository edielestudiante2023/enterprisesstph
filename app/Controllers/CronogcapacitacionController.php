<?php

namespace App\Controllers;

use App\Models\CronogcapacitacionModel;
use App\Models\ClientModel;
use App\Models\CapacitacionModel;
use App\Models\ContractModel;
use CodeIgniter\Controller;

class CronogcapacitacionController extends Controller
{

    public function listcronogCapacitacionAjax()
    {
        return view('consultant/list_cronogramas_ajax');
    }

    // API: Retorna la lista de clientes en formato JSON (igual que en otros módulos)
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

    // API: Retorna la lista de cronogramas filtrada por el parámetro 'cliente'
    public function getCronogramasAjax()
    {
        $clienteID = $this->request->getGet('cliente');
        $cronogModel = new CronogcapacitacionModel();
        $clientModel = new ClientModel();
        $capacitacionModel = new CapacitacionModel();

        if (empty($clienteID)) {
            return $this->response->setJSON([]);
        }

        $cronogramas = $cronogModel->where('id_cliente', $clienteID)->findAll();

        // Enriquecer cada registro con datos del cliente y capacitación
        foreach ($cronogramas as &$cronograma) {
            $cliente = $clientModel->find($cronograma['id_cliente']);
            $cronograma['nombre_cliente'] = $cliente['nombre_cliente'] ?? 'Cliente no encontrado';

            // Si el cronograma ya tiene nombre_capacitacion en la BD, usarlo
            // De lo contrario, buscar en la tabla antigua tbl_capacitaciones_sst
            if (empty($cronograma['nombre_capacitacion']) && !empty($cronograma['id_capacitacion'])) {
                $capacitacion = $capacitacionModel->find($cronograma['id_capacitacion']);
                $cronograma['nombre_capacitacion'] = $capacitacion['capacitacion'] ?? 'Capacitación no encontrada';
                $cronograma['objetivo_capacitacion'] = $capacitacion['objetivo_capacitacion'] ?? 'Objetivo no disponible';
            }

            // Generar botones de acciones
            $cronograma['acciones'] = '<a href="' . base_url('/editcronogCapacitacion/' . $cronograma['id_cronograma_capacitacion']) . '" class="btn btn-warning btn-sm">Editar</a> ' .
                '<a href="' . base_url('/deletecronogCapacitacion/' . $cronograma['id_cronograma_capacitacion']) . '" class="btn btn-danger btn-sm" onclick="return confirm(\'¿Estás seguro de eliminar este cronograma?\');">Eliminar</a>';
        }

        return $this->response->setJSON($cronogramas);
    }

    // API: Actualiza campos específicos del cronograma de capacitación (para edición inline)
    public function updatecronogCapacitacion()
    {
        log_message('debug', 'Datos recibidos: ' . print_r($this->request->getPost(), true));
        $id = $this->request->getPost('id');
        $field = $this->request->getPost('field');
        $value = $this->request->getPost('value');

        $allowedFields = [
            'nombre_capacitacion',
            'objetivo_capacitacion',
            'fecha_programada',
            'fecha_de_realizacion',
            'estado',
            'perfil_de_asistentes',
            'nombre_del_capacitador',
            'horas_de_duracion_de_la_capacitacion',
            'indicador_de_realizacion_de_la_capacitacion',
            'numero_de_asistentes_a_capacitacion',
            'numero_total_de_personas_programadas',
            'porcentaje_cobertura',
            'numero_de_personas_evaluadas',
            'promedio_de_calificaciones',
            'observaciones'
        ];

        if (!in_array($field, $allowedFields)) {
            log_message('error', 'Campo no permitido: ' . $field);
            return $this->response->setJSON(['success' => false, 'message' => 'Campo no permitido']);
        }

        $cronogModel = new CronogcapacitacionModel();

        // Si se actualiza alguno de los campos que afectan el porcentaje, recalcúlalo
        if (in_array($field, ['numero_de_asistentes_a_capacitacion', 'numero_total_de_personas_programadas'])) {
            // Obtén el registro actual para el otro valor
            $registro = $cronogModel->find($id);
            if ($field == 'numero_de_asistentes_a_capacitacion') {
                $numero_asistentes = $value;
                $numero_total_programados = $registro['numero_total_de_personas_programadas'];
            } else {
                $numero_asistentes = $registro['numero_de_asistentes_a_capacitacion'];
                $numero_total_programados = $value;
            }
            $porcentaje_cobertura = ($numero_total_programados > 0)
                ? number_format(($numero_asistentes / $numero_total_programados) * 100, 2)
                : 0;

            // Actualiza el campo modificado y el porcentaje en conjunto
            $updateData = [
                $field => $value,
                'porcentaje_cobertura' => $porcentaje_cobertura . '%'
            ];
        } else {
            $updateData = [$field => $value];
        }

    if ($cronogModel->update($id, $updateData)) {
        log_message('debug', 'Registro actualizado correctamente');
        return $this->response->setJSON([
            'success' => true, 
            'message' => 'Registro actualizado correctamente',
            'newValue' => isset($porcentaje_cobertura) ? $porcentaje_cobertura . '%' : $value
        ]);

            log_message('debug', 'Registro actualizado correctamente');
            return $this->response->setJSON(['success' => true, 'message' => 'Registro actualizado correctamente']);
        } else {
            log_message('error', 'Error al actualizar el registro');
            return $this->response->setJSON(['success' => false, 'message' => 'No se pudo actualizar el registro']);
        }
    }

    // Listar todos los cronogramas de capacitación
    public function listcronogCapacitacion()
    {
        $cronogModel = new CronogcapacitacionModel();
        $clientModel = new ClientModel();
        $capacitacionModel = new CapacitacionModel();

        // Obtenemos todos los cronogramas
        $cronogramas = $cronogModel->findAll();

        // Iteramos los cronogramas para obtener los datos relacionados (nombre del cliente y capacitación)
        foreach ($cronogramas as &$cronograma) {
            // Obtenemos el nombre del cliente
            $cliente = $clientModel->find($cronograma['id_cliente']);
            $cronograma['nombre_cliente'] = $cliente['nombre_cliente'] ?? 'Cliente no encontrado';

            // Obtenemos el nombre de la capacitación y el objetivo
            $capacitacion = $capacitacionModel->find($cronograma['id_capacitacion']);
            $cronograma['nombre_capacitacion'] = $capacitacion['capacitacion'] ?? 'Capacitación no encontrada';
            $cronograma['objetivo_capacitacion'] = $capacitacion['objetivo_capacitacion'] ?? 'Objetivo no disponible';
        }

        // Pasamos los datos a la vista
        $data['cronogramas'] = $cronogramas;
        return view('consultant/list_cronogramas', $data);
    }

    // Mostrar formulario para agregar nuevo cronograma de capacitación
    public function addcronogCapacitacion()
    {
        $capacitacionModel = new CapacitacionModel();
        $clienteModel = new ClientModel();

        // Obtener capacitaciones y clientes
        $capacitaciones = $capacitacionModel->findAll();
        $clientes = $clienteModel->findAll();

        // Preparar los datos para la vista
        $data = [
            'capacitaciones' => $capacitaciones,
            'clientes' => $clientes,
        ];

        return view('consultant/add_cronograma', $data);
    }

    // Guardar nuevo cronograma de capacitación
    public function addcronogCapacitacionPost()
    {
        $cronogModel = new CronogcapacitacionModel();

        // Depuración: Mostrar los valores recibidos
        log_message('debug', 'Datos POST recibidos: ' . print_r($this->request->getPost(), true));

        // Capturar el valor de nombre_capacitacion
        $nombre_capacitacion = $this->request->getPost('nombre_capacitacion');

        // Si `nombre_capacitacion` está vacío, detener el proceso
        if (empty($nombre_capacitacion)) {
            return redirect()->back()->with('msg', 'Error: El nombre de la capacitación es obligatorio.');
        }

        // Preparar los datos para la inserción
        $data = [
            'nombre_capacitacion' => $nombre_capacitacion,
            'objetivo_capacitacion' => $this->request->getPost('objetivo_capacitacion'),
            'id_cliente' => $this->request->getPost('id_cliente'),
            'fecha_programada' => $this->request->getPost('fecha_programada'),
            'fecha_de_realizacion' => $this->request->getPost('fecha_de_realizacion'),
            'estado' => $this->request->getPost('estado'),
            'perfil_de_asistentes' => $this->request->getPost('perfil_de_asistentes'),
            'nombre_del_capacitador' => $this->request->getPost('nombre_del_capacitador'),
            'horas_de_duracion_de_la_capacitacion' => $this->request->getPost('horas_de_duracion_de_la_capacitacion'),
            'indicador_de_realizacion_de_la_capacitacion' => $this->request->getPost('indicador_de_realizacion_de_la_capacitacion'),
            'numero_de_asistentes_a_capacitacion' => $this->request->getPost('numero_de_asistentes_a_capacitacion'),
            'numero_total_de_personas_programadas' => $this->request->getPost('numero_total_de_personas_programadas'),
            'porcentaje_cobertura' => $this->request->getPost('porcentaje_cobertura'),
            'numero_de_personas_evaluadas' => $this->request->getPost('numero_de_personas_evaluadas'),
            'promedio_de_calificaciones' => $this->request->getPost('promedio_de_calificaciones'),
            'observaciones' => $this->request->getPost('observaciones'),
        ];

        // Intentar insertar el nuevo cronograma
        if ($cronogModel->insert($data)) {
            return redirect()->to('/listcronogCapacitacion')->with('msg', 'Cronograma agregado exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al agregar cronograma.');
        }
    }

    // Mostrar formulario para editar cronograma de capacitación
    public function editcronogCapacitacion($id)
    {
        $cronogModel = new CronogcapacitacionModel();
        $clientModel = new ClientModel();
        $capacitacionModel = new CapacitacionModel();

        // Obtener el cronograma que se va a editar
        $cronograma = $cronogModel->find($id);
        if (!$cronograma) {
            return redirect()->to('/listcronogCapacitacion')->with('msg', 'Cronograma no encontrado.');
        }

        // Obtener listas de clientes y capacitaciones para los selects del formulario
        $clientes = $clientModel->findAll();
        $capacitaciones = $capacitacionModel->findAll();

        // Preparar los datos para la vista
        $data = [
            'cronograma' => $cronograma,
            'clientes' => $clientes,
            'capacitaciones' => $capacitaciones,
        ];

        return view('consultant/edit_cronograma', $data);
    }

    // Actualizar cronograma de capacitación
    public function editcronogCapacitacionPost($id)
    {
        $cronogModel = new CronogcapacitacionModel();

        $numero_asistentes = $this->request->getPost('numero_de_asistentes_a_capacitacion');
        $numero_total_programados = $this->request->getPost('numero_total_de_personas_programadas');

        // Calcular el porcentaje de cobertura
        $porcentaje_cobertura = ($numero_total_programados > 0)
            ? number_format(($numero_asistentes / $numero_total_programados) * 100, 2)
            : 0;

        $data = [
            'nombre_capacitacion' => $this->request->getPost('nombre_capacitacion'),
            'objetivo_capacitacion' => $this->request->getPost('objetivo_capacitacion'),
            'id_cliente' => $this->request->getPost('id_cliente'),
            'fecha_programada' => $this->request->getPost('fecha_programada'),
            'fecha_de_realizacion' => $this->request->getPost('fecha_de_realizacion'),
            'estado' => $this->request->getPost('estado'),
            'perfil_de_asistentes' => $this->request->getPost('perfil_de_asistentes'),
            'nombre_del_capacitador' => $this->request->getPost('nombre_del_capacitador'),
            'horas_de_duracion_de_la_capacitacion' => $this->request->getPost('horas_de_duracion_de_la_capacitacion'),
            'indicador_de_realizacion_de_la_capacitacion' => $this->request->getPost('indicador_de_realizacion_de_la_capacitacion'),
            'numero_de_asistentes_a_capacitacion' => $numero_asistentes,
            'numero_total_de_personas_programadas' => $numero_total_programados,
            'porcentaje_cobertura' => $porcentaje_cobertura . '%', // Agregar el símbolo de porcentaje
            'numero_de_personas_evaluadas' => $this->request->getPost('numero_de_personas_evaluadas'),
            'promedio_de_calificaciones' => $this->request->getPost('promedio_de_calificaciones'),
            'observaciones' => $this->request->getPost('observaciones'),
        ];

        if ($cronogModel->update($id, $data)) {
            return redirect()->to('/listcronogCapacitacion')->with('msg', 'Cronograma actualizado exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al actualizar cronograma');
        }
    }

    // Eliminar cronograma de capacitación
    public function deletecronogCapacitacion($id)
    {
        $cronogModel = new CronogcapacitacionModel();

        if ($cronogModel->delete($id)) {
            return redirect()->to('/listcronogCapacitacion')->with('msg', 'Cronograma eliminado exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al eliminar el cronograma');
        }
    }

    // Actualizar campos específicos del cronograma de capacitación

    /**
     * Actualiza la fecha programada de una capacitación según el mes seleccionado
     * Calcula el último día del mes automáticamente (considerando años bisiestos)
     */
    public function updateDateByMonth()
    {
        // Permitir tanto AJAX como POST normal para compatibilidad con producción
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Método no permitido']);
        }

        $id = $this->request->getPost('id');
        $month = (int) $this->request->getPost('month');

        if (empty($id) || $month < 1 || $month > 12) {
            return $this->response->setJSON(['success' => false, 'message' => 'Parámetros inválidos']);
        }

        try {
            // Obtener el año actual de la capacitación o usar el año actual
            $model = new CronogcapacitacionModel();
            $training = $model->find($id);

            if (!$training) {
                return $this->response->setJSON(['success' => false, 'message' => 'Capacitación no encontrada']);
            }

            // Determinar el año: usar el de fecha_programada si existe, sino el actual
            $year = date('Y');
            if (!empty($training['fecha_programada'])) {
                $existingDate = new \DateTime($training['fecha_programada']);
                $year = $existingDate->format('Y');
            }

            // Calcular el último día del mes usando DateTime (no requiere extensión calendar)
            $lastDayDate = new \DateTime("$year-$month-01");
            $lastDayDate->modify('last day of this month');
            $lastDay = (int) $lastDayDate->format('d');
            $newDate = sprintf('%04d-%02d-%02d', $year, $month, $lastDay);

            // Actualizar la fecha
            $updateData = [
                'fecha_programada' => $newDate,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($model->update($id, $updateData)) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Fecha actualizada correctamente',
                    'newDate' => $newDate,
                    'formatted' => date('d/m/Y', strtotime($newDate))
                ]);
            } else {
                return $this->response->setJSON(['success' => false, 'message' => 'Error al actualizar']);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Obtiene la lista de clientes en formato JSON para el selector del modal
     */
    public function getClients()
    {
        $db = \Config\Database::connect();

        $clients = $db->table('tbl_clientes')
            ->select('id_cliente, nombre_cliente')
            ->orderBy('nombre_cliente', 'ASC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON($clients);
    }

    /**
     * Obtiene el contrato activo del cliente seleccionado (para AJAX)
     * Prioriza el contrato activo, si no existe, toma el más reciente
     */
    public function getClientContract()
    {
        $idCliente = $this->request->getGet('id_cliente');

        if (empty($idCliente)) {
            return $this->response->setJSON(['success' => false, 'message' => 'ID de cliente requerido']);
        }

        $contractModel = new ContractModel();

        // Primero buscar contrato activo
        $contract = $contractModel->where('id_cliente', $idCliente)
                                  ->where('estado', 'activo')
                                  ->orderBy('fecha_fin', 'DESC')
                                  ->first();

        // Si no hay contrato activo, buscar el más reciente por fecha de creación
        if (!$contract) {
            $contract = $contractModel->where('id_cliente', $idCliente)
                                      ->orderBy('created_at', 'DESC')
                                      ->first();
        }

        if ($contract) {
            return $this->response->setJSON([
                'success' => true,
                'contract' => $contract
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No se encontró contrato para este cliente'
            ]);
        }
    }

    /**
     * Genera automáticamente el cronograma de capacitación desde las plantillas predefinidas
     */
    public function generate()
    {
        // Validar que la petición sea POST
        if (!$this->request->is('post')) {
            return redirect()->to(base_url('/listcronogCapacitacion'))
                ->with('error', 'Método no permitido');
        }

        // Obtener datos del formulario
        $idCliente = $this->request->getPost('id_cliente');
        $serviceType = strtolower($this->request->getPost('service_type'));

        // Validar campos requeridos
        if (empty($idCliente) || empty($serviceType)) {
            return redirect()->to(base_url('/listcronogCapacitacion'))
                ->with('error', 'Todos los campos son obligatorios');
        }

        try {
            // Obtener el nombre del cliente
            $db = \Config\Database::connect();
            $clientQuery = $db->table('tbl_clientes')
                ->select('nombre_cliente')
                ->where('id_cliente', $idCliente)
                ->get();

            $clientData = $clientQuery->getRow();
            $clientName = $clientData ? $clientData->nombre_cliente : "ID: {$idCliente}";

            // Instanciar la librería de capacitaciones
            $trainingLibrary = new \App\Libraries\TrainingLibrary();

            // Obtener las capacitaciones filtradas
            $trainings = $trainingLibrary->getTrainings($idCliente, $serviceType);

            // Validar que se obtuvieron capacitaciones
            if (empty($trainings)) {
                return redirect()->to(base_url('/listcronogCapacitacion'))
                    ->with('warning', 'No se encontraron capacitaciones para el tipo de servicio seleccionado (' . ucfirst($serviceType) . ')');
            }

            // Insertar las capacitaciones en la base de datos
            $cronogModel = new CronogcapacitacionModel();
            $successCount = 0;
            $errorCount = 0;

            foreach ($trainings as $training) {
                if ($cronogModel->insert($training)) {
                    $successCount++;
                } else {
                    $errorCount++;
                }
            }

            // Preparar mensaje de resultado
            $serviceTypeLabel = ucfirst($serviceType);
            $message = "<strong>Cronograma de Capacitación generado exitosamente:</strong><br>";
            $message .= "✓ Cliente: <strong>{$clientName}</strong><br>";
            $message .= "✓ Tipo de Servicio: {$serviceTypeLabel}<br>";
            $message .= "✓ Capacitaciones insertadas: {$successCount}<br>";

            if ($errorCount > 0) {
                $message .= "✗ Capacitaciones con errores: {$errorCount}<br>";
            }

            $flashType = ($errorCount === 0) ? 'success' : 'warning';

            return redirect()->to(base_url('/listcronogCapacitacion'))
                ->with($flashType, $message);

        } catch (\Exception $e) {
            return redirect()->to(base_url('/listcronogCapacitacion'))
                ->with('error', 'Error al generar el cronograma de capacitación: ' . $e->getMessage());
        }
    }
}
