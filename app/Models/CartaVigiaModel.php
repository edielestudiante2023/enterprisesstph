<?php

namespace App\Models;

use CodeIgniter\Model;

class CartaVigiaModel extends Model
{
    protected $table = 'tbl_carta_vigia';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'id_consultor',
        'nombre_vigia', 'documento_vigia', 'email_vigia', 'telefono_vigia',
        'token_firma', 'token_firma_expiracion', 'estado_firma',
        'firma_imagen', 'firma_ip', 'firma_fecha', 'codigo_verificacion',
        'ruta_pdf',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
