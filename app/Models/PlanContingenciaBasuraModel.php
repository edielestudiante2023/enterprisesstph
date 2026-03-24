<?php

namespace App\Models;

use CodeIgniter\Model;

class PlanContingenciaBasuraModel extends Model
{
    protected $table = 'tbl_plan_contingencia_basura';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'id_consultor', 'fecha_programa', 'nombre_responsable',
        'empresa_aseo', 'horario_recoleccion_actual', 'ruta_pdf', 'estado',
    ];
    protected $useTimestamps = true;

    public function getByConsultor(int $idConsultor, ?string $estado = null)
    {
        $builder = $this->select('tbl_plan_contingencia_basura.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_plan_contingencia_basura.id_cliente', 'left')
            ->where('tbl_plan_contingencia_basura.id_consultor', $idConsultor)
            ->orderBy('tbl_plan_contingencia_basura.updated_at', 'DESC');

        if ($estado) {
            $builder->where('tbl_plan_contingencia_basura.estado', $estado);
        }

        return $builder->findAll();
    }

    public function getAllPendientes()
    {
        return $this->select('tbl_plan_contingencia_basura.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_plan_contingencia_basura.id_cliente', 'left')
            ->where('tbl_plan_contingencia_basura.estado', 'borrador')
            ->orderBy('tbl_plan_contingencia_basura.updated_at', 'DESC')
            ->findAll();
    }
}
