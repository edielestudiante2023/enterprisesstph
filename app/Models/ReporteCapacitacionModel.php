<?php

namespace App\Models;

use CodeIgniter\Model;

class ReporteCapacitacionModel extends Model
{
    protected $table = 'tbl_reporte_capacitacion';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'id_consultor', 'fecha_capacitacion', 'nombre_capacitacion',
        'objetivo_capacitacion', 'perfil_asistentes', 'nombre_capacitador',
        'horas_duracion', 'numero_asistentes', 'numero_programados',
        'numero_evaluados', 'promedio_calificaciones', 'foto_listado_asistencia',
        'foto_capacitacion', 'foto_evaluacion', 'foto_otros_1', 'foto_otros_2',
        'observaciones', 'ruta_pdf', 'estado',
    ];
    protected $useTimestamps = true;

    public const PERFILES_ASISTENTES = [
        'contratistas'           => 'Contratistas',
        'administrador'          => 'Administrador',
        'consejo_administracion' => 'Consejo de Administracion',
        'residentes'             => 'Residentes',
        'todos'                  => 'Todos',
    ];

    public function getByConsultor(int $idConsultor, ?string $estado = null)
    {
        $builder = $this->select('tbl_reporte_capacitacion.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_reporte_capacitacion.id_cliente', 'left')
            ->where('tbl_reporte_capacitacion.id_consultor', $idConsultor)
            ->orderBy('tbl_reporte_capacitacion.updated_at', 'DESC');

        if ($estado) {
            $builder->where('tbl_reporte_capacitacion.estado', $estado);
        }

        return $builder->findAll();
    }

    public function getPendientesByConsultor(int $idConsultor)
    {
        return $this->select('tbl_reporte_capacitacion.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_reporte_capacitacion.id_cliente', 'left')
            ->where('tbl_reporte_capacitacion.id_consultor', $idConsultor)
            ->where('tbl_reporte_capacitacion.estado', 'borrador')
            ->orderBy('tbl_reporte_capacitacion.updated_at', 'DESC')
            ->findAll();
    }

    public function getAllPendientes()
    {
        return $this->select('tbl_reporte_capacitacion.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_reporte_capacitacion.id_cliente', 'left')
            ->where('tbl_reporte_capacitacion.estado', 'borrador')
            ->orderBy('tbl_reporte_capacitacion.updated_at', 'DESC')
            ->findAll();
    }

    public function getByCliente(int $idCliente)
    {
        return $this->select('tbl_reporte_capacitacion.*, tbl_consultor.nombre_consultor')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_reporte_capacitacion.id_consultor', 'left')
            ->where('tbl_reporte_capacitacion.id_cliente', $idCliente)
            ->orderBy('tbl_reporte_capacitacion.fecha_capacitacion', 'DESC')
            ->findAll();
    }
}
