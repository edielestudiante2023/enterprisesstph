<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ContractModel;
use App\Models\ClientPoliciesModel;
use App\Models\DocumentVersionModel;
use App\Models\PolicyTypeModel;
use CodeIgniter\Controller;
use CodeIgniter\I18n\Time;
use Dompdf\Dompdf;
use Dompdf\Options;
use setasign\Fpdi\Tcpdf\Fpdi;

/**
 * PdfUnificadoController - Genera un PDF unificado con todos los documentos del SG-SST
 */
class PdfUnificadoController extends Controller
{
    private $clientModel;
    private $consultantModel;
    private $contractModel;
    private $clientPoliciesModel;
    private $versionModel;
    private $policyTypeModel;

    /**
     * Mapeo de id_acceso a policy_type_id y vista
     */
    private $documentMapping = [
        1 => ['policy_type_id' => 1, 'view' => 'client/sgsst/1planear/p1_1_1asignacion_responsable', 'nombre' => 'Asignación de Responsable'],
        2 => ['policy_type_id' => 2, 'view' => 'client/sgsst/1planear/p1_1_2asignacion_responsabilidades', 'nombre' => 'Asignación de Responsabilidades'],
        3 => ['policy_type_id' => 3, 'view' => 'client/sgsst/1planear/p1_1_3vigia', 'nombre' => 'Asignación de Vigía'],
        4 => ['policy_type_id' => 4, 'view' => 'client/sgsst/1planear/p1_1_4exoneracion_cocolab', 'nombre' => 'Exoneración COCOLAB'],
        5 => ['policy_type_id' => 5, 'view' => 'client/sgsst/1planear/p1_1_5registro_asistencia', 'nombre' => 'Registro de Asistencia'],
        15 => ['policy_type_id' => 15, 'view' => 'client/sgsst/1planear/p1_2_1prgcapacitacion', 'nombre' => 'Programa de Capacitación'],
        16 => ['policy_type_id' => 16, 'view' => 'client/sgsst/1planear/p1_2_2prginduccion', 'nombre' => 'Programa de Inducción'],
        17 => ['policy_type_id' => 17, 'view' => 'client/sgsst/1planear/p1_2_3ftevaluacioninduccion', 'nombre' => 'Evaluación de Inducción'],
        18 => ['policy_type_id' => 18, 'view' => 'client/sgsst/1planear/p2_1_1politicasst', 'nombre' => 'Política de SST'],
        19 => ['policy_type_id' => 19, 'view' => 'client/sgsst/1planear/p2_1_2politicaalcohol', 'nombre' => 'Política de Alcohol'],
        20 => ['policy_type_id' => 20, 'view' => 'client/sgsst/1planear/p2_1_3politicaemergencias', 'nombre' => 'Política de Emergencias'],
        21 => ['policy_type_id' => 21, 'view' => 'client/sgsst/1planear/p2_1_4politicaepps', 'nombre' => 'Política de EPPs'],
        23 => ['policy_type_id' => 23, 'view' => 'client/sgsst/1planear/p2_1_6reghigsegind', 'nombre' => 'Reglamento de Higiene'],
        24 => ['policy_type_id' => 24, 'view' => 'client/sgsst/1planear/p2_2_1objetivos', 'nombre' => 'Objetivos del SG-SST'],
        25 => ['policy_type_id' => 25, 'view' => 'client/sgsst/1planear/p2_5_1documentacion', 'nombre' => 'Documentos del SG-SST'],
        26 => ['policy_type_id' => 26, 'view' => 'client/sgsst/1planear/p2_5_2rendiciondecuentas', 'nombre' => 'Rendición de Cuentas'],
        28 => ['policy_type_id' => 28, 'view' => 'client/sgsst/1planear/p2_5_4manproveedores', 'nombre' => 'Manual de Proveedores'],
        31 => ['policy_type_id' => 31, 'view' => 'client/sgsst/1planear/h1_1_3repoaccidente', 'nombre' => 'Reporte de Accidente'],
        36 => ['policy_type_id' => 36, 'view' => 'client/sgsst/1planear/h1_1_7identfpeligriesg', 'nombre' => 'Identificación de Peligros'],
    ];

    public function __construct()
    {
        $this->clientModel = new ClientModel();
        $this->consultantModel = new ConsultantModel();
        $this->contractModel = new ContractModel();
        $this->clientPoliciesModel = new ClientPoliciesModel();
        $this->versionModel = new DocumentVersionModel();
        $this->policyTypeModel = new PolicyTypeModel();
    }

    /**
     * Muestra la página de generación de PDF unificado
     */
    public function index($idClienteParam = null)
    {
        helper('access_library');

        $session = session();

        // Si viene parámetro y el usuario es consultant/admin, usar ese ID
        $role = $session->get('role');
        if ($idClienteParam && in_array($role, ['consultant', 'admin'])) {
            $clientId = $idClienteParam;
        } else {
            $clientId = $session->get('user_id');
        }

        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Cliente no autenticado.');
        }

        $client = $this->clientModel->find($clientId);

        if (!$client) {
            return redirect()->to('/login')->with('error', 'Cliente no encontrado.');
        }

        // Obtener accesos según el estándar del cliente
        $estandarNombre = $client['estandares'];
        $accesos = get_accesses_by_standard($estandarNombre);

        // Ordenar por dimensión PHVA
        $orden = ["Planear", "Hacer", "Verificar", "Actuar", "Indicadores"];
        usort($accesos, function ($a, $b) use ($orden) {
            return array_search($a['dimension'], $orden) - array_search($b['dimension'], $orden);
        });

        // Filtrar solo los que tienen PDF disponible (excluir indicadores)
        $accesosConPdf = array_filter($accesos, function($acceso) {
            return isset($this->documentMapping[$acceso['id_acceso']]) && $acceso['dimension'] !== 'Indicadores';
        });

        return view('client/pdf_unificado', [
            'client' => $client,
            'accesos' => $accesosConPdf,
            'totalDocumentos' => count($accesosConPdf)
        ]);
    }

    /**
     * Genera el PDF unificado con todos los documentos
     */
    public function generarPdfUnificado()
    {
        set_time_limit(600); // 10 minutos máximo
        ini_set('memory_limit', '1024M');

        helper('access_library');

        $session = session();
        $clientId = $session->get('user_id');

        if (!$clientId) {
            return redirect()->to('/login')->with('error', 'Cliente no autenticado.');
        }

        $client = $this->clientModel->find($clientId);

        if (!$client) {
            return redirect()->to('/login')->with('error', 'Cliente no encontrado.');
        }

        $consultant = $this->consultantModel->find($client['id_consultor']);
        $firstContractDate = $this->contractModel->getFirstContractDate($clientId);

        // Obtener accesos según el estándar del cliente
        $estandarNombre = $client['estandares'];
        $accesos = get_accesses_by_standard($estandarNombre);

        // Ordenar por dimensión PHVA
        $orden = ["Planear", "Hacer", "Verificar", "Actuar", "Indicadores"];
        usort($accesos, function ($a, $b) use ($orden) {
            return array_search($a['dimension'], $orden) - array_search($b['dimension'], $orden);
        });

        // Filtrar solo los que tienen PDF disponible (excluir indicadores)
        $accesosConPdf = array_filter($accesos, function($acceso) {
            return isset($this->documentMapping[$acceso['id_acceso']]) && $acceso['dimension'] !== 'Indicadores';
        });

        // Directorio temporal para PDFs individuales
        $tempDir = WRITEPATH . 'uploads/temp_pdfs/';
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $pdfFiles = [];
        $errores = [];

        // Generar cada PDF individualmente
        foreach ($accesosConPdf as $acceso) {
            $idAcceso = $acceso['id_acceso'];

            if (!isset($this->documentMapping[$idAcceso])) {
                continue;
            }

            $mapping = $this->documentMapping[$idAcceso];
            $pdfPath = $tempDir . 'doc_' . $idAcceso . '_' . uniqid() . '.pdf';

            try {
                // Generar el PDF directamente
                $pdfContent = $this->generarPdfDirecto($idAcceso, $clientId, $client, $consultant, $firstContractDate);

                if ($pdfContent) {
                    file_put_contents($pdfPath, $pdfContent);
                    $pdfFiles[] = [
                        'path' => $pdfPath,
                        'nombre' => $mapping['nombre'],
                        'id_acceso' => $idAcceso
                    ];
                }
            } catch (\Exception $e) {
                $errores[] = $mapping['nombre'] . ': ' . $e->getMessage();
                log_message('error', 'Error generando PDF para acceso ' . $idAcceso . ': ' . $e->getMessage());
            }
        }

        if (empty($pdfFiles)) {
            $this->limpiarDirectorioTemp($tempDir);
            $errorMsg = 'No se pudo generar ningún documento PDF.';
            if (!empty($errores)) {
                $errorMsg .= ' Errores: ' . implode(', ', array_slice($errores, 0, 3));
            }
            return redirect()->back()->with('error', $errorMsg);
        }

        // Fusionar todos los PDFs
        try {
            $pdfFinal = $this->fusionarPdfs($pdfFiles);

            // Limpiar archivos temporales
            $this->limpiarDirectorioTemp($tempDir);

            // Enviar el PDF fusionado
            $nombreArchivo = 'SG-SST_' . preg_replace('/[^a-zA-Z0-9]/', '_', $client['nombre_cliente']) . '_' . date('Y-m-d') . '.pdf';

            return $this->response
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $nombreArchivo . '"')
                ->setBody($pdfFinal);

        } catch (\Exception $e) {
            $this->limpiarDirectorioTemp($tempDir);
            log_message('error', 'Error fusionando PDFs: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al fusionar los documentos: ' . $e->getMessage());
        }
    }

    /**
     * Genera un PDF directamente sin llamadas HTTP
     */
    private function generarPdfDirecto($idAcceso, $clientId, $client, $consultant, $firstContractDate)
    {
        if (!isset($this->documentMapping[$idAcceso])) {
            return null;
        }

        $mapping = $this->documentMapping[$idAcceso];
        $policyTypeId = $mapping['policy_type_id'];
        $viewPath = $mapping['view'];

        // Obtener datos del documento
        $clientPolicy = $this->clientPoliciesModel->where('client_id', $clientId)
            ->where('policy_type_id', $policyTypeId)
            ->orderBy('id', 'DESC')
            ->first();

        if (!$clientPolicy) {
            return null;
        }

        $policyType = $this->policyTypeModel->find($policyTypeId);

        $latestVersion = $this->versionModel->where('client_id', $clientId)
            ->where('policy_type_id', $policyTypeId)
            ->orderBy('created_at', 'DESC')
            ->first();

        if (!$latestVersion) {
            return null;
        }

        $allVersions = $this->versionModel->where('client_id', $clientId)
            ->where('policy_type_id', $policyTypeId)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        // Sobrescribir fechas con la del primer contrato
        if ($firstContractDate) {
            $latestVersion['created_at'] = $firstContractDate;
            foreach ($allVersions as &$version) {
                $version['created_at'] = $firstContractDate;
            }
            unset($version);
        } else {
            $latestVersion['created_at'] = null;
            $latestVersion['sin_contrato'] = true;
            foreach ($allVersions as &$version) {
                $version['created_at'] = null;
                $version['sin_contrato'] = true;
            }
            unset($version);
        }

        // Formatear fecha para mostrar
        if ($latestVersion['created_at']) {
            $latestVersion['created_at'] = Time::parse($latestVersion['created_at'], 'America/Bogota')
                ->toLocalizedString('d MMMM yyyy');
        } elseif (isset($latestVersion['sin_contrato'])) {
            $latestVersion['created_at'] = 'PENDIENTE DE CONTRATO';
        }

        // Preparar datos para la vista
        $data = [
            'client' => $client,
            'consultant' => $consultant,
            'clientPolicy' => $clientPolicy,
            'policyType' => $policyType,
            'latestVersion' => $latestVersion,
            'allVersions' => $allVersions,
        ];

        // Verificar si la vista existe
        if (!file_exists(APPPATH . 'Views/' . $viewPath . '.php')) {
            log_message('warning', 'Vista no encontrada: ' . $viewPath);
            return null;
        }

        // Renderizar la vista
        $html = view($viewPath, $data);

        // Generar PDF con Dompdf
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    /**
     * Fusiona múltiples PDFs en uno solo usando FPDI
     */
    private function fusionarPdfs($pdfFiles)
    {
        $pdf = new Fpdi();
        $pdf->setAutoPageBreak(false);

        foreach ($pdfFiles as $pdfFile) {
            try {
                $pageCount = $pdf->setSourceFile($pdfFile['path']);

                for ($i = 1; $i <= $pageCount; $i++) {
                    $templateId = $pdf->importPage($i);
                    $size = $pdf->getTemplateSize($templateId);

                    $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $pdf->useTemplate($templateId);
                }
            } catch (\Exception $e) {
                log_message('error', 'Error importando PDF ' . $pdfFile['nombre'] . ': ' . $e->getMessage());
                continue;
            }
        }

        return $pdf->Output('', 'S');
    }

    /**
     * Limpia el directorio temporal de PDFs
     */
    private function limpiarDirectorioTemp($dir)
    {
        if (is_dir($dir)) {
            $files = glob($dir . '*.pdf');
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }
    }
}
