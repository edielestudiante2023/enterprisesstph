<?php

use App\Libraries\DocumentVersionsLibrary;
use App\Models\DocumentVersionModel;

if (!function_exists('assign_standard_document_versions_to_client')) {
    /**
     * Asigna automáticamente las 44 versiones de documentos estándar a un cliente nuevo
     *
     * Este helper consume la librería DocumentVersionsLibrary y crea los registros
     * en document_versions sin necesidad de cargar CSV.
     *
     * @param int $clientId ID del cliente recién creado
     * @param string $changeControlText Texto opcional para control de cambios
     * @return bool True si la asignación fue exitosa, False en caso contrario
     */
    function assign_standard_document_versions_to_client($clientId, $changeControlText = null)
    {
        try {
            $documentVersionModel = new DocumentVersionModel();

            // Verificar si el cliente ya tiene versiones asignadas (evitar duplicados)
            $existingVersions = $documentVersionModel->where('client_id', $clientId)->countAllResults();

            if ($existingVersions > 0) {
                log_message('info', "Cliente {$clientId} ya tiene {$existingVersions} versiones de documentos asignadas. Se omite asignación automática.");
                return true; // No es error, simplemente ya existen
            }

            // Obtener datos de la librería
            $versionsData = DocumentVersionsLibrary::generateDocumentVersionsData($clientId, $changeControlText);

            // Inserción masiva
            $inserted = $documentVersionModel->insertBatch($versionsData);

            if ($inserted) {
                log_message('info', "Se asignaron " . count($versionsData) . " versiones de documentos estándar al cliente {$clientId}");
                return true;
            } else {
                log_message('error', "Error al asignar versiones de documentos estándar al cliente {$clientId}");
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', "Excepción al asignar versiones de documentos al cliente {$clientId}: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('get_client_document_version')) {
    /**
     * Obtiene una versión específica de documento de un cliente
     *
     * @param int $clientId ID del cliente
     * @param int $policyTypeId ID del tipo de política/documento
     * @return array|null Datos de la versión o null si no existe
     */
    function get_client_document_version($clientId, $policyTypeId)
    {
        $documentVersionModel = new DocumentVersionModel();

        return $documentVersionModel->where('client_id', $clientId)
            ->where('policy_type_id', $policyTypeId)
            ->orderBy('version_number', 'DESC')
            ->first();
    }
}

if (!function_exists('sync_missing_document_versions_for_client')) {
    /**
     * Sincroniza versiones de documentos faltantes para un cliente existente
     *
     * Útil si se agregan nuevos documentos a la librería y necesitas
     * actualizar clientes antiguos.
     *
     * @param int $clientId ID del cliente
     * @param string $changeControlText Texto opcional para control de cambios
     * @return int Número de versiones de documentos agregadas
     */
    function sync_missing_document_versions_for_client($clientId, $changeControlText = null)
    {
        $documentVersionModel = new DocumentVersionModel();

        // Obtener policy_type_ids que el cliente ya tiene
        $existingVersions = $documentVersionModel->where('client_id', $clientId)
            ->select('policy_type_id')
            ->findAll();

        $existingIds = array_column($existingVersions, 'policy_type_id');

        // Obtener todos los documentos estándar de la librería
        $standardDocs = DocumentVersionsLibrary::getStandardDocumentVersions();
        $standardIds = array_column($standardDocs, 'policy_type_id');

        // Calcular faltantes
        $missingIds = array_diff($standardIds, $existingIds);

        if (empty($missingIds)) {
            return 0; // No hay nada que sincronizar
        }

        // Crear registros para los faltantes
        $timestamp = date('Y-m-d H:i:s');

        if ($changeControlText === null) {
            $changeControlText = 'Sincronizado automáticamente el ' . date('d/m/Y');
        }

        $dataToInsert = [];

        foreach ($standardDocs as $doc) {
            if (in_array($doc['policy_type_id'], $missingIds)) {
                $dataToInsert[] = [
                    'client_id' => $clientId,
                    'policy_type_id' => $doc['policy_type_id'],
                    'version_number' => 1,
                    'document_type' => $doc['document_type'],
                    'acronym' => $doc['acronym'],
                    'location' => 'DIGITAL',
                    'status' => 'ACTIVO',
                    'change_control' => $changeControlText,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp
                ];
            }
        }

        if (!empty($dataToInsert)) {
            $documentVersionModel->insertBatch($dataToInsert);
            log_message('info', "Se sincronizaron " . count($dataToInsert) . " versiones de documentos faltantes para el cliente {$clientId}");
        }

        return count($dataToInsert);
    }
}

if (!function_exists('get_standard_document_versions_count')) {
    /**
     * Obtiene el número total de versiones de documentos estándar definidos en la librería
     *
     * @return int Número de versiones de documentos (actualmente 44)
     */
    function get_standard_document_versions_count()
    {
        return DocumentVersionsLibrary::getDocumentVersionsCount();
    }
}

if (!function_exists('get_document_versions_stats')) {
    /**
     * Obtiene estadísticas de versiones de documentos para un cliente
     *
     * @param int $clientId ID del cliente
     * @return array Array con estadísticas [total, por_tipo, faltantes]
     */
    function get_document_versions_stats($clientId)
    {
        $documentVersionModel = new DocumentVersionModel();

        // Total de versiones del cliente
        $clientVersions = $documentVersionModel->where('client_id', $clientId)->findAll();
        $totalClient = count($clientVersions);

        // Contar por tipo
        $byType = [];
        foreach ($clientVersions as $version) {
            $type = $version['document_type'];
            if (!isset($byType[$type])) {
                $byType[$type] = 0;
            }
            $byType[$type]++;
        }

        // Total esperado
        $totalExpected = DocumentVersionsLibrary::getDocumentVersionsCount();

        // Faltantes
        $missing = $totalExpected - $totalClient;

        return [
            'total' => $totalClient,
            'expected' => $totalExpected,
            'missing' => $missing,
            'by_type' => $byType,
            'is_complete' => ($missing == 0)
        ];
    }
}
