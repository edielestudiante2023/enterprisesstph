<?php

namespace App\Models;

use CodeIgniter\Model;

class ZonaBbqDetalleEvidenciaModel extends Model
{
    protected $table = 'tbl_zona_bbq_detalle_evidencia';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_inspeccion', 'slot', 'categoria', 'descripcion', 'ruta_foto',
        'created_at', 'updated_at',
    ];
    protected $useTimestamps = true;

    public function getByInspeccion(int $idInspeccion): array
    {
        return $this->where('id_inspeccion', $idInspeccion)
            ->orderBy('slot', 'ASC')
            ->findAll();
    }

    public function mapaPorSlot(int $idInspeccion, int $totalSlots = 6): array
    {
        $filas = $this->getByInspeccion($idInspeccion);
        $mapa = array_fill(1, $totalSlots, null);
        foreach ($filas as $f) {
            $s = (int) $f['slot'];
            if ($s >= 1 && $s <= $totalSlots) $mapa[$s] = $f;
        }
        return $mapa;
    }
}
