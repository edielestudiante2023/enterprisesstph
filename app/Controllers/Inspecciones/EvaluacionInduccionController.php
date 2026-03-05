<?php

namespace App\Controllers\Inspecciones;

use App\Controllers\BaseController;
use App\Models\EvaluacionInduccionModel;
use App\Models\EvaluacionInduccionRespuestaModel;
use App\Models\AsistenciaInduccionModel;
use App\Models\ClientModel;

class EvaluacionInduccionController extends BaseController
{
    protected EvaluacionInduccionModel $evalModel;
    protected EvaluacionInduccionRespuestaModel $respuestaModel;

    public function __construct()
    {
        $this->evalModel      = new EvaluacionInduccionModel();
        $this->respuestaModel = new EvaluacionInduccionRespuestaModel();
    }

    // ── ADMIN ────────────────────────────────────────────────────────────────

    /**
     * Ver resultados de una evaluación (requiere auth).
     */
    public function resultados(int $id)
    {
        $evaluacion = $this->evalModel->find($id);
        if (!$evaluacion) {
            return redirect()->to('/inspecciones/asistencia-induccion')->with('error', 'Evaluación no encontrada');
        }

        $clientModel = new ClientModel();
        $respuestas  = $this->respuestaModel->getByEvaluacion($id);
        $promedio    = $this->respuestaModel->getPromedioByEvaluacion($id);

        $data = [
            'title'      => 'Resultados Evaluación',
            'evaluacion' => $evaluacion,
            'cliente'    => $clientModel->find($evaluacion['id_cliente']),
            'respuestas' => $respuestas,
            'promedio'   => $promedio,
            'preguntas'  => EvaluacionInduccionModel::PREGUNTAS,
        ];

        return view('inspecciones/layout_pwa', [
            'content' => view('inspecciones/evaluacion-induccion/resultados', $data),
            'title'   => 'Resultados Evaluación',
        ]);
    }

    /**
     * API: resultados por id_asistencia (para cargar en reporte-capacitacion).
     */
    public function apiResultados()
    {
        $idAsistencia = (int) $this->request->getGet('id_asistencia');
        if (!$idAsistencia) {
            return $this->response->setJSON(['success' => false, 'data' => []]);
        }

        $evaluacion = $this->evalModel->getByAsistencia($idAsistencia);
        if (!$evaluacion) {
            return $this->response->setJSON(['success' => false, 'data' => [], 'msg' => 'Sin evaluación creada']);
        }

        $respuestas = $this->respuestaModel->getByEvaluacion((int) $evaluacion['id']);
        $promedio   = $this->respuestaModel->getPromedioByEvaluacion((int) $evaluacion['id']);

        return $this->response->setJSON([
            'success'   => true,
            'evaluacion'=> $evaluacion,
            'respuestas'=> $respuestas,
            'promedio'  => $promedio,
        ]);
    }

    /**
     * API: resultados por cliente + fecha (para reporte-capacitacion).
     */
    public function apiResultadosPorFecha()
    {
        $idCliente = (int) $this->request->getGet('id_cliente');
        $fecha     = $this->request->getGet('fecha');

        if (!$idCliente || !$fecha) {
            return $this->response->setJSON(['success' => false, 'data' => []]);
        }

        // Buscar sesión de asistencia_induccion para ese cliente y fecha
        $asistenciaModel = new AsistenciaInduccionModel();
        $sesion = $asistenciaModel
            ->where('id_cliente', $idCliente)
            ->where('fecha_sesion', $fecha)
            ->where('tipo_charla', 'induccion_reinduccion')
            ->first();

        if (!$sesion) {
            return $this->response->setJSON(['success' => false, 'msg' => 'No hay sesión de inducción/reinducción para este cliente y fecha']);
        }

        $evaluacion = $this->evalModel->getByAsistencia((int) $sesion['id']);
        if (!$evaluacion) {
            return $this->response->setJSON(['success' => false, 'msg' => 'Evaluación no habilitada para esta sesión', 'id_asistencia' => $sesion['id']]);
        }

        $respuestas = $this->respuestaModel->getByEvaluacion((int) $evaluacion['id']);
        $promedio   = $this->respuestaModel->getPromedioByEvaluacion((int) $evaluacion['id']);

        return $this->response->setJSON([
            'success'   => true,
            'evaluacion'=> $evaluacion,
            'respuestas'=> $respuestas,
            'promedio'  => number_format($promedio, 2),
        ]);
    }

    // ── PÚBLICO (sin auth) ───────────────────────────────────────────────────

    /**
     * Mostrar formulario público de evaluación.
     * GET /evaluar/{token}
     */
    public function form(string $token)
    {
        $evaluacion = $this->evalModel->where('token', $token)->first();

        if (!$evaluacion || $evaluacion['estado'] === 'cerrado') {
            return view('inspecciones/evaluacion-induccion/cerrado');
        }

        $clientModel = new ClientModel();
        $conjuntos   = $clientModel->where('estado', 'activo')->orderBy('nombre_cliente', 'ASC')->findAll();

        $data = [
            'evaluacion' => $evaluacion,
            'conjuntos'  => $conjuntos,
            'preguntas'  => EvaluacionInduccionModel::PREGUNTAS,
        ];

        return view('inspecciones/evaluacion-induccion/form-publico', $data);
    }

    /**
     * Procesar respuestas del formulario público.
     * POST /evaluar/{token}/submit
     */
    public function submit(string $token)
    {
        $evaluacion = $this->evalModel->where('token', $token)->first();

        if (!$evaluacion || $evaluacion['estado'] === 'cerrado') {
            return redirect()->to('/evaluar/' . $token);
        }

        // Validaciones básicas
        $nombre = trim($this->request->getPost('nombre') ?? '');
        $cedula = trim($this->request->getPost('cedula') ?? '');

        if (!$nombre || !$cedula) {
            return redirect()->to('/evaluar/' . $token)->with('error', 'Nombre y cédula son obligatorios.');
        }

        if (!$this->request->getPost('acepta_tratamiento')) {
            return redirect()->to('/evaluar/' . $token)->with('error', 'Debe aceptar el tratamiento de datos personales para continuar.');
        }

        // Respuestas del cuestionario
        $respuestasRaw = $this->request->getPost('respuesta') ?? [];
        $calificacion  = EvaluacionInduccionModel::calcularCalificacion($respuestasRaw);

        $this->respuestaModel->insert([
            'id_evaluacion'       => $evaluacion['id'],
            'nombre'              => $nombre,
            'cedula'              => $cedula,
            'whatsapp'            => trim($this->request->getPost('whatsapp') ?? ''),
            'empresa_contratante' => trim($this->request->getPost('empresa_contratante') ?? ''),
            'cargo'               => trim($this->request->getPost('cargo') ?? ''),
            'id_cliente_conjunto' => (int) ($this->request->getPost('id_cliente_conjunto') ?? 0) ?: null,
            'acepta_tratamiento'  => 1,
            'respuestas'          => json_encode($respuestasRaw),
            'calificacion'        => $calificacion,
        ]);

        return redirect()->to('/evaluar/' . $token . '/gracias?cal=' . $calificacion);
    }

    /**
     * Pantalla de gracias tras enviar evaluación.
     * GET /evaluar/{token}/gracias
     */
    public function gracias(string $token)
    {
        $evaluacion = $this->evalModel->where('token', $token)->first();
        if (!$evaluacion) {
            return redirect()->to('/');
        }

        $cal = (float) ($this->request->getGet('cal') ?? 0);

        return view('inspecciones/evaluacion-induccion/gracias', [
            'evaluacion'   => $evaluacion,
            'calificacion' => $cal,
        ]);
    }
}
