<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo para la tabla de auditoría del Plan de Trabajo Anual (PTA)
 * Registra todos los cambios realizados en tbl_pta_cliente
 */
class PtaClienteAuditModel extends Model
{
    protected $table            = 'tbl_pta_cliente_audit';
    protected $primaryKey       = 'id_audit';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'id_ptacliente',
        'id_cliente',
        'accion',
        'campo_modificado',
        'valor_anterior',
        'valor_nuevo',
        'id_usuario',
        'nombre_usuario',
        'email_usuario',
        'rol_usuario',
        'ip_address',
        'user_agent',
        'metodo',
        'descripcion',
        'fecha_accion',
    ];

    protected $useTimestamps = false;

    // Validaciones
    protected $validationRules = [
        'id_ptacliente' => 'required|integer',
        'accion'        => 'required|in_list[INSERT,UPDATE,DELETE,BULK_UPDATE]',
        'id_usuario'    => 'required|integer',
    ];

    /**
     * Obtener auditoría con filtros
     */
    public function getAuditWithFilters($filters = [])
    {
        $builder = $this->builder();

        // Filtro por cliente
        if (!empty($filters['id_cliente'])) {
            $builder->where('id_cliente', $filters['id_cliente']);
        }

        // Filtro por usuario
        if (!empty($filters['id_usuario'])) {
            $builder->where('id_usuario', $filters['id_usuario']);
        }

        // Filtro por acción
        if (!empty($filters['accion'])) {
            $builder->where('accion', $filters['accion']);
        }

        // Filtro por campo modificado
        if (!empty($filters['campo_modificado'])) {
            $builder->where('campo_modificado', $filters['campo_modificado']);
        }

        // Filtro por rango de fechas
        if (!empty($filters['fecha_desde'])) {
            $builder->where('fecha_accion >=', $filters['fecha_desde'] . ' 00:00:00');
        }

        if (!empty($filters['fecha_hasta'])) {
            $builder->where('fecha_accion <=', $filters['fecha_hasta'] . ' 23:59:59');
        }

        // Filtro por ID de registro PTA específico
        if (!empty($filters['id_ptacliente'])) {
            $builder->where('id_ptacliente', $filters['id_ptacliente']);
        }

        // Ordenar por fecha descendente (más recientes primero)
        $builder->orderBy('fecha_accion', 'DESC');

        return $builder->get()->getResultArray();
    }

    /**
     * Obtener historial de un registro PTA específico
     */
    public function getHistoryByPtaId($idPtaCliente)
    {
        return $this->where('id_ptacliente', $idPtaCliente)
                    ->orderBy('fecha_accion', 'DESC')
                    ->findAll();
    }

    /**
     * Obtener actividad de un usuario específico
     */
    public function getActivityByUser($idUsuario, $limit = 100)
    {
        return $this->where('id_usuario', $idUsuario)
                    ->orderBy('fecha_accion', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Obtener estadísticas de auditoría
     */
    public function getStats($filters = [])
    {
        $builder = $this->builder();

        if (!empty($filters['id_cliente'])) {
            $builder->where('id_cliente', $filters['id_cliente']);
        }

        if (!empty($filters['fecha_desde'])) {
            $builder->where('fecha_accion >=', $filters['fecha_desde'] . ' 00:00:00');
        }

        if (!empty($filters['fecha_hasta'])) {
            $builder->where('fecha_accion <=', $filters['fecha_hasta'] . ' 23:59:59');
        }

        // Total de registros
        $total = $builder->countAllResults(false);

        // Por tipo de acción
        $porAccion = $builder->select('accion, COUNT(*) as cantidad')
                            ->groupBy('accion')
                            ->get()
                            ->getResultArray();

        // Reset builder
        $builder->resetQuery();

        // Por usuario (top 10)
        if (!empty($filters['id_cliente'])) {
            $builder->where('id_cliente', $filters['id_cliente']);
        }
        if (!empty($filters['fecha_desde'])) {
            $builder->where('fecha_accion >=', $filters['fecha_desde'] . ' 00:00:00');
        }
        if (!empty($filters['fecha_hasta'])) {
            $builder->where('fecha_accion <=', $filters['fecha_hasta'] . ' 23:59:59');
        }

        $porUsuario = $builder->select('MAX(nombre_usuario) as nombre_usuario, id_usuario, COUNT(*) as cantidad')
                              ->groupBy('id_usuario')
                              ->orderBy('cantidad', 'DESC')
                              ->limit(10)
                              ->get()
                              ->getResultArray();

        // Reset builder
        $builder->resetQuery();

        // Por campo modificado (top 10)
        if (!empty($filters['id_cliente'])) {
            $builder->where('id_cliente', $filters['id_cliente']);
        }
        if (!empty($filters['fecha_desde'])) {
            $builder->where('fecha_accion >=', $filters['fecha_desde'] . ' 00:00:00');
        }
        if (!empty($filters['fecha_hasta'])) {
            $builder->where('fecha_accion <=', $filters['fecha_hasta'] . ' 23:59:59');
        }

        $porCampo = $builder->select('campo_modificado, COUNT(*) as cantidad')
                            ->where('campo_modificado IS NOT NULL')
                            ->groupBy('campo_modificado')
                            ->orderBy('cantidad', 'DESC')
                            ->limit(10)
                            ->get()
                            ->getResultArray();

        return [
            'total'       => $total,
            'por_accion'  => $porAccion,
            'por_usuario' => $porUsuario,
            'por_campo'   => $porCampo,
        ];
    }

    /**
     * Obtener cambios recientes (últimas 24 horas)
     */
    public function getRecentChanges($limit = 50)
    {
        $yesterday = date('Y-m-d H:i:s', strtotime('-24 hours'));

        return $this->where('fecha_accion >=', $yesterday)
                    ->orderBy('fecha_accion', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Exportar auditoría a formato para Excel/CSV
     */
    public function getForExport($filters = [])
    {
        $data = $this->getAuditWithFilters($filters);

        // Mapear nombres de campos para el export
        $camposLegibles = [
            'estado_actividad'                  => 'Estado de Actividad',
            'porcentaje_avance'                 => 'Porcentaje de Avance',
            'fecha_propuesta'                   => 'Fecha Propuesta',
            'fecha_cierre'                      => 'Fecha de Cierre',
            'observaciones'                     => 'Observaciones',
            'phva_plandetrabajo'                => 'PHVA',
            'numeral_plandetrabajo'             => 'Numeral del Plan',
            'actividad_plandetrabajo'           => 'Actividad',
            'responsable_sugerido_plandetrabajo'=> 'Responsable Sugerido',
        ];

        $accionesLegibles = [
            'INSERT'      => 'Creación',
            'UPDATE'      => 'Modificación',
            'DELETE'      => 'Eliminación',
            'BULK_UPDATE' => 'Actualización Masiva',
        ];

        foreach ($data as &$row) {
            // Traducir campo modificado
            if (isset($row['campo_modificado']) && isset($camposLegibles[$row['campo_modificado']])) {
                $row['campo_modificado_legible'] = $camposLegibles[$row['campo_modificado']];
            } else {
                $row['campo_modificado_legible'] = $row['campo_modificado'] ?? '-';
            }

            // Traducir acción
            $row['accion_legible'] = $accionesLegibles[$row['accion']] ?? $row['accion'];
        }

        return $data;
    }
}
