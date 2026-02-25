<?php

namespace App\Models;

use CodeIgniter\Model;

class DotacionToderoModel extends Model
{
    protected $table = 'tbl_dotacion_todero';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'id_consultor', 'fecha_inspeccion', 'contratista', 'servicio',
        'nombre_cargo', 'actividades_frecuentes', 'foto_cuerpo_completo',
        'foto_cuarto_almacenamiento', 'estado_tapabocas', 'estado_guantes_nitrilo',
        'estado_mascarilla_polvo', 'estado_guantes_nylon', 'estado_guantes_caucho',
        'estado_gafas', 'estado_uniforme', 'estado_sombrero', 'estado_zapato',
        'estado_casco', 'estado_careta', 'estado_protector_auditivo', 'estado_respirador',
        'estado_guantes_vaqueta', 'estado_botas_dielectricas', 'estado_delantal_pvc',
        'concepto_final', 'observaciones', 'ruta_pdf', 'estado',
    ];
    protected $useTimestamps = true;

    public function getByConsultor(int $idConsultor, ?string $estado = null)
    {
        $builder = $this->select('tbl_dotacion_todero.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_dotacion_todero.id_cliente', 'left')
            ->where('tbl_dotacion_todero.id_consultor', $idConsultor)
            ->orderBy('tbl_dotacion_todero.updated_at', 'DESC');

        if ($estado) {
            $builder->where('tbl_dotacion_todero.estado', $estado);
        }

        return $builder->findAll();
    }

    public function getPendientesByConsultor(int $idConsultor)
    {
        return $this->select('tbl_dotacion_todero.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_dotacion_todero.id_cliente', 'left')
            ->where('tbl_dotacion_todero.id_consultor', $idConsultor)
            ->where('tbl_dotacion_todero.estado', 'borrador')
            ->orderBy('tbl_dotacion_todero.updated_at', 'DESC')
            ->findAll();
    }

    public function getAllPendientes()
    {
        return $this->select('tbl_dotacion_todero.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_dotacion_todero.id_cliente', 'left')
            ->where('tbl_dotacion_todero.estado', 'borrador')
            ->orderBy('tbl_dotacion_todero.updated_at', 'DESC')
            ->findAll();
    }

    public function getByCliente(int $idCliente)
    {
        return $this->select('tbl_dotacion_todero.*, tbl_consultor.nombre_consultor')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_dotacion_todero.id_consultor', 'left')
            ->where('tbl_dotacion_todero.id_cliente', $idCliente)
            ->orderBy('tbl_dotacion_todero.fecha_inspeccion', 'DESC')
            ->findAll();
    }
}
