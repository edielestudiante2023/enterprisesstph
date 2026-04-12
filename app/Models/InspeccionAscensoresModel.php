<?php

namespace App\Models;

use CodeIgniter\Model;

class InspeccionAscensoresModel extends Model
{
    protected $table = 'tbl_inspeccion_ascensores';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'id_consultor',
        'fecha_inspeccion',
        'empresa_mantenimiento', 'nit_empresa_mantenimiento', 'contacto_empresa_mantenimiento',
        'organismo_certificador_onac',
        'fecha_ultimo_certificado_onac', 'fecha_vencimiento_certificado_onac',
        'certificado_visible_al_publico',
        'cronograma_mantenimiento_anual',
        'reportes_tecnicos_disponibles',
        'total_ascensores',
        'recomendaciones_generales',
        'marco_normativo',
        'ruta_pdf', 'estado',
        'created_at', 'updated_at',
    ];
    protected $useTimestamps = true;

    public function getByConsultor(int $idConsultor, ?string $estado = null)
    {
        $builder = $this->select('tbl_inspeccion_ascensores.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_ascensores.id_cliente', 'left')
            ->where('tbl_inspeccion_ascensores.id_consultor', $idConsultor)
            ->orderBy('tbl_inspeccion_ascensores.updated_at', 'DESC');

        if ($estado) {
            $builder->where('tbl_inspeccion_ascensores.estado', $estado);
        }

        return $builder->findAll();
    }

    public function getPendientesByConsultor(int $idConsultor)
    {
        return $this->select('tbl_inspeccion_ascensores.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_ascensores.id_cliente', 'left')
            ->where('tbl_inspeccion_ascensores.id_consultor', $idConsultor)
            ->where('tbl_inspeccion_ascensores.estado', 'borrador')
            ->orderBy('tbl_inspeccion_ascensores.updated_at', 'DESC')
            ->findAll();
    }

    public function getAllPendientes()
    {
        return $this->select('tbl_inspeccion_ascensores.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_ascensores.id_cliente', 'left')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_ascensores.id_consultor', 'left')
            ->where('tbl_inspeccion_ascensores.estado', 'borrador')
            ->orderBy('tbl_inspeccion_ascensores.updated_at', 'DESC')
            ->findAll();
    }

    public function getByCliente(int $idCliente)
    {
        return $this->select('tbl_inspeccion_ascensores.*, tbl_consultor.nombre_consultor')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_ascensores.id_consultor', 'left')
            ->where('tbl_inspeccion_ascensores.id_cliente', $idCliente)
            ->orderBy('tbl_inspeccion_ascensores.fecha_inspeccion', 'DESC')
            ->findAll();
    }
}
