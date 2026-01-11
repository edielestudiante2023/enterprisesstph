<?php

use App\Libraries\DocumentLibrary;

/**
 * Helper simplificado para acceso rápido a DocumentLibrary
 *
 * Funciones de conveniencia para leer documentos desde la librería estática
 * sin necesidad de instanciar la clase cada vez.
 */

if (!function_exists('get_document')) {
    /**
     * Obtiene un documento por su ID
     *
     * @param int $documentId ID del documento (policy_type_id)
     * @return array|null Datos del documento
     */
    function get_document($documentId)
    {
        return DocumentLibrary::getDocument($documentId);
    }
}

if (!function_exists('get_latest_version')) {
    /**
     * Obtiene la última versión de un documento
     * (Alias de get_document para compatibilidad con código existente)
     *
     * @param int $documentId ID del documento
     * @return array|null Datos del documento
     */
    function get_latest_version($documentId)
    {
        return DocumentLibrary::getLatestVersion($documentId);
    }
}

if (!function_exists('get_all_document_versions')) {
    /**
     * Obtiene todas las versiones de un documento
     * (Para Propiedad Horizontal siempre retorna array con 1 elemento)
     *
     * @param int $documentId ID del documento
     * @return array Array de versiones
     */
    function get_all_document_versions($documentId)
    {
        return DocumentLibrary::getAllVersions($documentId);
    }
}

if (!function_exists('get_policy_type')) {
    /**
     * Obtiene información de un tipo de política/documento
     * (Alias de get_document para compatibilidad)
     *
     * @param int $policyTypeId ID del tipo de política
     * @return array|null Datos del documento
     */
    function get_policy_type($policyTypeId)
    {
        return DocumentLibrary::getDocument($policyTypeId);
    }
}

if (!function_exists('document_exists')) {
    /**
     * Verifica si un documento existe en la librería
     *
     * @param int $documentId ID del documento
     * @return bool True si existe
     */
    function document_exists($documentId)
    {
        return DocumentLibrary::exists($documentId);
    }
}
