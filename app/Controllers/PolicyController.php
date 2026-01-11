<?php

namespace App\Controllers;

use App\Models\ClientModel;
use CodeIgniter\Controller;
// Ya no usamos ClientPoliciesModel, DocumentVersionModel, PolicyTypeModel (migrado a DocumentLibrary.php)

/**
 * NOTA: Este controlador está deprecado
 * Las políticas ahora se gestionan desde app/Libraries/DocumentLibrary.php
 * Ya no se almacenan en base de datos, son archivos PHP estáticos
 */
class PolicyController extends Controller
{
    public function listPolicies()
    {
        helper('document_library');
        
        $clientModel = new ClientModel();
        $clients = $clientModel->findAll();

        // Obtener todos los documentos desde la librería
        $policyTypes = get_all_documents();

        return view('consultant/list_policies', [
            'clients' => $clients,
            'policies' => [], // Ya no hay policies por cliente, todo es estático
            'policyTypes' => $policyTypes
        ]);
    }

    public function addPolicy()
    {
        helper('document_library');

        $clientModel = new ClientModel();
        $clients = $clientModel->findAll();

        // Obtener todos los documentos desde la librería
        $policyTypes = get_all_documents();

        return view('consultant/add_policy', [
            'clients' => $clients,
            'policyTypes' => $policyTypes
        ]);
    }

    public function addPolicyPost()
    {
        // DEPRECADO: Ya no se crean políticas por cliente
        // Todo se maneja desde DocumentLibrary.php
        return redirect()->to('/listPolicies')->with('msg', 'Esta funcionalidad está deprecada. Las políticas se gestionan desde DocumentLibrary.php');
    }

    public function editPolicy($id)
    {
        // DEPRECADO: Ya no se editan políticas por cliente
        return redirect()->to('/listPolicies')->with('msg', 'Esta funcionalidad está deprecada. Las políticas se gestionan desde DocumentLibrary.php');
    }

    public function editPolicyPost($id)
    {
        // DEPRECADO: Ya no se editan políticas por cliente
        return redirect()->to('/listPolicies')->with('msg', 'Esta funcionalidad está deprecada. Las políticas se gestionan desde DocumentLibrary.php');
    }

    public function deletePolicy($id)
    {
        // DEPRECADO: Ya no se eliminan políticas por cliente
        return redirect()->to('/listPolicies')->with('msg', 'Esta funcionalidad está deprecada. Las políticas se gestionan desde DocumentLibrary.php');
    }

    public function listPolicyTypes()
    {
        helper('document_library');

        // Obtener todos los documentos desde la librería
        $policyTypes = get_all_documents();
        return view('consultant/list_policy_types', ['policyTypes' => $policyTypes]);
    }

    public function addPolicyType()
    {
        // DEPRECADO: Los tipos de política se gestionan en DocumentLibrary.php
        return redirect()->to('/listPolicyTypes')->with('msg', 'Esta funcionalidad está deprecada. Los documentos se gestionan desde DocumentLibrary.php');
    }

    public function addPolicyTypePost()
    {
        // DEPRECADO
        return redirect()->to('/listPolicyTypes')->with('msg', 'Esta funcionalidad está deprecada. Los documentos se gestionan desde DocumentLibrary.php');
    }

    public function editPolicyType($id)
    {
        // DEPRECADO
        return redirect()->to('/listPolicyTypes')->with('msg', 'Esta funcionalidad está deprecada. Los documentos se gestionan desde DocumentLibrary.php');
    }

    public function editPolicyTypePost($id)
    {
        // DEPRECADO
        return redirect()->to('/listPolicyTypes')->with('msg', 'Esta funcionalidad está deprecada. Los documentos se gestionan desde DocumentLibrary.php');
    }

    public function deletePolicyType($id)
    {
        // DEPRECADO
        return redirect()->to('/listPolicyTypes')->with('msg', 'Esta funcionalidad está deprecada. Los documentos se gestionan desde DocumentLibrary.php');
    }
}

