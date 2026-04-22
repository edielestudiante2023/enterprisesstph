<?php

namespace App\Models;

use CodeIgniter\Model;

class PiscinaBotiquinItemModel extends Model
{
    protected $table = 'tbl_piscina_botiquin_item';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_piscina_detalle', 'tipo_botiquin',
        'item_codigo', 'item_nombre', 'unidad_medida',
        'cantidad_exigida', 'cantidad_observada',
        'presente', 'observaciones',
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
     * Cuenta ítems presentes/faltantes para resumen en listado y PDF.
     */
    public function resumen(int $idPiscinaDetalle): array
    {
        $items = $this->getByPiscina($idPiscinaDetalle);
        $presentes = 0; $faltantes = 0; $parciales = 0;
        foreach ($items as $i) {
            switch ($i['presente']) {
                case 'SI':       $presentes++; break;
                case 'NO':       $faltantes++; break;
                case 'PARCIAL':  $parciales++; break;
            }
        }
        return [
            'total'      => count($items),
            'presentes'  => $presentes,
            'faltantes'  => $faltantes,
            'parciales'  => $parciales,
        ];
    }
}
