<?php

namespace App\Models;

use CodeIgniter\Model;

class KpiResiduosModel extends Model
{
    protected $table            = 'tbl_kpi_residuos';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'id_cliente', 'id_consultor', 'fecha_inspeccion', 'nombre_responsable',
        'indicador', 'cumplimiento',
        'registro_formato_1', 'registro_formato_2', 'registro_formato_3', 'registro_formato_4',
        'ruta_pdf', 'estado',
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    public function getByConsultor(int $consultorId)
    {
        return $this->select('tbl_kpi_residuos.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_kpi_residuos.id_cliente')
            ->where('tbl_kpi_residuos.id_consultor', $consultorId)
            ->orderBy('tbl_kpi_residuos.fecha_inspeccion', 'DESC')
            ->findAll();
    }

    public function getPendientesByConsultor(int $consultorId)
    {
        return $this->select('tbl_kpi_residuos.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_kpi_residuos.id_cliente')
            ->where('tbl_kpi_residuos.id_consultor', $consultorId)
            ->where('tbl_kpi_residuos.estado', 'borrador')
            ->orderBy('tbl_kpi_residuos.updated_at', 'DESC')
            ->findAll();
    }

    public function getAllPendientes()
    {
        return $this->select('tbl_kpi_residuos.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_kpi_residuos.id_cliente')
            ->where('tbl_kpi_residuos.estado', 'borrador')
            ->orderBy('tbl_kpi_residuos.updated_at', 'DESC')
            ->findAll();
    }

    public function getByCliente(int $clienteId)
    {
        return $this->select('tbl_kpi_residuos.*, tbl_consultor.nombre as nombre_consultor')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_kpi_residuos.id_consultor')
            ->where('tbl_kpi_residuos.id_cliente', $clienteId)
            ->where('tbl_kpi_residuos.estado', 'completo')
            ->orderBy('tbl_kpi_residuos.fecha_inspeccion', 'DESC')
            ->findAll();
    }
}
