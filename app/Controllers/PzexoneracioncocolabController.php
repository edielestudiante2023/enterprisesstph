<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ContractModel;
use App\Models\ClientPoliciesModel; // Usaremos este modelo para client_policies
use App\Models\DocumentVersionModel; // Usaremos este modelo para client_policies
use App\Models\PolicyTypeModel;
use CodeIgniter\I18n\Time; // Usaremos este modelo para client_policies

use Dompdf\Dompdf;
use Dompdf\Options;

use CodeIgniter\Controller;

class PzexoneracioncocolabController extends Controller
{



    public function exoneracionCocolab($urlClientId = null)
    {
        // Obtener el ID del cliente desde la sesión
        $session = session();
        $clientId = getEffectiveClientId($urlClientId);

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
        $policyTypeId = 6; // Supongamos que el ID de la política de alcohol y drogas es 1
        $clientPolicy = $clientPoliciesModel->where('client_id', $clientId)
            ->where('policy_type_id', $policyTypeId)
            ->orderBy('id', 'DESC')
            ->first();
        if (!$clientPolicy) {
            return redirect()->to('/dashboardclient')->with('error', 'No se encontró este documento para este cliente.');
        }

        // Obtener el tipo de política
        $policyType = $policyTypeModel->find($policyTypeId);

        // Obtener la fecha del primer contrato del cliente
        $contractModel = new ContractModel();
        $firstContractDate = $contractModel->getFirstContractDate($clientId);


        // Obtener la versión más reciente del documento
        $latestVersion = $versionModel->where('client_id', $clientId)
            ->where('policy_type_id', $policyTypeId)
            ->orderBy('created_at', 'DESC')
            ->first();

        if (!$latestVersion) {
            return redirect()->to('/dashboardclient')->with('error', 'No se encontró un versionamiento para este documento de este cliente.');
        }

        // Sobrescribir la fecha con la del primer contrato (o mostrar pendiente si no hay)
        if ($firstContractDate) {
            $latestVersion['created_at'] = $firstContractDate;
        } else {
            // Cliente sin contrato: mostrar "PENDIENTE DE CONTRATO"
            $latestVersion['created_at'] = null;
            $latestVersion['sin_contrato'] = true;
        }

        // Obtener todas las versiones del documento
        $allVersions = $versionModel->where('client_id', $clientId)
            ->where('policy_type_id', $policyTypeId)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        if (!$allVersions) {
            return redirect()->to('/dashboardclient')->with('error', 'No se encontró un versionamiento para este documento de este cliente.');
        }

        // Sobrescribir las fechas de todas las versiones con la del primer contrato
        foreach ($allVersions as &$version) {
            if ($firstContractDate) {
                $version['created_at'] = $firstContractDate;
            } else {
                $version['created_at'] = null;
                $version['sin_contrato'] = true;
            }
        }
        unset($version); // Romper la referencia


        // Pasar los datos a la vista
        $data = [
            'client' => $client,
            'consultant' => $consultant,
            'clientPolicy' => $clientPolicy,
            'policyType' => $policyType,
            'latestVersion' => $latestVersion,
            'allVersions' => $allVersions,  // Pasamos todas las versiones al footer
        ];

        return view('client/sgsst/1planear/p1_1_4exoneracion_cocolab', $data);
    }

    public function generatePdf_exoneracionCocolab()
    {
        // Instanciar Dompdf con opciones
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);

        // Obtener los mismos datos que en la función policyNoAlcoholDrogas
        $session = session();
        $clientId = getEffectiveClientId();

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $clientPoliciesModel = new ClientPoliciesModel();
        $policyTypeModel = new PolicyTypeModel();
        $versionModel = new DocumentVersionModel();

        // Obtener los datos necesarios
        $client = $clientModel->find($clientId);
        $consultant = $consultantModel->find($client['id_consultor']);

        // Obtener fecha del primer contrato del cliente
        $contractModel = new ContractModel();
        $firstContractDate = $contractModel->getFirstContractDate($clientId);
        $policyTypeId = 6; // Supongamos que el ID de la política de alcohol y drogas es 1
        $clientPolicy = $clientPoliciesModel->where('client_id', $clientId)
            ->where('policy_type_id', $policyTypeId)
            ->orderBy('id', 'DESC')
            ->first();
        $policyType = $policyTypeModel->find($policyTypeId);
        $latestVersion = $versionModel->where('client_id', $clientId)
            ->where('policy_type_id', $policyTypeId)
            ->orderBy('created_at', 'DESC')
            ->first();
        $allVersions = $versionModel->where('client_id', $clientId)
            ->where('policy_type_id', $policyTypeId)
            ->orderBy('created_at', 'DESC')
            ->findAll();

            // Sobrescribir la fecha con la del primer contrato
            if ($firstContractDate) {
                $latestVersion['created_at'] = $firstContractDate;
            } else {
                // Cliente sin contrato
                $latestVersion['created_at'] = null;
                $latestVersion['sin_contrato'] = true;
            }

            // Sobrescribir las fechas de todas las versiones con la del primer contrato
            foreach ($allVersions as &$version) {
                if ($firstContractDate) {
                    $version['created_at'] = $firstContractDate;
                } else {
                    $version['created_at'] = null;
                    $version['sin_contrato'] = true;
                }
            }
            unset($version); // Romper la referencia

            if ($latestVersion && $latestVersion['created_at']) {
                $latestVersion['created_at'] = Time::parse($latestVersion['created_at'], 'America/Bogota')
                                                   ->toLocalizedString('d MMMM yyyy');
            } elseif (isset($latestVersion['sin_contrato'])) {
                $latestVersion['created_at'] = 'PENDIENTE DE CONTRATO';
            }


        // Preparar los datos para la vista
        $data = [
            'client' => $client,
            'consultant' => $consultant,
            'clientPolicy' => $clientPolicy,
            'policyType' => $policyType,
            'latestVersion' => $latestVersion,
            'allVersions' => $allVersions,  // Pasamos todas las versiones al footer
        ];

        // Cargar la vista y pasar los datos
        $html = view('client/sgsst/1planear/p1_1_4exoneracion_cocolab', $data);

        // Cargar el HTML en Dompdf
        $dompdf->loadHtml($html);

        // Configurar el tamaño del papel y la orientación
        $dompdf->setPaper('A3', 'portrait');

        // Renderizar el PDF
        $dompdf->render();

        // Enviar el PDF al navegador para descargar
        $dompdf->stream('exoneracion_cocolab.pdf', ['Attachment' => false]);
    }
}
