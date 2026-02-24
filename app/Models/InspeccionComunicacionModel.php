<?php

namespace App\Models;

use CodeIgniter\Model;

class InspeccionComunicacionModel extends Model
{
    protected $table = 'tbl_inspeccion_comunicaciones';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'id_consultor', 'fecha_inspeccion',
        'cant_telefono_fijo', 'obs_telefono_fijo',
        'cant_telefonia_celular', 'obs_telefonia_celular',
        'cant_radio_onda_corta', 'obs_radio_onda_corta',
        'cant_software_citofonia', 'obs_software_citofonia',
        'cant_megafonia', 'obs_megafonia',
        'cant_cctv_audio', 'obs_cctv_audio',
        'cant_alarma_comunicacion', 'obs_alarma_comunicacion',
        'cant_voip', 'obs_voip',
        'foto_1', 'foto_2',
        'observaciones_finales', 'ruta_pdf', 'estado',
        'created_at', 'updated_at',
    ];
    protected $useTimestamps = true;

    public function getByConsultor(int $idConsultor, ?string $estado = null)
    {
        $builder = $this->select('tbl_inspeccion_comunicaciones.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_comunicaciones.id_cliente', 'left')
            ->where('tbl_inspeccion_comunicaciones.id_consultor', $idConsultor)
            ->orderBy('tbl_inspeccion_comunicaciones.updated_at', 'DESC');

        if ($estado) {
            $builder->where('tbl_inspeccion_comunicaciones.estado', $estado);
        }

        return $builder->findAll();
    }

    public function getPendientesByConsultor(int $idConsultor)
    {
        return $this->select('tbl_inspeccion_comunicaciones.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_comunicaciones.id_cliente', 'left')
            ->where('tbl_inspeccion_comunicaciones.id_consultor', $idConsultor)
            ->where('tbl_inspeccion_comunicaciones.estado', 'borrador')
            ->orderBy('tbl_inspeccion_comunicaciones.updated_at', 'DESC')
            ->findAll();
    }

    public function getAllPendientes()
    {
        return $this->select('tbl_inspeccion_comunicaciones.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_comunicaciones.id_cliente', 'left')
            ->where('tbl_inspeccion_comunicaciones.estado', 'borrador')
            ->orderBy('tbl_inspeccion_comunicaciones.updated_at', 'DESC')
            ->findAll();
    }

    public function getByCliente(int $idCliente)
    {
        return $this->select('tbl_inspeccion_comunicaciones.*, tbl_consultor.nombre_consultor')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_comunicaciones.id_consultor', 'left')
            ->where('tbl_inspeccion_comunicaciones.id_cliente', $idCliente)
            ->orderBy('tbl_inspeccion_comunicaciones.fecha_inspeccion', 'DESC')
            ->findAll();
    }
}
