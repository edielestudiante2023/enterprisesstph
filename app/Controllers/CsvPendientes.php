<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\SimplePendientesModel; // Usar el modelo simplificado
use PhpOffice\PhpSpreadsheet\IOFactory;

class CsvPendientes extends Controller
{
    public function index()
    {
        // Cargar la vista para subir el archivo CSV
        return view('consultant/csvpendientes');
    }

    public function upload()
    {
        $file = $this->request->getFile('file');

        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Mover el archivo a la carpeta writable/uploads
            $newName = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads', $newName);
            $filePath = WRITEPATH . 'uploads/' . $newName;

            try {
                // Leer el archivo CSV utilizando PhpSpreadsheet
                $spreadsheet = IOFactory::load($filePath);
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray();

                // Validar encabezados
                $headers = $rows[0];
                $requiredHeaders = ['id_cliente', 'responsable', 'tarea_actividad', 'fecha_asignacion', 'fecha_cierre', 'estado'];

                if ($headers !== $requiredHeaders) {
                    return redirect()->to(base_url('consultant/csvpendientes'))
                        ->with('error', 'El archivo no tiene los encabezados requeridos: ' . implode(', ', $requiredHeaders));
                }

                // Procesar las filas (omitimos la primera fila de encabezados)
                $model = new SimplePendientesModel(); // Modelo simplificado
                foreach (array_slice($rows, 1) as $row) {
                    // Validar y preparar los datos antes de insertar
                    $data = [
                        'id_cliente' => $row[0],
                        'responsable' => $row[1],
                        'tarea_actividad' => $row[2],
                        'fecha_asignacion' => $this->formatDate($row[3]),
                        'fecha_cierre' => $this->formatDate($row[4]),
                        'estado' => $row[5],
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ];

                    // Insertar los datos
                    $model->insert($data);
                }

                // Eliminar el archivo después de procesarlo
                unlink($filePath);

                return redirect()->to(base_url('consultant/csvpendientes'))
                    ->with('success', 'Archivo cargado exitosamente.');
            } catch (\Exception $e) {
                return redirect()->to(base_url('consultant/csvpendientes'))
                    ->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
            }
        }

        return redirect()->to(base_url('consultant/csvpendientes'))
            ->with('error', 'Error al subir el archivo.');
    }

    /**
     * Formatear la fecha al formato Y-m-d
     */
    private function formatDate($date)
    {
        if (empty($date)) {
            return null;
        }

        // Eliminar espacios en blanco
        $date = trim($date);

        // Validar si la fecha ya está en formato YYYY-MM-DD
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }

        // Lista de formatos aceptados
        $formats = ['d/m/Y', 'm-d-Y', 'Y-m-d'];

        foreach ($formats as $format) {
            $dt = \DateTime::createFromFormat($format, $date);
            // Verifica que la fecha parseada coincida exactamente con el input
            if ($dt && $dt->format($format) === $date) {
                return $dt->format('Y-m-d');
            }
        }

        return null;
    }
}
