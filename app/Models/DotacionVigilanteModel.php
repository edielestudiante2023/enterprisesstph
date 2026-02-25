<?php

namespace App\Models;

use CodeIgniter\Model;

class DotacionVigilanteModel extends Model
{
    protected $table = 'tbl_dotacion_vigilante';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'id_consultor', 'fecha_inspeccion', 'contratista', 'servicio',
        'nombre_cargo', 'actividades_frecuentes', 'foto_cuerpo_completo',
        'foto_cuarto_almacenamiento', 'estado_uniforme', 'estado_chaqueta',
        'estado_radio', 'estado_baston', 'estado_calzado', 'estado_gorra',
        'estado_carne', 'concepto_final', 'observaciones', 'ruta_pdf', 'estado',
    ];
    protected $useTimestamps = true;

    public function getByConsultor(int $idConsultor, ?string $estado = null)
    {
        $builder = $this->select('tbl_dotacion_vigilante.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_dotacion_vigilante.id_cliente', 'left')
            ->where('tbl_dotacion_vigilante.id_consultor', $idConsultor)
            ->orderBy('tbl_dotacion_vigilante.updated_at', 'DESC');

        if ($estado) {
            $builder->where('tbl_dotacion_vigilante.estado', $estado);
        }

        return $builder->findAll();
    }

    public function getPendientesByConsultor(int $idConsultor)
    {
        return $this->select('tbl_dotacion_vigilante.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_dotacion_vigilante.id_cliente', 'left')
            ->where('tbl_dotacion_vigilante.id_consultor', $idConsultor)
            ->where('tbl_dotacion_vigilante.estado', 'borrador')
            ->orderBy('tbl_dotacion_vigilante.updated_at', 'DESC')
            ->findAll();
    }

    public function getAllPendientes()
    {
        return $this->select('tbl_dotacion_vigilante.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_dotacion_vigilante.id_cliente', 'left')
            ->where('tbl_dotacion_vigilante.estado', 'borrador')
            ->orderBy('tbl_dotacion_vigilante.updated_at', 'DESC')
            ->findAll();
    }

    public function getByCliente(int $idCliente)
    {
        return $this->select('tbl_dotacion_vigilante.*, tbl_consultor.nombre_consultor')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_dotacion_vigilante.id_consultor', 'left')
            ->where('tbl_dotacion_vigilante.id_cliente', $idCliente)
            ->orderBy('tbl_dotacion_vigilante.fecha_inspeccion', 'DESC')
            ->findAll();
    }
}
