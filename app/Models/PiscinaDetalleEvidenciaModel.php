<?php

namespace App\Models;

use CodeIgniter\Model;

class PiscinaDetalleEvidenciaModel extends Model
{
    protected $table = 'tbl_piscina_detalle_evidencia';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_piscina_detalle', 'categoria', 'orden',
        'foto_path', 'descripcion',
    ];
    protected $useTimestamps = false;

    public function getByPiscina(int $idPiscinaDetalle): array
    {
        return $this->where('id_piscina_detalle', $idPiscinaDetalle)
            ->orderBy('categoria', 'ASC')
            ->orderBy('orden', 'ASC')
            ->findAll();
    }

    public function deleteByPiscina(int $idPiscinaDetalle): bool
    {
        return $this->where('id_piscina_detalle', $idPiscinaDetalle)->delete();
    }
}
