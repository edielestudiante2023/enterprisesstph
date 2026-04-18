<?php

namespace App\Models;

use CodeIgniter\Model;

class InspeccionProductosQuimicosModel extends Model
{
    protected $table = 'tbl_inspeccion_productos_quimicos';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;

    protected $allowedFields;

    public function __construct()
    {
        parent::__construct();

        $base = [
            'id_cliente', 'id_consultor',
            'fecha_inspeccion', 'ubicacion',
            'tiene_guadaniadora',
            'porcentaje_cumplimiento', 'nivel_riesgo',
            'observaciones_finales',
            'ruta_pdf', 'estado',
            'created_at', 'updated_at',
        ];
        for ($i = 1; $i <= 17; $i++) {
            $base[] = 'cal_item_' . str_pad($i, 2, '0', STR_PAD_LEFT);
        }
        $this->allowedFields = $base;
    }

    public function getByConsultor(int $idConsultor, ?string $estado = null)
    {
        $builder = $this->select('tbl_inspeccion_productos_quimicos.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_productos_quimicos.id_cliente', 'left')
            ->where('tbl_inspeccion_productos_quimicos.id_consultor', $idConsultor)
            ->orderBy('tbl_inspeccion_productos_quimicos.updated_at', 'DESC');

        if ($estado) {
            $builder->where('tbl_inspeccion_productos_quimicos.estado', $estado);
        }
        return $builder->findAll();
    }

    public function getPendientesByConsultor(int $idConsultor)
    {
        return $this->select('tbl_inspeccion_productos_quimicos.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_productos_quimicos.id_cliente', 'left')
            ->where('tbl_inspeccion_productos_quimicos.id_consultor', $idConsultor)
            ->where('tbl_inspeccion_productos_quimicos.estado', 'borrador')
            ->orderBy('tbl_inspeccion_productos_quimicos.updated_at', 'DESC')
            ->findAll();
    }

    public function getAllPendientes()
    {
        return $this->select('tbl_inspeccion_productos_quimicos.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_productos_quimicos.id_cliente', 'left')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_productos_quimicos.id_consultor', 'left')
            ->where('tbl_inspeccion_productos_quimicos.estado', 'borrador')
            ->orderBy('tbl_inspeccion_productos_quimicos.updated_at', 'DESC')
            ->findAll();
    }

    public function getByCliente(int $idCliente)
    {
        return $this->select('tbl_inspeccion_productos_quimicos.*, tbl_consultor.nombre_consultor')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_productos_quimicos.id_consultor', 'left')
            ->where('tbl_inspeccion_productos_quimicos.id_cliente', $idCliente)
            ->orderBy('tbl_inspeccion_productos_quimicos.fecha_inspeccion', 'DESC')
            ->findAll();
    }
}
