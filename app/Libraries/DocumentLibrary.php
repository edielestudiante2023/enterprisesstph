<?php

namespace App\Libraries;

/**
 * Librería centralizada de documentos SST para Propiedad Horizontal
 *
 * Esta librería contiene TODOS los documentos estándar que son idénticos para todos los clientes.
 * NO se duplican en la base de datos, se consumen directamente desde aquí.
 *
 * Reemplaza:
 * - client_policies (tabla eliminada)
 * - document_versions (tabla eliminada)
 * - policy_types (tabla reducida o eliminada)
 *
 * @version 2.0
 * @date 2025-01-09
 */
class DocumentLibrary
{
    /**
     * Obtiene un documento específico por su ID
     *
     * @param int $documentId ID del documento (policy_type_id)
     * @return array|null Datos del documento o null si no existe
     */
    public static function getDocument($documentId)
    {
        $documents = self::getAllDocuments();
        return $documents[$documentId] ?? null;
    }

    /**
     * Obtiene todos los documentos estándar
     *
     * @return array Array asociativo [id => datos del documento]
     */
    public static function getAllDocuments()
    {
        $baseDate = '2025-01-09 00:00:00';
        $changeControl = 'Elaborado por Cycloid Talent el 9 de enero de 2025';

        return [
            // FORMATOS (FT)
            1 => [
                'id' => 1,
                'type_name' => 'Política de SST',
                'description' => 'Política del Sistema de Gestión de Seguridad y Salud en el Trabajo',
                'document_type' => 'FT',
                'acronym' => 'SST-001',
                'version_number' => 1,
                'location' => 'DIGITAL',
                'status' => 'ACTIVO',
                'change_control' => $changeControl,
                'created_at' => $baseDate,
                'updated_at' => $baseDate,
                'default_content' => ''
            ],
            4 => [
                'id' => 4,
                'type_name' => 'Formato de Inspección',
                'description' => 'Formato para inspecciones de seguridad',
                'document_type' => 'FT',
                'acronym' => 'SST-002',
                'version_number' => 1,
                'location' => 'DIGITAL',
                'status' => 'ACTIVO',
                'change_control' => $changeControl,
                'created_at' => $baseDate,
                'updated_at' => $baseDate,
                'default_content' => ''
            ],
            5 => [
                'id' => 5,
                'type_name' => 'Registro de Capacitaciones',
                'description' => 'Formato para registro de capacitaciones',
                'document_type' => 'FT',
                'acronym' => 'SST-003',
                'version_number' => 1,
                'location' => 'DIGITAL',
                'status' => 'ACTIVO',
                'change_control' => $changeControl,
                'created_at' => $baseDate,
                'updated_at' => $baseDate,
                'default_content' => ''
            ],
            6 => [
                'id' => 6,
                'type_name' => 'Control de Dotación',
                'description' => 'Formato de control de dotación EPP',
                'document_type' => 'FT',
                'acronym' => 'SST-004',
                'version_number' => 1,
                'location' => 'DIGITAL',
                'status' => 'ACTIVO',
                'change_control' => $changeControl,
                'created_at' => $baseDate,
                'updated_at' => $baseDate,
                'default_content' => ''
            ],
            7 => [
                'id' => 7,
                'type_name' => 'Reporte de Incidentes',
                'description' => 'Formato para reporte de incidentes',
                'document_type' => 'FT',
                'acronym' => 'SST-005',
                'version_number' => 1,
                'location' => 'DIGITAL',
                'status' => 'ACTIVO',
                'change_control' => $changeControl,
                'created_at' => $baseDate,
                'updated_at' => $baseDate,
                'default_content' => ''
            ],
            8 => [
                'id' => 8,
                'type_name' => 'Evaluación de Riesgos',
                'description' => 'Formato de evaluación de riesgos',
                'document_type' => 'FT',
                'acronym' => 'SST-006',
                'version_number' => 1,
                'location' => 'DIGITAL',
                'status' => 'ACTIVO',
                'change_control' => $changeControl,
                'created_at' => $baseDate,
                'updated_at' => $baseDate,
                'default_content' => ''
            ],
            9 => [
                'id' => 9,
                'type_name' => 'Permisos de Trabajo',
                'description' => 'Formato de permisos de trabajo en alturas',
                'document_type' => 'FT',
                'acronym' => 'SST-007',
                'version_number' => 1,
                'location' => 'DIGITAL',
                'status' => 'ACTIVO',
                'change_control' => $changeControl,
                'created_at' => $baseDate,
                'updated_at' => $baseDate,
                'default_content' => ''
            ],
            10 => [
                'id' => 10,
                'type_name' => 'Exámenes Médicos',
                'description' => 'Control de exámenes médicos ocupacionales',
                'document_type' => 'FT',
                'acronym' => 'SST-008',
                'version_number' => 1,
                'location' => 'DIGITAL',
                'status' => 'ACTIVO',
                'change_control' => $changeControl,
                'created_at' => $baseDate,
                'updated_at' => $baseDate,
                'default_content' => ''
            ],
            11 => [
                'id' => 11,
                'type_name' => 'Responsabilidades SST',
                'description' => 'Asignación de responsabilidades en SST',
                'document_type' => 'FT',
                'acronym' => 'SST-009',
                'version_number' => 1,
                'location' => 'DIGITAL',
                'status' => 'ACTIVO',
                'change_control' => $changeControl,
                'created_at' => $baseDate,
                'updated_at' => $baseDate,
                'default_content' => 'Administración e implementación del Sistema de Gestión de Seguridad y Salud en el Trabajo, para lo cual deberá planificar, organizar y dirigir una evaluación, informar a la alta dirección sobre el funcionamiento y los resultados del SG-SST, y actualización de acuerdo con la normatividad vigente.'
            ],
            12 => [
                'id' => 12,
                'type_name' => 'Matriz Legal',
                'description' => 'Matriz de requisitos legales SST',
                'document_type' => 'FT',
                'acronym' => 'SST-010',
                'version_number' => 1,
                'location' => 'DIGITAL',
                'status' => 'ACTIVO',
                'change_control' => $changeControl,
                'created_at' => $baseDate,
                'updated_at' => $baseDate,
                'default_content' => ''
            ],
            13 => [
                'id' => 13,
                'type_name' => 'Indicadores SST',
                'description' => 'Seguimiento a indicadores del SG-SST',
                'document_type' => 'FT',
                'acronym' => 'SST-011',
                'version_number' => 1,
                'location' => 'DIGITAL',
                'status' => 'ACTIVO',
                'change_control' => $changeControl,
                'created_at' => $baseDate,
                'updated_at' => $baseDate,
                'default_content' => ''
            ],
            20 => [
                'id' => 20,
                'type_name' => 'Auditorías Internas',
                'description' => 'Formato de auditorías internas SST',
                'document_type' => 'FT',
                'acronym' => 'SST-012',
                'version_number' => 1,
                'location' => 'DIGITAL',
                'status' => 'ACTIVO',
                'change_control' => $changeControl,
                'created_at' => $baseDate,
                'updated_at' => $baseDate,
                'default_content' => ''
            ],
            27 => [
                'id' => 27,
                'type_name' => 'Acciones Correctivas',
                'description' => 'Formato de acciones correctivas y preventivas',
                'document_type' => 'FT',
                'acronym' => 'SST-013',
                'version_number' => 1,
                'location' => 'DIGITAL',
                'status' => 'ACTIVO',
                'change_control' => $changeControl,
                'created_at' => $baseDate,
                'updated_at' => $baseDate,
                'default_content' => ''
            ],
            28 => [
                'id' => 28,
                'type_name' => 'Revisión por la Dirección',
                'description' => 'Acta de revisión por la dirección',
                'document_type' => 'FT',
                'acronym' => 'SST-014',
                'version_number' => 1,
                'location' => 'DIGITAL',
                'status' => 'ACTIVO',
                'change_control' => $changeControl,
                'created_at' => $baseDate,
                'updated_at' => $baseDate,
                'default_content' => ''
            ],
            38 => [
                'id' => 38,
                'type_name' => 'Comité SST',
                'description' => 'Actas del Comité de SST',
                'document_type' => 'FT',
                'acronym' => 'SST-015',
                'version_number' => 1,
                'location' => 'DIGITAL',
                'status' => 'ACTIVO',
                'change_control' => $changeControl,
                'created_at' => $baseDate,
                'updated_at' => $baseDate,
                'default_content' => ''
            ],
            44 => [
                'id' => 44,
                'type_name' => 'Plan de Mejora',
                'description' => 'Plan de mejoramiento continuo',
                'document_type' => 'FT',
                'acronym' => 'SST-016',
                'version_number' => 1,
                'location' => 'DIGITAL',
                'status' => 'ACTIVO',
                'change_control' => $changeControl,
                'created_at' => $baseDate,
                'updated_at' => $baseDate,
                'default_content' => ''
            ],
            37 => [
                'id' => 37,
                'type_name' => 'Evaluación Inicial',
                'description' => 'Evaluación inicial del SG-SST',
                'document_type' => 'FT',
                'acronym' => 'SST-101',
                'version_number' => 1,
                'location' => 'DIGITAL',
                'status' => 'ACTIVO',
                'change_control' => $changeControl,
                'created_at' => $baseDate,
                'updated_at' => $baseDate,
                'default_content' => ''
            ],

            // MANUALES (MAN)
            14 => [
                'id' => 14,
                'type_name' => 'Manual del SG-SST',
                'description' => 'Manual del Sistema de Gestión de SST',
                'document_type' => 'MAN',
                'acronym' => 'SST-001',
                'version_number' => 1,
                'location' => 'DIGITAL',
                'status' => 'ACTIVO',
                'change_control' => $changeControl,
                'created_at' => $baseDate,
                'updated_at' => $baseDate,
                'default_content' => ''
            ],
            31 => [
                'id' => 31,
                'type_name' => 'Manual de Funciones SST',
                'description' => 'Manual de funciones y responsabilidades SST',
                'document_type' => 'MAN',
                'acronym' => 'SST-002',
                'version_number' => 1,
                'location' => 'DIGITAL',
                'status' => 'ACTIVO',
                'change_control' => $changeControl,
                'created_at' => $baseDate,
                'updated_at' => $baseDate,
                'default_content' => ''
            ],

            // PROCEDIMIENTOS (PRC)
            17 => [
                'id' => 17,
                'type_name' => 'Procedimiento de Identificación de Peligros',
                'description' => 'Procedimiento para identificación y valoración de riesgos',
                'document_type' => 'PRC',
                'acronym' => 'SST-001',
                'version_number' => 1,
                'location' => 'DIGITAL',
                'status' => 'ACTIVO',
                'change_control' => $changeControl,
                'created_at' => $baseDate,
                'updated_at' => $baseDate,
                'default_content' => ''
            ],
            29 => [
                'id' => 29,
                'type_name' => 'Procedimiento de Investigación de Accidentes',
                'description' => 'Procedimiento para investigación de incidentes y accidentes',
                'document_type' => 'PRC',
                'acronym' => 'SST-002',
                'version_number' => 1,
                'location' => 'DIGITAL',
                'status' => 'ACTIVO',
                'change_control' => $changeControl,
                'created_at' => $baseDate,
                'updated_at' => $baseDate,
                'default_content' => ''
            ],
            30 => [
                'id' => 30,
                'type_name' => 'Procedimiento de Auditorías Internas',
                'description' => 'Procedimiento para realización de auditorías',
                'document_type' => 'PRC',
                'acronym' => 'SST-003',
                'version_number' => 1,
                'location' => 'DIGITAL',
                'status' => 'ACTIVO',
                'change_control' => $changeControl,
                'created_at' => $baseDate,
                'updated_at' => $baseDate,
                'default_content' => ''
            ],
            32 => [
                'id' => 32,
                'type_name' => 'Procedimiento de Gestión del Cambio',
                'description' => 'Procedimiento para gestión del cambio',
                'document_type' => 'PRC',
                'acronym' => 'SST-004',
                'version_number' => 1,
                'location' => 'DIGITAL',
                'status' => 'ACTIVO',
                'change_control' => $changeControl,
                'created_at' => $baseDate,
                'updated_at' => $baseDate,
                'default_content' => ''
            ],
            34 => [
                'id' => 34,
                'type_name' => 'Procedimiento de Compras y Contratación',
                'description' => 'Procedimiento para compras con criterios SST',
                'document_type' => 'PRC',
                'acronym' => 'SST-006',
                'version_number' => 1,
                'location' => 'DIGITAL',
                'status' => 'ACTIVO',
                'change_control' => $changeControl,
                'created_at' => $baseDate,
                'updated_at' => $baseDate,
                'default_content' => 'ARL SURA'
            ],
            36 => [
                'id' => 36,
                'type_name' => 'Procedimiento de Comunicación',
                'description' => 'Procedimiento de comunicación SST',
                'document_type' => 'PRC',
                'acronym' => 'SST-007',
                'version_number' => 1,
                'location' => 'DIGITAL',
                'status' => 'ACTIVO',
                'change_control' => $changeControl,
                'created_at' => $baseDate,
                'updated_at' => $baseDate,
                'default_content' => ''
            ],
            39 => [
                'id' => 39,
                'type_name' => 'Procedimiento de Control de Documentos',
                'description' => 'Procedimiento para control de documentos',
                'document_type' => 'PRC',
                'acronym' => 'SST-008',
                'version_number' => 1,
                'location' => 'DIGITAL',
                'status' => 'ACTIVO',
                'change_control' => $changeControl,
                'created_at' => $baseDate,
                'updated_at' => $baseDate,
                'default_content' => ''
            ],
            40 => [
                'id' => 40,
                'type_name' => 'Procedimiento de Conservación de Registros',
                'description' => 'Procedimiento para conservación de registros',
                'document_type' => 'PRC',
                'acronym' => 'SST-009',
                'version_number' => 1,
                'location' => 'DIGITAL',
                'status' => 'ACTIVO',
                'change_control' => $changeControl,
                'created_at' => $baseDate,
                'updated_at' => $baseDate,
                'default_content' => ''
            ],
            41 => [
                'id' => 41,
                'type_name' => 'Procedimiento de Acciones Correctivas',
                'description' => 'Procedimiento para acciones correctivas y preventivas',
                'document_type' => 'PRC',
                'acronym' => 'SST-010',
                'version_number' => 1,
                'location' => 'DIGITAL',
                'status' => 'ACTIVO',
                'change_control' => $changeControl,
                'created_at' => $baseDate,
                'updated_at' => $baseDate,
                'default_content' => ''
            ],
            45 => [
                'id' => 45,
                'type_name' => 'Procedimiento de Preparación de Emergencias',
                'description' => 'Procedimiento para preparación y respuesta ante emergencias',
                'document_type' => 'PRC',
                'acronym' => 'SST-011',
                'version_number' => 1,
                'location' => 'DIGITAL',
                'status' => 'ACTIVO',
                'change_control' => $changeControl,
                'created_at' => $baseDate,
                'updated_at' => $baseDate,
                'default_content' => ''
            ],

            // PROGRAMAS (PRG)
            18 => [
                'id' => 18,
                'type_name' => 'Programa de Capacitación',
                'description' => 'Programa de capacitación en SST',
                'document_type' => 'PRG',
                'acronym' => 'SST-001',
                'version_number' => 1,
                'location' => 'DIGITAL',
                'status' => 'ACTIVO',
                'change_control' => $changeControl,
                'created_at' => $baseDate,
                'updated_at' => $baseDate,
                'default_content' => ''
            ],
            19 => [
                'id' => 19,
                'type_name' => 'Programa de Medicina Preventiva',
                'description' => 'Programa de medicina preventiva y del trabajo',
                'document_type' => 'PRG',
                'acronym' => 'SST-002',
                'version_number' => 1,
                'location' => 'DIGITAL',
                'status' => 'ACTIVO',
                'change_control' => $changeControl,
                'created_at' => $baseDate,
                'updated_at' => $baseDate,
                'default_content' => ''
            ],
            35 => [
                'id' => 35,
                'type_name' => 'Programa de Inspecciones',
                'description' => 'Programa de inspecciones de seguridad',
                'document_type' => 'PRG',
                'acronym' => 'SST-003',
                'version_number' => 1,
                'location' => 'DIGITAL',
                'status' => 'ACTIVO',
                'change_control' => $changeControl,
                'created_at' => $baseDate,
                'updated_at' => $baseDate,
                'default_content' => ''
            ],
            42 => [
                'id' => 42,
                'type_name' => 'Programa de Vigilancia Epidemiológica',
                'description' => 'Programa de vigilancia epidemiológica ocupacional',
                'document_type' => 'PRG',
                'acronym' => 'SST-004',
                'version_number' => 1,
                'location' => 'DIGITAL',
                'status' => 'ACTIVO',
                'change_control' => $changeControl,
                'created_at' => $baseDate,
                'updated_at' => $baseDate,
                'default_content' => ''
            ],
            33 => [
                'id' => 33,
                'type_name' => 'Programa de Orden y Aseo',
                'description' => 'Programa de orden y aseo 5S',
                'document_type' => 'PRG',
                'acronym' => 'SST-005',
                'version_number' => 1,
                'location' => 'DIGITAL',
                'status' => 'ACTIVO',
                'change_control' => $changeControl,
                'created_at' => $baseDate,
                'updated_at' => $baseDate,
                'default_content' => ''
            ],
            43 => [
                'id' => 43,
                'type_name' => 'Programa de Estilos de Vida Saludable',
                'description' => 'Programa de promoción de estilos de vida saludable',
                'document_type' => 'PRG',
                'acronym' => 'SST-005',
                'version_number' => 1,
                'location' => 'DIGITAL',
                'status' => 'ACTIVO',
                'change_control' => $changeControl,
                'created_at' => $baseDate,
                'updated_at' => $baseDate,
                'default_content' => ''
            ],

            // PLANES (PL)
            21 => [
                'id' => 21,
                'type_name' => 'Plan de Trabajo Anual',
                'description' => 'Plan de trabajo anual del SG-SST',
                'document_type' => 'PL',
                'acronym' => 'SST-001',
                'version_number' => 1,
                'location' => 'DIGITAL',
                'status' => 'ACTIVO',
                'change_control' => $changeControl,
                'created_at' => $baseDate,
                'updated_at' => $baseDate,
                'default_content' => ''
            ],
            22 => [
                'id' => 22,
                'type_name' => 'Plan de Emergencias',
                'description' => 'Plan de preparación y respuesta ante emergencias',
                'document_type' => 'PL',
                'acronym' => 'SST-002',
                'version_number' => 1,
                'location' => 'DIGITAL',
                'status' => 'ACTIVO',
                'change_control' => $changeControl,
                'created_at' => $baseDate,
                'updated_at' => $baseDate,
                'default_content' => ''
            ],
            23 => [
                'id' => 23,
                'type_name' => 'Plan de Evacuación',
                'description' => 'Plan de evacuación y puntos de encuentro',
                'document_type' => 'PL',
                'acronym' => 'SST-003',
                'version_number' => 1,
                'location' => 'DIGITAL',
                'status' => 'ACTIVO',
                'change_control' => $changeControl,
                'created_at' => $baseDate,
                'updated_at' => $baseDate,
                'default_content' => ''
            ],
            24 => [
                'id' => 24,
                'type_name' => 'Plan de Prevención y Atención de Emergencias',
                'description' => 'Plan integral de prevención y atención de emergencias',
                'document_type' => 'PL',
                'acronym' => 'SST-004',
                'version_number' => 1,
                'location' => 'DIGITAL',
                'status' => 'ACTIVO',
                'change_control' => $changeControl,
                'created_at' => $baseDate,
                'updated_at' => $baseDate,
                'default_content' => ''
            ],
            25 => [
                'id' => 25,
                'type_name' => 'Plan de Capacitación',
                'description' => 'Plan de capacitación anual',
                'document_type' => 'PL',
                'acronym' => 'SST-005',
                'version_number' => 1,
                'location' => 'DIGITAL',
                'status' => 'ACTIVO',
                'change_control' => $changeControl,
                'created_at' => $baseDate,
                'updated_at' => $baseDate,
                'default_content' => ''
            ],

            // REGISTROS (REG)
            26 => [
                'id' => 26,
                'type_name' => 'Registro de Asistencia a Capacitaciones',
                'description' => 'Registro de asistencia y evidencias de capacitación',
                'document_type' => 'REG',
                'acronym' => 'SST-001',
                'version_number' => 1,
                'location' => 'DIGITAL',
                'status' => 'ACTIVO',
                'change_control' => $changeControl,
                'created_at' => $baseDate,
                'updated_at' => $baseDate,
                'default_content' => 'No Existen Sucursales'
            ],

            // MATRICES (MA)
            46 => [
                'id' => 46,
                'type_name' => 'Matriz de Identificación de Peligros',
                'description' => 'Matriz IPEVR - Identificación de peligros y evaluación de riesgos',
                'document_type' => 'MA',
                'acronym' => 'SST-001',
                'version_number' => 1,
                'location' => 'DIGITAL',
                'status' => 'ACTIVO',
                'change_control' => $changeControl,
                'created_at' => $baseDate,
                'updated_at' => $baseDate,
                'default_content' => ''
            ],

            // ADICIONALES
            15 => [
                'id' => 15,
                'type_name' => 'Misión',
                'description' => 'Misión organizacional',
                'document_type' => 'DOC',
                'acronym' => 'ORG-001',
                'version_number' => 1,
                'location' => 'DIGITAL',
                'status' => 'ACTIVO',
                'change_control' => $changeControl,
                'created_at' => $baseDate,
                'updated_at' => $baseDate,
                'default_content' => 'Misión: Liderar la transformación positiva en el entorno laboral mediante el diagnóstico especializado en la Batería de Riesgo Psicosocial y Consultoría en Seguridad y Salud en el Trabajo.'
            ],
            16 => [
                'id' => 16,
                'type_name' => 'Visión',
                'description' => 'Visión organizacional',
                'document_type' => 'DOC',
                'acronym' => 'ORG-002',
                'version_number' => 1,
                'location' => 'DIGITAL',
                'status' => 'ACTIVO',
                'change_control' => $changeControl,
                'created_at' => $baseDate,
                'updated_at' => $baseDate,
                'default_content' => 'Visión: Posicionar a Cycloid Talent como el principal proveedor de administración SG-SST en Soacha para el 2024 y en Bogotá para el 2025.'
            ],
        ];
    }

    /**
     * Obtiene todas las versiones de un documento (para historial)
     *
     * Como todos los documentos tienen versión 1, retorna un array con el documento
     *
     * @param int $documentId ID del documento
     * @return array Array de versiones (siempre 1 elemento)
     */
    public static function getAllVersions($documentId)
    {
        $document = self::getDocument($documentId);
        return $document ? [$document] : [];
    }

    /**
     * Obtiene la última versión de un documento
     *
     * Alias de getDocument() para compatibilidad
     *
     * @param int $documentId ID del documento
     * @return array|null Datos del documento
     */
    public static function getLatestVersion($documentId)
    {
        return self::getDocument($documentId);
    }

    /**
     * Verifica si un documento existe
     *
     * @param int $documentId ID del documento
     * @return bool True si existe
     */
    public static function exists($documentId)
    {
        return self::getDocument($documentId) !== null;
    }

    /**
     * Obtiene documentos por tipo
     *
     * @param string $documentType Tipo (FT, MAN, PRC, PRG, PL, REG, MA)
     * @return array Array de documentos del tipo especificado
     */
    public static function getByType($documentType)
    {
        $documents = self::getAllDocuments();
        return array_filter($documents, function($doc) use ($documentType) {
            return $doc['document_type'] === $documentType;
        });
    }

    /**
     * Obtiene estadísticas de documentos
     *
     * @return array Array con conteo por tipo
     */
    public static function getStats()
    {
        $documents = self::getAllDocuments();
        $stats = [];

        foreach ($documents as $doc) {
            $type = $doc['document_type'];
            if (!isset($stats[$type])) {
                $stats[$type] = 0;
            }
            $stats[$type]++;
        }

        return $stats;
    }

    /**
     * Obtiene el total de documentos
     *
     * @return int Total de documentos (44)
     */
    public static function count()
    {
        return count(self::getAllDocuments());
    }
}
