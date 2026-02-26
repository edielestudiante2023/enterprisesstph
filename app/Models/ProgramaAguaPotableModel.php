<?php

namespace App\Models;

use CodeIgniter\Model;

class ProgramaAguaPotableModel extends Model
{
    protected $table = 'tbl_programa_agua_potable';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'id_consultor', 'fecha_programa', 'nombre_responsable',
        'cantidad_tanques', 'capacidad_individual', 'capacidad_total',
        'ruta_pdf', 'estado',
    ];
    protected $useTimestamps = true;

    public function getByConsultor(int $idConsultor, ?string $estado = null)
    {
        $builder = $this->select('tbl_programa_agua_potable.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_programa_agua_potable.id_cliente', 'left')
            ->where('tbl_programa_agua_potable.id_consultor', $idConsultor)
            ->orderBy('tbl_programa_agua_potable.updated_at', 'DESC');

        if ($estado) {
            $builder->where('tbl_programa_agua_potable.estado', $estado);
        }

        return $builder->findAll();
    }

    public function getPendientesByConsultor(int $idConsultor)
    {
        return $this->select('tbl_programa_agua_potable.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_programa_agua_potable.id_cliente', 'left')
            ->where('tbl_programa_agua_potable.id_consultor', $idConsultor)
            ->where('tbl_programa_agua_potable.estado', 'borrador')
            ->orderBy('tbl_programa_agua_potable.updated_at', 'DESC')
            ->findAll();
    }

    public function getAllPendientes()
    {
        return $this->select('tbl_programa_agua_potable.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_programa_agua_potable.id_cliente', 'left')
            ->where('tbl_programa_agua_potable.estado', 'borrador')
            ->orderBy('tbl_programa_agua_potable.updated_at', 'DESC')
            ->findAll();
    }

    public function getByCliente(int $idCliente)
    {
        return $this->select('tbl_programa_agua_potable.*, tbl_consultor.nombre_consultor')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_programa_agua_potable.id_consultor', 'left')
            ->where('tbl_programa_agua_potable.id_cliente', $idCliente)
            ->orderBy('tbl_programa_agua_potable.fecha_programa', 'DESC')
            ->findAll();
    }
}
