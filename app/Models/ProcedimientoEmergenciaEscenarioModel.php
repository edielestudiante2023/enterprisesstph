<?php

namespace App\Models;

use CodeIgniter\Model;

class ProcedimientoEmergenciaEscenarioModel extends Model
{
    protected $table = 'tbl_procedimiento_emergencia_escenario';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_procedimiento', 'orden',
        'escenario_codigo', 'escenario_nombre',
        'que_hacer', 'que_no_hacer', 'cuando', 'quien', 'recursos',
        'generado_con_ia', 'modelo_ia',
        'aprobado_por_consultor', 'aprobado_at',
        'observaciones',
    ];
    protected $useTimestamps = true;

    public function getByProcedimiento(int $idProcedimiento): array
    {
        return $this->where('id_procedimiento', $idProcedimiento)
            ->orderBy('orden', 'ASC')
            ->findAll();
    }

    public function deleteByProcedimiento(int $idProcedimiento): bool
    {
        return $this->where('id_procedimiento', $idProcedimiento)->delete();
    }
}
