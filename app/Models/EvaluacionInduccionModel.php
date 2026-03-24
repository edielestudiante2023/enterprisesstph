<?php

namespace App\Models;

use CodeIgniter\Model;

class EvaluacionInduccionModel extends Model
{
    protected $table      = 'tbl_evaluacion_induccion';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_asistencia_induccion', 'id_cliente', 'id_tema', 'titulo', 'token', 'estado',
    ];
    protected $useTimestamps = true;

    public function getByAsistencia(int $idAsistencia): ?array
    {
        return $this->where('id_asistencia_induccion', $idAsistencia)->first();
    }
}
