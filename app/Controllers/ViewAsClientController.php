<?php

namespace App\Controllers;

use App\Models\ClientModel;
use CodeIgniter\Controller;

class ViewAsClientController extends Controller
{
    /**
     * Muestra la página con Select2 para elegir un cliente
     * Solo accesible por consultant y admin
     */
    public function index()
    {
        $session = session();
        $role = $session->get('role');

        if (!in_array($role, ['consultant', 'admin'])) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        $clientModel = new ClientModel();
        $clients = $clientModel->where('estado', 'activo')->orderBy('nombre_cliente', 'ASC')->findAll();

        return view('consultant/view_as_client', [
            'clients' => $clients,
            'role' => $role,
            'nombreUsuario' => $session->get('nombre_usuario') ?? 'Usuario'
        ]);
    }

    /**
     * Muestra el dashboard del cliente seleccionado
     * Carga la misma vista que ve el cliente pero con el ID proporcionado
     */
    public function viewClient($idCliente)
    {
        $session = session();
        $role = $session->get('role');

        if (!in_array($role, ['consultant', 'admin'])) {
            return redirect()->to('/login')->with('error', 'Acceso no autorizado.');
        }

        helper('access_library');

        $clientModel = new ClientModel();
        $client = $clientModel->find($idCliente);

        if (!$client) {
            return redirect()->to('/vista-cliente')->with('error', 'Cliente no encontrado.');
        }

        // Obtener el estándar del cliente
        $estandarNombre = $client['estandares'];

        // Obtener accesos desde AccessLibrary
        $accesos = get_accesses_by_standard($estandarNombre);

        if (!empty($accesos)) {
            // Ordenar por dimensión PHVA
            $orden = ["Planear", "Hacer", "Verificar", "Actuar", "Indicadores"];

            usort($accesos, function ($a, $b) use ($orden) {
                return array_search($a['dimension'], $orden) - array_search($b['dimension'], $orden);
            });

            // Reemplazar el placeholder /1 en las URLs con el ID real del cliente
            foreach ($accesos as &$acceso) {
                $acceso['url'] = preg_replace('/\/1$/', '/' . $idCliente, $acceso['url']);
            }
            unset($acceso);
        } else {
            $accesos = [];
        }

        return view('client/dashboard', [
            'accesos' => $accesos,
            'client' => $client
        ]);
    }
}
