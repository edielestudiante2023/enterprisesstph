<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\EvaluacionSimulacroModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use Dompdf\Dompdf;

class EvaluacionSimulacroController extends BaseController
{
    protected EvaluacionSimulacroModel $evalModel;

    public function __construct()
    {
        $this->evalModel = new EvaluacionSimulacroModel();
    }

    /**
     * Lista de evaluaciones de simulacro (admin ve todas, consultor ve sus clientes)
     */
    public function list()
    {
        $userId = session()->get('user_id');
        $role = session()->get('role');

        if ($role === 'admin') {
            $evaluaciones = $this->evalModel
                ->select('tbl_evaluacion_simulacro.*, tbl_clientes.nombre_cliente')
                ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_evaluacion_simulacro.id_cliente', 'left')
                ->orderBy('tbl_evaluacion_simulacro.fecha', 'DESC')
                ->findAll();
        } else {
            $evaluaciones = $this->evalModel->getByConsultor($userId);
        }

        $data = [
            'title'        => 'Evaluacion Simulacro de Evacuacion',
            'evaluaciones' => $evaluaciones,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/simulacro/list', $data),
            'title'   => 'Ev. Simulacro',
        ]);
    }

    /**
     * Vista read-only de una evaluacion
     */
    public function view($id)
    {
        $eval = $this->evalModel->find($id);
        if (!$eval) {
            return redirect()->to('/inspecciones/simulacro')->with('error', 'No encontrada');
        }

        $clientModel = new ClientModel();

        $data = [
            'title'   => 'Ver Evaluacion Simulacro',
            'eval'    => $eval,
            'cliente' => $clientModel->find($eval['id_cliente']),
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/simulacro/view', $data),
            'title'   => 'Ver Simulacro',
        ]);
    }

    /**
     * Genera y muestra el PDF inline
     */
    public function generatePdf($id)
    {
        $eval = $this->evalModel->find($id);
        if (!$eval) {
            return redirect()->to('/inspecciones/simulacro')->with('error', 'No encontrada');
        }

        $pdfPath = $this->generarPdfInterno($id);
        $fullPath = FCPATH . $pdfPath;
        if (!file_exists($fullPath)) {
            return redirect()->back()->with('error', 'PDF no encontrado');
        }

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="simulacro_' . $id . '.pdf"')
            ->setBody(file_get_contents($fullPath));
    }

    /**
     * Finalizar: genera PDF + registra en tbl_reporte
     */
    public function finalizar($id)
    {
        $eval = $this->evalModel->find($id);
        if (!$eval) {
            return redirect()->to('/inspecciones/simulacro')->with('error', 'No encontrada');
        }

        $pdfPath = $this->generarPdfInterno($id);
        if (!$pdfPath) {
            return redirect()->back()->with('error', 'Error al generar PDF');
        }

        $this->evalModel->update($id, [
            'estado'   => 'completo',
            'ruta_pdf' => $pdfPath,
        ]);

        $eval = $this->evalModel->find($id);
        $this->uploadToReportes($eval, $pdfPath);

        return redirect()->to('/inspecciones/simulacro/view/' . $id)
            ->with('msg', 'Evaluacion finalizada y PDF generado');
    }

    /**
     * Eliminar (solo borradores)
     */
    public function delete($id)
    {
        $eval = $this->evalModel->find($id);
        if (!$eval) {
            return redirect()->to('/inspecciones/simulacro')->with('error', 'No encontrada');
        }
        if ($eval['estado'] === 'completo') {
            return redirect()->to('/inspecciones/simulacro')->with('error', 'No se puede eliminar una evaluacion completa');
        }

        // Borrar fotos
        foreach (['imagen_1', 'imagen_2'] as $campo) {
            if (!empty($eval[$campo]) && file_exists(FCPATH . $eval[$campo])) {
                unlink(FCPATH . $eval[$campo]);
            }
        }

        // Borrar PDF
        if (!empty($eval['ruta_pdf']) && file_exists(FCPATH . $eval['ruta_pdf'])) {
            unlink(FCPATH . $eval['ruta_pdf']);
        }

        $this->evalModel->delete($id);

        return redirect()->to('/inspecciones/simulacro')->with('msg', 'Evaluacion eliminada');
    }

    // ===== METODOS PRIVADOS =====

    /**
     * Genera el PDF con DOMPDF
     */
    private function generarPdfInterno($id): ?string
    {
        $eval = $this->evalModel->find($id);
        $clientModel = new ClientModel();
        $cliente = $clientModel->find($eval['id_cliente']);

        // Logo en base64
        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoMime = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        // Fotos a base64
        $fotosBase64 = [];
        foreach (['imagen_1', 'imagen_2'] as $campo) {
            $fotosBase64[$campo] = '';
            if (!empty($eval[$campo])) {
                $fotoPath = FCPATH . $eval[$campo];
                if (file_exists($fotoPath)) {
                    $mime = mime_content_type($fotoPath);
                    $fotosBase64[$campo] = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fotoPath));
                }
            }
        }

        // Consultor del cliente
        $consultorNombre = '';
        if (!empty($cliente['id_consultor'])) {
            $consultantModel = new ConsultantModel();
            $consultor = $consultantModel->find($cliente['id_consultor']);
            $consultorNombre = $consultor['nombre_consultor'] ?? '';
        }

        $data = [
            'eval'            => $eval,
            'cliente'         => $cliente,
            'consultorNombre' => $consultorNombre,
            'logoBase64'      => $logoBase64,
            'fotosBase64'     => $fotosBase64,
        ];

        $html = view('inspecciones/simulacro/pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $pdfDir = 'uploads/inspecciones/simulacro/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) {
            mkdir(FCPATH . $pdfDir, 0755, true);
        }

        $pdfFileName = 'simulacro_' . $id . '_' . date('Ymd_His') . '.pdf';
        $pdfPath = $pdfDir . $pdfFileName;

        // Borrar PDF anterior
        if (!empty($eval['ruta_pdf']) && file_exists(FCPATH . $eval['ruta_pdf'])) {
            unlink(FCPATH . $eval['ruta_pdf']);
        }

        file_put_contents(FCPATH . $pdfPath, $dompdf->output());

        return $pdfPath;
    }

    /**
     * Registra/actualiza el PDF en tbl_reporte
     */
    private function uploadToReportes(array $eval, string $pdfPath): bool
    {
        $reporteModel = new ReporteModel();
        $clientModel = new ClientModel();
        $cliente = $clientModel->find($eval['id_cliente']);
        if (!$cliente) return false;

        $nitCliente = $cliente['nit_cliente'];

        $existente = $reporteModel
            ->where('id_cliente', $eval['id_cliente'])
            ->where('id_report_type', 6)
            ->where('id_detailreport', 21)
            ->like('observaciones', 'eval_sim_id:' . $eval['id'])
            ->first();

        $destDir = ROOTPATH . 'public/uploads/' . $nitCliente;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $fileName = 'simulacro_' . $eval['id'] . '_' . date('Ymd_His') . '.pdf';
        $destPath = $destDir . '/' . $fileName;
        copy(FCPATH . $pdfPath, $destPath);

        $data = [
            'titulo_reporte'  => 'EVALUACION SIMULACRO - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $eval['fecha'],
            'id_detailreport' => 21,
            'id_report_type'  => 6,
            'id_cliente'      => $eval['id_cliente'],
            'estado'          => 'Activo',
            'observaciones'   => 'Generado automaticamente desde modulo de inspecciones. eval_sim_id:' . $eval['id'],
            'enlace'          => base_url('uploads/' . $nitCliente . '/' . $fileName),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        if ($existente) {
            return $reporteModel->update($existente['id_reporte'], $data);
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        return $reporteModel->save($data);
    }
}
