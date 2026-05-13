<?php

namespace App\Models;

use CodeIgniter\Model;

class InspeccionFrecuenciaClienteModel extends Model
{
    protected $table = 'tbl_inspeccion_frecuencia_cliente';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id_cliente', 'slug_inspeccion', 'veces_anio', 'created_at', 'updated_at'];
    protected $useTimestamps = true;

    /**
     * Devuelve un map [slug => veces_anio] para el cliente.
     */
    public function getByCliente(int $idCliente): array
    {
        $rows = $this->where('id_cliente', $idCliente)->findAll();
        $out = [];
        foreach ($rows as $r) {
            $out[$r['slug_inspeccion']] = (int) $r['veces_anio'];
        }
        return $out;
    }

    /**
     * Upsert: actualiza si existe, crea si no.
     * vecesAnio = 0 significa puntual (sin frecuencia fija).
     */
    public function setVecesAnio(int $idCliente, string $slug, int $vecesAnio): bool
    {
        if ($vecesAnio < 0 || $vecesAnio > 365) return false;

        $existing = $this->where('id_cliente', $idCliente)
            ->where('slug_inspeccion', $slug)
            ->first();
        if ($existing) {
            return (bool) $this->update($existing['id'], ['veces_anio' => $vecesAnio]);
        }
        return (bool) $this->insert([
            'id_cliente'      => $idCliente,
            'slug_inspeccion' => $slug,
            'veces_anio'      => $vecesAnio,
        ]);
    }

    /**
     * Aplica la misma frecuencia a varios tipos de inspeccion del cliente.
     */
    public function setManyVecesAnio(int $idCliente, array $slugs, int $vecesAnio): int
    {
        if ($vecesAnio < 0 || $vecesAnio > 365) return 0;

        $updated = 0;
        foreach (array_values(array_unique(array_filter($slugs))) as $slug) {
            if ($this->setVecesAnio($idCliente, (string) $slug, $vecesAnio)) {
                $updated++;
            }
        }

        return $updated;
    }
}
