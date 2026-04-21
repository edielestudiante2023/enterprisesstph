<?php

namespace App\Models;

use CodeIgniter\Model;

class PtaInspeccionMatchModel extends Model
{
    protected $table = 'tbl_pta_inspeccion_match';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'id_ptacliente', 'slug_inspeccion',
        'score', 'method', 'reasoning', 'ai_model',
        'created_at', 'updated_at',
    ];
    protected $useTimestamps = false;

    public function getByCliente(int $idCliente): array
    {
        return $this->where('id_cliente', $idCliente)
            ->orderBy('id_ptacliente', 'ASC')
            ->orderBy('score', 'DESC')
            ->findAll();
    }

    public function getByPta(int $idCliente, int $idPta): array
    {
        return $this->where('id_cliente', $idCliente)
            ->where('id_ptacliente', $idPta)
            ->orderBy('score', 'DESC')
            ->findAll();
    }

    public function upsert(array $data): bool
    {
        $existing = $this->where('id_cliente', $data['id_cliente'])
            ->where('id_ptacliente', $data['id_ptacliente'])
            ->where('slug_inspeccion', $data['slug_inspeccion'])
            ->first();

        if ($existing) {
            return (bool) $this->update($existing['id'], $data);
        }

        return (bool) $this->insert($data);
    }

    public function deleteMatch(int $idCliente, int $idPta, string $slug): bool
    {
        return $this->where('id_cliente', $idCliente)
            ->where('id_ptacliente', $idPta)
            ->where('slug_inspeccion', $slug)
            ->delete();
    }

    /**
     * Devuelve slugs mapeados para un cliente (deduplicados).
     */
    public function getSlugsByCliente(int $idCliente): array
    {
        $rows = $this->select('slug_inspeccion')
            ->where('id_cliente', $idCliente)
            ->distinct()
            ->findAll();
        return array_column($rows, 'slug_inspeccion');
    }

    /**
     * Devuelve mapa [id_ptacliente => [slug1, slug2, ...]] para un cliente.
     */
    public function getMapByCliente(int $idCliente): array
    {
        $rows = $this->select('id_ptacliente, slug_inspeccion, score, method')
            ->where('id_cliente', $idCliente)
            ->orderBy('score', 'DESC')
            ->findAll();
        $out = [];
        foreach ($rows as $r) {
            $out[(int) $r['id_ptacliente']][] = $r;
        }
        return $out;
    }
}
