<?php

namespace App\Models;

use CodeIgniter\Model;

class AgendamientoModel extends Model
{
    protected $table = 'tbl_agendamientos';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_cliente', 'id_consultor',
        'fecha_visita', 'hora_visita', 'frecuencia',
        'estado', 'confirmacion_calendar',
        'preparacion_cliente', 'observaciones',
        'email_enviado', 'fecha_email_enviado',
        'created_at', 'updated_at',
    ];
    protected $useTimestamps = true;

    /**
     * Agendamientos de un consultor con datos del cliente
     */
    public function getByConsultor(int $idConsultor, ?string $estado = null)
    {
        $builder = $this->select('tbl_agendamientos.*, tbl_clientes.nombre_cliente, tbl_clientes.correo_cliente, tbl_clientes.direccion_cliente, tbl_clientes.ciudad_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_agendamientos.id_cliente', 'left')
            ->where('tbl_agendamientos.id_consultor', $idConsultor)
            ->orderBy('tbl_agendamientos.fecha_visita', 'ASC');

        if ($estado) {
            $builder->where('tbl_agendamientos.estado', $estado);
        }

        return $builder->findAll();
    }

    /**
     * Todos los agendamientos con datos de cliente y consultor (admin)
     */
    public function getAll(?string $estado = null)
    {
        $builder = $this->select('tbl_agendamientos.*, tbl_clientes.nombre_cliente, tbl_clientes.correo_cliente, tbl_consultor.nombre_consultor, tbl_consultor.correo_consultor')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_agendamientos.id_cliente', 'left')
            ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_agendamientos.id_consultor', 'left')
            ->orderBy('tbl_agendamientos.fecha_visita', 'ASC');

        if ($estado) {
            $builder->where('tbl_agendamientos.estado', $estado);
        }

        return $builder->findAll();
    }

    /**
     * Última visita completa de un cliente (desde tbl_acta_visita)
     */
    public function getUltimaVisita(int $idCliente): ?array
    {
        $db = \Config\Database::connect();
        $result = $db->table('tbl_acta_visita')
            ->select('fecha_visita, hora_visita')
            ->where('id_cliente', $idCliente)
            ->where('estado', 'completo')
            ->orderBy('fecha_visita', 'DESC')
            ->orderBy('hora_visita', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        return $result ?: null;
    }

    /**
     * Agendamientos próximos del mes actual (pendiente/confirmado)
     */
    public function getProximosDelMes(int $idConsultor): array
    {
        return $this->select('tbl_agendamientos.*, tbl_clientes.nombre_cliente')
            ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_agendamientos.id_cliente', 'left')
            ->where('tbl_agendamientos.id_consultor', $idConsultor)
            ->whereIn('tbl_agendamientos.estado', ['pendiente', 'confirmado'])
            ->where('MONTH(tbl_agendamientos.fecha_visita)', date('m'))
            ->where('YEAR(tbl_agendamientos.fecha_visita)', date('Y'))
            ->orderBy('tbl_agendamientos.fecha_visita', 'ASC')
            ->findAll();
    }

    /**
     * Sugiere la próxima fecha de visita según frecuencia y última visita
     */
    public function sugerirProximaFecha(int $idCliente, string $frecuencia): string
    {
        $ultimaVisita = $this->getUltimaVisita($idCliente);

        if ($ultimaVisita) {
            $base = $ultimaVisita['fecha_visita'];
        } else {
            $base = date('Y-m-d');
        }

        $meses = match ($frecuencia) {
            'mensual'    => 1,
            'bimensual'  => 2,
            'trimestral' => 3,
            default      => 1,
        };

        return date('Y-m-d', strtotime($base . " +{$meses} months"));
    }

    /**
     * Resumen por consultor para panel admin
     * Retorna: id_consultor, nombre_consultor, total_clientes_activos, agendados_mes, sin_agendar
     */
    public function getResumenPorConsultor(): array
    {
        $db = \Config\Database::connect();
        $mesActual = date('Y-m');

        // Consultores con sus clientes activos
        $consultores = $db->table('tbl_consultor')
            ->select('tbl_consultor.id_consultor, tbl_consultor.nombre_consultor, tbl_consultor.correo_consultor, tbl_consultor.foto_consultor')
            ->get()
            ->getResultArray();

        $resumen = [];
        foreach ($consultores as $c) {
            $totalActivos = $db->table('tbl_clientes')
                ->where('id_consultor', $c['id_consultor'])
                ->where('estado', 'activo')
                ->countAllResults();

            if ($totalActivos === 0) continue;

            // Clientes agendados este mes (con agendamiento pendiente/confirmado)
            $agendados = $db->table('tbl_agendamientos')
                ->where('id_consultor', $c['id_consultor'])
                ->whereIn('estado', ['pendiente', 'confirmado', 'completado'])
                ->where("DATE_FORMAT(fecha_visita, '%Y-%m')", $mesActual)
                ->countAllResults();

            $sinAgendar = $totalActivos - $agendados;
            if ($sinAgendar < 0) $sinAgendar = 0;

            $pct = $totalActivos > 0 ? round(($agendados / $totalActivos) * 100) : 0;

            $resumen[] = [
                'id_consultor'     => $c['id_consultor'],
                'nombre_consultor' => $c['nombre_consultor'],
                'correo_consultor' => $c['correo_consultor'],
                'foto_consultor'   => $c['foto_consultor'],
                'total_activos'    => $totalActivos,
                'agendados'        => $agendados,
                'sin_agendar'      => $sinAgendar,
                'pct_cumplimiento' => $pct,
            ];
        }

        return $resumen;
    }

    /**
     * Detalle de clientes de un consultor con estado de agendamiento
     */
    public function getDetalleConsultor(int $idConsultor): array
    {
        $db = \Config\Database::connect();
        $mesActual = date('Y-m');

        $clientes = $db->table('tbl_clientes')
            ->select('id_cliente, nombre_cliente, correo_cliente, estado')
            ->where('id_consultor', $idConsultor)
            ->where('estado', 'activo')
            ->orderBy('nombre_cliente', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($clientes as &$cli) {
            // Última visita
            $ultima = $this->getUltimaVisita($cli['id_cliente']);
            $cli['ultima_visita'] = $ultima ? $ultima['fecha_visita'] : null;

            // Agendamiento más próximo
            $proximo = $db->table('tbl_agendamientos')
                ->select('id, fecha_visita, hora_visita, frecuencia, estado, email_enviado')
                ->where('id_cliente', $cli['id_cliente'])
                ->where('id_consultor', $idConsultor)
                ->whereIn('estado', ['pendiente', 'confirmado'])
                ->orderBy('fecha_visita', 'ASC')
                ->limit(1)
                ->get()
                ->getRowArray();

            $cli['proximo_agendamiento'] = $proximo;
        }

        return $clientes;
    }
}
