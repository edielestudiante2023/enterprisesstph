<?php

namespace App\Models;

use CodeIgniter\Model;

class EvaluacionTemaModel extends Model
{
    protected $table         = 'tbl_evaluacion_tema';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['nombre', 'descripcion', 'estado'];
    protected $useTimestamps = true;

    /**
     * Devuelve temas activos con el conteo de preguntas de cada uno.
     */
    public function getActivos(): array
    {
        return $this->select('tbl_evaluacion_tema.*, COUNT(tbl_evaluacion_pregunta.id) AS total_preguntas')
            ->join('tbl_evaluacion_pregunta', 'tbl_evaluacion_pregunta.id_tema = tbl_evaluacion_tema.id', 'left')
            ->where('tbl_evaluacion_tema.estado', 'activo')
            ->groupBy('tbl_evaluacion_tema.id')
            ->orderBy('tbl_evaluacion_tema.nombre', 'ASC')
            ->findAll();
    }

    /**
     * Devuelve todos los temas con conteo de preguntas.
     */
    public function getTodosConConteo(): array
    {
        return $this->select('tbl_evaluacion_tema.*, COUNT(tbl_evaluacion_pregunta.id) AS total_preguntas')
            ->join('tbl_evaluacion_pregunta', 'tbl_evaluacion_pregunta.id_tema = tbl_evaluacion_tema.id', 'left')
            ->groupBy('tbl_evaluacion_tema.id')
            ->orderBy('tbl_evaluacion_tema.nombre', 'ASC')
            ->findAll();
    }
}
