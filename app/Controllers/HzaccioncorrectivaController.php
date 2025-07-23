<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ClientPoliciesModel; // Usaremos este modelo para client_policies
use App\Models\DocumentVersionModel; // Usaremos este modelo para client_policies
use App\Models\PolicyTypeModel; // Usaremos este modelo para client_policies

use Dompdf\Dompdf;

use CodeIgniter\Controller;

class HzaccioncorrectivaController extends Controller
{



    public function accionCorrectiva()
    {
        // Obtener el ID del cliente desde la sesión
        $session = session();
        $clientId = $session->get('user_id'); // Asegúrate de que este ID es el del cliente

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $clientPoliciesModel = new ClientPoliciesModel();
        $policyTypeModel = new PolicyTypeModel();
        $versionModel = new DocumentVersionModel();

        // Obtener los datos del cliente
        $client = $clientModel->find($clientId);
        if (!$client) {
            return redirect()->to('/dashboardclient')->with('error', 'No se pudo encontrar la información del cliente');
        }

        // Obtener los datos del consultor relacionado con el cliente
        $consultant = $consultantModel->find($client['id_consultor']);
        if (!$consultant) {
            return redirect()->to('/dashboardclient')->with('error', 'No se pudo encontrar la información del consultor');
        }

        // Obtener la política de alcohol y drogas del cliente
        $policyTypeId = 41; // Supongamos que el ID de la política de alcohol y drogas es 1
        $clientPolicy = $clientPoliciesModel->where('client_id', $clientId)
            ->where('policy_type_id', $policyTypeId)
            ->orderBy('id', 'DESC')
            ->first();
        if (!$clientPolicy) {
            return redirect()->to('/dashboardclient')->with('error', 'No se encontró este documento para este cliente.');
        }

        // Obtener el tipo de política
        $policyType = $policyTypeModel->find($policyTypeId);

        // Obtener la versión más reciente del documento
        $latestVersion = $versionModel->where('client_id', $clientId)
            ->where('policy_type_id', $policyTypeId)
            ->orderBy('created_at', 'DESC')
            ->first();

        if (!$latestVersion) {
            return redirect()->to('/dashboardclient')->with('error', 'No se encontró un versionamiento para este documento de este cliente.');
        }

        // Obtener todas las versiones del documento
        $allVersions = $versionModel->where('client_id', $clientId)
            ->where('policy_type_id', $policyTypeId)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        if (!$allVersions) {
            return redirect()->to('/dashboardclient')->with('error', 'No se encontró un versionamiento para este documento de este cliente.');
        }


        // Pasar los datos a la vista
        $data = [
            'client' => $client,
            'consultant' => $consultant,
            'clientPolicy' => $clientPolicy,
            'policyType' => $policyType,
            'latestVersion' => $latestVersion,
            'allVersions' => $allVersions,  // Pasamos todas las versiones al footer
        ];

        return view('client/sgsst/1planear/h2_1_2accioncorrectiva', $data);
    }

   
}
