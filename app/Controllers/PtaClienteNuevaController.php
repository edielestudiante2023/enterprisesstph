<?php

namespace App\Controllers;

use App\Models\PtaClienteNuevaModel;
use App\Models\ClientModel;
use App\Models\ContractModel;
use App\Services\PtaAuditService;
use App\Services\PtaTransicionesService;
use CodeIgniter\Controller;

class PtaClienteNuevaController extends Controller
{

    public function listPtaClienteNuevaModel()
    {
        $clientModel = new ClientModel();
        $clients     = $clientModel->findAll();

        $request     = service('request');
        $cliente     = $request->getGet('cliente');
        $fecha_desde = $request->getGet('fecha_desde');
        $fecha_hasta = $request->getGet('fecha_hasta');
        $estado      = $request->getGet('estado');

        $records = null;

        // Si se ha enviado al menos un cliente, realizar la consulta
        if (!empty($cliente)) {
            $ptaModel = new PtaClienteNuevaModel();

            // Si tiene fechas específicas, usar rango de fechas
            if (!empty($fecha_desde) && !empty($fecha_hasta)) {
                // Primero verificar si hay datos en un rango más amplio
                $extendedStart = date('Y-m-d', strtotime($fecha_desde . ' -30 days'));
                $extendedEnd = date('Y-m-d', strtotime($fecha_hasta . ' +30 days'));

                $checkExtended = $ptaModel->where('id_cliente', $cliente)
                                        ->where('fecha_propuesta >=', $extendedStart)
                                        ->where('fecha_propuesta <=', $extendedEnd)
                                        ->countAllResults(false);

                // Realizar la consulta con el rango original
                $ptaModel->where('id_cliente', $cliente);
                $ptaModel->where('fecha_propuesta >=', $fecha_desde);
                $ptaModel->where('fecha_propuesta <=', $fecha_hasta);
            } else {
                // Sin fechas: mostrar TODOS los registros del cliente
                $ptaModel->where('id_cliente', $cliente);
                $checkExtended = 0; // No aplicable en este caso
            }

            // Aplicar filtro de estado si se proporciona
            if (!empty($estado)) {
                $ptaModel->where('estado_actividad', $estado);
            }

            // Obtener TODOS los registros sin paginación - DataTables manejará la paginación en el cliente
            $records = $ptaModel->findAll();

            // Mensajes según el resultado
            if (!empty($fecha_desde) && !empty($fecha_hasta)) {
                // Si no hay registros en el rango actual pero sí en el rango extendido
                if (empty($records) && $checkExtended > 0) {
                    session()->setFlashdata('warning', 'No se encontraron registros en el rango de fechas seleccionado. Intente ampliar el rango de fechas para ver más resultados.');
                } 
                // Si no hay registros en ningún rango
                elseif (empty($records)) {
                    session()->setFlashdata('info', 'No se encontraron registros, por defecto prueba con rango 1 ene a 31 dic. Si en definitiva no cargan datos Por favor, comuníquese con su backoffice para verificar la información.');
                }
            } else {
                // Sin fechas: mensaje diferente si no hay registros
                if (empty($records)) {
                    session()->setFlashdata('info', 'No se encontraron registros para este cliente. Por favor, comuníquese con su backoffice para verificar la información.');
                } else {
                    session()->setFlashdata('success', 'Mostrando todos los registros del cliente seleccionado (' . count($records) . ' registros encontrados).');
                }
            }

            // Mapear el nombre del cliente a cada registro
            $clientsArray = [];
            foreach ($clients as $clientData) {
                $clientsArray[$clientData['id_cliente']] = $clientData['nombre_cliente'];
            }
            foreach ($records as &$record) {
                $idCliente = $record['id_cliente'];
                $record['nombre_cliente'] = isset($clientsArray[$idCliente]) ? $clientsArray[$idCliente] : 'N/A';
            }
        }

        $filters = [
            'cliente'     => $cliente,
            'fecha_desde' => $fecha_desde,
            'fecha_hasta' => $fecha_hasta,
            'estado'      => $estado,
        ];

        // Obtener el contrato activo del cliente seleccionado
        $lastContract = null;
        $selectedClient = null;
        if (!empty($cliente)) {
            $contractModel = new ContractModel();
            // Priorizar contrato activo
            $lastContract = $contractModel->where('id_cliente', $cliente)
                                          ->where('estado', 'activo')
                                          ->orderBy('fecha_fin', 'DESC')
                                          ->first();

            // Si no hay contrato activo, buscar el más reciente por fecha de creación
            if (!$lastContract) {
                $lastContract = $contractModel->where('id_cliente', $cliente)
                                              ->orderBy('created_at', 'DESC')
                                              ->first();
            }

            // Obtener información del cliente seleccionado
            foreach ($clients as $c) {
                if ($c['id_cliente'] == $cliente) {
                    $selectedClient = $c;
                    break;
                }
            }
        }

        $data = [
            'clients' => $clients,
            'records' => $records,
            'filters' => $filters,
            'lastContract' => $lastContract,
            'selectedClient' => $selectedClient,
        ];

        return view('consultant/list_pta_cliente_nueva', $data);
    }

    /**
     * Muestra el formulario para agregar un nuevo registro.
     */
    public function addPtaClienteNuevaModel()
    {
        $clientModel = new ClientModel();
        $clients     = $clientModel->findAll();
        // Obtener filtros desde GET para pasarlos a la vista
        $filters = $this->request->getGet();

        $data = [
            'clients' => $clients,
            'filters' => $filters,
        ];
        return view('consultant/add_pta_cliente_nueva', $data);
    }

    /**
     * Procesa el formulario para agregar un nuevo registro.
     */
    public function addpostPtaClienteNuevaModel()
    {
        $ptaModel = new PtaClienteNuevaModel();
        $data = $this->request->getPost();
        $ptaModel->insert($data);

        // Obtener el ID del registro insertado
        $insertId = $ptaModel->getInsertID();

        // Registrar auditoría de creación
        PtaAuditService::logInsert($insertId, $data, __METHOD__);

        // Recuperar filtros enviados desde el formulario (campos ocultos)
        $filters = [
            'cliente'     => $this->request->getPost('filter_cliente'),
            'fecha_desde' => $this->request->getPost('filter_fecha_desde'),
            'fecha_hasta' => $this->request->getPost('filter_fecha_hasta'),
            'estado'      => $this->request->getPost('filter_estado'),
        ];

        return redirect()->to('/pta-cliente-nueva/list?' . http_build_query($filters))
            ->with('message', 'Registro agregado correctamente.');
    }

    /**
     * Muestra el formulario para editar un registro.
     */
    public function editPtaClienteNuevaModel($id = null)
    {
        $ptaModel    = new PtaClienteNuevaModel();
        $clientModel = new ClientModel();

        $record = $ptaModel->find($id);
        if (!$record) {
            return redirect()->back()->with('error', 'Registro no encontrado.');
        }

        $clients = $clientModel->findAll();
        // Obtener filtros desde GET
        $filters = service('request')->getGet();

        $data = [
            'record'  => $record,
            'clients' => $clients,
            'filters' => $filters,
        ];
        return view('consultant/edit_pta_cliente_nueva', $data);
    }

    /**
     * Procesa el formulario para editar un registro.
     */
    public function editpostPtaClienteNuevaModel($id = null)
    {
        $ptaModel = new PtaClienteNuevaModel();

        // Obtener datos anteriores para auditoría
        $datosAnteriores = $ptaModel->find($id);

        // Recoger datos del formulario
        $data = $this->request->getPost();
        $ptaModel->update($id, $data);

        // Registrar auditoría de múltiples cambios
        PtaAuditService::logMultiple(
            $id,
            $datosAnteriores,
            $data,
            __METHOD__,
            $datosAnteriores['id_cliente'] ?? null
        );

        // Registrar transición si el estado cambió desde ABIERTA
        if (isset($data['estado_actividad']) && ($datosAnteriores['estado_actividad'] ?? '') !== $data['estado_actividad']) {
            PtaTransicionesService::registrar(
                $id,
                (int) ($datosAnteriores['id_cliente'] ?? 0),
                $datosAnteriores['estado_actividad'] ?? '',
                $data['estado_actividad']
            );
        }

        // Recuperar filtros enviados desde campos ocultos
        $filters = [
            'cliente'     => $this->request->getPost('filter_cliente'),
            'fecha_desde' => $this->request->getPost('filter_fecha_desde'),
            'fecha_hasta' => $this->request->getPost('filter_fecha_hasta'),
            'estado'      => $this->request->getPost('filter_estado'),
        ];

        return redirect()->to('/pta-cliente-nueva/list?' . http_build_query($filters))
            ->with('message', 'Registro actualizado correctamente.');
    }

    /**
     * Elimina un registro.
     */
    public function deletePtaClienteNuevaModel($id = null)
    {
        // Verificar que se pase un ID válido
        if (empty($id) || $id == 0) {
            return redirect()->to('/pta-cliente-nueva/list')
                ->with('error', 'ID no válido para eliminar.');
        }

        $ptaModel = new PtaClienteNuevaModel();

        // Obtener datos antes de eliminar para auditoría
        $datosAnteriores = $ptaModel->find($id);

        // Registrar auditoría de eliminación ANTES de eliminar
        if ($datosAnteriores) {
            PtaAuditService::logDelete($id, $datosAnteriores, __METHOD__);
        }

        $ptaModel->where('id_ptacliente', $id)->delete();

        // Recuperar filtros desde GET para mantenerlos
        $filters = $this->request->getGet();

        return redirect()->to('/pta-cliente-nueva/list?' . http_build_query($filters))
            ->with('message', 'Registro eliminado correctamente.');
    }

    /**
     * Actualiza un registro mediante edición inline.
     * Se permiten editar todas las columnas excepto:
     *  - id_ptacliente
     *  - responsable_definido_paralaactividad
     *  - semana
     *  - created_at
     *  - updated_at
     *
     * Se espera recibir vía POST el ID y el campo modificado.
     */
    public function editinginlinePtaClienteNuevaModel()
    {
        $ptaModel = new PtaClienteNuevaModel();
        $id = $this->request->getPost('id');
        if (!$id) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'ID es requerido.'
            ]);
        }

        // Obtener datos anteriores para auditoría
        $datosAnteriores = $ptaModel->find($id);

        $postData = $this->request->getPost();
        $disallowed = [
            'id_ptacliente',
            'responsable_definido_paralaactividad',
            'semana',
            'created_at',
            'updated_at'
        ];
        foreach ($disallowed as $field) {
            if (isset($postData[$field])) {
                unset($postData[$field]);
            }
        }

        // Auto-calcular porcentaje basado en el estado
        if (isset($postData['estado_actividad'])) {
            $estado = $postData['estado_actividad'];
            switch ($estado) {
                case 'CERRADA':
                    $postData['porcentaje_avance'] = 100;
                    break;
                case 'GESTIONANDO':
                    $postData['porcentaje_avance'] = 50;
                    break;
                case 'ABIERTA':
                    $postData['porcentaje_avance'] = 0;
                    break;
            }
        }

        $ptaModel->update($id, $postData);

        // Registrar auditoría de cambios inline
        PtaAuditService::logMultiple(
            $id,
            $datosAnteriores,
            $postData,
            __METHOD__,
            $datosAnteriores['id_cliente'] ?? null
        );

        // Registrar transición si el estado cambió desde ABIERTA
        if (isset($postData['estado_actividad']) && ($datosAnteriores['estado_actividad'] ?? '') !== $postData['estado_actividad']) {
            PtaTransicionesService::registrar(
                (int) $id,
                (int) ($datosAnteriores['id_cliente'] ?? 0),
                $datosAnteriores['estado_actividad'] ?? '',
                $postData['estado_actividad']
            );
        }

        // Retornar también el porcentaje actualizado para actualizar la vista
        $response = [
            'status'  => 'success',
            'message' => 'Registro actualizado inline correctamente.'
        ];

        if (isset($postData['porcentaje_avance'])) {
            $response['porcentaje_avance'] = $postData['porcentaje_avance'];
        }

        return $this->response->setJSON($response);
    }

    /**
     * Actualiza el porcentaje de avance a 100 para registros cerrados
     */
    public function updateCerradas()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request method']);
        }

        $ids = $this->request->getPost('ids');
        if (empty($ids)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No IDs provided']);
        }

        $ptaModel = new PtaClienteNuevaModel();
        $data = ['porcentaje_avance' => 100];

        try {
            foreach ($ids as $id) {
                // Obtener valor anterior para auditoría
                $registro = $ptaModel->find($id);
                $valorAnterior = $registro['porcentaje_avance'] ?? null;

                $ptaModel->update($id, $data);

                // Registrar auditoría
                PtaAuditService::log(
                    $id,
                    'BULK_UPDATE',
                    'porcentaje_avance',
                    $valorAnterior,
                    100,
                    __METHOD__,
                    $registro['id_cliente'] ?? null
                );
            }
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Todos los cerrados quedaron calificados con 100'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error updating records: ' . $e->getMessage()
            ]);
        }
    }

    public function exportExcelPtaClienteNuevaModel()
    {
        $clientModel = new ClientModel();
        $clients = $clientModel->findAll();
        $filters = $this->request->getGet();
        $ptaModel = new PtaClienteNuevaModel();

        // Aplicar los mismos filtros que en listPtaClienteNuevaModel
        if (!empty($filters['cliente'])) {
            $ptaModel->where('id_cliente', $filters['cliente']);

            // Si tiene fechas específicas, usar rango de fechas
            if (!empty($filters['fecha_desde']) && !empty($filters['fecha_hasta'])) {
                $ptaModel->where('fecha_propuesta >=', $filters['fecha_desde']);
                $ptaModel->where('fecha_propuesta <=', $filters['fecha_hasta']);
            }

            // Aplicar filtro de estado si se proporciona
            if (!empty($filters['estado'])) {
                $ptaModel->where('estado_actividad', $filters['estado']);
            }
        }

        $records = $ptaModel->findAll();

        // Mapear el nombre del cliente
        $clientsArray = [];
        foreach ($clients as $clientData) {
            $clientsArray[$clientData['id_cliente']] = $clientData['nombre_cliente'];
        }
        foreach ($records as &$record) {
            $idCliente = $record['id_cliente'];
            $record['nombre_cliente'] = isset($clientsArray[$idCliente]) ? $clientsArray[$idCliente] : 'N/A';
        }

        // Preparar la descarga como Excel (CSV)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="pta_cliente_nueva.xls"');
        header('Cache-Control: max-age=0');

        $output = fopen('php://output', 'w');
        // Encabezado (omitiendo columnas ocultas y "Tipo Servicio")
        $header = ['ID', 'Cliente', 'PHVA', 'Numeral Plan Trabajo', 'Actividad', 'Responsable Sugerido', 'Fecha Propuesta', 'Fecha Cierre', 'Estado Actividad', 'Porcentaje Avance', 'Observaciones'];
        fputcsv($output, $header, "\t");
        foreach ($records as $row) {
            $data = [
                $row['id_ptacliente'],
                $row['nombre_cliente'],
                $row['phva_plandetrabajo'],
                $row['numeral_plandetrabajo'],
                $row['actividad_plandetrabajo'],
                $row['responsable_sugerido_plandetrabajo'],
                $row['fecha_propuesta'],
                $row['fecha_cierre'],
                $row['estado_actividad'],
                $row['porcentaje_avance'],
                $row['observaciones']
            ];
            fputcsv($output, $data, "\t");
        }
        fclose($output);
        exit;
    }

    /**
     * Actualiza la fecha propuesta de una actividad según el mes seleccionado
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
            // Obtener el año actual de la actividad o usar el año actual
            $model = new \App\Models\PtaClienteNuevaModel();
            $activity = $model->find($id);

            if (!$activity) {
                return $this->response->setJSON(['success' => false, 'message' => 'Actividad no encontrada']);
            }

            // Determinar el año: usar el de fecha_propuesta si existe, sino el actual
            $year = date('Y');
            if (!empty($activity['fecha_propuesta'])) {
                $existingDate = new \DateTime($activity['fecha_propuesta']);
                $year = $existingDate->format('Y');
            }

            // Calcular el último día del mes usando DateTime (no requiere extensión calendar)
            $lastDayDate = new \DateTime("$year-$month-01");
            $lastDayDate->modify('last day of this month');
            $lastDay = (int) $lastDayDate->format('d');
            $newDate = sprintf('%04d-%02d-%02d', $year, $month, $lastDay);

            // Obtener valor anterior para auditoría
            $valorAnterior = $activity['fecha_propuesta'] ?? null;

            // Actualizar la fecha
            $updateData = [
                'fecha_propuesta' => $newDate,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($model->update($id, $updateData)) {
                // Registrar auditoría
                PtaAuditService::log(
                    $id,
                    'UPDATE',
                    'fecha_propuesta',
                    $valorAnterior,
                    $newDate,
                    __METHOD__,
                    $activity['id_cliente'] ?? null
                );

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
}
