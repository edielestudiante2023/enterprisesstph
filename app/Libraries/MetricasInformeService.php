<?php

namespace App\Libraries;

use App\Models\InformeAvancesModel;
use App\Models\PtaTransicionesModel;

class MetricasInformeService
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Calcula cumplimiento de estándares desde evaluacion_inicial_sst
     * Fórmula: SUM(valor) / SUM(puntaje_cuantitativo) * 100
     */
    public function calcularCumplimientoEstandares(int $idCliente): float
    {
        $result = $this->db->table('evaluacion_inicial_sst')
            ->select('SUM(valor) as total_valor, SUM(puntaje_cuantitativo) as total_posible')
            ->where('id_cliente', $idCliente)
            ->get()
            ->getRowArray();

        if (!$result || floatval($result['total_posible']) == 0) {
            return 0.0;
        }

        return round((floatval($result['total_valor']) / floatval($result['total_posible'])) * 100, 2);
    }

    /**
     * Obtiene puntaje del informe anterior del mismo cliente
     */
    public function getPuntajeAnterior(int $idCliente): ?float
    {
        $model = new InformeAvancesModel();
        $ultimo = $model->getUltimoByCliente($idCliente);

        return $ultimo ? floatval($ultimo['puntaje_actual']) : null;
    }

    /**
     * Calcula fecha_desde: día siguiente al último informe completo, o null si no hay
     */
    public function getFechaDesde(int $idCliente): ?string
    {
        $model = new InformeAvancesModel();
        $ultimo = $model->getUltimoByCliente($idCliente);

        if ($ultimo) {
            $fecha = new \DateTime($ultimo['fecha_hasta']);
            $fecha->modify('+1 day');
            return $fecha->format('Y-m-d');
        }

        return null;
    }

    /**
     * Indicador plan de trabajo: % actividades CERRADA del total
     */
    public function calcularIndicadorPlanTrabajo(int $idCliente): float
    {
        $result = $this->db->table('tbl_pta_cliente')
            ->select("COUNT(*) as total, SUM(CASE WHEN estado_actividad = 'CERRADA' THEN 1 ELSE 0 END) as cerradas")
            ->where('id_cliente', $idCliente)
            ->get()
            ->getRowArray();

        if (!$result || intval($result['total']) == 0) {
            return 0.0;
        }

        return round((intval($result['cerradas']) / intval($result['total'])) * 100, 2);
    }

    /**
     * Indicador capacitación: % ejecutadas vs programadas+ejecutadas
     */
    public function calcularIndicadorCapacitacion(int $idCliente): float
    {
        $result = $this->db->table('tbl_cronog_capacitacion')
            ->select("COUNT(*) as total, SUM(CASE WHEN estado = 'EJECUTADA' THEN 1 ELSE 0 END) as ejecutadas")
            ->where('id_cliente', $idCliente)
            ->get()
            ->getRowArray();

        if (!$result || intval($result['total']) == 0) {
            return 0.0;
        }

        return round((intval($result['ejecutadas']) / intval($result['total'])) * 100, 2);
    }

    /**
     * Lista de pendientes abiertos del cliente
     */
    public function getActividadesAbiertas(int $idCliente): string
    {
        $rows = $this->db->table('tbl_pendientes')
            ->select('tarea_actividad, responsable, fecha_asignacion')
            ->where('id_cliente', $idCliente)
            ->where('estado', 'ABIERTA')
            ->orderBy('fecha_asignacion', 'DESC')
            ->get()
            ->getResultArray();

        if (empty($rows)) {
            return 'No hay actividades abiertas.';
        }

        $lines = [];
        foreach ($rows as $row) {
            $fecha = $row['fecha_asignacion'] ? date('d/m/Y', strtotime($row['fecha_asignacion'])) : 'S/F';
            $lines[] = "- {$row['tarea_actividad']} (Resp: {$row['responsable']}, Desde: {$fecha})";
        }

        return implode("\n", $lines);
    }

    /**
     * Actividades del PTA cerradas en el periodo (desde tbl_pta_transiciones)
     */
    public function getActividadesCerradasPeriodo(int $idCliente, string $desde, string $hasta): array
    {
        $transModel = new PtaTransicionesModel();
        return $transModel->getWithFilters([
            'id_cliente'  => $idCliente,
            'fecha_desde' => $desde,
            'fecha_hasta' => $hasta,
            'estado_nuevo' => 'CERRADA',
        ]);
    }

    /**
     * Formatea actividades cerradas para almacenar como texto
     */
    public function formatActividadesCerradas(array $actividades): string
    {
        if (empty($actividades)) {
            return 'No se cerraron actividades del PTA en este periodo.';
        }

        $lines = [];
        foreach ($actividades as $act) {
            $fecha = date('d/m/Y', strtotime($act['fecha_transicion']));
            $actividad = $act['actividad_plandetrabajo'] ?? 'Sin nombre';
            $numeral = $act['numeral_plandetrabajo'] ?? '';
            $phva = $act['phva_plandetrabajo'] ?? '';
            $resp = $act['responsable_sugerido_plandetrabajo'] ?? 'Sin asignar';
            $lines[] = "- [{$numeral}] {$actividad} | PHVA: {$phva} | Resp: {$resp} | Cerrada: {$fecha}";
        }

        return implode("\n", $lines);
    }

    /**
     * Determina estado de avance según diferencia neta
     */
    public function calcularEstadoAvance(float $diferencia): string
    {
        if ($diferencia > 5) {
            return 'AVANCE SIGNIFICATIVO';
        } elseif ($diferencia >= 1) {
            return 'AVANCE MODERADO';
        } elseif ($diferencia == 0) {
            return 'ESTABLE';
        } else {
            return 'REINICIO DE CICLO PHVA - BAJA PUNTAJE';
        }
    }

    /**
     * Genera enlace al dashboard del cliente
     */
    public function getEnlaceDashboard(int $idCliente): string
    {
        return base_url("consultant/dashboard-estandares?cliente={$idCliente}");
    }

    /**
     * Recopila actividades del periodo para el prompt de IA
     * Junta: actas de visita, inspecciones, capacitaciones ejecutadas, transiciones PTA
     */
    public function recopilarActividadesPeriodo(int $idCliente, string $desde, string $hasta): array
    {
        $actividades = [];

        // Actas de visita en el periodo
        $actas = $this->db->table('tbl_acta_visita')
            ->select('fecha_visita, objetivo_visita')
            ->where('id_cliente', $idCliente)
            ->where('fecha_visita >=', $desde)
            ->where('fecha_visita <=', $hasta)
            ->orderBy('fecha_visita', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($actas as $a) {
            $actividades[] = "Visita ({$a['fecha_visita']}): {$a['objetivo_visita']}";
        }

        // Capacitaciones ejecutadas en el periodo
        $caps = $this->db->table('tbl_cronog_capacitacion')
            ->select('fecha_programada, tema_capacitacion, estado')
            ->where('id_cliente', $idCliente)
            ->where('estado', 'EJECUTADA')
            ->where('fecha_programada >=', $desde)
            ->where('fecha_programada <=', $hasta)
            ->orderBy('fecha_programada', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($caps as $c) {
            $actividades[] = "Capacitación ejecutada ({$c['fecha_programada']}): {$c['tema_capacitacion']}";
        }

        // PTA cerradas en el periodo
        $cerradas = $this->getActividadesCerradasPeriodo($idCliente, $desde, $hasta);
        foreach ($cerradas as $t) {
            $actividades[] = "PTA cerrada ({$t['fecha_transicion']}): {$t['actividad_plandetrabajo']}";
        }

        // Pendientes cerrados en el periodo
        $pendientes = $this->db->table('tbl_pendientes')
            ->select('tarea_actividad, fecha_cierre')
            ->where('id_cliente', $idCliente)
            ->where('estado', 'CERRADA')
            ->where('fecha_cierre >=', $desde)
            ->where('fecha_cierre <=', $hasta)
            ->orderBy('fecha_cierre', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($pendientes as $p) {
            $actividades[] = "Compromiso cerrado ({$p['fecha_cierre']}): {$p['tarea_actividad']}";
        }

        return $actividades;
    }

    /**
     * Calcula todas las métricas de un cliente para el informe
     */
    public function calcularTodas(int $idCliente, string $fechaDesde, string $fechaHasta): array
    {
        $puntajeActual = $this->calcularCumplimientoEstandares($idCliente);
        $puntajeAnterior = $this->getPuntajeAnterior($idCliente);
        $diferencia = $puntajeAnterior !== null ? round($puntajeActual - $puntajeAnterior, 2) : 0.0;
        $estadoAvance = $this->calcularEstadoAvance($diferencia);

        $actividadesCerradas = $this->getActividadesCerradasPeriodo($idCliente, $fechaDesde, $fechaHasta);

        return [
            'puntaje_actual'               => $puntajeActual,
            'puntaje_anterior'             => $puntajeAnterior,
            'diferencia_neta'              => $diferencia,
            'estado_avance'                => $estadoAvance,
            'indicador_plan_trabajo'       => $this->calcularIndicadorPlanTrabajo($idCliente),
            'indicador_capacitacion'       => $this->calcularIndicadorCapacitacion($idCliente),
            'actividades_abiertas'         => $this->getActividadesAbiertas($idCliente),
            'actividades_cerradas_periodo' => $this->formatActividadesCerradas($actividadesCerradas),
            'actividades_cerradas_raw'     => $actividadesCerradas,
            'enlace_dashboard'             => $this->getEnlaceDashboard($idCliente),
            'fecha_desde_sugerida'         => $this->getFechaDesde($idCliente),
        ];
    }
}
