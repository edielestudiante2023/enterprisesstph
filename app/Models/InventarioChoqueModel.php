<?php

namespace App\Models;

use CodeIgniter\Model;

class InventarioChoqueModel extends Model
{
    protected $table = 'tbl_inventario_choque';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'id_consultor', 'fecha_captura', 'observaciones',
        'created_at', 'updated_at',
    ];
    protected $useTimestamps = true;
}
