<?php

namespace App\Models;

use CodeIgniter\Model;

class ZonaBbqEvidenciaMaestroModel extends Model
{
    protected $table = 'tbl_zona_bbq_evidencia_maestro';
    protected $primaryKey = 'id';
    protected $allowedFields = ['codigo', 'nombre', 'orden', 'activo'];
    protected $useTimestamps = false;

    public function getActivas(): array
    {
        return $this->where('activo', 1)->orderBy('orden', 'ASC')->findAll();
    }
}
