<?php

namespace App\Models;

use CodeIgniter\Model;

class ZonaBbqAsadorModel extends Model
{
    protected $table = 'tbl_zona_bbq_asador';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_inspeccion', 'numero',
        'estado_parrilla', 'estado_conexion_gas',
        'fecha_ultima_prueba_fuga',
        'observaciones', 'orden',
    ];
    protected $useTimestamps = false;

    public function getByInspeccion(int $idInspeccion): array
    {
        return $this->where('id_inspeccion', $idInspeccion)
            ->orderBy('orden', 'ASC')
            ->findAll();
    }
}
