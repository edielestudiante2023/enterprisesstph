<?php

namespace App\Models;

use CodeIgniter\Model;

class GabineteDetalleModel extends Model
{
    protected $table = 'tbl_gabinete_detalle';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_inspeccion', 'numero', 'ubicacion',
        'tiene_manguera', 'tiene_hacha', 'tiene_extintor',
        'tiene_valvula', 'tiene_boquilla', 'tiene_llave_spanner',
        'estado', 'senalizacion',
        'foto', 'observaciones',
    ];
    protected $useTimestamps = false;

    public function getByInspeccion(int $idInspeccion)
    {
        return $this->where('id_inspeccion', $idInspeccion)
            ->orderBy('numero', 'ASC')
            ->findAll();
    }

    public function deleteByInspeccion(int $idInspeccion)
    {
        return $this->where('id_inspeccion', $idInspeccion)->delete();
    }
}
