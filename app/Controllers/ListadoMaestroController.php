<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class ListadoMaestroController extends BaseController
{
    protected $db;
    protected $session;

    protected $datosDocumento = [
        'codigo'  => 'FT-SST-020',
        'nombre'  => 'FORMATO LISTADO MAESTRO DE DOCUMENTOS Y REGISTROS',
        'version' => '001'
    ];

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = \Config\Services::session();
    }

    /**
     * Vista selector de cliente
     */
    public function seleccionar()
    {
        $clientes = $this->db->table('tbl_clientes')
            ->select('id_cliente, nombre_cliente, nit_cliente')
            ->orderBy('nombre_cliente', 'ASC')
            ->get()->getResultArray();

        return view('listado_maestro/seleccionar_cliente', ['clientes' => $clientes]);
    }

    /**
     * Vista principal del listado maestro por cliente
     */
    public function index($idCliente)
    {
        $cliente = $this->db->table('tbl_clientes')
            ->where('id_cliente', $idCliente)
            ->get()->getRowArray();

        if (!$cliente) {
            return redirect()->to(base_url('listado-maestro'))->with('error', 'Cliente no encontrado');
        }

        $documentos = $this->db->table('tbl_listado_maestro_documentos')
            ->where('activo', 1)
            ->orderBy('orden', 'ASC')
            ->get()->getResultArray();

        return view('listado_maestro/index', [
            'cliente'           => $cliente,
            'documentos'        => $documentos,
            'codigoDocumento'   => $this->datosDocumento['codigo'],
            'versionDocumento'  => $this->datosDocumento['version'],
            'tituloDocumento'   => $this->datosDocumento['nombre'],
        ]);
    }

    /**
     * Exportar a PDF (DOMPDF, landscape)
     */
    public function exportarPdf($idCliente)
    {
        $cliente = $this->db->table('tbl_clientes')
            ->where('id_cliente', $idCliente)
            ->get()->getRowArray();

        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $documentos = $this->db->table('tbl_listado_maestro_documentos')
            ->where('activo', 1)
            ->orderBy('orden', 'ASC')
            ->get()->getResultArray();

        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoBase64 = 'data:' . mime_content_type($logoPath) . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        $html = view('listado_maestro/pdf', [
            'cliente'           => $cliente,
            'documentos'        => $documentos,
            'logoBase64'        => $logoBase64,
            'codigoDocumento'   => $this->datosDocumento['codigo'],
            'versionDocumento'  => $this->datosDocumento['version'],
            'tituloDocumento'   => $this->datosDocumento['nombre'],
        ]);

        $dompdf = new \Dompdf\Dompdf(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'landscape');
        $dompdf->render();

        $filename = "FT-SST-020_Listado_Maestro_" . preg_replace('/[^a-zA-Z0-9]/', '_', $cliente['nombre_cliente']) . ".pdf";

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }

    /**
     * Exportar a Excel (PhpSpreadsheet)
     */
    public function exportarExcel($idCliente)
    {
        $cliente = $this->db->table('tbl_clientes')
            ->where('id_cliente', $idCliente)
            ->get()->getRowArray();

        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $documentos = $this->db->table('tbl_listado_maestro_documentos')
            ->where('activo', 1)
            ->orderBy('orden', 'ASC')
            ->get()->getResultArray();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Listado Maestro');

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1a5f7a']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $subHeaderStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2c3e50']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $dataStyle = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ];

        // Row 1: Title
        $sheet->setCellValue('A1', 'SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO');
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1:I1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Row 2: Document name
        $sheet->setCellValue('A2', $this->datosDocumento['nombre']);
        $sheet->mergeCells('A2:I2');
        $sheet->getStyle('A2:I2')->applyFromArray($headerStyle);
        $sheet->getRowDimension(2)->setRowHeight(25);

        // Row 3: Client info
        $sheet->setCellValue('A3', 'Empresa: ' . $cliente['nombre_cliente'] . ' | NIT: ' . ($cliente['nit_cliente'] ?? 'N/A') . ' | Codigo: ' . $this->datosDocumento['codigo'] . ' | Version: ' . $this->datosDocumento['version']);
        $sheet->mergeCells('A3:I3');
        $sheet->getStyle('A3')->getFont()->setBold(true);

        // Row 5: Column headers
        $row = 5;
        $headers = ['ID', 'TIPO DE DOCUMENTO', 'CODIGO', 'NOMBRE DEL DOCUMENTO', 'VERSION', 'UBICACION', 'FECHA', 'ESTADO', 'CONTROL DE CAMBIOS'];
        foreach ($headers as $col => $header) {
            $colLetter = chr(65 + $col);
            $sheet->setCellValue($colLetter . $row, $header);
        }
        $sheet->getStyle("A{$row}:I{$row}")->applyFromArray($subHeaderStyle);
        $sheet->getRowDimension($row)->setRowHeight(20);

        // Data rows
        $row = 6;
        $idx = 1;
        foreach ($documentos as $doc) {
            $sheet->setCellValue('A' . $row, $idx);
            $sheet->setCellValue('B' . $row, $doc['tipo_documento']);
            $sheet->setCellValue('C' . $row, $doc['codigo']);
            $sheet->setCellValue('D' . $row, $doc['nombre_documento']);
            $sheet->setCellValue('E' . $row, $doc['version']);
            $sheet->setCellValue('F' . $row, $doc['ubicacion']);
            $sheet->setCellValue('G' . $row, $doc['fecha'] ? date('d/m/Y', strtotime($doc['fecha'])) : '');
            $sheet->setCellValue('H' . $row, $doc['estado']);
            $sheet->setCellValue('I' . $row, $doc['control_cambios'] ?? '');
            $sheet->getStyle("A{$row}:I{$row}")->applyFromArray($dataStyle);
            $row++;
            $idx++;
        }

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(16);
        $sheet->getColumnDimension('D')->setWidth(55);
        $sheet->getColumnDimension('E')->setWidth(10);
        $sheet->getColumnDimension('F')->setWidth(25);
        $sheet->getColumnDimension('G')->setWidth(14);
        $sheet->getColumnDimension('H')->setWidth(12);
        $sheet->getColumnDimension('I')->setWidth(25);

        $filename = "FT-SST-020_Listado_Maestro_" . preg_replace('/[^a-zA-Z0-9]/', '_', $cliente['nombre_cliente']) . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Vista de matrices disponibles para un cliente
     */
    public function matrices($idCliente)
    {
        $cliente = $this->db->table('tbl_clientes')
            ->where('id_cliente', $idCliente)
            ->get()->getRowArray();

        if (!$cliente) {
            return redirect()->to(base_url('listado-maestro'))->with('error', 'Cliente no encontrado');
        }

        $contrato = $this->db->table('tbl_contratos')
            ->where('id_cliente', $idCliente)
            ->where('estado', 'activo')
            ->orderBy('fecha_inicio', 'DESC')
            ->get()->getRowArray();

        return view('listado_maestro/matrices', [
            'cliente'  => $cliente,
            'contrato' => $contrato,
        ]);
    }

    /**
     * Generar Matriz EPP personalizada con logo y datos del cliente
     */
    public function generarMatrizEpp($idCliente)
    {
        $cliente = $this->db->table('tbl_clientes')
            ->where('id_cliente', $idCliente)
            ->get()->getRowArray();

        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $contrato = $this->db->table('tbl_contratos')
            ->where('id_cliente', $idCliente)
            ->where('estado', 'activo')
            ->orderBy('fecha_inicio', 'DESC')
            ->get()->getRowArray();

        $templatePath = APPPATH . 'Templates/matrices/MATRIZ ELEMENTOS DE PROTECCION PROPIEDAD HORIZONTAL.xlsx';
        if (!file_exists($templatePath)) {
            return redirect()->back()->with('error', 'Plantilla EPP no encontrada');
        }

        $spreadsheet = IOFactory::load($templatePath);

        // Preparar logo del cliente
        $logoPath = null;
        if (!empty($cliente['logo'])) {
            $fullLogoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($fullLogoPath)) {
                $logoPath = $fullLogoPath;
            }
        }

        // Fecha del contrato
        $fechaContrato = '';
        if (!empty($contrato['fecha_inicio'])) {
            setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'Spanish_Spain');
            $ts = strtotime($contrato['fecha_inicio']);
            $fechaContrato = date('j', $ts) . ' de ' . $this->getMesEspanol((int)date('n', $ts)) . ' ' . date('Y', $ts);
        }

        // Recorrer todas las hojas
        for ($i = 0; $i < $spreadsheet->getSheetCount(); $i++) {
            $sheet = $spreadsheet->getSheet($i);

            // Remover imagen existente en A1 y poner la del cliente
            if ($logoPath) {
                $this->reemplazarImagenEnCelda($sheet, 'A1', $logoPath, 100, 60);
            }

            // Insertar nombre del cliente en D2 (debajo del título del sistema)
            $sheet->setCellValue('D2', $cliente['nombre_cliente']);

            // Insertar fecha del contrato en D3
            if ($fechaContrato) {
                $sheet->setCellValue('D3', 'Fecha: ' . $fechaContrato);
            }
        }

        $nombreLimpio = preg_replace('/[^a-zA-Z0-9]/', '_', $cliente['nombre_cliente']);
        $filename = "MT-SST-002_Matriz_EPP_{$nombreLimpio}.xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Generar Matriz de Peligros personalizada con logo y datos del cliente
     */
    public function generarMatrizPeligros($idCliente)
    {
        $cliente = $this->db->table('tbl_clientes')
            ->where('id_cliente', $idCliente)
            ->get()->getRowArray();

        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $contrato = $this->db->table('tbl_contratos')
            ->where('id_cliente', $idCliente)
            ->where('estado', 'activo')
            ->orderBy('fecha_inicio', 'DESC')
            ->get()->getRowArray();

        $templatePath = APPPATH . 'Templates/matrices/MT-SST-001 MATRIZ DE PELIGROS.xlsx';
        if (!file_exists($templatePath)) {
            return redirect()->back()->with('error', 'Plantilla Peligros no encontrada');
        }

        $spreadsheet = IOFactory::load($templatePath);

        // Preparar logo del cliente
        $logoPath = null;
        if (!empty($cliente['logo'])) {
            $fullLogoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($fullLogoPath)) {
                $logoPath = $fullLogoPath;
            }
        }

        // Fecha del contrato
        $fechaContrato = '';
        if (!empty($contrato['fecha_inicio'])) {
            $ts = strtotime($contrato['fecha_inicio']);
            $fechaContrato = date('j', $ts) . ' de ' . $this->getMesEspanol((int)date('n', $ts)) . ' ' . date('Y', $ts);
        }

        // Hojas de datos son las primeras 5 (ADMINISTRACIÓN, SERVICIOS GENERALES, OPERACIONES, SEGURIDAD, PARQUEADERO)
        $hojasConDatos = min(5, $spreadsheet->getSheetCount());

        for ($i = 0; $i < $hojasConDatos; $i++) {
            $sheet = $spreadsheet->getSheet($i);

            // Reemplazar logo en A1
            if ($logoPath) {
                $this->reemplazarImagenEnCelda($sheet, 'A1', $logoPath, 100, 60);
            }

            // Solo en la primera hoja (ADMINISTRACIÓN) escribir nombre — las demás usan fórmula
            if ($i === 0) {
                $sheet->setCellValue('C1', $cliente['nombre_cliente']);
            }

            // Actualizar fecha de revisión en A5
            if ($fechaContrato) {
                $sheet->setCellValue('A5', 'FECHA DE REVISIÓN: ' . $fechaContrato);
            }
        }

        $nombreLimpio = preg_replace('/[^a-zA-Z0-9]/', '_', $cliente['nombre_cliente']);
        $filename = "MT-SST-001_Matriz_Peligros_{$nombreLimpio}.xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Generar ambas matrices a la vez (ZIP)
     */
    public function generarTodasMatrices($idCliente)
    {
        $cliente = $this->db->table('tbl_clientes')
            ->where('id_cliente', $idCliente)
            ->get()->getRowArray();

        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        // Generar ambos archivos en temp
        $tmpDir = sys_get_temp_dir();
        $nombreLimpio = preg_replace('/[^a-zA-Z0-9]/', '_', $cliente['nombre_cliente']);

        $archivos = [];

        // EPP
        $archivos[] = $this->generarMatrizEnTemp($idCliente, 'epp', $tmpDir, $nombreLimpio);
        // Peligros
        $archivos[] = $this->generarMatrizEnTemp($idCliente, 'peligros', $tmpDir, $nombreLimpio);

        // Crear ZIP
        $zipName = "Matrices_SST_{$nombreLimpio}.zip";
        $zipPath = $tmpDir . '/' . $zipName;

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return redirect()->back()->with('error', 'No se pudo crear el ZIP');
        }

        foreach ($archivos as $archivo) {
            if ($archivo && file_exists($archivo['path'])) {
                $zip->addFile($archivo['path'], $archivo['name']);
            }
        }
        $zip->close();

        // Limpiar temporales
        foreach ($archivos as $archivo) {
            if ($archivo && file_exists($archivo['path'])) {
                unlink($archivo['path']);
            }
        }

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zipName . '"');
        header('Content-Length: ' . filesize($zipPath));
        readfile($zipPath);
        unlink($zipPath);
        exit;
    }

    // ─── Helpers privados ───

    /**
     * Genera una matriz en archivo temporal y retorna path + nombre
     */
    private function generarMatrizEnTemp(int $idCliente, string $tipo, string $tmpDir, string $nombreLimpio): ?array
    {
        $cliente = $this->db->table('tbl_clientes')
            ->where('id_cliente', $idCliente)
            ->get()->getRowArray();

        $contrato = $this->db->table('tbl_contratos')
            ->where('id_cliente', $idCliente)
            ->where('estado', 'activo')
            ->orderBy('fecha_inicio', 'DESC')
            ->get()->getRowArray();

        $logoPath = null;
        if (!empty($cliente['logo'])) {
            $fullLogoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($fullLogoPath)) {
                $logoPath = $fullLogoPath;
            }
        }

        $fechaContrato = '';
        if (!empty($contrato['fecha_inicio'])) {
            $ts = strtotime($contrato['fecha_inicio']);
            $fechaContrato = date('j', $ts) . ' de ' . $this->getMesEspanol((int)date('n', $ts)) . ' ' . date('Y', $ts);
        }

        if ($tipo === 'epp') {
            $templatePath = APPPATH . 'Templates/matrices/MATRIZ ELEMENTOS DE PROTECCION PROPIEDAD HORIZONTAL.xlsx';
            $fileName = "MT-SST-002_Matriz_EPP_{$nombreLimpio}.xlsx";
        } else {
            $templatePath = APPPATH . 'Templates/matrices/MT-SST-001 MATRIZ DE PELIGROS.xlsx';
            $fileName = "MT-SST-001_Matriz_Peligros_{$nombreLimpio}.xlsx";
        }

        if (!file_exists($templatePath)) return null;

        $spreadsheet = IOFactory::load($templatePath);

        if ($tipo === 'epp') {
            for ($i = 0; $i < $spreadsheet->getSheetCount(); $i++) {
                $sheet = $spreadsheet->getSheet($i);
                if ($logoPath) $this->reemplazarImagenEnCelda($sheet, 'A1', $logoPath, 100, 60);
                $sheet->setCellValue('D2', $cliente['nombre_cliente']);
                if ($fechaContrato) $sheet->setCellValue('D3', 'Fecha: ' . $fechaContrato);
            }
        } else {
            $hojasConDatos = min(5, $spreadsheet->getSheetCount());
            for ($i = 0; $i < $hojasConDatos; $i++) {
                $sheet = $spreadsheet->getSheet($i);
                if ($logoPath) $this->reemplazarImagenEnCelda($sheet, 'A1', $logoPath, 100, 60);
                if ($i === 0) $sheet->setCellValue('C1', $cliente['nombre_cliente']);
                if ($fechaContrato) $sheet->setCellValue('A5', 'FECHA DE REVISIÓN: ' . $fechaContrato);
            }
        }

        $tmpPath = $tmpDir . '/' . uniqid() . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tmpPath);

        return ['path' => $tmpPath, 'name' => $fileName];
    }

    /**
     * Reemplaza la imagen existente en una celda por una nueva
     */
    private function reemplazarImagenEnCelda($sheet, string $celda, string $imagePath, int $width = 100, int $height = 60): void
    {
        // Remover drawings existentes en esa celda
        $drawings = $sheet->getDrawingCollection();
        $toRemove = [];
        foreach ($drawings as $key => $drawing) {
            if ($drawing->getCoordinates() === $celda) {
                $toRemove[] = $key;
            }
        }
        // Remover en orden inverso para no afectar índices
        foreach (array_reverse($toRemove) as $key) {
            $drawings->offsetUnset($key);
        }

        // Insertar nueva imagen
        $drawing = new Drawing();
        $drawing->setName('Logo Cliente');
        $drawing->setDescription('Logo ' . basename($imagePath));
        $drawing->setPath($imagePath);
        $drawing->setCoordinates($celda);
        $drawing->setWidth($width);
        $drawing->setHeight($height);
        $drawing->setOffsetX(5);
        $drawing->setOffsetY(5);
        $drawing->setWorksheet($sheet);
    }

    /**
     * Retorna el nombre del mes en español
     */
    private function getMesEspanol(int $mes): string
    {
        $meses = [1=>'enero',2=>'febrero',3=>'marzo',4=>'abril',5=>'mayo',6=>'junio',
                  7=>'julio',8=>'agosto',9=>'septiembre',10=>'octubre',11=>'noviembre',12=>'diciembre'];
        return $meses[$mes] ?? '';
    }
}
