<?php

namespace App\Models;

use CodeIgniter\Model;

class AscensorDetalleModel extends Model
{
    protected $table = 'tbl_ascensor_detalle';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_inspeccion', 'orden',
        'identificador', 'capacidad_kg', 'capacidad_personas', 'pisos_servidos', 'tipo',
        // Cabina
        'cab_piso_antideslizante', 'cab_iluminacion_normal', 'cab_iluminacion_emergencia',
        'cab_ventilacion', 'cab_pasamanos', 'cab_botonera_operativa', 'cab_display_piso',
        'cab_sensor_sobrecarga', 'cab_placa_capacidad_visible', 'cab_intercomunicador_funcional',
        // Puertas
        'pue_alineacion', 'pue_fotocelula_cortina', 'pue_mecanismo_cierre',
        'pue_enclavamientos', 'pue_nivelacion_piso',
        // Cuarto máquinas
        'cm_maquina_tractora', 'cm_poleas_cables', 'cm_sistema_freno', 'cm_tablero_control',
        'cm_iluminacion_ventilacion', 'cm_orden_aseo', 'cm_extintor_vigente', 'cm_acceso_restringido',
        // Foso
        'foso_amortiguadores', 'foso_limpieza', 'foso_sin_agua_residuos',
        'foso_interruptor_parada', 'foso_escalera_acceso', 'foso_iluminacion',
        // Shaft
        'shaft_integridad_estructural', 'shaft_estado_guias', 'shaft_sin_cableado_ajeno',
        // Eléctricos
        'elec_puesta_tierra', 'elec_limitador_velocidad', 'elec_paracaidas',
        'elec_final_carrera', 'elec_protecciones_termomagneticas',
        // Contrapeso
        'cp_guias_estado', 'cp_sin_obstaculos',
        // Señalización
        'sen_placa_capacidad', 'sen_instrucciones_emergencia',
        'sen_numero_emergencia', 'sen_certificado_visible',
        // Resultado
        'estado_general', 'certificado_onac_vigente',
        'foto', 'observaciones',
    ];
    protected $useTimestamps = false;

    public function getByInspeccion(int $idInspeccion)
    {
        return $this->where('id_inspeccion', $idInspeccion)
            ->orderBy('orden', 'ASC')
            ->findAll();
    }

    public function deleteByInspeccion(int $idInspeccion)
    {
        return $this->where('id_inspeccion', $idInspeccion)->delete();
    }
}
