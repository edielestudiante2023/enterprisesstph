<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\SessionModel;

class AuthFilter implements FilterInterface
{
    /**
     * Tiempos de inactividad por rol (en segundos)
     */
    private const TIMEOUT_BY_ROLE = [
        'client'     => 300,   // 5 minutos
        'consultant' => 600,   // 10 minutos
        'admin'      => 900,   // 15 minutos
    ];

    /**
     * Tiempo de inactividad por defecto (10 minutos)
     */
    private const DEFAULT_TIMEOUT = 600;

    /**
     * Verificar autenticación y timeout por inactividad antes de cada petición
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // Verificar si está logueado
        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $role = $session->get('role');
        $lastActivity = $session->get('last_activity');
        $currentTime = time();

        // Obtener timeout según el rol
        $timeout = self::TIMEOUT_BY_ROLE[$role] ?? self::DEFAULT_TIMEOUT;

        // Verificar si ha excedido el tiempo de inactividad
        if ($lastActivity && ($currentTime - $lastActivity) > $timeout) {
            // Cerrar sesión en la base de datos
            $idSesion = $session->get('id_sesion');
            if ($idSesion) {
                $sessionModel = new SessionModel();
                // Calcular duración hasta la última actividad (no hasta el timeout)
                $this->cerrarSesionConDuracion($sessionModel, $idSesion, $lastActivity);
            }

            // Destruir sesión PHP
            $session->destroy();

            // Redirigir al login con mensaje de sesión expirada
            return redirect()->to('/login')->with('msg', 'Tu sesión ha expirado por inactividad. Por favor, inicia sesión nuevamente.');
        }

        // Actualizar última actividad
        $session->set('last_activity', $currentTime);

        return null;
    }

    /**
     * No se necesita procesamiento después de la respuesta
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No hacer nada
    }

    /**
     * Cerrar sesión calculando duración hasta última actividad
     */
    private function cerrarSesionConDuracion(SessionModel $sessionModel, int $idSesion, int $lastActivity): void
    {
        $sesion = $sessionModel->find($idSesion);

        if (!$sesion || $sesion['estado'] !== 'activa') {
            return;
        }

        $inicio = strtotime($sesion['inicio_sesion']);
        $duracion = $lastActivity - $inicio;

        $sessionModel->update($idSesion, [
            'fin_sesion' => date('Y-m-d H:i:s', $lastActivity),
            'duracion_segundos' => $duracion,
            'estado' => 'expirada'
        ]);
    }
}
