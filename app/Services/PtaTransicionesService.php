<?php

namespace App\Services;

use App\Models\PtaTransicionesModel;

/**
 * Servicio para registrar transiciones de estado del PTA.
 * Solo registra cuando una actividad sale de estado ABIERTA.
 */
class PtaTransicionesService
{
    /**
     * Evalúa si hubo una transición de ABIERTA a otro estado y la registra.
     *
     * @param int    $idPtaCliente  ID de la actividad
     * @param int    $idCliente     ID del cliente
     * @param string $estadoAnterior Estado antes del cambio
     * @param string $estadoNuevo    Estado después del cambio
     * @return bool true si se registró una transición, false si no aplicaba
     */
    public static function registrar(
        int $idPtaCliente,
        int $idCliente,
        string $estadoAnterior,
        string $estadoNuevo
    ): bool {
        // Tratar vacío como ABIERTA (en BD muchos registros no tienen el texto literal)
        if ($estadoAnterior === '' || $estadoAnterior === null) {
            $estadoAnterior = 'ABIERTA';
        }

        // Solo registrar si el estado anterior era ABIERTA y el nuevo es diferente
        if ($estadoAnterior !== 'ABIERTA' || $estadoNuevo === 'ABIERTA') {
            return false;
        }

        try {
            $session = session();

            $idUsuario     = $session->get('id_usuario') ?? $session->get('user_id') ?? 0;
            $nombreUsuario = $session->get('nombre') ?? $session->get('nombre_usuario') ?? $session->get('username') ?? 'Sistema';

            $model = new PtaTransicionesModel();
            return (bool) $model->insert([
                'id_ptacliente'    => $idPtaCliente,
                'id_cliente'       => $idCliente,
                'estado_anterior'  => $estadoAnterior,
                'estado_nuevo'     => $estadoNuevo,
                'id_usuario'       => $idUsuario,
                'nombre_usuario'   => $nombreUsuario,
                'fecha_transicion' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            log_message('error', 'PtaTransicionesService::registrar error: ' . $e->getMessage());
            return false;
        }
    }
}
