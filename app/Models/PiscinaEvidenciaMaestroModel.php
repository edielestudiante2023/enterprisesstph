<?php

namespace App\Models;

use CodeIgniter\Model;

class PiscinaEvidenciaMaestroModel extends Model
{
    protected $table = 'tbl_piscina_evidencia_maestro';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_inspeccion', 'campo', 'orden',
        'foto_path', 'descripcion',
    ];
    protected $useTimestamps = false;

    /**
     * Devuelve todas las evidencias de una inspección, agrupadas por campo.
     *
     * @return array{string: array}  Mapa campo => [rows ordenadas por orden].
     */
    public function mapaPorCampo(int $idInspeccion): array
    {
        $rows = $this->where('id_inspeccion', $idInspeccion)
            ->orderBy('campo', 'ASC')
            ->orderBy('orden', 'ASC')
            ->findAll();
        $out = [];
        foreach ($rows as $r) {
            $out[$r['campo']][] = $r;
        }
        return $out;
    }

    public function getByCampo(int $idInspeccion, string $campo): array
    {
        return $this->where('id_inspeccion', $idInspeccion)
            ->where('campo', $campo)
            ->orderBy('orden', 'ASC')
            ->findAll();
    }

    public function deleteById(int $id): bool
    {
        return $this->delete($id);
    }
}
