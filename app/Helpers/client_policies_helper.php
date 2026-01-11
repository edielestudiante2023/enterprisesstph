<?php

use App\Libraries\PolicyTypesLibrary;
use App\Models\ClientPoliciesModel;

if (!function_exists('assign_standard_policies_to_client')) {
    /**
     * Asigna automáticamente los 46 documentos estándar a un cliente nuevo
     *
     * Este helper consume la librería PolicyTypesLibrary y crea los registros
     * en client_policies sin necesidad de cargar CSV.
     *
     * @param int $clientId ID del cliente recién creado
     * @return bool True si la asignación fue exitosa, False en caso contrario
     */
    function assign_standard_policies_to_client($clientId)
    {
        try {
            $clientPoliciesModel = new ClientPoliciesModel();

            // Verificar si el cliente ya tiene políticas asignadas (evitar duplicados)
            $existingPolicies = $clientPoliciesModel->where('client_id', $clientId)->countAllResults();

            if ($existingPolicies > 0) {
                log_message('info', "Cliente {$clientId} ya tiene {$existingPolicies} políticas asignadas. Se omite asignación automática.");
                return true; // No es error, simplemente ya existen
            }

            // Obtener datos de la librería
            $policiesData = PolicyTypesLibrary::generateClientPoliciesData($clientId);

            // Inserción masiva
            $inserted = $clientPoliciesModel->insertBatch($policiesData);

            if ($inserted) {
                log_message('info', "Se asignaron " . count($policiesData) . " documentos estándar al cliente {$clientId}");
                return true;
            } else {
                log_message('error', "Error al asignar documentos estándar al cliente {$clientId}");
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', "Excepción al asignar políticas al cliente {$clientId}: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('get_client_policy_content')) {
    /**
     * Obtiene el contenido de una política de un cliente
     *
     * @param int $clientId ID del cliente
     * @param int $policyTypeId ID del tipo de política
     * @return string Contenido de la política o string vacío
     */
    function get_client_policy_content($clientId, $policyTypeId)
    {
        $clientPoliciesModel = new ClientPoliciesModel();

        $policy = $clientPoliciesModel->where('client_id', $clientId)
            ->where('policy_type_id', $policyTypeId)
            ->first();

        if ($policy && !empty($policy['policy_content'])) {
            return $policy['policy_content'];
        }

        // Si no existe o está vacío, devolver contenido por defecto de la librería
        return PolicyTypesLibrary::getDefaultContent($policyTypeId);
    }
}

if (!function_exists('sync_missing_policies_for_client')) {
    /**
     * Sincroniza políticas faltantes para un cliente existente
     *
     * Útil si se agregan nuevos policy_types a la librería y necesitas
     * actualizar clientes antiguos.
     *
     * @param int $clientId ID del cliente
     * @return int Número de políticas agregadas
     */
    function sync_missing_policies_for_client($clientId)
    {
        $clientPoliciesModel = new ClientPoliciesModel();

        // Obtener policy_type_ids que el cliente ya tiene
        $existingPolicies = $clientPoliciesModel->where('client_id', $clientId)
            ->select('policy_type_id')
            ->findAll();

        $existingIds = array_column($existingPolicies, 'policy_type_id');

        // Obtener todos los IDs estándar de la librería
        $standardIds = PolicyTypesLibrary::getStandardPolicyTypeIds();

        // Calcular faltantes
        $missingIds = array_diff($standardIds, $existingIds);

        if (empty($missingIds)) {
            return 0; // No hay nada que sincronizar
        }

        // Crear registros para los faltantes
        $timestamp = date('Y-m-d H:i:s');
        $defaultContents = PolicyTypesLibrary::getDefaultContents();
        $dataToInsert = [];

        foreach ($missingIds as $policyTypeId) {
            $dataToInsert[] = [
                'client_id' => $clientId,
                'policy_type_id' => $policyTypeId,
                'policy_content' => $defaultContents[$policyTypeId] ?? '',
                'created_at' => $timestamp,
                'updated_at' => $timestamp
            ];
        }

        $clientPoliciesModel->insertBatch($dataToInsert);

        log_message('info', "Se sincronizaron " . count($missingIds) . " políticas faltantes para el cliente {$clientId}");

        return count($missingIds);
    }
}

if (!function_exists('get_standard_policy_types_count')) {
    /**
     * Obtiene el número total de policy_types estándar definidos en la librería
     *
     * @return int Número de policy_types (actualmente 44)
     */
    function get_standard_policy_types_count()
    {
        return count(PolicyTypesLibrary::getStandardPolicyTypeIds());
    }
}
