<?php

namespace App\Models;

use CodeIgniter\Model;

class EvaluacionSimulacroModel extends Model
{
    protected $table = 'tbl_evaluacion_simulacro';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente',
        'fecha', 'direccion', 'evento_simulado', 'alcance_simulacro',
        'tipo_evacuacion', 'personal_no_evacua', 'tipo_alarma',
        'distintivos_brigadistas', 'puntos_encuentro', 'recurso_humano', 'equipos_emergencia',
        'nombre_brigadista_lider', 'email_brigadista_lider', 'whatsapp_brigadista_lider',
        'imagen_1', 'imagen_2',
        'hora_inicio', 'alistamiento_recursos', 'asumir_roles', 'suena_alarma',
        'distribucion_roles', 'llegada_punto_encuentro', 'agrupacion_por_afinidad',
        'conteo_personal', 'agradecimiento_y_cierre', 'tiempo_total',
        'alarma_efectiva', 'orden_evacuacion', 'liderazgo_brigadistas',
        'organizacion_punto_encuentro', 'participacion_general',
        'evaluacion_cuantitativa', 'evaluacion_cualitativa', 'observaciones',
        'hombre', 'mujer', 'ninos', 'adultos_mayores', 'discapacidad', 'mascotas', 'total',
        'estado', 'ruta_pdf',
    ];
    protected $useTimestamps = true;

    /**
     * Evaluaciones por consultor (derivado via tbl_clientes.id_consultor)
     */
    public function getByConsultor(int $idConsultor, ?string $estado = null)
    {
        $builder = $this->select('tbl_evaluacion_simulacro.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_evaluacion_simulacro.id_cliente', 'left')
            ->where('tbl_clientes.id_consultor', $idConsultor)
            ->orderBy('tbl_evaluacion_simulacro.fecha', 'DESC');

        if ($estado) {
            $builder->where('tbl_evaluacion_simulacro.estado', $estado);
        }

        return $builder->findAll();
    }

    /**
     * Borradores por consultor (para dashboard pendientes)
     */
    public function getPendientesByConsultor(int $idConsultor)
    {
        return $this->select('tbl_evaluacion_simulacro.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_evaluacion_simulacro.id_cliente', 'left')
            ->where('tbl_clientes.id_consultor', $idConsultor)
            ->where('tbl_evaluacion_simulacro.estado', 'borrador')
            ->orderBy('tbl_evaluacion_simulacro.updated_at', 'DESC')
            ->findAll();
    }

    /**
     * Evaluaciones por cliente (para portal cliente)
     */
    public function getByCliente(int $idCliente)
    {
        return $this->where('id_cliente', $idCliente)
            ->orderBy('fecha', 'DESC')
            ->findAll();
    }

    /**
     * Evaluaciones completas por cliente
     */
    public function getCompletosByCliente(int $idCliente)
    {
        return $this->where('id_cliente', $idCliente)
            ->where('estado', 'completo')
            ->orderBy('fecha', 'DESC')
            ->findAll();
    }
}
