<?php

namespace App\Models;

use CodeIgniter\Model;

class PlanillaSSModel extends Model
{
    protected $table      = 'tbl_planilla_ss_inspeccion';
    protected $primaryKey = 'id';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'id_cliente',
        'periodo',
        'archivo',
        'observaciones',
        'id_consultor',
        'created_at',
    ];
}
