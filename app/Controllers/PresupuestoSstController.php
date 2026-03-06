<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Controlador para Módulo de Presupuesto SST
 * Asignación de recursos para el SG-SST
 * Adaptado para enterprisesstph (sin firmas, sin versionamiento)
 */
class PresupuestoSstController extends BaseController
{
    protected $db;
    protected $session;

    protected $datosDocumento = [
        'codigo'      => 'FT-SST',
        'nombre'      => 'Asignacion de recursos para el SG-SST',
        'descripcion' => 'Presupuesto anual de recursos para el SG-SST',
        'version'     => '001'
    ];

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = \Config\Services::session();
    }

    /**
     * Vista intermedia: selector de cliente para presupuesto
     */
    public function seleccionar()
    {
        $clientes = $this->db->table('tbl_clientes')
            ->select('id_cliente, nombre_cliente, nit_cliente')
            ->orderBy('nombre_cliente', 'ASC')
            ->get()->getResultArray();

        return view('presupuesto/seleccionar_cliente', ['clientes' => $clientes]);
    }

    /**
     * Genera el código completo del documento con consecutivo
     */
    protected function generarCodigoCompleto(int $idCliente): string
    {
        return $this->datosDocumento['codigo'] . '-001';
    }

    /**
     * Vista principal del presupuesto (tabla editable)
     */
    public function index($idCliente, $anio = null)
    {
        $anio = $anio ?? date('Y');

        $cliente = $this->db->table('tbl_clientes')
            ->where('id_cliente', $idCliente)
            ->get()->getRowArray();

        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $presupuesto = $this->getOrCreatePresupuesto($idCliente, $anio);

        $categorias = $this->db->table('tbl_presupuesto_categorias')
            ->where('activo', 1)
            ->orderBy('orden', 'ASC')
            ->get()->getResultArray();

        $items = $this->getItemsConDetalles($presupuesto['id_presupuesto'], $anio);
        $totales = $this->calcularTotales($items);
        $meses = $this->getMesesPresupuesto($presupuesto['mes_inicio'], $anio);

        $contexto = [];

        // Consultor
        $consultor = $this->getConsultor($idCliente, $cliente);

        return view('presupuesto/presupuesto_sst', [
            'cliente'            => $cliente,
            'presupuesto'        => $presupuesto,
            'categorias'         => $categorias,
            'items'              => $items,
            'totales'            => $totales,
            'meses'              => $meses,
            'anio'               => $anio,
            'anios_disponibles'  => range(2026, 2030),
            'contexto'           => $contexto,
            'itemsArray'         => $items,
            'consultor'          => $consultor,
            'codigoDocumento'    => $this->generarCodigoCompleto($idCliente),
            'versionDocumento'   => $this->datosDocumento['version'],
            'tituloDocumento'    => $this->datosDocumento['nombre']
        ]);
    }

    /**
     * Vista preview del presupuesto (formato documento formal)
     */
    public function preview($idCliente, $anio = null)
    {
        $anio = $anio ?? date('Y');

        $cliente = $this->db->table('tbl_clientes')
            ->where('id_cliente', $idCliente)
            ->get()->getRowArray();

        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        $presupuesto = $this->db->table('tbl_presupuesto_sst')
            ->where('id_cliente', $idCliente)
            ->where('anio', $anio)
            ->get()->getRowArray();

        if (!$presupuesto) {
            return redirect()->to(base_url("presupuesto/{$idCliente}/{$anio}"))
                ->with('error', 'No existe presupuesto para este año. Créelo primero.');
        }

        $contexto = $this->db->table('tbl_cliente_contexto_sst')
            ->where('id_cliente', $idCliente)
            ->get()->getRowArray() ?? [];

        $consultor = $this->getConsultor($idCliente, $cliente);

        $items = $this->getItemsConDetalles($presupuesto['id_presupuesto'], $anio);
        $totales = $this->calcularTotales($items);

        $itemsPorCategoria = $this->agruparPorCategoria($items);

        return view('presupuesto/presupuesto_preview', [
            'cliente'           => $cliente,
            'presupuesto'       => $presupuesto,
            'anio'              => $anio,
            'itemsPorCategoria' => $itemsPorCategoria,
            'totales'           => $totales,
            'contexto'          => $contexto,
            'consultor'         => $consultor,
            'codigoDocumento'   => $this->generarCodigoCompleto($idCliente),
            'versionDocumento'  => $this->datosDocumento['version'],
            'tituloDocumento'   => $this->datosDocumento['nombre']
        ]);
    }

    /**
     * Exportar a PDF con DOMPDF
     */
    public function exportarPdf($idCliente, $anio)
    {
        $cliente = $this->db->table('tbl_clientes')
            ->where('id_cliente', $idCliente)
            ->get()->getRowArray();

        $presupuesto = $this->db->table('tbl_presupuesto_sst')
            ->where('id_cliente', $idCliente)
            ->where('anio', $anio)
            ->get()->getRowArray();

        if (!$presupuesto) {
            return redirect()->back()->with('error', 'Presupuesto no encontrado');
        }

        $contexto = $this->db->table('tbl_cliente_contexto_sst')
            ->where('id_cliente', $idCliente)
            ->get()->getRowArray() ?? [];

        $consultor = $this->getConsultor($idCliente, $cliente);

        $items = $this->getItemsConDetalles($presupuesto['id_presupuesto'], $anio);
        $totales = $this->calcularTotales($items);
        $meses = $this->getMesesPresupuesto($presupuesto['mes_inicio'], $anio);
        $itemsPorCategoria = $this->agruparPorCategoria($items);

        // Logo a base64
        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoData = file_get_contents($logoPath);
                $logoMime = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode($logoData);
            }
        }

        $html = view('presupuesto/presupuesto_pdf', [
            'cliente'           => $cliente,
            'presupuesto'       => $presupuesto,
            'contexto'          => $contexto,
            'consultor'         => $consultor,
            'itemsPorCategoria' => $itemsPorCategoria,
            'totales'           => $totales,
            'meses'             => $meses,
            'anio'              => $anio,
            'logoBase64'        => $logoBase64,
            'codigoDocumento'   => $this->generarCodigoCompleto($idCliente),
            'versionDocumento'  => $this->datosDocumento['version'],
            'tituloDocumento'   => $this->datosDocumento['nombre']
        ]);

        $dompdf = new \Dompdf\Dompdf(['isRemoteEnabled' => true]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $filename = $this->datosDocumento['codigo'] . "_Presupuesto_{$cliente['nombre_cliente']}_{$anio}.pdf";

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }

    /**
     * Exportar a Word
     */
    public function exportarWord($idCliente, $anio)
    {
        $cliente = $this->db->table('tbl_clientes')
            ->where('id_cliente', $idCliente)
            ->get()->getRowArray();

        $presupuesto = $this->db->table('tbl_presupuesto_sst')
            ->where('id_cliente', $idCliente)
            ->where('anio', $anio)
            ->get()->getRowArray();

        if (!$presupuesto) {
            return redirect()->back()->with('error', 'Presupuesto no encontrado');
        }

        $contexto = $this->db->table('tbl_cliente_contexto_sst')
            ->where('id_cliente', $idCliente)
            ->get()->getRowArray() ?? [];

        $consultor = $this->getConsultor($idCliente, $cliente);

        $items = $this->getItemsConDetalles($presupuesto['id_presupuesto'], $anio);
        $totales = $this->calcularTotales($items);
        $meses = $this->getMesesPresupuesto($presupuesto['mes_inicio'], $anio);
        $itemsPorCategoria = $this->agruparPorCategoria($items);

        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoData = file_get_contents($logoPath);
                $logoMime = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode($logoData);
            }
        }

        $html = view('presupuesto/presupuesto_word', [
            'cliente'           => $cliente,
            'presupuesto'       => $presupuesto,
            'contexto'          => $contexto,
            'consultor'         => $consultor,
            'itemsPorCategoria' => $itemsPorCategoria,
            'totales'           => $totales,
            'meses'             => $meses,
            'anio'              => $anio,
            'logoBase64'        => $logoBase64,
            'codigoDocumento'   => $this->generarCodigoCompleto($idCliente),
            'versionDocumento'  => $this->datosDocumento['version'],
            'tituloDocumento'   => $this->datosDocumento['nombre']
        ]);

        $filename = $this->datosDocumento['codigo'] . "_Presupuesto_{$cliente['nombre_cliente']}_{$anio}.doc";

        return $this->response
            ->setHeader('Content-Type', 'application/msword')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($html);
    }

    /**
     * Exportar a Excel con PhpSpreadsheet
     */
    public function exportarExcel($idCliente, $anio)
    {
        $cliente = $this->db->table('tbl_clientes')
            ->where('id_cliente', $idCliente)
            ->get()->getRowArray();

        $presupuesto = $this->db->table('tbl_presupuesto_sst')
            ->where('id_cliente', $idCliente)
            ->where('anio', $anio)
            ->get()->getRowArray();

        if (!$presupuesto) {
            return redirect()->back()->with('error', 'Presupuesto no encontrado');
        }

        $items = $this->getItemsConDetalles($presupuesto['id_presupuesto'], $anio);
        $totales = $this->calcularTotales($items);
        $meses = $this->getMesesPresupuesto($presupuesto['mes_inicio'], $anio);
        $itemsPorCategoria = $this->agruparPorCategoria($items);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Presupuesto ' . $anio);

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1a5f7a']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $subHeaderStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2980b9']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $categoriaStyle = [
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'e8f4f8']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $subtotalStyle = [
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'd4edda']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $totalStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1a5f7a']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];

        // Encabezado
        $lastCol = $this->getColLetter(3 + count($meses) * 2 + 1);
        $sheet->setCellValue('A1', 'SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO');
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->getStyle('A1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(25);

        $sheet->setCellValue('A2', $this->datosDocumento['nombre'] . ' - AÑO ' . $anio);
        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->getStyle('A2')->applyFromArray($headerStyle);

        $sheet->setCellValue('A3', 'Empresa: ' . $cliente['nombre_cliente'] . ' | NIT: ' . ($cliente['nit'] ?? 'N/A') . ' | Codigo: ' . $this->datosDocumento['codigo']);
        $sheet->mergeCells("A3:{$lastCol}3");

        // Headers columnas
        $row = 5;
        $sheet->setCellValue('A' . $row, 'Item');
        $sheet->setCellValue('B' . $row, 'Actividad');
        $sheet->setCellValue('C' . $row, 'Descripcion');

        $col = 4;
        foreach ($meses as $mes) {
            $sheet->setCellValue($this->getColLetter($col) . $row, $mes['nombre']);
            $sheet->mergeCells($this->getColLetter($col) . $row . ':' . $this->getColLetter($col + 1) . $row);
            $col += 2;
        }
        $sheet->setCellValue($this->getColLetter($col) . $row, 'TOTAL');
        $sheet->mergeCells($this->getColLetter($col) . $row . ':' . $this->getColLetter($col + 1) . $row);
        $sheet->getStyle('A' . $row . ':' . $this->getColLetter($col + 1) . $row)->applyFromArray($headerStyle);

        // Sub-headers
        $row++;
        $sheet->setCellValue('A' . $row, '');
        $sheet->setCellValue('B' . $row, '');
        $sheet->setCellValue('C' . $row, '');
        $col = 4;
        foreach ($meses as $mes) {
            $sheet->setCellValue($this->getColLetter($col) . $row, 'Presup.');
            $sheet->setCellValue($this->getColLetter($col + 1) . $row, 'Ejec.');
            $col += 2;
        }
        $sheet->setCellValue($this->getColLetter($col) . $row, 'Presup.');
        $sheet->setCellValue($this->getColLetter($col + 1) . $row, 'Ejec.');
        $sheet->getStyle('A' . $row . ':' . $this->getColLetter($col + 1) . $row)->applyFromArray($subHeaderStyle);

        // Data
        $row++;
        foreach ($itemsPorCategoria as $codigoCat => $categoria) {
            $sheet->setCellValue('A' . $row, $codigoCat . '. ' . $categoria['nombre']);
            $sheet->mergeCells('A' . $row . ':' . $lastCol . $row);
            $sheet->getStyle('A' . $row . ':' . $lastCol . $row)->applyFromArray($categoriaStyle);
            $row++;

            foreach ($categoria['items'] as $item) {
                $sheet->setCellValue('A' . $row, $item['codigo_item']);
                $sheet->setCellValue('B' . $row, $item['actividad']);
                $sheet->setCellValue('C' . $row, $item['descripcion'] ?? '');

                $col = 4;
                foreach ($meses as $mes) {
                    $detalle = $item['detalles'][$mes['numero']] ?? null;
                    $presup = $detalle ? floatval($detalle['presupuestado']) : 0;
                    $ejec = $detalle ? floatval($detalle['ejecutado']) : 0;
                    $sheet->setCellValue($this->getColLetter($col) . $row, $presup);
                    $sheet->setCellValue($this->getColLetter($col + 1) . $row, $ejec);
                    $sheet->getStyle($this->getColLetter($col) . $row)->getNumberFormat()->setFormatCode('#,##0');
                    $sheet->getStyle($this->getColLetter($col + 1) . $row)->getNumberFormat()->setFormatCode('#,##0');
                    $col += 2;
                }
                $sheet->setCellValue($this->getColLetter($col) . $row, $item['total_presupuestado']);
                $sheet->setCellValue($this->getColLetter($col + 1) . $row, $item['total_ejecutado']);
                $sheet->getStyle($this->getColLetter($col) . $row)->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle($this->getColLetter($col + 1) . $row)->getNumberFormat()->setFormatCode('#,##0');
                $row++;
            }

            // Subtotal
            $totCat = $totales['por_categoria'][$codigoCat] ?? ['presupuestado' => 0, 'ejecutado' => 0, 'por_mes' => []];
            $sheet->setCellValue('A' . $row, 'Subtotal ' . $codigoCat);
            $sheet->mergeCells('A' . $row . ':C' . $row);
            $col = 4;
            foreach ($meses as $mes) {
                $totMes = $totCat['por_mes'][$mes['numero']] ?? ['presupuestado' => 0, 'ejecutado' => 0];
                $sheet->setCellValue($this->getColLetter($col) . $row, $totMes['presupuestado']);
                $sheet->setCellValue($this->getColLetter($col + 1) . $row, $totMes['ejecutado']);
                $sheet->getStyle($this->getColLetter($col) . $row)->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle($this->getColLetter($col + 1) . $row)->getNumberFormat()->setFormatCode('#,##0');
                $col += 2;
            }
            $sheet->setCellValue($this->getColLetter($col) . $row, $totCat['presupuestado']);
            $sheet->setCellValue($this->getColLetter($col + 1) . $row, $totCat['ejecutado']);
            $sheet->getStyle($this->getColLetter($col) . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle($this->getColLetter($col + 1) . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('A' . $row . ':' . $this->getColLetter($col + 1) . $row)->applyFromArray($subtotalStyle);
            $row++;
        }

        // Total general
        $sheet->setCellValue('A' . $row, 'TOTAL GENERAL');
        $sheet->mergeCells('A' . $row . ':C' . $row);
        $col = 4;
        foreach ($meses as $mes) {
            $totMes = $totales['por_mes'][$mes['numero']] ?? ['presupuestado' => 0, 'ejecutado' => 0];
            $sheet->setCellValue($this->getColLetter($col) . $row, $totMes['presupuestado']);
            $sheet->setCellValue($this->getColLetter($col + 1) . $row, $totMes['ejecutado']);
            $sheet->getStyle($this->getColLetter($col) . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle($this->getColLetter($col + 1) . $row)->getNumberFormat()->setFormatCode('#,##0');
            $col += 2;
        }
        $sheet->setCellValue($this->getColLetter($col) . $row, $totales['general_presupuestado']);
        $sheet->setCellValue($this->getColLetter($col + 1) . $row, $totales['general_ejecutado']);
        $sheet->getStyle($this->getColLetter($col) . $row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle($this->getColLetter($col + 1) . $row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('A' . $row . ':' . $this->getColLetter($col + 1) . $row)->applyFromArray($totalStyle);

        // Anchos
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(25);
        for ($i = 4; $i <= $col + 1; $i++) {
            $sheet->getColumnDimension($this->getColLetter($i))->setWidth(12);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = $this->datosDocumento['codigo'] . "_Presupuesto_{$cliente['nombre_cliente']}_{$anio}.xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    // ========================================
    // AJAX CRUD
    // ========================================

    public function agregarItem()
    {
        $idPresupuesto = $this->request->getPost('id_presupuesto');
        $idCategoria = $this->request->getPost('id_categoria');
        $codigoItem = $this->request->getPost('codigo_item');
        $actividad = $this->request->getPost('actividad');
        $valorInicial = $this->request->getPost('valor_inicial');

        if (!$idPresupuesto || !$idCategoria || !$actividad) {
            return $this->response->setJSON(['success' => false, 'message' => 'Datos incompletos']);
        }

        $presupuesto = $this->db->table('tbl_presupuesto_sst')
            ->where('id_presupuesto', $idPresupuesto)
            ->get()->getRowArray();

        $maxOrden = $this->db->table('tbl_presupuesto_items')
            ->selectMax('orden')
            ->where('id_presupuesto', $idPresupuesto)
            ->where('id_categoria', $idCategoria)
            ->get()->getRow()->orden ?? 0;

        $this->db->table('tbl_presupuesto_items')->insert([
            'id_presupuesto' => $idPresupuesto,
            'id_categoria'   => $idCategoria,
            'codigo_item'    => $codigoItem,
            'actividad'      => $actividad,
            'descripcion'    => '',
            'orden'          => $maxOrden + 1
        ]);

        $idItem = $this->db->insertID();

        $mesesJson = $this->request->getPost('meses');
        $meses = json_decode($mesesJson, true) ?? [];
        $valorInicial = floatval(str_replace([',', '$', ' '], '', $valorInicial ?? '0'));
        $anioPresupuesto = intval($presupuesto['anio']);

        foreach ($meses as $mes) {
            $mes = intval($mes);
            if ($mes >= 1 && $mes <= 12) {
                $this->db->table('tbl_presupuesto_detalle')->insert([
                    'id_item'       => $idItem,
                    'mes'           => $mes,
                    'anio'          => $anioPresupuesto,
                    'presupuestado' => $valorInicial,
                    'ejecutado'     => 0
                ]);
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'id_item' => $idItem,
            'message' => 'Ítem agregado correctamente con ' . count($meses) . ' meses'
        ]);
    }

    public function actualizarMonto()
    {
        $idItem = $this->request->getPost('id_item');
        $mes = $this->request->getPost('mes');
        $anio = $this->request->getPost('anio');
        $tipo = $this->request->getPost('tipo');
        $valor = $this->request->getPost('valor');

        if (!$idItem || !$mes || !$anio || !$tipo) {
            return $this->response->setJSON(['success' => false, 'message' => 'Datos incompletos']);
        }

        $valor = floatval(str_replace([',', '$', ' '], '', $valor));

        $detalle = $this->db->table('tbl_presupuesto_detalle')
            ->where('id_item', $idItem)
            ->where('mes', $mes)
            ->where('anio', $anio)
            ->get()->getRowArray();

        if ($detalle) {
            $this->db->table('tbl_presupuesto_detalle')
                ->where('id_detalle', $detalle['id_detalle'])
                ->update([$tipo => $valor]);
        } else {
            $this->db->table('tbl_presupuesto_detalle')->insert([
                'id_item' => $idItem,
                'mes'     => $mes,
                'anio'    => $anio,
                $tipo     => $valor
            ]);
        }

        $totalesItem = $this->db->table('tbl_presupuesto_detalle')
            ->selectSum('presupuestado', 'total_presupuestado')
            ->selectSum('ejecutado', 'total_ejecutado')
            ->where('id_item', $idItem)
            ->get()->getRowArray();

        return $this->response->setJSON([
            'success'            => true,
            'total_presupuestado' => floatval($totalesItem['total_presupuestado'] ?? 0),
            'total_ejecutado'     => floatval($totalesItem['total_ejecutado'] ?? 0)
        ]);
    }

    public function eliminarItem()
    {
        $idItem = $this->request->getPost('id_item');
        if (!$idItem) {
            return $this->response->setJSON(['success' => false, 'message' => 'ID de ítem requerido']);
        }

        $this->db->table('tbl_presupuesto_items')
            ->where('id_item', $idItem)
            ->update(['activo' => 0]);

        return $this->response->setJSON(['success' => true, 'message' => 'Ítem eliminado']);
    }

    public function actualizarItem()
    {
        $idItem = $this->request->getPost('id_item');
        $actividad = $this->request->getPost('actividad');
        $descripcion = $this->request->getPost('descripcion');

        if (!$idItem || !$actividad) {
            return $this->response->setJSON(['success' => false, 'message' => 'Datos incompletos']);
        }

        $this->db->table('tbl_presupuesto_items')
            ->where('id_item', $idItem)
            ->update(['actividad' => $actividad, 'descripcion' => $descripcion]);

        return $this->response->setJSON(['success' => true, 'message' => 'Ítem actualizado']);
    }

    public function cambiarEstado($idPresupuesto, $nuevoEstado)
    {
        $estadosValidos = ['borrador', 'aprobado', 'cerrado'];
        if (!in_array($nuevoEstado, $estadosValidos)) {
            return redirect()->back()->with('error', 'Estado no válido');
        }

        $presupuesto = $this->db->table('tbl_presupuesto_sst')
            ->where('id_presupuesto', $idPresupuesto)
            ->get()->getRowArray();

        if (!$presupuesto) {
            return redirect()->back()->with('error', 'Presupuesto no encontrado');
        }

        $this->db->table('tbl_presupuesto_sst')
            ->where('id_presupuesto', $idPresupuesto)
            ->update(['estado' => $nuevoEstado]);

        return redirect()->back()->with('success', 'Estado actualizado a ' . $nuevoEstado);
    }

    public function getTotales($idPresupuesto)
    {
        $presupuesto = $this->db->table('tbl_presupuesto_sst')
            ->where('id_presupuesto', $idPresupuesto)
            ->get()->getRowArray();

        if (!$presupuesto) {
            return $this->response->setJSON(['success' => false]);
        }

        $items = $this->getItemsConDetalles($idPresupuesto, $presupuesto['anio']);
        $totales = $this->calcularTotales($items);

        return $this->response->setJSON(['success' => true, 'totales' => $totales]);
    }

    public function copiarDeAnio($idCliente, $anioOrigen, $anioDestino)
    {
        $presupuestoOrigen = $this->db->table('tbl_presupuesto_sst')
            ->where('id_cliente', $idCliente)
            ->where('anio', $anioOrigen)
            ->get()->getRowArray();

        if (!$presupuestoOrigen) {
            return redirect()->back()->with('error', 'Presupuesto origen no encontrado');
        }

        $presupuestoDestino = $this->getOrCreatePresupuesto($idCliente, $anioDestino);

        $itemsOrigen = $this->db->table('tbl_presupuesto_items')
            ->where('id_presupuesto', $presupuestoOrigen['id_presupuesto'])
            ->where('activo', 1)
            ->get()->getResultArray();

        foreach ($itemsOrigen as $item) {
            $this->db->table('tbl_presupuesto_items')->insert([
                'id_presupuesto' => $presupuestoDestino['id_presupuesto'],
                'id_categoria'   => $item['id_categoria'],
                'codigo_item'    => $item['codigo_item'],
                'actividad'      => $item['actividad'],
                'descripcion'    => $item['descripcion'],
                'orden'          => $item['orden']
            ]);

            $nuevoIdItem = $this->db->insertID();

            $detallesOrigen = $this->db->table('tbl_presupuesto_detalle')
                ->where('id_item', $item['id_item'])
                ->get()->getResultArray();

            foreach ($detallesOrigen as $det) {
                $this->db->table('tbl_presupuesto_detalle')->insert([
                    'id_item'       => $nuevoIdItem,
                    'mes'           => $det['mes'],
                    'anio'          => $anioDestino,
                    'presupuestado' => $det['presupuestado'],
                    'ejecutado'     => 0
                ]);
            }
        }

        return redirect()->to(base_url("presupuesto/{$idCliente}/{$anioDestino}"))
            ->with('success', "Presupuesto copiado de {$anioOrigen} a {$anioDestino}");
    }

    // ========================================
    // PRIVATE HELPERS
    // ========================================

    private function getOrCreatePresupuesto($idCliente, $anio)
    {
        $presupuesto = $this->db->table('tbl_presupuesto_sst')
            ->where('id_cliente', $idCliente)
            ->where('anio', $anio)
            ->get()->getRowArray();

        if (!$presupuesto) {
            $this->db->table('tbl_presupuesto_sst')->insert([
                'id_cliente' => $idCliente,
                'anio'       => $anio,
                'mes_inicio' => 1,
                'estado'     => 'borrador'
            ]);
            $idPresupuesto = $this->db->insertID();
            $presupuesto = $this->db->table('tbl_presupuesto_sst')
                ->where('id_presupuesto', $idPresupuesto)
                ->get()->getRowArray();
        }

        return $presupuesto;
    }

    private function getItemsConDetalles($idPresupuesto, $anio)
    {
        $items = $this->db->table('tbl_presupuesto_items i')
            ->select('i.*, c.nombre as categoria_nombre, c.codigo as categoria_codigo')
            ->join('tbl_presupuesto_categorias c', 'c.id_categoria = i.id_categoria')
            ->where('i.id_presupuesto', $idPresupuesto)
            ->where('i.activo', 1)
            ->orderBy('c.orden', 'ASC')
            ->orderBy('i.orden', 'ASC')
            ->get()->getResultArray();

        foreach ($items as &$item) {
            $detalles = $this->db->table('tbl_presupuesto_detalle')
                ->where('id_item', $item['id_item'])
                ->where('anio', $anio)
                ->get()->getResultArray();

            $item['detalles'] = [];
            $item['total_presupuestado'] = 0;
            $item['total_ejecutado'] = 0;

            foreach ($detalles as $det) {
                $item['detalles'][$det['mes']] = $det;
                $item['total_presupuestado'] += floatval($det['presupuestado']);
                $item['total_ejecutado'] += floatval($det['ejecutado']);
            }
        }

        return $items;
    }

    private function calcularTotales($items)
    {
        $totales = [
            'por_categoria'        => [],
            'por_mes'              => [],
            'general_presupuestado' => 0,
            'general_ejecutado'     => 0
        ];

        foreach ($items as $item) {
            $cat = $item['categoria_codigo'];

            if (!isset($totales['por_categoria'][$cat])) {
                $totales['por_categoria'][$cat] = ['presupuestado' => 0, 'ejecutado' => 0, 'por_mes' => []];
            }

            $totales['por_categoria'][$cat]['presupuestado'] += $item['total_presupuestado'];
            $totales['por_categoria'][$cat]['ejecutado'] += $item['total_ejecutado'];

            foreach ($item['detalles'] as $mes => $det) {
                if (!isset($totales['por_mes'][$mes])) {
                    $totales['por_mes'][$mes] = ['presupuestado' => 0, 'ejecutado' => 0];
                }
                $totales['por_mes'][$mes]['presupuestado'] += floatval($det['presupuestado']);
                $totales['por_mes'][$mes]['ejecutado'] += floatval($det['ejecutado']);

                if (!isset($totales['por_categoria'][$cat]['por_mes'][$mes])) {
                    $totales['por_categoria'][$cat]['por_mes'][$mes] = ['presupuestado' => 0, 'ejecutado' => 0];
                }
                $totales['por_categoria'][$cat]['por_mes'][$mes]['presupuestado'] += floatval($det['presupuestado']);
                $totales['por_categoria'][$cat]['por_mes'][$mes]['ejecutado'] += floatval($det['ejecutado']);
            }

            $totales['general_presupuestado'] += $item['total_presupuestado'];
            $totales['general_ejecutado'] += $item['total_ejecutado'];
        }

        return $totales;
    }

    private function getMesesPresupuesto($mesInicio, $anio)
    {
        $nombresMeses = [
            1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr',
            5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
            9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'
        ];

        $meses = [];
        $mesActual = $mesInicio;
        $anioActual = $anio;

        for ($i = 0; $i < 12; $i++) {
            $meses[] = [
                'numero' => $mesActual,
                'anio'   => $anioActual,
                'nombre' => $nombresMeses[$mesActual] . '-' . substr($anioActual, 2)
            ];
            $mesActual++;
            if ($mesActual > 12) {
                $mesActual = 1;
                $anioActual++;
            }
        }

        return $meses;
    }

    private function getColLetter($num)
    {
        $letter = '';
        while ($num > 0) {
            $num--;
            $letter = chr(65 + ($num % 26)) . $letter;
            $num = intval($num / 26);
        }
        return $letter;
    }

    private function agruparPorCategoria($items)
    {
        $itemsPorCategoria = [];
        foreach ($items as $item) {
            $cat = $item['categoria_codigo'];
            if (!isset($itemsPorCategoria[$cat])) {
                $itemsPorCategoria[$cat] = ['nombre' => $item['categoria_nombre'], 'items' => []];
            }
            $itemsPorCategoria[$cat]['items'][] = $item;
        }
        return $itemsPorCategoria;
    }

    private function getConsultor($idCliente, $cliente)
    {
        $consultor = null;
        $idConsultor = session('id_consultor');
        if ($idConsultor) {
            $consultor = $this->db->table('tbl_consultor')
                ->where('id_consultor', $idConsultor)
                ->get()->getRowArray();
        }
        if (!$consultor) {
            $consultor = $this->db->table('tbl_consultor')
                ->where('id_cliente', $idCliente)
                ->get()->getRowArray();
        }
        if (!$consultor && !empty($cliente['id_consultor'])) {
            $consultor = $this->db->table('tbl_consultor')
                ->where('id_consultor', $cliente['id_consultor'])
                ->get()->getRowArray();
        }
        return $consultor ?? [];
    }
}
