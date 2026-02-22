<?php

namespace App\Models;

use CodeIgniter\Model;

class SessionModel extends Model
{
    protected $table = 'tbl_sesiones_usuario';

    public function __construct()
    {
        parent::__construct();
        // Asegurar zona horaria de Colombia
        date_default_timezone_set('America/Bogota');
    }
    protected $primaryKey = 'id_sesion';
    protected $allowedFields = [
        'id_usuario',
        'inicio_sesion',
        'fin_sesion',
        'duracion_segundos',
        'ip_address',
        'user_agent',
        'estado'
    ];

    /**
     * Iniciar una nueva sesión para el usuario
     */
    public function iniciarSesion(int $idUsuario, ?string $ip = null, ?string $userAgent = null): int
    {
        // Cerrar sesiones activas anteriores del mismo usuario
        $this->cerrarSesionesActivas($idUsuario);

        $data = [
            'id_usuario' => $idUsuario,
            'inicio_sesion' => date('Y-m-d H:i:s'),
            'ip_address' => $ip,
            'user_agent' => $userAgent ? substr($userAgent, 0, 500) : null,
            'estado' => 'activa'
        ];

        $this->insert($data);
        return $this->getInsertID();
    }

    /**
     * Cerrar sesión activa del usuario
     * Aplica timeout máximo según el rol del usuario
     */
    public function cerrarSesion(int $idSesion): bool
    {
        $sesion = $this->find($idSesion);

        if (!$sesion || $sesion['estado'] !== 'activa') {
            return false;
        }

        // Obtener el rol del usuario para aplicar el timeout correcto
        $builder = $this->db->table('tbl_usuarios');
        $usuario = $builder->select('tipo_usuario')->where('id_usuario', $sesion['id_usuario'])->get()->getRowArray();
        $tipoUsuario = $usuario['tipo_usuario'] ?? 'client';
        $timeoutMaximo = self::TIMEOUT_BY_ROLE[$tipoUsuario] ?? 600;

        $inicio = strtotime($sesion['inicio_sesion']);
        $ahora = time();
        $duracionReal = $ahora - $inicio;

        // Si la duración excede el timeout, marcar como expirada con duración = timeout
        if ($duracionReal > $timeoutMaximo) {
            $finTimestamp = $inicio + $timeoutMaximo;
            return $this->update($idSesion, [
                'fin_sesion' => date('Y-m-d H:i:s', $finTimestamp),
                'duracion_segundos' => $timeoutMaximo,
                'estado' => 'expirada'
            ]);
        }

        // Duración normal
        return $this->update($idSesion, [
            'fin_sesion' => date('Y-m-d H:i:s'),
            'duracion_segundos' => $duracionReal,
            'estado' => 'cerrada'
        ]);
    }

    /**
     * Cerrar sesiones activas de un usuario (cuando inicia nueva sesión)
     * Aplica timeout máximo según el rol del usuario
     */
    public function cerrarSesionesActivas(int $idUsuario): void
    {
        // Obtener el rol del usuario para aplicar el timeout correcto
        $builder = $this->db->table('tbl_usuarios');
        $usuario = $builder->select('tipo_usuario')->where('id_usuario', $idUsuario)->get()->getRowArray();
        $tipoUsuario = $usuario['tipo_usuario'] ?? 'client';
        $timeoutMaximo = self::TIMEOUT_BY_ROLE[$tipoUsuario] ?? 600;

        $sesionesActivas = $this->where('id_usuario', $idUsuario)
                                ->where('estado', 'activa')
                                ->findAll();

        foreach ($sesionesActivas as $sesion) {
            $inicio = strtotime($sesion['inicio_sesion']);
            $ahora = time();
            $duracionReal = $ahora - $inicio;

            // Si la duración excede el timeout, marcar como expirada con duración = timeout
            if ($duracionReal > $timeoutMaximo) {
                $finTimestamp = $inicio + $timeoutMaximo;
                $this->update($sesion['id_sesion'], [
                    'fin_sesion' => date('Y-m-d H:i:s', $finTimestamp),
                    'duracion_segundos' => $timeoutMaximo,
                    'estado' => 'expirada'
                ]);
            } else {
                // Duración normal, cerrar con el tiempo real
                $this->update($sesion['id_sesion'], [
                    'fin_sesion' => date('Y-m-d H:i:s'),
                    'duracion_segundos' => $duracionReal,
                    'estado' => 'cerrada'
                ]);
            }
        }
    }

    /**
     * Obtener sesión activa de un usuario
     */
    public function getSesionActiva(int $idUsuario): ?array
    {
        return $this->where('id_usuario', $idUsuario)
                    ->where('estado', 'activa')
                    ->first();
    }

    /**
     * Obtener resumen de consumo de todos los usuarios
     */
    public function getResumenConsumo(?string $fechaInicio = null, ?string $fechaFin = null): array
    {
        $builder = $this->db->table('tbl_usuarios u');
        $builder->select('
            u.id_usuario,
            u.nombre_completo,
            u.email,
            u.tipo_usuario,
            COUNT(s.id_sesion) as total_sesiones,
            COALESCE(SUM(s.duracion_segundos), 0) as tiempo_total_segundos,
            SEC_TO_TIME(COALESCE(SUM(s.duracion_segundos), 0)) as tiempo_total_formato,
            MAX(s.inicio_sesion) as ultima_sesion,
            COALESCE(AVG(s.duracion_segundos), 0) as promedio_duracion_segundos
        ');
        $builder->join('tbl_sesiones_usuario s', "u.id_usuario = s.id_usuario AND s.estado != 'activa'", 'left');

        if ($fechaInicio) {
            $builder->where('s.inicio_sesion >=', $fechaInicio . ' 00:00:00');
        }
        if ($fechaFin) {
            $builder->where('s.inicio_sesion <=', $fechaFin . ' 23:59:59');
        }

        $builder->groupBy('u.id_usuario, u.nombre_completo, u.email, u.tipo_usuario');
        $builder->orderBy('tiempo_total_segundos', 'DESC');

        return $builder->get()->getResultArray();
    }

    /**
     * Obtener historial de sesiones de un usuario
     */
    public function getHistorialUsuario(int $idUsuario, int $limit = 50): array
    {
        return $this->where('id_usuario', $idUsuario)
                    ->orderBy('inicio_sesion', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Obtener estadísticas generales
     */
    public function getEstadisticasGenerales(?string $fechaInicio = null, ?string $fechaFin = null): array
    {
        $builder = $this->db->table('tbl_sesiones_usuario');
        $builder->select('
            COUNT(*) as total_sesiones,
            COUNT(DISTINCT id_usuario) as usuarios_unicos,
            COALESCE(SUM(duracion_segundos), 0) as tiempo_total_segundos,
            COALESCE(AVG(duracion_segundos), 0) as promedio_duracion
        ');
        $builder->where('estado !=', 'activa');

        if ($fechaInicio) {
            $builder->where('inicio_sesion >=', $fechaInicio . ' 00:00:00');
        }
        if ($fechaFin) {
            $builder->where('inicio_sesion <=', $fechaFin . ' 23:59:59');
        }

        return $builder->get()->getRowArray();
    }

    /**
     * Obtener sesiones por día para gráfica
     */
    public function getSesionesPorDia(?string $fechaInicio = null, ?string $fechaFin = null): array
    {
        $builder = $this->db->table('tbl_sesiones_usuario');
        $builder->select('
            DATE(inicio_sesion) as fecha,
            COUNT(*) as total_sesiones,
            COUNT(DISTINCT id_usuario) as usuarios_unicos,
            COALESCE(SUM(duracion_segundos), 0) as tiempo_total
        ');
        $builder->where('estado !=', 'activa');

        if ($fechaInicio) {
            $builder->where('inicio_sesion >=', $fechaInicio . ' 00:00:00');
        }
        if ($fechaFin) {
            $builder->where('inicio_sesion <=', $fechaFin . ' 23:59:59');
        }

        $builder->groupBy('DATE(inicio_sesion)');
        $builder->orderBy('fecha', 'ASC');

        return $builder->get()->getResultArray();
    }

    /**
     * Tiempos de inactividad por rol (en segundos)
     */
    private const TIMEOUT_BY_ROLE = [
        'client'     => 300,   // 5 minutos
        'consultant' => 3600,  // 60 minutos
        'admin'      => 900,   // 15 minutos
    ];

    /**
     * Marcar sesiones expiradas (para ejecutar periódicamente)
     * Usa el timeout según el rol del usuario
     */
    public function marcarSesionesExpiradas(): int
    {
        // Obtener sesiones activas con información del usuario
        $builder = $this->db->table('tbl_sesiones_usuario s');
        $builder->select('s.*, u.tipo_usuario');
        $builder->join('tbl_usuarios u', 'u.id_usuario = s.id_usuario');
        $builder->where('s.estado', 'activa');

        $sesionesActivas = $builder->get()->getResultArray();

        $count = 0;
        $ahora = time();

        foreach ($sesionesActivas as $sesion) {
            $tipoUsuario = $sesion['tipo_usuario'];
            $timeout = self::TIMEOUT_BY_ROLE[$tipoUsuario] ?? 600; // Default 10 min

            $inicioTimestamp = strtotime($sesion['inicio_sesion']);
            $tiempoTranscurrido = $ahora - $inicioTimestamp;

            // Solo marcar como expirada si ha pasado más tiempo que el timeout
            if ($tiempoTranscurrido > $timeout) {
                // La duración es el timeout (momento en que expiró)
                $finTimestamp = $inicioTimestamp + $timeout;

                $this->update($sesion['id_sesion'], [
                    'fin_sesion' => date('Y-m-d H:i:s', $finTimestamp),
                    'duracion_segundos' => $timeout,
                    'estado' => 'expirada'
                ]);
                $count++;
            }
        }

        return $count;
    }
}
