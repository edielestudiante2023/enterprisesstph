<?php

namespace App\Models;

use CodeIgniter\Model;

class InspeccionNoAplicaModel extends Model
{
    protected $table = 'tbl_inspeccion_no_aplica';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente',
        'tipo_inspeccion',
        'motivo',
        'marcado_por',
        'fecha_marcado',
    ];
    protected $useTimestamps = false;

    /**
     * Devuelve los tipos marcados como "No Aplica" para un cliente, indexados por slug.
     */
    public function getByCliente(int $idCliente): array
    {
        $rows = $this->where('id_cliente', $idCliente)->findAll();
        $out = [];
        foreach ($rows as $r) {
            $out[$r['tipo_inspeccion']] = $r;
        }
        return $out;
    }

    public function marcar(int $idCliente, string $tipo, ?string $motivo, ?int $idConsultor): bool
    {
        $existing = $this->where('id_cliente', $idCliente)
            ->where('tipo_inspeccion', $tipo)
            ->first();

        if ($existing) {
            return $this->update($existing['id'], [
                'motivo'      => $motivo,
                'marcado_por' => $idConsultor,
            ]);
        }

        return (bool) $this->insert([
            'id_cliente'      => $idCliente,
            'tipo_inspeccion' => $tipo,
            'motivo'          => $motivo,
            'marcado_por'     => $idConsultor,
            'fecha_marcado'   => date('Y-m-d H:i:s'),
        ]);
    }

    public function quitar(int $idCliente, string $tipo): bool
    {
        return $this->where('id_cliente', $idCliente)
            ->where('tipo_inspeccion', $tipo)
            ->delete();
    }
}
