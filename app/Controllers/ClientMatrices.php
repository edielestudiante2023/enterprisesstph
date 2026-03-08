<?php

namespace App\Controllers;

use App\Models\MatrizModel;
use CodeIgniter\Controller;

class ClientMatrices extends Controller
{
    public function index($idClienteParam = null)
    {
        $session = session();

        // Si viene parámetro y el usuario es consultant/admin, usar ese ID
        $role = $session->get('role');
        if ($idClienteParam && in_array($role, ['consultant', 'admin'])) {
            $clientId = $idClienteParam;
        } else {
            $clientId = $session->get('user_id');
        }

        if (is_null($clientId)) {
            return redirect()->to('/login')->with('error', 'Sesión inválida. Inicia sesión nuevamente.');
        }

        $matrizModel = new MatrizModel();
        $matrices = $matrizModel->where('id_cliente', $clientId)->orderBy('id_matriz', 'DESC')->findAll();

        // Pasar los datos a la vista
        $data = [
            'matrices' => $matrices
        ];

        return view('client/lista_matrices', $data);
    }
}
