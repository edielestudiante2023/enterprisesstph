<?php

namespace App\Models;

use CodeIgniter\Model;

class SeguimientoHistorialModel extends Model
{
    protected $table         = 'tbl_seguimiento_historial';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'id_seguimiento', 'id_cliente', 'fecha_envio', 'estado', 'detalle',
    ];
}
