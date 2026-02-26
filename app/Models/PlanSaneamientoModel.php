<?php

namespace App\Models;

use CodeIgniter\Model;

class PlanSaneamientoModel extends Model
{
    protected $table = 'tbl_plan_saneamiento';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'id_consultor', 'fecha_programa', 'nombre_responsable',
        'ruta_pdf', 'estado',
    ];
    protected $useTimestamps = true;

    public function getByConsultor(int $idConsultor, ?string $estado = null)
    {
        $builder = $this->select('tbl_plan_saneamiento.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_plan_saneamiento.id_cliente', 'left')
            ->where('tbl_plan_saneamiento.id_consultor', $idConsultor)
            ->orderBy('tbl_plan_saneamiento.updated_at', 'DESC');

        if ($estado) {
            $builder->where('tbl_plan_saneamiento.estado', $estado);
        }

        return $builder->findAll();
    }

    public function getPendientesByConsultor(int $idConsultor)
    {
        return $this->select('tbl_plan_saneamiento.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_plan_saneamiento.id_cliente', 'left')
            ->where('tbl_plan_saneamiento.id_consultor', $idConsultor)
            ->where('tbl_plan_saneamiento.estado', 'borrador')
            ->orderBy('tbl_plan_saneamiento.updated_at', 'DESC')
            ->findAll();
    }

    public function getAllPendientes()
    {
        return $this->select('tbl_plan_saneamiento.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_plan_saneamiento.id_cliente', 'left')
            ->where('tbl_plan_saneamiento.estado', 'borrador')
            ->orderBy('tbl_plan_saneamiento.updated_at', 'DESC')
            ->findAll();
    }

    public function getByCliente(int $idCliente)
    {
        return $this->select('tbl_plan_saneamiento.*, tbl_consultor.nombre_consultor')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_plan_saneamiento.id_consultor', 'left')
            ->where('tbl_plan_saneamiento.id_cliente', $idCliente)
            ->orderBy('tbl_plan_saneamiento.fecha_programa', 'DESC')
            ->findAll();
    }
}
