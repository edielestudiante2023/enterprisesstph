<?php

namespace App\Models;

use CodeIgniter\Model;

class PiscinaParametroAguaModel extends Model
{
    protected $table = 'tbl_piscina_parametro_agua';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_piscina_detalle',
        'parametro', 'valor', 'valor_cualitativo',
        'unidad', 'conforme', 'rango_referencia', 'observaciones',
    ];
    protected $useTimestamps = false;

    public function getByPiscina(int $idPiscinaDetalle): array
    {
        return $this->where('id_piscina_detalle', $idPiscinaDetalle)
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    public function deleteByPiscina(int $idPiscinaDetalle): bool
    {
        return $this->where('id_piscina_detalle', $idPiscinaDetalle)->delete();
    }

    /**
     * Devuelve los valores numéricos en un mapa {parametro => valor} para pasar a IrapiCalculator.
     */
    public function mapaDeValores(int $idPiscinaDetalle): array
    {
        $rows = $this->getByPiscina($idPiscinaDetalle);
        $map = [];
        foreach ($rows as $r) {
            if ($r['valor'] !== null && $r['valor'] !== '') {
                $map[$r['parametro']] = (float)$r['valor'];
            }
        }
        return $map;
    }
}
