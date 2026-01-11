<?php 
namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\ConsultantModel;
// Ya no usamos ClientPoliciesModel, DocumentVersionModel, PolicyTypeModel (migrado a DocumentLibrary.php)

use Dompdf\Dompdf;

use CodeIgniter\Controller;

class Prueba1Controller extends Controller
{
    public function responsableDelSGSST()
    {
        // Cargar helper para acceso a DocumentLibrary
        helper('document_library');

        // Obtener el ID del cliente desde la sesión
        $session = session();
        $clientId = $session->get('user_id'); // Asegúrate de que este ID es el del cliente

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        // Obtener los datos del cliente
        $client = $clientModel->find($clientId);

        // Verificar si se obtuvo correctamente el cliente
        if (!$client) {
            return redirect()->to('/dashboardclient')->with('error', 'No se pudo encontrar la información del cliente');
        }

        // Verificar si el cliente tiene un consultor asignado
        if (empty($client['id_consultor'])) {
            return redirect()->to('/dashboardclient')->with('error', 'El cliente no tiene un consultor asignado');
        }

        // Obtener los datos del consultor relacionado con el cliente
        $consultant = $consultantModel->find($client['id_consultor']);

        if (!$consultant) {
            return redirect()->to('/dashboardclient')->with('error', 'No se pudo encontrar la información del consultor');
        }

        $data = [
            'client' => $client,
            'consultant' => $consultant,
            'clientPolicy' => $clientPolicy,
        ];

        return view('client/sgsst/1planear/responsabledelsgsst', $data);
    }



    public function prueba1()
{
        // Cargar helper para acceso a DocumentLibrary
        helper('document_library');

    // Obtener el ID del cliente desde la sesión
    $session = session();
    $clientId = $session->get('user_id'); // Asegúrate de que este ID es el del cliente

    $clientModel = new ClientModel();
    $consultantModel = new ConsultantModel();
            
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
    $policyTypeId = 4; // Supongamos que el ID de la política de alcohol y drogas es 1
    
    

    // Obtener el tipo de política
    $policyType = get_document($policyTypeId);
        if (!$policyType) {
            return redirect()->to('/dashboardclient')->with('error', 'No se encontró este documento.');
        }

    // Obtener la versión más reciente del documento
    $latestVersion = $policyType;

    // Obtener todas las versiones del documento
    $allVersions = get_all_document_versions($policyTypeId);

    // Para compatibilidad con vistas que usan $clientPolicy
        $clientPolicy = [
            'policy_content' => $policyType['default_content'] ?? ''
        ];

        // Pasar los datos a la vista
        $data = [
        'client' => $client,
        'consultant' => $consultant,
            'clientPolicy' => $clientPolicy,
        'policyType' => $policyType,
        'latestVersion' => $latestVersion,
        'allVersions' => $allVersions,  // Pasamos todas las versiones al footer
    ];

    return view('client/sgsst/1planear/prueba1', $data);
}

public function generatePdfNoAlcoholDrogas()
{
        // Cargar helper para acceso a DocumentLibrary
        helper('document_library');

    // Instanciar Dompdf
    $dompdf = new Dompdf();
    $dompdf->setOption('isRemoteEnabled', true);

    // Obtener los mismos datos que en la función policyNoAlcoholDrogas
    $session = session();
    $clientId = $session->get('user_id');

    $clientModel = new ClientModel();
    $consultantModel = new ConsultantModel();
            
    // Obtener los datos necesarios
    $client = $clientModel->find($clientId);
    $consultant = $consultantModel->find($client['id_consultor']);
    $policyTypeId = 1; // Supongamos que el ID de la política de alcohol y drogas es 1
    
    $policyType = get_document($policyTypeId);
        if (!$policyType) {
            return redirect()->to('/dashboardclient')->with('error', 'No se encontró este documento.');
        }
    $latestVersion = $policyType;
    $allVersions = get_all_document_versions($policyTypeId);

    // Para compatibilidad con vistas que usan $clientPolicy
        $clientPolicy = [
            'policy_content' => $policyType['default_content'] ?? ''
        ];

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
    $html = view('client/sgsst/1planear/prueba1', $data);

    // Cargar el HTML en Dompdf
    $dompdf->loadHtml($html);

    // Configurar el tamaño del papel y la orientación
    $dompdf->setPaper('A4', 'portrait');

    // Renderizar el PDF
    $dompdf->render();

    // Enviar el PDF al navegador para descargar
    $dompdf->stream('prueba1.pdf', ['Attachment' => false]);
}

}



?>