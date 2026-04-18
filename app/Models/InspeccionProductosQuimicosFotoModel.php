<?php

namespace App\Models;

use CodeIgniter\Model;

class InspeccionProductosQuimicosFotoModel extends Model
{
    protected $table = 'tbl_inspeccion_productos_quimicos_foto';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_inspeccion', 'orden', 'foto', 'observacion',
    ];
    protected $useTimestamps = false;

    public function getByInspeccion(int $idInspeccion)
    {
        return $this->where('id_inspeccion', $idInspeccion)
            ->orderBy('orden', 'ASC')
            ->findAll();
    }

    public function deleteByInspeccion(int $idInspeccion)
    {
        return $this->where('id_inspeccion', $idInspeccion)->delete();
    }
}
