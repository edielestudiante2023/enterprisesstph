<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\EvaluacionInduccionModel;
use App\Models\EvaluacionInduccionRespuestaModel;
use App\Models\EvaluacionSesionModel;
use App\Models\ClientModel;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QROutputInterface;

class EvaluacionInduccionController extends BaseController
{
    protected EvaluacionInduccionModel $evalModel;
    protected EvaluacionInduccionRespuestaModel $respuestaModel;

    public function __construct()
    {
        $this->evalModel      = new EvaluacionInduccionModel();
        $this->respuestaModel = new EvaluacionInduccionRespuestaModel();
    }

    // ══════════════════════════════════════════════════════════════════════════
    // CRUD ADMIN (requiere auth, dentro de /inspecciones/...)
    // ══════════════════════════════════════════════════════════════════════════

    public function list()
    {
        $evaluaciones = $this->evalModel
            ->select('tbl_evaluacion_induccion.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_evaluacion_induccion.id_cliente', 'left')
            ->orderBy('tbl_evaluacion_induccion.created_at', 'DESC')
            ->findAll();

        // Estadísticas por evaluación — solo mostrar las que tienen respuestas
        $conRespuestas = [];
        foreach ($evaluaciones as $e) {
            $respuestas = $this->respuestaModel->where('id_evaluacion', $e['id'])->findAll();
            $e['total_respuestas'] = count($respuestas);
            if ($e['total_respuestas'] === 0) continue;
            $calificaciones  = array_column($respuestas, 'calificacion');
            $e['promedio']   = round(array_sum($calificaciones) / count($calificaciones), 1);
            $e['aprobados']  = count(array_filter($calificaciones, fn($c) => $c >= 70));
            $conRespuestas[] = $e;
        }
        $evaluaciones = $conRespuestas;

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/evaluacion-induccion/list', ['evaluaciones' => $evaluaciones]),
            'title'   => 'Evaluaciones Inducción SST',
        ]);
    }

    public function create()
    {
        $clientModel = new ClientModel();

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/evaluacion-induccion/form', [
                'evaluacion' => null,
                'clientes'   => $clientModel->where('estado', 'activo')->orderBy('nombre_cliente')->findAll(),
            ]),
            'title' => 'Nueva Evaluación',
        ]);
    }

    public function store()
    {
        $token = bin2hex(random_bytes(20));

        $this->evalModel->insert([
            'id_cliente' => (int) $this->request->getPost('id_cliente'),
            'titulo'     => trim($this->request->getPost('titulo')) ?: 'Evaluación Inducción SST',
            'token'      => $token,
            'estado'     => 'activo',
        ]);
        $id = $this->evalModel->getInsertID();

        return redirect()->to('/inspecciones/evaluacion-induccion/view/' . $id)
            ->with('msg', 'Evaluación creada. Comparte el enlace o QR con los asistentes.');
    }

    public function edit(int $id)
    {
        $evaluacion = $this->evalModel->find($id);
        if (!$evaluacion) {
            return redirect()->to('/inspecciones/evaluacion-induccion')->with('error', 'No encontrada');
        }

        $clientModel = new ClientModel();

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/evaluacion-induccion/form', [
                'evaluacion' => $evaluacion,
                'clientes'   => $clientModel->where('estado', 'activo')->orderBy('nombre_cliente')->findAll(),
            ]),
            'title' => 'Editar Evaluación',
        ]);
    }

    public function update(int $id)
    {
        $evaluacion = $this->evalModel->find($id);
        if (!$evaluacion) {
            return redirect()->to('/inspecciones/evaluacion-induccion')->with('error', 'No encontrada');
        }

        $this->evalModel->update($id, [
            'id_cliente' => (int) $this->request->getPost('id_cliente'),
            'titulo'     => trim($this->request->getPost('titulo')) ?: 'Evaluación Inducción SST',
            'estado'     => $this->request->getPost('estado') ?: 'activo',
        ]);

        return redirect()->to('/inspecciones/evaluacion-induccion/view/' . $id)
            ->with('msg', 'Evaluación actualizada.');
    }

    public function view(int $id)
    {
        $evaluacion = $this->evalModel->find($id);
        if (!$evaluacion) {
            return redirect()->to('/inspecciones/evaluacion-induccion')->with('error', 'No encontrada');
        }

        $clientModel   = new ClientModel();
        $sesionModel   = new EvaluacionSesionModel();
        $respuestasAll = $this->respuestaModel->getByEvaluacion($id);
        $promedio      = $this->respuestaModel->getPromedioByEvaluacion($id);

        // Sesiones agrupadas por cliente+fecha con estadísticas
        $sesiones = $sesionModel->getSesionesByEvaluacion($id);
        foreach ($sesiones as &$s) {
            $db   = \Config\Database::connect();
            $resp = $db->table('tbl_evaluacion_induccion_respuesta')
                ->where('id_evaluacion', $id)
                ->where('id_cliente_conjunto', $s['id_cliente'])
                ->where('DATE(created_at)', $s['fecha_sesion'])
                ->orderBy('calificacion', 'DESC')
                ->get()->getResultArray();
            $s['respuestas'] = $resp;
            $s['total']      = count($resp);
            $cals            = array_column($resp, 'calificacion');
            $s['promedio']   = $s['total'] > 0 ? round(array_sum($cals) / $s['total'], 1) : 0;
            $s['aprobados']  = count(array_filter($cals, fn($c) => $c >= 70));
        }
        unset($s);

        // Respuestas sin cliente asignado
        $sinCliente = array_values(array_filter($respuestasAll, fn($r) => empty($r['id_cliente_conjunto'])));

        // QR como base64 inline
        $qrUrl    = base_url('evaluar/' . $evaluacion['token']);
        $qrBase64 = $this->generarQrBase64($qrUrl);

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/evaluacion-induccion/view', [
                'evaluacion' => $evaluacion,
                'cliente'    => $clientModel->find($evaluacion['id_cliente']),
                'respuestas' => $respuestasAll,
                'sesiones'   => $sesiones,
                'sinCliente' => $sinCliente,
                'promedio'   => $promedio,
                'preguntas'  => EvaluacionInduccionModel::PREGUNTAS,
                'qrBase64'   => $qrBase64,
            ]),
            'title' => 'Ver Evaluación',
        ]);
    }

    public function delete(int $id)
    {
        $evaluacion = $this->evalModel->find($id);
        if (!$evaluacion) {
            return redirect()->to('/inspecciones/evaluacion-induccion')->with('error', 'No encontrada');
        }

        $this->respuestaModel->where('id_evaluacion', $id)->delete();
        $this->evalModel->delete($id);

        return redirect()->to('/inspecciones/evaluacion-induccion')
            ->with('msg', 'Evaluación eliminada.');
    }

    public function toggleEstado(int $id)
    {
        $evaluacion = $this->evalModel->find($id);
        if (!$evaluacion) {
            return redirect()->to('/inspecciones/evaluacion-induccion')->with('error', 'No encontrada');
        }

        $nuevoEstado = $evaluacion['estado'] === 'activo' ? 'cerrado' : 'activo';
        $this->evalModel->update($id, ['estado' => $nuevoEstado]);

        $msg = $nuevoEstado === 'activo' ? 'Evaluación reabierta.' : 'Evaluación cerrada.';
        return redirect()->to('/inspecciones/evaluacion-induccion/view/' . $id)->with('msg', $msg);
    }

    // ── API para ReporteCapacitacion ─────────────────────────────────────────

    public function apiResultadosPorFecha()
    {
        $idCliente = (int) $this->request->getGet('id_cliente');
        $fecha     = $this->request->getGet('fecha');

        if (!$idCliente || !$fecha) {
            return $this->response->setJSON(['success' => false, 'data' => []]);
        }

        // Buscar evaluaciones de este cliente: misma semana alrededor de la fecha de capacitación
        $fechaDesde = date('Y-m-d', strtotime($fecha . ' -7 days'));
        $fechaHasta = date('Y-m-d', strtotime($fecha . ' +7 days'));

        $evaluaciones = $this->evalModel
            ->where('id_cliente', $idCliente)
            ->where('DATE(created_at) >=', $fechaDesde)
            ->where('DATE(created_at) <=', $fechaHasta)
            ->findAll();

        if (empty($evaluaciones)) {
            return $this->response->setJSON(['success' => false, 'msg' => 'No hay evaluaciones para este cliente y fecha.']);
        }

        $evalIds    = array_column($evaluaciones, 'id');
        $respuestas = $this->respuestaModel->whereIn('id_evaluacion', $evalIds)->orderBy('calificacion', 'DESC')->findAll();

        if (empty($respuestas)) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Evaluación existe pero sin respuestas aún.']);
        }

        $total = count($respuestas);
        $suma  = array_sum(array_column($respuestas, 'calificacion'));
        $prom  = $total > 0 ? round($suma / $total, 2) : 0;

        return $this->response->setJSON([
            'success'    => true,
            'respuestas' => $respuestas,
            'promedio'   => number_format($prom, 2),
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // PÚBLICO (sin auth) — /evaluar/{token}
    // ══════════════════════════════════════════════════════════════════════════

    public function form(string $token)
    {
        $evaluacion = $this->evalModel->where('token', $token)->first();

        if (!$evaluacion || $evaluacion['estado'] === 'cerrado') {
            return view('inspecciones/evaluacion-induccion/cerrado');
        }

        $clientModel = new ClientModel();

        return view('inspecciones/evaluacion-induccion/form-publico', [
            'evaluacion' => $evaluacion,
            'conjuntos'  => $clientModel->where('estado', 'activo')->orderBy('nombre_cliente', 'ASC')->findAll(),
            'preguntas'  => EvaluacionInduccionModel::PREGUNTAS,
        ]);
    }

    public function submit(string $token)
    {
        $evaluacion = $this->evalModel->where('token', $token)->first();

        if (!$evaluacion || $evaluacion['estado'] === 'cerrado') {
            return redirect()->to('/evaluar/' . $token);
        }

        $nombre = trim($this->request->getPost('nombre') ?? '');
        $cedula = trim($this->request->getPost('cedula') ?? '');

        if (!$nombre || !$cedula) {
            return redirect()->to('/evaluar/' . $token)->with('error', 'Nombre y cédula son obligatorios.');
        }
        if (!$this->request->getPost('acepta_tratamiento')) {
            return redirect()->to('/evaluar/' . $token)->with('error', 'Debe aceptar el tratamiento de datos personales.');
        }

        $respuestasRaw = $this->request->getPost('respuesta') ?? [];
        $calificacion  = EvaluacionInduccionModel::calcularCalificacion($respuestasRaw);

        $idCliente = (int) ($this->request->getPost('id_cliente_conjunto') ?? 0) ?: null;

        $this->respuestaModel->insert([
            'id_evaluacion'       => $evaluacion['id'],
            'nombre'              => $nombre,
            'cedula'              => $cedula,
            'whatsapp'            => trim($this->request->getPost('whatsapp') ?? ''),
            'empresa_contratante' => trim($this->request->getPost('empresa_contratante') ?? ''),
            'cargo'               => trim($this->request->getPost('cargo') ?? ''),
            'id_cliente_conjunto' => $idCliente,
            'acepta_tratamiento'  => 1,
            'respuestas'          => json_encode($respuestasRaw),
            'calificacion'        => $calificacion,
        ]);

        // Auto-crear sesión para este cliente+fecha
        if ($idCliente) {
            $sesionModel = new EvaluacionSesionModel();
            $sesionModel->obtenerOCrear($evaluacion['id'], $idCliente, date('Y-m-d'));
        }

        return redirect()->to('/evaluar/' . $token . '/gracias?cal=' . $calificacion);
    }

    public function gracias(string $token)
    {
        $evaluacion = $this->evalModel->where('token', $token)->first();
        if (!$evaluacion) {
            return redirect()->to('/');
        }

        return view('inspecciones/evaluacion-induccion/gracias', [
            'evaluacion'   => $evaluacion,
            'calificacion' => (float) ($this->request->getGet('cal') ?? 0),
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // HELPERS
    // ══════════════════════════════════════════════════════════════════════════

    private function generarQrBase64(string $url): string
    {
        try {
            $options = new QROptions;
            $options->outputType    = QROutputInterface::GDIMAGE_PNG;
            $options->eccLevel      = EccLevel::H;
            $options->scale         = 10;
            $options->imageBase64   = true;
            $options->quietzoneSize = 2;
            return (new QRCode($options))->render($url);
        } catch (\Throwable $e) {
            log_message('error', 'QR generation failed: ' . $e->getMessage() . ' | trace: ' . $e->getTraceAsString());
            return '';
        }
    }
}
