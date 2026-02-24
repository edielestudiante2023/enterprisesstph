<?php

namespace App\Models;

use CodeIgniter\Model;

class InspeccionGabineteModel extends Model
{
    protected $table = 'tbl_inspeccion_gabinetes';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'id_consultor', 'fecha_inspeccion',
        'tiene_gabinetes', 'entregados_constructora', 'cantidad_gabinetes',
        'elementos_gabinete', 'ubicacion_gabinetes', 'estado_senalizacion_gab',
        'foto_gab_1', 'foto_gab_2', 'observaciones_gabinetes',
        'tiene_detectores', 'detectores_entregados', 'cantidad_detectores',
        'ubicacion_detectores', 'foto_det_1', 'foto_det_2', 'observaciones_detectores',
        'ruta_pdf', 'estado',
        'created_at', 'updated_at',
    ];
    protected $useTimestamps = true;

    public function getByConsultor(int $idConsultor, ?string $estado = null)
    {
        $builder = $this->select('tbl_inspeccion_gabinetes.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_gabinetes.id_cliente', 'left')
            ->where('tbl_inspeccion_gabinetes.id_consultor', $idConsultor)
            ->orderBy('tbl_inspeccion_gabinetes.updated_at', 'DESC');

        if ($estado) {
            $builder->where('tbl_inspeccion_gabinetes.estado', $estado);
        }

        return $builder->findAll();
    }

    public function getPendientesByConsultor(int $idConsultor)
    {
        return $this->select('tbl_inspeccion_gabinetes.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_gabinetes.id_cliente', 'left')
            ->where('tbl_inspeccion_gabinetes.id_consultor', $idConsultor)
            ->where('tbl_inspeccion_gabinetes.estado', 'borrador')
            ->orderBy('tbl_inspeccion_gabinetes.updated_at', 'DESC')
            ->findAll();
    }

    public function getAllPendientes()
    {
        return $this->select('tbl_inspeccion_gabinetes.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_gabinetes.id_cliente', 'left')
            ->where('tbl_inspeccion_gabinetes.estado', 'borrador')
            ->orderBy('tbl_inspeccion_gabinetes.updated_at', 'DESC')
            ->findAll();
    }

    public function getByCliente(int $idCliente)
    {
        return $this->select('tbl_inspeccion_gabinetes.*, tbl_consultor.nombre_consultor')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_gabinetes.id_consultor', 'left')
            ->where('tbl_inspeccion_gabinetes.id_cliente', $idCliente)
            ->orderBy('tbl_inspeccion_gabinetes.fecha_inspeccion', 'DESC')
            ->findAll();
    }
}
