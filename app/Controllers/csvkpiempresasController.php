<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\ClientKpiModel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CsvKpiEmpresasController extends Controller
{
    public function index()
    {
        // Cargar la vista para subir el archivo CSV
        return view('consultant/csvkpisempresas');
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
                $requiredHeaders = [
                    'id_empresa', 
                    'nombre_empresa', 
                    'kpi', 
                    'valor_kpi', 
                    'fecha_registro', 
                    'responsable'
                ];

                if ($headers !== $requiredHeaders) {
                    return redirect()->to(base_url('consultant/csvkpisempresas'))
                        ->with('error', 'El archivo no tiene los encabezados requeridos.');
                }

                // Procesar las filas (omitiendo la primera fila de encabezados)
                $model = new ClientKpiModel();
                foreach (array_slice($rows, 1) as $row) {
                    // Crear un array asociativo usando los encabezados como claves
                    $data = [
                        'id_empresa' => $row[0],
                        'nombre_empresa' => $row[1],
                        'kpi' => $row[2],
                        'valor_kpi' => $row[3],
                        'fecha_registro' => $row[4],
                        'responsable' => $row[5],
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ];

                    // Insertar el registro en la base de datos
                    $model->insert($data);
                }

                // Eliminar el archivo después de procesarlo
                unlink($filePath);

                return redirect()->to(base_url('consultant/csvkpisempresas'))
                    ->with('success', 'Archivo CSV procesado con éxito.');
            } catch (\Exception $e) {
                return redirect()->to(base_url('consultant/csvkpisempresas'))
                    ->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
            }
        }

        return redirect()->to(base_url('consultant/csvkpisempresas'))
            ->with('error', 'Error al subir el archivo.');
    }
}
