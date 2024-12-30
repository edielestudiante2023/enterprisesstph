<?php

namespace App\Controllers;

use App\Models\ReporteModel;
use App\Models\ClientModel;
use App\Models\ReportTypeModel;
use CodeIgniter\Controller;
use CodeIgniter\Files\File;

class ReportController extends Controller
{
    public function index()
    {
        $model = new ReporteModel();
        $clientModel = new ClientModel();

        // Recuperar el parámetro de filtrado
        $clientId = $this->request->getGet('id_cliente');

        // Si se ha seleccionado un cliente específico, filtrar los reportes por ese cliente
        if ($clientId) {
            // Modificación: Añadir filtrado por id_cliente
            $reports = $model->where('id_cliente', $clientId)->orderBy('created_at', 'DESC')->findAll();
        } else {
            $reports = $model->orderBy('created_at', 'DESC')->findAll();
        }

        $clients = $clientModel->findAll();

        $data = [
            'reports' => $reports,
            'clients' => $clients
        ];

        // Modificación: Asegurarse de pasar los datos necesarios a la vista add_report
        return view('consultant/add_report', $data);
    }

    public function reportList()
    {
        $reporteModel = new ReporteModel();
        $reportTypeModel = new ReportTypeModel();
        $clientModel = new ClientModel();

        // Obtener todos los reportes
        $reports = $reporteModel->findAll();

        // Obtener todos los tipos de reporte
        $reportTypes = $reportTypeModel->findAll();

        // Obtener todos los clientes
        $clients = $clientModel->findAll();

        $data = [
            'reports' => $reports,
            'reportTypes' => $reportTypes,
            'clients' => $clients
        ];

        // Modificación: Asegurarse de pasar los datos necesarios a la vista report_list
        return view('consultant/report_list', $data);
    }


    public function addReport()
    {
        $documentTypeModel = new \App\Models\ReportTypeModel();
        $reportTypes = $documentTypeModel->findAll();

        $clientModel = new ClientModel();
        $clients = $clientModel->findAll();

        $data = [
            'reportTypes' => $reportTypes,
            'clients' => $clients
        ];

        return view('consultant/add_report', $data);
    }

    public function addReportPost()
    {
        $model = new ReporteModel();

        // Obtener id_consultor de la sesión
        $consultantId = session()->get('user_id');

        // Obtenemos el cliente correspondiente
        $clientModel = new ClientModel();
        $client = $clientModel->find($this->request->getVar('id_cliente'));

        if (!$client) {
            return redirect()->back()->with('msg', 'Cliente no encontrado');
        }

        // Recuperamos el NIT del cliente
        $nitCliente = $client['nit_cliente'];
        $uploadPath = ROOTPATH . 'public/uploads/' . $nitCliente;

        // Verificamos si la carpeta del cliente existe
        if (!is_dir($uploadPath)) {
            return redirect()->back()->with('msg', 'La carpeta del cliente no existe. Por favor, cree el cliente primero.');
        }

        // Procesamos el archivo subido
        $file = $this->request->getFile('archivo');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $fileName = $file->getRandomName(); // Generar un nombre único para el archivo
            $file->move($uploadPath, $fileName); // Mover el archivo a la carpeta del cliente
        } else {
            return redirect()->back()->with('msg', 'Error al subir archivo. Asegúrese de seleccionar un archivo válido.');
        }

        // Guardamos los datos del reporte en la base de datos
        $data = [
            'titulo_reporte' => $this->request->getVar('titulo_reporte'),
            'Tipo_documento' => $this->request->getVar('Tipo_documento'),
            'enlace' => base_url('uploads/' . $nitCliente . '/' . $fileName), // Guardamos la URL del archivo
            'estado' => $this->request->getVar('estado'),
            'observaciones' => $this->request->getVar('observaciones'),
            'id_cliente' => $this->request->getVar('id_cliente'),
            'id_consultor' => $consultantId,
            'id_report_type' => $this->request->getVar('id_report_type'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($model->save($data)) {
            return redirect()->to('/reportList')->with('msg', 'Reporte agregado exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al guardar reporte en la base de datos.');
        }
    }


    public function editReport($id)
    {
        $model = new ReporteModel();
        $report = $model->find($id);

        $documentTypeModel = new \App\Models\ReportTypeModel();
        $reportTypes = $documentTypeModel->findAll();

        $clientModel = new ClientModel();
        $clients = $clientModel->findAll();

        $data = [
            'report' => $report,
            'reportTypes' => $reportTypes,
            'clients' => $clients
        ];

        return view('consultant/edit_report', $data);
    }

    public function editReportPost($id)
{
    $model = new ReporteModel();

    // Obtener el reporte existente
    $reporte = $model->find($id);
    if (!$reporte) {
        return redirect()->to('/reportList')->with('msg', 'Reporte no encontrado');
    }

    // Obtener el cliente asociado
    $clientModel = new ClientModel();
    $cliente = $clientModel->find($reporte['id_cliente']);
    if (!$cliente) {
        return redirect()->to('/reportList')->with('msg', 'Cliente no encontrado');
    }

    // Obtener el NIT del cliente
    $nitCliente = $cliente['nit_cliente'];

    // Datos enviados desde el formulario
    $data = [
        'titulo_reporte' => $this->request->getVar('titulo_reporte'),
        'Tipo_documento' => $this->request->getVar('Tipo_documento'),
        'estado' => $this->request->getVar('estado'),
        'observaciones' => $this->request->getVar('observaciones'),
        'id_cliente' => $this->request->getVar('id_cliente'),
        'id_report_type' => $this->request->getVar('id_report_type'),
    ];

    // Comparar datos actuales con los existentes
    $datosIguales = true;
    foreach ($data as $key => $value) {
        if ($reporte[$key] != $value) {
            $datosIguales = false;
            break;
        }
    }

    // Procesar archivo subido (si existe)
    $file = $this->request->getFile('archivo'); // El nombre debe coincidir con el formulario
    if ($file && $file->isValid() && !$file->hasMoved()) {
        // Generar un nombre único para el archivo
        $newFileName = $file->getRandomName();

        // Ruta de la carpeta donde guardar el archivo (basada en el NIT del cliente)
        $clientFolder = ROOTPATH . 'public/uploads/' . $nitCliente;
        if (!is_dir($clientFolder)) {
            mkdir($clientFolder, 0777, true); // Crear la carpeta si no existe
        }

        // Mover el archivo al directorio correspondiente
        $file->move($clientFolder, $newFileName);

        // Actualizar el campo 'enlace' con la nueva ruta completa
        $data['enlace'] = base_url('uploads/' . $nitCliente . '/' . $newFileName);
        $datosIguales = false; // Si se sube un nuevo archivo, siempre hay un cambio
    } else {
        // Si no hay archivo nuevo y el enlace ya está configurado
        $data['enlace'] = $reporte['enlace'];
    }

    // Si no hubo cambios, redirigir a la lista de reportes con un mensaje
    if ($datosIguales) {
        return redirect()->to('/reportList')->with('msg', 'No se realizaron cambios en el reporte.');
    }

    // Agregar campo updated_at solo si hay cambios
    $data['updated_at'] = date('Y-m-d H:i:s');

    // Actualizar los datos en la base de datos
    if ($model->update($id, $data)) {
        return redirect()->to('/reportList')->with('msg', 'Reporte actualizado exitosamente');
    } else {
        return redirect()->to('/reportList')->with('msg', 'Error al actualizar reporte');
    }
}







    public function deleteReport($id)
    {
        $model = new ReporteModel();
        $model->delete($id);
        return redirect()->to('/reportList')->with('msg', 'Reporte eliminado exitosamente');
    }

    public function dashboard()
    {
        $clientModel = new ClientModel();
        $clients = $clientModel->findAll();

        $reportModel = new ReporteModel();
        $reports = $reportModel->orderBy('created_at', 'DESC')->findAll();

        // Verificar qué datos se están pasando a la vista
        var_dump($clients);
        var_dump($reports);
        exit;

        $data = [
            'clients' => $clients,
            'reports' => $reports
        ];

        return view('consultant/dashboard', $data);
    }
}
