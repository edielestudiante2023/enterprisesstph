<?php

namespace App\Models;

use CodeIgniter\Model;

class ProcedimientoEmergenciaAreaModel extends Model
{
    protected $table = 'tbl_procedimiento_emergencia_area';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'id_consultor',
        'fecha_elaboracion',
        'area', 'nombre_area_descriptivo',
        'responsable_area_nombre', 'responsable_area_cargo', 'responsable_area_contacto',
        'horario_operacion', 'aforo_maximo',
        'telefonos_emergencia', 'recursos_disponibles', 'observaciones_contexto',
        'marco_normativo', 'ruta_pdf', 'estado',
        'created_at', 'updated_at',
    ];
    protected $useTimestamps = true;

    public function getByConsultor(int $idConsultor, ?string $estado = null): array
    {
        $builder = $this->select('tbl_procedimiento_emergencia_area.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_procedimiento_emergencia_area.id_cliente', 'left')
            ->where('tbl_procedimiento_emergencia_area.id_consultor', $idConsultor)
            ->orderBy('tbl_procedimiento_emergencia_area.updated_at', 'DESC');

        if ($estado) {
            $builder->where('tbl_procedimiento_emergencia_area.estado', $estado);
        }

        return $builder->findAll();
    }

    public function getByCliente(int $idCliente): array
    {
        return $this->select('tbl_procedimiento_emergencia_area.*, tbl_consultor.nombre_consultor')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_procedimiento_emergencia_area.id_consultor', 'left')
            ->where('tbl_procedimiento_emergencia_area.id_cliente', $idCliente)
            ->orderBy('tbl_procedimiento_emergencia_area.fecha_elaboracion', 'DESC')
            ->findAll();
    }

    public function getAllPendientes(): array
    {
        return $this->select('tbl_procedimiento_emergencia_area.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_procedimiento_emergencia_area.id_cliente', 'left')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_procedimiento_emergencia_area.id_consultor', 'left')
            ->where('tbl_procedimiento_emergencia_area.estado', 'borrador')
            ->orderBy('tbl_procedimiento_emergencia_area.updated_at', 'DESC')
            ->findAll();
    }
}
