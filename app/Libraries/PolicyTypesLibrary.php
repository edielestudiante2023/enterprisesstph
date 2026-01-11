<?php

namespace App\Libraries;

/**
 * Librería de tipos de políticas/documentos estándar para Propiedad Horizontal
 *
 * Esta librería define los 46 tipos de documentos que se asignan automáticamente
 * a cada nuevo cliente de Propiedad Horizontal en Colombia.
 *
 * Basado en: Decreto 1072 de 2015, Resolución 0312 de 2019
 *
 * @version 1.0
 * @date 2025-01-09
 */
class PolicyTypesLibrary
{
    /**
     * Obtiene todos los policy_type_ids que deben asignarse a un nuevo cliente
     *
     * @return array Array de IDs de policy_types (del 1 al 46)
     */
    public static function getStandardPolicyTypeIds()
    {
        return [
            1, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20,
            21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36,
            37, 38, 39, 40, 41, 42, 43, 44, 45, 46
        ];
    }

    /**
     * Obtiene contenido predeterminado para tipos específicos
     *
     * Algunos policy_types tienen contenido genérico aplicable a todas las copropiedades.
     * La mayoría quedan vacíos ya que se completan desde document_versions.
     *
     * @return array Array asociativo [policy_type_id => contenido]
     */
    public static function getDefaultContents()
    {
        return [
            // Policy types con contenido estándar para Propiedad Horizontal
            11 => 'Administración e implementación del Sistema de Gestión de Seguridad y Salud en el Trabajo, para lo cual deberá planificar, organizar y dirigir una evaluación, informar a la alta dirección sobre el funcionamiento y los resultados del SG-SST, y actualización de acuerdo con la normatividad vigente.',

            15 => 'Misión: Liderar la transformación positiva en el entorno laboral mediante el diagnóstico especializado en la Batería de Riesgo Psicosocial y Consultoría en Seguridad y Salud en el Trabajo. Nos dedicamos exclusivamente a la evaluación exhaustiva y precisa de los factores psicosociales y las condiciones de seguridad y salud en la Propiedad Horizontal.',

            16 => 'Visión: Posicionar a Cycloid Talent como el principal proveedor de administración SG-SST en Soacha para el 2024 y en Bogotá para el 2025, así como crecer en 2024 un 50% la participación de nuestros clientes de Diagnóstico de Evaluación de Factores de Riesgo Psicosocial en relación al año anterior.',

            26 => 'No Existen Sucursales',

            34 => 'ARL SURA',

            // Los demás policy_types (mayoría) quedan con contenido vacío
            // Se gestionan a través de document_versions
        ];
    }

    /**
     * Obtiene el contenido predeterminado para un policy_type específico
     *
     * @param int $policyTypeId ID del tipo de política
     * @return string Contenido predeterminado o string vacío
     */
    public static function getDefaultContent($policyTypeId)
    {
        $contents = self::getDefaultContents();
        return $contents[$policyTypeId] ?? '';
    }

    /**
     * Verifica si un policy_type tiene contenido predeterminado
     *
     * @param int $policyTypeId ID del tipo de política
     * @return bool True si tiene contenido predeterminado
     */
    public static function hasDefaultContent($policyTypeId)
    {
        $contents = self::getDefaultContents();
        return isset($contents[$policyTypeId]) && !empty($contents[$policyTypeId]);
    }

    /**
     * Obtiene la estructura completa para inserción masiva en client_policies
     *
     * Este método genera los 46 registros listos para insertar cuando se crea un cliente.
     *
     * @param int $clientId ID del cliente
     * @return array Array de registros para insertar
     */
    public static function generateClientPoliciesData($clientId)
    {
        $policyTypeIds = self::getStandardPolicyTypeIds();
        $defaultContents = self::getDefaultContents();
        $timestamp = date('Y-m-d H:i:s');

        $data = [];

        foreach ($policyTypeIds as $policyTypeId) {
            $data[] = [
                'client_id' => $clientId,
                'policy_type_id' => $policyTypeId,
                'policy_content' => $defaultContents[$policyTypeId] ?? '',
                'created_at' => $timestamp,
                'updated_at' => $timestamp
            ];
        }

        return $data;
    }

    /**
     * Mapeo de policy_type_id a nombres descriptivos (opcional, para debugging)
     *
     * @return array Array asociativo [id => nombre]
     */
    public static function getPolicyTypeNames()
    {
        return [
            1 => 'Formato SST-001',
            4 => 'Formato SST-002',
            5 => 'Formato SST-003',
            6 => 'Formato SST-004',
            7 => 'Formato SST-005',
            8 => 'Formato SST-006',
            9 => 'Formato SST-007',
            10 => 'Formato SST-008',
            11 => 'Formato SST-009',
            12 => 'Formato SST-010',
            13 => 'Formato SST-011',
            14 => 'Manual SST-001',
            15 => 'Misión',
            16 => 'Visión',
            17 => 'Procedimiento SST-001',
            18 => 'Programa SST-001',
            19 => 'Programa SST-002',
            20 => 'Formato SST-012',
            21 => 'Plan SST-001',
            22 => 'Plan SST-002',
            23 => 'Plan SST-003',
            24 => 'Plan SST-004',
            25 => 'Plan SST-005',
            26 => 'Registro SST-001',
            27 => 'Formato SST-013',
            28 => 'Formato SST-014',
            29 => 'Procedimiento SST-002',
            30 => 'Procedimiento SST-003',
            31 => 'Manual SST-002',
            32 => 'Procedimiento SST-004',
            33 => 'Programa SST-005',
            34 => 'ARL',
            35 => 'Programa SST-003',
            36 => 'Procedimiento SST-007',
            37 => 'Formato SST-101',
            38 => 'Formato SST-015',
            39 => 'Procedimiento SST-008',
            40 => 'Procedimiento SST-009',
            41 => 'Procedimiento SST-010',
            42 => 'Programa SST-004',
            43 => 'Programa SST-005',
            44 => 'Formato SST-016',
            45 => 'Procedimiento SST-011',
            46 => 'Matriz SST-001'
        ];
    }
}
