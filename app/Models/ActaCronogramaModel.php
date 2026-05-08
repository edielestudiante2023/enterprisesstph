<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Tabla puente N:M entre actas de capacitación y cronogramas de capacitación.
 * Una sola acta puede dictar N capacitaciones (1 jornada → N PDFs al finalizar).
 */
class ActaCronogramaModel extends Model
{
    protected $table         = 'tbl_acta_cronograma';
    protected $primaryKey    = 'id_acta_cronograma';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'id_acta', 'id_cronograma',
        'objetivo_ia', 'ruta_pdf',
        'promedio_calificaciones', 'numero_evaluados',
        'created_at',
    ];

    /**
     * Vínculos del acta + datos del cronograma.
     */
    public function getByActa(int $idActa): array
    {
        return $this->select('tbl_acta_cronograma.*, tbl_cronog_capacitacion.nombre_capacitacion, tbl_cronog_capacitacion.objetivo_capacitacion, tbl_cronog_capacitacion.fecha_programada')
            ->join('tbl_cronog_capacitacion', 'tbl_cronog_capacitacion.id_cronograma_capacitacion = tbl_acta_cronograma.id_cronograma', 'left')
            ->where('tbl_acta_cronograma.id_acta', $idActa)
            ->orderBy('tbl_acta_cronograma.id_acta_cronograma', 'ASC')
            ->findAll();
    }

    /**
     * Sincroniza la lista de cronogramas vinculados a una acta:
     * inserta los nuevos, elimina los que no están en la lista.
     */
    public function syncForActa(int $idActa, array $idCronogramas): void
    {
        $idCronogramas = array_values(array_unique(array_map('intval', array_filter($idCronogramas))));

        $actuales = $this->where('id_acta', $idActa)->findAll();
        $actualesMap = [];
        foreach ($actuales as $row) {
            $actualesMap[(int)$row['id_cronograma']] = (int)$row['id_acta_cronograma'];
        }

        // Insertar nuevos
        foreach ($idCronogramas as $idCronog) {
            if (!isset($actualesMap[$idCronog])) {
                $this->insert([
                    'id_acta'       => $idActa,
                    'id_cronograma' => $idCronog,
                ]);
            }
        }

        // Eliminar los que ya no están seleccionados
        foreach ($actualesMap as $idCronog => $idVinculo) {
            if (!in_array($idCronog, $idCronogramas, true)) {
                $this->delete($idVinculo);
            }
        }
    }

    /**
     * Devuelve los IDs de cronograma vinculados a una acta.
     */
    public function getIdsCronogramaByActa(int $idActa): array
    {
        $rows = $this->select('id_cronograma')->where('id_acta', $idActa)->findAll();
        return array_map(fn($r) => (int)$r['id_cronograma'], $rows);
    }
}
