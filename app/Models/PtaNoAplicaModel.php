<?php

namespace App\Models;

use CodeIgniter\Model;

class PtaNoAplicaModel extends Model
{
    protected $table = 'tbl_pta_no_aplica';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'id_ptacliente', 'motivo', 'marcado_por', 'fecha_marcado',
    ];
    protected $useTimestamps = false;

    /**
     * Devuelve [id_ptacliente => row] para un cliente.
     */
    public function getByCliente(int $idCliente): array
    {
        $rows = $this->where('id_cliente', $idCliente)->findAll();
        $out = [];
        foreach ($rows as $r) {
            $out[(int) $r['id_ptacliente']] = $r;
        }
        return $out;
    }

    public function marcar(int $idCliente, int $idPta, ?string $motivo, ?int $idConsultor): bool
    {
        $existing = $this->where('id_cliente', $idCliente)
            ->where('id_ptacliente', $idPta)
            ->first();

        if ($existing) {
            return (bool) $this->update($existing['id'], [
                'motivo'      => $motivo,
                'marcado_por' => $idConsultor,
            ]);
        }

        return (bool) $this->insert([
            'id_cliente'    => $idCliente,
            'id_ptacliente' => $idPta,
            'motivo'        => $motivo,
            'marcado_por'   => $idConsultor,
            'fecha_marcado' => date('Y-m-d H:i:s'),
        ]);
    }

    public function quitar(int $idCliente, int $idPta): bool
    {
        return $this->where('id_cliente', $idCliente)
            ->where('id_ptacliente', $idPta)
            ->delete();
    }
}
