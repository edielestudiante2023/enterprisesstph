<?php

use App\Libraries\AccessLibrary;

/**
 * Helper simplificado para acceso rápido a AccessLibrary
 *
 * Funciones de conveniencia para leer accesos/menús desde la librería estática
 * sin necesidad de instanciar la clase cada vez.
 */

if (!function_exists('get_access')) {
    /**
     * Obtiene un acceso por su ID
     *
     * @param int $accessId ID del acceso
     * @return array|null Datos del acceso
     */
    function get_access($accessId)
    {
        return AccessLibrary::getAccess($accessId);
    }
}

if (!function_exists('get_all_accesses')) {
    /**
     * Obtiene todos los accesos disponibles
     *
     * @return array Array de todos los accesos
     */
    function get_all_accesses()
    {
        return AccessLibrary::getAllAccesses();
    }
}

if (!function_exists('get_accesses_by_dimension')) {
    /**
     * Obtiene accesos filtrados por dimensión PHVA
     *
     * @param string $dimension Dimensión (Planear, Hacer, Verificar, Actuar, Indicadores)
     * @return array Array de accesos de esa dimensión
     */
    function get_accesses_by_dimension($dimension)
    {
        return AccessLibrary::getAccessesByDimension($dimension);
    }
}

if (!function_exists('get_accesses_by_standard')) {
    /**
     * Obtiene accesos disponibles para un estándar específico
     *
     * @param string $standardName Nombre del estándar (Mensual, Bimensual, Trimestral, Proyecto)
     * @return array Array de accesos disponibles para ese estándar
     */
    function get_accesses_by_standard($standardName)
    {
        return AccessLibrary::getAccessesByStandard($standardName);
    }
}

if (!function_exists('access_exists')) {
    /**
     * Verifica si un acceso existe en la librería
     *
     * @param int $accessId ID del acceso
     * @return bool True si existe
     */
    function access_exists($accessId)
    {
        return AccessLibrary::exists($accessId);
    }
}
