<?php

namespace App\Controllers;

use App\Models\{ReporteModel, ClientModel, ReportTypeModel, DetailReportModel};
use CodeIgniter\Controller;

require __DIR__ . '/../../vendor/autoload.php';



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
        $clientModel = new ClientModel();

        // Obtener años disponibles dinámicamente desde la BD
        $db = \Config\Database::connect();
        $availableYears = $db->table('tbl_reporte')
            ->select('YEAR(created_at) as year')
            ->where('created_at IS NOT NULL')
            ->groupBy('YEAR(created_at)')
            ->orderBy('year', 'DESC')
            ->get()
            ->getResultArray();
        $years = array_column($availableYears, 'year');

        $selectedYear = $this->request->getGet('year') ?? date('Y');
        $clients = $clientModel->findAll();

        $data = [
            'clients' => $clients,
            'availableYears' => $years,
            'selectedYear' => $selectedYear,
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

        $idCliente = $this->request->getVar('id_cliente');
        $client = $clientModel->find($idCliente);
        log_message('debug', 'Cliente recuperado: ' . print_r($client, true));

        if (!$client) {
            return redirect()->back()->with('msg', 'Cliente no encontrado');
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

        // Procesar archivo
        $file = $this->request->getFile('archivo');
        $nitCliente = $client['nit_cliente'];
        $uploadPath = ROOTPATH . 'public/uploads/' . $nitCliente;

        // Crear directorio si no existe
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $fileName = $file->getRandomName();
            $file->move($uploadPath, $fileName);
        } else {
            return redirect()->back()->with('msg', 'Error al subir archivo. Asegúrese de seleccionar un archivo válido.');
        }

        // Guardar datos
        $data = [
            'titulo_reporte' => $this->request->getVar('titulo_reporte'),
            'id_detailreport' => $this->request->getVar('id_detailreport'),
            'id_report_type' => $this->request->getVar('id_report_type'),
            'id_cliente' => $idCliente,
            'estado' => $this->request->getVar('estado'),
            'observaciones' => $this->request->getVar('observaciones'),
            'enlace' => base_url('uploads/' . $nitCliente . '/' . $fileName),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($reporteModel->save($data)) {
            log_message('debug', 'Guardado exitoso, llamando a sendEmailToClient');
            // Llamar a sendEmailToClient con el ID del cliente
            $this->sendEmailToClient($idCliente, $data['titulo_reporte'], $data['enlace']);

            return redirect()->to('/addReport')->with('msg', 'Reporte agregado exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al guardar reporte en la base de datos.');
        }
    }

    private function sendEmailToClient($idCliente, $tituloReporte, $enlace)
    {
        // Validar el enlace antes de proceder
        if (!filter_var($enlace, FILTER_VALIDATE_URL)) {
            log_message('error', 'El enlace generado no es válido: ' . $enlace);
            return;
        }

        // Obtener los datos del cliente desde el modelo
        $clientModel = new \App\Models\ClientModel();
        $cliente = $clientModel->find($idCliente);

        if (!$cliente || empty($cliente['correo_cliente'])) {
            log_message('error', "No se encontró el cliente o el correo no está disponible para id_cliente: $idCliente");
            return;
        }

        $nombreCliente = $cliente['nombre_cliente'];

        // Recopilar destinatarios (sin duplicados)
        $destinatarios = [];

        // 1. Cliente
        $destinatarios[$cliente['correo_cliente']] = $nombreCliente;

        // 2. Consultor interno (vía id_consultor → tbl_consultor)
        if (!empty($cliente['id_consultor'])) {
            $consultorModel = new \App\Models\ConsultorModel();
            $consultor = $consultorModel->find($cliente['id_consultor']);
            if ($consultor && !empty($consultor['correo_consultor'])) {
                $destinatarios[$consultor['correo_consultor']] = $consultor['nombre_consultor'];
            }
        }

        // 3. Consultor externo
        if (!empty($cliente['email_consultor_externo'])) {
            $nombreExterno = $cliente['consultor_externo'] ?? 'Consultor Externo';
            $destinatarios[$cliente['email_consultor_externo']] = $nombreExterno;
        }

        // Crear el objeto Mail
        $email = new \SendGrid\Mail\Mail();
        $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "Cycloid Talent");
        $email->setSubject("Nuevo documento añadido en su aplicación Enterprisesst Propiedad Horizontal");

        foreach ($destinatarios as $correo => $nombre) {
            $email->addTo($correo, $nombre);
        }

        $emailContent = "
        <h3>Estimado/a $nombreCliente</h3>
        <p style='text-align: justify;'>Nos complace informarle que hemos añadido el documento <strong>$tituloReporte</strong> a su aplicación Enterprisesst. Este soporte evidencia los avances de nuestra gestión en Seguridad y Salud en el Trabajo (SG-SST).</p>
        <p style='text-align: justify;'>El documento <strong>$tituloReporte</strong> ya está disponible para su consulta inmediata en la sección de documentos dentro de su aplicación. Le invitamos a acceder a su plataforma de manera ágil y sencilla siguiendo el enlace:</p>

         <p style='text-align: center;'>
                <a href='https://phorizontal.cycloidtalent.com/' target='_blank' style='display: inline-block; padding: 15px 25px; background-color: #007bff; color: #ffffff; text-decoration: none; border-radius: 25px; font-size: 16px; font-weight: bold; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); transition: all 0.3s ease;'>
                    Ir a Enterprisesst
                </a>
        </p>

        <p style='text-align: justify;'>
            En <strong>Cycloid Talent</strong>, nos distinguimos por ser aliados estratégicos en la administración del SG-SST. Nuestro compromiso es ofrecerle soluciones innovadoras y personalizadas que potencien la seguridad y el bienestar en su copropiedad. Con Enterprisesst Propiedad Horizontal, no solo recibe herramientas de gestión, sino también el respaldo de un equipo de expertos enfocados en brindarle resultados sobresalientes.
            </p>

            <p style='text-align: justify;'>
            Le recordamos que nuestro equipo está disponible para atender cualquier inquietud o requerimiento adicional que pueda tener. Si necesita orientación sobre cómo aprovechar al máximo este documento o cualquier otro servicio de nuestro portafolio, no dude en ponerse en contacto con nosotros.
            </p>

            <p style='text-align: justify; font-size: 1.1em; font-weight: bold;'>
            Gracias por confiar en Cycloid Talent, donde su tranquilidad y éxito son nuestra prioridad.
            </p>

            <p style='text-align: center; font-size: 0.9em; color: #6c757d;'>
            Para más información, visite nuestra página web o contáctenos directamente a través de nuestros canales de atención.
            </p>
            ";

        $email->addContent("text/html", $emailContent);

        $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));

        try {
            $response = $sendgrid->send($email);
            log_message('debug', 'SendGrid Response Status Code: ' . $response->statusCode());
            log_message('debug', 'SendGrid Response Body: ' . $response->body());
            log_message('info', 'Email de reporte enviado a: ' . implode(', ', array_keys($destinatarios)));
        } catch (\Exception $e) {
            log_message('error', 'Excepción al enviar el correo: ' . $e->getMessage());
        }
    }






    /**
     * API endpoint for DataTables server-side processing.
     * Returns JSON with paginated, filtered, sorted report data.
     */
    public function apiReportList()
    {
        $db = \Config\Database::connect();

        // DataTables parameters
        $draw   = (int) $this->request->getGet('draw');
        $start  = (int) $this->request->getGet('start');
        $length = (int) $this->request->getGet('length');
        $searchValue = $this->request->getGet('search')['value'] ?? '';

        // Custom filters
        $year     = $this->request->getGet('year') ?? date('Y');
        $client   = $this->request->getGet('client') ?? '';
        $dateFrom = $this->request->getGet('dateFrom') ?? '';
        $dateTo   = $this->request->getGet('dateTo') ?? '';
        $month    = $this->request->getGet('month') ?? '';

        // Column mapping: DataTables column index => DB column
        $columns = [
            0 => null, // Acciones — not sortable/searchable
            1 => 'r.created_at',
            2 => null, // Enlace — not sortable/searchable
            3 => 'r.id_reporte',
            4 => 'r.titulo_reporte',
            5 => 'd.detail_report',
            6 => 'rt.report_type',
            7 => 'r.estado',
            8 => 'r.observaciones',
            9 => 'r.id_cliente',
            10 => 'c.nombre_cliente',
        ];

        // Base query builder
        $baseQuery = function () use ($db) {
            return $db->table('tbl_reporte r')
                ->select('r.id_reporte, r.titulo_reporte, r.enlace, r.estado, r.observaciones, r.id_cliente, r.created_at, r.id_report_type, r.id_detailreport')
                ->select('c.nombre_cliente')
                ->select('rt.report_type')
                ->select('d.detail_report')
                ->join('tbl_clientes c', 'c.id_cliente = r.id_cliente', 'left')
                ->join('report_type_table rt', 'rt.id_report_type = r.id_report_type', 'left')
                ->join('detail_report d', 'd.id_detailreport = r.id_detailreport', 'left');
        };

        // Apply custom filters to a query builder
        $applyFilters = function ($builder) use ($year, $client, $dateFrom, $dateTo, $month) {
            if ($year !== 'all' && $year !== '') {
                $builder->where('YEAR(r.created_at)', (int) $year);
            }
            if ($client !== '') {
                $builder->where('c.nombre_cliente', $client);
            }
            if ($dateFrom !== '') {
                $builder->where('r.created_at >=', $dateFrom . ' 00:00:00');
            }
            if ($dateTo !== '') {
                $builder->where('r.created_at <=', $dateTo . ' 23:59:59');
            }
            if ($month !== '') {
                $builder->where('MONTH(r.created_at)', (int) $month);
            }
            return $builder;
        };

        // 1. Total records (no filters)
        $recordsTotal = $db->table('tbl_reporte')->countAllResults();

        // 2. Filtered records count (with custom filters + search)
        $countBuilder = $applyFilters($baseQuery());

        // Global search
        if ($searchValue !== '') {
            $countBuilder->groupStart();
            $countBuilder->like('r.titulo_reporte', $searchValue);
            $countBuilder->orLike('r.estado', $searchValue);
            $countBuilder->orLike('r.observaciones', $searchValue);
            $countBuilder->orLike('c.nombre_cliente', $searchValue);
            $countBuilder->orLike('d.detail_report', $searchValue);
            $countBuilder->orLike('rt.report_type', $searchValue);
            $countBuilder->orLike('r.created_at', $searchValue);
            $countBuilder->groupEnd();
        }

        // Column-specific search
        $dtColumns = $this->request->getGet('columns') ?? [];
        foreach ($dtColumns as $idx => $col) {
            $searchVal = $col['search']['value'] ?? '';
            if ($searchVal !== '' && isset($columns[(int) $idx]) && $columns[(int) $idx] !== null) {
                $dbCol = $columns[(int) $idx];
                // Dropdown filters use regex ^value$ — extract the value
                if (preg_match('/^\^(.+)\$$/', $searchVal, $m)) {
                    $countBuilder->where($dbCol, $m[1]);
                } else {
                    $countBuilder->like($dbCol, $searchVal);
                }
            }
        }

        $recordsFiltered = $countBuilder->countAllResults(false);

        // 3. Data query (with filters + search + order + limit)
        $dataBuilder = $applyFilters($baseQuery());

        // Global search (same as above)
        if ($searchValue !== '') {
            $dataBuilder->groupStart();
            $dataBuilder->like('r.titulo_reporte', $searchValue);
            $dataBuilder->orLike('r.estado', $searchValue);
            $dataBuilder->orLike('r.observaciones', $searchValue);
            $dataBuilder->orLike('c.nombre_cliente', $searchValue);
            $dataBuilder->orLike('d.detail_report', $searchValue);
            $dataBuilder->orLike('rt.report_type', $searchValue);
            $dataBuilder->orLike('r.created_at', $searchValue);
            $dataBuilder->groupEnd();
        }

        // Column-specific search (same as above)
        foreach ($dtColumns as $idx => $col) {
            $searchVal = $col['search']['value'] ?? '';
            if ($searchVal !== '' && isset($columns[(int) $idx]) && $columns[(int) $idx] !== null) {
                $dbCol = $columns[(int) $idx];
                if (preg_match('/^\^(.+)\$$/', $searchVal, $m)) {
                    $dataBuilder->where($dbCol, $m[1]);
                } else {
                    $dataBuilder->like($dbCol, $searchVal);
                }
            }
        }

        // Order
        $orderParam = $this->request->getGet('order') ?? [];
        if (!empty($orderParam)) {
            foreach ($orderParam as $o) {
                $colIdx = (int) $o['column'];
                $dir = ($o['dir'] === 'asc') ? 'ASC' : 'DESC';
                if (isset($columns[$colIdx]) && $columns[$colIdx] !== null) {
                    $dataBuilder->orderBy($columns[$colIdx], $dir);
                }
            }
        } else {
            $dataBuilder->orderBy('r.created_at', 'DESC');
        }

        // Pagination
        if ($length > 0) {
            $dataBuilder->limit($length, $start);
        }

        $results = $dataBuilder->get()->getResultArray();

        // Format data for DataTables (array of arrays matching column order)
        $data = [];
        $baseUrl = base_url();
        foreach ($results as $row) {
            $id = $row['id_reporte'];
            $enlace = htmlspecialchars($row['enlace'] ?? '', ENT_QUOTES);
            $data[] = [
                // Col 0: Acciones
                '<i class="bi bi-plus-square details-control"></i> '
                    . '<a href="' . $baseUrl . '/editReport/' . $id . '" class="btn btn-warning btn-sm" title="Editar Reporte">Editar</a> '
                    . '<a href="' . $baseUrl . '/deleteReport/' . $id . '" class="btn btn-danger btn-sm" title="Eliminar Reporte" onclick="return confirm(\'¿Está seguro de eliminar este reporte?\');">Eliminar</a>',
                // Col 1: Fecha de Creación
                htmlspecialchars($row['created_at'] ?? ''),
                // Col 2: Enlace
                '<a href="' . $enlace . '" target="_blank"><i class="bi bi-link-45deg"></i></a>',
                // Col 3: ID
                $id,
                // Col 4: Título
                htmlspecialchars($row['titulo_reporte'] ?? ''),
                // Col 5: Tipo de Documento
                htmlspecialchars($row['detail_report'] ?? 'N/A'),
                // Col 6: Tipo de Reporte
                htmlspecialchars($row['report_type'] ?? ''),
                // Col 7: Estado
                htmlspecialchars($row['estado'] ?? ''),
                // Col 8: Observaciones
                htmlspecialchars($row['observaciones'] ?? ''),
                // Col 9: ID Cliente
                htmlspecialchars($row['id_cliente'] ?? ''),
                // Col 10: Nombre del Cliente
                htmlspecialchars($row['nombre_cliente'] ?? ''),
            ];
        }

        // 4. Monthly counts (for month cards) — same filters except month itself
        $monthBuilder = $db->table('tbl_reporte r')
            ->select('MONTH(r.created_at) as mes, COUNT(*) as total')
            ->join('tbl_clientes c', 'c.id_cliente = r.id_cliente', 'left')
            ->where('r.created_at IS NOT NULL');

        if ($year !== 'all' && $year !== '') {
            $monthBuilder->where('YEAR(r.created_at)', (int) $year);
        }
        if ($client !== '') {
            $monthBuilder->where('c.nombre_cliente', $client);
        }
        if ($dateFrom !== '') {
            $monthBuilder->where('r.created_at >=', $dateFrom . ' 00:00:00');
        }
        if ($dateTo !== '') {
            $monthBuilder->where('r.created_at <=', $dateTo . ' 23:59:59');
        }

        $monthResults = $monthBuilder->groupBy('MONTH(r.created_at)')->get()->getResultArray();

        $monthCounts = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthCounts[$i] = 0;
        }
        foreach ($monthResults as $mr) {
            $monthCounts[(int) $mr['mes']] = (int) $mr['total'];
        }

        return $this->response->setJSON([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
            'monthCounts'     => $monthCounts,
        ]);
    }

    /**
     * Export all filtered reports as CSV.
     */
    public function exportReportList()
    {
        $db = \Config\Database::connect();

        // Custom filters (same as apiReportList)
        $year     = $this->request->getGet('year') ?? date('Y');
        $client   = $this->request->getGet('client') ?? '';
        $dateFrom = $this->request->getGet('dateFrom') ?? '';
        $dateTo   = $this->request->getGet('dateTo') ?? '';
        $month    = $this->request->getGet('month') ?? '';

        $builder = $db->table('tbl_reporte r')
            ->select('r.id_reporte, r.created_at, r.titulo_reporte, r.estado, r.observaciones, r.id_cliente')
            ->select('c.nombre_cliente')
            ->select('rt.report_type')
            ->select('d.detail_report')
            ->join('tbl_clientes c', 'c.id_cliente = r.id_cliente', 'left')
            ->join('report_type_table rt', 'rt.id_report_type = r.id_report_type', 'left')
            ->join('detail_report d', 'd.id_detailreport = r.id_detailreport', 'left');

        if ($year !== 'all' && $year !== '') {
            $builder->where('YEAR(r.created_at)', (int) $year);
        }
        if ($client !== '') {
            $builder->where('c.nombre_cliente', $client);
        }
        if ($dateFrom !== '') {
            $builder->where('r.created_at >=', $dateFrom . ' 00:00:00');
        }
        if ($dateTo !== '') {
            $builder->where('r.created_at <=', $dateTo . ' 23:59:59');
        }
        if ($month !== '') {
            $builder->where('MONTH(r.created_at)', (int) $month);
        }

        $builder->orderBy('r.created_at', 'DESC');
        $results = $builder->get()->getResultArray();

        // Generate CSV with BOM for Excel compatibility
        $filename = 'Lista_de_Reportes_' . date('Y-m-d') . '.csv';

        $this->response->setHeader('Content-Type', 'text/csv; charset=utf-8');
        $this->response->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        // UTF-8 BOM
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Header row
        fputcsv($output, ['ID', 'Fecha de Creación', 'Título del Reporte', 'Tipo de Documento', 'Tipo de Reporte', 'Estado', 'Observaciones', 'ID Cliente', 'Nombre del Cliente']);

        foreach ($results as $row) {
            fputcsv($output, [
                $row['id_reporte'],
                $row['created_at'],
                $row['titulo_reporte'],
                $row['detail_report'] ?? 'N/A',
                $row['report_type'] ?? '',
                $row['estado'],
                $row['observaciones'],
                $row['id_cliente'],
                $row['nombre_cliente'] ?? '',
            ]);
        }

        fclose($output);
        return $this->response;
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
