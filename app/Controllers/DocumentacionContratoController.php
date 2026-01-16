<?php

namespace App\Controllers;

use App\Models\ContractModel;
use App\Models\ClientModel;
use App\Models\ReporteModel;
use CodeIgniter\Controller;
use ZipArchive;

/**
 * Controlador para descargar documentación de un cliente
 * filtrada por las fechas del contrato
 */
class DocumentacionContratoController extends Controller
{
    protected $contractModel;
    protected $clientModel;
    protected $reporteModel;

    public function __construct()
    {
        $this->contractModel = new ContractModel();
        $this->clientModel = new ClientModel();
        $this->reporteModel = new ReporteModel();
    }

    /**
     * Vista previa de los documentos del contrato
     */
    public function previsualizarDocumentacion($idContrato)
    {
        $session = session();
        $role = $session->get('role');
        $idConsultor = $session->get('id_consultor');

        // Obtener contrato con datos del cliente
        $contract = $this->contractModel
            ->select('tbl_contratos.*, tbl_clientes.nombre_cliente, tbl_clientes.nit_cliente, tbl_clientes.id_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_contratos.id_cliente')
            ->find($idContrato);

        if (!$contract) {
            return redirect()->to('/contracts')->with('error', 'Contrato no encontrado');
        }

        // Verificar permisos
        if ($role === 'consultor' && $contract['id_consultor'] != $idConsultor) {
            return redirect()->to('/contracts')->with('error', 'No tiene permisos');
        }

        // Obtener reportes del cliente dentro del rango de fechas del contrato
        $reportes = $this->reporteModel
            ->select('tbl_reporte.*, tbl_detailreport.detail_report_name, tbl_report_type.report_type_name')
            ->join('tbl_detailreport', 'tbl_detailreport.id_detailreport = tbl_reporte.id_detailreport', 'left')
            ->join('tbl_report_type', 'tbl_report_type.id_report_type = tbl_reporte.id_report_type', 'left')
            ->where('tbl_reporte.id_cliente', $contract['id_cliente'])
            ->where('DATE(tbl_reporte.created_at) >=', $contract['fecha_inicio'])
            ->where('DATE(tbl_reporte.created_at) <=', $contract['fecha_fin'])
            ->orderBy('tbl_reporte.created_at', 'ASC')
            ->findAll();

        // Verificar cuáles archivos existen físicamente
        $archivosValidos = [];
        $tamanoTotal = 0;

        foreach ($reportes as $reporte) {
            $enlace = $reporte['enlace'];
            // Convertir URL a ruta de archivo
            $rutaRelativa = str_replace(base_url(), '', $enlace);
            $rutaRelativa = ltrim($rutaRelativa, '/');
            $rutaArchivo = FCPATH . $rutaRelativa;

            if (file_exists($rutaArchivo)) {
                $tamano = filesize($rutaArchivo);
                $tamanoTotal += $tamano;
                $archivosValidos[] = [
                    'reporte' => $reporte,
                    'ruta' => $rutaArchivo,
                    'tamano' => $tamano,
                    'existe' => true
                ];
            } else {
                $archivosValidos[] = [
                    'reporte' => $reporte,
                    'ruta' => $rutaArchivo,
                    'tamano' => 0,
                    'existe' => false
                ];
            }
        }

        return view('contracts/previsualizar_documentacion', [
            'contract' => $contract,
            'archivos' => $archivosValidos,
            'tamanoTotal' => $tamanoTotal,
            'totalReportes' => count($reportes),
            'archivosExistentes' => count(array_filter($archivosValidos, fn($a) => $a['existe']))
        ]);
    }

    /**
     * Vista previa de documentación por ID de cliente (busca el último contrato)
     */
    public function previsualizarPorCliente($idCliente)
    {
        $session = session();
        $role = $session->get('role');
        $idConsultor = $session->get('id_consultor');

        // Obtener cliente
        $client = $this->clientModel->find($idCliente);
        if (!$client) {
            return redirect()->to('/reportList')->with('msg', 'Cliente no encontrado');
        }

        // Verificar permisos
        if ($role === 'consultor' && $client['id_consultor'] != $idConsultor) {
            return redirect()->to('/reportList')->with('msg', 'No tiene permisos para este cliente');
        }

        // Obtener el último contrato del cliente
        $contract = $this->contractModel
            ->where('id_cliente', $idCliente)
            ->orderBy('fecha_fin', 'DESC')
            ->first();

        if (!$contract) {
            return redirect()->to('/reportList')->with('msg', 'El cliente no tiene contratos registrados');
        }

        // Agregar datos del cliente al contrato
        $contract['nombre_cliente'] = $client['nombre_cliente'];
        $contract['nit_cliente'] = $client['nit_cliente'];
        $contract['id_consultor'] = $client['id_consultor'];

        // Obtener reportes del cliente dentro del rango de fechas del contrato
        $reportes = $this->reporteModel
            ->select('tbl_reporte.*, tbl_detailreport.detail_report_name, tbl_report_type.report_type_name')
            ->join('tbl_detailreport', 'tbl_detailreport.id_detailreport = tbl_reporte.id_detailreport', 'left')
            ->join('tbl_report_type', 'tbl_report_type.id_report_type = tbl_reporte.id_report_type', 'left')
            ->where('tbl_reporte.id_cliente', $idCliente)
            ->where('DATE(tbl_reporte.created_at) >=', $contract['fecha_inicio'])
            ->where('DATE(tbl_reporte.created_at) <=', $contract['fecha_fin'])
            ->orderBy('tbl_reporte.created_at', 'ASC')
            ->findAll();

        // Verificar cuáles archivos existen físicamente
        $archivosValidos = [];
        $tamanoTotal = 0;

        foreach ($reportes as $reporte) {
            $enlace = $reporte['enlace'];
            $rutaRelativa = str_replace(base_url(), '', $enlace);
            $rutaRelativa = ltrim($rutaRelativa, '/');
            $rutaArchivo = FCPATH . $rutaRelativa;

            if (file_exists($rutaArchivo)) {
                $tamano = filesize($rutaArchivo);
                $tamanoTotal += $tamano;
                $archivosValidos[] = [
                    'reporte' => $reporte,
                    'ruta' => $rutaArchivo,
                    'tamano' => $tamano,
                    'existe' => true
                ];
            } else {
                $archivosValidos[] = [
                    'reporte' => $reporte,
                    'ruta' => $rutaArchivo,
                    'tamano' => 0,
                    'existe' => false
                ];
            }
        }

        return view('contracts/previsualizar_documentacion', [
            'contract' => $contract,
            'archivos' => $archivosValidos,
            'tamanoTotal' => $tamanoTotal,
            'totalReportes' => count($reportes),
            'archivosExistentes' => count(array_filter($archivosValidos, fn($a) => $a['existe'])),
            'fromReportList' => true
        ]);
    }

    /**
     * Descarga documentación por ID de cliente (busca el último contrato)
     */
    public function descargarPorCliente($idCliente)
    {
        $session = session();
        $role = $session->get('role');
        $idConsultor = $session->get('id_consultor');

        // Obtener cliente
        $client = $this->clientModel->find($idCliente);
        if (!$client) {
            return redirect()->to('/reportList')->with('msg', 'Cliente no encontrado');
        }

        // Verificar permisos
        if ($role === 'consultor' && $client['id_consultor'] != $idConsultor) {
            return redirect()->to('/reportList')->with('msg', 'No tiene permisos para este cliente');
        }

        // Obtener el último contrato del cliente
        $contract = $this->contractModel
            ->where('id_cliente', $idCliente)
            ->orderBy('fecha_fin', 'DESC')
            ->first();

        if (!$contract) {
            return redirect()->to('/reportList')->with('msg', 'El cliente no tiene contratos registrados');
        }

        // Obtener reportes del cliente dentro del rango de fechas
        $reportes = $this->reporteModel
            ->where('id_cliente', $idCliente)
            ->where('DATE(created_at) >=', $contract['fecha_inicio'])
            ->where('DATE(created_at) <=', $contract['fecha_fin'])
            ->orderBy('created_at', 'ASC')
            ->findAll();

        if (empty($reportes)) {
            return redirect()->to('/reportList')->with('msg', 'No hay documentos en el período del contrato');
        }

        // Recolectar archivos existentes
        $archivosParaZip = [];
        foreach ($reportes as $reporte) {
            $enlace = $reporte['enlace'];
            $rutaRelativa = str_replace(base_url(), '', $enlace);
            $rutaRelativa = ltrim($rutaRelativa, '/');
            $rutaArchivo = FCPATH . $rutaRelativa;

            if (file_exists($rutaArchivo)) {
                $archivosParaZip[] = [
                    'ruta' => $rutaArchivo,
                    'nombre' => $reporte['titulo_reporte'] . '_' . date('Y-m-d', strtotime($reporte['created_at'])) . '.' . pathinfo($rutaArchivo, PATHINFO_EXTENSION)
                ];
            }
        }

        if (empty($archivosParaZip)) {
            return redirect()->to('/reportList')->with('msg', 'No se encontraron archivos físicos para descargar');
        }

        // Crear ZIP
        $nombreCliente = preg_replace('/[^a-zA-Z0-9]/', '_', $client['nombre_cliente']);
        $nombreZip = 'Documentacion_' . $nombreCliente . '_' . $contract['numero_contrato'] . '_' . date('Y-m-d') . '.zip';
        $rutaZip = WRITEPATH . 'uploads/' . $nombreZip;

        if (!is_dir(WRITEPATH . 'uploads')) {
            mkdir(WRITEPATH . 'uploads', 0755, true);
        }

        $zip = new ZipArchive();
        if ($zip->open($rutaZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return redirect()->to('/reportList')->with('msg', 'No se pudo crear el archivo ZIP');
        }

        $contador = [];
        foreach ($archivosParaZip as $archivo) {
            $nombreEnZip = $archivo['nombre'];
            if (isset($contador[$nombreEnZip])) {
                $contador[$nombreEnZip]++;
                $partes = pathinfo($nombreEnZip);
                $nombreEnZip = $partes['filename'] . '_' . $contador[$nombreEnZip] . '.' . $partes['extension'];
            } else {
                $contador[$nombreEnZip] = 1;
            }
            $zip->addFile($archivo['ruta'], $nombreEnZip);
        }

        $zip->close();

        if (!file_exists($rutaZip)) {
            return redirect()->to('/reportList')->with('msg', 'Error al crear el archivo ZIP');
        }

        register_shutdown_function(function() use ($rutaZip) {
            if (file_exists($rutaZip)) {
                @unlink($rutaZip);
            }
        });

        return $this->response->download($rutaZip, null)->setFileName($nombreZip);
    }

    /**
     * Descarga todos los documentos del contrato en un ZIP
     */
    public function descargarDocumentacion($idContrato)
    {
        $session = session();
        $role = $session->get('role');
        $idConsultor = $session->get('id_consultor');

        // Obtener contrato con datos del cliente
        $contract = $this->contractModel
            ->select('tbl_contratos.*, tbl_clientes.nombre_cliente, tbl_clientes.nit_cliente, tbl_clientes.id_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_contratos.id_cliente')
            ->find($idContrato);

        if (!$contract) {
            return redirect()->back()->with('error', 'Contrato no encontrado');
        }

        // Verificar permisos
        if ($role === 'consultor' && $contract['id_consultor'] != $idConsultor) {
            return redirect()->back()->with('error', 'No tiene permisos');
        }

        // Obtener reportes del cliente dentro del rango de fechas
        $reportes = $this->reporteModel
            ->where('id_cliente', $contract['id_cliente'])
            ->where('DATE(created_at) >=', $contract['fecha_inicio'])
            ->where('DATE(created_at) <=', $contract['fecha_fin'])
            ->orderBy('created_at', 'ASC')
            ->findAll();

        if (empty($reportes)) {
            return redirect()->back()->with('warning', 'No hay documentos en el período del contrato');
        }

        // Recolectar archivos existentes
        $archivosParaZip = [];
        foreach ($reportes as $reporte) {
            $enlace = $reporte['enlace'];
            $rutaRelativa = str_replace(base_url(), '', $enlace);
            $rutaRelativa = ltrim($rutaRelativa, '/');
            $rutaArchivo = FCPATH . $rutaRelativa;

            if (file_exists($rutaArchivo)) {
                $archivosParaZip[] = [
                    'ruta' => $rutaArchivo,
                    'nombre' => $reporte['titulo_reporte'] . '_' . date('Y-m-d', strtotime($reporte['created_at'])) . '.' . pathinfo($rutaArchivo, PATHINFO_EXTENSION)
                ];
            }
        }

        if (empty($archivosParaZip)) {
            return redirect()->back()->with('warning', 'No se encontraron archivos físicos para descargar');
        }

        // Crear ZIP
        $nombreCliente = preg_replace('/[^a-zA-Z0-9]/', '_', $contract['nombre_cliente']);
        $nombreZip = 'Documentacion_' . $nombreCliente . '_' . $contract['numero_contrato'] . '_' . date('Y-m-d') . '.zip';
        $rutaZip = WRITEPATH . 'uploads/' . $nombreZip;

        if (!is_dir(WRITEPATH . 'uploads')) {
            mkdir(WRITEPATH . 'uploads', 0755, true);
        }

        $zip = new ZipArchive();
        if ($zip->open($rutaZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return redirect()->back()->with('error', 'No se pudo crear el archivo ZIP');
        }

        // Agregar archivos al ZIP con nombres descriptivos
        $contador = [];
        foreach ($archivosParaZip as $archivo) {
            $nombreEnZip = $archivo['nombre'];
            // Evitar nombres duplicados
            if (isset($contador[$nombreEnZip])) {
                $contador[$nombreEnZip]++;
                $partes = pathinfo($nombreEnZip);
                $nombreEnZip = $partes['filename'] . '_' . $contador[$nombreEnZip] . '.' . $partes['extension'];
            } else {
                $contador[$nombreEnZip] = 1;
            }
            $zip->addFile($archivo['ruta'], $nombreEnZip);
        }

        $zip->close();

        if (!file_exists($rutaZip)) {
            return redirect()->back()->with('error', 'Error al crear el archivo ZIP');
        }

        // Programar eliminación del ZIP temporal
        register_shutdown_function(function() use ($rutaZip) {
            if (file_exists($rutaZip)) {
                @unlink($rutaZip);
            }
        });

        return $this->response->download($rutaZip, null)->setFileName($nombreZip);
    }
}
