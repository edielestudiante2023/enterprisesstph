<?php

namespace App\Models;

use CodeIgniter\Model;

class PlanContingenciaAguaModel extends Model
{
    protected $table = 'tbl_plan_contingencia_agua';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'id_consultor', 'fecha_programa', 'nombre_responsable',
        'empresa_carrotanque', 'capacidad_reserva', 'ruta_pdf', 'estado',
    ];
    protected $useTimestamps = true;

    public function getByConsultor(int $idConsultor, ?string $estado = null)
    {
        $builder = $this->select('tbl_plan_contingencia_agua.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_plan_contingencia_agua.id_cliente', 'left')
            ->where('tbl_plan_contingencia_agua.id_consultor', $idConsultor)
            ->orderBy('tbl_plan_contingencia_agua.updated_at', 'DESC');

        if ($estado) {
            $builder->where('tbl_plan_contingencia_agua.estado', $estado);
        }

        return $builder->findAll();
    }

    public function getAllPendientes()
    {
        return $this->select('tbl_plan_contingencia_agua.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_plan_contingencia_agua.id_cliente', 'left')
            ->where('tbl_plan_contingencia_agua.estado', 'borrador')
            ->orderBy('tbl_plan_contingencia_agua.updated_at', 'DESC')
            ->findAll();
    }
}
