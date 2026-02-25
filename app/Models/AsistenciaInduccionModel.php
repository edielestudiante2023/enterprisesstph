<?php

namespace App\Models;

use CodeIgniter\Model;

class AsistenciaInduccionModel extends Model
{
    protected $table = 'tbl_asistencia_induccion';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'id_consultor', 'fecha_sesion', 'tema', 'lugar', 'objetivo',
        'capacitador', 'tipo_charla', 'material', 'tiempo_horas', 'observaciones',
        'ruta_pdf_asistencia', 'ruta_pdf_responsabilidades', 'estado',
    ];
    protected $useTimestamps = true;

    public function getByConsultor(int $idConsultor, ?string $estado = null)
    {
        $builder = $this->select('tbl_asistencia_induccion.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_asistencia_induccion.id_cliente', 'left')
            ->where('tbl_asistencia_induccion.id_consultor', $idConsultor)
            ->orderBy('tbl_asistencia_induccion.updated_at', 'DESC');

        if ($estado) {
            $builder->where('tbl_asistencia_induccion.estado', $estado);
        }

        return $builder->findAll();
    }

    public function getPendientesByConsultor(int $idConsultor)
    {
        return $this->select('tbl_asistencia_induccion.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_asistencia_induccion.id_cliente', 'left')
            ->where('tbl_asistencia_induccion.id_consultor', $idConsultor)
            ->where('tbl_asistencia_induccion.estado', 'borrador')
            ->orderBy('tbl_asistencia_induccion.updated_at', 'DESC')
            ->findAll();
    }

    public function getAllPendientes()
    {
        return $this->select('tbl_asistencia_induccion.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_asistencia_induccion.id_cliente', 'left')
            ->where('tbl_asistencia_induccion.estado', 'borrador')
            ->orderBy('tbl_asistencia_induccion.updated_at', 'DESC')
            ->findAll();
    }

    public function getByCliente(int $idCliente)
    {
        return $this->select('tbl_asistencia_induccion.*, tbl_consultor.nombre_consultor')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_asistencia_induccion.id_consultor', 'left')
            ->where('tbl_asistencia_induccion.id_cliente', $idCliente)
            ->orderBy('tbl_asistencia_induccion.fecha_sesion', 'DESC')
            ->findAll();
    }
}
