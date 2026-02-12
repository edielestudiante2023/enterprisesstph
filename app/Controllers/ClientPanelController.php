<?php

namespace App\Controllers;

use App\Models\ClientModel;

class ClientPanelController extends BaseController
{
    public function showPanel($idClienteParam = null)
    {
        $session = session();

        // Si viene parámetro y el usuario es consultant/admin, usar ese ID
        $role = $session->get('role');
        if ($idClienteParam && in_array($role, ['consultant', 'admin'])) {
            $id_cliente = $idClienteParam;
        } else {
            $id_cliente = $session->get('user_id');
        }

        if (!$id_cliente) {
            return redirect()->to('/login')->with('error', 'Cliente no autenticado.');
        }

        // Obtener el cliente
        $clientModel = new ClientModel();
        $client = $clientModel->find($id_cliente);

        if (!$client) {
            return redirect()->to('/login')->with('error', 'Cliente no encontrado.');
        }

        // Limpieza y normalización del estándar
        $estandar = trim(strtoupper($client['estandares']));
        
        // Estándares restringidos
        $estandaresRestringidos = ['PROYECTO'];
        $isRestricted = in_array($estandar, $estandaresRestringidos); // Verificar si está restringido

        // Log para depuración
        error_log("Cliente: " . print_r($client, true));
        error_log("Estándar procesado: |$estandar|, ¿Es restringido?: " . ($isRestricted ? 'Sí' : 'No'));

        // Pasar datos básicos a la vista
        return view('client/panel', [
            'client' => $client,
            'isRestricted' => $isRestricted,
            'estandar' => $estandar,
        ]);
    }
}
