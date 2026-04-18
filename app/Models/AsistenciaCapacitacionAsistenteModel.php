<?php

namespace App\Models;

use CodeIgniter\Model;

class AsistenciaCapacitacionAsistenteModel extends Model
{
    protected $table = 'tbl_asistencia_capacitacion_asistente';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id_asistencia', 'nombre', 'cedula', 'cargo', 'firma'];
    protected $useTimestamps = true;

    public function getByAsistencia(int $idAsistencia)
    {
        return $this->where('id_asistencia', $idAsistencia)->orderBy('id', 'ASC')->findAll();
    }
}
