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
                ->whereIn('id_acceso', array_column($accesosData, 'id_acceso'))  // Obtener todos los accesos permitidos
                ->orderBy('FIELD(dimension, "Planear", "Hacer", "Verificar", "Actuar", "Indicadores")', '', false)  // Ordenar por dimensión
                ->findAll();

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


    public function documento()
    {
        return view('client/documento');
    }
    // Dentro del controlador ClientController o el que uses para los clientes

    public function viewDocuments()
    {
        $reportModel = new ReporteModel();

        // Obtener el ID del cliente desde la sesión
        $clientId = session()->get('user_id'); // Asegúrate de que 'user_id' almacene el ID del cliente

        // Filtrar los documentos por el ID del cliente
        $hojasDeCalculo = $reportModel->where('id_cliente', $clientId)
            ->where('id_report_type', 1) // ID para 'Hojas de cálculo interactivas'
            ->findAll();

        $matrices = $reportModel->where('id_cliente', $clientId)
            ->where('id_report_type', 2) // ID para 'Matrices'
            ->findAll();

        $data = [
            'hojasDeCalculo' => $hojasDeCalculo,
            'matrices' => $matrices
        ];

        return view('client/document_view', $data);
    }

    
    

    

}

