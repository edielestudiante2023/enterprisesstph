<?php

namespace App\Models;

use CodeIgniter\Model;

class MatrizVulnerabilidadModel extends Model
{
    protected $table = 'tbl_matriz_vulnerabilidad';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'id_consultor', 'fecha_inspeccion',
        'c1_plan_evacuacion', 'c2_alarma_evacuacion', 'c3_ruta_evacuacion',
        'c4_visitantes_rutas', 'c5_puntos_reunion', 'c6_puntos_reunion_2',
        'c7_senalizacion_evacuacion', 'c8_rutas_evacuacion', 'c9_ruta_principal',
        'c10_senal_alarma', 'c11_sistema_deteccion', 'c12_iluminacion',
        'c13_iluminacion_emergencia', 'c14_sistema_contra_incendio', 'c15_extintores',
        'c16_divulgacion_plan', 'c17_coordinador_plan', 'c18_brigada_emergencia',
        'c19_simulacros', 'c20_entidades_socorro', 'c21_ocupantes',
        'c22_plano_evacuacion', 'c23_rutas_circulacion', 'c24_puertas_salida',
        'c25_estructura_construccion',
        'observaciones', 'ruta_pdf', 'estado',
        'created_at', 'updated_at',
    ];
    protected $useTimestamps = true;

    public function getByConsultor(int $idConsultor, ?string $estado = null)
    {
        $builder = $this->select('tbl_matriz_vulnerabilidad.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_matriz_vulnerabilidad.id_cliente', 'left')
            ->where('tbl_matriz_vulnerabilidad.id_consultor', $idConsultor)
            ->orderBy('tbl_matriz_vulnerabilidad.updated_at', 'DESC');

        if ($estado) {
            $builder->where('tbl_matriz_vulnerabilidad.estado', $estado);
        }

        return $builder->findAll();
    }

    public function getPendientesByConsultor(int $idConsultor)
    {
        return $this->select('tbl_matriz_vulnerabilidad.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_matriz_vulnerabilidad.id_cliente', 'left')
            ->where('tbl_matriz_vulnerabilidad.id_consultor', $idConsultor)
            ->where('tbl_matriz_vulnerabilidad.estado', 'borrador')
            ->orderBy('tbl_matriz_vulnerabilidad.updated_at', 'DESC')
            ->findAll();
    }

    public function getAllPendientes()
    {
        return $this->select('tbl_matriz_vulnerabilidad.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_matriz_vulnerabilidad.id_cliente', 'left')
            ->where('tbl_matriz_vulnerabilidad.estado', 'borrador')
            ->orderBy('tbl_matriz_vulnerabilidad.updated_at', 'DESC')
            ->findAll();
    }

    public function getByCliente(int $idCliente)
    {
        return $this->select('tbl_matriz_vulnerabilidad.*, tbl_consultor.nombre_consultor')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_matriz_vulnerabilidad.id_consultor', 'left')
            ->where('tbl_matriz_vulnerabilidad.id_cliente', $idCliente)
            ->orderBy('tbl_matriz_vulnerabilidad.fecha_inspeccion', 'DESC')
            ->findAll();
    }
}
