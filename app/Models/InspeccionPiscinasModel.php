<?php

namespace App\Models;

use CodeIgniter\Model;

class InspeccionPiscinasModel extends Model
{
    protected $table = 'tbl_inspeccion_piscinas';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'id_consultor',
        'fecha_inspeccion',
        'empresa_mantenimiento', 'nit_empresa_mantenimiento', 'contacto_empresa_mantenimiento',
        'certificado_municipal_vigente', 'fecha_vencimiento_certificado_mpio',
        'total_piscinas',
        'recomendaciones_generales',
        'marco_normativo',
        'ruta_pdf', 'estado',
        'created_at', 'updated_at',
    ];
    protected $useTimestamps = true;

    public function getByConsultor(int $idConsultor, ?string $estado = null)
    {
        $builder = $this->select('tbl_inspeccion_piscinas.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_piscinas.id_cliente', 'left')
            ->where('tbl_inspeccion_piscinas.id_consultor', $idConsultor)
            ->orderBy('tbl_inspeccion_piscinas.updated_at', 'DESC');

        if ($estado) {
            $builder->where('tbl_inspeccion_piscinas.estado', $estado);
        }

        return $builder->findAll();
    }

    public function getPendientesByConsultor(int $idConsultor)
    {
        return $this->select('tbl_inspeccion_piscinas.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_piscinas.id_cliente', 'left')
            ->where('tbl_inspeccion_piscinas.id_consultor', $idConsultor)
            ->where('tbl_inspeccion_piscinas.estado', 'borrador')
            ->orderBy('tbl_inspeccion_piscinas.updated_at', 'DESC')
            ->findAll();
    }

    public function getAllPendientes()
    {
        return $this->select('tbl_inspeccion_piscinas.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_piscinas.id_cliente', 'left')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_piscinas.id_consultor', 'left')
            ->where('tbl_inspeccion_piscinas.estado', 'borrador')
            ->orderBy('tbl_inspeccion_piscinas.updated_at', 'DESC')
            ->findAll();
    }

    public function getByCliente(int $idCliente)
    {
        return $this->select('tbl_inspeccion_piscinas.*, tbl_consultor.nombre_consultor')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_piscinas.id_consultor', 'left')
            ->where('tbl_inspeccion_piscinas.id_cliente', $idCliente)
            ->orderBy('tbl_inspeccion_piscinas.fecha_inspeccion', 'DESC')
            ->findAll();
    }
}
