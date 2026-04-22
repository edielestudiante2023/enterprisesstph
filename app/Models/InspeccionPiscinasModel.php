<?php

namespace App\Models;

use CodeIgniter\Model;

class InspeccionPiscinasModel extends Model
{
    protected $table = 'tbl_inspeccion_piscinas';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'id_consultor',
        'fecha_inspeccion',
        'empresa_mantenimiento', 'nit_empresa_mantenimiento', 'contacto_empresa_mantenimiento',
        'superficie_total_establecimiento_m2',
        'concepto_sanitario', 'concepto_sanitario_fecha', 'concepto_sanitario_observaciones',
        'dea_presente', 'dea_ubicacion_senalizada', 'dea_personal_capacitado_cantidad',
        'operador_certificado_nombre', 'operador_certificado_entidad', 'operador_certificado_vigencia',
        'documentacion_art15_completa', 'documentacion_art15_observaciones',
        'plan_saneamiento_completo', 'plan_saneamiento_observaciones',
        'manejo_quimicos_conforme',
        'area_residuos_conforme', 'contenedores_codificados_color',
        'tablero_publico_resultados',
        'total_piscinas',
        'recomendaciones_generales',
        'marco_normativo',
        'ruta_pdf', 'estado',
        'created_at', 'updated_at',
    ];
    protected $useTimestamps = true;

    public function getByConsultor(int $idConsultor, ?string $estado = null)
    {
        $builder = $this->select('tbl_inspeccion_piscinas.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_piscinas.id_cliente', 'left')
            ->where('tbl_inspeccion_piscinas.id_consultor', $idConsultor)
            ->orderBy('tbl_inspeccion_piscinas.updated_at', 'DESC');

        if ($estado) {
            $builder->where('tbl_inspeccion_piscinas.estado', $estado);
        }

        return $builder->findAll();
    }

    public function getPendientesByConsultor(int $idConsultor)
    {
        return $this->select('tbl_inspeccion_piscinas.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_piscinas.id_cliente', 'left')
            ->where('tbl_inspeccion_piscinas.id_consultor', $idConsultor)
            ->where('tbl_inspeccion_piscinas.estado', 'borrador')
            ->orderBy('tbl_inspeccion_piscinas.updated_at', 'DESC')
            ->findAll();
    }

    public function getAllPendientes()
    {
        return $this->select('tbl_inspeccion_piscinas.*, tbl_clientes.nombre_cliente, tbl_consultor.nombre_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_piscinas.id_cliente', 'left')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_piscinas.id_consultor', 'left')
            ->where('tbl_inspeccion_piscinas.estado', 'borrador')
            ->orderBy('tbl_inspeccion_piscinas.updated_at', 'DESC')
            ->findAll();
    }

    public function getByCliente(int $idCliente)
    {
        return $this->select('tbl_inspeccion_piscinas.*, tbl_consultor.nombre_consultor')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_piscinas.id_consultor', 'left')
            ->where('tbl_inspeccion_piscinas.id_cliente', $idCliente)
            ->orderBy('tbl_inspeccion_piscinas.fecha_inspeccion', 'DESC')
            ->findAll();
    }
}
