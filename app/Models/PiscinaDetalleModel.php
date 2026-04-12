<?php

namespace App\Models;

use CodeIgniter\Model;

class PiscinaDetalleModel extends Model
{
    protected $table = 'tbl_piscina_detalle';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_inspeccion', 'orden',
        'identificador', 'tipo', 'profundidad_minima_m', 'profundidad_maxima_m',
        // Cerramientos
        'cerramiento_perimetral', 'puerta_control_acceso',
        // Alarmas
        'alarma_inmersion', 'alarma_80db_funcional',
        // Drenajes
        'drenaje_antiatrapamiento', 'minimo_dos_drenajes', 'sistema_liberacion_vacio',
        // Señalización
        'senalizacion_profundidad', 'baldosas_cambio_profundidad',
        // Emergencia
        'botiquin_primeros_auxilios', 'flotadores_circulares_min_2', 'baston_con_gancho', 'citofono_24h',
        // Avisos
        'aviso_menores_12_anos', 'aviso_reglamento_visible',
        // Agua
        'agua_limpia_visualmente', 'registro_cloro_diario', 'registro_ph_diario', 'desinfeccion_quimica_vigente',
        // Equipos
        'equipo_bombeo_operativo', 'filtros_operativos', 'dosificador_quimicos',
        // Higiene
        'duchas_previas_obligatorias',
        // Resultado
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
