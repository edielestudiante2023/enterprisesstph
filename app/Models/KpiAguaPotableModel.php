<?php

namespace App\Models;

use CodeIgniter\Model;

class KpiAguaPotableModel extends Model
{
    protected $table            = 'tbl_kpi_agua_potable';
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
        return $this->select('tbl_kpi_agua_potable.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_kpi_agua_potable.id_cliente')
            ->where('tbl_kpi_agua_potable.id_consultor', $consultorId)
            ->orderBy('tbl_kpi_agua_potable.fecha_inspeccion', 'DESC')
            ->findAll();
    }

    public function getPendientesByConsultor(int $consultorId)
    {
        return $this->select('tbl_kpi_agua_potable.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_kpi_agua_potable.id_cliente')
            ->where('tbl_kpi_agua_potable.id_consultor', $consultorId)
            ->where('tbl_kpi_agua_potable.estado', 'borrador')
            ->orderBy('tbl_kpi_agua_potable.updated_at', 'DESC')
            ->findAll();
    }

    public function getAllPendientes()
    {
        return $this->select('tbl_kpi_agua_potable.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_kpi_agua_potable.id_cliente')
            ->where('tbl_kpi_agua_potable.estado', 'borrador')
            ->orderBy('tbl_kpi_agua_potable.updated_at', 'DESC')
            ->findAll();
    }

    public function getByCliente(int $clienteId)
    {
        return $this->select('tbl_kpi_agua_potable.*, tbl_consultor.nombre as nombre_consultor')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_kpi_agua_potable.id_consultor')
            ->where('tbl_kpi_agua_potable.id_cliente', $clienteId)
            ->where('tbl_kpi_agua_potable.estado', 'completo')
            ->orderBy('tbl_kpi_agua_potable.fecha_inspeccion', 'DESC')
            ->findAll();
    }
}
