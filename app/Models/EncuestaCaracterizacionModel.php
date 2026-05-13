<?php

namespace App\Models;

use CodeIgniter\Model;

class EncuestaCaracterizacionModel extends Model
{
    protected $table      = 'tbl_encuesta_caracterizacion';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente',
        'titulo',
        'token',
        'estado',
    ];
    protected $useTimestamps = true;

    public function findByToken(string $token): ?array
    {
        if ($token === '') {
            return null;
        }

        return $this->where('token', $token)->first();
    }
}
