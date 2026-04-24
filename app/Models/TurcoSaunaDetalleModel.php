<?php

namespace App\Models;

use CodeIgniter\Model;

class TurcoSaunaDetalleModel extends Model
{
    protected $table = 'tbl_turco_sauna_detalle';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_inspeccion', 'recinto',
        'material_interno', 'fuente_calor', 'temperatura_operacion', 'sistema_ventilacion',
        'piso_antideslizante_interior', 'iluminacion_adecuada', 'aislamiento_electrico_ok',
        'puerta_abre_hacia_fuera', 'puerta_polarizada_visible_exterior', 'ventilacion_rendijas',
        'desague_piso_funcional', 'generador_vapor_mant_vigente',
        'hornillo_aislado_asiento', 'madera_sin_danos_tornillos', 'aviso_prohibido_aceites',
        'tiene_agarraderas_pasamanos', 'gfci_rcd_circuito',
        'profundidad_senalizada', 'cobertura_tapa_fuera_uso', 'cartel_prohibiciones_visibles',
        'profundidad_m', 'temperatura_agua_c',
        'observaciones', 'orden',
    ];
    protected $useTimestamps = false;

    public function getByInspeccion(int $idInspeccion): array
    {
        return $this->where('id_inspeccion', $idInspeccion)
            ->orderBy('FIELD(recinto, "TURCO", "SAUNA", "JACUZZI")', '', false)
            ->findAll();
    }

    /** Devuelve mapa ['TURCO' => row|null, 'SAUNA' => row|null, 'JACUZZI' => row|null]. */
    public function mapaPorRecinto(int $idInspeccion): array
    {
        $mapa = ['TURCO' => null, 'SAUNA' => null, 'JACUZZI' => null];
        foreach ($this->where('id_inspeccion', $idInspeccion)->findAll() as $row) {
            $mapa[$row['recinto']] = $row;
        }
        return $mapa;
    }
}
