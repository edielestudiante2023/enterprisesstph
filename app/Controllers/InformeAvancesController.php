<?php

namespace App\Controllers;

use App\Models\InformeAvancesModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use App\Models\ActaVisitaModel;
use App\Libraries\MetricasInformeService;
use App\Services\IADocumentacionService;
use Dompdf\Dompdf;

class InformeAvancesController extends BaseController
{
    protected InformeAvancesModel $informeModel;

    public function __construct()
    {
        $this->informeModel = new InformeAvancesModel();
    }

    // ─── LIST ───
    public function list()
    {
        $userId = session()->get('user_id');
        $role = session()->get('role');

        if ($role === 'admin') {
            $informes = $this->informeModel->getByConsultor($userId);
            $pendientes = $this->informeModel->getAllPendientes();
        } else {
            $informes = $this->informeModel->getByConsultor($userId);
            $pendientes = $this->informeModel->getPendientesByConsultor($userId);
        }

        return view('informe_avances/list', [
            'informes'   => $informes,
            'pendientes' => $pendientes,
            'role'       => $role,
        ]);
    }

    // ─── CREATE ───
    public function create($idCliente = null)
    {
        $data = [
            'informe'    => null,
            'id_cliente' => $idCliente,
            'mode'       => 'create',
        ];

        return view('informe_avances/form', $data);
    }

    // ─── STORE ───
    public function store()
    {
        $userId = session()->get('user_id');

        $data = $this->getInformePostData();
        $data['id_consultor'] = $userId;
        $data['estado'] = 'borrador';
        $data['anio'] = date('Y', strtotime($data['fecha_hasta']));

        // Subir imágenes de soportes
        for ($i = 1; $i <= 4; $i++) {
            $data["soporte_{$i}_imagen"] = $this->uploadFoto("soporte_{$i}_imagen", 'uploads/informe-avances/soportes/');
        }
        // Subir screenshots opcionales
        foreach (['img_cumplimiento_estandares', 'img_indicador_plan_trabajo', 'img_indicador_capacitacion'] as $campo) {
            $uploaded = $this->uploadFoto($campo, 'uploads/informe-avances/screenshots/');
            if ($uploaded) {
                $data[$campo] = $uploaded;
            }
        }

        $this->informeModel->insert($data);
        $id = $this->informeModel->getInsertID();

        return redirect()->to('/informe-avances/edit/' . $id)
            ->with('msg', 'Informe guardado como borrador');
    }

    // ─── EDIT ───
    public function edit($id)
    {
        $informe = $this->informeModel->find($id);
        if (!$informe) {
            return redirect()->to('/informe-avances')->with('error', 'Informe no encontrado');
        }

        return view('informe_avances/form', [
            'informe'    => $informe,
            'id_cliente' => $informe['id_cliente'],
            'mode'       => 'edit',
        ]);
    }

    // ─── UPDATE ───
    public function update($id)
    {
        $informe = $this->informeModel->find($id);
        if (!$informe || $informe['estado'] === 'completo') {
            return redirect()->to('/informe-avances')->with('error', 'No se puede editar');
        }

        $data = $this->getInformePostData();
        $data['anio'] = date('Y', strtotime($data['fecha_hasta']));

        // Subir imágenes de soportes (solo si hay archivo nuevo)
        for ($i = 1; $i <= 4; $i++) {
            $uploaded = $this->uploadFoto("soporte_{$i}_imagen", 'uploads/informe-avances/soportes/');
            if ($uploaded) {
                // Eliminar anterior
                if (!empty($informe["soporte_{$i}_imagen"]) && file_exists(FCPATH . $informe["soporte_{$i}_imagen"])) {
                    unlink(FCPATH . $informe["soporte_{$i}_imagen"]);
                }
                $data["soporte_{$i}_imagen"] = $uploaded;
            }
        }
        // Screenshots opcionales
        foreach (['img_cumplimiento_estandares', 'img_indicador_plan_trabajo', 'img_indicador_capacitacion'] as $campo) {
            $uploaded = $this->uploadFoto($campo, 'uploads/informe-avances/screenshots/');
            if ($uploaded) {
                if (!empty($informe[$campo]) && file_exists(FCPATH . $informe[$campo])) {
                    unlink(FCPATH . $informe[$campo]);
                }
                $data[$campo] = $uploaded;
            }
        }

        $this->informeModel->update($id, $data);

        return redirect()->to('/informe-avances/edit/' . $id)
            ->with('msg', 'Informe actualizado');
    }

    // ─── VIEW (read-only) ───
    public function view($id)
    {
        $informe = $this->informeModel->find($id);
        if (!$informe) {
            return redirect()->to('/informe-avances')->with('error', 'Informe no encontrado');
        }

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $cliente = $clientModel->find($informe['id_cliente']);
        $consultor = $consultantModel->find($informe['id_consultor']);

        return view('informe_avances/view', [
            'informe'   => $informe,
            'cliente'   => $cliente,
            'consultor' => $consultor,
        ]);
    }

    // ─── FINALIZAR ───
    public function finalizar($id)
    {
        $informe = $this->informeModel->find($id);
        if (!$informe) {
            return redirect()->to('/informe-avances')->with('error', 'Informe no encontrado');
        }

        $pdfPath = $this->generarPdfInterno($id);
        if (!$pdfPath) {
            return redirect()->back()->with('error', 'Error al generar PDF');
        }

        $this->informeModel->update($id, [
            'estado'   => 'completo',
            'ruta_pdf' => $pdfPath,
        ]);

        $informe = $this->informeModel->find($id);
        $this->uploadToReportes($informe, $pdfPath);

        return redirect()->to('/informe-avances/view/' . $id)
            ->with('msg', 'Informe finalizado y PDF generado');
    }

    // ─── GENERATE PDF (servir) ───
    public function generatePdf($id)
    {
        $informe = $this->informeModel->find($id);
        if (!$informe || empty($informe['ruta_pdf'])) {
            return redirect()->back()->with('error', 'PDF no disponible');
        }

        $pdfPath = $this->generarPdfInterno($id);
        if (!$pdfPath) {
            return redirect()->back()->with('error', 'Error al generar PDF');
        }

        $fullPath = FCPATH . $pdfPath;
        if (!file_exists($fullPath)) {
            return redirect()->back()->with('error', 'Archivo PDF no encontrado');
        }

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="informe_avances_' . $id . '.pdf"')
            ->setBody(file_get_contents($fullPath));
    }

    // ─── DELETE ───
    public function delete($id)
    {
        $informe = $this->informeModel->find($id);
        if (!$informe) {
            return redirect()->to('/informe-avances')->with('error', 'No encontrado');
        }
        if ($informe['estado'] === 'completo') {
            return redirect()->to('/informe-avances')->with('error', 'No se puede eliminar un informe completo');
        }

        // Eliminar archivos
        for ($i = 1; $i <= 4; $i++) {
            if (!empty($informe["soporte_{$i}_imagen"]) && file_exists(FCPATH . $informe["soporte_{$i}_imagen"])) {
                unlink(FCPATH . $informe["soporte_{$i}_imagen"]);
            }
        }
        foreach (['img_cumplimiento_estandares', 'img_indicador_plan_trabajo', 'img_indicador_capacitacion'] as $campo) {
            if (!empty($informe[$campo]) && file_exists(FCPATH . $informe[$campo])) {
                unlink(FCPATH . $informe[$campo]);
            }
        }
        if (!empty($informe['ruta_pdf']) && file_exists(FCPATH . $informe['ruta_pdf'])) {
            unlink(FCPATH . $informe['ruta_pdf']);
        }

        $this->informeModel->delete($id);

        return redirect()->to('/informe-avances')->with('msg', 'Informe eliminado');
    }

    // ─── AJAX: Calcular métricas ───
    public function calcularMetricas($idCliente)
    {
        $service = new MetricasInformeService();

        $fechaDesde = $this->request->getGet('fecha_desde') ?: ($service->getFechaDesde($idCliente) ?: date('Y-m-01'));
        $fechaHasta = $this->request->getGet('fecha_hasta') ?: date('Y-m-d');

        $metricas = $service->calcularTodas($idCliente, $fechaDesde, $fechaHasta);

        return $this->response->setJSON([
            'success' => true,
            'data'    => $metricas,
        ]);
    }

    // ─── AJAX: Generar resumen con IA ───
    public function generarResumen()
    {
        $idCliente = (int) $this->request->getPost('id_cliente');
        $fechaDesde = $this->request->getPost('fecha_desde');
        $fechaHasta = $this->request->getPost('fecha_hasta');

        if (!$idCliente || !$fechaDesde || !$fechaHasta) {
            return $this->response->setJSON(['success' => false, 'error' => 'Datos incompletos']);
        }

        $service = new MetricasInformeService();
        $metricas = $service->calcularTodas($idCliente, $fechaDesde, $fechaHasta);
        $actividades = $service->recopilarActividadesPeriodo($idCliente, $fechaDesde, $fechaHasta);

        $clientModel = new ClientModel();
        $cliente = $clientModel->find($idCliente);
        $nombreCliente = $cliente['nombre_cliente'] ?? 'Cliente';

        $prompt = $this->buildResumenPrompt($nombreCliente, $fechaDesde, $fechaHasta, $actividades, $metricas);

        try {
            $iaService = new IADocumentacionService();
            $resumen = $iaService->generarContenido($prompt, 2000);
            return $this->response->setJSON(['success' => true, 'resumen' => $resumen]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // ─── AJAX: API Clientes (para Select2) ───
    public function getClientes()
    {
        $clientModel = new ClientModel();

        $clientes = $clientModel->select('tbl_clientes.id_cliente, tbl_clientes.nombre_cliente, tbl_clientes.nit_cliente')
            ->join('tbl_contratos', "tbl_contratos.id_cliente = tbl_clientes.id_cliente AND tbl_contratos.estado = 'activo'")
            ->orderBy('tbl_clientes.nombre_cliente', 'ASC')
            ->findAll();

        return $this->response->setJSON($clientes);
    }

    // ─── API: Clientes que tuvieron visita en el periodo (para OpenClaw) ───
    public function getClientesConVisita()
    {
        $actaModel = new ActaVisitaModel();

        // Periodo: desde ultimo informe global o ultimos 3 meses por defecto
        $fechaDesde = $this->request->getGet('fecha_desde') ?: date('Y-m-d', strtotime('-3 months'));
        $fechaHasta = $this->request->getGet('fecha_hasta') ?: date('Y-m-d');

        $clientes = $actaModel->select('
                tbl_clientes.id_cliente,
                tbl_clientes.nombre_cliente,
                tbl_clientes.nit_cliente,
                tbl_clientes.correo_cliente,
                COUNT(tbl_acta_visita.id) as total_visitas,
                MAX(tbl_acta_visita.fecha_visita) as ultima_visita
            ')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_acta_visita.id_cliente')
            ->join('tbl_contratos', "tbl_contratos.id_cliente = tbl_clientes.id_cliente AND tbl_contratos.estado = 'activo'", 'inner')
            ->where('tbl_acta_visita.fecha_visita >=', $fechaDesde)
            ->where('tbl_acta_visita.fecha_visita <=', $fechaHasta)
            ->groupBy('tbl_clientes.id_cliente')
            ->orderBy('tbl_clientes.nombre_cliente', 'ASC')
            ->findAll();

        return $this->response->setJSON([
            'success'     => true,
            'periodo'     => ['desde' => $fechaDesde, 'hasta' => $fechaHasta],
            'total'       => count($clientes),
            'clientes'    => $clientes,
        ]);
    }

    // ─── PRIVATE: Recoger datos del POST ───
    private function getInformePostData(): array
    {
        return [
            'id_cliente'                   => $this->request->getPost('id_cliente'),
            'fecha_desde'                  => $this->request->getPost('fecha_desde'),
            'fecha_hasta'                  => $this->request->getPost('fecha_hasta'),
            'puntaje_anterior'             => $this->request->getPost('puntaje_anterior'),
            'puntaje_actual'               => $this->request->getPost('puntaje_actual'),
            'diferencia_neta'              => $this->request->getPost('diferencia_neta'),
            'estado_avance'                => $this->request->getPost('estado_avance'),
            'indicador_plan_trabajo'       => $this->request->getPost('indicador_plan_trabajo'),
            'indicador_capacitacion'       => $this->request->getPost('indicador_capacitacion'),
            'resumen_avance'               => $this->request->getPost('resumen_avance'),
            'observaciones'                => $this->request->getPost('observaciones'),
            'actividades_abiertas'         => $this->request->getPost('actividades_abiertas'),
            'actividades_cerradas_periodo' => $this->request->getPost('actividades_cerradas_periodo'),
            'enlace_dashboard'             => $this->request->getPost('enlace_dashboard'),
            'acta_visita_url'              => $this->request->getPost('acta_visita_url'),
            'soporte_1_texto'              => $this->request->getPost('soporte_1_texto'),
            'soporte_2_texto'              => $this->request->getPost('soporte_2_texto'),
            'soporte_3_texto'              => $this->request->getPost('soporte_3_texto'),
            'soporte_4_texto'              => $this->request->getPost('soporte_4_texto'),
        ];
    }

    // ─── PRIVATE: Upload foto ───
    private function uploadFoto(string $campo, string $dir): ?string
    {
        $file = $this->request->getFile($campo);
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return null;
        }

        if (!is_dir(FCPATH . $dir)) {
            mkdir(FCPATH . $dir, 0755, true);
        }

        $newName = $campo . '_' . time() . '_' . $file->getRandomName();
        $file->move(FCPATH . $dir, $newName);

        return $dir . $newName;
    }

    // ─── PRIVATE: Generar PDF interno ───
    private function generarPdfInterno(int $id): ?string
    {
        $informe = $this->informeModel->find($id);
        if (!$informe) return null;

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $cliente = $clientModel->find($informe['id_cliente']);
        $consultor = $consultantModel->find($informe['id_consultor']);

        // Logo en base64
        $logoBase64 = '';
        if (!empty($cliente['logo'])) {
            $logoPath = FCPATH . 'uploads/' . $cliente['logo'];
            if (file_exists($logoPath)) {
                $logoMime = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        // Soportes a base64
        $soportesBase64 = [];
        for ($i = 1; $i <= 4; $i++) {
            $soportesBase64[$i] = '';
            $imgField = "soporte_{$i}_imagen";
            if (!empty($informe[$imgField])) {
                $imgPath = FCPATH . $informe[$imgField];
                if (file_exists($imgPath)) {
                    $mime = mime_content_type($imgPath);
                    $soportesBase64[$i] = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($imgPath));
                }
            }
        }

        $data = [
            'informe'        => $informe,
            'cliente'        => $cliente,
            'consultor'      => $consultor,
            'logoBase64'     => $logoBase64,
            'soportesBase64' => $soportesBase64,
        ];

        $html = view('informe_avances/pdf', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $pdfDir = 'uploads/informe-avances/pdfs/';
        if (!is_dir(FCPATH . $pdfDir)) {
            mkdir(FCPATH . $pdfDir, 0755, true);
        }

        $pdfFileName = 'informe_avances_' . $id . '_' . date('Ymd_His') . '.pdf';
        $pdfPath = $pdfDir . $pdfFileName;

        // Eliminar PDF anterior
        if (!empty($informe['ruta_pdf']) && file_exists(FCPATH . $informe['ruta_pdf'])) {
            unlink(FCPATH . $informe['ruta_pdf']);
        }

        file_put_contents(FCPATH . $pdfPath, $dompdf->output());

        return $pdfPath;
    }

    // ─── PRIVATE: Upload to reportes ───
    private function uploadToReportes(array $informe, string $pdfPath): bool
    {
        $reporteModel = new ReporteModel();
        $clientModel = new ClientModel();
        $cliente = $clientModel->find($informe['id_cliente']);
        if (!$cliente) return false;

        $nitCliente = $cliente['nit_cliente'];

        $existente = $reporteModel
            ->where('id_cliente', $informe['id_cliente'])
            ->where('id_report_type', 6)
            ->where('id_detailreport', 37)
            ->like('observaciones', 'inf_avance_id:' . $informe['id'])
            ->first();

        $destDir = ROOTPATH . 'public/uploads/' . $nitCliente;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $periodo = date('M-Y', strtotime($informe['fecha_desde'])) . '_' . date('M-Y', strtotime($informe['fecha_hasta']));
        $fileName = 'informe_avances_' . $informe['id'] . '_' . $periodo . '.pdf';
        $destPath = $destDir . '/' . $fileName;
        copy(FCPATH . $pdfPath, $destPath);

        $data = [
            'titulo_reporte'  => 'INFORME DE AVANCES - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $periodo,
            'id_detailreport' => 37,
            'id_report_type'  => 6,
            'id_cliente'      => $informe['id_cliente'],
            'estado'          => 'Activo',
            'observaciones'   => 'Generado automaticamente. inf_avance_id:' . $informe['id'],
            'enlace'          => base_url('uploads/' . $nitCliente . '/' . $fileName),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        if ($existente) {
            return $reporteModel->update($existente['id_reporte'], $data);
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        return $reporteModel->save($data);
    }

    // ─── PRIVATE: Construir prompt IA para resumen ───
    private function buildResumenPrompt(string $nombreCliente, string $desde, string $hasta, array $actividades, array $metricas): string
    {
        $actividadesTexto = empty($actividades) ? 'No se registraron actividades en el periodo.' : implode("\n", $actividades);

        $estado = $metricas['estado_avance'] ?? 'ESTABLE';
        $puntajeActual = $metricas['puntaje_actual'] ?? 0;
        $puntajeAnterior = $metricas['puntaje_anterior'] ?? 'N/A';
        $diferencia = $metricas['diferencia_neta'] ?? 0;
        $planTrabajo = $metricas['indicador_plan_trabajo'] ?? 0;
        $capacitacion = $metricas['indicador_capacitacion'] ?? 0;

        return <<<PROMPT
Eres un consultor senior de Seguridad y Salud en el Trabajo (SG-SST) en Colombia.

Genera un resumen ejecutivo de avance del SG-SST para el cliente "{$nombreCliente}" correspondiente al periodo {$desde} a {$hasta}.

DATOS DEL PERIODO:
- Puntaje cumplimiento estándares mínimos actual: {$puntajeActual}%
- Puntaje periodo anterior: {$puntajeAnterior}%
- Diferencia neta: {$diferencia} puntos porcentuales
- Estado de avance: {$estado}
- Indicador plan de trabajo anual: {$planTrabajo}%
- Indicador programa de capacitación: {$capacitacion}%

ACTIVIDADES REALIZADAS EN EL PERIODO:
{$actividadesTexto}

INSTRUCCIONES:
1. Escribe en tercera persona, tono profesional y técnico.
2. Menciona las actividades más relevantes del periodo.
3. Analiza los indicadores y su tendencia.
4. Si hay avance, resáltalo. Si hay retroceso, indica las posibles causas y recomendaciones.
5. Máximo 4 párrafos.
6. No uses viñetas ni listas, solo prosa continua.
7. No incluyas saludos ni despedidas.
PROMPT;
    }

    // ─── ENVIAR INFORME POR EMAIL (SendGrid) ───
    public function enviar($id)
    {
        $informe = $this->informeModel->find($id);
        if (!$informe) {
            return $this->response->setJSON(['success' => false, 'error' => 'Informe no encontrado']);
        }

        if ($informe['estado'] !== 'completo' || empty($informe['ruta_pdf'])) {
            return $this->response->setJSON(['success' => false, 'error' => 'El informe debe estar finalizado con PDF generado']);
        }

        $clientModel = new ClientModel();
        $cliente = $clientModel->find($informe['id_cliente']);
        if (!$cliente || empty($cliente['correo_cliente'])) {
            return $this->response->setJSON(['success' => false, 'error' => 'Cliente sin correo electronico configurado']);
        }

        $pdfPath = FCPATH . $informe['ruta_pdf'];
        if (!file_exists($pdfPath)) {
            return $this->response->setJSON(['success' => false, 'error' => 'Archivo PDF no encontrado en disco']);
        }

        $sendgridApiKey = getenv('SENDGRID_API_KEY');
        if (!$sendgridApiKey) {
            return $this->response->setJSON(['success' => false, 'error' => 'SENDGRID_API_KEY no configurada']);
        }

        $periodo = date('d/m/Y', strtotime($informe['fecha_desde'])) . ' - ' . date('d/m/Y', strtotime($informe['fecha_hasta']));
        $nombreCliente = $cliente['nombre_cliente'] ?? 'Cliente';
        $estadoAvance = $informe['estado_avance'] ?? 'ESTABLE';
        $puntaje = number_format($informe['puntaje_actual'] ?? 0, 1);

        $subject = "Informe de Avances SG-SST - {$nombreCliente} - {$periodo}";

        $htmlContent = "
        <div style='font-family: Segoe UI, Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: #1c2437; padding: 20px; text-align: center;'>
                <h1 style='color: #bd9751; margin: 0; font-size: 20px;'>INFORME DE AVANCES SG-SST</h1>
            </div>
            <div style='padding: 25px; background: #f8f9fa;'>
                <p>Estimado(a) equipo de <strong>{$nombreCliente}</strong>,</p>
                <p>Adjunto encontrara el Informe de Avances del Sistema de Gestion de Seguridad y Salud en el Trabajo correspondiente al periodo <strong>{$periodo}</strong>.</p>
                <table style='width: 100%; border-collapse: collapse; margin: 15px 0;'>
                    <tr>
                        <td style='padding: 10px; background: #fff; border: 1px solid #ddd;'><strong>Cumplimiento Estandares:</strong></td>
                        <td style='padding: 10px; background: #fff; border: 1px solid #ddd; text-align: center; font-weight: bold; color: #bd9751;'>{$puntaje}%</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px; background: #fff; border: 1px solid #ddd;'><strong>Estado de Avance:</strong></td>
                        <td style='padding: 10px; background: #fff; border: 1px solid #ddd; text-align: center; font-weight: bold;'>{$estadoAvance}</td>
                    </tr>
                </table>
                <p>Por favor revise el documento adjunto para mayor detalle.</p>
                <p style='color: #666; font-size: 12px; margin-top: 30px;'>Este correo fue generado automaticamente por el SG-SST de Cycloid Talent.</p>
            </div>
        </div>";

        // Enviar con SendGrid SDK
        require_once ROOTPATH . 'vendor/autoload.php';

        $email = new \SendGrid\Mail\Mail();
        $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "Cycloid Talent - SG-SST");
        $email->setSubject($subject);
        $email->addTo($cliente['correo_cliente'], $nombreCliente);
        $email->addContent("text/html", $htmlContent);

        // Adjuntar PDF
        $pdfContent = base64_encode(file_get_contents($pdfPath));
        $pdfFilename = 'Informe_Avances_' . str_replace(' ', '_', $nombreCliente) . '_' . date('Y-m', strtotime($informe['fecha_hasta'])) . '.pdf';
        $email->addAttachment($pdfContent, 'application/pdf', $pdfFilename, 'attachment');

        $sendgrid = new \SendGrid($sendgridApiKey);

        try {
            $response = $sendgrid->send($email);

            if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                log_message('info', "InformeAvances: Email enviado a {$cliente['correo_cliente']} para informe #{$id}");
                return $this->response->setJSON([
                    'success' => true,
                    'message' => "Informe enviado a {$cliente['correo_cliente']}",
                    'destinatario' => $cliente['correo_cliente'],
                ]);
            } else {
                log_message('error', "InformeAvances: Error SendGrid. Status: {$response->statusCode()}. Body: {$response->body()}");
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Error al enviar email. Status: ' . $response->statusCode(),
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'InformeAvances: Exception SendGrid: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }

    // ─── API: Flujo completo (crear + finalizar + enviar) para OpenClaw ───
    public function apiGenerarYEnviar($idCliente)
    {
        $clientModel = new ClientModel();
        $cliente = $clientModel->find($idCliente);
        if (!$cliente) {
            return $this->response->setJSON(['success' => false, 'error' => 'Cliente no encontrado']);
        }

        $service = new MetricasInformeService();

        // Calcular fechas y métricas
        $fechaDesde = $service->getFechaDesde($idCliente) ?: date('Y-m-01');
        $fechaHasta = date('Y-m-d');

        // Validar que el cliente tuvo al menos una visita en el periodo
        $actaModel = new ActaVisitaModel();
        $visitasEnPeriodo = $actaModel
            ->where('id_cliente', $idCliente)
            ->where('fecha_visita >=', $fechaDesde)
            ->where('fecha_visita <=', $fechaHasta)
            ->countAllResults();

        if ($visitasEnPeriodo === 0) {
            return $this->response->setJSON([
                'success' => false,
                'error'   => 'No se puede generar informe: el cliente no tiene actas de visita en el periodo ' . $fechaDesde . ' a ' . $fechaHasta,
                'cliente' => $cliente['nombre_cliente'],
                'periodo' => ['desde' => $fechaDesde, 'hasta' => $fechaHasta],
            ]);
        }
        $metricas = $service->calcularTodas($idCliente, $fechaDesde, $fechaHasta);

        // Generar resumen IA
        $resumen = '';
        try {
            $actividades = $service->recopilarActividadesPeriodo($idCliente, $fechaDesde, $fechaHasta);
            $iaService = new IADocumentacionService();
            $prompt = $this->buildResumenPrompt($cliente['nombre_cliente'], $fechaDesde, $fechaHasta, $actividades, $metricas);
            $resumen = $iaService->generarContenido($prompt, 2000);
        } catch (\Exception $e) {
            $resumen = 'Resumen no disponible: ' . $e->getMessage();
        }

        // Crear informe
        $data = [
            'id_cliente'                   => $idCliente,
            'id_consultor'                 => $cliente['id_consultor'] ?? 1,
            'fecha_desde'                  => $fechaDesde,
            'fecha_hasta'                  => $fechaHasta,
            'anio'                         => date('Y'),
            'puntaje_anterior'             => $metricas['puntaje_anterior'],
            'puntaje_actual'               => $metricas['puntaje_actual'],
            'diferencia_neta'              => $metricas['diferencia_neta'],
            'estado_avance'                => $metricas['estado_avance'],
            'indicador_plan_trabajo'       => $metricas['indicador_plan_trabajo'],
            'indicador_capacitacion'       => $metricas['indicador_capacitacion'],
            'resumen_avance'               => $resumen,
            'actividades_abiertas'         => $metricas['actividades_abiertas'],
            'actividades_cerradas_periodo' => $metricas['actividades_cerradas_periodo'],
            'enlace_dashboard'             => $metricas['enlace_dashboard'],
            'estado'                       => 'borrador',
        ];

        $this->informeModel->insert($data);
        $informeId = $this->informeModel->getInsertID();

        // Finalizar (generar PDF)
        $pdfPath = $this->generarPdfInterno($informeId);
        if (!$pdfPath) {
            return $this->response->setJSON(['success' => false, 'error' => 'Error generando PDF', 'informe_id' => $informeId]);
        }

        $this->informeModel->update($informeId, [
            'estado'   => 'completo',
            'ruta_pdf' => $pdfPath,
        ]);

        $informe = $this->informeModel->find($informeId);
        $this->uploadToReportes($informe, $pdfPath);

        // Enviar por email
        $envioResult = $this->enviarInterno($informe, $cliente, $pdfPath);

        return $this->response->setJSON([
            'success'     => true,
            'informe_id'  => $informeId,
            'pdf_url'     => base_url($pdfPath),
            'email'       => $envioResult,
        ]);
    }

    // ─── PRIVATE: Envío interno (reutilizable) ───
    private function enviarInterno(array $informe, array $cliente, string $pdfPath): array
    {
        if (empty($cliente['correo_cliente'])) {
            return ['success' => false, 'error' => 'Cliente sin correo'];
        }

        $sendgridApiKey = getenv('SENDGRID_API_KEY');
        if (!$sendgridApiKey) {
            return ['success' => false, 'error' => 'SENDGRID_API_KEY no configurada'];
        }

        $fullPdfPath = FCPATH . $pdfPath;
        if (!file_exists($fullPdfPath)) {
            return ['success' => false, 'error' => 'PDF no encontrado'];
        }

        $periodo = date('d/m/Y', strtotime($informe['fecha_desde'])) . ' - ' . date('d/m/Y', strtotime($informe['fecha_hasta']));
        $nombreCliente = $cliente['nombre_cliente'] ?? 'Cliente';
        $puntaje = number_format($informe['puntaje_actual'] ?? 0, 1);

        $htmlContent = "
        <div style='font-family: Segoe UI, Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: #1c2437; padding: 20px; text-align: center;'>
                <h1 style='color: #bd9751; margin: 0; font-size: 20px;'>INFORME DE AVANCES SG-SST</h1>
            </div>
            <div style='padding: 25px; background: #f8f9fa;'>
                <p>Estimado(a) equipo de <strong>{$nombreCliente}</strong>,</p>
                <p>Adjunto el Informe de Avances del SG-SST periodo <strong>{$periodo}</strong>.</p>
                <p><strong>Cumplimiento:</strong> {$puntaje}% | <strong>Estado:</strong> {$informe['estado_avance']}</p>
                <p style='color: #666; font-size: 12px; margin-top: 20px;'>Generado automaticamente - Cycloid Talent SG-SST</p>
            </div>
        </div>";

        require_once ROOTPATH . 'vendor/autoload.php';

        $email = new \SendGrid\Mail\Mail();
        $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "Cycloid Talent - SG-SST");
        $email->setSubject("Informe de Avances SG-SST - {$nombreCliente} - {$periodo}");
        $email->addTo($cliente['correo_cliente'], $nombreCliente);
        $email->addContent("text/html", $htmlContent);
        $email->addAttachment(
            base64_encode(file_get_contents($fullPdfPath)),
            'application/pdf',
            'Informe_Avances_' . str_replace(' ', '_', $nombreCliente) . '.pdf',
            'attachment'
        );

        try {
            $sendgrid = new \SendGrid($sendgridApiKey);
            $response = $sendgrid->send($email);

            if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                return ['success' => true, 'destinatario' => $cliente['correo_cliente']];
            }
            return ['success' => false, 'error' => 'SendGrid status: ' . $response->statusCode()];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
