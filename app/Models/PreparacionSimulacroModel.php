<?php

namespace App\Models;

use CodeIgniter\Model;

class PreparacionSimulacroModel extends Model
{
    protected $table = 'tbl_preparacion_simulacro';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'id_consultor', 'fecha_simulacro', 'ubicacion', 'direccion',
        'evento_simulado', 'alcance_simulacro', 'tipo_evacuacion', 'personal_no_evacua',
        'tipo_alarma', 'distintivos_brigadistas', 'puntos_encuentro', 'recurso_humano',
        'equipos_emergencia', 'nombre_brigadista_lider', 'email_brigadista_lider',
        'whatsapp_brigadista_lider', 'entrega_formato_evaluacion', 'imagen_1', 'imagen_2',
        'hora_inicio', 'alistamiento_recursos', 'asumir_roles', 'suena_alarma',
        'distribucion_roles', 'llegada_punto_encuentro', 'agrupacion_por_afinidad',
        'conteo_personal', 'agradecimiento_cierre', 'observaciones', 'ruta_pdf', 'estado',
    ];
    protected $useTimestamps = true;

    public function getByConsultor(int $idConsultor, ?string $estado = null)
    {
        $builder = $this->select('tbl_preparacion_simulacro.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_preparacion_simulacro.id_cliente', 'left')
            ->where('tbl_preparacion_simulacro.id_consultor', $idConsultor)
            ->orderBy('tbl_preparacion_simulacro.updated_at', 'DESC');

        if ($estado) {
            $builder->where('tbl_preparacion_simulacro.estado', $estado);
        }

        return $builder->findAll();
    }

    public function getPendientesByConsultor(int $idConsultor)
    {
        return $this->select('tbl_preparacion_simulacro.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_preparacion_simulacro.id_cliente', 'left')
            ->where('tbl_preparacion_simulacro.id_consultor', $idConsultor)
            ->where('tbl_preparacion_simulacro.estado', 'borrador')
            ->orderBy('tbl_preparacion_simulacro.updated_at', 'DESC')
            ->findAll();
    }

    public function getAllPendientes()
    {
        return $this->select('tbl_preparacion_simulacro.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_preparacion_simulacro.id_cliente', 'left')
            ->where('tbl_preparacion_simulacro.estado', 'borrador')
            ->orderBy('tbl_preparacion_simulacro.updated_at', 'DESC')
            ->findAll();
    }

    public function getByCliente(int $idCliente)
    {
        return $this->select('tbl_preparacion_simulacro.*, tbl_consultor.nombre_consultor')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_preparacion_simulacro.id_consultor', 'left')
            ->where('tbl_preparacion_simulacro.id_cliente', $idCliente)
            ->orderBy('tbl_preparacion_simulacro.fecha_simulacro', 'DESC')
            ->findAll();
    }
}
