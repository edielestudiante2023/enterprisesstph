<?php

namespace App\Services;

use App\Models\PtaClienteAuditModel;

/**
 * Servicio centralizado para registrar auditoría del Plan de Trabajo Anual (PTA)
 * Uso: PtaAuditService::log($idPta, 'UPDATE', 'estado_actividad', 'ABIERTA', 'CERRADA', 'editinginlinePtaClienteNuevaModel');
 */
class PtaAuditService
{
    /**
     * Campos con nombres legibles para las descripciones
     */
    private static $camposLegibles = [
        'estado_actividad'                   => 'Estado de Actividad',
        'porcentaje_avance'                  => 'Porcentaje de Avance',
        'fecha_propuesta'                    => 'Fecha Propuesta',
        'fecha_cierre'                       => 'Fecha de Cierre',
        'observaciones'                      => 'Observaciones',
        'phva_plandetrabajo'                 => 'PHVA',
        'numeral_plandetrabajo'              => 'Numeral del Plan',
        'actividad_plandetrabajo'            => 'Actividad',
        'responsable_sugerido_plandetrabajo' => 'Responsable Sugerido',
        'id_cliente'                         => 'Cliente',
        'tipo_servicio'                      => 'Tipo de Servicio',
    ];

    /**
     * Registrar un evento de auditoría
     *
     * @param int         $idPtaCliente     ID del registro PTA
     * @param string      $accion           INSERT|UPDATE|DELETE|BULK_UPDATE
     * @param string|null $campoModificado  Nombre del campo que cambió
     * @param mixed       $valorAnterior    Valor antes del cambio
     * @param mixed       $valorNuevo       Valor después del cambio
     * @param string|null $metodo           Nombre del método que realizó el cambio
     * @param int|null    $idCliente        ID del cliente (opcional, se obtiene del registro si no se pasa)
     * @return bool
     */
    public static function log(
        int $idPtaCliente,
        string $accion,
        ?string $campoModificado = null,
        $valorAnterior = null,
        $valorNuevo = null,
        ?string $metodo = null,
        ?int $idCliente = null
    ): bool {
        try {
            $session = session();
            $request = service('request');

            // Obtener datos del usuario de la sesión
            $idUsuario     = $session->get('id_usuario') ?? $session->get('user_id') ?? 0;
            $nombreUsuario = $session->get('nombre') ?? $session->get('nombre_usuario') ?? $session->get('username') ?? 'Sistema';
            $emailUsuario  = $session->get('email') ?? $session->get('correo') ?? '';
            $rolUsuario    = $session->get('rol') ?? $session->get('role') ?? '';

            // Si no se pasó id_cliente, intentar obtenerlo del registro PTA
            if ($idCliente === null && $accion !== 'INSERT') {
                $ptaModel = new \App\Models\PtaClienteNuevaModel();
                $registro = $ptaModel->find($idPtaCliente);
                $idCliente = $registro['id_cliente'] ?? null;
            }

            // Generar descripción legible
            $descripcion = self::generarDescripcion($accion, $campoModificado, $valorAnterior, $valorNuevo);

            // Preparar datos para insertar
            $auditData = [
                'id_ptacliente'    => $idPtaCliente,
                'id_cliente'       => $idCliente,
                'accion'           => $accion,
                'campo_modificado' => $campoModificado,
                'valor_anterior'   => is_array($valorAnterior) ? json_encode($valorAnterior) : (string) $valorAnterior,
                'valor_nuevo'      => is_array($valorNuevo) ? json_encode($valorNuevo) : (string) $valorNuevo,
                'id_usuario'       => $idUsuario,
                'nombre_usuario'   => $nombreUsuario,
                'email_usuario'    => $emailUsuario,
                'rol_usuario'      => $rolUsuario,
                'ip_address'       => $request->getIPAddress(),
                'user_agent'       => substr($request->getUserAgent()->getAgentString(), 0, 500),
                'metodo'           => $metodo,
                'descripcion'      => $descripcion,
                'fecha_accion'     => date('Y-m-d H:i:s'),
            ];

            $auditModel = new PtaClienteAuditModel();
            return (bool) $auditModel->insert($auditData);
        } catch (\Exception $e) {
            log_message('error', 'Error en PtaAuditService::log: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Registrar múltiples cambios de una sola vez (para ediciones de formulario completo)
     *
     * @param int    $idPtaCliente   ID del registro PTA
     * @param array  $datosAnteriores Datos antes del cambio
     * @param array  $datosNuevos     Datos después del cambio
     * @param string $metodo          Nombre del método
     * @param int|null $idCliente     ID del cliente
     * @return int Cantidad de cambios registrados
     */
    public static function logMultiple(
        int $idPtaCliente,
        array $datosAnteriores,
        array $datosNuevos,
        string $metodo,
        ?int $idCliente = null
    ): int {
        $cambiosRegistrados = 0;

        // Campos a ignorar en la auditoría
        $camposIgnorar = ['updated_at', 'created_at', 'id_ptacliente'];

        foreach ($datosNuevos as $campo => $valorNuevo) {
            // Ignorar campos del sistema
            if (in_array($campo, $camposIgnorar)) {
                continue;
            }

            // Ignorar si el campo empieza con 'filter_'
            if (strpos($campo, 'filter_') === 0) {
                continue;
            }

            // Ignorar CSRF token
            if (strpos($campo, 'csrf') !== false) {
                continue;
            }

            $valorAnterior = $datosAnteriores[$campo] ?? null;

            // Solo registrar si hay cambio real
            if ((string) $valorAnterior !== (string) $valorNuevo) {
                if (self::log($idPtaCliente, 'UPDATE', $campo, $valorAnterior, $valorNuevo, $metodo, $idCliente)) {
                    $cambiosRegistrados++;
                }
            }
        }

        return $cambiosRegistrados;
    }

    /**
     * Registrar creación de un nuevo registro
     */
    public static function logInsert(int $idPtaCliente, array $datos, string $metodo = 'addpostPtaClienteNuevaModel'): bool
    {
        $idCliente = $datos['id_cliente'] ?? null;
        $descripcion = 'Se creó un nuevo registro en el Plan de Trabajo';

        return self::log($idPtaCliente, 'INSERT', null, null, json_encode($datos), $metodo, $idCliente);
    }

    /**
     * Registrar eliminación de un registro
     */
    public static function logDelete(int $idPtaCliente, array $datosAnteriores, string $metodo = 'deletePtaClienteNuevaModel'): bool
    {
        $idCliente = $datosAnteriores['id_cliente'] ?? null;

        return self::log($idPtaCliente, 'DELETE', null, json_encode($datosAnteriores), null, $metodo, $idCliente);
    }

    /**
     * Registrar actualización masiva
     */
    public static function logBulkUpdate(array $ids, string $campo, $valorAnterior, $valorNuevo, string $metodo, ?int $idCliente = null): int
    {
        $registrados = 0;

        foreach ($ids as $id) {
            if (self::log($id, 'BULK_UPDATE', $campo, $valorAnterior, $valorNuevo, $metodo, $idCliente)) {
                $registrados++;
            }
        }

        return $registrados;
    }

    /**
     * Generar descripción legible del cambio
     */
    private static function generarDescripcion(string $accion, ?string $campo, $valorAnterior, $valorNuevo): string
    {
        $campoLegible = self::$camposLegibles[$campo] ?? $campo ?? 'registro';

        switch ($accion) {
            case 'INSERT':
                return 'Se creó un nuevo registro en el Plan de Trabajo';

            case 'DELETE':
                return 'Se eliminó el registro del Plan de Trabajo';

            case 'UPDATE':
                if ($campo === 'estado_actividad') {
                    return "Cambió el estado de '{$valorAnterior}' a '{$valorNuevo}'";
                }
                if ($campo === 'porcentaje_avance') {
                    return "Cambió el avance de {$valorAnterior}% a {$valorNuevo}%";
                }
                if ($campo === 'fecha_propuesta' || $campo === 'fecha_cierre') {
                    return "Cambió {$campoLegible} de '{$valorAnterior}' a '{$valorNuevo}'";
                }
                return "Modificó {$campoLegible}: '{$valorAnterior}' → '{$valorNuevo}'";

            case 'BULK_UPDATE':
                return "Actualización masiva: {$campoLegible} cambiado a '{$valorNuevo}'";

            default:
                return "Acción {$accion} en {$campoLegible}";
        }
    }

    /**
     * Obtener nombre legible de un campo
     */
    public static function getCampoLegible(string $campo): string
    {
        return self::$camposLegibles[$campo] ?? $campo;
    }
}
