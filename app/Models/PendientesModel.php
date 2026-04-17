<?php

namespace App\Models;

use CodeIgniter\Model;

class PendientesModel extends Model
{
    protected $table = 'tbl_pendientes';
    protected $primaryKey = 'id_pendientes';
    protected $allowedFields = [
        'id_cliente',
        'responsable',
        'tarea_actividad',
        'fecha_asignacion',
        'fecha_cierre',
        'fecha_plazo',
        'fecha_cierre_real',
        'fecha_reclasificacion_auto',
        'estado',
        'conteo_dias',
        'estado_avance',
        'evidencia_para_cerrarla',
        'id_acta_visita',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Definir los callbacks para calcular 'conteo_dias'
    protected $beforeInsert = ['calculateConteoDias'];
    protected $beforeUpdate = ['calculateConteoDias'];

    protected $afterFind = ['formatFechaAsignacion'];

    protected function formatFechaAsignacion(array $data)
    {
        if (isset($data['data']) && is_array($data['data'])) {
            foreach ($data['data'] as &$row) {
                if (isset($row['fecha_asignacion'])) {
                    $row['fecha_asignacion'] = date('Y-m-d', strtotime($row['fecha_asignacion']));
                }
            }
        }
        return $data;
    }


    /**
     * Calcular 'conteo_dias' antes de insertar o actualizar
     */
    protected function calculateConteoDias(array $data)
    {
        $fechaAsignacion   = $data['data']['fecha_asignacion']   ?? null;
        $fechaCierreReal   = $data['data']['fecha_cierre_real']  ?? null;
        $fechaCierreLegacy = $data['data']['fecha_cierre']       ?? null;
        $estado            = $data['data']['estado']             ?? null;

        if (!$this->isValidDateString($fechaAsignacion) || !$estado) {
            return $data;
        }

        $asignacionDate = new \DateTime($fechaAsignacion);
        $currentDate = new \DateTime();
        $estadosCerrados = ['CERRADA', 'CERRADA POR FIN CONTRATO'];

        if ($estado === 'ABIERTA') {
            $conteoDias = $asignacionDate->diff($currentDate)->days;
        } elseif (in_array($estado, $estadosCerrados, true)) {
            $fechaFin = $this->isValidDateString($fechaCierreReal)
                ? $fechaCierreReal
                : ($this->isValidDateString($fechaCierreLegacy) ? $fechaCierreLegacy : null);
            $conteoDias = $fechaFin
                ? $asignacionDate->diff(new \DateTime($fechaFin))->days
                : 0;
        } else {
            $conteoDias = 0;
        }

        $data['data']['conteo_dias'] = $conteoDias;
        return $data;
    }

    private function isValidDateString($s): bool
    {
        if (empty($s)) return false;
        $s = (string) $s;
        if ($s === '0000-00-00' || $s === '0000-00-00 00:00:00') return false;
        $ts = strtotime($s);
        return $ts !== false && $ts > strtotime('2000-01-01');
    }

    /**
     * Obtener pendientes junto con el nombre del cliente
     */
    public function getPendientesWithCliente()
    {
        return $this->select('tbl_pendientes.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_pendientes.id_cliente')
            ->findAll();
    }
}
