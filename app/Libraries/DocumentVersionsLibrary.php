<?php

namespace App\Libraries;

/**
 * Librería de versiones de documentos estándar para Propiedad Horizontal
 *
 * Esta librería define las 44 versiones de documentos que se asignan automáticamente
 * a cada nuevo cliente de Propiedad Horizontal en Colombia.
 *
 * Estructura de document_versions:
 * - client_id: ID del cliente
 * - policy_type_id: ID del tipo de documento (1-46)
 * - version_number: Número de versión (siempre 1 para nuevos clientes)
 * - document_type: Tipo (FT, MAN, PRC, PRG, PL, REG, MA)
 * - acronym: Código del documento (SST-001, SST-002, etc.)
 * - location: Ubicación (DIGITAL)
 * - status: Estado (ACTIVO)
 * - change_control: Control de cambios
 *
 * @version 1.0
 * @date 2025-01-09
 */
class DocumentVersionsLibrary
{
    /**
     * Obtiene la estructura completa de las 44 versiones de documentos estándar
     *
     * Basado en el archivo SQL: app/SQL/document_versions.sql
     *
     * @return array Array de arrays asociativos con la estructura de cada documento
     */
    public static function getStandardDocumentVersions()
    {
        return [
            // Formatos (FT)
            ['policy_type_id' => 1,  'document_type' => 'FT',  'acronym' => 'SST-001'],
            ['policy_type_id' => 4,  'document_type' => 'FT',  'acronym' => 'SST-002'],
            ['policy_type_id' => 5,  'document_type' => 'FT',  'acronym' => 'SST-003'],
            ['policy_type_id' => 6,  'document_type' => 'FT',  'acronym' => 'SST-004'],
            ['policy_type_id' => 7,  'document_type' => 'FT',  'acronym' => 'SST-005'],
            ['policy_type_id' => 8,  'document_type' => 'FT',  'acronym' => 'SST-006'],
            ['policy_type_id' => 9,  'document_type' => 'FT',  'acronym' => 'SST-007'],
            ['policy_type_id' => 10, 'document_type' => 'FT',  'acronym' => 'SST-008'],
            ['policy_type_id' => 11, 'document_type' => 'FT',  'acronym' => 'SST-009'],
            ['policy_type_id' => 12, 'document_type' => 'FT',  'acronym' => 'SST-010'],
            ['policy_type_id' => 13, 'document_type' => 'FT',  'acronym' => 'SST-011'],
            ['policy_type_id' => 20, 'document_type' => 'FT',  'acronym' => 'SST-012'],
            ['policy_type_id' => 27, 'document_type' => 'FT',  'acronym' => 'SST-013'],
            ['policy_type_id' => 28, 'document_type' => 'FT',  'acronym' => 'SST-014'],
            ['policy_type_id' => 38, 'document_type' => 'FT',  'acronym' => 'SST-015'],
            ['policy_type_id' => 44, 'document_type' => 'FT',  'acronym' => 'SST-016'],
            ['policy_type_id' => 37, 'document_type' => 'FT',  'acronym' => 'SST-101'],

            // Manuales (MAN)
            ['policy_type_id' => 14, 'document_type' => 'MAN', 'acronym' => 'SST-001'],
            ['policy_type_id' => 31, 'document_type' => 'MAN', 'acronym' => 'SST-002'],

            // Procedimientos (PRC)
            ['policy_type_id' => 17, 'document_type' => 'PRC', 'acronym' => 'SST-001'],
            ['policy_type_id' => 29, 'document_type' => 'PRC', 'acronym' => 'SST-002'],
            ['policy_type_id' => 30, 'document_type' => 'PRC', 'acronym' => 'SST-003'],
            ['policy_type_id' => 32, 'document_type' => 'PRC', 'acronym' => 'SST-004'],
            ['policy_type_id' => 34, 'document_type' => 'PRC', 'acronym' => 'SST-006'],
            ['policy_type_id' => 36, 'document_type' => 'PRC', 'acronym' => 'SST-007'],
            ['policy_type_id' => 39, 'document_type' => 'PRC', 'acronym' => 'SST-008'],
            ['policy_type_id' => 40, 'document_type' => 'PRC', 'acronym' => 'SST-009'],
            ['policy_type_id' => 41, 'document_type' => 'PRC', 'acronym' => 'SST-010'],
            ['policy_type_id' => 45, 'document_type' => 'PRC', 'acronym' => 'SST-011'],

            // Programas (PRG)
            ['policy_type_id' => 18, 'document_type' => 'PRG', 'acronym' => 'SST-001'],
            ['policy_type_id' => 19, 'document_type' => 'PRG', 'acronym' => 'SST-002'],
            ['policy_type_id' => 35, 'document_type' => 'PRG', 'acronym' => 'SST-003'],
            ['policy_type_id' => 42, 'document_type' => 'PRG', 'acronym' => 'SST-004'],
            ['policy_type_id' => 33, 'document_type' => 'PRG', 'acronym' => 'SST-005'],
            ['policy_type_id' => 43, 'document_type' => 'PRG', 'acronym' => 'SST-005'], // Duplicado en SQL original

            // Planes (PL)
            ['policy_type_id' => 21, 'document_type' => 'PL',  'acronym' => 'SST-001'],
            ['policy_type_id' => 22, 'document_type' => 'PL',  'acronym' => 'SST-002'],
            ['policy_type_id' => 23, 'document_type' => 'PL',  'acronym' => 'SST-003'],
            ['policy_type_id' => 24, 'document_type' => 'PL',  'acronym' => 'SST-004'],
            ['policy_type_id' => 25, 'document_type' => 'PL',  'acronym' => 'SST-005'],

            // Registros (REG)
            ['policy_type_id' => 26, 'document_type' => 'REG', 'acronym' => 'SST-001'],

            // Matrices (MA)
            ['policy_type_id' => 46, 'document_type' => 'MA',  'acronym' => 'SST-001'],
        ];
    }

    /**
     * Genera los datos completos para inserción masiva en document_versions
     *
     * @param int $clientId ID del cliente
     * @param string $changeControlText Texto opcional para change_control (por defecto usa fecha actual)
     * @return array Array de registros listos para insertar
     */
    public static function generateDocumentVersionsData($clientId, $changeControlText = null)
    {
        $documents = self::getStandardDocumentVersions();
        $timestamp = date('Y-m-d H:i:s');

        // Texto por defecto para change_control
        if ($changeControlText === null) {
            $changeControlText = 'Elaborado por Cycloid Talent el ' . date('d') . ' de ' .
                                 self::getSpanishMonth(date('n')) . ' de ' . date('Y');
        }

        $data = [];

        foreach ($documents as $doc) {
            $data[] = [
                'client_id' => $clientId,
                'policy_type_id' => $doc['policy_type_id'],
                'version_number' => 1, // Siempre versión 1 para nuevos clientes
                'document_type' => $doc['document_type'],
                'acronym' => $doc['acronym'],
                'location' => 'DIGITAL',
                'status' => 'ACTIVO',
                'change_control' => $changeControlText,
                'created_at' => $timestamp,
                'updated_at' => $timestamp
            ];
        }

        return $data;
    }

    /**
     * Obtiene el nombre del mes en español
     *
     * @param int $monthNumber Número del mes (1-12)
     * @return string Nombre del mes en español
     */
    private static function getSpanishMonth($monthNumber)
    {
        $months = [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
        ];
        return $months[$monthNumber] ?? 'enero';
    }

    /**
     * Obtiene el total de documentos estándar
     *
     * @return int Número total de documentos (44)
     */
    public static function getDocumentVersionsCount()
    {
        return count(self::getStandardDocumentVersions());
    }

    /**
     * Obtiene documentos agrupados por tipo
     *
     * Útil para reportes y visualizaciones
     *
     * @return array Array asociativo [tipo => cantidad]
     */
    public static function getDocumentCountsByType()
    {
        $documents = self::getStandardDocumentVersions();
        $counts = [];

        foreach ($documents as $doc) {
            $type = $doc['document_type'];
            if (!isset($counts[$type])) {
                $counts[$type] = 0;
            }
            $counts[$type]++;
        }

        return $counts;
    }

    /**
     * Obtiene nombres descriptivos de los tipos de documentos
     *
     * @return array Array asociativo [código => nombre completo]
     */
    public static function getDocumentTypeNames()
    {
        return [
            'FT' => 'Formato',
            'MAN' => 'Manual',
            'PRC' => 'Procedimiento',
            'PRG' => 'Programa',
            'PL' => 'Plan',
            'REG' => 'Registro',
            'MA' => 'Matriz'
        ];
    }

    /**
     * Valida si un documento existe en la librería estándar
     *
     * @param int $policyTypeId ID del tipo de política
     * @return bool True si existe en los documentos estándar
     */
    public static function isStandardDocument($policyTypeId)
    {
        $documents = self::getStandardDocumentVersions();
        foreach ($documents as $doc) {
            if ($doc['policy_type_id'] == $policyTypeId) {
                return true;
            }
        }
        return false;
    }
}
