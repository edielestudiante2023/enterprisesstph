<?php 

namespace App\Models;

use CodeIgniter\Model;

class ConsultantModel extends Model
{
    protected $table = 'tbl_consultor';
    protected $primaryKey = 'id_consultor';
    protected $allowedFields = [
        'nombre_consultor', 
        'cedula_consultor', 
        'usuario', 
        'password', 
        'correo_consultor', 
        'telefono_consultor', 
        'numero_licencia', 
        'foto_consultor',   // Asegúrate de que este campo esté incluido
        'firma_consultor',  // Asegúrate de que este campo esté incluido
        'id_cliente'
    ];
}

