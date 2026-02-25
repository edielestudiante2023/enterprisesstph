<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditoriaZonaResiduosModel extends Model
{
    protected $table = 'tbl_auditoria_zona_residuos';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'id_consultor', 'fecha_inspeccion',
        'estado_acceso', 'foto_acceso',
        'estado_techo_pared_pisos', 'foto_techo_pared_pisos',
        'estado_ventilacion', 'foto_ventilacion',
        'estado_prevencion_incendios', 'foto_prevencion_incendios',
        'estado_drenajes', 'foto_drenajes',
        'proliferacion_plagas', 'foto_proliferacion_plagas',
        'estado_recipientes', 'foto_recipientes',
        'estado_reciclaje', 'foto_reciclaje',
        'estado_iluminarias', 'foto_iluminarias',
        'estado_senalizacion', 'foto_senalizacion',
        'estado_limpieza_desinfeccion', 'foto_limpieza_desinfeccion',
        'estado_poseta', 'foto_poseta',
        'observaciones', 'ruta_pdf', 'estado',
    ];
    protected $useTimestamps = true;

    public function getByConsultor(int $idConsultor, ?string $estado = null)
    {
        $builder = $this->select('tbl_auditoria_zona_residuos.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_auditoria_zona_residuos.id_cliente', 'left')
            ->where('tbl_auditoria_zona_residuos.id_consultor', $idConsultor)
            ->orderBy('tbl_auditoria_zona_residuos.updated_at', 'DESC');

        if ($estado) {
            $builder->where('tbl_auditoria_zona_residuos.estado', $estado);
        }

        return $builder->findAll();
    }

    public function getPendientesByConsultor(int $idConsultor)
    {
        return $this->select('tbl_auditoria_zona_residuos.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_auditoria_zona_residuos.id_cliente', 'left')
            ->where('tbl_auditoria_zona_residuos.id_consultor', $idConsultor)
            ->where('tbl_auditoria_zona_residuos.estado', 'borrador')
            ->orderBy('tbl_auditoria_zona_residuos.updated_at', 'DESC')
            ->findAll();
    }

    public function getAllPendientes()
    {
        return $this->select('tbl_auditoria_zona_residuos.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_auditoria_zona_residuos.id_cliente', 'left')
            ->where('tbl_auditoria_zona_residuos.estado', 'borrador')
            ->orderBy('tbl_auditoria_zona_residuos.updated_at', 'DESC')
            ->findAll();
    }

    public function getByCliente(int $idCliente)
    {
        return $this->select('tbl_auditoria_zona_residuos.*, tbl_consultor.nombre_consultor')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_auditoria_zona_residuos.id_consultor', 'left')
            ->where('tbl_auditoria_zona_residuos.id_cliente', $idCliente)
            ->orderBy('tbl_auditoria_zona_residuos.fecha_inspeccion', 'DESC')
            ->findAll();
    }
}
