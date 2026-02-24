<?php

namespace App\Models;

use CodeIgniter\Model;

class ProbabilidadPeligrosModel extends Model
{
    protected $table = 'tbl_probabilidad_peligros';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'id_consultor', 'fecha_inspeccion',
        'sismos', 'inundaciones', 'vendavales',
        'atentados', 'asalto_hurto', 'vandalismo',
        'incendios', 'explosiones', 'inhalacion_gases',
        'falla_estructural', 'intoxicacion_alimentos', 'densidad_poblacional',
        'observaciones', 'ruta_pdf', 'estado',
        'created_at', 'updated_at',
    ];
    protected $useTimestamps = true;

    public function getByConsultor(int $idConsultor, ?string $estado = null)
    {
        $builder = $this->select('tbl_probabilidad_peligros.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_probabilidad_peligros.id_cliente', 'left')
            ->where('tbl_probabilidad_peligros.id_consultor', $idConsultor)
            ->orderBy('tbl_probabilidad_peligros.updated_at', 'DESC');

        if ($estado) {
            $builder->where('tbl_probabilidad_peligros.estado', $estado);
        }

        return $builder->findAll();
    }

    public function getPendientesByConsultor(int $idConsultor)
    {
        return $this->select('tbl_probabilidad_peligros.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_probabilidad_peligros.id_cliente', 'left')
            ->where('tbl_probabilidad_peligros.id_consultor', $idConsultor)
            ->where('tbl_probabilidad_peligros.estado', 'borrador')
            ->orderBy('tbl_probabilidad_peligros.updated_at', 'DESC')
            ->findAll();
    }

    public function getAllPendientes()
    {
        return $this->select('tbl_probabilidad_peligros.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_probabilidad_peligros.id_cliente', 'left')
            ->where('tbl_probabilidad_peligros.estado', 'borrador')
            ->orderBy('tbl_probabilidad_peligros.updated_at', 'DESC')
            ->findAll();
    }

    public function getByCliente(int $idCliente)
    {
        return $this->select('tbl_probabilidad_peligros.*, tbl_consultor.nombre_consultor')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_probabilidad_peligros.id_consultor', 'left')
            ->where('tbl_probabilidad_peligros.id_cliente', $idCliente)
            ->orderBy('tbl_probabilidad_peligros.fecha_inspeccion', 'DESC')
            ->findAll();
    }
}
