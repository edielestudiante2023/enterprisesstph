<?php

namespace App\Models;

use CodeIgniter\Model;

class InspeccionPiscineroModel extends Model
{
    protected $table = 'tbl_inspeccion_piscinero';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'id_consultor',
        'fecha_inspeccion',
        'nombre_piscinero', 'cedula', 'telefono',
        'vinculacion', 'empresa_contratista', 'nit_empresa_contratista',
        'certificacion_rcp_vigente', 'fecha_vencimiento_rcp', 'foto_certificado_rcp',
        'curso_salvamento_acuatico', 'fecha_vencimiento_salvamento', 'foto_certificado_salvamento',
        'afiliacion_arl_vigente', 'afiliacion_eps_vigente',
        'examenes_medicos_ocupacionales', 'fecha_ultimo_examen_medico',
        'dotacion_epp_entregada',
        'gafas_proteccion_quimica', 'guantes_nitrilo', 'careta_proteccion', 'delantal_impermeable',
        'capacitacion_manejo_quimicos', 'conocimiento_hojas_seguridad', 'conocimiento_plan_emergencia',
        'horario_cubre_operacion_piscina', 'horario_inicio', 'horario_fin',
        'foto_piscinero',
        'observaciones',
        'marco_normativo',
        'ruta_pdf', 'estado',
        'created_at', 'updated_at',
    ];
    protected $useTimestamps = true;

    public function getByConsultor(int $idConsultor, ?string $estado = null)
    {
        $builder = $this->select('tbl_inspeccion_piscinero.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_piscinero.id_cliente', 'left')
            ->where('tbl_inspeccion_piscinero.id_consultor', $idConsultor)
            ->orderBy('tbl_inspeccion_piscinero.updated_at', 'DESC');

        if ($estado) {
            $builder->where('tbl_inspeccion_piscinero.estado', $estado);
        }

        return $builder->findAll();
    }

    public function getPendientesByConsultor(int $idConsultor)
    {
        return $this->select('tbl_inspeccion_piscinero.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_piscinero.id_cliente', 'left')
            ->where('tbl_inspeccion_piscinero.id_consultor', $idConsultor)
            ->where('tbl_inspeccion_piscinero.estado', 'borrador')
            ->orderBy('tbl_inspeccion_piscinero.updated_at', 'DESC')
            ->findAll();
    }

    public function getAllPendientes()
    {
        return $this->select('tbl_inspeccion_piscinero.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_piscinero.id_cliente', 'left')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_piscinero.id_consultor', 'left')
            ->where('tbl_inspeccion_piscinero.estado', 'borrador')
            ->orderBy('tbl_inspeccion_piscinero.updated_at', 'DESC')
            ->findAll();
    }

    public function getByCliente(int $idCliente)
    {
        return $this->select('tbl_inspeccion_piscinero.*, tbl_consultor.nombre_consultor')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_piscinero.id_consultor', 'left')
            ->where('tbl_inspeccion_piscinero.id_cliente', $idCliente)
            ->orderBy('tbl_inspeccion_piscinero.fecha_inspeccion', 'DESC')
            ->findAll();
    }
}
