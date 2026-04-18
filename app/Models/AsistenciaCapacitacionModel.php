<?php

namespace App\Models;

use CodeIgniter\Model;

class AsistenciaCapacitacionModel extends Model
{
    protected $table = 'tbl_asistencia_capacitacion';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'id_consultor', 'fecha_sesion', 'tema', 'lugar', 'objetivo',
        'capacitador', 'tipo_charla', 'material', 'tiempo_horas', 'observaciones',
        'ruta_pdf_asistencia', 'ruta_pdf_responsabilidades', 'estado',
        'evaluacion_habilitada', 'evaluacion_token',
    ];
    protected $useTimestamps = true;

    public function getByConsultor(int $idConsultor, ?string $estado = null)
    {
        $builder = $this->select('tbl_asistencia_capacitacion.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_asistencia_capacitacion.id_cliente', 'left')
            ->where('tbl_asistencia_capacitacion.id_consultor', $idConsultor)
            ->orderBy('tbl_asistencia_capacitacion.updated_at', 'DESC');

        if ($estado) {
            $builder->where('tbl_asistencia_capacitacion.estado', $estado);
        }

        return $builder->findAll();
    }

    public function getPendientesByConsultor(int $idConsultor)
    {
        return $this->select('tbl_asistencia_capacitacion.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_asistencia_capacitacion.id_cliente', 'left')
            ->where('tbl_asistencia_capacitacion.id_consultor', $idConsultor)
            ->where('tbl_asistencia_capacitacion.estado', 'borrador')
            ->orderBy('tbl_asistencia_capacitacion.updated_at', 'DESC')
            ->findAll();
    }

    public function getAllPendientes()
    {
        return $this->select('tbl_asistencia_capacitacion.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_asistencia_capacitacion.id_cliente', 'left')
            ->where('tbl_asistencia_capacitacion.estado', 'borrador')
            ->orderBy('tbl_asistencia_capacitacion.updated_at', 'DESC')
            ->findAll();
    }

    public function getByCliente(int $idCliente)
    {
        return $this->select('tbl_asistencia_capacitacion.*, tbl_consultor.nombre_consultor')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_asistencia_capacitacion.id_consultor', 'left')
            ->where('tbl_asistencia_capacitacion.id_cliente', $idCliente)
            ->orderBy('tbl_asistencia_capacitacion.fecha_sesion', 'DESC')
            ->findAll();
    }
}
