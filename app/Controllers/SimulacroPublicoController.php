<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\EvaluacionSimulacroModel;
use App\Models\ClientModel;

class SimulacroPublicoController extends BaseController
{
    protected EvaluacionSimulacroModel $evalModel;

    public function __construct()
    {
        $this->evalModel = new EvaluacionSimulacroModel();
    }

    /**
     * GET /simulacro — Formulario público wizard (standalone, sin layout_pwa)
     */
    public function form()
    {
        return view('simulacro/form_publico');
    }

    /**
     * GET /simulacro/api/clientes — Clientes activos con contrato activo (AJAX, público)
     */
    public function getClientesActivos()
    {
        $clientModel = new ClientModel();

        $clientes = $clientModel
            ->select('tbl_clientes.id_cliente, tbl_clientes.nombre_cliente')
            ->join('tbl_contratos', "tbl_contratos.id_cliente = tbl_clientes.id_cliente AND tbl_contratos.estado = 'activo'")
            ->where('tbl_clientes.estado', 'activo')
            ->orderBy('tbl_clientes.nombre_cliente', 'ASC')
            ->groupBy('tbl_clientes.id_cliente')
            ->findAll();

        return $this->response->setJSON($clientes);
    }

    /**
     * POST /simulacro/save-step — Guarda parcial de cada paso del wizard (AJAX)
     * Body JSON: { step: int, data: {campo: valor, ...}, id: int|null }
     * Retorna: { success: true, id: int }
     */
    public function saveStep()
    {
        $json = $this->request->getJSON(true);

        if (!$json || !isset($json['step']) || !isset($json['data'])) {
            return $this->response->setJSON(['success' => false, 'error' => 'Datos incompletos'])->setStatusCode(400);
        }

        $step = (int)$json['step'];
        $data = $json['data'];
        $id = !empty($json['id']) ? (int)$json['id'] : null;

        // Campos permitidos por paso (whitelist)
        $camposPorPaso = $this->getCamposPorPaso();

        if (!isset($camposPorPaso[$step])) {
            return $this->response->setJSON(['success' => false, 'error' => 'Paso invalido'])->setStatusCode(400);
        }

        // Filtrar solo campos permitidos para este paso
        $allowedFields = $camposPorPaso[$step];
        $saveData = [];
        foreach ($allowedFields as $campo) {
            if (array_key_exists($campo, $data)) {
                $saveData[$campo] = $data[$campo];
            }
        }

        if (empty($saveData)) {
            return $this->response->setJSON(['success' => false, 'error' => 'No hay datos para guardar'])->setStatusCode(400);
        }

        if ($id) {
            // Actualizar registro existente
            $existing = $this->evalModel->find($id);
            if (!$existing) {
                return $this->response->setJSON(['success' => false, 'error' => 'Registro no encontrado'])->setStatusCode(404);
            }
            if ($existing['estado'] === 'completo') {
                return $this->response->setJSON(['success' => false, 'error' => 'Evaluacion ya finalizada'])->setStatusCode(400);
            }
            $this->evalModel->update($id, $saveData);
        } else {
            // Crear nuevo registro (paso 1 siempre incluye id_cliente)
            if ($step === 1 && empty($saveData['id_cliente'])) {
                return $this->response->setJSON(['success' => false, 'error' => 'Seleccione una copropiedad'])->setStatusCode(400);
            }
            $saveData['estado'] = 'borrador';
            $this->evalModel->insert($saveData);
            $id = $this->evalModel->getInsertID();
        }

        return $this->response->setJSON(['success' => true, 'id' => $id]);
    }

    /**
     * POST /simulacro/upload-foto — Sube foto de evidencia (AJAX)
     * FormData: file (imagen), id (int), campo (imagen_1|imagen_2)
     */
    public function uploadFoto()
    {
        $id = (int)$this->request->getPost('id');
        $campo = $this->request->getPost('campo');

        if (!$id || !in_array($campo, ['imagen_1', 'imagen_2'])) {
            return $this->response->setJSON(['success' => false, 'error' => 'Parametros invalidos'])->setStatusCode(400);
        }

        $existing = $this->evalModel->find($id);
        if (!$existing || $existing['estado'] === 'completo') {
            return $this->response->setJSON(['success' => false, 'error' => 'Registro no encontrado o ya finalizado'])->setStatusCode(400);
        }

        $file = $this->request->getFile('file');
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return $this->response->setJSON(['success' => false, 'error' => 'Archivo invalido'])->setStatusCode(400);
        }

        // Validar tipo
        $validTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($file->getMimeType(), $validTypes)) {
            return $this->response->setJSON(['success' => false, 'error' => 'Solo se permiten imagenes JPG, PNG o WebP'])->setStatusCode(400);
        }

        // Validar tamaño (max 10MB)
        if ($file->getSize() > 10 * 1024 * 1024) {
            return $this->response->setJSON(['success' => false, 'error' => 'La imagen no puede superar 10MB'])->setStatusCode(400);
        }

        $dir = 'uploads/inspecciones/simulacro/fotos/';
        if (!is_dir(FCPATH . $dir)) {
            mkdir(FCPATH . $dir, 0755, true);
        }

        // Borrar foto anterior si existe
        if (!empty($existing[$campo]) && file_exists(FCPATH . $existing[$campo])) {
            unlink(FCPATH . $existing[$campo]);
        }

        $fileName = $file->getRandomName();
        $file->move(FCPATH . $dir, $fileName);
        $path = $dir . $fileName;

        $this->evalModel->update($id, [$campo => $path]);

        return $this->response->setJSON([
            'success' => true,
            'path'    => $path,
            'url'     => base_url($path),
        ]);
    }

    /**
     * POST /simulacro/store — Submit final del wizard
     * Body JSON: { id: int }
     */
    public function store()
    {
        $json = $this->request->getJSON(true);
        $id = !empty($json['id']) ? (int)$json['id'] : null;

        if (!$id) {
            return $this->response->setJSON(['success' => false, 'error' => 'ID requerido'])->setStatusCode(400);
        }

        $eval = $this->evalModel->find($id);
        if (!$eval) {
            return $this->response->setJSON(['success' => false, 'error' => 'Registro no encontrado'])->setStatusCode(404);
        }
        if ($eval['estado'] === 'completo') {
            return $this->response->setJSON(['success' => false, 'error' => 'Ya fue enviado'])->setStatusCode(400);
        }

        // Validar campos mínimos
        $errores = [];
        if (empty($eval['id_cliente'])) $errores[] = 'Copropiedad';
        if (empty($eval['fecha'])) $errores[] = 'Fecha';
        if (empty($eval['nombre_brigadista_lider'])) $errores[] = 'Nombre brigadista lider';

        if (!empty($errores)) {
            return $this->response->setJSON([
                'success' => false,
                'error'   => 'Campos obligatorios faltantes: ' . implode(', ', $errores),
            ])->setStatusCode(400);
        }

        // Calcular promedio evaluación cuantitativa
        $criterios = ['alarma_efectiva', 'orden_evacuacion', 'liderazgo_brigadistas', 'organizacion_punto_encuentro', 'participacion_general'];
        $suma = 0;
        $count = 0;
        foreach ($criterios as $c) {
            if (!empty($eval[$c]) && is_numeric($eval[$c])) {
                $suma += (int)$eval[$c];
                $count++;
            }
        }
        $promedio = $count > 0 ? round($suma / $count, 1) : 0;

        // Calcular total conteo
        $conteo = ($eval['hombre'] ?? 0) + ($eval['mujer'] ?? 0) + ($eval['ninos'] ?? 0)
                + ($eval['adultos_mayores'] ?? 0) + ($eval['discapacidad'] ?? 0) + ($eval['mascotas'] ?? 0);

        // Evaluación cualitativa automática
        $cualitativa = $this->getCualitativa($promedio);

        $this->evalModel->update($id, [
            'estado'                   => 'completo',
            'evaluacion_cuantitativa'  => $promedio . '/10',
            'evaluacion_cualitativa'   => !empty($eval['evaluacion_cualitativa']) ? $eval['evaluacion_cualitativa'] : $cualitativa,
            'total'                    => $conteo,
        ]);

        return $this->response->setJSON(['success' => true, 'message' => 'Evaluacion enviada exitosamente']);
    }

    // ===== MÉTODOS PRIVADOS =====

    /**
     * Whitelist de campos por paso del wizard
     */
    private function getCamposPorPaso(): array
    {
        return [
            // Paso 1: Selector de copropiedad
            1 => ['id_cliente', 'fecha'],
            // Paso 2: Info general
            2 => [
                'direccion', 'evento_simulado', 'alcance_simulacro',
                'tipo_evacuacion', 'personal_no_evacua', 'tipo_alarma',
                'puntos_encuentro', 'recurso_humano', 'equipos_emergencia',
            ],
            // Paso 3: Brigadista líder
            3 => [
                'nombre_brigadista_lider', 'email_brigadista_lider',
                'whatsapp_brigadista_lider', 'distintivos_brigadistas',
            ],
            // Paso 4: Cronómetro parte 1 (7 timestamps)
            4 => [
                'hora_inicio', 'alistamiento_recursos', 'asumir_roles',
                'suena_alarma', 'distribucion_roles', 'llegada_punto_encuentro',
                'agrupacion_por_afinidad',
            ],
            // Paso 5: Conteo de evacuados
            5 => ['hombre', 'mujer', 'ninos', 'adultos_mayores', 'discapacidad', 'mascotas', 'total'],
            // Paso 6: Evidencias fotográficas + observaciones
            6 => ['observaciones'],
            // Paso 7: Cronómetro parte 2 (2 timestamps + total)
            7 => ['conteo_personal', 'agradecimiento_y_cierre', 'tiempo_total'],
            // Paso 8: Evaluación cuantitativa
            8 => [
                'alarma_efectiva', 'orden_evacuacion', 'liderazgo_brigadistas',
                'organizacion_punto_encuentro', 'participacion_general',
                'evaluacion_cuantitativa', 'evaluacion_cualitativa',
            ],
            // Paso 9: Resumen (no guarda datos, solo confirma)
            9 => [],
        ];
    }

    /**
     * Etiqueta cualitativa basada en el promedio (misma lógica del Apps Script)
     */
    private function getCualitativa(float $promedio): string
    {
        if ($promedio >= 9.5) return 'Sobresaliente';
        if ($promedio >= 8.0) return 'Muy bueno';
        if ($promedio >= 6.5) return 'Bueno';
        if ($promedio >= 5.0) return 'Aceptable';
        if ($promedio >= 3.5) return 'Insuficiente';
        return 'Deficiente';
    }
}
