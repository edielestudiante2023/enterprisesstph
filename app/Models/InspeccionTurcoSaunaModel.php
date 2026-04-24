<?php

namespace App\Models;

use CodeIgniter\Model;

class InspeccionTurcoSaunaModel extends Model
{
    protected $table = 'tbl_inspeccion_turco_sauna';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'id_consultor',
        'fecha_inspeccion',
        'aplica_turco', 'aplica_sauna', 'aplica_jacuzzi',
        'aforo_maximo_turco', 'aforo_maximo_sauna', 'aforo_maximo_jacuzzi',
        'horario_operacion',
        'reglamento_visible', 'reglamento_prohibe_menores_solos',
        'aforo_senalizado', 'timbre_emergencia_funcional',
        'punto_hidratacion', 'control_temp_protegido',
        'piso_antideslizante_acceso', 'iluminacion_protegida_humedad',
        'alarma_humo_zona_adyacente', 'cronometro_visible',
        'observaciones_generales', 'recomendaciones_generales',
        'marco_normativo', 'ruta_pdf', 'estado',
        'created_at', 'updated_at',
    ];
    protected $useTimestamps = true;

    public function getByConsultor(int $idConsultor, ?string $estado = null)
    {
        $builder = $this->select('tbl_inspeccion_turco_sauna.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_inspeccion_turco_sauna.id_cliente', 'left')
            ->where('tbl_inspeccion_turco_sauna.id_consultor', $idConsultor)
            ->orderBy('tbl_inspeccion_turco_sauna.updated_at', 'DESC');
        if ($estado) $builder->where('tbl_inspeccion_turco_sauna.estado', $estado);
        return $builder->findAll();
    }

    public function getPendientesByConsultor(int $idConsultor)
    {
        return $this->getByConsultor($idConsultor, 'borrador');
    }

    public function getByCliente(int $idCliente)
    {
        return $this->select('tbl_inspeccion_turco_sauna.*, tbl_consultor.nombre_consultor')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_inspeccion_turco_sauna.id_consultor', 'left')
            ->where('tbl_inspeccion_turco_sauna.id_cliente', $idCliente)
            ->orderBy('tbl_inspeccion_turco_sauna.fecha_inspeccion', 'DESC')
            ->findAll();
    }
}
