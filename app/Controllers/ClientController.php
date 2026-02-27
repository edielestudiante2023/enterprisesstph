<?php

namespace App\Controllers;

use App\Models\ClientModel;
// Ya no usamos AccesoModel, EstandarModel, EstandarAccesoModel (migrado a AccessLibrary.php)
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
            // Cargar helper para acceso a AccessLibrary
            helper('access_library');

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

            // Obtener el estándar del cliente (Mensual, Bimensual, Trimestral, Proyecto)
            $estandarNombre = $client['estandares'];

            // Obtener accesos desde AccessLibrary (librería PHP estática)
            // Ya no consultamos BD (accesos, estandares, estandares_accesos)
            $accesos = get_accesses_by_standard($estandarNombre);

            // Si no hay accesos definidos para este estándar
            if (empty($accesos)) {
                // Pasar array vacío a la vista para que muestre el mensaje
                $accesos = [];
            } else {
                // Ordenar por dimensión PHVA
                $orden = ["Planear", "Hacer", "Verificar", "Actuar", "Indicadores"];

                usort($accesos, function ($a, $b) use ($orden) {
                    return array_search($a['dimension'], $orden) - array_search($b['dimension'], $orden);
                });
            }

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

    public function viewDocuments($clientIdParam = null)
    {
        $reportModel = new ReporteModel();

        // 1) Obtener el ID del cliente: parámetro (para consultores) o sesión (para clientes)
        $session = session();
        $role = $session->get('role');

        if ($clientIdParam && in_array($role, ['consultant', 'admin'])) {
            $clientId = $clientIdParam;
        } else {
            $clientId = $session->get('user_id');
        }

        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Sesión no válida.');
        }

        // 2) Mapeo de claves ⇒ ID de reporte
        $reportTypes = [
            'inspecciones'       => 1,
            'reportes'           => 2,
            'aseo'               => 3,
            'vigilancia'         => 4,
            'ambiental'          => 5,
            'actasdevisita'      => 6,
            'capacitaciones'     => 7,
            'cincuentahoras'     => 8,
            'reporteministerio'  => 9,
            'cierredemes'        => 10,
            'emergencias'        => 11,
            'otrosproveedores'   => 12,
            'secretariasalud'    => 13,
            'lavadotanques'      => 14,
            'localescomerciales' => 15,
            'fumigaciones'       => 16,
            'normatividad'       => 17,
            'contrato'           => 19,
            'saneamiento'        => 20,
            'consultor'          => 21,
        ];

        // 3) Mapeo de claves ⇒ títulos para el menú (topicsList)
        $topicsList = [
            'inspecciones'       => 'Inspecciones',
            'reportes'           => 'Reportes Generales',
            'aseo'               => 'Servicios de Aseo',
            'vigilancia'         => 'Vigilancia',
            'ambiental'          => 'Plan de Gestión Ambiental',
            'actasdevisita'      => 'Actas de Visita SST',
            'capacitaciones'     => 'Capacitaciones en SST',
            'cincuentahoras'     => 'Programa 50 Horas',
            'reporteministerio'  => 'Reportes Ministerio',
            'cierredemes'        => 'Cierre de Meses',
            'emergencias'        => 'Protocolos de Emergencia',
            'otrosproveedores'   => 'Otros Proveedores',
            'secretariasalud'    => 'Secretaría de Salud',
            'lavadotanques'      => 'Lavado de Tanques',
            'localescomerciales' => 'Locales Comerciales',
            'fumigaciones'       => 'Fumigaciones',
            'normatividad'       => 'Documentación Normativa',
            'contrato'           => 'Contratos',
            'saneamiento'        => 'Saneamiento Básico',
            'consultor'          => 'Informes de Consultor',
        ];

        // 4) Inicializar data con topicsList
        $data = [
            'topicsList' => $topicsList
        ];

        // 5) Cargar los reportes en cada key
        foreach ($reportTypes as $key => $typeId) {
            $data[$key] = $this->getReportsForType($reportModel, $clientId, $typeId);
        }

        // 6) Enviar todo a la vista
        return view('client/document_view', $data);
    }
}
