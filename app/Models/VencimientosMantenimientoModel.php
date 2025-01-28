<?php

namespace App\Models;

use CodeIgniter\Model;

class VencimientosMantenimientoModel extends Model
{
    protected $table = 'tbl_vencimientos_mantenimientos';
    protected $primaryKey = 'id_vencimientos_mmttos';
    protected $allowedFields = [
        'id_mantenimiento',
        'id_cliente',
        'id_consultor',
        'fecha_vencimiento',
        'estado_actividad',
        'fecha_realizacion',
        'observaciones'
    ];

    /**
     * Obtener mantenimientos próximos a vencer en menos de 30 días.
     */
    public function getUpcomingVencimientos()
    {
        $currentDate = date('Y-m-d');  // Fecha actual
        $dateThreshold = date('Y-m-d', strtotime('+30 days'));  // Próximos 30 días
    
        log_message('debug', "📅 Buscando vencimientos desde {$currentDate} hasta {$dateThreshold}, incluyendo vencidos.");
    
        // Buscar vencimientos dentro de los próximos 30 días **o vencimientos atrasados**
        $result = $this->where('estado_actividad', 'sin ejecutar')
                       ->where('fecha_vencimiento <=', $dateThreshold) // Incluye vencidos y próximos
                       ->findAll();
    
        log_message('debug', '📋 Vencimientos encontrados: ' . print_r($result, true));
    
        return $result;
    }
}
