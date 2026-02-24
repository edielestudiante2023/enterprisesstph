<?php

namespace App\Models;

use CodeIgniter\Model;

class InspeccionRecursosSeguridadModel extends Model
{
    protected $table = 'tbl_inspeccion_recursos_seguridad';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'id_consultor', 'fecha_inspeccion',
        'obs_lamparas', 'foto_lamparas',
        'obs_antideslizantes', 'foto_antideslizantes',
        'obs_pasamanos', 'foto_pasamanos',
        'obs_vigilancia', 'foto_vigilancia',
        'obs_iluminacion', 'foto_iluminacion',
        'obs_planes_respuesta',
        'observaciones', 'ruta_pdf', 'estado',
        'created_at', 'updated_at',
    ];
    protected $useTimestamps = true;

    public function getByConsultor(int $idConsultor, ?string $estado = null)
    {
        $builder = $this->select('tbl_inspeccion_recursos_seguridad.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_recursos_seguridad.id_cliente', 'left')
            ->where('tbl_inspeccion_recursos_seguridad.id_consultor', $idConsultor)
            ->orderBy('tbl_inspeccion_recursos_seguridad.updated_at', 'DESC');

        if ($estado) {
            $builder->where('tbl_inspeccion_recursos_seguridad.estado', $estado);
        }

        return $builder->findAll();
    }

    public function getPendientesByConsultor(int $idConsultor)
    {
        return $this->select('tbl_inspeccion_recursos_seguridad.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_recursos_seguridad.id_cliente', 'left')
            ->where('tbl_inspeccion_recursos_seguridad.id_consultor', $idConsultor)
            ->where('tbl_inspeccion_recursos_seguridad.estado', 'borrador')
            ->orderBy('tbl_inspeccion_recursos_seguridad.updated_at', 'DESC')
            ->findAll();
    }

    public function getAllPendientes()
    {
        return $this->select('tbl_inspeccion_recursos_seguridad.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_recursos_seguridad.id_cliente', 'left')
            ->where('tbl_inspeccion_recursos_seguridad.estado', 'borrador')
            ->orderBy('tbl_inspeccion_recursos_seguridad.updated_at', 'DESC')
            ->findAll();
    }

    public function getByCliente(int $idCliente)
    {
        return $this->select('tbl_inspeccion_recursos_seguridad.*, tbl_consultor.nombre_consultor')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_recursos_seguridad.id_consultor', 'left')
            ->where('tbl_inspeccion_recursos_seguridad.id_cliente', $idCliente)
            ->orderBy('tbl_inspeccion_recursos_seguridad.fecha_inspeccion', 'DESC')
            ->findAll();
    }
}
