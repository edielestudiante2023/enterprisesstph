<?php

namespace App\Models;

use CodeIgniter\Model;

class CertificadoServicioModel extends Model
{
    protected $table      = 'tbl_certificado_servicio';
    protected $primaryKey = 'id';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'id_cliente',
        'id_mantenimiento',
        'fecha_servicio',
        'fecha_inspeccion',
        'nueva_fecha_vencimiento',
        'archivo',
        'observaciones',
        'id_consultor',
        'id_vencimiento',
        'created_at',
    ];
}
