<?php

namespace App\Models;

use CodeIgniter\Model;

class EvaluacionInduccionRespuestaModel extends Model
{
    protected $table      = 'tbl_evaluacion_respuestas';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_evaluacion', 'nombre', 'cedula', 'whatsapp', 'empresa_contratante',
        'cargo', 'id_cliente_conjunto', 'acepta_tratamiento', 'respuestas', 'calificacion',
    ];
    protected $useTimestamps = true;

    public function getByEvaluacion(int $idEvaluacion): array
    {
        return $this->where('id_evaluacion', $idEvaluacion)
            ->orderBy('calificacion', 'DESC')
            ->findAll();
    }

    public function getPromedioByEvaluacion(int $idEvaluacion): float
    {
        $result = $this->selectAvg('calificacion', 'promedio')
            ->where('id_evaluacion', $idEvaluacion)
            ->first();
        return (float) ($result['promedio'] ?? 0);
    }
}
