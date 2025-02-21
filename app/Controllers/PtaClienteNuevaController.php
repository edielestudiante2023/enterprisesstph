<?php

namespace App\Controllers;

use App\Models\PtaClienteNuevaModel;
use App\Models\ClientModel;
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
        $pager   = null;

        // Solo si se han enviado cliente y rango de fechas se realiza la consulta.
        if (!empty($cliente) && !empty($fecha_desde) && !empty($fecha_hasta)) {
            $ptaModel = new PtaClienteNuevaModel();
            $ptaModel->where('id_cliente', $cliente);
            $ptaModel->where('fecha_propuesta >=', $fecha_desde);
            $ptaModel->where('fecha_propuesta <=', $fecha_hasta);
            if (!empty($estado)) {
                $ptaModel->where('estado_actividad', $estado);
            }
            $limit   = 50;
            $records = $ptaModel->paginate($limit, 'pta_cliente_nueva');
            $pager   = $ptaModel->pager;

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

        $data = [
            'clients' => $clients,
            'records' => $records,
            'pager'   => $pager,
            'filters' => $filters,
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

        // Recoger datos del formulario
        $data = $this->request->getPost();
        $ptaModel->update($id, $data);

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
        $ptaModel->update($id, $postData);
        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Registro actualizado inline correctamente.'
        ]);
    }

    /**
     * Exporta los registros filtrados a un archivo Excel (en este caso, CSV con encabezados para Excel).
     */
    public function exportExcelPtaClienteNuevaModel()
    {
        $clientModel = new ClientModel();
        $clients = $clientModel->findAll();
        $filters = $this->request->getGet();
        $ptaModel = new PtaClienteNuevaModel();
        if (!empty($filters['cliente']) && !empty($filters['fecha_desde']) && !empty($filters['fecha_hasta'])) {
            $ptaModel->where('id_cliente', $filters['cliente']);
            $ptaModel->where('fecha_propuesta >=', $filters['fecha_desde']);
            $ptaModel->where('fecha_propuesta <=', $filters['fecha_hasta']);
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
}
