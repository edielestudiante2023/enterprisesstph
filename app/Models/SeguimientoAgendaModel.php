<?php

namespace App\Models;

use CodeIgniter\Model;

class SeguimientoAgendaModel extends Model
{
    protected $table         = 'tbl_seguimiento_clientes';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'id_cliente', 'asunto', 'mensaje', 'opciones_fechas',
        'consultor', 'cargo_consultor', 'activo', 'detenido', 'motivo_detencion',
    ];

    public function getActivosConCliente(): array
    {
        return $this->db->table('tbl_seguimiento_clientes s')
            ->select('s.*, c.nombre_cliente, c.correo_cliente, c.nit_cliente')
            ->join('tbl_clientes c', 'c.id_cliente = s.id_cliente')
            ->where('s.activo', 1)
            ->where('s.detenido', 0)
            ->get()->getResultArray();
    }

    public function getAllConCliente(): array
    {
        return $this->db->table('tbl_seguimiento_clientes s')
            ->select('s.*, c.nombre_cliente, c.correo_cliente, c.nit_cliente')
            ->join('tbl_clientes c', 'c.id_cliente = s.id_cliente')
            ->orderBy('s.activo', 'DESC')
            ->orderBy('s.created_at', 'DESC')
            ->get()->getResultArray();
    }
}
