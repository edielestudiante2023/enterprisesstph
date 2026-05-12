<?php

namespace App\Models;

use CodeIgniter\Model;

class InspeccionFrecuenciaClienteModel extends Model
{
    protected $table = 'tbl_inspeccion_frecuencia_cliente';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id_cliente', 'slug_inspeccion', 'frecuencia', 'created_at', 'updated_at'];
    protected $useTimestamps = true;

    /**
     * Devuelve un map [slug => frecuencia] para el cliente.
     */
    public function getByCliente(int $idCliente): array
    {
        $rows = $this->where('id_cliente', $idCliente)->findAll();
        $out = [];
        foreach ($rows as $r) {
            $out[$r['slug_inspeccion']] = $r['frecuencia'];
        }
        return $out;
    }

    /**
     * Upsert: actualiza si existe, crea si no.
     */
    public function setFrecuencia(int $idCliente, string $slug, string $frecuencia): bool
    {
        $valid = ['mensual', 'bimensual', 'trimestral', 'semestral', 'anual', 'puntual'];
        if (!in_array($frecuencia, $valid, true)) return false;

        $existing = $this->where('id_cliente', $idCliente)
            ->where('slug_inspeccion', $slug)
            ->first();
        if ($existing) {
            return (bool) $this->update($existing['id'], ['frecuencia' => $frecuencia]);
        }
        return (bool) $this->insert([
            'id_cliente'      => $idCliente,
            'slug_inspeccion' => $slug,
            'frecuencia'      => $frecuencia,
        ]);
    }

    /**
     * Días de un intervalo de frecuencia.
     */
    public static function intervaloDias(string $frecuencia): ?int
    {
        return [
            'mensual'    => 30,
            'bimensual'  => 60,
            'trimestral' => 90,
            'semestral'  => 180,
            'anual'      => 365,
            'puntual'    => null,
        ][$frecuencia] ?? null;
    }
}
