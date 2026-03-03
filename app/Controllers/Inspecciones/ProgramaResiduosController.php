<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\ProgramaResiduosModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use Dompdf\Dompdf;

class ProgramaResiduosController extends BaseController
{
    protected ProgramaResiduosModel $inspeccionModel;

    public function __construct()
    {
        $this->inspeccionModel = new ProgramaResiduosModel();
    }

    public function list()
    {
        $userId = session()->get('user_id');
        $role = session()->get('role');

        if ($role === 'admin') {
            $inspecciones = $this->inspeccionModel
                ->select('tbl_programa_residuos.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
                ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_programa_residuos.id_cliente', 'left')
                ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_programa_residuos.id_consultor', 'left')
                ->orderBy('tbl_programa_residuos.fecha_programa', 'DESC')
                ->findAll();
        } else {
            $inspecciones = $this->inspeccionModel->getByConsultor($userId);
        }

        return view('inspecciones/layout_pwa', [
            'title'   => 'Programa Residuos Sólidos',
            'content' => view('inspecciones/residuos-solidos/list', [
                'inspecciones' => $inspecciones,
                'role'         => $role,
            ]),
        ]);
    }

    public function create($idCliente = null)
    {
        return view('inspecciones/layout_pwa', [
            'title'   => 'Nuevo Programa Residuos',
            'content' => view('inspecciones/residuos-solidos/form', [
                'inspeccion' => null,
                'idCliente'  => $idCliente,
            ]),
        ]);
    }

    public function store()
    {
        $rules = [
            'id_cliente'      => 'required|integer',
            'fecha_programa'  => 'required|valid_date',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Por favor completa los campos requeridos.');
        }

        $data = [
            'id_cliente'          => $this->request->getPost('id_cliente'),
            'id_consultor'        => session()->get('user_id'),
            'fecha_programa'      => $this->request->getPost('fecha_programa'),
            'nombre_responsable'  => $this->request->getPost('nombre_responsable'),
            'estado'              => 'borrador',
        ];

        $this->inspeccionModel->insert($data);
        $id = $this->inspeccionModel->getInsertID();

        return redirect()->to("/inspecciones/residuos-solidos/edit/{$id}")->with('msg', 'Programa guardado como borrador.');
    }

    public function edit($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/residuos-solidos')->with('error', 'Registro no encontrado.');
        }
        $clientModel = new ClientModel();
        $cliente = $clientModel->find($inspeccion['id_cliente']);

        return view('inspecciones/layout_pwa', [
            'title'   => 'Editar Programa Residuos',
            'content' => view('inspecciones/residuos-solidos/form', [
                'inspeccion' => $inspeccion,
                'idCliente'  => $inspeccion['id_cliente'],
                'cliente'    => $cliente,
            ]),
        ]);
    }

    public function update($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/residuos-solidos')->with('error', 'No se puede editar.');
        }

        $data = [
            'id_cliente'          => $this->request->getPost('id_cliente'),
            'fecha_programa'      => $this->request->getPost('fecha_programa'),
            'nombre_responsable'  => $this->request->getPost('nombre_responsable'),
        ];

        $this->inspeccionModel->update($id, $data);

        if ($this->request->getPost('finalizar')) {
            return $this->finalizar($id);
        }

        return redirect()->to("/inspecciones/residuos-solidos/edit/{$id}")->with('msg', 'Cambios guardados.');
    }

    public function view($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/residuos-solidos')->with('error', 'Registro no encontrado.');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $cliente = $clientModel->find($inspeccion['id_cliente']);
        $consultor = $consultantModel->find($inspeccion['id_consultor']);

        return view('inspecciones/layout_pwa', [
            'title'   => 'Programa Residuos Sólidos',
            'content' => view('inspecciones/residuos-solidos/view', [
                'inspeccion' => $inspeccion,
                'cliente'    => $cliente,
                'consultor'  => $consultor,
            ]),
        ]);
    }

    public function finalizar($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/residuos-solidos')->with('error', 'Registro no encontrado.');
        }

        $pdfPath = $this->generarPdfInterno($id);

        $this->inspeccionModel->update($id, [
            'estado'   => 'completo',
            'ruta_pdf' => $pdfPath,
        ]);

        $inspeccion = $this->inspeccionModel->find($id);
        $this->uploadToReportes($inspeccion, $pdfPath);

        // Enviar email con PDF adjunto al cliente, consultor y consultor externo
        $emailResult = $this->enviarNotificacionPdf($inspeccion, $pdfPath);
        $msg = 'Programa finalizado y PDF generado.';
        if ($emailResult['success']) {
            $msg .= ' ' . $emailResult['message'];
        } else {
            $msg .= ' (Email no enviado: ' . $emailResult['error'] . ')';
        }

        return redirect()->to("/inspecciones/residuos-solidos/view/{$id}")->with('msg', $msg);
    }

    public function generatePdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/residuos-solidos')->with('error', 'Registro no encontrado.');
        }

        $pdfPath = $this->generarPdfInterno($id);
        $this->inspeccionModel->update($id, ['ruta_pdf' => $pdfPath]);

        $pdfContent = file_get_contents(FCPATH . $pdfPath);
        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="programa_residuos_' . $id . '.pdf"')
            ->setBody($pdfContent);
    }

    public function delete($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion) {
            return redirect()->to('/inspecciones/residuos-solidos')->with('error', 'Registro no encontrado.');
        }
        if (!empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) {
            unlink(FCPATH . $inspeccion['ruta_pdf']);
        }

        $this->inspeccionModel->delete($id);

        return redirect()->to('/inspecciones/residuos-solidos')->with('msg', 'Programa eliminado.');
    }

    // ── Métodos privados ──────────────────────────────────────

        public function regenerarPdf($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || ($inspeccion['estado'] ?? '') !== 'completo') {
            return redirect()->to('/inspecciones/residuos-solidos')->with('error', 'Solo se puede regenerar un registro finalizado.');
        }

        $pdfPath = $this->generarPdfInterno($id);

        $this->inspeccionModel->update($id, [
            'ruta_pdf' => $pdfPath,
        ]);

        $inspeccion = $this->inspeccionModel->find($id);
        $this->uploadToReportes($inspeccion, $pdfPath);

        return redirect()->to("/inspecciones/residuos-solidos/view/{$id}")->with('msg', 'PDF regenerado exitosamente.');
    }

    private function generarPdfInterno($id): string
    {
        $inspeccion = $this->inspeccionModel->find($id);
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $cliente = $clientModel->find($inspeccion['id_cliente']);
        $consultor = $consultantModel->find($inspeccion['id_consultor']);

        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $mime = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        $data = [
            'inspeccion' => $inspeccion,
            'cliente'    => $cliente,
            'consultor'  => $consultor,
            'logoBase64' => $logoBase64,
        ];

        $html = view('inspecciones/residuos-solidos/pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $pdfDir = 'uploads/inspecciones/residuos-solidos/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) {
            mkdir(FCPATH . $pdfDir, 0755, true);
        }

        if (!empty($inspeccion['ruta_pdf']) && file_exists(FCPATH . $inspeccion['ruta_pdf'])) {
            unlink(FCPATH . $inspeccion['ruta_pdf']);
        }

        $pdfFileName = 'programa_residuos_' . $id . '_' . date('Ymd_His') . '.pdf';
        $pdfPath = $pdfDir . $pdfFileName;
        file_put_contents(FCPATH . $pdfPath, $dompdf->output());

        return $pdfPath;
    }

    // ── Email ─────────────────────────────────────────────────

    public function enviarEmail($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);
        if (!$inspeccion || $inspeccion['estado'] !== 'completo' || empty($inspeccion['ruta_pdf'])) {
            return redirect()->to("/inspecciones/residuos-solidos/view/{$id}")->with('error', 'El programa debe estar finalizado con PDF para enviar email.');
        }

        $result = $this->enviarNotificacionPdf($inspeccion, $inspeccion['ruta_pdf']);

        if ($result['success']) {
            return redirect()->to("/inspecciones/residuos-solidos/view/{$id}")->with('msg', $result['message']);
        }
        return redirect()->to("/inspecciones/residuos-solidos/view/{$id}")->with('error', $result['error']);
    }

    private function enviarNotificacionPdf(array $inspeccion, string $pdfPath): array
    {
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $cliente = $clientModel->find($inspeccion['id_cliente']);
        if (!$cliente) return ['success' => false, 'error' => 'Cliente no encontrado'];

        $consultor = $consultantModel->find($inspeccion['id_consultor']);

        $sendgridApiKey = getenv('SENDGRID_API_KEY');
        if (!$sendgridApiKey) {
            return ['success' => false, 'error' => 'SENDGRID_API_KEY no configurada'];
        }

        $nombreCliente   = $cliente['nombre_cliente'] ?? 'Cliente';
        $correoCliente   = $cliente['correo_cliente'] ?? '';
        $correoConsultor = $consultor['correo_consultor'] ?? '';
        $nombreConsultor = $consultor['nombre_consultor'] ?? 'Consultor';
        $consultorExterno      = $cliente['consultor_externo'] ?? '';
        $emailConsultorExterno = $cliente['email_consultor_externo'] ?? '';

        if (!$correoCliente && !$correoConsultor && !$emailConsultorExterno) {
            return ['success' => false, 'error' => 'No hay correos destinatarios configurados'];
        }

        $fechaFormateada = date('d/m/Y', strtotime($inspeccion['fecha_programa']));
        $subject = "Programa Manejo Integral de Residuos Sólidos - {$nombreCliente} - {$fechaFormateada}";

        $htmlContent = "
        <div style='font-family: Segoe UI, Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: #1c2437; padding: 20px; text-align: center; border-radius: 10px 10px 0 0;'>
                <h1 style='color: #bd9751; margin: 0; font-size: 20px;'>PROGRAMA MANEJO INTEGRAL DE RESIDUOS SÓLIDOS</h1>
            </div>
            <div style='padding: 25px; background: #f8f9fa; border-radius: 0 0 10px 10px;'>
                <p>En su plataforma <strong>EnterpriseSST</strong> se ha creado el nuevo documento <strong>PROGRAMA MANEJO INTEGRAL DE RESIDUOS SÓLIDOS</strong>.</p>
                <p>Encuentra el documento adjunto en formato PDF.</p>
                <table style='width: 100%; border-collapse: collapse; margin: 15px 0;'>
                    <tr>
                        <td style='padding: 10px; background: #fff; border: 1px solid #ddd;'><strong>Cliente:</strong></td>
                        <td style='padding: 10px; background: #fff; border: 1px solid #ddd;'>{$nombreCliente}</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px; background: #fff; border: 1px solid #ddd;'><strong>Fecha:</strong></td>
                        <td style='padding: 10px; background: #fff; border: 1px solid #ddd;'>{$fechaFormateada}</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px; background: #fff; border: 1px solid #ddd;'><strong>Responsable:</strong></td>
                        <td style='padding: 10px; background: #fff; border: 1px solid #ddd;'>" . htmlspecialchars($inspeccion['nombre_responsable'] ?? '—') . "</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px; background: #fff; border: 1px solid #ddd;'><strong>Consultor:</strong></td>
                        <td style='padding: 10px; background: #fff; border: 1px solid #ddd;'>{$nombreConsultor}</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px; background: #fff; border: 1px solid #ddd;'><strong>Documento:</strong></td>
                        <td style='padding: 10px; background: #fff; border: 1px solid #ddd;'>FT-SST-226 — Programa de Manejo Integral de Residuos Sólidos (Versión 001)</td>
                    </tr>
                </table>
                <p>Para acceder al recurso, ingrese a su aplicativo en la sección de documentos haciendo <a href='https://phorizontal.cycloidtalent.com/' style='color: #bd9751; font-weight: bold;'>clic aquí</a>.</p>
                <p style='color: #999; font-size: 11px; margin-top: 30px;'>Generado por SG-SST Cycloid Talent.</p>
            </div>
        </div>";

        // Leer PDF para adjuntar
        $pdfFullPath = FCPATH . $pdfPath;
        if (!file_exists($pdfFullPath)) {
            return ['success' => false, 'error' => 'Archivo PDF no encontrado en disco'];
        }
        $pdfContent = file_get_contents($pdfFullPath);

        require_once ROOTPATH . 'vendor/autoload.php';

        $email = new \SendGrid\Mail\Mail();
        $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "Cycloid Talent - SG-SST");
        $email->setSubject($subject);

        if ($correoCliente) {
            $email->addTo($correoCliente, $nombreCliente);
        }
        if ($correoConsultor) {
            $email->addTo($correoConsultor, $nombreConsultor);
        }
        if ($emailConsultorExterno) {
            $email->addTo($emailConsultorExterno, $consultorExterno ?: 'Consultor Externo');
        }

        $email->addContent("text/html", $htmlContent);

        // Adjuntar PDF
        $email->addAttachment(
            base64_encode($pdfContent),
            'application/pdf',
            'programa_residuos_solidos_' . $inspeccion['id'] . '.pdf',
            'attachment'
        );

        $sendgrid = new \SendGrid($sendgridApiKey);

        try {
            $response = $sendgrid->send($email);

            if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                $destinatarios = array_filter([$correoCliente, $correoConsultor, $emailConsultorExterno]);
                log_message('info', "ProgramaResiduos #{$inspeccion['id']}: Email enviado a " . implode(', ', $destinatarios));
                return ['success' => true, 'message' => 'Email enviado a: ' . implode(', ', $destinatarios)];
            } else {
                log_message('error', "ProgramaResiduos #{$inspeccion['id']}: Error SendGrid. Status: {$response->statusCode()}. Body: {$response->body()}");
                return ['success' => false, 'error' => 'Error al enviar email. Status: ' . $response->statusCode()];
            }
        } catch (\Exception $e) {
            log_message('error', "ProgramaResiduos #{$inspeccion['id']}: Exception SendGrid: " . $e->getMessage());
            return ['success' => false, 'error' => 'Error: ' . $e->getMessage()];
        }
    }

    private function uploadToReportes(array $inspeccion, string $pdfPath): bool
    {
        $reporteModel = new ReporteModel();
        $clientModel = new ClientModel();

        $cliente = $clientModel->find($inspeccion['id_cliente']);
        if (!$cliente) return false;

        $nitCliente = $cliente['nit_cliente'];

        $existente = $reporteModel
            ->where('id_cliente', $inspeccion['id_cliente'])
            ->where('id_report_type', 6)
            ->where('id_detailreport', 29)
            ->like('observaciones', 'prog_res_id:' . $inspeccion['id'])
            ->first();

        $destDir = ROOTPATH . 'public/uploads/' . $nitCliente;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $fileName = 'programa_residuos_' . $inspeccion['id'] . '_' . date('Ymd_His') . '.pdf';
        $destPath = $destDir . '/' . $fileName;
        copy(FCPATH . $pdfPath, $destPath);

        $data = [
            'titulo_reporte'  => 'PROGRAMA MANEJO INTEGRAL RESIDUOS SÓLIDOS - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $inspeccion['fecha_programa'],
            'id_detailreport' => 29,
            'id_report_type'  => 6,
            'id_cliente'      => $inspeccion['id_cliente'],
            'estado'          => 'Activo',
            'observaciones'   => 'Generado automaticamente desde modulo de inspecciones. prog_res_id:' . $inspeccion['id'],
            'enlace'          => base_url('uploads/' . $nitCliente . '/' . $fileName),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        if ($existente) {
            return $reporteModel->update($existente['id_reporte'], $data);
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        return (bool) $reporteModel->save($data);
    }
}
