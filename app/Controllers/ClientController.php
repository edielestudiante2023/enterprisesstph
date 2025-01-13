<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\AccesoModel;
use App\Models\EstandarModel;
use App\Models\EstandarAccesoModel;
use CodeIgniter\Controller;
use App\Models\ReporteModel;

class ClientController extends Controller
{
    public function index()
    {
        $session = session();
        $clientId = $session->get('user_id');

        $model = new ClientModel();
        $client = $model->find($clientId);

        if (!$client) {
            return redirect()->to('/login')->with('error', 'Cliente no encontrado');
        }

        $data = [
            'client' => $client
        ];

        return view('client/dashboard', $data);
    }

    public function dashboard()
    {
        try {
            $session = session();

            // Obtener el ID del cliente desde la sesión
            $id_cliente = $session->get('user_id');
            if (!$id_cliente) {
                return redirect()->to('/login')->with('error', 'Cliente no autenticado.');
            }

            // Obtener el cliente
            $clientModel = new ClientModel();
            $client = $clientModel->find($id_cliente);
            if (!$client) {
                return redirect()->to('/login')->with('error', 'Cliente no encontrado.');
            }

            // Inicializar $accesos como un array vacío
            $accesos = [];

            // Obtener el estándar del cliente (por ejemplo '7A')
            $estandarNombre = $client['estandares'];

            // Instanciar el modelo de estandares y obtener el ID del estándar (por ejemplo 1 para '7A')
            $estandarModel = new EstandarModel();
            $estandar = $estandarModel->where('nombre', $estandarNombre)->first();

            if (!$estandar) {
                return redirect()->to('/login')->with('error', 'Estándar no encontrado.');
            }

            $id_estandar = $estandar['id_estandar'];  // Esto nos da el ID numérico del estándar

            // Obtener los accesos permitidos para el estándar usando el modelo EstandarAccesoModel
            $estandarAccesoModel = new EstandarAccesoModel();
            $accesosData = $estandarAccesoModel->where('id_estandar', $id_estandar)->findAll();

            // Si no hay accesos asociados al estándar
            if (empty($accesosData)) {
                echo "No hay accesos disponibles para el estándar $estandarNombre.";
                exit;
            }

            // Instanciar el modelo de accesos para obtener los detalles de cada acceso ordenado por la dimensión
            $accesoModel = new AccesoModel();

            // Obtener los accesos permitidos para el estándar usando el modelo EstandarAccesoModel
            $estandarAccesoModel = new EstandarAccesoModel();
            $accesosData = $estandarAccesoModel->where('id_estandar', $id_estandar)->findAll();

            // Obtener todos los accesos relacionados con el estándar y ordenarlos por la dimensión
            $accesos = $accesoModel
                ->whereIn('id_acceso', array_column($accesosData, 'id_acceso'))
                ->findAll();

            // Ordenar en PHP usando el ciclo PHVA
            $orden = ["Planear", "Hacer", "Verificar", "Actuar", "Indicadores"];

            usort($accesos, function ($a, $b) use ($orden) {
                return array_search($a['dimension'], $orden) - array_search($b['dimension'], $orden);
            });


            // Pasar los accesos a la vista `dashboardclient`
            return view('client/dashboard', [
                'accesos' => $accesos,
                'client' => $client
            ]);
        } catch (\Exception $e) {
            echo "Ocurrió un error: " . $e->getMessage();
            exit;
        }
    }

    private function getReportsForType($reportModel, $clientId, $reportTypeId)
    {
        return $reportModel
            ->select('
                tbl_reporte.id_reporte,
                tbl_reporte.titulo_reporte,
                tbl_reporte.enlace,
                tbl_reporte.estado,
                tbl_reporte.observaciones,
                tbl_reporte.created_at,
                tbl_reporte.updated_at,
                detail_report.detail_report AS detalle_reporte,
                report_type_table.report_type AS tipo_reporte,
                tbl_clientes.nombre_cliente AS cliente_nombre
            ')
            ->join('detail_report', 'detail_report.id_detailreport = tbl_reporte.id_detailreport', 'left')
            ->join('report_type_table', 'report_type_table.id_report_type = tbl_reporte.id_report_type', 'left')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_reporte.id_cliente', 'left')
            ->where('tbl_reporte.id_cliente', $clientId)
            ->where('tbl_reporte.id_report_type', $reportTypeId)
            ->orderBy('tbl_reporte.created_at', 'DESC')
            ->findAll();
    }

    public function viewDocuments()
    {
        $reportModel = new ReporteModel();

        // Obtener el ID del cliente desde la sesión
        $clientId = session()->get('user_id');

        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Sesión no válida.');
        }

        // Mapeo de claves con sus respectivos ID de reporte
        $reportTypes = [
            'inspecciones'      => 1,
            'reportes'          => 2,
            'aseo'              => 3,
            'vigilancia'        => 4,
            'ambiental'         => 5,
            'actasdevisita'     => 6,
            'capacitaciones'    => 7,
            'cincuentahoras'    => 8,
            'reporteministerio' => 9,
            'cierredemes'       => 10,
            'emergencias'       => 11,
            'otrosproveedores'  => 12,
            'secretariasalud'   => 13,
            'lavadotanques'     => 14,
            'localescomerciales' => 15,
            'fumigaciones'      => 16,
            'normatividad'      => 17,
            'contrato'          => 19,
        ];

        $data = [];

        // Iterar sobre cada tipo de reporte y obtener los datos correspondientes
        foreach ($reportTypes as $key => $typeId) {
            $data[$key] = $this->getReportsForType($reportModel, $clientId, $typeId);
        }

        return view('client/document_view', $data);
    }
}
