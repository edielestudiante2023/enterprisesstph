<?php

namespace App\Models;

use CodeIgniter\Model;

class PlanillaSegSocialModel extends Model
{
    protected $table = 'tbl_planillas_seguridad_social';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'mes_aportes',
        'archivo_pdf',
        'fecha_cargue',
        'cantidad_envios',
        'fecha_envio',
        'estado_envio',
        'notas',
    ];
}
