<?php

namespace App\Controllers;

use App\Models\LookerStudioModel;
use CodeIgniter\Controller;

class ClientLookerStudioController extends Controller
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

        $lookerStudioModel = new LookerStudioModel();
        $lookerStudios = $lookerStudioModel->where('id_cliente', $clientId)->orderBy('id_looker', 'DESC')->findAll();

        // Pasar los datos a la vista
        $data = [
            'lookerStudios' => $lookerStudios
        ];

        return view('client/lista_lookerstudio', $data);
    }
}
