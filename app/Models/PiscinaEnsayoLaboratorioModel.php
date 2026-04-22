<?php

namespace App\Models;

use CodeIgniter\Model;

class PiscinaEnsayoLaboratorioModel extends Model
{
    protected $table = 'tbl_piscina_ensayo_laboratorio';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_piscina_detalle', 'tipo',
        'fecha_toma', 'fecha_emision_resultados',
        'laboratorio', 'laboratorio_nit', 'numero_informe',
        'norma_citada',
        'heterotrofos_ufc', 'coliformes_termotolerantes_ufc', 'ecoli_ufc',
        'pseudomonas_ufc', 'legionella_ufc',
        'conforme_global',
        'archivo_adjunto', 'observaciones',
    ];
    protected $useTimestamps = false;

    public function getByPiscina(int $idPiscinaDetalle): array
    {
        return $this->where('id_piscina_detalle', $idPiscinaDetalle)
            ->orderBy('tipo', 'ASC')
            ->orderBy('fecha_toma', 'DESC')
            ->findAll();
    }

    public function deleteByPiscina(int $idPiscinaDetalle): bool
    {
        return $this->where('id_piscina_detalle', $idPiscinaDetalle)->delete();
    }
}
