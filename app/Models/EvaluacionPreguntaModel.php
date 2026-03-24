<?php

namespace App\Models;

use CodeIgniter\Model;

class EvaluacionPreguntaModel extends Model
{
    protected $table         = 'tbl_evaluacion_pregunta';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['id_tema', 'orden', 'texto', 'correcta'];
    protected $useTimestamps = true;

    /**
     * Devuelve las preguntas de un tema con sus opciones, formateadas como el
     * array que esperan form-publico.php y calcularCalificacion():
     *   [['id' => int, 'texto' => str, 'correcta' => 'c', 'opciones' => ['a'=>str, 'b'=>str, ...]], ...]
     */
    public function getConOpcionesByTema(int $idTema): array
    {
        $preguntas = $this->where('id_tema', $idTema)
            ->orderBy('orden', 'ASC')
            ->findAll();

        if (empty($preguntas)) {
            return [];
        }

        $ids     = array_column($preguntas, 'id');
        $db      = \Config\Database::connect();
        $opcRows = $db->table('tbl_evaluacion_opcion')
            ->whereIn('id_pregunta', $ids)
            ->orderBy('letra', 'ASC')
            ->get()->getResultArray();

        // Indexar opciones por id_pregunta
        $opcionesPorPregunta = [];
        foreach ($opcRows as $o) {
            $opcionesPorPregunta[$o['id_pregunta']][$o['letra']] = $o['texto'];
        }

        $resultado = [];
        foreach ($preguntas as $p) {
            $resultado[] = [
                'id'       => (int) $p['id'],
                'texto'    => $p['texto'],
                'correcta' => $p['correcta'],
                'opciones' => $opcionesPorPregunta[$p['id']] ?? [],
            ];
        }
        return $resultado;
    }

    /**
     * Calcula la calificación (0-100) dadas las respuestas del usuario.
     * $respuestas = ['0' => 'c', '1' => 'd', ...]  (índice posicional del array de preguntas)
     */
    public static function calcularCalificacion(array $respuestas, array $preguntas): float
    {
        $total     = count($preguntas);
        $correctas = 0;
        foreach ($preguntas as $i => $pregunta) {
            if (isset($respuestas[$i]) && $respuestas[$i] === $pregunta['correcta']) {
                $correctas++;
            }
        }
        return $total > 0 ? round(($correctas / $total) * 100, 2) : 0;
    }
}
