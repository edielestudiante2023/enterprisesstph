<?php

namespace App\Models;

use CodeIgniter\Model;

class InventarioChoqueItemModel extends Model
{
    protected $table = 'tbl_inventario_choque_items';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_inventario', 'categoria', 'item', 'orden', 'marcado', 'updated_at',
    ];
    protected $useTimestamps = false;

    public function getByInventario(int $idInventario): array
    {
        return $this->where('id_inventario', $idInventario)
            ->orderBy('orden', 'ASC')
            ->findAll();
    }

    public function contarMarcados(int $idInventario): int
    {
        return $this->where('id_inventario', $idInventario)
            ->where('marcado', 1)
            ->countAllResults();
    }
}
