<?php

namespace App\Models;

use CodeIgniter\Model;

class PiscinaEnsayoLaboratorioModel extends Model
{
    protected $table = 'tbl_piscina_ensayo_laboratorio';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_inspeccion', 'tipo',
        'fecha_toma', 'laboratorio',
        'laboratorio_acreditado', 'reporta_cumplimiento',
        'conforme_global',
        'archivo_adjunto', 'observaciones',
    ];
    protected $useTimestamps = false;

    public function getByInspeccion(int $idInspeccion): array
    {
        return $this->where('id_inspeccion', $idInspeccion)
            ->orderBy('tipo', 'ASC')
            ->orderBy('fecha_toma', 'DESC')
            ->findAll();
    }

    public function deleteByInspeccion(int $idInspeccion): bool
    {
        return $this->where('id_inspeccion', $idInspeccion)->delete();
    }
}
