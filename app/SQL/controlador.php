<?php

namespace App\Controllers;

use App\Models\{ReporteModel, ClientModel, ReportTypeModel, DetailReportModel};
use CodeIgniter\Controller;

class ReportController extends Controller
{
    public function index()
    {
        $reporteModel = new ReporteModel();
        $reportTypeModel = new ReportTypeModel();
        $clientModel = new ClientModel();
        $detailReportModel = new DetailReportModel();

        $reports = $reporteModel->findAll();
        $reportTypes = $reportTypeModel->findAll();
        $clients = $clientModel->findAll();
        $details = $detailReportModel->findAll();

        $data = [
            'reports' => $reports,
            'reportTypes' => $reportTypes,
            'clients' => $clients,
            'details' => $details
        ];

        return view('consultant/add_report', $data);
    }

    public function reportList()
    {
        $reporteModel = new ReporteModel();
        $reportTypeModel = new ReportTypeModel();
        $clientModel = new ClientModel();
        $detailReportModel = new DetailReportModel();

        $reports = $reporteModel->findAll();
        $reportTypes = $reportTypeModel->findAll();
        $clients = $clientModel->findAll();
        $details = $detailReportModel->findAll();

        $data = [
            'reports' => $reports,
            'reportTypes' => $reportTypes,
            'clients' => $clients,
            'details' => $details
        ];

        return view('consultant/report_list', $data);
    }

    public function addReport()
    {
        $reporteModel = new ReporteModel();
        $reportTypeModel = new ReportTypeModel();
        $clientModel = new ClientModel();
        $detailReportModel = new DetailReportModel();

        $reports = $reporteModel->findAll();
        $reportTypes = $reportTypeModel->findAll();
        $clients = $clientModel->findAll();
        $details = $detailReportModel->findAll();

        $data = [
            'reports' => $reports,
            'reportTypes' => $reportTypes,
            'clients' => $clients,
            'details' => $details
        ];

        return view('consultant/add_report', $data);
    }

    public function addReportPost()
    {
        $reporteModel = new ReporteModel();
        $clientModel = new ClientModel();
        $reportTypeModel = new ReportTypeModel();
        $detailReportModel = new DetailReportModel();

        // Validar existencia del cliente
        $clientId = $this->request->getVar('id_cliente');
        $client = $clientModel->find($clientId);
        if (!$client) {
            return redirect()->back()->with('msg', 'Cliente no encontrado');
        }

        // Validar existencia de id_report_type
        $reportTypeId = $this->request->getVar('id_report_type');
        $reportType = $reportTypeModel->find($reportTypeId);
        if (!$reportType) {
            return redirect()->back()->with('msg', 'Tipo de reporte no válido');
        }

        // Validar existencia de id_detailreport
        $detailReportId = $this->request->getVar('id_detailreport');
        $detailReport = $detailReportModel->find($detailReportId);
        if (!$detailReport) {
            return redirect()->back()->with('msg', 'Detalle de reporte no válido');
        }

        // Procesar archivo
        $file = $this->request->getFile('archivo');
        $nitCliente = $client['nit_cliente'];
        $uploadPath = ROOTPATH . 'public/uploads/' . $nitCliente;

        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Crear carpeta si no existe
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $fileName = $file->getRandomName();
            $file->move($uploadPath, $fileName);
        } else {
            return redirect()->back()->with('msg', 'Error al subir archivo. Asegúrese de seleccionar un archivo válido.');
        }

        // Guardar datos
        $data = [
            'titulo_reporte' => $this->request->getVar('titulo_reporte'),
            'id_detailreport' => $detailReportId,
            'id_report_type' => $reportTypeId,
            'id_cliente' => $clientId,
            'estado' => $this->request->getVar('estado'),
            'observaciones' => $this->request->getVar('observaciones'),
            'enlace' => base_url('uploads/' . $nitCliente . '/' . $fileName),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($reporteModel->save($data)) {
            // Obtener el correo del cliente
            $correoCliente = $client['correo_cliente'];

            // Enviar correo electrónico
            $this->sendEmailToClient($correoCliente, $data);

            return redirect()->to('/reportList')->with('msg', 'Reporte agregado exitosamente y correo enviado al cliente');
        } else {
            return redirect()->back()->with('msg', 'Error al guardar reporte en la base de datos.');
        }
    }

    private function sendEmailToClient($to, $reportData)
    {
        // Obtener el servicio de correo con la configuración predeterminada
        $email = \Config\Services::email();

        // Establecer remitente y destinatario
        $email->setFrom(config('Email')->fromEmail, config('Email')->fromName);
        $email->setTo($to);

        // Asunto del correo
        $email->setSubject('Nuevo Reporte Disponible: ' . $reportData['titulo_reporte']);

        // Cuerpo del correo
        $message = "<p>Estimado cliente,</p>";
        $message .= "<p>Se ha generado un nuevo reporte: <strong>" . esc($reportData['titulo_reporte']) . "</strong>.</p>";
        $message .= "<p>Puedes acceder al reporte haciendo clic en el siguiente enlace:</p>";
        $message .= "<p><a href='" . esc($reportData['enlace']) . "'>Ver Reporte</a></p>";
        $message .= "<p>Gracias por confiar en nosotros.</p>";

        $email->setMessage($message);

        // Enviar el correo
        if (!$email->send()) {
            // Manejar el error de envío si es necesario
            log_message('error', 'Error al enviar correo a ' . $to . '. Error: ' . $email->printDebugger(['headers']));
        }
    }

    public function editReport($id)
    {
        $reporteModel = new ReporteModel();
        $reportTypeModel = new ReportTypeModel();
        $clientModel = new ClientModel();
        $detailReportModel = new DetailReportModel();

        $report = $reporteModel->find($id);

        if (!$report) {
            return redirect()->to('/reportList')->with('msg', 'Reporte no encontrado.');
        }

        $reportTypes = $reportTypeModel->findAll();
        $clients = $clientModel->findAll();
        $details = $detailReportModel->findAll();

        $data = [
            'report' => $report,
            'reportTypes' => $reportTypes,
            'clients' => $clients,
            'details' => $details
        ];

        return view('consultant/edit_report', $data);
    }

    public function editReportPost($id)
    {
        $reporteModel = new ReporteModel();
        $clientModel = new ClientModel();
        $reportTypeModel = new ReportTypeModel();
        $detailReportModel = new DetailReportModel();

        // Validar existencia del reporte
        $reporte = $reporteModel->find($id);
        if (!$reporte) {
            return redirect()->to('/reportList')->with('msg', 'Reporte no encontrado');
        }

        // Validar existencia del cliente
        $cliente = $clientModel->find($this->request->getVar('id_cliente'));
        if (!$cliente) {
            return redirect()->to('/reportList')->with('msg', 'Cliente no encontrado');
        }

        // Validar existencia de id_report_type
        $reportType = $reportTypeModel->find($this->request->getVar('id_report_type'));
        if (!$reportType) {
            return redirect()->back()->with('msg', 'Tipo de reporte no válido');
        }

        // Validar existencia de id_detailreport
        $detailReport = $detailReportModel->find($this->request->getVar('id_detailreport'));
        if (!$detailReport) {
            return redirect()->back()->with('msg', 'Detalle de reporte no válido');
        }

        $nitCliente = $cliente['nit_cliente'];

        // Procesar datos enviados desde el formulario
        $data = [
            'titulo_reporte' => $this->request->getVar('titulo_reporte'),
            'id_detailreport' => $this->request->getVar('id_detailreport'),
            'id_report_type' => $this->request->getVar('id_report_type'),
            'id_cliente' => $this->request->getVar('id_cliente'),
            'estado' => $this->request->getVar('estado'),
            'observaciones' => $this->request->getVar('observaciones'),
        ];

        // Procesar archivo subido (opcional)
        $file = $this->request->getFile('archivo');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newFileName = $file->getRandomName();
            $clientFolder = ROOTPATH . 'public/uploads/' . $nitCliente;

            // Crear carpeta si no existe
            if (!is_dir($clientFolder)) {
                mkdir($clientFolder, 0777, true);
            }

            // Mover archivo al directorio del cliente
            $file->move($clientFolder, $newFileName);

            // Actualizar enlace en los datos
            $data['enlace'] = base_url('uploads/' . $nitCliente . '/' . $newFileName);
        } else {
            // Mantener el enlace original si no se subió un archivo nuevo
            $data['enlace'] = $reporte['enlace'];
        }

        // Actualizar fecha de modificación
        $data['updated_at'] = date('Y-m-d H:i:s');

        // Actualizar el reporte en la base de datos
        if ($reporteModel->update($id, $data)) {
            return redirect()->to('/reportList')->with('msg', 'Reporte actualizado exitosamente');
        } else {
            return redirect()->to('/reportList')->with('msg', 'Error al actualizar el reporte');
        }
    }

    public function deleteReport($id)
    {
        $reporteModel = new ReporteModel();
        $reporteModel->delete($id);
        return redirect()->to('/reportList')->with('msg', 'Reporte eliminado exitosamente');
    }
}
