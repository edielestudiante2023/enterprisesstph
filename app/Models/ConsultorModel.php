<?php

namespace App\Models;

use CodeIgniter\Model;

class ConsultorModel extends Model
{
    protected $table = 'tbl_consultor';
    protected $primaryKey = 'id_consultor';
    protected $allowedFields = [
        'nombre_consultor',
        'cedula_consultor',
        'numero_licencia',
        'foto_consultor',
        'firma_consultor',
        'usuario',
        'password',
        'correo_consultor',
        'telefono_consultor',
        'id_cliente',
        'rol'
    ];
    protected $useTimestamps = false;

    /**
     * Obtiene todos los consultores activos
     */
    public function getActiveConsultores()
    {
        return $this->findAll();
    }

    /**
     * Obtiene un consultor por ID con información completa
     */
    public function getConsultorById($idConsultor)
    {
        return $this->find($idConsultor);
    }

    /**
     * Obtiene consultores con licencia SG-SST
     */
    public function getConsultoresWithLicense()
    {
        return $this->where('numero_licencia IS NOT NULL')
                    ->where('numero_licencia !=', '')
                    ->findAll();
    }
}
