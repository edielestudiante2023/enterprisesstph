<?php

namespace App\Models;

use CodeIgniter\Model;

class PiscinaDetalleModel extends Model
{
    protected $table = 'tbl_piscina_detalle';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_inspeccion', 'orden', 'identificador',
        'tipo', 'uso', 'climatizada',
        'superficie_piscina_m2', 'volumen_agua_m3',
        'perfil_profundidad', 'profundidad_max_m', 'profundidad_min_m',
        'aforo_piscina_max', 'aforo_deck_max',
        // Infraestructura
        'cerramiento_perimetral', 'puerta_control_acceso',
        'alarma_inmersion_80db', 'boton_parada_emergencia',
        'drenaje_antiatrapamiento', 'minimo_dos_drenajes', 'sistema_liberacion_vacio',
        'senalizacion_profundidad', 'baldosas_cambio_profundidad',
        'escaleras_acceso_antideslizantes', 'baranda_escaleras',
        'iluminacion_adecuada', 'ventilacion_adecuada',
        // Avisos
        'aviso_menores_12', 'aviso_reglamento', 'aviso_horario',
        'aviso_ducharse_antes', 'aviso_prohibido_zapatos',
        'aviso_telefonos_emergencia', 'aviso_aforo_visible',
        // Emergencia
        'botiquin_tipo', 'foto_botiquin', 'botiquin_observaciones_faltantes',
        'camilla_rescate',
        'flotadores_circulares_min_2', 'baston_con_gancho', 'citofono_24h',
        // Higiene
        'duchas_previas_obligatorias',
        'cubiculos_duchas_mujeres', 'cubiculos_duchas_hombres',
        'baranda_apoyo_duchas', 'lavapies_funcional',
        // Dosificación
        'dosificacion_independiente', 'sistema_seguridad_flujo', 'no_dosificacion_manual_con_publico',
        'equipo_bombeo_operativo', 'filtros_operativos',
        // Libro registro
        'libro_registro_existe', 'libro_ultima_semana_fecha', 'libro_observaciones',
        // Resultado general
        'estado_general',
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
