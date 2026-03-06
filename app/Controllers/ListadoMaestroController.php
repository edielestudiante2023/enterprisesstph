<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

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
}
