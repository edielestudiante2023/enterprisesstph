<?php

namespace App\Models;

use CodeIgniter\Model;

class InspeccionBrigadaSimulacrosModel extends Model
{
    protected $table      = 'tbl_inspeccion_brigada_simulacros';
    protected $primaryKey = 'id';

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $allowedFields = [
        'id_cliente',
        'id_consultor',
        'fecha_inspeccion',
        // Estado actual de la brigada
        'existe_brigada',
        'fecha_conformacion',
        'numero_brigadistas',
        'nombre_jefe_brigada',
        'brigada_capacitada',
        'cuenta_dotacion',
        'detalle_dotacion',
        // Capacitaciones
        'capacitacion_primeros_auxilios',
        'capacitacion_extintores',
        'capacitacion_evacuacion',
        'capacitacion_busqueda_rescate',
        'capacitacion_comunicaciones',
        'fecha_ultima_capacitacion',
        'capacitaciones_12m',
        // Simulacros
        'fecha_ultimo_simulacro',
        'tipo_simulacro',
        'participo_simulacro_nacional',
        'cantidad_simulacros_12m',
        // Hallazgos
        'fortalezas',
        'debilidades',
        'recomendaciones',
        'observaciones',
        // Fotos
        'foto_brigada_1',
        'foto_brigada_2',
        'foto_dotacion',
        'foto_acta_simulacro',
        // Control
        'estado',
        'ruta_pdf',
    ];

    public function getByConsultor(int $idConsultor, ?string $estado = null)
    {
        $builder = $this->select('tbl_inspeccion_brigada_simulacros.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_brigada_simulacros.id_cliente', 'left')
            ->where('tbl_inspeccion_brigada_simulacros.id_consultor', $idConsultor)
            ->orderBy('tbl_inspeccion_brigada_simulacros.updated_at', 'DESC');

        if ($estado) {
            $builder->where('tbl_inspeccion_brigada_simulacros.estado', $estado);
        }

        return $builder->findAll();
    }

    public function getByCliente(int $idCliente)
    {
        return $this->select('tbl_inspeccion_brigada_simulacros.*, tbl_consultor.nombre_consultor')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_brigada_simulacros.id_consultor', 'left')
            ->where('tbl_inspeccion_brigada_simulacros.id_cliente', $idCliente)
            ->orderBy('tbl_inspeccion_brigada_simulacros.fecha_inspeccion', 'DESC')
            ->findAll();
    }
}
