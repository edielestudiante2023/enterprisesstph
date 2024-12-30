<?php

namespace App\Controllers;

use App\Models\ClienteReportModel;
use CodeIgniter\Controller;

class ClienteReportController extends Controller
{
    public function index()
    {
        // Obtener el ID del cliente desde la sesión
        $session = session();
        $clientId = $session->get('user_id');

        // Verificar que el ID del cliente se obtenga correctamente
        if (is_null($clientId)) {
            return redirect()->to('/login')->with('error', 'Sesión inválida. Inicia sesión nuevamente.');
        }

        // Instanciar el modelo y recuperar los reportes
        $reportModel = new ClienteReportModel();
        $reports = $reportModel->where('id_cliente', $clientId)->orderBy('created_at', 'DESC')->findAll();

        // Pasar los reportes a la vista
        $data = [
            'reports' => $reports
        ];

        return view('client/report_dashboard', $data);
    }
}
