<?php

namespace App\Models;

use CodeIgniter\Model;

class EvaluacionSesionModel extends Model
{
    protected $table         = 'tbl_evaluacion_sesiones';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['id_evaluacion', 'id_cliente', 'fecha_sesion', 'codigo'];
    protected $useTimestamps = true;

    /**
     * Obtener o crear sesión para (evaluacion + cliente + fecha).
     * Devuelve la sesión con su código único.
     */
    public function obtenerOCrear(int $idEvaluacion, int $idCliente, string $fecha): array
    {
        $sesion = $this->where('id_evaluacion', $idEvaluacion)
            ->where('id_cliente', $idCliente)
            ->where('fecha_sesion', $fecha)
            ->first();

        if ($sesion) {
            return $sesion;
        }

        // Generar código único EV-YYYY-NNNN
        $anio = date('Y', strtotime($fecha));
        $seq  = $this->like('codigo', "EV-{$anio}-")->countAllResults() + 1;
        $codigo = 'EV-' . $anio . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);

        // Garantizar unicidad
        while ($this->where('codigo', $codigo)->first()) {
            $seq++;
            $codigo = 'EV-' . $anio . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
        }

        $this->insert([
            'id_evaluacion' => $idEvaluacion,
            'id_cliente'    => $idCliente,
            'fecha_sesion'  => $fecha,
            'codigo'        => $codigo,
        ]);

        return $this->find($this->getInsertID());
    }

    /**
     * Sesiones de una evaluación con nombre del cliente y estadísticas
     */
    public function getSesionesByEvaluacion(int $idEvaluacion): array
    {
        return $this->select('tbl_evaluacion_sesiones.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_evaluacion_sesiones.id_cliente', 'left')
            ->where('id_evaluacion', $idEvaluacion)
            ->orderBy('fecha_sesion', 'DESC')
            ->findAll();
    }
}
