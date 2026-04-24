<?php

namespace App\Models;

use CodeIgniter\Model;

class InspeccionZonaBbqModel extends Model
{
    protected $table = 'tbl_inspeccion_zona_bbq';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'id_consultor',
        'fecha_inspeccion',
        'numero_asadores', 'tipo_combustible', 'aforo_maximo', 'horario_operacion',
        'tiene_sistema_reserva',
        'reglamento_visible', 'extintor_cercano_vigente', 'tipo_extintor',
        'distancia_vegetacion_ok', 'distancia_vivienda_ok',
        'prueba_fugas_gas_vigente', 'valvula_corte_accesible',
        'cilindro_glp_exterior_ventilado', 'ventilacion_adecuada',
        'punto_agua_accesible', 'punto_electrico_gfci',
        'superficie_no_combustible',
        'senal_prohibido_menores_solos', 'senal_riesgo_quemadura',
        'mecheros_fuera_alcance', 'recipiente_cenizas_metalico',
        'alarma_humo_adyacente', 'plan_emergencia_documentado',
        'distancia_vegetacion_m', 'distancia_vivienda_m',
        'observaciones_generales', 'recomendaciones_generales',
        'marco_normativo', 'ruta_pdf', 'estado',
        'created_at', 'updated_at',
    ];
    protected $useTimestamps = true;

    public function getByConsultor(int $idConsultor, ?string $estado = null)
    {
        $builder = $this->select('tbl_inspeccion_zona_bbq.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_zona_bbq.id_cliente', 'left')
            ->where('tbl_inspeccion_zona_bbq.id_consultor', $idConsultor)
            ->orderBy('tbl_inspeccion_zona_bbq.updated_at', 'DESC');
        if ($estado) $builder->where('tbl_inspeccion_zona_bbq.estado', $estado);
        return $builder->findAll();
    }

    public function getPendientesByConsultor(int $idConsultor)
    {
        return $this->getByConsultor($idConsultor, 'borrador');
    }

    public function getByCliente(int $idCliente)
    {
        return $this->select('tbl_inspeccion_zona_bbq.*, tbl_consultor.nombre_consultor')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_zona_bbq.id_consultor', 'left')
            ->where('tbl_inspeccion_zona_bbq.id_cliente', $idCliente)
            ->orderBy('tbl_inspeccion_zona_bbq.fecha_inspeccion', 'DESC')
            ->findAll();
    }
}
