<?php

namespace App\Libraries;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class MatricesGeneratorLibrary
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Genera ambas matrices personalizadas para un cliente,
     * las guarda en uploads/matrices/{id_cliente}/ y crea registros en tbl_matrices.
     *
     * @return array ['epp' => id_matriz|null, 'peligros' => id_matriz|null, 'errors' => [...]]
     */
    public function generarYRegistrar(int $idCliente): array
    {
        $result = ['epp' => null, 'peligros' => null, 'errors' => []];

        $cliente = $this->db->table('tbl_clientes')
            ->where('id_cliente', $idCliente)
            ->get()->getRowArray();

        if (!$cliente) {
            $result['errors'][] = 'Cliente no encontrado';
            return $result;
        }

        $contrato = $this->db->table('tbl_contratos')
            ->where('id_cliente', $idCliente)
            ->where('estado', 'activo')
            ->orderBy('fecha_inicio', 'DESC')
            ->get()->getRowArray();

        // Preparar logo
        $logoPath = null;
        if (!empty($cliente['logo'])) {
            $fullLogoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($fullLogoPath)) {
                $logoPath = $fullLogoPath;
            }
        }

        // Fecha del contrato en español (fallback a fecha_ingreso del cliente)
        $fechaContrato = '';
        $fechaOrigen = $contrato['fecha_inicio'] ?? $cliente['fecha_ingreso'] ?? null;
        if (!empty($fechaOrigen)) {
            $ts = strtotime($fechaOrigen);
            $fechaContrato = date('j', $ts) . ' de ' . $this->getMesEspanol((int)date('n', $ts)) . ' ' . date('Y', $ts);
        }

        $nombreLimpio = preg_replace('/[^a-zA-Z0-9]/', '_', $cliente['nombre_cliente']);

        // Crear directorio de destino
        $destDir = FCPATH . 'uploads/matrices/' . $idCliente;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0775, true);
        }

        // Generar EPP
        $eppResult = $this->generarMatriz('epp', $cliente, $logoPath, $fechaContrato, $nombreLimpio, $destDir);
        if ($eppResult) {
            $result['epp'] = $this->registrarEnBD($idCliente, 'MT-SST-002 MATRIZ DE ELEMENTOS DE PROTECCION PERSONAL', 'EPP y Dotacion por cargo - 6 hojas', $eppResult['relativePath']);
        } else {
            $result['errors'][] = 'No se pudo generar Matriz EPP';
        }

        // Generar Peligros
        $pelResult = $this->generarMatriz('peligros', $cliente, $logoPath, $fechaContrato, $nombreLimpio, $destDir);
        if ($pelResult) {
            $result['peligros'] = $this->registrarEnBD($idCliente, 'MT-SST-001 MATRIZ DE PELIGROS Y VALORACION DE RIESGOS', 'Administracion, Serv. Generales, Operaciones, Seguridad, Parqueadero - 7 hojas', $pelResult['relativePath']);
        } else {
            $result['errors'][] = 'No se pudo generar Matriz Peligros';
        }

        return $result;
    }

    /**
     * Verifica si un cliente ya tiene matrices generadas en tbl_matrices (locales, no Drive)
     */
    public function clienteTieneMatricesLocales(int $idCliente): bool
    {
        $count = $this->db->table('tbl_matrices')
            ->where('id_cliente', $idCliente)
            ->like('enlace', 'uploads/matrices/')
            ->countAllResults();

        return $count >= 2;
    }

    /**
     * Regenera las matrices: elimina archivos y registros anteriores, y genera nuevos.
     */
    public function regenerar(int $idCliente): array
    {
        // Eliminar registros locales anteriores
        $registros = $this->db->table('tbl_matrices')
            ->where('id_cliente', $idCliente)
            ->like('enlace', 'uploads/matrices/')
            ->get()->getResultArray();

        foreach ($registros as $reg) {
            $filePath = FCPATH . $reg['enlace'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $this->db->table('tbl_matrices')->delete(['id_matriz' => $reg['id_matriz']]);
        }

        return $this->generarYRegistrar($idCliente);
    }

    // ─── Privados ───

    private function generarMatriz(string $tipo, array $cliente, ?string $logoPath, string $fechaContrato, string $nombreLimpio, string $destDir): ?array
    {
        if ($tipo === 'epp') {
            $templatePath = APPPATH . 'Templates/matrices/MATRIZ ELEMENTOS DE PROTECCION PROPIEDAD HORIZONTAL.xlsx';
            $fileName = "MT-SST-002_Matriz_EPP_{$nombreLimpio}.xlsx";
        } else {
            $templatePath = APPPATH . 'Templates/matrices/MT-SST-001 MATRIZ DE PELIGROS.xlsx';
            $fileName = "MT-SST-001_Matriz_Peligros_{$nombreLimpio}.xlsx";
        }

        if (!file_exists($templatePath)) {
            log_message('error', "MatricesGenerator: Plantilla no encontrada: {$templatePath}");
            return null;
        }

        try {
            $spreadsheet = IOFactory::load($templatePath);

            if ($tipo === 'epp') {
                for ($i = 0; $i < $spreadsheet->getSheetCount(); $i++) {
                    $sheet = $spreadsheet->getSheet($i);
                    if ($logoPath) $this->reemplazarImagenEnCelda($sheet, 'A1', $logoPath, 100, 60);
                    // M4 = celda FECHA en encabezado EPP (K4:L4 es label "FECHA", M4 es el valor)
                    $sheet->setCellValue('M4', $fechaContrato ?: '');
                }
            } else {
                $hojasConDatos = min(5, $spreadsheet->getSheetCount());
                for ($i = 0; $i < $hojasConDatos; $i++) {
                    $sheet = $spreadsheet->getSheet($i);
                    if ($logoPath) $this->reemplazarImagenEnCelda($sheet, 'A1', $logoPath, 100, 60);
                    if ($i === 0) $sheet->setCellValue('C1', $cliente['nombre_cliente']);
                    $sheet->setCellValue('A5', $fechaContrato ? 'FECHA DE REVISIÓN: ' . $fechaContrato : '');
                }
            }

            $savePath = $destDir . '/' . $fileName;
            $writer = new Xlsx($spreadsheet);
            $writer->save($savePath);

            $relativePath = 'uploads/matrices/' . $cliente['id_cliente'] . '/' . $fileName;

            log_message('info', "MatricesGenerator: Generada {$tipo} para cliente {$cliente['id_cliente']}: {$relativePath}");

            return ['path' => $savePath, 'relativePath' => $relativePath, 'fileName' => $fileName];
        } catch (\Exception $e) {
            log_message('error', "MatricesGenerator: Error generando {$tipo}: " . $e->getMessage());
            return null;
        }
    }

    private function registrarEnBD(int $idCliente, string $tipo, string $descripcion, string $enlaceRelativo): int
    {
        $data = [
            'tipo'          => $tipo,
            'descripcion'   => $descripcion,
            'observaciones' => 'Generada automaticamente',
            'enlace'        => $enlaceRelativo,
            'id_cliente'    => $idCliente,
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ];

        $this->db->table('tbl_matrices')->insert($data);
        return $this->db->insertID();
    }

    private function reemplazarImagenEnCelda($sheet, string $celda, string $imagePath, int $width = 100, int $height = 60): void
    {
        $drawings = $sheet->getDrawingCollection();
        $toRemove = [];
        foreach ($drawings as $key => $drawing) {
            if ($drawing->getCoordinates() === $celda) {
                $toRemove[] = $key;
            }
        }
        foreach (array_reverse($toRemove) as $key) {
            $drawings->offsetUnset($key);
        }

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

    private function getMesEspanol(int $mes): string
    {
        $meses = [1=>'enero',2=>'febrero',3=>'marzo',4=>'abril',5=>'mayo',6=>'junio',
                  7=>'julio',8=>'agosto',9=>'septiembre',10=>'octubre',11=>'noviembre',12=>'diciembre'];
        return $meses[$mes] ?? '';
    }
}
