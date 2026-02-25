<?php

namespace App\Models;

use CodeIgniter\Model;

class ProgramaLimpiezaModel extends Model
{
    protected $table = 'tbl_programa_limpieza';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'id_consultor', 'fecha_programa', 'nombre_responsable',
        'ruta_pdf', 'estado',
    ];
    protected $useTimestamps = true;

    public function getByConsultor(int $idConsultor, ?string $estado = null)
    {
        $builder = $this->select('tbl_programa_limpieza.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_programa_limpieza.id_cliente', 'left')
            ->where('tbl_programa_limpieza.id_consultor', $idConsultor)
            ->orderBy('tbl_programa_limpieza.updated_at', 'DESC');

        if ($estado) {
            $builder->where('tbl_programa_limpieza.estado', $estado);
        }

        return $builder->findAll();
    }

    public function getPendientesByConsultor(int $idConsultor)
    {
        return $this->select('tbl_programa_limpieza.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_programa_limpieza.id_cliente', 'left')
            ->where('tbl_programa_limpieza.id_consultor', $idConsultor)
            ->where('tbl_programa_limpieza.estado', 'borrador')
            ->orderBy('tbl_programa_limpieza.updated_at', 'DESC')
            ->findAll();
    }

    public function getAllPendientes()
    {
        return $this->select('tbl_programa_limpieza.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_programa_limpieza.id_cliente', 'left')
            ->where('tbl_programa_limpieza.estado', 'borrador')
            ->orderBy('tbl_programa_limpieza.updated_at', 'DESC')
            ->findAll();
    }

    public function getByCliente(int $idCliente)
    {
        return $this->select('tbl_programa_limpieza.*, tbl_consultor.nombre_consultor')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_programa_limpieza.id_consultor', 'left')
            ->where('tbl_programa_limpieza.id_cliente', $idCliente)
            ->orderBy('tbl_programa_limpieza.fecha_programa', 'DESC')
            ->findAll();
    }
}
