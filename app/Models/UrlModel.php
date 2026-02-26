<?php

namespace App\Models;

use CodeIgniter\Model;

class UrlModel extends Model
{
    protected $table            = 'tbl_urls';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['tipo', 'nombre', 'url'];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    public function getGroupedByTipo()
    {
        $all = $this->orderBy('tipo', 'ASC')->orderBy('nombre', 'ASC')->findAll();
        $grouped = [];
        foreach ($all as $row) {
            $grouped[$row['tipo']][] = $row;
        }
        return $grouped;
    }

    public function getTipos()
    {
        return $this->select('tipo')->distinct()->orderBy('tipo', 'ASC')->findAll();
    }
}
